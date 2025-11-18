<?php
// session_start();

// // Koneksi Database
// $host = 'localhost';
// $dbname = 'cashier';
// $username = 'root';
// $password = '';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch(PDOException $e) {
//     die("Koneksi gagal: " . $e->getMessage());
// }

// // Proses Simpan Kas Masuk
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_kas'])) {
//     $keterangan = $_POST['keterangan'];
//     $jumlah = str_replace(['.', ','], ['', '.'], $_POST['jumlah']);
    
//     $stmt = $pdo->prepare("INSERT INTO kas_masuk (keterangan, jumlah, tanggal) VALUES (?, ?, NOW())");
//     $stmt->execute([$keterangan, $jumlah]);
    
//     header("Location: " . $_SERVER['PHP_SELF']);
//     exit;
// }

// // Proses Update Tabel
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_tabel'])) {
//     foreach ($_POST['nomor_surat'] as $id => $nomor) {
//         $keterangan = $_POST['keterangan_tabel'][$id];
//         $jumlah = str_replace(['.', ','], ['', '.'], $_POST['jumlah_tabel'][$id]);
        
//         $stmt = $pdo->prepare("UPDATE kas_masuk SET nomor_surat = ?, keterangan = ?, jumlah = ? WHERE id = ?");
//         $stmt->execute([$nomor, $keterangan, $jumlah, $id]);
//     }
    
//     header("Location: " . $_SERVER['PHP_SELF']);
//     exit;
// }

// // Ambil Data Kas Masuk
// $stmt = $pdo->query("SELECT * FROM kas_masuk ORDER BY tanggal DESC");
// $data_kas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAS KELUAR</title>
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
            background-color: #007a36;
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
    color: #111;
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
    background: white;
    width: 80px;
    height: auto;
    object-fit: contain;
}

.footer-text h2 {
    font-size: 18px;
    font-weight: 700;
}

.footer-text .subtitle {
    font-size: 14px;
    margin-top: -4px;
    color: #333;
}

.footer-text .description {
    font-size: 13px;
    margin-top: 10px;
    line-height: 1.5;
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
}

.footer-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
    margin-top: 3px;
}

.link-item {
    text-decoration: none;
    color: inherit;
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
            <h1>KAS KELUAR</h1>
        </div>
        <div class="user-info">
            <div class="user-avatar">🏢</div>
            <div>
                <div class="company-name">PT. Mitra Saudara Lestari</div>
                <div class="company-type">Kasir</div>
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
                <input type="text" name="jumlah" placeholder="Masukkan jumlah kas keluar">
            </div>

            <div class="button-group">
                <button class="btn btn-primary">Simpan Kas Keluar</button>
                <button type="button" class="btn btn-secondary" onclick="history.back()">Kembali</button>
            </div>
        </form>

        <button class="btn-export">Export ke PDF</button>
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

