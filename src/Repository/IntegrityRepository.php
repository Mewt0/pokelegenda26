<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class IntegrityRepository
{
    /**
     * @var array<string,array<string,mixed>>
     */
    private array $acceptedWarnings = [];

    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function run(bool $fixSafe = false, string $runKey = ''): array
    {
        $runKey = $runKey !== '' ? $runKey : ('integrity_' . date('Ymd_His'));
        $this->acceptedWarnings = $this->loadAcceptedWarnings();
        $checks = [];

        $this->check($checks, $runKey, 'items.non_positive_count', 'warn', 'items_users rows with count <= 0', 'SELECT COUNT(*) FROM items_users WHERE count <= 0', $fixSafe, function (): int {
            return $this->executeAffected('DELETE FROM items_users WHERE count <= 0');
        });
        $this->check($checks, $runKey, 'items.orphan_user', 'warn', 'items_users rows owned by missing users', 'SELECT COUNT(*) FROM items_users iu LEFT JOIN users u ON u.id = iu.user_id WHERE iu.user_id > 0 AND u.id IS NULL', false);
        $this->check($checks, $runKey, 'items.invalid_item', 'warn', 'items_users rows with missing item definitions', 'SELECT COUNT(*) FROM items_users iu LEFT JOIN items i ON i.id = iu.item_id WHERE i.id IS NULL', false);

        if ($this->tableExists('items_poke')) {
            $this->check($checks, $runKey, 'held_items.orphan_pokemon', 'warn', 'held items attached to missing pokemon', 'SELECT COUNT(*) FROM items_poke ip LEFT JOIN pok_user p ON p.id = ip.id_poke WHERE p.id IS NULL', $fixSafe, function (): int {
                return $this->executeAffected('DELETE ip FROM items_poke ip LEFT JOIN pok_user p ON p.id = ip.id_poke WHERE p.id IS NULL');
            });
            $this->check($checks, $runKey, 'held_items.invalid_item', 'warn', 'held items with missing item definitions', 'SELECT COUNT(*) FROM items_poke ip LEFT JOIN items i ON i.id = ip.id_items WHERE i.id IS NULL', $fixSafe, function (): int {
                return $this->executeAffected('DELETE ip FROM items_poke ip LEFT JOIN items i ON i.id = ip.id_items WHERE i.id IS NULL');
            });
        }

        $reserveUser = $this->commissionReserveUserId();
        $this->check($checks, $runKey, 'pokemon.invalid_owner', 'warn', 'pokemon owned by missing users outside commission reserve', 'SELECT COUNT(*) FROM pok_user p LEFT JOIN users u ON u.id = p.users WHERE p.users > 0 AND p.users <> ' . $reserveUser . ' AND u.id IS NULL', false);
        $this->check($checks, $runKey, 'pokemon.permanent_primal_mega', 'p1', 'permanent Primal/Mega battle form ids in pok_user.basenum', 'SELECT COUNT(*) FROM pok_user WHERE basenum BETWEEN 5000 AND 5099', false);
        $this->check(
            $checks,
            $runKey,
            'pokemon.impossible_battle_stats',
            'p1',
            'player pokemon rows with impossible level or stats; these can create absurd PvE/PvP damage and broken Primal/Mega displays',
            'SELECT COUNT(*) FROM pok_user p JOIN users u ON u.id = p.users JOIN poke_base pb ON pb.id = p.basenum WHERE p.basenum > 0 AND (p.lvl < 1 OR p.lvl > 100 OR p.hp_max > 1000 OR p.atk > 1000 OR p.def > 1000 OR p.satk > 1000 OR p.sdef > 1000 OR p.speed > 1000)',
            $fixSafe,
            fn (): int => $this->repairImpossiblePokemonStats()
        );
        $this->check(
            $checks,
            $runKey,
            'pokemon.hp_overflow',
            'p1',
            'player pokemon rows where current HP is above max HP',
            'SELECT COUNT(*) FROM pok_user WHERE hp_my > hp_max AND hp_max > 0',
            $fixSafe,
            function (): int {
                return $this->executeAffected('UPDATE pok_user SET hp_my = hp_max WHERE hp_my > hp_max AND hp_max > 0');
            }
        );

        if ($this->tableExists('eggs')) {
            $this->check($checks, $runKey, 'eggs.invalid_owner', 'warn', 'eggs owned by missing users outside commission reserve', 'SELECT COUNT(*) FROM eggs e LEFT JOIN users u ON u.id = e.users_egg WHERE e.users_egg > 0 AND e.users_egg <> ' . $reserveUser . ' AND u.id IS NULL', false);
        }

        if ($this->tableExists('market_lots')) {
            $this->check($checks, $runKey, 'market.active_snapshot_missing', 'warn', 'active market lots without object snapshot', 'SELECT COUNT(*) FROM market_lots WHERE status = "active" AND (object_snapshot_json IS NULL OR object_snapshot_json = "" OR object_snapshot_json = "{}")', false);
            $this->check($checks, $runKey, 'market.active_pokemon_bad_reserve', 'p1', 'active pokemon lots whose pokemon is not in reserve owner', 'SELECT COUNT(*) FROM market_lots ml JOIN pok_user p ON p.id = ml.object_id WHERE ml.status = "active" AND ml.object_type = "pokemon" AND p.users <> ' . $reserveUser, false);
            if ($this->tableExists('eggs')) {
                $this->check($checks, $runKey, 'market.active_egg_bad_reserve', 'p1', 'active egg lots whose egg is not in reserve owner', 'SELECT COUNT(*) FROM market_lots ml JOIN eggs e ON e.id_egg = ml.object_id WHERE ml.status = "active" AND ml.object_type = "egg" AND e.users_egg <> ' . $reserveUser, false);
            }
            $this->check($checks, $runKey, 'market.sold_without_buyer', 'warn', 'sold lots without buyer_id', 'SELECT COUNT(*) FROM market_lots WHERE status = "sold" AND buyer_id <= 0', false);
        }

        if ($this->tableExists('battle_transformations')) {
            $this->check($checks, $runKey, 'battle.finished_active_transform', 'warn', 'active battle transformations on finished battles', 'SELECT COUNT(*) FROM battle_transformations bt JOIN battles b ON b.id = bt.battle_id WHERE bt.active = 1 AND b.pobeda <> 0', $fixSafe, function (): int {
                return $this->executeAffected('UPDATE battle_transformations bt JOIN battles b ON b.id = bt.battle_id SET bt.active = 0, bt.reverted_at = UNIX_TIMESTAMP(), bt.updated_at = UNIX_TIMESTAMP() WHERE bt.active = 1 AND b.pobeda <> 0');
            });
        }
        if ($this->tableExists('pvp_requests')) {
            $this->check($checks, $runKey, 'pvp.expired_pending_requests', 'warn', 'pending PvP requests past expires_at', 'SELECT COUNT(*) FROM pvp_requests WHERE status = "pending" AND expires_at > 0 AND expires_at <= UNIX_TIMESTAMP()', $fixSafe, function (): int {
                return $this->executeAffected('UPDATE pvp_requests SET status = "expired", responded_at = UNIX_TIMESTAMP(), updated_at = UNIX_TIMESTAMP() WHERE status = "pending" AND expires_at > 0 AND expires_at <= UNIX_TIMESTAMP()');
            });
        }
        $this->check($checks, $runKey, 'battle.zero_id_rows', 'warn', 'legacy battle rows with id <= 0 cannot be safely addressed by APIs', 'SELECT COUNT(*) FROM battles WHERE id <= 0', $fixSafe, function (): int {
            $this->executeAffected('DELETE FROM statpokemonbatle WHERE battleid <= 0');
            $this->executeAffected('DELETE FROM battle_dop WHERE battleid <= 0');
            $this->executeAffected('DELETE FROM battle_log WHERE battle_id <= 0');
            $this->executeAffected('DELETE FROM bttle_status WHERE buttleid <= 0');
            if ($this->tableExists('battle_transformations')) {
                $this->executeAffected('DELETE FROM battle_transformations WHERE battle_id <= 0');
            }
            return $this->executeAffected('DELETE FROM battles WHERE id <= 0');
        });
        $this->check(
            $checks,
            $runKey,
            'battle.duplicate_positive_ids',
            'p1',
            'positive battle ids used by more than one row; this can route PvE actions into PvP rows',
            'SELECT COUNT(*) FROM battles b WHERE b.id > 0 AND EXISTS (SELECT 1 FROM battles b2 WHERE b2.id = b.id AND (b2.user_1 <> b.user_1 OR b2.user_2 <> b.user_2 OR b2.batl_tip <> b.batl_tip OR b2.poke_1 <> b.poke_1 OR b2.poke_2 <> b.poke_2) LIMIT 1)',
            false
        );
        $this->check(
            $checks,
            $runKey,
            'users.stale_active_battle',
            'warn',
            'users marked as in battle but without a matching active battle row',
            'SELECT COUNT(*) FROM users u LEFT JOIN battles b ON b.id = u.battleid AND ((u.pve = 1 AND b.batl_tip = "pve" AND b.pobeda = 0 AND b.user_1 = u.id) OR (u.pvp = 1 AND b.batl_tip = "pvp" AND b.pobeda = 0 AND (b.user_1 = u.id OR b.user_2 = u.id))) WHERE (u.pve = 1 OR u.pvp = 1) AND b.id IS NULL',
            $fixSafe,
            function (): int {
                return $this->executeAffected(
                    'UPDATE users u
                       LEFT JOIN battles b ON b.id = u.battleid
                        AND ((u.pve = 1 AND b.batl_tip = "pve" AND b.pobeda = 0 AND b.user_1 = u.id)
                          OR (u.pvp = 1 AND b.batl_tip = "pvp" AND b.pobeda = 0 AND (b.user_1 = u.id OR b.user_2 = u.id)))
                        SET u.pve = 0, u.pvp = 0, u.battleid = 0
                      WHERE (u.pve = 1 OR u.pvp = 1)
                        AND b.id IS NULL'
                );
            }
        );
        $this->check($checks, $runKey, 'battle.active_unfinished', 'warn', 'unfinished battle rows; cleanup rules are reviewed in PvE/PvP phase', 'SELECT COUNT(*) FROM battles WHERE pobeda = 0', false);

        if ($this->tableExists('safe_storage_entries')) {
            $this->check($checks, $runKey, 'safe_storage.pending_entries', 'warn', 'pending objects in safe storage', 'SELECT COUNT(*) FROM safe_storage_entries WHERE status = "pending"', false);
        }
        if ($this->tableExists('safe_operation_rollbacks')) {
            $this->check($checks, $runKey, 'safe_storage.open_rollbacks', 'warn', 'open or failed rollback plans', 'SELECT COUNT(*) FROM safe_operation_rollbacks WHERE status IN ("open", "failed")', false);
        }

        $summary = ['p0' => 0, 'p1' => 0, 'warn' => 0, 'accepted' => 0, 'ok' => 0, 'fixed' => 0];
        foreach ($checks as $row) {
            $severity = (string) ($row['severity'] ?? 'warn');
            $status = (string) ($row['status'] ?? 'ok');
            if ($status === 'ok') {
                $summary['ok']++;
            } elseif ($status === 'accepted') {
                $summary['accepted']++;
            } elseif (isset($summary[$severity])) {
                $summary[$severity]++;
            } else {
                $summary['warn']++;
            }
            $summary['fixed'] += (int) ($row['fixed'] ?? 0);
        }

        $this->writeSetting('integrity.last_run_at', (string) time());

        return [
            'runKey' => $runKey,
            'fixSafe' => $fixSafe,
            'summary' => $summary,
            'checks' => $checks,
        ];
    }

    /**
     * @param list<array<string,mixed>> $checks
     */
    private function check(array &$checks, string $runKey, string $key, string $severity, string $description, string $sql, bool $fixSafe, ?callable $fixer = null): void
    {
        if (!$this->queryLooksRunnable($sql)) {
            return;
        }

        $count = $this->countSql($sql);
        $fixed = 0;
        if ($count > 0 && $fixSafe && $fixer !== null) {
            try {
                $fixed = max(0, (int) $fixer());
                $count = $this->countSql($sql);
            } catch (Throwable $e) {
                $this->log($runKey, $key, $severity, 'error', $count, $fixed, [
                    'description' => $description,
                    'error' => $e->getMessage(),
                ]);
                $checks[] = [
                    'key' => $key,
                    'severity' => $severity,
                    'status' => 'error',
                    'count' => $count,
                    'fixed' => $fixed,
                    'description' => $description,
                    'error' => $e->getMessage(),
                ];
                return;
            }
        }

        $status = $count > 0 ? 'found' : 'ok';
        $details = ['description' => $description];
        $row = [
            'key' => $key,
            'severity' => $severity,
            'status' => $status,
            'count' => $count,
            'fixed' => $fixed,
            'description' => $description,
        ];
        if ($status === 'found' && $severity === 'warn') {
            $acceptance = $this->acceptedWarnings[$key] ?? null;
            if (is_array($acceptance)) {
                $status = 'accepted';
                $details['accepted'] = true;
                $details['acceptance'] = $acceptance;
                $row['status'] = $status;
                $row['accepted'] = true;
                $row['acceptance'] = $acceptance;
            }
        }
        $this->log($runKey, $key, $severity, $status, $count, $fixed, $details);
        $checks[] = $row;
    }

    private function countSql(string $sql): int
    {
        return (int) ($this->db->query($sql)->fetchColumn() ?: 0);
    }

    private function executeAffected(string $sql): int
    {
        return (int) $this->db->exec($sql);
    }

    private function repairImpossiblePokemonStats(): int
    {
        $stmt = $this->db->query(
            'SELECT p.id, p.lvl, p.hp_my, p.hp_max, p.har,
                    p.hp_iv, p.atk_iv, p.def_iv, p.satk_iv, p.sdef_iv, p.speed_iv,
                    p.hp_ev, p.atk_ev, p.def_ev, p.satk_ev, p.sdef_ev, p.speed_ev,
                    pb.hp AS base_hp, pb.atk AS base_atk, pb.def AS base_def,
                    pb.satk AS base_satk, pb.sdef AS base_sdef, pb.speed AS base_speed,
                    h.atk AS nature_atk, h.def AS nature_def, h.satk AS nature_satk,
                    h.sdef AS nature_sdef, h.speed AS nature_speed
               FROM pok_user p
               JOIN users u ON u.id = p.users
               JOIN poke_base pb ON pb.id = p.basenum
          LEFT JOIN har h ON h.id_har = p.har
              WHERE p.basenum > 0
                AND (p.lvl < 1 OR p.lvl > 100 OR p.hp_max > 1000 OR p.atk > 1000
                  OR p.def > 1000 OR p.satk > 1000 OR p.sdef > 1000 OR p.speed > 1000)'
        );

        $update = $this->db->prepare(
            'UPDATE pok_user
                SET lvl = :lvl,
                    hp_my = :hp_my, hp_max = :hp_max,
                    atk = :atk, def = :def, satk = :satk, sdef = :sdef, speed = :speed,
                    hp_iv = :hp_iv, atk_iv = :atk_iv, def_iv = :def_iv,
                    satk_iv = :satk_iv, sdef_iv = :sdef_iv, speed_iv = :speed_iv,
                    hp_ev = :hp_ev, atk_ev = :atk_ev, def_ev = :def_ev,
                    satk_ev = :satk_ev, sdef_ev = :sdef_ev, speed_ev = :speed_ev,
                    evcount = :evcount
              WHERE id = :id
              LIMIT 1'
        );

        $fixed = 0;
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $level = max(1, min(100, (int) ($row['lvl'] ?? 1)));
            $iv = [
                'hp' => max(0, min(31, (int) ($row['hp_iv'] ?? 1))),
                'atk' => max(0, min(31, (int) ($row['atk_iv'] ?? 1))),
                'def' => max(0, min(31, (int) ($row['def_iv'] ?? 1))),
                'satk' => max(0, min(31, (int) ($row['satk_iv'] ?? 1))),
                'sdef' => max(0, min(31, (int) ($row['sdef_iv'] ?? 1))),
                'speed' => max(0, min(31, (int) ($row['speed_iv'] ?? 1))),
            ];
            $ev = [
                'hp' => max(0, min(252, (int) ($row['hp_ev'] ?? 0))),
                'atk' => max(0, min(252, (int) ($row['atk_ev'] ?? 0))),
                'def' => max(0, min(252, (int) ($row['def_ev'] ?? 0))),
                'satk' => max(0, min(252, (int) ($row['satk_ev'] ?? 0))),
                'sdef' => max(0, min(252, (int) ($row['sdef_ev'] ?? 0))),
                'speed' => max(0, min(252, (int) ($row['speed_ev'] ?? 0))),
            ];
            $calcStat = static function (int $base, int $ivValue, int $evValue, float $nature, int $lvl): int {
                return max(1, (int) round(((($ivValue + $base * 2 + (int) floor($evValue / 4)) * $lvl / 100) + 5) * max(0.1, $nature)));
            };
            $hpMax = max(1, (int) round((($iv['hp'] + (int) ($row['base_hp'] ?? 1) * 2 + (int) floor($ev['hp'] / 4) + 100) * $level / 100) + 10));
            $oldHpMax = max(1, (int) ($row['hp_max'] ?? 1));
            $ratio = max(0.0, min(1.0, (int) ($row['hp_my'] ?? $oldHpMax) / $oldHpMax));
            $hpMy = max(0, min($hpMax, (int) round($hpMax * $ratio)));

            $update->execute([
                'id' => (int) $row['id'],
                'lvl' => $level,
                'hp_my' => $hpMy,
                'hp_max' => $hpMax,
                'atk' => $calcStat((int) ($row['base_atk'] ?? 1), $iv['atk'], $ev['atk'], (float) ($row['nature_atk'] ?? 1), $level),
                'def' => $calcStat((int) ($row['base_def'] ?? 1), $iv['def'], $ev['def'], (float) ($row['nature_def'] ?? 1), $level),
                'satk' => $calcStat((int) ($row['base_satk'] ?? 1), $iv['satk'], $ev['satk'], (float) ($row['nature_satk'] ?? 1), $level),
                'sdef' => $calcStat((int) ($row['base_sdef'] ?? 1), $iv['sdef'], $ev['sdef'], (float) ($row['nature_sdef'] ?? 1), $level),
                'speed' => $calcStat((int) ($row['base_speed'] ?? 1), $iv['speed'], $ev['speed'], (float) ($row['nature_speed'] ?? 1), $level),
                'hp_iv' => $iv['hp'],
                'atk_iv' => $iv['atk'],
                'def_iv' => $iv['def'],
                'satk_iv' => $iv['satk'],
                'sdef_iv' => $iv['sdef'],
                'speed_iv' => $iv['speed'],
                'hp_ev' => $ev['hp'],
                'atk_ev' => $ev['atk'],
                'def_ev' => $ev['def'],
                'satk_ev' => $ev['satk'],
                'sdef_ev' => $ev['sdef'],
                'speed_ev' => $ev['speed'],
                'evcount' => array_sum($ev),
            ]);
            $fixed += $update->rowCount();
        }

        return $fixed;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    private function loadAcceptedWarnings(): array
    {
        if (!$this->tableExists('data_integrity_acceptances')) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT check_key, severity, status, reason, cleanup_policy, accepted_until, accepted_by, updated_at
                   FROM data_integrity_acceptances
                  WHERE severity = "warn"
                    AND status IN ("accepted", "accepted_for_beta")
                    AND (accepted_until = 0 OR accepted_until >= UNIX_TIMESTAMP())'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }

        $accepted = [];
        foreach ($rows as $row) {
            $key = (string) ($row['check_key'] ?? '');
            if ($key === '') {
                continue;
            }
            $accepted[$key] = [
                'status' => (string) ($row['status'] ?? 'accepted'),
                'reason' => (string) ($row['reason'] ?? ''),
                'cleanup_policy' => (string) ($row['cleanup_policy'] ?? ''),
                'accepted_until' => (int) ($row['accepted_until'] ?? 0),
                'accepted_by' => (string) ($row['accepted_by'] ?? ''),
                'updated_at' => (int) ($row['updated_at'] ?? 0),
            ];
        }

        return $accepted;
    }

    /**
     * @param array<string,mixed> $details
     */
    private function log(string $runKey, string $checkKey, string $severity, string $status, int $count, int $fixed, array $details): void
    {
        if (!$this->tableExists('data_integrity_logs')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO data_integrity_logs (run_key, check_key, severity, status, count_found, count_fixed, details_json, created_at)
             VALUES (:run_key, :check_key, :severity, :status, :count_found, :count_fixed, :details, :created)'
        )->execute([
            'run_key' => mb_substr($runKey, 0, 64),
            'check_key' => mb_substr($checkKey, 0, 80),
            'severity' => mb_substr($severity, 0, 16),
            'status' => mb_substr($status, 0, 16),
            'count_found' => $count,
            'count_fixed' => $fixed,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
            'created' => time(),
        ]);
    }

    private function commissionReserveUserId(): int
    {
        $value = $this->setting('commission.reserve_user_id', '3');
        return is_numeric($value) ? max(0, (int) $value) : 3;
    }

    private function setting(string $name, string $default): string
    {
        if (!$this->tableExists('site_settings')) {
            return $default;
        }
        $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $value = $stmt->fetchColumn();
        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function writeSetting(string $name, string $value): void
    {
        if (!$this->tableExists('site_settings')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO site_settings (name, value, updated_by, updated_at)
             VALUES (:name, :value, 0, :time)
             ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
        )->execute([
            'name' => $name,
            'value' => $value,
            'time' => time(),
        ]);
    }

    private function queryLooksRunnable(string $sql): bool
    {
        if (preg_match('/\bFROM\s+([a-zA-Z0-9_]+)/i', $sql, $matches) !== 1) {
            return false;
        }
        return $this->tableExists($matches[1]);
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table LIMIT 1');
            $stmt->execute(['table' => $table]);
            $cache[$table] = (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            $cache[$table] = false;
        }
        return $cache[$table];
    }
}
