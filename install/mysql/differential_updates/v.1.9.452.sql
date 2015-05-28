/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `sessions`
	CHANGE `id` `id` varbinary(100)   NOT NULL first ,
	CHANGE `pid` `pid` varbinary(100)   NULL COMMENT 'parrent session id' after `id` ;

DELETE FROM `notifications`;

ALTER TABLE `notifications`
	DROP FOREIGN KEY `FK_notifications__action_id`  ,
	DROP FOREIGN KEY `FK_notifications_user_id`  ;


/* Alter table in target */
ALTER TABLE `notifications`
	ADD COLUMN `action_ids` mediumtext  COLLATE utf8_general_ci NULL COMMENT 'list of last action ids for same grouped action' after `action_id` ,
	ADD COLUMN `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','rename','reopen','status_change','overdue','comment','comment_update','move','password_change','permissions','user_delete','user_create','login','login_fail')  COLLATE utf8_general_ci NOT NULL after `action_ids` ,
	CHANGE `action_time` `action_time` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP  on update CURRENT_TIMESTAMP COMMENT 'think to remove it (doubles field from action_log)' after `action_type` ,
	ADD COLUMN `prev_action_ids` text  COLLATE utf8_general_ci NULL COMMENT 'previous action ids(for same obj, action type, user) that have not yet been read' after `action_time` ,
	CHANGE `user_id` `user_id` int(10) unsigned   NOT NULL after `prev_action_ids` ,
	CHANGE `email_sent` `email_sent` tinyint(1)   NOT NULL DEFAULT -1 COMMENT '-1 doesnt need to send, 0 - no, 1 - yes' after `user_id` ,
	CHANGE `read` `read` tinyint(1)   NOT NULL DEFAULT 0 COMMENT 'notification has been read in CB' after `email_sent` ,
	ADD KEY `IDX_notifications_email_sent`(`email_sent`) ,
	ADD UNIQUE KEY `UNQ_notifications`(`object_id`,`action_type`,`user_id`) ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__action_id`
	FOREIGN KEY (`action_id`) REFERENCES `action_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications_user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
