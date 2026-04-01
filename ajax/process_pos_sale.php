<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

try {
    $pdo->beginTransaction();

    $customer_id    = (int)($data['customer_id'] ?? 0);
    $discount       = (float)($data['discount'] ?? 0);
    $subtotal       = (float)($data['subtotal'] ?? 0);
    $grand_total    = (float)($data['grand_total'] ?? 0);
    $paid_amount    = (float)($data['grand_total'] ?? 0); // POS usually fully paid
    $payment_method = 'cash';
    $invoice_no     = generateInvoiceNo($pdo, 'INV', 'sales', 'invoice_no');
    $sale_date      = date('Y-m-d');

    // 1. Insert into sales table
    $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_id, sale_date, subtotal, discount, total_amount, paid_amount, payment_method, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)");
    $stmt->execute([
        $invoice_no, 
        $customer_id == 0 ? null : $customer_id, 
        $sale_date, 
        $subtotal, 
        $discount, 
        $grand_total, 
        $paid_amount, 
        $payment_method, 
        $_SESSION['user_id']
    ]);
    
    $sale_id = $pdo->lastInsertId();

    // 2. Insert items and update stock
    $stmt_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, cost_price, quantity, unit_price) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($data['cart'] as $item) {
        $pid = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];

        // Get current buying price to store in sale record
        $p_info = $pdo->prepare("SELECT buying_price FROM products WHERE id = ?");
        $p_info->execute([$pid]);
        $buying_price = (float)$p_info->fetchColumn();

        $stmt_item->execute([$sale_id, $pid, $buying_price, $qty, $price]);
        
        // Update stock
        updateStock($pdo, $pid, $qty, 'subtract');
    }

    logActivity($pdo, $_SESSION['user_id'], "POS sale created: {$invoice_no}", 'sales', $sale_id);
    
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Sale completed successfully!', 
        'invoice_no' => $invoice_no,
        'sale_id' => $sale_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
