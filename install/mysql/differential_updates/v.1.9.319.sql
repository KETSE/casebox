/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tasks_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tasks_ai` AFTER INSERT ON `tasks`
    FOR EACH ROW BEGIN
 	INSERT INTO tasks_responsible_users (task_id, user_id)
		SELECT new.id, id
		FROM users_groups
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',id,',%');
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tasks_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tasks_au` AFTER UPDATE ON `tasks`
    FOR EACH ROW BEGIN
	DELETE FROM tasks_responsible_users
	WHERE task_id = old.id AND CONCAT(',', new.responsible_user_ids, ',') NOT LIKE CONCAT('%,',user_id,',%');
	INSERT INTO tasks_responsible_users (task_id, user_id)
		SELECT new.id, u.id
		FROM users_groups u
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',u.id,',%')
		ON DUPLICATE KEY UPDATE user_id = u.id;
    END;
$$
DELIMITER ;

/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_mark_all_childs_as_active`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_active`(in_id bigint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids2(id BIGINT UNSIGNED);
	delete from tmp_achild_ids;
	DELETE FROM tmp_achild_ids2;
	insert into tmp_achild_ids select id from tree where pid = in_id;
	while(ROW_COUNT() > 0)do
		update tree, tmp_achild_ids
		  set tree.did = NULL
		  ,tree.ddate = NULL
		  ,tree.dstatus = 0
		  , tree.updated = 1
		where tmp_achild_ids.id = tree.id;

		DELETE FROM tmp_achild_ids2;
		insert into tmp_achild_ids2 select id from tmp_achild_ids;
		delete from tmp_achild_ids;
		INSERT INTO tmp_achild_ids SELECT t.id FROM tree t join tmp_achild_ids2 c on t.pid = c.id;
	END WHILE;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_mark_all_childs_as_deleted`$$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_deleted`(in_id bigint unsigned, in_did int unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids2(id BIGINT UNSIGNED);
	delete from tmp_dchild_ids;
	DELETE FROM tmp_dchild_ids2;
	insert into tmp_dchild_ids select id from tree where pid = in_id;
	while(ROW_COUNT() > 0)do
		update tree, tmp_dchild_ids
		    set tree.did = in_did
			,tree.ddate = CURRENT_TIMESTAMP
			,tree.dstatus = 2
			,tree.updated = 1
		    where tmp_dchild_ids.id = tree.id;

		DELETE FROM tmp_dchild_ids2;
		insert into tmp_dchild_ids2 select id from tmp_dchild_ids;
		delete from tmp_dchild_ids;
		INSERT INTO tmp_dchild_ids SELECT t.id FROM tree t join tmp_dchild_ids2 c on t.pid = c.id;
	END WHILE;
    END$$
DELIMITER ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;