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
  <main class="market-page" data-csrf="<?= View::e($csrf) ?>">
    <nav class="market-crumbs">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong><?= View::e($module['title']) ?></strong>
    </nav>

    <section class="market-hero">
      <div>
        <p class="eyebrow">Покупки предметов</p>
        <h1>Покемаркет</h1>
        <p class="lead">Покупай расходники из системного магазина или забирай лоты, которые выставили игроки в твоём регионе.</p>
      </div>
      <div class="wallet" aria-label="Баланс">
        <span><b data-wallet-coins><?= number_format((int) ($wallet['coins'] ?? 0), 0, ',', ' ') ?></b> монет</span>
        <span><b data-wallet-diamonds><?= number_format((int) ($wallet['diamonds'] ?? 0), 0, ',', ' ') ?></b> алмазов</span>
      </div>
    </section>

    <section class="market-toolbar">
      <div class="tabs" role="tablist" aria-label="Разделы рынка">
        <button class="tab is-active" type="button" data-market-tab="catalog">Магазин</button>
        <button class="tab" type="button" data-market-tab="lots">Лоты игроков</button>
      </div>
      <label class="search">
        <span>Поиск</span>
        <input type="search" placeholder="Название или ID предмета" data-market-search>
      </label>
    </section>

    <div class="market-status" data-market-status></div>

    <section class="market-section is-active" data-market-panel="catalog">
      <div class="cards" data-catalog-list>
        <?php foreach ($catalog as $item): ?>
          <article class="market-card" data-card-name="<?= View::e(mb_strtolower((string) $item['name'])) ?>" data-card-id="<?= View::e((string) $item['item_id']) ?>">
            <div class="icon"><img src="<?= View::e($item['icon']) ?>" alt=""></div>
            <div class="body">
              <div class="title-row">
                <h2><?= View::e($item['name']) ?></h2>
                <span class="owned">есть: <?= number_format((int) ($item['owned'] ?? 0), 0, ',', ' ') ?></span>
              </div>
              <p><?= View::e($item['description']) ?></p>
              <div class="buy-row">
                <span class="price"><?= number_format((int) $item['price'], 0, ',', ' ') ?> <?= View::e(mb_strtolower((string) $item['currency_name'])) ?></span>
                <input type="number" min="<?= View::e((string) $item['min_count']) ?>" max="<?= View::e((string) $item['max_count']) ?>" value="1" aria-label="Количество">
                <button type="button" data-buy-catalog="<?= View::e((string) $item['id']) ?>">Купить</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($catalog === []): ?>
        <p class="empty">В системном магазине пока нет активных товаров.</p>
      <?php endif; ?>
    </section>

    <section class="market-section" data-market-panel="lots">
      <div class="cards" data-lot-list>
        <?php foreach ($lots as $lot): ?>
          <article class="market-card" data-card-name="<?= View::e(mb_strtolower((string) $lot['name'])) ?>" data-card-id="<?= View::e((string) $lot['item_id']) ?>">
            <div class="icon"><img src="<?= View::e($lot['icon']) ?>" alt=""></div>
            <div class="body">
              <div class="title-row">
                <h2><?= View::e($lot['name']) ?> x<?= number_format((int) $lot['count'], 0, ',', ' ') ?></h2>
                <span class="owned"><?= View::e($lot['seller_login']) ?></span>
              </div>
              <p><?= View::e($lot['description']) ?></p>
              <div class="buy-row">
                <span class="price"><?= number_format((int) $lot['unit_price'], 0, ',', ' ') ?> монет / шт</span>
                <input type="number" min="1" max="<?= View::e((string) $lot['count']) ?>" value="1" aria-label="Количество" <?= !empty($lot['is_own']) ? 'disabled' : '' ?>>
                <button type="button" data-buy-lot="<?= View::e((string) $lot['id_lot']) ?>" <?= !empty($lot['is_own']) ? 'disabled' : '' ?>>
                  <?= !empty($lot['is_own']) ? 'Свой лот' : 'Купить' ?>
                </button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($lots === []): ?>
        <p class="empty">В твоём регионе нет активных лотов игроков.</p>
      <?php endif; ?>
    </section>
  </main>
  <script src="/public/js/market-items.js"></script>
</body>
</html>
