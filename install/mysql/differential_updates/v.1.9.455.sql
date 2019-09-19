/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `notifications`
	DROP FOREIGN KEY `FK_notifications__action_id`  ,
	DROP FOREIGN KEY `FK_notifications_user_id`  ;


/* Alter table in target */
ALTER TABLE `notifications`
	ADD COLUMN `from_user_id` int(11)   NULL after `prev_action_ids` ,
	CHANGE `user_id` `user_id` int(10) unsigned   NOT NULL after `from_user_id` ,
	CHANGE `email_sent` `email_sent` tinyint(1)   NOT NULL DEFAULT -1 COMMENT '-1 doesnt need to send, 0 - no, 1 - yes' after `user_id` ,
	CHANGE `read` `read` tinyint(1)   NOT NULL DEFAULT 0 COMMENT 'notification has been read in CB' after `email_sent` ,
	DROP KEY `UNQ_notifications`, ADD UNIQUE KEY `UNQ_notifications`(`object_id`,`action_type`,`from_user_id`) ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__action_id`
	FOREIGN KEY (`action_id`) REFERENCES `action_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications_user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;