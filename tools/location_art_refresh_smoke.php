<?php
declare(strict_types=1);

// SELECT-only checks of actual location formatting; no player movement or /game/state calls.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/src/Support/Autoload.php';
$root = dirname(__DIR__);
Pokemon8\Support\Env::load($root . '/.env');
$db = Pokemon8\Database\Connection::make(require $root . '/config/database.php');
$content = Pokemon8\Game\LocationContentRepository::fromFile($root . '/config/location_content.php');
$service = new Pokemon8\Game\LocationStateService(new Pokemon8\Repository\LocationRepository($db),
    new Pokemon8\Game\LocationGraph([]), $root, $content);
$format = new ReflectionMethod($service, 'formatLocation');
$plane = new ReflectionMethod($service, 'formatPlaneLocation');
$imageFor = new ReflectionMethod($service, 'imageFor');
$mapping = require $root . '/config/location_image_overrides.php';
$results = [];
$rows = [];
foreach ($db->query('SELECT id, title, tipe FROM build ORDER BY id') as $location) {
    $id = (int) $location['id'];
    $state = $id === 95001 ? $plane->invoke($service, 0, $location) : $format->invoke($service, $location);
    $expected = '/img/room/generated-20261025/' . ($mapping[$id] ?? 'MISSING');
    $dimensions = is_file($root . $state['image']) ? getimagesize($root . $state['image']) : false;
    $ok = $state['image'] === $expected && $dimensions !== false && $dimensions[0] >= 1000
        && $dimensions['mime'] === 'image/webp';
    $results[] = ['check' => 'location.' . $id, 'pass' => $ok, 'image' => $state['image']];
    $rows[] = ['id' => $id, 'title' => $state['title'], 'image' => $state['image']];
    unset($mapping[$id]);
}
$results[] = ['check' => 'manifest.no_orphan_ids', 'pass' => $mapping === []];
foreach ([['../.env'], ['/etc/passwd'], ['missing.webp'], [123]] as $bad) {
    $catalog = new Pokemon8\Game\LocationImageCatalog($root, [1 => $bad[0]]);
    $results[] = ['check' => 'catalog.reject.' . json_encode($bad[0]), 'pass' => $catalog->pathFor(1) === null];
}
$results[] = ['check' => 'catalog.unmapped_id', 'pass' => (new Pokemon8\Game\LocationImageCatalog($root))->pathFor(-1) === null];
$results[] = ['check' => 'fallback.configured', 'pass' => $imageFor->invoke($service, -1, '/img/room/003.png') === '/img/room/003.png'];
$results[] = ['check' => 'fallback.default', 'pass' => $imageFor->invoke($service, -1, null) === '/img/room/001.png'];
$expectedArena = ['electric', 'fire', 'grass', 'rock', 'water', 'ground', 'psychic', 'fighting'];
foreach ($expectedArena as $index => $element) {
    $results[] = ['check' => 'arena.' . $element,
        'pass' => $imageFor->invoke($service, 80 + $index, null) === '/img/room/generated-20261025/arena-' . $element . '.webp'];
}
$failed = count(array_filter($results, static fn(array $r): bool => !$r['pass']));
$out = $root . '/storage/location-image-audit-20261025';
file_put_contents($out . '/current-locations.json', json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
file_put_contents($out . '/smoke.json', json_encode(['checked' => count($results), 'failed' => $failed, 'results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Public, static art review page contains only location names/IDs and public image paths.
$html = '<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>PokemonChic — новые локации</title>'
    . '<style>body{margin:0;background:#152427;color:#f5f1e7;font:16px system-ui,sans-serif}header{padding:24px}h1{margin:0 0 10px;font-size:28px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,290px),1fr));gap:16px;padding:0 24px 24px}figure{margin:0;background:#22383a;border-radius:12px;overflow:hidden}img{display:block;width:100%;aspect-ratio:3/2;object-fit:cover}figcaption{padding:12px}small{color:#b8d1cb}p{color:#c4dbd5}</style>'
    . '<header><h1>PokemonChic: обновлённый мир</h1><p>155 локаций · рисованные фоны в стиле Pokémon · без изменений инвентаря и спрайтов покемонов</p></header><main class="grid">';
foreach ($rows as $row) {
    $html .= '<figure><img loading="lazy" src="' . htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') . '" alt=""><figcaption>'
        . htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8') . '<br><small>Локация #' . $row['id'] . '</small></figcaption></figure>';
}
$html .= '</main></html>';
file_put_contents($root . '/img/room/generated-20261025/preview.html', $html);
printf("Location art: %d/%d checks PASS; %d locations; failed=%d\n", count($results) - $failed, count($results), count($rows), $failed);
exit($failed > 0 ? 1 : 0);
