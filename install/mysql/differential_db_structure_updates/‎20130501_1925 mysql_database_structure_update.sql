/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

USE `cb_achpr`;
/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_clean_deleted_nodes`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
create temporary table tmp_clean_tree_ids SELECT id FROM tree WHERE dstatus > 0;
	DELETE FROM cases WHERE id in (select id from tmp_clean_tree_ids);
	DELETE FROM objects WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM files WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM tasks WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	delete FROM tree WHERE id in (select id from tmp_clean_tree_ids);
	drop table tmp_clean_tree_ids;
    END$$
DELIMITER ;


/*  Drop Procedure in target  */

 DROP PROCEDURE `p_create_case_system_folders`;

/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_sort_templates`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_sort_templates`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Sort templates by l1 field and updates incremental order field'
BEGIN
	create table if not exists tmp_templates_sort (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `pid` int(11) unsigned DEFAULT NULL,
	  /*`l1` varchar(100) DEFAULT NULL,
	  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1=tag else = folder',/**/
	  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`));
	delete from tmp_templates_sort;
	SET @i = 0;

	insert into tmp_templates_sort (id, `order`) select id, @i:=@i+1 from templates where pid is null order by `type`, l1;
	while (select count(*) from templates t left join tmp_templates_sort ts1 on t.pid = ts1.id LEFT JOIN tmp_templates_sort ts2 ON t.id = ts2.id where ts1.id is not null and ts2.id is null) do
		SET @i = 0;
		SET @pid = 0;
		INSERT INTO tmp_templates_sort (id, `order`, pid)
			SELECT t.id, case when t.pid = @pid then @i:=@i+1 else @i:=1 END, @pid := t.pid
			FROM templates t left join tmp_templates_sort ts3 on t.pid = ts3.id LEFT JOIN tmp_templates_sort ts4 ON t.id = ts4.id WHERE ts3.id is NOT null and ts4.id is null ORDER BY t.pid, t.`type`, t.l1;
	end while;
	-- select * from tmp_templates_sort;
	update templates t, tmp_templates_sort ts set t.order = ts.order where t.id = ts.id;
	drop table tmp_templates_sort;

    END$$
DELIMITER ;


/*  Drop Function in target  */

 DROP FUNCTION `f_get_case_type_id`;

/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `f_get_objects_case_id`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_objects_case_id`(in_id int unsigned) RETURNS int(10) unsigned
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	declare tmp_pid int unsigned;
	DECLARE tmp_type varchar(10);
	DECLARE tmp_path TEXT CHARSET utf8 DEFAULT '';
	SET tmp_path = CONCAT('/', in_id);
	select t.pid, tp.`type` into tmp_pid, tmp_type from tree t left join templates tp on t.template_id = tp.id where t.id = in_id;
	while((tmp_pid is not null) AND (tmp_type <> 'case') AND ( INSTR(CONCAT(tmp_path, '/'), concat('/',tmp_pid,'/') ) =0) ) do
		SET tmp_path = CONCAT('/', tmp_pid, tmp_path);
		set in_id = tmp_pid;
		-- SELECT pid, `type` INTO tmp_pid, tmp_type FROM tree WHERE id = in_id;
		SELECT t.pid, tp.`type` INTO tmp_pid, tmp_type FROM tree t LEFT JOIN templates tp ON t.template_id = tp.id WHERE t.id = in_id;
	end while;

	if(tmp_type <> 'case') then
		set in_id = null;
	end if;
	return in_id;
    END$$
DELIMITER ;


/*  Drop Function in target  */

 DROP FUNCTION `f_get_sort_path`;

/*  Drop Function in target  */

 DROP FUNCTION `f_get_tags_value_l2_text`;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;