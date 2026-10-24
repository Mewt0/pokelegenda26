<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BattleReplayRepository;

/**
 * Расчёт урона одной атаки: эффективность типа, крит, погода, террейн, экраны и
 * предметы. Логика перенесена из BattleEngineService без изменений (TASK-047, шаг 3).
 *
 * Состояние lastDamageDealt остаётся в движке: сервис пишет и читает его через
 * setLastDamageDealt()/lastDamageDealt(), чтобы значение не разъезжалось.
 */
final class BattleDamageService
{
    public function __construct(
        private BattleRepository $battles,
        private BattleMathService $math,
        private ?BattleReplayRepository $replay,
        private BattleEngineService $engine,
    ) {
    }

public function damageMove(int $battleId, int $round, array $attacker, array &$defender, array $move): string
    {
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        $defenderName = strip_tags((string) ($defender['names'] ?? 'Покемон'));
        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        $category = (int) ($move['atac_categori'] ?? 1);
        $power = max(1, (int) ($move['atac_power'] ?? 1));
        $isSpecial = $category === 2;
        $atkDbField = $isSpecial ? 'satk' : 'atk';
        $atkStageField = $isSpecial ? 'spattac' : 'attac';
        $defDbField = $isSpecial ? 'sdef' : 'def';
        $defStageField = $isSpecial ? 'spdefend' : 'defend';
        if ($this->battles->hasBattleRoom($battleId, 'wonder_room')) {
            $defDbField = $isSpecial ? 'def' : 'sdef';
            $defStageField = $isSpecial ? 'defend' : 'spdefend';
        }

        $atk = $this->engine->effectiveStatFor($battleId, $attacker, $atkDbField, $atkStageField);
        $def = $this->engine->effectiveStatFor($battleId, $defender, $defDbField, $defStageField);
        if (!$isSpecial && $this->engine->hasMajorStatus($battleId, $attacker, 3)) {
            // Burn halves physical attack.
            $atk = max(1, (int) floor($atk / 2));
        }

        $lvl = max(1, (int) ($attacker['lvl'] ?? 1));
        $moveType = (string) ($move['atac_tip'] ?? 'Normal');
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
        if (BattleMoveEffectCatalog::key($moveName) === 'weather ball' && $weatherKind !== '') {
            $moveType = match ($weatherFamily) {
                'sun' => 'Fire',
                'rain' => 'Water',
                'hail' => 'Ice',
                'sandstorm' => 'Rock',
                default => $moveType,
            };
            $power *= 2;
        }
        $attackerTypes = $this->engine->effectiveTypeLabels($battleId, $attacker);
        $defenderTypes = $this->engine->effectiveTypeLabels($battleId, $defender);
        $stab = $this->math->stab($moveType, $attackerTypes);
        $typeEffect = $this->math->typeEffectiveness($moveType, $defenderTypes);
        $modifierLogs = [];
        if ($weatherKind === 'strong_winds'
            && in_array('flying', $defenderTypes, true)
            && in_array(strtolower($moveType), ['electric', 'ice', 'rock'], true)
            && $typeEffect > 1.0
        ) {
            $typeEffect /= 2;
            $modifierLogs[] = '[DAMAGE_MODIFIER] Flying weakness neutralized by Delta Stream.';
        }
        if ($typeEffect <= 0.0) {
            $this->engine->setLastDamageDealt(0);
            $this->replay?->recordDamage($battleId, $round, [
                'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
                'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
                'move_id' => (int) ($move['id'] ?? $move['atac_id'] ?? 0),
                'move_name' => $moveName,
                'damage' => 0,
                'blocked_by_type' => true,
                'type_effectiveness' => $typeEffect,
                'move_type' => $moveType,
            ]);
            return sprintf('%s использует %s. %s не получает урона. %s', $attackerName, $moveName, $defenderName, $this->math->typeMessage($typeEffect));
        }

        $base = (((2 * $lvl / 5 + 2) * $power * $atk / max(1, $def)) / 50) + 2;
        $randRoll = $this->engine->replayRoll($battleId, $round, 'damage_variance', 85, 100, null, null, [
            'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
            'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
            'move_id' => (int) ($move['id'] ?? $move['atac_id'] ?? 0),
            'move_name' => $moveName,
        ]);
        $rand = $randRoll / 100;
        $critChance = $this->engine->criticChance((string) ($move['critic'] ?? '3'));
        $critChance = max(0, min(100, $critChance + $this->engine->heldItemCriticalBonus($attacker)));
        $critRoll = $this->engine->replayRoll($battleId, $round, 'critical', 1, 100, $critChance, null, [
            'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
            'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
            'move_id' => (int) ($move['id'] ?? $move['atac_id'] ?? 0),
            'move_name' => $moveName,
        ]);
        $isCrit = $critRoll <= $critChance;
        $crit = $isCrit ? 1.5 : 1.0;
        $weatherPower = $this->engine->weatherPowerModifier($weatherKind, $moveType);
        if ($weatherPower > 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s move boosted by %s x%.1f.', $moveType, $this->engine->weatherLogKey($weatherKind), $weatherPower);
        } elseif ($weatherPower > 0.0 && $weatherPower < 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s move weakened by %s x%.1f.', $moveType, $this->engine->weatherLogKey($weatherKind), $weatherPower);
        }
        if (BattleMoveEffectCatalog::key($moveName) === 'solarbeam' || BattleMoveEffectCatalog::key($moveName) === 'solar beam') {
            $weatherPower *= in_array($weatherFamily, ['rain', 'sandstorm', 'hail'], true) ? 0.5 : 1.0;
        }
        $abilityPower = BattleAbilityCatalog::weatherAttackMultiplier(BattleAbilityCatalog::key($attacker), $weatherKind, $moveType);
        if (BattleAbilityCatalog::key($defender) === 'dry_skin' && strtolower($moveType) === 'fire') {
            $abilityPower *= 1.25;
        }
        $terrainKind = (string) (($this->battles->findBattleTerrain($battleId)['kind'] ?? ''));
        $terrainPower = $this->engine->terrainPowerModifier($terrainKind, $moveType);
        $screenPower = $this->engine->screenDamageModifier($battleId, $defender, $isSpecial);
        $heldPower = $this->engine->heldItemPowerModifier($attacker, $moveType, $category);
        $helpingHand = $this->battles->hasBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'helping_hand');
        $helpingHandPower = $helpingHand ? 1.5 : 1.0;
        if ($helpingHand) {
            $modifierLogs[] = '[DAMAGE_MODIFIER] Helping Hand x1.5.';
        }
        if ($heldPower > 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s boosted by held item x%.1f.', $moveType, $heldPower);
        }
        $damage = max(1, (int) floor($base * $stab * $typeEffect * $rand * $crit * $weatherPower * $abilityPower * $terrainPower * $screenPower * $heldPower * $helpingHandPower));
        $this->engine->setLastDamageDealt($damage);

        $hpBefore = (int) ($defender['hp_my'] ?? 0);
        $defender['hp_my'] = max(0, (int) ($defender['hp_my'] ?? 0) - $damage);
        $hpLeft = (int) ($defender['hp_my'] ?? 0);
        if ($helpingHand && $hpLeft < $hpBefore) {
            $this->battles->clearBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'helping_hand');
        }
        $hpMax = max(1, (int) ($defender['hp_max'] ?? 1));
        $this->replay?->recordDamage($battleId, $round, [
            'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
            'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
            'move_id' => (int) ($move['id'] ?? $move['atac_id'] ?? 0),
            'move_name' => $moveName,
            'move_type' => $moveType,
            'category' => $category,
            'level' => $lvl,
            'power' => $power,
            'atk' => $atk,
            'def' => $def,
            'base_damage' => round($base, 4),
            'random_roll' => $randRoll,
            'critical_roll' => $critRoll,
            'critical_chance' => $critChance,
            'critical' => $isCrit,
            'stab' => $stab,
            'type_effectiveness' => $typeEffect,
            'weather' => $weatherKind,
            'terrain' => $terrainKind,
            'modifiers' => [
                'random' => $rand,
                'critical' => $crit,
                'weather' => $weatherPower,
                'ability' => $abilityPower,
                'terrain' => $terrainPower,
                'screen' => $screenPower,
                'held_item' => $heldPower,
                'helping_hand' => $helpingHandPower,
            ],
            'modifier_logs' => $modifierLogs,
            'damage' => $damage,
            'hp_before' => $hpBefore,
            'hp_after' => $hpLeft,
            'hp_max' => $hpMax,
        ]);
        $typeText = $this->math->typeMessage($typeEffect);
        $critText = $isCrit ? ' КРИТ!' : '';
        $tail = trim($critText . ' ' . $typeText);
        $tail = $tail !== '' ? ' ' . $tail : '';

        $logTail = $modifierLogs !== [] ? ' ' . implode(' ', $modifierLogs) : '';
        return sprintf('%s использует %s.%s %s теряет %d HP (%d/%d).%s', $attackerName, $moveName, $tail, $defenderName, $damage, $hpLeft, $hpMax, $logTail);
    }
}
