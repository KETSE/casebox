/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

UPDATE templates_structure SET `type` = 'H' WHERE tag = 'H';

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ;


/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `data` `data` mediumtext  COLLATE utf8_general_ci NULL after `private_for_user` ;

/* Alter table in target */
ALTER TABLE `users_groups`
	CHANGE `data` `data` mediumtext  COLLATE utf8_general_ci NULL after `cfg` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `objects`
	ADD CONSTRAINT `FK_objects__cid`
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__private_for_user`
	FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;