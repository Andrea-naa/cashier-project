<?php
session_start();
require_once 'config/conn_db.php';

// Hanya untuk admin
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

// Handle aksi
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
    <style>
        /* Gaya CSS disingkat untuk ringkas */
        body { font-family: sans-serif; background: #fafafa; padding: 20px; }
        .btn { padding: 8px 12px; border-radius: 6px; cursor: pointer; }
        .btn-primary { background: #2e7d32; color: white; }
        .btn-outline { background: white; border: 1px solid #2e7d32; color: #2e7d32; }
        .btn-danger { background: #c62828; color: white; }
        .btn-sm { font-size: 13px; padding: 6px 10px; }
        .form-box, .table-wrap { background: white; padding: 16px; border-radius: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #eee; }
        thead th { background: #2e7d32; color: white; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <h2>Kelola User</h2>
    <?php if (!empty($msg)): ?>
        <div style="color:red; margin-bottom:10px"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="page-actions">
        <button id="btnShowAdd" class="btn btn-primary">Tambah User</button>
        <a href="dashboard.php" class="btn btn-outline">Kembali ke Dashboard</a>
    </div>

    <div id="addForm" class="form-box hidden">
        <h3>Tambah User Baru</h3>
        <form method="post">
            <input type="hidden" name="act" value="add">
            <label>Username<br><input type="text" name="username" required></label><br>
            <label>Password<br><input type="password" name="password" required></label><br>
            <label>Nama Lengkap<br><input type="text" name="nama_lengkap"></label><br>
            <label>Role<br><input type="text" name="role" placeholder="admin / kasir"></label><br><br>
            <button class="btn btn-primary" type="submit">Tambah</button>
            <button type="button" id="btnCancelAdd" class="btn btn-outline">Batal</button>
        </form>
    </div>

    <h3>Daftar User</h3>
    <div class="table-wrap">
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
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($u['role']) ?></td>
                    <td><?= $u['created_at'] ?></td>
                    <td>
                        <a class="btn btn-outline btn-sm" href="kelola_user.php?action=edit&id=<?= $u['id'] ?>">Edit</a>
                        <?php if ($u['id'] != $current_user_id): ?>
                            <a class="btn btn-danger btn-sm" href="kelola_user.php?action=delete&id=<?= $u['id'] ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
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
        <h3>Edit User #<?= $ed['id'] ?></h3>
        <form method="post">
            <input type="hidden" name="act" value="edit">
            <input type="hidden" name="id" value="<?= $ed['id'] ?>">
            <label>Username<br><input type="text" name="username" value="<?= htmlspecialchars($ed['username']) ?>" required></label><br>
            <label>Password baru (kosong = tidak diubah)<br><input type="password" name="password"></label><br>
            <label>Nama Lengkap<br><input type="text" name="nama_lengkap" value="<?= htmlspecialchars($ed['nama_lengkap']) ?>"></label><br>
            <label>Role<br><input type="text" name="role" value="<?= htmlspecialchars($ed['role']) ?>"></label><br><br>
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-outline" href="kelola_user.php">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <script>
        document.getElementById('btnShowAdd').onclick = function () {
            document.getElementById('addForm').classList.remove('hidden');
        };
        document.getElementById('btnCancelAdd').onclick = function () {
            document.getElementById('addForm').classList.add('hidden');
        };
    </script>
</body>
</html>