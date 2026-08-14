<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

function handle_upload(string $field, string $subdir): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime]) || $f['size'] > 2 * 1024 * 1024) return null;
    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../storage/uploads/' . $subdir . '/' . $name;
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        return 'storage/uploads/' . $subdir . '/' . $name;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role(['admin']);
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $fields = ['kode', 'nama', 'tipe', 'perusahaan', 'alamat_redaksi', 'telp', 'email', 'facebook', 'twitter', 'akta_notaris', 'ahu_number', 'npwp', 'signatory_name', 'signatory_title', 'accent_color'];
        $vals = [];
        foreach ($fields as $f) $vals[$f] = trim($_POST[$f] ?? '');

        if ($vals['kode'] === '' || $vals['nama'] === '' || $vals['signatory_name'] === '') {
            flash_set('error', 'Kode media, nama, dan nama penandatangan wajib diisi.');
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $logo = handle_upload('logo_file', 'logos');
            $signature = handle_upload('signature_file', 'signatures');
            $stempel = handle_upload('stempel_file', 'stempel');

            if ($id > 0) {
                $sql = 'UPDATE media SET kode=?, nama=?, tipe=?, perusahaan=?, alamat_redaksi=?, telp=?, email=?, facebook=?, twitter=?, akta_notaris=?, ahu_number=?, npwp=?, signatory_name=?, signatory_title=?, accent_color=?';
                $params = array_values($vals);
                if ($logo) { $sql .= ', logo_path=?'; $params[] = $logo; }
                if ($signature) { $sql .= ', signature_path=?'; $params[] = $signature; }
                if ($stempel) { $sql .= ', stempel_path=?'; $params[] = $stempel; }
                $sql .= ' WHERE id=?';
                $params[] = $id;
                db()->prepare($sql)->execute($params);
                log_activity((int)$u['id'], 'update_media', $vals['kode']);
            } else {
                db()->prepare('INSERT INTO media (kode,nama,tipe,perusahaan,alamat_redaksi,telp,email,facebook,twitter,akta_notaris,ahu_number,npwp,signatory_name,signatory_title,accent_color,logo_path,signature_path,stempel_path) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute([...array_values($vals), $logo, $signature, $stempel]);
                log_activity((int)$u['id'], 'create_media', $vals['kode']);
            }
            flash_set('success', 'Data media tersimpan.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('UPDATE media SET is_active = 0 WHERE id = ?')->execute([$id]);
        log_activity((int)$u['id'], 'delete_media', "id=$id");
        flash_set('success', 'Media dinonaktifkan.');
    }
    redirect('/admin/media.php');
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM media WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}
$rows = db()->query('SELECT * FROM media WHERE is_active = 1 ORDER BY kode ASC')->fetchAll();

$pageTitle = 'Media & Legalitas';
$activeNav = 'media';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Media &amp; Data Legalitas</h1></div>

<?php if ($u['role'] === 'admin'): ?>
<div class="card">
  <h3 style="margin-top:0;font-size:1rem"><?= $editRow ? 'Ubah Media: ' . e($editRow['kode']) : 'Tambah Media Baru' ?></h3>
  <form method="post" enctype="multipart/form-data" data-testid="media-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= e($editRow['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Kode Media</label><input type="text" name="kode" required value="<?= e($editRow['kode'] ?? '') ?>" data-testid="media-kode-input" placeholder="KB"></div>
      <div class="field"><label>Nama Media</label><input type="text" name="nama" required value="<?= e($editRow['nama'] ?? '') ?>" data-testid="media-nama-input"></div>
      <div class="field"><label>Tipe</label>
        <select name="tipe" data-testid="media-tipe-input">
          <?php foreach (['cetak' => 'Cetak', 'online' => 'Online', 'cetak_online' => 'Cetak & Online'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= ($editRow['tipe'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Nama Perusahaan (PT)</label><input type="text" name="perusahaan" required value="<?= e($editRow['perusahaan'] ?? '') ?>" data-testid="media-perusahaan-input"></div>
      <div class="field"><label>Warna Aksen</label><input type="color" name="accent_color" value="<?= e($editRow['accent_color'] ?? '#1d4ed8') ?>" data-testid="media-accent-input"></div>
      <div class="field"><label>Telp/HP</label><input type="text" name="telp" value="<?= e($editRow['telp'] ?? '') ?>" data-testid="media-telp-input"></div>
      <div class="field"><label>Email</label><input type="text" name="email" value="<?= e($editRow['email'] ?? '') ?>" data-testid="media-email-input"></div>
      <div class="field"><label>Facebook</label><input type="text" name="facebook" value="<?= e($editRow['facebook'] ?? '') ?>" data-testid="media-fb-input"></div>
      <div class="field"><label>Twitter</label><input type="text" name="twitter" value="<?= e($editRow['twitter'] ?? '') ?>" data-testid="media-tw-input"></div>
      <div class="field"><label>Nama Penandatangan</label><input type="text" name="signatory_name" required value="<?= e($editRow['signatory_name'] ?? '') ?>" data-testid="media-signatory-input"></div>
      <div class="field"><label>Jabatan Penandatangan</label><input type="text" name="signatory_title" value="<?= e($editRow['signatory_title'] ?? 'Pemimpin Perusahaan') ?>" data-testid="media-signatory-title-input"></div>
      <div class="field"><label>Akta Notaris</label><input type="text" name="akta_notaris" value="<?= e($editRow['akta_notaris'] ?? '') ?>" data-testid="media-akta-input"></div>
      <div class="field"><label>Nomor AHU</label><input type="text" name="ahu_number" value="<?= e($editRow['ahu_number'] ?? '') ?>" data-testid="media-ahu-input"></div>
      <div class="field"><label>NPWP</label><input type="text" name="npwp" value="<?= e($editRow['npwp'] ?? '') ?>" data-testid="media-npwp-input"></div>
    </div>
    <div class="field"><label>Alamat Redaksi</label><textarea name="alamat_redaksi" rows="2" data-testid="media-alamat-input"><?= e($editRow['alamat_redaksi'] ?? '') ?></textarea></div>
    <div class="form-grid">
      <div class="field"><label>Logo (PNG/JPG, opsional)</label><input type="file" name="logo_file" accept="image/png,image/jpeg" data-testid="media-logo-file"></div>
      <div class="field"><label>Tanda Tangan (PNG transparan, opsional)</label><input type="file" name="signature_file" accept="image/png,image/jpeg" data-testid="media-signature-file"></div>
      <div class="field"><label>Stempel (PNG transparan, opsional)</label><input type="file" name="stempel_file" accept="image/png,image/jpeg" data-testid="media-stempel-file"></div>
    </div>
    <button class="btn btn-primary" type="submit" data-testid="media-save-btn"><?= $editRow ? 'Simpan Perubahan' : 'Tambah Media' ?></button>
    <?php if ($editRow): ?><a href="/admin/media.php" class="btn" data-testid="media-cancel-edit">Batal</a><?php endif; ?>
  </form>
</div>
<?php endif; ?>

<div class="card">
  <div class="table-wrap">
  <table>
    <thead><tr><th>Kode</th><th>Nama Media</th><th>Perusahaan</th><th>Penandatangan</th><?php if ($u['role'] === 'admin'): ?><th></th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr data-testid="media-row-<?= e($r['kode']) ?>">
        <td><strong style="color:<?= e($r['accent_color']) ?>"><?= e($r['kode']) ?></strong></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['perusahaan']) ?></td>
        <td><?= e($r['signatory_name']) ?></td>
        <?php if ($u['role'] === 'admin'): ?>
        <td>
          <a class="btn btn-sm" href="/admin/media.php?edit=<?= (int)$r['id'] ?>" data-testid="media-edit-<?= e($r['kode']) ?>">Ubah</a>
          <form method="post" style="display:inline" data-confirm="Nonaktifkan media ini?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-danger" type="submit" data-testid="media-delete-<?= e($r['kode']) ?>">Nonaktifkan</button>
          </form>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
