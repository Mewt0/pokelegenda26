<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0 - Главная</title>
  <style>
    :root {
      --bg: #f4f7fb;
      --panel: #ffffff;
      --line: #d8e2ee;
      --text: #172033;
      --muted: #66758a;
      --accent: #1d66c2;
      --green: #16845f;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      background: linear-gradient(135deg, #f8fbff, #eef4f2);
      color: var(--text);
      font: 15px/1.5 Arial, Tahoma, sans-serif;
    }
    header, main { width: min(1120px, calc(100% - 32px)); margin: 0 auto; }
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding: 22px 0;
    }
    .brand { font-size: 28px; font-weight: 800; }
    .panel {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 18px;
      box-shadow: 0 14px 40px rgba(28, 44, 68, .1);
    }
    .grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
    .login { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .auth-stack { display: grid; gap: 14px; }
    .auth-card { border-top: 1px solid #edf1f7; padding-top: 12px; }
    .auth-card:first-child { border-top: 0; padding-top: 0; }
    .auth-card h2 { margin: 0 0 8px; font-size: 16px; }
    .auth-card p { margin: 0 0 10px; }
    .auth-card form { display: grid; gap: 8px; }
    .auth-card input, .auth-card button { width: 100%; }
    input, button {
      height: 38px;
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 0 10px;
      font: inherit;
    }
    button {
      background: var(--accent);
      color: #fff;
      font-weight: 700;
      cursor: pointer;
    }
    a { color: var(--accent); text-decoration: none; font-weight: 700; }
    a:hover { text-decoration: underline; }
    .news article + article { margin-top: 14px; border-top: 1px solid #edf1f7; padding-top: 14px; }
    .news h1, aside h2 { margin-top: 0; }
    .rank-row { display: flex; justify-content: space-between; gap: 12px; padding: 5px 0; border-bottom: 1px solid #edf1f7; }
    .rank-row b { color: var(--green); }
    .muted { color: var(--muted); }
    @media (max-width: 860px) {
      header, .grid { display: block; }
      .panel { margin-bottom: 14px; }
      .login input, .login button { width: 100%; }
    }
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
      <div class="login-extra"><a href="/password/forgot">Забыли пароль?</a></div>
    <?php endif; ?>
  </div>
</header>
<main class="grid">
  <section class="panel news">
    <h1>Новости</h1>
    <article>
      <h2>Новая версия проекта</h2>
      <p>Это стартовая страница нового ядра Pokemon 8.0. Старые маршруты постепенно заменяются нормальными контроллерами, сервисами и API.</p>
    </article>
    <article>
      <h2>Порядок миграции</h2>
      <p>Сначала авторизация, база, роутинг и игровой экран. Затем карта, чат, бои, инвентарь, квесты и админка.</p>
    </article>
  </section>
  <aside class="panel">
    <?php if (!$userLogin): ?>
      <div class="auth-stack">
        <section class="auth-card">
          <h2>Регистрация</h2>
          <p class="muted">Почта временно необязательна. Её можно будет добавить позже в профиле.</p>
          <form action="/register" method="post">
            <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
            <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин" required>
            <input name="PASSWORD" type="password" maxlength="72" autocomplete="new-password" placeholder="Пароль" required>
            <input name="PASSWORD_REPEAT" type="password" maxlength="72" autocomplete="new-password" placeholder="Повтор пароля" required>
            <input name="EMAIL" type="email" maxlength="100" autocomplete="email" placeholder="Email, необязательно">
            <button type="submit">Создать аккаунт</button>
          </form>
        </section>
      </div>
    <?php endif; ?>
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
