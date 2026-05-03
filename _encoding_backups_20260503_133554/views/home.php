<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f4f7fb; color: #172033; }
    header, main { max-width: 1120px; margin: 0 auto; padding: 24px; }
    header { display: flex; align-items: center; justify-content: space-between; gap: 24px; }
    .brand { font-size: 28px; font-weight: 700; }
    .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 18px; }
    .grid { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; align-items: start; }
    input, button { font: inherit; padding: 8px 10px; }
    button { cursor: pointer; }
    .login { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .news article + article { margin-top: 14px; border-top: 1px solid #edf1f7; padding-top: 14px; }
    .rank-row { display: flex; justify-content: space-between; gap: 12px; padding: 4px 0; }
  </style>
</head>
<body>
<header>
  <div class="brand">Pokemon 8.0</div>
  <div class="panel">
    <?php if ($userLogin): ?>
      Р СџРЎР‚Р С‘Р Р†Р ВµРЎвЂљ, <?= View::e($userLogin) ?> |
      <a href="/game">Р вЂ™ Р С‘Р С–РЎР‚РЎС“</a> |
      <a href="/logout">Р вЂ™РЎвЂ№РЎвЂ¦Р С•Р Т‘</a>
    <?php else: ?>
      <form class="login" action="/login" method="post">
        <input type="hidden" name="_csrf" value="<?= View::e($csrfToken) ?>">
        <input name="LOGIN" maxlength="16" autocomplete="username" placeholder="Р вЂєР С•Р С–Р С‘Р Р…">
        <input name="PASSWORD" type="password" maxlength="72" autocomplete="current-password" placeholder="Р СџР В°РЎР‚Р С•Р В»РЎРЉ">
        <button type="submit">Р вЂ™Р С•Р в„–РЎвЂљР С‘</button>
      </form>
    <?php endif; ?>
  </div>
</header>
<main class="grid">
  <section class="panel news">
    <h1>Р СњР С•Р Р†Р С•РЎРѓРЎвЂљР С‘</h1>
    <article>
      <h2>Р СњР С•Р Р†Р В°РЎРЏ Р Р†Р ВµРЎР‚РЎРѓР С‘РЎРЏ Р С—РЎР‚Р С•Р ВµР С”РЎвЂљР В°</h2>
      <p>Р В­РЎвЂљР С• РЎРѓРЎвЂљР В°РЎР‚РЎвЂљР С•Р Р†Р В°РЎРЏ РЎРѓРЎвЂљРЎР‚Р В°Р Р…Р С‘РЎвЂ Р В° Р Р…Р С•Р Р†Р С•Р С–Р С• РЎРЏР Т‘РЎР‚Р В° Pokemon 8.0. Р РЋРЎвЂљР В°РЎР‚Р В°РЎРЏ Р В»Р С•Р С–Р С‘Р С”Р В° Р В±РЎС“Р Т‘Р ВµРЎвЂљ Р С—Р ВµРЎР‚Р ВµР Р…Р С•РЎРѓР С‘РЎвЂљРЎРЉРЎРѓРЎРЏ РЎРѓРЎР‹Р Т‘Р В° Р С—Р С•РЎРѓРЎвЂљР ВµР С—Р ВµР Р…Р Р…Р С•, Р В±Р ВµР В· Р С”Р С•РЎРѓРЎвЂљРЎвЂ№Р В»Р ВµР в„– Р С‘ Р Т‘РЎС“Р В±Р В»Р ВµР в„–.</p>
    </article>
    <article>
      <h2>Р СџР С•РЎР‚РЎРЏР Т‘Р С•Р С” Р СР С‘Р С–РЎР‚Р В°РЎвЂ Р С‘Р С‘</h2>
      <p>Р РЋР Р…Р В°РЎвЂЎР В°Р В»Р В° Р В°Р Р†РЎвЂљР С•РЎР‚Р С‘Р В·Р В°РЎвЂ Р С‘РЎРЏ, Р В±Р В°Р В·Р В°, РЎР‚Р С•РЎС“РЎвЂљР С‘Р Р…Р С– Р С‘ Р С‘Р С–РЎР‚Р С•Р Р†РЎвЂ№Р Вµ РЎРЊР С”РЎР‚Р В°Р Р…РЎвЂ№. Р вЂ”Р В°РЎвЂљР ВµР С Р С”Р В°РЎР‚РЎвЂљР В°, РЎвЂЎР В°РЎвЂљ, Р В±Р С•Р С‘, Р С‘Р Р…Р Р†Р ВµР Р…РЎвЂљР В°РЎР‚РЎРЉ Р С‘ Р В°Р Т‘Р СР С‘Р Р…Р С”Р В°.</p>
    </article>
  </section>
  <aside class="panel">
    <h2>Р СћР С•Р С— Р В±Р С•Р в„–РЎвЂ Р С•Р Р†</h2>
    <?php foreach ($fighters as $index => $fighter): ?>
      <div class="rank-row">
        <span><?= $index + 1 ?>. <?= View::e($fighter['login'] ?? ('ID ' . $fighter['id'])) ?></span>
        <b><?= number_format((int) ($fighter['rang_b'] ?? 0), 0, '.', ' ') ?></b>
      </div>
    <?php endforeach; ?>
    <h2>Р СџР С•Р С”Р ВµР Т‘Р ВµР С”РЎРѓ</h2>
    <?php foreach ($pokedex as $index => $row): ?>
      <div class="rank-row">
        <span><?= $index + 1 ?>. <?= View::e($row['login'] ?? ('ID ' . $row['id'])) ?></span>
        <b><?= (int) ($row['count_poke'] ?? 0) ?></b>
      </div>
    <?php endforeach; ?>
  </aside>
</main>
</body>
</html>
