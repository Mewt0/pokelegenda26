/**
 * Звуковое оповещение о нападении дикого покемона.
 *
 * Звук синтезируется через WebAudio: отдельный аудиофайл не нужен, а короткий
 * трёхнотный сигнал не зависит от автозагрузки ассетов. Настройка sounds
 * берётся из /api/profile/settings; при выключенных звуках сигнал не играет.
 * Браузер может запретить автозвук до первого действия пользователя — в этом
 * случае модуль молча пропускает сигнал и не ломает игровой цикл.
 */
(() => {
  'use strict';

  const EVENT_NAME = 'pokemon:wild-encounter';
  let enabled = true;
  let audioContext = null;
  let lastBattleId = 0;
  let lastPlayedAt = 0;

  function readEnabled(settings) {
    if (!settings || typeof settings !== 'object') return;
    enabled = settings.sounds !== false && settings.sounds !== 0 && settings.sounds !== '0';
  }

  async function loadPreference() {
    try {
      const response = await fetch('/api/profile/settings', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) return;
      const data = await response.json();
      if (data && data.ok && data.settings) readEnabled(data.settings);
    } catch (error) {
      // Настройка недоступна — оставляем звук включённым по умолчанию.
    }
  }

  function ensureContext() {
    if (audioContext) return audioContext;
    const Constructor = window.AudioContext || window.webkitAudioContext;
    if (!Constructor) return null;
    try {
      audioContext = new Constructor();
    } catch (error) {
      audioContext = null;
    }
    return audioContext;
  }

  function tone(context, frequency, startsAt, duration, volume, type) {
    const oscillator = context.createOscillator();
    const gain = context.createGain();
    oscillator.type = type || 'triangle';
    oscillator.frequency.setValueAtTime(frequency, startsAt);
    gain.gain.setValueAtTime(0.0001, startsAt);
    gain.gain.exponentialRampToValueAtTime(volume, startsAt + 0.012);
    gain.gain.exponentialRampToValueAtTime(0.0001, startsAt + duration);
    oscillator.connect(gain);
    gain.connect(context.destination);
    oscillator.start(startsAt);
    oscillator.stop(startsAt + duration + 0.03);
  }

  function playAlert() {
    if (!enabled) return false;
    const context = ensureContext();
    if (!context) return false;
    if (context.state === 'suspended' && typeof context.resume === 'function') {
      context.resume().catch(() => {});
    }
    const now = context.currentTime + 0.02;
    // Короткий «боевой» сигнал: два восходящих тона и низкий завершающий.
    tone(context, 740, now, 0.13, 0.09, 'triangle');
    tone(context, 988, now + 0.15, 0.15, 0.08, 'triangle');
    tone(context, 659, now + 0.34, 0.2, 0.06, 'sine');
    return true;
  }

  function notify(detail) {
    const battleId = Number((detail && detail.battleId) || 0);
    if (battleId > 0 && battleId === lastBattleId) return false;
    const now = Date.now();
    if (now - lastPlayedAt < 1500) return false;
    lastBattleId = battleId;
    lastPlayedAt = now;
    return playAlert();
  }

  window.addEventListener(EVENT_NAME, (event) => notify(event && event.detail));
  window.addEventListener('profile-settings:saved', (event) => {
    if (event && event.detail && event.detail.settings) readEnabled(event.detail.settings);
  });
  document.addEventListener('pointerdown', () => {
    if (audioContext && audioContext.state === 'suspended') {
      audioContext.resume().catch(() => {});
    }
  }, { passive: true });

  loadPreference();

  window.WildEncounterAlert = {
    notify,
    play: playAlert,
    isEnabled: () => enabled,
  };
})();
