/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__template_id`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ,
	DROP FOREIGN KEY `FK_objects_pid`  ;

ALTER TABLE `objects_data`
	DROP FOREIGN KEY `FK_objects_data__field_id`  ,
	DROP FOREIGN KEY `FK_objects_data__private_for_user`  ;

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;


/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `title` `title` varchar(1000)  COLLATE utf8_general_ci NULL after `pid` ,
	CHANGE `custom_title` `custom_title` varchar(1000)  COLLATE utf8_general_ci NULL after `title` ;

/* Alter table in target */
ALTER TABLE `objects_data`
	CHANGE `info` `info` varchar(1000)  COLLATE utf8_general_ci NULL after `value` ;

/* Alter table in target */
ALTER TABLE `tree`
	CHANGE `name` `name` varchar(1000)  COLLATE utf8_general_ci NULL after `target_id` ;

/* Alter table in target */
ALTER TABLE `users_groups`
	CHANGE `system` `system` tinyint(3) unsigned   NOT NULL DEFAULT 0 COMMENT '1 - is a system group that cannot be deleted from ui' after `type` ,
	ADD COLUMN `first_name` varchar(100)  COLLATE utf8_general_ci NULL after `name` ,
	ADD COLUMN `last_name` varchar(100)  COLLATE utf8_general_ci NULL after `first_name` ,
	CHANGE `l1` `l1` varchar(150)  COLLATE utf8_general_ci NULL after `last_name` ,
	CHANGE `l2` `l2` varchar(150)  COLLATE utf8_general_ci NULL after `l1` ,
	CHANGE `l3` `l3` varchar(150)  COLLATE utf8_general_ci NULL after `l2` ,
	CHANGE `l4` `l4` varchar(150)  COLLATE utf8_general_ci NULL after `l3` ,
	CHANGE `sex` `sex` char(1)  COLLATE utf8_general_ci NULL COMMENT 'extracted gender from users data' after `l4` ,
	CHANGE `email` `email` varchar(150)  COLLATE utf8_general_ci NULL COMMENT 'primary user email' after `sex` ,
	CHANGE `photo` `photo` varchar(250)  COLLATE utf8_general_ci NULL COMMENT 'filename of uploated photo file' after `email` ,
	CHANGE `password` `password` varchar(255)  COLLATE utf8_general_ci NULL after `photo` ,
	ADD COLUMN `password_change` date   NULL after `password` ,
	CHANGE `recover_hash` `recover_hash` varchar(100)  COLLATE utf8_general_ci NULL after `password_change` ,
	CHANGE `language_id` `language_id` smallint(6) unsigned   NOT NULL DEFAULT 1 COMMENT 'extracted language index from users data' after `recover_hash` ,
	CHANGE `cfg` `cfg` text  COLLATE utf8_general_ci NULL after `language_id` ,
	CHANGE `last_login` `last_login` timestamp   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `cfg` ,
	CHANGE `login_successful` `login_successful` tinyint(1)   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `last_login` ,
	CHANGE `login_from_ip` `login_from_ip` varchar(40)  COLLATE utf8_general_ci NULL COMMENT 'should be moved to an auth log table for security enhancement' after `login_successful` ,
	CHANGE `last_logout` `last_logout` timestamp   NULL COMMENT 'should be moved to an auth log table for security enhancement' after `login_from_ip` ,
	CHANGE `last_action_time` `last_action_time` timestamp   NULL after `last_logout` ,
	CHANGE `enabled` `enabled` tinyint(1) unsigned   NULL DEFAULT 1 after `last_action_time` ,
	CHANGE `cid` `cid` int(11) unsigned   NULL COMMENT 'creator id' after `enabled` ,
	CHANGE `cdate` `cdate` timestamp   NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation timestamp' after `cid` ,
	CHANGE `uid` `uid` int(11) unsigned   NULL COMMENT 'updater id' after `cdate` ,
	CHANGE `udate` `udate` timestamp   NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'updated timestamp' after `uid` ,
	CHANGE `did` `did` int(11) unsigned   NULL COMMENT 'deleter id' after `udate` ,
	ADD COLUMN `ddate` timestamp   NULL after `did` ,
	CHANGE `searchField` `searchField` text  COLLATE utf8_general_ci NULL COMMENT 'helper field for users quick searching' after `ddate` ,
	DROP COLUMN `short_date_format` ,
	DROP COLUMN `long_date_format` ,
	DROP COLUMN `visible_in_reports` ,
	DROP COLUMN `deleted` ;
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
			SELECT user_id, 1;
		ELSE
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = login_successful-2, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 0;
		END IF;
	ELSE
		SELECT 0, 0;
	END IF;
    END$$
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
	if( coalesce(old.password, '') <> coalesce(new.password, '') ) THEN
		set new.password_change = CURRENT_DATE;
	end if;
    END;
$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `objects`
	ADD CONSTRAINT `FK_objects__cid`
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__private_for_user`
	FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects_pid`
	FOREIGN KEY (`pid`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `objects_data`
	ADD CONSTRAINT `FK_objects_data__field_id`
	FOREIGN KEY (`field_id`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects_data__private_for_user`
	FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;