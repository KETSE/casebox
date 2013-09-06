/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `actions_log`
	DROP FOREIGN KEY `FK_actions_log__user_id`  ;

ALTER TABLE `messages`
	DROP FOREIGN KEY `messages__to_user_id`  ;

ALTER TABLE `tasks`
	DROP FOREIGN KEY `FK_tasks__cid`  ,
	DROP FOREIGN KEY `FK_tasks__id`  ,
	DROP FOREIGN KEY `FK_tasks__object_id`  ,
	DROP FOREIGN KEY `FK_tasks__uid`  ;


/* Alter table in target */
ALTER TABLE `actions_log`
	CHANGE `case_id` `case_id` bigint(20) unsigned   NULL after `to_user_ids` ,
	CHANGE `object_id` `object_id` bigint(20) unsigned   NULL after `case_id` ,
	CHANGE `file_id` `file_id` bigint(20) unsigned   NULL after `object_id` ,
	CHANGE `task_id` `task_id` bigint(20) unsigned   NULL after `file_id` ,
	CHANGE `date` `date` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP after `task_id` ,
	CHANGE `action_type` `action_type` smallint(6) unsigned   NOT NULL COMMENT '1. Add case\n 2. open case\n 3. close case\n 4. add case object\n 5. update case object\n 6. delete case object\n 7. open case object\n 8. close case object\n 9. add case file\n 10. download case file\n 11. delete case file' after `date` ,
	CHANGE `remind_users` `remind_users` varchar(100)  COLLATE utf8_general_ci NULL after `action_type` ,
	CHANGE `result` `result` varchar(50)  COLLATE utf8_general_ci NULL after `remind_users` ,
	CHANGE `info` `info` text  COLLATE utf8_general_ci NULL after `result` ,
	CHANGE `l1` `l1` text  COLLATE utf8_general_ci NULL after `info` ,
	CHANGE `l2` `l2` text  COLLATE utf8_general_ci NULL after `l1` ,
	CHANGE `l4` `l4` text  COLLATE utf8_general_ci NULL after `l2` ,
	CHANGE `l3` `l3` text  COLLATE utf8_general_ci NULL after `l4` ,
	DROP COLUMN `office_id` ;

/* Alter table in target */
ALTER TABLE `messages`
	ADD COLUMN `node_id` bigint(20) unsigned   NULL after `to_user_id` ,
	ADD COLUMN `type` enum('','task_complete')  COLLATE utf8_general_ci NOT NULL COMMENT 'message type' after `node_id` ,
	CHANGE `subject` `subject` varchar(500)  COLLATE utf8_general_ci NULL after `type` ,
	CHANGE `message` `message` text  COLLATE utf8_general_ci NULL after `subject` ,
	CHANGE `cid` `cid` int(10) unsigned   NOT NULL after `message` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `cid` ,
	DROP COLUMN `nid` ,
	DROP KEY `messages__nid` ,
	ADD KEY `messages__node_id`(`node_id`) ,
	DROP FOREIGN KEY `messages__nid`  ;
ALTER TABLE `messages`
	ADD CONSTRAINT `messages__node_id`
	FOREIGN KEY (`node_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;


/* Alter table in target */
ALTER TABLE `tasks`
	CHANGE `object_id` `object_id` bigint(20) unsigned   NULL after `id` ,
	CHANGE `title` `title` varchar(250)  COLLATE utf8_general_ci NOT NULL after `object_id` ,
	CHANGE `date_start` `date_start` datetime   NULL COMMENT 'used for events' after `title` ,
	CHANGE `date_end` `date_end` datetime   NULL after `date_start` ,
	CHANGE `has_deadline` `has_deadline` tinyint(1)   NOT NULL DEFAULT 0 after `date_end` ,
	CHANGE `allday` `allday` tinyint(1)   NOT NULL DEFAULT 1 after `has_deadline` ,
	CHANGE `importance` `importance` tinyint(3) unsigned   NULL after `allday` ,
	CHANGE `category_id` `category_id` bigint(20) unsigned   NULL after `importance` ,
	CHANGE `type` `type` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'for tasks: 0-internal, 1-external. For events: 2' after `category_id` ,
	CHANGE `privacy` `privacy` tinyint(1) unsigned   NOT NULL DEFAULT 0 COMMENT '0-public, 1-private' after `type` ,
	CHANGE `responsible_party_id` `responsible_party_id` int(11) unsigned   NULL after `privacy` ,
	CHANGE `responsible_user_ids` `responsible_user_ids` varchar(100)  COLLATE utf8_general_ci NOT NULL after `responsible_party_id` ,
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
	DROP COLUMN `case_id` ,
	DROP COLUMN `updated` ,
	DROP KEY `FK_tasks__case_id` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `actions_log`
	ADD CONSTRAINT `FK_actions_log__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

ALTER TABLE `messages`
	ADD CONSTRAINT `messages__to_user_id`
	FOREIGN KEY (`to_user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

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