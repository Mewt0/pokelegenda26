<?php
use Pokemon8\View\View;

$title = (string) ($module['title'] ?? 'Квесты');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title) ?> - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-shell.css', '/public/css/quest-journal.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="quest-journal-body">
  <?php
    $questMode = 'standalone';
    $questRootId = 'questPage';
    require APP_ROOT . '/views/components/quest-journal-panel.php';
  ?>
  <script src="<?= View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script src="<?= View::e(asset_url('/public/js/quest-journal.js')) ?>"></script>
</body>
</html>
