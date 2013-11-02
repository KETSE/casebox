/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ;

ALTER TABLE `templates_structure`
	DROP FOREIGN KEY `FK_templates_structure__template_id`  ;

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;


/* Alter table in target */
ALTER TABLE `config`
	DROP KEY `PRIMARY`, ADD PRIMARY KEY(`id`) ,
	ADD UNIQUE KEY `unq_param`(`param`) ;

/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `title` `title` varchar(1000)  COLLATE utf8_general_ci NULL after `id` ,
	CHANGE `custom_title` `custom_title` varchar(1000)  COLLATE utf8_general_ci NULL after `title` ,
	CHANGE `date_start` `date_start` datetime   NULL after `custom_title` ,
	CHANGE `date_end` `date_end` datetime   NULL after `date_start` ,
	CHANGE `iconCls` `iconCls` varchar(150)  COLLATE utf8_general_ci NULL after `date_end` ,
	CHANGE `private_for_user` `private_for_user` int(11) unsigned   NULL after `iconCls` ,
	CHANGE `cid` `cid` int(11) unsigned   NULL after `private_for_user` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL after `uid` ,
	DROP COLUMN `old_id` ,
	DROP COLUMN `template_id` ,
	DROP COLUMN `files_count` ,
	DROP COLUMN `pid` ,
	DROP COLUMN `author` ,
	DROP COLUMN `is_active` ,
	DROP COLUMN `details` ,
	DROP KEY `FK_objects__template_id` ,
	DROP KEY `FK_objects_pid` ,
	DROP KEY `UK_old_id` ,
	DROP FOREIGN KEY `FK_objects__template_id`  ,
	DROP FOREIGN KEY `FK_objects_pid`  ;

/* Alter table in target */
ALTER TABLE `sessions`
	ADD COLUMN `user_id` int(10) unsigned   NOT NULL after `expires` ,
	CHANGE `data` `data` text  COLLATE utf8_general_ci NULL after `user_id` ;

/* Alter table in target */
ALTER TABLE `templates`
	CHANGE `type` `type` enum('case','object','file','task','user','email','template','field','search')  COLLATE utf8_general_ci NULL after `is_folder` ,
	CHANGE `l3` `l3` varchar(250)  COLLATE utf8_general_ci NULL after `l2` ,
	DROP FOREIGN KEY `FK_templates__pid`  ;

/* Alter table in target */
ALTER TABLE `templates_structure`
	CHANGE `level` `level` smallint(6) unsigned   NULL DEFAULT 0 after `tag` ,
	CHANGE `order` `order` smallint(6) unsigned   NULL DEFAULT 0 after `type` ,
	DROP FOREIGN KEY `FK_templates_structure__pid`  ;

/* Alter table in target */
ALTER TABLE `tree`
	CHANGE `cid` `cid` int(10) unsigned   NULL COMMENT 'creator id' after `inherit_acl` ,
	CHANGE `cdate` `cdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date' after `cid` ,
	CHANGE `uid` `uid` int(10)   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL COMMENT 'update date' after `uid` ,
	CHANGE `updated` `updated` tinyint(1)   NOT NULL DEFAULT 1 COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved' after `udate` ,
	CHANGE `oid` `oid` int(11)   NULL COMMENT 'owner id' after `updated` ,
	CHANGE `did` `did` int(10) unsigned   NULL COMMENT 'delete user id' after `oid` ,
	CHANGE `ddate` `ddate` timestamp   NULL after `did` ,
	CHANGE `dstatus` `dstatus` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' after `ddate` ,
	DROP COLUMN `acl_count` ,
	ADD KEY `tree_template_id`(`template_id`) ,
	DROP KEY `UNQ_pid_system_type_subtype_tag_id` ;
ALTER TABLE `tree`
	ADD CONSTRAINT `tree_template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE ;

/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_clear_lost_objects`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_clear_lost_ids(id bigint UNSIGNED);
	delete from tmp_clear_lost_ids;

	insert into tmp_clear_lost_ids
		SELECT o.id
		FROM objects o
		LEFT JOIN tree t
			ON o.`id` = t.id
		WHERE t.id IS NULL;
	DELETE FROM objects WHERE id IN
	(select id from tmp_clear_lost_ids);
	DELETE FROM tmp_clear_lost_ids;

	INSERT INTO tmp_clear_lost_ids
		SELECT od.id
		FROM objects_data od
		LEFT JOIN tree t
			ON od.`object_id` = t.id
		WHERE t.id IS NULL;
	DELETE FROM objects_data WHERE id IN
	(SELECT id FROM tmp_clear_lost_ids);

	drop table tmp_clear_lost_ids;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_user_login`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_user_login`(IN `in_username` VARCHAR(50), `in_password` VARCHAR(100), `in_from_ip` VARCHAR(40))
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'checks for login credetials and log the attemps'
BEGIN
	DECLARE `user_id` INT DEFAULT NULL;
	DECLARE `user_pass` VARCHAR(255);
	SELECT `id`, `password`  INTO `user_id`, `user_pass` FROM users_groups WHERE `name` = `in_username` and enabled = 1 and did is NULL;
	IF(user_id IS NOT NULL) THEN
		IF(`user_pass` = MD5(CONCAT('aero', `in_password`))) THEN
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = 1, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 1 `status`;
		ELSE
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = login_successful-2, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 0 `status`;
		END IF;
	ELSE
		SELECT 0 `user_id`, 0 `status`;
	END IF;
    END$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_bi` BEFORE INSERT ON `objects`
    FOR EACH ROW BEGIN
	-- SET new.is_active =((new.date_end is not null) && (new.date_end < now()));
	if(TRIM(NEW.custom_title) = '') THEN set new.custom_title = null; END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_bu` BEFORE UPDATE ON `objects`
    FOR EACH ROW BEGIN
	-- SET NEW.is_active =((NEW.date_end IS NOT NULL) && (NEW.date_end < NOW()));
	IF(TRIM(NEW.custom_title) = '') THEN SET NEW.custom_title = NULL; END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `templates_structure_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `templates_structure_bu` BEFORE UPDATE ON `templates_structure`
    FOR EACH ROW BEGIN
	IF(NEW.PID IS NOT NULL) THEN
		SET NEW.LEVEL = coalesce((SELECT `level` +1 FROM templates_structure WHERE id = NEW.PID), 0);
	END IF;
    END;
$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `objects`
	ADD CONSTRAINT `FK_objects__cid`
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__private_for_user`
	FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

ALTER TABLE `templates_structure`
	ADD CONSTRAINT `FK_templates_structure__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;