/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;


/* Alter table in target */
ALTER TABLE `tree`
	CHANGE `user_id` `user_id` int(20) unsigned   NULL after `pid` ,
	CHANGE `system` `system` tinyint(1)   NOT NULL DEFAULT 0 after `user_id` ,
	CHANGE `type` `type` smallint(5) unsigned   NOT NULL after `system` ,
	CHANGE `subtype` `subtype` smallint(5) unsigned   NOT NULL DEFAULT 0 after `type` ,
	CHANGE `template_id` `template_id` int(10) unsigned   NULL after `subtype` ,
	CHANGE `tag_id` `tag_id` int(10) unsigned   NULL after `template_id` ,
	CHANGE `target_id` `target_id` bigint(20) unsigned   NULL after `tag_id` ,
	CHANGE `name` `name` varchar(1000)  COLLATE utf8_general_ci NULL after `target_id` ,
	CHANGE `date` `date` datetime   NULL COMMENT 'start date' after `name` ,
	CHANGE `date_end` `date_end` datetime   NULL after `date` ,
	CHANGE `size` `size` bigint(20) unsigned   NULL after `date_end` ,
	CHANGE `is_main` `is_main` tinyint(1)   NULL after `size` ,
	CHANGE `cfg` `cfg` text  COLLATE utf8_general_ci NULL after `is_main` ,
	CHANGE `inherit_acl` `inherit_acl` tinyint(1)   NOT NULL DEFAULT 1 COMMENT 'inherit the access permissions from parent' after `cfg` ,
	CHANGE `acl_count` `acl_count` tinyint(4)   NOT NULL DEFAULT 0 COMMENT 'Count of acl rules set for this node in tree_acl table' after `inherit_acl` ,
	CHANGE `cid` `cid` int(10) unsigned   NULL COMMENT 'creator id' after `acl_count` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date' after `cid` ,
	CHANGE `uid` `uid` int(10)   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL COMMENT 'update date' after `uid` ,
	CHANGE `updated` `updated` tinyint(1)   NOT NULL DEFAULT 1 COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved' after `udate` ,
	CHANGE `oid` `oid` int(11)   NULL COMMENT 'owner id' after `updated` ,
	CHANGE `did` `did` int(10) unsigned   NULL COMMENT 'delete user id' after `oid` ,
	CHANGE `ddate` `ddate` timestamp   NULL after `did` ,
	CHANGE `dstatus` `dstatus` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' after `ddate` ,
	DROP COLUMN `case_id` ;
/*  Drop Procedure in target  */

 DROP PROCEDURE `p_mark_all_childs_as_updated`;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_ai` AFTER INSERT ON `objects`
    FOR EACH ROW BEGIN

	UPDATE tree
	SET `name` = COALESCE(new.custom_title, new.title)
		,`date` = COALESCE(new.date_start, new.cdate)
		,date_end = COALESCE(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
	WHERE id = new.id;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_au` AFTER UPDATE ON `objects`
    FOR EACH ROW BEGIN
	UPDATE tree
		SET `name` = COALESCE(new.custom_title, new.title)
		,`date` = coalesce(new.date_start, new.cdate)
		,date_end = coalesce(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
		WHERE id = new.id;
    END;
$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;