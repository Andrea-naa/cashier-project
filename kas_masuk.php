<?php
// Gunakan koneksi mysqli dari config
require_once 'config/conn_db.php';

// pastikan session tersedia (config/conn_db.php memanggil session_start())
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $nama_lengkap = $_SESSION['nama_lengkap'];
// Proses Simpan Kas Masuk ke tabel `transaksi`
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_kas'])) {
    $keterangan = trim($_POST['keterangan'] ?? '');
    $jumlah_raw = trim($_POST['jumlah'] ?? '0');
    // Bersihkan format ribuan dan koma
    $jumlah = str_replace(['.', ','], ['', '.'], $jumlah_raw);
    $jumlah = floatval($jumlah);


    $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (user_id, username, jenis_transaksi, nominal, keterangan, tanggal_transaksi) VALUES (?, ?, 'kas_terima', ?, ?, NOW())");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'isds', $user_id, $username, $jumlah, $keterangan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Log audit
    log_audit($user_id, $username, "Kas Masuk: " . rupiah_fmt($jumlah) . " - " . $keterangan);

    // Redirect agar form tidak submit ulang
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}    
  

// Ambil role user dari session (default: Kasir)
    $role = $_SESSION['role'] ?? 'Kasir';
// Ambil daftar kas masuk terbaru untuk ditampilkan di tabel
$data_kas = [];
$res = mysqli_query($conn, "SELECT id, user_id, username, nominal, keterangan, tanggal_transaksi FROM transaksi WHERE jenis_transaksi = 'kas_terima' ORDER BY tanggal_transaksi ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $data_kas[] = $r;
    }
    mysqli_free_result($res);
}

// Nomor surat terakhir (jika ada)
$last_nomor = '';
if (!empty($data_kas)) {
    $last = end($data_kas);
    $dt = strtotime($last['tanggal_transaksi']);
    $last_nomor = sprintf('%03d/KS/%02d/%04d', $last['id'], date('m', $dt), date('Y', $dt));
    // reset internal pointer just in case
    reset($data_kas);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAS MASUK</title>
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

        .company-name {
            font-size: 13px;
            font-weight: bold;
        }

        .company-type {
            font-size: 11px;
            opacity: .85;
        }

        /* ================= CONTAINER ================= */
        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background-color: white;
            padding: 40px 40px;
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
        }

        .form-group input:focus {
            background-color: white;
            outline: none;
            border-color: #009844;
        }

        /* ================= BUTTONS ================= */
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
        }

        .btn-secondary:hover {
            background-color: #c7c7c7;
        }

        .btn-export {
            display: block;
            margin: 40px auto 0;
            padding: 14px 40px;
            background-color: #009844;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            max-width: 300px;
            width: 100%;
        }

        .btn-export:hover {
            background-color: #007a36;
        }

        /* ================= FOOTER ================= */
        .ksk-footer {
            width: 100%;
            padding: 30px 40px;
            background: linear-gradient(to right, #00984489, #003216DB);
            color: #ffffff;
            border-top: 3px solid #333;
            font-family: 'Poppins', sans-serif;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 30px;
        }

        /* Left Section */
        .footer-left {
            display: flex;
            flex-direction: row;
            gap: 20px;
            width: 60%;
        }

        .footer-logo {
            width: 70px;
            height: 70px;
            /* background: white; */
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

        /* Right Section */
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
            color: black    ;
        }

        .footer-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            margin-top: 3px;
        }

        .link-item {
            text-decoration: none;
            color: black ;
        }

        .link-item:hover {
            opacity: 0.7;
        }

        /* RESPONSIVE */
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
        }


    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>KAS MASUK</h1>
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

    <div class="container">
        <form method="POST">
            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="keterangan" placeholder="Masukkan keterangan">
            </div>

            <div class="form-group">
                <label>Jumlah</label>
                <input type="text" name="jumlah" placeholder="Masukkan jumlah kas masuk">
            </div>

            <div class="button-group">
                <button type="submit" name="simpan_kas" class="btn btn-primary">Simpan Kas Masuk</button>
                <button type="button" class="btn btn-secondary" onclick="history.back()">Kembali</button>
            </div>
        </form>

            <!-- Nomor surat terakhir (ditampilkan di atas tabel) -->
            <?php if (!empty($last_nomor)): ?>
                <div style="margin:12px 0; font-weight:700;">Nomor: <?php echo htmlspecialchars($last_nomor); ?></div>
            <?php endif; ?>

            <!-- Tabel menampilkan inputan kas masuk yang sudah tersimpan -->
            <div class="form-group">
                <label>Daftar Kas Masuk</label>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background:#f2f2f2;">
                                <th style="border:1px solid #ddd; padding:10px; text-align:center; width:60px;">NO</th>
                                <th style="border:1px solid #ddd; padding:10px; text-align:center;">KETERANGAN</th>
                                <th style="border:1px solid #ddd; padding:10px; text-align:center; width:160px;">JUMLAH</th>
                                <th style="border:1px solid #ddd; padding:10px; text-align:center; width:160px;">TANGGAL</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($data_kas)): ?>
                            <?php $i = 1; foreach ($data_kas as $row): ?>
                                <?php
                                    $dt = strtotime($row['tanggal_transaksi']);
                                    $jumlah_fmt = number_format($row['nominal'], 0, ',', '.');
                                ?>
                                <tr>
                                    <td style="border:1px solid #ddd; padding:10px; text-align:center;"><?php echo $i; ?></td>
                                    <td style="border:1px solid #ddd; padding:10px;"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                    <td style="border:1px solid #ddd; padding:10px; text-align:right;">Rp. <?php echo $jumlah_fmt; ?></td>
                                    <td style="border:1px solid #ddd; padding:10px; text-align:center;"><?php echo date('d-M-Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                </tr>
                            <?php $i++; endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="border:1px solid #ddd; padding:14px; text-align:center;">Belum ada data kas masuk</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <a href="export_pdf.php?type=kas_masuk" target="_blank"><button class="btn-export">Export ke PDF</button></a>
    </div>

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

<script>
        // Toggle sidebar collapse when burger clicked
        (function(){
            var btn = document.getElementById('toggleSidebar');
            var sidebar = document.querySelector('.sidebar');
            var main = document.querySelector('.main-content');
            if (!btn) return;
            btn.addEventListener('click', function(){
                sidebar.classList.toggle('collapsed');
            });
        })();
    </script>

</body>
</html>

