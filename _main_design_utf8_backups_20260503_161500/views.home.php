<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f4f7fb; color: #172033; }
    header, main { max-width: 1120px; margin: 0 auto; padding: 24px; }
    header { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .brand { font-size: 28px; font-weight: 700; }
    .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 18px; }
    .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; align-items: start; }
    input, button { font: inherit; padding: 8px 10px; }
    button { cursor: pointer; }
    .login { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .news article + article { margin-top: 14px; border-top: 1px solid #edf1f7; padding-top: 14px; }
    .rank-row { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; }
  </style>
</head>
<body>
<header>
  <div class="brand">Pokemon 8.0</div>
  <div class="panel">
    <?php if ($userLogin): ?>
      Привет, <?= View::e($userLogin) ?> |
      <a href="/game">В игру</a> |
      <a href="/logout">Выход</a>
    <?php else: ?>
      <form class="login" action="/login" method="post">
        <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
        <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин">
        <input name="PASSWORD" type="password" maxlength="72" autocomplete="current-password" placeholder="Пароль">
        <button type="submit">Войти</button>
      </form>
    <?php endif; ?>
  </div>
</header>
<main class="grid">
  <section class="panel news">
    <h1>Новости</h1>
    <article>
      <h2>Новая версия проекта</h2>
      <p>Это стартовая страница нового ядра Pokemon 8.0. Старая логика будет переноситься сюда постепенно, без костылей и дублей.</p>
    </article>
    <article>
      <h2>Порядок миграции</h2>
      <p>Сначала авторизация, база, роутинг и игровые экраны. Затем карта, чат, бои, инвентарь и админка.</p>
    </article>
  </section>
  <aside class="panel">
    <h2>Топ бойцов</h2>
    <?php foreach ($fighters as $index => $fighter): ?>
      <div class="rank-row">
        <span><?= $index + 1 ?>. <?= View::e($fighter['login'] ?? ('ID ' . $fighter['id'])) ?></span>
        <b><?= number_format((int) ($fighter['rang_b'] ?? 0), 0, '.', ' ') ?></b>
      </div>
    <?php endforeach; ?>
    <h2>Покедекс</h2>
    <?php foreach ($pokedex as $index => $row): ?>
      <div class="rank-row">
        <span><?= $index + 1 ?>. <?= View::e($row['login'] ?? ('ID ' . $row['id'])) ?></span>
        <b><?= (int) ($row['count_poke'] ?? 0) ?></b>
      </div>
    <?php endforeach; ?>
  </aside>
</main>
</body>
</html>
