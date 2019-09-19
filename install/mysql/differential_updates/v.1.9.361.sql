/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_template_structure_levels`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DECLARE `tmp_level` INT DEFAULT 0;

	CREATE TABLE IF NOT EXISTS tmp_level_id (`id` INT(11) UNSIGNED NOT NULL, PRIMARY KEY (`id`));
	CREATE TABLE IF NOT EXISTS tmp_level_pid (`id` INT(11) UNSIGNED NOT NULL, PRIMARY KEY (`id`));

	INSERT INTO tmp_level_id
	  SELECT ts1.id
	  FROM templates_structure ts1
	  LEFT JOIN templates_structure ts2 ON ts1.pid = ts2.id
	  WHERE ts2.id IS NULL;

	WHILE (ROW_COUNT() > 0) DO
	  UPDATE templates_structure, tmp_level_id
	  SET templates_structure.`level` = tmp_level
	  WHERE templates_structure.id = tmp_level_id.id;

	  DELETE FROM tmp_level_pid;

	  INSERT INTO tmp_level_pid
		SELECT id FROM tmp_level_id;

	  DELETE FROM tmp_level_id;
	  INSERT INTO tmp_level_id
	    SELECT ts1.id
	    FROM templates_structure ts1
	    JOIN tmp_level_pid ts2 ON ts1.pid = ts2.id;

	  SET tmp_level = tmp_level + 1;
	END WHILE;

	DROP TABLE tmp_level_id;
	DROP TABLE tmp_level_pid;

    END$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `templates_structure_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `templates_structure_bi` BEFORE INSERT ON `templates_structure`
    FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	if (new.id = new.pid) then
		set msg = concat('Error: cyclic reference in templates_structure ', cast(new.id as char));
		signal sqlstate '45000' set message_text = msg;
	end if;
	/* end of trivial check for cycles */
	if(NEW.PID is not null) THEN
		SET NEW.LEVEL = COALESCE((SELECT `level` + 1 FROM templates_structure WHERE id = NEW.PID), 0);
	END IF;
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;