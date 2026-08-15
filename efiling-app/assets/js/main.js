document.addEventListener('DOMContentLoaded', function () {
  var themeGridContainer = document.getElementById('theme-grid-container');
  if (themeGridContainer && typeof renderThemeGrid === 'function') {
    renderThemeGrid(themeGridContainer);
  }

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  var sidebarToggle = document.querySelector('[data-testid="sidebar-toggle"]');
  var sidebarOverlay = document.querySelector('[data-testid="sidebar-overlay"]');
  var sidebarEl = document.querySelector('.sidebar');
  function closeSidebar() {
    if (sidebarEl) sidebarEl.classList.remove('open');
    if (sidebarOverlay) sidebarOverlay.classList.remove('visible');
  }
  if (sidebarToggle && sidebarEl) {
    sidebarToggle.addEventListener('click', function () {
      sidebarEl.classList.toggle('open');
      if (sidebarOverlay) sidebarOverlay.classList.toggle('visible');
    });
  }
  if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
  document.querySelectorAll('.sidebar .nav-link').forEach(function (link) {
    link.addEventListener('click', closeSidebar);
  });

  var accountDropdown = document.querySelector('[data-testid="account-dropdown"]');
  var accountTrigger = document.querySelector('[data-testid="account-trigger-btn"]');
  if (accountDropdown && accountTrigger) {
    accountTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = accountDropdown.classList.toggle('open');
      accountTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
      if (!accountDropdown.contains(e.target)) accountDropdown.classList.remove('open');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') accountDropdown.classList.remove('open');
    });
  }

  var selectAll = document.querySelector('[data-testid="select-all-checkbox"]');
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.row-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
    });
  }
});
