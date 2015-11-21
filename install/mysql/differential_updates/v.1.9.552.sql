/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Create table in target */
CREATE TABLE `guids`(
	`id` bigint(20) unsigned NOT NULL  auto_increment ,
	`name` varchar(200) COLLATE utf8_general_ci NOT NULL  ,
	PRIMARY KEY (`id`) ,
	UNIQUE KEY `guids_name`(`name`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;