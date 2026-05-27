<?php
use Pokemon8\View\View;

$quests = $journal['quests'] ?? [];
$summary = $journal['summary'] ?? [];
$labels = [
    'available' => 'Доступно',
    'active' => 'В процессе',
    'completed' => 'Завершено',
    'locked' => 'Закрыто',
    'cooldown' => 'Ожидание',
];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($module['title']) ?> - Pokemon 8.0</title>
  <style>
    body { margin: 0; background: #eef4fb; color: #14233a; font: 15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1180px; margin: 0 auto; padding: 22px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
    .top a { color: #125bb3; font-weight: 700; text-decoration: none; }
    .panel { background: #fff; border: 1px solid #c9d8ea; border-radius: 8px; padding: 18px; box-shadow: 0 14px 34px rgba(35, 76, 126, .08); }
    .toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
    .summary { display: flex; flex-wrap: wrap; gap: 8px; }
    .pill { border: 1px solid #c5d6ea; border-radius: 999px; padding: 5px 10px; color: #415a79; background: #f7fbff; font-size: 13px; font-weight: 700; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
    .quest { border: 1px solid #cbd9ea; border-radius: 8px; padding: 14px; background: linear-gradient(180deg, #ffffff, #f4f8fd); }
    .quest h2 { display: flex; justify-content: space-between; gap: 12px; margin: 0 0 8px; font-size: 18px; }
    .status { white-space: nowrap; color: #0f5d41; font-size: 13px; }
    .status.locked { color: #7a5d1b; }
    .status.completed { color: #2e62b8; }
    .status.cooldown { color: #9a4e16; }
    .meta { color: #5d718d; font-size: 13px; }
    .steps { margin: 12px 0 0; padding: 0; list-style: none; }
    .steps li { border-top: 1px solid #dbe7f4; padding: 8px 0; }
    .steps b { display: block; }
    .reward { margin-top: 8px; color: #245e35; font-size: 13px; font-weight: 700; }
    button { border: 1px solid #a9c2e4; background: #fff; color: #113966; border-radius: 6px; padding: 8px 12px; font-weight: 700; cursor: pointer; }
    button.primary { background: #2674dc; color: #fff; border-color: #2674dc; }
    button:disabled { opacity: .55; cursor: default; }
    .empty { padding: 34px; text-align: center; color: #60718a; border: 1px dashed #bfd0e5; border-radius: 8px; }
    .toast { min-height: 22px; color: #0d6b3a; font-weight: 700; }
  </style>
</head>
<body>
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong><?= View::e($module['title']) ?></strong>
    </nav>
    <section class="panel">
      <div class="toolbar">
        <div>
          <h1><?= View::e($module['title']) ?></h1>
          <div class="meta">Журнал читает `quest_definitions`, `quest_steps` и состояние legacy-таблицы `quest` через новый JSON API.</div>
        </div>
        <div>
          <div id="quest-toast" class="toast"></div>
          <div class="summary">
            <?php foreach ($labels as $key => $label): ?>
              <span class="pill"><?= View::e($label) ?>: <?= (int) ($summary[$key] ?? 0) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <?php if (!$quests): ?>
        <div class="empty">Квесты пока не заведены.</div>
      <?php else: ?>
        <div class="grid" id="quest-grid">
          <?php foreach ($quests as $quest): ?>
            <?php $status = (string) ($quest['status'] ?? 'available'); ?>
            <article class="quest" data-quest-id="<?= (int) $quest['id'] ?>">
              <h2>
                <span>#<?= (int) $quest['id'] ?> <?= View::e($quest['title']) ?></span>
                <span class="status <?= View::e($status) ?>"><?= View::e($labels[$status] ?? $status) ?></span>
              </h2>
              <div class="meta"><?= View::e($quest['description'] ?? '') ?></div>
              <?php if (!empty($quest['depends_on_quest_id'])): ?>
                <div class="meta">Зависит от квеста #<?= (int) $quest['depends_on_quest_id'] ?></div>
              <?php endif; ?>
              <?php if (!empty($quest['cooldown_remaining_seconds'])): ?>
                <div class="meta">До повтора: <?= (int) $quest['cooldown_remaining_seconds'] ?> сек.</div>
              <?php endif; ?>
              <?php if (!empty($quest['reward']['items'])): ?>
                <div class="reward">Награда: <?= View::e(json_encode($quest['reward']['items'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '') ?></div>
              <?php endif; ?>
              <ul class="steps">
                <?php foreach (($quest['steps'] ?? []) as $step): ?>
                  <li>
                    <b><?= View::e($step['title']) ?></b>
                    <span class="meta"><?= View::e($step['description']) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <?php if (!empty($quest['can_start'])): ?>
                <button class="primary" data-action="start">Принять квест</button>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <script>
    (() => {
      const csrf = <?= json_encode($csrf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const toast = document.getElementById('quest-toast');
      document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action="start"]');
        if (!button) return;
        const card = button.closest('[data-quest-id]');
        if (!card) return;
        button.disabled = true;
        const form = new FormData();
        form.set('_csrf', csrf);
        form.set('quest_id', card.dataset.questId);
        try {
          const response = await fetch('/api/quests/start', { method: 'POST', body: form, credentials: 'same-origin' });
          const data = await response.json();
          toast.textContent = data.message || (data.ok ? 'Квест обновлен.' : 'Ошибка.');
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
