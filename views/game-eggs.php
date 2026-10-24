<?php
use Pokemon8\View\View;

$rows = $eggs['eggs'] ?? [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-eggs.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="eggs-page">
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
              <a class="btn" href="/game/commission">В лавку</a>
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
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
