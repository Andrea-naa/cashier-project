<?php
// koneksi ke database
require_once 'config/conn_db.php';

// cek login dan serta ngambil role user
check_login();
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = get_user_role($user_id);

if (stripos($role, 'Administrator') === false) {
    die("Akses ditolak. Hanya untuk Administrator.");
}

$success_message = '';
$error_message = '';

// ambil data konfigurasi saat ini
$query = "SELECT * FROM konfigurasi LIMIT 1";
$result = mysqli_query($conn, $query);
$config = mysqli_fetch_assoc($result);

if (!$config) {
    // Kalau belum ada, buat default
    mysqli_query($conn, "INSERT INTO konfigurasi (nama_perusahaan, kode_perusahaan, alamat, kota, telepon, email) VALUES ('PT. Kalimantan Sawit Kusuma', 'KSK', 'Jl. W.R Supratman No. 42 Pontianak', 'Pontianak', '0561-733035', 'info@ksk.com')");
    $result = mysqli_query($conn, $query);
    $config = mysqli_fetch_assoc($result);
}

// proses update nomor suratnya 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $kode_perusahaan = strtoupper(clean_input($_POST['kode_perusahaan']));
    $nama_perusahaan = clean_input($_POST['nama_perusahaan']);
    $alamat = clean_input($_POST['alamat']);
    $kota = clean_input($_POST['kota']);
    $telepon = clean_input($_POST['telepon']);
    $email = clean_input($_POST['email']);
    $ttd_jabatan_1 = clean_input($_POST['ttd_jabatan_1']);
    $ttd_jabatan_2 = clean_input($_POST['ttd_jabatan_2']);
    $ttd_jabatan_3 = clean_input($_POST['ttd_jabatan_3']);
    $ttd_jabatan_4 = clean_input($_POST['ttd_jabatan_4']);
    
    // validasi kode perusahaan (hanya huruf, 2-10 karakter)
    if (!preg_match('/^[A-Z]{2,10}$/', $kode_perusahaan)) {
        $error_message = 'Kode perusahaan harus terdiri dari 2-10 huruf kapital tanpa spasi atau karakter khusus!';
    } else {
        $stmt = mysqli_prepare($conn, 
            "UPDATE konfigurasi SET 
                kode_perusahaan = ?,
                nama_perusahaan = ?,
                alamat = ?,
                kota = ?,
                telepon = ?,
                email = ?,
                ttd_jabatan_1 = ?,
                ttd_jabatan_2 = ?,
                ttd_jabatan_3 = ?,
                ttd_jabatan_4 = ?
            WHERE id = ?"
        );
        
        mysqli_stmt_bind_param($stmt, 'ssssssssssi', 
            $kode_perusahaan, $nama_perusahaan, $alamat, $kota, $telepon, $email,
            $ttd_jabatan_1, $ttd_jabatan_2, $ttd_jabatan_3, $ttd_jabatan_4,
            $config['id']
        );
        
        if (mysqli_stmt_execute($stmt)) {
            log_audit($user_id, $username, "Update Konfigurasi Nomor Surat: Kode Perusahaan = $kode_perusahaan");
            $success_message = 'Konfigurasi berhasil diupdate!';
            
            $result = mysqli_query($conn, $query);
            $config = mysqli_fetch_assoc($result);
        } else {
            $error_message = 'Gagal mengupdate konfigurasi!';
        }
        
        mysqli_stmt_close($stmt);
    }
}

//  contoh nomor surat
$kode = $config['kode_perusahaan'] ?? 'KSK';
$tahun = date('Y');
$bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
$bulan = date('n');

$contoh_kas_masuk = sprintf('001/KT-%s/%s/%04d', $kode, $bulan_romawi[$bulan], $tahun);
$contoh_kas_keluar = sprintf('001/KK-%s/%s/%04d', $kode, $bulan_romawi[$bulan], $tahun);
$contoh_stok_opname = sprintf('001/STOK-%s/%s/%04d', $kode, $bulan_romawi[$bulan], $tahun);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Nomor Surat - Sistem Kas Kebun</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #e8f0e8;
            min-height: 100vh;
            display: flex;
        }

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

        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

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

        .burger-menu {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .burger-menu:hover {
            background: rgba(255, 255, 255, 0.1);
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

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 40px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            width: 100%;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #2d7a3e;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 14px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .info-box h3 {
            color: #1565c0;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box .example {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin: 8px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #333;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #2d7a3e;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2d7a3e;
            box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group .help-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

         .button-group { 
            display: flex; 
            gap: 15px; 
            margin-top: 10px; 
            margin-bottom: 30px; 
        }
        .btn { 
            padding: 13px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            border: none; 
            transition: 0.25s; 
            flex: 1; 
            font-size: 14px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
            text-decoration: none; 
        }

        .btn-primary { 
            background-color: #009844; 
            color: white; 
        }

        .btn-primary:hover { 
            background-color: #017033FF; 
        }

        .btn-secondary { 
            background-color: #dcdcdc; 
            color: #333; 
        }

        .btn-secondary:hover { 
            background-color: #c7c7c7; 
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
            }

            .container {
                padding: 25px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
                <a href="audit_log.php">
                    <img src="assets/gambar/icon/audit_log.png" class="menu-icon">
                    <span>Audit Log</span>
                </a>
            </li>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item active">
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
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <button class="burger-menu" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>PENGATURAN NOMOR SURAT</h1>
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
                <div class="page-header">
                    <h2>
                        <i class="fas fa-file-alt"></i>
                        Pengaturan Format Nomor Surat
                    </h2>
                    <p>Atur kode perusahaan dan informasi lainnya untuk format nomor surat</p>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h3><i class="fas fa-info-circle"></i> Contoh Format Nomor Surat</h3>
                    <div class="example">
                        <strong>Kas Masuk:</strong> <?php echo $contoh_kas_masuk; ?>
                    </div>
                    <div class="example">
                        <strong>Kas Keluar:</strong> <?php echo $contoh_kas_keluar; ?>
                    </div>
                    <div class="example">
                        <strong>Stok Opname:</strong> <?php echo $contoh_stok_opname; ?>
                    </div>
                </div>

                <form method="POST" action="">
                    <div class="form-section">
                        <h3>Informasi Perusahaan</h3>
                        
                        <div class="form-group">
                            <label><i class=""></i> Kode Perusahaan *</label>
                            <input type="text" name="kode_perusahaan" 
                                   value="<?php echo htmlspecialchars($config['kode_perusahaan'] ?? 'KSK'); ?>" 
                                   required maxlength="10" pattern="[A-Z]{2,10}"
                                   style="text-transform: uppercase;">
                            <span class="help-text">Hanya huruf kapital, 2-10 karakter (contoh: KSK, MSL, FKK)</span>
                        </div>

                        <div class="form-group">
                            <label><i class=""></i> Nama Perusahaan *</label>
                            <input type="text" name="nama_perusahaan" 
                                   value="<?php echo htmlspecialchars($config['nama_perusahaan'] ?? ''); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label><i class=""></i> Alamat</label>
                            <textarea name="alamat"><?php echo htmlspecialchars($config['alamat'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label><i class=""></i> Kota</label>
                            <input type="text" name="kota" 
                                   value="<?php echo htmlspecialchars($config['kota'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label><i class=""></i> Telepon</label>
                            <input type="text" name="telepon" 
                                   value="<?php echo htmlspecialchars($config['telepon'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label><i class=""></i> Email</label>
                            <input type="email" name="email" 
                                   value="<?php echo htmlspecialchars($config['email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Jabatan Penandatangan</h3>
                        
                        <div class="form-group">
                            <label>Jabatan 1 (Penerima)</label>
                            <input type="text" name="ttd_jabatan_1" 
                                   value="<?php echo htmlspecialchars($config['ttd_jabatan_1'] ?? 'Finance Dept Head'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Jabatan 2 (Pemeriksa)</label>
                            <input type="text" name="ttd_jabatan_2" 
                                   value="<?php echo htmlspecialchars($config['ttd_jabatan_2'] ?? 'Finance Sub Dept Head'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Jabatan 3 (Pemeriksa Lanjutan)</label>
                            <input type="text" name="ttd_jabatan_3" 
                                   value="<?php echo htmlspecialchars($config['ttd_jabatan_3'] ?? 'Finance Div Head'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Jabatan 4 (Persetujuan)</label>
                            <input type="text" name="ttd_jabatan_4" 
                                   value="<?php echo htmlspecialchars($config['ttd_jabatan_4'] ?? 'Cashier'); ?>">
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_config" class="btn btn-primary">
                            <i class=""></i>
                            Simpan Perubahan
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class=""></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // input kode perusahaan kapita secara otomatis
        const kodeInput = document.querySelector('input[name="kode_perusahaan"]');
        if (kodeInput) {
            kodeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // close sidebar when clicking overlay
        document.getElementById('sidebarOverlay').addEventListener('click', toggleSidebar);
    </script>
</body>
</html>