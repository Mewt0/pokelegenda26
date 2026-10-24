# TASK-036 — Ф5: подключить спрайт иконок к экранам

**Исполнитель:** Codex-агент · **Зона (явно разрешена):** `public/js/**`, `public/css/**`, `views/**`
**Приоритет:** P1 — последний шаг Ф5
**База:** гейт `quick` на момент выдачи — **24/24**. Не сломай его.

---

## Что уже есть

Спрайт и слой стилей готовы и проверены в Chrome, но **не подключены ни к одной странице**:
ни один из 34 классов `.icon--*` не используется, а `public/css/icons.css` не подключён нигде.

* `public/img/ui/icons.svg` — 34 `<symbol>` и 34 `<mask>`.
* `public/css/icons.css` — базовый `.icon` (24×24, `currentColor`, обводка 1.75) и 34 класса
  `.icon--NAME` через SVG-маску.

Использование: `<span class="icon icon--chat-send" aria-hidden="true"></span>`.
Цвет берётся из `currentColor`, размер задаётся классу `.icon` или переопределяется.

## Что нужно сделать

### 1. Подключить слой

Добавить `/public/css/icons.css` в `views/components/head-styles.php` в базовый список —
сразу после `components.css`. Слой доступен всем страницам, как `base.css`.

### 2. Заменить заглушки

Соответствие берётся из `VISUAL_ASSETS_TODO.md` (разделы «Нужны для…»). Сейчас на этих
местах текстовые символы, эмодзи или ничего.

**Чат** (`public/js/chat.js`):
* кнопка отправки — вместо текстовой стрелки → `icon--chat-send`;
* очистка чата → `icon--chat-clear`;
* автопрокрутка → `icon--chat-autoscroll-on` / `icon--chat-autoscroll-off` (переключаются вместе с состоянием, сейчас там `×` и `⇣` и PNG `/public/img/ui/chat/*.png`);
* вкладки `Общий` / `Торг` / `Бой` / `Клан` / `Приват` → `icon--chat-tab-*`;
* меню по клику на ник (инфо, ответ, приват, друг, игнор, почта) → `icon--chat-user-menu-*`.

**Нижняя панель и статус** (`views/game-start.php`, `public/js/game-start-runtime.js`):
кнопка `Нападение` → `icon--ui-pve-on` / `icon--ui-pve-off` по состоянию;
режим → `icon--ui-mode-general`; настройки → `icon--ui-settings`;
индикатор связи → `icon--ui-signal`; админская debug-кнопка → `icon--ui-debug-battle`.

**Локации, переходы, NPC** (`public/js/game-start-runtime.js`):
пин рядом с названием локации → `icon--location-pin`;
переходы → `icon--route-city`, `icon--route-forest`, `icon--route-road`, `icon--route-cave`, `icon--route-water` (по типу перехода, тип уже есть в данных);
NPC → `icon--npc-look`, `icon--npc-person`, `icon--npc-shop`, `icon--npc-quest`.

**Игроки** (`public/js/player-menu.js`, `public/js/trainer-profile-window.js`):
дефолтный аватар → `icon--player-avatar-default`;
онлайн/офлайн → `icon--player-online` / `icon--player-offline` (сейчас цветная точка).

### 3. Доступность — обязательно

* Иконка сама по себе не несёт текста, поэтому у неё **всегда** `aria-hidden="true"`.
* Если кнопка состояла только из иконки — у неё **обязан** быть `aria-label` (в проекте
  это уже делалось для кнопок чата и инвентаря, посмотри примеры и не потеряй имена при замене).
* Если у кнопки есть текст рядом — иконка чисто декоративная, менять ничего не нужно.
* Переключатели (автопрокрутка, `Нападение`) отдают состояние через `aria-pressed`, имя
  при этом не меняется.

## Границы

* Существующие PNG-иконки меню (`menu-pokemon.png`, `menu-inventory.png`, `menu-profile.png`,
  `menu-pokedex.png`, `menu-attackdex.png`, `menu-quests.png`, `menu-battle.png`,
  `menu-mail.png`) **не трогай** — они рабочие и в TODO помечены как уже подключённые.
* Ничего не удаляй: PNG-файлы, переставшие использоваться, оставь на месте.
* Никаких новых зависимостей и сборки. Только токены из `tokens.css` в правилах.
* Не переименовывай классы и `data-`атрибуты, на которые ссылается JS.

## Приёмка

1. `php -l` по изменённым `views/**`.
2. `node --check` по изменённым `public/js/**`.
3. **Полный гейт, вывод дословно:**
   `php tools/open_test_regression.php --profile=quick --login=Tacos --password=REDACTED_USE_SMOKE_PASSWORD` → нужно **24/24**.
4. Подтверди, что `icons.css` реально в head: приведи список `css=` со страницы `/game`.
5. Проверь, что ни один класс `.icon--*` не остался без применения, и приведи список
   тех, что ты подключил.

## Отчёт

Изменённые файлы, команды, их вывод, `git diff --stat`, риски, что осталось.
Отчёт: `docs/agent-tasks/TASK-036-icon-integration.report.md`.
Не коммить, не пушить.
