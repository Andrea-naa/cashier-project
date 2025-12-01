<?php
// koneksi ke database
require_once 'config/conn_db.php';

// check login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
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
        $username = clean_input($_POST['username']);
        $password = $_POST['password'];
        $nama = clean_input($_POST['nama_lengkap']);
        $role_in = clean_input($_POST['role']);
        $created_at = date('Y-m-d H:i:s');

        if ($username && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES ('$username', '$hash', '$nama', '$role_in', '$created_at')");
            header('Location: kelola_user.php');
            exit();
        }
    } elseif ($act === 'edit') {
        $id = intval($_POST['id']);
        $username = clean_input($_POST['username']);
        $password = $_POST['password'];
        $nama = clean_input($_POST['nama_lengkap']);
        $role_in = clean_input($_POST['role']);

        if ($id && $username) {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username='$username', password='$hash', nama_lengkap='$nama', role='$role_in' WHERE id='$id'";
            } else {
                $sql = "UPDATE users SET username='$username', nama_lengkap='$nama', role='$role_in' WHERE id='$id'";
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
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        .top-header {
            background: #2d7a3e;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .top-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d7a3e;
            font-weight: 700;
            font-size: 18px;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 16px;
        }

        .user-role {
            font-size: 13px;
            opacity: 0.9;
        }

        .content-wrapper {
            padding: 40px;
        }

        .header {
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

        .header h2 {
            color: #2d7a3e;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h2 i {
            color: #2d7a3e;
        }

        .page-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background: #2d7a3e;
            color: white;
            box-shadow: 0 2px 8px rgba(45, 122, 62, 0.3);
        }

        .btn-primary:hover {
            background: #236030;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 122, 62, 0.4);
        }

        .btn-outline {
            background: white;
            border: 2px solid #2d7a3e;
            color: #2d7a3e;
        }

        .btn-outline:hover {
            background: #2d7a3e;
            color: white;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f56565 0%, #c53030 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
        }

        .btn-sm {
            font-size: 13px;
            padding: 8px 16px;
        }

        .alert {
            background: #fff5f5;
            border-left: 4px solid #f56565;
            color: #c53030;
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

        .form-box h3 i {
            color: #2d7a3e;
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

        .table-wrap h3 i {
            color: #2d7a3e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        thead th {
            background: #2d7a3e;
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:first-child {
            border-radius: 8px 0 0 0;
        }

        thead th:last-child {
            border-radius: 0 8px 0 0;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: #f7fafc;
            transform: scale(1.01);
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

        @media (max-width: 768px) {
            .header {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-users-cog"></i> Kelola User</h2>
            <div class="page-actions">
                <button id="btnShowAdd" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah User
                </button>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Kembali
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
                
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" required placeholder="Masukkan username">
                
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
                
                <label><i class="fas fa-id-card"></i> Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap">
                
                <label><i class="fas fa-user-tag"></i> Role</label>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="Administrator">Administrator</option>
                    <option value="Kasir">Kasir</option>
                </select>
                
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save"></i> Tambah User
                    </button>
                    <button type="button" id="btnCancelAdd" class="btn btn-outline">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <h3><i class="fas fa-list"></i> Daftar User</h3>
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> Username</th>
                        <th><i class="fas fa-id-card"></i> Nama Lengkap</th>
                        <th><i class="fas fa-user-tag"></i> Role</th>
                        <th><i class="fas fa-calendar"></i> Dibuat</th>
                        <th><i class="fas fa-cog"></i> Aksi</th>
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
                            <a class="btn btn-outline btn-sm" href="kelola_user.php?action=edit&id=<?= $u['id'] ?>">
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
                
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($ed['username']) ?>" required>
                
                <label><i class="fas fa-lock"></i> Password Baru</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                
                <label><i class="fas fa-id-card"></i> Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($ed['nama_lengkap']) ?>">
                
                <label><i class="fas fa-user-tag"></i> Role</label>
                <select name="role" required>
                    <option value="">Pilih Role</option>
                    <option value="Administrator" <?= $ed['role'] === 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                    <option value="Kasir" <?= $ed['role'] === 'Kasir' ? 'selected' : '' ?>>Kasir</option>
                </select>
                
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a class="btn btn-outline" href="kelola_user.php">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <!-- Konfirmasi Hapus -->
    <div id="modalOverlay" class="modal-overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
            <h4 id="modalTitle">Konfirmasi Penghapusan</h4>
            <p id="modalMessage">Apakah Anda yakin ingin menghapus user ini?</p>
            <div class="modal-actions">
                <button id="modalCancel" class="btn btn-outline">Batal</button>
                <button id="modalConfirm" class="btn btn-danger">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // script untuk menampilkan dan menyembunyikan form tambah user
        document.getElementById('btnShowAdd').onclick = function () {
            document.getElementById('addForm').classList.remove('hidden');
            document.getElementById('addForm').scrollIntoView({ behavior: 'smooth' });
        };
        document.getElementById('btnCancelAdd').onclick = function () {
            document.getElementById('addForm').classList.add('hidden');
        };
        // script untuk konfirmasi hapus user
        (function(){
            var overlay = document.getElementById('modalOverlay');
            var modalMessage = document.getElementById('modalMessage');
            var modalConfirm = document.getElementById('modalConfirm');
            var modalCancel = document.getElementById('modalCancel');
            var targetId = null;

            function openModal(id, username) {
                targetId = id;
                modalMessage.textContent = 'Apakah Anda yakin ingin menghapus akun "' + username + '"? Tindakan ini dapat dibatalkan.';
                overlay.classList.add('active');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                targetId = null;
                overlay.classList.remove('active');
                overlay.setAttribute('aria-hidden', 'true');
            }

            document.querySelectorAll('.btn-delete').forEach(function(btn){
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    var id = this.getAttribute('data-id');
                    var username = this.getAttribute('data-username') || '';
                    openModal(id, username);
                });
            });

            modalCancel.addEventListener('click', function(){
                closeModal();
            });

            overlay.addEventListener('click', function(e){
                if (e.target === overlay) closeModal();
            });

            modalConfirm.addEventListener('click', function(){
                if (!targetId) return closeModal();
                window.location.href = 'kelola_user.php?action=delete&id=' + encodeURIComponent(targetId);
            });
        })();
    </script>
</body>
</html>