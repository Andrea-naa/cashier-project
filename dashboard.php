<?php
session_start();

// cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Koneksi ke database
require_once 'config/conn_db.php';

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

// Ambil role user dari session (default: Kasir)
$role = $_SESSION['role'] ?? 'Kasir';
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
        /* Collapsed sidebar for burger toggle */
        .sidebar.collapsed {
            width: 64px;
            overflow: hidden;
        }
        .sidebar.collapsed .menu-title,
        .sidebar.collapsed .menu-item span:nth-child(2),
        .sidebar.collapsed .company-title {
            display: none;
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
            color: #1b5e20;
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
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
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
        
        /* Content Area */
        .content-area {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center; /* center the menu grid horizontally */
        }

        .menu-grid {
            display: flex;
            flex-direction: column;
            gap: 40px;
            width: 100%;
            max-width: 980px; /* control overall width */
            margin-top: 10px; /* small gap under header */
        }

        .menu-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 60px; /* larger gap between the two columns */
            justify-items: center; /* center cards in each column */
            align-items: start;
        }
        
        .menu-card {
            background: linear-gradient(to top, #009844, #00320E);
            border-radius: 15px;
            padding: 36px 28px;
            width: 260px; /* fixed width to control layout like example */
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
            /* border: 3px solid rgba(255,255,255,0.45); */
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
        
        /* Footer */
        .footer {
            background: linear-gradient(180deg, #66bb6a 0%, #1b5e20 100%);
            padding: 30px 40px;
            color: #e8f5e9;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 30px;
        }
        
        .footer-left {
            flex: 1;
            max-width: 500px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .footer-logo img {
            width: 60px;
            height: 60px;
            background: white;
            padding: 8px;
            border-radius: 10px;
        }
        
        .footer-company-name {
            font-size: 16px;
            font-weight: bold;
            color: #e8f5e9;
        }
        
        .footer-tagline {
            font-size: 11px;
            color: #dfeee0;
        }
        
        .footer-description {
            font-size: 12px;
            line-height: 1.6;
            color: #e8f5e9;
            text-align: justify;
        }
        
        .footer-right {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .footer-contact {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #1b5e20;
        }
        
        .contact-icon {
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 15px 0;
            }
            
            .menu-list {
                display: flex;
                overflow-x: auto;
                padding: 10px 0;
            }
            
            .menu-item {
                margin: 0 5px;
                white-space: nowrap;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .footer-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <li class="menu-item">
                <a href="kelola_user.php">
                    <img src="assets/gambar/icon/kelola_user.png" class="menu-icon">
                    <span>Kelola User</span>
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
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="welcome-text">SELAMAT DATANG!</div>
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
        
        <!-- Content Area -->
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
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <div class="footer-logo">
                        <img src="assets/gambar/logoksk.jpg" alt="KSK Logo"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect width=%2260%22 height=%2260%22 fill=%22%232e7d32%22 rx=%2210%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2224%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
                        <div>
                            <div class="footer-company-name">KALIMANTAN SAWIT KUSUMA GROUP</div>
                            <div class="footer-tagline">Oil Palm Plantation & Industries</div>
                        </div>
                    </div>
                    <p class="footer-description">
                        Kalimantan Sawit Kusuma (KSK) adalah sebuah grup perusahaan yang memiliki 
                        beberapa perusahaan afiliasi yang bergerak di berbagai bidang usaha, yaitu 
                        perkebunan kelapa sawit dan hortikultura, kontraktor dan alat berat dan 
                        pembangunan perkebunan serta jasa transportasi laut.
                    </p>
                </div>
                
                <div class="footer-right">
                    <div class="footer-contact">
                        <span class="contact-icon">🌐</span>
                        <span>kskgroup.co.id</span>
                    </div>
                    <div class="footer-contact">
                        <span class="contact-icon">📞</span>
                        <div>
                            <div>T: (+62 561) 733 035 (hunting)</div>
                            <div>F: (+62 561) 733 014</div>
                        </div>
                    </div>
                    <div class="footer-contact">
                        <span class="contact-icon">📍</span>
                        <div>
                            <div>W.R Supratman No. 42 Pontianak,</div>
                            <div>Kalimantan Barat 78122</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>