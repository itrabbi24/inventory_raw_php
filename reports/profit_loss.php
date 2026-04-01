<?php
$pageTitle = 'Profit & Loss Report';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

// Total Revenue (Sales)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$totalRevenue = $stmt->fetchColumn();

// Total COGS (Cost of Goods Sold)
// Calculated from sale_items joined with sales to filter by date
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(si.cost_price * si.quantity), 0) 
    FROM sale_items si 
    JOIN sales s ON si.sale_id = s.id 
    WHERE s.sale_date BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$totalCOGS = $stmt->fetchColumn();

// Total Expenses
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$totalExpenses = $stmt->fetchColumn();

$grossProfit = $totalRevenue - $totalCOGS;
$netProfit   = $grossProfit - $totalExpenses;
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Profit & Loss Report</h4>
                <h6>Full business performance summary</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="profit_loss.php" method="GET">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6"><div class="form-group"><label>From Date</label><input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-3 col-sm-6"><div class="form-group"><label>To Date</label><input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-3 col-sm-6"><button type="submit" class="btn btn-primary d-block mt-4">Filter Report</button></div>
                    </div>
                </form>

                <div class="row mt-5">
                    <div class="col-lg-12">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-primary text-white"><th>Description</th><th class="text-end">Amount (৳)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>A. Total Revenue (Sales)</strong></td><td class="text-end"><?php echo formatCurrency($totalRevenue); ?></td></tr>
                                <tr><td><strong>B. Cost of Goods Sold (COGS)</strong></td><td class="text-end"><?php echo formatCurrency($totalCOGS); ?></td></tr>
                                <tr class="bg-light"><td><strong>Gross Profit (A - B)</strong></td><td class="text-end"><strong><?php echo formatCurrency($grossProfit); ?></strong></td></tr>
                                <tr><td><strong>C. Total Operating Expenses</strong></td><td class="text-end"><?php echo formatCurrency($totalExpenses); ?></td></tr>
                                <tr class="<?php echo ($netProfit >=0) ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                                    <td><strong>Net Profit (Gross Profit - C)</strong></td>
                                    <td class="text-end"><h3><?php echo formatCurrency($netProfit); ?></h3></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-5 text-center text-muted">
                    <p>Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
                    <p>Developed by <strong>ARG RABBI</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
