<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function render_pdf(string $html, string $filename, string $mode = 'view') {
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    if ($mode === 'string') {
        return $dompdf->output();
    }
    $dompdf->stream($filename, ['Attachment' => $mode === 'download']);
    return null;
}

function letter_css(): string {
    return '
    body{font-family:"DejaVu Sans",sans-serif;font-size:12px;color:#111;line-height:1.55}
    .accent-bar{height:6px}
    .letterhead{padding:18px 0 10px;border-bottom:2px solid #111;margin-bottom:18px}
    .letterhead .co-name{font-size:17px;font-weight:bold;letter-spacing:.5px}
    .letterhead .co-sub{font-size:10px;color:#444}
    .doc-meta td{padding:1px 6px;vertical-align:top;font-size:12px}
    table.items{width:100%;border-collapse:collapse;margin:14px 0}
    table.items th,table.items td{border:1px solid #333;padding:6px 8px;font-size:11.5px}
    table.items th{background:#eee;text-align:left}
    .text-right{text-align:right}
    .signature-block{margin-top:26px;width:260px;float:right;text-align:center}
    .signature-block .place-date{margin-bottom:4px}
    .sig-img{max-height:70px;max-width:180px;margin:4px auto;display:block}
    .footer-legal{margin-top:70px;border-top:1px solid #999;padding-top:8px;font-size:9.5px;color:#333;clear:both}
    .clearfix{clear:both}
    ';
}

function media_letterhead_html(array $media): string {
    return '<div class="accent-bar" style="background:' . e($media['accent_color']) . '"></div>
    <div class="letterhead">
      <div class="co-name">' . e($media['perusahaan']) . '</div>
      <div class="co-sub">' . e($media['nama']) . '</div>
    </div>';
}

function media_footer_html(array $media): string {
    $tw = $media['twitter'] ? ' &middot; Twitter: ' . e($media['twitter']) : '';
    $fb = $media['facebook'] ? ' &middot; Facebook: ' . e($media['facebook']) : '';
    return '<div class="footer-legal">
      <strong>' . e($media['perusahaan']) . '</strong><br>
      Alamat Redaksi: ' . e($media['alamat_redaksi']) . '<br>
      Telp: ' . e($media['telp']) . $fb . $tw . ' &middot; Email: ' . e($media['email']) . '<br>
      Akta Notaris: ' . e($media['akta_notaris']) . ' &middot; ' . e($media['ahu_number']) . ' &middot; NPWP: ' . e($media['npwp']) . '
    </div>';
}

function signature_block_html(array $media, string $tanggal, bool $useSignature): string {
    $html = '<div class="signature-block"><div class="place-date">Banjarmasin, ' . e(date('d/m/Y', strtotime($tanggal))) . '</div>Hormat Kami,<br><strong>' . e($media['perusahaan']) . '</strong><br>';
    if ($useSignature && $media['signature_path']) {
        $path = realpath(__DIR__ . '/../' . $media['signature_path']);
        if ($path) $html .= '<img class="sig-img" src="file://' . $path . '">';
    } elseif ($useSignature && $media['stempel_path']) {
        $path = realpath(__DIR__ . '/../' . $media['stempel_path']);
        if ($path) $html .= '<img class="sig-img" src="file://' . $path . '">';
    } else {
        $html .= '<div style="height:60px"></div>';
    }
    if ($useSignature && $media['stempel_path'] && $media['signature_path']) {
        $path2 = realpath(__DIR__ . '/../' . $media['stempel_path']);
        if ($path2) $html .= '<img class="sig-img" style="margin-top:-40px" src="file://' . $path2 . '">';
    }
    $html .= '<u>' . e($media['signatory_name']) . '</u><br>' . e($media['signatory_title']) . '</div><div class="clearfix"></div>';
    return $html;
}

function build_surat_html(array $surat, array $items, array $media, array $instansi): string {
    $rows = '';
    foreach ($items as $it) {
        $rows .= '<tr><td>' . e($it['nama_rubrik']) . '</td><td>' . e($it['keterangan']) . '</td><td class="text-right">' . rupiah($it['harga_bulan']) . '</td>' .
            ($it['harga_tahun'] !== null ? '<td class="text-right">' . rupiah($it['harga_tahun']) . '</td>' : '<td></td>') . '</tr>';
    }
    $body = nl2br(e($surat['body']));
    return '<html><head><meta charset="UTF-8"><style>' . letter_css() . '</style></head><body>' .
        media_letterhead_html($media) .
        '<table class="doc-meta"><tr><td width="70">Nomor</td><td width="10">:</td><td>' . e($surat['nomor_surat']) . '</td></tr>' .
        '<tr><td>Lamp</td><td>:</td><td>-</td></tr>' .
        '<tr><td>Hal</td><td>:</td><td>' . e($surat['hal']) . '</td></tr></table>' .
        '<br><table class="doc-meta"><tr><td width="70">Kepada Yth</td><td width="10">,</td><td>' . e($instansi['jabatan_penerima']) . '</td></tr>' .
        '<tr><td>C.q</td><td></td><td>' . e($instansi['cq']) . '</td></tr>' .
        '<tr><td>di</td><td>-</td><td>' . e($instansi['lokasi']) . '</td></tr></table>' .
        '<p>Salam Sejahtera,</p>' .
        '<p>' . $body . '</p>' .
        '<table class="items"><thead><tr><th>Nama Rubrik/Item</th><th>Keterangan</th><th>Harga/Bulan</th><th>Harga/Tahun</th></tr></thead><tbody>' . $rows . '</tbody></table>' .
        signature_block_html($media, $surat['tanggal'], false) .
        media_footer_html($media) .
        '</body></html>';
}

function build_kwitansi_html(array $kwitansi, array $items, array $media): string {
    $rows = '';
    if (count($items) > 1) {
        foreach ($items as $it) {
            $rows .= '<tr><td>' . e($it['nama_item']) . '</td><td class="text-right">' . rupiah($it['harga']) . '</td></tr>';
        }
        $rows .= '<tr><th>TOTAL</th><th class="text-right">' . rupiah($kwitansi['jumlah']) . '</th></tr>';
        $rows = '<table class="items"><tbody>' . $rows . '</tbody></table>';
    }
    return '<html><head><meta charset="UTF-8"><style>' . letter_css() . '</style></head><body>' .
        media_letterhead_html($media) .
        '<h3 style="text-align:center;letter-spacing:1px">KWITANSI</h3>' .
        '<table class="doc-meta">' .
        '<tr><td width="150">Nomor</td><td width="10">:</td><td>' . e($kwitansi['nomor_kwitansi']) . '</td></tr>' .
        '<tr><td>Telah Diterima Dari</td><td>:</td><td>' . e($kwitansi['diterima_dari'] ?? 'BENDAHARA PENGELUARAN') . '</td></tr>' .
        '<tr><td>Jumlah</td><td>:</td><td>// ' . e($kwitansi['terbilang']) . ' //</td></tr>' .
        '<tr><td>Untuk Pembayaran</td><td>:</td><td>' . e($kwitansi['untuk_pembayaran']) . '</td></tr>' .
        '</table>' . $rows .
        '<p style="margin-top:14px"><strong>Terbilang: ' . rupiah($kwitansi['jumlah']) . '</strong></p>' .
        signature_block_html($media, $kwitansi['tanggal'], (bool)$kwitansi['gunakan_ttd_stempel']) .
        media_footer_html($media) .
        '</body></html>';
}
