-- 011_update_activity_log_table.sql
ALTER TABLE `activity_log` 
ADD COLUMN `table_name` VARCHAR(50) NULL AFTER `action`,
ADD COLUMN `record_id` INT UNSIGNED NULL AFTER `table_name`;
