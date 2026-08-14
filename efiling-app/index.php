<?php
require_once __DIR__ . '/includes/bootstrap.php';

$outlets = db()->query('SELECT * FROM outlets WHERE is_active = 1 ORDER BY urutan ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(APP_NAME) ?></title>
<meta name="description" content="Barito Media Group - grup media cetak & online terpercaya di Kalimantan.">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/homepage.css">
</head>
<body>
<header class="site-header">
  <div class="site-header-inner">
    <span class="brand-mark" data-testid="brand-logo">Barito Media Group</span>
    <a href="/login.php" class="btn-login" data-testid="nav-login-btn">Login E-Filing</a>
  </div>
</header>

<section class="hero" style="background-image:linear-gradient(100deg, rgba(10,10,10,0.92) 15%, rgba(10,10,10,0.55) 60%), url('https://images.unsplash.com/photo-1709497197725-2e97c76b31d0?crop=entropy&cs=srgb&fm=jpg&q=85');">
  <div class="hero-inner">
    <span class="hero-eyebrow">Sejak berdiri &middot; Kalimantan Tengah &amp; Selatan</span>
    <h1 class="hero-title">Menjaga Suara<br>Publik Kalimantan.</h1>
    <p class="hero-sub">Kami menaungi enam media cetak &amp; digital yang menyampaikan informasi, pembangunan, dan kebijakan publik secara akurat kepada masyarakat Kalimantan.</p>
    <a href="#outlets" class="btn-outline" data-testid="hero-cta-outlets">Lihat Media Kami &darr;</a>
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
</body>
</html>
