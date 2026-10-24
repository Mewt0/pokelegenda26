<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BattleReplayRepository;

/**
 * Состояние PvP-боя для клиента: бойцы, погода и террейн, атаки активного покемона,
 * варианты смены, лог по раундам.
 *
 * Логика перенесена из BattleEngineService без изменений (TASK-047, шаг 7). Форматтеры
 * (formatPokemon, formatMoves, groupLogByRound и остальные) пока живут в движке — их
 * вынос в отдельный презентер остаётся следующим шагом.
 */
final class BattlePvpService
{
    public function __construct(
        private BattleRepository $battles,
        private ?BattleReplayRepository $replay,
        private BattleEngineService $engine,
    ) {
    }

public function pvpState(int $userId, int $battleId): array
    {
        $battle = $this->battles->findPvpBattleForUser($userId, $battleId);
        if ($battle === null) {
            return ['ok' => true, 'active' => false];
        }

        $this->engine->deleteExpiredBattleEffects($battleId, (int) ($battle['raund'] ?? 1));

        $side = (int) ($battle['user_2'] ?? 0) === $userId ? 2 : 1;
        $opponentUserId = $side === 1 ? (int) ($battle['user_2'] ?? 0) : (int) ($battle['user_1'] ?? 0);
        $player = $this->battles->findPokemon((string) ($battle['poke_' . $side] ?? ''));
        $enemy = $this->battles->findPokemon((string) ($battle['poke_' . ($side === 1 ? 2 : 1)] ?? ''));
        $this->engine->attachBattleStatuses((int) ($battle['id'] ?? 0), $player, $enemy);

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
            $this->engine->applyEntryWeatherAbilities($battleId, (int) ($battle['raund'] ?? 1), $player, $enemy);
        }
        $ownAction = (int) ($battle['attac_' . $side] ?? 0);
        $enemyAction = (int) ($battle['attac_' . ($side === 1 ? 2 : 1)] ?? 0);
        $ownActionSet = $ownAction !== 0;
        $enemyActionSet = $enemyAction !== 0;
        $opponentLogin = $this->battles->findUserLoginById($opponentUserId);
        $logRows = $this->engine->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
        $playerData = $this->engine->formatPokemon($player);
        $enemyData = $this->engine->formatPokemon($enemy);
        $environment = $this->engine->battleEnvironment((int) $battle['id'], (int) ($battle['raund'] ?? 1));
        $enemyData['trainer'] = [
            'id' => $opponentUserId,
            'login' => $opponentLogin,
        ];
        $this->replay?->recordSnapshot(
            (int) $battle['id'],
            (int) ($battle['raund'] ?? 1),
            'pvp_state',
            $battle,
            $player,
            $enemy,
            $environment,
            ['viewer_id' => $userId, 'mode' => 'pvp', 'side' => $side, 'finished' => $finished]
        );

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
            'moves' => $this->engine->formatMoves($player),
            'switchOptions' => $finished ? [] : $this->engine->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)),
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
                'moves' => $this->engine->formatMoves($player),
                'switchOptions' => $finished ? [] : $this->engine->formatSwitchOptions($userId, (int) ($player['id'] ?? 0)),
                'log' => $logRows,
                'logByRound' => $this->engine->groupLogByRound($logRows),
            ],
        ];
    }
}
