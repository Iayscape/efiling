<?php
$__u = $u ?? current_user();
$__nav = $activeNav ?? '';
function navlink($href, $label, $key, $active) {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<a class="' . $cls . '" href="' . e($href) . '" data-testid="nav-' . e($key) . '">' . e($label) . '</a>';
}
?>
<aside class="sidebar" data-testid="sidebar">
  <div class="sidebar-brand">
    <strong>Barito Media Group</strong>
    <span>E-Filing Digital</span>
  </div>
  <div class="nav-group">
    <?php navlink('/admin/dashboard.php', 'Dashboard', 'dashboard', $__nav); ?>
    <?php navlink('/admin/surat_form.php', 'Buat Surat Penawaran', 'surat-baru', $__nav); ?>
    <?php navlink('/admin/kwitansi_form.php', 'Buat Kwitansi Manual', 'kwitansi-baru', $__nav); ?>
    <?php navlink('/admin/arsip.php', 'Arsip Digital', 'arsip', $__nav); ?>
  </div>
  <div class="nav-section-title">Data Master</div>
  <div class="nav-group">
    <?php navlink('/admin/instansi.php', 'Instansi Tujuan', 'instansi', $__nav); ?>
    <?php navlink('/admin/media.php', 'Media &amp; Legalitas', 'media', $__nav); ?>
    <?php navlink('/admin/jenis.php', 'Jenis Penawaran', 'jenis', $__nav); ?>
    <?php if ($__u && $__u['role'] === 'admin'): ?>
    <?php navlink('/admin/users.php', 'Pengguna', 'users', $__nav); ?>
    <?php endif; ?>
  </div>
  <div class="nav-section-title">Akun</div>
  <div class="nav-group">
    <?php navlink('/admin/settings.php', 'Personalisasi Tampilan', 'settings', $__nav); ?>
    <?php navlink('/admin/profile.php', 'Profil Saya', 'profile', $__nav); ?>
  </div>
  <div class="sidebar-footer">
    Masuk sebagai<br><strong style="color:var(--text-main)"><?= e($__u['name'] ?? '') ?></strong>
    <br><a href="/logout.php" data-testid="nav-logout-btn" style="color:#f87171">Keluar</a>
  </div>
</aside>
<main class="main-content">
<?php $__flash = flash_get(); if ($__flash): ?>
  <div class="alert alert-<?= e($__flash['type']) ?>" data-testid="flash-message"><?= e($__flash['msg']) ?></div>
<?php endif; ?>
