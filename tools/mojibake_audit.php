<?php
declare(strict_types=1);

/**
 * Р РµР»РёР·РЅС‹Р№ С‡РµРєР»РёСЃС‚: РёС‰РµРј Р±РёС‚С‹Рµ С‚РµРєСЃС‚С‹ (РґРІРѕР№РЅР°СЏ РєРѕРґРёСЂРѕРІРєР°) РІ РёСЃС…РѕРґРЅРёРєР°С… Рё РґР°РЅРЅС‹С….
 *
 * РџСЂРёР·РЅР°Рє РїРѕСЂС‡Рё: СЂСѓСЃСЃРєРёР№ С‚РµРєСЃС‚, Р·Р°РєРѕРґРёСЂРѕРІР°РЅРЅС‹Р№ UTF-8, РїСЂРѕС‡РёС‚Р°РЅРЅС‹Р№ РєР°Рє cp1251 Рё
 * СЃРЅРѕРІР° Р·Р°РїРёСЃР°РЅРЅС‹Р№ РІ UTF-8. РўРѕРіРґР° В«РљРѕР»Р»РµРєС†РёРѕРЅРµСЂВ» РІС‹РіР»СЏРґРёС‚ РєР°Рє В«Р С™Р С•Р В»Р В»Р ВµР С”РЎвЂ Р С‘Р С•Р Р…Р ВµРЎР‚В».
 *
 * Usage: php tools/mojibake_audit.php [--top 15]
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$root = dirname(__DIR__);
// РћРґРЅРѕР·РЅР°С‡РЅС‹Рµ Р±РёРіСЂР°РјРјС‹ РїРѕСЂС‡Рё: РєР°Р¶РґР°СЏ СЂСѓСЃСЃРєР°СЏ Р±СѓРєРІР° РІ РґРІРѕР№РЅРѕР№ РєРѕРґРёСЂРѕРІРєРµ СЃС‚Р°РЅРѕРІРёС‚СЃСЏ
// РїР°СЂРѕР№ В«Р В»/В«РЎВ» + СЃРёРјРІРѕР», РєРѕС‚РѕСЂРѕРіРѕ РІ РЅРѕСЂРјР°Р»СЊРЅРѕРј СЂСѓСЃСЃРєРѕРј РїРѕСЃР»Рµ РЅРёС… РЅРµ Р±С‹РІР°РµС‚
// (В°, Вµ, С‘, С•, С“, вЂљ, вЂћ, вЂ¦, вЂ , в‚¬, вЂ°, Р‰, РЉ, вЂ, вЂў, вЂ“, вЂ”, Р° С‚Р°РєР¶Рµ Р·Р°РіР»Р°РІРЅС‹Рµ Р‰ РЉ РЊ Р‹ РЏ).
// Р’РђР–РќРћ: В«РЎВ» + В«В» (РіРёР»СЊРµРјРµС‚С‹) Рё В«РЎВ» + РїСЂРѕР±РµР» вЂ” СЌС‚Рѕ РЅРѕСЂРјР°Р»СЊРЅС‹Р№ СЂСѓСЃСЃРєРёР№ С‚РµРєСЃС‚
// (В«Р›РЎВ», В«РЎРїР°СЃРёР±РѕВ»), С‚Р°РєРёРµ РїР°СЂС‹ СЃСЋРґР° РЅРµ РІС…РѕРґСЏС‚. РќР° СЌС‚РѕРј СЏ СѓР¶Рµ РґРІР°Р¶РґС‹ РѕС€РёР±Р°Р»СЃСЏ.
$markers = [
    'Р В°', 'Р Вµ', 'Р С‘', 'Р С•', 'РЎРѓ', 'РЎвЂљ', 'РЎР‚', 'Р Р†', 'Р Р…', 'Р В»', 'Р Т‘', 'Р С', 'Р С—', 'Р С”',
    'Р В±', 'Р В·', 'Р С–', 'РЎвЂЎ', 'РЎв‚¬', 'РЎР‹', 'РЎРЏ', 'РЎвЂ№', 'РЎРЉ', 'РЎС“', 'РЎвЂћ', 'РЎвЂ¦', 'РЎвЂ ', 'РЎвЂ°',
    'Р вЂ™', 'Р РЋ', 'Р Сћ', 'Р Сњ', 'Р С›', 'Р С™', 'Р Сџ', 'Р Сљ', 'Р вЂњ', 'Р вЂќ', 'Р вЂ“', 'Р вЂ”', 'Р Р€', 'Р В¤',
    'Р вЂє', 'Р Лњ', 'Р в„–', 'Р В¶',
];
$pattern = null;
$countMarkers = static function (string $text) use ($markers): int {
    $total = 0;
    foreach ($markers as $marker) {
        $total += substr_count($text, $marker);
    }
    return $total;
};

$scanDirs = ['src', 'views', 'public/js', 'public/css', 'include', 'config'];
$files = [];
foreach ($scanDirs as $dir) {
    $path = $root . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.(php|js|css)$/', $file->getFilename())) {
            $files[] = $file->getPathname();
        }
    }
}

$results = [];
foreach ($files as $file) {
    $raw = (string) file_get_contents($file);
    $count = $countMarkers($raw);
    if ($count > 0) {
        $results[str_replace($root . '/', '', str_replace('\\', '/', $file))] = $count;
    }
}

arsort($results);
echo 'С„Р°Р№Р»РѕРІ РїСЂРѕРІРµСЂРµРЅРѕ: ' . count($files) . ', СЃ РїСЂРёР·РЅР°РєР°РјРё РїРѕСЂС‡Рё: ' . count($results) . PHP_EOL . PHP_EOL;
$top = (int) (($argv[array_search('--top', $argv, true) + 1] ?? 15));
$limit = in_array('--top', $argv, true) ? $top : 15;
foreach (array_slice($results, 0, $limit, true) as $file => $count) {
    echo str_pad((string) $count, 7) . $file . PHP_EOL;
}

// РџСЂРёРјРµСЂ РёСЃРїРѕСЂС‡РµРЅРЅРѕР№ СЃС‚СЂРѕРєРё РґР»СЏ РЅР°РіР»СЏРґРЅРѕСЃС‚Рё.
foreach (array_slice($results, 0, 3, true) as $file => $count) {
    $lines = file($root . '/' . $file) ?: [];
    foreach ($lines as $number => $line) {
        if ($countMarkers($line) > 0) {
            echo PHP_EOL . $file . ':' . ($number + 1) . '  ' . trim(mb_substr($line, 0, 120)) . PHP_EOL;
            break 2;
        }
    }
}

echo PHP_EOL . "== Р±РёС‚С‹Рµ С‚РµРєСЃС‚С‹ РІ Р±Р°Р·Рµ (РєРІРµСЃС‚С‹) ==\n";
$stmt = $db->query("SELECT id, title FROM quest_definitions WHERE title REGEXP 'Р [В°-СЏ]' LIMIT 8");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($rows === []) {
    echo "РєРІРµСЃС‚С‹: С‡РёСЃС‚Рѕ\n";
}
foreach ($rows as $row) {
    echo "  #{$row['id']} {$row['title']}\n";
}
$stmt = $db->query("SELECT COUNT(*) FROM quest_steps WHERE description REGEXP 'Р [В°-СЏ]'");
echo 'С€Р°РіРѕРІ РєРІРµСЃС‚РѕРІ СЃ РїРѕСЂС‡РµР№: ' . (int) $stmt->fetchColumn() . PHP_EOL;
