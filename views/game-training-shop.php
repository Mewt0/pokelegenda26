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
  <title><?= View::e($module['title']) ?> - League of Pokemons</title>
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
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
