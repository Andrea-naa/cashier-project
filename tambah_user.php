<?php
// Proses jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "cashier");

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $role = $_POST['role'];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke tabel users
    $sql = "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $hashedPassword, $nama_lengkap, $role);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ User berhasil ditambahkan!</p>";
    } else {
        echo "<p style='color:red;'>❌ Gagal menambahkan user: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah User</title>
</head>
<body>
    <h2>Form Tambah User</h2>
    <form method="POST" action="">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Nama Lengkap:</label><br>
        <input type="text" name="nama_lengkap" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="admin">Administrator</option>
            <option value="kasir">Kasir</option>
        </select><br><br>

        <button type="submit">Tambah User</button>
    </form>
</body>
</html>