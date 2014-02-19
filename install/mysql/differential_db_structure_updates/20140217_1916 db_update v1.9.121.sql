/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `notifications`
	DROP FOREIGN KEY `FK_notifications__file_id`  ,
	DROP FOREIGN KEY `FK_notifications__object_id`  ,
	DROP FOREIGN KEY `FK_notifications__task_id`  ,
	DROP FOREIGN KEY `FK_notifications__user_id`  ;


/* Alter table in target */
ALTER TABLE `notifications`
	ADD COLUMN `sender` varchar(500)  COLLATE utf8_general_ci NULL after `file_id` ,
	CHANGE `subject` `subject` varchar(500)  COLLATE utf8_general_ci NULL after `sender` ,
	CHANGE `message` `message` text  COLLATE utf8_general_ci NOT NULL after `subject` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__file_id`
	FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications__task_id`
	FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;