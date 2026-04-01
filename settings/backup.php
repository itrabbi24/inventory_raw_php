<?php
$pageTitle = 'Database Backup';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Only Admins
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin') {
    echo "<script>window.location.href='".BASE_URL."dashboard/index.php';</script>";
    exit();
}

if (isset($_GET['download'])) {
    $filename = "backup_" . date("Y-m-d_H-i-s") . ".sql";
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Simple mysqldump-like logic (though we'll do it via PDO for portability)
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $output = "-- Inventory Management System Backup\n-- Date: " . date("Y-m-d H:i:s") . "\n\n";

    foreach ($tables as $table) {
        $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $output .= "\n\n" . $createTable['Create Table'] . ";\n\n";
        
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $keys = array_keys($row);
            $values = array_map(function($v) use ($pdo) { 
                return $v === null ? 'NULL' : $pdo->quote($v); 
            }, array_values($row));
            
            $output .= "INSERT INTO `{$table}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $values) . ");\n";
        }
    }
    
    logActivity($pdo, $_SESSION['user_id'], 'Downloaded database backup', 'settings', 0);
    echo $output;
    exit();
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>System Backup</h4>
                <h6>Keep your data safe with one-click exports</h6>
            </div>
        </div>

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <div class="bg-light d-inline-flex p-4 rounded-circle mb-3">
                        <img src="<?php echo BASE_URL; ?>assets/img/icons/dash1.svg" alt="db" style="width: 64px; height: 64px;">
                    </div>
                    <h3 class="fw-bold">Database Safeguard</h3>
                    <p class="text-muted mx-auto" style="max-width: 500px;">
                        Protect your business data by downloading a complete SQL snapshot of your inventory, sales, and accounts. We recommend backing up at the end of every business day.
                    </p>
                </div>
                
                <div class="alert alert-warning d-inline-block text-start border-0 shadow-sm mb-4">
                    <ul class="mb-0 small fw-bold">
                        <li><i class="fas fa-check-circle me-2"></i> Includes all product and stock records</li>
                        <li><i class="fas fa-check-circle me-2"></i> Captures full sales and history history</li>
                        <li><i class="fas fa-check-circle me-2"></i> Secure SQL format for easy recovery</li>
                    </ul>
                </div>

                <div>
                    <a href="?download=1" class="btn btn-warning btn-lg text-white px-5 py-3 fw-bold shadow-sm">
                        <i class="fas fa-file-export me-2"></i> START BACKUP DOWNLOAD
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
