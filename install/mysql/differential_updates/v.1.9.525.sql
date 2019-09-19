/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `notifications`
	DROP FOREIGN KEY `FK_notifications__action_id`  ,
	DROP FOREIGN KEY `FK_notifications_user_id`  ;


/* Alter table in target */
ALTER TABLE `notifications`
	ADD COLUMN `seen` tinyint(1)   NOT NULL DEFAULT 0 after `user_id` ,
	CHANGE `read` `read` tinyint(1)   NOT NULL DEFAULT 0 COMMENT 'notification has been read in CB' after `seen` ,
	DROP COLUMN `email_sent` ,
	DROP KEY `IDX_notifications_email_sent`, ADD KEY `IDX_notifications_seen`(`seen`) ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__action_id`
	FOREIGN KEY (`action_id`) REFERENCES `action_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications_user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;


UPDATE `notifications` SET seen = 1;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;