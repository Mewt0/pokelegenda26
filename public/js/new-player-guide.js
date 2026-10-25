/*
 * Guided new-player onboarding for the /game shell.
 *
 * Read-only client feature: it consumes the existing /api/quests journal, the existing
 * pokemon:quest-guide highlighting event and the rendered game DOM. It never starts
 * quests, chooses starters, toggles PvE, attacks, claims rewards or boards transport.
 */
(() => {
  'use strict';

  const world = document.querySelector('.world[data-user-id]');
  const card = document.getElementById('newPlayerGuide');
  if (!world || !card) return;

  const TOTAL_STEPS = 7;
  const TICKET_ITEM_ID = 90111;
  const POLL_MS = 30000;
  const USER_ID = Number(world.dataset.userId || 0);
  const STORAGE_KEY = USER_ID > 0 ? `pokemon.newPlayerGuide.v1.user.${USER_ID}` : '';
  const byId = (id) => document.getElementById(id);
  const el = {
    kicker: byId('newPlayerGuideKicker'),
    place: byId('newPlayerGuidePlace'),
    title: byId('newPlayerGuideTitle'),
    text: byId('newPlayerGuideText'),
    path: byId('newPlayerGuidePath'),
    rewards: byId('newPlayerGuideRewards'),
    rewardList: byId('newPlayerGuideRewardList'),
    ticks: byId('newPlayerGuideTicks'),
    primary: byId('newPlayerGuidePrimary'),
    journal: byId('newPlayerGuideJournal'),
    hide: byId('newPlayerGuideHide'),
    status: byId('newPlayerGuideStatus'),
    restore: byId('newPlayerGuideRestore'),
    inlineRestore: byId('newPlayerGuideInlineRestore'),
  };
  if (!el.title || !el.primary || !el.text) return;

  const announcer = document.createElement('div');
  announcer.className = 'visually-hidden';
  announcer.setAttribute('aria-live', 'polite');
  announcer.setAttribute('aria-atomic', 'true');
  document.body.appendChild(announcer);

  let prefs = readPrefs();
  let finished = !!prefs.completed;
  let hiddenByUser = !!prefs.hidden;
  let completionVisible = false;
  let stage = null;
  let lastStageKey = String(prefs.stage || '');
  let lastAnnouncedKey = '';
  let model = null;
  let loading = false;
  let loadQueued = false;
  let loadTimer = 0;
  let pollTimer = 0;
  let announceTimer = 0;
  let spotlightTimer = 0;
  let ticketSpotlightTimer = 0;
  let renderTimer = 0;
  let journalRestore = null;
  let liveStageObserved = false;
  let lastLocationId = Number(world.dataset.locationId || 0);
  let lastPveOn = null;
  let lastBattleTitle = '';
  let lastBattleActive = false;
  let battleHint = null;

  function readPrefs() {
    if (!STORAGE_KEY) return {};
    try {
      const raw = window.localStorage.getItem(STORAGE_KEY);
      const parsed = raw ? JSON.parse(raw) : null;
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  }

  function writePrefs(patch) {
    prefs = { ...prefs, ...patch };
    if (!STORAGE_KEY) return;
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    } catch (error) {
      // Private mode / storage quota: keep the state in memory for this session.
    }
  }

  function normalize(value) {
    return String(value || '').trim().toLowerCase().replace(/ё/g, 'е');
  }

  function includesAny(value, needles) {
    const haystack = normalize(value);
    return needles.some((needle) => haystack.includes(normalize(needle)));
  }

  function locationState() {
    const live = window.PokemonGameState && typeof window.PokemonGameState === 'object'
      ? window.PokemonGameState
      : null;
    const id = Number(world.dataset.locationId || (live && live.locationId) || 0);
    const title = String((byId('locationTitle') && byId('locationTitle').textContent) || '').trim();
    return { id, title };
  }

  function moveButtons() {
    return [...document.querySelectorAll('.move-btn[data-location-id]')];
  }

  function findMoveById(id) {
    const wanted = Number(id || 0);
    if (wanted <= 0) return null;
    return moveButtons().find((button) => Number(button.dataset.locationId || 0) === wanted) || null;
  }

  function findMoveByTitle(needles) {
    return moveButtons().find((button) => includesAny(button.dataset.locationTitle || button.textContent, needles)) || null;
  }

  function findNpcByTitle(needles) {
    const buttons = [...document.querySelectorAll('.npc-btn[data-npc-title]')];
    return buttons.find((button) => includesAny(button.dataset.npcTitle || button.textContent, needles)) || null;
  }

  function isBattleActive() {
    const overlay = byId('battleOverlay');
    const finish = byId('battleFinishBox');
    if (!overlay) return false;
    const open = overlay.classList.contains('is-open') || overlay.getAttribute('aria-hidden') === 'false';
    return !!open && !!finish && finish.hidden;
  }

  function isPveOn() {
    const button = byId('pveButton');
    const label = button && button.querySelector('span:last-child')
      ? String(button.querySelector('span:last-child').textContent || '')
      : '';
    return /вкл/i.test(label) && !/выкл/i.test(label);
  }

  function findTicketSlot() {
    const slots = [...document.querySelectorAll('#invGrid .inv-slot')];
    return slots.find((slot) => {
      const label = slot.querySelector('.item-id');
      return String((label && label.textContent) || '').trim() === `#${TICKET_ITEM_ID}`;
    }) || null;
  }

  function inventoryVisible() {
    const overlay = byId('inventoryOverlay');
    return !!overlay && overlay.classList.contains('is-open');
  }

  function buildClientNavigation(options) {
    const here = locationState();
    const requestedId = Number(options.targetLocationId || 0);
    const matchedMove = findMoveById(requestedId) || findMoveByTitle(options.locationNeedles || []);
    const targetLocationId = matchedMove ? Number(matchedMove.dataset.locationId || 0) : requestedId;
    const targetLocationTitle = String(
      options.targetTitle
      || (matchedMove && matchedMove.dataset.locationTitle)
      || (here.id === targetLocationId && here.title)
      || ''
    );
    const npc = findNpcByTitle(options.npcNeedles || []);
    const targetNpcTitle = String(
      (npc && npc.dataset.npcTitle)
      || options.npcTitle
      || ''
    );
    const atTarget = here.id > 0 && here.id === targetLocationId;
    return {
      kind: options.kind || 'npc',
      label: targetNpcTitle || targetLocationTitle || options.label || '',
      hint: options.hint || '',
      currentLocationId: here.id,
      targetLocationId,
      targetLocationTitle,
      targetNpcTitle,
      targetControl: options.targetControl || '',
      onTarget: atTarget,
      nextLocationId: atTarget ? 0 : targetLocationId,
      nextLocationTitle: targetLocationTitle,
      path: [],
    };
  }

  function rewardEntries(quest) {
    if (!quest) return [];
    if (Array.isArray(quest.reward_view) && quest.reward_view.length > 0) {
      return quest.reward_view
        .map((reward) => ({
          type: String(reward.type || 'item'),
          label: String(reward.label || 'Награда'),
          amount: Number(reward.amount || 0),
          icon: String(reward.icon || ''),
        }))
        .filter((reward) => reward.amount > 0);
    }

    const raw = quest.reward && typeof quest.reward === 'object' ? quest.reward : {};
    const entries = [];
    if (raw.items && typeof raw.items === 'object') {
      Object.entries(raw.items).forEach(([itemId, amount]) => {
        const value = Number(amount || 0);
        if (value > 0) {
          entries.push({
            type: 'item',
            label: `Предмет #${Number(itemId || 0)}`,
            amount: value,
            icon: `/public/img/items/${Number(itemId || 0)}.png`,
          });
        }
      });
    }
    if (Number(raw.rank || 0) > 0) {
      entries.push({
        type: 'rank',
        label: 'Рейтинг квестов',
        amount: Number(raw.rank),
        icon: '/public/img/ui/menu-profile.png',
      });
    }
    if (Number(raw.exp || 0) > 0) {
      entries.push({
        type: 'exp',
        label: 'Опыт',
        amount: Number(raw.exp),
        icon: '/public/img/ui/menu-pokemon.png',
      });
    }
    return entries;
  }

  function pathText(navigation) {
    const path = navigation && Array.isArray(navigation.path) ? navigation.path : [];
    const titles = path.map((point) => String((point && point.title) || '').trim()).filter(Boolean);
    if (titles.length > 1) return titles.join(' → ');
    if (navigation && navigation.nextLocationTitle && navigation.targetLocationTitle
      && normalize(navigation.nextLocationTitle) !== normalize(navigation.targetLocationTitle)) {
      return `${navigation.nextLocationTitle} → ${navigation.targetLocationTitle}`;
    }
    if (navigation && navigation.targetLocationTitle) return navigation.targetLocationTitle;
    return '';
  }

  function serverNavigation(quest) {
    if (!quest) return null;
    if (quest.current_step && quest.current_step.navigation) return quest.current_step.navigation;
    return quest.navigation || null;
  }

  function oakView(quest) {
    const here = locationState();
    const labMove = findMoveByTitle(['лаборатор']);
    const targetId = labMove ? Number(labMove.dataset.locationId || 0) : 3;
    const targetTitle = String((labMove && labMove.dataset.locationTitle) || 'Лаборатория профессора Оука');
    const oak = findNpcByTitle(['оук']);
    const oakTitle = String((oak && oak.dataset.npcTitle) || 'Профессор Оук');
    const atLab = (here.id > 0 && here.id === targetId) || includesAny(here.title, ['лаборатор']);
    const text = atLab
      ? `Вы в лаборатории. Нажмите на NPC «${oakTitle}» и выберите «Я помогу».`
      : (labMove
        ? `Внизу нажмите «${targetTitle}» — это вход в лабораторию. Внутри нажмите на «${oakTitle}» и выберите «Я помогу».`
        : `Доберитесь до Алабастии, затем войдите в лабораторию. Внутри нажмите на «${oakTitle}» и выберите «Я помогу».`);
    return {
      key: 'q1_intro',
      kind: 'active',
      step: 1,
      title: 'Ваш первый покемон',
      text,
      path: atLab ? `Цель: ${oakTitle}` : `Цель: ${targetTitle}`,
      place: here.title,
      rewards: [],
      quest,
      primary: {
        type: 'nav',
        label: atLab ? 'Показать Оука' : 'Показать лабораторию',
        navigation: buildClientNavigation({
          targetLocationId: targetId,
          targetTitle,
          npcNeedles: ['оук'],
          npcTitle: 'Профессор Оук',
          kind: 'npc',
          hint: 'Профессор Оук ждёт в лаборатории.',
        }),
      },
    };
  }

  function passerbyView(quest) {
    const here = locationState();
    const alabastia = findMoveByTitle(['алабаст']);
    const targetId = alabastia ? Number(alabastia.dataset.locationId || 0) : 1;
    const targetTitle = String((alabastia && alabastia.dataset.locationTitle) || 'Алабастия');
    const passerby = findNpcByTitle(['прохож']);
    const npcTitle = String((passerby && passerby.dataset.npcTitle) || 'Случайный прохожий');
    const atTarget = (here.id > 0 && here.id === targetId) || includesAny(here.title, ['алабаст']);
    const text = atTarget
      ? `Вы в Алабастии. Нажмите на NPC «${npcTitle}» и выберите «Спасибо за информацию».`
      : `Вернитесь в Алабастию: внизу нажмите «${targetTitle}». Затем нажмите на NPC «${npcTitle}» и выберите «Спасибо за информацию».`;
    return {
      key: 'q1_passerby',
      kind: 'active',
      step: 2,
      title: 'Спросите прохожего',
      text,
      path: atTarget ? `Цель: ${npcTitle}` : `Цель: ${targetTitle}`,
      place: here.title,
      rewards: [],
      quest,
      primary: {
        type: 'nav',
        label: atTarget ? 'Показать прохожего' : 'Показать Алабастию',
        navigation: buildClientNavigation({
          targetLocationId: targetId,
          targetTitle,
          npcNeedles: ['прохож'],
          npcTitle: 'Случайный прохожий',
          kind: 'npc',
          hint: 'Случайный прохожий расскажет, куда убежали покемоны.',
        }),
      },
    };
  }

  function starterView(quest) {
    const here = locationState();
    const labMove = findMoveByTitle(['лаборатор']);
    const targetId = labMove ? Number(labMove.dataset.locationId || 0) : 3;
    const targetTitle = String((labMove && labMove.dataset.locationTitle) || 'Лаборатория профессора Оука');
    const oak = findNpcByTitle(['оук']);
    const oakTitle = String((oak && oak.dataset.npcTitle) || 'Профессор Оук');
    const atLab = (here.id > 0 && here.id === targetId) || includesAny(here.title, ['лаборатор']);
    const text = atLab
      ? `Вы в лаборатории. Нажмите на «${oakTitle}» и выберите одного из трёх покемонов. Стартовый покемон выбирается один раз — поменять его позже не получится.`
      : `Вернитесь в лабораторию и поговорите с Оуком. Он предложит трёх покемонов. Стартовый покемон выбирается один раз — поменять его позже не получится.`;
    return {
      key: 'q1_starter',
      kind: 'active',
      step: 3,
      title: 'Выберите стартового покемона',
      text,
      path: atLab ? 'Цель: выбрать покемона' : `Цель: ${targetTitle}`,
      place: here.title,
      rewards: [],
      quest,
      primary: {
        type: 'nav',
        label: atLab ? 'Показать Оука' : 'Показать лабораторию',
        navigation: buildClientNavigation({
          targetLocationId: targetId,
          targetTitle,
          npcNeedles: ['оук'],
          npcTitle: 'Профессор Оук',
          kind: 'npc',
          hint: 'Профессор Оук даст выбрать стартового покемона.',
        }),
      },
    };
  }

  function finishView(quest) {
    const here = locationState();
    const labMove = findMoveByTitle(['лаборатор']);
    const targetId = labMove ? Number(labMove.dataset.locationId || 0) : 3;
    const targetTitle = String((labMove && labMove.dataset.locationTitle) || 'Лаборатория профессора Оука');
    const oak = findNpcByTitle(['оук']);
    const oakTitle = String((oak && oak.dataset.npcTitle) || 'Профессор Оук');
    const atLab = (here.id > 0 && here.id === targetId) || includesAny(here.title, ['лаборатор']);
    return {
      key: 'q1_finish',
      kind: 'active',
      step: 4,
      title: 'Заберите стартера и припасы',
      text: atLab
        ? `Нажмите на «${oakTitle}» и выберите «Получить стартера и припасы». Покемон 5 уровня сразу появится в команде, а награды будут зачислены.`
        : `Вернитесь в лабораторию и поговорите с Оуком. Нажмите «Получить стартера и припасы» — покемон 5 уровня появится в команде, а награды будут зачислены.`,
      path: atLab ? 'Цель: получить стартера' : `Цель: ${targetTitle}`,
      place: here.title,
      rewards: rewardEntries(quest),
      quest,
      primary: {
        type: 'nav',
        label: atLab ? 'Показать Оука' : 'Показать лабораторию',
        navigation: buildClientNavigation({
          targetLocationId: targetId,
          targetTitle,
          npcNeedles: ['оук'],
          npcTitle: 'Профессор Оук',
          kind: 'npc',
          hint: 'У Оука ждёт кнопка «Получить стартера и припасы».',
        }),
      },
    };
  }

  function q101View(quest) {
    const here = locationState();
    const started = !!quest && quest.state !== null && quest.state !== undefined;
    const battle = isBattleActive();
    const onRoad = (here.id > 0 && here.id === 4) || includesAny(here.title, ['дорога 1']);
    const pveOn = isPveOn();
    const roadMove = findMoveByTitle(['дорог']);
    const roadTitle = String((roadMove && roadMove.dataset.locationTitle) || 'Дорога 1');
    const navigation = serverNavigation(quest) || buildClientNavigation({
      targetLocationId: 4,
      targetTitle: roadTitle,
      kind: 'route',
      hint: 'Выйдите на Дорогу 1 и включите нападение.',
    });
    const route = pathText(navigation) || roadTitle;

    const title = 'Первый бой на Дороге 1';
    let text;
    let primary;

    if (!started) {
      text = `Стартовая история уже пройдена. В журнале доступен квест «${(quest && quest.title) || 'Первый бой на Дороге 1'}» — примите его, если хотите продолжить с подсказками, или играйте свободно.`;
      primary = { type: 'journal', label: 'Открыть журнал' };
    } else if (battle) {
      text = 'Бой открыт. Выберите приём в блоке «Атаки» — это ваши атаки. Следите за HP и PP: когда PP заканчивается, приём может стать недоступен. Бой не идёт сам.';
      primary = { type: 'battle', label: 'Показать кнопки боя' };
    } else if (onRoad) {
      text = pveOn
        ? 'Нападение включено. Оставайтесь на Дороге 1: дикий покемон появится сам. Когда откроется бой, выберите атаку.'
        : 'Вы на Дороге 1. Нажмите «Нападение: выкл» внизу — включится поиск диких покемонов. Оставайтесь здесь и ждите встречи.';
      primary = { type: 'pve', label: pveOn ? 'Проверить «Нападение»' : 'Показать «Нападение»' };
    } else {
      text = `Выйдите на Дорогу 1: внизу нажмите «${roadTitle}», затем включите «Нападение» и дождитесь дикого покемона. Когда откроется бой, выберите атаку.`;
      primary = { type: 'nav', label: 'Показать Дорогу 1' };
    }

    return {
      key: 'q101',
      kind: 'active',
      step: 5,
      title,
      text,
      path: started ? `Маршрут: ${route}` : '',
      place: here.title,
      rewards: started ? rewardEntries(quest) : [],
      quest,
      primary: { ...primary, navigation },
    };
  }

  function q102View(quest) {
    const here = locationState();
    const started = !!quest && quest.state !== null && quest.state !== undefined;
    const navigation = serverNavigation(quest);
    const route = pathText(navigation);
    const nextTitle = String((navigation && navigation.nextLocationTitle) || 'Дорога 1');
    const destination = String((navigation && navigation.targetLocationTitle) || 'Вертания');
    const atTarget = Number(here.id || 0) === Number((navigation && navigation.targetLocationId) || 16);
    const joy = findNpcByTitle(['покецентр', 'джой']);
    const joyTitle = String((joy && joy.dataset.npcTitle) || 'Покецентр');
    const routeLine = route || `${nextTitle} → ${destination}`;
    const text = started
      ? `Идите по маршруту: ${routeLine}. В Вертании зайдите в «${joyTitle}» и восстановите HP. После этого откроется первая поездка.`
      : `Квест «${(quest && quest.title) || 'Путь в Вертанию'}» уже доступен в журнале. Примите его, затем идите по маршруту: ${routeLine}. В Вертании загляните в Покецентр к Джой.`;

    let primary;
    if (!started) {
      primary = { type: 'journal', label: 'Открыть журнал' };
    } else if (atTarget && joy) {
      primary = {
        type: 'nav',
        label: 'Показать Покецентр',
        navigation: buildClientNavigation({
          targetLocationId: here.id,
          targetTitle: here.title,
          npcNeedles: ['покецентр', 'джой'],
          npcTitle: joyTitle,
          kind: 'npc',
          hint: 'Восстановите HP в Покецентре.',
        }),
      };
    } else if (navigation) {
      primary = { type: 'nav', label: 'Показать следующий переход', navigation };
    } else {
      primary = { type: 'journal', label: 'Открыть журнал' };
    }

    return {
      key: 'q102',
      kind: 'active',
      step: 6,
      title: 'Дорога в Вертанию',
      text,
      path: started ? `Маршрут: ${routeLine}` : '',
      place: here.title,
      rewards: started ? rewardEntries(quest) : [],
      quest,
      primary,
    };
  }

  function q103View(quest) {
    const here = locationState();
    const started = !!quest && quest.state !== null && quest.state !== undefined;
    const ticket = findTicketSlot();
    const inventoryOpen = inventoryVisible();
    const onBoard = Number(here.id || 0) === 95001 || includesAny(here.title, ['самолет', 'самолёт']);
    const planeId = Number(here.id || 0) || 95001;
    let text;
    if (!started) {
      text = `Квест «${(quest && quest.title) || 'Первый транспорт'}» уже доступен в журнале. Примите его, затем откройте «Инвентарь» и используйте билет на самолёт №90111.`;
    } else if (onBoard) {
      text = 'Вы на борту самолёта. Дождитесь прибытия: пока самолёт летит, выйти нельзя. Когда прибудете, нажмите на NPC «Выход» — тогда билет спишется.';
    } else if (inventoryOpen && ticket) {
      text = 'Билет на самолёт найден. Нажмите на него и выберите «Применить» внизу. В панели выберите рейс и ещё раз подтвердите «Применить» — вы сядете на самолёт. Квест засчитается сразу после посадки.';
    } else if (inventoryOpen) {
      text = 'Инвентарь открыт. Найдите билет на самолёт (предмет №90111): откройте категорию «Расходники» или введите название в поиск. Затем нажмите на билет и выберите «Применить» внизу.';
    } else {
      text = 'Откройте «Инвентарь» внизу и найдите билет на самолёт (предмет №90111). Нажмите на него и выберите «Применить» внизу, затем выберите рейс и подтвердите «Применить». Квест засчитается сразу после посадки.';
    }

    let primary;
    if (!started) {
      primary = { type: 'journal', label: 'Открыть журнал' };
    } else if (onBoard) {
      primary = {
        type: 'nav',
        label: 'Показать «Выход»',
        navigation: buildClientNavigation({
          targetLocationId: planeId,
          targetTitle: here.title || 'Самолёт',
          npcNeedles: ['выход'],
          npcTitle: 'Выход',
          kind: 'npc',
          hint: 'Выход из самолёта откроется после прибытия.',
        }),
      };
    } else if (inventoryOpen && ticket) {
      primary = { type: 'ticket', label: 'Показать билет' };
    } else if (inventoryOpen) {
      primary = { type: 'inventory', label: 'Найти билет' };
    } else {
      primary = { type: 'inventory', label: 'Открыть инвентарь' };
    }

    return {
      key: 'q103',
      kind: 'active',
      step: 7,
      title: 'Первый перелёт',
      text,
      path: 'Билет спишется только при выходе после прилёта. Пока самолёт летит, выход недоступен: дождитесь прибытия и нажмите «Выход».',
      place: here.title,
      rewards: started ? rewardEntries(quest) : [],
      quest,
      primary,
    };
  }

  function completeView(quest) {
    const here = locationState();
    const plane = Number(here.id || 0) === 95001 || includesAny(here.title, ['самолет', 'самолёт']);
    const text = plane
      ? 'Посадка прошла, квест засчитан. Награда уже зачислена. Когда самолёт прилетит, нажмите «Выход» — тогда билет спишется. После выхода можно играть свободно.'
      : 'Первый транспортный квест засчитан, награда уже зачислена. Дальше можно играть свободно: исследовать карту, ловить покемонов и брать новые квесты в журнале.';
    return {
      key: 'complete',
      kind: 'complete',
      step: TOTAL_STEPS,
      title: 'Готово: первый транспорт',
      text,
      path: plane ? 'Выход откроется после прибытия.' : '',
      place: here.title,
      rewards: rewardEntries(quest),
      quest,
      primary: { type: 'dismiss', label: 'Продолжить без подсказок' },
    };
  }

  function errorView() {
    return {
      key: 'error',
      kind: 'error',
      step: 1,
      title: 'Не удалось загрузить подсказки',
      text: 'Проверьте соединение и попробуйте снова. Это не значит, что квесты выполнены: подсказка не изменит их состояние.',
      path: '',
      place: locationState().title,
      rewards: [],
      primary: { type: 'retry', label: 'Повторить' },
    };
  }

  function loadingView() {
    return {
      key: 'loading',
      kind: 'loading',
      step: 1,
      title: 'Загружаю подсказки',
      text: 'Проверяю прогресс квестов. Это займёт пару секунд.',
      path: '',
      place: locationState().title,
      rewards: [],
      primary: { type: 'noop', label: 'Загрузка...', disabled: true },
    };
  }

  function computeStage(data) {
    const quests = Array.isArray(data && data.quests) ? data.quests : [];
    const questById = new Map(quests.map((quest) => [Number(quest.id || 0), quest]));
    const q1 = questById.get(1) || null;
    const q101 = questById.get(101) || null;
    const q102 = questById.get(102) || null;
    const q103 = questById.get(103) || null;
    const started = (quest) => !!quest && quest.state !== null && quest.state !== undefined;

    if (q103 && q103.completed) return { kind: 'complete', quest: q103 };
    if (!q1 || !started(q1)) return { kind: 'active', quest: q1, view: oakView(q1) };
    if (!q1.completed) {
      const process = Number(q1.process || 0);
      if (process <= 2) return { kind: 'active', quest: q1, view: passerbyView(q1) };
      if (process === 3) return { kind: 'active', quest: q1, view: starterView(q1) };
      return { kind: 'active', quest: q1, view: finishView(q1) };
    }
    if (!q101 || !started(q101) || !q101.completed) {
      return { kind: 'active', quest: q101, view: q101View(q101) };
    }
    if (!q102 || !started(q102) || !q102.completed) {
      return { kind: 'active', quest: q102, view: q102View(q102) };
    }
    if (!q103 || !started(q103) || !q103.completed) {
      return { kind: 'active', quest: q103, view: q103View(q103) };
    }
    return { kind: 'complete', quest: q103 };
  }

  function renderTicks(step, done) {
    if (!el.ticks) return;
    if (el.ticks.children.length !== TOTAL_STEPS) {
      el.ticks.textContent = '';
      for (let index = 1; index <= TOTAL_STEPS; index += 1) {
        const tick = document.createElement('span');
        tick.className = 'new-player-guide__tick';
        tick.dataset.step = String(index);
        el.ticks.appendChild(tick);
      }
    }
    [...el.ticks.children].forEach((tick, index) => {
      const number = index + 1;
      tick.classList.toggle('is-done', done || number < step);
      tick.classList.toggle('is-current', !done && number === step);
    });
  }

  function renderRewards(entries) {
    if (!el.rewards || !el.rewardList) return;
    el.rewardList.textContent = '';
    if (!Array.isArray(entries) || entries.length === 0) {
      el.rewards.hidden = true;
      return;
    }
    el.rewards.hidden = false;
    entries.forEach((entry) => {
      const item = document.createElement('li');
      item.className = 'new-player-guide__reward';
      const icon = document.createElement('img');
      icon.src = entry.icon || '/public/img/ui/menu-quests.png';
      icon.alt = '';
      icon.width = 22;
      icon.height = 22;
      icon.loading = 'lazy';
      icon.addEventListener('error', () => {
        icon.src = '/public/img/ui/menu-quests.png';
      }, { once: true });
      const label = document.createElement('span');
      label.className = 'new-player-guide__reward-label';
      label.textContent = entry.label;
      const amount = document.createElement('b');
      amount.className = 'new-player-guide__reward-amount';
      amount.textContent = entry.type === 'rank'
        ? `+${Number(entry.amount || 0).toLocaleString('ru-RU')}`
        : `× ${Number(entry.amount || 0).toLocaleString('ru-RU')}`;
      item.append(icon, label, amount);
      el.rewardList.appendChild(item);
    });
  }

  function announce(message) {
    if (!message) return;
    window.clearTimeout(announceTimer);
    announcer.textContent = '';
    announceTimer = window.setTimeout(() => {
      announcer.textContent = message;
    }, 40);
  }

  function setStatus(message, shouldAnnounce = false) {
    if (el.status) el.status.textContent = message || '';
    if (shouldAnnounce) announce(message);
  }

  function renderView(view, options = {}) {
    if (!view) return;
    stage = view;
    const done = view.kind === 'complete';
    const step = Math.max(1, Math.min(TOTAL_STEPS, Number(view.step || 1)));

    if (el.kicker) el.kicker.textContent = done ? `Все ${TOTAL_STEPS} шагов` : `Шаг ${step} из ${TOTAL_STEPS}`;
    if (el.place) {
      el.place.textContent = view.place || '';
      el.place.hidden = !view.place;
    }
    el.title.textContent = view.title;
    el.text.textContent = view.text;
    if (el.path) {
      el.path.textContent = view.path || '';
      el.path.hidden = !view.path;
    }
    renderRewards(view.rewards);
    renderTicks(step, done);

    el.primary.textContent = (view.primary && view.primary.label) || 'Далее';
    el.primary.disabled = !!(view.primary && view.primary.disabled);
    el.primary.hidden = false;
    if (el.journal) el.journal.hidden = !!(view.primary && view.primary.type === 'journal');
    if (el.hide) el.hide.hidden = done;
    el.status.textContent = '';
    card.classList.toggle('is-complete', done);
    card.setAttribute('aria-busy', view.kind === 'loading' ? 'true' : 'false');
    card.hidden = false;

    if (!options.noAnnounce && lastAnnouncedKey !== view.key) {
      lastAnnouncedKey = view.key;
      const announcement = done
        ? `${view.title}. ${view.text}`
        : `Шаг ${step} из ${TOTAL_STEPS}. ${view.title}. ${view.text}`;
      announce(announcement);
    }
    updateRestoreVisibility();
    updateBattleHint();
  }

  function focusElement(element, preventScroll = true) {
    if (!element || typeof element.focus !== 'function') return false;
    try {
      element.focus({ preventScroll });
    } catch (error) {
      try { element.focus(); } catch (ignored) { return false; }
    }
    return true;
  }

  function focusCard() {
    if (!el.title) return;
    focusElement(el.title, true);
    const rect = card.getBoundingClientRect();
    if (rect.top < 0 || rect.bottom > window.innerHeight) {
      card.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }
  }

  function focusMenuFallback() {
    const link = document.querySelector('.actionbar .main-menu a');
    focusElement(link, true);
  }

  function spotlight(element, options = {}) {
    if (!element) return false;
    element.classList.add('new-player-guide-spotlight');
    if (typeof element.scrollIntoView === 'function') {
      element.scrollIntoView({ block: 'center', inline: 'nearest' });
    }
    if (options.focus) focusElement(options.focusTarget || element, true);
    window.clearTimeout(spotlightTimer);
    spotlightTimer = window.setTimeout(() => {
      element.classList.remove('new-player-guide-spotlight');
    }, 7000);
    return true;
  }

  function createBattleHint() {
    const battleWindow = document.querySelector('.battle-dock-window') || document.querySelector('.battle-window');
    if (!battleWindow) return null;
    const hint = document.createElement('div');
    hint.className = 'new-player-guide-battle-hint';
    hint.setAttribute('role', 'note');
    hint.hidden = true;

    const copy = document.createElement('span');
    copy.className = 'new-player-guide-battle-hint__copy';
    const lead = document.createElement('b');
    lead.textContent = 'Первый бой.';
    copy.append(lead, document.createTextNode(' Выберите приём в блоке «Атаки». HP — на карточках покемонов, PP — на кнопках приёмов.'));

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'ui-btn new-player-guide-battle-hint__btn';
    button.textContent = 'Показать атаки';
    button.addEventListener('click', showBattleActions);

    hint.append(copy, button);
    const head = battleWindow.querySelector('.battle-dock-head') || battleWindow.querySelector('header');
    if (head && head.parentNode === battleWindow) head.insertAdjacentElement('afterend', hint);
    else battleWindow.prepend(hint);
    return hint;
  }

  function isWildBattle() {
    const title = String((byId('battleTitle') && byId('battleTitle').textContent) || '').toLowerCase();
    return title.includes('дик') || title.includes('wild') || title.includes('pve');
  }

  function showBattleActions() {
    const attacksTab = document.querySelector('[data-battle-tab="attacks"]');
    if (attacksTab && !attacksTab.classList.contains('is-active')) attacksTab.click();
    const box = document.querySelector('.battle-dock-moves')
      || document.querySelector('.battle-actions-col')
      || document.querySelector('.battle-dock-window');
    const firstMove = box ? box.querySelector('.battle-move-btn:not(:disabled)') : null;
    if (box) spotlight(box, { focus: !!firstMove, focusTarget: firstMove });
    setStatus('Блок атак подсвечен. Выберите приём сами и следите за HP и PP.', true);
  }

  function updateBattleHint() {
    if (!battleHint) return;
    const shouldShow = !!stage
      && stage.key === 'q101'
      && !hiddenByUser
      && !finished
      && !completionVisible
      && isBattleActive()
      && isWildBattle();
    battleHint.hidden = !shouldShow;
  }

  function dispatchGuide(navigation, quest) {
    if (!navigation) {
      setStatus('Маршрут появится, когда игра загрузит переходы. Откройте журнал и нажмите «Показать на карте».', true);
      return;
    }
    const step = quest && quest.current_step ? quest.current_step : null;
    window.dispatchEvent(new CustomEvent('pokemon:quest-guide', {
      detail: {
        action: 'activate',
        mode: 'map',
        quest: {
          id: Number((quest && quest.id) || 1),
          title: String((quest && quest.title) || 'Первый покемон'),
        },
        step,
        navigation,
      },
    }));
    setStatus('Маршрут подсвечен на карте. Идите по подсказке на экране.', true);
  }

  function openJournal() {
    const trigger = document.querySelector('[data-open-quests]');
    if (trigger) {
      trigger.click();
      setStatus('Журнал открыт. Найдите активный квест в списке.', true);
      return;
    }
    setStatus('Откройте журнал кнопкой «Квесты» внизу.', true);
  }

  function waitFor(predicate, timeout, interval) {
    return new Promise((resolve) => {
      const started = Date.now();
      const step = () => {
        const value = predicate();
        if (value) {
          resolve(value);
          return;
        }
        if (Date.now() - started >= timeout) {
          resolve(null);
          return;
        }
        window.setTimeout(step, interval);
      };
      step();
    });
  }

  function highlightTicket(slot) {
    if (!slot) return;
    slot.classList.add('new-player-guide-ticket');
    spotlight(slot, { focus: true });
    window.clearTimeout(ticketSpotlightTimer);
    ticketSpotlightTimer = window.setTimeout(() => {
      slot.classList.remove('new-player-guide-ticket');
    }, 8000);
  }

  async function openInventoryAndSpotTicket() {
    const existing = findTicketSlot();
    if (inventoryVisible() && existing) {
      highlightTicket(existing);
      if (stage && stage.key === 'q103') renderView(stage, { noAnnounce: true });
      setStatus('Билет найден. Нажмите на него, затем «Применить» внизу.', true);
      return;
    }
    if (!inventoryVisible()) {
      const link = byId('inventoryLink');
      if (link) link.click();
    }
    setStatus('Ищу билет №90111 в инвентаре...');
    const slot = await waitFor(findTicketSlot, 2600, 160);
    if (slot) {
      highlightTicket(slot);
      if (stage && stage.key === 'q103') renderView(stage, { noAnnounce: true });
      setStatus('Билет найден. Нажмите на него, затем «Применить» внизу.', true);
      return;
    }
    const category = [...document.querySelectorAll('#invCategories .inv-category')]
      .find((button) => /расход/i.test(String(button.textContent || '')));
    if (category) {
      spotlight(category, { focus: true });
      if (stage && stage.key === 'q103') renderView(stage, { noAnnounce: true });
      setStatus('Билет №90111 не найден на этой странице. Откройте категорию «Расходники»: билет лежит там. Если его нет совсем, проверьте награду за «Путь в Вертанию» в журнале.', true);
      return;
    }
    setStatus('Билет №90111 не найден на этой странице. Проверьте категорию «Расходники» или поиск по названию. Если билета нет совсем, проверьте награду за «Путь в Вертанию» в журнале.', true);
  }

  function hideGuide() {
    if (!stage) return;
    hiddenByUser = true;
    writePrefs({ hidden: true, stage: stage.key, seenAt: Date.now() });
    card.hidden = true;
    updateRestoreVisibility();
    updateBattleHint();
    setStatus('');
    announce('Подсказки новичка скрыты. Вернуть их можно кнопкой «Подсказки новичка» в нижней панели или в журнале.');
    if (el.inlineRestore && !el.inlineRestore.hidden) focusElement(el.inlineRestore, true);
    else if (el.restore && !el.restore.hidden) focusElement(el.restore, true);
    else focusMenuFallback();
  }

  function dismissCompleted() {
    completionVisible = false;
    hiddenByUser = true;
    finished = true;
    writePrefs({ completed: true, hidden: true, stage: 'complete', seenAt: Date.now() });
    card.hidden = true;
    stage = null;
    updateRestoreVisibility();
    updateBattleHint();
    announce('Онбординг завершён. Подсказки больше не появятся.');
    focusMenuFallback();
  }

  function restoreGuide() {
    if (finished) return;
    hiddenByUser = false;
    writePrefs({ hidden: false });
    if (stage && stage.kind !== 'loading' && stage.kind !== 'error') {
      liveStageObserved = true;
      renderView(stage, { noAnnounce: true });
      updateRestoreVisibility();
      focusCard();
      announce('Подсказки новичка включены.');
      return;
    }
    if (model) {
      applyModel(model);
      updateRestoreVisibility();
      focusCard();
      announce('Подсказки новичка включены.');
      return;
    }
    renderView(loadingView(), { noAnnounce: true });
    updateRestoreVisibility();
    focusCard();
    loadJournal({ userInitiated: true });
  }

  function updateRestoreVisibility() {
    const canRestore = !finished && !completionVisible && hiddenByUser;
    if (el.restore) el.restore.hidden = !canRestore;
    if (el.inlineRestore) el.inlineRestore.hidden = !canRestore;
    if (journalRestore) journalRestore.hidden = finished || !stage;
  }

  function applyModel(data) {
    model = data;
    const result = computeStage(data);

    if (result.kind === 'complete') {
      const wasOnboarding = liveStageObserved && !finished && !hiddenByUser;
      const quest = result.quest;
      if (!finished) {
        finished = true;
        writePrefs({ completed: true, hidden: hiddenByUser, stage: 'complete', seenAt: Date.now() });
      }
      if (wasOnboarding) {
        completionVisible = true;
        stage = completeView(quest);
        renderView(stage);
        return;
      }
      completionVisible = false;
      stage = null;
      card.hidden = true;
      updateRestoreVisibility();
      return;
    }

    const view = result.view;
    if (!view) return;
    if (lastStageKey !== view.key) {
      lastStageKey = view.key;
      writePrefs({ stage: view.key, seenAt: Date.now() });
    }
    stage = view;
    if (hiddenByUser) {
      card.hidden = true;
      updateRestoreVisibility();
      return;
    }
    liveStageObserved = true;
    renderView(view);
  }

  function runPrimary() {
    if (!stage || !stage.primary) return;
    const action = stage.primary;
    switch (action.type) {
      case 'nav':
        dispatchGuide(action.navigation || serverNavigation(stage.quest), stage.quest);
        break;
      case 'journal':
        openJournal();
        break;
      case 'pve': {
        const button = byId('pveButton');
        if (button) {
          const alreadyOn = isPveOn();
          spotlight(button, { focus: true });
          setStatus(alreadyOn
            ? 'Нападение уже включено. Ждите встречи на Дороге 1.'
            : 'Кнопка «Нападение» подсвечена. Нажмите её сами, чтобы включить поиск покемонов.', true);
        }
        break;
      }
      case 'battle':
        showBattleActions();
        break;
      case 'inventory':
        openInventoryAndSpotTicket();
        break;
      case 'ticket': {
        const slot = findTicketSlot();
        if (slot) {
          highlightTicket(slot);
          setStatus('Билет найден. Нажмите на него, затем «Применить» внизу.', true);
        } else {
          openInventoryAndSpotTicket();
        }
        break;
      }
      case 'retry':
        renderView(loadingView(), { noAnnounce: true });
        loadJournal({ userInitiated: true });
        break;
      case 'dismiss':
        dismissCompleted();
        break;
      default:
        break;
    }
  }

  async function loadJournal(options = {}) {
    if (finished) return;
    if (loading) {
      loadQueued = true;
      return;
    }
    loading = true;
    try {
      const response = await fetch('/api/quests', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        cache: 'no-store',
      });
      if (!response.ok) throw new Error(`http_${response.status}`);
      const data = await response.json();
      if (!data || data.ok !== true || !Array.isArray(data.quests)) throw new Error('payload');
      applyModel(data);
    } catch (error) {
      if (!model) {
        if (options.userInitiated) {
          renderView(errorView(), { noAnnounce: false });
        }
      } else if (stage && !hiddenByUser && !completionVisible && stage.kind !== 'complete') {
        setStatus('Не удалось обновить прогресс. Показываю последнюю известную подсказку.', false);
      }
    } finally {
      loading = false;
      if (loadQueued) {
        loadQueued = false;
        scheduleLoad(400);
      }
    }
  }

  function scheduleLoad(delay) {
    window.clearTimeout(loadTimer);
    loadTimer = window.setTimeout(() => {
      if (!document.hidden) loadJournal();
    }, Math.max(0, Number(delay || 0)));
  }

  function scheduleRender() {
    window.clearTimeout(renderTimer);
    renderTimer = window.setTimeout(() => {
      if (stage && !hiddenByUser && !finished) renderView(stage, { noAnnounce: true });
    }, 220);
  }

  function mountJournalRestore() {
    const side = document.querySelector('.quest-side-actions');
    if (!side) return;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'ui-btn new-player-guide-journal-restore';
    button.dataset.newPlayerGuideRestore = '1';
    button.textContent = 'Подсказки новичка';
    button.hidden = true;
    button.addEventListener('click', () => {
      const overlay = button.closest('[data-quest-overlay]');
      const close = overlay ? overlay.querySelector('[data-quest-close]') : null;
      if (close) close.click();
      restoreGuide();
    });
    side.appendChild(button);
    journalRestore = button;
  }

  function watchState() {
    const battleOverlay = byId('battleOverlay');
    const battleFinish = byId('battleFinishBox');
    const battleTitle = byId('battleTitle');
    lastBattleActive = isBattleActive();
    const battleObserver = new MutationObserver(() => {
      const current = isBattleActive();
      if (current === lastBattleActive) return;
      lastBattleActive = current;
      scheduleRender();
      updateBattleHint();
      if (!current && battleFinish && !battleFinish.hidden) scheduleLoad(2200);
    });
    if (battleOverlay) {
      battleObserver.observe(battleOverlay, { attributes: true, attributeFilter: ['class', 'aria-hidden'] });
    }
    if (battleFinish) {
      battleObserver.observe(battleFinish, { attributes: true, attributeFilter: ['hidden'] });
    }
    if (battleTitle) {
      lastBattleTitle = String(battleTitle.textContent || '');
      new MutationObserver(() => {
        const current = String(battleTitle.textContent || '');
        if (current === lastBattleTitle) return;
        lastBattleTitle = current;
        updateBattleHint();
        scheduleRender();
      }).observe(battleTitle, { childList: true, subtree: true, characterData: true });
    }

    const pveButton = byId('pveButton');
    if (pveButton) {
      lastPveOn = isPveOn();
      new MutationObserver(() => {
        const current = isPveOn();
        if (current === lastPveOn) return;
        lastPveOn = current;
        scheduleRender();
      }).observe(pveButton, {
        childList: true,
        subtree: true,
        characterData: true,
      });
    }

    const inventory = byId('inventoryOverlay');
    if (inventory) {
      new MutationObserver(() => {
        if (stage && stage.key === 'q103') scheduleRender();
      }).observe(inventory, { attributes: true, attributeFilter: ['class'] });
    }
  }

  el.primary.addEventListener('click', runPrimary);
  el.journal?.addEventListener('click', openJournal);
  el.hide?.addEventListener('click', hideGuide);
  el.restore?.addEventListener('click', restoreGuide);
  el.inlineRestore?.addEventListener('click', restoreGuide);

  window.addEventListener('pokemon:location-changed', (event) => {
    const detail = event && event.detail ? event.detail : {};
    const nextId = Number(detail.locationId || world.dataset.locationId || 0);
    if (nextId > 0 && nextId !== lastLocationId) {
      lastLocationId = nextId;
      scheduleLoad(650);
      return;
    }
    // The shell re-dispatches this event on every 5 s state render. Same location is
    // deliberately a no-op: PvE and battle controls have their own observers below.
  });
  window.addEventListener('focus', () => scheduleLoad(1200));
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) scheduleLoad(300);
  });
  document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;
    if (target.closest('#npcPanel button, [data-quest-start], #battleDoneBtn, #battleEscapeBtn')) {
      scheduleLoad(1500);
    }
    if (target.closest('#inventoryLink')) scheduleRender();
    if (target.closest('#pveButton')) scheduleRender();
  }, true);

  mountJournalRestore();
  battleHint = createBattleHint();
  watchState();
  updateRestoreVisibility();
  updateBattleHint();

  if (finished) {
    if (el.restore) el.restore.hidden = true;
    return;
  }

  pollTimer = window.setInterval(() => {
    if (!document.hidden && !finished && !completionVisible) loadJournal();
  }, POLL_MS);
  window.addEventListener('pagehide', () => window.clearInterval(pollTimer), { once: true });

  // Wait for the first game-state render so location/NPC/move buttons are real.
  window.setTimeout(() => loadJournal(), 250);
})();
