<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_POST['table']) || !isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit();
}

$table = $_POST['table'];
$id    = (int)$_POST['id'];

$allowed_tables = ['categories', 'brands', 'vendors', 'customers', 'settings', 'users'];
if (!in_array($table, $allowed_tables)) {
    echo json_encode(['status' => 'error', 'message' => 'Not allowed']);
    exit();
}

try {
    if ($table == 'categories' || $table == 'brands') {
        $name = sanitize($_POST['name'] ?? '');
        $desc = sanitize($_POST['description'] ?? '');
        $stmt = $pdo->prepare("UPDATE `{$table}` SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $desc, $id]);
    } elseif ($table == 'customers' || $table == 'vendors') {
        $name    = sanitize($_POST['name'] ?? '');
        $phone   = sanitize($_POST['phone'] ?? '');
        $email   = sanitize($_POST['email'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $stmt = $pdo->prepare("UPDATE `{$table}` SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $email, $address, $id]);
    }

    
    logActivity($pdo, $_SESSION['user_id'], "Updated record in {$table}", $table, $id);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
