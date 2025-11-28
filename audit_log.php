<?php
// koneksi ke database
require_once 'config/conn_db.php';

// Pastikan login 
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

// ngambil filter dari query string
$username = isset($_GET['username']) ? clean_input($_GET['username']) : '';
$actionq = isset($_GET['action_q']) ? clean_input($_GET['action_q']) : '';
$date_from = isset($_GET['date_from']) ? clean_input($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? clean_input($_GET['date_to']) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;

$conds = [];
if ($username !== '') $conds[] = "username LIKE '%" . $conn->real_escape_string($username) . "%'";
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
            margin:0; 
            padding:0; 
            box-sizing: border-box; 
        }

        body { 
            font-family: 'Inter', sans-serif; background: #e8f0e8; min-height: 100vh; }

        .container { 
            max-width: 100%; 
            margin: 0; 
            padding: 0 20px 40px; 
        }

        .top-header {
            background: #2d7a3e;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            margin-bottom: 20px;
        }

        .top-header h1 { 
            font-size: 20px; 
            margin: 0; 
            font-weight: 700; 
        }

        .header {
            background: white;
            padding: 18px 22px;
            border-radius: 10px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .header h2 {
             color: #2d7a3e; 
             font-size: 20px; 
             display:flex; 
             align-items:center; 
             gap:10px; 
            }

        .page-actions { 
            display:flex; 
            gap:10px; 
        }

        .btn { 
            padding: 10px 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            border: none; 
            font-weight: 600; 
            display:inline-flex; 
            align-items:center; 
            gap:8px; 
            text-decoration:none; 
            transition: all 0.3s ease; }
        .btn-primary { 
            background: #2d7a3e; 
            color: white; 
        }

        .btn-outline { 
            background: white; 
            border: 2px 
            solid #2d7a3e; 
            color: #2d7a3e; 
        }

        .btn-outline:hover { 
            background: #2d7a3e; 
            color: white; 
        }

        .btn:hover { 
            transform: translateY(-2px); 
        }

        /* bagian filter */
        .form-filters { 
            background: white; 
            padding: 16px; 
            border-radius: 10px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); 
            margin-bottom: 16px; 
            display:flex; 
            gap:10px; 
            flex-wrap:wrap; 
            align-items:center; 
        }

        .form-filters input[type=text], 
        .form-filters input[type=date] {
             padding:10px 12px; 
             border:1px solid #e2e8f0; 
             border-radius:8px; 
            }

        .form-filters button { 
            padding:10px 12px; 
            border-radius:8px; 
            border:none; 
            background:#2d7a3e; 
            color:white; 
            cursor:pointer; 
            font-weight: 600; 
        }

        .form-filters button:hover { 
            background: #236030; 
        }

        /* bagian tabel */
        .table-wrap {
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
            overflow-x:auto; 
        }

        table { 
            width:100%; 
            border-collapse: collapse; 
            min-width: 720px; 
        }

        thead th { 
            background: #2d7a3e; 
            color: white; 
            padding:12px; 
            text-align:left; 
            font-size:13px; 
        }

        tbody td { 
            padding:12px; 
            border-bottom:1px solid #f1f5f4; 
            color:#475057; 
            font-size:14px; 
        }

        tbody tr:hover { 
            background: #f7fafc; 
        }

        .muted { 
            color:#6b7280; 
            font-size:13px; 
        }

        .pagination {
            margin-top:12px; 
            display:flex; 
            gap:8px; 
            align-items:center; 
            flex-wrap:wrap; 
        }

        .page-link { 
            padding:6px 10px; 
            border-radius:8px; 
            border:1px solid #e5e7eb;
            background:white;
            text-decoration:none; 
            color:#374151; 
            transition: all 0.3s ease;
         }

        .page-link:hover { 
            background: #f3f4f6; 
        }

        .page-link.active { 
            background:#2d7a3e; 
            color:white; 
            border-color:#2d7a3e 
        }

        @media (max-width: 768px) {
            .header { 
                flex-direction: column; 
                align-items: flex-start; 
            }

            .form-filters { 
                flex-direction: column;
                 align-items: stretch; 
                }

            .page-actions .btn { 
                width: 100%; 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-history"></i> Audit Log</h2>
            <div class="page-actions">
                <a href="dashboard.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>

        <div class="form-filters">
            <form method="get" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; width:100%;">
                <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
                <input type="text" name="action_q" placeholder="Action contains" value="<?php echo htmlspecialchars($actionq); ?>">
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                <button type="submit"><i class="fas fa-search"></i> Filter</button>
                <a href="audit_log.php" class="page-link">Reset</a>
            </form>
        </div>

        <div class="small muted" style="margin:10px 0;">
            Menampilkan <?php echo ($total>0?($offset+1)."-".min($offset+$perPage,$total):"0"); ?> dari <?php echo $total; ?> entri
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
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
                    <tr><td colspan="5" class="muted" style="text-align:center; padding:20px;">
                        <i class="fas fa-inbox"></i> Belum ada data audit log.
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total > $perPage): ?>
        <div class="pagination">
            <?php
            $totalPages = max(1, ceil($total / $perPage));
            $baseUrl = 'audit_log.php?username=' . urlencode($username) . '&action_q=' . urlencode($actionq) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&page=';
            
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
</body>
</html>