(function () {
  'use strict';

  const app = document.querySelector('.world');
  if (!app) return;

  const csrf = String(app.dataset.csrf || '');
  let overlay = null;
  let content = null;
  let statusNode = null;
  let saveButton = null;
  let currentSection = 'account';
  let settings = null;
  let onSaved = null;

  function iconMarkup(name) {
    return '<span class="icon icon--' + name + '" aria-hidden="true"></span>';
  }

  const sections = [
    ['account', '👤', 'Аккаунт'],
    ['profile', '🎨', 'Профиль'],
    ['privacy', '🔒', 'Приватность'],
    ['interface', '🖥', 'Интерфейс'],
    ['notifications', '🔔', 'Уведомления'],
    ['extra', iconMarkup('ui-settings'), 'Дополнительно'],
  ];

  function ensureDom() {
    if (overlay) return;
    overlay = document.createElement('section');
    overlay.className = 'profile-settings-overlay ui-overlay';
    overlay.id = 'profileSettingsOverlay';
    overlay.setAttribute('aria-hidden', 'true');
    // data-ui-overlay подключает окно к общему помощнику (public/js/ui-overlay.js):
    // он даёт фокус-трап и возврат фокуса. Esc и клик по подложке здесь уже были
    // реализованы отдельно — обработчики складываются, а close() идемпотентен.
    overlay.setAttribute('data-ui-overlay', '');
    overlay.innerHTML = [
      '<div class="profile-settings-window ui-overlay__window" role="dialog" aria-modal="true" aria-label="Настройки аккаунта">',
        '<header class="profile-settings-head ui-overlay__head">',
          '<div class="profile-settings-heading">',
            '<h2 class="ui-overlay__title">Настройки аккаунта</h2>',
            '<p>Почта, профиль, приватность и будущие пункты меню</p>',
          '</div>',
          '<button type="button" class="profile-settings-close ui-btn ui-btn--ghost" aria-label="Закрыть">&times;</button>',
        '</header>',
        '<div class="profile-settings-body">',
          '<nav class="profile-settings-nav" data-settings-nav></nav>',
          '<main class="profile-settings-content ui-overlay__body" data-settings-content></main>',
        '</div>',
        '<footer class="profile-settings-footer ui-overlay__foot">',
          '<div class="profile-settings-status" role="status" aria-live="polite" data-settings-status>Открывается по кнопке «Настройки».</div>',
          '<button type="button" class="profile-settings-action ui-btn" data-settings-cancel>Отмена</button>',
          '<button type="button" class="profile-settings-action is-primary ui-btn ui-btn--primary" data-settings-save>Сохранить</button>',
        '</footer>',
      '</div>',
    ].join('');
    document.body.appendChild(overlay);
    if (window.UiOverlay && typeof window.UiOverlay.attach === 'function') {
      window.UiOverlay.attach(overlay);
    }

    content = overlay.querySelector('[data-settings-content]');
    statusNode = overlay.querySelector('[data-settings-status]');
    saveButton = overlay.querySelector('[data-settings-save]');

    overlay.querySelector('.profile-settings-close').addEventListener('click', close);
    overlay.querySelector('[data-settings-cancel]').addEventListener('click', close);
    overlay.addEventListener('click', event => {
      if (event.target === overlay) close();
    });
    document.addEventListener('keydown', event => {
      if (!event.defaultPrevented && event.key === 'Escape' && overlay.classList.contains('is-open')) close();
    });
    saveButton.addEventListener('click', saveCurrentSection);
    renderNav();
  }

  function renderNav() {
    const nav = overlay.querySelector('[data-settings-nav]');
    nav.innerHTML = '';
    sections.forEach(([key, icon, title]) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'profile-settings-tab ui-btn ' + (key === currentSection ? 'ui-btn--primary is-active' : 'ui-btn--ghost');
      button.dataset.section = key;
      button.innerHTML = '<span></span><b></b>';
      const iconNode = button.querySelector('span');
      iconNode.setAttribute('aria-hidden', 'true');
      if (key === 'extra') iconNode.innerHTML = iconMarkup('ui-settings');
      else iconNode.textContent = icon;
      button.setAttribute('aria-pressed', String(key === currentSection));
      button.querySelector('b').textContent = title;
      button.addEventListener('click', () => {
        currentSection = key;
        renderNav();
        renderSection();
      });
      nav.appendChild(button);
    });

    const future = document.createElement('div');
    future.className = 'profile-settings-future';
    future.textContent = 'Новые разделы можно добавлять сюда без переделки окна.';
    nav.appendChild(future);
  }

  async function open(options = {}) {
    ensureDom();
    onSaved = typeof options.onSaved === 'function' ? options.onSaved : null;
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    setStatus('Загружаю настройки...');
    await load();
  }

  function close() {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  }

  async function load() {
    try {
      const response = await fetch('/api/profile/settings', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      const data = await response.json();
      if (!data || data.ok !== true) {
        setStatus(data && data.message ? data.message : 'Не удалось загрузить настройки.', 'error');
        return;
      }
      settings = data.settings || {};
      renderSection();
      setStatus('Готово');
    } catch (error) {
      console.error('Profile settings load failed:', error);
      setStatus('Не удалось загрузить настройки.', 'error');
    }
  }

  function renderSection() {
    if (!content) return;
    saveButton.hidden = currentSection === 'extra';
    if (!settings) {
      content.innerHTML = '<h3>Настройки</h3><p class="profile-settings-hint ui-field__hint">Загрузка...</p>';
      return;
    }
    if (currentSection === 'account') return renderAccount();
    if (currentSection === 'profile') return renderProfile();
    if (currentSection === 'privacy') return renderPrivacy();
    if (currentSection === 'interface') return renderInterface();
    if (currentSection === 'notifications') return renderNotifications();
    renderExtra();
  }

  function renderAccount() {
    const account = settings.account || {};
    content.innerHTML = [
      '<h3>Аккаунт</h3>',
      '<div class="profile-settings-grid">',
        row('Логин', input('current_login', account.login || '', true)),
        row('Новый логин', input('new_login', '', false, 'Смена логина пока выключена')),
        row('Текущий email', input('current_email', account.emailMasked || account.email || '', true)),
        row('Новый email', input('new_email', '', false, 'mail@example.com', 'email')),
        row('Повтор email', input('repeat_email', '', false, 'Повторите email', 'email')),
        row('Старый пароль', input('old_password', '', false, 'Старый пароль', 'password')),
        row('Новый пароль', input('new_password', '', false, 'Новый пароль', 'password')),
        row('Повтор нового пароля', input('repeat_password', '', false, 'Повторите пароль', 'password')),
        '<div class="profile-settings-hint ui-field__hint">Двухфакторная защита и история входов подготовлены как будущие пункты.</div>',
      '</div>',
    ].join('');
  }

  function renderProfile() {
    const profile = settings.profile || {};
    content.innerHTML = [
      '<h3>Профиль</h3>',
      '<div class="profile-settings-grid">',
        row('Аватар', input('avatar_upload', 'Загрузка изображения будет подключена после проверки форматов.', true), 'is-wide'),
        row('Описание профиля', textarea('description', profile.description || '', 500), 'is-wide'),
        row('Фон профиля', select('profile_background', settings.options?.backgrounds || [], profile.background || 'classic')),
        row('Рамка профиля', select('profile_frame', settings.options?.frames || [], profile.frame || 'classic')),
        row('Титул', select('title', settings.options?.titles || [], profile.title || profile.rankTitle || '')),
      '</div>',
    ].join('');
    const textareaNode = content.querySelector('[name="description"]');
    const hint = document.createElement('div');
    hint.className = 'profile-settings-hint ui-field__hint';
    const updateHint = () => { hint.textContent = 'Символов: ' + textareaNode.value.length + ' / 500'; };
    textareaNode.addEventListener('input', updateHint);
    textareaNode.parentElement.appendChild(hint);
    updateHint();
  }

  function renderPrivacy() {
    content.innerHTML = [
      '<h3>Приватность</h3>',
      '<div class="profile-settings-grid">',
        toggle('show_online', 'Показывать онлайн', settings.showOnline !== false),
        toggle('allow_pm', 'Принимать ЛС', settings.allowPm !== false),
        toggle('allow_friend_requests', 'Разрешить заявки в друзья', settings.allowFriendRequests !== false),
        toggle('show_gifts', 'Показывать подарки', settings.showGifts !== false),
        toggle('show_achievements', 'Показывать достижения', settings.showAchievements !== false),
        toggle('show_party_public', 'Показывать покемонов в тренер-карте', settings.showPartyPublic !== false),
      '</div>',
    ].join('');
  }

  function renderInterface() {
    content.innerHTML = [
      '<h3>Интерфейс</h3>',
      '<div class="profile-settings-grid">',
        row('Тема интерфейса', select('theme', settings.options?.themes || [], settings.theme || 'auto')),
        row('Размер интерфейса', select('ui_size', settings.options?.uiSizes || [], settings.uiSize || 'normal')),
        toggle('animations', 'Анимации', settings.animations !== false),
        toggle('sounds', 'Звуки интерфейса', settings.sounds !== false),
      '</div>',
      '<div class="profile-settings-hint ui-field__hint">Тема, размер интерфейса и анимации применяются после сохранения. Звуки пока не влияют на игру: звуковой системы ещё нет.</div>',
    ].join('');
  }

  function renderNotifications() {
    content.innerHTML = [
      '<h3>Уведомления</h3>',
      '<div class="profile-settings-columns">',
        toggle('notify_messages', 'Сообщения', settings.notifyMessages !== false),
        toggle('notify_friends', 'Друзья', settings.notifyFriends !== false),
        toggle('notify_gifts', 'Подарки', settings.notifyGifts !== false),
        toggle('notify_clan', 'Клан', settings.notifyClan !== false),
        toggle('notify_system', 'Системные уведомления', settings.notifySystem !== false),
      '</div>',
    ].join('');
  }

  function renderExtra() {
    content.innerHTML = [
      '<h3>Дополнительно</h3>',
      '<div class="ui-empty">',
        '<div class="profile-settings-hint ui-field__hint">Здесь зарезервировано место под Telegram, Discord, VK, Google Authenticator, историю входов, активные сессии, список устройств, настройку блоков тренер-карты и персональные цвета профиля.</div>',
      '</div>',
    ].join('');
  }

  async function saveCurrentSection() {
    if (!settings || currentSection === 'extra') return;
    saveButton.disabled = true;
    saveButton.classList.add('is-loading');
    setStatus('Сохраняю...');
    try {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('section', currentSection);
      collectFields(body);
      const response = await fetch('/api/profile/settings', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body,
      });
      const data = await response.json();
      if (!data || data.ok !== true) {
        setStatus(data && data.message ? data.message : 'Не удалось сохранить.', 'error');
        return;
      }
      settings = data.settings || settings;
      setStatus('✓ Настройки успешно сохранены', 'ok');
      renderSection();
      syncToolbar();
      window.dispatchEvent(new CustomEvent('profile-settings:saved', { detail: { settings } }));
      if (onSaved) onSaved(settings);
    } catch (error) {
      console.error('Profile settings save failed:', error);
      setStatus('Ошибка сохранения настроек.', 'error');
    } finally {
      saveButton.disabled = false;
      saveButton.classList.remove('is-loading');
    }
  }

  function collectFields(body) {
    content.querySelectorAll('input, select, textarea').forEach(field => {
      if (!field.name) return;
      if (field.type === 'checkbox') {
        body.set(field.name, field.checked ? '1' : '0');
      } else if (!field.disabled && !field.readOnly) {
        body.set(field.name, field.value);
      }
    });
  }

  function syncToolbar() {
    const partyToggle = document.getElementById('profileShowPartyToggle');
    if (partyToggle && settings) {
      partyToggle.checked = settings.showPartyPublic !== false;
    }
  }

  function setStatus(text, type = '') {
    if (!statusNode) return;
    statusNode.textContent = text || '';
    statusNode.classList.toggle('is-ok', type === 'ok');
    statusNode.classList.toggle('is-error', type === 'error');
  }

  function row(label, field, extra = '') {
    // Подпись связывается с полем через id/for: раньше label и input жили рядом,
    // но программно связаны не были, и скринридер не называл поля.
    const nameMatch = String(field).match(/name="([^"]+)"/);
    if (nameMatch && !String(field).includes('id="')) {
      const fieldId = 'ps-' + nameMatch[1];
      const linked = String(field).replace(/^<(\w+)/, '<$1 id="' + fieldId + '"');
      return '<div class="profile-settings-row ui-field ' + extra + '"><label class="ui-field__label" for="' + fieldId + '">' + escapeHtml(label) + '</label><div>' + linked + '</div></div>';
    }

    return '<div class="profile-settings-row ui-field ' + extra + '"><label class="ui-field__label">' + escapeHtml(label) + '</label><div>' + field + '</div></div>';
  }

  function input(name, value, readonly = false, placeholder = '', type = 'text') {
    return '<input class="profile-settings-input ui-field__control" name="' + escapeHtml(name) + '" type="' + escapeHtml(type) + '" value="' + escapeHtml(value) + '"' + (readonly ? ' readonly' : '') + ' placeholder="' + escapeHtml(placeholder) + '">';
  }

  function textarea(name, value, maxlength) {
    return '<textarea class="profile-settings-textarea ui-field__control" name="' + escapeHtml(name) + '" maxlength="' + Number(maxlength || 500) + '">' + escapeHtml(value) + '</textarea>';
  }

  function select(name, options, value) {
    const list = Array.isArray(options) ? options : [];
    return '<select class="profile-settings-select ui-field__control" name="' + escapeHtml(name) + '">' + list.map(option => {
      const optionValue = String(option.value ?? '');
      const label = String(option.label ?? optionValue);
      return '<option value="' + escapeHtml(optionValue) + '"' + (optionValue === String(value ?? '') ? ' selected' : '') + '>' + escapeHtml(label) + '</option>';
    }).join('') + '</select>';
  }

  function toggle(name, label, checked) {
    return '<label class="profile-settings-toggle"><span>' + escapeHtml(label) + '</span><input type="checkbox" name="' + escapeHtml(name) + '"' + (checked ? ' checked' : '') + '></label>';
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    })[char]);
  }

  window.ProfileSettingsModal = {
    open,
    close,
  };
}());
