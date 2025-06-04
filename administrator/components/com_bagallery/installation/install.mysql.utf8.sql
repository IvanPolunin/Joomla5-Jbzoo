DROP TABLE IF EXISTS `#__bagallery_galleries`;
CREATE TABLE `#__bagallery_galleries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `bg_color` varchar(40) NOT NULL,
    `bg_color_hover` varchar(40) NOT NULL,
    `border_color` varchar(40) NOT NULL,
    `border_radius` varchar(10) NOT NULL,
    `font_color` varchar(40) NOT NULL,
    `font_color_hover` varchar(40) NOT NULL,
    `font_weight` varchar(8) NOT NULL,
    `font_size` varchar(10) NOT NULL,
    `alignment` varchar(8) NOT NULL,
    `gallery_layout` varchar(15) NOT NULL,
    `thumbnail_layout` varchar(8) NOT NULL,
    `column_number` varchar(8) NOT NULL,
    `image_width` varchar(8) NOT NULL,
    `image_spacing` varchar(8) NOT NULL,
    `pagination_type` varchar(15) NOT NULL,
    `images_per_page` varchar(5) NOT NULL,
    `pagination_bg` varchar(40) NOT NULL,
    `pagination_bg_hover` varchar(40) NOT NULL,
    `pagination_border` varchar(40) NOT NULL,
    `pagination_font` varchar(40) NOT NULL,
    `pagination_font_hover` varchar(40) NOT NULL,
    `pagination_radius` varchar(10) NOT NULL,
    `title_color` varchar(40) NOT NULL,
    `title_weight` varchar(10) NOT NULL,
    `title_size` varchar(10) NOT NULL,
    `title_alignment` varchar(10) NOT NULL,
    `category_color` varchar(40) NOT NULL,
    `category_weight` varchar(10) NOT NULL,
    `category_size` varchar(10) NOT NULL,
    `category_alignment` varchar(10) NOT NULL,
    `description_color` varchar(40) NOT NULL,
    `description_weight` varchar(10) NOT NULL,
    `description_size` varchar(10) NOT NULL,
    `description_alignment` varchar(10) NOT NULL,
    `caption_bg` varchar(10) NOT NULL,
    `caption_opacity` varchar(10) NOT NULL,
    `pagination_alignment` varchar(10) NOT NULL,
    `category_list` varchar(10) NOT NULL,
    `pagination` varchar(10) NOT NULL,
    `lazy_load` varchar(10) NOT NULL,
    `image_quality` varchar(10) NOT NULL,
    `display_title` varchar(10) NOT NULL,
    `display_categoty` varchar(10) NOT NULL,
    `lightbox_border` varchar(40) NOT NULL,
    `lightbox_bg` varchar(10) NOT NULL,
    `lightbox_bg_transparency` varchar(10) NOT NULL,
    `display_likes` varchar(10) NOT NULL,
    `watermark_upload` varchar(255) NOT NULL,
    `watermark_position` varchar(30) NOT NULL,
    `watermark_opacity` varchar(10) NOT NULL,
    `class_suffix`  varchar(255) NOT NULL,  
    `lightbox_display_title` varchar(10) NOT NULL,  
    `display_header` tinyint(1) NOT NULL,
    `lightbox_width` varchar(10) NOT NULL,
    `settings` MEDIUMTEXT NOT NULL,
    `gallery_items` LONGTEXT NOT NULL,
    `header_icons_color` varchar(40) NOT NULL,
    `nav_button_bg` varchar(40) NOT NULL,
    `nav_button_icon` varchar(40) NOT NULL,
    `auto_resize` tinyint(1) NOT NULL,
    `album_mode` tinyint(1) NOT NULL,
    `all_sorting` text NOT NULL,
    `enable_disqus` varchar(255) NOT NULL DEFAULT '0',
    `disqus_subdomen` varchar(255) NOT NULL,
    `sorting_mode` varchar(255) NOT NULL DEFAULT 'newest',
    `random_sorting` tinyint(1) NOT NULL DEFAULT 0,
    `disable_lightbox` tinyint(1) NOT NULL DEFAULT 0,
    `disable_caption` tinyint(1) NOT NULL DEFAULT 0,
    `scale_watermark` tinyint(1) NOT NULL DEFAULT 0,
    `vk_api_id` varchar(255) NOT NULL,
    `twitter_share` tinyint(1) NOT NULL DEFAULT 1,
    `facebook_share` tinyint(1) NOT NULL DEFAULT 1,
    `google_share` tinyint(1) NOT NULL DEFAULT 1,
    `pinterest_share` tinyint(1) NOT NULL DEFAULT 1,
    `linkedin_share` tinyint(1) NOT NULL DEFAULT 1,
    `vkontakte_share` tinyint(1) NOT NULL DEFAULT 1,
    `odnoklassniki_share` tinyint(1) NOT NULL DEFAULT 1,
    `display_download` tinyint(1) NOT NULL DEFAULT 1,
    `tablet_numb` varchar(8) NOT NULL DEFAULT '3',
    `phone_land_numb` varchar(8) NOT NULL DEFAULT '2',
    `phone_port_numb` varchar(8) NOT NULL DEFAULT '1',
    `display_zoom` tinyint(1) NOT NULL DEFAULT 1,
    `description_position` varchar(255)  NOT NULL DEFAULT 'below',
    `slide_amimation` varchar(255)  NOT NULL DEFAULT 'ba-gallery-fade-animation',
    `page_refresh` tinyint(1) NOT NULL DEFAULT 0,
    `disable_right_clk` tinyint(1) NOT NULL DEFAULT 0,
    `disable_shortcuts` tinyint(1) NOT NULL DEFAULT 0,
    `disable_dev_console` tinyint(1) NOT NULL DEFAULT 0,
    `load_jquery` tinyint(1) NOT NULL DEFAULT 1,
    `enable_alias` tinyint(1) NOT NULL DEFAULT 1,
    `display_fullscreen` tinyint(1) NOT NULL DEFAULT 1,
    `album_layout` varchar(15) NOT NULL DEFAULT 'justified',
    `album_width` varchar(8) NOT NULL DEFAULT '300',
    `album_quality` varchar(10) NOT NULL DEFAULT '40',
    `album_column_number` varchar(8) NOT NULL DEFAULT '4',
    `album_tablet_numb` varchar(8) NOT NULL DEFAULT '3',
    `album_phone_land_numb` varchar(8) NOT NULL DEFAULT '2',
    `album_phone_port_numb` varchar(8) NOT NULL DEFAULT '1',
    `album_image_spacing` varchar(8) NOT NULL DEFAULT '10',
    `album_disable_caption` tinyint(1) NOT NULL DEFAULT 0,
    `album_thumbnail_layout` varchar(8) NOT NULL DEFAULT '13',
    `album_caption_bg` varchar(255) NOT NULL,
    `album_display_title` varchar(10) NOT NULL,
    `album_display_img_count` varchar(10) NOT NULL,
    `album_title_color` varchar(40) NOT NULL,
    `album_title_weight` varchar(10) NOT NULL,
    `album_title_size` varchar(10) NOT NULL,
    `album_title_alignment` varchar(10) NOT NULL,
    `album_img_count_color` varchar(40) NOT NULL,
    `album_img_count_weight` varchar(10) NOT NULL,
    `album_img_count_size` varchar(10) NOT NULL,
    `album_img_count_alignment` varchar(10) NOT NULL,
    `enable_compression` tinyint(1) NOT NULL DEFAULT 0,
    `compression_width` varchar(10) NOT NULL DEFAULT '1920',
    `compression_quality` varchar(10) NOT NULL DEFAULT '80',
    `album_enable_lightbox` tinyint(1) NOT NULL DEFAULT 0,
    `disable_auto_scroll` tinyint(1) NOT NULL DEFAULT 0,
    `tags_bg_color` varchar(40) NOT NULL,
    `tags_bg_color_hover` varchar(40) NOT NULL,
    `tags_border_color` varchar(40) NOT NULL,
    `tags_border_radius` varchar(10) NOT NULL,
    `tags_font_color` varchar(40) NOT NULL,
    `tags_font_color_hover` varchar(40) NOT NULL,
    `tags_font_weight` varchar(8) NOT NULL,
    `tags_font_size` varchar(10) NOT NULL,
    `tags_alignment` varchar(8) NOT NULL,
    `enable_tags` varchar(10) NOT NULL DEFAULT '0',
    `tags_position` varchar(255) NOT NULL DEFAULT 'right',
    `colors_alignment` varchar(8) NOT NULL,
    `enable_colors` varchar(10) NOT NULL DEFAULT '0',
    `colors_position` varchar(255) NOT NULL DEFAULT 'right',
    `colors_border_radius` varchar(10) NOT NULL,
    `max_tags` int(11) NOT NULL,
    `max_colors` int(11) NOT NULL,
    `saved_time` varchar(255) NOT NULL DEFAULT '',
    `colors_method` varchar(255) NOT NULL DEFAULT '',
    `tags_method` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_category`;
CREATE TABLE `#__bagallery_category` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `parent` varchar(255) NOT NULL,
    `orders` int(11) NOT NULL,
    `access` int(11) NOT NULL DEFAULT 1,
    `password` varchar(255) NOT NULL DEFAULT '',

    `alias` varchar(255) NOT NULL DEFAULT '',
    `published` tinyint(1) NOT NULL DEFAULT 1,
    `default` tinyint(1) NOT NULL DEFAULT 1,
    `category_id` int(11) NOT NULL DEFAULT 0,
    `category_all` tinyint(1) NOT NULL DEFAULT 0,
    `description` mediumtext NOT NULL,
    `image` varchar(255) NOT NULL DEFAULT '',

    `settings` mediumtext NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_items`;
CREATE TABLE `#__bagallery_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `form_id` int(11) NOT NULL,
    `category` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `path` varchar(255) NOT NULL,
    `url` varchar(255) NOT NULL,
    `thumbnail_url` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `short` varchar(255) NOT NULL,
    `alt` varchar(255) NOT NULL,
    `description` MEDIUMTEXT NOT NULL,
    `link` varchar(255) NOT NULL,
    `video` MEDIUMTEXT NOT NULL,
    `settings` MEDIUMTEXT NOT NULL,
    `likes` int(11) NOT NULL DEFAULT 0,
    `imageId` varchar(10) NOT NULL,
    `target` varchar(10) NOT NULL,
    `lightboxUrl` varchar(255) NOT NULL,
    `watermark_name` varchar(255) NOT NULL,
    `hideInAll` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_users`;
CREATE TABLE `#__bagallery_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `image_id` int(11) NOT NULL,
    `ip` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__bagallery_api`;
CREATE TABLE `#__bagallery_api` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `service` varchar(255) NOT NULL,
    `key` varchar(255) NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

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

INSERT INTO `#__bagallery_api` (`service`, `key`) VALUES
('product_tour', 'false');