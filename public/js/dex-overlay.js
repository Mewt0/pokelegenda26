(() => {
  const overlay = document.getElementById('dexOverlay');
  if (!overlay) return;

  const state = {
    mode: 'pokemon',
    query: '',
    items: [],
    selectedPokemon: null,
    selectedAttack: null,
    pokemonShiny: false,
  };

  const tabs = overlay.querySelectorAll('[data-dex-tab]');
  const input = document.getElementById('dexSearchInput');
  const list = document.getElementById('dexList');
  const details = document.getElementById('dexDetails');
  const close = document.getElementById('dexCloseBtn');

  const esc = (v) => String(v ?? '').replace(/[&<>'"]/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
  }[c]));

  const endpoint = () => state.mode === 'pokemon' ? '/api/dex/pokemon' : '/api/dex/attacks';
  const detailEndpoint = (id) => state.mode === 'pokemon'
    ? `/api/dex/pokemon/show?id=${encodeURIComponent(id)}`
    : `/api/dex/attack/show?id=${encodeURIComponent(id)}`;

  function setMode(mode) {
    state.mode = mode === 'attacks' || mode === 'attack' || mode === 'move' ? 'attacks' : 'pokemon';
    tabs.forEach(t => t.classList.toggle('is-active', t.dataset.dexTab === state.mode));
    state.pokemonShiny = false;
  }

  async function open(mode = 'pokemon', id = 0) {
    setMode(mode);
    overlay.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-open');
    input.value = state.query;
    if (Number(id) > 0) {
      list.innerHTML = '<div class="dex-empty">Загружаю...</div>';
      await search(false);
      await showDetails(Number(id));
      return;
    }
    await search(true);
  }

  function closeDex() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  }

  async function search(autoOpenFirst = true) {
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
      if (autoOpenFirst && state.items[0]) {
        await showDetails(state.items[0].id);
      } else if (autoOpenFirst) {
        details.innerHTML = '<div class="dex-empty">Ничего не найдено</div>';
      }
    } catch (e) {
      list.innerHTML = '<div class="dex-empty">Ошибка сети</div>';
      details.innerHTML = '<div class="dex-empty">Проверь маршруты /api/dex/...</div>';
    }
  }

  function typeClass(type) {
    return 'type-' + String(type || 'normal').toLowerCase().replace(/[^a-z0-9]+/g, '-');
  }

  const TYPE_ALIASES = {
    normal: 'normal', fire: 'fire', water: 'water', grass: 'grass', electric: 'electric', ice: 'ice',
    fighting: 'fighting', fight: 'fighting', poison: 'poison', ground: 'ground', flying: 'flying',
    psychic: 'psychic', bug: 'bug', rock: 'rock', ghost: 'ghost', dragon: 'dragon', dark: 'dark',
    steel: 'steel', fairy: 'fairy',
  };

  function typeKey(type) {
    const raw = String(type || 'normal').toLowerCase().replace(/[^a-z0-9]+/g, '');
    return TYPE_ALIASES[raw] || raw || 'normal';
  }

  function typeIconSrc(type) {
    const key = typeKey(type);
    const known = {
      bug: true, dark: true, dragon: true, electric: true, fighting: true, fire: true,
      flying: true, ghost: true, grass: true, ground: true, ice: true, normal: true,
      poison: true, psychic: true, rock: true, steel: true, water: true,
    };
    return `/public/img/types/${known[key] ? key : 'normal'}.png`;
  }

  function iconImg(src, alt, className = '') {
    return `<img class="${esc(className)}" src="${esc(src)}" alt="${esc(alt)}" loading="lazy">`;
  }

  function sectionTitle(icon, title) {
    return `<span class="dex-section-title">${iconImg(`/public/img/ui/dex/${icon}.png`, title, 'dex-section-icon')}<span>${esc(title)}</span></span>`;
  }

  function typeBadge(type, mini = false) {
    const label = type || 'Normal';
    return `<span class="dex-type ${mini ? 'mini ' : ''}${typeClass(label)}">${iconImg(typeIconSrc(label), label, 'dex-type-icon')}<span>${esc(label)}</span></span>`;
  }

  function imgTag(src, alt, className = '', fallback = '') {
    const fb = fallback ? ` data-fallback="${esc(fallback)}"` : '';
    return `<img class="${esc(className)}" src="${esc(src)}" alt="${esc(alt)}"${fb} loading="lazy">`;
  }

  function renderList() {
    list.innerHTML = state.items.map(item => {
      if (state.mode === 'pokemon') {
        const sprites = item.sprites || {};
        const types = item.types || [];
        return `
          <button type="button" class="dex-row dex-pokemon-row" data-id="${Number(item.id)}">
            <div class="dex-row-info">
              <b>#${esc(item.code || item.number || item.id)} ${esc(item.name)}</b>
              <div class="dex-row-types">${types.map(t => typeBadge(t, true)).join('')}</div>
            </div>
            <div class="dex-row-thumb">
              ${imgTag(sprites.normal || `/pok/normal/${esc(item.code || item.id)}.png`, item.name, '', sprites.fallbackNormal || '')}
            </div>
          </button>`;
      }
      const acc = Number(item.accuracy || 0) <= 0 ? '∞' : Number(item.accuracy || 0);
      const tClass = typeClass(item.type);
      return `
        <button type="button" class="dex-row dex-attack-row" data-id="${Number(item.id)}">
          <div class="attack-row-icon ${tClass}">${iconImg(typeIconSrc(item.type), item.type || 'Normal')}</div>
          <div class="dex-row-info">
            <b>#${Number(item.id)} ${esc(item.name)}</b>
            <div class="dex-row-types">${typeBadge(item.type, true)}<span class="dex-mini-muted">${esc(item.categoryName || '')}</span></div>
          </div>
          <div class="attack-row-stats">
            <span><small>Сила</small><b>${Number(item.power || 0) || '-'}</b></span>
            <span><small>Точн.</small><b>${acc}</b></span>
            <span><small>PP</small><b>${Number(item.pp || 0)}</b></span>
          </div>
        </button>`;
    }).join('') || '<div class="dex-empty">Ничего не найдено</div>';
  }

  async function showDetails(id) {
    state.pokemonShiny = false;
    details.innerHTML = '<div class="dex-empty">Загрузка...</div>';
    try {
      const res = await fetch(detailEndpoint(id), { credentials: 'same-origin' });
      const data = await res.json();
      if (!data.ok) {
        details.innerHTML = '<div class="dex-empty">Не найдено</div>';
        return;
      }
      list.querySelectorAll('[data-id]').forEach(row => row.classList.toggle('is-selected', Number(row.dataset.id) === Number(id)));
      if (state.mode === 'pokemon') {
        state.selectedPokemon = data.pokemon;
        state.selectedAttack = null;
        renderPokemonDetails();
      } else {
        state.selectedAttack = data.attack;
        state.selectedPokemon = null;
        renderAttackDetails();
      }
    } catch (e) {
      details.innerHTML = '<div class="dex-empty">Ошибка загрузки данных</div>';
    }
  }

  function renderPokemonDetails() {
    const p = state.selectedPokemon;
    if (!p) return;
    details.innerHTML = pokemonHtml(p);
  }

  function activeSprite(entity) {
    const sprites = entity.sprites || {};
    return state.pokemonShiny ? (sprites.shiny || sprites.normal || '') : (sprites.normal || sprites.shiny || '');
  }

  function activeFallback(entity) {
    const sprites = entity.sprites || {};
    return state.pokemonShiny ? (sprites.fallbackShiny || sprites.fallbackNormal || '') : (sprites.fallbackNormal || sprites.fallbackShiny || '');
  }

  function pokemonHtml(p) {
    const stats = p.stats || {};
    const info = p.info || {};
    const types = p.types || [];
    const sprite = activeSprite(p);
    const fallback = activeFallback(p);
    const viewLabel = state.pokemonShiny ? 'Shiny вид' : 'Обычный вид';
    const viewHint = state.pokemonShiny ? 'Нажми, чтобы показать обычный вид' : 'Нажми, чтобы показать shiny вид';

    const evolutions = (p.evolutions || []).map((evo, idx, arr) => {
      const evoSprite = activeSprite(evo);
      const evoFallback = activeFallback(evo);
      const arrow = idx < arr.length - 1 ? `<div class="dex-evo-arrow"><span>→</span>${arr[idx + 1]?.level ? `<small>Ур. ${Number(arr[idx + 1].level)}</small>` : ''}</div>` : '';
      return `
        <div class="dex-evo-node ${evo.selected ? 'is-selected' : ''}" data-dex-pokemon-id="${Number(evo.id)}">
          <div class="dex-evo-img">${imgTag(evoSprite, evo.name, '', evoFallback)}</div>
          <b>#${esc(evo.code || evo.number || evo.id)}</b>
          <span>${esc(evo.name)}</span>
        </div>${arrow}`;
    }).join('') || '<div class="dex-empty">Нет эволюций</div>';

    const learn = (p.learnset || []).slice(0, 160).map(m => `
      <tr>
        <td>${Number(m.level || 0)}</td>
        <td><a href="#" data-dex-attack-id="${Number(m.id || 0)}">${esc(m.name)}</a></td>
        <td>${typeBadge(m.type, true)}</td>
        <td>${esc(m.categoryName || categoryName(m.category))}</td>
        <td>${Number(m.power || 0) || '-'}</td>
        <td>${Number(m.accuracy || 0) <= 0 ? '∞' : Number(m.accuracy || 0)}</td>
        <td>${Number(m.pp || 0)}</td>
      </tr>`).join('') || '<tr><td colspan="7">Нет атак</td></tr>';

    const egg = (p.eggMoves || []).slice(0, 100).map(m => `
      <tr>
        <td><a href="#" data-dex-attack-id="${Number(m.id || 0)}">${esc(m.name)}</a></td>
        <td>${typeBadge(m.type || 'Normal', true)}</td>
        <td>${Number(m.power || 0) || '-'}</td>
        <td>${Number(m.accuracy || 0) <= 0 ? '∞' : Number(m.accuracy || 0)}</td>
        <td>${Number(m.pp || 0)}</td>
      </tr>`).join('');

    const habitats = (p.habitats || []).slice(0, 80).map(h => `<li>${esc(h.title || h.name || h)}${h.lvl ? ` <small>Lv.${Number(h.lvl)}</small>` : ''}</li>`).join('');

    return `
      <section class="dex-pokemon-detail">
        <div class="dex-detail-topline">
          <h2>#${esc(p.code || p.number || p.id)} ${esc(p.name)}</h2>
          <div class="dex-detail-actions">
            <button type="button" class="dex-ghost-btn" title="Будет подключено позже">☆ В избранное</button>
            <button type="button" class="dex-ghost-btn" title="Будет подключено позже">🔊 Крик</button>
          </div>
        </div>

        <div class="dex-hero-grid">
          <div class="dex-visual-card">
            <button type="button" class="dex-sprite-toggle" title="${esc(viewHint)}" data-dex-toggle-shiny="1">
              ${imgTag(sprite, p.name, 'dex-main-sprite', fallback)}
            </button>
            <button type="button" class="dex-view-chip" data-dex-toggle-shiny="1">${state.pokemonShiny ? '✦' : '✧'} ${esc(viewLabel)}</button>
          </div>

          <div class="dex-data-card">
            <div class="dex-badges">${types.map(t => typeBadge(t)).join('')}</div>
            <div class="dex-stats modern">
              <div><b>HP</b><span>${Number(stats.hp || 0)}</span></div>
              <div><b>ATK</b><span>${Number(stats.atk || 0)}</span></div>
              <div><b>DEF</b><span>${Number(stats.def || 0)}</span></div>
              <div><b>SP.ATK</b><span>${Number(stats.spAtk || 0)}</span></div>
              <div><b>SP.DEF</b><span>${Number(stats.spDef || 0)}</span></div>
              <div><b>SPEED</b><span>${Number(stats.speed || 0)}</span></div>
            </div>
            <div class="dex-info-evo-grid">
              <div class="dex-info-block">
                <h3>${sectionTitle('info', 'Информация')}</h3>
                <p><b>Поколение:</b> ${Number(info.generation || 1)}</p>
                <p><b>Категория:</b> ${esc(info.category || 'Pokémon')}</p>
              </div>
              <div class="dex-evo-block">
                <h3>${sectionTitle('evolution', 'Эволюция')}</h3>
                <div class="dex-evo-chain">${evolutions}</div>
              </div>
            </div>
          </div>
        </div>

        <h3>${sectionTitle('pokedex', 'Описание')}</h3>
        <p class="dex-description">${esc(p.description || '')}</p>

        <h3>${sectionTitle('moves', 'Атаки по уровню')}</h3>
        <div class="dex-table-wrap"><table><thead><tr><th>Ур.</th><th>Атака</th><th>Тип</th><th>Категория</th><th>Сила</th><th>Точн.</th><th>PP</th></tr></thead><tbody>${learn}</tbody></table></div>
        ${egg ? `<h3>${sectionTitle('egg', 'Яйцевые атаки')}</h3><div class="dex-table-wrap"><table><thead><tr><th>Атака</th><th>Тип</th><th>Сила</th><th>Точн.</th><th>PP</th></tr></thead><tbody>${egg}</tbody></table></div>` : ''}
        ${habitats ? `<h3>${sectionTitle('habitat', 'Где обитает')}</h3><ul class="dex-habitats">${habitats}</ul>` : ''}
      </section>`;
  }

  function categoryName(category) {
    return Number(category) === 1 ? 'Физическая' : (Number(category) === 2 ? 'Специальная' : 'Статусная');
  }

  function renderAttackDetails() {
    const a = state.selectedAttack;
    if (!a) return;
    details.innerHTML = attackHtml(a);
  }

  function flagChip(label, active) {
    return `<span class="attack-flag ${active ? 'is-on' : 'is-off'}"><b>${active ? '✓' : '×'}</b> ${esc(label)}</span>`;
  }

  function formatAttackEffectList(items) {
    if (!items || !items.length) return '<div class="attack-empty-effect">Нет</div>';
    return items.map(e => `<div class="attack-effect-row"><span>${esc(e.label || e.effect || 'Эффект')}</span><b>${esc(e.chance || e.value || '—')}</b><p>${esc(e.description || '')}</p></div>`).join('');
  }

  function attackHtml(a) {
    const type = a.type || 'Normal';
    const typeCls = typeClass(type);
    const category = a.categoryName || categoryName(a.category);
    const acc = Number(a.accuracy || 0) <= 0 ? '∞' : Number(a.accuracy || 0) + '%';
    const power = Number(a.power || 0) || '—';
    const tags = a.tags || {};
    const flags = a.flags || {};
    const statEffects = formatAttackEffectList(a.statEffects || []);
    const secondary = formatAttackEffectList(a.secondaryEffects || []);
    const learnedRows = (a.learnedBy || []).slice(0, 160).map(p => {
      const sprite = p.sprite || '/public/img/ui/dex/pokedex.png';
      return `<tr>
        <td><img class="attack-learn-sprite" src="${esc(sprite)}" alt="${esc(p.name || '')}" loading="lazy"></td>
        <td><a href="#" data-dex-pokemon-id="${Number(p.id || 0)}">#${esc(p.code || p.id)} ${esc(p.name)}</a></td>
        <td>${Number(p.level || 0)} ур.</td>
      </tr>`;
    }).join('') || '<tr><td colspan="3">Нет данных</td></tr>';

    return `
      <section class="dex-attack-detail attack-theme">
        <div class="attack-detail-head">
          <div class="attack-big-icon ${typeCls}">${iconImg(typeIconSrc(type), type || 'Normal')}</div>
          <div class="attack-title-block">
            <h2>#${Number(a.id || 0)} ${esc(a.name)}</h2>
            <div class="dex-badges attack-badges">
              ${typeBadge(type)}
              <span>${esc(category)}</span>
              <span>PP ${Number(a.pp || 0)}</span>
            </div>
            <p>${esc(a.description || a.short || 'Описание атаки не заполнено.')}</p>
          </div>
        </div>

        <div class="attack-metric-grid">
          <div><b>Категория</b><span>${esc(category)}</span></div>
          <div><b>Сила</b><span>${power}</span></div>
          <div><b>Точность</b><span>${acc}</span></div>
          <div><b>PP</b><span>${Number(a.pp || 0)}</span></div>
          <div><b>Приоритет</b><span>${Number(a.priority || 0)}</span></div>
          <div><b>Цель</b><span>${esc(a.targetName || 'Противник')}</span></div>
        </div>

        <div class="attack-tag-card">
          <h3>${sectionTitle('attackdex', 'Метки атаки')}</h3>
          <div class="attack-flags">
            ${flagChip('Контактная', !!tags.contact)}
            ${flagChip('Блокируется', !!tags.blocked)}
            ${flagChip('Отражается', !!tags.reflectable)}
            ${flagChip('Многоударная', !!tags.multiHit)}
            ${flagChip('Двухходовая', !!tags.twoTurn)}
            ${flagChip('Отдача', !!tags.recoil)}
          </div>
        </div>

        <div class="attack-info-grid">
          <div class="attack-info-card">
            <h3>${sectionTitle('info', 'Детали')}</h3>
            <p>${esc(a.details || a.effectText || a.description || 'Дополнительные детали отсутствуют.')}</p>
            <div class="attack-code-lines">
              ${flags.dopEffect && flags.dopEffect !== '0' ? `<span>DOP: ${esc(flags.dopEffect)}</span>` : ''}
              ${Number(flags.status || 0) > 0 ? `<span>Статус: #${Number(flags.status)}</span>` : ''}
              ${Number(flags.stati || 0) > 0 ? `<span>ST: #${Number(flags.stati)}</span>` : ''}
            </div>
          </div>
          <div class="attack-info-card">
            <h3>${sectionTitle('moves', 'Стат-эффекты')}</h3>
            ${statEffects}
          </div>
          <div class="attack-info-card">
            <h3>${sectionTitle('moves', 'Доп. эффекты')}</h3>
            ${secondary}
          </div>
        </div>

        <h3>${sectionTitle('pokedex', 'Кто учит эту атаку')}</h3>
        <div class="dex-table-wrap attack-learn-table"><table><thead><tr><th></th><th>Покемон</th><th>Как изучает?</th></tr></thead><tbody>${learnedRows}</tbody></table></div>
      </section>`;
  }

  tabs.forEach(tab => tab.addEventListener('click', () => open(tab.dataset.dexTab)));
  input.addEventListener('input', () => { clearTimeout(input._t); input._t = setTimeout(() => search(true), 250); });
  list.addEventListener('click', e => {
    const row = e.target.closest('[data-id]');
    if (row) showDetails(row.dataset.id);
  });
  details.addEventListener('click', e => {
    const toggle = e.target.closest('[data-dex-toggle-shiny]');
    if (toggle && state.selectedPokemon) {
      e.preventDefault();
      state.pokemonShiny = !state.pokemonShiny;
      renderPokemonDetails();
      return;
    }
    const evo = e.target.closest('.dex-evo-node[data-dex-pokemon-id]');
    if (evo) {
      e.preventDefault();
      showDetails(Number(evo.dataset.dexPokemonId || 0));
      return;
    }
    const attack = e.target.closest('[data-dex-attack-id]');
    if (attack) { e.preventDefault(); open('attacks', Number(attack.dataset.dexAttackId || 0)); return; }
    const poke = e.target.closest('[data-dex-pokemon-id]');
    if (poke) { e.preventDefault(); open('pokemon', Number(poke.dataset.dexPokemonId || 0)); }
  });

  details.addEventListener('error', e => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement)) return;
    const fallback = img.getAttribute('data-fallback');
    if (fallback && img.src !== fallback) {
      img.src = fallback;
      img.removeAttribute('data-fallback');
      return;
    }
    img.style.display = 'none';
  }, true);

  list.addEventListener('error', e => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement)) return;
    const fallback = img.getAttribute('data-fallback');
    if (fallback && img.src !== fallback) {
      img.src = fallback;
      img.removeAttribute('data-fallback');
    }
  }, true);

  close.addEventListener('click', closeDex);
  overlay.addEventListener('click', e => { if (e.target === overlay) closeDex(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeDex(); });

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
