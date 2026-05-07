(() => {
  const root = document.querySelector('.shop-page');
  if (!root) return;

  const status = root.querySelector('[data-market-status]');
  const search = root.querySelector('[data-market-search]');
  const tabs = [...root.querySelectorAll('[data-market-tab]')];
  const panels = [...root.querySelectorAll('[data-market-panel]')];
  const buyButton = root.querySelector('[data-shop-buy]');
  const prevButton = root.querySelector('[data-shop-prev]');
  const nextButton = root.querySelector('[data-shop-next]');
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

  function activeItems() {
    const panel = root.querySelector(`[data-market-panel="${activeTab}"]`);
    return panel ? [...panel.querySelectorAll('.shop-item:not(.is-hidden)')] : [];
  }

  function setStatus(text, bad = false) {
    status.textContent = text || '';
    status.classList.toggle('is-bad', !!bad);
  }

  function formatNumber(value) {
    return Number(value || 0).toLocaleString('ru-RU');
  }

  function setTab(tab) {
    activeTab = tab;
    selected = null;
    tabs.forEach(button => button.classList.toggle('is-active', button.dataset.marketTab === tab));
    panels.forEach(panel => {
      panel.hidden = panel.dataset.marketPanel !== tab;
    });
    clearCart();
    filterCards();
  }

  function filterCards() {
    const value = (search?.value || '').trim().toLowerCase();
    root.querySelectorAll('.shop-item').forEach(card => {
      const visible = value === '' || (card.dataset.search || '').includes(value);
      card.classList.toggle('is-hidden', !visible);
    });
    pages[activeTab] = 0;
    applyPage();
  }

  function applyPage() {
    const cards = activeItems();
    const maxPage = Math.max(0, Math.ceil(cards.length / pageSize) - 1);
    pages[activeTab] = Math.max(0, Math.min(maxPage, pages[activeTab] || 0));
    cards.forEach((card, index) => {
      const page = Math.floor(index / pageSize);
      card.style.display = page === pages[activeTab] ? '' : 'none';
    });
    prevButton.disabled = pages[activeTab] <= 0;
    nextButton.disabled = pages[activeTab] >= maxPage;
  }

  function clearCart() {
    root.querySelectorAll('.shop-item.is-selected').forEach(item => item.classList.remove('is-selected'));
    selected = null;
    cartEmpty.hidden = false;
    cartCard.hidden = true;
    buyButton.disabled = true;
  }

  function selectItem(button) {
    if (button.disabled) return;
    root.querySelectorAll('.shop-item.is-selected').forEach(item => item.classList.remove('is-selected'));
    button.classList.add('is-selected');
    selected = button;

    const icon = button.querySelector('img')?.getAttribute('src') || '';
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
    cartCount.min = button.dataset.min || '1';
    cartCount.max = button.dataset.max || '99';
    cartCount.value = button.dataset.min || '1';
    cartEmpty.hidden = true;
    cartCard.hidden = false;
    buyButton.disabled = false;
    updateTotal();
  }

  function updateTotal() {
    if (!selected) {
      cartTotal.textContent = '0';
      return;
    }
    const count = Math.max(Number(cartCount.min || 1), Math.min(Number(cartCount.max || 1), Number(cartCount.value || 1)));
    if (String(count) !== cartCount.value) {
      cartCount.value = String(count);
    }
    const price = Number(selected.dataset.price || 0) * count;
    cartTotal.textContent = `${formatNumber(price)} ${String(selected.dataset.currency || 'монет').toLowerCase()}`;
  }

  function updateWallet(wallet) {
    if (!wallet) return;
    const coins = root.querySelector('[data-wallet-coins]');
    const diamonds = root.querySelector('[data-wallet-diamonds]');
    if (coins && Number.isFinite(Number(wallet.coins ?? wallet.count))) {
      const value = wallet.coins ?? (wallet.currency_item_id === 1 ? wallet.count : null);
      if (value !== null) coins.textContent = formatNumber(value);
    }
    if (diamonds && Number.isFinite(Number(wallet.diamonds))) {
      diamonds.textContent = formatNumber(wallet.diamonds);
    }
  }

  async function buySelected() {
    if (!selected) return;

    const body = new URLSearchParams();
    body.set('_csrf', root.dataset.csrf || '');
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
          setTimeout(() => window.location.reload(), 450);
          return;
        }
        const bought = Number(cartCount.value || 1);
        selected.dataset.owned = String(Number(selected.dataset.owned || 0) + bought);
        cartOwned.textContent = `У тебя есть: ${formatNumber(selected.dataset.owned)}`;
      }
    } catch (error) {
      setStatus('Ошибка сети при покупке.', true);
    } finally {
      if (selected) buyButton.disabled = false;
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

  setTab('catalog');
})();
