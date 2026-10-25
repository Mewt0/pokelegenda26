<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$directory = $root . '/public/img/items/ai-20261025';
$version = (string)filemtime(__FILE__);
$catalogue = json_decode(file_get_contents($directory . '/catalogue.json'), true, 512, JSON_THROW_ON_ERROR);
$index = json_decode(file_get_contents($root . '/public/img/items/index.json'), true, 512, JSON_THROW_ON_ERROR);
$escape = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$cards = ''; $updated = 0;
foreach ($catalogue as $item) {
    $id = (int)$item['id'];
    $relative = (string)($index[$id] ?? ($id . '.png'));
    $file = $root . '/public/img/items/' . $relative;
    if (!is_file($file)) { throw new RuntimeException('Missing active item icon: ' . $id); }
    $new = str_starts_with($relative, 'ai-20261025/');
    if ($new) { $updated++; }
    $src = $escape('/public/img/items/' . $relative . '?v=' . filemtime($file));
    $cards .= '<article data-id="' . $id . '"><h2>#' . $id . ' ' . $escape($item['name']) . '</h2><div class="media"><img src="' . $src . '" alt="' . $escape($item['name']) . '"></div><p class="state ' . ($new ? 'ready' : 'pending') . '">' . ($new ? 'Новый рисунок в игре' : 'Ожидает перерисовки') . '</p><div class="sizes"><img width="32" height="32" src="' . $src . '" alt="32 px"><img width="48" height="48" src="' . $src . '" alt="48 px"><img width="60" height="60" src="' . $src . '" alt="60 px"></div><p class="scale">32 / 48 / 60 px</p></article>';
}
file_put_contents($directory . '/preview.html', '<!doctype html><html lang="ru"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Предметы PokemonChic</title><link rel="stylesheet" href="preview.css?v=' . $version . '"><header><p class="eyebrow">POKEMONCHIC · ПРЕДМЕТЫ</p><h1>Предметы в игре</h1><p>Новые рисунки: ' . $updated . ' из ' . count($catalogue) . '. Все иконки — 128×128, в едином масштабе.</p><label for="itemSearch">Найти предмет</label><input id="itemSearch" type="search" placeholder="Название или #ID"><p id="searchStatus" role="status"></p></header><main>' . $cards . '</main><script src="preview.js?v=' . $version . '"></script></html>');
file_put_contents($directory . '/preview.js', 'document.getElementById("itemSearch").addEventListener("input",function(){const q=this.value.trim().toLocaleLowerCase("ru");let count=0;document.querySelectorAll("article").forEach(card=>{const match=q.startsWith("#")?card.dataset.id===q.slice(1):card.querySelector("h2").textContent.toLocaleLowerCase("ru").includes(q);card.hidden=!match;if(match)count++});document.getElementById("searchStatus").textContent=q?"Найдено: "+count:""});');
file_put_contents($directory . '/preview.css', 'body{margin:0;background:#eef2f7;color:#253044;font:15px system-ui}header{padding:28px;max-width:1440px;margin:auto}.eyebrow{color:#427b65;font-size:12px;font-weight:700;letter-spacing:.12em}h1{font-size:32px;margin:8px 0}header p{color:#596b80}label{display:block;font-size:13px;margin:18px 0 7px}input{box-sizing:border-box;width:min(100%,480px);padding:12px 14px;border:1px solid #aebed0;border-radius:9px;font:inherit;background:white;color:#253044}input:focus-visible{outline:3px solid #b4d9ca;outline-offset:2px}main{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;padding:0 28px 28px}article{padding:18px;background:white;border:1px solid #d7e0ec;border-radius:14px}h2{font-size:15px;min-height:40px;margin:0 0 10px}.media{height:150px;display:grid;place-items:center;background:#f2f6fc;border:1px solid #e0e7f0;border-radius:12px}.media img{width:140px;height:140px;object-fit:contain}.state{font-size:12px;text-align:center}.ready{color:#327251}.pending{color:#6e7380}.sizes{display:flex;justify-content:center;align-items:center;gap:16px;margin-top:18px;height:64px}.sizes img{object-fit:contain}.scale{text-align:center;font-size:12px;color:#596b80;margin-bottom:0}@media(max-width:600px){header{padding:20px}main{padding:0 16px 20px}h1{font-size:25px}}');
echo 'Game catalogue: ' . count($catalogue) . '; new artwork: ' . $updated . PHP_EOL;

