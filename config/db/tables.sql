--
-- Table structure for table `ext_project_project`
--

CREATE TABLE `ext_project_project` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL DEFAULT '0',
	`date_update` int(10) unsigned NOT NULL DEFAULT '0',
	`id_person_create` int(10) unsigned NOT NULL DEFAULT '0',
	`deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`date_start` int(10) unsigned NOT NULL DEFAULT '0',
	`date_end` int(10) unsigned NOT NULL DEFAULT '0',
	`date_deadline` int(10) unsigned NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL,
	`description` mediumtext NOT NULL,
	`status` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`id_company` int(10) unsigned NOT NULL DEFAULT '0',
	`id_taskpreset` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_project_task`
--

CREATE TABLE `ext_project_task` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`date_create` int(11) NOT NULL DEFAULT '0',
	`date_update` int(11) NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`deleted` tinyint(1) NOT NULL DEFAULT '0',
	`type` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`id_project` int(10) unsigned NOT NULL DEFAULT '0',
	`id_parenttask` mediumint(8) unsigned NOT NULL DEFAULT '0',
	`title` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`id_person_assigned` int(10) unsigned NOT NULL DEFAULT '0',
	`id_person_owner` int(10) unsigned NOT NULL DEFAULT '0',
	`date_deadline` int(10) unsigned NOT NULL DEFAULT '0',
	`date_start` int(10) unsigned NOT NULL DEFAULT '0',
	`date_end` int(10) unsigned NOT NULL DEFAULT '0',
	`tasknumber` int(11) DEFAULT '0',
	`status` tinyint(4) NOT NULL,
	`id_worktype` smallint(6) NOT NULL DEFAULT '0',
	`estimated_workload` mediumint(8) unsigned NOT NULL DEFAULT '0',
	`is_acknowledged` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`sorting` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `parenttask` (`id_parenttask`),
	KEY `project` (`id_project`),
	KEY `assigned_to` (`id_person_assigned`),
	KEY `multi` (`status`,`type`,`deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_project_role`
--

CREATE TABLE `ext_project_role` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`date_create` int(10) NOT NULL,
	`date_update` int(10) NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`rolekey` varchar(35) NOT NULL,
	`title` varchar(60) NOT NULL,
	`deleted` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_project_worktype`
--

CREATE TABLE `ext_project_worktype` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL DEFAULT '0',
	`date_update` int(11) NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`title` varchar(64) NOT NULL
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_project_mm_project_person`
--

CREATE TABLE `ext_project_mm_project_person` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`id_project` int(10) unsigned NOT NULL DEFAULT '0',
	`id_person` int(10) unsigned NOT NULL,
	`id_role` int(10) unsigned NOT NULL,
	`comment` tinytext NOT NULL,
	`is_public` tinyint(1) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `project` (`id_project`),
	KEY `person` (`id_person`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ext_project_taskpreset`
--

CREATE TABLE `ext_project_taskpreset` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`date_create` int(10) unsigned NOT NULL DEFAULT '0',
	`date_update` int(11) NOT NULL,
	`id_person_create` int(10) unsigned NOT NULL,
	`deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`title` varchar(64) NOT NULL,
	`tasktitle` varchar(255) NOT NULL,
	`description` mediumtext NOT NULL,
	`date_start` int(10) unsigned NOT NULL DEFAULT '0',
	`date_end` int(10) unsigned NOT NULL DEFAULT '0',
	`date_deadline` int(10) unsigned NOT NULL DEFAULT '0',
	`status` tinyint(4) NOT NULL,
	`id_worktype` smallint(6) NOT NULL DEFAULT '0',
	`estimated_workload` mediumint(8) unsigned NOT NULL DEFAULT '0',
	`is_public` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`quicktask_duration_days` int(10) unsigned NOT NULL DEFAULT '7',
	`person_assigned_role` int(10) unsigned NOT NULL DEFAULT '0',
	`id_person_assigned` int(10) unsigned NOT NULL DEFAULT '0',
	`person_owner_role` int(10) unsigned NOT NULL DEFAULT '0',
	`id_person_owner` int(10) unsigned NOT NULL DEFAULT '0'
	PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
