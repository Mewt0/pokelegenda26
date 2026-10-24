<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Новый пароль - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/auth-panel.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="auth-page">
  <main class="auth-panel">
    <h1>Новый пароль</h1>
    <p>Аккаунт: <b><?= View::e($login ?? '') ?></b></p>
    <form class="auth-form" action="/password/reset" method="post">
      <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
      <input type="hidden" name="token" value="<?= View::e($token) ?>">
      <input class="ui-field__control" name="PASSWORD" type="password" maxlength="72" autocomplete="new-password" placeholder="Новый пароль" required>
      <input class="ui-field__control" name="PASSWORD_REPEAT" type="password" maxlength="72" autocomplete="new-password" placeholder="Повтор пароля" required>
      <button class="ui-btn ui-btn--primary" type="submit">Сменить пароль</button>
    </form>
  </main>
</body>
</html>
