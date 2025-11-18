<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STOK OPNAME</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #E5FCED;
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

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            background-color: #0e8c4a;
            color: white;
        }

        .btn-secondary {
            background-color: #ddd;
            color: #333;
        }

        .btn-table {
            background-color: #0e8c4a;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            width: 200px;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
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
    width: 80px;
    height: auto;
    object-fit: contain;
}

.footer-text h2 {
    font-size: 18px;
    font-weight: 700;
    color: #e8f5e9;
}

.footer-text .subtitle {
    font-size: 14px;
    margin-top: -4px;
    color: #dfeee0;
}

.footer-text .description {
    font-size: 13px;
    margin-top: 10px;
    line-height: 1.5;
    color: #e8f5e9;
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
    color: #ffffff;
}

.footer-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
    margin-top: 3px;
}

.link-item {
    text-decoration: none;
    color: #ffffff;
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
            return 'Rp. ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ',00';
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
            let lainLain = parseFloat(document.getElementsByName('lain_lain')[0].value) || 0;
            
            // Hitung jumlah saldo fisik
            let saldoFisik = totalFisik + bonSementara + uangRusak + material + lainLain;
            
            // Update tampilan
            document.getElementById('total-fisik').textContent = formatRupiah(totalFisik);
            document.getElementById('saldo-fisik').textContent = formatRupiah(saldoFisik);
            
            // Hitung selisih (misalnya dari saldo buku - saldo fisik)
            // Untuk sekarang selisih = 0, bisa disesuaikan dengan saldo buku jika ada
            let selisih = 0;
            document.getElementById('selisih').textContent = formatRupiah(selisih);
        }

        // Jalankan saat halaman dimuat
        window.onload = function() {
            // Tambahkan event listener untuk semua input
            let inputs = document.querySelectorAll('.input-field, .input-number');
            inputs.forEach(function(input) {
                input.addEventListener('input', hitungTotal);
            });
            
            // Hitung total awal
            hitungTotal();
        };
    </script>
</head>
<body>
    <?php
    // Data dummy untuk tabel dengan nilai nominal masing-masing
    $fisik_uang_kas = [
        ['no' => 1, 'uraian' => 'Seratus Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 100000],
        ['no' => 2, 'uraian' => 'Lima Puluh Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 50000],
        ['no' => 3, 'uraian' => 'Dua Puluh Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 20000],
        ['no' => 4, 'uraian' => 'Sepuluh Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 10000],
        ['no' => 5, 'uraian' => 'Lima Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 5000],
        ['no' => 6, 'uraian' => 'Dua Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 2000],
        ['no' => 7, 'uraian' => 'Satu Ribuan Kertas', 'satuan' => 'Lembar', 'jumlah' => '', 'nominal' => 1000],
        ['no' => 8, 'uraian' => 'Satu Ribuan Logam', 'satuan' => 'Keping', 'jumlah' => '', 'nominal' => 1000],
        ['no' => 9, 'uraian' => 'Lima Ratusan Logam', 'satuan' => 'Keping', 'jumlah' => '', 'nominal' => 500],
        ['no' => 10, 'uraian' => 'Dua Ratusan Logam', 'satuan' => 'Keping', 'jumlah' => '', 'nominal' => 200],
        ['no' => 11, 'uraian' => 'Satu Ratusan Logam', 'satuan' => 'Keping', 'jumlah' => '', 'nominal' => 100],
    ];
    ?>

    <div class="header">
        <div class="header-left">
            <span class="menu-icon">☰</span>
            <h1>STOK OPNAME</h1>
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
                            <input type="number" name="jumlah_<?php echo $item['no']; ?>" class="input-number" value="<?php echo $item['jumlah']; ?>" min="0">
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
                <input type="number" name="bon_sementara" class="input-field" min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>III. Uang Rusak</label>
                <input type="number" name="uang_rusak" class="input-field" min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>IV. Materai (Lembar @10.000)</label>
                <input type="number" name="material" class="input-field" min="0" step="0.01">
            </div>

            <div class="form-group">
                <label>V. Lain-lain</label>
                <input type="number" name="lain_lain" class="input-field" min="0" step="0.01">
            </div>

            <div class="total-row">
                <span>JUMLAH SALDO FISIK</span>
                <span class="total-value" id="saldo-fisik">Rp. 0,00</span>
            </div>

            <div class="total-row" style="border-bottom: none;">
                <span>SELISIH</span>
                <span class="total-value" id="selisih">Rp. 0,00</span>
            </div>

            <div class="button-group">
                <button type="submit" name="simpan" class="btn btn-primary">Simpan Risk Opname</button>
                <button type="button" class="btn btn-secondary">Kembali</button>
            </div>

            <button type="button" class="btn-table">Lihat Tabel</button>
        </form>
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
     
    <?php
    // Proses form jika di-submit
    if(isset($_POST['simpan'])) {
        // Ambil data dari form
        $bon_sementara = $_POST['bon_sementara'] ?? '';
        $uang_rusak = $_POST['uang_rusak'] ?? '';
        $material = $_POST['material'] ?? '';
        $lain_lain = $_POST['lain_lain'] ?? '';
        
        // Proses data jumlah
        $jumlah_items = [];
        for($i = 1; $i <= 11; $i++) {
            $jumlah_items[$i] = $_POST["jumlah_$i"] ?? '';
        }
        
        // Di sini Anda bisa menambahkan logika untuk menyimpan ke database
        // Contoh:
        // $query = "INSERT INTO stok_opname ...";
        // mysqli_query($conn, $query);
        
        echo "<script>alert('Data berhasil disimpan!');</script>";
    }
    ?>
</body>
</html>