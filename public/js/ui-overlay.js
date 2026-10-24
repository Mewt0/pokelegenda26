/* Единое поведение оверлеев — план §5.3: закрытие по Esc и клику вне,
   фокус-трап, возврат фокуса.

   До этого файла фокус-трапа в проекте не было вовсе (ни одного обработчика
   Tab), а Esc был реализован отдельно в двенадцати файлах — по-разному.

   Разметка оверлея в проекте двухуровневая, и это важно для логики:
   внешняя `<section class="...-overlay" aria-hidden="true">` хранит состояние
   (класс `is-open`, `aria-hidden`), а внутреннее окно несёт
   `role="dialog" aria-modal="true"` и служит контейнером фокуса.
   Поэтому атрибут `data-ui-overlay` ставится на ВНЕШНИЙ элемент, а диалог
   ищется внутри него.

   Включение намеренно явное: иначе легко задеть боевое окно
   (`#battleOverlay`) — оно перетаскиваемое, и закрывать его по Esc нельзя
   без решения владельца.

   Свой обработчик закрытия у оверлея остаётся; в дополнение шлётся событие
   `ui-overlay:closed` с причиной (`escape` | `outside`). */
(function () {
  'use strict';

  if (window.UiOverlay) {
    return;
  }

  var OPEN_CLASS = 'is-open';
  var SELECTOR = '[data-ui-overlay]';
  var FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
  ].join(',');

  var current = null;
  var lastFocused = null;
  var stack = [];

  function isOpen(el) {
    return el.classList.contains(OPEN_CLASS) || el.getAttribute('aria-hidden') === 'false';
  }

  /* Контейнер фокуса: внутреннее окно диалога, а если его нет — сам оверлей. */
  function dialogOf(root) {
    return root.querySelector('[role="dialog"]') || root;
  }

  /* Видимые и доступные для фокуса элементы внутри диалога. Проверяем и
     размер: в разметке есть кнопки-заглушки нулевого размера, фокус на них
     ловушку бы сломал. */
  function focusable(root) {
    return Array.prototype.filter.call(dialogOf(root).querySelectorAll(FOCUSABLE), function (el) {
      if (el.hasAttribute('hidden') || el.getAttribute('aria-hidden') === 'true') {
        return false;
      }
      var style = window.getComputedStyle(el);
      if (style.visibility === 'hidden' || style.display === 'none') {
        return false;
      }
      var rect = el.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    });
  }

  function remember(el) {
    if (isOpen(el)) {
      if (stack.some(function (entry) { return entry.root === el; })) {
        return;
      }
      stack.push({ root: el, focus: document.activeElement });
      current = el;
      lastFocused = document.activeElement;
      var items = focusable(el);
      if (items.length) {
        try {
          items[0].focus();
        } catch (error) {
          /* фокус не критичен для работы */
        }
      }
      return;
    }

    var index = stack.findIndex(function (entry) { return entry.root === el; });
    if (index !== -1) {
      var wasTop = current === el;
      var entry = stack.splice(index, 1)[0];
      while (stack.length && !isOpen(stack[stack.length - 1].root)) stack.pop();
      current = stack.length ? stack[stack.length - 1].root : null;
      var restore = entry.focus;
      if (wasTop && restore && document.contains(restore) && (!current || current.contains(restore))) {
        try {
          restore.focus({ preventScroll: true });
        } catch (error) {
          /* фокус не критичен для работы */
        }
      }
      lastFocused = current ? stack[stack.length - 1].focus : null;
    }
  }

  function close(el, reason) {
    if (!isOpen(el)) {
      return;
    }
    el.classList.remove(OPEN_CLASS);
    el.setAttribute('aria-hidden', 'true');
    remember(el);
    el.dispatchEvent(new CustomEvent('ui-overlay:closed', {
      bubbles: true,
      detail: { reason: reason }
    }));
  }

  document.addEventListener('keydown', function (event) {
    if (!current) {
      return;
    }
    if (event.key === 'Escape') {
      event.preventDefault();
      close(current, 'escape');
      return;
    }
    if (event.key !== 'Tab') {
      return;
    }
    var items = focusable(current);
    if (!items.length) {
      event.preventDefault();
      return;
    }
    var first = items[0];
    var last = items[items.length - 1];
    var active = document.activeElement;
    if (event.shiftKey && (active === first || !current.contains(active))) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && (active === last || !current.contains(active))) {
      event.preventDefault();
      first.focus();
    }
  }, true);

  /* Клик по подложке: цель — сам оверлей, а не его содержимое. */
  document.addEventListener('mousedown', function (event) {
    if (current && event.target === current) {
      close(current, 'outside');
    }
  });

  function attach(el) {
    if (el.dataset.uiOverlayBound === '1') {
      return;
    }
    el.dataset.uiOverlayBound = '1';
    var update = function () {
      remember(el);
    };
    new MutationObserver(update).observe(el, {
      attributes: true,
      attributeFilter: ['class', 'aria-hidden']
    });
    update();
  }

  function scan(root) {
    var scope = root || document;
    Array.prototype.forEach.call(scope.querySelectorAll(SELECTOR), attach);
  }

  window.UiOverlay = {
    scan: scan,
    attach: attach,
    close: close,
    current: function () {
      return current;
    }
  };

  /* Привязываемся сразу к тому, что уже разобрано, и повторяем после
     построения документа: скрипт подключён в конце <body>, поэтому часть
     разметки на момент выполнения ещё может быть не разобрана. */
  scan();
  document.addEventListener('DOMContentLoaded', function () {
    scan();
  });
})();
