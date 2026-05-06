<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Админка - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/admin-panel.css">
</head>
<body>
  <main class="admin-shell" data-csrf="<?= View::e($csrf) ?>">
    <header class="admin-hero">
      <a class="admin-back" href="/game">Игровой мир</a>
      <div>
        <p class="admin-kicker">Новая панель управления</p>
        <h1>Админка</h1>
        <p>Переносим старые инструменты в новый слой: контроллеры, репозитории, JSON API, CSRF и нормальную БД.</p>
      </div>
    </header>

    <section class="admin-stats" id="adminStats">
      <?php foreach ($overview as $key => $value): ?>
        <article>
          <span><?= View::e((string) $key) ?></span>
          <b><?= number_format((int) $value, 0, ',', ' ') ?></b>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="admin-grid">
      <aside class="admin-card admin-nav">
        <h2>Разделы</h2>
        <button type="button" class="is-active" data-admin-tab="items">Предметы</button>
        <button type="button" data-admin-tab="drops">Дроп</button>
        <button type="button" data-admin-tab="legacy">Карта переноса</button>
      </aside>

      <section class="admin-card admin-panel is-active" data-admin-panel="items">
        <div class="admin-section-head">
          <div>
            <h2>Предметы</h2>
            <p>Создание и правка предметов без ручного SQL. Иконка берется из `/public/img/items`.</p>
          </div>
          <form class="admin-search" id="itemSearchForm">
            <input name="q" type="search" placeholder="ID или название">
            <button type="submit">Найти</button>
          </form>
        </div>

        <form class="admin-form" id="itemForm">
          <input type="hidden" name="_csrf" value="<?= View::e($csrf) ?>">
          <label>ID <input name="id" type="number" placeholder="пусто = новый ID"></label>
          <label>Название <input name="name" required placeholder="Например: Камень рассвета"></label>
          <label>Описание <textarea name="tittle" rows="3" placeholder="Что делает предмет"></textarea></label>
          <label>Иконка <input name="icon_file" placeholder="например 90110.png"></label>
          <label>Категория <input name="category" type="number" value="0"></label>
          <label>Используется <select name="uses"><option value="0">нет</option><option value="1">да</option></select></label>
          <label>Надевается <select name="dress"><option value="0">нет</option><option value="1">да</option></select></label>
          <label>Боевой предмет <select name="battleuse"><option value="0">нет</option><option value="1">да</option></select></label>
          <label>Можно торговать <select name="torg"><option value="0">да</option><option value="1">нет</option></select></label>
          <label>Метка <input name="dopolnen" value="admin"></label>
          <button type="submit">Сохранить предмет</button>
        </form>

        <div class="admin-status" id="itemStatus"></div>
        <div class="admin-list" id="itemList"></div>
      </section>

      <section class="admin-card admin-panel" data-admin-panel="drops">
        <div class="admin-section-head">
          <div>
            <h2>Дроп предметов</h2>
            <p>Правило описывает предмет, шанс, количество, время, локацию и источник дропа.</p>
          </div>
        </div>

        <form class="admin-form" id="dropForm">
          <input type="hidden" name="_csrf" value="<?= View::e($csrf) ?>">
          <input type="hidden" name="id">
          <label>Предмет ID <input name="item_id" type="number" required placeholder="например 3"></label>
          <label>Локация <select name="location_id" data-lookup="locations"><option value="0">Любая</option></select></label>
          <label>Конкретный слот дикого покемона <select name="pokebuild_id" data-lookup="wildSlots"><option value="0">Любой</option></select></label>
          <label>Базовый покемон <select name="pokemon_base_id" data-lookup="pokemon"><option value="0">Любой</option></select></label>
          <label>Источник <select name="source_type"><option value="wild">Дикий покемон</option><option value="trainer">Тренер</option><option value="npc">NPC</option><option value="event">Ивент</option></select></label>
          <label>Шанс, % <input name="chance_percent" type="number" min="0.0001" max="100" step="0.0001" required value="5"></label>
          <label>Мин. количество <input name="min_count" type="number" min="1" value="1"></label>
          <label>Макс. количество <input name="max_count" type="number" min="1" value="1"></label>
          <label>С <input name="time_start" type="time" value="00:00"></label>
          <label>До <input name="time_end" type="time" value="23:59"></label>
          <label>Quest ID <input name="quest_id" type="number" value="0"></label>
          <label>Quest process <input name="quest_process" type="number" value="0"></label>
          <label>Quest complete <input name="quest_complete" type="number" value="0"></label>
          <label>Включено <select name="enabled"><option value="1">да</option><option value="0">нет</option></select></label>
          <label>Заметка <input name="note" placeholder="для себя"></label>
          <button type="submit">Сохранить правило</button>
        </form>

        <div class="admin-status" id="dropStatus"></div>
        <div class="admin-list" id="dropList"></div>
      </section>

      <section class="admin-card admin-panel" data-admin-panel="legacy">
        <h2>Карта переноса старой админки</h2>
        <div class="admin-legacy-list">
          <?php foreach ($legacyModules as $module): ?>
            <article>
              <b><?= View::e($module['title']) ?></b>
              <span><?= View::e($module['status']) ?></span>
              <small><?= View::e($module['source']) ?></small>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </section>
  </main>
  <script src="/public/js/admin-panel.js"></script>
</body>
</html>
