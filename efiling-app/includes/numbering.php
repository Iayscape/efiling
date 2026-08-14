<?php

function next_sequence(string $doc_type, int $media_id): int {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE doc_counters SET last_number = last_number + 1 WHERE media_id = ? AND doc_type = ?');
    $stmt->execute([$media_id, $doc_type]);
    if ($stmt->rowCount() === 0) {
        $pdo->prepare('INSERT INTO doc_counters (media_id, doc_type, last_number) VALUES (?, ?, 1)')->execute([$media_id, $doc_type]);
        return 1;
    }
    $stmt2 = $pdo->prepare('SELECT last_number FROM doc_counters WHERE media_id = ? AND doc_type = ?');
    $stmt2->execute([$media_id, $doc_type]);
    return (int)$stmt2->fetchColumn();
}

function generate_nomor_surat(array $media, string $jenis_kode, int $bulan, int $tahun): string {
    $seq = next_sequence('penawaran', (int)$media['id']);
    return sprintf('%03d/%s/Red/%s/%s/%d', $seq, mb_strtoupper($jenis_kode), mb_strtoupper($media['kode']), roman_month($bulan), $tahun);
}

function generate_nomor_kwitansi(array $media, int $bulan, int $tahun): string {
    $seq = next_sequence('kwitansi', (int)$media['id']);
    return sprintf('%03d/KW/%s/Red/%s/%d', $seq, mb_strtoupper($media['kode']), roman_month($bulan), $tahun);
}
