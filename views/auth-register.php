<?php
declare(strict_types=1);

use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Регистрация - Pokemon 8.0</title>
  <style>
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:#eef5fb; color:#10233d; font:16px/1.45 Arial, sans-serif; }
    main { width:min(460px, calc(100% - 32px)); background:#fff; border:1px solid #cfe0f2; border-radius:10px; box-shadow:0 18px 50px rgba(30,50,80,.14); padding:24px; }
    h1 { margin:0 0 8px; font-size:30px; }
    p { margin:0 0 18px; color:#52647d; }
    form { display:grid; gap:12px; }
    input { height:44px; border:1px solid #bfd2e9; border-radius:8px; padding:0 12px; font:inherit; }
    button, a { min-height:42px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-weight:800; text-decoration:none; }
    button { border:1px solid #1558ab; background:#1d66c2; color:#fff; cursor:pointer; }
    a { color:#1d66c2; }
    .hint { font-size:14px; color:#6a7b91; margin-top:2px; }
    .links { display:flex; justify-content:space-between; gap:12px; margin-top:16px; }
  </style>
</head>
<body>
<main>
  <h1>Регистрация</h1>
  <p>Почта временно необязательна. Её можно будет привязать позже в профиле для восстановления пароля.</p>
  <form action="/register" method="post">
    <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
    <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин" required>
    <input name="PASSWORD" type="password" maxlength="72" autocomplete="new-password" placeholder="Пароль" required>
    <input name="PASSWORD_REPEAT" type="password" maxlength="72" autocomplete="new-password" placeholder="Повтор пароля" required>
    <input name="EMAIL" type="email" maxlength="100" autocomplete="email" placeholder="Email, необязательно">
    <div class="hint">Логин: латиница, цифры, подчёркивание или дефис, 3-16 символов.</div>
    <button type="submit">Создать аккаунт</button>
  </form>
  <div class="links">
    <a href="/">На главную</a>
    <a href="/password/forgot">Восстановить пароль</a>
  </div>
</main>
</body>
</html>
