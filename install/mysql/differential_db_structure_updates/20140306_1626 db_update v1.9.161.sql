/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_tag_id`  ;

/* Drop tables */
DROP TABLE `messages`;


DROP TABLE `objects_data`;


DROP TABLE `objects_duplicates`;


DROP TABLE `tags`;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;