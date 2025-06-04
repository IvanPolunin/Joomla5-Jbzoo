INSERT INTO `#__baforms_api` (`service`, `key`) VALUES
('turnstile', '{"site_key":"","secret_key":"","theme":"auto","size":"normal"}');

ALTER TABLE `#__baforms_submissions` ADD `form_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__baforms_submissions` ADD `user_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `#__baforms_submissions` ADD `user_ip` varchar(255) NOT NULL DEFAULT '';