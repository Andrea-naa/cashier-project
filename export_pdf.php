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
        $temp = " " . $huruf[$angka];
    } else if ($angka < 20) {
        $temp = terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $temp = terbilang($angka / 10) . " puluh" . terbilang($angka % 10);
    } else if ($angka < 200) {
        $temp = " seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $temp = terbilang($angka / 100) . " ratus" . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $temp = " seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $temp = terbilang($angka / 1000) . " ribu" . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $temp = terbilang($angka / 1000000) . " juta" . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        $temp = terbilang($angka / 1000000000) . " milyar" . terbilang(fmod($angka, 1000000000));
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


// BUKTI KAS MASUK / KELUAR
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
    
    // Ambil nomor dari database
    $nomor = $data['nomor_surat'];
    
    // Jika nomor_surat NULL, generate manual
    if (empty($nomor)) {
        $dt = strtotime($data['tanggal_transaksi']);
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $kode = ($type === 'kas_masuk') ? 'KT-MSL' : 'KK-MSL';
        $nomor = sprintf('%03d/%s/%s/%04d', $data['id'], $kode, $bulan_romawi[date('n', $dt)], date('Y', $dt));
    }
    
    $tanggal = date('d-M-Y', strtotime($data['tanggal_transaksi']));
    $nominal = floatval($data['nominal']);
    
    // Pisahkan rupiah dan sen
    $rupiah_bulat = floor($nominal);
    $sen = round(($nominal - $rupiah_bulat) * 100);
    
    // Terbilang hanya untuk rupiah (tanpa sen)
    $terbilang_text = ucwords(terbilang($rupiah_bulat));
    if ($sen > 0) {
        $terbilang_text .= " koma " . terbilang($sen);
    }
    $terbilang_text .= " rupiah";
    
    // HTML 
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
            padding-right: 10px;
        }
        .total-label {
            display: inline-block;
            width: 150px;
            text-align: left;
            font-weight: bold;
        }
        .total-value {
            display: inline-block;
            width: 150px;
            text-align: right;
            font-weight: bold;
        }
        
        .terbilang { 
            margin: 15px 0; 
            line-height: 1.5;
        }
        .terbilang-underline {
            text-decoration: underline;
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
            // padding: 0 5px;
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
    
    <div class="terbilang">
        Rp (Huruf) <span class="terbilang-underline">' . htmlspecialchars($terbilang_text) . '</span>
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

// STOK OPNAME KAS
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
        $nomor = sprintf('%03d/KAS-MSL/%s/%04d', $data['id'], $bulan_romawi[date('n', $dt)], date('Y', $dt));
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
    
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>
        @page { margin: 15mm 10mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 9pt; 
            color: #000; 
        }
        .header { text-align: center; margin-bottom: 15px; }
        .title { font-size: 14pt; font-weight: bold; }
        .info-box { text-align: right; margin-bottom: 10px; font-size: 9pt; }
        .saldo-box { text-align: right; background: #f0f0f0; padding: 8px; margin: 10px 0; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 8pt; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #e0e0e0; font-weight: bold; text-align: center; }
        .no-col { width: 30px; text-align: center; }
        .amount-col { width: 120px; text-align: right; }
        .total-row { background-color: #f5f5f5; font-weight: bold; }
        .section-title { font-weight: bold; margin: 10px 0 5px 0; font-size: 9pt; }
        .signature { margin-top: 30px; display: table; width: 100%; font-size: 8pt; }
        .sig-box { display: table-cell; width: 25%; text-align: center; vertical-align: top; }
        .sig-label { margin-bottom: 50px; display: block; }
        .sig-name { border-top: 1px solid #000; display: inline-block; padding-top: 3px; }
    </style>
    </head><body>';
    
    $html .= '<div style="font-size:9pt;">' . htmlspecialchars($config['nama_perusahaan']) . '</div>
    <div class="header"><div class="title">STOK OPNAME KAS</div></div>
    <div class="info-box">
        <strong>Nomor</strong> : ' . htmlspecialchars($nomor) . '<br>
        <strong>Tanggal</strong> : ' . htmlspecialchars($tanggal) . '
    </div>
    
    <div class="saldo-box">Saldo Buku Kas: Rp. ' . number_format($data['fisik_total'], 2, ',', '.') . '</div>
    
    <div class="section-title">I. Pemeriksaan Fisik Uang Kas</div>
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
    
    $html .= '<tr class="total-row">
        <td colspan="4" style="text-align:right; padding-right:10px;">JUMLAH</td>
        <td class="amount-col">Rp. ' . number_format($total_nilai, 2, ',', '.') . '</td>
    </tr></tbody></table>';
    
    $bon = floatval($data['bon_sementara']);
    $rusak = floatval($data['uang_rusak']);
    $materai = floatval($data['materai']);
    $lain = floatval($data['lainnya']);
    
    $html .= '<div class="section-title">II. Bon Sementara</div>
    <div style="text-align:right; margin-bottom:5px;">Rp. ' . number_format($bon, 2, ',', '.') . '</div>
    
    <div class="section-title">III. Uang Rusak</div>
    <div style="text-align:right; margin-bottom:5px;">Rp. ' . number_format($rusak, 2, ',', '.') . '</div>
    
    <div class="section-title">IV. Materai (Lembar @ 6.000)</div>
    <div style="text-align:right; margin-bottom:5px;">Rp. ' . number_format($materai, 2, ',', '.') . '</div>
    
    <div class="section-title">V. Lain-lain</div>
    <div style="text-align:right; margin-bottom:5px;">Rp. ' . number_format($lain, 2, ',', '.') . '</div>
    
    <div style="border-top:2px solid #000; margin:10px 0;"></div>
    <div style="display:flex; justify-content:space-between; font-weight:bold; margin:5px 0;">
        <span>JUMLAH SALDO FISIK</span>
        <span>Rp. ' . number_format($data['fisik_total'], 2, ',', '.') . '</span>
    </div>
    <div style="display:flex; justify-content:space-between; margin:5px 0;">
        <span>SELISIH</span>
        <span>Rp. ' . number_format($data['selisih'], 2, ',', '.') . '</span>
    </div>
    
    <div style="text-align:right; margin:15px 0; font-size:8pt;">' . htmlspecialchars($config['kota']) . ', ' . date('d-F-Y', strtotime($data['tanggal_opname'])) . '</div>
    
    <div class="signature">
        <div class="sig-box">
            <div class="sig-label">Disetujui Oleh :</div>
            <div class="sig-name">' . htmlspecialchars($config['ttd_jabatan_4']) . '</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Dicek Oleh :</div>
            <div class="sig-name">' . htmlspecialchars($config['ttd_jabatan_3']) . '</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Dibuat Oleh :</div>
            <div class="sig-name">' . htmlspecialchars($config['ttd_jabatan_2']) . '</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Dibuat Oleh :</div>
            <div class="sig-name">' . htmlspecialchars($config['ttd_jabatan_1']) . '</div>
        </div>
    </div>
    </body></html>';
    
    $filename = 'Stok_Opname_' . str_replace('/', '-', $nomor) . '.pdf';
}

// BUKU KAS HARIAN
elseif ($type === 'buku_kas') {
    
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');
    
    $sql = "SELECT * FROM transaksi WHERE DATE(tanggal_transaksi) BETWEEN '$date_from' AND '$date_to' ORDER BY tanggal_transaksi ASC";
    $res = mysqli_query($conn, $sql);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    
    $html = '<!doctype html><html><head><meta charset="utf-8">
    <style>
        @page { margin: 15mm; }
        body { 
            font-family: "Times New Roman", Times, serif; 
            font-size: 9pt; 
            color: #000; 
        }
        .header { text-align: center; margin-bottom: 15px; }
        .title { font-size: 14pt; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #e0e0e0; font-weight: bold; text-align: center; }
        .amount-col { text-align: right; }
        .total-row { background-color: #f5f5f5; font-weight: bold; }
    </style>
    </head><body>';
    
    $html .= '<div style="font-size:9pt;">' . htmlspecialchars($config['nama_perusahaan']) . '</div>
    <div class="header"><div class="title">BUKU KAS HARIAN</div></div>
    
    <table>
        <thead><tr><th>BUKTI KAS</th><th>URAIAN</th><th>DEBET</th><th>KREDIT</th></tr></thead>
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
            <td>' . htmlspecialchars($nomor) . '</td>
            <td>' . htmlspecialchars($row['keterangan']) . '</td>
            <td class="amount-col">' . ($debet > 0 ? number_format($debet, 2, ',', '.') : '-') . '</td>
            <td class="amount-col">' . ($kredit > 0 ? number_format($kredit, 2, ',', '.') : '-') . '</td>
        </tr>';
    }
    
    $saldo = $total_debet - $total_kredit;
    
    $html .= '</tbody></table>
    
    <div style="margin-top:15px; text-align:right;">
        <strong>Jumlah Transaksi:</strong> Rp. ' . number_format($total_debet, 2, ',', '.') . ' | Rp. ' . number_format($total_kredit, 2, ',', '.') . '<br>
        <strong>Saldo:</strong> Rp. ' . number_format($saldo, 2, ',', '.') . '<br>
        <strong>Balance:</strong> Rp. ' . number_format($total_debet, 2, ',', '.') . '
    </div>
    </body></html>';
    
    $filename = 'Buku_Kas_Harian_' . date('Ymd') . '.pdf';
}

else {
    die('ERROR: Type tidak dikenali. Gunakan: kas_masuk, kas_keluar, stok_opname, atau buku_kas');
}

// GENERATE PDF
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