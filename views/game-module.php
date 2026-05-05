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
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <style>
    body { margin: 0; background: #eef2f5; color: #172033; font: 14px/1.5 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1120px; margin: 0 auto; padding: 24px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 18px; }
    .top a, .grid a { color: #1d5faf; text-decoration: none; font-weight: 700; }
    .panel { background: #fff; border: 1px solid #cfd8e3; border-radius: 8px; padding: 18px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #e8f2ff; color: #174f91; font-weight: 700; }
    .badge.todo { background: #fff7e6; color: #8a4b00; }
    .badge.done { background: #e8f8ee; color: #17663a; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; margin-top: 16px; }
    .card { background: #fff; border: 1px solid #cfd8e3; border-radius: 8px; padding: 12px; }
    .card small { color: #5f6f86; }
  </style>
</head>
<body>
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
