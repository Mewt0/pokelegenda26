<?php
use Pokemon8\View\View;

$lots = $market['lots'] ?? [];
$own = $market['own_pokemon'] ?? [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <style>
    body { margin: 0; background: #eef4fb; color: #14233a; font: 15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1220px; margin: 0 auto; padding: 22px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
    .top a { color: #125bb3; font-weight: 700; text-decoration: none; }
    .layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 14px; }
    .panel { background: #fff; border: 1px solid #c9d8ea; border-radius: 8px; padding: 18px; box-shadow: 0 14px 34px rgba(35, 76, 126, .08); }
    .toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 12px; }
    .lots { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .lot { border: 1px solid #cbd9ea; border-radius: 8px; padding: 13px; background: linear-gradient(180deg, #ffffff, #f4f8fd); }
    .lot h2 { margin: 0 0 8px; font-size: 18px; }
    .meta { color: #5d718d; font-size: 13px; }
    .iv { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin: 10px 0; font-size: 13px; }
    input, select { width: 100%; box-sizing: border-box; border: 1px solid #b9c9df; border-radius: 6px; padding: 9px 10px; font: inherit; }
    label { display: block; margin: 10px 0 5px; color: #415a79; font-weight: 700; }
    button { border: 1px solid #a9c2e4; background: #fff; color: #113966; border-radius: 6px; padding: 8px 12px; font-weight: 700; cursor: pointer; }
    button.primary { background: #2674dc; color: #fff; border-color: #2674dc; }
    button.danger { color: #a22525; border-color: #e2b4b4; }
    button:disabled { opacity: .55; cursor: default; }
    .empty { padding: 34px; text-align: center; color: #60718a; border: 1px dashed #bfd0e5; border-radius: 8px; }
    .toast { min-height: 22px; color: #0d6b3a; font-weight: 700; }
    @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong><?= View::e($module['title']) ?></strong>
    </nav>
    <div class="layout">
      <section class="panel">
        <div class="toolbar">
          <div>
            <h1><?= View::e($module['title']) ?></h1>
            <div class="meta">Лоты работают через новый API поверх legacy-таблицы.</div>
          </div>
          <div id="market-toast" class="toast"></div>
        </div>
        <?php if (!$lots): ?>
          <div class="empty">Активных лотов пока нет.</div>
        <?php else: ?>
          <div class="lots">
            <?php foreach ($lots as $lot): ?>
              <article class="lot" data-lot-id="<?= (int) $lot['id_lot'] ?>">
                <h2>#<?= (int) $lot['base_id'] ?> <?= View::e($lot['name']) ?> Lv.<?= (int) $lot['level'] ?></h2>
                <div class="meta">Продавец: <?= View::e($lot['seller_login']) ?></div>
                <div class="meta">Цена: <?= number_format((int) $lot['price'], 0, '.', ' ') ?> монет</div>
                <?php if (($lot['private_to'] ?? 'no') !== 'no'): ?>
                  <div class="meta">Приватный лот: <?= View::e($lot['private_buyer_login'] ?: ('#' . $lot['private_to'])) ?></div>
                <?php endif; ?>
                <div class="iv">
                  <span>HP <?= (int) $lot['iv']['hp'] ?></span>
                  <span>Atk <?= (int) $lot['iv']['atk'] ?></span>
                  <span>Def <?= (int) $lot['iv']['def'] ?></span>
                  <span>SAtk <?= (int) $lot['iv']['satk'] ?></span>
                  <span>SDef <?= (int) $lot['iv']['sdef'] ?></span>
                  <span>Speed <?= (int) $lot['iv']['speed'] ?></span>
                </div>
                <?php if (!empty($lot['is_own'])): ?>
                  <button class="danger" data-action="cancel">Снять лот</button>
                <?php else: ?>
                  <button class="primary" data-action="buy">Купить</button>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <aside class="panel">
        <h2>Выставить покемона</h2>
        <form id="sell-form">
          <label for="pokemon_id">Покемон</label>
          <select id="pokemon_id" name="pokemon_id" required>
            <option value="">Выбери покемона</option>
            <?php foreach ($own as $pokemon): ?>
              <option value="<?= (int) $pokemon['id'] ?>">
                #<?= (int) $pokemon['id'] ?> <?= View::e($pokemon['names']) ?> Lv.<?= (int) $pokemon['lvl'] ?>
              </option>
            <?php endforeach; ?>
          </select>
          <label for="price">Цена в монетах</label>
          <input id="price" name="price" inputmode="numeric" min="1" placeholder="100000" required>
          <label for="private_to">Приватно для игрока</label>
          <input id="private_to" name="private_to" placeholder="Ник или ID, необязательно">
          <p class="meta">Нельзя выставить последнего активного покемона или покемона во время боя/обмена.</p>
          <button class="primary" type="submit">Выставить</button>
        </form>
      </aside>
    </div>
  </main>
  <script>
    (() => {
      const csrf = <?= json_encode($csrf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const toast = document.getElementById('market-toast');
      const send = async (url, fields) => {
        const form = new FormData();
        form.set('_csrf', csrf);
        Object.entries(fields).forEach(([key, value]) => form.set(key, value));
        const response = await fetch(url, { method: 'POST', body: form, credentials: 'same-origin' });
        return response.json();
      };
      document.getElementById('sell-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const data = await send('/api/market/pokemon/list', {
          pokemon_id: form.pokemon_id.value,
          price: form.price.value,
          private_to: form.private_to.value
        });
        toast.textContent = data.message || (data.ok ? 'Готово.' : 'Ошибка.');
        if (data.ok) window.setTimeout(() => location.reload(), 650);
      });
      document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const card = button.closest('[data-lot-id]');
        if (!card) return;
        button.disabled = true;
        try {
          const action = button.dataset.action;
          const data = await send(action === 'buy' ? '/api/market/pokemon/buy' : '/api/market/pokemon/cancel', {
            lot_id: card.dataset.lotId
          });
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
