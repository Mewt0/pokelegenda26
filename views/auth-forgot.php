<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Восстановление пароля - Pokemon 8.0</title>
  <style>
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:#eef4fb; color:#172033; font:15px/1.5 Arial,Tahoma,sans-serif; }
    .panel { width:min(520px, calc(100% - 28px)); background:#fff; border:1px solid #d5e2f2; border-radius:8px; padding:22px; box-shadow:0 18px 46px rgba(28,44,68,.14); }
    h1 { margin:0 0 10px; font-size:26px; }
    p { margin:0 0 16px; color:#5b6f8b; }
    form { display:grid; gap:10px; }
    input, button { height:42px; border:1px solid #cfdcec; border-radius:7px; padding:0 12px; font:inherit; }
    button { background:#1d66c2; color:#fff; font-weight:800; cursor:pointer; }
    a { display:inline-block; margin-top:14px; color:#1d66c2; font-weight:700; text-decoration:none; }
  </style>
</head>
<body>
  <main class="panel">
    <h1>Восстановление пароля</h1>
    <p>Введите email, который привязан к аккаунту. Если у аккаунта почты нет, восстановление через email недоступно.</p>
    <form action="/password/forgot" method="post">
      <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
      <input name="EMAIL" type="email" maxlength="100" autocomplete="email" placeholder="Email" required>
      <button type="submit">Отправить ссылку</button>
    </form>
    <a href="/">Вернуться ко входу</a>
  </main>
</body>
</html>
