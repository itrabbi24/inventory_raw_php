<?php
$pageTitle = 'Sales List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("
    SELECT s.*, c.name as customer_name 
    FROM sales s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    ORDER BY s.id DESC
");
$sales = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales List</h4>
                <h6>Manage your sales records</h6>
            </div>
            <div class="page-btn">
                <a href="add.php" class="btn btn-added"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Sale</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Customer Name</th>
                                <th>Total (৳)</th>
                                <th>Paid (৳)</th>
                                <th>Due (৳)</th>
                                <th>Method</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['sale_date']; ?></td>
                                <td><?php echo $sale['invoice_no']; ?></td>
                                <td><?php echo $sale['customer_name'] ?: 'Walk-in'; ?></td>
                                <td><?php echo formatCurrency($sale['total_amount']); ?></td>
                                <td><?php echo formatCurrency($sale['paid_amount']); ?></td>
                                <td><?php echo formatCurrency($sale['due_amount']); ?></td>
                                <td><span class="badges bg-lightgreen"><?php echo ucfirst($sale['payment_method']); ?></span></td>
                                <td>
                                    <a class="me-3" href="view.php?id=<?php echo $sale['id']; ?>">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/eye.svg" alt="img">
                                    </a>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteSale(<?php echo $sale['id']; ?>)">
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
function deleteSale(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will not restore stock automatically unless implemented.",
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'sales', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
