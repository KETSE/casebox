/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_template_id`  ;


/* Alter table in target */
ALTER TABLE `tree`
	ADD COLUMN `draft_pid` varchar(10)  COLLATE utf8_general_ci NULL COMMENT 'used to attach other objects to a non existing, yet creating item' after `draft` ,
	CHANGE `template_id` `template_id` int(10) unsigned   NULL after `draft_pid` ,
	CHANGE `tag_id` `tag_id` int(10) unsigned   NULL after `template_id` ,
	CHANGE `target_id` `target_id` bigint(20) unsigned   NULL after `tag_id` ,
	CHANGE `name` `name` varchar(1000)  COLLATE utf8_general_ci NULL after `target_id` ,
	CHANGE `date` `date` datetime   NULL COMMENT 'start date' after `name` ,
	CHANGE `date_end` `date_end` datetime   NULL after `date` ,
	CHANGE `size` `size` bigint(20) unsigned   NULL after `date_end` ,
	CHANGE `is_main` `is_main` tinyint(1)   NULL after `size` ,
	CHANGE `cfg` `cfg` text  COLLATE utf8_general_ci NULL after `is_main` ,
	CHANGE `inherit_acl` `inherit_acl` tinyint(1)   NOT NULL DEFAULT 1 COMMENT 'inherit the access permissions from parent' after `cfg` ,
	CHANGE `cid` `cid` int(10) unsigned   NULL COMMENT 'creator id' after `inherit_acl` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date' after `cid` ,
	CHANGE `uid` `uid` int(10)   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL COMMENT 'update date' after `uid` ,
	CHANGE `updated` `updated` tinyint(1)   NOT NULL DEFAULT 1 COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved' after `udate` ,
	CHANGE `oid` `oid` int(11)   NULL COMMENT 'owner id' after `updated` ,
	CHANGE `did` `did` int(10) unsigned   NULL COMMENT 'delete user id' after `oid` ,
	CHANGE `ddate` `ddate` timestamp   NULL after `did` ,
	CHANGE `dstatus` `dstatus` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' after `ddate` ;

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;