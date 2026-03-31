<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
session_start();

if (isset($_SESSION['user_id'])) {
    logActivity($pdo, $_SESSION['user_id'], 'User logged out', 'auth', $_SESSION['user_id']);
}

session_unset();
session_destroy();

header('Location: ' . BASE_URL . 'auth/login.php');
exit();
