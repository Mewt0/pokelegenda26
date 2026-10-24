<?php
$mailMode = 'standalone';
$mailRootId = 'mailPage';
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Почта - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-shell.css', '/public/css/mail-overlay.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body>
  <?php require APP_ROOT . '/views/components/mail-panel.php'; ?>
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/mail-overlay.js')) ?>"></script>
</body>
</html>
