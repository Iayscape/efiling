<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/pdf_generator.php';
require_once __DIR__ . '/../includes/docx_generator.php';

$u = require_login();

$type = $_GET['type'] ?? 'surat';
$format = $_GET['format'] ?? 'pdf';
$action = $_GET['action'] ?? 'download';
$ids = [];
if (isset($_GET['id'])) {
    $ids = [(int)$_GET['id']];
} elseif (!empty($_POST['ids'])) {
    foreach ($_POST['ids'] as $composite) {
        [$t, $rid] = explode('_', $composite, 2);
        $ids[] = ['type' => $t, 'id' => (int)$rid];
    }
    $format = $_POST['bulk_format'] ?? 'pdf';
}

function load_surat_bundle(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM surat_penawaran WHERE id = ?');
    $stmt->execute([$id]);
    $surat = $stmt->fetch();
    if (!$surat) return null;
    $itStmt = db()->prepare('SELECT * FROM surat_penawaran_items WHERE surat_id = ? ORDER BY urutan');
    $itStmt->execute([$id]);
    $items = $itStmt->fetchAll();
    $mStmt = db()->prepare('SELECT * FROM media WHERE id = ?');
    $mStmt->execute([$surat['media_id']]);
    $media = $mStmt->fetch();
    $iStmt = db()->prepare('SELECT * FROM instansi WHERE id = ?');
    $iStmt->execute([$surat['instansi_id']]);
    $instansi = $iStmt->fetch();
    return compact('surat', 'items', 'media', 'instansi');
}

function load_kwitansi_bundle(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM kwitansi WHERE id = ?');
    $stmt->execute([$id]);
    $kwitansi = $stmt->fetch();
    if (!$kwitansi) return null;
    $itStmt = db()->prepare('SELECT * FROM kwitansi_items WHERE kwitansi_id = ? ORDER BY urutan');
    $itStmt->execute([$id]);
    $items = $itStmt->fetchAll();
    $mStmt = db()->prepare('SELECT * FROM media WHERE id = ?');
    $mStmt->execute([$kwitansi['media_id']]);
    $media = $mStmt->fetch();
    return compact('kwitansi', 'items', 'media');
}

function doc_filename(string $type, array $bundle, string $ext): string {
    $nomor = $type === 'surat' ? $bundle['surat']['nomor_surat'] : $bundle['kwitansi']['nomor_kwitansi'];
    $label = $type === 'surat' ? 'Penawaran' : 'Kwitansi';
    return safe_filename($label . ' ' . $nomor) . '.' . $ext;
}

// Single document view/download
if (count($ids) === 1 && isset($ids[0]) && !is_array($ids[0])) {
    $id = $ids[0];
    if ($type === 'surat') {
        $b = load_surat_bundle($id);
        if (!$b) { die('Dokumen tidak ditemukan.'); }
        if ($format === 'word') {
            $phpWord = build_surat_docx($b['surat'], $b['items'], $b['media'], $b['instansi']);
            docx_output($phpWord, doc_filename('surat', $b, 'docx'));
        } else {
            $html = build_surat_html($b['surat'], $b['items'], $b['media'], $b['instansi']);
            render_pdf($html, doc_filename('surat', $b, 'pdf'), $action === 'view' ? 'view' : 'download');
        }
    } else {
        $b = load_kwitansi_bundle($id);
        if (!$b) { die('Dokumen tidak ditemukan.'); }
        if ($format === 'word') {
            $phpWord = build_kwitansi_docx($b['kwitansi'], $b['items'], $b['media']);
            docx_output($phpWord, doc_filename('kwitansi', $b, 'docx'));
        } else {
            $html = build_kwitansi_html($b['kwitansi'], $b['items'], $b['media']);
            render_pdf($html, doc_filename('kwitansi', $b, 'pdf'), $action === 'view' ? 'view' : 'download');
        }
    }
    exit;
}

// Bulk / multi-select -> ZIP with organized folder structure
if (!empty($ids) && is_array($ids[0] ?? null)) {
    $tmpZip = tempnam(sys_get_temp_dir(), 'zip');
    $zip = new ZipArchive();
    $zip->open($tmpZip, ZipArchive::OVERWRITE);

    foreach ($ids as $ref) {
        if ($ref['type'] === 'surat') {
            $b = load_surat_bundle($ref['id']);
            if (!$b) continue;
            $folder = safe_filename($b['instansi']['nama']) . '/' . safe_filename($b['media']['kode']) . '/' . $b['surat']['tahun'] . '/' . safe_filename($b['surat']['bulan_label']) . '/Penawaran/';
            if ($format === 'pdf' || $format === 'both') {
                $html = build_surat_html($b['surat'], $b['items'], $b['media'], $b['instansi']);
                $zip->addFromString($folder . doc_filename('surat', $b, 'pdf'), render_pdf($html, 'x', 'string'));
            }
            if ($format === 'word' || $format === 'both') {
                $phpWord = build_surat_docx($b['surat'], $b['items'], $b['media'], $b['instansi']);
                $tmp = docx_save_temp($phpWord);
                $zip->addFile($tmp, $folder . doc_filename('surat', $b, 'docx'));
            }
        } else {
            $b = load_kwitansi_bundle($ref['id']);
            if (!$b) continue;
            $stmtI = db()->prepare('SELECT nama FROM instansi WHERE id = ?');
            $stmtI->execute([$b['kwitansi']['instansi_id']]);
            $instansiNama = $stmtI->fetchColumn();
            $folder = safe_filename($instansiNama) . '/' . safe_filename($b['media']['kode']) . '/' . $b['kwitansi']['tahun'] . '/' . safe_filename($b['kwitansi']['bulan_label']) . '/Kwitansi/';
            if ($format === 'pdf' || $format === 'both') {
                $html = build_kwitansi_html($b['kwitansi'], $b['items'], $b['media']);
                $zip->addFromString($folder . doc_filename('kwitansi', $b, 'pdf'), render_pdf($html, 'x', 'string'));
            }
            if ($format === 'word' || $format === 'both') {
                $phpWord = build_kwitansi_docx($b['kwitansi'], $b['items'], $b['media']);
                $tmp = docx_save_temp($phpWord);
                $zip->addFile($tmp, $folder . doc_filename('kwitansi', $b, 'docx'));
            }
        }
    }
    $zip->close();
    log_activity((int)$u['id'], 'bulk_download', count($ids) . ' dokumen');

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="arsip-digital-' . date('Ymd-His') . '.zip"');
    header('Content-Length: ' . filesize($tmpZip));
    readfile($tmpZip);
    unlink($tmpZip);
    exit;
}

http_response_code(400);
die('Permintaan tidak valid.');
