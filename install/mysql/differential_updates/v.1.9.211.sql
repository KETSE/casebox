/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Create table in target */
CREATE TABLE `action_log`(
	`id` bigint(20) unsigned NOT NULL  auto_increment ,
	`object_id` bigint(20) unsigned NOT NULL  ,
	`object_pid` bigint(20) unsigned NULL  ,
	`user_id` int(10) unsigned NOT NULL  ,
	`action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','reopen','status_change','overdue','comment','move','permissions','user_delete','user_create','login','login_fail') COLLATE utf8_general_ci NOT NULL  ,
	`action_time` timestamp NOT NULL  DEFAULT CURRENT_TIMESTAMP ,
	`data` mediumtext COLLATE utf8_general_ci NULL  ,
	PRIMARY KEY (`id`) ,
	KEY `FK_action_log__object_id`(`object_id`) ,
	KEY `FK_action_log__object_pid`(`object_pid`) ,
	KEY `FK_action_log__user_id`(`user_id`) ,
	CONSTRAINT `FK_action_log__object_pid`
	FOREIGN KEY (`object_pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	CONSTRAINT `FK_action_log__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	CONSTRAINT `FK_action_log__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';

TRUNCATE TABLE `notifications`;

/* Alter table in target */
ALTER TABLE `notifications`
	CHANGE `id` `id` bigint(11) unsigned   NOT NULL auto_increment first ,
	CHANGE `action_type` `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','reopen','status_change','overdue','comment','move','permissions','user_delete','user_create','login','login_fail') COLLATE utf8_general_ci NOT NULL after `id` ,
	ADD COLUMN `action_time` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `action_type` ,
	CHANGE `object_id` `object_id` bigint(20) unsigned   NOT NULL after `action_time` ,
	ADD COLUMN `object_pid` bigint(20) unsigned   NULL after `object_id` ,
	CHANGE `user_id` `user_id` int(11) unsigned   NOT NULL after `object_pid` ,
	ADD COLUMN `data` mediumtext  COLLATE utf8_general_ci NOT NULL after `user_id` ,
	DROP COLUMN `time` ,
	DROP COLUMN `message` ,
	DROP COLUMN `case_id` ,
	DROP COLUMN `task_id` ,
	DROP COLUMN `subtype` ,
	DROP COLUMN `file_id` ,
	DROP COLUMN `sender` ,
	DROP COLUMN `subject` ,
	DROP KEY `FK_notifications__case_id`, ADD KEY `FK_notifications__case_id`(`object_pid`) ,
	DROP KEY `FK_notifications__file_id` ,
	DROP KEY `FK_notifications__task_id` ,
	ADD UNIQUE KEY `UNQ_notifications__action_type__object_id__user_id`(`action_type`,`object_id`,`user_id`) ,
	DROP FOREIGN KEY `FK_notifications__file_id`  ,
	DROP FOREIGN KEY `FK_notifications__object_id`  ,
	DROP FOREIGN KEY `FK_notifications__task_id`  ,
	DROP FOREIGN KEY `FK_notifications__user_id`  ;
ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;


/* Create table in target */
CREATE TABLE `user_subscriptions`(
	`id` bigint(20) unsigned NOT NULL  auto_increment ,
	`user_id` int(10) unsigned NOT NULL  ,
	`object_id` bigint(20) unsigned NOT NULL  ,
	`recursive` tinyint(1) NOT NULL  DEFAULT 0 ,
	`sdate` timestamp NOT NULL  DEFAULT CURRENT_TIMESTAMP COMMENT 'subscription timestamp' ,
	PRIMARY KEY (`id`) ,
	KEY `FK_user_subscriptions__object_id`(`object_id`) ,
	KEY `FK_user_subscriptions__user_id`(`user_id`) ,
	CONSTRAINT `FK_user_subscriptions__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	CONSTRAINT `FK_user_subscriptions__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
