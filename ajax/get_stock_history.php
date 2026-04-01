<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

$product_id = (int)($_GET['product_id'] ?? 0);

if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM stock_history WHERE product_id = ? ORDER BY id DESC");
        $stmt->execute([$product_id]);
        $history = $stmt->fetchAll();
        echo json_encode($history);
    } catch (Exception $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
