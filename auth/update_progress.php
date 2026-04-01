<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$settings = getSettings($pdo);
$status = "Initializing System Update...";
$done = false;

if (isset($_GET['execute'])) {
    $output = applyGitUpdates($pdo, $settings);
    // After code update, run migrations again just in case the code change included new migrations
    runMigrations($pdo);
    echo json_encode(['success' => true, 'output' => $output]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Update in Progress</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/animate.css">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .update-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 500px; text-align: center; border-top: 5px solid #ff9b44; }
        .loader-spin { font-size: 4rem; color: #ff9b44; margin-bottom: 20px; }
        .progress { height: 10px; border-radius: 5px; margin: 30px 0; }
        .progress-bar { background: #ff9b44; transition: width 0.5s ease; }
        .console-box { background: #1e1e1e; color: #0f0; padding: 15px; border-radius: 8px; text-align: left; font-family: monospace; font-size: 12px; max-height: 150px; overflow-y: auto; margin-top: 20px; display: none; }
    </style>
</head>
<body>
    <div class="update-card animate__animated animate__fadeInDown">
        <div class="loader-spin">
            <i class="fas fa-sync fa-spin"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">Updating System</h3>
        <p class="text-muted" id="status-text">Pulling latest changes from GitHub...</p>
        
        <div class="progress">
            <div id="pb" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 10%"></div>
        </div>

        <div id="console" class="console-box"></div>

        <div id="complete-btn" style="display:none;">
            <a href="../dashboard/index.php" class="btn btn-warning px-5 py-2 fw-bold text-white shadow-sm">
                GO TO DASHBOARD <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#pb').css('width', '50%');
                $('#status-text').text('Applying code changes and verifying files...');
                
                $.ajax({
                    url: 'update_progress.php?execute=1',
                    dataType: 'json',
                    success: function(res) {
                        $('#pb').css('width', '100%');
                        $('#pb').removeClass('progress-bar-animated');
                        $('#status-text').html('<span class="text-success fw-bold">Update Complete!</span> System is now up to date.');
                        $('#console').html(res.output.replace(/\n/g, '<br>')).fadeIn();
                        $('.loader-spin').html('<i class="fas fa-check-circle text-success animate__animated animate__bounceIn"></i>');
                        $('#complete-btn').fadeIn();
                    },
                    error: function() {
                        $('#status-text').html('<span class="text-danger">Update Failed!</span> Please contact support.');
                        $('.loader-spin').html('<i class="fas fa-exclamation-triangle text-danger"></i>');
                    }
                });
            }, 2000);
        });
    </script>
</body>
</html>
