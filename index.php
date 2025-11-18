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

$show_login = isset($_GET['login']) || $_SERVER['REQUEST_METHOD'] == 'POST';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Kas Kebun</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            position: relative;
        }
        
        /* Welcome Screen Background */
        body.welcome-bg {
            background-image: url('assets/gambar/tampilanmasukbg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        body.welcome-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.25);
        }
        
        /* Welcome Screen */
        .welcome-screen {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 1000px;
            width: 90%;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            margin-bottom: 40px;
            animation: logoFloat 3s ease-in-out infinite;
        }
        
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .logo-container img {
            width: 270px;
            height: 270px;
        }
        
        .logo-container img:hover {
            transform: scale(1.05);
        }
        
        .company-name {
            font-size: 35px;
            font-weight: 800;
            color: #009844;
            margin: 25px 0 35px 0;
            line-height: 1.3;
            /* text-shadow:  */
                /* 0 2px 20px rgba(0, 0, 0, 0.5),
                0 4px 40px rgba(45, 124, 62, 0.6); */
            letter-spacing: 1px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #009844 0%, #00320e 100%);
            color: #FFFFFFFF;
            padding: 18px 70px;
            border: none;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(168, 255, 120, 0.4), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 40px rgba(255, 255, 255, 0.5);
        }
        
        /* Login Screen Background */
        body.login-bg {
            background-image: url('assets/gambar/halaman_login.png');
            background-size: cover;
            background-position: center;
        }
        
        body.login-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        
        /* Login Screen */
        .login-screen {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .input-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        /* Logo & Company Info - Top Left */
        .top-left-info {
            position: fixed;
            top: 30px;
            left: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 100;
            animation: slideInLeft 0.6s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .top-left-info img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            /* box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3); */
            /* object-fit: cover; */
            /* border: 2px solid rgba(255, 255, 255, 0.3); */
        }
        
        .company-info {
            display: flex;
            flex-direction: column;
        }
        
        .company-info h3 {
            font-size: 14px;
            color: #000000FF;
            font-weight: 700;
            line-height: 1.3;
            margin: 0;
            /* text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5); */
        }
        
        .company-info p {
            font-size: 10px;
            color: #000000FF;
            font-weight: 500;
            margin: 1px;
        }
        
        
        /* Login Box - Simple & Clean */
        .login-box {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.98) 0%, rgba(240, 255, 240, 0.98) 100%);
            backdrop-filter: blur(25px);
            border-radius: 30px;
            padding: 35px 35px 30px 35px;
            width: 90%;
            max-width: 450px;
            box-shadow: 
                0 35px 80px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(45, 124, 62, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 1),
                0 0 60px rgba(45, 124, 62, 0.15);
            border: 2px solid rgba(45, 124, 62, 0.2);
            animation: fadeInScale 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            /* background: linear-gradient(90deg, #0d5e2a 0%, #2d7c3e 50%, #3fb950 100%); */
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .login-title {
    font-size: 36px;
    font-weight: 700;
    color: #1f6e3e;
    text-align: center;
    margin-bottom: 5px;
}

.title-underline {
    width: 110px; /* SAMA DENGAN LEBAR TEKS LOGIN */
    height: 6px;
    margin: 0 auto 25px auto;
    border-radius: 6px;
    background: linear-gradient(to right, #0d5e2a, #3fb950);
    box-shadow: 0 8px 18px rgba(0, 128, 0, 0.25);
}

        
        .login-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background: linear-gradient(135deg, #0d5e2a 0%, #3fb950 100%);
            border-radius: 3px;
            box-shadow: 0 3px 10px rgba(45, 124, 62, 0.3);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #1a4d2e;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .form-group input {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border: 2px solid #d4edda;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff 0%, #f8fff8 100%);
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2d7c3e;
            background: #ffffff;
            box-shadow: 
                0 0 0 4px rgba(45, 124, 62, 0.12),
                0 8px 25px rgba(45, 124, 62, 0.15);
            transform: translateY(-2px);
        }
        
        .form-group input:focus + .input-icon {
            color: #2d7c3e;
            transform: translateY(-50%) scale(1.1);
        }
        
        .form-group input::placeholder {
            color: #a0baa8;
        }
        
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #0d5e2a 0%, #2d7c3e 50%, #3fb950 100%);
            color: white;
            padding: 17px;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            box-shadow: 
                0 12px 30px rgba(13, 94, 42, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 0 20px rgba(45, 124, 62, 0.2);
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: left 0.6s;
        }
        
        .btn-submit:hover::before {
            left: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-4px);
            box-shadow: 
                0 18px 40px rgba(13, 94, 42, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.3),
                0 0 35px rgba(63, 185, 80, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(-2px);
        }
        
        .error-message {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            color: #c33;
            padding: 15px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 5px solid #c33;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 5px 20px rgba(204, 51, 51, 0.2);
            animation: shake 0.5s ease-in-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-link a {
            color: #2d7c3e;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
        }
        
        .back-link a:hover {
            color: #0d5e2a;
            background: linear-gradient(135deg, rgba(45, 124, 62, 0.08) 0%, rgba(63, 185, 80, 0.08) 100%);
            transform: translateX(-3px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .top-left-info {
                top: 20px;
                left: 20px;
                gap: 12px;
            }
            
            .top-left-info img {
                width: 50px;
                height: 50px;
            }
            
            .company-info h3 {
                font-size: 12px;
            }
            
            .company-info p {
                font-size: 9px;
            }
            
            .login-box {
                padding: 40px 30px;
            }
            
            .login-title {
                font-size: 28px;
            }
            
            .company-name {
                font-size: 26px;
            }
            
            .logo-container img {
                width: 160px;
                height: 160px;
            }
        }
    </style>
</head>
<body class="<?php echo $show_login ? 'login-bg' : 'welcome-bg'; ?>">
    <?php if (!$show_login): ?>
    <!-- Welcome Screen -->
    <div class="welcome-screen">
        <div class="logo-container">
<<<<<<< HEAD
            <!-- logo KSK -->
=======
>>>>>>> 3534b44e270ace00b70af867f33046146bb439e9
            <img src="assets/gambar/logoksk.jpg" alt="Logo KSK" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Cdefs%3E%3ClinearGradient id=%22grad%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%232d7c3e;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%233fb950;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width=%22200%22 height=%22200%22 fill=%22url(%23grad)%22 rx=%2230%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2280%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
        </div>
        
        <h1 class="company-name">
            KALIMANTAN SAWIT KUSUMA
        </h1>
        
        <a href="?login=1" class="btn-login">LOGIN</a>

    </div>
    
    <?php else: ?>
    <!-- Login Screen -->
    <div class="login-screen">
<<<<<<< HEAD
        <div class="login-box">
            <div class="login-header">
                <img src="assets/gambar/logoksk.jpg" alt="Logo KSK"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Crect width=%2260%22 height=%2260%22 fill=%22%232d7c3e%22 rx=%2210%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2224%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
                <div class="company-info">
                    <h3>KALIMANTAN SAWIT KUSUMA GROUP</h3>
                    <p>Oil Palm Plantation & Industries</p>
                </div>
=======
        <!-- Logo & Company Info - Top Left -->
        <div class="top-left-info">
            <img src="assets/gambar/logoksk.jpg" alt="Logo KSK"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22%3E%3Cdefs%3E%3ClinearGradient id=%22grad2%22 x1=%220%25%22 y1=%220%25%22 x2=%22100%25%22 y2=%22100%25%22%3E%3Cstop offset=%220%25%22 style=%22stop-color:%232d7c3e;stop-opacity:1%22 /%3E%3Cstop offset=%22100%25%22 style=%22stop-color:%233fb950;stop-opacity:1%22 /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width=%2260%22 height=%2260%22 fill=%22url(%23grad2)%22 rx=%2212%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-size=%2224%22 fill=%22white%22 font-weight=%22bold%22%3EKSK%3C/text%3E%3C/svg%3E'">
            <div class="company-info">
                <h3>KALIMANTAN SAWIT KUSUMA GROUP</h3>
                <p>Oil Palm Plantation & Industries</p>
>>>>>>> 3534b44e270ace00b70af867f33046146bb439e9
            </div>
        </div>
        
        <!-- Login Box -->
        <div class="login-box">
            <h2 class="login-title">LOGIN</h2>
            <div class="title-underline"></div>

            <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" 
                               placeholder="Masukkan username" required autofocus>
                        <img src="assets/gambar/icon/user.png" class="input-icon" alt="User Icon">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Masukkan password" required>
                        <img src="assets/gambar/icon/password.png" class="input-icon" alt="User Icon">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Login</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>