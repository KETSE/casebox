ALTER TABLE `templates`
  CHANGE `type` `type` ENUM('case','object','file','task','user','email','template','field','search','comment','shortcut','menu','config','time_tracking') CHARSET utf8 COLLATE utf8_general_ci NULL;
