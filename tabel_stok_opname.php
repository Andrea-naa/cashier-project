<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/conn_db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil informasi user dari session dengan fallback untuk mencegah undefined variable
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Guest';
$nama_lengkap = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'Kasir';

// bagian filter data tanggal transaksi
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_condition = '';

switch($filter) {
    case 'today':
        $date_condition = " WHERE DATE(tanggal_opname) = CURDATE()";
        break;
    case '7days':
        $date_condition = " WHERE DATE(tanggal_opname) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_condition = " WHERE MONTH(tanggal_opname) = MONTH(CURDATE()) AND YEAR(tanggal_opname) = YEAR(CURDATE())";
        break;
    default:
        $date_condition = '';
}

// bagian hapus data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM stok_opname WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $del_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: tabel_stok_opname.php?deleted=1");
    exit;
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;

// ngitung total data
$qCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM stok_opname $date_condition");
$total = 0;
if ($qCount) {
    $resultCount = mysqli_fetch_assoc($qCount);
    $total = $resultCount['total'] ?? 0;
    mysqli_free_result($qCount);
}
$totalPages = max(1, ceil($total / $limit));

// ngambil data stok opname dengan pagination
$rows = [];
$query = "SELECT * FROM stok_opname $date_condition ORDER BY tanggal_opname DESC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'ii', $start, $limit);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = $r;
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Stok Opname</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* header */
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

        /*  Main Wrapper  */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Filter Section - UPDATED STYLE */
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
            background-color: #dcdcdc;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .btn-filter:hover {
            background-color: #c7c7c7;
        }

        .btn-filter.active {
            background-color: #009844;
            color: white;
        }

        .btn-filter.active:hover {
            background-color: #017033;
        }

        .btn-filter i {
            font-size: 13px;
        }
        
        /*  kontener  */
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto 40px;
            background-color: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            flex: 1;
        }

        /*  notis  */
        .notice {
            background: #e6ffea;
            padding: 12px 16px;
            border: 1px solid #bde6c6;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2e7d32;
        }

        /* buttons  */
        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            border: none;
            transition: all 0.25s;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-primary:hover {
            background-color: #017033;
        }

        .btn-edit {
            background: #009844;
            color: #fff;
            min-width: 70px;
        }

        .btn-edit:hover {
            background: #017033;
        }

        .btn-delete {
            background: #dcdcdc;
            color: black;
            min-width: 70px;
        }

        .btn-delete:hover {
            background: #c7c7c7;
        }

        .btn-pdf {
            background: #009844;
            color: white;
            min-width: 70px;
        }

        .btn-pdf:hover {
            background: #017033;
        }

        /* tabel  */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background-color: #f2f2f2;
        }

        th {
            border: 1px solid #ddd;
            padding: 12px 10px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }

        td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
            text-align: left;
        }
        
        td:first-child {
            text-align: center;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .actions {
            display: flex;
            gap: 6px;
            justify-content: center;
            align-items: center;
        }

        .actions form {
            display: inline;
            margin: 0;
        }

        /* pagination  */
        .pager {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pager a {
            padding: 8px 14px;
            background-color: #009844;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: 0.25s;
        }

        .pager a:hover {
            background-color: #017033;
        }

        .pager a.active {
            background-color: #017033;
            font-weight: bold;
        }

        /* futer  */
        .ksk-footer {
            width: 100%;
            padding: 30px 40px;
            background: linear-gradient(to right, #00984489, #003216DB);
            color: #ffffff;
            border-top: 3px solid #333;
            font-family: 'Arial', sans-serif;
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

        /* RESPONSIVE  */
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
            
            .actions {
                flex-direction: column;
                gap: 6px;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media(max-width: 768px) {
            .container {
                padding: 25px 20px;
            }

            .header {
                padding: 15px 20px;
            }

            .header h1 {
                font-size: 18px;
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
            <li class="menu-item">
                <a href="logout.php">
                    <img src="assets/gambar/icon/logout.png" class="menu-icon">
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>DAFTAR STOK OPNAME</h1>
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

        <div class="content-wrapper">
            <!-- Filter Section - UPDATED -->
            <div class="filter-container">
                <div class="filter-wrapper">
                    <span class="filter-label">Filter:</span>
                    <a href="tabel_stok_opname.php?filter=today&page=<?= $page; ?>" 
                       class="btn-filter <?php echo $filter === 'today' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-day"></i>
                        <span>Hari Ini</span>
                    </a>
                    <a href="tabel_stok_opname.php?filter=7days&page=<?= $page; ?>" 
                       class="btn-filter <?php echo $filter === '7days' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-week"></i>
                        <span>7 Hari Terakhir</span>
                    </a>
                    <a href="tabel_stok_opname.php?filter=month&page=<?= $page; ?>" 
                       class="btn-filter <?php echo $filter === 'month' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Bulan Ini</span>
                    </a>
                    <a href="tabel_stok_opname.php?page=<?= $page; ?>" 
                       class="btn-filter <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-list"></i>
                        <span>Semua</span>
                    </a>
                </div>
            </div>

            <!-- CONTAINER -->
            <div class="container">
                <!-- Notifikasi -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="notice">✓ Data berhasil disimpan.</div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="notice">✓ Data berhasil dihapus.</div>
                <?php endif; ?>
                <?php if (isset($_GET['updated'])): ?>
                    <div class="notice">✓ Data berhasil diupdate.</div>
                <?php endif; ?>

                <!-- Button Group -->
                <div class="button-group">
                    <a href="dashboard.php" class="btn btn-primary">
                        Kembali ke Dashboard
                    </a>
                    <a href="stok_opname.php" class="btn btn-primary">
                        Buat Stok Opname Baru
                    </a>
                </div>

                <!-- Tabel Stok Opname -->
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:60px; text-align:center;">ID</th>
                                <th>Nomor Surat</th>
                                <th>Tanggal</th>
                                <th>User</th>
                                <th>Subtotal</th>
                                <th>Total Fisik</th>
                                <th>Saldo Sistem</th>
                                <th>Selisih</th>
                                <th style="width:220px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="9" style="text-align:center; padding:20px;">Belum ada data stok opname</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td style="text-align:center;"><?= intval($r['id']); ?></td>
                                    <td><?= htmlspecialchars($r['nomor_surat'] ?? '-'); ?></td>
                                    <td style="text-align:center;"><?= date('d-M-Y H:i', strtotime($r['tanggal_opname'])); ?></td>
                                    <td><?= htmlspecialchars($r['username']); ?></td>
                                    <td><?= 'Rp. ' . number_format($r['subtotal_fisik'], 0, ',', '.'); ?></td>
                                    <td><?= 'Rp. ' . number_format($r['fisik_total'], 0, ',', '.'); ?></td>
                                    <td><?= 'Rp. ' . number_format($r['saldo_sistem'], 0, ',', '.'); ?></td>
                                    <td><?= 'Rp. ' . number_format($r['selisih'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="stok_opname.php?edit=<?= intval($r['id']); ?>" class="btn btn-edit">
                                                Edit
                                            </a>
                                            <form method="post" onsubmit="return confirm('Hapus data ini?');">
                                                <input type="hidden" name="delete_id" value="<?= intval($r['id']); ?>">
                                                <button type="submit" class="btn btn-delete">
                                                    Delete
                                                </button>
                                            </form>
                                            <a href="export_pdf.php?type=kas_masuk&id=<?php echo $row['id']; ?>&print=1" 
                                            target="_blank" 
                                            class="btn btn-pdf btn-sm" 
                                            title="Cetak PDF">
                                                PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pager">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?page=<?= $p; ?>&filter=<?= $filter; ?>" <?= ($p == $page) ? 'class="active"' : ''; ?>><?= $p; ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- futer -->
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

        // Sidebar script
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