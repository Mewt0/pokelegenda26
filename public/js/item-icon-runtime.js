(() => {
  'use strict';

  const script = document.currentScript || document.getElementById('itemIconRuntime');
  const indexSrc = script?.dataset.indexSrc || '';
  if (!indexSrc) return;

  const numericImagePath = /^\/(?:public\/)?img\/items\/(\d+)\.png$/;
  const mappedArtPath = /^(?:ai-[^/]+|normalized-[^/]+)\//;

  let itemIndex = null;
  let indexVersion = '';

  try {
    indexVersion = new URL(indexSrc, document.baseURI).searchParams.get('v') || '';
  } catch (error) {
    indexVersion = '';
  }

  function canonicalImageSrc(itemId) {
    if (!itemIndex) return null;
    const mapped = itemIndex[String(itemId)];
    if (typeof mapped !== 'string') return null;

    const relative = mapped.replace(/^\/+/, '');
    if (!mappedArtPath.test(relative)) return null;

    return '/public/img/items/' + relative + (indexVersion ? '?v=' + encodeURIComponent(indexVersion) : '');
  }

  function numericItemIdFromSrc(src) {
    if (!src || typeof src !== 'string') return null;
    const value = src.trim();
    if (!value || value.startsWith('data:') || value.startsWith('blob:')) return null;

    let pathname = value;
    try {
      const parsed = new URL(value, document.baseURI);
      if (parsed.origin !== window.location.origin) return null;
      pathname = parsed.pathname;
    } catch (error) {
      return null;
    }

    const match = numericImagePath.exec(pathname);
    return match ? match[1] : null;
  }

  function resolveImage(image) {
    if (!itemIndex || !(image instanceof HTMLImageElement)) return;

    const itemId = numericItemIdFromSrc(image.getAttribute('src'));
    if (!itemId) return;

    const canonical = canonicalImageSrc(itemId);
    if (!canonical) return;

    // Only flat numeric paths reach this point; nested canonical paths are ignored
    // by numericImagePath, so the observer cannot loop on its own rewrite.
    image.setAttribute('src', canonical);
  }

  function scan(root) {
    if (!itemIndex || !root) return;
    if (root instanceof HTMLImageElement) {
      resolveImage(root);
      return;
    }
    if (typeof root.querySelectorAll === 'function') {
      root.querySelectorAll('img').forEach(resolveImage);
    }
  }

  let flushScheduled = false;
  const pendingImages = new Set();

  function flushPending() {
    flushScheduled = false;
    const images = [...pendingImages];
    pendingImages.clear();
    images.forEach(resolveImage);
  }

  function scheduleImage(image) {
    if (!(image instanceof HTMLImageElement)) return;
    pendingImages.add(image);
    if (flushScheduled) return;
    flushScheduled = true;
    if (typeof queueMicrotask === 'function') {
      queueMicrotask(flushPending);
    } else {
      setTimeout(flushPending, 0);
    }
  }

  function observeDom() {
    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === 'attributes') {
          scheduleImage(mutation.target);
          continue;
        }
        for (const node of mutation.addedNodes) {
          if (!(node instanceof Element)) continue;
          scheduleImage(node);
          node.querySelectorAll('img').forEach(scheduleImage);
        }
      }
    });

    observer.observe(document.documentElement, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['src'],
    });
  }

  function start() {
    scan(document);
    observeDom();
  }

  fetch(indexSrc, {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      if (!response.ok) throw new Error('item index unavailable');
      return response.json();
    })
    .then((data) => {
      if (!data || typeof data !== 'object' || Array.isArray(data)) {
        throw new Error('item index has unexpected shape');
      }
      itemIndex = data;
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
      } else {
        start();
      }
    })
    .catch(() => {
      // Keep the valid compatibility image already in the DOM.
    });
})();
