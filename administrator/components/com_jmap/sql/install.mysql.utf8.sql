-- Basic table from 1.0
CREATE TABLE IF NOT EXISTS `#__jmap` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`type` varchar(100) NOT NULL,
  	`name` text NOT NULL,
  	`description` text NOT NULL,
  	`checked_out` int unsigned NULL,
  	`checked_out_time` datetime NULL,
  	`published` tinyint NOT NULL DEFAULT '0',
  	`ordering` int NOT NULL DEFAULT '0',
  	`sqlquery` text NULL,
  	`sqlquery_managed` text NULL,
  	`params` text NULL,
  	PRIMARY KEY  (`id`),
  	KEY `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

INSERT INTO `#__jmap` (`id`, `type`, `name`, `description`, `published`, `ordering`, `sqlquery`, `sqlquery_managed`, `params`) VALUES (1, 'content', 'Content', 'Default contents source', 1, 1, '', '', '') ON DUPLICATE KEY UPDATE `id` = 1;

-- Updates on version 2.0
CREATE TABLE IF NOT EXISTS `#__jmap_pingomatic` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`title` varchar( 255 ) NOT NULL,
	`blogurl` varchar( 255 ) NOT NULL,
	`rssurl` varchar( 255 ) NULL,
	`services` text NOT NULL,
	`lastping` datetime NULL,
	`checked_out` int NULL,
	`checked_out_time` datetime NULL
) ENGINE=InnoDB CHARACTER SET `utf8` ;

-- Updates on version 2.1
CREATE TABLE IF NOT EXISTS `#__jmap_menu_priorities` (
	`id` int NOT NULL,
	`priority` char(3) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

-- Updates on version 3.0
CREATE TABLE IF NOT EXISTS `#__jmap_datasets` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
  	`name` text NOT NULL,
  	`description` text NOT NULL,
  	`checked_out` int NULL,
  	`checked_out_time` datetime NULL,
  	`published` tinyint NOT NULL DEFAULT '0',
  	`sources` text NOT NULL,
  	PRIMARY KEY  (`id`),
  	KEY `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

CREATE TABLE IF NOT EXISTS `#__jmap_dss_relations` (
	`datasetid` int NOT NULL,
	`datasourceid` int NOT NULL,
  PRIMARY KEY (`datasetid`, `datasourceid`)
) ENGINE=InnoDB CHARACTER SET `utf8` ;

CREATE TABLE IF NOT EXISTS `#__jmap_cats_priorities` (
	`id` int NOT NULL,
	`priority` char(3) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8` ; 

-- Updates on version 3.1
CREATE TABLE IF NOT EXISTS `#__jmap_google` (
	`id` int NOT NULL, 
	`token` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Updates on version 3.2
CREATE TABLE IF NOT EXISTS `#__jmap_metainfo` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`linkurl` varchar(600) NOT NULL,
	`meta_title` text NULL,
  	`meta_desc` text NULL,
  	`meta_image` varchar(255) NULL,
  	`robots` varchar(255) NULL,
  	`published` tinyint NOT NULL DEFAULT '0',
  	`excluded` tinyint NOT NULL DEFAULT '0',
  	PRIMARY KEY (`id`),
  	INDEX `robots` (`robots`),
  	INDEX `published` (`published`)
) ENGINE=InnoDB CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__jmap_canonicals` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`linkurl` varchar(600) NOT NULL,
	`canonical` varchar(600) NULL,
  	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__jmap_headings` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`linkurl` varchar(600) NOT NULL,
	`h1` text NULL,
  	`h2` text NULL,
  	`h3` text NULL,
  	PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__jmap_aigenerator` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`keywords_phrase` varchar( 255 ) NOT NULL,
	`contents` MEDIUMTEXT NULL,
	`api` varchar(50) NOT NULL DEFAULT 'bing',
	`maxresults` int NOT NULL DEFAULT '10',
	`language` char(7) NOT NULL DEFAULT '',
	`removeimgs` tinyint NOT NULL DEFAULT '0',
	`checked_out` int NULL,
	`checked_out_time` datetime NULL
) ENGINE=InnoDB CHARACTER SET `utf8`;

CREATE TABLE IF NOT EXISTS `#__jmap_text_replacements` (
	 `id` int unsigned NOT NULL AUTO_INCREMENT,
	 `original_text` VARCHAR( 1024 ) NOT NULL,
	 `target_text` VARCHAR( 1024 ) NOT NULL,
	 `original_text_regex` VARCHAR( 1024 ) NOT NULL,
	 `target_text_regex` VARCHAR( 1024 ) NOT NULL,
	 `checked_out` int unsigned NULL,
	 `checked_out_time` datetime NULL,
  	 `published` tinyint NOT NULL default '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8`;

-- Exceptions queries in reverse versioning order 10.0 -> 1.0