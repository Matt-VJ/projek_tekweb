## SQL quick import (copy-paste)
See `add_public_categories_posts.sql` or copy below:

```sql
-- DROP existing tables first so this file can be imported directly in phpMyAdmin
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `categories`;

CREATE TABLE IF NOT EXISTS `categories` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL,
   `slug` VARCHAR(120) NOT NULL,
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `title` VARCHAR(255) NOT NULL,
   `excerpt` TEXT NULL,
   `content` TEXT NULL,
   `category_id` INT NULL,
   `image` VARCHAR(255) NULL,
   `published_at` DATETIME NULL,
   PRIMARY KEY (`id`),
   KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `posts`
   ADD CONSTRAINT `fk_posts_category_public` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Sample categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('News','news'),
('Tutorials','tutorials'),
('Announcements','announcements')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Sample posts
INSERT INTO `posts` (`title`, `excerpt`, `content`, `category_id`, `image`, `published_at`) VALUES
('Welcome to the mini CMS','A short intro to the site','Full content here',1,NULL,NOW()),
('How to use the editor','Short guide','Editor usage details',2,NULL,NOW()),
('Maintenance notice','Planned maintenance','We will be updating...',3,NULL,NOW());
```
