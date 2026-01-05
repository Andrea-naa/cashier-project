<?php
// model transaksi
class Transaksi {
    private $conn;
    private $table_name = "transaksi";

    public $id;
    public $nomor_surat;
    public $user_id;
    public $username;
    public $jenis_transaksi;
    public $nominal;
    public $keterangan;
    public $tanggal_transaksi;
    public $is_approved;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generate nomor surat
    private function generateNomorSurat($prefix) {
        // Ambil kode perusahaan untuk keperluan tampilan nomor surat
        $query = "SELECT kode_perusahaan FROM konfigurasi LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $kode_perusahaan = $config['kode_perusahaan'] ?? 'KSK';

        $counterKey = $prefix;
        $displayKode = $prefix . '-' . $kode_perusahaan;
        $tahun = date('Y');
        $bulan = date('n');

        // Ambil atau buat counter untuk kunci ini
        $query = "SELECT counter FROM nomor_surat 
                  WHERE jenis_dokumen = :kode AND tahun = :tahun AND bulan = :bulan 
                  FOR UPDATE";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":kode", $counterKey);
        $stmt->bindParam(":tahun", $tahun);
        $stmt->bindParam(":bulan", $bulan);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $counter = intval($row['counter']) + 1;
            $query = "UPDATE nomor_surat SET counter = :counter, updated_at = NOW() 
                      WHERE jenis_dokumen = :kode AND tahun = :tahun AND bulan = :bulan";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":counter", $counter);
            $stmt->bindParam(":kode", $counterKey);
            $stmt->bindParam(":tahun", $tahun);
            $stmt->bindParam(":bulan", $bulan);
            $stmt->execute();
        } else {
            $counter = 1;
            $query = "INSERT INTO nomor_surat (jenis_dokumen, tahun, bulan, counter) 
                      VALUES (:kode, :tahun, :bulan, :counter)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":kode", $counterKey);
            $stmt->bindParam(":tahun", $tahun);
            $stmt->bindParam(":bulan", $bulan);
            $stmt->bindParam(":counter", $counter);
            $stmt->execute();
        }

        $bulan_romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return sprintf('%03d/%s/%s/%04d', $counter, $displayKode, $bulan_romawi[$bulan], $tahun);
    }

    // fungsi transaksi
    public function create() {
        try {
            $this->conn->beginTransaction();

            // Generate nomor surat
            $prefix = $this->jenis_transaksi === 'kas_terima' ? 'KT' : 'KK';
            $this->nomor_surat = $this->generateNomorSurat($prefix);

            $query = "INSERT INTO " . $this->table_name . "
                      (nomor_surat, user_id, username, jenis_transaksi, nominal, keterangan, is_approved)
                      VALUES (:nomor_surat, :user_id, :username, :jenis_transaksi, :nominal, :keterangan, :is_approved)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":nomor_surat", $this->nomor_surat);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":jenis_transaksi", $this->jenis_transaksi);
            $stmt->bindParam(":nominal", $this->nominal);
            $stmt->bindParam(":keterangan", $this->keterangan);
            $stmt->bindParam(":is_approved", $this->is_approved);

            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                
                // log audit
                $this->logAudit();
                
                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;

        } catch(Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // fungsi log audit
    private function logAudit() {
        $action = ($this->jenis_transaksi === 'kas_terima' ? 'Kas Masuk' : 'Kas Keluar') . 
                  ' #' . $this->nomor_surat . ': Rp. ' . number_format($this->nominal, 0, ',', '.');
        
        $query = "INSERT INTO audit_log (user_id, username, action, timestamp) 
                  VALUES (:user_id, :username, :action, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":action", $action);
        $stmt->execute();
    }

    // fungsi buat baca semua data
    public function read($filter = []) {
        $query = "SELECT t.*, u.nama_lengkap as created_by_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE 1=1";

        if (!empty($filter['jenis_transaksi'])) {
            $query .= " AND t.jenis_transaksi = :jenis_transaksi";
        }

        if (!empty($filter['date_from'])) {
            $query .= " AND DATE(t.tanggal_transaksi) >= :date_from";
        }

        if (!empty($filter['date_to'])) {
            $query .= " AND DATE(t.tanggal_transaksi) <= :date_to";
        }

        if (isset($filter['is_approved'])) {
            $query .= " AND t.is_approved = :is_approved";
        }

        $query .= " ORDER BY t.tanggal_transaksi DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($filter['jenis_transaksi'])) {
            $stmt->bindParam(":jenis_transaksi", $filter['jenis_transaksi']);
        }
        if (!empty($filter['date_from'])) {
            $stmt->bindParam(":date_from", $filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $stmt->bindParam(":date_to", $filter['date_to']);
        }
        if (isset($filter['is_approved'])) {
            $stmt->bindParam(":is_approved", $filter['is_approved']);
        }

        $stmt->execute();
        return $stmt;
    }

    // fungsi buat baca satu data
    public function readOne() {
        $query = "SELECT t.*, u.nama_lengkap as created_by_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.user_id = u.id
                  WHERE t.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nomor_surat = $row['nomor_surat'];
            $this->user_id = $row['user_id'];
            $this->username = $row['username'];
            $this->jenis_transaksi = $row['jenis_transaksi'];
            $this->nominal = $row['nominal'];
            $this->keterangan = $row['keterangan'];
            $this->tanggal_transaksi = $row['tanggal_transaksi'];
            $this->is_approved = $row['is_approved'];
            return true;
        }

        return false;
    }

    // fungsi update
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nominal = :nominal,
                      keterangan = :keterangan
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nominal", $this->nominal);
        $stmt->bindParam(":keterangan", $this->keterangan);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // fungsi delete
    public function delete() {
        try {
            $this->conn->beginTransaction();
            
            // Ambil nomor surat dan data lainnya sebelum dihapus
            $query = "SELECT nomor_surat, tanggal_transaksi FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return false;
            }
            
            $nomor_surat = $row['nomor_surat'];
            $tanggal_transaksi = $row['tanggal_transaksi'];
            
            // Extract tahun dan bulan dari tanggal transaksi
            $date = new DateTime($tanggal_transaksi);
            $tahun = $date->format('Y');
            $bulan = $date->format('n');
            
            if (strpos($nomor_surat, '/KT-') !== false) {
                $prefix = 'KT';
            } else {
                $prefix = 'KK';
            }
            
            // Hapus data transaksi
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            
            if(!$stmt->execute()) {
                $this->conn->rollBack();
                return false;
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>