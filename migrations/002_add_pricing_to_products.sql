ALTER TABLE products 
ADD COLUMN buying_price DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER min_stock_alert,
ADD COLUMN selling_price DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER buying_price;
