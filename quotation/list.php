<?php
$pageTitle = 'Quotation List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT q.*, c.name as customer_name FROM quotations q LEFT JOIN customers c ON q.customer_id = c.id ORDER BY q.id DESC");
$quotations = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Quotation List</h4>
                <h6>Manage your quotations</h6>
            </div>
            <div class="page-btn">
                <a href="add.php" class="btn btn-added"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Quotation</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Quotation No</th>
                                <th>Customer Name</th>
                                <th>Date</th>
                                <th>Total (৳)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotations as $q): ?>
                            <tr>
                                <td><?php echo $q['quotation_no']; ?></td>
                                <td><?php echo $q['customer_name'] ?: 'N/A'; ?></td>
                                <td><?php echo $q['quotation_date']; ?></td>
                                <td><?php echo formatCurrency($q['total_amount']); ?></td>
                                <td><span class="badges <?php echo ($q['status'] == 'draft') ? 'bg-lightyellow' : 'bg-lightgreen'; ?>"><?php echo ucfirst($q['status']); ?></span></td>
                                <td>
                                    <a class="me-3" href="view.php?id=<?php echo $q['id']; ?>">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/eye.svg" alt="img">
                                    </a>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteQuotation(<?php echo $q['id']; ?>)">
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
function deleteQuotation(id) {
    Swal.fire({
        title: 'Delete Quotation?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'quotations', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
