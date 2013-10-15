/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tasks`
	DROP FOREIGN KEY `FK_tasks__cid`  ,
	DROP FOREIGN KEY `FK_tasks__id`  ,
	DROP FOREIGN KEY `FK_tasks__object_id`  ,
	DROP FOREIGN KEY `FK_tasks__uid`  ;


/* Alter table in target */
ALTER TABLE `tasks`
	CHANGE `allday` `allday` tinyint(1)   NOT NULL DEFAULT 1 after `date_end` ,
	CHANGE `importance` `importance` tinyint(3) unsigned   NULL after `allday` ,
	CHANGE `category_id` `category_id` bigint(20) unsigned   NULL after `importance` ,
	CHANGE `type` `type` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'for tasks: 0-internal, 1-external. For events: 2' after `category_id` ,
	CHANGE `privacy` `privacy` tinyint(1) unsigned   NOT NULL DEFAULT 0 COMMENT '0-public, 1-private' after `type` ,
	CHANGE `responsible_user_ids` `responsible_user_ids` varchar(100)  COLLATE utf8_general_ci NOT NULL after `privacy` ,
	CHANGE `autoclose` `autoclose` tinyint(1)   NULL DEFAULT 1 after `responsible_user_ids` ,
	CHANGE `description` `description` text  COLLATE utf8_general_ci NULL COMMENT 'Task description' after `autoclose` ,
	CHANGE `parent_ids` `parent_ids` varchar(100)  COLLATE utf8_general_ci NULL COMMENT 'parent tasks' after `description` ,
	CHANGE `child_ids` `child_ids` varchar(100)  COLLATE utf8_general_ci NULL COMMENT 'child tasks. TO BE REVIEWED' after `parent_ids` ,
	CHANGE `time` `time` char(5)  COLLATE utf8_general_ci NULL after `child_ids` ,
	CHANGE `reminds` `reminds` varchar(250)  COLLATE utf8_general_ci NULL after `time` ,
	CHANGE `status` `status` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT '1 Overdue 2 Active 3 Completed 4 Pending' after `reminds` ,
	CHANGE `missed` `missed` tinyint(1) unsigned   NULL after `status` ,
	CHANGE `completed` `completed` timestamp   NULL COMMENT 'completed date (will be set automaticly, when all responsible users mark task as completed or the owner can close the task manually )' after `missed` ,
	CHANGE `cid` `cid` int(11) unsigned   NOT NULL DEFAULT 1 after `completed` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL DEFAULT 1 after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL DEFAULT '0000-00-00 00:00:00' after `uid` ,
	DROP COLUMN `has_deadline` ,
	ADD KEY `idx_status__date_end`(`status`,`date_end`) ,
	DROP KEY `idx_type__status__has_deadline` ;

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