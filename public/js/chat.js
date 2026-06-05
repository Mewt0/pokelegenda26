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
    const myLogin = myLoginInput ? myLoginInput.value.trim() : '';

    let lastId = 0;
    let currentChannel = 1;
    let isPrivate = false;
    let pmToId = 0;
    let pmToName = '';
    let currentLocationId = readLocationId();
    let userMenu = null;
    let chatHoverProfileTimer = 0;
    let chatHoverProfileKey = '';
    let chatHoverProfileOpenedAt = 0;
    let autoScroll = true;
    let syncedInitialHistory = false;
    let allMessages = [];

    const TIPE_ALL = 1;
    const TIPE_SYSTEM = 2;
    const TIPE_BATTLE = 3;
    const TIPE_TRADE = 4;
    const TIPE_CLAN = 5;

    function init() {
        if (!chatLog || !chatForm) return;

        createTabs();
        setupPmTarget();
        chatForm.addEventListener('submit', handleSend);

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
    }

    function createTabs() {
        const tabsContainer = document.createElement('div');
        tabsContainer.className = 'chat-tabs';
        tabsContainer.innerHTML = `
            <button type="button" class="chat-tab active" data-tipe="1">Общий</button>
            <button type="button" class="chat-tab" data-tipe="4">Торг</button>
            <button type="button" class="chat-tab" data-tipe="3">Бой</button>
            <button type="button" class="chat-tab" data-tipe="5">Клан</button>
            <button type="button" class="chat-tab" data-tipe="private">Приват</button>
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
            <button type="button" class="chat-tool-btn" data-chat-tool="clear" title="Очистить чат">×</button>
            <button type="button" class="chat-tool-btn is-active" data-chat-tool="scroll" title="Автопрокрутка включена">⇣</button>
        `;
        tabsContainer.appendChild(tools);

        const clearIcon = tools.querySelector('[data-chat-tool="clear"]');
        const scrollIcon = tools.querySelector('[data-chat-tool="scroll"]');
        if (clearIcon) {
            clearIcon.innerHTML = '<img src="/public/img/ui/chat/clear.png" alt="">';
        }
        if (scrollIcon) {
            scrollIcon.innerHTML = '<img src="/public/img/ui/chat/autoscroll-on.png" alt="">';
        }

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
                const icon = button.querySelector('img');
                if (icon) {
                    icon.src = autoScroll ? '/public/img/ui/chat/autoscroll-on.png' : '/public/img/ui/chat/autoscroll-off.png';
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
            <button type="button" id="pmClearBtn">×</button>
        `;
        chatForm.insertBefore(pmTarget, chatForm.firstChild);

        document.getElementById('pmClearBtn').addEventListener('click', () => {
            pmToId = 0;
            pmToName = '';
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

            if (detail.action === 'dialog') {
                switchToPublicChat();
                insertPublicReply(name);
                return;
            }

            if (detail.action === 'private') {
                if (id > 0) {
                    pmToId = id;
                    pmToName = name;
                    const privTab = document.querySelector('.chat-tab[data-tipe="private"]');
                    if (privTab) privTab.click();
                    updatePmDisplay();
                } else {
                    switchToPublicChat();
                    insertPublicReply(name);
                }
                chatInput.focus();
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
            <button type="button" data-action="info">Информация</button>
            <button type="button" data-action="reply">Написать</button>
            <button type="button" data-action="private">Написать ЛС</button>
            <button type="button" data-action="friend">Дружить</button>
            <button type="button" data-action="ignore">Игнорировать</button>
            <button type="button" data-action="mail">Написать на почту</button>
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
            replyButton.textContent = isSystem ? 'Ответить в чат' : 'Написать';
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

        if (action === 'reply') {
            switchToPublicChat();
            insertPublicReply(name);
            return;
        }

        if (action === 'private') {
            pmToId = id;
            pmToName = name;
            const privTab = document.querySelector('.chat-tab[data-tipe="private"]');
            if (privTab) privTab.click();
            updatePmDisplay();
            chatInput.focus();
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

    function updatePmDisplay() {
        const display = document.getElementById('pmTargetDisplay');
        const nameNode = document.getElementById('pmTargetName');

        if (isPrivate && pmToId > 0) {
            display.style.display = 'flex';
            nameNode.textContent = pmToName;
        } else {
            display.style.display = 'none';
        }
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

            if (data.messages.length > 0) {
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
        if (!msg.private && isMessageForMe(text, msg.author_name)) div.classList.add('is-mention');
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
        textSpan.textContent = text;
        div.appendChild(textSpan);

        chatLog.appendChild(div);
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

        const text = chatInput.value.trim();
        if (!text) return;

        if (!syncedInitialHistory) {
            await fetchMessages(true);
        }

        const body = new URLSearchParams();
        body.set('_csrf', csrf);
        body.set('text', text);
        body.set('tipe', currentChannel);

        if (isPrivate && pmToId > 0) {
            body.set('private', '1');
            body.set('userto', pmToId);
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

    window.Chat = { init };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
