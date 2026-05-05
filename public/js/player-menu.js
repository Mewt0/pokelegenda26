(function () {
  'use strict';

  const menu = document.createElement('div');
  menu.className = 'player-context-menu';
  menu.hidden = true;
  document.body.appendChild(menu);

  const toastStack = document.createElement('div');
  toastStack.className = 'game-toast-stack';
  document.body.appendChild(toastStack);

  let activeRow = null;
  let activeLogin = '';
  let activePlayerId = 0;
  let activeFriendStatus = 'loading';
  let knownIncomingRequests = new Set(loadSeenRequests());

  const app = document.querySelector('.world');
  const csrf = app ? app.dataset.csrf : '';

  const text = {
    localPlayer: 'игрок на локации',
    card: 'Тренеркарта',
    dialog: 'Открыть диалог',
    private: 'Написать в ЛС',
    battle: 'Вызвать на бой',
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

  function closeMenu() {
    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = null;
    activeLogin = '';
    activePlayerId = 0;
    activeFriendStatus = 'loading';
    menu.hidden = true;
    menu.innerHTML = '';
    menu.dataset.playerId = '';
  }

  function menuButton(action, icon, label, disabled = false) {
    return '<button type="button" data-player-action="' + action + '"' + (disabled ? ' disabled' : '') + '><span class="pcm-icon">' + icon + '</span><span>' + label + '</span></button>';
  }

  function friendButtonForStatus(status) {
    if (status === 'self') return '';
    if (status === 'incoming') return menuButton('friend-accept', '&#10003;', text.acceptFriend);
    if (status === 'friends') return menuButton('friend-remove', '&#8722;', text.removeFriend);
    if (status === 'outgoing') return menuButton('friend-pending', '&#8987;', text.requestSent, true);
    if (status === 'loading') return menuButton('friend-pending', '&#8987;', 'Проверяем...', true);
    return menuButton('friend', '&#9793;', text.friend);
  }

  function renderMenu() {
    if (!activeRow || !activeLogin) return;

    const initial = activeLogin.trim().charAt(0).toUpperCase() || '?';
    menu.innerHTML = [
      '<div class="pcm-head">',
        '<div class="pcm-avatar">' + initial + '</div>',
        '<div><b></b><small>' + text.localPlayer + '</small></div>',
      '</div>',
      menuButton('card', '&#9817;', text.card),
      menuButton('dialog', '&#9743;', text.dialog),
      menuButton('private', '&#9998;', text.private),
      '<hr>',
      menuButton('battle', '&#9876;', text.battle),
      menuButton('trade', '&#8644;', text.trade),
      '<hr>',
      friendButtonForStatus(activeFriendStatus),
      menuButton('ignore', '&#8856;', text.ignore),
      menuButton('mail', '&#9993;', text.mail),
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
      const data = await response.json();
      return data.ok ? String(data.status || 'none') : 'none';
    } catch (error) {
      console.error('Friend status failed:', error);
      return 'none';
    }
  }

  async function openMenu(row) {
    const login = row.dataset.playerLogin || row.querySelector('.user-name')?.textContent || '';
    if (!login) return;

    if (activeRow === row && !menu.hidden) {
      closeMenu();
      return;
    }

    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = row;
    activeLogin = login;
    activePlayerId = Number(row.dataset.playerId || 0);
    activeFriendStatus = 'loading';
    menu.dataset.playerId = String(activePlayerId || '');
    row.classList.add('is-menu-open');

    renderMenu();
    positionMenu(row);

    const status = await loadFriendStatus(activePlayerId);
    if (activeRow !== row || menu.hidden) return;
    activeFriendStatus = status;
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

  window.PokemonSocial = window.PokemonSocial || {};
  window.PokemonSocial.notify = notify;
  window.PokemonSocial.requestFriend = requestFriend;
  window.PokemonSocial.acceptFriend = acceptFriend;
  window.PokemonSocial.removeFriend = removeFriend;

  document.addEventListener('click', async event => {
    const actionButton = event.target.closest('[data-player-action]');
    if (actionButton && menu.contains(actionButton)) {
      const action = actionButton.dataset.playerAction;
      const playerId = Number(menu.dataset.playerId || 0);
      let friendResult = null;

      if (action === 'friend') friendResult = await requestFriend(playerId);
      if (action === 'friend-accept') friendResult = await acceptFriend(playerId);
      if (action === 'friend-remove') friendResult = await removeFriend(playerId);

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
  window.setInterval(pollIncomingRequests, 10000);
}());
