/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;


/* Alter table in target */
ALTER TABLE `tree`
	ADD COLUMN `acl_count` tinyint(4)   NOT NULL DEFAULT 0 COMMENT 'Count of acl rules set for this node in tree_acl table' after `inherit_acl` ,
	CHANGE `cid` `cid` int(10) unsigned   NULL COMMENT 'creator id' after `acl_count` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date' after `cid` ,
	CHANGE `uid` `uid` int(10)   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL COMMENT 'update date' after `uid` ,
	CHANGE `updated` `updated` tinyint(1)   NOT NULL DEFAULT 1 COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved' after `udate` ,
	CHANGE `oid` `oid` int(11)   NULL COMMENT 'owner id' after `updated` ,
	CHANGE `did` `did` int(10) unsigned   NULL COMMENT 'delete user id' after `oid` ,
	CHANGE `ddate` `ddate` timestamp   NULL after `did` ,
	CHANGE `dstatus` `dstatus` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' after `ddate` ;

/* Create table in target */
CREATE TABLE `tree_acl_security_sets`(
	`id` int(10) unsigned NOT NULL  auto_increment ,
	`set` varchar(500) COLLATE utf8_general_ci NOT NULL  ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';


/* Alter table in target */
ALTER TABLE `users_groups`
	CHANGE `name` `name` varchar(50)  COLLATE utf8_general_ci NOT NULL after `system` ,
	CHANGE `l1` `l1` varchar(150)  COLLATE utf8_general_ci NULL after `name` ,
	CHANGE `l2` `l2` varchar(150)  COLLATE utf8_general_ci NULL after `l1` ,
	CHANGE `l3` `l3` varchar(150)  COLLATE utf8_general_ci NULL after `l2` ,
	CHANGE `l4` `l4` varchar(150)  COLLATE utf8_general_ci NULL after `l3` ,
	CHANGE `sex` `sex` char(1)  COLLATE utf8_general_ci NULL after `l4` ,
	CHANGE `email` `email` varchar(150)  COLLATE utf8_general_ci NULL after `sex` ,
	CHANGE `photo` `photo` varchar(250)  COLLATE utf8_general_ci NULL after `email` ,
	CHANGE `password` `password` varchar(255)  COLLATE utf8_general_ci NULL after `photo` ,
	CHANGE `recover_hash` `recover_hash` varchar(100)  COLLATE utf8_general_ci NULL after `password` ,
	CHANGE `language_id` `language_id` smallint(6) unsigned   NOT NULL DEFAULT 1 after `recover_hash` ,
	CHANGE `short_date_format` `short_date_format` varchar(10)  COLLATE utf8_general_ci NULL after `language_id` ,
	CHANGE `long_date_format` `long_date_format` varchar(20)  COLLATE utf8_general_ci NULL after `short_date_format` ,
	CHANGE `cfg` `cfg` text  COLLATE utf8_general_ci NULL after `long_date_format` ,
	CHANGE `last_login` `last_login` timestamp   NULL after `cfg` ,
	CHANGE `login_successful` `login_successful` tinyint(1)   NULL after `last_login` ,
	CHANGE `login_from_ip` `login_from_ip` varchar(40)  COLLATE utf8_general_ci NULL after `login_successful` ,
	CHANGE `last_logout` `last_logout` timestamp   NULL after `login_from_ip` ,
	CHANGE `last_action_time` `last_action_time` timestamp   NULL after `last_logout` ,
	CHANGE `enabled` `enabled` tinyint(1) unsigned   NULL DEFAULT 1 after `last_action_time` ,
	CHANGE `visible_in_reports` `visible_in_reports` tinyint(1) unsigned   NULL DEFAULT 1 after `enabled` ,
	CHANGE `deleted` `deleted` tinyint(1) unsigned   NULL DEFAULT 0 after `visible_in_reports` ,
	CHANGE `cid` `cid` int(11) unsigned   NULL after `deleted` ,
	CHANGE `cdate` `cdate` timestamp   NULL DEFAULT CURRENT_TIMESTAMP after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL DEFAULT '0000-00-00 00:00:00' after `uid` ,
	CHANGE `did` `did` int(11) unsigned   NULL after `udate` ,
	CHANGE `searchField` `searchField` text  COLLATE utf8_general_ci NULL after `did` ,
	DROP COLUMN `tag_id` ,
	DROP KEY `FK_users_groups__tag_id` ,
	DROP FOREIGN KEY `FK_users_groups__tag_id`  ;
/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_recalculate_security_sets`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	truncate table `tree_acl_security_sets`;
	insert into tree_acl_security_sets (id, `set`)
		select node_id, `f_get_tree_inherit_ids`(node_id) from
		(SELECT DISTINCT node_id FROM `tree_acl`) t;
    END$$
DELIMITER ;


/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_tree_acl_count`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Update acl_count field in tree table'
BEGIN

	create temporary table tmp_tree_acl_count select node_id `id`, count(*) `count` FROM `tree_acl` group by node_id;
	UPDATE tree, tmp_tree_acl_count set tree.acl_count = tmp_tree_acl_count.count where tree.id = tmp_tree_acl_count.id;
	drop table tmp_tree_acl_count;
    END$$
DELIMITER ;


/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`users_groups_ad` */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_bi` BEFORE INSERT ON `users_groups`
    FOR EACH ROW BEGIN
	set new.searchField = concat(
		' '
		,coalesce(new.name, '')
		,' '
		,COALESCE(new.l1, '')
		,' '
		,COALESCE(new.l2, '')
		,' '
		,COALESCE(new.l3, '')
		,' '
		,COALESCE(new.l4, '')
		,' '
		,COALESCE(new.email, '')
		,' '
	);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_bu` BEFORE UPDATE ON `users_groups`
    FOR EACH ROW BEGIN
	SET new.searchField = CONCAT(
		' '
		,COALESCE(new.name, '')
		,' '
		,COALESCE(new.l1, '')
		,' '
		,COALESCE(new.l2, '')
		,' '
		,COALESCE(new.l3, '')
		,' '
		,COALESCE(new.l4, '')
		,' '
		,COALESCE(new.email, '')
		,' '
	);
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