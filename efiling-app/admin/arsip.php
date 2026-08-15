<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$u = require_login();

$instansiId = (int)($_GET['instansi_id'] ?? 0);
$mediaId = (int)($_GET['media_id'] ?? 0);
$tahun = (int)($_GET['tahun'] ?? 0);
$tipe = $_GET['tipe'] ?? 'semua';
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
if ($instansiId) { $where[] = 'i.id = ?'; $params[] = $instansiId; }
if ($mediaId) { $where[] = 'm.id = ?'; $params[] = $mediaId; }
if ($tahun) { $where[] = 'd.tahun = ?'; $params[] = $tahun; }
if ($q !== '') { $where[] = '(d.nomor LIKE ? OR d.judul LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sqlParts = [];
if ($tipe === 'semua' || $tipe === 'penawaran') {
    $sqlParts[] = "SELECT 'surat' AS tipe, s.id, s.nomor_surat AS nomor, s.hal AS judul, s.tahun, s.bulan_label, s.tanggal, i.id AS instansi_id, i.nama AS instansi_nama, m.id AS media_id, m.kode AS media_kode
      FROM surat_penawaran s JOIN instansi i ON i.id=s.instansi_id JOIN media m ON m.id=s.media_id";
}
if ($tipe === 'semua' || $tipe === 'kwitansi') {
    $sqlParts[] = "SELECT 'kwitansi' AS tipe, k.id, k.nomor_kwitansi AS nomor, k.untuk_pembayaran AS judul, k.tahun, k.bulan_label, k.tanggal, i.id AS instansi_id, i.nama AS instansi_nama, m.id AS media_id, m.kode AS media_kode
      FROM kwitansi k JOIN instansi i ON i.id=k.instansi_id JOIN media m ON m.id=k.media_id";
}
$union = implode(' UNION ALL ', $sqlParts);
$sql = "SELECT * FROM ($union) d $whereSql ORDER BY d.tanggal DESC, d.id DESC LIMIT 500";

$finalParams = [];
foreach ($sqlParts as $_) { $finalParams = array_merge($finalParams, $params); }
$stmt = db()->prepare($sql);
$stmt->execute($finalParams);
$rows = $stmt->fetchAll();

$instansiList = db()->query('SELECT * FROM instansi WHERE is_active=1 ORDER BY nama')->fetchAll();
$mediaList = db()->query('SELECT * FROM media WHERE is_active=1 ORDER BY kode')->fetchAll();
$tahunList = db()->query("SELECT DISTINCT tahun FROM ((SELECT tahun FROM surat_penawaran) UNION (SELECT tahun FROM kwitansi)) t ORDER BY tahun DESC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Arsip Digital';
$activeNav = 'arsip';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<div class="topbar"><h1 data-testid="page-title">Arsip Digital</h1></div>

<div class="card">
  <form method="get" class="filters" data-testid="arsip-filter-form">
    <select name="instansi_id" data-testid="filter-instansi-select">
      <option value="0">Semua Instansi</option>
      <?php foreach ($instansiList as $i): ?><option value="<?= (int)$i['id'] ?>" <?= $instansiId == $i['id'] ? 'selected' : '' ?>><?= e($i['nama']) ?></option><?php endforeach; ?>
    </select>
    <select name="media_id" data-testid="filter-media-select">
      <option value="0">Semua Media</option>
      <?php foreach ($mediaList as $m): ?><option value="<?= (int)$m['id'] ?>" <?= $mediaId == $m['id'] ? 'selected' : '' ?>><?= e($m['kode']) ?></option><?php endforeach; ?>
    </select>
    <select name="tahun" data-testid="filter-tahun-select">
      <option value="0">Semua Tahun</option>
      <?php foreach ($tahunList as $t): ?><option value="<?= (int)$t ?>" <?= $tahun == $t ? 'selected' : '' ?>><?= (int)$t ?></option><?php endforeach; ?>
    </select>
    <select name="tipe" data-testid="filter-tipe-select">
      <option value="semua" <?= $tipe === 'semua' ? 'selected' : '' ?>>Semua Jenis Dokumen</option>
      <option value="penawaran" <?= $tipe === 'penawaran' ? 'selected' : '' ?>>Penawaran</option>
      <option value="kwitansi" <?= $tipe === 'kwitansi' ? 'selected' : '' ?>>Kwitansi</option>
    </select>
    <input type="text" name="q" placeholder="Cari nomor / judul..." value="<?= e($q) ?>" data-testid="filter-search-input">
    <button class="btn btn-primary" type="submit" data-testid="filter-apply-btn">Filter</button>
    <a class="btn" href="/admin/arsip.php" data-testid="filter-reset-btn">Reset</a>
  </form>
</div>

<form method="post" action="/admin/download.php" data-testid="bulk-download-form">
  <div class="card">
    <div class="toolbar">
      <label style="display:flex;align-items:center;gap:.5rem;margin:0"><input type="checkbox" data-testid="select-all-checkbox"> Pilih Semua</label>
      <div class="filters">
        <select name="bulk_format" data-testid="bulk-format-select">
          <option value="pdf">Unduh sebagai PDF</option>
          <option value="word">Unduh sebagai Word</option>
          <option value="both">Unduh PDF + Word</option>
        </select>
        <button class="btn btn-primary" type="submit" data-testid="bulk-download-btn">Unduh Terpilih (ZIP)</button>
      </div>
    </div>
    <div class="table-wrap">
    <table>
      <thead><tr><th class="checkbox-cell"></th><th>Nomor</th><th>Jenis</th><th>Judul/Keterangan</th><th>Instansi</th><th>Media</th><th>Bulan/Tahun</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): $composite = $r['tipe'] . '_' . $r['id']; ?>
        <tr data-testid="arsip-row-<?= e($composite) ?>">
          <td class="checkbox-cell"><input type="checkbox" class="row-checkbox" name="ids[]" value="<?= e($composite) ?>" data-testid="row-checkbox-<?= e($composite) ?>"></td>
          <td><?= e($r['nomor']) ?></td>
          <td><span class="pill pill-<?= e($r['tipe']) ?>"><?= $r['tipe'] === 'surat' ? 'Penawaran' : 'Kwitansi' ?></span></td>
          <td style="max-width:280px"><?= e(mb_strimwidth((string)$r['judul'], 0, 70, '...')) ?></td>
          <td><?= e($r['instansi_nama']) ?></td>
          <td><?= e($r['media_kode']) ?></td>
          <td><?= e($r['bulan_label']) ?> <?= (int)$r['tahun'] ?></td>
          <td>
            <a class="btn btn-sm" href="<?= $r['tipe'] === 'surat' ? '/admin/surat_form.php?id=' . (int)$r['id'] : '/admin/kwitansi_form.php?id=' . (int)$r['id'] ?>" data-testid="arsip-edit-<?= e($composite) ?>">Ubah</a>
            <a class="btn btn-sm" href="/admin/download.php?type=<?= e($r['tipe']) ?>&id=<?= (int)$r['id'] ?>&format=pdf&action=view" target="_blank" data-testid="arsip-view-<?= e($composite) ?>">PDF</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="8" style="color:var(--text-muted)">Tidak ada dokumen sesuai filter.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
