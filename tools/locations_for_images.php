<?php
declare(strict_types=1);

/** Список локаций для подбора картинок: id, название, тип и признак «нужна картинка». */
require __DIR__ . '/../src/Support/Autoload.php';

Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');

$configPath = dirname(__DIR__) . '/config/location_content.php';
$content = is_file($configPath) ? require $configPath : [];
$configured = [];
foreach ((array) $content as $id => $entry) {
    $image = '';
    if (is_array($entry)) {
        foreach (['image', 'img', 'picture', 'photo'] as $key) {
            if (isset($entry[$key]) && is_string($entry[$key]) && $entry[$key] !== '') {
                $image = $entry[$key];
                break;
            }
        }
    }
    $configured[(int) $id] = $image;
}

// Сколько локаций делят одну картинку и где файла нет вовсе.
$usage = [];
foreach ($configured as $id => $image) {
    if ($image !== '') {
        $usage[$image][] = $id;
    }
}

$rows = [];
$contentById = [];
foreach ((array) $content as $id => $entry) {
    $contentById[(int) $id] = is_array($entry) ? $entry : [];
}
foreach ($db->query('SELECT id, title, tipe FROM build ORDER BY id') as $row) {
    $id = (int) $row['id'];
    $image = $configured[$id] ?? '';
    $relative = ltrim($image, '/');
    $exists = $image !== '' && (is_file(dirname(__DIR__) . '/' . $relative) || is_file(dirname(__DIR__) . '/public/' . $relative));
    $shared = $image !== '' ? count($usage[$image] ?? []) : 0;

    // Описание локации — источник для поискового запроса картинки: в названии
    // смысла мало («Тёмный лес» и «Лес» неразличимы), а в тексте он есть.
    $about = (string) ($contentById[$id]['about'] ?? '');

    $reason = 'есть своя картинка';
    if ($image === '') {
        $reason = 'картинки нет';
    } elseif (!$exists) {
        $reason = 'файла нет (битый путь)';
    } elseif ($shared >= 3) {
        $reason = "одна картинка на $shared локаций";
    }

    $rows[] = [
        'id' => $id,
        'title' => (string) $row['title'],
        'tipe' => (string) $row['tipe'],
        'about' => $about,
        'image' => $image,
        'file_exists' => $exists,
        'shared_by' => $shared,
        'needs_image' => $image === '' || !$exists || $shared >= 3,
        'reason' => $reason,
    ];
}

$out = dirname(__DIR__) . '/docs/design/locations.json';
file_put_contents($out, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$need = array_values(array_filter($rows, static fn (array $r): bool => $r['needs_image']));
echo 'всего локаций: ' . count($rows) . PHP_EOL;
echo 'нужна картинка: ' . count($need) . PHP_EOL;
echo 'записано: ' . $out . PHP_EOL;
foreach (array_slice($need, 0, 10) as $row) {
    echo "  {$row['id']} | {$row['title']} — {$row['reason']}\n";
}
