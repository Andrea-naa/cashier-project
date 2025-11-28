<?php
// koneksi ke database
require_once 'config/conn_db.php';

check_login();
// ngambil sesi serta role user
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'] ?? 'Kasir';

// ngambil konfigurasi dari database
$query_config = "SELECT * FROM konfigurasi LIMIT 1";
$result_config = mysqli_query($conn, $query_config);
$config = mysqli_fetch_assoc($result_config);

if (!$config) {
    $nama_perusahaan = 'PT. Kalimantan Sawit Kusuma';
} else {
    $nama_perusahaan = $config['nama_perusahaan'] ?? 'PT. Kalimantan Sawit Kusuma';
}

// pilter untuk tanggal
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-t');

// ngammbil data transaksi
$sql = "SELECT * FROM transaksi 
        WHERE DATE(tanggal_transaksi) BETWEEN '$date_from' AND '$date_to' 
        ORDER BY 
            CASE 
                WHEN jenis_transaksi = 'kas_terima' THEN 1 
                WHEN jenis_transaksi = 'kas_keluar' THEN 2 
                ELSE 3 
            END ASC,
            tanggal_transaksi ASC,
            id ASC";

$res = mysqli_query($conn, $sql);
$rows = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    mysqli_free_result($res);
}

// Hitung total
$total_debet = 0;
$total_kredit = 0;

foreach ($rows as $row) {
    if ($row['jenis_transaksi'] == 'kas_terima') {
        $total_debet += floatval($row['nominal']);
    } else {
        $total_kredit += floatval($row['nominal']);
    }
}

$saldo = $total_debet - $total_kredit;
$balance = $total_debet;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKU KAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #E5FCED;
            display: flex;
        }

        /* menu burger */
        .sidebar {
            width: 280px;
            background: #E7E7E7FF;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            left: -280px;
            top: 0;
            height: 100vh;
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px 20px;
            border-bottom: 2px solid #c8e6c9;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar-header img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
        }
        
        .company-title {
            font-size: 11px;
            font-weight: bold;
            color: #000000FF;
            line-height: 1.3;
        }
        
        .menu-title {
            padding: 10px 20px;
            font-size: 12px;
            color: #666;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .menu-list {
            list-style: none;
            flex: 1;
        }
        
        .menu-item {
            margin: 5px 15px;
        }
        
        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #009844;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .menu-item a:hover {
            background: #c8e6c9;
            transform: translateX(5px);
        }
        
        .menu-item.active a {
            background: #a5d6a7;
            font-weight: 600;
        }
        
        .menu-icon {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        /* operlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }

        /* pembungkus utama */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100vh;
        }

        /*  header  */
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

        .menu-burger {
            font-size: 26px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .menu-burger:hover {
            transform: scale(1.1);
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
            font-weight: bold;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-size: 13px;
            font-weight: bold;
        }

        .user-role {
            font-size: 11px;
            opacity: .85;
        }

        /*  kontener  */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

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
            margin-bottom: 20px;
            color: #333;
        }

        /*  pilter  */
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-filter {
            padding: 10px 24px;
            background: #009844;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-filter:hover {
            background: #017033;
        }

        /*  daftar kas keluar  */
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

        /*  tabel  */
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
            text-align: left;
        }

        td:nth-child(2) {
            text-align: center;
        }

        td:nth-child(3){
            text-align: left; 
        }
        td:nth-child(4) {
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #F9F9F9;
        }

        tbody tr:hover {
            background-color: #F0F0F0;
        }

        /* summary  */
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

        /* buttpons  */
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
            text-decoration: none;
            display: inline-block;
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

        /* futer  */
        .ksk-footer {
            width: 100%;
            padding: 30px 40px;
            background: linear-gradient(to right, #00984489, #003216DB);
            color: #ffffff;
            border-top: 3px solid #333;
            font-family: 'Poppins', sans-serif;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 30px;
        }

        /* bagian kiri futer */
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

        /* bagian kanan futer */
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
            
            .filter-form {
                flex-direction: column;
            }
            
            .form-group {
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
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
        }
    </style>
</head>

<body>
   <!-- buat menu burger -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/gambar/logoksk.jpg" alt="KSK Logo"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22%3E%3Crect width=%2250%22 height=%2250%22 fill=%22%232e7d32%22 rx=%228%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2220%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
            <div class="company-title">
                <h4>KALIMANTAN SAWIT KUSUMA GROUP</h4>
                <p>Oil Palm Plantation & Industries</p>
            </div>
        </div>
        
        <div class="menu-title">Dashboard Menu</div>
        
        <ul class="menu-list">
            <li class="menu-item">
                <a href="dashboard.php">
                    <img src="assets/gambar/icon/homescreen.png" class="menu-icon">
                    <span>Home</span>
                </a>
            </li>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Pengaturan Nomor Surat</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="approval.php">
                    <i class="fas fa-check-circle menu-icon"></i>
                    <span>Approval</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="menu-item">
                <a href="logout.php">
                    <img src="assets/gambar/icon/logout.png" class="menu-icon">
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- konten utama -->
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>BUKU KAS</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($nama_lengkap, 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="container">
                <h2 class="page-title">BUKU KAS HARIAN</h2>

                <!-- bagian filter -->
                <div class="filter-section">
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </form>
                </div>

                <h3 class="kas-list-title">Daftar Kas Harian</h3>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 120px;">BUKTI KAS</th>
                                <th style="width:130px;">TANGGAL</th>
                                <th>URAIAN</th>
                                <th style="width: 180px;">DEBET</th>
                                <th style="width: 180px;">KREDIT</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">Tidak ada transaksi pada periode ini.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nomor_surat'] ?? '-'); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                    <td><?php echo ($row['jenis_transaksi'] == 'kas_terima') ? number_format($row['nominal'], 2, ',', '.') : '-'; ?></td>
                                    <td><?php echo ($row['jenis_transaksi'] == 'kas_keluar') ? number_format($row['nominal'], 2, ',', '.') : '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="summary-section">
                    <div class="summary-row" style="border-top: 2px solid #333; padding-top: 15px;">
                        <span class="summary-label" style="font-size: 15px;">Jumlah Transaksi</span>
                        <div style="display: flex; gap: 100px;">
                            <span class="summary-value" id="totalDebet" style="min-width: 180px; text-align: right;">
                                <?php echo number_format($total_debet, 2, ',', '.'); ?>
                            </span>
                            <span class="summary-value" id="totalKredit" style="min-width: 180px; text-align: right;">
                                <?php echo number_format($total_kredit, 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label" style="font-size: 15px;">Saldo Per Tanggal <span id="currentDate"><?php echo date('d-F-Y', strtotime($date_to)); ?></span></span>
                        <div style="display: flex; gap: 100px;">
                            <span class="summary-value" style="min-width: 180px;"></span>
                            <span class="summary-value" id="saldo" style="min-width: 180px; text-align: right;">
                                <?php echo number_format($saldo, 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="summary-row" style="border-top: 2px solid #333; padding-top: 15px; margin-top: 15px;">
                        <!-- <span class="summary-label" style="font-size: 16px;">Balance</span>
                        <div style="display: flex; gap: 100px;">
                            <span class="summary-value" id="balanceDebet" style="min-width: 180px; text-align: right;">
                                <?php echo number_format($balance, 2, ',', '.'); ?>
                            </span>
                            <span class="summary-value" id="balanceKredit" style="min-width: 180px; text-align: right;">
                                <?php echo number_format($balance, 2, ',', '.'); ?>
                            </span>
                        </div> -->
                    </div>
                </div>

                <div class="button-group">
                        <a href="export_pdf.php?type=buku_kas&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&print=1" 
                        target="_blank" 
                        class="btn btn-primary">
                            Export ke PDF
                        </a>
                    <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Kembali</button>
                </div>
            </div>

            <footer class="ksk-footer">
                <div class="footer-content">
                    <!-- bagian kiri futer -->
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

                    <!-- bagian kanan futer -->
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
        </div>
    </div>

    <script>
                
        function autoPrint(pdfUrl) { 
            printJS({ 
                printable: pdfUrl, 
                type: 'pdf', 
                showModal: true, 
                modalMessage: 'Memproses dokumen...' 
            }); 
        }

        //  script menu burger
        const menuBurger = document.getElementById('menuBurger');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }

        menuBurger.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);

        const menuItems = document.querySelectorAll('.menu-item a');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    </script>
</body>
</html>