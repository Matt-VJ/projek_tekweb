-- DROP existing tables first so this file can be imported directly in phpMyAdmin
DROP TABLE IF EXISTS `topher_posts`;
DROP TABLE IF EXISTS `topher_categories`;

CREATE TABLE IF NOT EXISTS `topher_categories` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(100) NOT NULL,
   `slug` VARCHAR(120) NOT NULL,
   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `topher_posts` (
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

ALTER TABLE `topher_posts`
   ADD CONSTRAINT `fk_topher_posts_category_topher` FOREIGN KEY (`category_id`) REFERENCES `topher_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Sample categories
INSERT INTO `topher_categories` (`name`, `slug`) VALUES
('News','news'),
('Tutorials','tutorials'),
('Announcements','announcements')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Sample posts
INSERT INTO `topher_posts` (`title`, `excerpt`, `content`, `category_id`, `image`, `published_at`) VALUES
('Welcome to the mini CMS','A short intro to the site','Full content here',1,NULL,NOW()),
('How to use the editor','Short guide','Editor usage details',2,NULL,NOW()),
('Maintenance notice','Planned maintenance','We will be updating...',3,NULL,NOW());
```



