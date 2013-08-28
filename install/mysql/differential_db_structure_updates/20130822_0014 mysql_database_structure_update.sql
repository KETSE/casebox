/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

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
		-- if( coalesce(old.pid, 0) <> coalesce(new.pid, 0) ) THEN
			SELECT
				-- TRIM( ',' FROM CONCAT( ti.pids, ',', new.id) )
				ti.pids
				-- ,sfm_adjust_path( CONCAT( ti.path, new.name ), '/' )
				,ti.path
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
		/*else
			set tmp_new_pids = tmp_old_pids;
			SET tmp_new_path = tmp_old_path;
			SET tmp_new_case_id = tmp_old_case_id;
			SET tmp_new_security_set_id = tmp_old_security_set_id;
			SET tmp_new_security_set = tmp_old_security_set;
		end if;/**/


		/* detect security set for the node */
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
			END IF;
		END IF;
		/* end of detect security set for the node */
--		SET tmp_old_pids = TRIM( ',' FROM CONCAT(tmp_old_pids, ',', old.id) );
--		SET tmp_old_path =  sfm_adjust_path( CONCAT( tmp_old_path, old.name ), '/' );
		SET tmp_new_pids = TRIM( ',' FROM CONCAT(tmp_new_pids, ',', new.id) );
--		SET tmp_new_path = sfm_adjust_path( CONCAT( tmp_new_path, new.name), '/' );

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
				,tree_info.security_set_id =
					CASE
					WHEN ( coalesce(tree_info.security_set_id, 0) = coalesce(tmp_old_security_set_id, 0) )
						THEN tmp_new_security_set_id
					ELSE tree_info.security_set_id
					END
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
		WHERE tmp_tree_info_security_sets.id <> tmp_new_security_set_id
			AND tmp_tree_info_security_sets.id = tree_acl_security_sets.id;

		/* try to delete old security set if no dependances */
		if(tmp_old_security_set_id <> tmp_new_security_set_id) THEN
			if( (select count(*) from tree_info where security_set_id = tmp_old_security_set_id) = 0) THEN
				delete from `tree_acl_security_sets` where id = tmp_old_security_set_id;
			END IF;
		END IF;
	END IF;
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;