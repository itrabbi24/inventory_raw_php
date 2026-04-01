<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Summary cards data
$today = date('Y-m-d');
$startOfMonth = date('Y-m-01');

// Fetch Summary Stats
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_date=?");
$stmt->execute([$today]);
$todaySales = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM stock_in WHERE purchase_date=?");
$stmt->execute([$today]);
$todayPurchase = $stmt->fetchColumn();

// Profit tracking
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

// CHART DATA: Last 7 Days Activity
$chartLabels = [];
$chartSales = [];
$chartPurchase = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('D', strtotime($d));
    
    $s = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_date=?");
    $s->execute([$d]);
    $chartSales[] = (float)$s->fetchColumn();
    
    $p = $pdo->prepare("SELECT COALESCE(SUM(total_price),0) FROM stock_in WHERE purchase_date=?");
    $p->execute([$d]);
    $chartPurchase[] = (float)$p->fetchColumn();
}

// CHART DATA: Top 5 Categories
$catData = $pdo->query("
    SELECT c.name, COUNT(si.id) as sales_count 
    FROM categories c 
    JOIN products p ON c.id = p.category_id 
    JOIN sale_items si ON p.id = si.product_id 
    GROUP BY c.id 
    ORDER BY sales_count DESC 
    LIMIT 5
")->fetchAll();

$catLabels = [];
$catValues = [];
foreach($catData as $cd) {
    $catLabels[] = $cd['name'];
    $catValues[] = (int)$cd['sales_count'];
}

// Tables data
$recentSales = $pdo->query("SELECT s.*, c.name as customer_name FROM sales s LEFT JOIN customers c ON s.customer_id = c.id ORDER BY s.id DESC LIMIT 5")->fetchAll();
$lowStock = $pdo->query("SELECT * FROM products WHERE current_stock <= min_stock_alert AND status=1 LIMIT 5")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <!-- New High-Density Stats -->
        <div class="row">
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-count bg-gradient-primary text-white p-3 rounded-3 shadow-sm mb-4">
                    <div class="dash-counts">
                        <h4 class="text-white"><?php echo $totalProducts; ?></h4>
                        <h5 class="text-white opacity-75">Live Products</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-count bg-gradient-warning text-white p-3 rounded-3 shadow-sm mb-4">
                    <div class="dash-counts">
                        <h4 class="text-white"><?php echo formatCurrency((float)$todaySales); ?></h4>
                        <h5 class="text-white opacity-75">Today Sales</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-count bg-gradient-info text-white p-3 rounded-3 shadow-sm mb-4">
                    <div class="dash-counts">
                        <h4 class="text-white"><?php echo formatCurrency((float)$todayPurchase); ?></h4>
                        <h5 class="text-white opacity-75">Today Purchase</h5>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 col-12">
                <div class="dash-count bg-gradient-success text-white p-3 rounded-3 shadow-sm mb-4">
                    <div class="dash-counts">
                        <h4 class="text-white"><?php echo formatCurrency((float)$monthlyProfit); ?></h4>
                        <h5 class="text-white opacity-75">M. Profit (Net)</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Charts -->
        <div class="row">
            <div class="col-lg-8 col-sm-12 col-12 d-flex">
                <div class="card flex-fill shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 fw-bold">Sales vs Purchase (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="activityChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12 col-12 d-flex">
                <div class="card flex-fill shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="card-title mb-0 fw-bold">Bestselling Categories</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <!-- Recent Sales -->
            <div class="col-lg-7 col-sm-12 col-12 d-flex">
                <div class="card flex-fill shadow-sm border-0">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Recent Sales</h5>
                        <a href="<?php echo BASE_URL; ?>sales/list.php" class="btn btn-sm btn-light border-0 px-3">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Invoice</th>
                                        <th class="border-0">Customer</th>
                                        <th class="border-0">Total</th>
                                        <th class="border-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td><span class="text-warning fw-bold"><?php echo $sale['invoice_no']; ?></span></td>
                                            <td class="text-muted"><?php echo $sale['customer_name'] ?? 'Walk-in'; ?></td>
                                            <td class="fw-bold"><?php echo formatCurrency((float)$sale['total_amount']); ?></td>
                                            <td><span class="badges <?php echo ($sale['due_amount'] > 0) ? 'bg-lightred' : 'bg-lightgreen'; ?>"><?php echo ($sale['due_amount'] > 0) ? 'Due' : 'Paid'; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock -->
            <div class="col-lg-5 col-sm-12 col-12 d-flex">
                <div class="card flex-fill shadow-sm border-0">
                    <div class="card-header pb-0 bg-transparent border-0">
                        <h5 class="card-title mb-0 fw-bold text-dark">Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Item</th>
                                        <th class="border-0">Stock</th>
                                        <th class="border-0">State</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStock as $ls): ?>
                                        <tr>
                                            <td class="text-muted fw-semi-bold"><?php echo $ls['name']; ?></td>
                                            <td><span class="badge bg-soft-danger text-danger fs-6"><?php echo $ls['current_stock']; ?></span></td>
                                            <td><span class="text-danger small fw-bold">Refill Needed</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activity Chart
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Sales (৳)',
                data: <?php echo json_encode($chartSales); ?>,
                borderColor: '#ff9f43',
                backgroundColor: 'rgba(255, 159, 67, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }, {
                label: 'Purchases (৳)',
                data: <?php echo json_encode($chartPurchase); ?>,
                borderColor: '#2196f3',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($catLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($catValues); ?>,
                backgroundColor: ['#ff9f43', '#2196f3', '#00b8d9', '#4caf50', '#7460ee'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } }
        }
    });
});
</script>

<style>
    .bg-gradient-primary { background: linear-gradient(90deg, #ff9f43 0%, #ff8c21 100%); }
    .bg-gradient-warning { background: linear-gradient(90deg, #ffc107 0%, #e0a800 100%); }
    .bg-gradient-info { background: linear-gradient(90deg, #17a2b8 0%, #117a8b 100%); }
    .bg-gradient-success { background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%); }
    .badge.bg-soft-danger { background-color: rgba(255, 76, 76, 0.1); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
