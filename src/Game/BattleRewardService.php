<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use Pokemon8\Repository\BattleRepository;
use Pokemon8\Repository\BossRepository;
use Pokemon8\Repository\RewardRepository;
use Pokemon8\Repository\SafeStorageRepository;

/**
 * Награда за победу в PvE: монеты, опыт, EV, счастье, дропы, значок гим-лидера,
 * уведомление, запись rollback в safe storage и quest-хук.
 *
 * Логика перенесена из BattleEngineService без изменений (TASK-047, шаг 2). Движок
 * остаётся фасадом и отдаёт вспомогательные методы: значок, quest-хук и лог.
 */
final class BattleRewardService
{
    public function __construct(
        private BattleRepository $battles,
        private ?RewardRepository $rewards,
        private ?BossRepository $bosses,
        private ?SafeStorageRepository $safeStorage,
        private BattleEngineService $engine,
    ) {
    }

public function grantPveVictoryRewards(
        int $userId,
        array $battle,
        array $player,
        array $enemy,
        int $currentRound,
        array &$messages,
        ?array $bossAdvance
    ): array {
        if (!$this->battles->claimPveBattleFinish((int) $battle['id'], $userId, $userId)) {
            return [
                'rewards' => ['coins' => 0, 'exp' => 0, 'drops' => []],
                'logRows' => null,
                'alreadyFinished' => $this->engine->alreadyFinishedPveResponse($userId, 'Бой уже завершён, награда уже была обработана.'),
            ];
        }
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
        $gymBadge = $this->engine->grantGymBadgeForPveVictory($userId, $battle, $enemy, $currentRound);
        if ($gymBadge !== null) {
            $rewards['gymBadge'] = $gymBadge;
            $badgeMessage = 'Получен значок гим-лидера: ' . (string) ($gymBadge['title'] ?? 'Значок') . '.';
            if ((string) ($gymBadge['leader'] ?? '') !== '') {
                $badgeMessage .= ' Лидер: ' . (string) $gymBadge['leader'] . '.';
            }
            $messages[] = $badgeMessage;
            $this->battles->insertBattleLog((int) $battle['id'], $currentRound, $badgeMessage);
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
        $this->safeStorage?->recordRollback(
            'pve_battle_reward:' . (int) $battle['id'],
            'battle_reward',
            $userId,
            'battle',
            (int) $battle['id'],
            [
                'battle_id' => (int) $battle['id'],
                'player_pokemon_id' => (int) ($player['id'] ?? 0),
                'enemy_pokemon_id' => (int) ($enemy['id'] ?? 0),
                'round' => $currentRound,
            ],
            [
                'result' => 'win',
                'rewards' => $rewards,
                'effort' => $effort,
            ],
            [
                'manual_review' => true,
                'reverse_coins' => (int) ($rewards['coins'] ?? 0),
                'reverse_drops' => $drops,
                'reverse_exp' => (int) ($effort['exp'] ?? 0),
                'reverse_happiness' => (int) ($rewards['happiness'] ?? 0),
            ],
            'recorded'
        );
        $questMessage = $this->engine->completeFirstBattleQuest($userId, (int) $battle['id'], 'win');
        if ($questMessage !== null) {
            $messages[] = $questMessage;
            $this->battles->insertBattleLog((int) $battle['id'], $currentRound, $questMessage);
        }
        $finalLogRows = $this->engine->formatLogRows($this->battles->getBattleLog((int) $battle['id']));
        return ['rewards' => $rewards, 'logRows' => $finalLogRows, 'alreadyFinished' => null];
    }
}
