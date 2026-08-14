<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $kode = trim($_POST['kode'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $hal = trim($_POST['template_hal'] ?? '');
        $body = trim($_POST['template_body'] ?? '');
        $bayar = trim($_POST['template_pembayaran'] ?? '');
        if ($kode === '' || $nama === '') {
            flash_set('error', 'Kode dan nama jenis penawaran wajib diisi.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                db()->prepare('UPDATE jenis_penawaran SET kode=?, nama=?, template_hal=?, template_body=?, template_pembayaran=? WHERE id=?')
                    ->execute([$kode, $nama, $hal, $body, $bayar, $id]);
                log_activity((int)$u['id'], 'update_jenis', $kode);
            } else {
                db()->prepare('INSERT INTO jenis_penawaran (kode,nama,template_hal,template_body,template_pembayaran) VALUES (?,?,?,?,?)')
                    ->execute([$kode, $nama, $hal, $body, $bayar]);
                log_activity((int)$u['id'], 'create_jenis', $kode);
            }
            flash_set('success', 'Jenis penawaran tersimpan.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE jenis_penawaran SET is_active = 0 WHERE id = ?')->execute([$id]);
        flash_set('success', 'Jenis penawaran dinonaktifkan.');
    }
    redirect('/admin/jenis.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM jenis_penawaran WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}
$rows = db()->query('SELECT * FROM jenis_penawaran WHERE is_active = 1 ORDER BY nama ASC')->fetchAll();

$pageTitle = 'Jenis Penawaran';
$activeNav = 'jenis';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Jenis Penawaran &amp; Kode</h1></div>

<div class="card">
  <h3 style="margin-top:0;font-size:1rem"><?= $editRow ? 'Ubah Jenis: ' . e($editRow['nama']) : 'Tambah Jenis Penawaran Baru' ?></h3>
  <p style="color:var(--text-muted);font-size:.85rem;margin-top:-.5rem">Gunakan token <code>{instansi}</code>, <code>{media}</code>, <code>{bulan}</code>, <code>{tahun}</code> (huruf besar untuk versi KAPITAL) pada template. Template ini otomatis mengisi teks surat &amp; kwitansi.</p>
  <form method="post" data-testid="jenis-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Kode (untuk nomor surat)</label><input type="text" name="kode" required value="<?= e($editRow['kode'] ?? '') ?>" data-testid="jenis-kode-input" placeholder="Adv"></div>
      <div class="field"><label>Nama Jenis</label><input type="text" name="nama" required value="<?= e($editRow['nama'] ?? '') ?>" data-testid="jenis-nama-input" placeholder="Advertorial"></div>
    </div>
    <div class="field"><label>Template Hal (subjek surat)</label><input type="text" name="template_hal" value="<?= e($editRow['template_hal'] ?? '') ?>" data-testid="jenis-hal-input"></div>
    <div class="field"><label>Template Isi Surat</label><textarea name="template_body" rows="5" data-testid="jenis-body-input"><?= e($editRow['template_body'] ?? '') ?></textarea></div>
    <div class="field"><label>Template Keterangan Pembayaran (Kwitansi)</label><input type="text" name="template_pembayaran" value="<?= e($editRow['template_pembayaran'] ?? '') ?>" data-testid="jenis-bayar-input"></div>
    <button class="btn btn-primary" type="submit" data-testid="jenis-save-btn"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Jenis' ?></button>
    <?php if ($editRow): ?><a href="/admin/jenis.php" class="btn" data-testid="jenis-cancel-edit">Batal</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
  <table>
    <thead><tr><th>Kode</th><th>Nama</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-testid="jenis-row-<?= e($r['kode']) ?>">
        <td><?= e($r['kode']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td>
          <a class="btn btn-sm" href="/admin/jenis.php?edit=<?= (int)$r['id'] ?>" data-testid="jenis-edit-<?= e($r['kode']) ?>">Ubah</a>
          <form method="post" style="display:inline" data-confirm="Nonaktifkan jenis ini?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit" data-testid="jenis-delete-<?= e($r['kode']) ?>">Nonaktifkan</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
