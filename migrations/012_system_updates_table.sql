-- 012_system_updates_table.sql
CREATE TABLE IF NOT EXISTS `system_updates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `version_hash` VARCHAR(100) NOT NULL,
  `commit_message` TEXT,
  `author_name` VARCHAR(100),
  `commit_date` DATETIME,
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(20) DEFAULT 'success',
  UNIQUE KEY (`version_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
