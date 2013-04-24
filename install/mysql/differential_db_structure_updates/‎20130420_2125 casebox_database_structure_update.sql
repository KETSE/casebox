/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `config`
	ADD COLUMN `id` int(10) unsigned   NOT NULL auto_increment first ,
	ADD COLUMN `pid` int(11)   NULL after `id` ,
	CHANGE `param` `param` varchar(59)  COLLATE utf8_general_ci NOT NULL after `pid` ,
	CHANGE `value` `value` text  COLLATE utf8_general_ci NULL after `param` ,
	DROP COLUMN `default_value` ,
	ADD KEY `IDX_config__pid`(`pid`) ,
	DROP KEY `PRIMARY`, ADD PRIMARY KEY(`id`) ;


/* Create table in target */
CREATE TABLE `permissions_definition`(
	`id` tinyint(3) unsigned NOT NULL  auto_increment ,
	`bit_position` tinyint(3) unsigned NOT NULL  ,
	`translation_name` varchar(100) COLLATE utf8_general_ci NOT NULL  ,
	`description` varchar(100) COLLATE utf8_general_ci NULL  ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8';


/* Alter table in target */
ALTER TABLE `translations`
	ADD KEY `FK_translations_udate`(`udate`) ;


/* Drop in Second database */
DROP TABLE `translations_backup`;


/* Drop in Second database */
DROP TABLE `translations_original`;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;