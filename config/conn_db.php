<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cashier";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
session_start();
// Fungsi untuk membersihkan input dan mencegah SQL injection
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Fungsi untuk log audit
function log_audit($user_id, $username, $action) {
    global $conn;
    $timestamp = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $query = "INSERT INTO audit_log (user_id, username, action, ip_address, timestamp) 
              VALUES ('$user_id', '$username', '$action', '$ip_address', '$timestamp')";
    
    mysqli_query($conn, $query);
}
?>