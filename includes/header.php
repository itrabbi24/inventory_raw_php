<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/functions.php';

$settings = getSettings($pdo);
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Inventory Management System">
    <meta name="keywords" content="inventory, management, system, electronics, computer">
    <meta name="author" content="Antigravity">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $settings['company_name']; ?> - <?php echo $pageTitle; ?></title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/img/favicon.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/fontawesome.min.css">

    <!-- Animation CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/animate.css">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/select2/css/select2.min.css">

    <!-- Datatable CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dataTables.bootstrap4.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

</head>
<body>
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        <div class="header">

            <!-- Logo -->
            <div class="header-left active">
                <a href="<?php echo BASE_URL; ?>dashboard/index.php" class="logo">
                    <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="">
                </a>
                <a href="<?php echo BASE_URL; ?>dashboard/index.php" class="logo-small">
                    <img src="<?php echo BASE_URL; ?>assets/img/logo-small.png" alt="">
                </a>
                <a id="toggle_btn" href="javascript:void(0);">
                </a>
            </div>
            <!-- /Logo -->

            <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </a>

            <!-- Header Menu -->
            <ul class="nav user-menu">

                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                        <span class="user-img"><img src="<?php echo BASE_URL; ?>assets/img/profiles/user-avatar.png" alt="">
                        <span class="status online"></span></span>
                    </a>
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img"><img src="<?php echo BASE_URL; ?>assets/img/profiles/user-avatar.png" alt="">
                                <span class="status online"></span></span>
                                <div class="profilesets">
                                    <h6><?php echo $_SESSION['name']; ?></h6>
                                    <h5><?php echo ucfirst($_SESSION['role']); ?></h5>
                                </div>
                            </div>
                            <hr class="m-0">
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php"> <i class="me-2"  data-feather="log-out"></i>Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
            <!-- /Header Menu -->

            <!-- Mobile Menu -->
            <div class="dropdown mobile-user-menu">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
                </div>
            </div>
            <!-- /Mobile Menu -->
        </div>
        <!-- /Header -->
