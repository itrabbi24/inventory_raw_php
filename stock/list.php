<?php
$pageTitle = 'Stock In List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("
    SELECT s.*, v.name as vendor_name, p.name as product_name 
    FROM stock_in s 
    LEFT JOIN vendors v ON s.vendor_id = v.id 
    LEFT JOIN products p ON s.product_id = p.id 
    ORDER BY s.id DESC
");
$stocks = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Stock In List</h4>
                <h6>Manage your stock purchases</h6>
            </div>
            <div class="page-btn">
                <a href="add.php" class="btn btn-added"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Stock</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Vendor</th>
                                <th>Invoice No</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stocks as $stk): ?>
                            <tr>
                                <td><?php echo $stk['purchase_date']; ?></td>
                                <td><?php echo $stk['product_name']; ?></td>
                                <td><?php echo $stk['vendor_name']; ?></td>
                                <td><?php echo $stk['invoice_no']; ?></td>
                                <td><?php echo $stk['quantity']; ?></td>
                                <td><?php echo formatCurrency($stk['purchase_price']); ?></td>
                                <td><?php echo formatCurrency($stk['total_price']); ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteStock(<?php echo $stk['id']; ?>)">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/delete.svg" alt="img">
                                    </a>
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

<script>
function deleteStock(id) {
    Swal.fire({
        title: 'Delete this purchase record?',
        text: "Stock levels will not be auto-reversed unless logic is implemented.",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'stock_in', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
