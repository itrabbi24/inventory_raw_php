<?php
/**
 * Git Update Manager Functions
 * Handles automated pulling and system updates.
 */

if (!function_exists('checkGitUpdates')) {
    function checkGitUpdates($pdo, $settings) {
        if (!($settings['auto_update_enabled'] ?? 0)) return false;
        
        // Ensure .git directory exists or initialize
        if (!is_dir(dirname(__DIR__) . '/.git')) {
            $repo_url = "https://github.com/itrabbi24/inventory_raw_php.git";
            $remote = $settings['git_remote_name'] ?? 'origin';
            @shell_exec("git init 2>nul");
            @shell_exec("git remote add {$remote} {$repo_url} 2>nul");
            @shell_exec("git fetch {$remote} 2>nul");
            return true;
        }

        $remote = $settings['git_remote_name'] ?? 'origin';
        $branch = $settings['git_branch_name'] ?? 'main';
        
        @shell_exec("git fetch {$remote} 2>nul");
        
        $local_output = @shell_exec("git rev-parse HEAD 2>nul");
        $remote_output = @shell_exec("git rev-parse {$remote}/{$branch} 2>nul");

        $local_hash  = $local_output ? trim((string)$local_output) : '';
        $remote_hash = $remote_output ? trim((string)$remote_output) : '';
        
        if (empty($local_hash) || empty($remote_hash)) return false;
        return ($local_hash !== $remote_hash);
    }
}

if (!function_exists('getGitHistory')) {
    function getGitHistory($limit = 10) {
        $format = "%H|%s|%an|%ad";
        $output = @shell_exec("git log -n {$limit} --pretty=format:\"{$format}\" 2>nul");
        if (!$output) return [];

        $history = [];
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            $parts = explode("|", $line);
            if (count($parts) == 4) {
                $history[] = [
                    'hash'    => $parts[0],
                    'message' => $parts[1],
                    'author'  => $parts[2],
                    'date'    => $parts[3]
                ];
            }
        }
        return $history;
    }
}

if (!function_exists('applyGitUpdates')) {
    function applyGitUpdates($pdo, $settings, $force = false) {
    if (!$force && ($settings['auto_update_enabled'] ?? '0') !== '1') return ["status" => false, "msg" => "Auto-update disabled"];
        
        $remote = $settings['git_remote_name'] ?? 'origin';
        $branch = $settings['git_branch_name'] ?? 'main';
        
        // 1. Capture the current state (Snapshot for Rollback)
        $current_hash = @shell_exec("git rev-parse HEAD 2>nul") ?: null;
        
        try {
            // 2. Fetch the latest changes
            @shell_exec("git fetch {$remote} 2>nul");
            $target_log = @shell_exec("git log -n 1 --pretty=format:\"%H|%s|%an|%ad\" {$remote}/{$branch} 2>nul");
            
            if (!$target_log) throw new Exception("Could not connect to GitHub repository.");
            
            $target_parts = explode("|", trim($target_log));
            $target_hash = $target_parts[0];

            // 3. Attempt Update
            $reset_out = @shell_exec("git reset --hard {$remote}/{$branch} 2>&1");
            $pull_out = @shell_exec("git pull {$remote} {$branch} 2>&1");
            
            // 4. Verification Check
            $new_hash = @shell_exec("git rev-parse HEAD 2>nul");
            if (trim((string)$new_hash) !== trim($target_hash)) {
                throw new Exception("Code alignment failed. Git reset was incomplete.");
            }

            // 5. Automated Database Migration
            $migration_results = runMigrations($pdo);
            $mig_msg = count($migration_results) > 0 ? implode(", ", $migration_results) : "Database Up-to-date";

            // 6. Log success to database
            $stmt = $pdo->prepare("INSERT IGNORE INTO system_updates (version_hash, commit_message, author_name, commit_date, status) VALUES (?, ?, ?, ?, 'success')");
            $stmt->execute([$target_parts[0], $target_parts[1], $target_parts[2], date('Y-m-d H:i:s', strtotime($target_parts[3]))]);

            return [
                "status" => true, 
                "msg" => "Update Successful! Version: " . substr($target_hash,0,7) . ". Migrations: " . $mig_msg
            ];

        } catch (Exception $e) {
            // 7. ROLLBACK Mechanism
            if ($current_hash) {
                @shell_exec("git reset --hard {$current_hash} 2>nul");
                return ["status" => false, "msg" => "Fatal Error: " . $e->getMessage() . ". Rolling back to version " . substr($current_hash,0,7)];
            }
            return ["status" => false, "msg" => "Update Failed: " . $e->getMessage()];
        }
    }
}


