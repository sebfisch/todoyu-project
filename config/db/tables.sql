--
-- Table structure for table `ext_project_mm_project_user`
--

CREATE TABLE `ext_project_mm_project_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_project` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user` int(10) unsigned NOT NULL DEFAULT '0',
  `id_userrole` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`id_project`),
  KEY `person` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_project_project`
--

CREATE TABLE `ext_project_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `date_update` int(10) unsigned NOT NULL DEFAULT '0',
  `id_user_create` smallint(5) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date_start` int(10) unsigned NOT NULL DEFAULT '0',
  `date_end` int(10) unsigned NOT NULL DEFAULT '0',
  `date_deadline` int(10) unsigned NOT NULL DEFAULT '0',
  `date_finish` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_customer` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fixedcosts` float unsigned NOT NULL DEFAULT '0',
  `is_fixedcosts_paid` tinyint(1) unsigned NOT NULL DEFAULT '0',  
  `is_fixed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `id_fixedproject` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_rateset` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `customer` (`id_customer`),
  FULLTEXT KEY `name_domain` (`title`,`ext_hosting_domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_project_task`
--

CREATE TABLE `ext_project_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_create` int(11) NOT NULL DEFAULT '0',
  `date_update` int(11) NOT NULL,
  `id_user_create` smallint(6) NOT NULL DEFAULT '0',  
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_project` smallint(6) NOT NULL DEFAULT '0',
  `id_parenttask` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `id_user_assigned` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_user_owner` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date_deadline` int(10) unsigned NOT NULL DEFAULT '0',
  `date_start` int(10) unsigned NOT NULL DEFAULT '0',
  `date_end` int(10) unsigned NOT NULL DEFAULT '0',
  `date_finish` int(10) unsigned NOT NULL,
  `tasknumber` int(11) DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  `estimated_workload` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_estimatedworkload_public` tinyint(1) NOT NULL,
  `is_acknowledged` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `offered_accesslevel` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_offered` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `clearance_state` tinyint(3) unsigned NOT NULL DEFAULT '0', 
  `is_private` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sorting` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parenttask` (`id_parenttask`),
  KEY `project` (`id_project`),
  KEY `assigned_to` (`id_user_assigned`),
  KEY `cruser` (`id_user_create`),
  KEY `owner` (`id_user_owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_project_userrole`
--

CREATE TABLE `ext_project_userrole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_create` int(10) NOT NULL,
  `id_user_create` smallint(5) NOT NULL,
  `date_update` int(10) NOT NULL,
  `rolekey` varchar(35) NOT NULL,
  `title` varchar(60) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `ext_project_worktype`
--

CREATE TABLE `ext_project_worktype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date_update` int(11) NOT NULL,
  `id_user_create` smallint(5) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(64) NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;