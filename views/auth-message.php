<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title ?? 'Аккаунт') ?> - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/auth-panel.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="auth-page">
  <main class="auth-panel">
    <h1><?= View::e($title ?? 'Аккаунт') ?></h1>
    <p class="<?= !empty($bad) ? 'auth-error' : '' ?>"><?= View::e($message ?? '') ?></p>
    <a href="/">На главную</a>
  </main>
</body>
</html>
