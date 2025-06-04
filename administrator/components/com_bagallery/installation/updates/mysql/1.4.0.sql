ALTER TABLE `#__bagallery_galleries` ADD `enable_disqus` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__bagallery_galleries` ADD `disqus_subdomen` varchar(255) NOT NULL; 
ALTER TABLE `#__bagallery_items` ADD `lightboxUrl` varchar(255) NOT NULL;
