/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `menu`
	CHANGE `node_ids` `node_ids` varchar(1000)  COLLATE latin1_swedish_ci NULL after `id` ,
	CHANGE `node_template_ids` `node_template_ids` varchar(1000)  COLLATE latin1_swedish_ci NULL after `node_ids` ,
	CHANGE `user_group_ids` `user_group_ids` varchar(1000)  COLLATE latin1_swedish_ci NULL after `menu` ;

/* Create table in target */
CREATE TABLE `tree_user_config`(
	`id` bigint(20) unsigned NOT NULL  ,
	`user_id` int(10) unsigned NOT NULL  ,
	`cfg` text COLLATE utf8_general_ci NULL  ,
	PRIMARY KEY (`id`,`user_id`) ,
	KEY `tree_user_config__user_id`(`user_id`) ,
	CONSTRAINT `tree_user_config__id`
	FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	CONSTRAINT `tree_user_config__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;