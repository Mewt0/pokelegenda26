<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Транспорт - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-shop.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="shop-page">
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Транспорт</strong>
    </nav>

    <section class="panel" data-csrf="<?= View::e($csrf) ?>">
      <h1>Транспорт</h1>
      <p class="muted">Самолёты и пароходы перевозят между регионами. На карточке показывается предмет рейса, его имя и фото.</p>
      <?php if (!$routes): ?>
        <div class="empty">В этой локации сейчас нет доступных рейсов.</div>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($routes as $route): ?>
            <article class="card">
              <span class="icon"><img src="<?= View::e($route['itemIcon']) ?>" alt=""></span>
              <div>
                <h2><?= View::e($route['itemName']) ?></h2>
                <p><?= View::e($route['fromTitle']) ?> -> <?= View::e($route['toTitle']) ?></p>
                <p>Цена: <?= number_format((int) $route['priceCount'], 0, ',', ' ') ?> монет</p>
                <button type="button" data-route-id="<?= View::e((string) $route['id']) ?>">Отправиться</button>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="status" id="transportStatus"></div>
    </section>
  </main>
  <script>
    (() => {
      const panel = document.querySelector('.panel');
      const status = document.getElementById('transportStatus');
      function setStatus(text, bad = false) {
        status.textContent = text || '';
        status.classList.toggle('bad', !!bad);
      }
      document.addEventListener('click', async event => {
        const btn = event.target.closest('[data-route-id]');
        if (!btn) return;
        const body = new URLSearchParams();
        body.set('_csrf', panel.dataset.csrf || '');
        body.set('route_id', btn.dataset.routeId || '0');
        btn.disabled = true;
        try {
          const response = await fetch('/api/transport/travel', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json'},
            body
          });
          const data = await response.json();
          setStatus(data.message || 'Готово', !(data && data.ok));
          if (data && data.ok) window.setTimeout(() => { window.location.href = '/game'; }, 650);
        } catch (error) {
          setStatus('Ошибка рейса.', true);
        } finally {
          btn.disabled = false;
        }
      });
    })();
  </script>
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
