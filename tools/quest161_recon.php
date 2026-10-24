<?php
declare(strict_types=1);

/** Что нужно для квестов 161-162: характер «смелый» и счётчик побед. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

echo "== характер у покемонов: колонка har, значения ==\n";
foreach ($db->query('SELECT har, COUNT(*) c FROM pok_user GROUP BY har ORDER BY c DESC LIMIT 8') as $row) {
    echo "  har={$row['har']}  записей={$row['c']}\n";
}

$tables = array_map('strval', $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));
foreach ($tables as $table) {
    if (preg_match('/har|character|nature|temp/i', $table)) {
        echo "\n-- $table --\n";
        $columns = array_map(
            static fn (array $r): string => (string) $r['Field'],
            $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)
        );
        echo 'колонки: ' . implode(', ', $columns) . "\n";
        foreach ($db->query("SELECT * FROM `$table` LIMIT 12") as $row) {
            echo '  ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

echo "\n== счётчики побед у игрока ==\n";
foreach ($db->query('SELECT id, pve, pvp FROM users WHERE id = 1') as $row) {
    echo json_encode($row) . "\n";
}
foreach ($tables as $table) {
    if (preg_match('/battle|win|stat/i', $table)) {
        echo "  таблица-кандидат: $table (" . (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() . ")\n";
    }
}
