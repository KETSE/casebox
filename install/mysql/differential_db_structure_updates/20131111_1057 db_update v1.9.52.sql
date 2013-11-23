/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ;


/* Alter table in target */
ALTER TABLE `objects`
	ADD COLUMN `data` text  COLLATE utf8_general_ci NULL after `private_for_user` ,
	CHANGE `cid` `cid` int(11) unsigned   NULL after `data` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL after `uid` ;

/* Alter table in target */
ALTER TABLE `users_groups`
	ADD COLUMN `data` text  COLLATE utf8_general_ci NULL after `cfg` ,
	CHANGE `last_login` `last_login` timestamp   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `data` ,
	CHANGE `login_successful` `login_successful` tinyint(1)   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `last_login` ,
	CHANGE `login_from_ip` `login_from_ip` varchar(40)  COLLATE utf8_general_ci NULL COMMENT 'should be moved to an auth log table for security enhancement' after `login_successful` ,
	CHANGE `last_logout` `last_logout` timestamp   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `login_from_ip` ,
	CHANGE `last_action_time` `last_action_time` timestamp   NULL after `last_logout` ,
	CHANGE `enabled` `enabled` tinyint(1) unsigned   NULL DEFAULT 1 after `last_action_time` ,
	CHANGE `cid` `cid` int(11) unsigned   NULL COMMENT 'creator id' after `enabled` ,
	CHANGE `cdate` `cdate` timestamp   NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation timestamp' after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'updated timestamp' after `uid` ,
	CHANGE `did` `did` int(11) unsigned   NULL COMMENT 'deleter id' after `udate` ,
	CHANGE `ddate` `ddate` timestamp   NULL after `did` ,
	CHANGE `searchField` `searchField` text  COLLATE utf8_general_ci NULL COMMENT 'helper field for users quick searching' after `ddate` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `objects`
	ADD CONSTRAINT `FK_objects__cid`
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__private_for_user`
	FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;