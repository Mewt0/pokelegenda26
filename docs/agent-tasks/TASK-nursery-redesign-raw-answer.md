views/game-pokemon.php — добавить состояние питомника после let activeLearn = null;PHP    let team = [];
    let selectedId = 0;
    let detailOpen = false;
    let activeLearn = null;
    let nurseryOpen = false;
    const nurseryState = { search: '', from: '', to: '', group: '', favorites: false, values: {} };views/game-pokemon.php — заменить renderTeamView(), сохранив плитки активной командыPHP    function renderTeamView() {
      closePopups();
      teamBody.classList.toggle('is-overview', !detailOpen);
      list.innerHTML = '';

      if (!team.length) {
        list.className = 'team-list is-overview';
        appendEmptyTile(list, 'Покемонов нет');
        detail.innerHTML = '';
        detailId.textContent = '';
        return;
      }

      if (!detailOpen) {
        list.className = 'team-list is-overview' + (nurseryOpen ? ' is-nursery' : '');
        if (nurseryOpen) {
          renderNurseryView();
        } else {
          renderPokemonSection('Активная команда', activePokemon(), 'active', 'Команда пуста.');
          const openNursery = document.createElement('button');
          openNursery.type = 'button';
          openNursery.className = 'nursery-entry';
          openNursery.textContent = 'Питомник · ' + nurseryPokemon().length + ' покемонов →';
          openNursery.addEventListener('click', () => {
            nurseryOpen = true;
            renderAll();
          });
          list.appendChild(openNursery);
        }
        detail.innerHTML = '';
        detailId.textContent = '';
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
    }views/game-pokemon.php — добавить функции питомника перед renderTilesOnly()PHP    // Питомник работает с тем же массивом team, который загружает load().
    const nurseryFilterDefinitions = [
      ['type', 'Тип покемона'],
      ['tera', 'Тера-тип'],
      ['nature', 'Характер'],
      ['breeding', 'Разведение'],
      ['gender', 'Пол'],
      ['sympathy', 'Симпатия'],
      ['group', 'Группа покемона'],
      ['species', 'Вид покемона'],
      ['ability', 'Со способностью'],
      ['ancient', 'Древняя Сила'],
      ['quality', 'Кач-во'],
    ];

    // Только ID, явно приведённые для lookupNatures в ТЗ.
    // Для остальных — нет данных.
    const nurseryNatureNames = {
      1: 'Весёлый',
      2: 'Выносливый',
      3: 'Послушный',
      4: 'Серьёзный',
      5: 'Спокойный',
      6: 'Мягкий',
      7: 'Смелый',
      8: 'Наивный',
      9: 'Нахальный',
      10: 'Кроткий',
      11: 'Озорной',
      12: 'Непреклонный',
      13: 'Застенчивый',
    };

    const nurseryIcons = {
      search: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>',
      star: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.4 7 .9-5.1 5 .9 7.1-5.9-3.3-5.9 3.3.9-7.1-5.1-5 7-.9z"/></svg>',
      pencil: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 20 4.5-1 11-11-3.5-3.5-11 11L4 20zM14.5 6l3.5 3.5M4 20h16"/></svg>',
      check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 12 5 5L20 6"/></svg>',
      user: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>',
      trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v7M14 11v7"/></svg>',
      bag: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="8" width="16" height="13" rx="2"/><path d="M9 8V5a3 3 0 0 1 6 0v3M9 14h6M12 11v6"/></svg>',
      arrow: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12H5m6-6-6 6 6 6"/></svg>',
    };

    function nurseryRaw(poke, keys) {
      for (const key of keys) {
        if (
          Object.prototype.hasOwnProperty.call(poke, key)
          && poke[key] !== null
          && poke[key] !== ''
        ) {
          return poke[key];
        }
      }
      return null;
    }

    function nurseryText(value) {
      if (value === null || value === undefined || value === '') return '';
      if (Array.isArray(value)) {
        return value.map(nurseryText).filter(Boolean).join(', ');
      }
      if (typeof value === 'object') {
        return nurseryText(value.name ?? value.label ?? null);
      }
      if (typeof value === 'boolean') return value ? 'Да' : 'Нет';
      return String(value).trim();
    }

    function nurseryGender(poke) {
      const value = nurseryRaw(poke, ['gender', 'sex']);
      if (value === null) return '';

      const text = String(value).trim().toLowerCase();

      if (['male', 'm', '♂', 'мужской', 'самец'].includes(text)) return '♂';
      if (['female', 'f', '♀', 'женский', 'самка'].includes(text)) return '♀';
      if (['genderless', 'none', 'бесполый', '—'].includes(text)) return 'Бесполый';

      // Числовой код пола без документированной схемы не угадываем.
      return '';
    }

    function nurseryNature(poke) {
      const name = nurseryRaw(poke, ['natureName', 'nature_name']);
      if (typeof name === 'string' && /[а-яё]/i.test(name)) {
        return name.trim();
      }

      const raw = nurseryRaw(poke, ['natureId', 'nature_id', 'nature']);
      const id = raw && typeof raw === 'object'
        ? (raw.id ?? raw.name ?? null)
        : raw;

      if (typeof id === 'string' && /[а-яё]/i.test(id)) {
        return id.trim();
      }

      return Object.prototype.hasOwnProperty.call(nurseryNatureNames, String(id))
        ? nurseryNatureNames[String(id)]
        : '';
    }

    function nurseryField(poke, key) {
      if (key === 'nature') return nurseryNature(poke);
      if (key === 'gender') return nurseryGender(poke);

      if (key === 'species') {
        const dex = displayDexNumber(poke);
        return dex > 0
          ? '#' + pad3(dex) + ' ' + displayName(poke)
          : '';
      }

      if (key === 'ability') {
        const flag = nurseryRaw(poke, ['hasAbility', 'has_ability']);

        if (flag !== null) {
          return flag === true || flag === 1 || flag === '1'
            ? 'Есть'
            : 'Нет';
        }

        const ability = nurseryRaw(poke, [
          'ability',
          'abilityName',
          'ability_name'
        ]);

        if (ability === null) return '';

        return ability === 0 || ability === false || ability === '0'
          ? 'Нет'
          : 'Есть';
      }

      const fields = {
        type: [
          'pokemonTypes',
          'pokemon_types',
          'types',
          'pokemonType',
          'pokemon_type',
          'type'
        ],
        tera: ['teraType', 'tera_type'],
        breeding: ['breeding'],
        sympathy: ['sympathy', 'affection'],
        group: ['pokemonGroup', 'pokemon_group', 'eggGroup', 'egg_group'],
        ancient: ['ancientPower', 'ancient_power'],
        quality: ['quality'],
      };

      return nurseryText(nurseryRaw(poke, fields[key] || []));
    }

    function nurseryFieldValues(poke, key) {
      if (key !== 'type') {
        return [nurseryField(poke, key)].filter(Boolean);
      }

      const raw = nurseryRaw(poke, [
        'pokemonTypes',
        'pokemon_types',
        'types',
        'pokemonType',
        'pokemon_type',
        'type'
      ]);

      return (Array.isArray(raw) ? raw : [raw])
        .flatMap(item => nurseryText(item).split(/[,/]/))
        .map(value => value.trim())
        .filter(Boolean);
    }

    function nurseryFavorite(poke) {
      const flag = nurseryRaw(poke, [
        'isFavorite',
        'favorite',
        'is_favorite'
      ]);

      if (flag === null) return null;

      return flag === true
        || flag === 1
        || flag === '1'
        || flag === 'true';
    }

    function nurseryNumber(poke, field) {
      if (
        !Object.prototype.hasOwnProperty.call(poke, field)
        || poke[field] === null
        || poke[field] === ''
      ) {
        return null;
      }

      const value = Number(poke[field]);
      return Number.isFinite(value) ? value : null;
    }

    function nurseryPercent(value) {
      return value === null
        ? 'нет данных'
        : value.toFixed(2) + '%';
    }

    function nurseryBar(value) {
      return value === null
        ? 0
        : Math.max(0, Math.min(100, value));
    }

    function nurseryResetFilters() {
      nurseryState.search = '';
      nurseryState.from = '';
      nurseryState.to = '';
      nurseryState.group = '';
      nurseryState.favorites = false;
      nurseryState.values = {};
      renderAll();
    }

    function renderNurseryView() {
      const section = document.createElement('section');
      section.className = 'nursery-view';

      section.innerHTML = [
        '<header class="nursery-heading">',
          '<button type="button" class="nursery-back"></button>',
          '<strong>Питомник</strong>',
          '<small class="nursery-count"></small>',
        '</header>',
        '<div class="nursery-shell">',
          '<aside class="nursery-filters" aria-label="Фильтры питомника">',
            '<div class="nursery-filter-scroll"></div>',
            '<div class="nursery-capacity">',
              '<strong>Вместимость</strong>',
              '<span class="nursery-capacity-info">',
                '<span class="nursery-capacity-number"></span>',
                '<span class="nursery-capacity-icon"></span>',
              '</span>',
            '</div>',
          '</aside>',
          '<div class="nursery-results">',
            '<div class="nursery-toolbar">',
              '<label class="nursery-search">',
                '<span class="nursery-search-icon"></span>',
                '<input type="search" placeholder="Имя или номер покемона" aria-label="Имя или номер покемона">',
              '</label>',
              '<div class="nursery-toolbar-buttons">',
                '<button type="button" class="nursery-round" data-nursery-tool="star" aria-label="Только избранные" title="Только избранные"></button>',
                '<button type="button" class="nursery-round" data-nursery-tool="pencil" aria-label="Редактирование" title="Редактирование недоступно в текущей странице" disabled></button>',
                '<button type="button" class="nursery-round" data-nursery-tool="check" aria-label="Сбросить фильтры" title="Сбросить фильтры"></button>',
                '<button type="button" class="nursery-round" data-nursery-tool="user" aria-label="Активная команда" title="Активная команда"></button>',
              '</div>',
            '</div>',
            '<div class="nursery-found" role="status" aria-live="polite"></div>',
            '<div class="nursery-rows"></div>',
          '</div>',
        '</div>',
      ].join('');

      list.appendChild(section);

      section.querySelector('.nursery-back').innerHTML =
        nurseryIcons.arrow + '<span>Активная команда</span>';

      section.querySelector('.nursery-back').addEventListener('click', () => {
        nurseryOpen = false;
        renderAll();
      });

      const pokemons = nurseryPokemon();

      section.querySelector('.nursery-count').textContent =
        pokemons.length + ' в питомнике';

      section.querySelector('.nursery-capacity-number').textContent =
        pokemons.length + ' / 2000';

      section.querySelector('.nursery-capacity-icon').innerHTML =
        nurseryIcons.bag;

      section.querySelector('.nursery-search-icon').innerHTML =
        nurseryIcons.search;

      const filterBox = section.querySelector('.nursery-filter-scroll');

      addNurserySelect(
        filterBox,
        'Сгруппировать',
        'groupBy',
        [
          ['', 'Без группировки'],
          ['species', 'По виду покемона'],
          ['level', 'По уровню'],
          ['nature', 'По характеру'],
        ],
        nurseryState.group
      );

      const levels = document.createElement('fieldset');
      levels.className = 'nursery-level';

      levels.innerHTML = [
        '<legend>Уровень</legend>',
        '<div class="nursery-level-inputs">',
          '<input type="number" min="1" step="1" placeholder="Ур. от" aria-label="Ур. от">',
          '<input type="number" min="1" step="1" placeholder="Ур. до" aria-label="Ур. до">',
        '</div>',
      ].join('');

      const levelInputs = levels.querySelectorAll('input');

      levelInputs[0].value = nurseryState.from;
      levelInputs[1].value = nurseryState.to;

      levelInputs[0].addEventListener('input', event => {
        nurseryState.from = event.target.value;
        renderNurseryRows(section);
      });

      levelInputs[1].addEventListener('input', event => {
        nurseryState.to = event.target.value;
        renderNurseryRows(section);
      });

      filterBox.appendChild(levels);

      nurseryFilterDefinitions.forEach(([key, label]) => {
        const values = Array.from(
          new Set(
            pokemons.flatMap(poke => nurseryFieldValues(poke, key))
          )
        ).sort((a, b) => a.localeCompare(b, 'ru', { numeric: true }));

        if (
          nurseryState.values[key]
          && !values.includes(nurseryState.values[key])
        ) {
          delete nurseryState.values[key];
        }

        addNurserySelect(
          filterBox,
          label,
          key,
          values.length
            ? [['', 'Все'], ...values.map(value => [value, value])]
            : [['', 'нет данных']],
          nurseryState.values[key] || '',
          !values.length
        );
      });

      const groupSelect = filterBox.querySelector(
        '[data-nursery-filter="groupBy"]'
      );

      groupSelect.querySelector('[value="nature"]').disabled =
        !pokemons.some(poke => nurseryNature(poke));

      const favoriteButton = section.querySelector(
        '[data-nursery-tool="star"]'
      );

      const hasFavorites = pokemons.some(
        poke => nurseryFavorite(poke) !== null
      );

      favoriteButton.disabled = !hasFavorites;

      if (!hasFavorites) {
        favoriteButton.title = 'нет данных об избранных';
      }

      favoriteButton.setAttribute(
        'aria-pressed',
        String(nurseryState.favorites)
      );

      favoriteButton.addEventListener('click', () => {
        nurseryState.favorites = !nurseryState.favorites;
        favoriteButton.setAttribute(
          'aria-pressed',
          String(nurseryState.favorites)
        );
        renderNurseryRows(section);
      });

      section.querySelectorAll('[data-nursery-tool]').forEach(button => {
        button.innerHTML = nurseryIcons[button.dataset.nurseryTool];
      });

      section.querySelector(
        '[data-nursery-tool="check"]'
      ).addEventListener('click', nurseryResetFilters);

      section.querySelector(
        '[data-nursery-tool="user"]'
      ).addEventListener('click', () => {
        nurseryOpen = false;
        renderAll();
      });

      const search = section.querySelector('.nursery-search input');
      search.value = nurseryState.search;

      search.addEventListener('input', event => {
        nurseryState.search = event.target.value;
        renderNurseryRows(section);
      });

      section.querySelector('.nursery-rows').addEventListener('click', event => {
        const take = event.target.closest('[data-nursery-take]');

        if (take) {
          if (!take.disabled) {
            nurseryAction(Number(take.dataset.nurseryTake), 'take');
          }
          return;
        }

        const open = event.target.closest('[data-nursery-open]');

        if (open) {
          selectedId = Number(open.dataset.nurseryOpen);
          detailOpen = true;
          renderAll();
        }
      });

      renderNurseryRows(section);
    }

    function addNurserySelect(
      target,
      label,
      key,
      options,
      value,
      disabled = false
    ) {
      const control = document.createElement('label');
      control.className = 'nursery-select';

      const caption = document.createElement('span');
      caption.textContent = label;

      const select = document.createElement('select');
      select.setAttribute('aria-label', label);
      select.dataset.nurseryFilter = key;
      select.disabled = disabled;

      options.forEach(([optionValue, optionLabel]) => {
        const option = document.createElement('option');
        option.value = optionValue;
        option.textContent = optionLabel;
        select.appendChild(option);
      });

      select.value = value;

      select.addEventListener('change', event => {
        if (key === 'groupBy') {
          nurseryState.group = event.target.value;
        } else {
          nurseryState.values[key] = event.target.value;
        }

        renderNurseryRows(target.closest('.nursery-view'));
      });

      control.append(caption, select);
      target.appendChild(control);
    }

    function nurseryFiltered() {
      const search = normalizeSearch(nurseryState.search);

      const min = nurseryState.from === ''
        ? null
        : Number(nurseryState.from);

      const max = nurseryState.to === ''
        ? null
        : Number(nurseryState.to);

      return nurseryPokemon().filter(poke => {
        const level = nurseryNumber(poke, 'level');

        if (min !== null && (level === null || level < min)) {
          return false;
        }

        if (max !== null && (level === null || level > max)) {
          return false;
        }

        if (
          search
          && ![
            displayName(poke),
            poke.name,
            displayDexNumber(poke),
            poke.baseNum,
            poke.id,
            '#' + pad3(displayDexNumber(poke)),
          ].some(value => normalizeSearch(value).includes(search))
        ) {
          return false;
        }

        if (
          nurseryState.favorites
          && nurseryFavorite(poke) !== true
        ) {
          return false;
        }

        return nurseryFilterDefinitions.every(
          ([key]) => !nurseryState.values[key]
            || nurseryFieldValues(poke, key).includes(
              nurseryState.values[key]
            )
        );
      });
    }

    function renderNurseryRows(section) {
      const box = section.querySelector('.nursery-rows');
      const scrollTop = section.closest('.team-list').scrollTop;

      box.replaceChildren();

      const pokemons = nurseryFiltered();

      section.querySelector('.nursery-found').textContent =
        'Найдено: ' + pokemons.length;

      if (!pokemons.length) {
        const empty = document.createElement('p');
        empty.className = 'nursery-empty';

        empty.textContent = nurseryPokemon().length
          ? 'По выбранным фильтрам ничего не найдено.'
          : 'Питомник пуст.';

        box.appendChild(empty);
        return;
      }

      const group = nurseryState.group;

      const groupKey = poke => {
        if (group === 'level') {
          return nurseryNumber(poke, 'level') === null
            ? 'нет данных'
            : String(poke.level) + ' ур.';
        }

        if (group === 'species') {
          return nurseryField(poke, 'species') || 'нет данных';
        }

        if (group === 'nature') {
          return nurseryNature(poke) || 'нет данных';
        }

        return '';
      };

      if (group) {
        pokemons.sort((a, b) =>
          groupKey(a).localeCompare(groupKey(b), 'ru', { numeric: true })
        );
      }

      let previousGroup = null;
      const fragment = document.createDocumentFragment();

      pokemons.forEach(poke => {
        if (group && groupKey(poke) !== previousGroup) {
          previousGroup = groupKey(poke);

          const head = document.createElement('h3');
          head.className = 'nursery-group-heading';
          head.textContent = previousGroup;

          fragment.appendChild(head);
        }

        fragment.appendChild(renderNurseryRow(poke));
      });

      box.appendChild(fragment);
      section.closest('.team-list').scrollTop = scrollTop;
    }

    function renderNurseryRow(poke) {
      const row = document.createElement('article');
      row.className = 'nursery-row';

      const id = Number(poke.id || 0);
      const dex = displayDexNumber(poke);

      const hp = nurseryNumber(poke, 'hp');
      const hpMax = nurseryNumber(poke, 'hpMax');

      const hpPct = hp !== null && hpMax !== null && hpMax > 0
        ? hp / hpMax * 100
        : null;

      const sp = nurseryNumber(poke, 'sp');
      const spMax = nurseryNumber(poke, 'spMax');
      const spExplicit = nurseryNumber(poke, 'spPercent');

      const spPct = spExplicit !== null
        ? spExplicit
        : sp !== null && spMax !== null && spMax > 0
          ? sp / spMax * 100
          : null;

      const expPct =
        nurseryNumber(poke, 'expPercent')
        ?? nurseryNumber(poke, 'experiencePercent');

      const hpLabel = hp === null || hpMax === null
        ? 'нет данных'
        : hp + ' | ' + hpMax;

      const spLabel = sp === null || spMax === null
        ? 'нет данных'
        : sp + ' | ' + spMax;

      row.innerHTML = [
        '<button type="button" class="nursery-sprite" data-nursery-open="' + id + '" aria-label="Открыть покемона">',
          '<img alt="" loading="lazy">',
        '</button>',
        '<div class="nursery-vitals">',
          '<div class="nursery-meter nursery-meter-hp">',
            '<div class="nursery-meter-text">',
              '<b>HP</b>',
              '<span>' + hpLabel + '</span>',
              '<small>' + nurseryPercent(hpPct) + '</small>',
            '</div>',
            '<div class="nursery-track"><i style="width:' + nurseryBar(hpPct) + '%"></i></div>',
          '</div>',
          '<div class="nursery-experience" title="' + (
            expPct === null
              ? 'Нет данных об опыте'
              : 'Опыт: ' + nurseryPercent(expPct)
          ) + '">',
            '<i style="width:' + nurseryBar(expPct) + '%"></i>',
          '</div>',
          '<div class="nursery-meter nursery-meter-sp">',
            '<div class="nursery-meter-text">',
              '<b>SP</b>',
              '<span>' + spLabel + '</span>',
              '<small>' + nurseryPercent(spPct) + '</small>',
            '</div>',
            '<div class="nursery-track"><i style="width:' + nurseryBar(spPct) + '%"></i></div>',
          '</div>',
        '</div>',
        '<div class="nursery-stats">',
          '<button type="button" class="nursery-pokemon-name" data-nursery-open="' + id + '"></button>',
          '<div class="nursery-stat-grid"></div>',
        '</div>',
        '<div class="nursery-meta">',
          '<span class="nursery-nature"></span>',
          '<div class="nursery-meta-bottom">',
            '<span class="nursery-gender"></span>',
            '<span class="nursery-level"></span>',
            '<button type="button" class="nursery-take" data-nursery-take="' + id + '" title="В команду" aria-label="Забрать в команду"></button>',
            '<button type="button" class="nursery-trash" disabled title="Удаление не поддерживается текущим API" aria-label="Удаление недоступно"></button>',
          '</div>',
        '</div>',
      ].join('');

      const sprite = row.querySelector('.nursery-sprite img');
      setSprite(sprite, poke);

      row.querySelector('.nursery-pokemon-name').textContent =
        '#' + pad3(dex) + ' ' + displayName(poke);

      row.querySelector('.nursery-nature').textContent =
        nurseryNature(poke) || 'нет данных';

      const gender = nurseryGender(poke);

      row.querySelector('.nursery-gender').textContent =
        gender || 'пол: нет данных';

      row.querySelector('.nursery-level').textContent =
        nurseryNumber(poke, 'level') === null
          ? 'нет данных'
          : poke.level + ' ур.';

      row.querySelector('.nursery-take').innerHTML =
        nurseryIcons.user;

      row.querySelector('.nursery-take').disabled =
        activePokemon().length >= 6;

      if (activePokemon().length >= 6) {
        row.querySelector('.nursery-take').title =
          'В команде уже 6 покемонов';
      }

      row.querySelector('.nursery-trash').innerHTML =
        nurseryIcons.trash;

      const stats = poke.stats && typeof poke.stats === 'object'
        ? poke.stats
        : {};

      const ev = poke.ev && typeof poke.ev === 'object'
        ? poke.ev
        : {};

      const statKeys = [
        ['AT', 'atk'],
        ['DF', 'def'],
        ['SA', 'satk'],
        ['SD', 'sdef'],
        ['SP', 'speed'],
      ];

      const maxStat = Math.max(
        1,
        ...statKeys.map(([, key]) => Number(stats[key]) || 0)
      );

      const statGrid = row.querySelector('.nursery-stat-grid');

      statKeys.forEach(([label, key]) => {
        const actual = nurseryNumber(stats, key);
        const effort = nurseryNumber(ev, key);

        const cell = document.createElement('span');
        cell.className = 'nursery-stat';

        cell.title =
          label
          + ': EV | характеристика; полоса относительно максимальной характеристики этого покемона';

        cell.innerHTML = [
          '<span class="nursery-stat-line">',
            '<b></b>',
            '<span></span>',
          '</span>',
          '<span class="nursery-stat-track"><i></i></span>',
        ].join('');

        cell.querySelector('b').textContent = label;

        cell.querySelector('.nursery-stat-line span').textContent =
          (effort === null ? '—' : effort)
          + ' | '
          + (actual === null ? '—' : actual);

        cell.querySelector('i').style.width =
          actual === null
            ? '0%'
            : nurseryBar(actual / maxStat * 100) + '%';

        statGrid.appendChild(cell);
      });

      return row;
    }views/game-pokemon.php — заменить selectedPokemon(), чтобы открывались покемоны из питомникаPHP    function selectedPokemon() {
      const active = activePokemon();
      return team.find(poke => Number(poke.id) === Number(selectedId)) || active[0] || null;
    }views/game-pokemon.php — заменить сброс состояния и повторное открытие панелиPHP    function resetLocalState() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      nurseryOpen = false;
      activeLearn = null;
      renderAll();
    }

    async function reopenPanel() {
      closePopups();
      selectedId = 0;
      detailOpen = false;
      nurseryOpen = false;
      activeLearn = null;
      await load(false);
    }public/css/game-pokemon.css — добавить в конец файла стили списка, фильтров, полос характеристик и адаптивной раскладкиCSS/* Питомник: отдельный компактный список.
   Стили плиток .poke-tile не меняются. */

.nursery-entry {
  width: 100%;
  min-height: 42px;
  padding: var(--space-2) var(--space-3);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-lg);
  background: var(--surface-panel);
  color: var(--accent-text);
  font-weight: var(--fw-bold);
  text-align: left;
}

.nursery-entry:hover,
.nursery-entry:focus-visible {
  border-color: var(--focus-color);
  background: var(--surface-1);
  outline: none;
  box-shadow: var(--focus-ring);
}

.team-list.is-overview.is-nursery {
  overflow-x: hidden;
}

.nursery-view {
  min-width: 0;
}

.nursery-heading {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  margin: 0 0 var(--space-2);
}

.nursery-heading strong {
  font-size: var(--fs-500);
}

.nursery-heading small {
  margin-left: auto;
  color: var(--text-2);
}

.nursery-back {
  display: inline-flex;
  align-items: center;
  gap: var(--space-1);
  min-height: 32px;
  padding: 0 var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-0);
  color: var(--accent-text);
}

.nursery-back svg {
  width: 16px;
  height: 16px;
}

.nursery-shell {
  display: grid;
  grid-template-columns: 202px minmax(0, 1fr);
  align-items: start;
  gap: var(--space-3);
  min-width: 0;
}

.nursery-filters {
  position: sticky;
  top: 0;
  min-width: 0;
  max-height: calc(100vh - 122px);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-lg);
  background: var(--surface-panel);
}

.nursery-filter-scroll {
  display: grid;
  align-content: start;
  gap: var(--space-2);
  min-height: 0;
  overflow-y: auto;
  padding-right: var(--space-1);
}

.nursery-select {
  display: grid;
  min-width: 0;
  gap: var(--space-1);
  color: var(--text-1);
  font-size: var(--fs-200);
}

.nursery-select > span {
  font-weight: var(--fw-bold);
}

.nursery-select select,
.nursery-level input {
  width: 100%;
  min-width: 0;
  height: 30px;
  padding: 0 var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-0);
  color: var(--text-1);
  font: inherit;
}

.nursery-select select:disabled {
  color: var(--text-3);
  cursor: not-allowed;
}

.nursery-select select:focus-visible,
.nursery-level input:focus-visible,
.nursery-search input:focus-visible {
  outline: none;
  border-color: var(--focus-color);
  box-shadow: var(--focus-ring);
}

.nursery-level {
  min-width: 0;
  margin: 0;
  padding: 0;
  border: none;
}

.nursery-level legend {
  margin-bottom: var(--space-1);
  padding: 0;
  font-size: var(--fs-200);
  font-weight: var(--fw-bold);
}

.nursery-level-inputs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-1);
}

.nursery-level input {
  padding: 0 var(--space-1);
  font-size: var(--fs-100);
}

.nursery-level input::-webkit-inner-spin-button {
  margin: 0;
}

.nursery-capacity {
  flex: 0 0 auto;
  display: grid;
  gap: var(--space-1);
  border-top: 1px solid var(--border-1);
  padding: var(--space-2) var(--space-1) 0;
  font-size: var(--fs-200);
}

.nursery-capacity strong {
  text-align: center;
}

.nursery-capacity-info {
  display: grid;
  grid-template-columns: 1fr 30px;
  align-items: center;
  gap: var(--space-2);
}

.nursery-capacity-number {
  padding: var(--space-1);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-0);
  text-align: center;
  font-variant-numeric: tabular-nums;
}

.nursery-capacity-icon {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-0);
  color: var(--accent-text);
}

.nursery-capacity-icon svg {
  width: 20px;
  height: 20px;
}

.nursery-results {
  min-width: 0;
}

.nursery-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  margin-bottom: var(--space-2);
}

.nursery-search {
  display: flex;
  min-width: 0;
  flex: 1 1 220px;
  max-width: 540px;
  align-items: center;
  gap: var(--space-2);
  height: 36px;
  padding: 0 var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-0);
}

.nursery-search:focus-within {
  border-color: var(--focus-color);
  box-shadow: var(--focus-ring);
}

.nursery-search-icon {
  display: grid;
  place-items: center;
  color: var(--accent-text);
}

.nursery-search-icon svg {
  width: 19px;
  height: 19px;
}

.nursery-search input {
  width: 100%;
  min-width: 0;
  height: 32px;
  padding: 0;
  border: 0;
  outline: none;
  background: transparent;
  color: var(--text-1);
  font: inherit;
}

.nursery-search input:focus-visible {
  box-shadow: none;
}

.nursery-search input::placeholder {
  color: var(--text-2);
  opacity: 1;
}

.nursery-toolbar-buttons {
  display: flex;
  flex-wrap: wrap;
  flex: 0 0 auto;
  align-items: center;
  gap: var(--space-1);
}

.nursery-round,
.nursery-take,
.nursery-trash {
  display: grid;
  flex: 0 0 auto;
  place-items: center;
  border: 1px solid var(--border-1);
  border-radius: var(--radius-pill);
  background: var(--surface-0);
  color: var(--accent-text);
}

.nursery-round {
  width: 34px;
  height: 34px;
}

.nursery-round[aria-pressed="true"] {
  border-color: var(--focus-color);
  background: var(--accent-soft);
}

.nursery-round:disabled,
.nursery-take:disabled,
.nursery-trash:disabled {
  opacity: .45;
  cursor: not-allowed;
}

.nursery-found {
  min-height: 20px;
  margin-bottom: var(--space-1);
  color: var(--text-2);
  font-size: var(--fs-100);
}

.nursery-rows {
  display: grid;
  gap: var(--space-2);
  min-width: 0;
}

.nursery-group-heading {
  margin: var(--space-2) 0 0;
  padding: var(--space-1) var(--space-2);
  border-bottom: 1px solid var(--border-1);
  font-size: var(--fs-200);
  color: var(--accent-text);
}

.nursery-row {
  min-width: 0;
  display: grid;
  grid-template-columns:
    64px
    minmax(126px, .9fr)
    minmax(235px, 1.85fr)
    minmax(125px, .8fr);
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-lg);
  background: var(--surface-0);
  box-shadow: var(--shadow-1);
}

.nursery-sprite {
  width: 62px;
  height: 62px;
  padding: var(--space-1);
  display: grid;
  place-items: center;
  border: 1px solid var(--border-1);
  border-radius: var(--radius-lg);
  background: var(--surface-1);
}

.nursery-sprite img {
  display: block;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.nursery-vitals {
  min-width: 0;
  display: grid;
  gap: 3px;
}

.nursery-meter {
  min-width: 0;
}

.nursery-meter-text {
  display: grid;
  grid-template-columns: 20px minmax(0, 1fr) auto;
  gap: 3px;
  align-items: center;
  color: var(--text-2);
  font-size: var(--fs-100);
  font-variant-numeric: tabular-nums;
}

.nursery-meter-text b {
  color: var(--text-1);
}

.nursery-meter-text span {
  overflow-wrap: anywhere;
}

.nursery-meter-text small {
  font-size: inherit;
  white-space: nowrap;
}

.nursery-track,
.nursery-experience,
.nursery-stat-track {
  display: block;
  overflow: hidden;
  border: 1px solid var(--border-1);
  border-radius: var(--radius-pill);
  background: var(--border-soft);
}

.nursery-track {
  height: 8px;
  margin-top: 2px;
}

.nursery-track i,
.nursery-experience i,
.nursery-stat-track i {
  display: block;
  height: 100%;
  border-radius: inherit;
}

.nursery-meter-hp .nursery-track i {
  background: var(--success);
}

.nursery-meter-sp .nursery-track i {
  background: var(--accent-strong);
}

.nursery-experience {
  height: 4px;
}

.nursery-experience i {
  background: var(--warning);
}

.nursery-stats {
  min-width: 0;
  display: grid;
  gap: var(--space-2);
}

.nursery-pokemon-name {
  width: 100%;
  min-width: 0;
  min-height: 23px;
  padding: 0 var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-pill);
  background: var(--surface-1);
  color: var(--text-1);
  font-size: var(--fs-200);
  font-weight: var(--fw-bold);
  text-align: center;
  overflow-wrap: anywhere;
}

.nursery-stat-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 4px;
  min-width: 0;
}

.nursery-stat {
  min-width: 0;
  display: grid;
  gap: 3px;
  padding: 3px var(--space-1);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-md);
  background: var(--surface-1);
  font-size: var(--fs-100);
}

.nursery-stat-line {
  display: flex;
  min-width: 0;
  gap: 3px;
  justify-content: space-between;
  align-items: center;
  font-variant-numeric: tabular-nums;
}

.nursery-stat-line b {
  color: var(--accent-text);
}

.nursery-stat-line span {
  color: var(--text-1);
  white-space: nowrap;
}

.nursery-stat-track {
  height: 4px;
}

.nursery-stat-track i {
  background: var(--success);
}

.nursery-stat:nth-child(2n) .nursery-stat-track i {
  background: var(--warning);
}

.nursery-meta {
  min-width: 0;
  display: grid;
  gap: var(--space-2);
}

.nursery-nature {
  min-width: 0;
  padding: var(--space-1) var(--space-2);
  border: 1px solid var(--border-1);
  border-radius: var(--radius-pill);
  background: var(--surface-1);
  color: var(--text-1);
  text-align: center;
  font-size: var(--fs-200);
  overflow-wrap: anywhere;
}

.nursery-meta-bottom {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: var(--space-1);
  font-size: var(--fs-100);
}

.nursery-gender {
  color: var(--text-2);
}

.nursery-level {
  white-space: nowrap;
  color: var(--text-1);
}

.nursery-take,
.nursery-trash {
  width: 27px;
  height: 27px;
}

.nursery-round svg,
.nursery-take svg,
.nursery-trash svg,
.nursery-back svg,
.nursery-search-icon svg,
.nursery-capacity-icon svg {
  display: block;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.nursery-round svg,
.nursery-take svg,
.nursery-trash svg {
  width: 18px;
  height: 18px;
}

.nursery-back:hover,
.nursery-round:not(:disabled):hover,
.nursery-take:not(:disabled):hover,
.nursery-pokemon-name:hover,
.nursery-sprite:hover {
  border-color: var(--focus-color);
  background: var(--accent-soft);
}

.nursery-view button:focus-visible {
  outline: none;
  border-color: var(--focus-color);
  box-shadow: var(--focus-ring);
}

.nursery-empty {
  margin: 0;
  padding: var(--space-5) var(--space-3);
  border: 1px dashed var(--border-1);
  border-radius: var(--radius-lg);
  background: var(--surface-panel);
  color: var(--text-2);
  text-align: center;
}

@media (max-width: 1070px) {
  .nursery-row {
    grid-template-columns:
      60px
      minmax(125px, .8fr)
      minmax(225px, 1.5fr);
  }

  .nursery-meta {
    grid-column: 2 / -1;
    grid-row: 2;
    grid-template-columns: minmax(110px, 1fr) auto;
    align-items: center;
  }

  .nursery-meta-bottom {
    justify-content: flex-end;
  }
}

@media (max-width: 760px) {
  .nursery-shell {
    grid-template-columns: minmax(0, 1fr);
  }

  .nursery-filters {
    position: static;
    max-height: none;
  }

  .nursery-filter-scroll {
    max-height: 240px;
  }

  .nursery-row {
    grid-template-columns: 62px minmax(0, 1fr);
  }

  .nursery-sprite {
    grid-row: 1;
  }

  .nursery-vitals {
    grid-column: 2;
  }

  .nursery-stats {
    grid-column: 1 / -1;
    grid-row: 2;
  }

  .nursery-meta {
    grid-column: 1 / -1;
    grid-row: 3;
  }
}

@media (max-width: 430px) {
  .nursery-toolbar {
    flex-wrap: wrap;
  }

  .nursery-search {
    flex-basis: 100%;
  }

  .nursery-toolbar-buttons {
    width: 100%;
    justify-content: flex-end;
  }

  .nursery-meta {
    grid-template-columns: minmax(0, 1fr);
  }

  .nursery-meta-bottom {
    justify-content: flex-start;
  }
}

/*
Список: renderNurseryView() → renderNurseryRows() → renderNurseryRow().
Источник: существующий team из GET /api/pokemon/moves; питомник — active === false.
Подтверждены в странице: id, name, baseNum, level, hp, hpMax, stats, ev, happiness.
Характер, пол, SP, опыт, типы и остальные свойства фильтров в исходном JS не подтверждены; проверяются только при наличии в объекте.
lookupNatures в архиве отсутствует: ID 14+ требуют сверки с AdminRepository.php.
Для отсутствующих полей — «нет данных», без фиктивных значений.
POST /api/pokemon/nursery: существующее действие take; удаления нет, корзина отключена.
Карандаш отключён; звезда работает только при наличии поля избранного.
Вместимость 2000 взята из ТЗ; активные плитки и renderTile() не менялись.
Проверено: PHP/JS/CSS синтаксис, поиск, уровень, сброс фильтров, открытие карточки и адаптивная раскладка.
*/