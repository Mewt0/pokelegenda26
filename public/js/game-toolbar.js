(function () {
  const tools = document.getElementById('gameToolsMenu');
  const button = document.getElementById('gameToolsBtn');
  const dropdown = document.getElementById('gameToolsDropdown');
  const bugButton = document.getElementById('bugReportBtn');

  if (!tools || !button || !dropdown) return;

  function setOpen(open) {
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    dropdown.classList.toggle('is-open', open);
    dropdown.setAttribute('aria-hidden', open ? 'false' : 'true');
  }

  function openBugReport() {
    const overlay = document.getElementById('bugReportOverlay');
    if (!overlay) return;
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.getElementById('bugReportTitle')?.focus();
  }

  button.addEventListener('click', event => {
    event.preventDefault();
    event.stopImmediatePropagation();
    setOpen(button.getAttribute('aria-expanded') !== 'true');
  }, true);

  bugButton?.addEventListener('click', event => {
    event.preventDefault();
    event.stopImmediatePropagation();
    setOpen(false);
    openBugReport();
  }, true);

  document.addEventListener('click', event => {
    if (!tools.contains(event.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      setOpen(false);
    }
  });
})();
