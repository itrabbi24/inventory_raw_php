<?php

/**
 * Auto-generate sequential invoice/reference numbers.
 * Format: {PREFIX}-YYYYMMDD-{SEQ padded to 3 digits}
 * Example: INV-20240315-001
 */
function generateInvoiceNo(PDO $pdo, string $prefix, string $table, string $column): string {
    $today = date('Ymd');
    $likePattern = $prefix . '-' . $today . '-%';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` LIKE ?");
    $stmt->execute([$likePattern]);
    $count = (int)$stmt->fetchColumn();
    $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    return $prefix . '-' . $today . '-' . $seq;
}

/**
 * Get current stock quantity for a product.
 */
function getCurrentStock(PDO $pdo, int $product_id): int {
    $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return (int)($stmt->fetchColumn() ?? 0);
}

/**
 * Update product stock.
 * @param string $type 'add' | 'subtract'
 */
function updateStock(PDO $pdo, int $product_id, int $quantity, string $type): void {
    if ($type === 'add') {
        $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock + ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    } elseif ($type === 'subtract') {
        $current = getCurrentStock($pdo, $product_id);
        if ($current < $quantity) {
            throw new Exception("Insufficient stock for product ID {$product_id}. Available: {$current}, Requested: {$quantity}");
        }
        $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    }
}

/**
 * Format an amount with ৳ currency symbol.
 */
function formatCurrency(float $amount): string {
    return '৳ ' . number_format($amount, 2);
}

/**
 * Sanitize user input — strips tags, trims, encodes special chars.
 */
function sanitize(?string $input): string {
    if ($input === null) return '';
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Log user activity.
 */
function logActivity(PDO $pdo, int $user_id, string $action, string $module, int $reference_id = 0): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare(
        "INSERT INTO activity_log (user_id, action, module, reference_id, ip_address) VALUES (?,?,?,?,?)"
    );
    $stmt->execute([$user_id, $action, $module, $reference_id, $ip]);
}

/**
 * Return all settings as key => value associative array.
 */
function getSettings(PDO $pdo): array {
    $stmt = $pdo->query("SELECT key_name, key_value FROM settings");
    $rows = $stmt->fetchAll();
    $result = [];
    foreach ($rows as $row) {
        $result[$row['key_name']] = $row['key_value'];
    }
    return $result;
}

/**
 * Generate a CSRF token and store in session.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Upload a file safely. Returns saved filename or throws Exception.
 */
function uploadFile(array $file, string $subFolder, array $allowedExt = ['jpg','jpeg','png','gif','webp'], int $maxSize = 2097152): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size is ' . ($maxSize / 1048576) . 'MB.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedExt));
    }
    // Verify MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception('Invalid file MIME type.');
    }
    $destDir = UPLOAD_PATH . $subFolder . '/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $filename = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
        throw new Exception('Failed to move uploaded file.');
    }
    return $filename;
}

/**
 * Run pending database migrations from the migrations directory.
 * Scan for .sql files, execute them, and store the filename in migrations table.
 */
function runMigrations(PDO $pdo): array {
    $results = [];
    
    // 1. Ensure migrations table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `migration_name` VARCHAR(255) NOT NULL UNIQUE,
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $migrationPath = __DIR__ . '/../migrations/';
    if (!is_dir($migrationPath)) {
        mkdir($migrationPath, 0755, true);
    }

    // 2. Scan migrations directory
    $files = glob($migrationPath . '*.sql');
    if (!$files) return [];
    sort($files); // Run in order

    // 3. Get already executed ones
    $stmt = $pdo->query("SELECT migration_name FROM migrations");
    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($files as $file) {
        $name = basename($file);
        if (!in_array($name, $executed)) {
            try {
                $sqlSnippet = file_get_contents($file);
                if (!empty(trim($sqlSnippet))) {
                    // Split into multiple statements if necessary
                    $queries = array_filter(array_map('trim', explode(';', $sqlSnippet)));
                    foreach ($queries as $q) {
                        $pdo->exec($q);
                    }
                }
                // Mark as executed
                $stmtExec = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
                $stmtExec->execute([$name]);
                $results[] = "Success: " . $name;
            } catch (PDOException $e) {
                // If it's a minor error (e.g. column already exists), still mark it as executed
                if (stripos($e->getMessage(), 'Duplicate column name') !== false ||
                    stripos($e->getMessage(), 'Already exists') !== false) {
                     $stmtExec = $pdo->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
                     $stmtExec->execute([$name]);
                     $results[] = "Skipped (already applied): " . $name;
                } else {
                    $results[] = "Error in " . $name . ": " . $e->getMessage();
                }
            }
        }
    }
    return $results;
}

/**
 * Check for updates in the remote Git repository
 */
function checkGitUpdates($pdo, $settings) {
    if (!($settings['auto_update_enabled'] ?? 0)) return false;
    
    // 1. Check if this is a Git repository. If not, initialize it.
    if (!is_dir(dirname(__DIR__) . '/.git')) {
        $repo_url = "https://github.com/itrabbi24/inventory_raw_php.git";
        $remote = $settings['git_remote_name'] ?? 'origin';
        
        @shell_exec("git init 2>nul");
        @shell_exec("git remote add {$remote} {$repo_url} 2>nul");
        @shell_exec("git fetch {$remote} 2>nul");
        return true;
    }


    // 2. Fetch remote carefully (suppress stderr Windows style)
    @shell_exec("git fetch {$remote} 2>nul");
    
    // 3. Compare local and remote commits
    $local_output = @shell_exec("git rev-parse HEAD 2>nul");
    $remote_output = @shell_exec("git rev-parse {$remote}/{$branch} 2>nul");


    $local_hash  = $local_output ? trim((string)$local_output) : '';
    $remote_hash = $remote_output ? trim((string)$remote_output) : '';
    
    if (empty($local_hash) || empty($remote_hash)) return false;

    return ($local_hash !== $remote_hash);
}



/**
 * Execute Git pull to update the codebase
 */
function applyGitUpdates($pdo, $settings) {
    if (!($settings['auto_update_enabled'] ?? '0') === '1') return "Auto-update disabled";
    
    $remote = $settings['git_remote_name'] ?? 'origin';
    $branch = $settings['git_branch_name'] ?? 'main';
    
    // Force pull with output capture to avoid null errors (Windows compatible)
    $resetData = @shell_exec("git reset --hard {$remote}/{$branch} 2>&1") ?: "Reset data: [No Output]";
    $pullData = @shell_exec("git pull {$remote} {$branch} 2>&1") ?: "Pull data: [No Output]";
    
    return $resetData . "\n" . $pullData;
}




