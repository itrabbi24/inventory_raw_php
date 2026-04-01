<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $new_stock  = (int)($_POST['new_stock'] ?? 0);
    $reason     = sanitize($_POST['reason'] ?? 'Correction');
    $notes      = sanitize($_POST['notes'] ?? '');

    if ($product_id > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Create stock_history table if not exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS stock_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                old_stock INT NOT NULL,
                new_stock INT NOT NULL,
                change_qty INT NOT NULL,
                type ENUM('In', 'Out', 'Adjustment') DEFAULT 'Adjustment',
                reason VARCHAR(100),
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_by INT
            )");

            // 2. Get current stock
            $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $old_stock = (int)$stmt->fetchColumn();

            $change_qty = $new_stock - $old_stock;

            // 3. Update product
            $stmt = $pdo->prepare("UPDATE products SET current_stock = ? WHERE id = ?");
            $stmt->execute([$new_stock, $product_id]);

            // 4. Record history
            $stmt = $pdo->prepare("INSERT INTO stock_history (product_id, old_stock, new_stock, change_qty, reason, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $old_stock, $new_stock, $change_qty, $reason, $notes, $_SESSION['user_id']]);

            logActivity($pdo, $_SESSION['user_id'], "Stock adjusted for product #{$product_id}: {$old_stock} -> {$new_stock}", 'products', $product_id);

            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
    }
}
