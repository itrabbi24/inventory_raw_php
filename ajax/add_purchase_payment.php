<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$stock_id = (int)($_POST['stock_in_id'] ?? 0);
$amount   = (float)($_POST['amount'] ?? 0);
$method   = sanitize($_POST['method'] ?? 'cash');
$note     = sanitize($_POST['note'] ?? '');
$date     = sanitize($_POST['payment_date'] ?? date('Y-m-d'));

if ($stock_id <= 0 || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Get current due amount
    $stmt = $pdo->prepare("SELECT total_price, paid_amount FROM stock_in WHERE id = ?");
    $stmt->execute([$stock_id]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        throw new Exception("Purchase record not found");
    }

    $due = $purchase['total_price'] - $purchase['paid_amount'];
    if ($amount > ($due + 0.01)) {
        throw new Exception("Payment exceeds due amount ($due)");
    }

    // 2. Insert into purchase_payments
    $stmt_pay = $pdo->prepare("INSERT INTO purchase_payments (stock_in_id, payment_date, amount, method, note, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_pay->execute([$stock_id, $date, $amount, $method, $note, $_SESSION['user_id']]);

    // 3. Update stock_in's paid_amount
    $stmt_upd = $pdo->prepare("UPDATE stock_in SET paid_amount = paid_amount + ? WHERE id = ?");
    $stmt_upd->execute([$amount, $stock_id]);

    logActivity($pdo, $_SESSION['user_id'], "Added payment for Purchase ID: {$stock_id}, Amount: {$amount}", 'stock', $stock_id);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Purchase payment recorded successfully!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
