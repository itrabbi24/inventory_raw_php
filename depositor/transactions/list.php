<?php
$pageTitle = 'Depositor Transactions';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$stmt = $pdo->query("SELECT dt.*, d.name as depositor_name FROM depositor_transactions dt LEFT JOIN depositors d ON dt.depositor_id = d.id ORDER BY dt.id DESC");
$transactions = $stmt->fetchAll();

$depositors = $pdo->query("SELECT * FROM depositors WHERE status=1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $dep_id = (int)($_POST['depositor_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $type   = sanitize($_POST['type'] ?? 'deposit');
    $date   = sanitize($_POST['transaction_date'] ?? date('Y-m-d'));
    $notes  = sanitize($_POST['notes'] ?? '');

    if ($dep_id > 0 && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO depositor_transactions (depositor_id, amount, type, transaction_date, notes, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$dep_id, $amount, $type, $date, $notes, $_SESSION['user_id']]);
        header('Location: list.php');
        exit();
    }
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Depositor Transactions</h4>
                <h6>Track deposits & withdrawals</h6>
            </div>
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addTransModal"><img src="<?php echo BASE_URL; ?>assets/img/icons/plus.svg" alt="img" class="me-1">New Transaction</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr><th>Date</th><th>Name</th><th>Amount (৳)</th><th>Type</th><th>Notes</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tr): ?>
                            <tr>
                                <td><?php echo $tr['transaction_date']; ?></td>
                                <td><?php echo $tr['depositor_name']; ?></td>
                                <td><?php echo formatCurrency($tr['amount']); ?></td>
                                <td><span class="badges <?php echo ($tr['type'] == 'deposit') ? 'bg-lightgreen' : 'bg-lightred'; ?>"><?php echo ucfirst($tr['type']); ?></span></td>
                                <td><?php echo $tr['notes']; ?></td>
                                <td>
                                    <a class="confirm-text" href="javascript:void(0);" onclick="deleteTrans(<?php echo $tr['id']; ?>)">
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
<div class="modal fade" id="addTransModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Transaction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="list.php" method="POST">
                <div class="modal-body">
                    <div class="form-group"><label>Select Depositor</label>
                        <select name="depositor_id" class="select" required>
                            <option value="">Choose Name</option>
                            <?php foreach ($depositors as $d): ?><option value="<?php echo $d['id']; ?>"><?php echo $d['name']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Amount (৳)</label><input type="number" name="amount" step="0.01" class="form-control" required></div>
                    <div class="form-group"><label>Type</label>
                        <select name="type" class="select" required>
                            <option value="deposit">Deposit</option>
                            <option value="withdraw">Withdraw</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Date</label><input type="date" name="transaction_date" value="<?php echo date('Y-m-d'); ?>" class="form-control" required></div>
                    <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_transaction" class="btn btn-primary">Save Transaction</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteTrans(id) {
    Swal.fire({
        title: 'Delete this transaction?',
        icon: 'warning',
        showCancelButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('<?php echo BASE_URL; ?>ajax/delete_record.php', {table: 'depositor_transactions', id: id}, () => location.reload());
        }
    })
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
