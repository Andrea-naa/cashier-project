<?php
// koneksi ke database
require_once 'config/conn_db.php';

// Pastikan login 
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = 'Kasir';
$resr = mysqli_query($conn, "SELECT role FROM users WHERE id='" . intval($current_user_id) . "' LIMIT 1");
if ($resr && mysqli_num_rows($resr) > 0) {
    $rr = mysqli_fetch_assoc($resr);
    if (!empty($rr['role'])) $role = $rr['role'];
}

if (stripos($role, 'Administrator') === false) {
    echo "Akses ditolak. Hanya untuk admin.";
    exit();
}

// ngambil filter dari query string
$username_filter = isset($_GET['username']) ? clean_input($_GET['username']) : '';
$actionq = isset($_GET['action_q']) ? clean_input($_GET['action_q']) : '';
$date_from = isset($_GET['date_from']) ? clean_input($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? clean_input($_GET['date_to']) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

$conds = [];
if ($username_filter !== '') $conds[] = "username LIKE '%" . $conn->real_escape_string($username_filter) . "%'";
if ($actionq !== '') $conds[] = "action LIKE '%" . $conn->real_escape_string($actionq) . "%'";
if ($date_from !== '') $conds[] = "timestamp >= '" . $conn->real_escape_string($date_from) . " 00:00:00'";
if ($date_to !== '') $conds[] = "timestamp <= '" . $conn->real_escape_string($date_to) . " 23:59:59'";

$where = '';
if (count($conds) > 0) $where = 'WHERE ' . implode(' AND ', $conds);

// hitung total
$countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM audit_log $where");
$total = 0;
if ($countRes) {
    $r = mysqli_fetch_assoc($countRes);
    $total = intval($r['total']);
}

$offset = ($page - 1) * $perPage;

$sql = "SELECT * FROM audit_log $where ORDER BY timestamp DESC LIMIT $offset, $perPage";
$res = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Audit Log - Sistem Kas Kebun</title>
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
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
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

        .filter-section {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .filter-section h3 {
            color: #2d7a3e;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 13px;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-group input {
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2d7a3e;
            box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-primary:hover {
            background-color: #017033FF;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #dcdcdc;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #c7c7c7;
        }

        .btn-success {
            background-color: #009844;
            color: white;
        }

        .btn-success:hover {
            background-color: #017033FF;
            transform: translateY(-2px);
        }

        .info-text {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 16px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .table-section {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        thead th {
            background: #009844;
            color: white;
            padding: 14px 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #f1f5f4;
            color: #475057;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f7fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .pagination {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-link {
            padding: 8px 14px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: white;
            text-decoration: none;
            color: #374151;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .page-link:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .page-link.active {
            background: #009844;
            color: white;
            border-color: #009844;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
            }

            .header h1 {
                font-size: 18px;
            }

            .user-details {
                display: none;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .sidebar {
                width: 100%;
                left: -100%;
            }
            
            .page-header,
            .filter-section,
            .table-section {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <li class="menu-item active">
                <a href="audit_log.php">
                    <img src="assets/gambar/icon/audit_log.png" class="menu-icon">
                    <span>Audit Log</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Letter Formatting</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="approval.php">
                    <img src="assets/gambar/icon/approve.png" class="menu-icon">
                    <span>Approval</span>
                </a>
            </li>
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

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>AUDIT LOG</h1>
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
                    <div>
                        <h2>
                            <i class="fas fa-history"></i>
                            Audit Log System
                        </h2>
                        <p>Monitor dan tracking aktivitas pengguna dalam sistem</p>
                    </div>
                    <div>
                        <a href="dashboard.php" class="btn btn-success">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </div>
                </div>

                <div class="filter-section">
                    <form method="get" class="filter-form">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" placeholder="Cari username..." value="<?php echo htmlspecialchars($username_filter); ?>">
                        </div>
                        <div class="form-group">
                            <label>Action</label>
                            <input type="text" name="action_q" placeholder="Cari action..." value="<?php echo htmlspecialchars($actionq); ?>">
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="audit_log.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    Menampilkan <?php echo ($total>0?($offset+1)."-".min($offset+$perPage,$total):"0"); ?> dari <?php echo $total; ?> entri
                </div>

                <div class="table-section">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th style="width:100px;">User ID</th>
                                <th style="width:150px;">Username</th>
                                <th>Action</th>
                                <th style="width:180px;">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($res && mysqli_num_rows($res) > 0): $no = $offset + 1; while ($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['action']); ?></td>
                                <td><?php echo date('d-M-Y H:i:s', strtotime($row['timestamp'])); ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>Belum ada data audit log</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <?php if ($total > $perPage): ?>
                    <div class="pagination">
                        <?php
                        $totalPages = max(1, ceil($total / $perPage));
                        $baseUrl = 'audit_log.php?username=' . urlencode($username_filter) . '&action_q=' . urlencode($actionq) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&page=';
                        
                        if ($page > 1) {
                            echo '<a class="page-link" href="' . $baseUrl . ($page-1) . '"><i class="fas fa-chevron-left"></i></a>';
                        }

                        for ($p = 1; $p <= $totalPages; $p++) {
                            if ($p == $page) {
                                echo '<span class="page-link active">' . $p . '</span>';
                            } else {
                                echo '<a class="page-link" href="' . $baseUrl . $p . '">' . $p . '</a>';
                            }
                        }
                        
                        if ($page < $totalPages) {
                            echo '<a class="page-link" href="' . $baseUrl . ($page+1) . '"><i class="fas fa-chevron-right"></i></a>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script menu burger
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