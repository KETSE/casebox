/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;


/* Alter table in target */
ALTER TABLE `notifications`
	ADD COLUMN `sent` tinyint(1)   NOT NULL DEFAULT 0 after `data` ,
	ADD COLUMN `viewed` tinyint(1)   NOT NULL DEFAULT 0 after `sent` ,
	ADD KEY `FK_notifications__sent`(`sent`) ,
	ADD KEY `FK_notifications__viewed`(`viewed`) ;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

UPDATE notifications SET sent = 1;
