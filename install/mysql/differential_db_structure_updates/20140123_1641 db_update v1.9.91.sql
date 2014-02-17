/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Alter Trigger in target */

DELIMITER $$
/*!50003 DROP TRIGGER *//*!50032 IF EXISTS*//*!50003 `tasks_ai`*/$$
CREATE
    /*!50017 DEFINER = 'local'@'localhost' */
    TRIGGER `tasks_ai` AFTER INSERT ON `tasks`
    FOR EACH ROW BEGIN
	insert into tasks_responsible_users (task_id, user_id)
		select new.id, id
		from users_groups
		where concat(',',new.responsible_user_ids,',') like concat('%,',id,',%');
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
	delete from tasks_responsible_users
	where task_id = old.id and concat(',', new.responsible_user_ids, ',') not like concat('%,',user_id,',%');
	INSERT INTO tasks_responsible_users (task_id, user_id)
		SELECT new.id, u.id
		FROM users_groups u
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',u.id,',%')
		on duplicate key update user_id = u.id;
    END;
$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;