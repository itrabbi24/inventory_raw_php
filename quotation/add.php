<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id    = (int)($_POST['customer_id'] ?? 0);
    $quotation_no   = sanitize($_POST['quotation_no'] ?? '');
    $date           = sanitize($_POST['quotation_date'] ?? date('Y-m-d'));
    
    $product_ids    = $_POST['product_id'] ?? [];
    $quantities     = $_POST['quantity'] ?? [];
    $prices         = $_POST['unit_price'] ?? [];
    $serials        = $_POST['serial_number'] ?? [];
    $warranties     = $_POST['warranty'] ?? [];
    
    $subtotal       = (float)($_POST['subtotal'] ?? 0);
    $discount       = (float)($_POST['discount'] ?? 0);
    $total_amount   = (float)($_POST['grand_total'] ?? 0);

    if ($customer_id >= 0 && !empty($product_ids)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO quotations (quotation_no, customer_id, quotation_date, subtotal, discount, total_amount, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$quotation_no, $customer_id == 0 ? null : $customer_id, $date, $subtotal, $discount, $total_amount, $_SESSION['user_id']]);
            $quotation_id = $pdo->lastInsertId();

            $stmt_item = $pdo->prepare("INSERT INTO quotation_items (quotation_id, product_id, quantity, unit_price, serial_number, warranty_months) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($product_ids as $index => $pid) {
                $pid   = (int)$pid;
                $qty   = (int)$quantities[$index];
                $price = (float)$prices[$index];
                $sn    = sanitize($serials[$index]);
                $wr    = (int)$warranties[$index];
                if ($pid > 0 && $qty > 0) {
                    $stmt_item->execute([$quotation_id, $pid, $qty, $price, $sn, $wr]);
                }
            }
            
            logActivity($pdo, $_SESSION['user_id'], "Quotation created: {$quotation_no}", 'quotations', $quotation_id);
            $pdo->commit();
            $_SESSION['message'] = "Quotation saved successfully!";
            header('Location: list.php');
            exit();
        } catch (Exception $e) { 
            $pdo->rollBack(); 
            $error = "Error: " . $e->getMessage(); 
        }
    }
}

$pageTitle = 'Add Quotation';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$products = $pdo->query("SELECT id, name, current_stock FROM products WHERE status=1 ORDER BY name ASC")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers WHERE status=1 ORDER BY name ASC")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Quotation Add</h4>
                <h6>Create new quotation</h6>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <form action="add.php" method="POST">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Customer</label>
                                <select class="select" name="customer_id" required>
                                    <option value="0">Choose Customer</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Quotation Date</label>
                                <input type="date" name="quotation_date" value="<?php echo date('Y-m-d'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Quotation No</label>
                                <input type="text" name="quotation_no" value="<?php echo generateInvoiceNo($pdo, 'QT', 'quotations', 'quotation_no'); ?>" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="quotationTable">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Serial Number</th>
                                            <th>Warranty (Months)</th>
                                            <th>QTY</th>
                                            <th>Price (৳)</th>
                                            <th>Total (৳)</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-control select" name="product_id[]" onchange="updatePrice(this)" required>
                                                    <option value="">Select Product</option>
                                                    <?php foreach ($products as $p): ?>
                                                        <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="text" name="serial_number[]" class="form-control"></td>
                                            <td><input type="number" name="warranty[]" value="0" class="form-control"></td>
                                            <td><input type="number" name="quantity[]" value="1" class="form-control" oninput="calculateTotals()"></td>
                                            <td><input type="number" name="unit_price[]" value="0" class="form-control" oninput="calculateTotals()"></td>
                                            <td class="row-total">0.00</td>
                                            <td><button type="button" class="btn btn-primary" onclick="addRow()">+</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-lg-6 col-sm-12"></div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group"><label>Subtotal</label><input type="number" id="subtotal" name="subtotal" value="0" class="form-control" readonly></div>
                            <div class="form-group"><label>Discount</label><input type="number" id="discount" name="discount" value="0" class="form-control" oninput="calculateTotals()"></div>
                            <div class="form-group"><label>Grand Total</label><input type="number" id="grand_total" name="grand_total" value="0" class="form-control" readonly></div>
                        </div>
                    </div>
                    
                    <div class="col-lg-12 mt-4">
                        <button type="submit" class="btn btn-submit me-2">Save Quotation</button>
                        <a href="list.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function addRow() {
    let table = document.getElementById('quotationTable').getElementsByTagName('tbody')[0];
    let newRow = table.insertRow();
    newRow.innerHTML = `
        <td>
            <select class="form-control" name="product_id[]" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option><?php endforeach; ?>
            </select>
        </td>
        <td><input type="text" name="serial_number[]" class="form-control"></td>
        <td><input type="number" name="warranty[]" value="0" class="form-control"></td>
        <td><input type="number" name="quantity[]" value="1" class="form-control" oninput="calculateTotals()"></td>
        <td><input type="number" name="unit_price[]" value="0" class="form-control" oninput="calculateTotals()"></td>
        <td class="row-total">0.00</td>
        <td><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove(); calculateTotals();">-</button></td>
    `;
}

function calculateTotals() {
    let rows = document.getElementById('quotationTable').getElementsByTagName('tbody')[0].rows;
    let subtotal = 0;
    for(let row of rows) {
        let qty = row.cells[3].getElementsByTagName('input')[0].value;
        let price = row.cells[4].getElementsByTagName('input')[0].value;
        let total = qty * price;
        row.cells[5].innerText = total.toFixed(2);
        subtotal += total;
    }
    document.getElementById('subtotal').value = subtotal;
    let disc = document.getElementById('discount').value;
    document.getElementById('grand_total').value = (subtotal - disc).toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
