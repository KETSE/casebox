/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tree_user_config`
	DROP FOREIGN KEY `tree_user_config__user_id`  ;


/* Alter table in target */
ALTER TABLE `tree_user_config`
	ADD COLUMN `guid` varchar(50)  COLLATE utf8_general_ci NOT NULL COMMENT 'id of the tree node or vitual node' first ,
	CHANGE `user_id` `user_id` int(10) unsigned   NOT NULL after `guid` ,
	DROP COLUMN `id` ,
	DROP KEY `PRIMARY`, ADD PRIMARY KEY(`guid`,`user_id`) ,
	DROP FOREIGN KEY `tree_user_config__id`  ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tree_user_config`
	ADD CONSTRAINT `tree_user_config__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;