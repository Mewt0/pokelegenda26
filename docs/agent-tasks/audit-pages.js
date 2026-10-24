// Аудит страниц игры в реальном браузере: консольные ошибки, исключения, битые картинки,
// горизонтальное переполнение. Работает через CDP без внешних зависимостей.
//
// Использование:
//   node docs/agent-tasks/audit-pages.js <sessionCookie> [baseUrl]
//
// Требует запущенный headless Chrome с --remote-debugging-port=9222 (см. план, раздел 10).

const [, , cookie, base = 'http://pokemonchic.com', viewport = '1440x900'] = process.argv;
const [viewW, viewH] = viewport.split('x').map(Number);

const pages = [
  '/game', '/game/admin', '/game/items', '/game/pokemon', '/game/quests',
  '/game/commission', '/game/messages', '/game/eggs', '/game/events',
  '/game/tournaments', '/game/transport', '/game/diamond-shop', '/game/profile',
];

const version = await (await fetch('http://127.0.0.1:9222/json/version')).json();
const ws = new WebSocket(version.webSocketDebuggerUrl);
let nextId = 1;
const pending = new Map();
let consoleEvents = [];

ws.addEventListener('message', ev => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) {
    const { resolve, reject } = pending.get(msg.id);
    pending.delete(msg.id);
    msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
    return;
  }
  if (msg.method === 'Runtime.consoleAPICalled' && ['error', 'warning'].includes(msg.params.type)) {
    consoleEvents.push({
      type: msg.params.type,
      text: (msg.params.args || []).map(a => a.value ?? a.description ?? a.type).join(' ').slice(0, 200),
    });
  }
  if (msg.method === 'Runtime.exceptionThrown') {
    const d = msg.params.exceptionDetails || {};
    consoleEvents.push({ type: 'exception', text: String(d.exception?.description || d.text || '').slice(0, 220) });
  }
});

const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
  const id = nextId++;
  pending.set(id, { resolve, reject });
  ws.send(JSON.stringify({ id, method, params, ...(sessionId ? { sessionId } : {}) }));
});

await new Promise(r => ws.addEventListener('open', r, { once: true }));

let problems = 0;

for (const page of pages) {
  consoleEvents = [];
  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  await send('Page.enable', {}, sessionId);
  await send('Runtime.enable', {}, sessionId);
  await send('Network.enable', {}, sessionId);
  await send('Network.setCacheDisabled', { cacheDisabled: true }, sessionId);
  await send('Emulation.setDeviceMetricsOverride', { width: viewW, height: viewH, deviceScaleFactor: 1, mobile: viewW < 800 }, sessionId);
  await send('Network.setCookie', { name: 'PHPSESSID', value: cookie, domain: 'pokemonchic.com', path: '/', httpOnly: true }, sessionId);
  await send('Page.navigate', { url: base + page }, sessionId);
  await new Promise(r => setTimeout(r, 3500));

  const probe = await send('Runtime.evaluate', {
    expression: `JSON.stringify({
      title: document.title,
      overflowX: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
      brokenImages: [...document.querySelectorAll('img')].filter(i => (i.getAttribute('src') || '').trim() !== '' && i.complete && i.naturalWidth === 0).map(i => i.getAttribute('src')),
      emptySrcImages: [...document.querySelectorAll('img')].filter(i => (i.getAttribute('src') || '').trim() === '').length,
      bodyFont: getComputedStyle(document.body).fontFamily.split(',')[0],
      innerScrollersX: [...document.querySelectorAll('*')].filter(el => {
        const cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden') return false;
        // Только видимые пользователю скроллбары: auto/scroll.
        if (!['auto', 'scroll'].includes(cs.overflowX)) return false;
        // Осознанное усечение текста многоточием — это не баг.
        if (cs.textOverflow === 'ellipsis') return false;
        return el.scrollWidth - el.clientWidth > 4;
      }).map(el => (el.className ? '.' + String(el.className).trim().split(/\s+/)[0] : el.tagName.toLowerCase())).slice(0, 5),
      clippedX: [...document.querySelectorAll('*')].filter(el => {
        const cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden') return false;
        if (cs.overflowX !== 'hidden' || cs.textOverflow === 'ellipsis') return false;
        return el.scrollWidth - el.clientWidth > 8;
      }).map(el => (el.className ? '.' + String(el.className).trim().split(/\s+/)[0] : el.tagName.toLowerCase())).slice(0, 5),
      interLoaded: document.fonts ? document.fonts.check('14px Inter') : null
    })`,
    returnByValue: true,
  }, sessionId);

  const data = JSON.parse(probe.result.value);
  const errors = consoleEvents.filter(e => e.type !== 'warning');
  const warnings = consoleEvents.filter(e => e.type === 'warning');
  const bad = data.overflowX || data.brokenImages.length > 0 || errors.length > 0 || data.innerScrollersX.length > 0;

  if (bad) problems++;
  console.log(`${bad ? '[FAIL]' : '[ ok ]'} ${page.padEnd(20)} overflow=${data.overflowX} inner_scroll=${data.innerScrollersX.length} broken_img=${data.brokenImages.length} errors=${errors.length} warnings=${warnings.length} inter=${data.interLoaded} font=${data.bodyFont}`);

  if (data.innerScrollersX.length) console.log('        внутренний скролл по X: ' + data.innerScrollersX.join(', '));
  if (data.clippedX.length) console.log('        обрезано без многоточия: ' + data.clippedX.join(', '));
  if (data.brokenImages.length) console.log('        битые картинки: ' + data.brokenImages.slice(0, 6).join(', '));
  errors.slice(0, 4).forEach(e => console.log(`        ${e.type}: ${e.text}`));
  warnings.slice(0, 2).forEach(e => console.log(`        warn: ${e.text}`));

  await send('Target.closeTarget', { targetId });
}

console.log(`\nСтраниц проверено: ${pages.length}, с проблемами: ${problems}`);
ws.close();
process.exit(problems === 0 ? 0 : 1);
