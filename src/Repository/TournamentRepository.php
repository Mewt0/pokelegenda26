<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class TournamentRepository
{
    public function __construct(
        private PDO $db,
        private RewardRepository $rewards,
        private LocationRepository $locations,
    ) {
    }

    public function dashboardForUser(int $userId): array
    {
        if ($userId <= 0 || !$this->tableExists('admin_tournaments')) {
            return [
                'ok' => true,
                'serverTime' => time(),
                'timezone' => date_default_timezone_get(),
                'tournaments' => [],
            ];
        }

        $rows = $this->tournamentRows($userId);
        $tournaments = [];
        foreach ($rows as $row) {
            $tournaments[] = $this->formatTournament($row, $userId);
        }

        return [
            'ok' => true,
            'serverTime' => time(),
            'timezone' => date_default_timezone_get(),
            'tournaments' => $tournaments,
            'qaChecklist' => [
                ['title' => 'Расписание', 'expected' => 'Время сервера и дедлайн регистрации считаются на backend.'],
                ['title' => 'Взнос', 'expected' => 'Взнос списывается только внутри успешной регистрации.'],
                ['title' => 'Арена', 'expected' => 'Вход переносит игрока в location_id турнира, выход возвращает назад.'],
                ['title' => 'Награды', 'expected' => 'Награда и медаль выдаются один раз после статуса finished.'],
            ],
        ];
    }

    public function register(int $userId, int $tournamentId): array
    {
        if ($userId <= 0 || $tournamentId <= 0) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $tournament = $this->tournamentForUpdate($tournamentId);
            if (!$tournament) {
                throw new \RuntimeException('Турнир не найден.');
            }

            $now = time();
            if ((string) $tournament['status'] !== 'registration') {
                throw new \RuntimeException('Регистрация на этот турнир закрыта.');
            }
            $deadline = $this->registrationDeadline($tournament);
            if ($deadline > 0 && $now > $deadline) {
                throw new \RuntimeException('Дедлайн регистрации уже прошёл.');
            }

            $participant = $this->participantForUpdate($tournamentId, $userId);
            if ($participant && !in_array((string) $participant['status'], ['cancelled'], true)) {
                throw new \RuntimeException('Вы уже зарегистрированы на этот турнир.');
            }

            $maxParticipants = (int) ($tournament['max_participants'] ?? 0);
            if ($maxParticipants > 0 && $this->activeParticipantsCount($tournamentId) >= $maxParticipants) {
                throw new \RuntimeException('Лимит участников турнира уже заполнен.');
            }

            if (!$this->hasEligiblePokemon($userId, (int) $tournament['min_level'], (int) $tournament['max_level'])) {
                throw new \RuntimeException('Нет покемона подходящего уровня для участия.');
            }

            $feeItemId = (int) ($tournament['entry_fee_item_id'] ?? 0);
            $feeAmount = (int) ($tournament['entry_fee_amount'] ?? 0);
            if ($feeItemId > 0 && $feeAmount > 0) {
                $this->consumeItem($userId, $feeItemId, $feeAmount);
            }

            $state = $this->locations->findUserState($userId) ?: [];
            $returnLocation = (int) ($state['buildmy'] ?? 0);
            if ($participant) {
                $this->db->prepare(
                    'UPDATE admin_tournament_participants
                        SET status = "registered", pokemon_id = 0, score = 0, place_num = 0,
                            joined_at = :joined_at, fee_paid_at = :fee_paid_at, fee_refunded_at = 0,
                            checked_in_at = 0, left_at = 0, return_location_id = :return_location_id,
                            reward_claimed_at = 0, updated_at = :updated_at
                      WHERE id = :id'
                )->execute([
                    'joined_at' => $now,
                    'fee_paid_at' => ($feeItemId > 0 && $feeAmount > 0) ? $now : 0,
                    'return_location_id' => $returnLocation,
                    'updated_at' => $now,
                    'id' => (int) $participant['id'],
                ]);
            } else {
                $this->db->prepare(
                    'INSERT INTO admin_tournament_participants
                        (tournament_id, user_id, pokemon_id, status, score, place_num, joined_at,
                         fee_paid_at, fee_refunded_at, checked_in_at, left_at, return_location_id,
                         reward_claimed_at, updated_at)
                     VALUES
                        (:tournament_id, :user_id, 0, "registered", 0, 0, :joined_at,
                         :fee_paid_at, 0, 0, 0, :return_location_id, 0, :updated_at)'
                )->execute([
                    'tournament_id' => $tournamentId,
                    'user_id' => $userId,
                    'joined_at' => $now,
                    'fee_paid_at' => ($feeItemId > 0 && $feeAmount > 0) ? $now : 0,
                    'return_location_id' => $returnLocation,
                    'updated_at' => $now,
                ]);
            }

            $this->log($tournamentId, $userId, 'register', [
                'fee_item_id' => $feeItemId,
                'fee_amount' => $feeAmount,
                'return_location_id' => $returnLocation,
            ]);
            $this->rewards->notify($userId, 'Регистрация на турнир', 'Вы зарегистрированы на турнир "' . (string) $tournament['title'] . '".', 'success', [
                'source_type' => 'tournament',
                'source_id' => (string) $tournamentId,
            ]);

            if ($started) {
                $this->db->commit();
            }

            return ['ok' => true, 'message' => 'Регистрация завершена.', 'tournament' => $this->showForUser($userId, $tournamentId)];
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancel(int $userId, int $tournamentId): array
    {
        if ($userId <= 0 || $tournamentId <= 0) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $tournament = $this->tournamentForUpdate($tournamentId);
            $participant = $this->participantForUpdate($tournamentId, $userId);
            if (!$tournament || !$participant) {
                throw new \RuntimeException('Вы не зарегистрированы на этот турнир.');
            }
            if (in_array((string) $participant['status'], ['cancelled', 'eliminated', 'winner'], true)) {
                throw new \RuntimeException('Участие уже закрыто.');
            }
            if ((string) $tournament['status'] === 'active') {
                throw new \RuntimeException('Нельзя отменить участие после старта турнира.');
            }

            $now = time();
            $feeItemId = (int) ($tournament['entry_fee_item_id'] ?? 0);
            $feeAmount = (int) ($tournament['entry_fee_amount'] ?? 0);
            $refundAt = (int) ($participant['fee_refunded_at'] ?? 0);
            if ($feeItemId > 0 && $feeAmount > 0 && (int) ($participant['fee_paid_at'] ?? 0) > 0 && $refundAt <= 0) {
                $this->addItem($userId, $feeItemId, $feeAmount);
                $refundAt = $now;
            }

            $this->db->prepare(
                'UPDATE admin_tournament_participants
                    SET status = "cancelled", fee_refunded_at = :fee_refunded_at, updated_at = :updated_at
                  WHERE id = :id'
            )->execute([
                'fee_refunded_at' => $refundAt,
                'updated_at' => $now,
                'id' => (int) $participant['id'],
            ]);

            $returnLocation = (int) ($participant['return_location_id'] ?? 0);
            if ($returnLocation > 0 && (int) ($tournament['location_id'] ?? 0) > 0) {
                $state = $this->locations->findUserState($userId) ?: [];
                if ((int) ($state['buildmy'] ?? 0) === (int) $tournament['location_id']) {
                    $this->locations->updateUserLocation($userId, $returnLocation);
                }
            }

            $this->log($tournamentId, $userId, 'cancel', [
                'fee_item_id' => $feeItemId,
                'fee_amount' => $feeAmount,
                'refunded' => $refundAt > 0,
            ]);
            $this->rewards->notify($userId, 'Участие отменено', 'Участие в турнире "' . (string) $tournament['title'] . '" отменено.', 'info', [
                'source_type' => 'tournament',
                'source_id' => (string) $tournamentId,
            ]);

            if ($started) {
                $this->db->commit();
            }

            return ['ok' => true, 'message' => 'Участие отменено.', 'tournament' => $this->showForUser($userId, $tournamentId)];
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function enterArena(int $userId, int $tournamentId): array
    {
        if ($userId <= 0 || $tournamentId <= 0) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $tournament = $this->tournamentForUpdate($tournamentId);
            $participant = $this->participantForUpdate($tournamentId, $userId);
            if (!$tournament || !$participant || in_array((string) $participant['status'], ['cancelled', 'disqualified'], true)) {
                throw new \RuntimeException('Вы не зарегистрированы на этот турнир.');
            }
            if ((string) $tournament['status'] !== 'active') {
                throw new \RuntimeException('Арена откроется после старта турнира.');
            }

            $arenaId = (int) ($tournament['location_id'] ?? 0);
            if ($arenaId <= 0 || $this->locations->findLocation($arenaId) === null) {
                throw new \RuntimeException('Арена турнира не настроена.');
            }

            $state = $this->locations->findUserState($userId) ?: [];
            $returnLocation = (int) ($participant['return_location_id'] ?? 0);
            if ($returnLocation <= 0) {
                $returnLocation = (int) ($state['buildmy'] ?? 0);
            }

            $now = time();
            $this->locations->updateUserLocation($userId, $arenaId);
            $this->db->prepare(
                'UPDATE admin_tournament_participants
                    SET status = "checked_in", checked_in_at = IF(checked_in_at > 0, checked_in_at, :checked_in_at),
                        return_location_id = :return_location_id, updated_at = :updated_at
                  WHERE id = :id'
            )->execute([
                'checked_in_at' => $now,
                'return_location_id' => $returnLocation,
                'updated_at' => $now,
                'id' => (int) $participant['id'],
            ]);

            $this->log($tournamentId, $userId, 'arena.enter', [
                'arena_location_id' => $arenaId,
                'return_location_id' => $returnLocation,
            ]);

            if ($started) {
                $this->db->commit();
            }

            return ['ok' => true, 'message' => 'Вы перенесены на турнирную арену.', 'tournament' => $this->showForUser($userId, $tournamentId)];
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function leaveArena(int $userId, int $tournamentId): array
    {
        if ($userId <= 0 || $tournamentId <= 0) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $tournament = $this->tournamentForUpdate($tournamentId);
            $participant = $this->participantForUpdate($tournamentId, $userId);
            if (!$tournament || !$participant) {
                throw new \RuntimeException('Участие в турнире не найдено.');
            }

            $returnLocation = (int) ($participant['return_location_id'] ?? 0);
            $fallback = (int) ($tournament['arena_exit_location_id'] ?? 0);
            if ($returnLocation <= 0) {
                $returnLocation = $fallback > 0 ? $fallback : 1;
            }
            $this->locations->updateUserLocation($userId, $returnLocation);

            $now = time();
            $this->db->prepare(
                'UPDATE admin_tournament_participants
                    SET left_at = :left_at, updated_at = :updated_at
                  WHERE id = :id'
            )->execute([
                'left_at' => $now,
                'updated_at' => $now,
                'id' => (int) $participant['id'],
            ]);
            $this->log($tournamentId, $userId, 'arena.leave', ['return_location_id' => $returnLocation]);

            if ($started) {
                $this->db->commit();
            }

            return ['ok' => true, 'message' => 'Вы вышли с арены.', 'tournament' => $this->showForUser($userId, $tournamentId)];
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function claimReward(int $userId, int $tournamentId): array
    {
        if ($userId <= 0 || $tournamentId <= 0) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $tournament = $this->tournamentForUpdate($tournamentId);
            $participant = $this->participantForUpdate($tournamentId, $userId);
            if (!$tournament || !$participant) {
                throw new \RuntimeException('Участник турнира не найден.');
            }
            if ((string) $tournament['status'] !== 'finished') {
                throw new \RuntimeException('Награды доступны после завершения турнира.');
            }
            if ((int) ($participant['reward_claimed_at'] ?? 0) > 0) {
                throw new \RuntimeException('Награда уже была получена.');
            }
            if ((int) ($participant['place_num'] ?? 0) <= 0 && (string) ($participant['status'] ?? '') !== 'winner') {
                throw new \RuntimeException('Для этого участника нет призового места.');
            }

            $rewardData = $this->decodeJson((string) ($tournament['reward_json'] ?? ''));
            $items = $this->rewardItems($rewardData);
            if ($items !== []) {
                $this->rewards->grantPipeline($userId, ['items' => $items], 'tournament', (string) $tournamentId, [
                    'title' => 'Награда турнира: ' . (string) $tournament['title'],
                    'operation_key' => 'tournament:' . $tournamentId . ':reward:' . $userId,
                    'source' => 'Турнир',
                    'source_type' => 'tournament',
                    'source_id' => (string) $tournamentId,
                    'variant' => 'success',
                    'throw_on_fail' => true,
                ]);
            }

            $awardedMedals = $this->awardTournamentMedals($userId, $tournament);
            $now = time();
            $this->db->prepare(
                'UPDATE admin_tournament_participants
                    SET reward_claimed_at = :reward_claimed_at, updated_at = :updated_at
                  WHERE id = :id'
            )->execute([
                'reward_claimed_at' => $now,
                'updated_at' => $now,
                'id' => (int) $participant['id'],
            ]);

            $this->log($tournamentId, $userId, 'reward.claim', [
                'items' => $items,
                'medals' => $awardedMedals,
                'place_num' => (int) ($participant['place_num'] ?? 0),
            ]);

            if ($started) {
                $this->db->commit();
            }

            return [
                'ok' => true,
                'message' => 'Награда турнира получена.',
                'items' => $items,
                'medals' => $awardedMedals,
                'tournament' => $this->showForUser($userId, $tournamentId),
            ];
        } catch (Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function showForUser(int $userId, int $tournamentId): array
    {
        foreach ($this->tournamentRows($userId, $tournamentId) as $row) {
            return $this->formatTournament($row, $userId);
        }
        return [];
    }

    private function tournamentRows(int $userId, int $tournamentId = 0): array
    {
        $where = 'WHERE t.status <> "draft"';
        $params = ['user_id' => $userId];
        if ($tournamentId > 0) {
            $where .= ' AND t.id = :tournament_id';
            $params['tournament_id'] = $tournamentId;
        }

        $sql = 'SELECT t.*, u.login AS curator_login, b.title AS location_name, exit_b.title AS exit_location_name,
                       fee.name AS fee_item_name,
                       p.id AS participant_id, p.status AS participant_status, p.score AS participant_score,
                       p.place_num AS participant_place_num, p.joined_at AS participant_joined_at,
                       p.fee_paid_at AS participant_fee_paid_at, p.fee_refunded_at AS participant_fee_refunded_at,
                       p.checked_in_at AS participant_checked_in_at, p.left_at AS participant_left_at,
                       p.return_location_id AS participant_return_location_id,
                       p.reward_claimed_at AS participant_reward_claimed_at,
                       (SELECT COUNT(*) FROM admin_tournament_participants pc
                         WHERE pc.tournament_id = t.id AND pc.status NOT IN ("cancelled", "disqualified")) AS participants_count
                  FROM admin_tournaments t
             LEFT JOIN users u ON u.id = t.curator_user_id
             LEFT JOIN build b ON b.id = t.location_id
             LEFT JOIN build exit_b ON exit_b.id = t.arena_exit_location_id
             LEFT JOIN items fee ON fee.id = t.entry_fee_item_id
             LEFT JOIN admin_tournament_participants p ON p.tournament_id = t.id AND p.user_id = :user_id
                ' . $where . '
              ORDER BY t.starts_at ASC, t.id DESC
                 LIMIT 80';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function formatTournament(array $row, int $userId): array
    {
        $now = time();
        $deadline = $this->registrationDeadline($row);
        $participantId = (int) ($row['participant_id'] ?? 0);
        $participantStatus = (string) ($row['participant_status'] ?? '');
        $status = (string) ($row['status'] ?? 'draft');
        $registered = $participantId > 0 && !in_array($participantStatus, ['', 'cancelled', 'disqualified'], true);
        $rewardData = $this->decodeJson((string) ($row['reward_json'] ?? ''));
        $registrationOpen = $status === 'registration' && ($deadline <= 0 || $now <= $deadline);
        $arenaOpen = $status === 'active';
        $rewardAvailable = $status === 'finished' && $registered && (int) ($row['participant_reward_claimed_at'] ?? 0) <= 0
            && ((int) ($row['participant_place_num'] ?? 0) > 0 || $participantStatus === 'winner');

        return [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'status' => $status,
            'statusLabel' => $this->statusLabel($status),
            'startsAt' => (int) $row['starts_at'],
            'endsAt' => (int) $row['ends_at'],
            'registrationDeadlineAt' => $deadline,
            'startsAtText' => $this->timeText((int) $row['starts_at']),
            'endsAtText' => $this->timeText((int) $row['ends_at']),
            'registrationDeadlineText' => $this->timeText($deadline),
            'registrationOpen' => $registrationOpen,
            'arenaOpen' => $arenaOpen,
            'canRegister' => $registrationOpen && !$registered,
            'canCancel' => $registered && in_array($status, ['registration'], true),
            'canEnterArena' => $registered && $arenaOpen,
            'canLeaveArena' => $registered && $arenaOpen,
            'canClaimReward' => $rewardAvailable,
            'curator' => [
                'id' => (int) ($row['curator_user_id'] ?? 0),
                'login' => (string) ($row['curator_login'] ?? ''),
                'isCurrentUser' => (int) ($row['curator_user_id'] ?? 0) === $userId,
                'canManage' => (int) ($row['curator_user_id'] ?? 0) === $userId,
            ],
            'entryFee' => [
                'itemId' => (int) ($row['entry_fee_item_id'] ?? 0),
                'itemName' => (string) ($row['fee_item_name'] ?? ''),
                'amount' => (int) ($row['entry_fee_amount'] ?? 0),
                'label' => $this->feeLabel($row),
            ],
            'arena' => [
                'locationId' => (int) ($row['location_id'] ?? 0),
                'title' => (string) ($row['location_name'] ?? ''),
                'exitLocationId' => (int) ($row['arena_exit_location_id'] ?? 0),
                'exitTitle' => (string) ($row['exit_location_name'] ?? ''),
            ],
            'participants' => [
                'count' => (int) ($row['participants_count'] ?? 0),
                'max' => (int) ($row['max_participants'] ?? 0),
                'label' => $this->participantsLabel($row),
            ],
            'levelRange' => [
                'min' => (int) ($row['min_level'] ?? 1),
                'max' => (int) ($row['max_level'] ?? 100),
            ],
            'rules' => (string) ($row['rules'] ?? ''),
            'rewardNote' => (string) ($row['reward_note'] ?? ''),
            'rewards' => [
                'items' => $this->rewardItems($rewardData),
                'raw' => $rewardData,
                'medals' => $this->medalsForTournament((int) $row['id']),
            ],
            'participant' => $participantId > 0 ? [
                'id' => $participantId,
                'status' => $participantStatus,
                'statusLabel' => $this->participantStatusLabel($participantStatus),
                'score' => (int) ($row['participant_score'] ?? 0),
                'placeNum' => (int) ($row['participant_place_num'] ?? 0),
                'joinedAt' => (int) ($row['participant_joined_at'] ?? 0),
                'feePaidAt' => (int) ($row['participant_fee_paid_at'] ?? 0),
                'feeRefundedAt' => (int) ($row['participant_fee_refunded_at'] ?? 0),
                'checkedInAt' => (int) ($row['participant_checked_in_at'] ?? 0),
                'leftAt' => (int) ($row['participant_left_at'] ?? 0),
                'returnLocationId' => (int) ($row['participant_return_location_id'] ?? 0),
                'rewardClaimedAt' => (int) ($row['participant_reward_claimed_at'] ?? 0),
            ] : null,
        ];
    }

    private function tournamentForUpdate(int $tournamentId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_tournaments WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute(['id' => $tournamentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function participantForUpdate(int $tournamentId, int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_tournament_participants WHERE tournament_id = :tournament AND user_id = :user LIMIT 1 FOR UPDATE');
        $stmt->execute(['tournament' => $tournamentId, 'user' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function activeParticipantsCount(int $tournamentId): int
    {
        $stmt = $this->db->prepare('SELECT id FROM admin_tournament_participants WHERE tournament_id = :id AND status NOT IN ("cancelled", "disqualified") FOR UPDATE');
        $stmt->execute(['id' => $tournamentId]);
        return count($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    private function hasEligiblePokemon(int $userId, int $minLevel, int $maxLevel): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM pok_user
              WHERE users = :user
                AND lvl BETWEEN :min_level AND :max_level
                AND hp_max > 0'
        );
        $stmt->execute([
            'user' => $userId,
            'min_level' => max(1, $minLevel),
            'max_level' => max(1, $maxLevel),
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function consumeItem(int $userId, int $itemId, int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $remaining = $amount;
        $stmt = $this->db->prepare(
            'SELECT id, count FROM items_users
              WHERE user_id = :user AND item_id = :item AND count > 0
              ORDER BY id ASC
              FOR UPDATE'
        );
        $stmt->execute(['user' => $userId, 'item' => $itemId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $available = array_sum(array_map(static fn (array $row): int => (int) $row['count'], $rows));
        if ($available < $amount) {
            throw new \RuntimeException('Недостаточно средств для взноса.');
        }

        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }
            $rowCount = (int) $row['count'];
            $take = min($rowCount, $remaining);
            if ($take >= $rowCount) {
                $this->db->prepare('DELETE FROM items_users WHERE id = :id LIMIT 1')->execute(['id' => (int) $row['id']]);
            } else {
                $this->db->prepare('UPDATE items_users SET count = count - :take WHERE id = :id LIMIT 1')
                    ->execute(['take' => $take, 'id' => (int) $row['id']]);
            }
            $remaining -= $take;
        }
    }

    private function addItem(int $userId, int $itemId, int $amount): void
    {
        if ($userId <= 0 || $itemId <= 0 || $amount <= 0) {
            return;
        }

        $stmt = $this->db->prepare('SELECT id FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = "not" LIMIT 1 FOR UPDATE');
        $stmt->execute(['user' => $userId, 'item' => $itemId]);
        $rowId = (int) ($stmt->fetchColumn() ?: 0);
        if ($rowId > 0) {
            $this->db->prepare('UPDATE items_users SET count = count + :amount WHERE id = :id LIMIT 1')
                ->execute(['amount' => $amount, 'id' => $rowId]);
            return;
        }

        $this->db->prepare(
            'INSERT INTO items_users (item_id, user_id, count, dattimer, timers)
             VALUES (:item, :user, :amount, "not", "not")'
        )->execute(['item' => $itemId, 'user' => $userId, 'amount' => $amount]);
    }

    private function awardTournamentMedals(int $userId, array $tournament): array
    {
        if (!$this->tableExists('admin_medals') || !$this->tableExists('admin_user_medals')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM admin_medals
              WHERE enabled = 1 AND tournament_id = :tournament
              ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['tournament' => (int) $tournament['id']]);
        $medals = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $awarded = [];
        foreach ($medals as $medal) {
            $this->db->prepare(
                'INSERT IGNORE INTO admin_user_medals (medal_id, user_id, tournament_id, comment, awarded_by, awarded_at)
                 VALUES (:medal, :user, :tournament, :comment, :awarded_by, :awarded_at)'
            )->execute([
                'medal' => (int) $medal['id'],
                'user' => $userId,
                'tournament' => (int) $tournament['id'],
                'comment' => 'Награда турнира: ' . (string) $tournament['title'],
                'awarded_by' => (int) ($tournament['curator_user_id'] ?? 0),
                'awarded_at' => time(),
            ]);
            $awarded[] = [
                'id' => (int) $medal['id'],
                'title' => (string) $medal['title'],
            ];
        }
        return $awarded;
    }

    private function medalsForTournament(int $tournamentId): array
    {
        if (!$this->tableExists('admin_medals')) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT id, title, description, icon_file, medal_type
               FROM admin_medals
              WHERE enabled = 1 AND tournament_id = :tournament
              ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['tournament' => $tournamentId]);
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'description' => (string) $row['description'],
                'icon' => $this->medalIconPath((string) $row['icon_file']),
                'type' => (string) $row['medal_type'],
            ];
        }
        return $rows;
    }

    private function rewardItems(array $rewardData): array
    {
        $items = $rewardData['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $itemId => $count) {
            $itemId = (int) $itemId;
            $count = (int) $count;
            if ($itemId > 0 && $count > 0) {
                $normalized[$itemId] = ($normalized[$itemId] ?? 0) + $count;
            }
        }
        return $normalized;
    }

    private function registrationDeadline(array $row): int
    {
        $deadline = (int) ($row['registration_deadline_at'] ?? 0);
        if ($deadline > 0) {
            return $deadline;
        }
        return (int) ($row['starts_at'] ?? 0);
    }

    private function feeLabel(array $row): string
    {
        $amount = (int) ($row['entry_fee_amount'] ?? 0);
        if ($amount <= 0) {
            return 'без взноса';
        }
        $name = trim((string) ($row['fee_item_name'] ?? ''));
        if ($name === '') {
            $name = 'предмет #' . (int) ($row['entry_fee_item_id'] ?? 0);
        }
        return number_format($amount, 0, ',', ' ') . ' × ' . $name;
    }

    private function participantsLabel(array $row): string
    {
        $count = (int) ($row['participants_count'] ?? 0);
        $max = (int) ($row['max_participants'] ?? 0);
        return $max > 0 ? $count . '/' . $max : (string) $count;
    }

    private function timeText(int $timestamp): string
    {
        return $timestamp > 0 ? date('d.m.Y H:i', $timestamp) : 'не задано';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'registration' => 'Регистрация',
            'active' => 'Идёт турнир',
            'finished' => 'Завершён',
            'cancelled' => 'Отменён',
            default => 'Черновик',
        };
    }

    private function participantStatusLabel(string $status): string
    {
        return match ($status) {
            'registered' => 'Зарегистрирован',
            'checked_in' => 'На арене',
            'eliminated' => 'Выбыл',
            'winner' => 'Победитель',
            'disqualified' => 'Дисквалифицирован',
            'cancelled' => 'Отменено',
            default => 'Нет участия',
        };
    }

    private function medalIconPath(string $file): string
    {
        $file = basename($file);
        if ($file !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . $file)) {
            return '/public/img/items/' . $file;
        }
        if ($file !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/ui/' . $file)) {
            return '/public/img/ui/' . $file;
        }
        return '/public/img/ui/menu-profile.png';
    }

    private function log(int $tournamentId, int $userId, string $action, array $data = []): void
    {
        if (!$this->tableExists('admin_tournament_logs')) {
            return;
        }

        $this->db->prepare(
            'INSERT INTO admin_tournament_logs (tournament_id, user_id, action, data_json, created_at)
             VALUES (:tournament, :user, :action, :data, :created_at)'
        )->execute([
            'tournament' => $tournamentId,
            'user' => $userId,
            'action' => $action,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            'created_at' => time(),
        ]);
    }

    private function decodeJson(string $json): array
    {
        $json = trim($json);
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function tableExists(string $table): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $stmt->execute(['table' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
