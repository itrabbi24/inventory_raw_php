<?php
$pageTitle = 'General Settings';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Regular settings update
    if (isset($_POST['settings'])) {
        // Handle checkbox (if missing, set to 0)
        if (!isset($_POST['settings']['auto_update_enabled'])) {
            $_POST['settings']['auto_update_enabled'] = '0';
        }
        foreach ($_POST['settings'] as $key => $value) {

            $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = ?");
            $stmt->execute([sanitize($value), $key]);
        }
    }

    // Logo upload handling
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        try {
            $logo_name = uploadFile($_FILES['company_logo'], 'logo');
            $stmt = $pdo->prepare("UPDATE settings SET key_value = ? WHERE key_name = 'company_logo'");
            $stmt->execute([$logo_name]);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
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

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="general.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <h5 class="fw-bold text-warning border-bottom pb-2">Business Information</h5>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">COMPANY NAME</label>
                                <input type="text" name="settings[company_name]" value="<?php echo $settings['company_name']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">EMAIL ADDRESS</label>
                                <input type="email" name="settings[company_email]" value="<?php echo $settings['company_email']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">PHONE NUMBER</label>
                                <input type="text" name="settings[company_phone]" value="<?php echo $settings['company_phone']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-12 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">ADDRESS</label>
                                <textarea name="settings[company_address]" class="form-control" rows="2"><?php echo $settings['company_address']; ?></textarea>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-4 mt-4">
                            <h5 class="fw-bold text-warning border-bottom pb-2">Branding & Identity</h5>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">COMPANY LOGO</label>
                                <div class="custom-file-container mt-2">
                                    <div class="mb-3">
                                        <?php 
                                        $logo = !empty($settings['company_logo']) ? BASE_URL . 'uploads/logo/' . $settings['company_logo'] : BASE_URL . 'assets/img/logo.png';
                                        ?>
                                        <img src="<?php echo $logo; ?>" alt="logo" class="img-thumbnail" style="max-height: 100px; background: #f8f9fa;">
                                    </div>
                                    <input type="file" name="company_logo" class="form-control">
                                    <small class="text-muted">Recommended size: 200x100px (PNG/JPG)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">CURRENCY SYMBOL</label>
                                <input type="text" name="settings[currency_symbol]" value="<?php echo $settings['currency_symbol']; ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">INVOICE PREFIX</label>
                                <input type="text" name="settings[invoice_prefix]" value="<?php echo $settings['invoice_prefix']; ?>" class="form-control">
                            </div>
                        </div>

                        <div class="col-lg-12 mb-4 mt-4">
                            <h5 class="fw-bold text-danger border-bottom pb-2"><i class="fas fa-sync-alt me-2"></i>Software Maintenance</h5>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-4">
                                <label class="text-muted small fw-bold">AUTO-UPDATE SYSTEM</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="settings[auto_update_enabled]" value="1" id="autoUpd" <?php echo ($settings['auto_update_enabled'] == '1') ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted" for="autoUpd">Check & Pull Updates from GitHub</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">GIT REMOTE NAME</label>
                                <input type="text" name="settings[git_remote_name]" value="<?php echo $settings['git_remote_name'] ?: 'origin'; ?>" class="form-control" placeholder="origin">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="form-group mb-3">
                                <label class="text-muted small fw-bold">GIT BRANCH NAME</label>
                                <input type="text" name="settings[git_branch_name]" value="<?php echo $settings['git_branch_name'] ?: 'main'; ?>" class="form-control" placeholder="main">
                            </div>
                        </div>

                        <div class="col-lg-12 mt-2">
                             <div class="alert alert-light border border-dashed p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Manual System Update</h6>
                                    <p class="text-muted small mb-0">Forces a check for new code regardless of the 30-minute auto-check interval.</p>
                                </div>
                                <a href="<?php echo BASE_URL; ?>auth/update_progress.php?force=1" class="btn btn-outline-danger fw-bold px-4 shadow-sm">
                                    <i class="fas fa-arrow-alt-circle-up me-2"></i>CHECK & UPDATE NOW
                                </a>
                             </div>
                        </div>


                        
                        <div class="col-lg-12 mt-4 text-end">
                            <button type="submit" class="btn btn-warning px-5 py-2 text-white fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>SAVE CONFIGURATION
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
