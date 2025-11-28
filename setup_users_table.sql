-- Create users table for authentication
CREATE TABLE IF NOT EXISTS `users` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `username` VARCHAR(50) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create default admin user (username: admin, password: admin123)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username=username;
