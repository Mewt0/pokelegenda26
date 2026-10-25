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
      if (!response.ok) {
        return data && typeof data === 'object' ? { ok: false, message: data.message || 'Не удалось выполнить запрос.' } : { ok: false, message: 'Не удалось выполнить запрос.' };
      }
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
      submitting: false,
      mutating: false,
      loadVersion: 0,
      controller: null,
      composePeerKey: '',
      composePeerLabel: '',
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

    // Черновик письма принадлежит получателю: при смене адресата старый текст
    // уходит в свой слот, а в поле подставляется черновик нового получателя.
    const composeDrafts = new Map();
    state.composePeerKey = normalize(els.recipient ? els.recipient.value : '');
    state.composePeerLabel = els.recipient ? String(els.recipient.value || '').trim() : '';

    function composeField(name) {
      return els.compose ? els.compose.querySelector('[name="' + name + '"]') : null;
    }

    function composePeerKeyNow() {
      const recipient = composeField('recipient');
      return normalize(recipient ? recipient.value : '');
    }

    function composePeerLabelNow() {
      const recipient = composeField('recipient');
      return recipient ? String(recipient.value || '').trim() : '';
    }

    function composeHasBody() {
      const body = composeField('text');
      return !!(body && String(body.value || '').trim() !== '');
    }

    function adoptComposePeer() {
      state.composePeerKey = composePeerKeyNow();
      state.composePeerLabel = composePeerLabelNow();
    }

    function stashComposeDraft() {
      if (!els.compose || !state.composePeerKey) return;
      const body = composeField('text');
      const text = body ? String(body.value || '') : '';
      if (text.trim() === '') composeDrafts.delete(state.composePeerKey);
      else composeDrafts.set(state.composePeerKey, text);
    }

    function restoreComposeDraft(key) {
      const body = composeField('text');
      if (body) body.value = composeDrafts.get(key) || '';
    }

    function switchComposePeer(nextValue) {
      const nextKey = normalize(nextValue);
      if (nextKey === state.composePeerKey) {
        state.composePeerLabel = String(nextValue || '').trim() || state.composePeerLabel;
        return 'same';
      }
      const previousKey = state.composePeerKey;
      const previousLabel = state.composePeerLabel || previousKey;
      const hadBody = composeHasBody();
      const unboundBody = !previousKey && hadBody;
      stashComposeDraft();
      state.composePeerKey = nextKey;
      state.composePeerLabel = String(nextValue || '').trim();
      const recipient = composeField('recipient');
      if (recipient && String(recipient.value || '') !== String(nextValue || '')) {
        recipient.value = String(nextValue || '');
      }
      if (unboundBody) {
        setStatus('Текст остался в поле — проверьте, что он адресован получателю «' + state.composePeerLabel + '».');
        return 'kept';
      }
      if (composeDrafts.has(nextKey)) {
        restoreComposeDraft(nextKey);
        setStatus('Восстановлен черновик для получателя «' + state.composePeerLabel + '».');
        return 'restored';
      }
      restoreComposeDraft(nextKey);
      if (previousKey && hadBody) {
        setStatus('Прошлый текст относился к «' + previousLabel + '» и сохранён как черновик. Поле очищено для нового получателя.');
        return 'cleared';
      }
      return 'same';
    }

    function onRecipientChanged() {
      const recipient = composeField('recipient');
      if (!recipient) return;
      if (normalize(recipient.value) === state.composePeerKey) {
        state.composePeerLabel = String(recipient.value || '').trim();
        return;
      }
      switchComposePeer(recipient.value);
    }

    function beginNewCompose() {
      if (composeHasBody()) {
        const confirmed = window.confirm('Очистить текущий черновик и начать новое письмо? Текст сохранится и вернётся при выборе того же получателя.');
        if (!confirmed) return false;
      }
      stashComposeDraft();
      if (els.compose) els.compose.reset();
      ['recipient', 'subject', 'text'].forEach((name) => {
        const field = composeField(name);
        if (field) field.value = '';
      });
      state.composePeerKey = '';
      state.composePeerLabel = '';
      return true;
    }

    function setStatus(text, bad = false) {
      if (!els.status) return;
      if (!els.status.hasAttribute('aria-live')) {
        els.status.setAttribute('role', 'status');
        els.status.setAttribute('aria-live', 'polite');
        els.status.setAttribute('aria-atomic', 'true');
      }
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
        const bytes = encodeWindows1251(value);
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

    function encodeWindows1251(value) {
      const extra = {
        'Ђ': 0x80, 'Ѓ': 0x81, '‚': 0x82, 'ѓ': 0x83, '„': 0x84, '…': 0x85, '†': 0x86, '‡': 0x87,
        '€': 0x88, '‰': 0x89, 'Љ': 0x8a, '‹': 0x8b, 'Њ': 0x8c, 'Ќ': 0x8d, 'Ћ': 0x8e, 'Џ': 0x8f,
        'ђ': 0x90, '‘': 0x91, '’': 0x92, '“': 0x93, '”': 0x94, '•': 0x95, '–': 0x96, '—': 0x97,
        '™': 0x99, 'љ': 0x9a, '›': 0x9b, 'њ': 0x9c, 'ќ': 0x9d, 'ћ': 0x9e, 'џ': 0x9f,
        ' ': 0xa0, 'Ў': 0xa1, 'ў': 0xa2, 'Ј': 0xa3, '¤': 0xa4, 'Ґ': 0xa5, '¦': 0xa6, '§': 0xa7,
        'Ё': 0xa8, '©': 0xa9, 'Є': 0xaa, '«': 0xab, '¬': 0xac, '­': 0xad, '®': 0xae, 'Ї': 0xaf,
        '°': 0xb0, '±': 0xb1, 'І': 0xb2, 'і': 0xb3, 'ґ': 0xb4, 'µ': 0xb5, '¶': 0xb6, '·': 0xb7,
        'ё': 0xb8, '№': 0xb9, 'є': 0xba, '»': 0xbb, 'ј': 0xbc, 'Ѕ': 0xbd, 'ѕ': 0xbe, 'ї': 0xbf,
      };
      return Uint8Array.from([...value].map((char) => {
        const code = char.charCodeAt(0);
        if (code <= 0x7f) return code;
        if (code >= 0x0410 && code <= 0x044f) return code - 0x0410 + 0xc0;
        if (extra[char]) return extra[char];
        return code <= 0xff ? code : 0x3f;
      }));
    }

    function scoreReadable(value) {
      const words = (value.match(/[А-Яа-яЁё]{3,}/g) || []).length;
      const moj = (value.match(/[РС][\u0400-\u04FF]|[ÐÑ]/g) || []).length;
      const bad = (value.match(/[�]/g) || []).length;
      return words * 6 - moj * 4 - bad * 10;
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
      const row = filteredRows().find((item) => rowKey(state.tab, item) === state.selectedKey);
      return row ? { folder: state.tab, row } : null;
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
        button.setAttribute('aria-pressed', String(button.dataset.mailTab === state.tab));
      });
    }

    function emptyBox(text, kind = 'empty', action = '') {
      const actionMarkup = action
        ? '<button type="button" class="ui-btn" data-mail-' + action + '>' + (action === 'retry' ? 'Повторить' : 'Сбросить поиск') + '</button>'
        : '';
      return `<div class="ui-empty mail-empty-state is-${kind}"${kind === 'error' ? ' role="alert"' : ''}><span>${escapeHtml(text)}</span>${actionMarkup}</div>`;
    }

    function renderList() {
      if (!els.list) return;
      renderCounts();
      renderTabs();
      const rows = filteredRows();
      if (rows.length === 0) {
        state.selectedKey = '';
        els.list.innerHTML = state.query
          ? emptyBox('Письма не найдены.', 'empty', 'reset')
          : emptyBox('В этой вкладке пока пусто.');
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
        return `<button type="button" aria-current="${key === state.selectedKey ? 'true' : 'false'}" class="ui-card mail-card${unread ? ' is-unread' : ''}${key === state.selectedKey ? ' is-selected' : ''}" data-mail-select="${escapeHtml(key)}">
          <span>
            <b>${escapeHtml(subject)}</b>
            <small>${escapeHtml(peerPrefix(state.tab, row))}: ${escapeHtml(peer)} · #${Number(row.id || 0)} · ${escapeHtml(String(row.date || ''))}</small>
          </span>
          <i class="mail-card-tag">${unread ? 'Новое' : escapeHtml(folderLabels[state.tab] || 'Письмо')}</i>
          <p>${escapeHtml(text)}</p>
        </button>`;
      }).join('');
      renderPreview();
    }

    function renderPreview() {
      if (!els.preview) return;
      const selected = selectedRow();
      if (!selected) {
        els.preview.innerHTML = emptyBox('Выберите письмо из списка.');
        return;
      }
      const { folder, row } = selected;
      const subject = displayText(row.tema) || 'Без темы';
      const text = displayText(row.text) || 'Без текста.';
      const peer = peerLabel(folder, row);
      const isInbox = folder === 'inbox';
      const unread = isInbox && Number(row.active || 0) === 1;
      const outgoing = folder === 'sent' || (folder === 'archive' && row.folder === 'sent');
      const canReply = outgoing || (Number(row.inputusers || 0) > 0 && normalize(peer) !== 'система');
      els.preview.innerHTML = `<article class="mail-preview-card">
        <div class="mail-preview-meta">
          <span>#${Number(row.id || 0)}</span>
          <span>${escapeHtml(String(row.date || ''))}</span>
          <span>${escapeHtml(peerPrefix(folder, row))}: ${escapeHtml(peer)}</span>
        </div>
        <h3>${escapeHtml(subject)}</h3>
        <div class="mail-preview-text">${escapeHtml(text)}</div>
        <div class="mail-preview-actions">
          ${unread ? '<button type="button" class="ui-btn" data-mail-action="read">Прочитано</button>' : ''}
          ${folder !== 'archive' ? '<button type="button" class="ui-btn ui-btn--danger" data-mail-action="delete">В архив</button>' : ''}
          ${canReply ? '<button type="button" class="ui-btn ui-btn--primary" data-mail-action="reply">' + (outgoing ? 'Написать ещё' : 'Ответить') + '</button>' : '<span class="mail-system-note">Системное письмо · ответ не требуется</span>'}
        </div>
      </article>`;
      els.preview.querySelectorAll('button').forEach((button) => { button.disabled = state.mutating; });
    }

    async function load(quiet = false) {
      const version = ++state.loadVersion;
      state.controller?.abort();
      state.controller = new AbortController();
      state.loading = true;
      root.setAttribute('aria-busy', 'true');
      if (els.refresh) els.refresh.disabled = true;
      try {
        if (!quiet) setStatus('Загружаю почту...');
        const data = await fetch('/api/messages', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
          signal: state.controller.signal,
        }).then(readJson);
        if (version !== state.loadVersion) return;
        if (!data.ok) {
          setStatus(data.message || 'Не удалось загрузить почту.', true);
          if (els.list) els.list.innerHTML = emptyBox(data.message || 'Не удалось загрузить почту.', 'error', 'retry');
          state.selectedKey = '';
          if (els.preview) els.preview.innerHTML = emptyBox('Обновите почту, чтобы открыть письмо.');
          return;
        }
        state.inbox = Array.isArray(data.messages) ? data.messages : [];
        state.sent = Array.isArray(data.sent) ? data.sent : [];
        state.archive = Array.isArray(data.archive) ? data.archive : [];
        state.unread = Number(data.unread || 0);
        state.loaded = true;
        if (!selectedRow()) {
          const first = filteredRows()[0] || null;
          state.selectedKey = first ? rowKey(state.tab, first) : '';
        }
        renderList();
        setStatus('Готово');
      } catch (error) {
        if (version !== state.loadVersion || error.name === 'AbortError') return;
        setStatus('Не удалось загрузить почту. Попробуйте ещё раз.', true);
        if (els.list) els.list.innerHTML = emptyBox('Не удалось загрузить почту. Попробуйте ещё раз.', 'error', 'retry');
      } finally {
        if (version === state.loadVersion) {
          state.loading = false;
          root.setAttribute('aria-busy', 'false');
          if (els.refresh) els.refresh.disabled = false;
        }
      }
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
      if (!els.compose || state.submitting) return;
      if (composePeerKeyNow() !== state.composePeerKey && composeHasBody()) {
        const confirmed = window.confirm('Получатель изменён вручную. Отправить текущий текст новому получателю?');
        if (!confirmed) {
          setStatus('Отправка отменена: проверьте получателя и текст.', true);
          return;
        }
      }
      adoptComposePeer();
      const submit = els.compose.querySelector('button[type="submit"]');
      state.submitting = true;
      if (submit) submit.disabled = true;
      const payload = Object.fromEntries(new FormData(els.compose).entries());
      try {
        const data = await post('/api/messages/send', payload);
        setStatus(data.message || (data.ok ? 'Письмо отправлено.' : 'Ошибка отправки.'), !data.ok);
        if (data.ok) {
          const sentKey = state.composePeerKey;
          if (sentKey) composeDrafts.delete(sentKey);
          els.compose.reset();
          const body = composeField('text');
          if (body) body.value = '';
          adoptComposePeer();
          state.tab = 'sent';
          state.query = '';
          if (els.search) els.search.value = '';
          await load(true);
          state.selectedKey = data.id ? `sent:${Number(data.id)}` : '';
          setView('reader');
          renderList();
          setStatus('Письмо отправлено.');
        }
      } catch (error) {
        setStatus('Не удалось отправить письмо. Попробуйте ещё раз.', true);
      } finally {
        state.submitting = false;
        if (submit) submit.disabled = false;
      }
    }

    async function mutateSelected(action) {
      if (state.mutating) return;
      const selected = selectedRow();
      if (!selected) return;
      if (action === 'reply') {
        setView('compose');
        const peer = peerLabel(selected.folder, selected.row);
        const outcome = switchComposePeer(peer);
        const subjectInput = els.compose?.querySelector('[name="subject"]');
        if (subjectInput) {
          const subject = displayText(selected.row.tema) || '';
          subjectInput.value = subject.toLowerCase().startsWith('re:') ? subject : `Re: ${subject}`;
        }
        els.recipient?.focus();
        if (outcome === 'same') setStatus('Получатель подставлен для ответа.');
        return;
      }
      const endpoint = action === 'delete' ? '/api/messages/delete' : '/api/messages/read';
      state.mutating = true;
      renderPreview();
      try {
        const data = await post(endpoint, { id: Number(selected.row.id || 0) });
        setStatus(data.message || (data.ok ? 'Готово.' : 'Ошибка.'), !data.ok);
        if (data.ok) await load(true);
      } catch (error) {
        setStatus('Не удалось выполнить действие. Попробуйте ещё раз.', true);
      } finally {
        state.mutating = false;
        renderPreview();
      }
    }

    function setView(view) {
      root.dataset.mailView = view;
      const composePanel = root.querySelector('.mail-compose-panel');
      if (composePanel) composePanel.hidden = view !== 'compose';
    }

    function open(options = {}) {
      if (options.recipient && els.recipient) {
        switchComposePeer(String(options.recipient));
        setView('compose');
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

    root.querySelector('[data-mail-new]')?.addEventListener('click', () => {
      if (!beginNewCompose()) return;
      setView('compose');
      els.recipient?.focus();
    });
    root.querySelector('[data-mail-cancel-compose]')?.addEventListener('click', () => {
      setView('list');
      root.querySelector('[data-mail-new]')?.focus();
    });
    root.querySelector('[data-mail-back]')?.addEventListener('click', () => {
      setView('list');
      els.list?.querySelector('[aria-current="true"]')?.focus();
    });
    overlay?.addEventListener('ui-overlay:closed', close);

    els.compose?.addEventListener('submit', submitCompose);
    els.compose?.addEventListener('input', (event) => {
      if (!event.target || event.target.name !== 'text') return;
      if (composeHasBody()) adoptComposePeer();
    });
    els.compose?.addEventListener('reset', () => {
      if (state.composePeerKey) composeDrafts.delete(state.composePeerKey);
      window.setTimeout(() => {
        adoptComposePeer();
        const body = composeField('text');
        if (body) body.value = '';
        setStatus('Поле очищено.');
      }, 0);
    });
    els.recipient?.addEventListener('change', onRecipientChanged);
    els.tabs?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-mail-tab]');
      if (!button) return;
      state.tab = button.dataset.mailTab || 'inbox';
      state.selectedKey = '';
      setView('list');
      renderList();
    });
    els.search?.addEventListener('input', () => {
      state.query = els.search.value || '';
      renderList();
    });
    els.refresh?.addEventListener('click', () => load());
    root.addEventListener('click', (event) => {
      if (event.target.closest('[data-mail-retry]')) {
        load();
        return;
      }
      if (!event.target.closest('[data-mail-reset]')) return;
      if (els.search) els.search.value = '';
      state.query = '';
      renderList();
    });
    els.list?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-mail-select]');
      if (!button) return;
      state.selectedKey = button.dataset.mailSelect || '';
      setView('reader');
      renderList();
      if (selectedRow() && state.tab === 'inbox' && Number(selectedRow().row.active) === 1) mutateSelected('read');
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
      if (root.dataset.prefillRecipient) setView('compose');
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
    window.TrainerProfileWindow?.close();
    overlayMail.open({ recipient });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      if (event.defaultPrevented) return;
      overlayMail?.close();
    }
  });

  window.GameMailOverlay = {
    open: (options = {}) => overlayMail?.open(options),
    close: () => overlayMail?.close(),
    reload: () => mails.forEach((mail) => mail.load(true)),
  };
})();
