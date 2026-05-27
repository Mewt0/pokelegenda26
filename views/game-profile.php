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
  <title>Профиль <?= View::e($u['login']) ?> - Pokemon 8.0</title>
  <style>
    :root { --bg:#eef2f5; --panel:#fff; --soft:#f6f8fb; --line:#cfd8e3; --text:#172033; --muted:#66758a; --blue:#1d66c2; --green:#18864a; --gold:#a97100; }
    * { box-sizing:border-box; }
    body { margin:0; background:#dfe5eb; color:var(--text); font:14px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width:1160px; margin:0 auto; padding:18px; }
    .top { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .top a { color:#1d5faf; font-weight:700; text-decoration:none; }
    .shell { border:1px solid #aeb9c6; border-radius:8px; overflow:hidden; box-shadow:0 12px 28px rgba(0,0,0,.18); background:var(--panel); }
    .hero { display:grid; grid-template-columns:280px minmax(0,1fr) 280px; gap:0; min-height:560px; }
    .side, .center { padding:14px; }
    .side { background:#f1f4f7; border-right:1px solid var(--line); }
    .side.right { border-right:0; border-left:1px solid var(--line); }
    .center { background:linear-gradient(#ffffff,#f7fafc); display:grid; grid-template-rows:auto auto 1fr auto; gap:12px; }
    .card { background:#fff; border:1px solid var(--line); border-radius:8px; padding:12px; margin-bottom:10px; }
    .card h2 { margin:0 0 9px; font-size:14px; text-transform:uppercase; color:#506074; letter-spacing:.04em; }
    .kv { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:6px 12px; }
    .k { color:var(--muted); }
    .v { font-weight:700; text-align:right; }
    .namebar { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .namebar h1 { margin:0; min-width:0; font-size:24px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .badge { display:inline-flex; align-items:center; justify-content:center; min-height:24px; padding:3px 9px; border:1px solid var(--line); border-radius:999px; background:#fff; color:#4e6178; font-weight:700; white-space:nowrap; }
    .badge.online { color:#0f7a3a; border-color:#9bd7b2; background:#effaf2; }
    .avatar { display:grid; place-items:center; min-height:320px; border:1px solid var(--line); border-radius:8px; background:radial-gradient(circle at 50% 18%, #fff, #e9eef4); overflow:hidden; }
    .avatar img { max-width:100%; max-height:315px; object-fit:contain; image-rendering:auto; filter:drop-shadow(0 8px 16px rgba(0,0,0,.22)); }
    .rank-img { height:20px; object-fit:contain; }
    .party { display:grid; grid-template-columns:repeat(6, minmax(0,1fr)); gap:8px; }
    .poke { min-width:0; border:1px solid var(--line); border-radius:8px; background:#fff; padding:8px; text-align:center; }
    .poke img { width:54px; height:54px; object-fit:contain; image-rendering:auto; }
    .poke b { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12px; }
    .hp { height:6px; margin-top:6px; border-radius:99px; background:#dce4ec; overflow:hidden; }
    .hp i { display:block; height:100%; background:linear-gradient(90deg,#2aaa5b,#8bd55c); }
    .info { min-height:72px; color:#314157; white-space:pre-line; }
    .collection { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .score { padding:12px; border:1px solid var(--line); border-radius:8px; background:var(--soft); }
    .score span { display:block; color:var(--muted); }
    .score b { display:block; margin-top:3px; font-size:22px; }
    .score.pvp b { color:var(--blue); }
    .score.pve b { color:var(--green); }
    .score.karma.good b { color:var(--green); }
    .score.karma.bad b { color:#c73333; }
    .score.karma.neutral b { color:#64748b; }
    .present-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:7px; min-height:42px; }
    .present { aspect-ratio:1/1; display:grid; place-items:center; border:1px solid var(--line); border-radius:8px; background:#fff; }
    .present img { width:36px; height:36px; object-fit:contain; }
    .empty { color:var(--muted); font-size:13px; }
    .clan { display:flex; gap:10px; align-items:center; }
    .clan img { width:42px; height:42px; object-fit:contain; }
    .email-form { display:grid; gap:8px; }
    .email-form input, .email-form button { height:36px; border:1px solid var(--line); border-radius:7px; padding:0 10px; font:inherit; }
    .email-form button { background:var(--blue); color:#fff; font-weight:800; cursor:pointer; }
    @media (max-width: 980px) { .hero { grid-template-columns:1fr; } .side, .side.right { border:0; border-top:1px solid var(--line); } .party { grid-template-columns:repeat(3,1fr); } }
  </style>
</head>
<body>
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
</body>
</html>
