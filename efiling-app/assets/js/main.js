document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  var sidebarToggle = document.querySelector('[data-testid="sidebar-toggle"]');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function () {
      document.querySelector('.sidebar').classList.toggle('open');
    });
  }

  var selectAll = document.querySelector('[data-testid="select-all-checkbox"]');
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      document.querySelectorAll('.row-checkbox').forEach(function (cb) { cb.checked = selectAll.checked; });
    });
  }
});
