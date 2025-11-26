<?php
require_once 'config/conn_db.php';

// mengambil data user login
$user_id      = $_SESSION['user_id'];
$username     = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'] ?? 'Kasir';

// list pecahan fisik uang kas
$fisik_uang_kas = [
    ['no'=>1,'uraian'=>'Seratus Ribuan Kertas','satuan'=>'Lembar','nominal'=>100000],
    ['no'=>2,'uraian'=>'Lima Puluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>50000],
    ['no'=>3,'uraian'=>'Dua Puluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>20000],
    ['no'=>4,'uraian'=>'Sepuluh Ribuan Kertas','satuan'=>'Lembar','nominal'=>10000],
    ['no'=>5,'uraian'=>'Lima Ribuan Kertas','satuan'=>'Lembar','nominal'=>5000],
    ['no'=>6,'uraian'=>'Dua Ribuan Kertas','satuan'=>'Lembar','nominal'=>2000],
    ['no'=>7,'uraian'=>'Satu Ribuan Kertas','satuan'=>'Lembar','nominal'=>1000],
    ['no'=>8,'uraian'=>'Satu Ribuan Logam','satuan'=>'Keping','nominal'=>1000],
    ['no'=>9,'uraian'=>'Lima Ratusan Logam','satuan'=>'Keping','nominal'=>500],
    ['no'=>10,'uraian'=>'Dua Ratusan Logam','satuan'=>'Keping','nominal'=>200],
    ['no'=>11,'uraian'=>'Satu Ratusan Logam','satuan'=>'Keping','nominal'=>100],
];

// Hitung saldo sistem (dipindahkan ke atas agar bisa dipakai di JavaScript)
$q_system = mysqli_query($conn,
    "SELECT 
        (SELECT IFNULL(SUM(nominal),0) FROM transaksi WHERE jenis_transaksi='kas_terima') -
        (SELECT IFNULL(SUM(nominal),0) FROM transaksi WHERE jenis_transaksi='kas_keluar')
     AS saldo_sistem"
);
$saldo_sistem = mysqli_fetch_assoc($q_system)['saldo_sistem'] ?? 0;

// aksi edit data
$edit_mode = false;
$edit_id   = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

$edit_data = [];
$detail_map = [];

if ($edit_id > 0) {
    $edit_mode = true;

    $get_main = mysqli_query($conn, "SELECT * FROM stok_opname WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($get_main);

    $get_detail = mysqli_query($conn,
        "SELECT * FROM stok_opname_detail WHERE stok_opname_id=$edit_id ORDER BY no_urut ASC"
    );

    while ($d = mysqli_fetch_assoc($get_detail)) {
        $detail_map[$d['no_urut']] = $d['jumlah'];
    }
}

// aksi simpan data
if (isset($_POST['simpan'])) {

    // Hitung pecahan uang kas
    $subtotal_fisik = 0;
    $jumlah_items   = [];

    foreach ($fisik_uang_kas as $item) {
        $jumlah = floatval($_POST["jumlah_{$item['no']}"] ?? 0);

        $jumlah_items[] = [
            'no'     => $item['no'],
            'uraian' => $item['uraian'],
            'satuan' => $item['satuan'],
            'jumlah' => $jumlah,
            'nilai'  => $item['nominal']
        ];

        $subtotal_fisik += ($jumlah * $item['nominal']);
    }

    // bagian input lainnya
    $bon_sementara = floatval($_POST['bon_sementara'] ?? 0);
    $uang_rusak    = floatval($_POST['uang_rusak'] ?? 0);
    $materai = floatval($_POST['material'] ?? 0); // Simpan jumlah lembar
    $lain_lain     = floatval($_POST['lain_lain'] ?? 0);

    // Kalikan materai dengan 10.000 untuk perhitungan fisik_total
    $fisik_total = $subtotal_fisik + $bon_sementara + $uang_rusak + ($materai * 10000) + $lain_lain;

    // Selisih = Saldo Buku Kas - Jumlah Saldo Fisik
    $selisih = $saldo_sistem - $fisik_total;

    // aksi simpan ke database
    if (!$edit_mode) {

        $nomor_data = get_next_nomor_surat('STOK');
        $nomor_surat = $nomor_data['nomor'];

        $stmt = mysqli_prepare($conn,
            "INSERT INTO stok_opname
                (nomor_surat, user_id, username, subtotal_fisik, bon_sementara, uang_rusak, materai, lainnya, fisik_total, saldo_sistem, selisih)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'sisdddddddd',
            $nomor_surat, $user_id, $username, $subtotal_fisik, $bon_sementara, $uang_rusak,
            $materai, $lain_lain, $fisik_total, $saldo_sistem, $selisih
        );
        mysqli_stmt_execute($stmt);
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // bagian input detail
        $stmt2 = mysqli_prepare($conn,
            "INSERT INTO stok_opname_detail
                (stok_opname_id, no_urut, uraian, satuan, jumlah, nilai)
             VALUES (?,?,?,?,?,?)"
        );

        foreach ($jumlah_items as $it) {
            mysqli_stmt_bind_param(
                $stmt2,
                'iissid',
                $insert_id,
                $it['no'],
                $it['uraian'],
                $it['satuan'],
                $it['jumlah'],
                $it['nilai']
            );
            mysqli_stmt_execute($stmt2);
        }
        mysqli_stmt_close($stmt2);

    }
    // aksi update data
    else {

        mysqli_query($conn,
            "UPDATE stok_opname SET
                subtotal_fisik=$subtotal_fisik,
                bon_sementara=$bon_sementara,
                uang_rusak=$uang_rusak,
                materai=$materai,
                lainnya=$lain_lain,
                fisik_total=$fisik_total,
                saldo_sistem=$saldo_sistem,
                selisih=$selisih
             WHERE id=$edit_id"
        );

        // bagian buat hapus detail lama
        mysqli_query($conn, "DELETE FROM stok_opname_detail WHERE stok_opname_id=$edit_id");

        // bagian input ulang data detail
        $stmt3 = mysqli_prepare($conn,
            "INSERT INTO stok_opname_detail
                (stok_opname_id, no_urut, uraian, satuan, jumlah, nilai)
             VALUES (?,?,?,?,?,?)"
        );

        foreach ($jumlah_items as $it) {
            mysqli_stmt_bind_param(
                $stmt3,
                'iissid',
                $edit_id,
                $it['no'],
                $it['uraian'],
                $it['satuan'],
                $it['jumlah'],
                $it['nilai']
            );
            mysqli_stmt_execute($stmt3);
        }
        mysqli_stmt_close($stmt3);
    }

    header("Location: tabel_stok_opname.php?saved=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STOK OPNAME</title>
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
            display: flex;
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
        
        /* Main Wrapper */
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
            gap: 12px;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #009844;
            font-size: 16px;
            font-weight: bold;
        }

        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-size: 13px;
            font-weight: bold;
        }

        .user-role {
            font-size: 11px;
            opacity: .85;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 90%;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f8f8f8;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 8px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f0f0f0;
        }

        .input-field {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f5f5f5;
            font-size: 12px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .total-row span:first-child {
            flex: 1;
            text-align: center;
        }

        .total-row span:last-child {
            flex: 1;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            width: 250px;
            flex-shrink: 0;
        }

        .form-group .input-field {
            flex: 1;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background-color: #009844;
            color: white;
        }

        .btn-secondary {
            background-color: #ddd;
            color: #333;
        }

        .btn-table {
            background-color: #009844;
            color: white;
            padding: 12px 2px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            width: 350px;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        /*  futer  */
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
        }

        @media(max-width:768px){
            .container {
                padding: 25px 20px;
            }

            .button-group {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                left: -100%;
            }
        }

        .no-column {
            text-align: center;
            width: 40px;
        }

        .amount-column {
            width: 120px;
        }

        .input-number {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f5f5f5;
            font-size: 12px;
            text-align: right;
        }

        .total-value {
            color: #0e8c4a;
            font-weight: bold;
        }
    </style>
    <script>
        function formatRupiah(angka) {
            // buat menangani nilai negatif
            let prefix = '';
            if (angka < 0) {
                prefix = '-';
                angka = Math.abs(angka);
            }
            return prefix + 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ',00';
        }

        function hitungTotal() {
            let totalFisik = 0;
            
            // Hitung total fisik uang kas
            <?php foreach($fisik_uang_kas as $item): ?>
            let jumlah<?php echo $item['no']; ?> = parseFloat(document.getElementsByName('jumlah_<?php echo $item['no']; ?>')[0].value) || 0;
            totalFisik += jumlah<?php echo $item['no']; ?> * <?php echo $item['nominal']; ?>;
            <?php endforeach; ?>
            
            // Ambil nilai input lainnya
            let bonSementara = parseFloat(document.getElementsByName('bon_sementara')[0].value) || 0;
            let uangRusak = parseFloat(document.getElementsByName('uang_rusak')[0].value) || 0;
            let material = parseFloat(document.getElementsByName('material')[0].value) || 0;
            let materialNilai = material * 10000; // Kalikan dengan 10.000
            let lainLain = parseFloat(document.getElementsByName('lain_lain')[0].value) || 0;
            
            // Hitung jumlah saldo fisik
            let saldoFisik = totalFisik + bonSementara + uangRusak + materialNilai + lainLain;
            
            // Ambil saldo buku kas dari PHP
            let saldoBukuKas = <?php echo $saldo_sistem; ?>;
            
            // Hitung selisih = Saldo Buku Kas - Jumlah Saldo Fisik
            let selisih = saldoBukuKas - saldoFisik;
            
            // Update tampilan
            document.getElementById('total-fisik').textContent = formatRupiah(totalFisik);
            document.getElementById('saldo-fisik').textContent = formatRupiah(saldoFisik);
            document.getElementById('saldo-buku').textContent = formatRupiah(saldoBukuKas);
            document.getElementById('selisih').textContent = formatRupiah(selisih);
            
            // Debug log untuk melihat perhitungan
            console.log('Total Fisik:', totalFisik);
            console.log('Bon Sementara:', bonSementara);
            console.log('Uang Rusak:', uangRusak);
            console.log('Materai:', materialNilai);
            console.log('Lain-lain:', lainLain);
            console.log('Saldo Fisik:', saldoFisik);
            console.log('Saldo Buku Kas:', saldoBukuKas);
            console.log('Selisih:', selisih);
        }

        // memuat fungsi halaman
        window.onload = function() {
            // event listener untuk input perubahan
            let inputs = document.querySelectorAll('.input-field, .input-number');
            inputs.forEach(function(input) {
                input.addEventListener('input', hitungTotal);
                input.addEventListener('change', hitungTotal); // Tambahkan event change
            });
            
            // Hitung total pertama kali
            hitungTotal();
        };
    </script>
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
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Pengaturan Nomor Surat</span>
                </a>
            </li>
            <?php endif; ?> 
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="approval.php">
                    <i class="fas fa-check-circle menu-icon"></i>
                    <span>Approval</span>
                </a>
            </li>
            <?php endif; ?>
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
                <h1>STOK OPNAME</h1>
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
                <form method="POST" action="">
                    <div class="section-title">I. Pemeriksaan Fisik Uang Kas</div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th class="no-column">NO</th>
                                <th>URAIAN</th>
                                <th>SATUAN</th>
                                <th class="amount-column">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($fisik_uang_kas as $item): ?>
                            <tr>
                                <td class="no-column"><?php echo $item['no']; ?></td>
                                <td><?php echo $item['uraian']; ?></td>
                                <td><?php echo $item['satuan']; ?></td>
                                <td class="amount-column">
                                    <input type="number" 
                                           name="jumlah_<?php echo $item['no']; ?>" 
                                           class="input-number" 
                                           value="<?php echo $detail_map[$item['no']] ?? 0; ?>" 
                                           min="0">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="total-row">
                        <span>JUMLAH</span>
                        <span class="total-value" id="total-fisik">Rp. 0,00</span>
                    </div>

                    <div class="form-group">
                        <label>II. Bon Sementara</label>
                        <input type="number" name="bon_sementara" class="input-field" min="0" step="0.01" value="<?php echo $edit_data['bon_sementara'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>III. Uang Rusak</label>
                        <input type="number" name="uang_rusak" class="input-field" min="0" step="0.01" value="<?php echo $edit_data['uang_rusak'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>IV. Materai (Lembar @10.000)</label>
                        <input type="number" name="material" class="input-field" min="0" step="1" value="<?php echo $edit_data['materai'] ?? 0; ?>">
                    </div>

                    <div class="form-group">
                        <label>V. Lain-lain</label>
                        <input type="number" name="lain_lain" class="input-field" min="0" step="0.01" value="<?php echo $edit_data['lainnya'] ?? 0; ?>">
                    </div>

                    <div class="total-row">
                        <span>JUMLAH SALDO FISIK</span>
                        <span class="total-value" id="saldo-fisik">Rp. 0,00</span>
                    </div>

                    <div class="total-row">
                        <span>SALDO BUKU KAS</span>
                        <span class="total-value" id="saldo-buku">Rp. 0,00</span>
                    </div>

                    <div class="total-row" style="border-bottom: none;">
                        <span>SELISIH</span>
                        <span class="total-value" id="selisih">Rp. 0,00</span>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="simpan" class="btn btn-primary">
                            <?php echo $edit_mode ? 'Update' : 'Simpan'; ?> Stok Opname
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Kembali</button>
                    </div>

                    <button type="button" class="btn-table" onclick="window.location.href='tabel_stok_opname.php'">Lihat Tabel</button>
                </form>
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