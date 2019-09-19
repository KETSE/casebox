/* adding shortcut type to template types*/
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `templates`
	CHANGE `type` `type` enum('case','object','file','task','user','email','template','field','search','comment','shortcut')  COLLATE utf8_general_ci NULL after `is_folder` ;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;