<?php
$mailMode = (string) ($mailMode ?? 'standalone');
$mailOverlay = $mailMode === 'overlay';
$mailRootId = (string) ($mailRootId ?? ($mailOverlay ? 'mailOverlay' : 'mailPage'));
$mailCsrf = (string) ($csrf ?? '');
$mailPrefillRecipient = (string) ($prefillRecipient ?? '');
$mailUnread = (int) ($unread ?? 0);
?>
<?php if ($mailOverlay): ?>
  <section class="mail-overlay" id="<?= \Pokemon8\View\View::e($mailRootId) ?>" aria-hidden="true" data-mail-overlay>
<?php else: ?>
  <main class="mail-page">
<?php endif; ?>
  <div class="mail-window" role="dialog" aria-label="Почта" data-mail-root data-mode="<?= \Pokemon8\View\View::e($mailMode) ?>" data-csrf="<?= \Pokemon8\View\View::e($mailCsrf) ?>" data-prefill-recipient="<?= \Pokemon8\View\View::e($mailPrefillRecipient) ?>">
    <header class="mail-head">
      <div class="mail-title-mark" aria-hidden="true"><img src="/public/img/ui/menu-mail.png" alt=""></div>
      <div>
        <h1>Почта</h1>
        <p>Личные письма, системные уведомления и архив</p>
      </div>
      <div class="mail-head-actions">
        <span class="mail-unread-badge" data-mail-unread><?= $mailUnread > 0 ? \Pokemon8\View\View::e((string) $mailUnread) : '0' ?></span>
        <?php if ($mailOverlay): ?>
          <button type="button" class="mail-close" data-mail-close aria-label="Закрыть">×</button>
        <?php else: ?>
          <a href="/game" class="mail-close" aria-label="Закрыть">×</a>
        <?php endif; ?>
      </div>
    </header>

    <section class="mail-layout">
      <aside class="mail-compose-panel">
        <h2>Новое письмо</h2>
        <form data-mail-compose>
          <input type="hidden" name="_csrf" value="<?= \Pokemon8\View\View::e($mailCsrf) ?>">
          <label>
            <span>Получатель</span>
            <input name="recipient" data-mail-recipient value="<?= \Pokemon8\View\View::e($mailPrefillRecipient) ?>" placeholder="Ник или ID игрока" autocomplete="off" required>
          </label>
          <label>
            <span>Тема</span>
            <input name="subject" maxlength="120" placeholder="Тема письма" required>
          </label>
          <label class="mail-text-label">
            <span>Текст</span>
            <textarea name="text" maxlength="5000" placeholder="Сообщение..." required></textarea>
          </label>
          <div class="mail-compose-actions">
            <button type="submit" class="primary">Отправить</button>
            <button type="reset">Очистить</button>
          </div>
        </form>
      </aside>

      <section class="mail-center">
        <div class="mail-tabs" data-mail-tabs>
          <button type="button" class="is-active" data-mail-tab="inbox">Входящие <b data-mail-count="inbox">0</b></button>
          <button type="button" data-mail-tab="sent">Исходящие <b data-mail-count="sent">0</b></button>
          <button type="button" data-mail-tab="archive">Архив <b data-mail-count="archive">0</b></button>
        </div>
        <div class="mail-toolbar">
          <input type="search" data-mail-search placeholder="Поиск по теме, игроку или тексту">
          <button type="button" data-mail-refresh>Обновить</button>
        </div>
        <div class="mail-list" data-mail-list></div>
      </section>

      <aside class="mail-preview">
        <h2>Просмотр</h2>
        <div class="mail-preview-body" data-mail-preview>
          <div class="mail-empty-state">Выберите письмо из списка.</div>
        </div>
      </aside>
    </section>

    <footer class="mail-status" data-mail-status>Готово</footer>
  </div>
<?php if ($mailOverlay): ?>
  </section>
<?php else: ?>
  </main>
<?php endif; ?>
