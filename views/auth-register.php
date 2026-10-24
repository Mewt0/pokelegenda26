<?php
declare(strict_types=1);

use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Регистрация - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/auth-panel.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="auth-page">
<main class="auth-panel">
  <h1>Регистрация</h1>
  <p>Почта временно необязательна. Её можно будет привязать позже в профиле для восстановления пароля.</p>
  <form class="auth-form" action="/register" method="post">
    <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
    <input class="ui-field__control" name="LOGIN" maxlength="16" autocomplete="username" placeholder="Логин" required>
    <input class="ui-field__control" name="PASSWORD" type="password" maxlength="72" autocomplete="new-password" placeholder="Пароль" required>
    <input class="ui-field__control" name="PASSWORD_REPEAT" type="password" maxlength="72" autocomplete="new-password" placeholder="Повтор пароля" required>
    <input class="ui-field__control" name="EMAIL" type="email" maxlength="100" autocomplete="email" placeholder="Email, необязательно">
    <div class="auth-hint">Логин: латиница, цифры, подчёркивание или дефис, 3-16 символов.</div>
    <button class="ui-btn ui-btn--primary" type="submit">Создать аккаунт</button>
  </form>
  <div class="auth-links">
    <a class="ui-btn ui-btn--ghost" href="/">На главную</a>
    <a class="ui-btn ui-btn--ghost" href="/password/forgot">Восстановить пароль</a>
  </div>
</main>
</body>
</html>
