(() => {
  'use strict';

  const FLAG_KEY = 'admin.grant.v2';
  const PANEL_ID = 'adminItemGrantV2';
  const API = {
    settings: '/api/admin/settings',
    preview: '/api/admin/grants/items/preview',
    confirm: '/api/admin/grants/items/confirm'
  };

  let settingsPromise = null;
  const boundInputs = new WeakSet();
  const state = {
    panel: null,
    refs: null,
    csrf: '',
    stage: 'idle',
    sourceKey: '',
    ticket: '',
    normalized: null,
    summary: null,
    frozen: null,
    gen: 0,
    abort: null,
    handlePending: false,
    previewData: null,
    receiptData: null,
    receiptSummary: null,
    reasonValue: '',
    ackChecked: false,
    statusText: '',
    statusTone: '',
    errorMessage: ''
  };

  function node(tag, className, text) {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (text !== undefined && text !== null) el.textContent = String(text);
    return el;
  }

  function isEnabled() {
    if (!settingsPromise) {
      settingsPromise = fetch(API.settings, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' }
      }).then(response => {
        if (!response.ok) return false;
        return response.json().then(payload => {
          const settings = payload && payload.ok === true ? payload.settings : null;
          if (!settings || typeof settings !== 'object') return false;
          return String(settings[FLAG_KEY]) === '1';
        });
      }).catch(() => false);
    }
    return settingsPromise;
  }

  function refsFrom(panel) {
    return {
      panel,
      status: panel.querySelector('#aigv2Status'),
      error: panel.querySelector('#aigv2Error'),
      preview: panel.querySelector('.aigv2-preview'),
      facts: panel.querySelector('.aigv2-facts'),
      warn: panel.querySelector('.aigv2-warnings'),
      expiry: panel.querySelector('.aigv2-expiry'),
      confirm: panel.querySelector('.aigv2-confirm'),
      reason: panel.querySelector('#aigv2Reason'),
      reasonCount: panel.querySelector('#aigv2ReasonCount'),
      ack: panel.querySelector('#aigv2Ack'),
      confirmBtn: panel.querySelector('#aigv2Confirm'),
      retryBtn: panel.querySelector('#aigv2Retry'),
      receipt: panel.querySelector('#aigv2Receipt')
    };
  }

  function buildPanel() {
    const panel = node('section', 'aigv2');
    panel.id = PANEL_ID;
    panel.setAttribute('aria-labelledby', 'aigv2Title');

    const title = node('h3', 'aigv2-title', 'Выдача предмета v2');
    title.id = 'aigv2Title';
    panel.appendChild(title);
    panel.appendChild(node('p', 'aigv2-note', 'Только постоянные предметы. Временные выдачи по-прежнему оформляются старой формой.'));

    const status = node('p', 'aigv2-status');
    status.id = 'aigv2Status';
    status.hidden = true;
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    status.setAttribute('aria-atomic', 'true');
    panel.appendChild(status);

    const error = node('div', 'aigv2-error');
    error.id = 'aigv2Error';
    error.hidden = true;
    error.setAttribute('role', 'alert');
    panel.appendChild(error);

    const preview = node('div', 'aigv2-preview');
    preview.hidden = true;
    preview.appendChild(node('h4', 'aigv2-subtitle', 'Проверьте выдачу'));
    preview.appendChild(node('dl', 'aigv2-facts'));
    const warn = node('div', 'aigv2-warnings');
    warn.hidden = true;
    preview.appendChild(warn);
    preview.appendChild(node('p', 'aigv2-expiry'));
    panel.appendChild(preview);

    const confirm = node('div', 'aigv2-confirm');
    confirm.hidden = true;
    const reasonLabel = node('label', 'aigv2-label', 'Причина выдачи (8–500 символов)');
    reasonLabel.htmlFor = 'aigv2Reason';
    confirm.appendChild(reasonLabel);
    const reason = node('textarea', 'aigv2-reason');
    reason.id = 'aigv2Reason';
    reason.rows = 3;
    reason.maxLength = 500;
    reason.minLength = 8;
    reason.setAttribute('aria-describedby', 'aigv2ReasonHint aigv2ReasonCount');
    confirm.appendChild(reason);
    const hint = node('p', 'aigv2-hint', 'Причина сохраняется в аудите. Не указывайте пароли, токены и другие секреты.');
    hint.id = 'aigv2ReasonHint';
    confirm.appendChild(hint);
    const count = node('p', 'aigv2-count', '0 / 500');
    count.id = 'aigv2ReasonCount';
    count.setAttribute('aria-live', 'polite');
    confirm.appendChild(count);
    const ackLabel = node('label', 'aigv2-check');
    const ack = node('input');
    ack.type = 'checkbox';
    ack.id = 'aigv2Ack';
    ackLabel.appendChild(ack);
    ackLabel.appendChild(node('span', '', 'Подтверждаю точность получателя, предмета и количества.'));
    confirm.appendChild(ackLabel);

    const actions = node('div', 'aigv2-actions');
    const confirmBtn = node('button', 'aigv2-btn aigv2-btn--primary', 'Подтвердить выдачу');
    confirmBtn.type = 'button';
    confirmBtn.id = 'aigv2Confirm';
    confirmBtn.disabled = true;
    const retryBtn = node('button', 'aigv2-btn', 'Повторить ту же операцию');
    retryBtn.type = 'button';
    retryBtn.id = 'aigv2Retry';
    retryBtn.hidden = true;
    actions.appendChild(confirmBtn);
    actions.appendChild(retryBtn);
    confirm.appendChild(actions);
    panel.appendChild(confirm);

    const receipt = node('div', 'aigv2-receipt');
    receipt.id = 'aigv2Receipt';
    receipt.hidden = true;
    receipt.tabIndex = -1;
    receipt.setAttribute('role', 'status');
    receipt.setAttribute('aria-live', 'polite');
    panel.appendChild(receipt);

    return panel;
  }

  function ensurePanel(root) {
    if (!root) return null;
    let panel = root.querySelector('#' + PANEL_ID);
    if (!panel) {
      panel = buildPanel();
      root.appendChild(panel);
      state.panel = panel;
      state.refs = refsFrom(panel);
      bindControls();
      restorePanel();
      return panel;
    }
    if (state.panel !== panel) {
      state.panel = panel;
      state.refs = refsFrom(panel);
      bindControls();
      restorePanel();
    }
    return panel;
  }

  function restorePanel() {
    const refs = state.refs;
    if (!refs) return;
    refs.reason.value = state.reasonValue;
    refs.ack.checked = state.ackChecked;
    renderAll();
  }

  function renderAll() {
    const refs = state.refs;
    if (!refs) return;
    if (state.previewData) renderPreviewInto(refs, state.previewData);
    if (state.receiptData) renderReceiptInto(refs, state.receiptData);
    renderStatus();
    renderError();
    sync();
  }

  function bindControls() {
    const refs = state.refs;
    if (!refs || !refs.panel || !refs.reason || !refs.ack || !refs.confirmBtn || !refs.retryBtn) return;
    if (refs.panel.dataset.aigv2Bound === '1') {
      bindSourceInputs();
      return;
    }
    refs.panel.dataset.aigv2Bound = '1';
    refs.reason.addEventListener('input', sync);
    refs.ack.addEventListener('change', sync);
    refs.confirmBtn.addEventListener('click', onConfirm);
    refs.retryBtn.addEventListener('click', onRetry);
    refs.reason.addEventListener('keydown', event => {
      if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault();
        if (!refs.confirmBtn.disabled) onConfirm();
      }
    });
    bindSourceInputs();
  }

  function bindSourceInputs() {
    ['#grantItemUser', '#grantItemLookup', '#grantItemCount', '#grantItemTemporary'].forEach(selector => {
      const input = document.querySelector(selector);
      if (!input || boundInputs.has(input)) return;
      boundInputs.add(input);
      input.addEventListener(input.type === 'checkbox' ? 'change' : 'input', onSourceChanged);
    });
  }

  function onSourceChanged() {
    if (state.stage === 'previewing' || state.stage === 'ready') {
      cancelPreview();
      state.stage = 'idle';
      state.ticket = '';
      state.normalized = null;
      state.summary = null;
      state.frozen = null;
      state.previewData = null;
      state.reasonValue = '';
      state.ackChecked = false;
      if (state.refs) {
        state.refs.ack.checked = false;
        state.refs.reason.value = '';
        hidePreview();
      }
      clearError();
      setStatus('Данные изменились — сформируйте новое превью.', 'warn');
      sync();
      return;
    }
    if (state.stage === 'confirming' || state.stage === 'uncertain') {
      setStatus('Незавершённая операция: сначала повторите её с той же причиной.', 'warn');
    }
  }

  function sync() {
    const refs = state.refs;
    if (!refs) return;
    const pending = state.stage === 'previewing' || state.stage === 'confirming';
    const locked = pending || state.stage === 'uncertain' || state.stage === 'done';
    refs.reason.disabled = locked;
    refs.ack.disabled = locked;
    refs.panel.setAttribute('aria-busy', pending ? 'true' : 'false');
    updateCount();
    const hasPreview = !!state.normalized;
    refs.preview.hidden = !hasPreview;
    refs.confirm.hidden = !hasPreview || state.stage === 'done';
    const reasonOk = refs.reason.value.trim().length >= 8 && refs.reason.value.trim().length <= 500;
    refs.confirmBtn.hidden = state.stage === 'uncertain';
    refs.confirmBtn.disabled = state.stage !== 'ready' || !reasonOk || !refs.ack.checked;
    refs.retryBtn.hidden = state.stage !== 'uncertain';
    refs.retryBtn.disabled = pending;
    refs.receipt.hidden = !state.receiptData;
  }

  function updateCount() {
    const refs = state.refs;
    if (!refs) return;
    refs.reasonCount.textContent = refs.reason.value.length + ' / 500';
    refs.reasonCount.classList.toggle('aigv2-count--bad', refs.reason.value.length > 0 && refs.reason.value.trim().length < 8);
  }

  function setStatus(text, tone) {
    state.statusText = text || '';
    state.statusTone = tone || '';
    renderStatus();
  }

  function renderStatus() {
    const refs = state.refs;
    if (!refs) return;
    refs.status.textContent = state.statusText;
    refs.status.hidden = !state.statusText;
    refs.status.className = 'aigv2-status' + (state.statusTone ? ' aigv2-status--' + state.statusTone : '');
  }

  function showError(text) {
    state.errorMessage = text || '';
    renderError();
  }

  function renderError() {
    const refs = state.refs;
    if (!refs) return;
    refs.error.textContent = state.errorMessage;
    refs.error.hidden = !state.errorMessage;
  }

  function clearError() {
    showError('');
  }

  function hidePreview() {
    const refs = state.refs;
    if (!refs) return;
    refs.preview.hidden = true;
    refs.facts.textContent = '';
    refs.warn.textContent = '';
    refs.warn.hidden = true;
    refs.expiry.textContent = '';
  }

  function resetOperation() {
    cancelPreview();
    state.stage = 'idle';
    state.sourceKey = '';
    state.ticket = '';
    state.normalized = null;
    state.summary = null;
    state.frozen = null;
    state.previewData = null;
    state.receiptData = null;
    state.receiptSummary = null;
    state.reasonValue = '';
    state.ackChecked = false;
    if (!state.refs) return;
    hidePreview();
    state.refs.confirm.hidden = true;
    state.refs.ack.checked = false;
    state.refs.reason.value = '';
    state.refs.receipt.textContent = '';
    state.refs.receipt.hidden = true;
    clearError();
    setStatus('');
    sync();
  }

  function cancelPreview() {
    state.gen += 1;
    if (state.abort) {
      try { state.abort.abort(); } catch (error) { /* already settled */ }
      state.abort = null;
    }
  }

  function addFact(dl, label, value) {
    dl.appendChild(node('dt', '', label));
    dl.appendChild(node('dd', '', value === null || value === undefined || value === '' ? '—' : value));
  }

  function summaryText(summary, id, name) {
    const label = summary && summary.name ? summary.name : name;
    const clean = label ? String(label) : '';
    const idText = id === null || id === undefined || id === '' ? '' : '#' + id;
    return (idText + (clean ? ' ' + clean : '')).trim() || '—';
  }

  function parseLookup(value) {
    const raw = String(value === undefined || value === null ? '' : value).trim();
    if (!raw) return '';
    const match = raw.match(/^#?(\d+)\b/);
    return match ? match[1] : raw;
  }

  function liveInputValues() {
    const userEl = document.getElementById('grantItemUser');
    const itemEl = document.getElementById('grantItemLookup');
    const quantityEl = document.getElementById('grantItemCount');
    const temporaryEl = document.getElementById('grantItemTemporary');
    const userId = parseLookup(userEl ? userEl.value : '');
    const itemId = parseLookup(itemEl ? itemEl.value : '');
    const quantity = quantityEl && String(quantityEl.value).trim() ? String(quantityEl.value).trim() : '1';
    return {
      userId,
      itemId,
      quantity,
      temporary: !!(temporaryEl && temporaryEl.checked),
      key: [userId, itemId, quantity, '0'].join('|')
    };
  }

  function formatMoment(value) {
    if (value === null || value === undefined || value === '') return '—';
    const raw = String(value).trim();
    if (!/^\d+$/.test(raw)) return raw;
    const numeric = Number(raw);
    if (!Number.isFinite(numeric) || numeric <= 0) return raw;
    const millis = numeric < 100000000000 ? numeric * 1000 : numeric;
    const date = new Date(millis);
    if (!Number.isFinite(date.getTime())) return raw;
    try {
      return new Intl.DateTimeFormat('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      }).format(date);
    } catch (error) {
      return date.toLocaleString('ru-RU');
    }
  }

  function renderPreview(data) {
    state.previewData = data;
    state.summary = { target: data.target || {}, item: data.item || {} };
    renderPreviewInto(state.refs, data);
  }

  function renderPreviewInto(refs, data) {
    if (!refs) return;
    const normalized = data.normalized || {};
    const target = data.target || {};
    const item = data.item || {};
    refs.facts.textContent = '';
    addFact(refs.facts, 'Получатель', summaryText(target, normalized.target_user_id || target.id, target.login));
    addFact(refs.facts, 'Предмет', summaryText(item, normalized.item_id || item.id, item.name));
    addFact(refs.facts, 'Количество', normalized.quantity);
    addFact(refs.facts, 'Было → станет', String(data.before_qty) + ' → ' + String(data.after_qty));
    refs.warn.textContent = '';
    refs.warn.hidden = true;
    if (Array.isArray(data.warnings) && data.warnings.length) {
      refs.warn.appendChild(node('p', 'aigv2-warn-title', 'Предупреждения'));
      const list = node('ul', 'aigv2-warn-list');
      data.warnings.forEach(warning => {
        const text = typeof warning === 'string' ? warning : (warning && (warning.message || warning.text || warning.code)) || JSON.stringify(warning);
        list.appendChild(node('li', '', text));
      });
      refs.warn.appendChild(list);
      refs.warn.hidden = false;
    }
    refs.expiry.textContent = data.expires_at ? 'Превью действительно до ' + formatMoment(data.expires_at) + '.' : '';
    refs.preview.hidden = false;
  }

  function errorText(status, payload) {
    const code = payload && payload.error ? String(payload.error) : '';
    if (status === 403) return 'Недостаточно прав для выдачи предмета.';
    if (status === 419) return 'Сессия истекла. Обновите страницу и повторите.';
    if (status === 404) return 'Выдача v2 выключена или недоступна.';
    if (status === 422) return 'Сервер отклонил данные выдачи' + (code ? ' (' + code + ').' : '.');
    if (status === 409) return 'Данные или превью устарели. Сформируйте новое превью.';
    return 'Запрос отклонён' + (code ? ' (' + code + ').' : ' (' + status + ').');
  }

  async function startPreview(snapshot) {
    cancelPreview();
    const gen = ++state.gen;
    state.stage = 'previewing';
    state.sourceKey = snapshot.key;
    state.ticket = '';
    state.normalized = null;
    state.summary = null;
    state.frozen = null;
    state.previewData = null;
    state.reasonValue = '';
    state.ackChecked = false;
    if (state.refs) {
      state.refs.ack.checked = false;
      state.refs.reason.value = '';
      hidePreview();
    }
    clearError();
    setStatus('Формируем превью…', 'info');
    sync();

    const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
    state.abort = controller;
    const body = new URLSearchParams();
    body.set('_csrf', snapshot.csrf);
    body.set('target_user_id', snapshot.userId);
    body.set('item_id', snapshot.itemId);
    body.set('quantity', snapshot.quantity);

    let response;
    try {
      response = await fetch(API.preview, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body,
        signal: controller ? controller.signal : undefined
      });
    } catch (error) {
      if (gen !== state.gen) return;
      state.stage = 'idle';
      state.abort = null;
      setStatus('');
      showError('Не удалось получить превью. Проверьте соединение и нажмите «Выдать» снова.');
      sync();
      return;
    }
    if (gen !== state.gen) return;
    state.abort = null;

    let payload = null;
    try { payload = await response.json(); } catch (error) { payload = null; }
    if (gen !== state.gen) return;

    if (liveInputValues().key !== state.sourceKey) {
      cancelPreview();
      state.stage = 'idle';
      state.normalized = null;
      state.summary = null;
      state.previewData = null;
      hidePreview();
      setStatus('Данные изменились — сформируйте новое превью.', 'warn');
      sync();
      return;
    }

    if (response.ok && payload && payload.ok === true && payload.data && payload.data.normalized && payload.data.ticket) {
      state.normalized = payload.data.normalized;
      state.ticket = String(payload.data.ticket);
      state.stage = 'ready';
      renderPreview(payload.data);
      setStatus('Превью готово. Проверьте данные и подтвердите выдачу.', 'success');
      sync();
      if (state.refs) state.refs.reason.focus();
      return;
    }

    state.stage = 'idle';
    state.normalized = null;
    state.previewData = null;
    showError(errorText(response.status, payload));
    setStatus(response.status === 422 || response.status === 409 ? 'Исправьте данные и нажмите «Выдать» ещё раз.' : '', 'warn');
    sync();
  }

  function idempotencyKey() {
    if (!window.crypto || typeof window.crypto.getRandomValues !== 'function') return '';
    const bytes = new Uint8Array(32);
    window.crypto.getRandomValues(bytes);
    let key = '';
    for (let index = 0; index < bytes.length; index += 1) {
      key += bytes[index].toString(16).padStart(2, '0');
    }
    return key;
  }

  async function onConfirm() {
    if (state.stage !== 'ready' || !state.normalized || !state.ticket) return;
    if (liveInputValues().key !== state.sourceKey) {
      onSourceChanged();
      return;
    }
    const reason = state.refs.reason.value.trim();
    if (reason.length < 8 || reason.length > 500) {
      showError('Причина должна содержать от 8 до 500 символов.');
      state.refs.reason.focus();
      return;
    }
    if (!state.refs.ack.checked) {
      showError('Подтвердите выдачу, установив флажок.');
      state.refs.ack.focus();
      return;
    }
    if (!state.frozen) {
      const key = idempotencyKey();
      if (!key) {
        showError('Браузер не поддерживает криптографический ключ идемпотентности. Выдача остановлена.');
        return;
      }
      const body = new URLSearchParams();
      body.set('_csrf', state.csrf);
      body.set('ticket', state.ticket);
      body.set('payload', JSON.stringify(state.normalized));
      body.set('reason', reason);
      body.set('confirm', 'true');
      body.set('idempotency_key', key);
      state.frozen = { key, body: body.toString() };
    }
    state.reasonValue = reason;
    state.ackChecked = true;
    await submitFrozen();
  }

  async function submitFrozen() {
    if (!state.frozen || state.stage === 'confirming') return;
    const gen = state.gen;
    state.stage = 'confirming';
    state.reasonValue = state.refs ? state.refs.reason.value : state.reasonValue;
    state.ackChecked = true;
    clearError();
    setStatus('Подтверждаем выдачу…', 'info');
    sync();

    let response;
    try {
      response = await fetch(API.confirm, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: state.frozen.body
      });
    } catch (error) {
      if (gen !== state.gen) return;
      enterUncertain();
      return;
    }
    if (gen !== state.gen) return;

    let payload = null;
    try { payload = await response.json(); } catch (error) { payload = null; }
    if (gen !== state.gen) return;

    if (response.ok && payload && payload.ok === true) {
      const data = payload.data || {};
      state.stage = 'done';
      state.ticket = '';
      state.normalized = null;
      state.frozen = null;
      state.previewData = null;
      state.reasonValue = '';
      state.ackChecked = false;
      state.receiptData = data;
      renderReceipt(data);
      setStatus('Предмет выдан.', 'success');
      sync();
      if (state.refs) state.refs.receipt.focus();
      return;
    }

    if (!(response.status >= 400 && response.status < 500)) {
      enterUncertain();
      return;
    }

    state.stage = 'idle';
    state.ticket = '';
    state.normalized = null;
    state.frozen = null;
    state.previewData = null;
    state.reasonValue = '';
    state.ackChecked = false;
    if (state.refs) {
      state.refs.ack.checked = false;
      state.refs.reason.value = '';
      hidePreview();
      state.refs.confirm.hidden = true;
    }
    showError(errorText(response.status, payload));
    setStatus(response.status === 409 || response.status === 422
      ? 'Превью сброшено: сформируйте новое, нажав «Выдать».'
      : '', 'warn');
    sync();
  }

  function enterUncertain() {
    state.stage = 'uncertain';
    state.reasonValue = state.refs ? state.refs.reason.value : state.reasonValue;
    state.ackChecked = true;
    setStatus('Результат неизвестен: связь прервана. Причина и флажок заблокированы.', 'warn');
    showError('Подтверждение не получено. Повтор отправит ту же операцию с тем же ключом идемпотентности — вторая выдача не создастся.');
    sync();
    if (state.refs) state.refs.retryBtn.focus();
  }

  function onRetry() {
    if (state.stage !== 'uncertain' || !state.frozen) return;
    clearError();
    submitFrozen();
  }

  function renderReceipt(data) {
    state.receiptSummary = state.summary;
    state.receiptData = data;
    renderReceiptInto(state.refs, data);
  }

  function renderReceiptInto(refs, data) {
    if (!refs) return;
    const normalized = data.normalized || {};
    const summary = state.receiptSummary || state.summary;
    refs.receipt.textContent = '';
    refs.receipt.appendChild(node('h4', 'aigv2-subtitle', 'Выдача подтверждена'));
    const facts = node('dl', 'aigv2-facts');
    addFact(facts, 'Receipt', data.receipt_id);
    addFact(facts, 'Correlation', data.correlation_id);
    const target = summary && summary.target ? summary.target : null;
    const item = summary && summary.item ? summary.item : null;
    addFact(facts, 'Получатель', summaryText(target, normalized.target_user_id, target && target.login));
    addFact(facts, 'Предмет', summaryText(item, normalized.item_id, item && item.name));
    addFact(facts, 'Количество', normalized.quantity);
    addFact(facts, 'Было → станет', String(data.before_qty) + ' → ' + String(data.after_qty));
    addFact(facts, 'Статус', data.result);
    if (data.completed_at || data.created_at) {
      addFact(facts, 'Завершено', formatMoment(data.completed_at || data.created_at));
    }
    refs.receipt.appendChild(facts);
    refs.receipt.hidden = false;
  }

  async function handle(options) {
    const opts = options || {};
    const enabled = await isEnabled();
    if (!enabled) return false;
    if (state.handlePending) return true;
    state.handlePending = true;
    try {
      const root = opts.root || document.querySelector('#adminDanger');
      const panel = ensurePanel(root);
      if (!panel) {
        setStatus('Не удалось открыть форму выдачи v2. Обновите страницу.', 'warn');
        return true;
      }
      bindSourceInputs();
      if (!state.refs) return true;
      state.csrf = String(opts.csrf || '');
      if (state.stage === 'confirming' || state.stage === 'uncertain') {
        setStatus(state.stage === 'uncertain'
          ? 'Сначала повторите незавершённую операцию.'
          : 'Подтверждение уже отправлено, дождитесь ответа.', 'warn');
        return true;
      }
      const live = liveInputValues();
      if (live.temporary) {
        if (state.stage === 'previewing' || state.stage === 'ready') {
          setStatus('Сначала завершите или сбросьте текущую операцию v2.', 'warn');
          return true;
        }
        setStatus('Временные предметы v2 не поддерживает — используется старая выдача.', 'warn');
        return false;
      }
      if (!live.userId || !live.itemId) {
        showError('Укажите игрока и предмет.');
        return true;
      }
      if (!/^\d+$/.test(live.quantity) || Number(live.quantity) < 1) {
        showError('Количество должно быть целым числом не меньше 1.');
        return true;
      }
      if (!state.csrf) {
        showError('Не найден CSRF-токен. Обновите страницу.');
        return true;
      }
      if (state.stage === 'ready' && state.sourceKey === live.key) {
        state.refs.reason.focus();
        return true;
      }
      await startPreview({ userId: live.userId, itemId: live.itemId, quantity: live.quantity, key: live.key, csrf: state.csrf });
      return true;
    } catch (error) {
      showError('Не удалось запустить выдачу v2. Обновите страницу и попробуйте снова.');
      return true;
    } finally {
      state.handlePending = false;
    }
  }

  window.AdminItemGrantV2 = {
    mount(root) {
      return ensurePanel(root || document.querySelector('#adminDanger'));
    },
    handle,
    isEnabled
  };
})();
