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
    .messages { display: grid; gap: 10px; margin-top: 18px; }
    .message { border: 1px solid #d6e0ec; border-radius: 8px; padding: 12px; background: #fbfdff; }
    .message.is-unread { border-left: 4px solid #2f7df6; }
    .meta { display: flex; flex-wrap: wrap; gap: 10px; color: #5f6f86; font-size: 12px; margin-bottom: 6px; }
    .subject { font-weight: 700; color: #102447; margin-bottom: 6px; }
    .empty { color: #6b778a; margin-top: 16px; }
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
        <span class="badge partial"><?= View::e($statusLabels[$module['status']] ?? $module['status']) ?></span>
      </div>

      <?php if ($messages === []): ?>
        <p class="empty">Входящих сообщений пока нет.</p>
      <?php else: ?>
        <div class="messages">
          <?php foreach ($messages as $message): ?>
            <?php
              $sender = trim((string) ($message['sender_login'] ?? ''));
              if ($sender === '') {
                  $sender = (int) ($message['inputusers'] ?? 0) > 0 ? 'Игрок #' . (int) $message['inputusers'] : 'Система';
              }
              $subject = trim(strip_tags((string) ($message['tema'] ?? '')));
              $text = trim(strip_tags((string) ($message['text'] ?? '')));
            ?>
            <article class="message <?= (int) ($message['active'] ?? 0) === 1 ? 'is-unread' : '' ?>">
              <div class="meta">
                <span><?= View::e((string) ($message['date'] ?? '')) ?></span>
                <span>От: <?= View::e($sender) ?></span>
                <span>#<?= (int) ($message['id'] ?? 0) ?></span>
              </div>
              <div class="subject"><?= View::e($subject !== '' ? $subject : 'Без темы') ?></div>
              <div><?= View::e($text) ?></div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
