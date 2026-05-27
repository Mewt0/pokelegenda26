<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

trait AdminGmCenterRepositoryTrait
{
    public function gmCenter(): array
    {
        $now = time();
        $activeBattles = $this->gmActiveBattles(30);
        $stuckBattles = $this->gmStuckBattles(30);
        $recentErrors = $this->recentErrorLines(12);
        $marketModeration = $this->gmMarketModeration();
        $safeStorage = $this->gmSafeStorageSummary();
        $jobs = $this->gmBackgroundJobs();
        $migrations = $this->gmMigrationSummary();
        $replayTools = $this->gmReplayTools();
        $moderation = $this->gmModerationPanel();
        $qaSeedTools = $this->qaSeedTools();
        $bugReports = $this->gmBugReports();
        $logs = $this->gmSystemLogs();

        $cards = [
            $this->gmHealthCard('API/PHP errors', count($recentErrors), count($recentErrors) > 0 ? 'warn' : 'ok', 'Последние строки серверного error-log'),
            $this->gmHealthCard('Active battles', count($activeBattles), count($activeBattles) > 80 ? 'warn' : 'ok', 'Живые PvE/PvP rows'),
            $this->gmHealthCard('Stuck battles', count($stuckBattles), count($stuckBattles) > 0 ? 'warn' : 'ok', 'Старые незавершённые бои'),
            $this->gmHealthCard('Market risk', (int) $marketModeration['riskOpen'], (int) $marketModeration['riskOpen'] > 0 ? 'warn' : 'ok', 'Сделки на ручной review'),
            $this->gmHealthCard('Return storage', (int) $marketModeration['pendingReturns'], (int) $marketModeration['pendingReturns'] > 0 ? 'warn' : 'ok', 'Возвраты комиссионки'),
            $this->gmHealthCard('Safe storage', (int) $safeStorage['attentionTotal'], (int) $safeStorage['attentionTotal'] > 0 ? 'warn' : 'ok', 'Pending storage/open rollback'),
            $this->gmHealthCard('Background jobs', (int) $jobs['failed'], (int) $jobs['failed'] > 0 ? 'critical' : 'ok', 'Failed job runs'),
            $this->gmHealthCard('Migrations', (int) $migrations['attentionTotal'], (int) $migrations['attentionTotal'] > 0 ? 'critical' : 'ok', 'Pending/dirty/failed migrations'),
            $this->gmHealthCard('QA seed tools', (int) ($qaSeedTools['market']['activeQaLots'] ?? 0), 'ok', 'Tacos/NIGA/Система fixtures and local smokes'),
            $this->gmHealthCard('Bug reports', (int) $bugReports['open'], (int) $bugReports['criticalOpen'] > 0 ? 'critical' : ((int) $bugReports['open'] > 0 ? 'warn' : 'ok'), 'Игровые репорты со state/battle/log attachments'),
        ];

        return [
            'generatedAt' => $now,
            'health' => [
                'status' => $this->gmOverallStatus($cards),
                'cards' => $cards,
            ],
            'activeBattles' => $activeBattles,
            'stuckBattles' => $stuckBattles,
            'marketModeration' => $marketModeration,
            'replayTools' => $replayTools,
            'moderationPanel' => $moderation,
            'qaSeedTools' => $qaSeedTools,
            'bugReports' => $bugReports,
            'jobs' => $jobs,
            'migrations' => $migrations,
            'safeStorage' => $safeStorage,
            'logs' => $logs,
            'recentErrors' => $recentErrors,
        ];
    }

    private function gmHealthCard(string $label, int $value, string $status, string $details): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'status' => $status,
            'details' => $details,
        ];
    }

    private function gmOverallStatus(array $cards): string
    {
        foreach ($cards as $card) {
            if (($card['status'] ?? '') === 'critical') {
                return 'critical';
            }
        }
        foreach ($cards as $card) {
            if (($card['status'] ?? '') === 'warn') {
                return 'warn';
            }
        }
        return 'ok';
    }

    private function gmActiveBattles(int $limit): array
    {
        if (!$this->tableExists('battles')) {
            return [];
        }

        $now = time();
        $replaySelect = $this->tableExists('battle_replays')
            ? 'br.status AS replay_status, br.rounds AS replay_rounds, br.updated_at AS replay_updated_at'
            : '"" AS replay_status, 0 AS replay_rounds, 0 AS replay_updated_at';
        $replayJoin = $this->tableExists('battle_replays')
            ? 'LEFT JOIN battle_replays br ON br.battle_id = b.id'
            : '';
        $rows = $this->lookupRows(
            'SELECT b.id, b.user_1, b.user_2, b.batl_tip, b.raund, b.pobeda, b.time, b.times, b.hod_user_id,
                    u1.login AS user_1_login, u2.login AS user_2_login,
                    ' . $replaySelect . '
               FROM battles b
          LEFT JOIN users u1 ON u1.id = b.user_1
          LEFT JOIN users u2 ON u2.id = b.user_2
          ' . $replayJoin . '
              WHERE b.pobeda = 0
              ORDER BY b.time ASC, b.id DESC
              LIMIT ' . max(1, min(100, $limit))
        );

        return array_map(fn (array $row): array => $this->gmFormatBattleRow($row, $now), $rows);
    }

    private function gmStuckBattles(int $limit): array
    {
        if (!$this->tableExists('battles')) {
            return [];
        }

        $now = time();
        $pveSeconds = $this->gmSettingInt('background_jobs.stuck_pve_seconds', 86400);
        $pvpSeconds = $this->gmSettingInt('background_jobs.stuck_pvp_seconds', 14400);
        $replaySelect = $this->tableExists('battle_replays')
            ? 'br.status AS replay_status, br.rounds AS replay_rounds, br.updated_at AS replay_updated_at'
            : '"" AS replay_status, 0 AS replay_rounds, 0 AS replay_updated_at';
        $replayJoin = $this->tableExists('battle_replays')
            ? 'LEFT JOIN battle_replays br ON br.battle_id = b.id'
            : '';
        $rows = $this->lookupRowsPrepared(
            'SELECT b.id, b.user_1, b.user_2, b.batl_tip, b.raund, b.pobeda, b.time, b.times, b.hod_user_id,
                    u1.login AS user_1_login, u2.login AS user_2_login,
                    ' . $replaySelect . '
               FROM battles b
          LEFT JOIN users u1 ON u1.id = b.user_1
          LEFT JOIN users u2 ON u2.id = b.user_2
          ' . $replayJoin . '
              WHERE b.pobeda = 0
                AND b.time > 0
                AND (
                    (b.batl_tip = "pvp" AND b.time <= :pvp_cutoff)
                    OR (b.batl_tip <> "pvp" AND b.time <= :pve_cutoff)
                )
              ORDER BY b.time ASC, b.id DESC
              LIMIT ' . max(1, min(100, $limit)),
            [
                'pvp_cutoff' => $now - $pvpSeconds,
                'pve_cutoff' => $now - $pveSeconds,
            ]
        );

        return array_map(fn (array $row): array => $this->gmFormatBattleRow($row, $now, true), $rows);
    }

    private function gmFormatBattleRow(array $row, int $now, bool $forceStuck = false): array
    {
        $time = (int) ($row['time'] ?? 0);
        $type = (string) ($row['batl_tip'] ?? '');
        $age = $time > 0 ? max(0, $now - $time) : 0;
        $threshold = $type === 'pvp'
            ? $this->gmSettingInt('background_jobs.stuck_pvp_seconds', 14400)
            : $this->gmSettingInt('background_jobs.stuck_pve_seconds', 86400);

        return [
            'id' => (int) ($row['id'] ?? 0),
            'type' => $type,
            'round' => (int) ($row['raund'] ?? 0),
            'winner' => (int) ($row['pobeda'] ?? 0),
            'startedAt' => $time,
            'ageSeconds' => $age,
            'stuck' => $forceStuck || ($time > 0 && $age >= $threshold),
            'thresholdSeconds' => $threshold,
            'hodUserId' => (int) ($row['hod_user_id'] ?? 0),
            'user1' => [
                'id' => (int) ($row['user_1'] ?? 0),
                'login' => (string) ($row['user_1_login'] ?? ''),
            ],
            'user2' => [
                'id' => (int) ($row['user_2'] ?? 0),
                'login' => (string) ($row['user_2_login'] ?? ''),
            ],
            'replay' => [
                'status' => (string) ($row['replay_status'] ?? ''),
                'rounds' => (int) ($row['replay_rounds'] ?? 0),
                'updatedAt' => (int) ($row['replay_updated_at'] ?? 0),
                'available' => (string) ($row['replay_status'] ?? '') !== '',
            ],
            'suggestedAction' => $forceStuck ? 'open_replay_or_review_state' : 'watch',
        ];
    }

    private function gmMarketModeration(): array
    {
        $summary = [
            'activeLots' => 0,
            'expiredActiveLots' => 0,
            'lockedLots' => 0,
            'pendingReturns' => 0,
            'riskOpen' => 0,
            'riskApproved' => 0,
            'recentRiskLots' => [],
            'pendingReturnRows' => [],
        ];
        if (!$this->tableExists('market_lots')) {
            return $summary;
        }

        $summary['activeLots'] = $this->countTable('market_lots', 'status = "active" AND expires_at > UNIX_TIMESTAMP()');
        $summary['expiredActiveLots'] = $this->countTable('market_lots', 'status = "active" AND expires_at > 0 AND expires_at <= UNIX_TIMESTAMP()');
        $summary['lockedLots'] = $this->columnExists('market_lots', 'locked_at')
            ? $this->countTable('market_lots', 'status = "active" AND locked_at > 0')
            : 0;
        $summary['pendingReturns'] = $this->tableExists('market_return_storage')
            ? $this->countTable('market_return_storage', 'status = "pending"')
            : 0;
        $summary['riskOpen'] = $this->commissionRiskCount();
        $summary['riskApproved'] = $this->tableExists('market_deal_reviews')
            ? $this->countTable('market_deal_reviews', 'status = "approved"')
            : 0;
        $summary['recentRiskLots'] = $this->commissionLots('', 10, 0, ['risky' => '1', 'sort' => 'price_desc'])['rows'] ?? [];
        $summary['pendingReturnRows'] = $this->commissionReturns('', 10, 0, ['status' => 'pending'])['rows'] ?? [];

        return $summary;
    }

    private function gmReplayTools(): array
    {
        if (!$this->tableExists('battle_replays')) {
            return [
                'ready' => false,
                'active' => 0,
                'finished' => 0,
                'events' => 0,
                'lastUpdatedAt' => 0,
                'recent' => [],
            ];
        }

        return [
            'ready' => true,
            'active' => $this->countTable('battle_replays', 'status = "active"'),
            'finished' => $this->countTable('battle_replays', 'status IN ("finished", "completed")'),
            'events' => $this->tableExists('battle_replay_events') ? $this->countTable('battle_replay_events') : 0,
            'lastUpdatedAt' => (int) ($this->db->query('SELECT COALESCE(MAX(updated_at), 0) FROM battle_replays')->fetchColumn() ?: 0),
            'recent' => $this->lookupRows(
                'SELECT br.battle_id, br.battle_type, br.status, br.rounds, br.winner_id, br.updated_at,
                        u1.login AS user_1_login, u2.login AS user_2_login
                   FROM battle_replays br
              LEFT JOIN users u1 ON u1.id = br.user_1
              LEFT JOIN users u2 ON u2.id = br.user_2
                  ORDER BY br.updated_at DESC, br.battle_id DESC
                  LIMIT 10'
            ),
        ];
    }

    private function gmBugReports(): array
    {
        if (!$this->tableExists('bug_reports')) {
            return [
                'ready' => false,
                'open' => 0,
                'today' => 0,
                'criticalOpen' => 0,
                'withBattle' => 0,
                'lastCreatedAt' => 0,
                'recent' => [],
            ];
        }

        $todayStart = strtotime('today') ?: (time() - 86400);
        return [
            'ready' => true,
            'open' => $this->countTable('bug_reports', 'status IN ("open", "investigating")'),
            'today' => $this->countTable('bug_reports', 'created_at >= ' . (int) $todayStart),
            'criticalOpen' => $this->countTable('bug_reports', 'severity = "critical" AND status IN ("open", "investigating")'),
            'withBattle' => $this->countTable('bug_reports', 'battle_id > 0 AND status IN ("open", "investigating")'),
            'lastCreatedAt' => (int) ($this->db->query('SELECT COALESCE(MAX(created_at), 0) FROM bug_reports')->fetchColumn() ?: 0),
            'recent' => $this->lookupRows(
                'SELECT id, user_id, user_login, title, severity, status, route, battle_id, created_at
                   FROM bug_reports
                  ORDER BY created_at DESC, id DESC
                  LIMIT 10'
            ),
        ];
    }

    private function gmModerationPanel(): array
    {
        $now = time();
        $authorIdSelect = $this->columnExists('chats', 'author_id') ? 'author_id' : '0 AS author_id';
        $activePunishments = $this->tableExists('moderation_punishments')
            ? $this->countTable('moderation_punishments', 'active = 1 AND (expires_at = 0 OR expires_at > UNIX_TIMESTAMP())')
            : 0;

        return [
            'activePunishments' => $activePunishments,
            'activeBanIps' => $this->tableExists('banip') ? $this->countTable('banip') : 0,
            'recentPunishments' => $this->tableExists('moderation_punishments')
                ? $this->lookupRows(
                    'SELECT p.*, u.login AS moderator_login
                       FROM moderation_punishments p
                  LEFT JOIN users u ON u.id = p.moderator_user_id
                   ORDER BY p.id DESC
                      LIMIT 12'
                )
                : [],
            'recentChat' => $this->tableExists('chats')
                ? $this->lookupRows('SELECT id, author, ' . $authorIdSelect . ', room, time, text FROM chats ORDER BY id DESC LIMIT 12')
                : [],
            'generatedAt' => $now,
        ];
    }

    private function gmSafeStorageSummary(): array
    {
        $pending = $this->tableExists('safe_storage_entries')
            ? $this->countTable('safe_storage_entries', 'status = "pending"')
            : 0;
        $rollbacks = $this->tableExists('safe_operation_rollbacks')
            ? $this->countTable('safe_operation_rollbacks', 'status IN ("open", "failed")')
            : 0;

        return [
            'pendingStorage' => $pending,
            'openRollbacks' => $rollbacks,
            'attentionTotal' => $pending + $rollbacks,
            'recentStorage' => $this->tableExists('safe_storage_entries')
                ? $this->lookupRows(
                    'SELECT id, user_id, source_type, source_id, object_type, object_id, quantity, reason, status, created_at
                       FROM safe_storage_entries
                      ORDER BY id DESC
                      LIMIT 10'
                )
                : [],
            'recentRollbacks' => $this->tableExists('safe_operation_rollbacks')
                ? $this->lookupRows(
                    'SELECT id, operation_key, operation_type, user_id, source_type, source_id, status, error_message, created_at, updated_at
                       FROM safe_operation_rollbacks
                      ORDER BY id DESC
                      LIMIT 10'
                )
                : [],
        ];
    }

    private function gmBackgroundJobs(): array
    {
        if (!$this->tableExists('background_job_runs')) {
            return [
                'ready' => false,
                'failed' => 0,
                'running' => 0,
                'lastRunAt' => 0,
                'lastRuns' => [],
                'recentLogs' => [],
            ];
        }

        return [
            'ready' => true,
            'failed' => $this->countTable('background_job_runs', 'status = "failed"'),
            'running' => $this->countTable('background_job_runs', 'status = "running"'),
            'lastRunAt' => $this->backgroundJobLastRunAt(),
            'lastRuns' => $this->lookupRows(
                'SELECT id, job_name, status, dry_run, started_at, finished_at, duration_ms, error_message, summary_json
                   FROM background_job_runs
                  ORDER BY id DESC
                  LIMIT 12'
            ),
            'recentLogs' => $this->tableExists('background_job_logs')
                ? $this->lookupRows(
                    'SELECT id, run_id, job_name, action, severity, entity_type, entity_id, data_json, created_at
                       FROM background_job_logs
                      ORDER BY id DESC
                      LIMIT 16'
                )
                : [],
        ];
    }

    private function gmMigrationSummary(): array
    {
        if (!$this->tableExists('migration_status')) {
            return [
                'ready' => false,
                'total' => 0,
                'applied' => 0,
                'pending' => 0,
                'dirty' => 0,
                'failed' => 0,
                'attentionTotal' => 0,
                'checkedAt' => 0,
            ];
        }

        $row = $this->lookupRows('SELECT * FROM migration_status ORDER BY id DESC LIMIT 1')[0] ?? [];
        $pending = (int) ($row['pending_migrations'] ?? 0);
        $dirty = (int) ($row['dirty_migrations'] ?? 0);
        $failed = (int) ($row['failed_migrations'] ?? 0);
        return [
            'ready' => true,
            'total' => (int) ($row['total_migrations'] ?? 0),
            'applied' => (int) ($row['applied_migrations'] ?? 0),
            'pending' => $pending,
            'dirty' => $dirty,
            'failed' => $failed,
            'attentionTotal' => $pending + $dirty + $failed,
            'checkedAt' => (int) ($row['checked_at'] ?? 0),
            'snapshot' => $this->decodeJson((string) ($row['data_json'] ?? '')),
        ];
    }

    private function gmSystemLogs(): array
    {
        return [
            'adminAudit' => $this->auditRows(12),
            'market' => $this->tableExists('market_logs') ? ($this->commissionLogs('', 12, 0, ['period' => '30d'])['rows'] ?? []) : [],
            'background' => $this->tableExists('background_job_logs')
                ? $this->lookupRows(
                    'SELECT id, run_id, job_name, action, severity, entity_type, entity_id, data_json, created_at
                       FROM background_job_logs
                      ORDER BY id DESC
                      LIMIT 12'
                )
                : [],
            'economy' => $this->tableExists('economy_guard_alerts')
                ? $this->lookupRows(
                    'SELECT id, alert_type, severity, status, user_id, related_user_id, amount, score, title, last_seen_at
                       FROM economy_guard_alerts
                      ORDER BY last_seen_at DESC, id DESC
                      LIMIT 12'
                )
                : [],
            'integrity' => $this->tableExists('data_integrity_logs')
                ? $this->lookupRows(
                    'SELECT id, run_key, check_key, severity, status, count_found, count_fixed, created_at
                       FROM data_integrity_logs
                      ORDER BY id DESC
                      LIMIT 12'
                )
                : [],
            'mail' => $this->tableExists('mail_delivery_logs')
                ? $this->lookupRows(
                    'SELECT id, recipient, subject, transport, status, error_message, created_at, sent_at
                       FROM mail_delivery_logs
                      ORDER BY id DESC
                      LIMIT 12'
                )
                : [],
            'phpErrors' => $this->recentErrorLines(12),
        ];
    }

    private function gmSettingInt(string $name, int $default): int
    {
        $value = trim($this->setting($name, (string) $default));
        return ctype_digit($value) ? max(0, (int) $value) : $default;
    }

    private function decodeJson(string $json): array
    {
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
