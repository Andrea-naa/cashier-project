<?php
require_once 'config/conn_db.php';

check_admin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$success_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset ($_POST['bulk_approved'])){
    $ids = $_POST['approved_ids'] ?? [];
    $table = $_POST['table_name'] ?? '';

    if (!empty ($ids) && in_array ($table, ['transaksi', 'stok_opname'])){
        $approved_count = 0;
        foreach ($ids as $id) {
            if (approve_data ($table, intval($id), $user_id, $username)){
                $approved_count++;
            }
        }
        $success_massage = "<div class= 'alert-success'>Berhasil di Approve 
        $approved_count data!<div>";
    }
} 


$pending_transaksi = [];
$pending_stok = [];
$res_transaksi = mysqli_query ($conn, "SELECT t.*, u.nama_lengkap as created_by_name FROM transaksi t LEFT JOIN users u ON t.user_id = u.id WHERE t.is_approved = 0 ORDER BY t.tanggal_transaksi DESC");

if ($res_transaksi){
    while ($r = mysqli_fetch_assoc ($res_transaksi)){
        $pending_transaksi[] = $r;
    }
}

$res_stok = mysqli_query ($conn, "SELECT s.*, u.nama_lengkap as created_by_name FROM stok_opname s LEFT JOIN users u ON s.user_id = u.id WHERE s.is_approved = 0 ORDER BY s.tanggal_opname DESC");

if ($res_stok){
    while ($r = mysqli_fetch_assoc ($res_stok)){
        $pending_stok[] = $r;
    }
}

$total_pending = count ($pending_transaksi) + count ($pending_stok);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Data - Sistem Kasir</title>
    <style></style>
<head>
<body>
    <div class="header">
        <h1>Approval Data</h1>
        <p> Mengelola persetujuan dan stok opname <p>
    <div>
    
    <div class="container">
        <?php if ($success_message): ?>
            <?php echo $success_message; ?>
            <?php endif; ?>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"><i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_pending; ?><h3>
                    <p> Total Pending Approval <p>
                </div>
            </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"><i>
            </div>
            <div class="stat-info">
                <h3><?php echo count ($pending_transaksi); ?><h3>
                <p> Transaksi Pending Approval <p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"><i>
            </div>
            <div class="stat-info">
                <h3><?php echo count ($pending_stok); ?><h3>
                <p> Stok Opname Pending Approval <p>
            </div>
        </div>
    </div>
    
    <!-- Transaksi Pending -->
     <div class="section">
        <div class="section-header">
            <h2 class="section-title"> Transaksi Mengunggu Approval <h2>
                <a href="dashboard.php" class="btn btn-primary"> 
                <i class="fas fa-home"><i>Dashboard <a>
        </div>

        <?php if (!empty ($pending_transaksi)): ?>
            <form method="POST" id="formTransaksi">
                    <input type="hidden" name="table_name" value="transaksi">
                    <div style="margin-bottom: 15px;">
                        <button type="submit" name="bulk_approve" class="btn btn-success">
                            <i class="fas fa-check-double"></i> Approve Selected
                        </button>
                    </div>
                    
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
                                    <td><?php echo htmlspecialchars($row['nomor_surat']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                    <td>
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
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Tidak ada transaksi yang menunggu approval</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Stok Opname Pending -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">📦 Stok Opname Menunggu Approval</h2>
            </div>
            
            <?php if (!empty($pending_stok)): ?>
                <form method="POST" id="formStok">
                    <input type="hidden" name="table_name" value="stok_opname">
                    <div style="margin-bottom: 15px;">
                        <button type="submit" name="bulk_approve" class="btn btn-success">
                            <i class="fas fa-check-double"></i> Approve Selected
                        </button>
                    </div>
                    
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
                                    <td><?php echo htmlspecialchars($row['nomor_surat']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($row['tanggal_opname'])); ?></td>
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
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Tidak ada stok opname yang menunggu approval</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleAll(type, checked) {
            const checkboxes = document.querySelectorAll('.checkbox-' + type);
            checkboxes.forEach(cb => cb.checked = checked);
        }
        
        // Confirm before bulk approve
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const checked = this.querySelectorAll('input[name="approve_ids[]"]:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal 1 data untuk di-approve');
                    return false;
                }
                
                if (!confirm(`Approve ${checked.length} data yang dipilih?`)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
