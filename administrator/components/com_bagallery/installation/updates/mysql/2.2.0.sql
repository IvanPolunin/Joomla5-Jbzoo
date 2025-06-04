ALTER TABLE `#__bagallery_galleries` DROP `bg_active`;
ALTER TABLE `#__bagallery_galleries` DROP `bg_hover_active`;
ALTER TABLE `#__bagallery_galleries` DROP `border_color_active`;
ALTER TABLE `#__bagallery_galleries` DROP `font_color_active`;
ALTER TABLE `#__bagallery_galleries` DROP `font_color_hover_active`;
ALTER TABLE `#__bagallery_galleries` ADD `tags_bg_color` varchar(40) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_bg_color_hover` varchar(40) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_border_color` varchar(40) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_border_radius` varchar(10) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_font_color` varchar(40) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_font_color_hover` varchar(40) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_font_weight` varchar(8) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_font_size` varchar(10) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `tags_alignment` varchar(8) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `enable_tags` varchar(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__bagallery_galleries` ADD `tags_position` varchar(255) NOT NULL DEFAULT 'right';
ALTER TABLE `#__bagallery_galleries` ADD `colors_alignment` varchar(8) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `enable_colors` varchar(10) NOT NULL DEFAULT '0';
ALTER TABLE `#__bagallery_galleries` ADD `colors_position` varchar(255) NOT NULL DEFAULT 'right';
ALTER TABLE `#__bagallery_galleries` ADD `colors_border_radius` varchar(10) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `max_tags` int(11) NOT NULL DEFAULT 10;
ALTER TABLE `#__bagallery_galleries` ADD `max_colors` int(11) NOT NULL DEFAULT 10;

DROP TABLE IF EXISTS `#__bagallery_tags`;
CREATE TABLE `#__bagallery_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `hits` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_tags_map`;
CREATE TABLE `#__bagallery_tags_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `tag_id` int(11) NOT NULL,
    `image_id` int(11) NOT NULL,
    `gallery_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_colors`;
CREATE TABLE `#__bagallery_colors` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `alias` varchar(255) NOT NULL,
    `hits` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_colors_map`;
CREATE TABLE `#__bagallery_colors_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `color_id` int(11) NOT NULL,
    `image_id` int(11) NOT NULL,
    `gallery_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;