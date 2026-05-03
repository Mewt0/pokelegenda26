<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0 - игра</title>
</head>
<body>
  <h1>Добро пожаловать, <?= View::e($login) ?></h1>
  <p>Новое игровое ядро запущено. Следующий перенос: карта, чат, персонаж, инвентарь и бой.</p>
  <p><a href="/logout">Выйти</a></p>
</body>
</html>
