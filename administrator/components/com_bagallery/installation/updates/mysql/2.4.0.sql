ALTER TABLE `#__bagallery_category` ADD `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bagallery_category` ADD `published` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_category` ADD `default` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_category` ADD `category_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_category` ADD `category_all` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_category` ADD `description` mediumtext NOT NULL;
ALTER TABLE `#__bagallery_category` ADD `image` varchar(255) NOT NULL DEFAULT '';