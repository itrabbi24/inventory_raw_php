<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id     = (int)($_POST['product_id'] ?? 0);
    $vendor_id      = (int)($_POST['vendor_id'] ?? 0);
    $invoice        = sanitize($_POST['invoice_no'] ?? '');
    $serial         = sanitize($_POST['serial_number'] ?? '');
    $warranty       = (int)($_POST['warranty_months'] ?? 0);
    $quantity       = (int)($_POST['quantity'] ?? 1);
    $price          = (float)($_POST['purchase_price'] ?? 0);
    $shipping       = (float)($_POST['shipping_charge'] ?? 0);
    $date           = sanitize($_POST['purchase_date'] ?? date('Y-m-d'));

    if ($product_id > 0 && $quantity > 0) {
        try {
            $pdo->beginTransaction();
            
            // Insert stock in record
            $stmt = $pdo->prepare("INSERT INTO stock_in (product_id, vendor_id, invoice_no, serial_number, warranty_months, quantity, purchase_price, shipping_charge, purchase_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_id, $vendor_id, $invoice, $serial, $warranty, $quantity, $price, $shipping, $date, $_SESSION['user_id']]);
            $stock_id = $pdo->lastInsertId();

            // Update product current stock
            updateStock($pdo, $product_id, $quantity, 'add');
            
            // Update product's buying price with landed cost (Unit Price + Shipping per unit)
            $landed_cost = $price + ($shipping / $quantity);
            $stmt_update_price = $pdo->prepare("UPDATE products SET buying_price = ? WHERE id = ?");
            $stmt_update_price->execute([$landed_cost, $product_id]);
            
            logActivity($pdo, $_SESSION['user_id'], "Added stock for product ID: {$product_id}. Updated buying price to: {$landed_cost}", 'stock_in', $stock_id);


            $pdo->commit();
            $_SESSION['message'] = "Stock added successfully!";
            header('Location: list.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please select valid product and quantity";
    }
}

$pageTitle = 'Add Stock';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$products = $pdo->query("SELECT id, name, current_stock FROM products WHERE status=1 ORDER BY name ASC")->fetchAll();
$vendors  = $pdo->query("SELECT id, name FROM vendors WHERE status=1 ORDER BY name ASC")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchase Add</h4>
                <h6>Add new purchase/stock in</h6>
            </div>
        </div>
        
        <?php if ($message): ?><div class="alert alert-success mt-3"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger mt-3"><?php echo $error; ?></div><?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="add.php" method="POST">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Product</label>
                                <select class="select" name="product_id" required>
                                    <option value="0">Select Product</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?> (Stock: <?php echo $p['current_stock']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Vendor</label>
                                <select class="select" name="vendor_id">
                                    <option value="0">Select Vendor</option>
                                    <?php foreach ($vendors as $v): ?>
                                        <option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="INV-2024..." value="<?php echo generateInvoiceNo($pdo, 'STK', 'stock_in', 'invoice_no'); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Unit Purchase Price</label>
                                <input type="number" name="purchase_price" class="form-control" step="0.01" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Shipping Charge</label>
                                <input type="number" name="shipping_charge" class="form-control" step="0.01" value="0.00">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Warranty (Months)</label>
                                <input type="number" name="warranty_months" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Serial Number (If any)</label>
                                <input type="text" name="serial_number" class="form-control" placeholder="Enter serial numbers separated by comma">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                            <a href="list.php" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
