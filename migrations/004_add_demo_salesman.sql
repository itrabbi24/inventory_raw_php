-- Add a demo salesman user
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`) 
VALUES ('Demo Salesman', 'salesman@example.com', SHA2('sales123', 256), 'salesman', 1);
