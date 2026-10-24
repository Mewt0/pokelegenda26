<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BattleReplayRepository;

/**
 * Статусы, действующие в начале хода: яд, ожог, сон, заморозка, паралич, испуг,
 * ловушки, погодный урон и лечение от погоды/способностей.
 *
 * Логика перенесена из BattleEngineService без изменений (TASK-047, шаг 4). Движок
 * отдаёт вспомогательные методы, которые нужны этому коду.
 */
final class BattleTurnStatusService
{
    public function __construct(
        private BattleRepository $battles,
        private ?BattleReplayRepository $replay,
        private BattleEngineService $engine,
    ) {
    }

public function applyStartOfTurnStatus(int $battleId, int $round, array &$actor, ?array &$opponent = null, ?array $selectedMove = null): array
    {
        $name = strip_tags((string) ($actor['names'] ?? 'Покемон'));
        $battlePokemon = (string) ($actor['battle_pokemon'] ?? '');
        $messages = [];
        $canAct = true;

        foreach ($this->battles->findBattleMajorStatuses($battleId, $battlePokemon) as $status) {
            $id = (int) ($status['id'] ?? 0);
            if ($id === 1) { // poison
                $badlyPoisoned = $this->battles->hasBattleVolatile($battleId, $battlePokemon, 'badly_poisoned');
                $toxicStage = $badlyPoisoned ? max(1, $this->battles->incrementBattleVolatileStacks($battleId, $battlePokemon, 'badly_poisoned')) : 0;
                $damage = $badlyPoisoned
                    ? max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) * min(15, $toxicStage) / 16))
                    : max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s страдает от %s и теряет %d HP.', $name, $badlyPoisoned ? 'тяжелого яда' : 'яда', $damage);
            } elseif ($id === 3) { // burn
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s получает урон от ожога: %d HP.', $name, $damage);
            } elseif ($id === 2) { // sleep
                $moveKey = BattleMoveEffectCatalog::key((string) ($selectedMove['atac_name'] ?? ''));
                if (!in_array($moveKey, ['snore', 'sleep talk'], true)) {
                    $canAct = false;
                    $messages[] = sprintf('%s спит и пропускает ход.', $name);
                }
            } elseif ($id === 4) { // freeze
                $roll = $this->engine->replayRoll($battleId, $round, 'freeze_thaw', 1, 100, 20, null, [
                    'actor_key' => $battlePokemon,
                    'status_id' => $id,
                ]);
                if ($roll <= 20) {
                    $this->battles->deleteBattleStatus($battleId, $battlePokemon, 4);
                    $messages[] = sprintf('%s разморозился.', $name);
                } else {
                    $canAct = false;
                    $messages[] = sprintf('%s заморожен и не может атаковать.', $name);
                }
            } elseif ($id === 5) { // paralysis
                $roll = $this->engine->replayRoll($battleId, $round, 'paralysis_skip', 1, 100, 25, null, [
                    'actor_key' => $battlePokemon,
                    'status_id' => $id,
                ]);
                if ($roll <= 25) {
                    $canAct = false;
                    $messages[] = sprintf('%s парализован и пропускает ход.', $name);
                }
            } elseif ($id === 6) { // flinch/fear
                $canAct = false;
                $messages[] = sprintf('%s напуган и пропускает ход.', $name);
                $this->battles->deleteBattleStatus($battleId, $battlePokemon, 6);
            } elseif ($id === 7) { // confusion
                $roll = $this->engine->replayRoll($battleId, $round, 'confusion_self_hit', 1, 100, 50, null, [
                    'actor_key' => $battlePokemon,
                    'status_id' => $id,
                ]);
                if ($roll <= 50) {
                    $damage = max(1, (int) floor((((2 * max(1, (int) ($actor['lvl'] ?? 1)) / 5 + 2) * 40 * max(1, (int) ($actor['atk'] ?? 1)) / max(1, (int) ($actor['def'] ?? 1))) / 50) + 2));
                    $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                    $canAct = false;
                    $messages[] = sprintf('%s спутан и ранит себя на %d HP.', $name, $damage);
                    $this->replay?->recordDamage($battleId, $round, [
                        'actor_key' => $battlePokemon,
                        'target_key' => $battlePokemon,
                        'move_name' => 'confusion',
                        'damage' => $damage,
                        'hp_after' => (int) ($actor['hp_my'] ?? 0),
                        'self_damage' => true,
                    ]);
                }
            }
            if ($id === 8) { // leech seed
                if (in_array('grass', $this->engine->effectiveTypeLabels($battleId, $actor), true)) {
                    $this->battles->deleteBattleStatus($battleId, $battlePokemon, 8);
                    $messages[] = sprintf('%s невосприимчив к семенам-пиявкам.', $name);
                    continue;
                }
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                if ($opponent !== null) {
                    $opponent['hp_my'] = min(
                        max(1, (int) ($opponent['hp_max'] ?? 1)),
                        (int) ($opponent['hp_my'] ?? 0) + $damage
                    );
                    $opponentName = strip_tags((string) ($opponent['names'] ?? 'Покемон'));
                    $messages[] = sprintf('%s теряет %d HP от пиявок. %s восстанавливает %d HP.', $name, $damage, $opponentName, $damage);
                } else {
                    $messages[] = sprintf('%s теряет %d HP от пиявок.', $name, $damage);
                }
            }
            if ($id === 9) { // curse
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 4));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s страдает от проклятия и теряет %d HP.', $name, $damage);
            }
            if ((int) ($actor['hp_my'] ?? 0) <= 0) {
                $canAct = false;
                break;
            }
        }

        foreach ($this->battles->findBattleVolatiles($battleId, $battlePokemon) as $volatile) {
            $kind = (string) ($volatile['kind'] ?? '');
            if ($kind === 'partial_trap') {
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s теряет %d HP от удерживающей ловушки.', $name, $damage);
            } elseif ($kind === 'curse') {
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 4));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s страдает от проклятия и теряет %d HP.', $name, $damage);
            } elseif ($kind === 'nightmare' && $this->engine->hasMajorStatus($battleId, $actor, 2)) {
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 4));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s мучается кошмаром и теряет %d HP.', $name, $damage);
            } elseif ($kind === 'perish_song') {
                $left = max(0, (int) ($volatile['roundEnd'] ?? $round) - $round);
                if ($left <= 1) {
                    $actor['hp_my'] = 0;
                    $messages[] = sprintf('%s падает от гибельной песни.', $name);
                } else {
                    $messages[] = sprintf('%s слышит гибельную песнь: осталось %d раунд.', $name, $left - 1);
                }
            }

            if ((int) ($actor['hp_my'] ?? 0) <= 0) {
                $canAct = false;
                break;
            }
        }

        foreach ($this->battles->findBattleSideFields($battleId, $this->engine->battleSideForPokemon($actor)) as $field) {
            $kind = (string) ($field['kind'] ?? '');
            if (!in_array($kind, ['gmax_cannonade', 'gmax_vine_lash'], true)) {
                continue;
            }
            $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 6));
            $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
            $label = $kind === 'gmax_vine_lash' ? 'гигантских лоз' : 'водной канонады';
            $messages[] = sprintf('%s теряет %d HP от %s.', $name, $damage, $label);
            if ((int) ($actor['hp_my'] ?? 0) <= 0) {
                $canAct = false;
                break;
            }
        }

        $weather = $this->battles->findBattleWeather($battleId);
        $weatherKind = (string) ($weather['kind'] ?? '');
        $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
        if (in_array($weatherFamily, ['sandstorm', 'hail'], true) && (int) ($actor['hp_my'] ?? 0) > 0) {
            $types = $this->engine->effectiveTypeLabels($battleId, $actor);
            $immune = $weatherFamily === 'sandstorm'
                ? array_intersect($types, ['rock', 'ground', 'steel']) !== []
                : in_array('ice', $types, true);
            if (!$immune && !BattleAbilityCatalog::weatherDamageImmune(BattleAbilityCatalog::key($actor), $weatherKind)) {
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s получает %d HP урона от погоды.', $name, $damage);
                if ((int) ($actor['hp_my'] ?? 0) <= 0) {
                    $canAct = false;
                }
            }
        }

        $ability = BattleAbilityCatalog::key($actor);
        if ($ability === 'rain_dish' && $weatherFamily === 'rain' && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
            $healed = $this->engine->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP благодаря дождю.', $name, $healed);
            }
        } elseif ($ability === 'ice_body' && $weatherFamily === 'hail' && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
            $healed = $this->engine->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP ледяным телом.', $name, $healed);
            }
        } elseif ($ability === 'dry_skin') {
            if ($weatherFamily === 'rain' && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
                $healed = $this->engine->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8)));
                if ($healed > 0) {
                    $messages[] = sprintf('%s восстанавливает %d HP сухой кожей.', $name, $healed);
                }
            } elseif ($weatherFamily === 'sun') {
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s теряет %d HP из-за сухой кожи на солнце.', $name, $damage);
            }
        } elseif ($ability === 'solar_power' && $weatherFamily === 'sun') {
            $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8));
            $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
            $messages[] = sprintf('%s теряет %d HP от солнечной батареи.', $name, $damage);
        }

        $terrainKind = (string) (($this->battles->findBattleTerrain($battleId)['kind'] ?? ''));
        if ($terrainKind === 'grassy' && (int) ($actor['hp_my'] ?? 0) > 0 && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
            $healed = $this->engine->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP от травяной арены.', $name, $healed);
            }
        }

        return ['canAct' => $canAct, 'messages' => $messages];
    }
}
