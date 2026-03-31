<?php
$pageTitle = 'Product Wise Sales';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$product_id = (int)($_GET['product_id'] ?? 0);
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$query = "
    SELECT si.*, s.invoice_no, s.sale_date, c.name as customer_name, p.name as product_name 
    FROM sale_items si 
    JOIN sales s ON si.sale_id = s.id 
    JOIN products p ON si.product_id = p.id 
    LEFT JOIN customers c ON s.customer_id = c.id 
    WHERE s.sale_date BETWEEN ? AND ?
";
$params = [$start_date, $end_date];

if ($product_id > 0) {
    $query .= " AND si.product_id = ?";
    $params[] = $product_id;
}

$query .= " ORDER BY s.sale_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales_items = $stmt->fetchAll();

$products = $pdo->query("SELECT id, name FROM products WHERE status=1 ORDER BY name ASC")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Product Wise Sales Report</h4>
                <h6>Detailed sales breakdown by product</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="product_sales.php" method="GET">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group"><label>Product</label>
                                <select name="product_id" class="select">
                                    <option value="0">All Products</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $product_id) ? 'selected' : ''; ?>><?php echo $p['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2"><div class="form-group"><label>From</label><input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-2"><div class="form-group"><label>To</label><input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-2"><button type="submit" class="btn btn-primary mt-4">Filter</button></div>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Inv #</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Serial #</th>
                                <th>Warranty</th>
                                <th>Qty</th>
                                <th>Price (৳)</th>
                                <th>Total (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_items as $item): ?>
                            <tr>
                                <td><?php echo $item['sale_date']; ?></td>
                                <td><?php echo $item['invoice_no']; ?></td>
                                <td><?php echo $item['customer_name'] ?: 'Walk-in'; ?></td>
                                <td><?php echo $item['product_name']; ?></td>
                                <td><?php echo $item['serial_number']; ?></td>
                                <td><?php echo $item['warranty_months']; ?> Mo.</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo number_format($item['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
