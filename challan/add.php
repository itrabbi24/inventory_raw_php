<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id    = (int)($_POST['customer_id'] ?? 0);
    $challan_no     = sanitize($_POST['challan_no'] ?? '');
    $date           = sanitize($_POST['challan_date'] ?? date('Y-m-d'));
    $address        = sanitize($_POST['delivery_address'] ?? '');
    
    $desc           = $_POST['description'] ?? [];
    $product_ids    = $_POST['product_id'] ?? [];
    $quantities     = $_POST['quantity'] ?? [];
    $serials        = $_POST['serial_number'] ?? [];
    $warranties     = $_POST['warranty'] ?? [];


    if ($customer_id > 0 && !empty($product_ids)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO challan (challan_no, customer_id, challan_date, delivery_address, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$challan_no, $customer_id, $date, $address, $_SESSION['user_id']]);
            $challan_id = $pdo->lastInsertId();

            $stmt_item = $pdo->prepare("INSERT INTO challan_items (challan_id, product_id, description, quantity, serial_number, warranty_months) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($product_ids as $index => $pid) {
                $pid = (int)$pid;
                $qty = (int)$quantities[$index];
                $sn  = sanitize($serials[$index]);
                $wr  = (int)$warranties[$index];
                if ($pid > 0 && $qty > 0) {
                    $d = sanitize($desc[$index]);
                    $stmt_item->execute([$challan_id, $pid, $d, $qty, $sn, $wr]);
                }

            }
            
            logActivity($pdo, $_SESSION['user_id'], "New challan created: {$challan_no}", 'challan', $challan_id);
            $pdo->commit();
            $_SESSION['message'] = "Challan created successfully!";
            header('Location: list.php');
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}

$pageTitle = 'Add Challan';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$products = $pdo->query("SELECT id, name FROM products WHERE status=1 ORDER BY name ASC")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers WHERE status=1 ORDER BY name ASC")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Challan Add</h4>
                <h6>Create new delivery challan</h6>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success mt-3"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger mt-3"><?php echo $error; ?></div><?php endif; ?>

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
                                <label>Challan Date</label>
                                <input type="date" name="challan_date" value="<?php echo date('Y-m-d'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Challan No</label>
                                <input type="text" name="challan_no" value="<?php echo generateInvoiceNo($pdo, 'CHL', 'challan', 'challan_no'); ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-12 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Delivery Address</label>
                                <textarea name="delivery_address" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="challanTable">
                                    <thead>
                                        <tr>
                                            <th>Product Selection</th>
                                            <th>Description</th>
                                            <th>QTY</th>
                                            <th>Serial Number</th>
                                            <th>Warranty (Months)</th>
                                            <th>Action</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-control" name="product_id[]" required>
                                                    <option value="">Select Product</option>
                                                    <?php foreach ($products as $p): ?>
                                                        <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><textarea name="description[]" class="form-control" rows="1" placeholder="Optional details"></textarea></td>
                                            <td><input type="number" name="quantity[]" value="1" class="form-control"></td>
                                            <td><input type="text" name="serial_number[]" class="form-control"></td>
                                            <td><input type="number" name="warranty[]" value="0" class="form-control"></td>
                                            <td class="text-center"><button type="button" class="btn btn-primary" onclick="addChallanRow()"><i class="fas fa-plus"></i></button></td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-12 mt-4">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="list.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function addChallanRow() {
    let table = document.getElementById('challanTable').getElementsByTagName('tbody')[0];
    let newRow = table.insertRow();
    newRow.innerHTML = `
        <td>
            <select class="form-control" name="product_id[]" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $p): ?><option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option><?php endforeach; ?>
            </select>
        </td>
        <td><textarea name="description[]" class="form-control" rows="1" placeholder="Optional details"></textarea></td>
        <td><input type="number" name="quantity[]" value="1" class="form-control"></td>
        <td><input type="text" name="serial_number[]" class="form-control"></td>
        <td><input type="number" name="warranty[]" value="0" class="form-control"></td>
        <td class="text-center"><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">-</button></td>
    `;
}

</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
