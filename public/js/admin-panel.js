(() => {
  const root = document.querySelector('.admin-shell');
  if (!root) return;

  const csrf = root.dataset.csrf || '';
  const state = { lookups: null };

  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[char]));

  const post = async (url, form) => {
    const body = new URLSearchParams(new FormData(form));
    body.set('_csrf', csrf);
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json'},
      body
    });
    return response.json();
  };

  const getJson = async url => {
    const response = await fetch(url, {credentials: 'same-origin', headers: {'Accept': 'application/json'}});
    return response.json();
  };

  const status = (id, text, bad = false) => {
    const box = document.getElementById(id);
    if (!box) return;
    box.textContent = text || '';
    box.classList.toggle('is-bad', !!bad);
  };

  const switchTab = tab => {
    document.querySelectorAll('[data-admin-tab]').forEach(btn => {
      btn.classList.toggle('is-active', btn.dataset.adminTab === tab);
    });
    document.querySelectorAll('[data-admin-panel]').forEach(panel => {
      panel.classList.toggle('is-active', panel.dataset.adminPanel === tab);
    });
  };

  const fillLookup = (select, rows) => {
    const first = select.querySelector('option')?.outerHTML || '<option value="0">Любой</option>';
    select.innerHTML = first + rows.map(row => `<option value="${Number(row.id || 0)}">${esc(row.name || row.title || row.id)}</option>`).join('');
  };

  const loadLookups = async () => {
    const data = await getJson('/api/admin/lookups');
    if (!data.ok) return;
    state.lookups = data.lookups || {};
    document.querySelectorAll('[data-lookup]').forEach(select => {
      fillLookup(select, state.lookups[select.dataset.lookup] || []);
    });
  };

  const renderItems = items => {
    const list = document.getElementById('itemList');
    if (!list) return;
    list.innerHTML = (items || []).map(item => `
      <article class="admin-row">
        <img src="${esc(item.icon || '/public/img/ui/menu-inventory.png')}" alt="">
        <div>
          <b>#${Number(item.id || 0)} ${esc(item.name || 'Предмет')}</b>
          <small>${esc(item.tittle || '')}</small>
          <small>category ${Number(item.category || 0)} · uses ${Number(item.uses || 0)} · dress ${Number(item.dress || 0)} · battle ${Number(item.battleuse || 0)}</small>
        </div>
        <button type="button" data-edit-item="${Number(item.id || 0)}">В форму</button>
      </article>
    `).join('') || '<div class="admin-row"><div></div><b>Предметы не найдены</b><span></span></div>';

    list.querySelectorAll('[data-edit-item]').forEach(btn => {
      btn.addEventListener('click', () => {
        const item = items.find(row => Number(row.id) === Number(btn.dataset.editItem));
        const form = document.getElementById('itemForm');
        if (!item || !form) return;
        ['id', 'name', 'tittle', 'category', 'uses', 'dress', 'battleuse', 'torg', 'dopolnen'].forEach(key => {
          if (form.elements[key]) form.elements[key].value = item[key] ?? '';
        });
        status('itemStatus', 'Предмет загружен в форму.');
      });
    });
  };

  const loadItems = async (query = '') => {
    const data = await getJson('/api/admin/items?q=' + encodeURIComponent(query));
    if (data.ok) renderItems(data.items || []);
  };

  const renderDrops = rules => {
    const list = document.getElementById('dropList');
    if (!list) return;
    list.innerHTML = (rules || []).map(rule => {
      const source = rule.pokebuild_id > 0
        ? `слот #${Number(rule.pokebuild_id)}`
        : (rule.pokemon_base_id > 0 ? `покемон #${Number(rule.pokemon_base_id)} ${esc(rule.pokemon_name || '')}` : 'любой источник');
      return `
        <article class="admin-row">
          <img src="${esc(rule.item_icon || '/public/img/ui/menu-inventory.png')}" alt="">
          <div>
            <b>#${Number(rule.id || 0)} ${esc(rule.item_name || ('item ' + rule.item_id))}</b>
            <small>${esc(rule.location_title || 'Любая локация')} · ${source}</small>
            <small>${Number(rule.chance_percent || 0)}% · ${Number(rule.min_count || 1)}-${Number(rule.max_count || 1)} шт. · ${esc(rule.time_start || '')}-${esc(rule.time_end || '')} · ${Number(rule.enabled || 0) ? 'вкл' : 'выкл'}</small>
          </div>
          <button type="button" data-delete-drop="${Number(rule.id || 0)}">Удалить</button>
        </article>`;
    }).join('') || '<div class="admin-row"><div></div><b>Правил дропа пока нет</b><span></span></div>';

    list.querySelectorAll('[data-delete-drop]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const form = new FormData();
        form.set('_csrf', csrf);
        form.set('id', btn.dataset.deleteDrop || '0');
        const response = await fetch('/api/admin/drop-rules/delete', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'Accept': 'application/json'},
          body: new URLSearchParams(form)
        });
        const data = await response.json();
        status('dropStatus', data.message || '', !data.ok);
        if (data.ok) loadDrops();
      });
    });
  };

  const loadDrops = async () => {
    const data = await getJson('/api/admin/drop-rules');
    if (data.ok) renderDrops(data.rules || []);
  };

  document.querySelectorAll('[data-admin-tab]').forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.adminTab || 'items'));
  });

  document.getElementById('itemSearchForm')?.addEventListener('submit', event => {
    event.preventDefault();
    loadItems(new FormData(event.currentTarget).get('q') || '');
  });

  document.getElementById('itemForm')?.addEventListener('submit', async event => {
    event.preventDefault();
    const data = await post('/api/admin/items/save', event.currentTarget);
    status('itemStatus', data.message || '', !data.ok);
    if (data.ok) {
      event.currentTarget.reset();
      event.currentTarget.elements.dopolnen.value = 'admin';
      loadItems();
    }
  });

  document.getElementById('dropForm')?.addEventListener('submit', async event => {
    event.preventDefault();
    const data = await post('/api/admin/drop-rules/save', event.currentTarget);
    status('dropStatus', data.message || '', !data.ok);
    if (data.ok) {
      event.currentTarget.reset();
      event.currentTarget.elements.enabled.value = '1';
      event.currentTarget.elements.time_start.value = '00:00';
      event.currentTarget.elements.time_end.value = '23:59';
      loadDrops();
    }
  });

  loadLookups();
  loadItems();
  loadDrops();
})();
