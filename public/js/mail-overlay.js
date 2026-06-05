(() => {
  const roots = [...document.querySelectorAll('[data-mail-root]')];
  if (roots.length === 0) return;

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[char]));

  const normalize = (value) => String(value || '').trim().toLowerCase();
  const folderLabels = {
    inbox: 'Входящее',
    sent: 'Исходящее',
    archive: 'Архив',
  };

  async function readJson(response) {
    const text = await response.text();
    try {
      const data = JSON.parse(text);
      return data && typeof data === 'object' ? data : { ok: false, message: 'Некорректный ответ сервера.' };
    } catch (error) {
      if (response.status === 401) return { ok: false, message: 'Нужно войти в игру.' };
      if (response.status === 403) return { ok: false, message: 'Нет доступа.' };
      if (response.status === 419) return { ok: false, message: 'Сессия устарела. Обновите страницу.' };
      return { ok: false, message: 'Сервер вернул некорректный ответ.' };
    }
  }

  function createMail(root) {
    const overlay = root.closest('[data-mail-overlay]');
    const mode = root.dataset.mode || (overlay ? 'overlay' : 'standalone');
    const csrf = root.dataset.csrf || document.querySelector('.world[data-csrf]')?.dataset.csrf || '';
    const state = {
      tab: 'inbox',
      query: '',
      selectedKey: '',
      inbox: [],
      sent: [],
      archive: [],
      unread: 0,
      loaded: false,
      loading: false,
    };
    const els = {
      unread: root.querySelector('[data-mail-unread]'),
      compose: root.querySelector('[data-mail-compose]'),
      recipient: root.querySelector('[data-mail-recipient]'),
      tabs: root.querySelector('[data-mail-tabs]'),
      counts: [...root.querySelectorAll('[data-mail-count]')],
      search: root.querySelector('[data-mail-search]'),
      refresh: root.querySelector('[data-mail-refresh]'),
      list: root.querySelector('[data-mail-list]'),
      preview: root.querySelector('[data-mail-preview]'),
      status: root.querySelector('[data-mail-status]'),
      closeButtons: [...root.querySelectorAll('[data-mail-close]')],
    };

    if (els.recipient && root.dataset.prefillRecipient) {
      els.recipient.value = root.dataset.prefillRecipient;
    }

    function setStatus(text, bad = false) {
      if (!els.status) return;
      els.status.textContent = text || 'Готово';
      els.status.classList.toggle('is-bad', !!bad);
    }

    function rowKey(folder, row) {
      return `${folder}:${Number(row.id || 0)}`;
    }

    function currentRows() {
      return Array.isArray(state[state.tab]) ? state[state.tab] : [];
    }

    function allRows() {
      return {
        inbox: state.inbox,
        sent: state.sent,
        archive: state.archive,
      };
    }

    function displayText(value) {
      const raw = String(value ?? '').trim();
      if (!raw) return '';
      return repairMojibake(raw);
    }

    function repairMojibake(value) {
      if (!/[РС][\u0400-\u04FF]/.test(value) && !/[ÐÑ]/.test(value)) {
        return value;
      }
      const candidates = [];
      try {
        const bytes = Uint8Array.from([...value].map((char) => char.charCodeAt(0) & 255));
        candidates.push(new TextDecoder('utf-8', { fatal: false }).decode(bytes));
      } catch (error) {}
      try {
        candidates.push(decodeURIComponent(escape(value)));
      } catch (error) {}
      const best = candidates
        .filter((candidate) => candidate && candidate !== value)
        .sort((a, b) => scoreReadable(b) - scoreReadable(a))[0];
      return best && scoreReadable(best) > scoreReadable(value) ? best : value;
    }

    function scoreReadable(value) {
      const cyr = (value.match(/[А-Яа-яЁё]/g) || []).length;
      const moj = (value.match(/[ÐÑР][\u0400-\u04FF]?/g) || []).length;
      const bad = (value.match(/[�]/g) || []).length;
      return cyr * 3 - moj * 4 - bad * 10;
    }

    function peerLabel(folder, row) {
      if (folder === 'sent') {
        return displayText(row.recipient_login) || (Number(row.users || 0) > 0 ? `Игрок #${Number(row.users)}` : 'Система');
      }
      if (folder === 'archive') {
        const archiveFolder = String(row.folder || 'inbox');
        if (archiveFolder === 'sent') {
          return displayText(row.recipient_login) || (Number(row.users || 0) > 0 ? `Игрок #${Number(row.users)}` : 'Система');
        }
      }
      return displayText(row.sender_login) || (Number(row.inputusers || 0) > 0 ? `Игрок #${Number(row.inputusers)}` : 'Система');
    }

    function peerPrefix(folder, row) {
      if (folder === 'sent') return 'Кому';
      if (folder === 'archive' && String(row.folder || 'inbox') === 'sent') return 'Кому';
      return 'От';
    }

    function filteredRows() {
      const q = normalize(state.query);
      return currentRows().filter((row) => {
        if (!q) return true;
        const folder = state.tab;
        return normalize([
          displayText(row.tema),
          displayText(row.text),
          peerLabel(folder, row),
          row.id,
          row.date,
        ].join(' ')).includes(q);
      });
    }

    function selectedRow() {
      const rows = allRows();
      for (const [folder, list] of Object.entries(rows)) {
        const row = list.find((item) => rowKey(folder, item) === state.selectedKey);
        if (row) return { folder, row };
      }
      return null;
    }

    function renderCounts() {
      if (els.unread) {
        els.unread.textContent = String(Number(state.unread || 0));
      }
      els.counts.forEach((node) => {
        const key = node.dataset.mailCount;
        node.textContent = String((state[key] || []).length);
      });
    }

    function renderTabs() {
      root.querySelectorAll('[data-mail-tab]').forEach((button) => {
        button.classList.toggle('is-active', button.dataset.mailTab === state.tab);
      });
    }

    function renderList() {
      if (!els.list) return;
      renderCounts();
      renderTabs();
      const rows = filteredRows();
      if (rows.length === 0) {
        els.list.innerHTML = `<div class="mail-empty-state">${state.query ? 'Письма не найдены.' : 'В этой вкладке пока пусто.'}</div>`;
        renderPreview();
        return;
      }
      if (!selectedRow()) {
        state.selectedKey = rowKey(state.tab, rows[0]);
      }
      els.list.innerHTML = rows.map((row) => {
        const key = rowKey(state.tab, row);
        const subject = displayText(row.tema) || 'Без темы';
        const text = displayText(row.text);
        const unread = state.tab === 'inbox' && Number(row.active || 0) === 1;
        const peer = peerLabel(state.tab, row);
        return `<button type="button" class="mail-card${unread ? ' is-unread' : ''}${key === state.selectedKey ? ' is-selected' : ''}" data-mail-select="${escapeHtml(key)}">
          <span>
            <b>${escapeHtml(subject)}</b>
            <small>${escapeHtml(peerPrefix(state.tab, row))}: ${escapeHtml(peer)} · #${Number(row.id || 0)} · ${escapeHtml(String(row.date || ''))}</small>
          </span>
          <i class="mail-card-tag">${unread ? 'NEW' : escapeHtml(folderLabels[state.tab] || 'Письмо')}</i>
          <p>${escapeHtml(text)}</p>
        </button>`;
      }).join('');
      renderPreview();
    }

    function renderPreview() {
      if (!els.preview) return;
      const selected = selectedRow();
      if (!selected) {
        els.preview.innerHTML = '<div class="mail-empty-state">Выберите письмо из списка.</div>';
        return;
      }
      const { folder, row } = selected;
      const subject = displayText(row.tema) || 'Без темы';
      const text = displayText(row.text) || 'Без текста.';
      const peer = peerLabel(folder, row);
      const isInbox = folder === 'inbox';
      const unread = isInbox && Number(row.active || 0) === 1;
      els.preview.innerHTML = `<article class="mail-preview-card">
        <div class="mail-preview-meta">
          <span>#${Number(row.id || 0)}</span>
          <span>${escapeHtml(String(row.date || ''))}</span>
          <span>${escapeHtml(peerPrefix(folder, row))}: ${escapeHtml(peer)}</span>
        </div>
        <h3>${escapeHtml(subject)}</h3>
        <div class="mail-preview-text">${escapeHtml(text)}</div>
        <div class="mail-preview-actions">
          ${unread ? '<button type="button" data-mail-action="read">Прочитано</button>' : ''}
          ${folder !== 'archive' ? '<button type="button" class="danger" data-mail-action="delete">В архив</button>' : ''}
          <button type="button" data-mail-action="reply">Ответить</button>
        </div>
      </article>`;
    }

    async function load(quiet = false) {
      if (state.loading) return;
      state.loading = true;
      if (!quiet) setStatus('Загружаю почту...');
      const data = await fetch('/api/messages', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      }).then(readJson);
      state.loading = false;
      if (!data.ok) {
        setStatus(data.message || 'Не удалось загрузить почту.', true);
        return;
      }
      state.inbox = Array.isArray(data.messages) ? data.messages : [];
      state.sent = Array.isArray(data.sent) ? data.sent : [];
      state.archive = Array.isArray(data.archive) ? data.archive : [];
      state.unread = Number(data.unread || 0);
      state.loaded = true;
      if (!selectedRow()) {
        const first = currentRows()[0] || state.inbox[0] || state.sent[0] || state.archive[0] || null;
        state.selectedKey = first ? rowKey(state.tab, first) : '';
      }
      renderList();
      setStatus('Готово');
    }

    async function post(url, fields) {
      const form = new URLSearchParams();
      form.set('_csrf', csrf);
      Object.entries(fields).forEach(([key, value]) => form.set(key, value));
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: form,
      });
      return readJson(response);
    }

    async function submitCompose(event) {
      event.preventDefault();
      if (!els.compose) return;
      const submit = els.compose.querySelector('button[type="submit"]');
      if (submit) submit.disabled = true;
      const payload = Object.fromEntries(new FormData(els.compose).entries());
      const data = await post('/api/messages/send', payload);
      setStatus(data.message || (data.ok ? 'Письмо отправлено.' : 'Ошибка отправки.'), !data.ok);
      if (data.ok) {
        els.compose.reset();
        await load(true);
        state.tab = 'sent';
        state.selectedKey = data.id ? `sent:${Number(data.id)}` : '';
        renderList();
      }
      if (submit) submit.disabled = false;
    }

    async function mutateSelected(action) {
      const selected = selectedRow();
      if (!selected) return;
      if (action === 'reply') {
        const peer = peerLabel(selected.folder, selected.row);
        if (els.recipient) els.recipient.value = peer;
        const subjectInput = els.compose?.querySelector('[name="subject"]');
        if (subjectInput) {
          const subject = displayText(selected.row.tema) || '';
          subjectInput.value = subject.toLowerCase().startsWith('re:') ? subject : `Re: ${subject}`;
        }
        els.recipient?.focus();
        setStatus('Получатель подставлен для ответа.');
        return;
      }
      const endpoint = action === 'delete' ? '/api/messages/delete' : '/api/messages/read';
      const data = await post(endpoint, { id: Number(selected.row.id || 0) });
      setStatus(data.message || (data.ok ? 'Готово.' : 'Ошибка.'), !data.ok);
      if (data.ok) {
        await load(true);
      }
    }

    function open(options = {}) {
      if (options.recipient && els.recipient) {
        els.recipient.value = String(options.recipient);
      }
      if (overlay) {
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-mail-overlay');
      }
      if (!state.loaded) {
        load();
      } else {
        renderList();
      }
      window.setTimeout(() => {
        if (options.recipient && els.compose) {
          els.compose.querySelector('[name="subject"]')?.focus();
        } else {
          els.search?.focus();
        }
      }, 50);
    }

    function close() {
      if (!overlay) return;
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('has-mail-overlay');
      setStatus('Готово');
    }

    els.compose?.addEventListener('submit', submitCompose);
    els.tabs?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-mail-tab]');
      if (!button) return;
      state.tab = button.dataset.mailTab || 'inbox';
      state.selectedKey = '';
      renderList();
    });
    els.search?.addEventListener('input', () => {
      state.query = els.search.value || '';
      renderList();
    });
    els.refresh?.addEventListener('click', () => load());
    els.list?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-mail-select]');
      if (!button) return;
      state.selectedKey = button.dataset.mailSelect || '';
      renderList();
    });
    els.preview?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-mail-action]');
      if (!button) return;
      mutateSelected(button.dataset.mailAction || '');
    });
    els.closeButtons.forEach((button) => button.addEventListener('click', close));
    overlay?.addEventListener('click', (event) => {
      if (event.target === overlay) close();
    });

    if (mode !== 'overlay') {
      load(true);
    }

    return { open, close, load, root, overlay };
  }

  const mails = roots.map(createMail).filter(Boolean);
  const overlayMail = mails.find((mail) => mail.overlay);

  function recipientFromHref(href) {
    try {
      const url = new URL(href, window.location.origin);
      if (url.origin !== window.location.origin || url.pathname !== '/game/messages') return '';
      return url.searchParams.get('mail_to') || url.searchParams.get('to') || '';
    } catch (error) {
      return '';
    }
  }

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-open-mail], a[href^="/game/messages"], a[href*="/game/messages?"]');
    if (!trigger || !overlayMail) return;
    const href = trigger.getAttribute('href') || '';
    const recipient = recipientFromHref(href);
    event.preventDefault();
    overlayMail.open({ recipient });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      overlayMail?.close();
    }
  });

  window.GameMailOverlay = {
    open: (options = {}) => overlayMail?.open(options),
    close: () => overlayMail?.close(),
    reload: () => mails.forEach((mail) => mail.load(true)),
  };
})();
