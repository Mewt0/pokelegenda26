<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class EconomyGuardRepository
{
    private const COIN_ITEM_ID = 1;

    public function __construct(private PDO $db)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function scan(bool $dryRun = false, int $limit = 200): array
    {
        if (!$this->enabled()) {
            return ['enabled' => false, 'created' => 0, 'updated' => 0, 'dryRun' => $dryRun];
        }

        $limit = max(10, min(1000, $limit));
        $settings = $this->settings();
        $cutoff = time() - (int) $settings['scan_window_seconds'];
        $summary = [
            'enabled' => true,
            'dryRun' => $dryRun,
            'created' => 0,
            'updated' => 0,
            'checks' => [],
        ];

        foreach ([
            'suspicious_trades' => $this->scanSuspiciousTrades($limit),
            'massive_market_gain' => $this->scanMassiveMarketGain($cutoff, (int) $settings['massive_money_gain_threshold'], $limit),
            'massive_reward_gain' => $this->scanMassiveRewardGain($cutoff, (int) $settings['massive_money_gain_threshold'], $limit),
            'coin_balance' => $this->scanCoinBalances((int) $settings['coin_balance_threshold'], $limit),
            'transfer_abuse' => $this->scanTransferAbuse($cutoff, (int) $settings['transfer_pair_threshold'], (int) $settings['transfer_pair_count_threshold'], $limit),
            'fake_market_prices' => $this->scanFakeMarketPrices($settings, $limit),
        ] as $check => $alerts) {
            $summary['checks'][$check] = count($alerts);
            if ($dryRun) {
                continue;
            }
            foreach ($alerts as $alert) {
                $result = $this->upsertAlert($alert);
                $summary[$result ? 'created' : 'updated']++;
            }
        }

        return $summary;
    }

    /**
     * @return array<string,mixed>
     */
    public function alerts(array $filters = [], int $limit = 80, int $offset = 0): array
    {
        if (!$this->tableExists('economy_guard_alerts')) {
            return ['total' => 0, 'rows' => []];
        }
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'a.status = :status';
            $params['status'] = (string) $filters['status'];
        }
        if (!empty($filters['type'])) {
            $where[] = 'a.alert_type = :type';
            $params['type'] = (string) $filters['type'];
        }
        if (!empty($filters['severity'])) {
            $where[] = 'a.severity = :severity';
            $params['severity'] = (string) $filters['severity'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = '(a.user_id = :user OR a.related_user_id = :user)';
            $params['user'] = (int) $filters['user_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(a.title LIKE :q OR a.entity_id LIKE :q OR a.alert_key LIKE :q)';
            $params['q'] = '%' . (string) $filters['q'] . '%';
        }
        $whereSql = implode(' AND ', $where);
        $count = $this->prepareCount('SELECT COUNT(*) FROM economy_guard_alerts a WHERE ' . $whereSql, $params);
        $stmt = $this->db->prepare(
            'SELECT a.*, u.login AS user_login, ru.login AS related_user_login
               FROM economy_guard_alerts a
          LEFT JOIN users u ON u.id = a.user_id
          LEFT JOIN users ru ON ru.id = a.related_user_id
              WHERE ' . $whereSql . '
           ORDER BY FIELD(a.severity, "critical", "warn", "info"), a.last_seen_at DESC, a.id DESC
              LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();
        $rows = array_map(fn (array $row): array => $this->formatAlert($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        return ['total' => $count, 'rows' => $rows];
    }

    /**
     * @return array<string,mixed>
     */
    public function review(int $adminId, int $alertId, string $status, string $note = ''): array
    {
        if (!$this->tableExists('economy_guard_alerts')) {
            return ['ok' => false, 'message' => 'Economy Guard alerts table is missing.'];
        }
        if (!in_array($status, ['reviewed', 'ignored', 'open'], true)) {
            return ['ok' => false, 'message' => 'Некорректный статус ревью.'];
        }
        $stmt = $this->db->prepare(
            'UPDATE economy_guard_alerts
                SET status = :status, reviewed_by = :admin, reviewed_at = :time, note = :note
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute([
            'status' => $status,
            'admin' => $adminId,
            'time' => time(),
            'note' => mb_substr($note, 0, 255),
            'id' => $alertId,
        ]);

        return ['ok' => $stmt->rowCount() === 1, 'message' => $stmt->rowCount() === 1 ? 'Алерт обновлён.' : 'Алерт не найден.'];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function scanSuspiciousTrades(int $limit): array
    {
        if (!$this->tableExists('market_deal_reviews') || !$this->tableExists('market_lots')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT l.*, r.risk_score, r.risk_flags_json, r.updated_at AS risk_updated_at
               FROM market_deal_reviews r
               JOIN market_lots l ON l.id = r.lot_id
              WHERE r.status = "flagged"
           ORDER BY r.updated_at DESC, r.id DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $lotId = (int) $row['id'];
            $score = max(50, (int) ($row['risk_score'] ?? 0));
            $alerts[] = $this->alert(
                'suspicious_trade:lot:' . $lotId,
                'suspicious_trade',
                $score >= 80 ? 'critical' : 'warn',
                (int) ($row['seller_id'] ?? 0),
                (int) ($row['buyer_id'] ?? 0),
                'market_lot',
                (string) $lotId,
                (int) ($row['total_price'] ?? 0),
                $score,
                'Подозрительная сделка в Комиссионной лавке',
                ['lot' => $row, 'risk_flags' => $this->decode((string) ($row['risk_flags_json'] ?? '[]'))],
                (int) ($row['risk_updated_at'] ?? time())
            );
        }
        return $alerts;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function scanMassiveMarketGain(int $cutoff, int $threshold, int $limit): array
    {
        if (!$this->tableExists('market_lots')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT seller_id, COUNT(*) AS sales, SUM(total_price - commission_amount) AS income, MAX(sold_at) AS last_at
               FROM market_lots
              WHERE status = "sold"
                AND sold_at >= :cutoff
           GROUP BY seller_id
             HAVING income >= :threshold
           ORDER BY income DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $userId = (int) ($row['seller_id'] ?? 0);
            $amount = (int) ($row['income'] ?? 0);
            $alerts[] = $this->alert(
                'massive_money_gain:market:' . $userId . ':' . $cutoff,
                'massive_money_gain',
                $amount >= $threshold * 5 ? 'critical' : 'warn',
                $userId,
                0,
                'user',
                (string) $userId,
                $amount,
                min(100, (int) floor($amount / max(1, $threshold) * 30)),
                'Крупный доход монетами через рынок',
                ['source' => 'market_sales', 'sales' => (int) $row['sales'], 'window_cutoff' => $cutoff],
                (int) ($row['last_at'] ?? time())
            );
        }
        return $alerts;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function scanMassiveRewardGain(int $cutoff, int $threshold, int $limit): array
    {
        if (!$this->tableExists('reward_transactions') || !$this->tableExists('reward_transaction_entries')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT rt.user_id, COUNT(*) AS entries, SUM(e.quantity) AS amount, MAX(e.created_at) AS last_at
               FROM reward_transaction_entries e
               JOIN reward_transactions rt ON rt.id = e.transaction_id
              WHERE rt.status = "completed"
                AND e.status = "completed"
                AND e.reward_type = "item"
                AND e.object_id = :coin
                AND e.created_at >= :cutoff
           GROUP BY rt.user_id
             HAVING amount >= :threshold
           ORDER BY amount DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':coin', self::COIN_ITEM_ID, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            $amount = (int) ($row['amount'] ?? 0);
            $alerts[] = $this->alert(
                'massive_money_gain:rewards:' . $userId . ':' . $cutoff,
                'massive_money_gain',
                $amount >= $threshold * 5 ? 'critical' : 'warn',
                $userId,
                0,
                'reward_transaction',
                (string) $userId,
                $amount,
                min(100, (int) floor($amount / max(1, $threshold) * 30)),
                'Крупное начисление монет через reward pipeline',
                ['source' => 'reward_pipeline', 'entries' => (int) $row['entries'], 'window_cutoff' => $cutoff],
                (int) ($row['last_at'] ?? time())
            );
        }
        return $alerts;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function scanCoinBalances(int $threshold, int $limit): array
    {
        if (!$this->tableExists('items_users')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT user_id, SUM(count) AS balance
               FROM items_users
              WHERE item_id = :coin
                AND count > 0
           GROUP BY user_id
             HAVING balance >= :threshold
           ORDER BY balance DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':coin', self::COIN_ITEM_ID, PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $userId = (int) ($row['user_id'] ?? 0);
            $balance = (int) ($row['balance'] ?? 0);
            $alerts[] = $this->alert(
                'coin_balance:user:' . $userId,
                'massive_money_gain',
                $balance >= $threshold * 3 ? 'critical' : 'warn',
                $userId,
                0,
                'items_users',
                (string) self::COIN_ITEM_ID,
                $balance,
                min(100, (int) floor($balance / max(1, $threshold) * 30)),
                'Большой текущий баланс монет',
                ['source' => 'coin_balance'],
                time()
            );
        }
        return $alerts;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function scanTransferAbuse(int $cutoff, int $threshold, int $countThreshold, int $limit): array
    {
        if (!$this->tableExists('market_lots')) {
            return [];
        }
        $stmt = $this->db->prepare(
            'SELECT seller_id, buyer_id, COUNT(*) AS deals, SUM(total_price) AS total, MAX(sold_at) AS last_at
               FROM market_lots
              WHERE status = "sold"
                AND buyer_id > 0
                AND sold_at >= :cutoff
           GROUP BY seller_id, buyer_id
             HAVING total >= :threshold OR deals >= :count_threshold
           ORDER BY total DESC, deals DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':cutoff', $cutoff, PDO::PARAM_INT);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->bindValue(':count_threshold', $countThreshold, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $seller = (int) ($row['seller_id'] ?? 0);
            $buyer = (int) ($row['buyer_id'] ?? 0);
            $total = (int) ($row['total'] ?? 0);
            $deals = (int) ($row['deals'] ?? 0);
            $score = min(100, max(40, $deals * 10 + (int) floor($total / max(1, $threshold) * 30)));
            $alerts[] = $this->alert(
                'transfer_abuse:pair:' . $seller . ':' . $buyer . ':' . $cutoff,
                'transfer_abuse',
                $score >= 80 ? 'critical' : 'warn',
                $seller,
                $buyer,
                'market_pair',
                $seller . ':' . $buyer,
                $total,
                $score,
                'Возможный перевод ценностей через рынок',
                ['deals' => $deals, 'total' => $total, 'window_cutoff' => $cutoff],
                (int) ($row['last_at'] ?? time())
            );
        }
        return $alerts;
    }

    /**
     * @param array<string,int|bool> $settings
     * @return array<int,array<string,mixed>>
     */
    private function scanFakeMarketPrices(array $settings, int $limit): array
    {
        if (!$this->tableExists('market_lots')) {
            return [];
        }
        $window = time() - 30 * 86400;
        $multiplier = max(2, (int) $settings['fake_price_multiplier']);
        $minSales = max(1, (int) $settings['fake_price_min_sales']);
        $minUnit = max(1, (int) $settings['fake_price_min_unit']);
        $stmt = $this->db->prepare(
            'SELECT l.*, b.avg_unit, b.sales
               FROM market_lots l
               JOIN (
                    SELECT object_type, object_id, AVG(price_per_unit) AS avg_unit, COUNT(*) AS sales
                      FROM market_lots
                     WHERE status = "sold"
                       AND sold_at >= :window
                       AND price_per_unit > 0
                  GROUP BY object_type, object_id
                    HAVING sales >= :min_sales
               ) b ON b.object_type = l.object_type AND b.object_id = l.object_id
              WHERE l.price_per_unit >= :min_unit
                AND (
                     l.price_per_unit >= b.avg_unit * :multiplier
                     OR l.price_per_unit <= GREATEST(1, b.avg_unit / :multiplier_low)
                )
                AND (
                     l.status = "active"
                     OR (l.status = "sold" AND l.sold_at >= :window_recent)
                )
           ORDER BY l.created_at DESC
              LIMIT :limit'
        );
        $stmt->bindValue(':window', $window, PDO::PARAM_INT);
        $stmt->bindValue(':min_sales', $minSales, PDO::PARAM_INT);
        $stmt->bindValue(':min_unit', $minUnit, PDO::PARAM_INT);
        $stmt->bindValue(':multiplier', $multiplier, PDO::PARAM_INT);
        $stmt->bindValue(':multiplier_low', $multiplier, PDO::PARAM_INT);
        $stmt->bindValue(':window_recent', $window, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $alerts = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $lotId = (int) ($row['id'] ?? 0);
            $unit = (int) ($row['price_per_unit'] ?? 0);
            $avg = max(1, (float) ($row['avg_unit'] ?? 1));
            $ratio = $unit >= $avg ? $unit / $avg : $avg / max(1, $unit);
            $score = min(100, (int) floor($ratio * 15));
            $alerts[] = $this->alert(
                'fake_market_price:lot:' . $lotId,
                'fake_market_price',
                $score >= 80 ? 'critical' : 'warn',
                (int) ($row['seller_id'] ?? 0),
                (int) ($row['buyer_id'] ?? 0),
                'market_lot',
                (string) $lotId,
                (int) ($row['total_price'] ?? 0),
                $score,
                'Цена лота сильно отличается от истории рынка',
                ['lot' => $row, 'avg_unit' => $avg, 'ratio' => $ratio, 'sales' => (int) ($row['sales'] ?? 0)],
                max((int) ($row['sold_at'] ?? 0), (int) ($row['created_at'] ?? time()))
            );
        }
        return $alerts;
    }

    /**
     * @param array<string,mixed> $details
     * @return array<string,mixed>
     */
    private function alert(
        string $key,
        string $type,
        string $severity,
        int $userId,
        int $relatedUserId,
        string $entityType,
        string $entityId,
        int $amount,
        int $score,
        string $title,
        array $details,
        int $seenAt
    ): array {
        return [
            'alert_key' => $key,
            'alert_type' => $type,
            'severity' => $severity,
            'user_id' => $userId,
            'related_user_id' => $relatedUserId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'amount' => $amount,
            'score' => max(0, min(100, $score)),
            'title' => $title,
            'details' => $details,
            'seen_at' => $seenAt > 0 ? $seenAt : time(),
        ];
    }

    /**
     * @param array<string,mixed> $alert
     */
    private function upsertAlert(array $alert): bool
    {
        if (!$this->tableExists('economy_guard_alerts')) {
            return false;
        }
        $existing = $this->db->prepare('SELECT id, status FROM economy_guard_alerts WHERE alert_key = :key LIMIT 1');
        $existing->execute(['key' => (string) $alert['alert_key']]);
        $row = $existing->fetch(PDO::FETCH_ASSOC) ?: null;
        $isNew = $row === null;
        $now = time();
        $firstSeen = $isNew ? (int) $alert['seen_at'] : 0;
        $this->db->prepare(
            'INSERT INTO economy_guard_alerts
                (alert_key, alert_type, severity, status, user_id, related_user_id, entity_type, entity_id, amount, score, title, details_json, first_seen_at, last_seen_at, reviewed_by, reviewed_at, note)
             VALUES
                (:key, :type, :severity, "open", :user, :related, :entity_type, :entity_id, :amount, :score, :title, :details, :first_seen, :last_seen, 0, 0, "")
             ON DUPLICATE KEY UPDATE
                severity = VALUES(severity),
                amount = VALUES(amount),
                score = VALUES(score),
                title = VALUES(title),
                details_json = VALUES(details_json),
                last_seen_at = VALUES(last_seen_at),
                status = IF(status IN ("reviewed", "ignored"), status, "open")'
        )->execute([
            'key' => (string) $alert['alert_key'],
            'type' => (string) $alert['alert_type'],
            'severity' => (string) $alert['severity'],
            'user' => (int) $alert['user_id'],
            'related' => (int) $alert['related_user_id'],
            'entity_type' => (string) $alert['entity_type'],
            'entity_id' => (string) $alert['entity_id'],
            'amount' => (int) $alert['amount'],
            'score' => (int) $alert['score'],
            'title' => mb_substr((string) $alert['title'], 0, 190),
            'details' => $this->json((array) $alert['details']),
            'first_seen' => $firstSeen ?: $now,
            'last_seen' => (int) $alert['seen_at'],
        ]);
        return $isNew;
    }

    /**
     * @return array<string,int|bool>
     */
    private function settings(): array
    {
        $defaults = [
            'enabled' => true,
            'scan_window_seconds' => 86400,
            'massive_money_gain_threshold' => 10000000,
            'transfer_pair_threshold' => 10000000,
            'transfer_pair_count_threshold' => 5,
            'fake_price_multiplier' => 10,
            'fake_price_min_sales' => 3,
            'fake_price_min_unit' => 100000,
            'coin_balance_threshold' => 100000000,
        ];
        if (!$this->tableExists('site_settings')) {
            return $defaults;
        }
        $stmt = $this->db->query('SELECT name, value FROM site_settings WHERE name LIKE "economy_guard.%"');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $key = str_replace('economy_guard.', '', (string) ($row['name'] ?? ''));
            if (!array_key_exists($key, $defaults)) {
                continue;
            }
            $value = (string) ($row['value'] ?? '');
            if (is_bool($defaults[$key])) {
                $defaults[$key] = $value === '1' || mb_strtolower($value) === 'true';
            } else {
                $defaults[$key] = max(0, (int) $value);
            }
        }
        return $defaults;
    }

    private function enabled(): bool
    {
        return (bool) $this->settings()['enabled'];
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function formatAlert(array $row): array
    {
        $row['id'] = (int) ($row['id'] ?? 0);
        $row['user_id'] = (int) ($row['user_id'] ?? 0);
        $row['related_user_id'] = (int) ($row['related_user_id'] ?? 0);
        $row['amount'] = (int) ($row['amount'] ?? 0);
        $row['score'] = (int) ($row['score'] ?? 0);
        $row['details'] = $this->decode((string) ($row['details_json'] ?? '{}'));
        unset($row['details_json']);
        return $row;
    }

    /**
     * @param array<string,mixed> $params
     */
    private function prepareCount(string $sql, array $params): int
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function tableExists(string $table): bool
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return false;
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $stmt->execute(['table' => $table]);
        return (int) ($stmt->fetchColumn() ?: 0) > 0;
    }

    private function decode(string $value): array
    {
        $decoded = json_decode($value !== '' ? $value : '{}', true);
        return is_array($decoded) ? $decoded : [];
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}';
    }
}
