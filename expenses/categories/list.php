<?php
$pageTitle = 'Expense Categories';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$stmt = $pdo->query("SELECT * FROM expense_categories WHERE status=1 ORDER BY id DESC");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name'] ?? '');
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO expense_categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Expense Categories</h4>
                <h6>Manage expense types</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addCatModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Category</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr><th>Category Name</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $ec): ?>
                            <tr>
                                <td><?php echo $ec['name']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteCat(<?php echo $ec['id']; ?>)">
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
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Expense Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Category Name</label><input type="text" name="name" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_category" class="btn btn-primary">Add</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCat(id) {
    Swal.fire({
        title: 'Delete this category?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'expense_categories', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
