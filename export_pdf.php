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

// echo '<script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>';
// echo '<link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">';

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

// Script auto-print
$auto_print_script = '';
if (isset($_GET['print']) && $_GET['print'] == '1') {
    $auto_print_script = '
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>';
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
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Sistem Kas Keuangan</title>
        <style>
            body {
                font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 50px;
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                font-size: 28px;
                margin-bottom: 15px;
            }
            p {
                color: #6c757d;
                font-size: 16px;
                margin-bottom: 30px;
            }
            .code-box {
                background: #f8f9fa;
                border-left: 4px solid #dc3545;
                padding: 15px;
                margin: 20px 0;
                text-align: left;
                font-family: monospace;
                font-size: 14px;
                color: #495057;
            }
            .btn {
                padding: 14px 32px;
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                display: inline-block;
                font-weight: 600;
            }
            .btn:hover {
                background: linear-gradient(135deg, #c82333, #bd2130);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-icon">⚠️</div>
            <h1>Parameter Tidak Lengkap</h1>
            <p>Parameter <strong>"type"</strong> harus diisi.</p>
            <div class="code-box">
                <strong>Contoh penggunaan:</strong><br>
                export_pdf.php?type=kas_masuk&id=1&print=1<br>
                export_pdf.php?type=kas_keluar&id=2&print=1<br>
                export_pdf.php?type=stok_opname&id=3&print=1<br>
                export_pdf.php?type=buku_kas&date_from=2024-01-01&date_to=2024-01-31&print=1
            </div>
            <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>';
    exit;
}

if ($id <= 0 && $type !== 'buku_kas') {
    echo '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Sistem Kas Keuangan</title>
        <style>
            body {
                font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 50px;
                max-width: 600px;
                text-align: center;
            }
            .error-icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #dc3545;
                font-size: 28px;
                margin-bottom: 15px;
            }
            p {
                color: #6c757d;
                font-size: 16px;
                margin-bottom: 30px;
            }
            .code-box {
                background: #f8f9fa;
                border-left: 4px solid #dc3545;
                padding: 15px;
                margin: 20px 0;
                text-align: left;
                font-family: monospace;
                font-size: 14px;
                color: #495057;
            }
            .btn {
                padding: 14px 32px;
                background: linear-gradient(135deg, #dc3545, #c82333);
                color: white;
                text-decoration: none;
                border-radius: 10px;
                display: inline-block;
                font-weight: 600;
            }
            .btn:hover {
                background: linear-gradient(135deg, #c82333, #bd2130);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-icon">⚠️</div>
            <h1>Parameter Tidak Lengkap</h1>
            <p>Parameter <strong>"id"</strong> harus diisi untuk tipe <strong>' . htmlspecialchars($type) . '</strong>.</p>
            <div class="code-box">
                <strong>Contoh penggunaan:</strong><br>
                export_pdf.php?type=' . htmlspecialchars($type) . '&id=1&print=1
            </div>
            <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
        </div>
    </body>
    </html>';
    exit;
}

$html = '';


// bagian kas masuk / keluar
if ($type === 'kas_masuk' || $type === 'kas_keluar') {
    
    // Ambil data dulu tanpa filter jenis_transaksi
    $stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$data) {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Sistem Kas Keuangan</title>
            <style>
                body {
                    font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    padding: 50px;
                    max-width: 600px;
                    text-align: center;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #dc3545;
                    font-size: 28px;
                    margin-bottom: 15px;
                }
                p {
                    color: #6c757d;
                    font-size: 16px;
                    margin-bottom: 30px;
                }
                .btn {
                    padding: 14px 32px;
                    background: linear-gradient(135deg, #dc3545, #c82333);
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    display: inline-block;
                    font-weight: 600;
                }
                .btn:hover {
                    background: linear-gradient(135deg, #c82333, #bd2130);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-icon">⚠️</div>
                <h1>Data Tidak Ditemukan</h1>
                <p>Data transaksi dengan ID <strong>' . $id . '</strong> tidak ditemukan dalam database.</p>
                <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
            </div>
        </body>
        </html>';
        exit;
    }
    
    // Auto-detect jenis transaksi dari database
    $jenis_transaksi_db = $data['jenis_transaksi'];
    
    // Tentukan jenis berdasarkan data dari database (bukan dari parameter)
    if ($jenis_transaksi_db == 'kas_terima') {
        $jenis = 'MASUK';
        $type = 'kas_masuk'; // Update type agar konsisten
    } else {
        $jenis = 'KELUAR';
        $type = 'kas_keluar'; // Update type agar konsisten
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
    ' . $auto_print_script . '
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
// bagian stok opname
elseif ($type === 'stok_opname') {
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if (!$data) {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - Sistem Kas Keuangan</title>
            <style>
                body {
                    font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .container {
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    padding: 50px;
                    max-width: 600px;
                    text-align: center;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #dc3545;
                    font-size: 28px;
                    margin-bottom: 15px;
                }
                p {
                    color: #6c757d;
                    font-size: 16px;
                    margin-bottom: 30px;
                }
                .btn {
                    padding: 14px 32px;
                    background: linear-gradient(135deg, #dc3545, #c82333);
                    color: white;
                    text-decoration: none;
                    border-radius: 10px;
                    display: inline-block;
                    font-weight: 600;
                }
                .btn:hover {
                    background: linear-gradient(135deg, #c82333, #bd2130);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-icon">⚠️</div>
                <h1>Data Tidak Ditemukan</h1>
                <p>Data stok opname dengan ID <strong>' . $id . '</strong> tidak ditemukan dalam database.</p>
                <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
            </div>
        </body>
        </html>';
        exit;
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
    ' . $auto_print_script . '
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
    
    $html .= '</div>';

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
    
    $html .= '<div class="keterangan-label">Keterangan</div>';
    $keterangan_text = !empty($data['keterangan_lainnya']) ? htmlspecialchars($data['keterangan_lainnya']) : '';
    $html .= '<div class="keterangan-box">' . $keterangan_text . '</div>';
    
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
    
    $sql = "SELECT * FROM transaksi 
        WHERE DATE(tanggal_transaksi) BETWEEN '$date_from' AND '$date_to' 
        ORDER BY 
            CASE 
                WHEN jenis_transaksi = 'kas_terima' THEN 1 
                WHEN jenis_transaksi = 'kas_keluar' THEN 2 
                ELSE 3 
            END ASC,
            tanggal_transaksi ASC";
            
    $res = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    
    // Generate nomor surat untuk buku kas
    $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    $tanggal_to = strtotime($date_to);
    $nomor_buku = sprintf('001/KAS-KSK/%s/%04d', $bulan_romawi[date('n', $tanggal_to)], date('Y', $tanggal_to));
    $tanggal_formatted = date('d-M-Y', $tanggal_to);
    
    $html = '<!doctype html><html><head><meta charset="utf-8">
    ' . $auto_print_script . '
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
    $temp_folder = __DIR__ . '/temp_pdf/';
    if (!file_exists($temp_folder)) {
        mkdir($temp_folder, 0755, true);
    }
    
    // Generate nama file unik
    $pdf_filename = date('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    $pdf_path = $temp_folder . $pdf_filename;
    
    // Simpan PDF
    $output = $dompdf->output();
    file_put_contents($pdf_path, $output);
    
    // Jika ada parameter print=1
    if (isset($_GET['print']) && $_GET['print'] == '1') {
        
        // Tentukan judul berdasarkan type
        $judul_dokumen = 'Dokumen';
        switch($type) {
            case 'kas_masuk':
                $judul_dokumen = 'Bukti Kas Masuk';
                break;
            case 'kas_keluar':
                $judul_dokumen = 'Bukti Kas Keluar';
                break;
            case 'stok_opname':
                $judul_dokumen = 'Stok Opname Kas';
                break;
            case 'buku_kas':
                $judul_dokumen = 'Buku Kas Harian';
                break;
        }
        
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cetak PDF - Sistem Kas Keuangan</title>
        <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
        <link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 50px;
                max-width: 600px;
                width: 100%;
                text-align: center;
                animation: slideIn 0.5s ease-out;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #28a745, #20c997);
                border-radius: 50%;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
            }

            .logo svg {
                width: 45px;
                height: 45px;
                fill: white;
            }

            h1 {
                color: #1e7e34;
                font-size: 28px;
                margin-bottom: 10px;
                font-weight: 700;
            }

            .subtitle {
                color: #6c757d;
                font-size: 16px;
                margin-bottom: 30px;
            }

            .status {
                background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
                border-left: 4px solid #28a745;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 30px;
            }

            .status-icon {
                font-size: 48px;
                margin-bottom: 10px;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
            }

            .status-text {
                color: #1e7e34;
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .status-detail {
                color: #495057;
                font-size: 14px;
            }

            .progress-bar {
                width: 100%;
                height: 8px;
                background: #e9ecef;
                border-radius: 10px;
                overflow: hidden;
                margin: 20px 0;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #28a745, #20c997);
                width: 0%;
                animation: loading 2s ease-in-out forwards;
            }

            @keyframes loading {
                to {
                    width: 100%;
                }
            }

            .button-group {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn {
                padding: 14px 32px;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .btn-primary {
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #218838, #1aa87c);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
            }

            .btn-secondary {
                background: white;
                color: #28a745;
                border: 2px solid #28a745;
            }

            .btn-secondary:hover {
                background: #f8f9fa;
                transform: translateY(-2px);
            }

            .icon {
                width: 20px;
                height: 20px;
                fill: currentColor;
            }

            .info-box {
                background: #f8f9fa;
                border-radius: 10px;
                padding: 15px;
                margin-top: 20px;
                font-size: 13px;
                color: #6c757d;
                text-align: left;
            }

            .info-box strong {
                color: #1e7e34;
            }

            @media (max-width: 600px) {
                .container {
                    padding: 30px 20px;
                }

                h1 {
                    font-size: 24px;
                }

                .button-group {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="logo">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                </svg>
            </div>

            <h1>' . htmlspecialchars($judul_dokumen) . '</h1>
            <p class="subtitle">Sistem Kas Keuangan KSK Group</p>

            <div class="status">
                <div class="status-icon">📄</div>
                <div class="status-text">PDF Berhasil Dibuat!</div>
                <div class="status-detail">Dokumen Anda siap untuk dicetak</div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>

            <div class="button-group">
                <a href="#" class="btn btn-primary" id="openPdfBtn">
                    <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" fill="currentColor"/>
                    </svg>
                    Buka PDF
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10,20V14H14V20H19V12H22L12,3L2,12H5V20H10Z" fill="currentColor"/>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            <div class="info-box">
                <strong>💡 Tips:</strong><br>
                • Jendela cetak akan terbuka otomatis<br>
                • Pastikan printer Anda sudah terhubung<br>
                • Pilih orientasi Portrait untuk hasil terbaik<br>
                • Simpan PDF jika ingin menyimpan salinan digital
            </div>
        </div>

        <script>
            const pdfUrl = "temp_pdf/' . $pdf_filename . '";
            
            // Update tombol dengan URL yang benar
            document.getElementById("openPdfBtn").href = pdfUrl;

            // Auto open PDF dan trigger print setelah halaman dimuat
            window.onload = function() {
                // Buat iframe tersembunyi untuk load PDF
                const iframe = document.createElement("iframe");
                iframe.style.display = "none";
                iframe.src = pdfUrl;
                document.body.appendChild(iframe);
                
                // Trigger print dialog setelah PDF dimuat
                iframe.onload = function() {
                    setTimeout(function() {
                        try {
                            iframe.contentWindow.print();
                        } catch(e) {
                            console.log("Auto print blocked, user can manually print");
                        }
                    }, 800);
                };
            };
        </script>
    </body>
    </html>';
    } else {
        // Redirect ke PDF jika tanpa parameter print
        header("Location: temp_pdf/" . $pdf_filename);
    }
    
} catch (Exception $e) {
    die('ERROR saat menyimpan PDF: ' . $e->getMessage());
}
?>