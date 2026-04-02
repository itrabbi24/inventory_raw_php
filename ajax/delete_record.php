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
    // Determine if we should do a soft delete or a hard delete
    // Master tables (items that other things refer to) or items with a 'status' boolean/tinyint
    $soft_delete_tables = ['products', 'categories', 'brands', 'vendors', 'customers', 'users', 'expense_categories', 'depositors'];
    
    if (in_array($table, $soft_delete_tables)) {
        // Soft delete
        $stmt = $pdo->prepare("UPDATE `{$table}` SET status = 0 WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        // Hard delete for transactions/records that don't use 0/1 status or have CASCADE set
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);
    }

    logActivity($pdo, $_SESSION['user_id'], "Deleted record from {$table} (ID: {$id})", $table, $id);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

