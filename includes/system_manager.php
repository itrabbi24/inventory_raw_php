<?php
/**
 * System and Database Helper Functions
 */

/**
 * Fetch all system settings
 */
if (!function_exists('getSettings')) {
    function getSettings($pdo) {
        $defaults = [
            'company_name' => 'Inventory POS',
            'company_email' => 'admin@example.com',
            'company_phone' => '0123456789',
            'company_address' => 'Dhaka, Bangladesh',
            'company_logo' => '',
            'currency_symbol' => '৳',
            'invoice_prefix' => 'INV',
            'auto_update_enabled' => '0',
            'git_remote_name' => 'origin',
            'git_branch_name' => 'main'
        ];

        try {
            $stmt = $pdo->query("SELECT key_name, key_value FROM settings");
            $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return array_merge($defaults, $dbSettings ?: []);
        } catch (Exception $e) {
            return $defaults;
        }
    }
}

/**
 * Log user activities
 */
if (!function_exists('logActivity')) {
    function logActivity($pdo, $user_id, $action, $table_name = null, $record_id = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $table_name, $record_id, $ip, $agent]);
    }
}

/**
 * Run database migrations
 */
if (!function_exists('runMigrations')) {
    function runMigrations($pdo) {
        $migrations_dir = dirname(__DIR__) . '/migrations';
        if (!is_dir($migrations_dir)) return [];

        $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `migration` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;");

        $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        $files = glob($migrations_dir . '/*.sql');
        $results = [];

        foreach ($files as $file) {
            $name = basename($file);
            if (!in_array($name, $executed)) {
                $sql = file_get_contents($file);
                if (!empty(trim($sql))) {
                    try {
                        $pdo->exec($sql);
                        $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$name]);
                        $results[] = "Success: $name";
                    } catch (Exception $e) {
                        $results[] = "Error: $name (" . $e->getMessage() . ")";
                    }
                }
            }
        }
        return $results;
    }
}

/**
 * Update current stock of a product
 */
if (!function_exists('updateStock')) {
    function updateStock($pdo, $product_id, $quantity, $type = 'add') {
        if ($type === 'add') {
            $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock + ? WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
        }
        return $stmt->execute([$quantity, $product_id]);
    }
}

