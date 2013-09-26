/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tasks`
	DROP FOREIGN KEY `FK_tasks__cid`  ,
	DROP FOREIGN KEY `FK_tasks__id`  ,
	DROP FOREIGN KEY `FK_tasks__object_id`  ,
	DROP FOREIGN KEY `FK_tasks__uid`  ;


/* Alter table in target */
ALTER TABLE `tasks`
	CHANGE `responsible_user_ids` `responsible_user_ids` VARCHAR(100)  COLLATE utf8_general_ci NOT NULL AFTER `privacy` ,
	CHANGE `autoclose` `autoclose` TINYINT(1)   NULL DEFAULT 1 AFTER `responsible_user_ids` ,
	CHANGE `description` `description` TEXT  COLLATE utf8_general_ci NULL COMMENT 'Task description' AFTER `autoclose` ,
	CHANGE `parent_ids` `parent_ids` VARCHAR(100)  COLLATE utf8_general_ci NULL COMMENT 'parent tasks' AFTER `description` ,
	CHANGE `child_ids` `child_ids` VARCHAR(100)  COLLATE utf8_general_ci NULL COMMENT 'child tasks. TO BE REVIEWED' AFTER `parent_ids` ,
	CHANGE `time` `time` CHAR(5)  COLLATE utf8_general_ci NULL AFTER `child_ids` ,
	CHANGE `reminds` `reminds` VARCHAR(250)  COLLATE utf8_general_ci NULL AFTER `time` ,
	CHANGE `status` `status` TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0 COMMENT '1 Overdue 2 Active 3 Completed 4 Pending' AFTER `reminds` ,
	CHANGE `missed` `missed` TINYINT(1) UNSIGNED   NULL AFTER `status` ,
	CHANGE `completed` `completed` TIMESTAMP   NULL COMMENT 'completed date (will be set automaticly, when all responsible users mark task as completed or the owner can close the task manually )' AFTER `missed` ,
	CHANGE `cid` `cid` INT(11) UNSIGNED   NOT NULL DEFAULT 1 AFTER `completed` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `cid` ,
	CHANGE `uid` `uid` INT(11) UNSIGNED   NULL DEFAULT 1 AFTER `cdate` ,
	CHANGE `udate` `udate` TIMESTAMP   NULL DEFAULT '0000-00-00 00:00:00' AFTER `uid` ,
	DROP COLUMN `responsible_party_id` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tasks`
	ADD CONSTRAINT `FK_tasks__cid`
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_tasks__id`
	FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_tasks__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_tasks__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;