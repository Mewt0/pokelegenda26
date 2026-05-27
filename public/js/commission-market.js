(() => {
  const fallbackCategories = [
    { key: 'all', label: 'Все' },
    { key: 'pokemon', label: 'Покемоны' },
    { key: 'egg', label: 'Яйца покемонов' },
    { key: 'currency', label: 'Валюта' },
    { key: 'evolution', label: 'Предметы эволюции' },
    { key: 'held_item', label: 'Предметы снаряжения' },
    { key: 'craft', label: 'Предметы крафта' },
    { key: 'ticket', label: 'Билеты' },
    { key: 'mega_primal', label: 'Праймал/Мега предметы' },
    { key: 'tm', label: 'TM-атаки' },
    { key: 'gift_box', label: 'Подарочные ящики' },
    { key: 'other', label: 'Прочие предметы' },
  ];

  const bannedCategoryLabels = ['зель', 'ягод', 'potion', 'berry'];
  const roots = [...document.querySelectorAll('[data-commission-root]')];
  if (roots.length === 0) return;

  const formatNumber = (value) => Number(value || 0).toLocaleString('ru-RU');
  const formatDate = (timestamp) => timestamp ? new Date(Number(timestamp) * 1000).toLocaleString('ru-RU') : '';
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[char]));
  const normalizeString = (value) => String(value || '').trim().toLowerCase();

  async function readJson(response) {
    const text = await response.text();
    try {
      const data = JSON.parse(text);
      if (data && typeof data === 'object') {
        return data;
      }
      return { ok: false, message: 'Некорректный ответ сервера.' };
    } catch (error) {
      if (response.status === 401) {
        return { ok: false, message: 'Нужно войти в игру.' };
      }
      if (response.status === 403) {
        return { ok: false, message: 'Нет доступа.' };
      }
      if (response.status === 419) {
        return { ok: false, message: 'Сессия устарела. Обновите страницу.' };
      }
      return { ok: false, message: 'Сервер вернул некорректный ответ.' };
    }
  }

  function createMarket(root) {
    const overlay = root.closest('[data-commission-overlay]');
    const mode = root.dataset.mode || (overlay ? 'overlay' : 'standalone');
    const csrf = root.dataset.csrf || document.querySelector('.world[data-csrf]')?.dataset.csrf || '';
    const params = new URLSearchParams(window.location.search);
    const state = {
      category: params.get('category') || 'all',
      sort: 'new',
      q: '',
      view: 'lots',
      lots: [],
      my: { active: [], history: [], lots: [] },
      sellable: { items: [], pokemon: [], eggs: [] },
      categories: fallbackCategories,
      loadedLots: false,
      loadedSellable: false,
      loadedMy: false,
      searchTimer: 0,
      sellableQ: '',
    };

    const els = {
      sideTitle: root.querySelector('[data-commission-side-title]'),
      categories: root.querySelector('[data-commission-categories]'),
      sellablePanel: root.querySelector('[data-commission-sellable-panel]'),
      sellableGrid: root.querySelector('[data-commission-sellable-grid]'),
      sellableSearch: root.querySelector('[data-commission-sellable-search]'),
      tabs: [...root.querySelectorAll('[data-commission-view]')],
      viewTitle: root.querySelector('[data-commission-view-title]'),
      sort: root.querySelector('[data-commission-sort]'),
      search: root.querySelector('[data-commission-search]'),
      reset: root.querySelector('[data-commission-reset]'),
      refresh: root.querySelector('[data-commission-refresh]'),
      headRow: root.querySelector('[data-commission-head-row]'),
      lots: root.querySelector('[data-commission-lots]'),
      empty: root.querySelector('[data-commission-empty]'),
      status: root.querySelector('[data-commission-status]'),
      preview: root.querySelector('[data-commission-preview]'),
      form: root.querySelector('[data-commission-sell-form]'),
      sellType: root.querySelector('[data-commission-sell-type]'),
      sellObject: root.querySelector('[data-commission-sell-object]'),
      sellQty: root.querySelector('[data-commission-sell-qty]'),
      closeButtons: [...root.querySelectorAll('[data-commission-close]')],
    };

    function setStatus(text, bad = false) {
      if (!els.status) return;
      els.status.textContent = text || 'Готово';
      els.status.classList.toggle('is-bad', !!bad);
    }

    function setBadge(value) {
      const text = value > 0 ? (value > 99 ? '99+' : String(value)) : 'NEW';
      document.querySelectorAll('[data-commission-badge]').forEach((badge) => {
        badge.textContent = text;
      });
    }

    async function post(url, fields) {
      const form = new FormData();
      form.set('_csrf', csrf);
      Object.entries(fields).forEach(([key, value]) => {
        form.set(key, value);
      });
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        body: form,
      });
      return readJson(response);
    }

    function allowedCategories(categories) {
      const source = Array.isArray(categories) && categories.length > 0 ? categories : fallbackCategories;
      const seen = new Set();
      return source.filter((category) => {
        const key = String(category.key || '');
        const label = String(category.label || '');
        const normalized = normalizeString(`${key} ${label}`);
        if (seen.has(key)) return false;
        seen.add(key);
        return !bannedCategoryLabels.some((word) => normalized.includes(word));
      });
    }

    function renderCategories() {
      if (!els.categories) return;
      const categories = allowedCategories(state.categories);
      if (!categories.some((category) => category.key === state.category)) {
        state.category = 'all';
      }
      els.categories.innerHTML = categories.map((category) => (
        `<button type="button" class="commission-category${category.key === state.category ? ' is-active' : ''}" data-commission-category="${escapeHtml(category.key)}">${escapeHtml(category.label)}</button>`
      )).join('');
    }

    function lotRows() {
      if (state.view === 'my') {
        return [...(state.my.active || []), ...(state.my.history || [])];
      }
      return state.lots;
    }

    function allSellableRows() {
      return [
        ...(state.sellable.items || []),
        ...(state.sellable.pokemon || []),
        ...(state.sellable.eggs || []),
      ];
    }

    function renderTableHeader() {
      if (!els.headRow) return;
      const labels = state.view === 'my'
        ? ['Лот', 'Цена', 'За 1', 'Снять с продажи']
        : ['Лот', 'Цена', 'За 1', 'Продавец'];
      els.headRow.innerHTML = labels.map((label) => `<th>${escapeHtml(label)}</th>`).join('');
    }

    function actionForLot(lot) {
      if (lot.status === 'active' && lot.is_own) {
        return `<button type="button" class="cancel" data-commission-cancel="${Number(lot.id || 0)}">Снять</button>`;
      }
      if (lot.status === 'active' && lot.can_buy !== false) {
        return `<button type="button" class="buy" data-commission-buy="${Number(lot.id || 0)}">Купить</button>`;
      }
      return `<small>${escapeHtml(statusLabel(lot.status))}</small>`;
    }

    function statusLabel(status) {
      return {
        active: 'Активен',
        sold: 'Продан',
        cancelled: 'Снят',
        expired: 'Истёк',
        locked: 'В обработке',
      }[String(status || '')] || String(status || 'Статус');
    }

    function typeLabel(type) {
      return {
        item: 'Предмет',
        pokemon: 'Покемон',
        egg: 'Яйцо',
        currency: 'Валюта',
      }[String(type || '')] || String(type || 'Лот');
    }

    function renderLots() {
      if (!els.lots || !els.empty) return;
      renderTableHeader();
      const rows = lotRows();
      els.empty.textContent = state.view === 'my' ? 'Не найдено ни одного выставленного лота.' : 'Активных лотов пока нет.';
      els.empty.hidden = rows.length > 0;
      els.lots.innerHTML = rows.map((lot) => {
        const expires = formatDate(lot.expires_at);
        return `<tr>
          <td>
            <div class="commission-lot">
              <span class="commission-lot-icon">
                <img src="${escapeHtml(lot.object_icon || '/public/img/ui/chatgpt-pokeball.png')}" alt="" onerror="this.src='/public/img/ui/chatgpt-pokeball.png'">
                <b class="commission-lot-count">${formatNumber(lot.quantity)}</b>
              </span>
              <span>
                <b class="commission-lot-name">${escapeHtml(lot.object_name || 'Лот')}</b>
                <span class="commission-lot-sub">${escapeHtml(lot.category_label || typeLabel(lot.object_type))} · #${formatNumber(lot.object_id)}</span>
              </span>
              <button class="commission-preview-button" type="button" data-commission-preview-lot="${Number(lot.id || 0)}" aria-label="Предпросмотр">▥</button>
            </div>
          </td>
          <td><div class="commission-price">${formatNumber(lot.total_price)}</div></td>
          <td><div class="commission-price">${formatNumber(lot.price_per_unit)}</div></td>
          <td>
            <div class="commission-seller">
              <b>${escapeHtml(lot.seller_name || 'Игрок')}</b>
              <small>${expires ? `до ${escapeHtml(expires)}` : escapeHtml(statusLabel(lot.status))}</small>
              ${actionForLot(lot)}
            </div>
          </td>
        </tr>`;
      }).join('');
    }

    function renderSellableGrid() {
      if (!els.sellableGrid) return;
      const q = normalizeString(state.sellableQ);
      const rows = allSellableRows().filter((row) => {
        if (!q) return true;
        return normalizeString(`${row.name || ''} ${row.id || ''} ${row.category_label || ''}`).includes(q);
      });
      els.sellableGrid.innerHTML = rows.length > 0 ? rows.map((row) => `
        <button type="button" class="commission-sellable-card${row.blocked ? ' is-blocked' : ''}" data-commission-sellable-type="${escapeHtml(row.type || 'item')}" data-commission-sellable-id="${Number(row.id || 0)}" ${row.blocked ? 'disabled' : ''} title="${escapeHtml(row.blocked_reason || row.name || '')}">
          <img src="${escapeHtml(row.icon || '/public/img/ui/chatgpt-pokeball.png')}" alt="" onerror="this.src='/public/img/ui/chatgpt-pokeball.png'">
          ${row.count ? `<b>${formatNumber(row.count)}</b>` : ''}
          <span>${escapeHtml(row.name || 'Объект')}</span>
        </button>
      `).join('') : '<p class="commission-sellable-empty">Нет объектов, которые можно выставить.</p>';
    }

    function moveNames(moves) {
      if (!Array.isArray(moves) || moves.length === 0) return '';
      return moves.map((move) => typeof move === 'string' ? move : (move.name || `#${move.id || '?'}`)).join(', ');
    }

    function statPairs(data) {
      if (!data || typeof data !== 'object') return '';
      return Object.entries(data).map(([key, value]) => `${key}:${value}`).join(' / ');
    }

    function renderPreview(data) {
      if (!els.preview) return;
      if (!data) {
        els.preview.innerHTML = '<li>Выберите лот или объект для продажи.</li>';
        return;
      }
      const preview = data.preview || data;
      const rows = [];
      const title = data.object_name || data.name || preview.name || '';
      if (title) rows.push(['Название', title]);
      if (data.object_type || data.type) rows.push(['Тип', typeLabel(data.object_type || data.type)]);
      if (data.category_label) rows.push(['Категория', data.category_label]);
      if (data.count) rows.push(['Доступно', formatNumber(data.count)]);
      if (data.total_price) rows.push(['Цена', formatNumber(data.total_price)]);
      if (data.price_per_unit) rows.push(['Цена за 1', formatNumber(data.price_per_unit)]);
      if (preview.description) rows.push(['Описание', preview.description]);
      if (preview.level) rows.push(['Уровень', preview.level]);
      if (preview.sex !== undefined) rows.push(['Пол', Number(preview.sex) === 2 ? 'Самка' : (Number(preview.sex) === 1 ? 'Самец' : 'Бесполый')]);
      if (preview.shiny !== undefined) rows.push(['Shiny', preview.shiny ? 'да' : 'нет']);
      if (preview.nature) rows.push(['Характер', preview.nature]);
      if (preview.active !== undefined) rows.push(['Статус', preview.active ? 'Активная команда' : 'Питомник/хранилище']);
      if (preview.held_item && preview.held_item.name) rows.push(['Предмет', preview.held_item.name]);
      if (preview.iv) rows.push(['IV', statPairs(preview.iv)]);
      if (preview.ev) rows.push(['EV', statPairs(preview.ev)]);
      const moves = moveNames(preview.moves);
      if (moves) rows.push(['Атаки', moves]);
      if (preview.egg_attack_name) rows.push(['Яйцевая атака', preview.egg_attack_name]);
      if (preview.remaining_seconds !== undefined) rows.push(['До вылупления', `${formatNumber(Math.max(0, Math.ceil(Number(preview.remaining_seconds) / 3600)))} ч.`]);
      if (preview.effect_status) rows.push(['Статус эффекта', preview.effect_status]);
      if (preview.target_use_rule) rows.push(['Правило применения', preview.target_use_rule]);
      if (preview.compatibility) rows.push(['Совместимость', preview.compatibility]);
      if (preview.equippable !== undefined) rows.push(['Можно экипировать', preview.equippable ? 'да' : 'нет']);

      els.preview.innerHTML = rows.length
        ? rows.map(([key, value]) => `<li><b>${escapeHtml(key)}:</b> ${escapeHtml(value)}</li>`).join('')
        : '<li>Нет дополнительных данных.</li>';
    }

    function currentSellSource() {
      const type = els.sellType?.value || 'item';
      if (type === 'pokemon') return state.sellable.pokemon || [];
      if (type === 'egg') return state.sellable.eggs || [];
      return state.sellable.items || [];
    }

    function renderSellObjects(preferredId = null) {
      if (!els.sellType || !els.sellObject || !els.sellQty || !els.form) return;
      const rows = currentSellSource();
      const type = els.sellType.value;
      const available = rows.filter((row) => !row.blocked);
      els.sellObject.innerHTML = rows.length > 0
        ? rows.map((row) => (
          `<option value="${Number(row.id || 0)}" ${row.blocked ? 'disabled' : ''}>${escapeHtml(row.name || 'Объект')}${row.count ? ` x${formatNumber(row.count)}` : ''}${row.blocked_reason ? ` - ${escapeHtml(row.blocked_reason)}` : ''}</option>`
        )).join('')
        : '<option value="0">Нет доступных объектов</option>';
      const firstAvailable = available[0];
      if (firstAvailable) {
        const preferred = preferredId !== null
          ? available.find((row) => Number(row.id) === Number(preferredId))
          : null;
        els.sellObject.value = String((preferred || firstAvailable).id);
      }
      els.sellQty.disabled = type !== 'item';
      els.sellQty.value = '1';
      els.form.querySelector('button[type="submit"]').disabled = !firstAvailable;
      const selected = rows.find((row) => Number(row.id) === Number(els.sellObject.value));
      renderPreview(selected || firstAvailable || null);
      renderSellableGrid();
    }

    async function loadLots(quiet = false) {
      if (!quiet) setStatus('Загрузка лотов...');
      try {
        const qs = new URLSearchParams({ category: state.category, sort: state.sort, q: state.q });
        const data = await fetch(`/api/commission/lots?${qs}`, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        }).then(readJson);
        if (data.ok) {
          state.lots = Array.isArray(data.lots) ? data.lots : [];
          state.categories = allowedCategories(data.categories);
          state.loadedLots = true;
          renderCategories();
          renderLots();
          setBadge(Number(data.total || state.lots.length || 0));
          if (!quiet) setStatus(`Найдено лотов: ${formatNumber(data.total || state.lots.length)}`);
          return true;
        }
        setStatus(data.message || 'Не удалось загрузить лоты.', true);
      } catch (error) {
        setStatus('Ошибка сети при загрузке лотов.', true);
      }
      return false;
    }

    async function loadMy(quiet = false) {
      try {
        const data = await fetch('/api/commission/my', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        }).then(readJson);
        if (data.ok) {
          state.my = data;
          state.loadedMy = true;
          if (state.view === 'my') renderLots();
          return true;
        }
        if (!quiet) setStatus(data.message || 'Не удалось загрузить мои лоты.', true);
      } catch (error) {
        if (!quiet) setStatus('Ошибка сети при загрузке моих лотов.', true);
      }
      return false;
    }

    async function loadSellable(quiet = false) {
      try {
        const data = await fetch('/api/commission/sellable', {
          credentials: 'same-origin',
          headers: { Accept: 'application/json' },
        }).then(readJson);
        if (data.ok) {
          state.sellable = data;
          state.loadedSellable = true;
          renderSellObjects();
          renderSellableGrid();
          return true;
        }
        if (!quiet) setStatus(data.message || 'Не удалось загрузить объекты для продажи.', true);
      } catch (error) {
        if (!quiet) setStatus('Ошибка сети при загрузке объектов.', true);
      }
      return false;
    }

    async function loadAll(force = false) {
      if (!force && state.loadedLots && state.loadedSellable && state.loadedMy) {
        renderLots();
        return;
      }
      await Promise.all([
        loadLots(true),
        loadMy(true),
        loadSellable(true),
      ]);
      setStatus('Готово');
    }

    function setView(view) {
      state.view = view === 'my' ? 'my' : 'lots';
      els.tabs.forEach((button) => {
        button.classList.toggle('is-active', button.dataset.commissionView === state.view);
      });
      const lotTab = els.tabs.find((button) => button.dataset.commissionView === 'lots');
      const myTab = els.tabs.find((button) => button.dataset.commissionView === 'my');
      if (lotTab) {
        lotTab.hidden = state.view === 'lots';
        lotTab.textContent = 'Вернуться';
      }
      if (myTab) {
        myTab.hidden = state.view === 'my';
        myTab.textContent = 'Мои лоты';
      }
      if (els.sideTitle) {
        els.sideTitle.textContent = state.view === 'my' ? 'Мои объекты' : 'Категории';
      }
      if (els.viewTitle) {
        els.viewTitle.textContent = state.view === 'my' ? 'Лоты выставленные на продажу' : 'Активные лоты';
      }
      if (els.categories) {
        els.categories.hidden = state.view === 'my';
      }
      if (els.sellablePanel) {
        els.sellablePanel.hidden = state.view !== 'my';
      }
      if (state.view === 'my' && !state.loadedMy) {
        loadMy();
      }
      if (state.view === 'my' && !state.loadedSellable) {
        loadSellable();
      }
      renderSellableGrid();
      renderLots();
    }

    function open() {
      if (!overlay) return;
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.classList.add('has-commission-overlay');
      loadAll();
      window.setTimeout(() => els.search?.focus(), 40);
    }

    function close() {
      if (!overlay) return;
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('has-commission-overlay');
      setStatus('Готово');
    }

    els.categories?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-commission-category]');
      if (!button) return;
      state.category = button.dataset.commissionCategory || 'all';
      state.view = 'lots';
      setView('lots');
      renderCategories();
      loadLots();
    });

    els.tabs.forEach((button) => {
      button.addEventListener('click', () => setView(button.dataset.commissionView || 'lots'));
    });

    els.sort?.addEventListener('change', () => {
      state.sort = els.sort.value || 'new';
      loadLots();
    });

    els.search?.addEventListener('input', () => {
      window.clearTimeout(state.searchTimer);
      state.searchTimer = window.setTimeout(() => {
        state.q = els.search.value.trim();
        loadLots();
      }, 250);
    });

    els.reset?.addEventListener('click', () => {
      state.category = 'all';
      state.sort = 'new';
      state.q = '';
      if (els.sort) els.sort.value = 'new';
      if (els.search) els.search.value = '';
      renderCategories();
      loadLots();
    });

    els.refresh?.addEventListener('click', () => loadAll(true));

    els.lots?.addEventListener('click', async (event) => {
      const previewButton = event.target.closest('[data-commission-preview-lot]');
      if (previewButton) {
        const lot = lotRows().find((row) => Number(row.id) === Number(previewButton.dataset.commissionPreviewLot));
        renderPreview(lot || null);
      }

      const buyButton = event.target.closest('[data-commission-buy]');
      const cancelButton = event.target.closest('[data-commission-cancel]');
      if (!buyButton && !cancelButton) return;

      const button = buyButton || cancelButton;
      button.disabled = true;
      const lotId = Number(button.dataset.commissionBuy || button.dataset.commissionCancel || 0);
      const data = await post(buyButton ? '/api/commission/lots/buy' : '/api/commission/lots/cancel', { lot_id: lotId });
      setStatus(data.message || (data.ok ? 'Готово.' : 'Операция не выполнена.'), !data.ok);
      if (data.ok) {
        await loadAll(true);
      } else {
        button.disabled = false;
      }
    });

    els.sellType?.addEventListener('change', () => renderSellObjects());
    els.sellableSearch?.addEventListener('input', () => {
      state.sellableQ = els.sellableSearch.value.trim();
      renderSellableGrid();
    });
    els.sellableGrid?.addEventListener('click', (event) => {
      const card = event.target.closest('[data-commission-sellable-id]');
      if (!card || card.disabled) return;
      const type = card.dataset.commissionSellableType || 'item';
      const id = Number(card.dataset.commissionSellableId || 0);
      if (els.sellType) {
        els.sellType.value = type;
      }
      renderSellObjects(id);
      const selected = currentSellSource().find((row) => Number(row.id) === id);
      renderPreview(selected || null);
      root.querySelector('.commission-sell')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    });
    els.sellObject?.addEventListener('change', () => {
      const selected = currentSellSource().find((row) => Number(row.id) === Number(els.sellObject.value));
      renderPreview(selected || null);
    });

    els.form?.addEventListener('submit', async (event) => {
      event.preventDefault();
      const submit = els.form.querySelector('button[type="submit"]');
      submit.disabled = true;
      const payload = Object.fromEntries(new FormData(els.form).entries());
      const data = await post('/api/commission/lots', payload);
      setStatus(data.message || (data.ok ? 'Лот выставлен.' : 'Не удалось выставить лот.'), !data.ok);
      if (data.ok) {
        await loadAll(true);
      }
      submit.disabled = false;
    });

    els.closeButtons.forEach((button) => button.addEventListener('click', close));
    overlay?.addEventListener('click', (event) => {
      if (event.target === overlay) {
        close();
      }
    });

    renderCategories();
    renderSellObjects();
    setView(state.view);
    renderLots();

    if (mode === 'standalone') {
      loadAll(true);
    } else {
      loadLots(true);
    }

    return { open, close, reload: () => loadAll(true), root, overlay };
  }

  const markets = roots.map(createMarket).filter(Boolean);
  const overlayMarket = markets.find((market) => market.overlay);

  document.querySelectorAll('[data-open-commission]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      if (!overlayMarket) return;
      event.preventDefault();
      overlayMarket.open();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      overlayMarket?.close();
    }
  });

  window.GameCommissionOverlay = {
    open: () => overlayMarket?.open(),
    close: () => overlayMarket?.close(),
    reload: () => markets.forEach((market) => market.reload()),
  };
})();
