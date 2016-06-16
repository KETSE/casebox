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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8;

/*Data for the table `config` */

insert  into `config`(`id`,`pid`,`param`,`value`,`order`) values

(104,NULL,'project_name_en','CaseBox - Demo',NULL),

(105,NULL,'templateIcons','\nfa fa-arrow-circle-left fa-fl\nfa fa-arrow-circle-o-left fa-fl\nfa fa-arrow-circle-o-right fa-fl\nfa fa-arrow-circle-right fa-fl\nfa fa-arrow-left fa-fl\nfa fa-arrow-right fa-fl\nfa fa-book fa-fl\nfa fa-bookmark fa-fl\nfa fa-bookmark-o fa-fl\nfa fa-briefcase fa-fl\nfa fa-bug fa-fl\nfa fa-building fa-fl\nfa fa-building-o fa-fl\nfa fa-calendar-o fa-fl\nfa fa-camera fa-fl\nfa fa-comment fa-fl\nfa fa-comment-o fa-fl\nfa fa-commenting fa-fl\nfa fa-commenting-o fa-fl\nfa fa-comments fa-fl\nfa fa-comments-o fa-fl\nfa fa-envelope fa-fl\nfa fa-envelope-o fa-fl\nfa fa-external-link fa-fl\nfa fa-external-link-square  fa-fl\nfa fa-file fa-fl\nfa fa-file-archive-o fa-fl\nfa fa-file-audio-o fa-fl\nfa fa-file-code-o fa-fl\nfa fa-file-excel-o fa-fl\nfa fa-file-image-o fa-fl\nfa fa-file-movie-o fa-fl\nfa fa-file-o fa-fl\nfa fa-file-pdf-o fa-fl\nfa fa-file-photo-o fa-fl\nfa fa-file-picture-o fa-fl\nfa fa-file-powerpoint-o fa-fl\nfa fa-file-sound-o fa-fl\nfa fa-file-text fa-fl\nfa fa-file-text-o fa-fl\nfa fa-file-video-o fa-fl\nfa fa-file-word-o fa-fl\nfa fa-file-zip-o fa-fl\nfa fa-files-o fa-fl\nfa fa-film fa-fl\nfa fa-flash fa-fl\nfa fa-folder fa-fl\nfa fa-folder-o fa-fl\nfa fa-folder-open fa-fl\nfa fa-folder-open-o fa-fl\nfa fa-foursquare fa-fl\nfa fa-gavel fa-fl\nfa fa-gear fa-fl\nfa fa-gears fa-fl\nfa fa-info fa-fl\nfa fa-info-circle fa-fl\nfa fa-institution fa-fl\nfa fa-link fa-fl\nfa fa-print fa-fl\nfa fa-stack-exchange fa-fl\nfa fa-sticky-note fa-fl\nfa fa-sticky-note-o fa-fl\nfa fa-suitcase fa-fl\nfa fa-tasks fa-fl\nfa fa-university fa-fl\nfa fa-unlink fa-fl\nfa fa-user fa-fl\nfa fa-user-md fa-fl\nfa fa-user-plus fa-fl\nfa fa-user-secret fa-fl\nfa fa-user-times fa-fl\nfa fa-users fa-fl\nfa fa-warning fa-fl\nfa fa-wpforms fa-fl',NULL),

(106,NULL,'folder_templates','5,11,100',NULL),

(107,NULL,'default_folder_template','5',NULL),

(108,NULL,'default_file_template','6',NULL),

(109,NULL,'default_task_template','7',NULL),

(110,NULL,'default_language','en',NULL),

(111,NULL,'languages','en',NULL),

(112,NULL,'object_type_plugins','{\r\n  \"object\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"case\": [\"objectProperties\", \"files\", \"tasks\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"task\": [\"objectProperties\", \"files\", \"contentItems\", \"comments\", \"systemProperties\"]\r\n  ,\"file\": [\"thumb\", \"meta\", \"versions\", \"tasks\", \"comments\", \"systemProperties\"]\r\n}',NULL),

(113,NULL,'treeNodes','',NULL),

(114,113,'Tasks','{\n    \"pid\": 1\n}',1),

(115,113,'Dbnode','[]',2),

(116,113,'RecycleBin','{\r\n    \"pid\": \"1\",\r\n    \"facets\": [\r\n        \"did\"\r\n    ],\r\n    \"DC\": {\r\n        \"nid\": {}\r\n        ,\"name\": {}\r\n        ,\"cid\": {}\r\n        ,\"ddate\": {\r\n            \"solr_column_name\": \"ddate\"\r\n        }\r\n    }\r\n}',3),

(117,NULL,'default_object_plugins','{\n\"objectProperties\": {\n\"visibility\": {\n\"!context\": \"window\"\n,\"!template_type\": \"file\"\n}\n,\"order\": 0\n}\n,\"files\": {\n\"visibility\": {\n\"template_type\": \"object,search,case,task\"\n}\n,\"order\": 2\n}\n,\"tasks\": {\n\"visibility\": {\n\"template_type\": \"object,search,case,task\"\n}\n,\"order\": 3\n}\n,\"contentItems\": {\n\"visibility\": {\n\"!template_type\": \"file,time_tracking\"\n}\n,\"order\": 4\n}\n,\"thumb\": {\n\"visibility\": {\n\"!context\": \"window\"\n,\"template_type\": \"file\"\n}\n,\"order\": 5\n}\n,\"currentVersion\": {\n\"visibility\": {\n\"context\": \"window\"\n,\"template_type\": \"file\"\n}\n,\"order\": 6\n}\n,\"versions\": {\n\"visibility\": {\n\"template_type\": \"file\"\n}\n,\"order\": 7\n}\n,\"meta\": {\n\"visibility\": {\n\"template_type\": \"file\"\n}\n,\"order\": 8\n}\n,\"comments\": {\n\"order\": 9\n,\"visibility\": {\n\"!template_type\": \"time_tracking\"\n}\n\n}\n}',NULL),

(118,NULL,'files','{\r\n  \"max_versions\": \"*:1;php,odt,doc,docx,xls,xlsx:20;pdf:5;png,gif,jpg,jpeg,tif,tiff:2;\"\r\n\r\n  ,\"edit\" : {\r\n    \"text\": \"txt,php,js,xml,csv\"\r\n    ,\"html\": \"html,htm\"\r\n    ,\"webdav\": \"doc,docx,ppt,dot,dotx,xls,xlsm,xltx,ppt,pot,pps,pptx,odt,ott,odm,ods,odg,otg,odp,odf,odb\"\r\n  }\r\n\r\n  ,\"webdav_url\": \"https://webdav.host.com/{core_name}/edit-{node_id}/{name}\"\r\n}',NULL),

(119,NULL,'timezone','UTC',NULL),

(120,NULL,'language_en','{\r\n\"name\": \"English\"\r\n,\"locale\": \"en_US\"\r\n,\"long_date_format\": \"%F %j, %Y\"\r\n,\"short_date_format\": \"%m/%d/%Y\"\r\n,\"time_format\": \"%H:%i\"\r\n}',NULL),

(121,NULL,'language_fr','{\r\n\"name\": \"French\"\r\n,\"locale\": \"fr_FR\"\r\n,\"long_date_format\": \"%j %F %Y\"\r\n,\"short_date_format\": \"%d.%m.%Y\"\r\n,\"time_format\": \"%H:%i\"\r\n}\r\n',NULL),

(122,NULL,'language_ru','{\r\n\"name\": \"Русский\"\r\n,\"locale\": \"ru_RU\"\r\n,\"long_date_format\": \"%j %F %Y\"\r\n,\"short_date_format\": \"%d.%m.%Y\"\r\n,\"time_format\": \"%H:%i\"\r\n}',NULL),

(123,NULL,'default_facet_configs','{\r\n  \"template_type\": {\r\n    \"title\": \"[Type]\"\r\n    ,\"type\": \"objectTypes\"\r\n  }\r\n  ,\"template\": {\r\n    \"title\": \"[Template]\"\r\n    ,\"field\": \"template_id\"\r\n    ,\"type\": \"objects\"\r\n  }\r\n  ,\"creator\": {\r\n    \"title\": \"[Creator]\"\r\n    ,\"field\": \"cid\"\r\n    ,\"type\": \"users\"\r\n  }\r\n  ,\"owner\": {\r\n    \"title\": \"[Owner]\"\r\n    ,\"field\": \"oid\"\r\n    ,\"type\": \"users\"\r\n  }\r\n  ,\"updater\": {\r\n    \"title\": \"Updater\"\r\n    ,\"field\": \"uid\"\r\n    ,\"type\": \"users\"\r\n  }\r\n  ,\"date\": {\r\n    \"title\": \"[Date]\"\r\n    ,\"facet\": \"query\"\r\n    ,\"type\": \"dates\"\r\n    ,\"manualPeriod\": true\r\n    ,\"queries\": [\r\n      \"today\"\r\n      ,\"yesterday\"\r\n      ,\"week\"\r\n      ,\"month\"\r\n    ]\r\n    ,\"boolMode\": true\r\n  }\r\n  ,\"date_end\": {\r\n    \"title\": \"End date\"\r\n    ,\"facet\": \"query\"\r\n    ,\"type\": \"dates\"\r\n    ,\"queries\": [\r\n      \"today\"\r\n      ,\"week\"\r\n      ,\"next7days\"\r\n      ,\"next31days\"\r\n      ,\"month\"\r\n    ]\r\n    ,\"boolMode\": true\r\n  }\r\n  ,\"status\": {\r\n    \"title\": \"[Status]\"\r\n    ,\"type\": \"objects\"\r\n }\r\n  ,\"task_status\": {\r\n    \"title\": \"[Status]\"\r\n    ,\"type\": \"taskStatuses\"\r\n }\r\n  ,\"assigned\": {\r\n    \"title\": \"[TaskAssigned]\"\r\n    ,\"field\": \"task_u_assignee\"\r\n    ,\"type\": \"users\"\r\n    ,\"boolMode\": true\r\n  }\r\n\r\n}',NULL),

(124,NULL,'node_facets','{\r\n\"1\" : [\r\n  \"template_type\"\r\n  ,\"creator\"\r\n  ,\"template\"\r\n  ,\"date\"\r\n  ,\"status\"\r\n  ,\"assigned\"\r\n]\r\n}',NULL),

(125,NULL,'default_object_plugins','{\r\n  \"objectProperties\": {\r\n    \"visibility\": {\r\n      \"!context\": \"window\"\r\n      ,\"!template_type\": \"file\"\r\n    }\r\n    ,\"order\": 0\r\n  }\r\n  ,\"files\": {\r\n    \"visibility\": {\r\n      \"template_type\": \"object,search,case,task\"\r\n    }\r\n    ,\"order\": 2\r\n  }\r\n  ,\"tasks\": {\r\n    \"visibility\": {\r\n      \"template_type\": \"object,search,case,task\"\r\n    }\r\n    ,\"order\": 3\r\n  }\r\n  ,\"contentItems\": {\r\n    \"visibility\": {\r\n      \"!template_type\": \"file,time_tracking\"\r\n    }\r\n    ,\"order\": 4\r\n  }\r\n  ,\"thumb\": {\r\n    \"visibility\": {\r\n      \"!context\": \"window\"\r\n      ,\"template_type\": \"file\"\r\n    }\r\n    ,\"order\": 5\r\n  }\r\n  ,\"currentVersion\": {\r\n    \"visibility\": {\r\n      \"context\": \"window\"\r\n      ,\"template_type\": \"file\"\r\n    }\r\n    ,\"order\": 6\r\n  }\r\n  ,\"versions\": {\r\n    \"visibility\": {\r\n      \"template_type\": \"file\"\r\n    }\r\n    ,\"order\": 7\r\n  }\r\n  ,\"meta\": {\r\n    \"visibility\": {\r\n      \"template_type\": \"file\"\r\n    }\r\n    ,\"order\": 8\r\n  }\r\n  ,\"comments\": {\r\n    \"order\": 9\r\n    ,\"visibility\": {\r\n      \"!template_type\": \"time_tracking\"\r\n    }\r\n\r\n  }\r\n}',NULL),

(126,NULL,'images_display_size','512000',NULL),

(127,NULL,'default_DC','{\r\n\"nid\": {}\r\n,\"name\": {\r\n  \"solr_column_name\": \"name\"\r\n}\r\n,\"date\": {\r\n  \"solr_column_name\": \"date\"\r\n}\r\n,\"size\": {\r\n  \"solr_column_name\": \"size\"\r\n}\r\n,\"cid\": {\r\n  \"solr_column_name\": \"cid\"\r\n}\r\n,\"oid\": {\r\n  \"solr_column_name\": \"oid\"\r\n}\r\n,\"cdate\": {\r\n  \"solr_column_name\": \"cdate\"\r\n}\r\n,\"udate\": {\r\n  \"solr_column_name\": \"udate\"\r\n}\r\n}',NULL),

(128,NULL,'default_availableViews','grid,charts,pivot,activityStream',NULL),

(129,NULL,'DCConfigs','',NULL),

(130,129,'dc_tasks','{\r\n    \"nid\":[]\r\n    ,\"name\":[]\r\n    ,\"importance\":{\"solr_column_name\":\"task_importance\"}\r\n    ,\"order\":{\r\n        \"solr_column_name\":\"task_order\"\r\n        ,\"sortType\":\"asInt\"\r\n        ,\"align\":\"center\"\r\n        ,\"columnWidth\":\"10\"\r\n    }\r\n    ,\"time_estimated\":{\r\n        \"width\":\"20px\"\r\n        ,\"format\":\"H:i\"\r\n    }\r\n    ,\"phase\": {\r\n        \"solr_column_name\": \"task_phase\"\r\n    }\r\n    ,\"project\": {\r\n        \"solr_column_name\": \"task_projects\"\r\n    }\r\n    ,\"cid\":[]\r\n    ,\"assigned\":[]\r\n    ,\"comment_user_id\":[]\r\n    ,\"comment_date\":[]\r\n    ,\"cdate\":[]\r\n}',NULL),

(131,129,'dc_tasks_closed','{\r\n    \"nid\":[]\r\n    ,\"name\":[]\r\n    ,\"importance\":{\"solr_column_name\":\"task_importance\"}\r\n    ,\"order\":{\"solr_column_name\":\"task_order\"\r\n        ,\"sortType\":\"asInt\"\r\n        ,\"align\":\"center\"\r\n        ,\"columnWidth\":\"10\"\r\n    }\r\n    ,\"project\": {\r\n        \"solr_column_name\": \"task_projects\"\r\n    }    \r\n    ,\"time_completed\":{\r\n        \"columnWidth\":\"20\"\r\n        ,\"format\":\"H:i\"\r\n    }\r\n    ,\"time_estimated\":{\r\n        \"width\":\"20px\"\r\n        ,\"format\":\"H:i\"\r\n    }\r\n    ,\"task_d_closed\":{\r\n        \"solr_column_name\":\"task_d_closed\"\r\n        ,\"xtype\":\"datecolumn\"\r\n        ,\"format\":\"Y-m-d\"\r\n        ,\"title\":\"Closed date\"\r\n    }\r\n    ,\"cid\":[]\r\n    ,\"cdate\":[]\r\n    ,\"assigned\":[]\r\n    ,\"comment_user_id\":[]\r\n    ,\"comment_date\":[]\r\n}',NULL),

(132,NULL,'geoMapping','false',NULL);

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

insert  into `guids`(`id`,`name`) values

(2,'Dbnode'),

(3,'RecycleBin'),

(1,'Tasks');

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
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;

/*Data for the table `objects` */

insert  into `objects`(`id`,`data`,`sys_data`) values

(1,'{\"_title\":\"Tree\",\"en\":\"Tree\"}',NULL),

(2,'{\"_title\":\"System\",\"en\":\"System\"}',NULL),

(3,'{\"_title\":\"Templates\",\"en\":\"Templates\"}',NULL),

(4,'{\"_title\":\"Thesauri\",\"en\":\"Thesauri\"}',NULL),

(5,'{\"_title\":\"folder\",\"en\":\"Folder\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"fa fa-folder fa-fl\",\"cfg\":\"{\\\"createMethod\\\":\\\"inline\\\",\\n\\n  \\\"object_plugins\\\":\\n      [\\\"comments\\\",\\n       \\\"systemProperties\\\"\\n      ]\\n\\n}\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"3\"},\"time\":\"2016-06-09T13:50:12Z\"},\"solr\":{\"content\":\"folder\\nFolder\\nobject\\n1\\nfa fa-folder fa-fl\\n{\\\"createMethod\\\":\\\"inline\\\",\\n\\n  \\\"object_plugins\\\":\\n      [\\\"comments\\\",\\n       \\\"systemProperties\\\"\\n      ]\\n\\n}\\n{name}\\n\"},\"wu\":[]}'),

(6,'{\"_title\":\"file_template\",\"en\":\"File\",\"type\":\"file\",\"visible\":1,\"iconCls\":\"fa fa-file fa-fl\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"4\"},\"time\":\"2016-06-09T13:50:28Z\"},\"solr\":{\"content\":\"file_template\\nFile\\nfile\\n1\\nfa fa-file fa-fl\\n{name}\\n\"},\"wu\":[]}'),

(7,'{\"_title\":\"task\",\"en\":\"Task\",\"type\":\"task\",\"visible\":1,\"iconCls\":\"fa fa-calendar-o fa-fl\",\"title_template\":\"{name}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"5\"},\"time\":\"2016-06-09T13:51:01Z\"},\"solr\":{\"content\":\"task\\nTask\\ntask\\n1\\nfa fa-calendar-o fa-fl\\n{name}\\n\"},\"wu\":[]}'),

(8,'{\"_title\":\"Thesauri Item\",\"en\":\"Thesauri Item\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"fa fa-sticky-note fa-fl\",\"title_template\":\"{en}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"6\"},\"time\":\"2016-06-09T13:52:05Z\"},\"solr\":{\"content\":\"Thesauri Item\\nThesauri Item\\nobject\\n1\\nfa fa-sticky-note fa-fl\\n{en}\\n\"},\"wu\":[]}'),

(9,'{\"_title\":\"Comment\",\"en\":\"Comment\",\"type\":\"comment\",\"visible\":1,\"iconCls\":\"fa fa-comment fa-fl\",\"cfg\":\"{\\n  \\\"systemType\\\": 2\\n}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"7\"},\"time\":\"2016-06-09T13:52:26Z\"},\"solr\":{\"content\":\"Comment\\nComment\\ncomment\\n1\\nfa fa-comment fa-fl\\n{\\n  \\\"systemType\\\": 2\\n}\\n\"},\"wu\":[]}'),

(10,'{\"_title\":\"User\",\"en\":\"User\",\"type\":\"user\",\"visible\":1,\"iconCls\":\"fa fa-user fa-fl\",\"cfg\":\"{\\\"object_plugins\\\":{\\\"objectProperties\\\":[],\\\"userSecurity\\\":[]}}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"8\",\"\":null},\"time\":\"2016-06-16T09:15:11Z\"},\"solr\":{\"content\":\"\"},\"wu\":[]}'),

(11,'{\"_title\":\"Template\",\"en\":\"Template\",\"type\":\"template\",\"visible\":1,\"iconCls\":\"fa fa-file-code-o fa-fl\",\"cfg\":\"{\\n\\\"DC\\\": {\\n  \\\"nid\\\": {},\\n  \\\"name\\\": {},\\n  \\\"type\\\": {},\\n  \\\"cfg\\\": {},\\n  \\\"order\\\": {\\n     \\\"sortType\\\": \\\"asInt\\\"\\n     ,\\\"solr_column_name\\\": \\\"order\\\"\\n  }\\n}\\n}\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"16\"},\"time\":\"2016-06-09T13:56:21Z\"},\"solr\":{\"content\":\"Template\\nTemplate\\ntemplate\\n1\\nfa fa-file-code-o fa-fl\\n{\\n\\\"DC\\\": {\\n  \\\"nid\\\": {},\\n  \\\"name\\\": {},\\n  \\\"type\\\": {},\\n  \\\"cfg\\\": {},\\n  \\\"order\\\": {\\n     \\\"sortType\\\": \\\"asInt\\\"\\n     ,\\\"solr_column_name\\\": \\\"order\\\"\\n  }\\n}\\n}\\n\"},\"wu\":[]}'),

(12,'{\"_title\":\"Field\",\"en\":\"Field\",\"type\":\"field\",\"visible\":1,\"iconCls\":\"fa fa-foursquare fa-fl\",\"cfg\":\"[]\"}','{\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"9\"},\"time\":\"2016-06-09T13:53:18Z\"},\"solr\":{\"content\":\"Field\\nField\\nfield\\n1\\nfa fa-foursquare fa-fl\\n[]\\n\"},\"wu\":[],\"solrConfigUpdated\":true}'),

(13,'{\"_title\":\"en\",\"en\":\"Full name (en)\",\"type\":\"varchar\",\"order\":2}','{\"wu\":[],\"solr\":{\"content\":\"en\\nFull name (en)\\nvarchar\\n2\\n\",\"order\":2},\"lastAction\":{\"type\":\"update\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"1\":0,\"\":null}}}'),

(14,'{\"_title\":\"initials\",\"en\":\"Initials\",\"type\":\"varchar\",\"order\":5}','{\"wu\":[],\"solr\":{\"content\":\"initials\\nInitials\\nvarchar\\n5\\n\",\"order\":5},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(15,'{\"_title\":\"sex\",\"en\":\"Sex\",\"type\":\"combo\",\"cfg\":\"{\\\"source\\\":\\\"sex\\\"}\",\"order\":10}','{\"wu\":[],\"solr\":{\"content\":\"sex\\nSex\\ncombo\\n10\\n{\\\"source\\\":\\\"sex\\\"}\\n\",\"order\":10},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(17,'{\"_title\":\"email\",\"en\":\"Email\",\"type\":\"varchar\",\"cfg\":\"{\\\"validator\\\":\\\"email\\\"}\",\"order\":15}','{\"wu\":[],\"solr\":{\"content\":\"email\\nEmail\\nvarchar\\n15\\n{\\\"validator\\\":\\\"email\\\"}\\n\",\"order\":15},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(18,'{\"_title\":\"language_id\",\"en\":\"Language\",\"type\":\"combo\",\"cfg\":\"{\\\"source\\\":\\\"languages\\\", \\\"required\\\": true}\",\"order\":30}','{\"wu\":[],\"solr\":{\"content\":\"language_id\\nLanguage\\ncombo\\n30\\n{\\\"source\\\":\\\"languages\\\", \\\"required\\\": true}\\n\",\"order\":30},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(19,'{\"_title\":\"short_date_format\",\"en\":\"Date format\",\"type\":\"combo\",\"cfg\":\"{\\\"source\\\":\\\"shortDateFormats\\\"}\",\"order\":40}','{\"wu\":[],\"solr\":{\"content\":\"short_date_format\\nDate format\\ncombo\\n40\\n{\\\"source\\\":\\\"shortDateFormats\\\"}\\n\",\"order\":40},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(20,'{\"_title\":\"description\",\"en\":\"Description\",\"type\":\"varchar\",\"order\":50}','{\"wu\":[],\"solr\":{\"content\":\"description\\nDescription\\nvarchar\\n50\\n\",\"order\":50},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(22,'{\"_title\":\"phone\",\"en\":\"Phone\",\"type\":\"varchar\",\"order\":25}','{\"wu\":[],\"solr\":{\"content\":\"phone\\nPhone\\nvarchar\\n25\\n\",\"order\":25},\"lastAction\":{\"type\":\"update\",\"users\":{\"\":null},\"time\":\"2016-06-16T09:15:11Z\"}}'),

(24,'{\"en\":\"Program\",\"ru\":\"Program\",\"_title\":\"program\",\"type\":\"_objects\",\"order\":\"1\",\"cfg\":\"{\\r\\n\\\"source\\\":\\\"thesauri\\\"\\r\\n,\\\"thesauriId\\\": \\\"715\\\"\\r\\n,\\\"multiValued\\\": true\\r\\n,\\\"autoLoad\\\": true\\r\\n,\\\"editor\\\":\\\"form\\\"\\r\\n,\\\"renderer\\\": \\\"listGreenIcons\\\"\\r\\n,\\\"faceting\\\": true\\r\\n}\",\"solr_column_name\":\"category_id\"}','[]'),

(25,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),

(26,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_fieldTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),

(27,'{\"_title\":\"order\",\"en\":\"Order\",\"type\":\"int\",\"order\":\"6\",\"cfg\":\"{\\n  \\\"indexed\\\": true\\n}\",\"solr_column_name\":\"order\"}','{\"wu\":[],\"solr\":{\"content\":\"order\\nOrder\\nint\\n6\\n{\\n  \\\"indexed\\\": true\\n}\\norder\\n\"},\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"17\"},\"time\":\"2016-06-09T13:57:55Z\"}}'),

(28,'{\"_title\":\"cfg\",\"en\":\"Config\",\"ru\":\"Config\",\"type\":\"memo\",\"order\":\"7\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),

(29,'{\"en\":\"Solr column name\",\"ru\":\"Solr column name\",\"_title\":\"solr_column_name\",\"type\":\"varchar\",\"order\":\"8\",\"cfg\":\"[]\"}','[]'),

(30,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),

(31,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Name\",\"type\":\"varchar\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\",\\\"rea-dOnly\\\":true}\"}','[]'),

(32,'{\"en\":\"Type\",\"ru\":\"Type\",\"_title\":\"type\",\"type\":\"_templateTypesCombo\",\"order\":\"5\",\"cfg\":\"[]\"}','[]'),

(33,'{\"en\":\"Active\",\"ru\":\"Active\",\"_title\":\"visible\",\"type\":\"checkbox\",\"order\":\"6\",\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','[]'),

(34,'{\"en\":\"Icon class\",\"ru\":\"Icon class\",\"_title\":\"iconCls\",\"type\":\"iconcombo\",\"order\":\"7\",\"cfg\":\"[]\"}','[]'),

(35,'{\"en\":\"Config\",\"ru\":\"Config\",\"_title\":\"cfg\",\"type\":\"text\",\"order\":\"8\",\"cfg\":\"{\\\"height\\\":100}\"}','[]'),

(36,'{\"en\":\"Title template\",\"ru\":\"Title template\",\"_title\":\"title_template\",\"type\":\"text\",\"order\":\"9\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),

(37,'{\"en\":\"Info template\",\"ru\":\"Info template\",\"_title\":\"info_template\",\"type\":\"text\",\"order\":\"10\",\"cfg\":\"{\\\"height\\\":50}\"}','[]'),

(38,'{\"en\":\"Title (en)\",\"ru\":\"Title (en)\",\"_title\":\"en\",\"type\":\"varchar\",\"order\":\"1\",\"cfg\":\"[]\"}','[]'),

(39,'{\"_title\":\"iconCls\",\"en\":\"Icon class\",\"type\":\"iconcombo\",\"order\":5}','{\"solr\":[]}'),

(40,'{\"_title\":\"visible\",\"en\":\"Visible\",\"type\":\"checkbox\",\"order\":6}','{\"solr\":[]}'),

(41,'{\"_title\":\"order\",\"en\":\"Order\",\"type\":\"int\",\"order\":7,\"cfg\":\"{\\n\\\"indexed\\\": true\\n}\",\"solr_column_name\":\"order\"}','{\"solr\":[]}'),

(42,'{\"_title\":\"en\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":0,\"cfg\":\"{\\\"showIn\\\":\\\"top\\\"}\"}','{\"solr\":[]}'),

(44,'{\"_title\":\"_title\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":1,\"cfg\":\"{\\n\\\"required\\\": true\\n,\\\"hidePreview\\\": true\\n}\"}','[]'),

(45,'{\"_title\":\"assigned\",\"en\":\"Assigned\",\"type\":\"_objects\",\"order\":7,\"cfg\":\"{\\n  \\\"editor\\\": \\\"form\\\"\\n  ,\\\"source\\\": \\\"users\\\"\\n  ,\\\"renderer\\\": \\\"listObjIcons\\\"\\n  ,\\\"autoLoad\\\": true\\n  ,\\\"multiValued\\\": true\\n  ,\\\"hidePreview\\\": true\\n}\"}','[]'),

(46,'{\"_title\":\"importance\",\"en\":\"Importance\",\"type\":\"_objects\",\"order\":8,\"cfg\":\"{\\n  \\\"scope\\\": 53,\\n  \\\"value\\\": 54,\\n  \\\"faceting\\\": true\\n}\"}','[]'),

(47,'{\"_title\":\"description\",\"en\":\"Description\",\"type\":\"memo\",\"order\":10,\"cfg\":\"{\\n  \\\"height\\\": 100\\n  ,\\\"noHeader\\\": true\\n  ,\\\"hidePreview\\\": true\\n  ,\\\"linkRenderer\\\": \\\"user,object,url\\\"\\n}\"}','[]'),

(48,'{\"_title\":\"_title\",\"en\":\"Name\",\"ru\":\"Название\",\"type\":\"varchar\",\"order\":1}','[]'),

(49,'{\"_title\":\"_title\",\"en\":\"Text\",\"ru\":\"Текст\",\"type\":\"memo\",\"order\":0,\"cfg\":\"{\\n\\\"height\\\": 100\\n}\",\"solr_column_name\":\"content\"}','[]'),

(50,'{\"_title\":\"due_date\",\"en\":\"Due date\",\"type\":\"date\",\"order\":5,\"cfg\":\"{\\n\\\"hidePreview\\\": true\\n}\"}','[]'),

(51,'{\"_title\":\"due_time\",\"en\":\"Due time\",\"type\":\"time\",\"order\":6,\"cfg\":\"{\\n\\\"hidePreview\\\": true\\n}\"}','[]'),

(52,'{\"_title\":\"task\"}','[]'),

(53,'{\"_title\":\"Importance\"}','[]'),

(54,'{\"en\":\"Low\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":1}','[]'),

(55,'{\"en\":\"Medium\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":2}','[]'),

(56,'{\"en\":\"High\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":3}','[]'),

(57,'{\"en\":\"CRITICAL\",\"iconCls\":\"icon-tag-small\",\"visible\":1,\"order\":4}','[]'),

(58,'{\"_title\":\"shortcut\",\"en\":\"Shortcut\",\"type\":\"shortcut\",\"visible\":1,\"iconCls\":\"fa fa-external-link-square  fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"shortcut\\nShortcut\\nshortcut\\n1\\nfa fa-external-link-square  fa-fl\\n\"},\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"10\"},\"time\":\"2016-06-09T13:53:35Z\"},\"wu\":[]}'),

(59,'{\"_title\":\"Menu\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"move\",\"users\":{\"1\":1},\"time\":\"2015-09-07T12:32:02Z\"}}'),

(60,'{\"_title\":\"Menus\"}','{\"fu\":[],\"solr\":[]}'),

(61,'{\"_title\":\"- Menu separator -\",\"en\":\"- Menu separator -\",\"type\":\"object\",\"visible\":1}','{\"fu\":[1],\"solr\":[]}'),

(62,'{\"_title\":\"Menu rule\",\"en\":\"Menu rule\",\"type\":\"menu\",\"visible\":1}','{\"fu\":[1],\"solr\":[]}'),

(63,'{\"name\":\"_title\",\"en\":\"Title\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),

(64,'{\"name\":\"node_ids\",\"en\":\"Nodes\",\"type\":\"_objects\",\"order\":2,\"cfg\":\"{\\\"multiValued\\\":true,\\\"editor\\\":\\\"form\\\",\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),

(65,'{\"name\":\"template_ids\",\"en\":\"Templates\",\"type\":\"_objects\",\"order\":3,\"cfg\":\"{\\\"templates\\\":\\\"11\\\",\\\"editor\\\":\\\"form\\\",\\\"multiValued\\\":true,\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),

(66,'{\"name\":\"user_group_ids\",\"en\":\"Users\\/Groups\",\"type\":\"_objects\",\"order\":4,\"cfg\":\"{\\\"source\\\":\\\"usersgroups\\\",\\\"multiValued\\\":true}\"}','{\"fu\":[1],\"solr\":[]}'),

(67,'{\"name\":\"menu\",\"en\":\"Menu\",\"type\":\"_objects\",\"order\":5,\"cfg\":\"{\\\"templates\\\":\\\"11\\\",\\\"multiValued\\\":true,\\\"editor\\\":\\\"form\\\",\\\"allowValueSort\\\":true,\\\"renderer\\\":\\\"listObjIcons\\\"}\"}','{\"fu\":[1],\"solr\":[]}'),

(68,'{\"_title\":\"Global Menu\",\"menu\":\"7,83,61,5\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":15},\"time\":\"2015-09-01T07:28:14Z\"}}'),

(69,'{\"_title\":\"System Templates\",\"node_ids\":\"3\",\"template_ids\":null,\"user_group_ids\":null,\"menu\":\"11,5\"}','{\"fu\":[1],\"solr\":[]}'),

(70,'{\"_title\":\"System Templates SubMenu\",\"node_ids\":null,\"template_ids\":\"11\",\"user_group_ids\":null,\"menu\":\"12\"}','{\"fu\":[1],\"solr\":[]}'),

(71,'{\"_title\":\"System Fields\",\"node_ids\":null,\"template_ids\":\"12\",\"user_group_ids\":null,\"menu\":\"12\"}','{\"fu\":[1],\"solr\":[]}'),

(72,'{\"_title\":\"System Thesauri\",\"node_ids\":\"4\",\"template_ids\":\"5\",\"user_group_ids\":null,\"menu\":\"8,61,5\"}','{\"fu\":[1],\"solr\":[]}'),

(73,'{\"_title\":\"Create menu rules in this folder\",\"node_ids\":60,\"menu\":62}','{\"fu\":[1],\"solr\":[]}'),

(74,'{\"_title\":\"link\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:15:55Z\",\"users\":{\"1\":1}}}'),

(75,'{\"_title\":\"Type\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:16:08Z\",\"users\":{\"1\":2}}}'),

(76,'{\"en\":\"Article\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":1}','{\"fu\":[1],\"solr\":{\"order\":1},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:17:46Z\",\"users\":{\"1\":3}}}'),

(77,'{\"en\":\"Document\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":2}','{\"fu\":[1],\"solr\":{\"order\":2},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:06Z\",\"users\":{\"1\":4}}}'),

(78,'{\"en\":\"Image\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":3}','{\"fu\":[1],\"solr\":{\"order\":3},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:24Z\",\"users\":{\"1\":5}}}'),

(79,'{\"en\":\"Sound\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":4}','{\"fu\":[1],\"solr\":{\"order\":4},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:18:42Z\",\"users\":{\"1\":6}}}'),

(80,'{\"en\":\"Video\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":5}','{\"fu\":[1],\"solr\":{\"order\":5},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:03Z\",\"users\":{\"1\":7}}}'),

(81,'{\"en\":\"Website\",\"iconCls\":\"icon-element\",\"visible\":1,\"order\":6}','{\"fu\":[1],\"solr\":{\"order\":6},\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:25Z\",\"users\":{\"1\":8}}}'),

(82,'{\"_title\":\"Tags\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:19:43Z\",\"users\":{\"1\":9}}}'),

(83,'{\"_title\":\"link\",\"en\":\"Link\",\"type\":\"object\",\"visible\":1,\"iconCls\":\"fa fa-external-link fa-fl\",\"title_template\":\"{url}\"}','{\"fu\":[1],\"solr\":{\"content\":\"link\\nLink\\nobject\\n1\\nfa fa-external-link fa-fl\\n{url}\\n\"},\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"11\"},\"time\":\"2016-06-09T13:53:47Z\"},\"wu\":[]}'),

(84,'{\"_title\":\"type\",\"en\":\"Type\",\"type\":\"_objects\",\"order\":1,\"cfg\":\"{\\n\\\"scope\\\": 75 \\n}\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:25:21Z\",\"users\":{\"1\":11}}}'),

(85,'{\"_title\":\"url\",\"en\":\"URL\",\"type\":\"varchar\",\"order\":2}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:25:59Z\",\"users\":{\"1\":12}}}'),

(86,'{\"_title\":\"description\",\"en\":\"Description\",\"type\":\"varchar\",\"order\":3}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-01T07:26:29Z\",\"users\":{\"1\":13}}}'),

(87,'{\"_title\":\"tags\",\"en\":\"Tags\",\"type\":\"_objects\",\"order\":4,\"cfg\":\"{\\n\\\"scope\\\": 82\\n,\\\"editor\\\": \\\"tagField\\\"\\n}\"}','{\"fu\":[1],\"solr\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":16},\"time\":\"2015-09-01T07:30:36Z\"}}'),

(88,'{\"_title\":\"Built-in\"}','{\"fu\":[],\"solr\":[],\"lastAction\":{\"type\":\"create\",\"time\":\"2015-09-02T13:45:53Z\",\"users\":{\"1\":17}}}'),

(89,'{\"_title\":\"Config\"}','{\"fu\":[],\"solr\":[]}'),

(90,'{\"_title\":\"Config\"}','{\"fu\":[],\"solr\":[]}'),

(91,'{\"_title\":\"Config int option\",\"en\":\"Config int option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"fa fa-gear fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"Config int option\\nConfig int option\\nconfig\\n1\\nfa fa-gear fa-fl\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"12\"},\"time\":\"2016-06-09T13:54:28Z\"}}'),

(92,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),

(93,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"int\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),

(94,'{\"_title\":\"Config varchar option\",\"en\":\"Config varchar option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"fa fa-gear fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"Config varchar option\\nConfig varchar option\\nconfig\\n1\\nfa fa-gear fa-fl\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"13\"},\"time\":\"2016-06-09T13:54:40Z\"}}'),

(95,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),

(96,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"varchar\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),

(97,'{\"_title\":\"Config text option\",\"en\":\"Config text option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"fa fa-gear fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"Config text option\\nConfig text option\\nconfig\\n1\\nfa fa-gear fa-fl\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"14\"},\"time\":\"2016-06-09T13:54:50Z\"}}'),

(98,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),

(99,'{\"name\":\"value\",\"en\":\"Value\",\"type\":\"text\",\"order\":2}','{\"fu\":[1],\"solr\":[]}'),

(100,'{\"_title\":\"Config json option\",\"en\":\"Config json option\",\"type\":\"config\",\"visible\":1,\"iconCls\":\"fa fa-gears fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"Config json option\\nConfig json option\\nconfig\\n1\\nfa fa-gears fa-fl\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"15\"},\"time\":\"2016-06-09T13:55:06Z\"}}'),

(101,'{\"name\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"fu\":[1],\"solr\":[]}'),

(102,'{\"en\":\"Value\",\"type\":\"text\",\"order\":2,\"cfg\":\"{\\n\\\"editor\\\":\\\"ace\\\",\\n\\\"format\\\":\\\"json\\\",\\n\\\"validator\\\":\\\"json\\\"\\n}\"}','{\"fu\":[1],\"solr\":{\"content\":\"Value\\ntext\\n2\\n{\\n\\\"editor\\\":\\\"ace\\\",\\n\\\"format\\\":\\\"json\\\",\\n\\\"validator\\\":\\\"json\\\"\\n}\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"7\"},\"time\":\"2016-04-29T08:00:26Z\"}}'),

(103,'{\"name\":\"order\",\"en\":\"Order\",\"type\":\"int\",\"order\":3,\"solr_column_name\":\"order\",\"cfg\":\"{\\\"indexed\\\":true}\"}','{\"fu\":[1],\"solr\":[]}'),

(104,'{\"_title\":\"project_name_en\",\"value\":\"CaseBox - Demo\"}','{\"fu\":[1],\"solr\":[]}'),

(105,'{\"_title\":\"templateIcons\",\"value\":\"\\nfa fa-arrow-circle-left fa-fl\\nfa fa-arrow-circle-o-left fa-fl\\nfa fa-arrow-circle-o-right fa-fl\\nfa fa-arrow-circle-right fa-fl\\nfa fa-arrow-left fa-fl\\nfa fa-arrow-right fa-fl\\nfa fa-book fa-fl\\nfa fa-bookmark fa-fl\\nfa fa-bookmark-o fa-fl\\nfa fa-briefcase fa-fl\\nfa fa-bug fa-fl\\nfa fa-building fa-fl\\nfa fa-building-o fa-fl\\nfa fa-calendar-o fa-fl\\nfa fa-camera fa-fl\\nfa fa-comment fa-fl\\nfa fa-comment-o fa-fl\\nfa fa-commenting fa-fl\\nfa fa-commenting-o fa-fl\\nfa fa-comments fa-fl\\nfa fa-comments-o fa-fl\\nfa fa-envelope fa-fl\\nfa fa-envelope-o fa-fl\\nfa fa-external-link fa-fl\\nfa fa-external-link-square  fa-fl\\nfa fa-file fa-fl\\nfa fa-file-archive-o fa-fl\\nfa fa-file-audio-o fa-fl\\nfa fa-file-code-o fa-fl\\nfa fa-file-excel-o fa-fl\\nfa fa-file-image-o fa-fl\\nfa fa-file-movie-o fa-fl\\nfa fa-file-o fa-fl\\nfa fa-file-pdf-o fa-fl\\nfa fa-file-photo-o fa-fl\\nfa fa-file-picture-o fa-fl\\nfa fa-file-powerpoint-o fa-fl\\nfa fa-file-sound-o fa-fl\\nfa fa-file-text fa-fl\\nfa fa-file-text-o fa-fl\\nfa fa-file-video-o fa-fl\\nfa fa-file-word-o fa-fl\\nfa fa-file-zip-o fa-fl\\nfa fa-files-o fa-fl\\nfa fa-film fa-fl\\nfa fa-flash fa-fl\\nfa fa-folder fa-fl\\nfa fa-folder-o fa-fl\\nfa fa-folder-open fa-fl\\nfa fa-folder-open-o fa-fl\\nfa fa-foursquare fa-fl\\nfa fa-gavel fa-fl\\nfa fa-gear fa-fl\\nfa fa-gears fa-fl\\nfa fa-info fa-fl\\nfa fa-info-circle fa-fl\\nfa fa-institution fa-fl\\nfa fa-link fa-fl\\nfa fa-print fa-fl\\nfa fa-stack-exchange fa-fl\\nfa fa-sticky-note fa-fl\\nfa fa-sticky-note-o fa-fl\\nfa fa-suitcase fa-fl\\nfa fa-tasks fa-fl\\nfa fa-university fa-fl\\nfa fa-unlink fa-fl\\nfa fa-user fa-fl\\nfa fa-user-md fa-fl\\nfa fa-user-plus fa-fl\\nfa fa-user-secret fa-fl\\nfa fa-user-times fa-fl\\nfa fa-users fa-fl\\nfa fa-warning fa-fl\\nfa fa-wpforms fa-fl\"}','{\"fu\":[1],\"solr\":{\"content\":\"templateIcons\\n\\nfa fa-arrow-circle-left fa-fl\\nfa fa-arrow-circle-o-left fa-fl\\nfa fa-arrow-circle-o-right fa-fl\\nfa fa-arrow-circle-right fa-fl\\nfa fa-arrow-left fa-fl\\nfa fa-arrow-right fa-fl\\nfa fa-book fa-fl\\nfa fa-bookmark fa-fl\\nfa fa-bookmark-o fa-fl\\nfa fa-briefcase fa-fl\\nfa fa-bug fa-fl\\nfa fa-building fa-fl\\nfa fa-building-o fa-fl\\nfa fa-calendar-o fa-fl\\nfa fa-camera fa-fl\\nfa fa-comment fa-fl\\nfa fa-comment-o fa-fl\\nfa fa-commenting fa-fl\\nfa fa-commenting-o fa-fl\\nfa fa-comments fa-fl\\nfa fa-comments-o fa-fl\\nfa fa-envelope fa-fl\\nfa fa-envelope-o fa-fl\\nfa fa-external-link fa-fl\\nfa fa-external-link-square  fa-fl\\nfa fa-file fa-fl\\nfa fa-file-archive-o fa-fl\\nfa fa-file-audio-o fa-fl\\nfa fa-file-code-o fa-fl\\nfa fa-file-excel-o fa-fl\\nfa fa-file-image-o fa-fl\\nfa fa-file-movie-o fa-fl\\nfa fa-file-o fa-fl\\nfa fa-file-pdf-o fa-fl\\nfa fa-file-photo-o fa-fl\\nfa fa-file-picture-o fa-fl\\nfa fa-file-powerpoint-o fa-fl\\nfa fa-file-sound-o fa-fl\\nfa fa-file-text fa-fl\\nfa fa-file-text-o fa-fl\\nfa fa-file-video-o fa-fl\\nfa fa-file-word-o fa-fl\\nfa fa-file-zip-o fa-fl\\nfa fa-files-o fa-fl\\nfa fa-film fa-fl\\nfa fa-flash fa-fl\\nfa fa-folder fa-fl\\nfa fa-folder-o fa-fl\\nfa fa-folder-open fa-fl\\nfa fa-folder-open-o fa-fl\\nfa fa-foursquare fa-fl\\nfa fa-gavel fa-fl\\nfa fa-gear fa-fl\\nfa fa-gears fa-fl\\nfa fa-info fa-fl\\nfa fa-info-circle fa-fl\\nfa fa-institution fa-fl\\nfa fa-link fa-fl\\nfa fa-print fa-fl\\nfa fa-stack-exchange fa-fl\\nfa fa-sticky-note fa-fl\\nfa fa-sticky-note-o fa-fl\\nfa fa-suitcase fa-fl\\nfa fa-tasks fa-fl\\nfa fa-university fa-fl\\nfa fa-unlink fa-fl\\nfa fa-user fa-fl\\nfa fa-user-md fa-fl\\nfa fa-user-plus fa-fl\\nfa fa-user-secret fa-fl\\nfa fa-user-times fa-fl\\nfa fa-users fa-fl\\nfa fa-warning fa-fl\\nfa fa-wpforms fa-fl\\n\"},\"wu\":[],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"1\"},\"time\":\"2016-06-09T13:48:36Z\"}}'),

(106,'{\"_title\":\"folder_templates\",\"value\":\"5,11,100\"}','{\"fu\":[1],\"solr\":[]}'),

(107,'{\"_title\":\"default_folder_template\",\"value\":\"5\"}','{\"fu\":[1],\"solr\":[]}'),

(108,'{\"_title\":\"default_file_template\",\"value\":\"6\"}','{\"fu\":[1],\"solr\":[]}'),

(109,'{\"_title\":\"default_task_template\",\"value\":\"7\"}','{\"fu\":[1],\"solr\":[]}'),

(110,'{\"_title\":\"default_language\",\"value\":\"en\"}','{\"fu\":[1],\"solr\":[]}'),

(111,'{\"_title\":\"languages\",\"value\":\"en\"}','{\"fu\":[1],\"solr\":[]}'),

(112,'{\"_title\":\"object_type_plugins\",\"value\":\"{\\r\\n  \\\"object\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"tasks\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"case\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"tasks\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"task\\\": [\\\"objectProperties\\\", \\\"files\\\", \\\"contentItems\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n  ,\\\"file\\\": [\\\"thumb\\\", \\\"meta\\\", \\\"versions\\\", \\\"tasks\\\", \\\"comments\\\", \\\"systemProperties\\\"]\\r\\n}\"}','{\"fu\":[1],\"solr\":[]}'),

(113,'{\"_title\":\"treeNodes\",\"value\":\"\"}','{\"fu\":[1],\"solr\":[]}'),

(114,'{\"_title\":\"Tasks\",\"value\":\"{\\n    \\\"pid\\\": 1\\n}\",\"order\":1}','{\"fu\":[1],\"solr\":{\"order\":1}}'),

(115,'{\"_title\":\"Dbnode\",\"value\":\"[]\",\"order\":2}','{\"fu\":[1],\"solr\":{\"order\":2}}'),

(116,'{\"_title\":\"RecycleBin\",\"value\":\"{\\r\\n    \\\"pid\\\": \\\"1\\\",\\r\\n    \\\"facets\\\": [\\r\\n        \\\"did\\\"\\r\\n    ],\\r\\n    \\\"DC\\\": {\\r\\n        \\\"nid\\\": {}\\r\\n        ,\\\"name\\\": {}\\r\\n        ,\\\"cid\\\": {}\\r\\n        ,\\\"ddate\\\": {\\r\\n            \\\"solr_column_name\\\": \\\"ddate\\\"\\r\\n        }\\r\\n    }\\r\\n}\",\"order\":3}','{\"fu\":[1],\"solr\":{\"order\":3},\"wu\":[1],\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":1},\"time\":\"2015-11-25T13:52:47Z\"}}'),

(117,'{\"_title\":\"Create config options rule\",\"node_ids\":90,\"menu\":\"91,94,97,100\"}','{\"fu\":[1],\"solr\":[]}'),

(118,'{\"_title\":\"files\",\"value\":\"{\\r\\n  \\\"max_versions\\\": \\\"*:1;php,odt,doc,docx,xls,xlsx:20;pdf:5;png,gif,jpg,jpeg,tif,tiff:2;\\\"\\r\\n\\r\\n  ,\\\"edit\\\" : {\\r\\n    \\\"text\\\": \\\"txt,php,js,xml,csv\\\"\\r\\n    ,\\\"html\\\": \\\"html,htm\\\"\\r\\n    ,\\\"webdav\\\": \\\"doc,docx,ppt,dot,dotx,xls,xlsm,xltx,ppt,pot,pps,pptx,odt,ott,odm,ods,odg,otg,odp,odf,odb\\\"\\r\\n  }\\r\\n\\r\\n  ,\\\"webdav_url\\\": \\\"https:\\/\\/webdav.host.com\\/{core_name}\\/edit-{node_id}\\/{name}\\\"\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"files\\n{\\r\\n  \\\"max_versions\\\": \\\"*:1;php,odt,doc,docx,xls,xlsx:20;pdf:5;png,gif,jpg,jpeg,tif,tiff:2;\\\"\\r\\n\\r\\n  ,\\\"edit\\\" : {\\r\\n    \\\"text\\\": \\\"txt,php,js,xml,csv\\\"\\r\\n    ,\\\"html\\\": \\\"html,htm\\\"\\r\\n    ,\\\"webdav\\\": \\\"doc,docx,ppt,dot,dotx,xls,xlsm,xltx,ppt,pot,pps,pptx,odt,ott,odm,ods,odg,otg,odp,odf,odb\\\"\\r\\n  }\\r\\n\\r\\n  ,\\\"webdav_url\\\": \\\"https:\\/\\/webdav.host.com\\/{core_name}\\/edit-{node_id}\\/{name}\\\"\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:53:55Z\",\"users\":{\"1\":\"1\"}}}'),

(119,'{\"_title\":\"timezone\",\"value\":\"UTC\"}','{\"wu\":[],\"solr\":{\"content\":\"timezone\\nUTC\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:55:28Z\",\"users\":{\"1\":\"2\"}}}'),

(120,'{\"_title\":\"language_en\",\"value\":\"{\\r\\n\\\"name\\\": \\\"English\\\"\\r\\n,\\\"locale\\\": \\\"en_US\\\"\\r\\n,\\\"long_date_format\\\": \\\"%F %j, %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%m\\/%d\\/%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"language_en\\n{\\r\\n\\\"name\\\": \\\"English\\\"\\r\\n,\\\"locale\\\": \\\"en_US\\\"\\r\\n,\\\"long_date_format\\\": \\\"%F %j, %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%m\\/%d\\/%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:56:08Z\",\"users\":{\"1\":\"3\"}}}'),

(121,'{\"_title\":\"language_fr\",\"value\":\"{\\r\\n\\\"name\\\": \\\"French\\\"\\r\\n,\\\"locale\\\": \\\"fr_FR\\\"\\r\\n,\\\"long_date_format\\\": \\\"%j %F %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%d.%m.%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\\r\\n\"}','{\"wu\":[],\"solr\":{\"content\":\"language_fr\\n{\\r\\n\\\"name\\\": \\\"French\\\"\\r\\n,\\\"locale\\\": \\\"fr_FR\\\"\\r\\n,\\\"long_date_format\\\": \\\"%j %F %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%d.%m.%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\\r\\n\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:56:40Z\",\"users\":{\"1\":\"4\"}}}'),

(122,'{\"_title\":\"language_ru\",\"value\":\"{\\r\\n\\\"name\\\": \\\"Русский\\\"\\r\\n,\\\"locale\\\": \\\"ru_RU\\\"\\r\\n,\\\"long_date_format\\\": \\\"%j %F %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%d.%m.%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"language_ru\\n{\\r\\n\\\"name\\\": \\\"Русский\\\"\\r\\n,\\\"locale\\\": \\\"ru_RU\\\"\\r\\n,\\\"long_date_format\\\": \\\"%j %F %Y\\\"\\r\\n,\\\"short_date_format\\\": \\\"%d.%m.%Y\\\"\\r\\n,\\\"time_format\\\": \\\"%H:%i\\\"\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:57:06Z\",\"users\":{\"1\":\"5\"}}}'),

(123,'{\"_title\":\"default_facet_configs\",\"value\":\"{\\r\\n  \\\"template_type\\\": {\\r\\n    \\\"title\\\": \\\"[Type]\\\"\\r\\n    ,\\\"type\\\": \\\"objectTypes\\\"\\r\\n  }\\r\\n  ,\\\"template\\\": {\\r\\n    \\\"title\\\": \\\"[Template]\\\"\\r\\n    ,\\\"field\\\": \\\"template_id\\\"\\r\\n    ,\\\"type\\\": \\\"objects\\\"\\r\\n  }\\r\\n  ,\\\"creator\\\": {\\r\\n    \\\"title\\\": \\\"[Creator]\\\"\\r\\n    ,\\\"field\\\": \\\"cid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"owner\\\": {\\r\\n    \\\"title\\\": \\\"[Owner]\\\"\\r\\n    ,\\\"field\\\": \\\"oid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"updater\\\": {\\r\\n    \\\"title\\\": \\\"Updater\\\"\\r\\n    ,\\\"field\\\": \\\"uid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"date\\\": {\\r\\n    \\\"title\\\": \\\"[Date]\\\"\\r\\n    ,\\\"facet\\\": \\\"query\\\"\\r\\n    ,\\\"type\\\": \\\"dates\\\"\\r\\n    ,\\\"manualPeriod\\\": true\\r\\n    ,\\\"queries\\\": [\\r\\n      \\\"today\\\"\\r\\n      ,\\\"yesterday\\\"\\r\\n      ,\\\"week\\\"\\r\\n      ,\\\"month\\\"\\r\\n    ]\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n  ,\\\"date_end\\\": {\\r\\n    \\\"title\\\": \\\"End date\\\"\\r\\n    ,\\\"facet\\\": \\\"query\\\"\\r\\n    ,\\\"type\\\": \\\"dates\\\"\\r\\n    ,\\\"queries\\\": [\\r\\n      \\\"today\\\"\\r\\n      ,\\\"week\\\"\\r\\n      ,\\\"next7days\\\"\\r\\n      ,\\\"next31days\\\"\\r\\n      ,\\\"month\\\"\\r\\n    ]\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n  ,\\\"status\\\": {\\r\\n    \\\"title\\\": \\\"[Status]\\\"\\r\\n    ,\\\"type\\\": \\\"objects\\\"\\r\\n }\\r\\n  ,\\\"task_status\\\": {\\r\\n    \\\"title\\\": \\\"[Status]\\\"\\r\\n    ,\\\"type\\\": \\\"taskStatuses\\\"\\r\\n }\\r\\n  ,\\\"assigned\\\": {\\r\\n    \\\"title\\\": \\\"[TaskAssigned]\\\"\\r\\n    ,\\\"field\\\": \\\"task_u_assignee\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"default_facet_configs\\n{\\r\\n  \\\"template_type\\\": {\\r\\n    \\\"title\\\": \\\"[Type]\\\"\\r\\n    ,\\\"type\\\": \\\"objectTypes\\\"\\r\\n  }\\r\\n  ,\\\"template\\\": {\\r\\n    \\\"title\\\": \\\"[Template]\\\"\\r\\n    ,\\\"field\\\": \\\"template_id\\\"\\r\\n    ,\\\"type\\\": \\\"objects\\\"\\r\\n  }\\r\\n  ,\\\"creator\\\": {\\r\\n    \\\"title\\\": \\\"[Creator]\\\"\\r\\n    ,\\\"field\\\": \\\"cid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"owner\\\": {\\r\\n    \\\"title\\\": \\\"[Owner]\\\"\\r\\n    ,\\\"field\\\": \\\"oid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"updater\\\": {\\r\\n    \\\"title\\\": \\\"Updater\\\"\\r\\n    ,\\\"field\\\": \\\"uid\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n  }\\r\\n  ,\\\"date\\\": {\\r\\n    \\\"title\\\": \\\"[Date]\\\"\\r\\n    ,\\\"facet\\\": \\\"query\\\"\\r\\n    ,\\\"type\\\": \\\"dates\\\"\\r\\n    ,\\\"manualPeriod\\\": true\\r\\n    ,\\\"queries\\\": [\\r\\n      \\\"today\\\"\\r\\n      ,\\\"yesterday\\\"\\r\\n      ,\\\"week\\\"\\r\\n      ,\\\"month\\\"\\r\\n    ]\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n  ,\\\"date_end\\\": {\\r\\n    \\\"title\\\": \\\"End date\\\"\\r\\n    ,\\\"facet\\\": \\\"query\\\"\\r\\n    ,\\\"type\\\": \\\"dates\\\"\\r\\n    ,\\\"queries\\\": [\\r\\n      \\\"today\\\"\\r\\n      ,\\\"week\\\"\\r\\n      ,\\\"next7days\\\"\\r\\n      ,\\\"next31days\\\"\\r\\n      ,\\\"month\\\"\\r\\n    ]\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n  ,\\\"status\\\": {\\r\\n    \\\"title\\\": \\\"[Status]\\\"\\r\\n    ,\\\"type\\\": \\\"objects\\\"\\r\\n }\\r\\n  ,\\\"task_status\\\": {\\r\\n    \\\"title\\\": \\\"[Status]\\\"\\r\\n    ,\\\"type\\\": \\\"taskStatuses\\\"\\r\\n }\\r\\n  ,\\\"assigned\\\": {\\r\\n    \\\"title\\\": \\\"[TaskAssigned]\\\"\\r\\n    ,\\\"field\\\": \\\"task_u_assignee\\\"\\r\\n    ,\\\"type\\\": \\\"users\\\"\\r\\n    ,\\\"boolMode\\\": true\\r\\n  }\\r\\n\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T07:59:21Z\",\"users\":{\"1\":\"6\"}}}'),

(124,'{\"_title\":\"node_facets\",\"value\":\"{\\r\\n\\\"1\\\" : [\\r\\n  \\\"template_type\\\"\\r\\n  ,\\\"creator\\\"\\r\\n  ,\\\"template\\\"\\r\\n  ,\\\"date\\\"\\r\\n  ,\\\"status\\\"\\r\\n  ,\\\"assigned\\\"\\r\\n]\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"node_facets\\n{\\r\\n\\\"1\\\" : [\\r\\n  \\\"template_type\\\"\\r\\n  ,\\\"creator\\\"\\r\\n  ,\\\"template\\\"\\r\\n  ,\\\"date\\\"\\r\\n  ,\\\"status\\\"\\r\\n  ,\\\"assigned\\\"\\r\\n]\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:01:22Z\",\"users\":{\"1\":\"8\"}}}'),

(125,'{\"_title\":\"default_object_plugins\",\"value\":\"{\\r\\n  \\\"objectProperties\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!context\\\": \\\"window\\\"\\r\\n      ,\\\"!template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 0\\r\\n  }\\r\\n  ,\\\"files\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"object,search,case,task\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 2\\r\\n  }\\r\\n  ,\\\"tasks\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"object,search,case,task\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 3\\r\\n  }\\r\\n  ,\\\"contentItems\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!template_type\\\": \\\"file,time_tracking\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 4\\r\\n  }\\r\\n  ,\\\"thumb\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!context\\\": \\\"window\\\"\\r\\n      ,\\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 5\\r\\n  }\\r\\n  ,\\\"currentVersion\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"context\\\": \\\"window\\\"\\r\\n      ,\\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 6\\r\\n  }\\r\\n  ,\\\"versions\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 7\\r\\n  }\\r\\n  ,\\\"meta\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 8\\r\\n  }\\r\\n  ,\\\"comments\\\": {\\r\\n    \\\"order\\\": 9\\r\\n    ,\\\"visibility\\\": {\\r\\n      \\\"!template_type\\\": \\\"time_tracking\\\"\\r\\n    }\\r\\n\\r\\n  }\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"default_object_plugins\\n{\\r\\n  \\\"objectProperties\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!context\\\": \\\"window\\\"\\r\\n      ,\\\"!template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 0\\r\\n  }\\r\\n  ,\\\"files\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"object,search,case,task\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 2\\r\\n  }\\r\\n  ,\\\"tasks\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"object,search,case,task\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 3\\r\\n  }\\r\\n  ,\\\"contentItems\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!template_type\\\": \\\"file,time_tracking\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 4\\r\\n  }\\r\\n  ,\\\"thumb\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"!context\\\": \\\"window\\\"\\r\\n      ,\\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 5\\r\\n  }\\r\\n  ,\\\"currentVersion\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"context\\\": \\\"window\\\"\\r\\n      ,\\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 6\\r\\n  }\\r\\n  ,\\\"versions\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 7\\r\\n  }\\r\\n  ,\\\"meta\\\": {\\r\\n    \\\"visibility\\\": {\\r\\n      \\\"template_type\\\": \\\"file\\\"\\r\\n    }\\r\\n    ,\\\"order\\\": 8\\r\\n  }\\r\\n  ,\\\"comments\\\": {\\r\\n    \\\"order\\\": 9\\r\\n    ,\\\"visibility\\\": {\\r\\n      \\\"!template_type\\\": \\\"time_tracking\\\"\\r\\n    }\\r\\n\\r\\n  }\\r\\n}\\n\"},\"lastAction\":{\"type\":\"update\",\"users\":{\"1\":\"13\"},\"time\":\"2016-04-29T08:15:53Z\"}}'),

(126,'{\"_title\":\"images_display_size\",\"value\":512000}','{\"wu\":[],\"solr\":{\"content\":\"images_display_size\\n512000\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:11:54Z\",\"users\":{\"1\":\"10\"}}}'),

(127,'{\"_title\":\"default_DC\",\"value\":\"{\\r\\n\\\"nid\\\": {}\\r\\n,\\\"name\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"name\\\"\\r\\n}\\r\\n,\\\"date\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"date\\\"\\r\\n}\\r\\n,\\\"size\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"size\\\"\\r\\n}\\r\\n,\\\"cid\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"cid\\\"\\r\\n}\\r\\n,\\\"oid\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"oid\\\"\\r\\n}\\r\\n,\\\"cdate\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"cdate\\\"\\r\\n}\\r\\n,\\\"udate\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"udate\\\"\\r\\n}\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"default_DC\\n{\\r\\n\\\"nid\\\": {}\\r\\n,\\\"name\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"name\\\"\\r\\n}\\r\\n,\\\"date\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"date\\\"\\r\\n}\\r\\n,\\\"size\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"size\\\"\\r\\n}\\r\\n,\\\"cid\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"cid\\\"\\r\\n}\\r\\n,\\\"oid\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"oid\\\"\\r\\n}\\r\\n,\\\"cdate\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"cdate\\\"\\r\\n}\\r\\n,\\\"udate\\\": {\\r\\n  \\\"solr_column_name\\\": \\\"udate\\\"\\r\\n}\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:12:21Z\",\"users\":{\"1\":\"11\"}}}'),

(128,'{\"_title\":\"default_availableViews\",\"value\":\"grid,charts,pivot,activityStream\"}','{\"wu\":[],\"solr\":{\"content\":\"default_availableViews\\ngrid,charts,pivot,activityStream\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:14:13Z\",\"users\":{\"1\":\"12\"}}}'),

(129,'{\"_title\":\"DCConfigs\"}','{\"wu\":[],\"solr\":{\"content\":\"DCConfigs\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:17:58Z\",\"users\":{\"1\":\"14\"}}}'),

(130,'{\"_title\":\"dc_tasks\",\"value\":\"{\\r\\n    \\\"nid\\\":[]\\r\\n    ,\\\"name\\\":[]\\r\\n    ,\\\"importance\\\":{\\\"solr_column_name\\\":\\\"task_importance\\\"}\\r\\n    ,\\\"order\\\":{\\r\\n        \\\"solr_column_name\\\":\\\"task_order\\\"\\r\\n        ,\\\"sortType\\\":\\\"asInt\\\"\\r\\n        ,\\\"align\\\":\\\"center\\\"\\r\\n        ,\\\"columnWidth\\\":\\\"10\\\"\\r\\n    }\\r\\n    ,\\\"time_estimated\\\":{\\r\\n        \\\"width\\\":\\\"20px\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"phase\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_phase\\\"\\r\\n    }\\r\\n    ,\\\"project\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_projects\\\"\\r\\n    }\\r\\n    ,\\\"cid\\\":[]\\r\\n    ,\\\"assigned\\\":[]\\r\\n    ,\\\"comment_user_id\\\":[]\\r\\n    ,\\\"comment_date\\\":[]\\r\\n    ,\\\"cdate\\\":[]\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"dc_tasks\\n{\\r\\n    \\\"nid\\\":[]\\r\\n    ,\\\"name\\\":[]\\r\\n    ,\\\"importance\\\":{\\\"solr_column_name\\\":\\\"task_importance\\\"}\\r\\n    ,\\\"order\\\":{\\r\\n        \\\"solr_column_name\\\":\\\"task_order\\\"\\r\\n        ,\\\"sortType\\\":\\\"asInt\\\"\\r\\n        ,\\\"align\\\":\\\"center\\\"\\r\\n        ,\\\"columnWidth\\\":\\\"10\\\"\\r\\n    }\\r\\n    ,\\\"time_estimated\\\":{\\r\\n        \\\"width\\\":\\\"20px\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"phase\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_phase\\\"\\r\\n    }\\r\\n    ,\\\"project\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_projects\\\"\\r\\n    }\\r\\n    ,\\\"cid\\\":[]\\r\\n    ,\\\"assigned\\\":[]\\r\\n    ,\\\"comment_user_id\\\":[]\\r\\n    ,\\\"comment_date\\\":[]\\r\\n    ,\\\"cdate\\\":[]\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:18:25Z\",\"users\":{\"1\":\"15\"}}}'),

(131,'{\"_title\":\"dc_tasks_closed\",\"value\":\"{\\r\\n    \\\"nid\\\":[]\\r\\n    ,\\\"name\\\":[]\\r\\n    ,\\\"importance\\\":{\\\"solr_column_name\\\":\\\"task_importance\\\"}\\r\\n    ,\\\"order\\\":{\\\"solr_column_name\\\":\\\"task_order\\\"\\r\\n        ,\\\"sortType\\\":\\\"asInt\\\"\\r\\n        ,\\\"align\\\":\\\"center\\\"\\r\\n        ,\\\"columnWidth\\\":\\\"10\\\"\\r\\n    }\\r\\n    ,\\\"project\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_projects\\\"\\r\\n    }    \\r\\n    ,\\\"time_completed\\\":{\\r\\n        \\\"columnWidth\\\":\\\"20\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"time_estimated\\\":{\\r\\n        \\\"width\\\":\\\"20px\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"task_d_closed\\\":{\\r\\n        \\\"solr_column_name\\\":\\\"task_d_closed\\\"\\r\\n        ,\\\"xtype\\\":\\\"datecolumn\\\"\\r\\n        ,\\\"format\\\":\\\"Y-m-d\\\"\\r\\n        ,\\\"title\\\":\\\"Closed date\\\"\\r\\n    }\\r\\n    ,\\\"cid\\\":[]\\r\\n    ,\\\"cdate\\\":[]\\r\\n    ,\\\"assigned\\\":[]\\r\\n    ,\\\"comment_user_id\\\":[]\\r\\n    ,\\\"comment_date\\\":[]\\r\\n}\"}','{\"wu\":[],\"solr\":{\"content\":\"dc_tasks_closed\\n{\\r\\n    \\\"nid\\\":[]\\r\\n    ,\\\"name\\\":[]\\r\\n    ,\\\"importance\\\":{\\\"solr_column_name\\\":\\\"task_importance\\\"}\\r\\n    ,\\\"order\\\":{\\\"solr_column_name\\\":\\\"task_order\\\"\\r\\n        ,\\\"sortType\\\":\\\"asInt\\\"\\r\\n        ,\\\"align\\\":\\\"center\\\"\\r\\n        ,\\\"columnWidth\\\":\\\"10\\\"\\r\\n    }\\r\\n    ,\\\"project\\\": {\\r\\n        \\\"solr_column_name\\\": \\\"task_projects\\\"\\r\\n    }    \\r\\n    ,\\\"time_completed\\\":{\\r\\n        \\\"columnWidth\\\":\\\"20\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"time_estimated\\\":{\\r\\n        \\\"width\\\":\\\"20px\\\"\\r\\n        ,\\\"format\\\":\\\"H:i\\\"\\r\\n    }\\r\\n    ,\\\"task_d_closed\\\":{\\r\\n        \\\"solr_column_name\\\":\\\"task_d_closed\\\"\\r\\n        ,\\\"xtype\\\":\\\"datecolumn\\\"\\r\\n        ,\\\"format\\\":\\\"Y-m-d\\\"\\r\\n        ,\\\"title\\\":\\\"Closed date\\\"\\r\\n    }\\r\\n    ,\\\"cid\\\":[]\\r\\n    ,\\\"cdate\\\":[]\\r\\n    ,\\\"assigned\\\":[]\\r\\n    ,\\\"comment_user_id\\\":[]\\r\\n    ,\\\"comment_date\\\":[]\\r\\n}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:20:59Z\",\"users\":{\"1\":\"16\"}}}'),

(132,'{\"_title\":\"geoMapping\",\"value\":\"false\"}','{\"wu\":[],\"solr\":{\"content\":\"geoMapping\\nfalse\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-04-29T08:22:54Z\",\"users\":{\"1\":\"17\"}}}'),

(133,'{\"_title\":\"Security\"}','{\"wu\":[],\"solr\":{\"content\":\"Security\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(134,'{\"_title\":\"Users\"}','{\"wu\":[],\"solr\":{\"content\":\"Users\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(135,'{\"_title\":\"Groups\"}','{\"wu\":[],\"solr\":{\"content\":\"Groups\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(136,'{\"_title\":\"_title\",\"en\":\"Username\",\"type\":\"varchar\",\"order\":1}','{\"wu\":[],\"solr\":{\"content\":\"_title\\nUsername\\nvarchar\\n1\\n\",\"order\":1},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(137,'{\"_title\":\"country\",\"en\":\"Country\",\"type\":\"combo\",\"cfg\":\"{\\\"source\\\":\\\"countries\\\"}\",\"order\":20}','{\"wu\":[],\"solr\":{\"content\":\"country\\nCountry\\ncombo\\n20\\n{\\\"source\\\":\\\"countries\\\"}\\n\",\"order\":20},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(138,'{\"_title\":\"timezone\",\"en\":\"Timezone\",\"type\":\"combo\",\"cfg\":\"{\\\"source\\\":\\\"timezones\\\"}\",\"order\":35}','{\"wu\":[],\"solr\":{\"content\":\"timezone\\nTimezone\\ncombo\\n35\\n{\\\"source\\\":\\\"timezones\\\"}\\n\",\"order\":35},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(139,'{\"_title\":\"groups\",\"en\":\"Groups\",\"type\":\"_objects\",\"cfg\":\"{\\\"editor\\\":\\\"form\\\", \\\"scope\\\": \\\"135\\\"}\",\"order\":45}','{\"wu\":[],\"solr\":{\"content\":\"groups\\nGroups\\n_objects\\n45\\n{\\\"editor\\\":\\\"form\\\", \\\"scope\\\": \\\"135\\\"}\\n\",\"order\":45},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(140,'{\"_title\":\"group\",\"en\":\"Group\",\"type\":\"group\",\"iconCls\":\"fa fa-group fa-fl\",\"title_template\":\"{en}\"}','{\"wu\":[],\"solr\":{\"content\":\"group\\nGroup\\ngroup\\nfa fa-group fa-fl\\n{en}\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(141,'{\"_title\":\"_title\",\"en\":\"Name\",\"type\":\"varchar\",\"order\":1}','{\"wu\":[],\"solr\":{\"content\":\"_title\\nName\\nvarchar\\n1\\n\",\"order\":1},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(142,'{\"_title\":\"en\",\"en\":\"Name (en)\",\"type\":\"varchar\",\"order\":2}','{\"wu\":[],\"solr\":{\"content\":\"en\\nName (en)\\nvarchar\\n2\\n\",\"order\":2},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(143,'{\"_title\":\"Groups folder rule\",\"menu\":\"140\",\"node_ids\":\"135\"}','{\"wu\":[],\"solr\":{\"content\":\"Groups folder rule\\n135\\n140\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(144,'{\"_title\":\"Users folder rule\",\"menu\":\"10\",\"node_ids\":\"134\"}','{\"wu\":[],\"solr\":{\"content\":\"Users folder rule\\n134\\n10\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(145,'{\"_title\":\"everyone\",\"en\":\"Everyone\"}','{\"wu\":[],\"solr\":{\"content\":\"everyone\\nEveryone\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}'),

(146,'{\"en\":\"Full name en\",\"initials\":\"initals\",\"sex\":\"m\",\"room\":\"12\",\"email\":\"anemail@gmai.com\",\"phone\":\"+331111111\",\"language_id\":1,\"short_date_format\":\"d.m.Y\",\"description\":\"descr\",\"_title\":\"root\"}','{\"wu\":[],\"solr\":{\"content\":\"Full name en\\ninitals\\nm\\nanemail@gmai.com\\n+331111111\\n1\\nd.m.Y\\ndescr\\n\"},\"lastAction\":{\"type\":\"create\",\"time\":\"2016-06-16T09:15:11Z\",\"users\":{\"\":null}}}');

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
  `type` enum('case','comment','config','email','field','file','group','menu','object','search','shortcut','task','template','user') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `order` int(11) unsigned DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `iconCls` varchar(50) DEFAULT NULL,
  `cfg` text,
  `title_template` text,
  PRIMARY KEY (`id`),
  KEY `FK_templates__pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8;

/*Data for the table `templates` */

insert  into `templates`(`id`,`pid`,`type`,`name`,`order`,`visible`,`iconCls`,`cfg`,`title_template`) values

(5,88,'object','folder',5,1,'fa fa-folder fa-fl','{\"createMethod\":\"inline\",\n\n  \"object_plugins\":\n      [\"comments\",\n       \"systemProperties\"\n      ]\n\n}','{name}'),

(6,88,'file','file_template',6,1,'fa fa-file fa-fl',NULL,'{name}'),

(7,88,'task','task',3,1,'fa fa-calendar-o fa-fl',NULL,'{name}'),

(8,88,'object','Thesauri Item',0,1,'fa fa-sticky-note fa-fl',NULL,'{en}'),

(9,88,'comment','Comment',0,1,'fa fa-comment fa-fl','{\n  \"systemType\": 2\n}',NULL),

(10,88,'user','User',1,1,'fa fa-user fa-fl','{\"files\":\"1\",\"main_file\":\"1\"}',NULL),

(11,88,'template','Template',0,1,'fa fa-file-code-o fa-fl','{\n\"DC\": {\n  \"nid\": {},\n  \"name\": {},\n  \"type\": {},\n  \"cfg\": {},\n  \"order\": {\n     \"sortType\": \"asInt\"\n     ,\"solr_column_name\": \"order\"\n  }\n}\n}',NULL),

(12,88,'field','Field',0,1,'fa fa-foursquare fa-fl','[]',NULL),

(58,88,'shortcut','shortcut',0,1,'fa fa-external-link-square  fa-fl',NULL,NULL),

(61,59,'object','- Menu separator -',0,0,NULL,NULL,NULL),

(62,59,'menu','Menu rule',0,0,NULL,NULL,NULL),

(83,88,'object','link',0,1,'fa fa-external-link fa-fl',NULL,'{url}'),

(91,89,'config','Config int option',0,1,'fa fa-gear fa-fl',NULL,NULL),

(94,89,'config','Config varchar option',0,1,'fa fa-gear fa-fl',NULL,NULL),

(97,89,'config','Config text option',0,1,'fa fa-gear fa-fl',NULL,NULL),

(100,89,'config','Config json option',0,1,'fa fa-gears fa-fl',NULL,NULL),

(140,88,'group','group',0,0,'fa fa-group fa-fl',NULL,'{en}');

/*Table structure for table `templates_structure` */

DROP TABLE IF EXISTS `templates_structure`;

CREATE TABLE `templates_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned DEFAULT NULL,
  `template_id` int(11) unsigned NOT NULL,
  `tag` varchar(30) DEFAULT NULL,
  `level` smallint(6) unsigned DEFAULT '0',
  `name` varchar(1000) NOT NULL,
  `type` varchar(30) DEFAULT NULL COMMENT 'varchar,date,time,int,bool,text,combo,popup_list',
  `order` smallint(6) unsigned DEFAULT '0',
  `cfg` text,
  `solr_column_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `templates_structure_pid` (`pid`),
  KEY `templates_structure_template_id` (`template_id`),
  KEY `idx_templates_structure_type` (`type`),
  CONSTRAINT `FK_templates_structure__template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8;

/*Data for the table `templates_structure` */

insert  into `templates_structure`(`id`,`pid`,`template_id`,`tag`,`level`,`name`,`type`,`order`,`cfg`,`solr_column_name`) values

(13,10,10,'f',0,'en','varchar',2,NULL,NULL),

(14,10,10,'f',0,'initials','varchar',5,NULL,NULL),

(15,10,10,'f',0,'sex','combo',10,'{\"source\":\"sex\"}',NULL),

(17,10,10,'f',0,'email','varchar',15,'{\"validator\":\"email\"}',NULL),

(18,10,10,'f',0,'language_id','combo',30,'{\"source\":\"languages\", \"required\": true}',NULL),

(19,10,10,'f',0,'short_date_format','combo',40,'{\"source\":\"shortDateFormats\"}',NULL),

(20,10,10,'f',0,'description','varchar',50,NULL,NULL),

(22,10,10,'f',0,'phone','varchar',25,'{\"maxInstances\":\"10\"}',NULL),

(24,6,6,'f',0,'program','_objects',1,'{\"source\":\"tree\",\"multiValued\":true,\"autoLoad\":true,\"editor\":\"form\",\"renderer\":\"listGreenIcons\",\"faceting\":true,\"scope\":24265,\"oldThesauriId\":\"715\"}',NULL),

(25,12,12,NULL,0,'_title','varchar',NULL,'{\"showIn\":\"top\"}',NULL),

(26,12,12,NULL,0,'type','_fieldTypesCombo',5,'[]',NULL),

(27,12,12,NULL,0,'order','field',6,'{\n  \"indexed\": true\n}','order'),

(28,12,12,NULL,0,'cfg','memo',7,'{\"height\":100}',NULL),

(29,12,12,NULL,0,'solr_column_name','varchar',8,'[]',NULL),

(30,12,12,NULL,0,'en','varchar',1,'[]',NULL),

(31,11,11,NULL,0,'_title','varchar',NULL,'{\"showIn\":\"top\",\"rea-dOnly\":true}',NULL),

(32,11,11,NULL,0,'type','_templateTypesCombo',5,'[]',NULL),

(33,11,11,NULL,0,'visible','checkbox',6,'{\"showIn\":\"top\"}',NULL),

(34,11,11,NULL,0,'iconCls','iconcombo',7,'[]',NULL),

(35,11,11,NULL,0,'cfg','text',8,'{\"height\":100}',NULL),

(36,11,11,NULL,0,'title_template','text',9,'{\"height\":50}',NULL),

(37,11,11,NULL,0,'info_template','text',10,'{\"height\":50}',NULL),

(38,11,11,NULL,0,'en','varchar',1,'[]',NULL),

(39,8,8,NULL,0,'iconCls','iconcombo',5,NULL,NULL),

(40,8,8,NULL,0,'visible','checkbox',6,NULL,NULL),

(41,8,8,NULL,0,'order','int',7,'{\n\"indexed\": true\n}','order'),

(42,8,8,NULL,0,'en','varchar',0,'{\"showIn\":\"top\"}',NULL),

(44,7,7,NULL,0,'_title','varchar',1,'{\n\"required\": true\n,\"hidePreview\": true\n}',NULL),

(45,7,7,NULL,0,'assigned','_objects',7,'{\n  \"editor\": \"form\"\n  ,\"source\": \"users\"\n  ,\"renderer\": \"listObjIcons\"\n  ,\"autoLoad\": true\n  ,\"multiValued\": true\n  ,\"hidePreview\": true\n}',NULL),

(46,7,7,NULL,0,'importance','_objects',8,'{\n  \"scope\": 53,\n  \"value\": 54,\n  \"faceting\": true\n}',NULL),

(47,7,7,NULL,0,'description','memo',10,'{\n  \"height\": 100\n  ,\"noHeader\": true\n  ,\"hidePreview\": true\n  ,\"linkRenderer\": \"user,object,url\"\n}',NULL),

(48,5,5,NULL,0,'_title','varchar',1,NULL,NULL),

(49,9,9,NULL,0,'_title','memo',0,'{\n\"height\": 100\n}','content'),

(50,7,7,NULL,0,'due_date','date',5,'{\n\"hidePreview\": true\n}',NULL),

(51,7,7,NULL,0,'due_time','time',6,'{\n\"hidePreview\": true\n}',NULL),

(63,62,62,NULL,0,'_title','varchar',1,NULL,NULL),

(64,62,62,NULL,0,'node_ids','_objects',2,'{\"multiValued\":true,\"editor\":\"form\",\"renderer\":\"listObjIcons\"}',NULL),

(65,62,62,NULL,0,'template_ids','_objects',3,'{\"templates\":\"11\",\"editor\":\"form\",\"multiValued\":true,\"renderer\":\"listObjIcons\"}',NULL),

(66,62,62,NULL,0,'user_group_ids','_objects',4,'{\"source\":\"usersgroups\",\"multiValued\":true}',NULL),

(67,62,62,NULL,0,'menu','_objects',5,'{\"templates\":\"11\",\"multiValued\":true,\"editor\":\"form\",\"allowValueSort\":true,\"renderer\":\"listObjIcons\"}',NULL),

(84,83,83,NULL,0,'type','_objects',1,'{\n\"scope\": 75 \n}',NULL),

(85,83,83,NULL,0,'url','varchar',2,NULL,NULL),

(86,83,83,NULL,0,'description','varchar',3,NULL,NULL),

(87,83,83,NULL,0,'tags','_objects',4,'{\n\"scope\": 82\n,\"editor\": \"tagField\"\n}',NULL),

(92,91,91,NULL,0,'_title','varchar',1,NULL,NULL),

(93,91,91,NULL,0,'value','int',2,NULL,NULL),

(95,94,94,NULL,0,'_title','varchar',1,NULL,NULL),

(96,94,94,NULL,0,'value','varchar',2,NULL,NULL),

(98,97,97,NULL,0,'_title','varchar',1,NULL,NULL),

(99,97,97,NULL,0,'value','text',2,NULL,NULL),

(101,100,100,NULL,0,'_title','varchar',1,NULL,NULL),

(102,100,100,NULL,0,'value','field',2,'{\n\"editor\":\"ace\",\n\"format\":\"json\",\n\"validator\":\"json\"\n}',NULL),

(103,100,100,NULL,0,'order','int',3,'{\"indexed\":true}','order'),

(136,10,10,NULL,0,'_title','varchar',1,NULL,NULL),

(137,10,10,NULL,0,'country','combo',20,'{\"source\":\"countries\"}',NULL),

(138,10,10,NULL,0,'timezone','combo',35,'{\"source\":\"timezones\"}',NULL),

(139,10,10,NULL,0,'groups','_objects',45,'{\"editor\":\"form\", \"scope\": \"135\"}',NULL),

(141,140,140,NULL,0,'_title','varchar',1,NULL,NULL),

(142,140,140,NULL,0,'en','varchar',2,NULL,NULL);

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
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `draft` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `draft_pid` varchar(10) DEFAULT NULL COMMENT 'used to attach other objects to a non existing, yet creating item',
  `template_id` int(10) unsigned DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(1000) DEFAULT NULL,
  `date` datetime DEFAULT NULL COMMENT 'start date',
  `date_end` datetime DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
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
  KEY `tree_pid` (`pid`),
  KEY `tree_updated` (`updated`),
  KEY `IDX_tree_date__date_end` (`date`,`date_end`),
  KEY `tree_template_id` (`template_id`),
  KEY `tree_draft` (`draft`),
  CONSTRAINT `tree_pid` FOREIGN KEY (`pid`) REFERENCES `tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tree_template_id` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;

/*Data for the table `tree` */

insert  into `tree`(`id`,`pid`,`system`,`draft`,`draft_pid`,`template_id`,`target_id`,`name`,`date`,`date_end`,`size`,`cfg`,`inherit_acl`,`cid`,`cdate`,`uid`,`udate`,`updated`,`oid`,`did`,`ddate`,`dstatus`) values

(1,NULL,1,0,NULL,5,NULL,'Tree',NULL,NULL,NULL,'[]',0,1,'2012-11-17 17:10:21',1,'2014-01-17 13:53:00',0,1,NULL,NULL,0),

(2,1,0,0,NULL,5,NULL,'System',NULL,NULL,NULL,'[]',0,1,'2015-05-20 15:57:45',NULL,NULL,0,1,NULL,NULL,0),

(3,2,0,0,NULL,5,NULL,'Templates',NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2014-01-17 13:53:08',0,1,NULL,NULL,0),

(4,2,0,0,NULL,5,NULL,'Thesauri','2013-09-24 19:38:09',NULL,NULL,'[]',1,256,'2013-09-24 19:38:09',1,'2014-01-17 13:53:08',0,256,NULL,NULL,0),

(5,88,0,0,NULL,11,NULL,'folder',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',1,'2016-06-09 13:50:12',0,1,NULL,NULL,0),

(6,88,0,0,NULL,11,NULL,'file_template',NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:48',1,'2016-06-09 13:50:28',0,1,NULL,NULL,0),

(7,88,0,0,NULL,11,NULL,'task',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',1,'2016-06-09 13:51:01',0,1,NULL,NULL,0),

(8,88,0,0,NULL,11,NULL,'Thesauri Item',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2016-06-09 13:52:05',0,1,NULL,NULL,0),

(9,88,0,0,NULL,11,NULL,'Comment',NULL,NULL,NULL,'null',1,1,'2014-02-12 21:14:04',1,'2016-06-09 13:52:26',0,1,NULL,NULL,0),

(10,88,0,0,NULL,11,NULL,'User',NULL,NULL,NULL,'{\"files\":\"1\",\"main_file\":\"1\"}',1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(11,88,0,0,NULL,11,NULL,'Template',NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2016-06-09 13:56:21',0,1,NULL,NULL,0),

(12,88,0,0,NULL,11,NULL,'Field',NULL,NULL,NULL,'[]',1,1,'2014-01-17 13:50:45',1,'2016-06-09 13:53:18',0,1,NULL,NULL,0),

(13,10,0,0,NULL,12,NULL,'en',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(14,10,0,0,NULL,12,NULL,'initials',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(15,10,0,0,NULL,12,NULL,'sex',NULL,NULL,NULL,'{\"thesauriId\":\"90\"}',1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(17,10,0,0,NULL,12,NULL,'email',NULL,NULL,NULL,'{\"maxInstances\":\"3\"}',1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(18,10,0,0,NULL,12,NULL,'language_id',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(19,10,0,0,NULL,12,NULL,'short_date_format',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(20,10,0,0,NULL,12,NULL,'description',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(22,10,0,0,NULL,12,NULL,'phone',NULL,NULL,NULL,'{\"maxInstances\":\"10\"}',1,1,'2014-01-17 13:50:48',NULL,'2016-06-16 09:15:11',0,1,NULL,NULL,0),

(24,6,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:50',NULL,NULL,0,1,NULL,NULL,0),

(25,12,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-01-21 11:24:06',0,1,NULL,NULL,0),

(26,12,0,0,NULL,12,NULL,'type',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(27,12,0,0,NULL,12,NULL,'order',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2016-06-09 13:57:55',0,1,NULL,NULL,0),

(28,12,0,0,NULL,12,NULL,'cfg',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-28 16:12:37',0,1,NULL,NULL,0),

(29,12,0,0,NULL,12,NULL,'solr_column_name',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(30,12,0,0,NULL,12,NULL,'en',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(31,11,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',1,'2014-02-12 21:12:31',0,1,NULL,NULL,0),

(32,11,0,0,NULL,12,NULL,'type',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(33,11,0,0,NULL,12,NULL,'visible',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(34,11,0,0,NULL,12,NULL,'iconCls',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(35,11,0,0,NULL,12,NULL,'cfg',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(36,11,0,0,NULL,12,NULL,'title_template',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(37,11,0,0,NULL,12,NULL,'info_template',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(38,11,0,0,NULL,12,NULL,'en',NULL,NULL,NULL,NULL,1,1,'2014-01-17 13:50:51',NULL,NULL,0,1,NULL,NULL,0),

(39,8,0,0,NULL,12,NULL,'iconCls',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:08',0,1,NULL,NULL,0),

(40,8,0,0,NULL,12,NULL,'visible',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:42',0,1,NULL,NULL,0),

(41,8,0,0,NULL,12,NULL,'order',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:05:57',0,1,NULL,NULL,0),

(42,8,0,0,NULL,12,NULL,'en',NULL,NULL,NULL,'{\"showIn\":\"top\"}',1,1,'2014-01-17 14:09:11',1,'2015-07-21 12:04:56',0,1,NULL,NULL,0),

(44,7,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 09:34:21',0,1,NULL,NULL,0),

(45,7,0,0,NULL,12,NULL,'assigned',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 10:32:02',0,1,NULL,NULL,0),

(46,7,0,0,NULL,12,NULL,'importance',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 12:26:19',0,1,NULL,NULL,0),

(47,7,0,0,NULL,12,NULL,'description',NULL,NULL,NULL,NULL,1,1,'2014-01-17 14:33:42',1,'2015-05-21 10:32:34',0,1,NULL,NULL,0),

(48,5,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,'null',1,1,'2014-01-22 14:10:27',NULL,NULL,0,1,NULL,NULL,0),

(49,9,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,'null',1,1,'2014-02-12 21:15:03',NULL,NULL,0,1,NULL,NULL,0),

(50,7,0,0,NULL,12,NULL,'due_date',NULL,NULL,NULL,'null',1,1,'2015-05-21 10:30:34',NULL,NULL,0,1,NULL,NULL,0),

(51,7,0,0,NULL,12,NULL,'due_time',NULL,NULL,NULL,'null',1,1,'2015-05-21 10:31:04',NULL,NULL,0,1,NULL,NULL,0),

(52,4,0,0,NULL,5,NULL,'task',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:09:09',NULL,NULL,0,1,NULL,NULL,0),

(53,52,0,0,NULL,5,NULL,'Importance',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:09:33',NULL,NULL,0,1,NULL,NULL,0),

(54,53,0,0,NULL,8,NULL,'Low',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:23:09',NULL,NULL,0,1,NULL,NULL,0),

(55,53,0,0,NULL,8,NULL,'Medium',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:24:01',NULL,NULL,0,1,NULL,NULL,0),

(56,53,0,0,NULL,8,NULL,'High',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:24:41',NULL,NULL,0,1,NULL,NULL,0),

(57,53,0,0,NULL,8,NULL,'CRITICAL',NULL,NULL,NULL,'null',1,1,'2015-05-21 12:25:12',NULL,NULL,0,1,NULL,NULL,0),

(58,88,0,0,NULL,11,NULL,'shortcut',NULL,NULL,NULL,NULL,1,1,'2015-06-06 21:50:18',1,'2016-06-09 13:53:35',0,1,NULL,NULL,0),

(59,88,0,0,NULL,5,NULL,'Menu',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(60,2,0,0,NULL,5,NULL,'Menus',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(61,59,0,0,NULL,11,NULL,'- Menu separator -',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(62,59,0,0,NULL,11,NULL,'Menu rule',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(63,62,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(64,62,0,0,NULL,12,NULL,'node_ids',NULL,NULL,NULL,'{\"multiValued\":true,\"editor\":\"form\",\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(65,62,0,0,NULL,12,NULL,'template_ids',NULL,NULL,NULL,'{\"templates\":\"11\",\"editor\":\"form\",\"multiValued\":true,\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(66,62,0,0,NULL,12,NULL,'user_group_ids',NULL,NULL,NULL,'{\"source\":\"usersgroups\",\"multiValued\":true}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(67,62,0,0,NULL,12,NULL,'menu',NULL,NULL,NULL,'{\"templates\":\"11\",\"multiValued\":true,\"editor\":\"form\",\"allowValueSort\":true,\"renderer\":\"listObjIcons\"}',1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(68,60,0,0,NULL,62,NULL,'Global Menu',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',1,'2015-09-01 07:28:13',0,1,NULL,NULL,0),

(69,60,0,0,NULL,62,NULL,'System Templates',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(70,60,0,0,NULL,62,NULL,'System Templates SubMenu',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(71,60,0,0,NULL,62,NULL,'System Fields',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(72,60,0,0,NULL,62,NULL,'System Thesauri',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(73,60,0,0,NULL,62,NULL,'Create menu rules in this folder',NULL,NULL,NULL,NULL,1,1,'2015-07-24 07:45:11',NULL,NULL,0,1,NULL,NULL,0),

(74,4,0,0,NULL,5,NULL,'link',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:15:55',NULL,NULL,0,1,NULL,NULL,0),

(75,74,0,0,NULL,5,NULL,'Type',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:16:07',NULL,NULL,0,1,NULL,NULL,0),

(76,75,0,0,NULL,8,NULL,'Article',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:17:46',NULL,NULL,0,1,NULL,NULL,0),

(77,75,0,0,NULL,8,NULL,'Document',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:06',NULL,NULL,0,1,NULL,NULL,0),

(78,75,0,0,NULL,8,NULL,'Image',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:24',NULL,NULL,0,1,NULL,NULL,0),

(79,75,0,0,NULL,8,NULL,'Sound',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:18:42',NULL,NULL,0,1,NULL,NULL,0),

(80,75,0,0,NULL,8,NULL,'Video',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:03',NULL,NULL,0,1,NULL,NULL,0),

(81,75,0,0,NULL,8,NULL,'Website',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:25',NULL,NULL,0,1,NULL,NULL,0),

(82,74,0,0,NULL,5,NULL,'Tags',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:19:42',NULL,NULL,0,1,NULL,NULL,0),

(83,88,0,0,NULL,11,NULL,'link',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:23:21',1,'2016-06-09 13:53:47',0,1,NULL,NULL,0),

(84,83,0,0,NULL,12,NULL,'type',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:25:21',NULL,NULL,0,1,NULL,NULL,0),

(85,83,0,0,NULL,12,NULL,'url',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:25:58',NULL,NULL,0,1,NULL,NULL,0),

(86,83,0,0,NULL,12,NULL,'description',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:26:29',NULL,NULL,0,1,NULL,NULL,0),

(87,83,0,0,NULL,12,NULL,'tags',NULL,NULL,NULL,NULL,1,1,'2015-09-01 07:27:09',1,'2015-09-01 07:30:36',0,1,NULL,NULL,0),

(88,3,0,0,NULL,5,NULL,'Built-in',NULL,NULL,NULL,NULL,1,1,'2015-09-02 13:45:52',NULL,NULL,0,1,NULL,NULL,0),

(89,3,0,0,NULL,5,NULL,'Config',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(90,2,0,0,NULL,5,NULL,'Config',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(91,89,0,0,NULL,11,NULL,'Config int option',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2016-06-09 13:54:28',0,1,NULL,NULL,0),

(92,91,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(93,91,0,0,NULL,12,NULL,'value',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(94,89,0,0,NULL,11,NULL,'Config varchar option',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2016-06-09 13:54:40',0,1,NULL,NULL,0),

(95,94,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(96,94,0,0,NULL,12,NULL,'value',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(97,89,0,0,NULL,11,NULL,'Config text option',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2016-06-09 13:54:50',0,1,NULL,NULL,0),

(98,97,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(99,97,0,0,NULL,12,NULL,'value',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(100,89,0,0,NULL,11,NULL,'Config json option',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2016-06-09 13:55:06',0,1,NULL,NULL,0),

(101,100,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(102,100,0,0,NULL,12,NULL,'value',NULL,NULL,NULL,'{\"editor\":\"ace\",\"format\":\"json\",\"validator\":\"json\"}',1,1,'2015-09-09 12:58:27',1,'2016-04-29 08:00:26',0,1,NULL,NULL,0),

(103,100,0,0,NULL,12,NULL,'order',NULL,NULL,NULL,'{\"indexed\":true}',1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(104,90,0,0,NULL,94,NULL,'project_name_en',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(105,90,0,0,NULL,97,NULL,'templateIcons',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2016-06-09 13:48:37',0,1,NULL,NULL,0),

(106,90,0,0,NULL,97,NULL,'folder_templates',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(107,90,0,0,NULL,91,NULL,'default_folder_template',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(108,90,0,0,NULL,91,NULL,'default_file_template',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(109,90,0,0,NULL,91,NULL,'default_task_template',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(110,90,0,0,NULL,94,NULL,'default_language',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(111,90,0,0,NULL,94,NULL,'languages',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(112,90,0,0,NULL,100,NULL,'object_type_plugins',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(113,90,0,0,NULL,100,NULL,'treeNodes',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(114,113,0,0,NULL,100,NULL,'Tasks',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(115,113,0,0,NULL,100,NULL,'Dbnode',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(116,113,0,0,NULL,100,NULL,'RecycleBin',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',1,'2015-11-25 13:52:47',0,1,NULL,NULL,0),

(117,60,0,0,NULL,62,NULL,'Create config options rule',NULL,NULL,NULL,NULL,1,1,'2015-09-09 12:58:27',NULL,NULL,0,1,NULL,NULL,0),

(118,90,0,0,NULL,100,NULL,'files',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:53:55',NULL,NULL,0,1,NULL,NULL,0),

(119,90,0,0,NULL,94,NULL,'timezone',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:55:28',NULL,NULL,0,1,NULL,NULL,0),

(120,90,0,0,NULL,100,NULL,'language_en',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:56:08',NULL,NULL,0,1,NULL,NULL,0),

(121,90,0,0,NULL,100,NULL,'language_fr',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:56:40',NULL,NULL,0,1,NULL,NULL,0),

(122,90,0,0,NULL,100,NULL,'language_ru',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:57:06',NULL,NULL,0,1,NULL,NULL,0),

(123,90,0,0,NULL,100,NULL,'default_facet_configs',NULL,NULL,NULL,NULL,1,1,'2016-04-29 07:59:21',NULL,NULL,0,1,NULL,NULL,0),

(124,90,0,0,NULL,100,NULL,'node_facets',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:01:22',NULL,NULL,0,1,NULL,NULL,0),

(125,90,0,0,NULL,100,NULL,'default_object_plugins',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:04:38',1,'2016-04-29 08:15:53',0,1,NULL,NULL,0),

(126,90,0,0,NULL,91,NULL,'images_display_size',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:11:54',NULL,NULL,0,1,NULL,NULL,0),

(127,90,0,0,NULL,100,NULL,'default_DC',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:12:21',NULL,NULL,0,1,NULL,NULL,0),

(128,90,0,0,NULL,94,NULL,'default_availableViews',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:14:13',NULL,NULL,0,1,NULL,NULL,0),

(129,90,0,0,NULL,100,NULL,'DCConfigs',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:17:58',NULL,NULL,0,1,NULL,NULL,0),

(130,129,0,0,NULL,100,NULL,'dc_tasks',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:18:25',NULL,NULL,0,1,NULL,NULL,0),

(131,129,0,0,NULL,100,NULL,'dc_tasks_closed',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:20:58',NULL,NULL,0,1,NULL,NULL,0),

(132,90,0,0,NULL,94,NULL,'geoMapping',NULL,NULL,NULL,NULL,1,1,'2016-04-29 08:22:54',NULL,NULL,0,1,NULL,NULL,0),

(133,2,0,0,NULL,5,NULL,'Security',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(134,133,0,0,NULL,5,NULL,'Users',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(135,133,0,0,NULL,5,NULL,'Groups',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(136,10,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(137,10,0,0,NULL,12,NULL,'country',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(138,10,0,0,NULL,12,NULL,'timezone',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(139,10,0,0,NULL,12,NULL,'groups',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(140,88,0,0,NULL,11,NULL,'group',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(141,140,0,0,NULL,12,NULL,'_title',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(142,140,0,0,NULL,12,NULL,'en',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(143,60,0,0,NULL,62,NULL,'Groups folder rule',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(144,60,0,0,NULL,62,NULL,'Users folder rule',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(145,135,0,0,NULL,140,NULL,'everyone',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0),

(146,134,0,0,NULL,10,NULL,'root',NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,NULL,0,NULL,NULL,NULL,0);

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

insert  into `tree_info`(`id`,`pids`,`path`,`case_id`,`acl_count`,`security_set_id`,`updated`) values

(1,'1','',NULL,0,NULL,0),

(2,'1,2','/',NULL,0,NULL,0),

(3,'1,2,3','/System/',NULL,0,NULL,0),

(4,'1,2,4','/System/',NULL,0,NULL,0),

(5,'1,2,3,88,5','/System/Templates/',NULL,0,NULL,0),

(6,'1,2,3,88,6','/System/Templates/',NULL,0,NULL,0),

(7,'1,2,3,88,7','/System/Templates/',NULL,0,NULL,0),

(8,'1,2,3,88,8','/System/Templates/',NULL,0,NULL,0),

(9,'1,2,3,88,9','/System/Templates/',NULL,0,NULL,0),

(10,'1,2,3,88,10','/System/Templates/',NULL,0,NULL,0),

(11,'1,2,3,88,11','/System/Templates/',NULL,0,NULL,0),

(12,'1,2,3,88,12','/System/Templates/',NULL,0,NULL,0),

(13,'1,2,3,88,10,13','/System/Templates/User/',NULL,0,NULL,0),

(14,'1,2,3,88,10,14','/System/Templates/User/',NULL,0,NULL,0),

(15,'1,2,3,88,10,15','/System/Templates/User/',NULL,0,NULL,0),

(17,'1,2,3,88,10,17','/System/Templates/User/',NULL,0,NULL,0),

(18,'1,2,3,88,10,18','/System/Templates/User/',NULL,0,NULL,0),

(19,'1,2,3,88,10,19','/System/Templates/User/',NULL,0,NULL,0),

(20,'1,2,3,88,10,20','/System/Templates/User/',NULL,0,NULL,0),

(22,'1,2,3,88,10,22','/System/Templates/User/',NULL,0,NULL,0),

(24,'1,2,3,88,6,24','/System/Templates/file/',NULL,0,NULL,0),

(25,'1,2,3,88,12,25','/System/Templates/Fields template/',NULL,0,NULL,0),

(26,'1,2,3,88,12,26','/System/Templates/Fields template/',NULL,0,NULL,0),

(27,'1,2,3,88,12,27','/System/Templates/Fields template/',NULL,0,NULL,0),

(28,'1,2,3,88,12,28','/System/Templates/Fields template/',NULL,0,NULL,0),

(29,'1,2,3,88,12,29','/System/Templates/Fields template/',NULL,0,NULL,0),

(30,'1,2,3,88,12,30','/System/Templates/Fields template/',NULL,0,NULL,0),

(31,'1,2,3,88,11,31','/System/Templates/Templates template/',NULL,0,NULL,0),

(32,'1,2,3,88,11,32','/System/Templates/Templates template/',NULL,0,NULL,0),

(33,'1,2,3,88,11,33','/System/Templates/Templates template/',NULL,0,NULL,0),

(34,'1,2,3,88,11,34','/System/Templates/Templates template/',NULL,0,NULL,0),

(35,'1,2,3,88,11,35','/System/Templates/Templates template/',NULL,0,NULL,0),

(36,'1,2,3,88,11,36','/System/Templates/Templates template/',NULL,0,NULL,0),

(37,'1,2,3,88,11,37','/System/Templates/Templates template/',NULL,0,NULL,0),

(38,'1,2,3,88,11,38','/System/Templates/Templates template/',NULL,0,NULL,0),

(39,'1,2,3,88,8,39','/System/Templates/Thesauri Item/',NULL,0,NULL,0),

(40,'1,2,3,88,8,40','/System/Templates/Thesauri Item/',NULL,0,NULL,0),

(41,'1,2,3,88,8,41','/System/Templates/Thesauri Item/',NULL,0,NULL,0),

(42,'1,2,3,88,8,42','/System/Templates/Thesauri Item/',NULL,0,NULL,0),

(44,'1,2,3,88,7,44','/System/Templates/task/',NULL,0,NULL,0),

(45,'1,2,3,88,7,45','/System/Templates/task/',NULL,0,NULL,0),

(46,'1,2,3,88,7,46','/System/Templates/task/',NULL,0,NULL,0),

(47,'1,2,3,88,7,47','/System/Templates/task/',NULL,0,NULL,0),

(48,'1,2,3,88,5,48','/System/Templates/folder/',NULL,0,NULL,0),

(49,'1,2,3,88,9,49','/System/Templates/Comment/',NULL,0,NULL,0),

(50,'1,2,3,88,7,50','/System/Templates/task/',NULL,0,NULL,0),

(51,'1,2,3,88,7,51','/System/Templates/task/',NULL,0,NULL,0),

(52,'1,2,4,52',NULL,NULL,0,NULL,0),

(53,'1,2,4,52,53',NULL,NULL,0,NULL,0),

(54,'1,2,4,52,53,54',NULL,NULL,0,NULL,0),

(55,'1,2,4,52,53,55',NULL,NULL,0,NULL,0),

(56,'1,2,4,52,53,56',NULL,NULL,0,NULL,0),

(57,'1,2,4,52,53,57',NULL,NULL,0,NULL,0),

(58,'1,2,3,88,58',NULL,NULL,0,NULL,0),

(59,'1,2,3,88,59',NULL,NULL,0,NULL,0),

(60,'1,2,60',NULL,NULL,0,NULL,0),

(61,'1,2,3,88,59,61',NULL,NULL,0,NULL,0),

(62,'1,2,3,88,59,62',NULL,NULL,0,NULL,0),

(63,'1,2,3,88,59,62,63',NULL,NULL,0,NULL,0),

(64,'1,2,3,88,59,62,64',NULL,NULL,0,NULL,0),

(65,'1,2,3,88,59,62,65',NULL,NULL,0,NULL,0),

(66,'1,2,3,88,59,62,66',NULL,NULL,0,NULL,0),

(67,'1,2,3,88,59,62,67',NULL,NULL,0,NULL,0),

(68,'1,2,60,68',NULL,NULL,0,NULL,0),

(69,'1,2,60,69',NULL,NULL,0,NULL,0),

(70,'1,2,60,70',NULL,NULL,0,NULL,0),

(71,'1,2,60,71',NULL,NULL,0,NULL,0),

(72,'1,2,60,72',NULL,NULL,0,NULL,0),

(73,'1,2,60,73',NULL,NULL,0,NULL,0),

(74,'1,2,4,74',NULL,NULL,0,NULL,0),

(75,'1,2,4,74,75',NULL,NULL,0,NULL,0),

(76,'1,2,4,74,75,76',NULL,NULL,0,NULL,0),

(77,'1,2,4,74,75,77',NULL,NULL,0,NULL,0),

(78,'1,2,4,74,75,78',NULL,NULL,0,NULL,0),

(79,'1,2,4,74,75,79',NULL,NULL,0,NULL,0),

(80,'1,2,4,74,75,80',NULL,NULL,0,NULL,0),

(81,'1,2,4,74,75,81',NULL,NULL,0,NULL,0),

(82,'1,2,4,74,82',NULL,NULL,0,NULL,0),

(83,'1,2,3,88,83',NULL,NULL,0,NULL,0),

(84,'1,2,3,88,83,84',NULL,NULL,0,NULL,0),

(85,'1,2,3,88,83,85',NULL,NULL,0,NULL,0),

(86,'1,2,3,88,83,86',NULL,NULL,0,NULL,0),

(87,'1,2,3,88,83,87',NULL,NULL,0,NULL,0),

(88,'1,2,3,88',NULL,NULL,0,NULL,0),

(89,'1,2,3,89',NULL,NULL,0,NULL,0),

(90,'1,2,90',NULL,NULL,0,NULL,0),

(91,'1,2,3,89,91',NULL,NULL,0,NULL,0),

(92,'1,2,3,89,91,92',NULL,NULL,0,NULL,0),

(93,'1,2,3,89,91,93',NULL,NULL,0,NULL,0),

(94,'1,2,3,89,94',NULL,NULL,0,NULL,0),

(95,'1,2,3,89,94,95',NULL,NULL,0,NULL,0),

(96,'1,2,3,89,94,96',NULL,NULL,0,NULL,0),

(97,'1,2,3,89,97',NULL,NULL,0,NULL,0),

(98,'1,2,3,89,97,98',NULL,NULL,0,NULL,0),

(99,'1,2,3,89,97,99',NULL,NULL,0,NULL,0),

(100,'1,2,3,89,100',NULL,NULL,0,NULL,0),

(101,'1,2,3,89,100,101',NULL,NULL,0,NULL,0),

(102,'1,2,3,89,100,102',NULL,NULL,0,NULL,0),

(103,'1,2,3,89,100,103',NULL,NULL,0,NULL,0),

(104,'1,2,90,104',NULL,NULL,0,NULL,0),

(105,'1,2,90,105',NULL,NULL,0,NULL,0),

(106,'1,2,90,106',NULL,NULL,0,NULL,0),

(107,'1,2,90,107',NULL,NULL,0,NULL,0),

(108,'1,2,90,108',NULL,NULL,0,NULL,0),

(109,'1,2,90,109',NULL,NULL,0,NULL,0),

(110,'1,2,90,110',NULL,NULL,0,NULL,0),

(111,'1,2,90,111',NULL,NULL,0,NULL,0),

(112,'1,2,90,112',NULL,NULL,0,NULL,0),

(113,'1,2,90,113',NULL,NULL,0,NULL,0),

(114,'1,2,90,113,114',NULL,NULL,0,NULL,0),

(115,'1,2,90,113,115',NULL,NULL,0,NULL,0),

(116,'1,2,90,113,116',NULL,NULL,0,NULL,0),

(117,'1,2,60,117',NULL,NULL,0,NULL,0),

(118,'1,2,90,118',NULL,NULL,0,NULL,0),

(119,'1,2,90,119',NULL,NULL,0,NULL,0),

(120,'1,2,90,120',NULL,NULL,0,NULL,0),

(121,'1,2,90,121',NULL,NULL,0,NULL,0),

(122,'1,2,90,122',NULL,NULL,0,NULL,0),

(123,'1,2,90,123',NULL,NULL,0,NULL,0),

(124,'1,2,90,124',NULL,NULL,0,NULL,0),

(125,'1,2,90,125',NULL,NULL,0,NULL,0),

(126,'1,2,90,126',NULL,NULL,0,NULL,0),

(127,'1,2,90,127',NULL,NULL,0,NULL,0),

(128,'1,2,90,128',NULL,NULL,0,NULL,0),

(129,'1,2,90,129',NULL,NULL,0,NULL,0),

(130,'1,2,90,129,130',NULL,NULL,0,NULL,0),

(131,'1,2,90,129,131',NULL,NULL,0,NULL,0),

(132,'1,2,90,132',NULL,NULL,0,NULL,0),

(133,'1,2,133',NULL,NULL,0,NULL,0),

(134,'1,2,133,134',NULL,NULL,0,NULL,0),

(135,'1,2,133,135',NULL,NULL,0,NULL,0),

(136,'1,2,3,88,10,136',NULL,NULL,0,NULL,0),

(137,'1,2,3,88,10,137',NULL,NULL,0,NULL,0),

(138,'1,2,3,88,10,138',NULL,NULL,0,NULL,0),

(139,'1,2,3,88,10,139',NULL,NULL,0,NULL,0),

(140,'1,2,3,88,140',NULL,NULL,0,NULL,0),

(141,'1,2,3,88,140,141',NULL,NULL,0,NULL,0),

(142,'1,2,3,88,140,142',NULL,NULL,0,NULL,0),

(143,'1,2,60,143',NULL,NULL,0,NULL,0),

(144,'1,2,60,144',NULL,NULL,0,NULL,0),

(145,'1,2,133,135,145',NULL,NULL,0,NULL,0),

(146,'1,2,133,134,146',NULL,NULL,0,NULL,0);

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
  `salt` varchar(255) NOT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:json_array)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_type__name` (`name`,`type`),
  KEY `IDX_recover_hash` (`recover_hash`),
  KEY `FK_users_groups_language` (`language_id`),
  KEY `IDX_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;

/*Data for the table `users_groups` */

insert  into `users_groups`(`id`,`type`,`system`,`name`,`first_name`,`last_name`,`sex`,`email`,`photo`,`password`,`password_change`,`recover_hash`,`language_id`,`cfg`,`data`,`last_login`,`login_successful`,`login_from_ip`,`last_logout`,`last_action_time`,`enabled`,`cid`,`cdate`,`uid`,`udate`,`did`,`ddate`,`searchField`,`salt`,`roles`) values

(145,1,0,'everyone','Everyone',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,'0000-00-00 00:00:00',NULL,NULL,' everyone Everyone   ','',''),

(146,2,0,'root','Full name en',NULL,'m','anemail@gmai.com',NULL,'50775b4f5109fd22c46dabb17f710c17','2016-06-16',NULL,1,'{\"short_date_format\":\"d.m.Y\",\"country_code\":\"\",\"phone\":\"+331111111\",\"timezone\":\"\",\"color\":\"#a5c5e2\",\"state\":{\"mAc\":{\"width\":250,\"weight\":-10},\"mopp\":{\"weight\":-20},\"btree\":{\"paths\":[\"\\/1\",\"\\/1\\/2\",\"\\/1\\/2\\/133\"],\"width\":250,\"selected\":\"\\/1\\/2\\/133\\/134\",\"weight\":0}},\"lastNotifyTime\":\"2016-06-16 09:16:27\"}',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'2016-06-16 09:15:11',NULL,'0000-00-00 00:00:00',NULL,NULL,' root Full name en  anemail@gmai.com ','','{\"ROLE_USER\":\"ROLE_USER\"}');

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
		,COALESCE(new.first_name, '')
		,' '
		,COALESCE(new.last_name, '')
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
		,COALESCE(new.first_name, '')
		,' '
		,COALESCE(new.last_name, '')
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

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
