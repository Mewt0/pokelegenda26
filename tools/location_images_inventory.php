<?php
declare(strict_types=1);

/**
 * Инвентарь картинок локаций: что реально висит на каждой локации, существует ли
 * файл, не переиспользуется ли одна картинка на десятке мест (признак заглушки).
 *
 * Usage: php tools/location_images_inventory.php [out.md]
 */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$configPath = dirname(__DIR__) . '/config/location_content.php';
$content = is_file($configPath) ? require $configPath : [];
if (!is_array($content)) {
    fwrite(STDERR, "конфиг не вернул массив\n");
    exit(2);
}

echo 'локаций в конфиге: ' . count($content) . PHP_EOL;
$sample = array_slice($content, 0, 1, true);
echo "пример структуры записи:\n";
foreach ($sample as $id => $entry) {
    echo "  id=$id: " . json_encode(array_keys(is_array($entry) ? $entry : []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    if (is_array($entry)) {
        foreach ($entry as $key => $value) {
            if (is_string($value)) {
                echo "    $key = $value\n";
            }
        }
    }
}

$titles = [];
foreach ($db->query('SELECT id, title FROM build') as $row) {
    $titles[(int) $row['id']] = (string) $row['title'];
}

$rows = [];
$usage = [];
foreach ($content as $id => $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $image = '';
    foreach (['image', 'img', 'picture', 'photo', 'bg', 'background'] as $key) {
        if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
            $image = $entry[$key];
            break;
        }
    }
    $usage[$image][] = (int) $id;

    $relative = ltrim($image, '/');
    // Картинки локаций лежат В КОРНЕ проекта (`img/room/...`), а не в `public/`:
    // первая версия скрипта проверяла `public/img/...` и врала, что файлов нет.
    $candidates = [
        dirname(__DIR__) . '/' . $relative,
        dirname(__DIR__) . '/public/' . $relative,
    ];
    $absolute = '';
    foreach ($candidates as $candidate) {
        if ($image !== '' && is_file($candidate)) {
            $absolute = $candidate;
            break;
        }
    }
    $exists = $absolute !== '';
    $size = $exists ? (string) filesize($absolute) : '';
    $dimensions = $exists && function_exists('getimagesize') ? (@getimagesize($absolute) ?: []) : [];

    $rows[(int) $id] = [
        'id' => (int) $id,
        'title' => $titles[(int) $id] ?? '(нет в build)',
        'image' => $image,
        'exists' => $exists ? 'да' : 'НЕТ',
        'size' => $size,
        'dim' => isset($dimensions[0]) ? $dimensions[0] . 'x' . $dimensions[1] : '',
    ];
}

// Легаси-комнаты: именно их картинки видит игрок на странице локации.
// `img/room/.png` — пустой путь, то есть битая ссылка.
$broken = [];
$roomImages = [];
foreach (glob(dirname(__DIR__) . '/include/rooms/*.php') ?: [] as $room) {
    $roomId = (int) basename($room, '.php');
    $raw = (string) file_get_contents($room);
    if (preg_match('/img\/room\/\.png/', $raw)) {
        $broken[] = (string) $roomId;
    }
    if (preg_match_all('/[\'"]([^\'"]*img\/room\/[^\'"]*)[\'"]/i', $raw, $m)) {
        foreach (array_unique($m[1]) as $path) {
            $roomImages[$roomId][] = $path;
        }
    }
}

$roomLines = [];
$roomLines[] = '';
$roomLines[] = '## Что реально показано на странице локации (картинки из легаси-комнат)';
$roomLines[] = '';
$roomLines[] = '| id | локация | картинки из комнаты | файл есть |';
$roomLines[] = '|---|---|---|---|';
foreach ($roomImages as $roomId => $paths) {
    $status = [];
    foreach ($paths as $path) {
        $candidate = dirname(__DIR__) . '/' . ltrim($path, '/');
        $status[] = is_file($candidate) ? $path : $path . ' (НЕТ ФАЙЛА)';
    }
    $roomLines[] = sprintf('| %d | %s | %s | %s |', $roomId, $titles[$roomId] ?? '', implode('<br>', $status), '');
}

$lines = [];
$lines[] = '# Картинки локаций: инвентарь';
$lines[] = '';
$lines[] = 'Сгенерировано `tools/location_images_inventory.php`.';
$lines[] = '';
$lines[] = '| id | локация | картинка | файл есть | размер | пикс. | мест с этой же картинкой |';
$lines[] = '|---|---|---|---|---|---|---|';
foreach ($rows as $row) {
    $reuse = count($usage[$row['image']] ?? []);
    $lines[] = sprintf(
        '| %d | %s | %s | %s | %s | %s | %d |',
        $row['id'],
        $row['title'],
        $row['image'] === '' ? '(пусто)' : $row['image'],
        $row['exists'],
        $row['size'],
        $row['dim'],
        $reuse
    );
}

$lines[] = '';
$lines[] = '## Легаси-комнаты с пустым путём (img/room/.png), то есть без картинки';
$lines[] = '';
$lines[] = $broken === [] ? 'нет' : implode(', ', $broken);
$lines = array_merge($lines, $roomLines);

$lines[] = '';
$lines[] = '## Одна картинка на много локаций (признак заглушки)';
$lines[] = '';
foreach ($usage as $image => $ids) {
    if (count($ids) >= 3) {
        $names = [];
        foreach (array_slice($ids, 0, 5) as $id) {
            $names[] = $titles[$id] ?? ('id ' . $id);
        }
        $lines[] = '- `' . $image . '` — ' . count($ids) . ' локаций: ' . implode(', ', $names);
    }
}

$outPath = $argv[1] ?? (dirname(__DIR__) . '/docs/design/LOCATION_IMAGES.md');
file_put_contents($outPath, implode("\n", $lines) . "\n");
echo 'записано: ' . $outPath . PHP_EOL;
echo 'строк: ' . count($rows) . ', битых путей: ' . count($broken) . PHP_EOL;
