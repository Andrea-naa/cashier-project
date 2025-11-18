<?php
session_start();
require_once 'config/conn_db.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            // simpan role ke session jika ada
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'Kasir';
            
            // Log audit
            log_audit($user['id'], $user['username'], 'Login berhasil');
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}

// Get konfigurasi
$query_config = "SELECT * FROM konfigurasi LIMIT 1";
$result_config = mysqli_query($conn, $query_config);
$config = mysqli_fetch_assoc($result_config);

// Cek apakah show login form
$show_login = isset($_GET['login']) || $_SERVER['REQUEST_METHOD'] == 'POST';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Kas Kebun</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            /* font-family: 'monsterat','open-sans','open-instrument-sans'; */
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('assets/gambar/tampilanmasukbg.jpg');
            background-size: cover;
        }
        
        /* Halaman Tampilan Masuk */
        .welcome-screen {
            background: none; 
            padding: 20px 0; 
            border-radius: 0;
            box-shadow: none;
            text-align: center;
            max-width: 420px;
            width: 100%;
            font-family: 'open-sans';
        }
        
        .logo-container {
            margin-bottom: 30px;
        }
        
        .logo-container img {
            width: 320px; 
            height: auto;
        }
        
        .company-name {
            font-size: 27px; 
            font-weight: 800;
            color: #2d7c3e;
            margin: 18px 0 28px 0;
            line-height: 1.2;
        }
        
        .btn-login {
            background: #2d7c3e;
            color: white;
            padding: 15px 60px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-login:hover {
            background: #246630;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(45, 124, 62, 0.4);
        }
        
        /* Halaman Form Login */
        .login-screen {
            width: 100%;
            max-width: 450px;
            position: relative;
            font-family: 'monsterat';
        }
        
        .login-background {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('assets/gambar/halamanloginbg.jpg');
            background-size: cover;
            background-position: center;
            z-index: -1;
        }
        
        .login-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }
        
        .login-box {
            background: #2d7c3e;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .login-header {
            position: fixed;
            top: 20px;
            left: 20px;
            background: transparent; 
            padding: 8px 12px; 
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            box-shadow: none;
            z-index: 9999; 
            justify-content: flex-start;
        }
        
        .login-header img {
            width: 60px;
            height: auto;
        }
        
        .company-info {
            text-align: left;
        }
        
        .company-info h3 {
            font-size: 12px;
            color: #000; 
            font-weight: 800; 
            line-height: 1.3;
            margin: 0;
        }
        
        .company-info p {
            font-size: 10px;
            color: #000;
            font-weight: 400;
            margin: 3px 0 0 0;
        }
        
        .login-title {
            background: #2d7c3e;
            color: #ffffff;
            display: flex;
            align-items: flex-end; 
            justify-content: center;
            height: 80px; 
            padding-bottom: 10px; 
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        
        .login-form {
            background: #2d7c3e;
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2d7c3e;
            box-shadow: 0 0 0 3px rgba(45, 124, 62, 0.1);
        }
        
        .btn-submit {
            font-family: 'montserrat';
            width: 100%;
            background: white;
            color: black;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: grey;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(45, 124, 62, 0.4);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: black;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            color: grey;
        }
        
        /* Hide/Show */
        .hidden {
            display: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-screen,
            .login-screen {
                margin: 20px;
            }
            
            .logo-container img {
                width: 140px;
            }
            
            .company-name {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <?php if (!$show_login): ?>
    <!-- Halaman Tampilan Masuk -->
    <div class="welcome-screen">
        <div class="logo-container">
            <!-- logo KSK -->
            <img src="assets/gambar/logoksk.jpg" alt="Logo KSK" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22180%22 height=%22180%22%3E%3Crect width=%22180%22 height=%22180%22 fill=%22%232d7c3e%22 rx=%2220%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2260%22 fill=%22white%22%3EKSK%3C/text%3E%3C/svg%3E'">
        </div>
        
        <h1 class="company-name">
            KALIMANTAN SAWIT KUSUMA
        </h1>
        
        <a href="?login=1" class="btn-login">LOGIN</a>
    </div>
    
    <?php else: ?>
    <!-- Background Image -->
    <div class="login-background"></div>
    
    <!-- Halaman Form Login -->
    <div class="login-screen">
        <div class="login-box">
            <div class="login-header">
                <img src="assets/gambar/logoksk.jpg" alt="Logo KSK"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect width=%2260%22 height=%2260%22 fill=%22%232d7c3e%22 rx=%2210%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2224%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
                <div class="company-info">
                    <h3>KALIMANTAN SAWIT KUSUMA GROUP</h3>
                    <p>Oil Palm Plantation & Industries</p>
                </div>
            </div>
            
            <div class="login-title">LOGIN</div>
            
            <div class="login-form">
                <?php if ($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username"></label>
                        <input type="text" id="username" name="username" 
                               placeholder="Masukkan username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"></label>
                        <input type="password" id="password" name="password" 
                               placeholder="Masukkan password" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">LOGIN</button>
                </form>
                
                <div class="back-link">
                    <a href="index.php">Kembali</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>