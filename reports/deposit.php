<?php
$pageTitle = 'Deposit Report';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$start_date  = $_GET['start_date'] ?? date('Y-m-01');
$end_date    = $_GET['end_date'] ?? date('Y-m-d');
$depositor_id = (int)($_GET['depositor_id'] ?? 0);

$query = "SELECT dt.*, d.name as depositor_name FROM depositor_transactions dt LEFT JOIN depositors d ON dt.depositor_id = d.id WHERE dt.transaction_date BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($depositor_id > 0) {
    $query .= " AND dt.depositor_id = ?";
    $params[] = $depositor_id;
}

$query .= " ORDER BY dt.transaction_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$total_deposit = 0;
$total_withdraw = 0;
foreach ($transactions as $tr) {
    if ($tr['type'] == 'deposit') $total_deposit += (float)$tr['amount'];
    else $total_withdraw += (float)$tr['amount'];
}

$depositors = $pdo->query("SELECT * FROM depositors WHERE status=1")->fetchAll();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Deposit Report</h4>
                <h6>Deposit & Withdraw summary</h6>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="deposit.php" method="GET">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group"><label>Account Name</label>
                                <select name="depositor_id" class="select">
                                    <option value="0">All Accounts</option>
                                    <?php foreach ($depositors as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($depositor_id == $d['id']) ? 'selected' : ''; ?>><?php echo $d['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2"><div class="form-group"><label>From</label><input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-2"><div class="form-group"><label>To</label><input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control"></div></div>
                        <div class="col-lg-2"><button type="submit" class="btn btn-primary mt-4">Filter Report</button></div>
                    </div>
                </form>

                <div class="row mt-4 g-3">
                    <div class="col-lg-4 col-sm-6 col-12"><div class="dash-count das1"><div class="dash-counts"><h4><?php echo formatCurrency($total_deposit); ?></h4><h5>Total Deposited</h5></div></div></div>
                    <div class="col-lg-4 col-sm-6 col-12"><div class="dash-count das2"><div class="dash-counts"><h4><?php echo formatCurrency($total_withdraw); ?></h4><h5>Total Withdrawn</h5></div></div></div>
                    <div class="col-lg-4 col-sm-6 col-12"><div class="dash-count das3"><div class="dash-counts"><h4><?php echo formatCurrency($total_deposit - $total_withdraw); ?></h4><h5>Net Balance</h5></div></div></div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-hover datatable">
                        <thead class="bg-light">
                            <tr><th>Date</th><th>Account Name</th><th>Type</th><th>Amount (৳)</th><th>Notes</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $tr): ?>
                            <tr>
                                <td><?php echo $tr['transaction_date']; ?></td>
                                <td><?php echo $tr['depositor_name']; ?></td>
                                <td><span class="badges <?php echo ($tr['type'] == 'deposit') ? 'bg-lightgreen' : 'bg-lightred'; ?>"><?php echo ucfirst($tr['type']); ?></span></td>
                                <td class="fw-bold"><?php echo number_format($tr['amount'], 2); ?></td>
                                <td><?php echo $tr['notes']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
