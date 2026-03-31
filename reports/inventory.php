<?php
$pageTitle = 'Stock Report';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, b.name as brand_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN brands b ON p.brand_id = b.id 
    WHERE p.status = 1 
    ORDER BY p.current_stock ASC
");
$products = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Stock/Inventory Report</h4>
                <h6>Real-time stock availability summary</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Model</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Current Stock</th>
                                <th>Min. Alert</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo $p['name']; ?></td>
                                <td><?php echo $p['model']; ?></td>
                                <td><?php echo $p['category_name']; ?></td>
                                <td><?php echo $p['brand_name']; ?></td>
                                <td><strong><?php echo $p['current_stock']; ?> <?php echo ucfirst($p['unit']); ?></strong></td>
                                <td><?php echo $p['min_stock_alert']; ?></td>
                                <td>
                                    <?php if($p['current_stock'] <= 0): ?>
                                        <span class="badges bg-lightred">Out of Stock</span>
                                    <?php elseif($p['current_stock'] <= $p['min_stock_alert']): ?>
                                        <span class="badges bg-lightyellow">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badges bg-lightgreen">Available</span>
                                    <?php endif; ?>
                                </td>
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
