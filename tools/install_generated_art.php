<?php
declare(strict_types=1);

/** Export built-in imagegen results; original generated PNGs remain in the local image library. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$options = getopt('', ['catalog:', 'apply']);
$root = dirname(__DIR__);
$catalog = json_decode((string) file_get_contents($options['catalog'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
$apply = isset($options['apply']);
$backup = $root . '/storage/location-image-audit-20261025/original-ui';
$allowedUi = ['public/img/ui/menu-pokedex.png', 'public/img/ui/menu-attackdex.png'];
foreach (['pc','shop','mentor','quest','person','professor','collector','flower','artist','trainer','elder','ticket','default'] as $key) {
    $allowedUi[] = "public/img/ui/world/npcs/$key.png";
}
foreach (['town','forest','road','cave','water','mountain','beach','port','lab','stadium','admin','prison','house','power','ship','air','desert','bridge','park','default'] as $key) {
    $allowedUi[] = "public/img/ui/world/locations/$key.png";
}
foreach (['pokedex','attackdex','info','evolution','egg','moves','habitat'] as $key) {
    $allowedUi[] = "public/img/ui/dex/$key.png";
}
$results = [];
foreach ($catalog as $entry) {
    $scene = !isset($entry['destination']);
    if (!preg_match('/\A[a-z0-9-]+\z/D', $entry['key'])) { throw new RuntimeException('Invalid asset key'); }
    $destination = $entry['destination'] ?? ('img/room/generated-20261025/' . $entry['key'] . '.webp');
    if (!$scene && !in_array($destination, $allowedUi, true)) { throw new RuntimeException('Destination outside refresh scope'); }
    $source = imagecreatefrompng($entry['path']);
    if (!$source) { throw new RuntimeException('Cannot decode generated PNG'); }
    $width = $scene ? 1200 : (int) $entry['size'];
    $height = $scene ? (int) round(imagesy($source) * $width / imagesx($source)) : $width;
    $target = imagecreatetruecolor($width, $height);
    imagealphablending($target, false);
    imagesavealpha($target, true);
    imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
    imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
    if ($apply) {
        if (!is_dir(dirname($root . '/' . $destination))) { mkdir(dirname($root . '/' . $destination), 0775, true); }
        if (!$scene && is_file($root . '/' . $destination) && !is_file($backup . '/' . $destination)) {
            if (!is_dir(dirname($backup . '/' . $destination))) { mkdir(dirname($backup . '/' . $destination), 0775, true); }
            copy($root . '/' . $destination, $backup . '/' . $destination);
        }
        $temporary = $root . '/' . $destination . '.refreshing';
        $ok = $scene ? imagewebp($target, $temporary, 85) : imagepng($target, $temporary, 9);
        if (!$ok || !rename($temporary, $root . '/' . $destination)) { throw new RuntimeException('Export failed'); }
    }
    $results[] = ['key' => $entry['key'], 'destination' => $destination, 'width' => $width, 'height' => $height,
        'bytes' => $apply ? filesize($root . '/' . $destination) : null,
        'sha256' => $apply ? hash_file('sha256', $root . '/' . $destination) : null];
}
if ($apply) {
    file_put_contents($options['catalog'] . '.installed.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
echo ($apply ? 'Exported ' : 'Preview ') . count($results) . " assets; original UI copies: $backup\n";
