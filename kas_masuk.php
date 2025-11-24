<?php
require_once 'config/conn_db.php';

check_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'] ?? 'Kasir';
// bagian filter tanggal
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_condition = '';

switch($filter) {
    case 'today':
        $date_condition = " AND DATE(tanggal_transaksi) = CURDATE()";
        break;
    case '7days':
        $date_condition = " AND DATE(tanggal_transaksi) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_condition = " AND MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())";
        break;
    default:
        $date_condition = '';
}

$success_message = '';
$edit_mode = false;
$edit_data = [];

// bagian tombol aksi edit
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_id = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_terima' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if ($edit_data) {
        $edit_mode = true;
    } else {
        $success_message = '<div class="alert alert-error">Data tidak ditemukan!</div>';
    }
}

// bagian tombol aksi simpan (insert/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kas'])) {
    $keterangan = clean_input($_POST['keterangan'] ?? '');
    $jumlah_raw = trim($_POST['jumlah'] ?? '0');
    
    $jumlah = str_replace(['.', ','], ['', '.'], $jumlah_raw);
    $jumlah = floatval($jumlah);
    
    if ($jumlah > 0) {
        
        if ($edit_mode && isset($_POST['edit_id'])) {
            // bagian tombol update
            $edit_id = intval($_POST['edit_id']);
            $stmt = mysqli_prepare($conn, "UPDATE transaksi SET nominal = ?, keterangan = ? WHERE id = ? AND jenis_transaksi = 'kas_terima'");
            mysqli_stmt_bind_param($stmt, 'dsi', $jumlah, $keterangan, $edit_id);
            
            if (mysqli_stmt_execute($stmt)) {
                log_audit($user_id, $username, "Update Kas Masuk #$edit_id: " . rupiah_fmt($jumlah));
                mysqli_stmt_close($stmt);
                header('Location: kas_masuk.php?success=2');
                exit();
            }
            mysqli_stmt_close($stmt);
            
        } else {
            // bagian tombol simpan
            
            // Generate nomor surat GLOBAL
            $nomor_data = get_next_nomor_surat('KT-KSK');
            $nomor_surat = $nomor_data['nomor'];
            
            $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (user_id, username, jenis_transaksi, nominal, keterangan, nomor_surat, tanggal_transaksi) VALUES (?, ?, 'kas_terima', ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'isdss', $user_id, $username, $jumlah, $keterangan, $nomor_surat);
            
            if (mysqli_stmt_execute($stmt)) {
                log_audit($user_id, $username, "Kas Masuk #$nomor_surat: " . rupiah_fmt($jumlah));
                mysqli_stmt_close($stmt);
                header('Location: kas_masuk.php?success=1');
                exit();
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $success_message = '<div class="alert alert-error">Jumlah kas harus lebih dari 0!</div>';
    }
}

// bagian tombol aksi delete
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $delete_id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_terima'");
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    
    if (mysqli_stmt_execute($stmt)) {
        log_audit($user_id, $username, "Hapus Kas Masuk #$delete_id");
        mysqli_stmt_close($stmt);
        header('Location: kas_masuk.php?success=3');
        exit();
    }
    mysqli_stmt_close($stmt);
}

// pesan kalo berhasil
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $success_message = '<div class="alert alert-success">✓ Data kas masuk berhasil disimpan!</div>';
            break;
        case '2':
            $success_message = '<div class="alert alert-success">✓ Data kas masuk berhasil diupdate!</div>';
            break;
        case '3':
            $success_message = '<div class="alert alert-success">✓ Data kas masuk berhasil dihapus!</div>';
            break;
    }
}

// Ambil data kas masuk
$data_kas = [];
$res = mysqli_query($conn, "SELECT * FROM transaksi WHERE jenis_transaksi = 'kas_terima' $date_condition ORDER BY tanggal_transaksi DESC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $data_kas[] = $r;
    }
    mysqli_free_result($res);
}

// ngambil nomor terakhir
$last_nomor = get_last_nomor_surat('KT-KSK');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Masuk - Sistem Kas Kebun</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        /* Sidebar */
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
        
        /* Main Content Wrapper */
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

        .header h1 {
            font-size: 22px;
            font-weight: bold;
        }
        
        .menu-burger {
            font-size: 26px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .menu-burger:hover {
            transform: scale(1.1);
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
        }
        
        /* Filter Section */
        .filter-container {
            max-width: 860px;
            margin: 20px auto 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .filter-wrapper {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .btn-filter {
            flex: none;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .alert { 
            max-width: 860px; 
            margin: 20px auto 0; 
            padding: 15px 20px; 
            border-radius: 8px; 
            font-size: 14px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            animation: slideDown 0.3s ease; 
        }
        @keyframes slideDown { 
            from { 
            opacity: 0; 
            transform: translateY(-20px); 
        } to {
             opacity: 1; 
             transform: translateY(0); 
            } 
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
        
        .container { 
            width: 90%; 
            max-width: 900px; 
            margin: 0 auto 40px; 
            background-color: white; 
            padding: 40px; 
            border-radius: 14px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.12); 
            flex: 1; 
        }
        
        .form-group { 
            margin-bottom: 25px; 
        }
        .form-group label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            font-size: 15px; 
        }
        .form-group input { 
            width: 100%; 
            padding: 13px 16px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            background-color: #f2f2f2; 
            font-size: 14px; 
        }
        .form-group input:focus { 
            background-color: white; 
            outline: none; 
            border-color: #009844; 
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

        .btn-sm { 
            padding: 6px 12px; 
            font-size: 12px; 
            border-radius: 4px; 
            flex: none; 
        }

        .btn-pdf { 
            background-color: #009844; 
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-pdf:hover { 
            background-color: #017033; 
        }

        .btn-edit { 
            background-color: #009844; 
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-edit:hover { 
            background-color: #017033; 
        }

        .btn-delete { 
            background-color: #e0e0e0;
            color: #000;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-delete:hover { 
            background-color: #d0d0d0; 
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
        
        .table-wrapper { 
            overflow-x: auto; 
            margin-top: 20px; 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        thead { 
            background: #f2f2f2; 
        }

        th { 
            padding: 12px 10px; 
            text-align: center; 
            font-weight: 600; 
            border: 1px solid #ddd; 
        }

        td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            font-size: 13px; 
        }

        tbody tr:hover { 
            background: #f9f9f9; 
        }
        
        .nomor-info { 
            margin: 12px 0; 
            font-weight: 700; 
            color: #333; 
        }
        
        .ksk-footer { 
            width: 100%; 
            padding: 30px 40px; 
            background: linear-gradient(to right, #00984489, #003216DB); 
            color: #ffffff; 
            border-top: 3px solid #333; 
            margin-top: auto; 
        }

        .footer-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: start; 
            gap: 30px; 
        }

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

        .footer-right { 
            width: 40%; 
            display: flex; 
            flex-direction: 
            column; 
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
        
        .edit-badge { 
            background: #ffc107; 
            color: #000; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 11px; 
            margin-left: 10px; 
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
        }
        
        @media(max-width:768px){
            .container { 
                padding: 25px 20px; 
            }
            .button-group { 
                flex-direction: column; 
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
            
            .filter-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-wrapper .btn-filter {
                width: 100%;
                justify-content: center;
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
            <li class="menu-item">
                <a href="logout.php">
                    <img src="assets/gambar/icon/logout.png" class="menu-icon">
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>KAS MASUK <?php if($edit_mode) echo '<span class="edit-badge">MODE EDIT</span>'; ?></h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($nama_lengkap, 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="content-wrapper">
            <div class="filter-container">
                <div class="filter-wrapper">
                    <span class="filter-label">Filter:</span>
                    <a href="kas_masuk.php?filter=today<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                       class="btn btn-filter btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-day"></i> Hari Ini
                    </a>
                    <a href="kas_masuk.php?filter=7days<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                       class="btn btn-filter btn-sm <?php echo $filter === '7days' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-week"></i> 7 Hari Terakhir
                    </a>
                    <a href="kas_masuk.php?filter=month<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                       class="btn btn-filter btn-sm <?php echo $filter === 'month' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-alt"></i> Bulan Ini
                    </a>
                    <a href="kas_masuk.php<?php echo $edit_mode ? '?edit='.$edit_data['id'] : ''; ?>" 
                       class="btn btn-filter btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-list"></i> Semua
                    </a>
                </div>
            </div>

            <?php if ($success_message): ?>
                <?php echo $success_message; ?>
            <?php endif; ?>

            <div class="container">
                <form method="POST">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Masukkan keterangan" value="<?php echo htmlspecialchars($edit_data['keterangan'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="text" name="jumlah" placeholder="Masukkan jumlah kas masuk" value="<?php echo $edit_mode ? number_format($edit_data['nominal'], 0, ',', '.') : ''; ?>" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="simpan_kas" class="btn btn-primary">
                            <?php echo $edit_mode ? 'Update' : 'Simpan'; ?> Kas Masuk
                        </button>
                        <?php if ($edit_mode): ?>
                            <a href="kas_masuk.php" class="btn btn-secondary">
                                Batal Edit
                            </a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Kembali
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="form-group">
                    <label>Daftar Kas Masuk</label>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:50px;">NO</th>
                                    <th style="width:130px;">NOMOR SURAT</th>
                                    <th style="width:130px;">TANGGAL</th>
                                    <th>KETERANGAN</th>
                                    <th style="width:130px;">JUMLAH</th>
                                    <th style="width:220px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($data_kas)): ?>
                                <?php $i = 1; foreach ($data_kas as $row): ?>
                                    <tr>
                                        <td style="text-align:center;"><?php echo $i; ?></td>
                                        <td style="text-align:center;"><?php echo htmlspecialchars($row['nomor_surat'] ?? '-'); ?></td>
                                        <td style="text-align:center;"><?php echo date('d-M-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        <td style="text-align:right;">Rp. <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                                        <td style="text-align:center;">
                                            <div class="action-buttons">
                                                <a href="kas_masuk.php?edit=<?php echo $row['id']; ?>" class="btn btn-edit btn-sm" title="Edit">
                                                    Edit
                                                </a>
                                                <a href="kas_masuk.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                                    Delete
                                                </a>
                                                <a href="export_pdf.php?type=kas_masuk&id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-pdf btn-sm" title="Export PDF">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php $i++; endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:20px; color:#999;">
                            Belum ada data kas masuk
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <footer class="ksk-footer">
        <div class="footer-content">
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

    <script>
        // sidebar script
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
