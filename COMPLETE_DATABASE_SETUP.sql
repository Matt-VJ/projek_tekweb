CREATE DATABASE IF NOT EXISTS `mini_cms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mini_cms`;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `username` VARCHAR(50) NOT NULL,
   `password` VARCHAR(255) NOT NULL,
   `role` ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

DROP TABLE IF EXISTS `topher_categories`;
CREATE TABLE `topher_categories` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL,
   `slug` VARCHAR(120) NOT NULL,
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `topher_categories` (`name`, `slug`) VALUES
('Technology', 'technology'),
('Lifestyle', 'lifestyle'),
('Business', 'business'),
('Travel', 'travel'),
('Food', 'food');

DROP TABLE IF EXISTS `topher_posts`;
CREATE TABLE `topher_posts` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `title` VARCHAR(255) NOT NULL,
   `excerpt` TEXT NULL,
   `content` TEXT NULL,
   `category_id` INT NULL,
   `image` VARCHAR(500) NULL,
   `published_at` DATETIME NULL,
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   KEY `category_id` (`category_id`),
   KEY `published_at` (`published_at`),
   CONSTRAINT `fk_topher_posts_category` FOREIGN KEY (`category_id`) REFERENCES `topher_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `topher_posts` (`title`, `excerpt`, `content`, `category_id`, `image`, `published_at`) VALUES
('Welcome to Mini CMS', 
 'Your new content management system is ready to use!', 
 'Welcome to your new Mini CMS! This is a powerful yet simple content management system that allows you to create, edit, and manage your blog posts with ease. You can organize posts by categories, moderate comments, and manage users. Start by creating your first post in the admin panel!', 
 1, 
 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=800', 
 NOW()),

('Getting Started Guide', 
 'Learn how to use all the features of your CMS', 
 'This guide will walk you through all the main features of Mini CMS:\n\n1. Creating Posts: Navigate to "Kelola Postingan" to create new blog posts\n2. Managing Categories: Organize your content with categories\n3. Moderating Comments: Review and approve comments from readers\n4. User Management: Add editors and admins to help manage your site\n\nEnjoy your new CMS!', 
 1, 
 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800', 
 NOW()),

('Top 10 Travel Destinations', 
 'Discover the most beautiful places to visit this year', 
 'Traveling opens up a world of experiences and memories. Here are our top 10 destinations:\n\n1. Bali, Indonesia\n2. Paris, France\n3. Tokyo, Japan\n4. New York, USA\n5. Barcelona, Spain\n6. Dubai, UAE\n7. London, UK\n8. Rome, Italy\n9. Sydney, Australia\n10. Bangkok, Thailand\n\nEach destination offers unique culture, cuisine, and adventures!', 
 4, 
 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800', 
 NOW());

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `post_id` INT NOT NULL,
   `user_id` INT NULL,
   `author_name` VARCHAR(100) NULL,
   `author_email` VARCHAR(100) NULL,
   `content` TEXT NOT NULL,
   `status` ENUM('pending', 'approved', 'spam') NOT NULL DEFAULT 'pending',
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   KEY `post_id` (`post_id`),
   KEY `user_id` (`user_id`),
   KEY `status` (`status`),
   CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `topher_posts` (`id`) ON DELETE CASCADE,
   CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `comments` (`post_id`, `user_id`, `author_name`, `author_email`, `content`, `status`) VALUES
(1, NULL, 'John Doe', 'john@example.com', 'Great CMS! Very easy to use and intuitive.', 'approved'),
(1, NULL, 'Jane Smith', 'jane@example.com', 'Love the clean design and simple interface!', 'approved'),
(2, NULL, 'Mike Johnson', 'mike@example.com', 'Thanks for the helpful guide!', 'approved'),
(3, NULL, 'Sarah Williams', 'sarah@example.com', 'Bali is amazing! I visited last year.', 'pending');


ALTER TABLE users MODIFY username VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;