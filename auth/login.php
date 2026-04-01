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
            
            // require_once __DIR__ . '/../includes/functions.php'; // now included at the top
            logActivity($pdo, $user['id'], 'User logged in', 'auth', $user['id']);
            
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
        <meta name="description" content="Inventory Management System Login">
        <meta name="author" content="Antigravity">
        <title>Login - <?php echo $settings['company_name']; ?></title>
        
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/img/favicon.png">
        
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
        
        <!-- Fontawesome CSS -->
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/all.min.css">
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/fontawesome.min.css">
        
        <!-- Main CSS -->
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    </head>
    <body class="account-page">
        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <div class="account-content">
                <div class="login-wrapper">
                    <div class="login-content">
                        <div class="login-userset">
                            <div class="login-logo text-center">
                                <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="img" class="mb-3">
                                <h3>Inventory System</h3>
                            </div>
                            <div class="login-userheading">
                                <h3>Sign In</h3>
                                <h4>Please login to your account</h4>
                            </div>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <form action="login.php" method="POST">
                                <div class="form-login">
                                    <label>Email</label>
                                    <div class="form-addons">
                                        <input type="text" name="email" placeholder="Enter your email address" required>
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/mail.svg" alt="img">
                                    </div>
                                </div>
                                <div class="form-login">
                                    <label>Password</label>
                                    <div class="form-addons">
                                        <input type="password" name="password" placeholder="Enter your password" required>
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/lock.svg" alt="img">
                                    </div>
                                </div>
                                <div class="form-login">
                                    <button class="btn btn-login" type="submit">Sign In</button>
                                </div>
                            </form>
                            <div class="login-social">
                                <p class="no-acc">Demo Credential: argrabby@gmail.com / admin123</p>
                                <p class="mt-2 text-center text-muted">Developed by <strong>ARG RABBI</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="login-img">
                        <img src="<?php echo BASE_URL; ?>assets/img/login.jpg" alt="img">
                    </div>
                </div>
            </div>
        </div>
        <!-- /Main Wrapper -->
        
        <!-- jQuery -->
        <script src="<?php echo BASE_URL; ?>assets/js/jquery-3.6.0.min.js"></script>
        <!-- Bootstrap Core JS -->
        <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
        <!-- Custom JS -->
        <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
    </body>
</html>
