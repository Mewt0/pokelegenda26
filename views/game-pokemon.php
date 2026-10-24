<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Команда покемонов - League of Pokemons</title>
  <?php
    $headStyles = ['/public/css/game-pokemon.css'];
    require APP_ROOT . '/views/components/head-styles.php';
  ?>
</head>
<body data-csrf="<?= View::e($csrf) ?>">
  <main class="team-window">
    <header class="team-head">
      <span>Команда покемонов</span>
      <span class="team-head-actions">
        <button type="button" class="team-icon-btn" id="downloadBtn" aria-label="Скачать"><span class="icon icon--ui-download" aria-hidden="true"></span></button>
        <button type="button" class="team-close" id="closeBtn" aria-label="Закрыть">×</button>
      </span>
    </header>
    <section class="team-body">
      <aside class="team-list" id="pokemonList"></aside>
      <section class="detail" id="pokemonDetail" aria-live="polite"></section>
    </section>
    <footer class="team-foot">
      <span class="status" id="status">Загрузка...</span>
      <button type="button" id="detailId" class="pokemon-chat-id" title="Упомянуть покемона в чате" hidden></button>
    </footer>
  </main>

  <script src="<?= View::e(asset_url('/public/js/ui-preferences.js')) ?>"></script>
  <script>
    const csrf = document.body.dataset.csrf;
    const teamBody = document.querySelector('.team-body');
    const list = document.getElementById('pokemonList');
    const detail = document.getElementById('pokemonDetail');
    const status = document.getElementById('status');
    const detailId = document.getElementById('detailId');
    const slots = ['a', 'b', 'c', 'd'];
    const dragState = { active: false, offsetX: 0, offsetY: 0 };
    let team = [];
    let selectedId = 0;
    let detailOpen = false;
    let activeLearn = null;

    function setChatPokemonId(poke) {
      const id = Number(poke && poke.id || 0);
      detailId.textContent = id > 0 ? 'id' + id : '';
      detailId.dataset.pokemonId = String(id);
      detailId.hidden = id <= 0;
      detailId.setAttribute('aria-label', 'Упомянуть покемона #' + id + ' в чате');
    }

    detailId.addEventListener('click', () => {
      const id = Number(detailId.dataset.pokemonId || 0);
      if (!Number.isSafeInteger(id) || id <= 0) return;
      if (window.parent !== window && window.parent.Chat?.insertPokemonMention) {
        const parentChat = window.parent.Chat;
        window.parent.document.getElementById('pokemonCloseBtn')?.click();
        parentChat.insertPokemonMention(id);
      } else {
        try {
          sessionStorage.setItem('pokemonchic:chat-pokemon-mention', JSON.stringify({ id, time: Date.now() }));
          window.location.href = '/game';
        } catch (error) {
          setStatus('Напишите #' + id + ' в строке чата.');
        }
      }
    });

    document.getElementById('closeBtn').addEventListener('click', () => {
      resetLocalState();
      if (window.parent && window.parent !== window) {
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
      } else {
        window.location.href = '/game';
      }
    });
    setupParentWindowDrag();
    document.getElementById('downloadBtn').addEventListener('click', () => setStatus('Экспорт карточки будет подключен позже.'));
    document.addEventListener('click', event => {
      if (!event.target.closest('.learn-pop,.move-slot,.ev-pop,.stat-plus,.held-confirm-pop,.held-detail')) closePopups();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') closePopups();
    });

    function setStatus(text, bad = false) {
      status.textContent = text;
      status.classList.toggle('is-bad', bad);
    }

    function setupParentWindowDrag() {
      const head = document.querySelector('.team-head');
      if (!head || !window.parent || window.parent === window) return;
      if (window.matchMedia('(max-width: 760px)').matches) return;
      head.style.cursor = 'move';

      const parentDoc = window.parent.document;
      const parentWin = window.parent.window;
      const clamp = (left, top, win) => {
        const pad = 8;
        const maxLeft = Math.max(pad, parentWin.innerWidth - win.offsetWidth - pad);
        const maxTop = Math.max(pad, parentWin.innerHeight - win.offsetHeight - pad);
        return {
          left: Math.max(pad, Math.min(left, maxLeft)),
          top: Math.max(pad, Math.min(top, maxTop)),
        };
      };

      const parentPoint = event => {
        const frame = parentDoc.getElementById('pokemonFrame');
        const rect = frame ? frame.getBoundingClientRect() : { left: 0, top: 0 };
        return { x: rect.left + event.clientX, y: rect.top + event.clientY };
      };

      head.addEventListener('pointerdown', event => {
        if (event.target.closest('button')) return;
        const win = parentDoc.querySelector('.pokemon-window');
        if (!win) return;
        const point = parentPoint(event);
        const rect = win.getBoundingClientRect();
        dragState.active = true;
        dragState.offsetX = point.x - rect.left;
        dragState.offsetY = point.y - rect.top;
        win.classList.add('is-dragged');
        win.style.left = rect.left + 'px';
        win.style.top = rect.top + 'px';
        try { head.setPointerCapture(event.pointerId); } catch (e) {}
      });

      head.addEventListener('pointermove', event => {
        if (!dragState.active) return;
        const win = parentDoc.querySelector('.pokemon-window');
        if (!win) return;
        const point = parentPoint(event);
        const pos = clamp(point.x - dragState.offsetX, point.y - dragState.offsetY, win);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });

      const stopDrag = event => {
        dragState.active = false;
        try { head.releasePointerCapture(event.pointerId); } catch (e) {}
      };
      head.addEventListener('pointerup', stopDrag);
      head.addEventListener('pointercancel', stopDrag);
    }

    async function load(keepSelected = true) {
      try {
        const response = await fetch('/api/pokemon/moves', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus('Не удалось загрузить покемонов.', true);
          return;
        }
        team = payload.pokemon || [];
        if (!keepSelected) {
          detailOpen = false;
          selectedId = 0;
        } else if (!team.some(poke => Number(poke.id) === Number(selectedId))) {
          selectedId = 0;
          detailOpen = false;
        }
        renderAll();
        setStatus('Готово');
      } catch (error) {
        setStatus('Не удалось загрузить покемонов.', true);
      }
    }

    function renderAll() {
      renderTeamView();
      return;
      list.innerHTML = '';
      if (!team.length) {
        list.innerHTML = '<div class="empty-tile">- пусто -</div>';
        detail.innerHTML = '';
        return;
      }
      team.forEach(poke => list.appendChild(renderTile(poke)));
      const emptyCount = Math.max(0, 6 - team.length);
      for (let i = 0; i < emptyCount; i++) {
        const empty = document.createElement('div');
        empty.className = 'empty-tile';
        empty.textContent = '- пусто -';
        list.appendChild(empty);
      }
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      list.appendChild(pager);
      renderDetail(selectedPokemon());
    }

    function appendEmptyTile(target, text = 'Свободный слот') {
      const empty = document.createElement('div');
      empty.className = 'empty-tile';
      empty.textContent = text;
      target.appendChild(empty);
    }

    function appendPager(target) {
      const pager = document.createElement('div');
      pager.className = 'pager';
      pager.textContent = '...';
      target.appendChild(pager);
    }

    function renderTeamView() {
      closePopups();
      teamBody.classList.toggle('is-overview', !detailOpen);
      list.innerHTML = '';

      if (!team.length) {
        list.className = 'team-list is-overview';
        appendEmptyTile(list, 'Покемонов нет');
        detail.innerHTML = '';
        setChatPokemonId(null);
        return;
      }

      if (!detailOpen) {
        list.className = 'team-list is-overview';
        renderPokemonSection('Активная команда', activePokemon(), 'active', 'Команда пуста.');
        detail.innerHTML = '';
        setChatPokemonId(null);
        return;
      }

      const selected = selectedPokemon();
      list.className = 'team-list is-detail';
      list.appendChild(renderTile(selected));
      const moves = document.createElement('div');
      moves.className = 'move-list';
      slots.forEach((slot, index) => moves.appendChild(renderMoveSlot(selected, slot, index)));
      list.appendChild(moves);
      appendPager(list);
      renderPokemonDetail(selected);
    }

    function activePokemon() {
      return team.filter(poke => poke.active === true);
    }

    function nurseryPokemon() {
      return team.filter(poke => poke.active === false);
    }

    function renderPokemonSection(title, pokemons, kind, emptyText) {
      const section = document.createElement('section');
      section.className = 'team-section team-section-' + kind;
      const head = document.createElement('div');
      head.className = 'team-section-title';
      const max = kind === 'active' ? '/6' : '';
      head.innerHTML = '<span></span><small></small>';
      head.querySelector('span').textContent = title;
      head.querySelector('small').textContent = kind === 'active'
        ? String(pokemons.length) + max + ' с собой'
        : String(pokemons.length) + ' в питомнике';
      section.appendChild(head);

      const grid = document.createElement('div');
      grid.className = 'team-section-grid';
      section.appendChild(grid);

      if (!pokemons.length) {
        const empty = document.createElement('div');
        empty.className = 'empty-tile';
        empty.textContent = emptyText;
        grid.appendChild(empty);
      } else {
        pokemons.forEach(poke => grid.appendChild(renderTile(poke)));
      }

      if (kind === 'active') {
        for (let i = pokemons.length; i < 6; i++) appendEmptyTile(grid);
      }

      list.appendChild(section);
    }

    function renderTilesOnly() {
      const scrollTop = list.scrollTop;
      list.querySelectorAll('.poke-tile').forEach(tile => {
        tile.classList.toggle('is-active', Number(tile.dataset.id) === Number(selectedId));
      });
      list.scrollTop = scrollTop;
    }

    function renderTile(poke) {
      const tile = document.createElement('button');
      tile.type = 'button';
      tile.className = 'poke-tile' + (poke.active === false ? ' is-stored' : ' is-team') + (Number(poke.id) === Number(selectedId) ? ' is-active' : '');
      tile.dataset.id = String(poke.id);
      tile.addEventListener('click', () => {
        if (detailOpen && Number(poke.id) === Number(selectedId)) {
          openDexPokemon(poke);
          return;
        }
        selectedId = Number(poke.id);
        detailOpen = true;
        renderAll();
      });
      tile.innerHTML = [
        '<img class="ball" src="/public/img/legacy/ui/menu-pokemon.png" alt="">',
        '<span class="lock icon icon--ui-lock" aria-hidden="true"></span>',
        '<span class="tile-held" hidden><img alt=""></span>',
        '<div class="tile-icons"><span>ST</span><span class="icon icon--ui-shiny" aria-hidden="true"></span></div>',
        '<span class="tile-status"></span>',
        '<div class="tile-art"><img alt=""></div>',
        '<div class="tile-lvl"></div>',
        '<div class="hpbar"><i></i></div>',
        '<div class="tile-foot"><span class="tile-name"></span><span class="gender">♂</span></div>',
      ].join('');
      setSprite(tile.querySelector('.tile-art img'), poke);
      tile.querySelector('.tile-lvl').textContent = Number(poke.level || 0);
      tile.querySelector('.hpbar i').style.width = hpPercent(poke) + '%';
      tile.querySelector('.tile-name').textContent = displayName(poke);
      tile.querySelector('.tile-status').textContent = poke.active === false ? 'Питомник' : (poke.starter ? 'Стартовый' : 'Команда');
      renderHeldBadge(tile.querySelector('.tile-held'), poke);
      if (!poke.starter) tile.querySelector('.tile-icons span').innerHTML = '<span class="icon icon--ui-starter" aria-hidden="true"></span>';
      return tile;
    }

    function renderPokemonDetail(poke) {
      if (!poke) return;
      setChatPokemonId(poke);
      detail.innerHTML = [
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="held-detail" hidden><img alt=""><span></span></div>',
        '<div class="detail-actions"></div>',
        '<div class="stats"></div>',
        '<div class="training-box"></div>',
        '<div class="meta"><span class="icon icon--player-avatar-default" aria-hidden="true"></span> ВаДИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(displayDexNumber(poke)) + ' ' + displayName(poke) + '  ♂';
      renderHeldDetail(detail.querySelector('.held-detail'), poke);
      detail.querySelector('.starter').hidden = !poke.starter;
      renderStats(poke);
      renderTraining(poke);
      renderNurseryActions(poke);
    }

    function renderDetail(poke) {
      closePopups();
      if (!poke) return;
      setChatPokemonId(poke);
      detail.innerHTML = [
        '<div class="detail-left"><div class="detail-art"><img alt=""></div><div class="move-list"></div></div>',
        '<div class="detail-main">',
        '<h2 class="detail-title"></h2>',
        '<div class="detail-sub"></div>',
        '<span class="badge">Обычный характер</span>',
        '<div class="held-detail" hidden><img alt=""><span></span></div>',
        '<div class="detail-actions"></div>',
        '<div class="stats"></div>',
        '<div class="training-box"></div>',
        '<div class="meta"><span class="icon icon--player-avatar-default" aria-hidden="true"></span> ВадИлаа 2 дня назад <button type="button" class="starter">стартовый</button></div>',
        '</div>'
      ].join('');
      setSprite(detail.querySelector('.detail-art img'), poke);
      bindDexOpen(detail.querySelector('.detail-art'), poke);
      detail.querySelector('.detail-title').textContent = displayName(poke);
      bindDexOpen(detail.querySelector('.detail-title'), poke);
      detail.querySelector('.detail-sub').textContent = '#' + pad3(displayDexNumber(poke)) + ' ' + displayName(poke) + '  ♂';
      renderHeldDetail(detail.querySelector('.held-detail'), poke);
      detail.querySelector('.starter').hidden = !poke.starter;
      slots.forEach((slot, index) => detail.querySelector('.move-list').appendChild(renderMoveSlot(poke, slot, index)));
      renderStats(poke);
      renderTraining(poke);
      renderNurseryActions(poke);
    }

    function renderNurseryActions(poke) {
      const box = detail.querySelector('.detail-actions');
      if (!box || !poke) return;
      const activeCount = activePokemon().length;
      const isStored = poke.active === false;
      const action = isStored ? 'take' : 'store';
      const disabled = isStored ? activeCount >= 6 : activeCount <= 1 || !!poke.starter;
      const hint = isStored
        ? 'В команду'
        : (poke.starter ? 'Стартового нельзя убрать' : 'В питомник');
      box.innerHTML = [
        '<button type="button" class="team-action" data-team-back>← Все покемоны</button>',
        '<button type="button" class="team-action primary" data-nursery-action="' + action + '"' + (disabled ? ' disabled' : '') + '></button>',
      ].join('');
      box.querySelector('[data-team-back]').addEventListener('click', event => {
        event.stopPropagation();
        selectedId = 0;
        detailOpen = false;
        renderAll();
      });
      const button = box.querySelector('[data-nursery-action]');
      button.textContent = hint;
      button.addEventListener('click', event => {
        event.stopPropagation();
        nurseryAction(Number(poke.id || 0), action);
      });
    }

    function renderTraining(poke) {
      const box = detail.querySelector('.training-box');
      if (!box || !poke) return;
      const t = poke.training || {};
      const stage = Number(t.stage || 0);
      const named = t.namedEffect ? ', эффект: ' + namedEffectLabel(t.namedEffect) : '';
      const mark = trainingMarkHtml(t);
      box.innerHTML = [
        '<div class="training-head"><strong>Тренировка: ' + escapeHtml(t.stageName || 'Без тренировки') + '</strong>' + mark + '</div>',
        '<small>Бонус: +' + Number(t.bonus || 0) + '% к ' + escapeHtml(t.statLabel || 'Не выбран') + named + '</small>',
        '<small>' + (t.tamed ? 'Приручен: нельзя передавать.' : 'Не приручен.') + '</small>',
        '<div class="training-chances">',
          '<span>Успех: ' + formatChance(t.successChance) + '</span>',
          '<span>С умением: ' + formatChance(t.boostedSuccessChance) + '</span>',
          '<span>Ослабление: ' + formatChance(t.weakenChance) + '</span>',
        '</div>',
        '<div class="training-actions">',
          '<button type="button" data-training-action="train">Набор тренировки</button>',
          '<button type="button" data-training-action="weaken"' + (stage <= 0 ? ' disabled' : '') + '>Ослабить</button>',
        '</div>',
      ].join('');
      box.querySelectorAll('[data-training-action]').forEach(button => {
        button.addEventListener('click', event => {
          event.stopPropagation();
          useTrainingItem(Number(poke.id || 0), button.dataset.trainingAction || 'train');
        });
      });
    }

    function trainingMarkHtml(training) {
      const stage = Number(training && training.stage || 0);
      if (stage <= 0) return '';
      const label = escapeHtml(training.icon || String(stage));
      const title = escapeHtml((training.stageName || 'Тренировка') + ': +' + Number(training.bonus || 0) + '%');
      return '<span class="training-mark s' + stage + '" title="' + title + '">' + label + '</span>';
    }

    function trainingStatKey(label) {
      return {
        'Атака': 'atk',
        'Защита': 'def',
        'Скорость': 'speed',
        'Спец.атака': 'satk',
        'Спец.защита': 'sdef',
      }[label] || '';
    }

    function formatChance(value) {
      const num = Number(value || 0);
      return (Number.isInteger(num) ? String(num) : String(num).replace('.', ',')) + '%';
    }

    function namedEffectLabel(effect) {
      return {
        burn: 'ожог',
        paralyze: 'паралич',
        freeze: 'заморозка',
        poison: 'яд',
        confuse: 'спутанность',
        fear: 'страх',
      }[String(effect || '')] || effect;
    }

    function renderMoveSlot(poke, slot, index) {
      const current = poke.moves && poke.moves[slot] ? poke.moves[slot] : { id: 0, name: 'Нет атаки' };
      const type = current.type || 'Normal';
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'move-slot' + (Number(current.id || 0) <= 0 ? ' is-empty' : '');
      row.innerHTML = [
        '<span class="move-icon"></span>',
        '<span class="move-main"><span class="move-name"></span><span class="move-pp"></span><span class="move-hint"></span></span>'
      ].join('');
      const icon = row.querySelector('.move-icon');
      icon.classList.add(typeClass(type));
      icon.innerHTML = '<img alt="">';
      const img = icon.querySelector('img');
      img.src = typeIconSrc(type);
      img.alt = typeLabel(type);
      icon.title = typeLabel(type);
      row.querySelector('.move-name').textContent = current.name || 'Нет атаки';
      row.querySelector('.move-pp').textContent = current.id ? ppText(current) : '';
      row.querySelector('.move-hint').textContent = current.id ? 'Нажмите, чтобы заменить' : 'Изучить атаку';
      row.addEventListener('click', event => {
        event.stopPropagation();
        showLearnPopup(row, poke, slot, current);
      });
      return row;
    }

    function renderStats(poke) {
      const level = Number(poke.level || 0);
      const hp = Number(poke.hp || 0);
      const hpMax = Number(poke.hpMax || 1);
      const training = poke.training || {};
      const trainedStat = String(training.stat || '');
      const stats = poke.stats || {};
      const ev = poke.ev || {};
      const vitaminCapacity = poke.vitaminCapacity || {};
      const vitaminByStat = vitaminCapacity.byStat || {};
      const evMaxPerStat = Math.max(1, Number(ev.maxPerStat || 252));
      const evMaxTotal = Math.max(1, Number(ev.maxTotal || 510));
      const evTotal = Math.max(0, Number(ev.total || 0));
      const evRemainingTotal = Math.max(0, Number(ev.remainingTotal || Math.max(0, evMaxTotal - evTotal)));
      const vitaminUsesLeft = Math.max(0, Number(vitaminCapacity.remainingTotalUses || 0));
      const values = [
        { label: 'Счастье', key: '', value: Number(poke.happiness ?? 100), max: 100, isHp: false, hasEv: false },
        { label: 'Здоровье', key: 'hp', value: Number(stats.hp || hpMax || hp), max: Math.max(1, Number(stats.hp || hpMax || hp)), isHp: true, hasEv: true },
        { label: 'Атака', key: 'atk', value: Number(stats.atk || Math.max(1, Math.round(level * 0.58))), max: 100, isHp: false, hasEv: true },
        { label: 'Защита', key: 'def', value: Number(stats.def || Math.max(1, Math.round(level * 0.58))), max: 100, isHp: false, hasEv: true },
        { label: 'Скорость', key: 'speed', value: Number(stats.speed || Math.max(1, Math.round(level * 0.55))), max: 100, isHp: false, hasEv: true },
        { label: 'Спец.атака', key: 'satk', value: Number(stats.satk || Math.max(1, Math.round(level * 0.64))), max: 100, isHp: false, hasEv: true },
        { label: 'Спец.защита', key: 'sdef', value: Number(stats.sdef || Math.max(1, Math.round(level * 0.64))), max: 100, isHp: false, hasEv: true },
      ];
      const box = detail.querySelector('.stats');
      const summary = document.createElement('div');
      summary.className = 'vitamin-summary';
      summary.innerHTML = '<span>EV всего: <b></b></span><span>Можно влить ещё: <b></b></span><span>Доступно витаминов: <b></b></span>';
      summary.querySelectorAll('b')[0].textContent = evTotal + '/' + evMaxTotal;
      summary.querySelectorAll('b')[1].textContent = evRemainingTotal + ' EV';
      summary.querySelectorAll('b')[2].textContent = '×' + vitaminUsesLeft;
      box.appendChild(summary);
      values.forEach(item => {
        const label = item.label;
        const value = item.value;
        const max = item.max;
        const isHp = item.isHp;
        const evValue = item.hasEv ? Math.max(0, Number(ev[item.key] || 0)) : 0;
        const evRemaining = item.hasEv ? Math.max(0, Math.min(evMaxPerStat - evValue, evRemainingTotal)) : 0;
        const statVitaminLeft = item.hasEv ? Math.max(0, Number(vitaminByStat[item.key] || Math.floor(evRemaining / Math.max(1, Number(vitaminCapacity.step || 10))))) : 0;
        const isTrained = trainedStat !== '' && trainingStatKey(label) === trainedStat && Number(training.stage || 0) > 0;
        const row = document.createElement('div');
        row.className = 'stat-row' + (isHp ? ' hp' : '') + (isTrained ? ' is-trained' : '');
        row.innerHTML = '<span class="stat-label"><span></span></span><b class="stat-value"></b><span class="stat-track"><i></i></span><span class="stat-ev-chip"></span><button type="button" class="stat-plus">+</button>';
        row.querySelector('.stat-label span').textContent = label;
        if (isTrained) {
          row.querySelector('.stat-label').insertAdjacentHTML('beforeend', trainingMarkHtml(training));
        }
        row.querySelector('.stat-value').textContent = label === 'Счастье' ? '' : value;
        row.querySelector('i').style.width = Math.max(0, Math.min(100, isHp ? (hp / Math.max(1, hpMax) * 100) : (value / Math.max(1, max) * 100))) + '%';
        const evChip = row.querySelector('.stat-ev-chip');
        const plusButton = row.querySelector('.stat-plus');
        if (item.hasEv) {
          evChip.textContent = 'EV ' + evValue + '/' + evMaxPerStat;
          evChip.title = 'Можно влить ещё: ' + evRemaining + ' EV';
          evChip.classList.toggle('is-full', evRemaining <= 0);
          const vitaminChip = document.createElement('span');
          vitaminChip.className = 'stat-vitamin-chip' + (statVitaminLeft <= 0 ? ' is-empty' : '');
          vitaminChip.textContent = '×' + statVitaminLeft;
          vitaminChip.title = 'Можно влить ещё витаминов в этот стат: ' + statVitaminLeft;
          plusButton.title = 'EV: ' + evValue + '/' + evMaxPerStat + '. Можно влить ещё: ' + evRemaining + ' EV. Витаминов: ×' + statVitaminLeft;
          plusButton.setAttribute('aria-label', plusButton.title);
          plusButton.before(vitaminChip);
          plusButton.addEventListener('click', event => {
            event.stopPropagation();
            showEvPopup(row.querySelector('.stat-plus'), {
              label,
              ev: evValue,
              evMax: evMaxPerStat,
              remainingEv: evRemaining,
              vitamins: statVitaminLeft,
              totalEv: evTotal,
              totalMax: evMaxTotal,
              totalRemaining: evRemainingTotal,
            });
          });
        } else {
          evChip.textContent = '';
          evChip.style.visibility = 'hidden';
          plusButton.disabled = true;
          plusButton.textContent = '';
          // Отключённая кнопка без текста всё равно попадает в дерево доступности
          // безымянным элементом управления — даём ей имя (план §5.4).
          plusButton.setAttribute('aria-label', 'Очки усилий для этого стата недоступны');
        }
        box.appendChild(row);
      });
    }

    function showLearnPopup(anchor, poke, slot, current) {
      closePopups();
      const currentId = Number(current && current.id || 0);
      const moves = (poke.learnableMoves || []).filter(move => Number(move.id) !== currentId);
      activeLearn = { pokeId: Number(poke.id), slot, moves, currentId };
      const pop = document.createElement('div');
      pop.className = 'learn-pop';
      pop.innerHTML = '<h3>Заменить атаку</h3><input class="learn-search" type="search" placeholder="Поиск по атаке, типу или ID"><div class="learn-results"></div>';
      document.body.appendChild(pop);
      const input = pop.querySelector('.learn-search');
      const results = pop.querySelector('.learn-results');
      const render = () => renderLearnResults(results, moves, input.value);
      input.addEventListener('input', render);
      render();
      placePopup(pop, anchor, 8);
      window.setTimeout(() => input.focus(), 0);
    }

    function normalizeSearch(value) {
      return String(value || '').toLowerCase().replace(/[^a-z0-9а-яё]+/giu, '');
    }

    function renderLearnResults(target, moves, query) {
      const q = normalizeSearch(query);
      const filtered = moves.filter(move => {
        if (!q) return true;
        return normalizeSearch(move.name).includes(q)
          || normalizeSearch(move.type).includes(q)
          || String(Number(move.id || 0)).includes(q)
          || String(Number(move.level || 0)).includes(q);
      }).slice(0, 36);

      target.innerHTML = '';
      if (!filtered.length) {
        const empty = document.createElement('div');
        empty.className = 'learn-empty';
        empty.textContent = moves.length ? 'Ничего не найдено.' : 'Нет доступных атак.';
        target.appendChild(empty);
        return;
      }
      filtered.forEach(move => target.appendChild(renderLearnRow(move)));
    }

    function renderLearnRow(move) {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'learn-row';
      row.classList.add(typeClass(move.type || 'Normal'));
      row.title = typeLabel(move.type || 'Normal') + (Number(move.pp || 0) > 0 ? ', ' + Number(move.pp || 0) + ' PP' : '');
      row.innerHTML = '<span class="learn-type-icon"><img alt=""></span><strong></strong><small></small>';
      const icon = row.querySelector('.learn-type-icon');
      icon.classList.add(typeClass(move.type || 'Normal'));
      icon.title = typeLabel(move.type || 'Normal');
      row.querySelector('img').src = typeIconSrc(move.type || 'Normal');
      row.querySelector('img').alt = typeLabel(move.type || 'Normal');
      row.querySelector('strong').textContent = move.name || 'Атака';
      row.querySelector('small').textContent = 'Lv.' + Number(move.level || 0) + (Number(move.pp || 0) > 0 ? ' • ' + Number(move.pp || 0) + ' PP' : '');
      row.addEventListener('click', event => {
        event.stopPropagation();
        if (activeLearn) setMove(activeLearn.pokeId, activeLearn.slot, Number(move.id));
      });
      return row;
    }

    function showEvPopup(anchor, info) {
      closePopups();
      const data = info || {};
      const pop = document.createElement('div');
      pop.className = 'ev-pop';
      pop.innerHTML = '<strong></strong><div class="ev-pop-line"></div><div class="ev-pop-line"></div><div class="ev-pop-line"></div><button type="button">Понятно</button>';
      pop.querySelector('strong').textContent = data.label || 'EV';
      const lines = pop.querySelectorAll('.ev-pop-line');
      lines[0].textContent = 'EV: ' + Number(data.ev || 0) + '/' + Number(data.evMax || 252);
      lines[1].textContent = 'Можно влить ещё: ' + Number(data.remainingEv || 0) + ' EV';
      lines[2].textContent = 'Осталось витаминов: ×' + Number(data.vitamins || 0) + ' · общий лимит ' + Number(data.totalEv || 0) + '/' + Number(data.totalMax || 510);
      pop.querySelector('button').addEventListener('click', () => {
        closePopups();
      });
      document.body.appendChild(pop);
      placePopup(pop, anchor, 10);
    }

    function placePopup(pop, anchor, offset) {
      const rect = anchor.getBoundingClientRect();
      const width = pop.offsetWidth;
      const height = pop.offsetHeight;
      let left = rect.left + rect.width / 2 - width / 2;
      let top = rect.bottom + offset;
      left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
      if (top + height > window.innerHeight - 8) {
        const above = rect.top - height - offset;
        top = above >= 8 ? above : Math.max(44, (window.innerHeight - height) / 2);
      }
      pop.style.left = left + 'px';
      pop.style.top = top + 'px';
    }

    function closePopups() {
      document.querySelectorAll('.learn-pop,.ev-pop,.held-confirm-pop').forEach(node => node.remove());
      activeLearn = null;
    }

    async function useTrainingItem(pokemonId, action) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('action', action);
      try {
        const response = await fetch('/api/pokemon/training', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        await load(true);
      } catch (error) {
        setStatus('Тренировка не выполнена.', true);
      }
    }

    async function setMove(pokemonId, slot, moveId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('slot', slot);
      body.set('move_id', String(moveId));
      try {
        const response = await fetch('/api/pokemon/move', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        if (payload && payload.ok) {
          updateLocalMove(pokemonId, slot, moveId);
          renderAll();
        }
      } catch (error) {
        setStatus('Атака не обновлена.', true);
      }
    }

    async function nurseryAction(pokemonId, action) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      body.set('action', action);
      try {
        const response = await fetch('/api/pokemon/nursery', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        if (payload && Array.isArray(payload.pokemon)) {
          team = payload.pokemon;
          selectedId = 0;
          detailOpen = false;
          renderAll();
        } else {
          await load(false);
        }
      } catch (error) {
        setStatus('Питомник не обновлен.', true);
      }
    }

    function resetLocalState() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      activeLearn = null;
      renderAll();
    }

    async function reopenPanel() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      activeLearn = null;
      await load(false);
    }

    function updateLocalMove(pokemonId, slot, moveId) {
      const poke = team.find(item => Number(item.id) === Number(pokemonId));
      if (!poke) return;
      if (!poke.moves) poke.moves = {};
      const move = (poke.learnableMoves || []).find(item => Number(item.id) === Number(moveId));
      poke.moves[slot] = move
        ? { id: Number(move.id), name: move.name, type: move.type || 'Normal', pp: Number(move.pp || 0), ppMax: Number(move.pp || 0) }
        : { id: 0, name: 'Нет атаки' };
    }

    function selectedPokemon() {
      const active = activePokemon();
      return active.find(poke => Number(poke.id) === Number(selectedId)) || active[0] || null;
    }

    function displayName(poke) {
      return String(poke.name || ('Pokemon #' + Number(poke.baseNum || 0))).replace(/^#?\d+\s*/, '');
    }

    function heldItem(poke) {
      return poke && poke.heldItem && Number(poke.heldItem.id || 0) > 0 ? poke.heldItem : null;
    }

    function itemIconSrc(id) {
      const itemId = Number(id || 0);
      return '/public/img/items/' + itemId + '.png';
    }

    function setHeldIcon(img, itemId) {
      if (!img) return;
      const id = Number(itemId || 0);
      img.onerror = () => {
        img.onerror = () => { img.src = '/img/blank.gif'; };
        img.src = '/img/items/' + id + '.png';
      };
      img.src = itemIconSrc(id);
    }

    function renderHeldBadge(box, poke) {
      if (!box) return;
      const item = heldItem(poke);
      if (!item) {
        box.hidden = true;
        return;
      }
      box.hidden = false;
      box.title = item.name || ('Item #' + Number(item.id || 0));
      setHeldIcon(box.querySelector('img'), item.id);
    }

    function renderHeldDetail(box, poke) {
      if (!box) return;
      const item = heldItem(poke);
      if (!item) {
        box.hidden = true;
        box.onclick = null;
        box.onkeydown = null;
        box.removeAttribute('role');
        box.removeAttribute('tabindex');
        return;
      }
      box.hidden = false;
      setHeldIcon(box.querySelector('img'), item.id);
      box.querySelector('span').textContent = 'Держит: ' + (item.name || ('Item #' + Number(item.id || 0)));
      box.title = 'Нажми, чтобы снять предмет. ' + (item.title || item.name || '');
      box.setAttribute('role', 'button');
      box.tabIndex = 0;
      box.onclick = event => {
        event.stopPropagation();
        showHeldUnequipConfirm(box, poke, item);
      };
      box.onkeydown = event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          event.stopPropagation();
          showHeldUnequipConfirm(box, poke, item);
        }
      };
    }

    function showHeldUnequipConfirm(anchor, poke, item) {
      closePopups();
      const pop = document.createElement('div');
      pop.className = 'held-confirm-pop';
      pop.innerHTML = [
        '<strong>Снять предмет?</strong>',
        '<p></p>',
        '<div class="held-confirm-actions">',
          '<button type="button" class="confirm" data-held-confirm>Да</button>',
          '<button type="button" data-held-cancel>Нет</button>',
        '</div>'
      ].join('');
      pop.querySelector('p').textContent = 'Снять "' + (item.name || ('Item #' + Number(item.id || 0))) + '" с ' + displayName(poke) + ' и вернуть в инвентарь?';
      pop.querySelector('[data-held-confirm]').addEventListener('click', event => {
        event.stopPropagation();
        unequipHeldItem(Number(poke.id || 0));
      });
      pop.querySelector('[data-held-cancel]').addEventListener('click', event => {
        event.stopPropagation();
        closePopups();
      });
      document.body.appendChild(pop);
      placePopup(pop, anchor, 8);
    }

    async function unequipHeldItem(pokemonId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', String(pokemonId));
      try {
        setStatus('Снимаем предмет...');
        const response = await fetch('/api/inventory/unequip', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json' },
          body
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
        closePopups();
        if (payload && payload.ok) {
          await load(true);
        }
      } catch (error) {
        setStatus('Предмет не снят.', true);
      }
    }

    function hpPercent(poke) {
      return Math.max(0, Math.min(100, Number(poke.hp || 0) / Math.max(1, Number(poke.hpMax || 1)) * 100));
    }

    function pad3(value) {
      return String(Math.max(0, Number(value || 0))).padStart(3, '0');
    }

    function displayDexNumber(poke) {
      return Number(poke && (poke.dexNumber || poke.displayBaseNum || poke.baseId || poke.baseNum) || 0);
    }

    function isShiny(poke) {
      return String(poke.name || '').toLowerCase().includes('shiny');
    }

    function typeKey(type) {
      return String(type || 'Normal').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function typeClass(type) {
      return 'type-' + typeKey(type);
    }

    function typeIconSrc(type) {
      const map = {
        bug: 1,
        dark: 2,
        dragon: 3,
        electric: 4,
        fighting: 5,
        fire: 6,
        flying: 7,
        ghost: 8,
        grass: 9,
        ground: 10,
        ice: 11,
        normal: 12,
        poison: 13,
        psychic: 14,
        steel: 15,
        rock: 16,
        water: 17,
        pokesno: 12,
        item: 12,
        topokes: 12
      };
      const names = {
        1: 'bug',
        2: 'dark',
        3: 'dragon',
        4: 'electric',
        5: 'fighting',
        6: 'fire',
        7: 'flying',
        8: 'ghost',
        9: 'grass',
        10: 'ground',
        11: 'ice',
        12: 'normal',
        13: 'poison',
        14: 'psychic',
        15: 'steel',
        16: 'rock',
        17: 'water'
      };
      return '/public/img/types/' + (names[map[typeKey(type)] || 12] || 'normal') + '.png';
    }

    function escapeHtml(value) {
      return String(value ?? '').replace(/[&<>'"]/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      }[c]));
    }

    function typeLabel(type) {
      return String(type || 'Normal');
    }

    function ppText(move) {
      const pp = Number(move.pp || 0);
      const ppMax = Number(move.ppMax || 0);
      if (ppMax > 0) return pp + '/' + ppMax;
      return Number(move.pp || 0) > 0 ? String(pp) : 'PP';
    }

    function openDexPokemon(poke) {
      const dexId = Number(poke.baseNum || 0);
      if (dexId <= 0) return;
      if (window.parent && window.parent !== window && window.parent.PokemonDex) {
        window.parent.PokemonDex.openPokemon(dexId);
        const close = window.parent.document.getElementById('pokemonCloseBtn');
        if (close) close.click();
        return;
      }
      window.location.href = '/game/pokedex?id=' + encodeURIComponent(String(dexId));
    }

    function setSprite(img, poke) {
      const num = Number(poke.baseNum || 0);
      const padded = pad3(num);
      const urls = [];
      const assetSlug = spriteSlug(displayName(poke));
      if (isShiny(poke)) {
        urls.push('/Pok/shine/' + num + '.png');
        urls.push('/Pok/shiny/' + padded + '.gif');
        urls.push('/Pok/shiny/' + num + '.gif');
      }
      urls.push('/Pok/normal/' + num + '.png');
      urls.push('/Pok/' + num + '.jpg');
      if (assetSlug) {
        urls.push('/public/img/pokemon/art/' + assetSlug + '.png');
        urls.push('/public/img/pokemon/small/' + assetSlug + '.png');
      }
      urls.push('/Pok/pok/' + padded + '.gif');
      urls.push('/Pok/anim/' + padded + '.gif');
      let index = 0;
      img.onerror = () => {
        index += 1;
        img.src = urls[index] || '/img/blank.gif';
      };
      img.src = urls[index] || '/img/blank.gif';
    }

    function spriteSlug(name) {
      return String(name || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function bindDexOpen(node, poke) {
      if (!node) return;
      node.tabIndex = 0;
      node.setAttribute('role', 'button');
      node.title = 'Открыть в Покедексе';
      node.addEventListener('click', () => openDexPokemon(poke));
      node.addEventListener('keydown', event => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          openDexPokemon(poke);
        }
      });
    }

    window.PokemonTeamPanel = {
      reset: resetLocalState,
      open: reopenPanel,
      refresh: () => load(false),
    };

    load(false);
  </script>
</body>
</html>
