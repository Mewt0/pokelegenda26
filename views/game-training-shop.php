<?php
use Pokemon8\View\View;

$price = (int) ($currency['price'] ?? 0);
$currencyName = (string) ($currency['name'] ?? 'монет');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <style>
    body { margin:0; background:#eef4fb; color:#102544; font:15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width:980px; margin:0 auto; padding:24px; }
    .top { display:flex; gap:10px; align-items:center; margin-bottom:18px; }
    a { color:#1d5faf; text-decoration:none; font-weight:800; }
    .panel { border:1px solid #c8d6e6; border-radius:10px; background:#fff; padding:18px; box-shadow:0 12px 28px rgba(26,58,98,.08); }
    h1 { margin:0 0 8px; font-size:30px; }
    .muted { color:#62758f; margin:0 0 18px; }
    .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; }
    .card { display:grid; grid-template-columns:74px 1fr; gap:14px; align-items:center; border:1px solid #d1deec; border-radius:10px; padding:14px; background:#f8fbff; }
    .icon { width:68px; height:68px; display:grid; place-items:center; border:1px solid #bdd0e4; border-radius:10px; background:#fff; }
    .icon img { max-width:60px; max-height:60px; object-fit:contain; }
    h2 { margin:0 0 5px; font-size:19px; }
    p { margin:0; color:#4d627d; }
    .buy { margin-top:11px; display:flex; align-items:center; gap:8px; }
    input { width:64px; height:34px; border:1px solid #bacadd; border-radius:7px; padding:0 8px; }
    button { min-height:34px; border:1px solid #2f78dc; border-radius:7px; background:#2f78dc; color:#fff; font-weight:900; cursor:pointer; }
    .status { margin-top:14px; min-height:22px; font-weight:800; color:#1f6b3b; }
    .status.bad { color:#9b1c1c; }
  </style>
</head>
<body>
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong><?= View::e($module['title']) ?></strong>
    </nav>

    <section class="panel" data-csrf="<?= View::e($csrf) ?>" data-shop="<?= View::e($shop) ?>">
      <h1><?= View::e($module['title']) ?></h1>
      <p class="muted">Наборы тренировок продаются по <?= number_format($price, 0, ',', ' ') ?> <?= View::e($currencyName) ?> за штуку.</p>
      <div class="grid">
        <?php foreach ($items as $item): ?>
          <article class="card">
            <span class="icon"><img src="<?= View::e($item['image']) ?>" alt=""></span>
            <div>
              <h2><?= View::e($item['name']) ?></h2>
              <p><?= View::e($item['description']) ?></p>
              <div class="buy">
                <input type="number" min="1" max="99" value="1" aria-label="Количество">
                <button type="button" data-buy-item="<?= View::e((string) $item['id']) ?>">Купить</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="status" id="shopStatus"></div>
    </section>
  </main>
  <script>
    (() => {
      const panel = document.querySelector('.panel');
      const status = document.getElementById('shopStatus');
      function setStatus(text, bad = false) {
        status.textContent = text || '';
        status.classList.toggle('bad', !!bad);
      }
      document.addEventListener('click', async event => {
        const btn = event.target.closest('[data-buy-item]');
        if (!btn) return;
        const input = btn.closest('.card').querySelector('input');
        const body = new URLSearchParams();
        body.set('_csrf', panel.dataset.csrf || '');
        body.set('shop', panel.dataset.shop || 'market');
        body.set('item_id', btn.dataset.buyItem || '0');
        body.set('count', input.value || '1');
        btn.disabled = true;
        try {
          const response = await fetch('/api/shop/training/buy', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json'},
            body
          });
          const data = await response.json();
          setStatus(data.message || 'Готово', !(data && data.ok));
        } catch (error) {
          setStatus('Ошибка покупки.', true);
        } finally {
          btn.disabled = false;
        }
      });
    })();
  </script>
</body>
</html>
