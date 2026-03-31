<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Summary cards data
$today = date('Y-m-d');

// Total products
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn();

// Today's sales total
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_date=?");
$stmt->execute([$today]);
$todaySales = $stmt->fetchColumn();

// Today's purchase total
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM stock_in WHERE purchase_date=?");
$stmt->execute([$today]);
$todayPurchase = $stmt->fetchColumn();

// Total customers
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status=1")->fetchColumn();

// Total vendors
$totalVendors = $pdo->query("SELECT COUNT(*) FROM vendors WHERE status=1")->fetchColumn();

// Total due amount (across all sales)
$totalDue = $pdo->query("SELECT COALESCE(SUM(due_amount),0) FROM sales WHERE status != 'returned'")->fetchColumn();

// Total deposited (This month)
$startOfMonth = date('Y-m-01');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM depositor_transactions WHERE type='deposit' AND transaction_date >= ?");
$stmt->execute([$startOfMonth]);
$totalDeposited = $stmt->fetchColumn();

// Monthly Profit summary
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_date BETWEEN ? AND ?");
$stmt->execute([$startOfMonth, $today]);
$monthRevenue = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM stock_in WHERE purchase_date BETWEEN ? AND ?");
$stmt->execute([$startOfMonth, $today]);
$monthPurchaseCost = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmt->execute([$startOfMonth, $today]);
$monthExpenseTotal = $stmt->fetchColumn();

$monthlyProfit = $monthRevenue - $monthPurchaseCost - $monthExpenseTotal;

// Recent Sales (Safely handle if table is empty)
try {
    $recentSales = $pdo->query("SELECT s.*, c.name as customer_name FROM sales s LEFT JOIN customers c ON s.customer_id = c.id ORDER BY s.id DESC LIMIT 10")->fetchAll();
} catch (Exception $e) {
    $recentSales = [];
}

// Low Stock Alerts
try {
    $lowStock = $pdo->query("SELECT * FROM products WHERE current_stock <= min_stock_alert AND status=1 LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $lowStock = [];
}
?>
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h4><?php echo $totalProducts; ?></h4>
                            <h5>Total Products</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das1">
                        <div class="dash-counts">
                            <h4><?php echo formatCurrency((float)$todaySales); ?></h4>
                            <h5>Today's Sales</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="shopping-bag"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das2">
                        <div class="dash-counts">
                            <h4><?php echo formatCurrency((float)$todayPurchase); ?></h4>
                            <h5>Today's Purchase</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="file-text"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das3">
                        <div class="dash-counts">
                            <h4><?php echo formatCurrency((float)$monthlyProfit); ?></h4>
                            <h5>Monthly Profit</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="trending-up"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-7 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Sales</h5>
                            <div class="graph-sets">
                                <a href="<?php echo BASE_URL; ?>sales/list.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSales as $sale): ?>
                                            <tr>
                                                <td><a href="<?php echo BASE_URL; ?>sales/view.php?id=<?php echo $sale['id']; ?>"><?php echo $sale['invoice_no']; ?></a></td>
                                                <td><?php echo $sale['customer_name'] ?? 'Walk-in'; ?></td>
                                                <td><?php echo formatCurrency((float)$sale['total_amount']); ?></td>
                                                <td><?php echo formatCurrency((float)$sale['paid_amount']); ?></td>
                                                <td><?php echo formatCurrency((float)$sale['due_amount']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentSales)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No recent sales</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Low Stock Alert</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lowStock as $ls): ?>
                                            <tr>
                                                <td class="productimgname">
                                                    <a href="javascript:void(0);"><?php echo $ls['name']; ?></a>
                                                </td>
                                                <td><span class="badges bg-lightred"><?php echo $ls['current_stock']; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($lowStock)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No low stock items</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das4">
                        <div class="dash-counts">
                            <h4><?php echo $totalCustomers; ?></h4>
                            <h5>Total Customers</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="user"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das5">
                        <div class="dash-counts">
                            <h4><?php echo $totalVendors; ?></h4>
                            <h5>Total Vendors</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das6">
                        <div class="dash-counts">
                            <h4><?php echo formatCurrency((float)$totalDue); ?></h4>
                            <h5>Total Sales Due</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="credit-card"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das7">
                        <div class="dash-counts">
                            <h4><?php echo formatCurrency((float)$totalDeposited); ?></h4>
                            <h5>This Month Deposited</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="database"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
