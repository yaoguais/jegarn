-- set names utf8;
-- CREATE DATABASE `minions` DEFAULT CHARSET utf8mb4;
-- use `minions`;
CREATE TABLE m_user(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`username` varchar(32) NOT NULL,
	`password` varchar(128) NULL DEFAULT NULL,
	`create_time` int(10) NOT NULL,
	`nick` varchar(64) NULL DEFAULT NULL,
	`motto` varchar(64) NULL DEFAULT NULL,
	`avatar` varchar(255) NULL DEFAULT NULL,
	`token` varchar(128) NOT NULL,
	`reg_ip` varchar(24) NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY(`username`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE m_login_log(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`ip` varchar(24) NULL DEFAULT NULL,
	`status` tinyint(1) NOT NULL DEFAULT 0,/* 0:FAILED 1:SUCCESS */
	PRIMARY KEY(`id`),
	KEY `m_login_log_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE m_roster_group(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`name` varchar(64) NULL DEFAULT NULL,
	`rank` smallint(4) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	KEY `m_group_roster_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE m_roster(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`target_id` bigint(20) NOT NULL,
	`status` tinyint(1) NOT NULL,
	`create_time` int(10) NOT NULL,
	`update_time` int(10) NULL DEFAULT NULL,
	`remark` varchar(64) NULL DEFAULT NULL,
	`group_id` bigint(20) NOT NULL DEFAULT 0,
	`rank` smallint(4) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	KEY `m_roster_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE m_group(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`type` tinyint(1) NOT NULL,
	`name` varchar(64) NULL DEFAULT NULL,
	`create_time` int(10) NOT NULL,
	`description` varchar(256) NULL DEFAULT NULL,
	`icon` varchar(255) NULL DEFAULT NULL,
	PRIMARY KEY(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE m_group_user(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`gid` bigint(20) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`permission` tinyint(1) NOT NULL DEFAULT 0,
	`remark` varchar(64) NULL DEFAULT NULL,
	`create_time` int(10) NOT NULL,
	`status` tinyint(1) NOT NULL,
	PRIMARY KEY(`id`),
	KEY `m_group_user_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
