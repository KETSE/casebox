/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*  Create Function in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_next_autoincrement_id`(in_tablename TINYTEXT) RETURNS INT(11)
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	RETURN (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = in_tablename);
    END$$
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
	IF (new.id = new.pid) THEN
		SET msg = CONCAT('Error inserting cyclic reference: ', CAST(new.id AS CHAR));
		signal SQLSTATE '45000' SET message_text = msg;
	END IF;
	/* trivial check for cycles */

	/* set case_id field */
	IF( (new.template_id IS NOT NULL) AND (SELECT id FROM templates WHERE (id = new.template_id) AND (`type` = 'case') ) ) THEN
		SET new.case_id = f_get_next_autoincrement_id('tree');
	ELSE
		SET new.case_id = `f_get_objects_case_id`(new.pid);
	END IF;
	/* end of set case_id field */
	SET new.oid = COALESCE(new.oid, new.cid);
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;