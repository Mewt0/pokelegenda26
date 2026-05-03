<?php
use Pokemon8\View\View;
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Покемоны - Pokemon 8.0</title>
  <style>
    body { margin:0; background:#e7ebef; color:#172033; font:14px/1.4 Tahoma, Arial, sans-serif; }
    main { max-width:1100px; margin:0 auto; padding:16px; }
    a { color:#1d5faf; font-weight:700; text-decoration:none; }
    .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:12px; margin-top:14px; }
    .card { background:#fff; border:1px solid #c8d2df; border-radius:8px; padding:12px; }
    .head { display:flex; justify-content:space-between; gap:10px; align-items:center; }
    .hp { height:8px; background:#d8e0e8; border-radius:99px; overflow:hidden; margin:8px 0; }
    .hp i { display:block; height:100%; background:#34a853; }
    .slots { display:grid; gap:8px; margin-top:10px; }
    .slot { display:grid; grid-template-columns:24px minmax(0,1fr); gap:8px; align-items:center; }
    select { width:100%; min-width:0; height:32px; border:1px solid #a8b4c2; border-radius:5px; background:#fff; }
    .status { margin-top:10px; min-height:20px; color:#526173; font-weight:700; }
  </style>
</head>
<body data-csrf="<?= View::e($csrf) ?>">
  <main>
    <a href="/game">Игровой мир</a>
    <h1>Покемоны</h1>
    <p>Здесь меняются боевые атаки. В бою используются именно эти четыре слота.</p>
    <section class="grid" id="pokemonGrid"></section>
    <div class="status" id="status">Загрузка...</div>
  </main>
  <script>
    const csrf = document.body.dataset.csrf;
    const grid = document.getElementById('pokemonGrid');
    const status = document.getElementById('status');
    const slots = ['a', 'b', 'c', 'd'];

    function setStatus(text, bad = false) {
      status.textContent = text;
      status.style.color = bad ? '#a4161a' : '#526173';
    }

    async function load() {
      const response = await fetch('/api/pokemon/moves', { credentials: 'same-origin' });
      const payload = await response.json();
      if (!payload || payload.ok !== true) {
        setStatus('Не удалось загрузить покемонов.', true);
        return;
      }
      render(payload.pokemon || []);
      setStatus('Готово');
    }

    function render(pokemon) {
      grid.innerHTML = '';
      for (const poke of pokemon) {
        const card = document.createElement('article');
        card.className = 'card';
        const hp = Math.max(0, Math.min(100, (Number(poke.hp || 0) / Math.max(1, Number(poke.hpMax || 1))) * 100));
        card.innerHTML = '<div class="head"><b></b><span></span></div><div class="hp"><i></i></div><div class="slots"></div>';
        card.querySelector('b').textContent = '#' + poke.name + ' Lv.' + poke.level;
        card.querySelector('span').textContent = 'HP ' + poke.hp + '/' + poke.hpMax;
        card.querySelector('.hp i').style.width = hp + '%';
        const slotBox = card.querySelector('.slots');
        for (const slot of slots) {
          const row = document.createElement('label');
          row.className = 'slot';
          row.innerHTML = '<b></b><select></select>';
          row.querySelector('b').textContent = slot.toUpperCase();
          const select = row.querySelector('select');
          const empty = document.createElement('option');
          empty.value = '0';
          empty.textContent = 'Нет атаки';
          if (!Number(poke.moves && poke.moves[slot] && poke.moves[slot].id)) {
            empty.selected = true;
          }
          select.appendChild(empty);
          for (const move of poke.learnableMoves || []) {
            const option = document.createElement('option');
            option.value = String(move.id);
            option.textContent = move.name + ' / Lv.' + move.level;
            if (Number(poke.moves && poke.moves[slot] && poke.moves[slot].id) === Number(move.id)) {
              option.selected = true;
            }
            select.appendChild(option);
          }
          select.addEventListener('change', () => setMove(poke.id, slot, select.value));
          slotBox.appendChild(row);
        }
        grid.appendChild(card);
      }
    }

    async function setMove(pokemonId, slot, moveId) {
      const body = new URLSearchParams();
      body.set('_csrf', csrf);
      body.set('pokemon_id', pokemonId);
      body.set('slot', slot);
      body.set('move_id', moveId);
      const response = await fetch('/api/pokemon/move', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body
      });
      const payload = await response.json();
      setStatus(payload && payload.message ? payload.message : 'Готово', !(payload && payload.ok));
      if (payload && payload.ok) load();
    }

    load();
  </script>
</body>
</html>
