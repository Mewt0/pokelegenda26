(function () {
  'use strict';

  const menu = document.createElement('div');
  menu.className = 'player-context-menu';
  menu.hidden = true;
  document.body.appendChild(menu);

  let activeRow = null;
  let activeLogin = '';

  const text = {
    localPlayer: '\u0438\u0433\u0440\u043e\u043a \u043d\u0430 \u043b\u043e\u043a\u0430\u0446\u0438\u0438',
    card: '\u0422\u0440\u0435\u043d\u0435\u0440\u043a\u0430\u0440\u0442\u0430',
    dialog: '\u041e\u0442\u043a\u0440\u044b\u0442\u044c \u0434\u0438\u0430\u043b\u043e\u0433',
    private: '\u041d\u0430\u043f\u0438\u0441\u0430\u0442\u044c \u0432 \u041b\u0421',
    battle: '\u0412\u044b\u0437\u0432\u0430\u0442\u044c \u043d\u0430 \u0431\u043e\u0439',
    trade: '\u041f\u0440\u0435\u0434\u043b\u043e\u0436\u0438\u0442\u044c \u043e\u0431\u043c\u0435\u043d',
    friend: '\u0414\u043e\u0431\u0430\u0432\u0438\u0442\u044c \u0432 \u0434\u0440\u0443\u0437\u044c\u044f',
    ignore: '\u0414\u043e\u0431\u0430\u0432\u0438\u0442\u044c \u0432 \u0447\u0451\u0440\u043d\u044b\u0439 \u0441\u043f\u0438\u0441\u043e\u043a',
    mail: '\u041d\u0430\u043f\u0438\u0441\u0430\u0442\u044c \u043d\u0430 \u043f\u043e\u0447\u0442\u0443',
  };

  function closeMenu() {
    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = null;
    activeLogin = '';
    menu.hidden = true;
    menu.innerHTML = '';
    menu.dataset.playerId = '';
  }

  function menuButton(action, icon, label) {
    return '<button type="button" data-player-action="' + action + '"><span class="pcm-icon">' + icon + '</span><span>' + label + '</span></button>';
  }

  function openMenu(row) {
    const login = row.dataset.playerLogin || row.querySelector('.user-name')?.textContent || '';
    if (!login) return;

    if (activeRow === row && !menu.hidden) {
      closeMenu();
      return;
    }

    if (activeRow) activeRow.classList.remove('is-menu-open');
    activeRow = row;
    activeLogin = login;
    menu.dataset.playerId = row.dataset.playerId || '';
    row.classList.add('is-menu-open');

    const initial = login.trim().charAt(0).toUpperCase() || '?';
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
      menuButton('friend', '&#9793;', text.friend),
      menuButton('ignore', '&#8856;', text.ignore),
      menuButton('mail', '&#9993;', text.mail),
    ].join('');
    menu.querySelector('b').textContent = login;

    const rect = row.getBoundingClientRect();
    const width = 260;
    let left = Math.min(window.innerWidth - width - 10, Math.max(10, rect.left - 8));
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

  function actionUrl(action, login) {
    const encoded = encodeURIComponent(login);
    const id = Number(menu.dataset.playerId || 0);
    if (action === 'mail') return '/game/messages?mail_to=' + encoded;
    if (action === 'card') return id > 0 ? '/game/profile?id=' + id : '/game/profile?user=' + encoded;
    return '';
  }

  document.addEventListener('click', event => {
    const actionButton = event.target.closest('[data-player-action]');
    if (actionButton && menu.contains(actionButton)) {
      const action = actionButton.dataset.playerAction;
      const url = actionUrl(action, activeLogin);
      if (url) {
        window.location.href = url;
      } else {
        document.dispatchEvent(new CustomEvent('player-menu-action', {
          detail: {
            action,
            login: activeLogin,
            id: Number(menu.dataset.playerId || 0),
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
}());
