<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;

final class BattleEngineService
{
    public function __construct(private BattleRepository $battles)
    {
    }

    public function state(int $userId): array
    {
        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => true, 'active' => false];
        }

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => true, 'active' => false];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        if ($player === null || $enemy === null) {
            return [
                'ok' => false,
                'active' => true,
                'message' => 'Состояние боя повреждено.',
                'debug' => [
                    'battle_id' => (int) ($battle['id'] ?? 0),
                    'poke_1' => (string) ($battle['poke_1'] ?? ''),
                    'poke_2' => (string) ($battle['poke_2'] ?? ''),
                    'player_found' => $player !== null,
                    'enemy_found' => $enemy !== null,
                ],
            ];
        }

        $log = $this->battles->getBattleLog((int) $battle['id']);
        $logRows = $this->formatLogRows($log);

        return [
            'ok' => true,
            'active' => true,
            'battleId' => (int) $battle['id'], // backward compatibility
            'round' => (int) ($battle['raund'] ?? 1), // backward compatibility
            'player' => $this->formatPokemon($player), // backward compatibility
            'enemy' => $this->formatPokemon($enemy), // backward compatibility
            'moves' => $this->formatMoves($player), // backward compatibility
            'switchOptions' => $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)), // backward compatibility
            'log' => $logRows,
            'battle' => [
                'id' => (int) $battle['id'],
                'round' => (int) ($battle['raund'] ?? 1),
                'player' => $this->formatPokemon($player),
                'enemy' => $this->formatPokemon($enemy),
                'moves' => $this->formatMoves($player),
                'switchOptions' => $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)),
                'log' => $logRows,
                'logByRound' => $this->groupLogByRound($logRows),
            ],
        ];
    }

    public function action(int $userId, string $action, array $payload): array
    {
        return match ($action) {
            'attack' => $this->attack($userId, (int) ($payload['move_id'] ?? 0)),
            'switch' => $this->switchPokemon($userId, (int) ($payload['pokemon_id'] ?? 0)),
            'item' => $this->useItem($userId, (int) ($payload['item_user_id'] ?? 0)),
            'ball' => $this->useBall($userId, (int) ($payload['item_user_id'] ?? 0)),
            'escape' => $this->escape($userId),
            default => ['ok' => false, 'active' => true, 'message' => 'Неизвестное действие.'],
        };
    }

    public function acknowledgeEnd(int $userId): array
    {
        return ['ok' => true, 'active' => false, 'userId' => $userId];
    }

    private function attack(int $userId, int $moveId): array
    {
        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой не найден.'];
        }

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой завершен.'];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        $playerMove = $this->selectMove($this->movesForPokemon($player), $moveId);
        $enemyMove = $this->randomMove($this->battles->findAvailableMoves((int) ($enemy['basenum'] ?? 0), (int) ($enemy['lvl'] ?? 1)));

        $messages = [];
        $playerFirst = $this->whoActsFirst($playerMove, $enemyMove, (int) ($player['speed'] ?? 1), (int) ($enemy['speed'] ?? 1));

        if ($playerFirst) {
            $messages[] = $this->applyHit($player, $enemy, $playerMove);
            if ((int) $enemy['hp_my'] > 0) {
                $messages[] = $this->applyHit($enemy, $player, $enemyMove);
            }
        } else {
            $messages[] = $this->applyHit($enemy, $player, $enemyMove);
            if ((int) $player['hp_my'] > 0) {
                $messages[] = $this->applyHit($player, $enemy, $playerMove);
            }
        }

        $this->battles->updatePokemonHp((string) $battle['poke_1'], (int) $player['hp_my']);
        $this->battles->updatePokemonHp((string) $battle['poke_2'], (int) $enemy['hp_my']);

        foreach ($messages as $text) {
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $text);
        }

        $finished = false;
        $result = null;
        $rewards = ['coins' => 0, 'exp' => 0];
        $currentRound = (int) ($battle['raund'] ?? 1);
        if ((int) $enemy['hp_my'] <= 0) {
            $finished = true;
            $result = 'win';
            $enemyLvl = max(1, (int) ($enemy['lvl'] ?? 1));
            $rewards = [
                'coins' => max(5, $enemyLvl * 3),
                'exp' => max(10, $enemyLvl * 12),
            ];
            $this->battles->addCoins($userId, $rewards['coins']);
            $effort = $this->battles->addExperienceAndEffort(
                $userId,
                (int) ($player['id'] ?? 0),
                $rewards['exp'],
                4
            );
            if (($effort['exp'] ?? 0) > 0) {
                $rewardMessage = sprintf(
                    '%s получает %d опыта и %d EV%s.',
                    strip_tags((string) ($player['names'] ?? 'Покемон')),
                    (int) $effort['exp'],
                    (int) $effort['ev'],
                    (int) ($effort['levelUps'] ?? 0) > 0 ? ' — уровень ' . (int) $effort['level'] : ''
                );
                $messages[] = $rewardMessage;
                $this->battles->insertBattleLog((int) $battle['id'], $currentRound, $rewardMessage);
            }
            $this->battles->finishBattle((int) $battle['id'], $userId, $userId);
        } elseif ((int) $player['hp_my'] <= 0) {
            $finished = true;
            $result = 'lose';
            $this->battles->finishBattle((int) $battle['id'], $userId, 0);
        } else {
            $this->battles->incrementRoundAndResetActions((int) $battle['id']);
        }

        if ($finished) {
            $logRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            return [
                'ok' => true,
                'active' => false,
                'finished' => true,
                'result' => $result,
                'rewards' => $rewards,
                'messages' => array_values(array_filter($messages)),
                'battle' => [
                    'id' => (int) $battle['id'],
                    'round' => $currentRound,
                    'player' => $this->formatPokemon($player),
                    'enemy' => $this->formatPokemon($enemy),
                    'moves' => [],
                    'switchOptions' => [],
                    'log' => $logRows,
                    'logByRound' => $this->groupLogByRound($logRows),
                ],
            ];
        }

        $state = $this->state($userId);
        $state['ok'] = true;
        $state['messages'] = array_values(array_filter($messages));
        $state['finished'] = false;
        $state['result'] = null;
        $state['rewards'] = ['coins' => 0, 'exp' => 0];
        $state['battle']['logByRound'] = $this->groupLogByRound($state['log'] ?? []);

        return $state;
    }

    private function switchPokemon(int $userId, int $pokemonId): array
    {
        if ($pokemonId <= 0) {
            return ['ok' => false, 'active' => true, 'message' => 'Выберите покемона для смены.'];
        }

        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой не найден.'];
        }

        if (!$this->battles->switchPlayerPokemon($battleId, $userId, $pokemonId)) {
            return ['ok' => false, 'active' => true, 'message' => 'Смена покемона недоступна.'];
        }

        $state = $this->state($userId);
        $state['ok'] = true;
        $state['messages'] = ['Покемон успешно заменен.'];
        return $state;
    }

    private function useItem(int $userId, int $itemUserId): array
    {
        $item = $this->battles->findBattleInventoryItem($userId, $itemUserId);
        if ($item === null) {
            return ['ok' => false, 'active' => true, 'message' => 'Предмет недоступен.'];
        }

        if ((int) ($item['item_id'] ?? 0) === 3) {
            return $this->useBall($userId, $itemUserId);
        }

        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой не найден.'];
        }

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой завершен.'];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        $itemId = (int) ($item['item_id'] ?? 0);
        $playerName = strip_tags((string) ($player['names'] ?? 'Покемон'));

        if ($itemId === 15) {
            $removed = $this->battles->clearBattleStatus((string) ($player['battle_pokemon'] ?? $battle['poke_1']), 2);
            if ($removed <= 0) {
                return [
                    'ok' => false,
                    'active' => true,
                    'message' => 'Энергетик сейчас не нужен: покемон не спит.',
                ];
            }

            $message = sprintf('Игрок #%d использует: Энергетик, на: #%s.', $userId, $playerName);
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $message);
            $this->battles->incrementRoundAndResetActions((int) $battle['id']);

            $state = $this->state($userId);
            $state['ok'] = true;
            $state['messages'] = [$message];
            $state['finished'] = false;
            $state['result'] = null;
            $state['rewards'] = ['coins' => 0, 'exp' => 0];
            return $state;
        }

        return ['ok' => false, 'active' => true, 'message' => 'Этот предмет пока нельзя использовать в бою.'];
    }

    private function useBall(int $userId, int $itemUserId): array
    {
        $item = $this->battles->findBattleInventoryItem($userId, $itemUserId);
        if ($item === null || (int) ($item['item_id'] ?? 0) !== 3) {
            return ['ok' => false, 'active' => true, 'message' => 'Покебол недоступен.'];
        }

        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой не найден.'];
        }

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой завершен.'];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        if ((int) ($enemy['poimka'] ?? 0) !== 1) {
            return ['ok' => false, 'active' => true, 'message' => 'Этого покемона ловить нельзя.'];
        }

        $enemyName = strip_tags((string) ($enemy['names'] ?? 'Покемон'));
        $round = (int) ($battle['raund'] ?? 1);
        $caught = $this->tryCatchWildPokemon((int) ($enemy['hp_my'] ?? 1), (int) ($enemy['hp_max'] ?? 1), 1);

        if (!$caught) {
            $message = sprintf('Игрок #%d использует: Покебол, но #%s не хочет залазить в него.', $userId, $enemyName);
            $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
            $this->battles->incrementRoundAndResetActions((int) $battle['id']);

            $state = $this->state($userId);
            $state['ok'] = true;
            $state['messages'] = [$message];
            $state['finished'] = false;
            $state['result'] = null;
            $state['rewards'] = ['coins' => 0, 'exp' => 0];
            return $state;
        }

        $active = $this->battles->countActivePokemon($userId) >= 6 ? 0 : 1;
        $newPokemonId = $this->battles->catchWildPokemon($userId, (string) ($enemy['battle_pokemon'] ?? $battle['poke_2']), $active);
        if ($newPokemonId === null) {
            return ['ok' => false, 'active' => true, 'message' => 'Покемона не удалось добавить.'];
        }

        $this->battles->decrementInventoryItemRow($userId, $itemUserId);
        $message = $active > 0
            ? sprintf('Покемон #%s успешно пойман и добавлен в команду.', $enemyName)
            : sprintf('Покемон #%s успешно пойман и отправлен в питомник.', $enemyName);
        $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
        $this->battles->finishBattle((int) $battle['id'], $userId, $userId);

        return [
            'ok' => true,
            'active' => false,
            'finished' => true,
            'result' => 'caught',
            'rewards' => ['coins' => 0, 'exp' => 0],
            'messages' => [$message],
            'caughtPokemonId' => $newPokemonId,
            'caughtActive' => $active > 0,
            'battle' => [
                'id' => (int) $battle['id'],
                'round' => $round,
                'player' => $this->formatPokemon($player),
                'enemy' => $this->formatPokemon($enemy),
                'moves' => [],
                'switchOptions' => [],
                'log' => [],
                'logByRound' => [],
            ],
        ];
    }

    private function escape(int $userId): array
    {
        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => true, 'active' => false];
        }

        $this->battles->finishBattle($battleId, $userId, 0);
        return [
            'ok' => true,
            'active' => false,
            'finished' => true,
            'result' => 'escape',
            'rewards' => ['coins' => 0, 'exp' => 0],
            'messages' => ['Вы успешно сбежали из боя.'],
        ];
    }

    private function tryCatchWildPokemon(int $hp, int $hpMax, int $bonus): bool
    {
        $hp = max(1, $hp);
        $hpMax = max($hp, $hpMax);
        $bonus = max(1, $bonus);
        $catchValue = (int) round(((3 * $hpMax - 2 * $hp) * (random_int(0, 255) * $bonus) / (3 * $hp)) * 1);
        if ($catchValue <= 0) {
            $catchValue = 1;
        }

        $catchValue2 = sqrt(sqrt(996711660 / $catchValue));
        if ($catchValue2 <= 0) {
            $catchValue2 = 1;
        }

        $catch = (int) round(sqrt(918510 / $catchValue2));
        return $catchValue > $catch;
    }

    private function applyHit(array $attacker, array &$defender, array $move): string
    {
        $acc = max(1, min(100, (int) ($move['atac_accuracy'] ?? 100)));
        if (random_int(1, 100) > $acc) {
            $attackerName = (string) ($attacker['names'] ?? 'Покемон');
            $moveName = (string) ($move['atac_name'] ?? 'Атака');
            return sprintf('%s использует %s — промах!', $attackerName, $moveName);
        }

        $power = max(1, (int) ($move['atac_power'] ?? 35));
        $atk = max(1, (int) ($attacker['atk'] ?? 10));
        $def = max(1, (int) ($defender['def'] ?? 10));
        $lvl = max(1, (int) ($attacker['lvl'] ?? 1));

        $base = (((2 * $lvl / 5 + 2) * $power * $atk / $def) / 50) + 2;
        $rand = random_int(85, 100) / 100;
        $critChance = max(0, min(100, (int) ($move['critic'] ?? 6)));
        $isCrit = random_int(1, 100) <= $critChance;
        $crit = $isCrit ? 1.5 : 1.0;
        $damage = max(1, (int) floor($base * $rand * $crit));

        $defender['hp_my'] = max(0, (int) $defender['hp_my'] - $damage);

        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        $defenderName = strip_tags((string) ($defender['names'] ?? 'Покемон'));
        $hpLeft = (int) ($defender['hp_my'] ?? 0);
        $hpMax = max(1, (int) ($defender['hp_max'] ?? 1));

        if ($isCrit) {
            return sprintf('%s использует %s — КРИТ! %s теряет %d HP (%d/%d).', $attackerName, $moveName, $defenderName, $damage, $hpLeft, $hpMax);
        }

        return sprintf('%s использует %s. %s теряет %d HP (%d/%d).', $attackerName, $moveName, $defenderName, $damage, $hpLeft, $hpMax);
    }

    private function whoActsFirst(array $firstMove, array $secondMove, int $firstSpeed, int $secondSpeed): bool
    {
        $p1 = (int) ($firstMove['priorety'] ?? 0);
        $p2 = (int) ($secondMove['priorety'] ?? 0);
        if ($p1 !== $p2) {
            return $p1 > $p2;
        }
        if ($firstSpeed !== $secondSpeed) {
            return $firstSpeed >= $secondSpeed;
        }
        return random_int(0, 1) === 1;
    }

    private function selectMove(array $moves, int $moveId): array
    {
        foreach ($moves as $move) {
            if ((int) ($move['id'] ?? 0) === $moveId && $moveId > 0) {
                return $move;
            }
        }
        return $this->randomMove($moves);
    }

    private function randomMove(array $moves): array
    {
        if ($moves === []) {
            return [
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'critic' => 6,
                'priorety' => 0,
            ];
        }
        return $moves[array_rand($moves)];
    }

    private function formatPokemon(array $pokemon): array
    {
        return [
            'id' => (int) ($pokemon['id'] ?? 0),
            'baseNum' => (int) ($pokemon['basenum'] ?? 0),
            'name' => strip_tags((string) ($pokemon['names'] ?? 'Pokemon')),
            'level' => (int) ($pokemon['lvl'] ?? 1),
            'hp' => max(0, (int) ($pokemon['hp_my'] ?? 0)),
            'hpMax' => max(1, (int) ($pokemon['hp_max'] ?? 1)),
            'status' => '',
        ];
    }

    private function formatMoves(array $pokemon): array
    {
        $moves = $this->movesForPokemon($pokemon);
        $result = [];
        foreach (array_slice($moves, 0, 4) as $move) {
            $result[] = [
                'id' => (int) ($move['id'] ?? 0),
                'name' => (string) ($move['atac_name'] ?? ('Атака #' . (int) ($move['id'] ?? 0))),
                'power' => (int) ($move['atac_power'] ?? 0),
                'accuracy' => (int) ($move['atac_accuracy'] ?? 0),
            ];
        }
        return $result;
    }

    private function movesForPokemon(array $pokemon): array
    {
        $battlePokemon = (string) ($pokemon['battle_pokemon'] ?? '');
        if (str_starts_with($battlePokemon, 'pvp_') || str_starts_with($battlePokemon, 'user_')) {
            $selected = $this->battles->findSelectedMovesForPokemon((int) ($pokemon['id'] ?? 0));
            if ($selected !== []) {
                return $selected;
            }
        }

        return $this->battles->findAvailableMoves((int) ($pokemon['basenum'] ?? 0), (int) ($pokemon['lvl'] ?? 1));
    }

    private function formatSwitchOptions(int $userId, int $currentPokemonId): array
    {
        $result = [];
        foreach ($this->battles->findUserBattlePokemonOptions($userId) as $pokemon) {
            $id = (int) ($pokemon['id'] ?? 0);
            $hp = (int) ($pokemon['hp_my'] ?? 0);
            if ($id === $currentPokemonId || $hp <= 0) {
                continue;
            }
            $result[] = [
                'id' => $id,
                'name' => strip_tags((string) ($pokemon['names'] ?? ('Pokemon #' . $id))),
                'hp' => $hp,
                'hpMax' => max(1, (int) ($pokemon['hp_max'] ?? 1)),
                'disabled' => false,
            ];
        }
        return $result;
    }

    private function formatLogRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'round' => (int) ($row['raund'] ?? 1),
                'message' => (string) ($row['demage'] ?? ''),
            ];
        }
        return $result;
    }

    private function groupLogByRound(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $round = (int) ($row['round'] ?? 1);
            if (!isset($grouped[$round])) {
                $grouped[$round] = ['round' => $round, 'events' => []];
            }
            $grouped[$round]['events'][] = (string) ($row['message'] ?? '');
        }
        ksort($grouped);
        return array_values($grouped);
    }

    private function fallbackCombatants(int $userId, array $battle, ?array $player, ?array $enemy): array
    {
        // Legacy fallback #1: в старом PvE user_2 хранит id записи pok_pve
        if ($enemy === null) {
            $enemyId = (int) ($battle['user_2'] ?? 0);
            if ($enemyId > 0) {
                $enemy = $this->battles->findPokemon('pve_' . $enemyId);
            }
        }

        // Legacy fallback #1b: битый бой с poke_2 = pve_0 — лечим свежим pok_pve пользователя.
        if ($enemy === null) {
            $latestEnemyId = $this->battles->findLatestPvePokemonIdForUser($userId);
            if ($latestEnemyId > 0) {
                $this->battles->patchBattleEnemy((int) ($battle['id'] ?? 0), $latestEnemyId);
                $enemy = $this->battles->findPokemon('pve_' . $latestEnemyId);
            }
        }

        // Legacy fallback #2: берем первого живого активного покемона игрока
        if ($player === null) {
            $options = $this->battles->findUserBattlePokemonOptions($userId);
            foreach ($options as $opt) {
                $id = (int) ($opt['id'] ?? 0);
                $hp = (int) ($opt['hp_my'] ?? 0);
                if ($id > 0 && $hp > 0) {
                    $player = $this->battles->findPokemon('pvp_' . $id);
                    if ($player !== null) {
                        break;
                    }
                }
            }
        }

        return [$player, $enemy];
    }
}
