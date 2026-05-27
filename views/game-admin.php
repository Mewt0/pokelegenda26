<?php
use Pokemon8\View\View;

$adminCssVersion = @filemtime(__DIR__ . '/../public/css/admin-panel.css') ?: time();
$adminJsVersion = @filemtime(__DIR__ . '/../public/js/admin-panel.js') ?: time();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Админка - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/admin-panel.css?v=<?= (int) $adminCssVersion ?>">
</head>
<body>
  <main class="admin-shell" data-csrf="<?= View::e($csrf) ?>">
    <header class="admin-topbar">
      <a class="admin-back" href="/game">Игровой мир</a>
      <div>
        <p class="admin-kicker">Новая панель управления</p>
        <h1>Админка</h1>
      </div>
      <form class="admin-global-search" id="adminSearchForm">
        <input id="adminSearchInput" type="search" placeholder="Поиск в текущем разделе">
        <button type="submit">Найти</button>
      </form>
    </header>

    <section class="admin-stats" id="adminStats">
      <?php foreach ($overview as $key => $value): ?>
        <article>
          <span><?= View::e((string) $key) ?></span>
          <b><?= number_format((int) $value, 0, ',', ' ') ?></b>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="admin-layout">
      <aside class="admin-card admin-nav" id="adminNav" aria-label="Разделы админки">
        <span class="admin-nav-group">Операции</span>
        <button type="button" class="is-active" data-admin-tab="dashboard">Дашборд</button>
        <button type="button" data-admin-tab="users">Пользователи</button>
        <button type="button" data-admin-tab="commission">Комиссионная лавка</button>
        <button type="button" data-admin-tab="moderation">Модерация</button>
        <button type="button" data-admin-tab="settings">Система</button>
        <span class="admin-nav-group">Контент</span>
        <button type="button" data-admin-tab="items">Предметы</button>
        <button type="button" data-admin-tab="market">Покемаркет</button>
        <button type="button" data-admin-tab="drops">Дроп</button>
        <button type="button" data-admin-tab="wild">Дикие слоты</button>
        <button type="button" data-admin-tab="locations">Локации</button>
        <button type="button" data-admin-tab="pokemon">Покемоны</button>
        <button type="button" data-admin-tab="attacks">Атаки</button>
        <button type="button" data-admin-tab="news">Новости</button>
        <span class="admin-nav-group">Ивенты</span>
        <button type="button" data-admin-tab="bosses">Боссы</button>
        <button type="button" data-admin-tab="events">Ивенты/бусты</button>
        <button type="button" data-admin-tab="tournaments">Турниры</button>
        <button type="button" data-admin-tab="medals">Медали</button>
        <span class="admin-nav-group">Перенос</span>
        <button type="button" data-admin-tab="legacy">Legacy-карта</button>
      </aside>

      <section class="admin-card admin-workspace">
        <div class="admin-section-head">
          <div>
            <h2 id="adminTitle">Дашборд</h2>
            <p id="adminSubtitle">Обзор живого состояния игры и последние действия.</p>
          </div>
          <div class="admin-actions">
            <button type="button" id="adminRefresh">Обновить</button>
            <button type="button" id="adminCreate">Создать</button>
          </div>
        </div>
        <div class="admin-status" id="adminStatus"></div>
        <form class="admin-filterbar" id="adminFilterForm" hidden></form>
        <div class="admin-table-wrap">
          <table class="admin-table" id="adminTable">
            <thead></thead>
            <tbody></tbody>
          </table>
        </div>
        <div class="admin-pager" id="adminPager" hidden>
          <button type="button" id="adminPrevPage">Назад</button>
          <span id="adminPageInfo">1/1</span>
          <button type="button" id="adminNextPage">Дальше</button>
        </div>
        <div class="admin-legacy-list" id="adminLegacy" hidden>
          <?php foreach ($legacyModules as $module): ?>
            <article>
              <b><?= View::e($module['title']) ?></b>
              <span><?= View::e($module['status']) ?></span>
              <small><?= View::e($module['source']) ?></small>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <aside class="admin-card admin-inspector" id="adminInspector">
        <h2 id="inspectorTitle">Инспектор</h2>
        <p id="inspectorHint">Выберите строку или создайте новую запись.</p>
        <form class="admin-form" id="adminForm"></form>
        <div class="admin-danger" id="adminDanger"></div>
      </aside>
    </section>
  </main>
  <script src="/public/js/admin-panel.js?v=<?= (int) $adminJsVersion ?>"></script>
</body>
</html>
