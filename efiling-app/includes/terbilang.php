<?php

function angka_ke_kata(int $x): string {
    $x = abs($x);
    $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    if ($x < 12) {
        $temp = ' ' . $angka[$x];
    } elseif ($x < 20) {
        $temp = angka_ke_kata($x - 10) . ' belas';
    } elseif ($x < 100) {
        $temp = angka_ke_kata(intdiv($x, 10)) . ' puluh' . angka_ke_kata($x % 10);
    } elseif ($x < 200) {
        $temp = ' seratus' . angka_ke_kata($x - 100);
    } elseif ($x < 1000) {
        $temp = angka_ke_kata(intdiv($x, 100)) . ' ratus' . angka_ke_kata($x % 100);
    } elseif ($x < 2000) {
        $temp = ' seribu' . angka_ke_kata($x - 1000);
    } elseif ($x < 1000000) {
        $temp = angka_ke_kata(intdiv($x, 1000)) . ' ribu' . angka_ke_kata($x % 1000);
    } elseif ($x < 1000000000) {
        $temp = angka_ke_kata(intdiv($x, 1000000)) . ' juta' . angka_ke_kata($x % 1000000);
    } elseif ($x < 1000000000000) {
        $temp = angka_ke_kata(intdiv($x, 1000000000)) . ' milyar' . angka_ke_kata($x % 1000000000);
    } else {
        $temp = angka_ke_kata(intdiv($x, 1000000000000)) . ' triliun' . angka_ke_kata($x % 1000000000000);
    }
    return $temp;
}

function terbilang_rupiah($angka): string {
    $angka = (float)$angka;
    if ($angka == 0) return 'NOL RUPIAH';
    $hasil = trim(preg_replace('/\s+/', ' ', angka_ke_kata((int)round($angka))));
    return mb_strtoupper($hasil) . ' RUPIAH';
}
