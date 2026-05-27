<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Новый пароль - Pokemon 8.0</title>
  <style>
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:#eef4fb; color:#172033; font:15px/1.5 Arial,Tahoma,sans-serif; }
    .panel { width:min(520px, calc(100% - 28px)); background:#fff; border:1px solid #d5e2f2; border-radius:8px; padding:22px; box-shadow:0 18px 46px rgba(28,44,68,.14); }
    h1 { margin:0 0 10px; font-size:26px; }
    p { margin:0 0 16px; color:#5b6f8b; }
    form { display:grid; gap:10px; }
    input, button { height:42px; border:1px solid #cfdcec; border-radius:7px; padding:0 12px; font:inherit; }
    button { background:#1d66c2; color:#fff; font-weight:800; cursor:pointer; }
  </style>
</head>
<body>
  <main class="panel">
    <h1>Новый пароль</h1>
    <p>Аккаунт: <b><?= View::e($login ?? '') ?></b></p>
    <form action="/password/reset" method="post">
      <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
      <input type="hidden" name="token" value="<?= View::e($token) ?>">
      <input name="PASSWORD" type="password" maxlength="72" autocomplete="new-password" placeholder="Новый пароль" required>
      <input name="PASSWORD_REPEAT" type="password" maxlength="72" autocomplete="new-password" placeholder="Повтор пароля" required>
      <button type="submit">Сменить пароль</button>
    </form>
  </main>
</body>
</html>
