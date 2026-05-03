<?php
declare(strict_types=1);

namespace Pokemon8\Game;

use PDO;

final class WildEncounterService
{
    private ?string $lastBattleCreateError = null;

    public function __construct(private PDO $db)
    {
    }

    public function tryStartForUser(int $userId): ?array
    {
        $user = $this->loadUserForEncounter($userId);
        if ($user === null) {
            return null;
        }

        if (
            (int) ($user['build_pve'] ?? 0) <= 0
            || (int) ($user['pve_button'] ?? 0) !== 1
            || (int) ($user['pvp'] ?? 0) !== 0
            || (int) ($user['pve'] ?? 0) !== 0
            || (int) ($user['trade'] ?? 0) !== 0
            || (int) ($user['atack_poke'] ?? 0) > time()
        ) {
            return null;
        }

        $activeCount = $this->countActiveAlivePokemon($userId);
        if ($activeCount <= 0) {
            return [
                'ok' => true,
                'wildEncounter' => [
                    'started' => false,
                    'message' => 'Ваши покемоны слишком слабы. Восстановите их в покецентре.',
                ],
            ];
        }

        $encounter = $this->pickEncounter((int) $user['buildmy']);
        if ($encounter === null) {
            return [
                'ok' => true,
                'wildEncounter' => [
                    'started' => false,
                    'message' => 'Для этой локации сейчас нет доступных диких покемонов.',
                ],
            ];
        }

        $playerPokemonId = $this->pickStarterPokemonId($userId);
        if ($playerPokemonId <= 0) {
            return null;
        }

        $enemy = $this->createEnemyPokemon($userId, $encounter);
        if ($enemy === null) {
            return null;
        }

        $battleId = $this->createPveBattle($userId, $playerPokemonId, $enemy['id']);
        if ($battleId <= 0) {
            return null;
        }

        $this->markUserInPveBattle($userId, $battleId);

        return [
            'ok' => true,
            'wildEncounter' => [
                'started' => true,
                'message' => 'На вас напал дикий покемон!',
                'battleId' => $battleId,
            ],
        ];
    }

    /**
     * Отладочный принудительный старт боя.
     * Ограничен конкретной локацией, чтобы не ломать остальной мир.
     */
    public function forceStartForUserAtLocation(int $userId, int $allowedLocationId): array
    {
        $this->lastBattleCreateError = null;

        $user = $this->loadUserForEncounter($userId);
        if ($user === null) {
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Пользователь не найден.']];
        }

        $locationId = (int) ($user['buildmy'] ?? 0);
        if ($locationId !== $allowedLocationId) {
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Отладочная кнопка доступна только на выбранной локации.']];
        }

        if (
            (int) ($user['build_pve'] ?? 0) <= 0
            || (int) ($user['pvp'] ?? 0) !== 0
            || (int) ($user['pve'] ?? 0) !== 0
            || (int) ($user['trade'] ?? 0) !== 0
        ) {
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Сейчас нельзя начать бой (занят/уже в бою).']];
        }

        $activeCount = $this->countActiveAlivePokemon($userId);
        if ($activeCount <= 0) {
            return [
                'ok' => true,
                'wildEncounter' => [
                    'started' => false,
                    'message' => 'Ваши покемоны слишком слабы. Восстановите их в покецентре.',
                ],
            ];
        }

        $encounter = $this->pickEncounter($locationId);
        if ($encounter === null) {
            return [
                'ok' => true,
                'wildEncounter' => [
                    'started' => false,
                    'message' => 'Для этой локации сейчас нет доступных диких покемонов.',
                ],
            ];
        }

        $playerPokemonId = $this->pickStarterPokemonId($userId);
        if ($playerPokemonId <= 0) {
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Не найден активный покемон.']];
        }

        $enemy = $this->createEnemyPokemon($userId, $encounter);
        if ($enemy === null) {
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Не удалось создать дикого покемона.']];
        }

        $battleId = $this->createPveBattle($userId, $playerPokemonId, $enemy['id']);
        if ($battleId <= 0) {
            $extra = $this->lastBattleCreateError ? (' ' . $this->lastBattleCreateError) : '';
            return ['ok' => false, 'wildEncounter' => ['started' => false, 'message' => 'Не удалось создать бой.' . $extra]];
        }

        $this->markUserInPveBattle($userId, $battleId);

        return [
            'ok' => true,
            'wildEncounter' => [
                'started' => true,
                'message' => '[DEBUG] Принудительный бой запущен.',
                'battleId' => $battleId,
            ],
        ];
    }

    private function loadUserForEncounter(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.buildmy, u.pve, u.pvp, u.trade, u.pve_button, u.atack_poke, b.pve AS build_pve
               FROM users u
               INNER JOIN build b ON b.id = u.buildmy
              WHERE u.id = :id AND u.activation = 1
              LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function countActiveAlivePokemon(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1 AND hp_my > 0'
        );
        $stmt->execute(['user' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    private function pickEncounter(int $locationId): ?array
    {
        // 1) Основной путь: как в legacy, учитываем временные окна.
        $stmt = $this->db->prepare(
            'SELECT id, baseid, poimka, questupdate, lvl, sprz, chance
               FROM pokebuild
              WHERE building = :building
                AND CURTIME() BETWEEN timeone AND timetwo
              ORDER BY (RAND() * GREATEST(chance, 1)) DESC
              LIMIT 1'
        );
        $stmt->execute(['building' => $locationId]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }

        // 2) Fallback для новых/неполных данных: берем дикого покемона по локации без фильтра по времени.
        $fallback = $this->db->prepare(
            'SELECT id, baseid, poimka, questupdate, lvl, sprz, chance
               FROM pokebuild
              WHERE building = :building
              ORDER BY (RAND() * GREATEST(chance, 1)) DESC
              LIMIT 1'
        );
        $fallback->execute(['building' => $locationId]);
        $row = $fallback->fetch();

        return $row ?: null;
    }

    private function pickStarterPokemonId(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE users = :user AND active = 1 AND startepoke = 1 AND hp_my > 0
              LIMIT 1'
        );
        $stmt->execute(['user' => $userId]);
        $starter = $stmt->fetchColumn();
        if ($starter !== false) {
            return (int) $starter;
        }

        $fallback = $this->db->prepare(
            'SELECT id
               FROM pok_user
              WHERE users = :user AND active = 1 AND hp_my > 0
              ORDER BY RAND()
              LIMIT 1'
        );
        $fallback->execute(['user' => $userId]);

        return (int) ($fallback->fetchColumn() ?: 0);
    }

    private function createEnemyPokemon(int $userId, array $encounter): ?array
    {
        $baseId = (int) ($encounter['baseid'] ?? 0);
        if ($baseId <= 0) {
            return null;
        }

        $levelBase = max(1, (int) ($encounter['lvl'] ?? 1));
        $level = random_int($levelBase, $levelBase + 4);
        $base = $this->loadPokemonBase($baseId);
        if ($base === null) {
            return null;
        }

        $stats = $this->rollStats($base, $level);
        $name = (string) ($base['title'] ?? ('Pokemon #' . $baseId));
        $tips = 'normal';
        if ((int) ($encounter['poimka'] ?? 0) === 1 && random_int(1, 1000000) <= 10) {
            $tips = 'shine';
            $name = '<span class="pokesShiny">' . $name . ' - <b>Shiny</b></span>';
        }

        $enemyId = $this->nextPokPveId();
        if ($enemyId <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO pok_pve (
                id, users, basenum, names, lvl, sex,
                hp_my, hp_max, atk, def, satk, sdef, speed,
                hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv,
                tips, startepoke, reproduction, sprz, poimka
            ) VALUES (
                :id, :users, :basenum, :names, :lvl, :sex,
                :hp_my, :hp_max, :atk, :def, :satk, :sdef, :speed,
                :hp_ev, :atk_ev, :def_ev, :satk_ev, :sdef_ev, :speed_ev,
                :hp_iv, :atk_iv, :def_iv, :satk_iv, :sdef_iv, :speed_iv,
                :tips, :startepoke, :reproduction, :sprz, :poimka
            )'
        );

        $ok = $stmt->execute([
            'id' => $enemyId,
            'users' => $userId,
            'basenum' => $baseId,
            'names' => $name,
            'lvl' => $level,
            'sex' => random_int(1, 2),
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
            'startepoke' => (int) ($encounter['id'] ?? 0),
            'reproduction' => (int) ($encounter['questupdate'] ?? 0),
            'sprz' => (string) ($encounter['sprz'] ?? '0'),
            'poimka' => (int) ($encounter['poimka'] ?? 0),
        ]);

        if (!$ok) {
            return null;
        }

        $insertedId = (int) $this->db->lastInsertId();
        if ($insertedId <= 0) {
            $insertedId = $enemyId;
        }

        return ['id' => $insertedId];
    }

    private function loadPokemonBase(int $baseId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT hp, atk, def, satk, sdef, speed, title FROM poke_base WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $baseId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    private function rollStats(array $base, int $level): array
    {
        $iv = [
            'hp' => random_int(15, 31),
            'atk' => random_int(2, 31),
            'def' => random_int(15, 31),
            'satk' => random_int(2, 31),
            'sdef' => random_int(15, 31),
            'speed' => random_int(11, 31),
        ];
        $ev = [
            'hp' => random_int(50, 80),
            'atk' => random_int(30, 100),
            'def' => random_int(50, 80),
            'satk' => random_int(30, 100),
            'sdef' => random_int(50, 80),
            'speed' => random_int(1, 150),
        ];

        $hpBase = (int) ($base['hp'] ?? 1);
        $atkBase = (int) ($base['atk'] ?? 1);
        $defBase = (int) ($base['def'] ?? 1);
        $satkBase = (int) ($base['satk'] ?? 1);
        $sdefBase = (int) ($base['sdef'] ?? 1);
        $speedBase = (int) ($base['speed'] ?? 1);

        return [
            'hp' => (int) floor(((2 * $hpBase + $iv['hp'] + (int) floor($ev['hp'] / 4)) * $level) / 100) + $level + 10,
            'atk' => (int) floor(((2 * $atkBase + $iv['atk'] + (int) floor($ev['atk'] / 4)) * $level) / 100) + 5,
            'def' => (int) floor(((2 * $defBase + $iv['def'] + (int) floor($ev['def'] / 4)) * $level) / 100) + 5,
            'satk' => (int) floor(((2 * $satkBase + $iv['satk'] + (int) floor($ev['satk'] / 4)) * $level) / 100) + 5,
            'sdef' => (int) floor(((2 * $sdefBase + $iv['sdef'] + (int) floor($ev['sdef'] / 4)) * $level) / 100) + 5,
            'speed' => (int) floor(((2 * $speedBase + $iv['speed'] + (int) floor($ev['speed'] / 4)) * $level) / 100) + 5,
            'iv' => $iv,
            'ev' => $ev,
        ];
    }

    private function createPveBattle(int $userId, int $playerPokemonId, int $enemyPokemonId): int
    {
        // Legacy-совместимый формат создания PvE боя (см. include/files/gameload.world.php).
        $manualBattleId = $this->nextBattleId();
        if ($manualBattleId <= 0) {
            $this->lastBattleCreateError = '(cannot allocate next battle id)';
            return 0;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO battles (id, user_1, user_2, poke_1, poke_2, batl_tip, times)
             VALUES (:id, :user_1, :user_2, :poke_1, :poke_2, :batl_tip, :times)'
        );

        try {
            $ok = $stmt->execute([
                'id' => $manualBattleId,
                'user_1' => $userId,
                // В legacy PvE сюда кладут id записи pok_pve.
                'user_2' => $enemyPokemonId,
                'poke_1' => 'pvp_' . $playerPokemonId,
                'poke_2' => 'pve_' . $enemyPokemonId,
                'batl_tip' => 'pve',
                'times' => time() + 3600,
            ]);
        } catch (\Throwable $e) {
            $this->lastBattleCreateError = '(EX) ' . $e->getMessage();
            error_log('[PVE_FORCE] createPveBattle exception: ' . $e->getMessage());
            return 0;
        }

        if (!$ok) {
            $err = $stmt->errorInfo();
            $msg = is_array($err) ? trim(($err[0] ?? '') . ' ' . ($err[2] ?? '')) : 'unknown';
            $cols = $this->battleColumnsDebug();
            $colsText = $cols ? (' cols=' . implode(',', $cols)) : '';
            $this->lastBattleCreateError = '(' . $msg . $colsText . ')';
            return 0;
        }

        $battleId = (int) $this->db->lastInsertId();
        if ($battleId <= 0) {
            $battleId = $manualBattleId;
        }
        if ($battleId <= 0) {
            // Если в таблице нет AUTO_INCREMENT или драйвер не возвращает lastInsertId,
            // пытаемся найти свежесозданный бой по ключевым полям.
            try {
                $q = $this->db->prepare(
                    'SELECT id
                       FROM battles
                      WHERE user_1 = :user_1
                        AND poke_1 = :poke_1
                        AND poke_2 = :poke_2
                        AND batl_tip = "pve"
                      ORDER BY id DESC
                      LIMIT 1'
                );
                $q->execute([
                    'user_1' => $userId,
                    'poke_1' => 'pvp_' . $playerPokemonId,
                    'poke_2' => 'pve_' . $enemyPokemonId,
                ]);
                $battleId = (int) ($q->fetchColumn() ?: 0);
            } catch (\Throwable $e) {
                $cols = $this->battleColumnsDebug();
                $colsText = $cols ? (' cols=' . implode(',', $cols)) : '';
                $this->lastBattleCreateError = '(lastInsertId=0; select_id_failed: ' . $e->getMessage() . $colsText . ')';
                return 0;
            }
        }
        if ($battleId <= 0) {
            $cols = $this->battleColumnsDebug();
            $colsText = $cols ? (' cols=' . implode(',', $cols)) : '';
            $this->lastBattleCreateError = '(battleId=0 after insert; lastInsertId=0' . $colsText . ')';
            return 0;
        }

        // В legacy при создании боя добавляется базовая запись статусов (не всегда обязательна, но безопасно попытаться).
        try {
            $st = $this->db->prepare(
                'INSERT INTO statpokemonbatle (battleid, pokeid, accuracy, acc, tip)
                 VALUES (:battleid, :pokeid, :accuracy, :acc, :tip)'
            );
            $st->execute([
                'battleid' => $battleId,
                'pokeid' => 'pve_' . $enemyPokemonId,
                'accuracy' => 6,
                'acc' => 6,
                'tip' => 'plus',
            ]);
        } catch (\Throwable $e) {
            // Не блокируем бой, если таблицы/поля отличаются.
        }

        return $battleId;
    }

    private function nextBattleId(): int
    {
        try {
            $stmt = $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM battles');
            $nextId = (int) ($stmt ? $stmt->fetchColumn() : 0);
            return max(1, $nextId);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function nextPokPveId(): int
    {
        try {
            $stmt = $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM pok_pve');
            $nextId = (int) ($stmt ? $stmt->fetchColumn() : 0);
            return max(1, $nextId);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function battleColumnsDebug(): array
    {
        try {
            $stmt = $this->db->query('SHOW COLUMNS FROM battles');
            $rows = $stmt ? $stmt->fetchAll() : [];
            $cols = [];
            foreach ($rows as $row) {
                $name = (string) ($row['Field'] ?? '');
                if ($name !== '') {
                    $cols[] = $name;
                }
                if (count($cols) >= 30) {
                    break;
                }
            }
            return $cols;
        } catch (\Throwable) {
            return [];
        }
    }

    private function markUserInPveBattle(int $userId, int $battleId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET pve = 1, battleid = :battle WHERE id = :id LIMIT 1'
        );
        $stmt->execute([
            'battle' => $battleId,
            'id' => $userId,
        ]);
    }
}
