<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title ?? 'Аккаунт') ?> - Pokemon 8.0</title>
  <style>
    body { margin:0; min-height:100vh; display:grid; place-items:center; background:#eef4fb; color:#172033; font:15px/1.5 Arial,Tahoma,sans-serif; }
    .panel { width:min(520px, calc(100% - 28px)); background:#fff; border:1px solid #d5e2f2; border-radius:8px; padding:22px; box-shadow:0 18px 46px rgba(28,44,68,.14); }
    h1 { margin:0 0 12px; font-size:26px; }
    p { margin:0 0 16px; color:#40536f; }
    .bad { color:#b12626; }
    a { color:#1d66c2; font-weight:700; text-decoration:none; }
  </style>
</head>
<body>
  <main class="panel">
    <h1><?= View::e($title ?? 'Аккаунт') ?></h1>
    <p class="<?= !empty($bad) ? 'bad' : '' ?>"><?= View::e($message ?? '') ?></p>
    <a href="/">На главную</a>
  </main>
</body>
</html>
