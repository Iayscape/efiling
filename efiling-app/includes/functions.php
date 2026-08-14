<?php

function e($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function rupiah($n): string {
    return 'Rp' . number_format((float)$n, 0, ',', '.') . ',-';
}

function roman_month(int $m): string {
    $r = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $r[$m] ?? '';
}

function nama_bulan(int $m): string {
    $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return $b[$m] ?? '';
}

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function render_template(string $tpl, array $tokens): string {
    $search = [];
    $replace = [];
    foreach ($tokens as $k => $v) {
        $search[] = '{' . $k . '}';
        $replace[] = $v;
    }
    return str_replace($search, $replace, $tpl);
}

function build_tokens(string $instansiNama, string $mediaNama, int $bulan, string $bulanLabel, int $tahun): array {
    return [
        'instansi' => $instansiNama,
        'INSTANSI' => mb_strtoupper($instansiNama),
        'media' => $mediaNama,
        'MEDIA' => mb_strtoupper($mediaNama),
        'bulan' => $bulanLabel !== '' ? $bulanLabel : nama_bulan($bulan),
        'BULAN' => mb_strtoupper($bulanLabel !== '' ? $bulanLabel : nama_bulan($bulan)),
        'tahun' => (string)$tahun,
        'TAHUN' => (string)$tahun,
    ];
}

function slugify(string $s): string {
    $s = preg_replace('/[^a-zA-Z0-9]+/', '-', $s);
    return trim(strtolower($s), '-');
}

function safe_filename(string $s): string {
    $s = preg_replace('/[\/\\\\:*?"<>|]+/', '-', $s);
    return trim($s);
}
