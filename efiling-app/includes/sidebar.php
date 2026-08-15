<?php
$__u = $u ?? current_user();
$__nav = $activeNav ?? '';
function navlink($href, $label, $key, $active) {
    $cls = $active === $key ? 'nav-link active' : 'nav-link';
    echo '<a class="' . $cls . '" href="' . e($href) . '" data-testid="nav-' . e($key) . '">' . e($label) . '</a>';
}
?>
<button class="mobile-menu-btn" type="button" data-testid="sidebar-toggle" aria-label="Buka menu">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
<div class="sidebar-overlay" data-testid="sidebar-overlay"></div>
<aside class="sidebar" data-testid="sidebar">
  <div class="sidebar-brand">
    <strong>Arsip Digital</strong>
    <span>Barito Media Group</span>
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
    <?php navlink('/admin/media.php', 'Media & Legalitas', 'media', $__nav); ?>
    <?php navlink('/admin/jenis.php', 'Jenis Penawaran', 'jenis', $__nav); ?>
    <?php navlink('/admin/rubrik.php', 'Rubrik/Item', 'rubrik', $__nav); ?>
    <?php navlink('/admin/konsep.php', 'Konsep Surat & Kwitansi', 'konsep', $__nav); ?>
    <?php if ($__u && $__u['role'] === 'admin'): ?>
    <?php navlink('/admin/users.php', 'Pengguna', 'users', $__nav); ?>
    <?php endif; ?>
  </div>
  <div class="account-dropdown" data-testid="account-dropdown">
    <button type="button" class="account-trigger" data-testid="account-trigger-btn" aria-expanded="false">
      <span class="account-avatar"><?= e(mb_substr($__u['name'] ?? '?', 0, 1)) ?></span>
      <span class="account-info">
        <strong><?= e($__u['name'] ?? '') ?></strong>
        <span><?= e($__u['role'] === 'admin' ? 'Administrator' : 'Staff') ?></span>
      </span>
      <svg class="account-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <div class="account-menu" data-testid="account-menu" style="display:none">
      <a href="/admin/settings.php" data-testid="account-menu-settings">Personalisasi Tampilan</a>
      <a href="/admin/profile.php" data-testid="account-menu-profile">Profil Saya</a>
      <a href="/logout.php" class="account-menu-logout" data-testid="account-menu-logout">Keluar</a>
    </div>
  </div>
</aside>
<main class="main-content">
<?php $__flash = flash_get(); if ($__flash): ?>
  <div class="alert alert-<?= e($__flash['type']) ?>" data-testid="flash-message"><?= e($__flash['msg']) ?></div>
<?php endif; ?>
