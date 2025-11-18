<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TABEL STOK OPNAME</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #E5FCED;
        }

                /* ================= HEADER ================= */
        .header {
            background-color: #009844;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .menu-icon {
            font-size: 26px;
            cursor: pointer;
        }

        .header h1 {
            font-size: 22px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #2e7d32;
            font-size: 18px;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-role {
            
            font-size: 12px;
            opacity: 0.9;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
        }

        .company-type {
            font-size: 11px;
            opacity: .85;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .report-date {
            font-size: 11px;
            text-align: center;
            margin-bottom: 20px;
        }

        .saldo-buku {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .saldo-buku span:first-child {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 11px;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f8f8f8;
            font-weight: bold;
            text-align: center;
        }

        .no-column {
            width: 40px;
            text-align: center;
        }

        .satuan-column {
            width: 80px;
            text-align: center;
        }

        .jumlah-column {
            width: 80px;
            text-align: center;
        }

        .nilai-column {
            width: 130px;
            text-align: right;
        }

        td.no-column {
            text-align: center;
        }

        td.satuan-column {
            text-align: center;
        }

        td.jumlah-column {
            text-align: center;
        }

        td.nilai-column {
            text-align: right;
        }

        .total-row {
            background-color: #f8f8f8;
            font-weight: bold;
        }

        .section-row {
            font-weight: bold;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background-color: #0e8c4a;
            color: white;
        }

        .btn-secondary {
            background-color: #ddd;
            color: #333;
        }

        /* ================= FOOTER ================= */
        .ksk-footer {
            width: 100%;
            padding: 30px 40px;
            background: linear-gradient(to right, #00984489, #003216DB);
            color: #ffffff;
            border-top: 3px solid #333;
            font-family: 'Poppins', sans-serif;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 30px;
        }

        /* Left Section */
        .footer-left {
            display: flex;
            flex-direction: row;
            gap: 20px;
            width: 60%;
        }

        .footer-logo {
            width: 70px;
            height: 70px;
            /* background: white; */
            padding: 8px;
            border-radius: 10px;
        }

        .footer-text h2 {
            font-size: 18px;
            font-weight: 700;
            color: black;
        }

        .footer-text .subtitle {
            font-size: 14px;
            margin-top: -4px;
            color: black;
        }

        .footer-text .description {
            font-size: 13px;
            margin-top: 10px;
            line-height: 1.5;
            color: black;
        }

        /* Right Section */
        .footer-right {
            width: 40%;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .footer-item {
            display: flex;
            align-items: start;
            gap: 10px;
            color: black    ;
        }

        .footer-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            margin-top: 3px;
        }

        .link-item {
            text-decoration: none;
            color: black ;
        }

        .link-item:hover {
            opacity: 0.7;
        }


        @media(max-width:768px){
            .container {
                padding: 25px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            table {
                font-size: 10px;
            }
        }

        @media print {
            .header, .button-group, .ksk-footer {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .container {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <?php
    // Fungsi untuk format rupiah
    function formatRupiah($angka) {
        return 'Rp. ' . number_format($angka, 2, ',', '.');
    }

    // Data fisik uang kas dengan nominal
    $fisik_uang_kas = [
        ['no' => 1, 'uraian' => 'Seratus Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 100000],
        ['no' => 2, 'uraian' => 'Lima Puluh Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 50000],
        ['no' => 3, 'uraian' => 'Dua Puluh Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 20000],
        ['no' => 4, 'uraian' => 'Sepuluh Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 10000],
        ['no' => 5, 'uraian' => 'Lima Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 5000],
        ['no' => 6, 'uraian' => 'Dua Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 2000],
        ['no' => 7, 'uraian' => 'Satu Ribuan Kertas', 'satuan' => 'Lembar', 'nominal' => 1000],
        ['no' => 8, 'uraian' => 'Satu Ribuan Logam', 'satuan' => 'Keping', 'nominal' => 1000],
        ['no' => 9, 'uraian' => 'Lima Ratusan Logam', 'satuan' => 'Keping', 'nominal' => 500],
        ['no' => 10, 'uraian' => 'Dua Ratusan Logam', 'satuan' => 'Keping', 'nominal' => 200],
        ['no' => 11, 'uraian' => 'Satu Ratusan Logam', 'satuan' => 'Keping', 'nominal' => 100],
    ];

    // Simulasi data dari form (bisa diganti dengan data dari database)
    $data_jumlah = [
        1 => 1025,  // Seratus ribuan
        2 => 237,   // Lima puluh ribuan
        3 => 847,   // Dua puluh ribuan
        4 => 747,   // Sepuluh ribuan
        5 => 688,   // Lima ribuan
        6 => 102,   // Dua ribuan
        7 => 30,    // Satu ribuan kertas
        8 => 0,     // Satu ribuan logam
        9 => 0,     // Lima ratusan logam
        10 => 0,    // Dua ratusan logam
        11 => 0,    // Satu ratusan logam
    ];

    // Data tambahan
    $bon_sementara = 19795500;
    $uang_rusak = 200000;
    $materai = 0;
    $lain_lain = 139122934;
    $saldo_buku = 301551555;

    // Hitung total fisik uang kas
    $total_fisik = 0;
    foreach($fisik_uang_kas as $item) {
        $jumlah = isset($data_jumlah[$item['no']]) ? $data_jumlah[$item['no']] : 0;
        $total_fisik += $jumlah * $item['nominal'];
    }

    // Hitung jumlah saldo fisik
    $jumlah_saldo_fisik = $total_fisik + $bon_sementara + $uang_rusak + $materai + $lain_lain;

    // Hitung selisih
    $selisih = $jumlah_saldo_fisik - $saldo_buku;

    // Tanggal saat ini
    $tanggal = date('d-M-Y');
    ?>

   <div class="header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>TABEL STOK OPNAME</h1>
        </div>
        <div class="user-info">
            <div class="user-avatar">🏢</div>
            <div>
                <div class="company-name">PT. Mitra Saudara Lestari</div>
                <div class="company-type">Kasir</div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="company-name">PT. MITRA SAUDARA LESTARI - MSL</div>
        <div class="report-title">STOK OPNAME KAS</div>
        <div class="report-date">Tanggal <?php echo $tanggal; ?></div>

        <div class="saldo-buku">
            <span>Saldo Buku Kas</span>
            <span><?php echo formatRupiah($saldo_buku); ?></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="no-column">NO</th>
                    <th>URAIAN</th>
                    <th class="satuan-column">SATUAN</th>
                    <th class="jumlah-column">JUMLAH</th>
                    <th class="nilai-column">NILAI</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-row">
                    <td colspan="5">I. Pemeriksaan Fisik Uang Kas</td>
                </tr>
                <?php 
                foreach($fisik_uang_kas as $item): 
                    $jumlah = isset($data_jumlah[$item['no']]) ? $data_jumlah[$item['no']] : 0;
                    $nilai = $jumlah * $item['nominal'];
                ?>
                <tr>
                    <td class="no-column"><?php echo $item['no']; ?></td>
                    <td><?php echo $item['uraian']; ?></td>
                    <td class="satuan-column"><?php echo $item['satuan']; ?></td>
                    <td class="jumlah-column"><?php echo $jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-'; ?></td>
                    <td class="nilai-column"><?php echo $jumlah > 0 ? formatRupiah($nilai) : 'Rp. -'; ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align: center;">JUMLAH</td>
                    <td class="nilai-column"><?php echo formatRupiah($total_fisik); ?></td>
                </tr>
                <tr>
                    <td colspan="4">II. Bon Sementara</td>
                    <td class="nilai-column"><?php echo formatRupiah($bon_sementara); ?></td>
                </tr>
                <tr>
                    <td colspan="4">III. Uang Rusak</td>
                    <td class="nilai-column"><?php echo formatRupiah($uang_rusak); ?></td>
                </tr>
                <tr>
                    <td colspan="4">IV. Materai ( Lembar @ 10.000 )</td>
                    <td class="nilai-column"><?php echo formatRupiah($materai); ?></td>
                </tr>
                <tr>
                    <td colspan="4">V. Lain-lain</td>
                    <td class="nilai-column"><?php echo formatRupiah($lain_lain); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" style="text-align: center;">JUMLAH SALDO FISIK</td>
                    <td class="nilai-column"><?php echo formatRupiah($jumlah_saldo_fisik); ?></td>
                </tr>
                <tr>
                    <td colspan="4">Keterangan</td>
                    <td class="nilai-column"></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" style="text-align: center;">SELISIH</td>
                    <td class="nilai-column"><?php echo formatRupiah($selisih); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="button-group">
            <button type="button" class="btn btn-primary" onclick="window.print()">Export ke PDF</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='stok-opname.php'">Kembali</button>
        </div>
    </div>

    <footer class="ksk-footer">
  <div class="footer-content">

    <!-- Left Section -->
    <div class="footer-left">
      <img src="assets/gambar/logoksk.jpg" alt="KSK Logo" class="footer-logo">

      <div class="footer-text">
        <h2>KALIMANTAN SAWIT KUSUMA GROUP</h2>
        <p class="subtitle">Oil Palm Plantation & Industries</p>

        <p class="description">
          Kalimantan Sawit Kusuma (KSK) adalah sebuah grup perusahaan yang memiliki beberapa 
          perusahaan afiliasi yang bergerak di berbagai bidang usaha, yaitu perkebunan kelapa 
          sawit dan hortikultura, kontraktor alat berat dan pembangunan perkebunan serta jasa 
          transportasi laut.
        </p>
      </div>
    </div>

<!-- Right Section -->
<div class="footer-right">

  <a href="https://kskgroup.co.id" target="_blank" class="footer-item link-item">
    <img src="assets/gambar/icon/browser.png" class="footer-icon">
    <span>kskgroup.co.id</span>
  </a>

  <a href="tel:+62561733035" class="footer-item link-item">
    <img src="assets/gambar/icon/telfon.png" class="footer-icon">
    <span>
      T. (+62 561) 733 035 (hunting)<br>
      F. (+62 561) 733 014
    </span>
  </a>

  <a href="https://maps.app.goo.gl/MdtmPLQTTagexjF59" target="_blank" class="footer-item link-item">
    <img src="assets/gambar/icon/lokasi.png" class="footer-icon">
    <span>
      Jl. W.R Supratman No. 42 Pontianak,<br>
      Kalimantan Barat 78122
    </span>
  </a>

</div>

  </div>
</footer>
</body>
</html>