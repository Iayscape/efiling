<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $hal = trim($_POST['template_hal'] ?? '');
    $body = trim($_POST['template_body'] ?? '');
    $bayar = trim($_POST['template_pembayaran'] ?? '');
    if ($id > 0) {
        db()->prepare('UPDATE jenis_penawaran SET template_hal=?, template_body=?, template_pembayaran=? WHERE id=?')
            ->execute([$hal, $body, $bayar, $id]);
        log_activity((int)$u['id'], 'update_konsep', 'jenis_id:' . $id);
        flash_set('success', 'Konsep berhasil diperbarui.');
    }
    redirect('/admin/konsep.php');
}

$rows = db()->query('SELECT * FROM jenis_penawaran WHERE is_active = 1 ORDER BY nama ASC')->fetchAll();

$pageTitle = 'Konsep Surat & Kwitansi';
$activeNav = 'konsep';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Konsep Surat &amp; Kwitansi</h1></div>

<div class="card" style="margin-bottom:1.25rem">
  <p style="color:var(--text-muted);font-size:.85rem;margin:0">
    Ubah kalimat baku (konsep) yang otomatis mengisi <strong>Surat Penawaran</strong> (Hal &amp; Isi Surat)
    dan <strong>Kwitansi</strong> (Keterangan Pembayaran) per jenis penawaran. Gunakan token
    <code>{instansi}</code>, <code>{media}</code>, <code>{bulan}</code>, <code>{tahun}</code>
    (huruf besar <code>{INSTANSI}</code> dkk untuk versi KAPITAL) — akan diganti otomatis saat surat dibuat.
  </p>
</div>

<?php foreach ($rows as $r): ?>
<div class="card konsep-card" data-testid="konsep-card-<?= e($r['kode']) ?>">
  <div class="konsep-card-head">
    <h3 style="margin:0;font-size:1rem"><?= e($r['nama']) ?> <span class="konsep-kode-badge"><?= e($r['kode']) ?></span></h3>
  </div>
  <form method="post" class="konsep-form" data-testid="konsep-form-<?= e($r['kode']) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
    <div class="field">
      <label>Hal (Subjek Surat Penawaran)</label>
      <input type="text" name="template_hal" class="konsep-input-hal" value="<?= e($r['template_hal'] ?? '') ?>" data-testid="konsep-hal-input-<?= e($r['kode']) ?>">
    </div>
    <div class="field">
      <label>Isi Surat Penawaran</label>
      <textarea name="template_body" rows="4" class="konsep-input-body" data-testid="konsep-body-input-<?= e($r['kode']) ?>"><?= e($r['template_body'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label>Keterangan Pembayaran (Kwitansi)</label>
      <input type="text" name="template_pembayaran" class="konsep-input-bayar" value="<?= e($r['template_pembayaran'] ?? '') ?>" data-testid="konsep-bayar-input-<?= e($r['kode']) ?>">
    </div>
    <button class="btn btn-primary btn-sm" type="submit" data-testid="konsep-save-btn-<?= e($r['kode']) ?>">Simpan Konsep</button>
  </form>
  <div class="konsep-preview" data-testid="konsep-preview-<?= e($r['kode']) ?>">
    <div class="konsep-preview-label">Pratinjau (contoh data)</div>
    <div class="konsep-preview-line"><strong>Hal:</strong> <span class="konsep-preview-hal"></span></div>
    <div class="konsep-preview-line"><strong>Isi:</strong> <span class="konsep-preview-body"></span></div>
    <div class="konsep-preview-line"><strong>Pembayaran:</strong> <span class="konsep-preview-bayar"></span></div>
  </div>
</div>
<?php endforeach; ?>

<script src="/assets/js/konsep.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
