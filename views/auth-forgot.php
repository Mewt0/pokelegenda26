<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Восстановление пароля - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/auth-panel.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="auth-page">
  <main class="auth-panel">
    <h1>Восстановление пароля</h1>
    <p>Введите email, который привязан к аккаунту. Если у аккаунта почты нет, восстановление через email недоступно.</p>
    <form class="auth-form" action="/password/forgot" method="post">
      <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
      <input class="ui-field__control" name="EMAIL" type="email" maxlength="100" autocomplete="email" placeholder="Email" required>
      <button class="ui-btn ui-btn--primary" type="submit">Отправить ссылку</button>
    </form>
    <div class="auth-actions">
      <a href="/">Вернуться ко входу</a>
    </div>
  </main>
</body>
</html>
