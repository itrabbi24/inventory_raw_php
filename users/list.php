<?php
$pageTitle = 'User List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Only superadmin can manage users
if (!hasPermission('users')) {
    echo "<script>window.location='".BASE_URL."dashboard/index.php';</script>";
    exit();
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name     = sanitize($_POST['name'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $role     = sanitize($_POST['role'] ?? 'salesman');
    $password = password_hash($_POST['password'] ?? 'user123', PASSWORD_DEFAULT);

    if (!empty($name) && !empty($email)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $role, $password]);
            header('Location: list.php');
            exit();
        } catch (PDOException $e) { $error = "Email already exists!"; }
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>User Management</h4>
                <h6>Manage application users & roles</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addUserModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add User</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><span class="badges bg-lightyellow"><?php echo ucfirst($user['role']); ?></span></td>
                                <td><span class="badges <?php echo $user['status'] ? 'bg-lightgreen' : 'bg-lightred'; ?>"><?php echo $user['status'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteUser(<?php echo $user['id']; ?>)">
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
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Role</label>
                        <select name="role" class="select" required>
                            <option value="salesman">Salesman</option>
                            <option value="admin">Admin</option>
                            <option value="stock_manager">Stock Manager</option>
                            <option value="accountant">Accountant</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Set Password</label><input type="password" name="password" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-primary">Create User</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    if(id == 1) { Swal.fire('Error', 'Cannot delete superadmin!', 'error'); return; }
    Swal.fire({
        title: 'Delete this user?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'users', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
