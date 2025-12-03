<?php
// koneksi ke databse
require_once 'config/conn_db.php';

check_admin();
// ngambil sesi serta role user
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'] ?? 'Administrator';

$success_message = '';

// filter untuk jenis transaksi
$jenis_filter = isset($_GET['jenis']) ? $_GET['jenis'] : 'all';
$jenis_condition = '';
if ($jenis_filter == 'kas_terima') {
    $jenis_condition = " AND t.jenis_transaksi = 'kas_terima'";
} elseif ($jenis_filter == 'kas_keluar') {
    $jenis_condition = " AND t.jenis_transaksi = 'kas_keluar'";
}

// bagian approve sekaligus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_approve'])){
    $ids = $_POST['approve_ids'] ?? [];
    $table = $_POST['table_name'] ?? '';

    if (!empty($ids) && in_array($table, ['transaksi', 'stok_opname'])){
        $approved_count = 0;
        foreach ($ids as $id) {
            if (approve_data($table, intval($id), $user_id, $username)){
                $approved_count++;
            }
        }
        $success_message = "<div class='alert alert-success'>Berhasil approve $approved_count data!</div>";
    }
}


// bagian dis approve sekaligus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_reject'])){
    $ids = $_POST['reject_ids'] ?? [];
    $table = $_POST['table_name'] ?? '';
    $reason = clean_input($_POST['reject_reason'] ?? 'Tidak ada alasan');

    if (!empty($ids) && in_array($table, ['transaksi', 'stok_opname'])){
        $rejected_count = 0;
        foreach ($ids as $id) {
            if (reject_data($table, intval($id), $user_id, $username, $reason)){
                $rejected_count++;
            }
        }
        $success_message = "<div class='alert alert-warning'>Berhasil Reject $rejected_count data!</div>";
    }
}

// bagian approve satu satu
if (isset($_GET['approve']) && isset($_GET['table'])) {
    $id = intval($_GET['approve']);
    $table = $_GET['table'];
    
    if (in_array($table, ['transaksi', 'stok_opname'])) {
        if (approve_data($table, $id, $user_id, $username)) {
            $success_message = "<div class='alert alert-success'>✓ Data berhasil di-approve!</div>";
        }
    }
}

// bagian dis approve satu satu
if (isset($_GET['reject']) && isset($_GET['table'])) {
    $id = intval($_GET['reject']);
    $table = $_GET['table'];
    $reason = clean_input($_GET['reason'] ?? 'Ditolak oleh admin');
    
    if (in_array($table, ['transaksi', 'stok_opname'])) {
        if (reject_data($table, $id, $user_id, $username, $reason)) {
            $success_message = "<div class='alert alert-warning'>⚠ Data berhasil di-reject!</div>";
        }
    }
}

$pending_transaksi = [];
$pending_stok = [];

$res_transaksi = mysqli_query($conn, "SELECT t.*, u.nama_lengkap as created_by_name FROM transaksi t LEFT JOIN users u ON t.user_id = u.id WHERE t.is_approved = 0 AND t.is_rejected = 0 $jenis_condition ORDER BY t.tanggal_transaksi DESC");

if ($res_transaksi) {
    while ($r = mysqli_fetch_assoc($res_transaksi)) {
        $pending_transaksi[] = $r;
    }
}

$res_stok = mysqli_query($conn, "SELECT s.*, u.nama_lengkap as created_by_name FROM stok_opname s LEFT JOIN users u ON s.user_id = u.id WHERE s.is_approved = 0 AND s.is_rejected = 0 ORDER BY s.tanggal_opname DESC");

if ($res_stok) {
    while ($r = mysqli_fetch_assoc($res_stok)) {
        $pending_stok[] = $r;
    }
}

$total_pending = count($pending_transaksi) + count($pending_stok)

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Data - Sistem Kas Kebun</title>
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
    
    .stats-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
        width: 100%;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 153, 68, 0.15);
    }

    .stat-info h3 {
        font-size: 32px;
        color: #009844;
        font-weight: 700;
        margin-bottom: 6px;
        line-height: 1;
    }

    .stat-info p {
        color: #666;
        font-size: 13px;
        line-height: 1.3;
    }
    
    .alert { 
        max-width: 1200px; 
        margin: 20px auto 0; 
        padding: 15px 20px; 
        border-radius: 8px; 
        font-size: 14px; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        animation: slideDown 0.3s ease; 
        width: 90%;
    }
    
    @keyframes slideDown { 
        from { 
            opacity: 0; 
            transform: translateY(-20px); 
        } 
        to {
            opacity: 1; 
            transform: translateY(0); 
        } 
    }
    
    .alert-success { 
        background: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb; 
    }
    
    .alert-warning { 
        background: #fff3cd; 
        color: #856404; 
        border: 1px solid #ffeaa7; 
    }
    
    .alert-error { 
        background: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb; 
    }
    
    .container { 
        width: 90%; 
        max-width: 1200px; 
        margin: 20px auto; 
        background-color: white; 
        padding: 30px; 
        border-radius: 14px; 
        box-shadow: 0 3px 10px rgba(0,0,0,0.12); 
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #e8e8e8;
    }

    .section-title {
        font-size: 18px;
        color: #009844;
        font-weight: 600;
    }
    
    .btn { 
        padding: 10px 18px; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 600; 
        border: none; 
        transition: 0.25s; 
        font-size: 14px; 
        display: inline-flex; 
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
        background-color: #017033; 
    }

    .btn-success { 
        background-color: #009844; 
        color: white; 
    }

    .btn-success:hover { 
        background-color: #017033; 
    }

    .btn-secondary {
        background-color: #dcdcdc;
        color: #666;
        border: 1px solid #ddd;
    }

    .btn-secondary:hover {
        background-color: #c7c7c7;
        color: #333;
        border-color: #bbb;
    }

    .btn-warning {
        background-color: #dc3545;
        color: white;
    }

    .btn-warning:hover {
        background-color: #c82333;
    }

    /* Action Bar Layout */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Button Filter Styles */
    .btn-filter {
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 6px;
        transition: all 0.3s ease;
        border: 1px solid #ddd;
    }

    .btn-filter.btn-primary {
        background-color: #009844;
        color: white;
        border-color: #009844;
    }

    .btn-filter.btn-primary:hover {
        background-color: #017033;
        border-color: #017033;
    }

    .btn-filter.btn-secondary {
        background: white;
        color: #666;
        border: 1px solid #ddd;
    }

    .btn-filter.btn-secondary:hover {
        background: #f5f5f5;
        color: #333;
        border-color: #bbb;
    }

    /* Filter Info Styling */
    .filter-info {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: white;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .modal h3 {
        color: #009844;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .modal textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        margin-bottom: 20px;
        resize: vertical;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .table-wrapper { 
        overflow-x: auto; 
        margin-top: 15px; 
    }

    table { 
        width: 100%; 
        border-collapse: collapse; 
        background: white;
        border: 1px solid #ddd;
    }

    thead { 
        background: #DDDDDD; 
    }

    th { 
        padding: 12px 10px; 
        text-align: center; 
        font-weight: 600; 
        border: 1px solid #ddd; 
        font-size: 13px;
        color: #333;
    }

    td { 
        padding: 10px; 
        border: 1px solid #ddd; 
        font-size: 13px; 
        color: #424242;
    }

    tbody tr:hover { 
        background: #f9f9f9; 
    }
    
    .checkbox-col {
        width: 45px;
        text-align: center;
    }

    input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #009844;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
    }

    .badge-masuk {
        background: #c8e6c9;
        color: #009844;
    }

    .badge-keluar {
        background: #ffcdd2;
        color: #c62828;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-state p {
        font-size: 13px;
        color: #757575;
        margin-top: 10px;
    }

    /* Text Color untuk Selisih */
    .text-danger {
        color: #c62828;
        font-weight: 600;
    }

    .text-success {
        color: #009844;
        font-weight: 600;
    }
    
    /* Responsive Design */
    @media(max-width: 768px) {
        .container { 
            padding: 20px; 
            width: 95%;
        }
        
        .sidebar {
            width: 100%;
            left: -100%;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 0 15px;
        }
        
        .stat-card {
            padding: 16px 20px;
        }
        
        .stat-info h3 {
            font-size: 28px;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
            padding: 12px;
        }
        
        .filter-group,
        .action-buttons {
            width: 100%;
            justify-content: center;
        }
        
        .btn-filter,
        .action-buttons .btn {
            flex: 1;
            min-width: 100px;
        }

        .filter-info {
            width: 100%;
            justify-content: center;
            text-align: center;
        }
        
        .filter-info span {
            font-size: 13px;
        }

        .section-header {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .alert {
            width: 95%;
            padding: 12px 15px;
        }
    }

    @media(max-width: 480px) {
        .filter-group {
            flex-direction: column;
        }
        
        .btn-filter {
            width: 100%;
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-buttons .btn {
            width: 100%;
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
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Pengaturan Nomor Surat</span>
                </a>
            </li>
            <li class="menu-item active">
                <a href="approval.php">
                    <i class="fas fa-check-circle menu-icon"></i>
                    <span>Approval</span>
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
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>APPROVAL DATA</h1>
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
            <!-- kontener untuk jumlah pending -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo $total_pending; ?></h3>
                        <p>Total Pending Approval</p>
                    </div>
                </div>
            
                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo count($pending_transaksi); ?></h3>
                        <p>Transaksi Pending Approval</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3><?php echo count($pending_stok); ?></h3>
                        <p>Stok Opname Pending Approval</p>
                    </div>
                </div>
            </div>

            <?php if ($success_message): ?>
                <?php echo $success_message; ?>
            <?php endif; ?>

           <!-- bagian transaksi/kasmasuk dan kas keluar -->
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Transaksi Yang Menunggu Di Approve</h2>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </div>

                <?php if (!empty($pending_transaksi)): ?>
                    <form method="POST" id="formTransaksi">
                        <input type="hidden" name="table_name" value="transaksi">
            
            <!-- tombol aksi -->
            <div class="action-bar">
                <div class="filter-group">
                    <a href="approval.php?jenis=all" class="btn btn-filter <?php echo $jenis_filter == 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-list"></i> Semua
                    </a>
                    <a href="approval.php?jenis=kas_terima" class="btn btn-filter <?php echo $jenis_filter == 'kas_terima' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class=""></i> Kas Masuk
                    </a>
                    <a href="approval.php?jenis=kas_keluar" class="btn btn-filter <?php echo $jenis_filter == 'kas_keluar' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class=""></i> Kas Keluar
                    </a>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" name="bulk_approve" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-warning" onclick="openRejectModal('transaksi')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="selectAllTransaksi" 
                                       onchange="toggleAll('transaksi', this.checked)">
                            </th>
                            <th>Nomor Surat</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_transaksi as $row): ?>
                            <tr>
                                <td class="checkbox-col">
                                    <input type="checkbox" name="approve_ids[]" 
                                           value="<?php echo $row['id']; ?>" 
                                           class="checkbox-transaksi">
                                </td>
                                <td style="text-align:center;"><?php echo htmlspecialchars($row['nomor_surat']); ?></td>
                                <td style="text-align:center;"><?php echo date('d-M-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td style="text-align:center;">
                                    <?php if ($row['jenis_transaksi'] === 'kas_terima'): ?>
                                        <span class="badge badge-masuk">Kas Masuk</span>
                                    <?php else: ?>
                                        <span class="badge badge-keluar">Kas Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                <td style="text-align: right;">
                                    Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['created_by_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php else: ?>

        <div class="action-bar">
            <div class="filter-group">
                <a href="approval.php?jenis=all" class="btn btn-filter <?php echo $jenis_filter == 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-list"></i> Semua
                </a>
                <a href="approval.php?jenis=kas_terima" class="btn btn-filter <?php echo $jenis_filter == 'kas_terima' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-arrow-down"></i> Kas Masuk
                </a>
                <a href="approval.php?jenis=kas_keluar" class="btn btn-filter <?php echo $jenis_filter == 'kas_keluar' ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-arrow-up"></i> Kas Keluar
                </a>
            </div>
        </div>
        
        <div class="empty-state">
            <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
            <p>Tidak ada transaksi yang menunggu approval</p>
        </div>
    <?php endif; ?>
</div>

<!-- bagian stok opname -->
<div class="container">
    <div class="section-header">
        <h2 class="section-title">Stok Opname Menunggu Approval</h2>
    </div>
    
    <?php if (!empty($pending_stok)): ?>
        <form method="POST" id="formStok">
            <input type="hidden" name="table_name" value="stok_opname">
            
            <!-- tombol aksi -->
            <div class="action-bar">
                <div class="filter-info">
                    <i class="fas fa-info-circle" style="color: #009844;"></i>
                    <span style="color: #666; font-size: 14px;">
                        Pilih data yang ingin di-approve atau di-reject
                    </span>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" name="bulk_approve" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-warning" onclick="openRejectModal('stok')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="checkbox-col">
                                <input type="checkbox" id="selectAllStok" 
                                       onchange="toggleAll('stok', this.checked)">
                            </th>
                            <th>Nomor Surat</th>
                            <th>Tanggal</th>
                            <th>Fisik Total</th>
                            <th>Saldo Sistem</th>
                            <th>Selisih</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_stok as $row): ?>
                            <tr>
                                <td class="checkbox-col">
                                    <input type="checkbox" name="approve_ids[]" 
                                           value="<?php echo $row['id']; ?>" 
                                           class="checkbox-stok">
                                </td>
                                <td style="text-align:center;"><?php echo htmlspecialchars($row['nomor_surat']); ?></td>
                                <td style="text-align:center;"><?php echo date('d-M-Y', strtotime($row['tanggal_opname'])); ?></td>
                                <td style="text-align: right;">
                                    Rp <?php echo number_format($row['fisik_total'], 0, ',', '.'); ?>
                                </td>
                                <td style="text-align: right;">
                                    Rp <?php echo number_format($row['saldo_sistem'], 0, ',', '.'); ?>
                                </td>
                                <td style="text-align: right;">
                                    <span class="<?php echo $row['selisih'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                        Rp <?php echo number_format($row['selisih'], 0, ',', '.'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['created_by_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-check" style="font-size: 48px; color: #ccc; margin-bottom: 10px;"></i>
            <p>Tidak ada stok opname yang menunggu approval</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Reject -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal">
        <form method="POST" id="rejectForm">
            <h3><i class="fas fa-times-circle"></i> Reject Data</h3>
            <input type="hidden" name="table_name" id="rejectTableName">
            <div id="rejectIdsContainer"></div>
            
            <label for="reject_reason">Alasan Ditolak:</label>
            <textarea name="reject_reason" id="reject_reason" rows="4" 
                      placeholder="Masukkan alasan data ini ditolak.." required></textarea>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" name="bulk_reject" class="btn btn-warning">
                    <i class="fas fa-check"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>
                
        </body>

        <script>
// untuk chcekbox
function toggleAll(type, checked) {
    const checkboxes = document.querySelectorAll('.checkbox-' + type);
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
}

// checkbox tapi select semua datanya 
function checkAllSelected(type) {
    const checkboxes = document.querySelectorAll('.checkbox-' + type);
    const selectAllCheckbox = document.getElementById('selectAll' + type.charAt(0).toUpperCase() + type.slice(1));
    
    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
    const someChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
    
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }
}

// fungsi model
function openRejectModal(type) {
    const formId = type === 'transaksi' ? 'formTransaksi' : 'formStok';
    const form = document.getElementById(formId);
    const checkboxes = form.querySelectorAll('input[name="approve_ids[]"]:checked');

    if (checkboxes.length === 0) {
        alert('Pilih minimal satu data untuk di tolak')
    }

    // set nama tabel
    const tableName = type === 'transaksi' ? 'transaksi' : 'stok_opname';
    document.getElementById('rejectTableName').value = tableName;

    // menghapus dan mengisi id dari penolakan
    const container = document.getElementById('rejectIdsContainer');
    container.innerHTML = '';

    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'reject_ids[]';
        input.value = checkbox.value;
        container.appendChild(input);
    });

    // tampilkan model
    document.getElementById('rejectModal').classList.add('active');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
    document.getElementById('reject_reaseon').value = '';
    document.getElementById('rejectIdsContainer').innerHTML = '';
}

// script untuk event listener untuk checkbox berdasarkan tipe
document.addEventListener('DOMContentLoaded', function() {
    // Untuk transaksi
    const checkboxesTransaksi = document.querySelectorAll('.checkbox-transaksi');
    checkboxesTransaksi.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            checkAllSelected('transaksi');
        });
    });
    
    // Untuk stok opname
    const checkboxesStok = document.querySelectorAll('.checkbox-stok');
    checkboxesStok.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            checkAllSelected('stok');
        });
    });
    
    // menu burger
    const menuBurger = document.getElementById('menuBurger');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    menuBurger.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    });
    
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    });
});
</script>