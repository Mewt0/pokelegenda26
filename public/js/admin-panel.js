(() => {
  const root = document.querySelector('.admin-shell');
  if (!root) return;

  const csrf = root.dataset.csrf || '';
  const state = {
    tab: 'dashboard',
    rows: [],
    selected: null,
    page: 1,
    pagination: null,
    filters: {}
  };

  const $ = selector => document.querySelector(selector);
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));

  const lookupInput = (id, type, placeholder, value = '') => `
    <input id="${id}" type="text" list="${id}List" data-lookup-type="${type}" placeholder="${esc(placeholder)}" value="${esc(value)}" autocomplete="off">
    <datalist id="${id}List"></datalist>
  `;

  function lookupValueLabel(row) {
    const label = row.label || row.name || row.login || '';
    const id = String(row.id || '').trim();
    const cleanLabel = String(label || '').trim();
    if (id !== '' && cleanLabel.match(new RegExp('^#\\s*' + id + '\\b'))) {
      return cleanLabel;
    }
    return `#${id} ${cleanLabel}`.trim();
  }

  function lookupId(value) {
    const raw = String(value || '').trim();
    const match = raw.match(/^#?\s*(\d+)\b/);
    return match ? match[1] : '';
  }

  function cleanPokemonBaseName(baseId, name) {
    let clean = String(name || '').trim();
    const id = String(baseId || '').trim();
    if (clean && id) {
      clean = clean.replace(new RegExp('^#?0*' + id + '\\s+', 'i'), '').trim();
    }
    clean = clean.replace(/^#?0*\d+\s+/, '').trim();
    return clean;
  }

  function pokemonBaseLabel(baseId, name) {
    const id = String(baseId || '').trim();
    const clean = cleanPokemonBaseName(id, name);
    return id ? `#${id}${clean ? ' ' + clean : ''}` : clean;
  }

  function setupLookupInput(input, type) {
    if (!input) return;
    const list = document.getElementById(input.getAttribute('list') || '');
    if (!list) return;
    let timer = null;
    const load = async () => {
      const q = input.value.trim();
      const result = await request('/api/admin/lookups?type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q));
      if (!result.ok || !Array.isArray(result.rows)) return;
      list.innerHTML = result.rows.map(row => `<option value="${esc(lookupValueLabel(row))}"></option>`).join('');
    };
    input.addEventListener('focus', load);
    input.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(load, 220);
    });
  }

  function setDateTimeLocalFromNow(input, seconds) {
    if (!input) return;
    const date = new Date(Date.now() + seconds * 1000);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    input.value = date.toISOString().slice(0, 16);
  }

  function setMany(ids, value) {
    ids.forEach(id => {
      const input = document.getElementById(id);
      if (input) input.value = value;
    });
  }

  const eventBoostLabels = {
    exp: 'Опыт за PvE/PvP',
    coins: 'Монеты за бой',
    drop: 'Шанс дропа',
    quest_rewards: 'Квестовые награды',
    catch: 'Шанс ловли',
    happiness: 'Счастье покемонов'
  };

  const eventScopeLabels = {
    global: 'Везде',
    pve: 'Только PvE',
    pvp: 'Только PvP',
    quest: 'Только квесты',
    market: 'Магазин'
  };

  function labelFromMap(map, value) {
    return map[String(value || '')] || value || '';
  }

  function fmtMoney(value) {
    return Number(value || 0).toLocaleString('ru-RU');
  }

  function fmtTime(value, fallback = '') {
    const time = Number(value || 0);
    return time > 0 ? new Date(time * 1000).toLocaleString('ru-RU') : fallback;
  }

  function objectLabel(row) {
    const type = row.object_type ? `${row.object_type} ` : '';
    const id = row.object_id ? `#${row.object_id} ` : '';
    return `${type}${id}${row.object_name || ''}`.trim();
  }

  function riskLabel(row) {
    const risk = row.risk || {};
    return risk.reviewed ? 'Проверено' : (risk.is_risky ? `Риск ${risk.risk_score || ''}`.trim() : 'ОК');
  }

  function safeJson(value) {
    try {
      return JSON.stringify(value || {}, null, 2);
    } catch (error) {
      return '{}';
    }
  }

  function statLine(value) {
    if (!value || typeof value !== 'object') return '';
    return Object.entries(value).map(([key, val]) => `${key.toUpperCase()} ${val}`).join(' / ');
  }

  function detailItem(label, value) {
    if (value === undefined || value === null || value === '') return '';
    return `<div><b>${esc(label)}</b>${esc(value)}</div>`;
  }

  function snapshotSummaryHtml(row, snapshot) {
    const preview = snapshot.preview && typeof snapshot.preview === 'object' ? snapshot.preview : snapshot;
    if (row.object_type === 'pokemon') {
      const moves = Array.isArray(preview.moves) ? preview.moves.map(move => move.name || ('#' + move.id)).filter(Boolean).join(', ') : '';
      const held = preview.held_item && preview.held_item.id ? `#${preview.held_item.id} ${preview.held_item.name || ''}` : 'нет';
      return `
        <div class="admin-detail-grid admin-snapshot-grid">
          ${detailItem('Вид', preview.base_id ? '#' + preview.base_id : '')}
          ${detailItem('Уровень', preview.level)}
          ${detailItem('Shiny', preview.shiny ? 'да' : 'нет')}
          ${detailItem('Характер', preview.nature)}
          ${detailItem('Held item', held)}
          ${detailItem('IV', statLine(preview.iv))}
          ${detailItem('EV', statLine(preview.ev))}
          ${detailItem('Атаки', moves)}
        </div>
      `;
    }
    if (row.object_type === 'egg') {
      return `
        <div class="admin-detail-grid admin-snapshot-grid">
          ${detailItem('Вид яйца', preview.name || (preview.base_id ? '#' + preview.base_id : 'скрыт'))}
          ${detailItem('До вылупления', preview.remaining_seconds !== undefined ? `${preview.remaining_seconds} сек.` : '')}
          ${detailItem('Готово в', fmtTime(preview.ready_at || 0))}
          ${detailItem('Яйцевая атака', preview.egg_attack_name || (preview.egg_attack_id ? '#' + preview.egg_attack_id : ''))}
          ${detailItem('IV', statLine(preview.iv))}
        </div>
      `;
    }
    return `
      <div class="admin-detail-grid admin-snapshot-grid">
        ${detailItem('Категория', row.category || snapshot.category || '')}
        ${detailItem('Описание', preview.description || snapshot.description || '')}
        ${detailItem('Статус эффекта', preview.effect_status || snapshot.effect_status || '')}
        ${detailItem('Правило применения', preview.target_use_rule || snapshot.target_use_rule || '')}
        ${detailItem('Совместимость', preview.compatibility || preview.compatibility_rule || snapshot.compatibility_rule || '')}
        ${detailItem('Можно экипировать', preview.equippable === undefined ? '' : (preview.equippable ? 'да' : 'нет'))}
      </div>
    `;
  }

  function unixToLocalInput(value) {
    const time = Number(value || 0);
    if (!time) return '';
    const date = new Date(time * 1000);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
  }

  function fillEventPreset(boostKey, multiplier, hours, title) {
    const titleInput = document.querySelector('[name="title"]');
    const boostInput = document.querySelector('[name="boost_key"]');
    const multiplierInput = document.querySelector('[name="multiplier"]');
    const scopeInput = document.querySelector('[name="scope"]');
    const startInput = document.querySelector('[name="starts_at"]');
    const endInput = document.querySelector('[name="ends_at"]');
    const enabledInput = document.querySelector('[name="enabled"]');
    if (titleInput) titleInput.value = title;
    if (boostInput) boostInput.value = boostKey;
    if (multiplierInput) multiplierInput.value = String(multiplier);
    if (scopeInput) scopeInput.value = boostKey === 'quest_rewards' ? 'quest' : 'pve';
    if (startInput) setDateTimeLocalFromNow(startInput, 0);
    if (endInput) setDateTimeLocalFromNow(endInput, Math.max(1, hours) * 3600);
    if (enabledInput) enabledInput.checked = true;
  }

  const modules = {
    dashboard: {
      title: 'Дашборд',
      subtitle: 'Онлайн, последние бои, аудит и системные показатели.',
      endpoint: '/api/admin/dashboard',
      dataKey: 'dashboard',
      create: false,
      columns: ['Блок', 'Значение', 'Детали'],
      rows: data => {
        const overview = data.overview || {};
        const online = data.onlineUsers || [];
        const audit = data.recentAudit || [];
        const activeEvents = data.activeEvents || [];
        return [
          ...Object.keys(overview).map(key => ({ block: key, value: overview[key], details: 'count' })),
          ...activeEvents.map(event => ({
            block: 'active event',
            value: `${event.title} x${Number(event.multiplier || 1).toFixed(2)}`,
            details: `${event.boost_label || event.boost_key} · ${event.scope_label || event.scope}`
          })),
          ...(data.recentMarketLogs || []).slice(0, 8).map(row => ({
            block: 'market',
            value: `${row.action || ''} · lot #${row.lot_id || row.id || 0}`,
            details: `${row.object_name || ''} · ${fmtMoney(row.total_price || 0)}`
          })),
          ...(data.recentErrors || []).slice(0, 5).map(row => ({
            block: 'error log',
            value: 'PHP/API',
            details: row.line || ''
          })),
          ...online.map(user => ({ block: 'online', value: '#' + user.id + ' ' + user.login, details: 'loc ' + user.buildmy + ', battle ' + user.battleid })),
          ...audit.slice(0, 8).map(row => ({ block: 'audit', value: row.action, details: '#' + row.entity_id + ' ' + (row.admin_login || row.admin_id) }))
        ];
      },
      cells: row => [row.block, row.value, row.details],
      fields: []
    },
    users: {
      title: 'Пользователи',
      subtitle: 'Права, телепорт, карма, PvE/PvP состояние и доступ к админке.',
      endpoint: '/api/admin/users',
      dataKey: 'users',
      save: '/api/admin/users/save',
      columns: ['ID', 'Логин', 'Группа', 'Админ', 'Локация', 'Карма', 'Баланс'],
      cells: row => [row.id, row.login, row.groups, row.admins_panels, row.buildmy, row.karma_score, `${row.coins || 0} / ${row.diamonds || 0}`],
      fields: [
        ['id', 'ID', 'number', true],
        ['groups', 'Группа', 'number'],
        ['activation', 'Активен', 'number'],
        ['admins_panels', 'Доступ в админку', 'checkbox'],
        ['moderation', 'Модерация', 'number'],
        ['police', 'Полиция', 'number'],
        ['buildmy', 'Локация', 'number'],
        ['karma_score', 'Карма', 'number'],
        ['rang_a', 'Репутация A', 'number'],
        ['rang_b', 'Популярность', 'number'],
        ['battleid', 'Battle ID', 'number'],
        ['pve', 'PvE', 'number'],
        ['pvp', 'PvP', 'number']
      ],
      extra: 'userTools'
    },
    items: {
      title: 'Предметы',
      subtitle: 'Создание, правка, иконки, флаги использования и выдача игрокам.',
      endpoint: '/api/admin/items',
      dataKey: 'items',
      save: '/api/admin/items/save',
      delete: { url: '/api/admin/items', id: 'id', confirm: true },
      columns: ['ID', 'Иконка', 'Название', 'Категория', 'Uses', 'Battle'],
      cells: row => [row.id, `<img src="${esc(row.icon)}" alt="">`, row.name, row.category, row.uses, row.battleuse],
      fields: [
        ['id', 'ID', 'number'],
        ['name', 'Название', 'text'],
        ['tittle', 'Описание', 'textarea'],
        ['icon_file', 'Файл иконки', 'text'],
        ['category', 'Категория', 'number'],
        ['uses', 'Используется', 'number'],
        ['dress', 'Надевается', 'number'],
        ['delet', 'Удаляемый', 'number'],
        ['torg', 'Торговля', 'number'],
        ['elementary', 'Элемент', 'number'],
        ['battleuse', 'В бою', 'number'],
        ['dopolnen', 'Метка', 'text']
      ],
      extra: 'grantItem'
    },
    market: {
      title: 'Покемаркет',
      subtitle: 'Товары, цены, валюта, лимиты, остатки и порядок на полке.',
      endpoint: '/api/admin/market-items',
      dataKey: 'items',
      save: '/api/admin/market-items',
      delete: { url: '/api/admin/market-items', id: 'item_id' },
      columns: ['ID', 'Иконка', 'Товар', 'Цена', 'Валюта', 'Лимит', 'Вкл'],
      cells: row => [row.item_id, `<img src="${esc(row.icon)}" alt="">`, row.name, row.price, row.currency_name || row.currency_item_id, `${row.min_count}-${row.max_count}`, row.enabled],
      fields: [
        ['item_id', 'Item ID', 'number'],
        ['currency_item_id', 'Валюта item_id', 'number'],
        ['price', 'Цена', 'number'],
        ['min_count', 'Мин. кол-во', 'number'],
        ['max_count', 'Макс. кол-во', 'number'],
        ['max_owned', 'Макс. у игрока', 'number'],
        ['stock', 'Остаток (-1 безлимит)', 'number'],
        ['enabled', 'Включено', 'checkbox'],
        ['sort_order', 'Порядок', 'number'],
        ['note', 'Заметка', 'text']
      ]
    },
    drops: {
      title: 'Дроп',
      subtitle: 'Предмет, шанс, количество, время, локация и источник выпадения.',
      endpoint: '/api/admin/drop-rules',
      dataKey: 'rules',
      save: '/api/admin/drop-rules/save',
      delete: { url: '/api/admin/drop-rules/delete', id: 'id', method: 'POST' },
      columns: ['ID', 'Предмет', 'Локация', 'Источник', 'Шанс', 'Время', 'Вкл'],
      cells: row => [row.id, row.item_name || row.item_id, row.location_title || row.location_id, row.source_type, row.chance_percent + '%', `${row.time_start}-${row.time_end}`, row.enabled],
      fields: [
        ['id', 'ID', 'number'],
        ['item_id', 'Item ID', 'number'],
        ['location_id', 'Локация ID', 'number'],
        ['pokebuild_id', 'Wild slot ID', 'number'],
        ['pokemon_base_id', 'Base Pokemon ID', 'number'],
        ['source_type', 'Источник', 'select:wild,trainer,npc,event'],
        ['chance_percent', 'Шанс %', 'number'],
        ['min_count', 'Мин.', 'number'],
        ['max_count', 'Макс.', 'number'],
        ['time_start', 'С', 'time'],
        ['time_end', 'До', 'time'],
        ['quest_id', 'Quest ID', 'number'],
        ['quest_process', 'Quest process', 'number'],
        ['quest_complete', 'Quest complete', 'number'],
        ['enabled', 'Включено', 'checkbox'],
        ['note', 'Заметка', 'text']
      ]
    },
    wild: {
      title: 'Дикие слоты',
      subtitle: 'Кто нападает на каждой локации: покемон, уровень, шанс, время и квестовые условия.',
      endpoint: '/api/admin/wild-slots',
      dataKey: 'slots',
      save: '/api/admin/wild-slots',
      delete: { url: '/api/admin/wild-slots', id: 'id', confirm: true },
      columns: ['ID', 'Локация', 'Покемон', 'Lv', 'Шанс', 'Время', 'Квест'],
      cells: row => [row.id, row.location_title || row.building, row.pokemon_name || row.baseid, row.lvl, row.chance, `${row.timeone}-${row.timetwo}`, row.quest_id || ''],
      fields: [
        ['id', 'ID', 'number'],
        ['building', 'Локация ID', 'number'],
        ['baseid', 'Base Pokemon ID', 'number'],
        ['lvl', 'Уровень', 'number'],
        ['chance', 'Шанс', 'number'],
        ['poimka', 'Ловля', 'number'],
        ['quest_id', 'Quest ID', 'number'],
        ['q_process', 'Quest process', 'number'],
        ['questupdate', 'Quest update', 'number'],
        ['timeone', 'С', 'time'],
        ['timetwo', 'До', 'time'],
        ['sprz', 'Спец. флаг', 'number']
      ]
    },
    bosses: {
      title: 'Боссы',
      subtitle: 'Ивентовые боссы на локациях: команда 6x6, ручные статы, атаки, предметы и дроп.',
      endpoint: '/api/admin/bosses',
      dataKey: 'bosses',
      save: '/api/admin/bosses',
      delete: { url: '/api/admin/bosses', id: 'id', confirm: true },
      columns: ['ID', 'Название', 'Локация', 'Ивент', 'Команда', 'Дроп', 'Вкл'],
      cells: row => [
        row.id,
        row.title,
        row.location_title || row.location_id,
        row.event_key || '',
        row.team_count || (Array.isArray(row.team) ? row.team.length : 0),
        row.drop_count || (Array.isArray(row.drops) ? row.drops.length : 0),
        row.enabled
      ],
      fields: [
        ['id', 'ID', 'number'],
        ['location_id', 'Локация ID', 'number'],
        ['title', 'Название', 'text'],
        ['description', 'Описание', 'textarea'],
        ['event_key', 'Ключ ивента', 'text'],
        ['enabled', 'Включено', 'checkbox'],
        ['starts_at', 'Появляется с timestamp/дата', 'text'],
        ['ends_at', 'Исчезает в timestamp/дата', 'text'],
        ['conditions_json', 'Условия JSON', 'textarea'],
        ['team_json', 'Команда JSON до 6 покемонов', 'textarea'],
        ['drops_json', 'Дроп JSON', 'textarea']
      ],
      formRow: row => row || {
        enabled: 1,
        conditions_json: '{}',
        team_json: JSON.stringify([
          {
            slot: 1,
            base_id: 150,
            level: 80,
            gender: 1,
            nature_id: 1,
            shiny: false,
            held_item_id: 0,
            moves: [94, 105, 129, 219],
            stats: { hp: 320, atk: 180, def: 160, satk: 220, sdef: 170, speed: 180 }
          }
        ], null, 2),
        drops_json: JSON.stringify([
          { item_id: 1, chance_percent: 100, min_count: 10000, max_count: 25000, guaranteed: true },
          { item_id: 25, chance_percent: 15, min_count: 1, max_count: 2, rare: true }
        ], null, 2)
      },
      extra: 'bossTools'
    },
    locations: {
      title: 'Локации',
      subtitle: 'Базовые поля локаций: название, город, PvE, опасность и защита.',
      endpoint: '/api/admin/locations',
      dataKey: 'locations',
      save: '/api/admin/locations',
      delete: { url: '/api/admin/locations', id: 'id', confirm: true },
      columns: ['ID', 'Название', 'Town', 'Type', 'PvE', 'Zax'],
      cells: row => [row.id, row.title, row.town, row.tipe, row.pve, row.zax],
      fields: [
        ['id', 'ID', 'number'],
        ['title', 'Название', 'text'],
        ['town', 'Town', 'number'],
        ['tipe', 'Тип', 'number'],
        ['pve', 'PvE', 'number'],
        ['zax', 'Защита/опасность', 'number']
      ]
    },
    pokemon: {
      title: 'Покемоны игроков',
      subtitle: 'Поиск, выдача, базовая правка, тренировки и удаление.',
      endpoint: '/api/admin/pokemon',
      dataKey: 'pokemon',
      paginated: true,
      perPage: 100,
      save: '/api/admin/pokemon',
      delete: { url: '/api/admin/pokemon', id: 'id', confirm: true },
      columns: ['ID', 'Игрок', 'Base', 'Покемон', 'Lv', 'HP', 'Активен', 'Предмет', 'Тренировка'],
      cells: row => [
        row.id,
        row.login || row.users,
        pokemonBaseLabel(row.basenum, row.base_name),
        row.names,
        row.lvl,
        `${row.hp_my}/${row.hp_max}`,
        row.active,
        row.equipped_item_name || row.equipped_item_id || row.item || '',
        `${row.training_stage || 0} ${row.training_stat || ''}`
      ],
      filterFields: [
        ['login', 'Ник', 'text'],
        ['user_id', 'User ID', 'number'],
        ['pokemon_id', 'Pokemon ID', 'number'],
        ['base_id', 'Base ID', 'number'],
        ['name', 'Имя покемона', 'text'],
        ['level_min', 'Lv от', 'number'],
        ['level_max', 'Lv до', 'number'],
        ['shiny', 'Shiny', 'select:,1,0'],
        ['active', 'Активен', 'select:,1,0'],
        ['training_stage', 'Стадия', 'select:,0,1,2,3,4,5,6'],
        ['training_stat', 'Стат', 'select:,atk,def,satk,sdef,speed'],
        ['held_item_id', 'Item ID', 'number']
      ],
      fields: [
        ['id', 'ID', 'number', true],
        ['lvl', 'Уровень', 'number'],
        ['active', 'Активен', 'number'],
        ['hp_my', 'HP сейчас', 'number'],
        ['hp_max', 'HP макс', 'number'],
        ['training_stage', 'Стадия тренировки', 'number'],
        ['training_stat', 'Стат тренировки', 'select:,atk,def,satk,sdef,speed'],
        ['training_named_effect', 'Именной эффект', 'text'],
        ['training_tamed', 'Приручен', 'checkbox']
      ],
      extra: 'grantPokemon'
    },
    attacks: {
      title: 'Атаки',
      subtitle: 'Редактор атакадекса и привязка атак к покемонам.',
      endpoint: '/api/admin/attacks',
      dataKey: 'attacks',
      paginated: true,
      perPage: 100,
      save: '/api/admin/attacks',
      delete: { url: '/api/admin/attacks', id: 'atac_id', confirm: true },
      columns: ['ID', 'Название', 'Тип', 'Кат.', 'Сила', 'Точн.', 'PP'],
      cells: row => [row.atac_id, row.atac_name, row.atac_tip, row.atac_categori, row.atac_power, row.atac_accuracy, row.atac_pp],
      filterFields: [
        ['type', 'Тип', 'text'],
        ['category', 'Категория', 'select:,0,1,2,3'],
        ['power_min', 'Сила от', 'number'],
        ['power_max', 'Сила до', 'number'],
        ['accuracy_min', 'Точн. от', 'number'],
        ['accuracy_max', 'Точн. до', 'number'],
        ['pp_min', 'PP от', 'number'],
        ['pp_max', 'PP до', 'number'],
        ['effect', 'Эффект/описание', 'text']
      ],
      fields: [
        ['atac_id', 'ID', 'number'],
        ['atac_name', 'Название', 'text'],
        ['atac_tip', 'Тип', 'text'],
        ['atac_categori', 'Категория', 'number'],
        ['atac_pp', 'PP', 'number'],
        ['atac_power', 'Сила', 'number'],
        ['atac_accuracy', 'Точность', 'number'],
        ['priorety', 'Приоритет', 'number'],
        ['atac_tittle', 'Описание', 'textarea'],
        ['tittle_effect', 'Эффект', 'textarea']
      ],
      extra: 'attackLearn'
    },
    news: {
      title: 'Новости',
      subtitle: 'CRUD старой таблицы news через новый безопасный API.',
      endpoint: '/api/admin/news',
      dataKey: 'news',
      save: '/api/admin/news',
      delete: { url: '/api/admin/news', id: 'id', confirm: true },
      columns: ['ID', 'Дата', 'Тема', 'Автор', 'Ссылка'],
      cells: row => [row.id, row.date, row.subject, row.author, row.link],
      fields: [
        ['id', 'ID', 'number'],
        ['subject', 'Тема', 'text'],
        ['author', 'Автор', 'text'],
        ['date', 'Дата', 'date'],
        ['news', 'Текст', 'textarea'],
        ['link', 'Ссылка', 'text'],
        ['opis', 'Описание ссылки', 'text']
      ]
    },
    events: {
      title: 'Ивенты и бусты',
      subtitle: 'Включение x2/x4 событий: опыт, монеты, дроп, ловля, счастье покемонов и квестовые награды.',
      endpoint: '/api/admin/events',
      dataKey: 'events',
      save: '/api/admin/events',
      delete: { url: '/api/admin/events', id: 'id', confirm: true },
      columns: ['ID', 'Название', 'Бонус', 'Множитель', 'Область', 'Период', 'Статус'],
      cells: row => [
        row.id,
        row.title,
        row.boost_label || labelFromMap(eventBoostLabels, row.boost_key),
        'x' + Number(row.multiplier || 1).toFixed(2),
        row.scope_label || labelFromMap(eventScopeLabels, row.scope),
        `${row.starts_at > 0 ? new Date(Number(row.starts_at) * 1000).toLocaleString('ru-RU') : 'сразу'} — ${row.ends_at > 0 ? new Date(Number(row.ends_at) * 1000).toLocaleString('ru-RU') : 'бессрочно'}`,
        row.status_label || (Number(row.enabled || 0) ? 'включён' : 'выключен')
      ],
      fields: [
        ['id', 'ID', 'number'],
        ['title', 'Название', 'text'],
        ['boost_key', 'Что бустим', 'select:exp=Опыт,coins=Монеты,drop=Шанс дропа,quest_rewards=Квестовые награды,catch=Шанс ловли,happiness=Счастье покемонов'],
        ['multiplier', 'Множитель', 'number'],
        ['scope', 'Область', 'select:global=Везде,pve=PvE,pvp=PvP,quest=Квесты,market=Магазин'],
        ['starts_at', 'Старт', 'datetime-local'],
        ['ends_at', 'Конец', 'datetime-local'],
        ['enabled', 'Включено', 'checkbox'],
        ['note', 'Заметка', 'text']
      ],
      formRow: row => row ? {
        ...row,
        starts_at: unixToLocalInput(row.starts_at),
        ends_at: unixToLocalInput(row.ends_at)
      } : {
        title: 'x2 опыт на час',
        boost_key: 'exp',
        multiplier: '2.00',
        scope: 'pve',
        starts_at: unixToLocalInput(Math.floor(Date.now() / 1000)),
        ends_at: unixToLocalInput(Math.floor(Date.now() / 1000) + 3600),
        enabled: 1,
        note: ''
      },
      extra: 'eventTools'
    },
    tournaments: {
      title: 'Турниры',
      subtitle: 'Новый модуль турниров с нуля: расписание, куратор, взнос, арена, участники и награды.',
      endpoint: '/api/admin/tournaments',
      dataKey: 'tournaments',
      save: '/api/admin/tournaments',
      delete: { url: '/api/admin/tournaments', id: 'id', confirm: true },
      columns: ['ID', 'Название', 'Статус', 'Старт', 'Локация', 'Куратор', 'Участники'],
      cells: row => [
        row.id,
        row.title,
        row.status,
        row.starts_at > 0 ? new Date(Number(row.starts_at) * 1000).toLocaleString('ru-RU') : 'не задан',
        row.location_name || row.location_id,
        row.curator_login || row.curator_user_id || '',
        row.participants_count || 0
      ],
      fields: [
        ['id', 'ID', 'number', true],
        ['legacy_id', 'Legacy ID', 'number'],
        ['title', 'Название', 'text'],
        ['status', 'Статус', 'select:draft,registration,active,finished,cancelled'],
        ['starts_at', 'Старт UNIX или дата', 'text'],
        ['ends_at', 'Финиш UNIX или дата', 'text'],
        ['entry_fee_item_id', 'Предмет взноса', 'number'],
        ['entry_fee_amount', 'Размер взноса', 'number'],
        ['location_id', 'Локация/арена', 'number'],
        ['curator_user_id', 'Куратор user_id', 'number'],
        ['min_level', 'Мин. уровень', 'number'],
        ['max_level', 'Макс. уровень', 'number'],
        ['max_participants', 'Макс. участников', 'number'],
        ['rules', 'Правила', 'textarea'],
        ['reward_note', 'Награды', 'textarea']
      ],
      extra: 'tournamentTools'
    },
    medals: {
      title: 'Медали',
      subtitle: 'Каталог медалей и выдача игрокам. Старые medal.php/info_tur.php отсутствуют, поэтому логика новая.',
      endpoint: '/api/admin/medals',
      dataKey: 'medals',
      save: '/api/admin/medals',
      delete: { url: '/api/admin/medals', id: 'id', confirm: true },
      columns: ['ID', 'Иконка', 'Медаль', 'Тип', 'Турнир', 'Выдано', 'Вкл'],
      cells: row => [row.id, `<img src="${esc(row.icon)}" alt="">`, row.title, row.medal_type, row.tournament_title || row.tournament_id || '', row.awarded_count || 0, row.enabled],
      fields: [
        ['id', 'ID', 'number', true],
        ['title', 'Название', 'text'],
        ['description', 'Описание', 'textarea'],
        ['icon_file', 'Файл иконки', 'text'],
        ['medal_type', 'Тип', 'select:tournament,achievement,event,admin'],
        ['tournament_id', 'Турнир ID', 'number'],
        ['sort_order', 'Порядок', 'number'],
        ['enabled', 'Включена', 'checkbox']
      ],
      extra: 'medalTools'
    },
    moderation: {
      title: 'Модерация',
      subtitle: 'Наказания игроков, banip и последние сообщения чата.',
      endpoint: '/api/admin/moderation',
      dataKey: 'moderation',
      save: '/api/admin/moderation/action',
      create: true,
      columns: ['Тип', 'Игрок/IP', 'Модератор/Автор', 'Причина/Текст'],
      rows: data => [
        ...(data.punishments || []).map(row => ({
          type: row.active > 0 ? row.action : row.action + ' снят',
          id: row.target_login || row.target_user_id,
          target: row.target_login || row.target_user_id,
          author: row.moderator_login || ('#' + row.moderator_user_id),
          text: `${row.reason || ''} ${row.expires_at > 0 ? 'до ' + new Date(Number(row.expires_at) * 1000).toLocaleString() : 'бессрочно'}`
        })),
        ...(data.bans || []).map(row => ({ type: 'banip', id: row.ip, target: '', author: '#' + row.id, text: row.date })),
        ...(data.chat || []).map(row => ({ type: 'chat', id: row.id, target: row.author, author: row.author, text: row.text }))
      ],
      cells: row => [row.type, row.id, row.author, row.text],
      fields: [
        ['target', 'Игрок: ник или ID', 'text'],
        ['action', 'Действие', 'select:mute,unmute,ban,unban,warn'],
        ['duration', 'Срок', 'text'],
        ['reason', 'Причина', 'textarea']
      ],
      formRow: row => ({
        target: row ? (row.target || row.id || '') : '',
        action: row && row.type && String(row.type).includes('ban') ? 'unban' : 'mute',
        duration: '15minut',
        reason: row && row.text ? String(row.text).slice(0, 180) : ''
      }),
      extra: 'moderationTools'
    },
    commission: {
      title: 'Комиссионная лавка',
      subtitle: 'Продажи, логи, подозрительные сделки, возвраты и средние цены за 1/7/14/30 дней.',
      endpoint: '/api/admin/commission/logs',
      dataKey: 'logs',
      paginated: true,
      perPage: 80,
      create: false,
      columns: ['Время', 'Действие', 'Лот', 'Объект', 'Кол-во', 'За 1', 'Итого', 'Комиссия', 'Продавец', 'Покупатель', 'Риск'],
      cells: row => [
        row.log_created_at_text || fmtTime(row.log_created_at || row.sold_at || row.created_at),
        row.action || row.status,
        '#' + (row.lot_id || row.id),
        objectLabel(row),
        row.quantity || 0,
        fmtMoney(row.price_per_unit || 0),
        fmtMoney(row.total_price || 0),
        fmtMoney(row.commission_amount || 0),
        row.seller_name || ('#' + (row.seller_id || '')),
        row.buyer_name || (row.buyer_id ? '#' + row.buyer_id : ''),
        riskLabel(row)
      ],
      rowClass: row => row.risk?.reviewed ? 'is-reviewed' : (row.risk?.is_risky ? 'is-risk' : ''),
      fields: [],
      filterFields: [
        ['period', 'Период', 'select:=любой,today=сегодня,7d=7 дней,14d=14 дней,30d=30 дней,month=месяц'],
        ['status', 'Статус', 'select:=любой,active=active,sold=sold,cancelled=cancelled,expired=expired'],
        ['object_type', 'Тип', 'select:=любой,item=item,pokemon=pokemon,egg=egg'],
        ['category', 'Категория', 'select:=любая,held_item=held,evolution=evolution,ticket=ticket,mega_primal=mega/primal,tm=TM,gift_box=gift,craft=craft,currency=currency,other=other,pokemon=pokemon,egg=egg'],
        ['action', 'Лог-действие', 'select:=любое,create=create,buy=buy,cancel=cancel,expired=expired,return=return,settings.update=settings.update,risk.flagged=risk.flagged,risk.approved=risk.approved'],
        ['seller', 'Продавец', 'text'],
        ['buyer', 'Покупатель', 'text'],
        ['lot_id', 'Lot ID', 'number'],
        ['object_id', 'Object ID', 'number'],
        ['legacy', 'Legacy source', 'text'],
        ['price_min', 'Цена от', 'number'],
        ['price_max', 'Цена до', 'number'],
        ['date_from', 'С даты', 'date'],
        ['date_to', 'По дату', 'date'],
        ['system_only', 'Только Система', 'select:=нет,1=да'],
        ['risky', 'Только риск', 'select:=нет,1=да'],
        ['sort', 'Сортировка', 'select:new=новые,old=старые,price_asc=цена ↑,price_desc=цена ↓,unit_asc=за штуку ↑,unit_desc=за штуку ↓,commission_asc=комиссия ↑,commission_desc=комиссия ↓,quantity_asc=кол-во ↑,quantity_desc=кол-во ↓,seller=продавец,buyer=покупатель,name=название']
      ],
      extra: 'commissionTools'
    },
    economy_guard: {
      title: 'Economy Guard',
      subtitle: 'Автообнаружение подозрительных сделок, резкого прироста монет, трансферов и фейковых цен.',
      endpoint: '/api/admin/economy-guard/alerts',
      dataKey: 'alerts',
      paginated: true,
      perPage: 80,
      create: false,
      columns: ['Статус', 'Риск', 'Тип', 'Игрок', 'Связанный', 'Сумма', 'Объект', 'Описание', 'Обновлён'],
      cells: row => [
        row.status || '',
        `${row.severity || ''} · ${row.score || 0}`,
        row.alert_type || '',
        row.user_login || (row.user_id ? '#' + row.user_id : ''),
        row.related_user_login || (row.related_user_id ? '#' + row.related_user_id : ''),
        fmtMoney(row.amount || 0),
        `${row.entity_type || ''} ${row.entity_id ? '#' + row.entity_id : ''}`.trim(),
        row.title || '',
        fmtTime(row.last_seen_at || 0)
      ],
      rowClass: row => {
        if (row.status === 'reviewed' || row.status === 'ignored') return 'is-reviewed';
        return row.severity === 'critical' || Number(row.score || 0) >= 80 ? 'is-risk' : '';
      },
      fields: [],
      filterFields: [
        ['status', 'Статус', 'select:=любой,open=open,reviewed=reviewed,ignored=ignored'],
        ['type', 'Тип', 'select:=любой,suspicious_trade=suspicious_trade,massive_money_gain=massive_money_gain,transfer_abuse=transfer_abuse,fake_market_price=fake_market_price'],
        ['severity', 'Риск', 'select:=любой,critical=critical,warn=warn,info=info'],
        ['user_id', 'User ID', 'number']
      ],
      extra: 'economyGuardTools'
    },
    battle_replays: {
      title: 'Повторы боёв',
      subtitle: 'Снимки боя, логи раундов, random rolls, damage audit и просмотр спорных PvE/PvP ситуаций.',
      endpoint: '/api/admin/battle-replays',
      dataKey: 'replays',
      paginated: true,
      perPage: 80,
      create: false,
      columns: ['Battle', 'Тип', 'Статус', 'Раундов', 'Игроки', 'Победитель', 'Events', 'Damage', 'Rolls', 'Обновлён'],
      cells: row => [
        '#' + (row.battle_id || ''),
        row.battle_type || '',
        row.status || '',
        row.rounds || 0,
        `${row.user_1_login || ('#' + (row.user_1 || 0))} / ${row.user_2_login || (row.user_2 ? '#' + row.user_2 : 'wild')}`,
        row.winner_id || '',
        row.event_count || 0,
        row.damage_count || 0,
        row.roll_count || 0,
        fmtTime(row.updated_at || 0)
      ],
      fields: [],
      filterFields: [
        ['q', 'Battle ID / игрок / тип', 'text']
      ],
      extra: 'battleReplayTools'
    },
    settings: {
      title: 'Система',
      subtitle: 'Техработы, аудит и системные флаги.',
      endpoint: '/api/admin/settings',
      dataKey: 'settings',
      save: '/api/admin/settings',
      create: false,
      columns: ['Настройка', 'Значение'],
      rows: data => Object.keys(data || {}).map(key => ({ name: key, value: data[key] })),
      cells: row => [row.name, row.value],
      fields: [['techwork', 'Технические работы', 'checkbox']],
      formRow: () => Object.fromEntries(state.rows.map(row => [row.name, row.value])),
      extra: 'audit'
    },
    legacy: {
      title: 'Legacy-карта',
      subtitle: 'Живая карта старых разделов: источник, новый модуль, API, таблицы и статус переноса.',
      endpoint: '/api/admin/legacy-map',
      dataKey: 'legacyModules',
      create: false,
      columns: ['Раздел', 'Источник', 'Новый модуль', 'Данные', 'Статус'],
      cells: row => [row.title, `${row.source} (${row.source_status})`, row.new_area, row.data_status, row.status_label],
      fields: [],
      extra: 'legacyActions'
    }
  };

  async function request(url, options = {}) {
    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json', ...(options.headers || {}) },
      ...options
    });
    return response.json();
  }

  async function send(url, fields, method = 'POST') {
    const body = new URLSearchParams(fields || {});
    body.set('_csrf', csrf);
    return request(url, {
      method,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body
    });
  }

  function setStatus(text, bad = false) {
    const box = $('#adminStatus');
    box.textContent = text || '';
    box.classList.toggle('is-bad', !!bad);
  }

  function currentQuery() {
    return $('#adminSearchInput').value.trim();
  }

  function reloadCurrent() {
    return loadCurrent(currentQuery());
  }

  function setTab(tab) {
    state.tab = tab;
    state.selected = null;
    state.page = 1;
    state.pagination = null;
    state.filters = {};
    document.querySelectorAll('[data-admin-tab]').forEach(btn => btn.classList.toggle('is-active', btn.dataset.adminTab === tab));
    const config = modules[tab];
    $('#adminTitle').textContent = config.title;
    $('#adminSubtitle').textContent = config.subtitle;
    $('#adminCreate').hidden = config.create === false || !config.save;
    $('#adminSearchInput').value = '';
    $('#adminLegacy').hidden = true;
    $('#adminTable').hidden = false;
    renderFilterbar(config);
    renderPager(null);
    clearForm();
    loadCurrent();
  }

  function rowsFor(config, payload) {
    const data = config.dataKey ? payload[config.dataKey] : payload;
    return config.rows ? config.rows(data) : (data || payload.rows || []);
  }

  async function loadCurrent(query = '') {
    const config = modules[state.tab];
    setStatus('Загрузка...');
    if (!config.endpoint) {
      state.rows = [];
      state.pagination = null;
      renderTable(config, []);
      renderPager(null);
      setStatus('');
      return;
    }

    const params = new URLSearchParams();
    if (query) params.set('q', query);
    if (config.paginated) {
      params.set('page', String(state.page));
      params.set('per_page', String(config.perPage || 100));
    }
    Object.entries(state.filters || {}).forEach(([key, value]) => {
      if (value !== '') params.set(key, value);
    });

    const joiner = config.endpoint.includes('?') ? '&' : '?';
    const data = await request(config.endpoint + (params.toString() ? joiner + params.toString() : ''));
    if (!data.ok) {
      setStatus(data.message || data.error || 'Ошибка загрузки.', true);
      return;
    }

    state.pagination = data.pagination || null;
    state.rows = rowsFor(config, data);
    renderStats(data.dashboard && data.dashboard.overview ? data.dashboard.overview : null);
    renderTable(config, state.rows);
    renderPager(state.pagination);
    buildForm(config, config.formRow ? config.formRow(null) : null);
    setStatus('');
  }

  function renderStats(overview) {
    if (!overview) return;
    $('#adminStats').innerHTML = Object.keys(overview).map(key => `
      <article><span>${esc(key)}</span><b>${esc(overview[key])}</b></article>
    `).join('');
  }

  function renderFilterbar(config) {
    const form = $('#adminFilterForm');
    if (!form || !config.filterFields || !config.filterFields.length) {
      if (form) {
        form.hidden = true;
        form.innerHTML = '';
      }
      return;
    }

    form.hidden = false;
    form.innerHTML = config.filterFields.map(field => filterInputHtml(field)).join('') + `
      <button type="submit">Фильтр</button>
      <button type="button" id="adminFilterReset">Сброс</button>
    `;
    form.onsubmit = event => {
      event.preventDefault();
      state.filters = Object.fromEntries(new FormData(form).entries());
      state.page = 1;
      reloadCurrent();
    };
    $('#adminFilterReset').addEventListener('click', () => {
      state.filters = {};
      state.page = 1;
      renderFilterbar(config);
      reloadCurrent();
    });
  }

  function filterInputHtml(field) {
    const [name, label, type] = field;
    const value = state.filters[name] || '';
    if (type.startsWith('select:')) {
      const values = type.slice(7).split(',').map(raw => {
        const [optionValue, optionLabel] = raw.split('=');
        return {
          value: optionValue,
          label: optionLabel || optionValue || 'любой'
        };
      });
      return `<label>${esc(label)}<select name="${esc(name)}">${values.map(option => `<option value="${esc(option.value)}" ${String(value) === option.value ? 'selected' : ''}>${esc(option.label)}</option>`).join('')}</select></label>`;
    }
    return `<label>${esc(label)}<input name="${esc(name)}" type="${esc(type)}" value="${esc(value)}"></label>`;
  }

  function renderPager(pagination) {
    const pager = $('#adminPager');
    if (!pager) return;
    if (!pagination) {
      pager.hidden = true;
      return;
    }
    pager.hidden = false;
    $('#adminPageInfo').textContent = `${pagination.page}/${pagination.pages} · ${pagination.total}`;
    $('#adminPrevPage').disabled = pagination.page <= 1;
    $('#adminNextPage').disabled = pagination.page >= pagination.pages;
  }

  function renderTable(config, rows) {
    const table = $('#adminTable');
    table.querySelector('thead').innerHTML = '<tr>' + (config.columns || []).map(col => `<th>${esc(col)}</th>`).join('') + '</tr>';
    table.querySelector('tbody').innerHTML = rows.map((row, index) => {
      const cells = (config.cells ? config.cells(row) : Object.values(row)).map(cell => `<td>${cell && String(cell).startsWith('<img') ? cell : esc(cell)}</td>`).join('');
      const rowClass = config.rowClass ? String(config.rowClass(row) || '').replace(/[^a-zA-Z0-9_ -]/g, '') : '';
      return `<tr data-row="${index}" class="${esc(rowClass)}">${cells}</tr>`;
    }).join('') || `<tr><td colspan="${(config.columns || []).length || 1}">Нет данных</td></tr>`;
    table.querySelectorAll('tbody tr[data-row]').forEach(tr => {
      tr.addEventListener('click', () => {
        state.selected = rows[Number(tr.dataset.row)];
        table.querySelectorAll('tr').forEach(row => row.classList.remove('is-selected'));
        tr.classList.add('is-selected');
        buildForm(config, config.formRow ? config.formRow(state.selected) : state.selected);
      });
    });
  }

  function clearForm() {
    $('#adminForm').innerHTML = '';
    $('#adminDanger').innerHTML = '';
    $('#inspectorTitle').textContent = 'Инспектор';
    $('#inspectorHint').textContent = 'Выберите строку или создайте новую запись.';
  }

  function inputHtml(field, row) {
    const [name, label, type, readonly] = field;
    const value = row ? (row[name] ?? '') : '';
    if (type === 'textarea') {
      return `<label>${esc(label)}<textarea name="${esc(name)}" ${readonly ? 'readonly' : ''}>${esc(value)}</textarea></label>`;
    }
    if (type === 'checkbox') {
      const checked = Number(value || 0) ? 'checked' : '';
      return `<label class="admin-check"><input type="checkbox" name="${esc(name)}" value="1" ${checked} ${readonly ? 'disabled' : ''}> ${esc(label)}</label>`;
    }
    if (type.startsWith('select:')) {
      const values = type.slice(7).split(',').map(raw => {
        const [optionValue, optionLabel] = raw.split('=');
        return {
          value: optionValue,
          label: optionLabel || optionValue || 'не задано'
        };
      });
      return `<label>${esc(label)}<select name="${esc(name)}" ${readonly ? 'disabled' : ''}>${values.map(option => `<option value="${esc(option.value)}" ${String(value) === option.value ? 'selected' : ''}>${esc(option.label)}</option>`).join('')}</select></label>`;
    }
    return `<label>${esc(label)}<input name="${esc(name)}" type="${esc(type)}" value="${esc(value)}" ${readonly ? 'readonly' : ''}></label>`;
  }

  function buildForm(config, row) {
    const form = $('#adminForm');
    $('#inspectorTitle').textContent = row ? 'Редактирование' : (config.save ? 'Создание' : 'Инспектор');
    $('#inspectorHint').textContent = row ? 'Строка загружена в форму.' : 'Можно создать новую запись, если раздел это поддерживает.';

    if (!config.fields.length) {
      form.innerHTML = '<p class="muted">В этом разделе нет формы редактирования.</p>';
    } else {
      form.innerHTML = config.fields.map(field => inputHtml(field, row)).join('') + (config.save ? '<button type="submit">Сохранить</button>' : '');
      form.onsubmit = async event => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(form).entries());
        config.fields.filter(field => field[2] === 'checkbox').forEach(field => {
          if (!form.elements[field[0]]?.checked) data[field[0]] = '0';
        });
        const result = await send(config.save, data);
        setStatus(result.message || '', !result.ok);
        if (result.ok) reloadCurrent();
      };
    }

    buildDanger(config, row);
    buildExtra(config, row);
  }

  function buildDanger(config, row) {
    const danger = $('#adminDanger');
    danger.innerHTML = '';
    if (!row || !config.delete) return;

    const idValue = row[config.delete.id];
    danger.innerHTML = `
      <h3>Опасное действие</h3>
      ${config.delete.confirm ? '<input id="deleteConfirm" placeholder="Введите DELETE">' : ''}
      <button type="button" class="danger-btn" id="deleteButton">Удалить</button>
    `;
    $('#deleteButton').addEventListener('click', async () => {
      const fields = { [config.delete.id]: idValue };
      if (config.delete.confirm) fields.confirm = $('#deleteConfirm').value;
      const result = await send(config.delete.url, fields, config.delete.method || 'DELETE');
      setStatus(result.message || '', !result.ok);
      if (result.ok) reloadCurrent();
    });
  }

  function buildExtra(config, row) {
    const danger = $('#adminDanger');
    if (config.extra === 'userTools' && row) {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Инструменты игрока</h3>
        <button type="button" id="openUserPokemonBtn">Покемоны игрока</button>
        <button type="button" id="banUserBtn">Забанить IP</button>
        <button type="button" id="unbanUserBtn">Разбанить IP</button>
      `);
      $('#openUserPokemonBtn').addEventListener('click', () => {
        setTab('pokemon');
        state.filters = { user_id: String(row.id) };
        renderFilterbar(modules.pokemon);
        reloadCurrent();
      });
      $('#banUserBtn').addEventListener('click', () => userBan(row.id, 'ban'));
      $('#unbanUserBtn').addEventListener('click', () => userBan(row.id, 'unban'));
    }

    if (config.extra === 'moderationTools') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Команды чата</h3>
        <p class="muted">Модераторы могут писать прямо в чат: <code>/mute niga 15minut Нарушение правил 5.1</code>, <code>/unmute niga исправился</code>, <code>/ban niga 1d причина</code>, <code>/unban niga причина</code>, <code>/warn niga причина</code>.</p>
        <div class="admin-inline-actions">
          <button type="button" id="modMute15">Мут 15 минут</button>
          <button type="button" id="modMuteHour">Мут 1 час</button>
          <button type="button" id="modWarn">Предупреждение</button>
          <button type="button" id="modUnmute">Снять мут</button>
          <button type="button" class="danger-btn" id="modBanDay">Бан 1 день</button>
        </div>
      `);
      $('#modMute15')?.addEventListener('click', () => moderationAction('mute', '15minut'));
      $('#modMuteHour')?.addEventListener('click', () => moderationAction('mute', '1h'));
      $('#modWarn')?.addEventListener('click', () => moderationAction('warn', ''));
      $('#modUnmute')?.addEventListener('click', () => moderationAction('unmute', ''));
      $('#modBanDay')?.addEventListener('click', () => moderationAction('ban', '1d'));
    }

    if (config.extra === 'bossTools') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Шпаргалка по боссу</h3>
        <p class="muted">Команда задаётся массивом до 6 строк. Для каждого слота: <code>base_id</code>, <code>level</code>, <code>moves</code>, <code>held_item_id</code>, <code>stats</code>. Если <code>stats</code> пустой, статы считаются автоматически.</p>
        <p class="muted">Дроп задаётся массивом: <code>item_id</code>, <code>chance_percent</code>, <code>min_count</code>, <code>max_count</code>, <code>guaranteed</code>, <code>rare</code>. Награды выдаются только при победе над всей командой босса.</p>
        <button type="button" id="bossFillHalloween">Пример Хэллоуин</button>
      `);
      $('#bossFillHalloween')?.addEventListener('click', () => {
        const team = document.querySelector('[name="team_json"]');
        const drops = document.querySelector('[name="drops_json"]');
        const title = document.querySelector('[name="title"]');
        const eventKey = document.querySelector('[name="event_key"]');
        if (title && !title.value) title.value = 'Хэллоуинский рейд';
        if (eventKey && !eventKey.value) eventKey.value = 'halloween';
        if (team) {
          team.value = JSON.stringify([
            { slot: 1, base_id: 491, level: 80, shiny: false, moves: [94, 138, 171, 373], stats: { hp: 350, atk: 170, def: 170, satk: 230, sdef: 190, speed: 210 } },
            { slot: 2, base_id: 150, level: 82, shiny: false, moves: [94, 105, 129, 219], stats: { hp: 380, atk: 190, def: 180, satk: 240, sdef: 200, speed: 200 } }
          ], null, 2);
        }
        if (drops) {
          drops.value = JSON.stringify([
            { item_id: 1, chance_percent: 100, min_count: 25000, max_count: 75000, guaranteed: true },
            { item_id: 90004, chance_percent: 2, min_count: 1, max_count: 1, rare: true }
          ], null, 2);
        }
      });
    }

    if (config.extra === 'eventTools') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Быстро включить событие</h3>
        <p class="muted">Выбери готовую плюшку или руками задай бонус, множитель и время. Активные ивенты сразу читаются PvE-наградами, ловлей, дропом и квестовыми наградами.</p>
        <div class="admin-inline-actions admin-quick-actions">
          <button type="button" data-event-preset="exp:2:1:x2 опыт на час">x2 опыт 1ч</button>
          <button type="button" data-event-preset="exp:4:2:x4 опыт 2ч</button>
          <button type="button" data-event-preset="drop:2:1:x2 дроп на час">x2 дроп 1ч</button>
          <button type="button" data-event-preset="catch:2:1:x2 ловля на час">x2 ловля 1ч</button>
          <button type="button" data-event-preset="happiness:2:1:x2 счастье на час">x2 счастье 1ч</button>
          <button type="button" data-event-preset="coins:2:1:x2 монеты на час">x2 монеты 1ч</button>
          <button type="button" data-event-preset="quest_rewards:2:24:x2 награды квестов на сутки">x2 квесты 24ч</button>
        </div>
        <div class="admin-inline-actions admin-quick-actions">
          <button type="button" data-event-duration="3600">Конец +1 час</button>
          <button type="button" data-event-duration="86400">Конец +1 день</button>
          <button type="button" data-event-duration="604800">Конец +7 дней</button>
          <button type="button" data-event-enable="1">Включить</button>
          <button type="button" data-event-enable="0">Выключить</button>
        </div>
      `);
      document.querySelectorAll('[data-event-preset]').forEach(button => {
        button.addEventListener('click', () => {
          const [boost, multiplier, hours, ...titleParts] = String(button.dataset.eventPreset || '').split(':');
          fillEventPreset(boost, multiplier, Number(hours || 1), titleParts.join(':'));
        });
      });
      document.querySelectorAll('[data-event-duration]').forEach(button => {
        button.addEventListener('click', () => {
          const endInput = document.querySelector('[name="ends_at"]');
          if (endInput) setDateTimeLocalFromNow(endInput, Number(button.dataset.eventDuration || 3600));
        });
      });
      document.querySelectorAll('[data-event-enable]').forEach(button => {
        button.addEventListener('click', () => {
          const enabledInput = document.querySelector('[name="enabled"]');
          if (enabledInput) enabledInput.checked = button.dataset.eventEnable === '1';
        });
      });
    }

    if (config.extra === 'grantItem') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Выдать предмет</h3>
        <p class="muted">Можно писать ник, ID, название предмета или выбрать подсказку из поиска.</p>
        <label>Игрок
          ${lookupInput('grantItemUser', 'users', 'Ник или ID игрока')}
        </label>
        <label>Предмет
          ${lookupInput('grantItemLookup', 'items', 'Название или ID предмета', row ? `#${esc(row.id)} ${esc(row.name || row.tittle || '')}` : '')}
        </label>
        <label>Количество
          <input id="grantItemCount" type="number" min="1" value="1">
        </label>
        <label class="admin-check"><input id="grantItemTemporary" type="checkbox"> Временный предмет</label>
        <div class="admin-temp-fields" id="grantItemTemporaryFields" hidden>
          <label>Действует до
            <input id="grantItemExpiresAt" type="datetime-local">
          </label>
          <div class="admin-inline-actions admin-quick-actions">
            <button type="button" data-item-expire="3600">+1 час</button>
            <button type="button" data-item-expire="86400">+1 день</button>
            <button type="button" data-item-expire="604800">+7 дней</button>
            <button type="button" data-item-expire="2592000">+30 дней</button>
          </div>
          <label>Или секунд от текущего момента
            <input id="grantItemExpiresSeconds" type="number" min="1" placeholder="3600">
          </label>
          <p class="muted">Если указана дата, предмет будет скрыт и недоступен после этого времени.</p>
        </div>
        <button type="button" id="grantItemBtn">Выдать</button>
      `);
      setupLookupInput($('#grantItemUser'), 'users');
      setupLookupInput($('#grantItemLookup'), 'items');
      $('#grantItemTemporary').addEventListener('change', event => {
        $('#grantItemTemporaryFields').hidden = !event.target.checked;
        if (event.target.checked && !$('#grantItemExpiresAt').value && !$('#grantItemExpiresSeconds').value) {
          setDateTimeLocalFromNow($('#grantItemExpiresAt'), 86400);
        }
      });
      document.querySelectorAll('[data-item-expire]').forEach(button => {
        button.addEventListener('click', () => {
          const seconds = Number(button.dataset.itemExpire || 0);
          setDateTimeLocalFromNow($('#grantItemExpiresAt'), seconds);
          $('#grantItemExpiresSeconds').value = '';
          $('#grantItemTemporary').checked = true;
          $('#grantItemTemporaryFields').hidden = false;
        });
      });
      $('#grantItemBtn').addEventListener('click', grantItem);
    }

    if (config.extra === 'grantPokemon') {
      if (row) {
        danger.insertAdjacentHTML('beforeend', `
          <h3>Быстрые связи</h3>
          <button type="button" id="openPokemonOwnerBtn">Открыть владельца</button>
        `);
        $('#openPokemonOwnerBtn').addEventListener('click', () => {
          setTab('users');
          $('#adminSearchInput').value = String(row.users || row.login || '');
          reloadCurrent();
        });
      }
      const grantValue = (key, fallback = '') => row && row[key] !== undefined && row[key] !== null ? esc(row[key]) : fallback;
      danger.insertAdjacentHTML('beforeend', `
        <h3>Выдать покемона</h3>
        <p class="muted">Выбирай игрока и базового покемона через поиск. Если итоговые статы оставить пустыми, они посчитаются от базы, IV, EV, характера и уровня.</p>
        <label>Ник / User ID
          ${lookupInput('pokeGrantUser', 'users', 'Ник или User ID', row ? `#${esc(row.users || '')} ${esc(row.login || '')}` : '')}
        </label>
        <label>Pokemon Base ID / имя
          ${lookupInput('pokeGrantBase', 'pokeBase', 'Base ID или имя покемона', row ? pokemonBaseLabel(row.basenum, row.base_name) : '')}
        </label>
        <div class="admin-grid-mini">
          <label>Уровень<input id="pokeGrantLvl" type="number" min="1" max="100" value="5"></label>
          <label>Пол<select id="pokeGrantSex">
            <option value="1" ${row && String(row.sex) === '1' ? 'selected' : ''}>Самец</option>
            <option value="2" ${row && String(row.sex) === '2' ? 'selected' : ''}>Самка</option>
          </select></label>
          <label>Характер<select id="pokeGrantHar"><option value="1">Характер #1</option></select></label>
          <label>Вид<select id="pokeGrantTips">
            <option value="normal" ${row && String(row.tips || '').toLowerCase().includes('shine') ? '' : 'selected'}>Обычный</option>
            <option value="shine" ${row && String(row.tips || '').toLowerCase().includes('shine') ? 'selected' : ''}>Shiny</option>
          </select></label>
        </div>
        <label class="admin-check"><input id="pokeGrantShiny" type="checkbox" ${row && String(row.tips || '').toLowerCase().includes('shine') ? 'checked' : ''}> Shiny</label>
        <div class="admin-inline-actions admin-quick-actions">
          <button type="button" id="pokeGrantIvMax">Гены 32 всем</button>
          <button type="button" id="pokeGrantEvClear">EV 0 всем</button>
          <button type="button" id="pokeGrantStats60">Статы 60 всем</button>
          <button type="button" id="pokeGrantStatsClear">Авто-статы</button>
        </div>
        <h4>Гены / IV</h4>
        <p class="muted">Гены можно задавать вручную, например атака 28 и защита 32. EV ограничены 0-252 на стат и суммой 510.</p>
        <div class="admin-grid-mini">
          <label>HP ген<input id="pokeGrantHpIv" type="number" min="0" max="9999" value="${grantValue('hp_iv', '1')}" placeholder="например 28"></label>
          <label>Атака ген<input id="pokeGrantAtkIv" type="number" min="0" max="9999" value="${grantValue('atk_iv', '1')}" placeholder="например 28"></label>
          <label>Защита ген<input id="pokeGrantDefIv" type="number" min="0" max="9999" value="${grantValue('def_iv', '1')}" placeholder="например 32"></label>
          <label>Спец. атака ген<input id="pokeGrantSatkIv" type="number" min="0" max="9999" value="${grantValue('satk_iv', '1')}" placeholder="например 28"></label>
          <label>Спец. защита ген<input id="pokeGrantSdefIv" type="number" min="0" max="9999" value="${grantValue('sdef_iv', '1')}" placeholder="например 28"></label>
          <label>Скорость ген<input id="pokeGrantSpeedIv" type="number" min="0" max="9999" value="${grantValue('speed_iv', '1')}" placeholder="например 28"></label>
        </div>
        <h4>EV</h4>
        <div class="admin-grid-mini">
          <label>HP EV<input id="pokeGrantHpEv" type="number" min="0" max="252" value="${grantValue('hp_ev', '0')}" placeholder="0-252"></label>
          <label>Атака EV<input id="pokeGrantAtkEv" type="number" min="0" max="252" value="${grantValue('atk_ev', '0')}" placeholder="0-252"></label>
          <label>Защита EV<input id="pokeGrantDefEv" type="number" min="0" max="252" value="${grantValue('def_ev', '0')}" placeholder="0-252"></label>
          <label>Спец. атака EV<input id="pokeGrantSatkEv" type="number" min="0" max="252" value="${grantValue('satk_ev', '0')}" placeholder="0-252"></label>
          <label>Спец. защита EV<input id="pokeGrantSdefEv" type="number" min="0" max="252" value="${grantValue('sdef_ev', '0')}" placeholder="0-252"></label>
          <label>Скорость EV<input id="pokeGrantSpeedEv" type="number" min="0" max="252" value="${grantValue('speed_ev', '0')}" placeholder="0-252"></label>
        </div>
        <h4>Итоговые статы вручную</h4>
        <input id="pokeGrantStatAll" type="number" min="1" placeholder="Все статы одним числом, например 60">
        <div class="admin-grid-mini">
          <label>HP<input id="pokeGrantStatHp" type="number" min="1" value="${grantValue('hp_max')}" placeholder="авто"></label>
          <label>Атака<input id="pokeGrantStatAtk" type="number" min="1" value="${grantValue('atk')}" placeholder="авто"></label>
          <label>Защита<input id="pokeGrantStatDef" type="number" min="1" value="${grantValue('def')}" placeholder="авто"></label>
          <label>Спец. атака<input id="pokeGrantStatSatk" type="number" min="1" value="${grantValue('satk')}" placeholder="авто"></label>
          <label>Спец. защита<input id="pokeGrantStatSdef" type="number" min="1" value="${grantValue('sdef')}" placeholder="авто"></label>
          <label>Скорость<input id="pokeGrantStatSpeed" type="number" min="1" value="${grantValue('speed')}" placeholder="авто"></label>
        </div>
        <input id="pokeGrantHpMy" type="number" min="0" value="${grantValue('hp_my')}" placeholder="Текущее HP, если нужно">
        <button type="button" id="grantPokemonBtn">Выдать</button>
      `);
      setupLookupInput($('#pokeGrantUser'), 'users');
      setupLookupInput($('#pokeGrantBase'), 'pokeBase');
      loadNatureOptions(row ? row.har : '1');
      $('#pokeGrantTips').addEventListener('change', event => {
        $('#pokeGrantShiny').checked = event.target.value === 'shine';
      });
      $('#pokeGrantIvMax').addEventListener('click', () => setMany(['pokeGrantHpIv', 'pokeGrantAtkIv', 'pokeGrantDefIv', 'pokeGrantSatkIv', 'pokeGrantSdefIv', 'pokeGrantSpeedIv'], '32'));
      $('#pokeGrantEvClear').addEventListener('click', () => setMany(['pokeGrantHpEv', 'pokeGrantAtkEv', 'pokeGrantDefEv', 'pokeGrantSatkEv', 'pokeGrantSdefEv', 'pokeGrantSpeedEv'], '0'));
      $('#pokeGrantStats60').addEventListener('click', () => {
        $('#pokeGrantStatAll').value = '60';
        setMany(['pokeGrantStatHp', 'pokeGrantStatAtk', 'pokeGrantStatDef', 'pokeGrantStatSatk', 'pokeGrantStatSdef', 'pokeGrantStatSpeed', 'pokeGrantHpMy'], '');
      });
      $('#pokeGrantStatsClear').addEventListener('click', () => setMany(['pokeGrantStatAll', 'pokeGrantStatHp', 'pokeGrantStatAtk', 'pokeGrantStatDef', 'pokeGrantStatSatk', 'pokeGrantStatSdef', 'pokeGrantStatSpeed', 'pokeGrantHpMy'], ''));
      $('#grantPokemonBtn').addEventListener('click', grantPokemon);
    }

    if (config.extra === 'attackLearn') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Привязать атаку</h3>
        <input id="learnBase" type="number" placeholder="Base Pokemon ID">
        <input id="learnAttack" type="number" placeholder="Attack ID" value="${row ? esc(row.atac_id) : ''}">
        <input id="learnLevel" type="number" min="1" value="1">
        <select id="learnKind"><option value="level">По уровню</option><option value="egg">Яйцевая</option></select>
        <button type="button" id="saveLearnBtn">Сохранить привязку</button>
      `);
      $('#saveLearnBtn').addEventListener('click', saveAttackLearn);
    }

    if (config.extra === 'tournamentTools') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Участник турнира</h3>
        <input id="turParticipantTournament" type="number" placeholder="Tournament ID" value="${row ? esc(row.id) : ''}">
        <input id="turParticipantUser" type="number" placeholder="User ID">
        <input id="turParticipantPokemon" type="number" placeholder="Pokemon ID">
        <select id="turParticipantStatus">
          <option value="registered">registered</option>
          <option value="checked_in">checked_in</option>
          <option value="eliminated">eliminated</option>
          <option value="winner">winner</option>
          <option value="disqualified">disqualified</option>
        </select>
        <input id="turParticipantScore" type="number" placeholder="Очки" value="0">
        <input id="turParticipantPlace" type="number" placeholder="Место" value="0">
        <button type="button" id="saveTournamentParticipantBtn">Сохранить участника</button>
      `);
      $('#saveTournamentParticipantBtn').addEventListener('click', saveTournamentParticipant);
    }

    if (config.extra === 'medalTools') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Выдать медаль</h3>
        <input id="awardMedalId" type="number" placeholder="Medal ID" value="${row ? esc(row.id) : ''}">
        <input id="awardUserId" type="number" placeholder="User ID">
        <input id="awardTournamentId" type="number" placeholder="Tournament ID" value="${row && row.tournament_id ? esc(row.tournament_id) : ''}">
        <input id="awardComment" type="text" placeholder="Комментарий">
        <button type="button" id="awardMedalBtn">Выдать медаль</button>
      `);
      $('#awardMedalBtn').addEventListener('click', awardMedal);
    }

    if (config.extra === 'commissionTools') {
      if (!row) {
        danger.insertAdjacentHTML('beforeend', `
          <h3>Комиссионная лавка</h3>
          <p class="muted">Выберите лог или лот. Здесь будут детали сделки, средние цены и риск-флаги.</p>
          <div class="admin-inline-actions">
            <button type="button" id="commissionShowRisk">Показать риск</button>
            <button type="button" id="commissionShowReturns">Возвраты</button>
            <button type="button" id="commissionShowSystem">Лоты Система</button>
          </div>
        `);
        $('#commissionShowRisk')?.addEventListener('click', () => {
          state.filters = { risky: '1', sort: 'price_desc' };
          renderFilterbar(modules.commission);
          reloadCurrent();
        });
        $('#commissionShowReturns')?.addEventListener('click', async () => {
          const result = await request('/api/admin/commission/returns?status=pending');
          setStatus(result.ok ? `Возвраты pending: ${(result.returns || []).length}` : (result.message || 'Ошибка возвратов'), !result.ok);
        });
        $('#commissionShowSystem')?.addEventListener('click', () => {
          state.filters = { system_only: '1' };
          renderFilterbar(modules.commission);
          reloadCurrent();
        });
        return;
      }

      const risk = row.risk || {};
      const snapshot = row.snapshot || row.data?.snapshot || {};
      danger.insertAdjacentHTML('beforeend', `
        <h3>Детали сделки</h3>
        <span class="admin-risk-badge ${risk.is_risky && !risk.reviewed ? '' : 'is-ok'}">${esc(risk.risk_label || 'ОК')}</span>
        <div class="admin-detail-grid">
          <div><b>Лот</b>#${esc(row.lot_id || row.id)} · ${esc(row.status || '')}</div>
          <div><b>Объект</b>${esc(objectLabel(row))}</div>
          <div><b>Количество</b>${esc(row.quantity || 0)} × ${esc(fmtMoney(row.price_per_unit || 0))}</div>
          <div><b>Итого</b>${esc(fmtMoney(row.total_price || 0))} · комиссия ${esc(fmtMoney(row.commission_amount || 0))} · продавцу ${esc(fmtMoney(row.seller_income || 0))}</div>
          <div><b>Продавец</b>#${esc(row.seller_id || '')} ${esc(row.seller_name || '')}</div>
          <div><b>Покупатель</b>${row.buyer_id ? '#' + esc(row.buyer_id) + ' ' + esc(row.buyer_name || '') : 'нет'}</div>
          <div><b>Время</b>${esc(row.log_created_at_text || row.sold_at_text || row.created_at_text || '')}</div>
          <div><b>Legacy</b>${esc(row.legacy_source_type || '')} ${esc(row.legacy_source_id || '')}</div>
        </div>
        <h3>Снимок объекта</h3>
        ${snapshotSummaryHtml(row, snapshot)}
        <details class="admin-json-details">
          <summary>Показать JSON-снимок</summary>
          <pre class="admin-json-preview">${esc(safeJson(snapshot))}</pre>
        </details>
        <div class="admin-inline-actions">
          <button type="button" id="commissionSeller">Открыть продавца</button>
          <button type="button" id="commissionBuyer">Открыть покупателя</button>
          <button type="button" id="commissionSimilar">Похожие лоты</button>
          <button type="button" id="commissionHistory">История цены</button>
          ${risk.is_risky && !risk.reviewed ? '<button type="button" id="commissionApproveRisk">Проверено, сделка норм</button>' : ''}
        </div>
        <div id="commissionHistoryBox"></div>
      `);
      $('#commissionSeller')?.addEventListener('click', () => {
        setTab('users');
        $('#adminSearchInput').value = row.seller_name || String(row.seller_id || '');
        reloadCurrent();
      });
      $('#commissionBuyer')?.addEventListener('click', () => {
        if (!row.buyer_id) {
          setStatus('У сделки пока нет покупателя.', true);
          return;
        }
        setTab('users');
        $('#adminSearchInput').value = row.buyer_name || String(row.buyer_id || '');
        reloadCurrent();
      });
      $('#commissionSimilar')?.addEventListener('click', () => {
        state.filters = { object_type: row.object_type || '', object_id: String(row.object_id || ''), sort: 'unit_asc' };
        renderFilterbar(modules.commission);
        reloadCurrent();
      });
      $('#commissionHistory')?.addEventListener('click', () => loadCommissionHistory(row));
      $('#commissionApproveRisk')?.addEventListener('click', () => approveCommissionRisk(row));
    }

    if (config.extra === 'economyGuardTools') {
      if (!row) {
        danger.insertAdjacentHTML('beforeend', `
          <h3>Economy Guard</h3>
          <p class="muted">Сканирует лавку, reward pipeline и балансы: подозрительные цены, трансферы между игроками и резкий рост монет.</p>
          <div class="admin-inline-actions">
            <button type="button" id="economyGuardDryRun">Dry-run scan</button>
            <button type="button" id="economyGuardRun">Запустить scan</button>
            <button type="button" id="economyGuardOpenOnly">Только open</button>
            <button type="button" id="economyGuardCritical">Критичные</button>
          </div>
        `);
        $('#economyGuardDryRun')?.addEventListener('click', () => runEconomyGuardScan(true));
        $('#economyGuardRun')?.addEventListener('click', () => runEconomyGuardScan(false));
        $('#economyGuardOpenOnly')?.addEventListener('click', () => {
          state.filters = { status: 'open' };
          renderFilterbar(modules.economy_guard);
          reloadCurrent();
        });
        $('#economyGuardCritical')?.addEventListener('click', () => {
          state.filters = { status: 'open', severity: 'critical' };
          renderFilterbar(modules.economy_guard);
          reloadCurrent();
        });
        return;
      }

      const details = row.details || {};
      danger.insertAdjacentHTML('beforeend', `
        <h3>Economy Alert</h3>
        <span class="admin-risk-badge ${row.status === 'reviewed' || row.status === 'ignored' ? 'is-ok' : ''}">
          ${esc(row.severity || '')} · score ${esc(row.score || 0)} · ${esc(row.status || '')}
        </span>
        <div class="admin-detail-grid">
          <div><b>Alert</b>#${esc(row.id)} · ${esc(row.alert_type || '')}</div>
          <div><b>Игрок</b>${esc(row.user_login || ('#' + (row.user_id || 0)))}</div>
          <div><b>Связанный</b>${esc(row.related_user_login || (row.related_user_id ? '#' + row.related_user_id : 'нет'))}</div>
          <div><b>Сумма</b>${esc(fmtMoney(row.amount || 0))}</div>
          <div><b>Объект</b>${esc(row.entity_type || '')} ${row.entity_id ? '#' + esc(row.entity_id) : ''}</div>
          <div><b>Первый раз</b>${esc(fmtTime(row.first_seen_at || 0))}</div>
          <div><b>Последний раз</b>${esc(fmtTime(row.last_seen_at || 0))}</div>
          <div><b>Заметка</b>${esc(row.note || '')}</div>
        </div>
        <p class="audit-line">${esc(row.title || '')}</p>
        <details class="admin-json-details" open>
          <summary>Детали проверки</summary>
          <pre class="admin-json-preview">${esc(safeJson(details))}</pre>
        </details>
        <div class="admin-inline-actions">
          <button type="button" id="economyGuardReviewOk">Проверено, норма</button>
          <button type="button" id="economyGuardIgnore">Игнорировать</button>
          <button type="button" id="economyGuardReopen">Вернуть в open</button>
          <button type="button" id="economyGuardUserFilter">Все алерты игрока</button>
        </div>
      `);
      $('#economyGuardReviewOk')?.addEventListener('click', () => reviewEconomyGuardAlert(row, 'reviewed'));
      $('#economyGuardIgnore')?.addEventListener('click', () => reviewEconomyGuardAlert(row, 'ignored'));
      $('#economyGuardReopen')?.addEventListener('click', () => reviewEconomyGuardAlert(row, 'open'));
      $('#economyGuardUserFilter')?.addEventListener('click', () => {
        state.filters = { user_id: String(row.user_id || '') };
        renderFilterbar(modules.economy_guard);
        reloadCurrent();
      });
    }

    if (config.extra === 'battleReplayTools') {
      if (!row) {
        danger.insertAdjacentHTML('beforeend', `
          <h3>Battle Replay</h3>
          <p class="muted">Выберите бой в таблице. В инспекторе появятся снимки состояния, логи раундов, броски рандома и damage audit.</p>
        `);
        return;
      }
      danger.insertAdjacentHTML('beforeend', `
        <h3>Replay #${esc(row.battle_id || '')}</h3>
        <p class="audit-line">
          <b>${esc(row.battle_type || '')}</b> · ${esc(row.status || '')} · раундов ${esc(row.rounds || 0)}<br>
          <small>${esc(row.user_1_login || ('#' + (row.user_1 || 0)))} vs ${esc(row.user_2_login || (row.user_2 ? '#' + row.user_2 : 'wild'))}</small>
        </p>
        <div class="admin-inline-actions">
          <button type="button" id="battleReplayLoad">Открыть replay</button>
          <button type="button" id="battleReplayFilter">Найти этот бой</button>
        </div>
        <div id="battleReplayBox"></div>
      `);
      $('#battleReplayLoad')?.addEventListener('click', () => loadAdminBattleReplay(row));
      $('#battleReplayFilter')?.addEventListener('click', () => {
        state.filters = { q: String(row.battle_id || '') };
        renderFilterbar(modules.battle_replays);
        reloadCurrent();
      });
    }

    if (config.extra === 'legacyActions' && row) {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Legacy-раздел</h3>
        <p class="legacy-meta"><b>Источник:</b> ${esc(row.source)}<br>
        <b>Тип:</b> ${esc(row.source_type)} · <b>Размер:</b> ${esc(row.source_size || 0)} байт<br>
        <b>Таблица:</b> ${esc(row.data_table || 'нет')} · ${esc(row.data_status || '')}</p>
        <p class="legacy-note">${esc(row.notes || '')}</p>
        ${row.admin_tab && row.admin_tab !== 'legacy' ? '<button type="button" id="legacyOpenTab">Открыть новый раздел</button>' : ''}
        ${row.new_route ? '<button type="button" id="legacyOpenRoute">Открыть страницу</button>' : ''}
        ${row.api_route ? '<button type="button" id="legacyCheckApi">Проверить API</button>' : ''}
      `);
      $('#legacyOpenTab')?.addEventListener('click', () => setTab(row.admin_tab));
      $('#legacyOpenRoute')?.addEventListener('click', () => window.open(row.new_route, '_blank'));
      $('#legacyCheckApi')?.addEventListener('click', async () => {
        const result = await request(row.api_route);
        setStatus(result.ok ? `API отвечает: ${row.api_route}` : (result.message || result.error || 'API вернул ошибку'), !result.ok);
      });
    }

    if (config.extra === 'audit') {
      request('/api/admin/audit').then(data => {
        if (!data.ok) return;
        danger.insertAdjacentHTML('beforeend', '<h3>Последний аудит</h3>' + (data.audit || []).slice(0, 12).map(row => `
          <p class="audit-line"><b>${esc(row.action)}</b> ${esc(row.entity)} #${esc(row.entity_id)}<br><small>${esc(row.admin_login || row.admin_id)} · ${esc(row.created_at)}</small></p>
        `).join(''));
      });
    }
  }

  async function userBan(userId, mode) {
    const result = await send('/api/admin/users/ban', { user_id: userId, mode });
    setStatus(result.message || '', !result.ok);
  }

  async function moderationAction(action, duration) {
    const form = $('#adminForm');
    const data = Object.fromEntries(new FormData(form).entries());
    data.action = action;
    data.duration = duration || data.duration || '';
    if (!data.target) {
      setStatus('Сначала выберите строку с игроком или введите ник/ID.', true);
      return;
    }
    if (!data.reason) {
      data.reason = action === 'warn' ? 'Предупреждение модератора.' : 'Нарушение правил.';
    }
    const result = await send('/api/admin/moderation/action', data);
    setStatus(result.message || '', !result.ok);
    if (result.ok) reloadCurrent();
  }

  async function grantItem() {
    const userValue = $('#grantItemUser').value.trim();
    const itemValue = $('#grantItemLookup').value.trim();
    const result = await send('/api/admin/items/grant', {
      user_id: lookupId(userValue),
      user: userValue,
      item_id: lookupId(itemValue),
      item: itemValue,
      count: $('#grantItemCount').value,
      temporary: $('#grantItemTemporary')?.checked ? '1' : '0',
      expires_at: $('#grantItemExpiresAt')?.value || '',
      expires_seconds: $('#grantItemExpiresSeconds')?.value || ''
    });
    if (result.ok) {
      await reloadCurrent();
      setStatus(result.message || 'Предмет выдан.');
      return;
    }
    setStatus(result.message || '', true);
  }

  async function grantPokemon() {
    const userValue = $('#pokeGrantUser').value.trim();
    const baseValue = $('#pokeGrantBase').value.trim();
    const result = await send('/api/admin/pokemon/grant', {
      user_id: lookupId(userValue),
      user: userValue,
      base_id: lookupId(baseValue),
      pokemon: baseValue,
      lvl: $('#pokeGrantLvl').value,
      sex: $('#pokeGrantSex').value,
      har: $('#pokeGrantHar').value,
      tips: $('#pokeGrantTips').value,
      shiny: $('#pokeGrantShiny').checked || $('#pokeGrantTips').value === 'shine' ? '1' : '0',
      hp_iv: $('#pokeGrantHpIv').value,
      atk_iv: $('#pokeGrantAtkIv').value,
      def_iv: $('#pokeGrantDefIv').value,
      satk_iv: $('#pokeGrantSatkIv').value,
      sdef_iv: $('#pokeGrantSdefIv').value,
      speed_iv: $('#pokeGrantSpeedIv').value,
      hp_ev: $('#pokeGrantHpEv').value,
      atk_ev: $('#pokeGrantAtkEv').value,
      def_ev: $('#pokeGrantDefEv').value,
      satk_ev: $('#pokeGrantSatkEv').value,
      sdef_ev: $('#pokeGrantSdefEv').value,
      speed_ev: $('#pokeGrantSpeedEv').value,
      stat_all: $('#pokeGrantStatAll').value,
      stat_hp: $('#pokeGrantStatHp').value,
      stat_atk: $('#pokeGrantStatAtk').value,
      stat_def: $('#pokeGrantStatDef').value,
      stat_satk: $('#pokeGrantStatSatk').value,
      stat_sdef: $('#pokeGrantStatSdef').value,
      stat_speed: $('#pokeGrantStatSpeed').value,
      hp_my: $('#pokeGrantHpMy').value
    });
    if (result.ok) {
      await reloadCurrent();
      setStatus(result.message || 'Покемон выдан.');
      return;
    }
    setStatus(result.message || '', true);
  }

  async function loadNatureOptions(selected = '1') {
    const select = $('#pokeGrantHar');
    if (!select) return;
    const result = await request('/api/admin/lookups?type=natures');
    if (!result.ok || !Array.isArray(result.rows)) return;
    select.innerHTML = result.rows.map(row => `<option value="${esc(row.id)}">${esc(row.label || row.name || ('#' + row.id))}</option>`).join('');
    select.value = String(selected || '1');
    if (select.value !== String(selected || '1')) {
      select.value = '1';
    }
  }

  async function saveAttackLearn() {
    const result = await send('/api/admin/attacks/learn', {
      poke_base_id: $('#learnBase').value,
      atac_id: $('#learnAttack').value,
      atc_lvl: $('#learnLevel').value,
      kind: $('#learnKind').value
    });
    setStatus(result.message || '', !result.ok);
  }

  async function saveTournamentParticipant() {
    const result = await send('/api/admin/tournaments/participant', {
      tournament_id: $('#turParticipantTournament').value,
      user_id: $('#turParticipantUser').value,
      pokemon_id: $('#turParticipantPokemon').value,
      status: $('#turParticipantStatus').value,
      score: $('#turParticipantScore').value,
      place_num: $('#turParticipantPlace').value
    });
    setStatus(result.message || '', !result.ok);
    if (result.ok) reloadCurrent();
  }

  async function awardMedal() {
    const result = await send('/api/admin/medals/award', {
      medal_id: $('#awardMedalId').value,
      user_id: $('#awardUserId').value,
      tournament_id: $('#awardTournamentId').value,
      comment: $('#awardComment').value
    });
    setStatus(result.message || '', !result.ok);
    if (result.ok) reloadCurrent();
  }

  async function loadCommissionHistory(row) {
    const params = new URLSearchParams({
      object_type: row.object_type || '',
      object_id: String(row.object_id || ''),
      category: row.category || ''
    });
    const result = await request('/api/admin/commission/price-history?' + params.toString());
    const box = $('#commissionHistoryBox');
    if (!box) return;
    if (!result.ok) {
      box.innerHTML = `<p class="admin-status is-bad">${esc(result.message || 'Не удалось загрузить историю.')}</p>`;
      return;
    }
    const windows = result.windows || [];
    const active = result.active || [];
    const last = result.lastSold || null;
    box.innerHTML = '<h3>Средняя цена</h3>' + (windows.map(item => `
      <p class="audit-line"><b>${esc(item.days)}д</b> продаж ${esc(item.sales)} · средняя ${esc(fmtMoney(item.avg_unit))} · мин/макс ${esc(fmtMoney(item.min_unit))}/${esc(fmtMoney(item.max_unit))}<br>
      <small>оборот ${esc(fmtMoney(item.turnover))}, комиссия ${esc(fmtMoney(item.commission))}</small></p>
    `).join('') || '<p class="muted">Продаж нет.</p>') + `
      <h3>Последняя продажа</h3>
      ${last ? `<p class="audit-line">#${esc(last.lot_id || last.id)} · ${esc(last.object_name || '')}<br><small>${esc(fmtMoney(last.total_price || 0))} · ${esc(last.sold_at_text || '')}</small></p>` : '<p class="muted">Продаж по этому объекту ещё нет.</p>'}
      <h3>Активные похожие лоты</h3>
      ${active.length ? active.slice(0, 8).map(lot => `<p class="audit-line">#${esc(lot.lot_id || lot.id)} · ${esc(lot.object_name || '')}<br><small>${esc(fmtMoney(lot.price_per_unit || 0))} за штуку · ${esc(lot.seller_name || '')}</small></p>`).join('') : '<p class="muted">Активных похожих лотов нет.</p>'}
    `;
  }

  async function loadAdminBattleReplay(row) {
    const box = $('#battleReplayBox');
    if (!box) return;
    box.innerHTML = '<p class="muted">Загружаем replay...</p>';
    const result = await request('/api/admin/battle-replays/view?battle_id=' + encodeURIComponent(row.battle_id || 0));
    if (!result.ok) {
      box.innerHTML = `<p class="admin-status is-bad">${esc(result.message || 'Replay недоступен.')}</p>`;
      return;
    }
    const replay = result.replay || {};
    const summary = replay.summary || {};
    const rounds = Array.isArray(replay.rounds) ? replay.rounds : [];
    const byType = summary.byType || {};
    box.innerHTML = `
      <h3>Сводка</h3>
      <p class="audit-line">
        events ${esc(summary.events || 0)} · snapshots ${esc(byType.snapshot || 0)} · logs ${esc(byType.round_log || 0)} · rolls ${esc(byType.random_roll || 0)} · damage ${esc(byType.damage || 0)}
      </p>
      <h3>Раунды</h3>
      ${rounds.length ? rounds.map(round => {
        const logs = (round.logs || []).slice(-5).map(item => `<li>${esc(item)}</li>`).join('');
        const damage = (round.damage || []).slice(-5).map(item => `<li>${esc(item.move_name || item.moveName || 'damage')}: ${esc(item.damage || 0)} HP (${esc(item.actor_key || '')} -> ${esc(item.target_key || '')})</li>`).join('');
        const rolls = (round.randomRolls || []).slice(-6).map(item => `<li>${esc(item.label || 'roll')}: ${esc(item.roll)} / ${esc(item.threshold ?? '-')} ${item.success === true ? 'OK' : (item.success === false ? 'FAIL' : '')}</li>`).join('');
        return `
          <details class="admin-replay-round">
            <summary>Раунд ${esc(round.round)} · logs ${(round.logs || []).length} · rolls ${(round.randomRolls || []).length} · damage ${(round.damage || []).length}</summary>
            ${logs ? `<b>Логи</b><ul>${logs}</ul>` : ''}
            ${damage ? `<b>Урон</b><ul>${damage}</ul>` : ''}
            ${rolls ? `<b>Random</b><ul>${rolls}</ul>` : ''}
          </details>
        `;
      }).join('') : '<p class="muted">Событий пока нет.</p>'}
      <details>
        <summary>Raw replay JSON</summary>
        <pre>${esc(JSON.stringify(replay, null, 2))}</pre>
      </details>
    `;
  }

  async function approveCommissionRisk(row) {
    const result = await send('/api/admin/commission/risk-review', {
      lot_id: row.lot_id || row.id,
      note: 'Проверено вручную: сделка нормальная.'
    });
    setStatus(result.message || '', !result.ok);
    if (result.ok) reloadCurrent();
  }

  async function runEconomyGuardScan(dryRun) {
    const result = await send('/api/admin/economy-guard/scan', {
      dry_run: dryRun ? '1' : '0',
      limit: '300'
    });
    const summary = result.summary || {};
    const checks = summary.checks || {};
    const checkText = Object.keys(checks).map(key => `${key}: ${checks[key]}`).join(', ');
    setStatus(result.ok ? `Economy Guard ${dryRun ? 'dry-run' : 'scan'}: created ${summary.created || 0}, updated ${summary.updated || 0}. ${checkText}` : (result.message || 'Scan failed.'), !result.ok);
    if (result.ok && !dryRun) reloadCurrent();
  }

  async function reviewEconomyGuardAlert(row, status) {
    const note = status === 'reviewed'
      ? 'Проверено вручную: алерт нормальный.'
      : (status === 'ignored' ? 'Игнорировать в текущем виде.' : 'Возвращено в open.');
    const result = await send('/api/admin/economy-guard/review', {
      alert_id: row.id,
      status,
      note
    });
    setStatus(result.message || '', !result.ok);
    if (result.ok) reloadCurrent();
  }

  document.querySelectorAll('[data-admin-tab]').forEach(btn => btn.addEventListener('click', () => setTab(btn.dataset.adminTab)));
  $('#adminRefresh').addEventListener('click', () => reloadCurrent());
  $('#adminCreate').addEventListener('click', () => buildForm(modules[state.tab], null));
  $('#adminSearchForm').addEventListener('submit', event => {
    event.preventDefault();
    state.page = 1;
    reloadCurrent();
  });
  $('#adminPrevPage')?.addEventListener('click', () => {
    if (!state.pagination || state.pagination.page <= 1) return;
    state.page -= 1;
    reloadCurrent();
  });
  $('#adminNextPage')?.addEventListener('click', () => {
    if (!state.pagination || state.pagination.page >= state.pagination.pages) return;
    state.page += 1;
    reloadCurrent();
  });

  setTab('dashboard');
})();
