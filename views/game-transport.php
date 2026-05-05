<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Транспорт - Pokemon 8.0</title>
  <style>
    body { margin:0; background:#eef4fb; color:#102544; font:15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width:980px; margin:0 auto; padding:24px; }
    .top { display:flex; gap:10px; align-items:center; margin-bottom:18px; }
    a { color:#1d5faf; text-decoration:none; font-weight:800; }
    .panel { border:1px solid #c8d6e6; border-radius:10px; background:#fff; padding:18px; box-shadow:0 12px 28px rgba(26,58,98,.08); }
    h1 { margin:0 0 8px; font-size:30px; }
    .muted { color:#62758f; margin:0 0 18px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:14px; }
    .card { display:grid; grid-template-columns:76px 1fr; gap:14px; align-items:center; border:1px solid #d1deec; border-radius:10px; padding:14px; background:#f8fbff; }
    .icon { width:68px; height:68px; display:grid; place-items:center; border:1px solid #bdd0e4; border-radius:10px; background:#fff; }
    .icon img { max-width:60px; max-height:60px; object-fit:contain; }
    h2 { margin:0 0 5px; font-size:18px; }
    p { margin:0; color:#4d627d; }
    button { min-height:34px; margin-top:10px; border:1px solid #2f78dc; border-radius:7px; background:#2f78dc; color:#fff; font-weight:900; cursor:pointer; }
    .status { margin-top:14px; min-height:22px; font-weight:800; color:#1f6b3b; }
    .status.bad { color:#9b1c1c; }
    .empty { padding:18px; border:1px dashed #b8c7d9; border-radius:10px; color:#62758f; background:#f8fbff; }
  </style>
</head>
<body>
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
</body>
</html>
