ALTER TABLE `#__bagallery_category` CHANGE orders orders INT(11) NOT NULL;
ALTER TABLE `#__bagallery_category` ADD `access` int(11) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `enable_alias` tinyint(1) NOT NULL DEFAULT 1;