<?php
use Pokemon8\View\View;

$statusLabels = [
    'done' => 'готово',
    'partial' => 'частично',
    'todo' => 'нужно перенести',
];
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

    <section class="panel">
      <h1><?= View::e($module['title']) ?></h1>
      <p>
        <span class="badge <?= View::e($module['status']) ?>">
          <?= View::e($statusLabels[$module['status']] ?? $module['status']) ?>
        </span>
      </p>
      <p>
        Раздел уже имеет новый маршрут <code>/game/<?= View::e($slug) ?></code>.
        Legacy-вход <code>game.php?go=<?= View::e($module['legacy']) ?></code> больше не должен быть основным источником логики.
      </p>
      <p>
        Следующий шаг переноса: вынести бизнес-логику этого раздела из legacy PHP в сервисы, репозитории и JSON API.
      </p>
    </section>

    <section class="grid">
      <?php foreach ($modules as $path => $item): ?>
        <a class="card" href="/game/<?= View::e($path) ?>">
          <?= View::e($item['title']) ?><br>
          <small><?= View::e($statusLabels[$item['status']] ?? $item['status']) ?></small>
        </a>
      <?php endforeach; ?>
    </section>
  </main>
</body>
</html>
