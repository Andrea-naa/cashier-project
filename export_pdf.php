<?php
require_once 'config/conn_db.php';
require 'vendor/autoload.php'; // DOMPDF

use Dompdf\Dompdf;
use Dompdf\Options;

// HELPER FORMAT RUPIAH
function rupiah_fmt($n){
    return 'Rp. '.number_format($n, 0, ',', '.');
}

// AMBIL KONFIGURASI
$qk = mysqli_query($conn, "SELECT * FROM konfigurasi LIMIT 1");
$config = mysqli_fetch_assoc($qk) ?: [
    'nama_perusahaan' => 'PT. MITRA SAUDARA LESTARI - MSL',
    'alamat'          => '',
    'kota'            => '',
    'ttd_jabatan_1'   => 'Finance Dept Head',
    'ttd_jabatan_2'   => 'Finance Sub Dept Head',
    'ttd_jabatan_3'   => 'Finance Div Head',
    'ttd_jabatan_4'   => 'Cashier'
];


// DAFTAR PECAHAN (1 – 11, sama seperti form & database)

$pecahan = [
    ['no'=>1,'uraian'=>'Seratus Ribuan Kertas','satuan'=>'Lembar','nominal'=>100000],
    ['no'=>2,'uraian'=>'Lima Puluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>50000],
    ['no'=>3,'uraian'=>'Dua Puluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>20000],
    ['no'=>4,'uraian'=>'Sepuluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>10000],
    ['no'=>5,'uraian'=>'Lima Ribuan Kertas','satuan'=>'Lembar','nominal'=>5000],
    ['no'=>6,'uraian'=>'Dua Ribuan Kertas','satuan'=>'Lembar','nominal'=>2000],
    ['no'=>7,'uraian'=>'Satu Ribuan Kertas','satuan'=>'Lembar','nominal'=>1000],
    ['no'=>8,'uraian'=>'Satu Ribuan Logam','satuan'=>'Keping','nominal'=>1000],
    ['no'=>9,'uraian'=>'Lima Ratusan Logam','satuan'=>'Keping','nominal'=>500],
    ['no'=>10,'uraian'=>'Dua Ratusan Logam','satuan'=>'Keping','nominal'=>200],
    ['no'=>11,'uraian'=>'Satu Ratusan Logam','satuan'=>'Keping','nominal'=>100],
];

// MODE EXPORT – SINGLE / SEMUA / KAS MASUK / KAS KELUAR
$type = isset($_GET['type']) ? $_GET['type'] : 'stok_opname'; // default stok_opname
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rows = [];

if ($type === 'kas_masuk') {
    // EXPORT KAS MASUK
    $res = mysqli_query($conn, "SELECT id, user_id, username, nominal, keterangan, tanggal_transaksi FROM transaksi WHERE jenis_transaksi = 'kas_terima' ORDER BY tanggal_transaksi ASC");
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
} elseif ($type === 'kas_keluar') {
    // EXPORT KAS KELUAR
    $res = mysqli_query($conn, "SELECT id, user_id, username, nominal, keterangan, tanggal_transaksi FROM transaksi WHERE jenis_transaksi = 'kas_keluar' ORDER BY tanggal_transaksi ASC");
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
} elseif ($id > 0) {
    // EXPORT SATU DATA STOK OPNAME
    $stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($f = mysqli_fetch_assoc($res)) $rows[] = $f;
    mysqli_stmt_close($stmt);
} else {
    // EXPORT SEMUA STOK OPNAME
    $res = mysqli_query($conn, "SELECT * FROM stok_opname ORDER BY tanggal_opname DESC");
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
}

if (empty($rows)) {
    die("Tidak ada data untuk diexport.");
}

// BUILD HTML
$html = '<!doctype html><html><head><meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#000; }
.title { font-weight:700; font-size:16px; text-align:center; margin-bottom:5px; text-decoration:underline; }
.table { width:100%; border-collapse:collapse; margin-top:6px; }
.table th, .table td { border:1px solid #000; padding:6px; font-size:11px; }
.right { text-align:right; }
.center { text-align:center; }
.keterangan { height:60px; border-bottom:1px dotted #000; margin-top:12px; }
.ttd-wrap { width:100%; margin-top:30px; display:flex; justify-content:space-between; }
.ttd-box { width:24%; text-align:center; font-size:11px; }
</style>
</head><body>';

if ($type === 'kas_masuk' || $type === 'kas_keluar') {
    // EXPORT KAS MASUK / KELUAR
    $title = ($type === 'kas_masuk') ? 'LAPORAN KAS MASUK' : 'LAPORAN KAS KELUAR';

    // Nomor surat terakhir untuk kas keluar
    $last_nomor = '';
    if ($type === 'kas_keluar' && !empty($rows)) {
        $last = end($rows);
        $dt = strtotime($last['tanggal_transaksi']);
        $last_nomor = sprintf('%03d/KK/%02d/%04d', $last['id'], date('m', $dt), date('Y', $dt));
        reset($rows);
    }

    $html .= '
    <div style="font-weight:700; font-size:14px;">'.htmlspecialchars($config['nama_perusahaan']).'</div>
    <div class="title">'.$title.'</div>';
    if ($last_nomor) {
        $html .= '<div style="text-align:right; font-size:12px;">Nomor Surat Terakhir: '.htmlspecialchars($last_nomor).'</div>';
    }
    $html .= '
    <table class="table">
    <thead>
    <tr>
    <th class="center">NO</th>
    <th>KETERANGAN</th>
    <th class="right">JUMLAH</th>
    <th class="center">TANGGAL</th>
    </tr>
    </thead>
    <tbody>';

    if (!empty($rows)) {
        $i = 1;
        foreach ($rows as $row) {
            $jumlah_fmt = rupiah_fmt($row['nominal']);
            $html .= '<tr>
    <td class="center">' . $i . '</td>
    <td>' . htmlspecialchars($row['keterangan']) . '</td>
    <td class="right">' . $jumlah_fmt . '</td>
    <td class="center">' . date('d-M-Y H:i', strtotime($row['tanggal_transaksi'])) . '</td>
    </tr>';
            $i++;
        }
    } else {
        $html .= '<tr><td colspan="4" class="center">Belum ada data</td></tr>';
    }

    $html .= '</tbody></table>';

} else {
    // EXPORT STOK OPNAME
    foreach ($rows as $index => $row) {

        // AMBIL DETAIL
        $idop = intval($row['id']);
        $qd = mysqli_prepare($conn, "SELECT * FROM stok_opname_detail WHERE stok_opname_id = ? ORDER BY no_urut ASC");
        mysqli_stmt_bind_param($qd, 'i', $idop);
        mysqli_stmt_execute($qd);
        $resd = mysqli_stmt_get_result($qd);

        $detail_map = [];
        while ($d = mysqli_fetch_assoc($resd)) {
            $detail_map[$d['no_urut']] = $d;
        }
        mysqli_stmt_close($qd);

        // HEADER
        $html .= '
            <div style="font-weight:700; font-size:14px;">'.htmlspecialchars($config['nama_perusahaan']).'</div>
            <div class="title">STOK OPNAME KAS<br><span style="font-weight:400; font-size:12px">Tanggal '.date('d-M-Y', strtotime($row['tanggal_opname'])).'</span></div>
            <div style="text-align:right; font-size:12px;">User: '.htmlspecialchars($row['username']).'</div>
