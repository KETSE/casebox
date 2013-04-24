/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Drop in Second database */
DROP TABLE `cases_tags`;


/* Drop in Second database */
DROP TABLE `files_tags`;


/* Drop in Second database */
DROP TABLE `languages`;


/* Drop in Second database */
DROP TABLE `objects_tags`;


/* Drop in Second database */
DROP TABLE `objects_tree_tags`;


/* Drop in Second database */
DROP TABLE `tag_groups`;


/* Drop in Second database */
DROP TABLE `tag_groups__tags`;


/* Drop in Second database */
DROP TABLE `tag_groups__tags_result`;


/* Drop in Second database */
DROP TABLE `templates_per_tags`;


/* Alter table in target */
ALTER TABLE `users_groups`
	DROP FOREIGN KEY `FK_users_groups__language_id`  ;

/*  Drop Procedure in target  */

 DROP PROCEDURE `p_get_tags_group_tags`;

/*  Drop Procedure in target  */

 DROP PROCEDURE `p_update_tag_group_tags`;

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


/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_tags_ad` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_tags_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`cases_tags_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_data_ad` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_data_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`objects_data_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`tag_groups__tags_ad` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`tag_groups__tags_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`tag_groups__tags_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`tag_groups_bd` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`translations_ad` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`translations_ai` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`translations_au` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`users_groups_association_bi` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`users_groups_association_bu` */;

/* Drop trigger in target */

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS*//*!50003`users_groups_au` */;

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
	INSERT INTO tags (l1, l2, l3, l4, `type`, iconCls) VALUES (new.l1, new.l2, new.l3, new.l4, 1, CASE new.sex WHEN 'f' THEN 'icon-user-f' WHEN 'm' THEN 'icon-user-m' ELSE 'icon-user' END);
	set new.tag_id = last_insert_id();
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