ALTER TABLE `#__bagallery_galleries` ADD `scale_watermark` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_category` ADD `parent` varchar(255) NOT NULL;
ALTER TABLE `#__bagallery_items` ADD `watermark_name` varchar(255) NOT NULL;
ALTER TABLE `#__bagallery_items` ADD `hideInAll` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_galleries` ADD `vk_api_id` varchar(255) NOT NULL;
ALTER TABLE `#__bagallery_galleries` CHANGE `enable_disqus` `enable_disqus` VARCHAR(255) NOT NULL DEFAULT '0';
ALTER TABLE `#__bagallery_galleries` ADD `twitter_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `facebook_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `google_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `pinterest_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `linkedin_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `vkontakte_share` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `display_download` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `tablet_numb` varchar(8) NOT NULL DEFAULT '3';
ALTER TABLE `#__bagallery_galleries` ADD `phone_land_numb` varchar(8) NOT NULL DEFAULT '2';
ALTER TABLE `#__bagallery_galleries` ADD `phone_port_numb` varchar(8) NOT NULL DEFAULT '1';
ALTER TABLE `#__bagallery_galleries` DROP `close_icon_color`;
ALTER TABLE `#__bagallery_galleries` DROP `header_on_hover`;
ALTER TABLE `#__bagallery_galleries` DROP `header_bg`;
ALTER TABLE `#__bagallery_galleries` DROP `header_opacity`;
ALTER TABLE `#__bagallery_galleries` DROP `title_font_size`;
ALTER TABLE `#__bagallery_galleries` DROP `title_font_color`;
ALTER TABLE `#__bagallery_galleries` DROP `display_shara`;
ALTER TABLE `#__bagallery_galleries` DROP `lightbox_effects`;
ALTER TABLE `#__bagallery_galleries` ADD `display_zoom` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` DROP `font_weight_active`;
ALTER TABLE `#__bagallery_galleries` ADD `description_position` varchar(255)  NOT NULL DEFAULT 'below';
ALTER TABLE `#__bagallery_category` ADD `orders` tinyint(1) NOT NULL;
ALTER TABLE `#__bagallery_galleries` ADD `page_refresh` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_galleries` ADD `disable_right_clk` tinyint(1) NOT NULL DEFAULT 1;
ALTER TABLE `#__bagallery_galleries` ADD `disable_shortcuts` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_galleries` ADD `disable_dev_console` tinyint(1) NOT NULL DEFAULT 0;
CREATE TABLE `#__bagallery_api` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service` varchar(255) NOT NULL,
    `key` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
INSERT INTO `#__bagallery_api` (`service`, `key`) VALUES
('product_tour', 'false');