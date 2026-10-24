// Точечный замер элементов страницы через CDP: node probe.js <url> <sessionCookie> <expression>
const [, , url, cookie, expression] = process.argv;

const version = await (await fetch('http://127.0.0.1:9222/json/version')).json();
const ws = new WebSocket(version.webSocketDebuggerUrl);
let nextId = 1;
const pending = new Map();
ws.addEventListener('message', ev => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) {
    const { resolve, reject } = pending.get(msg.id);
    pending.delete(msg.id);
    msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
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
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false }, sessionId);
await send('Network.setCookie', { name: 'PHPSESSID', value: cookie, domain: 'pokemonchic.com', path: '/', httpOnly: true }, sessionId);
await send('Page.navigate', { url }, sessionId);
await new Promise(r => setTimeout(r, 4000));

const probe = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }, sessionId);
if (probe.exceptionDetails) {
  console.log('EVAL ERROR: ' + JSON.stringify(probe.exceptionDetails.exception || probe.exceptionDetails));
} else if (probe.result && probe.result.value !== undefined) {
  console.log(typeof probe.result.value === 'string' ? probe.result.value : JSON.stringify(probe.result.value, null, 1));
} else {
  console.log('NO VALUE: ' + JSON.stringify(probe.result));
}
await send('Target.closeTarget', { targetId });
ws.close();
