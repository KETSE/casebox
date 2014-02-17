/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

UPDATE templates_structure SET cfg = REPLACE(cfg, 'variable', 'dependent');
UPDATE templates_structure SET cfg = REPLACE(cfg, '}', ',"faceting": true}') WHERE use_as_tags >0;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `templates_structure`
	DROP FOREIGN KEY `FK_templates_structure__pid`  ,
	DROP FOREIGN KEY `FK_templates_structure__template_id`  ;


/* Alter table in target */
ALTER TABLE `templates_structure`
	CHANGE `solr_column_name` `solr_column_name` varchar(50)  COLLATE utf8_general_ci NULL after `cfg` ,
	DROP COLUMN `solr_faceted` ,
	DROP COLUMN `use_as_tags` ;

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



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `templates_structure`
	ADD CONSTRAINT `FK_templates_structure__pid`
	FOREIGN KEY (`pid`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_templates_structure__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
