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

// HANDLE DELETE via POST
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

// Count total rows
$qCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM stok_opname");
$total = 0;
if ($qCount) {
    $resultCount = mysqli_fetch_assoc($qCount);
    $total = $resultCount['total'] ?? 0;
    mysqli_free_result($qCount);
}
$totalPages = max(1, ceil($total / $limit));

// Fetch paginated rows
$rows = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname ORDER BY tanggal_opname DESC LIMIT ?, ?");
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

        /* ================= HEADER ================= */
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

        .menu-icon {
            font-size: 26px;
            cursor: pointer;
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

        /* ================= CONTAINER ================= */
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 40px auto;
            background-color: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            flex: 1;
        }

        /* ================= NOTICE ================= */
        .notice {
            background: #e6ffea;
            padding: 12px 16px;
            border: 1px solid #bde6c6;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2e7d32;
        }

        /* ================= BUTTONS ================= */
        .button-group {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .btn {
            padding: 13px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: 0.25s;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-primary:hover {
            background-color: #017033;
        }

        .btn-export {
            background-color: ##DFDFDFFF;
            color: black;
        }

        .btn-export:hover {
            background-color: #A8A8A8FF;
        }

        .btn-edit {
            background: #009844;
            color: #fff;
            padding: 8px 19px;
            font-size: 13px;
        }

        .btn-edit:hover {
            background: #017033;
        }

        .btn-delete {
            background: #DFDFDFFF;
            color: black;
            padding: 8px 12px;
            font-size: 13px;
        }

        .btn-delete:hover {
            background: #A8A8A8FF;
        }

        /* ================= TABLE ================= */
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
            text-align: left;
        }

        td {
            border: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .actions form {
            display: inline;
            margin: 0;
        }

        .actions a,
        .actions form {
            flex-shrink: 0;
        }

        /* ================= PAGINATION ================= */
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

        /* ================= FOOTER ================= */
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

        /* ================= RESPONSIVE ================= */
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
    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
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
            <a href="stok_opname.php"><button class="btn btn-primary">Buat Stok Opname Baru</button></a>
            <a href="export_pdf.php" target="_blank"><button class="btn btn-export">Export Semua ke PDF</button></a>
        </div>

        <!-- Tabel Stok Opname -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px; text-align:center;">ID</th>
                        <th>User</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:right;">Total Fisik</th>
                        <th style="text-align:right;">Saldo Sistem</th>
                        <th style="text-align:right;">Selisih</th>
                        <th style="text-align:center;">Tanggal</th>
                        <th style="width:220px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:20px;">Belum ada data stok opname</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td style="text-align:center;"><?= intval($r['id']); ?></td>
                            <td><?= htmlspecialchars($r['username']); ?></td>
                            <td style="text-align:right;"><?= 'Rp. ' . number_format($r['subtotal_fisik'], 0, ',', '.'); ?></td>
                            <td style="text-align:right;"><?= 'Rp. ' . number_format($r['fisik_total'], 0, ',', '.'); ?></td>
                            <td style="text-align:right;"><?= 'Rp. ' . number_format($r['saldo_sistem'], 0, ',', '.'); ?></td>
                            <td style="text-align:right;"><?= 'Rp. ' . number_format($r['selisih'], 0, ',', '.'); ?></td>
                            <td style="text-align:center;"><?= date('d-M-Y H:i', strtotime($r['tanggal_opname'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="stok_opname.php?edit=<?= intval($r['id']); ?>">
                                        <button class="btn btn-edit">Edit</button>
                                    </a>
                                    <form method="post" onsubmit="return confirm('Hapus data ini?');">
                                        <input type="hidden" name="delete_id" value="<?= intval($r['id']); ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
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
                <a href="?page=<?= $p; ?>" <?= ($p == $page) ? 'class="active"' : ''; ?>><?= $p; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer class="ksk-footer">
        <div class="footer-content">
            <!-- Left Section -->
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

            <!-- Right Section -->
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
</body>
</html>