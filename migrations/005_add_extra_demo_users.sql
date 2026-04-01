-- Add more demo users for different roles
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) VALUES 
('Demo Stock Manager', 'stock@example.com', SHA2('stock123', 256), 'stock_manager', 1),
('Demo Accountant', 'accountant@example.com', SHA2('acc123', 256), 'accountant', 1);
