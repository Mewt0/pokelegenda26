<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class BackgroundJobRepository
{
    private const JOBS = [
        'expire_market' => 'Истечение лотов Комиссионной лавки',
        'pvp_timeouts' => 'Истечение PvP-заявок',
        'stuck_battles' => 'Поиск зависших боёв',
        'temporary_items' => 'Очистка временных предметов и бустов',
        'transport_flights' => 'Контроль авиарейсов',
        'event_cleanup' => 'Очистка завершённых событий',
        'safe_storage_status' => 'Контроль Safe Storage',
        'economy_guard' => 'Economy Guard: экономика и трансферы',
    ];

    public function __construct(
        private PDO $db,
        private ?CommissionMarketRepository $commission = null,
        private ?EconomyGuardRepository $economyGuard = null,
    ) {
    }

    /**
     * @return array<string,string>
     */
    public function jobs(): array
    {
        return self::JOBS;
    }

    /**
     * @return array<string,mixed>
     */
    public function status(): array
    {
        $jobs = [];
        foreach (self::JOBS as $name => $label) {
            $jobs[$name] = [
                'label' => $label,
                'pending' => $this->pendingCount($name),
                'lastRun' => $this->lastRun($name),
            ];
        }

        return [
            'serverTime' => time(),
            'enabled' => $this->setting('background_jobs.enabled', '1') !== '0',
            'jobs' => $jobs,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function runAll(bool $dryRun = false, int $limit = 0): array
    {
        $rows = [];
        foreach (array_keys(self::JOBS) as $job) {
            $rows[$job] = $this->runJob($job, $dryRun, $limit);
        }

        return [
            'ok' => !array_filter($rows, static fn (array $row): bool => ($row['status'] ?? '') === 'failed'),
            'dryRun' => $dryRun,
            'jobs' => $rows,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function runJob(string $jobName, bool $dryRun = false, int $limit = 0): array
    {
        if (!isset(self::JOBS[$jobName])) {
            return ['ok' => false, 'status' => 'failed', 'message' => 'Unknown job: ' . $jobName];
        }
        if ($this->setting('background_jobs.enabled', '1') === '0') {
            return ['ok' => true, 'status' => 'skipped', 'message' => 'Background jobs are disabled.'];
        }

        $lockName = 'pokemon8_background_job_' . $jobName;
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 0)');
        $lock->execute(['name' => $lockName]);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            return ['ok' => true, 'status' => 'skipped', 'message' => 'Job is already running.'];
        }

        $startedAt = time();
        $startedMs = (int) floor(microtime(true) * 1000);
        $runId = $this->createRun($jobName, $dryRun, $startedAt);
        try {
            $summary = match ($jobName) {
                'expire_market' => $this->jobExpireMarket($dryRun, $this->limitFor('background_jobs.market_expire_limit', $limit), $runId),
                'pvp_timeouts' => $this->jobPvpTimeouts($dryRun, $this->limitFor('background_jobs.pvp_timeout_limit', $limit), $runId),
                'stuck_battles' => $this->jobStuckBattles($dryRun, $limit, $runId),
                'temporary_items' => $this->jobTemporaryItems($dryRun, $this->limitFor('background_jobs.temporary_items_limit', $limit), $runId),
                'transport_flights' => $this->jobTransportFlights($dryRun, $limit, $runId),
                'event_cleanup' => $this->jobEventCleanup($dryRun, $this->limitFor('background_jobs.event_cleanup_limit', $limit), $runId),
                'safe_storage_status' => $this->jobSafeStorageStatus($dryRun, $limit, $runId),
                'economy_guard' => $this->jobEconomyGuard($dryRun, $this->limitFor('background_jobs.economy_guard_limit', $limit), $runId),
                default => ['message' => 'Unknown job'],
            };
            $durationMs = max(0, (int) floor(microtime(true) * 1000) - $startedMs);
            $this->finishRun($runId, $jobName, 'success', $summary, '', $durationMs);
            return ['ok' => true, 'status' => 'success', 'runId' => $runId, 'summary' => $summary];
        } catch (Throwable $e) {
            $durationMs = max(0, (int) floor(microtime(true) * 1000) - $startedMs);
            $summary = ['message' => $e->getMessage()];
            $this->finishRun($runId, $jobName, 'failed', $summary, $e->getMessage(), $durationMs);
            return ['ok' => false, 'status' => 'failed', 'runId' => $runId, 'message' => $e->getMessage()];
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $lockName]);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function jobExpireMarket(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('market_lots')) {
            return ['skipped' => true, 'reason' => 'market_lots missing'];
        }
        if ($this->commission === null) {
            $this->log($runId, 'expire_market', 'market.expire.skipped', 'warn', 'market', '', ['reason' => 'commission repository missing']);
            return ['skipped' => true, 'reason' => 'commission repository missing'];
        }
        $result = $this->commission->expireDueLotsJob($limit, $dryRun);
        $this->log($runId, 'expire_market', $dryRun ? 'market.expire.dry_run' : 'market.expire.run', 'info', 'market', '', $result);
        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    private function jobPvpTimeouts(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('pvp_requests')) {
            return ['skipped' => true, 'reason' => 'pvp_requests missing'];
        }
        $now = time();
        $stmt = $this->db->prepare('SELECT id FROM pvp_requests WHERE status = "pending" AND expires_at > 0 AND expires_at <= :now ORDER BY expires_at ASC LIMIT ' . $limit);
        $stmt->execute(['now' => $now]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        if ($dryRun || $ids === []) {
            return ['due' => count($ids), 'expired' => 0, 'dryRun' => $dryRun];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $update = $this->db->prepare('UPDATE pvp_requests SET status = "expired", responded_at = ?, updated_at = ? WHERE status = "pending" AND id IN (' . $placeholders . ')');
        $update->execute(array_merge([$now, $now], $ids));
        $expired = $update->rowCount();
        $this->log($runId, 'pvp_timeouts', 'pvp.timeout.expired', 'info', 'pvp_request', '', ['ids' => $ids, 'expired' => $expired]);
        return ['due' => count($ids), 'expired' => $expired, 'dryRun' => false];
    }

    /**
     * @return array<string,mixed>
     */
    private function jobStuckBattles(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('battles')) {
            return ['skipped' => true, 'reason' => 'battles missing'];
        }
        $now = time();
        $pveSeconds = $this->settingInt('background_jobs.stuck_pve_seconds', 86400);
        $pvpSeconds = $this->settingInt('background_jobs.stuck_pvp_seconds', 14400);
        $battleStamp = 'COALESCE(NULLIF(times, 0), NULLIF(time, 0), 0)';
        $pve = $this->countSql('SELECT COUNT(*) FROM battles WHERE pobeda = 0 AND batl_tip = "pve" AND ' . $battleStamp . ' > 0 AND ' . $battleStamp . ' <= :cutoff', ['cutoff' => $now - $pveSeconds]);
        $pvp = $this->countSql('SELECT COUNT(*) FROM battles WHERE pobeda = 0 AND batl_tip = "pvp" AND ' . $battleStamp . ' > 0 AND ' . $battleStamp . ' <= :cutoff', ['cutoff' => $now - $pvpSeconds]);
        $this->log($runId, 'stuck_battles', 'battle.stuck.scan', ($pve + $pvp) > 0 ? 'warn' : 'info', 'battle', '', [
            'pve' => $pve,
            'pvp' => $pvp,
            'dryRun' => $dryRun,
            'note' => 'warning-only until manual PvE/PvP regression confirms safe auto-finish rules',
        ]);
        return ['pveStuck' => $pve, 'pvpStuck' => $pvp, 'mutated' => 0, 'dryRun' => $dryRun];
    }

    /**
     * @return array<string,mixed>
     */
    private function jobTemporaryItems(bool $dryRun, int $limit, int $runId): array
    {
        $now = time();
        $expiredInventory = $this->tableExists('items_users')
            ? $this->countSql('SELECT COUNT(*) FROM items_users WHERE dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) <= :now', ['now' => $now])
            : 0;
        $expiredHeld = $this->tableExists('items_poke') && $this->columnExists('items_poke', 'datetime')
            ? $this->countSql('SELECT COUNT(*) FROM items_poke WHERE datetime REGEXP "^[0-9]+$" AND CAST(datetime AS UNSIGNED) <= :now', ['now' => $now])
            : 0;
        $expiredBoosts = $this->tableExists('player_boosts')
            ? $this->countSql('SELECT COUNT(*) FROM player_boosts WHERE active = 1 AND expires_at > 0 AND expires_at <= :now', ['now' => $now])
            : 0;

        if ($dryRun) {
            return ['inventory' => $expiredInventory, 'held' => $expiredHeld, 'boosts' => $expiredBoosts, 'dryRun' => true];
        }

        $deletedInventory = 0;
        if ($expiredInventory > 0) {
            $stmt = $this->db->prepare('DELETE FROM items_users WHERE dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) <= :now ORDER BY id ASC LIMIT ' . $limit);
            $stmt->execute(['now' => $now]);
            $deletedInventory = $stmt->rowCount();
        }

        $deletedHeld = 0;
        if ($expiredHeld > 0) {
            $stmt = $this->db->prepare('DELETE FROM items_poke WHERE datetime REGEXP "^[0-9]+$" AND CAST(datetime AS UNSIGNED) <= :now LIMIT ' . $limit);
            $stmt->execute(['now' => $now]);
            $deletedHeld = $stmt->rowCount();
        }

        $disabledBoosts = 0;
        if ($expiredBoosts > 0) {
            $stmt = $this->db->prepare('UPDATE player_boosts SET active = 0 WHERE active = 1 AND expires_at > 0 AND expires_at <= :now LIMIT ' . $limit);
            $stmt->execute(['now' => $now]);
            $disabledBoosts = $stmt->rowCount();
        }

        $summary = [
            'inventoryDue' => $expiredInventory,
            'heldDue' => $expiredHeld,
            'boostsDue' => $expiredBoosts,
            'inventoryDeleted' => $deletedInventory,
            'heldDeleted' => $deletedHeld,
            'boostsDisabled' => $disabledBoosts,
            'dryRun' => false,
        ];
        $this->log($runId, 'temporary_items', 'temporary.cleanup', 'info', 'inventory', '', $summary);
        return $summary;
    }

    /**
     * @return array<string,mixed>
     */
    private function jobTransportFlights(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('transport_flights')) {
            return ['skipped' => true, 'reason' => 'transport_flights missing'];
        }
        $now = time();
        $arrived = $this->countSql('SELECT COUNT(*) FROM transport_flights WHERE status = "active" AND arrives_at > 0 AND arrives_at <= :now', ['now' => $now]);
        $stale = $this->countSql('SELECT COUNT(*) FROM transport_flights WHERE status = "active" AND started_at > 0 AND started_at <= :cutoff', ['cutoff' => $now - 86400]);
        $summary = ['arrivedWaitingExit' => $arrived, 'staleActive' => $stale, 'mutated' => 0, 'dryRun' => $dryRun];
        $this->log($runId, 'transport_flights', 'transport.flight.scan', $stale > 0 ? 'warn' : 'info', 'transport_flight', '', $summary);
        return $summary;
    }

    /**
     * @return array<string,mixed>
     */
    private function jobEventCleanup(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return ['skipped' => true, 'reason' => 'game_event_boosts missing'];
        }
        $now = time();
        $stmt = $this->db->prepare('SELECT id FROM game_event_boosts WHERE enabled = 1 AND ends_at > 0 AND ends_at < :now ORDER BY ends_at ASC LIMIT ' . $limit);
        $stmt->execute(['now' => $now]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
        if ($dryRun || $ids === []) {
            return ['ended' => count($ids), 'disabled' => 0, 'dryRun' => $dryRun];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $update = $this->db->prepare('UPDATE game_event_boosts SET enabled = 0, updated_at = ? WHERE id IN (' . $placeholders . ')');
        $update->execute(array_merge([$now], $ids));
        $disabled = $update->rowCount();
        $this->log($runId, 'event_cleanup', 'event.cleanup.disabled', 'info', 'game_event', '', ['ids' => $ids, 'disabled' => $disabled]);
        return ['ended' => count($ids), 'disabled' => $disabled, 'dryRun' => false];
    }

    /**
     * @return array<string,mixed>
     */
    private function jobSafeStorageStatus(bool $dryRun, int $limit, int $runId): array
    {
        $pending = $this->tableExists('safe_storage_entries')
            ? $this->countSql('SELECT COUNT(*) FROM safe_storage_entries WHERE status = "pending"')
            : 0;
        $rollbacks = $this->tableExists('safe_operation_rollbacks')
            ? $this->countSql('SELECT COUNT(*) FROM safe_operation_rollbacks WHERE status IN ("open", "failed")')
            : 0;
        $summary = ['pendingStorage' => $pending, 'openRollbacks' => $rollbacks, 'mutated' => 0, 'dryRun' => $dryRun];
        $this->log($runId, 'safe_storage_status', 'safe_storage.scan', ($pending + $rollbacks) > 0 ? 'warn' : 'info', 'safe_storage', '', $summary);
        return $summary;
    }

    /**
     * @return array<string,mixed>
     */
    private function jobEconomyGuard(bool $dryRun, int $limit, int $runId): array
    {
        if (!$this->tableExists('economy_guard_alerts')) {
            return ['skipped' => true, 'reason' => 'economy_guard_alerts missing'];
        }
        $guard = $this->economyGuard ?? new EconomyGuardRepository($this->db);
        $summary = $guard->scan($dryRun, $limit);
        $alertCount = (int) (($summary['created'] ?? 0) + ($summary['updated'] ?? 0));
        $this->log($runId, 'economy_guard', 'economy.guard.scan', $alertCount > 0 ? 'warn' : 'info', 'economy_guard', '', $summary);
        return $summary;
    }

    private function pendingCount(string $jobName): int
    {
        $now = time();
        return match ($jobName) {
            'expire_market' => $this->tableExists('market_lots') ? $this->countSql('SELECT COUNT(*) FROM market_lots WHERE status = "active" AND expires_at <= :now', ['now' => $now]) : 0,
            'pvp_timeouts' => $this->tableExists('pvp_requests') ? $this->countSql('SELECT COUNT(*) FROM pvp_requests WHERE status = "pending" AND expires_at > 0 AND expires_at <= :now', ['now' => $now]) : 0,
            'stuck_battles' => $this->tableExists('battles') ? $this->countSql('SELECT COUNT(*) FROM battles WHERE pobeda = 0 AND COALESCE(NULLIF(times, 0), NULLIF(time, 0), 0) > 0 AND COALESCE(NULLIF(times, 0), NULLIF(time, 0), 0) <= :cutoff', ['cutoff' => $now - 14400]) : 0,
            'temporary_items' => $this->tableExists('items_users') ? $this->countSql('SELECT COUNT(*) FROM items_users WHERE dattimer REGEXP "^[0-9]+$" AND CAST(dattimer AS UNSIGNED) <= :now', ['now' => $now]) : 0,
            'transport_flights' => $this->tableExists('transport_flights') ? $this->countSql('SELECT COUNT(*) FROM transport_flights WHERE status = "active" AND arrives_at > 0 AND arrives_at <= :now', ['now' => $now]) : 0,
            'event_cleanup' => $this->tableExists('game_event_boosts') ? $this->countSql('SELECT COUNT(*) FROM game_event_boosts WHERE enabled = 1 AND ends_at > 0 AND ends_at < :now', ['now' => $now]) : 0,
            'safe_storage_status' => $this->tableExists('safe_storage_entries') ? $this->countSql('SELECT COUNT(*) FROM safe_storage_entries WHERE status = "pending"') : 0,
            'economy_guard' => $this->tableExists('economy_guard_alerts') ? $this->countSql('SELECT COUNT(*) FROM economy_guard_alerts WHERE status = "open"') : 0,
            default => 0,
        };
    }

    private function createRun(string $jobName, bool $dryRun, int $startedAt): int
    {
        if (!$this->tableExists('background_job_runs')) {
            return 0;
        }
        $this->db->prepare(
            'INSERT INTO background_job_runs (job_name, status, dry_run, started_at, finished_at, duration_ms, summary_json, error_message)
             VALUES (:job, "running", :dry, :started, 0, 0, "{}", "")'
        )->execute([
            'job' => $jobName,
            'dry' => $dryRun ? 1 : 0,
            'started' => $startedAt,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * @param array<string,mixed> $summary
     */
    private function finishRun(int $runId, string $jobName, string $status, array $summary, string $error, int $durationMs): void
    {
        if ($runId <= 0 || !$this->tableExists('background_job_runs')) {
            return;
        }
        $this->db->prepare(
            'UPDATE background_job_runs
                SET status = :status, finished_at = :finished, duration_ms = :duration, summary_json = :summary, error_message = :error
              WHERE id = :id AND job_name = :job
              LIMIT 1'
        )->execute([
            'status' => $status,
            'finished' => time(),
            'duration' => $durationMs,
            'summary' => $this->json($summary),
            'error' => mb_substr($error, 0, 255),
            'id' => $runId,
            'job' => $jobName,
        ]);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function log(int $runId, string $jobName, string $action, string $severity, string $entityType, string|int $entityId, array $data): void
    {
        if (!$this->tableExists('background_job_logs')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO background_job_logs (run_id, job_name, action, severity, entity_type, entity_id, data_json, created_at)
             VALUES (:run, :job, :action, :severity, :entity_type, :entity_id, :data, :time)'
        )->execute([
            'run' => max(0, $runId),
            'job' => mb_substr($jobName, 0, 64),
            'action' => mb_substr($action, 0, 64),
            'severity' => mb_substr($severity, 0, 16),
            'entity_type' => mb_substr($entityType, 0, 32),
            'entity_id' => mb_substr((string) $entityId, 0, 64),
            'data' => $this->json($data),
            'time' => time(),
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function lastRun(string $jobName): ?array
    {
        if (!$this->tableExists('background_job_runs')) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT id, status, dry_run, started_at, finished_at, duration_ms, summary_json, error_message
               FROM background_job_runs
              WHERE job_name = :job
              ORDER BY id DESC
              LIMIT 1'
        );
        $stmt->execute(['job' => $jobName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            return null;
        }
        return [
            'id' => (int) $row['id'],
            'status' => (string) $row['status'],
            'dryRun' => (int) $row['dry_run'] === 1,
            'startedAt' => (int) $row['started_at'],
            'finishedAt' => (int) $row['finished_at'],
            'durationMs' => (int) $row['duration_ms'],
            'summary' => $this->decodeJson((string) ($row['summary_json'] ?? '{}')),
            'error' => (string) ($row['error_message'] ?? ''),
        ];
    }

    /**
     * @param array<string,mixed> $params
     */
    private function countSql(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function limitFor(string $setting, int $override): int
    {
        if ($override > 0) {
            return max(1, min(500, $override));
        }
        return max(1, min(500, $this->settingInt($setting, 100)));
    }

    private function settingInt(string $name, int $default): int
    {
        $value = $this->setting($name, (string) $default);
        return is_numeric($value) ? (int) $value : $default;
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

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        try {
            $stmt = $this->db->prepare(
                'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column LIMIT 1'
            );
            $stmt->execute(['table' => $table, 'column' => $column]);
            $cache[$key] = (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }

    /**
     * @param array<string,mixed> $data
     */
    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJson(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
