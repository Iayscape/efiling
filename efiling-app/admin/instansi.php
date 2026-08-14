<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $jabatan = trim($_POST['jabatan_penerima'] ?? '');
    $cq = trim($_POST['cq'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? 'Tempat');
    $ket = trim($_POST['keterangan'] ?? '');

    if ($action === 'save') {
        if ($nama === '') {
            flash_set('error', 'Nama instansi wajib diisi.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                db()->prepare('UPDATE instansi SET nama=?, jabatan_penerima=?, cq=?, lokasi=?, keterangan=? WHERE id=?')
                    ->execute([$nama, $jabatan, $cq, $lokasi, $ket, $id]);
                log_activity((int)$u['id'], 'update_instansi', $nama);
            } else {
                db()->prepare('INSERT INTO instansi (nama, jabatan_penerima, cq, lokasi, keterangan) VALUES (?,?,?,?,?)')
                    ->execute([$nama, $jabatan, $cq, $lokasi, $ket]);
                log_activity((int)$u['id'], 'create_instansi', $nama);
            }
            flash_set('success', 'Data instansi tersimpan.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE instansi SET is_active = 0 WHERE id = ?')->execute([$id]);
        log_activity((int)$u['id'], 'delete_instansi', "id=$id");
        flash_set('success', 'Instansi dihapus dari daftar aktif.');
    }
    redirect('/admin/instansi.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM instansi WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}

$rows = db()->query('SELECT * FROM instansi WHERE is_active = 1 ORDER BY nama ASC')->fetchAll();

$pageTitle = 'Instansi Tujuan';
$activeNav = 'instansi';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Instansi Tujuan Surat</h1></div>

<div class="card">
  <h3 style="margin-top:0;font-size:1rem"><?= $editRow ? 'Ubah Instansi' : 'Tambah Instansi Baru' ?></h3>
  <form method="post" data-testid="instansi-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Nama Instansi</label><input type="text" name="nama" required value="<?= e($editRow['nama'] ?? '') ?>" data-testid="instansi-nama-input" placeholder="Contoh: DPRD Kota Banjarmasin"></div>
      <div class="field"><label>Jabatan Penerima (Kepada Yth)</label><input type="text" name="jabatan_penerima" value="<?= e($editRow['jabatan_penerima'] ?? '') ?>" data-testid="instansi-jabatan-input" placeholder="Contoh: KETUA DPRD KOTA BANJARMASIN"></div>
      <div class="field"><label>C.q</label><input type="text" name="cq" value="<?= e($editRow['cq'] ?? '') ?>" data-testid="instansi-cq-input" placeholder="Contoh: Sekwan DPRD Kota Banjarmasin"></div>
      <div class="field"><label>Lokasi</label><input type="text" name="lokasi" value="<?= e($editRow['lokasi'] ?? 'Tempat') ?>" data-testid="instansi-lokasi-input"></div>
    </div>
    <div class="field"><label>Keterangan (opsional)</label><textarea name="keterangan" rows="2" data-testid="instansi-keterangan-input"><?= e($editRow['keterangan'] ?? '') ?></textarea></div>
    <button class="btn btn-primary" type="submit" data-testid="instansi-save-btn"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Instansi' ?></button>
    <?php if ($editRow): ?><a href="/admin/instansi.php" class="btn" data-testid="instansi-cancel-edit">Batal</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
  <table>
    <thead><tr><th>Nama Instansi</th><th>Jabatan Penerima</th><th>C.q</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-testid="instansi-row-<?= (int)$r['id'] ?>">
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['jabatan_penerima']) ?></td>
        <td><?= e($r['cq']) ?></td>
        <td>
          <a class="btn btn-sm" href="/admin/instansi.php?edit=<?= (int)$r['id'] ?>" data-testid="instansi-edit-<?= (int)$r['id'] ?>">Ubah</a>
          <form method="post" style="display:inline" data-confirm="Hapus instansi ini?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit" data-testid="instansi-delete-<?= (int)$r['id'] ?>">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
