<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

$totalSurat = (int)db()->query('SELECT COUNT(*) FROM surat_penawaran')->fetchColumn();
$totalKwitansi = (int)db()->query('SELECT COUNT(*) FROM kwitansi')->fetchColumn();
$totalInstansi = (int)db()->query('SELECT COUNT(*) FROM instansi WHERE is_active = 1')->fetchColumn();
$totalMedia = (int)db()->query('SELECT COUNT(*) FROM media WHERE is_active = 1')->fetchColumn();

$recent = db()->query("
  (SELECT 'surat' AS tipe, s.id, s.nomor_surat AS nomor, s.hal AS judul, i.nama AS instansi, m.kode AS media_kode, s.tanggal, s.created_at
   FROM surat_penawaran s JOIN instansi i ON i.id=s.instansi_id JOIN media m ON m.id=s.media_id)
  UNION ALL
  (SELECT 'kwitansi' AS tipe, k.id, k.nomor_kwitansi AS nomor, k.untuk_pembayaran AS judul, i.nama AS instansi, m.kode AS media_kode, k.tanggal, k.created_at
   FROM kwitansi k JOIN instansi i ON i.id=k.instansi_id JOIN media m ON m.id=k.media_id)
  ORDER BY created_at DESC LIMIT 10
")->fetchAll();

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar">
  <h1 data-testid="page-title">Dashboard</h1>
  <div class="topbar-user">
    <span class="badge-role"><?= e($u['role']) ?></span>
    <span><?= e($u['name']) ?></span>
  </div>
</div>

<div class="stat-grid">
  <div class="stat-card" data-testid="stat-surat"><div class="stat-label">Surat Penawaran</div><div class="stat-value"><?= $totalSurat ?></div></div>
  <div class="stat-card" data-testid="stat-kwitansi"><div class="stat-label">Kwitansi</div><div class="stat-value"><?= $totalKwitansi ?></div></div>
  <div class="stat-card" data-testid="stat-instansi"><div class="stat-label">Instansi Tujuan</div><div class="stat-value"><?= $totalInstansi ?></div></div>
  <div class="stat-card" data-testid="stat-media"><div class="stat-label">Media Aktif</div><div class="stat-value"><?= $totalMedia ?></div></div>
</div>

<div class="card">
  <div class="toolbar">
    <h3 style="font-family:inherit;font-size:1.1rem">Dokumen Terbaru</h3>
    <a href="/admin/surat_form.php" class="btn btn-primary" data-testid="dashboard-new-surat-btn">+ Surat Penawaran Baru</a>
  </div>
  <div class="table-wrap">
  <table>
    <thead><tr><th>Nomor</th><th>Jenis</th><th>Instansi</th><th>Media</th><th>Tanggal</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $r): ?>
      <tr data-testid="recent-doc-row">
        <td><?= e($r['nomor']) ?></td>
        <td><span class="pill pill-<?= e($r['tipe']) ?>"><?= $r['tipe'] === 'surat' ? 'Penawaran' : 'Kwitansi' ?></span></td>
        <td><?= e($r['instansi']) ?></td>
        <td><?= e($r['media_kode']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?>
      <tr><td colspan="5" style="color:var(--text-muted)">Belum ada dokumen. Mulai dengan membuat surat penawaran baru.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
