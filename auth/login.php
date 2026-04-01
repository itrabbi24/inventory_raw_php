<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $valid = false;
        if ($user) {
            // Support both SHA2-256 and password_verify
            if (strlen($user['password']) === 64) {
                $valid = (hash('sha256', $password) === $user['password']);
            } else {
                $valid = password_verify($password, $user['password']);
            }
        }

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['email']   = $user['email'];
            
            require_once __DIR__ . '/../includes/functions.php';
            logActivity($pdo, $user['id'], 'User logged in', 'auth', $user['id']);
            
            // 1. Run database migrations
            runMigrations($pdo);

            // 2. Check for Git Updates if enabled
            $settings = getSettings($pdo);
            if (checkGitUpdates($pdo, $settings)) {
                header("Location: " . BASE_URL . "auth/update_progress.php");
                exit();
            }
            
            header('Location: ' . BASE_URL . 'dashboard/index.php');

            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$settings = getSettings($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Inventory Management System - Modern Login">
    <title>Login - <?php echo $settings['company_name']; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap');

        body {
            background: linear-gradient(135deg, #FF9F43 0%, #1B2850 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            overflow: hidden;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-logo img {
            max-width: 160px;
            margin-bottom: 25px;
        }
        .login-header h3 {
            font-weight: 800;
            color: #1B2850;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #637381;
            font-size: 15px;
            margin-bottom: 35px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 22px;
        }
        .form-group label {
            font-weight: 600;
            color: #1B2850;
            margin-bottom: 10px;
            font-size: 14px;
            display: block;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i.prefix-icon {
            position: absolute;
            left: 18px;
            color: #FF9F43;
            font-size: 18px;
        }
        .form-control {
            padding-left: 50px;
            height: 54px;
            border-radius: 14px;
            border: 2px solid #F0F0F0;
            background: #FAFAFA;
            font-weight: 600;
            color: #1B2850;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: #FFF;
            border-color: #FF9F43;
            box-shadow: 0 0 0 4px rgba(255, 159, 67, 0.15);
        }
        .pass-toggle {
            position: absolute;
            right: 18px;
            color: #A0AEC0;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        .pass-toggle:hover {
            color: #FF9F43;
        }
        .login-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .login-footer a {
            color: #FF9F43;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-login {
            background: #FF9F43;
            border: none;
            color: #fff;
            width: 100%;
            height: 54px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 17px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(255, 159, 67, 0.4);
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #FE820E;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(255, 159, 67, 0.5);
            color: #fff;
        }
        .alert {
            border-radius: 14px;
            border: none;
            padding: 15px;
            font-size: 14px;
            margin-bottom: 25px;
            text-align: left;
        }
        .demo-box {
            background: #F8FAFC;
            border-radius: 16px;
            padding: 20px;
            margin-top: 35px;
            border: 1px dashed #E2E8F0;
        }
        .demo-box p {
            margin-bottom: 5px;
            font-size: 12px;
            color: #64748B;
        }
        .demo-box p strong {
            color: #475569;
        }
        .dev-info {
            margin-top: 25px;
            font-size: 12px;
            color: #94A3B8;
        }
        .form-check-input:checked {
            background-color: #FF9F43;
            border-color: #FF9F43;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-logo">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo">
        </div>
        
        <div class="login-header">
            <h3>Welcome Back</h3>
            <p>Please log in to your account to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger fade show">
                <i class="fas fa-times-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope prefix-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="admin@example.com" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock prefix-icon"></i>
                    <input type="password" name="password" class="form-control pass-input" placeholder="Your Password" required>
                    <i class="fas fa-eye-slash pass-toggle toggle-password"></i>
                </div>
            </div>

            <div class="login-footer">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remMe">
                    <label class="form-check-label text-muted" for="remMe">Remember Me</label>
                </div>
                <a href="javascript:void(0);">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-login">Sign In Now</button>
        </form>

        <div class="demo-box mt-4">
            <p class="mb-3 small fw-bold text-muted text-uppercase">Quick Demo Login</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <button type="button" class="btn btn-outline-warning btn-sm fw-bold px-3 py-1" onclick="fillDemo('argrabby@gmail.com', 'admin123')" title="Administrator">
                    <i class="fas fa-user-shield me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-outline-info btn-sm fw-bold px-3 py-1" onclick="fillDemo('salesman@example.com', 'sales123')" title="Sales Person">
                    <i class="fas fa-shopping-cart me-1"></i> Sales
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm fw-bold px-3 py-1" onclick="fillDemo('stock@example.com', 'stock123')" title="Stock Manager">
                    <i class="fas fa-boxes me-1"></i> Stock
                </button>
                <button type="button" class="btn btn-outline-success btn-sm fw-bold px-3 py-1" onclick="fillDemo('accountant@example.com', 'acc123')" title="Accountant">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Account
                </button>
            </div>
            <p class="mt-3 small text-muted border-top pt-2">Default: <span class="text-dark fw-bold">argrabby@gmail.com / admin123</span></p>
        </div>

        <div class="dev-info">
            Inventory Management System | Developed by <strong>ARG RABBI</strong>
        </div>
    </div>

    <!-- jQuery & Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS (For Password Toggle) -->
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    <script>
        function fillDemo(email, pass) {
            $('input[name="email"]').val(email);
            $('input[name="password"]').val(pass);
            
            // Subtle animation for visual feedback
            $('.login-card').addClass('animate__animated animate__pulse');
            setTimeout(() => {
                $('.login-card').removeClass('animate__animated animate__pulse');
            }, 1000);
        }
    </script>
</body>
</html>
