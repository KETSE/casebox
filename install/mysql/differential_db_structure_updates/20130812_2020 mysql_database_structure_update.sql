/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `tree_acl_security_sets`
	CHANGE `md5` `md5` varchar(32)  COLLATE utf8_general_ci NOT NULL DEFAULT '-' after `set` ;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;