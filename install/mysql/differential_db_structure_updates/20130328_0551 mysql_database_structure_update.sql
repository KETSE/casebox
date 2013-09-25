/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;


/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `templates_structure`
	DROP FOREIGN KEY `FK_templates_structure__pid`  ,
	DROP FOREIGN KEY `FK_templates_structure__template_id`  ;


/* Create table in target */
CREATE TABLE `objects_tree_tags`(
	`object_id` BIGINT(20) UNSIGNED NOT NULL  ,
	`tag_object_id` BIGINT(20) UNSIGNED NOT NULL  ,
	PRIMARY KEY (`object_id`,`tag_object_id`) ,
	KEY `FK_objects_tree_tags_tag_object_id`(`tag_object_id`) ,
	CONSTRAINT `FK_objects_tree_tags_tag_object_id`
	FOREIGN KEY (`tag_object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	CONSTRAINT `FK_objects_tree_tags__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET='utf8';


/* Alter table in target */
ALTER TABLE `templates_structure`
	CHANGE `use_as_tags` `use_as_tags` TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 - tags from \"tags\" table, 2 - tags from \"tree\" table' AFTER `cfg` ;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_data_ad`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_data_ad` AFTER DELETE ON `objects_data`
    FOR EACH ROW BEGIN
	DECLARE tmp_use_as_tags BOOL DEFAULT NULL;
	DECLARE i, tmp_id INT UNSIGNED;

	SELECT use_as_tags INTO tmp_use_as_tags FROM templates_structure WHERE id = old.field_id;
	IF(tmp_use_as_tags = 1) THEN
		DELETE FROM objects_tags  WHERE object_id = old.object_id AND `level` = 3 AND CONCAT(',', old.value, ',') LIKE CONCAT('%,', tag_id, ',%');
	END IF;
	IF(tmp_use_as_tags = 2) THEN
		DELETE FROM objects_tree_tags  WHERE object_id = old.object_id AND CONCAT(',', old.value, ',') LIKE CONCAT('%,', tag_object_id, ',%');
	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_data_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_data_ai` AFTER INSERT ON `objects_data`
    FOR EACH ROW BEGIN
	DECLARE tmp_use_as_tags BOOL DEFAULT NULL;
	DECLARE i, tmp_id INT UNSIGNED;
/*	SELECT use_as_tags INTO tmp_use_as_tags FROM templates_structure WHERE id = new.field_id;
	IF(tmp_use_as_tags = 1) THEN
		INSERT INTO objects_tags (object_id, tag_id, `level`) SELECT new.object_id, id, 3 FROM tags WHERE CONCAT(',', new.value, ',') LIKE CONCAT('%,', id, ',%')
		on duplicate key update `level` = 3;
	END IF;/**/

	SELECT use_as_tags INTO tmp_use_as_tags FROM templates_structure WHERE id = new.field_id;
	IF(tmp_use_as_tags = 1) THEN
		SET i = 1;
		SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		WHILE(tmp_id > 0) DO
			INSERT INTO objects_tags (object_id, tag_id, `level`) VALUES(new.object_id, tmp_id, 3) ON DUPLICATE KEY UPDATE `level` = 3;
			SET i = i +1;
			SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		END WHILE;
	END IF;
	IF(tmp_use_as_tags = 2) THEN
		SET i = 1;
		SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		WHILE(tmp_id > 0) DO
			INSERT INTO objects_tree_tags (object_id, tag_object_id) VALUES(new.object_id, tmp_id) ON DUPLICATE KEY UPDATE tag_object_id = tmp_id;
			SET i = i +1;
			SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		END WHILE;
	END IF;
    END;
$$
DELIMITER ;


/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `objects_data_au`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `objects_data_au` AFTER UPDATE ON `objects_data`
    FOR EACH ROW BEGIN
	DECLARE tmp_use_as_tags BOOL DEFAULT NULL;
	DECLARE i, tmp_id INT UNSIGNED;

	SELECT use_as_tags INTO tmp_use_as_tags FROM templates_structure WHERE id = new.field_id;
	IF(tmp_use_as_tags = 1) THEN
		DELETE FROM objects_tags  WHERE object_id = old.object_id AND `level` = 3 AND CONCAT(',', old.value, ',') LIKE CONCAT('%,', tag_id, ',%');
		SET i = 1;
		SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		WHILE(tmp_id > 0) DO
			INSERT INTO objects_tags (object_id, tag_id, `level`) VALUES(new.object_id, tmp_id, 3) ON DUPLICATE KEY UPDATE `level` = 3;
			SET i = i +1;
			SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		END WHILE;
	END IF;
	IF(tmp_use_as_tags = 2) THEN
		DELETE FROM objects_tree_tags  WHERE object_id = old.object_id AND CONCAT(',', old.value, ',') LIKE CONCAT('%,', tag_object_id, ',%');
		SET i = 1;
		SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		WHILE(tmp_id > 0) DO
			INSERT INTO objects_tree_tags (object_id, tag_object_id) VALUES(new.object_id, tmp_id) ON DUPLICATE KEY UPDATE tag_object_id = tmp_id;
			SET i = i +1;
			SET tmp_id = CAST(sfm_get_path_element(new.value, ',', i) AS UNSIGNED);
		END WHILE;
	END IF;
    END;
$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `templates_structure`
	ADD CONSTRAINT `FK_templates_structure__pid`
	FOREIGN KEY (`pid`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_templates_structure__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;