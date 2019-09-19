/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/* Foreign Keys must be dropped in the target to ensure that requires changes can be done*/

ALTER TABLE `action_log`
	DROP FOREIGN KEY `FK_action_log__object_id`  ,
	DROP FOREIGN KEY `FK_action_log__object_pid`  ,
	DROP FOREIGN KEY `FK_action_log__user_id`  ;

ALTER TABLE `files`
	DROP FOREIGN KEY `FK_files__content_id`  ,
	DROP FOREIGN KEY `FK_files__id`  ;

ALTER TABLE `templates_structure`
	DROP FOREIGN KEY `FK_templates_structure__template_id`  ;


/* Alter table in target */
ALTER TABLE `action_log`
	CHANGE `action_type` `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','rename','reopen','status_change','overdue','comment','comment_update','move','password_change','permissions','user_delete','user_create','login','login_fail')  COLLATE utf8_general_ci NOT NULL after `user_id` ,
	ADD COLUMN `activity_data_db` mediumtext  COLLATE utf8_general_ci NULL after `data` ,
	ADD COLUMN `activity_data_solr` mediumtext  COLLATE utf8_general_ci NULL after `activity_data_db` ,
	ADD KEY `IDX_action_time`(`action_time`) ;

/* Alter table in target */
ALTER TABLE `files`
	CHANGE `udate` `udate` timestamp   NULL after `cdate` ;

/* Alter table in target */
ALTER TABLE `notifications`
	CHANGE `id` `id` bigint(20) unsigned   NOT NULL auto_increment first ,
	ADD COLUMN `action_id` bigint(20) unsigned   NOT NULL after `id` ,
	CHANGE `user_id` `user_id` int(10) unsigned   NOT NULL after `action_id` ,
	ADD COLUMN `email_sent` tinyint(1)   NOT NULL DEFAULT 0 after `user_id` ,
	ADD COLUMN `read` tinyint(1)   NOT NULL DEFAULT 0 COMMENT 'notification has been read in CB' after `email_sent` ,
	DROP COLUMN `action_type` ,
	DROP COLUMN `action_time` ,
	DROP COLUMN `sent` ,
	DROP COLUMN `data` ,
	DROP COLUMN `object_id` ,
	DROP COLUMN `object_pid` ,
	DROP COLUMN `viewed` ,
	ADD KEY `FK_notifications__action_id`(`action_id`) ,
	DROP KEY `FK_notifications__case_id` ,
	DROP KEY `FK_notifications__object_id` ,
	DROP KEY `FK_notifications__sent` ,
	DROP KEY `FK_notifications__user_id` ,
	DROP KEY `FK_notifications__viewed` ,
	ADD KEY `FK_notifications_user_id`(`user_id`) ,
	DROP KEY `UNQ_notifications__action_type__object_id__user_id` ,
	DROP FOREIGN KEY `FK_notifications__object_id`  ,
	DROP FOREIGN KEY `FK_notifications__user_id`  ;
ALTER TABLE `notifications`
	ADD CONSTRAINT `FK_notifications__action_id`
	FOREIGN KEY (`action_id`) REFERENCES `action_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_notifications_user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;


/* Alter table in target */
ALTER TABLE `templates_structure`
	CHANGE `name` `name` varchar(1000)  COLLATE utf8_general_ci NOT NULL after `level` ,
	CHANGE `l1` `l1` varchar(1000)  COLLATE utf8_general_ci NULL after `name` ,
	CHANGE `l2` `l2` varchar(1000)  COLLATE utf8_general_ci NULL after `l1` ,
	CHANGE `l3` `l3` varchar(1000)  COLLATE utf8_general_ci NULL after `l2` ,
	CHANGE `l4` `l4` varchar(1000)  COLLATE utf8_general_ci NULL after `l3` ;
/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_delete_template_field_with_data`$$
CREATE PROCEDURE `p_delete_template_field_with_data`(in_field_id bigint unsigned)
    MODIFIES SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	delete from objects where id = in_field_id;
	DELETE FROM tree WHERE id = in_field_id;
	delete from templates_structure where id = in_field_id;
    END$$
DELIMITER ;


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_mark_all_child_drafts_as_active`$$
CREATE PROCEDURE `p_mark_all_child_drafts_as_active`(in_id bigint unsigned)
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


/*  Alter Procedure in target  */

DELIMITER $$
DROP PROCEDURE IF EXISTS `p_update_child_security_sets`$$
CREATE PROCEDURE `p_update_child_security_sets`(
	in_node_id bigint unsigned
	,in_from_security_set_id bigint unsigned
	,in_to_security_set_id bigint unsigned
     )
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_from_security_set, msg
		,tmp_to_security_set varchar(9999);
	DECLARE tmp_security_set_length INT UNSIGNED DEFAULT 0;
	-- get from set
	select `set`
	into tmp_from_security_set
	from `tree_acl_security_sets`
	where id = in_from_security_set_id;
	-- get to set
	SELECT `set`
	INTO tmp_to_security_set
	FROM `tree_acl_security_sets`
	WHERE id = in_to_security_set_id;
	-- set from set length
	SET tmp_security_set_length = LENGTH( tmp_from_security_set ) +1;
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_pids`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_childs`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);
	CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_update_child_sets_security_sets`(
		`id` BIGINT UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
	);
	DELETE FROM tmp_update_child_sets_pids;
	DELETE FROM tmp_update_child_sets_childs;
	DELETE FROM tmp_update_child_sets_security_sets;
	INSERT INTO tmp_update_child_sets_childs (id)
	values(in_node_id);
	WHILE( ROW_COUNT() > 0 )DO
		-- update empty security sets for childs to parent security set
		update tmp_update_child_sets_childs
			,tree_info
		set tree_info.security_set_id = in_to_security_set_id
		where tmp_update_child_sets_childs.id = tree_info.id
			and (	tree_info.security_set_id is null
				OR
				tree_info.security_set_id = in_from_security_set_id
			);
		DELETE FROM tmp_update_child_sets_pids;
		INSERT INTO tmp_update_child_sets_pids
			SELECT id
			FROM tmp_update_child_sets_childs;
		INSERT INTO tmp_update_child_sets_security_sets
			SELECT DISTINCT ti.security_set_id
			FROM tmp_update_child_sets_childs c
			JOIN tree_info ti
				ON c.id = ti.id
				and ti.security_set_id is not null
		ON DUPLICATE KEY UPDATE id = ti.security_set_id;
		DELETE FROM tmp_update_child_sets_childs;
		INSERT INTO tmp_update_child_sets_childs (id)
			SELECT t.id
			FROM tmp_update_child_sets_pids  ti
			JOIN tree t
				ON ti.id = t.pid
				and t.inherit_acl = 1;
	END WHILE;
	-- remove destination security_set from possible updated sets
	delete
	from tmp_update_child_sets_security_sets
	where id = in_to_security_set_id;
	/* update old child sequrity sets to new ones */
	UPDATE tmp_update_child_sets_security_sets
		,tree_acl_security_sets
		SET tree_acl_security_sets.`set` = CONCAT(
			tmp_to_security_set
			,CASE WHEN tmp_security_set_length IS NULL
			THEN
			  CONCAT(',', tree_acl_security_sets.set)
			ELSE
			 SUBSTRING(tree_acl_security_sets.set, tmp_security_set_length)
			END
		)
		,`tree_acl_security_sets`.updated = 1
	WHERE tmp_update_child_sets_security_sets.id = tree_acl_security_sets.id;
    END$$
DELIMITER ;



/* The foreign keys that were dropped are now re-created*/

ALTER TABLE `action_log`
	ADD CONSTRAINT `FK_action_log__object_id`
	FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_action_log__object_pid`
	FOREIGN KEY (`object_pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_action_log__user_id`
	FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `files`
	ADD CONSTRAINT `FK_files__content_id`
	FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ,
	ADD CONSTRAINT `FK_files__id`
	FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE `templates_structure`
	ADD CONSTRAINT `FK_templates_structure__template_id`
	FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;