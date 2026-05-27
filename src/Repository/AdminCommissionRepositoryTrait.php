<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

trait AdminCommissionRepositoryTrait
{
    public function commissionDashboard(array $filters = []): array
    {
        if (!$this->tableExists('market_lots')) {
            return [
                'overview' => [],
                'windows' => [],
                'recentLogs' => [],
                'returns' => [],
                'risk' => ['open' => 0, 'approved' => 0],
            ];
        }

        $now = time();
        $monthStart = strtotime(date('Y-m-01 00:00:00', $now)) ?: ($now - 30 * 86400);
        $overview = [
            'salesToday' => $this->soldCountSince(strtotime(date('Y-m-d 00:00:00', $now)) ?: ($now - 86400)),
            'sales7d' => $this->soldCountSince($now - 7 * 86400),
            'sales14d' => $this->soldCountSince($now - 14 * 86400),
            'sales30d' => $this->soldCountSince($now - 30 * 86400),
            'salesMonth' => $this->soldCountSince($monthStart),
            'turnover30d' => $this->soldSumSince($now - 30 * 86400, 'total_price'),
            'commission30d' => $this->soldSumSince($now - 30 * 86400, 'commission_amount'),
            'avgUnit30d' => $this->soldAvgSince($now - 30 * 86400),
            'activeLots' => $this->countTable('market_lots', 'status = "active" AND expires_at > UNIX_TIMESTAMP()'),
            'pendingReturns' => $this->tableExists('market_return_storage') ? $this->countTable('market_return_storage', 'status = "pending"') : 0,
            'riskOpen' => $this->commissionRiskCount(),
        ];

        return [
            'overview' => $overview,
            'windows' => $this->commissionPriceWindows([]),
            'recentLogs' => $this->commissionLogs('', 12, 0, ['period' => '30d'])['rows'],
            'returns' => $this->commissionReturns('', 12, 0, ['status' => 'pending'])['rows'],
            'risk' => [
                'open' => $overview['riskOpen'],
                'approved' => $this->tableExists('market_deal_reviews') ? $this->countTable('market_deal_reviews', 'status = "approved"') : 0,
            ],
        ];
    }

    public function commissionLots(string $query = '', int $limit = 80, int $offset = 0, array $filters = []): array
    {
        if (!$this->tableExists('market_lots')) {
            return ['rows' => [], 'total' => 0];
        }

        [$where, $params] = $this->commissionLotWhere($query, $filters, 'l');
        $sql = $this->commissionLotSelect()
            . ' WHERE ' . $where
            . ' ORDER BY ' . $this->commissionSort((string) ($filters['sort'] ?? 'new'), 'l')
            . ' LIMIT ' . max(1, min(200, $limit)) . ' OFFSET ' . max(0, $offset);
        $rows = array_map(fn (array $row): array => $this->formatCommissionLot($row), $this->lookupRowsPrepared($sql, $params));
        $total = $this->countPrepared(
            'SELECT COUNT(*) FROM market_lots l
              LEFT JOIN users buyer ON buyer.id = l.buyer_id
              LEFT JOIN market_deal_reviews r ON r.lot_id = l.id
             WHERE ' . $where,
            $params
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function commissionLogs(string $query = '', int $limit = 80, int $offset = 0, array $filters = []): array
    {
        if (!$this->tableExists('market_logs')) {
            return ['rows' => [], 'total' => 0];
        }

        [$where, $params] = $this->commissionLogWhere($query, $filters);
        $sort = $this->commissionSort((string) ($filters['sort'] ?? 'new'), 'l');
        $sql = 'SELECT log.id AS log_id, log.action, log.actor_id, actor.login AS actor_login, log.lot_id, log.data_json, log.created_at AS log_created_at,
                       l.*, buyer.login AS buyer_login, r.status AS review_status, r.reviewed_by, r.reviewed_at, r.note AS review_note, reviewer.login AS reviewer_login
                  FROM market_logs log
             LEFT JOIN market_lots l ON l.id = log.lot_id
             LEFT JOIN users actor ON actor.id = log.actor_id
             LEFT JOIN users buyer ON buyer.id = l.buyer_id
             LEFT JOIN market_deal_reviews r ON r.lot_id = l.id
             LEFT JOIN users reviewer ON reviewer.id = r.reviewed_by
                 WHERE ' . $where . '
              ORDER BY ' . str_replace('l.created_at', 'log.created_at', $sort) . ', log.id DESC
                 LIMIT ' . max(1, min(200, $limit)) . ' OFFSET ' . max(0, $offset);
        $rows = array_map(fn (array $row): array => $this->formatCommissionLog($row), $this->lookupRowsPrepared($sql, $params));
        $total = $this->countPrepared(
            'SELECT COUNT(*)
               FROM market_logs log
          LEFT JOIN market_lots l ON l.id = log.lot_id
          LEFT JOIN users actor ON actor.id = log.actor_id
          LEFT JOIN users buyer ON buyer.id = l.buyer_id
          LEFT JOIN market_deal_reviews r ON r.lot_id = l.id
              WHERE ' . $where,
            $params
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function commissionReturns(string $query = '', int $limit = 80, int $offset = 0, array $filters = []): array
    {
        if (!$this->tableExists('market_return_storage')) {
            return ['rows' => [], 'total' => 0];
        }

        $where = ['1=1'];
        $params = [];
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $where[] = 'r.status = :return_status';
            $params['return_status'] = $status;
        }
        if ($query !== '') {
            $where[] = '(CAST(r.id AS CHAR) = :q_exact OR CAST(r.lot_id AS CHAR) = :q_exact OR u.login LIKE :q_like OR l.object_name LIKE :q_like)';
            $params['q_exact'] = $query;
            $params['q_like'] = '%' . $query . '%';
        }
        $whereSql = implode(' AND ', $where);
        $rows = $this->lookupRowsPrepared(
            'SELECT r.*, u.login AS user_login, l.object_name, l.object_icon, l.category, l.total_price
               FROM market_return_storage r
          LEFT JOIN users u ON u.id = r.user_id
          LEFT JOIN market_lots l ON l.id = r.lot_id
              WHERE ' . $whereSql . '
           ORDER BY r.id DESC
              LIMIT ' . max(1, min(200, $limit)) . ' OFFSET ' . max(0, $offset),
            $params
        );

        return [
            'rows' => array_map(fn (array $row): array => $this->formatCommissionReturn($row), $rows),
            'total' => $this->countPrepared(
                'SELECT COUNT(*) FROM market_return_storage r LEFT JOIN users u ON u.id = r.user_id LEFT JOIN market_lots l ON l.id = r.lot_id WHERE ' . $whereSql,
                $params
            ),
        ];
    }

    public function commissionPriceHistory(array $filters = []): array
    {
        $windows = $this->commissionPriceWindows($filters);
        $active = [];
        $lastSold = null;
        if ($this->tableExists('market_lots')) {
            [$where, $params] = $this->commissionLotWhere('', array_merge($filters, ['status' => 'active']), 'l');
            $active = array_map(
                fn (array $row): array => $this->formatCommissionLot($row),
                $this->lookupRowsPrepared($this->commissionLotSelect() . ' WHERE ' . $where . ' ORDER BY l.price_per_unit ASC, l.id DESC LIMIT 20', $params)
            );
            [$soldWhere, $soldParams] = $this->commissionLotWhere('', array_merge($filters, ['status' => 'sold']), 'l');
            $soldRows = $this->lookupRowsPrepared(
                $this->commissionLotSelect() . ' WHERE ' . $soldWhere . ' ORDER BY l.sold_at DESC, l.id DESC LIMIT 1',
                $soldParams
            );
            if ($soldRows !== []) {
                $lastSold = $this->formatCommissionLot($soldRows[0]);
            }
        }

        return ['windows' => $windows, 'active' => $active, 'lastSold' => $lastSold];
    }

    public function reviewCommissionRisk(int $adminId, array $payload): array
    {
        if (!$this->tableExists('market_lots') || !$this->tableExists('market_deal_reviews')) {
            return ['ok' => false, 'message' => 'Таблица ревью подозрительных сделок не готова.'];
        }

        $lotId = max(0, (int) ($payload['lot_id'] ?? 0));
        if ($lotId <= 0) {
            return ['ok' => false, 'message' => 'Выберите сделку.'];
        }

        $stmt = $this->db->prepare('SELECT * FROM market_lots WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $lotId]);
        $lot = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lot) {
            return ['ok' => false, 'message' => 'Лот не найден.'];
        }

        $risk = $this->commissionDealRisk($lot);
        $note = mb_substr(trim((string) ($payload['note'] ?? 'Проверено вручную: сделка нормальная.')), 0, 255);
        $now = time();
        $this->db->prepare(
            'INSERT INTO market_deal_reviews (lot_id, status, risk_score, risk_flags_json, reviewed_by, reviewed_at, note, created_at, updated_at)
             VALUES (:lot, "approved", :score, :flags, :admin, :reviewed_at, :note, :created_at, :updated_at)
             ON DUPLICATE KEY UPDATE status = VALUES(status), risk_score = VALUES(risk_score), risk_flags_json = VALUES(risk_flags_json),
                                     reviewed_by = VALUES(reviewed_by), reviewed_at = VALUES(reviewed_at), note = VALUES(note), updated_at = VALUES(updated_at)'
        )->execute([
            'lot' => $lotId,
            'score' => (int) $risk['risk_score'],
            'flags' => json_encode($risk['risk_flags'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'admin' => $adminId,
            'reviewed_at' => $now,
            'note' => $note,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->db->prepare(
            'INSERT INTO market_logs (action, actor_id, lot_id, data_json, created_at)
             VALUES ("risk.approved", :admin, :lot, :data, :time)'
        )->execute([
            'admin' => $adminId,
            'lot' => $lotId,
            'data' => json_encode(['note' => $note, 'risk' => $risk], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'time' => $now,
        ]);
        $this->audit($adminId, 'commission.risk.approved', 'market_lots', $lotId, ['note' => $note, 'risk' => $risk]);

        return ['ok' => true, 'message' => 'Сделка помечена как проверенная.', 'risk' => $risk];
    }

    private function commissionLotSelect(): string
    {
        return 'SELECT l.*, buyer.login AS buyer_login, r.status AS review_status, r.reviewed_by, r.reviewed_at, r.note AS review_note, reviewer.login AS reviewer_login
                  FROM market_lots l
             LEFT JOIN users buyer ON buyer.id = l.buyer_id
             LEFT JOIN market_deal_reviews r ON r.lot_id = l.id
             LEFT JOIN users reviewer ON reviewer.id = r.reviewed_by';
    }

    private function commissionLotWhere(string $query, array $filters, string $alias): array
    {
        $where = ['1=1'];
        $params = [];
        $prefix = $alias . '.';

        foreach (['status', 'object_type', 'category'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '' && $value !== 'all') {
                $where[] = $prefix . $key . ' = :' . $key;
                $params[$key] = $value;
            }
        }
        foreach (['seller_id', 'buyer_id', 'object_id', 'lot_id'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '' && ctype_digit($value)) {
                $column = $key === 'lot_id' ? 'id' : $key;
                $where[] = $prefix . $column . ' = :' . $key;
                $params[$key] = (int) $value;
            }
        }

        $seller = trim((string) ($filters['seller'] ?? ''));
        if ($seller !== '') {
            $where[] = '(' . $prefix . 'seller_name LIKE :seller_like OR CAST(' . $prefix . 'seller_id AS CHAR) = :seller_exact)';
            $params['seller_like'] = '%' . $seller . '%';
            $params['seller_exact'] = $seller;
        }
        $buyer = trim((string) ($filters['buyer'] ?? ''));
        if ($buyer !== '') {
            $where[] = '(buyer.login LIKE :buyer_like OR CAST(' . $prefix . 'buyer_id AS CHAR) = :buyer_exact)';
            $params['buyer_like'] = '%' . $buyer . '%';
            $params['buyer_exact'] = $buyer;
        }
        $legacy = trim((string) ($filters['legacy'] ?? ''));
        if ($legacy !== '') {
            $where[] = '(' . $prefix . 'legacy_source_type LIKE :legacy_like OR CAST(' . $prefix . 'legacy_source_id AS CHAR) = :legacy_exact)';
            $params['legacy_like'] = '%' . $legacy . '%';
            $params['legacy_exact'] = $legacy;
        }
        $minPrice = trim((string) ($filters['price_min'] ?? ''));
        if ($minPrice !== '' && ctype_digit($minPrice)) {
            $where[] = $prefix . 'total_price >= :price_min';
            $params['price_min'] = (int) $minPrice;
        }
        $maxPrice = trim((string) ($filters['price_max'] ?? ''));
        if ($maxPrice !== '' && ctype_digit($maxPrice)) {
            $where[] = $prefix . 'total_price <= :price_max';
            $params['price_max'] = (int) $maxPrice;
        }
        if ((string) ($filters['system_only'] ?? '') === '1') {
            $where[] = $prefix . 'seller_name = "Система"';
        }
        if ((string) ($filters['risky'] ?? '') === '1') {
            $where[] = $this->commissionRiskSql($alias);
        }

        [$start, $end] = $this->commissionPeriodBounds($filters);
        if ($start > 0 || $end > 0) {
            $timeColumn = $alias === 'log'
                ? 'log.created_at'
                : 'COALESCE(NULLIF(' . $prefix . 'sold_at, 0), ' . $prefix . 'created_at)';
            if ($start > 0) {
                $where[] = $timeColumn . ' >= :period_start';
                $params['period_start'] = $start;
            }
            if ($end > 0) {
                $where[] = $timeColumn . ' <= :period_end';
                $params['period_end'] = $end;
            }
        }

        $query = trim($query);
        if ($query !== '') {
            $where[] = '(' . $prefix . 'object_name LIKE :q_like OR ' . $prefix . 'seller_name LIKE :q_like OR buyer.login LIKE :q_like OR CAST(' . $prefix . 'id AS CHAR) = :q_exact OR CAST(' . $prefix . 'object_id AS CHAR) = :q_exact)';
            $params['q_like'] = '%' . $query . '%';
            $params['q_exact'] = $query;
        }

        return [implode(' AND ', $where), $params];
    }

    private function commissionLogWhere(string $query, array $filters): array
    {
        $lotFilters = $filters;
        unset($lotFilters['period'], $lotFilters['date_from'], $lotFilters['date_to']);
        [$where, $params] = $this->commissionLotWhere($query, $lotFilters, 'l');
        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '' && $action !== 'all') {
            $where .= ' AND log.action = :log_action';
            $params['log_action'] = $action;
        }
        [$start, $end] = $this->commissionPeriodBounds($filters);
        if ($start > 0) {
            $where .= ' AND log.created_at >= :log_period_start';
            $params['log_period_start'] = $start;
        }
        if ($end > 0) {
            $where .= ' AND log.created_at <= :log_period_end';
            $params['log_period_end'] = $end;
        }
        return [$where, $params];
    }

    private function commissionSort(string $sort, string $alias): string
    {
        $prefix = $alias . '.';
        return match ($sort) {
            'old' => $prefix . 'created_at ASC',
            'price_asc' => $prefix . 'total_price ASC',
            'price_desc' => $prefix . 'total_price DESC',
            'unit_asc' => $prefix . 'price_per_unit ASC',
            'unit_desc' => $prefix . 'price_per_unit DESC',
            'commission_asc' => $prefix . 'commission_amount ASC',
            'commission_desc' => $prefix . 'commission_amount DESC',
            'quantity_asc' => $prefix . 'quantity ASC',
            'quantity_desc' => $prefix . 'quantity DESC',
            'seller' => $prefix . 'seller_name ASC',
            'buyer' => 'buyer.login ASC',
            'name' => $prefix . 'object_name ASC',
            default => $prefix . 'created_at DESC',
        };
    }

    private function commissionRiskSql(string $alias): string
    {
        $prefix = $alias . '.';
        return '((COALESCE(r.status, "") <> "approved") AND ('
            . $prefix . 'total_price >= 50000000 OR '
            . $prefix . 'price_per_unit >= 10000000 OR ('
            . $prefix . 'object_type = "item" AND ('
            . $prefix . 'object_name REGEXP "поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|great.?ball|ultra.?ball|master.?ball|ball|шар" OR '
            . $prefix . 'object_id IN (3,25,90004,90005)) AND ('
            . $prefix . 'total_price >= 1000000 OR ' . $prefix . 'price_per_unit >= 500000)) OR ('
            . $prefix . 'object_type = "item" AND ' . $prefix . 'category IN ("other","craft","ticket") AND ' . $prefix . 'price_per_unit >= 5000000) OR ('
            . $prefix . 'quantity >= 100 AND ' . $prefix . 'price_per_unit >= 250000)))';
    }

    private function commissionPeriodBounds(array $filters): array
    {
        $now = time();
        $period = (string) ($filters['period'] ?? '');
        $start = match ($period) {
            'today' => strtotime(date('Y-m-d 00:00:00', $now)) ?: 0,
            '7d' => $now - 7 * 86400,
            '14d' => $now - 14 * 86400,
            '30d' => $now - 30 * 86400,
            'month' => strtotime(date('Y-m-01 00:00:00', $now)) ?: 0,
            default => 0,
        };
        $end = 0;
        $from = $this->parseTimestamp((string) ($filters['date_from'] ?? ''));
        $to = $this->parseTimestamp((string) ($filters['date_to'] ?? ''));
        if ($from > 0) {
            $start = $from;
        }
        if ($to > 0) {
            $end = $to + 86399;
        }
        return [$start, $end];
    }

    private function formatCommissionLot(array $row): array
    {
        $risk = $this->commissionDealRisk($row);
        $reviewStatus = (string) ($row['review_status'] ?? '');
        if ($reviewStatus === 'approved') {
            $risk['reviewed'] = true;
            $risk['risk_label'] = 'Проверено: сделка норм';
        }
        return [
            'id' => (int) ($row['id'] ?? 0),
            'lot_id' => (int) ($row['id'] ?? 0),
            'seller_id' => (int) ($row['seller_id'] ?? 0),
            'seller_name' => (string) ($row['seller_name'] ?? ''),
            'buyer_id' => (int) ($row['buyer_id'] ?? 0),
            'buyer_name' => (string) ($row['buyer_login'] ?? ''),
            'object_type' => (string) ($row['object_type'] ?? ''),
            'object_id' => (int) ($row['object_id'] ?? 0),
            'object_name' => (string) ($row['object_name'] ?? ''),
            'object_icon' => (string) ($row['object_icon'] ?? ''),
            'category' => (string) ($row['category'] ?? ''),
            'quantity' => (int) ($row['quantity'] ?? 0),
            'price_per_unit' => (int) ($row['price_per_unit'] ?? 0),
            'total_price' => (int) ($row['total_price'] ?? 0),
            'commission_amount' => (int) ($row['commission_amount'] ?? 0),
            'seller_income' => max(0, (int) ($row['total_price'] ?? 0) - (int) ($row['commission_amount'] ?? 0)),
            'status' => (string) ($row['status'] ?? ''),
            'created_at' => (int) ($row['created_at'] ?? 0),
            'expires_at' => (int) ($row['expires_at'] ?? 0),
            'sold_at' => (int) ($row['sold_at'] ?? 0),
            'created_at_text' => $this->dateTimeText((int) ($row['created_at'] ?? 0)),
            'sold_at_text' => $this->dateTimeText((int) ($row['sold_at'] ?? 0)),
            'legacy_source_type' => (string) ($row['legacy_source_type'] ?? ''),
            'legacy_source_id' => (int) ($row['legacy_source_id'] ?? 0),
            'snapshot' => $this->jsonDecode((string) ($row['object_snapshot_json'] ?? '')),
            'reserve' => $this->jsonDecode((string) ($row['reserve_payload_json'] ?? '')),
            'risk' => $risk + [
                'review_status' => $reviewStatus,
                'reviewed_by' => (int) ($row['reviewed_by'] ?? 0),
                'reviewer_login' => (string) ($row['reviewer_login'] ?? ''),
                'reviewed_at' => (int) ($row['reviewed_at'] ?? 0),
                'review_note' => (string) ($row['review_note'] ?? ''),
            ],
        ];
    }

    private function formatCommissionLog(array $row): array
    {
        $lot = $this->formatCommissionLot($row);
        $data = $this->jsonDecode((string) ($row['data_json'] ?? ''));
        return $lot + [
            'log_id' => (int) ($row['log_id'] ?? 0),
            'action' => (string) ($row['action'] ?? ''),
            'actor_id' => (int) ($row['actor_id'] ?? 0),
            'actor_login' => (string) ($row['actor_login'] ?? ''),
            'log_created_at' => (int) ($row['log_created_at'] ?? 0),
            'log_created_at_text' => $this->dateTimeText((int) ($row['log_created_at'] ?? 0)),
            'data' => $data,
            'normalized' => [
                'seller_id' => (int) ($data['seller_id'] ?? $lot['seller_id']),
                'buyer_id' => (int) ($data['buyer_id'] ?? $lot['buyer_id']),
                'total' => (int) ($data['total'] ?? $lot['total_price']),
                'commission' => (int) ($data['commission'] ?? $lot['commission_amount']),
                'object_snapshot_json' => $lot['snapshot'],
            ],
        ];
    }

    private function formatCommissionReturn(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'user_id' => (int) ($row['user_id'] ?? 0),
            'user_login' => (string) ($row['user_login'] ?? ''),
            'lot_id' => (int) ($row['lot_id'] ?? 0),
            'object_type' => (string) ($row['object_type'] ?? ''),
            'object_id' => (int) ($row['object_id'] ?? 0),
            'object_name' => (string) ($row['object_name'] ?? ''),
            'quantity' => (int) ($row['quantity'] ?? 0),
            'status' => (string) ($row['status'] ?? ''),
            'created_at' => (int) ($row['created_at'] ?? 0),
            'resolved_at' => (int) ($row['resolved_at'] ?? 0),
            'created_at_text' => $this->dateTimeText((int) ($row['created_at'] ?? 0)),
            'payload' => $this->jsonDecode((string) ($row['payload_json'] ?? '')),
        ];
    }

    private function commissionDealRisk(array $lot): array
    {
        $flags = [];
        $score = 0;
        $objectType = (string) ($lot['object_type'] ?? '');
        $name = mb_strtolower((string) ($lot['object_name'] ?? ''), 'UTF-8');
        $category = (string) ($lot['category'] ?? '');
        $quantity = max(1, (int) ($lot['quantity'] ?? 1));
        $unit = max(0, (int) ($lot['price_per_unit'] ?? 0));
        $total = max(0, (int) ($lot['total_price'] ?? ($unit * $quantity)));
        if ($total >= 50_000_000) {
            $flags[] = 'Сумма сделки 50 млн+';
            $score += 60;
        }
        if ($unit >= 10_000_000) {
            $flags[] = 'Цена за штуку 10 млн+';
            $score += 35;
        }
        $isEasyItem = $objectType === 'item'
            && ((bool) preg_match('/поке.?бол|мастер.?бол|ультра.?бол|премиум.?бол|грит.?бол|great.?ball|ultra.?ball|master.?ball|ball|шар/ui', $name)
                || in_array((int) ($lot['object_id'] ?? 0), [3, 25, 90004, 90005], true));
        if ($isEasyItem && ($total >= 1_000_000 || $unit >= 500_000)) {
            $flags[] = 'Легкодоступный предмет с высокой ценой';
            $score += 55;
        }
        if ($objectType === 'item' && in_array($category, ['other', 'craft', 'ticket'], true) && $unit >= 5_000_000) {
            $flags[] = 'Утилитарный предмет с высокой ценой';
            $score += 25;
        }
        if ($quantity >= 100 && $unit >= 250_000) {
            $flags[] = 'Большой стак с высокой ценой за штуку';
            $score += 30;
        }
        return [
            'is_risky' => $flags !== [],
            'reviewed' => false,
            'risk_score' => min(100, $score),
            'risk_flags' => $flags,
            'risk_label' => $flags === [] ? 'ОК' : implode('; ', $flags),
        ];
    }

    private function commissionPriceWindows(array $filters): array
    {
        if (!$this->tableExists('market_lots')) {
            return [];
        }
        $now = time();
        return [
            $this->commissionPriceWindow($filters, 1, $now - 86400),
            $this->commissionPriceWindow($filters, 7, $now - 7 * 86400),
            $this->commissionPriceWindow($filters, 14, $now - 14 * 86400),
            $this->commissionPriceWindow($filters, 30, $now - 30 * 86400),
        ];
    }

    private function commissionPriceWindow(array $filters, int $days, int $since): array
    {
        $where = ['status = "sold"', 'sold_at >= :since'];
        $params = ['since' => $since];
        foreach (['object_type', 'category'] as $key) {
            $value = trim((string) ($filters[$key] ?? ''));
            if ($value !== '' && $value !== 'all') {
                $where[] = $key . ' = :' . $key;
                $params[$key] = $value;
            }
        }
        $objectId = trim((string) ($filters['object_id'] ?? ''));
        if ($objectId !== '' && ctype_digit($objectId)) {
            $where[] = 'object_id = :object_id';
            $params['object_id'] = (int) $objectId;
        }
        $query = trim((string) ($filters['q'] ?? ''));
        if ($query !== '') {
            $where[] = '(object_name LIKE :q OR CAST(id AS CHAR) = :q_exact OR CAST(object_id AS CHAR) = :q_exact)';
            $params['q'] = '%' . $query . '%';
            $params['q_exact'] = $query;
        }
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS sales, COALESCE(SUM(quantity), 0) AS quantity, COALESCE(SUM(total_price), 0) AS turnover,
                    COALESCE(SUM(commission_amount), 0) AS commission, COALESCE(AVG(price_per_unit), 0) AS avg_unit,
                    COALESCE(MIN(price_per_unit), 0) AS min_unit, COALESCE(MAX(price_per_unit), 0) AS max_unit,
                    COALESCE(MAX(sold_at), 0) AS last_sold_at
               FROM market_lots
              WHERE ' . implode(' AND ', $where)
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'days' => $days,
            'sales' => (int) ($row['sales'] ?? 0),
            'quantity' => (int) ($row['quantity'] ?? 0),
            'turnover' => (int) ($row['turnover'] ?? 0),
            'commission' => (int) ($row['commission'] ?? 0),
            'avg_unit' => (int) round((float) ($row['avg_unit'] ?? 0)),
            'min_unit' => (int) ($row['min_unit'] ?? 0),
            'max_unit' => (int) ($row['max_unit'] ?? 0),
            'last_sold_at' => (int) ($row['last_sold_at'] ?? 0),
        ];
    }

    private function soldCountSince(int $since): int
    {
        return $this->countPrepared('SELECT COUNT(*) FROM market_lots WHERE status = "sold" AND sold_at >= :since', ['since' => $since]);
    }

    private function soldSumSince(int $since, string $column): int
    {
        if (!in_array($column, ['total_price', 'commission_amount'], true)) {
            return 0;
        }
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(' . $column . '), 0) FROM market_lots WHERE status = "sold" AND sold_at >= :since');
        $stmt->execute(['since' => $since]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function soldAvgSince(int $since): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(AVG(price_per_unit), 0) FROM market_lots WHERE status = "sold" AND sold_at >= :since');
        $stmt->execute(['since' => $since]);
        return (int) round((float) ($stmt->fetchColumn() ?: 0));
    }

    private function commissionRiskCount(): int
    {
        if (!$this->tableExists('market_lots')) {
            return 0;
        }
        $join = $this->tableExists('market_deal_reviews')
            ? 'LEFT JOIN market_deal_reviews r ON r.lot_id = l.id'
            : 'LEFT JOIN (SELECT NULL AS lot_id, NULL AS status) r ON 1=0';
        return $this->countPrepared('SELECT COUNT(*) FROM market_lots l ' . $join . ' WHERE ' . $this->commissionRiskSql('l'), []);
    }

    private function countPrepared(string $sql, array $params): int
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function dateTimeText(int $timestamp): string
    {
        return $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : '';
    }

    private function jsonDecode(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
