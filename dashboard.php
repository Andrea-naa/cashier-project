<?php

// Koneksi ke database
require_once 'config/conn_db.php';
// cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// dapetin info dari sesi
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];

// dapetin konfigurasi dari database 
$query_config = "SELECT * FROM konfigurasi LIMIT 1";
$result_config = mysqli_query($conn, $query_config);
$config = mysqli_fetch_assoc($result_config);

// Jika belum ada konfigurasi, set default
if (!$config) {
    $insert_config = "INSERT INTO konfigurasi (nama_perusahaan, alamat, kota, telepon, email) 
                      VALUES ('PT. Kalimantan Sawit Kusuma', 'Jl. W.R Supratman No. 42 Pontianak', 
                              'Sungai Buluh', '0778-123456', 'info@msl.com')";
    mysqli_query($conn, $insert_config);
    
    $result_config = mysqli_query($conn, $query_config);
    $config = mysqli_fetch_assoc($result_config);
}

$nama_perusahaan = $config['nama_perusahaan'] ?? 'PT. Mitra Saudara Lestari';

// Ambil role user dari session
$role = $_SESSION['role'] ?? 'Kasir';

// Hitung saldo kas (Kas Masuk - Kas Keluar) dari tabel transaksi
$saldo_kas = 0;

// Hitung kas terima (masuk)
$query_masuk = "SELECT COALESCE(SUM(nominal), 0) as total FROM transaksi WHERE jenis_transaksi = 'kas_terima'";
$result_masuk = mysqli_query($conn, $query_masuk);
if ($result_masuk) {
    $row_masuk = mysqli_fetch_assoc($result_masuk);
    $saldo_kas += $row_masuk['total'] ?? 0;
}

// Hitung kas keluar
$query_keluar = "SELECT COALESCE(SUM(nominal), 0) as total FROM transaksi WHERE jenis_transaksi = 'kas_keluar'";
$result_keluar = mysqli_query($conn, $query_keluar);
if ($result_keluar) {
    $row_keluar = mysqli_fetch_assoc($result_keluar);
    $saldo_kas -= $row_keluar['total'] ?? 0;
}

// Format saldo kas dengan pemisah ribuan
$saldo_kas_formatted = number_format($saldo_kas, 0, ',', '.');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Kas Kebun</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #FFFFFFFF;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #E7E7E7FF;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
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
        
        /* konten utama */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* header */
        .header {
            background: linear-gradient(135deg, #009844 0%, #009844 100%);
            padding: 25px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome-text {
            font-size: 32px;
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
        
        /* area konten */
        .content-area {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .menu-grid {
            display: flex;
            flex-direction: column;
            gap: 40px;
            width: 100%;
            max-width: 980px;
            margin-top: 10px;
        }

        .menu-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 60px;
            justify-items: center;
            align-items: start;
        }
        
        .menu-card {
            background: linear-gradient(to top, #009844, #00320E);
            border-radius: 15px;
            padding: 36px 28px;
            width: 260px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.18);
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            background: linear-gradient(to top, #009844, #00320E);
        }
        
        .menu-card-icon {
            width: 84px;
            height: 84px;
            margin: 0 auto 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 44px;
        }
        
        .menu-card-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* futer */
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
        
        .header-left {
            display: flex;
            align-items: flex-start;
            gap: 0;
            flex-direction: column;
        }
        
        .saldo-kas-info {
            font-size: 15px;
            opacity: 0.95;
            margin-top: -5px;
        }
        
        /* biar responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 15px 0;
            }
            
            .sidebar-header {
                flex-direction: row;
                padding: 10px 15px;
            }
            
            .sidebar-header img {
                width: 40px;
                height: 40px;
            }
            
            .company-title h4 {
                font-size: 10px;
            }
            
            .company-title p {
                font-size: 8px;
            }
            
            .menu-title {
                padding: 10px 15px;
                font-size: 11px;
            }
            
            .menu-list {
                display: flex;
                overflow-x: auto;
                padding: 10px 0;
                gap: 10px;
            }
            
            .menu-item {
                margin: 0 5px;
                white-space: nowrap;
                flex-shrink: 0;
            }
            
            .menu-item a {
                padding: 10px 15px;
                font-size: 13px;
            }
            
            .menu-icon {
                width: 24px;
                height: 24px;
            }
            
            .header {
                flex-direction: column;
                padding: 20px 15px;
                gap: 15px;
            }
            
            .header-left {
                width: 100%;
                align-items: center;
                text-align: center;
            }
            
            .welcome-text {
                font-size: 24px;
            }
            
            .saldo-kas-info {
                font-size: 13px;
                margin-top: 5px;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            
            .user-name {
                font-size: 13px;
            }
            
            .user-role {
                font-size: 11px;
            }
            
            .content-area {
                padding: 20px 15px;
            }
            
            .menu-grid {
                gap: 20px;
                max-width: 100%;
            }
            
            .menu-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .menu-card {
                width: 100%;
                max-width: 350px;
                margin: 0 auto;
                padding: 30px 20px;
            }
            
            .menu-card-icon {
                width: 70px;
                height: 70px;
                font-size: 36px;
            }
            
            .menu-card-title {
                font-size: 16px;
            }
            
            .ksk-footer {
                padding: 20px 15px;
            }
            
            .footer-content {
                flex-direction: column;
                gap: 20px;
            }

            .footer-left, .footer-right {
                width: 100%;
            }

            .footer-left {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 15px;
            }

            .footer-logo {
                margin: 0 auto;
                width: 60px;
                height: 60px;
            }
            
            .footer-text h2 {
                font-size: 16px;
            }
            
            .footer-text .subtitle {
                font-size: 12px;
            }
            
            .footer-text .description {
                font-size: 12px;
                text-align: justify;
            }

            .footer-right {
                align-items: center;
            }
            
            .footer-item {
                justify-content: center;
                text-align: left;
                font-size: 13px;
            }
            
            .footer-icon {
                width: 18px;
                height: 18px;
            }
        }

        /* Untuk HP yang sangat kecil */
        @media (max-width: 480px) {
            .welcome-text {
                font-size: 20px;
            }
            
            .saldo-kas-info {
                font-size: 12px;
            }
            
            .user-avatar {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
            
            .content-area {
                padding: 15px 10px;
            }
            
            .menu-card {
                padding: 25px 15px;
                max-width: 300px;
            }
            
            .menu-card-icon {
                width: 60px;
                height: 60px;
            }
            
            .menu-card-title {
                font-size: 14px;
            }
            
            .footer-text h2 {
                font-size: 14px;
            }
            
            .footer-text .subtitle {
                font-size: 11px;
            }
            
            .footer-text .description {
                font-size: 11px;
            }
            
            .footer-item {
                font-size: 12px;
            }
            
            .sidebar-header {
                padding: 10px 10px;
            }
            
            .menu-item a {
                padding: 8px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- buat menu burger -->
    <div class="sidebar">
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
            <li class="menu-item active">
                <a href="dashboard.php">
                    <img src="assets/gambar/icon/homescreen.png" class="menu-icon">
                    <span>Home</span>
                </a>
            </li>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="audit_log.php">
                    <img src="assets/gambar/icon/audit_log.png" class="menu-icon">
                    <span>Audit Log</span>
                </a>
            </li>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Letter Formatting</span>
                </a>
            </li>
            <?php endif; ?>
                        <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="approval.php">
                    <img src="assets/gambar/icon/approve.png" class="menu-icon">
                    <span>Approval</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="menu-item">
                <a href="kelola_user.php">
                    <img src="assets/gambar/icon/kelola_user.png" class="menu-icon">
                    <span>User Management</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="menu-item">
                <a href="kas_transaksi.php">
                    <img src="assets/gambar/icon/folderkas.png" class="menu-icon">
                    <span>Transaction</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="logout.php">
                    <img src="assets/gambar/icon/logout.png" class="menu-icon">
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- konten utama -->
    <div class="main-content">
        <!-- header -->
        <div class="header">
            <div class="header-left">
                <div class="welcome-text">Dashboard</div>
                <div class="saldo-kas-info">Saldo Kas Saat Ini: <strong>Rp <?php echo $saldo_kas_formatted; ?></strong></div>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($nama_lengkap, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                </div>
            </div>
        </div>
        
        <!-- area konten -->
        <div class="content-area">
            <div class="menu-grid">
                <div class="menu-row">
                    <a href="kas_masuk.php" class="menu-card">
                        <img src="assets/gambar/icon/folder.png" class="menu-card-icon">
                        <div class="menu-card-title">KAS MASUK</div>
                    </a>

                    <a href="kas_keluar.php" class="menu-card">
                        <img src="assets/gambar/icon/folder.png" class="menu-card-icon">
                        <div class="menu-card-title">KAS KELUAR</div>
                    </a>
                </div>

                <div class="menu-row">
                    <a href="stok_opname.php" class="menu-card">
                        <img src="assets/gambar/icon/folder.png" class="menu-card-icon">
                        <div class="menu-card-title">STOK OPNAME</div>
                    </a>

                    <a href="buku_kas.php" class="menu-card">
                        <img src="assets/gambar/icon/folder.png" class="menu-card-icon">
                        <div class="menu-card-title">BUKU KAS</div>
                    </a>
                </div>
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
    
</body>
</html>