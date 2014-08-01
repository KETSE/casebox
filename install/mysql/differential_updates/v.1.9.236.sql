/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `sfm_adjust_path`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `sfm_adjust_path`(path VARCHAR(500), in_delimiter VARCHAR(50)) RETURNS varchar(500) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'adds slashes to the begin and end of the path'
BEGIN
	DECLARE tmp_delim_len SMALLINT;
	SET tmp_delim_len = CHAR_LENGTH(in_delimiter);
	IF(path IS NULL) THEN SET path = ''; END IF;
	IF(LEFT (path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(in_delimiter, path); END IF;
	IF(RIGHT(path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(path, in_delimiter); END IF;
	RETURN path;
    END$$
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
		SET tmp_old_path_length = CHAR_LENGTH( tmp_old_path ) +1;
		SET tmp_old_security_set_length = LENGTH( tmp_old_security_set ) +1;
		-- update node info with new data
		UPDATE tree_info
		SET	pids = tmp_new_pids
			,path = tmp_new_path
			,case_id = tmp_new_case_id
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.id;
		/* prepare new path, for name changes, to be updated in childs */
		set tmp_old_path = sfm_adjust_path(CONCAT(tmp_old_path, old.name), '/');
		SET tmp_new_path = sfm_adjust_path(CONCAT(tmp_new_path, new.name), '/');
		SET tmp_old_path_length = CHAR_LENGTH( tmp_old_path ) +1;
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

UPDATE tree_info SET path = f_get_tree_path(id);

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
