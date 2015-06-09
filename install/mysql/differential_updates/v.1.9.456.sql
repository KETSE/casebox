/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `notifications`
	DROP KEY `UNQ_notifications`, ADD UNIQUE KEY `UNQ_notifications`(`object_id`,`action_type`,`from_user_id`,`user_id`) ;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;