<?php
$pageTitle = 'Sales Report';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT s.*, c.name as customer_name 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    WHERE s.sale_date BETWEEN ? AND ? 
    ORDER BY s.sale_date DESC
");
$stmt->execute([$start_date, $end_date]);
$sales = $stmt->fetchAll();

$total_sales = 0;
foreach ($sales as $sale) {
    $total_sales += (float)$sale['total_amount'];
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales Report</h4>
                <h6>Generate your sales summary by date</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="sales.php" method="GET">
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
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filter Report</button>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                           <div class="dash-count das1 mt-4">
                               <div class="dash-counts">
                                   <h4><?php echo formatCurrency($total_sales); ?></h4>
                                   <h5>Total Period Sales</h5>
                               </div>
                           </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Customer Name</th>
                                <th>Subtotal (৳)</th>
                                <th>Discount (৳)</th>
                                <th>Total (৳)</th>
                                <th>Paid (৳)</th>
                                <th>Due (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['sale_date']; ?></td>
                                <td><?php echo $sale['invoice_no']; ?></td>
                                <td><?php echo $sale['customer_name'] ?: 'Walk-in'; ?></td>
                                <td><?php echo number_format($sale['subtotal'], 2); ?></td>
                                <td><?php echo number_format($sale['discount'], 2); ?></td>
                                <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td><?php echo number_format($sale['paid_amount'], 2); ?></td>
                                <td><?php echo number_format($sale['due_amount'], 2); ?></td>
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
