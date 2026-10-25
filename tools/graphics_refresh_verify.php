<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$out = $root . '/storage/location-image-audit-20261025';
$entries = json_decode((string) file_get_contents($root . '/docs/reports/GRAPHICS_REFRESH_ASSETS_2026-10-25.json'), true, 512, JSON_THROW_ON_ERROR);
$checks = [];
$ui = [];
$totalBytes = 0;
foreach ($entries as $entry) {
    $scene = !isset($entry['destination']);
    $path = $entry['destination'] ?? ('img/room/generated-20261025/' . $entry['key'] . '.webp');
    $dims = @getimagesize($root . '/' . $path);
    $totalBytes += is_file($root . '/' . $path) ? filesize($root . '/' . $path) : 0;
    $ok = $dims && ($scene ? $dims['mime'] === 'image/webp' && $dims[0] === 1200
        : $dims['mime'] === 'image/png' && $dims[0] === $entry['size'] && $dims[1] === $entry['size']);
    if (!$scene && $dims) {
        $im = imagecreatefrompng($root . '/' . $path);
        $transparent = 0;
        for ($y = 0; $y < imagesy($im); $y++) {
            for ($x = 0; $x < imagesx($im); $x++) {
                if (((imagecolorat($im, $x, $y) >> 24) & 127) >= 120) { $transparent++; }
            }
        }
        $ok = $ok && $transparent > imagesx($im) * imagesy($im) * 0.05;
        $ui[] = ['key' => $entry['key'], 'path' => '/' . $path, 'transparent_pixels' => $transparent];
    }
    $checks[] = ['check' => 'asset.' . $entry['key'], 'pass' => (bool) $ok];
}
$baseline = json_decode((string) file_get_contents($out . '/protected-hashes.json'), true, 512, JSON_THROW_ON_ERROR);
$changed = [];
foreach ($baseline as $entry) {
    if (!is_file($entry['path']) || hash_file('sha256', $entry['path']) !== strtolower($entry['sha256'])) { $changed[] = $entry['path']; }
}
$checks[] = ['check' => 'protected_files.' . count($baseline), 'pass' => $changed === []];

// Contact sheet: each icon at source, 32px and 24px, against both UI surfaces.
$sheet = imagecreatetruecolor(1000, (int) ceil(count($ui) / 4) * 140);
$light = imagecolorallocate($sheet, 244, 242, 232);
$dark = imagecolorallocate($sheet, 25, 43, 45);
$ink = imagecolorallocate($sheet, 20, 25, 25);
imagefill($sheet, 0, 0, $light);
foreach ($ui as $index => $icon) {
    $x = ($index % 4) * 250;
    $y = intdiv($index, 4) * 140;
    imagefilledrectangle($sheet, $x + 125, $y, $x + 249, $y + 110, $dark);
    $im = imagecreatefrompng($root . $icon['path']);
    foreach ([[$x + 5, 80], [$x + 88, 24], [$x + 132, 64], [$x + 205, 24]] as [$left, $size]) {
        imagecopyresampled($sheet, $im, $left, $y + 10, 0, 0, $size, $size, imagesx($im), imagesy($im));
    }
    imagestring($sheet, 3, $x + 5, $y + 115, $icon['key'], $ink);
}
imagepng($sheet, $out . '/ui-contact-sheet.png');

foreach (array_chunk(array_values(array_filter($entries, static fn(array $r): bool => !isset($r['destination']))), 20) as $page => $scenes) {
    $landscape = imagecreatetruecolor(1280, 1200);
    imagefill($landscape, 0, 0, $dark);
    $label = imagecolorallocate($landscape, 242, 239, 225);
    foreach ($scenes as $index => $scene) {
        $x = ($index % 4) * 320;
        $y = intdiv($index, 4) * 240;
        $im = imagecreatefromwebp($root . '/img/room/generated-20261025/' . $scene['key'] . '.webp');
        imagecopyresampled($landscape, $im, $x, $y, 0, 0, 310, 207, imagesx($im), imagesy($im));
        imagestring($landscape, 4, $x + 5, $y + 214, $scene['key'], $label);
    }
    imagepng($landscape, $out . '/environment-contact-' . ($page + 1) . '.png');
}

// Browser fixture executes the actual renderer/helper functions, without game API or login.
$source = (string) file_get_contents($root . '/public/js/game-start-runtime.js');
if (!preg_match('/const WORLD_ICON_VERSION = ([^;]+);/', $source, $versionMatch)) { throw new RuntimeException('Missing icon version'); }
$versionDeclaration = 'const WORLD_ICON_VERSION=' . $versionMatch[1] . ';';
$functions = '';
foreach (['iconMarkup', 'decorativeIcon', 'npcIconClass', 'routeIconClass', 'assetIcon', 'npcIconSrc', 'routeIconSrc', 'renderNpcs', 'renderMoves'] as $name) {
    if (!preg_match('/    function ' . $name . '\([^\n]*\{[\s\S]*?\n    }\r?\n/', $source, $match)) { throw new RuntimeException('Missing renderer ' . $name); }
    $functions .= $match[0] . "\n";
}
$npcTitles = ['Покецентр', 'Покемаркет', 'Наставник', 'Задание', 'Путешественник', 'Профессор', 'Коллекционер', 'Цветочный прилавок', 'Художник', 'Тренер', 'Старая женщина', 'Касса', 'Торговец'];
$npcIcons = ['cross', 'shop', 'mentor', 'quest', 'person', 'person', 'person', 'person', 'person', 'person', 'person', 'person', 'npc'];
$npcs = [];
foreach ($npcTitles as $index => $title) { $npcs[] = ['title' => $title, 'icon' => $npcIcons[$index]]; }
$moves = [];
foreach (['Город Алабастия', 'Лес', 'Дорога 1', 'Пещера', 'Озеро', 'Гора', 'Пляж', 'Порт', 'Лаборатория', 'Стадион', 'Администрация', 'Тюрьма', 'Дом', 'Электростанция', 'Теплоход', 'Вертолет', 'Пустыня', 'Мост', 'Парк', 'Неизвестная область'] as $index => $title) {
    $moves[] = ['id' => $index + 1, 'title' => $title];
}
$fixture = '<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>PokemonChic — проверка графики</title>';
foreach (['fonts', 'tokens', 'base', 'icons', 'game-start', 'game-shell'] as $css) {
    $fixture .= '<link rel="stylesheet" href="/public/css/' . $css . '.css?v=' . filemtime($root . '/public/css/' . $css . '.css') . '">';
}
$fixture .= '<style>html,body{height:auto!important;overflow:auto!important}body{padding:24px;background:#152427;color:#f5f1e7;font:16px system-ui}h1{font-size:26px}h2{margin:20px 0 10px;font-size:20px}#npcs,#moves{display:flex;flex-wrap:wrap;gap:8px;position:static}#npcs .npc-btn,#moves .move-btn{width:auto;min-height:44px}section{padding:16px;background:#22383a;border-radius:12px;margin:16px 0}.small-icons{display:flex;gap:14px;flex-wrap:wrap}.small-icons img{width:24px;height:24px;object-fit:contain}.sample{display:flex;gap:12px;align-items:center;padding:10px;background:#f4f2e8;border-radius:8px;color:#152427}button{cursor:pointer}#fallback .icon{width:32px;height:32px}</style>'
    . '<h1>Новая графика PokémonChic</h1><p>Изолированная проверка настоящих функций интерфейса без игровых запросов и изменения БД.</p><section><h2>NPC</h2><div id="npcPanel"></div><div id="npcs" class="npc-actions"></div></section>'
    . '<section><h2>Переходы</h2><div id="moves"></div></section><section><h2>Справочники — 24 px</h2><div class="small-icons">';
foreach (['pokedex', 'attackdex', 'info', 'evolution', 'egg', 'moves', 'habitat'] as $key) {
    $fixture .= '<span class="sample"><img src="/public/img/ui/dex/' . $key . '.png?v=20261025" alt="">' . $key . '</span>';
}
$fixture .= '</div></section><section><h2>Ошибка загрузки → SVG</h2><span id="fallback"></span><p id="class-check"></p><p id="click-result">Нажмите NPC или переход для проверки обработчика.</p></section>'
    . '<script>const state={activeNpc:false};' . $versionDeclaration . 'function openNpc(npc){document.getElementById("click-result").textContent="NPC: "+npc.title}function moveTo(id){document.getElementById("click-result").textContent="Маршрут: "+id}'
    . $functions . '\nrenderNpcs(' . json_encode($npcs, JSON_UNESCAPED_UNICODE) . ');renderMoves(' . json_encode($moves, JSON_UNESCAPED_UNICODE) . ');'
    . 'document.getElementById("fallback").appendChild(decorativeIcon("/img/room/generated-20261025/intentionally-missing.png","npc-person"));'
    . 'const cases=[["Твинлиф-Таун","town"],["Литтлрут-Таун","town"],["Туннель","cave"],["Пустошь","desert"],["Вертолёт","air"]];const passed=cases.filter(([title,key])=>routeIconSrc(title).includes("/locations/"+key+".png")).length;document.getElementById("class-check").textContent="Распознавание маршрутов: "+passed+"/"+cases.length;</script></html>';
// Single-quoted PHP strings do not interpret \n; use a real separator in inline script.
$fixture = str_replace('\\nrenderNpcs', "\nrenderNpcs", $fixture);
file_put_contents($root . '/img/room/generated-20261025/icons-preview.html', $fixture);
$failed = count(array_filter($checks, static fn(array $r): bool => !$r['pass']));
$report = ['assets' => count($entries), 'protected_files' => count($baseline), 'protected_changed' => $changed,
    'installed_bytes' => $totalBytes, 'checked' => count($checks), 'failed' => $failed, 'checks' => $checks];
file_put_contents($out . '/graphics-verification.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
printf("Graphics: %d assets; %d protected files; changed=%d; checks %d/%d PASS; bytes=%d\n", count($entries), count($baseline), count($changed), count($checks) - $failed, count($checks), $totalBytes);
exit($failed > 0 ? 1 : 0);
