<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

	// Add record infos
TodoyuSysmanagerExtManager::addRecordConfig('project', 'projectrole', array(
	'label'	=> 'LLL:project.records.projectrole',
	'form'	=> 'ext/project/config/form/admin/projectrole.xml',
	'list'	=> 'TodoyuProjectProjectroleroleManager::getRecords',
	'save'	=> 'TodoyuProjectProjectroleManager::saveProjectrole',
	'delete'=> 'TodoyuProjectProjectroleManager::deleteProjectrole',
	'object'=> 'TodoyuProjectrole',
	'table'	=> 'ext_project_role'
));

TodoyuSysmanagerExtManager::addRecordConfig('project', 'activity', array(
	'label'	=> 'LLL:project.records.activity',
	'form'	=> 'ext/project/config/form/admin/activity.xml',
	'list'	=> 'TodoyuProjectActivityManager::getRecords',
	'save'	=> 'TodoyuProjectActivityManager::saveActivity',
	'delete'=> 'TodoyuProjectActivityManager::deleteActivity',
	'object'=> 'TodoyuProjectActivity',
	'table'	=> 'ext_project_activity'
));

TodoyuSysmanagerExtManager::addRecordConfig('project', 'taskpreset', array(
	'label'	=> 'LLL:project.records.taskpreset',
	'form'	=> 'ext/project/config/form/admin/taskpreset.xml',
	'list'	=> 'TodoyuProjectTaskpresetManager::getRecords',
	'save'	=> 'TodoyuProjectTaskpresetManager::saveTaskpreset',
	'delete'=> 'TodoyuProjectTaskpresetManager::deleteTaskpreset',
	'object'=> 'TodoyuProjectTaskpreset',
	'table'	=> 'ext_project_taskpreset'
));

?>