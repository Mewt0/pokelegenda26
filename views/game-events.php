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
  <title>Игровые события - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-events.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="events-page">
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

        <?php /* QA-чеклист — внутренняя документация: на игровой странице её не показываем.
                 Данные остаются в GM Center (/game/admin). */ ?>
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
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
