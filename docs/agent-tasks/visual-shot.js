// Скриншот страницы через Chrome DevTools Protocol (без внешних зависимостей).
// Использование: node shot.js <url> <out.png> [width] [height] [sessionCookie]
const [, , url, out, w = '1440', h = '900', cookie = ''] = process.argv;

const listTargets = await (await fetch('http://127.0.0.1:9222/json/version')).json();
const ws = new WebSocket(listTargets.webSocketDebuggerUrl);
let nextId = 1;
const pending = new Map();
const events = [];

ws.addEventListener('message', ev => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) {
    const { resolve, reject } = pending.get(msg.id);
    pending.delete(msg.id);
    msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
  } else if (msg.method) {
    events.push(msg);
  }
});

const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
  const id = nextId++;
  pending.set(id, { resolve, reject });
  ws.send(JSON.stringify({ id, method, params, ...(sessionId ? { sessionId } : {}) }));
});

await new Promise(r => ws.addEventListener('open', r, { once: true }));

const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });

await send('Page.enable', {}, sessionId);
await send('Network.enable', {}, sessionId);
await send('Network.setCacheDisabled', { cacheDisabled: true }, sessionId);
await send('Emulation.setDeviceMetricsOverride', {
  width: Number(w), height: Number(h), deviceScaleFactor: 1, mobile: false,
}, sessionId);

if (cookie) {
  await send('Network.setCookie', {
    name: 'PHPSESSID', value: cookie, domain: 'pokemonchic.com', path: '/', httpOnly: true,
  }, sessionId);
}

await send('Page.navigate', { url }, sessionId);

// Ждём загрузку и даём странице дорисоваться (шрифты, fetch-и).
await new Promise(r => setTimeout(r, 4000));

const { data } = await send('Page.captureScreenshot', { format: 'png' }, sessionId);
const fs = await import('node:fs');
fs.writeFileSync(out, Buffer.from(data, 'base64'));

// Прикладная проверка: размеры документа и переполнение по горизонтали.
const probe = await send('Runtime.evaluate', {
  expression: `JSON.stringify({
    title: document.title,
    scrollW: document.documentElement.scrollWidth,
    clientW: document.documentElement.clientWidth,
    overflowX: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
    bodyFont: getComputedStyle(document.body).fontFamily,
    bodySize: getComputedStyle(document.body).fontSize,
    motion: document.documentElement.dataset.motion || null,
    interLoaded: document.fonts ? document.fonts.check('14px Inter') : null
  })`,
  returnByValue: true,
}, sessionId);

console.log(probe.result.value);
await send('Target.closeTarget', { targetId });
ws.close();
