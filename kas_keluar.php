<?php
// koneksi ke database
require_once 'config/conn_db.php';

check_login();
// mengambil data user login
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'] ?? 'Kasir';
$is_admin = (stripos($role, 'Administrator') !== false);

// bagian filter data tanggal transaksi
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$date_condition = '';

switch($filter) {
    case 'today':
        $date_condition = " AND DATE(tanggal_transaksi) = CURDATE()";
        break;
    case '7days':
        $date_condition = " AND DATE(tanggal_transaksi) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $date_condition = " AND MONTH(tanggal_transaksi) = MONTH(CURDATE()) AND YEAR(tanggal_transaksi) = YEAR(CURDATE())";
        break;
    default:
        $date_condition = '';
}

$approval_status = isset($_GET['approval_status']) ? $_GET['approval_status'] : 'all';
if ($approval_status === 'pending') {
    $date_condition .= " AND is_approved = 0";
} elseif ($approval_status === 'approved') {
    $date_condition .= " AND is_approved = 1";
}

$success_message = '';
$edit_mode = false;
$edit_data = [];

// bagian tombol edit
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_id = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_keluar' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    
    if ($edit_data) {
        // mengecek role user
        if (!$is_admin) {
            // mengecek apabila kasir mencoba edit data yang sudah di approve
            if ($edit_data['is_approved'] == 1) {
                $success_message = '<div class="alert alert-error">Data sudah di approve, kamu tidak dapat melakukan edit lagi!</div>';
                $edit_mode = false;
            }
            // mengecek apabila kasir mencoba edit data yang dimiliki user lain
            else if ($edit_data['user_id'] != $user_id) {
                $success_message = '<div class="alert alert-error">Kamu tidak memiliki akses untuk mengedit data ini!</div>';
                $edit_mode = false;
            }
            else {
                $edit_mode = true;
            }
        } else {
            $edit_mode = true;
        }
    } else {
        $success_message = '<div class="alert alert-error">Data tidak ditemukan!</div>';
    }
}

// bagian tombol simpan 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kas'])) {
    $keterangan = clean_input($_POST['keterangan'] ?? '');
    $jumlah_raw = trim($_POST['jumlah'] ?? '0');
    
    $jumlah = str_replace(['.', ','], ['', '.'], $jumlah_raw);
    $jumlah = floatval($jumlah);
    
if ($jumlah > 0) {
        
        if ($edit_mode && isset($_POST['edit_id'])) {
            // bagian tombol update
            $edit_id = intval($_POST['edit_id']);
            
            // proses validasi data
            if (!$is_admin) {
                $check_stmt = mysqli_prepare($conn, "SELECT is_approved, user_id FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_keluar'");
                mysqli_stmt_bind_param($check_stmt, 'i', $edit_id);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);
                $check_data = mysqli_fetch_assoc($check_result);
                mysqli_stmt_close($check_stmt);
                
                if ($check_data) {
                    if ($check_data['is_approved'] == 1) {
                        $success_message = '<div class="alert alert-error">Data sudah di approve, kamu tidak dapat melakukan edit lagi!</div>';
                        $edit_mode = false;
                    } else if ($check_data['user_id'] != $user_id) {
                        $success_message = '<div class="alert alert-error">Kamu tidak memiliki akses untuk mengedit data ini!</div>';
                        $edit_mode = false;
                    }
                }
            }
            
            if ($edit_mode || $is_admin) {
                $stmt = mysqli_prepare($conn, "UPDATE transaksi SET nominal = ?, keterangan = ? WHERE id = ? AND jenis_transaksi = 'kas_keluar'");
                mysqli_stmt_bind_param($stmt, 'dsi', $jumlah, $keterangan, $edit_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    log_audit($user_id, $username, "Update Kas Keluar #$edit_id: " . rupiah_fmt($jumlah));
                    mysqli_stmt_close($stmt);
                    header('Location: kas_keluar.php?success=2');
                    exit();
                }
                mysqli_stmt_close($stmt);
            }
            
        } else {
            // generate nomor surat
            $nomor_data = get_next_nomor_surat('KK');
            $nomor_surat = $nomor_data['nomor'];
            // bagian tombol simpan 
            $is_approved = $is_admin ? 1 : 0;
            $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (user_id, username, jenis_transaksi, nominal, keterangan, nomor_surat, tanggal_transaksi, is_approved) VALUES (?, ?, 'kas_keluar', ?, ?, ?, NOW(), ?)");
            mysqli_stmt_bind_param($stmt, 'isdssi', $user_id, $username, $jumlah, $keterangan, $nomor_surat, $is_approved);
            
            if (mysqli_stmt_execute($stmt)) {
                log_audit($user_id, $username, "Kas Keluar #$nomor_surat: " . rupiah_fmt($jumlah));
                mysqli_stmt_close($stmt);
                header('Location: kas_keluar.php?success=1');
                exit();
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $success_message = '<div class="alert alert-error">Jumlah kas harus lebih dari 0!</div>';
    }
}

// bagian tombol delete
if (isset($_GET['delete']) && intval($_GET['delete']) > 0) {
    $delete_id = intval($_GET['delete']);
    // mengecek validasi data sebelum melakukan delete
    $check_stmt = mysqli_prepare($conn, "SELECT is_approved, user_id FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_keluar'");
    mysqli_stmt_bind_param($check_stmt, 'i', $delete_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    if ($check_data) {
        if (!$is_admin) {
            // mengecek apabila kasir mencoba mendelete data yang sudah di approve
            if ($check_data['is_approved'] == 1) { 
                header('Location: kas_keluar.php?error=approved_delete'); 
                exit(); 
            }
            // mengecek apabila kasir mencoba mendelete data yang dimiliki user lain
            if ($check_data['user_id'] != $user_id) { 
                header('Location: kas_keluar.php?error=unauthorized_delete'); 
                exit(); 
            } 
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM transaksi WHERE id = ? AND jenis_transaksi = 'kas_keluar'"); mysqli_stmt_bind_param($stmt, 'i', $delete_id); if (mysqli_stmt_execute($stmt)) { log_audit($user_id, $username, "Hapus Kas Keluar #$delete_id"); mysqli_stmt_close($stmt); header('Location: kas_keluar.php?success=3'); exit(); } mysqli_stmt_close($stmt); 
    } 
}

// pesan kalo berhasil
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case '1':
            $success_message = '<div class="alert alert-success">✓ Data kas keluar berhasil disimpan!</div>';
            break;
        case '2':
            $success_message = '<div class="alert alert-success">✓ Data kas keluar berhasil diupdate!</div>';
            break;
        case '3':
            $success_message = '<div class="alert alert-success">✓ Data kas keluar berhasil dihapus!</div>';
            break;
    }
}
// pesan kalo error
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'approved_delete':
            $success_message = '<div class="alert alert-error">⚠️ Data sudah di-approve, kamu tidak dapat menghapus data ini!</div>';
            break;
        case 'unauthorized_delete':
            $success_message = '<div class="alert alert-error">⚠️ Kamu tidak memiliki akses untuk menghapus data ini!</div>';
            break;
    }
}

// bagian pagination
$limit_keluar = 5;
$page_keluar = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start_keluar = ($page_keluar - 1) * $limit_keluar;

// hitung total data
$qCount_keluar = mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE jenis_transaksi = 'kas_keluar' $date_condition");
$total_keluar = 0;
if ($qCount_keluar)
 {
    $resultCount_keluar = mysqli_fetch_assoc($qCount_keluar);
    $total_keluar = $resultCount_keluar['total'] ?? 0;
    mysqli_free_result($qCount_keluar);
 }
 $totalPages_keluar = max(1, ceil($total_keluar / $limit_keluar));

// ngambil data kas keluar
$data_kas = [];
$res = mysqli_query($conn, "SELECT t.*, u.nama_lengkap as approved_by_name FROM transaksi t LEFT JOIN users u ON t.approved_by = u.id WHERE t.jenis_transaksi = 'kas_keluar' $date_condition ORDER BY t.tanggal_transaksi DESC LIMIT $start_keluar, $limit_keluar"
);
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $data_kas[] = $r;
    }
    mysqli_free_result($res);
}  


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kas Keluar - Sistem Kas Kebun</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
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
        
        /* konten utama */
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
        /* * bagian filter */ 
        .filter-container {
            max-width: 860px;
            margin: 20px auto 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .filter-wrapper {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        .btn-filter {
            flex: none;
            padding: 8px 16px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .alert { 
            max-width: 860px; 
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
        } to {
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
            max-width: 1400px; 
            margin: 40px auto; 
            background-color: white; 
            padding: 40px; 
            border-radius: 14px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.12); 
            flex: 1; 
        }
        
        .form-group { 
            margin-bottom: 25px; 
        }
        .form-group label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            font-size: 15px; 
        }
        .form-group input { 
            width: 100%; 
            padding: 13px 16px; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            background-color: #f2f2f2; 
            font-size: 14px; 
        }
        .form-group input:focus { 
            background-color: white; 
            outline: none; 
            border-color: #009844; 
        }
        
        .button-group { 
            display: flex; 
            gap: 15px; 
            margin-top: 10px; 
            margin-bottom: 30px; 
        }
        .btn { 
            padding: 13px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
            border: none; 
            transition: 0.25s; 
            flex: 1; 
            font-size: 14px; 
            display: flex; 
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

        .btn-secondary { 
            background-color: #dcdcdc; 
            color: #333; 
        }

        .btn-secondary:hover { 
            background-color: #c7c7c7; 
        }

        .btn-sm { 
            padding: 6px 12px; 
            font-size: 12px; 
            border-radius: 4px; 
            flex: none; 
        }

        .btn-pdf { 
            background-color: #009844; 
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-pdf:hover { 
            background-color: #017033; 
        }

        .btn-edit { 
            background-color: #009844; 
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-edit:hover { 
            background-color: #017033; 
        }

        .btn-delete { 
            background-color: #e0e0e0;
            color: #000;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-delete:hover { 
            background-color: #d0d0d0; 
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: auto;
        }

        th:nth-child(1), td:nth-child(1) { 
            width: 50px; 
        }

        th:nth-child(2), td:nth-child(2) { 
            width: 170px; 
        } 

        th:nth-child(3), td:nth-child(3) {
            width: 110px; 
        } 

        th:nth-child(4), td:nth-child(4) { 
            width: auto; 
            min-width: 200px; 
        } 

        th:nth-child(5), td:nth-child(5) {
            width: 150px; 
            } 

        th:nth-child(6), td:nth-child(6) { 
            width: 120px; 
        } 

        th:nth-child(7), td:nth-child(7) {
            width: 280px; 
        }

        th:nth-child(8), td:nth-child(8) { 
            width: 100px; 
        } 

        td:nth-child(5) {
            text-align: right;
            padding-right: 15px;
            white-space: nowrap;
        }

        .table-wrapper { 
            overflow-x: visible;
            margin-top: 20px; 
        }


        thead { 
            background: #f2f2f2; 
        }

        th { 
            padding: 12px 10px; 
            text-align: center; 
            font-weight: 600; 
            border: 1px solid #ddd; 
        }

        td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            font-size: 13px; 
        }

        tbody tr:hover { 
            background: #f9f9f9; 
        }
        
        .nomor-info { 
            margin: 12px 0; 
            font-weight: 700; 
            color: #333; 
        }
        
        .ksk-footer { 
            width: 100%; 
            padding: 30px 40px; 
            background: linear-gradient(to right, #00984489, #003216DB); 
            color: #ffffff; 
            border-top: 3px solid #333; 
            margin-top: auto; 
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
            flex-direction: 
            column; 
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
        
        .edit-badge { 
            background: #ffc107; 
            color: #000; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 11px; 
            margin-left: 10px; 
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
                       
            .filter-wrapper {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-wrapper .btn-filter {
                width: 100%;
                justify-content: center;
            }
        }

        /* pagination */
        .pagination-wrapper {
            margin-top: 20px;
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            padding: 15px 0;
        }

        .pagination-btn {
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            min-width: 38px;
            height: 38px;
        }

        .pagination-btn.active {
            background-color: #009844;
            color: white;
            cursor: default;
            box-shadow: 0 2px 6px rgba(0, 152, 68, 0.3);
        }

        .pagination-btn.inactive {
            background-color: #dcdcdc;
            color: #333
        }

        .pagination-btn.inactive:hover {
            background-color: #c7c7c7;
            transform: translateY(-2px);
        }

        .pagination-btn.active:hover {
            transform: none;
        }

        .pagination-arrow {
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .pagination-wrapper {
                gap: 6px;
            }

            .pagination-btn {
                padding: 6px 10px;
                font-size: 12px;
                min-width: 34px;
                height: 34px;
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
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="audit_log.php">
                    <img src="assets/gambar/icon/audit_log.png" class="menu-icon">
                    <span>Audit Log</span>
                </a>
            </li>
            <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="setting_nomor.php">
                    <img src="assets/gambar/icon/settings.png" class="menu-icon">
                    <span>Letter Formatting</span>
                </a>
            </li>
            <?php endif; ?>
                        <?php if ($role === 'Administrator'): ?>
            <li class="menu-item">
                <a href="approval.php">
                    <img src="assets/gambar/icon/approve.png" class="menu-icon">
                    <span>Approval</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="menu-item">
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
                <h1>KAS KELUAR <?php if($edit_mode) echo '<span class="edit-badge">MODE EDIT</span>'; ?></h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($nama_lengkap, 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                </div>
            </div>
        </div>

             <!-- bagian filter -->
            <div class="content-wrapper">
            <div class="filter-container">
                <div class="filter-wrapper">
                    <span class="filter-label">Filter:</span>
                    <a href="kas_keluar.php?filter=today<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                    class="btn btn-filter btn-sm <?php echo $filter === 'today' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-day"></i> Hari Ini
                    </a>
                    <a href="kas_keluar.php?filter=7days<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                    class="btn btn-filter btn-sm <?php echo $filter === '7days' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-week"></i> 7 Hari Terakhir
                    </a>
                    <a href="kas_keluar.php?filter=month<?php echo $edit_mode ? '&edit='.$edit_data['id'] : ''; ?>" 
                    class="btn btn-filter btn-sm <?php echo $filter === 'month' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-calendar-alt"></i> Bulan Ini
                    </a>
                    <?php if ($is_admin): ?>
                        <a href="kas_keluar.php?filter=<?= $filter ?>&approval_status=pending" 
                        class="btn btn-filter btn-sm <?php echo $approval_status === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">
                            <i class="fas fa-clock"></i> Pending Approval
                        </a>
                        <a href="kas_keluar.php?filter=<?= $filter ?>&approval_status=approved" 
                        class="btn btn-filter btn-sm <?php echo $approval_status === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">
                            <i class="fas fa-check-circle"></i> Sudah Approved
                        </a>
                    <?php endif; ?>
                    
                    <a href="kas_keluar.php<?php echo $edit_mode ? '?edit='.$edit_data['id'] : ''; ?>" 
                    class="btn btn-filter btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                        <i class="fas fa-list"></i> Semua
                    </a>
                </div>
            </div>
            <?php if ($success_message): ?>
                <?php echo $success_message; ?>
            <?php endif; ?>

            <div class="container">
                <form method="POST">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Masukkan keterangan" value="<?php echo htmlspecialchars($edit_data['keterangan'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="text" name="jumlah" placeholder="Masukkan jumlah kas keluar" value="<?php echo $edit_mode ? number_format($edit_data['nominal'], 0, ',', '.') : ''; ?>" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="simpan_kas" class="btn btn-primary">
                            <?php echo $edit_mode ? 'Update' : 'Simpan'; ?> Kas Keluar
                        </button>
                        <?php if ($edit_mode): ?>
                            <a href="kas_keluar.php" class="btn btn-secondary">
                                Batal Edit
                            </a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Kembali
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="form-group">
                    <label>Daftar Kas Keluar</label>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:50px;">NO</th>
                                    <th style="width:130px;">NOMOR SURAT</th>
                                    <th style="width:130px;">TANGGAL</th>
                                    <th>KETERANGAN</th>
                                    <th style="width:130px;">JUMLAH</th>
                                    <th style="width:150px;">Username</th>
                                    <th style="width:220px;">AKSI</th>          
                                    <th style="width:100px;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($data_kas)): ?>
                                <?php $i = $start_keluar + 1; foreach ($data_kas as $row): ?>
                                    <tr>
                                        <td style="text-align:center;"><?php echo $i; ?></td>
                                        <td style="text-align:center;"><?php echo htmlspecialchars($row['nomor_surat'] ?? '-'); ?></td>
                                        <td style="text-align:center;"><?php echo date('d-M-Y', strtotime($row['tanggal_transaksi'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        <td style="text-align:left;">Rp. <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                        <td style="text-align:center;">
                                            <div class="action-buttons">
                                                <a href="kas_keluar.php?edit=<?php echo $row['id']; ?>" 
                                                class="btn btn-edit btn-sm" title="Edit">
                                                    Edit
                                                </a>
                                                
                                                <a href="kas_keluar.php?delete=<?php echo $row['id']; ?>" 
                                                class="btn btn-delete btn-sm" 
                                                onclick="return confirm('Yakin ingin menghapus data ini?')" 
                                                title="Hapus">
                                                    Delete
                                                </a>
                                                
                                                <a href="export_pdf.php?type=kas_keluar&id=<?php echo $row['id']; ?>&print=1" 
                                                target="_blank" 
                                                class="btn btn-pdf btn-sm" 
                                                title="Cetak PDF">
                                                    PDF
                                                </a>
                                            </div>
                                        </td>
                                            <td style="text-align:center;">
                                            <?php if ($row['is_approved'] == 1): ?>
                                                <span style="background:#d4edda; color:#155724; padding:4px 8px; border-radius:4px; font-size:11px;">
                                                    Approved
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php $i++; endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:20px; color:#999;">
                                        Belum ada data kas keluar
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        </div>  
                        
                <?php if ($totalPages_keluar > 1): ?>
                <div class="pagination-wrapper">
                    <?php
                    $baseUrl_keluar = 'kas_keluar.php?filter=' . $filter . '&approval_status=' . $approval_status . ($edit_mode ? '&edit='.$edit_data['id'] : '') . '&page=';
    
                    if ($page_keluar > 1) {
                        echo '<a class="pagination-btn inactive" href="' . $baseUrl_keluar . ($page_keluar-1) . '">
                                <i class="fas fa-chevron-left pagination-arrow"></i>
                            </a>';
                    }

                    echo '<a class="pagination-btn ' . ($page_keluar == 1 ? 'active' : 'inactive') . '" href="' . $baseUrl_keluar . '1">1</a>';
                    
                    if ($page_keluar > 3) {
                        echo '<span class="pagination-btn inactive" style="cursor: default;">...</span>';
                    }
                    
                    for ($p = max(2, $page_keluar - 1); $p <= min($totalPages_keluar - 1, $page_keluar + 1); $p++) {
                        if ($p == $page_keluar) {
                            echo '<span class="pagination-btn active">' . $p . '</span>';
                        } else {
                            echo '<a class="pagination-btn inactive" href="' . $baseUrl_keluar . $p . '">' . $p . '</a>';
                        }
                    }
                    
                    if ($page_keluar < $totalPages_keluar - 2) {
                        echo '<span class="pagination-btn inactive" style="cursor: default;">...</span>';
                    }
                    
                    if ($totalPages_keluar > 1) {
                        echo '<a class="pagination-btn ' . ($page_keluar == $totalPages_keluar ? 'active' : 'inactive') . '" href="' . $baseUrl_keluar . $totalPages_keluar . '">' . $totalPages_keluar . '</a>';
                    }

                    if ($page_keluar < $totalPages_keluar) {
                        echo '<a class="pagination-btn inactive" href="' . $baseUrl_keluar . ($page_keluar+1) . '">
                                <i class="fas fa-chevron-right pagination-arrow"></i>
                            </a>';
                    }
                    ?>
                </div>
                <?php endif; ?>
                    </div>
                </div>
            </div>

            <footer class="ksk-footer">
                <div class="footer-content">
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
                
        function autoPrint(pdfUrl) { 
            printJS({ 
                printable: pdfUrl, 
                type: 'pdf', 
                showModal: true, 
                modalMessage: 'Memproses dokumen...' 
            }); 
        }

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
    </script>
</body>
</html>