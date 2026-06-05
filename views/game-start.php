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
  <link rel="stylesheet" href="/public/css/chat.css?v=20260527-split1">
  <link rel="stylesheet" href="/public/css/game-shell.css?v=20260603-quest-guide">
  <link rel="stylesheet" href="/public/css/game-battle-dock.css?v=20260527-battle-replay">
  <link rel="stylesheet" href="/public/css/player-menu.css?v=20260527-breeding-popup2">
  <link rel="stylesheet" href="/public/css/trainer-profile-window.css?v=20260605-card-appearance1">
  <link rel="stylesheet" href="/public/css/profile-settings-modal.css?v=20260603-profile-settings">
  <link rel="stylesheet" href="/public/css/game-market-overlay.css?v=20260528-fix2">
  <link rel="stylesheet" href="/public/css/commission-market.css?v=20260603-layout-fix">
  <link rel="stylesheet" href="/public/css/mail-overlay.css?v=20260603-game-mail3">
  <link rel="stylesheet" href="/public/css/quest-journal.css?v=20260603-quest-guide">
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

    <section class="quest-guide-banner glass-card" id="questGuideBanner" hidden aria-live="polite">
      <div class="quest-guide-copy">
        <b id="questGuideTitle">Маршрут к цели</b>
        <span id="questGuideText">Подсветка включена.</span>
      </div>
      <button type="button" id="questGuideCancelBtn">Отменить помощь</button>
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
        <a href="/game/quests" data-open-quests><img src="/public/img/ui/menu-quests.png" alt="">Квесты</a>
        <a href="/game/battle/pvp"><img src="/public/img/ui/menu-battle.png" alt="">Бои</a>
        <a href="/game/tournaments"><img src="/public/img/ui/menu-battle.png" alt="">Турниры</a>
        <a href="/game/messages" data-open-mail><img src="/public/img/ui/menu-mail.png" alt="">Почта</a>
      </div>
      <div class="system-status">
        <?php if (!empty($isAdmin)): ?>
          <a href="/game/admin" class="admin-entry" title="Админпанель" aria-label="Админпанель">Админ</a>
        <?php endif; ?>
        <div class="game-tools-menu" id="gameToolsMenu">
          <button type="button" class="settings-btn" id="gameToolsBtn" aria-label="Настройки" aria-haspopup="true" aria-expanded="false">⚙</button>
          <div class="game-tools-dropdown" id="gameToolsDropdown" role="menu" aria-hidden="true">
            <label class="game-tools-toggle" role="menuitem">
              <input type="checkbox" id="profileShowPartyToggle">
              <span>
                <b>Показывать команду</b>
                <em>в тренер-карте</em>
              </span>
            </label>
            <div class="game-tools-status" id="profilePrivacyStatus" aria-live="polite"></div>
            <button type="button" id="bugReportBtn" role="menuitem">
              <span class="game-tools-icon" aria-hidden="true">⚑</span>
              <span>Report bug</span>
            </button>
          </div>
        </div>
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
  <?php
    $mailMode = 'overlay';
    $mailRootId = 'mailOverlay';
    $prefillRecipient = '';
    require APP_ROOT . '/views/components/mail-panel.php';
  ?>
  <?php
    $questMode = 'overlay';
    $questRootId = 'questOverlay';
    require APP_ROOT . '/views/components/quest-journal-panel.php';
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

  <script type="application/json" id="gameRuntimeConfig">
    <?= json_encode(['itemIconIndex' => $itemIconIndex], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}' ?>
  </script>
  <script src="/public/js/game-start-runtime.js?v=20260603-quest-guide"></script>
  <script src="/public/js/trainer-profile-window.js?v=20260605-card-appearance1"></script>
  <script src="/public/js/profile-settings-modal.js?v=20260603-profile-settings"></script>
  <script src="/public/js/chat.js?v=20260603-mail-overlay"></script>
  <script src="/public/js/player-menu.js?v=20260603-profile-actions"></script>
  <script src="/public/js/game-toolbar.js?v=20260603-profile-privacy"></script>
  <script src="/public/js/dex-overlay.js?v=20260525-dex-filters"></script>
  <script src="/public/js/game-market-overlay.js?v=20260528-fix2"></script>
  <script src="/public/js/commission-market.js?v=20260527-my-lots"></script>
  <script src="/public/js/mail-overlay.js?v=20260603-game-mail3"></script>
  <script src="/public/js/quest-journal.js?v=20260603-quest-guide"></script>

</body>
</html>
