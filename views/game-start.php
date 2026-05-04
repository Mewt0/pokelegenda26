<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pokemon 8.0 - Игровой мир</title>
  <link rel="stylesheet" href="/css/game-start.css">
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
        <input value="<?= View::e($login) ?>" readonly class="chat-my-login">
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
      <button type="button" id="debugForceBattleBtn" style="display:none">DEBUG: бой (Дорога 1)</button>
      <button type="button">Режим: общий</button>
      <input placeholder="Ник">
      <a href="/game/pokemon" id="pokemonLink">Покемоны</a>
      <a href="/game/items" id="inventoryLink">Инвентарь</a>
      <a href="/game/profile">Профиль</a>
      <a href="#" data-open-dex="pokemon">Покедекс</a>
      <a href="#" data-open-dex="attacks">Атакадекс</a>
      <a href="/game/quests">Квесты</a>
      <a href="/game/battle/pvp">Бои</a>
      <a href="/game/messages">Почта</a>
      <span class="status" id="status">Готово</span>
    </nav>
  </main>
  <section class="inventory-overlay" id="inventoryOverlay" aria-hidden="true">
    <div class="inventory-window" role="dialog" aria-label="Инвентарь">
      <header class="inv-top">
        <span class="ico">☰</span><span class="ico">⚙</span><span class="ico">◈</span><span class="ico">★</span>
        <div class="inv-search">
          <input id="invSearchInput" placeholder="Начните вводить название">
        </div>
      </header>
      <div class="inv-grid-wrap">
        <div class="inv-grid" id="invGrid"></div>
      </div>
      <footer class="inv-bottom">
        <button type="button" id="invRefreshBtn">⟳</button>
        <button type="button" id="invPrevBtn">≪</button>
        <button type="button" id="invNextBtn">≫</button>
        <span id="invPageInfo">1/1</span>
        <span class="inv-slots">СЛОТОВ ЗАНЯТО: <span id="invSlotsCount">0</span></span>
        <button type="button" class="inv-close" id="invCloseBtn">Закрыть</button>
      </footer>
    </div>
  </section>
  <section class="pokemon-overlay" id="pokemonOverlay" aria-hidden="true">
    <div class="pokemon-window" role="dialog" aria-label="Покемоны">
      <header class="pokemon-modal-head">
        <strong>Покемоны</strong>
        <button type="button" id="pokemonCloseBtn">×</button>
      </header>
      <iframe id="pokemonFrame" title="Покемоны" src="about:blank"></iframe>
    </div>
  </section>
  <section class="battle-overlay" id="battleOverlay" aria-hidden="true">
    <div class="battle-window" role="dialog" aria-label="PvE бой">
      <header class="battle-head">
        <span id="battleTitle">Дикий бой</span>
        <span class="battle-head-right">
          <span id="battleRound">Раунд 1</span>
          <button type="button" id="battleReviewClose" class="battle-review-close" aria-label="Закрыть просмотр боя">&times;</button>
        </span>
      </header>
      <div class="battle-layout">
        <aside class="battle-left">
          <div class="battle-actions-col">
            <button type="button" id="battleMove1" class="battle-move-btn">Атака 1</button>
            <button type="button" id="battleMove2" class="battle-move-btn">Атака 2</button>
            <button type="button" id="battleMove3" class="battle-move-btn">Атака 3</button>
            <button type="button" id="battleMove4" class="battle-move-btn">Атака 4</button>
          </div>
          <div class="battle-subactions">
            <select id="battleSwitchSelect" class="wide"></select>
            <button type="button" id="battleSwitchBtn">Сменить</button>
            <button type="button" id="battleEscapeBtn" class="danger">Сбежать</button>
          </div>
        </aside>

        <main class="battle-center">
          <div class="battle-arena">
            <div class="battle-arena-top">
              <article class="fighter fighter-enemy">
                <h3 id="battleEnemyName">Дикий покемон</h3>
                <div class="hpbar"><div class="hpfill" id="battleEnemyHpBar" style="width:100%"></div></div>
                <div id="battleEnemyHp" class="muted">HP 0/0</div>
                <div class="battle-status-badges" id="battleEnemyStatuses"></div>
              </article>
            </div>
            <div class="battle-arena-field">
              <div class="sprite sprite-enemy" id="battleEnemySprite" aria-hidden="true"><img id="battleEnemySpriteImg" alt=""></div>
              <div class="sprite sprite-player" id="battlePlayerSprite" aria-hidden="true"><img id="battlePlayerSpriteImg" alt=""></div>
            </div>
            <div class="battle-arena-bottom">
              <article class="fighter fighter-player">
                <h3 id="battlePlayerName">Ваш покемон</h3>
                <div class="hpbar"><div class="hpfill" id="battlePlayerHpBar" style="width:100%"></div></div>
                <div id="battlePlayerHp" class="muted">HP 0/0</div>
                <div class="battle-status-badges" id="battlePlayerStatuses"></div>
              </article>
            </div>
          </div>
        </main>

        <aside class="battle-right">
          <div class="battle-log" id="battleLog"></div>
          <div class="battle-finish" id="battleFinishBox" hidden>
            <button type="button" id="battleDoneBtn">Завершить бой</button>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="dex-overlay" id="dexOverlay" aria-hidden="true">
    <div class="dex-window" role="dialog" aria-label="Декс">
      <header class="dex-head">
        <nav>
          <button type="button" class="is-active" data-dex-tab="pokemon">Покедекс</button>
          <button type="button" data-dex-tab="attacks">Атакадекс</button>
        </nav>
        <input id="dexSearchInput" placeholder="Поиск по названию или ID">
        <button type="button" id="dexCloseBtn">×</button>
      </header>
      <div class="dex-body">
        <aside class="dex-list" id="dexList"></aside>
        <main class="dex-details" id="dexDetails"></main>
      </div>
    </div>
  </section>
  <div class="inv-tooltip" id="invTooltip"></div>
  <div class="battle-poke-tooltip" id="battlePokeTooltip"></div>

  <script>
    const app = document.querySelector('.world');
    const csrf = app.dataset.csrf;
    const state = { busy: false, locationId: 0, activeNpc: null, pveButton: false };
    const inventory = { page: 1, pages: 1, items: [], selected: null };
    const battleState = { active: false, reviewing: false, moves: [] };
    const battleWindowDrag = { ready: false, dragging: false, offsetX: 0, offsetY: 0 };
    const battleHoverState = { ready: false, player: null, enemy: null };
    const battlePocket = { loaded: false, items: [] };

    function setupBattleSideTabs() {
      const left = document.querySelector('.battle-left');
      if (!left || left.dataset.tabsReady === '1') return;
      left.dataset.tabsReady = '1';

      const actions = left.querySelector('.battle-actions-col');
      const subactions = left.querySelector('.battle-subactions');
      const switchSelect = document.getElementById('battleSwitchSelect');
      const switchBtn = document.getElementById('battleSwitchBtn');

      const turn = document.createElement('div');
      turn.className = 'battle-turn-card';
      turn.innerHTML = '<span class="battle-clock" aria-hidden="true">&#9687;</span><span><b>&#1042;&#1072;&#1096; &#1093;&#1086;&#1076;</b><small>&#1042;&#1088;&#1077;&#1084;&#1103; &#1085;&#1077; &#1086;&#1075;&#1088;&#1072;&#1085;&#1080;&#1095;&#1077;&#1085;&#1086;...</small></span>';

      const content = document.createElement('div');
      content.className = 'battle-side-content';
      content.innerHTML = [
        '<div class="battle-tab-panel is-active" data-battle-panel="attacks"></div>',
        '<div class="battle-tab-panel" data-battle-panel="switch"><div class="battle-list" id="battleSwitchList"></div></div>',
        '<div class="battle-tab-panel" data-battle-panel="items"><div class="battle-list" id="battleItemsList"></div></div>',
        '<div class="battle-tab-panel" data-battle-panel="balls"><div class="battle-list" id="battleBallsList"></div></div>'
      ].join('');

      const attacksPanel = content.querySelector('[data-battle-panel="attacks"]');
      const switchPanel = content.querySelector('[data-battle-panel="switch"]');
      if (actions) attacksPanel.appendChild(actions);
      if (switchSelect) {
        switchSelect.classList.add('battle-native-switch');
        switchPanel.appendChild(switchSelect);
      }
      if (switchBtn) {
        switchBtn.classList.add('battle-hidden-action');
        switchPanel.appendChild(switchBtn);
      }

      const tabs = document.createElement('nav');
      tabs.className = 'battle-tabs';
      tabs.setAttribute('aria-label', 'Battle actions');
      tabs.innerHTML = [
        '<button type="button" class="battle-tab is-active" data-battle-tab="attacks" title="Attacks">&#9889;</button>',
        '<button type="button" class="battle-tab" data-battle-tab="switch" title="Switch">&#8644;</button>',
        '<button type="button" class="battle-tab" data-battle-tab="items" title="Inventory">&#9635;</button>',
        '<button type="button" class="battle-tab" data-battle-tab="balls" title="Pokeballs">&#9675;</button>'
      ].join('');

      left.insertBefore(turn, left.firstChild);
      left.insertBefore(content, subactions || null);
      left.appendChild(tabs);

      const review = document.createElement('div');
      review.className = 'battle-review-actions';
      review.innerHTML = [
        '<div class="battle-review-title"><b>Режим просмотра</b><small>Время не ограничено...</small></div>',
        '<button type="button" id="battleReplayBtn">&#9654; Повтор боя</button>',
        '<button type="button" id="battleCloseReviewBtn">&#10006; Закрыть окно</button>'
      ].join('');
      left.appendChild(review);
      review.querySelector('#battleReplayBtn').addEventListener('click', () => setStatus('Повтор боя будет подключен позже.'));
      review.querySelector('#battleCloseReviewBtn').addEventListener('click', acknowledgeBattleEnd);

      tabs.querySelectorAll('[data-battle-tab]').forEach(button => {
        button.addEventListener('click', () => setBattleTab(button.dataset.battleTab));
      });
      setBattleTab('attacks');
    }

    function setBattleTab(name) {
      document.querySelectorAll('[data-battle-panel]').forEach(panel => {
        panel.classList.toggle('is-active', panel.dataset.battlePanel === name);
      });
      document.querySelectorAll('[data-battle-tab]').forEach(button => {
        button.classList.toggle('is-active', button.dataset.battleTab === name);
      });
      if (name === 'items' || name === 'balls') {
        loadBattlePocket();
      }
    }

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

      if (payload.redirect) {
        window.location.href = payload.redirect;
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
      state.pveButton = !!(payload.user && payload.user.pveButton);
      renderPveButton();
      renderDebugBattleButton(location);
      if (payload.battle && payload.battle.active) {
        openBattleOverlay();
        loadBattleState();
      }
      if (payload.wildEncounter && payload.wildEncounter.message) {
        setStatus(payload.wildEncounter.message);
      }

      if (payload.chatEvent && payload.chatEvent.text) {
        const line = document.createElement('div');
        line.textContent = payload.chatEvent.text;
        document.getElementById('chatLog').appendChild(line);
      }

      // Показываем id локации в статусе, чтобы не гадать.
      setStatus('Готово • локация #' + state.locationId);
    }

    function renderDebugBattleButton(location) {
      const btn = document.getElementById('debugForceBattleBtn');
      const title = String(location && location.title ? location.title : '').toLowerCase();
      const isRoad1ByTitle = title.includes('дорога 1');
      const isRoad1ById = Number(state.locationId) === 4;
      btn.style.display = (isRoad1ByTitle || isRoad1ById) ? '' : 'none';
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
      if (!users.length) {
        const empty = document.createElement('div');
        empty.className = 'muted';
        empty.textContent = 'Онлайн игроков в этой локации нет.';
        usersList.appendChild(empty);
        return;
      }
      for (const user of users) {
        const row = document.createElement('div');
        row.className = 'user-row';
        row.innerHTML = '<span class="dot ' + (user.online ? 'on' : '') + '"></span>' +
          '<span class="user-name"></span><span class="user-tags"></span>';
        row.querySelector('.user-name').textContent = user.login;
        row.querySelector('.user-tags').textContent = (user.pveButton ? 'pve' : '');
        usersList.appendChild(row);
      }
    }

    function iconLabel(icon) {
      const labels = { cross: '+', shop: '$', mentor: 'i', quest: '?', person: '@' };
      return labels[icon] || '@';
    }

    function renderPveButton() {
      const button = document.getElementById('pveButton');
      button.textContent = state.pveButton ? 'Нападение: вкл' : 'Нападение: выкл';
    }

    function openInventory() {
      const overlay = document.getElementById('inventoryOverlay');
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      loadInventoryPage(inventory.page || 1);
    }

    function closeInventory() {
      const overlay = document.getElementById('inventoryOverlay');
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }

    function openPokemonModal() {
      const overlay = document.getElementById('pokemonOverlay');
      const frame = document.getElementById('pokemonFrame');
      if (frame.getAttribute('src') === 'about:blank') {
        frame.src = '/game/pokemon';
      }
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
    }

    function closePokemonModal() {
      const overlay = document.getElementById('pokemonOverlay');
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }

    function openBattleOverlay() {
      setupBattleSideTabs();
      setupBattleDrag();
      const overlay = document.getElementById('battleOverlay');
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      battleState.active = true;
    }

    function setupBattleDrag() {
      if (battleWindowDrag.ready) return;
      battleWindowDrag.ready = true;
      const overlay = document.getElementById('battleOverlay');
      const win = overlay.querySelector('.battle-window');
      const head = win.querySelector('.battle-head');
      const clampWindow = (left, top) => {
        const overlayRect = overlay.getBoundingClientRect();
        const winRect = win.getBoundingClientRect();
        const pad = 8;
        const maxLeft = Math.max(pad, overlayRect.width - winRect.width - pad);
        const maxTop = Math.max(pad, overlayRect.height - winRect.height - pad);
        return {
          left: Math.max(pad, Math.min(left, maxLeft)),
          top: Math.max(pad, Math.min(top, maxTop)),
        };
      };

      head.addEventListener('pointerdown', event => {
        if (event.target.closest('button')) return;
        const rect = win.getBoundingClientRect();
        battleWindowDrag.dragging = true;
        battleWindowDrag.offsetX = event.clientX - rect.left;
        battleWindowDrag.offsetY = event.clientY - rect.top;
        win.classList.add('is-dragged');
        win.style.left = rect.left + 'px';
        win.style.top = rect.top + 'px';
        head.setPointerCapture(event.pointerId);
      });
      head.addEventListener('pointermove', event => {
        if (!battleWindowDrag.dragging) return;
        const pos = clampWindow(event.clientX - battleWindowDrag.offsetX, event.clientY - battleWindowDrag.offsetY);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });
      head.addEventListener('pointerup', event => {
        battleWindowDrag.dragging = false;
        try { head.releasePointerCapture(event.pointerId); } catch (e) {}
      });
      head.addEventListener('pointercancel', () => {
        battleWindowDrag.dragging = false;
      });
      window.addEventListener('resize', () => {
        if (!win.classList.contains('is-dragged')) return;
        const rect = win.getBoundingClientRect();
        const pos = clampWindow(rect.left, rect.top);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });
    }

    function closeBattleOverlay() {
      const overlay = document.getElementById('battleOverlay');
      overlay.classList.remove('is-open');
      overlay.classList.remove('is-review');
      overlay.setAttribute('aria-hidden', 'true');
      const left = document.querySelector('.battle-left');
      if (left) left.classList.remove('is-review');
      battleState.active = false;
      battleState.reviewing = false;
    }

    function renderBattleLog(logByRound, messages) {
      const log = document.getElementById('battleLog');
      log.innerHTML = '';

      if (Array.isArray(logByRound) && logByRound.length) {
        for (const chunk of logByRound) {
          const title = document.createElement('div');
          title.className = 'battle-log-round';
          title.textContent = (Number(chunk.round) || 1) + ' Раунд';
          log.appendChild(title);

          for (const eventText of (chunk.events || [])) {
            const line = document.createElement('div');
            line.className = 'battle-log-event';
            line.innerHTML = decorateBattleLogText(String(eventText || ''));
            log.appendChild(line);
          }
        }
      }

      if (Array.isArray(messages) && messages.length) {
        for (const message of messages) {
          const line = document.createElement('div');
          line.className = 'battle-log-event is-live';
          line.innerHTML = decorateBattleLogText(String(message || ''));
          log.appendChild(line);
        }
      }
    }

    function escapeHtml(text) {
      return String(text).replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[ch]));
    }

    function decorateBattleLogText(text) {
      let html = escapeHtml(text);

      // Подсветка ключевых слов и значений урона/HP.
      html = html.replace(/КРИТ!/gi, '<span class="log-crit">КРИТ!</span>');
      html = html.replace(/промах!?/gi, '<span class="log-miss">$&</span>');
      html = html.replace(/теряет\s+(\d+)\s+HP/gi, 'теряет <span class="log-damage">$1 HP</span>');
      html = html.replace(/\((\d+\/\d+)\)/g, '(<span class="log-hp">$1</span>)');
      html = html.replace(/Победа в бою\./gi, '<span class="log-win">Победа в бою.</span>');
      html = html.replace(/Поражение в бою\./gi, '<span class="log-lose">Поражение в бою.</span>');
      html = html.replace(/Вы успешно сбежали из боя\./gi, '<span class="log-escape">Вы успешно сбежали из боя.</span>');
      html = html.replace(/Награда:/gi, '<span class="log-reward">Награда:</span>');

      // Базовая цветная подсветка имен до первого "использует"/"теряет".
      html = html.replace(/^([^\.]+?)\sиспользует\s/i, '<span class="log-actor">$1</span> использует ');
      html = html.replace(/\. ([^\.]+?) теряет /i, '. <span class="log-target">$1</span> теряет ');

      return html;
    }

    function renderBattle(payload) {
      if (!payload || payload.ok !== true) {
        let msg = payload && payload.message ? payload.message : 'Ошибка состояния боя.';
        if (payload && payload.debug) {
          msg += ' [' + JSON.stringify(payload.debug) + ']';
          renderBattleSpritesFromDebug(payload.debug);
        }
        setStatus(msg, true);
        renderBattleLog([], [msg]);
        return;
      }
      if (!payload.active && !payload.finished && !payload.result) {
        if (battleState.reviewing) {
          return;
        }
        closeBattleOverlay();
        return;
      }
      openBattleOverlay();
      battleState.reviewing = !!payload.finished;
      const left = document.querySelector('.battle-left');
      if (left) left.classList.toggle('is-review', battleState.reviewing);
      document.getElementById('battleOverlay').classList.toggle('is-review', battleState.reviewing);

      const battle = payload.battle || payload;
      const player = battle.player || { name: 'Ваш покемон', level: 1, hp: 0, hpMax: 1, baseNum: 0 };
      const enemy = battle.enemy || { name: 'Дикий покемон', level: 1, hp: 0, hpMax: 1, baseNum: 0 };

      document.getElementById('battleTitle').textContent = 'PvE бой #' + battle.id;
      document.getElementById('battleRound').textContent = 'Раунд ' + (battle.round || 1);
      document.getElementById('battlePlayerName').textContent = player.name + ' Lv.' + player.level;
      document.getElementById('battleEnemyName').textContent = enemy.name + ' Lv.' + enemy.level;
      document.getElementById('battlePlayerHp').textContent = 'HP ' + player.hp + '/' + player.hpMax;
      document.getElementById('battleEnemyHp').textContent = 'HP ' + enemy.hp + '/' + enemy.hpMax;
      renderBattleStatuses('battlePlayerStatuses', player.statuses || [], player.majorStatuses || []);
      renderBattleStatuses('battleEnemyStatuses', enemy.statuses || [], enemy.majorStatuses || []);
      battleHoverState.player = player;
      battleHoverState.enemy = enemy;
      setupBattlePokemonHover();
      renderBattleSprites(player, enemy);

      const playerHpPercent = Math.max(0, Math.min(100, (player.hp / Math.max(1, player.hpMax)) * 100));
      const enemyHpPercent = Math.max(0, Math.min(100, (enemy.hp / Math.max(1, enemy.hpMax)) * 100));
      document.getElementById('battlePlayerHpBar').style.width = playerHpPercent + '%';
      document.getElementById('battleEnemyHpBar').style.width = enemyHpPercent + '%';

      battleState.moves = Array.isArray(battle.moves) ? battle.moves : [];
      for (let i = 0; i < 4; i++) {
        const button = document.getElementById('battleMove' + (i + 1));
        const move = battleState.moves[i];
        if (move) {
          button.disabled = false;
          const power = Number(move.power || 0);
          const acc = Number(move.accuracy || 0);
          const pp = Number(move.pp || 0);
          const ppMax = Number(move.ppMax || 0);
          button.disabled = ppMax > 0 && pp <= 0;
          button.innerHTML = '<span class="t"></span><span class="pp"></span><span class="s"></span>';
          button.querySelector('.t').textContent = move.name;
          button.querySelector('.pp').textContent = ppMax > 0 ? (pp + '/' + ppMax) : '';
          button.querySelector('.s').textContent = 'Сила: ' + power + ' • Точность: ' + acc + '%';
          button.dataset.moveId = String(move.id);
        } else {
          button.disabled = true;
          button.textContent = 'Нет атаки';
          button.dataset.moveId = '';
        }
      }

      const switchSelect = document.getElementById('battleSwitchSelect');
      switchSelect.innerHTML = '';
      const options = Array.isArray(battle.switchOptions) ? battle.switchOptions : [];
      for (const option of options) {
        const row = document.createElement('option');
        row.value = String(option.id);
        row.textContent = option.name + ' (' + option.hp + '/' + option.hpMax + ')';
        row.disabled = !!option.disabled;
        switchSelect.appendChild(row);
      }
      renderBattleSwitchList(options);

      const extraMessages = [...(payload.messages || [])];
      if (payload.rewards && Number(payload.rewards.coins || 0) > 0) {
        extraMessages.push('Награда: ' + Number(payload.rewards.coins) + ' монет, ' + Number(payload.rewards.exp || 0) + ' опыта.');
      }
      renderBattleLog(battle.logByRound || [], extraMessages);

      if (payload.finished) {
        const finishLine = payload.result === 'win'
          ? 'Победа в бою.'
          : (payload.result === 'caught' ? 'Покемон пойман.' : (payload.result === 'escape' ? 'Вы покинули бой.' : 'Поражение в бою.'));
        renderBattleLog(battle.logByRound || [], [...extraMessages, finishLine]);
      }

      const doneBox = document.getElementById('battleFinishBox');
      doneBox.hidden = !payload.finished;
      setBattleControlsDisabled(!!payload.finished);
    }

    function setBattleControlsDisabled(disabled) {
      for (let i = 1; i <= 4; i++) {
        document.getElementById('battleMove' + i).disabled = disabled || document.getElementById('battleMove' + i).disabled;
      }
      document.getElementById('battleSwitchSelect').disabled = disabled;
      document.getElementById('battleSwitchBtn').disabled = disabled;
      document.getElementById('battleEscapeBtn').disabled = disabled;
    }

    function renderBattleStatuses(targetId, statuses, majorStatuses = []) {
      const box = document.getElementById(targetId);
      if (!box) return;
      box.innerHTML = '';
      if (Array.isArray(majorStatuses)) {
        for (const status of majorStatuses.slice(0, 3)) {
          const badge = document.createElement('span');
          badge.className = 'major';
          badge.textContent = String(status.name || ('Статус #' + status.id));
          box.appendChild(badge);
        }
      }
      if (!Array.isArray(statuses)) return;
      for (const status of statuses.slice(0, 5)) {
        const badge = document.createElement('span');
        badge.className = status.kind === 'minus' ? 'minus' : 'plus';
        badge.textContent = String(status.label || '') + ' ' + String(status.sign || '') + String(status.value || '');
        box.appendChild(badge);
      }
    }

    function setupBattlePokemonHover() {
      if (battleHoverState.ready) return;
      battleHoverState.ready = true;
      const targets = [
        ['player', document.getElementById('battlePlayerSprite'), document.querySelector('.fighter-player')],
        ['enemy', document.getElementById('battleEnemySprite'), document.querySelector('.fighter-enemy')],
      ];
      for (const [kind, ...nodes] of targets) {
        for (const node of nodes) {
          if (!node) continue;
          node.addEventListener('mouseenter', event => showBattlePokemonTooltip(event, kind));
          node.addEventListener('mousemove', moveBattlePokemonTooltip);
          node.addEventListener('mouseleave', hideBattlePokemonTooltip);
        }
      }
    }

    function showBattlePokemonTooltip(event, kind) {
      const pokemon = battleHoverState[kind];
      if (!pokemon) return;
      const tooltip = document.getElementById('battlePokeTooltip');
      const hpMax = Math.max(1, Number(pokemon.hpMax || 1));
      const hp = Math.max(0, Number(pokemon.hp || 0));
      const stats = pokemon.stats || {};
      const moves = Array.isArray(pokemon.movesPreview) ? pokemon.movesPreview.slice(0, 4) : [];
      const statuses = Array.isArray(pokemon.statuses) ? pokemon.statuses : [];
      const majorStatuses = Array.isArray(pokemon.majorStatuses) ? pokemon.majorStatuses : [];
      const typeLabel = String(pokemon.tips || 'normal').toLowerCase().includes('shine') ? 'SHINY' : 'NORMAL';
      const statText = statuses.length
        ? statuses.map(s => escapeHtml(String(s.label || '') + ' ' + String(s.sign || '') + String(s.value || ''))).join(', ')
        : 'нет';
      const majorText = majorStatuses.length
        ? majorStatuses.map(s => escapeHtml(String(s.name || 'Статус'))).join(', ')
        : 'нет';
      const movesText = moves.length
        ? moves.map(m => '<span class="tip-move">• ' + escapeHtml(m.name || 'Атака') + '</span> (' + Number(m.pp || 0) + '/' + Number(m.ppMax || 0) + ' | ' + Number(m.power || 0) + '/' + Number(m.accuracy || 0) + ')').join('<br>')
        : 'нет данных';
      tooltip.innerHTML = [
        '<h4>' + escapeHtml(pokemon.name || 'Pokemon') + ' <small>Lv.' + Number(pokemon.level || 1) + '</small></h4>',
        '<span class="tip-type">' + typeLabel + '</span>',
        '<div>HP: ' + Math.round((hp / hpMax) * 100) + '% (' + hp + '/' + hpMax + ')</div>',
        '<div>Статусы: ' + majorText + '</div>',
        '<div>Модификаторы: ' + statText + '</div>',
        '<div>Раскрытые атаки:<br>' + movesText + '</div>',
        '<table><tr><th>Атака</th><th>Защита</th><th>С. Атака</th><th>С. Защита</th><th>Скорость</th></tr>',
        '<tr><td>' + Number(stats.atk || 0) + '</td><td>' + Number(stats.def || 0) + '</td><td>' + Number(stats.satk || 0) + '</td><td>' + Number(stats.sdef || 0) + '</td><td>' + Number(stats.speed || 0) + '</td></tr></table>',
      ].join('');
      tooltip.style.display = 'block';
      moveBattlePokemonTooltip(event);
    }

    function moveBattlePokemonTooltip(event) {
      const tooltip = document.getElementById('battlePokeTooltip');
      if (tooltip.style.display !== 'block') return;
      const pad = 12;
      let left = event.clientX + 14;
      let top = event.clientY + 14;
      const rect = tooltip.getBoundingClientRect();
      if (left + rect.width + pad > window.innerWidth) {
        left = event.clientX - rect.width - 14;
      }
      if (top + rect.height + pad > window.innerHeight) {
        top = event.clientY - rect.height - 14;
      }
      tooltip.style.left = Math.max(pad, left) + 'px';
      tooltip.style.top = Math.max(pad, top) + 'px';
    }

    function hideBattlePokemonTooltip() {
      document.getElementById('battlePokeTooltip').style.display = 'none';
    }

    function renderBattleSwitchList(options) {
      const list = document.getElementById('battleSwitchList');
      if (!list) return;
      list.innerHTML = '';
      if (!Array.isArray(options) || !options.length) {
        list.innerHTML = '<div class="battle-empty">&#1053;&#1077;&#1090; &#1087;&#1086;&#1082;&#1077;&#1084;&#1086;&#1085;&#1086;&#1074; &#1076;&#1083;&#1103; &#1079;&#1072;&#1084;&#1077;&#1085;&#1099;.</div>';
        return;
      }
      for (const option of options) {
        const hp = Number(option.hp || 0);
        const hpMax = Math.max(1, Number(option.hpMax || 1));
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'battle-switch-card';
        button.disabled = !!option.disabled;
        button.innerHTML = '<span class="mini-poke"></span><span class="battle-row-main"><b></b><span class="mini-bars"><i class="hp"></i><i class="xp"></i></span></span>';
        button.querySelector('b').textContent = option.name || 'Pokemon';
        button.querySelector('.mini-poke').textContent = '●';
        button.querySelector('.hp').style.width = Math.max(0, Math.min(100, (hp / hpMax) * 100)) + '%';
        button.querySelector('.xp').style.width = '22%';
        button.addEventListener('click', () => {
          if (button.disabled) return;
          document.getElementById('battleSwitchSelect').value = String(option.id);
          battleAction('switch', { pokemon_id: option.id });
        });
        list.appendChild(button);
      }
    }

    async function loadBattlePocket() {
      if (!battlePocket.loaded) {
        const itemsList = document.getElementById('battleItemsList');
        const ballsList = document.getElementById('battleBallsList');
        if (itemsList) itemsList.innerHTML = '<div class="battle-empty">Loading...</div>';
        if (ballsList) ballsList.innerHTML = '<div class="battle-empty">Loading...</div>';
        try {
          const response = await fetch('/api/inventory/battle', { credentials: 'same-origin' });
          const payload = await response.json();
          battlePocket.items = payload && payload.ok === true && Array.isArray(payload.items) ? payload.items : [];
          battlePocket.loaded = true;
        } catch (e) {
          battlePocket.items = [];
          battlePocket.loaded = true;
        }
      }
      renderBattlePocketLists();
    }

    function renderBattlePocketLists() {
      const all = battlePocket.items || [];
      const balls = all.filter(item => isBattleBall(item));
      const items = all.filter(item => !isBattleBall(item) && Number(item.item_id || 0) !== 1 && Number(item.battleuse || 0) === 1);
      renderBattlePocketList('battleItemsList', items, '&#1053;&#1077;&#1090; &#1073;&#1086;&#1077;&#1074;&#1099;&#1093; &#1087;&#1088;&#1077;&#1076;&#1084;&#1077;&#1090;&#1086;&#1074;.');
      renderBattlePocketList('battleBallsList', balls, '&#1053;&#1077;&#1090; &#1087;&#1086;&#1082;&#1077;&#1073;&#1086;&#1083;&#1086;&#1074;.');
    }

    function renderBattlePocketList(id, items, emptyText) {
      const list = document.getElementById(id);
      if (!list) return;
      list.innerHTML = '';
      if (!items.length) {
        list.innerHTML = '<div class="battle-empty">' + emptyText + '</div>';
        return;
      }
      for (const item of items.slice(0, 12)) {
        const row = document.createElement('button');
        row.type = 'button';
        row.className = 'battle-item-row';
        row.innerHTML = '<img alt=""><span><b></b><small></small></span><i aria-hidden="true">☆</i>';
        row.querySelector('img').src = '/img/items/' + Number(item.item_id || 0) + '.png';
        row.querySelector('b').textContent = item.name || item.tittle || ('Item #' + Number(item.item_id || 0));
        row.querySelector('small').innerHTML = '&#1050;&#1086;&#1083;&#1080;&#1095;&#1077;&#1089;&#1090;&#1074;&#1086;: ' + Number(item.count || 0) + ' &#1096;&#1090;.';
        row.addEventListener('click', () => {
          const action = id === 'battleBallsList' ? 'ball' : 'item';
          battleAction(action, { item_user_id: item.id });
        });
        list.appendChild(row);
      }
    }

    function isBattleBall(item) {
      const id = Number(item.item_id || 0);
      const name = String((item.name || '') + ' ' + (item.tittle || '')).toLowerCase();
      return id === 3
        || name.includes('ball')
        || name.includes('покеб')
        || name.includes('ультрабол')
        || name.includes('мастербол')
        || name.includes('шайнибол')
        || name.includes('ultra')
        || name.includes('master');
    }

    function pad3(num) {
      return String(Math.max(0, Number(num || 0))).padStart(3, '0');
    }

    function setSpriteWithFallback(imgEl, candidates) {
      const urls = candidates.filter(Boolean);
      let idx = 0;
      const tryNext = () => {
        if (idx >= urls.length) {
          imgEl.src = '/img/blank.gif';
          return;
        }
        imgEl.src = urls[idx++];
      };
      imgEl.onerror = tryNext;
      tryNext();
    }

    function spriteNumberCandidates(base) {
      const num = Number(base || 0);
      const plain = String(num);
      const padded = pad3(num);
      return plain === padded ? [padded] : [padded, plain];
    }

    function spriteCandidates(folder, base, extensions = ['gif']) {
      const urls = [];
      for (const name of spriteNumberCandidates(base)) {
        for (const ext of extensions) {
          urls.push('/pok/' + folder + '/' + name + '.' + ext);
        }
      }
      return urls;
    }

    function isShinyBattlePokemon(pokemon) {
      const joined = [pokemon && pokemon.name, pokemon && pokemon.tips, pokemon && pokemon.status]
        .map(v => String(v || '').toLowerCase())
        .join(' ');
      return joined.includes('shiny') || joined.includes('pokesshiny') || joined.includes('shine');
    }

    function spriteCandidatesAny(folder, base, extensions = ['gif', 'png', 'jpg']) {
      return spriteCandidates(folder, base, extensions);
    }

    function renderBattleSprites(player, enemy) {
      const playerBase = Number(player.baseNum || 0);
      const enemyBase = Number(enemy.baseNum || 0);
      const playerImg = document.getElementById('battlePlayerSpriteImg');
      const enemyImg = document.getElementById('battleEnemySpriteImg');

      playerImg.alt = player.name || 'Ваш покемон';
      enemyImg.alt = enemy.name || 'Дикий покемон';

      const playerIsShiny = isShinyBattlePokemon(player);
      const enemyIsShiny = isShinyBattlePokemon(enemy);

      // Back sprite for user's pokemon. Try shiny back first when name/tips says Shiny,
      // then normal back, then front/anim fallbacks so the pokemon never disappears.
      const playerCandidates = [];
      if (playerIsShiny) {
        playerCandidates.push(...spriteCandidatesAny('sback', playerBase));
        playerCandidates.push(...spriteCandidatesAny('Sback', playerBase));
      }
      playerCandidates.push(...spriteCandidatesAny('back', playerBase));
      if (playerIsShiny) {
        playerCandidates.push(...spriteCandidatesAny('shiny', playerBase));
        playerCandidates.push(...spriteCandidatesAny('shine', playerBase));
      }
      playerCandidates.push(...spriteCandidatesAny('anim', playerBase));
      playerCandidates.push(...spriteCandidatesAny('pok', playerBase));
      playerCandidates.push(...spriteCandidatesAny('normal', playerBase));

      // Front sprite for wild pokemon. Legacy project often stores battle sprites in /pok/anim/.
      const enemyCandidates = [];
      if (enemyIsShiny) {
        enemyCandidates.push(...spriteCandidatesAny('shiny', enemyBase));
        enemyCandidates.push(...spriteCandidatesAny('shine', enemyBase));
      }
      enemyCandidates.push(...spriteCandidatesAny('anim', enemyBase));
      enemyCandidates.push(...spriteCandidatesAny('pok', enemyBase));
      enemyCandidates.push(...spriteCandidatesAny('normal', enemyBase));

      setSpriteWithFallback(playerImg, playerCandidates);
      setSpriteWithFallback(enemyImg, enemyCandidates);
    }

    function renderBattleSpritesFromDebug(debug) {
      const playerTag = String(debug && debug.poke_1 ? debug.poke_1 : '');
      const enemyTag = String(debug && debug.poke_2 ? debug.poke_2 : '');
      const playerId = Number((playerTag.match(/_(\d+)$/) || [])[1] || 0);
      const enemyId = Number((enemyTag.match(/_(\d+)$/) || [])[1] || debug && debug.user_2 || 0);

      const playerImg = document.getElementById('battlePlayerSpriteImg');
      const enemyImg = document.getElementById('battleEnemySpriteImg');

      setSpriteWithFallback(playerImg, [
        '/pok/sback/' + pad3(playerId) + '.gif',
        '/pok/sback/' + playerId + '.gif',
        '/pok/back/' + pad3(playerId) + '.gif',
        '/pok/back/' + playerId + '.gif',
        '/pok/anim/' + pad3(playerId) + '.gif',
        '/pok/anim/' + playerId + '.gif',
        '/pok/back/001.gif',
      ]);
      setSpriteWithFallback(enemyImg, [
        '/pok/anim/' + pad3(enemyId) + '.gif',
        '/pok/anim/' + enemyId + '.gif',
        '/pok/pok/' + pad3(enemyId) + '.gif',
        '/pok/shiny/' + pad3(enemyId) + '.gif',
        '/pok/anim/001.gif',
      ]);
    }

    async function loadBattleState() {
      try {
        const response = await fetch('/api/battle/pve/state', { credentials: 'same-origin' });
        const payload = await response.json();
        renderBattle(payload);
      } catch (error) {
        setStatus('Не удалось обновить боевое состояние.', true);
      }
    }

    async function battleAction(action, extra = {}) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('action', action);
      Object.entries(extra).forEach(([key, value]) => body.set(key, String(value)));

      try {
        const response = await fetch('/api/battle/pve/action', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus(payload && payload.message ? payload.message : 'Боевое действие отклонено.', true);
        }
        if (action === 'item' || action === 'ball') {
          battlePocket.loaded = false;
        }
        renderBattle(payload);
      } catch (error) {
        setStatus('Боевое действие не выполнено.', true);
      }
    }

    async function acknowledgeBattleEnd() {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      try {
        await fetch('/api/battle/pve/ack-end', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
      } catch (e) {
        // ignore
      }
      closeBattleOverlay();
      document.getElementById('battleFinishBox').hidden = true;
      loadState();
    }

    function renderInventoryGrid(sourceItems = null) {
      const grid = document.getElementById('invGrid');
      const items = (sourceItems || inventory.items).slice(0, 60);
      grid.innerHTML = '';

      for (let i = 0; i < 60; i++) {
        const item = items[i] || null;
        const slot = document.createElement('button');
        slot.type = 'button';
        slot.className = 'inv-slot' + (item ? '' : ' empty');

        if (item) {
          slot.innerHTML = '<img alt=""><span class="cnt"></span><span class="item-id"></span>';
          slot.querySelector('img').src = '/img/items/' + Number(item.item_id || 0) + '.png';
          slot.querySelector('.cnt').textContent = Number(item.count || 0).toLocaleString('ru-RU');
          slot.querySelector('.item-id').textContent = '#' + Number(item.item_id || 0);
          if (inventory.selected && Number(inventory.selected.id) === Number(item.id)) {
            slot.classList.add('selected');
          }
          slot.addEventListener('click', () => {
            inventory.selected = item;
            renderInventoryGrid();
          });
          slot.addEventListener('mouseenter', (event) => showItemTooltip(event, item));
          slot.addEventListener('mousemove', moveItemTooltip);
          slot.addEventListener('mouseleave', hideItemTooltip);
        } else {
          slot.innerHTML = '<img alt="" src="/img/blank.gif">';
          slot.disabled = true;
        }
        grid.appendChild(slot);
      }

      document.getElementById('invSlotsCount').textContent = String(items.length);
      document.getElementById('invPageInfo').textContent = inventory.page + '/' + inventory.pages;
    }

    async function loadInventoryPage(page = 1) {
      try {
        const response = await fetch('/api/inventory/page?page=' + encodeURIComponent(page), { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          return;
        }
        inventory.page = Number(payload.page || 1);
        inventory.pages = Number(payload.pages || 1);
        inventory.items = Array.isArray(payload.items) ? payload.items : [];
        inventory.selected = null;
        renderInventoryGrid();
      } catch (e) {
        // ignore network error for now
      }
    }

    function showItemTooltip(event, item) {
      const tooltip = document.getElementById('invTooltip');
      const name = String(item.name || 'Без названия');
      const count = Number(item.count || 0).toLocaleString('ru-RU');
      const id = Number(item.item_id || 0);
      tooltip.innerHTML = '<strong>' + name + '</strong><br>ID: ' + id + ' / Кол-во: ' + count;
      tooltip.style.display = 'block';
      moveItemTooltip(event);
    }

    function moveItemTooltip(event) {
      const tooltip = document.getElementById('invTooltip');
      if (tooltip.style.display !== 'block') return;
      tooltip.style.left = (event.clientX + 14) + 'px';
      tooltip.style.top = (event.clientY + 14) + 'px';
    }

    function hideItemTooltip() {
      document.getElementById('invTooltip').style.display = 'none';
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

    async function togglePveButton() {
      try {
        setStatus('Переключаем нападение...');
        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('mode', state.pveButton ? 'off' : 'on');
        const response = await fetch('/api/game/pve-mode', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        render(await response.json());
      } catch (error) {
        setStatus('Не удалось переключить режим нападения.', true);
      }
    }

    document.getElementById('pveButton').addEventListener('click', togglePveButton);
    document.getElementById('debugForceBattleBtn').addEventListener('click', async () => {
      try {
        setStatus('DEBUG: запускаем бой...');
        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        const response = await fetch('/api/battle/pve/force', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        render(await response.json());
      } catch (e) {
        setStatus('DEBUG: не удалось запустить бой.', true);
      }
    });

    document.getElementById('inventoryLink').addEventListener('click', event => {
      event.preventDefault();
      openInventory();
    });
    document.getElementById('pokemonLink').addEventListener('click', event => {
      event.preventDefault();
      openPokemonModal();
    });
    document.getElementById('pokemonCloseBtn').addEventListener('click', closePokemonModal);
    document.getElementById('pokemonOverlay').addEventListener('click', event => {
      if (event.target.id === 'pokemonOverlay') {
        closePokemonModal();
      }
    });
    document.getElementById('invCloseBtn').addEventListener('click', closeInventory);
    document.getElementById('invRefreshBtn').addEventListener('click', () => loadInventoryPage(inventory.page));
    document.getElementById('invPrevBtn').addEventListener('click', () => loadInventoryPage(Math.max(1, inventory.page - 1)));
    document.getElementById('invNextBtn').addEventListener('click', () => loadInventoryPage(Math.min(inventory.pages, inventory.page + 1)));
    document.getElementById('inventoryOverlay').addEventListener('click', event => {
      if (event.target.id === 'inventoryOverlay') {
        closeInventory();
      }
    });
    document.getElementById('inventoryOverlay').addEventListener('mouseleave', hideItemTooltip);
    document.getElementById('invSearchInput').addEventListener('input', event => {
      const query = String(event.target.value || '').trim().toLowerCase();
      if (!query) {
        renderInventoryGrid();
        return;
      }
      const filtered = inventory.items.filter(item => String(item.name || '').toLowerCase().includes(query));
      renderInventoryGrid(filtered);
    });

    for (let i = 1; i <= 4; i++) {
      document.getElementById('battleMove' + i).addEventListener('click', event => {
        const moveId = Number(event.currentTarget.dataset.moveId || 0);
        if (moveId > 0) {
          battleAction('attack', { move_id: moveId });
        }
      });
    }
    document.getElementById('battleSwitchBtn').addEventListener('click', () => {
      const pokemonId = Number(document.getElementById('battleSwitchSelect').value || 0);
      if (pokemonId > 0) {
        battleAction('switch', { pokemon_id: pokemonId });
      }
    });
    document.getElementById('battleEscapeBtn').addEventListener('click', () => {
      battleAction('escape');
    });
    document.getElementById('battleDoneBtn').addEventListener('click', () => {
      acknowledgeBattleEnd();
    });
    document.getElementById('battleReviewClose').addEventListener('click', () => {
      acknowledgeBattleEnd();
    });

    loadState();
    setInterval(loadState, 5000);
    setInterval(() => {
      if (battleState.active && !battleState.reviewing) {
        loadBattleState();
      }
    }, 2000);
  </script>
  <script src="/js/chat.js"></script>
  <script src="/js/dex-overlay.js"></script>

</body>
</html>
