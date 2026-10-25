<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Команда покемонов - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-pokemon.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body data-csrf="<?= View::e($csrf) ?>">
  <main class="team-window">
    <header class="team-head">
      <span>Команда покемонов</span>
      <span class="team-head-actions">
        <button type="button" class="team-icon-btn" id="downloadBtn" aria-label="Скачать"><span class="icon icon--ui-download" aria-hidden="true"></span></button>
        <button type="button" class="team-close" id="closeBtn" aria-label="Закрыть">×</button>
      </span>
    </header>
    <section class="team-body">
      <aside class="team-list" id="pokemonList"></aside>
      <section class="detail" id="pokemonDetail" aria-live="polite"></section>
    </section>
    <footer class="team-foot">
      <span class="status" id="status">Загрузка...</span>
      <button type="button" id="detailId" class="pokemon-chat-id" title="Упомянуть покемона в чате" hidden></button>
    </footer>
  </main>

  <script src="<?= View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script src="<?= View::e(asset_url('/public/js/game-pokemon-page.js')) ?>"></script>
</body>
</html>
