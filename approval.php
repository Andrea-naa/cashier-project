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
        $success_message = "<div class='alert alert-success'>✓ Berhasil approve $approved_count data!</div>";
    }
}

$pending_transaksi = [];
$pending_stok = [];

$res_transaksi = mysqli_query($conn, "SELECT t.*, u.nama_lengkap as created_by_name FROM transaksi t LEFT JOIN users u ON t.user_id = u.id WHERE t.is_approved = 0 ORDER BY t.tanggal_transaksi DESC");

if ($res_transaksi){
    while ($r = mysqli_fetch_assoc($res_transaksi)){
        $pending_transaksi[] = $r;
    }
}

$res_stok = mysqli_query($conn, "SELECT s.*, u.nama_lengkap as created_by_name FROM stok_opname s LEFT JOIN users u ON s.user_id = u.id WHERE s.is_approved = 0 ORDER BY s.tanggal_opname DESC");

if ($res_stok){
    while ($r = mysqli_fetch_assoc($res_stok)){
        $pending_stok[] = $r;
    }
}

$total_pending = count($pending_transaksi) + count($pending_stok);
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
            background-color: #017033FF; 
        }

        .btn-success { 
            background-color: #009844; 
            color: white; 
        }

        .btn-success:hover { 
            background-color: #017033FF; 
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
            background: #DDDDDDFF; 
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
        }
        

        
        @media(max-width:768px){
            .container { 
                padding: 20px; 
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .stat-card {
                padding: 16px 20px;
            }
            
            .stat-info h3 {
                font-size: 28px;
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
                        <div style="margin-bottom: 15px;">
                            <button type="submit" name="bulk_approve" class="btn btn-success">
                                <i class=""></i> Approve Selected
                            </button>
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
                    <div class="empty-state">
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
                        <div style="margin-bottom: 15px;">
                            <button type="submit" name="bulk_approve" class="btn btn-success">
                                <i class=""></i> Approve Selected
                            </button>
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
                                                Rp <?php echo number_format($row['selisih'], 0, ',', '.'); ?>
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
                        <p>Tidak ada stok opname yang menunggu approval</p>
                    </div>
                <?php endif; ?>
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