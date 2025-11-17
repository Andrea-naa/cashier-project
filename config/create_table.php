<?php
// create connection
include "conn_db.php";

// sql to create table
$sql = "CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
  echo "Table users created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>