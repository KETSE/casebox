/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tree_bi`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tree_bi` BEFORE INSERT ON `tree`
    FOR EACH ROW BEGIN
	declare msg varchar(255);
	if (new.id = new.pid) then
		set msg = concat('Error inserting cyclic reference: ', cast(new.id as char));
		signal sqlstate '45000' set message_text = msg;
	-- else
		-- SET new.udate = CURRENT_TIMESTAMP;
	end if;
	set new.oid = coalesce(new.oid, new.cid);
    END;
$$
DELIMITER ;

ALTER TABLE `tasks` 
	DROP FOREIGN KEY `FK_tasks__case_id`  , 
	DROP FOREIGN KEY `FK_tasks__cid`  , 
	DROP FOREIGN KEY `FK_tasks__object_id`  , 
	DROP FOREIGN KEY `FK_tasks__uid`  ;


/* Alter table in target */
ALTER TABLE `cases` 
	CHANGE `date` `date` date   NULL after `close_date` ;


/* Alter table in target */
ALTER TABLE `tasks` 
	CHANGE `completed` `completed` timestamp   NULL COMMENT 'completed date (will be set automaticly, when all responsible users mark task as completed or the owner can close the task manually )' after `missed` ;


/* Alter table in target */
ALTER TABLE `users_groups` 
	ADD UNIQUE KEY `IDX_type__name`(`name`,`type`) , 
	DROP KEY `IDX_username` ;

/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_case_card_titles_for_cases`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Update case card name field'
BEGIN
	INSERT INTO objects_data (object_id, field_id, duplicate_id, VALUE)
	
	SELECT o.id, s.id, 0, c.name FROM cases c
	JOIN objects o ON c.id = o.id JOIN templates_structure s ON o.`template_id` = s.`template_id` AND s.`name` = '_title'
	
	ON DUPLICATE KEY UPDATE `value` = c.name;
    END$$
DELIMITER ;

 

/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tasks` 
	ADD CONSTRAINT `FK_tasks__case_id` 
	FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE , 
	ADD CONSTRAINT `FK_tasks__cid` 
	FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE , 
	ADD CONSTRAINT `FK_tasks__object_id` 
	FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE , 
	ADD CONSTRAINT `FK_tasks__uid` 
	FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;