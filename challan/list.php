<?php
$pageTitle = 'Challan List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT c.*, cust.name as customer_name FROM challan c LEFT JOIN customers cust ON c.customer_id = cust.id ORDER BY c.id DESC");
$challans = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Challan List</h4>
                <h6>Delivery challan records</h6>
            </div>
            <div class="page-btn">
                <a href="add.php" class="btn btn-added"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Challan</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Challan No</th>
                                <th>Customer Name</th>
                                <th>Challan Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($challans as $ch): ?>
                            <tr>
                                <td><?php echo $ch['challan_no']; ?></td>
                                <td><?php echo $ch['customer_name'] ?: 'N/A'; ?></td>
                                <td><?php echo $ch['challan_date']; ?></td>
                                <td>
                                    <select class="form-control status-select" onchange="changeStatus('challan', <?php echo $ch['id']; ?>, this.value)">
                                        <option value="pending" <?php echo ($ch['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="delivered" <?php echo ($ch['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo ($ch['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <a class="me-3" href="view.php?id=<?php echo $ch['id']; ?>">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/printer.svg" alt="img">
                                    </a>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteChallan(<?php echo $ch['id']; ?>)">
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
function changeStatus(table, id, status) {
    $.post('<?php echo BASE_URL; ?>ajax/update_status.php', {table: table, id: id, status: status}, function(res) {
        if (!res.success) alert(res.message);
    });
}

function deleteChallan(id) {
    Swal.fire({
        title: 'Delete this challan?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'challan', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
