<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $balance = (float)($_POST['opening_balance'] ?? 0);

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address, opening_balance) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $balance]);
        $_SESSION['message'] = "Customer '{$name}' added successfully!";
        header('Location: list.php');
        exit();
    }
}

$pageTitle = 'Customer List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM customers WHERE status=1 ORDER BY id DESC");
$customers = $stmt->fetchAll();
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

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success mt-2"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

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
                                    <a class="me-3" href="javascript:void(0);" onclick="editCustomer(<?php echo htmlspecialchars(json_encode($cust)); ?>)">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/edit.svg" alt="img">
                                    </a>
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

<!-- Add Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body p-4">
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">FULL NAME</label><input type="text" name="name" class="form-control" placeholder="Enter customer name" required></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">PHONE NUMBER</label><input type="text" name="phone" class="form-control" placeholder="01XXX-XXXXXX"></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">EMAIL ADDRESS</label><input type="email" name="email" class="form-control" placeholder="customer@example.com"></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">ADDRESS</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    <div class="form-group mb-0"><label class="small fw-bold mb-2">OPENING BALANCE (৳)</label><input type="number" name="opening_balance" value="0" class="form-control"></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_customer" class="btn btn-primary px-4 fw-bold shadow-sm">SAVE CUSTOMER</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Edit Customer Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="table" value="customers">
                <div class="modal-body p-4">
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">FULL NAME</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">PHONE NUMBER</label><input type="text" name="phone" id="edit_phone" class="form-control"></div>
                    <div class="form-group mb-3"><label class="small fw-bold mb-2">EMAIL ADDRESS</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                    <div class="form-group mb-0"><label class="small fw-bold mb-2">ADDRESS</label><textarea name="address" id="edit_address" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold text-white shadow-sm">UPDATE PROFILE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCustomer(cust) {
    $('#edit_id').val(cust.id);
    $('#edit_name').val(cust.name);
    $('#edit_phone').val(cust.phone);
    $('#edit_email').val(cust.email);
    $('#edit_address').val(cust.address);
    $('#editCustomerModal').modal('show');
}

$('#editForm').on('submit', function(e) {
    e.preventDefault();
    $.post('<?php echo BASE_URL; ?>ajax/update_record.php', $(this).serialize(), (res) => {
        let data = JSON.parse(res);
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Updated!', showConfirmButton: false, timer: 1000 }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

function deleteCustomer(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff9f43',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'customers', id: id}, () => location.reload());
        }
    })
}
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
