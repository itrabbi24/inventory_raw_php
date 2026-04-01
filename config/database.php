<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'inventory_db');
// Dynamic BASE_URL calculation
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$current_dir = str_replace('\\', '/', __DIR__);
$path = str_replace($doc_root, '', $current_dir);
$base_path = str_replace('/config', '', $path);
define('BASE_URL', $protocol . $host . $base_path . '/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// Role permissions: module => [allowed roles]
define('ROLE_PERMISSIONS', [
    'users'      => ['superadmin', 'admin'],
    'settings'   => ['superadmin', 'admin'],
    'reports'    => ['superadmin', 'admin', 'manager'],
    'stock'      => ['superadmin', 'admin', 'manager'],
    'sales'      => ['superadmin', 'admin', 'manager', 'salesman'],
    'challan'    => ['superadmin', 'admin', 'manager', 'salesman'],
    'quotation'  => ['superadmin', 'admin', 'manager', 'salesman'],
    'deposit'    => ['superadmin', 'admin', 'manager'],
    'products'   => ['superadmin', 'admin', 'manager', 'salesman'],
    'vendors'    => ['superadmin', 'admin', 'manager'],
    'customers'  => ['superadmin', 'admin', 'manager', 'salesman'],
]);

function hasPermission(string $module): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['role'])) return false;
    
    // Superadmin has access to everything
    if ($_SESSION['role'] === 'superadmin') return true;

    $perms = ROLE_PERMISSIONS;
    if (!isset($perms[$module])) return true; // no restriction defined
    return in_array($_SESSION['role'], $perms[$module]);
}
