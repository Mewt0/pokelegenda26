(function () {
  'use strict';

  const menu = document.createElement('div');
  menu.className = 'player-context-menu';
  menu.hidden = true;
  document.body.appendChild(menu);

  const toastStack = document.createElement('div');
  toastStack.className = 'game-toast-stack';
  document.body.appendChild(toastStack);

  const pvpModal = document.createElement('div');
  pvpModal.className = 'pvp-pokemon-modal';
  pvpModal.hidden = true;
  document.body.appendChild(pvpModal);

  let activeRow = null;
  let activeLogin = '';
  let activePlayerId = 0;
  let activeFriendStatus = 'loading';
  let activePvpStatus = 'loading';
  let activePvpPermission = null;
  let knownIncomingRequests = new Set(loadSeenRequests());
  let knownIncomingPvpRequests = new Set(loadSeenPvpRequests());

  const app = document.querySelector('.world');
  const csrf = app ? app.dataset.csrf : '';
  const currentUserId = Number(app && app.dataset.userId || 0);

  const text = {
    localPlayer: 'игрок на локации',
    card: 'Тренеркарта',
    dialog: 'Открыть диалог',
    private: 'Написать в ЛС',
    battle: 'Вызвать на бой',
    forceBattle: 'Напасть по ордеру',
    acceptBattle: 'Принять бой',
    battleSent: 'Вызов отправлен',
    battleActive: 'Игрок в бою',
    battleRestricted: 'Карма не позволяет',
    trade: 'Предложить обмен',
    friend: 'Добавить в друзья',
    acceptFriend: 'Принять заявку',
    requestSent: 'Заявка отправлена',
    removeFriend: 'Удалить из друзей',
    ignore: 'Добавить в чёрный список',
    mail: 'Написать на почту',
  };

  function loadSeenRequests() {
    try {
      return JSON.parse(localStorage.getItem('pokemon_friend_seen_requests') || '[]')
        .map(id => Number(id))
        .filter(Boolean);
    } catch (error) {
      return [];
    }
  }

  function saveSeenRequests() {
    try {
      localStorage.setItem('pokemon_friend_seen_requests', JSON.stringify([...knownIncomingRequests].slice(-200)));
    } catch (error) {
      // Local storage can be disabled; notifications still work for this session.
    }
  }

  function loadSeenPvpRequests() {
    try {
      return JSON.parse(localStorage.getItem('pokemon_pvp_seen_requests') || '[]')
        .map(id => Number(id))
        .filter(Boolean);
    } catch (error) {
      return [];
    }
  }

  function saveSeenPvpRequests() {
    try {
      localStorage.setItem('pokemon_pvp_seen_requests', JSON.stringify([...knownIncomingPvpRequests].slice(-200)));
    } catch (error) {
      // Local storage can be disabled; notifications still work for this session.
    }
  }

  function notify(message, variant = 'info') {
    if (!message) return;

    const toast = document.createElement('div');
    toast.className = 'game-toast game-toast-' + variant;
    toast.textContent = message;
    toastStack.appendChild(toast);

    window.setTimeout(() => toast.classList.add('is-visible'), 10);
    window.setTimeout(() => {
      toast.classList.remove('is-visible');
      window.setTimeout(() => toast.remove(), 220);
    }, 4200);
  }

  function pad3(value) {
    return String(Math.max(0, Number(value || 0))).padStart(3, '0');
  }

  function pokemonSprite(pokemon) {
    const base = Math.max(0, Number(pokemon && pokemon.baseNum || 0));
    return base > 0 ? '/Pok/normal/' + base + '.png' : '/public/img/ui/menu-pokemon.png';
  }

  function hpPercent(pokemon) {
    return Math.max(0, Math.min(100, Number(pokemon && pokemon.hp || 0) / Math.max(1, Number(pokemon && pokemon.hpMax || 1)) * 100));
  }

  function closeMenu() {
    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = null;
    activeLogin = '';
    activePlayerId = 0;
    activeFriendStatus = 'loading';
    activePvpStatus = 'loading';
    menu.hidden = true;
    menu.innerHTML = '';
    menu.dataset.playerId = '';
    activePvpPermission = null;
  }

  function escapeAttr(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function menuButton(action, icon, label, disabled = false, title = '') {
    const titleAttr = title ? ' title="' + escapeAttr(title) + '"' : '';
    return '<button type="button" data-player-action="' + action + '"' + titleAttr + (disabled ? ' disabled' : '') + '><span class="pcm-icon">' + icon + '</span><span>' + label + '</span></button>';
  }

  function friendButtonForStatus(status) {
    if (status === 'auth') return menuButton('friend-pending', '&#9888;', 'Нужно войти', true);
    if (status === 'self') return '';
    if (status === 'incoming') return menuButton('friend-accept', '&#10003;', text.acceptFriend);
    if (status === 'friends') return menuButton('friend-remove', '&#8722;', text.removeFriend);
    if (status === 'outgoing') return menuButton('friend-pending', '&#8987;', text.requestSent, true);
    if (status === 'loading') return menuButton('friend-pending', '&#8987;', 'Проверяем...', true);
    return menuButton('friend', '&#9793;', text.friend);
  }

  function battleButtonForStatus(status) {
    if (status === 'auth') return menuButton('battle-pending', '&#9888;', 'Нужно войти', true);
    if (status === 'self') return '';
    if (status === 'incoming') return menuButton('battle-accept', '&#9876;', text.acceptBattle);
    if (status === 'outgoing') return menuButton('battle-pending', '&#8987;', text.battleSent, true);
    if (status === 'active') return menuButton('battle-pending', '&#8987;', text.battleActive, true);
    if (status === 'restricted') return menuButton('battle-pending', '&#9888;', text.battleRestricted, true);
    if (status === 'loading') return menuButton('battle-pending', '&#8987;', 'Проверяем бой...', true);
    return menuButton('battle', '&#9876;', text.battle);
  }

  function forceBattleButtonForStatus(status, permission) {
    if (status === 'auth') return menuButton('battle-force-pending', '&#9888;', 'Нужно войти', true);
    if (status === 'self') return '';
    if (status === 'active') return menuButton('battle-force-pending', '&#8987;', text.battleActive, true);
    if (status === 'loading') return menuButton('battle-force-pending', '&#8987;', 'Проверяем ордер...', true);

    const message = permission && permission.message ? String(permission.message) : '';
    if (permission && permission.allowed === true) {
      const label = permission.requiresWarrant ? text.forceBattle : 'Напасть принудительно';
      return menuButton('battle-force', '&#9876;', label, false, message);
    }

    const label = message.includes('нужен ордер') ? 'Нужен ордер' : 'Ордер недоступен';
    return menuButton('battle-force-unavailable', '&#9888;', label, true, message || 'Принудительное нападение сейчас недоступно.');
  }

  function renderMenu() {
    if (!activeRow || !activeLogin || activePlayerId <= 0 || currentUserId <= 0) return;

    const initial = activeLogin.trim().charAt(0).toUpperCase() || '?';
    const isSelf = activePlayerId === currentUserId;
    menu.innerHTML = [
      '<div class="pcm-head">',
        '<div class="pcm-avatar">' + initial + '</div>',
        '<div><b></b><small>' + text.localPlayer + '</small></div>',
      '</div>',
      menuButton('card', '&#9817;', text.card),
      isSelf ? '' : menuButton('dialog', '&#9743;', text.dialog),
      isSelf ? '' : menuButton('private', '&#9998;', text.private),
      isSelf ? '' : '<hr>',
      isSelf ? '' : battleButtonForStatus(activePvpStatus),
      isSelf ? '' : forceBattleButtonForStatus(activePvpStatus, activePvpPermission),
      isSelf ? '' : menuButton('trade', '&#8644;', text.trade),
      isSelf ? '' : '<hr>',
      isSelf ? '' : friendButtonForStatus(activeFriendStatus),
      isSelf ? '' : menuButton('ignore', '&#8856;', text.ignore),
      isSelf ? '' : menuButton('mail', '&#9993;', text.mail),
    ].join('');
    menu.querySelector('b').textContent = activeLogin;
  }

  function positionMenu(row) {
    const rect = row.getBoundingClientRect();
    const width = 260;
    const left = Math.min(window.innerWidth - width - 10, Math.max(10, rect.left - 8));
    let top = rect.bottom + 8;
    menu.style.width = width + 'px';
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    menu.hidden = false;

    const menuRect = menu.getBoundingClientRect();
    if (menuRect.bottom > window.innerHeight - 10) {
      top = Math.max(10, rect.top - menuRect.height - 8);
      menu.style.top = top + 'px';
    }
  }

  async function loadFriendStatus(playerId) {
    if (!playerId) return 'none';

    try {
      const response = await fetch('/api/friends/status?user_id=' + encodeURIComponent(String(playerId)), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      if (response.status === 401 || response.status === 403) {
        return 'auth';
      }
      const data = await response.json();
      return data.ok ? String(data.status || 'none') : 'none';
    } catch (error) {
      console.error('Friend status failed:', error);
      return 'none';
    }
  }

  async function loadPvpInfo(playerId) {
    if (!playerId) return { status: 'none', permission: null };

    try {
      const response = await fetch('/api/battle/pvp/status?user_id=' + encodeURIComponent(String(playerId)), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      if (response.status === 401 || response.status === 403) {
        return { status: 'auth', permission: null };
      }
      const data = await response.json();
      return data.ok
        ? { status: String(data.status || 'none'), permission: data.permission || null }
        : { status: 'none', permission: null };
    } catch (error) {
      console.error('PvP status failed:', error);
      return { status: 'none', permission: null };
    }
  }

  async function openMenu(row) {
    const login = row.dataset.playerLogin || row.querySelector('.user-name')?.textContent || '';
    const playerId = Number(row.dataset.playerId || 0);
    if (currentUserId <= 0) {
      notify('Нужно войти в игру, чтобы открыть меню игрока.', 'error');
      return;
    }
    if (!login || playerId <= 0) {
      notify('Не удалось определить игрока. Обнови страницу.', 'error');
      return;
    }

    if (activeRow === row && !menu.hidden) {
      closeMenu();
      return;
    }

    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = row;
    activeLogin = login;
    activePlayerId = playerId;
    activeFriendStatus = activePlayerId === currentUserId ? 'self' : 'loading';
    activePvpStatus = activePlayerId === currentUserId ? 'self' : 'loading';
    activePvpPermission = null;
    menu.dataset.playerId = String(activePlayerId || '');
    row.classList.add('is-menu-open');

    renderMenu();
    positionMenu(row);

    if (activePlayerId === currentUserId) {
      return;
    }

    const [status, pvpInfo] = await Promise.all([
      loadFriendStatus(activePlayerId),
      loadPvpInfo(activePlayerId),
    ]);
    if (activeRow !== row || menu.hidden) return;
    activeFriendStatus = status;
    activePvpStatus = pvpInfo.status;
    activePvpPermission = pvpInfo.permission;
    renderMenu();
    positionMenu(row);
  }

  function actionUrl(action, login) {
    const encoded = encodeURIComponent(login);
    const id = Number(menu.dataset.playerId || 0);
    if (action === 'mail') return '/game/messages?mail_to=' + encoded;
    if (action === 'card') return id > 0 ? '/game/profile?id=' + id : '/game/profile?user=' + encoded;
    return '';
  }

  async function postFriendAction(endpoint, id, successFallback) {
    if (!id) {
      notify('Игрок не выбран.', 'error');
      return null;
    }

    try {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('user_id', String(id));

      const response = await fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        },
        body
      });
      const data = await response.json();
      notify(data.message || (data.ok ? successFallback : 'Действие не выполнено.'), data.ok ? 'success' : 'error');
      return data;
    } catch (error) {
      console.error('Friend action failed:', error);
      notify('Ошибка сервера при работе с друзьями.', 'error');
      return null;
    }
  }

  async function requestFriend(id) {
    const data = await postFriendAction('/api/friends/request', id, 'Заявка в друзья отправлена.');
    return data;
  }

  async function acceptFriend(id) {
    return postFriendAction('/api/friends/accept', id, 'Игрок добавлен в друзья.');
  }

  async function removeFriend(id) {
    return postFriendAction('/api/friends/remove', id, 'Игрок удалён из друзей.');
  }

  async function requestBattle(id) {
    if (!id) {
      notify('Игрок не выбран.', 'error');
      return null;
    }

    const pokemon = await choosePvpPokemon(activePvpStatus === 'incoming' ? 'Кого отправить в ответ?' : 'Кого отправить в бой?');
    if (!pokemon) {
      return null;
    }

    try {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('user_id', String(id));
      body.set('pokemon_id', String(pokemon.id));

      const response = await fetch('/api/battle/pvp/request', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        },
        body
      });
      const data = await response.json();
      notify(data.message || (data.ok ? 'Вызов на бой отправлен.' : 'Бой не удалось начать.'), data.ok ? 'success' : 'error');
      if (data.ok && (data.status === 'active' || Number(data.battleId || 0) > 0)) {
        document.dispatchEvent(new CustomEvent('pvp-battle-started', { detail: data }));
      }
      return data;
    } catch (error) {
      console.error('PvP action failed:', error);
      notify('Ошибка сервера при вызове на бой.', 'error');
      return null;
    }
  }

  async function forceBattle(id) {
    if (!id) {
      notify('Игрок не выбран.', 'error');
      return null;
    }

    const pokemon = await choosePvpPokemon('Кого отправить в принудительный бой?');
    if (!pokemon) {
      return null;
    }

    try {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('user_id', String(id));
      body.set('pokemon_id', String(pokemon.id));

      const response = await fetch('/api/battle/pvp/force', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        },
        body
      });
      const data = await response.json();
      notify(data.message || (data.ok ? 'Принудительный бой начался.' : 'Нападение не удалось.'), data.ok ? 'success' : 'error');
      if (data.ok && (data.status === 'active' || Number(data.battleId || 0) > 0)) {
        document.dispatchEvent(new CustomEvent('pvp-battle-started', { detail: data }));
      }
      return data;
    } catch (error) {
      console.error('Forced PvP action failed:', error);
      notify('Ошибка сервера при нападении по ордеру.', 'error');
      return null;
    }
  }

  async function loadPvpPokemonOptions() {
    const response = await fetch('/api/battle/pvp/pokemon-options', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
    });
    const data = await response.json();
    if (!data || data.ok !== true) {
      throw new Error(data && data.message ? data.message : 'Не удалось загрузить команду.');
    }
    return Array.isArray(data.pokemon) ? data.pokemon : [];
  }

  function choosePvpPokemon(title) {
    return new Promise(async resolve => {
      let settled = false;
      const done = value => {
        if (settled) return;
        settled = true;
        pvpModal.hidden = true;
        pvpModal.innerHTML = '';
        document.removeEventListener('keydown', onKey);
        resolve(value);
      };
      const onKey = event => {
        if (event.key === 'Escape') done(null);
      };
      document.addEventListener('keydown', onKey);

      pvpModal.hidden = false;
      pvpModal.innerHTML = [
        '<div class="pvp-pokemon-dialog" role="dialog" aria-modal="true">',
          '<header><div><b></b><span>Выберите живого покемона из активной команды</span></div><button type="button" data-pvp-close>&times;</button></header>',
          '<div class="pvp-pokemon-list"><div class="pvp-pokemon-empty">Загружаю команду...</div></div>',
        '</div>',
      ].join('');
      pvpModal.querySelector('header b').textContent = title || 'Выбор покемона';
      pvpModal.querySelector('[data-pvp-close]').addEventListener('click', () => done(null));
      pvpModal.addEventListener('click', event => {
        if (event.target === pvpModal) done(null);
      }, { once: true });

      const list = pvpModal.querySelector('.pvp-pokemon-list');
      try {
        const pokemon = await loadPvpPokemonOptions();
        if (!pokemon.length) {
          list.innerHTML = '<div class="pvp-pokemon-empty">Нет живых активных покемонов.</div>';
          return;
        }
        list.innerHTML = pokemon.map(item => {
          const hp = hpPercent(item);
          const name = String(item.name || ('Pokemon #' + Number(item.baseNum || 0))).replace(/^#?\d+\s*/, '');
          return [
            '<button type="button" class="pvp-pokemon-choice" data-pokemon-id="' + Number(item.id || 0) + '">',
              '<span class="pvp-pokemon-art"><img src="' + pokemonSprite(item) + '" alt=""></span>',
              '<span class="pvp-pokemon-main">',
                '<b>#' + pad3(item.baseNum) + ' ' + name + '</b>',
                '<small>Lv.' + Number(item.level || 0) + ' · HP ' + Number(item.hp || 0) + '/' + Number(item.hpMax || 0) + '</small>',
                '<i><em style="width:' + hp + '%"></em></i>',
              '</span>',
            '</button>',
          ].join('');
        }).join('');
        list.querySelectorAll('[data-pokemon-id]').forEach(button => {
          button.addEventListener('click', () => {
            const id = Number(button.dataset.pokemonId || 0);
            done(pokemon.find(item => Number(item.id || 0) === id) || null);
          });
        });
      } catch (error) {
        console.error('PvP pokemon options failed:', error);
        list.innerHTML = '<div class="pvp-pokemon-empty is-error">Не удалось загрузить команду.</div>';
      }
    });
  }

  async function pollIncomingRequests() {
    try {
      const response = await fetch('/api/friends/requests', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      const data = await response.json();
      if (!data.ok || !Array.isArray(data.requests)) return;

      data.requests.forEach(request => {
        const id = Number(request.id || 0);
        if (!id || knownIncomingRequests.has(id)) return;

        knownIncomingRequests.add(id);
        notify((request.login || 'Игрок') + ' отправил заявку в друзья.', 'friend');
      });
      saveSeenRequests();
    } catch (error) {
      // Silent: this endpoint polls often and should not annoy the player on transient errors.
    }
  }

  async function pollIncomingPvpRequests() {
    try {
      const response = await fetch('/api/battle/pvp/requests', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      const data = await response.json();
      if (!data.ok || !Array.isArray(data.requests)) return;

      data.requests.forEach(request => {
        const id = Number(request.id || 0);
        if (!id || knownIncomingPvpRequests.has(id)) return;

        knownIncomingPvpRequests.add(id);
        notify((request.login || 'Игрок') + ' вызывает вас на PvP бой.', 'friend');
      });
      saveSeenPvpRequests();
    } catch (error) {
      // Silent: this endpoint polls often and should not annoy the player on transient errors.
    }
  }

  window.PokemonSocial = window.PokemonSocial || {};
  window.PokemonSocial.notify = notify;
  window.PokemonSocial.requestFriend = requestFriend;
  window.PokemonSocial.acceptFriend = acceptFriend;
  window.PokemonSocial.removeFriend = removeFriend;
  window.PokemonSocial.requestBattle = requestBattle;
  window.PokemonSocial.forceBattle = forceBattle;

  document.addEventListener('click', async event => {
    const actionButton = event.target.closest('[data-player-action]');
    if (actionButton && menu.contains(actionButton)) {
      const action = actionButton.dataset.playerAction;
      const playerId = Number(menu.dataset.playerId || 0);
      let friendResult = null;

      if (action === 'friend') friendResult = await requestFriend(playerId);
      if (action === 'friend-accept') friendResult = await acceptFriend(playerId);
      if (action === 'friend-remove') friendResult = await removeFriend(playerId);
      if (action === 'battle' || action === 'battle-accept') {
        await requestBattle(playerId);
        closeMenu();
        return;
      }
      if (action === 'battle-force') {
        await forceBattle(playerId);
        closeMenu();
        return;
      }

      if (action.startsWith('friend')) {
        closeMenu();
        return;
      }

      const url = actionUrl(action, activeLogin);
      if (url) {
        window.location.href = url;
      } else {
        document.dispatchEvent(new CustomEvent('player-menu-action', {
          detail: {
            action,
            login: activeLogin,
            id: playerId,
          },
        }));
      }
      closeMenu();
      return;
    }

    const row = event.target.closest('.player-row');
    if (row && document.getElementById('usersList')?.contains(row)) {
      event.preventDefault();
      openMenu(row);
      return;
    }

    if (!event.target.closest('.player-context-menu')) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeMenu();
    if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('.player-row')) {
      event.preventDefault();
      openMenu(event.target.closest('.player-row'));
    }
  });

  window.addEventListener('resize', closeMenu);
  window.addEventListener('scroll', closeMenu, true);

  pollIncomingRequests();
  pollIncomingPvpRequests();
  window.setInterval(pollIncomingRequests, 10000);
  window.setInterval(pollIncomingPvpRequests, 10000);
}());
