/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

USE `casebox`;

/* Alter table in target */
ALTER TABLE `translations`
	ADD COLUMN `pid` int(10) unsigned   NULL after `id` ,
	CHANGE `name` `name` varbinary(100)   NULL after `pid` ,
	CHANGE `en` `en` varchar(250)  COLLATE utf8_general_ci NULL after `name` ,
	CHANGE `fr` `fr` varchar(250)  COLLATE utf8_general_ci NULL after `en` ,
	CHANGE `es` `es` varchar(250)  COLLATE utf8_general_ci NULL after `fr` ,
	CHANGE `ro` `ro` varchar(250)  COLLATE utf8_general_ci NULL after `es` ,
	CHANGE `ru` `ru` varchar(250)  COLLATE utf8_general_ci NULL after `ro` ,
	CHANGE `hy` `hy` varchar(250)  COLLATE utf8_general_ci NULL after `ru` ,
	CHANGE `type` `type` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT '0 - anywhere, 1 - server, 2 - client' after `hy` ,
	CHANGE `udate` `udate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `type` ,
	ADD KEY `FK_translations__pid`(`pid`) ,
	ADD CONSTRAINT `FK_translations__pid`
	FOREIGN KEY (`pid`) REFERENCES `translations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;