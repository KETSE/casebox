/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

DELETE FROM favorites;

/* Alter table in target */
ALTER TABLE `favorites`
	ADD COLUMN `id` bigint(20) unsigned   NOT NULL auto_increment first ,
	CHANGE `user_id` `user_id` int(10) unsigned   NOT NULL after `id` ,
	ADD COLUMN `node_id` varchar(20)  COLLATE utf8_general_ci NULL after `user_id` ,
	ADD COLUMN `data` text  COLLATE utf8_general_ci NOT NULL after `node_id` ,
	DROP COLUMN `object_id` ,
	DROP COLUMN `cdate` ,
	DROP KEY `FK_favorites_object_id` ,
	DROP KEY `PRIMARY`, ADD PRIMARY KEY(`id`) ,
	DROP FOREIGN KEY `FK_favorites_object_id`  ,
	DROP FOREIGN KEY `FK_favorites_user_id`  ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;