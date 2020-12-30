DROP TABLE IF EXISTS `section`;
CREATE TABLE `section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_id` tinyint unsigned NOT NULL DEFAULT '0',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `primary_display_idx` (`user_id`,`status`,`name`)
);

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_id` int NOT NULL DEFAULT '0',
  `task` varchar(255) NOT NULL DEFAULT '',
  `status` enum('Open','Closed','Deleted') NOT NULL DEFAULT 'Open',
  `created` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `priority` tinyint unsigned NOT NULL DEFAULT '1',
  `user_id` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `section_id` (`section_id`),
  KEY `status` (`status`),
  KEY `primary_display_idx` (`section_id`,`status`,`priority`,`task`),
  KEY `primary_count_idx` (`user_id`,`status`,`completed`)
);

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL DEFAULT '',
  `fullname` tinytext NOT NULL,
  `password` tinytext NOT NULL,
  `timezone` varchar(128) NOT NULL DEFAULT 'America/New_York',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
);
