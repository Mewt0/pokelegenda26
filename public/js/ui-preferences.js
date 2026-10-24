/**
 * Тема, размер и анимации интерфейса.
 *
 * Источник правды — GET /api/profile/settings; ответ сервера уточняет кэш.
 * Кэш в localStorage применяется синхронно при выполнении скрипта, поэтому
 * выбранная тема и масштаб не мигают системными значениями при навигации.
 * Мгновенные изменения приходят событием profile-settings:saved от модалки
 * настроек, изменения в другой вкладке — событием storage.
 *
 * Что реально применяется:
 *   theme    — html[data-theme="light|dark"], по умолчанию светлая;
 *   ui_size  — html[data-ui-size="compact|normal|large"] (масштаб в tokens.css);
 *   animations — html[data-motion="on|off"].
 *
 * Тёмная тема остаётся полноценной опцией: игра сама по себе светлая, а тёмную
 * игрок выбирает осознанно (`theme = "dark"`). Значение `auto` теперь означает
 * не «следовать за системой», а «как решил игрок, иначе светлая» — иначе игрок
 * с тёмной системной темой получал тёмную игру, не выбирая её.
 *
 * Что сохраняется, но не применяется: sounds — звуковой системы в игре нет.
 */
(function () {
  'use strict';

  const root = document.documentElement;
  const cacheKey = 'league-of-pokemons:ui-preferences:v1';
  const systemTheme = typeof window.matchMedia === 'function'
    ? window.matchMedia('(prefers-color-scheme: dark)')
    : null;
  let theme = 'light';
  let revision = 0;

  // Светлая — базовая тема игры. Системная тема не выбирает тему за игрока:
  // иначе на тёмной ОС игра открывалась бы тёмной без его решения.
  function effectiveTheme() {
    return theme === 'dark' ? 'dark' : 'light';
  }

  function syncSystemTheme() {
    // Системная тема больше не управляет игрой: игрок выбирает тему сам.
    // Слушатель оставлен только чтобы переключение системной темы не сбивало
    // уже применённую явную настройку.
    root.dataset.theme = effectiveTheme();
  }

  if (systemTheme) {
    if (typeof systemTheme.addEventListener === 'function') {
      systemTheme.addEventListener('change', syncSystemTheme);
    } else if (typeof systemTheme.addListener === 'function') {
      systemTheme.addListener(syncSystemTheme);
    }
  }
  // Некоторые браузеры откладывают media events для фоновой вкладки.
  document.addEventListener('visibilitychange', syncSystemTheme);

  function readCache() {
    try {
      return JSON.parse(localStorage.getItem(cacheKey) || 'null');
    } catch (error) {
      return null; // localStorage может быть отключён или содержать испорченные данные.
    }
  }

  function apply(settings, persist = true) {
    if (!settings || typeof settings !== 'object') return;
    revision += 1;

    // Явно выбранная тёмная тема сохраняется; всё остальное (включая прежнее
    // значение `auto` из старых профилей) даёт светлую игру.
    theme = settings.theme === 'dark' ? 'dark' : 'light';
    root.dataset.theme = effectiveTheme();

    const size = settings.uiSize ?? settings.ui_size;
    root.dataset.uiSize = ['compact', 'normal', 'large'].includes(size) ? size : 'normal';
    root.dataset.motion = settings.animations === false ? 'off' : 'on';

    if (persist) {
      try {
        localStorage.setItem(cacheKey, JSON.stringify({
          theme,
          uiSize: root.dataset.uiSize,
          animations: root.dataset.motion === 'on',
        }));
      } catch (error) {
        // Настройки в текущей вкладке работают и без доступного localStorage.
      }
    }
  }

  async function load() {
    const startedAtRevision = revision;
    try {
      const response = await fetch('/api/profile/settings', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) return;
      const data = await response.json();
      // Не перезаписываем более свежий save или изменение в другой вкладке.
      if (revision === startedAtRevision && data && data.ok && data.settings) {
        apply(data.settings);
      }
    } catch (error) {
      // При сбое сети сохраняется кэш (либо системные значения по умолчанию).
    }
  }

  // Выполняется до DOMContentLoaded и до асинхронного GET, без ожидания сети.
  const cached = readCache();
  if (cached) {
    apply(cached, false);
  } else {
    root.dataset.theme = effectiveTheme();
  }

  window.addEventListener('profile-settings:saved', event => {
    apply(event && event.detail ? event.detail.settings : null);
  });

  window.addEventListener('storage', event => {
    if (event.key === cacheKey) {
      apply(readCache() || {}, false);
    }
  });

  window.UiPreferences = { apply, load };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', load, { once: true });
  } else {
    load();
  }
}());
