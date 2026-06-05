<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use Throwable;

trait AdminQaSeedRepositoryTrait
{
    /** @return array<string,mixed> */
    public function qaSeedTools(): array
    {
        $accounts = [];
        foreach ($this->qaSeedLogins() as $login) {
            $accounts[] = $this->qaSeedAccountState($login);
        }

        return [
            'accounts' => $accounts,
            'activeTeam' => $this->qaSeedTeamCounts(true),
            'storedPokemon' => $this->qaSeedTeamCounts(false),
            'itemStacks' => $this->qaSeedItemStackCounts(),
            'market' => $this->qaSeedMarketState(),
            'lastActions' => $this->qaSeedLastActions(),
        ];
    }

    /** @param array<string,mixed> $payload */
    public function qaSeedRun(int $adminId, array $payload): array
    {
        $action = trim((string) ($payload['action'] ?? ''));
        return match ($action) {
            'setup_accounts' => $this->qaSeedSetupAccounts($adminId),
            'give_teams' => $this->qaSeedGiveTeams($adminId),
            'give_items' => $this->qaSeedGiveItems($adminId),
            'reset_market' => $this->qaSeedResetMarket($adminId),
            'run_smokes' => $this->qaSeedRunSmokes($adminId),
            default => ['ok' => false, 'message' => 'Неизвестное QA seed действие.'],
        };
    }

    /** @return array<int,string> */
    private function qaSeedLogins(): array
    {
        return ['Tacos', 'NIGA', 'Система'];
    }

    /** @return array<string,mixed> */
    private function qaSeedSetupAccounts(int $adminId): array
    {
        $created = [];
        $updated = [];
        $tacos = $this->qaSeedEnsureAccount('Tacos', true);
        $niga = $this->qaSeedEnsureAccount('NIGA', false);
        $system = $this->qaSeedEnsureAccount('Система', false, true);

        foreach ([$tacos, $niga, $system] as $row) {
            if (($row['created'] ?? false) === true) {
                $created[] = $row['login'];
            } else {
                $updated[] = $row['login'];
            }
        }

        $this->audit($adminId, 'qa_seed.setup_accounts', 'users', 0, [
            'created' => $created,
            'updated' => $updated,
            'accounts' => [$tacos, $niga, $system],
        ]);

        return [
            'ok' => true,
            'message' => sprintf('QA аккаунты готовы: создано %d, обновлено %d.', count($created), count($updated)),
            'summary' => ['created' => $created, 'updated' => $updated],
            'qaSeedTools' => $this->qaSeedTools(),
        ];
    }

    /** @return array<string,mixed> */
    private function qaSeedGiveTeams(int $adminId): array
    {
        $script = $this->qaSeedRunCliScript('prepare_qa_teams.php', ['--apply'], 120);
        $this->audit($adminId, 'qa_seed.give_teams', 'pok_user', 0, $script);

        return [
            'ok' => $script['ok'],
            'message' => $script['ok'] ? 'QA команды Tacos/NIGA подготовлены.' : 'Подготовка QA-команд завершилась с ошибкой.',
            'script' => $script,
            'qaSeedTools' => $this->qaSeedTools(),
        ];
    }

    /** @return array<string,mixed> */
    private function qaSeedGiveItems(int $adminId): array
    {
        $users = $this->qaSeedUserIds();
        if ($users === []) {
            return ['ok' => false, 'message' => 'Сначала подготовь QA аккаунты.'];
        }

        $itemIds = $this->qaSeedItemIds();
        $granted = [];
        $missing = [];
        foreach ($users as $login => $userId) {
            $granted[$login] = 0;
            foreach ($itemIds as $itemId => $count) {
                if (!$this->itemExists((int) $itemId)) {
                    $missing[(int) $itemId] = true;
                    continue;
                }
                $this->addItemToUser($userId, (int) $itemId, (int) $count);
                $granted[$login]++;
            }
        }

        $this->audit($adminId, 'qa_seed.give_items', 'items_users', 0, [
            'users' => $users,
            'granted_kinds' => $granted,
            'missing_item_ids' => array_keys($missing),
        ]);

        return [
            'ok' => true,
            'message' => sprintf('QA предметы выданы. Видов: %d, отсутствует в БД: %d.', count($itemIds), count($missing)),
            'summary' => ['granted' => $granted, 'missing' => array_keys($missing)],
            'qaSeedTools' => $this->qaSeedTools(),
        ];
    }

    /** @return array<string,mixed> */
    private function qaSeedResetMarket(int $adminId): array
    {
        if (!$this->tableExists('market_lots')) {
            return ['ok' => false, 'message' => 'Таблица market_lots не найдена.'];
        }

        $users = $this->qaSeedUserIds();
        if ($users === []) {
            return ['ok' => false, 'message' => 'QA аккаунты не найдены.'];
        }
        $ids = array_values($users);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $reserveUser = $this->qaSeedCommissionReserveUserId();
        $cancelled = 0;
        $returned = 0;
        $pendingReturns = 0;
        $errors = [];

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT *
                   FROM market_lots
                  WHERE status = "active"
                    AND seller_id IN (' . $placeholders . ')
                  ORDER BY id ASC
                  FOR UPDATE'
            );
            $stmt->execute($ids);
            $lots = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            foreach ($lots as $lot) {
                $lotId = (int) ($lot['id'] ?? 0);
                $sellerId = (int) ($lot['seller_id'] ?? 0);
                $type = (string) ($lot['object_type'] ?? '');
                $objectId = (int) ($lot['object_id'] ?? 0);
                $quantity = max(1, (int) ($lot['quantity'] ?? 1));
                try {
                    if ($type === 'item') {
                        $this->addItemToUser($sellerId, $objectId, $quantity);
                        $returned++;
                    } elseif ($type === 'pokemon') {
                        $active = $this->qaSeedActivePokemonCount($sellerId) < 6 ? 1 : 0;
                        $update = $this->db->prepare('UPDATE pok_user SET users = :seller, active = :active, startepoke = 0 WHERE id = :pokemon AND users = :reserve LIMIT 1');
                        $update->execute(['seller' => $sellerId, 'active' => $active, 'pokemon' => $objectId, 'reserve' => $reserveUser]);
                        if ($update->rowCount() !== 1) {
                            throw new \RuntimeException('reserved pokemon not found');
                        }
                        $returned++;
                    } elseif ($type === 'egg' && $this->tableExists('eggs')) {
                        $update = $this->db->prepare('UPDATE eggs SET users_egg = :seller WHERE id_egg = :egg AND users_egg = :reserve LIMIT 1');
                        $update->execute(['seller' => $sellerId, 'egg' => $objectId, 'reserve' => $reserveUser]);
                        if ($update->rowCount() !== 1) {
                            throw new \RuntimeException('reserved egg not found');
                        }
                        $returned++;
                    }
                } catch (Throwable $e) {
                    $pendingReturns++;
                    $errors[] = '#' . $lotId . ': ' . $e->getMessage();
                    $this->qaSeedStoreMarketReturn($sellerId, $lot, 'qa_reset', $e->getMessage());
                }

                $set = 'status = "cancelled"';
                foreach (['locked_by' => '0', 'locked_at' => '0', 'lock_reason' => '""', 'lock_token' => '""'] as $column => $value) {
                    if ($this->columnExists('market_lots', $column)) {
                        $set .= ', ' . $column . ' = ' . $value;
                    }
                }
                $this->db->prepare('UPDATE market_lots SET ' . $set . ' WHERE id = :id LIMIT 1')
                    ->execute(['id' => $lotId]);
                if ($this->tableExists('market_reserved_objects')) {
                    $this->db->prepare('DELETE FROM market_reserved_objects WHERE lot_id = :lot LIMIT 1')->execute(['lot' => $lotId]);
                }
                $this->qaSeedMarketLog('qa.reset.cancel', $adminId, $lotId, [
                    'seller_id' => $sellerId,
                    'object_type' => $type,
                    'object_id' => $objectId,
                    'quantity' => $quantity,
                    'pending_return' => $pendingReturns > 0,
                ]);
                $cancelled++;
            }
            $this->audit($adminId, 'qa_seed.reset_market', 'market_lots', 0, [
                'seller_ids' => $ids,
                'cancelled' => $cancelled,
                'returned' => $returned,
                'pending_returns' => $pendingReturns,
                'errors' => $errors,
            ]);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Market reset failed: ' . $e->getMessage()];
        }

        return [
            'ok' => true,
            'message' => sprintf('QA market reset: снято %d, возвращено %d, pending returns %d.', $cancelled, $returned, $pendingReturns),
            'summary' => ['cancelled' => $cancelled, 'returned' => $returned, 'pending_returns' => $pendingReturns, 'errors' => $errors],
            'qaSeedTools' => $this->qaSeedTools(),
        ];
    }

    /** @return array<string,mixed> */
    private function qaSeedRunSmokes(int $adminId): array
    {
        $scripts = [
            ['db_integrity_smoke.php', ['--json'], 60],
            ['background_jobs_smoke.php', [], 90],
            ['inventory_held_items_smoke.php', [], 90],
            ['commission_hardening_smoke.php', ['--iterations=5'], 120],
        ];

        $runs = [];
        $ok = true;
        foreach ($scripts as [$script, $args, $timeout]) {
            $run = $this->qaSeedRunCliScript($script, $args, $timeout);
            $runs[] = $run;
            $ok = $ok && (bool) ($run['ok'] ?? false);
        }
        $this->audit($adminId, 'qa_seed.run_smokes', 'tools', 0, ['runs' => $runs]);

        return [
            'ok' => $ok,
            'message' => $ok ? 'QA smokes прошли.' : 'Часть QA smokes упала, смотри вывод.',
            'runs' => $runs,
            'qaSeedTools' => $this->qaSeedTools(),
        ];
    }

    /** @return array<string,mixed> */
    private function qaSeedEnsureAccount(string $login, bool $adminPanel = false, bool $system = false): array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        $created = false;
        if (!$row) {
            $created = true;
            $password = $system ? '!system-account-no-login!' : password_hash('jungheinrick', PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                'INSERT INTO users
                    (login, password, email, email_verified_at, online, onlinetime, activation, `groups`,
                     moderation, police, datereg, avatars, clanid, clan_point, clan_adm,
                     pve, pvp, trade, info, gender, ip, rang, rang_a, rang_b, rang_c,
                     karma_score, count_poke, count_poke_s, buildmy, mychat, newuser, battleid,
                     pve_button, atack_poke, status_klan, youtuber, prefics, timepoke)
                 VALUES
                    (:login, :password, :email, 0, 0, :now, 1, :groups,
                     0, 0, :date, 1, 0, 0, 0,
                     0, 0, 0, :info, 1, 0, :rank, 0, 0, 0,
                     0, 0, 0, 1, 1, :newuser, 0,
                     0, 0, "", 0, 0, 0)'
            );
            $stmt->execute([
                'login' => $login,
                'password' => $password,
                'email' => $system ? 'system@pokemonchic.local' : mb_strtolower($login) . '@qa.pokemonchic.local',
                'now' => time(),
                'groups' => $adminPanel || $system ? 1 : 6,
                'date' => date('Y-m-d H:i:s'),
                'info' => $system ? 'Служебный аккаунт для системных и QA-операций.' : 'QA test account.',
                'rank' => 'Новичок',
                'newuser' => time() + 86400,
            ]);
            $row = $this->rowById('users', 'id', (int) $this->db->lastInsertId());
        }

        $userId = (int) ($row['id'] ?? 0);
        if ($userId > 0) {
            $this->ensureInformationUser($userId);
            $this->db->prepare(
                'UPDATE users
                    SET activation = 1,
                        online = 0,
                        pve = 0,
                        pvp = 0,
                        trade = 0,
                        battleid = 0,
                        `groups` = CASE WHEN :admin_panel = 1 OR :system = 1 THEN 1 ELSE `groups` END,
                        info = CASE WHEN :system_info = 1 THEN "Служебный аккаунт для системных и QA-операций." ELSE info END
                  WHERE id = :id
                  LIMIT 1'
            )->execute([
                'admin_panel' => $adminPanel ? 1 : 0,
                'system' => $system ? 1 : 0,
                'system_info' => $system ? 1 : 0,
                'id' => $userId,
            ]);
            $this->db->prepare('UPDATE information_users SET admins_panels = :admin WHERE users_id = :user')
                ->execute(['admin' => $adminPanel ? 1 : 0, 'user' => $userId]);
            if ($system) {
                $this->qaSeedSaveSetting('system.account_id', (string) $userId);
                $this->qaSeedSaveSetting('system.account_login', $login);
            }
        }

        return ['login' => $login, 'id' => $userId, 'created' => $created, 'admin' => $adminPanel, 'system' => $system];
    }

    /** @return array<string,int> */
    private function qaSeedUserIds(): array
    {
        $ids = [];
        foreach ($this->qaSeedLogins() as $login) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
            $stmt->execute(['login' => $login]);
            $id = (int) ($stmt->fetchColumn() ?: 0);
            if ($id > 0) {
                $ids[$login] = $id;
            }
        }
        return $ids;
    }

    /** @return array<int,int> */
    private function qaSeedItemIds(): array
    {
        $ids = [
            78, 80, 81, 82, 83, 85, 86, 87, 88, 89, 90, 91, 92, 93, 147, 152, 153, 154,
            181, 189, 210, 217, 219, 234, 235, 320, 321, 322, 323, 325, 326, 327, 328,
            330, 332, 333, 337, 338, 339, 342, 344, 349, 350, 356, 358, 360, 361, 365,
            366, 370, 371, 372, 373, 374, 375, 376, 377, 378, 380, 386, 489, 491, 492,
            496, 517, 520, 90020, 90021, 90022, 90023, 90024, 90200, 90201, 90203,
            90204, 90215, 90216, 90244,
        ];
        $result = [1 => 5_000_000, 2 => 500];
        foreach ($ids as $id) {
            $result[$id] = 2;
        }
        return $result;
    }

    /** @return array<string,mixed> */
    private function qaSeedAccountState(string $login): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.login, u.activation, u.groups, COALESCE(iu.admins_panels, 0) AS admins_panels
               FROM users u
          LEFT JOIN information_users iu ON iu.users_id = u.id
              WHERE LOWER(u.login) = LOWER(:login)
              LIMIT 1'
        );
        $stmt->execute(['login' => $login]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return [
            'login' => $login,
            'id' => (int) ($row['id'] ?? 0),
            'exists' => (int) ($row['id'] ?? 0) > 0,
            'active' => (int) ($row['activation'] ?? 0) === 1,
            'group' => (int) ($row['groups'] ?? 0),
            'adminPanel' => (int) ($row['admins_panels'] ?? 0) === 1,
        ];
    }

    /** @return array<string,int> */
    private function qaSeedTeamCounts(bool $active): array
    {
        $counts = [];
        foreach ($this->qaSeedUserIds() as $login => $id) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = :active');
            $stmt->execute(['user' => $id, 'active' => $active ? 1 : 0]);
            $counts[$login] = (int) ($stmt->fetchColumn() ?: 0);
        }
        return $counts;
    }

    /** @return array<string,int> */
    private function qaSeedItemStackCounts(): array
    {
        $counts = [];
        foreach ($this->qaSeedUserIds() as $login => $id) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM items_users WHERE user_id = :user AND count > 0');
            $stmt->execute(['user' => $id]);
            $counts[$login] = (int) ($stmt->fetchColumn() ?: 0);
        }
        return $counts;
    }

    /** @return array<string,mixed> */
    private function qaSeedMarketState(): array
    {
        if (!$this->tableExists('market_lots')) {
            return ['activeQaLots' => 0, 'soldQaLots' => 0, 'cancelledQaLots' => 0];
        }
        $ids = array_values($this->qaSeedUserIds());
        if ($ids === []) {
            return ['activeQaLots' => 0, 'soldQaLots' => 0, 'cancelledQaLots' => 0];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $count = function (string $status) use ($ids, $placeholders): int {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM market_lots WHERE seller_id IN (' . $placeholders . ') AND status = ?');
            $stmt->execute(array_merge($ids, [$status]));
            return (int) ($stmt->fetchColumn() ?: 0);
        };
        return [
            'activeQaLots' => $count('active'),
            'soldQaLots' => $count('sold'),
            'cancelledQaLots' => $count('cancelled'),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function qaSeedLastActions(): array
    {
        if (!$this->tableExists('admin_audit_log')) {
            return [];
        }
        return $this->lookupRows(
            'SELECT a.id, a.action, a.entity, a.entity_id, a.payload, a.created_at, u.login AS admin_login
               FROM admin_audit_log a
          LEFT JOIN users u ON u.id = a.admin_id
              WHERE a.action LIKE "qa_seed.%"
              ORDER BY a.id DESC
              LIMIT 8'
        );
    }

    /** @return array<string,mixed> */
    private function qaSeedRunCliScript(string $script, array $args = [], int $timeoutSeconds = 90): array
    {
        $appRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2);
        if (!preg_match('/^[a-z0-9_]+\.php$/i', $script)) {
            return ['ok' => false, 'script' => $script, 'exitCode' => 2, 'output' => 'Script is not whitelisted by name.'];
        }
        $path = $appRoot . '/tools/' . $script;
        if (!is_file($path)) {
            return ['ok' => false, 'script' => $script, 'exitCode' => 2, 'output' => 'Script not found.'];
        }

        $phpBinary = PHP_BINARY;
        $candidate = rtrim(PHP_BINDIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'php.exe' : 'php');
        if (is_file($candidate)) {
            $phpBinary = $candidate;
        }
        $cmd = array_merge([$phpBinary, $path], array_map('strval', $args));
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($cmd, $descriptor, $pipes, $appRoot);
        if (!is_resource($process)) {
            return ['ok' => false, 'script' => $script, 'exitCode' => 2, 'output' => 'Cannot start process.'];
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $output = '';
        $error = '';
        $started = time();
        $timedOut = false;
        while (true) {
            $output .= stream_get_contents($pipes[1]) ?: '';
            $error .= stream_get_contents($pipes[2]) ?: '';
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            if (time() - $started > $timeoutSeconds) {
                $timedOut = true;
                proc_terminate($process);
                break;
            }
            usleep(100000);
        }
        $output .= stream_get_contents($pipes[1]) ?: '';
        $error .= stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($timedOut) {
            $exitCode = 124;
            $error .= "\nTimed out after " . $timeoutSeconds . "s.";
        }

        $text = trim($output . ($error !== '' ? "\nSTDERR:\n" . $error : ''));
        if (mb_strlen($text) > 6000) {
            $text = mb_substr($text, 0, 6000) . "\n... truncated ...";
        }

        return [
            'ok' => $exitCode === 0,
            'script' => $script,
            'args' => $args,
            'exitCode' => $exitCode,
            'output' => $text,
        ];
    }

    private function qaSeedSaveSetting(string $name, string $value): void
    {
        $this->db->prepare(
            'INSERT INTO site_settings (name, value, updated_by, updated_at)
             VALUES (:name, :value, 0, :time)
             ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)'
        )->execute(['name' => $name, 'value' => $value, 'time' => time()]);
    }

    private function qaSeedCommissionReserveUserId(): int
    {
        $value = $this->setting('commission.reserve_user_id', '3');
        return ctype_digit($value) ? max(1, (int) $value) : 3;
    }

    private function qaSeedActivePokemonCount(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pok_user WHERE users = :user AND active = 1');
        $stmt->execute(['user' => $userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function qaSeedMarketLog(string $action, int $actorId, int $lotId, array $data): void
    {
        if (!$this->tableExists('market_logs')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO market_logs (action, actor_id, lot_id, data_json, created_at)
             VALUES (:action, :actor, :lot, :data, :created)'
        )->execute([
            'action' => $action,
            'actor' => $actorId,
            'lot' => $lotId,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created' => time(),
        ]);
    }

    private function qaSeedStoreMarketReturn(int $sellerId, array $lot, string $reason, string $error): void
    {
        if (!$this->tableExists('market_return_storage')) {
            return;
        }
        $this->db->prepare(
            'INSERT INTO market_return_storage (user_id, lot_id, object_type, object_id, quantity, payload_json, status, created_at, resolved_at)
             VALUES (:user, :lot, :type, :object, :quantity, :payload, "pending", :created, 0)'
        )->execute([
            'user' => $sellerId,
            'lot' => (int) ($lot['id'] ?? 0),
            'type' => (string) ($lot['object_type'] ?? ''),
            'object' => (int) ($lot['object_id'] ?? 0),
            'quantity' => max(1, (int) ($lot['quantity'] ?? 1)),
            'payload' => json_encode(['reason' => $reason, 'error' => $error, 'lot' => $lot], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created' => time(),
        ]);
    }
}
