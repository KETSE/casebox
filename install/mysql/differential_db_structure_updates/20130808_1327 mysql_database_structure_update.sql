/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter table in target */
ALTER TABLE `tree_acl_security_sets`
	ADD KEY `IDX_tree_acl_security_sets__set`(`set`(100)) ;

/* Alter table in target */
ALTER TABLE `tree_info`
	ADD KEY `tree_info__updated`(`updated`) ;
/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_mark_all_childs_as_updated`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_updated`(in_id bigint unsigned, in_update_bits tinyint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	/*CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids2(id BIGINT UNSIGNED);
	delete from tmp_child_ids;
	DELETE FROM tmp_child_ids2;
	insert into tmp_child_ids select id from tree where pid = in_id and dstatus = 0;
	while(ROW_COUNT() > 0)do
		update tree, tmp_child_ids
			set tree.updated = (tree.updated | in_update_bits)
				, tree.case_id = cASE when (tree.updated && 100)= 100 THEN `f_get_objects_case_id`(tree.id) ELSE tree.case_id END
			where tmp_child_ids.id = tree.id;
		DELETE FROM tmp_child_ids2;
		insert into tmp_child_ids2 select id from tmp_child_ids;
		delete from tmp_child_ids;
		INSERT INTO tmp_child_ids SELECT t.id FROM tree t join tmp_child_ids2 c on t.pid = c.id and t.dstatus = 0;
	END WHILE;/**/
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_update_child_security_sets`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_child_security_sets`(
	in_node_id bigint unsigned
	,in_from_set varchar(9999)
	,in_to_set varchar(9999)
     )
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_parent_security_set_id BIGINT UNSIGNED;
	DECLARE tmp_security_set_length INT UNSIGNED DEFAULT 0;
	SET tmp_security_set_length = LENGTH( in_from_set ) +1;

	-- get parent security set id for update child nodes without security set
	select security_set_id
	into tmp_parent_security_set_id
	from tree_info
	where id = in_node_id;

	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_pids`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_childs`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_security_sets`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);

	DELETE FROM tmp_update_child_sets_pids;
	DELETE FROM tmp_update_child_sets_childs;
	DELETE FROM tmp_update_child_sets_security_sets;

	INSERT INTO tmp_update_child_sets_childs (id)
		SELECT id
		FROM tree
		WHERE pid = in_node_id;

	WHILE( ROW_COUNT() > 0 )DO
		-- update empty security sets for childs to parent security set
		update tmp_update_child_sets_childs
			,tree_info
		set tree_info.security_set_id = tmp_parent_security_set_id
		where tmp_update_child_sets_childs.id = tree_info.id
			and tree_info.security_set_id is null;


		DELETE FROM tmp_update_child_sets_pids;

		INSERT INTO tmp_update_child_sets_pids
			SELECT id
			FROM tmp_update_child_sets_childs;

		INSERT INTO tmp_update_child_sets_security_sets
			SELECT DISTINCT ti.security_set_id
			FROM tmp_update_child_sets_childs c
			JOIN tree_info ti ON c.id = ti.id
		ON DUPLICATE KEY UPDATE id = ti.security_set_id;

		DELETE FROM tmp_update_child_sets_childs;

		INSERT INTO tmp_update_child_sets_childs (id)
			SELECT t.id
			FROM tmp_update_child_sets_pids  ti
			JOIN tree t ON ti.id = t.pid;
	END WHILE;

	-- remove parent security_set from possible updated sets
	delete from tmp_update_child_sets_security_sets where id = tmp_parent_security_set_id;

	/* update old child sequrity sets to new ones */
	UPDATE tmp_update_child_sets_security_sets
		,tree_acl_security_sets
		SET `set` = CONCAT(in_to_set, SUBSTRING(tree_acl_security_sets.set, tmp_security_set_length))
		,`tree_acl_security_sets`.updated = 1
	WHERE tmp_update_child_sets_security_sets.id = tree_acl_security_sets.id;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_update_tree_info`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_tree_info`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'update tree_info_table. \n	This procedure is a quick solution and is known to work slow on big trees.\n	It''s actually designed just for upgrading from an old casebox database to new security updates format.\n	'
BEGIN
	delete from tree_info;
	delete from `tree_acl_security_sets`;
	ALTER TABLE `tree_acl_security_sets` AUTO_INCREMENT = 1;

	create temporary table tmp_tree_info
	SELECT id
		,REPLACE(TRIM( '/' FROM `f_get_tree_ids_path`(id)), '/', ',') `pids`
		,`f_get_tree_path`(id) `path`
		,`f_get_objects_case_id`(id) `case_id`
		,(SELECT COUNT(*) FROM `tree_acl` WHERE id = t.id) `acl_count`
		,`f_get_security_set_id`(id) `security_set_id`
		,1 `updated`
	FROM tree t;

	INSERT INTO tree_info (
		id
		,pids
		,path
		,case_id
		,acl_count
		,security_set_id
		,updated
	) select * from tmp_tree_info ti
	on duplicate key
	update
		pids = ti.pids
		,path = ti.path
		,case_id =  ti.case_id
		,acl_count = ti.acl_count
		,security_set_id = ti.security_set_id
		,updated = 1;
	drop TEMPORARY TABLE tmp_tree_info;
	ALTER TABLE `tree_acl_security_sets` AUTO_INCREMENT = 1;

    END$$
DELIMITER ;


/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_users_first_and_last_names_from_l1`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	UPDATE users_groups
	SET
		first_name = `sfm_get_path_element`(l1, ' ', 1)
		,last_name = TRIM(
			CONCAT(
				`sfm_get_path_element`(l1, ' ', 2)
				,' '
				,`sfm_get_path_element`(l1, ' ', 3)
			)
		);
    END$$
DELIMITER ;


/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `f_get_security_set_id`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_security_set_id`(in_id bigint unsigned) RETURNS int(10) unsigned
    MODIFIES SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_i
		,tmp_new_security_set_id BIGINT UNSIGNED DEFAULT NULL;

	DECLARE tmp_ids_path
		,tmp_element
		,tmp_security_set VARCHAR(9999) DEFAULT '';

	DECLARE tmp_acl_count INT UNSIGNED DEFAULT 0;

	SET tmp_i = 1;
	set tmp_ids_path = f_get_tree_ids_path(in_id);
	set tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);
	while(tmp_element <> '')DO
		SELECT COUNT(*)
		into tmp_acl_count
		FROM tree_acl
		WHERE node_id = tmp_element;
		if(tmp_acl_count > 0)THEN
			set tmp_security_set = trim(',' FROM concat(tmp_security_set, ',', tmp_element));
		end if;
		set tmp_i = tmp_i + 1;
		SET tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);
	END WHILE;
	if(tmp_security_set <> '') THEN
		insert into `tree_acl_security_sets` (`set`)
		values(tmp_security_set) on duplicate key update id = last_insert_id(id);
		return last_insert_id();
	END IF;
	return null;
    END$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_ai` AFTER INSERT ON `objects`
    FOR EACH ROW BEGIN

	UPDATE tree
	SET `name` = COALESCE(new.custom_title, new.title)
		,`date` = COALESCE(new.date_start, new.cdate)
		,date_end = COALESCE(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
	WHERE id = new.id;

	/* if object is a case template then mark all case childs as update for roles reset */
	/*if(select 1 from templates where id = new.template_id and `type` = 'case') THEN
		call `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;/**/
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
		,`date` = coalesce(new.date_start, new.cdate)
		,date_end = coalesce(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
		WHERE id = new.id;

	/* if object is a case template then mark all case childs as update for roles reset */
	/*IF(SELECT 1 FROM templates WHERE id = new.template_id AND `type` = 'case') THEN
		CALL `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;/**/
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

	DECLARE tmp_acl_count
		,tmp_length INT DEFAULT 0;

	DECLARE tmp_old_security_set_id
		,tmp_new_security_set_id BIGINT UNSIGNED;
	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';

	SELECT ti.acl_count - 1
		,ti.security_set_id
		,ts.set
	INTO tmp_acl_count
		,tmp_old_security_set_id
		,tmp_old_security_set
	FROM tree_info ti
	JOIN `tree_acl_security_sets` ts ON ti.security_set_id = ts.id
	WHERE ti.id = old.node_id;

	/* we have to analize 2 cases when this is not the last deleted security rule and when it's the last one.
	In first case we have to mark as updated only the security set assigned to this node and child sets
	In second case we have to update all lower security sets form that tree branch and delete assigned security set for this node
	*/
	IF(tmp_acl_count > 1) THEN
		UPDATE tree_info
		SET acl_count = tmp_acl_count
		WHERE id = old.node_id;
		-- mark main security set as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE id = tmp_old_security_set_id;

		-- mark child security sets as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE `set` LIKE CONCAT(tmp_old_security_set, ',%');
	ELSE
		/* create new security set*/

		-- get old_security_set length
		set tmp_length = length( SUBSTRING_INDEX( tmp_old_security_set, ',', -1 ) );
		-- get string length for parent pids (without current node)
		set tmp_length = LENGTH( tmp_old_security_set) - tmp_length - 1;

		if(tmp_length < 0) Then
			Set tmp_length = 0;
		END IF;

		SET tmp_new_security_set = substring( tmp_old_security_set, 1,  tmp_length );

		-- next insert can be replaced with a select
		INSERT INTO tree_acl_security_sets (`set`)
		VALUES(tmp_new_security_set)
		ON DUPLICATE KEY
		UPDATE id = LAST_INSERT_ID(id);

		SET tmp_new_security_set_id = LAST_INSERT_ID();

		/* end of create new security set*/

		UPDATE tree_info
		SET acl_count = tmp_acl_count
			,security_set_id = tmp_new_security_set_id
		WHERE id = old.node_id;

		/* now we have to update all child security sets */
		 CALL p_update_child_security_sets(old.node_id, tmp_old_security_set, tmp_new_security_set);

		-- delete old security set
		if(tmp_new_security_set_id <> tmp_old_security_set_id) THEN
			delete from tree_acl_security_sets where id = tmp_old_security_set_id;
		END IF;
	END IF;
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
	declare tmp_acl_count int unsigned default 0;

	DECLARE tmp_security_set_id BIGINT UNSIGNED;
	DECLARE tmp_old_security_set
		,tmp_new_security_set varchar(9999) default '';

	select ti.acl_count + 1
		,ti.security_set_id
		,coalesce( ts.set, '')
	into tmp_acl_count
		,tmp_security_set_id
		,tmp_old_security_set
	from tree_info ti
	left join `tree_acl_security_sets` ts on ti.security_set_id = ts.id
	where ti.id = new.node_id;

	/* we have to analize 2 cases when node has already other security rules attached and when this is the first rule attached.
	In first case we have to mark as updated only the security set assigned to this node and child sets
	In second case we have to add the new security set and update all lower security sets form that tree baranch
	*/
	if(tmp_acl_count > 1) THEN
		UPDATE tree_info
		SET acl_count = tmp_acl_count
		WHERE id = new.node_id;

		-- mark main security set as updated
		update `tree_acl_security_sets`
		set updated = 1
		where id = tmp_security_set_id;

		-- mark child security sets as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE `set` like concat(tmp_old_security_set, ',%');
	ELSE
		/* create new security set*/
		set tmp_new_security_set = trim( ',' from concat(tmp_old_security_set, ',', new.node_id) );

		insert into tree_acl_security_sets (`set`)
		values(tmp_new_security_set)
		on duplicate key
		update id = last_insert_id(id);

		set tmp_security_set_id = last_insert_id();

		/* end of create new security set*/

		UPDATE tree_info
		SET 	acl_count = tmp_acl_count
			,security_set_id = tmp_security_set_id
		WHERE id = new.node_id;

		/* now we have to update all child security sets */
		CALL p_update_child_security_sets(new.node_id, tmp_old_security_set, tmp_new_security_set);
	END IF;
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

	DECLARE tmp_security_set_id BIGINT UNSIGNED;
	DECLARE tmp_security_set VARCHAR(9999) DEFAULT '';
	/* mark security set as updated including all dependent(child) security sets*/
	SELECT ti.security_set_id
		,ts.set
	INTO tmp_security_set_id
		,tmp_security_set
	FROM tree_info ti
	JOIN `tree_acl_security_sets` ts ON ti.security_set_id = ts.id
	WHERE ti.id = new.node_id;

	-- mark main security set as updated
	UPDATE `tree_acl_security_sets`
	SET updated = 1
	WHERE id = tmp_security_set_id;

	-- mark child security sets as updated
	UPDATE `tree_acl_security_sets`
	SET updated = 1
	WHERE `set` LIKE CONCAT(tmp_security_set, ',%');

	-- OLD CODE:
	-- update tree set updated = (updated | 10) where id = new.node_id;
	-- call p_mark_all_childs_as_updated(new.node_id, 10);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_security_sets_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_security_sets_bi` BEFORE INSERT ON `tree_acl_security_sets`
    FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_security_sets_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_security_sets_bu` BEFORE UPDATE ON `tree_acl_security_sets`
    FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_ai` AFTER INSERT ON `tree`
    FOR EACH ROW BEGIN
	/* get pids path, text path, case_id and store them in tree_info table*/
	declare tmp_new_case_id
		,tmp_new_security_set_id bigint unsigned default null;

	DECLARE tmp_new_pids
		,tmp_new_path text DEFAULT '';

	/* check if inserted node is a case */
	if( 	(new.template_id is not null)
		and (select id from templates where (id = new.template_id) and (`type` = 'case') )
	) THEN
		SET tmp_new_case_id = new.id;
	END IF;

	select
		trim( ',' from concat( ti.pids, ',' , t.id))
		,sfm_adjust_path( CONCAT( ti.path, t.name ), '/' )
		,coalesce(tmp_new_case_id, ti.case_id)
		,ti.security_set_id
	into
		tmp_new_pids
		,tmp_new_path
		,tmp_new_case_id
		,tmp_new_security_set_id
	from tree t
	left join tree_info ti on t.id = ti.id
	where t.id = new.pid;

	insert into tree_info (
		id
		,pids
		,path
		,case_id
		,security_set_id
	)
	values (
		new.id
		,tmp_new_pids
		,tmp_new_path
		,tmp_new_case_id
		,tmp_new_security_set_id
	);
	/* end of get pids path, text path, case_id and store them in tree_info table*/
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_au` AFTER UPDATE ON `tree`
    FOR EACH ROW BEGIN

	DECLARE tmp_old_pids
		,tmp_new_pids
		,tmp_old_path
		,tmp_new_path TEXT DEFAULT '';

	DECLARE tmp_old_case_id
		,tmp_new_case_id
		,tmp_old_security_set_id
		,tmp_new_security_set_id BIGINT UNSIGNED DEFAULT NULL;

	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';

	DECLARE tmp_old_pids_length
		,tmp_old_path_length
		,tmp_old_security_set_length
		,tmp_acl_count INT UNSIGNED DEFAULT 0;

	/* get pids path, text path, case_id and store them in tree_info table*/
	IF( (COALESCE(old.pid, 0) <> COALESCE(new.pid, 0) )
	    OR ( COALESCE(old.name, '') <> COALESCE(new.name, '') )
	  )THEN
		-- select old data
		SELECT
			ti.pids
			,ti.path
			,ti.case_id
			,ti.acl_count
			,ti.security_set_id
			,ts.set
		INTO
			tmp_old_pids
			,tmp_old_path
			,tmp_old_case_id
			,tmp_acl_count
			,tmp_old_security_set_id
			,tmp_old_security_set
		FROM tree_info ti
		LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
		WHERE ti.id = new.id;

		/* check if updated node is a case */
		IF(tmp_old_case_id = old.id) THEN
			SET tmp_new_case_id = new.id;
		END IF;

		-- find new data
		SELECT
			TRIM( ',' FROM CONCAT( ti.pids, ',', t.id) )
			,sfm_adjust_path( CONCAT( ti.path, t.name ), '/' )
			,COALESCE(tmp_new_case_id, ti.case_id)
			,ti.security_set_id
			,ts.set

		INTO
			tmp_new_pids
			,tmp_new_path
			,tmp_new_case_id
			,tmp_new_security_set_id
			,tmp_new_security_set
		FROM tree t
		LEFT JOIN tree_info ti ON t.id = ti.id
		LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
		WHERE t.id = new.pid;

		/* detect security set for the node */
		IF(tmp_acl_count > 0) THEN
			-- we need to replace security sets that include updated node id
			SET tmp_new_security_set = TRIM( ',' FROM CONCAT(tmp_new_security_set, ',', new.id ) );

			UPDATE tree_acl_security_sets
			SET `set` = tmp_new_security_set
				,updated = 1
			WHERE id = tmp_old_security_set_id;

			SET tmp_new_security_set_id = tmp_old_security_set_id;

		-- ELSE
			-- we have to rename security sets for all childs without including updated node in the searched sets

		END IF;
		/* end of detect security set for the node */

		-- update node info with new data
		UPDATE tree_info
		SET	pids = tmp_new_pids
			,path = tmp_new_path
			,case_id = tmp_new_case_id
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.id;


		/* now cyclic updating all childs info for this updated object */
		SET tmp_old_pids = TRIM( ',' FROM CONCAT(tmp_old_pids, ',', old.id) );
		SET tmp_old_path =  sfm_adjust_path( CONCAT( tmp_old_path, old.name ), '/' );
		SET tmp_new_pids = TRIM( ',' FROM CONCAT(tmp_new_pids, ',', new.id) );
		SET tmp_new_path = sfm_adjust_path( CONCAT( tmp_new_path, new.name), '/' );

		SET tmp_old_pids_length = LENGTH( tmp_old_pids ) +1;
		SET tmp_old_path_length = LENGTH( tmp_old_path ) +1;
		SET tmp_old_security_set_length = LENGTH( tmp_old_security_set ) +1;

		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_pids`(
			`id` BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (`id`)
		);

		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_childs`(
			`id` BIGINT UNSIGNED NOT NULL,
			PRIMARY KEY (`id`)
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_security_sets`(
			`id` BIGINT UNSIGNED NOT NULL,
			`new_id` BIGINT UNSIGNED NOT NULL,
			`set` VARCHAR(9999),
			PRIMARY KEY (`id`),
			INDEX `IDX_tmp_tree_info_security_sets__set` (`set`),
			INDEX `IDX_tmp_tree_info_security_sets__new_id` (`new_id`)
		);

		DELETE FROM tmp_tree_info_pids;

		DELETE FROM tmp_tree_info_childs;
		DELETE FROM tmp_tree_info_security_sets;

		INSERT INTO tmp_tree_info_childs (id)
			SELECT id
			FROM tree
			WHERE pid = new.id;

		WHILE( ROW_COUNT() > 0 )DO
			UPDATE tmp_tree_info_childs, tree_info
			SET
				tree_info.pids = CONCAT(tmp_new_pids, SUBSTRING(tree_info.pids, tmp_old_pids_length))
				,tree_info.path = CONCAT(tmp_new_path, SUBSTRING(tree_info.path, tmp_old_path_length))
				,tree_info.case_id = CASE WHEN (tree_info.case_id = tmp_old_case_id) THEN tmp_new_case_id ELSE COALESCE(tree_info.case_id, tmp_new_case_id) END
				,tree_info.security_set_id = CASE WHEN (tree_info.security_set_id = tmp_old_security_set_id) THEN tmp_new_security_set_id ELSE tree_info.security_set_id END
			WHERE tmp_tree_info_childs.id = tree_info.id;

			DELETE FROM tmp_tree_info_pids;

			INSERT INTO tmp_tree_info_pids
				SELECT id
				FROM tmp_tree_info_childs;

			INSERT INTO tmp_tree_info_security_sets (id)
				SELECT DISTINCT ti.security_set_id
				FROM tmp_tree_info_childs c
				JOIN tree_info ti ON c.id = ti.id
			ON DUPLICATE KEY UPDATE id = ti.security_set_id;

			DELETE FROM tmp_tree_info_childs;

			INSERT INTO tmp_tree_info_childs (id)
				SELECT t.id
				FROM tmp_tree_info_pids  ti
				JOIN tree t ON ti.id = t.pid;
		END WHILE;

		/* update old sequrity sets to new ones */
		UPDATE tmp_tree_info_security_sets
			,tree_acl_security_sets
			SET tree_acl_security_sets.`set` = CONCAT(tmp_new_security_set, SUBSTRING(tree_acl_security_sets.set, tmp_old_security_set_length))
				,tree_acl_security_sets.updated = 1
		WHERE tmp_tree_info_security_sets.id = tree_acl_security_sets.id;
	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_bi` BEFORE INSERT ON `tree`
    FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	if (new.id = new.pid) then
		set msg = concat('Error inserting cyclic reference: ', cast(new.id as char));
		signal sqlstate '45000' set message_text = msg;
	end if;
	/* end of trivial check for cycles */

	-- set owner id equal to creator id if null given
	set new.oid = coalesce(new.oid, new.cid);
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_info_bu`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_info_bu` BEFORE UPDATE ON `tree_info`
    FOR EACH ROW BEGIN
	if(
		(old.pids <> new.pids)
		OR(old.path <> new.path)
		OR ( coalesce(old.case_id, 0) <> coalesce(new.case_id, 0) )
		OR (old.acl_count <> new.acl_count)
		OR (old.security_set_id <> new.security_set_id)
	)
	THEN
		SET new.updated = 1;
	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_ai` AFTER INSERT ON `users_groups`
    FOR EACH ROW BEGIN
	declare tmp_everyone_user_id int unsigned;

	/* if we inserted a user then mark sequrity sets that contain everyone user as updated */
	IF( new.type = 2 ) THEN
		/* get everyone group id into temporary variable */
		SELECT id
		into tmp_everyone_user_id
		FROM users_groups
		WHERE `type` = 1
			AND `system` = 1
			AND name = 'everyone';

		/* update corresponding security sets */
		update `tree_acl_security_sets`
		set updated = 1
			where id in (
				select distinct security_set_id
				from `tree_acl_security_sets_result`
				where user_id = tmp_everyone_user_id

			)
		;
	END IF;

    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_association_ad`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_association_ad` AFTER DELETE ON `users_groups_association`
    FOR EACH ROW BEGIN
	/* mark sets as updated that contain deleted user */
	UPDATE tree_acl_security_sets
	SET updated = 1
		WHERE id IN (
			SELECT DISTINCT security_set_id
			FROM `tree_acl_security_sets_result`
			WHERE user_id = old.user_id
		)
	;
	/* end of mark sets as updated that contain deleted user */
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `users_groups_association_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `users_groups_association_ai` AFTER INSERT ON `users_groups_association`
    FOR EACH ROW BEGIN


	/* mark sets as updated that depend on this group */
	UPDATE tree_acl_security_sets
	SET updated = 1
		WHERE id IN (
			SELECT DISTINCT ti.security_set_id
			FROM `tree_acl` ta
			JOIN tree_info ti ON ti.`id` = ta.`node_id`
			WHERE ta.`user_group_id` = new.group_id
		)
	;
	/* end of mark sets as updated that depend on this group */
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;