<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Команда монстров - Pokemon 8.0</title>
  <style>
    * { box-sizing: border-box; }
    html, body { width: 100%; height: 100%; }
    body {
      margin: 0;
      overflow: hidden;
      background: #dfeaf5;
      color: #102544;
      font: 14px/1.35 "Trebuchet MS", Tahoma, Arial, sans-serif;
    }
    button { font: inherit; cursor: pointer; }
    .team-window {
      height: 100vh;
      display: grid;
      grid-template-rows: 42px minmax(0, 1fr) 28px;
      background: #e5eff8;
      border: 1px solid #8ea7bf;
      box-shadow: inset 0 1px 0 rgba(255,255,255,.72);
      overflow: hidden;
    }
    .team-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 14px;
      color: #061a32;
      font: 800 24px/1 Georgia, "Times New Roman", serif;
      letter-spacing: .02em;
      text-transform: uppercase;
    }
    .team-head-actions { display: inline-flex; align-items: center; gap: 8px; }
    .team-icon-btn,
    .team-close {
      width: 30px;
      height: 30px;
      display: grid;
      place-items: center;
      border: 0;
      background: transparent;
      color: #526678;
      font: 400 24px/1 Arial, sans-serif;
    }
    .team-close { font-size: 30px; }
    .team-body {
      min-height: 0;
      display: grid;
      grid-template-columns: 228px minmax(0, 1fr);
      gap: 14px;
      padding: 6px 12px 4px;
    }
    .team-body.is-overview {
      grid-template-columns: 1fr;
    }
    .team-list {
      min-width: 0;
      min-height: 0;
      display: grid;
      grid-template-columns: 1fr;
      align-content: start;
      gap: 8px;
      overflow-y: auto;
      overflow-x: hidden;
      padding: 2px 4px 2px 0;
    }
    .team-list.is-overview {
      grid-template-columns: repeat(auto-fill, 228px);
      gap: 10px;
      overflow: auto;
      padding: 2px 0;
    }
    .team-list.is-detail {
      overflow-y: auto;
      overflow-x: hidden;
    }
    .poke-tile {
      height: 104px;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: rgba(230, 238, 246, .84);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
      position: relative;
      overflow: hidden;
      text-align: left;
      padding: 6px 8px;
    }
    .team-list.is-overview .poke-tile,
    .team-list.is-detail .poke-tile {
      height: 150px;
    }
    .team-list.is-overview.is-many .poke-tile {
      height: 146px;
    }
    .poke-tile.is-active { border-color: #5d8cc0; box-shadow: 0 0 0 2px rgba(72,132,209,.14) inset; }
    .poke-tile .ball { position: absolute; left: 6px; top: 9px; width: 23px; height: 23px; object-fit: contain; }
    .poke-tile .lock { position: absolute; right: 8px; top: 10px; color: #8190a0; font-size: 13px; }
    .tile-icons { position: absolute; left: 25px; top: 37px; display: grid; gap: 3px; }
    .tile-icons span {
      width: 16px;
      height: 16px;
      display: grid;
      place-items: center;
      border: 1px solid #77899a;
      border-radius: 3px;
      background: #d9f1cb;
      color: #17365e;
      font-size: 10px;
      font-weight: 800;
    }
    .tile-icons span:nth-child(2) { background: #d9c5ff; }
    .tile-art { height: 58px; margin: 0 24px 0 42px; display: grid; place-items: center; }
    .tile-art img { max-width: 116px; max-height: 66px; object-fit: contain; }
    .team-list.is-overview .tile-art,
    .team-list.is-detail .tile-art {
      height: 91px;
    }
    .team-list.is-overview .tile-art img,
    .team-list.is-detail .tile-art img {
      max-width: 176px;
      max-height: 110px;
    }
    .team-list.is-overview.is-many .tile-art {
      height: 86px;
    }
    .team-list.is-overview.is-many .tile-art img {
      max-width: 162px;
      max-height: 102px;
    }
    .tile-lvl { margin-left: 5px; color: #071b30; font: 800 17px/1 Georgia, serif; }
    .hpbar { height: 7px; margin: 3px 0 5px; border: 1px solid #aebccc; border-radius: 999px; background: #d5e0ea; overflow: hidden; }
    .hpbar i { display: block; height: 100%; background: #0ac18f; }
    .tile-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .tile-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #0b1726; font: 800 14px/1 Georgia, serif; text-transform: uppercase; }
    .gender { color: #6c87a4; font-size: 17px; }
    .empty-tile {
      height: 56px;
      display: grid;
      place-items: center;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: rgba(231,239,247,.62);
      color: #637589;
      font-size: 16px;
    }
    .pager { text-align: center; color: #2a7dcc; font-size: 18px; letter-spacing: 5px; }
    .detail {
      min-width: 0;
      min-height: 0;
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      position: relative;
      overflow: hidden;
    }
    .team-body.is-overview .detail {
      display: none;
    }
    .detail-left { min-width: 0; display: grid; grid-template-rows: 142px auto; gap: 6px; }
    .detail-art { display: grid; place-items: center; cursor: pointer; border-radius: 6px; }
    .detail-art:hover { background: rgba(255,255,255,.34); }
    .detail-art img { max-width: 198px; max-height: 142px; object-fit: contain; }
    .move-list { min-width: 0; display: grid; gap: 5px; }
    .move-slot {
      height: 50px;
      display: grid;
      grid-template-columns: 42px minmax(0, 1fr);
      align-items: center;
      gap: 7px;
      border: 1px solid #a8b8c9;
      border-radius: 6px;
      background: rgba(231,239,247,.9);
      padding: 4px;
      text-align: left;
    }
    .move-slot.is-empty { opacity: .72; }
    .move-icon {
      width: 38px;
      height: 38px;
      display: grid;
      place-items: center;
      border: 1px solid rgba(20, 28, 36, .46);
      border-radius: 4px;
      background: linear-gradient(145deg, #d7d7d7, #a9a9a9);
      color: #1e2430;
      font-weight: 900;
      font-size: 22px;
      line-height: 1;
      text-shadow: 0 1px 0 rgba(255,255,255,.38);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.68), inset 0 -2px 0 rgba(0,0,0,.18), 0 1px 1px rgba(0,0,0,.14);
    }
    .move-icon img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: contain;
    }
    .type-normal { background: linear-gradient(145deg, #d6c39b, #9f8354); color: #5a3d1d; }
    .type-fire { background: linear-gradient(145deg, #ff9b42, #c44720); color: #6f190c; }
    .type-water { background: linear-gradient(145deg, #4bc9ef, #1688bd); color: #063c67; }
    .type-grass { background: linear-gradient(145deg, #8be468, #37a53d); color: #134e20; }
    .type-electric { background: linear-gradient(145deg, #fff153, #e7b900); color: #7a4c00; }
    .type-ice { background: linear-gradient(145deg, #b9f2ff, #64cce9); color: #1c6680; }
    .type-fighting { background: linear-gradient(145deg, #d7745c, #a63d35); color: #5f1a17; }
    .type-poison { background: linear-gradient(145deg, #d785ff, #9049c8); color: #4f1a79; }
    .type-ground { background: linear-gradient(145deg, #d2ad65, #8b622c); color: #4e3516; }
    .type-flying { background: linear-gradient(145deg, #93d7ff, #5a98d6); color: #174c84; }
    .type-psychic { background: linear-gradient(145deg, #ff83c6, #cf3b86); color: #7c1648; }
    .type-bug { background: linear-gradient(145deg, #d8df45, #91a721); color: #47560f; }
    .type-rock { background: linear-gradient(145deg, #c5b08c, #7e6a4c); color: #463824; }
    .type-ghost { background: linear-gradient(145deg, #7760b5, #3d2d70); color: #1e1645; }
    .type-dragon { background: linear-gradient(145deg, #b46eff, #6941bf); color: #361d7d; }
    .type-dark { background: linear-gradient(145deg, #6d6570, #29282d); color: #08090c; }
    .type-steel { background: linear-gradient(145deg, #c9d2dc, #8a99a7); color: #334250; }
    .type-fairy { background: linear-gradient(145deg, #ff9bd3, #db5d9f); color: #7d2455; }
    .move-main { min-width: 0; display: grid; gap: 4px; position: relative; }
    .move-name { min-width: 0; padding-right: 28px; color: #aa1616; font: 800 14px/1 Georgia, serif; text-transform: uppercase; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .move-pp { position: absolute; right: 0; top: 0; color: #526173; font-weight: 700; }
    .move-hint { color: #526173; font-size: 11px; }
    .detail-main { min-width: 0; padding-top: 2px; }
    .detail-title { margin: 0; color: #102544; font: 800 24px/1.08 Georgia, "Times New Roman", serif; text-transform: uppercase; overflow-wrap: anywhere; }
    .detail-title[role="button"] { cursor: pointer; }
    .detail-title[role="button"]:hover { color: #0d5ca8; }
    .detail-sub { margin-top: 3px; color: #123965; font-size: 14px; overflow-wrap: anywhere; }
    .badge {
      display: inline-flex;
      align-items: center;
      min-height: 22px;
      margin-top: 6px;
      padding: 0 8px;
      border: 1px solid #9fb0c0;
      border-radius: 4px;
      background: #eaf2fb;
      color: #183c62;
      font-weight: 700;
    }
    .stats { max-width: 100%; margin-top: 16px; display: grid; gap: 4px; }
    .stat-row { display: grid; grid-template-columns: 104px 38px minmax(92px, 1fr) 22px; align-items: center; gap: 7px; color: #1f3855; }
    .stat-value { text-align: right; color: #526173; font-weight: 700; }
    .stat-track { height: 10px; border: 1px solid #aab8c6; border-radius: 99px; background: #edf3f9; overflow: hidden; }
    .stat-track i { display: block; height: 100%; background: #d0c96a; }
    .stat-row.hp .stat-track i { background: #14bf91; }
    .stat-plus {
      width: 22px;
      height: 22px;
      border: 0;
      background: transparent;
      color: #00b86b;
      font: 800 20px/1 Arial, sans-serif;
    }
    .ev-left { justify-self: end; color: #00a75f; font-weight: 900; }
    .meta { margin-top: 18px; color: #0d5ca8; font-weight: 700; }
    .starter {
      float: right;
      min-height: 26px;
      padding: 0 10px;
      border: 0;
      border-radius: 5px;
      background: #91af13;
      color: #fff;
      font-weight: 800;
    }
    .learn-pop,
    .ev-pop {
      position: fixed;
      z-index: 40;
      border-radius: 5px;
      box-shadow: 0 8px 22px rgba(20,36,54,.28);
    }
    .learn-pop {
      width: 248px;
      max-height: min(360px, calc(100vh - 18px));
      overflow: auto;
      padding: 10px;
      border: 1px solid #a8b8c9;
      background: #e5eff8;
      color: #102544;
    }
    .learn-pop h3 { margin: 0 0 8px; color: #102544; font-size: 16px; }
    .learn-row {
      width: 100%;
      min-height: 56px;
      display: grid;
      grid-template-columns: 46px minmax(0, 1fr);
      gap: 8px;
      align-items: center;
      border: 1px solid #a8b8c9;
      border-radius: 5px;
      background: rgba(231,239,247,.96);
      text-align: left;
      padding: 4px;
      color: #5b3f86;
    }
    .learn-row + .learn-row { margin-top: 6px; }
    .learn-row strong { display: block; color: #8d4aa2; font: 800 16px/1 Georgia, serif; text-transform: uppercase; }
    .learn-row small { display: block; color: #526173; text-align: right; }
    .learn-empty { color: #637589; padding: 8px 2px; }
    .ev-pop {
      width: 264px;
      padding: 10px 12px 12px;
      background: rgba(12,14,18,.94);
      color: #fff;
      text-align: center;
    }
    .ev-pop strong { display: block; margin-bottom: 8px; }
    .ev-pop label { display: grid; grid-template-columns: 1fr 90px; align-items: center; gap: 8px; }
    .ev-pop input { min-width: 0; height: 28px; border: 1px solid #888; background: #4a4f56; color: #cad2dc; text-align: right; font: inherit; }
    .ev-pop button {
      width: 100%;
      height: 24px;
      margin-top: 8px;
      border: 1px solid #188bd6;
      border-radius: 3px;
      background: #1478bd;
      color: #fff;
      font-weight: 800;
    }
    .team-foot { display: flex; align-items: center; justify-content: space-between; padding: 0 14px 8px; color: #8190a0; font-weight: 700; }
    .status { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .is-bad { color: #a4161a; }
    @media (max-width: 760px) {
      body { overflow: auto; }
      .team-window { height: auto; min-height: 100vh; overflow: visible; }
      .team-body { grid-template-columns: 1fr; padding: 8px 10px; }
      .team-list { grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); max-height: 250px; }
      .detail { grid-template-columns: 1fr; }
      .detail-left { grid-template-rows: auto auto; }
      .detail-art { display: none; }
    }
  </style>
</head>
<body data-csrf="<?= View::e($csrf) ?>">
  <main class="team-window">
    <header class="team-head">
      <span>Команда монстров</span>
      <span class="team-head-actions">
        <button type="button" class="team-icon-btn" id="downloadBtn" aria-label="Скачать">⇩</button>
        <button type="button" class="team-close" id="closeBtn" aria-label="Закрыть">×</button>
      </span>
    </header>
    <section class="team-body">
      <aside class="team-list" id="pokemonList"></aside>
      <section class="detail" id="pokemonDetail" aria-live="polite"></section>
    </section>
    <footer class="team-foot">
      <span class="status" id="status">Загрузка...</span>
      <span id="detailId"></span>
    </footer>
  </main>

  <script>
    const csrf = document.body.dataset.csrf;
    const teamBody = document.querySelector('.team-body');
    const list = document.getElementById('pokemonList');
    const detail = document.getElementById('pokemonDetail');
    const status = document.getElementById('status');
    const detailId = document.getElementById('detailId');
    const slots = ['a', 'b', 'c', 'd'];
    let team = [];
    let selectedId = 0;
    let detailOpen = false;
    let activeLearn = null;

    document.getElementById('closeBtn').addEventListener('click', () => {
      if (window.parent && window.parent !== window) {
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
      } else {
        window.location.href = '/game';
      }
    });
    document.getElementById('downloadBtn').addEventListener('click', () => setStatus('Экспорт карточки будет подключен позже.'));
    document.addEventListener('click', event => {
      if (!event.target.closest('.learn-pop,.move-slot,.ev-pop,.stat-plus')) closePopups();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closePopups();
    });

    function setStatus(text, bad = false) {
      status.textContent = text;
      status.classList.toggle('is-bad', bad);
    }

    async function load(keepSelected = true) {
      try {
        const response = await fetch('/api/pokemon/moves', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus('Не удалось загрузить покемонов.', true);
          return;
        }
        team = payload.pokemon || [];
        if (!keepSelected) {
          detailOpen = false;
          selectedId = 0;
        } else if (!team.some(poke => Number(poke.id) === Number(selectedId))) {
          selectedId = Number((team[0] || {}).id || 0);
          detailOpen = !!selectedId;
        }
        renderAll();
        setStatus('Готово');
      } catch (error) {
        setStatus('Не удалось загрузить покемонов.', true);
      }
    }

    function renderAll() {
      renderTeamView();
      return;
      list.innerHTML = '';
      if (!team.length) {
        list.innerHTML = '<div class="empty-tile">- пусто -</div>';
        detail.innerHTML = '';
        return;
      }
      team.forEach(poke => list.appendChild(renderTile(poke)));
      const emptyCount = Math.max(0, 6 - team.length);
      for (let i = 0; i < emptyCount; i++) {
        const empty = document.createElement('div');
        empty.className = 'empty-tile';
        empty.textContent = '- пусто -';
        list.appendChild(empty);
      }
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      list.appendChild(pager);
      renderDetail(selectedPokemon());
    }

    function appendEmptyTile(target) {
      const empty = document.createElement('div');
      empty.className = 'empty-tile';
      empty.textContent = '- пусто -';
      target.appendChild(empty);
    }

    function appendPager(target) {
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      target.appendChild(pager);
    }

    function renderTeamView() {
      closePopups();
      teamBody.classList.toggle('is-overview', !detailOpen);
      list.innerHTML = '';

      if (!team.length) {
        list.className = 'team-list is-overview';
        appendEmptyTile(list);
        detail.innerHTML = '';
        detailId.textContent = '';
        return;
      }

      if (!detailOpen) {
        list.className = 'team-list is-overview' + (team.length > 3 ? ' is-many' : '');
        team.forEach(poke => list.appendChild(renderTile(poke)));
        for (let i = team.length; i < 6; i++) appendEmptyTile(list);
        detail.innerHTML = '';
        detailId.textContent = '';
        return;
      }

      const selected = selectedPokemon();
      list.className = 'team-list is-detail';
      list.appendChild(renderTile(selected));
      const moves = document.createElement('div');
      moves.className = 'move-list';
      slots.forEach((slot, index) => moves.appendChild(renderMoveSlot(selected, slot, index)));
      list.appendChild(moves);
      appendPager(list);
      renderPokemonDetail(selected);
    }

    function renderTilesOnly() {
      const scrollTop = list.scrollTop;
      list.querySelectorAll('.poke-tile').forEach(tile => {
        tile.classList.toggle('is-active', Number(tile.dataset.id) === Number(selectedId));
      });
      list.scrollTop = scrollTop;
    }

    function renderTile(poke) {
      const tile = document.createElement('button');
      tile.type = 'button';
      tile.className = 'poke-tile' + (Number(poke.id) === Number(selectedId) ? ' is-active' : '');
      tile.dataset.id = String(poke.id);
      tile.addEventListener('click', () => {
        if (detailOpen && Number(poke.id) === Number(selectedId)) {
          openDexPokemon(poke);
          return;
        }
        selectedId = Number(poke.id);
        detailOpen = true;
        renderAll();
      });
      tile.innerHTML = [
        '<img class="ball" src="/public/img/legacy/ui/menu-pokemon.png" alt="">',
        '<span class="lock" aria-hidden="true">▣</span>',
        '<div class="tile-icons"><span>ST</span><span>✦</span></div>',
        '<div class="tile-art"><img alt=""></div>',
        '<div class="tile-lvl"></div>',
        '<div class="hpbar"><i></i></div>',
        '<div class="tile-foot"><span class="tile-name"></span><span class="gender">♂</span></div>',
      ].join('');
      setSprite(tile.querySelector('.tile-art img'), poke);
      tile.querySelector('.tile-lvl').textContent = Number(poke.level || 0);
      tile.querySelector('.hpbar i').style.width = hpPercent(poke) + '%';
      tile.querySelector('.tile-name').textContent = displayName(poke);
      if (!poke.starter) tile.querySelector('.tile-icons span').textContent = '★';
      return tile;
    }

    function renderPokemonDetail(poke) {
      if (!poke) return;
      detailId.textContent = 'id' + Number(poke.id || 0);
      detail.innerHTML = [
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="stats"></div>',
        '<div class="meta">☻ ВаДИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(poke.baseNum) + ' ' + displayName(poke) + '  ♂';
      detail.querySelector('.starter').hidden = !poke.starter;
      renderStats(poke);
    }

    function renderDetail(poke) {
      closePopups();
      if (!poke) return;
      detailId.textContent = 'id' + Number(poke.id || 0);
      detail.innerHTML = [
        '<div class="detail-left"><div class="detail-art"><img alt=""></div><div class="move-list"></div></div>',
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="stats"></div>',
        '<div class="meta">☻ ВадИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      setSprite(detail.querySelector('.detail-art img'), poke);
      bindDexOpen(detail.querySelector('.detail-art'), poke);
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(poke.baseNum) + ' ' + displayName(poke) + '  ♂';
      detail.querySelector('.starter').hidden = !poke.starter;
      slots.forEach((slot, index) => detail.querySelector('.move-list').appendChild(renderMoveSlot(poke, slot, index)));
      renderStats(poke);
    }

    function renderMoveSlot(poke, slot, index) {
      const current = poke.moves && poke.moves[slot] ? poke.moves[slot] : { id: 0, name: 'Нет атаки' };
      const type = current.type || 'Normal';
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'move-slot' + (Number(current.id || 0) <= 0 ? ' is-empty' : '');
      row.innerHTML = [
        '<span class="move-icon"></span>',
        '<span class="move-main"><span class="move-name"></span><span class="move-pp"></span><span class="move-hint"></span></span>'
      ].join('');
      const icon = row.querySelector('.move-icon');
      icon.classList.add(typeClass(type));
      icon.innerHTML = '<img alt="">';
      const img = icon.querySelector('img');
      img.src = typeIconSrc(type);
      img.alt = typeLabel(type);
      icon.title = typeLabel(type);
      row.querySelector('.move-name').textContent = current.name || 'Нет атаки';
      row.querySelector('.move-pp').textContent = current.id ? ppText(current) : '';
      row.querySelector('.move-hint').textContent = current.id ? 'Нажмите, чтобы заменить' : 'Изучить атаку';
      row.addEventListener('click', event => {
        event.stopPropagation();
        showLearnPopup(row, poke, slot, current);
      });
      return row;
    }

    function renderStats(poke) {
      const level = Number(poke.level || 0);
      const hp = Number(poke.hp || 0);
      const hpMax = Number(poke.hpMax || 1);
      const values = [
        ['Счастье', 100, 100, false],
        ['Здоровье', hp, hpMax, true],
        ['Атака', Math.max(1, Math.round(level * 0.58)), 100, false],
        ['Защита', Math.max(1, Math.round(level * 0.58)), 100, false],
        ['Скорость', Math.max(1, Math.round(level * 0.55)), 100, false],
        ['Спец.атака', Math.max(1, Math.round(level * 0.64)), 100, false],
        ['Спец.защита', Math.max(1, Math.round(level * 0.64)), 100, false],
      ];
      const box = detail.querySelector('.stats');
      values.forEach(([label, value, max, isHp], index) => {
        const row = document.createElement('div');
        row.className = 'stat-row' + (isHp ? ' hp' : '');
        row.innerHTML = '<span></span><b class="stat-value"></b><span class="stat-track"><i></i></span><button type="button" class="stat-plus">+</button>';
        row.querySelector('span').textContent = label;
        row.querySelector('.stat-value').textContent = label === 'Счастье' ? '' : value;
        row.querySelector('i').style.width = Math.max(0, Math.min(100, value / Math.max(1, max) * 100)) + '%';
        row.querySelector('.stat-plus').addEventListener('click', event => {
          event.stopPropagation();
          showEvPopup(row.querySelector('.stat-plus'), label);
        });
        box.appendChild(row);
        if (index === values.length - 1) {
          const ev = document.createElement('b');
          ev.className = 'ev-left';
          ev.textContent = '18';
          box.appendChild(ev);
        }
      });
    }

    function showLearnPopup(anchor, poke, slot, current) {
      closePopups();
      activeLearn = { pokeId: Number(poke.id), slot };
      const pop = document.createElement('div');
      pop.className = 'learn-pop';
      pop.innerHTML = '<h3>Изучить атаку!</h3>';
      const currentId = Number(current && current.id || 0);
      const moves = (poke.learnableMoves || []).filter(move => Number(move.id) !== currentId).slice(0, 12);
      if (!moves.length) {
        const empty = document.createElement('div');
        empty.className = 'learn-empty';
        empty.textContent = 'Нет доступных атак.';
        pop.appendChild(empty);
      }
      moves.forEach(move => pop.appendChild(renderLearnRow(move)));
      document.body.appendChild(pop);
      placePopup(pop, anchor, 8);
    }

    function renderLearnRow(move) {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'learn-row';
      row.innerHTML = '<span class="move-icon"><img alt=""></span><span><strong></strong><small></small></span>';
      const icon = row.querySelector('.move-icon');
      icon.classList.add(typeClass(move.type || 'Normal'));
      icon.title = typeLabel(move.type || 'Normal');
      row.querySelector('img').src = typeIconSrc(move.type || 'Normal');
      row.querySelector('img').alt = typeLabel(move.type || 'Normal');
      row.querySelector('strong').textContent = move.name || 'Атака';
      row.querySelector('small').textContent = 'Lv.' + Number(move.level || 0) + (Number(move.pp || 0) > 0 ? ', ' + Number(move.pp || 0) + ' PP' : '');
      row.addEventListener('click', event => {
        event.stopPropagation();
        if (activeLearn) setMove(activeLearn.pokeId, activeLearn.slot, Number(move.id));
      });
      return row;
    }

    function showEvPopup(anchor, label) {
      closePopups();
      const pop = document.createElement('div');
      pop.className = 'ev-pop';
      pop.innerHTML = '<strong></strong><label><span>Увеличить EV на:</span><input type="number" min="1" max="18" value="1"></label><button type="button">Добавить</button>';
      pop.querySelector('strong').textContent = label;
      pop.querySelector('button').addEventListener('click', () => {
        setStatus('Прокачка EV будет подключена к серверу отдельно.');
        closePopups();
      });
      document.body.appendChild(pop);
      placePopup(pop, anchor, 10);
    }

    function placePopup(pop, anchor, offset) {
      const rect = anchor.getBoundingClientRect();
      const width = pop.offsetWidth;
      const height = pop.offsetHeight;
      let left = rect.left + rect.width / 2 - width / 2;
      let top = rect.bottom + offset;
      left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
      if (top + height > window.innerHeight - 8) {
        const above = rect.top - height - offset;
        top = above >= 8 ? above : Math.max(44, (window.innerHeight - height) / 2);
      }
      pop.style.left = left + 'px';
      pop.style.top = top + 'px';
    }

    function closePopups() {
      document.querySelectorAll('.learn-pop,.ev-pop').forEach(node => node.remove());
      activeLearn = null;
    }

    async function setMove(pokemonId, slot, moveId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('slot', slot);
      body.set('move_id', String(moveId));
      try {
        const response = await fetch('/api/pokemon/move', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        if (payload && payload.ok) {
          updateLocalMove(pokemonId, slot, moveId);
          renderAll();
        }
      } catch (error) {
        setStatus('Атака не обновлена.', true);
      }
    }

    function updateLocalMove(pokemonId, slot, moveId) {
      const poke = team.find(item => Number(item.id) === Number(pokemonId));
      if (!poke) return;
      if (!poke.moves) poke.moves = {};
      const move = (poke.learnableMoves || []).find(item => Number(item.id) === Number(moveId));
      poke.moves[slot] = move
        ? { id: Number(move.id), name: move.name, type: move.type || 'Normal', pp: Number(move.pp || 0), ppMax: Number(move.pp || 0) }
        : { id: 0, name: 'Нет атаки' };
    }

    function selectedPokemon() {
      return team.find(poke => Number(poke.id) === Number(selectedId)) || team[0] || null;
    }

    function displayName(poke) {
      return String(poke.name || ('Pokemon #' + Number(poke.baseNum || 0))).replace(/^#?\d+\s*/, '');
    }

    function hpPercent(poke) {
      return Math.max(0, Math.min(100, Number(poke.hp || 0) / Math.max(1, Number(poke.hpMax || 1)) * 100));
    }

    function pad3(value) {
      return String(Math.max(0, Number(value || 0))).padStart(3, '0');
    }

    function isShiny(poke) {
      return String(poke.name || '').toLowerCase().includes('shiny');
    }

    function typeKey(type) {
      return String(type || 'Normal').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function typeClass(type) {
      return 'type-' + typeKey(type);
    }

    function typeIconSrc(type) {
      const map = {
        bug: 1,
        dark: 2,
        dragon: 3,
        electric: 4,
        fighting: 5,
        fire: 6,
        flying: 7,
        ghost: 8,
        grass: 9,
        ground: 10,
        ice: 11,
        normal: 12,
        poison: 13,
        psychic: 14,
        steel: 15,
        rock: 16,
        water: 17,
        pokesno: 12,
        item: 12,
        topokes: 12
      };
      const names = {
        1: 'bug',
        2: 'dark',
        3: 'dragon',
        4: 'electric',
        5: 'fighting',
        6: 'fire',
        7: 'flying',
        8: 'ghost',
        9: 'grass',
        10: 'ground',
        11: 'ice',
        12: 'normal',
        13: 'poison',
        14: 'psychic',
        15: 'steel',
        16: 'rock',
        17: 'water'
      };
      return '/public/img/types/' + (names[map[typeKey(type)] || 12] || 'normal') + '.png';
    }

    function typeLabel(type) {
      return String(type || 'Normal');
    }

    function ppText(move) {
      const pp = Number(move.pp || 0);
      const ppMax = Number(move.ppMax || 0);
      if (ppMax > 0) return pp + '/' + ppMax;
      return Number(move.pp || 0) > 0 ? String(pp) : 'PP';
    }

    function openDexPokemon(poke) {
      const dexId = Number(poke.baseNum || 0);
      if (dexId <= 0) return;
      if (window.parent && window.parent !== window && window.parent.PokemonDex) {
        window.parent.PokemonDex.openPokemon(dexId);
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
        return;
      }
      window.location.href = '/game/pokedex?id=' + encodeURIComponent(String(dexId));
    }

    function setSprite(img, poke) {
      const num = Number(poke.baseNum || 0);
      const padded = pad3(num);
      const urls = [];
      const assetSlug = spriteSlug(displayName(poke));
      if (isShiny(poke)) {
        urls.push('/Pok/shine/' + num + '.png');
        urls.push('/Pok/shiny/' + padded + '.gif');
        urls.push('/Pok/shiny/' + num + '.gif');
      }
      urls.push('/Pok/normal/' + num + '.png');
      urls.push('/Pok/' + num + '.jpg');
      if (assetSlug) {
        urls.push('/public/img/pokemon/art/' + assetSlug + '.png');
        urls.push('/public/img/pokemon/small/' + assetSlug + '.png');
      }
      urls.push('/Pok/pok/' + padded + '.gif');
      urls.push('/Pok/anim/' + padded + '.gif');
      let index = 0;
      img.onerror = () => {
        index += 1;
        img.src = urls[index] || '/img/blank.gif';
      };
      img.src = urls[index] || '/img/blank.gif';
    }

    function spriteSlug(name) {
      return String(name || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function bindDexOpen(node, poke) {
      if (!node) return;
      node.tabIndex = 0;
      node.setAttribute('role', 'button');
      node.title = 'Открыть в Покедексе';
      node.addEventListener('click', () => openDexPokemon(poke));
      node.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openDexPokemon(poke);
        }
      });
    }

    load(false);
  </script>
</body>
</html>
