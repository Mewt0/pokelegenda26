<?php
declare(strict_types=1);

namespace Pokemon8\Repository;

use PDO;
use Throwable;

final class AdminRepository
{
    use AdminCommissionRepositoryTrait;

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
            'marketItems' => $this->countTable('market_shop_items'),
            'activeBattles' => $this->countTable('battles', 'pobeda = 0'),
            'auditRows' => $this->countTable('admin_audit_log'),
            'activeEvents' => $this->activeEventCount(),
            'tournaments' => $this->tableExists('admin_tournaments') ? $this->countTable('admin_tournaments') : 0,
            'medals' => $this->tableExists('admin_medals') ? $this->countTable('admin_medals') : 0,
            'commissionActiveLots' => $this->tableExists('market_lots') ? $this->countTable('market_lots', 'status = "active" AND expires_at > UNIX_TIMESTAMP()') : 0,
            'commissionPendingReturns' => $this->tableExists('market_return_storage') ? $this->countTable('market_return_storage', 'status = "pending"') : 0,
            'commissionRiskDeals' => $this->commissionRiskCount(),
            'safeStoragePending' => $this->tableExists('safe_storage_entries') ? $this->countTable('safe_storage_entries', 'status = "pending"') : 0,
            'safeRollbackOpen' => $this->tableExists('safe_operation_rollbacks') ? $this->countTable('safe_operation_rollbacks', 'status IN ("open", "failed")') : 0,
        ];
    }

    public function dashboard(): array
    {
        return [
            'overview' => $this->overview(),
            'onlineUsers' => $this->lookupRows(
                'SELECT id, login, buildmy, battleid, pve, pvp, karma_score, onlinetime
                   FROM users
                  WHERE online = 1 OR onlinetime >= UNIX_TIMESTAMP() - 900
                  ORDER BY onlinetime DESC
                  LIMIT 20'
            ),
            'recentAudit' => $this->auditRows(20),
            'recentBattles' => $this->lookupRows(
                'SELECT id, user_1, user_2, raund, pobeda, batl_tip
                   FROM battles
                  ORDER BY id DESC
                  LIMIT 20'
            ),
            'recentTournaments' => $this->tableExists('admin_tournaments')
                ? $this->lookupRows(
                    'SELECT t.id, t.title, t.status, t.starts_at, t.location_id, u.login AS curator_login
                       FROM admin_tournaments t
                  LEFT JOIN users u ON u.id = t.curator_user_id
                      ORDER BY t.id DESC
                      LIMIT 12'
                )
                : [],
            'activeEvents' => $this->activeEvents(),
            'recentMarketLogs' => $this->commissionLogs('', 12, 0, ['period' => '30d'])['rows'] ?? [],
            'commission' => $this->commissionDashboard(),
            'recentErrors' => $this->recentErrorLines(8),
            'settings' => $this->settings(),
        ];
    }

    public function legacyModules(string $search = ''): array
    {
        $rows = [
            $this->legacyModule('admin_dashboard', 'Главная старая админка', 'admin/admin.php', 'file', 'PARTIAL_NEW', 'dashboard', '/game/admin', '/api/admin/dashboard', 'admin_audit_log', 'Новый центр управления уже открывается отдельной страницей. Старый файл оставлен только как справочник.'),
            $this->legacyModule('news', 'Новости и оповещения', 'admin/bb_news_admin.php', 'file', 'PARTIAL_NEW', 'news', '/game/admin', '/api/admin/news', 'news', 'CRUD новостей перенесён в новый API и вкладку Новости.'),
            $this->legacyModule('pokemon_grant', 'Выдача покемонов', 'admin/poke.php', 'file', 'PARTIAL_NEW', 'pokemon', '/game/admin', '/api/admin/pokemon', 'pok_user', 'Выдача и правка покемонов перенесены в раздел Покемоны игроков.'),
            $this->legacyModule('attack_learn', 'Выдача атак и яйцевые атаки', 'admin/attak_pokes.php', 'file', 'PARTIAL_NEW', 'attacks', '/game/admin', '/api/admin/attacks', 'attac_poke', 'Привязка level-up и egg атак доступна в инспекторе вкладки Атаки.'),
            $this->legacyModule('items_grant', 'Выдача предметов', 'admin/gitem.php', 'file', 'PARTIAL_NEW', 'items', '/game/admin', '/api/admin/items', 'items_users', 'Выдача предметов работает, включая временные предметы через dattimer.'),
            $this->legacyModule('diamonds_grant', 'Выдача алмазов', 'admin/alm.php', 'file', 'SKIPPED_BY_DESIGN', 'users', '/game/admin', '/api/admin/users', 'items_users', 'Отдельную выдачу алмазов не переносим: экономические награды должны идти через награды, магазин и аудит.'),
            $this->legacyModule('bans', 'Баны и banip', 'admin/bans.php', 'file', 'PARTIAL_NEW', 'moderation', '/game/admin', '/api/admin/moderation', 'banip', 'Бан и разбан IP доступны из карточки игрока и модерации.'),
            $this->legacyModule('drop_rules', 'Предметы и дроп', 'items_drop', 'table', 'PARTIAL_NEW', 'drops', '/game/admin', '/api/admin/drop-rules', 'admin_drop_rules', 'Новая таблица правил дропа работает отдельно от legacy items_drop.'),
            $this->legacyModule('logs', 'Логи обмена, продаж и действий', 'admin/logs.php', 'file', 'PARTIAL_NEW', 'commission', '/game/admin', '/api/admin/commission/logs', 'market_logs/admin_audit_log', 'Продажи и подозрительные сделки смотрим во вкладке Комиссионная лавка, админ-действия остаются в audit.'),
            $this->legacyModule('locations', 'Локации и карта мира', 'include/data.world.php', 'file', 'PARTIAL_NEW', 'locations', '/game/admin', '/api/admin/locations', 'build', 'Локации редактируются через вкладку Локации, граф переходов читается из legacy data.world.php.'),
            $this->legacyModule('market', 'Покемаркет и товары', 'game.php?go=rinok', 'virtual', 'PARTIAL_NEW', 'market', '/game/market/items', '/api/market/items', 'market_shop_items', 'Игровой магазин и админский список товаров работают через новые API.'),
            $this->legacyModule('inventory', 'Инвентарь', 'game.php?go=items', 'virtual', 'PARTIAL_NEW', 'items', '/game/items', '/api/inventory/page', 'items_users', 'Инвентарь перенесён в новый overlay/API, legacy используется только как ориентир поведения.'),
            $this->legacyModule('pokedex', 'Покедекс', 'game.php?go=pokedex', 'virtual', 'PARTIAL_NEW', 'attacks', '/game/pokedex', '/api/dex/pokemon', 'pokemon', 'Покедекс работает через новые dex API.'),
            $this->legacyModule('attackdex', 'Атакадекс', 'game.php?go=atk', 'virtual', 'PARTIAL_NEW', 'attacks', '/game/attacks', '/api/dex/attacks', 'attac_power', 'Атаки грузятся постранично через новый API.'),
            $this->legacyModule('pve_battle', 'PvE бой', 'game.php?go=fight_pve', 'virtual', 'PARTIAL_NEW', 'dashboard', '/game/battle/pve', '/api/battle/pve/state', 'battles', 'Боёвка перенесена в новый battle dock, логика ещё расширяется.'),
            $this->legacyModule('pvp_battle', 'PvP бой', 'game.php?go=fight_pvp', 'virtual', 'PARTIAL_NEW', 'dashboard', '/game/battle/pvp', '/api/battle/pvp/status', 'pvp_battle_requests', 'Вызовы и принудительные бои вынесены в новые PvP API.'),
            $this->legacyModule('transport', 'Самолёт, пароход и транспорт', 'game.php?go=transport', 'virtual', 'PARTIAL_NEW', 'locations', '/game/transport', '/api/transport/routes', 'transport_routes', 'Транспорт подключён отдельными API и связан с локациями.'),
            $this->legacyModule('friends', 'Друзья и заявки', 'game.php?go=friends', 'virtual', 'DONE', 'users', '/game/friends', '/api/friends/status', 'friends', 'Базовая система друзей перенесена в новый API.'),
            $this->legacyModule('messages', 'Почта и сообщения', 'game.php?go=sends', 'virtual', 'PARTIAL_NEW', 'moderation', '/game/messages', '', 'chats', 'Страница есть, бизнес-логика почты ещё требует отдельного полного переноса.'),
            $this->legacyModule('tournaments', 'Турниры и медали', 'admin/info_tur.php, admin/info_tur_user.php, admin/medal.php', 'missing_file', 'PARTIAL_NEW', 'tournaments', '/game/admin', '/api/admin/tournaments', 'admin_tournaments', 'Старых файлов нет в дереве проекта, поэтому модуль переписывается с нуля. Старые упоминания в меню и NPC-кураторе используются только как источник бизнес-смысла.'),
        ];

        $rows = array_merge($rows, $this->legacySupportFiles(array_column($rows, 'source')));

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = array_values(array_filter($rows, static function (array $row) use ($needle): bool {
                return str_contains(mb_strtolower((string) $row['key']), $needle)
                    || str_contains(mb_strtolower((string) $row['title']), $needle)
                    || str_contains(mb_strtolower((string) $row['source']), $needle)
                    || str_contains(mb_strtolower((string) $row['status']), $needle)
                    || str_contains(mb_strtolower((string) $row['notes']), $needle);
            }));
        }

        usort($rows, static fn (array $a, array $b): int => [$a['status_order'], $a['title']] <=> [$b['status_order'], $b['title']]);
        return $rows;
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

        $before = $this->rowById('admin_drop_rules', 'id', $id);
        $this->audit($adminId, 'drop_rule.delete.before', 'admin_drop_rules', $id, ['before' => $before]);
        $stmt = $this->db->prepare('DELETE FROM admin_drop_rules WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $this->audit($adminId, 'drop_rule.delete', 'admin_drop_rules', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Правило дропа удалено.'];
    }

    public function wildSlots(string $search = '', int $limit = 120): array
    {
        $limit = max(1, min(300, $limit));
        $sql = 'SELECT pb.*, b.title AS location_title, CONCAT(p.Code, " ", p.Name) AS pokemon_name
                  FROM pokebuild pb
             LEFT JOIN build b ON b.id = pb.building
             LEFT JOIN pokemon p ON p.id = pb.baseid';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE pb.id = :id_search OR pb.building = :loc_search OR pb.baseid = :base_search OR b.title LIKE :search OR p.Name LIKE :search';
            $num = ctype_digit($search) ? (int) $search : -1;
            $params = [
                'id_search' => $num,
                'loc_search' => $num,
                'base_search' => $num,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY pb.building ASC, pb.id ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveWildSlot(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        $locationId = (int) ($payload['building'] ?? 0);
        $baseId = (int) ($payload['baseid'] ?? 0);
        if (!$this->rowById('build', 'id', $locationId)) {
            return ['ok' => false, 'message' => 'Локация не найдена.'];
        }
        if (!$this->rowById('pokemon', 'id', $baseId)) {
            return ['ok' => false, 'message' => 'Базовый покемон не найден.'];
        }

        $before = $id > 0 ? $this->rowById('pokebuild', 'id', $id) : [];
        $data = [
            'building' => $locationId,
            'baseid' => $baseId,
            'lvl' => max(1, min(100, (int) ($payload['lvl'] ?? 1))),
            'chance' => max(0, min(1000000, (int) ($payload['chance'] ?? 99))),
            'poimka' => max(0, (int) ($payload['poimka'] ?? 0)),
            'quest_id' => max(0, (int) ($payload['quest_id'] ?? 0)),
            'q_process' => max(0, (int) ($payload['q_process'] ?? 0)),
            'questupdate' => max(0, (int) ($payload['questupdate'] ?? 0)),
            'timeone' => $this->normalizeTime((string) ($payload['timeone'] ?? '00:00')),
            'timetwo' => $this->normalizeTime((string) ($payload['timetwo'] ?? '23:59')),
            'sprz' => max(0, (int) ($payload['sprz'] ?? 0)),
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE pokebuild
                    SET building = :building, baseid = :baseid, lvl = :lvl, chance = :chance,
                        poimka = :poimka, quest_id = :quest_id, q_process = :q_process,
                        questupdate = :questupdate, timeone = :timeone, timetwo = :timetwo, sprz = :sprz
                  WHERE id = :id'
            )->execute($data);
            $action = 'wild_slot.update';
        } else {
            $id = $this->nextTableId('pokebuild', 'id');
            $data['id'] = $id;
            $this->db->prepare(
                'INSERT INTO pokebuild
                    (id, building, baseid, lvl, chance, poimka, quest_id, q_process, questupdate, timeone, timetwo, sprz)
                 VALUES
                    (:id, :building, :baseid, :lvl, :chance, :poimka, :quest_id, :q_process, :questupdate, :timeone, :timetwo, :sprz)'
            )->execute($data);
            $action = 'wild_slot.create';
        }

        $this->audit($adminId, $action, 'pokebuild', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Дикий слот сохранен.', 'id' => $id];
    }

    public function deleteWildSlot(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('pokebuild', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Дикий слот не найден.'];
        }
        $this->audit($adminId, 'wild_slot.delete.before', 'pokebuild', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM pokebuild WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'wild_slot.delete', 'pokebuild', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Дикий слот удален.'];
    }

    public function lookups(string $type = '', string $query = ''): array
    {
        $type = trim($type);
        if ($type !== '') {
            return $this->lookupByType($type, $query);
        }

        $pokeBase = $this->formatPokeBaseLookupRows(
            $this->lookupRows('SELECT id, title AS name FROM poke_base ORDER BY id ASC LIMIT 1200')
        );

        return [
            'locations' => $this->lookupRows('SELECT id, title AS name FROM build ORDER BY title ASC LIMIT 300'),
            'pokemon' => $this->lookupRows('SELECT id, CONCAT(Code, " ", Name) AS name FROM pokemon ORDER BY id ASC LIMIT 1200'),
            'pokeBase' => $pokeBase,
            'attacks' => $this->lookupRows('SELECT atac_id AS id, atac_name AS name FROM attac_power ORDER BY atac_id ASC LIMIT 1200'),
            'items' => $this->lookupRows('SELECT id, name FROM items ORDER BY id ASC LIMIT 2000'),
            'users' => $this->lookupRows('SELECT id, login AS name FROM users ORDER BY id DESC LIMIT 600'),
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

    public function users(string $search = '', int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT u.id, u.login, u.email, u.groups, u.activation, u.moderation, u.police,
                       u.buildmy, u.mychat, u.battleid, u.pve, u.pvp, u.karma_score, u.rang_a, u.rang_b,
                       u.online, u.onlinetime, u.ip, COALESCE(iu.admins_panels, 0) AS admins_panels,
                       COALESCE(coins.count_sum, 0) AS coins, COALESCE(diamonds.count_sum, 0) AS diamonds
                  FROM users u
             LEFT JOIN information_users iu ON iu.users_id = u.id
             LEFT JOIN (SELECT user_id, SUM(count) AS count_sum FROM items_users WHERE item_id = 1 GROUP BY user_id) coins ON coins.user_id = u.id
             LEFT JOIN (SELECT user_id, SUM(count) AS count_sum FROM items_users WHERE item_id = 2 GROUP BY user_id) diamonds ON diamonds.user_id = u.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE u.id = :id_search OR u.login LIKE :search OR u.email LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY u.id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveUser(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        $before = $this->rowById('users', 'id', $id);
        if ($id <= 0 || !$before) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }

        $data = [
            'id' => $id,
            'groups' => (int) ($payload['groups'] ?? $before['groups']),
            'activation' => (int) ($payload['activation'] ?? $before['activation']),
            'moderation' => (int) ($payload['moderation'] ?? $before['moderation']),
            'police' => (int) ($payload['police'] ?? $before['police']),
            'buildmy' => (int) ($payload['buildmy'] ?? $before['buildmy']),
            'karma_score' => (int) ($payload['karma_score'] ?? $before['karma_score']),
            'rang_a' => (int) ($payload['rang_a'] ?? $before['rang_a']),
            'rang_b' => (int) ($payload['rang_b'] ?? $before['rang_b']),
            'battleid' => (int) ($payload['battleid'] ?? $before['battleid']),
            'pve' => (int) ($payload['pve'] ?? $before['pve']),
            'pvp' => (int) ($payload['pvp'] ?? $before['pvp']),
        ];

        $stmt = $this->db->prepare(
            'UPDATE users
                SET groups = :groups, activation = :activation, moderation = :moderation,
                    police = :police, buildmy = :buildmy, karma_score = :karma_score,
                    rang_a = :rang_a, rang_b = :rang_b, battleid = :battleid, pve = :pve, pvp = :pvp
              WHERE id = :id
              LIMIT 1'
        );
        $stmt->execute($data);

        $adminPanel = !empty($payload['admins_panels']) ? 1 : 0;
        $this->ensureInformationUser($id);
        $this->db->prepare('UPDATE information_users SET admins_panels = :value WHERE users_id = :user')
            ->execute(['value' => $adminPanel, 'user' => $id]);

        $this->audit($adminId, 'user.update', 'users', $id, ['before' => $before, 'after' => $data + ['admins_panels' => $adminPanel]]);
        return ['ok' => true, 'message' => 'Игрок сохранен.'];
    }

    public function banUser(int $adminId, array $payload): array
    {
        $userId = (int) ($payload['user_id'] ?? 0);
        $mode = (string) ($payload['mode'] ?? 'ban');
        $user = $this->rowById('users', 'id', $userId);
        if (!$user) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        $ip = (int) ($user['ip'] ?? 0);
        if ($ip <= 0) {
            return ['ok' => false, 'message' => 'У игрока нет IP для banip.'];
        }

        if ($mode === 'unban') {
            $beforeStmt = $this->db->prepare('SELECT * FROM banip WHERE ip = :ip');
            $beforeStmt->execute(['ip' => $ip]);
            $before = $beforeStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $this->db->prepare('DELETE FROM banip WHERE ip = :ip')->execute(['ip' => $ip]);
            $this->audit($adminId, 'banip.delete', 'banip', $ip, ['before' => $before, 'user_id' => $userId]);
            return ['ok' => true, 'message' => 'IP разбанен.'];
        }

        $exists = $this->db->prepare('SELECT 1 FROM banip WHERE ip = :ip LIMIT 1');
        $exists->execute(['ip' => $ip]);
        if (!$exists->fetchColumn()) {
            $this->db->prepare('INSERT INTO banip (id, ip, date) VALUES (:id, :ip, NOW())')
                ->execute(['id' => $this->nextTableId('banip', 'id'), 'ip' => $ip]);
        }
        $this->audit($adminId, 'banip.create', 'banip', $ip, ['user_id' => $userId, 'ip' => $ip]);
        return ['ok' => true, 'message' => 'IP забанен.'];
    }

    public function grantItem(int $adminId, array $payload): array
    {
        $userId = (int) ($payload['user_id'] ?? 0);
        $itemId = (int) ($payload['item_id'] ?? 0);
        $count = max(1, (int) ($payload['count'] ?? 1));
        if (!$this->rowById('users', 'id', $userId) || !$this->itemExists($itemId)) {
            return ['ok' => false, 'message' => 'Проверь игрока и предмет.'];
        }

        $expiresAt = $this->parseTemporaryItemExpiresAt($payload);
        if ($expiresAt === false) {
            return ['ok' => false, 'message' => 'Укажи будущую дату или количество секунд для временного предмета.'];
        }

        $rowId = $this->addItemToUser($userId, $itemId, $count, $expiresAt);
        $this->audit($adminId, 'item.grant', 'items_users', $rowId, [
            'user_id' => $userId,
            'item_id' => $itemId,
            'count' => $count,
            'temporary' => $expiresAt !== null,
            'expires_at' => $expiresAt,
        ]);
        return ['ok' => true, 'message' => $expiresAt === null ? 'Предмет выдан.' : 'Временный предмет выдан.'];
    }

    public function deleteItem(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->itemById($id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Предмет не найден.'];
        }
        $this->audit($adminId, 'item.delete.before', 'items', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM items WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'item.delete', 'items', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Предмет удален.'];
    }

    public function marketItems(string $search = '', int $limit = 100): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT m.*, i.name, i.tittle, ci.name AS currency_name
                  FROM market_shop_items m
             LEFT JOIN items i ON i.id = m.item_id
             LEFT JOIN items ci ON ci.id = m.currency_item_id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE m.item_id = :id_search OR i.name LIKE :search OR m.note LIKE :search';
            $params = ['id_search' => ctype_digit($search) ? (int) $search : -1, 'search' => '%' . $search . '%'];
        }
        $sql .= ' ORDER BY m.enabled DESC, m.sort_order ASC, m.item_id ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['icon'] = $this->itemIconPath((int) ($row['item_id'] ?? 0));
        }
        return $rows;
    }

    public function saveMarketItem(int $adminId, array $payload): array
    {
        $itemId = (int) ($payload['item_id'] ?? 0);
        if (!$this->itemExists($itemId)) {
            return ['ok' => false, 'message' => 'Предмет не найден.'];
        }
        $before = $this->marketItemByItemId($itemId);
        $data = [
            'item_id' => $itemId,
            'currency_item_id' => max(1, (int) ($payload['currency_item_id'] ?? 1)),
            'price' => max(0, (int) ($payload['price'] ?? 0)),
            'min_count' => max(1, (int) ($payload['min_count'] ?? 1)),
            'max_count' => max(1, (int) ($payload['max_count'] ?? 99)),
            'max_owned' => max(0, (int) ($payload['max_owned'] ?? 0)),
            'stock' => (int) ($payload['stock'] ?? -1),
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'note' => mb_substr(trim((string) ($payload['note'] ?? '')), 0, 255),
            'updated_at' => time(),
        ];
        $data['max_count'] = max($data['min_count'], $data['max_count']);
        if ($before) {
            $stmt = $this->db->prepare(
                'UPDATE market_shop_items
                    SET currency_item_id = :currency_item_id, price = :price, min_count = :min_count,
                        max_count = :max_count, max_owned = :max_owned, stock = :stock, enabled = :enabled,
                        sort_order = :sort_order, note = :note, updated_at = :updated_at
                  WHERE item_id = :item_id'
            );
            $stmt->execute($data);
            $action = 'market.update';
        } else {
            $data['created_at'] = time();
            $stmt = $this->db->prepare(
                'INSERT INTO market_shop_items
                    (item_id, currency_item_id, price, min_count, max_count, max_owned, stock, enabled, sort_order, note, created_at, updated_at)
                 VALUES
                    (:item_id, :currency_item_id, :price, :min_count, :max_count, :max_owned, :stock, :enabled, :sort_order, :note, :created_at, :updated_at)'
            );
            $stmt->execute($data);
            $action = 'market.create';
        }
        $this->audit($adminId, $action, 'market_shop_items', $itemId, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Товар сохранен.'];
    }

    public function deleteMarketItem(int $adminId, int $itemId): array
    {
        $before = $this->marketItemByItemId($itemId);
        if (!$before) {
            return ['ok' => false, 'message' => 'Товар не найден.'];
        }
        $this->audit($adminId, 'market.delete.before', 'market_shop_items', $itemId, ['before' => $before]);
        $this->db->prepare('DELETE FROM market_shop_items WHERE item_id = :item LIMIT 1')->execute(['item' => $itemId]);
        $this->audit($adminId, 'market.delete', 'market_shop_items', $itemId, ['before' => $before]);
        return ['ok' => true, 'message' => 'Товар удален из магазина.'];
    }

    public function locations(string $search = '', int $limit = 120): array
    {
        $limit = max(1, min(300, $limit));
        $sql = 'SELECT id, town, title, tipe, pve, zax FROM build';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE id = :id_search OR title LIKE :search';
            $params = ['id_search' => ctype_digit($search) ? (int) $search : -1, 'search' => '%' . $search . '%'];
        }
        $sql .= ' ORDER BY id ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveLocation(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            $id = $this->nextTableId('build', 'id');
        }
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название локации.'];
        }
        $before = $this->rowById('build', 'id', $id);
        $data = [
            'id' => $id,
            'town' => (int) ($payload['town'] ?? 0),
            'title' => $title,
            'tipe' => (int) ($payload['tipe'] ?? 0),
            'pve' => (int) ($payload['pve'] ?? 0),
            'zax' => (int) ($payload['zax'] ?? 0),
        ];
        if ($before) {
            $stmt = $this->db->prepare('UPDATE build SET town = :town, title = :title, tipe = :tipe, pve = :pve, zax = :zax WHERE id = :id');
            $stmt->execute($data);
            $action = 'location.update';
        } else {
            $stmt = $this->db->prepare('INSERT INTO build (id, town, title, tipe, pve, zax) VALUES (:id, :town, :title, :tipe, :pve, :zax)');
            $stmt->execute($data);
            $action = 'location.create';
        }
        $this->audit($adminId, $action, 'build', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Локация сохранена.'];
    }

    public function deleteLocation(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('build', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Локация не найдена.'];
        }
        $this->audit($adminId, 'location.delete.before', 'build', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM build WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'location.delete', 'build', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Локация удалена.'];
    }

    public function playerPokemon(string $search = '', int $limit = 80, int $offset = 0, array $filters = []): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        $sql = 'SELECT pu.id, pu.users, u.login, pu.basenum, pb.title AS base_name, pu.names, pu.lvl, pu.sex, pu.har, pu.active, pu.tips,
                       pu.item, ip.id_items AS equipped_item_id, held.name AS equipped_item_name,
                       pu.hp_my, pu.hp_max, pu.atk, pu.def, pu.satk, pu.sdef, pu.speed,
                       pu.hp_iv, pu.atk_iv, pu.def_iv, pu.satk_iv, pu.sdef_iv, pu.speed_iv,
                       pu.hp_ev, pu.atk_ev, pu.def_ev, pu.satk_ev, pu.sdef_ev, pu.speed_ev,
                       pu.training_stage, pu.training_stat, pu.training_named_effect, pu.training_tamed
                  FROM pok_user pu
             LEFT JOIN users u ON u.id = pu.users
             LEFT JOIN poke_base pb ON pb.id = pu.basenum
             LEFT JOIN items_poke ip ON ip.id_poke = pu.id
             LEFT JOIN items held ON held.id = ip.id_items';
        [$where, $params] = $this->pokemonFilterSql($search, $filters);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' ORDER BY pu.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['base_name'] = $this->cleanPokemonLookupName(
                (string) ($row['base_name'] ?? ''),
                (int) ($row['basenum'] ?? 0)
            );
            $row['names'] = $this->plainAdminText((string) ($row['names'] ?? ''));
            $row['equipped_item_name'] = $this->plainAdminText((string) ($row['equipped_item_name'] ?? ''));
        }
        unset($row);
        return $rows;
    }

    public function playerPokemonTotal(string $search = '', array $filters = []): int
    {
        [$where, $params] = $this->pokemonFilterSql($search, $filters);
        $sql = 'SELECT COUNT(DISTINCT pu.id)
                  FROM pok_user pu
             LEFT JOIN users u ON u.id = pu.users
             LEFT JOIN items_poke ip ON ip.id_poke = pu.id';
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function pokemonFilterSql(string $search, array $filters): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(pu.id = :id_search OR pu.users = :user_id_search OR pu.basenum = :base_id_search OR u.login LIKE :search_login OR pu.names LIKE :search_name)';
            $params['id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['user_id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['base_id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['search_login'] = '%' . $search . '%';
            $params['search_name'] = '%' . $search . '%';
        }
        foreach (['user_id' => 'pu.users', 'pokemon_id' => 'pu.id', 'base_id' => 'pu.basenum'] as $key => $column) {
            $value = (int) ($filters[$key] ?? 0);
            if ($value > 0) {
                $where[] = $column . ' = :' . $key;
                $params[$key] = $value;
            }
        }
        $login = trim((string) ($filters['login'] ?? ''));
        if ($login !== '') {
            $where[] = 'u.login LIKE :login_filter';
            $params['login_filter'] = '%' . $login . '%';
        }
        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $where[] = 'pu.names LIKE :pokemon_name_filter';
            $params['pokemon_name_filter'] = '%' . $name . '%';
        }
        $levelMin = (int) ($filters['level_min'] ?? 0);
        if ($levelMin > 0) {
            $where[] = 'pu.lvl >= :level_min';
            $params['level_min'] = $levelMin;
        }
        $levelMax = (int) ($filters['level_max'] ?? 0);
        if ($levelMax > 0) {
            $where[] = 'pu.lvl <= :level_max';
            $params['level_max'] = $levelMax;
        }
        $trainingStage = (string) ($filters['training_stage'] ?? '');
        if ($trainingStage !== '' && is_numeric($trainingStage)) {
            $where[] = 'pu.training_stage = :training_stage';
            $params['training_stage'] = (int) $trainingStage;
        }
        $trainingStat = trim((string) ($filters['training_stat'] ?? ''));
        if ($trainingStat !== '') {
            $where[] = 'pu.training_stat = :training_stat';
            $params['training_stat'] = $trainingStat;
        }
        $shiny = (string) ($filters['shiny'] ?? '');
        if ($shiny === '1') {
            $where[] = '(pu.tips LIKE "%shine%" OR pu.names LIKE "%Shiny%")';
        } elseif ($shiny === '0') {
            $where[] = '(pu.tips NOT LIKE "%shine%" AND pu.names NOT LIKE "%Shiny%")';
        }
        $active = (string) ($filters['active'] ?? '');
        if ($active !== '' && is_numeric($active)) {
            $where[] = 'pu.active = :active_filter';
            $params['active_filter'] = (int) $active;
        }
        $heldItemId = (int) ($filters['held_item_id'] ?? 0);
        if ($heldItemId > 0) {
            $where[] = '(pu.item = :held_item_id_pu OR ip.id_items = :held_item_id_ip)';
            $params['held_item_id_pu'] = $heldItemId;
            $params['held_item_id_ip'] = $heldItemId;
        }
        return [implode(' AND ', $where), $params];
    }

    public function savePlayerPokemon(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        $before = $this->rowById('pok_user', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Покемон не найден.'];
        }
        $data = [
            'id' => $id,
            'lvl' => max(1, min(100, (int) ($payload['lvl'] ?? $before['lvl']))),
            'active' => (int) ($payload['active'] ?? $before['active']),
            'hp_my' => max(0, (int) ($payload['hp_my'] ?? $before['hp_my'])),
            'hp_max' => max(1, (int) ($payload['hp_max'] ?? $before['hp_max'])),
            'training_stage' => max(0, min(6, (int) ($payload['training_stage'] ?? $before['training_stage']))),
            'training_stat' => trim((string) ($payload['training_stat'] ?? $before['training_stat'])),
            'training_named_effect' => trim((string) ($payload['training_named_effect'] ?? $before['training_named_effect'])),
            'training_tamed' => !empty($payload['training_tamed']) ? 1 : 0,
            'training_updated_at' => time(),
        ];
        $stmt = $this->db->prepare(
            'UPDATE pok_user
                SET lvl = :lvl, active = :active, hp_my = :hp_my, hp_max = :hp_max,
                    training_stage = :training_stage, training_stat = :training_stat,
                    training_named_effect = :training_named_effect, training_tamed = :training_tamed,
                    training_updated_at = :training_updated_at
              WHERE id = :id'
        );
        $stmt->execute($data);
        $this->audit($adminId, 'pokemon.update', 'pok_user', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Покемон сохранен.'];
    }

    public function grantPokemon(int $adminId, array $payload): array
    {
        $user = $this->resolveUserForGrant($payload);
        if (!$user) {
            return ['ok' => false, 'message' => 'Игрок не найден. Укажи user_id или ник.'];
        }

        $base = $this->resolvePokemonBaseForGrant($payload);
        if (!$base) {
            return ['ok' => false, 'message' => 'Базовый покемон не найден. Укажи base_id, номер или имя.'];
        }

        $userId = (int) $user['id'];
        $baseId = (int) $base['id'];
        $level = max(1, min(100, (int) ($payload['lvl'] ?? 5)));
        $shiny = !empty($payload['shiny']);
        $sex = max(1, min(2, (int) ($payload['sex'] ?? 1)));
        $har = max(1, (int) ($payload['har'] ?? 1));
        $nature = $this->rowById('har', 'id_har', $har) ?: ['atk' => 1, 'def' => 1, 'satk' => 1, 'sdef' => 1, 'speed' => 1];
        $iv = [
            'hp' => $this->grantInt($payload, 'hp_iv', 1, 0, 9999),
            'atk' => $this->grantInt($payload, 'atk_iv', 1, 0, 9999),
            'def' => $this->grantInt($payload, 'def_iv', 1, 0, 9999),
            'satk' => $this->grantInt($payload, 'satk_iv', 1, 0, 9999),
            'sdef' => $this->grantInt($payload, 'sdef_iv', 1, 0, 9999),
            'speed' => $this->grantInt($payload, 'speed_iv', 1, 0, 9999),
        ];
        $ev = [
            'hp' => $this->grantInt($payload, 'hp_ev', 0, 0, 252),
            'atk' => $this->grantInt($payload, 'atk_ev', 0, 0, 252),
            'def' => $this->grantInt($payload, 'def_ev', 0, 0, 252),
            'satk' => $this->grantInt($payload, 'satk_ev', 0, 0, 252),
            'sdef' => $this->grantInt($payload, 'sdef_ev', 0, 0, 252),
            'speed' => $this->grantInt($payload, 'speed_ev', 0, 0, 252),
        ];
        $evTotal = array_sum($ev);
        if ($evTotal > 510) {
            return ['ok' => false, 'message' => sprintf('Сумма EV не может быть больше 510. Сейчас указано %d.', $evTotal)];
        }
        $calcStat = static function (int $baseValue, int $ivValue, int $evValue, float $natureValue, int $level): int {
            return max(1, (int) round(((($ivValue + $baseValue * 2 + (int) floor($evValue / 4)) * $level / 100) + 5) * max(0.1, $natureValue)));
        };
        $calculated = [
            'hp' => max(1, (int) round((($iv['hp'] + ((int) $base['hp'] * 2) + (int) floor($ev['hp'] / 4) + 100) * $level / 100) + 10)),
            'atk' => $calcStat((int) $base['atk'], $iv['atk'], $ev['atk'], (float) $nature['atk'], $level),
            'def' => $calcStat((int) $base['def'], $iv['def'], $ev['def'], (float) $nature['def'], $level),
            'satk' => $calcStat((int) $base['satk'], $iv['satk'], $ev['satk'], (float) $nature['satk'], $level),
            'sdef' => $calcStat((int) $base['sdef'], $iv['sdef'], $ev['sdef'], (float) $nature['sdef'], $level),
            'speed' => $calcStat((int) $base['speed'], $iv['speed'], $ev['speed'], (float) $nature['speed'], $level),
        ];
        $allStats = $this->grantInt($payload, 'stat_all', 0, 0, 9999);
        $stats = [
            'hp' => $this->grantInt($payload, 'stat_hp', $allStats > 0 ? $allStats : $calculated['hp'], 1, 9999),
            'atk' => $this->grantInt($payload, 'stat_atk', $allStats > 0 ? $allStats : $calculated['atk'], 1, 9999),
            'def' => $this->grantInt($payload, 'stat_def', $allStats > 0 ? $allStats : $calculated['def'], 1, 9999),
            'satk' => $this->grantInt($payload, 'stat_satk', $allStats > 0 ? $allStats : $calculated['satk'], 1, 9999),
            'sdef' => $this->grantInt($payload, 'stat_sdef', $allStats > 0 ? $allStats : $calculated['sdef'], 1, 9999),
            'speed' => $this->grantInt($payload, 'stat_speed', $allStats > 0 ? $allStats : $calculated['speed'], 1, 9999),
        ];
        $hpCurrent = $this->grantInt($payload, 'hp_my', $stats['hp'], 0, $stats['hp']);
        $tips = $shiny ? 'shine' : mb_substr(trim((string) ($payload['tips'] ?? 'normal')), 0, 10);
        if ($tips === '') {
            $tips = 'normal';
        }
        $pokemonId = $this->nextTableId('pok_user', 'id');
        $name = $this->cleanPokemonGrantName($base, $baseId) . ($shiny ? ' - Shiny' : '');

        $startedTransaction = !$this->db->inTransaction();
        if ($startedTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO pok_user
                    (id, users, basenum, names, active, evcount, lvl, sex, har, hp_my, hp_max, exp, exp_b,
                     atk, def, satk, sdef, speed, hp_ev, atk_ev, def_ev, satk_ev, sdef_ev, speed_ev,
                     hp_iv, atk_iv, def_iv, satk_iv, sdef_iv, speed_iv, tips, startone, startepoke,
                     reproduction, happy, datemay, usersone, sprz, item, ability_key)
                 VALUES
                    (?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, 0, 100,
                     ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                     ?, ?, ?, ?, ?, ?, ?, 0, 0,
                     0, 0, NOW(), ?, 0, 0, ?)'
            );
            $stmt->execute([
                $pokemonId,
                $userId,
                $baseId,
                $name,
                array_sum($ev),
                $level,
                $sex,
                $har,
                $hpCurrent,
                $stats['hp'],
                $stats['atk'],
                $stats['def'],
                $stats['satk'],
                $stats['sdef'],
                $stats['speed'],
                $ev['hp'],
                $ev['atk'],
                $ev['def'],
                $ev['satk'],
                $ev['sdef'],
                $ev['speed'],
                $iv['hp'],
                $iv['atk'],
                $iv['def'],
                $iv['satk'],
                $iv['sdef'],
                $iv['speed'],
                $tips,
                $userId,
                $base['ability_key'] ?? null,
            ]);
            $seededMoves = $this->seedPokemonMoves($pokemonId, $baseId, $level);
            $this->audit($adminId, 'pokemon.grant', 'pok_user', $pokemonId, [
                'user_id' => $userId,
                'user_login' => $user['login'] ?? '',
                'base_id' => $baseId,
                'pokemon_name' => $name,
                'level' => $level,
                'shiny' => $shiny,
                'sex' => $sex,
                'har' => $har,
                'tips' => $tips,
                'iv' => $iv,
                'ev' => $ev,
                'all_stats' => $allStats,
                'stats' => $stats,
                'moves' => $seededMoves,
            ]);
            if ($startedTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'message' => 'Не удалось выдать покемона: ' . $e->getMessage()];
        }

        return [
            'ok' => true,
            'message' => $this->pokemonGrantMessage(
                $name,
                (string) ($user['login'] ?? ('#' . $userId)),
                $userId,
                $baseId,
                $pokemonId,
                $level,
                $shiny,
                $iv,
                $ev,
                $stats,
                $hpCurrent,
                $seededMoves,
                $this->natureNameForGrant($har)
            ),
            'pokemon_id' => $pokemonId,
            'base_id' => $baseId,
            'moves' => $seededMoves,
            'iv' => $iv,
            'ev' => $ev,
            'stats' => $stats,
        ];
    }

    private function pokemonGrantMessage(
        string $name,
        string $login,
        int $userId,
        int $baseId,
        int $pokemonId,
        int $level,
        bool $shiny,
        array $iv,
        array $ev,
        array $stats,
        int $hpCurrent,
        array $moves,
        string $natureName
    ): string
    {
        $startMove = $moves[0] ?? null;
        $startText = $startMove !== null && (int) ($startMove['id'] ?? 0) > 0
            ? sprintf('%s #%d', (string) ($startMove['name'] ?? 'атака'), (int) $startMove['id'])
            : 'не найдена';
        $warning = $startMove !== null && (int) ($startMove['id'] ?? 0) > 0
            ? ''
            : ' Внимание: стартовая атака не найдена, проверь learnset этого вида.';

        return sprintf(
            'Покемон #%d %s Lv.%d выдан игроку #%d %s. Pokemon ID созданной записи: #%d. Shiny: %s. Характер: %s. Гены/IV: HP %d / Atk %d / Def %d / SAtk %d / SDef %d / Speed %d. EV: HP %d / Atk %d / Def %d / SAtk %d / SDef %d / Speed %d (сумма %d). Стартовая атака: %s.%s HP %d/%d, статы: Atk %d, Def %d, SAtk %d, SDef %d, Speed %d.',
            $baseId,
            $name,
            $level,
            $userId,
            $login,
            $pokemonId,
            $shiny ? 'да' : 'нет',
            $natureName,
            (int) $iv['hp'],
            (int) $iv['atk'],
            (int) $iv['def'],
            (int) $iv['satk'],
            (int) $iv['sdef'],
            (int) $iv['speed'],
            (int) $ev['hp'],
            (int) $ev['atk'],
            (int) $ev['def'],
            (int) $ev['satk'],
            (int) $ev['sdef'],
            (int) $ev['speed'],
            array_sum($ev),
            $startText,
            $warning,
            $hpCurrent,
            (int) $stats['hp'],
            (int) $stats['atk'],
            (int) $stats['def'],
            (int) $stats['satk'],
            (int) $stats['sdef'],
            (int) $stats['speed'],
        );
    }

    private function resolveUserForGrant(array $payload): ?array
    {
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($userId > 0) {
            return $this->rowById('users', 'id', $userId);
        }

        $query = trim((string) ($payload['user'] ?? $payload['login'] ?? $payload['user_login'] ?? ''));
        if ($query === '') {
            return null;
        }

        if (ctype_digit($query)) {
            return $this->rowById('users', 'id', (int) $query);
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE login = :login LIMIT 1');
        $stmt->execute(['login' => $query]);
        $exact = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($exact) {
            return $exact;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE login LIKE :query ORDER BY id ASC LIMIT 1');
        $stmt->execute(['query' => '%' . $query . '%']);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function resolvePokemonBaseForGrant(array $payload): ?array
    {
        $baseId = (int) ($payload['base_id'] ?? $payload['poke_base_id'] ?? 0);
        if ($baseId > 0) {
            return $this->rowById('poke_base', 'id', $baseId);
        }

        $query = trim((string) ($payload['base'] ?? $payload['pokemon'] ?? $payload['pokemon_name'] ?? ''));
        if ($query === '') {
            return null;
        }

        if (ctype_digit($query)) {
            return $this->rowById('poke_base', 'id', (int) $query);
        }

        $like = '%' . $query . '%';
        $stmt = $this->db->prepare(
            'SELECT pb.*
               FROM poke_base pb
          LEFT JOIN pokemon p ON p.id = pb.id
              WHERE pb.title LIKE :title
                 OR p.Name LIKE :name
                 OR p.Code LIKE :code
              ORDER BY pb.id ASC
              LIMIT 1'
        );
        $stmt->execute([
            'title' => $like,
            'name' => $like,
            'code' => $like,
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function cleanPokemonGrantName(array $base, int $baseId): string
    {
        $title = $this->plainAdminText((string) ($base['title'] ?? ''));
        if ($title !== '') {
            $title = preg_replace('/^#?0*' . $baseId . '\s*/', '', $title) ?: $title;
            $title = preg_replace('/^#?0*\d+\s*/', '', $title) ?: $title;
            $title = trim($title);
        }
        return $title !== '' ? $title : ('Pokemon #' . $baseId);
    }

    private function cleanPokemonLookupName(string $title, int $baseId): string
    {
        $title = $this->plainAdminText($title);
        if ($title !== '') {
            $title = preg_replace('/^#?0*' . $baseId . '\s*/u', '', $title) ?: $title;
            $title = preg_replace('/^#?0*\d+\s*/u', '', $title) ?: $title;
            $title = trim($title);
        }
        return $title !== '' ? $title : ('Pokemon #' . $baseId);
    }

    /** @param array<int,array<string,mixed>> $rows */
    private function formatPokeBaseLookupRows(array $rows): array
    {
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $name = $this->cleanPokemonLookupName((string) ($row['name'] ?? $row['title'] ?? ''), $id);
            $row['name'] = $name;
            $row['label'] = '#' . $id . ' ' . $name;
        }
        unset($row);
        return $rows;
    }

    private function natureNameForGrant(int $id): string
    {
        foreach ($this->lookupNatures($id, 1) as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return (string) ($row['name'] ?? ('Характер #' . $id));
            }
        }
        return 'Характер #' . $id;
    }

    private function plainAdminText(string $value): string
    {
        return trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function grantInt(array $payload, string $key, int $default, int $min, int $max): int
    {
        $raw = trim((string) ($payload[$key] ?? ''));
        if ($raw === '' || !is_numeric($raw)) {
            return max($min, min($max, $default));
        }

        return max($min, min($max, (int) $raw));
    }

    public function deletePlayerPokemon(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('pok_user', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Покемон не найден.'];
        }
        $this->audit($adminId, 'pokemon.delete.before', 'pok_user', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM pok_user WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'pokemon.delete', 'pok_user', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Покемон удален.'];
    }

    public function attacks(string $search = '', int $limit = 100, int $offset = 0, array $filters = []): array
    {
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);
        $sql = 'SELECT atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy, priorety, atac_tittle, tittle_effect
                  FROM attac_power';
        [$where, $params] = $this->attackFilterSql($search, $filters);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' ORDER BY atac_id ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function attacksTotal(string $search = '', array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM attac_power';
        [$where, $params] = $this->attackFilterSql($search, $filters);
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private function attackFilterSql(string $search, array $filters): array
    {
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(atac_id = :id_search OR atac_name LIKE :search_name OR atac_tip LIKE :search_type OR atac_tittle LIKE :search_title OR tittle_effect LIKE :search_effect)';
            $params['id_search'] = ctype_digit($search) ? (int) $search : -1;
            $params['search_name'] = '%' . $search . '%';
            $params['search_type'] = '%' . $search . '%';
            $params['search_title'] = '%' . $search . '%';
            $params['search_effect'] = '%' . $search . '%';
        }
        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '') {
            $where[] = 'atac_tip LIKE :type_filter';
            $params['type_filter'] = '%' . $type . '%';
        }
        $effect = trim((string) ($filters['effect'] ?? ''));
        if ($effect !== '') {
            $where[] = '(atac_tittle LIKE :effect_filter_title OR tittle_effect LIKE :effect_filter_text OR dop_effect LIKE :effect_filter_dop)';
            $params['effect_filter_title'] = '%' . $effect . '%';
            $params['effect_filter_text'] = '%' . $effect . '%';
            $params['effect_filter_dop'] = '%' . $effect . '%';
        }
        foreach ([
            'category' => ['atac_categori', '='],
            'power_min' => ['atac_power', '>='],
            'power_max' => ['atac_power', '<='],
            'accuracy_min' => ['atac_accuracy', '>='],
            'accuracy_max' => ['atac_accuracy', '<='],
            'pp_min' => ['atac_pp', '>='],
            'pp_max' => ['atac_pp', '<='],
        ] as $key => [$column, $op]) {
            $value = (string) ($filters[$key] ?? '');
            if ($value !== '' && is_numeric($value)) {
                $where[] = $column . ' ' . $op . ' :' . $key;
                $params[$key] = (int) $value;
            }
        }
        return [implode(' AND ', $where), $params];
    }

    public function saveAttack(int $adminId, array $payload): array
    {
        $id = (int) ($payload['atac_id'] ?? 0);
        if ($id <= 0) {
            $id = $this->nextTableId('attac_power', 'atac_id');
        }
        $name = trim((string) ($payload['atac_name'] ?? ''));
        if ($name === '') {
            return ['ok' => false, 'message' => 'Укажи название атаки.'];
        }
        $before = $this->rowById('attac_power', 'atac_id', $id);
        $data = [
            'atac_id' => $id,
            'atac_name' => $name,
            'atac_tip' => trim((string) ($payload['atac_tip'] ?? 'Normal')),
            'atac_categori' => (int) ($payload['atac_categori'] ?? 1),
            'atac_pp' => max(0, (int) ($payload['atac_pp'] ?? 0)),
            'atac_power' => max(0, (int) ($payload['atac_power'] ?? 0)),
            'atac_accuracy' => max(0, (int) ($payload['atac_accuracy'] ?? 100)),
            'priorety' => (int) ($payload['priorety'] ?? 0),
            'atac_tittle' => trim((string) ($payload['atac_tittle'] ?? '')),
            'tittle_effect' => trim((string) ($payload['tittle_effect'] ?? '')),
        ];
        if ($before) {
            $stmt = $this->db->prepare(
                'UPDATE attac_power
                    SET atac_name = :atac_name, atac_tip = :atac_tip, atac_categori = :atac_categori,
                        atac_pp = :atac_pp, atac_power = :atac_power, atac_accuracy = :atac_accuracy,
                        priorety = :priorety, atac_tittle = :atac_tittle, tittle_effect = :tittle_effect
                  WHERE atac_id = :atac_id'
            );
            $stmt->execute($data);
            $action = 'attack.update';
        } else {
            $stmt = $this->db->prepare(
                'INSERT INTO attac_power
                    (atac_id, atac_name, atac_tip, atac_categori, atac_pp, atac_power, atac_accuracy,
                     atac_goal, atac_tittle, tittle_effect, atac_not, critic, chans_dop, chans_effect,
                     cool_attak, cool_attak2, dop_effect, priorety, stati, attac_effecti, titles)
                 VALUES
                    (:atac_id, :atac_name, :atac_tip, :atac_categori, :atac_pp, :atac_power, :atac_accuracy,
                     1, :atac_tittle, :tittle_effect, 0, "3", 0, 0, 0, 0, "0", :priorety, 0, 0, "")'
            );
            $stmt->execute($data);
            $action = 'attack.create';
        }
        $this->audit($adminId, $action, 'attac_power', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Атака сохранена.'];
    }

    public function saveAttackLearn(int $adminId, array $payload): array
    {
        $kind = (string) ($payload['kind'] ?? 'level');
        $baseId = (int) ($payload['poke_base_id'] ?? 0);
        $attackId = (int) ($payload['atac_id'] ?? 0);
        $level = max(1, (int) ($payload['atc_lvl'] ?? 1));
        if (!$this->rowById('poke_base', 'id', $baseId) || !$this->rowById('attac_power', 'atac_id', $attackId)) {
            return ['ok' => false, 'message' => 'Проверь покемона и атаку.'];
        }
        if ($kind === 'egg') {
            $exists = $this->db->prepare('SELECT id FROM attac_egg WHERE poke_base_id = :base AND atac_id = :attack LIMIT 1');
            $exists->execute(['base' => $baseId, 'attack' => $attackId]);
            if (!$exists->fetchColumn()) {
                $this->db->prepare('INSERT INTO attac_egg (id, poke_base_id, atac_id) VALUES (:id, :base, :attack)')
                    ->execute(['id' => $this->nextTableId('attac_egg', 'id'), 'base' => $baseId, 'attack' => $attackId]);
            }
            $this->audit($adminId, 'attack.egg.save', 'attac_egg', $baseId, $payload);
            return ['ok' => true, 'message' => 'Яйцевая атака сохранена.'];
        }
        $exists = $this->db->prepare('SELECT id_structure FROM attac_poke WHERE poke_base_id = :base AND atac_id = :attack AND atc_lvl = :lvl LIMIT 1');
        $exists->execute(['base' => $baseId, 'attack' => $attackId, 'lvl' => $level]);
        if (!$exists->fetchColumn()) {
            $this->db->prepare('INSERT INTO attac_poke (id_structure, atac_id, poke_base_id, atc_lvl) VALUES (:id, :attack, :base, :lvl)')
                ->execute(['id' => $this->nextTableId('attac_poke', 'id_structure'), 'attack' => $attackId, 'base' => $baseId, 'lvl' => $level]);
        }
        $this->audit($adminId, 'attack.learn.save', 'attac_poke', $baseId, $payload);
        return ['ok' => true, 'message' => 'Атака по уровню сохранена.'];
    }

    public function deleteAttack(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('attac_power', 'atac_id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Атака не найдена.'];
        }
        $this->audit($adminId, 'attack.delete.before', 'attac_power', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM attac_power WHERE atac_id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'attack.delete', 'attac_power', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Атака удалена.'];
    }

    public function news(string $search = '', int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT id, subject, author, date, link, opis, news FROM news';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE id = :id_search OR subject LIKE :search OR author LIKE :search OR news LIKE :search';
            $params = ['id_search' => ctype_digit($search) ? (int) $search : -1, 'search' => '%' . $search . '%'];
        }
        $sql .= ' ORDER BY id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveNews(int $adminId, array $payload): array
    {
        $id = (int) ($payload['id'] ?? 0);
        if ($id <= 0) {
            $id = $this->nextTableId('news', 'id');
        }
        $subject = trim((string) ($payload['subject'] ?? ''));
        $text = trim((string) ($payload['news'] ?? ''));
        if ($subject === '' || $text === '') {
            return ['ok' => false, 'message' => 'Укажи тему и текст новости.'];
        }
        $before = $this->rowById('news', 'id', $id);
        $data = [
            'id' => $id,
            'subject' => $subject,
            'news' => $text,
            'date' => trim((string) ($payload['date'] ?? date('Y-m-d'))) ?: date('Y-m-d'),
            'author' => trim((string) ($payload['author'] ?? 'Admin')),
            'link' => trim((string) ($payload['link'] ?? '')),
            'opis' => trim((string) ($payload['opis'] ?? '')),
        ];
        if ($before) {
            $stmt = $this->db->prepare('UPDATE news SET subject = :subject, news = :news, date = :date, author = :author, link = :link, opis = :opis WHERE id = :id');
            $stmt->execute($data);
            $action = 'news.update';
        } else {
            $stmt = $this->db->prepare('INSERT INTO news (id, subject, news, date, author, link, opis) VALUES (:id, :subject, :news, :date, :author, :link, :opis)');
            $stmt->execute($data);
            $action = 'news.create';
        }
        $this->audit($adminId, $action, 'news', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Новость сохранена.'];
    }

    public function deleteNews(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('news', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Новость не найдена.'];
        }
        $this->audit($adminId, 'news.delete.before', 'news', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM news WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'news.delete', 'news', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Новость удалена.'];
    }

    public function events(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $sql = 'SELECT * FROM game_event_boosts';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE id = :id_search OR title LIKE :search OR boost_key LIKE :search OR scope LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' ORDER BY enabled DESC, starts_at DESC, id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['boost_label'] = $this->boostLabel((string) ($row['boost_key'] ?? ''));
            $row['scope_label'] = $this->boostScopeLabel((string) ($row['scope'] ?? ''));
            $row['status_label'] = $this->eventStatusLabel($row);
        }

        return $rows;
    }

    public function saveEvent(int $adminId, array $payload): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return ['ok' => false, 'message' => 'Миграция game_event_boosts еще не применена.'];
        }

        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        $boostKey = $this->boostKey((string) ($payload['boost_key'] ?? 'exp'));
        $multiplier = (float) str_replace(',', '.', (string) ($payload['multiplier'] ?? '1'));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название ивента.'];
        }
        if ($multiplier < 1 || $multiplier > 10) {
            return ['ok' => false, 'message' => 'Множитель должен быть от x1 до x10.'];
        }

        $now = time();
        $before = $id > 0 ? $this->rowById('game_event_boosts', 'id', $id) : [];
        $startsAt = $this->parseTimestamp($payload['starts_at'] ?? 0);
        $endsAt = $this->parseTimestamp($payload['ends_at'] ?? 0);
        if ($startsAt > 0 && $endsAt > 0 && $endsAt <= $startsAt) {
            return ['ok' => false, 'message' => 'Дата окончания должна быть позже старта.'];
        }

        $data = [
            'title' => $title,
            'boost_key' => $boostKey,
            'multiplier' => number_format($multiplier, 2, '.', ''),
            'scope' => $this->boostScope((string) ($payload['scope'] ?? 'global')),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'note' => mb_substr(trim((string) ($payload['note'] ?? '')), 0, 255),
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE game_event_boosts
                    SET title = :title, boost_key = :boost_key, multiplier = :multiplier,
                        scope = :scope, starts_at = :starts_at, ends_at = :ends_at,
                        enabled = :enabled, note = :note, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'event.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO game_event_boosts
                    (title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note, created_by, created_at, updated_at)
                 VALUES
                    (:title, :boost_key, :multiplier, :scope, :starts_at, :ends_at, :enabled, :note, :created_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'event.create';
        }

        $this->audit($adminId, $action, 'game_event_boosts', $id, ['before' => $before, 'after' => $data]);
        return [
            'ok' => true,
            'message' => 'Ивент сохранен: ' . $this->boostLabel($boostKey) . ' x' . number_format($multiplier, 2, '.', '') . '.',
            'id' => $id,
        ];
    }

    public function deleteEvent(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('game_event_boosts', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Ивент не найден.'];
        }
        $this->audit($adminId, 'event.delete.before', 'game_event_boosts', $id, ['before' => $before]);
        $this->db->prepare('DELETE FROM game_event_boosts WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'event.delete', 'game_event_boosts', $id, ['before' => $before]);
        return ['ok' => true, 'message' => 'Ивент удален.'];
    }

    public function tournaments(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('admin_tournaments')) {
            return [];
        }

        $limit = max(1, min(200, $limit));
        $sql = 'SELECT t.*, u.login AS curator_login, b.title AS location_name,
                       COUNT(p.id) AS participants_count
                  FROM admin_tournaments t
             LEFT JOIN users u ON u.id = t.curator_user_id
             LEFT JOIN build b ON b.id = t.location_id
             LEFT JOIN admin_tournament_participants p ON p.tournament_id = t.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE t.id = :id_search OR t.title LIKE :search OR t.status LIKE :search OR u.login LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' GROUP BY t.id ORDER BY t.id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function saveTournament(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_tournaments')) {
            return ['ok' => false, 'message' => 'Миграция турниров еще не применена.'];
        }

        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название турнира.'];
        }

        $status = $this->tournamentStatus((string) ($payload['status'] ?? 'draft'));
        $now = time();
        $before = $id > 0 ? $this->rowById('admin_tournaments', 'id', $id) : [];
        $data = [
            'legacy_id' => max(0, (int) ($payload['legacy_id'] ?? 0)),
            'title' => $title,
            'status' => $status,
            'starts_at' => $this->adminTimestamp((string) ($payload['starts_at'] ?? '')),
            'ends_at' => $this->adminTimestamp((string) ($payload['ends_at'] ?? '')),
            'entry_fee_item_id' => max(0, (int) ($payload['entry_fee_item_id'] ?? 1)),
            'entry_fee_amount' => max(0, (int) ($payload['entry_fee_amount'] ?? 0)),
            'location_id' => max(0, (int) ($payload['location_id'] ?? 40)),
            'curator_user_id' => max(0, (int) ($payload['curator_user_id'] ?? 0)),
            'min_level' => max(1, (int) ($payload['min_level'] ?? 1)),
            'max_level' => max(1, (int) ($payload['max_level'] ?? 100)),
            'max_participants' => max(0, (int) ($payload['max_participants'] ?? 0)),
            'rules' => trim((string) ($payload['rules'] ?? '')),
            'reward_note' => trim((string) ($payload['reward_note'] ?? '')),
            'updated_by' => $adminId,
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE admin_tournaments
                    SET legacy_id = :legacy_id, title = :title, status = :status,
                        starts_at = :starts_at, ends_at = :ends_at,
                        entry_fee_item_id = :entry_fee_item_id, entry_fee_amount = :entry_fee_amount,
                        location_id = :location_id, curator_user_id = :curator_user_id,
                        min_level = :min_level, max_level = :max_level, max_participants = :max_participants,
                        rules = :rules, reward_note = :reward_note, updated_by = :updated_by, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'tournament.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO admin_tournaments
                    (legacy_id, title, status, starts_at, ends_at, entry_fee_item_id, entry_fee_amount,
                     location_id, curator_user_id, min_level, max_level, max_participants,
                     rules, reward_note, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:legacy_id, :title, :status, :starts_at, :ends_at, :entry_fee_item_id, :entry_fee_amount,
                     :location_id, :curator_user_id, :min_level, :max_level, :max_participants,
                     :rules, :reward_note, :created_by, :updated_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'tournament.create';
        }

        $this->audit($adminId, $action, 'admin_tournaments', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Турнир сохранен.', 'id' => $id];
    }

    public function deleteTournament(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('admin_tournaments', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }
        $participants = $this->tableExists('admin_tournament_participants')
            ? $this->lookupRows('SELECT * FROM admin_tournament_participants WHERE tournament_id = ' . (int) $id)
            : [];
        $this->audit($adminId, 'tournament.delete.before', 'admin_tournaments', $id, ['before' => $before, 'participants' => $participants]);
        $this->db->prepare('DELETE FROM admin_tournament_participants WHERE tournament_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM admin_tournaments WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'tournament.delete', 'admin_tournaments', $id, ['before' => $before, 'participants' => $participants]);
        return ['ok' => true, 'message' => 'Турнир удален.'];
    }

    public function saveTournamentParticipant(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_tournament_participants')) {
            return ['ok' => false, 'message' => 'Миграция участников турниров еще не применена.'];
        }
        $tournamentId = (int) ($payload['tournament_id'] ?? 0);
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($tournamentId <= 0 || $userId <= 0) {
            return ['ok' => false, 'message' => 'Укажи турнир и игрока.'];
        }
        if (!$this->rowById('admin_tournaments', 'id', $tournamentId)) {
            return ['ok' => false, 'message' => 'Турнир не найден.'];
        }
        if (!$this->rowById('users', 'id', $userId)) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }

        $data = [
            'tournament_id' => $tournamentId,
            'user_id' => $userId,
            'pokemon_id' => max(0, (int) ($payload['pokemon_id'] ?? 0)),
            'status' => $this->participantStatus((string) ($payload['status'] ?? 'registered')),
            'score' => (int) ($payload['score'] ?? 0),
            'place_num' => max(0, (int) ($payload['place_num'] ?? 0)),
            'joined_at' => time(),
            'updated_at' => time(),
        ];
        $before = $this->participantRow($tournamentId, $userId);
        $this->db->prepare(
            'INSERT INTO admin_tournament_participants
                (tournament_id, user_id, pokemon_id, status, score, place_num, joined_at, updated_at)
             VALUES
                (:tournament_id, :user_id, :pokemon_id, :status, :score, :place_num, :joined_at, :updated_at)
             ON DUPLICATE KEY UPDATE
                pokemon_id = VALUES(pokemon_id), status = VALUES(status), score = VALUES(score),
                place_num = VALUES(place_num), updated_at = VALUES(updated_at)'
        )->execute($data);
        $this->audit($adminId, 'tournament.participant.save', 'admin_tournament_participants', $tournamentId, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Участник сохранен.'];
    }

    public function medals(string $search = '', int $limit = 80): array
    {
        if (!$this->tableExists('admin_medals')) {
            return [];
        }
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT m.*, t.title AS tournament_title,
                       COUNT(um.id) AS awarded_count
                  FROM admin_medals m
             LEFT JOIN admin_tournaments t ON t.id = m.tournament_id
             LEFT JOIN admin_user_medals um ON um.medal_id = m.id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE m.id = :id_search OR m.title LIKE :search OR m.description LIKE :search OR m.medal_type LIKE :search';
            $params = [
                'id_search' => ctype_digit($search) ? (int) $search : -1,
                'search' => '%' . $search . '%',
            ];
        }
        $sql .= ' GROUP BY m.id ORDER BY m.sort_order ASC, m.id DESC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['icon'] = $this->medalIconPath((string) ($row['icon_file'] ?? ''));
        }
        return $rows;
    }

    public function saveMedal(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_medals')) {
            return ['ok' => false, 'message' => 'Миграция медалей еще не применена.'];
        }
        $id = (int) ($payload['id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        if ($title === '') {
            return ['ok' => false, 'message' => 'Укажи название медали.'];
        }
        $now = time();
        $before = $id > 0 ? $this->rowById('admin_medals', 'id', $id) : [];
        $data = [
            'title' => $title,
            'description' => trim((string) ($payload['description'] ?? '')),
            'icon_file' => basename(trim((string) ($payload['icon_file'] ?? ''))),
            'medal_type' => $this->medalType((string) ($payload['medal_type'] ?? 'tournament')),
            'tournament_id' => max(0, (int) ($payload['tournament_id'] ?? 0)),
            'sort_order' => (int) ($payload['sort_order'] ?? 0),
            'enabled' => !empty($payload['enabled']) ? 1 : 0,
            'updated_by' => $adminId,
            'updated_at' => $now,
        ];

        if ($before) {
            $data['id'] = $id;
            $this->db->prepare(
                'UPDATE admin_medals
                    SET title = :title, description = :description, icon_file = :icon_file,
                        medal_type = :medal_type, tournament_id = :tournament_id, sort_order = :sort_order,
                        enabled = :enabled, updated_by = :updated_by, updated_at = :updated_at
                  WHERE id = :id'
            )->execute($data);
            $action = 'medal.update';
        } else {
            $data['created_by'] = $adminId;
            $data['created_at'] = $now;
            $this->db->prepare(
                'INSERT INTO admin_medals
                    (title, description, icon_file, medal_type, tournament_id, sort_order, enabled, created_by, updated_by, created_at, updated_at)
                 VALUES
                    (:title, :description, :icon_file, :medal_type, :tournament_id, :sort_order, :enabled, :created_by, :updated_by, :created_at, :updated_at)'
            )->execute($data);
            $id = (int) $this->db->lastInsertId();
            $action = 'medal.create';
        }
        $this->audit($adminId, $action, 'admin_medals', $id, ['before' => $before, 'after' => $data]);
        return ['ok' => true, 'message' => 'Медаль сохранена.', 'id' => $id];
    }

    public function deleteMedal(int $adminId, int $id, string $confirm): array
    {
        if ($confirm !== 'DELETE') {
            return ['ok' => false, 'message' => 'Для удаления введи DELETE.'];
        }
        $before = $this->rowById('admin_medals', 'id', $id);
        if (!$before) {
            return ['ok' => false, 'message' => 'Медаль не найдена.'];
        }
        $awards = $this->tableExists('admin_user_medals')
            ? $this->lookupRows('SELECT * FROM admin_user_medals WHERE medal_id = ' . (int) $id)
            : [];
        $this->audit($adminId, 'medal.delete.before', 'admin_medals', $id, ['before' => $before, 'awards' => $awards]);
        $this->db->prepare('DELETE FROM admin_user_medals WHERE medal_id = :id')->execute(['id' => $id]);
        $this->db->prepare('DELETE FROM admin_medals WHERE id = :id LIMIT 1')->execute(['id' => $id]);
        $this->audit($adminId, 'medal.delete', 'admin_medals', $id, ['before' => $before, 'awards' => $awards]);
        return ['ok' => true, 'message' => 'Медаль удалена.'];
    }

    public function awardMedal(int $adminId, array $payload): array
    {
        if (!$this->tableExists('admin_user_medals')) {
            return ['ok' => false, 'message' => 'Миграция выдачи медалей еще не применена.'];
        }
        $medalId = (int) ($payload['medal_id'] ?? 0);
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($medalId <= 0 || $userId <= 0) {
            return ['ok' => false, 'message' => 'Укажи медаль и игрока.'];
        }
        if (!$this->rowById('admin_medals', 'id', $medalId)) {
            return ['ok' => false, 'message' => 'Медаль не найдена.'];
        }
        if (!$this->rowById('users', 'id', $userId)) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }
        $data = [
            'medal_id' => $medalId,
            'user_id' => $userId,
            'tournament_id' => max(0, (int) ($payload['tournament_id'] ?? 0)),
            'comment' => trim((string) ($payload['comment'] ?? '')),
            'awarded_by' => $adminId,
            'awarded_at' => time(),
        ];
        $this->db->prepare(
            'INSERT INTO admin_user_medals (medal_id, user_id, tournament_id, comment, awarded_by, awarded_at)
             VALUES (:medal_id, :user_id, :tournament_id, :comment, :awarded_by, :awarded_at)
             ON DUPLICATE KEY UPDATE comment = VALUES(comment), awarded_by = VALUES(awarded_by), awarded_at = VALUES(awarded_at)'
        )->execute($data);
        $this->audit($adminId, 'medal.award', 'admin_user_medals', $medalId, $data);
        return ['ok' => true, 'message' => 'Медаль выдана.'];
    }

    public function moderation(): array
    {
        $authorIdSelect = $this->columnExists('chats', 'author_id') ? 'author_id' : '0 AS author_id';
        $punishments = [];
        if ($this->tableExists('moderation_punishments')) {
            $punishments = $this->lookupRows(
                'SELECT p.*, u.login AS moderator_login
                   FROM moderation_punishments p
              LEFT JOIN users u ON u.id = p.moderator_user_id
               ORDER BY p.id DESC
                  LIMIT 100'
            );
        }
        return [
            'punishments' => $punishments,
            'bans' => $this->lookupRows('SELECT id, ip, date FROM banip ORDER BY id DESC LIMIT 80'),
            'chat' => $this->lookupRows('SELECT id, author, ' . $authorIdSelect . ', userto, private, room, tipe, time, text FROM chats ORDER BY id DESC LIMIT 80'),
        ];
    }

    public function lookupByType(string $type, string $query = '', int $limit = 30): array
    {
        $limit = max(1, min(80, $limit));
        $query = trim($query);
        $id = ctype_digit($query) ? (int) $query : -1;
        $like = '%' . $query . '%';
        $hasQuery = $query !== '';

        if (in_array($type, ['pokeBase', 'basePokemon'], true)) {
            return $this->formatPokeBaseLookupRows($this->lookupRowsPrepared(
                'SELECT id, title AS name FROM poke_base
                  ' . ($hasQuery ? 'WHERE id = :id OR title LIKE :like_title' : '') . '
                  ORDER BY id ASC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_title' => $like] : []
            ));
        }

        return match ($type) {
            'users', 'player', 'players' => $this->lookupRowsPrepared(
                'SELECT id, login AS name, login AS label FROM users
                  ' . ($hasQuery ? 'WHERE id = :id OR login LIKE :like_login OR email LIKE :like_email' : '') . '
                  ORDER BY id DESC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_login' => $like, 'like_email' => $like] : []
            ),
            'items', 'item' => $this->lookupRowsPrepared(
                'SELECT id, name, CONCAT("#", id, " ", name) AS label FROM items
                  ' . ($hasQuery ? 'WHERE id = :id OR name LIKE :like_name OR tittle LIKE :like_title' : '') . '
                  ORDER BY id ASC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_name' => $like, 'like_title' => $like] : []
            ),
            'attacks', 'attack' => $this->lookupRowsPrepared(
                'SELECT atac_id AS id, atac_name AS name, CONCAT("#", atac_id, " ", atac_name) AS label FROM attac_power
                  ' . ($hasQuery ? 'WHERE atac_id = :id OR atac_name LIKE :like_name OR atac_tip LIKE :like_type' : '') . '
                  ORDER BY atac_id ASC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_name' => $like, 'like_type' => $like] : []
            ),
            'pokemon', 'pokemonSpecies' => $this->lookupRowsPrepared(
                'SELECT id, CONCAT(Code, " ", Name) AS name, CONCAT("#", id, " ", Code, " ", Name) AS label FROM pokemon
                  ' . ($hasQuery ? 'WHERE id = :id OR Code LIKE :like_code OR Name LIKE :like_name' : '') . '
                  ORDER BY id ASC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_code' => $like, 'like_name' => $like] : []
            ),
            'natures', 'nature', 'har' => $this->lookupNatures($hasQuery ? $id : null, $limit),
            'locations', 'location' => $this->lookupRowsPrepared(
                'SELECT id, title AS name, CONCAT("#", id, " ", title) AS label FROM build
                  ' . ($hasQuery ? 'WHERE id = :id OR title LIKE :like_title' : '') . '
                  ORDER BY title ASC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_title' => $like] : []
            ),
            'wildSlots', 'wild_slot' => $this->lookupRowsPrepared(
                'SELECT pb.id, CONCAT("#", pb.id, " ", COALESCE(b.title, "Локация"), " - ", COALESCE(p.Code, ""), " ", COALESCE(p.Name, ""), " Lv.", pb.lvl) AS name,
                        CONCAT("#", pb.id, " ", COALESCE(b.title, "Локация"), " - ", COALESCE(p.Name, "pokemon")) AS label
                   FROM pokebuild pb
              LEFT JOIN build b ON b.id = pb.building
              LEFT JOIN pokemon p ON p.id = pb.baseid
                  ' . ($hasQuery ? 'WHERE pb.id = :id OR b.title LIKE :like_build OR p.Code LIKE :like_code OR p.Name LIKE :like_name' : '') . '
                  ORDER BY pb.id DESC LIMIT ' . $limit,
                $hasQuery ? ['id' => $id, 'like_build' => $like, 'like_code' => $like, 'like_name' => $like] : []
            ),
            default => [],
        };
    }

    public function moderationAction(int $adminId, array $payload): array
    {
        if (!$this->tableExists('moderation_punishments')) {
            return ['ok' => false, 'message' => 'Миграция moderation_punishments еще не применена.'];
        }

        $action = strtolower(trim((string) ($payload['action'] ?? '')));
        if (!in_array($action, ['mute', 'unmute', 'ban', 'unban', 'warn'], true)) {
            return ['ok' => false, 'message' => 'Выберите действие: mute, unmute, ban, unban или warn.'];
        }

        $target = $this->resolveModerationTarget($payload);
        if (!$target) {
            return ['ok' => false, 'message' => 'Игрок не найден.'];
        }

        $reason = trim((string) ($payload['reason'] ?? ''));
        if ($reason === '') {
            $reason = 'Действие модерации.';
        }

        if (in_array($action, ['unmute', 'unban'], true)) {
            $punishmentAction = $action === 'unmute' ? 'mute' : 'ban';
            $count = $this->revokeModerationPunishments($adminId, (int) $target['id'], $punishmentAction, $reason);
            if ($action === 'unban') {
                $count += $this->deleteBanIpForUser($target);
            }
            $this->audit($adminId, 'moderation.' . $action, 'users', (int) $target['id'], [
                'target' => $target,
                'reason' => $reason,
                'affected' => $count,
            ]);
            return ['ok' => true, 'message' => $count > 0 ? 'Наказание снято.' : 'Активное наказание не найдено.'];
        }

        if ($action === 'warn') {
            $punishmentId = $this->createModerationPunishment($adminId, $target, 'warn', time(), $reason, 'chat', 0);
            $this->audit($adminId, 'moderation.warn', 'moderation_punishments', $punishmentId, [
                'target' => $target,
                'reason' => $reason,
            ]);
            return ['ok' => true, 'message' => 'Предупреждение записано.'];
        }

        $expiresAt = $this->parseModerationDuration((string) ($payload['duration'] ?? ''));
        if ($expiresAt === null) {
            return ['ok' => false, 'message' => 'Укажите срок: 15minut, 1h, 2d или perm.'];
        }

        $scope = $action === 'mute' ? 'chat' : 'game';
        $punishmentId = $this->createModerationPunishment($adminId, $target, $action, $expiresAt, $reason, $scope, 1);
        if ($action === 'ban') {
            $this->createBanIpForUser($target);
        }

        $this->audit($adminId, 'moderation.' . $action, 'moderation_punishments', $punishmentId, [
            'target' => $target,
            'duration' => (string) ($payload['duration'] ?? ''),
            'expires_at' => $expiresAt,
            'reason' => $reason,
        ]);

        return ['ok' => true, 'message' => $action === 'mute' ? 'Мут выдан.' : 'Бан выдан.'];
    }

    public function auditRows(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        return $this->lookupRows(
            'SELECT a.id, a.admin_id, u.login AS admin_login, a.action, a.entity, a.entity_id, a.payload, a.created_at
               FROM admin_audit_log a
          LEFT JOIN users u ON u.id = a.admin_id
              ORDER BY a.id DESC
              LIMIT ' . $limit
        );
    }

    public function settings(): array
    {
        if (!$this->tableExists('site_settings')) {
            return ['techwork' => '0'];
        }
        $rows = $this->lookupRows('SELECT name, value, updated_by, updated_at FROM site_settings ORDER BY name ASC');
        $settings = [];
        foreach ($rows as $row) {
            $settings[(string) $row['name']] = (string) $row['value'];
        }
        return $settings + ['techwork' => '0'];
    }

    public function setting(string $name, string $default = ''): string
    {
        if (!$this->tableExists('site_settings')) {
            return $default;
        }
        $stmt = $this->db->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public function saveSettings(int $adminId, array $payload): array
    {
        if (!$this->tableExists('site_settings')) {
            return ['ok' => false, 'message' => 'Миграция site_settings еще не применена.'];
        }
        $allowed = ['techwork'];
        $saved = [];
        foreach ($allowed as $name) {
            if (!array_key_exists($name, $payload)) {
                continue;
            }
            $value = $name === 'techwork' ? (!empty($payload[$name]) ? '1' : '0') : trim((string) $payload[$name]);
            $this->db->prepare(
                'INSERT INTO site_settings (name, value, updated_by, updated_at)
                 VALUES (:name, :value, :admin, :time)
                 ON DUPLICATE KEY UPDATE value = VALUES(value), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)'
            )->execute(['name' => $name, 'value' => $value, 'admin' => $adminId, 'time' => time()]);
            $saved[$name] = $value;
        }
        $this->audit($adminId, 'settings.update', 'site_settings', 0, $saved);
        return ['ok' => true, 'message' => 'Настройки сохранены.', 'settings' => $this->settings()];
    }

    private function legacyModule(
        string $key,
        string $title,
        string $source,
        string $sourceType,
        string $status,
        string $adminTab,
        string $newRoute,
        string $apiRoute,
        string $dataTable,
        string $notes
    ): array {
        $sourceExists = $this->legacySourceExists($source, $sourceType);
        $dataExists = $dataTable !== '' && $this->tableExists($dataTable);
        $dataRows = $dataExists ? $this->countTable($dataTable) : null;

        return [
            'key' => $key,
            'title' => $title,
            'status' => $status,
            'status_label' => $this->legacyStatusLabel($status),
            'status_order' => $this->legacyStatusOrder($status),
            'source' => $source,
            'source_type' => $sourceType,
            'source_exists' => $sourceExists,
            'source_status' => $sourceType === 'virtual' ? 'маршрут' : ($sourceExists ? 'найден' : 'нет'),
            'source_size' => $this->legacySourceSize($source, $sourceType),
            'admin_tab' => $adminTab,
            'new_route' => $newRoute,
            'api_route' => $apiRoute,
            'new_area' => $adminTab !== '' ? $this->adminTabTitle($adminTab) : 'не задано',
            'data_table' => $dataTable,
            'data_exists' => $dataExists,
            'data_rows' => $dataRows,
            'data_status' => $dataTable === '' ? 'нет таблицы' : ($dataExists ? number_format((int) $dataRows, 0, ',', ' ') . ' строк' : 'таблица не найдена'),
            'notes' => $notes,
        ];
    }

    private function legacySupportFiles(array $mappedSources): array
    {
        if (!defined('APP_ROOT')) {
            return [];
        }

        $mapped = array_flip($mappedSources);
        $rows = [];
        foreach (glob(APP_ROOT . '/admin/*') ?: [] as $path) {
            if (!is_file($path)) {
                continue;
            }
            $source = 'admin/' . basename($path);
            if (isset($mapped[$source])) {
                continue;
            }
            $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            $title = match ($extension) {
                'php' => 'Legacy-файл: ' . basename($path),
                'css' => 'Legacy-стили: ' . basename($path),
                default => 'Legacy-ресурс: ' . basename($path),
            };
            $rows[] = $this->legacyModule(
                'legacy_' . preg_replace('/[^a-zA-Z0-9_]+/', '_', basename($path)),
                $title,
                $source,
                'file',
                'SOURCE_ONLY',
                'legacy',
                '',
                '',
                '',
                'Файл есть в старой админке, прямое выполнение отключено. Используется только как источник правил и старого поведения.'
            );
        }
        return $rows;
    }

    private function legacySourceExists(string $source, string $sourceType): bool
    {
        if ($sourceType === 'table') {
            return $this->tableExists($source);
        }
        if ($sourceType === 'virtual') {
            return $source !== '';
        }
        return defined('APP_ROOT') && is_file(APP_ROOT . '/' . ltrim($source, '/'));
    }

    private function legacySourceSize(string $source, string $sourceType): int
    {
        if ($sourceType !== 'file' || !defined('APP_ROOT')) {
            return 0;
        }
        $path = APP_ROOT . '/' . ltrim($source, '/');
        return is_file($path) ? (int) filesize($path) : 0;
    }

    private function legacyStatusLabel(string $status): string
    {
        return match ($status) {
            'DONE' => 'готово',
            'PARTIAL_NEW' => 'частично перенесено',
            'TODO_REWRITE' => 'нужно переписать',
            'SKIPPED_BY_DESIGN' => 'не переносим отдельно',
            'SOURCE_ONLY' => 'только источник',
            default => $status,
        };
    }

    private function legacyStatusOrder(string $status): int
    {
        return match ($status) {
            'TODO_REWRITE' => 10,
            'PARTIAL_NEW' => 20,
            'DONE' => 30,
            'SKIPPED_BY_DESIGN' => 40,
            'SOURCE_ONLY' => 50,
            default => 90,
        };
    }

    private function adminTabTitle(string $tab): string
    {
        return [
            'dashboard' => 'Дашборд',
            'users' => 'Пользователи',
            'items' => 'Предметы',
            'market' => 'Покемаркет',
            'drops' => 'Дроп',
            'locations' => 'Локации',
            'pokemon' => 'Покемоны',
            'attacks' => 'Атаки',
            'news' => 'Новости',
            'tournaments' => 'Турниры',
            'medals' => 'Медали',
            'moderation' => 'Модерация',
            'settings' => 'Система',
            'legacy' => 'Legacy-карта',
        ][$tab] ?? $tab;
    }

    private function itemById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!$row) {
            return [];
        }
        $row['icon'] = $this->itemIconPath($id);
        return $row;
    }

    private function rowById(string $table, string $column, int $id): array
    {
        if ($id <= 0 || !preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return [];
        }
        $stmt = $this->db->prepare(sprintf('SELECT * FROM `%s` WHERE `%s` = :id LIMIT 1', $table, $column));
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function marketItemByItemId(int $itemId): array
    {
        if ($itemId <= 0) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM market_shop_items WHERE item_id = :item LIMIT 1');
        $stmt->execute(['item' => $itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function parseTemporaryItemExpiresAt(array $payload): int|null|false
    {
        if (empty($payload['temporary'])) {
            return null;
        }

        $seconds = (int) ($payload['expires_seconds'] ?? 0);
        if ($seconds > 0) {
            return time() + $seconds;
        }

        $rawDate = trim((string) ($payload['expires_at'] ?? ''));
        if ($rawDate === '') {
            return false;
        }

        $timestamp = strtotime($rawDate);
        return $timestamp !== false && $timestamp > time() ? $timestamp : false;
    }

    private function addItemToUser(int $userId, int $itemId, int $count, ?int $expiresAt = null): int
    {
        $dattimer = $expiresAt === null ? 'not' : (string) $expiresAt;
        $rowId = 0;
        $this->withItemsUsersLock(function () use ($userId, $itemId, $count, $dattimer, &$rowId): void {
            $existing = $this->db->prepare('SELECT id FROM items_users WHERE user_id = :user AND item_id = :item AND dattimer = :dattimer LIMIT 1');
            $existing->execute(['user' => $userId, 'item' => $itemId, 'dattimer' => $dattimer]);
            $rowId = (int) ($existing->fetchColumn() ?: 0);
            if ($rowId > 0) {
                $this->db->prepare('UPDATE items_users SET count = count + :count WHERE id = :id LIMIT 1')
                    ->execute(['count' => $count, 'id' => $rowId]);
                return;
            }
            $rowId = $this->nextTableId('items_users', 'id');
            $this->db->prepare(
                'INSERT INTO items_users (id, item_id, user_id, count, dattimer, timers)
                 VALUES (:id, :item, :user, :count, :dattimer, "not")'
            )->execute([
                'id' => $rowId,
                'item' => $itemId,
                'user' => $userId,
                'count' => $count,
                'dattimer' => $dattimer,
            ]);
        });
        return $rowId;
    }

    private function ensureInformationUser(int $userId): void
    {
        $exists = $this->db->prepare('SELECT id FROM information_users WHERE users_id = :user LIMIT 1');
        $exists->execute(['user' => $userId]);
        if ($exists->fetchColumn()) {
            return;
        }
        $this->db->prepare('INSERT INTO information_users (id, users_id, admins_panels) VALUES (:id, :user, 0)')
            ->execute(['id' => $this->nextTableId('information_users', 'id'), 'user' => $userId]);
    }

    private function seedPokemonMoves(int $pokemonId, int $baseId, int $level): array
    {
        $startStmt = $this->db->prepare(
            'SELECT ap.atac_id, COALESCE(power.atac_pp, 0) AS pp, power.atac_name, ap.atc_lvl
               FROM attac_poke ap
          LEFT JOIN attac_power power ON power.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base AND ap.atc_lvl <= :lvl
              ORDER BY ap.atc_lvl ASC, ap.id_structure ASC
              LIMIT 1'
        );
        $startStmt->execute(['base' => $baseId, 'lvl' => $level]);
        $startMove = $startStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($startMove === null) {
            $fallbackStmt = $this->db->prepare(
                'SELECT ap.atac_id, COALESCE(power.atac_pp, 0) AS pp, power.atac_name, ap.atc_lvl
                   FROM attac_poke ap
              LEFT JOIN attac_power power ON power.atac_id = ap.atac_id
                  WHERE ap.poke_base_id = :base
                  ORDER BY ap.atc_lvl ASC, ap.id_structure ASC
                  LIMIT 1'
            );
            $fallbackStmt->execute(['base' => $baseId]);
            $startMove = $fallbackStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $moves = [];
        if ($startMove !== null) {
            $moves[] = $startMove;
        }

        $extraStmt = $this->db->prepare(
            'SELECT ap.atac_id, COALESCE(power.atac_pp, 0) AS pp, power.atac_name, ap.atc_lvl
               FROM attac_poke ap
          LEFT JOIN attac_power power ON power.atac_id = ap.atac_id
              WHERE ap.poke_base_id = :base
                AND ap.atc_lvl <= :lvl
                AND ap.atac_id <> :start
              ORDER BY ap.atc_lvl DESC, ap.id_structure DESC
              LIMIT 3'
        );
        $extraStmt->execute([
            'base' => $baseId,
            'lvl' => $level,
            'start' => (int) ($startMove['atac_id'] ?? 0),
        ]);
        foreach ($extraStmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $move) {
            $moves[] = $move;
        }

        $slots = [
            ['id' => 0, 'pp' => 0],
            ['id' => 0, 'pp' => 0],
            ['id' => 0, 'pp' => 0],
            ['id' => 0, 'pp' => 0],
        ];
        foreach ($moves as $index => $move) {
            $slots[$index] = ['id' => (int) $move['atac_id'], 'pp' => max(0, (int) $move['pp'])];
        }
        $this->db->prepare(
            'INSERT INTO attac_my_poke
                (id, pok_id, a_id, a_pp_min, a_pp_max, b_id, b_pp_min, b_pp_max, c_id, c_pp_min, c_pp_max, d_id, d_pp_min, d_pp_max)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $this->nextTableId('attac_my_poke', 'id'),
            $pokemonId,
            $slots[0]['id'],
            $slots[0]['pp'],
            $slots[0]['pp'],
            $slots[1]['id'],
            $slots[1]['pp'],
            $slots[1]['pp'],
            $slots[2]['id'],
            $slots[2]['pp'],
            $slots[2]['pp'],
            $slots[3]['id'],
            $slots[3]['pp'],
            $slots[3]['pp'],
        ]);

        return array_map(static fn (array $move): array => [
            'id' => (int) ($move['atac_id'] ?? 0),
            'name' => (string) ($move['atac_name'] ?? ''),
            'level' => (int) ($move['atc_lvl'] ?? 0),
            'pp' => max(0, (int) ($move['pp'] ?? 0)),
        ], $moves);
    }

    private function nextTableId(string $table, string $column): int
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return 1;
        }
        return (int) ($this->db->query(sprintf('SELECT COALESCE(MAX(`%s`), 0) + 1 FROM `%s`', $column, $table))->fetchColumn() ?: 1);
    }

    private function withItemsUsersLock(callable $callback): void
    {
        $lock = $this->db->prepare('SELECT GET_LOCK(:name, 15)');
        $lock->execute(['name' => 'pokemon8_seq_items_users_id']);
        if ((int) ($lock->fetchColumn() ?: 0) !== 1) {
            throw new \RuntimeException('Unable to acquire items_users lock.');
        }

        try {
            $callback();
        } finally {
            $release = $this->db->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => 'pokemon8_seq_items_users_id']);
        }
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }
        $stmt = $this->db->prepare(
            'SELECT 1
               FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
              LIMIT 1'
        );
        $stmt->execute(['table' => $table]);
        return $cache[$table] = (bool) $stmt->fetchColumn();
    }

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (isset($cache[$key])) {
            return $cache[$key];
        }
        $stmt = $this->db->prepare(
            'SELECT 1
               FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column
              LIMIT 1'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);
        return $cache[$key] = (bool) $stmt->fetchColumn();
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

    /** @param array<string,int|string> $params */
    private function lookupRowsPrepared(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function lookupNatures(?int $id, int $limit): array
    {
        $names = [
            1 => 'Веселый',
            2 => 'Выносливый',
            3 => 'Послушный',
            4 => 'Серьезный',
            5 => 'Спокойный',
            6 => 'Мягкий',
            7 => 'Смелый',
            8 => 'Наивный',
            9 => 'Нахальный',
            10 => 'Кроткий',
            11 => 'Озорной',
            12 => 'Непреклонный',
            13 => 'Застенчивый',
            14 => 'Одинокий',
            15 => 'Проказливый',
            16 => 'Осторожный',
            17 => 'Торопливый',
            18 => 'Чудаковатый',
            19 => 'Распущенный',
            20 => 'Робкий',
            21 => 'Мирный',
            22 => 'Скромный',
            23 => 'Храбрый',
            24 => 'Расслабленный',
            25 => 'Дерзкий',
            26 => 'Тихий',
        ];
        $statNames = [
            'atk' => 'атака',
            'def' => 'защита',
            'satk' => 'спец. атака',
            'sdef' => 'спец. защита',
            'speed' => 'скорость',
        ];
        $sql = 'SELECT id_har, atk, def, satk, sdef, speed FROM har'
            . ($id !== null && $id > 0 ? ' WHERE id_har = :id' : '')
            . ' ORDER BY id_har ASC LIMIT ' . max(1, min(80, $limit));
        $stmt = $this->db->prepare($sql);
        if ($id !== null && $id > 0) {
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $parts = [];
            foreach ($statNames as $key => $label) {
                $value = (float) ($row[$key] ?? 1);
                if ($value > 1.0) {
                    $parts[] = '+' . $label;
                } elseif ($value < 1.0) {
                    $parts[] = '-' . $label;
                }
            }
            $natureId = (int) $row['id_har'];
            $name = $names[$natureId] ?? ('Характер #' . $natureId);
            $rows[] = [
                'id' => $natureId,
                'name' => $name,
                'label' => '#' . $natureId . ' ' . $name . ($parts !== [] ? ' (' . implode(', ', $parts) . ')' : ' (без изменений)'),
            ];
        }
        return $rows;
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

    private function adminTimestamp(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        if (ctype_digit($value)) {
            return (int) $value;
        }
        $time = strtotime($value);
        return $time === false ? 0 : $time;
    }

    private function tournamentStatus(string $value): string
    {
        return in_array($value, ['draft', 'registration', 'active', 'finished', 'cancelled'], true) ? $value : 'draft';
    }

    private function participantStatus(string $value): string
    {
        return in_array($value, ['registered', 'checked_in', 'eliminated', 'winner', 'disqualified'], true) ? $value : 'registered';
    }

    private function medalType(string $value): string
    {
        return in_array($value, ['tournament', 'achievement', 'event', 'admin'], true) ? $value : 'tournament';
    }

    private function participantRow(int $tournamentId, int $userId): array
    {
        if ($tournamentId <= 0 || $userId <= 0 || !$this->tableExists('admin_tournament_participants')) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT * FROM admin_tournament_participants WHERE tournament_id = :tournament AND user_id = :user LIMIT 1');
        $stmt->execute(['tournament' => $tournamentId, 'user' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function medalIconPath(string $file): string
    {
        $file = basename($file);
        if ($file !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/items/' . $file)) {
            return '/public/img/items/' . $file;
        }
        if ($file !== '' && defined('APP_ROOT') && is_file(APP_ROOT . '/public/img/ui/' . $file)) {
            return '/public/img/ui/' . $file;
        }
        return '/public/img/ui/menu-profile.png';
    }

    private function sourceType(string $value): string
    {
        return in_array($value, ['wild', 'trainer', 'npc', 'event'], true) ? $value : 'wild';
    }

    private function recentErrorLines(int $limit): array
    {
        $path = dirname(__DIR__, 2) . '/log_php_errors.txt';
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $lines = array_slice($lines, -max(1, min(40, $limit)));
        return array_map(static fn (string $line): array => ['line' => $line], array_reverse($lines));
    }

    private function activeEventCount(): int
    {
        if (!$this->tableExists('game_event_boosts')) {
            return 0;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
               FROM game_event_boosts
              WHERE enabled = 1
                AND (starts_at = 0 OR starts_at <= :now_a)
                AND (ends_at = 0 OR ends_at >= :now_b)'
        );
        $stmt->execute(['now_a' => $now, 'now_b' => $now]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function activeEvents(int $limit = 12): array
    {
        if (!$this->tableExists('game_event_boosts')) {
            return [];
        }

        $now = time();
        $stmt = $this->db->prepare(
            'SELECT id, title, boost_key, multiplier, scope, starts_at, ends_at, enabled, note
               FROM game_event_boosts
              WHERE enabled = 1
                AND (starts_at = 0 OR starts_at <= :now_a)
                AND (ends_at = 0 OR ends_at >= :now_b)
              ORDER BY ends_at = 0 DESC, ends_at ASC, id DESC
              LIMIT ' . max(1, min(50, $limit))
        );
        $stmt->execute(['now_a' => $now, 'now_b' => $now]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$row) {
            $row['boost_label'] = $this->boostLabel((string) ($row['boost_key'] ?? ''));
            $row['scope_label'] = $this->boostScopeLabel((string) ($row['scope'] ?? ''));
            $row['status_label'] = $this->eventStatusLabel($row);
        }

        return $rows;
    }

    private function boostKey(string $value): string
    {
        $value = trim($value);
        if ($value === 'money') {
            $value = 'coins';
        }

        return in_array($value, ['exp', 'coins', 'drop', 'quest_rewards', 'catch', 'happiness'], true) ? $value : 'exp';
    }

    private function boostScope(string $value): string
    {
        return in_array($value, ['global', 'pve', 'pvp', 'quest', 'market'], true) ? $value : 'global';
    }

    private function boostLabel(string $value): string
    {
        return match ($this->boostKey($value)) {
            'exp' => 'Опыт',
            'coins' => 'Монеты',
            'drop' => 'Шанс дропа',
            'quest_rewards' => 'Квестовые награды',
            'catch' => 'Шанс ловли',
            'happiness' => 'Счастье покемонов',
            default => 'Бонус',
        };
    }

    private function boostScopeLabel(string $value): string
    {
        return match ($this->boostScope($value)) {
            'global' => 'Везде',
            'pve' => 'PvE',
            'pvp' => 'PvP',
            'quest' => 'Квесты',
            'market' => 'Магазин',
            default => 'Везде',
        };
    }

    /**
     * @param array<string,mixed> $row
     */
    private function eventStatusLabel(array $row): string
    {
        if ((int) ($row['enabled'] ?? 0) !== 1) {
            return 'выключен';
        }

        $now = time();
        $startsAt = (int) ($row['starts_at'] ?? 0);
        $endsAt = (int) ($row['ends_at'] ?? 0);
        if ($startsAt > 0 && $startsAt > $now) {
            return 'запланирован';
        }
        if ($endsAt > 0 && $endsAt < $now) {
            return 'завершён';
        }

        return 'активен';
    }

    /**
     * @param mixed $value
     */
    private function parseTimestamp($value): int
    {
        $raw = trim((string) $value);
        if ($raw === '' || $raw === '0') {
            return 0;
        }
        if (ctype_digit($raw)) {
            return max(0, (int) $raw);
        }

        $timestamp = strtotime($raw);
        return $timestamp === false ? 0 : max(0, $timestamp);
    }

    private function resolveModerationTarget(array $payload): array
    {
        $target = trim((string) ($payload['target'] ?? ''));
        $userId = (int) ($payload['user_id'] ?? 0);
        if ($target === '' && $userId > 0) {
            $target = (string) $userId;
        }
        if ($target === '') {
            return [];
        }

        if (ctype_digit($target)) {
            $stmt = $this->db->prepare('SELECT id, login, ip FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => (int) $target]);
        } else {
            $stmt = $this->db->prepare('SELECT id, login, ip FROM users WHERE LOWER(login) = LOWER(:login) LIMIT 1');
            $stmt->execute(['login' => $target]);
        }

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    private function createModerationPunishment(
        int $adminId,
        array $target,
        string $action,
        int $expiresAt,
        string $reason,
        string $scope,
        int $active
    ): int {
        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO moderation_punishments
                (target_user_id, target_login, moderator_user_id, action, scope, reason, starts_at, expires_at, active, created_at)
             VALUES
                (:target_user_id, :target_login, :moderator_user_id, :action, :scope, :reason, :starts_at, :expires_at, :active, :created_at)'
        );
        $stmt->execute([
            'target_user_id' => (int) $target['id'],
            'target_login' => (string) $target['login'],
            'moderator_user_id' => $adminId,
            'action' => $action,
            'scope' => $scope,
            'reason' => $reason,
            'starts_at' => $now,
            'expires_at' => $expiresAt,
            'active' => $active,
            'created_at' => $now,
        ]);
        return (int) $this->db->lastInsertId();
    }

    private function revokeModerationPunishments(int $adminId, int $targetUserId, string $action, string $reason): int
    {
        $stmt = $this->db->prepare(
            'UPDATE moderation_punishments
                SET active = 0, revoked_at = :revoked_at, revoked_by = :revoked_by, revoke_reason = :reason
              WHERE target_user_id = :target
                AND action = :action
                AND active = 1'
        );
        $stmt->execute([
            'revoked_at' => time(),
            'revoked_by' => $adminId,
            'reason' => $reason,
            'target' => $targetUserId,
            'action' => $action,
        ]);

        return $stmt->rowCount();
    }

    private function createBanIpForUser(array $target): void
    {
        $ip = (int) ($target['ip'] ?? 0);
        if ($ip <= 0) {
            return;
        }

        $exists = $this->db->prepare('SELECT 1 FROM banip WHERE ip = :ip LIMIT 1');
        $exists->execute(['ip' => $ip]);
        if ($exists->fetchColumn()) {
            return;
        }

        $this->db->prepare('INSERT INTO banip (id, ip, date) VALUES (:id, :ip, NOW())')
            ->execute(['id' => $this->nextTableId('banip', 'id'), 'ip' => $ip]);
    }

    private function deleteBanIpForUser(array $target): int
    {
        $ip = (int) ($target['ip'] ?? 0);
        if ($ip <= 0) {
            return 0;
        }

        $stmt = $this->db->prepare('DELETE FROM banip WHERE ip = :ip');
        $stmt->execute(['ip' => $ip]);
        return $stmt->rowCount();
    }

    private function parseModerationDuration(string $duration): ?int
    {
        $duration = mb_strtolower(trim($duration));
        if ($duration === '') {
            return null;
        }
        if (in_array($duration, ['0', 'perm', 'perma', 'permanent', 'forever', 'навсегда', 'бессрочно'], true)) {
            return 0;
        }
        if (!preg_match('/^(\d+)\s*([a-zа-я]+)$/iu', $duration, $matches)) {
            return null;
        }

        $amount = max(1, (int) $matches[1]);
        $unit = mb_strtolower($matches[2]);
        $seconds = match (true) {
            in_array($unit, ['m', 'min', 'mins', 'minute', 'minutes', 'minut', 'мин', 'минута', 'минут', 'минуты'], true) => $amount * 60,
            in_array($unit, ['h', 'hr', 'hour', 'hours', 'час', 'часа', 'часов'], true) => $amount * 3600,
            in_array($unit, ['d', 'day', 'days', 'д', 'день', 'дня', 'дней'], true) => $amount * 86400,
            in_array($unit, ['w', 'week', 'weeks', 'н', 'неделя', 'недели', 'недель'], true) => $amount * 604800,
            default => 0,
        };

        return $seconds > 0 ? time() + $seconds : null;
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

