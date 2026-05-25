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
        return [
          ...Object.keys(overview).map(key => ({ block: key, value: overview[key], details: 'count' })),
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
        row.basenum + (row.base_name ? ' ' + row.base_name : ''),
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
      subtitle: 'Глобальные x2/x4 события: опыт, монеты, дроп и другие множители.',
      endpoint: '/api/admin/events',
      dataKey: 'events',
      save: '/api/admin/events',
      delete: { url: '/api/admin/events', id: 'id', confirm: true },
      columns: ['ID', 'Название', 'Ключ', 'Множитель', 'Период', 'Вкл'],
      cells: row => [row.id, row.title, row.boost_key, 'x' + row.multiplier, `${row.starts_at || 0}-${row.ends_at || 0}`, row.enabled],
      fields: [
        ['id', 'ID', 'number'],
        ['title', 'Название', 'text'],
        ['boost_key', 'Что бустим', 'select:exp,coins,drop,quest_rewards,catch'],
        ['multiplier', 'Множитель', 'number'],
        ['scope', 'Область', 'select:global,pve,pvp,quest,market'],
        ['starts_at', 'Старт Unix', 'number'],
        ['ends_at', 'Конец Unix', 'number'],
        ['enabled', 'Включено', 'checkbox'],
        ['note', 'Заметка', 'text']
      ]
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
      const values = type.slice(7).split(',');
      return `<label>${esc(label)}<select name="${esc(name)}">${values.map(option => `<option value="${esc(option)}" ${String(value) === option ? 'selected' : ''}>${esc(option || 'любой')}</option>`).join('')}</select></label>`;
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
      return `<tr data-row="${index}">${cells}</tr>`;
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
      const values = type.slice(7).split(',');
      return `<label>${esc(label)}<select name="${esc(name)}" ${readonly ? 'disabled' : ''}>${values.map(option => `<option value="${esc(option)}" ${String(value) === option ? 'selected' : ''}>${esc(option || 'не задано')}</option>`).join('')}</select></label>`;
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

    if (config.extra === 'grantItem') {
      danger.insertAdjacentHTML('beforeend', `
        <h3>Выдать предмет</h3>
        <input id="grantUserId" type="number" placeholder="User ID">
        <input id="grantItemId" type="number" placeholder="Item ID" value="${row ? esc(row.id) : ''}">
        <input id="grantItemCount" type="number" min="1" value="1">
        <label class="admin-check"><input id="grantItemTemporary" type="checkbox"> Выдать на время</label>
        <div class="admin-temp-fields" id="grantItemTemporaryFields" hidden>
          <label>Действует до
            <input id="grantItemExpiresAt" type="datetime-local">
          </label>
          <label>Или секунд от сейчас
            <input id="grantItemExpiresSeconds" type="number" min="1" placeholder="3600">
          </label>
        </div>
        <button type="button" id="grantItemBtn">Выдать</button>
      `);
      $('#grantItemTemporary').addEventListener('change', event => {
        $('#grantItemTemporaryFields').hidden = !event.target.checked;
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
        <input id="pokeGrantUser" type="text" placeholder="User ID или ник" value="${row ? esc(row.users || row.login || '') : ''}">
        <input id="pokeGrantBase" type="text" placeholder="Base ID или имя покемона" value="${row ? esc(row.basenum || row.base_name || '') : ''}">
        <input id="pokeGrantLvl" type="number" min="1" max="100" value="5">
        <select id="pokeGrantSex">
          <option value="1" ${row && String(row.sex) === '1' ? 'selected' : ''}>Самец</option>
          <option value="2" ${row && String(row.sex) === '2' ? 'selected' : ''}>Самка</option>
        </select>
        <select id="pokeGrantHar"><option value="1">Характер #1</option></select>
        <select id="pokeGrantTips">
          <option value="normal" ${row && String(row.tips || '').toLowerCase().includes('shine') ? '' : 'selected'}>Обычный</option>
          <option value="shine" ${row && String(row.tips || '').toLowerCase().includes('shine') ? 'selected' : ''}>Shiny</option>
        </select>
        <label class="admin-check"><input id="pokeGrantShiny" type="checkbox" ${row && String(row.tips || '').toLowerCase().includes('shine') ? 'checked' : ''}> Shiny</label>
        <h4>Гены IV</h4>
        <div class="admin-grid-mini">
          <label>HP IV<input id="pokeGrantHpIv" type="number" min="0" max="31" value="${grantValue('hp_iv', '1')}" placeholder="0-31"></label>
          <label>Атака IV<input id="pokeGrantAtkIv" type="number" min="0" max="31" value="${grantValue('atk_iv', '1')}" placeholder="0-31"></label>
          <label>Защита IV<input id="pokeGrantDefIv" type="number" min="0" max="31" value="${grantValue('def_iv', '1')}" placeholder="0-31"></label>
          <label>Спец. атака IV<input id="pokeGrantSatkIv" type="number" min="0" max="31" value="${grantValue('satk_iv', '1')}" placeholder="0-31"></label>
          <label>Спец. защита IV<input id="pokeGrantSdefIv" type="number" min="0" max="31" value="${grantValue('sdef_iv', '1')}" placeholder="0-31"></label>
          <label>Скорость IV<input id="pokeGrantSpeedIv" type="number" min="0" max="31" value="${grantValue('speed_iv', '1')}" placeholder="0-31"></label>
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
      loadNatureOptions(row ? row.har : '1');
      $('#pokeGrantTips').addEventListener('change', event => {
        $('#pokeGrantShiny').checked = event.target.value === 'shine';
      });
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
    const result = await send('/api/admin/items/grant', {
      user_id: $('#grantUserId').value,
      item_id: $('#grantItemId').value,
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
    const result = await send('/api/admin/pokemon/grant', {
      user_id: /^\d+$/.test($('#pokeGrantUser').value.trim()) ? $('#pokeGrantUser').value.trim() : '',
      user: $('#pokeGrantUser').value,
      base_id: /^\d+$/.test($('#pokeGrantBase').value.trim()) ? $('#pokeGrantBase').value.trim() : '',
      pokemon: $('#pokeGrantBase').value,
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
