set names utf8;
CREATE DATABASE `minions` DEFAULT CHARSET UTF8;
use `minions`;
CREATE TABLE m_user(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`username` varchar(32) NOT NULL,
	`password` varchar(64) NULL DEFAULT NULL,
	`create_time` int(10) NOT NULL,
	`nick` varchar(64) NULL DEFAULT NULL,
	`avatar` varchar(255) NULL DEFAULT NULL,
	`token` varchar(128) NOT NULL,
	`reg_ip` varchar(24) NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	UNIQUE KEY(`username`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_login_log(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`ip` varchar(24) NULL DEFAULT NULL,
	`status` tinyint(1) NOT NULL DEFAULT 0,/* 0:FAILED 1:SUCCESS */
	PRIMARY KEY(`id`),
	KEY `m_login_log_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_group_roster(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`name` varchar(64) NULL DEFAULT NULL,
	`rank` smallint(4) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	KEY `m_group_roster_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_roster(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`friend_id` bigint(20) NOT NULL,
	`status` tinyint(1) NOT NULL,
	`create_time` int(10) NOT NULL,
	`update_time` int(10) NULL DEFAULT NULL,
	`remark` varchar(64) NULL DEFAULT NULL,
	`group_id` bigint(20) NULL DEFAULT NULL,
	`rank` smallint(4) NOT NULL DEFAULT 0,
	PRIMARY KEY(`id`),
	KEY `m_roster_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_group(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`type` tinyint(1) NOT NULL,
	`name` varchar(64) NULL DEFAULT NULL,
	`description` varchar(256) NULL DEFAULT NULL,
	PRIMARY KEY(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_group_user(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`gid` bigint(20) NOT NULL,
	`uid` bigint(20) NOT NULL,
	`admin` tinyint(1) NOT NULL DEFAULT 0,
	`remark` varchar(64) NULL DEFAULT NULL,
	PRIMARY KEY(`id`),
	KEY `m_group_user_uid`(`uid`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_message(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`to` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`message` TEXT NOT NULL,
	PRIMARY KEY(`id`),
	KEY `m_message_uid`(`uid`),
	KEY `m_message_to`(`to`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_offline_message(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`to` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`message` TEXT NOT NULL,
	PRIMARY KEY(`id`),
	KEY `m_offline_message_uid`(`uid`),
	KEY `m_offline_message_to`(`to`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_group_message(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`to` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`message` TEXT NOT NULL,
	PRIMARY KEY(`id`),
	KEY `m_group_message_uid`(`uid`),
	KEY `m_group_message_to`(`to`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_offline_group_message(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) NOT NULL,
	`to` bigint(20) NOT NULL,
	`create_time` int(10) NOT NULL,
	`message` TEXT NOT NULL,
	PRIMARY KEY(`id`),
	KEY `m_offline_group_message_uid`(`uid`),
	KEY `m_offline_group_message_to`(`to`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_property(
	`name` varchar(32) NOT NULL,
	`value` varchar(256) NULL DEFAULT NULL,
	PRIMARY KEY(`name`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE m_recycle(
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`data` text NOT NULL,
	PRIMARY KEY(`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

