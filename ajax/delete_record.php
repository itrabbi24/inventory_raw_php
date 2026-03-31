<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['table']) || !isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit();
}

$table = $_GET['table'];
$id    = (int)$_GET['id'];

// Whitelist tables to prevent SQL injection
$allowed_tables = ['products', 'categories', 'brands', 'vendors', 'customers', 'sales', 'stock_in', 'challan', 'quotations', 'users', 'depositors', 'depositor_transactions', 'expense_categories', 'expenses'];

if (!in_array($table, $allowed_tables)) {
    echo json_encode(['status' => 'error', 'message' => 'Table not allowed']);
    exit();
}

try {
    // For products, categories, brands, etc., we do soft delete by setting status = 0
    // For others like transactions, we might delete or soft delete
    $stmt = $pdo->prepare("UPDATE `{$table}` SET status = 0 WHERE id = ?");
    $stmt->execute([$id]);

    logActivity($pdo, $_SESSION['user_id'], "Deleted record from {$table}", $table, $id);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
