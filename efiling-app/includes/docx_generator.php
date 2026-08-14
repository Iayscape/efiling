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

function build_surat_docx(array $surat, array $items, array $media, array $instansi): PhpWord {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['marginTop' => 900, 'marginBottom' => 900]);

    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 14]);
    $section->addText($media['nama'], ['size' => 9, 'color' => '555555']);
    $section->addTextBreak(1);

    $table = $section->addTable();
    $table->addRow();
    $table->addCell(1200)->addText('Nomor');
    $table->addCell(200)->addText(':');
    $table->addCell(6000)->addText($surat['nomor_surat']);
    $table->addRow();
    $table->addCell(1200)->addText('Lamp');
    $table->addCell(200)->addText(':');
    $table->addCell(6000)->addText('-');
    $table->addRow();
    $table->addCell(1200)->addText('Hal');
    $table->addCell(200)->addText(':');
    $table->addCell(6000)->addText($surat['hal']);
    $section->addTextBreak(1);

    $section->addText('Kepada Yth, ' . $instansi['jabatan_penerima']);
    if ($instansi['cq']) $section->addText('C.q ' . $instansi['cq']);
    $section->addText('di - ' . $instansi['lokasi']);
    $section->addTextBreak(1);
    $section->addText('Salam Sejahtera,');
    $section->addTextBreak(1);

    foreach (explode("\n", (string)$surat['body']) as $para) {
        if (trim($para) !== '') $section->addText($para);
    }
    $section->addTextBreak(1);

    $t = $section->addTable(['borderSize' => 6, 'borderColor' => '333333', 'cellMargin' => 80]);
    $t->addRow();
    foreach (['Nama Rubrik/Item', 'Keterangan', 'Harga/Bulan', 'Harga/Tahun'] as $h) {
        $t->addCell(2500)->addText($h, ['bold' => true]);
    }
    foreach ($items as $it) {
        $t->addRow();
        $t->addCell(2500)->addText($it['nama_rubrik']);
        $t->addCell(2500)->addText((string)$it['keterangan']);
        $t->addCell(2500)->addText(rupiah($it['harga_bulan']));
        $t->addCell(2500)->addText($it['harga_tahun'] !== null ? rupiah($it['harga_tahun']) : '-');
    }

    $section->addTextBreak(2);
    $section->addText('Banjarmasin, ' . date('d/m/Y', strtotime($surat['tanggal'])), [], ['alignment' => 'right']);
    $section->addText('Hormat Kami,', [], ['alignment' => 'right']);
    $section->addText($media['perusahaan'], ['bold' => true], ['alignment' => 'right']);
    $section->addTextBreak(2);
    $section->addText($media['signatory_name'], ['underline' => 'single'], ['alignment' => 'right']);
    $section->addText($media['signatory_title'], [], ['alignment' => 'right']);

    $section->addTextBreak(3);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 9]);
    $section->addText('Alamat Redaksi: ' . $media['alamat_redaksi'], ['size' => 8]);
    $section->addText('Telp: ' . $media['telp'] . ' | Email: ' . $media['email'], ['size' => 8]);
    $section->addText('Akta Notaris: ' . $media['akta_notaris'] . ' | ' . $media['ahu_number'] . ' | NPWP: ' . $media['npwp'], ['size' => 8]);

    return $phpWord;
}

function build_kwitansi_docx(array $kwitansi, array $items, array $media): PhpWord {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['marginTop' => 900, 'marginBottom' => 900]);

    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 14]);
    $section->addText($media['nama'], ['size' => 9, 'color' => '555555']);
    $section->addTextBreak(1);
    $section->addText('KWITANSI', ['bold' => true, 'size' => 13], ['alignment' => 'center']);
    $section->addTextBreak(1);

    $table = $section->addTable();
    $rowsData = [
        ['Nomor', $kwitansi['nomor_kwitansi']],
        ['Telah Diterima Dari', $kwitansi['diterima_dari'] ?? 'BENDAHARA PENGELUARAN'],
        ['Jumlah', '// ' . $kwitansi['terbilang'] . ' //'],
        ['Untuk Pembayaran', $kwitansi['untuk_pembayaran']],
    ];
    foreach ($rowsData as [$label, $val]) {
        $table->addRow();
        $table->addCell(2500)->addText($label);
        $table->addCell(300)->addText(':');
        $table->addCell(5500)->addText((string)$val);
    }

    if (count($items) > 1) {
        $section->addTextBreak(1);
        $t2 = $section->addTable(['borderSize' => 6, 'borderColor' => '333333']);
        foreach ($items as $it) {
            $t2->addRow();
            $t2->addCell(4000)->addText($it['nama_item']);
            $t2->addCell(2500)->addText(rupiah($it['harga']));
        }
        $t2->addRow();
        $t2->addCell(4000)->addText('TOTAL', ['bold' => true]);
        $t2->addCell(2500)->addText(rupiah($kwitansi['jumlah']), ['bold' => true]);
    }

    $section->addTextBreak(1);
    $section->addText('Terbilang: ' . rupiah($kwitansi['jumlah']), ['bold' => true]);

    $section->addTextBreak(2);
    $section->addText('Banjarmasin, ' . date('d/m/Y', strtotime($kwitansi['tanggal'])), [], ['alignment' => 'right']);
    $section->addText('Hormat Kami,', [], ['alignment' => 'right']);
    $section->addText($media['perusahaan'], ['bold' => true], ['alignment' => 'right']);
    $section->addTextBreak(2);
    $section->addText($media['signatory_name'], ['underline' => 'single'], ['alignment' => 'right']);
    $section->addText($media['signatory_title'], [], ['alignment' => 'right']);

    $section->addTextBreak(3);
    $section->addText($media['perusahaan'], ['bold' => true, 'size' => 9]);
    $section->addText('Alamat Redaksi: ' . $media['alamat_redaksi'], ['size' => 8]);
    $section->addText('Telp: ' . $media['telp'] . ' | Email: ' . $media['email'], ['size' => 8]);

    return $phpWord;
}
