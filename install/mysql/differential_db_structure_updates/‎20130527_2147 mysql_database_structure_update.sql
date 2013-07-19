/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*  Alter Function in target  */

DELIMITER $$
DROP FUNCTION IF EXISTS `f_get_tree_inherit_ids`$$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tree_inherit_ids`(in_id bigint unsigned) RETURNS text CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Returns element ids path from the tree which inherit acl from parents'
BEGIN
	DECLARE tmp_pid BIGINT UNSIGNED DEFAULT NULL;
	DECLARE tmp_acl_count INT UNSIGNED DEFAULT 0;
	DECLARE tmp_inherit BOOL DEFAULT NULL;
	DECLARE rez text CHARSET utf8 DEFAULT '';


	SELECT pid, inherit_acl, acl_count INTO tmp_pid, tmp_inherit, tmp_acl_count FROM tree WHERE id = in_id;
	IF( tmp_acl_count > 0 ) THEN
		SET rez = CONCAT('/', in_id);
	END IF;
	WHILE( (tmp_pid IS NOT NULL) AND (tmp_inherit = 1) and ( INSTR(concat(rez, '/'), concat('/', tmp_pid, '/') ) = 0) ) DO
		SET in_id = tmp_pid;
		SELECT pid, inherit_acl, acl_count INTO tmp_pid, tmp_inherit, tmp_acl_count FROM tree WHERE id = in_id;
		IF( tmp_acl_count > 0 ) THEN
			SET rez = CONCAT('/', in_id, rez);
		END IF;
	END WHILE;
	RETURN rez;
    END$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_acl_ad`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_acl_ad` AFTER DELETE ON `tree_acl`
    FOR EACH ROW BEGIN
	UPDATE tree SET updated = (updated | 10), acl_count = acl_count -1 WHERE id = old.node_id;
	CALL p_mark_all_childs_as_updated(old.node_id, 10);
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
	UPDATE tree SET updated = (10 | updated), acl_count = acl_count + 1 WHERE id = new.node_id;
	CALL p_mark_all_childs_as_updated(new.node_id, 10);
    END;
$$
DELIMITER ;

CALL `p_update_tree_acl_count`();
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;