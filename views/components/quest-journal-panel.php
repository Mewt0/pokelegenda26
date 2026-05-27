<?php
$questMode = (string) ($questMode ?? 'standalone');
$questOverlay = $questMode === 'overlay';
$questRootId = (string) ($questRootId ?? ($questOverlay ? 'questOverlay' : 'questPage'));
$questCsrf = (string) ($csrf ?? '');
?>
<?php if ($questOverlay): ?>
  <section class="quest-overlay" id="<?= \Pokemon8\View\View::e($questRootId) ?>" aria-hidden="true" data-quest-overlay>
<?php else: ?>
  <main class="quest-page-shell">
<?php endif; ?>
  <div class="quest-window" role="dialog" aria-label="Журнал заданий" data-quest-root data-mode="<?= \Pokemon8\View\View::e($questMode) ?>" data-csrf="<?= \Pokemon8\View\View::e($questCsrf) ?>">
    <header class="quest-head">
      <div class="quest-head-mark" aria-hidden="true"><img src="/public/img/ui/menu-quests.png" alt=""></div>
      <div>
        <h1>Журнал заданий</h1>
        <p>Квесты, цели, прогресс и награды</p>
      </div>
      <div class="quest-head-summary" data-quest-summary></div>
      <?php if ($questOverlay): ?>
        <button type="button" class="quest-close" data-quest-close aria-label="Закрыть">×</button>
      <?php else: ?>
        <a href="/game" class="quest-close" aria-label="Закрыть">×</a>
      <?php endif; ?>
    </header>

    <section class="quest-layout">
      <aside class="quest-list-panel">
        <div class="quest-search-row">
          <input type="search" data-quest-search placeholder="Поиск квеста">
          <button type="button" data-quest-reset>Сброс</button>
        </div>
        <div class="quest-filter-row" data-quest-filters>
          <button type="button" class="is-active" data-quest-filter="all">Все</button>
          <button type="button" data-quest-filter="active">В процессе</button>
          <button type="button" data-quest-filter="available">Новые</button>
          <button type="button" data-quest-filter="completed">Выполнен</button>
          <button type="button" data-quest-filter="locked">Закрыт</button>
        </div>
        <div class="quest-list" data-quest-list></div>
      </aside>

      <section class="quest-detail" data-quest-detail>
        <div class="quest-empty-state">
          <img src="/public/img/ui/menu-quests.png" alt="">
          <b>Выберите квест</b>
          <span>Слева появятся активные и доступные задания.</span>
        </div>
      </section>

      <aside class="quest-reward-panel">
        <h2>Награды</h2>
        <div data-quest-rewards class="quest-rewards-empty">Выберите квест, чтобы увидеть награды.</div>
        <div class="quest-side-actions">
          <button type="button" data-quest-track disabled>Отследить квест</button>
          <button type="button" data-quest-map disabled>Показать на карте</button>
          <button type="button" data-quest-npc disabled>Перейти к NPC</button>
        </div>
      </aside>
    </section>

    <footer class="quest-status" data-quest-status>Готово</footer>
  </div>
<?php if ($questOverlay): ?>
  </section>
  <aside class="quest-mini-tracker" id="questMiniTracker" hidden data-quest-mini-tracker>
    <button type="button" data-open-quests aria-label="Открыть журнал заданий">
      <span class="quest-mini-title">Квест</span>
      <b data-quest-mini-title>Нет активного задания</b>
      <i><span data-quest-mini-progress style="width:0%"></span></i>
      <small data-quest-mini-meta>Откройте журнал заданий</small>
    </button>
  </aside>
  <div class="quest-toast-stack" id="questToastStack" aria-live="polite"></div>
<?php else: ?>
  </main>
<?php endif; ?>
