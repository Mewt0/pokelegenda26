(() => {
'use strict';
const itemIconIndex = JSON.parse(document.getElementById('itemIconIndex').textContent || '{}');
const csrf = document.body.dataset.csrf || '';
const state = { page: 1, pages: 1, selected: null, items: [] };

function setToast(text) {
  const toast = document.getElementById('toast');
  toast.textContent = text;
}

function clearToast() {
  document.getElementById('toast').textContent = '';
}

function itemIconVersion() {
  const script = document.getElementById('itemIconRuntime');
  const src = script && script.dataset ? (script.dataset.indexSrc || '') : '';
  if (!src) return '';
  try {
    return new URL(src, document.baseURI).searchParams.get('v') || '';
  } catch (error) {
    return '';
  }
}

function itemIconSrc(item) {
  const id = Number(item && item.item_id || 0);
  const indexed = itemIconIndex[String(id)];
  const path = '/public/img/items/' + (indexed || (id + '.png'));
  const version = itemIconVersion();
  return version ? path + '?v=' + encodeURIComponent(version) : path;
}

function setItemIcon(img, item) {
  const id = Number(item && item.item_id || 0);
  img.onerror = () => {
    img.onerror = () => { img.src = '/img/blank.gif'; };
    img.src = '/img/items/' + id + '.png';
  };
  img.src = itemIconSrc(item);
}

function itemTitle(item) {
  const count = Number(item.count || 0).toLocaleString('ru-RU');
  return `${item.name || 'Без названия'} x${count}`;
}

function renderPreview() {
  const item = state.selected;
  const nameEl = document.getElementById('previewName');
  const descEl = document.getElementById('previewDesc');
  const imgEl = document.getElementById('previewImg');
  const useBtn = document.getElementById('useBtn');
  const dressBtn = document.getElementById('dressBtn');
  const openBtn = document.getElementById('openBtn');
  const dropBtn = document.getElementById('dropBtn');
  const clanBtn = document.getElementById('clanBtn');
  const commissionBtn = document.getElementById('commissionBtn');

  if (!item) {
    imgEl.src = '/img/blank.gif';
    nameEl.textContent = 'Ничего не выбрано';
    descEl.textContent = 'Выбери слот, чтобы увидеть детали.';
    [useBtn, dressBtn, openBtn, dropBtn, clanBtn, commissionBtn].forEach((button) => button.disabled = true);
    return;
  }

  setItemIcon(imgEl, item);
  nameEl.textContent = itemTitle(item);

  const hints = [];
  if (item.tittle) hints.push(item.tittle);
  if (String(item.dattimer || 'not') !== 'not') hints.push('Есть ограничение по сроку действия.');
  if (String(item.timers || 'not') !== 'not') hints.push('Есть задержка перед повторным использованием.');
  descEl.textContent = hints.length ? hints.join(' ') : 'Описание отсутствует.';

  const elementary = String(item.elementary || '0') === '1';
  const targetUse = item.target_use && item.target_use.enabled === true;
  const giftUse = isGiftItem(item);
  useBtn.disabled = !targetUse;
  dressBtn.disabled = !(elementary && String(item.dress || '0') === '1');
  openBtn.disabled = !giftUse;
  dropBtn.disabled = String(item.delet || '0') === '1';
  clanBtn.disabled = String(item.delet || '0') === '1';
  commissionBtn.disabled = false;
}

function isGiftItem(item) {
  const rule = item && item.target_use ? item.target_use : null;
  if (!rule || rule.enabled !== true) return false;
  return String(rule.target_type || '') === 'gift' || String(rule.effect_key || '') === 'open_gift';
}

function selectedPokemonId() {
  return Number(document.getElementById('pokemonSelect').value || 0);
}

function renderPokemonEquipHint() {
  const select = document.getElementById('pokemonSelect');
  const option = select.options[select.selectedIndex];
  if (!option) return;

  const itemId = Number(option.dataset.itemId || 0);
  const itemName = option.dataset.itemName || '';
  const base = itemId > 0
    ? 'На покемоне: ' + (itemName || ('предмет #' + itemId)) + '.'
    : 'На выбранном покемоне нет предмета.';
  const toast = document.getElementById('toast');
  if (!toast.textContent || toast.dataset.equipHint === '1') {
    toast.textContent = base;
    toast.dataset.equipHint = '1';
  }
}

function updatePokemonOptions(pokemon) {
  if (!Array.isArray(pokemon)) return;
  const select = document.getElementById('pokemonSelect');
  const currentValue = select.value;
  select.innerHTML = '';
  for (const poke of pokemon) {
    const option = document.createElement('option');
    option.value = String(poke.id || 0);
    option.textContent = String(poke.names || 'Покемон').replace(/<[^>]*>/g, '');
    option.dataset.itemId = String(poke.equipped_item_id || 0);
    option.dataset.itemName = String(poke.equipped_item_name || '');
    select.appendChild(option);
  }
  if (currentValue) select.value = currentValue;
}

async function postInventoryAction(url, fields) {
  const body = new URLSearchParams();
  body.set('_csrf', csrf);
  Object.entries(fields || {}).forEach(([key, value]) => body.set(key, String(value)));

  try {
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body
    });
    if (!response.ok) {
      return { ok: false, message: 'Не удалось выполнить действие. Попробуйте ещё раз.' };
    }
    return response.json();
  } catch (error) {
    return { ok: false, message: 'Не удалось выполнить действие. Попробуйте ещё раз.' };
  }
}

async function equipSelectedItem() {
  if (!state.selected) return;
  const pokemonId = selectedPokemonId();
  if (pokemonId <= 0) {
    setToast('Выберите покемона.');
    return;
  }

  const payload = await postInventoryAction('/api/inventory/equip', {
    item_user_id: state.selected.id,
    pokemon_id: pokemonId
  });
  setToast(payload && payload.message ? payload.message : 'Готово.');
  document.getElementById('toast').dataset.equipHint = '';
  if (payload && payload.ok) {
    updatePokemonOptions(payload.pokemon);
    await loadPage(state.page);
    setToast(payload.message || 'Готово.');
  }
}

async function unequipSelectedPokemon() {
  const pokemonId = selectedPokemonId();
  if (pokemonId <= 0) {
    setToast('Выберите покемона.');
    return;
  }

  const payload = await postInventoryAction('/api/inventory/unequip', { pokemon_id: pokemonId });
  setToast(payload && payload.message ? payload.message : 'Готово.');
  document.getElementById('toast').dataset.equipHint = '';
  if (payload && payload.ok) {
    updatePokemonOptions(payload.pokemon);
    await loadPage(state.page);
    setToast(payload.message || 'Готово.');
  }
}

async function applySelectedItem() {
  if (!state.selected || !state.selected.target_use || state.selected.target_use.enabled !== true) {
    setToast('Для этого предмета не настроено применение на покемона.');
    return;
  }
  const pokemonId = selectedPokemonId();
  if (pokemonId <= 0) {
    setToast('Выберите покемона.');
    return;
  }

  const rule = state.selected.target_use;
  const amountInput = document.getElementById('amountDrop');
  const owned = Number(state.selected.count || 1);
  const min = Math.max(1, Number(rule.min_count || 1));
  const max = Math.max(min, Math.min(owned, Number(rule.max_count || owned)));
  let count = Number(amountInput.value || min);
  if (!rule.allow_quantity) count = 1;
  count = Math.max(min, Math.min(max, count));

  const payload = await postInventoryAction('/api/inventory/use-target', {
    item_user_id: state.selected.id,
    pokemon_id: pokemonId,
    count
  });
  const message = payload && payload.message ? payload.message : 'Готово.';
  setToast(message);
  if (payload && payload.ok) {
    await loadPage(state.page);
    setToast(message);
  }
}

async function openSelectedGift() {
  if (!state.selected || !isGiftItem(state.selected)) {
    setToast('Этот предмет нельзя открыть как подарок.');
    return;
  }

  const payload = await postInventoryAction('/api/inventory/open-gift', {
    item_user_id: state.selected.id
  });
  const message = payload && payload.message ? payload.message : 'Готово.';
  setToast(message);
  if (payload && payload.ok) {
    await loadPage(state.page);
    setToast(message);
  }
}

function renderGrid() {
  const grid = document.getElementById('invGrid');
  grid.innerHTML = '';
  const items = state.items.slice(0, 60);
  for (let i = 0; i < 60; i++) {
    const item = items[i] || null;
    const slot = document.createElement('button');
    slot.type = 'button';
    slot.className = 'slot' + (item ? '' : ' empty');

    if (item) {
      slot.innerHTML = '<img alt=""><span class="cnt"></span>';
      setItemIcon(slot.querySelector('img'), item);
      slot.querySelector('.cnt').textContent = Number(item.count || 0).toLocaleString('ru-RU');
      slot.title = itemTitle(item);
      slot.addEventListener('click', () => {
        state.selected = item;
        renderGrid();
        renderPreview();
        clearToast();
      });
      if (state.selected && Number(state.selected.id) === Number(item.id)) {
        slot.classList.add('selected');
      }
    } else {
      slot.innerHTML = '<img alt="" src="/img/blank.gif">';
      slot.disabled = true;
    }
    grid.appendChild(slot);
  }
  document.getElementById('occupiedCount').textContent = String(items.length);
}

function renderPager() {
  const pager = document.getElementById('pager');
  pager.innerHTML = '';
  for (let i = 1; i <= state.pages; i++) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = String(i);
    if (i === state.page) button.className = 'active';
    button.addEventListener('click', () => loadPage(i));
    pager.appendChild(button);
  }
}

async function loadPage(page = 1) {
  let payload = null;
  try {
    const response = await fetch('/api/inventory/page?page=' + encodeURIComponent(page), { credentials: 'same-origin' });
    if (!response.ok) {
      setToast('Не удалось загрузить инвентарь. Попробуйте ещё раз.');
      return;
    }
    payload = await response.json();
  } catch (error) {
    setToast('Не удалось загрузить инвентарь. Попробуйте ещё раз.');
    return;
  }
  if (!payload || payload.ok !== true) {
    setToast(payload && payload.message ? payload.message : 'Не удалось загрузить инвентарь.');
    return;
  }

  state.page = Number(payload.page || 1);
  state.pages = Number(payload.pages || 1);
  state.items = Array.isArray(payload.items) ? payload.items : [];
  state.selected = null;

  document.getElementById('totalCount').textContent = String(payload.total || 0);
  document.getElementById('pageInfo').value = state.page + '/' + state.pages;

  renderGrid();
  renderPager();
  renderPreview();
  clearToast();
}

function bindActionButtons() {
  const note = () => setToast('Действие будет подключено следующим этапом через API.');
  document.getElementById('useBtn').addEventListener('click', applySelectedItem);
  document.getElementById('dressBtn').addEventListener('click', equipSelectedItem);
  document.getElementById('undressBtn').addEventListener('click', unequipSelectedPokemon);
  document.getElementById('openBtn').addEventListener('click', openSelectedGift);
  document.getElementById('dropBtn').addEventListener('click', note);
  document.getElementById('clanBtn').addEventListener('click', note);
  document.getElementById('commissionBtn').addEventListener('click', () => {
    window.location.href = '/game/commission';
  });
  document.getElementById('pokemonSelect').addEventListener('change', () => {
    document.getElementById('toast').dataset.equipHint = '1';
    renderPokemonEquipHint();
  });
  document.getElementById('pokemonSelect').addEventListener('dblclick', unequipSelectedPokemon);
  document.getElementById('refreshBtn').addEventListener('click', () => loadPage(state.page));
  document.getElementById('prevPageBtn').addEventListener('click', () => loadPage(Math.max(1, state.page - 1)));
  document.getElementById('nextPageBtn').addEventListener('click', () => loadPage(Math.min(state.pages, state.page + 1)));
  document.getElementById('searchReset').addEventListener('click', () => {
    document.getElementById('searchInput').value = '';
    renderGrid();
  });
  document.getElementById('searchInput').addEventListener('input', () => {
    const query = document.getElementById('searchInput').value.trim().toLowerCase();
    if (!query) {
      renderGrid();
      return;
    }
    const filtered = state.items.filter((item) => String(item.name || '').toLowerCase().includes(query));
    const cached = state.items;
    state.items = filtered;
    renderGrid();
    state.items = cached;
  });
}

bindActionButtons();
loadPage(1);
renderPokemonEquipHint();
})();
