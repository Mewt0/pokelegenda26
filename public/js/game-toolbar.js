(function () {
  const tools = document.getElementById('gameToolsMenu');
  const button = document.getElementById('gameToolsBtn');
  const dropdown = document.getElementById('gameToolsDropdown');
  const bugButton = document.getElementById('bugReportBtn');
  const partyToggle = document.getElementById('profileShowPartyToggle');
  const privacyStatus = document.getElementById('profilePrivacyStatus');
  const world = document.querySelector('.world');
  const csrf = world ? String(world.dataset.csrf || '') : '';

  if (!tools || !button || !dropdown) return;

  function setOpen(open) {
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    dropdown.classList.toggle('is-open', open);
    dropdown.setAttribute('aria-hidden', open ? 'false' : 'true');
  }

  function openBugReport() {
    const overlay = document.getElementById('bugReportOverlay');
    if (!overlay) return;
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.getElementById('bugReportTitle')?.focus();
  }

  function setPrivacyStatus(text) {
    if (privacyStatus) privacyStatus.textContent = text || '';
  }

  async function loadProfilePrivacy() {
    if (!partyToggle) return;
    try {
      const response = await fetch('/api/profile/settings', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      const data = await response.json();
      if (!data || data.ok !== true) return;
      partyToggle.checked = data.settings && data.settings.showPartyPublic !== false;
      setPrivacyStatus(partyToggle.checked ? 'Команда видна.' : 'Команда скрыта.');
    } catch (error) {
      setPrivacyStatus('Не удалось загрузить настройку.');
    }
  }

  async function saveProfilePrivacy(showPartyPublic) {
    if (!partyToggle) return;
    partyToggle.disabled = true;
    setPrivacyStatus('Сохраняю...');
    try {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('show_party_public', showPartyPublic ? '1' : '0');
      const response = await fetch('/api/profile/settings', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body,
      });
      const data = await response.json();
      if (!data || data.ok !== true) {
        partyToggle.checked = !showPartyPublic;
        setPrivacyStatus(data && data.message ? data.message : 'Не удалось сохранить.');
        return;
      }
      partyToggle.checked = data.settings && data.settings.showPartyPublic !== false;
      setPrivacyStatus(data.message || (partyToggle.checked ? 'Команда видна.' : 'Команда скрыта.'));
    } catch (error) {
      partyToggle.checked = !showPartyPublic;
      setPrivacyStatus('Ошибка сохранения.');
    } finally {
      partyToggle.disabled = false;
    }
  }

  button.addEventListener('click', event => {
    event.preventDefault();
    event.stopImmediatePropagation();
    setOpen(button.getAttribute('aria-expanded') !== 'true');
  }, true);

  bugButton?.addEventListener('click', event => {
    event.preventDefault();
    event.stopImmediatePropagation();
    setOpen(false);
    openBugReport();
  }, true);

  partyToggle?.addEventListener('change', event => {
    event.stopPropagation();
    saveProfilePrivacy(partyToggle.checked);
  });

  document.addEventListener('click', event => {
    if (!tools.contains(event.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      setOpen(false);
    }
  });

  loadProfilePrivacy();
})();
