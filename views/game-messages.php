<?php
use Pokemon8\View\View;

$statusLabels = [
    'done' => 'готово',
    'partial' => 'частично',
    'todo' => 'нужно перенести',
];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Сообщения - Pokemon 8.0</title>
  <style>
    body { margin: 0; background: #eef2f5; color: #172033; font: 14px/1.5 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1120px; margin: 0 auto; padding: 24px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 18px; }
    .top a { color: #1d5faf; text-decoration: none; font-weight: 700; }
    .panel { background: #fff; border: 1px solid #cfd8e3; border-radius: 8px; padding: 18px; }
    .head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #e8f2ff; color: #174f91; font-weight: 700; }
    .badge.partial { background: #fff7e6; color: #8a4b00; }
    .grid { display: grid; grid-template-columns: minmax(280px, 360px) 1fr; gap: 16px; margin-top: 18px; align-items: start; }
    .compose { border: 1px solid #d6e0ec; border-radius: 8px; padding: 12px; background: #f8fbff; display: grid; gap: 10px; }
    .compose label { display: grid; gap: 5px; color: #52637a; font-weight: 700; }
    .compose input, .compose textarea { width: 100%; box-sizing: border-box; border: 1px solid #c9d6e5; border-radius: 6px; padding: 9px 10px; font: inherit; background: #fff; color: #172033; }
    .compose textarea { min-height: 140px; resize: vertical; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    button { border: 1px solid #b8c8da; background: #fff; border-radius: 6px; padding: 8px 12px; font: inherit; font-weight: 700; cursor: pointer; color: #17345f; }
    button.primary { background: #2f7df6; border-color: #2f7df6; color: #fff; }
    button.danger { color: #9f1f1f; }
    .toast { min-height: 20px; color: #1f7a41; font-weight: 700; }
    .tabs { display: flex; gap: 8px; margin-bottom: 10px; }
    .tab { border-color: #d6e0ec; }
    .tab.is-active { background: #e8f2ff; border-color: #9fc5ff; color: #135ec7; }
    .messages { display: grid; gap: 10px; margin-top: 18px; }
    .messages.is-hidden { display: none; }
    .message { border: 1px solid #d6e0ec; border-radius: 8px; padding: 12px; background: #fbfdff; }
    .message.is-unread { border-left: 4px solid #2f7df6; }
    .meta { display: flex; flex-wrap: wrap; gap: 10px; color: #5f6f86; font-size: 12px; margin-bottom: 6px; }
    .subject { font-weight: 700; color: #102447; margin-bottom: 6px; }
    .empty { color: #6b778a; margin-top: 16px; }
    @media (max-width: 820px) { .grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Сообщения</strong>
    </nav>

    <section class="panel">
      <div class="head">
        <h1>Сообщения</h1>
        <span class="badge <?= View::e((string) ($module['status'] ?? '')) ?>"><?= View::e($statusLabels[$module['status']] ?? $module['status']) ?><?= (int) ($unread ?? 0) > 0 ? ' • новых: ' . (int) $unread : '' ?></span>
      </div>

      <div class="grid">
        <form class="compose" id="mailCompose">
          <input type="hidden" name="_csrf" value="<?= View::e((string) ($csrf ?? '')) ?>">
          <label>
            Получатель
            <input name="recipient" value="<?= View::e((string) ($prefillRecipient ?? '')) ?>" placeholder="Ник или ID игрока" required>
          </label>
          <label>
            Тема
            <input name="subject" maxlength="120" placeholder="Тема письма" required>
          </label>
          <label>
            Текст
            <textarea name="text" maxlength="5000" placeholder="Сообщение..." required></textarea>
          </label>
          <div class="actions">
            <button class="primary" type="submit">Отправить</button>
            <button type="reset">Очистить</button>
          </div>
          <div class="toast" id="mailToast"></div>
        </form>

        <section>
          <div class="tabs">
            <button type="button" class="tab is-active" data-mail-tab="inbox">Входящие</button>
            <button type="button" class="tab" data-mail-tab="sent">Исходящие</button>
            <button type="button" class="tab" data-mail-tab="archive">Архив</button>
          </div>

          <div class="messages" data-mail-panel="inbox">
            <?php if ($messages === []): ?>
              <p class="empty">Входящих сообщений пока нет.</p>
            <?php else: ?>
              <?php foreach ($messages as $message): ?>
                <?php
                  $sender = trim((string) ($message['sender_login'] ?? ''));
                  if ($sender === '') {
                      $sender = (int) ($message['inputusers'] ?? 0) > 0 ? 'Игрок #' . (int) $message['inputusers'] : 'Система';
                  }
                  $subject = trim(strip_tags((string) ($message['tema'] ?? '')));
                  $text = trim(strip_tags((string) ($message['text'] ?? '')));
                ?>
                <article class="message <?= (int) ($message['active'] ?? 0) === 1 ? 'is-unread' : '' ?>" data-message-id="<?= (int) ($message['id'] ?? 0) ?>">
                  <div class="meta">
                    <span><?= View::e((string) ($message['date'] ?? '')) ?></span>
                    <span>От: <?= View::e($sender) ?></span>
                    <span>#<?= (int) ($message['id'] ?? 0) ?></span>
                  </div>
                  <div class="subject"><?= View::e($subject !== '' ? $subject : 'Без темы') ?></div>
                  <div><?= View::e($text) ?></div>
                  <div class="actions">
                    <?php if ((int) ($message['active'] ?? 0) === 1): ?>
                      <button type="button" data-mail-action="read">Прочитано</button>
                    <?php endif; ?>
                    <button type="button" class="danger" data-mail-action="delete">В архив</button>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="messages is-hidden" data-mail-panel="sent">
            <?php if (($sent ?? []) === []): ?>
              <p class="empty">Исходящих сообщений пока нет.</p>
            <?php else: ?>
              <?php foreach ($sent as $message): ?>
                <?php
                  $recipient = trim((string) ($message['recipient_login'] ?? ''));
                  if ($recipient === '') {
                      $recipient = (int) ($message['users'] ?? 0) > 0 ? 'Игрок #' . (int) $message['users'] : 'Система';
                  }
                  $subject = trim(strip_tags((string) ($message['tema'] ?? '')));
                  $text = trim(strip_tags((string) ($message['text'] ?? '')));
                ?>
                <article class="message">
                  <div class="meta">
                    <span><?= View::e((string) ($message['date'] ?? '')) ?></span>
                    <span>Кому: <?= View::e($recipient) ?></span>
                    <span>#<?= (int) ($message['id'] ?? 0) ?></span>
                  </div>
                  <div class="subject"><?= View::e($subject !== '' ? $subject : 'Без темы') ?></div>
                  <div><?= View::e($text) ?></div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="messages is-hidden" data-mail-panel="archive">
            <?php if (($archive ?? []) === []): ?>
              <p class="empty">Архив пока пуст.</p>
            <?php else: ?>
              <?php foreach ($archive as $message): ?>
                <?php
                  $folder = (string) ($message['folder'] ?? 'inbox');
                  $sender = trim((string) ($message['sender_login'] ?? ''));
                  $recipient = trim((string) ($message['recipient_login'] ?? ''));
                  $peer = $folder === 'sent'
                      ? ($recipient !== '' ? 'Кому: ' . $recipient : 'Кому: Игрок #' . (int) ($message['users'] ?? 0))
                      : ($sender !== '' ? 'От: ' . $sender : 'От: Игрок #' . (int) ($message['inputusers'] ?? 0));
                  $subject = trim(strip_tags((string) ($message['tema'] ?? '')));
                  $text = trim(strip_tags((string) ($message['text'] ?? '')));
                ?>
                <article class="message">
                  <div class="meta">
                    <span><?= View::e((string) ($message['date'] ?? '')) ?></span>
                    <span><?= View::e($peer) ?></span>
                    <span><?= $folder === 'sent' ? 'Исходящее' : 'Входящее' ?></span>
                    <span>#<?= (int) ($message['id'] ?? 0) ?></span>
                  </div>
                  <div class="subject"><?= View::e($subject !== '' ? $subject : 'Без темы') ?></div>
                  <div><?= View::e($text) ?></div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </section>
  </main>
  <script>
    const mailToast = document.getElementById('mailToast');
    const mailForm = document.getElementById('mailCompose');
    const say = (message, ok = true) => {
      mailToast.textContent = message || '';
      mailToast.style.color = ok ? '#1f7a41' : '#a32929';
    };
    const postMail = async (url, body) => {
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: new URLSearchParams(body)
      });
      return response.json();
    };

    mailForm.addEventListener('submit', async event => {
      event.preventDefault();
      const form = new FormData(mailForm);
      const payload = await postMail('/api/messages/send', Object.fromEntries(form.entries()));
      say(payload.message || (payload.ok ? 'Готово.' : 'Ошибка.'), !!payload.ok);
      if (payload.ok) mailForm.reset();
    });

    document.querySelectorAll('[data-mail-tab]').forEach(button => {
      button.addEventListener('click', () => {
        document.querySelectorAll('[data-mail-tab]').forEach(tab => tab.classList.toggle('is-active', tab === button));
        document.querySelectorAll('[data-mail-panel]').forEach(panel => {
          panel.classList.toggle('is-hidden', panel.dataset.mailPanel !== button.dataset.mailTab);
        });
      });
    });

    document.addEventListener('click', async event => {
      const actionButton = event.target.closest('[data-mail-action]');
      if (!actionButton) return;
      const card = actionButton.closest('[data-message-id]');
      const id = card ? card.dataset.messageId : '0';
      const csrf = mailForm.querySelector('[name="_csrf"]').value;
      const endpoint = actionButton.dataset.mailAction === 'delete' ? '/api/messages/delete' : '/api/messages/read';
      const payload = await postMail(endpoint, { _csrf: csrf, id });
      say(payload.message || (payload.ok ? 'Готово.' : 'Ошибка.'), !!payload.ok);
      if (payload.ok) {
        if (actionButton.dataset.mailAction === 'delete') {
          card.remove();
        } else {
          card.classList.remove('is-unread');
          actionButton.remove();
        }
      }
    });
  </script>
</body>
</html>
