(() => {
  const roots = [...document.querySelectorAll('[data-quest-root]')];
  if (roots.length === 0) return;

  const statusLabels = {
    available: 'Новый',
    active: 'В процессе',
    completed: 'Выполнен',
    locked: 'Заблокирован',
    cooldown: 'Ожидание',
  };
  const typeLabels = {
    story: 'Сюжетный',
    daily: 'Ежедневный',
    pvp: 'PvP',
    tournament: 'Турнирный',
    event: 'Ивентовый',
    chain: 'Цепочка',
  };
  const iconMarkup = (name) => `<span class="icon icon--${name}" aria-hidden="true"></span>`;
  const summaryConfig = [
    ['active', 'В процессе', '◎'],
    ['available', 'Новых', iconMarkup('npc-quest')],
    ['completed', 'Выполнено', iconMarkup('player-online')],
    ['locked', 'Закрыто', iconMarkup('chat-user-menu-ignore')],
  ];
  const highlightWords = [
    'Профессор', 'Оук', 'Спайк', 'Канто', 'Виридиан', 'Оливин',
    'Pidgey', 'Pidgeotto', 'Spearow', 'Metapod', 'Horsea',
    'NPC', 'город', 'локацию', 'предмет', 'покемонов',
  ];

  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[char]));
  const formatNumber = (value) => Number(value || 0).toLocaleString('ru-RU');
  const clampPercent = (value) => Math.max(0, Math.min(100, Number(value || 0)));

  function highlighted(text) {
    let html = escapeHtml(text);
    for (const word of highlightWords.sort((a, b) => b.length - a.length)) {
      const safe = escapeHtml(word);
      html = html.replaceAll(safe, `<span class="quest-highlight">${safe}</span>`);
    }
    return html;
  }

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
      if (response.status === 419) return { ok: false, message: 'Сессия устарела. Обновите страницу.' };
      return { ok: false, message: 'Сервер вернул некорректный ответ.' };
    }
  }

  function createJournal(root) {
    const overlay = root.closest('[data-quest-overlay]');
    const mode = root.dataset.mode || (overlay ? 'overlay' : 'standalone');
    const csrf = root.dataset.csrf || document.querySelector('.world[data-csrf]')?.dataset.csrf || '';
    const mini = mode === 'overlay' ? document.querySelector('[data-quest-mini-tracker]') : null;
    const toastStack = mode === 'overlay' ? document.getElementById('questToastStack') : null;
    const state = {
      quests: [],
      summary: {},
      selectedId: 0,
      filter: 'all',
      query: '',
      trackedQuestId: 0,
      loaded: false,
      loading: false,
    };
    const els = {
      summary: root.querySelector('[data-quest-summary]'),
      list: root.querySelector('[data-quest-list]'),
      detail: root.querySelector('[data-quest-detail]'),
      rewards: root.querySelector('[data-quest-rewards]'),
      status: root.querySelector('[data-quest-status]'),
      search: root.querySelector('[data-quest-search]'),
      reset: root.querySelector('[data-quest-reset]'),
      filters: root.querySelector('[data-quest-filters]'),
      track: root.querySelector('[data-quest-track]'),
      map: root.querySelector('[data-quest-map]'),
      npc: root.querySelector('[data-quest-npc]'),
      closeButtons: [...root.querySelectorAll('[data-quest-close]')],
    };

    // Подключаем единый слой к существующим элементам, не меняя их селекторы.
    overlay?.classList.add('ui-overlay');
    els.search?.classList.add('ui-field__control');
    els.reset?.classList.add('ui-btn');
    root.querySelectorAll('.quest-search-row button, .quest-side-actions button').forEach((button) => button.classList.add('ui-btn'));
    els.filters?.querySelectorAll('button').forEach((button) => {
      button.classList.add('ui-btn', button.classList.contains('is-active') ? 'ui-btn--primary' : 'ui-btn--ghost');
    });
    [els.track, els.map, els.npc].forEach((button) => button?.classList.add('ui-btn'));
    els.closeButtons.forEach((button) => button.classList.add('ui-btn', 'ui-btn--ghost'));
    mini?.querySelectorAll('button').forEach((button) => button.classList.add('ui-btn'));


    function selectedQuest() {
      return state.quests.find((quest) => Number(quest.id || 0) === state.selectedId) || null;
    }

    function setStatus(text, bad = false) {
      if (!els.status) return;
      els.status.textContent = text || 'Готово';
      els.status.classList.toggle('is-bad', !!bad);
    }

    function toast(text) {
      setStatus(text);
      if (!toastStack || !text) return;
      const node = document.createElement('div');
      node.className = 'quest-toast';
      node.textContent = text;
      toastStack.appendChild(node);
      window.setTimeout(() => node.remove(), 3200);
    }

    async function post(url, fields) {
      const form = new FormData();
      form.set('_csrf', csrf);
      Object.entries(fields).forEach(([key, value]) => form.set(key, value));
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        body: form,
      });
      return readJson(response);
    }

    function filteredQuests() {
      const query = state.query.trim().toLowerCase();
      return state.quests.filter((quest) => {
        const status = String(quest.status || '');
        if (state.filter !== 'all') {
          if (state.filter === 'available') {
            if (!(status === 'available' || (quest.can_start && status !== 'completed'))) return false;
          } else if (status !== state.filter) {
            return false;
          }
        }
        if (!query) return true;
        return `${quest.title || ''} ${quest.description || ''} ${quest.id || ''}`.toLowerCase().includes(query);
      });
    }

    function renderSummary() {
      if (!els.summary) return;
      els.summary.innerHTML = summaryConfig.map(([key, label, icon]) => (
        `<span class="quest-summary-pill is-${key}"><i>${icon}</i>${escapeHtml(label)}: ${formatNumber(state.summary[key] || 0)}</span>`
      )).join('');
    }

    function renderList() {
      if (!els.list) return;
      const rows = filteredQuests();
      if (rows.length === 0) {
        const canReset = !!els.reset && (state.query.trim() !== '' || state.filter !== 'all');
        els.list.innerHTML = `<div class="quest-empty-state ui-empty"><b>Квесты не найдены</b><span>${canReset ? 'Попробуйте сбросить фильтры.' : 'Пока нет доступных квестов.'}</span>${canReset ? '<button type="button" class="ui-btn" data-quest-empty-reset>Сбросить фильтры</button>' : ''}</div>`;
        return;
      }
      els.list.innerHTML = rows.map((quest) => {
        const progress = quest.progress || {};
        const percent = clampPercent(progress.percent);
        const status = String(quest.status || 'available');
        return `<button type="button" class="quest-list-card ui-card${Number(quest.id) === state.selectedId ? ' is-selected' : ''}${quest.tracked ? ' is-tracked' : ''}" data-quest-select="${Number(quest.id || 0)}">
          <span class="quest-list-card-icon ui-card__media"><img src="${escapeHtml(quest.icon || '/public/img/ui/menu-quests.png')}" alt=""></span>
          <span class="quest-list-card-main">
            <b class="ui-card__title">${escapeHtml(quest.title || `Квест #${quest.id}`)}</b>
            <small class="ui-card__meta">${escapeHtml(statusLabels[status] || status)} · ${escapeHtml(typeLabels[quest.type] || 'Квест')} · ${percent}%</small>
          </span>
          ${status === 'available' ? '<span class="quest-new-dot" aria-hidden="true"></span>' : ''}
          <span class="quest-card-arrow" aria-hidden="true">›</span>
          <span class="quest-list-progress"><span style="width:${percent}%"></span></span>
        </button>`;
      }).join('');
      const selected = els.list.querySelector('.quest-list-card.is-selected');
      if (selected) selected.scrollIntoView({ block: 'nearest' });
    }

    function renderDetail() {
      const quest = selectedQuest();
      if (!els.detail) return;
      if (!quest) {
        els.detail.innerHTML = '<div class="quest-empty-state ui-empty"><img src="/public/img/ui/menu-quests.png" alt=""><b>Выберите квест</b><span>Слева появятся активные и доступные задания.</span></div>';
        renderRewards(null);
        renderActions(null);
        return;
      }
      const status = String(quest.status || 'available');
      const progress = quest.progress || { current: 0, target: 1, percent: 0 };
      const percent = clampPercent(progress.percent);
      const objectives = Array.isArray(quest.steps) ? quest.steps : [];
      els.detail.innerHTML = `
        <div class="quest-detail-title">
          <h2>${escapeHtml(quest.title || `Квест #${quest.id}`)}</h2>
          <span class="quest-status-badge is-${escapeHtml(status)}"><i>${status === 'completed' ? iconMarkup('player-online') : status === 'available' ? iconMarkup('npc-quest') : status === 'locked' ? iconMarkup('chat-user-menu-ignore') : '◎'}</i>${escapeHtml(statusLabels[status] || status)}</span>
        </div>
        <p class="quest-description">${highlighted(quest.description || 'Описание пока не заполнено.')}</p>
        <div class="quest-overall">
          <strong>Общий прогресс</strong>
          <b>${percent}% · ${formatNumber(progress.current)} / ${formatNumber(progress.target)}</b>
          <span class="quest-objective-bar"><span style="width:${percent}%"></span></span>
        </div>
        <div class="quest-objectives">
          ${objectives.length > 0 ? objectives.map(renderObjective).join('') : '<div class="quest-empty-state ui-empty"><b>Цели не заведены</b><p>Квест пока содержит только общее состояние.</p></div>'}
        </div>
        <div class="quest-detail-actions">
          ${quest.can_start ? '<button type="button" class="ui-btn ui-btn--primary" data-quest-start>Принять квест</button>' : ''}
          <button type="button" class="ui-btn ui-btn--ghost" data-quest-track-inline>${quest.tracked ? 'Не отслеживать' : 'Отследить квест'}</button>
          <button type="button" class="ui-btn ui-btn--ghost" data-quest-map-inline>Показать на карте</button>
        </div>`;
      renderRewards(quest);
      renderActions(quest);
    }

    function renderObjective(step) {
      const progress = step.progress || { current: 0, target: 1, percent: 0 };
      const percent = clampPercent(progress.percent);
      const status = String(step.status || 'locked');
      const stateClass = status === 'done' ? 'is-done' : (percent >= 70 && status === 'active' ? 'is-near' : '');
      const label = status === 'done' ? 'Завершено' : (status === 'active' ? 'Выполняется' : 'Закрыто');
      return `<article class="quest-objective ui-card ${stateClass}">
        <span class="quest-objective-icon ui-card__media is-${escapeHtml(status)}">${status === 'done' ? iconMarkup('player-online') : status === 'active' ? '<img src="/public/img/ui/menu-profile.png" alt="">' : '•'}</span>
        <div class="quest-objective-main">
          <div class="quest-objective-head">
            <b class="ui-card__title">${escapeHtml(step.title || `Цель ${step.step_no || ''}`)}</b>
            <span class="quest-step-state is-${escapeHtml(status)}">${escapeHtml(label)}</span>
          </div>
          <p class="ui-card__meta">${highlighted(step.description || '')}</p>
          <span class="quest-objective-bar"><span style="width:${percent}%"></span></span>
          <div class="quest-objective-meta ui-card__meta">
            <span>${formatNumber(progress.current)} / ${formatNumber(progress.target)}</span>
            <span>${percent}%</span>
          </div>
        </div>
      </article>`;
    }

    function renderRewards(quest) {
      if (!els.rewards) return;
      const rewards = quest && Array.isArray(quest.reward_view) ? quest.reward_view : [];
      if (rewards.length === 0) {
        els.rewards.className = 'quest-rewards-empty ui-empty';
        els.rewards.innerHTML = quest ? 'Награды для квеста пока не указаны.' : 'Выберите квест, чтобы увидеть награды.';
        return;
      }
      els.rewards.className = 'quest-rewards';
      els.rewards.innerHTML = rewards.map((reward) => `
        <article class="quest-reward-card ui-card">
          <span class="quest-reward-card-media ui-card__media"><img src="${escapeHtml(reward.icon || '/public/img/ui/menu-quests.png')}" alt="" onerror="this.src='/public/img/ui/menu-quests.png'"></span>
          <span>
            <b class="ui-card__title">${escapeHtml(reward.label || 'Награда')}</b>
            <small class="ui-card__meta">${reward.type === 'rank' ? '+' : '× '}${formatNumber(reward.amount || 1)}</small>
          </span>
        </article>
      `).join('');
    }

    function renderActions(quest) {
      const disabled = !quest || quest.status === 'locked';
      if (els.track) {
        els.track.disabled = disabled;
        els.track.textContent = quest && quest.tracked ? 'Не отслеживать' : 'Отследить квест';
        els.track.classList.toggle('is-primary', !!quest && !quest.tracked && !disabled);
        els.track.classList.toggle('ui-btn--primary', !!quest && !quest.tracked && !disabled);
      }
      if (els.map) els.map.disabled = disabled;
      if (els.npc) els.npc.disabled = disabled;
    }

    function renderMiniTracker() {
      if (!mini) return;
      const tracked = state.quests.find((quest) => quest.tracked)
        || state.quests.find((quest) => quest.status === 'active')
        || state.quests.find((quest) => quest.can_start);
      if (!tracked) {
        mini.hidden = true;
        return;
      }
      mini.hidden = false;
      const percent = clampPercent(tracked.progress && tracked.progress.percent);
      mini.querySelector('[data-quest-mini-title]').textContent = tracked.title || `Квест #${tracked.id}`;
      mini.querySelector('[data-quest-mini-progress]').style.width = `${percent}%`;
      mini.querySelector('[data-quest-mini-meta]').textContent = `${statusLabels[tracked.status] || tracked.status} · ${percent}%`;
    }

    function renderAll() {
      renderSummary();
      renderList();
      renderDetail();
      renderMiniTracker();
    }

    async function load() {
      if (state.loading) return;
      state.loading = true;
      try {
        setStatus('Загружаю журнал...');
        const response = await fetch('/api/quests', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const data = await readJson(response);
        if (!data.ok) {
          setStatus(data.message || 'Не удалось загрузить квесты.', true);
          return;
        }
        state.quests = Array.isArray(data.quests) ? data.quests : [];
        state.summary = data.summary || {};
        state.trackedQuestId = Number(data.tracked_quest_id || 0);
        const preferred = state.quests.find((quest) => quest.tracked)
          || state.quests.find((quest) => quest.status === 'active')
          || state.quests.find((quest) => quest.can_start)
          || state.quests[0];
        if (!selectedQuest() && preferred) {
          state.selectedId = Number(preferred.id || 0);
        }
        state.loaded = true;
        renderAll();
        setStatus('Готово');
      } catch (error) {
        setStatus('Не удалось загрузить журнал. Попробуйте ещё раз.', true);
      } finally {
        state.loading = false;
      }
    }

    async function startQuest() {
      const quest = selectedQuest();
      if (!quest) return;
      const data = await post('/api/quests/start', { quest_id: quest.id });
      toast(data.message || (data.ok ? 'Квест принят.' : 'Ошибка.'));
      await load();
    }

    async function trackQuest() {
      const quest = selectedQuest();
      if (!quest) return;
      const nextId = quest.tracked ? 0 : Number(quest.id || 0);
      const data = await post('/api/quests/track', { quest_id: nextId });
      toast(data.message || (data.ok ? 'Отслеживание обновлено.' : 'Ошибка.'));
      await load();
    }

    function activeNavigationForQuest(quest) {
      if (!quest) return null;
      const step = quest.current_step || {};
      return step.navigation || quest.navigation || null;
    }

    function activateNavigation(kind) {
      const quest = selectedQuest();
      if (!quest) return;
      const step = quest.current_step || {};
      const navigation = activeNavigationForQuest(quest);
      const label = kind === 'npc' ? 'Путь к NPC' : 'Путь по карте';
      if (!navigation) {
        const action = step.action_key ? ` Текущий ключ цели: ${step.action_key}.` : '';
        toast(`${label} будет включен, когда у цели появится точная локация.${action}`);
        return;
      }

      window.dispatchEvent(new CustomEvent('pokemon:quest-guide', {
        detail: {
          action: 'activate',
          mode: kind,
          quest: {
            id: Number(quest.id || 0),
            title: String(quest.title || 'Квест'),
          },
          step,
          navigation,
        },
      }));

      const target = navigation.onTarget
        ? (navigation.targetNpcTitle || navigation.targetLocationTitle || navigation.label || 'цель')
        : (navigation.nextLocationTitle || navigation.targetLocationTitle || navigation.label || 'следующий переход');
      toast(`${label} включён: ${target}.`);
      if (mode === 'overlay') {
        close();
      }
    }

    function open() {
      if (overlay) overlay.setAttribute('aria-hidden', 'false');
      if (!state.loaded) load();
    }

    function close() {
      if (overlay) overlay.setAttribute('aria-hidden', 'true');
    }

    if (els.search) {
      els.search.addEventListener('input', () => {
        state.query = els.search.value || '';
        renderList();
      });
    }
    if (els.reset) {
      els.reset.addEventListener('click', () => {
        state.query = '';
        state.filter = 'all';
        if (els.search) els.search.value = '';
        els.filters?.querySelectorAll('button').forEach((button) => {
          const active = button.dataset.questFilter === 'all';
          button.classList.toggle('is-active', active);
          button.classList.toggle('ui-btn--primary', active);
          button.classList.toggle('ui-btn--ghost', !active);
        });
        renderAll();
      });
    }
    els.filters?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-quest-filter]');
      if (!button) return;
      state.filter = button.dataset.questFilter || 'all';
      els.filters.querySelectorAll('button').forEach((node) => {
        const active = node === button;
        node.classList.toggle('is-active', active);
        node.classList.toggle('ui-btn--primary', active);
        node.classList.toggle('ui-btn--ghost', !active);
      });
      renderList();
    });
    els.list?.addEventListener('click', (event) => {
      if (event.target.closest('[data-quest-empty-reset]')) {
        els.reset?.click();
        return;
      }
      const button = event.target.closest('[data-quest-select]');
      if (!button) return;
      state.selectedId = Number(button.dataset.questSelect || 0);
      renderAll();
    });
    els.detail?.addEventListener('click', (event) => {
      if (event.target.closest('[data-quest-start]')) startQuest();
      if (event.target.closest('[data-quest-track-inline]')) trackQuest();
      if (event.target.closest('[data-quest-map-inline]')) activateNavigation('map');
    });
    els.track?.addEventListener('click', trackQuest);
    els.map?.addEventListener('click', () => activateNavigation('map'));
    els.npc?.addEventListener('click', () => activateNavigation('npc'));
    els.closeButtons.forEach((button) => button.addEventListener('click', close));
    overlay?.addEventListener('click', (event) => {
      if (event.target === overlay) close();
    });

    if (mode !== 'overlay') {
      load();
    } else {
      window.setTimeout(load, 500);
    }

    return { open, close, load };
  }

  const journals = roots.map(createJournal);

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-open-quests]');
    if (!trigger) return;
    event.preventDefault();
    journals.forEach((journal) => journal.open());
  });
  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    journals.forEach((journal) => journal.close());
  });
  window.QuestJournal = journals[0] || null;
})();
