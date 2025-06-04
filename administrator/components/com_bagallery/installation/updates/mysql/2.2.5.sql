ALTER TABLE `#__bagallery_category` ADD `password` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bagallery_galleries` ADD `odnoklassniki_share` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_galleries` ADD `saved_time` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bagallery_galleries` ADD `colors_method` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__bagallery_galleries` ADD `tags_method` varchar(255) NOT NULL DEFAULT '';