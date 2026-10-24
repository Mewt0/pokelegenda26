<?php
declare(strict_types=1);

/**
 * Проверка «атак с приколом»: какие эффекты объявлены в данных и какие из них
 * вообще обрабатываются движком боя.
 *
 * Ищем таблицу атак, собираем уникальные эффекты/статусы и проверяем, встречается
 * ли каждое значение в BattleEngineService / каталоге эффектов. Значение, которого
 * нет в коде, — это атака, которую игрок использует, а она ничего не делает.
 *
 * Usage: php tools/attack_effects_audit.php
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$root = dirname(__DIR__);
$engine = (string) file_get_contents($root . '/src/Game/BattleEngineService.php');
$catalogFile = $root . '/src/Game/BattleMoveEffectCatalog.php';
$catalog = is_file($catalogFile) ? (string) file_get_contents($catalogFile) : '';
echo 'движок: ' . strlen($engine) . ' байт, каталог эффектов: ' . strlen($catalog) . " байт\n";

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
$attackTables = [];
foreach ($tables as $table) {
    if (preg_match('/^(atac|atack|attack|moves?|stat_attak|attac_dop)$/i', $table)) {
        $columns = array_map(
            static fn (array $r): string => (string) $r['Field'],
            $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
        );
        $count = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $attackTables[$table] = $columns;
        echo "\n-- $table ($count строк)\nколонки: " . implode(', ', $columns) . "\n";
    }
}

if ($attackTables === []) {
    echo "\nтаблица атак по имени не найдена. Кандидаты по упоминанию в движке:\n";
    if (preg_match_all('/FROM\s+`?([a-z_0-9]+)`?/i', $engine, $matches)) {
        foreach (array_unique($matches[1]) as $name) {
            if (!in_array($name, $tables, true)) {
                continue;
            }
            echo "  $name\n";
        }
    }
    exit(0);
}

foreach ($attackTables as $table => $columns) {
    // Сначала — упоминается ли сама таблица/колонки в движке: это надёжнее, чем
    // искать значение (число вроде «1» встречается в любом файле и даёт ложное «ОК»).
    foreach (['dop_effc', 'tip_s', 'setting', $table] as $needle) {
        $inEngine = str_contains($engine, $needle);
        $inCatalog = $catalog !== '' && str_contains($catalog, $needle);
        echo str_pad($table . '.' . $needle, 26) . (($inEngine || $inCatalog) ? 'упоминается в коде' : 'В КОДЕ НЕ НАЙДЕНО') . "\n";
    }

    $effectColumns = array_values(array_filter($columns, static fn (string $c): bool => (bool) preg_match('/effect|not|status|tip|prio|special|dop/i', $c)));
    foreach ($effectColumns as $column) {
        $values = $db->query("SELECT `$column` v, COUNT(*) c FROM `$table` GROUP BY `$column` ORDER BY c DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
        echo "  $column: " . implode(', ', array_map(static fn (array $r): string => $r['v'] . '(' . $r['c'] . ')', $values)) . "\n";
    }
}

// Какие атаки вообще имеют дополнительные эффекты и что это за атаки.
echo "\n== атаки с дополнительными эффектами (attac_dop) ==\n";
try {
    $sql = 'SELECT a.id_attc, a.dop_effc, a.tip_s, a.setting, n.name
              FROM attac_dop a
              LEFT JOIN attac n ON n.id = a.id_attc
             LIMIT 25';
    foreach ($db->query($sql) as $row) {
        echo '  ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
} catch (Throwable $error) {
    echo '  не удалось соединить с таблицей атак: ' . $error->getMessage() . "\n";
    foreach ($db->query('SELECT * FROM attac_dop LIMIT 8') as $row) {
        echo '  ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

