/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_update_child_security_sets`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_child_security_sets`(
	in_node_id bigint unsigned
	,in_from_security_set_id bigint unsigned
	,in_to_security_set_id bigint unsigned
     )
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_from_security_set, msg
		,tmp_to_security_set varchar(9999);

	DECLARE tmp_security_set_length INT UNSIGNED DEFAULT 0;

	-- get from set
	select `set`
	into tmp_from_security_set
	from `tree_acl_security_sets`
	where id = in_from_security_set_id;

	-- get to set
	SELECT `set`
	INTO tmp_to_security_set
	FROM `tree_acl_security_sets`
	WHERE id = in_to_security_set_id;

	-- set from set length
	SET tmp_security_set_length = LENGTH( tmp_from_security_set ) +1;

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
	values(in_node_id);

	WHILE( ROW_COUNT() > 0 )DO
		-- update empty security sets for childs to parent security set
		update tmp_update_child_sets_childs
			,tree_info
		set tree_info.security_set_id = in_to_security_set_id
		where tmp_update_child_sets_childs.id = tree_info.id
			and (	tree_info.security_set_id is null
				OR
				tree_info.security_set_id = in_from_security_set_id
			);


		DELETE FROM tmp_update_child_sets_pids;

		INSERT INTO tmp_update_child_sets_pids
			SELECT id
			FROM tmp_update_child_sets_childs;

		INSERT INTO tmp_update_child_sets_security_sets
			SELECT DISTINCT ti.security_set_id
			FROM tmp_update_child_sets_childs c
			JOIN tree_info ti
				ON c.id = ti.id
				and ti.security_set_id is not null
		ON DUPLICATE KEY UPDATE id = ti.security_set_id;

		DELETE FROM tmp_update_child_sets_childs;

		INSERT INTO tmp_update_child_sets_childs (id)
			SELECT t.id
			FROM tmp_update_child_sets_pids  ti
			JOIN tree t
				ON ti.id = t.pid
				and t.inherit_acl = 1;
	END WHILE;

	-- remove destination security_set from possible updated sets
	delete
	from tmp_update_child_sets_security_sets
	where id = in_to_security_set_id;
--	SET msg = CONCAT('Info: ', in_from_security_set_id, ': ',tmp_from_security_set,'->', in_to_security_set_id, ':', tmp_to_security_set);
--	signal SQLSTATE '45000' SET message_text = msg;

	/* update old child sequrity sets to new ones */
	UPDATE tmp_update_child_sets_security_sets
		,tree_acl_security_sets
		SET tree_acl_security_sets.`set` = CONCAT(
			tmp_to_security_set
			,SUBSTRING(tree_acl_security_sets.set, tmp_security_set_length)
		)
		,`tree_acl_security_sets`.updated = 1
	WHERE tmp_update_child_sets_security_sets.id = tree_acl_security_sets.id;
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
	DECLARE tmp_inherit_acl INT DEFAULT NULL;

	DECLARE tmp_ids_path
		,tmp_element
		,tmp_security_set VARCHAR(9999) DEFAULT '';

	DECLARE tmp_acl_count INT UNSIGNED DEFAULT 0;

	SET tmp_i = 1;
	set tmp_ids_path = f_get_tree_ids_path(in_id);
	set tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);

	while(tmp_element <> '')DO
		select inherit_acl
		into tmp_inherit_acl
		from tree
		where id = tmp_element;
		if(tmp_inherit_acl = 1) THEN
			SELECT COUNT(*)
			into tmp_acl_count
			FROM tree_acl
			WHERE node_id = tmp_element;

			if(tmp_acl_count > 0)THEN
				set tmp_security_set = trim(',' FROM concat(tmp_security_set, ',', tmp_element));
			end if;
		ELSE
			SET tmp_security_set = tmp_element;
		END IF;

		set tmp_i = tmp_i + 1;
		SET tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);
		set tmp_acl_count = 0;
	END WHILE;

	if(tmp_security_set <> '') THEN
		set tmp_i = null;

		select id
		into tmp_i
		from tree_acl_security_sets
		where `md5` = md5(tmp_security_set);

		if(tmp_i is null) then
			insert into `tree_acl_security_sets` (`set`)
			values(tmp_security_set)
			on duplicate key update id = last_insert_id(id);

			set tmp_i = last_insert_id();
		END IF;

		return tmp_i;
	END IF;

	return null;

    END$$
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
		,tmp_new_security_set_id BIGINT UNSIGNED default null;

	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';

	declare tmp_inherit_acl  tinyint(1) default 1;

	/*Note: node should have a security set associated if we are in after delete security rule */

	/* get node data */
	SELECT  case when (ti.acl_count >0)
			THEN ti.acl_count - 1
			ELSE 0
		END
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
	IF(tmp_acl_count > 0) THEN
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

		/* get inheritance status of the node */
		select inherit_acl
		into tmp_inherit_acl
		from tree
		where id = old.node_id;

		/* create new security set or delete it*/

		if(tmp_inherit_acl = 1) THEN
			-- get old_security_set length
			set tmp_length = length( SUBSTRING_INDEX( tmp_old_security_set, ',', -1 ) );
			-- get string length for parent pids (without current node)
			set tmp_length = LENGTH( tmp_old_security_set) - tmp_length - 1;

			if(tmp_length < 0) Then
				Set tmp_length = 0;
			END IF;

			SET tmp_new_security_set = substring( tmp_old_security_set, 1,  tmp_length );

			/* get new security set id/**/
			if(LENGTH(tmp_new_security_set) > 0) THEN
				select id
				into tmp_new_security_set_id
				from tree_acl_security_sets
				where `set` = tmp_new_security_set;
			else
				set tmp_new_security_set_id = null;
			END IF;
		END IF;

		-- update tree_info for processed node
		UPDATE tree_info
		SET acl_count = tmp_acl_count
			,security_set_id = tmp_new_security_set_id
		WHERE id = old.node_id;

		/* now we have to update all child security sets */
		CALL p_update_child_security_sets(old.node_id, tmp_old_security_set_id, tmp_new_security_set_id);

		IF( COALESCE(tmp_new_security_set_id, 0) <> tmp_old_security_set_id) THEN
			DELETE FROM tree_acl_security_sets
			WHERE id = tmp_old_security_set_id;
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

	DECLARE tmp_new_security_set_id
		,tmp_old_security_set_id BIGINT UNSIGNED default null;

	DECLARE tmp_old_security_set, msg
		,tmp_new_security_set varchar(9999) default '';

	select ti.acl_count + 1
		,ti.security_set_id
		,coalesce( ts.set, '')
	into tmp_acl_count
		,tmp_old_security_set_id
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
		where id = tmp_old_security_set_id;

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

		set tmp_new_security_set_id = last_insert_id();

		/* end of create new security set*/

		UPDATE tree_info
		SET 	acl_count = tmp_acl_count
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.node_id;

		/* now we have to update all child security sets */
		CALL p_update_child_security_sets(new.node_id, tmp_old_security_set_id, tmp_new_security_set_id);
	END IF;
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

	if(new.inherit_acl = 0) then
		set tmp_new_security_set_id = f_get_security_set_id(new.id);
	END IF;

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
	    OR ( old.inherit_acl <> new.inherit_acl )
	  )THEN

		-- get old data
		SELECT
			ti.pids -- 1,2,3
			,ti.path -- /Folder1/Folder2
			,ti.case_id -- null
			,ti.acl_count -- 2
			,ti.security_set_id -- 4
			,ts.set -- '1,3'
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

		/* form new data based on new parent
		*/

		if(new.pid is null) THEN
			SET tmp_new_pids = new.id;
			SET tmp_new_path = '/';
			-- tmp_new_case_id already set above
			SET tmp_new_security_set_id = null;
			set tmp_new_security_set = '';
		ELSE
			SELECT
				ti.pids
				,case when t.pid is null
					then ti.path
					else CONCAT( ti.path, t.name )
				END
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

			SET tmp_new_pids = TRIM( ',' FROM CONCAT( tmp_new_pids, ',', new.id) );
			SET tmp_new_path = sfm_adjust_path( tmp_new_path, '/' );
		END IF;
		/* end of form new data based on new parent */

		/* detect new security set for the node */
		IF(tmp_acl_count > 0) THEN
			-- we need to replace security sets that include updated node id
			IF(new.inherit_acl = 0) THEN
				SET tmp_new_security_set = new.id;
			else
				SET tmp_new_security_set = TRIM( ',' FROM CONCAT(tmp_new_security_set, ',', new.id ) );
			END IF;

			UPDATE tree_acl_security_sets
			SET `set` = tmp_new_security_set
				,updated = 1
			WHERE id = tmp_old_security_set_id;

			SET tmp_new_security_set_id = tmp_old_security_set_id;

		ELSE
			-- we have to rename security sets for all childs without including updated node in the searched sets
			IF(new.inherit_acl = 0) THEN
				SET tmp_new_security_set_id = NULL;
				SET tmp_new_security_set = '';
			END IF;
		END IF;
		/* end of detect security set for the node */

		SET tmp_old_pids_length = LENGTH( tmp_old_pids ) +1;
		SET tmp_old_path_length = LENGTH( tmp_old_path ) +1;
		SET tmp_old_security_set_length = LENGTH( tmp_old_security_set ) +1;

		-- update node info with new data
		UPDATE tree_info
		SET	pids = tmp_new_pids
			,path = tmp_new_path
			,case_id = tmp_new_case_id
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.id;


		/* now cyclic updating all childs info for this updated object */

		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_pids`(
			`id` BIGINT UNSIGNED NOT NULL,
			`inherit_acl` TINYINT(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`)
		);

		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_childs`(
			`id` BIGINT UNSIGNED NOT NULL,
			`inherit_acl` TINYINT(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`)
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_security_sets`(
			`id` BIGINT UNSIGNED NOT NULL,
			`new_id` BIGINT UNSIGNED NULL,
			`set` VARCHAR(9999),
			PRIMARY KEY (`id`),
			INDEX `IDX_tmp_tree_info_security_sets__set` (`set`),
			INDEX `IDX_tmp_tree_info_security_sets__new_id` (`new_id`)
		);

		DELETE FROM tmp_tree_info_pids;

		DELETE FROM tmp_tree_info_childs;
		DELETE FROM tmp_tree_info_security_sets;

		INSERT INTO tmp_tree_info_childs (id, inherit_acl)
			SELECT id, inherit_acl
			FROM tree
			WHERE pid = new.id;

		WHILE( ROW_COUNT() > 0 )DO
			UPDATE
				tmp_tree_info_childs
				,tree_info
			SET
				tree_info.pids = CONCAT(tmp_new_pids, SUBSTRING(tree_info.pids, tmp_old_pids_length))
				,tree_info.path = CONCAT(tmp_new_path, SUBSTRING(tree_info.path, tmp_old_path_length))
				,tree_info.case_id = CASE WHEN (tree_info.case_id = tmp_old_case_id) THEN tmp_new_case_id ELSE COALESCE(tree_info.case_id, tmp_new_case_id) END
				,tree_info.security_set_id =
					CASE
					WHEN (tmp_tree_info_childs.inherit_acl = 1)
					     AND ( coalesce(tree_info.security_set_id, 0) = coalesce(tmp_old_security_set_id, 0) )
						THEN tmp_new_security_set_id
					ELSE tree_info.security_set_id
					END
			WHERE tmp_tree_info_childs.id = tree_info.id;

			DELETE FROM tmp_tree_info_pids;

			INSERT INTO tmp_tree_info_pids
				SELECT id, inherit_acl
				FROM tmp_tree_info_childs;

			INSERT INTO tmp_tree_info_security_sets (id)
				SELECT DISTINCT ti.security_set_id
				FROM tmp_tree_info_childs c
				JOIN tree_info ti ON c.id = ti.id
				WHERE ti.security_set_id IS NOT NULL
					and c.inherit_acl = 1
			ON DUPLICATE KEY UPDATE id = ti.security_set_id;

			DELETE FROM tmp_tree_info_childs;

			INSERT INTO tmp_tree_info_childs (id, inherit_acl)
				SELECT
					t.id,
					case when ( (t.inherit_acl = 1) and (ti.inherit_acl = 1) ) then 1 else 0 END
				FROM tmp_tree_info_pids  ti
				JOIN tree t
					ON ti.id = t.pid;
		END WHILE;

		/* update old sequrity sets to new ones */
		UPDATE tmp_tree_info_security_sets
			,tree_acl_security_sets
			SET tree_acl_security_sets.`set` = TRIM( ',' FROM CONCAT(tmp_new_security_set, SUBSTRING(tree_acl_security_sets.set, tmp_old_security_set_length)) )
				,tree_acl_security_sets.updated = 1
		WHERE tmp_tree_info_security_sets.id <> coalesce(tmp_new_security_set_id, 0)
			AND tmp_tree_info_security_sets.id = tree_acl_security_sets.id
			AND tree_acl_security_sets.set LIKE CONCAT(tmp_old_security_set,',%');

		/* try to delete old security set if no dependances */
		if(tmp_old_security_set_id <> coalesce(tmp_new_security_set_id, 0)) THEN
			if( (select count(*) from tree_info where security_set_id = tmp_old_security_set_id) = 0) THEN
				delete from `tree_acl_security_sets` where id = tmp_old_security_set_id;
			END IF;
		END IF;
	END IF;
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;