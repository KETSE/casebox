/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `objects_data`
	DROP FOREIGN KEY `FK_objects_data__object_id`  ;
ALTER TABLE `objects_data`
	ADD CONSTRAINT `FK_objects_data__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;