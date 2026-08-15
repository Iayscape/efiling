document.addEventListener('DOMContentLoaded', function () {
  // Mega menu (desktop hover + click toggle for touch/keyboard)
  var navItem = document.querySelector('[data-testid="mega-menu-item"]');
  var trigger = document.querySelector('[data-testid="mega-menu-trigger"]');
  if (navItem && trigger) {
    var closeTimer = null;
    var isHovering = false;
    function openMenu() { clearTimeout(closeTimer); navItem.classList.add('menu-open'); }
    function scheduleClose() { closeTimer = setTimeout(function () { navItem.classList.remove('menu-open'); }, 150); }
    navItem.addEventListener('mouseenter', function () { isHovering = true; openMenu(); });
    navItem.addEventListener('mouseleave', function () { isHovering = false; scheduleClose(); });
    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      if (isHovering) { openMenu(); return; }
      navItem.classList.toggle('menu-open');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') navItem.classList.remove('menu-open');
    });
    document.addEventListener('click', function (e) {
      if (!navItem.contains(e.target)) navItem.classList.remove('menu-open');
    });
  }

  // Mobile off-canvas drawer
  var mobileToggle = document.querySelector('[data-testid="mobile-nav-toggle"]');
  var drawer = document.querySelector('[data-testid="mobile-drawer"]');
  var overlay = document.querySelector('[data-testid="drawer-overlay"]');
  var closeBtn = document.querySelector('[data-testid="mobile-drawer-close"]');
  function openDrawer() { if (drawer) drawer.classList.add('open'); if (overlay) overlay.classList.add('visible'); }
  function closeDrawer() { if (drawer) drawer.classList.remove('open'); if (overlay) overlay.classList.remove('visible'); }
  if (mobileToggle) mobileToggle.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (overlay) overlay.addEventListener('click', closeDrawer);

  // Hero carousel
  var slides = document.querySelectorAll('.hero-slide');
  var dots = document.querySelectorAll('.carousel-dot');
  var prevBtn = document.querySelector('[data-testid="carousel-prev"]');
  var nextBtn = document.querySelector('[data-testid="carousel-next"]');
  var current = 0;
  var timer = null;

  function showSlide(i) {
    current = (i + slides.length) % slides.length;
    slides.forEach(function (s, idx) { s.classList.toggle('active', idx === current); });
    dots.forEach(function (d, idx) { d.classList.toggle('active', idx === current); });
  }
  function next() { showSlide(current + 1); }
  function prev() { showSlide(current - 1); }
  function startAutoplay() { timer = setInterval(next, 5500); }
  function stopAutoplay() { clearInterval(timer); }

  if (slides.length) {
    showSlide(0);
    startAutoplay();
    var carousel = document.querySelector('.hero-carousel');
    if (carousel) {
      carousel.addEventListener('mouseenter', stopAutoplay);
      carousel.addEventListener('mouseleave', startAutoplay);
    }
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); stopAutoplay(); startAutoplay(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); stopAutoplay(); startAutoplay(); });
    dots.forEach(function (d, idx) {
      d.addEventListener('click', function () { showSlide(idx); stopAutoplay(); startAutoplay(); });
    });
  }
});
