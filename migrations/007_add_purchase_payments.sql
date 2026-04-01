-- 1. Alter stock_in to add payment fields
ALTER TABLE `stock_in` 
ADD COLUMN `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `total_price`,
ADD COLUMN `due_amount` DECIMAL(15,2) GENERATED ALWAYS AS (`total_price` - `paid_amount`) STORED AFTER `paid_amount`;

-- 2. Create purchase_payments table for vendor payments
CREATE TABLE IF NOT EXISTS `purchase_payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stock_in_id` INT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `method` ENUM('cash','bkash','nagad','bank','credit') NOT NULL DEFAULT 'cash',
  `note` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
