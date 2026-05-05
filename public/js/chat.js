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
    let currentChannel = 1; // All
    let isPrivate = false;
    let pmToId = 0;
    let pmToName = '';
    let currentLocationId = 0;
    let userMenu = null;
    let autoScroll = true;
    let syncedInitialHistory = false;

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
        
        // Start polling from the current moment without loading old history.
        fetchMessages(true);
        setInterval(fetchMessages, 3000);
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
                currentChannel = parseInt(tipe);
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

            const id = parseInt(author.dataset.id);
            const name = author.textContent;

            if (id > 0) {
                showUserMenu(id, name, e);
            }
        });

        document.addEventListener('click', hideUserMenu);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') hideUserMenu();
        });
    }

    function createUserMenu() {
        userMenu = document.createElement('div');
        userMenu.className = 'chat-user-menu';
        userMenu.hidden = true;
        userMenu.innerHTML = `
            <div class="chat-user-menu-title">Системное уведомление:</div>
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

            const id = parseInt(userMenu.dataset.userId || '0');
            const name = userMenu.dataset.userName || '';
            handleUserMenuAction(button.dataset.action, id, name);
        });
    }

    function showUserMenu(id, name, event) {
        if (!userMenu) return;

        userMenu.dataset.userId = String(id);
        userMenu.dataset.userName = name;
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

    function handleUserMenuAction(action, id, name) {
        hideUserMenu();

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
            window.location.href = `/game/messages?to=${encodeURIComponent(id)}`;
            return;
        }

        const labels = {
            info: 'Информация об игроке пока не подключена.',
            friend: 'Добавление в друзья пока не подключено.',
            ignore: 'Игнорирование пока не подключено.'
        };
        alert(labels[action] || 'Действие пока не подключено.');
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

    let allMessages = [];

    async function fetchMessages(syncOnly = false) {
        try {
            // Check if global state.locationId has changed
            if (window.state && window.state.locationId !== currentLocationId) {
                currentLocationId = window.state.locationId;
                lastId = 0;
                allMessages = [];
                chatLog.innerHTML = '';
                syncedInitialHistory = false;
            }

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
                // If it's a fresh load (afterId=0), we replace messages
                if (lastId === 0) {
                    allMessages = data.messages;
                } else {
                    allMessages = allMessages.concat(data.messages);
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

        if (msg.author_id > 0 || msg.author_name === 'System') {
            const authorSpan = document.createElement('span');
            authorSpan.className = 'chat-author';
            authorSpan.dataset.id = msg.author_id;
            authorSpan.textContent = msg.author_name;
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
        textSpan.textContent = text; // XSS Protection
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
                chatInput.value = text; // Return text on error
            }
        } catch (e) {
            console.error('Chat send error:', e);
            chatInput.value = text;
        }
    }

    // Export to global if needed
    window.Chat = { init };

    // Auto-init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
