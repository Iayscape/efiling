<?php
require_once __DIR__ . '/includes/bootstrap.php';

$outlets = db()->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY urutan ASC')->fetchAll();

$slides = [
    ['eyebrow' => 'Sejak berdiri &middot; Kalimantan Tengah &amp; Selatan', 'title' => 'Menjaga Suara<br>Publik Kalimantan.', 'sub' => 'Kami menaungi enam media cetak &amp; digital yang menyampaikan informasi, pembangunan, dan kebijakan publik secara akurat kepada masyarakat Kalimantan.', 'img' => 'https://images.unsplash.com/photo-1581092795360-fd1ca04f0952?crop=entropy&cs=srgb&fm=jpg&q=85'],
    ['eyebrow' => 'Warisan Percetakan &middot; Dedikasi Jurnalistik', 'title' => 'Mencetak Fakta,<br>Membangun Percaya.', 'sub' => 'Dari mesin cetak hingga kanal digital, setiap kata kami lahir dari proses jurnalistik yang bertanggung jawab.', 'img' => 'https://images.unsplash.com/photo-1581508512961-0e3b9524db40?crop=entropy&cs=srgb&fm=jpg&q=85'],
    ['eyebrow' => 'Enam Media &middot; Satu Jaringan', 'title' => 'Merangkai Cerita<br>dari Setiap Sudut Banua.', 'sub' => 'Koran Barito, Sinar Barito, Suluh Banua, Banta News, Barito Bersinar, dan Selidah Nusantara &mdash; hadir untuk masyarakat Kalimantan.', 'img' => 'https://images.unsplash.com/photo-1583511416766-083ba12de77c?crop=entropy&cs=srgb&fm=jpg&q=85'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?></title>
<meta name="description" content="Barito Media Group - grup media cetak & online terpercaya di Kalimantan.">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/homepage.css">
</head>
<body>
<header class="site-header">
  <div class="site-header-inner">
    <span class="brand-mark" data-testid="brand-logo">Barito Media Group</span>

    <nav class="main-nav">
      <div class="nav-item" data-testid="mega-menu-item">
        <button class="nav-trigger" data-testid="mega-menu-trigger" aria-expanded="false">
          Media Kami
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="mega-menu" data-testid="mega-menu-panel">
          <div class="mega-menu-inner">
            <?php foreach ($outlets as $o): ?>
            <a class="mega-menu-item" href="<?= e($o['url']) ?>" target="_blank" rel="noopener" data-testid="mega-menu-link-<?= e(slugify($o['nama'])) ?>">
              <?php if (!empty($o['logo_path'])): ?>
              <span class="mega-menu-logo"><img src="/<?= e($o['logo_path']) ?>" alt="Logo <?= e($o['nama']) ?>"></span>
              <?php endif; ?>
              <span class="mega-menu-text"><strong><?= e($o['nama']) ?></strong><span><?= e($o['tagline']) ?></span></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </nav>

    <div class="header-actions">
      <a href="/login.php" class="btn-login" data-testid="nav-login-btn">Login E-Filing</a>
      <button class="mobile-nav-toggle" data-testid="mobile-nav-toggle" aria-label="Buka menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>
  </div>
</header>

<div class="drawer-overlay" data-testid="drawer-overlay"></div>
<aside class="mobile-drawer" data-testid="mobile-drawer">
  <div class="mobile-drawer-header">
    <span class="brand-mark">Barito Media Group</span>
    <button class="mobile-drawer-close" data-testid="mobile-drawer-close" aria-label="Tutup menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="mobile-drawer-list">
    <?php foreach ($outlets as $o): ?>
    <a class="mobile-drawer-item" href="<?= e($o['url']) ?>" target="_blank" rel="noopener" data-testid="mobile-menu-link-<?= e(slugify($o['nama'])) ?>">
      <?php if (!empty($o['logo_path'])): ?>
      <span class="mega-menu-logo"><img src="/<?= e($o['logo_path']) ?>" alt=""></span>
      <?php endif; ?>
      <span class="mega-menu-text"><strong><?= e($o['nama']) ?></strong><span><?= e($o['tagline']) ?></span></span>
    </a>
    <?php endforeach; ?>
  </div>
  <a href="/login.php" class="mobile-drawer-login" data-testid="mobile-login-btn">Login E-Filing</a>
</aside>

<section class="hero-carousel" data-testid="hero-carousel">
  <?php foreach ($slides as $idx => $s): ?>
  <div class="hero-slide<?= $idx === 0 ? ' active' : '' ?>" style="background-image:url('<?= e($s['img']) ?>')">
    <div class="hero-slide-inner">
      <span class="hero-eyebrow"><?= $s['eyebrow'] ?></span>
      <h1 class="hero-title"><?= $s['title'] ?></h1>
      <p class="hero-sub"><?= $s['sub'] ?></p>
      <a href="#outlets" class="btn-outline" data-testid="hero-cta-outlets">Lihat Media Kami &darr;</a>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="carousel-controls">
    <button class="carousel-arrow" data-testid="carousel-prev" aria-label="Slide sebelumnya">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="carousel-dots" data-testid="carousel-dots">
      <?php foreach ($slides as $idx => $s): ?>
      <button class="carousel-dot<?= $idx === 0 ? ' active' : '' ?>" data-testid="carousel-dot-<?= $idx ?>" aria-label="Ke slide <?= $idx + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <button class="carousel-arrow" data-testid="carousel-next" aria-label="Slide berikutnya">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>
</section>

<section class="outlets" id="outlets">
  <div class="section-heading">
    <span class="section-eyebrow">Portofolio Media</span>
    <h2>Enam Suara, Satu Jaringan</h2>
  </div>
  <div class="outlet-grid" data-testid="outlet-grid">
    <?php foreach ($outlets as $o): ?>
    <a class="outlet-card" href="<?= e($o['url']) ?>" target="_blank" rel="noopener" style="--accent: <?= e($o['accent_color']) ?>" data-testid="outlet-card-<?= e(slugify($o['nama'])) ?>">
      <span class="outlet-index">0<?= (int)$o['urutan'] ?></span>
      <?php if (!empty($o['logo_path'])): ?>
      <div class="outlet-logo"><img src="/<?= e($o['logo_path']) ?>" alt="Logo <?= e($o['nama']) ?>" data-testid="outlet-logo-<?= e(slugify($o['nama'])) ?>"></div>
      <?php endif; ?>
      <h3><?= e($o['nama']) ?></h3>
      <p class="outlet-tagline">&ldquo;<?= e($o['tagline']) ?>&rdquo;</p>
      <p><?= e($o['deskripsi']) ?></p>
      <span class="outlet-link"><?= e(preg_replace('#^https?://#', '', $o['url'])) ?> &nearr;</span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="cta-strip">
  <div class="cta-strip-inner">
    <div>
      <h3>Tim Redaksi &amp; Administrasi</h3>
      <p>Akses arsip digital penawaran &amp; kwitansi melalui portal e-filing internal.</p>
    </div>
    <a href="/login.php" class="btn-solid" data-testid="cta-login-btn">Masuk ke E-Filing &rarr;</a>
  </div>
</section>

<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> Barito Media Group. Seluruh hak cipta dilindungi.</p>
</footer>

<script src="/assets/js/homepage.js"></script>
</body>
</html>
