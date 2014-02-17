/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `files`
	DROP FOREIGN KEY `FK_files__content_id`  ,
	DROP FOREIGN KEY `FK_files__id`  ;

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ,
	DROP FOREIGN KEY `tree_template_id`  ;


/* Alter table in target */
ALTER TABLE `files`
	CHANGE `cid` `cid` INT(10) UNSIGNED   NOT NULL DEFAULT 1 AFTER `title` ,
	CHANGE `uid` `uid` INT(10) UNSIGNED   NOT NULL DEFAULT 1 AFTER `cid` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uid` ,
	CHANGE `udate` `udate` TIMESTAMP   NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `cdate` ,
	DROP COLUMN `old_name` ,
	DROP COLUMN `old_id` ,
	DROP KEY `UNQ_old_id` ;

/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `data` `data` MEDIUMTEXT  COLLATE utf8_general_ci NULL AFTER `id` ,
	ADD COLUMN `sys_data` MEDIUMTEXT  COLLATE utf8_general_ci NULL AFTER `data` ,
	DROP COLUMN `iconCls` ,
	DROP COLUMN `title` ,
	DROP COLUMN `custom_title` ,
	DROP COLUMN `private_for_user` ,
	DROP COLUMN `date_start` ,
	DROP COLUMN `date_end` ,
	DROP COLUMN `cid` ,
	DROP COLUMN `cdate` ,
	DROP COLUMN `uid` ,
	DROP COLUMN `udate` ,
	DROP KEY `FK_objects__cid` ,
	DROP KEY `FK_objects__private_for_user` ,
	DROP KEY `FK_objects__uid` ,
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ;

/* Alter table in target */
ALTER TABLE `objects_data`
	DROP COLUMN `private_for_user` ,
	DROP KEY `FK_objects_data__private_for_user` ,
	DROP FOREIGN KEY `FK_objects_data__private_for_user`  ;

/* Alter table in target */
ALTER TABLE `tree`
	CHANGE `pid` `pid` BIGINT(20) UNSIGNED   NULL AFTER `id` ,
	CHANGE `user_id` `user_id` INT(20) UNSIGNED   NULL AFTER `pid` ,
	CHANGE `system` `system` TINYINT(1)   NOT NULL DEFAULT 0 AFTER `user_id` ,
	CHANGE `type` `type` SMALLINT(5) UNSIGNED   NULL AFTER `system` ,
	CHANGE `subtype` `subtype` SMALLINT(5) UNSIGNED   NOT NULL DEFAULT 0 AFTER `type` ,
	CHANGE `template_id` `template_id` INT(10) UNSIGNED   NULL AFTER `subtype` ,
	CHANGE `tag_id` `tag_id` INT(10) UNSIGNED   NULL AFTER `template_id` ,
	CHANGE `target_id` `target_id` BIGINT(20) UNSIGNED   NULL AFTER `tag_id` ,
	CHANGE `name` `name` VARCHAR(1000)  COLLATE utf8_general_ci NULL AFTER `target_id` ,
	CHANGE `date` `date` DATETIME   NULL COMMENT 'start date' AFTER `name` ,
	CHANGE `date_end` `date_end` DATETIME   NULL AFTER `date` ,
	CHANGE `size` `size` BIGINT(20) UNSIGNED   NULL AFTER `date_end` ,
	CHANGE `is_main` `is_main` TINYINT(1)   NULL AFTER `size` ,
	CHANGE `cfg` `cfg` TEXT  COLLATE utf8_general_ci NULL AFTER `is_main` ,
	CHANGE `inherit_acl` `inherit_acl` TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'inherit the access permissions from parent' AFTER `cfg` ,
	CHANGE `cid` `cid` INT(10) UNSIGNED   NULL COMMENT 'creator id' AFTER `inherit_acl` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date' AFTER `cid` ,
	CHANGE `uid` `uid` INT(10)   NULL COMMENT 'updater id' AFTER `cdate` ,
	CHANGE `udate` `udate` TIMESTAMP   NULL COMMENT 'update date' AFTER `uid` ,
	CHANGE `updated` `updated` TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved' AFTER `udate` ,
	CHANGE `oid` `oid` INT(11)   NULL COMMENT 'owner id' AFTER `updated` ,
	CHANGE `did` `did` INT(10) UNSIGNED   NULL COMMENT 'delete user id' AFTER `oid` ,
	CHANGE `ddate` `ddate` TIMESTAMP   NULL AFTER `did` ,
	CHANGE `dstatus` `dstatus` TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' AFTER `ddate` ,
	DROP COLUMN `old_id` ;
/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_bi` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_bu` */;


/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `files`
	ADD CONSTRAINT `FK_files__content_id`
	FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_files__id`
	FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;