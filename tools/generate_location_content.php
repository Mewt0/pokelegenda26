<?php
declare(strict_types=1);

use Pokemon8\Game\LegacyRoomDataExtractor;

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/src/Support/Autoload.php';

$extractor = new LegacyRoomDataExtractor(APP_ROOT . '/include/rooms');
$locations = [];

foreach (glob(APP_ROOT . '/include/rooms/*.php') ?: [] as $path) {
    $id = (int) basename($path, '.php');
    if ($id <= 0) {
        continue;
    }

    $room = $extractor->room($id);
    if ($room['name'] === null && $room['about'] === null && $room['image'] === null && $room['npcs'] === []) {
        continue;
    }

    $locations[$id] = $room;
}

ksort($locations);

$export = var_export($locations, true);
$content = <<<PHP
<?php
declare(strict_types=1);

// Generated from include/rooms/*.php by tools/generate_location_content.php.
// Do not edit text here by hand unless legacy source is already gone.
return $export;

PHP;

file_put_contents(APP_ROOT . '/config/location_content.php', $content);

echo 'Generated config/location_content.php with ' . count($locations) . ' locations.' . PHP_EOL;
