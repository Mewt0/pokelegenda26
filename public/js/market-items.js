(() => {
  const root = document.querySelector('.market-page');
  if (!root) return;

  const status = root.querySelector('[data-market-status]');
  const search = root.querySelector('[data-market-search]');
  const tabs = [...root.querySelectorAll('[data-market-tab]')];
  const panels = [...root.querySelectorAll('[data-market-panel]')];
  let activeTab = 'catalog';

  function setStatus(text, bad = false) {
    status.textContent = text || '';
    status.classList.toggle('is-bad', !!bad);
  }

  function setTab(tab) {
    activeTab = tab;
    tabs.forEach(button => button.classList.toggle('is-active', button.dataset.marketTab === tab));
    panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.marketPanel === tab));
    filterCards();
  }

  function filterCards() {
    const value = (search?.value || '').trim().toLowerCase();
    const panel = root.querySelector(`[data-market-panel="${activeTab}"]`);
    if (!panel) return;

    panel.querySelectorAll('.market-card').forEach(card => {
      const name = card.dataset.cardName || '';
      const id = card.dataset.cardId || '';
      const visible = value === '' || name.includes(value) || id.includes(value);
      card.classList.toggle('is-hidden', !visible);
    });
  }

  function updateWallet(wallet) {
    if (!wallet) return;
    const coins = root.querySelector('[data-wallet-coins]');
    const diamonds = root.querySelector('[data-wallet-diamonds]');
    if (coins && Number.isFinite(Number(wallet.coins ?? wallet.count))) {
      const value = wallet.coins ?? (wallet.currency_item_id === 1 ? wallet.count : null);
      if (value !== null) coins.textContent = Number(value).toLocaleString('ru-RU');
    }
    if (diamonds && Number.isFinite(Number(wallet.diamonds))) {
      diamonds.textContent = Number(wallet.diamonds).toLocaleString('ru-RU');
    }
  }

  async function buy(button, source) {
    const card = button.closest('.market-card');
    const input = card?.querySelector('input');
    const body = new URLSearchParams();
    body.set('_csrf', root.dataset.csrf || '');
    body.set('source', source);
    body.set('count', input?.value || '1');
    if (source === 'lot') {
      body.set('lot_id', button.dataset.buyLot || '0');
    } else {
      body.set('shop_item_id', button.dataset.buyCatalog || '0');
    }

    button.disabled = true;
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
        if (source === 'lot') {
          setTimeout(() => window.location.reload(), 450);
        }
      }
    } catch (error) {
      setStatus('Ошибка сети при покупке.', true);
    } finally {
      if (source !== 'lot') button.disabled = false;
    }
  }

  tabs.forEach(button => {
    button.addEventListener('click', () => setTab(button.dataset.marketTab || 'catalog'));
  });

  search?.addEventListener('input', filterCards);

  root.addEventListener('click', event => {
    const catalogButton = event.target.closest('[data-buy-catalog]');
    if (catalogButton) {
      buy(catalogButton, 'catalog');
      return;
    }

    const lotButton = event.target.closest('[data-buy-lot]');
    if (lotButton) {
      buy(lotButton, 'lot');
    }
  });
})();
