<?php
require_once __DIR__ . '/config/database.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit();
