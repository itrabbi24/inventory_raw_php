<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/functions.php';

$quotation_id = (int)($_POST['quotation_id'] ?? 0);

if ($quotation_id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Get Quotation Data
        $stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
        $stmt->execute([$quotation_id]);
        $q = $stmt->fetch();

        if (!$q) throw new Exception("Quotation not found.");
        if ($q['status'] === 'converted') throw new Exception("Quotation already converted to sale.");

        // 2. Generate Invoice No
        $invoice_no = generateInvoiceNo($pdo, 'INV', 'sales', 'invoice_no');

        // 3. Insert into Sales
        $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_id, sale_date, subtotal, discount, total_amount, paid_amount, payment_method, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $invoice_no, 
            $q['customer_id'], 
            date('Y-m-d'), 
            $q['subtotal'], 
            $q['discount'], 
            $q['total_amount'], 
            0, // Default paid 0
            'cash', 
            $_SESSION['user_id']
        ]);
        $sale_id = $pdo->lastInsertId();

        // 4. Get Quotation Items
        $stmt = $pdo->prepare("SELECT * FROM quotation_items WHERE quotation_id = ?");
        $stmt->execute([$quotation_id]);
        $items = $stmt->fetchAll();

        // 5. Insert Sale Items & Deduct Stock
        $stmt_sale_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, serial_number, warranty_months) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($items as $item) {
            $stmt_sale_item->execute([
                $sale_id,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price'],
                $item['serial_number'],
                $item['warranty_months']
            ]);
            
            // Deduct Stock
            updateStock($pdo, $item['product_id'], $item['quantity'], 'subtract');
        }

        // 6. Update Quotation Status
        $stmt = $pdo->prepare("UPDATE quotations SET status = 'converted' WHERE id = ?");
        $stmt->execute([$quotation_id]);

        logActivity($pdo, $_SESSION['user_id'], "Quotation #{$q['quotation_no']} converted to Sale #{$invoice_no}", 'sales', $sale_id);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'sale_id' => $sale_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quotation ID']);
}
