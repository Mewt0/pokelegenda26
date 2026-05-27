<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class BossRepository
{
    public function __construct(
        private PDO $db,
        private BattleRepository $battles,
        private ?RewardRepository $rewards = null,
    ) {
    }

    public function activeForLocation(int $locationId): array
    {
        if ($locationId <= 0 || !$this->tableExists('location_bosses')) {
            return [];
        }

        $now = time();
        $stmt = $this->db->prepare(
            'SELECT b.id, b.location_id, b.title, b.description, b.event_key, b.starts_at, b.ends_at,
                    COUNT(bp.id) AS team_count
               FROM location_bosses b
          LEFT JOIN location_boss_pokemon bp ON bp.boss_id = b.id AND bp.enabled = 1
              WHERE b.location_id = :location
                AND b.enabled = 1
                AND (b.starts_at = 0 OR b.starts_at <= :now_a)
                AND (b.ends_at = 0 OR b.ends_at >= :now_b)
              GROUP BY b.id
             HAVING team_count > 0
              ORDER BY b.id DESC
              LIMIT 10'
        );
        $stmt->execute([
            'location' => $locationId,
            'now_a' => $now,
            'now_b' => $now,
        ]);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'locationId' => (int) $row['location_id'],
                'title' => (string) $row['title'],
                'description' => (string) $row['description'],
                'eventKey' => (string) $row['event_key'],
                'startsAt' => (int) $row['starts_at'],
                'endsAt' => (int) $row['ends_at'],
                'teamCount' => (int) $row['team_count'],
            ];
        }
        return $rows;
    }

    public function adminBosses(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('location_bosses')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE b.id = :id_search OR b.title LIKE :search OR b.event_key LIKE :search OR loc.title LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }

        $stmt = $this->db->prepare(
            'SELECT b.*, loc.title AS location_title,
                    COUNT(DISTINCT bp.id) AS team_count,
                    COUNT(DISTINCT bd.id) AS drop_count
               FROM location_bosses b
          LEFT JOIN build loc ON loc.id = b.location_id
          LEFT JOIN location_boss_pokemon bp ON bp.boss_id = b.id
          LEFT JOIN location_boss_drops bd ON bd.boss_id = b.id
              ' . $where . '
              GROUP BY b.id
              ORDER BY b.id DESC
              LIMIT ' . $limit
        );
        $stmt->execute($params);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $id = (int) $row['id'];
            $row['team'] = $this->bossTeam($id);
            $row['drops'] = $this->bossDrops($id);
            $row['team_json'] = json_encode($row['team'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $row['drops_json'] = json_encode($row['drops'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $rows[] = $row;
        }

        return $rows;
    }

    public function saveBoss(int $adminId, array $payload): array
    {
        if (!$this->tableExists('location_bosses')) {
            return ['ok' => false, 'message' => 'Миграция боссов ещё не применена.'];
        }

        $id = max(0, (int) ($payload['id'] ?? 0));
        $title = trim((string) ($payload['title'] ?? ''));
        $locationId = max(0, (int) ($payload['location_id'] ?? 0));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название босса.'];
        }
        if ($locationId <= 0 || !$this->rowExists('build', 'id', $locationId)) {
            return ['ok' => false, 'message' => 'Выбери существующую локацию.'];
        }

        $team = $this->normalizeTeamJson((string) ($payload['team_json'] ?? ''));
        if ($team === []) {
            return ['ok' => false, 'message' => 'Добавь хотя бы одного покемона босса в team_json.'];
        }
        $drops = $this->normalizeDropsJson((string) ($payload['drops_json'] ?? ''));

        $data = [
            'location_id' => $locationId,
            'title' => mb_substr($title, 0, 120),
            'description' => trim((string) ($payload['description'] ?? '')),
            'event_key' => mb_substr(trim((string) ($payload['event_key'] ?? '')), 0, 64),
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'starts_at' => $this->adminTimestamp((string) ($payload['starts_at'] ?? '0')),
            'ends_at' => $this->adminTimestamp((string) ($payload['ends_at'] ?? '0')),
            'conditions_json' => trim((string) ($payload['conditions_json'] ?? '{}')) ?: '{}',
            'updated_at' => time(),
        ];

        if ($id > 0 && $this->rowExists('location_bosses', 'id', $id)) {
            $before = $this->rowById('location_bosses', 'id', $id);
            $stmt = $this->db->prepare(
                'UPDATE location_bosses
                    SET location_id = :location_id, title = :title, description = :description,
                        event_key = :event_key, enabled = :enabled, starts_at = :starts_at,
                        ends_at = :ends_at, conditions_json = :conditions_json, updated_at = :updated_at
                  WHERE id = :id
                  LIMIT 1'
            );
            $stmt->execute($data + ['id' => $id]);
            $action = 'boss.update';
        } else {
            $before = [];
            $data['created_by'] = $adminId;
            $data['created_at'] = time();
            $stmt = $this->db->prepare(
                'INSERT INTO location_bosses
                    (location_id, title, description, event_key, enabled, starts_at, ends_at,
                     conditions_json, created_by, created_at, updated_at)
                 VALUES
                    (:location_id, :title, :description, :event_key, :enabled, :starts_at, :ends_at,
                     :conditions_json, :created_by, :created_at, :updated_at)'
            );
            $stmt->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'boss.create';
        }

        $this->replaceBossTeam($id, $team);
        $this->replaceBossDrops($id, $drops);
        $this->audit($adminId, $action, 'location_bosses', $id, [
            'before' => $before,
            'after' => $data,
            'team' => $team,
            'drops' => $drops,
        ]);

        return ['ok' => true, 'message' => 'Босс сохранен.', 'bossId' => $id];
    }

    public function deleteBoss(int $adminId, int $id, ?string $confirm): array
    {
        if ($id <= 0 || !$this->rowExists('location_bosses', 'id', $id)) {
            return ['ok' => false, 'message' => 'Босс не найден.'];
        }
        if (trim((string) $confirm) !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }

        $snapshot = [
            'boss' => $this->rowById('location_bosses', 'id', $id),
            'team' => $this->bossTeam($id),
            'drops' => $this->bossDrops($id),
        ];
        $this->audit($adminId, 'boss.delete.before', 'location_bosses', $id, $snapshot);
        $this->db->prepare('DELETE FROM location_boss_drops WHERE boss_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM location_boss_pokemon WHERE boss_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM location_bosses WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'boss.delete', 'location_bosses', $id, $snapshot);
        return ['ok' => true, 'message' => 'Босс удален.'];
    }

    public function startForUser(int $userId, int $bossId): array
    {
        if ($userId <= 0 || $bossId <= 0) {
            return ['ok' => false, 'message' => 'Босс не найден.'];
        }

        $boss = $this->activeBossByIdForUser($userId, $bossId);
        if ($boss === null) {
            return ['ok' => false, 'message' => 'Этот босс сейчас недоступен на вашей локации.'];
        }

        $user = $this->userBattleState($userId);
        if (!$user || (int) $user['pve'] !== 0 || (int) $user['pvp'] !== 0 || (int) $user['trade'] !== 0) {
            return ['ok' => false, 'message' => 'Сейчас нельзя начать бой: игрок уже занят.'];
        }

        $player = $this->firstAlivePlayerPokemon($userId);
        if ($player === null) {
            return ['ok' => false, 'message' => 'Нужен хотя бы один живой покемон в активной команде.'];
        }

        $teamRows = $this->enabledBossTeamRows($bossId);
        if ($teamRows === []) {
            return ['ok' => false, 'message' => 'У босса нет активной команды.'];
        }

        $this->db->beginTransaction();
        try {
            $createdTeam = [];
            foreach ($teamRows as $row) {
                $createdTeam[] = $this->createBossPvePokemon($userId, $row);
            }
            $first = $createdTeam[0] ?? null;
            if (!$first) {
                throw new \RuntimeException('Boss team is empty after creation.');
            }

            $battleId = $this->createBossBattle($userId, (int) $player['id'], (int) $first['pok_pve_id']);
            $this->createSession($userId, $bossId, $battleId, (int) $player['id'], $createdTeam);
            $this->db->prepare('UPDATE users SET pve = 1, pvp = 0, battleid = :battle WHERE id = :user LIMIT 1')
                ->execute(['battle' => $battleId, 'user' => $userId]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось начать бой с боссом: ' . $e->getMessage()];
        }

        return [
            'ok' => true,
            'message' => 'Босс выходит на бой!',
            'battleId' => $battleId,
            'status' => 'active',
        ];
    }

    public function activeSessionForBattle(int $battleId): ?array
    {
        if ($battleId <= 0 || !$this->tableExists('boss_battle_sessions')) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT s.*, b.title, b.description, b.event_key
               FROM boss_battle_sessions s
               INNER JOIN location_bosses b ON b.id = s.boss_id
              WHERE s.battle_id = :battle
                AND s.status = "active"
              LIMIT 1'
        );
        $stmt->execute(['battle' => $battleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function activeMovesForBattle(int $battleId): array
    {
        $session = $this->activeSessionForBattle($battleId);
        if ($session === null) {
            return [];
        }

        $team = $this->decodeJsonList((string) ($session['boss_team_json'] ?? ''));
        $slot = (int) ($session['active_boss_slot'] ?? 1);
        $active = null;
        foreach ($team as $entry) {
            if ((int) ($entry['slot'] ?? 0) === $slot) {
                $active = $entry;
                break;
            }
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $active['moves'] ?? []))));
        if ($ids === []) {
            return [];
        }

        return $this->movesByIds($ids);
    }

    public function decorateBattleState(array $state): array
    {
        $battleId = (int) ($state['battle']['id'] ?? $state['battleId'] ?? 0);
        $session = $this->activeSessionForBattle($battleId);
        if ($session === null) {
            return $state;
        }

        $team = $this->decodeJsonList((string) ($session['boss_team_json'] ?? ''));
        $alive = 0;
        foreach ($team as $entry) {
            if ((int) ($entry['defeated'] ?? 0) === 0) {
                $alive++;
            }
        }

        $meta = [
            'mode' => 'boss',
            'title' => 'Босс: ' . (string) ($session['title'] ?? 'Босс'),
            'bossId' => (int) ($session['boss_id'] ?? 0),
            'activeBossSlot' => (int) ($session['active_boss_slot'] ?? 1),
            'bossTeamAlive' => $alive,
            'bossTeamSize' => count($team),
            'eventKey' => (string) ($session['event_key'] ?? ''),
        ];

        $state['mode'] = 'boss';
        $state['boss'] = $meta;
        if (isset($state['battle']) && is_array($state['battle'])) {
            $state['battle']['mode'] = 'boss';
            $state['battle']['title'] = $meta['title'];
            $state['battle']['boss'] = $meta;
            if (isset($state['battle']['enemy']) && is_array($state['battle']['enemy'])) {
                $state['battle']['enemy']['kind'] = 'Босс';
            }
        }
        return $state;
    }

    public function continueAfterEnemyFaint(int $userId, array $battle, array $enemy, int $round): ?array
    {
        $battleId = (int) ($battle['id'] ?? 0);
        $session = $this->activeSessionForBattle($battleId);
        if ($session === null) {
            return null;
        }

        $team = $this->decodeJsonList((string) ($session['boss_team_json'] ?? ''));
        $currentSlot = (int) ($session['active_boss_slot'] ?? 1);
        foreach ($team as &$entry) {
            if ((int) ($entry['slot'] ?? 0) === $currentSlot) {
                $entry['defeated'] = 1;
            }
        }
        unset($entry);

        $next = null;
        foreach ($team as $entry) {
            if ((int) ($entry['defeated'] ?? 0) === 0) {
                $next = $entry;
                break;
            }
        }

        if ($next === null) {
            $rewards = $this->grantBossRewards($userId, (int) ($session['boss_id'] ?? 0));
            $this->finishSession((int) $session['id'], 'won', $team, $rewards);
            return [
                'continued' => false,
                'won' => true,
                'rewards' => $rewards,
                'message' => 'Команда босса повержена.',
            ];
        }

        $nextSlot = (int) ($next['slot'] ?? 1);
        $nextPveId = (int) ($next['pok_pve_id'] ?? 0);
        $this->db->prepare(
            'UPDATE boss_battle_sessions
                SET active_boss_slot = :slot, boss_team_json = :team, updated_at = :updated
              WHERE id = :id
              LIMIT 1'
        )->execute([
            'slot' => $nextSlot,
            'team' => json_encode($team, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated' => time(),
            'id' => (int) $session['id'],
        ]);
        $this->battles->patchBattleEnemy($battleId, $nextPveId);
        $this->battles->initializeBattlePokemonStats($battleId, 'pve_' . $nextPveId);
        $message = sprintf('Босс выпускает следующего покемона: %s.', (string) ($next['name'] ?? ('слот #' . $nextSlot)));
        $this->battles->insertBattleLog($battleId, $round, $message);

        return [
            'continued' => true,
            'won' => false,
            'message' => $message,
        ];
    }

    public function continueAfterPlayerFaint(int $userId, array $battle, int $round): ?array
    {
        $battleId = (int) ($battle['id'] ?? 0);
        $session = $this->activeSessionForBattle($battleId);
        if ($session === null) {
            return null;
        }

        $currentId = $this->battlePokemonId((string) ($battle['poke_1'] ?? ''));
        $next = $this->nextAlivePlayerPokemon($userId, $currentId);
        if ($next === null) {
            $this->finishSession((int) $session['id'], 'lost', $this->decodeJsonList((string) $session['boss_team_json']), []);
            return [
                'continued' => false,
                'lost' => true,
                'message' => 'Вся команда игрока проиграла.',
            ];
        }

        $this->battles->switchPlayerPokemon($battleId, $userId, (int) $next['id']);
        $this->battles->initializeBattlePokemonStats($battleId, 'pvp_' . (int) $next['id']);
        $this->db->prepare('UPDATE boss_battle_sessions SET active_player_pokemon_id = :pokemon, updated_at = :updated WHERE id = :id LIMIT 1')
            ->execute(['pokemon' => (int) $next['id'], 'updated' => time(), 'id' => (int) $session['id']]);
        $message = sprintf('%s выходит продолжать бой с боссом.', strip_tags((string) ($next['names'] ?? 'Следующий покемон')));
        $this->battles->insertBattleLog($battleId, $round, $message);

        return [
            'continued' => true,
            'lost' => false,
            'message' => $message,
        ];
    }

    public function cleanupSessionForBattle(int $battleId, int $userId): void
    {
        $session = $this->activeOrFinishedSessionForBattle($battleId, $userId);
        if ($session === null) {
            return;
        }
        foreach ($this->decodeJsonList((string) ($session['boss_team_json'] ?? '')) as $entry) {
            $pokPveId = (int) ($entry['pok_pve_id'] ?? 0);
            if ($pokPveId > 0) {
                $this->db->prepare('DELETE FROM pok_pve WHERE id = :id LIMIT 1')->execute(['id' => $pokPveId]);
            }
        }
        $this->db->prepare('UPDATE boss_battle_sessions SET status = "acked", updated_at = :now WHERE id = :id LIMIT 1')
            ->execute(['now' => time(), 'id' => (int) $session['id']]);
    }

    private function activeBossByIdForUser(int $userId, int $bossId): ?array
    {
        $user = $this->userBattleState($userId);
        if (!$user) {
            return null;
        }
        foreach ($this->activeForLocation((int) $user['buildmy']) as $boss) {
            if ((int) $boss['id'] === $bossId) {
                return $boss;
            }
        }
        return null;
    }

    private function userBattleState(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, buildmy, pve, pvp, trade FROM users WHERE id = :id AND activation = 1 LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function firstAlivePlayerPokemon(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names
               FROM pok_user
              WHERE users = :user AND active = 1 AND hp_my > 0
              ORDER BY startepoke DESC, id ASC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function nextAlivePlayerPokemon(int $userId, int $currentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, names
               FROM pok_user
              WHERE users = :user AND active = 1 AND hp_my > 0 AND id <> :current
              ORDER BY startepoke DESC, id ASC
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId, 'current' => $currentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function enabledBossTeamRows(int $bossId): array
    {
        $stmt = $this->db->prepare(
            'SELECT bp.*, p.Name AS dex_name, pb.title AS base_title
               FROM location_boss_pokemon bp
          LEFT JOIN pokemon p ON p.id = bp.base_pokemon_id
          LEFT JOIN poke_base pb ON pb.id = bp.base_pokemon_id
              WHERE bp.boss_id = :boss AND bp.enabled = 1
              ORDER BY bp.slot_no ASC
              LIMIT 6'
        );
        $stmt->execute(['boss' => $bossId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function createBossPvePokemon(int $userId, array $row): array
    {
        $baseId = (int) ($row['base_pokemon_id'] ?? 0);
        $level = max(1, min(100, (int) ($row['level'] ?? 50)));
        $base = $this->loadPokemonBase($baseId);
        if ($base === null) {
            throw new \RuntimeException('Не найдена база покемона #' . $baseId);
        }

        $stats = $this->statsFromRow($row, $base, $level);
        $name = trim((string) (($row['base_title'] ?? '') ?: ($row['dex_name'] ?? ''))) ?: ('Pokemon #' . $baseId);
        $tips = (int) ($row['shiny'] ?? 0) === 1 ? 'shine' : 'normal';
        if ($tips === 'shine' && !str_contains(mb_strtolower($name), 'shiny')) {
            $name .= ' - Shiny';
        }

        $id = $this->nextTableIdLocked('pok_pve', 'id');
        $stmt = $this->db->prepare(
            'INSERT INTO pok_pve
                (id, users, basenum, names, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                 atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                 hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, poimka, startepoke,
                 reproduction, happy, battleid, sprz, ability_key)
             VALUES
                (:id, :users, :basenum, :names, 0, :lvl, :sex, :har, :hp_my, :hp_max, 0, 0,
                 :atk, :def, :satk, :sdef, :speed, :hp_ev, :atk_ev, :def_ev, :satk_ev, :sdef_ev, :speed_ev,
                 :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv, :tips, 0, :startepoke,
                 0, 0, 0, :sprz, :ability_key)'
        );
        $stmt->execute([
            'id' => $id,
            'users' => $userId,
            'basenum' => $baseId,
            'names' => $name,
            'lvl' => $level,
            'sex' => max(1, min(2, (int) ($row['gender'] ?? 1))),
            'har' => max(1, (int) ($row['nature_id'] ?? 1)),
            'hp_my' => $stats['hp'],
            'hp_max' => $stats['hp'],
            'atk' => $stats['atk'],
            'def' => $stats['def'],
            'satk' => $stats['satk'],
            'sdef' => $stats['sdef'],
            'speed' => $stats['speed'],
            'hp_ev' => $stats['ev']['hp'],
            'atk_ev' => $stats['ev']['atk'],
            'def_ev' => $stats['ev']['def'],
            'satk_ev' => $stats['ev']['satk'],
            'sdef_ev' => $stats['ev']['sdef'],
            'speed_ev' => $stats['ev']['speed'],
            'hp_iv' => $stats['iv']['hp'],
            'atk_iv' => $stats['iv']['atk'],
            'def_iv' => $stats['iv']['def'],
            'satk_iv' => $stats['iv']['satk'],
            'sdef_iv' => $stats['iv']['sdef'],
            'speed_iv' => $stats['iv']['speed'],
            'tips' => $tips,
            'startepoke' => (int) ($row['slot_no'] ?? 1),
            'sprz' => (int) ($row['held_item_id'] ?? 0),
            'ability_key' => trim((string) ($row['ability_key'] ?? '')) ?: null,
        ]);

        $moves = array_values(array_filter(array_map('intval', $this->decodeJsonList((string) ($row['moves_json'] ?? '')))));
        if ($moves === []) {
            $moves = array_column($this->battles->findAvailableMoves($baseId, $level), 'id');
        }

        return [
            'slot' => (int) ($row['slot_no'] ?? 1),
            'pok_pve_id' => $id,
            'base_id' => $baseId,
            'name' => $name,
            'level' => $level,
            'moves' => array_slice(array_values(array_unique(array_map('intval', $moves))), 0, 4),
            'held_item_id' => (int) ($row['held_item_id'] ?? 0),
            'defeated' => 0,
        ];
    }

    private function createBossBattle(int $userId, int $playerPokemonId, int $enemyPokemonId): int
    {
        $battleId = $this->nextTableIdLocked('battles', 'id');
        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO battles
                (id, user_1, user_2, poke_1, poke_2, attac_1, attac_2, item_1, item_2,
                 to_p, to_p2, batl_tip, time, pobeda, raund, effect_go, effect_go2,
                 effect, effect2, id_pogodi, times, time_1, time_2, to_it, to_it2,
                 hod_user_id, tips_battle, zamtru_1, zamtru_2, room, dates)
             VALUES
                (:id, :user_1, :user_2, :poke_1, :poke_2, 0, 0, 0, 0,
                 0, 0, "pve", 0, 0, 1, 0, 0,
                 0, 0, 1, :now, 0, 0, 0, 0,
                 0, 0, 0, 0, 0, "")'
        );
        $stmt->execute([
            'id' => $battleId,
            'user_1' => $userId,
            'user_2' => $enemyPokemonId,
            'poke_1' => 'pvp_' . $playerPokemonId,
            'poke_2' => 'pve_' . $enemyPokemonId,
            'now' => $now,
        ]);
        $this->battles->initializeBattlePokemonStats($battleId, 'pvp_' . $playerPokemonId);
        $this->battles->initializeBattlePokemonStats($battleId, 'pve_' . $enemyPokemonId);
        return $battleId;
    }

    private function createSession(int $userId, int $bossId, int $battleId, int $playerPokemonId, array $team): void
    {
        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO boss_battle_sessions
                (boss_id, user_id, battle_id, status, active_player_pokemon_id, active_boss_slot,
                 boss_team_json, rewards_json, started_at, finished_at, updated_at)
             VALUES
                (:boss, :user, :battle, "active", :player, 1, :team, "[]", :started, 0, :updated)'
        );
        $stmt->execute([
            'boss' => $bossId,
            'user' => $userId,
            'battle' => $battleId,
            'player' => $playerPokemonId,
            'team' => json_encode($team, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'started' => $now,
            'updated' => $now,
        ]);
    }

    private function grantBossRewards(int $userId, int $bossId): array
    {
        $drops = [];
        $dropMultiplier = $this->rewards?->activeMultiplier($userId, 'drop', 'pve') ?? 1.0;
        foreach ($this->bossDrops($bossId) as $row) {
            if (empty($row['enabled'])) {
                continue;
            }
            $chance = min(100.0, (float) ($row['chance_percent'] ?? 0) * $dropMultiplier);
            $guaranteed = (int) ($row['guaranteed'] ?? 0) === 1;
            if (!$guaranteed && ($chance <= 0 || random_int(1, 1000000) > (int) round(min(100, $chance) * 10000))) {
                continue;
            }
            $min = max(1, (int) ($row['min_count'] ?? 1));
            $max = max($min, (int) ($row['max_count'] ?? $min));
            $count = random_int($min, $max);
            $itemId = (int) ($row['item_id'] ?? 0);
            if ($itemId <= 0) {
                continue;
            }
            $this->rewards?->grantItems($userId, [$itemId => $count], 'Награда за босса');
            $drops[] = [
                'itemId' => $itemId,
                'name' => (string) ($row['item_name'] ?? ('Предмет #' . $itemId)),
                'count' => $count,
                'guaranteed' => $guaranteed,
                'rare' => (int) ($row['rare'] ?? 0) === 1,
            ];
        }
        return ['drops' => $drops];
    }

    private function finishSession(int $sessionId, string $status, array $team, array $rewards): void
    {
        $this->db->prepare(
            'UPDATE boss_battle_sessions
                SET status = :status, boss_team_json = :team, rewards_json = :rewards,
                    finished_at = :finished, updated_at = :updated
              WHERE id = :id
              LIMIT 1'
        )->execute([
            'status' => $status,
            'team' => json_encode($team, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'rewards' => json_encode($rewards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'finished' => time(),
            'updated' => time(),
            'id' => $sessionId,
        ]);
    }

    private function loadPokemonBase(int $baseId): ?array
    {
        $stmt = $this->db->prepare('SELECT hp, atk, def, satk, sdef, speed, title FROM poke_base WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $baseId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function statsFromRow(array $row, array $base, int $level): array
    {
        $manual = $this->decodeJsonMap((string) ($row['stats_json'] ?? ''));
        $iv = [
            'hp' => max(0, min(31, (int) ($manual['hp_iv'] ?? 31))),
            'atk' => max(0, min(31, (int) ($manual['atk_iv'] ?? 31))),
            'def' => max(0, min(31, (int) ($manual['def_iv'] ?? 31))),
            'satk' => max(0, min(31, (int) ($manual['satk_iv'] ?? 31))),
            'sdef' => max(0, min(31, (int) ($manual['sdef_iv'] ?? 31))),
            'speed' => max(0, min(31, (int) ($manual['speed_iv'] ?? 31))),
        ];
        $ev = [
            'hp' => max(0, min(252, (int) ($manual['hp_ev'] ?? 120))),
            'atk' => max(0, min(252, (int) ($manual['atk_ev'] ?? 120))),
            'def' => max(0, min(252, (int) ($manual['def_ev'] ?? 120))),
            'satk' => max(0, min(252, (int) ($manual['satk_ev'] ?? 120))),
            'sdef' => max(0, min(252, (int) ($manual['sdef_ev'] ?? 120))),
            'speed' => max(0, min(252, (int) ($manual['speed_ev'] ?? 120))),
        ];

        $computed = [
            'hp' => (int) floor(((2 * (int) $base['hp'] + $iv['hp'] + (int) floor($ev['hp'] / 4)) * $level) / 100) + $level + 10,
            'atk' => (int) floor(((2 * (int) $base['atk'] + $iv['atk'] + (int) floor($ev['atk'] / 4)) * $level) / 100) + 5,
            'def' => (int) floor(((2 * (int) $base['def'] + $iv['def'] + (int) floor($ev['def'] / 4)) * $level) / 100) + 5,
            'satk' => (int) floor(((2 * (int) $base['satk'] + $iv['satk'] + (int) floor($ev['satk'] / 4)) * $level) / 100) + 5,
            'sdef' => (int) floor(((2 * (int) $base['sdef'] + $iv['sdef'] + (int) floor($ev['sdef'] / 4)) * $level) / 100) + 5,
            'speed' => (int) floor(((2 * (int) $base['speed'] + $iv['speed'] + (int) floor($ev['speed'] / 4)) * $level) / 100) + 5,
        ];

        foreach (['hp', 'atk', 'def', 'satk', 'sdef', 'speed'] as $key) {
            if (isset($manual[$key]) && (int) $manual[$key] > 0) {
                $computed[$key] = (int) $manual[$key];
            }
        }

        return $computed + ['iv' => $iv, 'ev' => $ev];
    }

    private function movesByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            'SELECT atac_id AS id, atac_name, atac_tip, atac_power, atac_accuracy, atac_categori,
                    critic, priorety, atac_pp, chans_dop, chans_effect, atac_not, stati, attac_effecti,
                    titles, atac_tittle, tittle_effect
               FROM attac_power
              WHERE atac_id IN (' . $placeholders . ')'
        );
        $stmt->execute($ids);
        $byId = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $move) {
            $byId[(int) $move['id']] = $move;
        }
        $result = [];
        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $result[] = $byId[$id];
            }
        }
        return $result;
    }

    private function replaceBossTeam(int $bossId, array $team): void
    {
        $this->db->prepare('DELETE FROM location_boss_pokemon WHERE boss_id = :boss')->execute(['boss' => $bossId]);
        $stmt = $this->db->prepare(
            'INSERT INTO location_boss_pokemon
                (boss_id, slot_no, base_pokemon_id, level, gender, nature_id, shiny, held_item_id,
                 ability_key, stats_json, moves_json, item_effects_json, enabled)
             VALUES
                (:boss_id, :slot_no, :base_pokemon_id, :level, :gender, :nature_id, :shiny, :held_item_id,
                 :ability_key, :stats_json, :moves_json, :item_effects_json, :enabled)'
        );
        foreach (array_slice($team, 0, 6) as $slot => $entry) {
            $stmt->execute([
                'boss_id' => $bossId,
                'slot_no' => (int) ($entry['slot'] ?? ($slot + 1)),
                'base_pokemon_id' => (int) ($entry['base_id'] ?? $entry['base_pokemon_id'] ?? 0),
                'level' => max(1, min(100, (int) ($entry['level'] ?? 50))),
                'gender' => max(1, min(2, (int) ($entry['gender'] ?? 1))),
                'nature_id' => max(1, (int) ($entry['nature_id'] ?? 1)),
                'shiny' => !empty($entry['shiny']) ? 1 : 0,
                'held_item_id' => max(0, (int) ($entry['held_item_id'] ?? 0)),
                'ability_key' => mb_substr((string) ($entry['ability_key'] ?? ''), 0, 64),
                'stats_json' => json_encode($entry['stats'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'moves_json' => json_encode(array_slice(array_values(array_filter(array_map('intval', $entry['moves'] ?? []))), 0, 4), JSON_UNESCAPED_UNICODE),
                'item_effects_json' => json_encode($entry['item_effects'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'enabled' => array_key_exists('enabled', $entry) ? (!empty($entry['enabled']) ? 1 : 0) : 1,
            ]);
        }
    }

    private function replaceBossDrops(int $bossId, array $drops): void
    {
        $this->db->prepare('DELETE FROM location_boss_drops WHERE boss_id = :boss')->execute(['boss' => $bossId]);
        if ($drops === []) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO location_boss_drops
                (boss_id, item_id, chance_percent, min_count, max_count, guaranteed, rare, enabled)
             VALUES
                (:boss_id, :item_id, :chance_percent, :min_count, :max_count, :guaranteed, :rare, :enabled)'
        );
        foreach ($drops as $entry) {
            $min = max(1, (int) ($entry['min_count'] ?? $entry['count'] ?? 1));
            $max = max($min, (int) ($entry['max_count'] ?? $entry['count'] ?? $min));
            $stmt->execute([
                'boss_id' => $bossId,
                'item_id' => max(0, (int) ($entry['item_id'] ?? 0)),
                'chance_percent' => number_format(max(0, min(100, (float) ($entry['chance_percent'] ?? 0))), 4, '.', ''),
                'min_count' => $min,
                'max_count' => $max,
                'guaranteed' => !empty($entry['guaranteed']) ? 1 : 0,
                'rare' => !empty($entry['rare']) ? 1 : 0,
                'enabled' => array_key_exists('enabled', $entry) ? (!empty($entry['enabled']) ? 1 : 0) : 1,
            ]);
        }
    }

    private function bossTeam(int $bossId): array
    {
        if ($bossId <= 0) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT bp.*, p.Name AS pokemon_name, pb.title AS base_title
               FROM location_boss_pokemon bp
          LEFT JOIN pokemon p ON p.id = bp.base_pokemon_id
          LEFT JOIN poke_base pb ON pb.id = bp.base_pokemon_id
              WHERE bp.boss_id = :boss
              ORDER BY bp.slot_no ASC'
        );
        $stmt->execute(['boss' => $bossId]);
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = [
                'slot' => (int) $row['slot_no'],
                'base_id' => (int) $row['base_pokemon_id'],
                'name' => (string) (($row['base_title'] ?? '') ?: ($row['pokemon_name'] ?? '')),
                'level' => (int) $row['level'],
                'gender' => (int) $row['gender'],
                'nature_id' => (int) $row['nature_id'],
                'shiny' => (int) $row['shiny'] === 1,
                'held_item_id' => (int) $row['held_item_id'],
                'ability_key' => (string) $row['ability_key'],
                'stats' => $this->decodeJsonMap((string) $row['stats_json']),
                'moves' => array_values(array_map('intval', $this->decodeJsonList((string) $row['moves_json']))),
                'item_effects' => $this->decodeJsonMap((string) $row['item_effects_json']),
                'enabled' => (int) $row['enabled'] === 1,
            ];
        }
        return $rows;
    }

    private function bossDrops(int $bossId): array
    {
        if ($bossId <= 0) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT d.*, i.name AS item_name
               FROM location_boss_drops d
          LEFT JOIN items i ON i.id = d.item_id
              WHERE d.boss_id = :boss
              ORDER BY d.id ASC'
        );
        $stmt->execute(['boss' => $bossId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function normalizeTeamJson(string $json): array
    {
        $rows = $this->decodeJsonList($json);
        $team = [];
        foreach (array_slice($rows, 0, 6) as $index => $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $baseId = (int) ($entry['base_id'] ?? $entry['base_pokemon_id'] ?? 0);
            if ($baseId <= 0) {
                continue;
            }
            $entry['slot'] = max(1, min(6, (int) ($entry['slot'] ?? ($index + 1))));
            $entry['base_id'] = $baseId;
            $entry['level'] = max(1, min(100, (int) ($entry['level'] ?? 50)));
            $entry['moves'] = array_slice(array_values(array_filter(array_map('intval', $entry['moves'] ?? []))), 0, 4);
            $team[] = $entry;
        }
        return $team;
    }

    private function normalizeDropsJson(string $json): array
    {
        $rows = $this->decodeJsonList($json);
        $drops = [];
        foreach ($rows as $entry) {
            if (!is_array($entry) || (int) ($entry['item_id'] ?? 0) <= 0) {
                continue;
            }
            $drops[] = $entry;
        }
        return $drops;
    }

    private function decodeJsonList(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function decodeJsonMap(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function activeOrFinishedSessionForBattle(int $battleId, int $userId): ?array
    {
        if ($battleId <= 0 || !$this->tableExists('boss_battle_sessions')) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT * FROM boss_battle_sessions WHERE battle_id = :battle AND user_id = :user ORDER BY id DESC LIMIT 1');
        $stmt->execute(['battle' => $battleId, 'user' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function battlePokemonId(string $battlePokemon): int
    {
        if (preg_match('/_(\d+)$/', $battlePokemon, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    private function rowExists(string $table, string $column, int $id): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return false;
        }
        $stmt = $this->db->prepare(sprintf('SELECT 1 FROM `%s` WHERE `%s` = :id LIMIT 1', $table, $column));
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function rowById(string $table, string $column, int $id): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return [];
        }
        $stmt = $this->db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $table, $column));
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare('SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
        $stmt->execute(['table' => $table]);
        return $cache[$table] = (bool) $stmt->fetchColumn();
    }

    private function adminTimestamp(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return 0;
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        $time = strtotime($value);
        return $time === false ? 0 : $time;
    }

    private function nextTableIdLocked(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException('Unsupported sequence target.');
        }
        if ($table === 'battles' && $column === 'id') {
            return $this->nextBattleIdLocked();
        }

        $name = 'pokemon8_seq_' . $table . '_' . $column;
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => $name]);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            throw new \RuntimeException('Не удалось получить блокировку ID.');
        }
        try {
            return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $name]);
        }
    }

    private function nextBattleIdLocked(): int
    {
        $lockAcquired = false;
        try {
            if (!$this->tableExists('battle_id_sequence')) {
                if ($this->db->inTransaction()) {
                    throw new \RuntimeException('battle_id_sequence is not available inside transaction.');
                }
                $this->db->exec(
                    'CREATE TABLE IF NOT EXISTS battle_id_sequence (
                        id TINYINT NOT NULL PRIMARY KEY,
                        next_id INT(11) NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }

            $lockAcquired = ((int) ($this->db->query('SELECT GET_LOCK("pokemon8_seq_battles_id", 15)')->fetchColumn() ?: 0)) === 1;
            $maxId = (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM battles')->fetchColumn() ?: 1);
            $seed = max(time(), $maxId, 1);
            $insert = $this->db->prepare(
                'INSERT INTO battle_id_sequence (id, next_id)
                 VALUES (1, :seed)
                 ON DUPLICATE KEY UPDATE next_id = GREATEST(next_id, VALUES(next_id))'
            );
            $insert->execute(['seed' => $seed]);

            $current = (int) ($this->db->query('SELECT next_id FROM battle_id_sequence WHERE id = 1')->fetchColumn() ?: 0);
            $candidate = max($current, $seed, $maxId);
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $exists = $this->db->prepare('SELECT 1 FROM battles WHERE id = :id LIMIT 1');
                $exists->execute(['id' => $candidate]);
                if ($exists->fetchColumn() === false) {
                    $update = $this->db->prepare('UPDATE battle_id_sequence SET next_id = :next WHERE id = 1');
                    $update->execute(['next' => $candidate + 1]);
                    return $candidate;
                }
                $candidate++;
            }
        } catch (Throwable) {
            // fallback to runtime error below
        } finally {
            if ($lockAcquired) {
                try {
                    $this->db->query('SELECT RELEASE_LOCK("pokemon8_seq_battles_id")');
                } catch (Throwable) {
                    // no-op
                }
            }
        }

        throw new \RuntimeException('Не удалось выделить id боя.');
    }

    private function audit(int $adminId, string $action, string $entity, int $entityId, array $payload): void
    {
        if (!$this->tableExists('admin_audit_log')) {
            return;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO admin_audit_log (admin_id, action, entity, entity_id, payload, created_at)
             VALUES (:admin, :action, :entity, :entity_id, :payload, :created_at)'
        );
        $stmt->execute([
            'admin' => $adminId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => time(),
        ]);
    }
}
