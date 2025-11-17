<?php
session_start();
require_once 'config/conn_db.php';

// hanya untuk admin
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// mengambil role user 
$current_user_id = $_SESSION['user_id'];
$role = 'Kasir';
$resr = mysqli_query($conn, "SELECT role FROM users WHERE id='".intval($current_user_id)."' LIMIT 1");
if ($resr && mysqli_num_rows($resr) > 0) {
    $rr = mysqli_fetch_assoc($resr);
    if (!empty($rr['role'])) $role = $rr['role'];
}

if (stripos($role, 'admin') === false) {
    // hanya admin yang boleh akses
    echo "Akses ditolak. Hanya untuk admin.";
    exit();
}

// script PHP untuk Action yang ada di page ini
$action = $_GET['action'] ?? '';
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
            $sql = "INSERT INTO users (username, password, nama_lengkap, role, created_at) VALUES ('".$username."', '".$hash."', '".$nama."', '".$role_in."', '".$created_at."')";
            mysqli_query($conn, $sql);
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
                $sql = "UPDATE users SET username='".$username."', password='".$hash."', nama_lengkap='".$nama."', role='".$role_in."' WHERE id='".$id."'";
            } else {
                $sql = "UPDATE users SET username='".$username."', nama_lengkap='".$nama."', role='".$role_in."' WHERE id='".$id."'";
            }
            mysqli_query($conn, $sql);
            header('Location: kelola_user.php');
            exit();
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // jangan hapus akun sendiri
    if ($id === intval($current_user_id)) {
        $msg = 'Tidak dapat menghapus akun sendiri.';
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id='".$id."'");
        header('Location: kelola_user.php');
        exit();
    }
}

// ambil semua users
$users = mysqli_query($conn, "SELECT id, username, nama_lengkap, role, created_at FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Kelola User - Sistem Kas Kebun</title>
    <style>
        :root{--green:#2e7d32;--green-2:#66bb6a;--muted:#666}
        body{font-family:Segoe UI, Tahoma, sans-serif; padding:22px; background:#fafafa;color:#222}
        h2{margin-bottom:6px}
        .page-actions{margin-bottom:18px}
        .hidden{display:none}

        /* Buttons */
        .btn{background:var(--green);color:#fff;padding:9px 14px;border:none;border-radius:8px;cursor:pointer;box-shadow:0 4px 10px rgba(46,125,50,0.12);transition:all .18s ease}
        .btn:hover{transform:translateY(-2px);box-shadow:0 10px 18px rgba(46,125,50,0.14)}
        .btn-primary{background:var(--green)}
        .btn-outline{background:#fff;color:var(--green);border:1px solid var(--green);box-shadow:none}
        .btn-outline:hover{background:#f6fff6}
        .btn-danger{background:#c62828}
        .btn-sm{padding:6px 10px;border-radius:6px;font-size:13px}

        /* Table */
        .table-wrap{background:#fff;padding:14px;border-radius:10px;border:1px solid #eee;box-shadow:0 6px 20px rgba(0,0,0,0.04)}
        table{border-collapse:collapse;width:100%;}
        thead th{background:linear-gradient(180deg,var(--green),#176022);color:#fff;padding:12px;border-radius:6px}
        th,td{padding:12px;text-align:left;border-bottom:1px solid #f0f0f0}
        tbody tr:nth-child(odd){background:#fbfbfb}
        tbody tr:hover{background:#f2fff2}

        .small{font-size:13px;color:var(--muted)}

        /* Form box */
        .form-box{margin:12px 0;padding:16px;border-radius:10px;background:#fff;border:1px solid #eee;box-shadow:0 6px 18px rgba(0,0,0,0.04)}
        .form-box h3{margin-top:0}
        .form-row{margin:8px 0}
        .form-row input[type=text], .form-row input[type=password]{width:100%;padding:10px;border-radius:6px;border:1px solid #ddd}

        /* Edit box */
        .edit-box{margin-top:18px}

        /* Action buttons in table */
        td .btn{display:inline-block;margin-right:8px}

        @media (max-width:700px){
            .menu{padding:8px}
            .table-wrap{padding:8px}
            .form-box{padding:12px}
        }
    </style>
</head>
<body>
    <h2>Kelola User</h2>
    <?php if (!empty($msg)): ?><div style="color:red;margin-bottom:8px"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <div class="page-actions" style="margin-bottom:18px;display:flex;gap:12px;align-items:center;">
        <button id="btnShowAdd" class="btn btn-primary">Tambah User</button>
        <a href="dashboard.php" class="btn btn-outline">Kembali ke Dashboard</a>
    </div>

    <div id="addForm" class="form-box hidden">
        <h3>Tambah User Baru</h3>
        <form method="post">
            <input type="hidden" name="act" value="add">
            <div class="form-row"><label>Username<br><input type="text" name="username" required></label></div>
            <div class="form-row"><label>Password<br><input type="password" name="password" required></label></div>
            <div class="form-row"><label>Nama Lengkap<br><input type="text" name="nama_lengkap"></label></div>
            <div class="form-row"><label>Role<br><input type="text" name="role" placeholder="admin / kasir"></label></div>
            <div style="margin-top:12px">
                <button class="btn btn-primary" type="submit">Tambah</button>
                <button type="button" id="btnCancelAdd" class="btn btn-outline" style="margin-left:8px">Batal</button>
            </div>
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
            <?php while($u = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                <td><?php echo htmlspecialchars($u['role']); ?></td>
                <td class="small"><?php echo $u['created_at']; ?></td>
                <td>
                    <a class="btn btn-outline btn-sm" href="kelola_user.php?action=edit&id=<?php echo $u['id']; ?>">Edit</a>
                    <?php if ($u['id'] != $current_user_id): ?> 
                      <a class="btn btn-danger btn-sm" href="kelola_user.php?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

<?php
// jika edit mode, tampilkan form edit di bawah
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])):
    $id = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT id, username, nama_lengkap, role FROM users WHERE id='".$id."' LIMIT 1");
    $ed = mysqli_fetch_assoc($res);
?>
    <div class="form-box edit-box">
        <h3>Edit User #<?php echo $ed['id']; ?></h3>
        <form method="post">
            <input type="hidden" name="act" value="edit">
            <input type="hidden" name="id" value="<?php echo $ed['id']; ?>">
            <div class="form-row"><label>Username<br><input type="text" name="username" value="<?php echo htmlspecialchars($ed['username']); ?>" required></label></div>
            <div class="form-row"><label>Password baru (kosong = tidak diubah)<br><input type="password" name="password"></label></div>
            <div class="form-row"><label>Nama Lengkap<br><input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($ed['nama_lengkap']); ?>"></label></div>
            <div class="form-row"><label>Role<br><input type="text" name="role" value="<?php echo htmlspecialchars($ed['role']); ?>"></label></div>
            <div style="margin-top:12px">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <a class="btn btn-outline" href="kelola_user.php" style="margin-left:8px">Batal</a>
            </div>
        </form>
    </div>
<?php endif; ?>

    <script>
        (function(){
            var btn = document.getElementById('btnShowAdd');
            var form = document.getElementById('addForm');
            var btnCancel = document.getElementById('btnCancelAdd');
            if (btn){
                btn.addEventListener('click', function(){
                    form.classList.remove('hidden');
                    form.scrollIntoView({behavior:'smooth',block:'center'});
                });
            }
            if (btnCancel){
                btnCancel.addEventListener('click', function(){
                    form.classList.add('hidden');
                });
            }
        })();
    </script>
</body>
</html>
