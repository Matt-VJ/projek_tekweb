-- Create comments table
CREATE TABLE IF NOT EXISTS `comments` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `post_id` INT NOT NULL,
   `user_id` INT NULL,
   `author_name` VARCHAR(100) NULL,
   `content` TEXT NOT NULL,
   `status` ENUM('pending', 'approved', 'spam') NOT NULL DEFAULT 'approved',
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   KEY `post_id` (`post_id`),
   KEY `user_id` (`user_id`),
   KEY `status` (`status`),
   CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `topher_posts` (`id`) ON DELETE CASCADE,
   CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
