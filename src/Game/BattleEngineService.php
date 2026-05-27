<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BossRepository;
use Pokemon8\Repository\RewardRepository;

final class BattleEngineService
{
    private BattleMathService $math;
    private int $lastDamageDealt = 0;

    public function __construct(
        private BattleRepository $battles,
        private ?RewardRepository $rewards = null,
        private ?BossRepository $bosses = null,
    )
    {
        $this->math = new BattleMathService();
    }

    public function state(int $userId): array
    {
        $pvpBattleId = $this->battles->findActivePvpBattleIdForUser($userId);
        if ($pvpBattleId > 0) {
            return $this->pvpState($userId, $pvpBattleId);
        }

        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => true, 'active' => false];
        }

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => true, 'active' => false];
        }

        $this->deleteExpiredBattleEffects($battleId, (int) ($battle['raund'] ?? 1));

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
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
        $this->applyEntryWeatherAbilities($battleId, (int) ($battle['raund'] ?? 1), $player, $enemy);

        $log = $this->battles->getBattleLog((int) $battle['id']);
        $logRows = $this->formatLogRows($log);
        $winner = (int) ($battle['pobeda'] ?? 0);
        $finished = $winner !== 0;
        $result = null;
        if ($finished) {
            $result = $winner === $userId ? 'win' : ($winner === -2 ? 'escape' : 'lose');
        }
        $moves = $this->formatMoves($player);
        $switchOptions = $finished ? [] : $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0));
        $environment = $this->battleEnvironment((int) $battle['id'], (int) ($battle['raund'] ?? 1));

        return $this->decorateBossState([
            'ok' => true,
            'active' => true,
            'finished' => $finished,
            'result' => $result,
            'battleId' => (int) $battle['id'], // backward compatibility
            'round' => (int) ($battle['raund'] ?? 1), // backward compatibility
            'player' => $this->formatPokemon($player), // backward compatibility
            'enemy' => $this->formatPokemon($enemy), // backward compatibility
            'moves' => $moves, // backward compatibility
            'switchOptions' => $switchOptions, // backward compatibility
            'log' => $logRows,
            'weather' => $environment['weather'],
            'terrain' => $environment['terrain'],
            'battle' => [
                'id' => (int) $battle['id'],
                'round' => (int) ($battle['raund'] ?? 1),
                'finished' => $finished,
                'result' => $result,
                'weather' => $environment['weather'],
                'terrain' => $environment['terrain'],
                'player' => $this->formatPokemon($player),
                'enemy' => $this->formatPokemon($enemy),
                'moves' => $moves,
                'switchOptions' => $switchOptions,
                'log' => $logRows,
                'logByRound' => $this->groupLogByRound($logRows),
            ],
        ]);
    }

    public function action(int $userId, string $action, array $payload): array
    {
        if ($this->battles->findActivePvpBattleIdForUser($userId) > 0) {
            return $this->pvpAction($userId, $action, $payload);
        }

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
        if ($this->battles->findActivePvpBattleIdForUser($userId) > 0) {
            $this->battles->acknowledgePvpBattleForUser($userId);
            return ['ok' => true, 'active' => false, 'userId' => $userId];
        }

        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId > 0) {
            $this->bosses?->cleanupSessionForBattle($battleId, $userId);
        }
        $this->battles->cleanupFinishedBattleForUser($userId);
        return ['ok' => true, 'active' => false, 'userId' => $userId];
    }

    public function requestPvp(int $userId, int $targetUserId, int $pokemonId = 0): array
    {
        return $this->battles->requestOrAcceptPvp($userId, $targetUserId, $pokemonId);
    }

    public function forcePvp(int $userId, int $targetUserId, int $pokemonId = 0): array
    {
        return $this->battles->forcePvpAttack($userId, $targetUserId, $pokemonId);
    }

    public function acceptPvp(int $userId, int $requestId, int $pokemonId = 0): array
    {
        return $this->battles->acceptPvpRequest($userId, $requestId, $pokemonId);
    }

    public function declinePvp(int $userId, int $requestId): array
    {
        $ok = $this->battles->declinePvpRequest($userId, $requestId);
        return [
            'ok' => $ok,
            'message' => $ok ? 'Вызов на бой отклонен.' : 'Заявка на бой не найдена.',
        ];
    }

    public function pvpRequests(int $userId): array
    {
        return ['ok' => true, 'requests' => $this->battles->incomingPvpRequests($userId)];
    }

    public function pvpPokemonOptions(int $userId): array
    {
        return [
            'ok' => true,
            'pokemon' => array_map(
                fn (array $pokemon): array => $this->formatPvpOption($pokemon),
                $this->battles->findUserBattlePokemonOptions($userId)
            ),
        ];
    }

    public function battleHistory(int $userId): array
    {
        return [
            'ok' => true,
            'history' => $this->battles->battleHistoryForUser($userId),
        ];
    }

    public function pvpStatus(int $userId, int $targetUserId): array
    {
        return [
            'ok' => true,
            'status' => $this->battles->pvpRequestStatus($userId, $targetUserId),
            'permission' => $this->battles->pvpAttackPermission($userId, $targetUserId),
        ];
    }

    private function formatPvpOption(array $pokemon): array
    {
        $baseNum = (int) ($pokemon['basenum'] ?? 0);
        $code = str_pad((string) max(0, PokemonFormCatalog::displayBaseId($baseNum)), 3, '0', STR_PAD_LEFT);

        return [
            'id' => (int) ($pokemon['id'] ?? 0),
            'name' => trim(strip_tags((string) ($pokemon['names'] ?? ''))) ?: ('Pokemon #' . $baseNum),
            'baseNum' => $baseNum,
            'formId' => $baseNum,
            'dexNumber' => PokemonFormCatalog::displayBaseId($baseNum),
            'displayBaseNum' => PokemonFormCatalog::displayBaseId($baseNum),
            'formKey' => PokemonFormCatalog::formKey($baseNum, (string) ($pokemon['names'] ?? '')),
            'isForm' => PokemonFormCatalog::isForm($baseNum),
            'code' => $code,
            'level' => (int) ($pokemon['lvl'] ?? 0),
            'hp' => (int) ($pokemon['hp_my'] ?? 0),
            'hpMax' => (int) ($pokemon['hp_max'] ?? 0),
            'starter' => (int) ($pokemon['startepoke'] ?? 0) === 1,
        ];
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
        $this->deleteExpiredBattleEffects($battleId, (int) ($battle['raund'] ?? 1));

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }
        $this->applyEntryWeatherAbilities($battleId, (int) ($battle['raund'] ?? 1), $player, $enemy);

        $playerMove = $this->selectMove($this->movesForPokemon($player), $moveId);
        $enemyMove = $this->enemyMoveForBattle((int) ($battle['id'] ?? 0), $enemy);
        $usesSelectedMove = isset($playerMove['pp_min'], $playerMove['pp_max']);
        if ($usesSelectedMove && (int) ($playerMove['pp_min'] ?? 0) <= 0) {
            return [
                'ok' => false,
                'active' => true,
                'message' => 'У этой атаки закончились PP.',
                'battle' => $this->state($userId)['battle'] ?? [],
            ];
        }
        if ($usesSelectedMove) {
            $this->battles->decrementSelectedMovePp((int) ($player['id'] ?? 0), (int) ($playerMove['id'] ?? 0));
        }

        $messages = [];
        $playerFirst = $this->whoActsFirstForPokemon($battleId, $player, $enemy, $playerMove, $enemyMove);

        if ($playerFirst) {
            $messages[] = $this->applyMove((int) $battle['id'], (int) ($battle['raund'] ?? 1), $player, $enemy, $playerMove);
            if ((int) ($player['hp_my'] ?? 0) > 0 && (int) ($enemy['hp_my'] ?? 0) > 0) {
                $messages[] = $this->applyMove((int) $battle['id'], (int) ($battle['raund'] ?? 1), $enemy, $player, $enemyMove);
            }
        } else {
            $messages[] = $this->applyMove((int) $battle['id'], (int) ($battle['raund'] ?? 1), $enemy, $player, $enemyMove);
            if ((int) ($enemy['hp_my'] ?? 0) > 0 && (int) ($player['hp_my'] ?? 0) > 0) {
                $messages[] = $this->applyMove((int) $battle['id'], (int) ($battle['raund'] ?? 1), $player, $enemy, $playerMove);
            }
        }

        $this->battles->updatePokemonHp((string) $battle['poke_1'], (int) $player['hp_my']);
        $this->battles->updatePokemonHp((string) $battle['poke_2'], (int) $enemy['hp_my']);

        foreach ($messages as $text) {
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $text);
        }

        $finished = false;
        $result = null;
        $rewards = ['coins' => 0, 'exp' => 0, 'drops' => []];
        $currentRound = (int) ($battle['raund'] ?? 1);
        $finalLogRows = null;
        if ((int) $enemy['hp_my'] <= 0) {
            $bossAdvance = $this->bosses?->continueAfterEnemyFaint($userId, $battle, $enemy, $currentRound);
            if (is_array($bossAdvance) && !empty($bossAdvance['continued'])) {
                $this->battles->incrementRoundAndResetActions((int) $battle['id']);
                $state = $this->state($userId);
                $state['ok'] = true;
                $state['messages'] = array_values(array_filter([...$messages, (string) ($bossAdvance['message'] ?? '')]));
                $state['finished'] = false;
                $state['result'] = null;
                $state['rewards'] = ['coins' => 0, 'exp' => 0, 'drops' => []];
                return $state;
            }

            $finished = true;
            $result = 'win';
            $enemyLvl = max(1, (int) ($enemy['lvl'] ?? 1));
            $coinMultiplier = $this->rewards?->activeMultiplier($userId, 'coins', 'pve') ?? 1.0;
            $expMultiplier = $this->rewards?->activeMultiplier($userId, 'exp', 'pve') ?? 1.0;
            $rewards = [
                'coins' => (int) round(max(5, $enemyLvl * 3) * $coinMultiplier),
                'exp' => (int) round(max(10, $enemyLvl * 12) * $expMultiplier),
            ];
            $this->battles->addCoins($userId, $rewards['coins']);
            $happinessMultiplier = $this->rewards?->activeMultiplier($userId, 'happiness', 'pve') ?? 1.0;
            $happinessGain = max(1, (int) round($happinessMultiplier));
            $effort = $this->battles->addExperienceAndEffort(
                $userId,
                (int) ($player['id'] ?? 0),
                $rewards['exp'],
                4
            );
            $this->battles->addPokemonHappiness($userId, (int) ($player['id'] ?? 0), $happinessGain);
            $rewards['happiness'] = $happinessGain;
            $bossWon = is_array($bossAdvance ?? null) && !empty($bossAdvance['won']);
            $dropMultiplier = $this->rewards?->activeMultiplier($userId, 'drop', 'pve') ?? 1.0;
            $drops = $bossWon ? [] : $this->battles->rollAdminDropRewards($userId, $enemy, $dropMultiplier);
            if ($bossWon) {
                foreach (($bossAdvance['rewards']['drops'] ?? []) as $drop) {
                    $drops[] = $drop;
                }
            }
            $rewards['drops'] = $drops;
            foreach ($drops as $drop) {
                $dropMessage = sprintf(
                    'Получен предмет: %s x%d.',
                    (string) ($drop['name'] ?? 'Предмет'),
                    (int) ($drop['count'] ?? 1)
                );
                $messages[] = $dropMessage;
                $this->battles->insertBattleLog((int) $battle['id'], $currentRound, $dropMessage);
            }
            if ($this->rewards !== null) {
                $rewardParts = [];
                if ((int) ($rewards['coins'] ?? 0) > 0) {
                    $rewardParts[] = number_format((int) $rewards['coins'], 0, ',', ' ') . ' монет';
                }
                if ((int) ($rewards['exp'] ?? 0) > 0) {
                    $rewardParts[] = number_format((int) $rewards['exp'], 0, ',', ' ') . ' опыта';
                }
                if ((int) ($rewards['happiness'] ?? 0) > 0) {
                    $rewardParts[] = '+' . (int) $rewards['happiness'] . ' счастья';
                }
                foreach ($drops as $drop) {
                    $rewardParts[] = (string) ($drop['name'] ?? 'Предмет') . ' x' . (int) ($drop['count'] ?? 1);
                }
                if ($rewardParts !== []) {
                    $this->rewards->notify($userId, 'Награда за бой', 'Получено: ' . implode(', ', $rewardParts) . '.', 'reward', [
                        'battle_id' => (int) $battle['id'],
                        'result' => 'win',
                        'rewards' => $rewards,
                    ]);
                }
            }
            if (($effort['exp'] ?? 0) > 0) {
                $rewardMessage = sprintf(
                    '%s получает %d опыта и %d EV%s.',
                    strip_tags((string) ($player['names'] ?? 'Покемон')),
                    (int) $effort['exp'],
                    (int) $effort['ev'],
                    (int) ($effort['levelUps'] ?? 0) > 0 ? ' — уровень ' . (int) $effort['level'] : ''
                );
                if ($happinessGain > 0) {
                    $rewardMessage .= ' Счастье +' . $happinessGain . '.';
                }
                if (is_array($effort['evolution'] ?? null)) {
                    $rewardMessage .= sprintf(
                        ' Эволюция: %s → %s.',
                        (string) $effort['evolution']['fromName'],
                        (string) $effort['evolution']['toName']
                    );
                }
                $messages[] = $rewardMessage;
                $this->battles->insertBattleLog((int) $battle['id'], $currentRound, $rewardMessage);
            }
            $finalLogRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            $this->battles->finishBattle((int) $battle['id'], $userId, $userId);
        } elseif ((int) $player['hp_my'] <= 0) {
            $bossPlayerAdvance = $this->bosses?->continueAfterPlayerFaint($userId, $battle, $currentRound);
            if (is_array($bossPlayerAdvance) && !empty($bossPlayerAdvance['continued'])) {
                $this->battles->incrementRoundAndResetActions((int) $battle['id']);
                $state = $this->state($userId);
                $state['ok'] = true;
                $state['messages'] = array_values(array_filter([...$messages, (string) ($bossPlayerAdvance['message'] ?? '')]));
                $state['finished'] = false;
                $state['result'] = null;
                $state['rewards'] = ['coins' => 0, 'exp' => 0, 'drops' => []];
                return $state;
            }

            $finished = true;
            $result = 'lose';
            $finalLogRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            $this->battles->finishBattle((int) $battle['id'], $userId, -1);
        } else {
            $this->battles->incrementRoundAndResetActions((int) $battle['id']);
        }

        if ($finished) {
            $logRows = $finalLogRows ?? [];
            $environment = $this->battleEnvironment((int) $battle['id'], $currentRound);
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
                    'weather' => $environment['weather'],
                    'terrain' => $environment['terrain'],
                    'player' => $this->formatPokemon($player),
                    'enemy' => $this->formatPokemon($enemy),
                    'moves' => $this->formatMoves($player),
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
        $state['rewards'] = ['coins' => 0, 'exp' => 0, 'drops' => []];
        $state['battle']['logByRound'] = $this->groupLogByRound($state['log'] ?? []);

        return $state;
    }

    private function pvpState(int $userId, int $battleId): array
    {
        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => true, 'active' => false];
        }

        $this->deleteExpiredBattleEffects($battleId, (int) ($battle['raund'] ?? 1));

        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        $opponentUserId = $side === 1 ? (int) ($battle['user_2'] ?? 0) : (int) ($battle['user_1'] ?? 0);
        $player = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_' . ($side === 1 ? 2 : 1)] ?? ''));
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);

        if ($player === null || $enemy === null) {
            return [
                'ok' => false,
                'active' => true,
                'message' => 'Состояние PvP боя повреждено.',
                'debug' => [
                    'battle_id' => (int) ($battle['id'] ?? 0),
                    'poke_1' => (string) ($battle['poke_1'] ?? ''),
                    'poke_2' => (string) ($battle['poke_2'] ?? ''),
                    'side' => $side,
                ],
            ];
        }
        $winner = (int) ($battle['pobeda'] ?? 0);
        $finished = $winner !== 0;
        if (!$finished) {
            $this->applyEntryWeatherAbilities($battleId, (int) ($battle['raund'] ?? 1), $player, $enemy);
        }
        $ownAction = (int) ($battle['attac_' . $side] ?? 0);
        $enemyAction = (int) ($battle['attac_' . ($side === 1 ? 2 : 1)] ?? 0);
        $ownActionSet = $ownAction !== 0;
        $enemyActionSet = $enemyAction !== 0;
        $opponentLogin = $this->battles->findUserLoginById($opponentUserId);
        $logRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
        $playerData = $this->formatPokemon($player);
        $enemyData = $this->formatPokemon($enemy);
        $environment = $this->battleEnvironment((int) $battle['id'], (int) ($battle['raund'] ?? 1));
        $enemyData['trainer'] = [
            'id' => $opponentUserId,
            'login' => $opponentLogin,
        ];

        return [
            'ok' => true,
            'active' => !$finished,
            'finished' => $finished,
            'result' => $finished ? ($winner === $userId ? 'win' : 'lose') : null,
            'waitingForOpponent' => !$finished && $ownActionSet && !$enemyActionSet,
            'canAct' => !$finished && !$ownActionSet,
            'battleId' => (int) $battle['id'],
            'round' => (int) ($battle['raund'] ?? 1),
            'player' => $playerData,
            'enemy' => $enemyData,
            'moves' => $this->formatMoves($player),
            'switchOptions' => $finished ? [] : $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)),
            'log' => $logRows,
            'weather' => $environment['weather'],
            'terrain' => $environment['terrain'],
            'battle' => [
                'id' => (int) $battle['id'],
                'mode' => 'pvp',
                'title' => 'PvP бой против ' . $opponentLogin,
                'round' => (int) ($battle['raund'] ?? 1),
                'finished' => $finished,
                'result' => $finished ? ($winner === $userId ? 'win' : 'lose') : null,
                'weather' => $environment['weather'],
                'terrain' => $environment['terrain'],
                'waitingForOpponent' => !$finished && $ownActionSet && !$enemyActionSet,
                'canAct' => !$finished && !$ownActionSet,
                'player' => $playerData,
                'enemy' => $enemyData,
                'moves' => $this->formatMoves($player),
                'switchOptions' => $finished ? [] : $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)),
                'log' => $logRows,
                'logByRound' => $this->groupLogByRound($logRows),
            ],
        ];
    }

    private function pvpAction(int $userId, string $action, array $payload): array
    {
        return match ($action) {
            'attack' => $this->pvpAttack($userId, (int) ($payload['move_id'] ?? 0)),
            'switch' => $this->pvpSwitchPokemon($userId, (int) ($payload['pokemon_id'] ?? 0)),
            'item' => $this->pvpUseItem($userId, (int) ($payload['item_user_id'] ?? 0)),
            'ball' => [
                'ok' => false,
                'active' => true,
                'message' => 'Покеболы нельзя использовать в PvP-бою.',
                'battle' => $this->state($userId)['battle'] ?? [],
            ],
            'escape' => $this->pvpEscape($userId),
            default => [
                'ok' => false,
                'active' => true,
                'message' => 'В PvP сейчас доступны атака, смена покемона и сдача боя.',
                'battle' => $this->state($userId)['battle'] ?? [],
            ],
        };
    }

    private function pvpAttack(int $userId, int $moveId): array
    {
        $battleId = $this->battles->findActivePvpBattleIdForUser($userId);
        $battle = $battleId > 0 ? $this->battles->findPvpBattleForUser($userId, $battleId) : null;
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'PvP бой не найден.'];
        }
        if ((int) ($battle['pobeda'] ?? 0) !== 0) {
            return $this->pvpState($userId, $battleId);
        }

        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        if ((int) ($battle['attac_' . $side] ?? 0) !== 0) {
            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = ['Ход уже выбран. Ждем соперника.'];
            return $state;
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
        if ($player === null) {
            return ['ok' => false, 'active' => true, 'message' => 'Активный покемон не найден.'];
        }

        $playerMove = $this->findMoveById($this->movesForPokemon($player), $moveId);
        if ($playerMove === null) {
            return ['ok' => false, 'active' => true, 'message' => 'Эта атака недоступна.', 'battle' => $this->state($userId)['battle'] ?? []];
        }
        if (isset($playerMove['pp_min'], $playerMove['pp_max']) && (int) ($playerMove['pp_min'] ?? 0) <= 0) {
            return ['ok' => false, 'active' => true, 'message' => 'У этой атаки закончились PP.', 'battle' => $this->state($userId)['battle'] ?? []];
        }

        if (isset($playerMove['pp_min'], $playerMove['pp_max'])) {
            $this->battles->decrementSelectedMovePp((int) ($player['id'] ?? 0), (int) ($playerMove['id'] ?? 0));
        }
        $this->battles->setPvpBattleAction($battleId, $side, (int) ($playerMove['id'] ?? 0));

        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'PvP бой не найден.'];
        }
        if ((int) ($battle['attac_1'] ?? 0) === 0 || (int) ($battle['attac_2'] ?? 0) === 0) {
            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = ['Ход принят. Ждем соперника.'];
            return $state;
        }

        $this->resolvePvpRound($battle);
        return $this->pvpState($userId, $battleId);
    }

    private function resolvePvpRound(array $battle): void
    {
        $battleId = (int) ($battle['id'] ?? 0);
        $round = (int) ($battle['raund'] ?? 1);
        $first = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $second = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        if ($first === null || $second === null) {
            return;
        }
        $this->attachBattleStatuses($battleId, $first, $second);
        $this->applyEntryWeatherAbilities($battleId, $round, $first, $second);

        $firstAction = (int) ($battle['attac_1'] ?? 0);
        $secondAction = (int) ($battle['attac_2'] ?? 0);
        $firstMove = $firstAction > 0 ? ($this->findMoveById($this->movesForPokemon($first), $firstAction) ?? $this->randomMove($this->movesForPokemon($first))) : null;
        $secondMove = $secondAction > 0 ? ($this->findMoveById($this->movesForPokemon($second), $secondAction) ?? $this->randomMove($this->movesForPokemon($second))) : null;
        $firstActs = $firstMove !== null && $secondMove !== null
            ? $this->whoActsFirstForPokemon($battleId, $first, $second, $firstMove, $secondMove)
            : $firstMove !== null;

        $messages = [];
        if ($firstMove !== null && $secondMove !== null && $firstActs) {
            $messages[] = $this->applyMove($battleId, $round, $first, $second, $firstMove);
            if ((int) ($first['hp_my'] ?? 0) > 0 && (int) ($second['hp_my'] ?? 0) > 0) {
                $messages[] = $this->applyMove($battleId, $round, $second, $first, $secondMove);
            }
        } elseif ($firstMove !== null && $secondMove !== null) {
            $messages[] = $this->applyMove($battleId, $round, $second, $first, $secondMove);
            if ((int) ($first['hp_my'] ?? 0) > 0 && (int) ($second['hp_my'] ?? 0) > 0) {
                $messages[] = $this->applyMove($battleId, $round, $first, $second, $firstMove);
            }
        } elseif ($firstMove !== null) {
            $messages[] = $this->applyMove($battleId, $round, $first, $second, $firstMove);
        } elseif ($secondMove !== null) {
            $messages[] = $this->applyMove($battleId, $round, $second, $first, $secondMove);
        }

        $this->battles->updatePokemonHp((string) ($battle['poke_1'] ?? ''), (int) ($first['hp_my'] ?? 0));
        $this->battles->updatePokemonHp((string) ($battle['poke_2'] ?? ''), (int) ($second['hp_my'] ?? 0));
        foreach ($messages as $message) {
            $this->battles->insertBattleLog($battleId, $round, $message);
        }

        if ((int) ($first['hp_my'] ?? 0) <= 0) {
            $this->battles->finishPvpBattle($battleId, (int) ($battle['user_2'] ?? 0));
            $this->battles->insertBattleLog($battleId, $round, 'Победа тренера ' . $this->battles->findUserLoginById((int) ($battle['user_2'] ?? 0)) . '.');
            return;
        }
        if ((int) ($second['hp_my'] ?? 0) <= 0) {
            $this->battles->finishPvpBattle($battleId, (int) ($battle['user_1'] ?? 0));
            $this->battles->insertBattleLog($battleId, $round, 'Победа тренера ' . $this->battles->findUserLoginById((int) ($battle['user_1'] ?? 0)) . '.');
            return;
        }

        $this->battles->resetPvpBattleActionsAndIncrementRound($battleId);
    }

    private function pvpSwitchPokemon(int $userId, int $pokemonId): array
    {
        $battleId = $this->battles->findActivePvpBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => false, 'active' => false, 'message' => 'PvP бой не найден.'];
        }
        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'PvP бой не найден.'];
        }
        if ((int) ($battle['pobeda'] ?? 0) !== 0) {
            return $this->pvpState($userId, $battleId);
        }
        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        if ((int) ($battle['attac_' . $side] ?? 0) !== 0) {
            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = ['Ход уже выбран. Ждем соперника.'];
            return $state;
        }
        $activePokemon = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
        if ($activePokemon !== null && $this->isSwitchBlocked($battleId, $activePokemon)) {
            return ['ok' => false, 'active' => true, 'message' => 'Покемон не может смениться: он удержан ловушкой.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }
        if ($activePokemon !== null) {
            $this->battles->clearSwitchVolatiles($battleId, (string) ($activePokemon['battle_pokemon'] ?? ''));
        }
        if (!$this->battles->switchPvpPokemon($battleId, $userId, $pokemonId)) {
            return ['ok' => false, 'active' => true, 'message' => 'Смена покемона недоступна.', 'battle' => $this->state($userId)['battle'] ?? []];
        }

        $this->battles->setPvpBattleAction($battleId, $side, -$pokemonId);
        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle !== null) {
            $newPokemon = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
            if ($newPokemon !== null) {
                $this->applySingleEntryWeatherAbility($battleId, (int) ($battle['raund'] ?? 1), $newPokemon);
            }
        }
        $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $this->battles->findUserLoginById($userId) . ' меняет покемона.');
        if ($battle !== null && (int) ($battle['attac_1'] ?? 0) !== 0 && (int) ($battle['attac_2'] ?? 0) !== 0) {
            $this->resolvePvpRound($battle);
        }
        $state = $this->pvpState($userId, $battleId);
        $state['messages'] = ['Покемон успешно заменен.'];
        return $state;
    }

    private function pvpUseItem(int $userId, int $itemUserId): array
    {
        $battleId = $this->battles->findActivePvpBattleIdForUser($userId);
        $battle = $battleId > 0 ? $this->battles->findPvpBattleForUser($userId, $battleId) : null;
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'PvP бой не найден.'];
        }
        if ((int) ($battle['pobeda'] ?? 0) !== 0) {
            return $this->pvpState($userId, $battleId);
        }

        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        if ((int) ($battle['attac_' . $side] ?? 0) !== 0) {
            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = ['Ход уже выбран. Ждем соперника.'];
            return $state;
        }

        $item = $this->battles->findBattleInventoryItem($userId, $itemUserId);
        if ($item === null || (int) ($item['battleuse'] ?? 0) !== 1) {
            return ['ok' => false, 'active' => true, 'message' => 'Этот предмет нельзя использовать в PvP.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }
        if ((int) ($item['item_id'] ?? 0) === 3 || stripos((string) (($item['name'] ?? '') . ' ' . ($item['tittle'] ?? '')), 'ball') !== false || str_contains((string) (($item['name'] ?? '') . ' ' . ($item['tittle'] ?? '')), 'бол')) {
            return ['ok' => false, 'active' => true, 'message' => 'Покеболы в PvP недоступны.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
        if ($player === null) {
            return ['ok' => false, 'active' => true, 'message' => 'Активный покемон не найден.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }

        $itemId = (int) ($item['item_id'] ?? 0);
        $playerName = strip_tags((string) ($player['names'] ?? 'Покемон'));
        if ($itemId === 217) {
            $this->battles->restorePokemonMovePp((int) ($player['id'] ?? 0));
            $message = sprintf('%s использует витамин PP на %s.', $this->battles->findUserLoginById($userId), $playerName);
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
            $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $message);
            $this->battles->setPvpBattleAction($battleId, $side, -900000 - $itemUserId);

            $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
            if ($battle !== null && (int) ($battle['attac_1'] ?? 0) !== 0 && (int) ($battle['attac_2'] ?? 0) !== 0) {
                $this->resolvePvpRound($battle);
            }

            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = [$message];
            return $state;
        }
        if ($itemId === 861) {
            $maxHp = max(1, (int) ($player['hp_max'] ?? 1));
            $beforeHp = max(0, (int) ($player['hp_my'] ?? 0));
            if ($beforeHp >= $maxHp) {
                return ['ok' => false, 'active' => true, 'message' => 'Покемон уже полностью здоров. Предмет не списан.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
            }
            $newHp = min($maxHp, $beforeHp + max(1, (int) floor($maxHp / 2)));
            $this->battles->updatePokemonHp((string) ($player['battle_pokemon'] ?? $battle['poke_' . $side]), $newHp);
            $message = sprintf('%s использует кекс с ягодами на %s: +%d HP.', $this->battles->findUserLoginById($userId), $playerName, $newHp - $beforeHp);
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
            $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $message);
            $this->battles->setPvpBattleAction($battleId, $side, -900000 - $itemUserId);

            $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
            if ($battle !== null && (int) ($battle['attac_1'] ?? 0) !== 0 && (int) ($battle['attac_2'] ?? 0) !== 0) {
                $this->resolvePvpRound($battle);
            }

            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = [$message];
            return $state;
        }

        $genericItemUse = $this->applyGenericBattleItem($battleId, $userId, $itemUserId, $item, $player);
        if ($genericItemUse !== null) {
            if (empty($genericItemUse['ok'])) {
                return [
                    'ok' => false,
                    'active' => true,
                    'message' => (string) ($genericItemUse['message'] ?? 'Предмет не сработал. Предмет не списан.'),
                    'battle' => $this->pvpState($userId, $battleId)['battle'] ?? [],
                ];
            }

            $message = (string) ($genericItemUse['message'] ?? '');
            $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $message);
            $this->battles->setPvpBattleAction($battleId, $side, -900000 - $itemUserId);

            $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
            if ($battle !== null && (int) ($battle['attac_1'] ?? 0) !== 0 && (int) ($battle['attac_2'] ?? 0) !== 0) {
                $this->resolvePvpRound($battle);
            }

            $state = $this->pvpState($userId, $battleId);
            $state['messages'] = [$message];
            return $state;
        }

        if ($itemId !== 15) {
            return ['ok' => false, 'active' => true, 'message' => 'В beta PvP пока разрешены только боевые предметы со штатной логикой. Этот предмет не списан.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }

        $removed = $this->battles->clearBattleStatus((string) ($player['battle_pokemon'] ?? $battle['poke_' . $side]), 2);
        if ($removed <= 0) {
            return ['ok' => false, 'active' => true, 'message' => 'Энергетик сейчас не нужен: покемон не спит.', 'battle' => $this->pvpState($userId, $battleId)['battle'] ?? []];
        }

        $message = sprintf('%s использует Энергетик на %s.', $this->battles->findUserLoginById($userId), $playerName);
        $this->battles->decrementInventoryItemRow($userId, $itemUserId);
        $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $message);
        $this->battles->setPvpBattleAction($battleId, $side, -900000 - $itemUserId);

        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle !== null && (int) ($battle['attac_1'] ?? 0) !== 0 && (int) ($battle['attac_2'] ?? 0) !== 0) {
            $this->resolvePvpRound($battle);
        }

        $state = $this->pvpState($userId, $battleId);
        $state['messages'] = [$message];
        return $state;
    }

    private function pvpEscape(int $userId): array
    {
        $battleId = $this->battles->findActivePvpBattleIdForUser($userId);
        $battle = $battleId > 0 ? $this->battles->findPvpBattleForUser($userId, $battleId) : null;
        if ($battle === null) {
            return ['ok' => true, 'active' => false];
        }
        if ((int) ($battle['pobeda'] ?? 0) !== 0) {
            return $this->pvpState($userId, $battleId);
        }

        $winner = (int) ($battle['user_1'] ?? 0) === $userId ? (int) ($battle['user_2'] ?? 0) : (int) ($battle['user_1'] ?? 0);
        $this->battles->insertBattleLog($battleId, (int) ($battle['raund'] ?? 1), $this->battles->findUserLoginById($userId) . ' сдается.');
        $this->battles->finishPvpBattle($battleId, $winner);

        return $this->pvpState($userId, $battleId);
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

        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle !== null) {
            $activePokemon = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
            if ($activePokemon !== null && $this->isSwitchBlocked($battleId, $activePokemon)) {
                return ['ok' => false, 'active' => true, 'message' => 'Покемон не может смениться: он удержан ловушкой.'];
            }
            if ($activePokemon !== null) {
                $this->battles->clearSwitchVolatiles($battleId, (string) ($activePokemon['battle_pokemon'] ?? ''));
            }
        }

        if (!$this->battles->switchPlayerPokemon($battleId, $userId, $pokemonId)) {
            return ['ok' => false, 'active' => true, 'message' => 'Смена покемона недоступна.'];
        }

        $hazardMessages = [];
        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle !== null) {
            $pokemon = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ('pvp_' . $pokemonId)));
            if ($pokemon !== null) {
                $this->applySingleEntryWeatherAbility($battleId, (int) ($battle['raund'] ?? 1), $pokemon);
                $hazardMessages = $this->applySwitchHazards($battleId, (int) ($battle['raund'] ?? 1), $pokemon);
            }
        }

        return $this->resolvePveEnemyResponseAfterPlayerAction(
            $userId,
            $battleId,
            array_merge(['Покемон успешно заменен.'], $hazardMessages),
            false
        );
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
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }
        $this->applyEntryWeatherAbilities((int) ($battle['id'] ?? 0), (int) ($battle['raund'] ?? 1), $player, $enemy);

        $itemId = (int) ($item['item_id'] ?? 0);
        $playerName = strip_tags((string) ($player['names'] ?? 'Покемон'));

        if ($itemId === 217) {
            $this->battles->restorePokemonMovePp((int) ($player['id'] ?? 0));
            $message = sprintf('Игрок #%d использует витамин PP на %s.', $userId, $playerName);
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $message);
            return $this->resolvePveEnemyResponseAfterPlayerAction($userId, $battleId, [$message], true);
        }

        if ($itemId === 861) {
            $maxHp = max(1, (int) ($player['hp_max'] ?? 1));
            $beforeHp = max(0, (int) ($player['hp_my'] ?? 0));
            if ($beforeHp >= $maxHp) {
                return ['ok' => false, 'active' => true, 'message' => 'Покемон уже полностью здоров. Предмет не списан.'];
            }
            $newHp = min($maxHp, $beforeHp + max(1, (int) floor($maxHp / 2)));
            $this->battles->updatePokemonHp((string) ($player['battle_pokemon'] ?? $battle['poke_1']), $newHp);
            $message = sprintf('Игрок #%d использует кекс с ягодами на %s: +%d HP.', $userId, $playerName, $newHp - $beforeHp);
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $message);
            return $this->resolvePveEnemyResponseAfterPlayerAction($userId, $battleId, [$message], true);
        }

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
            return $this->resolvePveEnemyResponseAfterPlayerAction($userId, $battleId, [$message], true);
        }

        $genericItemUse = $this->applyGenericBattleItem((int) $battle['id'], $userId, $itemUserId, $item, $player);
        if ($genericItemUse !== null) {
            if (empty($genericItemUse['ok'])) {
                return ['ok' => false, 'active' => true, 'message' => (string) ($genericItemUse['message'] ?? 'Предмет не сработал. Предмет не списан.')];
            }
            $message = (string) ($genericItemUse['message'] ?? '');
            $this->battles->insertBattleLog((int) $battle['id'], (int) ($battle['raund'] ?? 1), $message);
            return $this->resolvePveEnemyResponseAfterPlayerAction($userId, $battleId, [$message], true);
        }

        return ['ok' => false, 'active' => true, 'message' => 'Этот предмет пока нельзя использовать в бою.'];
    }

    /** @return array{ok:bool,message:string}|null */
    private function applyGenericBattleItem(int $battleId, int $userId, int $itemUserId, array $item, array &$player): ?array
    {
        $effect = $this->battleItemEffect($item);
        if ($effect === null) {
            return null;
        }

        $battlePokemon = (string) ($player['battle_pokemon'] ?? '');
        $playerName = strip_tags((string) ($player['names'] ?? 'Покемон'));
        $itemName = strip_tags((string) ($item['name'] ?? 'Предмет'));
        $changed = false;
        $parts = [];

        $healAmount = (int) ($effect['heal'] ?? 0);
        $healPercent = (int) ($effect['healPercent'] ?? 0);
        if ($healAmount > 0 || $healPercent > 0) {
            $maxHp = max(1, (int) ($player['hp_max'] ?? 1));
            $beforeHp = max(0, (int) ($player['hp_my'] ?? 0));
            $amount = $healPercent > 0 ? max(1, (int) floor($maxHp * min(100, $healPercent) / 100)) : $healAmount;
            $healed = $this->healPokemon($player, $amount);
            if ($healed > 0) {
                $this->battles->updatePokemonHp($battlePokemon, (int) ($player['hp_my'] ?? $beforeHp));
                $changed = true;
                $parts[] = '+' . $healed . ' HP';
            }
        }

        $statusLabels = [];
        foreach (($effect['statuses'] ?? []) as $statusKey) {
            $statusId = $this->battleStatusIdByKey((string) $statusKey);
            if ($statusId <= 0) {
                continue;
            }
            $removed = $this->battles->clearBattleStatus($battlePokemon, $statusId);
            if ($statusId === 1) {
                $removed += $this->battles->clearBattleVolatile($battleId, $battlePokemon, 'badly_poisoned');
            }
            if ($removed > 0) {
                $changed = true;
                $statusLabels[] = $this->statusName($statusId);
            }
        }

        foreach (($effect['volatiles'] ?? []) as $volatileKey) {
            $removed = $this->battles->clearBattleVolatile($battleId, $battlePokemon, (string) $volatileKey);
            if ($removed > 0) {
                $changed = true;
                $statusLabels[] = $this->volatileLabel((string) $volatileKey);
            }
        }

        if ($statusLabels !== []) {
            $parts[] = 'снято: ' . implode(', ', array_unique($statusLabels));
        }

        if (!$changed) {
            return [
                'ok' => false,
                'message' => sprintf('%s сейчас не нужен: у %s нет подходящего эффекта или HP уже полные.', $itemName, $playerName),
            ];
        }

        $this->battles->decrementInventoryItemRow($userId, $itemUserId);

        return [
            'ok' => true,
            'message' => sprintf('Игрок #%d использует %s на %s: %s.', $userId, $itemName, $playerName, implode('; ', $parts)),
        ];
    }

    /** @return array{heal?:int,healPercent?:int,statuses?:list<string>,volatiles?:list<string>}|null */
    private function battleItemEffect(array $item): ?array
    {
        $itemId = (int) ($item['item_id'] ?? 0);
        $text = mb_strtolower((string) (($item['name'] ?? '') . ' ' . ($item['tittle'] ?? '') . ' ' . ($item['dopolnen'] ?? '')), 'UTF-8');
        $text = str_replace('ё', 'е', $text);
        $rawEffect = mb_strtolower(trim((string) ($item['dopolnen'] ?? '')), 'UTF-8');
        $effect = [];

        $addStatuses = static function (array $statuses) use (&$effect): void {
            $effect['statuses'] = array_values(array_unique([
                ...($effect['statuses'] ?? []),
                ...array_values(array_filter($statuses, static fn ($value): bool => trim((string) $value) !== '')),
            ]));
        };
        $addVolatiles = static function (array $volatiles) use (&$effect): void {
            $effect['volatiles'] = array_values(array_unique([
                ...($effect['volatiles'] ?? []),
                ...array_values(array_filter($volatiles, static fn ($value): bool => trim((string) $value) !== '')),
            ]));
        };

        if (preg_match('/(?:battle_)?heal_percent:(\d{1,3})/', $rawEffect, $m) === 1) {
            $effect['healPercent'] = max(1, min(100, (int) $m[1]));
        }
        if (preg_match('/(?:battle_)?heal_hp:(\d{1,5})/', $rawEffect, $m) === 1 || preg_match('/(?:battle_)?heal:(\d{1,5})/', $rawEffect, $m) === 1) {
            $effect['heal'] = max(1, (int) $m[1]);
        }
        if (preg_match('/(?:battle_)?cure:([a-z0-9_, -]+)/', $rawEffect, $m) === 1) {
            [$statuses, $volatiles] = $this->battleCureTargets((string) $m[1]);
            $addStatuses($statuses);
            $addVolatiles($volatiles);
        }

        if (str_contains($rawEffect, 'full_restore') || str_contains($text, 'full restore') || str_contains($text, 'полное восстанов')) {
            $effect['healPercent'] = 100;
            [$statuses, $volatiles] = $this->battleCureTargets('all');
            $addStatuses($statuses);
            $addVolatiles($volatiles);
        }
        if (str_contains($rawEffect, 'full_heal') || str_contains($text, 'полное исцел') || str_contains($text, 'full heal')) {
            [$statuses, $volatiles] = $this->battleCureTargets('all');
            $addStatuses($statuses);
            $addVolatiles($volatiles);
        }
        if (str_contains($text, 'антидот') || str_contains($text, 'противояд') || str_contains($text, 'отрав')) {
            $addStatuses(['poison']);
            $addVolatiles(['badly_poisoned']);
        }
        if (str_contains($text, 'антиожог') || str_contains($text, 'ожог')) {
            $addStatuses(['burn']);
        }
        if ($itemId === 15 || str_contains($text, 'энергетик') || str_contains($text, 'сон') || str_contains($text, 'пробужд')) {
            $addStatuses(['sleep']);
        }
        if (str_contains($text, 'паралич') || str_contains($text, 'paraly')) {
            $addStatuses(['paralyze']);
        }
        if (str_contains($text, 'замор') || str_contains($text, 'размороз')) {
            $addStatuses(['freeze']);
        }
        if (str_contains($text, 'спутан') || str_contains($text, 'confus')) {
            $addStatuses(['confuse']);
            $addVolatiles(['confusion']);
        }
        if (str_contains($text, 'max potion') || str_contains($text, 'максимальн') || str_contains($text, 'полное зелье')) {
            $effect['healPercent'] = max((int) ($effect['healPercent'] ?? 0), 100);
        } elseif (str_contains($text, 'зелье') || str_contains($text, 'potion')) {
            $effect['healPercent'] = max((int) ($effect['healPercent'] ?? 0), 50);
        }

        return $effect !== [] ? $effect : null;
    }

    /** @return array{0:list<string>,1:list<string>} */
    private function battleCureTargets(string $rawTargets): array
    {
        $rawTargets = str_replace([';', '|'], ',', strtolower(trim($rawTargets)));
        $parts = array_values(array_filter(array_map('trim', explode(',', $rawTargets))));
        if ($parts === [] || in_array('all', $parts, true) || in_array('status_all', $parts, true)) {
            return [
                ['poison', 'sleep', 'burn', 'freeze', 'paralyze', 'fear', 'confuse'],
                ['badly_poisoned', 'confusion', 'nightmare', 'heal_block', 'taunt', 'encore', 'torment', 'disable'],
            ];
        }

        $statuses = [];
        $volatiles = [];
        foreach ($parts as $part) {
            $part = str_replace('-', '_', $part);
            if (in_array($part, ['badly_poisoned', 'toxic'], true)) {
                $statuses[] = 'poison';
                $volatiles[] = 'badly_poisoned';
                continue;
            }
            if (in_array($part, ['confusion', 'confuse'], true)) {
                $statuses[] = 'confuse';
                $volatiles[] = 'confusion';
                continue;
            }
            if (in_array($part, ['nightmare', 'heal_block', 'taunt', 'encore', 'torment', 'disable'], true)) {
                $volatiles[] = $part;
                continue;
            }
            $statuses[] = $part;
        }

        return [array_values(array_unique($statuses)), array_values(array_unique($volatiles))];
    }

    private function battleStatusIdByKey(string $key): int
    {
        return match (strtolower(trim($key))) {
            'poison', 'badly_poisoned' => 1,
            'sleep' => 2,
            'burn' => 3,
            'freeze' => 4,
            'paralyze', 'paralysis' => 5,
            'fear', 'flinch' => 6,
            'confuse', 'confusion' => 7,
            'leech_seed' => 8,
            'curse' => 9,
            default => 0,
        };
    }

    private function volatileLabel(string $kind): string
    {
        return match ($kind) {
            'badly_poisoned' => 'Тяжелый яд',
            'confusion' => 'Спутанность',
            'nightmare' => 'Кошмары',
            'heal_block' => 'Запрет регенерации',
            'taunt' => 'Насмешка',
            'encore' => 'Эстафета повтора',
            'torment' => 'Мучение',
            'disable' => 'Запрет атаки',
            default => $kind,
        };
    }

    /**
     * Items and switches spend the player's PvE turn, so the wild pokemon must
     * still answer. Otherwise healing/switching becomes a free round skip.
     *
     * @param list<string> $playerMessages
     */
    private function resolvePveEnemyResponseAfterPlayerAction(
        int $userId,
        int $battleId,
        array $playerMessages,
        bool $playerMessagesLogged
    ): array {
        $battle = $this->battles->findPveBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Бой завершен.'];
        }

        $round = (int) ($battle['raund'] ?? 1);
        if (!$playerMessagesLogged) {
            foreach ($playerMessages as $message) {
                $message = trim((string) $message);
                if ($message !== '') {
                    $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
                }
            }
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        $this->applyEntryWeatherAbilities((int) ($battle['id'] ?? 0), $round, $player, $enemy);
        $enemyMessages = [];
        if ((int) ($player['hp_my'] ?? 0) > 0 && (int) ($enemy['hp_my'] ?? 0) > 0) {
            $enemyMove = $this->enemyMoveForBattle((int) $battle['id'], $enemy);
            $enemyMessages[] = $this->applyMove((int) $battle['id'], $round, $enemy, $player, $enemyMove);
        }

        $this->battles->updatePokemonHp((string) $battle['poke_1'], (int) ($player['hp_my'] ?? 0));
        $this->battles->updatePokemonHp((string) $battle['poke_2'], (int) ($enemy['hp_my'] ?? 0));

        foreach ($enemyMessages as $message) {
            $message = trim((string) $message);
            if ($message !== '') {
                $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
            }
        }

        $messages = array_values(array_filter(array_merge($playerMessages, $enemyMessages)));
        if ((int) ($player['hp_my'] ?? 0) <= 0) {
            $bossPlayerAdvance = $this->bosses?->continueAfterPlayerFaint($userId, $battle, $round);
            if (is_array($bossPlayerAdvance) && !empty($bossPlayerAdvance['continued'])) {
                $this->battles->incrementRoundAndResetActions((int) $battle['id']);
                $state = $this->state($userId);
                $state['ok'] = true;
                $state['messages'] = array_values(array_filter([...$messages, (string) ($bossPlayerAdvance['message'] ?? '')]));
                $state['finished'] = false;
                $state['result'] = null;
                $state['rewards'] = ['coins' => 0, 'exp' => 0, 'drops' => []];
                return $state;
            }

            $finalLogRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            $this->battles->finishBattle((int) $battle['id'], $userId, -1);
            $environment = $this->battleEnvironment((int) $battle['id'], $round);
            return [
                'ok' => true,
                'active' => false,
                'finished' => true,
                'result' => 'lose',
                'rewards' => ['coins' => 0, 'exp' => 0, 'drops' => []],
                'messages' => $messages,
                'battle' => [
                    'id' => (int) $battle['id'],
                    'round' => $round,
                    'weather' => $environment['weather'],
                    'terrain' => $environment['terrain'],
                    'player' => $this->formatPokemon($player),
                    'enemy' => $this->formatPokemon($enemy),
                    'moves' => $this->formatMoves($player),
                    'switchOptions' => [],
                    'log' => $finalLogRows,
                    'logByRound' => $this->groupLogByRound($finalLogRows),
                ],
            ];
        }

        $this->battles->incrementRoundAndResetActions((int) $battle['id']);
        $state = $this->state($userId);
        $state['ok'] = true;
        $state['messages'] = $messages;
        $state['finished'] = false;
        $state['result'] = null;
        $state['rewards'] = ['coins' => 0, 'exp' => 0, 'drops' => []];
        return $state;
    }

    private function useBall(int $userId, int $itemUserId): array
    {
        $item = $this->battles->findBattleInventoryItem($userId, $itemUserId);
        if ($item === null || !$this->isCaptureBallItem($item)) {
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
        if ($this->bosses?->activeSessionForBattle($battleId) !== null) {
            return ['ok' => false, 'active' => true, 'message' => 'Босса нельзя поймать покеболом.'];
        }

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        if ((int) ($enemy['poimka'] ?? 0) !== 1) {
            return ['ok' => false, 'active' => true, 'message' => 'Этого покемона ловить нельзя.'];
        }

        $enemyName = strip_tags((string) ($enemy['names'] ?? 'Покемон'));
        $round = (int) ($battle['raund'] ?? 1);
        $this->battles->decrementInventoryItemRow($userId, $itemUserId);
        $catchMultiplier = $this->rewards?->activeMultiplier($userId, 'catch', 'pve') ?? 1.0;
        $caught = $this->tryCatchWildPokemon(
            (int) ($enemy['hp_my'] ?? 1),
            (int) ($enemy['hp_max'] ?? 1),
            max(1, (int) round($this->captureBallBonus($item) * $catchMultiplier))
        );

        if (!$caught) {
            $message = sprintf('Игрок #%d использует: Покебол, но #%s не хочет залазить в него.', $userId, $enemyName);
            $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
            return $this->resolvePveEnemyResponseAfterPlayerAction($userId, $battleId, [$message], true);
        }

        $active = $this->battles->countActivePokemon($userId) >= 6 ? 0 : 1;
        $newPokemonId = $this->battles->catchWildPokemon($userId, (string) ($enemy['battle_pokemon'] ?? $battle['poke_2']), $active);
        if ($newPokemonId === null) {
            $message = sprintf('Покемон #%s вырвался из шара: не удалось добавить его в команду.', $enemyName);
            $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
            return ['ok' => false, 'active' => true, 'message' => $message];
        }

        $message = $active > 0
            ? sprintf('Покемон #%s успешно пойман и добавлен в команду.', $enemyName)
            : sprintf('Покемон #%s успешно пойман и отправлен в питомник.', $enemyName);
        $this->battles->insertBattleLog((int) $battle['id'], $round, $message);
        $logRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
        $this->battles->finishBattle((int) $battle['id'], $userId, $userId);
        $environment = $this->battleEnvironment((int) $battle['id'], $round);

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
                'weather' => $environment['weather'],
                'terrain' => $environment['terrain'],
                'player' => $this->formatPokemon($player),
                'enemy' => $this->formatPokemon($enemy),
                'moves' => $this->formatMoves($player),
                'switchOptions' => [],
                'log' => $logRows,
                'logByRound' => $this->groupLogByRound($logRows),
            ],
        ];
    }

    private function isCaptureBallItem(array $item): bool
    {
        $itemId = (int) ($item['item_id'] ?? 0);
        if (in_array($itemId, [3, 90004, 90005], true)) {
            return true;
        }

        $text = mb_strtolower((string) (($item['name'] ?? '') . ' ' . ($item['tittle'] ?? '') . ' ' . ($item['category'] ?? '')));
        return (bool) preg_match('/поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|great.?ball|ultra.?ball|master.?ball|ball|шар/ui', $text);
    }

    private function captureBallBonus(array $item): int
    {
        return match ((int) ($item['item_id'] ?? 0)) {
            90004 => 255,
            90005 => 2,
            default => 1,
        };
    }

    private function escape(int $userId): array
    {
        $battleId = $this->battles->findActivePveBattleIdForUser($userId);
        if ($battleId <= 0) {
            return ['ok' => true, 'active' => false];
        }

        $this->battles->finishBattle($battleId, $userId, -1);
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

    private function applyMove(int $battleId, int $round, array &$attacker, array &$defender, array $move): string
    {
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        $defenderName = strip_tags((string) ($defender['names'] ?? 'Покемон'));
        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        $moveId = (int) ($move['id'] ?? $move['atac_id'] ?? 0);
        $category = (int) ($move['atac_categori'] ?? 1);
        $power = (int) ($move['atac_power'] ?? 0);
        $parts = [];
        $this->lastDamageDealt = 0;

        $statusGate = $this->applyStartOfTurnStatus($battleId, $round, $attacker, $defender, $move);
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
        if ($category >= 3 && $this->heldItemHasMeta($attacker, 'held_item:assault_vest')) {
            $parts[] = sprintf('%s не может использовать статусный прием из-за штурмового жилета.', $attackerName);
            return implode(' ', $parts);
        }

        $weatherBlockText = $this->weatherBlockedMoveText($battleId, $attackerName, $moveName, (string) ($move['atac_tip'] ?? ''));
        if ($weatherBlockText !== '') {
            $parts[] = $weatherBlockText;
            return implode(' ', $parts);
        }

        $hitChance = $this->effectiveAccuracy($battleId, $attacker, $defender, $move);
        if (random_int(1, 100) > $hitChance) {
            $missText = sprintf('%s использует %s — промах!', $attackerName, $moveName);
            $crashText = $this->applyMissMoveEffect($attacker, $move);
            return trim(implode(' ', array_filter([...$parts, $missText, $crashText])));
        }

        $damageText = '';
        if ($category < 3 && $power > 0) {
            $damageText = $this->damageMove($battleId, $attacker, $defender, $move);
        } else {
            $damageText = sprintf('%s использует %s.', $attackerName, $moveName);
        }
        $parts[] = $damageText;

        // Primary stat effects from stat_attak are authoritative. Fallback by name only if DB has no row.
        $effects = $this->battles->findMoveStatEffects($moveId);
        if ($effects === []) {
            $effects = $this->statMoveEffects($moveName);
        }
        foreach ($effects as $effect) {
            if (BattleMoveEffectCatalog::key($moveName) === 'growth'
                && BattleAbilityCatalog::weatherFamily((string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''))) === 'sun'
            ) {
                $effect['delta'] = 2;
            }
            $text = $this->applyStageEffect($battleId, $attacker, $defender, $effect);
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
            $statusText = $this->tryApplyStatus($battleId, $round, $defender, $primaryStatus, (int) ($catalogPrimaryStatus['chance'] ?? 100), (string) ($move['atac_tip'] ?? ''), $moveName);
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
            if (random_int(1, 100) > $chance) {
                continue;
            }
            if (($effect['kind'] ?? '') === 'status') {
                $target = ($effect['target'] ?? 'enemy') === 'self' ? $attacker : $defender;
                $statusText = $this->tryApplyStatus($battleId, $round, $target, (int) ($effect['statusId'] ?? 0), $chance, (string) ($move['atac_tip'] ?? ''), $moveName);
                if ($statusText !== '') {
                    $parts[] = $statusText;
                }
                continue;
            }
            $text = $this->applyStageEffect($battleId, $attacker, $defender, $effect);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        $trainingText = $this->applyNamedTrainingEffect($battleId, $round, $attacker, $defender, $move);
        if ($trainingText !== '') {
            $parts[] = $trainingText;
        }

        $specialText = $this->applySpecialMoveEffect($battleId, $round, $attacker, $defender, $move);
        if ($specialText !== '') {
            $parts[] = $specialText;
        }

        if ((int) ($defender['hp_my'] ?? 0) <= 0 && $this->battles->hasBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), 'destiny_bond')) {
            $attacker['hp_my'] = 0;
            $parts[] = sprintf('%s забирает %s с собой.', $defenderName, $attackerName);
        }

        return implode(' ', array_values(array_filter($parts)));
    }

    private function applyNamedTrainingEffect(int $battleId, int $round, array $attacker, array $defender, array $move): string
    {
        if ((int) ($attacker['training_stage'] ?? 0) < 6 || $this->lastDamageDealt <= 0) {
            return '';
        }
        if ((int) ($move['atac_categori'] ?? 1) >= 3 || (int) ($move['atac_power'] ?? 0) <= 0) {
            return '';
        }
        if (random_int(1, 100) > 5) {
            return '';
        }

        $effect = (string) ($attacker['training_named_effect'] ?? '');
        $statusId = match ($effect) {
            'poison' => 1,
            'burn' => 3,
            'freeze' => 4,
            'paralyze' => 5,
            'fear' => 6,
            'confuse' => 7,
            default => 0,
        };
        if ($statusId <= 0) {
            return '';
        }

        return $this->tryApplyStatus($battleId, $round, $defender, $statusId, 100, (string) ($move['atac_tip'] ?? ''), (string) ($move['atac_name'] ?? ''));
    }

    private function applySpecialMoveEffect(int $battleId, int $round, array &$attacker, array &$defender, array $move): string
    {
        $moveId = (int) ($move['id'] ?? $move['atac_id'] ?? 0);
        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        $defenderName = strip_tags((string) ($defender['names'] ?? 'Покемон'));

        $terrain = BattleMoveEffectCatalog::terrain($moveId, $moveName);
        if ($terrain !== null) {
            $this->battles->setBattleTerrain($battleId, (string) $terrain['kind'], $round + 5);
            return sprintf('%s меняет арену: %s.', $attackerName, (string) $terrain['label']);
        }

        $room = BattleMoveEffectCatalog::room($moveId, $moveName);
        if ($room !== null) {
            $added = $this->battles->addBattleRoom($battleId, (string) $room['kind'], $round + 5);
            return $added ? sprintf('%s создает поле: %s.', $attackerName, (string) $room['label']) : sprintf('%s уже действует.', (string) $room['label']);
        }

        $screen = BattleMoveEffectCatalog::screenKind($moveId, $moveName);
        if ($screen !== null) {
            $side = $this->battleSideForPokemon($attacker);
            $added = $this->battles->addBattleSideField($battleId, $side, $screen, $round + 5);
            return $added ? sprintf('%s ставит защитный экран.', $attackerName) : 'Этот экран уже действует.';
        }

        $weather = BattleMoveEffectCatalog::weather($moveId, $moveName);
        if ($weather !== null) {
            $currentWeather = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
            if (in_array($currentWeather, ['heavy_rain', 'harsh_sun', 'strong_winds'], true)) {
                return sprintf('%s пытается изменить погоду, но %s не дает обычной погоде вступить в силу.', $attackerName, $this->weatherLabel($currentWeather));
            }
            $this->battles->setBattleWeather($battleId, (string) $weather['kind'], $round + $this->weatherDuration($attacker, (string) $weather['kind']));
            return sprintf('%s меняет погоду: %s.', $attackerName, (string) $weather['label']);
        }

        $hazard = BattleMoveEffectCatalog::hazardKind($moveId, $moveName);
        if ($hazard !== null) {
            $side = $this->battleSideForPokemon($defender);
            $added = $this->battles->addBattleHazard($battleId, $side, $hazard);
            return $added
                ? sprintf('%s расставляет ловушку: %s.', $attackerName, $moveName)
                : sprintf('Ловушка %s уже лежит на поле.', $moveName);
        }

        $sideField = BattleMoveEffectCatalog::sideFieldKind($moveId, $moveName);
        if ($sideField !== null) {
            $side = $this->battleSideForPokemon($defender);
            $added = $this->battles->addBattleSideField($battleId, $side, $sideField, $round + 4);
            return $added
                ? sprintf('%s покрывает сторону соперника опасным полем.', $attackerName)
                : 'Такое поле уже действует на стороне соперника.';
        }

        $trap = BattleMoveEffectCatalog::trapKind($moveId, $moveName);
        if ($trap !== null) {
            $added = $this->battles->addBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), $trap, $round + 4);
            return $added
                ? sprintf('%s больше не может свободно смениться.', $defenderName)
                : sprintf('%s уже удержан ловушкой.', $defenderName);
        }

        $volatile = BattleMoveEffectCatalog::volatileKind($moveId, $moveName);
        if ($volatile !== null) {
            $target = $volatile === 'destiny_bond' ? $attacker : $defender;
            $added = $this->battles->addBattleVolatile($battleId, (string) ($target['battle_pokemon'] ?? ''), $volatile, $round + ($volatile === 'perish_song' ? 4 : 3));
            return $added ? $this->volatileApplyText($volatile, strip_tags((string) ($target['names'] ?? 'Покемон'))) : '';
        }

        if (BattleMoveEffectCatalog::key($moveName) === 'curse') {
            $attackerTypes = $this->math->typeLabelsFromPokemon($attacker);
            if (in_array('ghost', $attackerTypes, true)) {
                $cost = max(1, (int) floor(max(1, (int) ($attacker['hp_max'] ?? 1)) / 2));
                $attacker['hp_my'] = max(1, (int) ($attacker['hp_my'] ?? 1) - $cost);
                $this->battles->addBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), 'curse', 999999);
                return sprintf('%s жертвует %d HP и проклинает %s.', $attackerName, $cost, $defenderName);
            }

            $texts = [];
            foreach ([
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита'],
                ['target' => 'self', 'kind' => 'minus', 'field' => 'speed', 'delta' => 1, 'label' => 'Скорость'],
            ] as $effect) {
                $texts[] = $this->applyStageEffect($battleId, $attacker, $defender, $effect);
            }
            return implode(' ', array_filter($texts));
        }

        if ($moveId === 156) {
            if ($this->battles->hasBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'heal_block')) {
                return sprintf('%s не может восстановить HP из-за запрета регенерации.', $attackerName);
            }
            $attacker['hp_my'] = max(1, (int) ($attacker['hp_max'] ?? 1));
            $this->battles->applyBattleStatus($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 2, $round, 2);
            return sprintf('%s полностью восстанавливает здоровье и засыпает.', $attackerName);
        }

        if (in_array($moveId, [105, 135], true)) {
            if ($this->battles->hasBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'heal_block')) {
                return sprintf('%s не может восстановить HP из-за запрета регенерации.', $attackerName);
            }
            $healed = $this->healPokemon($attacker, (int) floor(max(1, (int) ($attacker['hp_max'] ?? 1)) / 2));
            return $healed > 0 ? sprintf('%s восстанавливает %d HP.', $attackerName, $healed) : sprintf('%s уже полностью здоров.', $attackerName);
        }

        if (in_array(BattleMoveEffectCatalog::key($moveName), ['morning sun', 'synthesis', 'moonlight'], true)) {
            if ($this->battles->hasBattleVolatile($battleId, (string) ($attacker['battle_pokemon'] ?? ''), 'heal_block')) {
                return sprintf('%s не может восстановить HP из-за запрета регенерации.', $attackerName);
            }
            $maxHp = max(1, (int) ($attacker['hp_max'] ?? 1));
            $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
            $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
            $ratio = $weatherFamily === 'sun' ? 2 / 3 : ($weatherKind === '' ? 1 / 2 : 1 / 4);
            $healed = $this->healPokemon($attacker, (int) floor($maxHp * $ratio));
            return $healed > 0 ? sprintf('%s восстанавливает %d HP.', $attackerName, $healed) : sprintf('%s уже полностью здоров.', $attackerName);
        }

        if ($moveId === 505) {
            if ($this->battles->hasBattleVolatile($battleId, (string) ($defender['battle_pokemon'] ?? ''), 'heal_block')) {
                return sprintf('%s не может восстановить HP из-за запрета регенерации.', $defenderName);
            }
            $healed = $this->healPokemon($defender, (int) floor(max(1, (int) ($defender['hp_max'] ?? 1)) / 2));
            return $healed > 0 ? sprintf('%s лечит %s на %d HP.', $attackerName, $defenderName, $healed) : sprintf('%s уже полностью здоров.', $defenderName);
        }

        if ($moveId === 187) {
            $cost = max(1, (int) floor(max(1, (int) ($attacker['hp_max'] ?? 1)) / 2));
            $attacker['hp_my'] = max(1, (int) ($attacker['hp_my'] ?? 1) - $cost);
            return sprintf('%s жертвует %d HP ради усиления.', $attackerName, $cost);
        }

        if (in_array($moveId, [120, 153], true)) {
            $attacker['hp_my'] = 0;
            return sprintf('%s теряет все HP после %s.', $attackerName, $moveName);
        }

        $recoilKind = BattleMoveEffectCatalog::recoilKind($moveId, $moveName);
        if ($recoilKind !== null && $recoilKind !== 'crash_on_miss') {
            $damage = match ($recoilKind) {
                'half_damage' => max(1, (int) floor($this->lastDamageDealt / 2)),
                'half_max' => max(1, (int) floor(max(1, (int) ($attacker['hp_max'] ?? 1)) / 2)),
                default => max(1, (int) floor($this->lastDamageDealt / 3)),
            };
            if ($damage > 0) {
                $attacker['hp_my'] = max(0, (int) ($attacker['hp_my'] ?? 0) - $damage);
                return sprintf('%s получает отдачу: %d HP.', $attackerName, $damage);
            }
        }

        return '';
    }

    private function applyMissMoveEffect(array &$attacker, array $move): string
    {
        $moveName = (string) ($move['atac_name'] ?? 'Атака');
        if (BattleMoveEffectCatalog::recoilKind((int) ($move['id'] ?? 0), $moveName) !== 'crash_on_miss') {
            return '';
        }

        $damage = max(1, (int) floor(max(1, (int) ($attacker['hp_max'] ?? 1)) / 2));
        $attacker['hp_my'] = max(0, (int) ($attacker['hp_my'] ?? 0) - $damage);
        $attackerName = strip_tags((string) ($attacker['names'] ?? 'Покемон'));
        return sprintf('%s ударяется после промаха и теряет %d HP.', $attackerName, $damage);
    }

    private function volatileApplyText(string $kind, string $targetName): string
    {
        return match ($kind) {
            'nightmare' => sprintf('%s мучают кошмары.', $targetName),
            'perish_song' => sprintf('%s слышит гибельную песнь.', $targetName),
            'destiny_bond' => sprintf('%s готов утащить соперника за собой.', $targetName),
            'taunt' => sprintf('%s спровоцирован и не может использовать статусные приемы.', $targetName),
            'encore' => sprintf('%s вынужден повторять прошлую атаку.', $targetName),
            'torment' => sprintf('%s не сможет повторять одну атаку подряд.', $targetName),
            'disable' => sprintf('Одна из атак %s заблокирована.', $targetName),
            'knock_off' => sprintf('%s теряет эффект удерживаемого предмета на этот бой.', $targetName),
            default => '',
        };
    }

    private function healPokemon(array &$pokemon, int $amount): int
    {
        $before = max(0, (int) ($pokemon['hp_my'] ?? 0));
        $max = max(1, (int) ($pokemon['hp_max'] ?? 1));
        $after = min($max, $before + max(0, $amount));
        $pokemon['hp_my'] = $after;
        return max(0, $after - $before);
    }

    private function battleSideForPokemon(array $pokemon): int
    {
        $battlePokemon = (string) ($pokemon['battle_pokemon'] ?? '');
        return str_starts_with($battlePokemon, 'pve_') ? 2 : 1;
    }

    private function applyEntryWeatherAbilities(int $battleId, int $round, array $first, array $second): void
    {
        $this->syncPermanentWeatherSource($battleId, $round, $first, $second);
        $this->applySingleEntryWeatherAbility($battleId, $round, $first);
        $this->applySingleEntryWeatherAbility($battleId, $round, $second);
    }

    private function syncPermanentWeatherSource(int $battleId, int $round, array $first, array $second): void
    {
        $current = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        if (!in_array($current, ['heavy_rain', 'harsh_sun', 'strong_winds'], true)) {
            return;
        }

        foreach ([$first, $second] as $pokemon) {
            if (BattleAbilityCatalog::startsWeather(BattleAbilityCatalog::key($pokemon)) === $current) {
                return;
            }
        }

        $this->battles->clearBattleWeather($battleId);
        $this->battles->insertBattleLog($battleId, $round, sprintf('%s рассеивается.', $this->weatherLabel($current)));
    }

    private function applySingleEntryWeatherAbility(int $battleId, int $round, array $pokemon): void
    {
        $ability = BattleAbilityCatalog::key($pokemon);
        if ($ability === '') {
            return;
        }
        if ($ability === 'weather_lock') {
            if ($this->battles->findBattleWeather($battleId) !== null) {
                $this->battles->clearBattleWeather($battleId);
                $name = strip_tags((string) ($pokemon['names'] ?? 'Покемон'));
                $this->battles->insertBattleLog($battleId, $round, sprintf('%s рассеивает погоду.', $name));
            }
            return;
        }

        $weather = BattleAbilityCatalog::startsWeather($ability);
        if ($weather === null) {
            return;
        }
        $battlePokemon = (string) ($pokemon['battle_pokemon'] ?? '');
        $isStrongWeather = in_array($weather, ['heavy_rain', 'harsh_sun', 'strong_winds'], true);
        if (!$isStrongWeather && $battlePokemon !== '' && $this->battles->hasBattleVolatile($battleId, $battlePokemon, 'weather_started')) {
            return;
        }
        $current = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        if (in_array($current, ['heavy_rain', 'harsh_sun', 'strong_winds'], true) && !$isStrongWeather) {
            $name = strip_tags((string) ($pokemon['names'] ?? 'Покемон'));
            $this->battles->insertBattleLog($battleId, $round, sprintf('%s пытается вызвать %s, но %s не дает обычной погоде вступить в силу.', $name, $this->weatherLabel($weather), $this->weatherLabel($current)));
            if ($battlePokemon !== '') {
                $this->battles->addBattleVolatile($battleId, $battlePokemon, 'weather_started', 999999);
            }
            return;
        }
        if ($current !== $weather) {
            $this->battles->setBattleWeather($battleId, $weather, $round + $this->weatherDuration($pokemon, $weather));
            $name = strip_tags((string) ($pokemon['names'] ?? 'Покемон'));
            $this->battles->insertBattleLog($battleId, $round, sprintf('%s вызывает погоду: %s.', $name, $this->weatherLabel($weather)));
            $this->battles->insertBattleLog($battleId, $round, sprintf('[WEATHER] %s activated by %s.', $this->weatherLogKey($weather), $name));
        }
        if (!$isStrongWeather && $battlePokemon !== '') {
            $this->battles->addBattleVolatile($battleId, $battlePokemon, 'weather_started', 999999);
        }
    }

    /** @return list<string> */
    private function applySwitchHazards(int $battleId, int $round, array &$pokemon): array
    {
        $side = $this->battleSideForPokemon($pokemon);
        $name = strip_tags((string) ($pokemon['names'] ?? 'Покемон'));
        $messages = [];

        foreach ($this->battles->findBattleHazardLayers($battleId, $side) as $hazard => $layers) {
            if ($hazard === 'spikes') {
                $divisor = match (min(3, max(1, $layers))) {
                    1 => 8,
                    2 => 6,
                    default => 4,
                };
                $damage = max(1, (int) floor(max(1, (int) ($pokemon['hp_max'] ?? 1)) / $divisor));
                $pokemon['hp_my'] = max(0, (int) ($pokemon['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s получает %d HP урона от шипов.', $name, $damage);
            } elseif ($hazard === 'toxic_spikes') {
                $statusText = $this->tryApplyStatus($battleId, $round, $pokemon, 1, 100, 'Poison', 'Toxic Spikes');
                if ($statusText !== '') {
                    if ($layers >= 2) {
                        $this->battles->addBattleVolatile($battleId, (string) ($pokemon['battle_pokemon'] ?? ''), 'badly_poisoned', 999999);
                    }
                    $messages[] = sprintf('%s отравлен токсичными шипами.', $name);
                }
            } elseif ($hazard === 'stealth_rock' || $hazard === 'steel_spikes') {
                $multiplier = $hazard === 'stealth_rock'
                    ? max(0.25, min(4.0, $this->math->typeEffectiveness('Rock', $this->effectiveTypeLabels($battleId, $pokemon))))
                    : 1.0;
                $damage = max(1, (int) floor(max(1, (int) ($pokemon['hp_max'] ?? 1)) / 8 * $multiplier));
                $pokemon['hp_my'] = max(0, (int) ($pokemon['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s получает %d HP урона от полевой ловушки.', $name, $damage);
            } elseif ($hazard === 'sticky_web') {
                $text = $this->applyStageEffect($battleId, $pokemon, $pokemon, [
                    'target' => 'self',
                    'kind' => 'minus',
                    'field' => 'speed',
                    'delta' => 1,
                    'label' => 'Скорость',
                ]);
                if ($text !== '') {
                    $messages[] = 'Липкая паутина замедляет цель. ' . $text;
                }
            }
        }

        if ($messages !== []) {
            $this->battles->updatePokemonHp((string) ($pokemon['battle_pokemon'] ?? ''), (int) ($pokemon['hp_my'] ?? 0));
            foreach ($messages as $message) {
                $this->battles->insertBattleLog($battleId, $round, $message);
            }
        }

        return $messages;
    }

    private function damageMove(int $battleId, array $attacker, array &$defender, array $move): string
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

        $atk = $this->effectiveStatFor($battleId, $attacker, $atkDbField, $atkStageField);
        $def = $this->effectiveStatFor($battleId, $defender, $defDbField, $defStageField);
        if (!$isSpecial && $this->hasMajorStatus($battleId, $attacker, 3)) {
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
        $attackerTypes = $this->effectiveTypeLabels($battleId, $attacker);
        $defenderTypes = $this->effectiveTypeLabels($battleId, $defender);
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
            $this->lastDamageDealt = 0;
            return sprintf('%s использует %s. %s не получает урона. %s', $attackerName, $moveName, $defenderName, $this->math->typeMessage($typeEffect));
        }

        $base = (((2 * $lvl / 5 + 2) * $power * $atk / max(1, $def)) / 50) + 2;
        $rand = random_int(85, 100) / 100;
        $critChance = $this->criticChance((string) ($move['critic'] ?? '3'));
        $critChance = max(0, min(100, $critChance + $this->heldItemCriticalBonus($attacker)));
        $isCrit = random_int(1, 100) <= $critChance;
        $crit = $isCrit ? 1.5 : 1.0;
        $weatherPower = $this->weatherPowerModifier($weatherKind, $moveType);
        if ($weatherPower > 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s move boosted by %s x%.1f.', $moveType, $this->weatherLogKey($weatherKind), $weatherPower);
        } elseif ($weatherPower > 0.0 && $weatherPower < 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s move weakened by %s x%.1f.', $moveType, $this->weatherLogKey($weatherKind), $weatherPower);
        }
        if (BattleMoveEffectCatalog::key($moveName) === 'solarbeam' || BattleMoveEffectCatalog::key($moveName) === 'solar beam') {
            $weatherPower *= in_array($weatherFamily, ['rain', 'sandstorm', 'hail'], true) ? 0.5 : 1.0;
        }
        $abilityPower = BattleAbilityCatalog::weatherAttackMultiplier(BattleAbilityCatalog::key($attacker), $weatherKind, $moveType);
        if (BattleAbilityCatalog::key($defender) === 'dry_skin' && strtolower($moveType) === 'fire') {
            $abilityPower *= 1.25;
        }
        $terrainKind = (string) (($this->battles->findBattleTerrain($battleId)['kind'] ?? ''));
        $terrainPower = $this->terrainPowerModifier($terrainKind, $moveType);
        $screenPower = $this->screenDamageModifier($battleId, $defender, $isSpecial);
        $heldPower = $this->heldItemPowerModifier($attacker, $moveType, $category);
        if ($heldPower > 1.0) {
            $modifierLogs[] = sprintf('[DAMAGE_MODIFIER] %s boosted by held item x%.1f.', $moveType, $heldPower);
        }
        $damage = max(1, (int) floor($base * $stab * $typeEffect * $rand * $crit * $weatherPower * $abilityPower * $terrainPower * $screenPower * $heldPower));
        $this->lastDamageDealt = $damage;

        $defender['hp_my'] = max(0, (int) ($defender['hp_my'] ?? 0) - $damage);
        $hpLeft = (int) ($defender['hp_my'] ?? 0);
        $hpMax = max(1, (int) ($defender['hp_max'] ?? 1));
        $typeText = $this->math->typeMessage($typeEffect);
        $critText = $isCrit ? ' КРИТ!' : '';
        $tail = trim($critText . ' ' . $typeText);
        $tail = $tail !== '' ? ' ' . $tail : '';

        $logTail = $modifierLogs !== [] ? ' ' . implode(' ', $modifierLogs) : '';
        return sprintf('%s использует %s.%s %s теряет %d HP (%d/%d).%s', $attackerName, $moveName, $tail, $defenderName, $damage, $hpLeft, $hpMax, $logTail);
    }

    /** @param array{target?:string,kind?:string,field?:string,delta?:int,label?:string} $effect */
    private function applyStageEffect(int $battleId, array $attacker, array $defender, array $effect): string
    {
        $target = ($effect['target'] ?? 'enemy') === 'self' ? $attacker : $defender;
        $targetName = strip_tags((string) ($target['names'] ?? 'Покемон'));
        $battlePokemon = (string) ($target['battle_pokemon'] ?? '');
        $kind = (string) ($effect['kind'] ?? 'minus');
        $field = (string) ($effect['field'] ?? '');
        $delta = max(1, min(6, (int) ($effect['delta'] ?? 1)));
        $label = (string) ($effect['label'] ?? $field);
        $applied = $this->battles->applyBattleStatStage($battleId, $battlePokemon, $kind, $field, $delta);
        $sign = $kind === 'minus' ? '-' : '+';
        if ($applied <= 0) {
            return sprintf('%s: %s уже на пределе.', $targetName, $label);
        }
        return sprintf('%s: %s %s%d.', $targetName, $label, $sign, $applied);
    }

    private function tryApplyStatus(int $battleId, int $round, array $target, int $statusId, int $chance, string $moveType = '', string $moveName = ''): string
    {
        if ($statusId <= 0 || random_int(1, 100) > max(1, min(100, $chance))) {
            return '';
        }
        $targetName = strip_tags((string) ($target['names'] ?? 'Покемон'));
        $battlePokemon = (string) ($target['battle_pokemon'] ?? '');
        if ($this->isStatusImmune($battleId, $target, $statusId, $moveType)) {
            return sprintf('%s невосприимчив к статусу: %s.', $targetName, $this->statusName($statusId));
        }
        if ($this->isPersistentStatus($statusId)) {
            foreach ($this->battles->findBattleMajorStatuses($battleId, $battlePokemon) as $existing) {
                if ($this->isPersistentStatus((int) ($existing['id'] ?? 0))) {
                    return sprintf('%s уже имеет стабильный статус.', $targetName);
                }
            }
        }
        if ($this->battles->hasBattleStatus($battleId, $battlePokemon, $statusId)) {
            return sprintf('%s уже имеет этот статус.', $targetName);
        }
        $duration = match ($statusId) {
            2 => random_int(1, 3),
            4 => random_int(1, 3),
            6 => 1,
            7 => random_int(1, 4),
            default => 999999,
        };
        $ok = $this->battles->applyBattleStatus($battleId, $battlePokemon, $statusId, $round, $duration);
        if (!$ok) {
            return '';
        }
        return sprintf('%s получает статус: %s.', $targetName, $this->statusName($statusId));
    }

    /** @return array{canAct:bool,messages:list<string>} */
    private function applyStartOfTurnStatus(int $battleId, int $round, array &$actor, ?array &$opponent = null, ?array $selectedMove = null): array
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
                if (random_int(1, 100) <= 20) {
                    $this->battles->deleteBattleStatus($battleId, $battlePokemon, 4);
                    $messages[] = sprintf('%s разморозился.', $name);
                } else {
                    $canAct = false;
                    $messages[] = sprintf('%s заморожен и не может атаковать.', $name);
                }
            } elseif ($id === 5) { // paralysis
                if (random_int(1, 100) <= 25) {
                    $canAct = false;
                    $messages[] = sprintf('%s парализован и пропускает ход.', $name);
                }
            } elseif ($id === 6) { // flinch/fear
                $canAct = false;
                $messages[] = sprintf('%s напуган и пропускает ход.', $name);
                $this->battles->deleteBattleStatus($battleId, $battlePokemon, 6);
            } elseif ($id === 7) { // confusion
                if (random_int(1, 100) <= 50) {
                    $damage = max(1, (int) floor((((2 * max(1, (int) ($actor['lvl'] ?? 1)) / 5 + 2) * 40 * max(1, (int) ($actor['atk'] ?? 1)) / max(1, (int) ($actor['def'] ?? 1))) / 50) + 2));
                    $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                    $canAct = false;
                    $messages[] = sprintf('%s спутан и ранит себя на %d HP.', $name, $damage);
                }
            }
            if ($id === 8) { // leech seed
                if (in_array('grass', $this->effectiveTypeLabels($battleId, $actor), true)) {
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
            } elseif ($kind === 'nightmare' && $this->hasMajorStatus($battleId, $actor, 2)) {
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

        foreach ($this->battles->findBattleSideFields($battleId, $this->battleSideForPokemon($actor)) as $field) {
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
            $types = $this->effectiveTypeLabels($battleId, $actor);
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
            $healed = $this->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP благодаря дождю.', $name, $healed);
            }
        } elseif ($ability === 'ice_body' && $weatherFamily === 'hail' && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
            $healed = $this->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP ледяным телом.', $name, $healed);
            }
        } elseif ($ability === 'dry_skin') {
            if ($weatherFamily === 'rain' && !$this->battles->hasBattleVolatile($battleId, $battlePokemon, 'heal_block')) {
                $healed = $this->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8)));
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
            $healed = $this->healPokemon($actor, max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16)));
            if ($healed > 0) {
                $messages[] = sprintf('%s восстанавливает %d HP от травяной арены.', $name, $healed);
            }
        }

        return ['canAct' => $canAct, 'messages' => $messages];
    }

    private function statMoveEffects(string $moveName): array
    {
        return BattleMoveEffectCatalog::statEffects($moveName);
    }

    private function effectiveStatFor(int $battleId, array $pokemon, string $dbField, string $stageField): int
    {
        $stages = $this->battles->findBattleStatStageParts($battleId, (string) ($pokemon['battle_pokemon'] ?? ''));
        $value = $this->math->effectiveStat(
            (int) ($pokemon[$dbField] ?? 1),
            (int) ($stages['plus'][$stageField] ?? 0),
            (int) ($stages['minus'][$stageField] ?? 0)
        );
        if ($stageField === 'speed' && $this->hasMajorStatus($battleId, $pokemon, 5)) {
            $value = max(1, (int) floor($value / 4));
        }
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
        $ability = BattleAbilityCatalog::key($pokemon);
        if ($stageField === 'speed') {
            $value = max(1, (int) floor($value * BattleAbilityCatalog::speedMultiplier($ability, $weatherKind)));
        }
        if ($dbField === 'satk' && $ability === 'solar_power' && $weatherFamily === 'sun') {
            $value = max(1, (int) floor($value * 1.5));
        }
        $types = $this->effectiveTypeLabels($battleId, $pokemon);
        if ($dbField === 'sdef' && $weatherFamily === 'sandstorm' && in_array('rock', $types, true)) {
            $value = max(1, (int) floor($value * 1.5));
        }
        if ($dbField === 'def' && $weatherFamily === 'hail' && in_array('ice', $types, true)) {
            $value = max(1, (int) floor($value * 1.5));
        }
        $value = max(1, (int) floor($value * $this->heldItemStatModifier($pokemon, $dbField)));
        return $value;
    }

    private function effectiveAccuracy(int $battleId, array $attacker, array $defender, array $move): int
    {
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
        $moveKey = BattleMoveEffectCatalog::key((string) ($move['atac_name'] ?? ''));
        if ($weatherFamily === 'rain' && in_array($moveKey, ['thunder', 'hurricane'], true)) {
            return 100;
        }
        if ($weatherFamily === 'sun' && in_array($moveKey, ['thunder', 'hurricane'], true)) {
            return 50;
        }
        if ($weatherFamily === 'hail' && $moveKey === 'blizzard') {
            return 100;
        }

        $attackerStages = $this->battles->findBattleStatStageParts($battleId, (string) ($attacker['battle_pokemon'] ?? ''));
        $defenderStages = $this->battles->findBattleStatStageParts($battleId, (string) ($defender['battle_pokemon'] ?? ''));
        $chance = $this->math->accuracyChance((int) ($move['atac_accuracy'] ?? 100), $attackerStages, $defenderStages);
        return max(1, min(100, (int) round($chance * $this->heldItemAccuracyMultiplier($attacker))));
    }

    private function weatherPowerModifier(string $weatherKind, string $moveType): float
    {
        $weatherKind = BattleAbilityCatalog::weatherFamily($weatherKind);
        $type = strtolower($moveType);
        if ($weatherKind === 'sun') {
            return $type === 'fire' ? 1.5 : ($type === 'water' ? 0.5 : 1.0);
        }
        if ($weatherKind === 'rain') {
            return $type === 'water' ? 1.5 : ($type === 'fire' ? 0.5 : 1.0);
        }
        return 1.0;
    }

    private function heldItemPowerModifier(array $pokemon, string $moveType, int $category): float
    {
        $type = strtolower(trim($moveType));
        if ($type === '') {
            return 1.0;
        }

        if ($this->heldItemHasMeta($pokemon, 'held_item:choice_specs') && $category === 2) {
            return 1.5;
        }

        $baseId = $this->heldItemPokemonDisplayBaseId($pokemon);
        if ($this->heldItemHasMeta($pokemon, 'held_item:soul_dew')
            && in_array($baseId, [380, 381], true)
            && in_array($type, ['dragon', 'psychic'], true)
        ) {
            return 1.2;
        }
        if ($this->heldItemHasMeta($pokemon, 'held_item:ice_gem') && $type === 'ice') {
            return 1.3;
        }

        $metaText = $this->heldItemMetaText($pokemon);
        foreach ($this->heldItemTypeBoosts((int) ($pokemon['held_item_id'] ?? 0)) as $needle => $types) {
            if (str_contains($metaText, $needle) && in_array($type, $types, true)) {
                return 1.2;
            }
        }

        return 1.0;
    }

    private function heldItemStatModifier(array $pokemon, string $dbField): float
    {
        $itemId = (int) ($pokemon['held_item_id'] ?? 0);
        $baseId = $this->heldItemPokemonDisplayBaseId($pokemon);
        if ($dbField === 'atk' && $itemId === 358 && in_array($baseId, [104, 105], true)) {
            return 2.0;
        }
        if ($dbField === 'sdef' && $this->heldItemHasMeta($pokemon, 'held_item:assault_vest')) {
            return 1.5;
        }
        if ($dbField === 'speed' && $this->heldItemHasMeta($pokemon, 'held_item:iron_ball')) {
            return 0.5;
        }

        return 1.0;
    }

    private function heldItemAccuracyMultiplier(array $pokemon): float
    {
        return $this->heldItemHasMeta($pokemon, 'held_item:wide_lens') ? 1.1 : 1.0;
    }

    private function heldItemCriticalBonus(array $pokemon): int
    {
        $itemId = (int) ($pokemon['held_item_id'] ?? 0);
        if (in_array($itemId, [81, 371], true)
            || $this->heldItemHasMeta($pokemon, 'held_item:scope_lens')
            || str_contains($this->heldItemMetaText($pokemon), 'razor claw')
        ) {
            return 6;
        }

        return 0;
    }

    private function heldItemQuickClawActivated(array $pokemon): bool
    {
        return ((int) ($pokemon['held_item_id'] ?? 0) === 80 || $this->heldItemHasMeta($pokemon, 'held_item:quick_claw'))
            && random_int(1, 100) <= 20;
    }

    private function heldItemHasMeta(array $pokemon, string $needle): bool
    {
        return str_contains($this->heldItemMetaText($pokemon), strtolower($needle));
    }

    private function heldItemMetaText(array $pokemon): string
    {
        $fallback = $this->heldItemFallbackMeta((int) ($pokemon['held_item_id'] ?? 0));
        $text = (string) (($pokemon['held_item_meta'] ?? '') . ' ' . $fallback . ' ' . ($pokemon['held_item_name'] ?? '') . ' ' . ($pokemon['held_item_title'] ?? ''));
        $text = mb_strtolower($text, 'UTF-8');
        return str_replace('ё', 'е', $text);
    }

    private function heldItemFallbackMeta(int $itemId): string
    {
        return match ($itemId) {
            78, 83, 85, 491 => 'held_item:normal',
            80 => 'held_item:quick_claw',
            81, 371 => 'held_item:scope_lens',
            82 => 'held_item:ghost',
            86, 350 => 'held_item:fire',
            87, 328 => 'held_item:electric',
            88 => 'held_item:steel',
            89, 339 => 'held_item:rock',
            90, 349 => 'held_item:psychic',
            91 => 'held_item:poison',
            92 => 'held_item:training',
            93, 323 => 'held_item:dragon',
            181 => 'held_item:dark',
            320 => 'held_item:absorb_bulb',
            321, 325 => 'held_item:wing',
            322, 372 => 'held_item:metronome',
            326 => 'held_item:binding',
            333 => 'held_item:flame_orb',
            337 => 'held_item:fairy',
            342 => 'held_item:fighting',
            344 => 'held_item:soul_dew',
            358 => 'held_item:thick_club',
            360 => 'held_item:upgrade',
            361 => 'held_item:ring_target',
            365 => 'held_item:dubious_disc',
            366 => 'held_item:scarf',
            370 => 'held_item:focus_band',
            373 => 'held_item:iron_ball',
            374 => 'held_item:metal_powder',
            376 => 'held_item:ground',
            377, 378 => 'held_item:ice',
            380 => 'held_item:ice_gem',
            386 => 'held_item:choice_specs',
            489 => 'held_item:light_clay',
            492 => 'held_item:wide_lens',
            496 => 'held_item:everstone',
            517 => 'held_item:assault_vest',
            520 => 'held_item:safety_goggles',
            default => '',
        };
    }

    private function heldItemPokemonDisplayBaseId(array $pokemon): int
    {
        return PokemonFormCatalog::displayBaseId((int) ($pokemon['basenum'] ?? $pokemon['baseNum'] ?? 0));
    }

    /** @return array<string,list<string>> */
    private function heldItemTypeBoosts(int $itemId): array
    {
        $boosts = [
            'held_item:normal' => ['normal'],
            'held_item:fire' => ['fire'],
            'held_item:water' => ['water'],
            'held_item:electric' => ['electric'],
            'held_item:grass' => ['grass'],
            'held_item:ice' => ['ice'],
            'held_item:fighting' => ['fighting'],
            'held_item:poison' => ['poison'],
            'held_item:ground' => ['ground'],
            'held_item:flying' => ['flying'],
            'held_item:psychic' => ['psychic'],
            'held_item:bug' => ['bug'],
            'held_item:rock' => ['rock'],
            'held_item:ghost' => ['ghost'],
            'held_item:dragon' => ['dragon'],
            'held_item:dark' => ['dark'],
            'held_item:steel' => ['steel'],
            'held_item:fairy' => ['fairy'],
            'held_item:ice_gem' => ['ice'],
        ];

        return $boosts;
    }

    private function weatherBlockedMoveText(int $battleId, string $attackerName, string $moveName, string $moveType): string
    {
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $type = strtolower(trim($moveType));
        if ($weatherKind === 'heavy_rain' && $type === 'fire') {
            return sprintf('%s пытается использовать %s, но Сильный ливень гасит огненные атаки. [DAMAGE_BLOCKED] Fire move blocked by Primordial Sea.', $attackerName, $moveName);
        }
        if ($weatherKind === 'harsh_sun' && $type === 'water') {
            return sprintf('%s пытается использовать %s, но Жаркое солнце испаряет водные атаки. [DAMAGE_BLOCKED] Water move blocked by Desolate Land.', $attackerName, $moveName);
        }
        return '';
    }

    private function weatherLogKey(string $kind): string
    {
        return match ($kind) {
            'heavy_rain' => 'Primordial Sea',
            'harsh_sun' => 'Desolate Land',
            'strong_winds' => 'Delta Stream',
            'rain' => 'Rain',
            'sun' => 'Sun',
            'sandstorm' => 'Sandstorm',
            'hail' => 'Hail',
            default => $kind !== '' ? $kind : 'Weather',
        };
    }

    /** @return list<string> */
    private function effectiveTypeLabels(int $battleId, array $pokemon): array
    {
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $forecast = BattleAbilityCatalog::forecastType(BattleAbilityCatalog::key($pokemon), $weatherKind);
        if ($forecast !== null) {
            return [$forecast];
        }
        return $this->math->typeLabelsFromPokemon($pokemon);
    }

    private function weatherDuration(array $pokemon, string $weather): int
    {
        if (in_array($weather, ['heavy_rain', 'harsh_sun', 'strong_winds'], true)) {
            return 999999;
        }
        $itemText = mb_strtolower((string) (($pokemon['held_item_name'] ?? '') . ' ' . ($pokemon['held_item_title'] ?? '')), 'UTF-8');
        $itemText = str_replace('ё', 'е', $itemText);
        $stoneMatches = match ($weather) {
            'rain' => ['damp rock', 'дожд', 'влажн'],
            'sun' => ['heat rock', 'солнеч', 'жар'],
            'sandstorm' => ['smooth rock', 'песчан'],
            'hail' => ['icy rock', 'ледян', 'снеж'],
            default => [],
        };
        foreach ([...$stoneMatches, 'погодн'] as $needle) {
            if ($needle !== '' && str_contains($itemText, $needle)) {
                return 10;
            }
        }
        return 5;
    }

    private function terrainPowerModifier(string $terrainKind, string $moveType): float
    {
        $type = strtolower($moveType);
        return match ($terrainKind) {
            'electric' => $type === 'electric' ? 1.5 : 1.0,
            'misty' => $type === 'dragon' ? 0.5 : 1.0,
            'grassy' => $type === 'grass' ? 1.5 : 1.0,
            default => 1.0,
        };
    }

    private function screenDamageModifier(int $battleId, array $defender, bool $isSpecial): float
    {
        $side = $this->battleSideForPokemon($defender);
        foreach ($this->battles->findBattleSideFields($battleId, $side) as $field) {
            $kind = (string) ($field['kind'] ?? '');
            if ((!$isSpecial && $kind === 'reflect') || ($isSpecial && $kind === 'light_screen')) {
                return 0.5;
            }
        }
        return 1.0;
    }

    private function hasMajorStatus(int $battleId, array $pokemon, int $statusId): bool
    {
        return $this->battles->hasBattleStatus($battleId, (string) ($pokemon['battle_pokemon'] ?? ''), $statusId);
    }

    private function isPersistentStatus(int $statusId): bool
    {
        return in_array($statusId, [1, 2, 3, 4, 5], true);
    }

    private function isStatusImmune(int $battleId, array $target, int $statusId, string $moveType): bool
    {
        $types = $this->effectiveTypeLabels($battleId, $target);
        $terrainKind = (string) (($this->battles->findBattleTerrain($battleId)['kind'] ?? ''));
        $weatherKind = (string) (($this->battles->findBattleWeather($battleId)['kind'] ?? ''));
        $weatherFamily = BattleAbilityCatalog::weatherFamily($weatherKind);
        if (BattleAbilityCatalog::key($target) === 'leaf_guard' && $weatherFamily === 'sun' && $this->isPersistentStatus($statusId)) {
            return true;
        }
        if ($terrainKind === 'misty' && $this->isPersistentStatus($statusId)) {
            return true;
        }
        if ($terrainKind === 'electric' && $statusId === 2) {
            return true;
        }

        return match ($statusId) {
            1 => in_array('poison', $types, true) || in_array('steel', $types, true),
            2 => false,
            3 => in_array('fire', $types, true),
            4 => in_array('ice', $types, true),
            5 => in_array('electric', $types, true) || (strtolower($moveType) === 'electric' && in_array('ground', $types, true)),
            8 => in_array('grass', $types, true),
            default => false,
        };
    }

    private function isSwitchBlocked(int $battleId, array $pokemon): bool
    {
        $battlePokemon = (string) ($pokemon['battle_pokemon'] ?? '');
        return $this->battles->hasBattleVolatile($battleId, $battlePokemon, 'trap')
            || $this->battles->hasBattleVolatile($battleId, $battlePokemon, 'partial_trap');
    }

    private function deleteExpiredBattleEffects(int $battleId, int $round): void
    {
        $this->battles->deleteExpiredBattleStatuses($battleId, $round);
        $this->battles->deleteExpiredBattleEffects($battleId, $round);
    }

    private function statusName(int $statusId): string
    {
        return match ($statusId) {
            1 => 'Отравлен',
            2 => 'Усыплен',
            3 => 'В огне',
            4 => 'Заморожен',
            5 => 'Парализован',
            6 => 'Напуган',
            7 => 'Спутан',
            8 => 'Растения-пиявки',
            9 => 'Проклят',
            default => 'Статус #' . $statusId,
        };
    }

    private function criticChance(string $critic): int
    {
        $value = (int) $critic;
        return match (true) {
            $value >= 7 => 12,
            $value <= 0 => 0,
            default => 6,
        };
    }

    private function whoActsFirstForPokemon(int $battleId, array $first, array $second, array $firstMove, array $secondMove): bool
    {
        $p1 = (int) ($firstMove['priorety'] ?? 0);
        $p2 = (int) ($secondMove['priorety'] ?? 0);
        if ($p1 !== $p2) {
            return $p1 > $p2;
        }

        $firstQuick = $this->heldItemQuickClawActivated($first);
        $secondQuick = $this->heldItemQuickClawActivated($second);
        if ($firstQuick !== $secondQuick) {
            return $firstQuick;
        }

        return $this->whoActsFirst(
            $battleId,
            $firstMove,
            $secondMove,
            $this->effectiveStatFor($battleId, $first, 'speed', 'speed'),
            $this->effectiveStatFor($battleId, $second, 'speed', 'speed')
        );
    }

    private function whoActsFirst(int $battleId, array $firstMove, array $secondMove, int $firstSpeed, int $secondSpeed): bool
    {
        $p1 = (int) ($firstMove['priorety'] ?? 0);
        $p2 = (int) ($secondMove['priorety'] ?? 0);
        if ($p1 !== $p2) {
            return $p1 > $p2;
        }
        if ($firstSpeed !== $secondSpeed) {
            return $this->battles->hasBattleRoom($battleId, 'trick_room')
                ? $firstSpeed <= $secondSpeed
                : $firstSpeed >= $secondSpeed;
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

    private function enemyMoveForBattle(int $battleId, array $enemy): array
    {
        $bossMoves = $this->bosses?->activeMovesForBattle($battleId) ?? [];
        if ($bossMoves !== []) {
            return $this->randomMove($bossMoves);
        }

        return $this->randomMove($this->battles->findAvailableMoves(
            (int) ($enemy['basenum'] ?? 0),
            (int) ($enemy['lvl'] ?? 1)
        ));
    }

    private function decorateBossState(array $state): array
    {
        return $this->bosses !== null ? $this->bosses->decorateBattleState($state) : $state;
    }

    /** @return array{weather:array{kind:string,family:string,name:string,turns:int,roundEnd:int},terrain:array{kind:string,name:string,turns:int,roundEnd:int}} */
    private function battleEnvironment(int $battleId, int $round): array
    {
        $weather = $this->battles->findBattleWeather($battleId);
        $terrain = $this->battles->findBattleTerrain($battleId);
        $weatherKind = (string) ($weather['kind'] ?? '');
        $terrainKind = (string) ($terrain['kind'] ?? '');
        $permanentWeather = in_array($weatherKind, ['heavy_rain', 'harsh_sun', 'strong_winds'], true);

        return [
            'weather' => [
                'kind' => $weatherKind,
                'family' => BattleAbilityCatalog::weatherFamily($weatherKind),
                'name' => $this->weatherLabel($weatherKind),
                'turns' => ($weatherKind !== '' && !$permanentWeather) ? max(0, (int) ($weather['roundEnd'] ?? 0) - $round) : 0,
                'roundEnd' => (int) ($weather['roundEnd'] ?? 0),
            ],
            'terrain' => [
                'kind' => $terrainKind,
                'name' => $this->terrainLabel($terrainKind),
                'turns' => $terrainKind !== '' ? max(0, (int) ($terrain['roundEnd'] ?? 0) - $round) : 0,
                'roundEnd' => (int) ($terrain['roundEnd'] ?? 0),
            ],
        ];
    }

    private function weatherLabel(string $kind): string
    {
        return match ($kind) {
            'rain' => 'Дождь',
            'heavy_rain' => 'Сильный ливень',
            'sun' => 'Солнечная погода',
            'harsh_sun' => 'Жаркое солнце',
            'sandstorm' => 'Песчаная буря',
            'hail' => 'Град',
            'strong_winds' => 'Сильный ветер',
            default => 'Поле боя',
        };
    }

    private function terrainLabel(string $kind): string
    {
        return match ($kind) {
            'electric' => 'Электрическая арена',
            'misty' => 'Туманная арена',
            'grassy' => 'Травяная арена',
            default => '',
        };
    }

    private function findMoveById(array $moves, int $moveId): ?array
    {
        if ($moveId <= 0) {
            return null;
        }
        foreach ($moves as $move) {
            if ((int) ($move['id'] ?? 0) === $moveId) {
                return $move;
            }
        }
        return null;
    }

    private function randomMove(array $moves): array
    {
        if ($moves === []) {
            return [
                'id' => 33,
                'atac_name' => 'Tackle',
                'atac_power' => 40,
                'atac_accuracy' => 100,
                'atac_tip' => 'Normal',
                'atac_categori' => 1,
                'critic' => 6,
                'priorety' => 0,
            ];
        }
        return $moves[array_rand($moves)];
    }

    private function formatPokemon(array $pokemon): array
    {
        $baseNum = (int) ($pokemon['basenum'] ?? 0);
        return [
            'id' => (int) ($pokemon['id'] ?? 0),
            'baseNum' => $baseNum,
            'formId' => $baseNum,
            'dexNumber' => PokemonFormCatalog::displayBaseId($baseNum),
            'displayBaseNum' => PokemonFormCatalog::displayBaseId($baseNum),
            'formKey' => PokemonFormCatalog::formKey($baseNum, (string) ($pokemon['names'] ?? '')),
            'isForm' => PokemonFormCatalog::isForm($baseNum),
            'battleTransformation' => [
                'active' => (int) ($pokemon['battle_form_id'] ?? 0) > 0,
                'formId' => (int) ($pokemon['battle_form_id'] ?? 0),
                'originalBaseNum' => (int) ($pokemon['original_basenum'] ?? $baseNum),
                'type' => (string) ($pokemon['battle_form_type'] ?? ''),
                'key' => (string) ($pokemon['battle_form_key'] ?? ''),
                'label' => (string) ($pokemon['battle_form_label'] ?? ''),
                'originalName' => strip_tags((string) ($pokemon['original_name'] ?? '')),
            ],
            'name' => strip_tags((string) ($pokemon['names'] ?? 'Pokemon')),
            'level' => (int) ($pokemon['lvl'] ?? 1),
            'hp' => max(0, (int) ($pokemon['hp_my'] ?? 0)),
            'hpMax' => max(1, (int) ($pokemon['hp_max'] ?? 1)),
            'tips' => (string) ($pokemon['tips'] ?? 'normal'),
            'stats' => [
                'atk' => (int) ($pokemon['atk'] ?? 0),
                'def' => (int) ($pokemon['def'] ?? 0),
                'satk' => (int) ($pokemon['satk'] ?? 0),
                'sdef' => (int) ($pokemon['sdef'] ?? 0),
                'speed' => (int) ($pokemon['speed'] ?? 0),
            ],
            'statuses' => is_array($pokemon['statuses'] ?? null) ? $pokemon['statuses'] : [],
            'movesPreview' => $this->formatMoves($pokemon),
            'types' => $this->math->typeLabelsFromPokemon($pokemon),
            'majorStatuses' => is_array($pokemon['majorStatuses'] ?? null) ? $pokemon['majorStatuses'] : [],
            'sprites' => $this->battlePokemonSprites($baseNum, (string) ($pokemon['names'] ?? '')),
            'heldItem' => [
                'id' => (int) ($pokemon['held_item_id'] ?? 0),
                'name' => strip_tags((string) ($pokemon['held_item_name'] ?? '')),
            ],
            'status' => '',
        ];
    }

    /** @return array<string,string> */
    private function battlePokemonSprites(int $baseId, string $name): array
    {
        $spriteId = $this->battleSpriteId($baseId, $name);
        if ($spriteId <= 0) {
            return [];
        }

        $paths = [
            'front' => '/Pok/spriteanim/' . $spriteId . '.gif',
            'back' => '/Pok/back/' . $spriteId . '.gif',
            'frontShiny' => '/Pok/shiny/' . $spriteId . '.gif',
            'backShiny' => '/Pok/sback/' . $spriteId . '.gif',
            'sprite' => '/Pok/spriteanim/' . $spriteId . '.gif',
            'sback' => '/Pok/sback/' . $spriteId . '.gif',
        ];

        $result = [];
        foreach ($paths as $key => $path) {
            if (!defined('APP_ROOT') || is_file(APP_ROOT . $path)) {
                $result[$key] = $path;
            }
        }
        return $result;
    }

    private function battleSpriteId(int $baseId, string $name): int
    {
        if (in_array($baseId, [5017, 5018, 5019], true)) {
            return $baseId;
        }

        $lower = strtolower($name);
        if (str_contains($lower, 'kyogre') && (str_contains($lower, 'primal') || str_contains($lower, 'праймал'))) {
            return 5017;
        }
        if (str_contains($lower, 'groudon') && (str_contains($lower, 'primal') || str_contains($lower, 'праймал'))) {
            return 5018;
        }
        if (str_contains($lower, 'rayquaza') && str_contains($lower, 'mega')) {
            return 5019;
        }

        return 0;
    }

    private function formatMoves(array $pokemon): array
    {
        $moves = $this->movesForPokemon($pokemon);
        $result = [];
        foreach (array_slice($moves, 0, 4) as $move) {
            $moveId = (int) ($move['id'] ?? 0);
            $description = trim(strip_tags((string) (
                ($move['atac_tittle'] ?? '')
                ?: ($move['titles'] ?? '')
                ?: ($move['tittle_effect'] ?? '')
            )));
            $result[] = [
                'id' => $moveId,
                'name' => (string) ($move['atac_name'] ?? ('Атака #' . $moveId)),
                'power' => (int) ($move['atac_power'] ?? 0),
                'accuracy' => (int) ($move['atac_accuracy'] ?? 0),
                'type' => (string) ($move['atac_tip'] ?? 'Normal'),
                'category' => (int) ($move['atac_categori'] ?? 1),
                'categoryName' => $this->moveCategoryName((int) ($move['atac_categori'] ?? 1)),
                'pp' => isset($move['pp_min']) ? max(0, (int) $move['pp_min']) : max(0, (int) ($move['atac_pp'] ?? 0)),
                'ppMax' => isset($move['pp_max']) ? max(0, (int) $move['pp_max']) : max(0, (int) ($move['atac_pp'] ?? 0)),
                'description' => $description,
                'details' => trim(strip_tags((string) (($move['tittle_effect'] ?? '') ?: ($move['titles'] ?? '')))),
                'effects' => $this->formatMoveEffects($moveId, (string) ($move['atac_name'] ?? '')),
            ];
        }
        return $result;
    }

    private function moveCategoryName(int $category): string
    {
        return match ($category) {
            1 => 'Физическая',
            2 => 'Специальная',
            default => 'Статусная',
        };
    }

    /** @return list<array<string,mixed>> */
    private function formatMoveEffects(int $moveId, string $moveName): array
    {
        if ($moveId <= 0) {
            return [];
        }

        $effects = [];
        $rawEffects = array_merge($this->battles->findMoveStatEffects($moveId), $this->battles->findMoveSecondaryEffects($moveId));
        if ($this->battles->findMoveStatEffects($moveId) === []) {
            $rawEffects = array_merge($rawEffects, BattleMoveEffectCatalog::statEffects($moveName));
        }
        $primary = BattleMoveEffectCatalog::primaryStatus($moveId, $moveName);
        if ($primary !== null) {
            $rawEffects[] = [
                'target' => 'enemy',
                'kind' => 'status',
                'field' => 'status',
                'delta' => 0,
                'statusId' => (int) $primary['statusId'],
                'chance' => (int) $primary['chance'],
            ];
        }
        foreach (BattleMoveEffectCatalog::secondaryStatuses($moveId, $moveName) as $statusEffect) {
            $rawEffects[] = [
                'target' => 'enemy',
                'kind' => 'status',
                'field' => 'status',
                'delta' => 0,
                'statusId' => (int) $statusEffect['statusId'],
                'chance' => (int) $statusEffect['chance'],
            ];
        }

        foreach ($rawEffects as $effect) {
            $field = (string) ($effect['field'] ?? '');
            $statusId = (int) ($effect['statusId'] ?? 0);
            $kind = (string) ($effect['kind'] ?? '');
            $delta = (int) ($effect['delta'] ?? 0);

            $effects[] = [
                'target' => (string) ($effect['target'] ?? 'enemy'),
                'kind' => $kind,
                'field' => $field,
                'label' => $statusId > 0 ? $this->battleStatusLabel($statusId) : $this->stageLabel($field),
                'value' => $statusId > 0 ? '' : (($kind === 'plus' ? '+' : '-') . max(1, $delta)),
                'chance' => isset($effect['chance']) ? (int) $effect['chance'] : 100,
                'statusId' => $statusId,
            ];
        }

        $specials = [
            [BattleMoveEffectCatalog::hazardKind($moveId, $moveName), 'Полевая ловушка'],
            [BattleMoveEffectCatalog::trapKind($moveId, $moveName), 'Запрет смены'],
            [BattleMoveEffectCatalog::sideFieldKind($moveId, $moveName), 'Урон полем'],
            [BattleMoveEffectCatalog::screenKind($moveId, $moveName), 'Экран'],
            [BattleMoveEffectCatalog::volatileKind($moveId, $moveName), 'Особый эффект'],
            [BattleMoveEffectCatalog::recoilKind($moveId, $moveName), 'Отдача'],
        ];
        foreach ($specials as [$kind, $label]) {
            if ($kind !== null && $kind !== '') {
                $effects[] = [
                    'target' => 'enemy',
                    'kind' => 'special',
                    'field' => (string) $kind,
                    'label' => $label,
                    'value' => '',
                    'chance' => 100,
                    'statusId' => 0,
                ];
            }
        }
        if (BattleMoveEffectCatalog::weather($moveId, $moveName) !== null) {
            $effects[] = [
                'target' => 'field',
                'kind' => 'special',
                'field' => 'weather',
                'label' => 'Погода',
                'value' => '',
                'chance' => 100,
                'statusId' => 0,
            ];
        }
        if (BattleMoveEffectCatalog::terrain($moveId, $moveName) !== null) {
            $effects[] = [
                'target' => 'field',
                'kind' => 'special',
                'field' => 'terrain',
                'label' => 'Арена',
                'value' => '',
                'chance' => 100,
                'statusId' => 0,
            ];
        }
        if (BattleMoveEffectCatalog::room($moveId, $moveName) !== null) {
            $effects[] = [
                'target' => 'field',
                'kind' => 'special',
                'field' => 'room',
                'label' => 'Комната',
                'value' => '',
                'chance' => 100,
                'statusId' => 0,
            ];
        }

        return $effects;
    }

    private function stageLabel(string $field): string
    {
        return match ($field) {
            'attac' => 'Атака',
            'spattac' => 'Спец. атака',
            'defend' => 'Защита',
            'spdefend' => 'Спец. защита',
            'speed' => 'Скорость',
            'acc' => 'Ловкость',
            'accuracy' => 'Точность',
            default => 'Параметр',
        };
    }

    private function battleStatusLabel(int $statusId): string
    {
        return match ($statusId) {
            1 => 'Отравление',
            2 => 'Сон',
            3 => 'Ожог',
            4 => 'Заморозка',
            5 => 'Паралич',
            6 => 'Страх',
            7 => 'Спутанность',
            8 => 'Растения-пиявки',
            9 => 'Проклятие',
            default => 'Статус #' . $statusId,
        };
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
                'baseNum' => (int) ($pokemon['basenum'] ?? 0),
                'dexNumber' => PokemonFormCatalog::displayBaseId((int) ($pokemon['basenum'] ?? 0)),
                'displayBaseNum' => PokemonFormCatalog::displayBaseId((int) ($pokemon['basenum'] ?? 0)),
                'name' => strip_tags((string) ($pokemon['names'] ?? ('Pokemon #' . $id))),
                'level' => (int) ($pokemon['lvl'] ?? 1),
                'tips' => (string) ($pokemon['tips'] ?? ''),
                'gender' => (string) ($pokemon['sex'] ?? ''),
                'hp' => $hp,
                'hpMax' => max(1, (int) ($pokemon['hp_max'] ?? 1)),
                'sprites' => $this->battlePokemonSprites((int) ($pokemon['basenum'] ?? 0), (string) ($pokemon['names'] ?? '')),
                'heldItemId' => (int) ($pokemon['held_item_id'] ?? 0),
                'heldItemName' => strip_tags((string) ($pokemon['held_item_name'] ?? '')),
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

    private function attachBattleStatuses(int $battleId, ?array &$player, ?array &$enemy): void
    {
        if ($player !== null) {
            $bp = (string) ($player['battle_pokemon'] ?? '');
            $player['statuses'] = $this->battles->findBattleStatuses($battleId, $bp);
            $player['majorStatuses'] = $this->battles->findBattleMajorStatuses($battleId, $bp);
        }
        if ($enemy !== null) {
            $bp = (string) ($enemy['battle_pokemon'] ?? '');
            $enemy['statuses'] = $this->battles->findBattleStatuses($battleId, $bp);
            $enemy['majorStatuses'] = $this->battles->findBattleMajorStatuses($battleId, $bp);
        }
    }
}
