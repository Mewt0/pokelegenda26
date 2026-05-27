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
      const items = [
        ['active', 'В процессе'],
        ['available', 'Новые'],
        ['completed', 'Выполнено'],
        ['locked', 'Закрыто'],
      ];
      els.summary.innerHTML = items.map(([key, label]) => (
        `<span class="quest-summary-pill">${escapeHtml(label)}: ${formatNumber(state.summary[key] || 0)}</span>`
      )).join('');
    }

    function renderList() {
      if (!els.list) return;
      const rows = filteredQuests();
      if (rows.length === 0) {
        els.list.innerHTML = '<div class="quest-empty-state"><b>Квесты не найдены</b><span>Попробуйте сбросить фильтр.</span></div>';
        return;
      }
      els.list.innerHTML = rows.map((quest) => {
        const progress = quest.progress || {};
        const percent = clampPercent(progress.percent);
        const status = String(quest.status || 'available');
        return `<button type="button" class="quest-list-card${Number(quest.id) === state.selectedId ? ' is-selected' : ''}${quest.tracked ? ' is-tracked' : ''}" data-quest-select="${Number(quest.id || 0)}">
          <span class="quest-list-card-icon"><img src="${escapeHtml(quest.icon || '/public/img/ui/menu-quests.png')}" alt=""></span>
          <span>
            <b>${escapeHtml(quest.title || `Квест #${quest.id}`)}</b>
            <small>${escapeHtml(statusLabels[status] || status)} · ${escapeHtml(typeLabels[quest.type] || 'Квест')} · ${percent}%</small>
          </span>
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
        els.detail.innerHTML = '<div class="quest-empty-state"><img src="/public/img/ui/menu-quests.png" alt=""><b>Выберите квест</b><span>Слева появятся активные и доступные задания.</span></div>';
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
          <span class="quest-status-badge is-${escapeHtml(status)}">${escapeHtml(statusLabels[status] || status)}</span>
        </div>
        <p class="quest-description">${highlighted(quest.description || 'Описание пока не заполнено.')}</p>
        <div class="quest-overall">
          <strong>Общий прогресс</strong>
          <b>${percent}% · ${formatNumber(progress.current)} / ${formatNumber(progress.target)}</b>
          <span class="quest-objective-bar"><span style="width:${percent}%"></span></span>
        </div>
        <div class="quest-objectives">
          ${objectives.length > 0 ? objectives.map(renderObjective).join('') : '<div class="quest-objective"><b>Цели не заведены</b><p>Квест пока содержит только общее состояние.</p></div>'}
        </div>
        <div class="quest-detail-actions">
          ${quest.can_start ? '<button type="button" class="primary" data-quest-start>Принять квест</button>' : ''}
          <button type="button" data-quest-track-inline>${quest.tracked ? 'Не отслеживать' : 'Отследить квест'}</button>
          <button type="button" data-quest-map-inline>Показать на карте</button>
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
      return `<article class="quest-objective ${stateClass}">
        <div class="quest-objective-head">
          <b>${escapeHtml(step.title || `Цель ${step.step_no || ''}`)}</b>
          <span class="quest-step-state is-${escapeHtml(status)}">${escapeHtml(label)}</span>
        </div>
        <p>${highlighted(step.description || '')}</p>
        <span class="quest-objective-bar"><span style="width:${percent}%"></span></span>
        <div class="quest-objective-meta">
          <span>${formatNumber(progress.current)} / ${formatNumber(progress.target)}</span>
          <span>${percent}%</span>
        </div>
      </article>`;
    }

    function renderRewards(quest) {
      if (!els.rewards) return;
      const rewards = quest && Array.isArray(quest.reward_view) ? quest.reward_view : [];
      if (rewards.length === 0) {
        els.rewards.className = 'quest-rewards-empty';
        els.rewards.innerHTML = quest ? 'Награды для квеста пока не указаны.' : 'Выберите квест, чтобы увидеть награды.';
        return;
      }
      els.rewards.className = 'quest-rewards';
      els.rewards.innerHTML = rewards.map((reward) => `
        <article class="quest-reward-card">
          <img src="${escapeHtml(reward.icon || '/public/img/ui/menu-quests.png')}" alt="" onerror="this.src='/public/img/ui/menu-quests.png'">
          <span>
            <b>${escapeHtml(reward.label || 'Награда')}</b>
            <small>${escapeHtml(reward.type || 'reward')} × ${formatNumber(reward.amount || 1)}</small>
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
      setStatus('Загружаю журнал...');
      const response = await fetch('/api/quests', { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const data = await readJson(response);
      state.loading = false;
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

    function hintNavigation(kind) {
      const quest = selectedQuest();
      if (!quest) return;
      const step = quest.current_step || {};
      const label = kind === 'npc' ? 'Переход к NPC' : 'Метка на карте';
      const action = step.action_key ? ` Текущий ключ цели: ${step.action_key}.` : '';
      toast(`${label} будет включен, когда у цели появится точная локация.${action}`);
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
        els.filters?.querySelectorAll('button').forEach((button) => button.classList.toggle('is-active', button.dataset.questFilter === 'all'));
        renderAll();
      });
    }
    els.filters?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-quest-filter]');
      if (!button) return;
      state.filter = button.dataset.questFilter || 'all';
      els.filters.querySelectorAll('button').forEach((node) => node.classList.toggle('is-active', node === button));
      renderList();
    });
    els.list?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-quest-select]');
      if (!button) return;
      state.selectedId = Number(button.dataset.questSelect || 0);
      renderAll();
    });
    els.detail?.addEventListener('click', (event) => {
      if (event.target.closest('[data-quest-start]')) startQuest();
      if (event.target.closest('[data-quest-track-inline]')) trackQuest();
      if (event.target.closest('[data-quest-map-inline]')) hintNavigation('map');
    });
    els.track?.addEventListener('click', trackQuest);
    els.map?.addEventListener('click', () => hintNavigation('map'));
    els.npc?.addEventListener('click', () => hintNavigation('npc'));
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
