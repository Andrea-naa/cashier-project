<?php

require_once 'config/conn_db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<h3>Error:</h3>";
    echo "<pre>$errstr in $errfile on line $errline</pre>";
    die();
});

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// fungsi terbilang
function terbilang($angka) {
    $angka = (int) abs($angka);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    
    if ($angka < 12) {
        $temp = $huruf[$angka];
    } else if ($angka < 20) {
        $temp = terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $temp = terbilang($angka / 10) . " puluh " . terbilang($angka % 10);
    } else if ($angka < 200) {
        $temp = "seratus " . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $temp = terbilang($angka / 100) . " ratus " . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $temp = "seribu " . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $temp = terbilang($angka / 1000) . " ribu " . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $temp = terbilang($angka / 1000000) . " juta " . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        $temp = terbilang($angka / 1000000000) . " milyar " . terbilang(fmod($angka, 1000000000));
    }
    
    return trim($temp);
}

// ngambil konfigurasi
$qk = mysqli_query($conn, "SELECT * FROM konfigurasi LIMIT 1");
$config = mysqli_fetch_assoc($qk) ?: [
    'nama_perusahaan' => 'PT. MITRA SAUDARA LESTARI - MSL',
    'kota'            => 'Pontianak',
    'ttd_jabatan_1'   => 'Cashier',
    'ttd_jabatan_2'   => 'Finance Div Head',
    'ttd_jabatan_3'   => 'Finance Sub Dept Head',
    'ttd_jabatan_4'   => 'Finance Dept Head Pjs'
];


// ngambil parameter
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (empty($type)) {
    die('ERROR: Parameter "type" harus diisi. Contoh: export_pdf.php?type=kas_masuk&id=1');
}

if ($id <= 0 && $type !== 'buku_kas') {
    die('ERROR: Parameter "id" harus diisi. Contoh: export_pdf.php?type=kas_masuk&id=1');
}

$html = '';


// bagian kas masuk / keluar
if ($type === 'kas_masuk' || $type === 'kas_keluar') {
    
    $jenis_transaksi = ($type === 'kas_masuk') ? 'kas_terima' : 'kas_keluar';
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ? AND jenis_transaksi = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'is', $id, $jenis_transaksi);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$data) {
        die('ERROR: Data transaksi tidak ditemukan (ID: ' . $id . ')');
    }
    
    $jenis = ($type === 'kas_masuk') ? 'MASUK' : 'KELUAR';
    
    // ngambil nomor dari database
    $nomor = $data['nomor_surat'];
    
    // Jika nomor_surat NULL, generate manual
    if (empty($nomor)) {
        $dt = strtotime($data['tanggal_transaksi']);
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $kode = ($type === 'kas_masuk') ? 'KT-KSK' : 'KK-KSK';
        $nomor = sprintf('%03d/%s/%s/%04d', $data['id'], $kode, $bulan_romawi[date('n', $dt)], date('Y', $dt));
    }
    
    $tanggal = date('d-M-Y', strtotime($data['tanggal_transaksi']));
    $nominal = floatval($data['nominal']);
    
    // misahkan rupiah dan sen
    $rupiah_bulat = floor($nominal);
    $sen = round(($nominal - $rupiah_bulat) * 100);
    
    // Buat teks terbilang
    $terbilang_raw = terbilang($rupiah_bulat);
    $terbilang_text = ucfirst($terbilang_raw);
    if ($sen > 0) {
        $terbilang_text .= " koma " . terbilang($sen);
    }
    $terbilang_text .= " rupiah";
    
    // html untuk pdf 
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>
        @page { margin: 20mm 15mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 11pt; 
            color: #000; 
        }
        .header-row { 
            display: table; 
            width: 100%; 
            margin-bottom: 15px; 
        }
        .header-left { 
            display: table-cell; 
            width: 20%; 
            vertical-align: top; 
            font-size: 10pt; 
            line-height: 1.4;
        }
        .header-center { 
            display: table-cell; 
            width: 55%; 
            text-align: center; 
            vertical-align: top; 
        }
        .header-right { 
            display: table-cell; 
            width: 25%; 
            text-align: left; 
            vertical-align: top; 
            font-size: 10pt; 
            line-height: 1.4;
            white-space: nowrap; 
            line-height: 1.6;
        }
        .title { 
            font-size: 16pt; 
            font-weight: bold; 
            margin: 0; 
        }
        .subtitle { 
            font-size: 11pt; 
            margin: 3px 0 0 0; 
        }
        .table-transaksi { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0 20px 0; 
        }
        .table-transaksi th, .table-transaksi td { 
            border: 1px solid #000; 
            padding: 8px 10px; 
        }
        .table-transaksi th { 
            background-color: #ffffff; 
            font-weight: bold; 
            text-align: center; 
        }
        .no-col { 
            width: 60px; 
            text-align: center; 
        }
        .amount-col { 
            width: 150px; 
            text-align: right; 
        }
        
        .total-section {
            margin: 15px 0;
            text-align: right;
        }
        .total-label {
            display: inline-block;
            text-align: left;
            font-weight: bold;
            margin-right: 10px;
        }
        .total-value {
            display: inline-block;
            width: 150px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #000;
            padding: 5px 10px;
        }
        
        .terbilang-section {
            margin: 15px 0;
            line-height: 1.5;
        }
        .terbilang-label {
            display: inline-block;
            vertical-align: top;
            margin-right: 5px;
        }
        .terbilang-value {
            display: inline-block;
            text-decoration: underline;
            vertical-align: top;
            max-width: calc(100% - 80px);
        }
        
        .signature {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .sig-box { 
            display: table-cell; 
            width: 25%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 5px;
        }
        .sig-box1 { 
            display: table-cell; 
            width: 25%;
            text-align: center; 
            vertical-align: top;
        }
        .sig-label { 
            font-size: 10pt; 
            margin-bottom: 60px; 
            display: block; 
        }
        .sig-label1 {
            font-size: 10pt;
            margin-bottom: 60px; 
            display: block;
            margin-left: 100px;
        }
        .sig-name { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
        }
        .sig-name1 { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
            margin-top: 76px;
        }
         
    </style>
    </head><body>';
    
    $html .= '<div class="header-row">
        <div class="header-left"><strong>Diterima dari :</strong><br>Terlampir</div>
        <div class="header-center">
            <div class="title">BUKTI KAS ' . $jenis . '</div>
            <div class="subtitle">' . htmlspecialchars($config['nama_perusahaan']) . '</div>
        </div>
        <div class="header-right">
            <strong>Nomor</strong> : ' . htmlspecialchars($nomor) . '<br>
            <strong>Tanggal</strong> : ' . htmlspecialchars($tanggal) . '
        </div>
    </div>
    
    <table class="table-transaksi">
        <thead>
            <tr>
                <th class="no-col">NO</th>
                <th>KETERANGAN</th>
                <th class="amount-col">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="no-col">1</td>
                <td>' . htmlspecialchars($data['keterangan']) . '</td>
                <td class="amount-col">Rp. ' . number_format($nominal, 2, ',', '.') . '</td>
            </tr>
        </tbody>
    </table>
    
    <div class="total-section">
        <span class="total-label">Total Diterima:</span>
        <span class="total-value">Rp. ' . number_format($nominal, 2, ',', '.') . '</span>
    </div>
    
    <div class="terbilang-section">
        <span class="terbilang-label">Rp (Huruf)</span>
        <span class="terbilang-value">' . htmlspecialchars($terbilang_text) . '</span>
    </div>
    
    <div class="signature">
        <div class="sig-box">
            <div class="sig-label">Diterima Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_1']) . '</span>
        </div>
        <div class="sig-box1">
            <div class="sig-label1">Dicek Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_2']) . '</span>
        </div>
        <div class="sig-box1">
            <span class="sig-name1">' . htmlspecialchars($config['ttd_jabatan_3']) . '</span>
        </div>
        <div class="sig-box">
            <div class="sig-label">Disetujui Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_4']) . '</span>
        </div>
    </div>
    </body></html>';
    
    $filename = 'Bukti_Kas_' . $jenis . '_' . str_replace('/', '-', $nomor) . '.pdf';
}

// bagian stok opname
elseif ($type === 'stok_opname') {
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$data) {
        die('ERROR: Data stok opname tidak ditemukan (ID: ' . $id . ')');
    }
    
    $nomor = $data['nomor_surat'];
        if (empty($nomor)) {
        $dt = strtotime($data['tanggal_opname']);
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $nomor = sprintf('%03d/KAS-KSK/%s/%04d', $data['id'], $bulan_romawi[date('n', $dt)], date('Y', $dt));
    }
    
    $tanggal = date('d-M-Y', strtotime($data['tanggal_opname']));
    
    // Ambil detail
    $qd = mysqli_prepare($conn, "SELECT * FROM stok_opname_detail WHERE stok_opname_id = ? ORDER BY no_urut ASC");
    mysqli_stmt_bind_param($qd, 'i', $id);
    mysqli_stmt_execute($qd);
    $resd = mysqli_stmt_get_result($qd);
    $details = [];
    while ($d = mysqli_fetch_assoc($resd)) {
        $details[$d['no_urut']] = $d;
    }
    mysqli_stmt_close($qd);
    
    // Hitung saldo kas dari database
    $q_saldo = mysqli_query($conn,
        "SELECT 
            (SELECT IFNULL(SUM(nominal),0) FROM transaksi WHERE jenis_transaksi='kas_terima') -
            (SELECT IFNULL(SUM(nominal),0) FROM transaksi WHERE jenis_transaksi='kas_keluar')
         AS saldo_kas"
    );
    $saldo_buku_kas = mysqli_fetch_assoc($q_saldo)['saldo_kas'] ?? 0;
    
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>
        @page { margin: 15mm 10mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 9pt; 
            color: #000; 
        }
        .header-row { 
            display: table;
            width: 100%; 
            margin-bottom: 15px; 
        }
        .header-left { 
            display: table-cell;
            width: 30%;
            vertical-align: top;
            font-size: 10pt;
        }
        .header-center { 
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }
        .header-right { 
            display: table-cell;
            width: 30%;
            text-align: left;
            vertical-align: top;
            font-size: 10pt;
            line-height: 1.6;
        }
        .title { 
            font-size: 16pt; 
            font-weight: bold; 
            margin: 0;
        }
        
        .saldo-container {
            display: table;
            width: 100%;
            margin: 15px 0 8px 0;
        }
        .saldo-row {
            display: table-row;
        }
        .saldo-label {
            display: table-cell;
            width: 70%;
            font-size: 10pt;
            vertical-align: middle;
        }
        .saldo-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }
        .saldo-value-box {
            display: inline-block;
            border: 2px solid #000;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 10pt;
            min-width: 180px;
            text-align: right;
        }
        
        .section-title { 
            font-weight: bold; 
            margin: 0 0 5px 0; 
            font-size: 9pt; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 8pt; 
            margin-bottom: 10px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 5px; 
        }
        th { 
            background-color: #e0e0e0; 
            font-weight: bold; 
            text-align: center; 
        }
        .no-col { 
            width: 30px; 
            text-align: center; 
        }
        .amount-col { 
            width: 120px; 
            text-align: right; 
        }
        
        /* Summary section */
        .summary-section {
            margin: 15px 0;
        }
        
        .jumlah-row {
            display: table;
            width: 100%;
            padding: 10px 0;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }
        .jumlah-left {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }
        .jumlah-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }
        .summary-left {
            display: table-cell;
            width: 70%;
            font-size: 9pt;
            padding-left: 0;
        }
        .summary-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-size: 9pt;
            padding-right: 0;
        }
        .summary-value-box {
            display: inline-block;
            border: 1px solid #000;
            padding: 5px 10px;
            min-width: 150px;
            text-align: right;
        }

        .total-fisik-section {
            margin: 15px 0;
        }
        .total-row {
            display: table;
            width: 100%;
            margin: 5px 0;
        }
        .total-label {
            display: table-cell;
            font-weight: bold;
            font-size: 9pt;
            text-align: right;
            padding-right: 15px;
            width: 70%;
            vertical-align: middle;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            width: 30%;
            vertical-align: middle;
        }
        .total-value-box {
            display: inline-block;
            border: 1px solid #000;
            padding: 5px 10px;
            min-width: 150px;
            text-align: right;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .keterangan-label {
            font-size: 9pt;
            margin: 15px 0 0 0;
        }
        .keterangan-box {
            margin: 0;
            padding: 0;
            min-height: 15px;
        }
        .keterangan-dots {
            border-bottom: 1px dotted #999;
            height: 1px;
            width: 100%;
            margin: 2px 0;
        }
        
        .signature {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .sig-box { 
            display: table-cell; 
            width: 25%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 5px;
        }
        .sig-box1 { 
            display: table-cell; 
            width: 25%;
            text-align: center; 
            vertical-align: top;
        }
        .sig-label { 
            font-size: 10pt; 
            margin-bottom: 60px; 
            display: block; 
        }
        .sig-label1 {
            font-size: 10pt;
            margin-bottom: 60px; 
            display: block;
            margin-left: 100px;
        }
        .sig-name { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
        }
        .sig-name1 { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
            margin-top: 76px;
        }
        
        .location-date {
            text-align: right;
            margin: 20px 0 10px 0;
            font-size: 9pt;
        }
    </style>
    </head><body>';
    
    // header
    $html .= '<div class="header-row">
        <div class="header-left">' . htmlspecialchars($config['nama_perusahaan']) . '</div>
        <div class="header-center">
            <div class="title">STOK OPNAME KAS</div>
            <div style="font-size:9pt; margin-top:5px;">Tanggal ' . htmlspecialchars($tanggal) . '</div>
        </div>
        <div class="header-right">
        </div>
    </div>';
    
    $html .= '<div class="saldo-container">
        <div class="saldo-row">
            <div class="saldo-label">Saldo Buku Kas</div>
            <div class="saldo-value">
                <span class="saldo-value-box">Rp. ' . number_format($saldo_buku_kas, 2, ',', '.') . '</span>
            </div>
        </div>
    </div>';
    
    // tabel untuk pecahan
    $html .= '<div class="section-title">I. Pemeriksaan Fisik Uang Kas</div>
    <table>
        <thead><tr><th class="no-col">NO</th><th>URAIAN</th><th>SATUAN</th><th>JUMLAH</th><th class="amount-col">NILAI</th></tr></thead>
        <tbody>';
    
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
    
    $total_nilai = 0;
    foreach ($pecahan as $p) {
        $detail = $details[$p['no']] ?? null;
        $jumlah = $detail ? intval($detail['jumlah']) : 0;
        $nilai = $jumlah * $p['nominal'];
        $total_nilai += $nilai;
        
        $html .= '<tr>
            <td class="no-col">' . $p['no'] . '</td>
            <td>' . htmlspecialchars($p['uraian']) . '</td>
            <td style="text-align:center;">' . htmlspecialchars($p['satuan']) . '</td>
            <td style="text-align:right;">' . ($jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-') . '</td>
            <td class="amount-col">Rp. ' . number_format($nilai, 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
    
    $bon = floatval($data['bon_sementara']);
    $rusak = floatval($data['uang_rusak']);
    $materai = floatval($data['materai']);
    $lain = floatval($data['lainnya']);

    $html .= '<div class="jumlah-row">
        <div class="jumlah-left">JUMLAH</div>
        <div class="jumlah-right">Rp. ' . number_format($total_nilai, 2, ',', '.') . '</div>
    </div>';
    
    $html .= '<div class="summary-section">';
    
    $html .= '<div class="summary-row">
        <div class="summary-left">II. Bon Sementara</div>
        <div class="summary-right">
            <span class="summary-value-box">Rp. ' . number_format($bon, 2, ',', '.') . '</span>
        </div>
    </div>';
    
    $html .= '<div class="summary-row">
        <div class="summary-left">III. Uang Rusak</div>
        <div class="summary-right">
            <span class="summary-value-box">Rp. ' . number_format($rusak, 2, ',', '.') . '</span>
        </div>
    </div>';
    
    $html .= '<div class="summary-row">
        <div class="summary-left">IV. Materai (Lembar @ 10.000)</div>
        <div class="summary-right">
            <span class="summary-value-box">Rp. ' . number_format($materai, 2, ',', '.') . '</span>
        </div>
    </div>';
    
    $html .= '<div class="summary-row">
        <div class="summary-left">V. Lain-lain</div>
        <div class="summary-right">
            <span class="summary-value-box">Rp. ' . number_format($lain, 2, ',', '.') . '</span>
        </div>
    </div>';
    
    $html .= '</div>'; // akhir dari bagian summary-section

    $html .= '<div class="total-fisik-section">
        <div class="total-row">
            <div class="total-label">JUMLAH SALDO FISIK</div>
            <div class="total-value">
                <span class="total-value-box">Rp. ' . number_format($data['fisik_total'], 2, ',', '.') . '</span>
            </div>
        </div>
        <div class="total-row">
            <div class="total-label">SELISIH</div>
            <div class="total-value">
                <span class="total-value-box">Rp. ' . number_format($data['selisih'], 2, ',', '.') . '</span>
            </div>
        </div>
    </div>';
    
    // Keterangan
    $html .= '<div class="keterangan-label">Keterangan</div>';
    $keterangan_text = !empty($data['keterangan_lainnya']) ? htmlspecialchars($data['keterangan_lainnya']) : '';
    $html .= '<div class="keterangan-box">' . $keterangan_text . '</div>';
    
    // Lokasi dan tanggal
    $html .= '<div class="location-date">' 
        . htmlspecialchars($config['kota']) . ', ' 
        . date('d-F-Y', strtotime($data['tanggal_opname'])) . '</div>';
    
    $html .= '<div class="signature">
        <div class="sig-box">
            <div class="sig-label">Diterima Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_1']) . '</span>
        </div>
        <div class="sig-box1">
            <div class="sig-label1">Dicek Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_2']) . '</span>
        </div>
        <div class="sig-box1">
            <span class="sig-name1">' . htmlspecialchars($config['ttd_jabatan_3']) . '</span>
        </div>
        <div class="sig-box">
            <div class="sig-label">Disetujui Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_4']) . '</span>
        </div>
    </div>
    </body></html>';
    
    $filename = 'Stok_Opname_' . str_replace('/', '-', $nomor) . '.pdf';
}

// bagian buku kas
elseif ($type === 'buku_kas') {
    
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
    
    $sql = "SELECT * FROM transaksi WHERE DATE(tanggal_transaksi) BETWEEN '$date_from' AND '$date_to' ORDER BY tanggal_transaksi ASC";
    $res = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    
    // Generate nomor surat untuk buku kas
    $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    $tanggal_to = strtotime($date_to);
    $nomor_buku = sprintf('001/KAS-KSK/%s/%04d', $bulan_romawi[date('n', $tanggal_to)], date('Y', $tanggal_to));
    $tanggal_formatted = date('d-M-Y', $tanggal_to);
    
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>
        @page { margin: 20mm 15mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 10pt; 
            color: #000; 
        }
        
        /* header */
        .header-row { 
            display: table; 
            width: 100%; 
            margin-bottom: 20px; 
        }
        .header-left { 
            display: table-cell; 
            width: 30%; 
            vertical-align: top; 
            font-size: 10pt;
            line-height: 1.4;
        }
        .header-center { 
            display: table-cell; 
            width: 40%; 
            text-align: center; 
            vertical-align: top; 
        }
        .header-right { 
            display: table-cell; 
            width: 30%; 
            text-align: left; 
            vertical-align: top; 
            font-size: 10pt;
            line-height: 1.6;
        }
        .title { 
            font-size: 16pt; 
            font-weight: bold; 
            margin: 0;
            text-decoration: underline;
        }
        
        /* Tabel */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 9pt; 
            margin-bottom: 20px;
        }
        th, td { 
            border: 1px solid #000; 
            padding: 6px 8px; 
        }
        th { 
            background-color: #e0e0e0; 
            font-weight: bold; 
            text-align: center; 
        }
        .amount-col { 
            text-align: right; 
        }
        .text-col {
            text-align: left;
        }
        
        /* Summary Section */
        .summary-divider {
            border-top: 2px solid #000;
            margin: 15px 0 10px 0;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin: 8px 0;
            font-size: 10pt;
        }
        .summary-label {
            display: table-cell;
            width: 50%;
            font-weight: bold;
            padding-right: 10px;
        }
        .summary-values {
            display: table-cell;
            width: 50%;
            text-align: right;
        }
        .summary-values span {
            display: inline-block;
            width: 48%;
            text-align: right;
            font-weight: bold;
        }
        .balance-row {
            border-bottom: 2px solid #000;
            padding-top: 10px;
            margin-top: 1px;
        }
        
        /* futer */
        .footer-location {
            text-align: right;
            margin: 20px 0 30px 0;
            font-size: 10pt;
            font-weight: bold;
        }
        .signature { 
            margin-top: 40px; 
            display: table; 
            width: 100%;
            max-width: 2000px;
        }
        .sig-box { 
            display: table-cell; 
            width: 25%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 5px;
        }
        .sig-box1 { 
            display: table-cell; 
            width: 25%;
            text-align: center; 
            vertical-align: top;
        }
        .sig-label { 
            font-size: 10pt; 
            margin-bottom: 60px; 
            display: block; 
        }
        .sig-label1 {
            font-size: 10pt;
            margin-bottom: 60px; 
            display: block;
            margin-left: 100px;
        }
        .sig-name { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
        }
        .sig-name1 { 
            font-size: 10pt; 
            border-bottom: 1px solid #000; 
            display: inline-block; 
            padding: 2px 10px; 
            min-width: 120px;
            margin-top: 76px;
        }
        .sig-role {
            font-size: 9pt;
            margin-top: 3px;
            display: block;
        }
    </style>
    </head><body>';
    
    // header
    $html .= '<div class="header-row">
        <div class="header-left">' . htmlspecialchars($config['nama_perusahaan']) . '</div>
        <div class="header-center">
            <div class="title">BUKU KAS HARIAN</div>
        </div>
        <div class="header-right">
            <strong>Nomor</strong> : ' . htmlspecialchars($nomor_buku) . '<br>
            <strong>Tanggal</strong> : ' . htmlspecialchars($tanggal_formatted) . '
        </div>
    </div>';
    
    // tabel transaksi
    $html .= '<table>
        <thead>
            <tr>
                <th style="width: 120px;">BUKTI KAS</th>
                <th>URAIAN</th>
                <th style="width: 140px;">DEBET</th>
                <th style="width: 140px;">KREDIT</th>
            </tr>
        </thead>
        <tbody>';
    
    $total_debet = 0;
    $total_kredit = 0;
    
    foreach ($rows as $row) {
        $nomor = $row['nomor_surat'] ?? '-';
        
        $debet = ($row['jenis_transaksi'] == 'kas_terima') ? floatval($row['nominal']) : 0;
        $kredit = ($row['jenis_transaksi'] == 'kas_keluar') ? floatval($row['nominal']) : 0;
        
        $total_debet += $debet;
        $total_kredit += $kredit;
        
        $html .= '<tr>
            <td style="text-align:center;">' . htmlspecialchars($nomor) . '</td>
            <td class="text-col">' . htmlspecialchars($row['keterangan']) . '</td>
            <td class="amount-col">' . ($debet > 0 ? number_format($debet, 2, ',', '.') : '-') . '</td>
            <td class="amount-col">' . ($kredit > 0 ? number_format($kredit, 2, ',', '.') : '-') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Summary Section
    $saldo = $total_debet - $total_kredit;
    
    $html .= '<div class="summary-divider"></div>
    
    <div class="summary-row">
        <div class="summary-label">Jumlah Transaksi</div>
        <div class="summary-values">
            <span>' . number_format($total_debet, 2, ',', '.') . '</span>
            <span>' . number_format($total_kredit, 2, ',', '.') . '</span>
        </div>
    </div>
    
    <div class="summary-row">
        <div class="summary-label">Saldo Per Tanggal ' . date('d-F-Y', strtotime($date_to)) . '</div>
        <div class="summary-values">
            <span></span>
            <span>' . number_format($saldo, 2, ',', '.') . '</span>
        </div>
    </div>
    
    <div class="summary-row balance-row">
        <div class="summary-label">Balance</div>
        <div class="summary-values">
            <span>' . number_format($total_debet, 2, ',', '.') . '</span>
            <span>' . number_format($total_debet, 2, ',', '.') . '</span>
        </div>
    </div>';
    
    // futer
    $html .= '<div class="footer-location">' . htmlspecialchars($config['kota']) . ', ' . date('d-F-Y', strtotime($date_to)) . '</div>
    
    <div class="signature">
        <div class="sig-box">
            <div class="sig-label">Diterima Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_1']) . '</span>
        </div>
        <div class="sig-box1">
            <div class="sig-label1">Dicek Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_2']) . '</span>
        </div>
        <div class="sig-box1">
            <span class="sig-name1">' . htmlspecialchars($config['ttd_jabatan_3']) . '</span>
        </div>
        <div class="sig-box">
            <div class="sig-label">Disetujui Oleh :</div>
            <span class="sig-name">' . htmlspecialchars($config['ttd_jabatan_4']) . '</span>
        </div>
    </div>
    
    </body></html>';
    
    $filename = 'Buku_Kas_Harian_' . date('Ymd', strtotime($date_to)) . '.pdf';
}


// biar bisa generate pdf
try {
    $options = new Options();
    $options->set('defaultFont', 'Times New Roman');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, array("Attachment" => false));
    
} catch (Exception $e) {
    die('ERROR saat generate PDF: ' . $e->getMessage());
}
?>