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
  <title>Pokemon 8.0 - Игровой мир</title>
  <link rel="stylesheet" href="/public/css/game-start.css?v=20260526-pokemon-drag3">
  <link rel="stylesheet" href="/public/css/game-shell.css?v=20260527-bug-reporter-2">
  <link rel="stylesheet" href="/public/css/game-battle-dock.css?v=20260527-battle-replay">
  <link rel="stylesheet" href="/public/css/player-menu.css">
  <link rel="stylesheet" href="/public/css/trainer-profile-window.css?v=20260527-social-polish4">
  <link rel="stylesheet" href="/public/css/game-market-overlay.css">
  <link rel="stylesheet" href="/public/css/commission-market.css?v=20260527-my-lots">
</head>
<body>
  <main class="world game-shell" data-csrf="<?= View::e($csrf) ?>" data-user-id="<?= (int) ($userId ?? 0) ?>">
    <section class="location location-card glass-card" id="location">
      <div class="location-image-wrap">
        <img class="location-image" id="locationImage" alt="">
      </div>
      <div class="location-info">
        <div class="location-title-row">
          <div class="location-pin" aria-hidden="true"></div>
          <h1 class="location-title" id="locationTitle">Загрузка...</h1>
        </div>
        <div class="decor-line"><span class="decor-dot"></span></div>
        <div class="location-text location-description" id="locationText"></div>
        <div class="npc-strip npc-actions" id="npcs"></div>
        <div class="npc-strip boss-actions" id="bosses"></div>
        <div class="npc-panel" id="npcPanel" aria-live="polite"></div>
      </div>
    </section>

    <section class="travel-card glass-card" aria-label="Переходы">
      <div class="travel-line"></div>
      <div class="moves travel-buttons" id="moves"></div>
      <div class="travel-line"></div>
    </section>

    <section class="main-grid">
      <section class="chat chat-panel glass-card">
        <div class="chat-log chat-messages" id="chatLog"></div>
        <form class="chat-form chat-input-bar" id="chatForm">
          <input value="<?= View::e($login) ?>" readonly class="chat-my-login">
          <input id="chatInput" class="chat-input" placeholder="Сообщение..." autocomplete="off">
          <button type="submit" class="send-btn" aria-label="Отправить"><img src="/public/img/ui/chat/send.png" alt=""></button>
        </form>
      </section>

      <aside class="users players-panel glass-card">
        <h2 class="players-title" id="usersTitle">Игроки</h2>
        <div class="players-list" id="usersList"></div>
      </aside>
    </section>

    <nav class="actionbar bottom-toolbar glass-card">
      <div class="quick-controls">
        <button type="button" id="pveButton" class="status-pill danger">Нападение: выкл</button>
        <?php if (!empty($isAdmin)): ?>
          <button type="button" id="debugForceBattleBtn" class="status-pill debug" style="display:none">DEBUG: бой (Дорога 1)</button>
        <?php endif; ?>
        <button type="button" id="bugReportBtn" class="status-pill bug-report">Report bug</button>
        <button type="button" class="status-pill mode">Режим: общий</button>
      </div>
      <div class="main-menu">
        <a href="/game/pokemon" id="pokemonLink"><img src="/public/img/ui/menu-pokemon.png" alt="">Покемоны</a>
        <a href="/game/items" id="inventoryLink"><img src="/public/img/ui/menu-inventory.png" alt="">Инвентарь</a>
        <a href="/game/market/items" data-open-market><img src="/public/img/ui/menu-market.png" alt="">Покемаркет</a>
        <a href="/game/commission" class="commission-menu-entry" data-open-commission>
          <img src="/public/img/ui/menu-market.png" alt="">
          <span>Лавка</span>
          <b class="commission-menu-badge" data-commission-badge>NEW</b>
        </a>
        <a href="/game/profile"><img src="/public/img/ui/menu-profile.png" alt="">Профиль</a>
        <a href="#" data-open-dex="pokemon"><img src="/public/img/ui/menu-pokedex.png" alt="">Покедекс</a>
        <a href="#" data-open-dex="attacks"><img src="/public/img/ui/menu-attackdex.png" alt="">Атакадекс</a>
        <a href="/game/quests"><img src="/public/img/ui/menu-quests.png" alt="">Квесты</a>
        <a href="/game/battle/pvp"><img src="/public/img/ui/menu-battle.png" alt="">Бои</a>
        <a href="/game/messages"><img src="/public/img/ui/menu-mail.png" alt="">Почта</a>
      </div>
      <div class="system-status">
        <?php if (!empty($isAdmin)): ?>
          <a href="/game/admin" class="admin-entry" title="Админпанель" aria-label="Админпанель">Админ</a>
        <?php endif; ?>
        <button type="button" class="settings-btn" aria-label="Настройки">⚙</button>
        <span class="status" id="status">Готово</span>
        <span class="signal" aria-hidden="true">▂▄▆</span>
      </div>
    </nav>
  </main>
  <section class="inventory-overlay" id="inventoryOverlay" aria-hidden="true">
    <div class="inventory-window" role="dialog" aria-label="Инвентарь">
      <header class="inv-top">
        <span class="ico">☰</span><span class="ico">⚙</span><span class="ico">◈</span><span class="ico">★</span>
        <div class="inv-search">
          <input id="invSearchInput" placeholder="Начните вводить название">
        </div>
      </header>
      <div class="inv-categories" id="invCategories" aria-label="Категории инвентаря"></div>
      <div class="inv-grid-wrap">
        <div class="inv-grid" id="invGrid"></div>
      </div>
      <div class="inv-target-panel" id="invTargetPanel" hidden>
        <div>
          <strong id="invTargetTitle">Применить предмет</strong>
          <p id="invTargetHint">Выберите покемона и количество.</p>
        </div>
        <select id="invTargetPokemon"></select>
        <input id="invTargetCount" type="number" min="1" value="1" aria-label="Количество">
        <button type="button" id="invTargetApplyBtn">Применить</button>
        <button type="button" id="invTargetCancelBtn">×</button>
      </div>
      <footer class="inv-bottom">
        <button type="button" id="invRefreshBtn">⟳</button>
        <button type="button" id="invPrevBtn">≪</button>
        <button type="button" id="invNextBtn">≫</button>
        <span id="invPageInfo">1/1</span>
        <button type="button" id="invUseTargetBtn" disabled>Применить</button>
        <span class="inv-slots">СЛОТОВ ЗАНЯТО: <span id="invSlotsCount">0</span></span>
        <button type="button" class="inv-close" id="invCloseBtn">Закрыть</button>
      </footer>
    </div>
  </section>
  <section class="pokemon-overlay" id="pokemonOverlay" aria-hidden="true">
    <div class="pokemon-window" role="dialog" aria-label="Покемоны">
      <div class="pokemon-drag-handle" id="pokemonDragHandle" aria-hidden="true" title="Перетащить окно"></div>
      <header class="pokemon-modal-head">
        <strong>Покемоны</strong>
        <button type="button" id="pokemonCloseBtn">×</button>
      </header>
      <iframe id="pokemonFrame" title="Покемоны" src="about:blank"></iframe>
    </div>
  </section>
  <section class="market-overlay" id="marketOverlay" aria-hidden="true">
    <div class="market-window shop-window" role="dialog" aria-label="Покемаркет">
      <header class="shop-titlebar">
        <button type="button" class="shop-close" data-market-close aria-label="Закрыть">×</button>
        <h1>Список товаров</h1>
      </header>

      <div class="shop-tabs">
        <button class="is-active" type="button" data-market-tab="catalog">Магазин</button>
        <button type="button" data-market-tab="lots">Лоты игроков</button>
        <label>
          <span>Поиск</span>
          <input type="search" data-market-search placeholder="Название или ID">
        </label>
        <div class="shop-wallet">
          <span><b data-wallet-coins>0</b> монет</span>
          <span><b data-wallet-diamonds>0</b> алмазов</span>
        </div>
      </div>

      <div class="shop-layout">
        <section class="shop-catalog" data-market-panel="catalog">
          <div class="shop-grid" data-shop-grid="catalog"></div>
          <p class="shop-empty" data-market-empty="catalog" hidden>В магазине пока нет активных товаров.</p>
        </section>

        <section class="shop-catalog" data-market-panel="lots" hidden>
          <div class="shop-grid" data-shop-grid="lots"></div>
          <p class="shop-empty" data-market-empty="lots" hidden>В твоём регионе нет активных лотов игроков.</p>
        </section>

        <aside class="shop-cart" aria-live="polite">
          <h2>Покупки</h2>
          <div class="cart-empty" data-cart-empty>Выбери товар на полке.</div>
          <div class="cart-card" data-cart-card hidden>
            <div class="cart-art"><img data-cart-icon alt=""></div>
            <div class="cart-info">
              <h3 data-cart-name></h3>
              <p data-cart-description></p>
              <span data-cart-owned></span>
            </div>
            <label class="cart-count">
              <span>Количество</span>
              <input type="number" min="1" value="1" data-cart-count>
            </label>
            <div class="cart-total">
              <span>Итого</span>
              <b data-cart-total>0</b>
            </div>
          </div>
        </aside>
      </div>

      <footer class="shop-footer">
        <button type="button" data-shop-prev>Назад</button>
        <div class="shop-status" data-market-status></div>
        <button type="button" data-shop-next>Далее</button>
        <button type="button" class="buy-button" data-shop-buy disabled>Купить</button>
      </footer>
    </div>
  </section>
  <?php
    $commissionMode = 'overlay';
    $commissionRootId = 'commissionOverlay';
    require APP_ROOT . '/views/components/commission-market-panel.php';
  ?>
  <section class="battle-overlay battle-dock-overlay" id="battleOverlay" aria-hidden="true">
    <div class="battle-window battle-dock-window" role="dialog" aria-label="PvE бой">
      <header class="battle-head battle-dock-head">
        <span id="battleTitle">Дикий бой</span>
        <strong id="battleRound">Раунд 1</strong>
        <span class="battle-turn-label" id="battleTurnLabel">Ваш ход</span>
        <button type="button" id="battleReviewClose" class="battle-review-close" aria-label="Закрыть просмотр боя">&times;</button>
      </header>
      <div class="battle-layout battle-dock-layout">
        <aside class="battle-left battle-dock-side battle-dock-left">
          <div class="battle-dock-side-icons">
            <span class="battle-dock-effect is-down">-1</span>
            <span class="battle-dock-weather">≋ <span id="battleWeatherTurns">0</span></span>
          </div>
          <article class="fighter fighter-player battle-dock-card">
            <div class="battle-dock-card-top">
              <img class="battle-dock-ball" src="/public/img/ui/chatgpt-pokeball.png" alt="">
              <span class="battle-dock-level" id="battlePlayerLevel">1</span>
              <div class="sprite sprite-player" id="battlePlayerSprite" aria-hidden="true"><img id="battlePlayerSpriteImg" alt=""></div>
              <span class="battle-dock-gender" id="battlePlayerGender">♂</span>
              <div class="battle-status-badges" id="battlePlayerStatuses"></div>
            </div>
            <h3 id="battlePlayerName">Ваш покемон</h3>
            <div class="battle-held-item" id="battlePlayerHeld" hidden><img alt=""><span></span></div>
            <div class="hpbar"><div class="hpfill" id="battlePlayerHpBar" style="width:100%"></div></div>
            <div class="battle-dock-energy"><i id="battlePlayerEnergyBar" style="width:42%"></i></div>
            <div id="battlePlayerHp" class="muted">HP 0/0</div>
          </article>
          <div class="battle-actions-col battle-dock-moves">
            <button type="button" id="battleMove1" class="battle-move-btn">Атака 1</button>
            <button type="button" id="battleMove2" class="battle-move-btn">Атака 2</button>
            <button type="button" id="battleMove3" class="battle-move-btn">Атака 3</button>
            <button type="button" id="battleMove4" class="battle-move-btn">Атака 4</button>
          </div>
        </aside>

        <main class="battle-center battle-dock-log-panel">
          <div class="battle-dock-weather-title" id="battleWeatherLabel">Поле боя</div>
          <div class="battle-log" id="battleLog"></div>
          <div class="battle-finish" id="battleFinishBox" hidden>
            <button type="button" id="battleDoneBtn">Завершить бой</button>
          </div>
        </main>

        <aside class="battle-right battle-dock-side battle-dock-right">
          <div class="battle-dock-side-icons is-right">
            <span class="battle-dock-effect is-up">+2</span>
            <span class="battle-dock-effect is-down">-1</span>
          </div>
          <article class="fighter fighter-enemy battle-dock-card">
            <div class="battle-dock-card-top">
              <img class="battle-dock-ball" src="/public/img/ui/chatgpt-pokeball.png" alt="">
              <span class="battle-dock-level" id="battleEnemyLevel">1</span>
              <div class="sprite sprite-enemy" id="battleEnemySprite" aria-hidden="true"><img id="battleEnemySpriteImg" alt=""></div>
              <span class="battle-dock-gender" id="battleEnemyGender">♂</span>
              <div class="battle-status-badges" id="battleEnemyStatuses"></div>
            </div>
            <h3 id="battleEnemyName">Дикий покемон</h3>
            <div class="battle-held-item" id="battleEnemyHeld" hidden><img alt=""><span></span></div>
            <div class="hpbar"><div class="hpfill" id="battleEnemyHpBar" style="width:100%"></div></div>
            <div class="battle-dock-energy"><i id="battleEnemyEnergyBar" style="width:26%"></i></div>
            <div id="battleEnemyHp" class="muted">HP 0/0</div>
          </article>
          <div class="battle-dock-catch-info">
            <b id="battleEnemyKind">Дикий покемон</b>
            <span id="battleEnemyCatchText">Можно поймать</span>
            <em id="battleEnemyRarity">Частый</em>
          </div>
          <div class="battle-dock-actions">
            <button type="button" class="battle-tab is-active" data-battle-tab="attacks" title="Атаки"><span>⚔</span><b>Fight</b></button>
            <button type="button" class="battle-tab" data-battle-tab="switch" title="Покемоны"><span>↻</span><b>Pokémon</b></button>
            <button type="button" class="battle-tab" data-battle-tab="items" title="Предметы"><span>▣</span><b>Items</b></button>
            <button type="button" class="battle-tab" data-battle-tab="balls" title="Покеболы"><span>○</span><b>Balls</b></button>
          </div>
          <button type="button" id="battleEscapeBtn" class="battle-dock-danger battle-run-button" title="Сбежать">⚑ Сбежать</button>
          <div class="battle-dock-panels">
            <div class="battle-tab-panel" data-battle-panel="switch">
              <select id="battleSwitchSelect" class="wide"></select>
              <button type="button" id="battleSwitchBtn">Сменить</button>
              <div class="battle-list" id="battleSwitchList"></div>
            </div>
            <div class="battle-tab-panel" data-battle-panel="items">
              <div class="battle-pocket-toolbar" id="battleItemCategoryTabs"></div>
              <div class="battle-list" id="battleItemsList"></div>
            </div>
            <div class="battle-tab-panel" data-battle-panel="balls">
              <div class="battle-pocket-title">Покеболы</div>
              <div class="battle-list" id="battleBallsList"></div>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="dex-overlay" id="dexOverlay" aria-hidden="true">
    <div class="dex-window" role="dialog" aria-label="Декс">
      <header class="dex-head">
        <nav>
          <button type="button" class="is-active" data-dex-tab="pokemon">Покедекс</button>
          <button type="button" data-dex-tab="attacks">Атакадекс</button>
        </nav>
        <div class="dex-search-panel">
          <input id="dexSearchInput" placeholder="Поиск по названию или ID">
          <div class="dex-filter-row" id="dexPokemonFilters">
            <select id="dexPokemonTypeFilter" aria-label="Тип покемона">
              <option value="">Все типы</option>
              <option value="Normal">Normal</option>
              <option value="Fire">Fire</option>
              <option value="Water">Water</option>
              <option value="Grass">Grass</option>
              <option value="Electric">Electric</option>
              <option value="Ice">Ice</option>
              <option value="Fighting">Fighting</option>
              <option value="Poison">Poison</option>
              <option value="Ground">Ground</option>
              <option value="Flying">Flying</option>
              <option value="Psychic">Psychic</option>
              <option value="Bug">Bug</option>
              <option value="Rock">Rock</option>
              <option value="Ghost">Ghost</option>
              <option value="Dragon">Dragon</option>
              <option value="Dark">Dark</option>
              <option value="Steel">Steel</option>
              <option value="Fairy">Fairy</option>
            </select>
            <select id="dexPokemonGenerationFilter" aria-label="Поколение">
              <option value="">Все поколения</option>
              <option value="1">I</option>
              <option value="2">II</option>
              <option value="3">III</option>
              <option value="4">IV</option>
              <option value="5">V</option>
              <option value="6">VI</option>
              <option value="7">VII</option>
              <option value="8">VIII</option>
              <option value="9">IX</option>
            </select>
            <select id="dexPokemonFormFilter" aria-label="Форма">
              <option value="">Все формы</option>
              <option value="normal">Обычные</option>
              <option value="mega">Mega</option>
              <option value="primal">Primal</option>
            </select>
          </div>
          <div class="dex-filter-row is-hidden" id="dexAttackFilters">
            <select id="dexAttackTypeFilter" aria-label="Тип атаки">
              <option value="">Все типы</option>
              <option value="Normal">Normal</option>
              <option value="Fire">Fire</option>
              <option value="Water">Water</option>
              <option value="Grass">Grass</option>
              <option value="Electric">Electric</option>
              <option value="Ice">Ice</option>
              <option value="Fighting">Fighting</option>
              <option value="Poison">Poison</option>
              <option value="Ground">Ground</option>
              <option value="Flying">Flying</option>
              <option value="Psychic">Psychic</option>
              <option value="Bug">Bug</option>
              <option value="Rock">Rock</option>
              <option value="Ghost">Ghost</option>
              <option value="Dragon">Dragon</option>
              <option value="Dark">Dark</option>
              <option value="Steel">Steel</option>
              <option value="Fairy">Fairy</option>
            </select>
            <select id="dexAttackCategoryFilter" aria-label="Категория атаки">
              <option value="">Все категории</option>
              <option value="1">Физические</option>
              <option value="2">Специальные</option>
              <option value="3">Статусные</option>
            </select>
            <input id="dexAttackPowerMin" inputmode="numeric" placeholder="Сила от">
            <input id="dexAttackPowerMax" inputmode="numeric" placeholder="до">
            <input id="dexAttackAccuracyMin" inputmode="numeric" placeholder="Точн. от">
            <input id="dexAttackAccuracyMax" inputmode="numeric" placeholder="до">
            <input id="dexAttackPokemonFilter" placeholder="Покемон">
            <label class="dex-check"><input type="checkbox" id="dexAttackTmFilter"> TM</label>
          </div>
        </div>
        <button type="button" id="dexCloseBtn">×</button>
      </header>
      <div class="dex-body">
        <aside class="dex-list" id="dexList"></aside>
        <main class="dex-details" id="dexDetails"></main>
      </div>
    </div>
  </section>
  <section class="bug-report-overlay" id="bugReportOverlay" aria-hidden="true">
    <div class="bug-report-window glass-card" role="dialog" aria-modal="true" aria-label="Report bug">
      <header>
        <div>
          <strong>Report bug</strong>
          <p>Прикрепим state, battle id и последние клиентские логи.</p>
        </div>
        <button type="button" id="bugReportCloseBtn" aria-label="Закрыть">×</button>
      </header>
      <label>
        <span>Коротко что сломалось</span>
        <input id="bugReportTitle" maxlength="190" placeholder="Например: пропали кнопки боя">
      </label>
      <label>
        <span>Тип</span>
        <select id="bugReportSeverity">
          <option value="bug">Баг</option>
          <option value="critical">Критично</option>
          <option value="visual">Визуал</option>
          <option value="balance">Баланс</option>
          <option value="ux">UX</option>
          <option value="other">Другое</option>
        </select>
      </label>
      <label>
        <span>Описание / шаги</span>
        <textarea id="bugReportDescription" rows="5" maxlength="4000" placeholder="Что делал, что ожидал, что произошло"></textarea>
      </label>
      <div class="bug-report-attachments" id="bugReportAttachments"></div>
      <footer>
        <button type="button" id="bugReportSubmitBtn">Отправить</button>
        <button type="button" id="bugReportCancelBtn">Отмена</button>
      </footer>
    </div>
  </section>
  <div class="inv-tooltip" id="invTooltip"></div>
  <div class="battle-poke-tooltip" id="battlePokeTooltip"></div>
  <div class="battle-move-tooltip" id="battleMoveTooltip"></div>

  <script>
    const itemIconIndex = <?= json_encode($itemIconIndex, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}' ?>;
    const app = document.querySelector('.world');
    const csrf = app.dataset.csrf;
    const state = { busy: false, locationId: 0, activeNpc: null, pveButton: false };
    window.state = state;
    window.PokemonGameState = state;
    const inventory = { page: 1, pages: 1, items: [], selected: null, pokemon: [], category: '', query: '', categories: [], flightRoutes: [], flightRoutesLoaded: false, flightRoutesLoading: false };
    const battleState = { active: false, reviewing: false, mode: '', moves: [], knownMoves: [] };
    const battleWindowDrag = { ready: false, dragging: false, offsetX: 0, offsetY: 0 };
    const pokemonWindowDrag = { ready: false, dragging: false, offsetX: 0, offsetY: 0 };
    const battleHoverState = { ready: false, player: null, enemy: null, movePinned: false };
    const battlePocket = { loaded: false, items: [], categories: { items: [], balls: [] }, activeItemCategory: 'all' };
    const bugClientLogs = [];

    function recordBugClientLog(level, message, context = {}) {
      bugClientLogs.push({
        level,
        message: String(message || '').slice(0, 500),
        context,
        time: new Date().toISOString()
      });
      while (bugClientLogs.length > 80) {
        bugClientLogs.shift();
      }
    }

    window.addEventListener('error', event => {
      recordBugClientLog('error', event.message || 'window.error', {
        source: event.filename || '',
        line: event.lineno || 0,
        column: event.colno || 0
      });
    });
    window.addEventListener('unhandledrejection', event => {
      recordBugClientLog('promise', event.reason && (event.reason.message || String(event.reason)) || 'unhandled rejection');
    });

    function setupBattleSideTabs() {
      const left = document.querySelector('.battle-left');
      if (!left || left.dataset.tabsReady === '1') return;
      left.dataset.tabsReady = '1';

      const dockWindow = document.querySelector('.battle-dock-window');
      if (dockWindow) {
        document.querySelectorAll('[data-battle-tab]').forEach(button => {
          button.addEventListener('click', () => setBattleTab(button.dataset.battleTab));
        });
        setBattleTab('attacks');
        return;
      }

      const actions = left.querySelector('.battle-actions-col');
      const subactions = left.querySelector('.battle-subactions');
      const switchSelect = document.getElementById('battleSwitchSelect');
      const switchBtn = document.getElementById('battleSwitchBtn');

      const turn = document.createElement('div');
      turn.className = 'battle-turn-card';
      turn.innerHTML = '<span class="battle-clock" aria-hidden="true">&#9687;</span><span><b>&#1042;&#1072;&#1096; &#1093;&#1086;&#1076;</b><small>&#1042;&#1088;&#1077;&#1084;&#1103; &#1085;&#1077; &#1086;&#1075;&#1088;&#1072;&#1085;&#1080;&#1095;&#1077;&#1085;&#1086;...</small></span>';

      const content = document.createElement('div');
      content.className = 'battle-side-content';
      content.innerHTML = [
        '<div class="battle-tab-panel is-active" data-battle-panel="attacks"></div>',
        '<div class="battle-tab-panel" data-battle-panel="switch"><div class="battle-list" id="battleSwitchList"></div></div>',
        '<div class="battle-tab-panel" data-battle-panel="items"><div class="battle-pocket-toolbar" id="battleItemCategoryTabs"></div><div class="battle-list" id="battleItemsList"></div></div>',
        '<div class="battle-tab-panel" data-battle-panel="balls"><div class="battle-list" id="battleBallsList"></div></div>'
      ].join('');

      const attacksPanel = content.querySelector('[data-battle-panel="attacks"]');
      const switchPanel = content.querySelector('[data-battle-panel="switch"]');
      if (actions) attacksPanel.appendChild(actions);
      if (switchSelect) {
        switchSelect.classList.add('battle-native-switch');
        switchPanel.appendChild(switchSelect);
      }
      if (switchBtn) {
        switchBtn.classList.add('battle-hidden-action');
        switchPanel.appendChild(switchBtn);
      }

      const tabs = document.createElement('nav');
      tabs.className = 'battle-tabs';
      tabs.setAttribute('aria-label', 'Battle actions');
      tabs.innerHTML = [
        '<button type="button" class="battle-tab is-active" data-battle-tab="attacks" title="Attacks"><span>&#9889;</span><b>Fight</b></button>',
        '<button type="button" class="battle-tab" data-battle-tab="switch" title="Switch"><span>&#8644;</span><b>Pokémon</b></button>',
        '<button type="button" class="battle-tab" data-battle-tab="items" title="Inventory"><span>&#9635;</span><b>Items</b></button>',
        '<button type="button" class="battle-tab" data-battle-tab="balls" title="Pokeballs"><span>&#9675;</span><b>Balls</b></button>'
      ].join('');

      left.insertBefore(turn, left.firstChild);
      left.insertBefore(content, subactions || null);
      left.appendChild(tabs);

      const review = document.createElement('div');
      review.className = 'battle-review-actions';
      review.innerHTML = [
        '<div class="battle-review-title"><b>Режим просмотра</b><small>Время не ограничено...</small></div>',
        '<button type="button" id="battleReplayBtn">&#9654; Повтор боя</button>',
        '<button type="button" id="battleCloseReviewBtn">&#10006; Закрыть окно</button>'
      ].join('');
      left.appendChild(review);
      review.querySelector('#battleReplayBtn').addEventListener('click', openBattleReplay);
      review.querySelector('#battleCloseReviewBtn').addEventListener('click', acknowledgeBattleEnd);

      tabs.querySelectorAll('[data-battle-tab]').forEach(button => {
        button.addEventListener('click', () => setBattleTab(button.dataset.battleTab));
      });
      setBattleTab('attacks');
    }

    function setBattleTab(name) {
      if (name === 'balls' && battleState.mode === 'pvp') {
        setStatus('Покеболы нельзя использовать в PvP-бою.', true);
        name = 'attacks';
      }
      document.querySelectorAll('[data-battle-panel]').forEach(panel => {
        panel.classList.toggle('is-active', panel.dataset.battlePanel === name);
      });
      document.querySelectorAll('[data-battle-tab]').forEach(button => {
        button.classList.toggle('is-active', button.dataset.battleTab === name);
      });
      const dock = document.querySelector('.battle-dock-window');
      if (dock) {
        dock.classList.toggle('is-subpanel-open', name === 'switch' || name === 'items' || name === 'balls');
        dock.classList.toggle('is-switch-panel', name === 'switch');
      }
      if (name === 'items' || name === 'balls') {
        loadBattlePocket();
      }
    }

    function currentBattleReplayId() {
      const explicit = Number(battleState.battleId || 0);
      if (explicit > 0) return explicit;
      const title = document.getElementById('battleTitle')?.textContent || '';
      const match = title.match(/#(\d+)/);
      return match ? Number(match[1] || 0) : 0;
    }

    async function openBattleReplay() {
      const battleId = currentBattleReplayId();
      if (!battleId) {
        setStatus('Повтор боя недоступен: battle_id не найден.', true);
        return;
      }
      setStatus('Загружаем повтор боя...');
      try {
        const response = await fetch('/api/battle/replay?battle_id=' + encodeURIComponent(String(battleId)), { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload.ok) {
          setStatus(payload.message || 'Повтор боя недоступен.', true);
          return;
        }
        renderBattleReplay(payload.replay || {});
        setStatus('Повтор боя загружен.');
      } catch (error) {
        setStatus('Не удалось загрузить повтор боя.', true);
      }
    }

    function renderBattleReplay(replay) {
      document.querySelector('.battle-replay-modal')?.remove();
      const summary = replay.summary || {};
      const byType = summary.byType || {};
      const rounds = Array.isArray(replay.rounds) ? replay.rounds : [];
      const modal = document.createElement('div');
      modal.className = 'battle-replay-modal';
      modal.innerHTML = `
        <div class="battle-replay-card" role="dialog" aria-modal="true" aria-label="Повтор боя">
          <header>
            <div>
              <b>Повтор боя #${escapeHtml((replay.battle && replay.battle.battle_id) || currentBattleReplayId())}</b>
              <small>events ${escapeHtml(summary.events || 0)} · logs ${escapeHtml(byType.round_log || 0)} · rolls ${escapeHtml(byType.random_roll || 0)} · damage ${escapeHtml(byType.damage || 0)}</small>
            </div>
            <button type="button" class="battle-replay-close" aria-label="Закрыть">&times;</button>
          </header>
          <div class="battle-replay-rounds">
            ${rounds.length ? rounds.map(round => {
              const logs = (round.logs || []).slice(-8).map(item => `<li>${escapeHtml(item)}</li>`).join('');
              const damage = (round.damage || []).slice(-8).map(item => `<li>${escapeHtml(item.move_name || item.moveName || 'damage')}: ${escapeHtml(item.damage || 0)} HP <small>${escapeHtml(item.actor_key || '')} -> ${escapeHtml(item.target_key || '')}</small></li>`).join('');
              const rolls = (round.randomRolls || []).slice(-10).map(item => `<li>${escapeHtml(item.label || 'roll')}: ${escapeHtml(item.roll)}${item.threshold !== null && item.threshold !== undefined ? ' / ' + escapeHtml(item.threshold) : ''} ${item.success === true ? '<b>OK</b>' : (item.success === false ? '<b>FAIL</b>' : '')}</li>`).join('');
              return `
                <details class="battle-replay-round" ${round.round === rounds[rounds.length - 1].round ? 'open' : ''}>
                  <summary>Раунд ${escapeHtml(round.round)} · logs ${(round.logs || []).length} · rolls ${(round.randomRolls || []).length} · damage ${(round.damage || []).length}</summary>
                  ${logs ? `<section><b>Логи</b><ul>${logs}</ul></section>` : ''}
                  ${damage ? `<section><b>Урон</b><ul>${damage}</ul></section>` : ''}
                  ${rolls ? `<section><b>Random</b><ul>${rolls}</ul></section>` : ''}
                </details>
              `;
            }).join('') : '<p class="battle-empty">Replay ещё не содержит событий.</p>'}
          </div>
        </div>
      `;
      document.body.appendChild(modal);
      const close = () => modal.remove();
      modal.querySelector('.battle-replay-close').addEventListener('click', close);
      modal.addEventListener('click', event => {
        if (event.target === modal) close();
      });
      const onKey = event => {
        if (event.key === 'Escape') {
          close();
          document.removeEventListener('keydown', onKey);
        }
      };
      document.addEventListener('keydown', onKey);
    }

    function syncBattleCatchControls(isPvpBattle) {
      const ballsDisabled = !!isPvpBattle;
      document.querySelectorAll('[data-battle-tab="balls"]').forEach(button => {
        button.hidden = ballsDisabled;
        button.disabled = ballsDisabled;
        button.setAttribute('aria-disabled', ballsDisabled ? 'true' : 'false');
      });
      document.querySelectorAll('[data-battle-panel="balls"]').forEach(panel => {
        panel.hidden = ballsDisabled;
      });
      document.querySelectorAll('.battle-dock-catch-info').forEach(block => {
        block.classList.toggle('is-disabled', ballsDisabled);
      });
      if (ballsDisabled) {
        const ballsList = document.getElementById('battleBallsList');
        if (ballsList) {
          ballsList.innerHTML = '<div class="battle-empty">Ловля недоступна в PvP-бою.</div>';
        }
        const activeTab = document.querySelector('[data-battle-tab].is-active')?.dataset.battleTab || '';
        if (activeTab === 'balls') {
          setBattleTab('attacks');
        }
      }
    }

    function setStatus(message, isError = false) {
      const el = document.getElementById('status');
      el.textContent = message;
      el.className = isError ? 'status error' : 'status';
      recordBugClientLog(isError ? 'error' : 'status', message, {
        locationId: state.locationId,
        battleId: currentBattleReplayId(),
        battleMode: battleState.mode || ''
      });
    }

    function bugReportJson(value) {
      const seen = new WeakSet();
      return JSON.stringify(value, (key, val) => {
        if (typeof val === 'function') return undefined;
        if (val && typeof val === 'object') {
          if (seen.has(val)) return '[circular]';
          seen.add(val);
        }
        if (typeof val === 'string' && val.length > 1000) {
          return val.slice(0, 1000) + '...';
        }
        return val;
      });
    }

    function bugReportSnapshot() {
      const battleId = currentBattleReplayId();
      return {
        url: window.location.href,
        route: window.location.pathname,
        locationId: Number(state.locationId || 0),
        status: document.getElementById('status')?.textContent || '',
        locationTitle: document.getElementById('locationTitle')?.textContent || '',
        pveButton: !!state.pveButton,
        battleId,
        battleState: {
          active: !!battleState.active,
          reviewing: !!battleState.reviewing,
          mode: battleState.mode || '',
          battleId,
          moves: (battleState.moves || []).map(move => ({
            id: move.id || move.move_id || 0,
            name: move.name || move.title || '',
            pp: move.pp || move.now_pp || ''
          }))
        },
        state: {
          busy: !!state.busy,
          locationId: Number(state.locationId || 0),
          activeNpc: state.activeNpc && (state.activeNpc.id || state.activeNpc.title || state.activeNpc.name) || null
        },
        viewport: {
          width: window.innerWidth,
          height: window.innerHeight,
          devicePixelRatio: window.devicePixelRatio || 1
        }
      };
    }

    function updateBugReportAttachmentSummary() {
      const box = document.getElementById('bugReportAttachments');
      if (!box) return;
      const snapshot = bugReportSnapshot();
      box.innerHTML = '';
      [
        ['State', `локация #${snapshot.locationId || 0}, ${snapshot.locationTitle || 'без названия'}`],
        ['Battle ID', snapshot.battleId ? `#${snapshot.battleId} (${snapshot.battleState.mode || 'battle'})` : 'нет активного battle id'],
        ['Client logs', `${bugClientLogs.length} событий`],
        ['Server logs', 'последние строки приложит сервер']
      ].forEach(([label, value]) => {
        const row = document.createElement('div');
        row.innerHTML = `<b>${label}</b><span></span>`;
        row.querySelector('span').textContent = value;
        box.appendChild(row);
      });
    }

    function openBugReportModal() {
      const overlay = document.getElementById('bugReportOverlay');
      if (!overlay) return;
      updateBugReportAttachmentSummary();
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      document.getElementById('bugReportTitle')?.focus();
      recordBugClientLog('ui', 'bug report modal opened', { battleId: currentBattleReplayId() });
    }

    function closeBugReportModal() {
      const overlay = document.getElementById('bugReportOverlay');
      if (!overlay) return;
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }

    async function submitBugReport() {
      const submit = document.getElementById('bugReportSubmitBtn');
      const title = document.getElementById('bugReportTitle')?.value.trim() || '';
      const description = document.getElementById('bugReportDescription')?.value.trim() || '';
      const severity = document.getElementById('bugReportSeverity')?.value || 'bug';
      if (title === '' && description === '') {
        setStatus('Опиши баг хотя бы одной строкой.', true);
        document.getElementById('bugReportTitle')?.focus();
        return;
      }
      const snapshot = bugReportSnapshot();
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('title', title);
      body.set('description', description);
      body.set('severity', severity);
      body.set('page_url', window.location.href);
      body.set('route', window.location.pathname);
      body.set('location_id', String(snapshot.locationId || 0));
      body.set('battle_id', String(snapshot.battleId || 0));
      body.set('battle_type', snapshot.battleState.mode || '');
      body.set('client_state_json', bugReportJson(snapshot));
      body.set('battle_state_json', bugReportJson(snapshot.battleState));
      body.set('client_logs_json', bugReportJson(bugClientLogs));
      body.set('viewport_json', bugReportJson(snapshot.viewport));
      body.set('meta_json', bugReportJson({ userAgent: navigator.userAgent, language: navigator.language }));

      if (submit) {
        submit.disabled = true;
        submit.textContent = 'Отправляем...';
      }
      try {
        const response = await fetch('/api/bug-reports', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        const payload = await response.json();
        setStatus(payload.message || (payload.ok ? 'Bug report отправлен.' : 'Не удалось отправить bug report.'), !payload.ok);
        if (payload.ok) {
          closeBugReportModal();
          document.getElementById('bugReportTitle').value = '';
          document.getElementById('bugReportDescription').value = '';
        }
      } catch (error) {
        setStatus('Не удалось отправить bug report.', true);
        recordBugClientLog('error', 'bug report submit failed', { error: String(error && error.message || error) });
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.textContent = 'Отправить';
        }
      }
    }

    function showGameNotice(message, variant = 'info') {
      if (!message) return;
      if (window.PokemonSocial && typeof window.PokemonSocial.notify === 'function') {
        window.PokemonSocial.notify(message, variant);
        return;
      }
      setStatus(message, variant === 'error');
    }

    async function fetchGameNotifications() {
      try {
        const response = await fetch('/api/notifications', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) return;
        for (const notice of (payload.notifications || [])) {
          showGameNotice(notice.message || notice.title || '', notice.variant || 'info');
        }
      } catch (error) {
        // Уведомления не должны ломать игровой экран.
      }
    }

    function render(payload) {
      if (!payload || payload.ok !== true) {
        setStatus(payload && payload.message ? payload.message : 'Не удалось загрузить состояние.', true);
        return;
      }

      if (payload.redirect) {
        window.location.href = payload.redirect;
        return;
      }

      const location = payload.location;
      const previousLocationId = Number(state.locationId || 0);
      const nextLocationId = Number(location.id || 0);
      const locationChanged = previousLocationId > 0 && previousLocationId !== nextLocationId;
      state.locationId = nextLocationId;
      app.dataset.locationId = String(state.locationId);
      window.dispatchEvent(new CustomEvent('pokemon:location-changed', { detail: { locationId: state.locationId } }));
      document.getElementById('locationTitle').textContent = location.title;
      document.getElementById('locationImage').src = location.image;
      document.getElementById('locationImage').alt = location.title;
      document.getElementById('locationText').textContent = location.description || ('Локация #' + location.id);

      if (locationChanged) {
        closeNpcPanel();
      }

      renderNpcs(location.npcs || []);
      renderBosses(payload.bosses || []);
      renderMoves(payload.moves || []);
      renderUsers(location.title, payload.users || []);
      state.pveButton = !!(payload.user && payload.user.pveButton);
      renderPveButton();
      renderDebugBattleButton(location);
      if (payload.battle && payload.battle.active) {
        openBattleOverlay();
        loadBattleState();
      }
      if (payload.wildEncounter && payload.wildEncounter.message) {
        setStatus(payload.wildEncounter.message);
      }

      if (payload.chatEvent && payload.chatEvent.text) {
        const line = document.createElement('div');
        line.textContent = payload.chatEvent.text;
        document.getElementById('chatLog').appendChild(line);
      }

      // Автообновление мира не должно сбивать открытый диалог NPC/питомника.
      if (!state.activeNpc) {
        setStatus('Готово • локация #' + state.locationId);
      }
    }

    function closeNpcPanel() {
      state.activeNpc = null;
      const panel = document.getElementById('npcPanel');
      panel.className = 'npc-panel';
      panel.innerHTML = '';
    }

    function renderDebugBattleButton(location) {
      const btn = document.getElementById('debugForceBattleBtn');
      if (!btn) return;
      const title = String(location && location.title ? location.title : '').toLowerCase();
      const isRoad1ByTitle = title.includes('дорога 1');
      const isRoad1ById = Number(state.locationId) === 4;
      btn.style.display = (isRoad1ByTitle || isRoad1ById) ? '' : 'none';
    }

    function renderNpcs(npcs) {
      const panel = document.getElementById('npcPanel');
      if (!state.activeNpc) {
        panel.className = 'npc-panel';
        panel.innerHTML = '';
      }

      const list = document.getElementById('npcs');
      list.innerHTML = '';
      for (const npc of npcs) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'npc-btn';
        button.innerHTML = '<span class="npc-icon"><img alt="" loading="lazy"></span><span class="npc-title"></span>';
        button.querySelector('.npc-icon img').src = npcIconSrc(npc);
        button.querySelector('.npc-title').textContent = npc.title;
        button.addEventListener('click', () => openNpc(npc));
        list.appendChild(button);
      }

      if (!list.children.length) {
        const empty = document.createElement('span');
        empty.className = 'muted';
        empty.textContent = 'NPC на этой локации пока переносятся.';
        list.appendChild(empty);
      }
    }

    function renderBosses(bosses) {
      const list = document.getElementById('bosses');
      if (!list) return;
      list.innerHTML = '';
      for (const boss of bosses) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'npc-btn boss-btn';
        button.innerHTML = '<span class="npc-icon"><img alt="" loading="lazy"></span><span class="npc-title"></span>';
        button.querySelector('.npc-icon img').src = assetIcon('npcs', 'trainer');
        button.querySelector('.npc-title').textContent = (boss.title || 'Босс') + ' · 6x6';
        button.title = boss.description || 'Ивентовый босс локации';
        button.addEventListener('click', () => startBossBattle(Number(boss.id || 0)));
        list.appendChild(button);
      }
    }

    function renderMoves(movesData) {
      const moves = document.getElementById('moves');
      moves.innerHTML = '';
      for (const move of movesData) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'move-btn route-btn';
        button.innerHTML = '<span class="route-ico" aria-hidden="true"><img alt="" loading="lazy"></span><span class="route-title"></span>';
        button.querySelector('.route-ico img').src = routeIconSrc(move.title);
        button.querySelector('.route-title').textContent = move.title;
        button.dataset.locationId = move.id;
        button.addEventListener('click', () => moveTo(move.id));
        moves.appendChild(button);
      }
    }

    function renderUsers(locationTitle, users) {
      document.getElementById('usersTitle').textContent = locationTitle + ' (' + users.length + ')';
      const usersList = document.getElementById('usersList');
      usersList.innerHTML = '';
      if (!users.length) {
        const empty = document.createElement('div');
        empty.className = 'muted';
        empty.textContent = 'Онлайн игроков в этой локации нет.';
        usersList.appendChild(empty);
        return;
      }
      for (const user of users) {
        const row = document.createElement('div');
        row.className = 'user-row player-row';
        row.tabIndex = 0;
        row.setAttribute('role', 'button');
        row.setAttribute('aria-label', 'Открыть меню игрока ' + String(user.login || ''));
        row.dataset.playerLogin = user.login || '';
        row.dataset.playerId = user.id || user.user_id || '';
        row.dataset.playerOnline = user.online ? '1' : '0';
        row.innerHTML = '<span class="dot ' + (user.online ? 'on' : '') + '"></span>' +
          '<span class="avatar" aria-hidden="true"></span><span class="user-name player-name"></span><span class="user-tags"></span>';
        row.querySelector('.avatar').textContent = String(user.login || '?').trim().charAt(0).toUpperCase() || '?';
        row.querySelector('.user-name').textContent = user.login;
        row.querySelector('.user-tags').textContent = (user.pveButton ? 'pve' : '');
        usersList.appendChild(row);
      }
    }

    function assetIcon(base, key) {
      return '/public/img/ui/world/' + base + '/' + key + '.png';
    }

    function npcIconSrc(npc) {
      const icon = String(npc && npc.icon ? npc.icon : '').toLowerCase();
      const title = String(npc && npc.title ? npc.title : '').toLowerCase();
      let key = 'default';

      if (icon === 'cross') key = 'pc';
      else if (icon === 'shop') key = 'shop';
      else if (icon === 'mentor') key = 'mentor';
      else if (icon === 'quest') key = 'quest';
      else if (icon === 'person') key = 'person';

      if (title.includes('профессор') || title.includes('исследователь')) key = 'professor';
      else if (title.includes('коллекционер')) key = 'collector';
      else if (title.includes('цветоч')) key = 'flower';
      else if (title.includes('худож')) key = 'artist';
      else if (title.includes('тренер') || title.includes('арен')) key = 'trainer';
      else if (title.includes('старая')) key = 'elder';
      else if (title.includes('касса') || title.includes('теплоход')) key = 'ticket';
      else if (title.includes('покецентр')) key = 'pc';
      else if (title.includes('покемаркет')) key = 'shop';

      return assetIcon('npcs', key);
    }

    function routeIconSrc(title) {
      const value = String(title || '').toLowerCase();
      let key = 'default';

      if (value.includes('лес')) key = 'forest';
      else if (value.includes('дорога') || value.includes('маршрут') || value.includes('перевал')) key = 'road';
      else if (value.includes('пещ') || value.includes('тунель') || value.includes('подвал')) key = 'cave';
      else if (value.includes('озеро') || value.includes('вод')) key = 'water';
      else if (value.includes('гора') || value.includes('скал') || value.includes('вершин')) key = 'mountain';
      else if (value.includes('пляж')) key = 'beach';
      else if (value.includes('порт')) key = 'port';
      else if (value.includes('лаборатор')) key = 'lab';
      else if (value.includes('стадион')) key = 'stadium';
      else if (value.includes('админ') || value.includes('панель') || value.includes('полицей')) key = 'admin';
      else if (value.includes('тюрь')) key = 'prison';
      else if (value.includes('дом') || value.includes('центр')) key = 'house';
      else if (value.includes('электро')) key = 'power';
      else if (value.includes('теплоход') || value.includes('дирижаб')) key = 'ship';
      else if (value.includes('вертолет')) key = 'air';
      else if (value.includes('пустын')) key = 'desert';
      else if (value.includes('мост')) key = 'bridge';
      else if (value.includes('парк') || value.includes('зона трениров')) key = 'park';
      else if (
        value.includes('алабаст') || value.includes('вертания') || value.includes('пьютер') || value.includes('церулин') ||
        value.includes('целадон') || value.includes('саффрон') || value.includes('фуксия') || value.includes('лавандер') ||
        value.includes('вермилион') || value.includes('экрутек') || value.includes('оливин') || value.includes('азалия') ||
        value.includes('литтлруд') || value.includes('олдэйл') || value.includes('петалбург') || value.includes('рустборн') ||
        value.includes('маувайл') || value.includes('феларбор') || value.includes('лаваридж') || value.includes('фортри') ||
        value.includes('лилистовн') || value.includes('сити') || value.includes('пригород')
      ) key = 'town';

      return assetIcon('locations', key);
    }

    function renderPveButton() {
      const button = document.getElementById('pveButton');
      button.textContent = state.pveButton ? 'Нападение: вкл' : 'Нападение: выкл';
    }

    function openInventory() {
      const overlay = document.getElementById('inventoryOverlay');
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      loadInventoryPage(inventory.page || 1);
    }

    function closeInventory() {
      const overlay = document.getElementById('inventoryOverlay');
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }

    function openPokemonModal() {
      const overlay = document.getElementById('pokemonOverlay');
      const frame = document.getElementById('pokemonFrame');
      setupPokemonDrag();
      if (frame.getAttribute('src') === 'about:blank') {
        frame.src = '/game/pokemon';
      } else if (frame.contentWindow && frame.contentWindow.PokemonTeamPanel) {
        frame.contentWindow.PokemonTeamPanel.open();
      }
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
    }

    function closePokemonModal() {
      const overlay = document.getElementById('pokemonOverlay');
      const frame = document.getElementById('pokemonFrame');
      if (frame.contentWindow && frame.contentWindow.PokemonTeamPanel) {
        frame.contentWindow.PokemonTeamPanel.reset();
      }
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }

    function setupPokemonDrag() {
      if (pokemonWindowDrag.ready) return;
      pokemonWindowDrag.ready = true;
      const overlay = document.getElementById('pokemonOverlay');
      const win = overlay.querySelector('.pokemon-window');
      const handle = document.getElementById('pokemonDragHandle');
      const clampWindow = (left, top) => {
        const pad = 8;
        const maxLeft = Math.max(pad, window.innerWidth - win.offsetWidth - pad);
        const maxTop = Math.max(pad, window.innerHeight - win.offsetHeight - pad);
        return {
          left: Math.max(pad, Math.min(left, maxLeft)),
          top: Math.max(pad, Math.min(top, maxTop)),
        };
      };

      const startDrag = event => {
        if (typeof event.button === 'number' && event.button !== 0) return;
        const rect = win.getBoundingClientRect();
        pokemonWindowDrag.dragging = true;
        pokemonWindowDrag.offsetX = event.clientX - rect.left;
        pokemonWindowDrag.offsetY = event.clientY - rect.top;
        win.classList.add('is-dragged');
        win.classList.add('is-dragging');
        win.style.left = rect.left + 'px';
        win.style.top = rect.top + 'px';
        event.preventDefault();
        if ('pointerId' in event) {
          try { handle.setPointerCapture(event.pointerId); } catch (e) {}
        }
      };
      const moveDrag = event => {
        if (!pokemonWindowDrag.dragging) return;
        const pos = clampWindow(event.clientX - pokemonWindowDrag.offsetX, event.clientY - pokemonWindowDrag.offsetY);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      };
      const stopDrag = event => {
        pokemonWindowDrag.dragging = false;
        win.classList.remove('is-dragging');
        if ('pointerId' in event) {
          try { handle.releasePointerCapture(event.pointerId); } catch (e) {}
        }
      };

      handle.addEventListener('pointerdown', startDrag);
      window.addEventListener('pointermove', moveDrag);
      window.addEventListener('pointerup', stopDrag);
      window.addEventListener('pointercancel', stopDrag);
      handle.addEventListener('mousedown', startDrag);
      window.addEventListener('mousemove', moveDrag);
      window.addEventListener('mouseup', stopDrag);
    }

    function openBattleOverlay() {
      setupBattleSideTabs();
      setupBattleDrag();
      const overlay = document.getElementById('battleOverlay');
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
      battleState.active = true;
    }

    function setupBattleDrag() {
      if (battleWindowDrag.ready) return;
      battleWindowDrag.ready = true;
      const overlay = document.getElementById('battleOverlay');
      const win = overlay.querySelector('.battle-window');
      const head = win.querySelector('.battle-head');
      const clampWindow = (left, top) => {
        const overlayRect = overlay.getBoundingClientRect();
        const winRect = win.getBoundingClientRect();
        const pad = 8;
        const maxLeft = Math.max(pad, overlayRect.width - winRect.width - pad);
        const maxTop = Math.max(pad, overlayRect.height - winRect.height - pad);
        return {
          left: Math.max(pad, Math.min(left, maxLeft)),
          top: Math.max(pad, Math.min(top, maxTop)),
        };
      };

      head.addEventListener('pointerdown', event => {
        if (event.target.closest('button')) return;
        const rect = win.getBoundingClientRect();
        battleWindowDrag.dragging = true;
        battleWindowDrag.offsetX = event.clientX - rect.left;
        battleWindowDrag.offsetY = event.clientY - rect.top;
        win.classList.add('is-dragged');
        win.style.left = rect.left + 'px';
        win.style.top = rect.top + 'px';
        head.setPointerCapture(event.pointerId);
      });
      head.addEventListener('pointermove', event => {
        if (!battleWindowDrag.dragging) return;
        const pos = clampWindow(event.clientX - battleWindowDrag.offsetX, event.clientY - battleWindowDrag.offsetY);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });
      head.addEventListener('pointerup', event => {
        battleWindowDrag.dragging = false;
        try { head.releasePointerCapture(event.pointerId); } catch (e) {}
      });
      head.addEventListener('pointercancel', () => {
        battleWindowDrag.dragging = false;
      });
      window.addEventListener('resize', () => {
        if (!win.classList.contains('is-dragged')) return;
        const rect = win.getBoundingClientRect();
        const pos = clampWindow(rect.left, rect.top);
        win.style.left = pos.left + 'px';
        win.style.top = pos.top + 'px';
      });
    }

    function closeBattleOverlay() {
      const overlay = document.getElementById('battleOverlay');
      hideBattleMoveTooltip();
      overlay.classList.remove('is-open');
      overlay.classList.remove('is-review');
      overlay.setAttribute('aria-hidden', 'true');
      const left = document.querySelector('.battle-left');
      if (left) left.classList.remove('is-review');
      battleState.active = false;
      battleState.reviewing = false;
      battleState.mode = '';
      syncBattleCatchControls(false);
    }

    function renderBattleLog(logByRound, messages) {
      const log = document.getElementById('battleLog');
      log.innerHTML = '';
      const seenMessages = new Set();

      if (Array.isArray(logByRound) && logByRound.length) {
        for (const chunk of logByRound) {
          const title = document.createElement('div');
          title.className = 'battle-log-round';
          title.textContent = 'Раунд ' + (Number(chunk.round) || 1);
          log.appendChild(title);

          for (const eventText of (chunk.events || [])) {
            seenMessages.add(String(eventText || '').trim());
            const line = document.createElement('div');
            line.className = 'battle-log-event';
            line.innerHTML = decorateBattleLogText(String(eventText || ''));
            log.appendChild(line);
          }
        }
      }

      if (Array.isArray(messages) && messages.length) {
        for (const message of messages) {
          if (seenMessages.has(String(message || '').trim())) continue;
          const line = document.createElement('div');
          line.className = 'battle-log-event is-live';
          line.innerHTML = decorateBattleLogText(String(message || ''));
          log.appendChild(line);
        }
      }
      log.scrollTop = 0;
    }

    function escapeHtml(text) {
      return String(text).replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[ch]));
    }

    function escapeRegExp(text) {
      return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function collectKnownBattleMoves(player, enemy, moves) {
      const result = [];
      const seen = new Set();
      const pushMove = (move) => {
        if (!move || !move.name) return;
        const key = String(move.name).toLowerCase();
        if (seen.has(key)) return;
        seen.add(key);
        result.push(move);
      };
      (Array.isArray(moves) ? moves : []).forEach(pushMove);
      (Array.isArray(player && player.movesPreview) ? player.movesPreview : []).forEach(pushMove);
      (Array.isArray(enemy && enemy.movesPreview) ? enemy.movesPreview : []).forEach(pushMove);
      battleState.knownMoves = result;
    }

    function decorateBattleLogMoves(html) {
      const moves = (battleState.knownMoves || [])
        .map((move, index) => ({ move, index, name: String(move.name || '') }))
        .filter(item => item.name.length >= 2)
        .sort((a, b) => b.name.length - a.name.length);
      for (const item of moves) {
        const escapedName = escapeHtml(item.name);
        const rx = new RegExp('(^|[^\\wА-Яа-яЁё])(' + escapeRegExp(escapedName) + ')(?=$|[^\\wА-Яа-яЁё])', 'giu');
        html = html.replace(rx, '$1<button type="button" class="battle-log-move" data-move-index="' + item.index + '">$2</button>');
      }
      return html;
    }

    function decorateBattleLogText(text) {
      let html = escapeHtml(text);

      // Подсветка ключевых слов и значений урона/HP.
      html = html.replace(/КРИТ!/gi, '<span class="log-crit">КРИТ!</span>');
      html = html.replace(/промах!?/gi, '<span class="log-miss">$&</span>');
      html = html.replace(/теряет\s+(\d+)\s+HP/gi, 'теряет <span class="log-damage">$1 HP</span>');
      html = html.replace(/\((\d+\/\d+)\)/g, '(<span class="log-hp">$1</span>)');
      html = html.replace(/Победа в бою\./gi, '<span class="log-win">Победа в бою.</span>');
      html = html.replace(/Поражение в бою\./gi, '<span class="log-lose">Поражение в бою.</span>');
      html = html.replace(/Вы успешно сбежали из боя\./gi, '<span class="log-escape">Вы успешно сбежали из боя.</span>');
      html = html.replace(/Награда:/gi, '<span class="log-reward">Награда:</span>');

      // Базовая цветная подсветка имен до первого "использует"/"теряет".
      html = html.replace(/^([^\.]+?)\sиспользует\s/i, '<span class="log-actor">$1</span> использует ');
      html = html.replace(/\. ([^\.]+?) теряет /i, '. <span class="log-target">$1</span> теряет ');

      return decorateBattleLogMoves(html);
    }

    function battleTypeKey(type) {
      const raw = String(type || 'normal').toLowerCase().replace(/[^a-z0-9а-яё]+/gi, '');
      const aliases = {
        normal: 'normal',
        fighting: 'fighting',
        fire: 'fire',
        water: 'water',
        grass: 'grass',
        electric: 'electric',
        ice: 'ice',
        poison: 'poison',
        ground: 'ground',
        flying: 'flying',
        psychic: 'psychic',
        bug: 'bug',
        rock: 'rock',
        ghost: 'ghost',
        dragon: 'dragon',
        dark: 'dark',
        steel: 'steel',
        норма: 'normal',
        нормальный: 'normal',
        боевой: 'fighting',
        огонь: 'fire',
        огненный: 'fire',
        вода: 'water',
        водный: 'water',
        трава: 'grass',
        травяной: 'grass',
        электро: 'electric',
        электрический: 'electric',
        лед: 'ice',
        ледяной: 'ice',
        яд: 'poison',
        ядовитый: 'poison',
        земля: 'ground',
        земляной: 'ground',
        летающий: 'flying',
        психический: 'psychic',
        жук: 'bug',
        камень: 'rock',
        каменный: 'rock',
        призрак: 'ghost',
        дракон: 'dragon',
        темный: 'dark',
        сталь: 'steel',
        стальной: 'steel'
      };
      return aliases[raw] || 'normal';
    }

    function battleTypeIconSrc(type) {
      return '/public/img/types/' + battleTypeKey(type) + '.png';
    }

    function battleGender(pokemon) {
      const raw = String((pokemon && (pokemon.gender || pokemon.sex || pokemon.pol)) || '').toLowerCase();
      if (raw.includes('female') || raw.includes('ж') || raw === '2') return '♀';
      if (raw.includes('male') || raw.includes('м') || raw === '1') return '♂';
      return '♂';
    }

    function battleEnergyPercent(pokemon) {
      const value = Number((pokemon && (pokemon.energy ?? pokemon.exp ?? pokemon.xpPercent)) || 0);
      if (value > 0) return Math.max(0, Math.min(100, value));
      return 26;
    }

    function renderBattle(payload) {
      if (!payload || payload.ok !== true) {
        let msg = payload && payload.message ? payload.message : 'Ошибка состояния боя.';
        if (payload && payload.debug) {
          msg += ' [' + JSON.stringify(payload.debug) + ']';
          renderBattleSpritesFromDebug(payload.debug);
        }
        setStatus(msg, true);
        renderBattleLog([], [msg]);
        return;
      }
      if (!payload.active && !payload.finished && !payload.result) {
        if (battleState.reviewing) {
          return;
        }
        closeBattleOverlay();
        return;
      }
      openBattleOverlay();
      battleState.reviewing = !!payload.finished;
      const left = document.querySelector('.battle-left');
      if (left) left.classList.toggle('is-review', battleState.reviewing);
      document.getElementById('battleOverlay').classList.toggle('is-review', battleState.reviewing);

      const battle = payload.battle || payload;
      const player = battle.player || { name: 'Ваш покемон', level: 1, hp: 0, hpMax: 1, baseNum: 0 };
      const enemy = battle.enemy || { name: 'Дикий покемон', level: 1, hp: 0, hpMax: 1, baseNum: 0 };
      const isPvpBattle = battle.mode === 'pvp' || !!(enemy && enemy.trainer);
      battleState.mode = isPvpBattle ? 'pvp' : 'pve';
      battleState.battleId = Number(battle.id || payload.battle_id || payload.battleId || 0);
      syncBattleCatchControls(isPvpBattle);

      document.getElementById('battleTitle').textContent = battle.title || ((battle.mode === 'pvp' ? 'PvP бой #' : 'PvE бой #') + battle.id);
      document.getElementById('battleRound').textContent = 'Раунд ' + (battle.round || 1);
      const turnLabel = document.getElementById('battleTurnLabel');
      if (turnLabel) {
        turnLabel.textContent = payload.finished
          ? 'Бой завершен'
          : (battle.waitingForOpponent ? 'Ждем соперника' : 'Ваш ход');
      }
      const weatherLabel = document.getElementById('battleWeatherLabel');
      if (weatherLabel) {
        weatherLabel.textContent = battle.weather && battle.weather.name ? battle.weather.name : 'Поле боя';
      }
      const weatherTurns = document.getElementById('battleWeatherTurns');
      if (weatherTurns) {
        weatherTurns.textContent = battle.weather && battle.weather.turns ? battle.weather.turns : '';
      }
      document.getElementById('battlePlayerName').textContent = player.name || 'Ваш покемон';
      document.getElementById('battleEnemyName').textContent = enemy.name || 'Дикий покемон';
      renderBattleHeldItem('battlePlayerHeld', player.heldItem || player.held_item || null);
      renderBattleHeldItem('battleEnemyHeld', enemy.heldItem || enemy.held_item || null);
      const playerLevel = document.getElementById('battlePlayerLevel');
      const enemyLevel = document.getElementById('battleEnemyLevel');
      if (playerLevel) playerLevel.textContent = Number(player.level || 1);
      if (enemyLevel) enemyLevel.textContent = Number(enemy.level || 1);
      const playerGender = document.getElementById('battlePlayerGender');
      const enemyGender = document.getElementById('battleEnemyGender');
      if (playerGender) playerGender.textContent = battleGender(player);
      if (enemyGender) enemyGender.textContent = battleGender(enemy);
      document.getElementById('battlePlayerHp').textContent = 'HP ' + player.hp + '/' + player.hpMax;
      document.getElementById('battleEnemyHp').textContent = 'HP ' + enemy.hp + '/' + enemy.hpMax;
      renderBattleStatuses('battlePlayerStatuses', player.statuses || [], player.majorStatuses || []);
      renderBattleStatuses('battleEnemyStatuses', enemy.statuses || [], enemy.majorStatuses || []);
      battleHoverState.player = player;
      battleHoverState.enemy = enemy;
      setupBattlePokemonHover();
      renderBattleSprites(player, enemy);

      const playerHpPercent = Math.max(0, Math.min(100, (player.hp / Math.max(1, player.hpMax)) * 100));
      const enemyHpPercent = Math.max(0, Math.min(100, (enemy.hp / Math.max(1, enemy.hpMax)) * 100));
      document.getElementById('battlePlayerHpBar').style.width = playerHpPercent + '%';
      document.getElementById('battleEnemyHpBar').style.width = enemyHpPercent + '%';
      const playerEnergy = document.getElementById('battlePlayerEnergyBar');
      const enemyEnergy = document.getElementById('battleEnemyEnergyBar');
      if (playerEnergy) playerEnergy.style.width = battleEnergyPercent(player) + '%';
      if (enemyEnergy) enemyEnergy.style.width = battleEnergyPercent(enemy) + '%';
      const enemyKind = document.getElementById('battleEnemyKind');
      const enemyCatchText = document.getElementById('battleEnemyCatchText');
      const enemyRarity = document.getElementById('battleEnemyRarity');
      if (enemyKind) enemyKind.textContent = isPvpBattle || (battle.enemy && battle.enemy.trainer) ? 'Покемон тренера' : 'Дикий покемон';
      if (enemyCatchText) enemyCatchText.textContent = isPvpBattle ? 'Ловля недоступна в PvP' : (battle.enemy && battle.enemy.trainer ? 'Нельзя поймать' : 'Можно поймать');
      if (enemyRarity) enemyRarity.textContent = enemy.rarity || enemy.rank || 'Частый';

      battleState.moves = Array.isArray(battle.moves) ? battle.moves : [];
      collectKnownBattleMoves(player, enemy, battleState.moves);
      for (let i = 0; i < 4; i++) {
        const button = document.getElementById('battleMove' + (i + 1));
        const move = battleState.moves[i];
        button.className = 'battle-move-btn';
        button.dataset.moveIndex = String(i);
        if (move) {
          button.disabled = false;
          const power = Number(move.power || 0);
          const acc = Number(move.accuracy || 0);
          const pp = Number(move.pp || 0);
          const ppMax = Number(move.ppMax || 0);
          button.disabled = ppMax > 0 && pp <= 0;
          button.classList.add('type-' + battleTypeKey(move.type || move.tip || move.tipe || move.element));
          button.innerHTML = '<span class="battle-move-icon"><img alt=""></span><span class="battle-move-main"><span class="t"></span><span class="s"></span></span><span class="pp"></span>';
          button.querySelector('.battle-move-icon img').src = battleTypeIconSrc(move.type || move.tip || move.tipe || move.element);
          button.querySelector('.t').textContent = move.name;
          button.querySelector('.pp').textContent = ppMax > 0 ? (pp + '/' + ppMax) : '';
          button.querySelector('.s').textContent = 'Сила: ' + (power || 0) + ' • Точность: ' + (acc || 0) + '%';
          button.dataset.moveId = String(move.id);
        } else {
          button.disabled = true;
          button.classList.add('type-normal');
          button.innerHTML = '<span class="battle-move-icon"><img src="/public/img/types/normal.png" alt=""></span><span class="battle-move-main"><span class="t">Нет атаки</span><span class="s">Сила: 0 • Точность: 0%</span></span><span class="pp"></span>';
          button.dataset.moveId = '';
        }
      }

      const switchSelect = document.getElementById('battleSwitchSelect');
      switchSelect.innerHTML = '';
      const options = Array.isArray(battle.switchOptions) ? battle.switchOptions : [];
      for (const option of options) {
        const row = document.createElement('option');
        row.value = String(option.id);
        row.textContent = option.name + ' (' + option.hp + '/' + option.hpMax + ')';
        row.disabled = !!option.disabled;
        switchSelect.appendChild(row);
      }
      renderBattleSwitchList(options);

      const extraMessages = [...(payload.messages || [])];
      if (payload.rewards && Number(payload.rewards.coins || 0) > 0) {
        const rewardParts = [
          Number(payload.rewards.coins) + ' монет',
          Number(payload.rewards.exp || 0) + ' опыта',
        ];
        if (Number(payload.rewards.happiness || 0) > 0) {
          rewardParts.push('+' + Number(payload.rewards.happiness || 0) + ' счастья');
        }
        extraMessages.push('Награда: ' + rewardParts.join(', ') + '.');
      }
      renderBattleLog(battle.logByRound || [], extraMessages);

      if (payload.finished) {
        const finishLine = payload.result === 'win'
          ? 'Победа в бою.'
          : (payload.result === 'caught' ? 'Покемон пойман.' : (payload.result === 'escape' ? 'Вы покинули бой.' : 'Поражение в бою.'));
        renderBattleLog(battle.logByRound || [], [...extraMessages, finishLine]);
      }

      const doneBox = document.getElementById('battleFinishBox');
      doneBox.hidden = !payload.finished;
      setBattleControlsDisabled(!!payload.finished || battle.canAct === false || battle.waitingForOpponent === true);
    }

    function renderBattleHeldItem(id, heldItem) {
      const el = document.getElementById(id);
      if (!el) return;
      const itemId = Number(heldItem && (heldItem.id || heldItem.item_id) || 0);
      if (itemId <= 0) {
        el.hidden = true;
        return;
      }
      el.hidden = false;
      setItemIcon(el.querySelector('img'), { item_id: itemId });
      el.querySelector('span').textContent = heldItem.name || ('Item #' + itemId);
      el.title = el.querySelector('span').textContent;
    }

    function setBattleControlsDisabled(disabled) {
      for (let i = 1; i <= 4; i++) {
        document.getElementById('battleMove' + i).disabled = disabled || document.getElementById('battleMove' + i).disabled;
      }
      document.getElementById('battleSwitchSelect').disabled = disabled;
      document.getElementById('battleSwitchBtn').disabled = disabled;
      document.getElementById('battleEscapeBtn').disabled = disabled;
    }

    function renderBattleStatuses(targetId, statuses, majorStatuses = []) {
      const box = document.getElementById(targetId);
      if (!box) return;
      box.innerHTML = '';
      if (Array.isArray(majorStatuses)) {
        for (const status of majorStatuses.slice(0, 3)) {
          const badge = document.createElement('span');
          badge.className = 'major';
          badge.textContent = String(status.name || ('Статус #' + status.id));
          box.appendChild(badge);
        }
      }
      if (!Array.isArray(statuses)) return;
      for (const status of statuses.slice(0, 5)) {
        const badge = document.createElement('span');
        badge.className = status.kind === 'minus' ? 'minus' : 'plus';
        badge.textContent = String(status.label || '') + ' ' + String(status.sign || '') + String(status.value || '');
        box.appendChild(badge);
      }
    }

    function setupBattlePokemonHover() {
      if (battleHoverState.ready) return;
      battleHoverState.ready = true;
      const targets = [
        ['player', document.getElementById('battlePlayerSprite'), document.querySelector('.fighter-player')],
        ['enemy', document.getElementById('battleEnemySprite'), document.querySelector('.fighter-enemy')],
      ];
      for (const [kind, ...nodes] of targets) {
        for (const node of nodes) {
          if (!node) continue;
          node.addEventListener('mouseenter', event => showBattlePokemonTooltip(event, kind));
          node.addEventListener('mousemove', moveBattlePokemonTooltip);
          node.addEventListener('mouseleave', hideBattlePokemonTooltip);
        }
      }
    }

    function showBattlePokemonTooltip(event, kind) {
      const pokemon = battleHoverState[kind];
      if (!pokemon) return;
      const tooltip = document.getElementById('battlePokeTooltip');
      const hpMax = Math.max(1, Number(pokemon.hpMax || 1));
      const hp = Math.max(0, Number(pokemon.hp || 0));
      const stats = pokemon.stats || {};
      const moves = Array.isArray(pokemon.movesPreview) ? pokemon.movesPreview.slice(0, 4) : [];
      const statuses = Array.isArray(pokemon.statuses) ? pokemon.statuses : [];
      const majorStatuses = Array.isArray(pokemon.majorStatuses) ? pokemon.majorStatuses : [];
      const typeLabel = String(pokemon.tips || 'normal').toLowerCase().includes('shine') ? 'SHINY' : 'NORMAL';
      const statText = statuses.length
        ? statuses.map(s => escapeHtml(String(s.label || '') + ' ' + String(s.sign || '') + String(s.value || ''))).join(', ')
        : 'нет';
      const majorText = majorStatuses.length
        ? majorStatuses.map(s => escapeHtml(String(s.name || 'Статус'))).join(', ')
        : 'нет';
      const movesText = moves.length
        ? moves.map(m => '<span class="tip-move">• ' + escapeHtml(m.name || 'Атака') + '</span> (' + Number(m.pp || 0) + '/' + Number(m.ppMax || 0) + ' | ' + Number(m.power || 0) + '/' + Number(m.accuracy || 0) + ')').join('<br>')
        : 'нет данных';
      tooltip.innerHTML = [
        '<h4>' + escapeHtml(pokemon.name || 'Pokemon') + ' <small>Lv.' + Number(pokemon.level || 1) + '</small></h4>',
        '<span class="tip-type">' + typeLabel + '</span>',
        '<div>HP: ' + Math.round((hp / hpMax) * 100) + '% (' + hp + '/' + hpMax + ')</div>',
        '<div>Статусы: ' + majorText + '</div>',
        '<div>Модификаторы: ' + statText + '</div>',
        '<div>Раскрытые атаки:<br>' + movesText + '</div>',
        '<table><tr><th>Атака</th><th>Защита</th><th>С. Атака</th><th>С. Защита</th><th>Скорость</th></tr>',
        '<tr><td>' + Number(stats.atk || 0) + '</td><td>' + Number(stats.def || 0) + '</td><td>' + Number(stats.satk || 0) + '</td><td>' + Number(stats.sdef || 0) + '</td><td>' + Number(stats.speed || 0) + '</td></tr></table>',
      ].join('');
      tooltip.style.display = 'block';
      moveBattlePokemonTooltip(event);
    }

    function moveBattlePokemonTooltip(event) {
      const tooltip = document.getElementById('battlePokeTooltip');
      if (tooltip.style.display !== 'block') return;
      const pad = 12;
      let left = event.clientX + 14;
      let top = event.clientY + 14;
      const rect = tooltip.getBoundingClientRect();
      if (left + rect.width + pad > window.innerWidth) {
        left = event.clientX - rect.width - 14;
      }
      if (top + rect.height + pad > window.innerHeight) {
        top = event.clientY - rect.height - 14;
      }
      tooltip.style.left = Math.max(pad, left) + 'px';
      tooltip.style.top = Math.max(pad, top) + 'px';
    }

    function hideBattlePokemonTooltip() {
      document.getElementById('battlePokeTooltip').style.display = 'none';
    }

    function battleMoveCategoryName(move) {
      if (move.categoryName) return String(move.categoryName);
      const category = Number(move.category || move.atac_categori || 0);
      if (category === 1) return 'Физическая';
      if (category === 2) return 'Специальная';
      return 'Статусная';
    }

    function battleMoveEffectText(effect) {
      const target = effect.target === 'self' ? 'пользователю' : 'цели';
      if (Number(effect.statusId || 0) > 0 || effect.kind === 'status') {
        const chance = Number(effect.chance || 100);
        return (chance < 100 ? chance + '%: ' : '') + 'накладывает статус ' + (effect.label || ('#' + Number(effect.statusId || 0)));
      }
      const chance = Number(effect.chance || 100);
      const prefix = chance < 100 ? chance + '%: ' : '';
      return prefix + (effect.label || 'Параметр') + ' ' + target + ' ' + (effect.value || '');
    }

    function showBattleMoveTooltip(event, move) {
      if (!move || !Number(move.id || 0)) return;
      const tooltip = document.getElementById('battleMoveTooltip');
      const type = move.type || move.tip || move.tipe || move.element || 'Normal';
      const icon = battleTypeIconSrc(type);
      const power = Number(move.power || 0);
      const accuracy = Number(move.accuracy || 0);
      const pp = Number(move.pp || 0);
      const ppMax = Number(move.ppMax || move.pp || 0);
      const effects = Array.isArray(move.effects) ? move.effects : [];
      const effectHtml = effects.length
        ? effects.map(effect => '<span>' + escapeHtml(battleMoveEffectText(effect)) + '</span>').join('')
        : '<span>Дополнительных эффектов нет.</span>';
      const description = String(move.description || move.details || 'Описание атаки пока не заполнено.');

      tooltip.innerHTML = [
        '<div class="bmt-head">',
          '<img src="' + icon + '" alt="">',
          '<div><b>' + escapeHtml(move.name || 'Атака') + '</b><small>' + escapeHtml(type) + ', ' + pp + '/' + ppMax + ' PP</small></div>',
        '</div>',
        '<div class="bmt-chips">',
          '<span>' + battleMoveCategoryName(move) + '</span>',
          '<span>Точность ' + (accuracy || 0) + '</span>',
          '<span>Мощность ' + (power || 0) + '</span>',
        '</div>',
        '<p>' + escapeHtml(description) + '</p>',
        '<div class="bmt-effects">' + effectHtml + '</div>',
      ].join('');
      tooltip.style.display = 'block';
      moveBattleMoveTooltip(event);
    }

    function moveBattleMoveTooltip(event) {
      const tooltip = document.getElementById('battleMoveTooltip');
      if (tooltip.style.display !== 'block') return;
      const pad = 12;
      let left = event.clientX + 14;
      let top = event.clientY + 14;
      const rect = tooltip.getBoundingClientRect();
      if (left + rect.width + pad > window.innerWidth) {
        left = event.clientX - rect.width - 14;
      }
      if (top + rect.height + pad > window.innerHeight) {
        top = event.clientY - rect.height - 14;
      }
      tooltip.style.left = Math.max(pad, left) + 'px';
      tooltip.style.top = Math.max(pad, top) + 'px';
    }

    function hideBattleMoveTooltip() {
      battleHoverState.movePinned = false;
      document.getElementById('battleMoveTooltip').style.display = 'none';
    }

    function renderBattleSwitchList(options) {
      const list = document.getElementById('battleSwitchList');
      if (!list) return;
      list.innerHTML = '';
      if (!Array.isArray(options) || !options.length) {
        list.innerHTML = '<div class="battle-empty">&#1053;&#1077;&#1090; &#1087;&#1086;&#1082;&#1077;&#1084;&#1086;&#1085;&#1086;&#1074; &#1076;&#1083;&#1103; &#1079;&#1072;&#1084;&#1077;&#1085;&#1099;.</div>';
        return;
      }
      for (const option of options) {
        const hp = Number(option.hp || 0);
        const hpMax = Math.max(1, Number(option.hpMax || 1));
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'battle-switch-card';
        button.disabled = !!option.disabled;
        button.innerHTML = '<span class="mini-poke"><img alt=""></span><span class="battle-row-main"><b></b><small class="battle-row-meta"></small><small class="battle-row-held" hidden><img alt=""><span></span></small><span class="mini-bars"><i class="hp"></i><i class="xp"></i></span></span>';
        button.querySelector('b').textContent = option.name || 'Pokemon';
        button.querySelector('.battle-row-meta').textContent = 'Lv.' + Number(option.level || 1) + ' • HP ' + hp + '/' + hpMax;
        const miniImg = button.querySelector('.mini-poke img');
        const miniCandidates = pokemonSpriteUrls(option, isShinyBattlePokemon(option)
          ? ['frontShiny', 'shiny', 'frontSprite', 'spriteFront', 'sprite', 'front', 'normal']
          : ['frontSprite', 'spriteFront', 'sprite', 'front', 'normal']
        );
        if (isShinyBattlePokemon(option)) {
          miniCandidates.push(...spriteCandidatesAny('shiny', pokemonBaseNum(option)));
          miniCandidates.push(...spriteCandidatesAny('shine', pokemonBaseNum(option)));
        }
        miniCandidates.push(...spriteCandidatesAny('anim', pokemonBaseNum(option)));
        miniCandidates.push(...spriteCandidatesAny('pok', pokemonBaseNum(option)));
        miniCandidates.push(...spriteCandidatesAny('normal', pokemonBaseNum(option)));
        setSpriteWithFallback(miniImg, miniCandidates);
        const held = button.querySelector('.battle-row-held');
        if (held && Number(option.heldItemId || 0) > 0) {
          held.hidden = false;
          setItemIcon(held.querySelector('img'), { item_id: option.heldItemId });
          held.querySelector('span').textContent = option.heldItemName || ('Item #' + Number(option.heldItemId || 0));
        }
        button.querySelector('.hp').style.width = Math.max(0, Math.min(100, (hp / hpMax) * 100)) + '%';
        button.querySelector('.xp').style.width = '22%';
        button.addEventListener('click', () => {
          if (button.disabled) return;
          document.getElementById('battleSwitchSelect').value = String(option.id);
          battleAction('switch', { pokemon_id: option.id });
        });
        list.appendChild(button);
      }
    }

    async function loadBattlePocket() {
      if (!battlePocket.loaded) {
        const itemsList = document.getElementById('battleItemsList');
        const ballsList = document.getElementById('battleBallsList');
        if (itemsList) itemsList.innerHTML = '<div class="battle-empty">Loading...</div>';
        if (ballsList) ballsList.innerHTML = '<div class="battle-empty">Loading...</div>';
        try {
          const response = await fetch('/api/inventory/battle', { credentials: 'same-origin' });
          const payload = await response.json();
          battlePocket.items = payload && payload.ok === true && Array.isArray(payload.items) ? payload.items : [];
          battlePocket.categories = payload && payload.categories ? payload.categories : { items: [], balls: [] };
          battlePocket.loaded = true;
          if (window.console && typeof window.console.debug === 'function') {
            const summary = payload && payload.summary ? payload.summary : {};
            console.debug('[INVENTORY] Loaded ' + Number(summary.items || 0) + ' items');
            console.debug('[INVENTORY] Loaded ' + Number(summary.balls || 0) + ' pokeballs');
          }
        } catch (e) {
          battlePocket.items = [];
          battlePocket.categories = { items: [], balls: [] };
          battlePocket.loaded = true;
        }
      }
      renderBattlePocketLists();
    }

    function renderBattlePocketLists() {
      const all = battlePocket.items || [];
      const balls = battleState.mode === 'pvp' ? [] : all.filter(item => battleItemPocket(item) === 'balls');
      const itemCategory = battlePocket.activeItemCategory || 'all';
      const items = all.filter(item => {
        if (battleItemPocket(item) !== 'items' || Number(item.item_id || 0) === 1) return false;
        return itemCategory === 'all' || String(item.battle_category || 'utility') === itemCategory;
      });
      renderBattlePocketToolbar(all.filter(item => battleItemPocket(item) === 'items'));
      renderBattlePocketList('battleItemsList', items, '&#1053;&#1077;&#1090; &#1073;&#1086;&#1077;&#1074;&#1099;&#1093; &#1087;&#1088;&#1077;&#1076;&#1084;&#1077;&#1090;&#1086;&#1074;.');
      renderBattlePocketList('battleBallsList', balls, battleState.mode === 'pvp' ? 'Ловля недоступна в PvP-бою.' : '&#1053;&#1077;&#1090; &#1087;&#1086;&#1082;&#1077;&#1073;&#1086;&#1083;&#1086;&#1074;.');
    }

    function renderBattlePocketToolbar(items) {
      const toolbar = document.getElementById('battleItemCategoryTabs');
      if (!toolbar) return;
      const counts = new Map();
      (items || []).forEach(item => {
        const key = String(item.battle_category || 'utility');
        counts.set(key, (counts.get(key) || 0) + 1);
      });
      const order = ['all', 'healing', 'status', 'revive', 'buffs', 'utility'];
      toolbar.innerHTML = '';
      for (const key of order) {
        const count = key === 'all' ? (items || []).length : (counts.get(key) || 0);
        if (key !== 'all' && count <= 0) continue;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'battle-pocket-chip' + (battlePocket.activeItemCategory === key ? ' is-active' : '');
        button.dataset.battleItemCategory = key;
        button.textContent = battlePocketCategoryLabel(key) + ' ' + count;
        button.addEventListener('click', () => {
          battlePocket.activeItemCategory = key;
          if (window.console && typeof window.console.debug === 'function') {
            console.debug('[UI] Battle inventory category switched to ' + key);
          }
          renderBattlePocketLists();
        });
        toolbar.appendChild(button);
      }
    }

    function battlePocketCategoryLabel(key) {
      return {
        all: 'Все',
        healing: 'Лечение',
        status: 'Статусы',
        revive: 'Revive',
        buffs: 'Баффы',
        utility: 'Разное',
      }[key] || 'Разное';
    }

    function renderBattlePocketList(id, items, emptyText) {
      const list = document.getElementById(id);
      if (!list) return;
      list.innerHTML = '';
      if (!items.length) {
        list.innerHTML = '<div class="battle-empty">' + emptyText + '</div>';
        return;
      }
      for (const item of items) {
        const row = document.createElement('button');
        row.type = 'button';
        row.className = 'battle-item-row' + (id === 'battleBallsList' ? ' is-ball' : '');
        row.innerHTML = '<img alt=""><span><b></b><small></small></span><i aria-hidden="true"></i>';
        const rowImg = row.querySelector('img');
        setItemIcon(rowImg, item);
        row.querySelector('b').textContent = item.name || item.tittle || ('Item #' + Number(item.item_id || 0));
        const meta = id === 'battleBallsList'
          ? ('x' + Number(item.count || 0) + ' • Catch Rate x' + Number(item.catch_modifier || 1))
          : (String(item.battle_category_label || 'Предмет') + ' • x' + Number(item.count || 0));
        row.querySelector('small').textContent = meta;
        row.querySelector('i').textContent = 'x' + Number(item.count || 0);
        row.addEventListener('click', () => {
          const action = id === 'battleBallsList' ? 'ball' : 'item';
          if (action === 'ball' && battleState.mode === 'pvp') {
            setStatus('Покеболы нельзя использовать в PvP-бою.', true);
            return;
          }
          if (window.console && typeof window.console.debug === 'function') {
            console.debug('[ITEM_USE] ' + (item.name || item.tittle || ('Item #' + Number(item.item_id || 0))) + ' used');
          }
          battleAction(action, { item_user_id: item.id });
        });
        list.appendChild(row);
      }
      if (list.dataset.scrollReady !== '1') {
        list.dataset.scrollReady = '1';
        list.addEventListener('scroll', () => {
          if (window.console && typeof window.console.debug === 'function') {
            console.debug('[SCROLL] Inventory scroll position updated');
          }
        }, { passive: true });
      }
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

    function isBattleBall(item) {
      const id = Number(item.item_id || 0);
      const name = String((item.name || '') + ' ' + (item.tittle || '')).toLowerCase();
      return id === 3
        || id === 90004
        || id === 90005
        || name.includes('ball')
        || name.includes('покеб')
        || name.includes('ультрабол')
        || name.includes('мастербол')
        || name.includes('премиум бол')
        || name.includes('шайнибол')
        || /\bшар\b/u.test(name)
        || name.includes('ultra')
        || name.includes('master');
    }

    function battleItemPocket(item) {
      if (item && (item.battle_pocket === 'balls' || item.battle_pocket === 'items')) {
        return item.battle_pocket;
      }
      return isBattleBall(item) ? 'balls' : 'items';
    }

    function pad3(num) {
      return String(Math.max(0, Number(num || 0))).padStart(3, '0');
    }

    function setSpriteWithFallback(imgEl, candidates) {
      const urls = candidates.filter(Boolean);
      let idx = 0;
      const tryNext = () => {
        if (idx >= urls.length) {
          imgEl.src = '/img/blank.gif';
          return;
        }
        imgEl.src = urls[idx++];
      };
      imgEl.onload = () => { imgEl.style.visibility = 'visible'; };
      imgEl.onerror = tryNext;
      imgEl.style.visibility = 'hidden';
      tryNext();
    }

    function spriteNumberCandidates(base) {
      const num = Number(base || 0);
      const plain = String(num);
      const padded = pad3(num);
      return plain === padded ? [padded] : [padded, plain];
    }

    function spriteCandidates(folder, base, extensions = ['gif']) {
      const urls = [];
      for (const name of spriteNumberCandidates(base)) {
        for (const ext of extensions) {
          urls.push('/Pok/' + folder + '/' + name + '.' + ext);
        }
      }
      return urls;
    }

    function isShinyBattlePokemon(pokemon) {
      const joined = [pokemon && pokemon.name, pokemon && pokemon.tips, pokemon && pokemon.status]
        .map(v => String(v || '').toLowerCase())
        .join(' ');
      return joined.includes('shiny') || joined.includes('pokesshiny') || joined.includes('shine');
    }

    function spriteCandidatesAny(folder, base, extensions = ['gif', 'png', 'jpg']) {
      return spriteCandidates(folder, base, extensions);
    }

    function spritePngCandidates(folder, base) {
      const num = Number(base || 0);
      const plain = String(num);
      const padded = pad3(num);
      const names = plain === padded ? [plain] : [plain, padded];
      return names.map(name => '/Pok/' + folder + '/' + name + '.png');
    }

    function spriteGifCandidates(folder, base, preferPadded = false) {
      const num = Number(base || 0);
      const plain = String(num);
      const padded = pad3(num);
      const names = plain === padded
        ? [plain]
        : (preferPadded ? [padded, plain] : [plain, padded]);
      return names.map(name => '/Pok/' + folder + '/' + name + '.gif');
    }

    function pokemonBaseNum(pokemon) {
      return Number(
        pokemon && (
          pokemon.baseNum
          || pokemon.base_num
          || pokemon.basenum
          || pokemon.num
          || pokemon.number
          || pokemon.code
          || pokemon.pokedexId
        ) || 0
      );
    }

    function pokemonSpriteUrls(pokemon, keys = []) {
      const result = [];
      if (!pokemon || typeof pokemon !== 'object') return result;
      for (const key of keys) {
        if (pokemon[key]) result.push(String(pokemon[key]));
      }
      if (pokemon.sprites && typeof pokemon.sprites === 'object') {
        for (const key of keys) {
          if (pokemon.sprites[key]) result.push(String(pokemon.sprites[key]));
        }
        for (const key of ['backShiny', 'frontShiny', 'back', 'sback', 'front', 'normal', 'shiny', 'sprite']) {
          if (pokemon.sprites[key]) result.push(String(pokemon.sprites[key]));
        }
      }
      return result;
    }

    function renderBattleSprites(player, enemy) {
      const playerBase = pokemonBaseNum(player);
      const enemyBase = pokemonBaseNum(enemy);
      const playerImg = document.getElementById('battlePlayerSpriteImg');
      const enemyImg = document.getElementById('battleEnemySpriteImg');

      playerImg.alt = player.name || 'Ваш покемон';
      enemyImg.alt = enemy.name || 'Дикий покемон';

      const playerIsShiny = isShinyBattlePokemon(player);
      const enemyIsShiny = isShinyBattlePokemon(enemy);

      // Back sprite for user's pokemon. Try shiny back first when name/tips says Shiny,
      // then normal back, then front/anim fallbacks so the pokemon never disappears.
      const playerCandidates = pokemonSpriteUrls(
        player,
        playerIsShiny
          ? ['backShiny', 'sback', 'backSprite', 'spriteBack', 'back', 'sprite']
          : ['backSprite', 'spriteBack', 'back', 'sprite']
      );
      if (playerIsShiny) {
        playerCandidates.push(...spriteCandidatesAny('sback', playerBase));
        playerCandidates.push(...spriteCandidatesAny('Sback', playerBase));
      }
      playerCandidates.push(...spriteCandidatesAny('back', playerBase));
      if (playerIsShiny) {
        playerCandidates.push(...spriteCandidatesAny('shiny', playerBase));
        playerCandidates.push(...spriteCandidatesAny('shine', playerBase));
      }
      playerCandidates.push(...spriteCandidatesAny('anim', playerBase));
      playerCandidates.push(...spriteCandidatesAny('pok', playerBase));
      playerCandidates.push(...spriteCandidatesAny('normal', playerBase));

      // Front sprite for wild pokemon: prefer animated battle GIFs, keep PNG as fallback.
      const enemyCandidates = pokemonSpriteUrls(
        enemy,
        enemyIsShiny
          ? ['frontShiny', 'shiny', 'frontSprite', 'spriteFront', 'sprite', 'front', 'normal']
          : ['frontSprite', 'spriteFront', 'sprite', 'front', 'normal']
      );
      if (enemyIsShiny) {
        enemyCandidates.push(...spriteGifCandidates('shiny', enemyBase, true));
        enemyCandidates.push(...spritePngCandidates('shine', enemyBase));
      }
      enemyCandidates.push(...spriteGifCandidates('spriteanim', enemyBase));
      enemyCandidates.push(...spriteGifCandidates('pok', enemyBase, true));
      enemyCandidates.push(...spriteGifCandidates('anim', enemyBase));
      enemyCandidates.push(...spritePngCandidates('normal', enemyBase));

      setSpriteWithFallback(playerImg, playerCandidates);
      setSpriteWithFallback(enemyImg, enemyCandidates);
    }

    function renderBattleSpritesFromDebug(debug) {
      const playerTag = String(debug && debug.poke_1 ? debug.poke_1 : '');
      const enemyTag = String(debug && debug.poke_2 ? debug.poke_2 : '');
      const playerId = Number((playerTag.match(/_(\d+)$/) || [])[1] || 0);
      const enemyId = Number((enemyTag.match(/_(\d+)$/) || [])[1] || debug && debug.user_2 || 0);

      const playerImg = document.getElementById('battlePlayerSpriteImg');
      const enemyImg = document.getElementById('battleEnemySpriteImg');

      setSpriteWithFallback(playerImg, [
        '/Pok/sback/' + pad3(playerId) + '.gif',
        '/Pok/sback/' + playerId + '.gif',
        '/Pok/back/' + pad3(playerId) + '.gif',
        '/Pok/back/' + playerId + '.gif',
        '/Pok/normal/' + playerId + '.png',
        '/Pok/anim/' + pad3(playerId) + '.gif',
        '/Pok/anim/' + playerId + '.gif',
        '/Pok/back/001.gif',
      ]);
      setSpriteWithFallback(enemyImg, [
        '/Pok/spriteanim/' + enemyId + '.gif',
        '/Pok/pok/' + pad3(enemyId) + '.gif',
        '/Pok/pok/' + enemyId + '.gif',
        '/Pok/anim/' + pad3(enemyId) + '.gif',
        '/Pok/anim/' + enemyId + '.gif',
        '/Pok/normal/' + enemyId + '.png',
        '/Pok/normal/' + pad3(enemyId) + '.png',
        '/Pok/anim/001.gif',
      ]);
    }

    async function loadBattleState() {
      try {
        const response = await fetch('/api/battle/pve/state', { credentials: 'same-origin' });
        const payload = await response.json();
        renderBattle(payload);
      } catch (error) {
        setStatus('Не удалось обновить боевое состояние.', true);
      }
    }

    async function battleAction(action, extra = {}) {
      if (action === 'ball' && battleState.mode === 'pvp') {
        setStatus('Покеболы нельзя использовать в PvP-бою.', true);
        return;
      }
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('action', action);
      Object.entries(extra).forEach(([key, value]) => body.set(key, String(value)));

      try {
        const response = await fetch('/api/battle/pve/action', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus(payload && payload.message ? payload.message : 'Боевое действие отклонено.', true);
        }
        const pocketAction = action === 'item' || action === 'ball';
        if (pocketAction) {
          battlePocket.loaded = false;
        }
        renderBattle(payload);
        const activeBattleTab = document.querySelector('[data-battle-tab].is-active')?.dataset.battleTab || '';
        if (pocketAction && !payload.finished && (activeBattleTab === 'items' || activeBattleTab === 'balls')) {
          loadBattlePocket();
        }
      } catch (error) {
        setStatus('Боевое действие не выполнено.', true);
      }
    }

    async function acknowledgeBattleEnd() {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      try {
        await fetch('/api/battle/pve/ack-end', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
      } catch (e) {
        // ignore
      }
      closeBattleOverlay();
      document.getElementById('battleFinishBox').hidden = true;
      loadState();
    }

    window.PokemonBattle = window.PokemonBattle || {};
    window.PokemonBattle.loadState = loadState;
    window.PokemonBattle.loadBattleState = loadBattleState;
    document.addEventListener('pvp-battle-started', () => {
      openBattleOverlay();
      loadBattleState();
      loadState();
    });

    function renderInventoryGrid(sourceItems = null) {
      const grid = document.getElementById('invGrid');
      const items = (sourceItems || inventory.items).slice(0, 60);
      grid.innerHTML = '';
      renderInventoryCategories();

      for (let i = 0; i < 60; i++) {
        const item = items[i] || null;
        const slot = document.createElement('button');
        slot.type = 'button';
        slot.className = 'inv-slot' + (item ? '' : ' empty');

        if (item) {
          slot.innerHTML = '<img alt=""><span class="cnt"></span><span class="item-id"></span>';
          const icon = slot.querySelector('img');
          setItemIcon(icon, item);
          slot.querySelector('.cnt').textContent = Number(item.count || 0).toLocaleString('ru-RU');
          slot.querySelector('.item-id').textContent = '#' + Number(item.item_id || 0);
          if (item.target_use && item.target_use.enabled) {
            slot.classList.add('targetable');
          }
          if (inventory.selected && Number(inventory.selected.id) === Number(item.id)) {
            slot.classList.add('selected');
          }
          slot.addEventListener('click', () => {
            inventory.selected = item;
            renderInventoryGrid();
            renderInventoryTargetControls(false);
          });
          slot.addEventListener('mouseenter', (event) => showItemTooltip(event, item));
          slot.addEventListener('mousemove', moveItemTooltip);
          slot.addEventListener('mouseleave', hideItemTooltip);
        } else {
          slot.innerHTML = '<img alt="" src="/img/blank.gif">';
          slot.disabled = true;
        }
        grid.appendChild(slot);
      }

      document.getElementById('invSlotsCount').textContent = String(items.length);
      document.getElementById('invPageInfo').textContent = inventory.page + '/' + inventory.pages;
      renderInventoryTargetControls();
    }

    function renderInventoryCategories() {
      const box = document.getElementById('invCategories');
      if (!box) return;
      const categories = inventory.categories && inventory.categories.length ? inventory.categories : [
        { key: '', label: 'Все' },
        { key: 'balls', label: 'Покеболы' },
        { key: 'tm', label: 'ТМ' },
        { key: 'eggs', label: 'Яйца' },
        { key: 'evolution', label: 'Эволюция' },
        { key: 'consumables', label: 'Расходники' },
        { key: 'quest', label: 'Квестовые' },
        { key: 'drop', label: 'Дроп' },
      ];
      box.innerHTML = '';
      for (const category of categories) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'inv-category' + (String(category.key || '') === String(inventory.category || '') ? ' is-active' : '');
        button.dataset.category = String(category.key || '');
        button.textContent = String(category.label || 'Категория');
        box.appendChild(button);
      }
    }

    function selectedItemTargetRule() {
      const item = inventory.selected;
      if (!item || !item.target_use || item.target_use.enabled !== true) return null;
      const targetType = String(item.target_use.target_type || 'pokemon');
      if (targetType !== 'pokemon' && !isFlightTargetRule(item.target_use) && !isGiftTargetRule(item.target_use)) return null;
      return item.target_use;
    }

    function isGiftTargetRule(rule) {
      if (!rule) return false;
      const targetType = String(rule.target_type || 'pokemon');
      const effectKey = String(rule.effect_key || '');
      return targetType === 'gift' || effectKey === 'open_gift';
    }

    function isFlightTargetRule(rule) {
      if (!rule) return false;
      const targetType = String(rule.target_type || 'pokemon');
      const effectKey = String(rule.effect_key || '');
      return targetType === 'flight' || targetType === 'transport_flight' || effectKey === 'plane_ticket';
    }

    function renderInventoryTargetControls(keepPanel = true) {
      const useButton = document.getElementById('invUseTargetBtn');
      const panel = document.getElementById('invTargetPanel');
      const rule = selectedItemTargetRule();
      useButton.disabled = !rule;

      if (!rule) {
        panel.hidden = true;
        return;
      }

      document.getElementById('invTargetTitle').textContent = rule.title || 'Применить предмет';
      document.getElementById('invTargetHint').textContent = rule.hint || 'Выберите покемона для применения предмета.';
      const countInput = document.getElementById('invTargetCount');
      const select = document.getElementById('invTargetPokemon');
      const previous = select.value;
      select.innerHTML = '';

      if (isGiftTargetRule(rule)) {
        countInput.value = '1';
        countInput.disabled = true;
        countInput.hidden = true;
        select.hidden = true;
        select.innerHTML = '<option value="1">Открыть подарок</option>';
        document.getElementById('invTargetApplyBtn').disabled = false;
      } else if (isFlightTargetRule(rule)) {
        countInput.value = '1';
        countInput.disabled = true;
        countInput.hidden = true;
        select.hidden = false;
        select.setAttribute('aria-label', 'Рейс самолёта');
        if (inventory.flightRoutesLoading) {
          const option = document.createElement('option');
          option.value = '';
          option.textContent = 'Загрузка рейсов...';
          select.appendChild(option);
        } else if (!inventory.flightRoutes.length) {
          const option = document.createElement('option');
          option.value = '';
          option.textContent = inventory.flightRoutesLoaded ? 'Нет доступных рейсов' : 'Откройте список рейсов';
          select.appendChild(option);
        } else {
          for (const route of inventory.flightRoutes) {
            const option = document.createElement('option');
            option.value = String(route.id || 0);
            option.textContent = String(route.title || 'Рейс') + ' · ' + String(route.durationText || '15 мин.');
            option.title = String(route.description || '');
            select.appendChild(option);
          }
        }
        if (previous) select.value = previous;
        if (!select.value && select.options.length > 0) select.selectedIndex = 0;
        document.getElementById('invTargetApplyBtn').disabled = !Number(select.value || 0);
      } else {
        countInput.hidden = false;
        select.hidden = false;
        const owned = Number(inventory.selected.count || 1);
        const min = Math.max(1, Number(rule.min_count || 1));
        const max = Math.max(min, Math.min(owned, Number(rule.max_count || owned)));
        countInput.min = String(min);
        countInput.max = String(max);
        countInput.value = String(rule.allow_quantity ? Math.min(max, Math.max(min, Number(countInput.value || min))) : 1);
        countInput.disabled = !rule.allow_quantity;
        select.setAttribute('aria-label', 'Покемон');
        for (const pokemon of inventory.pokemon || []) {
          const option = document.createElement('option');
          option.value = String(pokemon.id || 0);
          option.textContent = String(pokemon.names || 'Покемон').replace(/<[^>]*>/g, '');
          select.appendChild(option);
        }
        if (previous) select.value = previous;
        if (!select.value && select.options.length > 0) select.selectedIndex = 0;
        document.getElementById('invTargetApplyBtn').disabled = !Number(select.value || 0);
      }

      if (!keepPanel) {
        panel.hidden = true;
      }
    }

    async function loadFlightRoutes() {
      if (inventory.flightRoutesLoading) return;
      inventory.flightRoutesLoading = true;
      renderInventoryTargetControls();
      try {
        const response = await fetch('/api/transport/flight-routes', { credentials: 'same-origin' });
        const payload = await response.json();
        inventory.flightRoutes = payload && payload.ok === true && Array.isArray(payload.routes) ? payload.routes : [];
        inventory.flightRoutesLoaded = true;
      } catch (error) {
        inventory.flightRoutes = [];
        inventory.flightRoutesLoaded = true;
      } finally {
        inventory.flightRoutesLoading = false;
        renderInventoryTargetControls();
      }
    }

    async function openInventoryTargetPanel() {
      const rule = selectedItemTargetRule();
      if (!rule) return;
      if (isFlightTargetRule(rule) && !inventory.flightRoutesLoaded) {
        await loadFlightRoutes();
      }
      renderInventoryTargetControls();
      document.getElementById('invTargetPanel').hidden = false;
      hideItemTooltip();
    }

    async function applyInventoryTargetItem() {
      const rule = selectedItemTargetRule();
      if (!rule || !inventory.selected) return;
      const selectedTargetId = Number(document.getElementById('invTargetPokemon').value || 0);
      const count = Number(document.getElementById('invTargetCount').value || 1);
      if (!isGiftTargetRule(rule) && selectedTargetId <= 0) {
        setStatus(isFlightTargetRule(rule) ? 'Выберите рейс.' : 'Выберите покемона.');
        return;
      }

      if (isGiftTargetRule(rule)) {
        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('item_user_id', String(inventory.selected.id || 0));

        try {
          const response = await fetch('/api/inventory/open-gift', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body,
          });
          const payload = await response.json();
          setStatus(payload && payload.message ? payload.message : 'Готово.', !(payload && payload.ok === true));
          if (payload && payload.ok === true) {
            document.getElementById('invTargetPanel').hidden = true;
            loadInventoryPage(inventory.page);
          }
        } catch (e) {
          setStatus('Не удалось открыть подарок.', true);
        }
        return;
      }

      if (isFlightTargetRule(rule)) {
        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('item_user_id', String(inventory.selected.id || 0));
        body.set('route_id', String(selectedTargetId));

        try {
          const response = await fetch('/api/transport/flight-start', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body,
          });
          const payload = await response.json();
          setStatus(payload && payload.message ? payload.message : 'Готово.', !(payload && payload.ok === true));
          if (payload && payload.ok === true) {
            document.getElementById('invTargetPanel').hidden = true;
            closeInventory();
            await loadInventoryPage(inventory.page);
            await loadState();
          }
        } catch (e) {
          setStatus('Не удалось начать перелёт.', true);
        }
        return;
      }

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('item_user_id', String(inventory.selected.id || 0));
      body.set('pokemon_id', String(selectedTargetId));
      body.set('count', String(count));

      try {
        const response = await fetch('/api/inventory/use-target', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        const payload = await response.json();
        setStatus(payload && payload.message ? payload.message : 'Готово.');
        if (payload && payload.ok === true) {
          document.getElementById('invTargetPanel').hidden = true;
          loadInventoryPage(inventory.page);
        }
      } catch (e) {
        setStatus('Не удалось применить предмет.');
      }
    }

    async function loadInventoryPage(page = 1) {
      try {
        const params = new URLSearchParams();
        params.set('page', String(page));
        if (inventory.category) params.set('category', inventory.category);
        if (inventory.query) params.set('q', inventory.query);
        const response = await fetch('/api/inventory/page?' + params.toString(), { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          return;
        }
        inventory.page = Number(payload.page || 1);
        inventory.pages = Number(payload.pages || 1);
        inventory.category = String(payload.category || inventory.category || '');
        inventory.query = String(payload.query || inventory.query || '');
        inventory.categories = Array.isArray(payload.categories) ? payload.categories : inventory.categories;
        inventory.items = Array.isArray(payload.items) ? payload.items : [];
        inventory.pokemon = Array.isArray(payload.pokemon) ? payload.pokemon : [];
        inventory.selected = null;
        document.getElementById('invTargetPanel').hidden = true;
        renderInventoryGrid();
      } catch (e) {
        // ignore network error for now
      }
    }

    function showItemTooltip(event, item) {
      const tooltip = document.getElementById('invTooltip');
      const name = String(item.name || 'Без названия');
      const count = Number(item.count || 0).toLocaleString('ru-RU');
      const id = Number(item.item_id || 0);
      tooltip.innerHTML = '<strong>' + name + '</strong><br>ID: ' + id + ' / Кол-во: ' + count;
      tooltip.style.display = 'block';
      moveItemTooltip(event);
    }

    function moveItemTooltip(event) {
      const tooltip = document.getElementById('invTooltip');
      if (tooltip.style.display !== 'block') return;
      tooltip.style.left = (event.clientX + 14) + 'px';
      tooltip.style.top = (event.clientY + 14) + 'px';
    }

    function hideItemTooltip() {
      document.getElementById('invTooltip').style.display = 'none';
    }

    function renderFlightDialog(title, text) {
      renderNpcDialog({
        ok: true,
        npc: {
          title,
          text,
          choices: [{ label: 'Закрыть', close: true }],
        },
      });
    }

    async function showFlightStatus() {
      try {
        setStatus('Проводник...');
        const response = await fetch('/api/transport/flight-status', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          setStatus(payload && payload.message ? payload.message : 'Проводник не отвечает.', true);
          return;
        }
        const flight = payload.flight || {};
        const lines = [
          flight.statusText || 'Самолёт в пути.',
          flight.description || '',
        ].filter(Boolean);
        renderFlightDialog('Проводник', lines.join('\n'));
      } catch (error) {
        setStatus('Проводник не отвечает.', true);
      }
    }

    async function exitFlight() {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);

      try {
        setStatus('Выход...');
        const response = await fetch('/api/transport/flight-exit', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        const payload = await response.json();
        if (!payload || payload.ok !== true) {
          const flight = payload && payload.flight ? payload.flight : null;
          const text = payload && payload.message ? payload.message : 'Мы ещё летим.';
          renderFlightDialog('Выход', flight && flight.remainingText ? text + '\nДо прибытия осталось ' + flight.remainingText + '.' : text);
          setStatus(text, true);
          return;
        }
        document.getElementById('npcPanel').className = 'npc-panel';
        document.getElementById('npcPanel').innerHTML = '';
        setStatus(payload.message || 'Самолёт прибыл.');
        await loadState();
      } catch (error) {
        setStatus('Не удалось выйти из самолёта.', true);
      }
    }

    async function openNpc(npc, overrideParams = null) {
      const npcParams = overrideParams || (npc && npc.params) || {};
      if (String(npcParams.npc || '') === 'flight_status') {
        await showFlightStatus();
        return;
      }
      if (String(npcParams.npc || '') === 'flight_exit') {
        await exitFlight();
        return;
      }

      const route = npcRoute(npc, overrideParams);
      if (route) {
        openNpcRoute(route);
        return;
      }

      state.activeNpc = { ...npc, params: overrideParams || npc.params || {} };
      const params = new URLSearchParams();
      params.set('location_id', state.locationId);
      for (const [key, value] of Object.entries(overrideParams || npc.params || {})) {
        params.set(key, value);
      }

      try {
        setStatus('NPC...');
        const response = await fetch('/api/location/npc?' + params.toString(), { credentials: 'same-origin' });
        renderNpcDialog(await response.json());
      } catch (error) {
        setStatus('NPC не отвечает.', true);
      }
    }

    function npcRoute(npc, overrideParams = null) {
      const params = overrideParams || (npc && npc.params) || {};
      const title = String(npc && npc.title ? npc.title : '').toLowerCase();
      const icon = String(npc && npc.icon ? npc.icon : '').toLowerCase();

      if (String(params.npc || '') === '2' && (title.includes('покемаркет') || icon === 'shop')) {
        return '/game/market/items';
      }

      return null;
    }

    function openNpcRoute(route) {
      if (route === '/game/market/items' && window.GameMarketOverlay && typeof window.GameMarketOverlay.open === 'function') {
        window.GameMarketOverlay.open();
        return;
      }
      window.location.href = route;
    }

    async function runNpcAction(action) {
      if (!state.activeNpc) return;

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('location_id', state.locationId);
      body.set('action', action);
      for (const [key, value] of Object.entries(state.activeNpc.params || {})) {
        body.set(key, value);
      }

      try {
        setStatus('NPC...');
        const response = await fetch('/api/location/npc/action', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        renderNpcDialog(await response.json());
      } catch (error) {
        setStatus('Действие NPC не выполнено.', true);
      }
    }

    function renderNpcDialog(payload) {
      if (!payload || payload.ok !== true) {
        setStatus(payload && payload.message ? payload.message : 'NPC не отвечает.', true);
        return;
      }

      const npc = payload.npc;
      const panel = document.getElementById('npcPanel');
      panel.className = 'npc-panel is-open';
      panel.innerHTML = '';

      const title = document.createElement('h2');
      title.textContent = npc.title;
      const body = document.createElement('p');
      body.textContent = npc.text;

      panel.appendChild(title);
      panel.appendChild(body);

      const choices = document.createElement('div');
      choices.className = 'npc-strip';
      for (const choice of npc.choices || []) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'npc-btn';
        button.textContent = choice.label || '...';
        if (choice.hint) {
          button.title = choice.hint;
        }
        button.disabled = !!choice.disabled;
        if (choice.close) {
          button.addEventListener('click', () => {
            closeNpcPanel();
            setStatus('Готово');
          });
        } else if (choice.action) {
          button.addEventListener('click', () => runNpcAction(choice.action));
        } else if (choice.route) {
          button.addEventListener('click', () => openNpcRoute(choice.route));
        } else if (choice.params) {
          button.addEventListener('click', () => openNpc(state.activeNpc, choice.params));
        }
        choices.appendChild(button);
      }
      if (choices.children.length) {
        panel.appendChild(choices);
      }

      setStatus('NPC: ' + npc.title);
    }

    async function loadState() {
      setStatus('Загрузка...');
      const response = await fetch('/api/game/state', { credentials: 'same-origin' });
      render(await response.json());
    }

    async function moveTo(locationId) {
      if (state.busy) return;
      state.busy = true;
      setStatus('Переход...');
      document.querySelectorAll('.move-btn').forEach(button => button.disabled = true);

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('location_id', locationId);

      try {
        const response = await fetch('/api/map/move', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body
        });
        render(await response.json());
      } catch (error) {
        setStatus('Переход не выполнен.', true);
      } finally {
        state.busy = false;
        document.querySelectorAll('.move-btn').forEach(button => button.disabled = false);
      }
    }

    async function startBossBattle(bossId) {
      if (!bossId || state.busy) return;
      state.busy = true;
      setStatus('Босс выходит на бой...');
      document.querySelectorAll('.boss-btn').forEach(button => button.disabled = true);

      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('boss_id', String(bossId));

      try {
        const response = await fetch('/api/bosses/start', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        const payload = await response.json();
        if (!payload.ok) {
          setStatus(payload.message || 'Не удалось начать бой с боссом.', true);
          return;
        }
        setStatus(payload.message || 'Босс выходит на бой!');
        openBattleOverlay();
        loadBattleState();
      } catch (error) {
        setStatus('Не удалось начать бой с боссом.', true);
      } finally {
        state.busy = false;
        document.querySelectorAll('.boss-btn').forEach(button => button.disabled = false);
      }
    }

    async function togglePveButton() {
      try {
        setStatus('Переключаем нападение...');
        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('mode', state.pveButton ? 'off' : 'on');
        const response = await fetch('/api/game/pve-mode', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body,
        });
        render(await response.json());
      } catch (error) {
        setStatus('Не удалось переключить режим нападения.', true);
      }
    }

    document.getElementById('pveButton').addEventListener('click', togglePveButton);
    document.getElementById('bugReportBtn')?.addEventListener('click', openBugReportModal);
    document.getElementById('bugReportCloseBtn')?.addEventListener('click', closeBugReportModal);
    document.getElementById('bugReportCancelBtn')?.addEventListener('click', closeBugReportModal);
    document.getElementById('bugReportSubmitBtn')?.addEventListener('click', submitBugReport);
    document.getElementById('bugReportOverlay')?.addEventListener('click', event => {
      if (event.target.id === 'bugReportOverlay') {
        closeBugReportModal();
      }
    });
    const debugForceBattleBtn = document.getElementById('debugForceBattleBtn');
    if (debugForceBattleBtn) {
      debugForceBattleBtn.addEventListener('click', async () => {
        try {
          setStatus('DEBUG: запускаем бой...');
          const body = new URLSearchParams();
          body.set('_csrf', csrf);
          const response = await fetch('/api/battle/pve/force', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body
          });
          render(await response.json());
        } catch (e) {
          setStatus('DEBUG: не удалось запустить бой.', true);
        }
      });
    }

    document.getElementById('inventoryLink').addEventListener('click', event => {
      event.preventDefault();
      openInventory();
    });
    document.getElementById('pokemonLink').addEventListener('click', event => {
      event.preventDefault();
      openPokemonModal();
    });
    document.getElementById('pokemonCloseBtn').addEventListener('click', closePokemonModal);
    document.getElementById('pokemonOverlay').addEventListener('click', event => {
      if (event.target.id === 'pokemonOverlay') {
        closePokemonModal();
      }
    });
    document.getElementById('invCloseBtn').addEventListener('click', closeInventory);
    document.getElementById('invRefreshBtn').addEventListener('click', () => loadInventoryPage(inventory.page));
    document.getElementById('invPrevBtn').addEventListener('click', () => loadInventoryPage(Math.max(1, inventory.page - 1)));
    document.getElementById('invNextBtn').addEventListener('click', () => loadInventoryPage(Math.min(inventory.pages, inventory.page + 1)));
    document.getElementById('invUseTargetBtn').addEventListener('click', openInventoryTargetPanel);
    document.getElementById('invTargetApplyBtn').addEventListener('click', applyInventoryTargetItem);
    document.getElementById('invTargetPokemon').addEventListener('change', () => renderInventoryTargetControls());
    document.getElementById('invTargetCancelBtn').addEventListener('click', () => {
      document.getElementById('invTargetPanel').hidden = true;
    });
    document.getElementById('inventoryOverlay').addEventListener('click', event => {
      if (event.target.id === 'inventoryOverlay') {
        closeInventory();
      }
    });
    document.getElementById('inventoryOverlay').addEventListener('mouseleave', hideItemTooltip);
    document.getElementById('invCategories').addEventListener('click', event => {
      const button = event.target.closest('[data-category]');
      if (!button) return;
      inventory.category = String(button.dataset.category || '');
      loadInventoryPage(1);
    });
    let inventorySearchTimer = 0;
    document.getElementById('invSearchInput').addEventListener('input', event => {
      inventory.query = String(event.target.value || '').trim();
      window.clearTimeout(inventorySearchTimer);
      inventorySearchTimer = window.setTimeout(() => loadInventoryPage(1), 220);
    });

    for (let i = 1; i <= 4; i++) {
      const moveButton = document.getElementById('battleMove' + i);
      moveButton.addEventListener('click', event => {
        const moveId = Number(event.currentTarget.dataset.moveId || 0);
        if (moveId > 0) {
          battleAction('attack', { move_id: moveId });
        }
      });
    }
    const battleMoves = document.querySelector('.battle-dock-moves');
    if (battleMoves) {
      battleMoves.addEventListener('mouseover', event => {
        const icon = event.target.closest('.battle-move-icon');
        if (!icon || !battleMoves.contains(icon)) return;
        const button = icon.closest('.battle-move-btn');
        const idx = Number(button && button.dataset.moveIndex || 0);
        showBattleMoveTooltip(event, battleState.moves[idx]);
      });
      battleMoves.addEventListener('mousemove', event => {
        if (!event.target.closest('.battle-move-icon')) return;
        moveBattleMoveTooltip(event);
      });
      battleMoves.addEventListener('mouseout', event => {
        const icon = event.target.closest('.battle-move-icon');
        if (!icon) return;
        if (event.relatedTarget && icon.contains(event.relatedTarget)) return;
        if (battleHoverState.movePinned) return;
        hideBattleMoveTooltip();
      });
    }
    document.getElementById('battleLog').addEventListener('click', event => {
      const button = event.target.closest('.battle-log-move');
      if (!button) return;
      event.preventDefault();
      event.stopPropagation();
      const idx = Number(button.dataset.moveIndex || -1);
      const rect = button.getBoundingClientRect();
      showBattleMoveTooltip({ clientX: rect.left, clientY: rect.bottom }, battleState.knownMoves[idx]);
      battleHoverState.movePinned = true;
    });
    document.addEventListener('click', event => {
      if (!battleHoverState.movePinned) return;
      if (event.target.closest('.battle-log-move')) return;
      if (event.target.closest('#battleMoveTooltip')) return;
      hideBattleMoveTooltip();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') hideBattleMoveTooltip();
      if (event.key === 'Escape') closeBugReportModal();
    });
    document.addEventListener('scroll', hideBattleMoveTooltip, true);
    document.addEventListener('mousemove', event => {
      if (!battleHoverState.movePinned) return;
      if (event.target.closest('#battleMoveTooltip') || event.target.closest('.battle-log-move')) return;
      hideBattleMoveTooltip();
    });
    document.getElementById('battleSwitchBtn').addEventListener('click', () => {
      const pokemonId = Number(document.getElementById('battleSwitchSelect').value || 0);
      if (pokemonId > 0) {
        battleAction('switch', { pokemon_id: pokemonId });
      }
    });
    document.getElementById('battleEscapeBtn').addEventListener('click', () => {
      battleAction('escape');
    });
    document.getElementById('battleDoneBtn').addEventListener('click', () => {
      acknowledgeBattleEnd();
    });
    document.getElementById('battleReviewClose').addEventListener('click', () => {
      acknowledgeBattleEnd();
    });

    loadState();
    fetchGameNotifications();
    setInterval(loadState, 5000);
    setInterval(fetchGameNotifications, 4000);
    setInterval(() => {
      if (battleState.active && !battleState.reviewing) {
        loadBattleState();
      }
    }, 2000);
  </script>
  <script src="/public/js/trainer-profile-window.js?v=20260527-social-polish4"></script>
  <script src="/public/js/chat.js?v=20260525-trainer-card-hover"></script>
  <script src="/public/js/player-menu.js?v=20260526-breeding-entry"></script>
  <script src="/public/js/dex-overlay.js?v=20260525-dex-filters"></script>
  <script src="/public/js/game-market-overlay.js"></script>
  <script src="/public/js/commission-market.js?v=20260527-my-lots"></script>

</body>
</html>
