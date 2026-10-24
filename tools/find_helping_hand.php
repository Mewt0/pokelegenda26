<?php
declare(strict_types=1);

/** Ищем «Руку помощи» в данных атак: как она там названа и что у неё в полях. */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$patterns = ['Help%', '%Рука%', '%рука%', '%помощ%', '%Помощ%', 'Hand%'];
foreach ($patterns as $pattern) {
    $stmt = $db->prepare('SELECT atac_id, atac_name, atac_power, atac_not, dop_effect, priorety, atac_tip, atac_categori, atac_goal FROM attac_power WHERE atac_name LIKE :p LIMIT 10');
    $stmt->execute(['p' => $pattern]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows !== []) {
        echo "== LIKE $pattern ==\n";
        foreach ($rows as $row) {
            echo '  ' . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

echo "\n== атаки со словом помощи в русском написании (первые 20 названий) ==\n";
foreach ($db->query('SELECT atac_id, atac_name FROM attac_power ORDER BY atac_id LIMIT 40') as $row) {
    echo "  {$row['atac_id']}: {$row['atac_name']}\n";
}
