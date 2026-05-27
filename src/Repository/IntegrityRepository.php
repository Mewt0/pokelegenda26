<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class IntegrityRepository
{
    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function run(bool $fixSafe = false, string $runKey = ''): array
    {
        $runKey = $runKey !== '' ? $runKey : ('integrity_' . date('Ymd_His'));
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
        $this->check($checks, $runKey, 'battle.active_unfinished', 'warn', 'unfinished battle rows; cleanup rules are reviewed in PvE/PvP phase', 'SELECT COUNT(*) FROM battles WHERE pobeda = 0', false);

        if ($this->tableExists('safe_storage_entries')) {
            $this->check($checks, $runKey, 'safe_storage.pending_entries', 'warn', 'pending objects in safe storage', 'SELECT COUNT(*) FROM safe_storage_entries WHERE status = "pending"', false);
        }
        if ($this->tableExists('safe_operation_rollbacks')) {
            $this->check($checks, $runKey, 'safe_storage.open_rollbacks', 'warn', 'open or failed rollback plans', 'SELECT COUNT(*) FROM safe_operation_rollbacks WHERE status IN ("open", "failed")', false);
        }

        $summary = ['p0' => 0, 'p1' => 0, 'warn' => 0, 'ok' => 0, 'fixed' => 0];
        foreach ($checks as $row) {
            $severity = (string) ($row['severity'] ?? 'warn');
            $status = (string) ($row['status'] ?? 'ok');
            if ($status === 'ok') {
                $summary['ok']++;
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
        $this->log($runKey, $key, $severity, $status, $count, $fixed, ['description' => $description]);
        $checks[] = [
            'key' => $key,
            'severity' => $severity,
            'status' => $status,
            'count' => $count,
            'fixed' => $fixed,
            'description' => $description,
        ];
    }

    private function countSql(string $sql): int
    {
        return (int) ($this->db->query($sql)->fetchColumn() ?: 0);
    }

    private function executeAffected(string $sql): int
    {
        return (int) $this->db->exec($sql);
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
