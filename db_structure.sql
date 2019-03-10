CREATE TABLE IF NOT EXISTS `App` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(8) unsigned NOT NULL,
  `name` varchar(32) NOT NULL,
  `type` enum('WEB','NATIVE') NOT NULL DEFAULT 'WEB',
  `secret` varchar(64) NOT NULL,
  `callback` varchar(64) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_id` (`app_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `LocalAuth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) NOT NULL DEFAULT '0',
  `username` varchar(64) NOT NULL,
  `password` varchar(128) DEFAULT '0',
  `locked` enum('Y','N') DEFAULT 'N',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7979 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `UserInfo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) NOT NULL DEFAULT '0',
  `name` varchar(64) DEFAULT '0',
  `cell` varchar(32) DEFAULT '0',
  `email` varchar(128) DEFAULT '0',
  `qq` varchar(16) DEFAULT '0',
  `reg_time` int(11) unsigned DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`user_id`),
  KEY `cell` (`cell`)
) ENGINE=InnoDB AUTO_INCREMENT=7979 DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `VIP` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 for male, 1 for female, allow others',
  `cell` varchar(32) NOT NULL,
  `qq` varchar(32) DEFAULT NULL,
  `department` varchar(64) DEFAULT NULL,
  `grade` varchar(16) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cell` (`cell`)
) ENGINE=InnoDB AUTO_INCREMENT=4551 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `Permission` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `perm_id` int(8) unsigned NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `extend` varchar(64) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perm_id` (`perm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `Role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(8) unsigned NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `Role_Perm` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(8) unsigned NOT NULL,
  `perm_id` int(8) unsigned NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `role_id_perm_id` (`role_id`,`perm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `User_Role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) NOT NULL,
  `role_id` int(8) unsigned NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '1',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id_role_id` (`user_id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `OTP` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `number` varchar(64) NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`number`)
) ENGINE=InnoDB AUTO_INCREMENT=3565 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `Session` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=3625 DEFAULT CHARSET=utf8mb4;
