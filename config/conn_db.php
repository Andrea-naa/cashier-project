<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cashier";

// Create connection with error reporting
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
 * Generate nomor surat berikutnya (INDEPENDENT per menu)
 * Format: 001/KODE/XI/2025
 * 
 * @param string $kode - Kode surat (KT-MSL, KK-MSL, KAS-MSL)
 * @return array ['nomor' => 'formatted', 'urut' => 1]
 */
function get_next_nomor_surat($kode = 'KT-MSL') {
    global $conn;
    
    $tahun = date('Y');
    $bulan = date('n'); // 1-12
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Lock row untuk avoid race condition - BERDASARKAN JENIS_DOKUMEN
        $stmt = $conn->prepare("SELECT counter FROM nomor_surat WHERE jenis_dokumen = ? AND tahun = ? AND bulan = ? FOR UPDATE");
        $stmt->bind_param("sii", $kode, $tahun, $bulan);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            // Update counter
            $counter = intval($row['counter']) + 1;
            $stmt = $conn->prepare("UPDATE nomor_surat SET counter = ?, updated_at = NOW() WHERE jenis_dokumen = ? AND tahun = ? AND bulan = ?");
            $stmt->bind_param("isii", $counter, $kode, $tahun, $bulan);
            $stmt->execute();
            $stmt->close();
        } else {
            // Insert baru untuk jenis_dokumen/bulan/tahun ini
            $counter = 1;
            $stmt = $conn->prepare("INSERT INTO nomor_surat (jenis_dokumen, tahun, bulan, counter) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $kode, $tahun, $bulan, $counter);
            $stmt->execute();
            $stmt->close();
        }
        
        // Commit
        mysqli_commit($conn);
        
        // Format nomor
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
 * Get nomor surat terakhir (untuk display saja) - PER MENU
 * 
 * @param string $kode - Kode surat (KT-MSL, KK-MSL, KAS-MSL)
 * @return string - Nomor surat terakhir atau default
 */
function get_last_nomor_surat($kode = 'KT-MSL') {
    global $conn;
    
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
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
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
?>