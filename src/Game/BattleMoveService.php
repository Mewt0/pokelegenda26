<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BattleReplayRepository;

/**
 * Одна атака в бою: проверка погодных блоков, точность, крит, урон через
 * BattleDamageService, эффекты атаки (стадии, статусы, тренировочные эффекты,
 * особые атаки) и тексты для лога.
 *
 * Логика перенесена из BattleEngineService без изменений (TASK-047, шаг 6). Движок
 * отдаёт вспомогательные методы, которыми пользуется этот код.
 */
final class BattleMoveService
{
    public function __construct(
        private BattleRepository $battles,
        private ?BattleReplayRepository $replay,
        private BattleDamageService $damageService,
        private BattleTurnStatusService $turnStatusService,
        private BattleEngineService $engine,
    ) {
    }

public function applyMove(int $battleId, int $round, array &$attacker, array &$defender, array $move): string
    {
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        $defenderName = strip_tags((string) ($defender['names'] ?? 'Покемон'));
        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        $moveId = (int) ($move['id'] ?? $move['atac_id'] ?? 0);
        $category = (int) ($move['atac_categori'] ?? 1);
        $power = (int) ($move['atac_power'] ?? 0);
        $parts = [];
        $this->engine->setLastDamageDealt(0);

        $statusGate = $this->turnStatusService->applyStartOfTurnStatus($battleId, $round, $attacker, $defender, $move);
        foreach ($statusGate['messages'] as $m) {
            $parts[] = $m;
        }
        if (!$statusGate['canAct']) {
            return implode(' ', $parts);
        }
        if ($category >= 3 && $this->battles->hasBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'taunt')) {
            $parts[] = sprintf('%s не может использовать статусный прием из-за провокации.', $attackerName);
            return implode(' ', $parts);
        }
        if ($category >= 3 && $this->engine->heldItemHasMeta($attacker, 'held_item:assault_vest')) {
            $parts[] = sprintf('%s не может использовать статусный прием из-за штурмового жилета.', $attackerName);
            return implode(' ', $parts);
        }

        // In single battles Helping Hand buffs the user, not its opponent.
        // Keep the charge on the Pokemon until a move actually deals damage.
        if ($moveId === 270) {
            $added = $this->battles->addBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'helping_hand', 999999);
            $parts[] = $added
                ? sprintf('%s использует %s — следующая наносящая урон атака усилена.', $attackerName, $moveName)
                : sprintf('%s использует %s — усиление уже действует.', $attackerName, $moveName);
            return implode(' ', $parts);
        }

        $weatherBlockText = $this->engine->weatherBlockedMoveText($battleId, $attackerName, $moveName, (string) ($move['atac_tip'] ?? ''));
        if ($weatherBlockText !== '') {
            $parts[] = $weatherBlockText;
            return implode(' ', $parts);
        }

        $hitChance = $this->engine->effectiveAccuracy($battleId, $attacker, $defender, $move);
        $hitRoll = $this->engine->replayRoll($battleId, $round, 'accuracy', 1, 100, $hitChance, null, [
            'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
            'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
            'move_id' => $moveId,
            'move_name' => $moveName,
        ]);
        $this->replay?->recordRandomRoll($battleId, $round, 'accuracy_result', $hitRoll, 1, 100, $hitChance, $hitRoll <= $hitChance, [
            'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
            'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
            'move_id' => $moveId,
            'move_name' => $moveName,
        ]);
        if ($hitRoll > $hitChance) {
            $missText = sprintf('%s использует %s — промах!', $attackerName, $moveName);
            $crashText = $this->engine->applyMissMoveEffect($attacker, $move);
            return trim(implode(' ', array_filter([...$parts, $missText, $crashText])));
        }

        $damageText = '';
        if ($category < 3 && $power > 0) {
            $damageText = $this->damageService->damageMove($battleId, $round, $attacker, $defender, $move);
        } else {
            $damageText = sprintf('%s использует %s.', $attackerName, $moveName);
        }
        $parts[] = $damageText;

        // Primary stat effects from stat_attak are authoritative. Fallback by name only if DB has no row.
        $effects = $this->battles->findMoveStatEffects($moveId);
        if ($effects === []) {
            $effects = $this->engine->statMoveEffects($moveName);
        }
        foreach ($effects as $effect) {
            if (BattleMoveEffectCatalog::key($moveName) === 'growth'
                && BattleAbilityCatalog::weatherFamily((string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''))) === 'sun'
            ) {
                $effect['delta'] = 2;
            }
            $text = $this->engine->applyStageEffect($battleId, $attacker, $defender, $effect);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        // Status from atac_not for pure status moves.
        $primaryStatus = $this->battles->findMovePrimaryStatusId($moveId);
        $catalogPrimaryStatus = BattleMoveEffectCatalog::primaryStatus($moveId, $moveName);
        if ($primaryStatus <= 0 && $catalogPrimaryStatus !== null) {
            $primaryStatus = (int) $catalogPrimaryStatus['statusId'];
        }
        if ($primaryStatus > 0 && ($category >= 3 || $power <= 0)) {
            $statusText = $this->engine->tryApplyStatus($battleId, $round, $defender, $primaryStatus, (int) ($catalogPrimaryStatus['chance'] ?? 100), (string) ($move['atac_tip'] ?? ''), $moveName);
            if ($statusText !== '') {
                $parts[] = $statusText;
            }
            if (BattleMoveEffectCatalog::key($moveName) === 'toxic' && $this->battles->hasBattleStatus($battleId, (string) ($defender['battle_pokemon'] ?? ''), 1)) {
                $this->battles->addBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), 'badly_poisoned', 999999);
            }
        }

        // Secondary effects from attac_dop after a successful hit.
        $secondaryEffects = $this->battles->findMoveSecondaryEffects($moveId);
        foreach (BattleMoveEffectCatalog::secondaryStatuses($moveId, $moveName) as $statusEffect) {
            $alreadyKnown = false;
            foreach ($secondaryEffects as $existingEffect) {
                if (($existingEffect['kind'] ?? '') === 'status' && (int) ($existingEffect['statusId'] ?? 0) === (int) $statusEffect['statusId']) {
                    $alreadyKnown = true;
                    break;
                }
            }
            if (!$alreadyKnown) {
                $secondaryEffects[] = [
                    'target' => (string) ($statusEffect['target'] ?? 'enemy'),
                    'kind' => 'status',
                    'field' => 'status',
                    'delta' => 0,
                    'label' => 'Статус',
                    'statusId' => (int) $statusEffect['statusId'],
                    'chance' => (int) $statusEffect['chance'],
                ];
            }
        }
        foreach ($secondaryEffects as $effect) {
            $chance = max(1, min(100, (int) ($effect['chance'] ?? 100)));
            $effectRoll = $this->engine->replayRoll($battleId, $round, 'secondary_effect', 1, 100, $chance, null, [
                'actor_key' => (string) ($attacker['battle_pokemon'] ?? ''),
                'target_key' => (string) ($defender['battle_pokemon'] ?? ''),
                'move_id' => $moveId,
                'move_name' => $moveName,
                'effect' => $effect,
            ]);
            if ($effectRoll > $chance) {
                continue;
            }
            if (($effect['kind'] ?? '') === 'status') {
                $target = ($effect['target'] ?? 'enemy') === 'self' ? $attacker : $defender;
                $statusText = $this->engine->tryApplyStatus($battleId, $round, $target, (int) ($effect['statusId'] ?? 0), $chance, (string) ($move['atac_tip'] ?? ''), $moveName);
                if ($statusText !== '') {
                    $parts[] = $statusText;
                }
                continue;
            }
            $text = $this->engine->applyStageEffect($battleId, $attacker, $defender, $effect);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        $trainingText = $this->engine->applyNamedTrainingEffect($battleId, $round, $attacker, $defender, $move);
        if ($trainingText !== '') {
            $parts[] = $trainingText;
        }

        $specialText = $this->engine->applySpecialMoveEffect($battleId, $round, $attacker, $defender, $move);
        if ($specialText !== '') {
            $parts[] = $specialText;
        }

        if ((int) ($defender['hp_my'] ?? 0) <= 0 && $this->battles->hasBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), 'destiny_bond')) {
            $attacker['hp_my'] = 0;
            $parts[] = sprintf('%s забирает %s с собой.', $defenderName, $attackerName);
        }

        return implode(' ', array_values(array_filter($parts)));
    }
}
