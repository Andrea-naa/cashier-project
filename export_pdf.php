<?php
// ===================================
// EXPORT PDF - FIXED VERSION
// ===================================

// Enable error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_pdf.log');

// Koneksi database
require_once 'config/conn_db.php';

// Cek autoload
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('ERROR: Composer autoload not found. Jalankan: composer install');
}

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ===================================
// AMBIL KONFIGURASI PERUSAHAAN
// ===================================
$qk = mysqli_query($conn, "SELECT * FROM konfigurasi LIMIT 1");
$config = mysqli_fetch_assoc($qk) ?: [
    'nama_perusahaan' => 'PT. KALIMANTAN SAWIT KUSUMA',
    'alamat'          => 'Jl. W.R Supratman No. 42 Pontianak',
    'kota'            => 'Pontianak',
    'ttd_jabatan_1'   => 'Finance Dept Head',
    'ttd_jabatan_2'   => 'Finance Sub Dept Head',
    'ttd_jabatan_3'   => 'Finance Div Head',
    'ttd_jabatan_4'   => 'Cashier'
];

// ===================================
// DAFTAR PECAHAN UANG
// ===================================
$pecahan = [
    ['no'=>1, 'uraian'=>'Seratus Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>100000],
    ['no'=>2, 'uraian'=>'Lima Puluh Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>50000],
    ['no'=>3, 'uraian'=>'Dua Puluh Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>20000],
    ['no'=>4, 'uraian'=>'Sepuluh Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>10000],
    ['no'=>5, 'uraian'=>'Lima Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>5000],
    ['no'=>6, 'uraian'=>'Dua Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>2000],
    ['no'=>7, 'uraian'=>'Satu Ribuan Kertas', 'satuan'=>'Lembar', 'nominal'=>1000],
    ['no'=>8, 'uraian'=>'Satu Ribuan Logam', 'satuan'=>'Keping', 'nominal'=>1000],
    ['no'=>9, 'uraian'=>'Lima Ratusan Logam', 'satuan'=>'Keping', 'nominal'=>500],
    ['no'=>10, 'uraian'=>'Dua Ratusan Logam', 'satuan'=>'Keping', 'nominal'=>200],
    ['no'=>11, 'uraian'=>'Satu Ratusan Logam', 'satuan'=>'Keping', 'nominal'=>100],
];

// ===================================
// AMBIL DATA BERDASARKAN TYPE
// ===================================
$type = isset($_GET['type']) ? $_GET['type'] : 'stok_opname';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$rows = [];

try {
    if ($type === 'kas_masuk') {
        // Export Kas Masuk
        $res = mysqli_query($conn, "SELECT * FROM transaksi WHERE jenis_transaksi = 'kas_terima' ORDER BY tanggal_transaksi ASC");
        if (!$res) throw new Exception('Query kas_masuk error: ' . mysqli_error($conn));
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        
    } elseif ($type === 'kas_keluar') {
        // Export Kas Keluar
        $res = mysqli_query($conn, "SELECT * FROM transaksi WHERE jenis_transaksi = 'kas_keluar' ORDER BY tanggal_transaksi ASC");
        if (!$res) throw new Exception('Query kas_keluar error: ' . mysqli_error($conn));
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        
    } elseif ($id > 0) {
        // Export Satu Stok Opname
        $stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($f = mysqli_fetch_assoc($res)) $rows[] = $f;
        mysqli_stmt_close($stmt);
        
    } else {
        // Export Semua Stok Opname
        $res = mysqli_query($conn, "SELECT * FROM stok_opname ORDER BY tanggal_opname DESC");
        if (!$res) throw new Exception('Query stok_opname error: ' . mysqli_error($conn));
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    }
    
} catch (Exception $e) {
    die('ERROR: ' . $e->getMessage());
}

if (empty($rows)) {
    die('<h2>Tidak ada data untuk diexport.</h2><br><a href="javascript:history.back()">Kembali</a>');
}

// ===================================
// BUILD HTML UNTUK PDF
// ===================================
$html = '<!doctype html><html><head><meta charset="utf-8">
<style>
    body { 
        font-family: DejaVu Sans, Arial, sans-serif; 
        font-size: 12px; 
        color: #000; 
        margin: 20px;
    }
    .header-company {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .title { 
        font-weight: 700; 
        font-size: 18px; 
        text-align: center; 
        margin: 20px 0;
        text-decoration: underline; 
    }
    .subtitle {
        text-align: center;
        font-size: 12px;
        margin-bottom: 20px;
    }
    .table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 10px; 
    }
    .table th, .table td { 
        border: 1px solid #000; 
        padding: 8px; 
        font-size: 11px; 
    }
    .table th {
        background-color: #f0f0f0;
        font-weight: 700;
    }
    .right { text-align: right; }
    .center { text-align: center; }
    .info-box {
        margin: 15px 0;
        padding: 10px;
        border: 1px solid #ddd;
        background: #f9f9f9;
    }
    .ttd-wrap { 
        width: 100%; 
        margin-top: 40px; 
        display: table;
    }
    .ttd-box { 
        width: 24%; 
        text-align: center; 
        font-size: 11px;
        display: inline-block;
        vertical-align: top;
        margin-right: 1%;
    }
    .page-break {
        page-break-before: always;
    }
</style>
</head><body>';

// ===================================
// GENERATE PDF BERDASARKAN TYPE
// ===================================

if ($type === 'kas_masuk' || $type === 'kas_keluar') {
    // ========== PDF KAS MASUK / KELUAR ==========
    
    $title = ($type === 'kas_masuk') ? 'LAPORAN KAS MASUK' : 'LAPORAN KAS KELUAR';
    $jenis = ($type === 'kas_masuk') ? 'KM' : 'KK';
    
    // Nomor surat terakhir
    $last_nomor = '';
    if (!empty($rows)) {
        $last = end($rows);
        $dt = strtotime($last['tanggal_transaksi']);
        $last_nomor = sprintf('%03d/%s/%02d/%04d', $last['id'], $jenis, date('m', $dt), date('Y', $dt));
        reset($rows);
    }
    
    $html .= '<div class="header-company">' . htmlspecialchars($config['nama_perusahaan']) . '</div>';
    $html .= '<div class="title">' . $title . '</div>';
    
    if ($last_nomor) {
        $html .= '<div style="text-align:right; font-size:11px; margin-bottom:10px;">
                    <strong>Nomor Surat Terakhir:</strong> ' . htmlspecialchars($last_nomor) . '
                  </div>';
    }
    
    $html .= '<div class="subtitle">Tanggal Cetak: ' . date('d F Y H:i') . '</div>';
    
    $html .= '<table class="table">
                <thead>
                    <tr>
                        <th class="center" style="width:50px;">NO</th>
                        <th>KETERANGAN</th>
                        <th class="right" style="width:150px;">JUMLAH</th>
                        <th class="center" style="width:120px;">TANGGAL</th>
                    </tr>
                </thead>
                <tbody>';
    
    $total = 0;
    foreach ($rows as $i => $row) {
        $jumlah = floatval($row['nominal']);
        $total += $jumlah;
        
        $html .= '<tr>
                    <td class="center">' . ($i + 1) . '</td>
                    <td>' . htmlspecialchars($row['keterangan']) . '</td>
                    <td class="right">' . rupiah_fmt($jumlah) . '</td>
                    <td class="center">' . date('d-M-Y H:i', strtotime($row['tanggal_transaksi'])) . '</td>
                  </tr>';
    }
    
    $html .= '<tr style="background:#f0f0f0; font-weight:700;">
                <td colspan="2" class="right">TOTAL</td>
                <td class="right">' . rupiah_fmt($total) . '</td>
                <td></td>
              </tr>';
    
    $html .= '</tbody></table>';
    
} else {
    // ========== PDF STOK OPNAME ==========
    
    foreach ($rows as $index => $row) {
        if ($index > 0) {
            $html .= '<div class="page-break"></div>';
        }
        
        // Ambil detail pecahan
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
        
        // Header
        $html .= '<div class="header-company">' . htmlspecialchars($config['nama_perusahaan']) . '</div>';
        $html .= '<div class="title">STOK OPNAME KAS</div>';
        $html .= '<div class="subtitle">Tanggal: ' . date('d F Y', strtotime($row['tanggal_opname'])) . '</div>';
        
        // Info
        $html .= '<div class="info-box">
                    <strong>User:</strong> ' . htmlspecialchars($row['username']) . '<br>
                    <strong>Tanggal Opname:</strong> ' . date('d-M-Y H:i', strtotime($row['tanggal_opname'])) . '
                  </div>';
        
        // Tabel pecahan
        $html .= '<table class="table">
                    <thead>
                        <tr>
                            <th class="center" style="width:40px;">NO</th>
                            <th>URAIAN</th>
                            <th class="center" style="width:80px;">SATUAN</th>
                            <th class="right" style="width:100px;">JUMLAH</th>
                            <th class="right" style="width:150px;">NILAI</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $total_jumlah = 0;
        $total_nilai = 0;
        
        foreach ($pecahan as $p) {
            $no = $p['no'];
            $detail = $detail_map[$no] ?? ['jumlah' => 0];
            $jumlah = intval($detail['jumlah']);
            $nilai = $jumlah * $p['nominal'];
            
            $total_jumlah += $jumlah;
            $total_nilai += $nilai;
            
            $html .= '<tr>
                        <td class="center">' . $no . '</td>
                        <td>' . htmlspecialchars($p['uraian']) . '</td>
                        <td class="center">' . htmlspecialchars($p['satuan']) . '</td>
                        <td class="right">' . number_format($jumlah, 0, ',', '.') . '</td>
                        <td class="right">' . rupiah_fmt($nilai) . '</td>
                      </tr>';
        }
        
        $html .= '<tr style="background:#f0f0f0; font-weight:700;">
                    <td colspan="3" class="right">SUB TOTAL</td>
                    <td class="right">' . number_format($total_jumlah, 0, ',', '.') . '</td>
                    <td class="right">' . rupiah_fmt($total_nilai) . '</td>
                  </tr>';
        
        // Tambahan
        $bon = floatval($row['bon_sementara']);
        $rusak = floatval($row['uang_rusak']);
        $materai = floatval($row['materai']);
        $lain = floatval($row['lainnya']);
        
        if ($bon > 0) {
            $html .= '<tr><td colspan="4" class="right">Bon Sementara</td><td class="right">' . rupiah_fmt($bon) . '</td></tr>';
        }
        if ($rusak > 0) {
            $html .= '<tr><td colspan="4" class="right">Uang Rusak</td><td class="right">' . rupiah_fmt($rusak) . '</td></tr>';
        }
        if ($materai > 0) {
            $html .= '<tr><td colspan="4" class="right">Materai</td><td class="right">' . rupiah_fmt($materai) . '</td></tr>';
        }
        if ($lain > 0) {
            $html .= '<tr><td colspan="4" class="right">Lain-lain</td><td class="right">' . rupiah_fmt($lain) . '</td></tr>';
        }
        
        // Total Fisik
        $fisik_total = floatval($row['fisik_total']);
        $saldo_sistem = floatval($row['saldo_sistem']);
        $selisih = floatval($row['selisih']);
        
        $html .= '<tr style="background:#e0e0e0; font-weight:700;">
                    <td colspan="4" class="right">TOTAL FISIK</td>
                    <td class="right">' . rupiah_fmt($fisik_total) . '</td>
                  </tr>
                  <tr>
                    <td colspan="4" class="right">Saldo Sistem</td>
                    <td class="right">' . rupiah_fmt($saldo_sistem) . '</td>
                  </tr>
                  <tr style="font-weight:700;">
                    <td colspan="4" class="right">SELISIH</td>
                    <td class="right">' . rupiah_fmt($selisih) . '</td>
                  </tr>';
        
        $html .= '</tbody></table>';
        
        // TTD
        $html .= '<div class="ttd-wrap">
                    <div class="ttd-box">
                        <div>' . htmlspecialchars($config['ttd_jabatan_1']) . '</div>
                        <br><br><br>
                        <div>_________________</div>
                    </div>
                    <div class="ttd-box">
                        <div>' . htmlspecialchars($config['ttd_jabatan_2']) . '</div>
                        <br><br><br>
                        <div>_________________</div>
                    </div>
                    <div class="ttd-box">
                        <div>' . htmlspecialchars($config['ttd_jabatan_3']) . '</div>
                        <br><br><br>
                        <div>_________________</div>
                    </div>
                    <div class="ttd-box">
                        <div>' . htmlspecialchars($config['ttd_jabatan_4']) . '</div>
                        <br><br><br>
                        <div>_________________</div>
                    </div>
                  </div>';
    }
}

$html .= '</body></html>';

// ===================================
// GENERATE & OUTPUT PDF
// ===================================
try {
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Filename
    if ($type === 'kas_masuk') {
        $filename = 'Laporan_Kas_Masuk_' . date('Ymd_His') . '.pdf';
    } elseif ($type === 'kas_keluar') {
        $filename = 'Laporan_Kas_Keluar_' . date('Ymd_His') . '.pdf';
    } else {
        $filename = 'Stok_Opname_' . date('Ymd_His') . '.pdf';
    }
    
    // Output PDF (inline = tampil di browser, attachment = download)
    $dompdf->stream($filename, array("Attachment" => false));
    
} catch (Exception $e) {
    die('ERROR saat generate PDF: ' . $e->getMessage());
}
?>