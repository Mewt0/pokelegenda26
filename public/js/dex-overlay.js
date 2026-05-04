(() => {
  const overlay = document.getElementById('dexOverlay');
  if (!overlay) return;

  const state = { mode: 'pokemon', query: '', items: [] };
  const tabs = overlay.querySelectorAll('[data-dex-tab]');
  const input = document.getElementById('dexSearchInput');
  const list = document.getElementById('dexList');
  const details = document.getElementById('dexDetails');
  const close = document.getElementById('dexCloseBtn');

  const esc = (v) => String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
  const endpoint = () => state.mode === 'pokemon' ? '/api/dex/pokemon' : '/api/dex/attacks';
  const detailEndpoint = (id) => state.mode === 'pokemon'
    ? `/api/dex/pokemon/show?id=${encodeURIComponent(id)}`
    : `/api/dex/attack/show?id=${encodeURIComponent(id)}`;

  function setMode(mode) {
    state.mode = mode === 'attacks' || mode === 'attack' || mode === 'move' ? 'attacks' : 'pokemon';
    tabs.forEach(t => t.classList.toggle('is-active', t.dataset.dexTab === state.mode));
  }

  async function open(mode = 'pokemon', id = 0) {
    setMode(mode);
    overlay.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-open');
    input.value = state.query;
    if (Number(id) > 0) {
      list.innerHTML = '<div class="dex-empty">Загружаю...</div>';
      await showDetails(Number(id));
      return;
    }
    await search();
  }

  function closeDex() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  }

  async function search() {
    const q = input.value.trim();
    state.query = q;
    list.innerHTML = '<div class="dex-empty">Загрузка...</div>';
    try {
      const res = await fetch(endpoint() + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' });
      const data = await res.json();
      if (!data.ok) {
        list.innerHTML = '<div class="dex-empty">Ошибка загрузки</div>';
        details.innerHTML = '<div class="dex-empty">API вернул ошибку</div>';
        return;
      }
      state.items = data.items || [];
      renderList();
      if (state.items[0]) showDetails(state.items[0].id);
      else details.innerHTML = '<div class="dex-empty">Ничего не найдено</div>';
    } catch (e) {
      list.innerHTML = '<div class="dex-empty">Ошибка сети</div>';
      details.innerHTML = '<div class="dex-empty">Проверь маршруты /api/dex/...</div>';
    }
  }

  function renderList() {
    list.innerHTML = state.items.map(item => {
      if (state.mode === 'pokemon') {
        return `<button type="button" class="dex-row" data-id="${Number(item.id)}"><b>#${esc(item.code || item.id)} ${esc(item.name)}</b><small>${esc((item.types || []).join(' / '))}</small></button>`;
      }
      const acc = Number(item.accuracy || 0) <= 0 ? '∞' : Number(item.accuracy || 0);
      return `<button type="button" class="dex-row" data-id="${Number(item.id)}"><b>#${Number(item.id)} ${esc(item.name)}</b><small>${esc(item.type)} · POW ${Number(item.power || 0)} · ACC ${acc}</small></button>`;
    }).join('') || '<div class="dex-empty">Ничего не найдено</div>';
  }

  async function showDetails(id) {
    details.innerHTML = '<div class="dex-empty">Загрузка...</div>';
    try {
      const res = await fetch(detailEndpoint(id), { credentials: 'same-origin' });
      const data = await res.json();
      if (!data.ok) {
        details.innerHTML = '<div class="dex-empty">Не найдено</div>';
        return;
      }
      details.innerHTML = state.mode === 'pokemon' ? pokemonHtml(data.pokemon) : attackHtml(data.attack);
    } catch (e) {
      details.innerHTML = '<div class="dex-empty">Ошибка загрузки данных</div>';
    }
  }

  function pokemonHtml(p) {
    const stats = p.stats || {};
    const learn = (p.learnset || []).slice(0, 120).map(m => `<tr><td>${Number(m.level || 0)}</td><td><a href="#" data-dex-attack-id="${Number(m.id || 0)}">${esc(m.name)}</a></td><td>${esc(m.type)}</td><td>${Number(m.power || 0) || '-'}</td><td>${Number(m.accuracy || 0) <= 0 ? '∞' : Number(m.accuracy || 0)}</td></tr>`).join('') || '<tr><td colspan="5">Нет атак</td></tr>';
    const egg = (p.eggMoves || []).slice(0, 80).map(m => `<tr><td><a href="#" data-dex-attack-id="${Number(m.id || 0)}">${esc(m.name)}</a></td><td>${esc(m.type || '')}</td></tr>`).join('');
    const habitats = (p.habitats || []).slice(0, 80).map(h => `<li>${esc(h.title || h.name || h)}</li>`).join('');
    return `
      <h2>#${esc(p.code || p.id)} ${esc(p.name)}</h2>
      <div class="dex-badges">${(p.types || []).map(t => `<span>${esc(t)}</span>`).join('')}</div>
      <div class="dex-stats">
        <b>HP</b><span>${Number(stats.hp || 0)}</span><b>ATK</b><span>${Number(stats.atk || 0)}</span><b>DEF</b><span>${Number(stats.def || 0)}</span>
        <b>SP.ATK</b><span>${Number(stats.spAtk || 0)}</span><b>SP.DEF</b><span>${Number(stats.spDef || 0)}</span><b>SPEED</b><span>${Number(stats.speed || 0)}</span>
      </div>
      <h3>Эволюция</h3><p>${esc((p.evolution && p.evolution.text) || 'Нет данных')}</p>
      <h3>Атаки по уровню</h3>
      <div class="dex-table-wrap"><table><thead><tr><th>Ур.</th><th>Атака</th><th>Тип</th><th>Сила</th><th>Точн.</th></tr></thead><tbody>${learn}</tbody></table></div>
      ${egg ? `<h3>Яйцевые атаки</h3><div class="dex-table-wrap"><table><tbody>${egg}</tbody></table></div>` : ''}
      ${habitats ? `<h3>Где обитает</h3><ul>${habitats}</ul>` : ''}`;
  }

  function attackHtml(a) {
    const statEffects = (a.statEffects || []).map(e => `<li>${esc(e.target)}: ${esc(e.stat || e.label)} ${e.kind === 'minus' ? '-' : '+'}${Number(e.value || e.delta || 0)}</li>`).join('') || '<li>Нет</li>';
    const secondary = (a.secondaryEffects || []).map(e => `<li>${esc(e.description || JSON.stringify(e))}</li>`).join('') || '<li>Нет</li>';
    const learned = (a.learnedBy || []).slice(0, 100).map(p => `<tr><td>${Number(p.level || 0)}</td><td><a href="#" data-dex-pokemon-id="${Number(p.id || 0)}">#${Number(p.id || 0)} ${esc(p.name)}</a></td></tr>`).join('') || '<tr><td colspan="2">Нет данных</td></tr>';
    const acc = Number(a.accuracy || 0) <= 0 ? '∞' : Number(a.accuracy || 0) + '%';
    return `
      <h2>#${Number(a.id || 0)} ${esc(a.name)}</h2>
      <div class="dex-badges"><span>${esc(a.type)}</span><span>${esc(a.categoryName || ('Категория ' + a.category))}</span><span>PP ${Number(a.pp || 0)}</span></div>
      <div class="dex-stats"><b>Сила</b><span>${Number(a.power || 0) || '-'}</span><b>Точность</b><span>${acc}</span><b>Приоритет</b><span>${Number(a.priority || 0)}</span></div>
      <p>${esc(a.description || a.short || '')}</p>
      <h3>Стат-эффекты</h3><ul>${statEffects}</ul>
      <h3>Доп. эффекты</h3><ul>${secondary}</ul>
      <h3>Кто учит</h3>
      <div class="dex-table-wrap"><table><thead><tr><th>Ур.</th><th>Покемон</th></tr></thead><tbody>${learned}</tbody></table></div>`;
  }

  tabs.forEach(tab => tab.addEventListener('click', () => open(tab.dataset.dexTab)));
  input.addEventListener('input', () => { clearTimeout(input._t); input._t = setTimeout(search, 250); });
  list.addEventListener('click', e => {
    const row = e.target.closest('[data-id]');
    if (row) showDetails(row.dataset.id);
  });
  details.addEventListener('click', e => {
    const attack = e.target.closest('[data-dex-attack-id]');
    if (attack) { e.preventDefault(); open('attacks', Number(attack.dataset.dexAttackId || 0)); return; }
    const poke = e.target.closest('[data-dex-pokemon-id]');
    if (poke) { e.preventDefault(); open('pokemon', Number(poke.dataset.dexPokemonId || 0)); }
  });
  close.addEventListener('click', closeDex);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeDex(); });

  document.querySelectorAll('[data-open-dex]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      open(btn.dataset.openDex || 'pokemon');
    });
  });

  // Compatibility: catch old legacy links in logs/descriptions and open new overlay instead of placeholder pages.
  document.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a) return;
    const href = a.getAttribute('href') || '';
    let m = href.match(/[?&]go=pokedex[^#]*[?&]id=(\d+)/i) || href.match(/\/game\/pokedex[^#]*[?&]id=(\d+)/i);
    if (m) { e.preventDefault(); open('pokemon', Number(m[1])); return; }
    m = href.match(/[?&]go=atk[^#]*[?&]id=(\d+)/i) || href.match(/\/game\/atk[^#]*[?&]id=(\d+)/i) || href.match(/\/game\/attack[^#]*[?&]id=(\d+)/i);
    if (m) { e.preventDefault(); open('attacks', Number(m[1])); }
  });

  window.PokemonDex = {
    open,
    openPokemon: (id) => open('pokemon', id),
    openAttack: (id) => open('attacks', id),
    close: closeDex,
  };
})();
