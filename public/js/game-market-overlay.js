(() => {
  const overlay = document.getElementById('marketOverlay');
  if (!overlay) return;

  const root = overlay;
  const csrf = document.querySelector('.world[data-csrf]')?.dataset.csrf || '';
  const closeButtons = [...root.querySelectorAll('[data-market-close]')];
  const status = root.querySelector('[data-market-status]');
  const search = root.querySelector('[data-market-search]');
  const tabs = [...root.querySelectorAll('[data-market-tab]')];
  const panels = [...root.querySelectorAll('[data-market-panel]')];
  const buyButton = root.querySelector('[data-shop-buy]');
  const prevButton = root.querySelector('[data-shop-prev]');
  const nextButton = root.querySelector('[data-shop-next]');
  const pageInfo = root.querySelector('[data-market-page-info]');
  const slotsCount = root.querySelector('[data-market-slots]');
  const cartEmpty = root.querySelector('[data-cart-empty]');
  const cartCard = root.querySelector('[data-cart-card]');
  const cartIcon = root.querySelector('[data-cart-icon]');
  const cartName = root.querySelector('[data-cart-name]');
  const cartDescription = root.querySelector('[data-cart-description]');
  const cartOwned = root.querySelector('[data-cart-owned]');
  const cartCount = root.querySelector('[data-cart-count]');
  const cartTotal = root.querySelector('[data-cart-total]');

  const pageSize = 21;
  const pages = { catalog: 0, lots: 0 };
  let activeTab = 'catalog';
  let selected = null;
  let loaded = false;

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('ru-RU');
  }

  function setStatus(text, bad = false) {
    if (!status) return;
    status.textContent = text || '';
    status.classList.toggle('is-bad', !!bad);
  }

  function open() {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('has-market-overlay');
    loadMarket();
    setTimeout(() => search?.focus(), 30);
  }

  function close() {
    overlay.classList.remove('is-open');
    overlay.classList.remove('has-selection');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('has-market-overlay');
    clearCart();
    setStatus('');
  }

  function activeItems() {
    const panel = root.querySelector(`[data-market-panel="${activeTab}"]`);
    return panel ? [...panel.querySelectorAll('.shop-item:not(.is-hidden)')] : [];
  }

  function setTab(tab) {
    activeTab = tab === 'lots' ? 'lots' : 'catalog';
    selected = null;
    tabs.forEach(button => button.classList.toggle('is-active', button.dataset.marketTab === activeTab));
    panels.forEach(panel => {
      panel.hidden = panel.dataset.marketPanel !== activeTab;
    });
    clearCart();
    filterCards();
  }

  async function loadMarket(force = false) {
    if (loaded && !force) {
      setTab(activeTab);
      return;
    }

    setStatus('Загрузка товаров...');
    try {
      const response = await fetch('/api/market/items', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      const data = await response.json();
      if (!data || data.ok !== true) {
        setStatus(data?.message || 'Не удалось загрузить магазин.', true);
        return;
      }

      renderGrid('catalog', data.catalog || []);
      renderGrid('lots', data.lots || []);
      updateWallet(data.wallet);
      loaded = true;
      setStatus('');
      setTab('catalog');
    } catch (error) {
      setStatus('Ошибка сети при загрузке магазина.', true);
    }
  }

  function renderGrid(type, items) {
    const grid = root.querySelector(`[data-shop-grid="${type}"]`);
    const empty = root.querySelector(`[data-market-empty="${type}"]`);
    if (!grid) return;

    grid.replaceChildren();
    items.forEach(item => grid.appendChild(createItemButton(type, item)));
    if (empty) {
      empty.hidden = items.length > 0;
    }
  }

  function createItemButton(type, item) {
    const isLot = type === 'lots';
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'shop-item';
    button.dataset.source = isLot ? 'lot' : 'catalog';
    button.dataset.id = String(isLot ? item.id_lot : item.id);
    button.dataset.itemId = String(item.item_id || 0);
    button.dataset.name = String(item.name || 'Предмет');
    button.dataset.price = String(isLot ? item.unit_price : item.price);
    button.dataset.currency = String(item.currency_name || 'Монета');
    button.dataset.min = String(item.min_count || 1);
    button.dataset.max = String(isLot ? item.count : item.max_count || 99);
    button.dataset.maxOwned = String(item.max_owned || 0);
    button.dataset.owned = String(isLot ? item.count : item.owned || 0);
    button.dataset.description = String(item.description || 'Описание пока не заполнено.');
    button.dataset.seller = String(item.seller_login || '');
    button.dataset.search = `${button.dataset.name} ${button.dataset.itemId}`.toLowerCase();

    const maxOwned = Number(button.dataset.maxOwned || 0);
    const owned = Number(button.dataset.owned || 0);
    if ((isLot && item.is_own) || (!isLot && maxOwned > 0 && owned >= maxOwned)) {
      button.disabled = true;
    }

    const art = document.createElement('span');
    art.className = 'shop-art';
    const img = document.createElement('img');
    img.src = String(item.icon || '/public/img/items/3.png');
    img.alt = '';
    art.appendChild(img);

    const price = document.createElement('span');
    price.className = 'shop-price';
    const priceText = document.createElement('b');
    priceText.textContent = formatNumber(button.dataset.price);
    const coin = document.createElement('img');
    coin.src = '/public/img/items/1.png';
    coin.alt = '';
    price.append(priceText, coin);

    button.append(art, price);
    return button;
  }

  function filterCards() {
    const value = (search?.value || '').trim().toLowerCase();
    root.querySelectorAll('.shop-item').forEach(card => {
      const visible = value === '' || (card.dataset.search || '').includes(value);
      card.classList.toggle('is-hidden', !visible);
    });
    if (selected && selected.classList.contains('is-hidden')) {
      clearCart();
    }
    pages[activeTab] = 0;
    applyPage();
  }

  function applyPage() {
    const cards = activeItems();
    const panel = root.querySelector(`[data-market-panel="${activeTab}"]`);
    const grid = root.querySelector(`[data-shop-grid="${activeTab}"]`);
    if (!grid || !panel) return;

    grid.querySelectorAll('.shop-slot-empty').forEach(slot => slot.remove());
    panel.querySelectorAll('.shop-item.is-hidden').forEach(card => {
      card.style.display = 'none';
    });

    const maxPage = Math.max(0, Math.ceil(cards.length / pageSize) - 1);
    pages[activeTab] = Math.max(0, Math.min(maxPage, pages[activeTab] || 0));
    cards.forEach((card, index) => {
      const page = Math.floor(index / pageSize);
      const visible = page === pages[activeTab];
      card.style.display = visible ? '' : 'none';
    });

    if (prevButton) prevButton.disabled = pages[activeTab] <= 0;
    if (nextButton) nextButton.disabled = pages[activeTab] >= maxPage;
    if (pageInfo) pageInfo.textContent = `${pages[activeTab] + 1}/${maxPage + 1}`;
    if (slotsCount) slotsCount.textContent = String(cards.length);
    const empty = root.querySelector(`[data-market-empty="${activeTab}"]`);
    if (empty) {
      const value = (search?.value || '').trim();
      empty.textContent = cards.length > 0
        ? ''
        : (value !== '' ? 'По такому запросу ничего не найдено.' : (activeTab === 'lots' ? 'В твоём регионе нет активных лотов игроков.' : 'В магазине пока нет активных товаров.'));
      empty.hidden = cards.length > 0;
    }
  }

  function clearCart() {
    root.querySelectorAll('.shop-item.is-selected').forEach(item => item.classList.remove('is-selected'));
    root.classList.remove('has-selection');
    selected = null;
    if (cartEmpty) cartEmpty.hidden = false;
    if (cartCard) cartCard.hidden = true;
    if (buyButton) buyButton.disabled = true;
  }

  function selectItem(button) {
    if (button.disabled) return;
    root.querySelectorAll('.shop-item.is-selected').forEach(item => item.classList.remove('is-selected'));
    button.classList.add('is-selected');
    root.classList.add('has-selection');
    selected = button;

    const icon = button.querySelector('.shop-art img')?.getAttribute('src') || '/public/img/items/3.png';
    cartIcon.src = icon;
    cartName.textContent = button.dataset.name || 'Предмет';
    cartDescription.textContent = button.dataset.description || 'Описание пока не заполнено.';

    if (button.dataset.source === 'lot') {
      cartOwned.textContent = `Продавец: ${button.dataset.seller || 'игрок'} • доступно: ${formatNumber(button.dataset.max)}`;
    } else {
      const maxOwned = Number(button.dataset.maxOwned || 0);
      cartOwned.textContent = maxOwned > 0
        ? `У тебя есть: ${formatNumber(button.dataset.owned)} • лимит: ${formatNumber(maxOwned)}`
        : `У тебя есть: ${formatNumber(button.dataset.owned)}`;
    }

    const remaining = remainingLimit(button);
    cartCount.min = button.dataset.min || '1';
    cartCount.max = String(Math.max(1, Math.min(Number(button.dataset.max || 1), remaining)));
    cartCount.value = cartCount.min;
    cartEmpty.hidden = true;
    cartCard.hidden = false;
    buyButton.disabled = remaining <= 0;
    updateTotal();
  }

  function remainingLimit(button) {
    const max = Number(button.dataset.max || 1);
    const maxOwned = Number(button.dataset.maxOwned || 0);
    if (button.dataset.source === 'lot' || maxOwned <= 0) {
      return max;
    }
    return Math.max(0, Math.min(max, maxOwned - Number(button.dataset.owned || 0)));
  }

  function updateTotal() {
    if (!selected) {
      cartTotal.textContent = '0';
      return;
    }

    const min = Number(cartCount.min || 1);
    const max = Number(cartCount.max || 1);
    const count = Math.max(min, Math.min(max, Number(cartCount.value || min)));
    if (String(count) !== cartCount.value) {
      cartCount.value = String(count);
    }
    cartTotal.textContent = `${formatNumber(Number(selected.dataset.price || 0) * count)} ${String(selected.dataset.currency || 'монет').toLowerCase()}`;
  }

  function updateWallet(wallet) {
    if (!wallet) return;
    const coins = root.querySelector('[data-wallet-coins]');
    const diamonds = root.querySelector('[data-wallet-diamonds]');

    if (coins) {
      const value = wallet.coins ?? (Number(wallet.currency_item_id) === 1 ? wallet.count : null);
      if (value !== null && value !== undefined) coins.textContent = formatNumber(value);
    }
    if (diamonds && wallet.diamonds !== undefined) {
      diamonds.textContent = formatNumber(wallet.diamonds);
    }
  }

  async function buySelected() {
    if (!selected) return;

    const body = new URLSearchParams();
    body.set('_csrf', csrf);
    body.set('source', selected.dataset.source || 'catalog');
    body.set('count', cartCount.value || '1');
    if (selected.dataset.source === 'lot') {
      body.set('lot_id', selected.dataset.id || '0');
    } else {
      body.set('shop_item_id', selected.dataset.id || '0');
    }

    buyButton.disabled = true;
    setStatus('Покупка...');
    try {
      const response = await fetch('/api/market/items/buy', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          'Accept': 'application/json'
        },
        body
      });
      const data = await response.json();
      setStatus(data.message || 'Готово.', !(data && data.ok));
      if (data && data.ok) {
        updateWallet(data.wallet);
        if (selected.dataset.source === 'lot') {
          loaded = false;
          await loadMarket(true);
          return;
        }

        const bought = Number(cartCount.value || 1);
        selected.dataset.owned = String(Number(selected.dataset.owned || 0) + bought);
        if (remainingLimit(selected) <= 0) {
          selected.disabled = true;
          clearCart();
          return;
        }
        selectItem(selected);
      }
    } catch (error) {
      setStatus('Ошибка сети при покупке.', true);
    } finally {
      if (selected && remainingLimit(selected) > 0) {
        buyButton.disabled = false;
      }
    }
  }

  tabs.forEach(button => {
    button.addEventListener('click', () => setTab(button.dataset.marketTab || 'catalog'));
  });

  root.addEventListener('click', event => {
    const item = event.target.closest('.shop-item');
    if (item && root.contains(item)) {
      selectItem(item);
    }
  });

  document.querySelectorAll('[data-open-market]').forEach(link => {
    link.addEventListener('click', event => {
      event.preventDefault();
      open();
    });
  });

  closeButtons.forEach(button => button.addEventListener('click', close));
  search?.addEventListener('input', filterCards);
  cartCount?.addEventListener('input', updateTotal);
  buyButton?.addEventListener('click', buySelected);
  prevButton?.addEventListener('click', () => {
    pages[activeTab] = Math.max(0, (pages[activeTab] || 0) - 1);
    applyPage();
  });
  nextButton?.addEventListener('click', () => {
    pages[activeTab] = (pages[activeTab] || 0) + 1;
    applyPage();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
      close();
    }
  });

  window.GameMarketOverlay = { open, close, reload: () => loadMarket(true) };
})();
