/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `translations`
	ADD COLUMN `pid` int(10) unsigned   NULL after `id` ,
	CHANGE `name` `name` varbinary(100)   NULL after `pid` ,
	ADD COLUMN `en` varchar(250)  COLLATE utf8_general_ci NULL after `name` ,
	ADD COLUMN `es` varchar(250)  COLLATE utf8_general_ci NULL after `en` ,
	ADD COLUMN `ge` varchar(250)  COLLATE utf8_general_ci NULL after `es` ,
	ADD COLUMN `fr` varchar(250)  COLLATE utf8_general_ci NULL after `ge` ,
	ADD COLUMN `hy` varchar(250)  COLLATE utf8_general_ci NULL after `fr` ,
	ADD COLUMN `pt` varchar(250)  COLLATE utf8_general_ci NULL after `hy` ,
	ADD COLUMN `ro` varchar(250)  COLLATE utf8_general_ci NULL after `pt` ,
	ADD COLUMN `ru` varchar(250)  COLLATE utf8_general_ci NULL after `ro` ,
	ADD COLUMN `ar` varchar(1000)  COLLATE utf8_general_ci NULL after `ru` ,
	ADD COLUMN `zh` varchar(1000)  COLLATE utf8_general_ci NULL after `ar` ,
	CHANGE `type` `type` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT '0 - anywhere, 1 - server, 2 - client' after `zh` ,
	CHANGE `udate` `udate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `type` ,
	ADD COLUMN `info` varchar(1000)  COLLATE utf8_general_ci NULL COMMENT 'Where in CB the term is used, what it means' after `udate` ,
	ADD COLUMN `deleted` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT '0 - not deleted, 1 - deleted' after `info` ,
	DROP COLUMN `l1` ,
	DROP COLUMN `l2` ,
	DROP COLUMN `l3` ,
	DROP COLUMN `l4` ,
	ADD KEY `FK_translations__pid`(`pid`) ,
	ADD KEY `FK_translations_udate`(`udate`) ,
	DROP KEY `IDX_translations_name` ,
	ADD UNIQUE KEY `UNIQUE_translations__name`(`name`) ;
ALTER TABLE `translations`
	ADD CONSTRAINT `FK_translations__pid`
	FOREIGN KEY (`pid`) REFERENCES `translations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

DELIMITER $$

CREATE
    DEFINER = 'local'@'localhost'
    TRIGGER `translation_bu` BEFORE UPDATE
    ON `translations`
    FOR EACH ROW BEGIN
	SET new.udate = CURRENT_TIMESTAMP;
    END$$

DELIMITER ;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;