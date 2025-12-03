<?php
// koneksi ke database
require_once 'config/conn_db.php';

// check login
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

// bagian menangani aksi tambah, edit, dan hapus
$action = $_GET['action'] ?? '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'add') {
        $username_in = clean_input($_POST['username']);
        $password = $_POST['password'];
        $nama = clean_input($_POST['nama_lengkap']);
        $role_in = clean_input($_POST['role']);
        $created_at = date('Y-m-d H:i:s');

        if ($username_in && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES ('$username_in', '$hash', '$nama', '$role_in', '$created_at')");
            header('Location: kelola_user.php');
            exit();
        }
    } elseif ($act === 'edit') {
        $id = intval($_POST['id']);
        $username_in = clean_input($_POST['username']);
        $password = $_POST['password'];
        $nama = clean_input($_POST['nama_lengkap']);
        $role_in = clean_input($_POST['role']);

        if ($id && $username_in) {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username='$username_in', password='$hash', nama_lengkap='$nama', role='$role_in' WHERE id='$id'";
            } else {
                $sql = "UPDATE users SET username='$username_in', nama_lengkap='$nama', role='$role_in' WHERE id='$id'";
            }
            mysqli_query($conn, $sql);
            header('Location: kelola_user.php');
            exit();
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id === intval($current_user_id)) {
        $msg = 'Tidak dapat menghapus akun sendiri.';
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
        header('Location: kelola_user.php');
        exit();
    }
}

$users = mysqli_query($conn, "SELECT id, username, nama_lengkap, role, created_at FROM users ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola User - Sistem Kas Kebun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            max-width: 1500px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            background: white;
            padding: 24px 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h2 {
            color: #2d7a3e;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            min-width: 150px;
            height: 40px;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-primary:hover {
            background-color: #017033;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 152, 68, 0.3);
        }

        .btn-secondary {
            background-color: #dcdcdc;
            color: black;
        }

        .btn-secondary:hover {
            background-color: ##c7c7c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-sm {
            font-size: 13px;
            padding: 8px 16px;
            min-width: 90px;
            height: 36px;
        }

        .btn i {
            font-size: 14px;
        }

        .alert {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
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

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .form-box h3 {
            color: #2d7a3e;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-box label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            margin-top: 16px;
            font-size: 14px;
        }

        .form-box input,
        .form-box select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        .form-box input:focus,
        .form-box select:focus {
            outline: none;
            border-color: #2d7a3e;
            box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-wrap {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        .table-wrap h3 {
            color: #2d7a3e;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Styling dasar untuk semua header */
        thead th {
            background: #009844;
            color: white;
            padding: 16px 12px;
            text-align: center;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Border radius untuk sudut */
        thead th:first-child {
            border-top-left-radius: 12px;
        }

        thead th:last-child {
            border-top-right-radius: 12px;
        }

        /* Warna berbeda per kolom header */
        thead th:nth-child(1) {
            text-align: center;
            background: #009844;
        }

        thead th:nth-child(2) {
            background: #009844;
        }

        thead th:nth-child(3) {
            background: #009844;
        }

        thead th:nth-child(4) {
            background: #009844;
        }

        thead th:nth-child(5) {
            background: #009844;
        }

        thead th:nth-child(6) {
            background: #009844;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f7fafc;
        }

        tbody td {
            padding: 16px 12px;
            color: #4a5568;
            font-size: 14px;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-admin {
            background: #e6fffa;
            color: #234e52;
        }

        .badge-kasir {
            background: #fef5e7;
            color: #7d6608;
        }

        .hidden {
            display: none;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            width: 420px;
            max-width: calc(100% - 40px);
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.35);
            text-align: left;
        }

        .modal h4 {
            margin-bottom: 10px;
            color: #2d7a3e;
            font-size: 18px;
        }

        .modal p {
            color: #4a5568;
            margin-bottom: 18px;
            line-height: 1.4;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-actions {
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .table-wrap {
                padding: 16px;
            }

            table {
                font-size: 13px;
            }

            thead th,
            tbody td {
                padding: 12px 8px;
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
        }
    </style>
</head>
<body>
    <!-- menu burger -->
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
            <li class="menu-item active">
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
                <i class="fas fa-bars menu-burger" id="menuBurger"></i>
                <h1>KELOLA USER</h1>
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
                    <h2><i class="fas fa-users-cog"></i> Kelola User</h2>
                    <div class="page-actions">
                        <button id="btnShowAdd" class="btn btn-primary">
                            <i class=""></i>Tambah User
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class=""></i>Kembali
                        </a>
                    </div>
                </div>

                <?php if (!empty($msg)): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <div id="addForm" class="form-box hidden">
                    <h3><i class="fas fa-user-plus"></i> Tambah User Baru</h3>
                    <form method="post">
                        <input type="hidden" name="act" value="add">
                        
                        <label>Username</label>
                        <input type="text" name="username" required placeholder="Masukkan username">
                        
                        <label>Password</label>
                        <input type="password" name="password" required placeholder="Masukkan password">
                        
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap">
                        
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="Administrator">Administrator</option>
                            <option value="Kasir">Kasir</option>
                        </select>
                        
                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class=""></i> Tambah User
                            </button>
                            <button type="button" id="btnCancelAdd" class="btn btn-secondary">
                                <i class=""></i> Batal
                            </button>
                        </div>
                    </form>
                </div>

                <?php
                if ($action === 'edit' && isset($_GET['id'])):
                    $id = intval($_GET['id']);
                    $res = mysqli_query($conn, "SELECT id, username, nama_lengkap, role FROM users WHERE id='$id' LIMIT 1");
                    $ed = mysqli_fetch_assoc($res);
                ?>
                <div class="form-box">
                    <h3><i class="fas fa-user-edit"></i> Edit User #<?= $ed['id'] ?></h3>
                    <form method="post">
                        <input type="hidden" name="act" value="edit">
                        <input type="hidden" name="id" value="<?= $ed['id'] ?>">
                        
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($ed['username']) ?>" required>
                        
                        <label>Password Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($ed['nama_lengkap']) ?>">
                        
                        <label>Role</label>
                        <select name="role" required>
                            <option value="">Pilih Role</option>
                            <option value="Administrator" <?= $ed['role'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                            <option value="Kasir" <?= $ed['role'] === 'Kasir' ? 'selected' : '' ?>>Kasir</option>
                        </select>
                        
                        <div class="form-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class=""></i> Simpan Perubahan
                            </button>
                            <a class="btn btn-secondary" href="kelola_user.php">
                                <i class=""></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="table-wrap">
                    <h3><i class="fas fa-list"></i> Daftar User</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><strong><?= $u['id'] ?></strong></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                <td>
                                    <span class="badge <?= stripos($u['role'], 'admin') !== false ? 'badge-admin' : 'badge-kasir' ?>">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a class="btn btn-secondary btn-sm" href="kelola_user.php?action=edit&id=<?= $u['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($u['id'] != $current_user_id): ?>
                                        <a class="btn btn-danger btn-sm btn-delete" href="#" data-id="<?= $u['id'] ?>" data-username="<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- modal konfirmasi hapus -->
    <div id="modalOverlay" class="modal-overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <h4 id="modalTitle">Konfirmasi Penghapusan</h4>
            <p id="modalMessage">Apakah Anda yakin ingin menghapus user ini?</p>
            <div class="modal-actions">
                <button id="modalCancel" class="btn btn-secondary">Batal</button>
                <button id="modalConfirm" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>

    <script>
    // script menu burger
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

    // Script untuk Tambah User
    const btnShowAdd = document.getElementById('btnShowAdd');
    const btnCancelAdd = document.getElementById('btnCancelAdd');
    const addForm = document.getElementById('addForm');

    if (btnShowAdd) {
        btnShowAdd.addEventListener('click', function() {
            addForm.classList.remove('hidden');
            addForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    if (btnCancelAdd) {
        btnCancelAdd.addEventListener('click', function() {
            addForm.classList.add('hidden');
            // Reset form
            addForm.querySelector('form').reset();
        });
    }

    // Script untuk konfirmasi hapus
    const btnDeletes = document.querySelectorAll('.btn-delete');
    const modalOverlay = document.getElementById('modalOverlay');
    const modalCancel = document.getElementById('modalCancel');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalMessage = document.getElementById('modalMessage');
    let deleteUrl = '';

    btnDeletes.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            deleteUrl = 'kelola_user.php?action=delete&id=' + userId;
            modalMessage.textContent = 'Apakah Anda yakin ingin menghapus user "' + username + '"?';
            modalOverlay.classList.add('active');
        });
    });

    modalCancel.addEventListener('click', function() {
        modalOverlay.classList.remove('active');
        deleteUrl = '';
    });

    modalConfirm.addEventListener('click', function() {
        if (deleteUrl) {
            window.location.href = deleteUrl;
        }
    });

    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            modalOverlay.classList.remove('active');
            deleteUrl = '';
        }
    });

    // input kode perusahaan kapita secara otomatis
    const kodeInput = document.querySelector('input[name="kode_perusahaan"]');
    if (kodeInput) {
        kodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
</script>
</body>
</html>