/*
 * Filename: f:\Projekte\yootheme-supermailer\sql\mysql\install.mysql.utf8.sql
 * Path: f:\Projekte\yootheme-supermailer\sql\mysql
 * Created Date: Tuesday, July 30th 2024, 9:19:10 pm
 * Author: Necati Meral https://meral.cloud
 * 
 * Copyright (c) 2024 Meral IT
 */
CREATE TABLE IF NOT EXISTS `#__supermailer` (
	`id` char(36) NOT NULL,
	`email` varchar(256) NOT NULL,
	`recipient` varchar(256) NOT NULL,
	`expiration` timestamp NULL,
	`registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`confirmation` timestamp NULL,
	`payload` varchar(500) NOT NULL,
	`state` tinyint(4) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE = INNODB DEFAULT CHARSET = utf8;