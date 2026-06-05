(() => {
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));

  const state = {
    mode: 'item'
  };

  function render(api) {
    const root = api.root;
    if (!root) return;

    root.innerHTML = `
      <section class="content-wizard">
        <header class="content-wizard-hero">
          <div>
            <p class="content-wizard-kicker">Простая рабочая админка</p>
            <h3>Мастер контента</h3>
            <p>Здесь собраны понятные сценарии: добавить предмет, подготовить квест, описать NPC и понять, куда класть картинки.</p>
          </div>
          <div class="content-wizard-note">
            <b>Правило</b>
            <span>Если действие ещё зависит от legacy-слоя, мастер покажет это явно и не даст сохранить “невидимый” контент.</span>
          </div>
        </header>

        <div class="content-wizard-grid">
          ${card('item', 'Предмет', 'Создать запись в items и привязать иконку из public/img/items.', 'Работает сейчас')}
          ${card('quest', 'Квест', 'Собрать quest_definitions, quest_steps и reward_json без ручного поиска таблиц.', 'Черновик')}
          ${card('npc', 'NPC', 'Описать NPC, локацию, действие и понять, где он подключается на карте.', 'Черновик')}
          ${card('assets', 'Картинки', 'Короткая карта папок: предметы, покемоны, аватары, локации.', 'Справочник')}
        </div>

        <section class="content-wizard-panel" data-content-wizard-body></section>
      </section>
    `;

    root.querySelectorAll('[data-content-mode]').forEach(button => {
      button.addEventListener('click', () => {
        state.mode = button.dataset.contentMode || 'item';
        renderBody(api);
      });
    });
    renderBody(api);
  }

  function card(mode, title, text, badge) {
    return `
      <button type="button" class="content-wizard-card" data-content-mode="${esc(mode)}">
        <span>${esc(badge)}</span>
        <b>${esc(title)}</b>
        <small>${esc(text)}</small>
      </button>
    `;
  }

  function renderBody(api) {
    const root = api.root;
    const body = root?.querySelector('[data-content-wizard-body]');
    if (!body) return;
    root.querySelectorAll('[data-content-mode]').forEach(button => {
      button.classList.toggle('is-active', button.dataset.contentMode === state.mode);
    });

    if (state.mode === 'quest') {
      body.innerHTML = questHtml();
      wireQuest(api);
    } else if (state.mode === 'npc') {
      body.innerHTML = npcHtml();
      wireNpc(api);
    } else if (state.mode === 'assets') {
      body.innerHTML = assetsHtml();
      wireAssets(api);
    } else {
      body.innerHTML = itemHtml();
      wireItem(api);
    }
  }

  function itemHtml() {
    return `
      <div class="content-wizard-columns">
        <form class="content-wizard-form" data-content-item-form>
          <h3>Новый предмет</h3>
          <p class="content-wizard-help">Предмет сохраняется сразу в таблицу <b>items</b>. Иконка берётся из <b>public/img/items</b>.</p>
          <div class="content-wizard-row">
            <label>ID предмета <input name="id" type="number" placeholder="Можно пустым"></label>
            <label>Файл иконки <input name="icon_file" type="text" placeholder="например 520.png"></label>
          </div>
          <label>Название RU <input name="name" required placeholder="Например: Защитные очки"></label>
          <label>Описание <textarea name="tittle" placeholder="Короткое описание для игрока"></textarea></label>
          <div class="content-wizard-row">
            <label>Тип в игре
              <select name="dopolnen">
                <option value="held_item">Предмет снаряжения</option>
                <option value="evolution">Эволюционный предмет</option>
                <option value="ticket">Билет</option>
                <option value="tm">TM-атака</option>
                <option value="gift_box">Подарочный ящик</option>
                <option value="craft">Материал крафта</option>
                <option value="utility">Утилити</option>
                <option value="admin">Другое/admin</option>
              </select>
            </label>
            <label>Legacy category <input name="category" type="number" value="0"></label>
          </div>
          <div class="content-wizard-flags">
            ${check('uses', 'Можно использовать')}
            ${check('dress', 'Можно надевать')}
            ${check('torg', 'Можно продавать')}
            ${check('battleuse', 'Можно в бою')}
            ${check('delet', 'Можно удалить')}
          </div>
          <button type="submit">Сохранить предмет</button>
        </form>

        <aside class="content-wizard-side">
          <h3>Как подключить картинку</h3>
          <ol>
            <li>Выбери ID предмета или сохрани предмет и посмотри выданный ID.</li>
            <li>Положи картинку в <code>public/img/items/&lt;id&gt;.png</code>.</li>
            <li>Если файл называется иначе, укажи его в поле “Файл иконки”.</li>
            <li>Fallback старых картинок: <code>img/items</code>, но новый runtime должен брать <code>public/img/items</code>.</li>
          </ol>
          <div class="content-wizard-actions">
            <button type="button" data-open-admin-tab="items">Открыть список предметов</button>
            <button type="button" data-open-admin-tab="market">Открыть Покемаркет</button>
          </div>
        </aside>
      </div>
    `;
  }

  function check(name, label) {
    return `<label><input type="checkbox" name="${esc(name)}" value="1"> ${esc(label)}</label>`;
  }

  function wireItem(api) {
    const form = api.root.querySelector('[data-content-item-form]');
    form?.addEventListener('submit', async event => {
      event.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      ['uses', 'dress', 'torg', 'battleuse', 'delet', 'elementary'].forEach(name => {
        if (!form.elements[name]?.checked) data[name] = '0';
      });
      const result = await api.send('/api/admin/items/save', data);
      api.setStatus(result.message || (result.ok ? 'Предмет сохранён.' : 'Ошибка сохранения.'), !result.ok);
      renderInspector(api, itemResultHtml(result));
    });
    wireOpenTabs(api);
  }

  function itemResultHtml(result) {
    if (!result || !result.ok) {
      return `
        <h3>Результат</h3>
        <p class="muted">${esc(result?.message || 'Предмет не сохранён.')}</p>
      `;
    }
    const item = result.item || {};
    const id = Number(item.id || 0);
    return `
      <h3>Предмет готов</h3>
      <div class="content-wizard-result">
        <b>#${esc(id)} ${esc(item.name || '')}</b>
        <span>${esc(item.tittle || '')}</span>
        <code>public/img/items/${esc(id)}.png</code>
      </div>
      <p class="muted">Теперь можно проверить предмет в инвентаре, магазине или выдать через раздел “Предметы”.</p>
    `;
  }

  function questHtml() {
    return `
      <div class="content-wizard-columns">
        <form class="content-wizard-form" data-content-quest-form>
          <h3>Черновик квеста</h3>
          <p class="content-wizard-help">Квесты живут в <b>quest_definitions</b> и <b>quest_steps</b>. Этот мастер собирает безопасный черновик, чтобы не писать JSON руками.</p>
          <label>Название <input name="title" required placeholder="Например: Потерянный билет"></label>
          <label>Описание <textarea name="description" placeholder="Игровой текст квеста"></textarea></label>
          <div class="content-wizard-row">
            <label>Тип
              <select name="type">
                <option value="story">Сюжетный</option>
                <option value="daily">Ежедневный</option>
                <option value="pvp">PvP</option>
                <option value="event">Ивентовый</option>
                <option value="hidden">Скрытый</option>
              </select>
            </label>
            <label>Зависит от Quest ID <input name="depends_on_quest_id" type="number" value="0"></label>
          </div>
          <label>Шаги квеста <textarea name="steps" placeholder="Поговорить с NPC | talk_ticket_master | 1&#10;Победить дикого покемона | fpe_first_battle | 20"></textarea></label>
          <label>Награды JSON <textarea name="reward_json" placeholder='{"items":{"1":5000},"rank":1}'></textarea></label>
          <button type="submit">Собрать черновик</button>
        </form>

        <aside class="content-wizard-side">
          <h3>Что важно</h3>
          <ul>
            <li><b>action_key</b> должен поддерживаться игровой логикой, иначе прогресс не сдвинется.</li>
            <li><b>reward_json</b> должен идти через общий reward-flow.</li>
            <li>Для кнопки “Показать на карте” у цели должен быть навигационный target в <code>QuestRepository</code>.</li>
          </ul>
          <div class="content-wizard-actions">
            <button type="button" data-open-admin-tab="legacy">Открыть Legacy-карту</button>
            <button type="button" data-open-admin-tab="events">Открыть ивенты/бусты</button>
          </div>
        </aside>
      </div>
    `;
  }

  function wireQuest(api) {
    const form = api.root.querySelector('[data-content-quest-form]');
    form?.addEventListener('submit', event => {
      event.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      const steps = String(data.steps || '').split('\n').map((line, index) => {
        const [title, actionKey, required] = line.split('|').map(part => String(part || '').trim());
        return title ? {
          step_no: index + 1,
          title,
          action_key: actionKey || 'todo_action_key',
          required_process: Number(required || 1)
        } : null;
      }).filter(Boolean);
      renderInspector(api, `
        <h3>Черновик квеста</h3>
        <p class="muted">Это структура для нового quest endpoint или миграции. Сейчас мастер не пишет квест напрямую, чтобы не создать квест без рабочей логики прогресса.</p>
        <pre class="content-wizard-code">${esc(JSON.stringify({
          quest_definitions: {
            title: data.title,
            description: data.description,
            depends_on_quest_id: Number(data.depends_on_quest_id || 0),
            repeatable: data.type === 'daily' ? 1 : 0,
            reward_json: data.reward_json || null,
            enabled: 1
          },
          quest_steps: steps
        }, null, 2))}</pre>
      `);
      api.setStatus('Черновик квеста собран. Проверь action_key и reward_json.', false);
    });
    wireOpenTabs(api);
  }

  function npcHtml() {
    return `
      <div class="content-wizard-columns">
        <form class="content-wizard-form" data-content-npc-form>
          <h3>Черновик NPC</h3>
          <p class="content-wizard-help">NPC на карте пока читаются из <b>config/location_content.php</b>, который генерируется из <b>include/rooms/*.php</b>.</p>
          <label>Имя NPC <input name="name" required placeholder="Например: Смотритель арены"></label>
          <div class="content-wizard-row">
            <label>Локация ID <input name="location_id" type="number" placeholder="Например 1"></label>
            <label>Тип действия
              <select name="action">
                <option value="quest">Выдаёт квест</option>
                <option value="shop">Магазин</option>
                <option value="heal">Лечение</option>
                <option value="transport">Транспорт</option>
                <option value="tournament">Турнир</option>
                <option value="dialog">Только диалог</option>
              </select>
            </label>
          </div>
          <label>Диалог <textarea name="dialog" placeholder="Текст, который увидит игрок"></textarea></label>
          <label>Условие появления <input name="condition" placeholder="Например: quest_id=101 completed"></label>
          <button type="submit">Собрать карточку NPC</button>
        </form>

        <aside class="content-wizard-side">
          <h3>Почему не сохраняется сразу</h3>
          <p>Если просто записать NPC в БД, он не появится на карте: текущий рендер берёт список из сгенерированного конфига локаций.</p>
          <ol>
            <li>Сначала нужен новый storage для NPC.</li>
            <li>Потом админский save endpoint.</li>
            <li>Потом карта должна читать NPC из нового storage, а не только из legacy rooms.</li>
          </ol>
          <div class="content-wizard-actions">
            <button type="button" data-open-admin-tab="locations">Открыть локации</button>
            <button type="button" data-open-admin-tab="legacy">Открыть Legacy-карту</button>
          </div>
        </aside>
      </div>
    `;
  }

  function wireNpc(api) {
    const form = api.root.querySelector('[data-content-npc-form]');
    form?.addEventListener('submit', event => {
      event.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      renderInspector(api, `
        <h3>Карточка NPC</h3>
        <div class="content-wizard-result">
          <b>${esc(data.name || 'NPC')}</b>
          <span>Локация #${esc(data.location_id || 'не выбрана')} · действие: ${esc(data.action || '')}</span>
          <span>${esc(data.dialog || 'Диалог не задан.')}</span>
        </div>
        <h3>Техническая карта</h3>
        <pre class="content-wizard-code">${esc(JSON.stringify({
          current_runtime: 'config/location_content.php',
          legacy_source: 'include/rooms/*.php',
          game_logic: 'NpcDialogService',
          next_step: 'создать новый npc storage + admin save endpoint'
        }, null, 2))}</pre>
      `);
      api.setStatus('Карточка NPC собрана. Для реального сохранения нужен новый NPC storage.', false);
    });
    wireOpenTabs(api);
  }

  function assetsHtml() {
    return `
      <div class="content-wizard-assets">
        ${assetBlock('Предметы', 'public/img/items/<id>.png', 'items, items_users, item_gameplay_metadata', 'Новый runtime берёт иконки отсюда. index.json нужен для нестандартных имён файлов.')}
        ${assetBlock('Покемоны для Покедекса', 'public/img/pokemon/art, small, art-shiny, small-shiny', 'DexRepository', 'Используется в Покедексе и карточках. Battle sprites отдельно.')}
        ${assetBlock('Боевые спрайты', 'Pok/spriteanim, Pok/back, Pok/shiny, Pok/sback', 'BattleEngineService', 'PvE/PvP тянут front/back showdown-style спрайты отсюда.')}
        ${assetBlock('Аватары', 'img/ava/<id>.png', 'users.avatars, ProfileRepository', 'Тренер-карта использует старую папку аватаров.')}
        ${assetBlock('Локации/NPC', 'img/room, public/img/ui/world', 'LocationStateService, location_content', 'Карта и NPC всё ещё частично совместимы с legacy ресурсами.')}
      </div>
    `;
  }

  function assetBlock(title, path, owner, note) {
    return `
      <article class="content-wizard-asset">
        <b>${esc(title)}</b>
        <code>${esc(path)}</code>
        <span>${esc(owner)}</span>
        <p>${esc(note)}</p>
      </article>
    `;
  }

  function wireAssets(api) {
    renderInspector(api, `
      <h3>Коротко</h3>
      <p class="muted">Для новых предметов почти всегда достаточно записи в <code>items</code> и файла <code>public/img/items/&lt;id&gt;.png</code>. Для NPC пока нужен перенос storage.</p>
    `);
  }

  function wireOpenTabs(api) {
    api.root.querySelectorAll('[data-open-admin-tab]').forEach(button => {
      button.addEventListener('click', () => api.openTab(button.dataset.openAdminTab || 'dashboard'));
    });
  }

  function renderInspector(api, html) {
    if (!api.inspector) return;
    api.inspector.innerHTML = html;
  }

  window.PokemonAdminContentWizard = { render };
})();
