<?php
$pageTitle = 'General Settings';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
        $stmt->execute([sanitize($value), $key]);
    }
    logActivity($pdo, $_SESSION['user_id'], 'Updated general settings', 'settings', 0);
    $message = 'Settings updated successfully!';
    // Refresh settings
    $settings = getSettings($pdo);
}
?>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>General Settings</h4>
                <h6>Manage your company configuration</h6>
            </div>
        </div>
        
        <?php if ($message): ?><div class="alert alert-success mt-3"><?php echo $message; ?></div><?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="general.php" method="POST">
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="settings[company_name]" value="<?php echo $settings['company_name']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Company Email</label>
                                <input type="email" name="settings[company_email]" value="<?php echo $settings['company_email']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Company Phone</label>
                                <input type="text" name="settings[company_phone]" value="<?php echo $settings['company_phone']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Company Address</label>
                                <textarea name="settings[company_address]" class="form-control"><?php echo $settings['company_address']; ?></textarea>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Currency Symbol</label>
                                <input type="text" name="settings[currency_symbol]" value="<?php echo $settings['currency_symbol']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Invoice Prefix</label>
                                <input type="text" name="settings[invoice_prefix]" value="<?php echo $settings['invoice_prefix']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Update Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
