/*
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
  `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','rename','reopen','status_change','overdue','comment','comment_update','move','password_change','permissions','user_delete','user_create','login','login_fail','file_upload','file_update') NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` mediumtext,
  `activity_data_db` mediumtext,
  `activity_data_solr` mediumtext,
  PRIMARY KEY (`id`),
  KEY `FK_action_log__object_id` (`object_id`),
  KEY `FK_action_log__object_pid` (`object_pid`),
  KEY `FK_action_log__user_id` (`user_id`),
  KEY `IDX_action_time` (`action_time`),
  CONSTRAINT `FK_action_log__object_id` FOREIGN KEY (`object_id`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_action_log__object_pid` FOREIGN KEY (`object_pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_action_log__user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `action_log` */

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT NULL,
  `param` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`id`,`pid`,`param`,`value`,`order`) values (104,NULL,'project_name_en','CaseBox - Demo',NULL),(105,NULL,'templateIcons','\r\nicon-arrow-left-medium\r\nicon-arrow-left-medium-green\r\nicon-arrow-left\r\nicon-arrow-right-medium\r\nicon-arrow-right\r\nicon-case_card\r\nicon-complaint\r\nicon-complaint-subjects\r\nicon-info-action\r\nicon-decision\r\nicon-echr_complaint\r\nicon-echr_decision\r\nicon-petition\r\nicon-balloon\r\nicon-bell\r\nicon-blog-blue\r\nicon-blog-magenta\r\nicon-blue-document-small\r\nicon-committee-phase\r\nicon-document-medium\r\nicon-document-stamp\r\nicon-document-text\r\nicon-mail\r\nicon-object1\r\nicon-object2\r\nicon-object3\r\nicon-object4\r\nicon-object5\r\nicon-object6\r\nicon-object7\r\nicon-object8\r\nicon-zone\r\nicon-applicant\r\nicon-suspect\r\nicon-milestone',NULL),(106,NULL,'folder_templates','5,11,100',NULL),(107,NULL,'default_folder_template','5',NULL),(108,NULL,'default_file_template','6',NULL),(109,NULL,'default_task_template','7',NULL),(110,NULL,'default_language','en',NULL),(111,NULL,'languages','en',NULL),(112,NULL,'object_type_plugins','{\r\n  \"object\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"case\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"task\": [\"objectProperties\", \"files\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"file\": [\"thumb\", \"meta\", \"versions\", \"tasks\", \"comments\", \"systemProperties\"]\r\n}',NULL),(113,NULL,'treeNodes','',NULL),(114,113,'Tasks','{\n    \"pid\": 1\n}',1),(115,113,'Dbnode','[]',2),(116,113,'RecycleBin','{\r\n    \"pid\": \"1\",\r\n    \"facets\": [\r\n        \"did\"\r\n    ],\r\n    \"DC\": {\r\n        \"nid\": {}\r\n        ,\"name\": {}\r\n        ,\"cid\": {}\r\n        ,\"ddate\": {\r\n            \"solr_column_name\": \"ddate\"\r\n        }\r\n    }\r\n}',3);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `crons` */

/*Table structure for table `favorites` */

DROP TABLE IF EXISTS `favorites`;

CREATE TABLE `favorites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `node_id` varchar(20) DEFAULT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
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
  `udate` timestamp NULL DEFAULT NULL,
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

/*Table structure for table `guids` */

DROP TABLE IF EXISTS `guids`;

CREATE TABLE `guids` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guids_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `guids` */

insert  into `guids`(`id`,`name`) values (2,'Dbnode'),(3,'RecycleBin'),(1,'Tasks');

/*Table structure for table `notifications` */

DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) unsigned DEFAULT NULL COMMENT 'think to remove it (doubles field from action_log)',
  `action_id` bigint(20) unsigned NOT NULL,
  `action_ids` mediumtext COMMENT 'list of last action ids for same grouped action',
  `action_type` enum('create','update','delete','complete','completion_decline','completion_on_behalf','close','rename','reopen','status_change','overdue','comment','comment_update','move','password_change','permissions','user_delete','user_create','login','login_fail','file_upload','file_update') NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'think to remove it (doubles field from action_log)',
  `prev_action_ids` text COMMENT 'previous action ids(for same obj, action type, user) that have not yet been read',
  `from_user_id` int(11) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'notification has been read in CB',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_notifications` (`object_id`,`action_type`,`from_user_id`,`user_id`),
  KEY `FK_notifications__action_id` (`action_id`),
  KEY `FK_notifications_user_id` (`user_id`),
  KEY `IDX_notifications_seen` (`seen`),
  CONSTRAINT `FK_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_notifications__action_id` FOREIGN KEY (`action_id`) REFERENCES `action_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `notifications` */

/*Table structure for table `objects` */

DROP TABLE IF EXISTS `objects`;

CREATE TABLE `objects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` mediumtext,
  `sys_data` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8;

/*Data for the table `objects` */

insert  into `objects`(`id`,`data`,`sys_data`) values (1,'{\"_title\":\"Tree\",\"en\":\"Tree\"}',NULL),(2,'{\"_title\":\"System\",\"en\":\"System\"}',NULL),(3,'{\"_title\":\"Templates\",\"en\":\"Templates\"}',NULL),(4,'{\"_title\":\"Thesauri\",\"en\":\"Thesauri\"}',NULL),(5,'{\"_title\":\"folder\",\"en\":\"Folder\",\"ru\":\"Folder\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-folder\",\"cfg\":\"{\\\"createMethod\\\":\\\"inline\\\",\\n\\n  \\\"object_plugins\\\":\\n      [\\\"objectProperties\\\",\\n       \\\"comments\\\",\\n       \\\"systemProperties\\\"\\n      ]\\n\\n}\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":2},\"time\":\"2015-09-07T12:33:12Z\"},\"solr\":[]}'),(6,'{\"_title\":\"file_template\",\"en\":\"File\",\"ru\":\"File\",\"type\":\"file\",\"visible\":1,\"iconCls\":\"file-\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":3},\"time\":\"2015-09-07T12:33:13Z\"},\"solr\":[]}'),(7,'{\"_title\":\"task\",\"en\":\"Task\",\"ru\":\"Task\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"icon-task\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":4},\"time\":\"2015-09-07T12:33:13Z\"},\"solr\":[]}'),(8,'{\"_title\":\"Thesauri Item\",\"en\":\"Thesauri Item\",\"ru\":\"Thesauri Item\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-blue-document-small\",\"title_template\":\"{en}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":5},\"time\":\"2015-09-07T12:33:13Z\"},\"solr\":[]}'),(9,'{\"_title\":\"Comment\",\"en\":\"Comment\",\"ru\":\"Коментарий\",\"type\":\"comment\",\"visible\":1,\"iconCls\":\"icon-balloon\",\"cfg\":\"{\\n  \\\"systemType\\\": 2\\n}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":6},\"time\":\"2015-09-07T12:33:13Z\"},\"solr\":[]}'),(10,'{\"_title\":\"User\",\"en\":\"User\",\"type\":\"user\",\"visible\":1,\"iconCls\":\"icon-object4\",\"cfg\":\"{\\\"files\\\":\\\"1\\\",\\\"main_file\\\":\\\"1\\\"}\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":7},\"time\":\"2015-09-07T12:33:14Z\"},\"solr\":[]}'),(11,'{\"_title\":\"Template\",\"en\":\"Template\",\"ru\":\"Template\",\"type\":\"template\",\"visible\":1,\"iconCls\":\"icon-template\",\"cfg\":\"[]\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":18},\"time\":\"2015-09-02T13:46:13Z\"},\"solr\":[]}'),(12,'{\"_title\":\"Field\",\"en\":\"Field\",\"ru\":\"Field\",\"type\":\"field\",\"visible\":1,\"iconCls\":\"icon-snippet\",\"cfg\":\"[]\"}','{\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":19},\"time\":\"2015-09-02T13:46:13Z\"},\"solr\":[]}'),(13,'{\"_title\":\"en\",\"en\":\"Full name (en)\",\"type\":\"varchar\",\"order\":\"1\"}','[]'),(14,'{\"en\":\"Initials\",\"ru\":\"Initiales\",\"_title\":\"initials\",\"type\":\"varchar\",\"order\":\"4\"}','[]'),(15,'{\"en\":\"Sex\",\"ru\":\"Sexe\",\"_title\":\"sex\",\"type\":\"_sex\",\"order\":\"5\",\"cfg\":\"{\\\"thesauriId\\\":\\\"90\\\"}\"}','[]'),(16,'{\"en\":\"Position\",\"ru\":\"Titre\",\"_title\":\"position\",\"type\":\"combo\",\"order\":\"7\",\"cfg\":\"{\\\"thesauriId\\\":\\\"362\\\"}\"}','[]'),(17,'{\"en\":\"E-mail\",\"ru\":\"E-mail\",\"_title\":\"email\",\"type\":\"varchar\",\"order\":\"9\",\"cfg\":\"{\\\"maxInstances\\\":\\\"3\\\"}\"}','[]'),(18,'{\"en\":\"Language\",\"ru\":\"Langue\",\"_title\":\"language_id\",\"type\":\"_language\",\"order\":\"11\"}','[]'),(19,'{\"en\":\"Date format\",\"ru\":\"Format de date\",\"_title\":\"short_date_format\",\"type\":\"_short_date_format\",\"order\":\"12\"}','[]'),(20,'{\"en\":\"Description\",\"ru\":\"Description\",\"_title\":\"description\",\"type\":\"varchar\",\"order\":\"13\"}','[]'),(21,'{\"en\":\"Room\",\"ru\":\"Salle\",\"_title\":\"room\",\"type\":\"varchar\",\"order\":\"8\"}','[]'),(22,'{\"en\":\"Phone\",\"ru\":\"Téléphone\",\"_title\":\"phone\",\"type\":\"varchar\",\"order\":\"10\",\"cfg\":\"{\\\"maxInstances\\\":\\\"10\\\"}\"}','[]'),(23,'{\"en\":\"Location\",\"ru\":\"Emplacement\",\"_title\":\"location\",\"type\":\"combo\",\"order\":\"6\",\"cfg\":\"{\\\"thesauriId\\\":\\\"394\\\"}\"}','[]'),(24,'{\"en\":\"Program\",\"ru\":\"Program\",\"_title\":\"program\",\"type\":\"_objects\",\"order\":\"1\",\"cfg\":\"{\\r\\n\\\"source\\\":\\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"715\\\"\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"category_id\"}','[]'),(25,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(26,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_fieldTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(27,'{\"en\":\"Order\",\"ru\":\"Order\",\"_title\":\"order\",\"type\":\"int\",\"order\":\"6\",\"cfg\":\"[]\"}','[]'),(28,'{\"_title\":\"cfg\",\"en\":\"Config\",\"ru\":\"Config\",\"type\":\"memo\",\"order\":\"7\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(29,'{\"en\":\"Solr column name\",\"ru\":\"Solr column name\",\"_title\":\"solr_column_name\",\"type\":\"varchar\",\"order\":\"8\",\"cfg\":\"[]\"}','[]'),(30,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(31,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\",\\\"rea-dOnly\\\":true}\"}','[]'),(32,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),(33,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"6\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),(34,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"7\",\"cfg\":\"[]\"}','[]'),(35,'{\"en\":\"Config\",\"ru\":\"Config\",\"_title\":\"cfg\",\"type\":\"text\",\"order\":\"8\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),(36,'{\"en\":\"Title template\",\"ru\":\"Title template\",\"_title\":\"title_template\",\"type\":\"text\",\"order\":\"9\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(37,'{\"en\":\"Info template\",\"ru\":\"Info template\",\"_title\":\"info_template\",\"type\":\"text\",\"order\":\"10\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),(38,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),(39,'{\"_title\":\"iconCls\",\"en\":\"Icon class\",\"type\":\"iconcombo\",\"order\":5}','{\"solr\":[]}'),(40,'{\"_title\":\"visible\",\"en\":\"Visible\",\"type\":\"checkbox\",\"order\":6}','{\"solr\":[]}'),(41,'{\"_title\":\"order\",\"en\":\"Order\",\"type\":\"int\",\"order\":7,\"cfg\":\"{\\n\\\"indexed\\\": true\\n}\",\"solr_column_name\":\"order\"}','{\"solr\":[]}'),(42,'{\"_title\":\"en\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":0,\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','{\"solr\":[]}'),(43,'{\"_title\":\"ru\",\"type\":\"varchar\",\"order\":1,\"cfg\":{\"showIn\":\"top\"}}','[]'),(44,'{\"_title\":\"_title\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\n\\\"required\\\": true\\n,\\\"hidePreview\\\": true\\n}\"}','[]'),(45,'{\"_title\":\"assigned\",\"en\":\"Assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n  \\\"editor\\\": \\\"form\\\"\\n  ,\\\"source\\\": \\\"users\\\"\\n  ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n  ,\\\"autoLoad\\\": true\\n  ,\\\"multiValued\\\": true\\n  ,\\\"hidePreview\\\": true\\n}\"}','[]'),(46,'{\"_title\":\"importance\",\"en\":\"Importance\",\"type\":\"_objects\",\"order\":8,\"cfg\":\"{\\n  \\\"scope\\\": 53,\\n  \\\"value\\\": 54,\\n  \\\"faceting\\\": true\\n}\"}','[]'),(47,'{\"_title\":\"description\",\"en\":\"Description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n  \\\"height\\\": 100\\n  ,\\\"noHeader\\\": true\\n  ,\\\"hidePreview\\\": true\\n  ,\\\"linkRenderer\\\": \\\"user,object,url\\\"\\n}\"}','[]'),(48,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Название\",\"type\":\"varchar\",\"order\":1}','[]'),(49,'{\"_title\":\"_title\",\"en\":\"Text\",\"ru\":\"Текст\",\"type\":\"memo\",\"order\":0,\"cfg\":\"{\\n\\\"height\\\": 100\\n}\",\"solr_column_name\":\"content\"}','[]'),(50,'{\"_title\":\"due_date\",\"en\":\"Due date\",\"type\":\"date\",\"order\":5,\"cfg\":\"{\\n\\\"hidePreview\\\": true\\n}\"}','[]'),(51,'{\"_title\":\"due_time\",\"en\":\"Due time\",\"type\":\"time\",\"order\":6,\"cfg\":\"{\\n\\\"hidePreview\\\": true\\n}\"}','[]'),(52,'{\"_title\":\"task\"}','[]'),(53,'{\"_title\":\"Importance\"}','[]'),(54,'{\"en\":\"Low\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":1}','[]'),(55,'{\"en\":\"Medium\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":2}','[]'),(56,'{\"en\":\"High\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":3}','[]'),(57,'{\"en\":\"CRITICAL\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":4}','[]'),(58,'{\"_title\":\"shortcut\",\"en\":\"Shortcut\",\"type\":\"shortcut\",\"visible\":1,\"iconCls\":\"i-shortcut\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":8},\"time\":\"2015-09-07T12:33:14Z\"}}'),(59,'{\"_title\":\"Menu\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":1},\"time\":\"2015-09-07T12:32:02Z\"}}'),(60,'{\"_title\":\"Menus\"}','{\"fu\":[],\"solr\":[]}'),(61,'{\"_title\":\"- Menu separator -\",\"en\":\"- Menu separator -\",\"type\":\"object\",\"visible\":1}','{\"fu\":[1],\"solr\":[]}'),(62,'{\"_title\":\"Menu rule\",\"en\":\"Menu rule\",\"type\":\"menu\",\"visible\":1}','{\"fu\":[1],\"solr\":[]}'),(63,'{\"name\":\"_title\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),(64,'{\"name\":\"node_ids\",\"en\":\"Nodes\",\"type\":\"_objects\",\"order\":2,\"cfg\":\"{\\\"multiValued\\\":true,\\\"editor\\\":\\\"form\\\",\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),(65,'{\"name\":\"template_ids\",\"en\":\"Templates\",\"type\":\"_objects\",\"order\":3,\"cfg\":\"{\\\"templates\\\":\\\"11\\\",\\\"editor\\\":\\\"form\\\",\\\"multiValued\\\":true,\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),(66,'{\"name\":\"user_group_ids\",\"en\":\"Users\\/Groups\",\"type\":\"_objects\",\"order\":4,\"cfg\":\"{\\\"source\\\":\\\"usersgroups\\\",\\\"multiValued\\\":true}\"}','{\"fu\":[1],\"solr\":[]}'),(67,'{\"name\":\"menu\",\"en\":\"Menu\",\"type\":\"_objects\",\"order\":5,\"cfg\":\"{\\\"templates\\\":\\\"11\\\",\\\"multiValued\\\":true,\\\"editor\\\":\\\"form\\\",\\\"allowValueSort\\\":true,\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),(68,'{\"_title\":\"Global Menu\",\"menu\":\"7,83,61,5\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":15},\"time\":\"2015-09-01T07:28:14Z\"}}'),(69,'{\"_title\":\"System Templates\",\"node_ids\":\"3\",\"template_ids\":null,\"user_group_ids\":null,\"menu\":\"11,5\"}','{\"fu\":[1],\"solr\":[]}'),(70,'{\"_title\":\"System Templates SubMenu\",\"node_ids\":null,\"template_ids\":\"11\",\"user_group_ids\":null,\"menu\":\"12\"}','{\"fu\":[1],\"solr\":[]}'),(71,'{\"_title\":\"System Fields\",\"node_ids\":null,\"template_ids\":\"12\",\"user_group_ids\":null,\"menu\":\"12\"}','{\"fu\":[1],\"solr\":[]}'),(72,'{\"_title\":\"System Thesauri\",\"node_ids\":\"4\",\"template_ids\":\"5\",\"user_group_ids\":null,\"menu\":\"8,61,5\"}','{\"fu\":[1],\"solr\":[]}'),(73,'{\"_title\":\"Create menu rules in this folder\",\"node_ids\":60,\"menu\":62}','{\"fu\":[1],\"solr\":[]}'),(74,'{\"_title\":\"link\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:15:55Z\",\"users\":{\"1\":1}}}'),(75,'{\"_title\":\"Type\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:16:08Z\",\"users\":{\"1\":2}}}'),(76,'{\"en\":\"Article\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":1}','{\"fu\":[1],\"solr\":{\"order\":1},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:17:46Z\",\"users\":{\"1\":3}}}'),(77,'{\"en\":\"Document\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":2}','{\"fu\":[1],\"solr\":{\"order\":2},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:06Z\",\"users\":{\"1\":4}}}'),(78,'{\"en\":\"Image\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":3}','{\"fu\":[1],\"solr\":{\"order\":3},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:24Z\",\"users\":{\"1\":5}}}'),(79,'{\"en\":\"Sound\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":4}','{\"fu\":[1],\"solr\":{\"order\":4},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:42Z\",\"users\":{\"1\":6}}}'),(80,'{\"en\":\"Video\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":5}','{\"fu\":[1],\"solr\":{\"order\":5},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:03Z\",\"users\":{\"1\":7}}}'),(81,'{\"en\":\"Website\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":6}','{\"fu\":[1],\"solr\":{\"order\":6},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:25Z\",\"users\":{\"1\":8}}}'),(82,'{\"_title\":\"Tags\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:43Z\",\"users\":{\"1\":9}}}'),(83,'{\"_title\":\"link\",\"en\":\"Link\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"icon-shortcut\",\"title_template\":\"{url}\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":9},\"time\":\"2015-09-07T12:33:14Z\"}}'),(84,'{\"_title\":\"type\",\"en\":\"Type\",\"type\":\"_objects\",\"order\":1,\"cfg\":\"{\\n\\\"scope\\\": 75 \\n}\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:25:21Z\",\"users\":{\"1\":11}}}'),(85,'{\"_title\":\"url\",\"en\":\"URL\",\"type\":\"varchar\",\"order\":2}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:25:59Z\",\"users\":{\"1\":12}}}'),(86,'{\"_title\":\"description\",\"en\":\"Description\",\"type\":\"varchar\",\"order\":3}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:26:29Z\",\"users\":{\"1\":13}}}'),(87,'{\"_title\":\"tags\",\"en\":\"Tags\",\"type\":\"_objects\",\"order\":4,\"cfg\":\"{\\n\\\"scope\\\": 82\\n,\\\"editor\\\": \\\"tagField\\\"\\n}\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":16},\"time\":\"2015-09-01T07:30:36Z\"}}'),(88,'{\"_title\":\"Built-in\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-02T13:45:53Z\",\"users\":{\"1\":17}}}'),(89,'{\"_title\":\"Config\"}','{\"fu\":[],\"solr\":[]}'),(90,'{\"_title\":\"Config\"}','{\"fu\":[],\"solr\":[]}'),(91,'{\"_title\":\"Config int option\",\"en\":\"Config int option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"icon-element\"}','{\"fu\":[1],\"solr\":[]}'),(92,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),(93,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"int\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),(94,'{\"_title\":\"Config varchar option\",\"en\":\"Config varchar option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"icon-element\"}','{\"fu\":[1],\"solr\":[]}'),(95,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),(96,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"varchar\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),(97,'{\"_title\":\"Config text option\",\"en\":\"Config text option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"icon-element\"}','{\"fu\":[1],\"solr\":[]}'),(98,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),(99,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"text\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),(100,'{\"_title\":\"Config json option\",\"en\":\"Config json option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"icon-element\"}','{\"fu\":[1],\"solr\":[]}'),(101,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),(102,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"text\",\"order\":2,\"cfg\":\"{\\\"editor\\\":\\\"ace\\\",\\\"format\\\":\\\"json\\\",\\\"validator\\\":\\\"json\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),(103,'{\"name\":\"order\",\"en\":\"Order\",\"type\":\"int\",\"order\":3,\"solr_column_name\":\"order\",\"cfg\":\"{\\\"indexed\\\":true}\"}','{\"fu\":[1],\"solr\":[]}'),(104,'{\"_title\":\"project_name_en\",\"value\":\"CaseBox - Demo\"}','{\"fu\":[1],\"solr\":[]}'),(105,'{\"_title\":\"templateIcons\",\"value\":\"\\r\\nicon-arrow-left-medium\\r\\nicon-arrow-left-medium-green\\r\\nicon-arrow-left\\r\\nicon-arrow-right-medium\\r\\nicon-arrow-right\\r\\nicon-case_card\\r\\nicon-complaint\\r\\nicon-complaint-subjects\\r\\nicon-info-action\\r\\nicon-decision\\r\\nicon-echr_complaint\\r\\nicon-echr_decision\\r\\nicon-petition\\r\\nicon-balloon\\r\\nicon-bell\\r\\nicon-blog-blue\\r\\nicon-blog-magenta\\r\\nicon-blue-document-small\\r\\nicon-committee-phase\\r\\nicon-document-medium\\r\\nicon-document-stamp\\r\\nicon-document-text\\r\\nicon-mail\\r\\nicon-object1\\r\\nicon-object2\\r\\nicon-object3\\r\\nicon-object4\\r\\nicon-object5\\r\\nicon-object6\\r\\nicon-object7\\r\\nicon-object8\\r\\nicon-zone\\r\\nicon-applicant\\r\\nicon-suspect\\r\\nicon-milestone\"}','{\"fu\":[1],\"solr\":[]}'),(106,'{\"_title\":\"folder_templates\",\"value\":\"5,11,100\"}','{\"fu\":[1],\"solr\":[]}'),(107,'{\"_title\":\"default_folder_template\",\"value\":\"5\"}','{\"fu\":[1],\"solr\":[]}'),(108,'{\"_title\":\"default_file_template\",\"value\":\"6\"}','{\"fu\":[1],\"solr\":[]}'),(109,'{\"_title\":\"default_task_template\",\"value\":\"7\"}','{\"fu\":[1],\"solr\":[]}'),(110,'{\"_title\":\"default_language\",\"value\":\"en\"}','{\"fu\":[1],\"solr\":[]}'),(111,'{\"_title\":\"languages\",\"value\":\"en\"}','{\"fu\":[1],\"solr\":[]}'),(112,'{\"_title\":\"object_type_plugins\",\"value\":\"{\\r\\n  \\\"object\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"tasks\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"case\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"tasks\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"task\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"file\\\": [\\\"thumb\\\", \\\"meta\\\", \\\"versions\\\", \\\"tasks\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n}\"}','{\"fu\":[1],\"solr\":[]}'),(113,'{\"_title\":\"treeNodes\",\"value\":\"\"}','{\"fu\":[1],\"solr\":[]}'),(114,'{\"_title\":\"Tasks\",\"value\":\"{\\n    \\\"pid\\\": 1\\n}\",\"order\":1}','{\"fu\":[1],\"solr\":{\"order\":1}}'),(115,'{\"_title\":\"Dbnode\",\"value\":\"[]\",\"order\":2}','{\"fu\":[1],\"solr\":{\"order\":2}}'),(116,'{\"_title\":\"RecycleBin\",\"value\":\"{\\r\\n    \\\"pid\\\": \\\"1\\\",\\r\\n    \\\"facets\\\": [\\r\\n        \\\"did\\\"\\r\\n    ],\\r\\n    \\\"DC\\\": {\\r\\n        \\\"nid\\\": {}\\r\\n        ,\\\"name\\\": {}\\r\\n        ,\\\"cid\\\": {}\\r\\n        ,\\\"ddate\\\": {\\r\\n            \\\"solr_column_name\\\": \\\"ddate\\\"\\r\\n        }\\r\\n    }\\r\\n}\",\"order\":3}','{\"fu\":[1],\"solr\":{\"order\":3},\"wu\":[1],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":1},\"time\":\"2015-11-25T13:52:47Z\"}}'),(117,'{\"_title\":\"Create config options rule\",\"node_ids\":90,\"menu\":\"91,94,97,100\"}','{\"fu\":[1],\"solr\":[]}');

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varbinary(100) NOT NULL,
  `pid` varbinary(100) DEFAULT NULL COMMENT 'parrent session id',
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

/*Table structure for table `templates` */

DROP TABLE IF EXISTS `templates`;

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `is_folder` tinyint(1) unsigned DEFAULT '0',
  `type` enum('case','object','file','task','user','email','template','field','search','comment','shortcut','menu','config') DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;

/*Data for the table `templates` */

insert  into `templates`(`id`,`pid`,`is_folder`,`type`,`name`,`l1`,`l2`,`l3`,`l4`,`order`,`visible`,`iconCls`,`default_field`,`cfg`,`title_template`,`info_template`) values (5,3,0,'object','folder','Folder','Folder','Folder','Folder',5,1,'icon-folder',NULL,'{\"createMethod\":\"inline\",\n\n  \"object_plugins\":\n      [\"objectProperties\",\n       \"comments\",\n       \"systemProperties\"\n      ]\n\n}','{name}',NULL),(6,3,0,'file','file_template','File','File','File','File',6,1,'file-',NULL,'[]','{name}',NULL),(7,3,0,'task','task','Task','Task','Task','Task',3,1,'icon-task',NULL,NULL,'{name}',NULL),(8,3,0,'object','Thesauri Item','Thesauri item','Thesauri item','Thesauri item','Thesauri item',0,1,'icon-blue-document-small',NULL,NULL,'{en}',NULL),(9,3,0,'comment','Comment',NULL,NULL,NULL,NULL,0,1,'icon-balloon',NULL,'{\n  \"systemType\": 2\n}',NULL,NULL),(10,3,0,'user','User','User',NULL,'Пользователь',NULL,1,1,'icon-object4',NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',NULL,NULL),(11,3,0,'template','Template','Template','Template','Template','Template',0,1,'icon-template',NULL,'[]',NULL,NULL),(12,3,0,'field','Field','Field','Field','Field','Field',0,1,'icon-snippet',NULL,'[]',NULL,NULL),(58,3,0,'shortcut','shortcut','Shortcut',NULL,NULL,NULL,0,0,'i-shortcut',NULL,NULL,NULL,NULL),(61,59,0,'object','- Menu separator -','- Menu separator -',NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL),(62,59,0,'menu','Menu rule','Menu rule',NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL),(83,3,0,'object','link','Link',NULL,NULL,NULL,0,0,'icon-shortcut',NULL,NULL,'{url}',NULL),(91,89,0,'config','Config int option','Config int option',NULL,NULL,NULL,0,0,'icon-element',NULL,NULL,NULL,NULL),(94,89,0,'config','Config varchar option','Config varchar option',NULL,NULL,NULL,0,0,'icon-element',NULL,NULL,NULL,NULL),(97,89,0,'config','Config text option','Config text option',NULL,NULL,NULL,0,0,'icon-element',NULL,NULL,NULL,NULL),(100,89,0,'config','Config json option','Config json option',NULL,NULL,NULL,0,0,'icon-element',NULL,NULL,NULL,NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8;

/*Data for the table `templates_structure` */

insert  into `templates_structure`(`id`,`pid`,`template_id`,`tag`,`level`,`name`,`l1`,`l2`,`l3`,`l4`,`type`,`order`,`cfg`,`solr_column_name`) values (13,10,10,'f',0,'en','Full name (en)',NULL,NULL,NULL,'varchar',1,NULL,NULL),(14,10,10,'f',0,'initials','Initials','Initiales','Инициалы',NULL,'varchar',4,'[]',NULL),(15,10,10,'f',0,'sex','Sex','Sexe','Пол',NULL,'_sex',5,'{\"thesauriId\":\"90\"}',NULL),(16,10,10,'f',0,'position','Position','Titre','Должность',NULL,'_objects',7,'{\"source\":\"tree\",\"scope\":24340,\"oldThesauriId\":\"362\"}',NULL),(17,10,10,'f',0,'email','E-mail','E-mail','E-mail',NULL,'varchar',9,'{\"maxInstances\":\"3\"}',NULL),(18,10,10,'f',0,'language_id','Language','Langue','Язык',NULL,'_language',11,'[]',NULL),(19,10,10,'f',0,'short_date_format','Date format','Format de date','Формат даты',NULL,'_short_date_format',12,'[]',NULL),(20,10,10,'f',0,'description','Description','Description','Примечание',NULL,'varchar',13,'[]',NULL),(21,10,10,'f',0,'room','Room','Salle','Кабинет',NULL,'varchar',8,'[]',NULL),(22,10,10,'f',0,'phone','Phone','Téléphone','Телефон',NULL,'varchar',10,'{\"maxInstances\":\"10\"}',NULL),(23,10,10,'f',0,'location','Location','Emplacement','Расположение',NULL,'_objects',6,'{\"source\":\"tree\",\"scope\":24373,\"oldThesauriId\":\"394\"}',NULL),(24,6,6,'f',0,'program','Program','Program','Program','Program','_objects',1,'{\"source\":\"tree\",\"multiValued\":true,\"autoLoad\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"faceting\":true,\"scope\":24265,\"oldThesauriId\":\"715\"}',NULL),(25,12,12,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\"}',NULL),(26,12,12,NULL,0,'type','Type','Type','Type','Type','_fieldTypesCombo',5,'[]',NULL),(27,12,12,NULL,0,'order','Order','Order','Order','Order','int',6,'[]',NULL),(28,12,12,NULL,0,'cfg','Config','Config','Config','Config','memo',7,'{\"height\":100}',NULL),(29,12,12,NULL,0,'solr_column_name','Solr column name','Solr column name','Solr column name','Solr column name','varchar',8,'[]',NULL),(30,12,12,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(31,11,11,NULL,0,'_title','Name','Name','Name','Name','varchar',NULL,'{\"showIn\":\"top\",\"rea-dOnly\":true}',NULL),(32,11,11,NULL,0,'type','Type','Type','Type','Type','_templateTypesCombo',5,'[]',NULL),(33,11,11,NULL,0,'visible','Active','Active','Active','Active','checkbox',6,'{\"showIn\":\"top\"}',NULL),(34,11,11,NULL,0,'iconCls','Icon class','Icon class','Icon class','Icon class','iconcombo',7,'[]',NULL),(35,11,11,NULL,0,'cfg','Config','Config','Config','Config','text',8,'{\"height\":100}',NULL),(36,11,11,NULL,0,'title_template','Title template','Title template','Title template','Title template','text',9,'{\"height\":50}',NULL),(37,11,11,NULL,0,'info_template','Info template','Info template','Info template','Info template','text',10,'{\"height\":50}',NULL),(38,11,11,NULL,0,'en','Title (en)','Title (en)','Title (en)','Title (en)','varchar',1,'[]',NULL),(39,8,8,NULL,0,'iconCls','Icon class',NULL,NULL,NULL,'iconcombo',5,NULL,NULL),(40,8,8,NULL,0,'visible','Visible',NULL,NULL,NULL,'checkbox',6,NULL,NULL),(41,8,8,NULL,0,'order','Order',NULL,NULL,NULL,'int',7,'{\n\"indexed\": true\n}','order'),(42,8,8,NULL,0,'en','Title',NULL,NULL,NULL,'varchar',0,'{\"showIn\":\"top\"}',NULL),(43,8,8,NULL,0,'ru','Title (ru)','Title (ru)','Title (ru)','Title (ru)','varchar',1,'{\"showIn\":\"top\"}',NULL),(44,7,7,NULL,0,'_title','Title',NULL,NULL,NULL,'varchar',1,'{\n\"required\": true\n,\"hidePreview\": true\n}',NULL),(45,7,7,NULL,0,'assigned','Assigned',NULL,NULL,NULL,'_objects',7,'{\n  \"editor\": \"form\"\n  ,\"source\": \"users\"\n  ,\"renderer\": \"listObjIcons\"\n  ,\"autoLoad\": true\n  ,\"multiValued\": true\n  ,\"hidePreview\": true\n}',NULL),(46,7,7,NULL,0,'importance','Importance',NULL,NULL,NULL,'_objects',8,'{\n  \"scope\": 53,\n  \"value\": 54,\n  \"faceting\": true\n}',NULL),(47,7,7,NULL,0,'description','Description',NULL,NULL,NULL,'memo',10,'{\n  \"height\": 100\n  ,\"noHeader\": true\n  ,\"hidePreview\": true\n  ,\"linkRenderer\": \"user,object,url\"\n}',NULL),(48,5,5,NULL,0,'_title','Name','Название',NULL,NULL,'varchar',1,NULL,NULL),(49,9,9,NULL,0,'_title','Text','Текст',NULL,NULL,'memo',0,'{\n\"height\": 100\n}','content'),(50,7,7,NULL,0,'due_date','Due date',NULL,NULL,NULL,'date',5,'{\n\"hidePreview\": true\n}',NULL),(51,7,7,NULL,0,'due_time','Due time',NULL,NULL,NULL,'time',6,'{\n\"hidePreview\": true\n}',NULL),(63,62,62,NULL,0,'_title','Title',NULL,NULL,NULL,'varchar',1,NULL,NULL),(64,62,62,NULL,0,'node_ids','Nodes',NULL,NULL,NULL,'_objects',2,'{\"multiValued\":true,\"editor\":\"form\",\"renderer\":\"listObjIcons\"}',NULL),(65,62,62,NULL,0,'template_ids','Templates',NULL,NULL,NULL,'_objects',3,'{\"templates\":\"11\",\"editor\":\"form\",\"multiValued\":true,\"renderer\":\"listObjIcons\"}',NULL),(66,62,62,NULL,0,'user_group_ids','Users/Groups',NULL,NULL,NULL,'_objects',4,'{\"source\":\"usersgroups\",\"multiValued\":true}',NULL),(67,62,62,NULL,0,'menu','Menu',NULL,NULL,NULL,'_objects',5,'{\"templates\":\"11\",\"multiValued\":true,\"editor\":\"form\",\"allowValueSort\":true,\"renderer\":\"listObjIcons\"}',NULL),(84,83,83,NULL,0,'type','Type',NULL,NULL,NULL,'_objects',1,'{\n\"scope\": 75 \n}',NULL),(85,83,83,NULL,0,'url','URL',NULL,NULL,NULL,'varchar',2,NULL,NULL),(86,83,83,NULL,0,'description','Description',NULL,NULL,NULL,'varchar',3,NULL,NULL),(87,83,83,NULL,0,'tags','Tags',NULL,NULL,NULL,'_objects',4,'{\n\"scope\": 82\n,\"editor\": \"tagField\"\n}',NULL),(92,91,91,NULL,0,'_title','Name',NULL,NULL,NULL,'varchar',1,NULL,NULL),(93,91,91,NULL,0,'value','Value',NULL,NULL,NULL,'int',2,NULL,NULL),(95,94,94,NULL,0,'_title','Name',NULL,NULL,NULL,'varchar',1,NULL,NULL),(96,94,94,NULL,0,'value','Value',NULL,NULL,NULL,'varchar',2,NULL,NULL),(98,97,97,NULL,0,'_title','Name',NULL,NULL,NULL,'varchar',1,NULL,NULL),(99,97,97,NULL,0,'value','Value',NULL,NULL,NULL,'text',2,NULL,NULL),(101,100,100,NULL,0,'_title','Name',NULL,NULL,NULL,'varchar',1,NULL,NULL),(102,100,100,NULL,0,'value','Value',NULL,NULL,NULL,'text',2,'{\"editor\":\"ace\",\"format\":\"json\",\"validator\":\"json\"}',NULL),(103,100,100,NULL,0,'order','Order',NULL,NULL,NULL,'int',3,'{\"indexed\":true}','order');

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
  `info` varchar(1000) DEFAULT NULL COMMENT 'Where in CB the term is used, what it means',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - not deleted, 1 - deleted',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_translations__name` (`name`),
  KEY `FK_translations__pid` (`pid`),
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
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8;

/*Data for the table `tree` */

insert  into `tree`(`id`,`pid`,`user_id`,`system`,`type`,`draft`,`draft_pid`,`template_id`,`tag_id`,`target_id`,`name`,`date`,`date_end`,`size`,`is_main`,`cfg`,`inherit_acl`,`cid`,`cdate`,`uid`,`udate`,`updated`,`oid`,`did`,`ddate`,`dstatus`) values (1,NULL,NULL,1,1,0,NULL,5,NULL,NULL,'Tree',NULL,NULL,NULL,1,'[]',0,1,'2012-11-17 17:10:21',1,'2014-01-17 13:53:00',0,1,NULL,NULL,0),(2,1,NULL,0,1,0,NULL,5,NULL,NULL,'System',NULL,NULL,NULL,NULL,'[]',0,1,'2015-05-20 15:57:45',NULL,NULL,0,1,NULL,NULL,0),(3,2,NULL,0,NULL,0,NULL,5,NULL,NULL,'Templates',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),(4,2,NULL,0,4,0,NULL,5,NULL,NULL,'Thesauri','2013-09-24 19:38:09',NULL,NULL,NULL,'[]',1,256,'2013-09-24 19:38:09',1,'2014-01-17 13:53:08',0,256,NULL,NULL,0),(5,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'folder',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',240,'2014-02-28 20:01:55',0,1,NULL,NULL,0),(6,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'file',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(7,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'task',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',1,'2015-05-21 06:58:35',0,1,NULL,NULL,0),(8,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'Thesauri Item',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',240,'2014-02-28 14:12:11',0,1,NULL,NULL,0),(9,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'Comment',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:14:04',NULL,NULL,0,1,NULL,NULL,0),(10,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'User',NULL,NULL,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',1,1,'2014-01-17 13:50:48',1,'2014-01-17 14:09:12',0,1,NULL,NULL,0),(11,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'Template',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(12,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'Field',NULL,NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:50:51',0,1,NULL,NULL,0),(13,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',1,'2015-05-21 06:36:59',0,1,NULL,NULL,0),(14,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'initials',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(15,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'sex',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(16,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'position',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(17,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'email',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(18,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'language_id',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(19,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'short_date_format',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(20,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(21,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'room',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(22,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'phone',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(23,10,NULL,0,NULL,0,NULL,12,NULL,NULL,'location',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,NULL,0,1,NULL,NULL,0),(24,6,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),(25,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-01-21 11:24:06',0,1,NULL,NULL,0),(26,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(27,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(28,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-28 16:12:37',0,1,NULL,NULL,0),(29,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'solr_column_name',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(30,12,NULL,0,NULL,0,NULL,12,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(31,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-12 21:12:31',0,1,NULL,NULL,0),(32,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(33,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(34,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(35,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'cfg',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(36,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'title_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(37,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'info_template',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(38,11,NULL,0,NULL,0,NULL,12,NULL,NULL,'en',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),(39,8,NULL,0,NULL,0,NULL,12,NULL,NULL,'iconCls',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:08',0,1,NULL,NULL,0),(40,8,NULL,0,NULL,0,NULL,12,NULL,NULL,'visible',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:42',0,1,NULL,NULL,0),(41,8,NULL,0,NULL,0,NULL,12,NULL,NULL,'order',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:57',0,1,NULL,NULL,0),(42,8,NULL,0,NULL,0,NULL,12,NULL,NULL,'en',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:04:56',0,1,NULL,NULL,0),(43,8,NULL,0,NULL,0,NULL,12,NULL,NULL,'ru',NULL,NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',NULL,NULL,0,1,1,'2015-05-21 12:20:51',1),(44,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 09:34:21',0,1,NULL,NULL,0),(45,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'assigned',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 10:32:02',0,1,NULL,NULL,0),(46,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'importance',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 12:26:19',0,1,NULL,NULL,0),(47,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 10:32:34',0,1,NULL,NULL,0),(48,5,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-01-22 14:10:27',NULL,NULL,0,1,NULL,NULL,0),(49,9,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,'null',1,1,'2014-02-12 21:15:03',NULL,NULL,0,1,NULL,NULL,0),(50,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'due_date',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 10:30:34',NULL,NULL,0,1,NULL,NULL,0),(51,7,NULL,0,NULL,0,NULL,12,NULL,NULL,'due_time',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 10:31:04',NULL,NULL,0,1,NULL,NULL,0),(52,4,NULL,0,NULL,0,NULL,5,NULL,NULL,'task',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:09:09',NULL,NULL,0,1,NULL,NULL,0),(53,52,NULL,0,NULL,0,NULL,5,NULL,NULL,'Importance',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:09:33',NULL,NULL,0,1,NULL,NULL,0),(54,53,NULL,0,NULL,0,NULL,8,NULL,NULL,'Low',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:23:09',NULL,NULL,0,1,NULL,NULL,0),(55,53,NULL,0,NULL,0,NULL,8,NULL,NULL,'Medium',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:24:01',NULL,NULL,0,1,NULL,NULL,0),(56,53,NULL,0,NULL,0,NULL,8,NULL,NULL,'High',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:24:41',NULL,NULL,0,1,NULL,NULL,0),(57,53,NULL,0,NULL,0,NULL,8,NULL,NULL,'CRITICAL',NULL,NULL,NULL,NULL,'null',1,1,'2015-05-21 12:25:12',NULL,NULL,0,1,NULL,NULL,0),(58,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'shortcut',NULL,NULL,NULL,NULL,NULL,1,1,'2015-06-06 21:50:18',NULL,NULL,0,1,NULL,NULL,0),(59,88,NULL,0,NULL,0,NULL,5,NULL,NULL,'Menu',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(60,2,NULL,0,NULL,0,NULL,5,NULL,NULL,'Menus',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(61,59,NULL,0,NULL,0,NULL,11,NULL,NULL,'- Menu separator -',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(62,59,NULL,0,NULL,0,NULL,11,NULL,NULL,'Menu rule',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(63,62,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(64,62,NULL,0,NULL,0,NULL,12,NULL,NULL,'node_ids',NULL,NULL,NULL,NULL,'{\"multiValued\":true,\"editor\":\"form\",\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(65,62,NULL,0,NULL,0,NULL,12,NULL,NULL,'template_ids',NULL,NULL,NULL,NULL,'{\"templates\":\"11\",\"editor\":\"form\",\"multiValued\":true,\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(66,62,NULL,0,NULL,0,NULL,12,NULL,NULL,'user_group_ids',NULL,NULL,NULL,NULL,'{\"source\":\"usersgroups\",\"multiValued\":true}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(67,62,NULL,0,NULL,0,NULL,12,NULL,NULL,'menu',NULL,NULL,NULL,NULL,'{\"templates\":\"11\",\"multiValued\":true,\"editor\":\"form\",\"allowValueSort\":true,\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(68,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'Global Menu',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',1,'2015-09-01 07:28:13',0,1,NULL,NULL,0),(69,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'System Templates',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(70,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'System Templates SubMenu',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(71,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'System Fields',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(72,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'System Thesauri',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(73,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'Create menu rules in this folder',NULL,NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),(74,4,NULL,0,NULL,0,NULL,5,NULL,NULL,'link',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:15:55',NULL,NULL,0,1,NULL,NULL,0),(75,74,NULL,0,NULL,0,NULL,5,NULL,NULL,'Type',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:16:07',NULL,NULL,0,1,NULL,NULL,0),(76,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Article',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:17:46',NULL,NULL,0,1,NULL,NULL,0),(77,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Document',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:06',NULL,NULL,0,1,NULL,NULL,0),(78,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Image',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:24',NULL,NULL,0,1,NULL,NULL,0),(79,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Sound',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:42',NULL,NULL,0,1,NULL,NULL,0),(80,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Video',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:03',NULL,NULL,0,1,NULL,NULL,0),(81,75,NULL,0,NULL,0,NULL,8,NULL,NULL,'Website',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:25',NULL,NULL,0,1,NULL,NULL,0),(82,74,NULL,0,NULL,0,NULL,5,NULL,NULL,'Tags',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:42',NULL,NULL,0,1,NULL,NULL,0),(83,88,NULL,0,NULL,0,NULL,11,NULL,NULL,'link',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:23:21',NULL,NULL,0,1,NULL,NULL,0),(84,83,NULL,0,NULL,0,NULL,12,NULL,NULL,'type',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:25:21',NULL,NULL,0,1,NULL,NULL,0),(85,83,NULL,0,NULL,0,NULL,12,NULL,NULL,'url',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:25:58',NULL,NULL,0,1,NULL,NULL,0),(86,83,NULL,0,NULL,0,NULL,12,NULL,NULL,'description',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:26:29',NULL,NULL,0,1,NULL,NULL,0),(87,83,NULL,0,NULL,0,NULL,12,NULL,NULL,'tags',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:27:09',1,'2015-09-01 07:30:36',0,1,NULL,NULL,0),(88,3,NULL,0,NULL,0,NULL,5,NULL,NULL,'Built-in',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-02 13:45:52',NULL,NULL,0,1,NULL,NULL,0),(89,3,NULL,0,NULL,0,NULL,5,NULL,NULL,'Config',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(90,2,NULL,0,NULL,0,NULL,5,NULL,NULL,'Config',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(91,89,NULL,0,NULL,0,NULL,11,NULL,NULL,'Config int option',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(92,91,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(93,91,NULL,0,NULL,0,NULL,12,NULL,NULL,'value',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(94,89,NULL,0,NULL,0,NULL,11,NULL,NULL,'Config varchar option',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(95,94,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(96,94,NULL,0,NULL,0,NULL,12,NULL,NULL,'value',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(97,89,NULL,0,NULL,0,NULL,11,NULL,NULL,'Config text option',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(98,97,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(99,97,NULL,0,NULL,0,NULL,12,NULL,NULL,'value',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(100,89,NULL,0,NULL,0,NULL,11,NULL,NULL,'Config json option',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(101,100,NULL,0,NULL,0,NULL,12,NULL,NULL,'_title',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(102,100,NULL,0,NULL,0,NULL,12,NULL,NULL,'value',NULL,NULL,NULL,NULL,'{\"editor\":\"ace\",\"format\":\"json\",\"validator\":\"json\"}',1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(103,100,NULL,0,NULL,0,NULL,12,NULL,NULL,'order',NULL,NULL,NULL,NULL,'{\"indexed\":true}',1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(104,90,NULL,0,NULL,0,NULL,94,NULL,NULL,'project_name_en',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(105,90,NULL,0,NULL,0,NULL,97,NULL,NULL,'templateIcons',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(106,90,NULL,0,NULL,0,NULL,97,NULL,NULL,'folder_templates',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(107,90,NULL,0,NULL,0,NULL,91,NULL,NULL,'default_folder_template',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(108,90,NULL,0,NULL,0,NULL,91,NULL,NULL,'default_file_template',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(109,90,NULL,0,NULL,0,NULL,91,NULL,NULL,'default_task_template',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(110,90,NULL,0,NULL,0,NULL,94,NULL,NULL,'default_language',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(111,90,NULL,0,NULL,0,NULL,94,NULL,NULL,'languages',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(112,90,NULL,0,NULL,0,NULL,100,NULL,NULL,'object_type_plugins',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(113,90,NULL,0,NULL,0,NULL,100,NULL,NULL,'treeNodes',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(114,113,NULL,0,NULL,0,NULL,100,NULL,NULL,'Tasks',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(115,113,NULL,0,NULL,0,NULL,100,NULL,NULL,'Dbnode',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),(116,113,NULL,0,NULL,0,NULL,100,NULL,NULL,'RecycleBin',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2015-11-25 13:52:47',0,1,NULL,NULL,0),(117,60,NULL,0,NULL,0,NULL,62,NULL,NULL,'Create config options rule',NULL,NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl` */

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tree_acl_security_sets` */

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

insert  into `tree_info`(`id`,`pids`,`path`,`case_id`,`acl_count`,`security_set_id`,`updated`) values (1,'1','',NULL,0,NULL,0),(2,'1,2','/',NULL,0,NULL,0),(3,'1,2,3','/System/',NULL,0,NULL,0),(4,'1,2,4','/System/',NULL,0,NULL,0),(5,'1,2,3,88,5','/System/Templates/',NULL,0,NULL,0),(6,'1,2,3,88,6','/System/Templates/',NULL,0,NULL,0),(7,'1,2,3,88,7','/System/Templates/',NULL,0,NULL,0),(8,'1,2,3,88,8','/System/Templates/',NULL,0,NULL,0),(9,'1,2,3,88,9','/System/Templates/',NULL,0,NULL,0),(10,'1,2,3,88,10','/System/Templates/',NULL,0,NULL,0),(11,'1,2,3,88,11','/System/Templates/',NULL,0,NULL,0),(12,'1,2,3,88,12','/System/Templates/',NULL,0,NULL,0),(13,'1,2,3,88,10,13','/System/Templates/User/',NULL,0,NULL,0),(14,'1,2,3,88,10,14','/System/Templates/User/',NULL,0,NULL,0),(15,'1,2,3,88,10,15','/System/Templates/User/',NULL,0,NULL,0),(16,'1,2,3,88,10,16','/System/Templates/User/',NULL,0,NULL,0),(17,'1,2,3,88,10,17','/System/Templates/User/',NULL,0,NULL,0),(18,'1,2,3,88,10,18','/System/Templates/User/',NULL,0,NULL,0),(19,'1,2,3,88,10,19','/System/Templates/User/',NULL,0,NULL,0),(20,'1,2,3,88,10,20','/System/Templates/User/',NULL,0,NULL,0),(21,'1,2,3,88,10,21','/System/Templates/User/',NULL,0,NULL,0),(22,'1,2,3,88,10,22','/System/Templates/User/',NULL,0,NULL,0),(23,'1,2,3,88,10,23','/System/Templates/User/',NULL,0,NULL,0),(24,'1,2,3,88,6,24','/System/Templates/file/',NULL,0,NULL,0),(25,'1,2,3,88,12,25','/System/Templates/Fields template/',NULL,0,NULL,0),(26,'1,2,3,88,12,26','/System/Templates/Fields template/',NULL,0,NULL,0),(27,'1,2,3,88,12,27','/System/Templates/Fields template/',NULL,0,NULL,0),(28,'1,2,3,88,12,28','/System/Templates/Fields template/',NULL,0,NULL,0),(29,'1,2,3,88,12,29','/System/Templates/Fields template/',NULL,0,NULL,0),(30,'1,2,3,88,12,30','/System/Templates/Fields template/',NULL,0,NULL,0),(31,'1,2,3,88,11,31','/System/Templates/Templates template/',NULL,0,NULL,0),(32,'1,2,3,88,11,32','/System/Templates/Templates template/',NULL,0,NULL,0),(33,'1,2,3,88,11,33','/System/Templates/Templates template/',NULL,0,NULL,0),(34,'1,2,3,88,11,34','/System/Templates/Templates template/',NULL,0,NULL,0),(35,'1,2,3,88,11,35','/System/Templates/Templates template/',NULL,0,NULL,0),(36,'1,2,3,88,11,36','/System/Templates/Templates template/',NULL,0,NULL,0),(37,'1,2,3,88,11,37','/System/Templates/Templates template/',NULL,0,NULL,0),(38,'1,2,3,88,11,38','/System/Templates/Templates template/',NULL,0,NULL,0),(39,'1,2,3,88,8,39','/System/Templates/Thesauri Item/',NULL,0,NULL,0),(40,'1,2,3,88,8,40','/System/Templates/Thesauri Item/',NULL,0,NULL,0),(41,'1,2,3,88,8,41','/System/Templates/Thesauri Item/',NULL,0,NULL,0),(42,'1,2,3,88,8,42','/System/Templates/Thesauri Item/',NULL,0,NULL,0),(43,'1,2,3,88,8,43','/System/Templates/Thesauri Item/',NULL,0,NULL,0),(44,'1,2,3,88,7,44','/System/Templates/task/',NULL,0,NULL,0),(45,'1,2,3,88,7,45','/System/Templates/task/',NULL,0,NULL,0),(46,'1,2,3,88,7,46','/System/Templates/task/',NULL,0,NULL,0),(47,'1,2,3,88,7,47','/System/Templates/task/',NULL,0,NULL,0),(48,'1,2,3,88,5,48','/System/Templates/folder/',NULL,0,NULL,0),(49,'1,2,3,88,9,49','/System/Templates/Comment/',NULL,0,NULL,0),(50,'1,2,3,88,7,50','/System/Templates/task/',NULL,0,NULL,0),(51,'1,2,3,88,7,51','/System/Templates/task/',NULL,0,NULL,0),(52,'1,2,4,52',NULL,NULL,0,NULL,0),(53,'1,2,4,52,53',NULL,NULL,0,NULL,0),(54,'1,2,4,52,53,54',NULL,NULL,0,NULL,0),(55,'1,2,4,52,53,55',NULL,NULL,0,NULL,0),(56,'1,2,4,52,53,56',NULL,NULL,0,NULL,0),(57,'1,2,4,52,53,57',NULL,NULL,0,NULL,0),(58,'1,2,3,88,58',NULL,NULL,0,NULL,0),(59,'1,2,3,88,59',NULL,NULL,0,NULL,0),(60,'1,2,60',NULL,NULL,0,NULL,0),(61,'1,2,3,88,59,61',NULL,NULL,0,NULL,0),(62,'1,2,3,88,59,62',NULL,NULL,0,NULL,0),(63,'1,2,3,88,59,62,63',NULL,NULL,0,NULL,0),(64,'1,2,3,88,59,62,64',NULL,NULL,0,NULL,0),(65,'1,2,3,88,59,62,65',NULL,NULL,0,NULL,0),(66,'1,2,3,88,59,62,66',NULL,NULL,0,NULL,0),(67,'1,2,3,88,59,62,67',NULL,NULL,0,NULL,0),(68,'1,2,60,68',NULL,NULL,0,NULL,0),(69,'1,2,60,69',NULL,NULL,0,NULL,0),(70,'1,2,60,70',NULL,NULL,0,NULL,0),(71,'1,2,60,71',NULL,NULL,0,NULL,0),(72,'1,2,60,72',NULL,NULL,0,NULL,0),(73,'1,2,60,73',NULL,NULL,0,NULL,0),(74,'1,2,4,74',NULL,NULL,0,NULL,0),(75,'1,2,4,74,75',NULL,NULL,0,NULL,0),(76,'1,2,4,74,75,76',NULL,NULL,0,NULL,0),(77,'1,2,4,74,75,77',NULL,NULL,0,NULL,0),(78,'1,2,4,74,75,78',NULL,NULL,0,NULL,0),(79,'1,2,4,74,75,79',NULL,NULL,0,NULL,0),(80,'1,2,4,74,75,80',NULL,NULL,0,NULL,0),(81,'1,2,4,74,75,81',NULL,NULL,0,NULL,0),(82,'1,2,4,74,82',NULL,NULL,0,NULL,0),(83,'1,2,3,88,83',NULL,NULL,0,NULL,0),(84,'1,2,3,88,83,84',NULL,NULL,0,NULL,0),(85,'1,2,3,88,83,85',NULL,NULL,0,NULL,0),(86,'1,2,3,88,83,86',NULL,NULL,0,NULL,0),(87,'1,2,3,88,83,87',NULL,NULL,0,NULL,0),(88,'1,2,3,88',NULL,NULL,0,NULL,0),(89,'1,2,3,89',NULL,NULL,0,NULL,0),(90,'1,2,90',NULL,NULL,0,NULL,0),(91,'1,2,3,89,91',NULL,NULL,0,NULL,0),(92,'1,2,3,89,91,92',NULL,NULL,0,NULL,0),(93,'1,2,3,89,91,93',NULL,NULL,0,NULL,0),(94,'1,2,3,89,94',NULL,NULL,0,NULL,0),(95,'1,2,3,89,94,95',NULL,NULL,0,NULL,0),(96,'1,2,3,89,94,96',NULL,NULL,0,NULL,0),(97,'1,2,3,89,97',NULL,NULL,0,NULL,0),(98,'1,2,3,89,97,98',NULL,NULL,0,NULL,0),(99,'1,2,3,89,97,99',NULL,NULL,0,NULL,0),(100,'1,2,3,89,100',NULL,NULL,0,NULL,0),(101,'1,2,3,89,100,101',NULL,NULL,0,NULL,0),(102,'1,2,3,89,100,102',NULL,NULL,0,NULL,0),(103,'1,2,3,89,100,103',NULL,NULL,0,NULL,0),(104,'1,2,90,104',NULL,NULL,0,NULL,0),(105,'1,2,90,105',NULL,NULL,0,NULL,0),(106,'1,2,90,106',NULL,NULL,0,NULL,0),(107,'1,2,90,107',NULL,NULL,0,NULL,0),(108,'1,2,90,108',NULL,NULL,0,NULL,0),(109,'1,2,90,109',NULL,NULL,0,NULL,0),(110,'1,2,90,110',NULL,NULL,0,NULL,0),(111,'1,2,90,111',NULL,NULL,0,NULL,0),(112,'1,2,90,112',NULL,NULL,0,NULL,0),(113,'1,2,90,113',NULL,NULL,0,NULL,0),(114,'1,2,90,113,114',NULL,NULL,0,NULL,0),(115,'1,2,90,113,115',NULL,NULL,0,NULL,0),(116,'1,2,90,113,116',NULL,NULL,0,NULL,0),(117,'1,2,60,117',NULL,NULL,0,NULL,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups` */

insert  into `users_groups`(`id`,`type`,`system`,`name`,`first_name`,`last_name`,`l1`,`l2`,`l3`,`l4`,`sex`,`email`,`photo`,`password`,`password_change`,`recover_hash`,`language_id`,`cfg`,`data`,`last_login`,`login_successful`,`login_from_ip`,`last_logout`,`last_action_time`,`enabled`,`cid`,`cdate`,`uid`,`udate`,`did`,`ddate`,`searchField`) values (1,2,1,'root','Administrator','','Administrator','Administrator','Administrator','Administrator','m','a',NULL,'50775b4f5109fd22c46dabb17f710c17','2015-05-21',NULL,1,'{\"short_date_format\":\"%m\\/%d\\/%Y\",\"long_date_format\":\"%F %j, %Y\",\"country_code\":\"\",\"phone\":\"\",\"timezone\":\"\",\"security\":{\"recovery_email\":true,\"email\":\"admin@mail.server.com\"},\"state\":{\"mAc\":{\"width\":250,\"weight\":-10},\"mopp\":{\"weight\":-20},\"oew100\":{\"width\":600,\"height\":450,\"maximized\":false,\"size\":{\"width\":600,\"height\":450},\"pos\":[853,500]},\"oevg\":{\"columns\":{\"title\":{\"idx\":0,\"width\":100},\"value\":{\"idx\":1,\"flex\":1}},\"group\":null}},\"color\":\"#8fada9\",\"lastNotifyTime\":\"2015-11-25 13:52:48\"}','{\"email\": \"a\"}','2015-12-16 12:53:28',1,'|127.0.0.1|',NULL,'2015-12-16 12:53:47',1,1,NULL,1,'2013-03-20 12:57:29',NULL,NULL,' root Administrator Administrator Administrator Administrator a '),(2,1,1,'everyone','Everyone',NULL,'Everyone','Everyone','Everyone','Everyone',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2015-05-20 17:17:52',NULL,'0000-00-00 00:00:00',NULL,NULL,' everyone Everyone Everyone Everyone Everyone  ');

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
	In second case we have to add the new security set and update all lower security sets form that tree branch
	*/
	IF((tmp_acl_count > 1) OR
	  (tmp_old_security_set = new.node_id) OR
	  (CONCAT(',', tmp_old_security_set) LIKE CONCAT('%,', new.node_id))
	 ) THEN
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
