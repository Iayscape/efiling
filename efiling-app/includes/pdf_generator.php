<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function render_pdf(string $html, string $filename, string $mode = 'view') {
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->setChroot(realpath(__DIR__ . '/..'));
    $dompdf = new Dompdf($options);
    // Ukuran F4 (215.9mm x 330.2mm) dalam points, portrait
    $dompdf->setPaper([0, 0, 612.28, 935.43]);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    if ($mode === 'string') {
        return $dompdf->output();
    }
    $dompdf->stream($filename, ['Attachment' => $mode === 'download']);
    return null;
}

function shrink_tier(int $rowCount): array {
    if ($rowCount >= 10) {
        return ['body' => 9.5, 'lh' => 1.3, 'cell' => 9, 'gap' => 8, 'sig' => 14, 'foot' => 34];
    }
    if ($rowCount >= 6) {
        return ['body' => 10.5, 'lh' => 1.4, 'cell' => 10, 'gap' => 12, 'sig' => 20, 'foot' => 50];
    }
    return ['body' => 12, 'lh' => 1.55, 'cell' => 11.5, 'gap' => 18, 'sig' => 26, 'foot' => 70];
}

function letter_css(int $rowCount = 0): string {
    $t = shrink_tier($rowCount);
    return '
    body{font-family:"DejaVu Sans",sans-serif;font-size:' . $t['body'] . 'px;color:#111;line-height:' . $t['lh'] . '}
    .accent-bar{height:5px}
    .letterhead{padding:' . round($t['gap'] * 0.7) . 'px 0 ' . round($t['gap'] * 0.5) . 'px;border-bottom:2px solid #111;margin-bottom:' . $t['gap'] . 'px;display:table;width:100%}
    .letterhead-logo{display:table-cell;width:150px;vertical-align:middle;padding-right:14px}
    .letterhead-logo img{max-height:42px;max-width:150px;display:block}
    .letterhead-text{display:table-cell;vertical-align:middle}
    .letterhead .co-name{font-size:' . ($t['body'] + 4.5) . 'px;font-weight:bold;letter-spacing:.5px}
    .letterhead .co-sub{font-size:' . ($t['body'] - 1.5) . 'px;color:#444}
    .doc-meta td{padding:1px 6px;vertical-align:top;font-size:' . $t['body'] . 'px}
    table.items{width:100%;border-collapse:collapse;margin:' . round($t['gap'] * 0.8) . 'px 0}
    table.items th,table.items td{border:1px solid #333;padding:4px 7px;font-size:' . $t['cell'] . 'px}
    table.items th{background:#eee;text-align:left}
    .text-right{text-align:right}
    .signature-block{margin-top:' . $t['sig'] . 'px;width:260px;float:right;text-align:center}
    .signature-block .place-date{margin-bottom:4px}
    .sig-img{max-height:60px;max-width:170px;margin:4px auto;display:block}
    .footer-legal{margin-top:' . $t['foot'] . 'px;border-top:1px solid #999;padding-top:6px;font-size:9px;color:#333;clear:both}
    .clearfix{clear:both}
    ';
}

function media_letterhead_html(array $media): string {
    $logo = '';
    if (!empty($media['logo_path'])) {
        $path = realpath(__DIR__ . '/../' . $media['logo_path']);
        if ($path) $logo = '<div class="letterhead-logo"><img src="file://' . $path . '"></div>';
    }
    return '<div class="accent-bar" style="background:' . e($media['accent_color']) . '"></div>
    <div class="letterhead">' . $logo . '
      <div class="letterhead-text">
        <div class="co-name">' . e($media['perusahaan']) . '</div>
        <div class="co-sub">' . e($media['nama']) . '</div>
      </div>
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
    $hasYearly = false;
    foreach ($items as $it) {
        if ($it['harga_tahun'] !== null) { $hasYearly = true; break; }
    }
    $rows = '';
    foreach ($items as $it) {
        $rows .= '<tr><td>' . e($it['nama_rubrik']) . '</td><td>' . e($it['keterangan']) . '</td><td class="text-right">' . rupiah($it['harga_bulan']) . '</td>' .
            ($hasYearly ? '<td class="text-right">' . ($it['harga_tahun'] !== null ? rupiah($it['harga_tahun']) : '') . '</td>' : '') . '</tr>';
    }
    $thYearly = $hasYearly ? '<th>Harga/Tahun</th>' : '';
    $body = nl2br(e($surat['body']));
    return '<html><head><meta charset="UTF-8"><style>' . letter_css(count($items)) . '</style></head><body>' .
        media_letterhead_html($media) .
        '<table class="doc-meta"><tr><td width="70">Nomor</td><td width="10">:</td><td>' . e($surat['nomor_surat']) . '</td></tr>' .
        '<tr><td>Lamp</td><td>:</td><td>-</td></tr>' .
        '<tr><td>Hal</td><td>:</td><td>' . e($surat['hal']) . '</td></tr></table>' .
        '<br><table class="doc-meta"><tr><td width="70">Kepada Yth</td><td width="10">,</td><td>' . e($instansi['jabatan_penerima']) . '</td></tr>' .
        '<tr><td>C.q</td><td></td><td>' . e($instansi['cq']) . '</td></tr>' .
        '<tr><td>di</td><td>-</td><td>' . e($instansi['lokasi']) . '</td></tr></table>' .
        '<p>Salam Sejahtera,</p>' .
        '<p>' . $body . '</p>' .
        '<table class="items"><thead><tr><th>Nama Rubrik/Item</th><th>Keterangan</th><th>Harga/Bulan</th>' . $thYearly . '</tr></thead><tbody>' . $rows . '</tbody></table>' .
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
