<?php
declare(strict_types=1);

/**
 * Готовит материалы для внешнего исполнителя: дампы таблиц атак в CSV и вырезки
 * движка, где применяются особые эффекты.
 *
 * Usage: php tools/export_attacks_pack.php <папка>
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$root = dirname(__DIR__);
$out = $argv[1] ?? ($root . '/storage/attacks-pack');
if (!is_dir($out)) {
    mkdir($out, 0777, true);
}

// 1. Дампы таблиц в CSV (заголовок + строки, разделитель ;, UTF-8).
foreach (['attac_power', 'attac_dop', 'stat_attak'] as $table) {
    $handle = fopen($out . '/' . $table . '.csv', 'wb');
    $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows !== []) {
        fputcsv($handle, array_keys($rows[0]), ';');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }
    }
    fclose($handle);
    echo "$table: " . count($rows) . " строк -> $table.csv\n";
}

// 2. Вырезки движка: методы, где применяются эффекты атак.
$engine = (string) file_get_contents($root . '/src/Game/BattleEngineService.php');
$lines = explode("\n", $engine);
$wanted = ['applySpecialMoveEffect', 'applyNamedTrainingEffect', 'applyStageEffect', 'applyMove', 'applyMissMoveEffect', 'hazardKind'];
$excerpt = [];
foreach ($wanted as $method) {
    foreach ($lines as $index => $line) {
        if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $line)) {
            continue;
        }
        $excerpt[] = "// ===== $method (строка " . ($index + 1) . ") =====";
        $depth = 0;
        $started = false;
        for ($i = $index; $i < min(count($lines), $index + 400); $i++) {
            $excerpt[] = $lines[$i];
            $depth += substr_count($lines[$i], '{');
            $depth -= substr_count($lines[$i], '}');
            if (str_contains($lines[$i], '{')) {
                $started = true;
            }
            if ($started && $depth <= 0) {
                break;
            }
        }
        $excerpt[] = '';
        break;
    }
}
file_put_contents($out . '/BattleEngineService_excerpts.php.txt', implode("\n", $excerpt));
echo 'вырезки движка: ' . strlen(implode("\n", $excerpt)) . " байт\n";

// 3. Список работающих атак — то, по чему исполнитель выяснит смысл номеров эффектов.
$catalog = (string) file_get_contents($root . '/src/Game/BattleMoveEffectCatalog.php');
$key = static fn (string $name): string => trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9]+/i', ' ', strtolower(trim($name)))));
$codeKeys = $key($catalog . $engine);

$sql = "SELECT a.atac_id, a.atac_name, a.atac_not, a.dop_effect, a.stati, a.chans_effect, a.chans_dop, a.critic,
               d.dop_effc, d.tip_s, d.setting,
               d.def d_def, d.atc d_atc, d.sdef d_sdef, d.satc d_satc, d.speed d_speed, d.acc d_acc,
               (SELECT COUNT(*) FROM stat_attak s WHERE s.id_atk = a.atac_id) stat_rows
          FROM attac_power a
          LEFT JOIN attac_dop d ON d.id_attc = a.atac_id
         WHERE a.atac_id IN (SELECT id_attc FROM attac_dop UNION SELECT id_atk FROM stat_attak)
         ORDER BY a.atac_id";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$working = [];
$broken = [];
foreach ($rows as $row) {
    $name = (string) $row['atac_name'];
    if ($name === '') {
        continue;
    }
    if (str_contains($codeKeys, $key($name))) {
        $working[] = $row;
    } else {
        $broken[] = $row;
    }
}

$writeCsv = static function (string $path, array $rows): void {
    $fp = fopen($path, 'wb');
    if ($rows !== []) {
        fputcsv($fp, array_keys($rows[0]), ';');
        foreach ($rows as $row) {
            fputcsv($fp, $row, ';');
        }
    }
    fclose($fp);
};
$writeCsv($out . '/attacks_working.csv', $working);
$writeCsv($out . '/attacks_broken.csv', $broken);

echo 'работающих: ' . count($working) . ', неработающих: ' . count($broken) . "\n";
echo "готово: $out\n";
