<?php
use Pokemon8\View\View;

$dashboard = is_array($tournaments ?? null) ? $tournaments : [];
$initialJson = json_encode($dashboard, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}';
$csrfToken = (string) ($csrf ?? '');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Турниры - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-tournaments.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="tournaments-page">
  <main class="wrap" data-csrf="<?= View::e($csrfToken) ?>" data-tournaments-root>
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Турниры</strong>
    </nav>

    <section class="hero">
      <div>
        <h1>Турниры</h1>
        <p>Расписание, регистрация, взносы, арена и награды. Время и дедлайны считаются по серверу.</p>
      </div>
      <div class="hero-badge">
        <div class="muted">Время сервера</div>
        <strong data-server-time>...</strong>
        <div class="muted" data-server-zone></div>
      </div>
    </section>

    <section class="grid">
      <div class="panel">
        <div class="card-top">
          <h2>Доступные турниры</h2>
          <button type="button" data-refresh-tournaments>Обновить</button>
        </div>
        <div class="cards" data-tournament-list></div>
        <div class="empty" data-tournament-empty hidden>Сейчас нет открытых турниров.</div>
        <div class="log" data-tournament-log>Готово</div>
      </div>

      <?php /* QA-заметки — внутренняя документация: на игровой странице их не показываем.
               Данные по-прежнему доступны в GM Center (/game/admin). */ ?>
    </section>
  </main>

  <script type="application/json" id="tournamentInitial"><?= $initialJson ?></script>
  <script src="<?= View::e(asset_url('/public/js/game-tournaments-page.js')) ?>"></script>
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
