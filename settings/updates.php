<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$settings = getSettings($pdo);
// Fetch apply update if requested
if (isset($_GET['action']) && $_GET['action'] == 'apply') {
    $result = applyGitUpdates($pdo, $settings, true); // Force manually
    if ($result['status'] === true) {
        $_SESSION['message'] = '<div class="alert alert-success mt-3 py-3 shadow-sm border-0"><i class="fas fa-check-circle me-2"></i><strong>Success!</strong> ' . $result['msg'] . '</div>';
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger mt-3 py-3 shadow-sm border-0"><i class="fas fa-exclamation-triangle me-2"></i><strong>Update Interrupted!</strong> ' . $result['msg'] . '</div>';
    }
    header('Location: updates.php');
    exit();
}

$pageTitle = 'System Updates';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';


// Fetch update history from database
$stmt = $pdo->query("SELECT * FROM system_updates ORDER BY applied_at DESC");
$db_history = $stmt->fetchAll();

// Fetch live history from Git
$git_history = getGitHistory(10);
$current_hash = @shell_exec("git rev-parse HEAD 2>nul") ?: 'N/A';
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Software Version & Updates</h4>
                <h6>Keep your inventory system up to date</h6>
            </div>
            <div class="page-btn">
                <a href="updates.php?action=apply" class="btn btn-added shadow-sm"><img src="<?php echo BASE_URL; ?>assets/img/icons/edit.svg" alt="img" class="me-1">Update System Now</a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-4">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>


        <div class="row">
            <!-- Current Version Info -->
            <div class="col-lg-12">
                <div class="card bg-primary text-white shadow-lg border-0 mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-code-branch fa-3x opacity-50"></i>
                            </div>
                            <div>
                                <h6 class="text-uppercase small fw-bold opacity-75 mb-1">Current Version Hash</h6>
                                <h3 class="fw-bold m-0"><?php echo trim($current_hash); ?></h3>
                                <p class="m-0 small mt-1"><i class="fas fa-check-circle me-1"></i> Running on official main branch</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Changes from GitHub -->
            <div class="col-lg-6 col-sm-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark"><i class="fab fa-github me-2"></i> Recent Development History</h5>
                        <p class="text-muted small">Latest changes from the repository</p>
                    </div>
                    <div class="card-body pt-2">
                        <div class="timeline">
                            <?php if (empty($git_history)): ?>
                                <p class="text-center text-muted">No git history found.</p>
                            <?php else: ?>
                                <ul class="list-unstyled">
                                    <?php foreach ($git_history as $commit): ?>
                                        <li class="mb-4 pb-2 border-bottom last-border-0">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="fw-bold mb-1 text-primary"><?php echo htmlspecialchars($commit['message']); ?></h6>
                                                <small class="text-muted"><?php echo date('d M, Y', strtotime($commit['date'])); ?></small>
                                            </div>
                                            <p class="text-muted small mb-0">By <strong><?php echo htmlspecialchars($commit['author']); ?></strong></p>
                                            <code class="x-small text-muted"><?php echo substr($commit['hash'], 0, 7); ?></code>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Log Database -->
            <div class="col-lg-6 col-sm-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark"><i class="fas fa-history me-2"></i> Local Update Logs</h5>
                        <p class="text-muted small">Record of updates applied to this server</p>
                    </div>
                    <div class="card-body pt-2">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="small fw-bold">Date</th>
                                        <th class="small fw-bold">Message</th>
                                        <th class="small fw-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($db_history)): ?>
                                        <tr><td colspan="3" class="text-center py-4">No local logs found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($db_history as $log): ?>
                                            <tr>
                                                <td class="small"><?php echo date('d M, Y H:i', strtotime($log['applied_at'])); ?></td>
                                                <td>
                                                    <span class="d-block small fw-bold"><?php echo htmlspecialchars($log['commit_message']); ?></span>
                                                    <code class="x-small opacity-50"><?php echo substr($log['version_hash'], 0, 7); ?></code>
                                                </td>
                                                <td><span class="badge bg-success-light text-success rounded-pill px-3">Applied</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-success-light { background-color: #e2f6ed; }
.x-small { font-size: 11px; }
.timeline h6 { font-size: 14px; }
.last-border-0:last-child { border-bottom: 0 !important; }
.card { transition: transform 0.2s; }
.card:hover { transform: translateY(-5px); }
.bg-primary { background-color: #ff9b44 !important; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
