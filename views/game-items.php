<?php
use Pokemon8\View\View;

$itemIconIndexPath = APP_ROOT . '/public/img/items/index.json';
$itemIconIndex = is_file($itemIconIndexPath)
    ? (json_decode((string) file_get_contents($itemIconIndexPath), true) ?: [])
    : [];
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Инвентарь - League of Pokemons</title>
  <style>
    :root { --line:#b7bcc3; --panel:#e6e8ea; --cell:#f5f7f8; --text:#1d2430; --muted:#546173; --accent:#2a6fbd; }
    * { box-sizing: border-box; }
    body { margin:0; background:#d9dde0; color:var(--text); font:14px/1.35 Tahoma, Arial, sans-serif; }
    .page { max-width:1020px; margin:0 auto; padding:14px 10px 24px; }
    .head { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .head a { color:#1e5da3; font-weight:700; text-decoration:none; }
    .window { background:linear-gradient(#f1f2f3,#dfe2e5); border:1px solid #949aa1; border-radius:7px; box-shadow:0 8px 28px rgba(0,0,0,.2); overflow:hidden; }
    .toolbar { display:flex; align-items:center; gap:6px; padding:6px; border-bottom:1px solid #b6bcc3; background:#eceff1; }
    .ico { width:26px; height:26px; border:1px solid #8f969e; border-radius:4px; background:linear-gradient(#fff,#dde3e8); display:grid; place-items:center; font-size:13px; cursor:pointer; }
    .search { margin-left:auto; display:flex; align-items:center; gap:6px; }
    .search input { width:280px; height:28px; border:1px solid #9aa2ab; border-radius:4px; padding:0 10px; }
    .grid-wrap { padding:6px; background:#d9dde0; }
    .inv-grid { display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:4px; }
    .slot { aspect-ratio:1/1; border:1px solid #a8b0b8; border-radius:3px; background:radial-gradient(circle at 50% 45%, #fff 0%, #f7f7f7 35%, #ebedf0 100%); position:relative; padding:2px; cursor:pointer; }
    .slot:hover { border-color:#6f9fcf; }
    .slot.selected { border-color:#2a6fbd; box-shadow:0 0 0 1px #2a6fbd inset; }
    .slot.empty { cursor:default; opacity:.8; }
    .slot img { width:100%; height:100%; object-fit:contain; image-rendering:pixelated; }
    .cnt { position:absolute; left:3px; bottom:1px; font-size:11px; font-weight:700; color:#2a2a2a; text-shadow:0 1px 0 #fff; }
    .bottombar { display:flex; align-items:center; gap:8px; padding:7px 8px; border-top:1px solid #b6bcc3; background:#eceff1; }
    .nav { display:flex; gap:6px; }
    .nav button, .act button { min-width:34px; height:30px; border:1px solid #9098a1; border-radius:4px; background:linear-gradient(#fff,#dde3e8); cursor:pointer; }
    .act { display:flex; gap:6px; margin-left:6px; }
    .slots { margin-left:auto; font-weight:700; letter-spacing:.2px; }
    .gem { margin-left:12px; border:1px solid #9098a1; border-radius:4px; min-width:68px; height:30px; display:grid; place-items:center; background:linear-gradient(#fff,#dde3e8); color:#1b74bc; font-weight:700; }
    .side { margin-top:10px; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .card { background:#edf0f2; border:1px solid #b4bbc2; border-radius:6px; padding:8px; }
    .card h3 { margin:0 0 6px; font-size:14px; }
    .preview { display:grid; grid-template-columns:42px 1fr; gap:8px; align-items:center; }
    .preview img { width:36px; height:36px; object-fit:contain; image-rendering:pixelated; }
    .preview .nm { font-weight:700; }
    .preview .ds { font-size:12px; color:var(--muted); margin-top:2px; }
    .controls { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:8px; }
    .controls input, .controls select, .controls button { height:32px; border:1px solid #97a0aa; border-radius:4px; padding:0 8px; font:inherit; background:#fff; }
    .controls button { background:linear-gradient(#fff,#dde3e8); cursor:pointer; font-weight:700; color:#1f4e87; }
    .controls button[disabled] { opacity:.5; cursor:not-allowed; }
    .pager { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; }
    .pager button { min-width:32px; height:28px; border:1px solid #9098a1; border-radius:4px; background:#fff; cursor:pointer; }
    .pager button.active { background:#2a6fbd; color:#fff; border-color:#2a6fbd; }
    .hint { margin-top:6px; font-size:12px; color:var(--muted); }
    @media (max-width: 980px) { .search input{width:180px;} .side{grid-template-columns:1fr;} }
  </style>
</head>
<body data-csrf="<?= View::e((string) $csrf) ?>">
  <main class="page">
    <div class="head">
      <div>
        <a href="/game">Игровой мир</a>
        <span class="muted"> / Инвентарь</span>
      </div>
      <div class="muted">Всего предметов: <span id="totalCount"><?= View::e((string) $total) ?></span></div>
    </div>
    <section class="window">
      <header class="toolbar">
        <button class="ico" type="button">☰</button>
        <button class="ico" type="button">⚙</button>
        <button class="ico" type="button">◈</button>
        <button class="ico" type="button">✦</button>
        <button class="ico" type="button">⚑</button>
        <button class="ico" type="button">★</button>
        <button class="ico" type="button">⚒</button>
        <button class="ico" type="button">❖</button>
        <div class="search">
          <input id="searchInput" placeholder="Начните вводить название">
          <button class="ico" id="searchReset" type="button">✕</button>
        </div>
      </header>
      <div class="grid-wrap">
        <section class="inv-grid" id="invGrid"></section>
      </div>
      <footer class="bottombar">
        <div class="nav">
          <button id="refreshBtn" type="button">⟳</button>
          <button id="prevPageBtn" type="button">≪</button>
          <button id="nextPageBtn" type="button">≫</button>
        </div>
        <div class="act">
          <button id="dropBtn" type="button" disabled>🖐</button>
          <button id="useBtn" type="button" disabled>✋</button>
        </div>
        <div class="slots">СЛОТОВ ЗАНЯТО: <span id="occupiedCount">0</span></div>
        <div class="gem">💎 +</div>
      </footer>
    </section>

    <div class="side">
      <section class="card">
        <h3>Выбранный предмет</h3>
        <div class="preview">
          <img id="previewImg" src="/img/blank.gif" alt="">
          <div>
            <div class="nm" id="previewName">Ничего не выбрано</div>
            <div class="ds" id="previewDesc">Выбери слот, чтобы увидеть детали.</div>
          </div>
        </div>
        <div class="controls">
          <input id="amountDrop" type="number" min="1" placeholder="Кол-во применить">
          <input id="amountClan" type="number" min="1" placeholder="Кол-во (клан)">
          <button id="clanBtn" type="button" disabled>Отдать клану</button>
          <button id="dressBtn" type="button" disabled>Одеть</button>
          <button id="undressBtn" type="button">Снять</button>
          <button id="openBtn" type="button" disabled>Открыть</button>
        </div>
      </section>
      <section class="card">
        <h3>Пагинация и покемон</h3>
        <div class="controls">
          <select id="pokemonSelect">
            <?php foreach ($pokemons as $pokemon): ?>
              <option
                value="<?= View::e((string) ($pokemon['id'] ?? '0')) ?>"
                data-item-id="<?= View::e((string) ($pokemon['equipped_item_id'] ?? '0')) ?>"
                data-item-name="<?= View::e((string) ($pokemon['equipped_item_name'] ?? '')) ?>"
              ><?= View::e(strip_tags((string) ($pokemon['names'] ?? 'Покемон'))) ?></option>
            <?php endforeach; ?>
          </select>
          <input id="pageInfo" value="1/1" readonly>
        </div>
        <nav class="pager" id="pager"></nav>
        <div id="toast" class="hint"></div>
      </section>
    </div>
  </main>
  <script>
    const itemIconIndex = <?= json_encode($itemIconIndex, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}' ?>;
    const csrf = document.body.dataset.csrf || '';
    const state = { page: 1, pages: 1, selected: null, items: [] };

    function setToast(text) {
      const toast = document.getElementById('toast');
      toast.textContent = text;
    }

    function clearToast() {
      document.getElementById('toast').textContent = '';
    }

    function itemIconSrc(item) {
      const id = Number(item && item.item_id || 0);
      const indexed = itemIconIndex[String(id)];
      return '/public/img/items/' + (indexed || (id + '.png'));
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

      if (!item) {
        imgEl.src = '/img/blank.gif';
        nameEl.textContent = 'Ничего не выбрано';
        descEl.textContent = 'Выбери слот, чтобы увидеть детали.';
        [useBtn, dressBtn, openBtn, dropBtn, clanBtn].forEach((button) => button.disabled = true);
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
      useBtn.disabled = !targetUse;
      dressBtn.disabled = !(elementary && String(item.dress || '0') === '1');
      openBtn.disabled = !(String(item.elementary || '0') !== '1');
      dropBtn.disabled = String(item.delet || '0') === '1';
      clanBtn.disabled = String(item.delet || '0') === '1';
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

      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body
      });
      return response.json();
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
      setToast(payload && payload.message ? payload.message : 'Готово.');
      if (payload && payload.ok) {
        await loadPage(state.page);
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
      const response = await fetch('/api/inventory/page?page=' + encodeURIComponent(page), { credentials: 'same-origin' });
      const payload = await response.json();
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
      document.getElementById('openBtn').addEventListener('click', note);
      document.getElementById('dropBtn').addEventListener('click', note);
      document.getElementById('clanBtn').addEventListener('click', note);
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
  </script>
</body>
</html>
