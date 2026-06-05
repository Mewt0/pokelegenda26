<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

trait AdminEventsTournamentRepositoryTrait
{
    public function events(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $sql = 'SELECT * FROM game_event_boosts';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE id = :id_search OR title LIKE :search OR boost_key LIKE :search OR scope LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY enabled DESC, starts_at DESC, id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['boost_label'] = $this->boostLabel((string) ($row['boost_key'] ?? ''));
            $row['scope_label'] = $this->boostScopeLabel((string) ($row['scope'] ?? ''));
            $row['status_label'] = $this->eventStatusLabel($row);
        }

        return $rows;
    }

    public function saveEvent(int $adminId, array $payload): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return ['ok' => false, 'message' => 'Миграция game_event_boosts еще не применена.'];
        }

        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        $boostKey = $this->boostKey((string) ($payload['boost_key'] ?? 'exp'));
        $multiplier = (float) str_replace(',', '.', (string) ($payload['multiplier'] ?? '1'));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название ивента.'];
        }
        if ($multiplier < 1 || $multiplier > 10) {
            return ['ok' => false, 'message' => 'Множитель должен быть от x1 до x10.'];
        }

        $now = time();
        $before = $id > 0 ? $this->rowById('game_event_boosts', 'id', $id) : [];
        $startsAt = $this->parseTimestamp($payload['starts_at'] ?? 0);
        $endsAt = $this->parseTimestamp($payload['ends_at'] ?? 0);
        if ($startsAt > 0 && $endsAt > 0 && $endsAt <= $startsAt) {
            return ['ok' => false, 'message' => 'Дата окончания должна быть позже старта.'];
        }

        $data = [
            'title' => $title,
            'boost_key' => $boostKey,
            'multiplier' => number_format($multiplier, 2, '.', ''),
            'scope' => $this->boostScope((string) ($payload['scope'] ?? 'global')),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'note' => mb_substr(trim((string) ($payload['note'] ?? '')), 0, 255),
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE game_event_boosts
                    SET title = :title, boost_key = :boost_key, multiplier = :multiplier,
                        scope = :scope, starts_at = :starts_at, ends_at = :ends_at,
                        enabled = :enabled, note = :note, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'event.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO game_event_boosts
                    (title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note, created_by, created_at, updated_at)
                 VALUES
                    (:title, :boost_key, :multiplier, :scope, :starts_at, :ends_at, :enabled, :note, :created_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'event.create';
        }

        $this->audit($adminId, $action, 'game_event_boosts', $id, ['before' => $before, 'after' => $data]);
        return [
            'ok' => true,
            'message' => 'Ивент сохранен: ' . $this->boostLabel($boostKey) . ' x' . number_format($multiplier, 2, '.', '') . '.',
            'id' => $id,
        ];
    }

    public function deleteEvent(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('game_event_boosts', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Ивент не найден.'];
        }
        $this->audit($adminId, 'event.delete.before', 'game_event_boosts', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM game_event_boosts WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'event.delete', 'game_event_boosts', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Ивент удален.'];
    }

    public function tournaments(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('admin_tournaments')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $sql = 'SELECT t.*, u.login AS curator_login, b.title AS location_name,
                       COUNT(p.id) AS participants_count
                  FROM admin_tournaments t
             LEFT JOIN users u ON u.id = t.curator_user_id
             LEFT JOIN build b ON b.id = t.location_id
             LEFT JOIN admin_tournament_participants p ON p.tournament_id = t.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE t.id = :id_search OR t.title LIKE :search OR t.status LIKE :search OR u.login LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' GROUP BY t.id ORDER BY t.id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveTournament(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_tournaments')) {
            return ['ok' => false, 'message' => 'Миграция турниров еще не применена.'];
        }

        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название турнира.'];
        }

        $status = $this->tournamentStatus((string) ($payload['status'] ?? 'draft'));
        $now = time();
        $before = $id > 0 ? $this->rowById('admin_tournaments', 'id', $id) : [];
        $data = [
            'legacy_id' => max(0, (int) ($payload['legacy_id'] ?? 0)),
            'title' => $title,
            'status' => $status,
            'starts_at' => $this->adminTimestamp((string) ($payload['starts_at'] ?? '')),
            'ends_at' => $this->adminTimestamp((string) ($payload['ends_at'] ?? '')),
            'registration_deadline_at' => $this->adminTimestamp((string) ($payload['registration_deadline_at'] ?? '')),
            'entry_fee_item_id' => max(0, (int) ($payload['entry_fee_item_id'] ?? 1)),
            'entry_fee_amount' => max(0, (int) ($payload['entry_fee_amount'] ?? 0)),
            'location_id' => max(0, (int) ($payload['location_id'] ?? 40)),
            'arena_exit_location_id' => max(0, (int) ($payload['arena_exit_location_id'] ?? 0)),
            'curator_user_id' => max(0, (int) ($payload['curator_user_id'] ?? 0)),
            'min_level' => max(1, (int) ($payload['min_level'] ?? 1)),
            'max_level' => max(1, (int) ($payload['max_level'] ?? 100)),
            'max_participants' => max(0, (int) ($payload['max_participants'] ?? 0)),
            'rules' => trim((string) ($payload['rules'] ?? '')),
            'reward_note' => trim((string) ($payload['reward_note'] ?? '')),
            'reward_json' => trim((string) ($payload['reward_json'] ?? '')),
            'updated_by' => $adminId,
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE admin_tournaments
                    SET legacy_id = :legacy_id, title = :title, status = :status,
                        starts_at = :starts_at, ends_at = :ends_at, registration_deadline_at = :registration_deadline_at,
                        entry_fee_item_id = :entry_fee_item_id, entry_fee_amount = :entry_fee_amount,
                        location_id = :location_id, arena_exit_location_id = :arena_exit_location_id, curator_user_id = :curator_user_id,
                        min_level = :min_level, max_level = :max_level, max_participants = :max_participants,
                        rules = :rules, reward_note = :reward_note, reward_json = :reward_json, updated_by = :updated_by, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'tournament.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO admin_tournaments
                    (legacy_id, title, status, starts_at, ends_at, registration_deadline_at, entry_fee_item_id, entry_fee_amount,
                     location_id, arena_exit_location_id, curator_user_id, min_level, max_level, max_participants,
                     rules, reward_note, reward_json, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:legacy_id, :title, :status, :starts_at, :ends_at, :registration_deadline_at, :entry_fee_item_id, :entry_fee_amount,
                     :location_id, :arena_exit_location_id, :curator_user_id, :min_level, :max_level, :max_participants,
                     :rules, :reward_note, :reward_json, :created_by, :updated_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'tournament.create';
        }

        $this->audit($adminId, $action, 'admin_tournaments', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Турнир сохранен.', 'id' => $id];
    }

    public function deleteTournament(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('admin_tournaments', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }
        $participants = $this->tableExists('admin_tournament_participants')
            ? $this->lookupRows('SELECT * FROM admin_tournament_participants WHERE tournament_id = ' . (int) $id)
            : [];
        $this->audit($adminId, 'tournament.delete.before', 'admin_tournaments', $id, ['before' => $before, 'participants' => $participants]);
        $this->db->prepare('DELETE FROM admin_tournament_participants WHERE tournament_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM admin_tournaments WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'tournament.delete', 'admin_tournaments', $id, ['before' => $before, 'participants' => $participants]);
        return ['ok' => true, 'message' => 'Турнир удален.'];
    }

    public function saveTournamentParticipant(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_tournament_participants')) {
            return ['ok' => false, 'message' => 'Миграция участников турниров еще не применена.'];
        }
        $tournamentId = (int) ($payload['tournament_id'] ?? 0);
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($tournamentId <= 0 || $userId <= 0) {
            return ['ok' => false, 'message' => 'Укажи турнир и игрока.'];
        }
        if (!$this->rowById('admin_tournaments', 'id', $tournamentId)) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }
        if (!$this->rowById('users', 'id', $userId)) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }

        $data = [
            'tournament_id' => $tournamentId,
            'user_id' => $userId,
            'pokemon_id' => max(0, (int) ($payload['pokemon_id'] ?? 0)),
            'status' => $this->participantStatus((string) ($payload['status'] ?? 'registered')),
            'score' => (int) ($payload['score'] ?? 0),
            'place_num' => max(0, (int) ($payload['place_num'] ?? 0)),
            'joined_at' => time(),
            'updated_at' => time(),
        ];
        $before = $this->participantRow($tournamentId, $userId);
        $this->db->prepare(
            'INSERT INTO admin_tournament_participants
                (tournament_id, user_id, pokemon_id, status, score, place_num, joined_at, updated_at)
             VALUES
                (:tournament_id, :user_id, :pokemon_id, :status, :score, :place_num, :joined_at, :updated_at)
             ON DUPLICATE KEY UPDATE
                pokemon_id = VALUES(pokemon_id), status = VALUES(status), score = VALUES(score),
                place_num = VALUES(place_num), updated_at = VALUES(updated_at)'
        )->execute($data);
        $this->audit($adminId, 'tournament.participant.save', 'admin_tournament_participants', $tournamentId, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Участник сохранен.'];
    }

    public function medals(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('admin_medals')) {
            return [];
        }
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT m.*, t.title AS tournament_title,
                       COUNT(um.id) AS awarded_count
                  FROM admin_medals m
             LEFT JOIN admin_tournaments t ON t.id = m.tournament_id
             LEFT JOIN admin_user_medals um ON um.medal_id = m.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE m.id = :id_search OR m.title LIKE :search OR m.description LIKE :search OR m.medal_type LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' GROUP BY m.id ORDER BY m.sort_order ASC, m.id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['icon'] = $this->medalIconPath((string) ($row['icon_file'] ?? ''));
        }
        return $rows;
    }

    public function saveMedal(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_medals')) {
            return ['ok' => false, 'message' => 'Миграция медалей еще не применена.'];
        }
        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название медали.'];
        }
        $now = time();
        $before = $id > 0 ? $this->rowById('admin_medals', 'id', $id) : [];
        $data = [
            'title' => $title,
            'description' => trim((string) ($payload['description'] ?? '')),
            'icon_file' => basename(trim((string) ($payload['icon_file'] ?? ''))),
            'medal_type' => $this->medalType((string) ($payload['medal_type'] ?? 'tournament')),
            'tournament_id' => max(0, (int) ($payload['tournament_id'] ?? 0)),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'updated_by' => $adminId,
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE admin_medals
                    SET title = :title, description = :description, icon_file = :icon_file,
                        medal_type = :medal_type, tournament_id = :tournament_id, sort_order = :sort_order,
                        enabled = :enabled, updated_by = :updated_by, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'medal.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO admin_medals
                    (title, description, icon_file, medal_type, tournament_id, sort_order, enabled, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:title, :description, :icon_file, :medal_type, :tournament_id, :sort_order, :enabled, :created_by, :updated_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'medal.create';
        }
        $this->audit($adminId, $action, 'admin_medals', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Медаль сохранена.', 'id' => $id];
    }

    public function deleteMedal(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('admin_medals', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Медаль не найдена.'];
        }
        $awards = $this->tableExists('admin_user_medals')
            ? $this->lookupRows('SELECT * FROM admin_user_medals WHERE medal_id = ' . (int) $id)
            : [];
        $this->audit($adminId, 'medal.delete.before', 'admin_medals', $id, ['before' => $before, 'awards' => $awards]);
        $this->db->prepare('DELETE FROM admin_user_medals WHERE medal_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM admin_medals WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'medal.delete', 'admin_medals', $id, ['before' => $before, 'awards' => $awards]);
        return ['ok' => true, 'message' => 'Медаль удалена.'];
    }

    public function awardMedal(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_user_medals')) {
            return ['ok' => false, 'message' => 'Миграция выдачи медалей еще не применена.'];
        }
        $medalId = (int) ($payload['medal_id'] ?? 0);
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($medalId <= 0 || $userId <= 0) {
            return ['ok' => false, 'message' => 'Укажи медаль и игрока.'];
        }
        if (!$this->rowById('admin_medals', 'id', $medalId)) {
            return ['ok' => false, 'message' => 'Медаль не найдена.'];
        }
        if (!$this->rowById('users', 'id', $userId)) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        $data = [
            'medal_id' => $medalId,
            'user_id' => $userId,
            'tournament_id' => max(0, (int) ($payload['tournament_id'] ?? 0)),
            'comment' => trim((string) ($payload['comment'] ?? '')),
            'awarded_by' => $adminId,
            'awarded_at' => time(),
        ];
        $this->db->prepare(
            'INSERT INTO admin_user_medals (medal_id, user_id, tournament_id, comment, awarded_by, awarded_at)
             VALUES (:medal_id, :user_id, :tournament_id, :comment, :awarded_by, :awarded_at)
             ON DUPLICATE KEY UPDATE comment = VALUES(comment), awarded_by = VALUES(awarded_by), awarded_at = VALUES(awarded_at)'
        )->execute($data);
        $this->audit($adminId, 'medal.award', 'admin_user_medals', $medalId, $data);
        return ['ok' => true, 'message' => 'Медаль выдана.'];
    }
}
