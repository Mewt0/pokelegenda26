<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;

final class BattleEngineService
{
    private BattleMathService $math;

    public function __construct(private BattleRepository $battles)
    {
        $this->math = new BattleMathService();
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

        $this->battles->deleteExpiredBattleStatuses($battleId, (int) ($battle['raund'] ?? 1));

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

        $log = $this->battles->getBattleLog((int) $battle['id']);
        $logRows = $this->formatLogRows($log);
        $winner = (int) ($battle['pobeda'] ?? 0);
        $finished = $winner !== 0;
        $result = null;
        if ($finished) {
            $result = $winner === $userId ? 'win' : ($winner === -2 ? 'escape' : 'lose');
        }
        $moves = $finished ? [] : $this->formatMoves($player);
        $switchOptions = $finished ? [] : $this->formatSwitchOptions($userId, (int) ($player['id'] ?? 0));

        return [
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
            'battle' => [
                'id' => (int) $battle['id'],
                'round' => (int) ($battle['raund'] ?? 1),
                'finished' => $finished,
                'result' => $result,
                'player' => $this->formatPokemon($player),
                'enemy' => $this->formatPokemon($enemy),
                'moves' => $moves,
                'switchOptions' => $switchOptions,
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
        $this->battles->cleanupFinishedBattleForUser($userId);
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
        $this->battles->deleteExpiredBattleStatuses($battleId, (int) ($battle['raund'] ?? 1));

        $player = $this->battles->findPokemon((string) ($battle['poke_1'] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_2'] ?? ''));
        [$player, $enemy] = $this->fallbackCombatants($userId, $battle, $player, $enemy);
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
        if ($player === null || $enemy === null) {
            return ['ok' => false, 'active' => false, 'message' => 'Не удалось получить покемонов.'];
        }

        $playerMove = $this->selectMove($this->movesForPokemon($player), $moveId);
        $enemyMove = $this->randomMove($this->battles->findAvailableMoves((int) ($enemy['basenum'] ?? 0), (int) ($enemy['lvl'] ?? 1)));
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
        $playerFirst = $this->whoActsFirst(
            $playerMove,
            $enemyMove,
            $this->effectiveStatFor($battleId, $player, 'speed', 'speed'),
            $this->effectiveStatFor($battleId, $enemy, 'speed', 'speed')
        );

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
        $rewards = ['coins' => 0, 'exp' => 0];
        $currentRound = (int) ($battle['raund'] ?? 1);
        $finalLogRows = null;
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
            $finalLogRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            $this->battles->finishBattle((int) $battle['id'], $userId, $userId);
        } elseif ((int) $player['hp_my'] <= 0) {
            $finished = true;
            $result = 'lose';
            $finalLogRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
            $this->battles->finishBattle((int) $battle['id'], $userId, -1);
        } else {
            $this->battles->incrementRoundAndResetActions((int) $battle['id']);
        }

        if ($finished) {
            $logRows = $finalLogRows ?? [];
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
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
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
        $this->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);
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
            $this->battles->decrementInventoryItemRow($userId, $itemUserId);
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
        $logRows = $this->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
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
                'log' => $logRows,
                'logByRound' => $this->groupLogByRound($logRows),
            ],
        ];
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

        $statusGate = $this->applyStartOfTurnStatus($battleId, $round, $attacker);
        foreach ($statusGate['messages'] as $m) {
            $parts[] = $m;
        }
        if (!$statusGate['canAct']) {
            return implode(' ', $parts);
        }

        $hitChance = $this->effectiveAccuracy($battleId, $attacker, $defender, $move);
        if (random_int(1, 100) > $hitChance) {
            return trim(implode(' ', $parts) . ' ' . sprintf('%s использует %s — промах!', $attackerName, $moveName));
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
            $text = $this->applyStageEffect($battleId, $attacker, $defender, $effect);
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        // Status from atac_not for pure status moves.
        $primaryStatus = $this->battles->findMovePrimaryStatusId($moveId);
        if ($primaryStatus > 0 && ($category >= 3 || $power <= 0)) {
            $statusText = $this->tryApplyStatus($battleId, $round, $defender, $primaryStatus, 100);
            if ($statusText !== '') {
                $parts[] = $statusText;
            }
        }

        // Secondary effects from attac_dop after a successful hit.
        foreach ($this->battles->findMoveSecondaryEffects($moveId) as $effect) {
            $chance = max(1, min(100, (int) ($effect['chance'] ?? 100)));
            if (random_int(1, 100) > $chance) {
                continue;
            }
            if (($effect['kind'] ?? '') === 'status') {
                $target = ($effect['target'] ?? 'enemy') === 'self' ? $attacker : $defender;
                $statusText = $this->tryApplyStatus($battleId, $round, $target, (int) ($effect['statusId'] ?? 0), $chance);
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

        return implode(' ', array_values(array_filter($parts)));
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

        $atk = $this->effectiveStatFor($battleId, $attacker, $atkDbField, $atkStageField);
        $def = $this->effectiveStatFor($battleId, $defender, $defDbField, $defStageField);
        if (!$isSpecial && $this->hasMajorStatus($battleId, $attacker, 3)) {
            // Burn halves physical attack.
            $atk = max(1, (int) floor($atk / 2));
        }

        $lvl = max(1, (int) ($attacker['lvl'] ?? 1));
        $moveType = (string) ($move['atac_tip'] ?? 'Normal');
        $attackerTypes = $this->math->typeLabelsFromPokemon($attacker);
        $defenderTypes = $this->math->typeLabelsFromPokemon($defender);
        $stab = $this->math->stab($moveType, $attackerTypes);
        $typeEffect = $this->math->typeEffectiveness($moveType, $defenderTypes);
        if ($typeEffect <= 0.0) {
            return sprintf('%s использует %s. %s не получает урона. %s', $attackerName, $moveName, $defenderName, $this->math->typeMessage($typeEffect));
        }

        $base = (((2 * $lvl / 5 + 2) * $power * $atk / max(1, $def)) / 50) + 2;
        $rand = random_int(85, 100) / 100;
        $critChance = $this->criticChance((string) ($move['critic'] ?? '3'));
        $isCrit = random_int(1, 100) <= $critChance;
        $crit = $isCrit ? 1.5 : 1.0;
        $damage = max(1, (int) floor($base * $stab * $typeEffect * $rand * $crit));

        $defender['hp_my'] = max(0, (int) ($defender['hp_my'] ?? 0) - $damage);
        $hpLeft = (int) ($defender['hp_my'] ?? 0);
        $hpMax = max(1, (int) ($defender['hp_max'] ?? 1));
        $typeText = $this->math->typeMessage($typeEffect);
        $critText = $isCrit ? ' КРИТ!' : '';
        $tail = trim($critText . ' ' . $typeText);
        $tail = $tail !== '' ? ' ' . $tail : '';

        return sprintf('%s использует %s.%s %s теряет %d HP (%d/%d).', $attackerName, $moveName, $tail, $defenderName, $damage, $hpLeft, $hpMax);
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

    private function tryApplyStatus(int $battleId, int $round, array $target, int $statusId, int $chance): string
    {
        if ($statusId <= 0 || random_int(1, 100) > max(1, min(100, $chance))) {
            return '';
        }
        $targetName = strip_tags((string) ($target['names'] ?? 'Покемон'));
        $battlePokemon = (string) ($target['battle_pokemon'] ?? '');
        if ($this->battles->hasBattleStatus($battleId, $battlePokemon, $statusId)) {
            return sprintf('%s уже имеет этот статус.', $targetName);
        }
        $duration = match ($statusId) {
            2 => random_int(2, 4),
            4 => random_int(1, 3),
            6 => 1,
            7 => random_int(2, 5),
            default => 999999,
        };
        $ok = $this->battles->applyBattleStatus($battleId, $battlePokemon, $statusId, $round, $duration);
        if (!$ok) {
            return '';
        }
        return sprintf('%s получает статус: %s.', $targetName, $this->statusName($statusId));
    }

    /** @return array{canAct:bool,messages:list<string>} */
    private function applyStartOfTurnStatus(int $battleId, int $round, array &$actor): array
    {
        $name = strip_tags((string) ($actor['names'] ?? 'Покемон'));
        $battlePokemon = (string) ($actor['battle_pokemon'] ?? '');
        $messages = [];
        $canAct = true;

        foreach ($this->battles->findBattleMajorStatuses($battleId, $battlePokemon) as $status) {
            $id = (int) ($status['id'] ?? 0);
            if ($id === 1) { // poison
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 8));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s страдает от яда и теряет %d HP.', $name, $damage);
            } elseif ($id === 3) { // burn
                $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 16));
                $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                $messages[] = sprintf('%s получает урон от ожога: %d HP.', $name, $damage);
            } elseif ($id === 2) { // sleep
                if (random_int(1, 100) <= 65) {
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
                if (random_int(1, 100) <= 33) {
                    $damage = max(1, (int) floor(max(1, (int) ($actor['hp_max'] ?? 1)) / 10));
                    $actor['hp_my'] = max(0, (int) ($actor['hp_my'] ?? 0) - $damage);
                    $canAct = false;
                    $messages[] = sprintf('%s спутан и ранит себя на %d HP.', $name, $damage);
                }
            }
            if ((int) ($actor['hp_my'] ?? 0) <= 0) {
                $canAct = false;
                break;
            }
        }

        return ['canAct' => $canAct, 'messages' => $messages];
    }

    private function statMoveEffects(string $moveName): array
    {
        $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $moveName) ?? $moveName));
        $key = preg_replace('/\s+/', ' ', $key) ?? $key;

        $effects = [
            'growl' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака']],
            'tail whip' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'leer' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'string shot' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'speed', 'delta' => 2, 'label' => 'Скорость']],
            'screech' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'defend', 'delta' => 2, 'label' => 'Защита']],
            'charm' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'attac', 'delta' => 2, 'label' => 'Атака']],
            'fake tears' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'spdefend', 'delta' => 2, 'label' => 'Спец. Защита']],
            'metal sound' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'spdefend', 'delta' => 2, 'label' => 'Спец. Защита']],
            'sand attack' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'accuracy', 'delta' => 1, 'label' => 'Точность']],
            'smokescreen' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'accuracy', 'delta' => 1, 'label' => 'Точность']],
            'sweet scent' => [['target' => 'enemy', 'kind' => 'minus', 'field' => 'acc', 'delta' => 2, 'label' => 'Ловкость']],

            'swords dance' => [['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 2, 'label' => 'Атака']],
            'calm mind' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 1, 'label' => 'Спец. Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'spdefend', 'delta' => 1, 'label' => 'Спец. Защита'],
            ],
            'nasty plot' => [['target' => 'self', 'kind' => 'plus', 'field' => 'spattac', 'delta' => 2, 'label' => 'Спец. Атака']],
            'agility' => [['target' => 'self', 'kind' => 'plus', 'field' => 'speed', 'delta' => 2, 'label' => 'Скорость']],
            'iron defense' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 2, 'label' => 'Защита']],
            'harden' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'defense curl' => [['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита']],
            'bulk up' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'defend', 'delta' => 1, 'label' => 'Защита'],
            ],
            'dragon dance' => [
                ['target' => 'self', 'kind' => 'plus', 'field' => 'attac', 'delta' => 1, 'label' => 'Атака'],
                ['target' => 'self', 'kind' => 'plus', 'field' => 'speed', 'delta' => 1, 'label' => 'Скорость'],
            ],
            'double team' => [['target' => 'self', 'kind' => 'plus', 'field' => 'acc', 'delta' => 1, 'label' => 'Ловкость']],
        ];

        return $effects[$key] ?? [];
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
            $value = max(1, (int) floor($value / 2));
        }
        return $value;
    }

    private function effectiveAccuracy(int $battleId, array $attacker, array $defender, array $move): int
    {
        $attackerStages = $this->battles->findBattleStatStageParts($battleId, (string) ($attacker['battle_pokemon'] ?? ''));
        $defenderStages = $this->battles->findBattleStatStageParts($battleId, (string) ($defender['battle_pokemon'] ?? ''));
        return $this->math->accuracyChance((int) ($move['atac_accuracy'] ?? 100), $attackerStages, $defenderStages);
    }

    private function hasMajorStatus(int $battleId, array $pokemon, int $statusId): bool
    {
        return $this->battles->hasBattleStatus($battleId, (string) ($pokemon['battle_pokemon'] ?? ''), $statusId);
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
        return [
            'id' => (int) ($pokemon['id'] ?? 0),
            'baseNum' => (int) ($pokemon['basenum'] ?? 0),
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
            'status' => '',
        ];
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
                'effects' => $this->formatMoveEffects($moveId),
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
    private function formatMoveEffects(int $moveId): array
    {
        if ($moveId <= 0) {
            return [];
        }

        $effects = [];
        foreach (array_merge($this->battles->findMoveStatEffects($moveId), $this->battles->findMoveSecondaryEffects($moveId)) as $effect) {
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
