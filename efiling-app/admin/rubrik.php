<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $nama = trim($_POST['nama_rubrik'] ?? '');
        if ($nama === '') {
            flash_set('error', 'Nama rubrik wajib diisi.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                db()->prepare('UPDATE rubrik SET nama_rubrik=? WHERE id=?')->execute([$nama, $id]);
                log_activity((int)$u['id'], 'update_rubrik', $nama);
            } else {
                db()->prepare('INSERT INTO rubrik (nama_rubrik) VALUES (?)')->execute([$nama]);
                log_activity((int)$u['id'], 'create_rubrik', $nama);
            }
            flash_set('success', 'Rubrik/Item tersimpan.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE rubrik SET is_active = 0 WHERE id = ?')->execute([$id]);
        flash_set('success', 'Rubrik/Item dinonaktifkan.');
    }
    redirect('/admin/rubrik.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM rubrik WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}
$rows = db()->query('SELECT * FROM rubrik WHERE is_active = 1 ORDER BY nama_rubrik ASC')->fetchAll();

$pageTitle = 'Rubrik/Item';
$activeNav = 'rubrik';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Rubrik/Item</h1></div>

<div class="card">
  <h3 style="margin-top:0;font-size:1rem"><?= $editRow ? 'Ubah Rubrik: ' . e($editRow['nama_rubrik']) : 'Tambah Rubrik/Item Baru' ?></h3>
  <p style="color:var(--text-muted);font-size:.85rem;margin-top:-.5rem">Daftar nama rubrik/item ini akan muncul sebagai pilihan saat membuat Surat Penawaran &amp; Kwitansi, jadi tidak perlu ketik ulang.</p>
  <form method="post" data-testid="rubrik-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">
    <div class="field"><label>Nama Rubrik/Item</label><input type="text" name="nama_rubrik" required value="<?= e($editRow['nama_rubrik'] ?? '') ?>" data-testid="rubrik-nama-input" placeholder="Advertorial"></div>
    <button class="btn btn-primary" type="submit" data-testid="rubrik-save-btn"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Rubrik' ?></button>
    <?php if ($editRow): ?><a href="/admin/rubrik.php" class="btn" data-testid="rubrik-cancel-edit">Batal</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="table-wrap">
  <table>
    <thead><tr><th>Nama Rubrik/Item</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-testid="rubrik-row-<?= (int)$r['id'] ?>">
        <td><?= e($r['nama_rubrik']) ?></td>
        <td>
          <a class="btn btn-sm" href="/admin/rubrik.php?edit=<?= (int)$r['id'] ?>" data-testid="rubrik-edit-<?= (int)$r['id'] ?>">Ubah</a>
          <form method="post" style="display:inline" data-confirm="Nonaktifkan rubrik ini?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit" data-testid="rubrik-delete-<?= (int)$r['id'] ?>">Nonaktifkan</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
