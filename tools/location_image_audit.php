<?php
declare(strict_types=1);

// Read-only world inventory, using the same resolution order as LocationStateService.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/src/Support/Autoload.php';
$root = dirname(__DIR__);
Pokemon8\Support\Env::load($root . '/.env');
$db = Pokemon8\Database\Connection::make(require $root . '/config/database.php');
$content = Pokemon8\Game\LocationContentRepository::fromFile($root . '/config/location_content.php');
$rows = [];
foreach ($db->query('SELECT id, title, tipe FROM build ORDER BY id') as $location) {
    $id = (int) $location['id'];
    $room = $content->find($id);
    $configured = $room['image'];
    $numbered = sprintf('/img/room/%03d.png', $id);
    $image = $configured && is_file($root . $configured) ? $configured
        : (is_file($root . $numbered) ? $numbered : '/img/room/001.png');
    if ($id === 95001) { $image = '/img/room/001.png'; }
    $rows[] = ['id' => $id, 'title' => $room['name'] ?: $location['title'],
        'about' => strip_tags($room['about'] ?? ''), 'configured' => $configured,
        'image' => $image, 'sha256' => is_file($root . $image) ? hash_file('sha256', $root . $image) : null];
}
$out = $root . '/storage/location-image-audit-20261025';
if (!is_dir($out)) { mkdir($out, 0775, true); }
file_put_contents($out . '/inventory.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$font = 'C:/Windows/Fonts/arial.ttf';
foreach (array_chunk($rows, 24) as $page => $group) {
    $sheet = imagecreatetruecolor(1200, 1140);
    $bg = imagecolorallocate($sheet, 240, 242, 245);
    $fg = imagecolorallocate($sheet, 20, 25, 30);
    imagefill($sheet, 0, 0, $bg);
    foreach ($group as $index => $row) {
        $x = ($index % 4) * 300;
        $y = intdiv($index, 4) * 190;
        $bytes = @file_get_contents($root . $row['image']);
        $source = $bytes !== false ? @imagecreatefromstring($bytes) : false;
        if ($source !== false) {
            imagecopyresampled($sheet, $source, $x, $y, 0, 0, 290, 140, imagesx($source), imagesy($source));
        }
        imagettftext($sheet, 11, 0, $x + 3, $y + 156, $fg, $font, $row['id'] . ': ' . mb_substr($row['title'], 0, 32));
        imagettftext($sheet, 9, 0, $x + 3, $y + 174, $fg, $font, mb_substr($row['image'], 0, 47));
    }
    imagepng($sheet, $out . '/sheet-' . ($page + 1) . '.png');
}
echo count($rows) . " locations; inventory and sheets: $out\n";
