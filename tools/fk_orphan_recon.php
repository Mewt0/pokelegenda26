<?php
declare(strict_types=1);

use Pokemon8\Database\Connection;
use Pokemon8\Support\Env;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/src/Support/Autoload.php';

Env::load(APP_ROOT . '/.env');
$db = Connection::make(require APP_ROOT . '/config/database.php');
$json = in_array('--json', $argv, true);

/**
 * @var array<int, array{
 *   name:string,
 *   child_table:string,
 *   child_column:string,
 *   parent_table:string,
 *   parent_column:string,
 *   zero_is_empty:bool,
 *   null_is_empty:bool,
 *   note:string
 * }>
 */
$relations = [
    [
        'name' => 'fk_items_users_user_id_users_id',
        'child_table' => 'items_users',
        'child_column' => 'user_id',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Inventory owner must be a live user.',
    ],
    [
        'name' => 'fk_items_users_item_id_items_id',
        'child_table' => 'items_users',
        'child_column' => 'item_id',
        'parent_table' => 'items',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Inventory stack must point to an item definition.',
    ],
    [
        'name' => 'fk_pok_user_users_users_id',
        'child_table' => 'pok_user',
        'child_column' => 'users',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Owned pokemon must point to a live user or the system reserve user.',
    ],
    [
        'name' => 'fk_pok_user_basenum_poke_base_id',
        'child_table' => 'pok_user',
        'child_column' => 'basenum',
        'parent_table' => 'poke_base',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Owned pokemon species must point to a base pokemon definition.',
    ],
    [
        'name' => 'fk_eggs_users_egg_users_id',
        'child_table' => 'eggs',
        'child_column' => 'users_egg',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Egg owner must point to a live user or the system reserve user.',
    ],
    [
        'name' => 'fk_battles_user_1_users_id',
        'child_table' => 'battles',
        'child_column' => 'user_1',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => true,
        'null_is_empty' => true,
        'note' => 'Battle participant 1. Zero can mean no player in legacy rows.',
    ],
    [
        'name' => 'fk_battles_user_2_users_id',
        'child_table' => 'battles',
        'child_column' => 'user_2',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => true,
        'null_is_empty' => true,
        'note' => 'Battle participant 2. Zero can mean PvE/wild side in legacy rows.',
    ],
    [
        'name' => 'fk_market_lots_seller_user_id_users_id',
        'child_table' => 'market_lots',
        'child_column' => 'seller_user_id',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Commission lot seller must be a live user.',
    ],
    [
        'name' => 'fk_market_lots_buyer_user_id_users_id',
        'child_table' => 'market_lots',
        'child_column' => 'buyer_user_id',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => true,
        'null_is_empty' => true,
        'note' => 'Buyer can be empty until the lot is purchased.',
    ],
    [
        'name' => 'fk_market_reserved_objects_owner_user_id_users_id',
        'child_table' => 'market_reserved_objects',
        'child_column' => 'owner_user_id',
        'parent_table' => 'users',
        'parent_column' => 'id',
        'zero_is_empty' => false,
        'null_is_empty' => true,
        'note' => 'Reserved market object owner must be a live user.',
    ],
];

$result = [
    'generated_at' => date(DATE_ATOM),
    'database' => (string) $db->query('SELECT DATABASE()')->fetchColumn(),
    'relations' => [],
];

foreach ($relations as $relation) {
    $result['relations'][] = inspectRelation($db, $relation);
}

if ($json) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(0);
}

echo "FK orphan recon: database={$result['database']} generated_at={$result['generated_at']}\n";
foreach ($result['relations'] as $relation) {
    echo sprintf(
        "\n[%s] %s.%s -> %s.%s\n",
        $relation['status'],
        $relation['child']['table'],
        $relation['child']['column'],
        $relation['parent']['table'],
        $relation['parent']['column']
    );
    echo sprintf(
        "  engines: child=%s parent=%s; columns: child=%s parent=%s; index=%s; existing_fk=%s\n",
        $relation['child']['engine'] ?? 'missing',
        $relation['parent']['engine'] ?? 'missing',
        $relation['child']['column_type'] ?? 'missing',
        $relation['parent']['column_type'] ?? 'missing',
        $relation['child']['has_index'] ? 'yes' : 'no',
        $relation['existing_fk'] ? 'yes' : 'no'
    );
    echo sprintf(
        "  rows=%d orphan_rows=%d null_rows=%d zero_rows=%d ignored_empty_rows=%d\n",
        $relation['child']['rows'],
        $relation['orphan_rows'],
        $relation['null_rows'],
        $relation['zero_rows'],
        $relation['ignored_empty_rows']
    );
    echo "  note: {$relation['note']}\n";
    if ($relation['samples'] !== []) {
        echo "  samples:\n";
        foreach ($relation['samples'] as $sample) {
            echo '    - ' . json_encode($sample, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        }
    }
}

/**
 * @param array{name:string,child_table:string,child_column:string,parent_table:string,parent_column:string,zero_is_empty:bool,null_is_empty:bool,note:string} $relation
 * @return array<string, mixed>
 */
function inspectRelation(PDO $db, array $relation): array
{
    $childTable = $relation['child_table'];
    $parentTable = $relation['parent_table'];
    $childColumn = $relation['child_column'];
    $parentColumn = $relation['parent_column'];
    $childMeta = tableMeta($db, $childTable, $childColumn);
    $parentMeta = tableMeta($db, $parentTable, $parentColumn);

    $report = [
        'name' => $relation['name'],
        'status' => 'missing_schema',
        'note' => $relation['note'],
        'child' => $childMeta,
        'parent' => $parentMeta,
        'existing_fk' => false,
        'orphan_rows' => 0,
        'null_rows' => 0,
        'zero_rows' => 0,
        'ignored_empty_rows' => 0,
        'samples' => [],
    ];

    if (!$childMeta['table_exists'] || !$parentMeta['table_exists'] || !$childMeta['column_exists'] || !$parentMeta['column_exists']) {
        return $report;
    }

    $report['existing_fk'] = hasForeignKey($db, $childTable, $childColumn, $parentTable, $parentColumn);
    $report['null_rows'] = countWhere($db, $childTable, "`$childColumn` IS NULL");
    $report['zero_rows'] = countWhere($db, $childTable, "`$childColumn` = 0");
    $empty = [];
    if ($relation['null_is_empty']) {
        $empty[] = "`c`.`$childColumn` IS NULL";
    }
    if ($relation['zero_is_empty']) {
        $empty[] = "`c`.`$childColumn` = 0";
    }
    $emptySql = $empty === [] ? '0' : '(' . implode(' OR ', $empty) . ')';
    $report['ignored_empty_rows'] = countOrphans($db, $relation, true);
    $report['orphan_rows'] = countOrphans($db, $relation, false);
    $report['samples'] = orphanSamples($db, $relation, $emptySql);

    $report['status'] = $report['orphan_rows'] === 0 ? 'clean' : 'requires_review';
    if (!$childMeta['has_index'] || !$parentMeta['has_index'] || ($childMeta['column_type'] ?? '') !== ($parentMeta['column_type'] ?? '')) {
        $report['status'] = $report['orphan_rows'] === 0 ? 'requires_schema_review' : 'requires_review';
    }
    if (($childMeta['engine'] ?? '') !== 'InnoDB' || ($parentMeta['engine'] ?? '') !== 'InnoDB') {
        $report['status'] = 'requires_engine_review';
    }

    return $report;
}

/**
 * @return array<string, mixed>
 */
function tableMeta(PDO $db, string $table, string $column): array
{
    $engineStmt = $db->prepare(
        'SELECT ENGINE, TABLE_ROWS
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
    );
    $engineStmt->execute(['table' => $table]);
    $tableRow = $engineStmt->fetch();

    $columnStmt = $db->prepare(
        'SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
    );
    $columnStmt->execute(['table' => $table, 'column' => $column]);
    $columnRow = $columnStmt->fetch();

    return [
        'table' => $table,
        'column' => $column,
        'table_exists' => $tableRow !== false,
        'column_exists' => $columnRow !== false,
        'engine' => $tableRow['ENGINE'] ?? null,
        'estimated_rows' => isset($tableRow['TABLE_ROWS']) ? (int) $tableRow['TABLE_ROWS'] : null,
        'rows' => $tableRow === false ? 0 : countRows($db, $table),
        'column_type' => $columnRow['COLUMN_TYPE'] ?? null,
        'nullable' => ($columnRow['IS_NULLABLE'] ?? '') === 'YES',
        'column_key' => $columnRow['COLUMN_KEY'] ?? null,
        'has_index' => $columnRow !== false && hasIndex($db, $table, $column),
    ];
}

function countRows(PDO $db, string $table): int
{
    return (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

function countWhere(PDO $db, string $table, string $where): int
{
    return (int) $db->query("SELECT COUNT(*) FROM `$table` WHERE $where")->fetchColumn();
}

/**
 * @param array<string, mixed> $relation
 */
function countOrphans(PDO $db, array $relation, bool $onlyIgnoredEmpty): int
{
    $childTable = $relation['child_table'];
    $childColumn = $relation['child_column'];
    $parentTable = $relation['parent_table'];
    $parentColumn = $relation['parent_column'];
    $empty = [];
    if ($relation['null_is_empty']) {
        $empty[] = "`c`.`$childColumn` IS NULL";
    }
    if ($relation['zero_is_empty']) {
        $empty[] = "`c`.`$childColumn` = 0";
    }
    $emptySql = $empty === [] ? '0' : '(' . implode(' OR ', $empty) . ')';
    $modeSql = $onlyIgnoredEmpty ? $emptySql : "NOT $emptySql";

    return (int) $db->query(
        "SELECT COUNT(*)
         FROM `$childTable` c
         LEFT JOIN `$parentTable` p ON p.`$parentColumn` = c.`$childColumn`
         WHERE p.`$parentColumn` IS NULL AND $modeSql"
    )->fetchColumn();
}

/**
 * @param array<string, mixed> $relation
 * @return array<int, array<string, mixed>>
 */
function orphanSamples(PDO $db, array $relation, string $emptySql): array
{
    $childTable = $relation['child_table'];
    $childColumn = $relation['child_column'];
    $parentTable = $relation['parent_table'];
    $parentColumn = $relation['parent_column'];
    $columns = sampleColumns($db, $childTable);
    $selects = [];
    foreach ($columns as $column) {
        $selects[] = "`c`.`$column` AS `$column`";
    }
    if ($selects === []) {
        $selects[] = "`c`.`$childColumn` AS `$childColumn`";
    }

    $sql = sprintf(
        'SELECT %s
         FROM `%s` c
         LEFT JOIN `%s` p ON p.`%s` = c.`%s`
         WHERE p.`%s` IS NULL AND NOT %s
         LIMIT 20',
        implode(', ', $selects),
        $childTable,
        $parentTable,
        $parentColumn,
        $childColumn,
        $parentColumn,
        $emptySql
    );

    return $db->query($sql)->fetchAll() ?: [];
}

/**
 * @return array<int, string>
 */
function sampleColumns(PDO $db, string $table): array
{
    $preferred = ['id', 'user_id', 'users', 'users_egg', 'item_id', 'basenum', 'user_1', 'user_2', 'seller_user_id', 'buyer_user_id', 'owner_user_id', 'date', 'time', 'created_at', 'updated_at'];
    $stmt = $db->prepare(
        'SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
    );
    $stmt->execute(['table' => $table]);
    $available = array_flip(array_column($stmt->fetchAll(), 'COLUMN_NAME'));
    $columns = [];
    foreach ($preferred as $column) {
        if (isset($available[$column])) {
            $columns[] = $column;
        }
    }
    return array_values(array_unique($columns));
}

function hasIndex(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column'
    );
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

function hasForeignKey(PDO $db, string $childTable, string $childColumn, string $parentTable, string $parentColumn): bool
{
    $stmt = $db->prepare(
        'SELECT COUNT(*)
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :child_table
           AND COLUMN_NAME = :child_column
           AND REFERENCED_TABLE_NAME = :parent_table
           AND REFERENCED_COLUMN_NAME = :parent_column'
    );
    $stmt->execute([
        'child_table' => $childTable,
        'child_column' => $childColumn,
        'parent_table' => $parentTable,
        'parent_column' => $parentColumn,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}
