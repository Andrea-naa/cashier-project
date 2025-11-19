<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKU KAS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #E5FCED;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            gap: 12px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #009844;
            font-size: 16px;
        }

        .company-name {
            font-size: 13px;
            font-weight: bold;
        }

        .company-type {
            font-size: 11px;
            opacity: .85;
        }

        /* ================= CONTAINER ================= */
        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
            background-color: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            flex: 1;
        }

        .page-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
        }

        /* ================= DAFTAR KAS KELUAR ================= */
        .kas-list-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 14px;
        }

        /* ================= TABLE ================= */
        .table-wrapper {
            overflow-x: auto;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead {
            background-color: #E8E8E8;
        }

        th {
            padding: 14px 12px;
            text-align: center;
            font-weight: 600;
            color: #333;
            border: 1px solid #C0C0C0;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border: 1px solid #D3D3D3;
            color: #333;
            text-align: center;
            vertical-align: middle;
        }

        td:nth-child(1) {
            text-align: center;
        }

        td:nth-child(2) {
            text-align: left;
        }

        td:nth-child(3),
        td:nth-child(4) {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background-color: #F9F9F9;
        }

        tbody tr:hover {
            background-color: #F0F0F0;
        }

        /* ================= SUMMARY ================= */
        .summary-section {
            margin: 30px 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }

        .summary-label {
            font-weight: 600;
            color: #333;
        }

        .summary-value {
            font-weight: 600;
            color: #333;
        }

        .summary-divider {
            border-top: 2px solid #D3D3D3;
            margin: 10px 0;
        }

        .balance-row {
            font-weight: bold;
            font-size: 15px;
        }

        /* ================= BUTTONS ================= */
        .button-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 14px 0;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: 0.25s;
            font-size: 14px;
            text-align: center;
            max-width: 300px;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-primary:hover {
            background-color: #007a36;
        }

        .btn-secondary {
            background-color: #dcdcdc;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #c7c7c7;
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
            color: black;
        }

        .footer-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            margin-top: 3px;
        }

        .link-item {
            text-decoration: none;
            color: black;
        }

        .link-item:hover {
            opacity: 0.7;
        }

        /* RESPONSIVE */
        @media (max-width: 780px) {
            .footer-content {
                flex-direction: column;
            }

            .footer-left, .footer-right {
                width: 100%;
            }

            .footer-left {
                flex-direction: column;
                text-align: center;
            }

            .footer-logo {
                margin: 0 auto;
            }

            .footer-right {
                text-align: center;
                align-items: center;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media(max-width:768px){
            .container {
                padding: 25px 20px;
            }

            table {
                font-size: 11px;
            }

            th, td {
                padding: 8px 6px;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>BUKU KAS</h1>
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
        <h2 class="page-title">BUKU KAS HARIAN</h2>

        <h3 class="kas-list-title">Daftar Kas Harian</h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 120px;">BUKTI KAS</th>
                        <th>URAIAN</th>
                        <th style="width: 180px;">DEBET</th>
                        <th style="width: 180px;">KREDIT</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="4" class="empty-state">Tidak ada transaksi. Data akan dimuat dari database.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="summary-section">
            <div class="summary-row" style="border-top: 2px solid #333; padding-top: 15px;">
                <span class="summary-label" style="font-size: 15px;">Jumlah Transaksi</span>
                <div style="display: flex; gap: 100px;">
                    <span class="summary-value" id="totalDebet" style="min-width: 180px; text-align: right;">-</span>
                    <span class="summary-value" id="totalKredit" style="min-width: 180px; text-align: right;">-</span>
                </div>
            </div>
            
            <div class="summary-row">
                <span class="summary-label" style="font-size: 15px;">Saldo Per Tanggal <span id="currentDate"></span></span>
                <div style="display: flex; gap: 100px;">
                    <span class="summary-value" style="min-width: 180px;"></span>
                    <span class="summary-value" id="saldo" style="min-width: 180px; text-align: right;">-</span>
                </div>
            </div>
            
            <div class="summary-row balance-row" style="border-top: 2px solid #333; padding-top: 15px; margin-top: 15px;">
                <span class="summary-label" style="font-size: 16px;">Balance</span>
                <div style="display: flex; gap: 100px;">
                    <span class="summary-value" id="balanceDebet" style="min-width: 180px; text-align: right;">-</span>
                    <span class="summary-value" id="balanceKredit" style="min-width: 180px; text-align: right;">-</span>
                </div>
            </div>
        </div>

        <div class="button-group">
            <button class="btn btn-primary">Export ke PDF</button>
            <button class="btn btn-secondary" onclick="history.back()">Kembali</button>
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

    <script>
        // Format angka ke format standar dengan koma
        function formatRupiah(angka) {
            return angka.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Set tanggal saat ini
        function setCurrentDate() {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const today = new Date();
            const day = today.getDate();
            const month = months[today.getMonth()];
            const year = today.getFullYear();
            
            document.getElementById('currentDate').textContent = `${day}-${month}-${year}`;
        }

        // Hitung total dan update summary
        function calculateTotals() {
            const rows = document.querySelectorAll('#tableBody tr');
            let totalDebet = 0;
            let totalKredit = 0;
            let hasData = false;

            rows.forEach(row => {
                // Skip jika row adalah empty state
                if (row.cells.length === 4 && !row.querySelector('.empty-state')) {
                    hasData = true;
                    const debetCell = row.cells[2].textContent.trim();
                    const kreditCell = row.cells[3].textContent.trim();

                    // Parse debet (skip jika '-')
                    if (debetCell !== '-' && debetCell !== '') {
                        const debet = parseFloat(debetCell.replace(/[^0-9,-]/g, '').replace(',', '.'));
                        if (!isNaN(debet)) totalDebet += debet;
                    }

                    // Parse kredit (skip jika '-')
                    if (kreditCell !== '-' && kreditCell !== '') {
                        const kredit = parseFloat(kreditCell.replace(/[^0-9,-]/g, '').replace(',', '.'));
                        if (!isNaN(kredit)) totalKredit += kredit;
                    }
                }
            });

            // Update tampilan
            if (hasData) {
                // Hitung saldo (Debet - Kredit)
                const saldo = totalDebet - totalKredit;
                
                document.getElementById('totalDebet').textContent = formatRupiah(totalDebet);
                document.getElementById('totalKredit').textContent = formatRupiah(totalKredit);
                document.getElementById('saldo').textContent = formatRupiah(saldo);
                
                // Balance harus sama di kedua sisi (Total Debet = Total Kredit + Saldo)
                const balanceAmount = totalDebet;
                document.getElementById('balanceDebet').textContent = formatRupiah(balanceAmount);
                document.getElementById('balanceKredit').textContent = formatRupiah(balanceAmount);
            } else {
                // Tampilkan tanda '-' jika tidak ada data
                document.getElementById('totalDebet').textContent = '-';
                document.getElementById('totalKredit').textContent = '-';
                document.getElementById('saldo').textContent = '-';
                document.getElementById('balanceDebet').textContent = '-';
                document.getElementById('balanceKredit').textContent = '-';
            }
        }

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            setCurrentDate();
            calculateTotals();
        });

        // Fungsi untuk menambah data ke tabel (akan dipanggil dari database)
        function addTransaction(buktiKas, uraian, debet, kredit) {
            const tbody = document.getElementById('tableBody');
            
            // Hapus empty state jika ada
            const emptyState = tbody.querySelector('.empty-state');
            if (emptyState) {
                tbody.innerHTML = '';
            }
            
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${buktiKas}</td>
                <td>${uraian}</td>
                <td style="text-align: right;">${debet || '-'}</td>
                <td style="text-align: right;">${kredit || '-'}</td>
            `;
            
            calculateTotals();
        }

        // Fungsi untuk load data dari database (contoh)
        // Fungsi ini akan dipanggil dari backend PHP dengan data dari database
        function loadDataFromDatabase(data) {
            // data adalah array dari database
            // Contoh: [{bukti_kas: '001/KK-MSL/XI/2025', uraian: '...', debet: '', kredit: '50000'}, ...]
            
            data.forEach(item => {
                addTransaction(item.bukti_kas, item.uraian, item.debet, item.kredit);
            });
        }

        /* 
        CARA PENGGUNAAN DENGAN DATABASE:
        
        1. Di file PHP, load data dari database
        2. Convert ke format JSON
        3. Panggil fungsi loadDataFromDatabase dengan data tersebut
        
        Contoh di PHP:
        
        <?php
        // Ambil data dari database
        $query = "SELECT bukti_kas, uraian, debet, kredit FROM transaksi WHERE tanggal = CURDATE()";
        $result = mysqli_query($conn, $query);
        $data = [];
        while($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        ?>
        
        <script>
        // Load data ke tabel
        loadDataFromDatabase(<?php echo json_encode($data); ?>);
        </script>
        */
    </script>

</body>
</html>