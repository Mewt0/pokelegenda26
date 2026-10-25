(() => {
'use strict';
(() => {
  const root = document.querySelector('[data-tournaments-root]');
  const csrf = root?.dataset.csrf || '';
  const state = {data: JSON.parse(document.getElementById('tournamentInitial')?.textContent || '{}')};
  const list = document.querySelector('[data-tournament-list]');
  const empty = document.querySelector('[data-tournament-empty]');
  const log = document.querySelector('[data-tournament-log]');

  const esc = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[ch]));
  const setLog = (message, type = '') => {
    log.className = `log ${type}`;
    log.textContent = message || 'Готово';
  };
  const post = async (url, tournamentId) => {
    const body = new URLSearchParams({_csrf: csrf, tournament_id: String(tournamentId)});
    const response = await fetch(url, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body});
    return response.json();
  };
  const refresh = async () => {
    const response = await fetch('/api/tournaments', {credentials: 'same-origin'});
    state.data = await response.json();
    render();
  };
  const action = async (url, tournamentId) => {
    setLog('Выполняю действие...');
    const result = await post(url, tournamentId);
    if (result.ok) {
      setLog(result.message || 'Готово', 'success');
      await refresh();
      return;
    }
    setLog(result.message || 'Действие не выполнено.', 'error');
    await refresh();
  };

  const render = () => {
    const data = state.data || {};
    const serverTime = Number(data.serverTime || 0);
    document.querySelector('[data-server-time]').textContent = serverTime > 0 ? new Date(serverTime * 1000).toLocaleString('ru-RU') : 'неизвестно';
    document.querySelector('[data-server-zone]').textContent = data.timezone || '';
    renderQa(data.qaChecklist || []);
    renderList(data.tournaments || []);
  };

  const renderQa = rows => {
    const target = document.querySelector('[data-qa-list]');
    if (!target) return;
    const panel = target.closest('.panel');
    // QA-заметки внутренние: если сервер их не прислал (обычный игрок),
    // панель целиком скрывается, чтобы не висел пустой заголовок «QA-проверки».
    if (!Array.isArray(rows) || rows.length === 0) {
      if (panel) panel.hidden = true;
      return;
    }
    if (panel) panel.hidden = false;
    target.replaceChildren(...rows.map(row => {
      const div = document.createElement('div');
      div.className = 'qa-row';
      div.innerHTML = `<strong>${esc(row.title)}</strong><span>${esc(row.expected)}</span>`;
      return div;
    }));
  };

  const renderList = tournaments => {
    list.replaceChildren();
    empty.hidden = tournaments.length > 0;
    tournaments.forEach(tournament => list.appendChild(card(tournament)));
  };

  const card = tournament => {
    const article = document.createElement('article');
    article.className = 'card';
    const statusClass = tournament.status === 'active' ? 'active' : (tournament.status === 'finished' ? 'finished' : (tournament.status === 'cancelled' ? 'cancelled' : ''));
    const participant = tournament.participant;
    const actions = [];
    if (tournament.canRegister) actions.push(`<button type="button" class="primary" data-action="/api/tournaments/register" data-id="${tournament.id}">Зарегистрироваться</button>`);
    if (tournament.canCancel) actions.push(`<button type="button" class="danger" data-action="/api/tournaments/cancel" data-id="${tournament.id}">Отменить участие</button>`);
    if (tournament.canEnterArena) actions.push(`<button type="button" class="success" data-action="/api/tournaments/arena-enter" data-id="${tournament.id}">Войти на арену</button>`);
    if (tournament.canLeaveArena) actions.push(`<button type="button" data-action="/api/tournaments/arena-leave" data-id="${tournament.id}">Выйти с арены</button>`);
    if (tournament.canClaimReward) actions.push(`<button type="button" class="success" data-action="/api/tournaments/claim-reward" data-id="${tournament.id}">Получить награду</button>`);
    if (actions.length === 0) actions.push('<button type="button" disabled>Нет доступных действий</button>');

    const rewardItems = Object.entries(tournament.rewards?.items || {}).map(([id, count]) => `#${esc(id)} x${esc(count)}`).join(', ');
    const medals = (tournament.rewards?.medals || []).map(row => esc(row.title)).join(', ');
    article.innerHTML = `
      <div class="card-top">
        <div>
          <h3>${esc(tournament.title)}</h3>
          <div class="muted">#${esc(tournament.id)} · ${esc(tournament.participants?.label || '0')} участников</div>
        </div>
        <span class="status ${statusClass}">${esc(tournament.statusLabel)}</span>
      </div>
      <div class="info-grid">
        <div class="info"><span>Расписание</span><b>${esc(tournament.startsAtText)} → ${esc(tournament.endsAtText)}</b></div>
        <div class="info"><span>Дедлайн регистрации</span><b>${esc(tournament.registrationDeadlineText)}</b></div>
        <div class="info"><span>Взнос</span><b>${esc(tournament.entryFee?.label || 'без взноса')}</b></div>
        <div class="info"><span>Куратор</span><b>${esc(tournament.curator?.login || ('#' + (tournament.curator?.id || 0)))}</b></div>
        <div class="info"><span>Арена</span><b>${esc(tournament.arena?.title || ('#' + (tournament.arena?.locationId || 0)))}</b></div>
        <div class="info"><span>Уровни</span><b>${esc(tournament.levelRange?.min)}-${esc(tournament.levelRange?.max)}</b></div>
      </div>
      ${tournament.rules ? `<div class="rules">${esc(tournament.rules)}</div>` : ''}
      <div class="participant">
        <strong>Ваш статус</strong>
        <span>${esc(participant?.statusLabel || 'Не участвуете')}${participant?.placeNum ? ` · место ${esc(participant.placeNum)}` : ''}</span>
      </div>
      <div class="muted">Награды: ${esc(tournament.rewardNote || rewardItems || medals || 'не указаны')}</div>
      <div class="actions">${actions.join('')}</div>
    `;
    article.querySelectorAll('[data-action]').forEach(button => {
      button.addEventListener('click', () => action(button.dataset.action, button.dataset.id));
    });
    return article;
  };

  document.querySelector('[data-refresh-tournaments]')?.addEventListener('click', refresh);
  render();
})();
})();
