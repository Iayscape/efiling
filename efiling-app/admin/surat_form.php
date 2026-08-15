<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/pdf_generator.php';
$u = require_login();

function sync_kwitansi_from_surat(int $suratId): void {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM surat_penawaran WHERE id = ?');
    $stmt->execute([$suratId]);
    $surat = $stmt->fetch();
    if (!$surat) return;

    $itemsStmt = $pdo->prepare('SELECT * FROM surat_penawaran_items WHERE surat_id = ? ORDER BY urutan');
    $itemsStmt->execute([$suratId]);
    $items = $itemsStmt->fetchAll();

    $mediaStmt = $pdo->prepare('SELECT * FROM media WHERE id = ?');
    $mediaStmt->execute([$surat['media_id']]);
    $media = $mediaStmt->fetch();

    $instansiStmt = $pdo->prepare('SELECT * FROM instansi WHERE id = ?');
    $instansiStmt->execute([$surat['instansi_id']]);
    $instansi = $instansiStmt->fetch();

    $jenisStmt = $pdo->prepare('SELECT * FROM jenis_penawaran WHERE id = ?');
    $jenisStmt->execute([$surat['jenis_id']]);
    $jenis = $jenisStmt->fetch();

    $total = 0;
    foreach ($items as $it) $total += (float)$it['harga_bulan'];

    $tokens = build_tokens($instansi['nama'], $media['nama'], (int)$surat['bulan'], $surat['bulan_label'], (int)$surat['tahun']);
    $pembayaran = $jenis ? render_template($jenis['template_pembayaran'], $tokens) : '';
    $terbilang = terbilang_rupiah($total);

    $kStmt = $pdo->prepare('SELECT * FROM kwitansi WHERE surat_id = ?');
    $kStmt->execute([$suratId]);
    $kwitansi = $kStmt->fetch();

    if ($kwitansi) {
        $pdo->prepare('UPDATE kwitansi SET media_id=?, jenis_id=?, instansi_id=?, tanggal=?, bulan=?, bulan_label=?, tahun=?, untuk_pembayaran=?, jumlah=?, terbilang=? WHERE id=?')
            ->execute([$surat['media_id'], $surat['jenis_id'], $surat['instansi_id'], $surat['tanggal'], $surat['bulan'], $surat['bulan_label'], $surat['tahun'], $pembayaran, $total, $terbilang, $kwitansi['id']]);
        $kwitansiId = $kwitansi['id'];
        $pdo->prepare('DELETE FROM kwitansi_items WHERE kwitansi_id = ?')->execute([$kwitansiId]);
    } else {
        $nomorK = generate_nomor_kwitansi($media, (int)$surat['bulan'], (int)$surat['tahun']);
            $diterimaDari = 'BENDAHARA PENGELUARAN SEKRETARIAT ' . mb_strtoupper($instansi['nama']);
        $pdo->prepare('INSERT INTO kwitansi (nomor_kwitansi, surat_id, media_id, jenis_id, instansi_id, tanggal, bulan, bulan_label, tahun, diterima_dari, untuk_pembayaran, jumlah, terbilang, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$nomorK, $suratId, $surat['media_id'], $surat['jenis_id'], $surat['instansi_id'], $surat['tanggal'], $surat['bulan'], $surat['bulan_label'], $surat['tahun'], $diterimaDari, $pembayaran, $total, $terbilang, $surat['created_by']]);
        $kwitansiId = (int)$pdo->lastInsertId();
    }

    $urutan = 0;
    foreach ($items as $it) {
        $pdo->prepare('INSERT INTO kwitansi_items (kwitansi_id, nama_item, harga, urutan) VALUES (?,?,?,?)')
            ->execute([$kwitansiId, $it['nama_rubrik'], $it['harga_bulan'], $urutan++]);
    }
}

$id = (int)($_GET['id'] ?? 0);
$surat = null;
$items = [];
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM surat_penawaran WHERE id = ?');
    $stmt->execute([$id]);
    $surat = $stmt->fetch();
    if (!$surat) { flash_set('error', 'Surat tidak ditemukan.'); redirect('/admin/arsip.php'); }
    $itStmt = db()->prepare('SELECT * FROM surat_penawaran_items WHERE surat_id = ? ORDER BY urutan');
    $itStmt->execute([$id]);
    $items = $itStmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $instansiId = (int)$_POST['instansi_id'];
    $mediaId = (int)$_POST['media_id'];
    $jenisId = (int)$_POST['jenis_id'];
    $tanggal = $_POST['tanggal'];
    $bulanLabel = trim($_POST['bulan_label'] ?? '');
    $tahun = (int)$_POST['tahun'];
    $bulan = (int)date('n', strtotime($tanggal));
    $hal = trim($_POST['hal'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');
    $namaArr = $_POST['item_nama'] ?? [];
    $ketArr = $_POST['item_ket'] ?? [];
    $hargaArr = $_POST['item_harga'] ?? [];
    $hargaTahunArr = $_POST['item_harga_tahun'] ?? [];

    if ($instansiId <= 0 || $mediaId <= 0 || $jenisId <= 0 || empty($namaArr)) {
        flash_set('error', 'Lengkapi instansi, media, jenis, dan minimal 1 item.');
        redirect($id > 0 ? "/admin/surat_form.php?id=$id" : '/admin/surat_form.php');
    }

    $mediaStmt = db()->prepare('SELECT * FROM media WHERE id = ?');
    $mediaStmt->execute([$mediaId]);
    $media = $mediaStmt->fetch();

    $pdo = db();
    if ($id > 0) {
        $pdo->prepare('UPDATE surat_penawaran SET instansi_id=?, media_id=?, jenis_id=?, tanggal=?, bulan=?, bulan_label=?, tahun=?, hal=?, body=?, catatan=? WHERE id=?')
            ->execute([$instansiId, $mediaId, $jenisId, $tanggal, $bulan, $bulanLabel, $tahun, $hal, $body, $catatan, $id]);
        $pdo->prepare('DELETE FROM surat_penawaran_items WHERE surat_id = ?')->execute([$id]);
        $suratId = $id;
        log_activity((int)$u['id'], 'update_surat', $surat['nomor_surat']);
    } else {
        $jenisStmt = $pdo->prepare('SELECT kode FROM jenis_penawaran WHERE id = ?');
        $jenisStmt->execute([$jenisId]);
        $jenisKode = $jenisStmt->fetchColumn();
        $nomorSurat = generate_nomor_surat($media, $jenisKode, $bulan, $tahun);
        $pdo->prepare('INSERT INTO surat_penawaran (nomor_surat, media_id, jenis_id, instansi_id, tanggal, bulan, bulan_label, tahun, hal, body, catatan, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$nomorSurat, $mediaId, $jenisId, $instansiId, $tanggal, $bulan, $bulanLabel, $tahun, $hal, $body, $catatan, $u['id']]);
        $suratId = (int)$pdo->lastInsertId();
        log_activity((int)$u['id'], 'create_surat', $nomorSurat);
    }

    $urutan = 0;
    foreach ($namaArr as $i => $nama) {
        if (trim($nama) === '') continue;
        $pdo->prepare('INSERT INTO surat_penawaran_items (surat_id, nama_rubrik, keterangan, harga_bulan, harga_tahun, urutan) VALUES (?,?,?,?,?,?)')
            ->execute([$suratId, $nama, $ketArr[$i] ?? '', (float)str_replace(['.', ','], ['', '.'], $hargaArr[$i] ?? 0), $hargaTahunArr[$i] !== '' ? (float)str_replace(['.', ','], ['', '.'], $hargaTahunArr[$i]) : null, $urutan++]);
    }

    sync_kwitansi_from_surat($suratId);

    flash_set('success', 'Surat penawaran tersimpan & kwitansi otomatis dibuat/diperbarui.');
    redirect("/admin/surat_form.php?id=$suratId");
}

$instansiList = db()->query('SELECT * FROM instansi WHERE is_active = 1 ORDER BY nama')->fetchAll();
$mediaList = db()->query('SELECT * FROM media WHERE is_active = 1 ORDER BY kode')->fetchAll();
$jenisList = db()->query('SELECT * FROM jenis_penawaran WHERE is_active = 1 ORDER BY nama')->fetchAll();

$linkedKwitansi = null;
if ($id > 0) {
    $kStmt = db()->prepare('SELECT * FROM kwitansi WHERE surat_id = ?');
    $kStmt->execute([$id]);
    $linkedKwitansi = $kStmt->fetch();
}

$pageTitle = $id > 0 ? 'Ubah Surat Penawaran' : 'Surat Penawaran Baru';
$activeNav = 'surat-baru';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar">
  <h1 data-testid="page-title"><?= $id > 0 ? 'Ubah Surat: ' . e($surat['nomor_surat']) : 'Surat Penawaran Baru' ?></h1>
  <?php if ($linkedKwitansi): ?>
  <a class="btn" href="/admin/download.php?type=kwitansi&id=<?= (int)$linkedKwitansi['id'] ?>&format=pdf&action=view" target="_blank" data-testid="view-linked-kwitansi-btn">Lihat Kwitansi: <?= e($linkedKwitansi['nomor_kwitansi']) ?></a>
  <?php endif; ?>
</div>

<form method="post" class="card" data-testid="surat-form">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field">
      <label>Instansi Tujuan</label>
      <select name="instansi_id" id="instansi_id" required data-testid="surat-instansi-select">
        <option value="">-- Pilih Instansi --</option>
        <?php foreach ($instansiList as $i): ?>
        <option value="<?= (int)$i['id'] ?>" <?= ($surat['instansi_id'] ?? '') == $i['id'] ? 'selected' : '' ?>><?= e($i['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Media</label>
      <select name="media_id" id="media_id" required data-testid="surat-media-select">
        <option value="">-- Pilih Media --</option>
        <?php foreach ($mediaList as $m): ?>
        <option value="<?= (int)$m['id'] ?>" <?= ($surat['media_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= e($m['kode'] . ' - ' . $m['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Jenis Penawaran</label>
      <select name="jenis_id" id="jenis_id" required data-testid="surat-jenis-select">
        <option value="">-- Pilih Jenis --</option>
        <?php foreach ($jenisList as $j): ?>
        <option value="<?= (int)$j['id'] ?>" <?= ($surat['jenis_id'] ?? '') == $j['id'] ? 'selected' : '' ?>><?= e($j['nama']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Tanggal</label>
      <input type="date" name="tanggal" id="tanggal" value="<?= e($surat['tanggal'] ?? date('Y-m-d')) ?>" required data-testid="surat-tanggal-input">
    </div>
    <div class="field">
      <label>Label Bulan (bisa rentang, misal "Juli-September")</label>
      <input type="text" name="bulan_label" id="bulan_label" value="<?= e($surat['bulan_label'] ?? '') ?>" data-testid="surat-bulan-label-input">
    </div>
    <div class="field">
      <label>Tahun</label>
      <input type="number" name="tahun" id="tahun" value="<?= e($surat['tahun'] ?? date('Y')) ?>" required data-testid="surat-tahun-input">
    </div>
  </div>

  <div class="field">
    <label>Hal (Subjek Surat) <button type="button" class="btn btn-sm" id="btn-refill" data-testid="surat-refill-template-btn">Isi dari Template</button></label>
    <input type="text" name="hal" id="hal" value="<?= e($surat['hal'] ?? '') ?>" data-testid="surat-hal-input">
  </div>
  <div class="field">
    <label>Isi Surat</label>
    <textarea name="body" id="body" rows="6" data-testid="surat-body-input"><?= e($surat['body'] ?? '') ?></textarea>
  </div>
  <div class="field">
    <label>Catatan Internal (opsional, tidak tercetak)</label>
    <textarea name="catatan" rows="2" data-testid="surat-catatan-input"><?= e($surat['catatan'] ?? '') ?></textarea>
  </div>

  <h3 style="font-size:1rem">Rubrik/Item Penawaran</h3>
  <div id="item-rows" data-testid="surat-item-rows">
    <?php if ($items): foreach ($items as $it): ?>
    <div class="item-row">
      <div><label>Nama Rubrik</label><input type="text" name="item_nama[]" value="<?= e($it['nama_rubrik']) ?>" required data-testid="item-nama-input"></div>
      <div><label>Keterangan</label><input type="text" name="item_ket[]" value="<?= e($it['keterangan']) ?>" data-testid="item-ket-input"></div>
      <div><label>Harga/Bulan</label><input type="text" name="item_harga[]" value="<?= e($it['harga_bulan']) ?>" class="item-harga" required data-testid="item-harga-input"></div>
      <div><label>Harga/Tahun (opsional)</label><input type="text" name="item_harga_tahun[]" value="<?= e($it['harga_tahun']) ?>" data-testid="item-harga-tahun-input"></div>
      <button type="button" class="btn-remove" data-testid="item-remove-btn">&times;</button>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <template id="item-row-template">
    <div class="item-row">
      <div><label>Nama Rubrik</label><input type="text" name="item_nama[]" required data-testid="item-nama-input"></div>
      <div><label>Keterangan</label><input type="text" name="item_ket[]" data-testid="item-ket-input"></div>
      <div><label>Harga/Bulan</label><input type="text" name="item_harga[]" class="item-harga" required data-testid="item-harga-input"></div>
      <div><label>Harga/Tahun (opsional)</label><input type="text" name="item_harga_tahun[]" data-testid="item-harga-tahun-input"></div>
      <button type="button" class="btn-remove" data-testid="item-remove-btn">&times;</button>
    </div>
  </template>
  <button type="button" class="btn" id="btn-add-item" data-testid="surat-add-item-btn">+ Tambah Item</button>
  <div class="total-line">Total: <span id="total-display" data-testid="surat-total-display">Rp0,-</span></div>

  <div style="margin-top:1.5rem">
    <button class="btn btn-primary" type="submit" data-testid="surat-save-btn"><?= $id > 0 ? 'Simpan Perubahan' : 'Simpan & Buat Kwitansi Otomatis' ?></button>
    <?php if ($id > 0): ?>
    <a class="btn" href="/admin/download.php?type=surat&id=<?= $id ?>&format=pdf&action=view" target="_blank" data-testid="surat-view-pdf-btn">Lihat PDF</a>
    <a class="btn" href="/admin/download.php?type=surat&id=<?= $id ?>&format=word&action=download" data-testid="surat-download-word-btn">Unduh Word</a>
    <?php endif; ?>
  </div>
</form>

<script type="application/json" id="jenis-templates-data"><?= json_encode(array_column($jenisList, null, 'id')) ?></script>
<script type="application/json" id="instansi-names-data"><?= json_encode(array_column($instansiList, 'nama', 'id')) ?></script>
<script type="application/json" id="media-names-data"><?= json_encode(array_column($mediaList, 'nama', 'id')) ?></script>
<script src="/assets/js/surat-form.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
