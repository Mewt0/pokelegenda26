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
  <title>Комиссионная лавка - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-shell.css', '/public/css/commission-market.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body>
  <?php require APP_ROOT . '/views/components/commission-market-panel.php'; ?>
  <script src="<?= View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script src="<?= View::e(asset_url('/public/js/commission-market.js')) ?>"></script>
</body>
</html>
