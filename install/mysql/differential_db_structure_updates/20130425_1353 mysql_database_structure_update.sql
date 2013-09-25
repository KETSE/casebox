/* copy default value to a separate parameter (a new row) */
SELECT default_value INTO @rp_def FROM config WHERE param = 'responsible_party';
INSERT INTO config (param, `value`) VALUES('responsible_party_default', @rp_def);
DELETE FROM config WHERE `value` = '' OR VALUE IS NULL;

/* transform template types to enum value*/
ALTER TABLE `templates`
  CHANGE `type` `type` VARCHAR(10) NULL;

UPDATE templates SET TYPE =
CASE WHEN `type` BETWEEN 1 AND 3 THEN 'object'
WHEN `type` = 4 THEN 'case'
WHEN `type` = 6 THEN 'user'
WHEN `type` = 7 THEN 'object'
WHEN `type` = 8 THEN 'email'
WHEN `type` = -100 THEN 'template'
END;

ALTER TABLE `templates` CHANGE `type` `type` ENUM('case','object','file','task','user','email','template') NOT NULL COMMENT '0-folder, 1-case object, 2-in action, 3-out action, 4-case template, 6-user, 7-contact, 8-email';


-- SYNC DB
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `templates`
	DROP FOREIGN KEY `FK_templates__pid`  ;

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_tag_id`  ;


/* Alter table in target */
ALTER TABLE `config`
	ADD COLUMN `id` INT(10) UNSIGNED   NOT NULL AUTO_INCREMENT FIRST ,
	ADD COLUMN `pid` INT(10) UNSIGNED   NULL AFTER `id` ,
	CHANGE `param` `param` VARCHAR(50)  COLLATE utf8_general_ci NOT NULL AFTER `pid` ,
	CHANGE `value` `value` TEXT  COLLATE utf8_general_ci NOT NULL AFTER `param` ,
	DROP COLUMN `default_value` ,
	DROP KEY `PRIMARY`, ADD PRIMARY KEY(`id`,`param`) ;


/* Alter table in target */
ALTER TABLE `templates`
	CHANGE `type` `type` ENUM('case','object','file','task','user','email','template')  COLLATE utf8_general_ci NOT NULL COMMENT '0-folder, 1-case object, 2-in action, 3-out action, 4-case template, 6-user, 7-contact, 8-email' AFTER `is_folder` ;


/* Alter table in target */
ALTER TABLE `tree`
	ADD COLUMN `template_id` INT(10) UNSIGNED   NULL AFTER `subtype` ,
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
	CHANGE `updated` `updated` TINYINT(1)   NOT NULL DEFAULT 1 AFTER `udate` ,
	CHANGE `oid` `oid` INT(11)   NULL COMMENT 'owner id' AFTER `updated` ,
	CHANGE `did` `did` INT(10) UNSIGNED   NULL COMMENT 'delete user id' AFTER `oid` ,
	CHANGE `ddate` `ddate` TIMESTAMP   NULL AFTER `did` ,
	CHANGE `dstatus` `dstatus` TINYINT(3) UNSIGNED   NOT NULL DEFAULT 0 COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted' AFTER `ddate` ;

/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `f_get_objects_case_id`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_objects_case_id`(in_id INT UNSIGNED) RETURNS INT(10) UNSIGNED
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_pid INT UNSIGNED;
	DECLARE tmp_type VARCHAR(10);
	DECLARE tmp_path TEXT CHARSET utf8 DEFAULT '';
	SET tmp_path = CONCAT('/', in_id);
	SELECT t.pid, tp.`type` INTO tmp_pid, tmp_type FROM tree t LEFT JOIN templates tp ON t.template_id = tp.id WHERE t.id = in_id;
	WHILE((tmp_pid IS NOT NULL) AND (tmp_type <> 'case') AND ( INSTR(CONCAT(tmp_path, '/'), CONCAT('/',tmp_pid,'/') ) =0) ) DO
		SET tmp_path = CONCAT('/', tmp_pid, tmp_path);
		SET in_id = tmp_pid;
		-- SELECT pid, `type` INTO tmp_pid, tmp_type FROM tree WHERE id = in_id;
		SELECT t.pid, tp.`type` INTO tmp_pid, tmp_type FROM tree t LEFT JOIN templates tp ON t.template_id = tp.id WHERE t.id = in_id;
	END WHILE;

	IF(tmp_type <> 'case') THEN
		SET in_id = NULL;
	END IF;
	RETURN in_id;
    END$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `templates`
	ADD CONSTRAINT `FK_templates__pid`
	FOREIGN KEY (`pid`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_tag_id`
	FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;


-- create templates for cases, tasks, folders and files


	-- case template
SET @pid = (SELECT id FROM templates WHERE NAME = 'casesTemplates');

	INSERT INTO templates (pid, `type`, `name`, l1, l2, l3, l4, iconCls, cfg, title_template)
	VALUES (@pid, 'case', 'case_template', 'Case', 'Case', 'Case', 'Case', 'icon-briefcase', NULL, '{name}');

SET @case_template_id = LAST_INSERT_ID();

/* move all other fields from case templates to our new template */
	UPDATE templates_structure SET template_id = @case_template_id WHERE template_id IN (SELECT id FROM templates WHERE `type` = 'case' AND id <> @case_template_id);
/*update all objects that point to old case template to new case template */
	UPDATE objects SET template_id = @case_template_id WHERE template_id IN (SELECT id FROM templates WHERE `type` = 'case' AND id <> @case_template_id);

/*delete old case templates */
DELETE FROM templates WHERE `type` = 'case' AND id <> @case_template_id;

/* adding new template fields*/
	INSERT INTO templates_structure (template_id, tag, NAME, l1, l2, l3, l4, `type`, `order`, cfg )
	VALUES (@case_template_id, 'f', 'name', 'Name', 'Name', 'Name', 'Name', 'varchar', 1, '{"showIn":"top"}');

SET @name_field_id = LAST_INSERT_ID();

	INSERT INTO templates_structure (template_id, tag, NAME, l1, l2, l3, l4, `type`, `order`, cfg )
	VALUES (@case_template_id, 'f', 'nr', 'Number', 'Number', 'Number', 'Number', 'varchar', 2, '{"showIn":"top"}');

SET @nr_field_id = LAST_INSERT_ID();

	INSERT INTO templates_structure (template_id, tag, NAME, l1, l2, l3, l4, `type`, `order`, cfg )
	VALUES (@case_template_id, 'f', '_date_start', 'Date', 'Date', 'Date', 'Date', 'date', 3, '{"showIn":"top"}');

SET @date_start_field_id = LAST_INSERT_ID();

	INSERT INTO templates_structure (template_id, tag, NAME, l1, l2, l3, l4, `type`, `order`, cfg )
	VALUES (@case_template_id, 'f', '_date_end', 'End date', 'End date', 'End date', 'End date', 'date', 4, '{"showIn":"top"}');

SET @date_end_field_id = LAST_INSERT_ID();


	-- tasks & events templates
SET @pid = (SELECT id FROM templates WHERE NAME = 'system');

	INSERT INTO templates (pid, `type`, `name`, l1, l2, l3, l4, iconCls, cfg, title_template)
	VALUES (@pid, 'task', 'tasks', 'Task', 'Task', 'Task', 'Task', 'icon-task', '{"data":{"type":6}}', '{name}');

SET @task_template_id = LAST_INSERT_ID();

	INSERT INTO templates (pid, `type`, `name`, l1, l2, l3, l4, iconCls, cfg, title_template)
	VALUES (@pid, 'task', 'event', 'Event', 'Event', 'Event', 'Event', 'icon-event', '{"data":{"type":7}}', '{name}');

SET @event_template_id = LAST_INSERT_ID();

	-- folders template
	INSERT INTO templates (pid, `type`, `name`, l1, l2, l3, l4, iconCls, cfg, title_template)
	VALUES (@pid, 'object', 'folder', 'Folder', 'Folder', 'Folder', 'Folder', 'icon-folder', '{"createMethod":"inline"}', '{name}');

SET @folder_template_id = LAST_INSERT_ID();


	-- file template
	INSERT INTO templates (pid, `type`, `name`, l1, l2, l3, l4, iconCls, cfg, title_template)
	VALUES (@pid, 'file', 'file_template', 'File', 'File', 'File', 'File', 'file-', NULL,  '{name}');

SET @file_template_id = LAST_INSERT_ID();

/* setting default files and folders template ids in config and set folder_templates = case_template_id,folder_template_id*/
INSERT INTO config (param, `value`) VALUES('folder_templates', CONCAT(@folder_template_id, ',',@case_template_id) );
INSERT INTO config (param, `value`) VALUES('default_folder_template', @folder_template_id);
INSERT INTO config (param, `value`) VALUES('default_file_template', @file_template_id);


/* inserting cases data into objects tables*/
CREATE TEMPORARY TABLE tmp_cases SELECT id, NAME, @case_template_id, `date`, close_date, cid, cdate, uid, udate FROM cases c;

INSERT INTO objects (id, custom_title, template_id, date_start, date_end, cid, cdate, uid, udate)
SELECT id, NAME, @case_template_id, `date`, close_date, cid, cdate, uid, udate FROM tmp_cases c
ON DUPLICATE KEY UPDATE template_id = @case_template_id, date_start = c.`date`, date_end = c.close_date, cid = c.cid, cdate = c.cdate, uid = c.uid, udate = c.udate;
DROP TABLE tmp_cases;

INSERT INTO objects_data  (object_id, field_id, duplicate_id, `value`) SELECT id, @name_field_id, 0, NAME FROM cases;
INSERT INTO objects_data  (object_id, field_id, duplicate_id, `value`) SELECT id, @nr_field_id, 0, nr FROM cases;
INSERT INTO objects_data  (object_id, field_id, duplicate_id, `value`) SELECT id, @date_start_field_id, 0, `date` FROM cases;
INSERT INTO objects_data  (object_id, field_id, duplicate_id, `value`) SELECT id, @date_end_field_id, 0, `close_date` FROM cases;

UPDATE tree SET template_id = @folder_template_id WHERE TYPE = 1;
UPDATE tree SET template_id = @case_template_id WHERE TYPE = 3;
UPDATE tree SET template_id = @file_template_id WHERE TYPE = 5;
UPDATE tree SET template_id = @task_template_id WHERE TYPE = 6;
UPDATE tree SET template_id = @event_template_id WHERE TYPE = 7;
UPDATE tree, objects SET tree.template_id = objects.template_id WHERE tree.id = objects.id;

