<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

function docx_output(PhpWord $phpWord, string $filename): void {
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

function docx_save_temp(PhpWord $phpWord): string {
    $tmp = tempnam(sys_get_temp_dir(), 'docx');
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tmp);
    return $tmp;
}

function docx_shrink_tier(int $rowCount): array {
    if ($rowCount >= 10) return ['title' => 12, 'sub' => 8, 'body' => 9, 'cell' => 8, 'break1' => 0, 'break2' => 1];
    if ($rowCount >= 6) return ['title' => 13, 'sub' => 8.5, 'body' => 9.5, 'cell' => 9, 'break1' => 1, 'break2' => 1];
    return ['title' => 14, 'sub' => 9, 'body' => 10, 'cell' => 10, 'break1' => 1, 'break2' => 2];
}

function docx_image_box(string $path, int $maxW = 130, int $maxH = 42): array {
    $size = @getimagesize($path);
    if (!$size) return ['width' => $maxW, 'height' => $maxH];
    [$w, $h] = $size;
    $ratio = min($maxW / $w, $maxH / $h);
    return ['width' => round($w * $ratio), 'height' => round($h * $ratio)];
}

function docx_letterhead(\PhpOffice\PhpWord\Element\Section $section, array $media, array $t): void {
    if (!empty($media['logo_path'])) {
        $path = realpath(__DIR__ . '/../' . $media['logo_path']);
        if ($path) {
            $box = docx_image_box($path, 260, 75);
            $section->addImage($path, ['width' => $box['width'], 'height' => $box['height'], 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            return;
        }
    }
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => $t['title']], ['alignment' => 'center']);
}

function docx_watermark(\PhpOffice\PhpWord\Element\Section $section, array $media): void {
    if (empty($media['logo_path'])) return;
    $faded = faded_logo_path($media['logo_path']);
    if (!$faded) return;
    $box = docx_image_box($faded, 420, 420);
    $section->addImage($faded, [
        'width' => $box['width'], 'height' => $box['height'],
        'pos' => 'absolute', 'wrap' => 'behind',
        'hPos' => 'center', 'hPosRelTo' => 'page',
        'vPos' => 'center', 'vPosRelTo' => 'page',
    ]);
}

function build_surat_docx(array $surat, array $items, array $media, array $instansi): PhpWord {
    $t = docx_shrink_tier(count($items));
    $hasYearly = false;
    foreach ($items as $it) { if ($it['harga_tahun'] !== null) { $hasYearly = true; break; } }
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['paperSize' => 'Folio', 'marginTop' => 700, 'marginBottom' => 700]);

    docx_letterhead($section, $media, $t);
    $section->addTextBreak($t['break1']);

    $table = $section->addTable();
    $table->addRow();
    $table->addCell(1200)->addText('Nomor', ['size' => $t['body']]);
    $table->addCell(200)->addText(':', ['size' => $t['body']]);
    $table->addCell(6000)->addText($surat['nomor_surat'], ['size' => $t['body']]);
    $table->addRow();
    $table->addCell(1200)->addText('Lamp', ['size' => $t['body']]);
    $table->addCell(200)->addText(':', ['size' => $t['body']]);
    $table->addCell(6000)->addText('-', ['size' => $t['body']]);
    $table->addRow();
    $table->addCell(1200)->addText('Hal', ['size' => $t['body']]);
    $table->addCell(200)->addText(':', ['size' => $t['body']]);
    $table->addCell(6000)->addText($surat['hal'], ['size' => $t['body']]);
    $section->addTextBreak($t['break1']);

    $section->addText('Kepada Yth, ' . $instansi['jabatan_penerima'], ['size' => $t['body']]);
    if ($instansi['cq']) $section->addText('C.q ' . $instansi['cq'], ['size' => $t['body']]);
    $section->addText('di - ' . $instansi['lokasi'], ['size' => $t['body']]);
    $section->addTextBreak($t['break1']);
    $section->addText('Salam Sejahtera,', ['size' => $t['body']]);
    $section->addTextBreak($t['break1']);

    foreach (explode("\n", (string)$surat['body']) as $para) {
        if (trim($para) !== '') $section->addText($para, ['size' => $t['body']]);
    }
    $section->addTextBreak($t['break1']);

    $headers = ['Nama Rubrik/Item', 'Keterangan', 'Harga/Bulan'];
    if ($hasYearly) $headers[] = 'Harga/Tahun';
    $colWidth = (int)(9500 / count($headers));
    $tbl = $section->addTable(['borderSize' => 6, 'borderColor' => '333333', 'cellMargin' => 80]);
    $tbl->addRow();
    foreach ($headers as $h) {
        $tbl->addCell($colWidth)->addText($h, ['bold' => true, 'size' => $t['cell']]);
    }
    foreach ($items as $it) {
        $tbl->addRow();
        $tbl->addCell($colWidth)->addText($it['nama_rubrik'], ['size' => $t['cell']]);
        $tbl->addCell($colWidth)->addText((string)$it['keterangan'], ['size' => $t['cell']]);
        $tbl->addCell($colWidth)->addText(rupiah($it['harga_bulan']), ['size' => $t['cell']]);
        if ($hasYearly) $tbl->addCell($colWidth)->addText($it['harga_tahun'] !== null ? rupiah($it['harga_tahun']) : '-', ['size' => $t['cell']]);
    }

    $section->addTextBreak($t['break2']);
    $section->addText('Banjarmasin, ' . date('d/m/Y', strtotime($surat['tanggal'])), ['size' => $t['body']], ['alignment' => 'right']);
    $section->addText('Hormat Kami,', ['size' => $t['body']], ['alignment' => 'right']);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => $t['body']], ['alignment' => 'right']);
    $section->addTextBreak($t['break2']);
    $section->addText($media['signatory_name'], ['underline' => 'single', 'size' => $t['body']], ['alignment' => 'right']);
    $section->addText($media['signatory_title'], ['size' => $t['body']], ['alignment' => 'right']);

    $section->addTextBreak($t['break2']);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 9]);
    $section->addText('Alamat Redaksi: ' . $media['alamat_redaksi'], ['size' => 8]);
    $section->addText('Telp: ' . $media['telp'] . ' | Email: ' . $media['email'], ['size' => 8]);
    $section->addText('Akta Notaris: ' . $media['akta_notaris'] . ' | ' . $media['ahu_number'] . ' | NPWP: ' . $media['npwp'], ['size' => 8]);

    return $phpWord;
}

function build_kwitansi_docx(array $kwitansi, array $items, array $media): PhpWord {
    $t = docx_shrink_tier(count($items));
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['paperSize' => 'Folio', 'marginTop' => 700, 'marginBottom' => 700]);

    docx_watermark($section, $media);
    docx_letterhead($section, $media, $t);
    $section->addTextBreak($t['break1']);
    $section->addText('KWITANSI', ['bold' => true, 'size' => $t['title'] - 1], ['alignment' => 'center']);
    $section->addTextBreak($t['break1']);

    $table = $section->addTable();
    $rowsData = [
        ['Nomor', $kwitansi['nomor_kwitansi']],
        ['Telah Diterima Dari', $kwitansi['diterima_dari'] ?? 'BENDAHARA PENGELUARAN'],
        ['Jumlah', '// ' . $kwitansi['terbilang'] . ' //'],
        ['Untuk Pembayaran', $kwitansi['untuk_pembayaran']],
    ];
    foreach ($rowsData as [$label, $val]) {
        $table->addRow();
        $table->addCell(2500)->addText($label, ['size' => $t['body']]);
        $table->addCell(300)->addText(':', ['size' => $t['body']]);
        $table->addCell(5500)->addText((string)$val, ['size' => $t['body']]);
    }

    if (count($items) > 1) {
        $section->addTextBreak($t['break1']);
        $t2 = $section->addTable(['borderSize' => 6, 'borderColor' => '333333']);
        foreach ($items as $it) {
            $t2->addRow();
            $t2->addCell(4000)->addText($it['nama_item'], ['size' => $t['cell']]);
            $t2->addCell(2500)->addText(rupiah($it['harga']), ['size' => $t['cell']]);
        }
        $t2->addRow();
        $t2->addCell(4000)->addText('TOTAL', ['bold' => true, 'size' => $t['cell']]);
        $t2->addCell(2500)->addText(rupiah($kwitansi['jumlah']), ['bold' => true, 'size' => $t['cell']]);
    }

    $section->addTextBreak($t['break1']);
    $section->addText('Terbilang: ' . rupiah($kwitansi['jumlah']), ['bold' => true, 'size' => $t['body']]);

    $section->addTextBreak($t['break2']);
    $section->addText('Banjarmasin, ' . date('d/m/Y', strtotime($kwitansi['tanggal'])), ['size' => $t['body']], ['alignment' => 'right']);
    $section->addText('Hormat Kami,', ['size' => $t['body']], ['alignment' => 'right']);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => $t['body']], ['alignment' => 'right']);
    $section->addTextBreak($t['break2']);
    $section->addText($media['signatory_name'], ['underline' => 'single', 'size' => $t['body']], ['alignment' => 'right']);
    $section->addText($media['signatory_title'], ['size' => $t['body']], ['alignment' => 'right']);

    $section->addTextBreak($t['break2']);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 9]);
    $section->addText('Alamat Redaksi: ' . $media['alamat_redaksi'], ['size' => 8]);
    $section->addText('Telp: ' . $media['telp'] . ' | Email: ' . $media['email'], ['size' => 8]);

    return $phpWord;
}
