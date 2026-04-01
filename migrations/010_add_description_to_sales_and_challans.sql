-- 010_add_description_to_sales_and_challans.sql
ALTER TABLE `sale_items` ADD COLUMN `description` TEXT AFTER `product_id`;
ALTER TABLE `challan_items` ADD COLUMN `description` TEXT AFTER `product_id`;
