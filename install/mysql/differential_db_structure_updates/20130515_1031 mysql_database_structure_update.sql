/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `templates`
  CHANGE `type` `type` ENUM('case','object','file','task','user','email','template') CHARSET utf8 COLLATE utf8_general_ci NULL;

ALTER TABLE `users_groups_association`   
  CHANGE `uid` `uid` INT(11) UNSIGNED NULL,
  CHANGE `udate` `udate` TIMESTAMP NULL;

ALTER TABLE `objects`
	DROP FOREIGN KEY `FK_objects__cid`  ,
	DROP FOREIGN KEY `FK_objects__private_for_user`  ,
	DROP FOREIGN KEY `FK_objects__template_id`  ,
	DROP FOREIGN KEY `FK_objects__uid`  ,
	DROP FOREIGN KEY `FK_objects_pid`  ;


/* Alter table in target */
ALTER TABLE `objects`
	CHANGE `title` `title` VARCHAR(200)  COLLATE utf8_general_ci NULL AFTER `pid` ,
	CHANGE `custom_title` `custom_title` VARCHAR(200)  COLLATE utf8_general_ci NULL AFTER `title` ,
	CHANGE `template_id` `template_id` INT(11) UNSIGNED   NULL AFTER `custom_title` ,
	CHANGE `date_start` `date_start` DATETIME   NULL AFTER `template_id` ,
	CHANGE `date_end` `date_end` DATETIME   NULL AFTER `date_start` ,
	CHANGE `author` `author` INT(11) UNSIGNED   NULL AFTER `date_end` ,
	CHANGE `is_active` `is_active` TINYINT(1) UNSIGNED   NULL DEFAULT 0 AFTER `author` ,
	CHANGE `iconCls` `iconCls` VARCHAR(150)  COLLATE utf8_general_ci NULL AFTER `is_active` ,
	CHANGE `details` `details` TEXT  COLLATE utf8_general_ci NULL AFTER `iconCls` ,
	CHANGE `private_for_user` `private_for_user` INT(11) UNSIGNED   NULL AFTER `details` ,
	CHANGE `files_count` `files_count` INT(10) UNSIGNED   NULL AFTER `private_for_user` ,
	CHANGE `updated` `updated` TINYINT(1) UNSIGNED   NOT NULL DEFAULT 1 AFTER `files_count` ,
	CHANGE `cid` `cid` INT(11) UNSIGNED   NULL AFTER `updated` ,
	CHANGE `cdate` `cdate` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `cid` ,
	CHANGE `uid` `uid` INT(11) UNSIGNED   NULL AFTER `cdate` ,
	CHANGE `udate` `udate` TIMESTAMP   NULL AFTER `uid` ,
	DROP COLUMN `phase_id` ,
	DROP COLUMN `case_id` ,
	DROP COLUMN `type_id` ,
	DROP KEY `FK_objects__case_id` ,
	DROP FOREIGN KEY `FK_objects__case_id`  ;


/* Alter table in target */
ALTER TABLE `objects_data`
	ADD KEY `IDX_object_id`(`object_id`) ,
	DROP FOREIGN KEY `FK_objects_data__object_id`  ;


/* Alter table in target */
ALTER TABLE `objects_duplicates`
	DROP FOREIGN KEY `FK_objects_duplicates__object_id`  ;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_ad`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_ad` AFTER DELETE ON `objects`
    FOR EACH ROW BEGIN
	/* if object is a case template then mark all case childs as update for roles reset */
	IF(SELECT 1 FROM templates WHERE id = old.template_id AND `type` = 'case') THEN
		CALL `p_mark_all_childs_as_updated`(old.id);
	END IF;
    END;
$$
DELIMITER ;


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
		CALL `p_mark_all_childs_as_updated`(new.id);
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
	IF(new.updated = 1) THEN
		UPDATE tree SET `name` = COALESCE(new.custom_title, new.title), `date` = COALESCE(new.date_start, new.cdate), date_end = COALESCE(new.date_end, new.date_start, new.date_start), cid = new.cid, cdate = new.cdate, uid = new.uid, udate = new.udate WHERE id = new.id;

		/* if object is a case template then mark all case childs as update for roles reset */
		IF(SELECT 1 FROM templates WHERE id = new.template_id AND `type` = 'case') THEN
			CALL `p_mark_all_childs_as_updated`(new.id);
		END IF;
	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_bi` BEFORE INSERT ON `objects`
    FOR EACH ROW BEGIN
	SET new.is_active =((new.date_end IS NOT NULL) && (new.date_end < NOW()));
	IF(TRIM(NEW.custom_title) = '') THEN SET new.custom_title = NULL; END IF;
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
	IF(new.updated = old.updated) THEN
		SET new.updated = 1;
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
	ADD CONSTRAINT `FK_objects__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects__uid`
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_objects_pid`
	FOREIGN KEY (`pid`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;