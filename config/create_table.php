<?php
// create connection
include "conn_db.php";

// sql to create table
$sql = "ALTER TABLE `nomor_surat` 
ADD COLUMN `jenis_dokumen` VARCHAR(20) NOT NULL DEFAULT 'KT-MSL' AFTER `id`,
DROP INDEX `tahun_bulan`,
ADD UNIQUE KEY `unique_nomor` (`jenis_dokumen`, `tahun`, `bulan`);";

if ($conn->query($sql) === TRUE) {
  echo "Table users created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>