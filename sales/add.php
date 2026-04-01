<?php
$pageTitle = 'Add Sales';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$products = $pdo->query("SELECT id, name, current_stock FROM products WHERE status=1 AND current_stock > 0 ORDER BY name ASC")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers WHERE status=1 ORDER BY name ASC")->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id    = (int)($_POST['customer_id'] ?? 0);
    $invoice        = sanitize($_POST['invoice_no'] ?? '');
    $date           = sanitize($_POST['sale_date'] ?? date('Y-m-d'));
    
    $product_ids    = $_POST['product_id'] ?? [];
    $quantities     = $_POST['quantity'] ?? [];
    $prices         = $_POST['unit_price'] ?? [];
    $serials        = $_POST['serial_number'] ?? [];
    $warranties     = $_POST['warranty'] ?? [];
    
    $subtotal       = (float)($_POST['subtotal'] ?? 0);
    $discount       = (float)($_POST['discount'] ?? 0);
    $vat            = (float)($_POST['vat'] ?? 0);
    $total_amount   = (float)($_POST['grand_total'] ?? 0);
    $paid_amount    = (float)($_POST['paid_amount'] ?? 0);
    $payment_method = sanitize($_POST['payment_method'] ?? 'cash');

    if ($customer_id > 0 && !empty($product_ids)) {
        try {
            $pdo->beginTransaction();
            
            // Insert Sales main record
            $stmt = $pdo->prepare("INSERT INTO sales (invoice_no, customer_id, sale_date, subtotal, discount, vat, total_amount, paid_amount, payment_method, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$invoice, $customer_id, $date, $subtotal, $discount, $vat, $total_amount, $paid_amount, $payment_method, $_SESSION['user_id']]);
            $sale_id = $pdo->lastInsertId();

            // Insert Sale Items and Deduct Stock
            $stmt_item = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, serial_number, warranty_months) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($product_ids as $index => $pid) {
                $pid   = (int)$pid;
                $qty   = (int)$quantities[$index];
                $price = (float)$prices[$index];
                $sn    = sanitize($serials[$index]);
                $wr    = (int)$warranties[$index];
                
                if ($pid > 0 && $qty > 0) {
                    $stmt_item->execute([$sale_id, $pid, $qty, $price, $sn, $wr]);
                    updateStock($pdo, $pid, $qty, 'subtract');
                }
            }
            
            logActivity($pdo, $_SESSION['user_id'], "New sale created: {$invoice}", 'sales', $sale_id);
            $pdo->commit();
            $message = "Sale record created successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please select customer and at least one product";
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales Add</h4>
                <h6>Create new sale record</h6>
            </div>
        </div>
        
        <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <form action="add.php" method="POST" id="salesForm">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Customer</label>
                                <select class="select" name="customer_id" required>
                                    <option value="0">Walk-in Customer</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Sale Date</label>
                                <input type="date" name="sale_date" value="<?php echo date('Y-m-d'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" value="<?php echo generateInvoiceNo($pdo, 'INV', 'sales', 'invoice_no'); ?>" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row align-items-end mb-3">
                        <div class="col-lg-10 col-sm-10 col-10">
                            <div class="form-group mb-0">
                                <label>Search & Selection Product</label>
                                <select id="productSelect" class="form-control select">
                                    <option value="">Choose Product</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" data-name="<?php echo $p['name']; ?>" data-stock="<?php echo $p['current_stock']; ?>">
                                            <?php echo $p['name']; ?> (Stock: <?php echo $p['current_stock']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-2 col-2">
                            <button type="button" class="btn btn-submit w-100 h-100" onclick="addProductRow()">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="salesTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 30%;">Product Name</th>
                                            <th style="width: 15%;">Serial No</th>
                                            <th style="width: 10%;">Warranty (M)</th>
                                            <th style="width: 10%;">QTY</th>
                                            <th style="width: 15%;">Unit Price (৳)</th>
                                            <th style="width: 15%;">Total (৳)</th>
                                            <th style="width: 5%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dynamic items here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-lg-8 col-sm-12"></div>
                        <div class="col-lg-4 col-sm-12">
                            <div class="total-order">
                                <ul>
                                    <li>
                                        <h4>Subtotal</h4>
                                        <h5><input type="number" id="subtotal" name="subtotal" value="0" class="form-control text-end border-0 bg-transparent py-0" readonly></h5>
                                    </li>
                                    <li>
                                        <h4>Discount</h4>
                                        <h5><input type="number" id="discount" name="discount" value="0" class="form-control text-end border-0 bg-transparent py-0" oninput="calculateTotals()"></h5>
                                    </li>
                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5><input type="number" id="grand_total" name="grand_total" value="0" class="form-control text-end border-0 bg-transparent py-1 fw-bold" readonly style="font-size: 1.25rem;"></h5>
                                    </li>
                                </ul>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group row align-items-center">
                                <label class="col-lg-4 col-form-label">Paid Amount</label>
                                <div class="col-lg-8">
                                    <input type="number" name="paid_amount" value="0" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-lg-4 col-form-label">Method</label>
                                <div class="col-lg-8">
                                    <select class="select" name="payment_method">
                                        <option value="cash">Cash</option>
                                        <option value="bkash">bKash</option>
                                        <option value="nagad">Nagad</option>
                                        <option value="bank">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-12 text-end mt-4">
                        <button type="submit" class="btn btn-submit me-2 px-5">Finalize Sale</button>
                        <a href="list.php" class="btn btn-cancel px-4">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function addProductRow() {
    let select = document.getElementById('productSelect');
    let pid = select.value;
    if(!pid) return;
    
    let option = select.options[select.selectedIndex];
    let name = option.getAttribute('data-name');
    let stock = parseInt(option.getAttribute('data-stock'));
    
    // Check if product already exists in table
    let existingIds = document.getElementsByName('product_id[]');
    for(let i=0; i<existingIds.length; i++) {
        if(existingIds[i].value === pid) {
            alert('Product already added to list');
            return;
        }
    }

    let table = document.getElementById('salesTable').getElementsByTagName('tbody')[0];
    let newRow = table.insertRow();
    
    newRow.innerHTML = `
        <td class="align-middle">
            <strong>${name}</strong>
            <input type="hidden" name="product_id[]" value="${pid}">
        </td>
        <td><input type="text" name="serial_number[]" class="form-control form-control-sm" placeholder="Serial"></td>
        <td><input type="number" name="warranty[]" value="0" min="0" class="form-control form-control-sm text-center"></td>
        <td><input type="number" name="quantity[]" value="1" min="1" max="${stock}" class="form-control form-control-sm text-center" oninput="calculateTotals()"></td>
        <td><input type="number" name="unit_price[]" value="0" step="0.01" class="form-control form-control-sm text-end" oninput="calculateTotals()"></td>
        <td class="row-total text-end align-middle fw-bold">0.00</td>
        <td class="text-center align-middle">
            <a href="javascript:void(0);" onclick="this.parentElement.parentElement.remove(); calculateTotals();" class="text-danger">
                <i class="fas fa-trash-alt"></i>
            </a>
        </td>
    `;
    
    calculateTotals();
    // Reset select
    // select.value = ""; // optional
}

function calculateTotals() {
    let rows = document.getElementById('salesTable').getElementsByTagName('tbody')[0].rows;
    let subtotal = 0;
    
    for(let row of rows) {
        let qty = parseFloat(row.cells[3].getElementsByTagName('input')[0].value) || 0;
        let price = parseFloat(row.cells[4].getElementsByTagName('input')[0].value) || 0;
        let total = qty * price;
        row.cells[5].innerText = total.toFixed(2);
        subtotal += total;
    }
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    let discount = parseFloat(document.getElementById('discount').value) || 0;
    let grand_total = subtotal - discount;
    document.getElementById('grand_total').value = grand_total.toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
