/*
SQLyog Ultimate v10.42 
MySQL - 5.5.28-log : Database - cb_demo1
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`cb_demo1` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `cb_demo1`;

/*Table structure for table `actions_log` */

DROP TABLE IF EXISTS `actions_log`;

CREATE TABLE `actions_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `to_user_ids` varchar(100) DEFAULT NULL,
  `office_id` int(11) unsigned DEFAULT NULL,
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
  KEY `FK_actions_log__to_user_id` (`to_user_ids`),
  KEY `FK_actions_log__case_id` (`case_id`),
  KEY `FK_actions_log__object_id` (`object_id`),
  KEY `FK_actions_log__task_id` (`task_id`),
  CONSTRAINT `FK_actions_log__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `actions_log` */

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `param` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`,`param`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`id`,`pid`,`param`,`value`) values (1,NULL,'max_files_version_count','*:1;doc,docx,xls,xlsx,pdf:5;png,gif,jpg,jpeg,tif,tiff:2'),(2,NULL,'project_name_en','Demo 1'),(3,NULL,'project_name_ru','Demo 1'),(7,NULL,'task_categories','69'),(8,NULL,'templateIcons','\r\nicon-arrow-left-medium\r\nicon-arrow-left-medium-green\r\nicon-arrow-left\r\nicon-arrow-right-medium\r\nicon-arrow-right\r\nicon-case_card\r\nicon-complaint\r\nicon-complaint-subjects\r\nicon-info-action\r\nicon-decision\r\nicon-echr_complaint\r\nicon-echr_decision\r\nicon-petition\r\nicon-balloon\r\nicon-bell\r\nicon-blog-blue\r\nicon-blog-magenta\r\nicon-blue-document-small\r\nicon-committee-phase\r\nicon-document-medium\r\nicon-document-stamp\r\nicon-document-text\r\nicon-mail\r\nicon-object1\r\nicon-object2\r\nicon-object3\r\nicon-object4\r\nicon-object5\r\nicon-object6\r\nicon-object7\r\nicon-object8\r\nicon-zone\r\nicon-applicant\r\nicon-suspect'),(10,NULL,'folder_templates','18,21'),(11,NULL,'default_folder_template','18'),(12,NULL,'default_file_template','19'),(13,NULL,'default_task_template','16'),(14,NULL,'default_event_template','17'),(15,NULL,'languages','en,ru');

/*Table structure for table `crons` */

DROP TABLE IF EXISTS `crons`;

CREATE TABLE `crons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cron_id` varchar(30) DEFAULT NULL,
  `cron_file` varchar(500) DEFAULT NULL,
  `last_start_time` timestamp NULL DEFAULT NULL,
  `last_end_time` timestamp NULL DEFAULT NULL,
  `execution_info` longtext,
  `execution_skip_times` smallint(6) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `crons` */

insert  into `crons`(`id`,`cron_id`,`cron_file`,`last_start_time`,`last_end_time`,`execution_info`,`execution_skip_times`) values (1,'send_log_notifications','/var/www/vhosts/casebox.org/casebox/crons/cron_send_log_notifications.php','2013-04-22 09:14:31','2013-04-22 09:14:31','ok',0),(2,'solr_update_tree','/var/www/casebox/casebox/crons/cron_solr_update_tree.php','2013-05-19 11:27:21','2013-05-19 11:27:21','ok',0),(3,'extract_file_contents','/var/www/casebox/casebox/crons/cron_extracting_file_contents.php','2013-05-19 11:25:02','2013-05-19 11:25:02','{\"Total\":0,\"Processed\":0,\"Not found\":0,\"Processed List\":[],\"Not found List\":[]}',0),(4,'check_deadlines','/var/www/casebox/casebox/crons/cron_check_deadlines.php','2013-05-19 11:27:02','2013-05-19 11:27:02','ok',0),(5,'test','/var/www/casebox/casebox/crons/test_mail_format.php','2013-01-24 09:14:54','2013-01-24 09:14:54','ok',0),(6,'send_notifications','/var/www/casebox/sys/crons/cron_send_notifications.php','2013-05-19 11:28:03','2013-05-19 11:28:03','ok',0);

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
  `old_id` int(10) unsigned DEFAULT NULL,
  `old_name` varchar(250) DEFAULT NULL,
  `cid` int(10) unsigned NOT NULL DEFAULT '1',
  `uid` int(10) unsigned NOT NULL DEFAULT '1',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `udate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_old_id` (`old_id`),
  KEY `idx_name` (`name`),
  KEY `FK_files__content_id` (`content_id`),
  CONSTRAINT `FK_files__content_id` FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;

/*Data for the table `files` */

insert  into `files`(`id`,`content_id`,`date`,`name`,`title`,`old_id`,`old_name`,`cid`,`uid`,`cdate`,`udate`) values (31,1,'1987-06-26','Honduras-Velasquez Rodriguez-C-1-DJPO-19870626-DOC-Eng.doc','',NULL,NULL,1,1,'2013-01-28 16:09:26','2013-01-28 16:09:26'),(32,2,'1987-06-26','Honduras-Velasquez Rodriguez-C-1-DJPO-19870626-doc-spa.doc','',NULL,NULL,1,1,'2013-01-28 16:09:59','2013-01-28 16:09:59'),(33,3,'1987-06-26','Honduras-Velasquez Rodriguez-C-1-DJPO-19870626-PDF-Eng.pdf','',NULL,NULL,1,1,'2013-01-28 16:10:14','2013-01-28 16:10:14'),(34,4,'1987-06-26','Honduras-Velasquez Rodriguez-C-1-DJPO-19870626-pdf-spa.pdf','',NULL,NULL,1,1,'2013-01-28 16:10:47','2013-01-28 16:10:47'),(70,5,'2013-02-01','Test Document.docx','Test document',NULL,NULL,2,2,'2013-02-01 12:11:35','2013-02-01 12:11:35'),(102,6,'0000-00-00','hib.bat<img src=a onerror=alert(document.cookie)>',NULL,NULL,NULL,3,3,'2013-04-30 17:03:32','2013-04-30 17:03:32'),(103,7,'0000-00-00','rest.bat','',NULL,NULL,3,3,'2013-05-01 15:21:05','2013-05-01 15:21:05'),(104,6,'0000-00-00','hib.bat','',NULL,NULL,3,3,'2013-05-01 15:25:39','2013-05-01 15:25:39'),(105,6,'0000-00-00','hib (1).bat','',NULL,NULL,3,3,'2013-05-01 15:26:44','2013-05-01 15:26:44'),(106,6,'0000-00-00','hib.bat','',NULL,NULL,3,3,'2013-05-01 15:27:59','2013-05-01 15:27:59'),(107,8,NULL,'1351wordsforchildrenandadults.pdf','',NULL,NULL,3,3,'2013-05-12 09:02:45','2013-05-12 09:02:45'),(108,9,NULL,'jquery_1_3_1440.png','',NULL,NULL,3,3,'2013-05-12 09:03:02','2013-05-12 09:03:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Data for the table `files_content` */

insert  into `files_content`(`id`,`size`,`pages`,`type`,`path`,`ref_count`,`parse_status`,`skip_parsing`,`md5`) values (1,115200,1,'application/msword','1987/06/26',1,1,0,'5e992ada5ab15c95f8d75c328c10eaa9s115200'),(2,133632,19,'application/msword','1987/06/26',1,1,0,'e2f9ffd600a198f484b9364b55304c53s133632'),(3,114271,18,'application/pdf','1987/06/26',1,1,0,'2385969ffaa146411f7d79e899e595f1s114271'),(4,97863,19,'application/pdf','1987/06/26',1,1,0,'fef1de96115eca29befbe4a604ebb062s97863'),(5,24219,1,'application/vnd.openxmlformats-officedocument.wordprocessingml.document','2013/02/01',1,1,0,'5b7d8c53bbb48a5deb34279cccc715dds24219'),(6,70,NULL,'application/octet-stream','2013/04/30',4,1,0,'00f6d201f66a33c5c568ae087335bdc6s70'),(7,55,NULL,'application/octet-stream','2013/05/01',1,1,0,'9d09444f0420ca2338e06a9fba087a8as55'),(8,17897201,141,'application/pdf','2013/05/12',1,1,1,'576231ba9b5cdee1adf350c84d66b8bds17897201'),(9,719539,NULL,'image/png','2013/05/12',1,NULL,1,'9b169feb4ac6a536a688d84fd5189367s719539');

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
  `node_ids` varchar(20) DEFAULT NULL,
  `node_template_ids` varchar(10) DEFAULT NULL,
  `menu` text,
  `user_group_ids` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `menu` */

insert  into `menu`(`id`,`node_ids`,`node_template_ids`,`menu`,`user_group_ids`) values (1,'23',NULL,'20,-,16,17,-,18',NULL),(2,NULL,NULL,'16,17,-,18',NULL),(3,'73',NULL,'22,-,18',NULL),(4,'24',NULL,'21,-,18',NULL),(5,NULL,'21','23',NULL);

/*Table structure for table `messages` */

DROP TABLE IF EXISTS `messages`;

CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `to_user_id` int(10) unsigned DEFAULT NULL,
  `nid` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `message` text,
  `cid` int(10) unsigned NOT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `messages__nid` (`nid`),
  KEY `messages__to_user_id` (`to_user_id`),
  CONSTRAINT `messages__nid` FOREIGN KEY (`nid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages__to_user_id` FOREIGN KEY (`to_user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `messages` */

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
  `subject` varchar(250) DEFAULT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_notifications__file_id` (`file_id`),
  KEY `FK_notifications__user_id` (`user_id`),
  KEY `FK_notifications__case_id` (`case_id`),
  KEY `FK_notifications__object_id` (`object_id`),
  KEY `FK_notifications__task_id` (`task_id`),
  CONSTRAINT `FK_notifications__file_id` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__object_id` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `notifications` */

/*Table structure for table `objects` */

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `old_id` int(10) unsigned DEFAULT NULL,
  `pid` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `custom_title` varchar(200) DEFAULT NULL,
  `template_id` int(11) unsigned DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `author` int(11) unsigned DEFAULT NULL,
  `is_active` tinyint(1) unsigned DEFAULT '0',
  `iconCls` varchar(150) DEFAULT NULL,
  `details` text,
  `private_for_user` int(11) unsigned DEFAULT NULL,
  `files_count` int(10) unsigned DEFAULT NULL,
  `cid` int(11) unsigned DEFAULT NULL,
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(11) unsigned DEFAULT NULL,
  `udate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_old_id` (`old_id`,`template_id`),
  KEY `FK_objects__private_for_user` (`private_for_user`),
  KEY `FK_objects__template_id` (`template_id`),
  KEY `FK_objects__cid` (`cid`),
  KEY `FK_objects__uid` (`uid`),
  KEY `FK_objects_pid` (`pid`),
  CONSTRAINT `FK_objects__cid` FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_objects__private_for_user` FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_objects__template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_objects__uid` FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_objects_pid` FOREIGN KEY (`pid`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8;

/*Data for the table `objects` */

insert  into `objects`(`id`,`old_id`,`pid`,`title`,`custom_title`,`template_id`,`date_start`,`date_end`,`author`,`is_active`,`iconCls`,`details`,`private_for_user`,`files_count`,`cid`,`cdate`,`uid`,`udate`) values (75,NULL,NULL,'','Switzerland',21,'2013-04-27 00:00:00',NULL,NULL,0,'icon-blog-blue',NULL,1,NULL,1,'2013-04-27 20:55:36',NULL,NULL),(76,NULL,NULL,'','Italy',21,'2013-04-27 00:00:00',NULL,NULL,0,'icon-blog-blue',NULL,1,NULL,1,'2013-04-27 20:55:44',NULL,NULL),(77,NULL,NULL,'','Russia',21,'2013-04-27 00:00:00',NULL,NULL,0,'icon-blog-blue',NULL,1,NULL,1,'2013-04-27 20:55:53',NULL,NULL),(78,NULL,NULL,'','Ukraine',21,'2013-04-27 00:00:00',NULL,NULL,0,'icon-blog-blue',NULL,1,NULL,1,'2013-04-27 20:56:01',NULL,NULL),(79,NULL,NULL,'','Geneva',23,'2013-04-27 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,1,NULL,1,'2013-04-27 20:56:52',NULL,NULL),(80,NULL,NULL,'','Bern',23,'2013-04-27 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,1,NULL,1,'2013-04-27 20:56:59',NULL,NULL),(81,NULL,NULL,'','Moscow',23,'2013-04-27 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,1,NULL,1,'2013-04-27 20:57:17',NULL,NULL),(82,NULL,NULL,'','Nizny-Novgorod',23,'2013-04-27 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,1,NULL,1,'2013-04-27 20:57:30',NULL,NULL),(83,NULL,NULL,'Folder (1)','A',18,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,1,'2013-04-27 20:57:44',NULL,NULL),(84,NULL,NULL,'Folder (1)','B',18,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,1,'2013-04-27 20:59:16',NULL,NULL),(85,NULL,NULL,'Folder (1)','C',18,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,1,'2013-04-27 20:59:20',NULL,NULL),(86,NULL,NULL,'','Archie Bloomberg',22,'2013-04-27 00:00:00',NULL,NULL,0,'icon-object6',NULL,1,NULL,1,'2013-04-27 20:59:53',NULL,NULL),(87,NULL,NULL,'','Matthew Hamilton',22,'2013-04-27 00:00:00',NULL,NULL,0,'icon-object6',NULL,1,NULL,1,'2013-04-27 21:00:08',NULL,NULL),(88,NULL,NULL,'','Christopher Smith',22,'2013-04-27 00:00:00',NULL,NULL,0,'icon-object6',NULL,1,NULL,1,'2013-04-27 21:00:33',NULL,NULL),(89,NULL,NULL,'Folder (1)','M',18,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,1,'2013-04-27 21:00:39',NULL,NULL),(90,NULL,NULL,'','Carol Billings',22,'2013-04-27 00:00:00',NULL,NULL,0,'icon-object6',NULL,1,NULL,1,'2013-04-27 21:01:33',NULL,NULL),(91,NULL,NULL,'','Ben Sparks',22,'2013-04-27 00:00:00',NULL,NULL,0,'icon-object6',NULL,1,NULL,1,'2013-04-27 21:02:07',NULL,NULL),(92,NULL,NULL,'','Content Management Interoperability Services',20,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-text',NULL,NULL,NULL,1,'2013-04-27 21:10:45',3,'2013-04-29 21:27:34'),(95,NULL,NULL,'','Content management system',20,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-text',NULL,NULL,NULL,3,'2013-04-28 08:38:33',3,'2013-04-28 08:52:47'),(96,NULL,NULL,'','Document management system',20,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-text',NULL,3,NULL,3,'2013-04-28 08:46:07',3,'2013-04-28 08:47:57'),(97,NULL,NULL,'','Milan',23,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,3,NULL,3,'2013-04-28 08:46:20',NULL,NULL),(98,NULL,NULL,'','Rome',23,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,3,NULL,3,'2013-04-28 08:46:31',NULL,NULL),(99,NULL,NULL,'','Kiev',23,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,3,NULL,3,'2013-04-28 08:46:39',NULL,NULL),(100,NULL,NULL,'','Kharkov',23,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-medium',NULL,3,NULL,3,'2013-04-28 08:46:56',NULL,NULL),(101,NULL,NULL,'','Object-relational database',20,'2013-04-28 00:00:00',NULL,NULL,0,'icon-document-text',NULL,NULL,NULL,3,'2013-04-28 08:50:48',3,'2013-04-28 08:51:47');

/*Table structure for table `objects_data` */

DROP TABLE IF EXISTS `objects_data`;

CREATE TABLE `objects_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  `duplicate_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` text,
  `info` varchar(250) DEFAULT NULL,
  `files` varchar(250) DEFAULT NULL,
  `private_for_user` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_object_id__field_id__duplicate_id` (`object_id`,`field_id`,`duplicate_id`),
  KEY `FK_objects_data__duplicate_id` (`duplicate_id`),
  KEY `FK_objects_data__field_id` (`field_id`),
  KEY `FK_objects_data__private_for_user` (`private_for_user`),
  KEY `IDX_object_id` (`object_id`),
  CONSTRAINT `FK_objects_data__field_id` FOREIGN KEY (`field_id`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_objects_data__private_for_user` FOREIGN KEY (`private_for_user`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8;

/*Data for the table `objects_data` */

insert  into `objects_data`(`id`,`object_id`,`field_id`,`duplicate_id`,`value`,`info`,`files`,`private_for_user`) values (73,75,325,0,'Switzerland','',NULL,NULL),(74,75,326,0,'2013-04-27T00:00:00','',NULL,NULL),(75,76,325,0,'Italy','',NULL,NULL),(76,76,326,0,'2013-04-27T00:00:00','',NULL,NULL),(77,77,325,0,'Russia','',NULL,NULL),(78,77,326,0,'2013-04-27T00:00:00','',NULL,NULL),(79,78,325,0,'Ukraine','',NULL,NULL),(80,78,326,0,'2013-04-27T00:00:00','',NULL,NULL),(81,79,328,0,'Geneva','',NULL,NULL),(82,79,330,0,'2013-04-27T00:00:00','',NULL,NULL),(83,80,328,0,'Bern','',NULL,NULL),(84,80,330,0,'2013-04-27T00:00:00','',NULL,NULL),(85,81,328,0,'Moscow','',NULL,NULL),(86,81,330,0,'2013-04-27T00:00:00','',NULL,NULL),(87,82,328,0,'Nizny-Novgorod','',NULL,NULL),(88,82,330,0,'2013-04-27T00:00:00','',NULL,NULL),(89,86,327,0,'Archie Bloomberg','',NULL,NULL),(90,86,329,0,'2013-04-27T00:00:00','',NULL,NULL),(91,87,327,0,'Matthew Hamilton','',NULL,NULL),(92,87,329,0,'2013-04-27T00:00:00','',NULL,NULL),(93,88,327,0,'Christopher Smith','',NULL,NULL),(94,88,329,0,'2013-04-27T00:00:00','',NULL,NULL),(95,90,327,0,'Carol Billings','',NULL,NULL),(96,90,329,0,'2013-04-27T00:00:00','',NULL,NULL),(97,91,327,0,'Ben Sparks','',NULL,NULL),(98,91,329,0,'2013-04-27T00:00:00','',NULL,NULL),(99,92,331,0,'90','',NULL,NULL),(100,92,323,0,'77','',NULL,NULL),(101,92,324,0,'81','',NULL,NULL),(102,92,320,0,'Content Management Interoperability Services','',NULL,NULL),(103,92,321,0,'2013-04-28T00:00:00','',NULL,NULL),(104,92,322,0,'<b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">Content Management Interoperability Services</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">(</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">CMIS</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">) is an</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Open_standard\" title=\"Open standard\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">open standard</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">that allows different</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Content_management_systems\" title=\"Content management systems\" class=\"mw-redirect\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">content management systems</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">to inter-operate over the</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Internet\" title=\"Internet\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">Internet</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">.</span><sup id=\"cite_ref-1\" class=\"reference\" style=\"font-family: sans-serif; line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services#cite_note-1\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[1]</a></sup><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">Specifically, CMIS defines an</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Abstraction_layer\" title=\"Abstraction layer\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">abstraction layer</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">for controlling diverse</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Document_management_system\" title=\"Document management system\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">document management systems and repositories</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">using</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/World_Wide_Web\" title=\"World Wide Web\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">web</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Communications_protocol\" title=\"Communications protocol\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">protocols</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">.</span><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">CMIS defines a&nbsp;<a href=\"http://en.wikipedia.org/wiki/Domain_model\" title=\"Domain model\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">domain model</a>&nbsp;plus&nbsp;<a href=\"http://en.wikipedia.org/wiki/Web_service\" title=\"Web service\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">web services</a>&nbsp;and&nbsp;<a href=\"http://en.wikipedia.org/wiki/AtomPub\" title=\"AtomPub\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Restful AtomPub (RFC5023)</a>&nbsp;bindings that can be used by applications.&nbsp;<a href=\"http://en.wikipedia.org/wiki/OASIS_(organization)\" title=\"OASIS (organization)\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">OASIS</a>, a web standards consortium, approved CMIS as an OASIS Specification on May 1, 2010.<sup id=\"cite_ref-2\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services#cite_note-2\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[2]</a></sup>&nbsp;CMIS 1.1 has been approved as an OASIS specification on December 12, 2012.<sup id=\"cite_ref-3\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services#cite_note-3\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[3]</a></sup></p><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">CMIS provides a common data model covering typed files and folders with generic properties that can be set or read. There is a set of services for adding and retrieving documents (\'objects\'). There may be an access control system, a checkout and version control facility, and the ability to define generic relations. Two protocol bindings are defined, one using WSDL and&nbsp;<a href=\"http://en.wikipedia.org/wiki/Simple_Object_Access_Protocol\" title=\"Simple Object Access Protocol\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">SOAP</a>&nbsp;and another using<a href=\"http://en.wikipedia.org/wiki/Representational_State_Transfer\" title=\"Representational State Transfer\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Representational State Transfer (REST)</a>, using the AtomPub convention.<sup id=\"cite_ref-4\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services#cite_note-4\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[4]</a></sup>&nbsp;The model is based on common architectures of document management systems.</p><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">Although initiated by&nbsp;<a href=\"http://en.wikipedia.org/wiki/Association_for_Information_and_Image_Management\" title=\"Association for Information and Image Management\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">AIIM</a>, CMIS is now administered by the OASIS standards body. Participants in the process include&nbsp;<a href=\"http://en.wikipedia.org/wiki/Adobe_Systems_Incorporated\" title=\"Adobe Systems Incorporated\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Adobe Systems Incorporated</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Alfresco_(software)\" title=\"Alfresco (software)\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Alfresco</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/EMC_Corporation\" title=\"EMC Corporation\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">EMC</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Exo_platform\" title=\"Exo platform\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">eXo</a>,<a href=\"http://en.wikipedia.org/wiki/FatWire\" title=\"FatWire\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">FatWire</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/HP\" title=\"HP\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">HP</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/IBM\" title=\"IBM\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">IBM</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/ISIS_Papyrus\" title=\"ISIS Papyrus\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">ISIS Papyrus</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Liferay\" title=\"Liferay\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Liferay</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Microsoft\" title=\"Microsoft\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Microsoft</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Nuxeo\" title=\"Nuxeo\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Nuxeo</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Open_Text_Corporation\" title=\"Open Text Corporation\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Open Text</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Oracle_Corporation\" title=\"Oracle Corporation\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Oracle</a>,&nbsp;<a href=\"http://en.wikipedia.org/wiki/Newgen_Software\" title=\"Newgen Software\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Newgen OmniDocs</a>&nbsp;and&nbsp;<a href=\"http://en.wikipedia.org/wiki/SAP_AG\" title=\"SAP AG\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">SAP</a>. The standard is available for public comment at OASIS.<sup id=\"cite_ref-5\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_Management_Interoperability_Services#cite_note-5\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[5]</a></sup></p>','',NULL,NULL),(123,95,331,0,'88','',NULL,NULL),(124,95,323,0,'75','',NULL,NULL),(125,95,324,0,'80','',NULL,NULL),(126,95,320,0,'Content management system','',NULL,NULL),(127,95,321,0,'2013-04-28T00:00:00','',NULL,NULL),(128,95,322,0,'<span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">A</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">Content Management System (CMS)</b><sup id=\"cite_ref-MEC-UCS_1-0\" class=\"reference\" style=\"font-family: sans-serif; line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_management_system#cite_note-MEC-UCS-1\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[1]</a></sup><sup id=\"cite_ref-2\" class=\"reference\" style=\"font-family: sans-serif; line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_management_system#cite_note-2\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[2]</a></sup><sup id=\"cite_ref-3\" class=\"reference\" style=\"font-family: sans-serif; line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_management_system#cite_note-3\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[3]</a></sup><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">is a</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Computer_program\" title=\"Computer program\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">computer program</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">that allows</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Electronic_publishing\" title=\"Electronic publishing\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">publishing</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">,</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Editing\" title=\"Editing\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">editing</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">and modifying</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Content_(media)\" title=\"Content (media)\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">content</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">as well as maintenance from a central interface. Such systems of</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Content_management\" title=\"Content management\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">content management</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">provide procedures to manage</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Workflow\" title=\"Workflow\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">workflow</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">in a</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Collaborative_software\" title=\"Collaborative software\" style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px; text-decoration: none; color: rgb(11, 0, 128); background-image: none;\">collaborative environment</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">.</span><sup id=\"cite_ref-4\" class=\"reference\" style=\"font-family: sans-serif; line-height: 1em;\"><a href=\"http://en.wikipedia.org/wiki/Content_management_system#cite_note-4\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[4]</a></sup><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;</span><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">These procedures can be manual steps or an automated cascade.</span><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">The first content management system (CMS) was announced at the end of the 1990s. This CMS was designed to simplify the complex task of writing numerous versions of code and to make the website development process more flexible. CMS platforms allow users to centralize data editing, publishing and modification on a single back-end interface. CMS platforms are often used as&nbsp;<a href=\"http://en.wikipedia.org/wiki/Blog_software\" title=\"Blog software\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">blog software</a>.</p><br><h2 style=\"background-image: none; font-weight: normal; margin: 0px 0px 0.6em; overflow: hidden; padding-top: 0.5em; padding-bottom: 0.17em; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(170, 170, 170); font-size: 19px; font-family: sans-serif; line-height: 19.1875px;\"><span class=\"mw-headline\" id=\"Main_features\">Main features</span></h2><div class=\"rellink relarticle mainarticle\" style=\"font-style: italic; padding-left: 1.6em; margin-bottom: 0.5em; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">Main article:&nbsp;<a href=\"http://en.wikipedia.org/wiki/Comparison_of_content_management_systems\" title=\"Comparison of content management systems\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">Comparison of content management systems</a></div><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">The core function of content management systems is to present information on web sites. CMS features vary widely from system to system. Simple systems showcase a handful of features, while other releases, notably&nbsp;<a href=\"http://en.wikipedia.org/wiki/Content_management_system#Enterprise_content_management_systems\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">enterprise systems</a>, offer more complex and powerful functions. Most CMS include Web-based publishing, format management, revision control (<a href=\"http://en.wikipedia.org/wiki/Version_control\" title=\"Version control\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">version control</a>), indexing, search, and retrieval. The CMS increments the version number when new updates are added to an already-existing file. A CMS may serve as a central repository containing documents, movies, pictures, phone numbers, scientific data. CMSs can be used for storing, controlling, revising, semantically enriching and publishing documentation.</p>','',NULL,NULL),(129,96,331,0,'87','',NULL,NULL),(130,96,323,0,'76','',NULL,NULL),(131,96,320,0,'Document management system','',NULL,NULL),(132,96,321,0,'2013-04-28T00:00:00','',NULL,NULL),(133,96,322,0,'<span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">A&nbsp;</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">document management system</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(DMS) is a&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Computer_system\" title=\"Computer system\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">computer system</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(or set of computer programs) used to track and store&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Electronic_document\" title=\"Electronic document\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">electronic documents</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">. It is usually also capable of keeping track of the different versions modified by different users (history tracking). The term has some overlap with the concepts of&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Content_management_system\" title=\"Content management system\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">content management systems</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">. It is often viewed as a component of&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Enterprise_content_management\" title=\"Enterprise content management\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">enterprise content management</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(ECM) systems and related to&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Digital_asset_management\" title=\"Digital asset management\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">digital asset management</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">,&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Document_imaging\" title=\"Document imaging\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">document imaging</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">,&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Workflow\" title=\"Workflow\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">workflow</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;systems and&nbsp;</span><a href=\"https://en.wikipedia.org/wiki/Records_management\" title=\"Records management\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">records management</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;systems.</span><br><br><h2 style=\"background-image: none; font-weight: normal; margin: 0px 0px 0.6em; overflow: hidden; padding-top: 0.5em; padding-bottom: 0.17em; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(170, 170, 170); font-size: 19px; font-family: sans-serif; line-height: 19.1875px;\"><span class=\"mw-headline\" id=\"History\">History</span></h2><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">Beginning in the late 1970s, a number of vendors began developing software systems to manage paper-based documents. These systems dealt with paper documents, which included not only printed and published documents, but also photographs, prints, etc.</p><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">Later developers began to write a second type of system which could manage electronic documents, i.e., all those documents, or files, created on computers, and often stored on users\' local&nbsp;<a href=\"https://en.wikipedia.org/wiki/File-system\" title=\"File-system\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">file-systems</a>. The earliest electronic document management (EDM) systems managed either proprietary file types, or a limited number of&nbsp;<a href=\"https://en.wikipedia.org/wiki/File_format\" title=\"File format\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">file formats</a>. Many of these systems later<sup class=\"noprint Inline-Template\" style=\"line-height: 1em; white-space: nowrap;\">[<i><a href=\"https://en.wikipedia.org/wiki/Wikipedia:Manual_of_Style/Dates_and_numbers#Chronological_items\" title=\"Wikipedia:Manual of Style/Dates and numbers\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\"><span title=\"The time period mentioned near this tag is ambiguous. (February 2011)\">when?</span></a></i>]</sup>&nbsp;became known as&nbsp;<a href=\"https://en.wikipedia.org/wiki/Document_imaging\" title=\"Document imaging\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">document imaging</a>&nbsp;systems, because they focused on the capture, storage, indexing and retrieval of&nbsp;<a href=\"https://en.wikipedia.org/wiki/Image_file_formats\" title=\"Image file formats\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">image file formats</a>. EDM systems evolved to a point where systems could manage any type of file format that could be stored on the network. The applications grew to encompass electronic documents,<a href=\"https://en.wikipedia.org/wiki/Collaboration_tool\" title=\"Collaboration tool\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">collaboration tools</a>, security, workflow, and&nbsp;<a href=\"https://en.wikipedia.org/wiki/Audit\" title=\"Audit\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">auditing</a>&nbsp;capabilities.</p><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">These systems enabled an organization to capture faxes and forms, to save copies of the documents as images, and to store the image files in the&nbsp;<a href=\"https://en.wikipedia.org/wiki/Information_repository\" title=\"Information repository\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">repository</a>&nbsp;for security and quick retrieval (retrieval made possible because the system handled the extraction of the text from the document in the process of capture, and the text-indexer function provided<a href=\"https://en.wikipedia.org/wiki/Text_retrieval\" title=\"Text retrieval\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">text-retrieval</a>&nbsp;capabilities).</p><p style=\"margin: 0.4em 0px 0.5em; line-height: 19.1875px; font-family: sans-serif; font-size: 13px;\">While many EDM systems store documents in their native file format (Microsoft Word or Excel, PDF), some web-based document management systems are beginning to store content in the form of&nbsp;<a href=\"https://en.wikipedia.org/wiki/Html\" title=\"Html\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; background-position: initial initial; background-repeat: initial initial;\">html</a>. These policy management systems<sup id=\"cite_ref-1\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"https://en.wikipedia.org/wiki/Document_management_system#cite_note-1\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[1]</a></sup>&nbsp;require content to be imported into the system. However, once content is imported, the software acts like a search engine so users can find what they are looking for faster. The html format allows for better application of search capabilities such as full-text searching and stemming.<sup id=\"cite_ref-2\" class=\"reference\" style=\"line-height: 1em;\"><a href=\"https://en.wikipedia.org/wiki/Document_management_system#cite_note-2\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; white-space: nowrap; background-position: initial initial; background-repeat: initial initial;\">[2]</a></sup></p>','',NULL,NULL),(134,97,328,0,'Milan','',NULL,NULL),(135,97,330,0,'2013-04-28T00:00:00','',NULL,NULL),(136,98,328,0,'Rome','',NULL,NULL),(137,98,330,0,'2013-04-28T00:00:00','',NULL,NULL),(138,99,328,0,'Kiev','',NULL,NULL),(139,99,330,0,'2013-04-28T00:00:00','',NULL,NULL),(140,100,328,0,'Kharkov','',NULL,NULL),(141,100,330,0,'2013-04-28T00:00:00','',NULL,NULL),(144,96,324,0,'98','',NULL,NULL),(154,101,331,0,'86','',NULL,NULL),(155,101,323,0,'78','',NULL,NULL),(156,101,324,0,'100','',NULL,NULL),(157,101,320,0,'Object-relational database','',NULL,NULL),(158,101,321,0,'2013-04-28T00:00:00','',NULL,NULL),(159,101,322,0,'<span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">An&nbsp;</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">object-relational database</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">ORD</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">), or&nbsp;</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">object-relational database management system</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(</span><b style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">ORDBMS</b><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">), is a&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Database_management_system\" title=\"Database management system\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">database management system</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(DBMS) similar to a&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Relational_database\" title=\"Relational database\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">relational database</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">, but with an&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Object-oriented\" title=\"Object-oriented\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">object-oriented</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;database model: objects, classes and inheritance are directly supported in&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Database_schema\" title=\"Database schema\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">database schemas</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;and in the&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Query_language\" title=\"Query language\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">query language</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">. In addition, just as with proper relational systems, it supports extension of the&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Data_model\" title=\"Data model\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">data model</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;with custom&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Data_type\" title=\"Data type\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">data-types</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;and&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/Method_(computer_science)\" title=\"Method (computer science)\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">methods</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">.</span><br><br><br><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">An object-relational database can be said to provide a middle ground between relational databases and&nbsp;</span><i style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">object-oriented databases</i><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;(</span><a href=\"http://en.wikipedia.org/wiki/OODBMS\" title=\"OODBMS\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">OODBMS</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">). In object-relational databases, the approach is essentially that of relational databases: the data resides in the database and is manipulated collectively with queries in a query language; at the other extreme are OODBMSes in which the database is essentially a persistent object store for software written in an</span><a href=\"http://en.wikipedia.org/wiki/Object-oriented_programming_language\" title=\"Object-oriented programming language\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">object-oriented programming language</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">, with a programming&nbsp;</span><a href=\"http://en.wikipedia.org/wiki/API\" title=\"API\" class=\"mw-redirect\" style=\"text-decoration: none; color: rgb(11, 0, 128); background-image: none; font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">API</a><span style=\"font-family: sans-serif; font-size: 13px; line-height: 19.1875px;\">&nbsp;for storing and retrieving objects, and little or no specific support for querying.</span>','',NULL,NULL);

/*Table structure for table `objects_duplicates` */

DROP TABLE IF EXISTS `objects_duplicates`;

CREATE TABLE `objects_duplicates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `object_id` bigint(20) unsigned NOT NULL,
  `field_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_objects_duplicates__field_id` (`field_id`),
  KEY `FK_objects_duplicates__object_id` (`object_id`),
  KEY `FK_objects_duplicates__pid` (`pid`),
  CONSTRAINT `FK_objects_duplicates__field_id` FOREIGN KEY (`field_id`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `objects_duplicates` */

/*Table structure for table `tags` */

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `old_id` varchar(64) DEFAULT NULL,
  `old_group_id` int(10) unsigned DEFAULT NULL,
  `pid` int(11) unsigned DEFAULT NULL,
  `l1` varchar(200) DEFAULT NULL,
  `l2` varchar(200) DEFAULT NULL,
  `l3` varchar(200) DEFAULT NULL,
  `l4` varchar(200) DEFAULT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1=tag else = folder',
  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(1) DEFAULT NULL,
  `iconCls` varchar(50) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_old_id__group_id` (`old_id`,`old_group_id`),
  KEY `FK_tags__pid` (`pid`),
  KEY `FK_tags__user_id` (`user_id`),
  CONSTRAINT `FK_tags__pid` FOREIGN KEY (`pid`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tags__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8;

/*Data for the table `tags` */

insert  into `tags`(`id`,`old_id`,`old_group_id`,`pid`,`l1`,`l2`,`l3`,`l4`,`type`,`order`,`hidden`,`iconCls`,`user_id`,`group_id`) values (1,NULL,NULL,NULL,'Administrator','Administrator','Administrator','Administrator',1,33,NULL,'icon-user-m',NULL,9),(28,NULL,NULL,NULL,'System folders','System folders',NULL,NULL,0,36,NULL,NULL,NULL,NULL),(29,NULL,NULL,28,'Folder 1','Folder 1',NULL,NULL,1,1,NULL,NULL,NULL,NULL),(30,NULL,NULL,28,'Folder 2','Folder 2',NULL,NULL,1,2,NULL,NULL,NULL,NULL),(32,NULL,NULL,NULL,'Demo User2','Demo User2','Демо Пользователь2',NULL,1,0,NULL,'icon-user-m',NULL,9),(33,NULL,NULL,NULL,'Keywords','Keywords',NULL,NULL,0,38,NULL,NULL,NULL,NULL),(34,NULL,NULL,NULL,'Descriptors','Descriptors',NULL,NULL,0,39,NULL,NULL,NULL,NULL),(67,NULL,NULL,NULL,'Demo User1','Demo User1','Демо Пользователь1',NULL,1,0,NULL,'icon-user-m',NULL,9),(69,NULL,NULL,NULL,'Task categories','Task categories',NULL,NULL,0,42,NULL,NULL,NULL,NULL),(70,NULL,NULL,69,'Regional','Regional',NULL,NULL,1,1,NULL,'task-blue',NULL,NULL),(71,NULL,NULL,69,'International','International',NULL,NULL,1,2,NULL,'task-orange',NULL,NULL),(73,NULL,NULL,NULL,'SYSTEM','SYSTÈME','СИСТЕМА',NULL,1,0,NULL,NULL,NULL,9),(74,NULL,NULL,NULL,'Everyone','Tous','Все',NULL,1,0,NULL,NULL,NULL,9),(75,NULL,NULL,NULL,'Administrators','Administrateurs','Администраторы',NULL,1,0,NULL,NULL,NULL,9),(76,NULL,NULL,NULL,'Managers','Gestionnaires','Менеджеры',NULL,1,0,NULL,NULL,NULL,9),(77,NULL,NULL,NULL,'Lawyers','Avocats','Адвокаты',NULL,1,0,NULL,NULL,NULL,9);

/*Table structure for table `tasks` */

DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `case_id` bigint(20) unsigned DEFAULT NULL,
  `object_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(250) NOT NULL,
  `date_start` datetime DEFAULT NULL COMMENT 'used for events',
  `date_end` datetime DEFAULT NULL,
  `has_deadline` tinyint(1) NOT NULL DEFAULT '0',
  `allday` tinyint(1) NOT NULL DEFAULT '1',
  `importance` tinyint(3) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'for tasks: 0-internal, 1-external. For events: 2',
  `privacy` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-public, 1-private',
  `responsible_party_id` int(11) unsigned DEFAULT NULL,
  `responsible_user_ids` varchar(100) NOT NULL,
  `autoclose` tinyint(1) DEFAULT '1',
  `description` text,
  `parent_ids` varchar(100) DEFAULT NULL,
  `child_ids` varchar(100) DEFAULT NULL,
  `time` char(5) DEFAULT NULL,
  `reminds` varchar(250) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1 Overdue 2 Active 3 Completed 4 Pending',
  `missed` tinyint(1) unsigned DEFAULT NULL,
  `completed` timestamp NULL DEFAULT NULL COMMENT 'completed date (will be set automaticly, when all responsible users mark task as completed or the owner can close the task manually )',
  `updated` tinyint(1) NOT NULL DEFAULT '1',
  `cid` int(11) unsigned NOT NULL DEFAULT '1',
  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(11) unsigned DEFAULT '1',
  `udate` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `FK_tasks_user_id` (`responsible_user_ids`),
  KEY `IDX_status` (`status`),
  KEY `idx_type__status__has_deadline` (`has_deadline`,`type`,`status`),
  KEY `FK_tasks__case_id` (`case_id`),
  KEY `FK_tasks__cid` (`cid`),
  KEY `FK_tasks__object_id` (`object_id`),
  KEY `FK_tasks__uid` (`uid`),
  CONSTRAINT `FK_tasks__cid` FOREIGN KEY (`cid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks__object_id` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_tasks__uid` FOREIGN KEY (`uid`) REFERENCES `users_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tasks` */

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

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `is_folder` tinyint(1) unsigned DEFAULT '0',
  `type` enum('case','object','file','task','user','email','template') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `l1` varchar(100) DEFAULT NULL,
  `l2` varchar(100) DEFAULT NULL,
  `l3` varchar(250) NOT NULL,
  `l4` varchar(100) DEFAULT NULL,
  `order` int(11) unsigned DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `iconCls` varchar(50) DEFAULT NULL,
  `default_field` varchar(50) DEFAULT NULL,
  `cfg` text,
  `title_template` text,
  `info_template` text,
  PRIMARY KEY (`id`),
  KEY `FK_templates__pid` (`pid`),
  CONSTRAINT `FK_templates__pid` FOREIGN KEY (`pid`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

/*Data for the table `templates` */

insert  into `templates`(`id`,`pid`,`is_folder`,`type`,`name`,`l1`,`l2`,`l3`,`l4`,`order`,`visible`,`iconCls`,`default_field`,`cfg`,`title_template`,`info_template`) values (2,NULL,1,'','system','System','System','Система',NULL,2,1,'icon-folder',NULL,NULL,NULL,NULL),(4,NULL,0,'template','templatesProperies','Template for editing template properties','Template for editing template properties','Template for editing template properties',NULL,3,1,NULL,NULL,NULL,NULL,NULL),(10,2,0,'user','user','User','Utilisateur','Пользователь',NULL,1,1,'icon-user',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(11,2,0,'email','email','Email','Email','Email',NULL,2,1,'icon-mail',NULL,'{\"files\":1,\"main_file\":\"1\"}',NULL,NULL),(16,2,0,'task','tasks','Task','Task','Task','Task',3,1,'icon-task',NULL,'{\"data\":{\"type\":6}}','{name}',NULL),(17,2,0,'task','event','Event','Event','Event','Event',4,1,'icon-event',NULL,'{\"data\":{\"type\":7}}','{name}',NULL),(18,2,0,'object','folder','Folder','Folder','Folder','Folder',5,1,'icon-folder',NULL,'{\"createMethod\":\"inline\"}','{name}',NULL),(19,2,0,'file','file_template','File','File','File','File',6,1,'file-',NULL,NULL,'{name}',NULL),(20,NULL,0,'object',NULL,'Article','Статья','',NULL,0,1,'icon-document-text',NULL,'[]',NULL,NULL),(21,NULL,0,'object',NULL,'Country','Страна','',NULL,1,1,'icon-blog-blue',NULL,'[]',NULL,NULL),(22,NULL,0,'object',NULL,'Author','Автор','',NULL,0,1,'icon-object6',NULL,'[]',NULL,NULL),(23,NULL,0,'object',NULL,'City','Город','',NULL,0,1,'icon-document-medium',NULL,'[]',NULL,NULL);

/*Table structure for table `templates_structure` */

DROP TABLE IF EXISTS `templates_structure`;

CREATE TABLE `templates_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `template_id` int(11) unsigned NOT NULL,
  `tag` varchar(30) DEFAULT NULL,
  `level` smallint(6) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `l1` varchar(100) DEFAULT NULL,
  `l2` varchar(100) DEFAULT NULL,
  `l3` varchar(250) DEFAULT NULL,
  `l4` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL COMMENT 'varchar,date,time,int,bool,text,combo,popup_list',
  `order` smallint(6) unsigned NOT NULL DEFAULT '0',
  `cfg` text,
  `solr_column_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_id__name` (`template_id`,`name`),
  KEY `templates_structure_pid` (`pid`),
  KEY `templates_structure_template_id` (`template_id`),
  KEY `idx_templates_structure_type` (`type`),
  CONSTRAINT `FK_templates_structure__pid` FOREIGN KEY (`pid`) REFERENCES `templates_structure` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_templates_structure__template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=332 DEFAULT CHARSET=utf8;

/*Data for the table `templates_structure` */

insert  into `templates_structure`(`id`,`pid`,`template_id`,`tag`,`level`,`name`,`l1`,`l2`,`l3`,`l4`,`type`,`order`,`cfg`,`solr_column_name`) values (221,NULL,10,'f',0,'l1','Full name (en)','Nom complet (en)','Полное имя (en)',NULL,'varchar',1,NULL,NULL),(222,NULL,10,'f',0,'l2','Full name (fr)','Nom complet (fr)','Полное имя (fr)',NULL,'varchar',2,NULL,NULL),(223,NULL,10,'f',0,'l3','Full name (ru)','Nom complet (ru)','Полное имя (ru)',NULL,'varchar',3,NULL,NULL),(224,NULL,10,'f',0,'initials','Initials','Initiales','Инициалы',NULL,'varchar',4,NULL,NULL),(225,NULL,10,'f',0,'sex','Sex','Sexe','Пол',NULL,'_sex',5,NULL,NULL),(226,NULL,10,'f',0,'title_en','Title (en)','Titre (en)','Должность (en)',NULL,'varchar',6,NULL,NULL),(227,NULL,10,'f',0,'title_fr','Title (fr)','Titre (fr)','Должность (fr)',NULL,'varchar',7,NULL,NULL),(228,NULL,10,'f',0,'title_ru','Title (ru)','Titre (ru)','Должность (ru)',NULL,'varchar',8,NULL,NULL),(229,NULL,10,'f',0,'email','E-mail','E-mail','E-mail',NULL,'varchar',10,'{\"maxInstances\":\"3\"}',NULL),(230,NULL,10,'f',0,'language_id','Language','Langue','Язык',NULL,'_language',12,NULL,NULL),(231,NULL,10,'f',0,'short_date_format','Date format','Format de date','Формат даты',NULL,'_short_date_format',13,NULL,NULL),(232,NULL,10,'f',0,'description','Description','Description','Примечание',NULL,'varchar',14,NULL,NULL),(233,NULL,10,'f',0,'room','Room','Salle','Кабинет',NULL,'varchar',9,NULL,NULL),(234,NULL,10,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',11,'{\"maxInstances\":\"10\"}',NULL),(267,NULL,4,'f',0,'iconCls','Icon class','Icon class','Icon class',NULL,'iconcombo',3,NULL,NULL),(268,NULL,4,'f',0,'default_field','Default field','Default field','Default field',NULL,'fieldscombo',4,NULL,NULL),(269,NULL,4,'f',0,'gridJsClass','JavaScript grid class','JavaScript grid class','JavaScript grid class',NULL,'jsclasscombo',2,NULL,NULL),(270,NULL,4,'f',0,'visible','Active','Active','Active',NULL,'checkbox',1,NULL,NULL),(271,NULL,4,'f',0,'files','Files','Files','Files',NULL,'checkbox',5,NULL,NULL),(272,NULL,4,'f',0,'type','Type','Type','Type','Type','_templateTypesCombo',0,NULL,NULL),(273,NULL,11,'f',0,'_title','Subject','Sujet','Название',NULL,'varchar',0,'{\"showIn\": \"top\"}',NULL),(274,NULL,11,'f',0,'_date_start','Date','Date','Дата',NULL,'date',1,'{\"showIn\": \"top\"}','date_start'),(275,NULL,11,'f',0,'from','From','D\'après','От',NULL,'varchar',3,'{\"thesauriId\":\"73\"}','strings'),(276,NULL,11,'f',0,'_content','Content','Teneur','Содержание',NULL,'html',1,'{\"showIn\": \"tabsheet\"}','texts'),(320,NULL,20,'f',0,'_title','Title','Название',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(321,NULL,20,'f',0,'_date_start','Date','Дата',NULL,NULL,'date',2,'{\"showIn\": \"top\"}',NULL),(322,NULL,20,'f',0,'content','Content','Содержание',NULL,NULL,'html',0,'{\"showIn\": \"tabsheet\"}',NULL),(323,NULL,20,'f',0,'country','Country','Страна',NULL,NULL,'_objects',5,'{\"source\": \"tree\", \"scope\": \"24\", \"templates\": [21],\"faceting\": true}',NULL),(324,323,20,'f',1,'city','City','Город',NULL,NULL,'_objects',6,'{\"source\": \"tree\", \"scope\": \"dependent\", \"templates\": [23], \"dependency\": {}}',NULL),(325,NULL,21,'f',0,'_title','Title','Название',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(326,NULL,21,'f',0,'_date_start','Date','Дата',NULL,NULL,'date',2,'{\"showIn\": \"top\"}',NULL),(327,NULL,22,'f',0,'_title','Title','Название',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(328,NULL,23,'f',0,'_title','Title','Название',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(329,NULL,22,'f',0,'_date_start','Date','Дата',NULL,NULL,'date',2,'{\"showIn\": \"top\"}',NULL),(330,NULL,23,'f',0,'_date_start','Date','Дата',NULL,NULL,'date',2,'{\"showIn\": \"top\"}',NULL),(331,NULL,20,'f',0,'author','Author','Автор',NULL,NULL,'_objects',3,'{\"source\": \"tree\", \"scope\": \"73\", \"descendants\": true, \"templates\": [22],\"faceting\": true}',NULL);

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
  `old_id` int(10) unsigned DEFAULT NULL,
  `pid` bigint(20) unsigned DEFAULT NULL,
  `case_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(20) unsigned DEFAULT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL,
  `subtype` smallint(5) unsigned NOT NULL DEFAULT '0',
  `template_id` int(10) unsigned DEFAULT NULL,
  `tag_id` int(10) unsigned DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
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
  UNIQUE KEY `UNQ_pid_system_type_subtype_tag_id` (`pid`,`system`,`type`,`subtype`,`tag_id`),
  KEY `tree_tag_id` (`tag_id`),
  KEY `tree_pid` (`pid`),
  KEY `tree_updated` (`updated`),
  KEY `IDX_tree_date__date_end` (`date`,`date_end`),
  CONSTRAINT `tree_pid` FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;

/*Data for the table `tree` */

insert  into `tree`(`id`,`old_id`,`pid`,`case_id`,`user_id`,`system`,`type`,`subtype`,`template_id`,`tag_id`,`target_id`,`name`,`date`,`date_end`,`size`,`is_main`,`cfg`,`inherit_acl`,`cid`,`cdate`,`uid`,`udate`,`updated`,`oid`,`did`,`ddate`,`dstatus`) values (1,NULL,NULL,NULL,NULL,1,1,0,18,NULL,NULL,'Home',NULL,NULL,NULL,1,NULL,1,1,'2012-11-17 12:22:37',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(2,NULL,NULL,NULL,1,1,1,2,18,NULL,NULL,'[Favorites]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(3,NULL,2,NULL,1,1,1,1,18,NULL,NULL,'[Recent]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(4,NULL,NULL,NULL,1,1,1,3,18,NULL,NULL,'[MyCaseBox]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(5,NULL,4,NULL,1,1,1,4,18,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(6,NULL,4,NULL,1,1,1,5,18,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(7,NULL,6,NULL,1,1,1,1,18,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(8,NULL,6,NULL,1,1,1,1,18,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(9,NULL,6,NULL,1,1,1,1,18,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(10,NULL,4,NULL,1,1,1,6,18,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(11,NULL,10,NULL,1,1,1,1,18,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(12,NULL,10,NULL,1,1,1,1,18,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(13,NULL,4,NULL,1,1,1,7,18,NULL,NULL,'[PrivateArea]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(14,NULL,NULL,NULL,NULL,1,1,8,18,NULL,NULL,'Casebox',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(15,NULL,14,NULL,NULL,1,1,4,18,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(16,NULL,14,NULL,NULL,1,1,5,18,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(17,NULL,16,NULL,NULL,1,1,1,18,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(18,NULL,16,NULL,NULL,1,1,1,18,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(19,NULL,16,NULL,NULL,1,1,1,18,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(20,NULL,14,NULL,NULL,1,1,6,18,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(21,NULL,20,NULL,NULL,1,1,1,18,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(22,NULL,20,NULL,NULL,1,1,1,18,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2012-12-02 20:36:44',NULL,'2013-03-21 14:07:36',0,NULL,NULL,NULL,0),(23,NULL,1,NULL,1,0,1,0,18,NULL,NULL,'Articles',NULL,NULL,NULL,NULL,NULL,1,1,'2013-01-28 09:48:01',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(24,NULL,1,NULL,1,0,1,0,18,NULL,NULL,'Countries',NULL,NULL,NULL,NULL,NULL,1,1,'2013-01-28 09:48:18',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(63,NULL,23,NULL,1,0,1,0,18,NULL,NULL,'2012',NULL,NULL,NULL,NULL,NULL,1,1,'2013-01-31 09:49:36',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(64,NULL,23,NULL,1,0,1,0,18,NULL,NULL,'2011',NULL,NULL,NULL,NULL,NULL,1,1,'2013-01-31 09:49:42',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(73,NULL,1,NULL,NULL,0,1,0,18,NULL,NULL,'Authors',NULL,NULL,NULL,NULL,NULL,1,1,'2013-02-05 09:27:20',1,'2013-03-21 14:07:36',0,1,NULL,NULL,0),(74,NULL,2,NULL,1,1,1,3,18,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2013-04-27 19:28:09',NULL,NULL,0,NULL,NULL,NULL,0),(75,NULL,24,NULL,NULL,0,4,0,21,NULL,NULL,'Switzerland','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:55:36',NULL,NULL,0,1,NULL,NULL,0),(76,NULL,24,NULL,NULL,0,4,0,21,NULL,NULL,'Italy','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:55:44',NULL,NULL,0,1,NULL,NULL,0),(77,NULL,24,NULL,NULL,0,4,0,21,NULL,NULL,'Russia','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:55:53',NULL,NULL,0,1,NULL,NULL,0),(78,NULL,24,NULL,NULL,0,4,0,21,NULL,NULL,'Ukraine','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:56:01',NULL,NULL,0,1,NULL,NULL,0),(79,NULL,75,NULL,NULL,0,4,0,23,NULL,NULL,'Geneva','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:56:52',NULL,NULL,0,1,NULL,NULL,0),(80,NULL,75,NULL,NULL,0,4,0,23,NULL,NULL,'Bern','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:56:59',NULL,NULL,0,1,NULL,NULL,0),(81,NULL,77,NULL,NULL,0,4,0,23,NULL,NULL,'Moscow','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:57:17',NULL,NULL,0,1,NULL,NULL,0),(82,NULL,77,NULL,NULL,0,4,0,23,NULL,NULL,'Nizny-Novgorod','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:57:30',NULL,NULL,0,1,NULL,NULL,0),(83,NULL,73,NULL,NULL,0,4,0,18,NULL,NULL,'A','2013-04-27 20:57:44',NULL,NULL,NULL,NULL,1,1,'2013-04-27 20:57:44',NULL,NULL,0,1,NULL,NULL,0),(84,NULL,73,NULL,NULL,0,4,0,18,NULL,NULL,'B','2013-04-27 20:59:16',NULL,NULL,NULL,NULL,1,1,'2013-04-27 20:59:16',NULL,NULL,0,1,NULL,NULL,0),(85,NULL,73,NULL,NULL,0,4,0,18,NULL,NULL,'C','2013-04-27 20:59:20',NULL,NULL,NULL,NULL,1,1,'2013-04-27 20:59:20',NULL,NULL,0,1,NULL,NULL,0),(86,NULL,83,NULL,NULL,0,4,0,22,NULL,NULL,'Archie Bloomberg','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 20:59:53',NULL,NULL,0,1,NULL,NULL,0),(87,NULL,89,NULL,NULL,0,4,0,22,NULL,NULL,'Matthew Hamilton','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 21:00:08',NULL,NULL,0,1,NULL,NULL,0),(88,NULL,85,NULL,NULL,0,4,0,22,NULL,NULL,'Christopher Smith','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 21:00:33',NULL,NULL,0,1,NULL,NULL,0),(89,NULL,73,NULL,NULL,0,4,0,18,NULL,NULL,'M','2013-04-27 21:00:39',NULL,NULL,NULL,NULL,1,1,'2013-04-27 21:00:39',NULL,NULL,0,1,NULL,NULL,0),(90,NULL,85,NULL,NULL,0,4,0,22,NULL,NULL,'Carol Billings','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 21:01:33',NULL,NULL,0,1,NULL,NULL,0),(91,NULL,84,NULL,NULL,0,4,0,22,NULL,NULL,'Ben Sparks','2013-04-27 00:00:00','2013-04-27 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 21:02:07',NULL,NULL,0,1,NULL,NULL,0),(92,NULL,64,NULL,NULL,0,4,0,20,NULL,NULL,'Content Management Interoperability Services','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,1,'2013-04-27 21:10:45',3,'2013-04-29 21:27:34',0,1,NULL,NULL,0),(93,NULL,NULL,NULL,3,1,1,2,18,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2013-04-28 08:35:24',NULL,NULL,0,NULL,NULL,NULL,0),(94,NULL,93,NULL,3,1,1,3,18,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2013-04-28 08:35:24',NULL,NULL,0,NULL,NULL,NULL,0),(95,NULL,63,NULL,NULL,0,4,0,20,NULL,NULL,'Content management system','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:38:33',3,'2013-04-28 08:52:47',0,3,NULL,NULL,0),(96,NULL,63,NULL,NULL,0,4,0,20,NULL,NULL,'Document management system','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:46:07',3,'2013-04-28 08:47:57',0,3,NULL,NULL,0),(97,NULL,76,NULL,NULL,0,4,0,23,NULL,NULL,'Milan','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:46:20',NULL,NULL,0,3,NULL,NULL,0),(98,NULL,76,NULL,NULL,0,4,0,23,NULL,NULL,'Rome','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:46:31',NULL,NULL,0,3,NULL,NULL,0),(99,NULL,78,NULL,NULL,0,4,0,23,NULL,NULL,'Kiev','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:46:39',NULL,NULL,0,3,NULL,NULL,0),(100,NULL,78,NULL,NULL,0,4,0,23,NULL,NULL,'Kharkov','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:46:56',NULL,NULL,0,3,NULL,NULL,0),(101,NULL,64,NULL,NULL,0,4,0,20,NULL,NULL,'Object-relational database','2013-04-28 00:00:00','2013-04-28 00:00:00',NULL,NULL,NULL,1,3,'2013-04-28 08:50:48',3,'2013-04-28 08:51:47',0,3,NULL,NULL,0),(102,NULL,1,NULL,NULL,0,5,0,19,NULL,NULL,'hib.bat<img src=a onerror=alert(document.cookie)>','0000-00-00 00:00:00','0000-00-00 00:00:00',70,NULL,NULL,1,3,'2013-04-30 17:03:32',3,'2013-04-30 17:03:32',0,3,3,'2013-05-01 15:21:18',1),(103,NULL,1,NULL,NULL,0,5,0,19,NULL,NULL,'rest.bat','0000-00-00 00:00:00','0000-00-00 00:00:00',55,NULL,NULL,1,3,'2013-05-01 15:21:05',3,'2013-05-01 15:21:05',0,3,3,'2013-05-01 15:27:44',1),(104,NULL,1,NULL,NULL,0,5,0,19,NULL,NULL,'hib.bat','0000-00-00 00:00:00','0000-00-00 00:00:00',70,NULL,NULL,1,3,'2013-05-01 15:25:39',3,'2013-05-01 15:25:39',0,3,3,'2013-05-01 15:27:44',1),(105,NULL,1,NULL,NULL,0,5,0,19,NULL,NULL,'hib (1).bat','0000-00-00 00:00:00','0000-00-00 00:00:00',70,NULL,NULL,1,3,'2013-05-01 15:26:44',3,'2013-05-01 15:26:44',0,3,3,'2013-05-01 15:27:12',1),(106,NULL,1,NULL,NULL,0,5,0,19,NULL,NULL,'hib.bat','0000-00-00 00:00:00','0000-00-00 00:00:00',70,NULL,NULL,1,3,'2013-05-01 15:27:59',3,'2013-05-01 15:27:59',0,3,NULL,NULL,0),(107,NULL,64,NULL,NULL,0,5,0,19,NULL,NULL,'1351wordsforchildrenandadults.pdf','2013-05-12 09:02:45','2013-05-12 09:02:45',17897201,NULL,NULL,1,3,'2013-05-12 09:02:45',3,'2013-05-12 09:02:45',0,3,NULL,NULL,0),(108,NULL,64,NULL,NULL,0,5,0,19,NULL,NULL,'jquery_1_3_1440.png','2013-05-12 09:03:02','2013-05-12 09:03:02',719539,NULL,NULL,1,3,'2013-05-12 09:03:02',3,'2013-05-12 09:03:02',0,3,NULL,NULL,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=528 DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl` */

insert  into `tree_acl`(`id`,`node_id`,`user_group_id`,`allow`,`deny`,`cid`,`cdate`,`uid`,`udate`) values (1,1,6,4095,0,NULL,'2013-03-20 13:57:28',NULL,NULL),(2,1,7,4095,0,NULL,'2013-03-21 12:01:16',1,'2013-04-27 19:44:09'),(3,2,1,4095,0,NULL,'2013-04-27 19:28:09',NULL,NULL),(7,93,3,4095,0,NULL,'2013-04-28 08:35:24',NULL,NULL),(519,24,10,4095,0,3,'2013-05-07 16:20:09',3,'2013-05-07 16:20:14'),(521,24,7,0,4095,3,'2013-05-07 16:20:56',NULL,NULL);

/*Table structure for table `users_groups` */

DROP TABLE IF EXISTS `users_groups`;

CREATE TABLE `users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '1 - group, 2 - user',
  `system` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tag_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `l1` varchar(150) DEFAULT NULL,
  `l2` varchar(150) DEFAULT NULL,
  `l3` varchar(150) DEFAULT NULL,
  `l4` varchar(150) DEFAULT NULL,
  `sex` char(1) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `recover_hash` varchar(100) DEFAULT NULL,
  `language_id` smallint(6) unsigned NOT NULL DEFAULT '1',
  `short_date_format` varchar(10) DEFAULT NULL,
  `long_date_format` varchar(20) DEFAULT NULL,
  `cfg` text,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_successful` tinyint(1) DEFAULT NULL,
  `login_from_ip` varchar(40) DEFAULT NULL,
  `last_logout` timestamp NULL DEFAULT NULL,
  `last_action_time` timestamp NULL DEFAULT NULL,
  `enabled` tinyint(1) unsigned DEFAULT '1',
  `visible_in_reports` tinyint(1) unsigned DEFAULT '1',
  `deleted` tinyint(1) unsigned DEFAULT '0',
  `cid` int(11) unsigned DEFAULT NULL,
  `cdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int(11) unsigned DEFAULT NULL,
  `udate` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `did` int(11) unsigned DEFAULT NULL,
  `searchField` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_type__name` (`name`,`type`),
  KEY `IDX_recover_hash` (`recover_hash`),
  KEY `FK_users_groups__tag_id` (`tag_id`),
  KEY `FK_users_groups_language` (`language_id`),
  KEY `IDX_type` (`type`),
  CONSTRAINT `FK_users_groups__tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups` */

insert  into `users_groups`(`id`,`type`,`system`,`tag_id`,`name`,`l1`,`l2`,`l3`,`l4`,`sex`,`email`,`photo`,`password`,`recover_hash`,`language_id`,`short_date_format`,`long_date_format`,`cfg`,`last_login`,`login_successful`,`login_from_ip`,`last_logout`,`last_action_time`,`enabled`,`visible_in_reports`,`deleted`,`cid`,`cdate`,`uid`,`udate`,`did`,`searchField`) values (1,2,0,1,'root','Administrator','Administrator','Administrator','Administrator','m',NULL,NULL,'8fe8b64432d3b41f7dbc5d8024337e04',NULL,1,'%d.%m.%Y',NULL,'{\"personal_tags\":false}','2013-05-07 16:26:05',1,'|109.185.172.18|',NULL,'2013-05-07 16:26:05',1,1,0,1,'2010-12-05 23:00:32',1,'2013-03-20 12:54:49',NULL,' root Administrator Administrator Administrator Administrator  '),(2,2,0,32,'demo2','Demo User2','Demo User2','Демо Пользователь2',NULL,'m',NULL,NULL,'2fb38c012f0742a23859ab029658c133',NULL,1,NULL,NULL,NULL,'2013-03-14 14:34:50',1,'|195.131.166.187|',NULL,'2013-03-14 14:34:50',1,1,0,1,'2013-01-29 14:11:13',1,'2013-03-20 12:54:49',NULL,' demo2 Demo User2 Demo User2 Демо Пользователь2   '),(3,2,0,67,'demo1','Demo User1','Demo User1','Демо Пользователь1',NULL,'m',NULL,'3_Al_Haq.png','16e9ccc303140d6395b5757c749ebaff',NULL,1,NULL,NULL,NULL,'2013-05-13 14:32:16',1,'|109.185.172.18|',NULL,'2013-05-13 14:32:16',1,1,0,1,'2013-03-06 05:31:41',1,'2013-03-20 12:54:49',NULL,' demo1 Demo User1 Demo User1 Демо Пользователь1   '),(6,1,1,73,'system','SYSTEM','SYSTÈME','СИСТЕМА',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:06:12',NULL,' system SYSTEM SYSTÈME СИСТЕМА   '),(7,1,1,74,'everyone','Everyone','Tous','Все',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:06:13',NULL,' everyone Everyone Tous Все   '),(8,1,0,75,'Administrators','Administrators','Administrateurs','Администраторы',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:06:13',NULL,' Administrators Administrators Administrateurs Администраторы   '),(9,1,0,76,'Managers','Managers','Gestionnaires','Менеджеры',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:06:13',NULL,' Managers Managers Gestionnaires Менеджеры   '),(10,1,0,77,'Lawyers','Lawyers','Avocats','Адвокаты',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:06:13',NULL,' Lawyers Lawyers Avocats Адвокаты   ');

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

insert  into `users_groups_association`(`user_id`,`group_id`,`cid`,`cdate`,`uid`,`udate`) values (1,6,1,'2013-03-20 13:57:25',0,'2013-03-20 13:57:25'),(3,10,1,'2013-05-07 17:00:21',0,'0000-00-00 00:00:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=379 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups_data` */

insert  into `users_groups_data`(`id`,`user_id`,`field_id`,`duplicate_id`,`value`,`info`) values (326,1,221,0,'Administrator',''),(328,1,222,0,'Administrator',''),(329,1,223,0,'Administrator',''),(331,1,225,0,'m',''),(332,1,226,0,'Developer',''),(333,1,227,0,'Développeur',''),(334,1,228,0,'Разработчик',''),(337,1,230,0,'1',''),(349,1,231,0,'%d.%m.%Y',''),(351,2,221,0,'Demo User2',''),(352,2,222,0,'Demo User2',''),(354,3,221,0,'Demo User1',''),(355,3,222,0,'Demo User1',''),(364,3,223,0,'Демо Пользователь1',''),(365,3,225,0,'m',''),(368,2,223,0,'Демо Пользователь2',''),(369,2,225,0,'m','');

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

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `actions_log_ai` AFTER INSERT ON `actions_log` FOR EACH ROW BEGIN
	update users_groups set last_action_time = current_timestamp where id = NEW.user_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `files` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_ai` AFTER INSERT ON `files` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_ad` AFTER DELETE ON `files` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_content` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_content_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_content_bi` BEFORE INSERT ON `files_content` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_versions_ai` AFTER INSERT ON `files_versions` FOR EACH ROW BEGIN
	if(new.content_id is not null) THEN
		update files_content set ref_count = ref_count + 1 where id = new.content_id;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_versions_au` AFTER UPDATE ON `files_versions` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `files_versions_ad` AFTER DELETE ON `files_versions` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `objects` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `objects_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `objects_bi` BEFORE INSERT ON `objects` FOR EACH ROW BEGIN
	set new.is_active =((new.date_end is not null) && (new.date_end < now()));
	if(TRIM(NEW.custom_title) = '') THEN set new.custom_title = null; END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `objects` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `objects_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `objects_ai` AFTER INSERT ON `objects` FOR EACH ROW BEGIN
	UPDATE tree SET `name` = COALESCE(new.custom_title, new.title), `date` = COALESCE(new.date_start, new.cdate), date_end = COALESCE(new.date_end, new.date_start, new.date_start), cid = new.cid, cdate = new.cdate, uid = new.uid, udate = new.udate WHERE id = new.id;
	
	/* if object is a case template then mark all case childs as update for roles reset */
	if(select 1 from templates where id = new.template_id and `type` = 'case') THEN 
		call `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `objects` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `objects_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `objects_bu` BEFORE UPDATE ON `objects` FOR EACH ROW BEGIN
	SET NEW.is_active =((NEW.date_end IS NOT NULL) && (NEW.date_end < NOW()));
	IF(TRIM(NEW.custom_title) = '') THEN SET NEW.custom_title = NULL; END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `objects` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `objects_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `objects_au` AFTER UPDATE ON `objects` FOR EACH ROW BEGIN
	UPDATE tree 
		SET `name` = COALESCE(new.custom_title, new.title)
		,`date` = coalesce(new.date_start, new.cdate)
		,date_end = coalesce(new.date_end, new.date_start, new.date_start)
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
		WHERE id = new.id;
	
	/* if object is a case template then mark all case childs as update for roles reset */
	IF(SELECT 1 FROM templates WHERE id = new.template_id AND `type` = 'case') THEN 
		CALL `p_mark_all_childs_as_updated`(new.id, 1);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `tasks_bi` BEFORE INSERT ON `tasks` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `tasks_ai` AFTER INSERT ON `tasks` FOR EACH ROW BEGIN
	insert into tasks_responsible_users (task_id, user_id) select new.id, id from users_groups where concat(',',new.responsible_user_ids,',') like concat('%,',id,',%');
	UPDATE tree SET 
		`name` = new.title
		,`date` = coalesce(new.date_start, new.date_end, new.cdate)
		,date_end = new.date_end
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
	WHERE id = new.id;
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
	delete from tasks_responsible_users  where task_id = old.id and concat(',', new.responsible_user_ids, ',') not like concat('%,',user_id,',%');
	INSERT INTO tasks_responsible_users (task_id, user_id) SELECT new.id, u.id FROM users_groups u WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',u.id,',%')
		on duplicate key update user_id = u.id;
	UPDATE tree SET 
		`name` = new.title
		,`date` = COALESCE(new.date_start, new.date_end, new.cdate)
		,date_end = new.date_end
		,cid = new.cid
		,cdate = new.cdate
		,uid = new.uid
		,udate = new.udate
		,updated = (updated | 1)
	WHERE id = new.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `templates_structure` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `templates_structure_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `templates_structure_bi` BEFORE INSERT ON `templates_structure` FOR EACH ROW BEGIN
	if(NEW.PID is not null) THEN
	SET NEW.LEVEL = (select `level` +1 from templates_structure where id = NEW.PID);
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `templates_structure` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `templates_structure_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `templates_structure_bu` BEFORE UPDATE ON `templates_structure` FOR EACH ROW BEGIN
	IF(NEW.PID IS NOT NULL) THEN
		SET NEW.LEVEL = (SELECT `level` +1 FROM templates_structure WHERE id = NEW.PID);
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
	/* trivial check for cycles */
	
	/* set case_id field */
	if( (new.template_id is not null) and (select id from templates where id = new.template_id and `type` = 'case') ) THEN
		SET new.case_id = new.id;
	ELSE
		SET new.case_id = `f_get_objects_case_id`(new.pid);
	END IF;
	/* end of set case_id field */
	set new.oid = coalesce(new.oid, new.cid);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_bu` BEFORE UPDATE ON `tree` FOR EACH ROW BEGIN
	/* set case_id field */
	if( new.pid <> old.pid ) THEN 
		IF( (new.template_id IS NOT NULL) AND (SELECT id FROM templates WHERE id = new.template_id AND `type` = 'case') ) THEN
			SET new.case_id = new.id;
		ELSE
			SET new.case_id = `f_get_objects_case_id`(new.pid);
		END IF;
	END IF;
	/* end of set case_id field */
	
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_ai` AFTER INSERT ON `tree_acl` FOR EACH ROW BEGIN
	UPDATE tree SET updated = (10 | updated) WHERE id = new.node_id;
	CALL p_mark_all_childs_as_updated(new.node_id, 10);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_au` AFTER UPDATE ON `tree_acl` FOR EACH ROW BEGIN
	update tree set updated = (updated | 10) where id = new.node_id;
	call p_mark_all_childs_as_updated(new.node_id, 10);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'local'@'localhost' */ /*!50003 TRIGGER `tree_acl_ad` AFTER DELETE ON `tree_acl` FOR EACH ROW BEGIN
	UPDATE tree SET updated = (updated | 10) WHERE id = old.node_id;
	CALL p_mark_all_childs_as_updated(old.node_id, 10);
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_bi` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_bi` BEFORE INSERT ON `users_groups` FOR EACH ROW BEGIN
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
	INSERT INTO tags (l1, l2, l3, l4, `type`, iconCls) VALUES (new.l1, new.l2, new.l3, new.l4, 1, CASE new.sex WHEN 'f' THEN 'icon-user-f' WHEN 'm' THEN 'icon-user-m' ELSE 'icon-user' END);
	set new.tag_id = last_insert_id();
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_bu` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_bu` BEFORE UPDATE ON `users_groups` FOR EACH ROW BEGIN
	declare tmp_iconCls varchar(20) default 'icon-user';
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
	set tmp_iconCls = CASE new.sex WHEN 'f' THEN 'icon-user-f' WHEN 'm' THEN 'icon-user-m' END;
	
	INSERT INTO tags (id, l1, l2, l3, l4, `type`, iconCls) VALUES (new.tag_id, new.l1, new.l2, new.l3, new.l4, 1, tmp_iconCls)
		on duplicate key update id = last_insert_id(new.tag_id), l1 = new.l1, l2 = new.l2, l3 = new.l3, l4 = new.l4, `type` = 1, iconCls = tmp_iconCls;
	SET new.tag_id = LAST_INSERT_ID();
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_ad` AFTER DELETE ON `users_groups` FOR EACH ROW BEGIN
	delete from tags where id = old.tag_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups_data` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_data_ai` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_data_ai` AFTER INSERT ON `users_groups_data` FOR EACH ROW BEGIN
	DECLARE tmp_field_name VARCHAR(100) DEFAULT NULL;
	SELECT `name` INTO tmp_field_name FROM templates_structure WHERE id = new.field_id;
	IF(tmp_field_name = 'l1') THEN UPDATE users_groups SET l1 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'l2') THEN UPDATE users_groups SET l2 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'l3') THEN UPDATE users_groups SET l3 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'l4') THEN UPDATE users_groups SET l4 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'sex') THEN UPDATE users_groups SET sex = new.value WHERE id = new.user_id;
	-- ELSEIF(tmp_field_name = 'email') THEN UPDATE users_groups SET email = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'language_id') THEN UPDATE users_groups SET `language_id` = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'short_date_format') THEN UPDATE users_groups SET `short_date_format` = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'long_date_format') THEN UPDATE users_groups SET `long_date_format` = new.value WHERE id = new.user_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups_data` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_data_au` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_data_au` AFTER UPDATE ON `users_groups_data` FOR EACH ROW BEGIN
	DECLARE tmp_field_name VARCHAR(100) DEFAULT NULL;
	SELECT `name` INTO tmp_field_name FROM templates_structure WHERE id = new.field_id;
	if(tmp_field_name = 'l1') THEN UPDATE users_groups SET l1 = new.value WHERE id = new.user_id;
	elseIF(tmp_field_name = 'l2') THEN UPDATE users_groups SET l2 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'l3') THEN UPDATE users_groups SET l3 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'l4') THEN UPDATE users_groups SET l4 = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'sex') THEN UPDATE users_groups SET sex = new.value WHERE id = new.user_id;
	-- ELSEIF(tmp_field_name = 'email') THEN UPDATE users_groups SET email = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'language_id') THEN UPDATE users_groups SET `language_id` = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'short_date_format') THEN UPDATE users_groups SET `short_date_format` = new.value WHERE id = new.user_id;
	ELSEIF(tmp_field_name = 'long_date_format') THEN UPDATE users_groups SET `long_date_format` = new.value WHERE id = new.user_id;
	END if;
    END */$$


DELIMITER ;

/* Trigger structure for table `users_groups_data` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `users_groups_data_ad` */$$

/*!50003 CREATE */ /*!50017 DEFINER = 'webadmin'@'%' */ /*!50003 TRIGGER `users_groups_data_ad` AFTER DELETE ON `users_groups_data` FOR EACH ROW BEGIN
	DECLARE tmp_field_name VARCHAR(100) DEFAULT NULL;
	SELECT `name` INTO tmp_field_name FROM templates_structure WHERE id = old.field_id;
	IF(tmp_field_name = 'l1') THEN UPDATE users_groups SET l1 = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'l2') THEN UPDATE users_groups SET l2 = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'l3') THEN UPDATE users_groups SET l3 = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'l4') THEN UPDATE users_groups SET l4 = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'sex') THEN UPDATE users_groups SET sex = null WHERE id = old.user_id;
	-- ELSEIF(tmp_field_name = 'email') THEN UPDATE users_groups SET email = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'language_id') THEN UPDATE users_groups SET `language_id` = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'short_date_format') THEN UPDATE users_groups SET `short_date_format` = null WHERE id = old.user_id;
	ELSEIF(tmp_field_name = 'long_date_format') THEN UPDATE users_groups SET `long_date_format` = NULL WHERE id = old.user_id;
	END if;
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
	DECLARE tmp_inherit BOOL DEFAULT NULL;
	DECLARE rez text CHARSET utf8 DEFAULT '';
	
	set rez = concat('/', in_id);
	
	SELECT pid, inherit_acl INTO tmp_pid, tmp_inherit FROM tree WHERE id = in_id;
	WHILE( (tmp_pid IS NOT NULL) AND (tmp_inherit = 1) and ( INSTR(concat(rez, '/'), concat('/', tmp_pid, '/') ) =0) ) DO
		SET rez = CONCAT('/', tmp_pid, rez);
		SELECT pid, inherit_acl INTO tmp_pid, tmp_inherit FROM tree WHERE id = tmp_pid;
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

/* Procedure structure for procedure `p_mark_all_childs_as_updated` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_mark_all_childs_as_updated` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`local`@`localhost` PROCEDURE `p_mark_all_childs_as_updated`(in_id bigint unsigned, in_update_bits tinyint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_child_ids2(id BIGINT UNSIGNED);
	delete from tmp_child_ids;
	DELETE FROM tmp_child_ids2;
	insert into tmp_child_ids select id from tree where pid = in_id and dstatus = 0;
	while(ROW_COUNT() > 0)do
		update tree, tmp_child_ids 
			set tree.updated = (tree.updated | in_update_bits)
				, tree.case_id = cASE when (tree.updated && 100)= 100 THEN `f_get_objects_case_id`(tree.id) ELSE tree.case_id END
			where tmp_child_ids.id = tree.id;
		DELETE FROM tmp_child_ids2;
		insert into tmp_child_ids2 select id from tmp_child_ids;
		delete from tmp_child_ids;
		INSERT INTO tmp_child_ids SELECT t.id FROM tree t join tmp_child_ids2 c on t.pid = c.id and t.dstatus = 0;
	END WHILE;
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
	SELECT `id`, `password`  INTO `user_id`, `user_pass` FROM users_groups WHERE `name` = `in_username` and enabled = 1 and deleted = 0;
	IF(user_id IS NOT NULL) THEN 
		IF(`user_pass` = MD5(CONCAT('aero', `in_password`))) THEN 
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = 1, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 1;
		ELSE
			UPDATE users_groups SET last_login = CURRENT_TIMESTAMP, login_successful = login_successful-2, login_from_ip = `in_from_ip`  WHERE id = `user_id`;
			SELECT user_id, 0;
		END IF;
	ELSE 
		SELECT 0, 0;
	END IF;
    END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
