<?php
// create connection
include "conn_db.php";

// sql to create table
$sql = "ALTER TABLE `stok_opname` 
ADD COLUMN `nomor_surat` VARCHAR(50) NULL AFTER `id`,
ADD INDEX `idx_nomor_surat` (`nomor_surat`)";

if ($conn->query($sql) === TRUE) {
  echo "Table users created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>