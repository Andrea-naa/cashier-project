<?php
// create connection
include "conn_db.php";

// Create database
$sql = "CREATE DATABASE cashier";
if ($conn->query($sql) === TRUE) {
  echo "Database cashier created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

// close connection
$conn->close();
?>