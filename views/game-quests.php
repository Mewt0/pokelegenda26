<?php
use Pokemon8\View\View;

$title = (string) ($module['title'] ?? 'Квесты');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title) ?> - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/game-shell.css?v=20260527-bug-reporter-2">
  <link rel="stylesheet" href="/public/css/quest-journal.css?v=20260527-quest-mockup2">
</head>
<body class="quest-journal-body">
  <?php
    $questMode = 'standalone';
    $questRootId = 'questPage';
    require APP_ROOT . '/views/components/quest-journal-panel.php';
  ?>
  <script src="/public/js/quest-journal.js?v=20260527-quest-mockup"></script>
</body>
</html>
