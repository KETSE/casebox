<?php
namespace DisplayColumns;

use CB\DB as DB;

class Instance extends CB\Plugin
{
    public function install()
    {
        //create db table if not exists
        DB\dbQuery(
            'CREATE TABLE IF NOT EXISTS `tree_user_config` (
            `id` BIGINT(20) UNSIGNED NOT NULL,
            `user_id` INT(10) UNSIGNED NOT NULL,
            `cfg` TEXT,
            PRIMARY KEY (`id`,`user_id`),
            KEY `tree_user_config__user_id` (`user_id`),
            CONSTRAINT `tree_user_config__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `tree_user_config__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=INNODB DEFAULT CHARSET=utf8'
        ) or die(DB\dbQueryError());

    }

    public function init()
    {
    }
}
