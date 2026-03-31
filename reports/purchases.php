<?php
$pageTitle = 'Purchase Report';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT s.*, v.name as vendor_name, p.name as product_name 
    FROM stock_in s 
    LEFT JOIN vendors v ON s.vendor_id = v.id 
    LEFT JOIN products p ON s.product_id = p.id 
    WHERE s.purchase_date BETWEEN ? AND ? 
    ORDER BY s.purchase_date DESC
");
$stmt->execute([$start_date, $end_date]);
$stocks = $stmt->fetchAll();

$total_purchase = 0;
foreach ($stocks as $stk) {
    $total_purchase += (float)$stk['total_price'];
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchase Report</h4>
                <h6>Generate your purchase history summary</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="purchases.php" method="GET">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="dash-count das2 mt-4">
                                <div class="dash-counts">
                                    <h4><?php echo formatCurrency($total_purchase); ?></h4>
                                    <h5>Total Period Purchase</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <button type="submit" class="btn btn-primary d-block mt-4">Filter Report</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Product</th>
                                <th>Vendor</th>
                                <th>Qty</th>
                                <th>Unit Price (৳)</th>
                                <th>Total (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $stk): ?>
                            <tr>
                                <td><?php echo $stk['purchase_date']; ?></td>
                                <td><?php echo $stk['invoice_no']; ?></td>
                                <td><?php echo $stk['product_name']; ?></td>
                                <td><?php echo $stk['vendor_name'] ?: 'N/A'; ?></td>
                                <td><?php echo $stk['quantity']; ?></td>
                                <td><?php echo number_format($stk['purchase_price'], 2); ?></td>
                                <td><?php echo number_format($stk['total_price'], 2); ?></td>
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
