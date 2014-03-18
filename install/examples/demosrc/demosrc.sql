/*
SQLyog Ultimate v11.3 (64 bit)
MySQL - 5.5.9 : Database - cb_demosrc
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`cb_demosrc` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `cb_demosrc`;

/*Table structure for table `actions_log` */

DROP TABLE IF EXISTS `actions_log`;

CREATE TABLE `actions_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `to_user_ids` varchar(100) DEFAULT NULL,
  `case_id` bigint(20) unsigned DEFAULT NULL,
  `object_id` bigint(20) unsigned DEFAULT NULL,
  `file_id` bigint(20) unsigned DEFAULT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `action_type` smallint(6) unsigned NOT NULL COMMENT '1. Add case\n 2. open case\n 3. close case\n 4. add case object\n 5. update case object\n 6. delete case object\n 7. open case object\n 8. close case object\n 9. add case file\n 10. download case file\n 11. delete case file',
  `remind_users` varchar(100) DEFAULT NULL,
  `result` varchar(50) DEFAULT NULL,
  `info` text,
  `l1` text,
  `l2` text,
  `l4` text,
  `l3` text,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date`),
  KEY `idx_date__action_type` (`date`,`action_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_date__remind_users` (`date`,`remind_users`),
  KEY `FK_actions_log__case_id` (`case_id`),
  KEY `FK_actions_log__object_id` (`object_id`),
  KEY `FK_actions_log__to_user_id` (`to_user_ids`),
  KEY `FK_actions_log__task_id` (`task_id`),
  CONSTRAINT `FK_actions_log__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8;

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `param` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_param` (`param`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`id`,`pid`,`param`,`value`) values (1,NULL,'project_name_en','CaseBox - Demo'),(2,NULL,'project_name_ru','CaseBox - Demo'),(3,NULL,'responsible_party','232'),(4,NULL,'responsible_party_default','345'),(6,NULL,'task_categories','715'),(7,NULL,'templateIcons','\r\nicon-arrow-left-medium\r\nicon-arrow-left-medium-green\r\nicon-arrow-left\r\nicon-arrow-right-medium\r\nicon-arrow-right\r\nicon-case_card\r\nicon-complaint\r\nicon-complaint-subjects\r\nicon-info-action\r\nicon-decision\r\nicon-echr_complaint\r\nicon-echr_decision\r\nicon-petition\r\nicon-balloon\r\nicon-bell\r\nicon-blog-blue\r\nicon-blog-magenta\r\nicon-blue-document-small\r\nicon-committee-phase\r\nicon-document-medium\r\nicon-document-stamp\r\nicon-document-text\r\nicon-mail\r\nicon-object1\r\nicon-object2\r\nicon-object3\r\nicon-object4\r\nicon-object5\r\nicon-object6\r\nicon-object7\r\nicon-object8\r\nicon-zone\r\nicon-applicant\r\nicon-suspect\r\nicon-milestone'),(9,NULL,'folder_templates','24074,24079,24044'),(10,NULL,'default_folder_template','24074'),(11,NULL,'default_file_template','24075'),(12,NULL,'default_task_template','24072'),(13,NULL,'default_event_template','24073'),(14,NULL,'action_templates','24195'),(15,NULL,'default_home_folder_cfg','{\"controller\": \"UsersHomeFolder\"}'),(16,NULL,'default_language','en'),(17,NULL,'languages','en,ru'),(18,NULL,'rootNode','{\r\n\"id\": 0\r\n,\"text\": \"My CaseBox\"\r\n}'),(19,NULL,'object_type_plugins','{\r\n  \"object\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"case\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"task\": [\"objectProperties\", \"files\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"file\": [\"thumb\", \"meta\", \"versions\", \"tasks\", \"comments\", \"systemProperties\"]\r\n}'),(20,NULL,'treeNodes','{\r\n    \"MyCalendar\": {}\r\n    ,\"ManagersCalendar\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\ManagersCalendar\" \r\n    }\r\n    ,\"Tasks\": {}\r\n    ,\"TasksForCase\": {}\r\n    ,\"cases_per_roles\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\CasesGrouped\" \r\n    }\r\n    ,\"task_offices\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\Offices\" \r\n    }\r\n    ,\"task_office_users\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\OfficeUsers\" \r\n    }\r\n    ,\"office_cases\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\OfficeCases\" \r\n    }\r\n    ,\"task_types\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\TaskTypes\" \r\n    }\r\n    ,\"task_statuses\": {\r\n       \"class\": \"osji\\\\TreeNode\\\\TaskStatuses\" \r\n    }\r\n    ,\"RealSubnode\":{\r\n       \"pid\": \"0\"\r\n       ,\"realNodeId\": \"root\"\r\n       ,\"title\": \"All Folders\"\r\n    }\r\n    ,\"Dbnode\":{}\r\n}');

/*Table structure for table `crons` */

DROP TABLE IF EXISTS `crons`;

CREATE TABLE `crons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cron_id` varchar(30) DEFAULT NULL,
  `cron_file` varchar(500) DEFAULT NULL,
  `last_start_time` timestamp NULL DEFAULT NULL,
  `last_end_time` timestamp NULL DEFAULT NULL,
  `execution_info` longtext,
  `last_action` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `crons` */

insert  into `crons`(`id`,`cron_id`,`cron_file`,`last_start_time`,`last_end_time`,`execution_info`,`last_action`) values (1,'solr_update_tree','D:\\devel\\www\\cb2\\casebox\\crons\\cron_solr_update_tree.php','2014-02-28 14:30:31','2014-02-28 14:30:33','ok','2014-02-28 14:30:33'),(2,'send_log_notifications','/var/www/casebox/casebox/crons/cron_send_log_notifications.php','2013-04-22 09:14:31','2013-04-22 09:14:31','ok','0000-00-00 00:00:00'),(3,'extract_file_contents','/var/www/casebox/casebox/crons/cron_extracting_file_contents.php','2013-07-12 10:54:03','2013-07-12 10:54:03','{\"Total\":0,\"Processed\":0,\"Not found\":0,\"Processed List\":[],\"Not found List\":[]}','0000-00-00 00:00:00'),(4,'check_core_email','/var/www/casebox/casebox/crons/cron_check_cores_mail.php','2013-04-20 18:20:01','2013-04-20 18:20:02','ok','0000-00-00 00:00:00'),(5,'check_deadlines','/var/www/casebox/casebox/crons/cron_check_deadlines.php','2014-02-28 14:33:05','2014-02-28 14:33:05','ok','2014-02-28 14:33:05'),(6,'test','/var/www/casebox/casebox/crons/test_mail_format.php','2013-01-24 09:14:53','2013-01-24 09:14:53','ok','0000-00-00 00:00:00'),(7,'send_notifications','/var/www/casebox/sys/crons/cron_send_notifications.php','2014-02-28 14:34:04','2014-02-28 14:34:04','ok','2014-02-28 14:34:04'),(8,'extract_files_content','/var/www/casebox/sys/crons/cron_extract_files_content.php','2014-02-28 14:34:04','2014-02-28 14:34:04','{\"Total\":0,\"Processed\":0,\"Not found\":0,\"Processed List\":[],\"Not found List\":[]}','2014-02-28 14:34:04');

/*Table structure for table `favorites` */

DROP TABLE IF EXISTS `favorites`;

CREATE TABLE `favorites` (
  `user_id` int(10) unsigned NOT NULL,
  `object_id` bigint(20) unsigned NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`object_id`),
  KEY `FK_favorites_object_id` (`object_id`),
  CONSTRAINT `FK_favorites_object_id` FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_favorites_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `favorites` */

/*Table structure for table `file_previews` */

DROP TABLE IF EXISTS `file_previews`;

CREATE TABLE `file_previews` (
  `id` bigint(20) unsigned NOT NULL,
  `group` varchar(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - ok, 1 - on queue, 2 - processing, 3 - processed',
  `filename` varchar(100) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ladate` timestamp NULL DEFAULT NULL COMMENT 'last access date',
  PRIMARY KEY (`id`),
  KEY `tree_previews__status_group` (`group`,`status`),
  CONSTRAINT `FK_file_previews_content_id` FOREIGN KEY (`id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `file_previews` */

/*Table structure for table `files` */

DROP TABLE IF EXISTS `files`;

CREATE TABLE `files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` bigint(20) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `cid` int(10) unsigned NOT NULL DEFAULT '1',
  `uid` int(10) unsigned NOT NULL DEFAULT '1',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `udate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `FK_files__content_id` (`content_id`),
  CONSTRAINT `FK_files__content_id` FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_files__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `files` */

/*Table structure for table `files_content` */

DROP TABLE IF EXISTS `files_content`;

CREATE TABLE `files_content` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `size` bigint(20) unsigned DEFAULT NULL,
  `pages` int(11) unsigned DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `path` varchar(250) DEFAULT NULL,
  `ref_count` int(11) NOT NULL DEFAULT '0',
  `parse_status` tinyint(1) unsigned DEFAULT NULL,
  `skip_parsing` tinyint(1) NOT NULL DEFAULT '0',
  `md5` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_md5` (`md5`),
  KEY `idx_size` (`size`),
  KEY `idx_parse_status` (`parse_status`),
  KEY `idx_skip_parsing` (`skip_parsing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `files_content` */

/*Table structure for table `files_versions` */

DROP TABLE IF EXISTS `files_versions`;

CREATE TABLE `files_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` bigint(20) unsigned NOT NULL,
  `content_id` bigint(20) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `cid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `udate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `FK_file_id` (`file_id`),
  KEY `FK_content_id` (`content_id`),
  CONSTRAINT `FK_content_id` FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`),
  CONSTRAINT `FK_file_id` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `files_versions` */

/*Table structure for table `menu` */

DROP TABLE IF EXISTS `menu`;

CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_ids` varchar(1000) DEFAULT NULL,
  `node_template_ids` varchar(1000) DEFAULT NULL,
  `menu` text,
  `user_group_ids` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Data for the table `menu` */

insert  into `menu`(`id`,`node_ids`,`node_template_ids`,`menu`,`user_group_ids`) values (1,NULL,NULL,'24072,24073,24078,-,24074,-,24079,-,24195',NULL),(2,'24042',NULL,'24044,24043,24074',NULL),(3,NULL,'24044','24043',NULL),(4,NULL,'24043','24043',NULL),(5,'23940',NULL,'24217,24074',NULL),(6,'24265',NULL,'24484',NULL),(7,NULL,'24079','24072,24073,24078,-,24195,-,24074',NULL);

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) unsigned DEFAULT NULL,
  `action_type` tinyint(4) unsigned DEFAULT NULL,
  `case_id` bigint(20) unsigned DEFAULT NULL,
  `object_id` bigint(20) unsigned DEFAULT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `subtype` smallint(6) DEFAULT NULL COMMENT 'for tasks: 0 - genereal, 1 - for deadline',
  `file_id` bigint(20) unsigned DEFAULT NULL,
  `sender` varchar(500) DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_notifications__user_id` (`user_id`),
  KEY `FK_notifications__file_id` (`file_id`),
  KEY `FK_notifications__case_id` (`case_id`),
  KEY `FK_notifications__object_id` (`object_id`),
  KEY `FK_notifications__task_id` (`task_id`),
  CONSTRAINT `FK_notifications__file_id` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__object_id` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `notifications` */

insert  into `notifications`(`id`,`time`,`user_id`,`action_type`,`case_id`,`object_id`,`task_id`,`subtype`,`file_id`,`sender`,`subject`,`message`) values (1,'2014-02-28 15:18:38',240,21,NULL,NULL,25078,0,NULL,'Oleg Burlaca (demosrc) <emails.sender@server.com>','New task: Oleg Burlaca: Scan papers (/Tree/Demo/Documents/)','\n<div style=\"max-width:600px;background-color: #FFF; font: 13px/normal Arial,sans-serif\">\n<h2 style=\"font-size: 1.5em; display: block;\">Scan papers</h2>\n<div style=\"padding: 10px 0px\">Fri Feb 28, 2014 - Fri Mar 7</div>\n<div style=\"color: #777; padding-bottom: 10px; border-bottom: 1px solid #EEE;\"></div>\n<table style=\"width: 100%; margin-top: 10px; font: 13px/normal Arial,sans-serif;\"><tbody>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Status:</td><td style=\"padding: 5px 0; vertical-align:top\"><span class=\"status-style\">active</span></td></tr>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Importance:</td><td style=\"padding: 5px 0; vertical-align:top\">Low</td></tr>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Category:</td><td style=\"padding: 5px 0; vertical-align:top\"></td></tr>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Path:</td><td style=\"padding: 5px 0; color: #777; vertical-align:top\">/Tree/Demo/Documents/</td></tr>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Owner:</td><td style=\"padding: 5px 0; vertical-align:top\"><table style=\"font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif; font-size: 11px; color: #333; width: 100%; display: table; border-collapse: separate; border-spacing: 0;\"><tbody>\n        <tr><td style=\"width: 1% !important; padding-right: 5px; vertical-align:top\"><img style=\"width:32px;height:32px\" src=\"https://demosrc.casebox.org/photo/240.jpg\"></td>\n            <td style=\"vertical-align:top\"><b>Oleg Burlaca</b><p style=\"color:#777;margin:0;padding:0\">Created:\n               <span style=\"color: #777\" title=\"{full_created_date_text}\">2014, February 28 13:18</span></p>\n           </td>\n        </tr>\n    </tbody>\n    </table>\n</td></tr>\n<tr><td style=\"width: 1%; padding: 5px 15px 5px 0; color: #777; vertical-align:top\">Assigned:</td><td style=\"vertical-align:top\"><table style=\"font-family: \'lucida grande\',tahoma,verdana,arial,sans-serif; font-size: 11px; color: #333; width: 100%; display: table; border-collapse: separate; border-spacing: 0;\"><tbody>\n\r<tr><td style=\"width: 1% !important; padding:5px 5px 5px 0px; vertical-align:top; white-space: nowrap\">\n\r<img src=\"https://demosrc.casebox.org/photo/240.jpg\" style=\"width:32px; height: 32px\" alt=\"Oleg Burlaca\" title=\"Oleg Burlaca\"/>\n\r\n\r</td><td style=\"padding: 5px 5px 5px 0; vertical-align:top\"><b>Oleg Burlaca</b>\n\r<p style=\"color:#777;margin:0;padding:0\">\n\rwaiting for action\n\r</p></td></tr></tbody></table></td></tr>\n\n</tbody></table></div>\n\n');

/*Table structure for table `objects` */

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` mediumtext,
  `sys_data` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25079 DEFAULT CHARSET=utf8;

/*Data for the table `objects` */

insert  into `objects`(`id`,`data`,`sys_data`) values (1456,'{\"status\":24260,\"_title\":\"0803-Moidunov\",\"nr\":\"0803\",\"program\":\"24266\"}','[]'),(1602,'{\"_title\":\"0807-Akmatov\",\"nr\":\"0807\",\"program\":\"24266\",\"status\":24260,\"lead\":\"262\"}','[]'),(2178,'{\"status\":24260,\"_title\":\"0808-Akunov\",\"nr\":\"0808\",\"program\":\"24266\"}','[]'),(2621,'{\"status\":24260,\"_title\":\"0809-Ernazarov\",\"nr\":\"0809\",\"program\":\"24266\"}','[]'),(2943,'{\"status\":24260,\"_title\":\"0810-Gerasimov\",\"nr\":\"0810\",\"program\":\"24266\"}','[]'),(3329,'{\"status\":24260,\"_title\":\"0915-Zhovtis\",\"nr\":\"0915\",\"program\":\"24266\"}','[]'),(4129,'{\"status\":24260,\"_title\":\"1010-Askarov\",\"nr\":\"1010\",\"program\":\"24266\"}','[]'),(4886,'{\"status\":24260,\"_title\":\"1107-Andijan\",\"nr\":\"1107\",\"program\":\"24266\"}','[]'),(5188,'{\"status\":24260,\"_title\":\"1113-Khadjev (Muradova)\",\"nr\":\"1113\",\"program\":\"24266\"}','[]'),(5568,'{\"status\":24260,\"_title\":\"0704-Bolgonbaev\",\"nr\":\"0704\",\"program\":\"24266\"}','[]'),(5587,'{\"status\":24260,\"_title\":\"0707-Sanginov\",\"nr\":\"0707\",\"program\":\"24266\"}','[]'),(5607,'{\"status\":24260,\"_title\":\"0815-Sakeev\",\"nr\":\"0815\",\"program\":\"24266\"}','[]'),(5625,'{\"status\":24260,\"_title\":\"1101-Ismanov\",\"nr\":\"1101\",\"program\":\"24266\"}','[]'),(5671,'{\"status\":24260,\"_title\":\"0405-Nachova\",\"nr\":\"0405\",\"program\":\"24267\"}','[]'),(5690,'{\"status\":24260,\"_title\":\"0504-Y&B\",\"nr\":\"0504\",\"program\":\"24267\"}','[]'),(5746,'{\"status\":24260,\"_title\":\"0301-DH\",\"nr\":\"0301\",\"program\":\"24267\"}','[]'),(6128,'{\"status\":24260,\"_title\":\"0408-Mauritania\",\"nr\":\"0408\",\"program\":\"24267\"}','[]'),(6366,'{\"status\":24260,\"_title\":\"0502-Solomon\",\"nr\":\"0502\",\"program\":\"24267\"}','[]'),(6385,'{\"status\":24260,\"_title\":\"0602-Ali\",\"nr\":\"0602\",\"program\":\"24267\"}','[]'),(7041,'{\"status\":24260,\"_title\":\"0603-Bagdonavichus\",\"nr\":\"0603\",\"program\":\"24267\"}','[]'),(7381,'{\"status\":24260,\"_title\":\"0604-Good\",\"nr\":\"0604\",\"program\":\"24267\"}','[]'),(7405,'{\"status\":24260,\"_title\":\"0605-People\",\"nr\":\"0605\",\"program\":\"24267\"}','[]'),(7841,'{\"status\":24260,\"_title\":\"0606-Williams\",\"nr\":\"0606\",\"program\":\"24267\"}','[]'),(7946,'{\"status\":24260,\"_title\":\"0702-Makhashev\",\"nr\":\"0702\",\"program\":\"24267\"}','[]'),(8033,'{\"status\":24260,\"_title\":\"0703-Makuc\",\"nr\":\"0703\",\"program\":\"24267\"}','[]'),(8148,'{\"status\":24260,\"_title\":\"0802-Antilleans\",\"nr\":\"0802\",\"_date_start\":\"2013-05-24T00:00:00\",\"manager\":\"31\",\"program\":\"24267\"}','[]'),(8254,'{\"status\":24260,\"_title\":\"0805-Fadia\",\"nr\":\"0805\",\"program\":\"24267\"}','[]'),(8384,'{\"status\":24260,\"_title\":\"0812-Nubian Minors\",\"nr\":\"0812\",\"program\":\"24267\"}','[]'),(8905,'{\"status\":24260,\"_title\":\"0816-Timishev\",\"nr\":\"0816\",\"program\":\"24267\"}','[]'),(8990,'{\"status\":24260,\"_title\":\"0817-Sejdic\",\"nr\":\"0817\",\"program\":\"24267\"}','[]'),(9034,'{\"status\":24260,\"_title\":\"0819-Bueno\",\"nr\":\"0819\",\"program\":\"24267\"}','[]'),(9182,'{\"status\":24260,\"_title\":\"0822-Adalah\",\"nr\":\"0822\",\"program\":\"24267\"}','[]'),(9253,'{\"status\":24260,\"_title\":\"0824-Shaya\",\"nr\":\"0824\",\"program\":\"24267\"}','[]'),(9275,'{\"status\":24260,\"_title\":\"0902-Suleymanovic\",\"nr\":\"0902\",\"program\":\"24267\"}','[]'),(9432,'{\"status\":24260,\"_title\":\"0904-Mikhaj\",\"nr\":\"0904\",\"program\":\"24267\"}','[]'),(9600,'{\"status\":24260,\"_title\":\"0905-SPIB\",\"nr\":\"0905\",\"program\":\"24267\"}','[]'),(9741,'{\"status\":24260,\"_title\":\"0906-EC v Italy\",\"nr\":\"0906\",\"program\":\"24267\"}','[]'),(10047,'{\"status\":24260,\"_title\":\"0910-Omerovic\",\"nr\":\"0910\",\"program\":\"24267\"}','[]'),(10130,'{\"status\":24260,\"_title\":\"0911-Panah\",\"nr\":\"0911\",\"program\":\"24267\"}','[]'),(10402,'{\"status\":24260,\"_title\":\"0912-Dupont\",\"nr\":\"0912\",\"program\":\"24267\"}','[]'),(10851,'{\"status\":24260,\"_title\":\"0913-Weiss (Germany headscarves)\",\"nr\":\"0913\",\"program\":\"24267\"}','[]'),(11175,'{\"status\":24260,\"_title\":\"1001-Germany Education General\",\"nr\":\"1001\",\"program\":\"24267\"}','[]'),(11260,'{\"status\":24260,\"_title\":\"1003-Iseni\",\"nr\":\"1003\",\"program\":\"24267\"}','[]'),(11349,'{\"status\":24260,\"_title\":\"1008-Ouardiri\",\"nr\":\"1008\",\"program\":\"24267\"}','[]'),(11548,'{\"status\":24260,\"_title\":\"1101-QPC\",\"nr\":\"1101\",\"program\":\"24267\"}','[]'),(11605,'{\"status\":24260,\"_title\":\"1102-Baby DR\",\"nr\":\"1102\",\"program\":\"24267\"}','[]'),(11628,'{\"status\":24260,\"_title\":\"1110-Cicek\",\"nr\":\"1110\",\"program\":\"24267\"}','[]'),(11651,'{\"status\":24260,\"_title\":\"1114-Berlin Segregated Classes\",\"nr\":\"1114\",\"program\":\"24267\"}','[]'),(12845,'{\"status\":24260,\"_title\":\"1202-SAS\",\"nr\":\"1202\",\"program\":\"24267\"}','[]'),(13006,'{\"status\":24260,\"_title\":\"1204-Salkanovic\",\"nr\":\"1204\",\"program\":\"24267\"}','[]'),(13032,'{\"status\":24260,\"_title\":\"1205-Dabetic\",\"nr\":\"1205\",\"program\":\"24267\"}','[]'),(13051,'{\"_title\":\"1207-Leonardo da Vinci\",\"nr\":\"1207\",\"program\":\"24267\",\"status\":24261}','[]'),(13314,'{\"status\":24260,\"_title\":\"0505-Ramzy\",\"nr\":\"0505\",\"program\":\"24267\"}','[]'),(13334,'{\"status\":24260,\"_title\":\"0201-Marques\",\"nr\":\"0201\",\"program\":\"24268\"}','[]'),(13387,'{\"status\":24260,\"_title\":\"0404-GPU\",\"nr\":\"0404\",\"program\":\"24268\"}','[]'),(13425,'{\"status\":24260,\"_title\":\"0403-Claude\",\"nr\":\"0403\",\"program\":\"24268\"}','[]'),(13500,'{\"status\":24260,\"_title\":\"0406-Herrera\",\"nr\":\"0406\",\"program\":\"24268\"}','[]'),(13561,'{\"status\":24260,\"_title\":\"0401-Freedom FM\",\"nr\":\"0401\",\"program\":\"24268\"}','[]'),(13667,'{\"status\":24260,\"_title\":\"0506-Romanenko\",\"nr\":\"0506\",\"program\":\"24268\"}','[]'),(13703,'{\"status\":24260,\"_title\":\"0804-SLAJ\",\"nr\":\"0804\",\"program\":\"24268\"}','[]'),(13745,'{\"status\":24260,\"_title\":\"0811-Hydara\",\"nr\":\"0811\",\"program\":\"24268\"}','[]'),(14027,'{\"status\":24260,\"_title\":\"0823-Kasabova\",\"nr\":\"0823\",\"program\":\"24268\"}','[]'),(14088,'{\"status\":24260,\"_title\":\"0901-MGN\",\"nr\":\"0901\",\"program\":\"24268\"}','[]'),(14169,'{\"status\":24260,\"_title\":\"0903-Pauliukiene\",\"nr\":\"0903\",\"program\":\"24268\"}','[]'),(14220,'{\"status\":24260,\"_title\":\"0914-Sanoma\",\"nr\":\"0914\",\"program\":\"24268\"}','[]'),(14271,'{\"status\":24260,\"_title\":\"1002-Centro 7\",\"nr\":\"1002\",\"program\":\"24268\"}','[]'),(14347,'{\"status\":24260,\"_title\":\"1013-Egypt Bloggers\",\"nr\":\"1013\",\"program\":\"24268\"}','[]'),(14372,'{\"status\":24260,\"_title\":\"1109-Yildirim\",\"nr\":\"1109\",\"program\":\"24268\"}','[]'),(14426,'{\"status\":24260,\"_title\":\"0710-Chardon\",\"nr\":\"0710\",\"program\":\"24268\"}','[]'),(14446,'{\"status\":24260,\"_title\":\"0801-CDDI\",\"nr\":\"0801\",\"program\":\"24268\"}','[]'),(14479,'{\"status\":24260,\"_title\":\"0806-HCLU\",\"nr\":\"0806\",\"program\":\"24268\"}','[]'),(14522,'{\"status\":24260,\"_title\":\"0818-El-Masri\",\"nr\":\"0818\",\"program\":\"24268\"}','[]'),(15795,'{\"status\":24260,\"_title\":\"0916-Vargas\",\"nr\":\"0916\",\"program\":\"24268\"}','[]'),(15836,'{\"status\":24260,\"_title\":\"1005-Araguaia\",\"nr\":\"1005\",\"program\":\"24268\"}','[]'),(15887,'{\"status\":24260,\"_title\":\"1011-Bubon\",\"nr\":\"1011\",\"program\":\"24268\"}','[]'),(15932,'{\"status\":24260,\"_title\":\"1112-Mpagi\",\"nr\":\"1112\",\"program\":\"24268\"}','[]'),(15985,'{\"status\":24260,\"_title\":\"1201-Diario Militar\",\"nr\":\"1201\",\"program\":\"24268\"}','[]'),(16295,'{\"status\":24260,\"_title\":\"0402-Anyaele\",\"nr\":\"0402\",\"program\":\"24269\"}','[]'),(16314,'{\"status\":24260,\"_title\":\"0607-Prosecutor\",\"nr\":\"0607\",\"program\":\"24269\"}','[]'),(16340,'{\"status\":24260,\"_title\":\"1007-Gaza\",\"nr\":\"1007\",\"program\":\"24269\"}','[]'),(16418,'{\"status\":24260,\"_title\":\"1104-Duvalier\",\"nr\":\"1104\",\"program\":\"24269\"}','[]'),(16538,'{\"status\":24260,\"_title\":\"1117-Kenya Complementarity\",\"nr\":\"1117\",\"program\":\"24269\"}','[]'),(16765,'{\"status\":24260,\"_title\":\"1206-Kenya Police Shootings\",\"nr\":\"1206\",\"program\":\"24269\"}','[]'),(16799,'{\"status\":24260,\"_title\":\"0701-APDHE(AFR)\",\"nr\":\"0701\",\"program\":\"24270\"}','[]'),(16900,'{\"status\":24260,\"_title\":\"0813-APDHE(ESP)\",\"nr\":\"0813\",\"program\":\"24270\"}','[]'),(17081,'{\"status\":24260,\"_title\":\"0907-Hussar\",\"nr\":\"0907\",\"program\":\"24270\"}','[]'),(18734,'{\"status\":24260,\"_title\":\"0909-Argor Heraeus\",\"nr\":\"0909\",\"program\":\"24270\"}','[]'),(18752,'{\"status\":24260,\"_title\":\"1103-Malibu\",\"nr\":\"1103\",\"program\":\"24270\"}','[]'),(18960,'{\"status\":24260,\"_title\":\"1105-Frontex\",\"nr\":\"1105\",\"program\":\"24271\"}','[]'),(19041,'{\"status\":24260,\"_title\":\"1111-Cosentino\",\"nr\":\"1111\",\"program\":\"24271\"}','[]'),(19229,'{\"status\":24260,\"_title\":\"1012-Alade\",\"nr\":\"1012\",\"program\":\"24272\"}','[]'),(19324,'{\"status\":24260,\"_title\":\"1106-Arrest Rights\",\"nr\":\"1106\",\"program\":\"24272\"}','[]'),(19505,'{\"status\":24260,\"_title\":\"1108-Magnitsky\",\"nr\":\"1108\",\"program\":\"24272\"}','[]'),(21292,'{\"status\":24260,\"_title\":\"1116-Lipowicz\",\"nr\":\"1116\",\"program\":\"24272\"}','[]'),(21557,'{\"status\":24260,\"_title\":\"1006-Al-Nashiri v Poland\",\"nr\":\"1006\",\"program\":\"24273\"}','[]'),(22486,'{\"status\":24260,\"_title\":\"1009-El-Sharkawi\",\"nr\":\"1009\",\"program\":\"24273\"}','[]'),(22633,'{\"status\":24260,\"_title\":\"1203-Al Nashiri v Romania\",\"nr\":\"1203\",\"program\":\"24273\"}','[]'),(23129,'{\"status\":24260,\"_title\":\"xxxx-Hizb-ut-Tahrir TPI\",\"nr\":\"xxxx\",\"program\":\"24273\"}','[]'),(23156,'{\"status\":24260,\"_title\":\"1004-Salim\",\"nr\":\"1004\",\"program\":\"24273\"}','[]'),(23355,'{\"status\":24260,\"_title\":\"xxxx-Abdulmalik\",\"nr\":\"xxxx\",\"program\":\"24273\"}','[]'),(23459,'{\"_title\":\"Reply from Gambian governemnt\",\"_date_start\":\"2013-01-10T00:00:00\",\"tags\":\"24400,24436,24431,24429\"}','[]'),(23460,'{\"_title\":\"notification\",\"court\":\"24391\",\"_date_start\":\"2013-01-10T00:00:00\"}','[]'),(23465,'{\"_title\":\"case card\",\"_date_start\":\"2013-01-11T00:00:00\",\"state\":\"24316\",\"court\":\"24396\",\"tags\":\"24403,24404,24408\"}','[]'),(23466,'{\"_title\":\"Application to ECHR\",\"court\":\"24391\",\"_date_start\":\"2013-01-11T00:00:00\"}','[]'),(23470,'{\"_title\":\"dummy case\",\"nr\":\"1234\",\"_date_start\":\"2013-01-14T00:00:00\",\"manager\":\"4,7\",\"lead\":\"5\",\"support\":\"8,9\",\"court\":\"24394,24395\",\"program\":\"24268\",\"status\":\"24260\"}','[]'),(23492,'{\"status\":24260,\"_title\":\"0709-Akmatov\",\"nr\":\"999\",\"_date_start\":\"2013-01-15\"}','[]'),(23613,'{\"_title\":\"Decision from court N.182\",\"court\":\"24398\",\"_date_start\":\"2013-01-15T00:00:00\",\"tags\":\"24405,24404,24407\"}','[]'),(23614,'{\"_title\":\"Our complaint had been received by the court\",\"court\":\"24398\",\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23615,'{\"_title\":\"Application to ECHR\",\"court\":\"24391\",\"_date_start\":\"2013-01-15T00:00:00\",\"tags\":\"24405,24407,24400\"}','[]'),(23616,'{\"_title\":\"Judgement on the merits\",\"court\":\"24391\",\"_date_start\":\"2013-01-01T00:00:00\",\"tags\":\"24404,24406\"}','[]'),(23618,'{\"_title\":\"Some general comments about the case\",\"_date_start\":\"2013-01-03T00:00:00\"}','[]'),(23624,'{\"_title\":\"Akmatov case card\",\"_date_start\":\"2013-01-15T00:00:00\",\"_content\":\"gf<br>asd<br>fas<br>df<br>asd<br>fas<br>df<br>asd<br>fas<br>df<br>asd<br>f\",\"state\":\"24337\",\"court\":\"24393\",\"tags\":\"24407,24402,24406,24409\"}','[]'),(23634,'{\"_title\":\"Monthly Report - February 2013\",\"court\":{\"value\":null,\"info\":\"infomraoitn \"},\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23636,'{\"_title\":\"Monthly Report\",\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23647,'{\"_title\":\"this is an imported email \",\"_date_start\":\"2013-01-21 22:02:34\",\"from\":\"Oleg Burlaca <oleg@burlaca.com>\",\"_content\":\"---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 7:31 PM\\nSubject: Fwd: this is an imported email (\\/Home\\/Test folder)\\nTo: OSJI OSI <osjibox@gmail.com>\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 7:26 PM\\nSubject: this is an imported email (\\/Home\\/Test folder\\/)\\nTo: osjibox@gmail.com\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 6:16 PM\\nSubject: Fwd: A new action from email (\\/Test\\/Files)\\nTo: osjibox@gmail.com\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 6:13 PM\\nSubject: A new action from email (\\/Test\\/Files\\/)\\nTo: osjibox@gmail.com\\n\\n\\nText here\\n\\nhere text text text\\n\\n\"}','[]'),(23668,'{\"_title\":\"follow-up\",\"_date_start\":\"2013-03-20T00:00:00\",\"_content\":\"can you please follow-up on this?\"}','[]'),(23670,'{\"_title\":\"comment!\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23676,'{\"_title\":\"to the court\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23678,'{\"_title\":\"case card\",\"_date_start\":\"2013-01-22T00:00:00\",\"state\":\"24325\",\"court\":\"24398\",\"tags\":\"24439,24401,24400\"}','[]'),(23681,'{\"_title\":\"Received communication from Turkish Government\",\"_date_start\":\"2013-01-23T00:00:00\"}','[]'),(23682,'{\"_title\":\"received second reply from Turkey\",\"_date_start\":\"2013-01-23T00:00:00\"}','[]'),(23684,'{\"_title\":\"Letter setting hearing date\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23685,'{\"_title\":\"an email letter \",\"_date_start\":\"2013-01-25 09:22:16\",\"from\":\"Oleg Burlaca <oleg@burlaca.com>\",\"_content\":\"---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Fri, Jan 25, 2013 at 11:19 AM\\nSubject: an email letter (\\/Home\\/Test\\/)\\nTo: OSJI OSI <osjibox@gmail.com>\\n\\n\\nasdfasdf asdf asdf\\n\\n\"}','[]'),(23706,'{\"status\":24260,\"_title\":\"test\",\"nr\":\"9999\",\"_date_start\":\"2013-02-13\",\"program\":\"24266\"}','[]'),(23717,'{\"status\":24260,\"_title\":\"tzretz\",\"nr\":\"test\",\"_date_start\":\"2013-02-15T00:00:00\",\"program\":\"24266,24267\",\"lead\":\"1\",\"support\":\"1\"}','[]'),(23739,'{\"_title\":\"TestAction\",\"_date_start\":\"2013-05-28T00:00:00\",\"program\":\"24266\"}','[]'),(23743,'{\"_title\":\"Moidunov action test\",\"_date_start\":\"2013-05-26T00:00:00\",\"program\":\"24266,24267\"}','[]'),(23748,'{\"_title\":\"Action test 31.05.2013\",\"_date_start\":\"2013-05-31T00:00:00\",\"program\":\"24266\"}','[]'),(23777,'{\"_title\":\"[Test] Netherlands Police Shootings Constitutional Case\",\"nr\":\"111111\",\"_date_start\":\"2013-06-01T00:00:00\",\"manager\":\"49\",\"lead\":\"5\",\"support\":\"34,42\",\"court\":\"24396\",\"program\":\"24266\",\"status\":\"24263\",\"country\":\"24325,24335,24339\"}','[]'),(23778,'[]','[]'),(23779,'[]','[]'),(23780,'[]','[]'),(23781,'[]','[]'),(23782,'[]','[]'),(23783,'[]','[]'),(23784,'[]','[]'),(23785,'[]','[]'),(23786,'[]','[]'),(23787,'[]','[]'),(23797,'{\"_title\":\"Decision on merits \",\"_date_start\":\"2013-06-01T00:00:00\",\"program\":\"24266\"}','[]'),(23798,'{\"_title\":\"Additional documents sent to the court\",\"_date_start\":\"2013-03-21T00:00:00\",\"program\":\"24266\"}','[]'),(23799,'{\"_title\":\"Government Reply\",\"_date_start\":\"2013-03-24T00:00:00\",\"content\":\"&nbsp;\",\"program\":\"24266\"}','[]'),(23800,'{\"_title\":\"Communication \",\"_date_start\":\"2013-06-01T00:00:00\",\"content\":\"<br>\",\"program\":\"24266\"}','[]'),(23801,'{\"_title\":\"Letter of introduction\",\"_date_start\":\"2013-06-01T00:00:00\",\"content\":\"&nbsp;\",\"program\":\"24266\"}','[]'),(23815,'{\"status\":24260,\"_title\":\"12\",\"nr\":\"12\",\"_date_start\":\"2013-06-18T00:00:00\"}','[]'),(23816,'[]','[]'),(23817,'[]','[]'),(23818,'[]','[]'),(23819,'[]','[]'),(23820,'[]','[]'),(23821,'[]','[]'),(23822,'[]','[]'),(23823,'[]','[]'),(23824,'[]','[]'),(23825,'[]','[]'),(23831,'[]','[]'),(23841,'[]','[]'),(23850,'[]','[]'),(23851,'[]','[]'),(23852,'[]','[]'),(23853,'[]','[]'),(23854,'[]','[]'),(23855,'[]','[]'),(23856,'[]','[]'),(23857,'[]','[]'),(23869,'[]','[]'),(23871,'{\"_title\":\"test\",\"nr\":\"55555\",\"_date_start\":\"2013-09-24T00:00:00\",\"court\":\"24393\",\"status\":\"24260\",\"tags\":\"24406\"}','[]'),(23872,'[]','[]'),(23873,'[]','[]'),(23874,'[]','[]'),(23875,'[]','[]'),(23876,'[]','[]'),(23877,'[]','[]'),(23878,'[]','[]'),(23879,'[]','[]'),(23880,'[]','[]'),(23881,'[]','[]'),(23897,'{\"program\":\"24267\"}','[]'),(23898,'[]','[]'),(23904,'{\"_title\":\"Test case N1\",\"nr\":\"1\",\"_date_start\":\"2013-09-24T00:00:00\",\"court\":\"24391\",\"program\":\"24274,24273\",\"status\":24260,\"lead\":\"240\",\"support\":\"240,1\"}','[]'),(23905,'[]','[]'),(23906,'[]','[]'),(23907,'[]','[]'),(23908,'[]','[]'),(23909,'[]','[]'),(23910,'[]','[]'),(23911,'[]','[]'),(23912,'[]','[]'),(23913,'[]','[]'),(23914,'[]','[]'),(23915,'{\"status\":24260,\"_title\":\"zrezqe\",\"nr\":\"6436\",\"_date_start\":\"2013-09-24T00:00:00\"}','[]'),(23916,'[]','[]'),(23917,'[]','[]'),(23918,'[]','[]'),(23919,'[]','[]'),(23920,'[]','[]'),(23921,'[]','[]'),(23922,'[]','[]'),(23923,'[]','[]'),(23924,'[]','[]'),(23925,'[]','[]'),(23926,'[]','[]'),(23928,'[]','[]'),(23929,'{\"_title\":\"09292 First case\",\"nr\":\"09292\",\"_date_start\":\"2013-09-24T00:00:00\",\"manager\":\"5\",\"lead\":\"9\",\"support\":\"24,6\",\"court\":\"24393\",\"program\":\"24271\",\"status\":\"24263\",\"tags\":\"24406\",\"country\":\"24330,24339\"}','[]'),(23930,'[]','[]'),(23931,'[]','[]'),(23932,'[]','[]'),(23933,'[]','[]'),(23934,'[]','[]'),(23935,'[]','[]'),(23936,'[]','[]'),(23937,'[]','[]'),(23938,'[]','[]'),(23939,'[]','[]'),(23940,'[]','[]'),(23942,'{\"status\":24260,\"_title\":\"New case\",\"nr\":\"45542\",\"_date_start\":\"2013-09-24T00:00:00\",\"program\":\"24271\"}','[]'),(23943,'[]','[]'),(23944,'[]','[]'),(23945,'[]','[]'),(23946,'[]','[]'),(23947,'[]','[]'),(23948,'[]','[]'),(23949,'[]','[]'),(23950,'[]','[]'),(23951,'[]','[]'),(23952,'[]','[]'),(23954,'{\"_title\":\"Fatulev\",\"nr\":\"123090\",\"_date_start\":\"2013-09-24T00:00:00\",\"manager\":\"55\",\"lead\":\"9\",\"support\":\"24\",\"court\":\"24395\",\"program\":\"24266,24269\",\"status\":\"24264\",\"tags\":\"24407\",\"country\":\"24320\"}','[]'),(23955,'[]','[]'),(23956,'[]','[]'),(23957,'[]','[]'),(23958,'[]','[]'),(23959,'[]','[]'),(23960,'[]','[]'),(23961,'[]','[]'),(23962,'[]','[]'),(23963,'[]','[]'),(23964,'[]','[]'),(23967,'[]','[]'),(23968,'{\"_title\":\"Girleanu\",\"nr\":\"09252013\",\"_date_start\":\"2013-09-25T00:00:00\",\"manager\":\"256\",\"lead\":\"240\",\"program\":\"24268\",\"status\":\"24260\",\"country\":\"24310\"}','[]'),(23969,'[]','[]'),(23970,'[]','[]'),(23971,'[]','[]'),(23972,'[]','[]'),(23973,'[]','[]'),(23974,'[]','[]'),(23975,'[]','[]'),(23976,'[]','[]'),(23977,'[]','[]'),(23978,'[]','[]'),(23988,'{\"_title\":\"Katherin Test\",\"nr\":\"9122013\",\"_date_start\":\"2013-09-25T00:00:00\",\"program\":\"24266,24267\",\"country\":\"24333\",\"status\":\"24260\",\"lead\":\"240\",\"support\":\"5\"}','[]'),(23989,'[]','[]'),(23990,'[]','[]'),(23991,'[]','[]'),(23992,'[]','[]'),(23993,'[]','[]'),(23994,'[]','[]'),(23995,'[]','[]'),(23996,'[]','[]'),(23997,'[]','[]'),(23998,'[]','[]'),(24007,'{\"_title\":\"Svetlana\",\"nr\":\"097856\",\"_date_start\":\"2013-09-26T00:00:00\",\"manager\":\"7\",\"lead\":\"8\",\"court\":\"24393,24395\",\"program\":\"24266\",\"status\":\"24260\",\"tags\":\"24406,24403\",\"country\":\"24317\"}','[]'),(24008,'[]','[]'),(24009,'[]','[]'),(24010,'[]','[]'),(24011,'[]','[]'),(24012,'[]','[]'),(24013,'[]','[]'),(24014,'[]','[]'),(24015,'[]','[]'),(24016,'[]','[]'),(24017,'[]','[]'),(24024,'{\"_title\":\"File EC v Italy brief (tentative)\",\"_date_start\":\"2013-10-01T00:00:00\",\"program\":\"24267\",\"tags\":\"24405\"}','[]'),(24028,'{\"_title\":\"Test action\",\"_date_start\":\"2013-09-30T00:00:00\",\"program\":\"24270\",\"tags\":\"24406\"}','[]'),(24042,'[]','[]'),(24043,'{\"_title\":\"Fields template\",\"en\":\"Fields template\",\"ru\":\"Fields template\",\"type\":\"field\",\"visible\":1,\"iconCls\":\"icon-snippet\",\"cfg\":\"[]\"}','[]'),(24044,'{\"_title\":\"Templates template\",\"en\":\"Templates template\",\"ru\":\"Templates template\",\"type\":\"template\",\"visible\":1,\"iconCls\":\"icon-template\",\"cfg\":\"[]\"}','[]'),(24045,'{\"_title\":\"templatesProperies\",\"en\":\"Template for editing template properties\",\"ru\":\"Template for editing template properties\",\"type\":\"template\",\"visible\":1}','[]'),(24046,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"1\"}','[]'),(24047,'{\"en\":\"JavaScript grid class\",\"ru\":\"JavaScript grid class\",\"_title\":\"gridJsClass\",\"type\":\"jsclasscombo\",\"order\":\"2\"}','[]'),(24048,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"3\"}','[]'),(24049,'{\"en\":\"Default field\",\"ru\":\"Default field\",\"_title\":\"default_field\",\"type\":\"fieldscombo\",\"order\":\"4\"}','[]'),(24050,'{\"en\":\"Files\",\"ru\":\"Files\",\"_title\":\"files\",\"type\":\"checkbox\",\"order\":\"5\"}','[]'),(24051,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\"}','[]'),(24052,'[]','[]'),(24053,'{\"_title\":\"User\",\"en\":\"User\",\"type\":\"user\",\"visible\":1,\"iconCls\":\"icon-object4\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24054,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24055,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24056,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24057,'{\"en\":\"Initials\",\"ru\":\"Initiales\",\"_title\":\"initials\",\"type\":\"varchar\",\"order\":\"4\"}','[]'),(24058,'{\"en\":\"Sex\",\"ru\":\"Sexe\",\"_title\":\"sex\",\"type\":\"_sex\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\"}','[]'),(24059,'{\"en\":\"Position\",\"ru\":\"Titre\",\"_title\":\"position\",\"type\":\"combo\",\"order\":\"7\",\"cfg\":\"{\\\"thesauriId\\\":\\\"362\\\"}\"}','[]'),(24060,'{\"en\":\"E-mail\",\"ru\":\"E-mail\",\"_title\":\"email\",\"type\":\"varchar\",\"order\":\"9\",\"cfg\":\"{\\\"maxInstances\\\":\\\"3\\\"}\"}','[]'),(24061,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language_id\",\"type\":\"_language\",\"order\":\"11\"}','[]'),(24062,'{\"en\":\"Date format\",\"ru\":\"Format de date\",\"_title\":\"short_date_format\",\"type\":\"_short_date_format\",\"order\":\"12\"}','[]'),(24063,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"13\"}','[]'),(24064,'{\"en\":\"Room\",\"ru\":\"Salle\",\"_title\":\"room\",\"type\":\"varchar\",\"order\":\"8\"}','[]'),(24065,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"10\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24066,'{\"en\":\"Location\",\"ru\":\"Emplacement\",\"_title\":\"location\",\"type\":\"combo\",\"order\":\"6\",\"cfg\":\"{\\\"thesauriId\\\":\\\"394\\\"}\"}','[]'),(24067,'{\"_title\":\"email\",\"en\":\"Email\",\"ru\":\"Email\",\"type\":\"email\",\"visible\":1,\"iconCls\":\"icon-mail\",\"cfg\":\"{\\\"files\\\":1,\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24068,'{\"en\":\"Subject\",\"ru\":\"Sujet\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24069,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24070,'{\"en\":\"From\",\"ru\":\"D\'après\",\"_title\":\"from\",\"type\":\"varchar\",\"order\":\"3\",\"cfg\":\"{\\\"thesauriId\\\":\\\"73\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24071,'{\"en\":\"Content\",\"ru\":\"Teneur\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24072,'{\"_title\":\"tasks\",\"en\":\"Task\",\"ru\":\"Task\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"icon-task\",\"cfg\":\"{\\\"data\\\":{\\\"type\\\":6}}\",\"title_template\":\"{name}\"}','[]'),(24073,'{\"_title\":\"event\",\"en\":\"Event\",\"ru\":\"Event\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"icon-event\",\"cfg\":\"{\\\"data\\\":{\\\"type\\\":7}}\",\"title_template\":\"{name}\"}','[]'),(24074,'{\"_title\":\"folder\",\"en\":\"Folder\",\"ru\":\"Folder\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-folder\",\"cfg\":\"{\\\"createMethod\\\":\\\"inline\\\"}\",\"title_template\":\"{name}\"}','[]'),(24075,'{\"_title\":\"file_template\",\"en\":\"File\",\"ru\":\"File\",\"type\":\"file\",\"visible\":1,\"iconCls\":\"file-\",\"title_template\":\"{name}\"}','[]'),(24076,'{\"en\":\"Program\",\"ru\":\"Program\",\"_title\":\"program\",\"type\":\"_objects\",\"order\":\"1\",\"cfg\":\"{\\r\\n\\\"source\\\":\\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"715\\\"\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"category_id\"}','[]'),(24077,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24078,'{\"_title\":\"milestone\",\"en\":\"Milestone\",\"ru\":\"Milestone\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"i-flag\",\"cfg\":\"[]\",\"title_template\":\"{name}\"}','[]'),(24079,'{\"_title\":\"case_template\",\"en\":\"Case\",\"ru\":\"Case\",\"type\":\"case\",\"visible\":1,\"iconCls\":\"icon-briefcase\",\"cfg\":\"{\\\"system_folders\\\": 24248}\",\"title_template\":\"{name}\"}','[]'),(24080,'{\"en\":\"Name\",\"ru\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24081,'{\"en\":\"Number\",\"ru\":\"Number\",\"_title\":\"nr\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24082,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24083,'{\"en\":\"End date\",\"ru\":\"End date\",\"_title\":\"_date_end\",\"type\":\"date\",\"order\":\"4\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24084,'{\"en\":\"Manager\",\"ru\":\"Manager\",\"_title\":\"manager\",\"type\":\"_objects\",\"order\":\"20\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids1\"}','[]'),(24085,'{\"en\":\"Lead\",\"ru\":\"Lead\",\"_title\":\"lead\",\"type\":\"_objects\",\"order\":\"21\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids2\"}','[]'),(24086,'{\"en\":\"Support\",\"ru\":\"Support\",\"_title\":\"support\",\"type\":\"_objects\",\"order\":\"22\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids3\"}','[]'),(24087,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"_objects\",\"order\":\"5\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"724\\\"\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"thesauri\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24088,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"_objects\",\"order\":\"6\",\"cfg\":\"{\\r\\n\\\"source\\\": \\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"715\\\"\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"editor\\\": \\\"form\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"category_id\"}','[]'),(24089,'{\"en\":\"Status\",\"ru\":\"Status\",\"_title\":\"status\",\"type\":\"_objects\",\"order\":\"8\",\"cfg\":\"{\\r\\n\\\"source\\\":\\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"356\\\"\\r\\n,\\\"multiValued\\\": false\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"status\"}','[]'),(24090,'{\"_title\":\"tags\",\"en\":\"Tags\",\"ru\":\"Tags\",\"type\":\"_objects\",\"order\":\"10\",\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24399\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\\n\"}','[]'),(24091,'{\"en\":\"Country\",\"ru\":\"Country\",\"_title\":\"country\",\"type\":\"_objects\",\"order\":\"7\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"351\\\"\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"thesauri\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24092,'[]','[]'),(24093,'[]','[]'),(24094,'[]','[]'),(24095,'{\"_title\":\"suspect\",\"en\":\"subject\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-suspect\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{f58} {f45} {f46}\"}','[]'),(24096,'{\"en\":\"Name\",\"_title\":\"fname\",\"type\":\"varchar\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24097,'{\"en\":\"Middle name\",\"_title\":\"patronymic\",\"type\":\"varchar\",\"order\":\"4\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24098,'{\"en\":\"Sex\",\"_title\":\"sex\",\"type\":\"combo\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24099,'{\"en\":\"Age\",\"_title\":\"age\",\"type\":\"int\",\"order\":\"6\",\"solr_column_name\":\"ints\"}','[]'),(24100,'{\"en\":\"Place of service\\/work\",\"_title\":\"work\",\"type\":\"varchar\",\"order\":\"7\",\"solr_column_name\":\"strings\"}','[]'),(24101,'{\"en\":\"Rank at the time of the incident\",\"_title\":\"rank\",\"type\":\"varchar\",\"order\":\"8\",\"solr_column_name\":\"strings\"}','[]'),(24102,'{\"en\":\"Outfit\",\"_title\":\"dressing\",\"type\":\"combo\",\"order\":\"9\",\"cfg\":\"{\\\"thesauriId\\\":\\\"118\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24103,'{\"en\":\"Intoxication from the statements of the applicant\\r\\n\",\"_title\":\"drunk_words\",\"type\":\"combo\",\"order\":\"10\",\"cfg\":\"{\\\"thesauriId\\\":\\\"100\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24104,'{\"en\":\"Nickname\\r\\n\",\"_title\":\"nickname\",\"type\":\"varchar\",\"order\":\"11\",\"solr_column_name\":\"strings\"}','[]'),(24105,'{\"en\":\"Physical description\\r\\n\",\"_title\":\"look\",\"type\":\"varchar\",\"order\":\"12\",\"solr_column_name\":\"strings\"}','[]'),(24106,'{\"en\":\"Special features\\r\\n\",\"_title\":\"distinctive_marks\",\"type\":\"varchar\",\"order\":\"13\",\"solr_column_name\":\"strings\"}','[]'),(24107,'{\"en\":\"Last name\\r\\n\",\"_title\":\"lname\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24108,'{\"_title\":\"Case card\",\"en\":\"Case card\",\"ru\":\"Case card\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-blog-blue\",\"cfg\":\"{\\\"files\\\":\\\"0\\\",\\\"main_file\\\":\\\"0\\\"}\"}','[]'),(24109,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24110,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24111,'{\"en\":\"Content\",\"ru\":\"Content\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24112,'{\"en\":\"State\",\"ru\":\"State\",\"_title\":\"state\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"351\\\"}\"}','[]'),(24113,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"2\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"The main court\\\"}\"}','[]'),(24114,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24115,'[]','[]'),(24116,'{\"_title\":\"informationalLetter\",\"en\":\"Incoming action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-arrow-left-medium-green\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{template_title}: {object_title}\"}','[]'),(24117,'{\"en\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24118,'{\"en\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24119,'{\"en\":\"Content\\r\\n\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24120,'{\"en\":\"Author\",\"ru\":\"Auteur\",\"_title\":\"author\",\"type\":\"combo\",\"order\":\"3\",\"cfg\":\"{\\\"thesauriId\\\":\\\"337\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24121,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"341\\\",\\\"maxInstances\\\":\\\"3\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24122,'{\"_title\":\"email1\",\"en\":\"email\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-mail-receive\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24123,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24124,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24125,'{\"_title\":\"communication\",\"en\":\"Communication\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-balloon\",\"cfg\":\"[]\"}','[]'),(24126,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24127,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24128,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24129,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24130,'{\"_title\":\"decision\",\"en\":\"Decision\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-decision\",\"cfg\":\"[]\"}','[]'),(24131,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24132,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24133,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24134,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24135,'{\"_title\":\"judgement\",\"en\":\"Judgement\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-echr_decision\",\"cfg\":\"[]\"}','[]'),(24136,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24137,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24138,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24139,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24140,'{\"_title\":\"gv_reply\",\"en\":\"Government reply\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-object8\",\"cfg\":\"[]\"}','[]'),(24141,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24142,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24143,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24144,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24145,'{\"_title\":\"notification\",\"en\":\"Notification\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-bell\",\"cfg\":\"[]\"}','[]'),(24146,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24147,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24148,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24149,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24150,'[]','[]'),(24151,'{\"_title\":\"Outgoing action\",\"en\":\"Outgoing action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-arrow-right-medium\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{template_title}\"}','[]'),(24152,'{\"en\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24153,'{\"en\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24154,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"341\\\",\\\"maxInstances\\\":\\\"3\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24155,'{\"_title\":\"email2\",\"en\":\"email\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-mail-send\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24156,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24157,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24158,'{\"_title\":\"written_comments\",\"en\":\"Written comments\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-document-text\",\"cfg\":\"[]\"}','[]'),(24159,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24160,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24161,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24162,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24163,'{\"_title\":\"application\",\"en\":\"Application\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-echr_complaint\",\"cfg\":\"[]\"}','[]'),(24164,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24165,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24166,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24167,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"5\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24168,'{\"en\":\"Application Nr.\",\"ru\":\"Application Nr.\",\"_title\":\"appnr\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24169,'[]','[]'),(24170,'{\"_title\":\"Client\",\"en\":\"Client\",\"ru\":\"Client\",\"type\":\"object\",\"visible\":1,\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24171,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24172,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24173,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24174,'{\"en\":\"Sex\",\"ru\":\"Sexe\",\"_title\":\"sex\",\"type\":\"_sex\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\"}','[]'),(24175,'{\"en\":\"Birthday\",\"ru\":\"Anniversaire\",\"_title\":\"birth\",\"type\":\"date\",\"order\":\"6\"}','[]'),(24176,'{\"en\":\"Citizenship\",\"ru\":\"Citoyenneté\",\"_title\":\"citizenship\",\"type\":\"combo\",\"order\":\"7\",\"cfg\":\"{\\\"thesauriId\\\":\\\"310\\\"}\"}','[]'),(24177,'{\"en\":\"Nationality\",\"ru\":\"Nationalité\",\"_title\":\"nationality\",\"type\":\"combo\",\"order\":\"8\",\"cfg\":\"{\\\"thesauriId\\\":\\\"309\\\"}\"}','[]'),(24178,'{\"en\":\"E-mail\",\"ru\":\"E-mail\",\"_title\":\"email\",\"type\":\"varchar\",\"order\":\"10\",\"cfg\":\"{\\\"maxInstances\\\":\\\"3\\\"}\"}','[]'),(24179,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"14\"}','[]'),(24180,'{\"en\":\"Address\",\"ru\":\"Adresse\",\"_title\":\"address\",\"type\":\"varchar\",\"order\":\"13\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24181,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"11\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24182,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"popuplist\",\"order\":\"4\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"324\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24183,'{\"_title\":\"Organization\",\"en\":\"Organization\",\"ru\":\"Organisation\",\"type\":\"object\",\"visible\":1,\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24184,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24185,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24186,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24187,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"11\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24188,'{\"en\":\"Fax\",\"ru\":\"Télécopieur\",\"_title\":\"фах\",\"type\":\"varchar\",\"order\":\"12\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24189,'{\"en\":\"Postal index\",\"ru\":\"Indice postal\",\"_title\":\"postal_index\",\"type\":\"varchar\",\"order\":\"13\"}','[]'),(24190,'{\"en\":\"Address\",\"ru\":\"Adresse\",\"_title\":\"address\",\"type\":\"varchar\",\"order\":\"14\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24191,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"16\"}','[]'),(24192,'{\"en\":\"Regions\",\"ru\":\"Régions\",\"_title\":\"regions\",\"type\":\"popuplist\",\"order\":\"15\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"283\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24193,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"popuplist\",\"order\":\"4\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"277\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24194,'{\"_title\":\"Test\",\"en\":\"Test\",\"ru\":\"Test\",\"visible\":1,\"iconCls\":\"icon-none\"}','[]'),(24195,'{\"_title\":\"Action\",\"en\":\"Action\",\"ru\":\"Action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-petition\",\"cfg\":\"[]\"}','[]'),(24196,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24197,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24198,'{\"_title\":\"content\",\"en\":\"Content\",\"ru\":\"Content\",\"type\":\"html\",\"order\":10,\"cfg\":\"{\\\"showIn\\\": \\\"tabSheet\\\"}\"}','[]'),(24199,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"_objects\",\"order\":5,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24265\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\\n\",\"solr_column_name\":\"category_id\"}','[]'),(24200,'{\"_title\":\"tags\",\"en\":\"Tags\",\"ru\":\"Tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24399\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\"}','[]'),(24201,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24202,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_fieldTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(24203,'{\"en\":\"Order\",\"ru\":\"Order\",\"_title\":\"order\",\"type\":\"int\",\"order\":\"6\",\"cfg\":\"[]\"}','[]'),(24204,'{\"en\":\"Config\",\"ru\":\"Config\",\"_title\":\"cfg\",\"type\":\"text\",\"order\":\"7\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(24205,'{\"en\":\"Solr column name\",\"ru\":\"Solr column name\",\"_title\":\"solr_column_name\",\"type\":\"varchar\",\"order\":\"8\",\"cfg\":\"[]\"}','[]'),(24206,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(24207,'{\"en\":\"Title (ru)\",\"ru\":\"Title (ru)\",\"_title\":\"ru\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"[]\"}','[]'),(24208,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\",\\\"rea-dOnly\\\":true}\"}','[]'),(24209,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(24210,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"6\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24211,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"7\",\"cfg\":\"[]\"}','[]'),(24212,'{\"en\":\"Config\",\"ru\":\"Config\",\"_title\":\"cfg\",\"type\":\"text\",\"order\":\"8\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(24213,'{\"en\":\"Title template\",\"ru\":\"Title template\",\"_title\":\"title_template\",\"type\":\"text\",\"order\":\"9\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(24214,'{\"en\":\"Info template\",\"ru\":\"Info template\",\"_title\":\"info_template\",\"type\":\"text\",\"order\":\"10\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(24215,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(24216,'{\"en\":\"Title (ru)\",\"ru\":\"Title (ru)\",\"_title\":\"ru\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"[]\"}','[]'),(24217,'{\"_title\":\"Thesauri Item\",\"en\":\"Thesauri Item\",\"ru\":\"Thesauri Item\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-blue-document-small\",\"title_template\":\"{en}\"}','[]'),(24218,'{\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":5,\"cfg\":null}','[]'),(24219,'{\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":6,\"cfg\":null}','[]'),(24220,'{\"_title\":\"order\",\"type\":\"int\",\"order\":7,\"cfg\":null}','[]'),(24221,'{\"_title\":\"en\",\"type\":\"varchar\",\"order\":0,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24222,'{\"_title\":\"ru\",\"type\":\"varchar\",\"order\":1,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24223,'{\"_title\":\"System\"}','[]'),(24224,'{\"_title\":\"Phases\"}','[]'),(24225,'{\"en\":\"preliminary check\",\"visible\":1,\"order\":\"1\"}','[]'),(24226,'{\"en\":\"investigation\",\"visible\":1,\"order\":\"2\"}','[]'),(24227,'{\"en\":\"court\",\"visible\":1,\"order\":\"3\"}','[]'),(24228,'{\"en\":\"civil claim\",\"visible\":1,\"order\":\"4\"}','[]'),(24229,'{\"en\":\"ECHR\",\"visible\":1,\"order\":\"5\"}','[]'),(24239,'{\"_title\":\"Responsible party\"}','[]'),(24240,'{\"en\":\"OSJI\",\"ru\":\"OSJI\",\"visible\":1,\"order\":\"1\"}','[]'),(24241,'{\"en\":\"State\",\"ru\":\"State\",\"visible\":1,\"order\":\"2\"}','[]'),(24242,'{\"en\":\"ECHR\",\"ru\":\"ECHR\",\"visible\":1,\"order\":\"3\"}','[]'),(24243,'{\"_title\":\"Files\"}','[]'),(24244,'{\"en\":\"Research\",\"ru\":\"Research\",\"visible\":1,\"order\":\"1\"}','[]'),(24245,'{\"en\":\"CaseLaw\",\"ru\":\"CaseLaw\",\"visible\":1,\"order\":\"2\"}','[]'),(24246,'{\"en\":\"EDR\",\"ru\":\"EDR\",\"visible\":1,\"order\":\"3\"}','[]'),(24247,'{\"en\":\"Exhibit\",\"ru\":\"Exhibit\",\"visible\":1,\"order\":\"4\"}','[]'),(24248,'{\"_title\":\"Case Folders\"}','[]'),(24249,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24250,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24251,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24252,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24253,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24254,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24255,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24256,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24257,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24258,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24259,'{\"_title\":\"Case statuses\"}','[]'),(24260,'{\"en\":\"Active\",\"ru\":\"Actif\",\"visible\":1,\"order\":\"2\"}','[]'),(24261,'{\"en\":\"Closed\",\"ru\":\"Fermé\",\"visible\":1,\"order\":\"4\"}','[]'),(24262,'{\"en\":\"Archived\",\"ru\":\"Archivé\",\"visible\":1,\"order\":\"5\"}','[]'),(24263,'{\"en\":\"Withdrawn\",\"visible\":1,\"order\":\"3\"}','[]'),(24264,'{\"en\":\"Under consideration\",\"ru\":\"À l\'étude\",\"visible\":1,\"order\":\"1\"}','[]'),(24265,'{\"_title\":\"Office\"}','[]'),(24266,'{\"en\":\"CAT\",\"ru\":\"CAT\",\"security_group\":242,\"managers\":\"256,240\",\"iconCls\":\"task-blue\",\"visible\":1,\"order\":\"2\"}','[]'),(24267,'{\"en\":\"ECD\",\"ru\":\"ECD\",\"security_group\":243,\"managers\":\"1,5,256\",\"iconCls\":\"task-green\",\"visible\":1,\"order\":\"3\"}','[]'),(24268,'{\"en\":\"FOIE\",\"ru\":\"FOIE\",\"security_group\":244,\"managers\":\"265\",\"iconCls\":\"task-orange\",\"visible\":1,\"order\":\"4\"}','[]'),(24269,'{\"en\":\"ICJ\",\"ru\":\"ICJ\",\"security_group\":245,\"iconCls\":\"task-purple\",\"visible\":1,\"order\":\"5\"}','[]'),(24270,'{\"en\":\"LRC\",\"ru\":\"LRC\",\"security_group\":246,\"iconCls\":\"task-red\",\"visible\":1,\"order\":\"6\"}','[]'),(24271,'{\"en\":\"MIG\",\"ru\":\"MIG\",\"security_group\":247,\"iconCls\":\"task-yellow\",\"visible\":1,\"order\":\"7\"}','[]'),(24272,'{\"en\":\"NCJ\",\"ru\":\"NCJ\",\"security_group\":248,\"iconCls\":\"task-olive\",\"visible\":1,\"order\":\"8\"}','[]'),(24273,'{\"en\":\"NSC\",\"security_group\":249,\"managers\":\"31,25\",\"iconCls\":\"task-steel\",\"visible\":1,\"order\":\"9\"}','[]'),(24274,'{\"en\":\"Default\",\"ru\":\"Default\",\"iconCls\":\"task-gray\",\"visible\":1,\"order\":\"1\"}','[]'),(24275,'{\"_title\":\"Fields\"}','[]'),(24276,'{\"_title\":\"yes\\/no\"}','[]'),(24277,'{\"en\":\"yes\",\"visible\":1,\"order\":\"1\"}','[]'),(24278,'{\"en\":\"no\",\"visible\":1,\"order\":\"2\"}','[]'),(24279,'{\"_title\":\"sex\"}','[]'),(24280,'{\"en\":\"male\",\"visible\":1,\"order\":\"1\"}','[]'),(24281,'{\"en\":\"female\",\"visible\":1,\"order\":\"2\"}','[]'),(24282,'{\"_title\":\"checkbox\"}','[]'),(24283,'{\"en\":\"yes\",\"visible\":1,\"order\":\"1\"}','[]'),(24284,'{\"en\":\"no\",\"visible\":1,\"order\":\"2\"}','[]'),(24285,'{\"_title\":\"types of letters\"}','[]'),(24286,'{\"en\":\"response\",\"visible\":1,\"order\":\"1\"}','[]'),(24287,'{\"en\":\"decision\",\"visible\":1,\"order\":\"2\"}','[]'),(24288,'{\"en\":\"communication\",\"visible\":1,\"order\":\"3\"}','[]'),(24289,'{\"en\":\"notification\",\"visible\":1,\"order\":\"4\"}','[]'),(24290,'{\"en\":\"presentation\",\"visible\":1,\"order\":\"5\"}','[]'),(24291,'{\"en\":\"according to the examination check\",\"visible\":1,\"order\":\"6\"}','[]'),(24292,'{\"en\":\"complaint\",\"iconCls\":\"icon-bullet_gray\",\"visible\":1,\"order\":\"7\"}','[]'),(24293,'{\"en\":\"check initiation\",\"visible\":1,\"order\":\"8\"}','[]'),(24294,'{\"en\":\"petition\",\"visible\":1,\"order\":\"9\"}','[]'),(24295,'{\"en\":\"appeal\",\"visible\":1,\"order\":\"10\"}','[]'),(24296,'{\"en\":\"claim\",\"visible\":1,\"order\":\"11\"}','[]'),(24297,'{\"en\":\"informative letter\",\"visible\":1,\"order\":\"12\"}','[]'),(24298,'{\"en\":\"violation\",\"visible\":1,\"order\":\"13\"}','[]'),(24299,'{\"en\":\"complaint of the defendant\",\"iconCls\":\"icon-bullet_gray\",\"visible\":1,\"order\":\"14\"}','[]'),(24300,'{\"_title\":\"Author\"}','[]'),(24301,'{\"en\":\"Court\",\"ru\":\"Cour\",\"visible\":1,\"order\":\"1\"}','[]'),(24302,'{\"en\":\"Applicant\",\"ru\":\"Demandeur\",\"visible\":1,\"order\":\"2\"}','[]'),(24303,'{\"en\":\"Government\",\"ru\":\"Government\",\"visible\":1,\"order\":\"3\"}','[]'),(24304,'{\"_title\":\"Languages\"}','[]'),(24305,'{\"en\":\"Eng\",\"ru\":\"Eng\",\"visible\":1,\"order\":\"1\"}','[]'),(24306,'{\"en\":\"Rus\",\"ru\":\"Rus\",\"visible\":1,\"order\":\"2\"}','[]'),(24307,'{\"en\":\"Uzb\",\"ru\":\"Uzb\",\"visible\":1,\"order\":\"3\"}','[]'),(24308,'{\"_title\":\"Country\"}','[]'),(24309,'{\"en\":\"Kyrgyzstan\",\"ru\":\"Kyrgyzstan\",\"visible\":1,\"order\":\"20\"}','[]'),(24310,'{\"en\":\"Italy\",\"ru\":\"Italy\",\"visible\":1,\"order\":\"17\"}','[]'),(24311,'{\"en\":\"Macedonia\",\"ru\":\"Macedonia\",\"visible\":1,\"order\":\"22\"}','[]'),(24312,'{\"en\":\"Germany\",\"ru\":\"Germany\",\"visible\":1,\"order\":\"14\"}','[]'),(24313,'{\"en\":\"Russia\",\"visible\":1,\"order\":\"27\"}','[]'),(24314,'{\"en\":\"Turkey\",\"visible\":1,\"order\":\"31\"}','[]'),(24315,'{\"en\":\"Romania\",\"visible\":1,\"order\":\"26\"}','[]'),(24316,'{\"en\":\"Poland\",\"visible\":1,\"order\":\"25\"}','[]'),(24317,'{\"en\":\"Czech Republic\",\"visible\":1,\"order\":\"9\"}','[]'),(24318,'{\"en\":\"Israel\",\"visible\":1,\"order\":\"16\"}','[]'),(24319,'{\"en\":\"Kenya\",\"visible\":1,\"order\":\"19\"}','[]'),(24320,'{\"en\":\"Kazakhstan\",\"visible\":1,\"order\":\"18\"}','[]'),(24321,'{\"en\":\"Slovenia\",\"visible\":1,\"order\":\"29\"}','[]'),(24322,'{\"en\":\"Bulgaria\",\"visible\":1,\"order\":\"4\"}','[]'),(24323,'{\"en\":\"Gambia\",\"visible\":1,\"order\":\"13\"}','[]'),(24324,'{\"en\":\"Switzerland\",\"visible\":1,\"order\":\"30\"}','[]'),(24325,'{\"en\":\"Netherlands\",\"visible\":1,\"order\":\"24\"}','[]'),(24326,'{\"en\":\"Dominican Republic\",\"visible\":1,\"order\":\"11\"}','[]'),(24327,'{\"en\":\"Angola\",\"visible\":1,\"order\":\"1\"}','[]'),(24328,'{\"en\":\"Equatorial Guinea\",\"visible\":1,\"order\":\"12\"}','[]'),(24329,'{\"en\":\"Bosnia and Herzegovina\",\"visible\":1,\"order\":\"2\"}','[]'),(24330,'{\"en\":\"Denmark\",\"visible\":1,\"order\":\"10\"}','[]'),(24331,'{\"en\":\"Lithuania\",\"visible\":1,\"order\":\"21\"}','[]'),(24332,'{\"en\":\"Côte d\'Ivoire\",\"visible\":1,\"order\":\"8\"}','[]'),(24333,'{\"en\":\"Chile\",\"visible\":1,\"order\":\"6\"}','[]'),(24334,'{\"en\":\"Hungary\",\"visible\":1,\"order\":\"15\"}','[]'),(24335,'{\"en\":\"Mauritania\",\"visible\":1,\"order\":\"23\"}','[]'),(24336,'{\"en\":\"Cameroon\",\"visible\":1,\"order\":\"5\"}','[]'),(24337,'{\"en\":\"Botswana\",\"visible\":1,\"order\":\"3\"}','[]'),(24338,'{\"en\":\"Rwanda\",\"visible\":1,\"order\":\"28\"}','[]'),(24339,'{\"en\":\"Costa Rica\",\"visible\":1,\"order\":\"7\"}','[]'),(24340,'{\"_title\":\"Position\"}','[]'),(24341,'{\"en\":\"Administrative Associate \",\"ru\":\"Associate administrative\",\"visible\":1,\"order\":\"1\"}','[]'),(24342,'{\"en\":\"Associate Legal Officer\",\"ru\":\"Juriste adjoint\",\"visible\":1,\"order\":\"2\"}','[]'),(24343,'{\"en\":\"Communications Officer\",\"ru\":\"Agente des communications\",\"visible\":1,\"order\":\"3\"}','[]'),(24344,'{\"en\":\"Director\",\"ru\":\"Directeur\",\"visible\":1,\"order\":\"4\"}','[]'),(24345,'{\"en\":\"Director of Administration\",\"ru\":\"Director d\'administration\",\"visible\":1,\"order\":\"5\"}','[]'),(24346,'{\"en\":\"Director of Programs\",\"ru\":\"Directeur des programmes\",\"visible\":1,\"order\":\"6\"}','[]'),(24347,'{\"en\":\"Executive Assistant\",\"ru\":\"Assistante de direction\",\"visible\":1,\"order\":\"7\"}','[]'),(24348,'{\"en\":\"Executive Director\",\"ru\":\"Directeur exécutif\",\"visible\":1,\"order\":\"8\"}','[]'),(24349,'{\"en\":\"Intern\",\"ru\":\"Interne\",\"visible\":1,\"order\":\"9\"}','[]'),(24350,'{\"en\":\"KRT Monitor\",\"ru\":\"KRT moniteur\",\"visible\":1,\"order\":\"10\"}','[]'),(24351,'{\"en\":\"Lawyer\",\"ru\":\"Avocat\",\"visible\":1,\"order\":\"11\"}','[]'),(24352,'{\"en\":\"Legal Intern\",\"ru\":\"Stagiaire juridique\",\"visible\":1,\"order\":\"12\"}','[]'),(24353,'{\"en\":\"Legal Officer\",\"ru\":\"Conseiller juridique\",\"visible\":1,\"order\":\"13\"}','[]'),(24354,'{\"en\":\"Litigation Director\",\"ru\":\"Directeur des litiges\",\"visible\":1,\"order\":\"14\"}','[]'),(24355,'{\"en\":\"Litigation Fellow\",\"ru\":\"Contentieux boursier\",\"visible\":1,\"order\":\"15\"}','[]'),(24356,'{\"en\":\"Policy Officer\",\"ru\":\"Responsable de la politique\",\"visible\":1,\"order\":\"16\"}','[]'),(24357,'{\"en\":\"Program Assistant\",\"ru\":\"Assistant de programme\",\"visible\":1,\"order\":\"17\"}','[]'),(24358,'{\"en\":\"Program Associate\",\"ru\":\"Associé au programme\",\"visible\":1,\"order\":\"18\"}','[]'),(24359,'{\"en\":\"Program Coordinator\",\"ru\":\"Coordonnateur du programme\",\"visible\":1,\"order\":\"19\"}','[]'),(24360,'{\"en\":\"Program Officer\",\"ru\":\"Agent de programme\",\"visible\":1,\"order\":\"20\"}','[]'),(24361,'{\"en\":\"Project Coordinator\",\"ru\":\"Coordinateur du projet\",\"visible\":1,\"order\":\"21\"}','[]'),(24362,'{\"en\":\"Project Manager\",\"ru\":\"Chef de projet\",\"visible\":1,\"order\":\"22\"}','[]'),(24363,'{\"en\":\"Resident Fellow\",\"ru\":\"Compatriotes résident\",\"visible\":1,\"order\":\"23\"}','[]'),(24364,'{\"en\":\"Senior Advisor\",\"ru\":\"Conseiller principal\",\"visible\":1,\"order\":\"24\"}','[]'),(24365,'{\"en\":\"Senior Advocacy Advisor\",\"ru\":\"Conseiller principal plaidoyer\",\"visible\":1,\"order\":\"25\"}','[]'),(24366,'{\"en\":\"Senior Advocacy Officer\",\"ru\":\"Officier supérieur de plaidoyer\",\"visible\":1,\"order\":\"26\"}','[]'),(24367,'{\"en\":\"Senior Attorney\",\"ru\":\"Avocat principal\",\"visible\":1,\"order\":\"27\"}','[]'),(24368,'{\"en\":\"Senior Legal Advisor\",\"ru\":\"Conseiller juridique principal\",\"visible\":1,\"order\":\"28\"}','[]'),(24369,'{\"en\":\"Senior Legal Officer\",\"ru\":\"Juriste principal\",\"visible\":1,\"order\":\"29\"}','[]'),(24370,'{\"en\":\"Senior Officer\",\"ru\":\"Officier supérieur\",\"visible\":1,\"order\":\"30\"}','[]'),(24371,'{\"en\":\"Senior Project Manager\",\"ru\":\"Chef de projet senior\",\"visible\":1,\"order\":\"31\"}','[]'),(24372,'{\"en\":\"Temporary Program Coordinator\",\"visible\":1,\"order\":\"32\"}','[]'),(24373,'{\"_title\":\"Location\"}','[]'),(24374,'{\"en\":\"Abuja\",\"ru\":\"Abuja\",\"visible\":1,\"order\":\"1\"}','[]'),(24375,'{\"en\":\"Amsterdam\",\"ru\":\"Amsterdam\",\"visible\":1,\"order\":\"2\"}','[]'),(24376,'{\"en\":\"Bishkek\",\"ru\":\"Bishkek\",\"visible\":1,\"order\":\"3\"}','[]'),(24377,'{\"en\":\"Brussels\",\"ru\":\"Brussels\",\"visible\":1,\"order\":\"4\"}','[]'),(24378,'{\"en\":\"Budapest\",\"ru\":\"Budapest\",\"visible\":1,\"order\":\"5\"}','[]'),(24379,'{\"en\":\"Cambodia\",\"ru\":\"Cambodia\",\"visible\":1,\"order\":\"6\"}','[]'),(24380,'{\"en\":\"Geneva\",\"ru\":\"Geneva\",\"visible\":1,\"order\":\"7\"}','[]'),(24381,'{\"en\":\"London\",\"ru\":\"London\",\"visible\":1,\"order\":\"8\"}','[]'),(24382,'{\"en\":\"Madrid\",\"ru\":\"Madrid\",\"visible\":1,\"order\":\"9\"}','[]'),(24383,'{\"en\":\"Mexico City\",\"ru\":\"Mexico City\",\"visible\":1,\"order\":\"10\"}','[]'),(24384,'{\"en\":\"New York\",\"ru\":\"New York\",\"visible\":1,\"order\":\"11\"}','[]'),(24385,'{\"en\":\"Paris\",\"ru\":\"Paris\",\"visible\":1,\"order\":\"12\"}','[]'),(24386,'{\"en\":\"Santo Domingo\",\"ru\":\"Santo Domingo\",\"visible\":1,\"order\":\"13\"}','[]'),(24387,'{\"en\":\"The Hague\",\"ru\":\"The Hague\",\"visible\":1,\"order\":\"14\"}','[]'),(24388,'{\"en\":\"Tirana\",\"ru\":\"Tirana\",\"visible\":1,\"order\":\"15\"}','[]'),(24389,'{\"en\":\"Washington\",\"ru\":\"Washington\",\"visible\":1,\"order\":\"16\"}','[]'),(24390,'{\"_title\":\"Court\"}','[]'),(24391,'{\"en\":\"ECHR\",\"visible\":1,\"order\":\"1\"}','[]'),(24392,'{\"en\":\"ACHPR\",\"visible\":1,\"order\":\"2\"}','[]'),(24393,'{\"en\":\"UNHRC\",\"visible\":1,\"order\":\"3\"}','[]'),(24394,'{\"en\":\"IACHR\",\"visible\":1,\"order\":\"4\"}','[]'),(24395,'{\"en\":\"CAT\",\"visible\":1,\"order\":\"5\"}','[]'),(24396,'{\"en\":\"UNCAT\",\"visible\":1,\"order\":\"6\"}','[]'),(24397,'{\"en\":\"ECOWAS\",\"visible\":1,\"order\":\"7\"}','[]'),(24398,'{\"en\":\"Domestic Court\",\"visible\":1,\"order\":\"8\"}','[]'),(24399,'{\"_title\":\"Tags\"}','[]'),(24400,'{\"en\":\"Citizenship\",\"visible\":1,\"order\":\"1\"}','[]'),(24401,'{\"en\":\"Discrimination\",\"visible\":1,\"order\":\"2\"}','[]'),(24402,'{\"en\":\"Family Unification\",\"visible\":1,\"order\":\"3\"}','[]'),(24403,'{\"en\":\"Torture\",\"visible\":1,\"order\":\"4\"}','[]'),(24404,'{\"en\":\"Rendition\",\"visible\":1,\"order\":\"5\"}','[]'),(24405,'{\"en\":\"Statelessness\",\"visible\":1,\"order\":\"6\"}','[]'),(24406,'{\"en\":\"Natural resources\",\"visible\":1,\"order\":\"7\"}','[]'),(24407,'{\"en\":\"Corruption\",\"visible\":1,\"order\":\"8\"}','[]'),(24408,'{\"en\":\"Spoliation\",\"visible\":1,\"order\":\"9\"}','[]'),(24409,'{\"en\":\"Unjust enrichment\",\"visible\":1,\"order\":\"10\"}','[]'),(24410,'{\"en\":\"Money laundering\",\"visible\":1,\"order\":\"11\"}','[]'),(24411,'{\"en\":\"Roma\",\"visible\":1,\"order\":\"12\"}','[]'),(24412,'{\"en\":\"Inhuman treatment\",\"visible\":1,\"order\":\"13\"}','[]'),(24413,'{\"en\":\"Right to information\",\"visible\":1,\"order\":\"14\"}','[]'),(24414,'{\"en\":\"Right to truth\",\"visible\":1,\"order\":\"15\"}','[]'),(24415,'{\"en\":\"Access to information\",\"visible\":1,\"order\":\"16\"}','[]'),(24416,'{\"en\":\"Education\",\"visible\":1,\"order\":\"17\"}','[]'),(24417,'{\"en\":\"Ethnic profiling\",\"visible\":1,\"order\":\"18\"}','[]'),(24418,'{\"en\":\"Database\",\"visible\":1,\"order\":\"19\"}','[]'),(24419,'{\"en\":\"Freedom of expression\",\"visible\":1,\"order\":\"20\"}','[]'),(24420,'{\"en\":\"Freedom of information\",\"visible\":1,\"order\":\"21\"}','[]'),(24421,'{\"en\":\"Central Asia\",\"visible\":1,\"order\":\"22\"}','[]'),(24422,'{\"en\":\"War Crime\",\"visible\":1,\"order\":\"23\"}','[]'),(24423,'{\"en\":\"Investigation\",\"visible\":1,\"order\":\"24\"}','[]'),(24424,'{\"en\":\"Interrogation\",\"visible\":1,\"order\":\"25\"}','[]'),(24425,'{\"en\":\"Ineffective investigation\",\"visible\":1,\"order\":\"26\"}','[]'),(24426,'{\"en\":\"Police custody\",\"visible\":1,\"order\":\"27\"}','[]'),(24427,'{\"en\":\"PTD\",\"visible\":1,\"order\":\"28\"}','[]'),(24428,'{\"en\":\"Pretrial Detention\",\"visible\":1,\"order\":\"29\"}','[]'),(24429,'{\"en\":\"Impunity\",\"visible\":1,\"order\":\"30\"}','[]'),(24430,'{\"en\":\"Nationality\",\"visible\":1,\"order\":\"31\"}','[]'),(24431,'{\"en\":\"Public watchdog\",\"visible\":1,\"order\":\"32\"}','[]'),(24432,'{\"en\":\"NGO\",\"visible\":1,\"order\":\"33\"}','[]'),(24433,'{\"en\":\"Ill-treatment\",\"visible\":1,\"order\":\"34\"}','[]'),(24434,'{\"en\":\"Journalist\",\"visible\":1,\"order\":\"35\"}','[]'),(24435,'{\"en\":\"Defamation\",\"visible\":1,\"order\":\"36\"}','[]'),(24436,'{\"en\":\"Right to life\",\"visible\":1,\"order\":\"37\"}','[]'),(24437,'{\"en\":\"Death in custody\",\"visible\":1,\"order\":\"38\"}','[]'),(24438,'{\"en\":\"Press freedom\",\"visible\":1,\"order\":\"39\"}','[]'),(24439,'{\"en\":\"Racial profiling\",\"visible\":1,\"order\":\"40\"}','[]'),(24440,'{\"en\":\"Fair trial\",\"visible\":1,\"order\":\"41\"}','[]'),(24441,'{\"en\":\"Alex Evdokimov\",\"ru\":\"Alex Evdokimov\",\"iconCls\":\"icon-user-m\",\"visible\":1}','[]'),(24442,'{\"en\":\"Oleg Burlaca\",\"ru\":\"Oleg Burlaca\",\"visible\":1}','[]'),(24443,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24444,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24445,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24446,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24447,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24448,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24449,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24450,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24451,'{\"_title\":\"category\",\"en\":\"Programs\",\"ru\":\"Programs\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24265\\n,\\\"value\\\": 24274\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\"}','[]'),(24452,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24453,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24454,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24455,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24456,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24457,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24458,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24459,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24460,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24461,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24462,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24463,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24464,'{\"_title\":\"category\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n                \\\"source\\\": \\\"tree\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"scope\\\": 715\\n                ,\\\"value\\\": \\n            }\",\"en\":\"Category\",\"ru\":\"Category\"}','[]'),(24465,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24466,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24467,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24468,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24469,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24470,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24471,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24472,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24473,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24474,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24475,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24476,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24477,'{\"_title\":\"category\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n                \\\"source\\\": \\\"tree\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"scope\\\": 715\\n                ,\\\"value\\\": \\n            }\",\"en\":\"Category\",\"ru\":\"Category\"}','[]'),(24478,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24479,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24480,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24481,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24483,'{\"_title\":\"Test ECD group tasks\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-21T12:46:24\",\"date_end\":\"2014-01-28T00:00:00\",\"datetime_start\":\"2014-01-21T12:46:24\"}},\"assigned\":\"1,5,7,8\",\"importance\":1,\"category\":24267,\"reminders\":{\"childs\":[]}}','[]'),(24484,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-object8\",\"title_template\":\"{en}\"}','[]'),(24485,'{\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":5,\"cfg\":null}','[]'),(24486,'{\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":6,\"cfg\":null}','[]'),(24487,'{\"_title\":\"order\",\"type\":\"int\",\"order\":7,\"cfg\":null}','[]'),(24488,'{\"_title\":\"en\",\"type\":\"varchar\",\"order\":0,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24489,'{\"_title\":\"ru\",\"type\":\"varchar\",\"order\":1,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24490,'{\"_title\":\"managers\",\"en\":\"Managers\",\"ru\":\"Менеджеры\",\"type\":\"_objects\",\"order\":3,\"cfg\":\"{\\n\\\"editor\\\": \\\"form\\\"\\n,\\\"source\\\": \\\"users\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"multiValued\\\": true\\n,\\\"faceting\\\": true\\n}\",\"solr_column_name\":\"user_ids\"}','[]'),(24492,'{\"_title\":\"test111\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-21T15:57:28\",\"date_end\":\"2014-01-23T00:00:00\",\"datetime_start\":\"2014-01-21T15:57:28\"}},\"assigned\":\"4\",\"importance\":1,\"category\":24274,\"description\":\"asd\",\"reminders\":{\"childs\":[]}}','[]'),(24495,'[]','[]'),(24496,'[]','[]'),(24497,'[]','[]'),(24503,'[]','[]'),(24504,'{\"en\":\"gray\",\"ru\":\"серый\",\"iconCls\":\"task-gray\",\"visible\":1,\"order\":10}','[]'),(24505,'{\"en\":\"blue\",\"ru\":\"синий\",\"iconCls\":\"task-blue\",\"visible\":1,\"order\":20}','[]'),(24506,'{\"en\":\"green\",\"ru\":\"зелёный\",\"iconCls\":\"task-green\",\"visible\":1,\"order\":30}','[]'),(24507,'{\"en\":\"orange\",\"ru\":\"оранжевый\",\"iconCls\":\"task-orange\",\"visible\":1,\"order\":40}','[]'),(24508,'{\"en\":\"teal\",\"ru\":\"бирюзовый\",\"iconCls\":\"task-teal\",\"visible\":1,\"order\":45}','[]'),(24509,'{\"en\":\"purple\",\"ru\":\"фиолетовый\",\"iconCls\":\"task-purple\",\"visible\":1,\"order\":50}','[]'),(24510,'{\"en\":\"red\",\"ru\":\"красный\",\"iconCls\":\"task-red\",\"visible\":1,\"order\":60}','[]'),(24511,'{\"en\":\"yellow\",\"ru\":\"желтый\",\"iconCls\":\"task-yellow\",\"visible\":1,\"order\":70}','[]'),(24512,'{\"en\":\"olive\",\"ru\":\"оливковый\",\"iconCls\":\"task-olive\",\"visible\":1,\"order\":80}','[]'),(24513,'{\"en\":\"steel\",\"ru\":\"сталь\",\"iconCls\":\"task-steel\",\"visible\":1,\"order\":90}','[]'),(24514,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24515,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24516,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24517,'{\"_title\":\"security_group\",\"en\":\"Users group\",\"ru\":\"Группа пользователей\",\"type\":\"_objects\",\"order\":2,\"cfg\":\"{\\n\\\"source\\\": \\\"groups\\\"\\n,\\\"autoLoad\\\": true\\n}\"}','[]'),(24521,'{\"_title\":\"Step2\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-22T15:25:35\",\"datetime_start\":\"2014-01-22T15:25:35\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24523,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Название\",\"type\":\"varchar\",\"order\":1}','[]'),(24525,'{\"_title\":\"2222\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-22T16:40:10\",\"datetime_start\":\"2014-01-22T16:40:10\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24526,'{\"status\":24260,\"_title\":\"Test sys folders\"}','[]'),(24527,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24528,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24529,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24530,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24531,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24532,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24533,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24534,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24535,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24536,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24540,'{\"status\":24260,\"_title\":\"Неш Програм Цреате програм тест\",\"program\":\"24267\",\"lead\":\"\",\"support\":\"\"}','[]'),(24541,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24542,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24543,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24544,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24545,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24546,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24547,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24548,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24549,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24550,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24552,'{\"_title\":\"DateTime task 1\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-01-18T09:00:00.000Z\",\"datetime_end\":\"2014-01-27T12:00:00.000Z\"}},\"assigned\":\"6,25,9\",\"importance\":\"1\",\"category\":24274,\"color\":24508,\"description\":\"2\",\"reminders\":{\"childs\":{\"count\":110,\"units\":\"1\"}}}','[]'),(24553,'{\"_title\":\"Selft task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-09T00:00:00\",\"date_end\":\"2014-01-16T23:59:59\",\"datetime_start\":\"2014-01-27T08:06:23.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24554,'{\"_title\":\"Test 2\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-07T00:00:00\",\"date_end\":\"2014-01-14T23:59:59\",\"datetime_start\":\"2014-01-27T08:21:38.000Z\"}},\"assigned\":\"6\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24555,'{\"_title\":\"123\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-06T00:00:00\",\"date_end\":\"2014-01-07T23:59:59\",\"datetime_start\":\"2014-01-27T08:26:29.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24556,'{\"_title\":\"T1\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-27T11:39:29\",\"datetime_start\":\"2014-01-27T09:39:29.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"color\":24506,\"reminders\":{\"childs\":[]}}','[]'),(24557,'{\"_title\":\"T2\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-01-01T00:00:00\",\"date_end\":\"2014-01-02T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24558,'{\"_title\":\"T3\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-08T00:00:00\",\"date_end\":\"2014-01-15T23:59:59\",\"datetime_start\":\"2014-01-28T09:42:52.000Z\"}},\"assigned\":\"1\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24560,'{\"_title\":\"T4\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-29T00:00:00\",\"date_end\":\"2014-01-30T23:59:59\",\"datetime_start\":\"2014-01-28T10:21:51.000Z\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24561,'{\"_title\":\"Task created by Oleg\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-01-29T14:00:00.000Z\",\"datetime_end\":\"2014-01-29T22:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24513,\"description\":\"some description here\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24564,'{\"_title\":\"Scan a document\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-29T17:36:43\",\"date_end\":\"2014-01-31T00:00:00\",\"datetime_start\":\"2014-01-29T15:36:43.000Z\"}},\"assigned\":\"263,240\",\"importance\":1,\"category\":\"24267,24271,24266\",\"color\":24512,\"description\":\"Nice info\",\"reminders\":{\"childs\":[]}}','[]'),(24565,'{\"_title\":\"todays task\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-01-31T00:00:00\",\"date_end\":\"2014-02-01T00:00:00\"}},\"assigned\":\"240,262,263\",\"importance\":\"1\",\"category\":24274,\"color\":24513,\"description\":\"cool task\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24577,'{\"_title\":\"New folder\"}','[]'),(24588,'{\"_title\":\"Apples\",\"nr\":\"88776644\",\"_date_start\":\"2014-01-31T00:00:00\",\"_date_end\":\"2014-03-27T00:00:00\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24332\",\"status\":24260,\"tags\":\"24407\",\"lead\":\"256\",\"support\":\"240\"}','[]'),(24589,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24590,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24591,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24592,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24593,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24594,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24595,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24596,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24597,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24598,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24600,'{\"_title\":\"Test\"}','[]'),(24601,'{\"_title\":\"cool case\"}','[]'),(24602,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24603,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24604,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24605,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24606,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24607,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24608,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24609,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24610,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24611,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24612,'{\"_title\":\"Test\"}','[]'),(24613,'{\"_title\":\"0-Incoming\"}','[]'),(24614,'{\"_title\":\"0. Incoming\"}','[]'),(24615,'{\"_title\":\"1-Summaries\"}','[]'),(24616,'{\"_title\":\"1. Correspondence\"}','[]'),(24617,'{\"_title\":\"My Case\"}','[]'),(24618,'{\"_title\":\"1-Summaries\"}','[]'),(24619,'{\"_title\":\"2-Correspondence\"}','[]'),(24620,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24621,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24622,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24623,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24624,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24625,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24626,'{\"_title\":\"0-Incoming\"}','[]'),(24627,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24628,'{\"_title\":\"subfolder\"}','[]'),(24629,'{\"_title\":\"another case\",\"nr\":\"02\"}','[]'),(24630,'{\"_title\":\"1-Summaries\"}','[]'),(24631,'{\"_title\":\"2-Correspondence\"}','[]'),(24632,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24633,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24634,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24635,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24636,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24637,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24638,'{\"_title\":\"0-Incoming\"}','[]'),(24639,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24640,'{\"_title\":\"subfolder\"}','[]'),(24646,'{\"content\":\"text here\",\"program\":\"24270,24267\",\"_title\":\"some case\",\"_date_start\":\"2014-02-19T00:00:00\",\"tags\":\"24400\"}','[]'),(24647,'{\"_title\":\"3-Meetings\"}','[]'),(24648,'{\"_title\":\"2. Filings\"}','[]'),(24649,'{\"_title\":\"5-Filings\"}','[]'),(24650,'{\"_title\":\"3. Evidence\"}','[]'),(24651,'{\"_title\":\"7-Advocacy\"}','[]'),(24652,'{\"_title\":\"4. Research\"}','[]'),(24653,'{\"_title\":\"5. Administrative\"}','[]'),(24654,'{\"_title\":\"My case here\",\"nr\":\"001\",\"_date_start\":\"2014-02-05T00:00:00\",\"program\":\"24266\",\"status\":24260,\"lead\":\"240\"}','[]'),(24655,'{\"_title\":\"0-Incoming\"}','[]'),(24656,'{\"_title\":\"1-Summaries\"}','[]'),(24657,'{\"_title\":\"2-Correspondence\"}','[]'),(24658,'{\"_title\":\"subfolder\"}','[]'),(24659,'{\"_title\":\"3-Meetings\"}','[]'),(24660,'{\"_title\":\"4-Filings\"}','[]'),(24661,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24662,'{\"_title\":\"6-Evidence\"}','[]'),(24663,'{\"_title\":\"7-Advocacy\"}','[]'),(24664,'{\"_title\":\"8-Research\"}','[]'),(24665,'{\"_title\":\"9-Administrative\"}','[]'),(24666,'{\"_title\":\"Here we go\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-05T00:00:00\",\"date_end\":\"2014-02-08T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24511,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24667,'{\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-12T08:00:00.000Z\",\"datetime_end\":\"2014-02-12T09:00:00.000Z\"}},\"importance\":\"1\",\"category\":24274,\"color\":24505,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24668,'{\"_title\":\"Implement scanning\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-17T00:00:00\",\"date_end\":\"2014-02-20T00:00:00\"}},\"assigned\":\"262,263\",\"importance\":\"1\",\"category\":\"\",\"color\":24509,\"description\":\"and also OCR!\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24669,'{\"_title\":\"Do this\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-03T11:54:37\",\"datetime_start\":\"2014-02-03T09:54:37.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24679,'{\"_title\":\"Bla bla\",\"status\":24260,\"lead\":\"240,256\",\"support\":\"265\"}','[]'),(24680,'{\"_title\":\"0-Incoming\"}','[]'),(24681,'{\"_title\":\"1-Summaries\"}','[]'),(24682,'{\"_title\":\"2-Correspondence\"}','[]'),(24683,'{\"_title\":\"3-Meetings\"}','[]'),(24684,'{\"_title\":\"4-Filings\"}','[]'),(24685,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24686,'{\"_title\":\"6-Evidence\"}','[]'),(24687,'{\"_title\":\"7-Advocacy\"}','[]'),(24688,'{\"_title\":\"8-Research\"}','[]'),(24689,'{\"_title\":\"9-Administrative\"}','[]'),(24691,'{\"_title\":\"Scan papers\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-03T13:38:25\",\"datetime_start\":\"2014-02-03T12:38:25.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24266,24267\",\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24692,'{\"_title\":\"report to anyone\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\",\"date_end\":\"2012-12-31T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24510,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24693,'{\"_title\":\"Finish reporting module for organization B\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-07T15:45:00.000Z\",\"datetime_end\":\"2014-02-13T14:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24511,\"description\":\"Why do tasks have start and end dates?  I think only events should have start and end dates.  Tasks have fixed deadlines.\",\"reminders\":[{\"childs\":[]},{\"childs\":[]}]}','[]'),(24694,'{\"_title\":\"The reminder functions do not work...what do you mean by count and units?\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-13T23:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":\"24274\",\"color\":24508,\"description\":\"Not answered yet...Kindly clarify.\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24695,'{\"_title\":\"Call the lawyer\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-14T15:00:00.000Z\",\"datetime_end\":\"1990-01-11T21:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24511,\"description\":\"the drop downs for programs are still missing\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24696,'{\"_title\":\"Pears\",\"nr\":\"9898989\",\"_date_start\":\"2010-12-13T00:00:00\",\"_date_end\":\"1996-02-12T00:00:00\",\"court\":\"24391,24395,24392,24396\",\"program\":\"24270,24268,24274\",\"country\":\"24339,24330,24332,24322,24337,24329\",\"status\":24261,\"tags\":\"24417\",\"lead\":\"256\",\"support\":\"240\"}','[]'),(24697,'{\"_title\":\"0-Incoming\"}','[]'),(24698,'{\"_title\":\"1-Summaries\"}','[]'),(24699,'{\"_title\":\"2-Correspondence\"}','[]'),(24700,'{\"_title\":\"3-Meetings\"}','[]'),(24701,'{\"_title\":\"4-Filings\"}','[]'),(24702,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24703,'{\"_title\":\"6-Evidence\"}','[]'),(24704,'{\"_title\":\"7-Advocacy\"}','[]'),(24705,'{\"_title\":\"8-Research\"}','[]'),(24706,'{\"_title\":\"9-Administrative\"}','[]'),(24707,'{\"_title\":\".TemporaryItems\"}','[]'),(24708,'{\"_title\":\"folders.501\"}','[]'),(24709,'{\"_title\":\"TemporaryItems\"}','[]'),(24710,'{\"_title\":\"(A Document Being Saved By TextEdit)\"}','[]'),(24713,'{\"_title\":\"(A Document Being Saved By TextEdit)\"}','[]'),(24716,'{\"_title\":\"test\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"3\",\"color\":24505,\"description\":\"text here, and some new text\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24717,'{\"_title\":\"another test\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"264,240\",\"importance\":\"2\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24718,'{\"_title\":\"testing email\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"description\":\"some task here\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24719,'{\"_title\":\"need to do this\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00Z\",\"date_end\":\"2014-02-27T00:00:00Z\"}},\"assigned\":\"240,256\",\"importance\":\"1\",\"category\":\"24274\",\"color\":24513,\"description\":\"do this before deadline\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24720,'{\"_title\":\"Documents\"}','[]'),(24723,'{\"_title\":\"for Oleg\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T14:44:35\",\"datetime_start\":\"2014-02-06T12:44:35.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"description\":\"this\",\"reminders\":{\"childs\":[]}}','[]'),(24724,'{\"_title\":\"My case W\",\"nr\":\"010\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-03-01T00:00:00Z\",\"court\":\"24391,24395\",\"program\":\"24267,24270\",\"country\":\"24337,24336\",\"status\":24260,\"tags\":\"24418,24435\",\"lead\":\"240,256\",\"support\":\"256,240\"}','[]'),(24725,'{\"_title\":\"0-Incoming\"}','[]'),(24726,'{\"_title\":\"1-Summaries\"}','[]'),(24727,'{\"_title\":\"2-Correspondence\"}','[]'),(24728,'{\"_title\":\"3-Meetings\"}','[]'),(24729,'{\"_title\":\"4-Filings\"}','[]'),(24730,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24731,'{\"_title\":\"6-Evidence\"}','[]'),(24732,'{\"_title\":\"7-Advocacy\"}','[]'),(24733,'{\"_title\":\"8-Research\"}','[]'),(24734,'{\"_title\":\"9-Administrative\"}','[]'),(24735,'{\"_title\":\"one more\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T16:31:31\",\"datetime_start\":\"2014-02-06T14:31:31.000Z\"}},\"assigned\":\"240,232,256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24736,'{\"_title\":\"cool\"}','[]'),(24737,'{\"_title\":\"0-Incoming\"}','[]'),(24738,'{\"_title\":\"1-Summaries\"}','[]'),(24739,'{\"_title\":\"2-Correspondence\"}','[]'),(24740,'{\"_title\":\"3-Meetings\"}','[]'),(24741,'{\"_title\":\"4-Filings\"}','[]'),(24742,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24743,'{\"_title\":\"6-Evidence\"}','[]'),(24744,'{\"_title\":\"7-Advocacy\"}','[]'),(24745,'{\"_title\":\"8-Research\"}','[]'),(24746,'{\"_title\":\"9-Administrative\"}','[]'),(24749,'{\"_title\":\"second apple\",\"program\":\"24266,24267\",\"lead\":\"256\"}','[]'),(24750,'{\"_title\":\"0-Incoming\"}','[]'),(24751,'{\"_title\":\"1-Summaries\"}','[]'),(24752,'{\"_title\":\"2-Correspondence\"}','[]'),(24753,'{\"_title\":\"3-Meetings\"}','[]'),(24754,'{\"_title\":\"4-Filings\"}','[]'),(24755,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24756,'{\"_title\":\"6-Evidence\"}','[]'),(24757,'{\"_title\":\"7-Advocacy\"}','[]'),(24758,'{\"_title\":\"8-Research\"}','[]'),(24759,'{\"_title\":\"9-Administrative\"}','[]'),(24760,'{\"_title\":\"Implement this\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:35:06\",\"datetime_start\":\"2014-02-06T20:35:06.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24761,'{\"_title\":\"notifications doesn\'t work\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:48:57\",\"datetime_start\":\"2014-02-06T20:48:57.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"text here\\n\",\"reminders\":{\"childs\":[]}}','[]'),(24762,'{\"_title\":\"notifications doesn\'t work\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:48:57\",\"datetime_start\":\"2014-02-06T20:48:57.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"text here\\n\",\"reminders\":{\"childs\":[]}}','[]'),(24763,'{\"_title\":\"test that\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:50:28\",\"datetime_start\":\"2014-02-06T20:50:28.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"some info here\",\"reminders\":{\"childs\":[]}}','[]'),(24764,'{\"_title\":\"Apples\",\"nr\":\"7878787\",\"_date_start\":\"2014-02-20T00:00:00\",\"_date_end\":\"2014-02-28T00:00:00\",\"court\":\"24391,24397,24394\",\"program\":\"24270,24268,24274\",\"country\":\"24333,24322,24337,24329\",\"status\":24260,\"tags\":\"24435,24401\",\"lead\":\"256\",\"support\":\"7\"}','[]'),(24765,'{\"_title\":\"0-Incoming\"}','[]'),(24766,'{\"_title\":\"1-Summaries\"}','[]'),(24767,'{\"_title\":\"2-Correspondence\"}','[]'),(24768,'{\"_title\":\"3-Meetings\"}','[]'),(24769,'{\"_title\":\"4-Filings\"}','[]'),(24770,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24771,'{\"_title\":\"6-Evidence\"}','[]'),(24772,'{\"_title\":\"7-Advocacy\"}','[]'),(24773,'{\"_title\":\"8-Research\"}','[]'),(24774,'{\"_title\":\"9-Administrative\"}','[]'),(24775,'{\"_title\":\"some action here\",\"_date_start\":\"2014-02-07T00:00:00\",\"tags\":\"24418\"}','[]'),(24776,'{\"_title\":\"Test task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-13T00:00:00\",\"datetime_start\":\"2014-02-06T22:01:19.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"color\":24510,\"description\":\"test the task\",\"reminders\":{\"childs\":{\"count\":2,\"units\":2}}}','[]'),(24778,'{\"_title\":\"Call the prosecutor\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T18:52:28\",\"datetime_start\":\"2014-02-06T23:52:28.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":\"24274,24267\",\"color\":24510,\"description\":\"Call the prosecutor at 555-5555 to schedule an appointment for the hearing\",\"reminders\":{\"childs\":{\"count\":2,\"units\":3}}}','[]'),(24779,'{\"_title\":\"Spigunov case\",\"status\":24260,\"lead\":\"240\",\"support\":\"256\"}','[]'),(24780,'{\"_title\":\"0-Incoming\"}','[]'),(24781,'{\"_title\":\"1-Summaries\"}','[]'),(24782,'{\"_title\":\"2-Correspondence\"}','[]'),(24783,'{\"_title\":\"3-Meetings\"}','[]'),(24784,'{\"_title\":\"4-Filings\"}','[]'),(24785,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24786,'{\"_title\":\"6-Evidence\"}','[]'),(24787,'{\"_title\":\"7-Advocacy\"}','[]'),(24788,'{\"_title\":\"8-Research\"}','[]'),(24789,'{\"_title\":\"9-Administrative\"}','[]'),(24790,'{\"_title\":\"a phone call\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-07T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":\"24274,24266\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24791,'{\"_title\":\"Testing notifications from new tasks class\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-07T00:00:00\",\"date_end\":\"2014-02-14T00:00:00\"}},\"assigned\":\"240,1\",\"importance\":\"1\",\"category\":24274,\"color\":24504,\"description\":\"testing notifications on user add \",\"reminders\":{\"childs\":{\"count\":\"111\",\"units\":\"2\"}}}','[]'),(24792,'{\"_title\":\"selftask test\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:09:56\",\"datetime_start\":\"2014-02-07T18:09:56.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24268\",\"reminders\":{\"childs\":[]}}','[]'),(24793,'{\"_title\":\"Can you do this? ... maybe?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:25:32\",\"datetime_start\":\"2014-02-07T18:25:32.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24794,'{\"_title\":\"submit documents\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:36:35\",\"datetime_start\":\"2014-02-07T18:36:35.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24795,'{\"_title\":\"dual task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:51:48\",\"datetime_start\":\"2014-02-07T18:51:48.000Z\"}},\"assigned\":\"262\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24801,'{\"_title\":\"Call embassy\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-11T16:00:00.000Z\",\"datetime_end\":\"2014-02-11T16:20:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24512,\"reminders\":{\"childs\":{\"count\":15,\"units\":1}}}','[]'),(24805,'{\"_title\":\"tests\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-11T00:00:00\"}},\"importance\":\"2\",\"category\":24274,\"color\":24504,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":{\"count\":2,\"units\":3}}}','[]'),(24806,'{\"_title\":\"testA\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-11T00:00:00\",\"date_end\":\"2014-02-04T00:00:00\"}},\"importance\":2,\"category\":\"24274,24269,24271\",\"color\":24504,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24810,'{\"_title\":\"test today\",\"nr\":\"01\",\"_date_start\":\"2014-02-20T00:00:00\"}','[]'),(24811,'{\"_title\":\"0-Incoming\"}','[]'),(24812,'{\"_title\":\"1-Summaries\"}','[]'),(24813,'{\"_title\":\"2-Correspondence\"}','[]'),(24814,'{\"_title\":\"3-Meetings\"}','[]'),(24815,'{\"_title\":\"4-Filings\"}','[]'),(24816,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24817,'{\"_title\":\"6-Evidence\"}','[]'),(24818,'{\"_title\":\"7-Advocacy\"}','[]'),(24819,'{\"_title\":\"8-Research\"}','[]'),(24820,'{\"_title\":\"9-Administrative\"}','[]'),(24821,'{\"_title\":\"print letter\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-12T17:25:02\",\"datetime_start\":\"2014-02-12T15:25:02.000Z\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24822,'{\"_title\":\"Comment\",\"en\":\"Comment\",\"ru\":\"Коментарий\",\"type\":\"comment\",\"visible\":1,\"iconCls\":\"icon-balloon\",\"cfg\":\"{\\n  \\\"systemType\\\": 2\\n}\"}','[]'),(24823,'{\"_title\":\"_title\",\"en\":\"Text\",\"ru\":\"Текст\",\"type\":\"memo\",\"order\":0,\"cfg\":\"{\\n\\\"height\\\": 100\\n}\",\"solr_column_name\":\"content\"}','[]'),(24826,'{\"_title\":\"my first case\"}','[]'),(24827,'{\"_title\":\"a second comment here\"}','[]'),(24828,'{\"_title\":\"something new here\"}','[]'),(24829,'{\"_title\":\"good\"}','[]'),(24830,'{\"_title\":\"too sllow\"}','[]'),(24831,'{\"_title\":\"fast\"}','[]'),(24832,'{\"_title\":\"good\"}','[]'),(24834,'{\"_title\":\"why it\'s so slow\"}','[]'),(24843,'{\"_title\":\"I couldn\'t find the case file.  Could you please send it to me via email?\"}','[]'),(24844,'{\"_title\":\"When I hit enter, will my comment be uploaded?\"}','[]'),(24845,'{\"_title\":\"not sure...seems to be a delay\"}','[]'),(24846,'{\"_title\":\"not sure...seems to be a delay\"}','[]'),(24847,'{\"_title\":\"launch\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-19T00:00:00Z\",\"datetime_start\":\"2014-02-19T10:34:00.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24850,'{\"_title\":\"First task for Osborne\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"\",\"datetime_end\":\"2014-02-26T21:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":\"24270,24267,24266,24272\",\"color\":24506,\"reminders\":{\"childs\":{\"count\":2,\"units\":2}}}','[]'),(24851,'{\"_title\":\"First Event for Osborne\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"2014-03-01T01:00:00.000Z\",\"datetime_end\":\"2014-03-01T03:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":24274,\"color\":24506,\"reminders\":{\"childs\":{\"count\":3,\"units\":2}}}','[]'),(24852,'{\"_title\":\"First Milestone for Osborne\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-24T00:00:00Z\",\"datetime_start\":\"2014-02-21T21:31:08.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":\"24273,24271,24272,24268,24266,24269,24270\",\"color\":24506,\"reminders\":{\"childs\":{\"count\":1,\"units\":2}}}','[]'),(24853,'{\"_title\":\"Task within a task?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:03:26.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24854,'{\"_title\":\"a Task within the manager\'s calendar?\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\"}},\"assigned\":\"256,240\",\"importance\":\"1\",\"category\":24274,\"description\":\"This task is set for the 21st, but in the calendar it appears on the 20th\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24855,'{\"_title\":\"a task within a user?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:07:07.000Z\"}},\"assigned\":\"\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24856,'{\"_title\":\"This date should be the 22st of February\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-22T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:14:50.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24857,'{\"_title\":\"Tasks are appearing incorrectly on the calendar\"}','[]'),(24858,'{\"_title\":\"this task is supposed to be on the 21st but it appears on the 20th in the calendar Write a comment...\"}','[]'),(24859,'{\"_title\":\"I\'m going to write a long comment to see if the text wraps\"}','[]'),(24860,'{\"_title\":\"but it doesn\'t and now I cannot see the end of my commentWrite a comment...\"}','[]'),(24861,'{\"_title\":\"Write a comment Write a comment...\"}','[]'),(24862,'{\"_title\":\"write a comment stays in the box on the second and third comments.  It should disappearWrite a comment...\"}','[]'),(24863,'{\"_title\":\"WRONG DAY DISPLAYED IN CALENDAR\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:25:25.000Z\"}},\"assigned\":\"256,240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24864,'{\"_title\":\"This is my first comment\"}','[]'),(24865,'{\"_title\":\"When I write another comment\"}','[]'),(24866,'{\"_title\":\"But the third time? Write a comment...\"}','[]'),(24867,'{\"_title\":\"Why does it work no?\"}','[]'),(24868,'{\"_title\":\"why does it work?\"}','[]'),(24869,'{\"_title\":\"When before it didnt? what about text wrapping what about that?\"}','[]'),(24870,'{\"_title\":\"I am creating a task by clicking on the calendar\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-14T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:35:01.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24871,'{\"_title\":\"trying the comments again\"}','[]'),(24872,'{\"_title\":\"now checking? Write a comment...\"}','[]'),(24873,'{\"_title\":\"sometimes it works and sometimes not\"}','[]'),(24874,'{\"_title\":\"Helllo!\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-25T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:39:03.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24875,'{\"_title\":\"This works\"}','[]'),(24876,'{\"_title\":\"and this does too\"}','[]'),(24877,'{\"_title\":\"this one too\"}','[]'),(24878,'{\"_title\":\"waiting for it\"}','[]'),(24879,'{\"_title\":\"but not happening\"}','[]'),(24880,'{\"_title\":\"hello\"}','[]'),(24881,'{\"_title\":\"what abouWrite a comment...\"}','[]'),(24882,'{\"_title\":\"New Test Case-Edited by Osborne\",\"nr\":\"9898989-Edit\",\"_date_start\":\"2014-03-27T00:00:00Z\",\"_date_end\":\"2014-01-28T00:00:00Z\",\"court\":\"24391,24394,24396,24393\",\"program\":\"24268,24272,24269,24266\",\"country\":\"24336\",\"status\":24262,\"tags\":\"24407,24406,24438,24403,24414,24408\",\"lead\":\"265,256\",\"support\":\"256,265\"}','[]'),(24883,'{\"_title\":\"0-Incoming\"}','[]'),(24884,'{\"_title\":\"1-Summaries\"}','[]'),(24885,'{\"_title\":\"2-Correspondence\"}','[]'),(24886,'{\"_title\":\"3-Meetings\"}','[]'),(24887,'{\"_title\":\"4-Filings\"}','[]'),(24888,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24889,'{\"_title\":\"6-Evidence\"}','[]'),(24890,'{\"_title\":\"7-Advocacy\"}','[]'),(24891,'{\"_title\":\"8-Research\"}','[]'),(24892,'{\"_title\":\"9-Administrative\"}','[]'),(24893,'{\"_title\":\"TestA1\",\"nr\":\"0909\",\"_date_start\":\"2014-02-25T00:00:00Z\",\"_date_end\":\"2014-02-27T00:00:00Z\",\"court\":\"24395\",\"program\":\"24269,24271\",\"country\":\"24333\",\"status\":24264,\"tags\":\"24400\",\"lead\":\"256\",\"support\":\"263,262,265\"}','[]'),(24894,'{\"_title\":\"0-Incoming\"}','[]'),(24895,'{\"_title\":\"1-Summaries\"}','[]'),(24896,'{\"_title\":\"2-Correspondence\"}','[]'),(24897,'{\"_title\":\"3-Meetings\"}','[]'),(24898,'{\"_title\":\"4-Filings\"}','[]'),(24899,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24900,'{\"_title\":\"6-Evidence\"}','[]'),(24901,'{\"_title\":\"7-Advocacy\"}','[]'),(24902,'{\"_title\":\"8-Research\"}','[]'),(24903,'{\"_title\":\"9-Administrative\"}','[]'),(24905,'{\"_title\":\"TestCaseA\",\"nr\":\"002\",\"_date_start\":\"2014-02-26T00:00:00Z\",\"_date_end\":\"2014-02-26T00:00:00Z\",\"court\":\"24391\",\"program\":\"24267\",\"country\":\"24333\",\"status\":24263,\"tags\":\"24400\",\"lead\":\"256\",\"support\":\"263\"}','[]'),(24906,'{\"_title\":\"0-Incoming\"}','[]'),(24907,'{\"_title\":\"1-Summaries\"}','[]'),(24908,'{\"_title\":\"2-Correspondence\"}','[]'),(24909,'{\"_title\":\"3-Meetings\"}','[]'),(24910,'{\"_title\":\"4-Filings\"}','[]'),(24911,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24912,'{\"_title\":\"6-Evidence\"}','[]'),(24913,'{\"_title\":\"7-Advocacy\"}','[]'),(24914,'{\"_title\":\"8-Research\"}','[]'),(24915,'{\"_title\":\"9-Administrative\"}','[]'),(24916,'{\"_title\":\"TESTCASEAO\",\"nr\":\"1234\",\"_date_start\":\"2014-02-11T00:00:00Z\",\"_date_end\":\"2014-02-26T00:00:00Z\",\"court\":\"24395\",\"program\":\"24269\",\"country\":\"24333\",\"status\":24262,\"tags\":\"24400,24407\",\"lead\":\"256\",\"support\":\"262\"}','[]'),(24917,'{\"_title\":\"0-Incoming\"}','[]'),(24918,'{\"_title\":\"1-Summaries\"}','[]'),(24919,'{\"_title\":\"2-Correspondence\"}','[]'),(24920,'{\"_title\":\"3-Meetings\"}','[]'),(24921,'{\"_title\":\"4-Filings\"}','[]'),(24922,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24923,'{\"_title\":\"6-Evidence\"}','[]'),(24924,'{\"_title\":\"7-Advocacy\"}','[]'),(24925,'{\"_title\":\"8-Research\"}','[]'),(24926,'{\"_title\":\"9-Administrative\"}','[]'),(24927,'{\"_title\":\"TestActA0\",\"_date_start\":\"2014-02-26T00:00:00Z\",\"tags\":\"\",\"program\":\"24269\",\"content\":\"<font face=\\\"courier new\\\">This is a test for AO<\\/font>\"}','[]'),(24929,'{\"_title\":\"test\",\"_date_start\":\"2014-02-27T00:00:00Z\"}','[]'),(24930,'{\"_title\":\"case1\"}','[]'),(24931,'{\"_title\":\"0-Incoming\"}','[]'),(24932,'{\"_title\":\"1-Summaries\"}','[]'),(24933,'{\"_title\":\"2-Correspondence\"}','[]'),(24934,'{\"_title\":\"3-Meetings\"}','[]'),(24935,'{\"_title\":\"4-Filings\"}','[]'),(24936,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24937,'{\"_title\":\"6-Evidence\"}','[]'),(24938,'{\"_title\":\"7-Advocacy\"}','[]'),(24939,'{\"_title\":\"8-Research\"}','[]'),(24940,'{\"_title\":\"9-Administrative\"}','[]'),(24941,'{\"_title\":\"case2\"}','[]'),(24942,'{\"_title\":\"0-Incoming\"}','[]'),(24943,'{\"_title\":\"1-Summaries\"}','[]'),(24944,'{\"_title\":\"2-Correspondence\"}','[]'),(24945,'{\"_title\":\"3-Meetings\"}','[]'),(24946,'{\"_title\":\"4-Filings\"}','[]'),(24947,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24948,'{\"_title\":\"6-Evidence\"}','[]'),(24949,'{\"_title\":\"7-Advocacy\"}','[]'),(24950,'{\"_title\":\"8-Research\"}','[]'),(24951,'{\"_title\":\"9-Administrative\"}','[]'),(24954,'{\"_title\":\"Test Osborne\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24395\",\"program\":\"24274,24266,24267\",\"country\":\"24319\",\"status\":24260,\"tags\":\"24415,24435,24416,24400\",\"support\":\"265\"}','[]'),(24955,'{\"_title\":\"0-Incoming\"}','[]'),(24956,'{\"_title\":\"1-Summaries\"}','[]'),(24957,'{\"_title\":\"2-Correspondence\"}','[]'),(24958,'{\"_title\":\"3-Meetings\"}','[]'),(24959,'{\"_title\":\"4-Filings\"}','[]'),(24960,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24961,'{\"_title\":\"6-Evidence\"}','[]'),(24962,'{\"_title\":\"7-Advocacy\"}','[]'),(24963,'{\"_title\":\"8-Research\"}','[]'),(24964,'{\"_title\":\"9-Administrative\"}','[]'),(24965,'{\"_title\":\"TestXA\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"date_end\":\"2014-03-07T00:00:00Z\",\"datetime_start\":\"2014-02-27T08:36:17.000Z\"}},\"assigned\":\"256\",\"importance\":2,\"category\":\"24270,24272\",\"color\":24510,\"reminders\":{\"childs\":[]}}','[]'),(24966,'{\"_title\":\"test\"}','[]'),(24967,'{\"_title\":\"test\"}','[]'),(24968,'{\"_title\":\"limit\"}','[]'),(24969,'{\"_title\":\"limit\"}','[]'),(24970,'{\"_title\":\"still\"}','[]'),(24971,'{\"_title\":\"still\"}','[]'),(24972,'{\"_title\":\"close case\"}','[]'),(24973,'{\"_title\":\"reopen case\"}','[]'),(24974,'{\"_title\":\"^$%%#$@#%$&^\"}','[]'),(24975,'{\"_title\":\",sd,flgtftredzxcsz.,\\/.,\"}','[]'),(24977,'{\"_title\":\"This is test comment to confirm how this works\"}','[]'),(24978,'{\"_title\":\"Testing completion of task.\"}','[]'),(24979,'{\"_title\":\"I have received the task and completed it\"}','[]'),(24980,'{\"_title\":\"Commenting works fine on closing tasks\"}','[]'),(24981,'{\"_title\":\"It seems there is no\"}','[]'),(24982,'{\"_title\":\"limit\"}','[]'),(24983,'{\"_title\":\"on\"}','[]'),(24984,'{\"_title\":\"the\"}','[]'),(24985,'{\"_title\":\"@\"}','[]'),(24986,'{\"_title\":\"#\"}','[]'),(24987,'{\"_title\":\"of\"}','[]'),(24988,'{\"_title\":\"commnents\"}','[]'),(24989,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"datetime_start\":\"2014-02-27T09:09:48.000Z\"}},\"assigned\":\"265,262,263\",\"importance\":1,\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":{\"childs\":{\"count\":10,\"units\":1}}}','[]'),(24990,'{\"_title\":\"Should we be\"}','[]'),(24991,'{\"_title\":\"able to comment after closing a task\"}','[]'),(24992,'{\"_title\":\"?Write a comment...\"}','[]'),(24993,'{\"_title\":\"d\"}','[]'),(24994,'{\"_title\":\"dWrite a comment...\"}','[]'),(24995,'{\"_title\":\"d\"}','[]'),(24996,'{\"_title\":\"dWrite a comment...\"}','[]'),(24997,'{\"_title\":\"dWrite a comment...\"}','[]'),(24998,'{\"_title\":\"sWrite a comment...\"}','[]'),(24999,'{\"_title\":\"eWrite a comment...\"}','[]'),(25000,'{\"_title\":\"gWrite a comment...\"}','[]'),(25001,'{\"_title\":\"words \\\"Write a comment\\\" sometimes come up in the comment also\"}','[]'),(25002,'{\"_title\":\"dWrite a comment...\"}','[]'),(25003,'{\"_title\":\"gWrite a comment...\"}','[]'),(25004,'{\"_title\":\"cWrite a comment...\"}','[]'),(25005,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25006,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25007,'{\"_title\":\"test\"}','[]'),(25008,'{\"_title\":\"sWrite a comment...\"}','[]'),(25009,'{\"_title\":\"Osborne Test 27th\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24395,24397,24393,24396,24398\",\"program\":\"24274,24266,24269,24273\",\"country\":\"24319\",\"status\":24260,\"tags\":\"24415,24416,24435\",\"lead\":\"256\",\"support\":\"265\"}','[]'),(25010,'{\"_title\":\"0-Incoming\"}','[]'),(25011,'{\"_title\":\"1-Summaries\"}','[]'),(25012,'{\"_title\":\"2-Correspondence\"}','[]'),(25013,'{\"_title\":\"3-Meetings\"}','[]'),(25014,'{\"_title\":\"4-Filings\"}','[]'),(25015,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25016,'{\"_title\":\"6-Evidence\"}','[]'),(25017,'{\"_title\":\"7-Advocacy\"}','[]'),(25018,'{\"_title\":\"8-Research\"}','[]'),(25019,'{\"_title\":\"9-Administrative\"}','[]'),(25023,'{\"_title\":\"Still no email notification\"}','[]'),(25030,'{\"_title\":\"hghg\"}','[]'),(25031,'{\"_title\":\"gWrite a comment...\"}','[]'),(25032,'{\"_title\":\"kWrite a comment...\"}','[]'),(25035,'{\"_title\":\"hh\"}','[]'),(25036,'{\"_title\":\"k\"}','[]'),(25037,'{\"_title\":\"dWrite a comment...\"}','[]'),(25038,'{\"_title\":\"Osborne Test in Calendar\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24330\",\"status\":24260,\"tags\":\"24418\",\"lead\":\"265\",\"support\":\"256\"}','[]'),(25039,'{\"_title\":\"0-Incoming\"}','[]'),(25040,'{\"_title\":\"1-Summaries\"}','[]'),(25041,'{\"_title\":\"2-Correspondence\"}','[]'),(25042,'{\"_title\":\"3-Meetings\"}','[]'),(25043,'{\"_title\":\"4-Filings\"}','[]'),(25044,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25045,'{\"_title\":\"6-Evidence\"}','[]'),(25046,'{\"_title\":\"7-Advocacy\"}','[]'),(25047,'{\"_title\":\"8-Research\"}','[]'),(25048,'{\"_title\":\"9-Administrative\"}','[]'),(25049,'{\"_title\":\"added case in calendar view\"}','[]'),(25050,'{\"_title\":\"something has to be done about wrapping long comments like thiiiiiissssssss onnnnnneeeeeee.......add a scroll bar here\"}','[]'),(25051,'{\"_title\":\"Osborne Test in Calendar\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24330\",\"status\":24260,\"tags\":\"24418\",\"lead\":\"265\",\"support\":\"256\"}','[]'),(25052,'{\"_title\":\"0-Incoming\"}','[]'),(25053,'{\"_title\":\"1-Summaries\"}','[]'),(25054,'{\"_title\":\"2-Correspondence\"}','[]'),(25055,'{\"_title\":\"3-Meetings\"}','[]'),(25056,'{\"_title\":\"4-Filings\"}','[]'),(25057,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25058,'{\"_title\":\"6-Evidence\"}','[]'),(25059,'{\"_title\":\"7-Advocacy\"}','[]'),(25060,'{\"_title\":\"8-Research\"}','[]'),(25061,'{\"_title\":\"9-Administrative\"}','[]'),(25062,'{\"_title\":\"added case in calendar view\"}','[]'),(25063,'{\"_title\":\"is the end date supposed to saved if it is earlier than the Start date?\"}','[]'),(25064,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\"}},\"assigned\":\"265,262,263\",\"importance\":\"1\",\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":{\"childs\":{\"count\":1,\"units\":\"1\"}}}','[]'),(25065,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25066,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25067,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-27T00:00:00.000000Z\",\"date_end\":null}},\"assigned\":\"265,262,263\",\"importance\":\"1\",\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":[{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}],\"category_id\":\"24274\"}','[]'),(25068,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25069,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25070,'{\"_title\":\"Test event\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"\",\"datetime_end\":null}},\"assigned\":\"265\",\"importance\":\"3\",\"category\":24274,\"color\":24506,\"description\":\"do u get the notification?\",\"reminders\":{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}}','[]'),(25071,'{\"_title\":\"Test milestone\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"date_end\":\"2014-02-18T00:00:00Z\",\"datetime_start\":\"2014-02-27T13:42:58.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":24274,\"color\":24509,\"description\":\"test\",\"reminders\":{\"childs\":{\"count\":1,\"units\":1}}}','[]'),(25072,'{\"_title\":\"Test Action\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"program\":\"24274\",\"content\":\"test content for action<br>\"}','[]'),(25073,'{\"_title\":\"Test action\",\"_date_start\":\"2014-02-28T00:00:00Z\",\"tags\":\"\",\"program\":\"24274,24266\",\"content\":\"Not really sure what adding action does..The tags field has no values in the pop up windows. Searching some of the terms used in the tags field in other content types still yields no result.<br>\"}','[]'),(25074,'{\"_title\":\"Test comment on manager\'s task....\"}','[]'),(25075,'{\"_title\":\"Test 01\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-27T21:00:00.000Z\",\"datetime_end\":\"2014-03-06T21:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":\"3\",\"category\":24274,\"color\":24506,\"description\":\"Test edited\",\"reminders\":{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}}','[]'),(25076,'{\"_title\":\"edits\"}','[]'),(25077,'{\"_title\":\"ana test\"}','[]'),(25078,'{\"_title\":\"Scan papers\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-28T00:00:00Z\",\"date_end\":\"2014-03-07T00:00:00Z\",\"datetime_start\":\"2014-02-28T13:17:26.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]');

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varbinary(50) NOT NULL,
  `pid` varbinary(50) DEFAULT NULL COMMENT 'parrent session id',
  `last_action` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires` timestamp NULL DEFAULT NULL COMMENT 'expire could be null for non expiring sessions',
  `user_id` int(10) unsigned NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `idx_expires` (`expires`),
  KEY `idx_last_action` (`last_action`),
  KEY `idx_pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `sessions` */

insert  into `sessions`(`id`,`pid`,`last_action`,`expires`,`user_id`,`data`) values ('93o98deuo95u08v2962ibgtqa2','93o98deuo95u08v2962ibgtqa2','2014-02-25 21:06:42',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"4a12b6fd445d55ebe093be9f9237fe57\";user|a:15:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}s:11:\"TSV_checked\";b:1;}message|N;'),('hrg3vciud7r129r2vfu882drm2','hrg3vciud7r129r2vfu882drm2','2014-02-25 16:15:57',NULL,256,'ips|s:15:\"|67.85.184.221|\";key|s:32:\"04baabeba1d13c3b1dcb8da5e0819512\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('pejq8gb88b52qhkupnv3mh8bb2','pejq8gb88b52qhkupnv3mh8bb2','2014-02-25 21:06:04',NULL,0,''),('usv759u3o1bnfed4mj7au123s7','usv759u3o1bnfed4mj7au123s7','2014-02-25 21:06:08',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"442c20479b5d2880e3a32c5847be11ee\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('r3gmm8mbb54idcqijitbhqjd76','r3gmm8mbb54idcqijitbhqjd76','2014-02-27 14:21:49',NULL,0,'ips|s:15:\"|41.90.234.130|\";key|s:32:\"bf87a2409d838236b78c247e3fab7286\";'),('1a629vfe2b1un34nqba0deius1','1a629vfe2b1un34nqba0deius1','2014-02-27 10:21:58',NULL,256,'ips|s:12:\"|62.8.75.28|\";key|s:32:\"3e7b6a3cdf66d24b75296dd620cb4c23\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('mi3rg76h8bt4a3f3nsn0v26m03','mi3rg76h8bt4a3f3nsn0v26m03','2014-02-25 21:06:09',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"56672b77735c961738466b1d51fbe20b\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('jm3oe92s08piiq6oqqic1a6ig1','jm3oe92s08piiq6oqqic1a6ig1','2014-02-25 21:06:10',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"c029685b858e1db03bd331eb9ae8830b\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('lnfboqjfr9qjaiu6c5ob83jdv5','lnfboqjfr9qjaiu6c5ob83jdv5','2014-02-25 21:06:10',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"c029685b858e1db03bd331eb9ae8830b\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('0ddme0mbc11j783lbl7djgpep1','0ddme0mbc11j783lbl7djgpep1','2014-02-25 21:06:10',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"c029685b858e1db03bd331eb9ae8830b\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('cq7aprn2e0q1pb8mtkpvft2qh7','cq7aprn2e0q1pb8mtkpvft2qh7','2014-02-25 21:06:14',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"8d1bb6326d6bfb7cfc058ef9d5893ee8\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('05qnoinfrfau7bfgdruldimhl5','05qnoinfrfau7bfgdruldimhl5','2014-02-25 21:06:20',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"b3ab2f8e1732a2a444979d5e1ddc616c\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('umim73vh0gmdfl68gljtf78ig3','umim73vh0gmdfl68gljtf78ig3','2014-02-25 21:06:20',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"806a4eddacae4a79a1731e4d6d9c6607\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('v9kfk9ce3h619ptq2vf6p3lkq0','v9kfk9ce3h619ptq2vf6p3lkq0','2014-02-25 21:06:20',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"806a4eddacae4a79a1731e4d6d9c6607\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('fmulpssd16cb8713rmjoq8p6p0','fmulpssd16cb8713rmjoq8p6p0','2014-02-25 21:06:21',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"4f65c3c112ccb438df59056f3f7db8de\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('m2n0mnrah3mn604ejff68ec6t3','m2n0mnrah3mn604ejff68ec6t3','2014-02-25 21:06:21',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"4f65c3c112ccb438df59056f3f7db8de\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('iuj757s1ernd74ffha81hafcf2','iuj757s1ernd74ffha81hafcf2','2014-02-25 21:06:29',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"c057ca8bfe4add8e203b0de10e4e9608\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('8jtpnoqps9ippdh3p6j7ghjec0','8jtpnoqps9ippdh3p6j7ghjec0','2014-02-25 21:06:43',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"0d7b0440990407a422f266caec28a4f9\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('7v4so04eos7t6qpvlnghn0htt4','7v4so04eos7t6qpvlnghn0htt4','2014-02-25 21:06:43',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"2bdd9d2993c24cf4f7218b05291a35de\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('3h6q563q10k2bootoh0g96pda4','3h6q563q10k2bootoh0g96pda4','2014-02-25 21:06:43',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"2bdd9d2993c24cf4f7218b05291a35de\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('bf791f812sevqal2hc9e39o4b2','bf791f812sevqal2hc9e39o4b2','2014-02-25 21:06:44',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"bcdb7b6c419342e4c803fc64256425a8\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('dhnhq48hrqphtgso0273b1nbi1','dhnhq48hrqphtgso0273b1nbi1','2014-02-25 21:06:44',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"bcdb7b6c419342e4c803fc64256425a8\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('igri549ugdu8ckb8o0r49rjg97','igri549ugdu8ckb8o0r49rjg97','2014-02-25 21:06:45',NULL,240,'ips|s:16:\"|109.185.172.18|\";key|s:32:\"3d6f5d5fa372ee6c3f8f7db026e34e18\";user|a:14:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}}'),('38soto9jhsqe3h8neltrp88144','38soto9jhsqe3h8neltrp88144','2014-02-26 16:26:38',NULL,256,'ips|s:15:\"|41.215.42.102|\";key|s:32:\"e363a60cfa5541706cb3c61866e42d3e\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('uuucv6tei9tvuapfja6n6q6hr0','uuucv6tei9tvuapfja6n6q6hr0','2014-02-26 20:58:49',NULL,256,'ips|s:15:\"|67.85.184.221|\";key|s:32:\"64de19afe90171dfe38643024750617c\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('rpv9em17caeo8sojbcb1ucba70','rpv9em17caeo8sojbcb1ucba70','2014-02-27 10:07:37',NULL,265,'ips|s:15:\"|62.24.111.244|\";key|s:32:\"9980d2d0b713c8226c1c7eb7d34dbaf3\";verified|i:1393492089;user|a:15:{s:2:\"id\";s:3:\"265\";s:4:\"name\";s:11:\"TesterWwwCB\";s:10:\"first_name\";s:7:\"Osborne\";s:9:\"last_name\";s:5:\"Omuya\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:23:\"test.huridocs@gmail.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:7:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:4:\"+254\";s:5:\"phone\";s:12:\"254727536635\";s:8:\"timezone\";s:7:\"Nairobi\";s:2:\"TZ\";s:14:\"Africa/Nairobi\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:0:{}s:11:\"TSV_checked\";b:1;}message|N;'),('u7s3n7fcgt8i2jkdp0bvntjbf1','u7s3n7fcgt8i2jkdp0bvntjbf1','2014-02-27 05:02:30',NULL,256,'ips|s:14:\"|41.79.169.14|\";key|s:32:\"bdcb69917c3e3e4f7d662a708784bdaf\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('bgfl9qo93jk4k438j0dqvvlpq6','bgfl9qo93jk4k438j0dqvvlpq6','2014-02-27 06:23:16',NULL,256,'ips|s:15:\"|212.49.88.100|\";key|s:32:\"adac3759e11d71f0cc1d6483123d6f96\";user|a:15:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}s:11:\"TSV_checked\";b:1;}message|N;'),('8r6gpjkkd2fujkneagirj07oe1','8r6gpjkkd2fujkneagirj07oe1','2014-02-28 08:20:53',NULL,265,'ips|s:17:\"|105.231.125.193|\";key|s:32:\"13c14879cdba3883d59dba796d8d52a5\";user|a:15:{s:2:\"id\";s:3:\"265\";s:4:\"name\";s:11:\"TesterWwwCB\";s:10:\"first_name\";s:7:\"Osborne\";s:9:\"last_name\";s:5:\"Omuya\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:23:\"test.huridocs@gmail.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:7:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:4:\"+254\";s:5:\"phone\";s:12:\"254727536635\";s:8:\"timezone\";s:7:\"Nairobi\";s:2:\"TZ\";s:14:\"Africa/Nairobi\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:0:{}s:11:\"TSV_checked\";b:1;}message|N;'),('2l75buua53pmjf2p1pnau22rj1','2l75buua53pmjf2p1pnau22rj1','2014-02-27 07:57:05',NULL,0,''),('egvq10p0bcsosqe56dlmvb5pt6','egvq10p0bcsosqe56dlmvb5pt6','2014-02-27 07:57:22',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"d1e76c0ac003aeab7b332ae00a86e940\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('tql2fiffllv2v8tkv304v4qrg2','tql2fiffllv2v8tkv304v4qrg2','2014-02-27 07:57:24',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"67d936ade06dd4225e7cd89d13de3c51\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('t3v18minivhh7buth46hkb0tf1','t3v18minivhh7buth46hkb0tf1','2014-02-27 07:57:26',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"0aa36be78d23a55d171757a0e70b98f5\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('ur15ii936qtm16kvl2b8phrpm6','ur15ii936qtm16kvl2b8phrpm6','2014-02-27 07:57:27',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"096ecf6e1d1fb2ad0f2005ca03a48dc3\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('td9mc1kburrqumg95an522i803','td9mc1kburrqumg95an522i803','2014-02-27 07:57:28',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"d6a2668dcc8995b20642e004e66b664a\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('ehq0mof85qieuabiavokn776c6','ehq0mof85qieuabiavokn776c6','2014-02-27 07:58:26',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"6f8fdbd517c80ae448498476107cb935\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('egqv3h0r4k1fdo7o08r4746s74','egqv3h0r4k1fdo7o08r4746s74','2014-02-27 07:58:30',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"f9a6c1b61ba57682df55b4c9c11fff4d\";user|a:1:{s:2:\"id\";s:3:\"256\";}'),('e5hcrrqior7fa97k1o3e3kt4e4','e5hcrrqior7fa97k1o3e3kt4e4','2014-02-27 08:22:39',NULL,256,'ips|s:15:\"|62.24.111.253|\";key|s:32:\"75faecadb79088b6f3376d9a554d820a\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('6phu480hr8g53udbrdchfgec76','6phu480hr8g53udbrdchfgec76','2014-02-27 08:45:32',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"679bdc721a5f14d7f8514abcc3029e94\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('dosh0ibkbk13pi4ka42v6juga4','dosh0ibkbk13pi4ka42v6juga4','2014-02-27 08:58:48',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"85890ffb152060f40b8ced0874bf3ce5\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('oel27h2jvkg37utlt1qvrsmc73','oel27h2jvkg37utlt1qvrsmc73','2014-02-27 08:58:50',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"149b6cab1e18d5c8aad0c8d9903a2a35\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('csba4q2c8ahfmapc380prvk186','csba4q2c8ahfmapc380prvk186','2014-02-27 08:58:52',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"9f52a12e94590de4c8c699305732ce4b\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('bvjcpi4ll7f1ul8cgn50op12b4','bvjcpi4ll7f1ul8cgn50op12b4','2014-02-27 08:58:53',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"d3ccb198fa236e9c698c524c218d689f\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('4gcgl455tq96e4fipn0edavqo3','4gcgl455tq96e4fipn0edavqo3','2014-02-27 08:58:54',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"38364c41ff044362eb886b95334bdeb3\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('6toltcbge9o6v1jiln7j6urhh4','6toltcbge9o6v1jiln7j6urhh4','2014-02-27 08:58:58',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"554817beca961e6bf743cfedb904036e\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('en48uoc2no4od7aneukad1sf36','en48uoc2no4od7aneukad1sf36','2014-02-27 08:59:25',NULL,256,'ips|s:15:\"|62.24.111.242|\";key|s:32:\"8bfd60f84d17e6b56f9d974762047418\";user|a:14:{s:2:\"id\";s:3:\"256\";s:4:\"name\";s:17:\"katherin.machalek\";s:10:\"first_name\";s:8:\"Katherin\";s:9:\"last_name\";s:8:\"Machalek\";s:3:\"sex\";s:1:\"f\";s:5:\"email\";s:30:\"katherin.machalek@huridocs.org\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:6:{s:17:\"short_date_format\";s:8:\"%m/%d/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:0:\"\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:30:\"{\"email\":\"phylabra@yahoo.com\"}\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:0;s:6:\"groups\";a:4:{i:0;s:3:\"242\";i:1;s:3:\"243\";i:2;s:3:\"247\";i:3;s:3:\"252\";}}'),('emv6vurjcfho49gafik0odlmd2','emv6vurjcfho49gafik0odlmd2','2014-02-27 09:00:37',NULL,0,''),('ggcurmk15guugsppjsmm8o7re5','ggcurmk15guugsppjsmm8o7re5','2014-02-27 09:01:21',NULL,0,''),('jbbh00cqcvf5hp5rq7l11l1di4','jbbh00cqcvf5hp5rq7l11l1di4','2014-02-28 15:21:23',NULL,240,'ips|s:11:\"|127.0.0.1|\";key|s:32:\"66f7f207f355eb8384ac87882c511b55\";user|a:15:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}s:11:\"TSV_checked\";b:1;}message|N;verified|i:1393591078;'),('4n5srnv494sebfispce1ak5ke3','4n5srnv494sebfispce1ak5ke3','2014-02-28 13:42:54',NULL,240,'ips|s:14:\"|89.217.10.27|\";key|s:32:\"4ef10a4e4f0411e04d406b9bdce61ab7\";user|a:15:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}s:11:\"TSV_checked\";b:1;}message|N;'),('eeftt705utd020qc9to1p98bc5','eeftt705utd020qc9to1p98bc5','2014-02-28 14:33:13',NULL,240,'ips|s:16:\"|188.240.73.107|\";key|s:32:\"722b7421c02bbd640dc2ba4549c9a95e\";user|a:15:{s:2:\"id\";s:3:\"240\";s:4:\"name\";s:8:\"oburlaca\";s:10:\"first_name\";s:4:\"Oleg\";s:9:\"last_name\";s:7:\"Burlaca\";s:3:\"sex\";s:1:\"m\";s:5:\"email\";s:16:\"oleg@burlaca.com\";s:11:\"language_id\";s:1:\"1\";s:3:\"cfg\";a:9:{s:17:\"short_date_format\";s:8:\"%d/%m/%Y\";s:16:\"long_date_format\";s:9:\"%F %j, %Y\";s:11:\"canAddUsers\";s:4:\"true\";s:12:\"canAddGroups\";s:4:\"true\";s:12:\"country_code\";s:0:\"\";s:5:\"phone\";s:0:\"\";s:8:\"timezone\";s:8:\"Chisinau\";s:2:\"TZ\";s:15:\"Europe/Chisinau\";s:11:\"time_format\";s:5:\"%H:%i\";}s:4:\"data\";s:2:\"[]\";s:8:\"language\";s:2:\"en\";s:6:\"locale\";s:5:\"en_US\";s:5:\"admin\";N;s:6:\"manage\";b:1;s:6:\"groups\";a:2:{i:0;s:3:\"235\";i:1;s:3:\"238\";}s:11:\"TSV_checked\";b:1;}message|N;');

/*Table structure for table `tasks` */

DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(250) NOT NULL,
  `date_start` datetime DEFAULT NULL COMMENT 'used for events',
  `date_end` datetime DEFAULT NULL,
  `allday` tinyint(1) NOT NULL DEFAULT '1',
  `importance` tinyint(3) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'for tasks: 0-internal, 1-external. For events: 2',
  `privacy` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-public, 1-private',
  `responsible_user_ids` varchar(100) NOT NULL,
  `autoclose` tinyint(1) DEFAULT '1',
  `description` text COMMENT 'Task description',
  `parent_ids` varchar(100) DEFAULT NULL COMMENT 'parent tasks',
  `child_ids` varchar(100) DEFAULT NULL COMMENT 'child tasks. TO BE REVIEWED',
  `time` char(5) DEFAULT NULL,
  `reminds` varchar(250) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1 Overdue 2 Active 3 Completed 4 Pending',
  `missed` tinyint(1) unsigned DEFAULT NULL,
  `completed` timestamp NULL DEFAULT NULL COMMENT 'completed date (will be set automaticly, when all responsible users mark task as completed or the owner can close the task manually )',
  `cid` int(11) unsigned NOT NULL DEFAULT '1',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(11) unsigned DEFAULT '1',
  `udate` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `FK_tasks_user_id` (`responsible_user_ids`),
  KEY `IDX_status` (`status`),
  KEY `FK_tasks__cid` (`cid`),
  KEY `FK_tasks__object_id` (`object_id`),
  KEY `FK_tasks__uid` (`uid`),
  KEY `idx_status__date_end` (`status`,`date_end`),
  CONSTRAINT `FK_tasks__cid` FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks__object_id` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks__uid` FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25079 DEFAULT CHARSET=utf8;

/*Data for the table `tasks` */

insert  into `tasks`(`id`,`object_id`,`title`,`date_start`,`date_end`,`allday`,`importance`,`category_id`,`type`,`privacy`,`responsible_user_ids`,`autoclose`,`description`,`parent_ids`,`child_ids`,`time`,`reminds`,`status`,`missed`,`completed`,`cid`,`cdate`,`uid`,`udate`) values (25078,NULL,'Scan papers','2014-02-28 00:00:00','2014-03-07 00:00:00',1,1,24274,0,0,'240',1,NULL,NULL,NULL,NULL,NULL,2,0,NULL,240,'2014-02-28 15:18:38',1,'0000-00-00 00:00:00');

/*Table structure for table `tasks_reminders` */

DROP TABLE IF EXISTS `tasks_reminders`;

CREATE TABLE `tasks_reminders` (
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `reminds` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`task_id`,`user_id`),
  KEY `FK_tasks_reminders__user_id` (`user_id`),
  CONSTRAINT `FK_tasks_reminders__task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks_reminders__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tasks_reminders` */

insert  into `tasks_reminders`(`task_id`,`user_id`,`reminds`) values (25078,240,'');

/*Table structure for table `tasks_responsible_users` */

DROP TABLE IF EXISTS `tasks_responsible_users`;

CREATE TABLE `tasks_responsible_users` (
  `task_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned DEFAULT '0' COMMENT '0-pending, 1-done',
  `thesauri_response_id` int(10) unsigned DEFAULT NULL,
  `time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`task_id`,`user_id`),
  KEY `FK_user_id` (`user_id`),
  CONSTRAINT `FK_tasks_responsible_users__task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks_responsible_users__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tasks_responsible_users` */

insert  into `tasks_responsible_users`(`task_id`,`user_id`,`status`,`thesauri_response_id`,`time`) values (25078,240,0,NULL,NULL);

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `is_folder` tinyint(1) unsigned DEFAULT '0',
  `type` enum('case','object','file','task','user','email','template','field','search','comment') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `l1` varchar(100) DEFAULT NULL,
  `l2` varchar(100) DEFAULT NULL,
  `l3` varchar(250) DEFAULT NULL,
  `l4` varchar(100) DEFAULT NULL,
  `order` int(11) unsigned DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `iconCls` varchar(50) DEFAULT NULL,
  `default_field` varchar(50) DEFAULT NULL,
  `cfg` text,
  `title_template` text,
  `info_template` text,
  PRIMARY KEY (`id`),
  KEY `FK_templates__pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=24823 DEFAULT CHARSET=utf8;

/*Data for the table `templates` */

insert  into `templates`(`id`,`pid`,`is_folder`,`type`,`name`,`l1`,`l2`,`l3`,`l4`,`order`,`visible`,`iconCls`,`default_field`,`cfg`,`title_template`,`info_template`) values (24043,24042,0,'field','Fields template','Fields template','Fields template','Fields template','Fields template',0,1,'icon-snippet',NULL,'[]',NULL,NULL),(24044,24042,0,'template','Templates template','Templates template','Templates template','Templates template','Templates template',0,1,'icon-template',NULL,'[]',NULL,NULL),(24045,24042,0,'template','templatesProperies','Template for editing template properties','Template for editing template properties','Template for editing template properties',NULL,3,1,NULL,NULL,NULL,NULL,NULL),(24052,24042,1,'','system','System','System','Система',NULL,2,1,'icon-folder',NULL,NULL,NULL,NULL),(24053,24052,0,'user','User','User',NULL,'Пользователь',NULL,1,1,'icon-object4',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24067,24052,0,'email','email','Email','Email','Email',NULL,2,1,'icon-mail',NULL,'{\"files\":1,\"main_file\":\"1\"}',NULL,NULL),(24072,24052,0,'task','tasks','Task','Task','Task','Task',3,1,'icon-task',NULL,'{\"data\":{\"type\":6}}','{name}',NULL),(24073,24052,0,'task','event','Event','Event','Event','Event',4,1,'icon-event',NULL,'{\"data\":{\"type\":7}}','{name}',NULL),(24074,24052,0,'object','folder','Folder','Folder','Folder','Folder',5,1,'icon-folder',NULL,'{\"createMethod\":\"inline\"}','{name}',NULL),(24075,24052,0,'file','file_template','File','File','File','File',6,1,'file-',NULL,'[]','{name}',NULL),(24078,24052,0,'task','milestone','Milestone','Milestone','Milestone','Milestone',4,1,'i-flag',NULL,'[]','{name}',NULL),(24079,24042,0,'case','case_template','Case','Case','Case','Case',1,1,'icon-briefcase',NULL,'{\"system_folders\": 24248}','{name}',NULL),(24092,24042,1,'','Old','Old','Old','Old',NULL,0,1,'icon-folder',NULL,NULL,NULL,NULL),(24093,24092,1,'','casesTemplates','Cases templates','Modèles du cas','Шаблоны дел',NULL,1,1,'icon-briefcase',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24094,24093,1,'','caseObjects','Case objects','Objets du cas','Объекты дел',NULL,1,1,'icon-folder',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24095,24094,0,'object','suspect','subject',NULL,'субъект',NULL,2,-1,'icon-suspect',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}','{f58} {f45} {f46}',NULL),(24108,24094,0,'object','Case card','Case card','Case card','Case card',NULL,1,1,'icon-blog-blue',NULL,'{\"files\":\"0\",\"main_file\":\"0\"}',NULL,NULL),(24115,24093,1,'','incomingActions','Incoming actions','Actions entrants','Входящие события',NULL,2,1,'icon-folder',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24116,24115,0,'object','informationalLetter','Incoming action',NULL,'Incoming action',NULL,5,-1,'icon-arrow-left-medium-green',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}','{template_title}: {object_title}',NULL),(24122,24115,0,'object','email1','email',NULL,'email',NULL,3,-1,'icon-mail-receive',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24125,24115,0,'object','communication','Communication',NULL,'Communication',NULL,1,1,'icon-balloon',NULL,'[]',NULL,NULL),(24130,24115,0,'object','decision','Decision',NULL,'Decision',NULL,2,1,'icon-decision',NULL,'[]',NULL,NULL),(24135,24115,0,'object','judgement','Judgement',NULL,'Judgement',NULL,6,1,'icon-echr_decision',NULL,'[]',NULL,NULL),(24140,24115,0,'object','gv_reply','Government reply',NULL,'Government reply',NULL,4,1,'icon-object8',NULL,'[]',NULL,NULL),(24145,24115,0,'object','notification','Notification',NULL,'Notification',NULL,7,1,'icon-bell',NULL,'[]',NULL,NULL),(24150,24093,1,'','incomingActions','Outgoing actions','Actions sortantes','Исходящие события',NULL,3,1,'icon-folder',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24151,24150,0,'object','Outgoing action','Outgoing action',NULL,'Outgoing action',NULL,3,-1,'icon-arrow-right-medium',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}','{template_title}',NULL),(24155,24150,0,'object','email2','email',NULL,'email',NULL,2,-1,'icon-mail-send',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24158,24150,0,'object','written_comments','Written comments',NULL,'Written comments',NULL,4,1,'icon-document-text',NULL,'[]',NULL,NULL),(24163,24150,0,'object','application','Application',NULL,'Application',NULL,1,1,'icon-echr_complaint',NULL,'[]',NULL,NULL),(24169,24093,1,'','contacts','Contact templates','Modèles de contacts','Шаблоны контактов',NULL,4,1,'icon-folder',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24170,24169,0,'object','Client','Client','Client','Клиент',NULL,1,1,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24183,24169,0,'object','Organization','Organization','Organisation','Организация',NULL,2,1,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24194,24092,0,'',NULL,'Test','Test','Test',NULL,2,1,'icon-none',NULL,NULL,NULL,NULL),(24195,24042,0,'object','Action','Action','Action','Action',NULL,1,1,'icon-petition',NULL,'[]',NULL,NULL),(24217,24052,0,'object','Thesauri Item','Thesauri item','Thesauri item','Thesauri item','Thesauri item',0,1,'icon-blue-document-small',NULL,NULL,'{en}',NULL),(24484,24042,0,'object','office','Program item','Program item','Program item','Program item',0,1,'icon-object8',NULL,NULL,'{en}',NULL),(24822,24042,0,'comment','Comment',NULL,NULL,NULL,NULL,0,1,'icon-balloon',NULL,'{\n  \"systemType\": 2\n}',NULL,NULL);

/*Table structure for table `templates_structure` */

DROP TABLE IF EXISTS `templates_structure`;

CREATE TABLE `templates_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `template_id` int(11) unsigned NOT NULL,
  `tag` varchar(30) DEFAULT NULL,
  `level` smallint(6) unsigned DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `l1` varchar(100) DEFAULT NULL,
  `l2` varchar(100) DEFAULT NULL,
  `l3` varchar(250) DEFAULT NULL,
  `l4` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL COMMENT 'varchar,date,time,int,bool,text,combo,popup_list',
  `order` smallint(6) unsigned DEFAULT '0',
  `cfg` text,
  `solr_column_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_id__name` (`template_id`,`name`),
  KEY `templates_structure_pid` (`pid`),
  KEY `templates_structure_template_id` (`template_id`),
  KEY `idx_templates_structure_type` (`type`),
  CONSTRAINT `FK_templates_structure__template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24824 DEFAULT CHARSET=utf8;

/*Data for the table `templates_structure` */

insert  into `templates_structure`(`id`,`pid`,`template_id`,`tag`,`level`,`name`,`l1`,`l2`,`l3`,`l4`,`type`,`order`,`cfg`,`solr_column_name`) values (24046,24045,24045,'f',0,'visible','Active','Active','Active',NULL,'checkbox',1,NULL,NULL),(24047,24045,24045,'f',0,'gridJsClass','JavaScript grid class','JavaScript grid class','JavaScript grid class',NULL,'jsclasscombo',2,NULL,NULL),(24048,24045,24045,'f',0,'iconCls','Icon class','Icon class','Icon class',NULL,'iconcombo',3,NULL,NULL),(24049,24045,24045,'f',0,'default_field','Default field','Default field','Default field',NULL,'fieldscombo',4,NULL,NULL),(24050,24045,24045,'f',0,'files','Files','Files','Files',NULL,'checkbox',5,NULL,NULL),(24051,24045,24045,'f',0,'type','Type','Type','Type','Type','_templateTypesCombo',0,NULL,NULL),(24054,24053,24053,'f',0,'l1','Full name (en)','Nom complet (en)','Полное имя (en)',NULL,'varchar',1,'[]',NULL),(24055,24053,24053,'f',0,'l2','Full name (fr)','Nom complet (fr)','Полное имя (fr)',NULL,'varchar',2,'[]',NULL),(24056,24053,24053,'f',0,'l3','Full name (ru)','Nom complet (ru)','Полное имя (ru)',NULL,'varchar',3,'[]',NULL),(24057,24053,24053,'f',0,'initials','Initials','Initiales','Инициалы',NULL,'varchar',4,'[]',NULL),(24058,24053,24053,'f',0,'sex','Sex','Sexe','Пол',NULL,'_sex',5,'{\"thesauriId\":\"90\"}',NULL),(24059,24053,24053,'f',0,'position','Position','Titre','Должность',NULL,'_objects',7,'{\"source\":\"tree\",\"scope\":24340,\"oldThesauriId\":\"362\"}',NULL),(24060,24053,24053,'f',0,'email','E-mail','E-mail','E-mail',NULL,'varchar',9,'{\"maxInstances\":\"3\"}',NULL),(24061,24053,24053,'f',0,'language_id','Language','Langue','Язык',NULL,'_language',11,'[]',NULL),(24062,24053,24053,'f',0,'short_date_format','Date format','Format de date','Формат даты',NULL,'_short_date_format',12,'[]',NULL),(24063,24053,24053,'f',0,'description','Description','Description','Примечание',NULL,'varchar',13,'[]',NULL),(24064,24053,24053,'f',0,'room','Room','Salle','Кабинет',NULL,'varchar',8,'[]',NULL),(24065,24053,24053,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',10,'{\"maxInstances\":\"10\"}',NULL),(24066,24053,24053,'f',0,'location','Location','Emplacement','Расположение',NULL,'_objects',6,'{\"source\":\"tree\",\"scope\":24373,\"oldThesauriId\":\"394\"}',NULL),(24068,24067,24067,'f',0,'_title','Subject','Sujet','Название',NULL,'varchar',0,'{\"showIn\": \"top\"}',NULL),(24069,24067,24067,'f',0,'_date_start','Date','Date','Дата',NULL,'date',1,'{\"showIn\": \"top\"}','date_start'),(24070,24067,24067,'f',0,'from','From','D\'après','От',NULL,'varchar',3,'{\"thesauriId\":\"73\"}','strings'),(24071,24067,24067,'f',0,'_content','Content','Teneur','Содержание',NULL,'html',1,'{\"showIn\": \"tabsheet\"}','texts'),(24076,24075,24075,'f',0,'program','Program','Program','Program','Program','_objects',1,'{\"source\":\"tree\",\"multiValued\":true,\"autoLoad\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"faceting\":true,\"scope\":24265,\"oldThesauriId\":\"715\"}','category_id'),(24077,24075,24075,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24080,24079,24079,'f',0,'_title','Name','Name','Name','Name','varchar',1,'{\"showIn\":\"top\"}',NULL),(24081,24079,24079,'f',0,'nr','Number','Number','Number','Number','varchar',2,'{\"showIn\":\"top\"}',NULL),(24082,24079,24079,'f',0,'_date_start','Date','Date','Date','Date','date',3,'{\"showIn\":\"top\"}',NULL),(24083,24079,24079,'f',0,'_date_end','End date','End date','End date','End date','date',4,'{\"showIn\":\"top\"}',NULL),(24085,24079,24079,'f',0,'lead','Lead','Lead','Lead','Lead','_objects',21,'{\"editor\":\"form\",\"source\":\"users\",\"renderer\":\"listObjIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true}','role_ids2'),(24086,24079,24079,'f',0,'support','Support','Support','Support','Support','_objects',22,'{\"editor\":\"form\",\"source\":\"users\",\"renderer\":\"listObjIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true}','role_ids3'),(24087,24079,24079,'f',0,'court','Court','Court','Court','Court','_objects',5,'{\"editor\":\"form\",\"source\":\"tree\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true,\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24088,24079,24079,'f',0,'office','Office','Офис','Program','Program','_objects',6,'{\r\n\"source\": \"thesauri\"\r\n,\"thesauriId\": \"715\"\r\n,\"multiValued\": true\r\n,\"autoLoad\": true\r\n,\"editor\": \"form\"\r\n,\"renderer\": \"listGreenIcons\"\r\n,\"faceting\": true\r\n}','category_id'),(24089,24079,24079,'f',0,'status','Status','Status','Status','Status','_objects',8,'{\"source\":\"tree\",\"multiValued\":false,\"autoLoad\":true,\"faceting\":true,\"scope\":24259,\"oldThesauriId\":\"356\"}','status'),(24090,24079,24079,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',10,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24399\n,\"multiValued\": true\n,\"editor\": \"form\"\n}\n',NULL),(24091,24079,24079,'f',0,'country','Country','Country','Country','Country','_objects',7,'{\"editor\":\"form\",\"source\":\"tree\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true,\"scope\":24308,\"oldThesauriId\":\"351\"}',NULL),(24096,24095,24095,'f',0,'fname','Name',NULL,'Имя',NULL,'varchar',3,'{\"showIn\":\"top\"}','strings'),(24097,24095,24095,'f',0,'patronymic','Middle name',NULL,'Отчество',NULL,'varchar',4,'{\"showIn\":\"top\"}','strings'),(24098,24095,24095,'f',0,'sex','Sex',NULL,'Пол',NULL,'_objects',5,'{\"source\":\"tree\",\"scope\":24279,\"oldThesauriId\":\"90\"}','ints'),(24099,24095,24095,'f',0,'age','Age',NULL,'Возраст',NULL,'int',6,'[]','ints'),(24100,24095,24095,'f',0,'work','Place of service/work',NULL,'Место службы/работы',NULL,'varchar',7,'[]','strings'),(24101,24095,24095,'f',0,'rank','Rank at the time of the incident',NULL,'Звание на момент происшествия',NULL,'varchar',8,'[]','strings'),(24102,24095,24095,'f',0,'dressing','Outfit',NULL,'Форма одежды',NULL,'_objects',9,'{\"source\":\"tree\",\"oldThesauriId\":\"118\"}','ints'),(24103,24095,24095,'f',0,'drunk_words','Intoxication from the statements of the applicant\r\n',NULL,'Состояние опьянения со слов заявителя',NULL,'_objects',10,'{\"source\":\"tree\",\"scope\":24282,\"oldThesauriId\":\"100\"}','ints'),(24104,24095,24095,'f',0,'nickname','Nickname\r\n',NULL,'Прозвище',NULL,'varchar',11,'[]','strings'),(24105,24095,24095,'f',0,'look','Physical description\r\n',NULL,'Описание внешности',NULL,'varchar',12,'[]','strings'),(24106,24095,24095,'f',0,'distinctive_marks','Special features\r\n',NULL,'Особые приметы',NULL,'varchar',13,'[]','strings'),(24107,24095,24095,'f',0,'lname','Last name\r\n',NULL,'Фамилия',NULL,'varchar',2,'{\"showIn\":\"top\"}','strings'),(24109,24108,24108,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24110,24108,24108,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24111,24108,24108,'f',0,'_content','Content','Content','Content','Content','html',3,'{\"showIn\":\"tabsheet\"}','texts'),(24112,24108,24108,'f',0,'state','State','State','State','State','_objects',1,'{\"source\":\"tree\",\"scope\":24308,\"oldThesauriId\":\"351\"}',NULL),(24113,24108,24108,'f',0,'court','Court','Court','Court','Court','_objects',2,'{\"hint\":\"The main court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24114,24108,24108,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24117,24116,24116,'f',0,'_title','Name',NULL,'Наименование',NULL,'varchar',1,'{\"showIn\":\"top\"}',NULL),(24118,24116,24116,'f',0,'_date_start','Date',NULL,'Дата',NULL,'date',2,'{\"showIn\":\"top\"}','date_start'),(24119,24116,24116,NULL,0,'_content','Content\r\n',NULL,'Содержание',NULL,'html',3,'{\"showIn\":\"tabsheet\"}','texts'),(24120,24116,24116,'f',0,'author','Author','Auteur','Автор',NULL,'_objects',3,'{\"source\":\"tree\",\"scope\":24300,\"oldThesauriId\":\"337\"}','ints'),(24121,24116,24116,'f',0,'language','Language','Langue','Язык',NULL,'_objects',4,'{\"maxInstances\":\"3\",\"source\":\"tree\",\"scope\":24304,\"oldThesauriId\":\"341\"}','ints'),(24123,24122,24122,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\": \"top\"}',NULL),(24124,24122,24122,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\": \"top\"}','date_start'),(24126,24125,24125,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24127,24125,24125,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24128,24125,24125,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24129,24125,24125,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24131,24130,24130,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24132,24130,24130,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24133,24130,24130,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24134,24130,24130,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24136,24135,24135,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24137,24135,24135,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24138,24135,24135,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24139,24135,24135,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24141,24140,24140,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24142,24140,24140,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24143,24140,24140,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24144,24140,24140,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24146,24145,24145,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24147,24145,24145,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24148,24145,24145,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24149,24145,24145,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24152,24151,24151,'f',0,'_title','Name',NULL,'Название',NULL,'varchar',0,'{\"showIn\":\"top\"}',NULL),(24153,24151,24151,'f',0,'_date_start','Date',NULL,'Дата',NULL,'date',1,'{\"showIn\":\"top\"}','date_start'),(24154,24151,24151,'f',0,'language','Language','Langue','Язык',NULL,'_objects',4,'{\"maxInstances\":\"3\",\"source\":\"tree\",\"scope\":24304,\"oldThesauriId\":\"341\"}','ints'),(24156,24155,24155,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\": \"top\"}',NULL),(24157,24155,24155,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\": \"top\"}','date_start'),(24159,24158,24158,'f',0,'_title','Title','Title','Title','Title','varchar',0,'{\"showIn\":\"top\"}',NULL),(24160,24158,24158,'f',0,'court','Court','Court','Court','Court','_objects',1,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24161,24158,24158,'f',0,'_date_start','Date','Date','Date','Date','date',1,'{\"showIn\":\"top\"}','date_start'),(24162,24158,24158,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24164,24163,24163,'f',0,'_title','Title','Title','Title','Title','varchar',1,'{\"showIn\":\"top\"}',NULL),(24165,24163,24163,'f',0,'court','Court','Court','Court','Court','_objects',4,'{\"hint\":\"Other court if not the same as the main case Court\",\"source\":\"tree\",\"scope\":24390,\"oldThesauriId\":\"724\"}',NULL),(24166,24163,24163,'f',0,'_date_start','Date','Date','Date','Date','date',2,'{\"showIn\":\"top\"}','date_start'),(24167,24163,24163,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',5,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24168,24163,24163,'f',0,'appnr','Application Nr.','Application Nr.','Application Nr.','Application Nr.','varchar',3,'[]',NULL),(24171,24170,24170,'f',0,'l1','Full name (en)','Nom complet (en)','Полное имя (en)',NULL,'varchar',1,'[]',NULL),(24172,24170,24170,'f',0,'l2','Full name (fr)','Nom complet (fr)','Полное имя (fr)',NULL,'varchar',2,'[]',NULL),(24173,24170,24170,'f',0,'l3','Full name (ru)','Nom complet (ru)','Полное имя (ru)',NULL,'varchar',3,'[]',NULL),(24174,24170,24170,'f',0,'sex','Sex','Sexe','Пол',NULL,'_sex',5,'{\"thesauriId\":\"90\"}',NULL),(24175,24170,24170,'f',0,'birth','Birthday','Anniversaire','Дата рождения',NULL,'date',6,'[]',NULL),(24176,24170,24170,'f',0,'citizenship','Citizenship','Citoyenneté','Гражданство',NULL,'_objects',7,'{\"source\":\"tree\",\"oldThesauriId\":\"310\"}',NULL),(24177,24170,24170,'f',0,'nationality','Nationality','Nationalité','Национальность',NULL,'_objects',8,'{\"source\":\"tree\",\"oldThesauriId\":\"309\"}',NULL),(24178,24170,24170,'f',0,'email','E-mail','E-mail','E-mail',NULL,'varchar',10,'{\"maxInstances\":\"3\"}',NULL),(24179,24170,24170,'f',0,'description','Description','Description','Примечание',NULL,'varchar',14,'[]',NULL),(24180,24170,24170,'f',0,'address','Address','Adresse','Адрес',NULL,'varchar',13,'{\"maxInstances\":\"5\"}',NULL),(24181,24170,24170,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',11,'{\"maxInstances\":\"10\"}',NULL),(24182,24170,24170,'f',0,'type','Type','Type','Тип',NULL,'_objects',4,'{\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"oldThesauriId\":\"324\"}',NULL),(24184,24183,24183,'f',0,'l1','Full name (en)','Nom complet (en)','Полное имя (en)',NULL,'varchar',1,'[]',NULL),(24185,24183,24183,'f',0,'l2','Full name (fr)','Nom complet (fr)','Полное имя (fr)',NULL,'varchar',2,'[]',NULL),(24186,24183,24183,'f',0,'l3','Full name (ru)','Nom complet (ru)','Полное имя (ru)',NULL,'varchar',3,'[]',NULL),(24187,24183,24183,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',11,'{\"maxInstances\":\"10\"}',NULL),(24188,24183,24183,'f',0,'фах','Fax','Télécopieur','Фах',NULL,'varchar',12,'{\"maxInstances\":\"5\"}',NULL),(24189,24183,24183,'f',0,'postal_index','Postal index','Indice postal','Почтовый индекс',NULL,'varchar',13,'[]',NULL),(24190,24183,24183,'f',0,'address','Address','Adresse','Адрес',NULL,'varchar',14,'{\"maxInstances\":\"5\"}',NULL),(24191,24183,24183,'f',0,'description','Description','Description','Примечание',NULL,'varchar',16,'[]',NULL),(24192,24183,24183,'f',0,'regions','Regions','Régions','Регионы',NULL,'_objects',15,'{\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"oldThesauriId\":\"283\"}',NULL),(24193,24183,24183,'f',0,'type','Type','Type','Тип',NULL,'_objects',4,'{\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"oldThesauriId\":\"277\"}',NULL),(24196,24195,24195,'f',0,'_title','Title','Title','Title','Title','varchar',1,'{\"showIn\":\"top\"}',NULL),(24197,24195,24195,'f',0,'_date_start','Date','Date','Date','Date','date',2,'{\"showIn\":\"top\"}',NULL),(24198,24195,24195,'f',0,'content','Content','Content','Content','Content','html',10,'{\"showIn\": \"tabSheet\"}',NULL),(24199,24195,24195,'f',0,'office','Office','Офис','Program','Program','_objects',5,'{\n\"source\": \"tree\"\n,\"renderer\": \"listGreenIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"multiValued\": true\n,\"editor\": \"form\"\n}\n','category_id'),(24200,24195,24195,'f',0,'tags','Tags','Tags','Tags','Tags','popuplist',3,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24399\n,\"multiValued\": true\n,\"editor\": \"form\"\n}',NULL),(24201,24043,24043,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\"}',NULL),(24202,24043,24043,NULL,0,'type','Type','Type','Type','Type','_fieldTypesCombo',5,'[]',NULL),(24203,24043,24043,NULL,0,'order','Order','Order','Order','Order','int',6,'[]',NULL),(24204,24043,24043,NULL,0,'cfg','Config','Config','Config','Config','text',7,'{\"height\":100}',NULL),(24205,24043,24043,NULL,0,'solr_column_name','Solr column name','Solr column name','Solr column name','Solr column name','varchar',8,'[]',NULL),(24206,24043,24043,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(24207,24043,24043,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',2,'[]',NULL),(24208,24044,24044,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\",\"rea-dOnly\":true}',NULL),(24209,24044,24044,NULL,0,'type','Type','Type','Type','Type','_templateTypesCombo',5,'[]',NULL),(24210,24044,24044,NULL,0,'visible','Active','Active','Active','Active','checkbox',6,'{\"showIn\":\"top\"}',NULL),(24211,24044,24044,NULL,0,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',7,'[]',NULL),(24212,24044,24044,NULL,0,'cfg','Config','Config','Config','Config','text',8,'{\"height\":100}',NULL),(24213,24044,24044,NULL,0,'title_template','Title template','Title template','Title template','Title template','text',9,'{\"height\":50}',NULL),(24214,24044,24044,NULL,0,'info_template','Info template','Info template','Info template','Info template','text',10,'{\"height\":50}',NULL),(24215,24044,24044,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(24216,24044,24044,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',2,'[]',NULL),(24218,24217,24217,NULL,0,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',5,'[]',NULL),(24219,24217,24217,NULL,0,'visible','Active','Active','Active','Active','checkbox',6,'[]',NULL),(24220,24217,24217,NULL,0,'order','Order','Order','Order','Order','int',7,'[]',NULL),(24221,24217,24217,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',0,'{\"showIn\":\"top\"}',NULL),(24222,24217,24217,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',1,'{\"showIn\":\"top\"}',NULL),(24443,24072,24072,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24444,24072,24072,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24445,24444,24072,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24446,24444,24072,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24447,24444,24072,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24448,24444,24072,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24449,24072,24072,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24450,24072,24072,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24451,24072,24072,NULL,0,'category','Programs','Programs',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n,\"multiValued\": true\n,\"editor\": \"form\"\n}',NULL),(24452,24072,24072,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24453,24072,24072,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24454,24453,24072,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24455,24453,24072,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24456,24073,24073,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24457,24073,24073,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24458,24457,24073,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24459,24457,24073,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24460,24457,24073,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24461,24457,24073,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24462,24073,24073,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24463,24073,24073,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24464,24073,24073,NULL,0,'category','Category','Category',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"editor\": \"form\"\r\n,\"multiValued\": true\r\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n}',NULL),(24465,24073,24073,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24466,24073,24073,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24467,24466,24073,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24468,24466,24073,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24469,24078,24078,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24470,24078,24078,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24471,24470,24078,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24472,24470,24078,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24473,24470,24078,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24474,24470,24078,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24475,24078,24078,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24476,24078,24078,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24477,24078,24078,NULL,0,'category','Category','Category',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"editor\": \"form\"\r\n,\"multiValued\": true\r\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n}',NULL),(24478,24078,24078,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24479,24078,24078,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24480,24479,24078,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24481,24479,24078,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24485,24484,24484,NULL,NULL,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',5,'[]',NULL),(24486,24484,24484,NULL,NULL,'visible','Active','Active','Active','Active','checkbox',6,'[]',NULL),(24487,24484,24484,NULL,NULL,'order','Order','Order','Order','Order','int',7,'[]',NULL),(24488,24484,24484,NULL,NULL,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',0,'{\"showIn\":\"top\"}',NULL),(24489,24484,24484,NULL,NULL,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',1,'{\"showIn\":\"top\"}',NULL),(24490,24484,24484,NULL,0,'managers','Managers','Менеджеры',NULL,NULL,'_objects',3,'{\n\"editor\": \"form\"\n,\"source\": \"users\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"multiValued\": true\n,\"faceting\": true\n}','user_ids'),(24514,24072,24072,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24515,24078,24078,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24516,24073,24073,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24517,24484,24484,NULL,0,'security_group','Users group','Группа пользователей',NULL,NULL,'_objects',2,'{\n\"source\": \"groups\"\n,\"autoLoad\": true\n}',NULL),(24523,24074,24074,NULL,NULL,'_title','Name','Название',NULL,NULL,'varchar',1,NULL,NULL),(24823,24822,24822,NULL,NULL,'_title','Text','Текст',NULL,NULL,'memo',0,'{\n\"height\": 100\n}','content');

/*Table structure for table `translations` */

DROP TABLE IF EXISTS `translations`;

CREATE TABLE `translations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varbinary(100) DEFAULT NULL,
  `l1` varchar(250) DEFAULT NULL,
  `l2` varchar(250) DEFAULT NULL,
  `l3` varchar(250) DEFAULT NULL,
  `l4` varchar(250) DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - anywhere, 1 - server, 2 - client',
  `udate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_translations_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `translations` */

/*Table structure for table `tree` */

DROP TABLE IF EXISTS `tree`;

CREATE TABLE `tree` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(20) unsigned DEFAULT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned DEFAULT NULL,
  `subtype` smallint(5) unsigned NOT NULL DEFAULT '0',
  `template_id` int(10) unsigned DEFAULT NULL,
  `tag_id` int(10) unsigned DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(1000) DEFAULT NULL,
  `date` datetime DEFAULT NULL COMMENT 'start date',
  `date_end` datetime DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT NULL,
  `cfg` text,
  `inherit_acl` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'inherit the access permissions from parent',
  `cid` int(10) unsigned DEFAULT NULL COMMENT 'creator id',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation date',
  `uid` int(10) DEFAULT NULL COMMENT 'updater id',
  `udate` timestamp NULL DEFAULT NULL COMMENT 'update date',
  `updated` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1st bit - node updated, 2nd - security updated, 3rd - node moved',
  `oid` int(11) DEFAULT NULL COMMENT 'owner id',
  `did` int(10) unsigned DEFAULT NULL COMMENT 'delete user id',
  `ddate` timestamp NULL DEFAULT NULL,
  `dstatus` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'delete item status: 0 - not deleted, 1 - deleted, 2 - parent deleted',
  PRIMARY KEY (`id`),
  KEY `tree_tag_id` (`tag_id`),
  KEY `tree_pid` (`pid`),
  KEY `tree_updated` (`updated`),
  KEY `IDX_tree_date__date_end` (`date`,`date_end`),
  KEY `tree_template_id` (`template_id`),
  CONSTRAINT `tree_pid` FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25079 DEFAULT CHARSET=utf8;

/*Data for the table `tree` */

insert  into `tree`(`id`,`pid`,`user_id`,`system`,`type`,`subtype`,`template_id`,`tag_id`,`target_id`,`name`,`date`,`date_end`,`size`,`is_main`,`cfg`,`inherit_acl`,`cid`,`cdate`,`uid`,`udate`,`updated`,`oid`,`did`,`ddate`,`dstatus`) values (1,NULL,NULL,1,1,0,24074,NULL,NULL,'Tree',NULL,NULL,NULL,1,'[]',1,1,'2012-11-17 17:10:21',1,'2014-01-17 13:53:00',0,1,NULL,NULL,0),(23432,NULL,49,1,1,2,24074,NULL,NULL,'[Favorites]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23433,23432,49,1,1,1,24074,NULL,NULL,'[Recent]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23434,NULL,49,1,1,3,24074,NULL,NULL,'[MyCaseBox]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23435,23434,49,1,1,4,24074,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23436,23434,49,1,1,5,24074,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23437,23436,49,1,1,1,24074,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23438,23436,49,1,1,1,24074,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23439,23436,49,1,1,1,24074,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23440,23434,49,1,1,6,24074,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23441,23440,49,1,1,1,24074,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23442,23440,49,1,1,1,24074,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23443,23434,49,1,1,7,24074,NULL,NULL,'[PrivateArea]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23444,NULL,NULL,1,1,8,24074,NULL,NULL,'Casebox',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23445,23444,NULL,1,1,4,24074,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23446,23444,NULL,1,1,5,24074,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23447,23446,NULL,1,1,1,24074,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23448,23446,NULL,1,1,1,24074,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23449,23446,NULL,1,1,1,24074,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23450,23444,NULL,1,1,6,24074,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23451,23450,NULL,1,1,1,24074,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23452,23450,NULL,1,1,1,24074,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23490,1,227,0,1,0,24074,NULL,NULL,'Demo',NULL,NULL,NULL,NULL,'[]',1,227,'2013-01-15 10:56:59',1,'2014-01-17 13:53:07',0,227,NULL,NULL,0),(23491,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Resp to Govt observations-BB-10.24.11 (tracked).doc','2011-10-03 00:00:00','2011-10-03 00:00:00',435200,NULL,'[]',1,7,'2013-01-15 15:09:48',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23492,23490,NULL,0,3,0,24079,NULL,NULL,'0709-Akmatov','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,5,'2013-01-15 11:09:16',1,'2014-01-17 14:11:17',0,5,NULL,NULL,0),(23493,23492,NULL,1,1,9,24074,289,NULL,'1-Summaries',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23494,23492,NULL,1,1,9,24074,290,NULL,'2-Correspondence',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23495,23492,NULL,1,1,9,24074,291,NULL,'3-Meetings',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23496,23492,NULL,1,1,9,24074,292,NULL,'4-Filings',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23497,23492,NULL,1,1,9,24074,293,NULL,'5-OSJI Filings',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23498,23492,NULL,1,1,9,24074,294,NULL,'6-Evidence',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23499,23492,NULL,1,1,9,24074,295,NULL,'7-Advocacy',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23500,23492,NULL,1,1,9,24074,297,NULL,'9-Administrative',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23501,23492,NULL,1,1,9,24074,712,NULL,'0-Incoming',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23502,23492,NULL,1,1,9,24074,713,NULL,'8-Research',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-15 11:09:16',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23508,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-1-Case Summary-NE-9.18.08.doc','2013-01-15 00:00:00',NULL,31232,NULL,'[]',1,5,'2013-01-15 11:13:53',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23509,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-1-Preliminary Assessment-NE-2.27.07.doc','2013-01-15 00:00:00',NULL,35840,NULL,'[]',1,5,'2013-01-15 11:13:53',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23510,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-1-press release - KSz-04.06.11.doc','2013-01-15 00:00:00',NULL,27648,NULL,'[]',1,5,'2013-01-15 11:13:53',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23511,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Chronology-EM-3.24.10.doc','2013-01-15 00:00:00',NULL,97792,NULL,'[]',1,34,'2013-01-15 11:14:44',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23512,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Initial Case Outline-EM-3.31.10.doc','2013-01-15 00:00:00',NULL,69120,NULL,'[]',1,34,'2013-01-15 11:14:44',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23513,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Initial Questions-EM-3.24.10.doc','2013-01-15 00:00:00',NULL,49664,NULL,'[]',1,34,'2013-01-15 11:14:44',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23514,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-7-Website Case Reports-BB-02 16 11.doc','2013-01-15 00:00:00',NULL,55808,NULL,'[]',1,8,'2013-01-15 11:15:35',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23515,23493,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-7-Website Case Reports-RS-4.5.11.doc','2013-01-15 00:00:00',NULL,51712,NULL,'[]',1,8,'2013-01-15 11:15:35',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23516,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Cvr Ltr to HRC Communication-RS-4.7.11.doc','2013-01-15 00:00:00',NULL,31232,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23517,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Cvr Ltr to HRC Communication-RS-4.7.11.pdf','2013-01-15 00:00:00',NULL,27550,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23518,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Cvr Ltr to HRC Reply-SB-11.10.11.doc','2013-01-15 00:00:00',NULL,26624,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23519,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Cvr Ltr to HRC Reply-SB-11.10.11.pdf','2013-01-15 00:00:00',NULL,250837,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23520,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat answering questions-08.17.2010.htm','2013-01-15 00:00:00',NULL,36688,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23521,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat clarifying medical examinations-11.22.2010.htm','2013-01-15 00:00:00',NULL,14748,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23522,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on exhaustion documentation-11.19.2010.htm','2013-01-15 00:00:00',NULL,14373,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23523,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on fire at prosecution office-08.26.2010.txt','2013-01-15 00:00:00',NULL,3092,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23524,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on judicial procedure-10.21.2010.htm','2013-01-15 00:00:00',NULL,23180,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23525,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on missing evidence-08.16.2010.htm','2013-01-15 00:00:00',NULL,36688,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23526,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on Muminov\'s record of complaint against Akmatov-11.23.2010.htm','2013-01-15 00:00:00',NULL,11989,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23527,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on PoA and statement-10.19.2010.htm','2013-01-15 00:00:00',NULL,12987,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23528,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on PoA with eng-rus attachments of PoA scans-11.12.2010.htm','2013-01-15 00:00:00',NULL,39166,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23529,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat on reasons for investigation reinstatements-12.6.2010.htm','2013-01-15 00:00:00',NULL,25508,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23530,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat with attached english translation of Muminov recording of complaint-11.16.2010.htm','2013-01-15 00:00:00',NULL,44772,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23531,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat with missing evidence attachments in english-11.16.2010.htm','2013-01-15 00:00:00',NULL,45181,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23532,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat with missing evidence attachments in english-11.3.2010.htm','2013-01-15 00:00:00',NULL,23270,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23533,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat with missing evidence attachments in russian-11.17.2010.htm','2013-01-15 00:00:00',NULL,47652,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23534,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Email from Nurzat-8.27.2010.txt','2013-01-15 00:00:00',NULL,1295,NULL,'[]',1,5,'2013-01-15 11:17:49',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23535,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl govt observations-C-9.14.11.pdf','2013-01-15 00:00:00',NULL,285824,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23536,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl govt observations-ENG-9.14.11.doc','2013-01-15 00:00:00',NULL,41472,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23537,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl govt submission-C-2 6 12 (ENG).doc','2013-01-15 00:00:00',NULL,32256,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23538,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl govt submission-C-2.6.12 (ENG).doc','2013-01-15 00:00:00',NULL,33280,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23539,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl govt submission-C-2.6.12.pdf','2013-01-15 00:00:00',NULL,298720,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23540,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI encl rules of procedure-C-4.21.11.pdf','2013-01-15 00:00:00',NULL,610065,NULL,'[]',1,34,'2013-01-15 11:18:48',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23541,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI re communication of case-C-4.21.11.pdf','2013-01-15 00:00:00',NULL,47941,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23542,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI re receipt of 11.10 submission-C-11.17.11.pdf','2013-01-15 00:00:00',NULL,54276,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23543,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI re receipt of comments-C-4 13 12.doc','2013-01-15 00:00:00',NULL,30720,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23544,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI re receipt of comments-C-4.13.12.pdf','2013-01-15 00:00:00',NULL,33554,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23545,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-HRC to JI_RUS-04.21.2011.doc','2013-01-15 00:00:00',NULL,29184,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23546,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI Cvr ltr to HRC encl Reply and Statement-SB-4.6.12.pdf','2013-01-15 00:00:00',NULL,54037,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23547,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs As Sent-SB-06.18.12.pdf','2013-01-15 00:00:00',NULL,58379,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23548,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-BB-05.29.12.docx','2013-01-15 00:00:00',NULL,24945,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23549,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-BB-05.31.12.docx','2013-01-15 00:00:00',NULL,24441,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23550,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-BB-06.18.12.docx','2013-01-15 00:00:00',NULL,24624,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23551,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-BB-6 6 12.docx','2013-01-15 00:00:00',NULL,25431,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23552,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-RS-6.5.12.docx','2013-01-15 00:00:00',NULL,24788,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23553,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI ltr to HRC re investigation docs-SB-06.18.12.docx','2013-01-15 00:00:00',NULL,24658,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23554,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-JI to HRC encl Reply and Statement As Sent-SB-4.6.12.pdf','2013-01-15 00:00:00',NULL,262089,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23555,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Letter to Physicians for Human Rights-EM-4.5.10.doc','2013-01-15 00:00:00',NULL,30720,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23556,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-2-Requests for Information-EM-3.31.10.doc','2013-01-15 00:00:00',NULL,53760,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23557,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-6-reply from the Prosecutor\'s office-04.21.2012 (RUS).pdf','2013-01-15 00:00:00',NULL,288566,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23558,23494,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-6-reply of the Prosecutor\'s office-04.21.2012 (ENG).doc','2013-01-15 00:00:00',NULL,30208,NULL,'[]',1,8,'2013-01-15 11:22:24',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23559,23497,NULL,0,5,0,24075,NULL,NULL,'Akmatov pdf HRC complaint in Russian.pdf','2013-01-15 00:00:00',NULL,62963343,NULL,'[]',1,5,'2013-01-15 11:26:04',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23560,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication As Filed-RS-4.7.11.doc','2013-01-15 00:00:00',NULL,442368,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23561,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication As Filed-RS-4.7.11.pdf','2013-01-15 00:00:00',NULL,729723,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23562,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication Public-SB-5.11.11.doc','2013-01-15 00:00:00',NULL,431616,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23563,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication Public-SB-5.11.11.pdf','2013-01-15 00:00:00',NULL,737213,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23564,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication-BB-04 06 11_RUS.doc','2013-01-15 00:00:00',NULL,769024,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23565,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Cvr Ltr encl HRC Reply-SB-11.10.11.pdf','2013-01-15 00:00:00',NULL,218356,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23566,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Govt observations on Communication_ENG-G-9.14.11.doc','2013-01-15 00:00:00',NULL,41472,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23567,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-HRC ltr encl govt observations-C-9.14.11.pdf','2013-01-15 00:00:00',NULL,285825,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23568,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-List of Documents-BB-01.31.11.doc','2013-01-15 00:00:00',NULL,51712,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23569,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-List of Documents-EM-12.10.10.doc','2013-01-15 00:00:00',NULL,44032,NULL,'[]',1,5,'2013-01-15 11:29:18',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23570,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Resp to Govt observations As Filed-RS-4.5.12.doc','2013-01-15 00:00:00',NULL,331776,NULL,'[]',1,34,'2013-01-15 11:29:57',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23571,23497,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Resp to Govt observations As Filed-SB-4.5.12.pdf','2013-01-15 00:00:00',NULL,186463,NULL,'[]',1,8,'2013-01-15 11:30:16',1,'2014-01-17 14:11:16',0,8,NULL,NULL,0),(23572,23497,5,0,1,0,24074,NULL,NULL,'Drafts',NULL,NULL,NULL,NULL,'[]',1,5,'2013-01-15 11:31:58',1,'2014-01-17 13:53:07',0,5,NULL,NULL,0),(23583,23572,NULL,0,5,0,24075,NULL,NULL,'7381_20411_ICCPR 2009_Alt report Rus NGOs_Oct 2009_eng.doc','2013-01-15 00:00:00','2013-01-15 00:00:00',603136,NULL,'[]',1,5,'2013-01-15 14:44:31',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23590,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication-SB-1.24.13.doc','2013-01-15 00:00:00','2013-01-15 00:00:00',442368,NULL,'[]',1,7,'2013-01-24 22:18:47',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23591,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Facts Section-EM-4.12.10.doc','2013-01-15 00:00:00',NULL,127488,NULL,'[]',1,5,'2013-01-15 11:32:20',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23592,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Legal Argument Right to Life-EM-5.3.10.doc','2013-01-15 00:00:00',NULL,71168,NULL,'[]',1,5,'2013-01-15 11:32:20',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23593,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Legal Arguments-SB-1.28.13.doc','2013-01-15 00:00:00','2013-01-15 00:00:00',238080,NULL,'[]',1,7,'2013-01-28 23:04:58',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23594,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Legal Arguments-EM-7.19.10.doc','2013-01-15 00:00:00',NULL,257536,NULL,'[]',1,5,'2013-01-15 11:32:20',1,'2014-01-17 14:11:16',0,5,NULL,NULL,0),(23596,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Resp to Govt observations-SB-1.23.13.doc','2013-01-15 00:00:00','2013-01-15 00:00:00',288256,NULL,'[]',1,7,'2013-01-23 14:57:09',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23600,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Resp to Govt observations-SB-1.21.13.doc','2013-01-15 00:00:00','2013-01-15 00:00:00',332288,NULL,'[]',1,7,'2013-01-22 03:04:30',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23613,23496,NULL,0,4,2,24130,NULL,NULL,'Decision from court N.182','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,34,'2013-01-15 13:24:24',1,'2014-01-17 14:11:18',0,34,NULL,NULL,0),(23614,23496,NULL,0,4,2,24145,NULL,NULL,'Our complaint had been received by the court','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,34,'2013-01-15 13:25:03',1,'2014-01-17 14:11:18',0,34,NULL,NULL,0),(23615,23497,NULL,0,4,3,24163,NULL,NULL,'Application to ECHR','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,34,'2013-01-15 13:25:57',1,'2014-01-17 14:11:18',0,34,NULL,NULL,0),(23616,23497,NULL,0,4,2,24135,NULL,NULL,'Judgement on the merits','2013-01-01 00:00:00','2013-01-01 00:00:00',NULL,NULL,'[]',1,34,'2013-01-15 13:57:45',1,'2014-01-17 14:11:18',0,34,NULL,NULL,0),(23617,23615,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-6-appellate court decision_RUS-02.15.2011.pdf','2013-01-15 00:00:00',NULL,104374,NULL,'[]',1,34,'2013-01-15 13:59:45',1,'2014-01-17 14:11:16',0,34,NULL,NULL,0),(23618,23497,NULL,0,4,3,24158,NULL,NULL,'Some general comments about the case','2013-01-03 00:00:00','2013-01-03 00:00:00',NULL,NULL,'[]',1,34,'2013-01-15 14:02:21',1,'2014-01-17 14:11:18',0,34,NULL,NULL,0),(23620,23501,NULL,0,5,0,24075,NULL,NULL,'40022816ContrOSI (1).doc','2013-01-15 00:00:00',NULL,75264,NULL,'[]',1,7,'2013-01-15 15:03:00',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23621,23501,NULL,0,5,0,24075,NULL,NULL,'7381_20411_ICCPR 2009_Alt report Rus NGOs_Oct 2009_eng.doc','2013-01-15 00:00:00',NULL,603136,NULL,'[]',1,7,'2013-01-15 15:03:00',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23623,23572,NULL,0,5,0,24075,NULL,NULL,'Procedures+Manual+8-29-12.doc','2013-01-15 00:00:00',NULL,1851392,NULL,'[]',1,7,'2013-01-15 15:14:06',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23624,23492,NULL,0,4,1,24108,NULL,NULL,'Akmatov case card','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,8,'2013-01-15 15:23:14',1,'2014-01-17 14:11:18',0,8,NULL,NULL,0),(23634,1,NULL,0,4,2,24145,NULL,NULL,'Monthly Report - February 2013','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,49,'2013-01-15 15:52:42',1,'2014-01-17 14:11:18',0,49,NULL,NULL,0),(23636,1,NULL,0,4,3,24158,NULL,NULL,'Monthly Report','2013-01-15 00:00:00','2013-01-15 00:00:00',NULL,NULL,'[]',1,49,'2013-01-15 15:54:44',1,'2014-01-17 14:11:18',0,49,NULL,NULL,0),(23653,23572,NULL,0,5,0,24075,NULL,NULL,'CAT-0807-Akmatov-5-Communication Draft-BB-04.06.11.doc','2011-04-06 00:00:00','2011-04-06 00:00:00',492544,NULL,'[]',1,7,'2013-01-22 03:43:35',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23656,23572,NULL,0,5,0,24075,NULL,NULL,'Copy of CAT-0807-Akmatov-5-Communication Draft-BB-02.10.11.doc','2011-02-10 00:00:00',NULL,556032,NULL,'[]',1,7,'2013-01-22 03:43:35',1,'2014-01-17 14:11:16',0,7,NULL,NULL,0),(23681,1,NULL,0,4,2,24125,NULL,NULL,'Received communication from Turkish Government','2013-01-23 00:00:00','2013-01-23 00:00:00',NULL,NULL,'[]',1,26,'2013-01-23 15:41:04',1,'2014-01-17 14:11:18',0,26,NULL,NULL,0),(23684,23492,NULL,0,4,2,24145,NULL,NULL,'Letter setting hearing date','2013-01-22 00:00:00','2013-01-22 00:00:00',NULL,NULL,'[]',1,7,'2013-01-24 22:48:57',1,'2014-01-17 14:11:18',0,7,NULL,NULL,0),(23730,NULL,1,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-03-28 08:10:02',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23731,23730,1,1,1,6,24074,NULL,NULL,'[Emails]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-03-28 08:20:03',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23732,23730,1,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-04-22 13:27:18',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23733,NULL,239,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-17 08:48:05',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23734,23733,239,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-17 08:48:05',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23735,NULL,232,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-23 17:05:04',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23736,23735,232,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-23 17:05:04',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23741,NULL,240,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-26 08:18:39',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23742,23741,240,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-26 08:18:39',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23744,NULL,241,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-27 13:13:54',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23745,23744,241,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-27 13:13:54',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23748,23734,NULL,0,4,0,24195,NULL,NULL,'Action test 31.05.2013','2013-05-31 00:00:00','2013-05-31 00:00:00',NULL,NULL,'[]',1,239,'2013-05-31 13:40:29',1,'2014-01-17 14:11:18',0,239,NULL,NULL,0),(23807,NULL,250,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-06-04 10:44:29',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23808,23807,250,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-06-04 10:44:29',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23809,NULL,7,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-06-04 12:21:09',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23810,23809,7,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-06-04 12:21:09',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23811,23808,NULL,0,5,0,24075,NULL,NULL,'oe2_geotag.PNG','2013-06-04 13:06:35','2013-06-04 13:06:35',117605,NULL,'[]',1,250,'2013-06-04 13:06:35',1,'2014-01-17 14:11:17',0,250,NULL,NULL,0),(23815,23734,NULL,0,4,0,24079,NULL,NULL,'12','2013-06-18 00:00:00','0000-00-00 00:00:00',NULL,NULL,'[]',1,239,'2013-06-18 14:13:45',1,'2014-01-17 14:11:17',0,239,NULL,NULL,0),(23816,23815,NULL,0,4,0,24074,289,NULL,'1-Summaries','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23817,23815,NULL,0,4,0,24074,290,NULL,'2-Correspondence','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23818,23815,NULL,0,4,0,24074,291,NULL,'3-Meetings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23819,23815,NULL,0,4,0,24074,292,NULL,'4-Filings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23820,23815,NULL,0,4,0,24074,293,NULL,'5-OSJI Filings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23821,23815,NULL,0,4,0,24074,294,NULL,'6-Evidence','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23822,23815,NULL,0,4,0,24074,295,NULL,'7-Advocacy','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23823,23815,NULL,0,4,0,24074,297,NULL,'9-Administrative','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23824,23815,NULL,0,4,0,24074,712,NULL,'0-Incoming','2013-06-18 14:13:47',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:47',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23825,23815,NULL,0,4,0,24074,713,NULL,'8-Research','2013-06-18 14:13:47',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:47',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23827,1,NULL,0,5,0,24075,NULL,NULL,'Search including in results table values from thesauri.docx','2013-06-18 16:02:02','2013-06-18 16:02:02',15281,NULL,'[]',1,250,'2013-06-18 16:02:02',1,'2014-01-17 14:11:17',0,250,NULL,NULL,0),(23883,NULL,254,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-09-24 13:13:13',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23884,23883,254,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-09-24 13:13:13',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23885,NULL,256,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-09-24 14:07:42',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23886,23885,256,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-09-24 14:07:42',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23940,1,NULL,0,4,0,24074,NULL,NULL,'Thesauri','2013-09-24 19:38:09',NULL,NULL,NULL,'[]',1,256,'2013-09-24 19:38:09',1,'2014-01-17 13:53:08',0,256,NULL,NULL,0),(24042,1,NULL,0,NULL,0,24074,NULL,NULL,'Templates',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(24043,24052,NULL,0,NULL,0,24044,NULL,NULL,'Fields template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(24044,24052,NULL,0,NULL,0,24044,NULL,NULL,'Templates template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(24052,24042,NULL,0,NULL,0,24074,NULL,NULL,'System',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(24053,24052,NULL,0,NULL,0,24044,NULL,NULL,'User',NULL,NULL,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24054,24053,NULL,0,NULL,0,24043,NULL,NULL,'l1',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24055,24053,NULL,0,NULL,0,24043,NULL,NULL,'l2',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24056,24053,NULL,0,NULL,0,24043,NULL,NULL,'l3',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24057,24053,NULL,0,NULL,0,24043,NULL,NULL,'initials',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24058,24053,NULL,0,NULL,0,24043,NULL,NULL,'sex',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24059,24053,NULL,0,NULL,0,24043,NULL,NULL,'position',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24060,24053,NULL,0,NULL,0,24043,NULL,NULL,'email',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24061,24053,NULL,0,NULL,0,24043,NULL,NULL,'language_id',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24062,24053,NULL,0,NULL,0,24043,NULL,NULL,'short_date_format',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24063,24053,NULL,0,NULL,0,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24064,24053,NULL,0,NULL,0,24043,NULL,NULL,'room',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24065,24053,NULL,0,NULL,0,24043,NULL,NULL,'phone',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24066,24053,NULL,0,NULL,0,24043,NULL,NULL,'location',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24067,24052,NULL,0,NULL,0,24044,NULL,NULL,'email',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24068,24067,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24069,24067,NULL,0,NULL,0,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24070,24067,NULL,0,NULL,0,24043,NULL,NULL,'from',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24071,24067,NULL,0,NULL,0,24043,NULL,NULL,'_content',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24072,24052,NULL,0,NULL,0,24044,NULL,NULL,'tasks',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24073,24052,NULL,0,NULL,0,24044,NULL,NULL,'event',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24074,24052,NULL,0,NULL,0,24044,NULL,NULL,'folder',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24075,24052,NULL,0,NULL,0,24044,NULL,NULL,'file_template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24076,24075,NULL,0,NULL,0,24043,NULL,NULL,'program',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24077,24075,NULL,0,NULL,0,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24078,24052,NULL,0,NULL,0,24044,NULL,NULL,'milestone',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-01-17 14:21:08',0,1,NULL,NULL,0),(24079,24042,NULL,0,NULL,0,24044,NULL,NULL,'case_template',NULL,NULL,NULL,NULL,'{\"system_folders\":\"350\"}',1,1,'2014-01-17 13:50:50',1,'2014-01-23 08:31:16',0,1,NULL,NULL,0),(24080,24079,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24081,24079,NULL,0,NULL,0,24043,NULL,NULL,'nr',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24082,24079,NULL,0,NULL,0,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24083,24079,NULL,0,NULL,0,24043,NULL,NULL,'_date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24084,24079,NULL,0,NULL,0,24043,NULL,NULL,'manager',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,240,'2014-02-28 15:05:49',1),(24085,24079,NULL,0,NULL,0,24043,NULL,NULL,'lead',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24086,24079,NULL,0,NULL,0,24043,NULL,NULL,'support',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24087,24079,NULL,0,NULL,0,24043,NULL,NULL,'court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24088,24079,NULL,0,NULL,0,24043,NULL,NULL,'office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',240,'2014-02-28 15:05:10',0,1,NULL,NULL,0),(24089,24079,NULL,0,NULL,0,24043,NULL,NULL,'status',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24090,24079,NULL,0,NULL,0,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-06 15:27:14',0,1,NULL,NULL,0),(24091,24079,NULL,0,NULL,0,24043,NULL,NULL,'country',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24195,24042,NULL,0,NULL,0,24044,NULL,NULL,'Action',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:51',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24196,24195,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24197,24195,NULL,0,NULL,0,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24198,24195,NULL,0,NULL,0,24043,NULL,NULL,'content',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',240,'2014-02-06 22:43:28',0,1,NULL,NULL,0),(24199,24195,NULL,0,NULL,0,24043,NULL,NULL,'office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',240,'2014-02-28 15:04:09',0,1,NULL,NULL,0),(24200,24195,NULL,0,NULL,0,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',240,'2014-02-06 22:50:07',0,1,NULL,NULL,0),(24201,24043,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-01-21 11:24:06',0,1,NULL,NULL,0),(24202,24043,NULL,0,NULL,0,24043,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24203,24043,NULL,0,NULL,0,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24204,24043,NULL,0,NULL,0,24043,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24205,24043,NULL,0,NULL,0,24043,NULL,NULL,'solr_column_name',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24206,24043,NULL,0,NULL,0,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24207,24043,NULL,0,NULL,0,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24208,24044,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-12 21:12:31',0,1,NULL,NULL,0),(24209,24044,NULL,0,NULL,0,24043,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24210,24044,NULL,0,NULL,0,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24211,24044,NULL,0,NULL,0,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24212,24044,NULL,0,NULL,0,24043,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24213,24044,NULL,0,NULL,0,24043,NULL,NULL,'title_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24214,24044,NULL,0,NULL,0,24043,NULL,NULL,'info_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24215,24044,NULL,0,NULL,0,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24216,24044,NULL,0,NULL,0,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24217,24052,NULL,0,NULL,0,24044,NULL,NULL,'Thesauri Item',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:12:11',0,1,NULL,NULL,0),(24218,24217,NULL,0,NULL,0,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24219,24217,NULL,0,NULL,0,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24220,24217,NULL,0,NULL,0,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24221,24217,NULL,0,NULL,0,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24222,24217,NULL,0,NULL,0,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24223,23940,NULL,0,NULL,0,24074,NULL,NULL,'System',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24224,24223,NULL,0,NULL,0,24074,NULL,NULL,'Phases',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24225,24224,NULL,0,NULL,0,24217,NULL,NULL,'preliminary check',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24226,24224,NULL,0,NULL,0,24217,NULL,NULL,'investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24227,24224,NULL,0,NULL,0,24217,NULL,NULL,'court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24228,24224,NULL,0,NULL,0,24217,NULL,NULL,'civil claim',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24229,24224,NULL,0,NULL,0,24217,NULL,NULL,'ECHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24239,24223,NULL,0,NULL,0,24074,NULL,NULL,'Responsible party',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24240,24239,NULL,0,NULL,0,24217,NULL,NULL,'OSJI',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24241,24239,NULL,0,NULL,0,24217,NULL,NULL,'State',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24242,24239,NULL,0,NULL,0,24217,NULL,NULL,'ECHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24243,24223,NULL,0,NULL,0,24074,NULL,NULL,'Files',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24244,24243,NULL,0,NULL,0,24217,NULL,NULL,'Research',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24245,24243,NULL,0,NULL,0,24217,NULL,NULL,'CaseLaw',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24246,24243,NULL,0,NULL,0,24217,NULL,NULL,'EDR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24247,24243,NULL,0,NULL,0,24217,NULL,NULL,'Exhibit',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24248,24223,NULL,0,NULL,0,24074,NULL,NULL,'Case Folders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:39:08',0,1,NULL,NULL,0),(24259,24223,NULL,0,NULL,0,24074,NULL,NULL,'Case statuses',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24260,24259,NULL,0,NULL,0,24217,NULL,NULL,'Active',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24261,24259,NULL,0,NULL,0,24217,NULL,NULL,'Closed',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24262,24259,NULL,0,NULL,0,24217,NULL,NULL,'Archived',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24263,24259,NULL,0,NULL,0,24217,NULL,NULL,'Withdrawn',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24264,24259,NULL,0,NULL,0,24217,NULL,NULL,'Under consideration',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24265,24223,NULL,0,NULL,0,24074,NULL,NULL,'Office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:39:59',0,1,NULL,NULL,0),(24266,24265,NULL,0,NULL,0,24484,NULL,NULL,'CAT',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-03 13:03:58',0,1,NULL,NULL,0),(24267,24265,NULL,0,NULL,0,24484,NULL,NULL,'ECD',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',256,'2014-02-03 13:06:40',0,1,NULL,NULL,0),(24268,24265,NULL,0,NULL,0,24484,NULL,NULL,'FOIE',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',265,'2014-02-21 21:22:59',0,1,NULL,NULL,0),(24269,24265,NULL,0,NULL,0,24484,NULL,NULL,'ICJ',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-01-22 11:52:49',0,1,NULL,NULL,0),(24270,24265,NULL,0,NULL,0,24484,NULL,NULL,'LRC',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-01-22 11:52:34',0,1,NULL,NULL,0),(24271,24265,NULL,0,NULL,0,24484,NULL,NULL,'MIG',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-01-22 11:52:15',0,1,NULL,NULL,0),(24272,24265,NULL,0,NULL,0,24484,NULL,NULL,'NCJ',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-01-22 11:51:54',0,1,NULL,NULL,0),(24273,24265,NULL,0,NULL,0,24484,NULL,NULL,'NSC',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-01-22 11:51:33',0,1,NULL,NULL,0),(24274,24265,NULL,0,NULL,0,24484,NULL,NULL,'Default',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',240,'2014-02-06 18:19:48',0,1,NULL,NULL,0),(24275,23940,NULL,0,NULL,0,24074,NULL,NULL,'Fields',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24276,24275,NULL,0,NULL,0,24074,NULL,NULL,'yes/no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24277,24276,NULL,0,NULL,0,24217,NULL,NULL,'yes',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24278,24276,NULL,0,NULL,0,24217,NULL,NULL,'no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24279,24275,NULL,0,NULL,0,24074,NULL,NULL,'Gender',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24280,24279,NULL,0,NULL,0,24217,NULL,NULL,'male',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24281,24279,NULL,0,NULL,0,24217,NULL,NULL,'female',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24282,24275,NULL,0,NULL,0,24074,NULL,NULL,'checkbox',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24283,24282,NULL,0,NULL,0,24217,NULL,NULL,'yes',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24284,24282,NULL,0,NULL,0,24217,NULL,NULL,'no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24285,24275,NULL,0,NULL,0,24074,NULL,NULL,'types of letters',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24286,24285,NULL,0,NULL,0,24217,NULL,NULL,'response',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,240,'2014-02-28 14:49:21',1),(24287,24285,NULL,0,NULL,0,24217,NULL,NULL,'decision',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24288,24285,NULL,0,NULL,0,24217,NULL,NULL,'communication',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24289,24285,NULL,0,NULL,0,24217,NULL,NULL,'notification',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24290,24285,NULL,0,NULL,0,24217,NULL,NULL,'presentation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24291,24285,NULL,0,NULL,0,24217,NULL,NULL,'according to the examination check',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,240,'2014-02-28 14:48:58',1),(24292,24285,NULL,0,NULL,0,24217,NULL,NULL,'complaint',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24293,24285,NULL,0,NULL,0,24217,NULL,NULL,'check initiation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24294,24285,NULL,0,NULL,0,24217,NULL,NULL,'petition',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24295,24285,NULL,0,NULL,0,24217,NULL,NULL,'appeal',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24296,24285,NULL,0,NULL,0,24217,NULL,NULL,'claim',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24297,24285,NULL,0,NULL,0,24217,NULL,NULL,'informative letter',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24298,24285,NULL,0,NULL,0,24217,NULL,NULL,'violation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,240,'2014-02-28 14:49:16',1),(24299,24285,NULL,0,NULL,0,24217,NULL,NULL,'complaint of the defendant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,240,'2014-02-28 14:49:06',1),(24300,24275,NULL,0,NULL,0,24074,NULL,NULL,'Author',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24301,24300,NULL,0,NULL,0,24217,NULL,NULL,'Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24302,24300,NULL,0,NULL,0,24217,NULL,NULL,'Applicant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24303,24300,NULL,0,NULL,0,24217,NULL,NULL,'Government',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24304,24275,NULL,0,NULL,0,24074,NULL,NULL,'Languages',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24305,24304,NULL,0,NULL,0,24217,NULL,NULL,'Eng',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24306,24304,NULL,0,NULL,0,24217,NULL,NULL,'Rus',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24307,24304,NULL,0,NULL,0,24217,NULL,NULL,'Uzb',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24308,24275,NULL,0,NULL,0,24074,NULL,NULL,'Country',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24309,24308,NULL,0,NULL,0,24217,NULL,NULL,'Kyrgyzstan',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24310,24308,NULL,0,NULL,0,24217,NULL,NULL,'Italy',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24311,24308,NULL,0,NULL,0,24217,NULL,NULL,'Macedonia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24312,24308,NULL,0,NULL,0,24217,NULL,NULL,'Germany',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24313,24308,NULL,0,NULL,0,24217,NULL,NULL,'Russia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24314,24308,NULL,0,NULL,0,24217,NULL,NULL,'Turkey',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24315,24308,NULL,0,NULL,0,24217,NULL,NULL,'Romania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24316,24308,NULL,0,NULL,0,24217,NULL,NULL,'Poland',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24317,24308,NULL,0,NULL,0,24217,NULL,NULL,'Czech Republic',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24318,24308,NULL,0,NULL,0,24217,NULL,NULL,'Israel',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24319,24308,NULL,0,NULL,0,24217,NULL,NULL,'Kenya',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24320,24308,NULL,0,NULL,0,24217,NULL,NULL,'Kazakhstan',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24321,24308,NULL,0,NULL,0,24217,NULL,NULL,'Slovenia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24322,24308,NULL,0,NULL,0,24217,NULL,NULL,'Bulgaria',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24323,24308,NULL,0,NULL,0,24217,NULL,NULL,'Gambia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24324,24308,NULL,0,NULL,0,24217,NULL,NULL,'Switzerland',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24325,24308,NULL,0,NULL,0,24217,NULL,NULL,'Netherlands',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24326,24308,NULL,0,NULL,0,24217,NULL,NULL,'Dominican Republic',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24327,24308,NULL,0,NULL,0,24217,NULL,NULL,'Angola',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24328,24308,NULL,0,NULL,0,24217,NULL,NULL,'Equatorial Guinea',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24329,24308,NULL,0,NULL,0,24217,NULL,NULL,'Bosnia and Herzegovina',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24330,24308,NULL,0,NULL,0,24217,NULL,NULL,'Denmark',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24331,24308,NULL,0,NULL,0,24217,NULL,NULL,'Lithuania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24332,24308,NULL,0,NULL,0,24217,NULL,NULL,'Côte d\'Ivoire',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24333,24308,NULL,0,NULL,0,24217,NULL,NULL,'Chile',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24334,24308,NULL,0,NULL,0,24217,NULL,NULL,'Hungary',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24335,24308,NULL,0,NULL,0,24217,NULL,NULL,'Mauritania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24336,24308,NULL,0,NULL,0,24217,NULL,NULL,'Cameroon',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24337,24308,NULL,0,NULL,0,24217,NULL,NULL,'Botswana',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24338,24308,NULL,0,NULL,0,24217,NULL,NULL,'Rwanda',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24339,24308,NULL,0,NULL,0,24217,NULL,NULL,'Costa Rica',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24340,24275,NULL,0,NULL,0,24074,NULL,NULL,'Position',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24341,24340,NULL,0,NULL,0,24217,NULL,NULL,'Administrative Associate ',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24342,24340,NULL,0,NULL,0,24217,NULL,NULL,'Associate Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24343,24340,NULL,0,NULL,0,24217,NULL,NULL,'Communications Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24344,24340,NULL,0,NULL,0,24217,NULL,NULL,'Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24345,24340,NULL,0,NULL,0,24217,NULL,NULL,'Director of Administration',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24346,24340,NULL,0,NULL,0,24217,NULL,NULL,'Director of Programs',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24347,24340,NULL,0,NULL,0,24217,NULL,NULL,'Executive Assistant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24348,24340,NULL,0,NULL,0,24217,NULL,NULL,'Executive Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24349,24340,NULL,0,NULL,0,24217,NULL,NULL,'Intern',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24350,24340,NULL,0,NULL,0,24217,NULL,NULL,'KRT Monitor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24351,24340,NULL,0,NULL,0,24217,NULL,NULL,'Lawyer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24352,24340,NULL,0,NULL,0,24217,NULL,NULL,'Legal Intern',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24353,24340,NULL,0,NULL,0,24217,NULL,NULL,'Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24354,24340,NULL,0,NULL,0,24217,NULL,NULL,'Litigation Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24355,24340,NULL,0,NULL,0,24217,NULL,NULL,'Litigation Fellow',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24356,24340,NULL,0,NULL,0,24217,NULL,NULL,'Policy Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24357,24340,NULL,0,NULL,0,24217,NULL,NULL,'Program Assistant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24358,24340,NULL,0,NULL,0,24217,NULL,NULL,'Program Associate',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24359,24340,NULL,0,NULL,0,24217,NULL,NULL,'Program Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24360,24340,NULL,0,NULL,0,24217,NULL,NULL,'Program Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24361,24340,NULL,0,NULL,0,24217,NULL,NULL,'Project Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24362,24340,NULL,0,NULL,0,24217,NULL,NULL,'Project Manager',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24363,24340,NULL,0,NULL,0,24217,NULL,NULL,'Resident Fellow',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24364,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24365,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Advocacy Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24366,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Advocacy Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24367,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Attorney',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24368,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Legal Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24369,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24370,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24371,24340,NULL,0,NULL,0,24217,NULL,NULL,'Senior Project Manager',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24372,24340,NULL,0,NULL,0,24217,NULL,NULL,'Temporary Program Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24373,24275,NULL,0,NULL,0,24074,NULL,NULL,'Location',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24374,24373,NULL,0,NULL,0,24217,NULL,NULL,'Abuja',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24375,24373,NULL,0,NULL,0,24217,NULL,NULL,'Amsterdam',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24376,24373,NULL,0,NULL,0,24217,NULL,NULL,'Bishkek',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24377,24373,NULL,0,NULL,0,24217,NULL,NULL,'Brussels',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24378,24373,NULL,0,NULL,0,24217,NULL,NULL,'Budapest',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24379,24373,NULL,0,NULL,0,24217,NULL,NULL,'Cambodia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24380,24373,NULL,0,NULL,0,24217,NULL,NULL,'Geneva',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24381,24373,NULL,0,NULL,0,24217,NULL,NULL,'London',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24382,24373,NULL,0,NULL,0,24217,NULL,NULL,'Madrid',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24383,24373,NULL,0,NULL,0,24217,NULL,NULL,'Mexico City',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24384,24373,NULL,0,NULL,0,24217,NULL,NULL,'New York',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24385,24373,NULL,0,NULL,0,24217,NULL,NULL,'Paris',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24386,24373,NULL,0,NULL,0,24217,NULL,NULL,'Santo Domingo',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24387,24373,NULL,0,NULL,0,24217,NULL,NULL,'The Hague',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24388,24373,NULL,0,NULL,0,24217,NULL,NULL,'Tirana',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24389,24373,NULL,0,NULL,0,24217,NULL,NULL,'Washington',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24390,24275,NULL,0,NULL,0,24074,NULL,NULL,'Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24391,24390,NULL,0,NULL,0,24217,NULL,NULL,'ECHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24392,24390,NULL,0,NULL,0,24217,NULL,NULL,'ACHPR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24393,24390,NULL,0,NULL,0,24217,NULL,NULL,'UNHRC',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24394,24390,NULL,0,NULL,0,24217,NULL,NULL,'IACHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24395,24390,NULL,0,NULL,0,24217,NULL,NULL,'CAT',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24396,24390,NULL,0,NULL,0,24217,NULL,NULL,'UNCAT',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24397,24390,NULL,0,NULL,0,24217,NULL,NULL,'ECOWAS',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24398,24390,NULL,0,NULL,0,24217,NULL,NULL,'Domestic Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24399,24275,NULL,0,NULL,0,24074,NULL,NULL,'Tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24400,24399,NULL,0,NULL,0,24217,NULL,NULL,'Citizenship',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24401,24399,NULL,0,NULL,0,24217,NULL,NULL,'Discrimination',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24402,24399,NULL,0,NULL,0,24217,NULL,NULL,'Family Unification',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24403,24399,NULL,0,NULL,0,24217,NULL,NULL,'Torture',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24404,24399,NULL,0,NULL,0,24217,NULL,NULL,'Rendition',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24405,24399,NULL,0,NULL,0,24217,NULL,NULL,'Statelessness',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24406,24399,NULL,0,NULL,0,24217,NULL,NULL,'Natural resources',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24407,24399,NULL,0,NULL,0,24217,NULL,NULL,'Corruption',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24408,24399,NULL,0,NULL,0,24217,NULL,NULL,'Spoliation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24409,24399,NULL,0,NULL,0,24217,NULL,NULL,'Unjust enrichment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24410,24399,NULL,0,NULL,0,24217,NULL,NULL,'Money laundering',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24411,24399,NULL,0,NULL,0,24217,NULL,NULL,'Roma',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24412,24399,NULL,0,NULL,0,24217,NULL,NULL,'Inhuman treatment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24413,24399,NULL,0,NULL,0,24217,NULL,NULL,'Right to information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24414,24399,NULL,0,NULL,0,24217,NULL,NULL,'Right to truth',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24415,24399,NULL,0,NULL,0,24217,NULL,NULL,'Access to information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24416,24399,NULL,0,NULL,0,24217,NULL,NULL,'Education',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24417,24399,NULL,0,NULL,0,24217,NULL,NULL,'Ethnic profiling',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24418,24399,NULL,0,NULL,0,24217,NULL,NULL,'Database',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24419,24399,NULL,0,NULL,0,24217,NULL,NULL,'Freedom of expression',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24420,24399,NULL,0,NULL,0,24217,NULL,NULL,'Freedom of information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24421,24399,NULL,0,NULL,0,24217,NULL,NULL,'Central Asia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24422,24399,NULL,0,NULL,0,24217,NULL,NULL,'War Crime',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24423,24399,NULL,0,NULL,0,24217,NULL,NULL,'Investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24424,24399,NULL,0,NULL,0,24217,NULL,NULL,'Interrogation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24425,24399,NULL,0,NULL,0,24217,NULL,NULL,'Ineffective investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24426,24399,NULL,0,NULL,0,24217,NULL,NULL,'Police custody',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24427,24399,NULL,0,NULL,0,24217,NULL,NULL,'PTD',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24428,24399,NULL,0,NULL,0,24217,NULL,NULL,'Pretrial Detention',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24429,24399,NULL,0,NULL,0,24217,NULL,NULL,'Impunity',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24430,24399,NULL,0,NULL,0,24217,NULL,NULL,'Nationality',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24431,24399,NULL,0,NULL,0,24217,NULL,NULL,'Public watchdog',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24432,24399,NULL,0,NULL,0,24217,NULL,NULL,'NGO',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24433,24399,NULL,0,NULL,0,24217,NULL,NULL,'Ill-treatment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24434,24399,NULL,0,NULL,0,24217,NULL,NULL,'Journalist',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24435,24399,NULL,0,NULL,0,24217,NULL,NULL,'Defamation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24436,24399,NULL,0,NULL,0,24217,NULL,NULL,'Right to life',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24437,24399,NULL,0,NULL,0,24217,NULL,NULL,'Death in custody',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24438,24399,NULL,0,NULL,0,24217,NULL,NULL,'Press freedom',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24439,24399,NULL,0,NULL,0,24217,NULL,NULL,'Racial profiling',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24440,24399,NULL,0,NULL,0,24217,NULL,NULL,'Fair trial',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24441,23940,NULL,0,NULL,0,24217,NULL,NULL,'Alex Evdokimov',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24442,23940,NULL,0,NULL,0,24217,NULL,NULL,'Oleg Burlaca',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24443,24072,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24444,24072,NULL,0,NULL,0,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24445,24444,NULL,0,NULL,0,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24446,24444,NULL,0,NULL,0,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24447,24444,NULL,0,NULL,0,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24448,24444,NULL,0,NULL,0,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24449,24072,NULL,0,NULL,0,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24450,24072,NULL,0,NULL,0,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24451,24072,NULL,0,NULL,0,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',240,'2014-02-06 13:19:22',0,1,NULL,NULL,0),(24452,24072,NULL,0,NULL,0,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24453,24072,NULL,0,NULL,0,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24454,24453,NULL,0,NULL,0,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24455,24453,NULL,0,NULL,0,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24456,24073,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24457,24073,NULL,0,NULL,0,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24458,24457,NULL,0,NULL,0,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24459,24457,NULL,0,NULL,0,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24460,24457,NULL,0,NULL,0,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24461,24457,NULL,0,NULL,0,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24462,24073,NULL,0,NULL,0,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24463,24073,NULL,0,NULL,0,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24464,24073,NULL,0,NULL,0,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24465,24073,NULL,0,NULL,0,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24466,24073,NULL,0,NULL,0,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24467,24466,NULL,0,NULL,0,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24468,24466,NULL,0,NULL,0,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24469,24078,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24470,24078,NULL,0,NULL,0,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24471,24470,NULL,0,NULL,0,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24472,24470,NULL,0,NULL,0,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24473,24470,NULL,0,NULL,0,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24474,24470,NULL,0,NULL,0,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24475,24078,NULL,0,NULL,0,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24476,24078,NULL,0,NULL,0,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24477,24078,NULL,0,NULL,0,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24478,24078,NULL,0,NULL,0,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24479,24078,NULL,0,NULL,0,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24480,24479,NULL,0,NULL,0,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24481,24479,NULL,0,NULL,0,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24484,24042,NULL,0,NULL,0,24044,NULL,NULL,'office',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',240,'2014-02-28 15:07:12',0,1,NULL,NULL,0),(24485,24484,NULL,0,NULL,0,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24486,24484,NULL,0,NULL,0,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24487,24484,NULL,0,NULL,0,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24488,24484,NULL,0,NULL,0,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24489,24484,NULL,0,NULL,0,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24490,24484,NULL,0,NULL,0,24043,NULL,NULL,'managers',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-21 11:23:09',1,'2014-01-21 11:55:01',0,1,NULL,NULL,0),(24493,NULL,257,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,1,'2014-01-21 14:39:43',NULL,NULL,0,1,NULL,NULL,0),(24494,24493,257,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-21 14:39:43',NULL,NULL,0,1,NULL,NULL,0),(24503,23940,NULL,0,NULL,0,24074,NULL,NULL,'Colors',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 09:57:06',NULL,NULL,0,1,NULL,NULL,0),(24504,24503,NULL,0,NULL,0,24217,NULL,NULL,'gray',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-gray\"}',1,1,'2014-01-22 09:58:10',NULL,NULL,0,1,NULL,NULL,0),(24505,24503,NULL,0,NULL,0,24217,NULL,NULL,'blue',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-blue\"}',1,1,'2014-01-22 09:58:43',NULL,NULL,0,1,NULL,NULL,0),(24506,24503,NULL,0,NULL,0,24217,NULL,NULL,'green',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-green\"}',1,1,'2014-01-22 09:59:32',NULL,NULL,0,1,NULL,NULL,0),(24507,24503,NULL,0,NULL,0,24217,NULL,NULL,'orange',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-orange\"}',1,1,'2014-01-22 10:00:04',NULL,NULL,0,1,NULL,NULL,0),(24508,24503,NULL,0,NULL,0,24217,NULL,NULL,'teal',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-teal\"}',1,1,'2014-01-22 10:00:04',1,'2014-01-22 10:51:15',0,1,NULL,NULL,0),(24509,24503,NULL,0,NULL,0,24217,NULL,NULL,'purple',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-purple\"}',1,1,'2014-01-22 10:01:03',NULL,NULL,0,1,NULL,NULL,0),(24510,24503,NULL,0,NULL,0,24217,NULL,NULL,'red',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-red\"}',1,1,'2014-01-22 10:01:38',NULL,NULL,0,1,NULL,NULL,0),(24511,24503,NULL,0,NULL,0,24217,NULL,NULL,'yellow',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-yellow\"}',1,1,'2014-01-22 10:01:38',1,'2014-01-22 10:35:17',0,1,NULL,NULL,0),(24512,24503,NULL,0,NULL,0,24217,NULL,NULL,'olive',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-olive\"}',1,1,'2014-01-22 10:03:14',NULL,NULL,0,1,NULL,NULL,0),(24513,24503,NULL,0,NULL,0,24217,NULL,NULL,'steel',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-steel\"}',1,1,'2014-01-22 10:03:55',NULL,NULL,0,1,NULL,NULL,0),(24514,24072,NULL,0,NULL,0,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:07:16',1,'2014-01-22 10:08:21',0,1,NULL,NULL,0),(24515,24078,NULL,0,NULL,0,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:08:12',NULL,NULL,0,1,NULL,NULL,0),(24516,24073,NULL,0,NULL,0,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:08:55',NULL,NULL,0,1,NULL,NULL,0),(24517,24484,NULL,0,NULL,0,24043,NULL,NULL,'security_group',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 11:41:29',1,'2014-01-22 11:49:53',0,1,NULL,NULL,0),(24523,24074,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 14:10:27',NULL,NULL,0,1,NULL,NULL,0),(24562,NULL,262,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-01-29 15:50:39',NULL,NULL,0,NULL,NULL,NULL,0),(24563,24562,262,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-01-29 15:50:39',NULL,NULL,0,NULL,NULL,NULL,0),(24569,1,NULL,0,5,0,24075,NULL,NULL,'New OpenDocument Presentation.odp','2014-01-31 00:00:00','2014-01-31 00:00:00',36620,NULL,NULL,1,240,'2014-01-31 11:14:10',240,'2014-01-31 11:14:26',0,240,NULL,NULL,0),(24576,1,NULL,0,5,0,24075,NULL,NULL,'New OpenDocument Text.odt','2014-01-31 00:00:00','2014-01-31 00:00:00',4879,NULL,NULL,1,240,'2014-01-31 11:20:09',240,'2014-01-31 11:21:15',0,240,NULL,NULL,0),(24614,24248,NULL,0,NULL,0,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'null',1,240,'2014-01-31 13:04:48',240,'2014-02-28 15:10:31',0,240,NULL,NULL,0),(24615,24248,NULL,0,NULL,0,24074,NULL,NULL,'1-Summaries',NULL,NULL,NULL,NULL,'null',1,240,'2014-01-31 13:05:00',NULL,NULL,0,240,240,'2014-02-28 15:09:01',1),(24616,24248,NULL,0,NULL,0,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'null',1,240,'2014-01-31 13:05:13',240,'2014-02-28 15:10:40',0,240,NULL,NULL,0),(24647,24248,NULL,0,NULL,0,24074,NULL,NULL,'3-Meetings',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:12:53',NULL,NULL,0,240,240,'2014-02-28 15:09:24',1),(24648,24248,NULL,0,NULL,0,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:07',240,'2014-02-28 15:10:49',0,240,NULL,NULL,0),(24649,24248,NULL,0,NULL,0,24074,NULL,NULL,'5-Filings',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:24',240,'2014-02-28 15:08:30',0,240,240,'2014-02-28 15:08:44',1),(24650,24248,NULL,0,NULL,0,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:35',240,'2014-02-28 15:11:01',0,240,NULL,NULL,0),(24651,24248,NULL,0,NULL,0,24074,NULL,NULL,'7-Advocacy',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:47',NULL,NULL,0,240,240,'2014-02-28 15:09:36',1),(24652,24248,NULL,0,NULL,0,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:58',240,'2014-02-28 15:11:15',0,240,NULL,NULL,0),(24653,24248,NULL,0,NULL,0,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:14:11',240,'2014-02-28 15:11:24',0,240,NULL,NULL,0),(24712,1,NULL,0,5,0,24075,NULL,NULL,'testingwebdav.txt.sb-2bad71fc-tNKl86','2014-02-05 00:00:00','2014-02-05 00:00:00',32,NULL,NULL,1,240,'2014-02-05 15:05:26',240,'2014-02-05 15:06:49',0,240,NULL,NULL,0),(24715,1,NULL,0,5,0,24075,NULL,NULL,'testingwebdav.txt','2014-02-06 00:00:00','2014-02-06 00:00:00',48,NULL,NULL,1,240,'2014-02-06 09:44:21',240,'2014-02-06 09:44:21',0,240,NULL,NULL,0),(24720,23490,NULL,0,NULL,0,24074,NULL,NULL,'Documents',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-06 12:10:22',NULL,NULL,0,240,NULL,NULL,0),(24721,24720,NULL,0,5,0,24075,NULL,NULL,'Building a Local Economy- adapt fr S. Witt.doc','2014-02-06 12:10:33','2014-02-06 12:10:33',27648,NULL,NULL,1,240,'2014-02-06 12:10:33',240,'2014-02-06 12:10:33',0,240,NULL,NULL,0),(24722,24720,NULL,0,5,0,24075,NULL,NULL,'Building-a-Local-Economy-adapt-fr-S.-Witt.doc','2014-02-12 00:00:00','2014-02-12 00:00:00',25088,NULL,NULL,1,240,'2014-02-12 21:59:33',240,'2014-02-12 21:59:33',0,240,NULL,NULL,0),(24748,24720,NULL,0,5,0,24075,NULL,NULL,'Untitled 1.odt','2014-02-06 00:00:00','2014-02-06 00:00:00',8209,NULL,NULL,1,240,'2014-02-06 22:39:33',240,'2014-02-06 22:39:33',0,240,240,'2014-02-28 15:17:21',1),(24775,24720,NULL,0,NULL,0,24195,NULL,NULL,'some action here','2014-02-07 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-02-06 22:42:59',240,'2014-02-06 22:48:30',0,240,NULL,NULL,0),(24779,23490,NULL,0,NULL,0,24079,NULL,NULL,'Spigunov case',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-07 09:42:00',240,'2014-02-07 09:42:46',0,240,NULL,NULL,0),(24780,24779,NULL,0,NULL,0,24074,NULL,NULL,'0-Incoming',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24781,24779,NULL,0,NULL,0,24074,NULL,NULL,'1-Summaries',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24782,24779,NULL,0,NULL,0,24074,NULL,NULL,'2-Correspondence',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24783,24779,NULL,0,NULL,0,24074,NULL,NULL,'3-Meetings',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24784,24779,NULL,0,NULL,0,24074,NULL,NULL,'4-Filings',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24785,24779,NULL,0,NULL,0,24074,NULL,NULL,'5-OSJI Filings',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24786,24779,NULL,0,NULL,0,24074,NULL,NULL,'6-Evidence',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24787,24779,NULL,0,NULL,0,24074,NULL,NULL,'7-Advocacy',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24788,24779,NULL,0,NULL,0,24074,NULL,NULL,'8-Research',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24789,24779,NULL,0,NULL,0,24074,NULL,NULL,'9-Administrative',NULL,NULL,NULL,NULL,'[]',1,240,'2014-02-07 09:42:00',NULL,NULL,0,240,NULL,NULL,0),(24800,1,NULL,0,5,0,24075,NULL,NULL,'test document.doc','2014-02-10 18:00:37','2014-02-10 18:00:37',26112,NULL,NULL,1,256,'2014-02-10 18:00:37',256,'2014-02-10 18:00:37',0,256,NULL,NULL,0),(24802,1,NULL,0,5,0,24075,NULL,NULL,'test excel.xls','2014-02-10 18:54:26','2014-02-10 18:54:26',16896,NULL,NULL,1,256,'2014-02-10 18:54:26',256,'2014-02-10 18:54:26',0,256,NULL,NULL,0),(24822,24052,NULL,0,NULL,0,24044,NULL,NULL,'Comment',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:14:04',NULL,NULL,0,1,NULL,NULL,0),(24823,24822,NULL,0,NULL,0,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:15:03',NULL,NULL,0,1,NULL,NULL,0),(24833,23490,NULL,0,5,0,24075,NULL,NULL,'casebox-faceting.jpeg','2014-02-12 21:50:58','2014-02-12 21:50:58',361268,NULL,NULL,1,240,'2014-02-12 21:50:58',240,'2014-02-12 21:50:58',0,240,NULL,NULL,0),(24834,1,NULL,2,NULL,0,24822,NULL,NULL,'why it\'s so slow',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-12 22:12:05',NULL,NULL,0,240,NULL,NULL,0),(24848,NULL,265,1,1,2,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-02-21 21:21:46',NULL,NULL,0,NULL,NULL,NULL,0),(24849,24848,265,1,1,3,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-02-21 21:21:46',NULL,NULL,0,NULL,NULL,NULL,0),(24904,24720,NULL,0,5,0,24075,NULL,NULL,'1848-2010-Turchenyak-et-al-v-Belarus_en.doc','2014-02-25 21:06:40','2014-02-25 21:06:40',111104,NULL,NULL,1,240,'2014-02-25 21:06:40',240,'2014-02-25 21:06:40',0,240,NULL,NULL,0),(24927,1,NULL,0,NULL,0,24195,NULL,NULL,'TestActA0','2014-02-26 00:00:00',NULL,NULL,NULL,'null',1,256,'2014-02-27 04:55:04',NULL,NULL,0,256,NULL,NULL,0),(24929,1,NULL,0,NULL,0,24195,NULL,NULL,'test','2014-02-27 00:00:00',NULL,NULL,NULL,'null',1,256,'2014-02-27 06:57:54',NULL,NULL,0,256,NULL,NULL,0),(25072,1,NULL,0,NULL,0,24195,NULL,NULL,'Test Action','2014-02-27 00:00:00',NULL,NULL,NULL,'null',1,265,'2014-02-27 13:47:08',NULL,NULL,0,265,NULL,NULL,0),(25073,1,NULL,0,NULL,0,24195,NULL,NULL,'Test action','2014-02-28 00:00:00',NULL,NULL,NULL,'null',1,265,'2014-02-27 14:03:11',NULL,NULL,0,265,NULL,NULL,0),(25078,24720,NULL,0,NULL,0,24072,NULL,NULL,'Scan papers','2014-02-28 00:00:00','2014-03-07 00:00:00',NULL,NULL,'null',1,240,'2014-02-28 15:18:38',NULL,NULL,0,240,NULL,NULL,0);

/*Table structure for table `tree_acl` */

DROP TABLE IF EXISTS `tree_acl`;

CREATE TABLE `tree_acl` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` bigint(20) unsigned NOT NULL,
  `user_group_id` int(10) unsigned NOT NULL,
  `allow` int(16) NOT NULL DEFAULT '0',
  `deny` int(16) NOT NULL DEFAULT '0',
  `cid` int(10) unsigned DEFAULT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(10) unsigned DEFAULT NULL,
  `udate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `FK_tree_acl__node_id__user_group_id` (`node_id`,`user_group_id`),
  KEY `FK_tree_acl__user_group_id` (`user_group_id`),
  CONSTRAINT `FK_tree_acl__node_id` FOREIGN KEY (`node_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tree_acl__user_group_id` FOREIGN KEY (`user_group_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl` */

insert  into `tree_acl`(`id`,`node_id`,`user_group_id`,`allow`,`deny`,`cid`,`cdate`,`uid`,`udate`) values (1,1,233,4095,0,NULL,'2013-03-20 13:56:21',NULL,NULL),(2,1,234,4095,0,NULL,'2013-03-21 12:02:02',239,'2013-06-24 01:21:49'),(3,23730,1,4095,0,NULL,'2013-04-22 13:27:18',NULL,NULL),(12,23741,240,4095,0,NULL,'2013-05-26 08:18:39',NULL,NULL),(118,23811,242,4095,0,250,'2013-06-04 13:07:21',250,'2013-06-04 13:07:41'),(123,1,252,4095,0,1,'2013-09-23 19:06:12',1,'2013-09-23 19:06:15'),(127,1,235,4095,0,256,'2013-09-24 19:41:42',256,'2013-09-24 19:41:48');

/*Table structure for table `tree_acl_security_sets` */

DROP TABLE IF EXISTS `tree_acl_security_sets`;

CREATE TABLE `tree_acl_security_sets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `set` varchar(9999) NOT NULL,
  `md5` varchar(32) NOT NULL DEFAULT '-',
  `updated` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_tree_acl_security_sets__md5` (`md5`),
  KEY `IDX_tree_acl_security_sets__set` (`set`(100))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl_security_sets` */

insert  into `tree_acl_security_sets`(`id`,`set`,`md5`,`updated`) values (1,'1','c4ca4238a0b923820dcc509a6f75849b',1),(2,'23730','f87b7d1f666a0a1d86568485a520bffa',1),(3,'23741','5580d031cccd368c6cd90bc0999c207e',1),(4,'23811','3f6e4c5abb908a8ac7ca70a2a8fad69c',1);

/*Table structure for table `tree_acl_security_sets_result` */

DROP TABLE IF EXISTS `tree_acl_security_sets_result`;

CREATE TABLE `tree_acl_security_sets_result` (
  `security_set_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `bit0` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=deny, 1=allow',
  `bit1` tinyint(1) NOT NULL DEFAULT '0',
  `bit2` tinyint(1) DEFAULT '0',
  `bit3` tinyint(1) DEFAULT '0',
  `bit4` tinyint(1) DEFAULT '0',
  `bit5` tinyint(1) DEFAULT '0',
  `bit6` tinyint(1) DEFAULT '0',
  `bit7` tinyint(1) DEFAULT '0',
  `bit8` tinyint(1) DEFAULT '0',
  `bit9` tinyint(1) DEFAULT '0',
  `bit10` tinyint(1) DEFAULT '0',
  `bit11` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`security_set_id`,`user_id`),
  KEY `IDX_tree_acl_security_sets_result__user_id` (`user_id`),
  CONSTRAINT `FK_tree_acl_security_sets_result__security_set_id` FOREIGN KEY (`security_set_id`) REFERENCES `tree_acl_security_sets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tree_acl_security_sets_result__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl_security_sets_result` */

/*Table structure for table `tree_info` */

DROP TABLE IF EXISTS `tree_info`;

CREATE TABLE `tree_info` (
  `id` bigint(20) unsigned NOT NULL,
  `pids` text NOT NULL COMMENT 'comma separated parent ids',
  `path` text COMMENT 'slash separated parent names',
  `case_id` bigint(20) unsigned DEFAULT NULL,
  `acl_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'count of security rules associated with this node in the tree',
  `security_set_id` bigint(20) unsigned DEFAULT NULL,
  `updated` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tree_info__case_id` (`case_id`),
  KEY `tree_info__security_set_id` (`security_set_id`),
  KEY `tree_info__updated` (`updated`),
  KEY `tree_info_pids` (`pids`(200)),
  CONSTRAINT `tree_info__case_id` FOREIGN KEY (`case_id`) REFERENCES `tree` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tree_info__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_info__security_set_id` FOREIGN KEY (`security_set_id`) REFERENCES `tree_acl_security_sets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_info` */

insert  into `tree_info`(`id`,`pids`,`path`,`case_id`,`acl_count`,`security_set_id`,`updated`) values (1,'1','',NULL,4,1,1),(23432,'23432','',NULL,0,NULL,1),(23433,'23432,23433','/',NULL,0,NULL,1),(23434,'23434','',NULL,0,NULL,1),(23435,'23434,23435','/',NULL,0,NULL,1),(23436,'23434,23436','/',NULL,0,NULL,1),(23437,'23434,23436,23437','/[Tasks]/',NULL,0,NULL,1),(23438,'23434,23436,23438','/[Tasks]/',NULL,0,NULL,1),(23439,'23434,23436,23439','/[Tasks]/',NULL,0,NULL,1),(23440,'23434,23440','/',NULL,0,NULL,1),(23441,'23434,23440,23441','/[Messages]/',NULL,0,NULL,1),(23442,'23434,23440,23442','/[Messages]/',NULL,0,NULL,1),(23443,'23434,23443','/',NULL,0,NULL,1),(23444,'23444','',NULL,0,NULL,1),(23445,'23444,23445','/',NULL,0,NULL,1),(23446,'23444,23446','/',NULL,0,NULL,1),(23447,'23444,23446,23447','/[Tasks]/',NULL,0,NULL,1),(23448,'23444,23446,23448','/[Tasks]/',NULL,0,NULL,1),(23449,'23444,23446,23449','/[Tasks]/',NULL,0,NULL,1),(23450,'23444,23450','/',NULL,0,NULL,1),(23451,'23444,23450,23451','/[Messages]/',NULL,0,NULL,1),(23452,'23444,23450,23452','/[Messages]/',NULL,0,NULL,1),(23490,'1,23490','/',NULL,0,1,1),(23491,'1,23490,23492,23497,23491','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23492,'1,23490,23492','/Demo/',23492,0,1,1),(23493,'1,23490,23492,23493','/Demo/0709-Akmatov/',23492,0,1,1),(23494,'1,23490,23492,23494','/Demo/0709-Akmatov/',23492,0,1,1),(23495,'1,23490,23492,23495','/Demo/0709-Akmatov/',23492,0,1,1),(23496,'1,23490,23492,23496','/Demo/0709-Akmatov/',23492,0,1,1),(23497,'1,23490,23492,23497','/Demo/0709-Akmatov/',23492,0,1,1),(23498,'1,23490,23492,23498','/Demo/0709-Akmatov/',23492,0,1,1),(23499,'1,23490,23492,23499','/Demo/0709-Akmatov/',23492,0,1,1),(23500,'1,23490,23492,23500','/Demo/0709-Akmatov/',23492,0,1,1),(23501,'1,23490,23492,23501','/Demo/0709-Akmatov/',23492,0,1,1),(23502,'1,23490,23492,23502','/Demo/0709-Akmatov/',23492,0,1,1),(23508,'1,23490,23492,23493,23508','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23509,'1,23490,23492,23493,23509','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23510,'1,23490,23492,23493,23510','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23511,'1,23490,23492,23493,23511','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23512,'1,23490,23492,23493,23512','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23513,'1,23490,23492,23493,23513','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23514,'1,23490,23492,23493,23514','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23515,'1,23490,23492,23493,23515','/Demo/0709-Akmatov/1-Summaries/',23492,0,1,1),(23516,'1,23490,23492,23494,23516','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23517,'1,23490,23492,23494,23517','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23518,'1,23490,23492,23494,23518','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23519,'1,23490,23492,23494,23519','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23520,'1,23490,23492,23494,23520','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23521,'1,23490,23492,23494,23521','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23522,'1,23490,23492,23494,23522','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23523,'1,23490,23492,23494,23523','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23524,'1,23490,23492,23494,23524','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23525,'1,23490,23492,23494,23525','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23526,'1,23490,23492,23494,23526','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23527,'1,23490,23492,23494,23527','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23528,'1,23490,23492,23494,23528','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23529,'1,23490,23492,23494,23529','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23530,'1,23490,23492,23494,23530','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23531,'1,23490,23492,23494,23531','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23532,'1,23490,23492,23494,23532','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23533,'1,23490,23492,23494,23533','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23534,'1,23490,23492,23494,23534','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23535,'1,23490,23492,23494,23535','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23536,'1,23490,23492,23494,23536','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23537,'1,23490,23492,23494,23537','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23538,'1,23490,23492,23494,23538','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23539,'1,23490,23492,23494,23539','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23540,'1,23490,23492,23494,23540','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23541,'1,23490,23492,23494,23541','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23542,'1,23490,23492,23494,23542','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23543,'1,23490,23492,23494,23543','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23544,'1,23490,23492,23494,23544','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23545,'1,23490,23492,23494,23545','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23546,'1,23490,23492,23494,23546','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23547,'1,23490,23492,23494,23547','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23548,'1,23490,23492,23494,23548','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23549,'1,23490,23492,23494,23549','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23550,'1,23490,23492,23494,23550','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23551,'1,23490,23492,23494,23551','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23552,'1,23490,23492,23494,23552','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23553,'1,23490,23492,23494,23553','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23554,'1,23490,23492,23494,23554','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23555,'1,23490,23492,23494,23555','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23556,'1,23490,23492,23494,23556','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23557,'1,23490,23492,23494,23557','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23558,'1,23490,23492,23494,23558','/Demo/0709-Akmatov/2-Correspondence/',23492,0,1,1),(23559,'1,23490,23492,23497,23559','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23560,'1,23490,23492,23497,23560','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23561,'1,23490,23492,23497,23561','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23562,'1,23490,23492,23497,23562','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23563,'1,23490,23492,23497,23563','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23564,'1,23490,23492,23497,23564','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23565,'1,23490,23492,23497,23565','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23566,'1,23490,23492,23497,23566','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23567,'1,23490,23492,23497,23567','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23568,'1,23490,23492,23497,23568','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23569,'1,23490,23492,23497,23569','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23570,'1,23490,23492,23497,23570','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23571,'1,23490,23492,23497,23571','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23572,'1,23490,23492,23497,23572','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23583,'1,23490,23492,23497,23572,23583','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23590,'1,23490,23492,23497,23572,23590','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23591,'1,23490,23492,23497,23572,23591','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23592,'1,23490,23492,23497,23572,23592','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23593,'1,23490,23492,23497,23572,23593','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23594,'1,23490,23492,23497,23572,23594','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23596,'1,23490,23492,23497,23572,23596','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23600,'1,23490,23492,23497,23572,23600','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23613,'1,23490,23492,23496,23613','/Demo/0709-Akmatov/4-Filings/',23492,0,1,1),(23614,'1,23490,23492,23496,23614','/Demo/0709-Akmatov/4-Filings/',23492,0,1,1),(23615,'1,23490,23492,23497,23615','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23616,'1,23490,23492,23497,23616','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23617,'1,23490,23492,23497,23615,23617','/Demo/0709-Akmatov/5-OSJI Filings/Application to ECHR/',23492,0,1,1),(23618,'1,23490,23492,23497,23618','/Demo/0709-Akmatov/5-OSJI Filings/',23492,0,1,1),(23620,'1,23490,23492,23501,23620','/Demo/0709-Akmatov/0-Incoming/',23492,0,1,1),(23621,'1,23490,23492,23501,23621','/Demo/0709-Akmatov/0-Incoming/',23492,0,1,1),(23623,'1,23490,23492,23497,23572,23623','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23624,'1,23490,23492,23624','/Demo/0709-Akmatov/',23492,0,1,1),(23634,'1,23634','/',NULL,0,1,1),(23636,'1,23636','/',NULL,0,1,1),(23653,'1,23490,23492,23497,23572,23653','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23656,'1,23490,23492,23497,23572,23656','/Demo/0709-Akmatov/5-OSJI Filings/Drafts/',23492,0,1,1),(23681,'1,23681','/',NULL,0,1,1),(23684,'1,23490,23492,23684','/Demo/0709-Akmatov/',23492,0,1,1),(23730,'23730','',NULL,1,2,1),(23731,'23730,23731','/',NULL,0,2,1),(23732,'23730,23732','/',NULL,0,2,1),(23733,'23733','',NULL,0,NULL,1),(23734,'23733,23734','/',NULL,0,NULL,1),(23735,'23735','',NULL,0,NULL,1),(23736,'23735,23736','/',NULL,0,NULL,1),(23741,'23741','',NULL,1,3,1),(23742,'23741,23742','/',NULL,0,3,1),(23744,'23744','',NULL,0,NULL,1),(23745,'23744,23745','/',NULL,0,NULL,1),(23748,'23733,23734,23748','/[MyDocuments]/',NULL,0,NULL,1),(23807,'23807','',NULL,0,NULL,1),(23808,'23807,23808','/',NULL,0,NULL,1),(23809,'23809','',NULL,0,NULL,1),(23810,'23809,23810','/',NULL,0,NULL,1),(23811,'23807,23808,23811','/[MyDocuments]/',NULL,1,4,1),(23815,'23733,23734,23815','/[MyDocuments]/',23815,0,NULL,1),(23816,'23733,23734,23815,23816','/[MyDocuments]/12/',23815,0,NULL,1),(23817,'23733,23734,23815,23817','/[MyDocuments]/12/',23815,0,NULL,1),(23818,'23733,23734,23815,23818','/[MyDocuments]/12/',23815,0,NULL,1),(23819,'23733,23734,23815,23819','/[MyDocuments]/12/',23815,0,NULL,1),(23820,'23733,23734,23815,23820','/[MyDocuments]/12/',23815,0,NULL,1),(23821,'23733,23734,23815,23821','/[MyDocuments]/12/',23815,0,NULL,1),(23822,'23733,23734,23815,23822','/[MyDocuments]/12/',23815,0,NULL,1),(23823,'23733,23734,23815,23823','/[MyDocuments]/12/',23815,0,NULL,1),(23824,'23733,23734,23815,23824','/[MyDocuments]/12/',23815,0,NULL,1),(23825,'23733,23734,23815,23825','/[MyDocuments]/12/',23815,0,NULL,1),(23827,'1,23827','/',NULL,0,1,1),(23883,'23883','',NULL,0,NULL,1),(23884,'23883,23884','/',NULL,0,NULL,1),(23885,'23885','',NULL,0,NULL,1),(23886,'23885,23886','/',NULL,0,NULL,1),(23940,'1,23940','/',NULL,0,1,1),(24042,'1,24042','/',NULL,0,1,1),(24043,'1,24042,24052,24043','/Templates/System/',NULL,0,1,1),(24044,'1,24042,24052,24044','/Templates/System/',NULL,0,1,1),(24052,'1,24042,24052','/Templates/',NULL,0,1,1),(24053,'1,24042,24052,24053','/Templates/System/',NULL,0,1,1),(24054,'1,24042,24052,24053,24054','/Templates/System/User/',NULL,0,1,1),(24055,'1,24042,24052,24053,24055','/Templates/System/User/',NULL,0,1,1),(24056,'1,24042,24052,24053,24056','/Templates/System/User/',NULL,0,1,1),(24057,'1,24042,24052,24053,24057','/Templates/System/User/',NULL,0,1,1),(24058,'1,24042,24052,24053,24058','/Templates/System/User/',NULL,0,1,1),(24059,'1,24042,24052,24053,24059','/Templates/System/User/',NULL,0,1,1),(24060,'1,24042,24052,24053,24060','/Templates/System/User/',NULL,0,1,1),(24061,'1,24042,24052,24053,24061','/Templates/System/User/',NULL,0,1,1),(24062,'1,24042,24052,24053,24062','/Templates/System/User/',NULL,0,1,1),(24063,'1,24042,24052,24053,24063','/Templates/System/User/',NULL,0,1,1),(24064,'1,24042,24052,24053,24064','/Templates/System/User/',NULL,0,1,1),(24065,'1,24042,24052,24053,24065','/Templates/System/User/',NULL,0,1,1),(24066,'1,24042,24052,24053,24066','/Templates/System/User/',NULL,0,1,1),(24067,'1,24042,24052,24067','/Templates/System/',NULL,0,1,1),(24068,'1,24042,24052,24067,24068','/Templates/System/email/',NULL,0,1,1),(24069,'1,24042,24052,24067,24069','/Templates/System/email/',NULL,0,1,1),(24070,'1,24042,24052,24067,24070','/Templates/System/email/',NULL,0,1,1),(24071,'1,24042,24052,24067,24071','/Templates/System/email/',NULL,0,1,1),(24072,'1,24042,24052,24072','/Templates/System/',NULL,0,1,1),(24073,'1,24042,24052,24073','/Templates/System/',NULL,0,1,1),(24074,'1,24042,24052,24074','/Templates/System/',NULL,0,1,1),(24075,'1,24042,24052,24075','/Templates/System/',NULL,0,1,1),(24076,'1,24042,24052,24075,24076','/Templates/System/file_template/',NULL,0,1,1),(24077,'1,24042,24052,24075,24077','/Templates/System/file_template/',NULL,0,1,1),(24078,'1,24042,24052,24078','/Templates/System/',NULL,0,1,1),(24079,'1,24042,24079','/Templates/',NULL,0,1,1),(24080,'1,24042,24079,24080','/Templates/case_template/',NULL,0,1,1),(24081,'1,24042,24079,24081','/Templates/case_template/',NULL,0,1,1),(24082,'1,24042,24079,24082','/Templates/case_template/',NULL,0,1,1),(24083,'1,24042,24079,24083','/Templates/case_template/',NULL,0,1,1),(24084,'1,24042,24079,24084','/Templates/case_template/',NULL,0,1,1),(24085,'1,24042,24079,24085','/Templates/case_template/',NULL,0,1,1),(24086,'1,24042,24079,24086','/Templates/case_template/',NULL,0,1,1),(24087,'1,24042,24079,24087','/Templates/case_template/',NULL,0,1,1),(24088,'1,24042,24079,24088','/Templates/case_template/',NULL,0,1,1),(24089,'1,24042,24079,24089','/Templates/case_template/',NULL,0,1,1),(24090,'1,24042,24079,24090','/Templates/case_template/',NULL,0,1,1),(24091,'1,24042,24079,24091','/Templates/case_template/',NULL,0,1,1),(24195,'1,24042,24195','/Templates/',NULL,0,1,1),(24196,'1,24042,24195,24196','/Templates/Action/',NULL,0,1,1),(24197,'1,24042,24195,24197','/Templates/Action/',NULL,0,1,1),(24198,'1,24042,24195,24198','/Templates/Action/',NULL,0,1,1),(24199,'1,24042,24195,24199','/Templates/Action/',NULL,0,1,1),(24200,'1,24042,24195,24200','/Templates/Action/',NULL,0,1,1),(24201,'1,24042,24052,24043,24201','/Templates/System/Fields template/',NULL,0,1,1),(24202,'1,24042,24052,24043,24202','/Templates/System/Fields template/',NULL,0,1,1),(24203,'1,24042,24052,24043,24203','/Templates/System/Fields template/',NULL,0,1,1),(24204,'1,24042,24052,24043,24204','/Templates/System/Fields template/',NULL,0,1,1),(24205,'1,24042,24052,24043,24205','/Templates/System/Fields template/',NULL,0,1,1),(24206,'1,24042,24052,24043,24206','/Templates/System/Fields template/',NULL,0,1,1),(24207,'1,24042,24052,24043,24207','/Templates/System/Fields template/',NULL,0,1,1),(24208,'1,24042,24052,24044,24208','/Templates/System/Templates template/',NULL,0,1,1),(24209,'1,24042,24052,24044,24209','/Templates/System/Templates template/',NULL,0,1,1),(24210,'1,24042,24052,24044,24210','/Templates/System/Templates template/',NULL,0,1,1),(24211,'1,24042,24052,24044,24211','/Templates/System/Templates template/',NULL,0,1,1),(24212,'1,24042,24052,24044,24212','/Templates/System/Templates template/',NULL,0,1,1),(24213,'1,24042,24052,24044,24213','/Templates/System/Templates template/',NULL,0,1,1),(24214,'1,24042,24052,24044,24214','/Templates/System/Templates template/',NULL,0,1,1),(24215,'1,24042,24052,24044,24215','/Templates/System/Templates template/',NULL,0,1,1),(24216,'1,24042,24052,24044,24216','/Templates/System/Templates template/',NULL,0,1,1),(24217,'1,24042,24052,24217','/Templates/System/',NULL,0,1,1),(24218,'1,24042,24052,24217,24218','/Templates/System/Thesauri Item/',NULL,0,1,1),(24219,'1,24042,24052,24217,24219','/Templates/System/Thesauri Item/',NULL,0,1,1),(24220,'1,24042,24052,24217,24220','/Templates/System/Thesauri Item/',NULL,0,1,1),(24221,'1,24042,24052,24217,24221','/Templates/System/Thesauri Item/',NULL,0,1,1),(24222,'1,24042,24052,24217,24222','/Templates/System/Thesauri Item/',NULL,0,1,1),(24223,'1,23940,24223','/Thesauri/',NULL,0,1,1),(24224,'1,23940,24223,24224','/Thesauri/System/',NULL,0,1,1),(24225,'1,23940,24223,24224,24225','/Thesauri/System/Phases/',NULL,0,1,1),(24226,'1,23940,24223,24224,24226','/Thesauri/System/Phases/',NULL,0,1,1),(24227,'1,23940,24223,24224,24227','/Thesauri/System/Phases/',NULL,0,1,1),(24228,'1,23940,24223,24224,24228','/Thesauri/System/Phases/',NULL,0,1,1),(24229,'1,23940,24223,24224,24229','/Thesauri/System/Phases/',NULL,0,1,1),(24239,'1,23940,24223,24239','/Thesauri/System/',NULL,0,1,1),(24240,'1,23940,24223,24239,24240','/Thesauri/System/Responsible party/',NULL,0,1,1),(24241,'1,23940,24223,24239,24241','/Thesauri/System/Responsible party/',NULL,0,1,1),(24242,'1,23940,24223,24239,24242','/Thesauri/System/Responsible party/',NULL,0,1,1),(24243,'1,23940,24223,24243','/Thesauri/System/',NULL,0,1,1),(24244,'1,23940,24223,24243,24244','/Thesauri/System/Files/',NULL,0,1,1),(24245,'1,23940,24223,24243,24245','/Thesauri/System/Files/',NULL,0,1,1),(24246,'1,23940,24223,24243,24246','/Thesauri/System/Files/',NULL,0,1,1),(24247,'1,23940,24223,24243,24247','/Thesauri/System/Files/',NULL,0,1,1),(24248,'1,23940,24223,24248','/Thesauri/System/',NULL,0,1,1),(24259,'1,23940,24223,24259','/Thesauri/System/',NULL,0,1,1),(24260,'1,23940,24223,24259,24260','/Thesauri/System/Case statuses/',NULL,0,1,1),(24261,'1,23940,24223,24259,24261','/Thesauri/System/Case statuses/',NULL,0,1,1),(24262,'1,23940,24223,24259,24262','/Thesauri/System/Case statuses/',NULL,0,1,1),(24263,'1,23940,24223,24259,24263','/Thesauri/System/Case statuses/',NULL,0,1,1),(24264,'1,23940,24223,24259,24264','/Thesauri/System/Case statuses/',NULL,0,1,1),(24265,'1,23940,24223,24265','/Thesauri/System/',NULL,0,1,1),(24266,'1,23940,24223,24265,24266','/Thesauri/System/Office/',NULL,0,1,1),(24267,'1,23940,24223,24265,24267','/Thesauri/System/Office/',NULL,0,1,1),(24268,'1,23940,24223,24265,24268','/Thesauri/System/Office/',NULL,0,1,1),(24269,'1,23940,24223,24265,24269','/Thesauri/System/Office/',NULL,0,1,1),(24270,'1,23940,24223,24265,24270','/Thesauri/System/Office/',NULL,0,1,1),(24271,'1,23940,24223,24265,24271','/Thesauri/System/Office/',NULL,0,1,1),(24272,'1,23940,24223,24265,24272','/Thesauri/System/Office/',NULL,0,1,1),(24273,'1,23940,24223,24265,24273','/Thesauri/System/Office/',NULL,0,1,1),(24274,'1,23940,24223,24265,24274','/Thesauri/System/Office/',NULL,0,1,1),(24275,'1,23940,24275','/Thesauri/',NULL,0,1,1),(24276,'1,23940,24275,24276','/Thesauri/Fields/',NULL,0,1,1),(24277,'1,23940,24275,24276,24277','/Thesauri/Fields/yes/no/',NULL,0,1,1),(24278,'1,23940,24275,24276,24278','/Thesauri/Fields/yes/no/',NULL,0,1,1),(24279,'1,23940,24275,24279','/Thesauri/Fields/',NULL,0,1,1),(24280,'1,23940,24275,24279,24280','/Thesauri/Fields/Gender/',NULL,0,1,1),(24281,'1,23940,24275,24279,24281','/Thesauri/Fields/Gender/',NULL,0,1,1),(24282,'1,23940,24275,24282','/Thesauri/Fields/',NULL,0,1,1),(24283,'1,23940,24275,24282,24283','/Thesauri/Fields/checkbox/',NULL,0,1,1),(24284,'1,23940,24275,24282,24284','/Thesauri/Fields/checkbox/',NULL,0,1,1),(24285,'1,23940,24275,24285','/Thesauri/Fields/',NULL,0,1,1),(24286,'1,23940,24275,24285,24286','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24287,'1,23940,24275,24285,24287','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24288,'1,23940,24275,24285,24288','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24289,'1,23940,24275,24285,24289','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24290,'1,23940,24275,24285,24290','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24291,'1,23940,24275,24285,24291','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24292,'1,23940,24275,24285,24292','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24293,'1,23940,24275,24285,24293','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24294,'1,23940,24275,24285,24294','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24295,'1,23940,24275,24285,24295','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24296,'1,23940,24275,24285,24296','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24297,'1,23940,24275,24285,24297','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24298,'1,23940,24275,24285,24298','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24299,'1,23940,24275,24285,24299','/Thesauri/Fields/types of letters/',NULL,0,1,1),(24300,'1,23940,24275,24300','/Thesauri/Fields/',NULL,0,1,1),(24301,'1,23940,24275,24300,24301','/Thesauri/Fields/Author/',NULL,0,1,1),(24302,'1,23940,24275,24300,24302','/Thesauri/Fields/Author/',NULL,0,1,1),(24303,'1,23940,24275,24300,24303','/Thesauri/Fields/Author/',NULL,0,1,1),(24304,'1,23940,24275,24304','/Thesauri/Fields/',NULL,0,1,1),(24305,'1,23940,24275,24304,24305','/Thesauri/Fields/Languages/',NULL,0,1,1),(24306,'1,23940,24275,24304,24306','/Thesauri/Fields/Languages/',NULL,0,1,1),(24307,'1,23940,24275,24304,24307','/Thesauri/Fields/Languages/',NULL,0,1,1),(24308,'1,23940,24275,24308','/Thesauri/Fields/',NULL,0,1,1),(24309,'1,23940,24275,24308,24309','/Thesauri/Fields/Country/',NULL,0,1,1),(24310,'1,23940,24275,24308,24310','/Thesauri/Fields/Country/',NULL,0,1,1),(24311,'1,23940,24275,24308,24311','/Thesauri/Fields/Country/',NULL,0,1,1),(24312,'1,23940,24275,24308,24312','/Thesauri/Fields/Country/',NULL,0,1,1),(24313,'1,23940,24275,24308,24313','/Thesauri/Fields/Country/',NULL,0,1,1),(24314,'1,23940,24275,24308,24314','/Thesauri/Fields/Country/',NULL,0,1,1),(24315,'1,23940,24275,24308,24315','/Thesauri/Fields/Country/',NULL,0,1,1),(24316,'1,23940,24275,24308,24316','/Thesauri/Fields/Country/',NULL,0,1,1),(24317,'1,23940,24275,24308,24317','/Thesauri/Fields/Country/',NULL,0,1,1),(24318,'1,23940,24275,24308,24318','/Thesauri/Fields/Country/',NULL,0,1,1),(24319,'1,23940,24275,24308,24319','/Thesauri/Fields/Country/',NULL,0,1,1),(24320,'1,23940,24275,24308,24320','/Thesauri/Fields/Country/',NULL,0,1,1),(24321,'1,23940,24275,24308,24321','/Thesauri/Fields/Country/',NULL,0,1,1),(24322,'1,23940,24275,24308,24322','/Thesauri/Fields/Country/',NULL,0,1,1),(24323,'1,23940,24275,24308,24323','/Thesauri/Fields/Country/',NULL,0,1,1),(24324,'1,23940,24275,24308,24324','/Thesauri/Fields/Country/',NULL,0,1,1),(24325,'1,23940,24275,24308,24325','/Thesauri/Fields/Country/',NULL,0,1,1),(24326,'1,23940,24275,24308,24326','/Thesauri/Fields/Country/',NULL,0,1,1),(24327,'1,23940,24275,24308,24327','/Thesauri/Fields/Country/',NULL,0,1,1),(24328,'1,23940,24275,24308,24328','/Thesauri/Fields/Country/',NULL,0,1,1),(24329,'1,23940,24275,24308,24329','/Thesauri/Fields/Country/',NULL,0,1,1),(24330,'1,23940,24275,24308,24330','/Thesauri/Fields/Country/',NULL,0,1,1),(24331,'1,23940,24275,24308,24331','/Thesauri/Fields/Country/',NULL,0,1,1),(24332,'1,23940,24275,24308,24332','/Thesauri/Fields/Country/',NULL,0,1,1),(24333,'1,23940,24275,24308,24333','/Thesauri/Fields/Country/',NULL,0,1,1),(24334,'1,23940,24275,24308,24334','/Thesauri/Fields/Country/',NULL,0,1,1),(24335,'1,23940,24275,24308,24335','/Thesauri/Fields/Country/',NULL,0,1,1),(24336,'1,23940,24275,24308,24336','/Thesauri/Fields/Country/',NULL,0,1,1),(24337,'1,23940,24275,24308,24337','/Thesauri/Fields/Country/',NULL,0,1,1),(24338,'1,23940,24275,24308,24338','/Thesauri/Fields/Country/',NULL,0,1,1),(24339,'1,23940,24275,24308,24339','/Thesauri/Fields/Country/',NULL,0,1,1),(24340,'1,23940,24275,24340','/Thesauri/Fields/',NULL,0,1,1),(24341,'1,23940,24275,24340,24341','/Thesauri/Fields/Position/',NULL,0,1,1),(24342,'1,23940,24275,24340,24342','/Thesauri/Fields/Position/',NULL,0,1,1),(24343,'1,23940,24275,24340,24343','/Thesauri/Fields/Position/',NULL,0,1,1),(24344,'1,23940,24275,24340,24344','/Thesauri/Fields/Position/',NULL,0,1,1),(24345,'1,23940,24275,24340,24345','/Thesauri/Fields/Position/',NULL,0,1,1),(24346,'1,23940,24275,24340,24346','/Thesauri/Fields/Position/',NULL,0,1,1),(24347,'1,23940,24275,24340,24347','/Thesauri/Fields/Position/',NULL,0,1,1),(24348,'1,23940,24275,24340,24348','/Thesauri/Fields/Position/',NULL,0,1,1),(24349,'1,23940,24275,24340,24349','/Thesauri/Fields/Position/',NULL,0,1,1),(24350,'1,23940,24275,24340,24350','/Thesauri/Fields/Position/',NULL,0,1,1),(24351,'1,23940,24275,24340,24351','/Thesauri/Fields/Position/',NULL,0,1,1),(24352,'1,23940,24275,24340,24352','/Thesauri/Fields/Position/',NULL,0,1,1),(24353,'1,23940,24275,24340,24353','/Thesauri/Fields/Position/',NULL,0,1,1),(24354,'1,23940,24275,24340,24354','/Thesauri/Fields/Position/',NULL,0,1,1),(24355,'1,23940,24275,24340,24355','/Thesauri/Fields/Position/',NULL,0,1,1),(24356,'1,23940,24275,24340,24356','/Thesauri/Fields/Position/',NULL,0,1,1),(24357,'1,23940,24275,24340,24357','/Thesauri/Fields/Position/',NULL,0,1,1),(24358,'1,23940,24275,24340,24358','/Thesauri/Fields/Position/',NULL,0,1,1),(24359,'1,23940,24275,24340,24359','/Thesauri/Fields/Position/',NULL,0,1,1),(24360,'1,23940,24275,24340,24360','/Thesauri/Fields/Position/',NULL,0,1,1),(24361,'1,23940,24275,24340,24361','/Thesauri/Fields/Position/',NULL,0,1,1),(24362,'1,23940,24275,24340,24362','/Thesauri/Fields/Position/',NULL,0,1,1),(24363,'1,23940,24275,24340,24363','/Thesauri/Fields/Position/',NULL,0,1,1),(24364,'1,23940,24275,24340,24364','/Thesauri/Fields/Position/',NULL,0,1,1),(24365,'1,23940,24275,24340,24365','/Thesauri/Fields/Position/',NULL,0,1,1),(24366,'1,23940,24275,24340,24366','/Thesauri/Fields/Position/',NULL,0,1,1),(24367,'1,23940,24275,24340,24367','/Thesauri/Fields/Position/',NULL,0,1,1),(24368,'1,23940,24275,24340,24368','/Thesauri/Fields/Position/',NULL,0,1,1),(24369,'1,23940,24275,24340,24369','/Thesauri/Fields/Position/',NULL,0,1,1),(24370,'1,23940,24275,24340,24370','/Thesauri/Fields/Position/',NULL,0,1,1),(24371,'1,23940,24275,24340,24371','/Thesauri/Fields/Position/',NULL,0,1,1),(24372,'1,23940,24275,24340,24372','/Thesauri/Fields/Position/',NULL,0,1,1),(24373,'1,23940,24275,24373','/Thesauri/Fields/',NULL,0,1,1),(24374,'1,23940,24275,24373,24374','/Thesauri/Fields/Location/',NULL,0,1,1),(24375,'1,23940,24275,24373,24375','/Thesauri/Fields/Location/',NULL,0,1,1),(24376,'1,23940,24275,24373,24376','/Thesauri/Fields/Location/',NULL,0,1,1),(24377,'1,23940,24275,24373,24377','/Thesauri/Fields/Location/',NULL,0,1,1),(24378,'1,23940,24275,24373,24378','/Thesauri/Fields/Location/',NULL,0,1,1),(24379,'1,23940,24275,24373,24379','/Thesauri/Fields/Location/',NULL,0,1,1),(24380,'1,23940,24275,24373,24380','/Thesauri/Fields/Location/',NULL,0,1,1),(24381,'1,23940,24275,24373,24381','/Thesauri/Fields/Location/',NULL,0,1,1),(24382,'1,23940,24275,24373,24382','/Thesauri/Fields/Location/',NULL,0,1,1),(24383,'1,23940,24275,24373,24383','/Thesauri/Fields/Location/',NULL,0,1,1),(24384,'1,23940,24275,24373,24384','/Thesauri/Fields/Location/',NULL,0,1,1),(24385,'1,23940,24275,24373,24385','/Thesauri/Fields/Location/',NULL,0,1,1),(24386,'1,23940,24275,24373,24386','/Thesauri/Fields/Location/',NULL,0,1,1),(24387,'1,23940,24275,24373,24387','/Thesauri/Fields/Location/',NULL,0,1,1),(24388,'1,23940,24275,24373,24388','/Thesauri/Fields/Location/',NULL,0,1,1),(24389,'1,23940,24275,24373,24389','/Thesauri/Fields/Location/',NULL,0,1,1),(24390,'1,23940,24275,24390','/Thesauri/Fields/',NULL,0,1,1),(24391,'1,23940,24275,24390,24391','/Thesauri/Fields/Court/',NULL,0,1,1),(24392,'1,23940,24275,24390,24392','/Thesauri/Fields/Court/',NULL,0,1,1),(24393,'1,23940,24275,24390,24393','/Thesauri/Fields/Court/',NULL,0,1,1),(24394,'1,23940,24275,24390,24394','/Thesauri/Fields/Court/',NULL,0,1,1),(24395,'1,23940,24275,24390,24395','/Thesauri/Fields/Court/',NULL,0,1,1),(24396,'1,23940,24275,24390,24396','/Thesauri/Fields/Court/',NULL,0,1,1),(24397,'1,23940,24275,24390,24397','/Thesauri/Fields/Court/',NULL,0,1,1),(24398,'1,23940,24275,24390,24398','/Thesauri/Fields/Court/',NULL,0,1,1),(24399,'1,23940,24275,24399','/Thesauri/Fields/',NULL,0,1,1),(24400,'1,23940,24275,24399,24400','/Thesauri/Fields/Tags/',NULL,0,1,1),(24401,'1,23940,24275,24399,24401','/Thesauri/Fields/Tags/',NULL,0,1,1),(24402,'1,23940,24275,24399,24402','/Thesauri/Fields/Tags/',NULL,0,1,1),(24403,'1,23940,24275,24399,24403','/Thesauri/Fields/Tags/',NULL,0,1,1),(24404,'1,23940,24275,24399,24404','/Thesauri/Fields/Tags/',NULL,0,1,1),(24405,'1,23940,24275,24399,24405','/Thesauri/Fields/Tags/',NULL,0,1,1),(24406,'1,23940,24275,24399,24406','/Thesauri/Fields/Tags/',NULL,0,1,1),(24407,'1,23940,24275,24399,24407','/Thesauri/Fields/Tags/',NULL,0,1,1),(24408,'1,23940,24275,24399,24408','/Thesauri/Fields/Tags/',NULL,0,1,1),(24409,'1,23940,24275,24399,24409','/Thesauri/Fields/Tags/',NULL,0,1,1),(24410,'1,23940,24275,24399,24410','/Thesauri/Fields/Tags/',NULL,0,1,1),(24411,'1,23940,24275,24399,24411','/Thesauri/Fields/Tags/',NULL,0,1,1),(24412,'1,23940,24275,24399,24412','/Thesauri/Fields/Tags/',NULL,0,1,1),(24413,'1,23940,24275,24399,24413','/Thesauri/Fields/Tags/',NULL,0,1,1),(24414,'1,23940,24275,24399,24414','/Thesauri/Fields/Tags/',NULL,0,1,1),(24415,'1,23940,24275,24399,24415','/Thesauri/Fields/Tags/',NULL,0,1,1),(24416,'1,23940,24275,24399,24416','/Thesauri/Fields/Tags/',NULL,0,1,1),(24417,'1,23940,24275,24399,24417','/Thesauri/Fields/Tags/',NULL,0,1,1),(24418,'1,23940,24275,24399,24418','/Thesauri/Fields/Tags/',NULL,0,1,1),(24419,'1,23940,24275,24399,24419','/Thesauri/Fields/Tags/',NULL,0,1,1),(24420,'1,23940,24275,24399,24420','/Thesauri/Fields/Tags/',NULL,0,1,1),(24421,'1,23940,24275,24399,24421','/Thesauri/Fields/Tags/',NULL,0,1,1),(24422,'1,23940,24275,24399,24422','/Thesauri/Fields/Tags/',NULL,0,1,1),(24423,'1,23940,24275,24399,24423','/Thesauri/Fields/Tags/',NULL,0,1,1),(24424,'1,23940,24275,24399,24424','/Thesauri/Fields/Tags/',NULL,0,1,1),(24425,'1,23940,24275,24399,24425','/Thesauri/Fields/Tags/',NULL,0,1,1),(24426,'1,23940,24275,24399,24426','/Thesauri/Fields/Tags/',NULL,0,1,1),(24427,'1,23940,24275,24399,24427','/Thesauri/Fields/Tags/',NULL,0,1,1),(24428,'1,23940,24275,24399,24428','/Thesauri/Fields/Tags/',NULL,0,1,1),(24429,'1,23940,24275,24399,24429','/Thesauri/Fields/Tags/',NULL,0,1,1),(24430,'1,23940,24275,24399,24430','/Thesauri/Fields/Tags/',NULL,0,1,1),(24431,'1,23940,24275,24399,24431','/Thesauri/Fields/Tags/',NULL,0,1,1),(24432,'1,23940,24275,24399,24432','/Thesauri/Fields/Tags/',NULL,0,1,1),(24433,'1,23940,24275,24399,24433','/Thesauri/Fields/Tags/',NULL,0,1,1),(24434,'1,23940,24275,24399,24434','/Thesauri/Fields/Tags/',NULL,0,1,1),(24435,'1,23940,24275,24399,24435','/Thesauri/Fields/Tags/',NULL,0,1,1),(24436,'1,23940,24275,24399,24436','/Thesauri/Fields/Tags/',NULL,0,1,1),(24437,'1,23940,24275,24399,24437','/Thesauri/Fields/Tags/',NULL,0,1,1),(24438,'1,23940,24275,24399,24438','/Thesauri/Fields/Tags/',NULL,0,1,1),(24439,'1,23940,24275,24399,24439','/Thesauri/Fields/Tags/',NULL,0,1,1),(24440,'1,23940,24275,24399,24440','/Thesauri/Fields/Tags/',NULL,0,1,1),(24441,'1,23940,24441','/Thesauri/',NULL,0,1,1),(24442,'1,23940,24442','/Thesauri/',NULL,0,1,1),(24443,'1,24042,24052,24072,24443','/Templates/System/tasks/',NULL,0,1,1),(24444,'1,24042,24052,24072,24444','/Templates/System/tasks/',NULL,0,1,1),(24445,'1,24042,24052,24072,24444,24445','/Templates/System/tasks/allday/',NULL,0,1,1),(24446,'1,24042,24052,24072,24444,24446','/Templates/System/tasks/allday/',NULL,0,1,1),(24447,'1,24042,24052,24072,24444,24447','/Templates/System/tasks/allday/',NULL,0,1,1),(24448,'1,24042,24052,24072,24444,24448','/Templates/System/tasks/allday/',NULL,0,1,1),(24449,'1,24042,24052,24072,24449','/Templates/System/tasks/',NULL,0,1,1),(24450,'1,24042,24052,24072,24450','/Templates/System/tasks/',NULL,0,1,1),(24451,'1,24042,24052,24072,24451','/Templates/System/tasks/',NULL,0,1,1),(24452,'1,24042,24052,24072,24452','/Templates/System/tasks/',NULL,0,1,1),(24453,'1,24042,24052,24072,24453','/Templates/System/tasks/',NULL,0,1,1),(24454,'1,24042,24052,24072,24453,24454','/Templates/System/tasks/reminders/',NULL,0,1,1),(24455,'1,24042,24052,24072,24453,24455','/Templates/System/tasks/reminders/',NULL,0,1,1),(24456,'1,24042,24052,24073,24456','/Templates/System/event/',NULL,0,1,1),(24457,'1,24042,24052,24073,24457','/Templates/System/event/',NULL,0,1,1),(24458,'1,24042,24052,24073,24457,24458','/Templates/System/event/allday/',NULL,0,1,1),(24459,'1,24042,24052,24073,24457,24459','/Templates/System/event/allday/',NULL,0,1,1),(24460,'1,24042,24052,24073,24457,24460','/Templates/System/event/allday/',NULL,0,1,1),(24461,'1,24042,24052,24073,24457,24461','/Templates/System/event/allday/',NULL,0,1,1),(24462,'1,24042,24052,24073,24462','/Templates/System/event/',NULL,0,1,1),(24463,'1,24042,24052,24073,24463','/Templates/System/event/',NULL,0,1,1),(24464,'1,24042,24052,24073,24464','/Templates/System/event/',NULL,0,1,1),(24465,'1,24042,24052,24073,24465','/Templates/System/event/',NULL,0,1,1),(24466,'1,24042,24052,24073,24466','/Templates/System/event/',NULL,0,1,1),(24467,'1,24042,24052,24073,24466,24467','/Templates/System/event/reminders/',NULL,0,1,1),(24468,'1,24042,24052,24073,24466,24468','/Templates/System/event/reminders/',NULL,0,1,1),(24469,'1,24042,24052,24078,24469','/Templates/System/milestone/',NULL,0,1,1),(24470,'1,24042,24052,24078,24470','/Templates/System/milestone/',NULL,0,1,1),(24471,'1,24042,24052,24078,24470,24471','/Templates/System/milestone/allday/',NULL,0,1,1),(24472,'1,24042,24052,24078,24470,24472','/Templates/System/milestone/allday/',NULL,0,1,1),(24473,'1,24042,24052,24078,24470,24473','/Templates/System/milestone/allday/',NULL,0,1,1),(24474,'1,24042,24052,24078,24470,24474','/Templates/System/milestone/allday/',NULL,0,1,1),(24475,'1,24042,24052,24078,24475','/Templates/System/milestone/',NULL,0,1,1),(24476,'1,24042,24052,24078,24476','/Templates/System/milestone/',NULL,0,1,1),(24477,'1,24042,24052,24078,24477','/Templates/System/milestone/',NULL,0,1,1),(24478,'1,24042,24052,24078,24478','/Templates/System/milestone/',NULL,0,1,1),(24479,'1,24042,24052,24078,24479','/Templates/System/milestone/',NULL,0,1,1),(24480,'1,24042,24052,24078,24479,24480','/Templates/System/milestone/reminders/',NULL,0,1,1),(24481,'1,24042,24052,24078,24479,24481','/Templates/System/milestone/reminders/',NULL,0,1,1),(24484,'1,24042,24484','/Templates/',NULL,0,1,1),(24485,'1,24042,24484,24485','/Templates/office/',NULL,0,1,1),(24486,'1,24042,24484,24486','/Templates/office/',NULL,0,1,1),(24487,'1,24042,24484,24487','/Templates/office/',NULL,0,1,1),(24488,'1,24042,24484,24488','/Templates/office/',NULL,0,1,1),(24489,'1,24042,24484,24489','/Templates/office/',NULL,0,1,1),(24490,'1,24042,24484,24490','/Templates/office/',NULL,0,1,1),(24493,'24493','',NULL,0,NULL,1),(24494,'24493,24494','/',NULL,0,NULL,1),(24503,'1,23940,24503','/Thesauri/',NULL,0,1,1),(24504,'1,23940,24503,24504','/Thesauri/Colors/',NULL,0,1,1),(24505,'1,23940,24503,24505','/Thesauri/Colors/',NULL,0,1,1),(24506,'1,23940,24503,24506','/Thesauri/Colors/',NULL,0,1,1),(24507,'1,23940,24503,24507','/Thesauri/Colors/',NULL,0,1,1),(24508,'1,23940,24503,24508','/Thesauri/Colors/',NULL,0,1,1),(24509,'1,23940,24503,24509','/Thesauri/Colors/',NULL,0,1,1),(24510,'1,23940,24503,24510','/Thesauri/Colors/',NULL,0,1,1),(24511,'1,23940,24503,24511','/Thesauri/Colors/',NULL,0,1,1),(24512,'1,23940,24503,24512','/Thesauri/Colors/',NULL,0,1,1),(24513,'1,23940,24503,24513','/Thesauri/Colors/',NULL,0,1,1),(24514,'1,24042,24052,24072,24514','/Templates/System/tasks/',NULL,0,1,1),(24515,'1,24042,24052,24078,24515','/Templates/System/milestone/',NULL,0,1,1),(24516,'1,24042,24052,24073,24516','/Templates/System/event/',NULL,0,1,1),(24517,'1,24042,24484,24517','/Templates/office/',NULL,0,1,1),(24523,'1,24042,24052,24074,24523','/Templates/System/folder/',NULL,0,1,1),(24562,'24562','',NULL,0,NULL,1),(24563,'24562,24563','/',NULL,0,NULL,1),(24569,'1,24569','/',NULL,0,1,1),(24576,'1,24576','/',NULL,0,1,1),(24614,'1,23940,24223,24248,24614','/Thesauri/System/Case Folders/',NULL,0,1,1),(24615,'1,23940,24223,24248,24615','/Thesauri/System/Case Folders/',NULL,0,1,1),(24616,'1,23940,24223,24248,24616','/Thesauri/System/Case Folders/',NULL,0,1,1),(24647,'1,23940,24223,24248,24647','/Thesauri/System/Case Folders/',NULL,0,1,1),(24648,'1,23940,24223,24248,24648','/Thesauri/System/Case Folders/',NULL,0,1,1),(24649,'1,23940,24223,24248,24649','/Thesauri/System/Case Folders/',NULL,0,1,1),(24650,'1,23940,24223,24248,24650','/Thesauri/System/Case Folders/',NULL,0,1,1),(24651,'1,23940,24223,24248,24651','/Thesauri/System/Case Folders/',NULL,0,1,1),(24652,'1,23940,24223,24248,24652','/Thesauri/System/Case Folders/',NULL,0,1,1),(24653,'1,23940,24223,24248,24653','/Thesauri/System/Case Folders/',NULL,0,1,1),(24712,'1,24712','/',NULL,0,1,1),(24715,'1,24715','/',NULL,0,1,1),(24720,'1,23490,24720','/Demo/',NULL,0,1,1),(24721,'1,23490,24720,24721','/Demo/Documents/',NULL,0,1,1),(24722,'1,23490,24720,24722','/Demo/Documents/',NULL,0,1,1),(24748,'1,23490,24720,24748','/Demo/Documents/',NULL,0,1,1),(24775,'1,23490,24720,24775','/Demo/Documents/',NULL,0,1,1),(24779,'1,23490,24779','/Demo/',24779,0,1,1),(24780,'1,23490,24779,24780','/Demo/Spigunov case/',24779,0,1,1),(24781,'1,23490,24779,24781','/Demo/Spigunov case/',24779,0,1,1),(24782,'1,23490,24779,24782','/Demo/Spigunov case/',24779,0,1,1),(24783,'1,23490,24779,24783','/Demo/Spigunov case/',24779,0,1,1),(24784,'1,23490,24779,24784','/Demo/Spigunov case/',24779,0,1,1),(24785,'1,23490,24779,24785','/Demo/Spigunov case/',24779,0,1,1),(24786,'1,23490,24779,24786','/Demo/Spigunov case/',24779,0,1,1),(24787,'1,23490,24779,24787','/Demo/Spigunov case/',24779,0,1,1),(24788,'1,23490,24779,24788','/Demo/Spigunov case/',24779,0,1,1),(24789,'1,23490,24779,24789','/Demo/Spigunov case/',24779,0,1,1),(24800,'1,24800','/',NULL,0,1,1),(24802,'1,24802','/',NULL,0,1,1),(24822,'1,24042,24052,24822','/Templates/System/',NULL,0,1,1),(24823,'1,24042,24052,24822,24823','/Templates/System/Comment/',NULL,0,1,1),(24833,'1,23490,24833','/Demo/',NULL,0,1,1),(24834,'1,24834','/',NULL,0,1,1),(24848,'24848','',NULL,0,NULL,1),(24849,'24848,24849','/',NULL,0,NULL,1),(24904,'1,23490,24720,24904','/Demo/Documents/',NULL,0,1,1),(24927,'1,24927','/',NULL,0,1,1),(24929,'1,24929','/',NULL,0,1,1),(25072,'1,25072','/',NULL,0,1,1),(25073,'1,25073','/',NULL,0,1,1),(25078,'1,23490,24720,25078','/Demo/Documents/',NULL,0,1,1);

/*Table structure for table `tree_user_config` */

DROP TABLE IF EXISTS `tree_user_config`;

CREATE TABLE `tree_user_config` (
  `id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `cfg` text,
  PRIMARY KEY (`id`,`user_id`),
  KEY `tree_user_config__user_id` (`user_id`),
  CONSTRAINT `tree_user_config__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_user_config__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_user_config` */

/*Table structure for table `users_groups` */

DROP TABLE IF EXISTS `users_groups`;

CREATE TABLE `users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '1 - group, 2 - user',
  `system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1 - is a system group that cannot be deleted from ui',
  `name` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `l1` varchar(150) DEFAULT NULL,
  `l2` varchar(150) DEFAULT NULL,
  `l3` varchar(150) DEFAULT NULL,
  `l4` varchar(150) DEFAULT NULL,
  `sex` char(1) DEFAULT NULL COMMENT 'extracted gender from users data',
  `email` varchar(150) DEFAULT NULL COMMENT 'primary user email',
  `photo` varchar(250) DEFAULT NULL COMMENT 'filename of uploated photo file',
  `password` varchar(255) DEFAULT NULL,
  `password_change` date DEFAULT NULL,
  `recover_hash` varchar(100) DEFAULT NULL,
  `language_id` smallint(6) unsigned NOT NULL DEFAULT '1' COMMENT 'extracted language index from users data',
  `cfg` text,
  `data` mediumtext,
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'should be moved to an auth log table for security enhancement',
  `login_successful` tinyint(1) DEFAULT NULL COMMENT 'should be moved to an auth log table for security enhancement',
  `login_from_ip` varchar(40) DEFAULT NULL COMMENT 'should be moved to an auth log table for security enhancement',
  `last_logout` timestamp NULL DEFAULT NULL COMMENT 'should be moved to an auth log table for security enhancement',
  `last_action_time` timestamp NULL DEFAULT NULL,
  `enabled` tinyint(1) unsigned DEFAULT '1',
  `cid` int(11) unsigned DEFAULT NULL COMMENT 'creator id',
  `cdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'creation timestamp',
  `uid` int(11) unsigned DEFAULT NULL COMMENT 'updater id',
  `udate` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'updated timestamp',
  `did` int(11) unsigned DEFAULT NULL COMMENT 'deleter id',
  `ddate` timestamp NULL DEFAULT NULL,
  `searchField` text COMMENT 'helper field for users quick searching',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_type__name` (`name`,`type`),
  KEY `IDX_recover_hash` (`recover_hash`),
  KEY `FK_users_groups_language` (`language_id`),
  KEY `IDX_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups` */

insert  into `users_groups`(`id`,`type`,`system`,`name`,`first_name`,`last_name`,`l1`,`l2`,`l3`,`l4`,`sex`,`email`,`photo`,`password`,`password_change`,`recover_hash`,`language_id`,`cfg`,`data`,`last_login`,`login_successful`,`login_from_ip`,`last_logout`,`last_action_time`,`enabled`,`cid`,`cdate`,`uid`,`udate`,`did`,`ddate`,`searchField`) values (1,2,0,'root','Administrator','','Administrator','Administrator','Administrator','Administrator','m','oburlaca@gmail.com',NULL,'50775b4f5109fd22c46dabb17f710c17','2014-02-28',NULL,1,'{\"short_date_format\":\"%m\\/%d\\/%Y\",\"long_date_format\":\"%F %j, %Y\",\"country_code\":\"\",\"phone\":\"\",\"timezone\":\"\",\"security\":{\"recovery_email\":true,\"email\":\"oburlaca@gmail.com\"}}','[]','2014-02-17 19:40:52',1,'|109.185.172.18|',NULL,'2014-02-28 07:39:02',1,1,NULL,1,'2013-03-20 12:57:29',NULL,NULL,' root Administrator Administrator Administrator Administrator oburlaca@gmail.com '),(233,1,1,'system','SYSTEM','','SYSTEM','SYSTÈME','СИСТЕМА',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:09',NULL,NULL,' system SYSTEM SYSTÈME СИСТЕМА   '),(234,1,1,'everyone','Everyone','','Everyone','Tous','Все',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:10',NULL,NULL,' everyone Everyone Tous Все   '),(235,1,0,'Administrators','Administrators','','Administrators','Administrateurs','Администраторы',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:10',NULL,NULL,' Administrators Administrators Administrateurs Администраторы   '),(236,1,0,'Managers','Managers','','Managers','Gestionnaires','Менеджеры',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:10',NULL,NULL,' Managers Managers Gestionnaires Менеджеры   '),(238,1,0,'Users','Users','','Users','Users','Пользователи',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:11',NULL,NULL,' Users Users Users Пользователи   '),(240,2,0,'oburlaca','Oleg','Burlaca','Oleg Burlaca','Oleg Burlaca','Oleg Burlaca',NULL,'m','oleg@burlaca.com','240_2.png','46ae99bd879ff123b64793b8ce986137','2013-09-25',NULL,1,'{\"short_date_format\":\"%d\\/%m\\/%Y\",\"long_date_format\":\"%F %j, %Y\",\"canAddUsers\":\"true\",\"canAddGroups\":\"true\",\"country_code\":\"\",\"phone\":\"\",\"timezone\":\"Chisinau\",\"TZ\":\"Europe\\/Chisinau\"}','[]','2014-02-28 14:05:57',1,'|188.240.73.107|',NULL,'2014-02-28 14:33:13',1,232,'2013-05-24 14:05:01',1,'0000-00-00 00:00:00',NULL,NULL,' oburlaca Oleg Burlaca Oleg Burlaca Oleg Burlaca  oleg@burlaca.com '),(242,1,0,'CAT','CAT','','CAT','CAT','CAT','CAT',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:03',NULL,'0000-00-00 00:00:00',NULL,NULL,' CAT CAT CAT CAT CAT  '),(243,1,0,'ECD','ECD','','ECD','ECD','ECD','ECD',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:10',NULL,'0000-00-00 00:00:00',NULL,NULL,' ECD ECD ECD ECD ECD  '),(244,1,0,'FOIE','FOIE','','FOIE','FOIE','FOIE','FOIE',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:20',NULL,'0000-00-00 00:00:00',NULL,NULL,' FOIE FOIE FOIE FOIE FOIE  '),(245,1,0,'ICJ','ICJ','','ICJ','ICJ','ICJ','ICJ',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:28',NULL,'0000-00-00 00:00:00',NULL,NULL,' ICJ ICJ ICJ ICJ ICJ  '),(246,1,0,'LRC','LRC','','LRC','LRC','LRC','LRC',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:38',NULL,'0000-00-00 00:00:00',NULL,NULL,' LRC LRC LRC LRC LRC  '),(247,1,0,'MIG','MIG','','MIG','MIG','MIG','MIG',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:48',NULL,'0000-00-00 00:00:00',NULL,NULL,' MIG MIG MIG MIG MIG  '),(248,1,0,'NCJ','NCJ','','NCJ','NCJ','NCJ','NCJ',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:57',NULL,'0000-00-00 00:00:00',NULL,NULL,' NCJ NCJ NCJ NCJ NCJ  '),(249,1,0,'NSC','NSC','','NSC','NSC','NSC','NSC',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:54:06',NULL,'0000-00-00 00:00:00',NULL,NULL,' NSC NSC NSC NSC NSC  '),(252,1,0,'Pilot',NULL,NULL,'Pilot','Pilot','Pilot','Pilot',NULL,'',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-09-23 18:46:53',NULL,'0000-00-00 00:00:00',NULL,NULL,' Pilot Pilot Pilot Pilot Pilot  ');

/*Table structure for table `users_groups_association` */

DROP TABLE IF EXISTS `users_groups_association`;

CREATE TABLE `users_groups_association` (
  `user_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `cid` int(11) unsigned NOT NULL DEFAULT '1',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(11) unsigned DEFAULT NULL,
  `udate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `FK_users_groups_association__group_id` (`group_id`),
  CONSTRAINT `FK_users_groups_association__group_id` FOREIGN KEY (`group_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_users_groups_association__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `users_groups_association` */

insert  into `users_groups_association`(`user_id`,`group_id`,`cid`,`cdate`,`uid`,`udate`) values (1,233,1,'2013-03-20 13:56:17',0,'2013-03-20 13:56:17'),(240,235,1,'2013-12-05 16:28:14',NULL,NULL),(240,238,232,'2013-05-24 14:05:01',NULL,NULL);

/*Table structure for table `users_groups_data` */

DROP TABLE IF EXISTS `users_groups_data`;

CREATE TABLE `users_groups_data` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  `duplicate_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` text,
  `info` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_user_id__field_id__duplicate_id` (`user_id`,`field_id`,`duplicate_id`),
  KEY `FK_users_data__field_id` (`field_id`),
  KEY `FK_users_data__duplicate_id` (`duplicate_id`),
  CONSTRAINT `FK_users_data__field_id` FOREIGN KEY (`field_id`) REFERENCES `templates_structure` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_users_data__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3402 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups_data` */

insert  into `users_groups_data`(`id`,`user_id`,`field_id`,`duplicate_id`,`value`,`info`) values (2873,240,24060,0,'oleg@burlaca.com',NULL),(2874,240,24054,0,'Oleg Burlaca',NULL),(2875,240,24055,0,'Oleg Burlaca',NULL),(2876,240,24056,0,'Oleg Burlaca',NULL),(3401,240,24061,0,'1',NULL);

/*Table structure for table `users_groups_duplicates` */

DROP TABLE IF EXISTS `users_groups_duplicates`;

CREATE TABLE `users_groups_duplicates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_users_duplicates__user_id` (`user_id`),
  KEY `FK_users_duplicates__field_id` (`field_id`),
  KEY `FK_users_duplicates__pid` (`pid`),
  CONSTRAINT `FK_users_duplicates__field_id` FOREIGN KEY (`field_id`) REFERENCES `templates_structure` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_users_duplicates__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `users_groups_duplicates` */

/* Trigger structure for table `actions_log` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `actions_log_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `actions_log_ai` AFTER INSERT ON `actions_log` FOR EACH ROW BEGIN
	update users_groups set last_action_time = current_timestamp where id = NEW.user_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `files` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_ai` AFTER INSERT ON `files` FOR EACH ROW BEGIN
	UPDATE tree SET
		`name` = new.name
		, `date` = COALESCE(new.date, new.cdate)
		, cid = new.cid
		, cdate = new.cdate
		, uid = new.uid
		, udate = new.udate
		, size = (SELECT size FROM files_content WHERE id = new.content_id)
	WHERE id = new.id;
	if(new.content_id is not null) THEN
		update files_content set ref_count = ref_count + 1 where id = new.content_id;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_au` AFTER UPDATE ON `files` FOR EACH ROW BEGIN
	UPDATE tree SET
		`name` = new.name
		, `date` = coalesce(new.date, new.cdate)
		, date_end = COALESCE(new.date, new.cdate)
		, cid = new.cid
		, cdate = new.cdate
		, uid = new.uid
		, udate = new.udate
		, updated = (updated | 1)
		, size = (select size from files_content where id = new.content_id)
	WHERE id = new.id;
	if(coalesce(old.content_id, 0) <> coalesce(new.content_id, 0) ) then
		IF(old.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
		END IF;
		IF(new.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count + 1 WHERE id = new.content_id;
		END IF;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_ad` AFTER DELETE ON `files` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_content` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_content_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_content_bi` BEFORE INSERT ON `files_content` FOR EACH ROW BEGIN
	if( (coalesce(new.size, 0) = 0) or (new.type like 'image%') ) THEN
		set new.skip_parsing = 1;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_content` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_content_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_content_au` AFTER UPDATE ON `files_content` FOR EACH ROW BEGIN
	update tree, files set tree.updated = (tree.updated | 1) where files.content_id = NEW.id and files.id = tree.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_versions_ai` AFTER INSERT ON `files_versions` FOR EACH ROW BEGIN
	if(new.content_id is not null) THEN
		update files_content set ref_count = ref_count + 1 where id = new.content_id;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_versions_au` AFTER UPDATE ON `files_versions` FOR EACH ROW BEGIN
	if(coalesce(old.content_id, 0) <> coalesce(new.content_id, 0) ) then
		IF(old.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
		END IF;
		IF(new.content_id IS NOT NULL) THEN
			UPDATE files_content SET ref_count = ref_count + 1 WHERE id = new.content_id;
		END IF;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `files_versions_ad` AFTER DELETE ON `files_versions` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tasks_bi` BEFORE INSERT ON `tasks` FOR EACH ROW BEGIN
	SET new.missed = (new.date_end < current_timestamp);
	IF((new.missed = 1) && (new.status != 3) ) THEN
		SET new.status = 1;
	END IF;
	-- SET new.udate = CURRENT_TIMESTAMP;
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tasks_ai` AFTER INSERT ON `tasks` FOR EACH ROW BEGIN
 	INSERT INTO tasks_responsible_users (task_id, user_id)
		SELECT new.id, id
		FROM users_groups
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',id,',%');
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tasks_bu` BEFORE UPDATE ON `tasks` FOR EACH ROW BEGIN
	IF(new.status != 3) THEN -- not completed
		SET new.missed = (new.date_end < CURRENT_DATE);
		if(new.missed = 1) THEN
			set new.status = 1;
		end if;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tasks_au` AFTER UPDATE ON `tasks` FOR EACH ROW BEGIN
	DELETE FROM tasks_responsible_users
	WHERE task_id = old.id AND CONCAT(',', new.responsible_user_ids, ',') NOT LIKE CONCAT('%,',user_id,',%');
	INSERT INTO tasks_responsible_users (task_id, user_id)
		SELECT new.id, u.id
		FROM users_groups u
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',u.id,',%')
		ON DUPLICATE KEY UPDATE user_id = u.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `templates_structure` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `templates_structure_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `templates_structure_bi` BEFORE INSERT ON `templates_structure` FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	if (new.id = new.pid) then
		set msg = concat('Error: cyclic reference in templates_structure ', cast(new.id as char));
		signal sqlstate '45000' set message_text = msg;
	end if;
	/* end of trivial check for cycles */
	if(NEW.PID is not null) THEN
		SET NEW.LEVEL = (select `level` +1 from templates_structure where id = NEW.PID);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `templates_structure` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `templates_structure_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `templates_structure_bu` BEFORE UPDATE ON `templates_structure` FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	IF (new.id = new.pid) THEN
		SET msg = CONCAT('Error: cyclic reference in templates_structure ', CAST(new.id AS CHAR));
		signal SQLSTATE '45000' SET message_text = msg;
	END IF;
	/* end of trivial check for cycles */
	IF(NEW.PID IS NOT NULL) THEN
		SET NEW.LEVEL = coalesce((SELECT `level` +1 FROM templates_structure WHERE id = NEW.PID), 0);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_bi` BEFORE INSERT ON `tree` FOR EACH ROW BEGIN
	DECLARE msg VARCHAR(255);
	/* trivial check for cycles */
	if (new.id = new.pid) then
		set msg = concat('Error inserting cyclic reference: ', cast(new.id as char));
		signal sqlstate '45000' set message_text = msg;
	end if;
	/* end of trivial check for cycles */
	-- set owner id equal to creator id if null given
	set new.oid = coalesce(new.oid, new.cid);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_ai` AFTER INSERT ON `tree` FOR EACH ROW BEGIN
	/* get pids path, text path, case_id and store them in tree_info table*/
	declare tmp_new_case_id
		,tmp_new_security_set_id bigint unsigned default null;
	DECLARE tmp_new_pids
		,tmp_new_path text DEFAULT '';
	/* check if inserted node is a case */
	if( 	(new.template_id is not null)
		and (select id from templates where (id = new.template_id) and (`type` = 'case') )
	) THEN
		SET tmp_new_case_id = new.id;
	END IF;
	select
		ti.pids
		,CASE WHEN t.pid IS NULL
			THEN ti.path
			ELSE CONCAT( ti.path, t.name )
		END
		,coalesce(tmp_new_case_id, ti.case_id)
		,ti.security_set_id
	into
		tmp_new_pids
		,tmp_new_path
		,tmp_new_case_id
		,tmp_new_security_set_id
	from tree t
	left join tree_info ti on t.id = ti.id
	where t.id = new.pid;
	SET tmp_new_pids = TRIM( ',' FROM CONCAT( tmp_new_pids, ',', new.id) );
	SET tmp_new_path = sfm_adjust_path( tmp_new_path, '/' );
	if(new.inherit_acl = 0) then
		set tmp_new_security_set_id = f_get_security_set_id(new.id);
	END IF;
	insert into tree_info (
		id
		,pids
		,path
		,case_id
		,security_set_id
	)
	values (
		new.id
		,tmp_new_pids
		,tmp_new_path
		,tmp_new_case_id
		,tmp_new_security_set_id
	);
	/* end of get pids path, text path, case_id and store them in tree_info table*/
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_au` AFTER UPDATE ON `tree` FOR EACH ROW BEGIN
	DECLARE tmp_old_pids
		,tmp_new_pids
		,tmp_old_path
		,tmp_new_path TEXT DEFAULT '';
	DECLARE tmp_old_case_id
		,tmp_new_case_id
		,tmp_old_security_set_id
		,tmp_new_security_set_id BIGINT UNSIGNED DEFAULT NULL;
	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';
	DECLARE tmp_old_pids_length
		,tmp_old_path_length
		,tmp_old_security_set_length
		,tmp_acl_count INT UNSIGNED DEFAULT 0;
	/* get pids path, text path, case_id and store them in tree_info table*/
	IF( (COALESCE(old.pid, 0) <> COALESCE(new.pid, 0) )
	    OR ( COALESCE(old.name, '') <> COALESCE(new.name, '') )
	    OR ( old.inherit_acl <> new.inherit_acl )
	  )THEN
		-- get old data
		SELECT
			ti.pids -- 1,2,3
			,ti.path -- /Folder1/Folder2
			,ti.case_id -- null
			,ti.acl_count -- 2
			,ti.security_set_id -- 4
			,ts.set -- '1,3'
		INTO
			tmp_old_pids
			,tmp_old_path
			,tmp_old_case_id
			,tmp_acl_count
			,tmp_old_security_set_id
			,tmp_old_security_set
		FROM tree_info ti
		LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
		WHERE ti.id = new.id;
		/* check if updated node is a case */
		IF(tmp_old_case_id = old.id) THEN
			SET tmp_new_case_id = new.id;
		END IF;
		/* form new data based on new parent
		*/
		if(new.pid is null) THEN
			SET tmp_new_pids = new.id;
			SET tmp_new_path = '/';
			-- tmp_new_case_id already set above
			SET tmp_new_security_set_id = null;
			set tmp_new_security_set = '';
		ELSE
			SELECT
				ti.pids
				,case when t.pid is null
					then ti.path
					else CONCAT( ti.path, t.name )
				END
				,COALESCE(tmp_new_case_id, ti.case_id)
				,ti.security_set_id
				,ts.set
			INTO
				tmp_new_pids
				,tmp_new_path
				,tmp_new_case_id
				,tmp_new_security_set_id
				,tmp_new_security_set
			FROM tree t
			LEFT JOIN tree_info ti ON t.id = ti.id
			LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
			WHERE t.id = new.pid;
			SET tmp_new_pids = TRIM( ',' FROM CONCAT( tmp_new_pids, ',', new.id) );
			SET tmp_new_path = sfm_adjust_path( tmp_new_path, '/' );
		END IF;
		/* end of form new data based on new parent */
		/* detect new security set for the node */
		IF(tmp_acl_count > 0) THEN
			-- we need to replace security sets that include updated node id
			IF(new.inherit_acl = 0) THEN
				SET tmp_new_security_set = new.id;
			else
				SET tmp_new_security_set = TRIM( ',' FROM CONCAT(tmp_new_security_set, ',', new.id ) );
			END IF;
			UPDATE tree_acl_security_sets
			SET `set` = tmp_new_security_set
				,updated = 1
			WHERE id = tmp_old_security_set_id;
			SET tmp_new_security_set_id = tmp_old_security_set_id;
		ELSE
			-- we have to rename security sets for all childs without including updated node in the searched sets
			IF(new.inherit_acl = 0) THEN
				SET tmp_new_security_set_id = NULL;
				SET tmp_new_security_set = '';
			END IF;
		END IF;
		/* end of detect security set for the node */
		SET tmp_old_pids_length = LENGTH( tmp_old_pids ) +1;
		SET tmp_old_path_length = LENGTH( tmp_old_path ) +1;
		SET tmp_old_security_set_length = LENGTH( tmp_old_security_set ) +1;
		-- update node info with new data
		UPDATE tree_info
		SET	pids = tmp_new_pids
			,path = tmp_new_path
			,case_id = tmp_new_case_id
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.id;
		/* prepare new path, for name changes, to be updated in childs */
		set tmp_old_path = sfm_adjust_path(CONCAT(tmp_old_path, old.name), '/');
		SET tmp_new_path = sfm_adjust_path(CONCAT(tmp_new_path, new.name), '/');
		SET tmp_old_path_length = LENGTH( tmp_old_path ) +1;
		/* now cyclic updating all childs info for this updated object */
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_pids`(
			`id` BIGINT UNSIGNED NOT NULL,
			`inherit_acl` TINYINT(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`)
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_childs`(
			`id` BIGINT UNSIGNED NOT NULL,
			`inherit_acl` TINYINT(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`)
		);
		CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_tree_info_security_sets`(
			`id` BIGINT UNSIGNED NOT NULL,
			`new_id` BIGINT UNSIGNED NULL,
			`set` VARCHAR(9999),
			PRIMARY KEY (`id`),
			INDEX `IDX_tmp_tree_info_security_sets__set` (`set`),
			INDEX `IDX_tmp_tree_info_security_sets__new_id` (`new_id`)
		);
		DELETE FROM tmp_tree_info_pids;
		DELETE FROM tmp_tree_info_childs;
		DELETE FROM tmp_tree_info_security_sets;
		INSERT INTO tmp_tree_info_childs (id, inherit_acl)
			SELECT id, inherit_acl
			FROM tree
			WHERE pid = new.id;
		WHILE( ROW_COUNT() > 0 )DO
			UPDATE
				tmp_tree_info_childs
				,tree_info
			SET
				tree_info.pids = CONCAT(tmp_new_pids, SUBSTRING(tree_info.pids, tmp_old_pids_length))
				,tree_info.path = CONCAT(tmp_new_path, SUBSTRING(tree_info.path, tmp_old_path_length))
				,tree_info.case_id = CASE WHEN (tree_info.case_id = tmp_old_case_id) THEN tmp_new_case_id ELSE COALESCE(tree_info.case_id, tmp_new_case_id) END
				,tree_info.security_set_id =
					CASE
					WHEN (tmp_tree_info_childs.inherit_acl = 1)
					     AND ( coalesce(tree_info.security_set_id, 0) = coalesce(tmp_old_security_set_id, 0) )
						THEN tmp_new_security_set_id
					ELSE tree_info.security_set_id
					END
			WHERE tmp_tree_info_childs.id = tree_info.id;
			DELETE FROM tmp_tree_info_pids;
			INSERT INTO tmp_tree_info_pids
				SELECT id, inherit_acl
				FROM tmp_tree_info_childs;
			INSERT INTO tmp_tree_info_security_sets (id)
				SELECT DISTINCT ti.security_set_id
				FROM tmp_tree_info_childs c
				JOIN tree_info ti ON c.id = ti.id
				WHERE ti.security_set_id IS NOT NULL
					and c.inherit_acl = 1
			ON DUPLICATE KEY UPDATE id = ti.security_set_id;
			DELETE FROM tmp_tree_info_childs;
			INSERT INTO tmp_tree_info_childs (id, inherit_acl)
				SELECT
					t.id,
					case when ( (t.inherit_acl = 1) and (ti.inherit_acl = 1) ) then 1 else 0 END
				FROM tmp_tree_info_pids  ti
				JOIN tree t
					ON ti.id = t.pid;
		END WHILE;
		/* update old sequrity sets to new ones */
		UPDATE tmp_tree_info_security_sets
			,tree_acl_security_sets
			SET tree_acl_security_sets.`set` = TRIM( ',' FROM CONCAT(tmp_new_security_set, SUBSTRING(tree_acl_security_sets.set, tmp_old_security_set_length)) )
				,tree_acl_security_sets.updated = 1
		WHERE tmp_tree_info_security_sets.id <> coalesce(tmp_new_security_set_id, 0)
			AND tmp_tree_info_security_sets.id = tree_acl_security_sets.id
			AND tree_acl_security_sets.set LIKE CONCAT(tmp_old_security_set,',%');
		/* try to delete old security set if no dependances */
		if(tmp_old_security_set_id <> coalesce(tmp_new_security_set_id, 0)) THEN
			if( (select count(*) from tree_info where security_set_id = tmp_old_security_set_id) = 0) THEN
				delete from `tree_acl_security_sets` where id = tmp_old_security_set_id;
			END IF;
		END IF;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_ai` AFTER INSERT ON `tree_acl` FOR EACH ROW BEGIN
	declare tmp_acl_count int unsigned default 0;
	DECLARE tmp_new_security_set_id
		,tmp_old_security_set_id BIGINT UNSIGNED default null;
	DECLARE tmp_old_security_set, msg
		,tmp_new_security_set varchar(9999) default '';
	select ti.acl_count + 1
		,ti.security_set_id
		,coalesce( ts.set, '')
	into tmp_acl_count
		,tmp_old_security_set_id
		,tmp_old_security_set
	from tree_info ti
	left join `tree_acl_security_sets` ts on ti.security_set_id = ts.id
	where ti.id = new.node_id;
	/* we have to analize 2 cases when node has already other security rules attached and when this is the first rule attached.
	In first case we have to mark as updated only the security set assigned to this node and child sets
	In second case we have to add the new security set and update all lower security sets form that tree baranch
	*/
	if(tmp_acl_count > 1) THEN
		UPDATE tree_info
		SET acl_count = tmp_acl_count
		WHERE id = new.node_id;
		-- mark main security set as updated
		update `tree_acl_security_sets`
		set updated = 1
		where id = tmp_old_security_set_id;
		-- mark child security sets as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE `set` like concat(tmp_old_security_set, ',%');
	ELSE
		/* create new security set*/
		set tmp_new_security_set = trim( ',' from concat(tmp_old_security_set, ',', new.node_id) );
		insert into tree_acl_security_sets (`set`)
		values(tmp_new_security_set)
		on duplicate key
		update id = last_insert_id(id);
		set tmp_new_security_set_id = last_insert_id();
		/* end of create new security set*/
		UPDATE tree_info
		SET 	acl_count = tmp_acl_count
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.node_id;
		/* now we have to update all child security sets */
		CALL p_update_child_security_sets(new.node_id, tmp_old_security_set_id, tmp_new_security_set_id);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_au` AFTER UPDATE ON `tree_acl` FOR EACH ROW BEGIN
	DECLARE tmp_security_set_id BIGINT UNSIGNED;
	DECLARE tmp_security_set VARCHAR(9999) DEFAULT '';
	/* mark security set as updated including all dependent(child) security sets*/
	SELECT ti.security_set_id
		,ts.set
	INTO tmp_security_set_id
		,tmp_security_set
	FROM tree_info ti
	JOIN `tree_acl_security_sets` ts ON ti.security_set_id = ts.id
	WHERE ti.id = new.node_id;
	-- mark main security set as updated
	UPDATE `tree_acl_security_sets`
	SET updated = 1
	WHERE id = tmp_security_set_id;
	-- mark child security sets as updated
	UPDATE `tree_acl_security_sets`
	SET updated = 1
	WHERE `set` LIKE CONCAT(tmp_security_set, ',%');
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_ad` AFTER DELETE ON `tree_acl` FOR EACH ROW BEGIN
	DECLARE tmp_acl_count
		,tmp_length INT DEFAULT 0;
	DECLARE tmp_old_security_set_id
		,tmp_new_security_set_id BIGINT UNSIGNED default null;
	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';
	declare tmp_inherit_acl  tinyint(1) default 1;
	/*Note: node should have a security set associated if we are in after delete security rule */
	/* get node data */
	SELECT  case when (ti.acl_count >0)
			THEN ti.acl_count - 1
			ELSE 0
		END
		,ti.security_set_id
		,ts.set
	INTO tmp_acl_count
		,tmp_old_security_set_id
		,tmp_old_security_set
	FROM tree_info ti
	JOIN `tree_acl_security_sets` ts ON ti.security_set_id = ts.id
	WHERE ti.id = old.node_id;
	/* we have to analize 2 cases when this is not the last deleted security rule and when it's the last one.
	In first case we have to mark as updated only the security set assigned to this node and child sets
	In second case we have to update all lower security sets form that tree branch and delete assigned security set for this node
	*/
	IF(tmp_acl_count > 0) THEN
		UPDATE tree_info
		SET acl_count = tmp_acl_count
		WHERE id = old.node_id;
		-- mark main security set as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE id = tmp_old_security_set_id;
		-- mark child security sets as updated
		UPDATE `tree_acl_security_sets`
		SET updated = 1
		WHERE `set` LIKE CONCAT(tmp_old_security_set, ',%');
	ELSE
		/* get inheritance status of the node */
		select inherit_acl
		into tmp_inherit_acl
		from tree
		where id = old.node_id;
		/* create new security set or delete it*/
		if(tmp_inherit_acl = 1) THEN
			-- get old_security_set length
			set tmp_length = length( SUBSTRING_INDEX( tmp_old_security_set, ',', -1 ) );
			-- get string length for parent pids (without current node)
			set tmp_length = LENGTH( tmp_old_security_set) - tmp_length - 1;
			if(tmp_length < 0) Then
				Set tmp_length = 0;
			END IF;
			SET tmp_new_security_set = substring( tmp_old_security_set, 1,  tmp_length );
			/* get new security set id/**/
			if(LENGTH(tmp_new_security_set) > 0) THEN
				select id
				into tmp_new_security_set_id
				from tree_acl_security_sets
				where `set` = tmp_new_security_set;
			else
				set tmp_new_security_set_id = null;
			END IF;
		END IF;
		-- update tree_info for processed node
		UPDATE tree_info
		SET acl_count = tmp_acl_count
			,security_set_id = tmp_new_security_set_id
		WHERE id = old.node_id;
		/* now we have to update all child security sets */
		CALL p_update_child_security_sets(old.node_id, tmp_old_security_set_id, tmp_new_security_set_id);
		IF( COALESCE(tmp_new_security_set_id, 0) <> tmp_old_security_set_id) THEN
			DELETE FROM tree_acl_security_sets
			WHERE id = tmp_old_security_set_id;
		END IF;
	END IF;
  END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl_security_sets` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_security_sets_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_security_sets_bi` BEFORE INSERT ON `tree_acl_security_sets` FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl_security_sets` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_security_sets_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_security_sets_bu` BEFORE UPDATE ON `tree_acl_security_sets` FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_info` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_info_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_info_bu` BEFORE UPDATE ON `tree_info` FOR EACH ROW BEGIN
	if(
		(old.pids <> new.pids)
		OR(old.path <> new.path)
		OR ( coalesce(old.case_id, 0) <> coalesce(new.case_id, 0) )
		OR (old.acl_count <> new.acl_count)
		OR ( COALESCE(old.security_set_id, 0) <> COALESCE(new.security_set_id, 0) )
	)
	THEN
		SET new.updated = 1;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `users_groups_bi` BEFORE INSERT ON `users_groups` FOR EACH ROW BEGIN
	set new.searchField = concat(
		' '
		,coalesce(new.name, '')
		,' '
		,COALESCE(new.l1, '')
		,' '
		,COALESCE(new.l2, '')
		,' '
		,COALESCE(new.l3, '')
		,' '
		,COALESCE(new.l4, '')
		,' '
		,COALESCE(new.email, '')
		,' '
	);
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `users_groups_ai` AFTER INSERT ON `users_groups` FOR EACH ROW BEGIN
	declare tmp_everyone_user_id int unsigned;
	/* if we inserted a user then mark sequrity sets that contain everyone user as updated */
	IF( new.type = 2 ) THEN
		/* get everyone group id into temporary variable */
		SELECT id
		into tmp_everyone_user_id
		FROM users_groups
		WHERE `type` = 1
			AND `system` = 1
			AND name = 'everyone';
		/* update corresponding security sets */
		update `tree_acl_security_sets`
		set updated = 1
			where id in (
				select distinct security_set_id
				from `tree_acl_security_sets_result`
				where user_id = tmp_everyone_user_id
			)
		;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `users_groups_bu` BEFORE UPDATE ON `users_groups` FOR EACH ROW BEGIN
	SET new.searchField = CONCAT(
		' '
		,COALESCE(new.name, '')
		,' '
		,COALESCE(new.l1, '')
		,' '
		,COALESCE(new.l2, '')
		,' '
		,COALESCE(new.l3, '')
		,' '
		,COALESCE(new.l4, '')
		,' '
		,COALESCE(new.email, '')
		,' '
	);
	if( coalesce(old.password, '') <> coalesce(new.password, '') ) THEN
		set new.password_change = CURRENT_DATE;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups_association` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_association_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `users_groups_association_ai` AFTER INSERT ON `users_groups_association` FOR EACH ROW BEGIN
	/* mark sets as updated that depend on this group */
	UPDATE tree_acl_security_sets
	SET updated = 1
		WHERE id IN (
			SELECT DISTINCT ti.security_set_id
			FROM `tree_acl` ta
			JOIN tree_info ti ON ti.`id` = ta.`node_id`
			WHERE ta.`user_group_id` = new.group_id
		)
	;
	/* end of mark sets as updated that depend on this group */
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups_association` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_association_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `users_groups_association_ad` AFTER DELETE ON `users_groups_association` FOR EACH ROW BEGIN
	/* mark sets as updated that contain deleted user */
	UPDATE tree_acl_security_sets
	SET updated = 1
		WHERE id IN (
			SELECT DISTINCT security_set_id
			FROM `tree_acl_security_sets_result`
			WHERE user_id = old.user_id
		)
	;
	/* end of mark sets as updated that contain deleted user */
    END */$$


DELIMITER ;

/* Function  structure for function  `f_get_next_autoincrement_id` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_next_autoincrement_id` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_next_autoincrement_id`(in_tablename tinytext) RETURNS int(11)
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	return (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = in_tablename);
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_objects_case_id` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_objects_case_id` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_objects_case_id`(in_id int unsigned) RETURNS int(10) unsigned
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	declare tmp_pid int unsigned;
	DECLARE tmp_type varchar(10);
	DECLARE tmp_path TEXT CHARSET utf8 DEFAULT '';
	SET tmp_path = CONCAT('/', in_id);
	select t.pid, tp.`type` into tmp_pid, tmp_type from tree t left join templates tp on t.template_id = tp.id where t.id = in_id;
	while((tmp_pid is not null) AND (tmp_type <> 'case') AND ( INSTR(CONCAT(tmp_path, '/'), concat('/',tmp_pid,'/') ) =0) ) do
		SET tmp_path = CONCAT('/', tmp_pid, tmp_path);
		set in_id = tmp_pid;
		-- SELECT pid, `type` INTO tmp_pid, tmp_type FROM tree WHERE id = in_id;
		SELECT t.pid, tp.`type` INTO tmp_pid, tmp_type FROM tree t LEFT JOIN templates tp ON t.template_id = tp.id WHERE t.id = in_id;
	end while;
	if(tmp_type <> 'case') then
		set in_id = null;
	end if;
	return in_id;
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_security_set_id` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_security_set_id` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_security_set_id`(in_id bigint unsigned) RETURNS int(10) unsigned
    MODIFIES SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	DECLARE tmp_i
		,tmp_new_security_set_id BIGINT UNSIGNED DEFAULT NULL;
	DECLARE tmp_inherit_acl INT DEFAULT NULL;
	DECLARE tmp_ids_path
		,tmp_element
		,tmp_security_set VARCHAR(9999) DEFAULT '';
	DECLARE tmp_acl_count INT UNSIGNED DEFAULT 0;
	SET tmp_i = 1;
	set tmp_ids_path = f_get_tree_ids_path(in_id);
	set tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);
	while(tmp_element <> '')DO
		select inherit_acl
		into tmp_inherit_acl
		from tree
		where id = tmp_element;
		if(tmp_inherit_acl = 1) THEN
			SELECT COUNT(*)
			into tmp_acl_count
			FROM tree_acl
			WHERE node_id = tmp_element;
			if(tmp_acl_count > 0)THEN
				set tmp_security_set = trim(',' FROM concat(tmp_security_set, ',', tmp_element));
			end if;
		ELSE
			SET tmp_security_set = tmp_element;
		END IF;
		set tmp_i = tmp_i + 1;
		SET tmp_element = `sfm_get_path_element`(tmp_ids_path, '/', tmp_i);
		set tmp_acl_count = 0;
	END WHILE;
	if(tmp_security_set <> '') THEN
		set tmp_i = null;
		select id
		into tmp_i
		from tree_acl_security_sets
		where `md5` = md5(tmp_security_set);
		if(tmp_i is null) then
			insert into `tree_acl_security_sets` (`set`)
			values(tmp_security_set)
			on duplicate key update id = last_insert_id(id);
			set tmp_i = last_insert_id();
		END IF;
		return tmp_i;
	END IF;
	return null;
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_tag_pids` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_tag_pids` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tag_pids`(in_id int UNSIGNED) RETURNS varchar(300) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	declare rez varchar(300) CHARSET utf8;
	declare tmp_pid int UNSIGNED;
	set rez = in_id;
	select pid INTO tmp_pid from tags where id = in_id;
	while(tmp_pid is not null)do
		SET rez = CONCAT(tmp_pid, '/', rez);
		SELECT pid INTO tmp_pid FROM tags WHERE id = tmp_pid;
	END while;
	return rez;
END */$$
DELIMITER ;

/* Function  structure for function  `f_get_tree_ids_path` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_tree_ids_path` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tree_ids_path`(in_id bigint unsigned) RETURNS text CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Returns element ids path from the tree'
BEGIN
	DECLARE tmp_pid BIGINT UNSIGNED DEFAULT NULL;
	DECLARE rez text CHARSET utf8 DEFAULT '';
	set rez = concat('/', in_id);
	SELECT pid INTO tmp_pid FROM tree WHERE id = in_id;
	WHILE( (tmp_pid IS NOT NULL) and ( INSTR(concat(rez, '/'), concat('/', tmp_pid, '/') ) =0) ) DO
		SET rez = CONCAT('/', tmp_pid, rez);
		SELECT pid INTO tmp_pid FROM tree WHERE id = tmp_pid;
	END WHILE;
	RETURN rez;
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_tree_inherit_ids` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_tree_inherit_ids` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tree_inherit_ids`(in_id bigint unsigned) RETURNS text CHARSET utf8
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
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_tree_path` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_tree_path` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tree_path`(in_id bigint unsigned) RETURNS text CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'Returns element path from the tree'
BEGIN
	DECLARE tmp_pid BIGINT UNSIGNED DEFAULT NULL;
	DECLARE tmp_name varchar(500) CHARSET utf8 DEFAULT '';
	DECLARE rez text CHARSET utf8 DEFAULT '';
	DECLARE tmp_path TEXT CHARSET utf8 DEFAULT '';
	SET tmp_path = CONCAT('/', in_id);
	SELECT pid INTO tmp_pid FROM tree WHERE id = in_id;
	WHILE( (tmp_pid IS NOT NULL) AND ( INSTR(CONCAT(tmp_path, '/'), concat('/',tmp_pid,'/') ) =0) ) DO
		SET tmp_path = CONCAT('/', tmp_pid, tmp_path);
		SET rez = CONCAT('/', tmp_name, rez);
		SELECT pid, `name` INTO tmp_pid, tmp_name FROM tree WHERE id = tmp_pid;
	END WHILE;
	RETURN rez;
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_tree_pids` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_tree_pids` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `f_get_tree_pids`(in_id bigint unsigned) RETURNS varchar(500) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	declare tmp_pid bigint unsigned default null;
	DECLARE rez varchar(500) CHARSET utf8 default '';
	select pid into tmp_pid from tree where id = in_id;
	while( (tmp_pid is not null) AND ( INSTR(CONCAT(',',rez, ','), concat(',',tmp_pid,',') ) =0) )do
		if(rez <> '') then
			set rez = concat(',', rez);
		end if;
		set rez = concat(tmp_pid, rez);
		SELECT pid INTO tmp_pid FROM tree WHERE id = tmp_pid;
	end while;
	return rez;
    END */$$
DELIMITER ;

/* Function  structure for function  `remove_extra_spaces` */

/*!50003 DROP FUNCTION IF EXISTS `remove_extra_spaces` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `remove_extra_spaces`(
inString VARCHAR(500) CHARSET utf8) RETURNS varchar(500) CHARSET utf8
    DETERMINISTIC
BEGIN
	DECLARE _outString VARCHAR(500) CHARSET utf8;
	SET _outString = REPLACE(inString, '  ', ' ');
	while(inString <> _outString) do
		set inString = _outString;
		set _outString = replace(inString, '  ', ' ');
	END WHILE;
	SET _outString = TRIM(_outString);
	RETURN _outString;
END */$$
DELIMITER ;

/* Function  structure for function  `sfm_adjust_path` */

/*!50003 DROP FUNCTION IF EXISTS `sfm_adjust_path` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `sfm_adjust_path`(path VARCHAR(500), in_delimiter VARCHAR(50)) RETURNS varchar(500) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'adds slashes to the begin and end of the path'
BEGIN
	DECLARE tmp_delim_len SMALLINT;
	SET tmp_delim_len = LENGTH(in_delimiter);
	IF(path IS NULL) THEN SET path = ''; END IF;
	IF(LEFT (path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(in_delimiter, path); END IF;
	IF(RIGHT(path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(path, in_delimiter); END IF;
	RETURN path;
    END */$$
DELIMITER ;

/* Function  structure for function  `sfm_get_path_element` */

/*!50003 DROP FUNCTION IF EXISTS `sfm_get_path_element` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `sfm_get_path_element`(in_path VARCHAR(500) CHARSET utf8, in_delimiter VARCHAR(50) CHARSET utf8, in_element_index SMALLINT) RETURNS varchar(500) CHARSET utf8
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	SET in_path = sfm_adjust_path(in_path, in_delimiter);
	RETURN (SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(in_path, in_delimiter, in_element_index + 1), in_delimiter, -1));
END */$$
DELIMITER ;

/* Function  structure for function  `templates_get_path` */

/*!50003 DROP FUNCTION IF EXISTS `templates_get_path` */;
DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` FUNCTION `templates_get_path`(in_id int) RETURNS varchar(300) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
BEGIN
	declare rez, tmp varchar(300) CHARSET utf8;
	declare tmp_pid int;
	set rez = '';
	select title, pid INTO rez, tmp_pid from templates where id = in_id;
	while((tmp_pid is not null) and(tmp_pid not in (1)))do
		SELECT title, pid INTO tmp, tmp_pid FROM templates WHERE id = tmp_pid;
		if(coalesce(tmp, '') <> '') THEN
			if(coalesce(rez, '') <> '') THEN
				set rez = concat(tmp, ', ', rez);
			ELSE
				SET rez = tmp;
			END IF;
		END IF;
	END while;
	return rez;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_add_user` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_add_user` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_add_user`(username varchar(50), pass varchar(100) )
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	insert into users (`name`, `password`) values(username, MD5(CONCAT('aero', pass)));
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_clean_deleted_nodes` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_clean_deleted_nodes` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_clean_deleted_nodes`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	create temporary table tmp_clean_tree_ids SELECT id FROM tree WHERE dstatus > 0;
	DELETE FROM objects WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM files WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	DELETE FROM tasks WHERE id IN (SELECT id FROM tmp_clean_tree_ids);
	delete FROM tree WHERE id in (select id from tmp_clean_tree_ids);
	drop table tmp_clean_tree_ids;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_clear_lost_objects` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_clear_lost_objects` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_clear_lost_objects`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_clear_lost_ids(id bigint UNSIGNED);
	delete from tmp_clear_lost_ids;
	insert into tmp_clear_lost_ids
		SELECT o.id
		FROM objects o
		LEFT JOIN tree t
			ON o.`id` = t.id
		WHERE t.id IS NULL;
	DELETE FROM objects WHERE id IN
	(select id from tmp_clear_lost_ids);
	DELETE FROM tmp_clear_lost_ids;
	INSERT INTO tmp_clear_lost_ids
		SELECT od.id
		FROM objects_data od
		LEFT JOIN tree t
			ON od.`object_id` = t.id
		WHERE t.id IS NULL;
	DELETE FROM objects_data WHERE id IN
	(SELECT id FROM tmp_clear_lost_ids);
	drop table tmp_clear_lost_ids;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_delete_template_field_with_data` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_delete_template_field_with_data` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_delete_template_field_with_data`(in_field_id bigint unsigned)
    MODIFIES SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	delete from objects_data where field_id = in_field_id;
	DELETE FROM users_data WHERE field_id = in_field_id;
	delete from templates_structure where id = in_field_id;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_delete_tree_node` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_delete_tree_node` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_delete_tree_node`(in_id bigint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DELETE FROM tree WHERE id = in_id;
	DELETE FROM objects WHERE id = in_id;
	DELETE FROM files WHERE id = in_id;
	DELETE FROM tasks WHERE id = in_id;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_mark_all_childs_as_deleted` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_mark_all_childs_as_deleted` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_deleted`(in_id bigint unsigned, in_did int unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids2(id BIGINT UNSIGNED);
	delete from tmp_dchild_ids;
	DELETE FROM tmp_dchild_ids2;
	insert into tmp_dchild_ids select id from tree where pid = in_id;
	while(ROW_COUNT() > 0)do
		update tree, tmp_dchild_ids set tree.did = in_did, tree.ddate = CURRENT_TIMESTAMP, tree.dstatus = 2 where tmp_dchild_ids.id = tree.id;
		DELETE FROM tmp_dchild_ids2;
		insert into tmp_dchild_ids2 select id from tmp_dchild_ids;
		delete from tmp_dchild_ids;
		INSERT INTO tmp_dchild_ids SELECT t.id FROM tree t join tmp_dchild_ids2 c on t.pid = c.id;
	END WHILE;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_recalculate_security_sets` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_recalculate_security_sets` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_recalculate_security_sets`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	truncate table `tree_acl_security_sets`;
	insert into tree_acl_security_sets (id, `set`)
		select node_id, `f_get_tree_inherit_ids`(node_id) from
		(SELECT DISTINCT node_id FROM `tree_acl`) t;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_sort_tags` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_sort_tags` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_sort_tags`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Sort tags by l1 field and updates incremental order field'
BEGIN
	create table if not exists tmp_tags_sort (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `pid` int(11) unsigned DEFAULT NULL,
	  /*`l1` varchar(100) DEFAULT NULL,
	  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1=tag else = folder',/**/
	  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`));
	delete from tmp_tags_sort;
	SET @i = 0;
	insert into tmp_tags_sort (id, `order`) select id, @i:=@i+1 from tags where pid is null order by `type`, l1;
	while (select count(*) from tags t left join tmp_tags_sort ts1 on t.pid = ts1.id LEFT JOIN tmp_tags_sort ts2 ON t.id = ts2.id where ts1.id is not null and ts2.id is null) do
		SET @i = 0;
		SET @pid = 0;
		INSERT INTO tmp_tags_sort (id, `order`, pid)
			SELECT t.id, case when t.pid = @pid then @i:=@i+1 else @i:=1 END, @pid := t.pid
			FROM tags t left join tmp_tags_sort ts3 on t.pid = ts3.id LEFT JOIN tmp_tags_sort ts4 ON t.id = ts4.id WHERE ts3.id is NOT null and ts4.id is null ORDER BY t.pid, t.`type`, t.l1;
	end while;
	-- select * from tmp_tags_sort;
	update tags t, tmp_tags_sort ts set t.order = ts.order where t.id = ts.id;
	drop table tmp_tags_sort;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_sort_templates` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_sort_templates` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_sort_templates`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Sort templates by l1 field and updates incremental order field'
BEGIN
	create table if not exists tmp_templates_sort (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `pid` int(11) unsigned DEFAULT NULL,
	  /*`l1` varchar(100) DEFAULT NULL,
	  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1=tag else = folder',/**/
	  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`));
	delete from tmp_templates_sort;
	SET @i = 0;
	insert into tmp_templates_sort (id, `order`) select id, @i:=@i+1 from templates where pid is null order by `type`, l1;
	while (select count(*) from templates t left join tmp_templates_sort ts1 on t.pid = ts1.id LEFT JOIN tmp_templates_sort ts2 ON t.id = ts2.id where ts1.id is not null and ts2.id is null) do
		SET @i = 0;
		SET @pid = 0;
		INSERT INTO tmp_templates_sort (id, `order`, pid)
			SELECT t.id, case when t.pid = @pid then @i:=@i+1 else @i:=1 END, @pid := t.pid
			FROM templates t left join tmp_templates_sort ts3 on t.pid = ts3.id LEFT JOIN tmp_templates_sort ts4 ON t.id = ts4.id WHERE ts3.id is NOT null and ts4.id is null ORDER BY t.pid, t.`type`, t.l1;
	end while;
	-- select * from tmp_templates_sort;
	update templates t, tmp_templates_sort ts set t.order = ts.order where t.id = ts.id;
	drop table tmp_templates_sort;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_child_security_sets` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_child_security_sets` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_child_security_sets`(
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
			,SUBSTRING(tree_acl_security_sets.set, tmp_security_set_length)
		)
		,`tree_acl_security_sets`.updated = 1
	WHERE tmp_update_child_sets_security_sets.id = tree_acl_security_sets.id;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_files_content__ref_count` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_files_content__ref_count` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_files_content__ref_count`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	UPDATE files_content c SET ref_count = COALESCE((SELECT COUNT(id) FROM files WHERE content_id = c.id), 0)+
	COALESCE((SELECT COUNT(id) FROM files_versions WHERE content_id = c.id), 0);
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_tree_acl_count` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_tree_acl_count` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_tree_acl_count`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'Update acl_count field in tree table'
BEGIN
	create temporary table tmp_tree_acl_count select node_id `id`, count(*) `count` FROM `tree_acl` group by node_id;
	UPDATE tree, tmp_tree_acl_count set tree.acl_count = tmp_tree_acl_count.count where tree.id = tmp_tree_acl_count.id;
	drop table tmp_tree_acl_count;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_tree_info` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_tree_info` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_tree_info`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'update tree_info_table. \n	This procedure is a quick solution and is known to work slow on big trees.\n	It''s actually designed just for upgrading from an old casebox database to new security updates format.\n	'
BEGIN
	delete from tree_info;
	delete from `tree_acl_security_sets`;
	ALTER TABLE `tree_acl_security_sets` AUTO_INCREMENT = 1;
	create temporary table tmp_tree_info
	SELECT id
		,REPLACE(TRIM( '/' FROM `f_get_tree_ids_path`(id)), '/', ',') `pids`
		,`f_get_tree_path`(id) `path`
		,`f_get_objects_case_id`(id) `case_id`
		,(SELECT COUNT(*) FROM `tree_acl` WHERE node_id = t.id) `acl_count`
		,`f_get_security_set_id`(id) `security_set_id`
		,1 `updated`
	FROM tree t;
	INSERT INTO tree_info (
		id
		,pids
		,path
		,case_id
		,acl_count
		,security_set_id
		,updated
	) select * from tmp_tree_info ti
	on duplicate key
	update
		pids = ti.pids
		,path = ti.path
		,case_id =  ti.case_id
		,acl_count = ti.acl_count
		,security_set_id = ti.security_set_id
		,updated = 1;
	drop TEMPORARY TABLE tmp_tree_info;
	ALTER TABLE `tree_acl_security_sets` AUTO_INCREMENT = 1;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_users_first_and_last_names_from_l1` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_users_first_and_last_names_from_l1` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_update_users_first_and_last_names_from_l1`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	UPDATE users_groups
	SET
		first_name = `sfm_get_path_element`(l1, ' ', 1)
		,last_name = TRIM(
			CONCAT(
				`sfm_get_path_element`(l1, ' ', 2)
				,' '
				,`sfm_get_path_element`(l1, ' ', 3)
			)
		);
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_user_login` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_user_login` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_user_login`(IN `in_username` VARCHAR(50), `in_password` VARCHAR(100), `in_from_ip` VARCHAR(40))
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
    COMMENT 'checks for login credetials and log the attemps'
BEGIN
	DECLARE `user_id` INT DEFAULT NULL;
	DECLARE `user_pass` VARCHAR(255);
	SELECT `id`, `password`  INTO `user_id`, `user_pass` FROM users_groups WHERE `name` = `in_username` and enabled = 1 and did is NULL;
	IF(user_id IS NOT NULL) THEN
		IF(`user_pass` = MD5(CONCAT('aero', `in_password`))) THEN
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = 1, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 1 `status`;
		ELSE
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = login_successful-2, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 0 `status`;
		END IF;
	ELSE
		SELECT 0 `user_id`, 0 `status`;
	END IF;
    END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
