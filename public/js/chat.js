/**
 * Pokemon 8.0 Chat Component (Vanilla JS)
 */
(function() {
    const chatLog = document.getElementById('chatLog');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const app = document.querySelector('.world');
    const csrf = app ? app.dataset.csrf : '';

    let lastId = 0;
    let currentChannel = 1; // All
    let isPrivate = false;
    let pmToId = 0;
    let pmToName = '';

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

        // Start polling
        fetchMessages();
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

        // Click on username in chat
        chatLog.addEventListener('click', (e) => {
            const author = e.target.closest('.chat-author');
            if (!author) return;

            const id = parseInt(author.dataset.id);
            const name = author.textContent;

            if (id > 0) {
                pmToId = id;
                pmToName = name;
                updatePmDisplay();

                // Switch to private tab if not already there
                const privTab = document.querySelector('.chat-tab[data-tipe="private"]');
                if (privTab) privTab.click();

                chatInput.focus();
            }
        });
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

    async function fetchMessages() {
        try {
            const response = await fetch(`/api/chat/messages?after_id=${lastId}`, { credentials: 'same-origin' });
            const data = await response.json();

            if (data.ok && data.messages.length > 0) {
                allMessages = allMessages.concat(data.messages);
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
                // В общем показываем общие + системные (кроме боев/торга если нужно фильтровать, но обычно в общем всё)
                return msg.private === false;
            }

            return msg.private === false && msg.tipe === currentChannel;
        });

        filtered.forEach(appendMessage);
        chatLog.scrollTop = chatLog.scrollHeight;
    }

    function appendMessage(msg) {
        const div = document.createElement('div');
        div.className = 'chat-row';
        if (msg.private) div.classList.add('is-private');
        if (msg.tipe === TIPE_SYSTEM) div.classList.add('is-system');

        const time = new Date(msg.time * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const timeSpan = document.createElement('span');
        timeSpan.className = 'chat-time';
        timeSpan.textContent = `[${time}] `;
        div.appendChild(timeSpan);

        if (msg.author_id > 0) {
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
        textSpan.textContent = msg.text; // XSS Protection
        div.appendChild(textSpan);

        chatLog.appendChild(div);
    }

    async function handleSend(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

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
            }
        } catch (e) {
            console.error('Chat send error:', e);
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
