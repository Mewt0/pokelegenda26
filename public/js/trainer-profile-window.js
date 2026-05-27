(function () {
  'use strict';

  const app = document.querySelector('.world');
  if (!app) return;

  let overlay = null;
  let win = null;
  let dragReady = false;
  let dragging = false;
  let dragPointerId = null;
  let offsetX = 0;
  let offsetY = 0;
  let loadSeq = 0;
  let currentProfileOptions = {};

  function ensureDom() {
    if (overlay) return;

    overlay = document.createElement('section');
    overlay.className = 'trainer-profile-overlay';
    overlay.id = 'trainerProfileOverlay';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML = [
      '<div class="trainer-profile-window" role="dialog" aria-label="Профиль игрока">',
        '<header class="trainer-profile-head">',
          '<strong>Профиль игрока</strong>',
          '<button type="button" class="trainer-profile-close" aria-label="Закрыть">&times;</button>',
        '</header>',
        '<div class="trainer-profile-body">',
          '<aside class="trainer-profile-side">',
            '<section class="trainer-profile-panel">',
              '<div class="trainer-profile-clan-top">',
                '<div class="trainer-profile-clan-art"><img data-trainer-clan-img alt=""></div>',
                '<div class="trainer-profile-clan-name"><div>Клан</div><b data-trainer-clan-name></b></div>',
              '</div>',
              '<div class="trainer-profile-ribbon">Вклад рейтинга: <span data-trainer-clan-points>0</span></div>',
            '</section>',
            '<section class="trainer-profile-panel">',
              '<h3>Награды</h3>',
              '<div class="trainer-profile-gym-title">Значки гим-лидеров</div>',
              '<div class="trainer-profile-gymbadges" data-trainer-gym-badges></div>',
              '<div class="trainer-profile-count trainer-profile-badges-count"><span data-trainer-badges-count>0</span> значков</div>',
              '<div class="trainer-profile-awards" data-trainer-awards></div>',
              '<div class="trainer-profile-count"><span data-trainer-awards-count>0</span> наград</div>',
            '</section>',
            '<section class="trainer-profile-panel trainer-profile-info">',
              '<h3>Информация</h3>',
              '<div class="trainer-profile-bio" data-trainer-info></div>',
              '<div class="trainer-profile-stats" data-trainer-stats></div>',
            '</section>',
          '</aside>',
          '<main class="trainer-profile-center">',
            '<section class="trainer-profile-namezone">',
              '<div class="trainer-profile-hand" aria-hidden="true">&#128075;</div>',
              '<div class="trainer-profile-name-main">',
                '<h2 data-trainer-name>...</h2>',
                '<div class="trainer-profile-party" data-trainer-party></div>',
                '<div class="trainer-profile-party-meter"><i data-trainer-party-meter style="width:0%"></i></div>',
                '<div class="trainer-profile-social" data-trainer-social></div>',
              '</div>',
              '<div class="trainer-profile-hand is-right" aria-hidden="true">&#128075;</div>',
            '</section>',
            '<section class="trainer-profile-avatar-zone">',
              '<img class="trainer-profile-avatar" data-trainer-avatar alt="">',
            '</section>',
          '</main>',
          '<aside class="trainer-profile-side">',
            '<section class="trainer-profile-panel">',
              '<div class="trainer-profile-activity-top">',
                '<div class="trainer-profile-activity-name"><div>Активность тренера</div><b data-trainer-activity></b></div>',
                '<div class="trainer-profile-vip"><img data-trainer-rank-img alt=""></div>',
              '</div>',
              '<div class="trainer-profile-ribbon">Рейтинг: <span data-trainer-rating>0</span></div>',
            '</section>',
            '<section class="trainer-profile-panel">',
              '<h3>Подарки</h3>',
              '<div class="trainer-profile-gifts" data-trainer-gifts></div>',
              '<div class="trainer-profile-count"><span data-trainer-gifts-count>0</span> подарков</div>',
            '</section>',
            '<section class="trainer-profile-panel trainer-profile-friends">',
              '<h3>Список друзей (<span data-trainer-friends-count>0</span>)</h3>',
              '<div class="trainer-profile-friends-list" data-trainer-friends></div>',
            '</section>',
          '</aside>',
        '</div>',
        '<footer class="trainer-profile-foot">',
          '<span data-trainer-town>...</span>',
          '<b class="trainer-profile-location" data-trainer-location>...</b>',
          '<span>UID: <b data-trainer-uid>0</b></span>',
        '</footer>',
      '</div>',
    ].join('');
    document.body.appendChild(overlay);
    win = overlay.querySelector('.trainer-profile-window');

    overlay.querySelector('.trainer-profile-close').addEventListener('click', close);
    overlay.addEventListener('click', event => {
      if (event.target === overlay) close();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
        close();
      }
    });
    setupDrag();
  }

  function setupDrag() {
    if (dragReady || !win) return;
    dragReady = true;
    const head = win.querySelector('.trainer-profile-head');

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

    const beginDrag = (event, pointerId = null) => {
      if (event.target.closest('button')) return;
      const rect = win.getBoundingClientRect();
      dragging = true;
      dragPointerId = pointerId;
      offsetX = event.clientX - rect.left;
      offsetY = event.clientY - rect.top;
      win.classList.add('is-dragged');
      win.style.left = rect.left + 'px';
      win.style.top = rect.top + 'px';
      event.preventDefault();
    };

    const moveDrag = event => {
      if (!dragging) return;
      const pos = clampWindow(event.clientX - offsetX, event.clientY - offsetY);
      win.style.left = pos.left + 'px';
      win.style.top = pos.top + 'px';
    };

    const endDrag = () => {
      dragging = false;
      dragPointerId = null;
    };

    head.addEventListener('pointerdown', event => {
      beginDrag(event, event.pointerId);
      try { head.setPointerCapture(event.pointerId); } catch (e) {}
    });
    document.addEventListener('pointermove', event => {
      if (dragPointerId !== null && event.pointerId !== dragPointerId) return;
      moveDrag(event);
    });
    document.addEventListener('pointerup', event => {
      if (dragPointerId !== null && event.pointerId !== dragPointerId) return;
      try { head.releasePointerCapture(event.pointerId); } catch (e) {}
      endDrag();
    });
    document.addEventListener('pointercancel', event => {
      if (dragPointerId !== null && event.pointerId !== dragPointerId) return;
      endDrag();
    });

    head.addEventListener('mousedown', event => {
      if (event.button !== 0 || dragging) return;
      beginDrag(event);
    });
    document.addEventListener('mousemove', moveDrag);
    document.addEventListener('mouseup', endDrag);

    document.addEventListener('mouseleave', () => {
      if (dragging) endDrag();
    });
    window.addEventListener('resize', () => {
      if (!win.classList.contains('is-dragged')) return;
      const rect = win.getBoundingClientRect();
      const pos = clampWindow(rect.left, rect.top);
      win.style.left = pos.left + 'px';
      win.style.top = pos.top + 'px';
    });
  }

  function open(options = {}) {
    ensureDom();
    currentProfileOptions = { ...options };
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    renderLoading(options.login || options.user || options.id || '');
    loadProfile(options);
  }

  function close() {
    if (!overlay) return;
    dragging = false;
    dragPointerId = null;
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  }

  async function loadProfile(options) {
    const seq = ++loadSeq;
    const params = new URLSearchParams();
    if (Number(options.id || 0) > 0) {
      params.set('id', String(Number(options.id)));
    } else if (String(options.user || options.login || '').trim() !== '') {
      params.set('user', String(options.user || options.login).trim());
    }

    try {
      const response = await fetch('/api/profile/card?' + params.toString(), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      const data = await response.json();
      if (seq !== loadSeq) return;

      if (!data || data.ok !== true) {
        renderError(data && data.message ? data.message : 'Профиль не найден.');
        return;
      }

      renderProfile(data.profile || {});
    } catch (error) {
      if (seq !== loadSeq) return;
      console.error('Trainer profile failed:', error);
      renderError('Не удалось загрузить профиль тренера.');
    }
  }

  function qs(selector) {
    return overlay.querySelector(selector);
  }

  function setText(selector, value) {
    const node = qs(selector);
    if (node) node.textContent = String(value ?? '');
  }

  function setImage(selector, src, fallback) {
    const image = qs(selector);
    if (!image) return;
    image.onerror = () => {
      if (fallback && image.src.indexOf(fallback) === -1) {
        image.src = fallback;
      }
    };
    image.src = src || fallback || '';
  }

  function renderLoading(target) {
    setText('[data-trainer-name]', target ? 'Загружаю: ' + target : 'Загружаю профиль...');
    setText('[data-trainer-info]', 'Получаю данные тренера.');
    setText('[data-trainer-clan-name]', '...');
    setText('[data-trainer-clan-points]', '0');
    setText('[data-trainer-activity]', '...');
    setText('[data-trainer-rating]', '0');
    setText('[data-trainer-town]', '...');
    setText('[data-trainer-location]', '...');
    setText('[data-trainer-uid]', '0');
    setImage('[data-trainer-avatar]', '/public/img/ui/menu-profile.png', '/public/img/ui/menu-profile.png');
    setImage('[data-trainer-rank-img]', '/img/info/rang/0.png', '/public/img/ui/menu-profile.png');
    clearList('[data-trainer-party]', 'Загрузка...');
    clearList('[data-trainer-gym-badges]', '');
    clearList('[data-trainer-awards]', '');
    clearList('[data-trainer-gifts]', '');
    clearList('[data-trainer-friends]', 'Загрузка...');
    clearList('[data-trainer-social]', '');
    setText('[data-trainer-awards-count]', '0');
    setText('[data-trainer-badges-count]', '0');
    setText('[data-trainer-gifts-count]', '0');
    setText('[data-trainer-friends-count]', '0');
    qs('[data-trainer-party-meter]').style.width = '0%';
    qs('[data-trainer-stats]').innerHTML = '';
  }

  function renderError(message) {
    setText('[data-trainer-name]', 'Профиль недоступен');
    clearList('[data-trainer-party]', '');
    clearList('[data-trainer-gym-badges]', '');
    clearList('[data-trainer-awards]', '');
    clearList('[data-trainer-gifts]', '');
    clearList('[data-trainer-friends]', '');
    clearList('[data-trainer-social]', '');
    setText('[data-trainer-badges-count]', '0');
    qs('[data-trainer-stats]').innerHTML = '<div class="trainer-profile-error"></div>';
    qs('.trainer-profile-error').textContent = message;
  }

  function renderProfile(profile) {
    const user = profile.user || {};
    const clan = user.clan || {};
    const karma = user.karma || {};
    const party = Array.isArray(profile.party) ? profile.party : [];
    const awards = Array.isArray(profile.awards) ? profile.awards : [];
    const gifts = Array.isArray(profile.gifts) ? profile.gifts : [];
    const gymBadges = Array.isArray(profile.gymBadges)
      ? profile.gymBadges
      : (Array.isArray(profile.badges) ? profile.badges : []);
    const friends = Array.isArray(profile.friends) ? profile.friends : [];
    const social = profile.social || {};

    setText('[data-trainer-name]', '[' + Number(user.id || profile.uid || 0) + '] ' + (user.login || 'Тренер'));
    setText('[data-trainer-clan-name]', clan.name || 'Без клана');
    setText('[data-trainer-clan-points]', formatNumber(clan.points || 0));
    setImage('[data-trainer-clan-img]', clan.image ? '/img/clan/' + clan.image + '.png' : '/public/img/ui/menu-profile.png', '/public/img/ui/menu-profile.png');
    setText('[data-trainer-activity]', user.online ? 'В игре' : (user.group || 'Тренер'));
    setText('[data-trainer-rating]', formatNumber(user.pvpRating || 0));
    setText('[data-trainer-info]', user.info || 'Информация о тренере пока не заполнена.');
    setText('[data-trainer-town]', user.town || 'Неизвестный регион');
    setText('[data-trainer-location]', user.location || 'Неизвестная локация');
    setText('[data-trainer-uid]', Number(user.id || profile.uid || 0));
    setImage('[data-trainer-avatar]', user.avatar || '/public/img/ui/menu-profile.png', '/public/img/ui/menu-profile.png');
    setImage('[data-trainer-rank-img]', user.rankImage || '/img/info/rang/0.png', '/img/info/rang/0.png');

    renderParty(party);
    renderGymBadges(gymBadges);
    renderPresentCells('[data-trainer-awards]', awards, 10);
    renderPresentCells('[data-trainer-gifts]', gifts, 10);
    renderFriends(friends);
    renderSocialActions(user, social);
    renderStats(user, karma);
    setText('[data-trainer-awards-count]', awards.length);
    setText('[data-trainer-badges-count]', gymBadges.length);
    setText('[data-trainer-gifts-count]', gifts.length);
    setText('[data-trainer-friends-count]', friends.length);
  }

  function renderStats(user, karma) {
    const stats = [
      ['Ранг', user.rank || 'Новичок'],
      ['Рейтинг PVP', formatNumber(user.pvpRating || 0) + ' (' + (user.pvpTitle || 'Новичок') + ')'],
      ['Рейтинг PVE', formatNumber(user.pveRating || 0) + ' (' + (user.pveTitle || 'Искатель') + ')'],
      ['Игровой рейтинг', formatNumber(user.questRating || 0)],
      ['Коллекция', [user.normalDex || 0, user.shinyDex || 0].join(' / ')],
      ['Репутация', (karma.title || 'Нейтральная репутация') + ' ' + formatNumber(karma.score || 0)],
      ['Регистрация', user.registeredAt || 'нет данных'],
      ['Проведено в игре', formatOnlineTime(user.lastOnline)],
    ];

    const box = qs('[data-trainer-stats]');
    box.innerHTML = '';
    stats.forEach(([label, value]) => {
      const row = document.createElement('div');
      row.className = 'trainer-profile-stat';
      const l = document.createElement('span');
      l.textContent = label + ':';
      const v = document.createElement('b');
      v.textContent = value;
      row.append(l, v);
      box.appendChild(row);
    });
  }

  function renderParty(party) {
    const box = qs('[data-trainer-party]');
    box.innerHTML = '';
    const alive = party.slice(0, 6);
    for (let i = 0; i < 6; i++) {
      const pokemon = alive[i] || null;
      const slot = document.createElement('span');
      slot.className = 'trainer-profile-party-slot';
      const image = document.createElement('img');
      image.className = 'trainer-profile-party-mon';
      image.alt = pokemon ? (pokemon.name || 'Покемон') : 'Пустой слот';
      image.title = pokemon ? (pokemon.name || 'Покемон') + ' Lv.' + Number(pokemon.level || 0) : 'Пустой слот';
      image.src = pokemon ? pokemonSprite(pokemon) : '/public/img/ui/chatgpt-pokeball.png';
      image.onerror = () => {
        image.onerror = null;
        image.src = '/public/img/ui/chatgpt-pokeball.png';
      };
      slot.appendChild(image);
      const item = pokemon ? heldItem(pokemon) : null;
      if (item) {
        const held = document.createElement('img');
        held.className = 'trainer-profile-party-held';
        held.alt = item.name || 'Предмет';
        held.title = item.name || ('Item #' + Number(item.id || 0));
        held.src = item.image || itemIconSrc(item.id);
        held.onerror = () => {
          held.onerror = () => { held.src = '/public/img/ui/menu-inventory.png'; };
          held.src = '/img/items/' + Number(item.id || 0) + '.png';
        };
        slot.appendChild(held);
      }
      box.appendChild(slot);
    }
    qs('[data-trainer-party-meter]').style.width = Math.min(100, Math.round((alive.length / 6) * 100)) + '%';
  }

  function heldItem(pokemon) {
    return pokemon && pokemon.heldItem && Number(pokemon.heldItem.id || 0) > 0 ? pokemon.heldItem : null;
  }

  function itemIconSrc(id) {
    return '/public/img/items/' + Number(id || 0) + '.png';
  }

  function renderGymBadges(badges) {
    const box = qs('[data-trainer-gym-badges]');
    box.innerHTML = '';
    const visible = badges.slice(0, 12);
    if (!visible.length) {
      const empty = document.createElement('span');
      empty.className = 'trainer-profile-gym-empty';
      empty.textContent = 'Пока нет значков.';
      box.appendChild(empty);
      return;
    }

    visible.forEach(badge => {
      const cell = document.createElement('span');
      cell.className = 'trainer-profile-gym-badge';
      const image = document.createElement('img');
      image.src = badge.image || '/public/img/ui/menu-profile.png';
      image.alt = badge.title || 'Значок';
      const issuedAt = Number(badge.issuedAt || badge.awardedAt || 0);
      const source = badge.source || {};
      image.title = [
        badge.title,
        badge.leader ? 'Лидер: ' + badge.leader : '',
        badge.location ? 'Локация: ' + badge.location : '',
        issuedAt ? 'Получен: ' + formatOnlineTime(issuedAt) : '',
        source.type ? 'Источник: ' + source.type + (source.id ? ' #' + Number(source.id) : '') : '',
      ].filter(Boolean).join(' • ');
      image.onerror = () => {
        image.onerror = null;
        image.src = '/public/img/ui/menu-profile.png';
      };
      cell.appendChild(image);
      box.appendChild(cell);
    });

    if (badges.length > visible.length) {
      const more = document.createElement('span');
      more.className = 'trainer-profile-gym-badge trainer-profile-gym-more';
      more.textContent = '+' + (badges.length - visible.length);
      more.title = 'Ещё значков: ' + (badges.length - visible.length);
      box.appendChild(more);
    }
  }

  function renderSocialActions(user, social) {
    const box = qs('[data-trainer-social]');
    box.innerHTML = '';
    const id = Number(user.id || social.profileId || 0);
    const login = String(user.login || '').trim();
    const status = String(social.status || (social.own ? 'self' : 'none'));
    const own = Boolean(social.own || status === 'self');

    const state = document.createElement('span');
    state.className = 'trainer-profile-social-status';
    state.textContent = own ? 'Мой профиль' : socialLabel(status);
    box.appendChild(state);

    if (own || id <= 0) return;

    if (social.canMessage !== false) {
      const message = document.createElement('a');
      message.className = 'trainer-profile-social-button';
      message.href = '/game/messages?mail_to=' + encodeURIComponent(login || String(id));
      message.textContent = 'Сообщение';
      box.appendChild(message);
    }

    if (social.canAcceptFriend) {
      box.appendChild(socialButton('Принять', () => runSocialAction('acceptFriend', id)));
    } else if (social.canRemoveFriend) {
      box.appendChild(socialButton('Убрать', () => runSocialAction('removeFriend', id)));
    } else if (social.canRequestFriend) {
      box.appendChild(socialButton('В друзья', () => runSocialAction('requestFriend', id)));
    } else if (status === 'outgoing') {
      const pending = document.createElement('button');
      pending.type = 'button';
      pending.className = 'trainer-profile-social-button is-disabled';
      pending.disabled = true;
      pending.textContent = 'Заявка отправлена';
      box.appendChild(pending);
    }

    if (social.canBattle !== false) {
      box.appendChild(socialButton('Вызвать', () => runSocialAction('requestBattle', id)));
    }
  }

  function socialButton(label, handler) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'trainer-profile-social-button';
    button.textContent = label;
    button.addEventListener('click', handler);
    return button;
  }

  function socialLabel(status) {
    if (status === 'friends') return 'Друзья';
    if (status === 'incoming') return 'Входящая';
    if (status === 'outgoing') return 'Заявка отправлена';
    return 'Не в друзьях';
  }

  async function runSocialAction(method, id) {
    const api = window.PokemonSocial || {};
    if (!api || typeof api[method] !== 'function') {
      notify('Социальные действия ещё загружаются.', 'error');
      return;
    }

    try {
      await api[method](id);
      if (method !== 'requestBattle') {
        loadProfile(currentProfileOptions);
      }
    } catch (error) {
      console.error('Trainer social action failed:', error);
      notify('Не удалось выполнить действие.', 'error');
    }
  }

  function notify(message, type) {
    if (window.PokemonSocial && typeof window.PokemonSocial.notify === 'function') {
      window.PokemonSocial.notify(message, type || 'info');
      return;
    }
    if (type === 'error') {
      console.warn(message);
    }
  }

  function pokemonSprite(pokemon) {
    const base = Math.max(0, Number(pokemon && pokemon.baseNum || 0));
    if (base <= 0) return '/public/img/ui/chatgpt-pokeball.png';
    const shiny = String(pokemon && pokemon.name || '').toLowerCase().includes('shiny');
    if (base >= 5000) return shiny ? '/Pok/shiny/' + base + '.gif' : '/Pok/pok/' + base + '.gif';
    return shiny ? '/Pok/shiny/' + base + '.gif' : '/Pok/normal/' + base + '.png';
  }

  function renderPresentCells(selector, items, limit) {
    const box = qs(selector);
    box.innerHTML = '';
    const visible = items.slice(0, limit);
    for (let i = 0; i < limit; i++) {
      const item = visible[i] || null;
      const cell = document.createElement('span');
      cell.className = 'trainer-profile-cell';
      if (item) {
        const image = document.createElement('img');
        image.src = item.image || '/public/img/ui/menu-inventory.png';
        image.alt = item.name || '';
        image.title = [item.name, item.title, item.comment].filter(Boolean).join(' ');
        image.onerror = () => {
          image.onerror = null;
          image.src = '/public/img/ui/menu-inventory.png';
        };
        cell.appendChild(image);
      }
      box.appendChild(cell);
    }
  }

  function renderFriends(friends) {
    const box = qs('[data-trainer-friends]');
    box.innerHTML = '';
    if (!friends.length) {
      const empty = document.createElement('div');
      empty.className = 'trainer-profile-empty';
      empty.textContent = 'Друзей пока нет.';
      box.appendChild(empty);
      return;
    }

    friends.forEach(friend => {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'trainer-profile-friend' + (friend.online ? ' is-online' : '');
      row.title = friend.group || 'Тренер';
      row.dataset.profileId = String(friend.id || 0);
      row.innerHTML = '<i></i><span></span>';
      row.querySelector('span').textContent = friend.login || ('#' + Number(friend.id || 0));
      row.addEventListener('click', () => {
        if (Number(friend.id || 0) > 0) open({ id: Number(friend.id) });
      });
      box.appendChild(row);
    });
  }

  function clearList(selector, text) {
    const box = qs(selector);
    if (!box) return;
    box.innerHTML = '';
    if (text) {
      const node = document.createElement('div');
      node.className = 'trainer-profile-loading';
      node.textContent = text;
      box.appendChild(node);
    }
  }

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('ru-RU');
  }

  function formatOnlineTime(timestamp) {
    const time = Number(timestamp || 0);
    if (time <= 0) return 'нет данных';
    const date = new Date(time * 1000);
    return date.toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
  }

  document.addEventListener('player-menu-action', event => {
    const detail = event.detail || {};
    if (detail.action !== 'card') return;
    open({ id: Number(detail.id || 0), login: detail.login || '' });
  });

  document.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented) return;
    if (link.target && link.target !== '_self') return;

    let url = null;
    try {
      url = new URL(link.href, window.location.origin);
    } catch (error) {
      return;
    }

    if (url.origin !== window.location.origin || url.pathname !== '/game/profile') return;
    const id = Number(url.searchParams.get('id') || 0);
    const login = String(url.searchParams.get('user') || url.searchParams.get('login') || '').trim();
    if (id <= 0 && login === '') {
      event.preventDefault();
      open({});
      return;
    }

    event.preventDefault();
    open(id > 0 ? { id, login } : { login });
  });

  window.TrainerProfileWindow = {
    open,
    close,
  };
}());
