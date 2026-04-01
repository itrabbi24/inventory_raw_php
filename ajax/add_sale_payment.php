<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$sale_id = (int)($_POST['sale_id'] ?? 0);
$amount  = (float)($_POST['amount'] ?? 0);
$method  = sanitize($_POST['method'] ?? 'cash');
$note    = sanitize($_POST['note'] ?? '');
$date    = sanitize($_POST['payment_date'] ?? date('Y-m-d'));

if ($sale_id <= 0 || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Get current due amount
    $stmt = $pdo->prepare("SELECT total_amount, paid_amount FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();

    if (!$sale) {
        throw new Exception("Sale record not found");
    }

    $due = $sale['total_amount'] - $sale['paid_amount'];
    if ($amount > ($due + 0.01)) { // Small buffer for float issues
        throw new Exception("Payment amount exceeds due amount ($due)");
    }

    // 2. Insert into sale_payments
    $stmt_pay = $pdo->prepare("INSERT INTO sale_payments (sale_id, payment_date, amount, method, note, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_pay->execute([$sale_id, $date, $amount, $method, $note, $_SESSION['user_id']]);

    // 3. Update sales table's paid_amount
    $stmt_upd = $pdo->prepare("UPDATE sales SET paid_amount = paid_amount + ? WHERE id = ?");
    $stmt_upd->execute([$amount, $sale_id]);

    logActivity($pdo, $_SESSION['user_id'], "Added payment for Sale ID: {$sale_id}, Amount: {$amount}", 'sales', $sale_id);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Payment updated successfully!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
