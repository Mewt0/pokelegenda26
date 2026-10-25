<?php
use Pokemon8\View\View;

$itemIconIndexPath = APP_ROOT . '/public/img/items/index.json';
$itemIconIndex = is_file($itemIconIndexPath)
    ? (json_decode((string) file_get_contents($itemIconIndexPath), true) ?: [])
    : [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Инвентарь - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-items.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="items-page" data-csrf="<?= View::e((string) $csrf) ?>">
  <main class="page">
    <div class="head">
      <div>
        <a href="/game">Игровой мир</a>
        <span class="muted"> / Инвентарь</span>
      </div>
      <div class="muted">Всего предметов: <span id="totalCount"><?= View::e((string) $total) ?></span></div>
    </div>
    <section class="window">
      <header class="toolbar">
        <button class="ico" type="button">☰</button>
        <button class="ico" type="button"><span class="icon icon--ui-settings" aria-hidden="true"></span></button>
        <button class="ico" type="button"><span class="icon icon--ui-mode-general" aria-hidden="true"></span></button>
        <button class="ico" type="button">✦</button>
        <button class="ico" type="button">⚑</button>
        <button class="ico" type="button"><span class="icon icon--ui-pve-on" aria-hidden="true"></span></button>
        <button class="ico" type="button">⚒</button>
        <button class="ico" type="button">❖</button>
        <div class="search">
          <input id="searchInput" placeholder="Начните вводить название">
          <button class="ico" id="searchReset" type="button" aria-label="Очистить поиск"><span class="icon icon--chat-clear" aria-hidden="true"></span></button>
        </div>
      </header>
      <div class="grid-wrap">
        <section class="inv-grid" id="invGrid"></section>
      </div>
      <footer class="bottombar">
        <div class="nav">
          <button id="refreshBtn" type="button" aria-label="Обновить список">⟳</button>
          <button id="prevPageBtn" type="button" aria-label="Предыдущая страница">≪</button>
          <button id="nextPageBtn" type="button" aria-label="Следующая страница">≫</button>
        </div>
        <div class="act">
          <button id="dropBtn" type="button" disabled aria-label="Выбросить предмет">🖐</button>
          <button id="useBtn" type="button" disabled aria-label="Использовать предмет">✋</button>
        </div>
        <div class="slots">СЛОТОВ ЗАНЯТО: <span id="occupiedCount">0</span></div>
        <div class="gem">💎 +</div>
      </footer>
    </section>

    <div class="side">
      <section class="card">
        <h3>Выбранный предмет</h3>
        <div class="preview">
          <img id="previewImg" src="/img/blank.gif" alt="">
          <div>
            <div class="nm" id="previewName">Ничего не выбрано</div>
            <div class="ds" id="previewDesc">Выбери слот, чтобы увидеть детали.</div>
          </div>
        </div>
        <div class="controls">
          <input id="amountDrop" type="number" min="1" placeholder="Кол-во применить">
          <input id="amountClan" type="number" min="1" placeholder="Кол-во (клан)">
          <button id="clanBtn" type="button" disabled>Отдать клану</button>
          <button id="dressBtn" type="button" disabled>Одеть</button>
          <button id="undressBtn" type="button">Снять</button>
          <button id="openBtn" type="button" disabled>Открыть</button>
          <button id="commissionBtn" type="button">В лавку</button>
        </div>
      </section>
      <section class="card">
        <h3>Пагинация и покемон</h3>
        <div class="controls">
          <select id="pokemonSelect">
            <?php foreach ($pokemons as $pokemon): ?>
              <option
                value="<?= View::e((string) ($pokemon['id'] ?? '0')) ?>"
                data-item-id="<?= View::e((string) ($pokemon['equipped_item_id'] ?? '0')) ?>"
                data-item-name="<?= View::e((string) ($pokemon['equipped_item_name'] ?? '')) ?>"
              ><?= View::e(strip_tags((string) ($pokemon['names'] ?? 'Покемон'))) ?></option>
            <?php endforeach; ?>
          </select>
          <input id="pageInfo" value="1/1" readonly>
        </div>
        <nav class="pager" id="pager"></nav>
        <div id="toast" class="hint"></div>
      </section>
    </div>
  </main>
  <script src="<?= View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script type="application/json" id="itemIconIndex"><?= json_encode($itemIconIndex, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}' ?></script>
  <script src="<?= View::e(asset_url('/public/js/game-items-page.js')) ?>"></script>
</body>
</html>
