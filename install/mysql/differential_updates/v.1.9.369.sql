/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `tree`
	DROP FOREIGN KEY `tree_pid`  ,
	DROP FOREIGN KEY `tree_template_id`  ;


/* Alter table in target */
ALTER TABLE `tree`
	ADD COLUMN `draft` tinyint(1) unsigned   NOT NULL DEFAULT 0 after `type` ,
	CHANGE `template_id` `template_id` int(10) unsigned   NULL after `draft` ,
	DROP COLUMN `subtype` ,
	ADD KEY `tree_draft`(`draft`) ;
/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_child_drafts_as_active`(in_id bigint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids2(id BIGINT UNSIGNED);

	delete from tmp_achild_ids;
	DELETE FROM tmp_achild_ids2;
	insert into tmp_achild_ids
		select id
		from tree
		where pid = in_id and draft = 1;

	while(ROW_COUNT() > 0)do
		update tree, tmp_achild_ids
		  set 	tree.draft = 0
			,tree.updated = 1
		where tmp_achild_ids.id = tree.id;

		DELETE FROM tmp_achild_ids2;

		insert into tmp_achild_ids2
			select id
			from tmp_achild_ids;
		delete from tmp_achild_ids;

		INSERT INTO tmp_achild_ids
			SELECT t.id
			FROM tree t
			join tmp_achild_ids2 c
			  on t.pid = c.id and t.draft = 1;
	END WHILE;
    END$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `tree`
	ADD CONSTRAINT `tree_pid`
	FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `tree_template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;