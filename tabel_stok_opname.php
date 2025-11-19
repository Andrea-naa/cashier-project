<?php
require_once 'config/conn_db.php';

// HANDLE DELETE via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM stok_opname WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $del_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: tabel_stok_opname.php?deleted=1");
    exit;
    }

    // Pagination setup
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $start = ($page - 1) * $limit;

    // Count total rows
    $qCount = mysqli_query($conn, "SELECT COUNT(*) as total FROM stok_opname");
    $total = mysqli_fetch_assoc($qCount)['total'] ?? 0;
    $totalPages = max(1, ceil($total / $limit));

    // Fetch paginated rows
    $stmt = mysqli_prepare($conn, "SELECT * FROM stok_opname ORDER BY tanggal_opname DESC LIMIT ?, ?");
    mysqli_stmt_bind_param($stmt, 'ii', $start, $limit);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
    mysqli_stmt_close($stmt);
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Stok Opname</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            padding: 16px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 13px;
        }
        .actions button {
            margin-right: 6px;
        }
        .btn {
            padding: 8px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .btn-edit {
            background: #ffd966;
        }
        .btn-delete {
            background: #ff7b7b;
            color: #fff;
        }
        .btn-export {
            background: #2e86de;
            color: #fff;
        }
        .pager a {
            margin-right: 6px;
        }
        .notice {
            background: #e6ffea;
            padding: 10px;
            border: 1px solid #bde6c6;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Daftar Stok Opname</h2>

    <div style="margin-bottom:10px;">
        <a href="stok_opname.php"><button class="btn">Buat Stok Opname Baru</button></a>
        <a href="export_pdf.php" target="_blank"><button class="btn btn-export">Export Semua ke PDF</button></a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="notice">Data berhasil disimpan.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice">Data berhasil dihapus.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="notice">Data berhasil diupdate.</div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>User</th>
                <th>Subtotal</th>
                <th>Total Fisik</th>
                <th>Saldo Sistem</th>
                <th>Selisih</th>
                <th>Tanggal</th>
                <th style="width:220px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="8" style="text-align:center;">Belum ada data stok opname</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td><?= intval($r['id']); ?></td>
                    <td><?= htmlspecialchars($r['username']); ?></td>
                    <td><?= 'Rp. ' . number_format($r['subtotal_fisik'], 0, ',', '.'); ?></td>
                    <td><?= 'Rp. ' . number_format($r['fisik_total'], 0, ',', '.'); ?></td>
                    <td><?= 'Rp. ' . number_format($r['saldo_sistem'], 0, ',', '.'); ?></td>
                    <td><?= 'Rp. ' . number_format($r['selisih'], 0, ',', '.'); ?></td>
                    <td><?= date('d-M-Y H:i', strtotime($r['tanggal_opname'])); ?></td>
                    <td class="actions">
                        <a href="stok_opname.php?edit=<?= intval($r['id']); ?>">
                            <button class="btn btn-edit">Edit</button>
                        </a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Hapus data ini?');">
                            <input type="hidden" name="delete_id" value="<?= intval($r['id']); ?>">
                            <button type="submit" class="btn btn-delete">Delete</button>
                        </form>
                        <a href="export_pdf.php?id=<?= intval($r['id']); ?>" target="_blank">
                            <button class="btn btn-export">Export</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div style="margin-top:12px;" class="pager">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p; ?>"><?= $p; ?></a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>