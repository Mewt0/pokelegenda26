<?php
use Pokemon8\View\View;

$dashboard = is_array($tournaments ?? null) ? $tournaments : [];
$initialJson = json_encode($dashboard, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}';
$csrfToken = (string) ($csrf ?? '');
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Турниры - Pokemon 8.0</title>
  <style>
    :root {
      --bg: #eef6ff;
      --panel: rgba(255, 255, 255, .93);
      --line: #c8dcf4;
      --text: #10213d;
      --muted: #62738d;
      --blue: #2d72e8;
      --green: #159862;
      --red: #c24545;
      --gold: #b77812;
    }
    * { box-sizing: border-box; }
    body { margin: 0; color: var(--text); background: linear-gradient(180deg, #e7f2ff, #f7fbff); font: 15px/1.45 Tahoma, Arial, sans-serif; }
    .wrap { max-width: 1180px; margin: 0 auto; padding: 20px; }
    .top { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .top a { color: #1458aa; font-weight: 800; text-decoration: none; }
    .hero, .panel, .card { background: var(--panel); border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 16px 42px rgba(62, 98, 140, .12); }
    .hero { padding: 20px; margin-bottom: 14px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 14px; align-items: center; }
    h1 { margin: 0 0 8px; font-size: clamp(28px, 4vw, 42px); line-height: 1.05; }
    h2, h3 { margin: 0; }
    .muted, .hero p { color: var(--muted); margin: 0; }
    .hero-badge { border: 1px solid #bad2ef; border-radius: 8px; background: #fff; padding: 10px 12px; min-width: 180px; text-align: right; }
    .grid { display: grid; grid-template-columns: minmax(0, 1fr) 310px; gap: 14px; align-items: start; }
    .panel { padding: 14px; }
    .cards { display: grid; gap: 12px; }
    .card { padding: 14px; display: grid; gap: 12px; }
    .card-top { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
    .status { display: inline-flex; align-items: center; border-radius: 999px; padding: 5px 9px; font-weight: 800; background: #e9f1ff; color: #165cba; white-space: nowrap; }
    .status.active { color: #0c744c; background: #e7f9f1; }
    .status.finished { color: #7a520d; background: #fff2cf; }
    .status.cancelled { color: #9a2f2f; background: #ffe8e8; }
    .info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .info { border: 1px solid #d8e7f7; background: #f8fbff; border-radius: 8px; padding: 9px; min-height: 66px; }
    .info span { display: block; color: var(--muted); font-size: 12px; margin-bottom: 3px; }
    .info b { overflow-wrap: anywhere; }
    .rules { border-left: 4px solid #7eb0ef; padding: 8px 10px; background: #f5faff; color: #30496a; border-radius: 8px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    button, .btn { border: 1px solid #b9d1ef; border-radius: 8px; background: #fff; color: #124e9c; font-weight: 800; padding: 9px 12px; cursor: pointer; text-decoration: none; }
    button.primary { color: #fff; background: var(--blue); border-color: var(--blue); }
    button.danger { color: var(--red); border-color: #efb7b7; }
    button.success { color: #fff; background: var(--green); border-color: var(--green); }
    button:disabled { opacity: .55; cursor: default; }
    .participant { border: 1px solid #d7e6f6; border-radius: 8px; padding: 8px 10px; background: #fff; display: flex; justify-content: space-between; gap: 10px; }
    .empty { border: 1px dashed var(--line); color: var(--muted); border-radius: 8px; padding: 14px; background: rgba(255,255,255,.7); }
    .log { margin-top: 10px; border-radius: 8px; padding: 10px; background: #10213d; color: #e8f2ff; min-height: 42px; }
    .log.error { background: #5d2020; }
    .log.success { background: #114c38; }
    .side-list { display: grid; gap: 8px; margin-top: 10px; }
    .qa-row { border: 1px solid var(--line); border-radius: 8px; background: #fff; padding: 9px 10px; }
    .qa-row strong { display: block; margin-bottom: 3px; }
    @media (max-width: 920px) {
      .wrap { padding: 12px; }
      .hero { grid-template-columns: 1fr; }
      .hero-badge { text-align: left; }
      .grid { grid-template-columns: 1fr; }
      .info-grid { grid-template-columns: 1fr; }
      .card-top { display: grid; }
    }
  </style>
</head>
<body>
  <main class="wrap" data-csrf="<?= View::e($csrfToken) ?>" data-tournaments-root>
    <nav class="top">
      <a href="/game">Игровой мир</a>
      <span>/</span>
      <strong>Турниры</strong>
    </nav>

    <section class="hero">
      <div>
        <h1>Турниры</h1>
        <p>Расписание, регистрация, взносы, арена и награды работают через новый backend/API без legacy iframe.</p>
      </div>
      <div class="hero-badge">
        <div class="muted">Время сервера</div>
        <strong data-server-time>...</strong>
        <div class="muted" data-server-zone></div>
      </div>
    </section>

    <section class="grid">
      <div class="panel">
        <div class="card-top">
          <h2>Доступные турниры</h2>
          <button type="button" data-refresh-tournaments>Обновить</button>
        </div>
        <div class="cards" data-tournament-list></div>
        <div class="empty" data-tournament-empty hidden>Сейчас нет открытых турниров.</div>
        <div class="log" data-tournament-log>Готово</div>
      </div>

      <aside class="panel">
        <h2>QA-проверки</h2>
        <div class="side-list" data-qa-list></div>
      </aside>
    </section>
  </main>

  <script type="application/json" id="tournamentInitial"><?= $initialJson ?></script>
  <script>
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
  </script>
</body>
</html>
