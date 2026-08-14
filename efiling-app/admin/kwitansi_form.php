<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

$id = (int)($_GET['id'] ?? 0);
$kwitansi = null;
$items = [];
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM kwitansi WHERE id = ?');
    $stmt->execute([$id]);
    $kwitansi = $stmt->fetch();
    if (!$kwitansi) { flash_set('error', 'Kwitansi tidak ditemukan.'); redirect('/admin/arsip.php'); }
    $itStmt = db()->prepare('SELECT * FROM kwitansi_items WHERE kwitansi_id = ? ORDER BY urutan');
    $itStmt->execute([$id]);
    $items = $itStmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $instansiId = (int)$_POST['instansi_id'];
    $mediaId = (int)$_POST['media_id'];
    $tanggal = $_POST['tanggal'];
    $bulanLabel = trim($_POST['bulan_label'] ?? '');
    $tahun = (int)$_POST['tahun'];
    $bulan = (int)date('n', strtotime($tanggal));
    $diterimaDari = trim($_POST['diterima_dari'] ?? '');
    $untukPembayaran = trim($_POST['untuk_pembayaran'] ?? '');
    $gunakanTtd = isset($_POST['gunakan_ttd_stempel']) ? 1 : 0;
    $namaArr = $_POST['item_nama'] ?? [];
    $hargaArr = $_POST['item_harga'] ?? [];

    if ($instansiId <= 0 || $mediaId <= 0 || empty($namaArr)) {
        flash_set('error', 'Lengkapi instansi, media, dan minimal 1 item.');
        redirect($id > 0 ? "/admin/kwitansi_form.php?id=$id" : '/admin/kwitansi_form.php');
    }

    $total = 0;
    foreach ($hargaArr as $h) $total += (float)str_replace(['.', ','], ['', '.'], $h);
    $terbilang = terbilang_rupiah($total);

    $pdo = db();
    if ($id > 0) {
        $pdo->prepare('UPDATE kwitansi SET instansi_id=?, media_id=?, tanggal=?, bulan=?, bulan_label=?, tahun=?, diterima_dari=?, untuk_pembayaran=?, jumlah=?, terbilang=?, gunakan_ttd_stempel=? WHERE id=?')
            ->execute([$instansiId, $mediaId, $tanggal, $bulan, $bulanLabel, $tahun, $diterimaDari, $untukPembayaran, $total, $terbilang, $gunakanTtd, $id]);
        $kwitansiId = $id;
        $pdo->prepare('DELETE FROM kwitansi_items WHERE kwitansi_id = ?')->execute([$id]);
        log_activity((int)$u['id'], 'update_kwitansi', $kwitansi['nomor_kwitansi']);
    } else {
        $mediaStmt = $pdo->prepare('SELECT * FROM media WHERE id = ?');
        $mediaStmt->execute([$mediaId]);
        $media = $mediaStmt->fetch();
        $nomor = generate_nomor_kwitansi($media, $bulan, $tahun);
        $pdo->prepare('INSERT INTO kwitansi (nomor_kwitansi, media_id, instansi_id, tanggal, bulan, bulan_label, tahun, diterima_dari, untuk_pembayaran, jumlah, terbilang, gunakan_ttd_stempel, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$nomor, $mediaId, $instansiId, $tanggal, $bulan, $bulanLabel, $tahun, $diterimaDari, $untukPembayaran, $total, $terbilang, $gunakanTtd, $u['id']]);
        $kwitansiId = (int)$pdo->lastInsertId();
        log_activity((int)$u['id'], 'create_kwitansi', $nomor);
    }

    $urutan = 0;
    foreach ($namaArr as $i => $nama) {
        if (trim($nama) === '') continue;
        $pdo->prepare('INSERT INTO kwitansi_items (kwitansi_id, nama_item, harga, urutan) VALUES (?,?,?,?)')
            ->execute([$kwitansiId, $nama, (float)str_replace(['.', ','], ['', '.'], $hargaArr[$i] ?? 0), $urutan++]);
    }

    flash_set('success', 'Kwitansi tersimpan.');
    redirect("/admin/kwitansi_form.php?id=$kwitansiId");
}

$instansiList = db()->query('SELECT * FROM instansi WHERE is_active = 1 ORDER BY nama')->fetchAll();
$mediaList = db()->query('SELECT * FROM media WHERE is_active = 1 ORDER BY kode')->fetchAll();

$pageTitle = $id > 0 ? 'Ubah Kwitansi' : 'Kwitansi Baru';
$activeNav = 'arsip';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title"><?= $id > 0 ? 'Ubah Kwitansi: ' . e($kwitansi['nomor_kwitansi']) : 'Kwitansi Baru (Tanpa Surat)' ?></h1></div>

<?php if ($kwitansi && $kwitansi['surat_id']): ?>
<div class="alert alert-success" data-testid="kwitansi-linked-notice">Kwitansi ini terhubung otomatis dengan <a href="/admin/surat_form.php?id=<?= (int)$kwitansi['surat_id'] ?>">Surat Penawaran</a>. Perubahan pada surat akan menimpa ulang item &amp; jumlah di sini.</div>
<?php endif; ?>

<form method="post" class="card" data-testid="kwitansi-form">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field">
      <label>Instansi</label>
      <select name="instansi_id" required data-testid="kwitansi-instansi-select">
        <option value="">-- Pilih --</option>
        <?php foreach ($instansiList as $i): ?>
        <option value="<?= (int)$i['id'] ?>" <?= ($kwitansi['instansi_id'] ?? '') == $i['id'] ? 'selected' : '' ?>><?= e($i['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Media</label>
      <select name="media_id" required data-testid="kwitansi-media-select">
        <option value="">-- Pilih --</option>
        <?php foreach ($mediaList as $m): ?>
        <option value="<?= (int)$m['id'] ?>" <?= ($kwitansi['media_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= e($m['kode'] . ' - ' . $m['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Tanggal</label><input type="date" name="tanggal" value="<?= e($kwitansi['tanggal'] ?? date('Y-m-d')) ?>" required data-testid="kwitansi-tanggal-input"></div>
    <div class="field"><label>Label Bulan</label><input type="text" name="bulan_label" value="<?= e($kwitansi['bulan_label'] ?? '') ?>" data-testid="kwitansi-bulan-label-input"></div>
    <div class="field"><label>Tahun</label><input type="number" name="tahun" value="<?= e($kwitansi['tahun'] ?? date('Y')) ?>" required data-testid="kwitansi-tahun-input"></div>
  </div>
  <div class="field"><label>Telah Diterima Dari</label><input type="text" name="diterima_dari" value="<?= e($kwitansi['diterima_dari'] ?? '') ?>" data-testid="kwitansi-diterima-input"></div>
  <div class="field"><label>Untuk Pembayaran</label><textarea name="untuk_pembayaran" rows="2" data-testid="kwitansi-pembayaran-input"><?= e($kwitansi['untuk_pembayaran'] ?? '') ?></textarea></div>
  <div class="field"><label><input type="checkbox" name="gunakan_ttd_stempel" value="1" <?= !empty($kwitansi['gunakan_ttd_stempel']) ? 'checked' : '' ?> data-testid="kwitansi-ttd-checkbox"> Gunakan tanda tangan &amp; stempel otomatis (jika sudah diunggah di data Media)</label></div>

  <h3 style="font-size:1rem">Rincian Item</h3>
  <div id="item-rows" data-testid="kwitansi-item-rows">
    <?php if ($items): foreach ($items as $it): ?>
    <div class="item-row" style="grid-template-columns:3fr 2fr auto">
      <div><label>Nama Item</label><input type="text" name="item_nama[]" value="<?= e($it['nama_item']) ?>" required data-testid="k-item-nama-input"></div>
      <div><label>Harga</label><input type="text" name="item_harga[]" value="<?= e($it['harga']) ?>" class="item-harga" required data-testid="k-item-harga-input"></div>
      <button type="button" class="btn-remove" data-testid="k-item-remove-btn">&times;</button>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <template id="item-row-template">
    <div class="item-row" style="grid-template-columns:3fr 2fr auto">
      <div><label>Nama Item</label><input type="text" name="item_nama[]" required data-testid="k-item-nama-input"></div>
      <div><label>Harga</label><input type="text" name="item_harga[]" class="item-harga" required data-testid="k-item-harga-input"></div>
      <button type="button" class="btn-remove" data-testid="k-item-remove-btn">&times;</button>
    </div>
  </template>
  <button type="button" class="btn" id="btn-add-item" data-testid="kwitansi-add-item-btn">+ Tambah Item</button>
  <div class="total-line">Total: <span id="total-display" data-testid="kwitansi-total-display">Rp0,-</span></div>

  <div style="margin-top:1.5rem">
    <button class="btn btn-primary" type="submit" data-testid="kwitansi-save-btn">Simpan Kwitansi</button>
    <?php if ($id > 0): ?>
    <a class="btn" href="/admin/download.php?type=kwitansi&id=<?= $id ?>&format=pdf&action=view" target="_blank" data-testid="kwitansi-view-pdf-btn">Lihat PDF</a>
    <a class="btn" href="/admin/download.php?type=kwitansi&id=<?= $id ?>&format=word&action=download" data-testid="kwitansi-download-word-btn">Unduh Word</a>
    <?php endif; ?>
  </div>
</form>
<script src="/assets/js/surat-form.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
