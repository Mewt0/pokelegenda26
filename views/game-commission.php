<?php
use Pokemon8\View\View;

$commissionMode = 'standalone';
$commissionRootId = 'commissionPage';
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Комиссионная лавка - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/game-shell.css">
  <link rel="stylesheet" href="/public/css/commission-market.css?v=20260527-my-lots">
</head>
<body>
  <?php require APP_ROOT . '/views/components/commission-market-panel.php'; ?>
  <script src="/public/js/commission-market.js?v=20260527-my-lots"></script>
</body>
</html>
