<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;

final class AdminRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function canAccess(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT u.id, u.groups, u.activation, COALESCE(iu.admins_panels, 0) AS admins_panels
               FROM users u
          LEFT JOIN information_users iu ON iu.users_id = u.id
              WHERE u.id = :id
              LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        return (int) ($row['activation'] ?? 0) === 1
            && (int) ($row['groups'] ?? 0) === 1
            && (int) ($row['admins_panels'] ?? 0) === 1;
    }

    public function overview(): array
    {
        return [
            'users' => $this->countTable('users'),
            'items' => $this->countTable('items'),
            'dropRules' => $this->countTable('admin_drop_rules'),
            'enabledDropRules' => $this->countTable('admin_drop_rules', 'enabled = 1'),
            'legacyDropRules' => $this->countTable('items_drop'),
            'locations' => $this->countTable('build'),
        ];
    }

    public function legacyModules(): array
    {
        return [
            ['key' => 'news', 'title' => 'Новости и оповещения', 'status' => 'TODO_REWRITE', 'source' => 'admin/bb_news_admin.php'],
            ['key' => 'pok', 'title' => 'Выдача покемонов', 'status' => 'TODO_REWRITE', 'source' => 'admin/poke.php'],
            ['key' => 'attak_pokes', 'title' => 'Выдача атак и яиц с атаками', 'status' => 'TODO_REWRITE', 'source' => 'admin/attak_pokes.php'],
            ['key' => 'alm', 'title' => 'Выдача алмазов', 'status' => 'TODO_REWRITE', 'source' => 'admin/alm.php'],
            ['key' => 'gitem', 'title' => 'Выдача предметов', 'status' => 'PARTIAL_NEW', 'source' => 'admin/gitem.php'],
            ['key' => 'logs', 'title' => 'Логи обмена и продаж', 'status' => 'TODO_REWRITE', 'source' => 'admin/logs.php'],
            ['key' => 'bans', 'title' => 'Баны', 'status' => 'TODO_REWRITE', 'source' => 'admin/bans.php'],
            ['key' => 'drop_rules', 'title' => 'Предметы и дроп', 'status' => 'PARTIAL_NEW', 'source' => 'items_drop'],
            ['key' => 'tournaments', 'title' => 'Турниры и медали', 'status' => 'TODO_REWRITE', 'source' => 'admin/info_tur.php'],
        ];
    }

    public function items(string $search = '', int $limit = 60): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT id, name, tittle, category, uses, dress, delet, torg, elementary, battleuse, dopolnen
                  FROM items';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE id = :id_search OR name LIKE :search OR tittle LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $items = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $row['icon'] = $this->itemIconPath((int) ($row['id'] ?? 0));
            $items[] = $row;
        }
        return $items;
    }

    public function saveItem(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            $id = $this->nextItemId();
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $title = trim((string) ($payload['tittle'] ?? $payload['title'] ?? ''));
        if ($name === '') {
            return ['ok' => false, 'message' => 'Укажи название предмета.'];
        }

        $data = [
            'id' => $id,
            'cools' => max(0, (int) ($payload['cools'] ?? 0)),
            'name' => $name,
            'tittle' => $title,
            'category' => max(0, (int) ($payload['category'] ?? 0)),
            'uses' => (int) ($payload['uses'] ?? 0),
            'dress' => (int) ($payload['dress'] ?? 0),
            'delet' => (int) ($payload['delet'] ?? 0),
            'torg' => (int) ($payload['torg'] ?? 0),
            'elementary' => (int) ($payload['elementary'] ?? 0),
            'timesnapoke' => max(0, (int) ($payload['timesnapoke'] ?? 0)),
            'times' => trim((string) ($payload['times'] ?? '0')),
            'battleuse' => (int) ($payload['battleuse'] ?? 0),
            'dopolnen' => trim((string) ($payload['dopolnen'] ?? 'admin')),
        ];

        if ($this->itemExists($id)) {
            $stmt = $this->db->prepare(
                'UPDATE items
                    SET cools = :cools, name = :name, tittle = :tittle, category = :category,
                        uses = :uses, dress = :dress, delet = :delet, torg = :torg,
                        elementary = :elementary, timesnapoke = :timesnapoke, times = :times,
                        battleuse = :battleuse, dopolnen = :dopolnen
                  WHERE id = :id'
            );
            $stmt->execute($data);
            $action = 'item.update';
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO items
                    (id, cools, name, tittle, category, uses, dress, delet, torg, elementary, timesnapoke, times, battleuse, dopolnen)
                 VALUES
                    (:id, :cools, :name, :tittle, :category, :uses, :dress, :delet, :torg, :elementary, :timesnapoke, :times, :battleuse, :dopolnen)'
            );
            $stmt->execute($data);
            $action = 'item.create';
        }

        $iconFile = trim((string) ($payload['icon_file'] ?? ''));
        if ($iconFile !== '') {
            $this->setItemIcon($id, $iconFile);
        }

        $this->audit($adminId, $action, 'items', $id, $data);
        return ['ok' => true, 'message' => 'Предмет сохранен.', 'item' => $this->itemById($id)];
    }

    public function dropRules(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->db->query(
            'SELECT r.*, i.name AS item_name, b.title AS location_title,
                    pb.baseid AS pokebuild_baseid, p.Name AS pokemon_name, p.Code AS pokemon_code
               FROM admin_drop_rules r
          LEFT JOIN items i ON i.id = r.item_id
          LEFT JOIN build b ON b.id = r.location_id
          LEFT JOIN pokebuild pb ON pb.id = r.pokebuild_id
          LEFT JOIN pokemon p ON p.id = r.pokemon_base_id
              ORDER BY r.id DESC
              LIMIT ' . $limit
        );

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $row['item_icon'] = $this->itemIconPath((int) ($row['item_id'] ?? 0));
            $rows[] = $row;
        }
        return $rows;
    }

    public function saveDropRule(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        $itemId = (int) ($payload['item_id'] ?? 0);
        if ($itemId <= 0 || !$this->itemExists($itemId)) {
            return ['ok' => false, 'message' => 'Выбери существующий предмет.'];
        }

        $chance = (float) str_replace(',', '.', (string) ($payload['chance_percent'] ?? '0'));
        if ($chance <= 0 || $chance > 100) {
            return ['ok' => false, 'message' => 'Шанс должен быть больше 0 и не выше 100%.'];
        }

        $min = max(1, (int) ($payload['min_count'] ?? 1));
        $max = max($min, (int) ($payload['max_count'] ?? $min));
        $locationId = max(0, (int) ($payload['location_id'] ?? 0));
        $pokebuildId = max(0, (int) ($payload['pokebuild_id'] ?? 0));
        $pokemonBaseId = max(0, (int) ($payload['pokemon_base_id'] ?? 0));
        if ($locationId <= 0 && $pokebuildId <= 0 && $pokemonBaseId <= 0) {
            return ['ok' => false, 'message' => 'Укажи локацию, конкретную запись дикого покемона или базового покемона.'];
        }

        $data = [
            'item_id' => $itemId,
            'location_id' => $locationId,
            'pokebuild_id' => $pokebuildId,
            'pokemon_base_id' => $pokemonBaseId,
            'source_type' => $this->sourceType((string) ($payload['source_type'] ?? 'wild')),
            'chance_percent' => number_format($chance, 4, '.', ''),
            'min_count' => $min,
            'max_count' => $max,
            'time_start' => $this->normalizeTime((string) ($payload['time_start'] ?? '00:00')),
            'time_end' => $this->normalizeTime((string) ($payload['time_end'] ?? '23:59')),
            'quest_id' => max(0, (int) ($payload['quest_id'] ?? 0)),
            'quest_process' => max(0, (int) ($payload['quest_process'] ?? 0)),
            'quest_complete' => max(0, (int) ($payload['quest_complete'] ?? 0)),
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'note' => mb_substr(trim((string) ($payload['note'] ?? '')), 0, 255),
            'updated_at' => time(),
        ];

        if ($id > 0 && $this->dropRuleExists($id)) {
            $data['id'] = $id;
            $stmt = $this->db->prepare(
                'UPDATE admin_drop_rules
                    SET item_id = :item_id, location_id = :location_id, pokebuild_id = :pokebuild_id,
                        pokemon_base_id = :pokemon_base_id, source_type = :source_type,
                        chance_percent = :chance_percent, min_count = :min_count, max_count = :max_count,
                        time_start = :time_start, time_end = :time_end, quest_id = :quest_id,
                        quest_process = :quest_process, quest_complete = :quest_complete,
                        enabled = :enabled, note = :note, updated_at = :updated_at
                  WHERE id = :id'
            );
            $stmt->execute($data);
            $action = 'drop_rule.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = time();
            $stmt = $this->db->prepare(
                'INSERT INTO admin_drop_rules
                    (item_id, location_id, pokebuild_id, pokemon_base_id, source_type, chance_percent,
                     min_count, max_count, time_start, time_end, quest_id, quest_process, quest_complete,
                     enabled, note, created_by, created_at, updated_at)
                 VALUES
                    (:item_id, :location_id, :pokebuild_id, :pokemon_base_id, :source_type, :chance_percent,
                     :min_count, :max_count, :time_start, :time_end, :quest_id, :quest_process, :quest_complete,
                     :enabled, :note, :created_by, :created_at, :updated_at)'
            );
            $stmt->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'drop_rule.create';
        }

        $this->audit($adminId, $action, 'admin_drop_rules', $id, $data);
        return ['ok' => true, 'message' => 'Правило дропа сохранено.', 'ruleId' => $id];
    }

    public function deleteDropRule(int $adminId, int $id): array
    {
        if ($id <= 0 || !$this->dropRuleExists($id)) {
            return ['ok' => false, 'message' => 'Правило не найдено.'];
        }

        $stmt = $this->db->prepare('DELETE FROM admin_drop_rules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $this->audit($adminId, 'drop_rule.delete', 'admin_drop_rules', $id, []);
        return ['ok' => true, 'message' => 'Правило дропа удалено.'];
    }

    public function lookups(): array
    {
        return [
            'locations' => $this->lookupRows('SELECT id, title AS name FROM build ORDER BY title ASC LIMIT 300'),
            'pokemon' => $this->lookupRows('SELECT id, CONCAT(Code, " ", Name) AS name FROM pokemon ORDER BY id ASC LIMIT 1200'),
            'wildSlots' => $this->lookupRows(
                'SELECT pb.id, CONCAT("#", pb.id, " ", b.title, " - ", p.Code, " ", p.Name, " Lv.", pb.lvl) AS name
                   FROM pokebuild pb
              LEFT JOIN build b ON b.id = pb.building
              LEFT JOIN pokemon p ON p.id = pb.baseid
                  ORDER BY pb.id DESC
                  LIMIT 600'
            ),
        ];
    }

    private function itemById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $row['icon'] = $this->itemIconPath($id);
        return $row;
    }

    private function itemExists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function dropRuleExists(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM admin_drop_rules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function nextItemId(): int
    {
        return (int) ($this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM items')->fetchColumn() ?: 1);
    }

    private function itemIconPath(int $itemId): string
    {
        $index = $this->itemIconIndex();
        $file = (string) ($index[(string) $itemId] ?? '');
        if ($file !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . basename($file))) {
            return '/public/img/items/' . basename($file);
        }
        if (defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . $itemId . '.png')) {
            return '/public/img/items/' . $itemId . '.png';
        }
        return '/public/img/ui/menu-inventory.png';
    }

    private function setItemIcon(int $itemId, string $file): void
    {
        if (!defined('APP_ROOT')) {
            return;
        }
        $file = basename($file);
        if ($file === '' || !is_file(APP_ROOT . '/public/img/items/' . $file)) {
            return;
        }

        $path = APP_ROOT . '/public/img/items/index.json';
        $index = $this->itemIconIndex();
        $index[(string) $itemId] = $file;
        ksort($index, SORT_NATURAL);
        file_put_contents($path, json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
    }

    private function itemIconIndex(): array
    {
        static $index = null;
        if ($index !== null) {
            return $index;
        }
        $index = [];
        if (defined('APP_ROOT')) {
            $path = APP_ROOT . '/public/img/items/index.json';
            if (is_file($path)) {
                $decoded = json_decode((string) file_get_contents($path), true);
                if (is_array($decoded)) {
                    $index = $decoded;
                }
            }
        }
        return $index;
    }

    private function lookupRows(string $sql): array
    {
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function countTable(string $table, string $where = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $table . ($where !== '' ? ' WHERE ' . $where : '');
        return (int) ($this->db->query($sql)->fetchColumn() ?: 0);
    }

    private function normalizeTime(string $value): string
    {
        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }
        return '00:00:00';
    }

    private function sourceType(string $value): string
    {
        return in_array($value, ['wild', 'trainer', 'npc', 'event'], true) ? $value : 'wild';
    }

    private function audit(int $adminId, string $action, string $entity, int $entityId, array $payload): void
    {
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
