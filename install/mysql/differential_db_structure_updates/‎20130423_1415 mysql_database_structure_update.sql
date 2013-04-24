/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Create table in target */
CREATE TABLE `menu`(
	`id` int(10) unsigned NOT NULL  auto_increment ,
	`node_ids` varchar(20) COLLATE latin1_swedish_ci NULL  ,
	`node_template_ids` varchar(10) COLLATE latin1_swedish_ci NULL  ,
	`menu` text COLLATE latin1_swedish_ci NULL  ,
	`user_group_ids` varchar(10) COLLATE latin1_swedish_ci NULL  ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET='latin1';

/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `f_get_case_type_id`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_case_type_id`(in_case_id INT UNSIGNED) RETURNS int(10) unsigned
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_case_type_id INT UNSIGNED default null;

	select type_id into tmp_case_type_id from cases where id = in_case_id;
	/*IF(COALESCE(tmp_case_type_id, 0) = 0) THEN
		select tgt.tag_id into tmp_case_type_id from
		tag_groups tg
		join tag_groups__tags_result tgt on tg.id = tgt.tags_group_id
		join cases_tags ct on tgt.tag_id = ct.tag_id and ct.case_id = in_case_id
		where tg.system = 2 limit 1;
	END IF;/**/
	return tmp_case_type_id;
    END$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_bu` BEFORE UPDATE ON `users_groups`
    FOR EACH ROW BEGIN
	declare tmp_iconCls varchar(20) default 'icon-user';
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
	set tmp_iconCls = CASE new.sex WHEN 'f' THEN 'icon-user-f' WHEN 'm' THEN 'icon-user-m' END;

	INSERT INTO tags (id, l1, l2, l3, l4, `type`, iconCls) VALUES (new.tag_id, new.l1, new.l2, new.l3, new.l4, 1, tmp_iconCls)
		on duplicate key update id = last_insert_id(new.tag_id), l1 = new.l1, l2 = new.l2, l3 = new.l3, l4 = new.l4, `type` = 1, iconCls = tmp_iconCls;
	SET new.tag_id = LAST_INSERT_ID();
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;