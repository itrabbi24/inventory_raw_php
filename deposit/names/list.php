<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_deposit'])) {
    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO depositors (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $address]);
        $_SESSION['message'] = "Deposit account '{$name}' created!";
        header('Location: list.php');
        exit();
    }
}

$pageTitle = 'Deposit List';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM depositors WHERE status=1 ORDER BY id DESC");
$deposits = $stmt->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Deposit Names</h4>
                <h6>Master list of deposits</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addDepModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Deposit Account</a>
            </div>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success mt-2"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr><th>Name</th><th>Phone</th><th>Address</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deposits as $dep): ?>
                            <tr>
                                <td><?php echo $dep['name']; ?></td>
                                <td><?php echo $dep['phone']; ?></td>
                                <td><?php echo $dep['address']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteDep(<?php echo $dep['id']; ?>)">
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

<!-- Modal -->
<div class="modal fade" id="addDepModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add New Deposit Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_deposit" class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteDep(id) {
    Swal.fire({
        title: 'Delete this deposit account?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'depositors', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
