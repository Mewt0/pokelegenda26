<?php
$mailMode = 'standalone';
$mailRootId = 'mailPage';
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Почта - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/game-shell.css">
  <link rel="stylesheet" href="/public/css/mail-overlay.css?v=20260603-game-mail3">
</head>
<body>
  <?php require APP_ROOT . '/views/components/mail-panel.php'; ?>
  <script src="/public/js/mail-overlay.js?v=20260603-game-mail3"></script>
</body>
</html>
