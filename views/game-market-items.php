<?php
use Pokemon8\View\View;

$wallet = $wallet ?? ['coins' => 0, 'diamonds' => 0];
$catalog = is_array($catalog ?? null) ? $catalog : [];
$lots = is_array($lots ?? null) ? $lots : [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <link rel="stylesheet" href="/public/css/market-items.css">
</head>
<body>
  <main class="shop-page" data-csrf="<?= View::e($csrf) ?>">
    <section class="shop-window" aria-label="Покемаркет">
      <header class="shop-titlebar">
        <a href="/game" class="shop-close" aria-label="Вернуться в игру">×</a>
        <h1>Список товаров</h1>
      </header>

      <div class="shop-tabs">
        <button class="is-active" type="button" data-market-tab="catalog">Магазин</button>
        <button type="button" data-market-tab="lots">Лоты игроков</button>
        <label>
          <span>Поиск</span>
          <input type="search" data-market-search placeholder="Название или ID">
        </label>
        <div class="shop-wallet">
          <span><b data-wallet-coins><?= number_format((int) ($wallet['coins'] ?? 0), 0, ',', ' ') ?></b> монет</span>
          <span><b data-wallet-diamonds><?= number_format((int) ($wallet['diamonds'] ?? 0), 0, ',', ' ') ?></b> алмазов</span>
        </div>
      </div>

      <div class="shop-layout">
        <section class="shop-catalog" data-market-panel="catalog">
          <div class="shop-grid" data-shop-grid="catalog">
            <?php foreach ($catalog as $item): ?>
              <button
                type="button"
                class="shop-item"
                data-source="catalog"
                data-id="<?= View::e((string) $item['id']) ?>"
                data-item-id="<?= View::e((string) $item['item_id']) ?>"
                data-name="<?= View::e((string) $item['name']) ?>"
                data-price="<?= View::e((string) $item['price']) ?>"
                data-currency="<?= View::e((string) $item['currency_name']) ?>"
                data-min="<?= View::e((string) $item['min_count']) ?>"
                data-max="<?= View::e((string) $item['max_count']) ?>"
                data-max-owned="<?= View::e((string) ($item['max_owned'] ?? 0)) ?>"
                data-owned="<?= View::e((string) ($item['owned'] ?? 0)) ?>"
                data-description="<?= View::e((string) $item['description']) ?>"
                data-search="<?= View::e(mb_strtolower((string) $item['name'] . ' ' . $item['item_id'])) ?>"
              >
                <span class="shop-art"><img src="<?= View::e($item['icon']) ?>" alt=""></span>
                <span class="shop-price">
                  <b><?= number_format((int) $item['price'], 0, ',', ' ') ?></b>
                  <img src="/public/img/items/1.png" alt="">
                </span>
              </button>
            <?php endforeach; ?>
          </div>
          <?php if ($catalog === []): ?>
            <p class="shop-empty">В магазине пока нет активных товаров.</p>
          <?php endif; ?>
        </section>

        <section class="shop-catalog" data-market-panel="lots" hidden>
          <div class="shop-grid" data-shop-grid="lots">
            <?php foreach ($lots as $lot): ?>
              <button
                type="button"
                class="shop-item"
                data-source="lot"
                data-id="<?= View::e((string) $lot['id_lot']) ?>"
                data-item-id="<?= View::e((string) $lot['item_id']) ?>"
                data-name="<?= View::e((string) $lot['name']) ?>"
                data-price="<?= View::e((string) $lot['unit_price']) ?>"
                data-currency="Монета"
                data-min="1"
                data-max="<?= View::e((string) $lot['count']) ?>"
                data-owned="<?= View::e((string) $lot['count']) ?>"
                data-description="<?= View::e((string) $lot['description']) ?>"
                data-seller="<?= View::e((string) $lot['seller_login']) ?>"
                data-search="<?= View::e(mb_strtolower((string) $lot['name'] . ' ' . $lot['item_id'])) ?>"
                <?= !empty($lot['is_own']) ? 'disabled' : '' ?>
              >
                <span class="shop-art"><img src="<?= View::e($lot['icon']) ?>" alt=""></span>
                <span class="shop-price">
                  <b><?= number_format((int) $lot['unit_price'], 0, ',', ' ') ?></b>
                  <img src="/public/img/items/1.png" alt="">
                </span>
              </button>
            <?php endforeach; ?>
          </div>
          <?php if ($lots === []): ?>
            <p class="shop-empty">В твоём регионе нет активных лотов игроков.</p>
          <?php endif; ?>
        </section>

        <aside class="shop-cart" aria-live="polite">
          <h2>Покупки</h2>
          <div class="cart-empty" data-cart-empty>Выбери товар на полке.</div>
          <div class="cart-card" data-cart-card hidden>
            <div class="cart-art"><img data-cart-icon alt=""></div>
            <div class="cart-info">
              <h3 data-cart-name></h3>
              <p data-cart-description></p>
              <span data-cart-owned></span>
            </div>
            <label class="cart-count">
              <span>Количество</span>
              <input type="number" min="1" value="1" data-cart-count>
            </label>
            <div class="cart-total">
              <span>Итого</span>
              <b data-cart-total>0</b>
            </div>
          </div>
        </aside>
      </div>

      <footer class="shop-footer">
        <button type="button" data-shop-prev>Назад</button>
        <div class="shop-status" data-market-status></div>
        <button type="button" data-shop-next>Далее</button>
        <button type="button" class="buy-button" data-shop-buy disabled>Купить</button>
      </footer>
    </section>
  </main>
  <script src="/public/js/market-items.js"></script>
</body>
</html>
