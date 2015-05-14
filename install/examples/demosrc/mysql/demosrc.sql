/*
SQLyog Ultimate v11.5 (64 bit)
MySQL - 5.5.9 : Database - aa_demosrc
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `action_log` */

DROP TABLE IF EXISTS `action_log`;

CREATE TABLE `action_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) unsigned NOT NULL,
  `object_pid` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','reopen','status_change','overdue','comment','move','permissions','user_delete','user_create','login','login_fail') NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` mediumtext,
  PRIMARY KEY (`id`),
  KEY `FK_action_log__object_id` (`object_id`),
  KEY `FK_action_log__object_pid` (`object_pid`),
  KEY `FK_action_log__user_id` (`user_id`),
  CONSTRAINT `FK_action_log__object_id` FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_action_log__object_pid` FOREIGN KEY (`object_pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_action_log__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Data for the table `action_log` */

insert  into `action_log`(`id`,`object_id`,`object_pid`,`user_id`,`action_type`,`action_time`,`data`) values (1,1,NULL,1,'login_fail','2015-01-12 16:32:45','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(2,1,NULL,1,'login_fail','2015-01-12 16:32:48','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(3,1,NULL,1,'login_fail','2015-01-12 16:32:52','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(4,1,NULL,1,'login','2015-01-12 16:32:57','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(5,1,NULL,1,'login_fail','2015-02-17 09:18:16','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(6,1,NULL,1,'login_fail','2015-02-17 09:18:20','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(7,1,NULL,1,'login_fail','2015-02-17 09:18:30','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(8,1,NULL,1,'login','2015-02-17 09:18:34','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}'),(9,1,NULL,1,'','2015-02-17 09:19:36','{\"name\":\"root\",\"iconCls\":\"icon-none\",\"pids\":[],\"path\":\"\",\"template_id\":null,\"case_id\":null,\"date\":null,\"size\":null,\"cid\":null,\"oid\":null,\"uid\":null,\"cdate\":null,\"udate\":null}');

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `param` varchar(50) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unq_param` (`param`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`id`,`pid`,`param`,`value`) values (1,NULL,'project_name_en','CaseBox - Demo'),(2,NULL,'project_name_ru','CaseBox - Demo'),(3,NULL,'responsible_party','232'),(4,NULL,'responsible_party_default','345'),(6,NULL,'task_categories','715'),(7,NULL,'templateIcons','\r\nicon-arrow-left-medium\r\nicon-arrow-left-medium-green\r\nicon-arrow-left\r\nicon-arrow-right-medium\r\nicon-arrow-right\r\nicon-case_card\r\nicon-complaint\r\nicon-complaint-subjects\r\nicon-info-action\r\nicon-decision\r\nicon-echr_complaint\r\nicon-echr_decision\r\nicon-petition\r\nicon-balloon\r\nicon-bell\r\nicon-blog-blue\r\nicon-blog-magenta\r\nicon-blue-document-small\r\nicon-committee-phase\r\nicon-document-medium\r\nicon-document-stamp\r\nicon-document-text\r\nicon-mail\r\nicon-object1\r\nicon-object2\r\nicon-object3\r\nicon-object4\r\nicon-object5\r\nicon-object6\r\nicon-object7\r\nicon-object8\r\nicon-zone\r\nicon-applicant\r\nicon-suspect\r\nicon-milestone'),(9,NULL,'folder_templates','24074,24079,24043,24044'),(10,NULL,'default_folder_template','24074'),(11,NULL,'default_file_template','24075'),(12,NULL,'default_task_template','24072'),(13,NULL,'default_event_template','24073'),(14,NULL,'action_templates','24195'),(16,NULL,'default_language','en'),(17,NULL,'languages','en,ru'),(18,NULL,'rootNode','{\r\n\"id\": 0\r\n,\"text\": \"My CaseBox\"\r\n}'),(19,NULL,'object_type_plugins','{\r\n  \"object\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"case\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"task\": [\"objectProperties\", \"files\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"file\": [\"thumb\", \"meta\", \"versions\", \"tasks\", \"comments\", \"systemProperties\"]\r\n}'),(21,NULL,'js','[\r\n  \"js/graph.js\"\r\n  ,\"js/init.js\"\r\n]\r\n'),(22,NULL,'api','{\r\n  \"Demosrc_Graph\": {\r\n    \"methods\": {\r\n      \"load\": {\"len\": 1}\r\n    }\r\n  }\r\n}\r\n'),(24,NULL,'treeNodes','{\r\n    \"MyCalendar\": {}\r\n\r\n    ,\"ManagersCalendar\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\ManagersCalendar\" \r\n    }\r\n    ,\"Tasks\": {}\r\n\r\n  ,\"RecentActivity\": {\r\n    \"pid\": 1\r\n    ,\"includeTemplates\": []\r\n    ,\"excludeTemplates\": [13116]\r\n\r\n    ,\"commented_DC\": {\r\n      \"name\": {}\r\n      ,\"comment_user_id\": {}\r\n      ,\"comment_date\": {}\r\n    }\r\n    ,\"modified_DC\": {\r\n      \"name\": {}\r\n      ,\"uid\": {}\r\n      ,\"udate\": {}\r\n    }\r\n    ,\"added_DC\": {\r\n      \"name\": {}\r\n      ,\"cid\": {}\r\n      ,\"cdate\": {}\r\n    }\r\n\r\n    ,\"commented_DC_sort\": {\r\n      \"property\": \"comment_date\"\r\n      ,\"direction\": \"DESC\"\r\n    }\r\n    ,\"modified_DC_sort\": {\r\n      \"property\": \"udate\"\r\n      ,\"direction\": \"DESC\"\r\n    }\r\n    ,\"added_DC_sort\": {\r\n      \"property\": \"cdate\"\r\n      ,\"direction\": \"DESC\"\r\n    }\r\n  }\r\n\r\n    ,\"TasksForCase\": {}\r\n\r\n    ,\"CasesGrouped\": {\r\n    }\r\n\r\n    ,\"task_offices\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\Offices\" \r\n    }\r\n\r\n    ,\"task_office_users\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\OfficeUsers\" \r\n    }\r\n\r\n    ,\"office_cases\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\OfficeCases\" \r\n    }\r\n\r\n    ,\"task_types\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\TaskTypes\" \r\n    }\r\n\r\n    ,\"task_statuses\": {\r\n       \"class\": \"Demosrc\\\\TreeNode\\\\TaskStatuses\" \r\n    }\r\n\r\n    ,\"RealSubnode\":{\r\n       \"pid\": \"0\"\r\n       ,\"realNodeId\": \"root\"\r\n       ,\"title\": \"All Folders\"\r\n    }\r\n\r\n    ,\"Dbnode\":{}\r\n\r\n    ,\"ActionLog\":{\r\n      \"pid\": 0\r\n    }\r\n\r\n    ,\"RecycleBin\":{\r\n      \"pid\": 0\r\n      ,\"facets\": [\"did\"]\r\n      ,\"DC\": {\r\n        \"name\": {\r\n          \"solr_column_name\": \"name\"\r\n        }\r\n        ,\"date\": {\r\n          \"solr_column_name\": \"date\"\r\n        }\r\n        ,\"size\": {\r\n          \"solr_column_name\": \"size\"\r\n        }\r\n        ,\"deleted_by\": {\r\n          \"title\": \"Deleted by\"\r\n          ,\"solr_column_name\": \"did\"\r\n        }\r\n      }\r\n    }\r\n}'),(25,NULL,'listeners','{\r\n  \"beforeNodeDbCreate\": {\r\n    \"Demosrc_Custom\": [\"beforeNodeDbCreate\"]\r\n  }\r\n  ,\"beforeNodeDbUpdate\": {\r\n    \"Demosrc_Custom\": [\"beforeNodeDbCreate\"]\r\n  }\r\n  ,\"treeInitialize\": {\r\n    \"Demosrc_Tree\": [\"onTreeInitialize\"]\r\n  }\r\n}');

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

insert  into `crons`(`id`,`cron_id`,`cron_file`,`last_start_time`,`last_end_time`,`execution_info`,`last_action`) values (1,'solr_update_tree','D:\\devel\\www\\cb2\\casebox\\crons\\cron_solr_update_tree.php','2015-02-17 09:17:54','2015-02-17 09:17:54','ok','2015-05-12 13:11:14'),(2,'send_log_notifications','/var/www/casebox/casebox/crons/cron_send_log_notifications.php','2013-04-22 09:14:31','2013-04-22 09:14:31','ok','0000-00-00 00:00:00'),(3,'extract_file_contents','/var/www/casebox/casebox/crons/cron_extracting_file_contents.php','2013-07-12 10:54:03','2013-07-12 10:54:03','{\"Total\":0,\"Processed\":0,\"Not found\":0,\"Processed List\":[],\"Not found List\":[]}','0000-00-00 00:00:00'),(4,'check_core_email','/var/www/casebox/casebox/crons/cron_check_cores_mail.php','2013-04-20 18:20:01','2013-04-20 18:20:02','ok','0000-00-00 00:00:00'),(5,'check_deadlines','/var/www/casebox/casebox/crons/cron_check_deadlines.php','2014-05-19 15:39:13','2014-05-19 15:39:13','ok','2014-05-19 15:39:13'),(6,'test','/var/www/casebox/casebox/crons/test_mail_format.php','2013-01-24 09:14:53','2013-01-24 09:14:53','ok','0000-00-00 00:00:00'),(7,'send_notifications','/var/www/casebox/sys/crons/cron_send_notifications.php','2014-05-19 15:40:23','2014-05-19 15:40:23','ok','2014-05-19 15:40:23'),(8,'extract_files_content','/var/www/casebox/sys/crons/cron_extract_files_content.php','2014-05-19 15:40:35','2014-05-19 15:40:35','{\"Total\":0,\"Processed\":0,\"Not found\":0,\"Processed List\":[],\"Not found List\":[]}','2014-05-19 15:40:35');

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

insert  into `file_previews`(`id`,`group`,`status`,`filename`,`size`,`cdate`,`ladate`) values (17547,'office',0,'17547_.html',54509,'2014-02-28 19:11:22',NULL),(17549,'office',0,'17549_.html',80375,'2014-02-28 19:50:20',NULL),(17553,'office',0,'17553_.html',804,'2014-03-02 21:27:44',NULL),(17555,'office',0,'17555_.html',3292,'2014-03-02 21:36:24',NULL),(17565,'office',0,'17565_.html',100,'2014-03-02 23:26:18',NULL),(17566,'office',0,'17566_.html',108,'2014-03-02 23:40:26',NULL),(17572,'office',0,'17572_.html',218,'2014-03-03 03:19:50',NULL),(17574,'office',0,'17574_.html',155,'2014-03-03 04:01:00',NULL),(17575,'office',0,'17575_.html',115,'2014-03-03 04:01:18',NULL);

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
  `udate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `FK_files__content_id` (`content_id`),
  CONSTRAINT `FK_files__content_id` FOREIGN KEY (`content_id`) REFERENCES `files_content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_files__id` FOREIGN KEY (`id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25225 DEFAULT CHARSET=utf8;

/*Data for the table `files` */

insert  into `files`(`id`,`content_id`,`date`,`name`,`title`,`cid`,`uid`,`cdate`,`udate`) values (25136,17550,NULL,'Death Penalty in the OSCE Area - Background Paper.pdf','',269,269,'2014-03-02 21:18:37','2014-03-02 21:18:37'),(25137,17551,NULL,'1910-2009-Zhuk-v.-Belarus-Final2.pdf','',269,269,'2014-03-02 21:18:58','2014-03-02 21:18:58'),(25138,17552,NULL,'IHF Intervention to the 2006 OSCE Human Dimension Meeting.pdf','',269,269,'2014-03-02 21:20:42','2014-03-02 21:20:42'),(25148,17553,NULL,'Request for Intervention with Government of Belarus.odt','',269,269,'2014-03-02 21:27:38','2014-03-02 21:27:38'),(25150,17554,NULL,'Condemnation letter after execution.odt','',269,269,'2014-03-02 21:33:21','2014-03-02 21:33:21'),(25151,17555,NULL,'PRExecutionsBelarus_March 2010.doc','',269,269,'2014-03-02 21:36:18','2014-03-02 21:36:18'),(25152,17556,NULL,'Gelman CAse Merits and Reparations.pdf','',266,266,'2014-03-02 21:37:08','2014-03-02 21:37:08'),(25153,17556,NULL,'gelman_seriec_221_ing.pdf','',266,266,'2014-03-02 21:37:21','2014-03-02 21:37:21'),(25160,17557,NULL,'2 Executions in Belarus Draw Condemnation - NYTimes.pdf','',269,269,'2014-03-02 21:51:23','2014-03-02 21:51:23'),(25164,17558,NULL,'Courtwatch PL -Court_Monitoring_Methodology.pdf','',269,269,'2014-03-02 22:20:59','2014-03-02 22:20:59'),(25165,17559,NULL,'krawczak vs poland_eng.doc','',269,269,'2014-03-02 22:45:49','2014-03-02 22:45:49'),(25166,17560,NULL,'poplawski vs poland.doc','',269,269,'2014-03-02 22:46:11','2014-03-02 22:46:11'),(25169,17561,NULL,'Receipt for flight booking.odt','',269,269,'2014-03-02 22:57:50','2014-03-02 22:57:50'),(25177,17562,NULL,'479-2011 -EEvsRussia.pdf','',269,269,'2014-03-02 23:19:38','2014-03-02 23:19:38'),(25178,17563,NULL,'Refworld _ Widespread Torture in the Chechen Republic. Human Rights Watch Briefing Paper for the 37th Session UN Committee against Torture.pdf','',269,269,'2014-03-02 23:22:34','2014-03-02 23:22:34'),(25179,17564,NULL,'NNCAT_torture_Russia.pdf','',269,269,'2014-03-02 23:23:36','2014-03-02 23:23:36'),(25180,17567,NULL,'Written testimony.odt','',269,269,'2014-03-02 23:45:02','2014-03-02 23:45:02'),(25196,17568,NULL,'Coulson & Anor v R [2013] EWCA Crim 1026 (28 June 2013).pdf','',267,267,'2014-03-03 02:56:41','2014-03-03 02:56:41'),(25197,17569,NULL,'Investigatory Powers Act - 2000 - changes to legislation.pdf','',267,267,'2014-03-03 02:56:57','2014-03-03 02:56:57'),(25198,17570,NULL,'Investigatory Powers Act - 2000.pdf','',267,267,'2014-03-03 02:57:02','2014-03-03 02:57:02'),(25203,17571,NULL,'15-2013-youonlyclicktwice.pdf','',267,267,'2014-03-03 03:11:59','2014-03-03 03:11:59'),(25204,17573,NULL,'Complaint National Cyber Crime Unit of the National Crime Agency.odt','',266,266,'2014-03-03 03:23:16','2014-03-03 03:23:16'),(25215,17574,NULL,'Snowden Leaks.odt','',267,267,'2014-03-03 03:40:14','2014-03-03 03:40:14'),(25216,17575,NULL,'Luke Hardings book.odt','',267,267,'2014-03-03 03:43:07','2014-03-03 03:43:07'),(25217,17576,NULL,'Lawyers working on this case - security measures.ods','',267,267,'2014-03-03 03:47:16','2014-03-03 03:47:16'),(25222,17577,NULL,'GCHQ and European spy agencies worked together on mass surveillance _ UK news _ The Guardian.pdf','',267,267,'2014-03-03 04:00:47','2014-03-03 04:00:47'),(25223,17578,NULL,'Surveillance, democracy, transparency – a global view _ World news _ The Guardian.pdf','',267,267,'2014-03-03 04:00:50','2014-03-03 04:00:50'),(25224,17579,NULL,'Letter of warning.odt','',267,267,'2014-03-03 04:03:54','2014-03-03 04:03:54');

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
) ENGINE=InnoDB AUTO_INCREMENT=17580 DEFAULT CHARSET=utf8;

/*Data for the table `files_content` */

insert  into `files_content`(`id`,`size`,`pages`,`type`,`path`,`ref_count`,`parse_status`,`skip_parsing`,`md5`) values (17547,111104,NULL,'application/msword','2014/02/28',1,1,0,'568969d8214daf388a1e7bf119aa91eds111104'),(17548,154112,NULL,'application/msword','2014/02/28',1,1,0,'d9dc45a4470912cda21a00c073fac468s154112'),(17549,110592,15,'application/msword','2014/02/28',1,1,0,'28c7da4257aa630149894f4b5b0f9947s110592'),(17550,378763,NULL,'application/pdf','2014/03/02',1,1,0,'d436295388bb381615df56256e2185ebs378763'),(17551,87476,NULL,'application/pdf','2014/03/02',1,1,0,'c9d83051e3cdc92312638898b2c41584s87476'),(17552,69040,NULL,'application/pdf','2014/03/02',1,1,0,'b8646a8c806d2a8ea6b3e73ae9d16ddes69040'),(17553,15048,1,'application/vnd.oasis.opendocument.text','2014/03/02',1,1,0,'6dea98803b034d95f6d0971c283629e7s15048'),(17554,66209,5,'application/vnd.oasis.opendocument.text','2014/03/02',1,1,0,'8378ee6731605a43d544422d7d2d5c88s66209'),(17555,26112,1,'application/msword','2014/03/02',1,1,0,'0cb84e5266be4922f568531bb9c12bd8s26112'),(17556,620424,NULL,'application/pdf','2014/03/02',2,1,0,'357afad08031b19dec9eafc5d895fc63s620424'),(17557,100210,NULL,'application/pdf','2014/03/02',1,1,0,'c94dd528ebfb6f3b24a4cba6baa0f956s100210'),(17558,4544828,NULL,'application/pdf','2014/03/02',1,1,0,'0315e037558dfb84a365145ea8d8be26s4544828'),(17559,89088,12,'application/msword','2014/03/02',1,1,0,'333b6039838990a38b56078ae59074c3s89088'),(17560,96768,1,'application/msword','2014/03/02',1,1,0,'33c5115f955547f13294214c956b0b9as96768'),(17561,9687,1,'application/vnd.oasis.opendocument.text','2014/03/02',1,1,0,'acced4a970a660f9eb60f4ddae6e838es9687'),(17562,59110,NULL,'application/pdf','2014/03/02',1,1,0,'c24bb47d7548750ddb3bc4da0beae5f2s59110'),(17563,139417,NULL,'application/pdf','2014/03/02',1,1,0,'038ccdd5f228afec4efcf4a1e4974ee9s139417'),(17564,51741,NULL,'application/pdf','2014/03/02',1,1,0,'8ec09f8161bffa3ad622a5a26e65dc32s51741'),(17565,7405,1,'application/vnd.oasis.opendocument.text','2014/03/02',2,1,1,'bcf5306153530578e72ac63f552cc913s7405'),(17566,8602,1,'application/vnd.oasis.opendocument.text','2014/03/02',1,1,0,'6b8dabea4a2757732e92af313f6fa738s8602'),(17567,10836,1,'application/vnd.oasis.opendocument.text','2014/03/02',1,1,0,'99a39ab47076c6940efc771be033b5d3s10836'),(17568,65880,NULL,'application/pdf','2014/03/03',1,1,0,'1256bcd6dee7b5d77d7592fb880c3459s65880'),(17569,1639276,NULL,'application/pdf','2014/03/03',1,1,0,'6e0ddfb1fa3e100db96245e464e47b6as1639276'),(17570,1019195,NULL,'application/pdf','2014/03/03',1,1,0,'382c991203d7351ff6049302c80acb19s1019195'),(17571,933736,NULL,'application/pdf','2014/03/03',1,1,0,'50c760a1e96dd786183172bc9a5f4d9as933736'),(17572,11578,1,'application/vnd.oasis.opendocument.text','2014/03/03',1,1,0,'a7378c076349a6c87a57024bd8ea021as11578'),(17573,14516,1,'application/vnd.oasis.opendocument.text','2014/03/03',1,1,0,'d3d36500d42ca8ff0360b6ffa95986e1s14516'),(17574,10163,1,'application/vnd.oasis.opendocument.text','2014/03/03',1,1,0,'a8ded008818a7ac3fbaa4c6af3cbaef4s10163'),(17575,9202,1,'application/vnd.oasis.opendocument.text','2014/03/03',1,1,0,'e93f8bf96d6447eaaf0ebfb04eb08259s9202'),(17576,21328,NULL,'application/vnd.oasis.opendocument.spreadsheet','2014/03/03',1,1,0,'cde7053d71d71ed48c6242e59710dbd9s21328'),(17577,77194,NULL,'application/pdf','2014/03/03',1,1,0,'59102a3ef8c1521f2f490a2ef71a658cs77194'),(17578,186629,NULL,'application/pdf','2014/03/03',1,1,0,'862ba6bae9e4f13c556f9937d42b94c0s186629'),(17579,16125,1,'application/vnd.oasis.opendocument.text','2014/03/03',1,1,0,'ec97ea2005365d5927ccf4dd35704d5ds16125');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `files_versions` */

insert  into `files_versions`(`id`,`file_id`,`content_id`,`date`,`name`,`cid`,`uid`,`cdate`,`udate`) values (2,25180,17565,NULL,'Written testimony.odt',269,269,'2014-03-02 23:26:13','2014-03-02 23:26:13'),(3,25180,17566,NULL,'Written testimony.odt',240,240,'2014-03-02 23:39:50','2014-03-02 23:39:50'),(4,25204,17565,NULL,'Complaint National Cyber Crime Unit of the National Crime Agency.odt',267,267,'2014-03-03 03:14:40','2014-03-03 03:14:40'),(5,25204,17572,NULL,'Complaint National Cyber Crime Unit of the National Crime Agency.odt',267,267,'2014-03-03 03:16:03','2014-03-03 03:16:03');

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
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','reopen','status_change','overdue','comment','move','permissions','user_delete','user_create','login','login_fail') NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `object_id` bigint(20) unsigned NOT NULL,
  `object_pid` bigint(20) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_notifications__action_type__object_id__user_id` (`action_type`,`object_id`,`user_id`),
  KEY `FK_notifications__user_id` (`user_id`),
  KEY `FK_notifications__object_id` (`object_id`),
  KEY `FK_notifications__case_id` (`object_pid`),
  KEY `FK_notifications__sent` (`sent`),
  KEY `FK_notifications__viewed` (`viewed`),
  CONSTRAINT `FK_notifications__object_id` FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `notifications` */

/*Table structure for table `objects` */

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` mediumtext,
  `sys_data` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25252 DEFAULT CHARSET=utf8;

/*Data for the table `objects` */

insert  into `objects`(`id`,`data`,`sys_data`) values (1456,'{\"status\":24260,\"_title\":\"0803-Moidunov\",\"nr\":\"0803\",\"program\":\"24266\"}','[]'),(1602,'{\"_title\":\"0807-Akmatov\",\"nr\":\"0807\",\"program\":\"24266\",\"status\":24260,\"lead\":\"262\"}','[]'),(2178,'{\"status\":24260,\"_title\":\"0808-Akunov\",\"nr\":\"0808\",\"program\":\"24266\"}','[]'),(2621,'{\"status\":24260,\"_title\":\"0809-Ernazarov\",\"nr\":\"0809\",\"program\":\"24266\"}','[]'),(2943,'{\"status\":24260,\"_title\":\"0810-Gerasimov\",\"nr\":\"0810\",\"program\":\"24266\"}','[]'),(3329,'{\"status\":24260,\"_title\":\"0915-Zhovtis\",\"nr\":\"0915\",\"program\":\"24266\"}','[]'),(4129,'{\"status\":24260,\"_title\":\"1010-Askarov\",\"nr\":\"1010\",\"program\":\"24266\"}','[]'),(4886,'{\"status\":24260,\"_title\":\"1107-Andijan\",\"nr\":\"1107\",\"program\":\"24266\"}','[]'),(5188,'{\"status\":24260,\"_title\":\"1113-Khadjev (Muradova)\",\"nr\":\"1113\",\"program\":\"24266\"}','[]'),(5568,'{\"status\":24260,\"_title\":\"0704-Bolgonbaev\",\"nr\":\"0704\",\"program\":\"24266\"}','[]'),(5587,'{\"status\":24260,\"_title\":\"0707-Sanginov\",\"nr\":\"0707\",\"program\":\"24266\"}','[]'),(5607,'{\"status\":24260,\"_title\":\"0815-Sakeev\",\"nr\":\"0815\",\"program\":\"24266\"}','[]'),(5625,'{\"status\":24260,\"_title\":\"1101-Ismanov\",\"nr\":\"1101\",\"program\":\"24266\"}','[]'),(5671,'{\"status\":24260,\"_title\":\"0405-Nachova\",\"nr\":\"0405\",\"program\":\"24267\"}','[]'),(5690,'{\"status\":24260,\"_title\":\"0504-Y&B\",\"nr\":\"0504\",\"program\":\"24267\"}','[]'),(5746,'{\"status\":24260,\"_title\":\"0301-DH\",\"nr\":\"0301\",\"program\":\"24267\"}','[]'),(6128,'{\"status\":24260,\"_title\":\"0408-Mauritania\",\"nr\":\"0408\",\"program\":\"24267\"}','[]'),(6366,'{\"status\":24260,\"_title\":\"0502-Solomon\",\"nr\":\"0502\",\"program\":\"24267\"}','[]'),(6385,'{\"status\":24260,\"_title\":\"0602-Ali\",\"nr\":\"0602\",\"program\":\"24267\"}','[]'),(7041,'{\"status\":24260,\"_title\":\"0603-Bagdonavichus\",\"nr\":\"0603\",\"program\":\"24267\"}','[]'),(7381,'{\"status\":24260,\"_title\":\"0604-Good\",\"nr\":\"0604\",\"program\":\"24267\"}','[]'),(7405,'{\"status\":24260,\"_title\":\"0605-People\",\"nr\":\"0605\",\"program\":\"24267\"}','[]'),(7841,'{\"status\":24260,\"_title\":\"0606-Williams\",\"nr\":\"0606\",\"program\":\"24267\"}','[]'),(7946,'{\"status\":24260,\"_title\":\"0702-Makhashev\",\"nr\":\"0702\",\"program\":\"24267\"}','[]'),(8033,'{\"status\":24260,\"_title\":\"0703-Makuc\",\"nr\":\"0703\",\"program\":\"24267\"}','[]'),(8148,'{\"status\":24260,\"_title\":\"0802-Antilleans\",\"nr\":\"0802\",\"_date_start\":\"2013-05-24T00:00:00\",\"manager\":\"31\",\"program\":\"24267\"}','[]'),(8254,'{\"status\":24260,\"_title\":\"0805-Fadia\",\"nr\":\"0805\",\"program\":\"24267\"}','[]'),(8384,'{\"status\":24260,\"_title\":\"0812-Nubian Minors\",\"nr\":\"0812\",\"program\":\"24267\"}','[]'),(8905,'{\"status\":24260,\"_title\":\"0816-Timishev\",\"nr\":\"0816\",\"program\":\"24267\"}','[]'),(8990,'{\"status\":24260,\"_title\":\"0817-Sejdic\",\"nr\":\"0817\",\"program\":\"24267\"}','[]'),(9034,'{\"status\":24260,\"_title\":\"0819-Bueno\",\"nr\":\"0819\",\"program\":\"24267\"}','[]'),(9182,'{\"status\":24260,\"_title\":\"0822-Adalah\",\"nr\":\"0822\",\"program\":\"24267\"}','[]'),(9253,'{\"status\":24260,\"_title\":\"0824-Shaya\",\"nr\":\"0824\",\"program\":\"24267\"}','[]'),(9275,'{\"status\":24260,\"_title\":\"0902-Suleymanovic\",\"nr\":\"0902\",\"program\":\"24267\"}','[]'),(9432,'{\"status\":24260,\"_title\":\"0904-Mikhaj\",\"nr\":\"0904\",\"program\":\"24267\"}','[]'),(9600,'{\"status\":24260,\"_title\":\"0905-SPIB\",\"nr\":\"0905\",\"program\":\"24267\"}','[]'),(9741,'{\"status\":24260,\"_title\":\"0906-EC v Italy\",\"nr\":\"0906\",\"program\":\"24267\"}','[]'),(10047,'{\"status\":24260,\"_title\":\"0910-Omerovic\",\"nr\":\"0910\",\"program\":\"24267\"}','[]'),(10130,'{\"status\":24260,\"_title\":\"0911-Panah\",\"nr\":\"0911\",\"program\":\"24267\"}','[]'),(10402,'{\"status\":24260,\"_title\":\"0912-Dupont\",\"nr\":\"0912\",\"program\":\"24267\"}','[]'),(10851,'{\"status\":24260,\"_title\":\"0913-Weiss (Germany headscarves)\",\"nr\":\"0913\",\"program\":\"24267\"}','[]'),(11175,'{\"status\":24260,\"_title\":\"1001-Germany Education General\",\"nr\":\"1001\",\"program\":\"24267\"}','[]'),(11260,'{\"status\":24260,\"_title\":\"1003-Iseni\",\"nr\":\"1003\",\"program\":\"24267\"}','[]'),(11349,'{\"status\":24260,\"_title\":\"1008-Ouardiri\",\"nr\":\"1008\",\"program\":\"24267\"}','[]'),(11548,'{\"status\":24260,\"_title\":\"1101-QPC\",\"nr\":\"1101\",\"program\":\"24267\"}','[]'),(11605,'{\"status\":24260,\"_title\":\"1102-Baby DR\",\"nr\":\"1102\",\"program\":\"24267\"}','[]'),(11628,'{\"status\":24260,\"_title\":\"1110-Cicek\",\"nr\":\"1110\",\"program\":\"24267\"}','[]'),(11651,'{\"status\":24260,\"_title\":\"1114-Berlin Segregated Classes\",\"nr\":\"1114\",\"program\":\"24267\"}','[]'),(12845,'{\"status\":24260,\"_title\":\"1202-SAS\",\"nr\":\"1202\",\"program\":\"24267\"}','[]'),(13006,'{\"status\":24260,\"_title\":\"1204-Salkanovic\",\"nr\":\"1204\",\"program\":\"24267\"}','[]'),(13032,'{\"status\":24260,\"_title\":\"1205-Dabetic\",\"nr\":\"1205\",\"program\":\"24267\"}','[]'),(13051,'{\"_title\":\"1207-Leonardo da Vinci\",\"nr\":\"1207\",\"program\":\"24267\",\"status\":24261}','[]'),(13314,'{\"status\":24260,\"_title\":\"0505-Ramzy\",\"nr\":\"0505\",\"program\":\"24267\"}','[]'),(13334,'{\"status\":24260,\"_title\":\"0201-Marques\",\"nr\":\"0201\",\"program\":\"24268\"}','[]'),(13387,'{\"status\":24260,\"_title\":\"0404-GPU\",\"nr\":\"0404\",\"program\":\"24268\"}','[]'),(13425,'{\"status\":24260,\"_title\":\"0403-Claude\",\"nr\":\"0403\",\"program\":\"24268\"}','[]'),(13500,'{\"status\":24260,\"_title\":\"0406-Herrera\",\"nr\":\"0406\",\"program\":\"24268\"}','[]'),(13561,'{\"status\":24260,\"_title\":\"0401-Freedom FM\",\"nr\":\"0401\",\"program\":\"24268\"}','[]'),(13667,'{\"status\":24260,\"_title\":\"0506-Romanenko\",\"nr\":\"0506\",\"program\":\"24268\"}','[]'),(13703,'{\"status\":24260,\"_title\":\"0804-SLAJ\",\"nr\":\"0804\",\"program\":\"24268\"}','[]'),(13745,'{\"status\":24260,\"_title\":\"0811-Hydara\",\"nr\":\"0811\",\"program\":\"24268\"}','[]'),(14027,'{\"status\":24260,\"_title\":\"0823-Kasabova\",\"nr\":\"0823\",\"program\":\"24268\"}','[]'),(14088,'{\"status\":24260,\"_title\":\"0901-MGN\",\"nr\":\"0901\",\"program\":\"24268\"}','[]'),(14169,'{\"status\":24260,\"_title\":\"0903-Pauliukiene\",\"nr\":\"0903\",\"program\":\"24268\"}','[]'),(14220,'{\"status\":24260,\"_title\":\"0914-Sanoma\",\"nr\":\"0914\",\"program\":\"24268\"}','[]'),(14271,'{\"status\":24260,\"_title\":\"1002-Centro 7\",\"nr\":\"1002\",\"program\":\"24268\"}','[]'),(14347,'{\"status\":24260,\"_title\":\"1013-Egypt Bloggers\",\"nr\":\"1013\",\"program\":\"24268\"}','[]'),(14372,'{\"status\":24260,\"_title\":\"1109-Yildirim\",\"nr\":\"1109\",\"program\":\"24268\"}','[]'),(14426,'{\"status\":24260,\"_title\":\"0710-Chardon\",\"nr\":\"0710\",\"program\":\"24268\"}','[]'),(14446,'{\"status\":24260,\"_title\":\"0801-CDDI\",\"nr\":\"0801\",\"program\":\"24268\"}','[]'),(14479,'{\"status\":24260,\"_title\":\"0806-HCLU\",\"nr\":\"0806\",\"program\":\"24268\"}','[]'),(14522,'{\"status\":24260,\"_title\":\"0818-El-Masri\",\"nr\":\"0818\",\"program\":\"24268\"}','[]'),(15795,'{\"status\":24260,\"_title\":\"0916-Vargas\",\"nr\":\"0916\",\"program\":\"24268\"}','[]'),(15836,'{\"status\":24260,\"_title\":\"1005-Araguaia\",\"nr\":\"1005\",\"program\":\"24268\"}','[]'),(15887,'{\"status\":24260,\"_title\":\"1011-Bubon\",\"nr\":\"1011\",\"program\":\"24268\"}','[]'),(15932,'{\"status\":24260,\"_title\":\"1112-Mpagi\",\"nr\":\"1112\",\"program\":\"24268\"}','[]'),(15985,'{\"status\":24260,\"_title\":\"1201-Diario Militar\",\"nr\":\"1201\",\"program\":\"24268\"}','[]'),(16295,'{\"status\":24260,\"_title\":\"0402-Anyaele\",\"nr\":\"0402\",\"program\":\"24269\"}','[]'),(16314,'{\"status\":24260,\"_title\":\"0607-Prosecutor\",\"nr\":\"0607\",\"program\":\"24269\"}','[]'),(16340,'{\"status\":24260,\"_title\":\"1007-Gaza\",\"nr\":\"1007\",\"program\":\"24269\"}','[]'),(16418,'{\"status\":24260,\"_title\":\"1104-Duvalier\",\"nr\":\"1104\",\"program\":\"24269\"}','[]'),(16538,'{\"status\":24260,\"_title\":\"1117-Kenya Complementarity\",\"nr\":\"1117\",\"program\":\"24269\"}','[]'),(16765,'{\"status\":24260,\"_title\":\"1206-Kenya Police Shootings\",\"nr\":\"1206\",\"program\":\"24269\"}','[]'),(16799,'{\"status\":24260,\"_title\":\"0701-APDHE(AFR)\",\"nr\":\"0701\",\"program\":\"24270\"}','[]'),(16900,'{\"status\":24260,\"_title\":\"0813-APDHE(ESP)\",\"nr\":\"0813\",\"program\":\"24270\"}','[]'),(17081,'{\"status\":24260,\"_title\":\"0907-Hussar\",\"nr\":\"0907\",\"program\":\"24270\"}','[]'),(18734,'{\"status\":24260,\"_title\":\"0909-Argor Heraeus\",\"nr\":\"0909\",\"program\":\"24270\"}','[]'),(18752,'{\"status\":24260,\"_title\":\"1103-Malibu\",\"nr\":\"1103\",\"program\":\"24270\"}','[]'),(18960,'{\"status\":24260,\"_title\":\"1105-Frontex\",\"nr\":\"1105\",\"program\":\"24271\"}','[]'),(19041,'{\"status\":24260,\"_title\":\"1111-Cosentino\",\"nr\":\"1111\",\"program\":\"24271\"}','[]'),(19229,'{\"status\":24260,\"_title\":\"1012-Alade\",\"nr\":\"1012\",\"program\":\"24272\"}','[]'),(19324,'{\"status\":24260,\"_title\":\"1106-Arrest Rights\",\"nr\":\"1106\",\"program\":\"24272\"}','[]'),(19505,'{\"status\":24260,\"_title\":\"1108-Magnitsky\",\"nr\":\"1108\",\"program\":\"24272\"}','[]'),(21292,'{\"status\":24260,\"_title\":\"1116-Lipowicz\",\"nr\":\"1116\",\"program\":\"24272\"}','[]'),(21557,'{\"status\":24260,\"_title\":\"1006-Al-Nashiri v Poland\",\"nr\":\"1006\",\"program\":\"24273\"}','[]'),(22486,'{\"status\":24260,\"_title\":\"1009-El-Sharkawi\",\"nr\":\"1009\",\"program\":\"24273\"}','[]'),(22633,'{\"status\":24260,\"_title\":\"1203-Al Nashiri v Romania\",\"nr\":\"1203\",\"program\":\"24273\"}','[]'),(23129,'{\"status\":24260,\"_title\":\"xxxx-Hizb-ut-Tahrir TPI\",\"nr\":\"xxxx\",\"program\":\"24273\"}','[]'),(23156,'{\"status\":24260,\"_title\":\"1004-Salim\",\"nr\":\"1004\",\"program\":\"24273\"}','[]'),(23355,'{\"status\":24260,\"_title\":\"xxxx-Abdulmalik\",\"nr\":\"xxxx\",\"program\":\"24273\"}','[]'),(23459,'{\"_title\":\"Reply from Gambian governemnt\",\"_date_start\":\"2013-01-10T00:00:00\",\"tags\":\"24400,24436,24431,24429\"}','[]'),(23460,'{\"_title\":\"notification\",\"court\":\"24391\",\"_date_start\":\"2013-01-10T00:00:00\"}','[]'),(23465,'{\"_title\":\"case card\",\"_date_start\":\"2013-01-11T00:00:00\",\"state\":\"24316\",\"court\":\"24396\",\"tags\":\"24403,24404,24408\"}','[]'),(23466,'{\"_title\":\"Application to ECHR\",\"court\":\"24391\",\"_date_start\":\"2013-01-11T00:00:00\"}','[]'),(23470,'{\"_title\":\"dummy case\",\"nr\":\"1234\",\"_date_start\":\"2013-01-14T00:00:00\",\"manager\":\"4,7\",\"lead\":\"5\",\"support\":\"8,9\",\"court\":\"24394,24395\",\"program\":\"24268\",\"status\":\"24260\"}','[]'),(23492,'{\"status\":24260,\"_title\":\"0709-Akmatov\",\"nr\":\"999\",\"_date_start\":\"2013-01-15\"}','[]'),(23613,'{\"_title\":\"Decision from court N.182\",\"court\":\"24398\",\"_date_start\":\"2013-01-15T00:00:00\",\"tags\":\"24405,24404,24407\"}','[]'),(23614,'{\"_title\":\"Our complaint had been received by the court\",\"court\":\"24398\",\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23615,'{\"_title\":\"Application to ECHR\",\"court\":\"24391\",\"_date_start\":\"2013-01-15T00:00:00\",\"tags\":\"24405,24407,24400\"}','[]'),(23616,'{\"_title\":\"Judgement on the merits\",\"court\":\"24391\",\"_date_start\":\"2013-01-01T00:00:00\",\"tags\":\"24404,24406\"}','[]'),(23618,'{\"_title\":\"Some general comments about the case\",\"_date_start\":\"2013-01-03T00:00:00\"}','[]'),(23624,'{\"_title\":\"Akmatov case card\",\"_date_start\":\"2013-01-15T00:00:00\",\"_content\":\"gf<br>asd<br>fas<br>df<br>asd<br>fas<br>df<br>asd<br>fas<br>df<br>asd<br>f\",\"state\":\"24337\",\"court\":\"24393\",\"tags\":\"24407,24402,24406,24409\"}','[]'),(23634,'{\"_title\":\"Monthly Report - February 2013\",\"court\":{\"value\":null,\"info\":\"infomraoitn \"},\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23636,'{\"_title\":\"Monthly Report\",\"_date_start\":\"2013-01-15T00:00:00\"}','[]'),(23647,'{\"_title\":\"this is an imported email \",\"_date_start\":\"2013-01-21 22:02:34\",\"from\":\"Oleg Burlaca <oleg@burlaca.com>\",\"_content\":\"---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 7:31 PM\\nSubject: Fwd: this is an imported email (\\/Home\\/Test folder)\\nTo: OSJI OSI <osjibox@gmail.com>\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 7:26 PM\\nSubject: this is an imported email (\\/Home\\/Test folder\\/)\\nTo: osjibox@gmail.com\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 6:16 PM\\nSubject: Fwd: A new action from email (\\/Test\\/Files)\\nTo: osjibox@gmail.com\\n\\n\\n\\n\\n---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Mon, Jan 21, 2013 at 6:13 PM\\nSubject: A new action from email (\\/Test\\/Files\\/)\\nTo: osjibox@gmail.com\\n\\n\\nText here\\n\\nhere text text text\\n\\n\"}','[]'),(23668,'{\"_title\":\"follow-up\",\"_date_start\":\"2013-03-20T00:00:00\",\"_content\":\"can you please follow-up on this?\"}','[]'),(23670,'{\"_title\":\"comment!\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23676,'{\"_title\":\"to the court\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23678,'{\"_title\":\"case card\",\"_date_start\":\"2013-01-22T00:00:00\",\"state\":\"24325\",\"court\":\"24398\",\"tags\":\"24439,24401,24400\"}','[]'),(23681,'{\"_title\":\"Received communication from Turkish Government\",\"_date_start\":\"2013-01-23T00:00:00\"}','[]'),(23682,'{\"_title\":\"received second reply from Turkey\",\"_date_start\":\"2013-01-23T00:00:00\"}','[]'),(23684,'{\"_title\":\"Letter setting hearing date\",\"_date_start\":\"2013-01-22T00:00:00\"}','[]'),(23685,'{\"_title\":\"an email letter \",\"_date_start\":\"2013-01-25 09:22:16\",\"from\":\"Oleg Burlaca <oleg@burlaca.com>\",\"_content\":\"---------- Forwarded message ----------\\nFrom: Oleg Burlaca <oleg@burlaca.com>\\nDate: Fri, Jan 25, 2013 at 11:19 AM\\nSubject: an email letter (\\/Home\\/Test\\/)\\nTo: OSJI OSI <osjibox@gmail.com>\\n\\n\\nasdfasdf asdf asdf\\n\\n\"}','[]'),(23706,'{\"status\":24260,\"_title\":\"test\",\"nr\":\"9999\",\"_date_start\":\"2013-02-13\",\"program\":\"24266\"}','[]'),(23717,'{\"status\":24260,\"_title\":\"tzretz\",\"nr\":\"test\",\"_date_start\":\"2013-02-15T00:00:00\",\"program\":\"24266,24267\",\"lead\":\"1\",\"support\":\"1\"}','[]'),(23739,'{\"_title\":\"TestAction\",\"_date_start\":\"2013-05-28T00:00:00\",\"program\":\"24266\"}','[]'),(23743,'{\"_title\":\"Moidunov action test\",\"_date_start\":\"2013-05-26T00:00:00\",\"program\":\"24266,24267\"}','[]'),(23748,'{\"_title\":\"Action test 31.05.2013\",\"_date_start\":\"2013-05-31T00:00:00\",\"program\":\"24266\"}','[]'),(23777,'{\"_title\":\"[Test] Netherlands Police Shootings Constitutional Case\",\"nr\":\"111111\",\"_date_start\":\"2013-06-01T00:00:00\",\"manager\":\"49\",\"lead\":\"5\",\"support\":\"34,42\",\"court\":\"24396\",\"program\":\"24266\",\"status\":\"24263\",\"country\":\"24325,24335,24339\"}','[]'),(23778,'[]','[]'),(23779,'[]','[]'),(23780,'[]','[]'),(23781,'[]','[]'),(23782,'[]','[]'),(23783,'[]','[]'),(23784,'[]','[]'),(23785,'[]','[]'),(23786,'[]','[]'),(23787,'[]','[]'),(23797,'{\"_title\":\"Decision on merits \",\"_date_start\":\"2013-06-01T00:00:00\",\"program\":\"24266\"}','[]'),(23798,'{\"_title\":\"Additional documents sent to the court\",\"_date_start\":\"2013-03-21T00:00:00\",\"program\":\"24266\"}','[]'),(23799,'{\"_title\":\"Government Reply\",\"_date_start\":\"2013-03-24T00:00:00\",\"content\":\"&nbsp;\",\"program\":\"24266\"}','[]'),(23800,'{\"_title\":\"Communication \",\"_date_start\":\"2013-06-01T00:00:00\",\"content\":\"<br>\",\"program\":\"24266\"}','[]'),(23801,'{\"_title\":\"Letter of introduction\",\"_date_start\":\"2013-06-01T00:00:00\",\"content\":\"&nbsp;\",\"program\":\"24266\"}','[]'),(23815,'{\"status\":24260,\"_title\":\"12\",\"nr\":\"12\",\"_date_start\":\"2013-06-18T00:00:00\"}','[]'),(23816,'[]','[]'),(23817,'[]','[]'),(23818,'[]','[]'),(23819,'[]','[]'),(23820,'[]','[]'),(23821,'[]','[]'),(23822,'[]','[]'),(23823,'[]','[]'),(23824,'[]','[]'),(23825,'[]','[]'),(23831,'[]','[]'),(23841,'[]','[]'),(23850,'[]','[]'),(23851,'[]','[]'),(23852,'[]','[]'),(23853,'[]','[]'),(23854,'[]','[]'),(23855,'[]','[]'),(23856,'[]','[]'),(23857,'[]','[]'),(23869,'[]','[]'),(23871,'{\"_title\":\"test\",\"nr\":\"55555\",\"_date_start\":\"2013-09-24T00:00:00\",\"court\":\"24393\",\"status\":\"24260\",\"tags\":\"24406\"}','[]'),(23872,'[]','[]'),(23873,'[]','[]'),(23874,'[]','[]'),(23875,'[]','[]'),(23876,'[]','[]'),(23877,'[]','[]'),(23878,'[]','[]'),(23879,'[]','[]'),(23880,'[]','[]'),(23881,'[]','[]'),(23897,'{\"program\":\"24267\"}','[]'),(23898,'[]','[]'),(23904,'{\"_title\":\"Test case N1\",\"nr\":\"1\",\"_date_start\":\"2013-09-24T00:00:00\",\"court\":\"24391\",\"program\":\"24274,24273\",\"status\":24260,\"lead\":\"240\",\"support\":\"240,1\"}','[]'),(23905,'[]','[]'),(23906,'[]','[]'),(23907,'[]','[]'),(23908,'[]','[]'),(23909,'[]','[]'),(23910,'[]','[]'),(23911,'[]','[]'),(23912,'[]','[]'),(23913,'[]','[]'),(23914,'[]','[]'),(23915,'{\"status\":24260,\"_title\":\"zrezqe\",\"nr\":\"6436\",\"_date_start\":\"2013-09-24T00:00:00\"}','[]'),(23916,'[]','[]'),(23917,'[]','[]'),(23918,'[]','[]'),(23919,'[]','[]'),(23920,'[]','[]'),(23921,'[]','[]'),(23922,'[]','[]'),(23923,'[]','[]'),(23924,'[]','[]'),(23925,'[]','[]'),(23926,'[]','[]'),(23928,'[]','[]'),(23929,'{\"_title\":\"09292 First case\",\"nr\":\"09292\",\"_date_start\":\"2013-09-24T00:00:00\",\"manager\":\"5\",\"lead\":\"9\",\"support\":\"24,6\",\"court\":\"24393\",\"program\":\"24271\",\"status\":\"24263\",\"tags\":\"24406\",\"country\":\"24330,24339\"}','[]'),(23930,'[]','[]'),(23931,'[]','[]'),(23932,'[]','[]'),(23933,'[]','[]'),(23934,'[]','[]'),(23935,'[]','[]'),(23936,'[]','[]'),(23937,'[]','[]'),(23938,'[]','[]'),(23939,'[]','[]'),(23940,'[]','[]'),(23942,'{\"status\":24260,\"_title\":\"New case\",\"nr\":\"45542\",\"_date_start\":\"2013-09-24T00:00:00\",\"program\":\"24271\"}','[]'),(23943,'[]','[]'),(23944,'[]','[]'),(23945,'[]','[]'),(23946,'[]','[]'),(23947,'[]','[]'),(23948,'[]','[]'),(23949,'[]','[]'),(23950,'[]','[]'),(23951,'[]','[]'),(23952,'[]','[]'),(23954,'{\"_title\":\"Fatulev\",\"nr\":\"123090\",\"_date_start\":\"2013-09-24T00:00:00\",\"manager\":\"55\",\"lead\":\"9\",\"support\":\"24\",\"court\":\"24395\",\"program\":\"24266,24269\",\"status\":\"24264\",\"tags\":\"24407\",\"country\":\"24320\"}','[]'),(23955,'[]','[]'),(23956,'[]','[]'),(23957,'[]','[]'),(23958,'[]','[]'),(23959,'[]','[]'),(23960,'[]','[]'),(23961,'[]','[]'),(23962,'[]','[]'),(23963,'[]','[]'),(23964,'[]','[]'),(23967,'[]','[]'),(23968,'{\"_title\":\"Girleanu\",\"nr\":\"09252013\",\"_date_start\":\"2013-09-25T00:00:00\",\"manager\":\"256\",\"lead\":\"240\",\"program\":\"24268\",\"status\":\"24260\",\"country\":\"24310\"}','[]'),(23969,'[]','[]'),(23970,'[]','[]'),(23971,'[]','[]'),(23972,'[]','[]'),(23973,'[]','[]'),(23974,'[]','[]'),(23975,'[]','[]'),(23976,'[]','[]'),(23977,'[]','[]'),(23978,'[]','[]'),(23988,'{\"_title\":\"Katherin Test\",\"nr\":\"9122013\",\"_date_start\":\"2013-09-25T00:00:00\",\"program\":\"24266,24267\",\"country\":\"24333\",\"status\":\"24260\",\"lead\":\"240\",\"support\":\"5\"}','[]'),(23989,'[]','[]'),(23990,'[]','[]'),(23991,'[]','[]'),(23992,'[]','[]'),(23993,'[]','[]'),(23994,'[]','[]'),(23995,'[]','[]'),(23996,'[]','[]'),(23997,'[]','[]'),(23998,'[]','[]'),(24007,'{\"_title\":\"Svetlana\",\"nr\":\"097856\",\"_date_start\":\"2013-09-26T00:00:00\",\"manager\":\"7\",\"lead\":\"8\",\"court\":\"24393,24395\",\"program\":\"24266\",\"status\":\"24260\",\"tags\":\"24406,24403\",\"country\":\"24317\"}','[]'),(24008,'[]','[]'),(24009,'[]','[]'),(24010,'[]','[]'),(24011,'[]','[]'),(24012,'[]','[]'),(24013,'[]','[]'),(24014,'[]','[]'),(24015,'[]','[]'),(24016,'[]','[]'),(24017,'[]','[]'),(24024,'{\"_title\":\"File EC v Italy brief (tentative)\",\"_date_start\":\"2013-10-01T00:00:00\",\"program\":\"24267\",\"tags\":\"24405\"}','[]'),(24028,'{\"_title\":\"Test action\",\"_date_start\":\"2013-09-30T00:00:00\",\"program\":\"24270\",\"tags\":\"24406\"}','[]'),(24042,'[]','[]'),(24043,'{\"_title\":\"Fields template\",\"en\":\"Fields template\",\"ru\":\"Fields template\",\"type\":\"field\",\"visible\":1,\"iconCls\":\"icon-snippet\",\"cfg\":\"[]\"}','[]'),(24044,'{\"_title\":\"Templates template\",\"en\":\"Templates template\",\"ru\":\"Templates template\",\"type\":\"template\",\"visible\":1,\"iconCls\":\"icon-template\",\"cfg\":\"[]\"}','[]'),(24045,'{\"_title\":\"templatesProperies\",\"en\":\"Template for editing template properties\",\"ru\":\"Template for editing template properties\",\"type\":\"template\",\"visible\":1}','[]'),(24046,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"1\"}','[]'),(24047,'{\"en\":\"JavaScript grid class\",\"ru\":\"JavaScript grid class\",\"_title\":\"gridJsClass\",\"type\":\"jsclasscombo\",\"order\":\"2\"}','[]'),(24048,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"3\"}','[]'),(24049,'{\"en\":\"Default field\",\"ru\":\"Default field\",\"_title\":\"default_field\",\"type\":\"fieldscombo\",\"order\":\"4\"}','[]'),(24050,'{\"en\":\"Files\",\"ru\":\"Files\",\"_title\":\"files\",\"type\":\"checkbox\",\"order\":\"5\"}','[]'),(24051,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\"}','[]'),(24052,'[]','[]'),(24053,'{\"_title\":\"User\",\"en\":\"User\",\"type\":\"user\",\"visible\":1,\"iconCls\":\"icon-object4\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24054,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24055,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24056,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24057,'{\"en\":\"Initials\",\"ru\":\"Initiales\",\"_title\":\"initials\",\"type\":\"varchar\",\"order\":\"4\"}','[]'),(24058,'{\"en\":\"Sex\",\"ru\":\"Sexe\",\"_title\":\"sex\",\"type\":\"_sex\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\"}','[]'),(24059,'{\"en\":\"Position\",\"ru\":\"Titre\",\"_title\":\"position\",\"type\":\"combo\",\"order\":\"7\",\"cfg\":\"{\\\"thesauriId\\\":\\\"362\\\"}\"}','[]'),(24060,'{\"en\":\"E-mail\",\"ru\":\"E-mail\",\"_title\":\"email\",\"type\":\"varchar\",\"order\":\"9\",\"cfg\":\"{\\\"maxInstances\\\":\\\"3\\\"}\"}','[]'),(24061,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language_id\",\"type\":\"_language\",\"order\":\"11\"}','[]'),(24062,'{\"en\":\"Date format\",\"ru\":\"Format de date\",\"_title\":\"short_date_format\",\"type\":\"_short_date_format\",\"order\":\"12\"}','[]'),(24063,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"13\"}','[]'),(24064,'{\"en\":\"Room\",\"ru\":\"Salle\",\"_title\":\"room\",\"type\":\"varchar\",\"order\":\"8\"}','[]'),(24065,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"10\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24066,'{\"en\":\"Location\",\"ru\":\"Emplacement\",\"_title\":\"location\",\"type\":\"combo\",\"order\":\"6\",\"cfg\":\"{\\\"thesauriId\\\":\\\"394\\\"}\"}','[]'),(24067,'{\"_title\":\"email\",\"en\":\"Email\",\"ru\":\"Email\",\"type\":\"email\",\"visible\":1,\"iconCls\":\"icon-mail\",\"cfg\":\"{\\\"files\\\":1,\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24068,'{\"en\":\"Subject\",\"ru\":\"Sujet\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24069,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24070,'{\"en\":\"From\",\"ru\":\"D\'après\",\"_title\":\"from\",\"type\":\"varchar\",\"order\":\"3\",\"cfg\":\"{\\\"thesauriId\\\":\\\"73\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24071,'{\"en\":\"Content\",\"ru\":\"Teneur\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24072,'{\"_title\":\"tasks\",\"en\":\"Task\",\"ru\":\"Task\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"icon-task\",\"cfg\":\"{\\\"data\\\":{\\\"type\\\":6}}\",\"title_template\":\"{name}\"}','[]'),(24073,'{\"_title\":\"event\",\"en\":\"Event\",\"ru\":\"Event\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"icon-event\",\"cfg\":\"{\\\"data\\\":{\\\"type\\\":7}}\",\"title_template\":\"{name}\"}','[]'),(24074,'{\"_title\":\"folder\",\"en\":\"Folder\",\"ru\":\"Folder\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-folder\",\"cfg\":\"{\\\"createMethod\\\":\\\"inline\\\",\\n\\n  \\\"object_plugins\\\":\\n      [\\\"objectProperties\\\",\\n       \\\"comments\\\",\\n       \\\"systemProperties\\\"\\n      ]\\n\\n}\",\"title_template\":\"{name}\"}','[]'),(24075,'{\"_title\":\"file_template\",\"en\":\"File\",\"ru\":\"File\",\"type\":\"file\",\"visible\":1,\"iconCls\":\"file-\",\"title_template\":\"{name}\"}','[]'),(24076,'{\"en\":\"Program\",\"ru\":\"Program\",\"_title\":\"program\",\"type\":\"_objects\",\"order\":\"1\",\"cfg\":\"{\\r\\n\\\"source\\\":\\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"715\\\"\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"category_id\"}','[]'),(24077,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24078,'{\"_title\":\"milestone\",\"en\":\"Milestone\",\"ru\":\"Milestone\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"i-flag\",\"cfg\":\"[]\",\"title_template\":\"{name}\"}','[]'),(24079,'{\"_title\":\"case_template\",\"en\":\"Case\",\"ru\":\"Case\",\"type\":\"case\",\"visible\":1,\"iconCls\":\"icon-briefcase\",\"cfg\":\"{\\\"system_folders\\\": 24248}\",\"title_template\":\"{name}\"}','[]'),(24080,'{\"en\":\"Name\",\"ru\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24081,'{\"en\":\"Number\",\"ru\":\"Number\",\"_title\":\"nr\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24082,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24083,'{\"en\":\"End date\",\"ru\":\"End date\",\"_title\":\"_date_end\",\"type\":\"date\",\"order\":\"4\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24084,'{\"en\":\"Manager\",\"ru\":\"Manager\",\"_title\":\"manager\",\"type\":\"_objects\",\"order\":\"20\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids1\"}','[]'),(24085,'{\"en\":\"Lead\",\"ru\":\"Lead\",\"_title\":\"lead\",\"type\":\"_objects\",\"order\":\"21\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids2\"}','[]'),(24086,'{\"en\":\"Support\",\"ru\":\"Support\",\"_title\":\"support\",\"type\":\"_objects\",\"order\":\"22\",\"cfg\":\"{\\r\\n\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"source\\\": \\\"users\\\"\\r\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"multiValued\\\": true\\r\\n}\",\"solr_column_name\":\"role_ids3\"}','[]'),(24087,'{\"_title\":\"court\",\"en\":\"Court\",\"ru\":\"Court\",\"type\":\"_objects\",\"order\":\"5\",\"cfg\":\"{\\n\\\"scope\\\":\\\"24390\\\"\\n,\\\"editor\\\":\\\"form\\\"\\n,\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"multiValued\\\": true\\n,\\\"faceting\\\": true\\n}\"}','[]'),(24088,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"_objects\",\"order\":\"6\",\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": \\\"24265\\\"\\n,\\\"multiValued\\\": true\\n,\\\"autoLoad\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\n,\\\"faceting\\\": true\\n}\",\"solr_column_name\":\"category_id\"}','[]'),(24089,'{\"_title\":\"status\",\"en\":\"Status\",\"ru\":\"Status\",\"type\":\"_objects\",\"order\":\"8\",\"cfg\":\"{\\n\\\"source\\\":\\\"tree\\\"\\n,\\\"scope\\\": \\\"24259\\\"\\n,\\\"multiValued\\\": false\\n,\\\"autoLoad\\\": true\\n,\\\"faceting\\\": true\\n}\",\"solr_column_name\":\"status\"}','[]'),(24090,'{\"_title\":\"tags\",\"en\":\"Tags\",\"ru\":\"Tags\",\"type\":\"_objects\",\"order\":\"10\",\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24399\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\\n\"}','[]'),(24091,'{\"_title\":\"country\",\"en\":\"Country\",\"ru\":\"Country\",\"type\":\"_objects\",\"order\":\"7\",\"cfg\":\"{\\n\\\"scope\\\":\\\"24308\\\"\\n,\\\"editor\\\":\\\"form\\\"\\n,\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"multiValued\\\": true\\n,\\\"faceting\\\": true\\n}\"}','[]'),(24092,'[]','[]'),(24093,'[]','[]'),(24094,'[]','[]'),(24095,'{\"_title\":\"suspect\",\"en\":\"subject\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-suspect\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{f58} {f45} {f46}\"}','[]'),(24096,'{\"en\":\"Name\",\"_title\":\"fname\",\"type\":\"varchar\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24097,'{\"en\":\"Middle name\",\"_title\":\"patronymic\",\"type\":\"varchar\",\"order\":\"4\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24098,'{\"en\":\"Sex\",\"_title\":\"sex\",\"type\":\"combo\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24099,'{\"en\":\"Age\",\"_title\":\"age\",\"type\":\"int\",\"order\":\"6\",\"solr_column_name\":\"ints\"}','[]'),(24100,'{\"en\":\"Place of service\\/work\",\"_title\":\"work\",\"type\":\"varchar\",\"order\":\"7\",\"solr_column_name\":\"strings\"}','[]'),(24101,'{\"en\":\"Rank at the time of the incident\",\"_title\":\"rank\",\"type\":\"varchar\",\"order\":\"8\",\"solr_column_name\":\"strings\"}','[]'),(24102,'{\"en\":\"Outfit\",\"_title\":\"dressing\",\"type\":\"combo\",\"order\":\"9\",\"cfg\":\"{\\\"thesauriId\\\":\\\"118\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24103,'{\"en\":\"Intoxication from the statements of the applicant\\r\\n\",\"_title\":\"drunk_words\",\"type\":\"combo\",\"order\":\"10\",\"cfg\":\"{\\\"thesauriId\\\":\\\"100\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24104,'{\"en\":\"Nickname\\r\\n\",\"_title\":\"nickname\",\"type\":\"varchar\",\"order\":\"11\",\"solr_column_name\":\"strings\"}','[]'),(24105,'{\"en\":\"Physical description\\r\\n\",\"_title\":\"look\",\"type\":\"varchar\",\"order\":\"12\",\"solr_column_name\":\"strings\"}','[]'),(24106,'{\"en\":\"Special features\\r\\n\",\"_title\":\"distinctive_marks\",\"type\":\"varchar\",\"order\":\"13\",\"solr_column_name\":\"strings\"}','[]'),(24107,'{\"en\":\"Last name\\r\\n\",\"_title\":\"lname\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"strings\"}','[]'),(24108,'{\"_title\":\"Case card\",\"en\":\"Case card\",\"ru\":\"Case card\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-blog-blue\",\"cfg\":\"{\\\"files\\\":\\\"0\\\",\\\"main_file\\\":\\\"0\\\"}\"}','[]'),(24109,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24110,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24111,'{\"en\":\"Content\",\"ru\":\"Content\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24112,'{\"en\":\"State\",\"ru\":\"State\",\"_title\":\"state\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"351\\\"}\"}','[]'),(24113,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"2\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"The main court\\\"}\"}','[]'),(24114,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24115,'[]','[]'),(24116,'{\"_title\":\"informationalLetter\",\"en\":\"Incoming action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-arrow-left-medium-green\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{template_title}: {object_title}\"}','[]'),(24117,'{\"en\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24118,'{\"en\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24119,'{\"en\":\"Content\\r\\n\",\"_title\":\"_content\",\"type\":\"html\",\"order\":\"3\",\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\",\"solr_column_name\":\"texts\"}','[]'),(24120,'{\"en\":\"Author\",\"ru\":\"Auteur\",\"_title\":\"author\",\"type\":\"combo\",\"order\":\"3\",\"cfg\":\"{\\\"thesauriId\\\":\\\"337\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24121,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"341\\\",\\\"maxInstances\\\":\\\"3\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24122,'{\"_title\":\"email1\",\"en\":\"email\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-mail-receive\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24123,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24124,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24125,'{\"_title\":\"communication\",\"en\":\"Communication\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-balloon\",\"cfg\":\"[]\"}','[]'),(24126,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24127,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24128,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24129,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24130,'{\"_title\":\"decision\",\"en\":\"Decision\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-decision\",\"cfg\":\"[]\"}','[]'),(24131,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24132,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24133,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24134,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24135,'{\"_title\":\"judgement\",\"en\":\"Judgement\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-echr_decision\",\"cfg\":\"[]\"}','[]'),(24136,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24137,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24138,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24139,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24140,'{\"_title\":\"gv_reply\",\"en\":\"Government reply\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-object8\",\"cfg\":\"[]\"}','[]'),(24141,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24142,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24143,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24144,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24145,'{\"_title\":\"notification\",\"en\":\"Notification\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-bell\",\"cfg\":\"[]\"}','[]'),(24146,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24147,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24148,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24149,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24150,'[]','[]'),(24151,'{\"_title\":\"Outgoing action\",\"en\":\"Outgoing action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-arrow-right-medium\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\",\"title_template\":\"{template_title}\"}','[]'),(24152,'{\"en\":\"Name\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24153,'{\"en\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24154,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"341\\\",\\\"maxInstances\\\":\\\"3\\\"}\",\"solr_column_name\":\"ints\"}','[]'),(24155,'{\"_title\":\"email2\",\"en\":\"email\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-mail-send\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24156,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24157,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24158,'{\"_title\":\"written_comments\",\"en\":\"Written comments\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-document-text\",\"cfg\":\"[]\"}','[]'),(24159,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24160,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"1\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24161,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24162,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"3\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24163,'{\"_title\":\"application\",\"en\":\"Application\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-echr_complaint\",\"cfg\":\"[]\"}','[]'),(24164,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24165,'{\"en\":\"Court\",\"ru\":\"Court\",\"_title\":\"court\",\"type\":\"combo\",\"order\":\"4\",\"cfg\":\"{\\\"thesauriId\\\":\\\"724\\\", \\\"hint\\\": \\\"Other court if not the same as the main case Court\\\"}\"}','[]'),(24166,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"solr_column_name\":\"date_start\"}','[]'),(24167,'{\"en\":\"Tags\",\"ru\":\"Tags\",\"_title\":\"tags\",\"type\":\"popuplist\",\"order\":\"5\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\": \\\"760\\\"\\r\\n,\\\"multiple\\\": \\\"true\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24168,'{\"en\":\"Application Nr.\",\"ru\":\"Application Nr.\",\"_title\":\"appnr\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24169,'[]','[]'),(24170,'{\"_title\":\"Client\",\"en\":\"Client\",\"ru\":\"Client\",\"type\":\"object\",\"visible\":1,\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24171,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24172,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24173,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24174,'{\"en\":\"Sex\",\"ru\":\"Sexe\",\"_title\":\"sex\",\"type\":\"_sex\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\"}','[]'),(24175,'{\"en\":\"Birthday\",\"ru\":\"Anniversaire\",\"_title\":\"birth\",\"type\":\"date\",\"order\":\"6\"}','[]'),(24176,'{\"en\":\"Citizenship\",\"ru\":\"Citoyenneté\",\"_title\":\"citizenship\",\"type\":\"combo\",\"order\":\"7\",\"cfg\":\"{\\\"thesauriId\\\":\\\"310\\\"}\"}','[]'),(24177,'{\"en\":\"Nationality\",\"ru\":\"Nationalité\",\"_title\":\"nationality\",\"type\":\"combo\",\"order\":\"8\",\"cfg\":\"{\\\"thesauriId\\\":\\\"309\\\"}\"}','[]'),(24178,'{\"en\":\"E-mail\",\"ru\":\"E-mail\",\"_title\":\"email\",\"type\":\"varchar\",\"order\":\"10\",\"cfg\":\"{\\\"maxInstances\\\":\\\"3\\\"}\"}','[]'),(24179,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"14\"}','[]'),(24180,'{\"en\":\"Address\",\"ru\":\"Adresse\",\"_title\":\"address\",\"type\":\"varchar\",\"order\":\"13\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24181,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"11\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24182,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"popuplist\",\"order\":\"4\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"324\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24183,'{\"_title\":\"Organization\",\"en\":\"Organization\",\"ru\":\"Organisation\",\"type\":\"object\",\"visible\":1,\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','[]'),(24184,'{\"en\":\"Full name (en)\",\"ru\":\"Nom complet (en)\",\"_title\":\"l1\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(24185,'{\"en\":\"Full name (fr)\",\"ru\":\"Nom complet (fr)\",\"_title\":\"l2\",\"type\":\"varchar\",\"order\":\"2\"}','[]'),(24186,'{\"en\":\"Full name (ru)\",\"ru\":\"Nom complet (ru)\",\"_title\":\"l3\",\"type\":\"varchar\",\"order\":\"3\"}','[]'),(24187,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"11\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(24188,'{\"en\":\"Fax\",\"ru\":\"Télécopieur\",\"_title\":\"фах\",\"type\":\"varchar\",\"order\":\"12\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24189,'{\"en\":\"Postal index\",\"ru\":\"Indice postal\",\"_title\":\"postal_index\",\"type\":\"varchar\",\"order\":\"13\"}','[]'),(24190,'{\"en\":\"Address\",\"ru\":\"Adresse\",\"_title\":\"address\",\"type\":\"varchar\",\"order\":\"14\",\"cfg\":\"{\\\"maxInstances\\\":\\\"5\\\"}\"}','[]'),(24191,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"16\"}','[]'),(24192,'{\"en\":\"Regions\",\"ru\":\"Régions\",\"_title\":\"regions\",\"type\":\"popuplist\",\"order\":\"15\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"283\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24193,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"popuplist\",\"order\":\"4\",\"cfg\":\"{\\r\\n\\\"thesauriId\\\":\\\"277\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\"}','[]'),(24194,'{\"_title\":\"Test\",\"en\":\"Test\",\"ru\":\"Test\",\"visible\":1,\"iconCls\":\"icon-none\"}','[]'),(24195,'{\"_title\":\"Action\",\"en\":\"Action\",\"ru\":\"Action\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-petition\",\"cfg\":\"[]\"}','[]'),(24196,'{\"en\":\"Title\",\"ru\":\"Title\",\"_title\":\"_title\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24197,'{\"en\":\"Date\",\"ru\":\"Date\",\"_title\":\"_date_start\",\"type\":\"date\",\"order\":\"2\",\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\"}','[]'),(24198,'{\"_title\":\"content\",\"en\":\"Content\",\"ru\":\"Content\",\"type\":\"html\",\"order\":10,\"cfg\":\"{\\\"showIn\\\": \\\"tabsheet\\\"}\"}','[]'),(24199,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"_objects\",\"order\":5,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24265\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\\n\",\"solr_column_name\":\"category_id\"}','[]'),(24200,'{\"_title\":\"tags\",\"en\":\"Tags\",\"ru\":\"Tags\",\"type\":\"_objects\",\"order\":\"3\",\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24399\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\\n\"}','[]'),(24201,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24202,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_fieldTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(24203,'{\"en\":\"Order\",\"ru\":\"Order\",\"_title\":\"order\",\"type\":\"int\",\"order\":\"6\",\"cfg\":\"[]\"}','[]'),(24204,'{\"_title\":\"cfg\",\"en\":\"Config\",\"ru\":\"Config\",\"type\":\"memo\",\"order\":\"7\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(24205,'{\"en\":\"Solr column name\",\"ru\":\"Solr column name\",\"_title\":\"solr_column_name\",\"type\":\"varchar\",\"order\":\"8\",\"cfg\":\"[]\"}','[]'),(24206,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(24207,'{\"en\":\"Title (ru)\",\"ru\":\"Title (ru)\",\"_title\":\"ru\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"[]\"}','[]'),(24208,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\",\\\"rea-dOnly\\\":true}\"}','[]'),(24209,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(24210,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"6\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(24211,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"7\",\"cfg\":\"[]\"}','[]'),(24212,'{\"en\":\"Config\",\"ru\":\"Config\",\"_title\":\"cfg\",\"type\":\"text\",\"order\":\"8\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(24213,'{\"en\":\"Title template\",\"ru\":\"Title template\",\"_title\":\"title_template\",\"type\":\"text\",\"order\":\"9\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(24214,'{\"en\":\"Info template\",\"ru\":\"Info template\",\"_title\":\"info_template\",\"type\":\"text\",\"order\":\"10\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(24215,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(24216,'{\"en\":\"Title (ru)\",\"ru\":\"Title (ru)\",\"_title\":\"ru\",\"type\":\"varchar\",\"order\":\"2\",\"cfg\":\"[]\"}','[]'),(24217,'{\"_title\":\"Thesauri Item\",\"en\":\"Thesauri Item\",\"ru\":\"Thesauri Item\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-blue-document-small\",\"title_template\":\"{en}\"}','[]'),(24218,'{\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":5,\"cfg\":null}','[]'),(24219,'{\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":6,\"cfg\":null}','[]'),(24220,'{\"_title\":\"order\",\"type\":\"int\",\"order\":7,\"cfg\":null}','[]'),(24221,'{\"_title\":\"en\",\"type\":\"varchar\",\"order\":0,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24222,'{\"_title\":\"ru\",\"type\":\"varchar\",\"order\":1,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24223,'{\"_title\":\"System\"}','[]'),(24224,'{\"_title\":\"Phases\"}','[]'),(24225,'{\"en\":\"preliminary check\",\"visible\":1,\"order\":\"1\"}','[]'),(24226,'{\"en\":\"investigation\",\"visible\":1,\"order\":\"2\"}','[]'),(24227,'{\"en\":\"court\",\"visible\":1,\"order\":\"3\"}','[]'),(24228,'{\"en\":\"civil claim\",\"visible\":1,\"order\":\"4\"}','[]'),(24229,'{\"en\":\"ECHR\",\"visible\":1,\"order\":\"5\"}','[]'),(24239,'{\"_title\":\"Responsible party\"}','[]'),(24240,'{\"en\":\"OSJI\",\"ru\":\"OSJI\",\"visible\":1,\"order\":\"1\"}','[]'),(24241,'{\"en\":\"State\",\"ru\":\"State\",\"visible\":1,\"order\":\"2\"}','[]'),(24242,'{\"en\":\"ECHR\",\"ru\":\"ECHR\",\"visible\":1,\"order\":\"3\"}','[]'),(24243,'{\"_title\":\"Files\"}','[]'),(24244,'{\"en\":\"Research\",\"ru\":\"Research\",\"visible\":1,\"order\":\"1\"}','[]'),(24245,'{\"en\":\"CaseLaw\",\"ru\":\"CaseLaw\",\"visible\":1,\"order\":\"2\"}','[]'),(24246,'{\"en\":\"EDR\",\"ru\":\"EDR\",\"visible\":1,\"order\":\"3\"}','[]'),(24247,'{\"en\":\"Exhibit\",\"ru\":\"Exhibit\",\"visible\":1,\"order\":\"4\"}','[]'),(24248,'{\"_title\":\"Case Folders\"}','[]'),(24249,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24250,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24251,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24252,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24253,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24254,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24255,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24256,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24257,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24258,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24259,'{\"_title\":\"Case statuses\"}','[]'),(24260,'{\"en\":\"Active\",\"ru\":\"Actif\",\"visible\":1,\"order\":\"2\"}','[]'),(24261,'{\"en\":\"Closed\",\"ru\":\"Fermé\",\"visible\":1,\"order\":\"4\"}','[]'),(24262,'{\"en\":\"Archived\",\"ru\":\"Archivé\",\"visible\":1,\"order\":\"5\"}','[]'),(24263,'{\"en\":\"Withdrawn\",\"visible\":1,\"order\":\"3\"}','[]'),(24264,'{\"en\":\"Under consideration\",\"ru\":\"À l\'étude\",\"visible\":1,\"order\":\"1\"}','[]'),(24265,'{\"_title\":\"Office\"}','[]'),(24266,'{\"en\":\"Moscow\",\"ru\":\"Москва\",\"security_group\":242,\"managers\":\"268\",\"iconCls\":\"task-blue\",\"visible\":1,\"order\":\"2\"}','[]'),(24267,'{\"en\":\"New York\",\"ru\":\"New York\",\"security_group\":243,\"managers\":\"269\",\"iconCls\":\"task-green\",\"visible\":1,\"order\":\"3\"}','[]'),(24268,'{\"en\":\"Paris\",\"ru\":\"Paris\",\"security_group\":244,\"managers\":\"266\",\"iconCls\":\"task-orange\",\"visible\":1,\"order\":\"4\"}','[]'),(24269,'{\"en\":\"London\",\"ru\":\"London\",\"security_group\":245,\"managers\":\"267\",\"iconCls\":\"task-purple\",\"visible\":1,\"order\":\"5\"}','[]'),(24270,'{\"en\":\"Buenos Aires\",\"ru\":\"Buenos Aires\",\"security_group\":246,\"iconCls\":\"task-red\",\"visible\":1,\"order\":\"6\"}','[]'),(24271,'{\"en\":\"Tokyo\",\"ru\":\"Tokyo\",\"security_group\":247,\"iconCls\":\"task-yellow\",\"visible\":1,\"order\":\"7\"}','[]'),(24272,'{\"en\":\"San Francisco\",\"ru\":\"San Francisco\",\"security_group\":248,\"iconCls\":\"task-olive\",\"visible\":1,\"order\":\"8\"}','[]'),(24273,'{\"en\":\"Lima\",\"ru\":\"Lima\",\"security_group\":249,\"managers\":\"266\",\"iconCls\":\"task-steel\",\"visible\":1,\"order\":\"9\"}','[]'),(24274,'{\"en\":\"global\",\"ru\":\"global\",\"iconCls\":\"task-gray\",\"visible\":1,\"order\":\"1\"}','[]'),(24275,'{\"_title\":\"Fields\"}','[]'),(24276,'{\"_title\":\"yes\\/no\"}','[]'),(24277,'{\"en\":\"yes\",\"visible\":1,\"order\":\"1\"}','[]'),(24278,'{\"en\":\"no\",\"visible\":1,\"order\":\"2\"}','[]'),(24279,'{\"_title\":\"sex\"}','[]'),(24280,'{\"en\":\"male\",\"visible\":1,\"order\":\"1\"}','[]'),(24281,'{\"en\":\"female\",\"visible\":1,\"order\":\"2\"}','[]'),(24282,'{\"_title\":\"checkbox\"}','[]'),(24283,'{\"en\":\"yes\",\"visible\":1,\"order\":\"1\"}','[]'),(24284,'{\"en\":\"no\",\"visible\":1,\"order\":\"2\"}','[]'),(24285,'{\"_title\":\"types of letters\"}','[]'),(24286,'{\"en\":\"response\",\"visible\":1,\"order\":\"1\"}','[]'),(24287,'{\"en\":\"decision\",\"visible\":1,\"order\":\"2\"}','[]'),(24288,'{\"en\":\"communication\",\"visible\":1,\"order\":\"3\"}','[]'),(24289,'{\"en\":\"notification\",\"visible\":1,\"order\":\"4\"}','[]'),(24290,'{\"en\":\"presentation\",\"visible\":1,\"order\":\"5\"}','[]'),(24291,'{\"en\":\"according to the examination check\",\"visible\":1,\"order\":\"6\"}','[]'),(24292,'{\"en\":\"complaint\",\"iconCls\":\"icon-bullet_gray\",\"visible\":1,\"order\":\"7\"}','[]'),(24293,'{\"en\":\"check initiation\",\"visible\":1,\"order\":\"8\"}','[]'),(24294,'{\"en\":\"petition\",\"visible\":1,\"order\":\"9\"}','[]'),(24295,'{\"en\":\"appeal\",\"visible\":1,\"order\":\"10\"}','[]'),(24296,'{\"en\":\"claim\",\"visible\":1,\"order\":\"11\"}','[]'),(24297,'{\"en\":\"informative letter\",\"visible\":1,\"order\":\"12\"}','[]'),(24298,'{\"en\":\"violation\",\"visible\":1,\"order\":\"13\"}','[]'),(24299,'{\"en\":\"complaint of the defendant\",\"iconCls\":\"icon-bullet_gray\",\"visible\":1,\"order\":\"14\"}','[]'),(24300,'{\"_title\":\"Author\"}','[]'),(24301,'{\"en\":\"Court\",\"ru\":\"Cour\",\"visible\":1,\"order\":\"1\"}','[]'),(24302,'{\"en\":\"Applicant\",\"ru\":\"Demandeur\",\"visible\":1,\"order\":\"2\"}','[]'),(24303,'{\"en\":\"Government\",\"ru\":\"Government\",\"visible\":1,\"order\":\"3\"}','[]'),(24304,'{\"_title\":\"Languages\"}','[]'),(24305,'{\"en\":\"Eng\",\"ru\":\"Eng\",\"visible\":1,\"order\":\"1\"}','[]'),(24306,'{\"en\":\"Rus\",\"ru\":\"Rus\",\"visible\":1,\"order\":\"2\"}','[]'),(24307,'{\"en\":\"Uzb\",\"ru\":\"Uzb\",\"visible\":1,\"order\":\"3\"}','[]'),(24308,'{\"_title\":\"Country\"}','[]'),(24309,'{\"en\":\"Kyrgyzstan\",\"ru\":\"Kyrgyzstan\",\"visible\":1,\"order\":\"20\"}','[]'),(24310,'{\"en\":\"Italy\",\"ru\":\"Italy\",\"visible\":1,\"order\":\"17\"}','[]'),(24311,'{\"en\":\"Macedonia\",\"ru\":\"Macedonia\",\"visible\":1,\"order\":\"22\"}','[]'),(24312,'{\"en\":\"Germany\",\"ru\":\"Germany\",\"visible\":1,\"order\":\"14\"}','[]'),(24313,'{\"en\":\"Russia\",\"visible\":1,\"order\":\"27\"}','[]'),(24314,'{\"en\":\"Turkey\",\"visible\":1,\"order\":\"31\"}','[]'),(24315,'{\"en\":\"Romania\",\"visible\":1,\"order\":\"26\"}','[]'),(24316,'{\"en\":\"Poland\",\"visible\":1,\"order\":\"25\"}','[]'),(24317,'{\"en\":\"Czech Republic\",\"visible\":1,\"order\":\"9\"}','[]'),(24318,'{\"en\":\"Israel\",\"visible\":1,\"order\":\"16\"}','[]'),(24319,'{\"en\":\"Kenya\",\"visible\":1,\"order\":\"19\"}','[]'),(24320,'{\"en\":\"Kazakhstan\",\"visible\":1,\"order\":\"18\"}','[]'),(24321,'{\"en\":\"Slovenia\",\"visible\":1,\"order\":\"29\"}','[]'),(24322,'{\"en\":\"Bulgaria\",\"visible\":1,\"order\":\"4\"}','[]'),(24323,'{\"en\":\"Gambia\",\"visible\":1,\"order\":\"13\"}','[]'),(24324,'{\"en\":\"Switzerland\",\"visible\":1,\"order\":\"30\"}','[]'),(24325,'{\"en\":\"Netherlands\",\"visible\":1,\"order\":\"24\"}','[]'),(24326,'{\"en\":\"Dominican Republic\",\"visible\":1,\"order\":\"11\"}','[]'),(24327,'{\"en\":\"Angola\",\"visible\":1,\"order\":\"1\"}','[]'),(24328,'{\"en\":\"Equatorial Guinea\",\"visible\":1,\"order\":\"12\"}','[]'),(24329,'{\"en\":\"Bosnia and Herzegovina\",\"visible\":1,\"order\":\"2\"}','[]'),(24330,'{\"en\":\"Denmark\",\"visible\":1,\"order\":\"10\"}','[]'),(24331,'{\"en\":\"Lithuania\",\"visible\":1,\"order\":\"21\"}','[]'),(24332,'{\"en\":\"Côte d\'Ivoire\",\"visible\":1,\"order\":\"8\"}','[]'),(24333,'{\"en\":\"Chile\",\"visible\":1,\"order\":\"6\"}','[]'),(24334,'{\"en\":\"Hungary\",\"visible\":1,\"order\":\"15\"}','[]'),(24335,'{\"en\":\"Mauritania\",\"visible\":1,\"order\":\"23\"}','[]'),(24336,'{\"en\":\"Cameroon\",\"visible\":1,\"order\":\"5\"}','[]'),(24337,'{\"en\":\"Botswana\",\"visible\":1,\"order\":\"3\"}','[]'),(24338,'{\"en\":\"Rwanda\",\"visible\":1,\"order\":\"28\"}','[]'),(24339,'{\"en\":\"Costa Rica\",\"visible\":1,\"order\":\"7\"}','[]'),(24340,'{\"_title\":\"Position\"}','[]'),(24341,'{\"en\":\"Administrative Associate \",\"ru\":\"Associate administrative\",\"visible\":1,\"order\":\"1\"}','[]'),(24342,'{\"en\":\"Associate Legal Officer\",\"ru\":\"Juriste adjoint\",\"visible\":1,\"order\":\"2\"}','[]'),(24343,'{\"en\":\"Communications Officer\",\"ru\":\"Agente des communications\",\"visible\":1,\"order\":\"3\"}','[]'),(24344,'{\"en\":\"Director\",\"ru\":\"Directeur\",\"visible\":1,\"order\":\"4\"}','[]'),(24345,'{\"en\":\"Director of Administration\",\"ru\":\"Director d\'administration\",\"visible\":1,\"order\":\"5\"}','[]'),(24346,'{\"en\":\"Director of Programs\",\"ru\":\"Directeur des programmes\",\"visible\":1,\"order\":\"6\"}','[]'),(24347,'{\"en\":\"Executive Assistant\",\"ru\":\"Assistante de direction\",\"visible\":1,\"order\":\"7\"}','[]'),(24348,'{\"en\":\"Executive Director\",\"ru\":\"Directeur exécutif\",\"visible\":1,\"order\":\"8\"}','[]'),(24349,'{\"en\":\"Intern\",\"ru\":\"Interne\",\"visible\":1,\"order\":\"9\"}','[]'),(24350,'{\"en\":\"KRT Monitor\",\"ru\":\"KRT moniteur\",\"visible\":1,\"order\":\"10\"}','[]'),(24351,'{\"en\":\"Lawyer\",\"ru\":\"Avocat\",\"visible\":1,\"order\":\"11\"}','[]'),(24352,'{\"en\":\"Legal Intern\",\"ru\":\"Stagiaire juridique\",\"visible\":1,\"order\":\"12\"}','[]'),(24353,'{\"en\":\"Legal Officer\",\"ru\":\"Conseiller juridique\",\"visible\":1,\"order\":\"13\"}','[]'),(24354,'{\"en\":\"Litigation Director\",\"ru\":\"Directeur des litiges\",\"visible\":1,\"order\":\"14\"}','[]'),(24355,'{\"en\":\"Litigation Fellow\",\"ru\":\"Contentieux boursier\",\"visible\":1,\"order\":\"15\"}','[]'),(24356,'{\"en\":\"Policy Officer\",\"ru\":\"Responsable de la politique\",\"visible\":1,\"order\":\"16\"}','[]'),(24357,'{\"en\":\"Program Assistant\",\"ru\":\"Assistant de programme\",\"visible\":1,\"order\":\"17\"}','[]'),(24358,'{\"en\":\"Program Associate\",\"ru\":\"Associé au programme\",\"visible\":1,\"order\":\"18\"}','[]'),(24359,'{\"en\":\"Program Coordinator\",\"ru\":\"Coordonnateur du programme\",\"visible\":1,\"order\":\"19\"}','[]'),(24360,'{\"en\":\"Program Officer\",\"ru\":\"Agent de programme\",\"visible\":1,\"order\":\"20\"}','[]'),(24361,'{\"en\":\"Project Coordinator\",\"ru\":\"Coordinateur du projet\",\"visible\":1,\"order\":\"21\"}','[]'),(24362,'{\"en\":\"Project Manager\",\"ru\":\"Chef de projet\",\"visible\":1,\"order\":\"22\"}','[]'),(24363,'{\"en\":\"Resident Fellow\",\"ru\":\"Compatriotes résident\",\"visible\":1,\"order\":\"23\"}','[]'),(24364,'{\"en\":\"Senior Advisor\",\"ru\":\"Conseiller principal\",\"visible\":1,\"order\":\"24\"}','[]'),(24365,'{\"en\":\"Senior Advocacy Advisor\",\"ru\":\"Conseiller principal plaidoyer\",\"visible\":1,\"order\":\"25\"}','[]'),(24366,'{\"en\":\"Senior Advocacy Officer\",\"ru\":\"Officier supérieur de plaidoyer\",\"visible\":1,\"order\":\"26\"}','[]'),(24367,'{\"en\":\"Senior Attorney\",\"ru\":\"Avocat principal\",\"visible\":1,\"order\":\"27\"}','[]'),(24368,'{\"en\":\"Senior Legal Advisor\",\"ru\":\"Conseiller juridique principal\",\"visible\":1,\"order\":\"28\"}','[]'),(24369,'{\"en\":\"Senior Legal Officer\",\"ru\":\"Juriste principal\",\"visible\":1,\"order\":\"29\"}','[]'),(24370,'{\"en\":\"Senior Officer\",\"ru\":\"Officier supérieur\",\"visible\":1,\"order\":\"30\"}','[]'),(24371,'{\"en\":\"Senior Project Manager\",\"ru\":\"Chef de projet senior\",\"visible\":1,\"order\":\"31\"}','[]'),(24372,'{\"en\":\"Temporary Program Coordinator\",\"visible\":1,\"order\":\"32\"}','[]'),(24373,'{\"_title\":\"Location\"}','[]'),(24374,'{\"en\":\"Abuja\",\"ru\":\"Abuja\",\"visible\":1,\"order\":\"1\"}','[]'),(24375,'{\"en\":\"Amsterdam\",\"ru\":\"Amsterdam\",\"visible\":1,\"order\":\"2\"}','[]'),(24376,'{\"en\":\"Bishkek\",\"ru\":\"Bishkek\",\"visible\":1,\"order\":\"3\"}','[]'),(24377,'{\"en\":\"Brussels\",\"ru\":\"Brussels\",\"visible\":1,\"order\":\"4\"}','[]'),(24378,'{\"en\":\"Budapest\",\"ru\":\"Budapest\",\"visible\":1,\"order\":\"5\"}','[]'),(24379,'{\"en\":\"Cambodia\",\"ru\":\"Cambodia\",\"visible\":1,\"order\":\"6\"}','[]'),(24380,'{\"en\":\"Geneva\",\"ru\":\"Geneva\",\"visible\":1,\"order\":\"7\"}','[]'),(24381,'{\"en\":\"London\",\"ru\":\"London\",\"visible\":1,\"order\":\"8\"}','[]'),(24382,'{\"en\":\"Madrid\",\"ru\":\"Madrid\",\"visible\":1,\"order\":\"9\"}','[]'),(24383,'{\"en\":\"Mexico City\",\"ru\":\"Mexico City\",\"visible\":1,\"order\":\"10\"}','[]'),(24384,'{\"en\":\"New York\",\"ru\":\"New York\",\"visible\":1,\"order\":\"11\"}','[]'),(24385,'{\"en\":\"Paris\",\"ru\":\"Paris\",\"visible\":1,\"order\":\"12\"}','[]'),(24386,'{\"en\":\"Santo Domingo\",\"ru\":\"Santo Domingo\",\"visible\":1,\"order\":\"13\"}','[]'),(24387,'{\"en\":\"The Hague\",\"ru\":\"The Hague\",\"visible\":1,\"order\":\"14\"}','[]'),(24388,'{\"en\":\"Tirana\",\"ru\":\"Tirana\",\"visible\":1,\"order\":\"15\"}','[]'),(24389,'{\"en\":\"Washington\",\"ru\":\"Washington\",\"visible\":1,\"order\":\"16\"}','[]'),(24390,'{\"_title\":\"Court\"}','[]'),(24391,'{\"en\":\"ECHR\",\"visible\":1,\"order\":\"1\"}','[]'),(24392,'{\"en\":\"ACHPR\",\"visible\":1,\"order\":\"2\"}','[]'),(24393,'{\"en\":\"UNHRC\",\"visible\":1,\"order\":\"3\"}','[]'),(24394,'{\"en\":\"IACHR\",\"visible\":1,\"order\":\"4\"}','[]'),(24395,'{\"en\":\"CAT\",\"visible\":1,\"order\":\"5\"}','[]'),(24396,'{\"en\":\"UNCAT\",\"visible\":1,\"order\":\"6\"}','[]'),(24397,'{\"en\":\"ECOWAS\",\"visible\":1,\"order\":\"7\"}','[]'),(24398,'{\"en\":\"Domestic Court\",\"visible\":1,\"order\":\"8\"}','[]'),(24399,'{\"_title\":\"Tags\"}','[]'),(24400,'{\"en\":\"Citizenship\",\"visible\":1,\"order\":\"1\"}','[]'),(24401,'{\"en\":\"Discrimination\",\"visible\":1,\"order\":\"2\"}','[]'),(24402,'{\"en\":\"Family Unification\",\"visible\":1,\"order\":\"3\"}','[]'),(24403,'{\"en\":\"Torture\",\"visible\":1,\"order\":\"4\"}','[]'),(24404,'{\"en\":\"Rendition\",\"visible\":1,\"order\":\"5\"}','[]'),(24405,'{\"en\":\"Statelessness\",\"visible\":1,\"order\":\"6\"}','[]'),(24406,'{\"en\":\"Natural resources\",\"visible\":1,\"order\":\"7\"}','[]'),(24407,'{\"en\":\"Corruption\",\"visible\":1,\"order\":\"8\"}','[]'),(24408,'{\"en\":\"Spoliation\",\"visible\":1,\"order\":\"9\"}','[]'),(24409,'{\"en\":\"Unjust enrichment\",\"visible\":1,\"order\":\"10\"}','[]'),(24410,'{\"en\":\"Money laundering\",\"visible\":1,\"order\":\"11\"}','[]'),(24411,'{\"en\":\"Roma\",\"visible\":1,\"order\":\"12\"}','[]'),(24412,'{\"en\":\"Inhuman treatment\",\"visible\":1,\"order\":\"13\"}','[]'),(24413,'{\"en\":\"Right to information\",\"visible\":1,\"order\":\"14\"}','[]'),(24414,'{\"en\":\"Right to truth\",\"visible\":1,\"order\":\"15\"}','[]'),(24415,'{\"en\":\"Access to information\",\"visible\":1,\"order\":\"16\"}','[]'),(24416,'{\"en\":\"Education\",\"visible\":1,\"order\":\"17\"}','[]'),(24417,'{\"en\":\"Ethnic profiling\",\"visible\":1,\"order\":\"18\"}','[]'),(24418,'{\"en\":\"Database\",\"visible\":1,\"order\":\"19\"}','[]'),(24419,'{\"en\":\"Freedom of expression\",\"visible\":1,\"order\":\"20\"}','[]'),(24420,'{\"en\":\"Freedom of information\",\"visible\":1,\"order\":\"21\"}','[]'),(24421,'{\"en\":\"Central Asia\",\"visible\":1,\"order\":\"22\"}','[]'),(24422,'{\"en\":\"War Crime\",\"visible\":1,\"order\":\"23\"}','[]'),(24423,'{\"en\":\"Investigation\",\"visible\":1,\"order\":\"24\"}','[]'),(24424,'{\"en\":\"Interrogation\",\"visible\":1,\"order\":\"25\"}','[]'),(24425,'{\"en\":\"Ineffective investigation\",\"visible\":1,\"order\":\"26\"}','[]'),(24426,'{\"en\":\"Police custody\",\"visible\":1,\"order\":\"27\"}','[]'),(24427,'{\"en\":\"PTD\",\"visible\":1,\"order\":\"28\"}','[]'),(24428,'{\"en\":\"Pretrial Detention\",\"visible\":1,\"order\":\"29\"}','[]'),(24429,'{\"en\":\"Impunity\",\"visible\":1,\"order\":\"30\"}','[]'),(24430,'{\"en\":\"Nationality\",\"visible\":1,\"order\":\"31\"}','[]'),(24431,'{\"en\":\"Public watchdog\",\"visible\":1,\"order\":\"32\"}','[]'),(24432,'{\"en\":\"NGO\",\"visible\":1,\"order\":\"33\"}','[]'),(24433,'{\"en\":\"Ill-treatment\",\"visible\":1,\"order\":\"34\"}','[]'),(24434,'{\"en\":\"Journalist\",\"visible\":1,\"order\":\"35\"}','[]'),(24435,'{\"en\":\"Defamation\",\"visible\":1,\"order\":\"36\"}','[]'),(24436,'{\"en\":\"Right to life\",\"visible\":1,\"order\":\"37\"}','[]'),(24437,'{\"en\":\"Death in custody\",\"visible\":1,\"order\":\"38\"}','[]'),(24438,'{\"en\":\"Press freedom\",\"visible\":1,\"order\":\"39\"}','[]'),(24439,'{\"en\":\"Racial profiling\",\"visible\":1,\"order\":\"40\"}','[]'),(24440,'{\"en\":\"Fair trial\",\"visible\":1,\"order\":\"41\"}','[]'),(24441,'{\"en\":\"Alex Evdokimov\",\"ru\":\"Alex Evdokimov\",\"iconCls\":\"icon-user-m\",\"visible\":1}','[]'),(24442,'{\"en\":\"Oleg Burlaca\",\"ru\":\"Oleg Burlaca\",\"visible\":1}','[]'),(24443,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24444,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24445,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24446,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24447,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24448,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24449,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24450,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24451,'{\"_title\":\"category\",\"en\":\"Programs\",\"ru\":\"Programs\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"scope\\\": 24265\\n,\\\"value\\\": 24274\\n,\\\"multiValued\\\": true\\n,\\\"editor\\\": \\\"form\\\"\\n}\"}','[]'),(24452,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24453,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24454,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24455,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24456,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24457,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24458,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24459,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24460,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24461,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24462,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24463,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24464,'{\"_title\":\"category\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n                \\\"source\\\": \\\"tree\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"scope\\\": 715\\n                ,\\\"value\\\": \\n            }\",\"en\":\"Category\",\"ru\":\"Category\"}','[]'),(24465,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24466,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24467,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24468,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24469,'{\"_title\":\"_title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\"}\",\"en\":\"Title\",\"ru\":\"Title\"}','[]'),(24470,'{\"_title\":\"allday\",\"type\":\"checkbox\",\"order\":2,\"cfg\":\"{\\\"showIn\\\": \\\"top\\\", \\\"value\\\": 1}\",\"en\":\"All day\",\"ru\":\"All day\"}','[]'),(24471,'{\"_title\":\"date_start\",\"type\":\"date\",\"order\":3,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24472,'{\"_title\":\"date_end\",\"type\":\"date\",\"order\":4,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [1]} }\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24473,'{\"_title\":\"datetime_start\",\"type\":\"datetime\",\"order\":5,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}, \\\"value\\\": \\\"now\\\"}\",\"en\":\"Start\",\"ru\":\"Start\"}','[]'),(24474,'{\"_title\":\"datetime_end\",\"type\":\"datetime\",\"order\":6,\"cfg\":\"{\\\"dependency\\\": {\\\"pidValues\\\": [-1]}}\",\"en\":\"End\",\"ru\":\"End\"}','[]'),(24475,'{\"_title\":\"assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n                \\\"editor\\\": \\\"form\\\"\\n                ,\\\"source\\\": \\\"users\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"multiValued\\\": true\\n            }\",\"en\":\"Assigned\",\"ru\":\"Assigned\"}','[]'),(24476,'{\"_title\":\"importance\",\"type\":\"importance\",\"order\":8,\"cfg\":\"{\\n                \\\"value\\\": 1\\n            }\",\"en\":\"Importance\",\"ru\":\"Importance\"}','[]'),(24477,'{\"_title\":\"category\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n                \\\"source\\\": \\\"tree\\\"\\n                ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n                ,\\\"autoLoad\\\": true\\n                ,\\\"scope\\\": 715\\n                ,\\\"value\\\": \\n            }\",\"en\":\"Category\",\"ru\":\"Category\"}','[]'),(24478,'{\"_title\":\"description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n                \\\"height\\\": 100\\n            }\",\"en\":\"Description\",\"ru\":\"Description\"}','[]'),(24479,'{\"_title\":\"reminders\",\"type\":\"H\",\"order\":11,\"cfg\":\"{\\n                \\\"maxInstances\\\": 5\\n            }\",\"en\":\"Reminders\",\"ru\":\"Reminders\"}','[]'),(24480,'{\"_title\":\"count\",\"type\":\"int\",\"order\":12,\"en\":\"Count\",\"ru\":\"Count\"}','[]'),(24481,'{\"_title\":\"units\",\"type\":\"timeunits\",\"order\":13,\"en\":\"Units\",\"ru\":\"Units\"}','[]'),(24483,'{\"_title\":\"Test ECD group tasks\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-21T12:46:24\",\"date_end\":\"2014-01-28T00:00:00\",\"datetime_start\":\"2014-01-21T12:46:24\"}},\"assigned\":\"1,5,7,8\",\"importance\":1,\"category\":24267,\"reminders\":{\"childs\":[]}}','[]'),(24484,'{\"_title\":\"office\",\"en\":\"Office\",\"ru\":\"Офис\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-object8\",\"title_template\":\"{en}\"}','[]'),(24485,'{\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":5,\"cfg\":null}','[]'),(24486,'{\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":6,\"cfg\":null}','[]'),(24487,'{\"_title\":\"order\",\"type\":\"int\",\"order\":7,\"cfg\":null}','[]'),(24488,'{\"_title\":\"en\",\"type\":\"varchar\",\"order\":0,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24489,'{\"_title\":\"ru\",\"type\":\"varchar\",\"order\":1,\"cfg\":{\"showIn\":\"top\"}}','[]'),(24490,'{\"_title\":\"managers\",\"en\":\"Managers\",\"ru\":\"Менеджеры\",\"type\":\"_objects\",\"order\":3,\"cfg\":\"{\\n\\\"editor\\\": \\\"form\\\"\\n,\\\"source\\\": \\\"users\\\"\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n,\\\"autoLoad\\\": true\\n,\\\"multiValued\\\": true\\n,\\\"faceting\\\": true\\n}\",\"solr_column_name\":\"user_ids\"}','[]'),(24492,'{\"_title\":\"test111\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-21T15:57:28\",\"date_end\":\"2014-01-23T00:00:00\",\"datetime_start\":\"2014-01-21T15:57:28\"}},\"assigned\":\"4\",\"importance\":1,\"category\":24274,\"description\":\"asd\",\"reminders\":{\"childs\":[]}}','[]'),(24495,'[]','[]'),(24496,'[]','[]'),(24497,'[]','[]'),(24503,'[]','[]'),(24504,'{\"en\":\"gray\",\"ru\":\"серый\",\"iconCls\":\"task-gray\",\"visible\":1,\"order\":10}','[]'),(24505,'{\"en\":\"blue\",\"ru\":\"синий\",\"iconCls\":\"task-blue\",\"visible\":1,\"order\":20}','[]'),(24506,'{\"en\":\"green\",\"ru\":\"зелёный\",\"iconCls\":\"task-green\",\"visible\":1,\"order\":30}','[]'),(24507,'{\"en\":\"orange\",\"ru\":\"оранжевый\",\"iconCls\":\"task-orange\",\"visible\":1,\"order\":40}','[]'),(24508,'{\"en\":\"teal\",\"ru\":\"бирюзовый\",\"iconCls\":\"task-teal\",\"visible\":1,\"order\":45}','[]'),(24509,'{\"en\":\"purple\",\"ru\":\"фиолетовый\",\"iconCls\":\"task-purple\",\"visible\":1,\"order\":50}','[]'),(24510,'{\"en\":\"red\",\"ru\":\"красный\",\"iconCls\":\"task-red\",\"visible\":1,\"order\":60}','[]'),(24511,'{\"en\":\"yellow\",\"ru\":\"желтый\",\"iconCls\":\"task-yellow\",\"visible\":1,\"order\":70}','[]'),(24512,'{\"en\":\"olive\",\"ru\":\"оливковый\",\"iconCls\":\"task-olive\",\"visible\":1,\"order\":80}','[]'),(24513,'{\"en\":\"steel\",\"ru\":\"сталь\",\"iconCls\":\"task-steel\",\"visible\":1,\"order\":90}','[]'),(24514,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24515,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24516,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(24517,'{\"_title\":\"security_group\",\"en\":\"Users group\",\"ru\":\"Группа пользователей\",\"type\":\"_objects\",\"order\":2,\"cfg\":\"{\\n\\\"source\\\": \\\"groups\\\"\\n,\\\"autoLoad\\\": true\\n}\"}','[]'),(24521,'{\"_title\":\"Step2\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-22T15:25:35\",\"datetime_start\":\"2014-01-22T15:25:35\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24523,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Название\",\"type\":\"varchar\",\"order\":1}','[]'),(24525,'{\"_title\":\"2222\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-22T16:40:10\",\"datetime_start\":\"2014-01-22T16:40:10\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24526,'{\"status\":24260,\"_title\":\"Test sys folders\"}','[]'),(24527,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24528,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24529,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24530,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24531,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24532,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24533,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24534,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24535,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24536,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24540,'{\"status\":24260,\"_title\":\"Неш Програм Цреате програм тест\",\"program\":\"24267\",\"lead\":\"\",\"support\":\"\"}','[]'),(24541,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24542,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24543,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24544,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24545,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24546,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24547,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24548,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24549,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24550,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24552,'{\"_title\":\"DateTime task 1\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-01-18T09:00:00.000Z\",\"datetime_end\":\"2014-01-27T12:00:00.000Z\"}},\"assigned\":\"6,25,9\",\"importance\":\"1\",\"category\":24274,\"color\":24508,\"description\":\"2\",\"reminders\":{\"childs\":{\"count\":110,\"units\":\"1\"}}}','[]'),(24553,'{\"_title\":\"Selft task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-09T00:00:00\",\"date_end\":\"2014-01-16T23:59:59\",\"datetime_start\":\"2014-01-27T08:06:23.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24554,'{\"_title\":\"Test 2\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-07T00:00:00\",\"date_end\":\"2014-01-14T23:59:59\",\"datetime_start\":\"2014-01-27T08:21:38.000Z\"}},\"assigned\":\"6\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24555,'{\"_title\":\"123\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-06T00:00:00\",\"date_end\":\"2014-01-07T23:59:59\",\"datetime_start\":\"2014-01-27T08:26:29.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24556,'{\"_title\":\"T1\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-27T11:39:29\",\"datetime_start\":\"2014-01-27T09:39:29.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"color\":24506,\"reminders\":{\"childs\":[]}}','[]'),(24557,'{\"_title\":\"T2\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-01-01T00:00:00\",\"date_end\":\"2014-01-02T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24558,'{\"_title\":\"T3\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-08T00:00:00\",\"date_end\":\"2014-01-15T23:59:59\",\"datetime_start\":\"2014-01-28T09:42:52.000Z\"}},\"assigned\":\"1\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24560,'{\"_title\":\"T4\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-29T00:00:00\",\"date_end\":\"2014-01-30T23:59:59\",\"datetime_start\":\"2014-01-28T10:21:51.000Z\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24561,'{\"_title\":\"Task created by Oleg\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-01-29T14:00:00.000Z\",\"datetime_end\":\"2014-01-29T22:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24513,\"description\":\"some description here\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24564,'{\"_title\":\"Scan a document\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-01-29T17:36:43\",\"date_end\":\"2014-01-31T00:00:00\",\"datetime_start\":\"2014-01-29T15:36:43.000Z\"}},\"assigned\":\"263,240\",\"importance\":1,\"category\":\"24267,24271,24266\",\"color\":24512,\"description\":\"Nice info\",\"reminders\":{\"childs\":[]}}','[]'),(24565,'{\"_title\":\"todays task\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-01-31T00:00:00\",\"date_end\":\"2014-02-01T00:00:00\"}},\"assigned\":\"240,262,263\",\"importance\":\"1\",\"category\":24274,\"color\":24513,\"description\":\"cool task\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24577,'{\"_title\":\"New folder\"}','[]'),(24588,'{\"_title\":\"Apples\",\"nr\":\"88776644\",\"_date_start\":\"2014-01-31T00:00:00\",\"_date_end\":\"2014-03-27T00:00:00\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24332\",\"status\":24260,\"tags\":\"24407\",\"lead\":\"256\",\"support\":\"240\"}','[]'),(24589,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24590,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24591,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24592,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24593,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24594,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24595,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24596,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24597,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24598,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24600,'{\"_title\":\"Test\"}','[]'),(24601,'{\"_title\":\"cool case\"}','[]'),(24602,'{\"en\":\"1-Summaries\",\"ru\":\"1-Summaries\",\"visible\":1,\"order\":\"2\"}','[]'),(24603,'{\"en\":\"2-Correspondence\",\"ru\":\"2-Correspondence\",\"visible\":1,\"order\":\"3\"}','[]'),(24604,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24605,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24606,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24607,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24608,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24609,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24610,'{\"en\":\"0-Incoming\",\"ru\":\"0-Incoming\",\"visible\":1,\"order\":\"1\"}','[]'),(24611,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24612,'{\"_title\":\"Test\"}','[]'),(24613,'{\"_title\":\"0-Incoming\"}','[]'),(24614,'{\"_title\":\"0. Incoming\"}','[]'),(24615,'{\"_title\":\"1-Summaries\"}','[]'),(24616,'{\"_title\":\"1. Correspondence\"}','[]'),(24617,'{\"_title\":\"My Case\"}','[]'),(24618,'{\"_title\":\"1-Summaries\"}','[]'),(24619,'{\"_title\":\"2-Correspondence\"}','[]'),(24620,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24621,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24622,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24623,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24624,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24625,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24626,'{\"_title\":\"0-Incoming\"}','[]'),(24627,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24628,'{\"_title\":\"subfolder\"}','[]'),(24629,'{\"_title\":\"another case\",\"nr\":\"02\"}','[]'),(24630,'{\"_title\":\"1-Summaries\"}','[]'),(24631,'{\"_title\":\"2-Correspondence\"}','[]'),(24632,'{\"en\":\"3-Meetings\",\"ru\":\"3-Meetings\",\"visible\":1,\"order\":\"4\"}','[]'),(24633,'{\"en\":\"4-Filings\",\"ru\":\"4-Filings\",\"visible\":1,\"order\":\"5\"}','[]'),(24634,'{\"en\":\"5-OSJI Filings\",\"ru\":\"5-OSJI Filings\",\"visible\":1,\"order\":\"6\"}','[]'),(24635,'{\"en\":\"6-Evidence\",\"ru\":\"6-Evidence\",\"visible\":1,\"order\":\"7\"}','[]'),(24636,'{\"en\":\"7-Advocacy\",\"ru\":\"7-Advocacy\",\"visible\":1,\"order\":\"8\"}','[]'),(24637,'{\"en\":\"9-Administrative\",\"ru\":\"9-Administrative\",\"visible\":1,\"order\":\"10\"}','[]'),(24638,'{\"_title\":\"0-Incoming\"}','[]'),(24639,'{\"en\":\"8-Research\",\"ru\":\"8-Research\",\"visible\":1,\"order\":\"9\"}','[]'),(24640,'{\"_title\":\"subfolder\"}','[]'),(24646,'{\"content\":\"text here\",\"program\":\"24270,24267\",\"_title\":\"some case\",\"_date_start\":\"2014-02-19T00:00:00\",\"tags\":\"24400\"}','[]'),(24647,'{\"_title\":\"3-Meetings\"}','[]'),(24648,'{\"_title\":\"2. Filings\"}','[]'),(24649,'{\"_title\":\"5-Filings\"}','[]'),(24650,'{\"_title\":\"3. Evidence\"}','[]'),(24651,'{\"_title\":\"7-Advocacy\"}','[]'),(24652,'{\"_title\":\"4. Research\"}','[]'),(24653,'{\"_title\":\"5. Administrative\"}','[]'),(24654,'{\"_title\":\"My case here\",\"nr\":\"001\",\"_date_start\":\"2014-02-05T00:00:00\",\"program\":\"24266\",\"status\":24260,\"lead\":\"240\"}','[]'),(24655,'{\"_title\":\"0-Incoming\"}','[]'),(24656,'{\"_title\":\"1-Summaries\"}','[]'),(24657,'{\"_title\":\"2-Correspondence\"}','[]'),(24658,'{\"_title\":\"subfolder\"}','[]'),(24659,'{\"_title\":\"3-Meetings\"}','[]'),(24660,'{\"_title\":\"4-Filings\"}','[]'),(24661,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24662,'{\"_title\":\"6-Evidence\"}','[]'),(24663,'{\"_title\":\"7-Advocacy\"}','[]'),(24664,'{\"_title\":\"8-Research\"}','[]'),(24665,'{\"_title\":\"9-Administrative\"}','[]'),(24666,'{\"_title\":\"Here we go\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-05T00:00:00\",\"date_end\":\"2014-02-08T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24511,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24667,'{\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-12T08:00:00.000Z\",\"datetime_end\":\"2014-02-12T09:00:00.000Z\"}},\"importance\":\"1\",\"category\":24274,\"color\":24505,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24668,'{\"_title\":\"Implement scanning\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-17T00:00:00\",\"date_end\":\"2014-02-20T00:00:00\"}},\"assigned\":\"262,263\",\"importance\":\"1\",\"category\":\"\",\"color\":24509,\"description\":\"and also OCR!\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24669,'{\"_title\":\"Do this\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-03T11:54:37\",\"datetime_start\":\"2014-02-03T09:54:37.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24679,'{\"_title\":\"Bla bla\",\"status\":24260,\"lead\":\"240,256\",\"support\":\"265\"}','[]'),(24680,'{\"_title\":\"0-Incoming\"}','[]'),(24681,'{\"_title\":\"1-Summaries\"}','[]'),(24682,'{\"_title\":\"2-Correspondence\"}','[]'),(24683,'{\"_title\":\"3-Meetings\"}','[]'),(24684,'{\"_title\":\"4-Filings\"}','[]'),(24685,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24686,'{\"_title\":\"6-Evidence\"}','[]'),(24687,'{\"_title\":\"7-Advocacy\"}','[]'),(24688,'{\"_title\":\"8-Research\"}','[]'),(24689,'{\"_title\":\"9-Administrative\"}','[]'),(24691,'{\"_title\":\"Scan papers\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-03T13:38:25\",\"datetime_start\":\"2014-02-03T12:38:25.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24266,24267\",\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24692,'{\"_title\":\"report to anyone\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\",\"date_end\":\"2012-12-31T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24510,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24693,'{\"_title\":\"Finish reporting module for organization B\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-07T15:45:00.000Z\",\"datetime_end\":\"2014-02-13T14:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24511,\"description\":\"Why do tasks have start and end dates?  I think only events should have start and end dates.  Tasks have fixed deadlines.\",\"reminders\":[{\"childs\":[]},{\"childs\":[]}]}','[]'),(24694,'{\"_title\":\"The reminder functions do not work...what do you mean by count and units?\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-13T23:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":\"24274\",\"color\":24508,\"description\":\"Not answered yet...Kindly clarify.\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24695,'{\"_title\":\"Call the lawyer\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-14T15:00:00.000Z\",\"datetime_end\":\"1990-01-11T21:00:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"color\":24511,\"description\":\"the drop downs for programs are still missing\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24696,'{\"_title\":\"Pears\",\"nr\":\"9898989\",\"_date_start\":\"2010-12-13T00:00:00\",\"_date_end\":\"1996-02-12T00:00:00\",\"court\":\"24391,24395,24392,24396\",\"program\":\"24270,24268,24274\",\"country\":\"24339,24330,24332,24322,24337,24329\",\"status\":24261,\"tags\":\"24417\",\"lead\":\"256\",\"support\":\"240\"}','[]'),(24697,'{\"_title\":\"0-Incoming\"}','[]'),(24698,'{\"_title\":\"1-Summaries\"}','[]'),(24699,'{\"_title\":\"2-Correspondence\"}','[]'),(24700,'{\"_title\":\"3-Meetings\"}','[]'),(24701,'{\"_title\":\"4-Filings\"}','[]'),(24702,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24703,'{\"_title\":\"6-Evidence\"}','[]'),(24704,'{\"_title\":\"7-Advocacy\"}','[]'),(24705,'{\"_title\":\"8-Research\"}','[]'),(24706,'{\"_title\":\"9-Administrative\"}','[]'),(24707,'{\"_title\":\".TemporaryItems\"}','[]'),(24708,'{\"_title\":\"folders.501\"}','[]'),(24709,'{\"_title\":\"TemporaryItems\"}','[]'),(24710,'{\"_title\":\"(A Document Being Saved By TextEdit)\"}','[]'),(24713,'{\"_title\":\"(A Document Being Saved By TextEdit)\"}','[]'),(24716,'{\"_title\":\"test\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"3\",\"color\":24505,\"description\":\"text here, and some new text\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24717,'{\"_title\":\"another test\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"264,240\",\"importance\":\"2\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24718,'{\"_title\":\"testing email\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"description\":\"some task here\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24719,'{\"_title\":\"need to do this\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-06T00:00:00Z\",\"date_end\":\"2014-02-27T00:00:00Z\"}},\"assigned\":\"240,256\",\"importance\":\"1\",\"category\":\"24274\",\"color\":24513,\"description\":\"do this before deadline\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24720,'{\"_title\":\"Documents\"}','[]'),(24723,'{\"_title\":\"for Oleg\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T14:44:35\",\"datetime_start\":\"2014-02-06T12:44:35.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"description\":\"this\",\"reminders\":{\"childs\":[]}}','[]'),(24724,'{\"_title\":\"My case W\",\"nr\":\"010\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-03-01T00:00:00Z\",\"court\":\"24391,24395\",\"program\":\"24267,24270\",\"country\":\"24337,24336\",\"status\":24260,\"tags\":\"24418,24435\",\"lead\":\"240,256\",\"support\":\"256,240\"}','[]'),(24725,'{\"_title\":\"0-Incoming\"}','[]'),(24726,'{\"_title\":\"1-Summaries\"}','[]'),(24727,'{\"_title\":\"2-Correspondence\"}','[]'),(24728,'{\"_title\":\"3-Meetings\"}','[]'),(24729,'{\"_title\":\"4-Filings\"}','[]'),(24730,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24731,'{\"_title\":\"6-Evidence\"}','[]'),(24732,'{\"_title\":\"7-Advocacy\"}','[]'),(24733,'{\"_title\":\"8-Research\"}','[]'),(24734,'{\"_title\":\"9-Administrative\"}','[]'),(24735,'{\"_title\":\"one more\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T16:31:31\",\"datetime_start\":\"2014-02-06T14:31:31.000Z\"}},\"assigned\":\"240,232,256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24736,'{\"_title\":\"cool\"}','[]'),(24737,'{\"_title\":\"0-Incoming\"}','[]'),(24738,'{\"_title\":\"1-Summaries\"}','[]'),(24739,'{\"_title\":\"2-Correspondence\"}','[]'),(24740,'{\"_title\":\"3-Meetings\"}','[]'),(24741,'{\"_title\":\"4-Filings\"}','[]'),(24742,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24743,'{\"_title\":\"6-Evidence\"}','[]'),(24744,'{\"_title\":\"7-Advocacy\"}','[]'),(24745,'{\"_title\":\"8-Research\"}','[]'),(24746,'{\"_title\":\"9-Administrative\"}','[]'),(24749,'{\"_title\":\"second apple\",\"program\":\"24266,24267\",\"lead\":\"256\"}','[]'),(24750,'{\"_title\":\"0-Incoming\"}','[]'),(24751,'{\"_title\":\"1-Summaries\"}','[]'),(24752,'{\"_title\":\"2-Correspondence\"}','[]'),(24753,'{\"_title\":\"3-Meetings\"}','[]'),(24754,'{\"_title\":\"4-Filings\"}','[]'),(24755,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24756,'{\"_title\":\"6-Evidence\"}','[]'),(24757,'{\"_title\":\"7-Advocacy\"}','[]'),(24758,'{\"_title\":\"8-Research\"}','[]'),(24759,'{\"_title\":\"9-Administrative\"}','[]'),(24760,'{\"_title\":\"Implement this\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:35:06\",\"datetime_start\":\"2014-02-06T20:35:06.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24761,'{\"_title\":\"notifications doesn\'t work\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:48:57\",\"datetime_start\":\"2014-02-06T20:48:57.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"text here\\n\",\"reminders\":{\"childs\":[]}}','[]'),(24762,'{\"_title\":\"notifications doesn\'t work\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:48:57\",\"datetime_start\":\"2014-02-06T20:48:57.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"text here\\n\",\"reminders\":{\"childs\":[]}}','[]'),(24763,'{\"_title\":\"test that\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T22:50:28\",\"datetime_start\":\"2014-02-06T20:50:28.000Z\"}},\"assigned\":\"240,264\",\"importance\":1,\"category\":24274,\"description\":\"some info here\",\"reminders\":{\"childs\":[]}}','[]'),(24764,'{\"_title\":\"Apples\",\"nr\":\"7878787\",\"_date_start\":\"2014-02-20T00:00:00\",\"_date_end\":\"2014-02-28T00:00:00\",\"court\":\"24391,24397,24394\",\"program\":\"24270,24268,24274\",\"country\":\"24333,24322,24337,24329\",\"status\":24260,\"tags\":\"24435,24401\",\"lead\":\"256\",\"support\":\"7\"}','[]'),(24765,'{\"_title\":\"0-Incoming\"}','[]'),(24766,'{\"_title\":\"1-Summaries\"}','[]'),(24767,'{\"_title\":\"2-Correspondence\"}','[]'),(24768,'{\"_title\":\"3-Meetings\"}','[]'),(24769,'{\"_title\":\"4-Filings\"}','[]'),(24770,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24771,'{\"_title\":\"6-Evidence\"}','[]'),(24772,'{\"_title\":\"7-Advocacy\"}','[]'),(24773,'{\"_title\":\"8-Research\"}','[]'),(24774,'{\"_title\":\"9-Administrative\"}','[]'),(24775,'{\"_title\":\"some action here\",\"_date_start\":\"2014-02-07T00:00:00\",\"tags\":\"24418\"}','[]'),(24776,'{\"_title\":\"Test task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-13T00:00:00\",\"datetime_start\":\"2014-02-06T22:01:19.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"color\":24510,\"description\":\"test the task\",\"reminders\":{\"childs\":{\"count\":2,\"units\":2}}}','[]'),(24778,'{\"_title\":\"Call the prosecutor\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-06T18:52:28\",\"datetime_start\":\"2014-02-06T23:52:28.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":\"24274,24267\",\"color\":24510,\"description\":\"Call the prosecutor at 555-5555 to schedule an appointment for the hearing\",\"reminders\":{\"childs\":{\"count\":2,\"units\":3}}}','[]'),(24779,'{\"_title\":\"Spigunov case\",\"nr\":\"12\",\"_date_start\":\"2014-03-07T00:00:00Z\",\"_date_end\":\"2014-03-21T00:00:00Z\",\"court\":\"24398,24394\",\"office\":\"24269\",\"country\":\"24337,24336\",\"status\":24264,\"tags\":\"24437,24401\",\"lead\":\"240\",\"support\":\"256\"}','[]'),(24780,'{\"_title\":\"0-Incoming\"}','[]'),(24781,'{\"_title\":\"1-Summaries\"}','[]'),(24782,'{\"_title\":\"2-Correspondence\"}','[]'),(24783,'{\"_title\":\"3-Meetings\"}','[]'),(24784,'{\"_title\":\"4-Filings\"}','[]'),(24785,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24786,'{\"_title\":\"6-Evidence\"}','[]'),(24787,'{\"_title\":\"7-Advocacy\"}','[]'),(24788,'{\"_title\":\"8-Research\"}','[]'),(24789,'{\"_title\":\"9-Administrative\"}','[]'),(24790,'{\"_title\":\"a phone call\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-07T00:00:00\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":\"24274,24266\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24791,'{\"_title\":\"Testing notifications from new tasks class\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-07T00:00:00\",\"date_end\":\"2014-02-14T00:00:00\"}},\"assigned\":\"240,1\",\"importance\":\"1\",\"category\":24274,\"color\":24504,\"description\":\"testing notifications on user add \",\"reminders\":{\"childs\":{\"count\":\"111\",\"units\":\"2\"}}}','[]'),(24792,'{\"_title\":\"selftask test\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:09:56\",\"datetime_start\":\"2014-02-07T18:09:56.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24268\",\"reminders\":{\"childs\":[]}}','[]'),(24793,'{\"_title\":\"Can you do this? ... maybe?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:25:32\",\"datetime_start\":\"2014-02-07T18:25:32.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24794,'{\"_title\":\"submit documents\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:36:35\",\"datetime_start\":\"2014-02-07T18:36:35.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24795,'{\"_title\":\"dual task\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-07T20:51:48\",\"datetime_start\":\"2014-02-07T18:51:48.000Z\"}},\"assigned\":\"262\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24801,'{\"_title\":\"Call embassy\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-11T16:00:00.000Z\",\"datetime_end\":\"2014-02-11T16:20:00.000Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"color\":24512,\"reminders\":{\"childs\":{\"count\":15,\"units\":1}}}','[]'),(24805,'{\"_title\":\"tests\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-11T00:00:00\"}},\"importance\":\"2\",\"category\":24274,\"color\":24504,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":{\"count\":2,\"units\":3}}}','[]'),(24806,'{\"_title\":\"testA\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-11T00:00:00\",\"date_end\":\"2014-02-04T00:00:00\"}},\"importance\":2,\"category\":\"24274,24269,24271\",\"color\":24504,\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24810,'{\"_title\":\"test today\",\"nr\":\"01\",\"_date_start\":\"2014-02-20T00:00:00\"}','[]'),(24811,'{\"_title\":\"0-Incoming\"}','[]'),(24812,'{\"_title\":\"1-Summaries\"}','[]'),(24813,'{\"_title\":\"2-Correspondence\"}','[]'),(24814,'{\"_title\":\"3-Meetings\"}','[]'),(24815,'{\"_title\":\"4-Filings\"}','[]'),(24816,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24817,'{\"_title\":\"6-Evidence\"}','[]'),(24818,'{\"_title\":\"7-Advocacy\"}','[]'),(24819,'{\"_title\":\"8-Research\"}','[]'),(24820,'{\"_title\":\"9-Administrative\"}','[]'),(24821,'{\"_title\":\"print letter\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-12T17:25:02\",\"datetime_start\":\"2014-02-12T15:25:02.000Z\"}},\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24822,'{\"_title\":\"Comment\",\"en\":\"Comment\",\"ru\":\"Коментарий\",\"type\":\"comment\",\"visible\":1,\"iconCls\":\"icon-balloon\",\"cfg\":\"{\\n  \\\"systemType\\\": 2\\n}\"}','[]'),(24823,'{\"_title\":\"_title\",\"en\":\"Text\",\"ru\":\"Текст\",\"type\":\"memo\",\"order\":0,\"cfg\":\"{\\n\\\"height\\\": 100\\n}\",\"solr_column_name\":\"content\"}','[]'),(24826,'{\"_title\":\"my first case\"}','[]'),(24827,'{\"_title\":\"a second comment here\"}','[]'),(24828,'{\"_title\":\"something new here\"}','[]'),(24829,'{\"_title\":\"good\"}','[]'),(24830,'{\"_title\":\"too sllow\"}','[]'),(24831,'{\"_title\":\"fast\"}','[]'),(24832,'{\"_title\":\"good\"}','[]'),(24834,'{\"_title\":\"why it\'s so slow\"}','[]'),(24843,'{\"_title\":\"I couldn\'t find the case file.  Could you please send it to me via email?\"}','[]'),(24844,'{\"_title\":\"When I hit enter, will my comment be uploaded?\"}','[]'),(24845,'{\"_title\":\"not sure...seems to be a delay\"}','[]'),(24846,'{\"_title\":\"not sure...seems to be a delay\"}','[]'),(24847,'{\"_title\":\"launch\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-19T00:00:00Z\",\"datetime_start\":\"2014-02-19T10:34:00.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24850,'{\"_title\":\"First task for Osborne\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"\",\"datetime_end\":\"2014-02-26T21:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":\"24270,24267,24266,24272\",\"color\":24506,\"reminders\":{\"childs\":{\"count\":2,\"units\":2}}}','[]'),(24851,'{\"_title\":\"First Event for Osborne\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"2014-03-01T01:00:00.000Z\",\"datetime_end\":\"2014-03-01T03:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":24274,\"color\":24506,\"reminders\":{\"childs\":{\"count\":3,\"units\":2}}}','[]'),(24852,'{\"_title\":\"First Milestone for Osborne\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-24T00:00:00Z\",\"datetime_start\":\"2014-02-21T21:31:08.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":\"24273,24271,24272,24268,24266,24269,24270\",\"color\":24506,\"reminders\":{\"childs\":{\"count\":1,\"units\":2}}}','[]'),(24853,'{\"_title\":\"Task within a task?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:03:26.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"color\":24505,\"reminders\":{\"childs\":[]}}','[]'),(24854,'{\"_title\":\"a Task within the manager\'s calendar?\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\"}},\"assigned\":\"256,240\",\"importance\":\"1\",\"category\":24274,\"description\":\"This task is set for the 21st, but in the calendar it appears on the 20th\\n\",\"reminders\":{\"value\":\"{\\\"childs\\\":[]}\",\"childs\":[]}}','[]'),(24855,'{\"_title\":\"a task within a user?\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:07:07.000Z\"}},\"assigned\":\"\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24856,'{\"_title\":\"This date should be the 22st of February\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-22T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:14:50.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24857,'{\"_title\":\"Tasks are appearing incorrectly on the calendar\"}','[]'),(24858,'{\"_title\":\"this task is supposed to be on the 21st but it appears on the 20th in the calendar Write a comment...\"}','[]'),(24859,'{\"_title\":\"I\'m going to write a long comment to see if the text wraps\"}','[]'),(24860,'{\"_title\":\"but it doesn\'t and now I cannot see the end of my commentWrite a comment...\"}','[]'),(24861,'{\"_title\":\"Write a comment Write a comment...\"}','[]'),(24862,'{\"_title\":\"write a comment stays in the box on the second and third comments.  It should disappearWrite a comment...\"}','[]'),(24863,'{\"_title\":\"WRONG DAY DISPLAYED IN CALENDAR\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-21T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:25:25.000Z\"}},\"assigned\":\"256,240\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24864,'{\"_title\":\"This is my first comment\"}','[]'),(24865,'{\"_title\":\"When I write another comment\"}','[]'),(24866,'{\"_title\":\"But the third time? Write a comment...\"}','[]'),(24867,'{\"_title\":\"Why does it work no?\"}','[]'),(24868,'{\"_title\":\"why does it work?\"}','[]'),(24869,'{\"_title\":\"When before it didnt? what about text wrapping what about that?\"}','[]'),(24870,'{\"_title\":\"I am creating a task by clicking on the calendar\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-14T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:35:01.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24871,'{\"_title\":\"trying the comments again\"}','[]'),(24872,'{\"_title\":\"now checking? Write a comment...\"}','[]'),(24873,'{\"_title\":\"sometimes it works and sometimes not\"}','[]'),(24874,'{\"_title\":\"Helllo!\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-25T00:00:00Z\",\"datetime_start\":\"2014-02-21T22:39:03.000Z\"}},\"assigned\":\"256\",\"importance\":1,\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(24875,'{\"_title\":\"This works\"}','[]'),(24876,'{\"_title\":\"and this does too\"}','[]'),(24877,'{\"_title\":\"this one too\"}','[]'),(24878,'{\"_title\":\"waiting for it\"}','[]'),(24879,'{\"_title\":\"but not happening\"}','[]'),(24880,'{\"_title\":\"hello\"}','[]'),(24881,'{\"_title\":\"what abouWrite a comment...\"}','[]'),(24882,'{\"_title\":\"New Test Case-Edited by Osborne\",\"nr\":\"9898989-Edit\",\"_date_start\":\"2014-03-27T00:00:00Z\",\"_date_end\":\"2014-01-28T00:00:00Z\",\"court\":\"24391,24394,24396,24393\",\"program\":\"24268,24272,24269,24266\",\"country\":\"24336\",\"status\":24262,\"tags\":\"24407,24406,24438,24403,24414,24408\",\"lead\":\"265,256\",\"support\":\"256,265\"}','[]'),(24883,'{\"_title\":\"0-Incoming\"}','[]'),(24884,'{\"_title\":\"1-Summaries\"}','[]'),(24885,'{\"_title\":\"2-Correspondence\"}','[]'),(24886,'{\"_title\":\"3-Meetings\"}','[]'),(24887,'{\"_title\":\"4-Filings\"}','[]'),(24888,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24889,'{\"_title\":\"6-Evidence\"}','[]'),(24890,'{\"_title\":\"7-Advocacy\"}','[]'),(24891,'{\"_title\":\"8-Research\"}','[]'),(24892,'{\"_title\":\"9-Administrative\"}','[]'),(24893,'{\"_title\":\"TestA1\",\"nr\":\"0909\",\"_date_start\":\"2014-02-25T00:00:00Z\",\"_date_end\":\"2014-02-27T00:00:00Z\",\"court\":\"24395\",\"program\":\"24269,24271\",\"country\":\"24333\",\"status\":24264,\"tags\":\"24400\",\"lead\":\"256\",\"support\":\"263,262,265\"}','[]'),(24894,'{\"_title\":\"0-Incoming\"}','[]'),(24895,'{\"_title\":\"1-Summaries\"}','[]'),(24896,'{\"_title\":\"2-Correspondence\"}','[]'),(24897,'{\"_title\":\"3-Meetings\"}','[]'),(24898,'{\"_title\":\"4-Filings\"}','[]'),(24899,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24900,'{\"_title\":\"6-Evidence\"}','[]'),(24901,'{\"_title\":\"7-Advocacy\"}','[]'),(24902,'{\"_title\":\"8-Research\"}','[]'),(24903,'{\"_title\":\"9-Administrative\"}','[]'),(24905,'{\"_title\":\"TestCaseA\",\"nr\":\"002\",\"_date_start\":\"2014-02-26T00:00:00Z\",\"_date_end\":\"2014-02-26T00:00:00Z\",\"court\":\"24391\",\"program\":\"24267\",\"country\":\"24333\",\"status\":24263,\"tags\":\"24400\",\"lead\":\"256\",\"support\":\"263\"}','[]'),(24906,'{\"_title\":\"0-Incoming\"}','[]'),(24907,'{\"_title\":\"1-Summaries\"}','[]'),(24908,'{\"_title\":\"2-Correspondence\"}','[]'),(24909,'{\"_title\":\"3-Meetings\"}','[]'),(24910,'{\"_title\":\"4-Filings\"}','[]'),(24911,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24912,'{\"_title\":\"6-Evidence\"}','[]'),(24913,'{\"_title\":\"7-Advocacy\"}','[]'),(24914,'{\"_title\":\"8-Research\"}','[]'),(24915,'{\"_title\":\"9-Administrative\"}','[]'),(24916,'{\"_title\":\"TESTCASEAO\",\"nr\":\"1234\",\"_date_start\":\"2014-02-11T00:00:00Z\",\"_date_end\":\"2014-02-26T00:00:00Z\",\"court\":\"24395\",\"program\":\"24269\",\"country\":\"24333\",\"status\":24262,\"tags\":\"24400,24407\",\"lead\":\"256\",\"support\":\"262\"}','[]'),(24917,'{\"_title\":\"0-Incoming\"}','[]'),(24918,'{\"_title\":\"1-Summaries\"}','[]'),(24919,'{\"_title\":\"2-Correspondence\"}','[]'),(24920,'{\"_title\":\"3-Meetings\"}','[]'),(24921,'{\"_title\":\"4-Filings\"}','[]'),(24922,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24923,'{\"_title\":\"6-Evidence\"}','[]'),(24924,'{\"_title\":\"7-Advocacy\"}','[]'),(24925,'{\"_title\":\"8-Research\"}','[]'),(24926,'{\"_title\":\"9-Administrative\"}','[]'),(24927,'{\"_title\":\"TestActA0\",\"_date_start\":\"2014-02-26T00:00:00Z\",\"tags\":\"\",\"program\":\"24269\",\"content\":\"<font face=\\\"courier new\\\">This is a test for AO<\\/font>\"}','[]'),(24929,'{\"_title\":\"test\",\"_date_start\":\"2014-02-27T00:00:00Z\"}','[]'),(24930,'{\"_title\":\"case1\"}','[]'),(24931,'{\"_title\":\"0-Incoming\"}','[]'),(24932,'{\"_title\":\"1-Summaries\"}','[]'),(24933,'{\"_title\":\"2-Correspondence\"}','[]'),(24934,'{\"_title\":\"3-Meetings\"}','[]'),(24935,'{\"_title\":\"4-Filings\"}','[]'),(24936,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24937,'{\"_title\":\"6-Evidence\"}','[]'),(24938,'{\"_title\":\"7-Advocacy\"}','[]'),(24939,'{\"_title\":\"8-Research\"}','[]'),(24940,'{\"_title\":\"9-Administrative\"}','[]'),(24941,'{\"_title\":\"case2\"}','[]'),(24942,'{\"_title\":\"0-Incoming\"}','[]'),(24943,'{\"_title\":\"1-Summaries\"}','[]'),(24944,'{\"_title\":\"2-Correspondence\"}','[]'),(24945,'{\"_title\":\"3-Meetings\"}','[]'),(24946,'{\"_title\":\"4-Filings\"}','[]'),(24947,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24948,'{\"_title\":\"6-Evidence\"}','[]'),(24949,'{\"_title\":\"7-Advocacy\"}','[]'),(24950,'{\"_title\":\"8-Research\"}','[]'),(24951,'{\"_title\":\"9-Administrative\"}','[]'),(24954,'{\"_title\":\"Test Osborne\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24395\",\"program\":\"24274,24266,24267\",\"country\":\"24319\",\"status\":24260,\"tags\":\"24415,24435,24416,24400\",\"support\":\"265\"}','[]'),(24955,'{\"_title\":\"0-Incoming\"}','[]'),(24956,'{\"_title\":\"1-Summaries\"}','[]'),(24957,'{\"_title\":\"2-Correspondence\"}','[]'),(24958,'{\"_title\":\"3-Meetings\"}','[]'),(24959,'{\"_title\":\"4-Filings\"}','[]'),(24960,'{\"_title\":\"5-OSJI Filings\"}','[]'),(24961,'{\"_title\":\"6-Evidence\"}','[]'),(24962,'{\"_title\":\"7-Advocacy\"}','[]'),(24963,'{\"_title\":\"8-Research\"}','[]'),(24964,'{\"_title\":\"9-Administrative\"}','[]'),(24965,'{\"_title\":\"TestXA\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"date_end\":\"2014-03-07T00:00:00Z\",\"datetime_start\":\"2014-02-27T08:36:17.000Z\"}},\"assigned\":\"256\",\"importance\":2,\"category\":\"24270,24272\",\"color\":24510,\"reminders\":{\"childs\":[]}}','[]'),(24966,'{\"_title\":\"test\"}','[]'),(24967,'{\"_title\":\"test\"}','[]'),(24968,'{\"_title\":\"limit\"}','[]'),(24969,'{\"_title\":\"limit\"}','[]'),(24970,'{\"_title\":\"still\"}','[]'),(24971,'{\"_title\":\"still\"}','[]'),(24972,'{\"_title\":\"close case\"}','[]'),(24973,'{\"_title\":\"reopen case\"}','[]'),(24974,'{\"_title\":\"^$%%#$@#%$&^\"}','[]'),(24975,'{\"_title\":\",sd,flgtftredzxcsz.,\\/.,\"}','[]'),(24977,'{\"_title\":\"This is test comment to confirm how this works\"}','[]'),(24978,'{\"_title\":\"Testing completion of task.\"}','[]'),(24979,'{\"_title\":\"I have received the task and completed it\"}','[]'),(24980,'{\"_title\":\"Commenting works fine on closing tasks\"}','[]'),(24981,'{\"_title\":\"It seems there is no\"}','[]'),(24982,'{\"_title\":\"limit\"}','[]'),(24983,'{\"_title\":\"on\"}','[]'),(24984,'{\"_title\":\"the\"}','[]'),(24985,'{\"_title\":\"@\"}','[]'),(24986,'{\"_title\":\"#\"}','[]'),(24987,'{\"_title\":\"of\"}','[]'),(24988,'{\"_title\":\"commnents\"}','[]'),(24989,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"datetime_start\":\"2014-02-27T09:09:48.000Z\"}},\"assigned\":\"265,262,263\",\"importance\":1,\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":{\"childs\":{\"count\":10,\"units\":1}}}','[]'),(24990,'{\"_title\":\"Should we be\"}','[]'),(24991,'{\"_title\":\"able to comment after closing a task\"}','[]'),(24992,'{\"_title\":\"?Write a comment...\"}','[]'),(24993,'{\"_title\":\"d\"}','[]'),(24994,'{\"_title\":\"dWrite a comment...\"}','[]'),(24995,'{\"_title\":\"d\"}','[]'),(24996,'{\"_title\":\"dWrite a comment...\"}','[]'),(24997,'{\"_title\":\"dWrite a comment...\"}','[]'),(24998,'{\"_title\":\"sWrite a comment...\"}','[]'),(24999,'{\"_title\":\"eWrite a comment...\"}','[]'),(25000,'{\"_title\":\"gWrite a comment...\"}','[]'),(25001,'{\"_title\":\"words \\\"Write a comment\\\" sometimes come up in the comment also\"}','[]'),(25002,'{\"_title\":\"dWrite a comment...\"}','[]'),(25003,'{\"_title\":\"gWrite a comment...\"}','[]'),(25004,'{\"_title\":\"cWrite a comment...\"}','[]'),(25005,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25006,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25007,'{\"_title\":\"test\"}','[]'),(25008,'{\"_title\":\"sWrite a comment...\"}','[]'),(25009,'{\"_title\":\"Osborne Test 27th\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24395,24397,24393,24396,24398\",\"program\":\"24274,24266,24269,24273\",\"country\":\"24319\",\"status\":24260,\"tags\":\"24415,24416,24435\",\"lead\":\"256\",\"support\":\"265\"}','[]'),(25010,'{\"_title\":\"0-Incoming\"}','[]'),(25011,'{\"_title\":\"1-Summaries\"}','[]'),(25012,'{\"_title\":\"2-Correspondence\"}','[]'),(25013,'{\"_title\":\"3-Meetings\"}','[]'),(25014,'{\"_title\":\"4-Filings\"}','[]'),(25015,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25016,'{\"_title\":\"6-Evidence\"}','[]'),(25017,'{\"_title\":\"7-Advocacy\"}','[]'),(25018,'{\"_title\":\"8-Research\"}','[]'),(25019,'{\"_title\":\"9-Administrative\"}','[]'),(25023,'{\"_title\":\"Still no email notification\"}','[]'),(25030,'{\"_title\":\"hghg\"}','[]'),(25031,'{\"_title\":\"gWrite a comment...\"}','[]'),(25032,'{\"_title\":\"kWrite a comment...\"}','[]'),(25035,'{\"_title\":\"hh\"}','[]'),(25036,'{\"_title\":\"k\"}','[]'),(25037,'{\"_title\":\"dWrite a comment...\"}','[]'),(25038,'{\"_title\":\"Osborne Test in Calendar\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24330\",\"status\":24260,\"tags\":\"24418\",\"lead\":\"265\",\"support\":\"256\"}','[]'),(25039,'{\"_title\":\"0-Incoming\"}','[]'),(25040,'{\"_title\":\"1-Summaries\"}','[]'),(25041,'{\"_title\":\"2-Correspondence\"}','[]'),(25042,'{\"_title\":\"3-Meetings\"}','[]'),(25043,'{\"_title\":\"4-Filings\"}','[]'),(25044,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25045,'{\"_title\":\"6-Evidence\"}','[]'),(25046,'{\"_title\":\"7-Advocacy\"}','[]'),(25047,'{\"_title\":\"8-Research\"}','[]'),(25048,'{\"_title\":\"9-Administrative\"}','[]'),(25049,'{\"_title\":\"added case in calendar view\"}','[]'),(25050,'{\"_title\":\"something has to be done about wrapping long comments like thiiiiiissssssss onnnnnneeeeeee.......add a scroll bar here\"}','[]'),(25051,'{\"_title\":\"Osborne Test in Calendar\",\"nr\":\"1234\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"_date_end\":\"2014-02-28T00:00:00Z\",\"court\":\"24391\",\"program\":\"24274\",\"country\":\"24330\",\"status\":24260,\"tags\":\"24418\",\"lead\":\"265\",\"support\":\"256\"}','[]'),(25052,'{\"_title\":\"0-Incoming\"}','[]'),(25053,'{\"_title\":\"1-Summaries\"}','[]'),(25054,'{\"_title\":\"2-Correspondence\"}','[]'),(25055,'{\"_title\":\"3-Meetings\"}','[]'),(25056,'{\"_title\":\"4-Filings\"}','[]'),(25057,'{\"_title\":\"5-OSJI Filings\"}','[]'),(25058,'{\"_title\":\"6-Evidence\"}','[]'),(25059,'{\"_title\":\"7-Advocacy\"}','[]'),(25060,'{\"_title\":\"8-Research\"}','[]'),(25061,'{\"_title\":\"9-Administrative\"}','[]'),(25062,'{\"_title\":\"added case in calendar view\"}','[]'),(25063,'{\"_title\":\"is the end date supposed to saved if it is earlier than the Start date?\"}','[]'),(25064,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\"}},\"assigned\":\"265,262,263\",\"importance\":\"1\",\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":{\"childs\":{\"count\":1,\"units\":\"1\"}}}','[]'),(25065,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25066,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25067,'{\"_title\":\"TestAO1\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-27T00:00:00.000000Z\",\"date_end\":null}},\"assigned\":\"265,262,263\",\"importance\":\"1\",\"category\":\"24274,24270\",\"color\":24509,\"description\":\"Test Task\",\"reminders\":[{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}],\"category_id\":\"24274\"}','[]'),(25068,'{\"_title\":\"just completed the task testAO1\"}','[]'),(25069,'{\"_title\":\"Is this your test Abraham?I am closing it to test closing\"}','[]'),(25070,'{\"_title\":\"Test event\",\"allday\":{\"value\":-1,\"childs\":{\"date_start\":null,\"date_end\":null,\"datetime_start\":\"\",\"datetime_end\":null}},\"assigned\":\"265\",\"importance\":\"3\",\"category\":24274,\"color\":24506,\"description\":\"do u get the notification?\",\"reminders\":{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}}','[]'),(25071,'{\"_title\":\"Test milestone\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-02-27T00:00:00Z\",\"date_end\":\"2014-02-18T00:00:00Z\",\"datetime_start\":\"2014-02-27T13:42:58.000Z\"}},\"assigned\":\"265\",\"importance\":1,\"category\":24274,\"color\":24509,\"description\":\"test\",\"reminders\":{\"childs\":{\"count\":1,\"units\":1}}}','[]'),(25072,'{\"_title\":\"Test Action\",\"_date_start\":\"2014-02-27T00:00:00Z\",\"program\":\"24274\",\"content\":\"test content for action<br>\"}','[]'),(25073,'{\"_title\":\"Test action\",\"_date_start\":\"2014-02-28T00:00:00Z\",\"tags\":\"\",\"program\":\"24274,24266\",\"content\":\"Not really sure what adding action does..The tags field has no values in the pop up windows. Searching some of the terms used in the tags field in other content types still yields no result.<br>\"}','[]'),(25074,'{\"_title\":\"Test comment on manager\'s task....\"}','[]'),(25075,'{\"_title\":\"Test 01\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-02-27T21:00:00.000Z\",\"datetime_end\":\"2014-03-06T21:00:00.000Z\"}},\"assigned\":\"265\",\"importance\":\"3\",\"category\":24274,\"color\":24506,\"description\":\"Test edited\",\"reminders\":{\"childs\":{\"count\":\"1\",\"units\":\"1\"}}}','[]'),(25076,'{\"_title\":\"edits\"}','[]'),(25077,'{\"_title\":\"ana test\"}','[]'),(25078,'{\"_title\":\"Scan papers\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-02-12T00:00:00Z\",\"date_end\":\"\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(25079,'{\"_title\":\"Petrov vs. Argentina\",\"nr\":\"123\",\"_date_start\":\"2014-03-14T00:00:00Z\",\"court\":\"24391,24396\",\"office\":\"24272,24266\",\"country\":\"24322,24339\",\"status\":24260,\"tags\":\"24418,24435\",\"lead\":\"240\"}','[]'),(25080,'{\"_title\":\"0. Incoming\"}','[]'),(25081,'{\"_title\":\"1. Correspondence\"}','[]'),(25082,'{\"_title\":\"2. Filings\"}','[]'),(25083,'{\"_title\":\"3. Evidence\"}','[]'),(25084,'{\"_title\":\"4. Research\"}','[]'),(25085,'{\"_title\":\"5. Administrative\"}','[]'),(25086,'{\"_title\":\"Letter from the court\",\"_date_start\":\"2014-03-27T00:00:00Z\",\"office\":\"24273,24272\"}','[]'),(25087,'{\"_title\":\"Petrov vs. Zambia\",\"nr\":\"456\",\"_date_start\":\"2014-03-19T00:00:00Z\",\"court\":\"24393,24391,24398\",\"office\":\"24270\",\"country\":\"24338\",\"status\":24260,\"tags\":\"24421,24437,24418\",\"lead\":\"266\",\"support\":\"240\"}','[]'),(25088,'{\"_title\":\"0. Incoming\"}','[]'),(25089,'{\"_title\":\"1. Correspondence\"}','[]'),(25090,'{\"_title\":\"2. Filings\"}','[]'),(25091,'{\"_title\":\"3. Evidence\"}','[]'),(25092,'{\"_title\":\"4. Research\"}','[]'),(25093,'{\"_title\":\"5. Administrative\"}','[]'),(25094,'{\"_title\":\"Written request \",\"_date_start\":\"2014-02-01T00:00:00Z\",\"office\":\"24266\",\"color\":24513,\"content\":\"\"}','[]'),(25095,'{\"_title\":\"in_links\",\"en\":\"Following  Actions\",\"ru\":\"Following  Action\",\"type\":\"_objects\",\"order\":11,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\",\\n\\\"scope\\\": \\\"project\\\",\\n\\\"templates\\\": [24195],\\n\\\"descendants\\\": true,\\n\\\"multiValued\\\":true,\\n\\\"renderer\\\": \\\"listObjIcons\\\",\\n\\\"editor\\\": \\\"form\\\"  \\n}\"}','[]'),(25097,'{\"_title\":\"test\",\"content\":\"\",\"in_links\":\"25094\"}','[]'),(25099,'{\"_title\":\"out_links\",\"en\":\"Outgoing links\",\"ru\":\"Outgoing links\",\"type\":\"_objects\",\"order\":11,\"cfg\":\"{\\n\\\"scope\\\": \\\"project\\\",\\n\\\"templates\\\": [24195],\\n\\\"descendants\\\": true,\\n\\\"renderer\\\": \\\"listObjIcons\\\",\\n\\\"editor\\\": \\\"form\\\"  \\n}\"}','[]'),(25100,'{\"_title\":\"out_links\",\"en\":\"Preceding Actions\",\"ru\":\"Preceding Actions\",\"type\":\"_objects\",\"order\":10,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\",\\n\\\"scope\\\": \\\"project\\\",\\n\\\"templates\\\": [24195],\\n\\\"descendants\\\": true,\\n\\\"renderer\\\": \\\"listObjIcons\\\",\\n\\\"editor\\\": \\\"form\\\"  \\n}\"}','[]'),(25101,'{\"_title\":\"Decision to extend the investigation\",\"_date_start\":\"2014-02-02T00:00:00Z\",\"color\":24511,\"content\":\"\",\"in_links\":\"25094\"}','[]'),(25102,'{\"_title\":\"Decision to not open a criminal case\",\"_date_start\":\"2014-02-03T00:00:00Z\",\"color\":24506,\"content\":\"\",\"in_links\":\"25101\",\"out_links\":\"25101\"}','[]'),(25103,'{\"_title\":\"Applicant asking if there are any news\",\"_date_start\":\"2014-02-04T00:00:00Z\",\"color\":24513,\"content\":\"\"}','[]'),(25104,'{\"_title\":\"Complaint\",\"_date_start\":\"2014-02-05T00:00:00Z\",\"content\":\"\"}','[]'),(25105,'{\"_title\":\"Application sent \",\"_date_start\":\"2014-03-07T00:00:00Z\",\"content\":\"\"}','[]'),(25106,'{\"_title\":\"Case communicated\",\"_date_start\":\"2014-02-08T00:00:00Z\",\"tags\":\"\",\"content\":\"\",\"in_links\":\"25104\"}','[]'),(25107,'{\"_title\":\"Decision on admissibility\",\"_date_start\":\"2014-02-09T00:00:00Z\",\"content\":\"\",\"in_links\":\"25106\"}','[]'),(25108,'{\"_title\":\"Judgement on the merits \",\"_date_start\":\"2014-02-12T00:00:00Z\",\"content\":\"\"}','[]'),(25109,'{\"_title\":\"Communication with the state\",\"_date_start\":\"2014-02-15T00:00:00Z\",\"content\":\"\"}','[]'),(25110,'{\"_title\":\"Send letter to the registrar\",\"_date_start\":\"2014-02-15T00:00:00Z\",\"content\":\"\"}','[]'),(25112,NULL,'{\"subscribers\":{\"on\":[\"240\"],\"off\":[]}}'),(25113,'{\"_title\":\"The 4th chapter needs revisions. please provide more evidence\"}','[]'),(25114,'{\"_title\":\"Cases\"}','[]'),(25117,'{\"_title\":\"color\",\"en\":\"Color\",\"ru\":\"Цвет\",\"type\":\"_objects\",\"order\":9,\"cfg\":\"{\\n\\\"source\\\": \\\"tree\\\"\\n,\\\"scope\\\": 24503\\n,\\\"autoLoad\\\": true\\n,\\\"renderer\\\": \\\"listObjIcons\\\"\\n}\"}','[]'),(25118,'{\"_title\":\"Prepare a draft complaint\",\"_date_start\":\"2014-02-18T00:00:00Z\",\"color\":24506,\"content\":\"\"}','[]'),(25121,'{\"_title\":\"Krasuski vs. Poland\",\"nr\":\"124\",\"_date_start\":\"2013-11-11T00:00:00Z\",\"court\":\"24391\",\"office\":\"24269\",\"country\":\"24316\",\"status\":24260,\"tags\":\"24440,24433\",\"lead\":\"267\",\"support\":\"269\"}','{\"subscribers\":{\"on\":[\"267\"],\"off\":[]}}'),(25122,'{\"_title\":\"0. Incoming\"}','[]'),(25123,'{\"_title\":\"1. Correspondence\"}','[]'),(25124,'{\"_title\":\"2. Filings\"}','[]'),(25125,'{\"_title\":\"3. Evidence\"}','[]'),(25126,'{\"_title\":\"4. Research\"}','[]'),(25127,'{\"_title\":\"5. Administrative\"}','[]'),(25128,'{\"_title\":\"Zhuk vs. Belarus\",\"nr\":\"61\",\"_date_start\":\"2012-02-14T00:00:00Z\",\"_date_end\":\"2013-10-15T00:00:00Z\",\"court\":\"24393\",\"office\":\"24269,24267\",\"country\":\"25135\",\"status\":24261,\"tags\":\"24440,24412\",\"lead\":\"269\",\"support\":\"270\"}','[]'),(25129,'{\"_title\":\"0. Incoming\"}','[]'),(25130,'{\"_title\":\"1. Correspondence\"}','[]'),(25131,'{\"_title\":\"2. Filings\"}','[]'),(25132,'{\"_title\":\"3. Evidence\"}','[]'),(25133,'{\"_title\":\"4. Research\"}','[]'),(25134,'{\"_title\":\"5. Administrative\"}','[]'),(25135,'{\"en\":\"Belarus\",\"ru\":\"Беларусь\",\"visible\":1}','[]'),(25139,'{\"_title\":\"María Claudia García Gelman vs. Uruguay\",\"nr\":\"2006 - 21\",\"_date_start\":\"2006-02-08T00:00:00Z\",\"court\":\"24394\",\"office\":\"24270,24267\",\"country\":\"25147\",\"status\":24260,\"tags\":\"25149,24403\",\"lead\":\"266\",\"support\":\"269,268\"}','[]'),(25140,'{\"_title\":\"0. Incoming\"}','[]'),(25141,'{\"_title\":\"1. Correspondence\"}','[]'),(25142,'{\"_title\":\"2. Filings\"}','[]'),(25143,'{\"_title\":\"3. Evidence\"}','[]'),(25144,'{\"_title\":\"4. Research\"}','[]'),(25145,'{\"_title\":\"5. Administrative\"}','[]'),(25146,'{\"_title\":\"Send case summary for 2013 Report on Right to Fair Trial\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-26T00:00:00Z\"}},\"assigned\":\"270\",\"importance\":\"2\",\"category\":\"24269\",\"description\":\"Please send the summary for the 2013 Report on the Right to the Fair Trial. Note the communications of the HRC.\",\"reminders\":{\"childs\":[]}}','[]'),(25147,'{\"en\":\"Uruguay\",\"ru\":\"Uruguay\"}','[]'),(25149,'{\"en\":\"Adoption\",\"ru\":\"Adoption\",\"visible\":1,\"order\":8}','[]'),(25154,'{\"_title\":\"Condemnation of Execution\",\"_date_start\":\"2010-04-18T00:00:00Z\",\"tags\":\"24436,24433,24440\",\"office\":\"24269\",\"content\":\"\"}','[]'),(25155,'{\"_title\":\"Gelman press conference\",\"_date_start\":\"2014-03-31T00:00:00Z\",\"tags\":\"25149,24403\",\"office\":\"24270\",\"content\":\"\"}','[]'),(25156,'{\"_title\":\"Prepare the press release\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-20T00:00:00Z\",\"date_end\":\"\"}},\"assigned\":\"269,268\",\"importance\":\"3\",\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(25157,'{\"_title\":\"Invite journalists\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-21T00:00:00Z\"}},\"assigned\":\"268\",\"importance\":\"1\",\"category\":24274,\"description\":\"Please can you look up the media database and send me the shortlist of journalists we need to invite to the press conference, so we can call  them up individually to brief them about the case.\",\"reminders\":{\"childs\":[]}}','[]'),(25158,'{\"_title\":\"Follow up with Svetlana Zhuk\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-17T00:00:00Z\",\"date_end\":\"2014-03-20T00:00:00Z\"}},\"assigned\":\"270\",\"importance\":\"2\",\"category\":\"24269\",\"color\":24508,\"description\":\"Please follow up with the mother of the victim and ask her, if there are any services we need to provide and if there have been reprisals against her due to campaigning.\\n\",\"reminders\":{\"childs\":[]}}','[]'),(25159,'{\"_title\":\"Venue for press conference\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-18T00:00:00Z\"}},\"assigned\":\"240\",\"importance\":\"1\",\"category\":\"24267\",\"description\":\"Hi Erik, please can you take care of the venue preparations for the Gelman press conference? Lets use the Montevideo room as usual, make sure the videoconferencing works as we\'ll have some journalists attending from our Washington office.\",\"reminders\":{\"childs\":[]}}','[]'),(25161,'{\"_title\":\"Gelman appeal\",\"_date_start\":\"2014-03-27T00:00:00Z\",\"content\":\"\"}','[]'),(25162,'{\"_title\":\"Prepare Response to Request from Special Rapporteur\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-15T00:00:00Z\"}},\"assigned\":\"270\",\"importance\":\"2\",\"category\":\"24269\",\"color\":24510,\"description\":\"Please prepare the response to the Special Rapporteur on the situation of human rights in Belarus \",\"reminders\":{\"childs\":[]}}','[]'),(25163,'{\"_title\":\"Submit response to Special Rapporteur\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-24T00:00:00Z\"}},\"assigned\":\"269\",\"importance\":\"1\",\"category\":\"24269\",\"color\":24506,\"description\":\"Submit our response about background of the case to Special Rapporteur on the situation of human rights in Belarus \",\"reminders\":{\"childs\":[]}}','[]'),(25167,'{\"_title\":\"Do additional case law research\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-03-18T00:00:00Z\",\"datetime_start\":\"2014-03-02T22:52:58.000Z\"}},\"assigned\":\"269\",\"importance\":1,\"category\":\"24269\",\"description\":\"Please do additional case law research using echr.ketse.com\\n\",\"reminders\":{\"childs\":[]}}','{\"subscribers\":{\"on\":[\"266\"],\"off\":[]}}'),(25168,'{\"_title\":\"Interview Krasuski in Warsaw prison\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-04-08T00:00:00Z\"}},\"assigned\":\"267,269\",\"importance\":\"1\",\"category\":\"24269\",\"description\":\"Interview scheduled with the victim in cooperation with the Polish NGO Court Watch Poland Foundation\",\"reminders\":{\"childs\":[]}}','[]'),(25170,'{\"_title\":\"E.E. vs. Russia\",\"nr\":\"23\",\"_date_start\":\"2010-08-26T00:00:00Z\",\"_date_end\":\"2013-05-24T00:00:00Z\",\"court\":\"24395\",\"office\":\"24266\",\"country\":\"24313\",\"status\":24261,\"tags\":\"24403,24426\",\"lead\":\"270\",\"support\":\"269\"}','[]'),(25171,'{\"_title\":\"0. Incoming\"}','[]'),(25172,'{\"_title\":\"1. Correspondence\"}','[]'),(25173,'{\"_title\":\"2. Filings\"}','[]'),(25174,'{\"_title\":\"3. Evidence\"}','[]'),(25175,'{\"_title\":\"4. Research\"}','[]'),(25176,'{\"_title\":\"5. Administrative\"}','[]'),(25185,'{\"_title\":\"Brief on progress of the case\",\"allday\":{\"value\":\"-1\",\"childs\":{\"datetime_start\":\"2014-03-28T11:00:00.000Z\",\"datetime_end\":\"2014-03-28T13:00:00.000Z\"}},\"assigned\":\"269\",\"importance\":\"1\",\"category\":24274,\"reminders\":{\"childs\":[]}}','[]'),(25186,'{\"_title\":\"Privacy International (on behalf of Tadesse Kersmo) vs.  UK\",\"nr\":\"333\",\"_date_start\":\"2014-02-17T00:00:00Z\",\"court\":\"24398\",\"office\":\"24269\",\"country\":\"25193,25194\",\"status\":24260,\"tags\":\"25195\",\"lead\":\"267\",\"support\":\"266,268\"}','[]'),(25187,'{\"_title\":\"0. Incoming\"}','[]'),(25188,'{\"_title\":\"1. Correspondence\"}','[]'),(25189,'{\"_title\":\"2. Filings\"}','[]'),(25190,'{\"_title\":\"3. Evidence\"}','[]'),(25191,'{\"_title\":\"4. Research\"}','[]'),(25192,'{\"_title\":\"5. Administrative\"}','[]'),(25193,'{\"en\":\"United Kingdom\",\"ru\":\"United Kingdom\",\"visible\":1}','[]'),(25194,'{\"en\":\"Ethiopia\",\"ru\":\"Ethiopia\",\"visible\":1}','[]'),(25195,'{\"en\":\"Privacy\",\"ru\":\"Privacy\",\"visible\":1}','[]'),(25199,'{\"_title\":\"Complaint to National Cyber Crime Unit of the National Crime Agency\",\"_date_start\":\"2014-03-07T00:00:00Z\",\"tags\":\"25195\",\"office\":\"24269\",\"color\":24512,\"content\":\"\"}','[]'),(25200,'{\"_title\":\"This is an important case, as it will set a precedent regarding the right to fair trial. Please focus, so we will have all documents at our finger tips all the time, and coordinate effectively, using Casebox.\"}','[]'),(25201,'{\"_title\":\"Domestic Cases\"}','[]'),(25202,'{\"_title\":\"Media request from the Guardian - Interview\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-03-06T00:00:00Z\",\"datetime_start\":\"2014-03-03T03:08:45.000Z\"}},\"assigned\":\"268\",\"importance\":3,\"category\":\"24269\",\"description\":\"Please coordinate with Tom Smith from the Guardian for the interview they have requested.\",\"reminders\":{\"childs\":[]}}','[]'),(25205,'{\"_title\":\"I am really interested in the results for an upcoming meeting I have with a journalist from the Independent. Could you brief me once you\'re done?\"}','[]'),(25206,'{\"_title\":\"RightsCon vs. NSA, GCHQ\",\"nr\":\"911\",\"_date_start\":\"2014-03-03T00:00:00Z\",\"_date_end\":\"2014-03-05T00:00:00Z\",\"court\":\"24398,24394\",\"office\":\"24272,24273\",\"country\":\"25193,25213\",\"status\":24264,\"tags\":\"24418,25195,24414\",\"lead\":\"269\",\"support\":\"266,268\"}','[]'),(25207,'{\"_title\":\"0. Incoming\"}','[]'),(25208,'{\"_title\":\"1. Correspondence\"}','[]'),(25209,'{\"_title\":\"2. Filings\"}','[]'),(25210,'{\"_title\":\"3. Evidence\"}','[]'),(25211,'{\"_title\":\"4. Research\"}','[]'),(25212,'{\"_title\":\"5. Administrative\"}','[]'),(25213,'{\"en\":\"United States\",\"ru\":\"United States\",\"visible\":1}','[]'),(25214,'{\"_title\":\"Demonstration in the Fishbowl\",\"allday\":{\"value\":\"1\",\"childs\":{\"date_start\":\"2014-03-03T00:00:00Z\"}},\"assigned\":\"266\",\"importance\":\"3\",\"category\":\"24274,24272,24269\",\"description\":\"Rally the participants, create slogans, inform media. #rightscon #privacyisaright\\n\",\"reminders\":{\"childs\":[]}}','[]'),(25217,NULL,'{\"subscribers\":{\"on\":[269,267,\"266\"],\"off\":[]}}'),(25218,'{\"_title\":\"We really need to improve our security. When we take on this case, we can be sure that they will be after us.\"}','[]'),(25219,'{\"_title\":\"Yes, I fully agree. Please, everyone, let\'s get this going asap.\"}','[]'),(25220,'{\"_title\":\"But there\'s nothing much we can do against  it. If they want to get in, they will. Let\'s spend our time more wisely than trying to fight a fight we cannot win. The tools are clunky, too.\"}','[]'),(25221,'{\"_title\":\"I disagree. Snowden said that properly implemented encryption works. So we will make it work for us as well.\"}','[]'),(25225,'{\"_title\":\"John v. US\",\"nr\":\"037\",\"_date_start\":\"2014-03-12T00:00:00Z\",\"court\":\"24398\",\"office\":\"24268,24267\",\"country\":\"25213\",\"status\":24260,\"lead\":\"240\",\"support\":\"266,268\"}','[]'),(25226,'{\"_title\":\"0. Incoming\"}','[]'),(25227,'{\"_title\":\"1. Correspondence\"}','[]'),(25228,'{\"_title\":\"2. Filings\"}','[]'),(25229,'{\"_title\":\"3. Evidence\"}','[]'),(25230,'{\"_title\":\"4. Research\"}','[]'),(25231,'{\"_title\":\"5. Administrative\"}','[]'),(25232,'{\"_title\":\"Final Post of Charles Taylor Trial Blog\",\"_date_start\":\"2014-02-01T00:00:00Z\",\"content\":\"\"}','[]'),(25233,'{\"_title\":\"Exchange of Thanks: Victims and Special Court Prosector Express Gratitude in Sierra Leone\",\"_date_start\":\"2014-02-10T00:00:00Z\",\"content\":\"\",\"out_links\":\"25234,25235\"}','[]'),(25234,'{\"_title\":\"In Sierra Leone, Victims Celebrate Taylor’s Conviction\",\"_date_start\":\"2014-02-08T00:00:00Z\",\"content\":\"\",\"out_links\":\"25235\"}','[]'),(25235,'{\"_title\":\"Charles Taylor’s Conviction and Sentence Upheld: What next for him?\",\"_date_start\":\"2014-02-07T00:00:00Z\",\"color\":24507,\"content\":\"\",\"out_links\":\"25232\",\"in_links\":\"\"}','[]'),(25236,'{\"_title\":\"Appeals Chamber Upholds Taylor’s Jail Sentence\",\"_date_start\":\"2014-02-10T00:00:00Z\",\"content\":\"\"}','[]'),(25237,'{\"_title\":\"Charles Taylor’s Fate: Will He Be Back in Liberia?\",\"_date_start\":\"2014-02-11T00:00:00Z\",\"content\":\"\",\"out_links\":\"25233,25232\"}','[]'),(25238,'{\"_title\":\"Resources Ahead of Taylor Appeal Judgment\",\"_date_start\":\"2014-02-12T00:00:00Z\",\"color\":24506,\"content\":\"\"}','[]'),(25239,'{\"_title\":\"Special Court Announces Date of Taylor Appeal Judgment\",\"_date_start\":\"2014-02-14T00:00:00Z\",\"color\":24510,\"content\":\"\",\"out_links\":\"25233,25236\",\"in_links\":\"25232\"}','[]'),(25240,'{\"_title\":\"Charles Taylor’s Former Investigator Sentenced to Two and Half years in Jail\",\"_date_start\":\"2014-02-15T00:00:00Z\",\"content\":\"\",\"out_links\":\"25236\",\"in_links\":\"25234\"}','[]'),(25241,'{\"_title\":\"Appeals Chamber Concludes Oral Hearings in Charles Taylor’s Appeal\",\"_date_start\":\"2014-02-15T00:00:00Z\",\"content\":\"\"}','[]'),(25242,'{\"_title\":\"Parties in Taylor Trial Make Appeals Submissions\",\"_date_start\":\"2014-02-16T00:00:00Z\",\"color\":24506,\"content\":\"\"}','[]'),(25243,'{\"_title\":\"Charles Taylor Oral Appeal Hearings–Tuesday Jan. 22 and Wednesday Jan. 23 2012\",\"_date_start\":\"2014-02-17T00:00:00Z\",\"color\":24511,\"content\":\"\"}','[]'),(25244,'{\"_title\":\"Appeals Hearing in Taylor Case Postponed\",\"_date_start\":\"2014-02-18T00:00:00Z\",\"color\":24511,\"content\":\"\",\"out_links\":\"25243,25242\"}','[]'),(25245,'{\"_title\":\"Why the Special Court for Sierra Leone Should Establish an Independent Commission to Address Alternate Judge Sow’s Allegation in the Charles Taylor Case\",\"_date_start\":\"2014-02-18T00:00:00Z\",\"color\":24511,\"content\":\"\"}','[]'),(25246,'{\"_title\":\"Public Reaction in Sierra Leone to the Judgment of Charles Taylor\",\"_date_start\":\"2014-02-19T00:00:00Z\",\"color\":24510,\"content\":\"\",\"out_links\":\"25243\",\"in_links\":\"25245\"}','[]'),(25247,'{\"_title\":\"Prosecution and Defense to Appeal Charles Taylor Judgment and Sentence\",\"_date_start\":\"2014-02-15T00:00:00Z\",\"color\":24511,\"content\":\"\",\"out_links\":\"25234,25235\",\"in_links\":\"25238\"}','[]'),(25248,'{\"_title\":\"Charles Taylor Sentenced to 50 Years in Jail\",\"_date_start\":\"2014-02-20T00:00:00Z\",\"color\":24511,\"content\":\"\",\"out_links\":\"25246\"}','[]'),(25249,'{\"_title\":\"At Prosecution and Defense Oral Arguments on Sentencing, Charles Taylor Makes Public Statement\",\"_date_start\":\"2014-02-22T00:00:00Z\",\"color\":24510,\"content\":\"\",\"out_links\":\"25248\",\"in_links\":\"25245\"}','[]'),(25250,'{\"_title\":\"Prepare a draft reply for the court\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-04-01T00:00:00Z\",\"datetime_start\":\"2014-04-01T09:51:42.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24267\",\"color\":24505,\"description\":\"Use the latest evidence that I\'ve uploaded to the case, and write the letter\",\"reminders\":{\"childs\":[]}}','[]'),(25251,'{\"_title\":\"Send me the bill for your last trip to UK\",\"allday\":{\"value\":1,\"childs\":{\"date_start\":\"2014-04-01T00:00:00Z\",\"datetime_start\":\"2014-04-01T09:56:56.000Z\"}},\"assigned\":\"240\",\"importance\":1,\"category\":\"24267\",\"description\":\"Erik, don\'t forget to invoice us for you research trip in London\\n\",\"reminders\":{\"childs\":[]}}','[]');

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

insert  into `sessions`(`id`,`pid`,`last_action`,`expires`,`user_id`,`data`) values ('lrasd5lddkuh0s62ck3di9a180','lrasd5lddkuh0s62ck3di9a180','2015-02-17 09:19:36',NULL,0,'');

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
) ENGINE=InnoDB AUTO_INCREMENT=25252 DEFAULT CHARSET=utf8;

/*Data for the table `tasks` */

insert  into `tasks`(`id`,`object_id`,`title`,`date_start`,`date_end`,`allday`,`importance`,`category_id`,`type`,`privacy`,`responsible_user_ids`,`autoclose`,`description`,`parent_ids`,`child_ids`,`time`,`reminds`,`status`,`missed`,`completed`,`cid`,`cdate`,`uid`,`udate`) values (25146,NULL,'Send case summary for 2013 Report on Right to Fair Trial','2014-03-26 00:00:00',NULL,1,2,24269,0,0,'270',1,'Please send the summary for the 2013 Report on the Right to the Fair Trial. Note the communications of the HRC.',NULL,NULL,NULL,NULL,2,NULL,NULL,269,'2014-03-02 21:23:19',1,'0000-00-00 00:00:00'),(25156,NULL,'Prepare the press release','2014-03-20 00:00:00',NULL,1,3,24274,0,0,'269,268',1,NULL,NULL,NULL,NULL,NULL,2,NULL,NULL,266,'2014-03-02 21:43:34',1,'0000-00-00 00:00:00'),(25157,NULL,'Invite journalists','2014-03-21 00:00:00',NULL,1,1,24274,0,0,'268',1,'Please can you look up the media database and send me the shortlist of journalists we need to invite to the press conference, so we can call  them up individually to brief them about the case.',NULL,NULL,NULL,NULL,2,NULL,NULL,266,'2014-03-02 21:45:42',1,'0000-00-00 00:00:00'),(25158,NULL,'Follow up with Svetlana Zhuk','2014-03-17 00:00:00','2014-03-20 00:00:00',1,2,24269,0,0,'270',1,'Please follow up with the mother of the victim and ask her, if there are any services we need to provide and if there have been reprisals against her due to campaigning.\n',NULL,NULL,NULL,NULL,1,1,NULL,269,'2014-03-02 21:48:56',1,'0000-00-00 00:00:00'),(25159,NULL,'Venue for press conference','2014-03-18 00:00:00',NULL,1,1,24267,0,0,'240',1,'Hi Erik, please can you take care of the venue preparations for the Gelman press conference? Lets use the Montevideo room as usual, make sure the videoconferencing works as we\'ll have some journalists attending from our Washington office.',NULL,NULL,NULL,NULL,2,NULL,NULL,266,'2014-03-02 21:50:53',1,'0000-00-00 00:00:00'),(25162,NULL,'Prepare Response to Request from Special Rapporteur','2014-03-15 00:00:00',NULL,1,2,24269,0,0,'270',1,'Please prepare the response to the Special Rapporteur on the situation of human rights in Belarus ',NULL,NULL,NULL,NULL,1,NULL,NULL,269,'2014-03-02 21:55:03',1,'0000-00-00 00:00:00'),(25163,NULL,'Submit response to Special Rapporteur','2014-03-24 00:00:00',NULL,1,1,24269,0,0,'269',1,'Submit our response about background of the case to Special Rapporteur on the situation of human rights in Belarus ',NULL,NULL,NULL,NULL,2,NULL,NULL,269,'2014-03-02 22:08:24',1,'0000-00-00 00:00:00'),(25167,NULL,'Do additional case law research','2014-03-18 00:00:00',NULL,1,1,24269,0,0,'269',1,'Please do additional case law research using echr.ketse.com\n',NULL,NULL,NULL,NULL,2,NULL,NULL,269,'2014-03-02 22:53:57',1,'0000-00-00 00:00:00'),(25168,NULL,'Interview Krasuski in Warsaw prison','2014-04-08 00:00:00',NULL,1,1,24269,0,0,'267,269',1,'Interview scheduled with the victim in cooperation with the Polish NGO Court Watch Poland Foundation',NULL,NULL,NULL,NULL,2,NULL,NULL,269,'2014-03-02 22:56:54',1,'0000-00-00 00:00:00'),(25185,NULL,'Brief on progress of the case','2014-03-28 11:00:00','2014-03-28 13:00:00',-1,1,24274,0,0,'269',1,NULL,NULL,NULL,NULL,NULL,1,0,NULL,267,'2014-03-03 02:01:54',1,'0000-00-00 00:00:00'),(25202,NULL,'Media request from the Guardian - Interview','2014-03-06 00:00:00',NULL,1,3,24269,0,0,'268',1,'Please coordinate with Tom Smith from the Guardian for the interview they have requested.',NULL,NULL,NULL,NULL,2,NULL,NULL,267,'2014-03-03 03:09:56',1,'0000-00-00 00:00:00'),(25214,NULL,'Demonstration in the Fishbowl','2014-03-03 00:00:00',NULL,1,3,24274,0,0,'266',1,'Rally the participants, create slogans, inform media. #rightscon #privacyisaright\n',NULL,NULL,NULL,NULL,2,NULL,NULL,267,'2014-03-03 03:37:30',1,'0000-00-00 00:00:00'),(25250,NULL,'Prepare a draft reply for the court','2014-04-01 00:00:00',NULL,1,1,24267,0,0,'240',1,'Use the latest evidence that I\'ve uploaded to the case, and write the letter',NULL,NULL,NULL,NULL,2,NULL,NULL,269,'2014-04-01 09:53:09',1,'0000-00-00 00:00:00'),(25251,NULL,'Send me the bill for your last trip to UK','2014-04-01 00:00:00',NULL,1,1,24267,0,0,'240',1,'Erik, don\'t forget to invoice us for you research trip in London\n',NULL,NULL,NULL,NULL,2,NULL,NULL,268,'2014-04-01 09:57:59',1,'0000-00-00 00:00:00');

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

insert  into `tasks_reminders`(`task_id`,`user_id`,`reminds`) values (25146,267,''),(25146,269,''),(25156,266,''),(25157,266,''),(25158,267,''),(25158,269,''),(25159,240,''),(25159,266,''),(25162,267,''),(25162,269,''),(25163,267,''),(25163,269,''),(25167,269,''),(25168,267,''),(25168,269,''),(25185,267,''),(25202,267,''),(25214,267,''),(25250,269,''),(25251,268,'');

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

insert  into `tasks_responsible_users`(`task_id`,`user_id`,`status`,`thesauri_response_id`,`time`) values (25146,270,0,NULL,NULL),(25156,268,0,NULL,NULL),(25156,269,0,NULL,NULL),(25157,268,0,NULL,NULL),(25158,270,0,NULL,NULL),(25159,240,0,NULL,NULL),(25162,270,0,NULL,NULL),(25163,269,0,NULL,NULL),(25167,269,0,NULL,NULL),(25168,267,0,NULL,NULL),(25168,269,0,NULL,NULL),(25185,269,0,NULL,NULL),(25202,268,0,NULL,NULL),(25214,266,0,NULL,NULL),(25250,240,0,NULL,NULL),(25251,240,0,NULL,NULL);

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `is_folder` tinyint(1) unsigned DEFAULT '0',
  `type` enum('case','object','file','task','user','email','template','field','search','comment','shortcut') DEFAULT NULL,
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

insert  into `templates`(`id`,`pid`,`is_folder`,`type`,`name`,`l1`,`l2`,`l3`,`l4`,`order`,`visible`,`iconCls`,`default_field`,`cfg`,`title_template`,`info_template`) values (24043,24042,0,'field','Fields template','Fields template','Fields template','Fields template','Fields template',0,1,'icon-snippet',NULL,'[]',NULL,NULL),(24044,24042,0,'template','Templates template','Templates template','Templates template','Templates template','Templates template',0,1,'icon-template',NULL,'[]',NULL,NULL),(24052,24042,1,'','system','System','System','Система',NULL,2,1,'icon-folder',NULL,NULL,NULL,NULL),(24053,24052,0,'user','User','User',NULL,'Пользователь',NULL,1,1,'icon-object4',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(24067,24052,0,'email','email','Email','Email','Email',NULL,2,1,'icon-mail',NULL,'{\"files\":1,\"main_file\":\"1\"}',NULL,NULL),(24072,24052,0,'task','tasks','Task','Task','Task','Task',3,1,'icon-task',NULL,'{\"data\":{\"type\":6}}','{name}',NULL),(24073,24052,0,'task','event','Event','Event','Event','Event',4,1,'icon-event',NULL,'{\"data\":{\"type\":7}}','{name}',NULL),(24074,24052,0,'object','folder','Folder','Folder','Folder','Folder',5,1,'icon-folder',NULL,'{\"createMethod\":\"inline\",\n\n  \"object_plugins\":\n      [\"objectProperties\",\n       \"comments\",\n       \"systemProperties\"\n      ]\n\n}','{name}',NULL),(24075,24052,0,'file','file_template','File','File','File','File',6,1,'file-',NULL,'[]','{name}',NULL),(24078,24052,0,'task','milestone','Milestone','Milestone','Milestone','Milestone',4,1,'i-flag',NULL,'[]','{name}',NULL),(24079,24042,0,'case','case_template','Case','Case','Case','Case',1,1,'icon-briefcase',NULL,'{\"system_folders\": 24248}','{name}',NULL),(24195,24042,0,'object','Action','Action','Action','Action',NULL,1,1,'icon-petition',NULL,'[]',NULL,NULL),(24217,24052,0,'object','Thesauri Item','Thesauri item','Thesauri item','Thesauri item','Thesauri item',0,1,'icon-blue-document-small',NULL,NULL,'{en}',NULL),(24484,24042,0,'object','office','Program item','Program item','Program item','Program item',0,1,'icon-object8',NULL,NULL,'{en}',NULL),(24822,24042,0,'comment','Comment',NULL,NULL,NULL,NULL,0,1,'icon-balloon',NULL,'{\n  \"systemType\": 2\n}',NULL,NULL);

/*Table structure for table `templates_structure` */

DROP TABLE IF EXISTS `templates_structure`;

CREATE TABLE `templates_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `template_id` int(11) unsigned NOT NULL,
  `tag` varchar(30) DEFAULT NULL,
  `level` smallint(6) unsigned DEFAULT '0',
  `name` varchar(1000) NOT NULL,
  `l1` varchar(1000) DEFAULT NULL,
  `l2` varchar(1000) DEFAULT NULL,
  `l3` varchar(1000) DEFAULT NULL,
  `l4` varchar(1000) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL COMMENT 'varchar,date,time,int,bool,text,combo,popup_list',
  `order` smallint(6) unsigned DEFAULT '0',
  `cfg` text,
  `solr_column_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `templates_structure_pid` (`pid`),
  KEY `templates_structure_template_id` (`template_id`),
  KEY `idx_templates_structure_type` (`type`),
  CONSTRAINT `FK_templates_structure__template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25118 DEFAULT CHARSET=utf8;

/*Data for the table `templates_structure` */

insert  into `templates_structure`(`id`,`pid`,`template_id`,`tag`,`level`,`name`,`l1`,`l2`,`l3`,`l4`,`type`,`order`,`cfg`,`solr_column_name`) values (24054,24053,24053,'f',0,'l1','Full name (en)','Nom complet (en)','Полное имя (en)',NULL,'varchar',1,'[]',NULL),(24055,24053,24053,'f',0,'l2','Full name (fr)','Nom complet (fr)','Полное имя (fr)',NULL,'varchar',2,'[]',NULL),(24056,24053,24053,'f',0,'l3','Full name (ru)','Nom complet (ru)','Полное имя (ru)',NULL,'varchar',3,'[]',NULL),(24057,24053,24053,'f',0,'initials','Initials','Initiales','Инициалы',NULL,'varchar',4,'[]',NULL),(24058,24053,24053,'f',0,'sex','Sex','Sexe','Пол',NULL,'_sex',5,'{\"thesauriId\":\"90\"}',NULL),(24059,24053,24053,'f',0,'position','Position','Titre','Должность',NULL,'_objects',7,'{\"source\":\"tree\",\"scope\":24340,\"oldThesauriId\":\"362\"}',NULL),(24060,24053,24053,'f',0,'email','E-mail','E-mail','E-mail',NULL,'varchar',9,'{\"maxInstances\":\"3\"}',NULL),(24061,24053,24053,'f',0,'language_id','Language','Langue','Язык',NULL,'_language',11,'[]',NULL),(24062,24053,24053,'f',0,'short_date_format','Date format','Format de date','Формат даты',NULL,'_short_date_format',12,'[]',NULL),(24063,24053,24053,'f',0,'description','Description','Description','Примечание',NULL,'varchar',13,'[]',NULL),(24064,24053,24053,'f',0,'room','Room','Salle','Кабинет',NULL,'varchar',8,'[]',NULL),(24065,24053,24053,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',10,'{\"maxInstances\":\"10\"}',NULL),(24066,24053,24053,'f',0,'location','Location','Emplacement','Расположение',NULL,'_objects',6,'{\"source\":\"tree\",\"scope\":24373,\"oldThesauriId\":\"394\"}',NULL),(24068,24067,24067,'f',0,'_title','Subject','Sujet','Название',NULL,'varchar',0,'{\"showIn\": \"top\"}',NULL),(24069,24067,24067,'f',0,'_date_start','Date','Date','Дата',NULL,'date',1,'{\"showIn\": \"top\"}','date_start'),(24070,24067,24067,'f',0,'from','From','D\'après','От',NULL,'varchar',3,'{\"thesauriId\":\"73\"}','strings'),(24071,24067,24067,'f',0,'_content','Content','Teneur','Содержание',NULL,'html',1,'{\"showIn\": \"tabsheet\"}','texts'),(24076,24075,24075,'f',0,'program','Program','Program','Program','Program','_objects',1,'{\"source\":\"tree\",\"multiValued\":true,\"autoLoad\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"faceting\":true,\"scope\":24265,\"oldThesauriId\":\"715\"}',NULL),(24077,24075,24075,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\"multiple\":\"true\",\"faceting\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"autoLoad\":true,\"source\":\"tree\",\"scope\":24399,\"oldThesauriId\":\"760\"}',NULL),(24080,24079,24079,'f',0,'_title','Name','Name','Name','Name','varchar',1,'{\"showIn\":\"top\"}',NULL),(24081,24079,24079,'f',0,'nr','Number','Number','Number','Number','varchar',2,'{\"showIn\":\"top\"}',NULL),(24082,24079,24079,'f',0,'_date_start','Date','Date','Date','Date','date',3,'{\"showIn\":\"top\"}',NULL),(24083,24079,24079,'f',0,'_date_end','End date','End date','End date','End date','date',4,'{\"showIn\":\"top\"}',NULL),(24085,24079,24079,'f',0,'lead','Lead','Lead','Lead','Lead','_objects',21,'{\"editor\":\"form\",\"source\":\"users\",\"renderer\":\"listObjIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true}','role_ids2'),(24086,24079,24079,'f',0,'support','Support','Support','Support','Support','_objects',22,'{\"editor\":\"form\",\"source\":\"users\",\"renderer\":\"listObjIcons\",\"autoLoad\":true,\"multiValued\":true,\"faceting\":true}','role_ids3'),(24087,24079,24079,'f',0,'court','Court','Court','Court','Court','_objects',5,'{\n\"scope\":\"24390\"\n,\"editor\":\"form\"\n,\"source\": \"tree\"\n,\"renderer\": \"listGreenIcons\"\n,\"autoLoad\": true\n,\"multiValued\": true\n,\"faceting\": true\n}',NULL),(24088,24079,24079,'f',0,'office','Office','Офис','Program','Program','_objects',6,'{\n\"source\": \"tree\"\n,\"scope\": \"24265\"\n,\"multiValued\": true\n,\"autoLoad\": true\n,\"editor\": \"form\"\n,\"renderer\": \"listGreenIcons\"\n,\"faceting\": true\n}',NULL),(24089,24079,24079,'f',0,'status','Status','Status','Status','Status','_objects',8,'{\n\"source\":\"tree\"\n,\"scope\": \"24259\"\n,\"multiValued\": false\n,\"autoLoad\": true\n,\"faceting\": true\n}','status'),(24090,24079,24079,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',10,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24399\n,\"multiValued\": true\n,\"editor\": \"form\"\n}\n',NULL),(24091,24079,24079,'f',0,'country','Country','Country','Country','Country','_objects',7,'{\n\"scope\":\"24308\"\n,\"editor\":\"form\"\n,\"source\": \"tree\"\n,\"renderer\": \"listGreenIcons\"\n,\"autoLoad\": true\n,\"multiValued\": true\n,\"faceting\": true\n}',NULL),(24196,24195,24195,'f',0,'_title','Title','Title','Title','Title','varchar',1,'{\"showIn\":\"top\"}',NULL),(24197,24195,24195,'f',0,'_date_start','Date','Date','Date','Date','date',2,'{\"showIn\":\"top\"}',NULL),(24198,24195,24195,'f',0,'content','Content','Content','Content','Content','html',10,'{\"showIn\": \"tabsheet\"}',NULL),(24199,24195,24195,'f',0,'office','Office','Офис','Program','Program','_objects',5,'{\n\"source\": \"tree\"\n,\"renderer\": \"listGreenIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"multiValued\": true\n,\"editor\": \"form\"\n}\n',NULL),(24200,24195,24195,'f',0,'tags','Tags','Tags','Tags','Tags','_objects',3,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24399\n,\"multiValued\": true\n,\"editor\": \"form\"\n}\n',NULL),(24201,24043,24043,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\"}',NULL),(24202,24043,24043,NULL,0,'type','Type','Type','Type','Type','_fieldTypesCombo',5,'[]',NULL),(24203,24043,24043,NULL,0,'order','Order','Order','Order','Order','int',6,'[]',NULL),(24204,24043,24043,NULL,0,'cfg','Config','Config','Config','Config','memo',7,'{\"height\":100}',NULL),(24205,24043,24043,NULL,0,'solr_column_name','Solr column name','Solr column name','Solr column name','Solr column name','varchar',8,'[]',NULL),(24206,24043,24043,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(24207,24043,24043,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',2,'[]',NULL),(24208,24044,24044,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\",\"rea-dOnly\":true}',NULL),(24209,24044,24044,NULL,0,'type','Type','Type','Type','Type','_templateTypesCombo',5,'[]',NULL),(24210,24044,24044,NULL,0,'visible','Active','Active','Active','Active','checkbox',6,'{\"showIn\":\"top\"}',NULL),(24211,24044,24044,NULL,0,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',7,'[]',NULL),(24212,24044,24044,NULL,0,'cfg','Config','Config','Config','Config','text',8,'{\"height\":100}',NULL),(24213,24044,24044,NULL,0,'title_template','Title template','Title template','Title template','Title template','text',9,'{\"height\":50}',NULL),(24214,24044,24044,NULL,0,'info_template','Info template','Info template','Info template','Info template','text',10,'{\"height\":50}',NULL),(24215,24044,24044,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(24216,24044,24044,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',2,'[]',NULL),(24218,24217,24217,NULL,0,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',5,'[]',NULL),(24219,24217,24217,NULL,0,'visible','Active','Active','Active','Active','checkbox',6,'[]',NULL),(24220,24217,24217,NULL,0,'order','Order','Order','Order','Order','int',7,'[]',NULL),(24221,24217,24217,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',0,'{\"showIn\":\"top\"}',NULL),(24222,24217,24217,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',1,'{\"showIn\":\"top\"}',NULL),(24443,24072,24072,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24444,24072,24072,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24445,24444,24072,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24446,24444,24072,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24447,24444,24072,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24448,24444,24072,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24449,24072,24072,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24450,24072,24072,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24451,24072,24072,NULL,0,'category','Programs','Programs',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n,\"multiValued\": true\n,\"editor\": \"form\"\n}',NULL),(24452,24072,24072,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24453,24072,24072,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24454,24453,24072,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24455,24453,24072,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24456,24073,24073,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24457,24073,24073,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24458,24457,24073,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24459,24457,24073,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24460,24457,24073,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24461,24457,24073,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24462,24073,24073,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24463,24073,24073,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24464,24073,24073,NULL,0,'category','Category','Category',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"editor\": \"form\"\r\n,\"multiValued\": true\r\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n}',NULL),(24465,24073,24073,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24466,24073,24073,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24467,24466,24073,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24468,24466,24073,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24469,24078,24078,NULL,NULL,'_title','Title','Title',NULL,NULL,'varchar',1,'{\"showIn\": \"top\"}',NULL),(24470,24078,24078,NULL,NULL,'allday','All day','All day',NULL,NULL,'checkbox',2,'{\"showIn\": \"top\", \"value\": 1}',NULL),(24471,24470,24078,NULL,NULL,'date_start','Start','Start',NULL,NULL,'date',3,'{\"dependency\": {\"pidValues\": [1]}, \"value\": \"now\"}',NULL),(24472,24470,24078,NULL,NULL,'date_end','End','End',NULL,NULL,'date',4,'{\"dependency\": {\"pidValues\": [1]} }',NULL),(24473,24470,24078,NULL,NULL,'datetime_start','Start','Start',NULL,NULL,'datetime',5,'{\"dependency\": {\"pidValues\": [-1]}, \"value\": \"now\"}',NULL),(24474,24470,24078,NULL,NULL,'datetime_end','End','End',NULL,NULL,'datetime',6,'{\"dependency\": {\"pidValues\": [-1]}}',NULL),(24475,24078,24078,NULL,NULL,'assigned','Assigned','Assigned',NULL,NULL,'_objects',7,'{\n                \"editor\": \"form\"\n                ,\"source\": \"users\"\n                ,\"renderer\": \"listObjIcons\"\n                ,\"autoLoad\": true\n                ,\"multiValued\": true\n            }',NULL),(24476,24078,24078,NULL,NULL,'importance','Importance','Importance',NULL,NULL,'importance',8,'{\n                \"value\": 1\n            }',NULL),(24477,24078,24078,NULL,0,'category','Category','Category',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"editor\": \"form\"\r\n,\"multiValued\": true\r\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"scope\": 24265\n,\"value\": 24274\n}',NULL),(24478,24078,24078,NULL,NULL,'description','Description','Description',NULL,NULL,'memo',10,'{\n                \"height\": 100\n            }',NULL),(24479,24078,24078,NULL,NULL,'reminders','Reminders','Reminders',NULL,NULL,'H',11,'{\n                \"maxInstances\": 5\n            }',NULL),(24480,24479,24078,NULL,NULL,'count','Count','Count',NULL,NULL,'int',12,NULL,NULL),(24481,24479,24078,NULL,NULL,'units','Units','Units',NULL,NULL,'timeunits',13,NULL,NULL),(24485,24484,24484,NULL,NULL,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',5,'[]',NULL),(24486,24484,24484,NULL,NULL,'visible','Active','Active','Active','Active','checkbox',6,'[]',NULL),(24487,24484,24484,NULL,NULL,'order','Order','Order','Order','Order','int',7,'[]',NULL),(24488,24484,24484,NULL,NULL,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',0,'{\"showIn\":\"top\"}',NULL),(24489,24484,24484,NULL,NULL,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',1,'{\"showIn\":\"top\"}',NULL),(24490,24484,24484,NULL,0,'managers','Managers','Менеджеры',NULL,NULL,'_objects',3,'{\n\"editor\": \"form\"\n,\"source\": \"users\"\n,\"renderer\": \"listObjIcons\"\n,\"autoLoad\": true\n,\"multiValued\": true\n,\"faceting\": true\n}','user_ids'),(24514,24072,24072,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24515,24078,24078,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24516,24073,24073,NULL,0,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\n\"source\": \"tree\"\n,\"scope\": 24503\n,\"autoLoad\": true\n,\"renderer\": \"listObjIcons\"\n}',NULL),(24517,24484,24484,NULL,0,'security_group','Users group','Группа пользователей',NULL,NULL,'_objects',2,'{\n\"source\": \"groups\"\n,\"autoLoad\": true\n}',NULL),(24523,24074,24074,NULL,NULL,'_title','Name','Название',NULL,NULL,'varchar',1,NULL,NULL),(24823,24822,24822,NULL,NULL,'_title','Text','Текст',NULL,NULL,'memo',0,'{\n\"height\": 100\n}','content'),(25095,24195,24195,NULL,0,'in_links','Following  Actions','Following  Action',NULL,NULL,'_objects',11,'{\n\"source\": \"tree\",\n\"scope\": \"project\",\n\"templates\": [24195],\n\"descendants\": true,\n\"multiValued\":true,\n\"renderer\": \"listObjIcons\",\n\"editor\": \"form\"  \n}',NULL),(25100,24195,24195,NULL,0,'out_links','Preceding Actions','Preceding Actions',NULL,NULL,'_objects',10,'{\n\"source\": \"tree\",\n\"scope\": \"project\",\n\"templates\": [24195],\n\"descendants\": true,\n\"renderer\": \"listObjIcons\",\n\"editor\": \"form\"  \n}',NULL),(25117,24195,24195,NULL,NULL,'color','Color','Цвет',NULL,NULL,'_objects',9,'{\"source\":\"tree\",\"scope\":24503,\"autoLoad\":true,\"renderer\":\"listObjIcons\"}',NULL);

/*Table structure for table `translations` */

DROP TABLE IF EXISTS `translations`;

CREATE TABLE `translations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `name` varbinary(100) DEFAULT NULL,
  `en` varchar(250) DEFAULT NULL,
  `es` varchar(250) DEFAULT NULL,
  `ge` varchar(250) DEFAULT NULL,
  `fr` varchar(250) DEFAULT NULL,
  `hy` varchar(250) DEFAULT NULL,
  `pt` varchar(250) DEFAULT NULL,
  `ro` varchar(250) DEFAULT NULL,
  `ru` varchar(250) DEFAULT NULL,
  `ar` varchar(1000) DEFAULT NULL,
  `zh` varchar(1000) DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - anywhere, 1 - server, 2 - client',
  `udate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `info` varchar(1000) DEFAULT NULL COMMENT 'Where in CB the term is used, what it means',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - not deleted, 1 - deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_translations__name` (`name`),
  KEY `FK_translations__pid` (`pid`),
  KEY `FK_translations_udate` (`udate`),
  CONSTRAINT `FK_translations__pid` FOREIGN KEY (`pid`) REFERENCES `translations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
  `draft` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `draft_pid` varchar(10) DEFAULT NULL COMMENT 'used to attach other objects to a non existing, yet creating item',
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
  KEY `tree_draft` (`draft`),
  CONSTRAINT `tree_pid` FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25252 DEFAULT CHARSET=utf8;

/*Data for the table `tree` */

insert  into `tree`(`id`,`pid`,`user_id`,`system`,`type`,`draft`,`draft_pid`,`template_id`,`tag_id`,`target_id`,`name`,`date`,`date_end`,`size`,`is_main`,`cfg`,`inherit_acl`,`cid`,`cdate`,`uid`,`udate`,`updated`,`oid`,`did`,`ddate`,`dstatus`) values (1,NULL,NULL,1,1,0,NULL,24074,NULL,NULL,'Tree',NULL,NULL,NULL,1,'[]',0,1,'2012-11-17 17:10:21',1,'2014-01-17 13:53:00',0,1,NULL,NULL,0),(23432,NULL,49,1,1,0,NULL,24074,NULL,NULL,'[Favorites]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23433,23432,49,1,1,0,NULL,24074,NULL,NULL,'[Recent]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23434,NULL,49,1,1,0,NULL,24074,NULL,NULL,'[MyCaseBox]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23435,23434,49,1,1,0,NULL,24074,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23436,23434,49,1,1,0,NULL,24074,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23437,23436,49,1,1,0,NULL,24074,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23438,23436,49,1,1,0,NULL,24074,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23439,23436,49,1,1,0,NULL,24074,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23440,23434,49,1,1,0,NULL,24074,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23441,23440,49,1,1,0,NULL,24074,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23442,23440,49,1,1,0,NULL,24074,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23443,23434,49,1,1,0,NULL,24074,NULL,NULL,'[PrivateArea]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23444,NULL,NULL,1,1,0,NULL,24074,NULL,NULL,'Casebox',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23445,23444,NULL,1,1,0,NULL,24074,NULL,NULL,'[Cases]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23446,23444,NULL,1,1,0,NULL,24074,NULL,NULL,'[Tasks]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23447,23446,NULL,1,1,0,NULL,24074,NULL,NULL,'[Upcoming]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23448,23446,NULL,1,1,0,NULL,24074,NULL,NULL,'[Missed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23449,23446,NULL,1,1,0,NULL,24074,NULL,NULL,'[Closed]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23450,23444,NULL,1,1,0,NULL,24074,NULL,NULL,'[Messages]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23451,23450,NULL,1,1,0,NULL,24074,NULL,NULL,'[New]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23452,23450,NULL,1,1,0,NULL,24074,NULL,NULL,'[Unread]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-01-09 13:36:51',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23490,1,227,0,1,0,NULL,24074,NULL,NULL,'ECHR Cases',NULL,NULL,NULL,NULL,'[]',1,227,'2013-01-15 10:56:59',1,'2014-01-17 13:53:07',0,227,NULL,NULL,0),(23730,NULL,1,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-03-28 08:10:02',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23731,23730,1,1,1,0,NULL,24074,NULL,NULL,'[Emails]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-03-28 08:20:03',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23732,23730,1,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-04-22 13:27:18',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23733,NULL,239,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-17 08:48:05',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23734,23733,239,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-17 08:48:05',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23735,NULL,232,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-23 17:05:04',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23736,23735,232,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-23 17:05:04',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23741,NULL,240,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-26 08:18:39',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23742,23741,240,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-26 08:18:39',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23744,NULL,241,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-05-27 13:13:54',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23745,23744,241,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-05-27 13:13:54',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23748,23734,NULL,0,4,0,NULL,24195,NULL,NULL,'Action test 31.05.2013','2013-05-31 00:00:00','2013-05-31 00:00:00',NULL,NULL,'[]',1,239,'2013-05-31 13:40:29',1,'2014-01-17 14:11:18',0,239,NULL,NULL,0),(23807,NULL,250,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-06-04 10:44:29',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23808,23807,250,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-06-04 10:44:29',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23809,NULL,7,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-06-04 12:21:09',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23810,23809,7,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-06-04 12:21:09',1,'2014-01-17 13:53:07',0,1,NULL,NULL,0),(23811,23808,NULL,0,5,0,NULL,24075,NULL,NULL,'oe2_geotag.PNG','2013-06-04 13:06:35','2013-06-04 13:06:35',117605,NULL,'[]',1,250,'2013-06-04 13:06:35',1,'2014-01-17 14:11:17',0,250,NULL,NULL,0),(23815,23734,NULL,0,4,0,NULL,24079,NULL,NULL,'12','2013-06-18 00:00:00','0000-00-00 00:00:00',NULL,NULL,'[]',1,239,'2013-06-18 14:13:45',1,'2014-01-17 14:11:17',0,239,NULL,NULL,0),(23816,23815,NULL,0,4,0,NULL,24074,289,NULL,'1-Summaries','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23817,23815,NULL,0,4,0,NULL,24074,290,NULL,'2-Correspondence','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23818,23815,NULL,0,4,0,NULL,24074,291,NULL,'3-Meetings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23819,23815,NULL,0,4,0,NULL,24074,292,NULL,'4-Filings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23820,23815,NULL,0,4,0,NULL,24074,293,NULL,'5-OSJI Filings','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23821,23815,NULL,0,4,0,NULL,24074,294,NULL,'6-Evidence','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23822,23815,NULL,0,4,0,NULL,24074,295,NULL,'7-Advocacy','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23823,23815,NULL,0,4,0,NULL,24074,297,NULL,'9-Administrative','2013-06-18 14:13:46',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:46',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23824,23815,NULL,0,4,0,NULL,24074,712,NULL,'0-Incoming','2013-06-18 14:13:47',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:47',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23825,23815,NULL,0,4,0,NULL,24074,713,NULL,'8-Research','2013-06-18 14:13:47',NULL,NULL,NULL,'[]',1,239,'2013-06-18 14:13:47',1,'2014-01-17 13:53:07',0,239,NULL,NULL,0),(23883,NULL,254,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-09-24 13:13:13',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23884,23883,254,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-09-24 13:13:13',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23885,NULL,256,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\":\"UsersHomeFolder\"}',1,1,'2013-09-24 14:07:42',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23886,23885,256,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,'[]',1,1,'2013-09-24 14:07:42',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(23940,1,NULL,0,4,0,NULL,24074,NULL,NULL,'Thesauri','2013-09-24 19:38:09',NULL,NULL,NULL,'[]',1,256,'2013-09-24 19:38:09',1,'2014-01-17 13:53:08',0,256,NULL,NULL,0),(24042,1,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Templates',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(24043,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'Fields template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(24044,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'Templates template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(24052,24042,NULL,0,NULL,0,NULL,24074,NULL,NULL,'System',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(24053,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'User',NULL,NULL,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24054,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'l1',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24055,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'l2',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24056,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'l3',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24057,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'initials',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24058,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'sex',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24059,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'position',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24060,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'email',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24061,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'language_id',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24062,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'short_date_format',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24063,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24064,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'room',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24065,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'phone',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24066,24053,NULL,0,NULL,0,NULL,24043,NULL,NULL,'location',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24067,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'email',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24068,24067,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24069,24067,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24070,24067,NULL,0,NULL,0,NULL,24043,NULL,NULL,'from',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24071,24067,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_content',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24072,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'tasks',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24073,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'event',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24074,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'folder',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',240,'2014-02-28 20:01:55',0,1,NULL,NULL,0),(24075,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'file_template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24076,24075,NULL,0,NULL,0,NULL,24043,NULL,NULL,'program',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24077,24075,NULL,0,NULL,0,NULL,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24078,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'milestone',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-01-17 14:21:08',0,1,NULL,NULL,0),(24079,24042,NULL,0,NULL,0,NULL,24044,NULL,NULL,'case_template',NULL,NULL,NULL,NULL,'{\"system_folders\":\"350\"}',1,1,'2014-01-17 13:50:50',1,'2014-01-23 08:31:16',0,1,NULL,NULL,0),(24080,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24081,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'nr',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24082,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24083,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24085,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'lead',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24086,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'support',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(24087,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-28 16:09:18',0,1,NULL,NULL,0),(24088,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-28 16:10:22',0,1,NULL,NULL,0),(24089,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'status',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-28 16:12:19',0,1,NULL,NULL,0),(24090,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-06 15:27:14',0,1,NULL,NULL,0),(24091,24079,NULL,0,NULL,0,NULL,24043,NULL,NULL,'country',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',1,'2014-02-28 16:07:24',0,1,NULL,NULL,0),(24195,24042,NULL,0,NULL,0,NULL,24044,NULL,NULL,'Action',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:51',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(24196,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24197,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24198,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'content',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',240,'2014-02-28 18:21:37',0,1,NULL,NULL,0),(24199,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',240,'2014-02-28 15:04:09',0,1,NULL,NULL,0),(24200,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-28 16:26:02',0,1,NULL,NULL,0),(24201,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-01-21 11:24:06',0,1,NULL,NULL,0),(24202,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24203,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24204,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-28 16:12:37',0,1,NULL,NULL,0),(24205,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'solr_column_name',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24206,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24207,24043,NULL,0,NULL,0,NULL,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24208,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-12 21:12:31',0,1,NULL,NULL,0),(24209,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24210,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24211,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24212,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24213,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'title_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24214,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'info_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24215,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24216,24044,NULL,0,NULL,0,NULL,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(24217,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'Thesauri Item',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:12:11',0,1,NULL,NULL,0),(24218,24217,NULL,0,NULL,0,NULL,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24219,24217,NULL,0,NULL,0,NULL,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24220,24217,NULL,0,NULL,0,NULL,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24221,24217,NULL,0,NULL,0,NULL,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24222,24217,NULL,0,NULL,0,NULL,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24223,23940,NULL,0,NULL,0,NULL,24074,NULL,NULL,'System',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24224,24223,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Phases',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24225,24224,NULL,0,NULL,0,NULL,24217,NULL,NULL,'preliminary check',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24226,24224,NULL,0,NULL,0,NULL,24217,NULL,NULL,'investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24227,24224,NULL,0,NULL,0,NULL,24217,NULL,NULL,'court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24228,24224,NULL,0,NULL,0,NULL,24217,NULL,NULL,'civil claim',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24229,24224,NULL,0,NULL,0,NULL,24217,NULL,NULL,'ECHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24243,24223,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Files',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24244,24243,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Research',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24245,24243,NULL,0,NULL,0,NULL,24217,NULL,NULL,'CaseLaw',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24246,24243,NULL,0,NULL,0,NULL,24217,NULL,NULL,'EDR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24247,24243,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Exhibit',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24248,24223,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Case Folders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:39:08',0,1,NULL,NULL,0),(24259,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Case statuses',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24260,24259,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Active',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24261,24259,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Closed',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24262,24259,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Archived',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24263,24259,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Withdrawn',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24264,24259,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Under consideration',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,NULL,NULL,0),(24265,23940,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Office',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:39:59',0,1,NULL,NULL,0),(24266,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'Moscow',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-03-31 21:09:30',0,1,NULL,NULL,0),(24267,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'New York',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',240,'2014-03-31 21:10:04',0,1,NULL,NULL,0),(24268,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'Paris',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',240,'2014-04-01 09:21:11',0,1,NULL,NULL,0),(24269,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'London',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',267,'2014-03-03 01:50:15',0,1,NULL,NULL,0),(24270,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'Buenos Aires',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-02-28 16:17:56',0,1,NULL,NULL,0),(24271,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'Tokyo',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-02-28 16:17:41',0,1,NULL,NULL,0),(24272,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'San Francisco',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-02-28 16:20:03',0,1,NULL,NULL,0),(24273,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'Lima',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',240,'2014-03-31 21:09:45',0,1,NULL,NULL,0),(24274,24265,NULL,0,NULL,0,NULL,24484,NULL,NULL,'global',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',1,'2014-02-28 16:18:24',0,1,NULL,NULL,0),(24275,23940,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Fields',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24276,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'yes/no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24277,24276,NULL,0,NULL,0,NULL,24217,NULL,NULL,'yes',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24278,24276,NULL,0,NULL,0,NULL,24217,NULL,NULL,'no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24279,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Gender',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24280,24279,NULL,0,NULL,0,NULL,24217,NULL,NULL,'male',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24281,24279,NULL,0,NULL,0,NULL,24217,NULL,NULL,'female',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24282,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'checkbox',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24283,24282,NULL,0,NULL,0,NULL,24217,NULL,NULL,'yes',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24284,24282,NULL,0,NULL,0,NULL,24217,NULL,NULL,'no',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24285,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'types of letters',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24287,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'decision',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24288,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'communication',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24289,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'notification',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24290,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'presentation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24292,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'complaint',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24293,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'check initiation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24294,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'petition',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24295,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'appeal',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24296,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'claim',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24297,24285,NULL,0,NULL,0,NULL,24217,NULL,NULL,'informative letter',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24300,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Author',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24301,24300,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24302,24300,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Applicant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24303,24300,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Government',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24304,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Languages',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24305,24304,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Eng',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24306,24304,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Rus',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24307,24304,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Uzb',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24308,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Country',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24309,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Kyrgyzstan',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24310,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Italy',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24311,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Macedonia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24312,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Germany',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24313,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Russia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24314,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Turkey',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24315,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Romania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24316,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Poland',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24317,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Czech Republic',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24318,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Israel',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24319,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Kenya',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24320,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Kazakhstan',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24321,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Slovenia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24322,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Bulgaria',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24323,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Gambia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24324,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Switzerland',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24325,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Netherlands',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24326,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Dominican Republic',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24327,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Angola',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24328,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Equatorial Guinea',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24329,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Bosnia and Herzegovina',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24330,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Denmark',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24331,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Lithuania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24332,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Côte d\'Ivoire',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24333,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Chile',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24334,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Hungary',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24335,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Mauritania',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24336,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Cameroon',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24337,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Botswana',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24338,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Rwanda',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24339,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Costa Rica',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24340,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Position',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24341,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Administrative Associate ',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24342,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Associate Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24343,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Communications Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24344,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24345,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Director of Administration',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24346,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Director of Programs',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24347,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Executive Assistant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24348,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Executive Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24349,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Intern',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24350,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'KRT Monitor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24351,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Lawyer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24352,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Legal Intern',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24353,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24354,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Litigation Director',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24355,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Litigation Fellow',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24356,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Policy Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24357,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Program Assistant',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24358,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Program Associate',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24359,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Program Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24360,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Program Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24361,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Project Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24362,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Project Manager',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24363,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Resident Fellow',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24364,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24365,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Advocacy Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24366,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Advocacy Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24367,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Attorney',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24368,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Legal Advisor',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24369,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Legal Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24370,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Officer',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24371,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Senior Project Manager',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24372,24340,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Temporary Program Coordinator',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24373,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Location',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24374,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Abuja',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24375,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Amsterdam',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24376,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Bishkek',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24377,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Brussels',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24378,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Budapest',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24379,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Cambodia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24380,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Geneva',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24381,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'London',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24382,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Madrid',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24383,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Mexico City',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24384,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'New York',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24385,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Paris',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24386,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Santo Domingo',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24387,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'The Hague',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24388,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Tirana',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24389,24373,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Washington',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24390,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24391,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'ECHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24392,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'ACHPR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24393,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'UNHRC',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24394,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'IACHR',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24395,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'CAT',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24396,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'UNCAT',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24397,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'ECOWAS',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24398,24390,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Domestic Court',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24399,24275,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Tags',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24400,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Citizenship',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24401,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Discrimination',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24402,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Family Unification',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24403,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Torture',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24404,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Rendition',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24405,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Statelessness',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24406,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Natural resources',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24407,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Corruption',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24408,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Spoliation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24409,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Unjust enrichment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24410,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Money laundering',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24411,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Roma',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24412,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Inhuman treatment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24413,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Right to information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24414,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Right to truth',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24415,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Access to information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24416,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Education',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24417,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Ethnic profiling',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24418,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Database',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24419,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Freedom of expression',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24420,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Freedom of information',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24421,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Central Asia',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24422,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'War Crime',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24423,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24424,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Interrogation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24425,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Ineffective investigation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24426,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Police custody',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24427,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'PTD',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24428,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Pretrial Detention',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24429,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Impunity',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24430,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Nationality',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24431,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Public watchdog',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24432,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'NGO',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24433,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Ill-treatment',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24434,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Journalist',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24435,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Defamation',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24436,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Right to life',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24437,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Death in custody',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24438,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Press freedom',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24439,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Racial profiling',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24440,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Fair trial',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24441,23940,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Alex Evdokimov',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24442,23940,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Oleg Burlaca',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:12',NULL,NULL,0,1,NULL,NULL,0),(24443,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24444,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24445,24444,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24446,24444,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24447,24444,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24448,24444,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24449,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24450,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24451,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',240,'2014-02-06 13:19:22',0,1,NULL,NULL,0),(24452,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24453,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24454,24453,NULL,0,NULL,0,NULL,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24455,24453,NULL,0,NULL,0,NULL,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24456,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24457,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24458,24457,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24459,24457,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24460,24457,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24461,24457,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24462,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24463,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24464,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24465,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24466,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24467,24466,NULL,0,NULL,0,NULL,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24468,24466,NULL,0,NULL,0,NULL,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24469,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24470,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'allday',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24471,24470,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24472,24470,NULL,0,NULL,0,NULL,24043,NULL,NULL,'date_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24473,24470,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_start',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24474,24470,NULL,0,NULL,0,NULL,24043,NULL,NULL,'datetime_end',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24475,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24476,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24477,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'category',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24478,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24479,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'reminders',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24480,24479,NULL,0,NULL,0,NULL,24043,NULL,NULL,'count',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24481,24479,NULL,0,NULL,0,NULL,24043,NULL,NULL,'units',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',NULL,NULL,0,1,NULL,NULL,0),(24484,24042,NULL,0,NULL,0,NULL,24044,NULL,NULL,'office',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',240,'2014-02-28 15:07:12',0,1,NULL,NULL,0),(24485,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24486,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'visible',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24487,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'order',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24488,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'en',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24489,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'ru',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-21 11:06:21',NULL,NULL,0,1,NULL,NULL,0),(24490,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'managers',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-21 11:23:09',1,'2014-01-21 11:55:01',0,1,NULL,NULL,0),(24493,NULL,257,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,1,'2014-01-21 14:39:43',NULL,NULL,0,1,NULL,NULL,0),(24494,24493,257,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-21 14:39:43',NULL,NULL,0,1,NULL,NULL,0),(24503,24223,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Colors',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 09:57:06',NULL,NULL,0,1,NULL,NULL,0),(24504,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'gray',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-gray\"}',1,1,'2014-01-22 09:58:10',NULL,NULL,0,1,NULL,NULL,0),(24505,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'blue',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-blue\"}',1,1,'2014-01-22 09:58:43',NULL,NULL,0,1,NULL,NULL,0),(24506,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'green',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-green\"}',1,1,'2014-01-22 09:59:32',NULL,NULL,0,1,NULL,NULL,0),(24507,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'orange',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-orange\"}',1,1,'2014-01-22 10:00:04',NULL,NULL,0,1,NULL,NULL,0),(24508,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'teal',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-teal\"}',1,1,'2014-01-22 10:00:04',1,'2014-01-22 10:51:15',0,1,NULL,NULL,0),(24509,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'purple',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-purple\"}',1,1,'2014-01-22 10:01:03',NULL,NULL,0,1,NULL,NULL,0),(24510,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'red',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-red\"}',1,1,'2014-01-22 10:01:38',NULL,NULL,0,1,NULL,NULL,0),(24511,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'yellow',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-yellow\"}',1,1,'2014-01-22 10:01:38',1,'2014-01-22 10:35:17',0,1,NULL,NULL,0),(24512,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'olive',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-olive\"}',1,1,'2014-01-22 10:03:14',NULL,NULL,0,1,NULL,NULL,0),(24513,24503,NULL,0,NULL,0,NULL,24217,NULL,NULL,'steel',NULL,NULL,NULL,NULL,'{\"iconCls\": \"task-steel\"}',1,1,'2014-01-22 10:03:55',NULL,NULL,0,1,NULL,NULL,0),(24514,24072,NULL,0,NULL,0,NULL,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:07:16',1,'2014-01-22 10:08:21',0,1,NULL,NULL,0),(24515,24078,NULL,0,NULL,0,NULL,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:08:12',NULL,NULL,0,1,NULL,NULL,0),(24516,24073,NULL,0,NULL,0,NULL,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 10:08:55',NULL,NULL,0,1,NULL,NULL,0),(24517,24484,NULL,0,NULL,0,NULL,24043,NULL,NULL,'security_group',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 11:41:29',1,'2014-01-22 11:49:53',0,1,NULL,NULL,0),(24523,24074,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 14:10:27',NULL,NULL,0,1,NULL,NULL,0),(24562,NULL,262,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-01-29 15:50:39',NULL,NULL,0,NULL,NULL,NULL,0),(24563,24562,262,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-01-29 15:50:39',NULL,NULL,0,NULL,NULL,NULL,0),(24614,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'null',1,240,'2014-01-31 13:04:48',240,'2014-02-28 15:10:31',0,240,NULL,NULL,0),(24616,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'null',1,240,'2014-01-31 13:05:13',240,'2014-02-28 15:10:40',0,240,NULL,NULL,0),(24648,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:07',240,'2014-02-28 15:10:49',0,240,NULL,NULL,0),(24650,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:35',240,'2014-02-28 15:11:01',0,240,NULL,NULL,0),(24652,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:13:58',240,'2014-02-28 15:11:15',0,240,NULL,NULL,0),(24653,24248,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-03 09:14:11',240,'2014-02-28 15:11:24',0,240,NULL,NULL,0),(24822,24052,NULL,0,NULL,0,NULL,24044,NULL,NULL,'Comment',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:14:04',NULL,NULL,0,1,NULL,NULL,0),(24823,24822,NULL,0,NULL,0,NULL,24043,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:15:03',NULL,NULL,0,1,NULL,NULL,0),(24834,1,NULL,2,NULL,0,NULL,24822,NULL,NULL,'why it\'s so slow',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-12 22:12:05',NULL,NULL,0,240,NULL,NULL,0),(24848,NULL,265,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-02-21 21:21:46',NULL,NULL,0,NULL,NULL,NULL,0),(24849,24848,265,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-02-21 21:21:46',NULL,NULL,0,NULL,NULL,NULL,0),(25095,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'in_links',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-28 18:27:55',240,'2014-03-03 17:13:04',0,240,NULL,NULL,0),(25100,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'out_links',NULL,NULL,NULL,NULL,'{\"scope\":\"project\",\"templates\":[24195],\"descendants\":true,\"renderer\":\"listObjIcons\",\"editor\":\"form\"}',1,240,'2014-02-28 18:48:22',240,'2014-03-03 17:12:25',0,240,NULL,NULL,0),(25114,1,NULL,0,NULL,0,NULL,24074,NULL,NULL,'IACHR Cases',NULL,NULL,NULL,NULL,'null',1,240,'2014-02-28 20:26:15',NULL,NULL,0,240,NULL,NULL,0),(25115,NULL,266,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-02-28 21:03:13',NULL,NULL,0,NULL,NULL,NULL,0),(25116,25115,266,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-02-28 21:03:13',NULL,NULL,0,NULL,NULL,NULL,0),(25117,24195,NULL,0,NULL,0,NULL,24043,NULL,NULL,'color',NULL,NULL,NULL,NULL,'{\"source\":\"tree\",\"scope\":24503,\"autoLoad\":true,\"renderer\":\"listObjIcons\"}',1,240,'2014-02-28 21:56:07',NULL,NULL,0,1,NULL,NULL,0),(25119,NULL,269,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-03-02 20:16:11',NULL,NULL,0,NULL,NULL,NULL,0),(25120,25119,269,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-03-02 20:16:11',NULL,NULL,0,NULL,NULL,NULL,0),(25121,23490,NULL,0,NULL,0,NULL,24079,NULL,NULL,'Krasuski vs. Poland','2013-11-11 00:00:00',NULL,NULL,NULL,'null',1,269,'2014-03-02 20:26:36',267,'2014-03-03 02:03:25',0,269,NULL,NULL,0),(25122,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25123,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25124,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25125,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25126,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25127,25121,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 20:26:36',NULL,NULL,0,240,NULL,NULL,0),(25128,23490,NULL,0,NULL,0,NULL,24079,NULL,NULL,'Zhuk vs. Belarus','2012-02-14 00:00:00','2013-10-15 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 21:00:24',240,'2014-04-01 09:24:43',0,269,NULL,NULL,0),(25129,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25130,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25131,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25132,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25133,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25134,25128,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 21:00:24',NULL,NULL,0,240,NULL,NULL,0),(25135,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Belarus',NULL,NULL,NULL,NULL,'null',1,269,'2014-03-02 21:01:39',NULL,NULL,0,269,NULL,NULL,0),(25136,25133,NULL,0,5,0,NULL,24075,NULL,NULL,'Death Penalty in the OSCE Area - Background Paper.pdf','2014-03-02 21:18:37','2014-03-02 21:18:37',378763,NULL,NULL,1,269,'2014-03-02 21:18:37',269,'2014-03-02 21:18:37',0,269,NULL,NULL,0),(25137,25130,NULL,0,5,0,NULL,24075,NULL,NULL,'1910-2009-Zhuk-v.-Belarus-Final2.pdf','2014-03-02 21:18:58','2014-03-02 21:18:58',87476,NULL,NULL,1,269,'2014-03-02 21:18:58',269,'2014-03-02 21:18:58',0,269,NULL,NULL,0),(25138,25133,NULL,0,5,0,NULL,24075,NULL,NULL,'IHF Intervention to the 2006 OSCE Human Dimension Meeting.pdf','2014-03-02 21:20:42','2014-03-02 21:20:42',69040,NULL,NULL,1,269,'2014-03-02 21:20:42',269,'2014-03-02 21:20:42',0,269,NULL,NULL,0),(25139,25114,NULL,0,NULL,0,NULL,24079,NULL,NULL,'María Claudia García Gelman vs. Uruguay','2006-02-08 00:00:00',NULL,NULL,NULL,'null',1,266,'2014-03-02 21:23:11',240,'2014-04-01 10:10:08',0,266,NULL,NULL,0),(25140,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25141,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25142,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25143,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25144,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25145,25139,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,266,'2014-03-02 21:23:11',NULL,NULL,0,240,NULL,NULL,0),(25146,25128,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Send case summary for 2013 Report on Right to Fair Trial','2014-03-26 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 21:23:19',267,'2014-03-03 01:58:30',0,269,NULL,NULL,0),(25147,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Uruguay',NULL,NULL,NULL,NULL,'null',1,266,'2014-03-02 21:27:35',NULL,NULL,0,266,NULL,NULL,0),(25148,25130,NULL,0,5,0,NULL,24075,NULL,NULL,'Request for Intervention with Government of Belarus.odt','2014-03-02 21:27:38','2014-03-02 21:27:38',15048,NULL,NULL,1,269,'2014-03-02 21:27:38',269,'2014-03-02 21:27:38',0,269,NULL,NULL,0),(25149,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Adoption',NULL,NULL,NULL,NULL,'null',1,266,'2014-03-02 21:31:17',266,'2014-03-02 21:32:15',0,266,NULL,NULL,0),(25150,25154,NULL,0,5,0,NULL,24075,NULL,NULL,'Condemnation letter after execution.odt','2014-03-02 21:33:21','2014-03-02 21:33:21',66209,NULL,NULL,1,269,'2014-03-02 21:33:21',269,'2014-03-02 21:33:21',0,269,NULL,NULL,0),(25151,25132,NULL,0,5,0,NULL,24075,NULL,NULL,'PRExecutionsBelarus_March 2010.doc','2014-03-02 21:36:18','2014-03-02 21:36:18',26112,NULL,NULL,1,269,'2014-03-02 21:36:18',269,'2014-03-02 21:36:18',0,269,NULL,NULL,0),(25152,25139,NULL,0,5,0,NULL,24075,NULL,NULL,'Gelman CAse Merits and Reparations.pdf','2014-03-02 21:37:08','2014-03-02 21:37:08',620424,NULL,NULL,1,266,'2014-03-02 21:37:08',266,'2014-03-02 21:38:18',0,266,NULL,NULL,0),(25153,25139,NULL,0,5,0,NULL,24075,NULL,NULL,'gelman_seriec_221_ing.pdf','2014-03-02 21:37:21','2014-03-02 21:37:21',620424,NULL,NULL,1,266,'2014-03-02 21:37:21',266,'2014-03-02 21:37:21',0,266,NULL,NULL,0),(25154,25130,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Condemnation of Execution','2010-04-18 00:00:00',NULL,NULL,NULL,'null',1,269,'2014-03-02 21:40:11',NULL,NULL,0,269,NULL,NULL,0),(25155,25139,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Gelman press conference','2014-03-31 00:00:00',NULL,NULL,NULL,'null',1,266,'2014-03-02 21:40:50',NULL,NULL,0,266,NULL,NULL,0),(25156,25155,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Prepare the press release','2014-03-20 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,266,'2014-03-02 21:43:34',266,'2014-03-02 21:54:28',0,266,NULL,NULL,0),(25157,25155,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Invite journalists','2014-03-21 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,266,'2014-03-02 21:45:42',266,'2014-03-03 02:54:09',0,266,NULL,NULL,0),(25158,25128,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Follow up with Svetlana Zhuk','2014-03-17 00:00:00','2014-03-20 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 21:48:56',267,'2014-03-03 01:58:01',0,269,NULL,NULL,0),(25159,25155,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Venue for press conference','2014-03-18 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,266,'2014-03-02 21:50:53',240,'2014-04-01 10:10:08',0,266,NULL,NULL,0),(25160,25132,NULL,0,5,0,NULL,24075,NULL,NULL,'2 Executions in Belarus Draw Condemnation - NYTimes.pdf','2014-03-02 21:51:23','2014-03-02 21:51:23',100210,NULL,NULL,1,269,'2014-03-02 21:51:23',269,'2014-03-02 21:51:23',0,269,NULL,NULL,0),(25161,25155,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Gelman appeal','2014-03-27 00:00:00',NULL,NULL,NULL,'null',1,266,'2014-03-02 21:52:09',NULL,NULL,0,266,NULL,NULL,0),(25162,25128,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Prepare Response to Request from Special Rapporteur','2014-03-15 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 21:55:03',267,'2014-03-03 02:06:44',0,269,NULL,NULL,0),(25163,25128,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Submit response to Special Rapporteur','2014-03-24 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 22:08:24',267,'2014-03-03 01:58:43',0,269,NULL,NULL,0),(25164,25126,NULL,0,5,0,NULL,24075,NULL,NULL,'Courtwatch PL -Court_Monitoring_Methodology.pdf','2014-03-02 22:20:59','2014-03-02 22:20:59',4544828,NULL,NULL,1,269,'2014-03-02 22:20:59',269,'2014-03-02 22:20:59',0,269,NULL,NULL,0),(25165,25126,NULL,0,5,0,NULL,24075,NULL,NULL,'krawczak vs poland_eng.doc','2014-03-02 22:45:49','2014-03-02 22:45:49',89088,NULL,NULL,1,269,'2014-03-02 22:45:49',269,'2014-03-02 22:45:49',0,269,NULL,NULL,0),(25166,25126,NULL,0,5,0,NULL,24075,NULL,NULL,'poplawski vs poland.doc','2014-03-02 22:46:11','2014-03-02 22:46:11',96768,NULL,NULL,1,269,'2014-03-02 22:46:11',269,'2014-03-02 22:46:11',0,269,NULL,NULL,0),(25167,25121,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Do additional case law research','2014-03-18 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 22:53:57',NULL,NULL,0,269,NULL,NULL,0),(25168,25121,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Interview Krasuski in Warsaw prison','2014-04-08 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 22:56:54',267,'2014-03-03 01:51:33',0,269,NULL,NULL,0),(25169,25127,NULL,0,5,0,NULL,24075,NULL,NULL,'Receipt for flight booking.odt','2014-03-02 22:57:50','2014-03-02 22:57:50',9687,NULL,NULL,1,269,'2014-03-02 22:57:50',269,'2014-03-02 22:57:50',0,269,NULL,NULL,0),(25170,23490,NULL,0,NULL,0,NULL,24079,NULL,NULL,'E.E. vs. Russia','2010-08-26 00:00:00','2013-05-24 00:00:00',NULL,NULL,'null',1,269,'2014-03-02 23:18:02',269,'2014-03-02 23:45:02',0,269,NULL,NULL,0),(25171,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25172,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25173,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25174,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25175,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25176,25170,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,269,'2014-03-02 23:18:02',NULL,NULL,0,240,NULL,NULL,0),(25177,25170,NULL,0,5,0,NULL,24075,NULL,NULL,'479-2011 -EEvsRussia.pdf','2014-03-02 23:19:38','2014-03-02 23:19:38',59110,NULL,NULL,1,269,'2014-03-02 23:19:38',269,'2014-03-02 23:19:38',0,269,NULL,NULL,0),(25178,25175,NULL,0,5,0,NULL,24075,NULL,NULL,'Refworld _ Widespread Torture in the Chechen Republic. Human Rights Watch Briefing Paper for the 37th Session UN Committee against Torture.pdf','2014-03-02 23:22:34','2014-03-02 23:22:34',139417,NULL,NULL,1,269,'2014-03-02 23:22:34',269,'2014-03-02 23:22:34',0,269,NULL,NULL,0),(25179,25175,NULL,0,5,0,NULL,24075,NULL,NULL,'NNCAT_torture_Russia.pdf','2014-03-02 23:23:36','2014-03-02 23:23:36',51741,NULL,NULL,1,269,'2014-03-02 23:23:36',269,'2014-03-02 23:23:36',0,269,NULL,NULL,0),(25180,25174,NULL,0,5,0,NULL,24075,NULL,NULL,'Written testimony.odt','2014-03-02 23:45:02','2014-03-02 23:45:02',10836,NULL,NULL,1,269,'2014-03-02 23:45:02',269,'2014-03-02 23:45:02',0,269,NULL,NULL,0),(25181,NULL,268,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-03-03 01:43:46',NULL,NULL,0,NULL,NULL,NULL,0),(25182,25181,268,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-03-03 01:43:46',NULL,NULL,0,NULL,NULL,NULL,0),(25183,NULL,267,1,1,0,NULL,24074,NULL,NULL,'[Home]',NULL,NULL,NULL,NULL,'{\"controller\": \"UsersHomeFolder\"}',1,NULL,'2014-03-03 01:44:43',NULL,NULL,0,NULL,NULL,NULL,0),(25184,25183,267,1,1,0,NULL,24074,NULL,NULL,'[MyDocuments]',NULL,NULL,NULL,NULL,NULL,1,NULL,'2014-03-03 01:44:43',NULL,NULL,0,NULL,NULL,NULL,0),(25185,25127,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Brief on progress of the case','2014-03-28 11:00:00','2014-03-28 13:00:00',NULL,NULL,'null',1,267,'2014-03-03 02:01:54',267,'2014-03-03 02:03:25',0,267,NULL,NULL,0),(25186,25201,NULL,0,NULL,0,NULL,24079,NULL,NULL,'Privacy International (on behalf of Tadesse Kersmo) vs.  UK','2014-02-17 00:00:00',NULL,NULL,NULL,'null',1,267,'2014-03-03 02:49:42',267,'2014-03-03 03:38:39',0,267,NULL,NULL,0),(25187,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25188,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25189,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25190,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25191,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25192,25186,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 02:49:42',NULL,NULL,0,240,NULL,NULL,0),(25193,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'United Kingdom',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 02:50:40',NULL,NULL,0,267,NULL,NULL,0),(25194,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Ethiopia',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 02:51:19',NULL,NULL,0,267,NULL,NULL,0),(25195,24399,NULL,0,NULL,0,NULL,24217,NULL,NULL,'Privacy',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 02:51:42',NULL,NULL,0,267,NULL,NULL,0),(25196,25191,NULL,0,5,0,NULL,24075,NULL,NULL,'Coulson & Anor v R [2013] EWCA Crim 1026 (28 June 2013).pdf','2014-03-03 02:56:41','2014-03-03 02:56:41',65880,NULL,NULL,1,267,'2014-03-03 02:56:41',267,'2014-03-03 02:56:41',0,267,NULL,NULL,0),(25197,25191,NULL,0,5,0,NULL,24075,NULL,NULL,'Investigatory Powers Act - 2000 - changes to legislation.pdf','2014-03-03 02:56:57','2014-03-03 02:56:57',1639276,NULL,NULL,1,267,'2014-03-03 02:56:57',267,'2014-03-03 02:56:57',0,267,NULL,NULL,0),(25198,25191,NULL,0,5,0,NULL,24075,NULL,NULL,'Investigatory Powers Act - 2000.pdf','2014-03-03 02:57:02','2014-03-03 02:57:02',1019195,NULL,NULL,1,267,'2014-03-03 02:57:02',267,'2014-03-03 02:57:02',0,267,NULL,NULL,0),(25199,25189,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Complaint to National Cyber Crime Unit of the National Crime Agency','2014-03-07 00:00:00',NULL,NULL,NULL,'null',1,267,'2014-03-03 02:59:29',267,'2014-03-03 03:00:51',0,267,NULL,NULL,0),(25200,25121,NULL,2,NULL,0,NULL,24822,NULL,NULL,'This is an important case, as it will set a precedent regarding the right to fair trial. Please focus, so we will have all documents at our finger tips all the time, and coordinate effectively, using Casebox.',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 03:05:19',NULL,NULL,0,267,NULL,NULL,0),(25201,1,NULL,0,NULL,0,NULL,24074,NULL,NULL,'Domestic Cases',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 03:07:00',NULL,NULL,0,267,NULL,NULL,0),(25202,25187,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Media request from the Guardian - Interview','2014-03-06 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,267,'2014-03-03 03:09:56',NULL,NULL,0,267,NULL,NULL,0),(25203,25190,NULL,0,5,0,NULL,24075,NULL,NULL,'15-2013-youonlyclicktwice.pdf','2014-03-03 03:11:59','2014-03-03 03:11:59',933736,NULL,NULL,1,267,'2014-03-03 03:11:59',267,'2014-03-03 03:11:59',0,267,NULL,NULL,0),(25204,25199,NULL,0,5,0,NULL,24075,NULL,NULL,'Complaint National Cyber Crime Unit of the National Crime Agency.odt','2014-03-03 03:23:16','2014-03-03 03:23:16',14516,NULL,NULL,1,266,'2014-03-03 03:23:16',266,'2014-03-03 03:23:16',0,266,NULL,NULL,0),(25205,25167,NULL,2,NULL,0,NULL,24822,NULL,NULL,'I am really interested in the results for an upcoming meeting I have with a journalist from the Independent. Could you brief me once you\'re done?',NULL,NULL,NULL,NULL,'null',1,266,'2014-03-03 03:27:07',NULL,NULL,0,266,NULL,NULL,0),(25206,25201,NULL,0,NULL,0,NULL,24079,NULL,NULL,'RightsCon vs. NSA, GCHQ','2014-03-03 00:00:00','2014-03-05 00:00:00',NULL,NULL,'null',1,267,'2014-03-03 03:34:22',240,'2014-03-31 10:54:49',0,267,NULL,NULL,0),(25207,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25208,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25209,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25210,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25211,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25212,25206,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,267,'2014-03-03 03:34:22',NULL,NULL,0,240,NULL,NULL,0),(25213,24308,NULL,0,NULL,0,NULL,24217,NULL,NULL,'United States',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 03:35:01',NULL,NULL,0,267,NULL,NULL,0),(25214,25206,NULL,0,NULL,0,NULL,24073,NULL,NULL,'Demonstration in the Fishbowl','2014-03-03 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,267,'2014-03-03 03:37:30',267,'2014-03-03 03:38:20',0,267,NULL,NULL,0),(25215,25210,NULL,0,5,0,NULL,24075,NULL,NULL,'Snowden Leaks.odt','2014-03-03 03:40:14','2014-03-03 03:40:14',10163,NULL,NULL,1,267,'2014-03-03 03:40:14',267,'2014-03-03 03:40:14',0,267,NULL,NULL,0),(25216,25208,NULL,0,5,0,NULL,24075,NULL,NULL,'Luke Hardings book.odt','2014-03-03 03:43:07','2014-03-03 03:43:07',9202,NULL,NULL,1,267,'2014-03-03 03:43:07',267,'2014-03-03 03:43:07',0,267,NULL,NULL,0),(25217,25212,NULL,0,5,0,NULL,24075,NULL,NULL,'Lawyers working on this case - security measures.ods','2014-03-03 03:47:16','2014-03-03 03:47:16',21328,NULL,NULL,1,267,'2014-03-03 03:47:16',267,'2014-03-03 03:47:16',0,267,NULL,NULL,0),(25218,25217,NULL,2,NULL,0,NULL,24822,NULL,NULL,'We really need to improve our security. When we take on this case, we can be sure that they will be after us.',NULL,NULL,NULL,NULL,'null',1,269,'2014-03-03 03:48:47',NULL,NULL,0,269,NULL,NULL,0),(25219,25217,NULL,2,NULL,0,NULL,24822,NULL,NULL,'Yes, I fully agree. Please, everyone, let\'s get this going asap.',NULL,NULL,NULL,NULL,'null',1,267,'2014-03-03 03:51:35',NULL,NULL,0,267,NULL,NULL,0),(25220,25217,NULL,2,NULL,0,NULL,24822,NULL,NULL,'But there\'s nothing much we can do against  it. If they want to get in, they will. Let\'s spend our time more wisely than trying to fight a fight we cannot win. The tools are clunky, too.',NULL,NULL,NULL,NULL,'null',1,266,'2014-03-03 03:53:19',NULL,NULL,0,266,NULL,NULL,0),(25221,25217,NULL,2,NULL,0,NULL,24822,NULL,NULL,'I disagree. Snowden said that properly implemented encryption works. So we will make it work for us as well.',NULL,NULL,NULL,NULL,'null',1,269,'2014-03-03 03:54:45',NULL,NULL,0,269,NULL,NULL,0),(25222,25211,NULL,0,5,0,NULL,24075,NULL,NULL,'GCHQ and European spy agencies worked together on mass surveillance _ UK news _ The Guardian.pdf','2014-03-03 04:00:47','2014-03-03 04:00:47',77194,NULL,NULL,1,267,'2014-03-03 04:00:47',267,'2014-03-03 04:00:47',0,267,NULL,NULL,0),(25223,25211,NULL,0,5,0,NULL,24075,NULL,NULL,'Surveillance, democracy, transparency – a global view _ World news _ The Guardian.pdf','2014-03-03 04:00:50','2014-03-03 04:00:50',186629,NULL,NULL,1,267,'2014-03-03 04:00:50',267,'2014-03-03 04:00:50',0,267,NULL,NULL,0),(25224,25208,NULL,0,5,0,NULL,24075,NULL,NULL,'Letter of warning.odt','2014-03-03 04:03:54','2014-03-03 04:03:54',16125,NULL,NULL,1,267,'2014-03-03 04:03:54',267,'2014-03-03 04:03:54',0,267,NULL,NULL,0),(25225,25201,NULL,0,NULL,0,NULL,24079,NULL,NULL,'John v. US','2014-03-12 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 16:56:39',240,'2014-04-01 20:39:06',0,240,NULL,NULL,0),(25226,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'0. Incoming',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25227,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'1. Correspondence',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25228,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'2. Filings',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25229,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'3. Evidence',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25230,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'4. Research',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25231,25225,NULL,0,NULL,0,NULL,24074,NULL,NULL,'5. Administrative',NULL,NULL,NULL,NULL,'[]',1,240,'2014-03-03 16:56:39',NULL,NULL,0,240,NULL,NULL,0),(25232,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Final Post of Charles Taylor Trial Blog','2014-02-01 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 16:57:57',NULL,NULL,0,240,NULL,NULL,0),(25233,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Exchange of Thanks: Victims and Special Court Prosector Express Gratitude in Sierra Leone','2014-02-10 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 16:58:26',240,'2014-03-03 17:18:20',0,240,NULL,NULL,0),(25234,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'In Sierra Leone, Victims Celebrate Taylor’s Conviction','2014-02-08 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 16:58:50',240,'2014-03-03 17:17:58',0,240,NULL,NULL,0),(25235,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Charles Taylor’s Conviction and Sentence Upheld: What next for him?','2014-02-07 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 16:59:19',240,'2014-03-03 17:17:34',0,240,NULL,NULL,0),(25236,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Appeals Chamber Upholds Taylor’s Jail Sentence','2014-02-10 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:00:59',NULL,NULL,0,240,NULL,NULL,0),(25237,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Charles Taylor’s Fate: Will He Be Back in Liberia?','2014-02-11 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:01:20',240,'2014-03-03 17:19:01',0,240,NULL,NULL,0),(25238,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Resources Ahead of Taylor Appeal Judgment','2014-02-12 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:01:41',240,'2014-04-01 20:38:14',0,240,NULL,NULL,0),(25239,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Special Court Announces Date of Taylor Appeal Judgment','2014-02-14 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:01:56',240,'2014-03-03 17:19:31',0,240,NULL,NULL,0),(25240,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Charles Taylor’s Former Investigator Sentenced to Two and Half years in Jail','2014-02-15 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:02:13',240,'2014-03-03 17:20:59',0,240,NULL,NULL,0),(25241,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Appeals Chamber Concludes Oral Hearings in Charles Taylor’s Appeal','2014-02-15 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:02:31',NULL,NULL,0,240,NULL,NULL,0),(25242,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Parties in Taylor Trial Make Appeals Submissions','2014-02-16 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:02:57',240,'2014-03-03 17:25:29',0,240,NULL,NULL,0),(25243,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Charles Taylor Oral Appeal Hearings–Tuesday Jan. 22 and Wednesday Jan. 23 2012','2014-02-17 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:03:16',240,'2014-04-01 20:38:27',0,240,NULL,NULL,0),(25244,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Appeals Hearing in Taylor Case Postponed','2014-02-18 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:03:33',240,'2014-04-01 20:38:37',0,240,NULL,NULL,0),(25245,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Why the Special Court for Sierra Leone Should Establish an Independent Commission to Address Alternate Judge Sow’s Allegation in the Charles Taylor Case','2014-02-18 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:03:48',240,'2014-03-03 18:01:45',0,240,NULL,NULL,0),(25246,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Public Reaction in Sierra Leone to the Judgment of Charles Taylor','2014-02-19 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:04:12',240,'2014-04-01 20:37:41',0,240,NULL,NULL,0),(25247,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Prosecution and Defense to Appeal Charles Taylor Judgment and Sentence','2014-02-15 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:04:34',240,'2014-04-01 20:38:50',0,240,NULL,NULL,0),(25248,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'Charles Taylor Sentenced to 50 Years in Jail','2014-02-20 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:04:53',240,'2014-04-01 20:39:06',0,240,NULL,NULL,0),(25249,25227,NULL,0,NULL,0,NULL,24195,NULL,NULL,'At Prosecution and Defense Oral Arguments on Sentencing, Charles Taylor Makes Public Statement','2014-02-22 00:00:00',NULL,NULL,NULL,'null',1,240,'2014-03-03 17:05:55',240,'2014-04-01 20:37:55',0,240,NULL,NULL,0),(25250,25229,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Prepare a draft reply for the court','2014-04-01 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,269,'2014-04-01 09:53:09',NULL,NULL,0,269,NULL,NULL,0),(25251,25231,NULL,0,NULL,0,NULL,24072,NULL,NULL,'Send me the bill for your last trip to UK','2014-04-01 00:00:00','0000-00-00 00:00:00',NULL,NULL,'null',1,268,'2014-04-01 09:57:59',NULL,NULL,0,268,NULL,NULL,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl` */

insert  into `tree_acl`(`id`,`node_id`,`user_group_id`,`allow`,`deny`,`cid`,`cdate`,`uid`,`udate`) values (3,23730,1,4095,0,NULL,'2013-04-22 13:27:18',NULL,NULL),(12,23741,240,4095,0,NULL,'2013-05-26 08:18:39',NULL,NULL),(118,23811,242,4095,0,250,'2013-06-04 13:07:21',250,'2013-06-04 13:07:41'),(133,1,233,4095,0,1,'2014-02-28 14:58:34',1,'2014-02-28 14:59:07'),(134,1,235,4095,0,1,'2014-02-28 14:58:39',240,'2014-02-28 15:04:50'),(136,1,234,4095,0,1,'2014-02-28 14:58:51',1,'2014-02-28 14:59:07'),(142,25115,266,4095,0,NULL,'2014-02-28 21:03:13',NULL,NULL),(143,25119,269,4095,0,NULL,'2014-03-02 20:16:11',NULL,NULL),(144,25181,268,4095,0,NULL,'2014-03-03 01:43:46',NULL,NULL),(145,25183,267,4095,0,NULL,'2014-03-03 01:44:43',NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl_security_sets` */

insert  into `tree_acl_security_sets`(`id`,`set`,`md5`,`updated`) values (1,'1','c4ca4238a0b923820dcc509a6f75849b',0),(2,'23730','f87b7d1f666a0a1d86568485a520bffa',0),(3,'23741','5580d031cccd368c6cd90bc0999c207e',0),(4,'23811','3f6e4c5abb908a8ac7ca70a2a8fad69c',0),(5,'25115','b2335c67c2b28490257e34f369099f4e',0),(6,'25119','6e890e85592f3ffa529332e0e35bc207',0),(7,'25181','78cc593b765dd45436b48b6a2e7ac672',0),(8,'25183','71bfbe458113bbc3b27576494be78972',0);

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

insert  into `tree_acl_security_sets_result`(`security_set_id`,`user_id`,`bit0`,`bit1`,`bit2`,`bit3`,`bit4`,`bit5`,`bit6`,`bit7`,`bit8`,`bit9`,`bit10`,`bit11`) values (1,1,1,1,1,1,1,1,1,1,1,1,1,1),(1,234,1,1,1,1,1,1,1,1,1,1,1,1),(1,240,1,1,1,1,1,1,1,1,1,1,1,1),(1,266,1,1,1,1,1,1,1,1,1,1,1,1),(2,1,1,1,1,1,1,1,1,1,1,1,1,1),(3,240,1,1,1,1,1,1,1,1,1,1,1,1),(4,268,1,1,1,1,1,1,1,1,1,1,1,1),(5,266,1,1,1,1,1,1,1,1,1,1,1,1),(6,269,1,1,1,1,1,1,1,1,1,1,1,1),(7,268,1,1,1,1,1,1,1,1,1,1,1,1),(8,267,1,1,1,1,1,1,1,1,1,1,1,1);

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

insert  into `tree_info`(`id`,`pids`,`path`,`case_id`,`acl_count`,`security_set_id`,`updated`) values (1,'1','',NULL,4,1,0),(23432,'23432','',NULL,0,NULL,0),(23433,'23432,23433','/',NULL,0,NULL,0),(23434,'23434','',NULL,0,NULL,0),(23435,'23434,23435','/',NULL,0,NULL,0),(23436,'23434,23436','/',NULL,0,NULL,0),(23437,'23434,23436,23437','/[Tasks]/',NULL,0,NULL,0),(23438,'23434,23436,23438','/[Tasks]/',NULL,0,NULL,0),(23439,'23434,23436,23439','/[Tasks]/',NULL,0,NULL,0),(23440,'23434,23440','/',NULL,0,NULL,0),(23441,'23434,23440,23441','/[Messages]/',NULL,0,NULL,0),(23442,'23434,23440,23442','/[Messages]/',NULL,0,NULL,0),(23443,'23434,23443','/',NULL,0,NULL,0),(23444,'23444','',NULL,0,NULL,0),(23445,'23444,23445','/',NULL,0,NULL,0),(23446,'23444,23446','/',NULL,0,NULL,0),(23447,'23444,23446,23447','/[Tasks]/',NULL,0,NULL,0),(23448,'23444,23446,23448','/[Tasks]/',NULL,0,NULL,0),(23449,'23444,23446,23449','/[Tasks]/',NULL,0,NULL,0),(23450,'23444,23450','/',NULL,0,NULL,0),(23451,'23444,23450,23451','/[Messages]/',NULL,0,NULL,0),(23452,'23444,23450,23452','/[Messages]/',NULL,0,NULL,0),(23490,'1,23490','/',NULL,0,1,0),(23730,'23730','',NULL,1,2,0),(23731,'23730,23731','/',NULL,0,2,0),(23732,'23730,23732','/',NULL,0,2,0),(23733,'23733','',NULL,0,NULL,0),(23734,'23733,23734','/',NULL,0,NULL,0),(23735,'23735','',NULL,0,NULL,0),(23736,'23735,23736','/',NULL,0,NULL,0),(23741,'23741','',NULL,1,3,0),(23742,'23741,23742','/',NULL,0,3,0),(23744,'23744','',NULL,0,NULL,0),(23745,'23744,23745','/',NULL,0,NULL,0),(23748,'23733,23734,23748','/[MyDocuments]/',NULL,0,NULL,0),(23807,'23807','',NULL,0,NULL,0),(23808,'23807,23808','/',NULL,0,NULL,0),(23809,'23809','',NULL,0,NULL,0),(23810,'23809,23810','/',NULL,0,NULL,0),(23811,'23807,23808,23811','/[MyDocuments]/',NULL,1,4,0),(23815,'23733,23734,23815','/[MyDocuments]/',23815,0,NULL,0),(23816,'23733,23734,23815,23816','/[MyDocuments]/12/',23815,0,NULL,0),(23817,'23733,23734,23815,23817','/[MyDocuments]/12/',23815,0,NULL,0),(23818,'23733,23734,23815,23818','/[MyDocuments]/12/',23815,0,NULL,0),(23819,'23733,23734,23815,23819','/[MyDocuments]/12/',23815,0,NULL,0),(23820,'23733,23734,23815,23820','/[MyDocuments]/12/',23815,0,NULL,0),(23821,'23733,23734,23815,23821','/[MyDocuments]/12/',23815,0,NULL,0),(23822,'23733,23734,23815,23822','/[MyDocuments]/12/',23815,0,NULL,0),(23823,'23733,23734,23815,23823','/[MyDocuments]/12/',23815,0,NULL,0),(23824,'23733,23734,23815,23824','/[MyDocuments]/12/',23815,0,NULL,0),(23825,'23733,23734,23815,23825','/[MyDocuments]/12/',23815,0,NULL,0),(23883,'23883','',NULL,0,NULL,0),(23884,'23883,23884','/',NULL,0,NULL,0),(23885,'23885','',NULL,0,NULL,0),(23886,'23885,23886','/',NULL,0,NULL,0),(23940,'1,23940','/',NULL,0,1,0),(24042,'1,24042','/',NULL,0,1,0),(24043,'1,24042,24052,24043','/Templates/System/',NULL,0,1,0),(24044,'1,24042,24052,24044','/Templates/System/',NULL,0,1,0),(24052,'1,24042,24052','/Templates/',NULL,0,1,0),(24053,'1,24042,24052,24053','/Templates/System/',NULL,0,1,0),(24054,'1,24042,24052,24053,24054','/Templates/System/User/',NULL,0,1,0),(24055,'1,24042,24052,24053,24055','/Templates/System/User/',NULL,0,1,0),(24056,'1,24042,24052,24053,24056','/Templates/System/User/',NULL,0,1,0),(24057,'1,24042,24052,24053,24057','/Templates/System/User/',NULL,0,1,0),(24058,'1,24042,24052,24053,24058','/Templates/System/User/',NULL,0,1,0),(24059,'1,24042,24052,24053,24059','/Templates/System/User/',NULL,0,1,0),(24060,'1,24042,24052,24053,24060','/Templates/System/User/',NULL,0,1,0),(24061,'1,24042,24052,24053,24061','/Templates/System/User/',NULL,0,1,0),(24062,'1,24042,24052,24053,24062','/Templates/System/User/',NULL,0,1,0),(24063,'1,24042,24052,24053,24063','/Templates/System/User/',NULL,0,1,0),(24064,'1,24042,24052,24053,24064','/Templates/System/User/',NULL,0,1,0),(24065,'1,24042,24052,24053,24065','/Templates/System/User/',NULL,0,1,0),(24066,'1,24042,24052,24053,24066','/Templates/System/User/',NULL,0,1,0),(24067,'1,24042,24052,24067','/Templates/System/',NULL,0,1,0),(24068,'1,24042,24052,24067,24068','/Templates/System/email/',NULL,0,1,0),(24069,'1,24042,24052,24067,24069','/Templates/System/email/',NULL,0,1,0),(24070,'1,24042,24052,24067,24070','/Templates/System/email/',NULL,0,1,0),(24071,'1,24042,24052,24067,24071','/Templates/System/email/',NULL,0,1,0),(24072,'1,24042,24052,24072','/Templates/System/',NULL,0,1,0),(24073,'1,24042,24052,24073','/Templates/System/',NULL,0,1,0),(24074,'1,24042,24052,24074','/Templates/System/',NULL,0,1,0),(24075,'1,24042,24052,24075','/Templates/System/',NULL,0,1,0),(24076,'1,24042,24052,24075,24076','/Templates/System/file_template/',NULL,0,1,0),(24077,'1,24042,24052,24075,24077','/Templates/System/file_template/',NULL,0,1,0),(24078,'1,24042,24052,24078','/Templates/System/',NULL,0,1,0),(24079,'1,24042,24079','/Templates/',NULL,0,1,0),(24080,'1,24042,24079,24080','/Templates/case_template/',NULL,0,1,0),(24081,'1,24042,24079,24081','/Templates/case_template/',NULL,0,1,0),(24082,'1,24042,24079,24082','/Templates/case_template/',NULL,0,1,0),(24083,'1,24042,24079,24083','/Templates/case_template/',NULL,0,1,0),(24085,'1,24042,24079,24085','/Templates/case_template/',NULL,0,1,0),(24086,'1,24042,24079,24086','/Templates/case_template/',NULL,0,1,0),(24087,'1,24042,24079,24087','/Templates/case_template/',NULL,0,1,0),(24088,'1,24042,24079,24088','/Templates/case_template/',NULL,0,1,0),(24089,'1,24042,24079,24089','/Templates/case_template/',NULL,0,1,0),(24090,'1,24042,24079,24090','/Templates/case_template/',NULL,0,1,0),(24091,'1,24042,24079,24091','/Templates/case_template/',NULL,0,1,0),(24195,'1,24042,24195','/Templates/',NULL,0,1,0),(24196,'1,24042,24195,24196','/Templates/Action/',NULL,0,1,0),(24197,'1,24042,24195,24197','/Templates/Action/',NULL,0,1,0),(24198,'1,24042,24195,24198','/Templates/Action/',NULL,0,1,0),(24199,'1,24042,24195,24199','/Templates/Action/',NULL,0,1,0),(24200,'1,24042,24195,24200','/Templates/Action/',NULL,0,1,0),(24201,'1,24042,24052,24043,24201','/Templates/System/Fields template/',NULL,0,1,0),(24202,'1,24042,24052,24043,24202','/Templates/System/Fields template/',NULL,0,1,0),(24203,'1,24042,24052,24043,24203','/Templates/System/Fields template/',NULL,0,1,0),(24204,'1,24042,24052,24043,24204','/Templates/System/Fields template/',NULL,0,1,0),(24205,'1,24042,24052,24043,24205','/Templates/System/Fields template/',NULL,0,1,0),(24206,'1,24042,24052,24043,24206','/Templates/System/Fields template/',NULL,0,1,0),(24207,'1,24042,24052,24043,24207','/Templates/System/Fields template/',NULL,0,1,0),(24208,'1,24042,24052,24044,24208','/Templates/System/Templates template/',NULL,0,1,0),(24209,'1,24042,24052,24044,24209','/Templates/System/Templates template/',NULL,0,1,0),(24210,'1,24042,24052,24044,24210','/Templates/System/Templates template/',NULL,0,1,0),(24211,'1,24042,24052,24044,24211','/Templates/System/Templates template/',NULL,0,1,0),(24212,'1,24042,24052,24044,24212','/Templates/System/Templates template/',NULL,0,1,0),(24213,'1,24042,24052,24044,24213','/Templates/System/Templates template/',NULL,0,1,0),(24214,'1,24042,24052,24044,24214','/Templates/System/Templates template/',NULL,0,1,0),(24215,'1,24042,24052,24044,24215','/Templates/System/Templates template/',NULL,0,1,0),(24216,'1,24042,24052,24044,24216','/Templates/System/Templates template/',NULL,0,1,0),(24217,'1,24042,24052,24217','/Templates/System/',NULL,0,1,0),(24218,'1,24042,24052,24217,24218','/Templates/System/Thesauri Item/',NULL,0,1,0),(24219,'1,24042,24052,24217,24219','/Templates/System/Thesauri Item/',NULL,0,1,0),(24220,'1,24042,24052,24217,24220','/Templates/System/Thesauri Item/',NULL,0,1,0),(24221,'1,24042,24052,24217,24221','/Templates/System/Thesauri Item/',NULL,0,1,0),(24222,'1,24042,24052,24217,24222','/Templates/System/Thesauri Item/',NULL,0,1,0),(24223,'1,23940,24223','/Thesauri/',NULL,0,1,0),(24224,'1,23940,24223,24224','/Thesauri/System/',NULL,0,1,0),(24225,'1,23940,24223,24224,24225','/Thesauri/System/Phases/',NULL,0,1,0),(24226,'1,23940,24223,24224,24226','/Thesauri/System/Phases/',NULL,0,1,0),(24227,'1,23940,24223,24224,24227','/Thesauri/System/Phases/',NULL,0,1,0),(24228,'1,23940,24223,24224,24228','/Thesauri/System/Phases/',NULL,0,1,0),(24229,'1,23940,24223,24224,24229','/Thesauri/System/Phases/',NULL,0,1,0),(24243,'1,23940,24223,24243','/Thesauri/System/',NULL,0,1,0),(24244,'1,23940,24223,24243,24244','/Thesauri/System/Files/',NULL,0,1,0),(24245,'1,23940,24223,24243,24245','/Thesauri/System/Files/',NULL,0,1,0),(24246,'1,23940,24223,24243,24246','/Thesauri/System/Files/',NULL,0,1,0),(24247,'1,23940,24223,24243,24247','/Thesauri/System/Files/',NULL,0,1,0),(24248,'1,23940,24223,24248','/Thesauri/System/',NULL,0,1,0),(24259,'1,23940,24275,24259','/Thesauri/Fields/',NULL,0,1,0),(24260,'1,23940,24275,24259,24260','/Thesauri/Fields/Case statuses/',NULL,0,1,0),(24261,'1,23940,24275,24259,24261','/Thesauri/Fields/Case statuses/',NULL,0,1,0),(24262,'1,23940,24275,24259,24262','/Thesauri/Fields/Case statuses/',NULL,0,1,0),(24263,'1,23940,24275,24259,24263','/Thesauri/Fields/Case statuses/',NULL,0,1,0),(24264,'1,23940,24275,24259,24264','/Thesauri/Fields/Case statuses/',NULL,0,1,0),(24265,'1,23940,24265','/Thesauri/',NULL,0,1,0),(24266,'1,23940,24265,24266','/Thesauri/Office/',NULL,0,1,0),(24267,'1,23940,24265,24267','/Thesauri/Office/',NULL,0,1,0),(24268,'1,23940,24265,24268','/Thesauri/Office/',NULL,0,1,0),(24269,'1,23940,24265,24269','/Thesauri/Office/',NULL,0,1,0),(24270,'1,23940,24265,24270','/Thesauri/Office/',NULL,0,1,0),(24271,'1,23940,24265,24271','/Thesauri/Office/',NULL,0,1,0),(24272,'1,23940,24265,24272','/Thesauri/Office/',NULL,0,1,0),(24273,'1,23940,24265,24273','/Thesauri/Office/',NULL,0,1,0),(24274,'1,23940,24265,24274','/Thesauri/Office/',NULL,0,1,0),(24275,'1,23940,24275','/Thesauri/',NULL,0,1,0),(24276,'1,23940,24275,24276','/Thesauri/Fields/',NULL,0,1,0),(24277,'1,23940,24275,24276,24277','/Thesauri/Fields/yes/no/',NULL,0,1,0),(24278,'1,23940,24275,24276,24278','/Thesauri/Fields/yes/no/',NULL,0,1,0),(24279,'1,23940,24275,24279','/Thesauri/Fields/',NULL,0,1,0),(24280,'1,23940,24275,24279,24280','/Thesauri/Fields/Gender/',NULL,0,1,0),(24281,'1,23940,24275,24279,24281','/Thesauri/Fields/Gender/',NULL,0,1,0),(24282,'1,23940,24275,24282','/Thesauri/Fields/',NULL,0,1,0),(24283,'1,23940,24275,24282,24283','/Thesauri/Fields/checkbox/',NULL,0,1,0),(24284,'1,23940,24275,24282,24284','/Thesauri/Fields/checkbox/',NULL,0,1,0),(24285,'1,23940,24275,24285','/Thesauri/Fields/',NULL,0,1,0),(24287,'1,23940,24275,24285,24287','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24288,'1,23940,24275,24285,24288','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24289,'1,23940,24275,24285,24289','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24290,'1,23940,24275,24285,24290','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24292,'1,23940,24275,24285,24292','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24293,'1,23940,24275,24285,24293','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24294,'1,23940,24275,24285,24294','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24295,'1,23940,24275,24285,24295','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24296,'1,23940,24275,24285,24296','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24297,'1,23940,24275,24285,24297','/Thesauri/Fields/types of letters/',NULL,0,1,0),(24300,'1,23940,24275,24300','/Thesauri/Fields/',NULL,0,1,0),(24301,'1,23940,24275,24300,24301','/Thesauri/Fields/Author/',NULL,0,1,0),(24302,'1,23940,24275,24300,24302','/Thesauri/Fields/Author/',NULL,0,1,0),(24303,'1,23940,24275,24300,24303','/Thesauri/Fields/Author/',NULL,0,1,0),(24304,'1,23940,24275,24304','/Thesauri/Fields/',NULL,0,1,0),(24305,'1,23940,24275,24304,24305','/Thesauri/Fields/Languages/',NULL,0,1,0),(24306,'1,23940,24275,24304,24306','/Thesauri/Fields/Languages/',NULL,0,1,0),(24307,'1,23940,24275,24304,24307','/Thesauri/Fields/Languages/',NULL,0,1,0),(24308,'1,23940,24275,24308','/Thesauri/Fields/',NULL,0,1,0),(24309,'1,23940,24275,24308,24309','/Thesauri/Fields/Country/',NULL,0,1,0),(24310,'1,23940,24275,24308,24310','/Thesauri/Fields/Country/',NULL,0,1,0),(24311,'1,23940,24275,24308,24311','/Thesauri/Fields/Country/',NULL,0,1,0),(24312,'1,23940,24275,24308,24312','/Thesauri/Fields/Country/',NULL,0,1,0),(24313,'1,23940,24275,24308,24313','/Thesauri/Fields/Country/',NULL,0,1,0),(24314,'1,23940,24275,24308,24314','/Thesauri/Fields/Country/',NULL,0,1,0),(24315,'1,23940,24275,24308,24315','/Thesauri/Fields/Country/',NULL,0,1,0),(24316,'1,23940,24275,24308,24316','/Thesauri/Fields/Country/',NULL,0,1,0),(24317,'1,23940,24275,24308,24317','/Thesauri/Fields/Country/',NULL,0,1,0),(24318,'1,23940,24275,24308,24318','/Thesauri/Fields/Country/',NULL,0,1,0),(24319,'1,23940,24275,24308,24319','/Thesauri/Fields/Country/',NULL,0,1,0),(24320,'1,23940,24275,24308,24320','/Thesauri/Fields/Country/',NULL,0,1,0),(24321,'1,23940,24275,24308,24321','/Thesauri/Fields/Country/',NULL,0,1,0),(24322,'1,23940,24275,24308,24322','/Thesauri/Fields/Country/',NULL,0,1,0),(24323,'1,23940,24275,24308,24323','/Thesauri/Fields/Country/',NULL,0,1,0),(24324,'1,23940,24275,24308,24324','/Thesauri/Fields/Country/',NULL,0,1,0),(24325,'1,23940,24275,24308,24325','/Thesauri/Fields/Country/',NULL,0,1,0),(24326,'1,23940,24275,24308,24326','/Thesauri/Fields/Country/',NULL,0,1,0),(24327,'1,23940,24275,24308,24327','/Thesauri/Fields/Country/',NULL,0,1,0),(24328,'1,23940,24275,24308,24328','/Thesauri/Fields/Country/',NULL,0,1,0),(24329,'1,23940,24275,24308,24329','/Thesauri/Fields/Country/',NULL,0,1,0),(24330,'1,23940,24275,24308,24330','/Thesauri/Fields/Country/',NULL,0,1,0),(24331,'1,23940,24275,24308,24331','/Thesauri/Fields/Country/',NULL,0,1,0),(24332,'1,23940,24275,24308,24332','/Thesauri/Fields/Country/',NULL,0,1,0),(24333,'1,23940,24275,24308,24333','/Thesauri/Fields/Country/',NULL,0,1,0),(24334,'1,23940,24275,24308,24334','/Thesauri/Fields/Country/',NULL,0,1,0),(24335,'1,23940,24275,24308,24335','/Thesauri/Fields/Country/',NULL,0,1,0),(24336,'1,23940,24275,24308,24336','/Thesauri/Fields/Country/',NULL,0,1,0),(24337,'1,23940,24275,24308,24337','/Thesauri/Fields/Country/',NULL,0,1,0),(24338,'1,23940,24275,24308,24338','/Thesauri/Fields/Country/',NULL,0,1,0),(24339,'1,23940,24275,24308,24339','/Thesauri/Fields/Country/',NULL,0,1,0),(24340,'1,23940,24275,24340','/Thesauri/Fields/',NULL,0,1,0),(24341,'1,23940,24275,24340,24341','/Thesauri/Fields/Position/',NULL,0,1,0),(24342,'1,23940,24275,24340,24342','/Thesauri/Fields/Position/',NULL,0,1,0),(24343,'1,23940,24275,24340,24343','/Thesauri/Fields/Position/',NULL,0,1,0),(24344,'1,23940,24275,24340,24344','/Thesauri/Fields/Position/',NULL,0,1,0),(24345,'1,23940,24275,24340,24345','/Thesauri/Fields/Position/',NULL,0,1,0),(24346,'1,23940,24275,24340,24346','/Thesauri/Fields/Position/',NULL,0,1,0),(24347,'1,23940,24275,24340,24347','/Thesauri/Fields/Position/',NULL,0,1,0),(24348,'1,23940,24275,24340,24348','/Thesauri/Fields/Position/',NULL,0,1,0),(24349,'1,23940,24275,24340,24349','/Thesauri/Fields/Position/',NULL,0,1,0),(24350,'1,23940,24275,24340,24350','/Thesauri/Fields/Position/',NULL,0,1,0),(24351,'1,23940,24275,24340,24351','/Thesauri/Fields/Position/',NULL,0,1,0),(24352,'1,23940,24275,24340,24352','/Thesauri/Fields/Position/',NULL,0,1,0),(24353,'1,23940,24275,24340,24353','/Thesauri/Fields/Position/',NULL,0,1,0),(24354,'1,23940,24275,24340,24354','/Thesauri/Fields/Position/',NULL,0,1,0),(24355,'1,23940,24275,24340,24355','/Thesauri/Fields/Position/',NULL,0,1,0),(24356,'1,23940,24275,24340,24356','/Thesauri/Fields/Position/',NULL,0,1,0),(24357,'1,23940,24275,24340,24357','/Thesauri/Fields/Position/',NULL,0,1,0),(24358,'1,23940,24275,24340,24358','/Thesauri/Fields/Position/',NULL,0,1,0),(24359,'1,23940,24275,24340,24359','/Thesauri/Fields/Position/',NULL,0,1,0),(24360,'1,23940,24275,24340,24360','/Thesauri/Fields/Position/',NULL,0,1,0),(24361,'1,23940,24275,24340,24361','/Thesauri/Fields/Position/',NULL,0,1,0),(24362,'1,23940,24275,24340,24362','/Thesauri/Fields/Position/',NULL,0,1,0),(24363,'1,23940,24275,24340,24363','/Thesauri/Fields/Position/',NULL,0,1,0),(24364,'1,23940,24275,24340,24364','/Thesauri/Fields/Position/',NULL,0,1,0),(24365,'1,23940,24275,24340,24365','/Thesauri/Fields/Position/',NULL,0,1,0),(24366,'1,23940,24275,24340,24366','/Thesauri/Fields/Position/',NULL,0,1,0),(24367,'1,23940,24275,24340,24367','/Thesauri/Fields/Position/',NULL,0,1,0),(24368,'1,23940,24275,24340,24368','/Thesauri/Fields/Position/',NULL,0,1,0),(24369,'1,23940,24275,24340,24369','/Thesauri/Fields/Position/',NULL,0,1,0),(24370,'1,23940,24275,24340,24370','/Thesauri/Fields/Position/',NULL,0,1,0),(24371,'1,23940,24275,24340,24371','/Thesauri/Fields/Position/',NULL,0,1,0),(24372,'1,23940,24275,24340,24372','/Thesauri/Fields/Position/',NULL,0,1,0),(24373,'1,23940,24275,24373','/Thesauri/Fields/',NULL,0,1,0),(24374,'1,23940,24275,24373,24374','/Thesauri/Fields/Location/',NULL,0,1,0),(24375,'1,23940,24275,24373,24375','/Thesauri/Fields/Location/',NULL,0,1,0),(24376,'1,23940,24275,24373,24376','/Thesauri/Fields/Location/',NULL,0,1,0),(24377,'1,23940,24275,24373,24377','/Thesauri/Fields/Location/',NULL,0,1,0),(24378,'1,23940,24275,24373,24378','/Thesauri/Fields/Location/',NULL,0,1,0),(24379,'1,23940,24275,24373,24379','/Thesauri/Fields/Location/',NULL,0,1,0),(24380,'1,23940,24275,24373,24380','/Thesauri/Fields/Location/',NULL,0,1,0),(24381,'1,23940,24275,24373,24381','/Thesauri/Fields/Location/',NULL,0,1,0),(24382,'1,23940,24275,24373,24382','/Thesauri/Fields/Location/',NULL,0,1,0),(24383,'1,23940,24275,24373,24383','/Thesauri/Fields/Location/',NULL,0,1,0),(24384,'1,23940,24275,24373,24384','/Thesauri/Fields/Location/',NULL,0,1,0),(24385,'1,23940,24275,24373,24385','/Thesauri/Fields/Location/',NULL,0,1,0),(24386,'1,23940,24275,24373,24386','/Thesauri/Fields/Location/',NULL,0,1,0),(24387,'1,23940,24275,24373,24387','/Thesauri/Fields/Location/',NULL,0,1,0),(24388,'1,23940,24275,24373,24388','/Thesauri/Fields/Location/',NULL,0,1,0),(24389,'1,23940,24275,24373,24389','/Thesauri/Fields/Location/',NULL,0,1,0),(24390,'1,23940,24275,24390','/Thesauri/Fields/',NULL,0,1,0),(24391,'1,23940,24275,24390,24391','/Thesauri/Fields/Court/',NULL,0,1,0),(24392,'1,23940,24275,24390,24392','/Thesauri/Fields/Court/',NULL,0,1,0),(24393,'1,23940,24275,24390,24393','/Thesauri/Fields/Court/',NULL,0,1,0),(24394,'1,23940,24275,24390,24394','/Thesauri/Fields/Court/',NULL,0,1,0),(24395,'1,23940,24275,24390,24395','/Thesauri/Fields/Court/',NULL,0,1,0),(24396,'1,23940,24275,24390,24396','/Thesauri/Fields/Court/',NULL,0,1,0),(24397,'1,23940,24275,24390,24397','/Thesauri/Fields/Court/',NULL,0,1,0),(24398,'1,23940,24275,24390,24398','/Thesauri/Fields/Court/',NULL,0,1,0),(24399,'1,23940,24275,24399','/Thesauri/Fields/',NULL,0,1,0),(24400,'1,23940,24275,24399,24400','/Thesauri/Fields/Tags/',NULL,0,1,0),(24401,'1,23940,24275,24399,24401','/Thesauri/Fields/Tags/',NULL,0,1,0),(24402,'1,23940,24275,24399,24402','/Thesauri/Fields/Tags/',NULL,0,1,0),(24403,'1,23940,24275,24399,24403','/Thesauri/Fields/Tags/',NULL,0,1,0),(24404,'1,23940,24275,24399,24404','/Thesauri/Fields/Tags/',NULL,0,1,0),(24405,'1,23940,24275,24399,24405','/Thesauri/Fields/Tags/',NULL,0,1,0),(24406,'1,23940,24275,24399,24406','/Thesauri/Fields/Tags/',NULL,0,1,0),(24407,'1,23940,24275,24399,24407','/Thesauri/Fields/Tags/',NULL,0,1,0),(24408,'1,23940,24275,24399,24408','/Thesauri/Fields/Tags/',NULL,0,1,0),(24409,'1,23940,24275,24399,24409','/Thesauri/Fields/Tags/',NULL,0,1,0),(24410,'1,23940,24275,24399,24410','/Thesauri/Fields/Tags/',NULL,0,1,0),(24411,'1,23940,24275,24399,24411','/Thesauri/Fields/Tags/',NULL,0,1,0),(24412,'1,23940,24275,24399,24412','/Thesauri/Fields/Tags/',NULL,0,1,0),(24413,'1,23940,24275,24399,24413','/Thesauri/Fields/Tags/',NULL,0,1,0),(24414,'1,23940,24275,24399,24414','/Thesauri/Fields/Tags/',NULL,0,1,0),(24415,'1,23940,24275,24399,24415','/Thesauri/Fields/Tags/',NULL,0,1,0),(24416,'1,23940,24275,24399,24416','/Thesauri/Fields/Tags/',NULL,0,1,0),(24417,'1,23940,24275,24399,24417','/Thesauri/Fields/Tags/',NULL,0,1,0),(24418,'1,23940,24275,24399,24418','/Thesauri/Fields/Tags/',NULL,0,1,0),(24419,'1,23940,24275,24399,24419','/Thesauri/Fields/Tags/',NULL,0,1,0),(24420,'1,23940,24275,24399,24420','/Thesauri/Fields/Tags/',NULL,0,1,0),(24421,'1,23940,24275,24399,24421','/Thesauri/Fields/Tags/',NULL,0,1,0),(24422,'1,23940,24275,24399,24422','/Thesauri/Fields/Tags/',NULL,0,1,0),(24423,'1,23940,24275,24399,24423','/Thesauri/Fields/Tags/',NULL,0,1,0),(24424,'1,23940,24275,24399,24424','/Thesauri/Fields/Tags/',NULL,0,1,0),(24425,'1,23940,24275,24399,24425','/Thesauri/Fields/Tags/',NULL,0,1,0),(24426,'1,23940,24275,24399,24426','/Thesauri/Fields/Tags/',NULL,0,1,0),(24427,'1,23940,24275,24399,24427','/Thesauri/Fields/Tags/',NULL,0,1,0),(24428,'1,23940,24275,24399,24428','/Thesauri/Fields/Tags/',NULL,0,1,0),(24429,'1,23940,24275,24399,24429','/Thesauri/Fields/Tags/',NULL,0,1,0),(24430,'1,23940,24275,24399,24430','/Thesauri/Fields/Tags/',NULL,0,1,0),(24431,'1,23940,24275,24399,24431','/Thesauri/Fields/Tags/',NULL,0,1,0),(24432,'1,23940,24275,24399,24432','/Thesauri/Fields/Tags/',NULL,0,1,0),(24433,'1,23940,24275,24399,24433','/Thesauri/Fields/Tags/',NULL,0,1,0),(24434,'1,23940,24275,24399,24434','/Thesauri/Fields/Tags/',NULL,0,1,0),(24435,'1,23940,24275,24399,24435','/Thesauri/Fields/Tags/',NULL,0,1,0),(24436,'1,23940,24275,24399,24436','/Thesauri/Fields/Tags/',NULL,0,1,0),(24437,'1,23940,24275,24399,24437','/Thesauri/Fields/Tags/',NULL,0,1,0),(24438,'1,23940,24275,24399,24438','/Thesauri/Fields/Tags/',NULL,0,1,0),(24439,'1,23940,24275,24399,24439','/Thesauri/Fields/Tags/',NULL,0,1,0),(24440,'1,23940,24275,24399,24440','/Thesauri/Fields/Tags/',NULL,0,1,0),(24441,'1,23940,24441','/Thesauri/',NULL,0,1,0),(24442,'1,23940,24442','/Thesauri/',NULL,0,1,0),(24443,'1,24042,24052,24072,24443','/Templates/System/tasks/',NULL,0,1,0),(24444,'1,24042,24052,24072,24444','/Templates/System/tasks/',NULL,0,1,0),(24445,'1,24042,24052,24072,24444,24445','/Templates/System/tasks/allday/',NULL,0,1,0),(24446,'1,24042,24052,24072,24444,24446','/Templates/System/tasks/allday/',NULL,0,1,0),(24447,'1,24042,24052,24072,24444,24447','/Templates/System/tasks/allday/',NULL,0,1,0),(24448,'1,24042,24052,24072,24444,24448','/Templates/System/tasks/allday/',NULL,0,1,0),(24449,'1,24042,24052,24072,24449','/Templates/System/tasks/',NULL,0,1,0),(24450,'1,24042,24052,24072,24450','/Templates/System/tasks/',NULL,0,1,0),(24451,'1,24042,24052,24072,24451','/Templates/System/tasks/',NULL,0,1,0),(24452,'1,24042,24052,24072,24452','/Templates/System/tasks/',NULL,0,1,0),(24453,'1,24042,24052,24072,24453','/Templates/System/tasks/',NULL,0,1,0),(24454,'1,24042,24052,24072,24453,24454','/Templates/System/tasks/reminders/',NULL,0,1,0),(24455,'1,24042,24052,24072,24453,24455','/Templates/System/tasks/reminders/',NULL,0,1,0),(24456,'1,24042,24052,24073,24456','/Templates/System/event/',NULL,0,1,0),(24457,'1,24042,24052,24073,24457','/Templates/System/event/',NULL,0,1,0),(24458,'1,24042,24052,24073,24457,24458','/Templates/System/event/allday/',NULL,0,1,0),(24459,'1,24042,24052,24073,24457,24459','/Templates/System/event/allday/',NULL,0,1,0),(24460,'1,24042,24052,24073,24457,24460','/Templates/System/event/allday/',NULL,0,1,0),(24461,'1,24042,24052,24073,24457,24461','/Templates/System/event/allday/',NULL,0,1,0),(24462,'1,24042,24052,24073,24462','/Templates/System/event/',NULL,0,1,0),(24463,'1,24042,24052,24073,24463','/Templates/System/event/',NULL,0,1,0),(24464,'1,24042,24052,24073,24464','/Templates/System/event/',NULL,0,1,0),(24465,'1,24042,24052,24073,24465','/Templates/System/event/',NULL,0,1,0),(24466,'1,24042,24052,24073,24466','/Templates/System/event/',NULL,0,1,0),(24467,'1,24042,24052,24073,24466,24467','/Templates/System/event/reminders/',NULL,0,1,0),(24468,'1,24042,24052,24073,24466,24468','/Templates/System/event/reminders/',NULL,0,1,0),(24469,'1,24042,24052,24078,24469','/Templates/System/milestone/',NULL,0,1,0),(24470,'1,24042,24052,24078,24470','/Templates/System/milestone/',NULL,0,1,0),(24471,'1,24042,24052,24078,24470,24471','/Templates/System/milestone/allday/',NULL,0,1,0),(24472,'1,24042,24052,24078,24470,24472','/Templates/System/milestone/allday/',NULL,0,1,0),(24473,'1,24042,24052,24078,24470,24473','/Templates/System/milestone/allday/',NULL,0,1,0),(24474,'1,24042,24052,24078,24470,24474','/Templates/System/milestone/allday/',NULL,0,1,0),(24475,'1,24042,24052,24078,24475','/Templates/System/milestone/',NULL,0,1,0),(24476,'1,24042,24052,24078,24476','/Templates/System/milestone/',NULL,0,1,0),(24477,'1,24042,24052,24078,24477','/Templates/System/milestone/',NULL,0,1,0),(24478,'1,24042,24052,24078,24478','/Templates/System/milestone/',NULL,0,1,0),(24479,'1,24042,24052,24078,24479','/Templates/System/milestone/',NULL,0,1,0),(24480,'1,24042,24052,24078,24479,24480','/Templates/System/milestone/reminders/',NULL,0,1,0),(24481,'1,24042,24052,24078,24479,24481','/Templates/System/milestone/reminders/',NULL,0,1,0),(24484,'1,24042,24484','/Templates/',NULL,0,1,0),(24485,'1,24042,24484,24485','/Templates/office/',NULL,0,1,0),(24486,'1,24042,24484,24486','/Templates/office/',NULL,0,1,0),(24487,'1,24042,24484,24487','/Templates/office/',NULL,0,1,0),(24488,'1,24042,24484,24488','/Templates/office/',NULL,0,1,0),(24489,'1,24042,24484,24489','/Templates/office/',NULL,0,1,0),(24490,'1,24042,24484,24490','/Templates/office/',NULL,0,1,0),(24493,'24493','',NULL,0,NULL,0),(24494,'24493,24494','/',NULL,0,NULL,0),(24503,'1,23940,24223,24503','/Thesauri/System/',NULL,0,1,0),(24504,'1,23940,24223,24503,24504','/Thesauri/System/Colors/',NULL,0,1,0),(24505,'1,23940,24223,24503,24505','/Thesauri/System/Colors/',NULL,0,1,0),(24506,'1,23940,24223,24503,24506','/Thesauri/System/Colors/',NULL,0,1,0),(24507,'1,23940,24223,24503,24507','/Thesauri/System/Colors/',NULL,0,1,0),(24508,'1,23940,24223,24503,24508','/Thesauri/System/Colors/',NULL,0,1,0),(24509,'1,23940,24223,24503,24509','/Thesauri/System/Colors/',NULL,0,1,0),(24510,'1,23940,24223,24503,24510','/Thesauri/System/Colors/',NULL,0,1,0),(24511,'1,23940,24223,24503,24511','/Thesauri/System/Colors/',NULL,0,1,0),(24512,'1,23940,24223,24503,24512','/Thesauri/System/Colors/',NULL,0,1,0),(24513,'1,23940,24223,24503,24513','/Thesauri/System/Colors/',NULL,0,1,0),(24514,'1,24042,24052,24072,24514','/Templates/System/tasks/',NULL,0,1,0),(24515,'1,24042,24052,24078,24515','/Templates/System/milestone/',NULL,0,1,0),(24516,'1,24042,24052,24073,24516','/Templates/System/event/',NULL,0,1,0),(24517,'1,24042,24484,24517','/Templates/office/',NULL,0,1,0),(24523,'1,24042,24052,24074,24523','/Templates/System/folder/',NULL,0,1,0),(24562,'24562','',NULL,0,NULL,0),(24563,'24562,24563','/',NULL,0,NULL,0),(24614,'1,23940,24223,24248,24614','/Thesauri/System/Case Folders/',NULL,0,1,0),(24616,'1,23940,24223,24248,24616','/Thesauri/System/Case Folders/',NULL,0,1,0),(24648,'1,23940,24223,24248,24648','/Thesauri/System/Case Folders/',NULL,0,1,0),(24650,'1,23940,24223,24248,24650','/Thesauri/System/Case Folders/',NULL,0,1,0),(24652,'1,23940,24223,24248,24652','/Thesauri/System/Case Folders/',NULL,0,1,0),(24653,'1,23940,24223,24248,24653','/Thesauri/System/Case Folders/',NULL,0,1,0),(24822,'1,24042,24052,24822','/Templates/System/',NULL,0,1,0),(24823,'1,24042,24052,24822,24823','/Templates/System/Comment/',NULL,0,1,0),(24834,'1,24834','/',NULL,0,1,0),(24848,'24848','',NULL,0,NULL,0),(24849,'24848,24849','/',NULL,0,NULL,0),(25095,'1,24042,24195,25095','/Templates/Action/',NULL,0,1,0),(25100,'1,24042,24195,25100','/Templates/Action/',NULL,0,1,0),(25114,'1,25114','/',NULL,0,1,0),(25115,'25115','/',NULL,1,5,0),(25116,'25115,25116','/',NULL,0,5,0),(25117,'1,24042,24195,25117','/Templates/Action/',NULL,0,1,0),(25119,'25119','/',NULL,1,6,0),(25120,'25119,25120','/',NULL,0,6,0),(25121,'1,23490,25121','/ECHR Cases/',25121,0,1,0),(25122,'1,23490,25121,25122','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25123,'1,23490,25121,25123','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25124,'1,23490,25121,25124','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25125,'1,23490,25121,25125','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25126,'1,23490,25121,25126','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25127,'1,23490,25121,25127','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25128,'1,23490,25128','/ECHR Cases/',25128,0,1,0),(25129,'1,23490,25128,25129','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25130,'1,23490,25128,25130','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25131,'1,23490,25128,25131','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25132,'1,23490,25128,25132','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25133,'1,23490,25128,25133','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25134,'1,23490,25128,25134','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25135,'1,23940,24275,24308,25135','/Thesauri/Fields/Country/',NULL,0,1,0),(25136,'1,23490,25128,25133,25136','/ECHR Cases/Zhuk vs. Belarus/4. Research/',25128,0,1,0),(25137,'1,23490,25128,25130,25137','/ECHR Cases/Zhuk vs. Belarus/1. Correspondence/',25128,0,1,0),(25138,'1,23490,25128,25133,25138','/ECHR Cases/Zhuk vs. Belarus/4. Research/',25128,0,1,0),(25139,'1,25114,25139','/IACHR Cases/',25139,0,1,0),(25140,'1,25114,25139,25140','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25141,'1,25114,25139,25141','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25142,'1,25114,25139,25142','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25143,'1,25114,25139,25143','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25144,'1,25114,25139,25144','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25145,'1,25114,25139,25145','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25146,'1,23490,25128,25146','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25147,'1,23940,24275,24308,25147','/Thesauri/Fields/Country/',NULL,0,1,0),(25148,'1,23490,25128,25130,25148','/ECHR Cases/Zhuk vs. Belarus/1. Correspondence/',25128,0,1,0),(25149,'1,23940,24275,24399,25149','/Thesauri/Fields/Tags/',NULL,0,1,0),(25150,'1,23490,25128,25130,25154,25150','/ECHR Cases/Zhuk vs. Belarus/1. Correspondence/Condemnation of Execution/',25128,0,1,0),(25151,'1,23490,25128,25132,25151','/ECHR Cases/Zhuk vs. Belarus/3. Evidence/',25128,0,1,0),(25152,'1,25114,25139,25152','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25153,'1,25114,25139,25153','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25154,'1,23490,25128,25130,25154','/ECHR Cases/Zhuk vs. Belarus/1. Correspondence/',25128,0,1,0),(25155,'1,25114,25139,25155','/IACHR Cases/María Claudia García Gelman vs. Uruguay/',25139,0,1,0),(25156,'1,25114,25139,25155,25156','/IACHR Cases/María Claudia García Gelman vs. Uruguay/an press conference/',25139,0,1,0),(25157,'1,25114,25139,25155,25157','/IACHR Cases/María Claudia García Gelman vs. Uruguay/an press conference/',25139,0,1,0),(25158,'1,23490,25128,25158','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25159,'1,25114,25139,25155,25159','/IACHR Cases/María Claudia García Gelman vs. Uruguay/an press conference/',25139,0,1,0),(25160,'1,23490,25128,25132,25160','/ECHR Cases/Zhuk vs. Belarus/3. Evidence/',25128,0,1,0),(25161,'1,25114,25139,25155,25161','/IACHR Cases/María Claudia García Gelman vs. Uruguay/an press conference/',25139,0,1,0),(25162,'1,23490,25128,25162','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25163,'1,23490,25128,25163','/ECHR Cases/Zhuk vs. Belarus/',25128,0,1,0),(25164,'1,23490,25121,25126,25164','/ECHR Cases/Krasuski vs. Poland/4. Research/',25121,0,1,0),(25165,'1,23490,25121,25126,25165','/ECHR Cases/Krasuski vs. Poland/4. Research/',25121,0,1,0),(25166,'1,23490,25121,25126,25166','/ECHR Cases/Krasuski vs. Poland/4. Research/',25121,0,1,0),(25167,'1,23490,25121,25167','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25168,'1,23490,25121,25168','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25169,'1,23490,25121,25127,25169','/ECHR Cases/Krasuski vs. Poland/5. Administrative/',25121,0,1,0),(25170,'1,23490,25170','/ECHR Cases/',25170,0,1,0),(25171,'1,23490,25170,25171','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25172,'1,23490,25170,25172','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25173,'1,23490,25170,25173','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25174,'1,23490,25170,25174','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25175,'1,23490,25170,25175','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25176,'1,23490,25170,25176','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25177,'1,23490,25170,25177','/ECHR Cases/E.E. vs. Russia/',25170,0,1,0),(25178,'1,23490,25170,25175,25178','/ECHR Cases/E.E. vs. Russia/4. Research/',25170,0,1,0),(25179,'1,23490,25170,25175,25179','/ECHR Cases/E.E. vs. Russia/4. Research/',25170,0,1,0),(25180,'1,23490,25170,25174,25180','/ECHR Cases/E.E. vs. Russia/3. Evidence/',25170,0,1,0),(25181,'25181','/',NULL,1,7,0),(25182,'25181,25182','/',NULL,0,7,0),(25183,'25183','/',NULL,1,8,0),(25184,'25183,25184','/',NULL,0,8,0),(25185,'1,23490,25121,25127,25185','/ECHR Cases/Krasuski vs. Poland/5. Administrative/',25121,0,1,0),(25186,'1,25201,25186','/Domestic Cases/',25186,0,1,0),(25187,'1,25201,25186,25187','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25188,'1,25201,25186,25188','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25189,'1,25201,25186,25189','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25190,'1,25201,25186,25190','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25191,'1,25201,25186,25191','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25192,'1,25201,25186,25192','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/',25186,0,1,0),(25193,'1,23940,24275,24308,25193','/Thesauri/Fields/Country/',NULL,0,1,0),(25194,'1,23940,24275,24308,25194','/Thesauri/Fields/Country/',NULL,0,1,0),(25195,'1,23940,24275,24399,25195','/Thesauri/Fields/Tags/',NULL,0,1,0),(25196,'1,25201,25186,25191,25196','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/4. Research/',25186,0,1,0),(25197,'1,25201,25186,25191,25197','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/4. Research/',25186,0,1,0),(25198,'1,25201,25186,25191,25198','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/4. Research/',25186,0,1,0),(25199,'1,25201,25186,25189,25199','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/2. Filings/',25186,0,1,0),(25200,'1,23490,25121,25200','/ECHR Cases/Krasuski vs. Poland/',25121,0,1,0),(25201,'1,25201','/',NULL,0,1,0),(25202,'1,25201,25186,25187,25202','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/0. Incoming/',25186,0,1,0),(25203,'1,25201,25186,25190,25203','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/3. Evidence/',25186,0,1,0),(25204,'1,25201,25186,25189,25199,25204','/Domestic Cases/Privacy International (on behalf of Tadesse Kersmo) vs.  UK/2. Filings/Complaint to National Cyber Crime Unit of the National Crime Agency/',25186,0,1,0),(25205,'1,23490,25121,25167,25205','/ECHR Cases/Krasuski vs. Poland/Do additional case law research/',25121,0,1,0),(25206,'1,25201,25206','/Domestic Cases/',25206,0,1,0),(25207,'1,25201,25206,25207','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25208,'1,25201,25206,25208','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25209,'1,25201,25206,25209','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25210,'1,25201,25206,25210','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25211,'1,25201,25206,25211','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25212,'1,25201,25206,25212','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25213,'1,23940,24275,24308,25213','/Thesauri/Fields/Country/',NULL,0,1,0),(25214,'1,25201,25206,25214','/Domestic Cases/RightsCon vs. NSA, GCHQ/',25206,0,1,0),(25215,'1,25201,25206,25210,25215','/Domestic Cases/RightsCon vs. NSA, GCHQ/3. Evidence/',25206,0,1,0),(25216,'1,25201,25206,25208,25216','/Domestic Cases/RightsCon vs. NSA, GCHQ/1. Correspondence/',25206,0,1,0),(25217,'1,25201,25206,25212,25217','/Domestic Cases/RightsCon vs. NSA, GCHQ/5. Administrative/',25206,0,1,0),(25218,'1,25201,25206,25212,25217,25218','/Domestic Cases/RightsCon vs. NSA, GCHQ/5. Administrative/Lawyers working on this case - security measures.ods/',25206,0,1,0),(25219,'1,25201,25206,25212,25217,25219','/Domestic Cases/RightsCon vs. NSA, GCHQ/5. Administrative/Lawyers working on this case - security measures.ods/',25206,0,1,0),(25220,'1,25201,25206,25212,25217,25220','/Domestic Cases/RightsCon vs. NSA, GCHQ/5. Administrative/Lawyers working on this case - security measures.ods/',25206,0,1,0),(25221,'1,25201,25206,25212,25217,25221','/Domestic Cases/RightsCon vs. NSA, GCHQ/5. Administrative/Lawyers working on this case - security measures.ods/',25206,0,1,0),(25222,'1,25201,25206,25211,25222','/Domestic Cases/RightsCon vs. NSA, GCHQ/4. Research/',25206,0,1,0),(25223,'1,25201,25206,25211,25223','/Domestic Cases/RightsCon vs. NSA, GCHQ/4. Research/',25206,0,1,0),(25224,'1,25201,25206,25208,25224','/Domestic Cases/RightsCon vs. NSA, GCHQ/1. Correspondence/',25206,0,1,0),(25225,'1,25201,25225','/Domestic Cases/',25225,0,1,0),(25226,'1,25201,25225,25226','/Domestic Cases/John v. US/',25225,0,1,0),(25227,'1,25201,25225,25227','/Domestic Cases/John v. US/',25225,0,1,0),(25228,'1,25201,25225,25228','/Domestic Cases/John v. US/',25225,0,1,0),(25229,'1,25201,25225,25229','/Domestic Cases/John v. US/',25225,0,1,0),(25230,'1,25201,25225,25230','/Domestic Cases/John v. US/',25225,0,1,0),(25231,'1,25201,25225,25231','/Domestic Cases/John v. US/',25225,0,1,0),(25232,'1,25201,25225,25227,25232','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25233,'1,25201,25225,25227,25233','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25234,'1,25201,25225,25227,25234','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25235,'1,25201,25225,25227,25235','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25236,'1,25201,25225,25227,25236','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25237,'1,25201,25225,25227,25237','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25238,'1,25201,25225,25227,25238','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25239,'1,25201,25225,25227,25239','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25240,'1,25201,25225,25227,25240','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25241,'1,25201,25225,25227,25241','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25242,'1,25201,25225,25227,25242','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25243,'1,25201,25225,25227,25243','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25244,'1,25201,25225,25227,25244','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25245,'1,25201,25225,25227,25245','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25246,'1,25201,25225,25227,25246','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25247,'1,25201,25225,25227,25247','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25248,'1,25201,25225,25227,25248','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25249,'1,25201,25225,25227,25249','/Domestic Cases/John v. US/1. Correspondence/',25225,0,1,0),(25250,'1,25201,25225,25229,25250','/Domestic Cases/John v. US/3. Evidence/',25225,0,1,0),(25251,'1,25201,25225,25231,25251','/Domestic Cases/John v. US/5. Administrative/',25225,0,1,0);

/*Table structure for table `tree_user_config` */

DROP TABLE IF EXISTS `tree_user_config`;

CREATE TABLE `tree_user_config` (
  `guid` varchar(50) NOT NULL COMMENT 'id of the tree node or vitual node',
  `user_id` int(10) unsigned NOT NULL,
  `cfg` text,
  PRIMARY KEY (`guid`,`user_id`),
  KEY `tree_user_config__user_id` (`user_id`),
  CONSTRAINT `tree_user_config__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_user_config` */

/*Table structure for table `user_subscriptions` */

DROP TABLE IF EXISTS `user_subscriptions`;

CREATE TABLE `user_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `object_id` bigint(20) unsigned NOT NULL,
  `recursive` tinyint(1) NOT NULL DEFAULT '0',
  `sdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'subscription timestamp',
  PRIMARY KEY (`id`),
  KEY `FK_user_subscriptions__object_id` (`object_id`),
  KEY `FK_user_subscriptions__user_id` (`user_id`),
  CONSTRAINT `FK_user_subscriptions__object_id` FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_user_subscriptions__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `user_subscriptions` */

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
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups` */

insert  into `users_groups`(`id`,`type`,`system`,`name`,`first_name`,`last_name`,`l1`,`l2`,`l3`,`l4`,`sex`,`email`,`photo`,`password`,`password_change`,`recover_hash`,`language_id`,`cfg`,`data`,`last_login`,`login_successful`,`login_from_ip`,`last_logout`,`last_action_time`,`enabled`,`cid`,`cdate`,`uid`,`udate`,`did`,`ddate`,`searchField`) values (1,2,0,'root','Administrator','','Administrator','Administrator','Administrator','Administrator','m','test@test.com','1_m6.png','50775b4f5109fd22c46dabb17f710c17','2015-05-12',NULL,1,'{\"short_date_format\":\"%m\\/%d\\/%Y\",\"long_date_format\":\"%F %j, %Y\",\"country_code\":\"\",\"phone\":\"\",\"timezone\":\"\",\"security\":{\"recovery_email\":true,\"email\":\"oburlaca@gmail.com\"},\"state\":{\"mAc\":{\"collapsed\":false,\"width\":250,\"weight\":-10},\"btree\":{\"paths\":[\"\\/0\",\"\\/0\\/3-tasks\",\"\\/0\\/1\",\"\\/0\\/1\\/24042\",\"\\/0\\/1\\/24042\\/24052\"],\"width\":250,\"selected\":\"\\/0\\/1\\/24042\\/24052\\/24075\"},\"mopp\":{\"weight\":-20},\"oevg\":{\"columns\":{\"title\":{\"idx\":0,\"flex\":290},\"value\":{\"idx\":1,\"flex\":290}},\"group\":null},\"oew24072\":{\"width\":600,\"height\":450,\"maximized\":false,\"size\":{\"width\":600,\"height\":450},\"pos\":[924,182]}}}','{\"email\": \"test@test.com\"}','2015-02-17 09:18:34',1,'|127.0.0.1|',NULL,'2014-05-19 19:30:36',1,1,NULL,1,'2013-03-20 12:57:29',NULL,NULL,' root Administrator Administrator Administrator Administrator test@test.com '),(233,1,1,'system','SYSTEM','','SYSTEM','SYSTÈME','СИСТЕМА',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:09',NULL,NULL,' system SYSTEM SYSTÈME СИСТЕМА   '),(234,1,1,'everyone','Everyone','','Everyone','Tous','Все',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:10',NULL,NULL,' everyone Everyone Tous Все   '),(235,1,0,'Administrators','Administrators','','Administrators','Administrateurs','Администраторы',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:10',NULL,NULL,' Administrators Administrators Administrateurs Администраторы   '),(236,1,0,'Berlin','Managers','','Managers','Gestionnaires','Менеджеры',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',1,'2013-03-20 13:08:10',NULL,NULL,' Berlin Managers Gestionnaires Менеджеры   '),(238,1,0,'Users','Users','','Users','Users','Пользователи',NULL,NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-03-17 09:35:11',NULL,'2013-03-20 13:08:11',NULL,NULL,' Users Users Users Пользователи   '),(240,2,0,'enorman','Erik','Norman','Erik Norman','Erik Norman','Erik Norman',NULL,'m','oburlaca@gmail.com','240_m1.jpg','8fe8b64432d3b41f7dbc5d8024337e04','2014-03-31',NULL,1,'{\"short_date_format\":\"%d\\/%m\\/%Y\",\"long_date_format\":\"%F %j, %Y\",\"country_code\":\"+32\",\"phone\":\"\",\"timezone\":\"Cairo\",\"security\":{\"recovery_email\":true,\"email\":\"oburlaca@gmail.com\"},\"TZ\":\"Africa\\/Cairo\",\"canAddUsers\":\"true\",\"canAddGroups\":\"true\"}','[]','2014-04-01 09:23:27',1,'|109.185.172.18|',NULL,'2014-04-01 20:39:15',1,232,'2013-05-24 14:05:01',1,'0000-00-00 00:00:00',NULL,NULL,' enorman Erik Norman Erik Norman Erik Norman  oburlaca@gmail.com '),(242,1,0,'Moscow','Moscow','','Moscow','Moscow','Moscow','Moscow',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:03',1,'0000-00-00 00:00:00',NULL,NULL,' Moscow Moscow Moscow Moscow Moscow  '),(243,1,0,'New York','New York','','New York','New York','New York','New York',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:10',1,'0000-00-00 00:00:00',NULL,NULL,' New York New York New York New York New York  '),(244,1,0,'Paris','Paris','','Paris','Paris','Paris','Paris',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:20',1,'0000-00-00 00:00:00',NULL,NULL,' Paris Paris Paris Paris Paris  '),(245,1,0,'London','London','','London','London','London','London',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:28',1,'0000-00-00 00:00:00',NULL,NULL,' London London London London London  '),(246,1,0,'Buenos Aires','Buenos Aires','','Buenos Aires','Buenos Aires','Buenos Aires','Buenos Aires',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:38',1,'0000-00-00 00:00:00',NULL,NULL,' Buenos Aires Buenos Aires Buenos Aires Buenos Aires Buenos Aires  '),(247,1,0,'Tokyo','Tokyo','','Tokyo','Tokyo','Tokyo','Tokyo',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:48',1,'0000-00-00 00:00:00',NULL,NULL,' Tokyo Tokyo Tokyo Tokyo Tokyo  '),(248,1,0,'San Francisco','San Francisco','','San Francisco','San Francisco','San Francisco','San Francisco',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:53:57',1,'0000-00-00 00:00:00',NULL,NULL,' San Francisco San Francisco San Francisco San Francisco San Francisco  '),(249,1,0,'Lima','Lima','','Lima','Lima','Lima','Lima',NULL,'',NULL,NULL,NULL,NULL,1,'{}',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2013-05-29 09:54:06',1,'0000-00-00 00:00:00',NULL,NULL,' Lima Lima Lima Lima Lima  '),(266,2,0,'rstone','Robin','Stone',NULL,NULL,NULL,NULL,NULL,'danieldesposito@huridocs.org','266_m5.jpg','8fe8b64432d3b41f7dbc5d8024337e04',NULL,NULL,1,NULL,NULL,'2014-08-28 16:00:49',-1,'|127.0.0.1|',NULL,'2014-03-03 03:53:26',1,1,'2014-02-28 15:55:43',1,'0000-00-00 00:00:00',NULL,NULL,' rstone     danieldesposito@huridocs.org '),(267,2,0,'ladkins','Lorraine','Adkins',NULL,NULL,NULL,NULL,'','test@test.com','267_f2.jpg','8fe8b64432d3b41f7dbc5d8024337e04',NULL,NULL,1,'{\"country_code\":\"\",\"phone\":\"\",\"timezone\":\"\",\"short_date_format\":\"%m\\/%d\\/%Y\",\"long_date_format\":\"%F %j, %Y\"}','{\"position\":24348,\"language_id\":1}','2014-05-19 07:35:55',1,'|109.185.172.18|',NULL,'2014-05-19 07:36:22',1,240,'2014-02-28 19:55:37',240,'0000-00-00 00:00:00',NULL,NULL,' ladkins     test@test.com '),(268,2,0,'mcrawford','Marc','Crawford',NULL,NULL,NULL,NULL,NULL,'test@test.com','268_m3.jpg','8fe8b64432d3b41f7dbc5d8024337e04',NULL,NULL,1,NULL,NULL,'2014-04-01 09:55:12',1,'|109.185.172.18|',NULL,'2014-04-01 10:59:18',1,240,'2014-02-28 20:05:10',240,'0000-00-00 00:00:00',NULL,NULL,' mcrawford     test@test.com '),(269,2,0,'rmack','Ray','Mack',NULL,NULL,NULL,NULL,NULL,'ray@test.com','269_m4.jpg','8fe8b64432d3b41f7dbc5d8024337e04',NULL,NULL,1,NULL,NULL,'2014-04-22 12:41:20',1,'|109.185.172.18|',NULL,'2014-04-22 12:41:20',1,240,'2014-02-28 20:06:32',240,'0000-00-00 00:00:00',NULL,NULL,' rmack     ray@test.com '),(270,2,0,'ctorres','Cecil','Torres',NULL,NULL,NULL,NULL,NULL,'cecil@test.com','270_f3.jpg','8fe8b64432d3b41f7dbc5d8024337e04',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,240,'2014-02-28 20:07:50',240,'0000-00-00 00:00:00',NULL,NULL,' ctorres     cecil@test.com ');

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

insert  into `users_groups_association`(`user_id`,`group_id`,`cid`,`cdate`,`uid`,`udate`) values (1,233,1,'2013-03-20 13:56:17',0,'2013-03-20 13:56:17'),(240,235,1,'2013-12-05 16:28:14',NULL,NULL),(240,238,232,'2013-05-24 14:05:01',NULL,NULL),(240,243,1,'2014-04-01 09:23:17',NULL,NULL),(266,235,1,'2014-02-28 15:55:43',NULL,NULL),(267,243,240,'2014-02-28 19:55:37',NULL,NULL),(268,242,240,'2014-03-02 23:50:21',NULL,NULL),(268,243,240,'2014-02-28 20:05:10',NULL,NULL),(269,248,240,'2014-02-28 20:06:32',NULL,NULL),(270,249,240,'2014-02-28 20:07:50',NULL,NULL);

/* Trigger structure for table `files` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_ai` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `files_ai` AFTER INSERT ON `files` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `files_au` AFTER UPDATE ON `files` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `files_ad` AFTER DELETE ON `files` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_content` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_content_bi` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `files_content_bi` BEFORE INSERT ON `files_content` FOR EACH ROW BEGIN
	if( (coalesce(new.size, 0) = 0) or (new.type like 'image%') ) THEN
		set new.skip_parsing = 1;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_content` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_content_au` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `files_content_au` AFTER UPDATE ON `files_content` FOR EACH ROW BEGIN
	update tree, files set tree.updated = (tree.updated | 1) where files.content_id = NEW.id and files.id = tree.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_ai` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `files_versions_ai` AFTER INSERT ON `files_versions` FOR EACH ROW BEGIN
	if(new.content_id is not null) THEN
		update files_content set ref_count = ref_count + 1 where id = new.content_id;
	end if;
    END */$$


DELIMITER ;

/* Trigger structure for table `files_versions` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `files_versions_au` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `files_versions_au` AFTER UPDATE ON `files_versions` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `files_versions_ad` AFTER DELETE ON `files_versions` FOR EACH ROW BEGIN
	IF(old.content_id IS NOT NULL) THEN
		UPDATE files_content SET ref_count = ref_count - 1 WHERE id = old.content_id;
	END IF;
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_bi` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tasks_bi` BEFORE INSERT ON `tasks` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tasks_ai` AFTER INSERT ON `tasks` FOR EACH ROW BEGIN
 	INSERT INTO tasks_responsible_users (task_id, user_id) 
		SELECT new.id, id 
		FROM users_groups 
		WHERE CONCAT(',',new.responsible_user_ids,',') LIKE CONCAT('%,',id,',%');
    END */$$


DELIMITER ;

/* Trigger structure for table `tasks` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tasks_bu` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tasks_bu` BEFORE UPDATE ON `tasks` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tasks_au` AFTER UPDATE ON `tasks` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `templates_structure_bi` BEFORE INSERT ON `templates_structure` FOR EACH ROW BEGIN
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
    END */$$


DELIMITER ;

/* Trigger structure for table `templates_structure` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `templates_structure_bu` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `templates_structure_bu` BEFORE UPDATE ON `templates_structure` FOR EACH ROW BEGIN
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

/* Trigger structure for table `translations` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `translation_bu` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `translation_bu` BEFORE UPDATE ON `translations` FOR EACH ROW BEGIN
	SET new.udate = CURRENT_TIMESTAMP;
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_bi` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tree_bi` BEFORE INSERT ON `tree` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tree_ai` AFTER INSERT ON `tree` FOR EACH ROW BEGIN
	/* get pids path, text path, case_id and store them in tree_info table*/
	declare tmp_new_case_id
		,tmp_new_security_set_id bigint unsigned default null;
	DECLARE tmp_new_pids TEXT DEFAULT '';
	/* check if inserted node is a case */
	if( 	(new.template_id is not null)
		and (select id from templates where (id = new.template_id) and (`type` = 'case') )
	) THEN
		SET tmp_new_case_id = new.id;
	END IF;
	select
		ti.pids
		,coalesce(tmp_new_case_id, ti.case_id)
		,ti.security_set_id
	into
		tmp_new_pids
		,tmp_new_case_id
		,tmp_new_security_set_id
	from tree t
	left join tree_info ti on t.id = ti.id
	where t.id = new.pid;
	SET tmp_new_pids = TRIM( ',' FROM CONCAT( tmp_new_pids, ',', new.id) );
	if(new.inherit_acl = 0) then
		set tmp_new_security_set_id = f_get_security_set_id(new.id);
	END IF;
	insert into tree_info (
		id
		,pids
		,case_id
		,security_set_id
	)
	values (
		new.id
		,tmp_new_pids
		,tmp_new_case_id
		,tmp_new_security_set_id
	);
	/* end of get pids path, text path, case_id and store them in tree_info table*/
    END */$$


DELIMITER ;

/* Trigger structure for table `tree` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_au` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tree_au` AFTER UPDATE ON `tree` FOR EACH ROW BEGIN
	DECLARE tmp_old_pids
		,tmp_new_pids TEXT DEFAULT '';
	
	DECLARE tmp_old_case_id
		,tmp_new_case_id
		,tmp_old_security_set_id
		,tmp_new_security_set_id BIGINT UNSIGNED DEFAULT NULL;
	DECLARE tmp_old_security_set
		,tmp_new_security_set VARCHAR(9999) DEFAULT '';
	DECLARE tmp_old_pids_length
		,tmp_old_security_set_length
		,tmp_acl_count INT UNSIGNED DEFAULT 0;
	
	/* get pids path, case_id and store them in tree_info table*/
	IF( (COALESCE(old.pid, 0) <> COALESCE(new.pid, 0) )
	    OR ( old.inherit_acl <> new.inherit_acl )
	  )THEN
		-- get old data
		SELECT
			ti.pids -- 1,2,3
			,ti.case_id -- null
			,ti.acl_count -- 2
			,ti.security_set_id -- 4
			,ts.set -- '1,3'
		INTO
			tmp_old_pids
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
			-- tmp_new_case_id already set above
			SET tmp_new_security_set_id = null;
			set tmp_new_security_set = '';
		ELSE
			SELECT
				ti.pids
				,COALESCE(tmp_new_case_id, ti.case_id)
				,ti.security_set_id
				,ts.set
			INTO
				tmp_new_pids
				,tmp_new_case_id
				,tmp_new_security_set_id
				,tmp_new_security_set
			FROM tree t
			LEFT JOIN tree_info ti ON t.id = ti.id
			LEFT JOIN tree_acl_security_sets ts ON ti.security_set_id = ts.id
			WHERE t.id = new.pid;
			
			SET tmp_new_pids = TRIM( ',' FROM CONCAT( tmp_new_pids, ',', new.id) );
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
		SET tmp_old_security_set_length = LENGTH( tmp_old_security_set ) +1;
		-- update node info with new data
		UPDATE tree_info
		SET	pids = tmp_new_pids
			,case_id = tmp_new_case_id
			,security_set_id = tmp_new_security_set_id
		WHERE id = new.id;
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

/*!50003 CREATE */ /*!50003 TRIGGER `tree_acl_ai` AFTER INSERT ON `tree_acl` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tree_acl_au` AFTER UPDATE ON `tree_acl` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tree_acl_ad` AFTER DELETE ON `tree_acl` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `tree_acl_security_sets_bi` BEFORE INSERT ON `tree_acl_security_sets` FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_acl_security_sets` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_acl_security_sets_bu` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tree_acl_security_sets_bu` BEFORE UPDATE ON `tree_acl_security_sets` FOR EACH ROW BEGIN
	set new.md5 = md5(new.set);
    END */$$


DELIMITER ;

/* Trigger structure for table `tree_info` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `tree_info_bu` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `tree_info_bu` BEFORE UPDATE ON `tree_info` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `users_groups_bi` BEFORE INSERT ON `users_groups` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `users_groups_ai` AFTER INSERT ON `users_groups` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `users_groups_bu` BEFORE UPDATE ON `users_groups` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `users_groups_association_ai` AFTER INSERT ON `users_groups_association` FOR EACH ROW BEGIN
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

/*!50003 CREATE */ /*!50003 TRIGGER `users_groups_association_ad` AFTER DELETE ON `users_groups_association` FOR EACH ROW BEGIN
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

/*!50003 CREATE FUNCTION `f_get_next_autoincrement_id`(in_tablename tinytext) RETURNS int(11)
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	return (SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME = in_tablename);
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_objects_case_id` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_objects_case_id` */;
DELIMITER $$

/*!50003 CREATE FUNCTION `f_get_objects_case_id`(in_id int unsigned) RETURNS int(10) unsigned
    READS SQL DATA
    SQL SECURITY INVOKER
BEGIN
	declare tmp_pid int unsigned;
	DECLARE tmp_type varchar(10);
	DECLARE tmp_path TEXT CHARSET utf8 DEFAULT '';
	SET tmp_path = CONCAT('/', in_id);
	select t.pid, tp.`type` into tmp_pid, tmp_type from tree t left join templates tp on t.template_id = tp.id where t.id = in_id;
	WHILE((tmp_pid IS NOT NULL) AND (COALESCE(tmp_type,'') <> 'case') AND ( INSTR(CONCAT(tmp_path, '/'), CONCAT('/',tmp_pid,'/') ) =0) ) DO
		SET tmp_path = CONCAT('/', tmp_pid, tmp_path);
		SET in_id = tmp_pid;
		-- SELECT pid, `type` INTO tmp_pid, tmp_type FROM tree WHERE id = in_id;
		SELECT t.pid, tp.`type` INTO tmp_pid, tmp_type FROM tree t LEFT JOIN templates tp ON t.template_id = tp.id WHERE t.id = in_id;
	END WHILE;
	IF(COALESCE(tmp_type,'') <> 'case') THEN
		set in_id = null;
	end if;
	return in_id;
    END */$$
DELIMITER ;

/* Function  structure for function  `f_get_security_set_id` */

/*!50003 DROP FUNCTION IF EXISTS `f_get_security_set_id` */;
DELIMITER $$

/*!50003 CREATE FUNCTION `f_get_security_set_id`(in_id bigint unsigned) RETURNS int(10) unsigned
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

/*!50003 CREATE FUNCTION `f_get_tag_pids`(in_id int UNSIGNED) RETURNS varchar(300) CHARSET utf8
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

/*!50003 CREATE FUNCTION `f_get_tree_ids_path`(in_id bigint unsigned) RETURNS text CHARSET utf8
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

/*!50003 CREATE FUNCTION `f_get_tree_inherit_ids`(in_id bigint unsigned) RETURNS text CHARSET utf8
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

/*!50003 CREATE FUNCTION `f_get_tree_path`(in_id bigint unsigned) RETURNS text CHARSET utf8
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

/*!50003 CREATE FUNCTION `f_get_tree_pids`(in_id bigint unsigned) RETURNS varchar(500) CHARSET utf8
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

/*!50003 CREATE FUNCTION `remove_extra_spaces`(
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

/*!50003 CREATE FUNCTION `sfm_adjust_path`(path VARCHAR(500), in_delimiter VARCHAR(50)) RETURNS varchar(500) CHARSET utf8
    READS SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'adds slashes to the begin and end of the path'
BEGIN
	DECLARE tmp_delim_len SMALLINT;
	SET tmp_delim_len = CHAR_LENGTH(in_delimiter);
	IF(path IS NULL) THEN SET path = ''; END IF;
	IF(LEFT (path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(in_delimiter, path); END IF;
	IF(RIGHT(path, tmp_delim_len) <> in_delimiter) THEN SET path = CONCAT(path, in_delimiter); END IF;
	RETURN path;
    END */$$
DELIMITER ;

/* Function  structure for function  `sfm_get_path_element` */

/*!50003 DROP FUNCTION IF EXISTS `sfm_get_path_element` */;
DELIMITER $$

/*!50003 CREATE FUNCTION `sfm_get_path_element`(in_path VARCHAR(500) CHARSET utf8, in_delimiter VARCHAR(50) CHARSET utf8, in_element_index SMALLINT) RETURNS varchar(500) CHARSET utf8
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

/*!50003 CREATE FUNCTION `templates_get_path`(in_id int) RETURNS varchar(300) CHARSET utf8
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

/*!50003 CREATE PROCEDURE `p_add_user`(username varchar(50), pass varchar(100) )
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	insert into users (`name`, `password`) values(username, MD5(CONCAT('aero', pass)));
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_clean_deleted_nodes` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_clean_deleted_nodes` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_clean_deleted_nodes`()
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

/*!50003 CREATE PROCEDURE `p_clear_lost_objects`()
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

/*!50003 CREATE PROCEDURE `p_delete_template_field_with_data`(in_field_id bigint unsigned)
    MODIFIES SQL DATA
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT 'string'
BEGIN
	delete from objects where id = in_field_id;
	DELETE FROM tree WHERE id = in_field_id;
	delete from templates_structure where id = in_field_id;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_delete_tree_node` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_delete_tree_node` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_delete_tree_node`(in_id bigint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	DELETE FROM tree WHERE id = in_id;
	DELETE FROM objects WHERE id = in_id;
	DELETE FROM files WHERE id = in_id;
	DELETE FROM tasks WHERE id = in_id;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_mark_all_childs_as_active` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_mark_all_childs_as_active` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_mark_all_childs_as_active`(in_id bigint unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_achild_ids2(id BIGINT UNSIGNED);
	delete from tmp_achild_ids;
	DELETE FROM tmp_achild_ids2;
	insert into tmp_achild_ids select id from tree where pid = in_id;
	while(ROW_COUNT() > 0)do
		update tree, tmp_achild_ids 
		  set tree.did = NULL
		  ,tree.ddate = NULL
		  ,tree.dstatus = 0 
		  , tree.updated = 1
		where tmp_achild_ids.id = tree.id;
		
		DELETE FROM tmp_achild_ids2;
		insert into tmp_achild_ids2 select id from tmp_achild_ids;
		delete from tmp_achild_ids;
		INSERT INTO tmp_achild_ids SELECT t.id FROM tree t join tmp_achild_ids2 c on t.pid = c.id;
	END WHILE;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_mark_all_childs_as_deleted` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_mark_all_childs_as_deleted` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_mark_all_childs_as_deleted`(in_id bigint unsigned, in_did int unsigned)
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids(id bigint UNSIGNED);
	CREATE TEMPORARY TABLE IF NOT EXISTS tmp_dchild_ids2(id BIGINT UNSIGNED);
	delete from tmp_dchild_ids;
	DELETE FROM tmp_dchild_ids2;
	insert into tmp_dchild_ids select id from tree where pid = in_id;
	while(ROW_COUNT() > 0)do
		update tree, tmp_dchild_ids 
		    set tree.did = in_did
			,tree.ddate = CURRENT_TIMESTAMP
			,tree.dstatus = 2
			,tree.updated = 1
		    where tmp_dchild_ids.id = tree.id;
		    
		DELETE FROM tmp_dchild_ids2;
		insert into tmp_dchild_ids2 select id from tmp_dchild_ids;
		delete from tmp_dchild_ids;
		INSERT INTO tmp_dchild_ids SELECT t.id FROM tree t join tmp_dchild_ids2 c on t.pid = c.id;
	END WHILE;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_mark_all_child_drafts_as_active` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_mark_all_child_drafts_as_active` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_mark_all_child_drafts_as_active`(in_id bigint unsigned)
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
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_recalculate_security_sets` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_recalculate_security_sets` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_recalculate_security_sets`()
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

/*!50003 CREATE PROCEDURE `p_sort_tags`()
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

/*!50003 CREATE PROCEDURE `p_sort_templates`()
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

/*!50003 CREATE PROCEDURE `p_update_child_security_sets`(
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
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_files_content__ref_count` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_files_content__ref_count` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_update_files_content__ref_count`()
    MODIFIES SQL DATA
    SQL SECURITY INVOKER
BEGIN
	UPDATE files_content c SET ref_count = COALESCE((SELECT COUNT(id) FROM files WHERE content_id = c.id), 0)+
	COALESCE((SELECT COUNT(id) FROM files_versions WHERE content_id = c.id), 0);
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_template_structure_levels` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_template_structure_levels` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_update_template_structure_levels`()
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
	
    END */$$
DELIMITER ;

/* Procedure structure for procedure `p_update_tree_acl_count` */

/*!50003 DROP PROCEDURE IF EXISTS  `p_update_tree_acl_count` */;

DELIMITER $$

/*!50003 CREATE PROCEDURE `p_update_tree_acl_count`()
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

/*!50003 CREATE PROCEDURE `p_update_tree_info`()
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

/*!50003 CREATE PROCEDURE `p_update_users_first_and_last_names_from_l1`()
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

/*!50003 CREATE PROCEDURE `p_user_login`(IN `in_username` VARCHAR(50), `in_password` VARCHAR(100), `in_from_ip` VARCHAR(40))
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
