<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0 - Игровой мир</title>
  <style>
    :root {
      --bg: #eef2f5;
      --panel: #ffffff;
      --line: #cfd8e3;
      --text: #172033;
      --muted: #66758a;
      --accent: #1d66c2;
      --accent-soft: #e8f2ff;
      --danger: #b42318;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      background: var(--bg);
      color: var(--text);
      font: 14px/1.45 Tahoma, Arial, sans-serif;
    }
    .world {
      min-height: 100vh;
      display: grid;
      grid-template-columns: minmax(0, 1fr) 280px;
      grid-template-rows: minmax(360px, 1fr) 280px 56px;
    }
    .location {
      grid-column: 1 / 3;
      padding: 14px;
      background: #676a67;
      overflow: auto;
    }
    .location-card {
      background: var(--panel);
      border: 1px solid rgba(0,0,0,.18);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,.16);
    }
    .location-title {
      margin: 0;
      padding: 14px 18px;
      text-align: center;
      font: 700 28px/1.1 Georgia, "Times New Roman", serif;
      background: #d6d9d4;
      border-bottom: 1px solid #b8bdb7;
    }
    .location-main {
      display: grid;
      grid-template-columns: 320px minmax(0, 1fr);
      gap: 12px;
      padding: 12px;
      background: #e3e5e1;
    }
    .location-image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #b8bdb7;
      background: #dce3e8;
    }
    .location-text {
      min-height: 180px;
      padding: 12px;
      border: 1px solid #b8bdb7;
      border-radius: 6px;
      background: rgba(255,255,255,.45);
      font: italic 20px/1.35 Georgia, "Times New Roman", serif;
    }
    .moves, .npc-strip {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      padding: 12px;
      background: #d6d9d4;
      border-top: 1px solid #b8bdb7;
    }
    .npc-strip { background: #edf1ec; }
    .move-btn, .npc-btn {
      border: 1px solid #aeb7c2;
      background: #fff;
      color: #111;
      border-radius: 6px;
      padding: 8px 12px;
      cursor: pointer;
      font-weight: 700;
    }
    .move-btn:hover, .npc-btn:hover { border-color: var(--accent); color: var(--accent); }
    .move-btn:disabled { opacity: .55; cursor: wait; }
    .npc-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .npc-icon {
      width: 24px;
      height: 24px;
      display: inline-grid;
      place-items: center;
      border-radius: 50%;
      background: var(--accent-soft);
      color: var(--accent);
      font-size: 13px;
      line-height: 1;
    }
    .npc-panel {
      display: none;
      margin: 12px;
      padding: 12px;
      border: 1px solid #b8bdb7;
      border-radius: 6px;
      background: rgba(255,255,255,.72);
      color: #111;
    }
    .npc-panel.is-open { display: block; }
    .npc-panel h2 { margin: 0 0 6px; font-size: 18px; }
    .npc-panel p { margin: 0; color: #344154; }
    .chat {
      grid-column: 1;
      grid-row: 2;
      background: #f7f9fc;
      border-top: 1px solid var(--line);
      border-right: 1px solid var(--line);
      padding: 12px;
      display: grid;
      grid-template-rows: 1fr auto;
      gap: 10px;
    }
    .chat-log {
      overflow: auto;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 8px;
      padding: 10px;
      font-weight: 700;
    }
    .chat-form {
      display: grid;
      grid-template-columns: 180px 1fr 48px;
      gap: 8px;
    }
    .chat-form input {
      height: 38px;
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 0 10px;
      font: inherit;
      background: #fff;
    }
    .chat-form button, .actionbar button, .actionbar a {
      height: 38px;
      border: 1px solid var(--line);
      border-radius: 6px;
      background: #fff;
      color: #174f91;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 14px;
      text-decoration: none;
      white-space: nowrap;
    }
    .users {
      grid-column: 2;
      grid-row: 2;
      background: #676a67;
      color: #050505;
      padding: 12px;
      overflow: auto;
    }
    .users h2 {
      margin: 0 0 8px;
      text-align: center;
      font-size: 16px;
    }
    .user-row {
      display: flex;
      gap: 6px;
      align-items: center;
      min-width: 0;
      margin: 4px 0;
      font-weight: 700;
    }
    .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #8a8f92;
      flex: 0 0 auto;
    }
    .dot.on { background: #20b15a; }
    .user-name {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .user-tags { color: #222; font-size: 12px; font-weight: 400; }
    .actionbar {
      grid-column: 1 / 3;
      grid-row: 3;
      display: flex;
      gap: 10px;
      align-items: center;
      padding: 8px 14px;
      background: #f4f6f9;
      border-top: 1px solid var(--line);
      overflow-x: auto;
    }
    .actionbar input {
      height: 38px;
      border: 1px solid var(--line);
      border-radius: 6px;
      padding: 0 10px;
    }
    .status {
      margin-left: auto;
      color: var(--muted);
      font-size: 13px;
      white-space: nowrap;
    }
    .error {
      color: var(--danger);
      font-weight: 700;
    }
    .muted { color: var(--muted); }
    @media (max-width: 900px) {
      .world { grid-template-columns: 1fr; grid-template-rows: auto 260px 220px 56px; }
      .location, .chat, .users, .actionbar { grid-column: 1; }
      .location { grid-row: 1; }
      .chat { grid-row: 2; }
      .users { grid-row: 3; }
      .actionbar { grid-row: 4; }
      .location-main { grid-template-columns: 1fr; }
      .chat-form { grid-template-columns: 1fr 44px; }
      .chat-form input:first-child { display: none; }
    }
  </style>
</head>
<body>
  <main class="world" data-csrf="<?= View::e($csrf) ?>">
    <section class="location" id="location">
      <div class="location-card">
        <h1 class="location-title" id="locationTitle">Загрузка...</h1>
        <div class="location-main">
          <img class="location-image" id="locationImage" alt="">
          <div class="location-text" id="locationText"></div>
        </div>
        <div class="npc-strip" id="npcs"></div>
        <div class="npc-panel" id="npcPanel" aria-live="polite"></div>
        <div class="moves" id="moves"></div>
      </div>
    </section>

    <section class="chat">
      <div class="chat-log" id="chatLog"></div>
      <form class="chat-form" id="chatForm">
        <input value="<?= View::e($login) ?>" readonly>
        <input id="chatInput" placeholder="Сообщение..." autocomplete="off">
        <button type="submit">▶</button>
      </form>
    </section>

    <aside class="users">
      <h2 id="usersTitle">Игроки</h2>
      <div id="usersList"></div>
    </aside>

    <nav class="actionbar">
      <button type="button" id="pveButton">Нападение: выкл</button>
      <button type="button">Режим: общий</button>
      <input placeholder="Ник">
      <a href="/game/pokemon">Покемоны</a>
      <a href="/game/items">Инвентарь</a>
      <a href="/game/quests">Квесты</a>
      <a href="/game/battle/pvp">Бои</a>
      <a href="/game/messages">Почта</a>
      <span class="status" id="status">Готово</span>
    </nav>
  </main>

  <script>
    const app = document.querySelector('.world');
    const csrf = app.dataset.csrf;
    const state = { busy: false, locationId: 0, activeNpc: null };

    function setStatus(message, isError = false) {
      const el = document.getElementById('status');
      el.textContent = message;
      el.className = isError ? 'status error' : 'status';
    }

    function render(payload) {
      if (!payload || payload.ok !== true) {
        setStatus(payload && payload.message ? payload.message : 'Не удалось загрузить состояние.', true);
        return;
      }

      const location = payload.location;
      state.locationId = Number(location.id || 0);
      document.getElementById('locationTitle').textContent = location.title;
      document.getElementById('locationImage').src = location.image;
      document.getElementById('locationImage').alt = location.title;
      document.getElementById('locationText').textContent = location.description || ('Локация #' + location.id);

      renderNpcs(location.npcs || []);
      renderMoves(payload.moves || []);
      renderUsers(location.title, payload.users || []);

      if (payload.chatEvent && payload.chatEvent.text) {
        const line = document.createElement('div');
        line.textContent = payload.chatEvent.text;
        document.getElementById('chatLog').appendChild(line);
      }

      setStatus('Готово');
    }

    function renderNpcs(npcs) {
      const panel = document.getElementById('npcPanel');
      panel.className = 'npc-panel';
      panel.innerHTML = '';

      const list = document.getElementById('npcs');
      list.innerHTML = '';
      for (const npc of npcs) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'npc-btn';
        button.innerHTML = '<span class="npc-icon"></span><span class="npc-title"></span>';
        button.querySelector('.npc-icon').textContent = iconLabel(npc.icon);
        button.querySelector('.npc-title').textContent = npc.title;
        button.addEventListener('click', () => openNpc(npc));
        list.appendChild(button);
      }

      if (!list.children.length) {
        const empty = document.createElement('span');
        empty.className = 'muted';
        empty.textContent = 'NPC на этой локации пока переносятся.';
        list.appendChild(empty);
      }
    }

    function renderMoves(movesData) {
      const moves = document.getElementById('moves');
      moves.innerHTML = '';
      for (const move of movesData) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'move-btn';
        button.textContent = move.title;
        button.dataset.locationId = move.id;
        button.addEventListener('click', () => moveTo(move.id));
        moves.appendChild(button);
      }
    }

    function renderUsers(locationTitle, users) {
      document.getElementById('usersTitle').textContent = locationTitle + ' (' + users.length + ')';
      const usersList = document.getElementById('usersList');
      usersList.innerHTML = '';
      for (const user of users) {
        const row = document.createElement('div');
        row.className = 'user-row';
        row.innerHTML = '<span class="dot ' + (user.online ? 'on' : '') + '"></span>' +
          '<span class="user-name"></span><span class="user-tags"></span>';
        row.querySelector('.user-name').textContent = user.login;
        row.querySelector('.user-tags').textContent = (user.online ? 'on' : 'off') + (user.pveButton ? ' / pve' : '');
        usersList.appendChild(row);
      }
    }

    function iconLabel(icon) {
      const labels = { cross: '+', shop: '$', mentor: 'i', quest: '?', person: '@' };
      return labels[icon] || '@';
    }

    async function openNpc(npc, overrideParams = null) {
      state.activeNpc = npc;
      const params = new URLSearchParams();
      params.set('location_id', state.locationId);
      for (const [key, value] of Object.entries(overrideParams || npc.params || {})) {
        params.set(key, value);
      }

      try {
        setStatus('NPC...');
        const response = await fetch('/api/location/npc?' + params.toString(), { credentials: 'same-origin' });
        renderNpcDialog(await response.json());
      } catch (error) {
        setStatus('NPC не отвечает.', true);
      }
    }

    async function runNpcAction(action) {
      if (!state.activeNpc) return;

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('location_id', state.locationId);
      body.set('action', action);
      for (const [key, value] of Object.entries(state.activeNpc.params || {})) {
        body.set(key, value);
      }

      try {
        setStatus('NPC...');
        const response = await fetch('/api/location/npc/action', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        renderNpcDialog(await response.json());
      } catch (error) {
        setStatus('Действие NPC не выполнено.', true);
      }
    }

    function renderNpcDialog(payload) {
      if (!payload || payload.ok !== true) {
        setStatus(payload && payload.message ? payload.message : 'NPC не отвечает.', true);
        return;
      }

      const npc = payload.npc;
      const panel = document.getElementById('npcPanel');
      panel.className = 'npc-panel is-open';
      panel.innerHTML = '';

      const title = document.createElement('h2');
      title.textContent = npc.title;
      const body = document.createElement('p');
      body.textContent = npc.text;

      panel.appendChild(title);
      panel.appendChild(body);

      const choices = document.createElement('div');
      choices.className = 'npc-strip';
      for (const choice of npc.choices || []) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'npc-btn';
        button.textContent = choice.label || '...';
        if (choice.hint) {
          button.title = choice.hint;
        }
        button.disabled = !!choice.disabled;
        if (choice.close) {
          button.addEventListener('click', () => {
            panel.className = 'npc-panel';
            panel.innerHTML = '';
            setStatus('Готово');
          });
        } else if (choice.action) {
          button.addEventListener('click', () => runNpcAction(choice.action));
        } else if (choice.params) {
          button.addEventListener('click', () => openNpc(state.activeNpc, choice.params));
        }
        choices.appendChild(button);
      }
      if (choices.children.length) {
        panel.appendChild(choices);
      }

      setStatus('NPC: ' + npc.title);
    }

    async function loadState() {
      setStatus('Загрузка...');
      const response = await fetch('/api/game/state', { credentials: 'same-origin' });
      render(await response.json());
    }

    async function moveTo(locationId) {
      if (state.busy) return;
      state.busy = true;
      setStatus('Переход...');
      document.querySelectorAll('.move-btn').forEach(button => button.disabled = true);

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('location_id', locationId);

      try {
        const response = await fetch('/api/map/move', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        render(await response.json());
      } catch (error) {
        setStatus('Переход не выполнен.', true);
      } finally {
        state.busy = false;
        document.querySelectorAll('.move-btn').forEach(button => button.disabled = false);
      }
    }

    document.getElementById('chatForm').addEventListener('submit', event => {
      event.preventDefault();
      const input = document.getElementById('chatInput');
      if (!input.value.trim()) return;
      const line = document.createElement('div');
      line.textContent = input.value.trim();
      document.getElementById('chatLog').appendChild(line);
      input.value = '';
    });

    loadState();
  </script>
</body>
</html>
