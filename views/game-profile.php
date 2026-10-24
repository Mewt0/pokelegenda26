<?php
use Pokemon8\View\View;

$u = $user;
$clan = $u['clan'];
$party = $party ?? [];
$awards = $awards ?? [];
$gifts = $gifts ?? [];
$karma = $u['karma'] ?? ['score' => 0, 'state' => 'neutral', 'title' => 'Нейтральная репутация'];

$fmt = static fn (int $value): string => number_format($value, 0, '.', ' ');
$pokemonFrontSrc = static function (array $poke): string {
    $base = str_pad((string) (int) ($poke['baseNum'] ?? 0), 3, '0', STR_PAD_LEFT);
    $tips = strtolower((string) ($poke['tips'] ?? 'normal'));
    $folder = ($tips === 'shine' || $tips === 'shiny') ? 'shiny' : 'pok';
    return '/pok/' . $folder . '/' . $base . '.gif';
};
$lastOnline = (int) ($u['lastOnline'] ?? 0);
$lastOnlineText = $lastOnline > 0 ? date('Y-m-d H:i', $lastOnline) : 'нет данных';
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Профиль <?= View::e($u['login']) ?> - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-profile.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body class="profile-page">
  <main class="wrap">
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Профиль тренера</strong>
    </nav>

    <section class="shell">
      <div class="hero">
        <aside class="side">
          <section class="card">
            <h2>Клан</h2>
            <?php if ((int) $clan['id'] > 0): ?>
              <div class="clan">
                <?php if ($clan['image'] !== ''): ?><img src="/img/clan/<?= View::e($clan['image']) ?>.png" alt=""><?php endif; ?>
                <div>
                  <b><?= View::e($clan['name']) ?></b><br>
                  <span class="empty"><?= View::e($clan['status'] ?: 'участник') ?></span>
                </div>
              </div>
              <div class="kv" style="margin-top:10px">
                <div class="k">Очки</div><div class="v"><?= $fmt((int) $clan['points']) ?></div>
                <div class="k">Репутация</div><div class="v"><?= $fmt((int) $clan['reputation']) ?></div>
              </div>
            <?php else: ?>
              <div class="empty">Тренер не состоит в клане.</div>
            <?php endif; ?>
          </section>

          <section class="card">
            <h2>Информация</h2>
            <div class="info"><?= View::e($u['info'] !== '' ? $u['info'] : 'Информация о тренере пока не заполнена.') ?></div>
          </section>

          <section class="card">
            <h2>Коллекция</h2>
            <div class="collection">
              <div class="score"><span>Покедекс</span><b><?= (int) $u['normalDex'] ?>/649</b></div>
              <div class="score"><span>Шайнидекс</span><b><?= (int) $u['shinyDex'] ?>/649</b></div>
            </div>
          </section>
        </aside>

        <section class="center">
          <div class="namebar">
            <span class="badge <?= $u['online'] ? 'online' : '' ?>"><?= $u['online'] ? 'Онлайн' : 'Оффлайн' ?></span>
            <h1><?= View::e($u['login']) ?></h1>
            <span class="badge"><?= View::e($u['group']) ?></span>
          </div>

          <div class="party">
            <?php for ($i = 0; $i < 6; $i++): $poke = $party[$i] ?? null; ?>
              <div class="poke">
                <?php if ($poke): ?>
                  <img src="<?= View::e($pokemonFrontSrc($poke)) ?>" alt="">
                  <b><?= View::e($poke['name']) ?> Lv.<?= (int) $poke['level'] ?></b>
                  <div class="hp"><i style="width:<?= max(0, min(100, ((int) $poke['hp'] / max(1, (int) $poke['hpMax'])) * 100)) ?>%"></i></div>
                <?php else: ?>
                  <img src="/img/info/pokeball2.png" alt="">
                  <b class="empty">Пусто</b>
                <?php endif; ?>
              </div>
            <?php endfor; ?>
          </div>

          <div class="avatar">
            <img src="<?= View::e($u['avatar']) ?>" alt="">
          </div>

          <div style="text-align:center">
            <img class="rank-img" src="<?= View::e($u['rankImage']) ?>" alt="">
          </div>
        </section>

        <aside class="side right">
          <section class="card">
            <h2>Рейтинг</h2>
            <div class="score pvp"><span>PVP: <?= View::e($u['pvpTitle']) ?></span><b><?= $fmt((int) $u['pvpRating']) ?></b></div>
            <div class="score pve" style="margin-top:8px"><span>PVE: <?= View::e($u['pveTitle']) ?></span><b><?= $fmt((int) $u['pveRating']) ?></b></div>
            <div class="score karma <?= View::e($karma['state']) ?>" style="margin-top:8px"><span><?= View::e($karma['title']) ?></span><b><?= $fmt((int) $karma['score']) ?></b></div>
            <div class="kv" style="margin-top:10px">
              <div class="k">Квестовые очки</div><div class="v"><?= $fmt((int) $u['questRating']) ?></div>
              <div class="k">Ранг</div><div class="v"><?= View::e($u['rank']) ?></div>
            </div>
          </section>

          <section class="card">
            <h2>Локация</h2>
            <div class="kv">
              <div class="k">Город</div><div class="v"><?= View::e($u['town']) ?></div>
              <div class="k">Место</div><div class="v"><?= View::e($u['location']) ?></div>
              <div class="k">Регистрация</div><div class="v"><?= View::e($u['registeredAt']) ?></div>
              <div class="k">Был в игре</div><div class="v"><?= View::e($lastOnlineText) ?></div>
            </div>
          </section>

          <?php if (!empty($viewerOwnsProfile)): ?>
            <section class="card">
              <h2>Почта</h2>
              <form class="email-form" action="/profile/email" method="post">
                <input type="hidden" name="_csrf" value="<?= View::e($csrfToken ?? '') ?>">
                <input name="EMAIL" type="email" maxlength="100" value="<?= View::e((string) ($u['email'] ?? '')) ?>" placeholder="Email для восстановления пароля">
                <button type="submit">Сохранить почту</button>
              </form>
              <p class="empty" style="margin-top:8px">
                <?= $u['email'] !== '' ? ($u['emailVerified'] ? 'Почта подтверждена.' : 'Почта сохранена, подтверждение будет подключено позже.') : 'Почта не привязана. Без неё восстановление пароля через email недоступно.' ?>
              </p>
            </section>
          <?php endif; ?>

          <section class="card">
            <h2>Награды</h2>
            <div class="present-grid">
              <?php if ($awards): foreach ($awards as $item): ?>
                <span class="present" title="<?= View::e($item['name'] . ' ' . $item['title']) ?>"><img src="<?= View::e($item['image']) ?>" alt=""></span>
              <?php endforeach; else: ?>
                <span class="empty">Наград пока нет.</span>
              <?php endif; ?>
            </div>
          </section>

          <section class="card">
            <h2>Подарки</h2>
            <div class="present-grid">
              <?php if ($gifts): foreach ($gifts as $item): ?>
                <span class="present" title="<?= View::e($item['name'] . ' ' . $item['title']) ?>"><img src="<?= View::e($item['image']) ?>" alt=""></span>
              <?php endforeach; else: ?>
                <span class="empty">Подарков пока нет.</span>
              <?php endif; ?>
            </div>
          </section>
        </aside>
      </div>
    </section>
  </main>
  <script src="<?= \Pokemon8\View\View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
</body>
</html>
