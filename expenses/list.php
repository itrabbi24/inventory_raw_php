<?php
$pageTitle = 'Expense List';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$stmt = $pdo->query("SELECT e.*, ec.name as category_name FROM expenses e LEFT JOIN expense_categories ec ON e.category_id = ec.id ORDER BY e.id DESC");
$expenses = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM expense_categories WHERE status=1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $cat_id = (int)($_POST['category_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $date   = sanitize($_POST['expense_date'] ?? date('Y-m-d'));
    $desc   = sanitize($_POST['description'] ?? '');

    if ($cat_id > 0 && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO expenses (category_id, amount, expense_date, description, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cat_id, $amount, $date, $desc, $_SESSION['user_id']]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Expense Management</h4>
                <h6>Track your business expenses</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">Add Expense</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr><th>Date</th><th>Category</th><th>Amount (৳)</th><th>Description</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $ex): ?>
                            <tr>
                                <td><?php echo $ex['expense_date']; ?></td>
                                <td><?php echo $ex['category_name']; ?></td>
                                <td><?php echo formatCurrency($ex['amount']); ?></td>
                                <td><?php echo $ex['description']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteExpense(<?php echo $ex['id']; ?>)">
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
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Expense Category</label>
                        <select name="category_id" class="select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $ec): ?><option value="<?php echo $ec['id']; ?>"><?php echo $ec['name']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Amount (৳)</label><input type="number" name="amount" step="0.01" class="form-control" required></div>
                    <div class="form-group"><label>Expense Date</label><input type="date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" class="form-control" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_expense" class="btn btn-primary">Save Expense</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteExpense(id) {
    Swal.fire({
        title: 'Delete this expense?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'expenses', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
