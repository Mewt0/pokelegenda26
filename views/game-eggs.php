<?php
use Pokemon8\View\View;

$rows = $eggs['eggs'] ?? [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <style>
    body { margin: 0; background: #eef4fb; color: #14233a; font: 15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1180px; margin: 0 auto; padding: 22px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
    .top a { color: #125bb3; font-weight: 700; text-decoration: none; }
    .panel { background: #fff; border: 1px solid #c9d8ea; border-radius: 8px; padding: 18px; box-shadow: 0 14px 34px rgba(35, 76, 126, .08); }
    .toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 12px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
    .egg { border: 1px solid #cbd9ea; border-radius: 8px; padding: 14px; background: linear-gradient(180deg, #ffffff, #f4f8fd); }
    .egg h2 { margin: 0 0 8px; font-size: 18px; }
    .meta { color: #5d718d; font-size: 13px; }
    .iv { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 10px 0; font-size: 13px; }
    button { border: 1px solid #a9c2e4; background: #fff; color: #113966; border-radius: 6px; padding: 8px 12px; font-weight: 700; cursor: pointer; }
    button.primary { background: #2674dc; color: #fff; border-color: #2674dc; }
    button:disabled { opacity: .55; cursor: default; }
    .empty { padding: 34px; text-align: center; color: #60718a; border: 1px dashed #bfd0e5; border-radius: 8px; }
    .toast { min-height: 22px; color: #0d6b3a; font-weight: 700; }
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
      <div class="toolbar">
        <div>
          <h1><?= View::e($module['title']) ?></h1>
          <div class="meta">Инкубация и вылупление идут через новый JSON API.</div>
        </div>
        <div id="egg-toast" class="toast"></div>
      </div>

      <?php if (!$rows): ?>
        <div class="empty">У тебя пока нет яиц.</div>
      <?php else: ?>
        <div class="grid" id="egg-grid">
          <?php foreach ($rows as $egg): ?>
            <article class="egg" data-egg-id="<?= (int) $egg['id'] ?>">
              <h2>#<?= (int) $egg['base_id'] ?> <?= View::e($egg['name']) ?></h2>
              <div class="meta">
                <?= !empty($egg['ready']) ? 'Готово к вылуплению' : 'Осталось: ' . (int) $egg['remaining_seconds'] . ' сек.' ?>
              </div>
              <div class="meta">Яйцевая атака: <?= View::e($egg['egg_attack_name'] ?: 'не задана') ?></div>
              <div class="iv">
                <span>HP <?= (int) $egg['iv']['hp'] ?></span>
                <span>Atk <?= (int) $egg['iv']['atk'] ?></span>
                <span>Def <?= (int) $egg['iv']['def'] ?></span>
                <span>SAtk <?= (int) $egg['iv']['satk'] ?></span>
                <span>SDef <?= (int) $egg['iv']['sdef'] ?></span>
                <span>Speed <?= (int) $egg['iv']['speed'] ?></span>
              </div>
              <?php if (!empty($egg['ready'])): ?>
                <button class="primary" data-action="hatch">Вылупить</button>
              <?php else: ?>
                <button data-action="incubate">Инкубировать</button>
              <?php endif; ?>
              <a href="/game/commission" style="display:inline-block;margin-left:6px;border:1px solid #a9c2e4;background:#fff;color:#113966;border-radius:6px;padding:8px 12px;font-weight:700;text-decoration:none">В лавку</a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <script>
    (() => {
      const csrf = <?= json_encode($csrf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const toast = document.getElementById('egg-toast');
      document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const card = button.closest('[data-egg-id]');
        if (!card) return;
        const action = button.dataset.action;
        button.disabled = true;
        const form = new FormData();
        form.set('_csrf', csrf);
        form.set('egg_id', card.dataset.eggId);
        try {
          const response = await fetch(action === 'hatch' ? '/api/eggs/hatch' : '/api/eggs/incubate', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
          });
          const data = await response.json();
          toast.textContent = data.message || (data.ok ? 'Готово.' : 'Ошибка.');
          if (data.ok) window.setTimeout(() => location.reload(), 650);
        } catch (error) {
          toast.textContent = 'Ошибка сети.';
        } finally {
          button.disabled = false;
        }
      });
    })();
  </script>
</body>
</html>
