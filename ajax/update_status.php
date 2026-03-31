<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$table = $_POST['table'] ?? '';
$id    = (int)($_POST['id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');

$allowedTables = ['challan', 'quotations', 'sales', 'stock_in'];

if (in_array($table, $allowedTables) && $id > 0 && !empty($status)) {
    try {
        $stmt = $pdo->prepare("UPDATE `{$table}` SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        logActivity($pdo, $_SESSION['user_id'], "Updated status of {$table} ID {$id} to {$status}", $table, $id);
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
}
?>
