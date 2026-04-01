-- 009_add_description_to_quotation_items.sql
ALTER TABLE `quotation_items` 
ADD COLUMN `description` TEXT AFTER `product_id`;
