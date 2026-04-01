<?php
$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='".BASE_URL."dashboard/index.php';</script>";
    exit();
}

$message = '';
$error = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name     = sanitize($_POST['name']);
    $email    = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = sanitize($_POST['role']);

    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        $error = "Email already exists!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 1)");
        if ($stmt->execute([$name, $email, $password, $role])) {
            logActivity($pdo, $_SESSION['user_id'], "Created new user: {$email} ({$role})", 'users', $pdo->lastInsertId());
            $message = "User created successfully!";
        } else {
            $error = "Failed to create user.";
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>User Management</h4>
                <h6>Control system access and roles</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add New User
                </a>
            </div>
        </div>

        <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-light-primary text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-weight: bold;">
                                            <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <?php echo $u['name']; ?>
                                        <?php if($u['id'] == $_SESSION['user_id']): ?><span class="badge bg-soft-info text-info ms-2">You</span><?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo $u['email']; ?></td>
                                <td>
                                    <span class="badges <?php 
                                        echo ($u['role'] == 'admin') ? 'bg-lightred' : (($u['role'] == 'manager') ? 'bg-lightgreen' : 'bg-lightyellow'); 
                                    ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $u['status'] ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                                        <?php echo $u['status'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteUser(<?php echo $u['id']; ?>)">
                                        <img src="<?php echo BASE_URL; ?>assets/img/icons/delete.svg" alt="img">
                                    </a>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title fw-bold">Create System User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold">FULL NAME</label>
                        <input type="text" name="name" class="form-control border-0 bg-light" placeholder="e.g. John Doe" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control border-0 bg-light" placeholder="user@company.com" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-muted small fw-bold">PASSWORD</label>
                        <input type="password" name="password" class="form-control border-0 bg-light" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-muted small fw-bold">SYSTEM ROLE</label>
                        <select name="role" class="select" required>
                            <option value="salesman">Salesman (POS Only)</option>
                            <option value="manager">Manager (Operations)</option>
                            <option value="admin">Administrator (Full Access)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-warning px-5 text-white fw-bold shadow-sm">CREATE ACCOUNT</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    Swal.fire({
        title: 'Remove User Account?',
        text: "This user will no longer be able to log in.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff9f43',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Remove User'
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'users', id: id}, () => location.reload());
        }
    })
}
</script>

<style>
    .bg-soft-info { background-color: rgba(0, 184, 217, 0.1); }
    .bg-light-primary { background-color: #f0f7ff; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
