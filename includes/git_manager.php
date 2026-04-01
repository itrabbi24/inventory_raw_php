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
    function applyGitUpdates($pdo, $settings) {
        if (!($settings['auto_update_enabled'] ?? '0') === '1') return "Auto-update disabled";
        
        $remote = $settings['git_remote_name'] ?? 'origin';
        $branch = $settings['git_branch_name'] ?? 'main';
        
        // Before pulling, get the latest remote commit for logging
        @shell_exec("git fetch {$remote} 2>nul");
        $remote_log = @shell_exec("git log -n 1 --pretty=format:\"%H|%s|%an|%ad\" {$remote}/{$branch} 2>nul");
        
        // Execute reset and pull
        $resetData = @shell_exec("git reset --hard {$remote}/{$branch} 2>&1") ?: "Reset: [No Output]";
        $pullData = @shell_exec("git pull {$remote} {$branch} 2>&1") ?: "Pull: [No Output]";
        
        // Log the update to database if successful
        if ($remote_log) {
            $parts = explode("|", trim($remote_log));
            if (count($parts) == 4) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO system_updates (version_hash, commit_message, author_name, commit_date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$parts[0], $parts[1], $parts[2], date('Y-m-d H:i:s', strtotime($parts[3]))]);
            }
        }
        
        return $resetData . "\n" . $pullData;
    }
}

