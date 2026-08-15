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

function faded_logo_path(string $logoPath, int $opacityPct = 9): ?string {
    $src = realpath(__DIR__ . '/../' . $logoPath);
    if (!$src) return null;
    $cacheDir = __DIR__ . '/../storage/cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
    $cacheFile = $cacheDir . '/wm_' . md5($logoPath . $opacityPct) . '.png';
    if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($src)) {
        return $cacheFile;
    }
    $info = @getimagesize($src);
    if (!$info) return null;
    $srcImg = $info['mime'] === 'image/png' ? @imagecreatefrompng($src) : @imagecreatefromjpeg($src);
    if (!$srcImg) return null;
    $w = imagesx($srcImg);
    $h = imagesy($srcImg);
    // Step 1: flatten source (with possible alpha) onto white so transparent areas become white
    $flat = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($flat, 255, 255, 255);
    imagefill($flat, 0, 0, $white);
    imagealphablending($flat, true);
    imagecopy($flat, $srcImg, 0, 0, 0, 0, $w, $h);
    // Step 2: blend the flattened (fully opaque) image onto white at low opacity
    $canvas = imagecreatetruecolor($w, $h);
    $white2 = imagecolorallocate($canvas, 255, 255, 255);
    imagefill($canvas, 0, 0, $white2);
    imagecopymerge($canvas, $flat, 0, 0, 0, 0, $w, $h, $opacityPct);
    imagepng($canvas, $cacheFile);
    imagedestroy($srcImg);
    imagedestroy($flat);
    imagedestroy($canvas);
    return $cacheFile;
}
