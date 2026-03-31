<?php
$pageTitle = 'Customer List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM customers WHERE status=1 ORDER BY id DESC");
$customers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $balance = (float)($_POST['opening_balance'] ?? 0);

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, opening_balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $balance]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Customer List</h4>
                <h6>Manage your customers</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Customer</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $cust): ?>
                            <tr>
                                <td><?php echo $cust['name']; ?></td>
                                <td><?php echo $cust['phone']; ?></td>
                                <td><?php echo $cust['email']; ?></td>
                                <td><?php echo $cust['address']; ?></td>
                                <td><?php echo formatCurrency($cust['opening_balance']); ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteCustomer(<?php echo $cust['id']; ?>)">
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

<!-- Modal logic here -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Customer Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
                    <div class="form-group"><label>Opening Balance</label><input type="number" name="opening_balance" value="0" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_customer" class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCustomer(id) {
    Swal.fire({
        title: 'Delete Customer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'customers', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
