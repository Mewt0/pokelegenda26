<?php
$commissionMode = (string) ($commissionMode ?? 'standalone');
$commissionOverlay = $commissionMode === 'overlay';
$commissionRootId = (string) ($commissionRootId ?? ($commissionOverlay ? 'commissionOverlay' : 'commissionPage'));
$commissionCsrf = (string) ($csrf ?? '');
?>
<?php if ($commissionOverlay): ?>
  <section class="commission-overlay" id="<?= \Pokemon8\View\View::e($commissionRootId) ?>" aria-hidden="true" data-commission-overlay>
<?php else: ?>
  <main class="commission-page">
<?php endif; ?>
  <div class="commission-window" role="dialog" aria-label="Комиссионная лавка" data-commission-root data-mode="<?= \Pokemon8\View\View::e($commissionMode) ?>" data-csrf="<?= \Pokemon8\View\View::e($commissionCsrf) ?>">
    <header class="commission-head">
      <div class="commission-title-mark" aria-hidden="true"><img src="/public/img/ui/menu-market.png" alt=""></div>
      <div>
        <h1>Комиссионная лавка</h1>
        <p>Игроки продают предметы, покемонов и яйца</p>
      </div>
      <?php if ($commissionOverlay): ?>
        <button type="button" class="commission-close" data-commission-close aria-label="Закрыть">×</button>
      <?php else: ?>
        <a href="/game" class="commission-close" aria-label="Закрыть">×</a>
      <?php endif; ?>
    </header>

    <section class="commission-layout">
      <aside class="commission-side">
        <div class="commission-side-title" data-commission-side-title>Категории</div>
        <div class="commission-categories" data-commission-categories></div>
        <div class="commission-sellable-panel" data-commission-sellable-panel hidden>
          <div class="commission-sellable-search">
            <input type="search" data-commission-sellable-search placeholder="Найти свой объект">
          </div>
          <div class="commission-sellable-grid" data-commission-sellable-grid></div>
        </div>
        <div class="commission-tabs">
          <button type="button" class="is-active commission-tab-lots" data-commission-view="lots">Лоты</button>
          <button type="button" class="commission-tab-my" data-commission-view="my">Мои лоты</button>
        </div>
      </aside>

      <section class="commission-center">
        <div class="commission-view-title" data-commission-view-title>Активные лоты</div>
        <div class="commission-toolbar">
          <select data-commission-sort aria-label="Сортировка">
            <option value="price_asc">↑ Цена</option>
            <option value="price_desc">↓ Цена</option>
            <option value="unit_asc">Цена за 1: дешевле</option>
            <option value="unit_desc">Цена за 1: дороже</option>
            <option value="new" selected>Новые</option>
            <option value="old">Старые</option>
            <option value="ending">Скоро закончатся</option>
            <option value="name">Название</option>
          </select>
          <input type="search" data-commission-search placeholder="Название предмета, покемона или яйца">
          <button type="button" data-commission-reset>Сброс</button>
          <button type="button" data-commission-refresh>Обновить</button>
        </div>

        <div class="commission-list-wrap">
          <table class="commission-table">
            <thead>
              <tr data-commission-head-row>
                <th>Лот</th>
                <th>Цена</th>
                <th>За 1</th>
                <th>Продавец</th>
              </tr>
            </thead>
            <tbody data-commission-lots></tbody>
          </table>
          <div class="commission-empty" data-commission-empty hidden>Активных лотов пока нет.</div>
        </div>

        <section class="commission-sell">
          <h2>Выставить лот</h2>
          <form data-commission-sell-form>
            <label><span>Тип</span><select name="object_type" data-commission-sell-type><option value="item">Предмет</option><option value="pokemon">Покемон</option><option value="egg">Яйцо</option></select></label>
            <label><span>Объект</span><select name="object_id" data-commission-sell-object></select></label>
            <label><span>Кол-во</span><input name="quantity" data-commission-sell-qty type="number" min="1" value="1"></label>
            <label><span>Цена за 1</span><input name="price_per_unit" type="number" min="1" value="1000"></label>
            <label><span>Срок</span><select name="duration_hours"><option value="24">24 часа</option><option value="48">48 часов</option><option value="72">72 часа</option></select></label>
            <button type="submit">Выставить</button>
          </form>
        </section>
      </section>

      <aside class="commission-preview">
        <h2>Предпросмотр</h2>
        <ul data-commission-preview>
          <li>Выберите лот или объект для продажи.</li>
        </ul>
      </aside>
    </section>

    <footer class="commission-status" data-commission-status>Готово</footer>
  </div>
<?php if ($commissionOverlay): ?>
  </section>
<?php else: ?>
  </main>
<?php endif; ?>
