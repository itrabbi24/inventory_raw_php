<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'Invalid Product ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        echo json_encode([
            'success' => true,
            'price'   => $product['selling_price'],
            'buying_price' => $product['buying_price'],
            'stock'   => $product['current_stock']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
