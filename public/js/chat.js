/**
 * Pokemon 8.0 Chat Component (Vanilla JS)
 */
(function() {
    const chatLog = document.getElementById('chatLog');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const myLoginInput = document.querySelector('.chat-my-login');
    const app = document.querySelector('.world');
    const csrf = app ? app.dataset.csrf : '';
    const myLogin = myLoginInput ? String(myLoginInput.dataset.login || '').trim() : '';
    const myUserId = Number(app && app.dataset.userId || 0);

    let lastId = 0;
    let currentChannel = 1;
    let isPrivate = false;
    let pmToId = 0;
    /**
     * Упоминание показывается игроку ником, а не служебным маркером: в поле ввода
     * стоит «@Ник», и только при отправке (в общем чате) он превращается в
     * [[user:id]] — id остаётся источником правды, а глазами видно человека.
     */
    let pendingMention = null;
    let pmToName = '';
    let currentLocationId = readLocationId();
    let userMenu = null;
    let chatHoverProfileTimer = 0;
    let chatHoverProfileKey = '';
    let chatHoverProfileOpenedAt = 0;
    let autoScroll = true;
    let syncedInitialHistory = false;
    let allMessages = [];
    let pokemonPicker = null;
    let pokemonPickerButton = null;
    let pokemonPickerItems = [];
    let pokemonPickerIndex = 0;
    let pokemonPickerRange = null;
    let userPicker = null;
    let userPickerButton = null;
    let userPickerItems = [];
    let userPickerIndex = 0;
    let userPickerRange = null;
    const knownPlayers = new Map();
    let lastNotifiedId = 0;
    let activePokemonPopover = null;

    const TIPE_ALL = 1;
    const TIPE_SYSTEM = 2;
    const TIPE_BATTLE = 3;
    const TIPE_TRADE = 4;
    const TIPE_CLAN = 5;

    function init() {
        if (!chatLog || !chatForm) return;

        createTabs();
        setupPmTarget();
        updatePmDisplay();
        setupPokemonMentionPicker();
        setupUserMentionPicker();
        chatInput.addEventListener('input', updateMentionPickers);
        chatInput.addEventListener('keydown', handleMentionKeydown);
        chatForm.addEventListener('submit', handleSend);
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && activePokemonPopover) {
                event.preventDefault();
                activePokemonPopover.close();
            }
        }, true);
        document.addEventListener('pointerdown', event => {
            if (activePokemonPopover && !activePokemonPopover.card.contains(event.target)
                && !activePokemonPopover.trigger.contains(event.target)) activePokemonPopover.close();
        });
        window.addEventListener('resize', () => activePokemonPopover?.close());
        document.addEventListener('scroll', event => {
            if (activePokemonPopover && !activePokemonPopover.card.contains(event.target)) activePokemonPopover.close();
        }, true);

        try {
            const stored = JSON.parse(sessionStorage.getItem('pokemonchic:chat-pokemon-mention') || 'null');
            sessionStorage.removeItem('pokemonchic:chat-pokemon-mention');
            if (stored && Date.now() - stored.time >= 0 && Date.now() - stored.time < 300000) insertPokemonMention(stored.id);
        } catch (error) { /* Chat works when session storage is unavailable. */ }

        fetchMessages(true);
        window.addEventListener('pokemon:location-changed', event => {
            resetLocation(Number(event.detail && event.detail.locationId ? event.detail.locationId : readLocationId()));
        });

        setInterval(fetchMessages, 3000);
    }

    function readLocationId() {
        if (window.state && typeof window.state.locationId !== 'undefined') {
            return Number(window.state.locationId || 0);
        }
        if (window.PokemonGameState && typeof window.PokemonGameState.locationId !== 'undefined') {
            return Number(window.PokemonGameState.locationId || 0);
        }
        if (app && app.dataset.locationId) {
            return Number(app.dataset.locationId || 0);
        }
        return 0;
    }

    function resetLocation(locationId) {
        const nextLocationId = Number(locationId || 0);
        if (nextLocationId === currentLocationId) return;
        currentLocationId = nextLocationId;
        lastId = 0;
        allMessages = [];
        chatLog.innerHTML = '';
        syncedInitialHistory = false;
        closeUserPicker(false);
        closePokemonPicker(false);
    }

    function createTabs() {
        const tabsContainer = document.createElement('div');
        tabsContainer.className = 'chat-tabs';
        tabsContainer.innerHTML = `
            <button type="button" class="chat-tab active" data-tipe="1"><span class="icon icon--chat-tab-general" aria-hidden="true"></span><span>Общий</span></button>
            <button type="button" class="chat-tab" data-tipe="4"><span class="icon icon--chat-tab-trade" aria-hidden="true"></span><span>Торг</span></button>
            <button type="button" class="chat-tab" data-tipe="3"><span class="icon icon--chat-tab-battle" aria-hidden="true"></span><span>Бой</span></button>
            <button type="button" class="chat-tab" data-tipe="5"><span class="icon icon--chat-tab-clan" aria-hidden="true"></span><span>Клан</span></button>
            <button type="button" class="chat-tab" data-tipe="private"><span class="icon icon--chat-tab-private" aria-hidden="true"></span><span>Приват</span></button>
        `;

        chatLog.parentNode.insertBefore(tabsContainer, chatLog);
        createChatTools(tabsContainer);

        tabsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.chat-tab');
            if (!btn) return;

            document.querySelectorAll('.chat-tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const tipe = btn.dataset.tipe;
            if (tipe === 'private') {
                isPrivate = true;
                currentChannel = TIPE_ALL;
            } else {
                isPrivate = false;
                currentChannel = parseInt(tipe, 10);
            }

            updatePmDisplay();
            renderVisibleMessages();
        });
    }

    function createChatTools(tabsContainer) {
        const tools = document.createElement('div');
        tools.className = 'chat-tools';
        tools.innerHTML = `
            <button type="button" class="chat-tool-btn" data-chat-tool="clear" title="Очистить чат" aria-label="Очистить чат"><span class="icon icon--chat-clear" aria-hidden="true"></span></button>
            <button type="button" class="chat-tool-btn is-active" data-chat-tool="scroll" title="Автопрокрутка включена" aria-label="Автопрокрутка" aria-pressed="true"><span class="icon icon--chat-autoscroll-on" aria-hidden="true"></span></button>
        `;
        tabsContainer.appendChild(tools);


        tools.addEventListener('click', (e) => {
            const button = e.target.closest('[data-chat-tool]');
            if (!button) return;
            e.preventDefault();
            e.stopPropagation();

            if (button.dataset.chatTool === 'clear') {
                clearChatClientSide();
                return;
            }

            if (button.dataset.chatTool === 'scroll') {
                autoScroll = !autoScroll;
                button.classList.toggle('is-active', autoScroll);
                button.title = autoScroll ? 'Автопрокрутка включена' : 'Автопрокрутка выключена';
                // Это переключатель, поэтому состояние отдаём через aria-pressed,
                // а имя кнопки оставляем неизменным. Иконка внутри — с alt="",
                // то есть доступное имя держится только на aria-label/title,
                // а title на тач-устройствах не показывается.
                button.setAttribute('aria-pressed', autoScroll ? 'true' : 'false');
                const icon = button.querySelector('.icon');
                if (icon) {
                    icon.classList.toggle('icon--chat-autoscroll-on', autoScroll);
                    icon.classList.toggle('icon--chat-autoscroll-off', !autoScroll);
                }
            }
        });
    }

    function clearChatClientSide() {
        allMessages = [];
        chatLog.innerHTML = '';
    }

    function setupPmTarget() {
        const pmTarget = document.createElement('div');
        pmTarget.id = 'pmTargetDisplay';
        pmTarget.className = 'pm-target-display';
        pmTarget.style.display = 'none';
        pmTarget.innerHTML = `
            <span>Приват для: <strong id="pmTargetName"></strong></span>
            <button type="button" id="pmClearBtn" aria-label="Сбросить получателя личного сообщения">&times;</button>
        `;
        chatForm.insertBefore(pmTarget, chatForm.firstChild);

        // Блок показывает и личного получателя, и адресата упоминания, поэтому
        // подпись «Приват для:» заменяем на нейтральное «Кому:».
        const labelNode = pmTarget.querySelector('span');
        if (labelNode && labelNode.firstChild) {
            labelNode.firstChild.nodeValue = 'Кому: ';
        }

        document.getElementById('pmClearBtn').addEventListener('click', () => {
            pmToId = 0;
            pmToName = '';
            pendingMention = null;
            chatInput.value = chatInput.value.replace(/^\s*@[^\s]+\s*/, '');
            updatePmDisplay();
        });

        // Стерев «@Ник» из поля, игрок пишет в общий чат, а не выбранному игроку.
        chatInput.addEventListener('input', () => {
            syncMentionWithInput();
            updatePmDisplay();
        });

        createUserMenu();

        chatLog.addEventListener('click', (e) => {
            const author = e.target.closest('.chat-author');
            if (!author) return;
            e.preventDefault();
            e.stopPropagation();

            const id = parseInt(author.dataset.id, 10);
            const name = author.textContent;
            const isSystem = author.dataset.system === '1';

            if (id > 0 || name || isSystem) {
                showUserMenu(id, name, e, isSystem);
            }
        });

        chatLog.addEventListener('mouseover', (e) => {
            const author = e.target.closest('.chat-author');
            if (!author || !chatLog.contains(author)) return;
            if (author.contains(e.relatedTarget)) return;
            scheduleChatHoverTrainerCard(author);
        });

        chatLog.addEventListener('mouseout', (e) => {
            const author = e.target.closest('.chat-author');
            if (!author || !chatLog.contains(author)) return;
            if (author.contains(e.relatedTarget)) return;
            clearChatHoverProfileTimer();
        });

        document.addEventListener('click', hideUserMenu);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                clearChatHoverProfileTimer();
                hideUserMenu();
            }
        });
        document.addEventListener('player-menu-action', (e) => {
            const detail = e.detail || {};
            const id = parseInt(detail.id || '0', 10);
            const name = String(detail.login || '');
            if (!name) return;

            // «Написать» = публичное сообщение в общем чате с упоминанием игрока.
            // Личные сообщения открывает только действие «private» (кнопка «ЛС»).
            if (detail.action === 'dialog') {
                openGeneralMention(id, name);
                return;
            }

            if (detail.action === 'private') {
                openPrivateConversation(id, name);
                return;
            }

            if (detail.action === 'friend') {
                requestFriend(id, name);
            }
        });
    }

    function createUserMenu() {
        userMenu = document.createElement('div');
        userMenu.className = 'chat-user-menu';
        userMenu.hidden = true;
        userMenu.innerHTML = `
            <div class="chat-user-menu-title" hidden></div>
            <button type="button" data-action="info"><span class="icon icon--chat-user-menu-info" aria-hidden="true"></span><span>Информация</span></button>
            <button type="button" data-action="reply"><span class="icon icon--chat-user-menu-reply" aria-hidden="true"></span><span>Написать в чат</span></button>
            <button type="button" data-action="private"><span class="icon icon--chat-user-menu-private" aria-hidden="true"></span><span>Написать ЛС</span></button>
            <button type="button" data-action="friend"><span class="icon icon--chat-user-menu-friend" aria-hidden="true"></span><span>Дружить</span></button>
            <button type="button" data-action="ignore"><span class="icon icon--chat-user-menu-ignore" aria-hidden="true"></span><span>Игнорировать</span></button>
            <button type="button" data-action="mail"><span class="icon icon--chat-user-menu-mail" aria-hidden="true"></span><span>Написать на почту</span></button>
        `;
        document.body.appendChild(userMenu);

        userMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            const button = e.target.closest('button[data-action]');
            if (!button) return;

            const id = parseInt(userMenu.dataset.userId || '0', 10);
            const name = userMenu.dataset.userName || '';
            handleUserMenuAction(button.dataset.action, id, name);
        });
    }

    function isSystemAuthor(msg) {
        const name = String(msg && msg.author_name || '').trim().toLowerCase();
        const hasHumanName = name !== '' && !['system', 'система', 'администрация', 'сервис', 'event', 'events'].includes(name);
        return Number(msg && msg.tipe || 0) === TIPE_SYSTEM
            || ['system', 'система', 'администрация', 'сервис', 'event', 'events'].includes(name)
            || (Number(msg && msg.author_id || 0) <= 0 && !hasHumanName);
    }

    function showUserMenu(id, name, event, isSystem = false) {
        if (!userMenu) return;

        userMenu.dataset.userId = String(id);
        userMenu.dataset.userName = name;
        userMenu.dataset.system = isSystem ? '1' : '0';

        const title = userMenu.querySelector('.chat-user-menu-title');
        if (title) {
            title.hidden = !isSystem;
            title.textContent = isSystem ? 'Системное уведомление:' : '';
        }

        const hasPlayerId = Number(id || 0) > 0;
        userMenu.querySelectorAll('[data-action="private"], [data-action="friend"], [data-action="ignore"]').forEach(button => {
            button.hidden = isSystem || !hasPlayerId;
        });
        userMenu.querySelectorAll('[data-action="mail"]').forEach(button => {
            button.hidden = isSystem || (!hasPlayerId && !name);
        });

        const replyButton = userMenu.querySelector('[data-action="reply"]');
        if (replyButton) {
            const label = replyButton.querySelector('span:last-child');
            if (label) label.textContent = isSystem ? 'Ответить в чат' : 'Написать';
        }

        userMenu.hidden = false;

        const margin = 8;
        const rect = userMenu.getBoundingClientRect();
        const left = Math.min(event.clientX, window.innerWidth - rect.width - margin);
        const top = Math.min(event.clientY, window.innerHeight - rect.height - margin);
        userMenu.style.left = `${Math.max(margin, left)}px`;
        userMenu.style.top = `${Math.max(margin, top)}px`;
    }

    function hideUserMenu() {
        if (userMenu) userMenu.hidden = true;
    }

    function clearChatHoverProfileTimer() {
        if (chatHoverProfileTimer) {
            clearTimeout(chatHoverProfileTimer);
            chatHoverProfileTimer = 0;
        }
    }

    function openTrainerCardFromChat(id, name) {
        const login = String(name || '').trim();
        const playerId = Number(id || 0);
        if (!login && playerId <= 0) return false;
        if (!window.TrainerProfileWindow || typeof window.TrainerProfileWindow.open !== 'function') {
            return false;
        }

        window.TrainerProfileWindow.open(playerId > 0 ? { id: playerId, login } : { login });
        return true;
    }

    function scheduleChatHoverTrainerCard(author) {
        clearChatHoverProfileTimer();
        if (!author || author.dataset.system === '1') return;

        const id = parseInt(author.dataset.id || '0', 10);
        const name = String(author.textContent || '').trim();
        const key = id > 0 ? `id:${id}` : `login:${name.toLowerCase()}`;
        if (!name && id <= 0) return;

        chatHoverProfileTimer = window.setTimeout(() => {
            chatHoverProfileTimer = 0;
            const now = Date.now();
            if (chatHoverProfileKey === key && now - chatHoverProfileOpenedAt < 3000) {
                return;
            }
            if (openTrainerCardFromChat(id, name)) {
                chatHoverProfileKey = key;
                chatHoverProfileOpenedAt = now;
            }
        }, 750);
    }

    function handleUserMenuAction(action, id, name) {
        hideUserMenu();

        if (action === 'info') {
            const isSystem = userMenu && userMenu.dataset.system === '1';
            if (!isSystem && openTrainerCardFromChat(id, name)) {
                return;
            }
            if (window.PokemonSocial && typeof window.PokemonSocial.notify === 'function') {
                window.PokemonSocial.notify('Тренеркарта доступна только для игровых аккаунтов.', 'error');
            } else {
                alert('Тренеркарта доступна только для игровых аккаунтов.');
            }
            return;
        }

        // «Написать» в меню игрока — публичное сообщение в общем чате с упоминанием.
        if (action === 'reply') {
            if (id > 0) openGeneralMention(id, name);
            else {
                switchToPublicChat();
                insertPublicReply(name);
            }
            return;
        }

        // Личные сообщения открывает только «Написать ЛС».
        if (action === 'private') {
            if (id > 0) openPrivateConversation(id, name);
            else {
                switchToPublicChat();
                insertPublicReply(name);
            }
            return;
        }

        if (action === 'mail') {
            if (window.GameMailOverlay && typeof window.GameMailOverlay.open === 'function') {
                window.GameMailOverlay.open({ recipient: id > 0 ? String(id) : name });
                return;
            }
            if (id > 0) {
                window.location.href = `/game/messages?to=${encodeURIComponent(id)}`;
            } else {
                window.location.href = `/game/messages?mail_to=${encodeURIComponent(name)}`;
            }
            return;
        }

        if (action === 'friend') {
            requestFriend(id, name);
            return;
        }

        const labels = {
            ignore: 'Игнорирование пока не подключено.'
        };
        alert(labels[action] || 'Действие пока не подключено.');
    }

    async function requestFriend(id, name) {
        if (window.PokemonSocial && typeof window.PokemonSocial.requestFriend === 'function') {
            window.PokemonSocial.requestFriend(id, name);
            return;
        }

        if (!id) {
            alert('Игрок не выбран.');
            return;
        }

        try {
            const body = new URLSearchParams();
            body.set('_csrf', csrf);
            body.set('user_id', String(id));

            const response = await fetch('/api/friends/request', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body
            });
            const data = await response.json();
            alert(data.message || (data.ok ? 'Заявка в друзья отправлена.' : 'Не удалось отправить заявку.'));
        } catch (e) {
            console.error('Friend request error:', e);
            alert('Ошибка сервера при отправке заявки в друзья.');
        }
    }

    function switchToPublicChat() {
        if (!isPrivate) return;
        const publicTab = document.querySelector('.chat-tab[data-tipe="1"]');
        if (publicTab) publicTab.click();
    }

    function insertPublicReply(name) {
        const prefix = `${name}, `;
        const value = chatInput.value.trimStart();
        if (!value.startsWith(prefix)) {
            chatInput.value = prefix + value;
        }
        chatInput.focus();
        chatInput.setSelectionRange(chatInput.value.length, chatInput.value.length);
    }

    function openPrivateConversation(id, name) {
        if (!Number.isSafeInteger(id) || id <= 0 || id === myUserId) return;
        knownPlayers.set(id, String(name));
        pmToId = id;
        pmToName = String(name);
        const privTab = document.querySelector('.chat-tab[data-tipe="private"]');
        if (privTab) privTab.click();
        updatePmDisplay();
        insertMentionMarker(id, name);
    }

    /**
     * «Написать» — это сообщение в ОБЩИЙ чат с упоминанием игрока.
     * Приват включается только кнопкой «Написать ЛС»: раньше обе кнопки уводили
     * в личные сообщения, из-за чего публичное сообщение нельзя было отправить
     * с упоминанием.
     */
    function openGeneralMention(id, name) {
        if (!Number.isSafeInteger(id) || id <= 0 || id === myUserId) return;
        knownPlayers.set(id, String(name));
        pmToId = 0;
        pmToName = '';
        const generalTab = document.querySelector('.chat-tab[data-tipe="1"]');
        if (generalTab) generalTab.click();
        updatePmDisplay();
        insertMentionMarker(id, name);
    }

    function insertMentionMarker(id, name) {
        const label = String(name || knownPlayers.get(id) || '').trim();
        if (!label) return;

        pendingMention = { id: id, name: label };
        const token = '@' + label;

        // Убираем из начала поля прошлое упоминание, чтобы не накапливались.
        const rest = chatInput.value.replace(/^\s*@[^\s]+\s*/, '').trimStart();
        chatInput.value = rest ? token + ' ' + rest : token + ' ';
        chatInput.focus();
        chatInput.setSelectionRange(chatInput.value.length, chatInput.value.length);
        updatePmDisplay();
        closeUserPicker(false);
        closePokemonPicker(false);
    }

    function updatePmDisplay() {
        const display = document.getElementById('pmTargetDisplay');
        const nameNode = document.getElementById('pmTargetName');

        // Кому пишем — видно всегда: и в личных сообщениях, и когда в поле стоит
        // упоминание «@Ник». Раньше блок показывался только в привате, поэтому игрок
        // видел свой собственный логин и не понимал, кому уйдёт сообщение.
        const target = isPrivate ? (pmToId > 0 ? pmToName : '') : (pendingMention ? pendingMention.name : '');
        if (myLoginInput) myLoginInput.value = target;

        if (target) {
            display.style.display = 'flex';
            nameNode.textContent = target;
        } else {
            display.style.display = 'none';
            nameNode.textContent = '';
        }
    }

    /**
     * Стирание упоминания из поля = отказ от адресата: сообщение уйдёт в общий чат,
     * а не «в никуда» и не в приват. Так игрок может передумать и написать всем.
     */
    function syncMentionWithInput() {
        if (!pendingMention) return;

        const token = '@' + pendingMention.name;
        if (chatInput.value.trimStart().startsWith(token)) return;

        pendingMention = null;
        if (isPrivate) {
            isPrivate = false;
            pmToId = 0;
            pmToName = '';
            switchToPublicChat();
        }
        updatePmDisplay();
    }

    function setupPokemonMentionPicker() {
        if (!chatInput || !chatForm) return;

        pokemonPickerButton = document.createElement('button');
        pokemonPickerButton.type = 'button';
        pokemonPickerButton.className = 'chat-pokemon-mention-btn';
        pokemonPickerButton.setAttribute('aria-label', 'Упомянуть покемона из команды');
        pokemonPickerButton.setAttribute('aria-haspopup', 'listbox');
        pokemonPickerButton.setAttribute('aria-expanded', 'false');
        pokemonPickerButton.textContent = '#';

        pokemonPicker = document.createElement('div');
        pokemonPicker.className = 'chat-pokemon-picker';
        pokemonPicker.hidden = true;
        pokemonPicker.setAttribute('role', 'listbox');
        pokemonPicker.setAttribute('aria-label', 'Покемоны вашей команды');

        chatInput.insertAdjacentElement('afterend', pokemonPickerButton);
        chatForm.appendChild(pokemonPicker);

        pokemonPickerButton.addEventListener('click', async () => {
            if (!pokemonPicker.hidden) {
                closePokemonPicker(true);
                return;
            }
            closeUserPicker(false);
            pokemonPickerRange = { start: chatInput.selectionStart, end: chatInput.selectionEnd };
            await openPokemonPicker();
        });

        pokemonPicker.addEventListener('click', (event) => {
            const option = event.target.closest('[data-pokemon-id]');
            if (!option) return;
            insertPokemonMention(option.dataset.pokemonId);
        });

        pokemonPicker.addEventListener('keydown', handleMentionKeydown);

        document.addEventListener('click', (event) => {
            if (pokemonPicker.hidden) return;
            if (pokemonPicker.contains(event.target) || pokemonPickerButton.contains(event.target)) return;
            closePokemonPicker(false);
        });
    }

    async function openPokemonPicker() {
        pokemonPicker.hidden = false;
        pokemonPickerButton.setAttribute('aria-expanded', 'true');
        pokemonPicker.innerHTML = '<div class="chat-pokemon-picker-empty">Загружаю команду...</div>';

        try {
            const response = await fetch('/api/chat/team-pokemon', { credentials: 'same-origin' });
            const data = await response.json();
            if (!response.ok || data.ok !== true) throw new Error('Team fetch failed');
            const pokemon = Array.isArray(data.pokemon) ? data.pokemon : [];
            pokemonPicker._team = pokemon;
            renderPokemonPicker(pokemon);
        } catch (error) {
            console.error('Pokemon mention picker error:', error);
            pokemonPicker.innerHTML = '<div class="chat-pokemon-picker-empty">Не удалось загрузить команду.</div>';
        }
    }

    function renderPokemonPicker(pokemon) {
        pokemonPicker.innerHTML = '';
        if (pokemon.length === 0) {
            pokemonPicker.innerHTML = '<div class="chat-pokemon-picker-empty">В вашей команде нет покемонов.</div>';
            pokemonPickerItems = [];
            return;
        }

        const query = (pokemonPickerRange && pokemonPickerRange.query || '').toLocaleLowerCase();
        const filtered = pokemon.filter(item => String(item.name || '').toLocaleLowerCase().includes(query));
        if (!filtered.length) {
            pokemonPicker.innerHTML = '<div class="chat-pokemon-picker-empty">Совпадений нет.</div>';
            pokemonPickerItems = [];
            return;
        }
        filtered.forEach((item, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'chat-pokemon-picker-item';
            option.dataset.pokemonId = String(item.id || 0);
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
            option.tabIndex = index === 0 ? 0 : -1;

            const img = document.createElement('img');
            const sprite = safeSprite(item.sprite);
            if (sprite) img.src = sprite;
            else img.hidden = true;
            img.alt = '';
            img.loading = 'lazy';
            option.appendChild(img);

            const label = document.createElement('span');
            label.textContent = `${item.name || `#${item.dexNumber || item.baseNum || item.id}`} · Lv.${Number(item.level || 0)}`;
            option.appendChild(label);

            pokemonPicker.appendChild(option);
        });

        pokemonPickerItems = Array.from(pokemonPicker.querySelectorAll('.chat-pokemon-picker-item'));
        pokemonPickerIndex = 0;
        if (document.activeElement !== chatInput) pokemonPickerItems[0].focus();
    }

    function movePokemonPickerFocus(direction) {
        if (pokemonPickerItems.length === 0) return;
        pokemonPickerItems[pokemonPickerIndex].tabIndex = -1;
        pokemonPickerItems[pokemonPickerIndex].setAttribute('aria-selected', 'false');
        pokemonPickerIndex = (pokemonPickerIndex + direction + pokemonPickerItems.length) % pokemonPickerItems.length;
        pokemonPickerItems[pokemonPickerIndex].tabIndex = 0;
        pokemonPickerItems[pokemonPickerIndex].setAttribute('aria-selected', 'true');
        if (document.activeElement !== chatInput) pokemonPickerItems[pokemonPickerIndex].focus();
    }

    function insertPokemonMention(pokemonId) {
        const id = Number(pokemonId);
        if (!Number.isSafeInteger(id) || id <= 0) return;
        replaceMention(pokemonPickerRange, `[[poke:${id}]]`);
        closePokemonPicker(true);
    }

    function closePokemonPicker(restoreFocus) {
        if (!pokemonPicker) return;
        pokemonPicker.hidden = true;
        pokemonPickerButton.setAttribute('aria-expanded', 'false');
        pokemonPickerRange = null;
        if (restoreFocus) chatInput.focus();
    }

    function setupUserMentionPicker() {
        userPickerButton = document.createElement('button');
        userPickerButton.type = 'button';
        userPickerButton.className = 'chat-user-mention-btn';
        userPickerButton.textContent = '@';
        userPickerButton.setAttribute('aria-label', 'Упомянуть игрока');
        userPickerButton.setAttribute('aria-haspopup', 'listbox');
        userPickerButton.setAttribute('aria-expanded', 'false');
        userPicker = document.createElement('div');
        userPicker.className = 'chat-user-picker';
        userPicker.hidden = true;
        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'chat-user-picker-search';
        search.placeholder = 'Поиск игрока';
        search.setAttribute('aria-label', 'Поиск игрока по нику');
        const list = document.createElement('div');
        list.className = 'chat-user-picker-list';
        list.id = 'chatUserMentionList';
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-label', 'Игроки для упоминания');
        userPicker.append(search, list);
        chatInput.insertAdjacentElement('afterend', userPickerButton);
        chatForm.appendChild(userPicker);
        userPickerButton.setAttribute('aria-controls', list.id);
        userPickerButton.addEventListener('click', () => {
            if (!userPicker.hidden) return closeUserPicker(true);
            userPickerRange = { start: chatInput.selectionStart, end: chatInput.selectionEnd };
            openUserPicker('', true);
        });
        search.addEventListener('input', () => renderUserPicker(search.value));
        userPicker.addEventListener('click', event => {
            const item = event.target.closest('[data-user-id]');
            if (item) insertUserMention(item.dataset.userId);
        });
        userPicker.addEventListener('keydown', handleMentionKeydown);
        document.addEventListener('click', event => {
            if (!userPicker.hidden && !userPicker.contains(event.target)
                && !userPickerButton.contains(event.target) && event.target !== chatInput) {
                closeUserPicker(false);
            }
        });
    }

    function knownMentionPlayers() {
        const players = new Map(knownPlayers);
        for (const row of document.querySelectorAll('#usersList .player-row')) {
            const id = Number(row.dataset.playerId || 0);
            const name = String(row.dataset.playerLogin || row.querySelector('.user-name')?.textContent || '').trim();
            if (Number.isSafeInteger(id) && id > 0 && name) players.set(id, name);
        }
        for (const msg of allMessages) {
            if (Number(msg.author_id) > 0 && msg.author_name) players.set(Number(msg.author_id), String(msg.author_name));
            if (Number(msg.to_id) > 0 && msg.to_name) players.set(Number(msg.to_id), String(msg.to_name));
        }
        players.delete(myUserId);
        return [...players].map(([id, name]) => ({ id, name })).sort((a, b) => a.name.localeCompare(b.name, 'ru'));
    }

    function openUserPicker(query, focusSearch = false) {
        closePokemonPicker(false);
        userPicker.hidden = false;
        userPickerButton.setAttribute('aria-expanded', 'true');
        const search = userPicker.querySelector('.chat-user-picker-search');
        search.value = query;
        renderUserPicker(query);
        if (focusSearch) search.focus();
    }

    function renderUserPicker(query) {
        const list = userPicker.querySelector('.chat-user-picker-list');
        list.replaceChildren();
        const matches = knownMentionPlayers().filter(item => item.name.toLocaleLowerCase().includes(query.toLocaleLowerCase())).slice(0, 50);
        if (!matches.length) {
            const empty = document.createElement('div');
            empty.className = 'chat-pokemon-picker-empty';
            empty.textContent = 'Игроки не найдены. Доступны игроки комнаты и недавние собеседники.';
            list.appendChild(empty);
        }
        matches.forEach((player, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'chat-user-picker-item';
            option.dataset.userId = String(player.id);
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
            option.tabIndex = -1;
            option.textContent = '@' + player.name;
            list.appendChild(option);
        });
        userPickerItems = [...list.querySelectorAll('[data-user-id]')];
        userPickerIndex = 0;
    }

    function closeUserPicker(restoreFocus) {
        if (!userPicker) return;
        userPicker.hidden = true;
        userPickerButton.setAttribute('aria-expanded', 'false');
        userPickerRange = null;
        if (restoreFocus) chatInput.focus();
    }

    function insertUserMention(userId) {
        const id = Number(userId);
        if (!Number.isSafeInteger(id) || id <= 0) return;
        const player = knownMentionPlayers().find(player => player.id === id);
        if (!player) return;
        if (isPrivate) openPrivateConversation(id, player.name);
        else insertMentionMarker(id, player.name);
        closeUserPicker(true);
    }

    function replaceMention(range, marker) {
        const start = range ? range.start : chatInput.selectionStart;
        const end = range ? range.end : chatInput.selectionEnd;
        const prefix = chatInput.value.slice(0, start);
        const suffix = chatInput.value.slice(end);
        const space = suffix && !/^\s/u.test(suffix) ? ' ' : '';
        chatInput.value = prefix + marker + space + suffix;
        chatInput.focus();
        const caret = prefix.length + marker.length + space.length;
        chatInput.setSelectionRange(caret, caret);
    }

    function updateMentionPickers() {
        const caret = chatInput.selectionStart;
        const before = chatInput.value.slice(0, caret);
        const match = /(^|\s)([@#])([^\s@#]*)$/u.exec(before);
        if (!match) {
            closeUserPicker(false);
            closePokemonPicker(false);
            return;
        }
        const range = { start: caret - match[2].length - match[3].length, end: caret, query: match[3] };
        if (match[2] === '@') {
            userPickerRange = range;
            openUserPicker(match[3]);
        } else {
            closeUserPicker(false);
            // A typed numeric #ID is a complete reference, not a picker query.
            // Enter must send it unchanged, not choose the first team member.
            if (/^[0-9]+$/.test(match[3])) {
                closePokemonPicker(false);
                return;
            }
            pokemonPickerRange = range;
            if (pokemonPicker.hidden) openPokemonPicker();
            else {
                const all = pokemonPicker._team || [];
                renderPokemonPicker(all);
            }
        }
    }

    function handleMentionKeydown(event) {
        const isUser = userPicker && !userPicker.hidden;
        const isPoke = pokemonPicker && !pokemonPicker.hidden;
        if (!isUser && !isPoke) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeUserPicker(true);
            closePokemonPicker(true);
            return;
        }
        if (!['ArrowUp', 'ArrowDown', 'Enter'].includes(event.key)) return;
        const items = isUser ? userPickerItems : pokemonPickerItems;
        if (!items.length && event.key !== 'Enter') return;
        event.preventDefault();
        if (!items.length) return;
        if (event.key === 'Enter') {
            if (isUser) insertUserMention(items[userPickerIndex].dataset.userId);
            else insertPokemonMention(items[pokemonPickerIndex].dataset.pokemonId);
            return;
        }
        if (isUser) {
            userPickerIndex = (userPickerIndex + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            items.forEach((option, index) => option.setAttribute('aria-selected', index === userPickerIndex ? 'true' : 'false'));
            items[userPickerIndex].scrollIntoView({ block: 'nearest' });
        } else {
            movePokemonPickerFocus(event.key === 'ArrowDown' ? 1 : -1);
        }
    }

    function safeSprite(path) {
        return typeof path === 'string' && /^\/(?!\/)[^\s]*$/u.test(path) ? path : '';
    }

    function mergeMessages(messages) {
        const byId = new Map();
        for (const msg of allMessages) {
            byId.set(Number(msg.id || 0), msg);
        }
        for (const msg of messages || []) {
            const id = Number(msg.id || 0);
            if (id > 0) {
                byId.set(id, msg);
            } else {
                byId.set(Date.now() + Math.random(), msg);
            }
        }
        allMessages = Array.from(byId.values()).sort((a, b) => Number(a.id || 0) - Number(b.id || 0));
    }

    async function fetchMessages(syncOnly = false) {
        try {
            resetLocation(readLocationId());

            const shouldSyncOnly = syncOnly || !syncedInitialHistory;
            const params = new URLSearchParams();
            params.set('after_id', String(lastId));
            if (shouldSyncOnly) params.set('sync_only', '1');

            const response = await fetch(`/api/chat/messages?${params.toString()}`, { credentials: 'same-origin' });
            const data = await response.json();

            if (!data.ok) return;

            if (shouldSyncOnly) {
                lastId = Number(data.lastId || lastId || 0);
                syncedInitialHistory = true;
                return;
            }

            if (Array.isArray(data.messages) && data.messages.length > 0) {
                const fresh = data.messages.filter(msg => Number(msg.id || 0) > lastId);
                fresh.filter(msg => Number(msg.author_id) !== myUserId
                    && (msg.mentioned_me === true || (myUserId > 0 && String(msg.text || '').includes(`[[user:${myUserId}]]`))
                        || (msg.private && Number(msg.to_id) === myUserId)))
                    .slice(0, 3).forEach(msg => {
                        if (Number(msg.id) <= lastNotifiedId) return;
                        lastNotifiedId = Number(msg.id);
                        if (window.PokemonSocial && typeof window.PokemonSocial.notify === 'function') {
                            const preview = cleanMessageText(msg.text)
                                .replace(/\[\[user:(\d+)\]\]/gu, (_, id) => '@' + (msg.user_mentions?.[id] || 'Игрок'))
                                .replace(/\[\[poke:\d+\]\]/gu, '[покемон]');
                            window.PokemonSocial.notify((msg.private ? 'ЛС: ' : 'Вас упомянули в чате: ')
                                + preview.slice(0, 150), 'info');
                        }
                    });
                if (lastId === 0) {
                    allMessages = data.messages;
                } else {
                    mergeMessages(data.messages);
                }

                if (allMessages.length > 200) allMessages = allMessages.slice(-200);

                lastId = data.lastId;
                renderVisibleMessages();
            }
        } catch (e) {
            console.error('Chat fetch error:', e);
        }
    }

    function renderVisibleMessages() {
        activePokemonPopover?.close();
        chatLog.innerHTML = '';

        const filtered = allMessages.filter(msg => {
            if (isPrivate) {
                return msg.private === true;
            }

            if (currentChannel === TIPE_ALL) {
                return msg.private === false;
            }

            return msg.private === false && msg.tipe === currentChannel;
        });

        filtered.forEach(appendMessage);
        if (autoScroll) {
            chatLog.scrollTop = chatLog.scrollHeight;
        }
    }

    function appendMessage(msg) {
        const div = document.createElement('div');
        div.className = 'chat-row';
        const text = cleanMessageText(msg.text);
        if (msg.private) div.classList.add('is-private');
        if ((Number(msg.author_id) !== myUserId && (msg.mentioned_me === true
            || (myUserId > 0 && text.includes(`[[user:${myUserId}]]`))))
            || (!msg.private && isMessageForMe(text, msg.author_name))) div.classList.add('is-mention');
        if (msg.tipe === TIPE_SYSTEM) div.classList.add('is-system');

        const time = new Date(msg.time * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const timeSpan = document.createElement('span');
        timeSpan.className = 'chat-time';
        timeSpan.textContent = `[${time}] `;
        div.appendChild(timeSpan);

        const systemAuthor = isSystemAuthor(msg);
        const authorName = String(msg.author_name || '').trim();
        if (authorName !== '') {
            const authorSpan = document.createElement('span');
            authorSpan.className = 'chat-author';
            authorSpan.dataset.id = systemAuthor ? '0' : String(Number(msg.author_id || 0));
            authorSpan.dataset.system = systemAuthor ? '1' : '0';
            authorSpan.textContent = authorName;
            div.appendChild(authorSpan);

            if (msg.private && msg.to_id > 0) {
                const toSpan = document.createElement('span');
                toSpan.className = 'chat-to';
                toSpan.textContent = ` -> ${msg.to_name}`;
                div.appendChild(toSpan);
            }

            const sep = document.createElement('span');
            sep.textContent = ': ';
            div.appendChild(sep);
        }

        const textSpan = document.createElement('span');
        textSpan.className = 'chat-text';
        renderMentionText(textSpan, text, msg);
        div.appendChild(textSpan);

        if (!/\[\[poke:\d+\]\]/u.test(text) && msg.pokemon_card) {
            const card = renderPokemonCard(msg.pokemon_card);
            if (card) div.appendChild(card);
        }

        chatLog.appendChild(div);
    }

    function renderMentionText(container, text, msg) {
        const pattern = /\[\[(user|poke):(\d+)\]\]/gu;
        let pos = 0;
        for (const match of text.matchAll(pattern)) {
            container.appendChild(document.createTextNode(text.slice(pos, match.index)));
            const id = Number(match[2]);
            if (match[1] === 'user') {
                const mention = document.createElement('strong');
                mention.className = 'chat-user-mention';
                const names = msg.user_mentions || {};
                const name = names[id] || knownMentionPlayers().find(p => p.id === id)?.name;
                mention.textContent = '@' + (name || 'Игрок');
                container.appendChild(mention);
            } else {
                const firstPokeId = Number((String(msg.text || '').match(/\[\[poke:(\d+)\]\]/u) || [])[1]);
                const cardData = (Array.isArray(msg.pokemon_cards) ? msg.pokemon_cards : [msg.pokemon_card])
                    .find(card => card && Number(card.id || card.pokemon_id || firstPokeId) === id);
                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'chat-pokemon-mention-trigger';
                trigger.textContent = '#' + id + (cardData && cardData.available !== false && cardData.name ? ' · ' + cardData.name : '');
                trigger.setAttribute('aria-label', 'Показать карточку покемона #' + id);
                trigger.setAttribute('aria-expanded', 'false');
                container.appendChild(trigger);
                const card = renderPokemonCard(cardData || { available: false });
                card.hidden = true;
                container.appendChild(card);
                bindPokemonPopover(trigger, card, 'chat-pokemon-' + Number(msg.id) + '-' + id + '-' + match.index);
            }
            pos = match.index + match[0].length;
        }
        container.appendChild(document.createTextNode(text.slice(pos)));
        // Invalid tokens are never evaluated as markup and are not surfaced raw.
        if (/\[\[(?:user|poke):/u.test(container.textContent)) {
            for (const node of [...container.childNodes]) {
                if (node.nodeType === Node.TEXT_NODE) node.textContent = node.textContent.replace(/\[\[(?:user|poke):[^\]]*\]\]/gu, '[недоступное упоминание]');
            }
        }
    }

    function bindPokemonPopover(trigger, card, id) {
        let dismissTimer = 0;
        let pinned = false;
        card.id = id;
        card.classList.add('chat-pokemon-popover');
        card.setAttribute('role', 'tooltip');
        if (typeof card.showPopover === 'function') card.setAttribute('popover', 'manual');
        trigger.setAttribute('aria-controls', id);
        const close = () => {
            clearTimeout(dismissTimer);
            pinned = false;
            if (typeof card.hidePopover === 'function' && card.matches(':popover-open')) card.hidePopover();
            card.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
            trigger.removeAttribute('aria-describedby');
            if (activePokemonPopover?.card === card) activePokemonPopover = null;
        };
        const show = () => {
            clearTimeout(dismissTimer);
            if (activePokemonPopover?.card !== card) activePokemonPopover?.close();
            card.hidden = false;
            if (typeof card.showPopover === 'function' && !card.matches(':popover-open')) card.showPopover();
            const anchor = trigger.getBoundingClientRect();
            const bounds = card.getBoundingClientRect();
            const left = Math.max(12, Math.min(anchor.left, innerWidth - bounds.width - 12));
            const below = anchor.bottom + 8;
            const top = below + bounds.height <= innerHeight - 12 ? below : Math.max(12, anchor.top - bounds.height - 8);
            card.style.left = left + 'px';
            card.style.top = top + 'px';
            trigger.setAttribute('aria-expanded', 'true');
            trigger.setAttribute('aria-describedby', id);
            activePokemonPopover = { trigger, card, close };
        };
        const dismissLater = () => {
            clearTimeout(dismissTimer);
            if (!pinned) dismissTimer = setTimeout(close, 180);
        };
        trigger.addEventListener('mouseenter', show);
        trigger.addEventListener('mouseleave', dismissLater);
        trigger.addEventListener('focus', show);
        trigger.addEventListener('blur', () => { pinned = false; dismissLater(); });
        trigger.addEventListener('click', () => {
            if (pinned && activePokemonPopover?.card === card) close();
            else { show(); pinned = true; }
        });
        card.addEventListener('mouseenter', () => clearTimeout(dismissTimer));
        card.addEventListener('mouseleave', dismissLater);
    }

    function renderPokemonCard(card) {
        const wrap = document.createElement('article');
        wrap.className = 'chat-pokemon-card';
        if (!card || card.available === false) {
            wrap.classList.add('is-missing');
            wrap.textContent = 'Карточка покемона недоступна.';
            return wrap;
        }
        const spriteUrl = safeSprite(card.sprite);
        if (spriteUrl) {
            const sprite = document.createElement('img');
            sprite.className = 'chat-pokemon-card-sprite';
            sprite.src = spriteUrl;
            sprite.alt = '';
            sprite.loading = 'lazy';
            wrap.appendChild(sprite);
        }
        const body = document.createElement('div');
        body.className = 'chat-pokemon-card-body';
        wrap.appendChild(body);
        const title = document.createElement('div');
        title.className = 'chat-pokemon-card-title';
        const dex = card.dexNumber ?? card.baseNum;
        title.textContent = [dex != null ? '#' + dex : '', card.name, card.level != null ? 'Ур. ' + card.level : '']
            .filter(Boolean).join(' · ');
        body.appendChild(title);
        const reference = document.createElement('div');
        reference.className = 'chat-pokemon-card-reference';
        reference.textContent = 'ID ' + Number(card.id || 0) + (card.shiny ? ' · Shiny' : '');
        body.appendChild(reference);

        const addField = (label, value) => {
            if (value === undefined || value === null || value === '') return;
            const line = document.createElement('div');
            line.className = 'chat-pokemon-card-line';
            line.dataset.label = label;
            line.textContent = label + ': ' + String(value);
            body.appendChild(line);
        };
        const pair = (current, maximum) => {
            if (current == null) return null;
            return maximum == null ? current : current + '/' + maximum;
        };
        const stats = card.stats || {};
        const val = (...keys) => {
            for (const key of keys) if (stats[key] != null) return stats[key];
            return null;
        };
        const sex = card.gender ?? card.sex;
        const gender = sex === 1 || sex === 'M' ? '♂' : sex === 2 || sex === 'F' ? '♀' : sex === 0 || sex === 'unknown' ? null : sex;
        addField('Пол', gender);
        addField('Характер', card.nature);
        addField('Способность', card.ability);
        addField('Тип', Array.isArray(card.types) ? card.types.filter(t => typeof t === 'string').join(' / ')
            : (typeof (card.types ?? card.type) === 'string' ? (card.types ?? card.type) : null));
        addField('Терратип', card.teraType ?? card.tera_type);
        addField('HP', pair(val('hpCurrent', 'hp_current'), val('hpMax', 'hp', 'hp_max')));
        for (const [label, keys] of [
            ['ATK', ['atk']], ['DEF', ['def']], ['SP.ATK', ['satk', 'spAtk', 'sp_atk']],
            ['SP.DEF', ['sdef', 'spDef', 'sp_def']], ['Скорость', ['speed', 'spd']]
        ]) addField(label, val(...keys));
        addField('Счастье', pair(card.happiness ?? card.happy, card.happinessMax ?? card.happiness_max));
        addField('Опыт', pair(card.experience ?? card.exp, card.experienceMax ?? card.expMax ?? card.exp_max));
        const moves = Array.isArray(card.moves) ? card.moves.slice(0, 4) : [];
        moves.forEach((move, index) => {
            if (!move || !move.name) return;
            addField('Атака ' + (index + 1), move.name + (move.pp == null ? ''
                : ' · PP ' + pair(move.pp, move.ppMax ?? move.pp_max)));
        });
        if (card.breedable === false || card.canBreed === false) addField('Ограничение', 'Не разводится');
        if (card.transferable === false || card.canTransfer === false) addField('Ограничение', 'Не передаётся');
        return wrap;
    }

    function cleanMessageText(text) {
        return String(text || '').replace(/<\/?(?:b|font)(?:\s+[^>]*)?>/gi, '').trim();
    }

    function isMessageForMe(text, authorName) {
        if (!myLogin || !text) return false;
        if (String(authorName || '').toLowerCase() === myLogin.toLowerCase()) return false;

        const escaped = escapeRegExp(myLogin);
        const directPrefix = new RegExp(`^\\s*@?${escaped}\\s*[,;:>\\-]\\s+`, 'i');
        const mentionToken = new RegExp(`(^|\\s)@${escaped}(?=\\s|[,;:.!?]|$)`, 'i');

        return directPrefix.test(text) || mentionToken.test(text);
    }

    function escapeRegExp(value) {
        return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    async function handleSend(e) {
        e.preventDefault();

        if (isPrivate && pmToId <= 0) {
            alert('Выберите получателя для личного сообщения (кликните по нику в чате).');
            return;
        }

        let text = chatInput.value.trim();
        if (!text) return;

        // В поле ввода упоминание показано ником («@NIGA»). В общем чате превращаем
        // его в [[user:id]] (сервер по нему ищет получателя), в личных сообщениях
        // просто убираем — получатель и так задан через userto.
        if (pendingMention) {
            const token = '@' + pendingMention.name;
            if (text.startsWith(token)) {
                const rest = text.slice(token.length).replace(/^\s+/, '');
                text = isPrivate ? rest : (`[[user:${pendingMention.id}]] ` + rest).trim();
            }
            pendingMention = null;
            updatePmDisplay();
        }

        if (!text) return;

        closeUserPicker(false);
        closePokemonPicker(false);

        if (!syncedInitialHistory) {
            await fetchMessages(true);
        }

        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('text', text);
        body.set('tipe', String(currentChannel));

        if (isPrivate && pmToId > 0) {
            body.set('private', '1');
            body.set('userto', String(pmToId));
        }

        chatInput.value = '';

        try {
            const response = await fetch('/api/chat/messages', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body
            });
            const data = await response.json();
            if (data.ok) {
                fetchMessages();
            } else {
                alert(data.message || 'Ошибка отправки');
                chatInput.value = text;
            }
        } catch (e) {
            console.error('Chat send error:', e);
            chatInput.value = text;
        }
    }

    function insertPokemonMention(pokemonId) {
        const id = Number(pokemonId);
        if (!Number.isSafeInteger(id) || id <= 0 || !chatInput) return false;
        const token = '#' + id;
        const value = chatInput.value;
        chatInput.value = value + (value && !/\s$/.test(value) ? ' ' : '') + token + ' ';
        closeUserPicker(false);
        closePokemonPicker(false);
        requestAnimationFrame(() => {
            chatInput.focus();
            chatInput.setSelectionRange(chatInput.value.length, chatInput.value.length);
        });
        return true;
    }

    window.Chat = { init, insertPokemonMention };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
