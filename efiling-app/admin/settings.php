<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

$pageTitle = 'Personalisasi Tampilan';
$activeNav = 'settings';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Personalisasi Tampilan</h1></div>
<div class="card">
  <p style="color:var(--text-muted);margin-top:0">Pilih palet warna favorit Anda. Tersedia 100+ pilihan mewakili warna dasar Hitam, Putih, Biru, Kuning &amp; Merah. Pilihan otomatis tersimpan untuk akun Anda.</p>
  <div id="theme-grid-container" data-testid="theme-grid-container"></div>
</div>
<script src="/assets/js/theme-manager.js"></script>
<script>renderThemeGrid(document.getElementById('theme-grid-container'));</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
