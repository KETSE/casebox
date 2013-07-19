/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `files`
	DROP FOREIGN KEY `FK_files__content_id`  ;

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__template_id`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ,
	DROP FOREIGN KEY `FK_objects_pid`  ;

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;

ALTER TABLE `users_groups_association`
	DROP FOREIGN KEY `FK_users_groups_association__group_id`  ,
	DROP FOREIGN KEY `FK_users_groups_association__user_id`  ;


/* Drop in Second database */
DROP TABLE `cases`;


/* Alter table in target */
ALTER TABLE `files`
	CHANGE `old_id` `old_id` INT(10) UNSIGNED   NULL AFTER `title` ,
	CHANGE `old_name` `old_name` VARCHAR(250)  COLLATE utf8_general_ci NULL AFTER `old_id` ,
	CHANGE `cid` `cid` INT(10) UNSIGNED   NOT NULL DEFAULT 1 AFTER `old_name` ,
	CHANGE `uid` `uid` INT(10) UNSIGNED   NOT NULL DEFAULT 1 AFTER `cid` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `uid` ,
	CHANGE `udate` `udate` TIMESTAMP   NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `cdate` ,
	DROP COLUMN `updated` ;


/* Alter table in target */
ALTER TABLE `notifications`
	DROP FOREIGN KEY `FK_notifications__case_id`  ;


/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `cid` `cid` INT(11) UNSIGNED   NULL AFTER `files_count` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `cid` ,
	CHANGE `uid` `uid` INT(11) UNSIGNED   NULL AFTER `cdate` ,
	CHANGE `udate` `udate` TIMESTAMP   NULL AFTER `uid` ,
	DROP COLUMN `updated` ;


/* Drop in Second database */
DROP TABLE `opened_cases`;


/* Alter table in target */
ALTER TABLE `tasks`
	DROP FOREIGN KEY `FK_tasks__case_id`  ;


/* Alter table in target */
ALTER TABLE `tree`
	ADD COLUMN `case_id` BIGINT(20) UNSIGNED   NULL AFTER `pid` ,
	CHANGE `user_id` `user_id` INT(20) UNSIGNED   NULL AFTER `case_id` ,
	CHANGE `system` `system` TINYINT(1)   NOT NULL DEFAULT 0 AFTER `user_id` ,
	CHANGE `type` `type` SMALLINT(5) UNSIGNED   NOT NULL AFTER `system` ,
	CHANGE `subtype` `subtype` SMALLINT(5) UNSIGNED   NOT NULL DEFAULT 0 AFTER `type` ,
	CHANGE `template_id` `template_id` INT(10) UNSIGNED   NULL AFTER `subtype` ,
	CHANGE `tag_id` `tag_id` INT(10) UNSIGNED   NULL AFTER `template_id` ,
	CHANGE `target_id` `target_id` BIGINT(20) UNSIGNED   NULL AFTER `tag_id` ,
	CHANGE `name` `name` VARCHAR(150)  COLLATE utf8_general_ci NULL AFTER `target_id` ,
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
	CHANGE `dstatus` `dstatus` TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' AFTER `ddate` ;


/* Alter table in target */
ALTER TABLE `users_groups_association`
	CHANGE `uid` `uid` INT(11) UNSIGNED   NULL AFTER `cdate` ,
	CHANGE `udate` `udate` TIMESTAMP   NULL AFTER `uid` ;

/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_clean_deleted_nodes`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_clean_deleted_nodes`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE tmp_clean_tree_ids SELECT id FROM tree WHERE dstatus > 0;

	DELETE FROM objects WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM files WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM tasks WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM tree WHERE id IN (SELECT id FROM tmp_clean_tree_ids);

	DROP TABLE tmp_clean_tree_ids;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_delete_tree_node`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_delete_tree_node`(in_id BIGINT UNSIGNED)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DELETE FROM tree WHERE id = in_id;
	DELETE FROM objects WHERE id = in_id;
	DELETE FROM files WHERE id = in_id;
	DELETE FROM tasks WHERE id = in_id;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_mark_all_childs_as_updated`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_updated`(in_id BIGINT UNSIGNED, in_update_bits TINYINT UNSIGNED)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids(id BIGINT UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids2(id BIGINT UNSIGNED);
	DELETE FROM tmp_child_ids;
	DELETE FROM tmp_child_ids2;
	INSERT INTO tmp_child_ids SELECT id FROM tree WHERE pid = in_id AND dstatus = 0;
	WHILE(ROW_COUNT() > 0)DO
		UPDATE tree, tmp_child_ids
			SET tree.updated = (tree.updated | in_update_bits)
				, tree.case_id = CASE WHEN (tree.updated && 100)= 100 THEN `f_get_objects_case_id`(tree.id) ELSE tree.case_id END
			WHERE tmp_child_ids.id = tree.id;
		DELETE FROM tmp_child_ids2;
		INSERT INTO tmp_child_ids2 SELECT id FROM tmp_child_ids;
		DELETE FROM tmp_child_ids;
		INSERT INTO tmp_child_ids SELECT t.id FROM tree t JOIN tmp_child_ids2 c ON t.pid = c.id AND t.dstatus = 0;
	END WHILE;
    END$$
DELIMITER ;


/*  Drop Procedure in target  */

 DROP PROCEDURE `p_update_case_card_titles_for_cases`;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_bi` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_bu` */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `files_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `files_au` AFTER UPDATE ON `files`
    FOR EACH ROW BEGIN
	UPDATE tree SET
		`name` = new.name
		, `date` = COALESCE(new.date, new.cdate)
		, date_end = COALESCE(new.date, new.cdate)
		, cid = new.cid
		, cdate = new.cdate
		, uid = new.uid
		, udate = new.udate
		, updated = (updated | 1)
		, size = (SELECT size FROM files_content WHERE id = new.content_id)
	WHERE id = new.id;

	IF(COALESCE(old.content_id, 0) <> COALESCE(new.content_id, 0) ) THEN
		IF(old.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
		END IF;

		IF(new.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count + 1 WHERE id = new.content_id;
		END IF;
	END IF;
    END;
$$
DELIMITER ;


/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`files_bu` */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `files_content_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `files_content_au` AFTER UPDATE ON `files_content`
    FOR EACH ROW BEGIN
	UPDATE tree, files SET tree.updated = (tree.updated | 1) WHERE files.content_id = NEW.id AND files.id = tree.id;
    END;
$$
DELIMITER ;


/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_ad` */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_ai` AFTER INSERT ON `objects`
    FOR EACH ROW BEGIN
	UPDATE tree SET `name` = COALESCE(new.custom_title, new.title), `date` = COALESCE(new.date_start, new.cdate), date_end = COALESCE(new.date_end, new.date_start, new.date_start), cid = new.cid, cdate = new.cdate, uid = new.uid, udate = new.udate WHERE id = new.id;

	/* if object is a case template then mark all case childs as update for roles reset */
	IF(SELECT 1 FROM templates WHERE id = new.template_id AND `type` = 'case') THEN
		CALL `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;
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
		,`date` = COALESCE(new.date_start, new.cdate)
		,date_end = COALESCE(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
		WHERE id = new.id;

	/* if object is a case template then mark all case childs as update for roles reset */
	IF(SELECT 1 FROM templates WHERE id = new.template_id AND `type` = 'case') THEN
		CALL `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;
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
	SET NEW.is_active =((NEW.date_end IS NOT NULL) && (NEW.date_end < NOW()));
	IF(TRIM(NEW.custom_title) = '') THEN SET NEW.custom_title = NULL; END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tasks_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tasks_au` AFTER UPDATE ON `tasks`
    FOR EACH ROW BEGIN
	DELETE FROM tasks_responsible_users  WHERE task_id = old.id AND CONCAT(',', new.responsible_user_ids, ',') NOT LIKE CONCAT('%,',user_id,',%');
	INSERT INTO tasks_responsible_users (task_id, user_id) SELECT new.id, u.id FROM users_groups u WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',u.id,',%')
		ON DUPLICATE KEY UPDATE user_id = u.id;
	UPDATE tree SET
		`name` = new.title
		,`date` = COALESCE(new.date_start, new.date_end, new.cdate)
		,date_end = new.date_end
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
	WHERE id = new.id;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tasks_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tasks_bu` BEFORE UPDATE ON `tasks`
    FOR EACH ROW BEGIN
	IF(new.status != 3) THEN -- not completed
		SET new.missed = (new.date_end < CURRENT_DATE);
		IF(new.missed = 1) THEN
			SET new.status = 1;
		END IF;

	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_ad`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_ad` AFTER DELETE ON `tree_acl`
    FOR EACH ROW BEGIN
	UPDATE tree SET updated = (updated | 10) WHERE id = old.node_id;
	CALL p_mark_all_childs_as_updated(old.node_id, 10);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_ai` AFTER INSERT ON `tree_acl`
    FOR EACH ROW BEGIN
	UPDATE tree SET updated = (10 | updated) WHERE id = new.node_id;
	CALL p_mark_all_childs_as_updated(new.node_id, 10);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_au` AFTER UPDATE ON `tree_acl`
    FOR EACH ROW BEGIN
	UPDATE tree SET updated = (updated | 10) WHERE id = new.node_id;
	CALL p_mark_all_childs_as_updated(new.node_id, 10);
    END;
$$
DELIMITER ;


/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`tree_bd` */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_bi` BEFORE INSERT ON `tree`
    FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	IF (new.id = new.pid) THEN
		SET msg = CONCAT('Error inserting cyclic reference: ', CAST(new.id AS CHAR));
		signal SQLSTATE '45000' SET message_text = msg;
	END IF;
	/* trivial check for cycles */

	/* set case_id field */
	IF( (new.template_id IS NOT NULL) AND (SELECT id FROM templates WHERE id = new.template_id AND `type` = 'case') ) THEN
		SET new.case_id = new.id;
	ELSE
		SET new.case_id = `f_get_objects_case_id`(new.pid);
	END IF;
	/* end of set case_id field */
	SET new.oid = COALESCE(new.oid, new.cid);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_bu` BEFORE UPDATE ON `tree`
    FOR EACH ROW BEGIN
	/* set case_id field */
	IF( new.pid <> old.pid ) THEN
		IF( (new.template_id IS NOT NULL) AND (SELECT id FROM templates WHERE id = new.template_id AND `type` = 'case') ) THEN
			SET new.case_id = new.id;
		ELSE
			SET new.case_id = `f_get_objects_case_id`(new.pid);
		END IF;
	END IF;
	/* end of set case_id field */

    END;
$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `files`
	ADD CONSTRAINT `FK_files__content_id`
	FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

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

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ;

ALTER TABLE `users_groups_association`
	ADD CONSTRAINT `FK_users_groups_association__group_id`
	FOREIGN KEY (`group_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_users_groups_association__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

UPDATE tree SET case_id = f_get_objects_case_id(id);

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;