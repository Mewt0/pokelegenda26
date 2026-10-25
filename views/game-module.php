<?php
use Pokemon8\View\View;

$isBattle = in_array($slug, ['battle/pvp', 'battle/pve'], true);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-module.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="module-page">
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong><?= View::e($module['title']) ?></strong>
    </nav>

    <section class="panel module-empty">
      <h1><?= View::e($module['title']) ?></h1>
      <?php if ($isBattle): ?>
        <p class="module-lead">Бои доступны в игровом мире.</p>
        <p class="module-text">
          Бои начинаются в игровом мире: при встрече с диким покемоном или когда другой тренер
          вызывает тебя на PvP. Если бой уже идёт, он открывается на главном экране.
        </p>
      <?php else: ?>
        <p class="module-lead">Этот раздел пока недоступен.</p>
        <p class="module-text">
          Мы уже работаем над ним. Вернись в игровой мир — там доступны основные занятия тренера.
        </p>
      <?php endif; ?>
      <div class="module-actions">
        <a class="module-button module-button--primary" href="/game">Вернуться в игровой мир</a>
      </div>
    </section>
  </main>
</body>
</html>
