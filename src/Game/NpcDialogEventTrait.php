<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;

trait NpcDialogEventTrait
{
    private function hippodromeGaren(int $userId, int $step): array
    {
        if (!$this->hippodromeReady()) {
            return $this->dialog('Гарен', 'Ипподром готовится к открытию: таблицы скачек ещё не применены. После миграции я смогу принимать покемонов на заезды.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $this->hippodromeSettleDueRaces();
        $race = $this->hippodromeCurrentRace($userId);
        $participant = $race ? $this->hippodromeParticipant((int) $race['id'], $userId) : [];

        if ($step === 2) {
            return $this->dialog('Гарен', 'Правила ипподрома простые: выставляешь одного активного покемона нужного уровня, платишь регистрационный взнос 20 000 монет, а результат решает Скорость. Если участников меньше шести, заезд отменяется и взнос возвращается через систему наград.', [
                ['label' => 'К текущему заезду', 'params' => ['quest_npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        if ($step === 3) {
            return $this->hippodromeParticipantsDialog($race);
        }

        if (!$race) {
            return $this->dialog('Гарен', 'Сегодня заездов пока нет. Я открою регистрацию, как только ипподром будет готов принять участников.', [
                ['label' => 'Уйти', 'close' => true],
            ]);
        }

        $status = (string) ($race['status'] ?? 'registration');
        $level = (int) ($race['level_required'] ?? 100);
        $count = (int) ($race['participants_count'] ?? 0);
        $fee = (int) ($race['entry_fee_amount'] ?? 20000);
        $text = sprintf(
            "%s\n\nУровень заезда: Lv.%d. Взнос: %s монет. Участников: %d/%s. Регистрация до: %s.",
            (string) ($race['title'] ?? 'Ипподром Пьютера'),
            $level,
            number_format($fee, 0, ',', ' '),
            $count,
            (int) ($race['max_participants'] ?? 0) > 0 ? (string) (int) $race['max_participants'] : 'без лимита',
            $this->hippodromeTime((int) ($race['registration_ends_at'] ?? 0))
        );

        $choices = [
            ['label' => 'Правила', 'params' => ['quest_npc' => '1', 'do' => '2']],
            ['label' => 'Участники и результаты', 'params' => ['quest_npc' => '1', 'do' => '3']],
        ];

        if ($participant) {
            $pokemonName = (string) ($participant['pokemon_name'] ?? ('Pokemon #' . (int) ($participant['pokemon_id'] ?? 0)));
            $place = (int) ($participant['place_num'] ?? 0);
            if ($status === 'finished' && $place > 0) {
                $text .= sprintf("\n\nТвой %s занял %d место. Приз уже начислен системой.", $pokemonName, $place);
            } else {
                $text .= "\n\nТы уже зарегистрировал: " . $pokemonName . '.';
            }
        } elseif ($status === 'registration') {
            foreach ($this->hippodromeEligiblePokemon($userId, $level) as $pokemon) {
                $choices[] = [
                    'label' => sprintf('%s Lv.%d · Speed %d', (string) $pokemon['names'], (int) $pokemon['lvl'], (int) $pokemon['speed']),
                    'action' => 'hippodrome_register:' . (int) $race['id'] . ':' . (int) $pokemon['id'],
                ];
            }
            if (count($choices) === 2) {
                $text .= "\n\nУ тебя нет активного покемона нужного уровня для этого заезда.";
            }
        } else {
            $text .= "\n\nРегистрация закрыта. Загляни к результатам или дождись следующего заезда.";
        }

        $choices[] = ['label' => 'Уйти', 'close' => true];
        return $this->dialog('Гарен', $text, $choices);
    }

    private function hippodromeRegisterAction(int $userId, string $action): array
    {
        [, $raceId, $pokemonId] = array_pad(explode(':', $action), 3, 0);
        $raceId = (int) $raceId;
        $pokemonId = (int) $pokemonId;
        if (!$this->hippodromeReady() || $raceId <= 0 || $pokemonId <= 0) {
            return $this->dialog('Гарен', 'Заезд или покемон не найден.', [
                ['label' => 'Назад', 'params' => ['quest_npc' => '1', 'do' => '1']],
            ]);
        }

        $started = !$this->db->inTransaction();
        try {
            if ($started) {
                $this->db->beginTransaction();
            }

            $race = $this->hippodromeRaceForUpdate($raceId);
            if (!$race) {
                throw new \RuntimeException('Заезд не найден.');
            }
            if ((string) ($race['status'] ?? '') !== 'registration' || (int) ($race['registration_ends_at'] ?? 0) < time()) {
                throw new \RuntimeException('Регистрация на этот заезд уже закрыта.');
            }
            if ($this->hippodromeParticipant($raceId, $userId)) {
                throw new \RuntimeException('Ты уже зарегистрирован на этот заезд.');
            }

            $pokemon = $this->hippodromePokemonForUpdate($userId, $pokemonId);
            if (!$pokemon) {
                throw new \RuntimeException('Покемон не найден в активной команде.');
            }
            if ((int) ($pokemon['lvl'] ?? 0) !== (int) ($race['level_required'] ?? 0)) {
                throw new \RuntimeException('Уровень покемона не соответствует требованиям заезда.');
            }
            if ($this->activePokemonCount($userId) <= 1) {
                throw new \RuntimeException('Нельзя выставить последнего активного покемона.');
            }

            $feeItemId = (int) ($race['entry_fee_item_id'] ?? 1);
            $feeAmount = (int) ($race['entry_fee_amount'] ?? 20000);
            if (!$this->inventory->removeItem($userId, $feeItemId, $feeAmount)) {
                throw new \RuntimeException('Недостаточно монет для регистрации.');
            }

            $now = time();
            $this->db->prepare(
                'INSERT INTO hippodrome_participants
                    (race_id, user_id, pokemon_id, pokemon_base_id, pokemon_name, pokemon_level, speed_score,
                     status, place_num, prize_amount, joined_at, reward_claimed_at, updated_at)
                 VALUES
                    (:race, :user, :pokemon, :base, :name, :level, :speed,
                     "registered", 0, 0, :joined_at, 0, :updated_at)'
            )->execute([
                'race' => $raceId,
                'user' => $userId,
                'pokemon' => $pokemonId,
                'base' => (int) ($pokemon['basenum'] ?? 0),
                'name' => (string) ($pokemon['names'] ?? ('Pokemon #' . $pokemonId)),
                'level' => (int) ($pokemon['lvl'] ?? 0),
                'speed' => (int) ($pokemon['speed'] ?? 0),
                'joined_at' => $now,
                'updated_at' => $now,
            ]);
            $this->db->prepare('UPDATE pok_user SET active = 3 WHERE id = :pokemon AND users = :user LIMIT 1')
                ->execute(['pokemon' => $pokemonId, 'user' => $userId]);
            $this->db->prepare('UPDATE hippodrome_races SET prize_pool = prize_pool + :fee, updated_at = :time WHERE id = :id LIMIT 1')
                ->execute(['fee' => $feeAmount, 'time' => $now, 'id' => $raceId]);

            $this->hippodromeLog($raceId, $userId, 'register', [
                'pokemon_id' => $pokemonId,
                'pokemon_name' => (string) ($pokemon['names'] ?? ''),
                'speed' => (int) ($pokemon['speed'] ?? 0),
                'fee_item_id' => $feeItemId,
                'fee_amount' => $feeAmount,
            ]);
            $this->rewards?->notify($userId, 'Ипподром', 'Покемон зарегистрирован на заезд. Результат появится после окончания скачки.', 'success', [
                'source_type' => 'hippodrome',
                'source_id' => (string) $raceId,
            ]);

            if ($started) {
                $this->db->commit();
            }

            return $this->dialog('Гарен', 'Регистрация завершена. Покемон временно закреплён за ипподромом до окончания заезда.', [
                ['label' => 'Участники', 'params' => ['quest_npc' => '1', 'do' => '3']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        } catch (\Throwable $e) {
            if ($started && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return $this->dialog('Гарен', $e->getMessage(), [
                ['label' => 'Назад', 'params' => ['quest_npc' => '1', 'do' => '1']],
                ['label' => 'Уйти', 'close' => true],
            ]);
        }
    }

    private function hippodromeParticipantsDialog(array $race): array
    {
        if (!$race) {
            return $this->dialog('Гарен', 'Заезд ещё не открыт.', [
                ['label' => 'Назад', 'params' => ['quest_npc' => '1', 'do' => '1']],
            ]);
        }

        $participants = $this->hippodromeParticipants((int) $race['id']);
        if ($participants === []) {
            $text = 'На этот заезд пока никто не зарегистрировался.';
        } else {
            $lines = [];
            foreach ($participants as $row) {
                $place = (int) ($row['place_num'] ?? 0);
                $prefix = $place > 0 ? $place . ' место · ' : '';
                $lines[] = sprintf(
                    '%s%s: %s Lv.%d, Speed %d',
                    $prefix,
                    (string) ($row['user_login'] ?? ('#' . (int) $row['user_id'])),
                    (string) ($row['pokemon_name'] ?? ''),
                    (int) ($row['pokemon_level'] ?? 0),
                    (int) ($row['speed_score'] ?? 0)
                );
            }
            $text = implode("\n", $lines);
        }

        return $this->dialog('Гарен', $text, [
            ['label' => 'Назад к заезду', 'params' => ['quest_npc' => '1', 'do' => '1']],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }

    private function hippodromeCurrentRace(int $userId): array
    {
        $todayStart = strtotime(date('Y-m-d 00:00:00')) ?: (time() - 86400);
        $stmt = $this->db->prepare(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM hippodrome_participants p WHERE p.race_id = r.id AND p.status <> "cancelled") AS participants_count
               FROM hippodrome_races r
              WHERE r.created_at >= :today
              ORDER BY FIELD(r.status, "registration", "active", "finished", "cancelled"), r.id DESC
              LIMIT 1'
        );
        $stmt->execute(['today' => $todayStart]);
        $race = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if ($race && in_array((string) ($race['status'] ?? ''), ['registration', 'active', 'finished'], true)) {
            return $race;
        }

        $level = $this->hippodromeSuggestedLevel($userId);
        $now = time();
        $registrationEnds = $now + 30 * 60;
        $startsAt = $registrationEnds;
        $endsAt = $startsAt + 15 * 60;
        $this->db->prepare(
            'INSERT INTO hippodrome_races
                (title, status, level_required, entry_fee_item_id, entry_fee_amount,
                 registration_starts_at, registration_ends_at, starts_at, ends_at,
                 min_participants, max_participants, prize_pool, created_by, created_at, updated_at)
             VALUES
                (:title, "registration", :level, 1, 20000,
                 :reg_start, :reg_end, :starts_at, :ends_at,
                 6, 0, 0, :created_by, :created_at, :updated_at)'
        )->execute([
            'title' => 'Скачки Пьютера · ' . date('d.m.Y H:i'),
            'level' => $level,
            'reg_start' => $now,
            'reg_end' => $registrationEnds,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'created_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $raceId = (int) $this->db->lastInsertId();
        $this->hippodromeLog($raceId, $userId, 'race.create', ['level_required' => $level]);

        return $this->hippodromeRace($raceId);
    }

    private function hippodromeSettleDueRaces(): void
    {
        if (!$this->hippodromeReady()) {
            return;
        }

        $now = time();
        $stmt = $this->db->prepare('SELECT * FROM hippodrome_races WHERE status IN ("registration", "active") AND ends_at > 0 AND ends_at <= :now ORDER BY id ASC LIMIT 10');
        $stmt->execute(['now' => $now]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $race) {
            $started = !$this->db->inTransaction();
            try {
                if ($started) {
                    $this->db->beginTransaction();
                }
                $race = $this->hippodromeRaceForUpdate((int) $race['id']);
                if (!$race || !in_array((string) ($race['status'] ?? ''), ['registration', 'active'], true)) {
                    if ($started && $this->db->inTransaction()) {
                        $this->db->commit();
                    }
                    continue;
                }
                $participants = $this->hippodromeParticipantsForUpdate((int) $race['id']);
                if (count($participants) < (int) ($race['min_participants'] ?? 6)) {
                    $this->hippodromeCancelRace($race, $participants, 'Недостаточно участников.');
                } else {
                    $this->hippodromeFinishRace($race, $participants);
                }
                if ($started) {
                    $this->db->commit();
                }
            } catch (\Throwable $e) {
                if ($started && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $this->hippodromeLog((int) ($race['id'] ?? 0), 0, 'settle.error', ['error' => $e->getMessage()]);
            }
        }
    }

    private function hippodromeFinishRace(array $race, array $participants): void
    {
        usort($participants, static fn (array $a, array $b): int => ((int) $b['speed_score'] <=> (int) $a['speed_score']) ?: ((int) $a['id'] <=> (int) $b['id']));
        $pool = max(0, (int) ($race['prize_pool'] ?? 0));
        $prizes = [
            1 => (int) round($pool * 0.50),
            2 => (int) round($pool * 0.30),
            3 => max(0, $pool - (int) round($pool * 0.50) - (int) round($pool * 0.30)),
        ];
        $now = time();
        foreach ($participants as $index => $row) {
            $place = $index + 1;
            $prize = $place <= 3 ? max(0, (int) ($prizes[$place] ?? 0)) : 0;
            $status = $place <= 3 ? 'winner' : 'finished';
            $this->db->prepare(
                'UPDATE hippodrome_participants
                    SET status = :status, place_num = :place, prize_amount = :prize,
                        reward_claimed_at = IF(:prize > 0, :now, reward_claimed_at), updated_at = :now
                  WHERE id = :id LIMIT 1'
            )->execute([
                'status' => $status,
                'place' => $place <= 3 ? $place : 0,
                'prize' => $prize,
                'now' => $now,
                'id' => (int) $row['id'],
            ]);
            $this->db->prepare('UPDATE pok_user SET active = 1 WHERE id = :pokemon AND users = :user AND active = 3 LIMIT 1')
                ->execute(['pokemon' => (int) $row['pokemon_id'], 'user' => (int) $row['user_id']]);
            if ($prize > 0) {
                $this->grantRewardItems((int) $row['user_id'], [1 => $prize], 'Ипподром: призовое место #' . $place);
            }
        }
        $this->db->prepare('UPDATE hippodrome_races SET status = "finished", settled_at = :now, updated_at = :now WHERE id = :id LIMIT 1')
            ->execute(['now' => $now, 'id' => (int) $race['id']]);
        $this->hippodromeLog((int) $race['id'], 0, 'race.finish', ['participants' => count($participants), 'prize_pool' => $pool]);
    }

    private function hippodromeCancelRace(array $race, array $participants, string $reason): void
    {
        $now = time();
        $fee = max(0, (int) ($race['entry_fee_amount'] ?? 0));
        foreach ($participants as $row) {
            $this->db->prepare('UPDATE pok_user SET active = 1 WHERE id = :pokemon AND users = :user AND active = 3 LIMIT 1')
                ->execute(['pokemon' => (int) $row['pokemon_id'], 'user' => (int) $row['user_id']]);
            $this->db->prepare('UPDATE hippodrome_participants SET status = "cancelled", updated_at = :now WHERE id = :id LIMIT 1')
                ->execute(['now' => $now, 'id' => (int) $row['id']]);
            if ($fee > 0) {
                $this->grantRewardItems((int) $row['user_id'], [1 => $fee], 'Ипподром: возврат взноса');
            }
        }
        $this->db->prepare('UPDATE hippodrome_races SET status = "cancelled", settled_at = :now, updated_at = :now, note = :note WHERE id = :id LIMIT 1')
            ->execute(['now' => $now, 'note' => $reason, 'id' => (int) $race['id']]);
        $this->hippodromeLog((int) $race['id'], 0, 'race.cancel', ['reason' => $reason, 'participants' => count($participants)]);
    }

    private function hippodromeEligiblePokemon(int $userId, int $level): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, basenum, names, lvl, speed
               FROM pok_user
              WHERE users = :user AND active = 1 AND lvl = :level AND hp_max > 0
              ORDER BY speed DESC, id ASC
              LIMIT 12'
        );
        $stmt->execute(['user' => $userId, 'level' => $level]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeSuggestedLevel(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT lvl FROM pok_user WHERE users = :user AND active = 1 ORDER BY lvl DESC, speed DESC LIMIT 1');
        $stmt->execute(['user' => $userId]);
        $level = (int) ($stmt->fetchColumn() ?: 100);
        return max(1, min(100, $level));
    }

    private function hippodromeParticipant(int $raceId, int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM hippodrome_participants WHERE race_id = :race AND user_id = :user LIMIT 1');
        $stmt->execute(['race' => $raceId, 'user' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeParticipants(int $raceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.login AS user_login
               FROM hippodrome_participants p
          LEFT JOIN users u ON u.id = p.user_id
              WHERE p.race_id = :race
              ORDER BY p.place_num > 0 DESC, p.place_num ASC, p.speed_score DESC, p.id ASC'
        );
        $stmt->execute(['race' => $raceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeParticipantsForUpdate(int $raceId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM hippodrome_participants WHERE race_id = :race AND status = "registered" ORDER BY speed_score DESC, id ASC FOR UPDATE');
        $stmt->execute(['race' => $raceId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromePokemonForUpdate(int $userId, int $pokemonId): array
    {
        $stmt = $this->db->prepare('SELECT id, basenum, names, lvl, speed FROM pok_user WHERE id = :pokemon AND users = :user AND active = 1 LIMIT 1 FOR UPDATE');
        $stmt->execute(['pokemon' => $pokemonId, 'user' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeRace(int $raceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*,
                    (SELECT COUNT(*) FROM hippodrome_participants p WHERE p.race_id = r.id AND p.status <> "cancelled") AS participants_count
               FROM hippodrome_races r
              WHERE r.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $raceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeRaceForUpdate(int $raceId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM hippodrome_races WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute(['id' => $raceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function hippodromeLog(int $raceId, int $userId, string $action, array $data = []): void
    {
        if (!$this->hippodromeTableExists('hippodrome_logs')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO hippodrome_logs (race_id, user_id, action, data_json, created_at)
             VALUES (:race, :user, :action, :data, :created_at)'
        )->execute([
            'race' => $raceId,
            'user' => $userId,
            'action' => $action,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            'created_at' => time(),
        ]);
    }

    private function hippodromeReady(): bool
    {
        return $this->hippodromeTableExists('hippodrome_races')
            && $this->hippodromeTableExists('hippodrome_participants')
            && $this->hippodromeTableExists('hippodrome_logs');
    }

    private function hippodromeTableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $stmt->execute(['table' => $table]);
        return $cache[$table] = (int) $stmt->fetchColumn() > 0;
    }

    private function hippodromeTime(int $time): string
    {
        return $time > 0 ? date('d.m.Y H:i', $time) : 'не задано';
    }

    private function eventHallNpc(int $questNpcId): array
    {
        $dialogs = [
            1 => ['Makasimka', 'Спасибо, что заглянул в праздничный зал. Ивентовые задания теперь должны выдаваться через новый event API, чтобы награды не дублировались.'],
            2 => ['LEGENDA', 'Рад видеть тебя на празднике. Подарочные сценарии перенесены в систему подарков и reward pipeline; старые прямые выдачи отключены.'],
            3 => ['MiladyMio', 'Добро пожаловать. Праздничные NPC остаются на карте, но награды и прогресс должны идти через новые события.'],
            4 => ['Роберт', 'Я слежу за итогами праздника. Если награда доступна, она должна прийти через event rewards и уведомления, без старого PHP-скрипта.'],
        ];
        [$name, $text] = $dialogs[$questNpcId] ?? ['Праздничный NPC', 'Праздничный персонаж подключен к новой карте.'];

        return $this->dialog($name, $text, [
            ['label' => 'Открыть события', 'route' => '/game/events'],
            ['label' => 'Уйти', 'close' => true],
        ]);
    }
}
