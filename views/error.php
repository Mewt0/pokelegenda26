<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ошибка</title>
</head>
<body>
  <h1>Не получилось войти</h1>
  <p><?= View::e($message) ?></p>
  <p><a href="/">Вернуться назад</a></p>
</body>
</html>
