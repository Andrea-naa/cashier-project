<?php
// konfigurasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cashier";

// laporan eoror
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate nomor surat berikutnya (per menu)
 * Format: 001/KODE/XI/2025
 * 
 * @param string $prefix - Prefix surat (KT, KK, KAS)
 * @return array ['nomor' => 'formatted', 'urut' => 1]
 */
function get_next_nomor_surat($prefix = 'KT') {
    global $conn;
    
    // Ambil kode perusahaan dari tabel konfigurasi
    $query_config = "SELECT kode_perusahaan FROM konfigurasi LIMIT 1";
    $result_config = mysqli_query($conn, $query_config);
    $config = mysqli_fetch_assoc($result_config);
    $kode_perusahaan = $config['kode_perusahaan'] ?? 'KSK';
    
    // Buat kode perusahaan
    $kode = $prefix . '-' . $kode_perusahaan;
    
    $tahun = date('Y');
    $bulan = date('n'); // 1-12
    
    // mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Cek apakah sudah ada entri untuk jenis_dokumen tahun dan bulan
        $stmt = $conn->prepare("SELECT counter FROM nomor_surat WHERE jenis_dokumen = ? AND tahun = ? AND bulan = ? FOR UPDATE");
        $stmt->bind_param("sii", $kode, $tahun, $bulan);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            // update counter
            $counter = intval($row['counter']) + 1;
            $stmt = $conn->prepare("UPDATE nomor_surat SET counter = ?, updated_at = NOW() WHERE jenis_dokumen = ? AND tahun = ? AND bulan = ?");
            $stmt->bind_param("isii", $counter, $kode, $tahun, $bulan);
            $stmt->execute();
            $stmt->close();
        } else {
            // input untuk jenis_dokumen bulan atau tahun ini
            $counter = 1;
            $stmt = $conn->prepare("INSERT INTO nomor_surat (jenis_dokumen, tahun, bulan, counter) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $kode, $tahun, $bulan, $counter);
            $stmt->execute();
            $stmt->close();
        }
        
        mysqli_commit($conn);
        
        // Format nomor surat
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $nomor_formatted = sprintf('%03d/%s/%s/%04d', $counter, $kode, $bulan_romawi[$bulan], $tahun);
        
        return [
            'urut' => $counter,
            'nomor' => $nomor_formatted,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'kode' => $kode
        ];
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

/**
 * Get nomor surat terakhir - per menu
 * 
 * @param string $prefix - Prefix surat (KT, KK, KAS)
 * @return string - Nomor surat terakhir atau default
 */
function get_last_nomor_surat($prefix = 'KT') {
    global $conn;
    
    // Ambil kode perusahaan dari tabel konfigurasi
    $query_config = "SELECT kode_perusahaan FROM konfigurasi LIMIT 1";
    $result_config = mysqli_query($conn, $query_config);
    $config = mysqli_fetch_assoc($result_config);
    $kode_perusahaan = $config['kode_perusahaan'] ?? 'KSK';
    
    // bagian kode perusahaan
    $kode = $prefix . '-' . $kode_perusahaan;
    
    $tahun = date('Y');
    $bulan = date('n');
    
    $stmt = $conn->prepare("SELECT counter FROM nomor_surat WHERE jenis_dokumen = ? AND tahun = ? AND bulan = ? LIMIT 1");
    $stmt->bind_param("sii", $kode, $tahun, $bulan);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row) {
        $counter = intval($row['counter']);
        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return sprintf('Nomor Terakhir: %03d/%s/%s/%04d', $counter, $kode, $bulan_romawi[$bulan], $tahun);
    }
    
    $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return sprintf('Nomor Berikutnya: 001/%s/%s/%04d', $kode, $bulan_romawi[$bulan], $tahun);
}

// bagian fungsi umum lainnya
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    // Do NOT use htmlspecialchars here - only escape for display in HTML
    return $data;
}

function sanitize_sql($data) {
    global $conn;
    return $conn->real_escape_string($data);
}

function rupiah_fmt($n) {
    return 'Rp. ' . number_format($n, 0, ',', '.');
}

function log_audit($user_id, $username, $action) {
    global $conn;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, action, ip_address, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $username, $action, $ip_address);
    $stmt->execute();
    $stmt->close();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

function check_admin() {
    global $conn;
    check_login();
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || stripos($user['role'], 'Administrator') === false) {
        die("Akses ditolak. Hanya untuk admin.");
    }
}

function get_user_role($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user['role'] ?? 'Kasir';
}

function get_saldo_kas() {
    global $conn;
    $query = "SELECT 
                (SELECT COALESCE(SUM(nominal), 0) FROM transaksi WHERE jenis_transaksi = 'kas_terima') -
                (SELECT COALESCE(SUM(nominal), 0) FROM transaksi WHERE jenis_transaksi = 'kas_keluar')
              AS saldo";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['saldo'] ?? 0;
}

function validate_number($value, $default = 0) {
    $value = str_replace(['.', ','], ['', '.'], $value);
    return is_numeric($value) ? floatval($value) : $default;
}

// bagian untuk cek apakah data sudah di approve
function is_data_approved($table, $id) {
    global $conn;
    $stmt = $conn->prepare("SELECT is_approved FROM $table WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return ($data && $data['is_approved']== 1);
}

// bagian untuk cek apakah user boleh mengedit atau menghapus data
function can_modify_data ($table, $id, $user_role) {
    // admin boleh selalu mengedit atau hapus
    if (stripos($user_role, 'Administrator')!== false){
        return true;
    }
    // selain admin, hanya boleh jika data belum di approve
    return !is_data_approved($table. $id);
}

// bagian untuk approve data
function approve_data($table, $id, $admin_id, $admin_username) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE $table SET is_approved = 1, approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $admin_id, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        log_audit($admin_id, $admin_username, "Approve data dari tabel $table ID: $id");
    }
    
    return $success;
}

// bagian untuk nolak data
function reject_data($table, $id, $admin_id, $admin_username, $reason = '') {
    global $conn;

    $stmt = $conn->prepare("UPDATE $table SET is_rejected = 1, rejected_by = ?, rejected_at = NOW(), reject_reason = ? WHERE id = ?");
    $stmt->bind_param("isi",$admin_id, $reason, $id);
    $succes = $stmt->execute();
    $stmt->close();

    if ($succes) {
        log_audit($admin_id, $admin_username, "Data di tolak dari tabel $table ID: $id - Reason: $reason");
    }
    
    return $succes;
}

// bagian untuk membatalkan data yang ditolak
function unreject_data($table, $id, $admin_id, $admin_username) {
    global $conn;

    $stmt = $conn->prepare("UPDATE $table SET is_rejected = 0, rejected_by = NULL, rejected_at = NULL, reject_reason = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    $succes = $stmt->execute();
    $stmt->close();

    if ($succes) {
        log_audit($admin_id, $admin_username, "Batalkan data yang ditolak dari tabel $table ID: $id");
    }

    return $success;
}
?>