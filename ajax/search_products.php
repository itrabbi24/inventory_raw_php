<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

// If query is empty or 'all', we will return all results
$search_query = "%$query%";
if ($query === 'all' || empty($query)) {
    $search_query = "%";
}

try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.model, p.purchase_price, p.current_stock, p.category_id, p.image, c.name as category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.name LIKE ? OR p.model LIKE ?) AND p.status = 1
        LIMIT 20
    ");
    $stmt->execute([$search_query, $search_query]);
    $products = $stmt->fetchAll();
    
    echo json_encode($products);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
