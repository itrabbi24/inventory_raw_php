<?php
$pageTitle = 'Vendor List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM vendors WHERE status=1 ORDER BY id DESC");
$vendors = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vendor'])) {
    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $email   = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $company = sanitize($_POST['company_name'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO vendors (name, phone, email, address, company_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address, $company]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Vendor List</h4>
                <h6>Manage your vendors/suppliers</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addVendorModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Vendor</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><?php echo $vendor['name']; ?></td>
                                <td><?php echo $vendor['company_name']; ?></td>
                                <td><?php echo $vendor['phone']; ?></td>
                                <td><?php echo $vendor['email']; ?></td>
                                <td><?php echo $vendor['address']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteVendor(<?php echo $vendor['id']; ?>)">
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
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Vendor Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Company Name</label><input type="text" name="company_name" class="form-control"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_vendor" class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteVendor(id) {
    Swal.fire({
        title: 'Delete Vendor?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'vendors', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
