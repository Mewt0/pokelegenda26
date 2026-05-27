<?php
use Pokemon8\View\View;

$events = is_array($events ?? null) ? $events : [];
$activeEvents = $events['activeEvents'] ?? [];
$upcomingEvents = $events['upcomingEvents'] ?? [];
$personalBoosts = $events['personalBoosts'] ?? [];
$multipliers = $events['effectiveMultipliers'] ?? [];
$qaChecklist = $events['qaChecklist'] ?? [];
$serverTime = (int) ($events['serverTime'] ?? time());

$timeText = static function (int $timestamp): string {
    return $timestamp > 0 ? date('d.m.Y H:i', $timestamp) : 'без ограничения';
};

$durationText = static function (int $seconds): string {
    if ($seconds <= 0) {
        return 'без ограничения';
    }
    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    if ($days > 0) {
        return $days . ' дн. ' . $hours . ' ч.';
    }
    if ($hours > 0) {
        return $hours . ' ч. ' . $minutes . ' мин.';
    }
    return max(1, $minutes) . ' мин.';
};

$renderEvent = static function (array $event) use ($timeText, $durationText): void {
    ?>
    <article class="event-card">
      <div class="event-card__top">
        <strong><?= View::e($event['title'] ?? 'Ивент') ?></strong>
        <span>x<?= View::e(number_format((float) ($event['multiplier'] ?? 1), 2, '.', '')) ?></span>
      </div>
      <div class="event-card__meta">
        <?= View::e($event['boost_label'] ?? $event['boost_key'] ?? '') ?> ·
        <?= View::e($event['scope_label'] ?? $event['scope'] ?? '') ?> ·
        <?= View::e($event['status_label'] ?? '') ?>
      </div>
      <div class="event-card__time">
        <?= View::e($timeText((int) ($event['starts_at'] ?? 0))) ?> → <?= View::e($timeText((int) ($event['ends_at'] ?? 0))) ?>
      </div>
      <?php if ((int) ($event['remaining_seconds'] ?? 0) > 0): ?>
        <div class="event-card__left">Осталось: <?= View::e($durationText((int) $event['remaining_seconds'])) ?></div>
      <?php elseif ((int) ($event['starts_in_seconds'] ?? 0) > 0): ?>
        <div class="event-card__left">Старт через: <?= View::e($durationText((int) $event['starts_in_seconds'])) ?></div>
      <?php endif; ?>
      <?php if (!empty($event['note'])): ?>
        <p><?= View::e($event['note']) ?></p>
      <?php endif; ?>
    </article>
    <?php
};
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Игровые события - Pokemon 8.0</title>
  <style>
    :root {
      --bg: #eef5ff;
      --panel: rgba(255, 255, 255, .92);
      --line: #cfe0f5;
      --text: #10213d;
      --muted: #62738c;
      --blue: #2d72e8;
      --green: #18a56b;
    }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--bg); color: var(--text); font: 15px/1.5 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1180px; margin: 0 auto; padding: 22px; }
    .top { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
    .top a { color: #155bb7; text-decoration: none; font-weight: 700; }
    .hero, .panel { background: var(--panel); border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 18px 50px rgba(50, 87, 130, .10); }
    .hero { padding: 22px; margin-bottom: 14px; }
    h1 { margin: 0 0 10px; font-size: clamp(28px, 4vw, 46px); line-height: 1.05; }
    h2 { margin: 0 0 12px; font-size: 20px; }
    .hero p, .panel p { color: var(--muted); margin: 0; }
    .grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(300px, .8fr); gap: 14px; }
    .panel { padding: 16px; margin-bottom: 14px; }
    .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; }
    .event-card { border: 1px solid var(--line); border-radius: 8px; padding: 12px; background: #fff; min-height: 128px; }
    .event-card__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 17px; }
    .event-card__top span { color: var(--green); font-weight: 800; }
    .event-card__meta, .event-card__time, .event-card__left { margin-top: 6px; color: var(--muted); }
    .event-card__left { color: #0f7a4f; font-weight: 700; }
    .empty { border: 1px dashed var(--line); color: var(--muted); border-radius: 8px; padding: 14px; background: rgba(255,255,255,.65); }
    .matrix { display: grid; gap: 8px; }
    .scope-title { margin: 12px 0 8px; color: #37506f; font-weight: 800; }
    .multi-row, .qa-row { display: flex; justify-content: space-between; gap: 10px; align-items: center; border: 1px solid var(--line); border-radius: 8px; padding: 9px 10px; background: #fff; }
    .multi-row b { color: var(--blue); }
    .multi-row.is-active b { color: var(--green); }
    .qa-row { display: block; }
    .qa-row strong { display: block; margin-bottom: 4px; }
    .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .btn { border: 1px solid #b9d1ef; border-radius: 8px; padding: 10px 14px; color: #124e9c; background: #fff; text-decoration: none; font-weight: 800; cursor: pointer; }
    .btn.primary { background: #2f75ee; color: #fff; border-color: #2f75ee; }
    code { background: #eef4ff; border: 1px solid #d8e6fb; border-radius: 6px; padding: 2px 6px; }
    @media (max-width: 860px) {
      .wrap { padding: 12px; }
      .grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <main class="wrap" data-csrf="<?= View::e($csrf ?? '') ?>">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Игровые события</strong>
    </nav>

    <section class="hero">
      <h1>Игровые события</h1>
      <p>
        Новый раздел работает от JSON API и таблиц событий. Legacy-вход
        <code>game.php?go=gameload</code> больше не нужен как источник бизнес-логики.
      </p>
      <div class="actions">
        <a class="btn primary" href="/game/admin">Настроить в админке</a>
        <a class="btn" href="/api/events" target="_blank" rel="noopener">Открыть JSON</a>
        <button class="btn" type="button" id="refreshEvents">Проверить активные эффекты</button>
      </div>
    </section>

    <section class="grid">
      <div>
        <section class="panel">
          <h2>Активные события</h2>
          <?php if ($activeEvents === []): ?>
            <div class="empty">Сейчас нет включённых глобальных событий.</div>
          <?php else: ?>
            <div class="cards">
              <?php foreach ($activeEvents as $event): ?>
                <?php $renderEvent($event); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <section class="panel">
          <h2>Запланированные события</h2>
          <?php if ($upcomingEvents === []): ?>
            <div class="empty">Будущих событий пока нет.</div>
          <?php else: ?>
            <div class="cards">
              <?php foreach ($upcomingEvents as $event): ?>
                <?php $renderEvent($event); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <section class="panel">
          <h2>Личные бусты</h2>
          <?php if ($personalBoosts === []): ?>
            <div class="empty">У персонажа нет активных личных бустов от предметов.</div>
          <?php else: ?>
            <div class="cards">
              <?php foreach ($personalBoosts as $event): ?>
                <?php $renderEvent($event); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </div>

      <aside>
        <section class="panel">
          <h2>Итоговые множители</h2>
          <p>Время сервера: <?= View::e(date('d.m.Y H:i:s', $serverTime)) ?></p>
          <div class="matrix" id="eventMultipliers">
            <?php foreach ($multipliers as $scope => $rows): ?>
              <div class="scope-title"><?= View::e($scope) ?></div>
              <?php foreach ($rows as $row): ?>
                <div class="multi-row <?= !empty($row['active']) ? 'is-active' : '' ?>">
                  <span><?= View::e($row['boost_label'] ?? $row['boost_key'] ?? '') ?></span>
                  <b>x<?= View::e(number_format((float) ($row['multiplier'] ?? 1), 2, '.', '')) ?></b>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="panel">
          <h2>QA-чеклист</h2>
          <div class="matrix">
            <?php foreach ($qaChecklist as $item): ?>
              <div class="qa-row">
                <strong><?= View::e($item['title'] ?? '') ?></strong>
                <span><?= View::e($item['expected'] ?? '') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      </aside>
    </section>
  </main>
  <script>
    document.getElementById('refreshEvents')?.addEventListener('click', async () => {
      const button = document.getElementById('refreshEvents');
      button.disabled = true;
      try {
        const response = await fetch('/api/events/active', {credentials: 'same-origin'});
        const data = await response.json();
        const target = document.getElementById('eventMultipliers');
        target.replaceChildren();
        Object.entries(data.effectiveMultipliers || {}).forEach(([scope, values]) => {
          const title = document.createElement('div');
          title.className = 'scope-title';
          title.textContent = scope;
          target.appendChild(title);
          Object.values(values).forEach((row) => {
            const line = document.createElement('div');
            line.className = 'multi-row' + (row.active ? ' is-active' : '');
            const label = document.createElement('span');
            label.textContent = String(row.boost_label || row.boost_key || '');
            const value = document.createElement('b');
            value.textContent = 'x' + Number(row.multiplier || 1).toFixed(2);
            line.append(label, value);
            target.appendChild(line);
          });
        });
      } catch (error) {
        alert('Не удалось обновить события.');
      } finally {
        button.disabled = false;
      }
    });
  </script>
</body>
</html>
