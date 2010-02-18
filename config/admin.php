<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

	// Add record infos
TodoyuExtManager::addRecordConfig('project', 'projectrole', array(
	'label'	=> 'LLL:project.records.projectrole',
	'form'	=> 'ext/project/config/form/admin/projectrole.xml',
	'list'	=> 'TodoyuProjectroleManager::getRecords',
	'save'	=> 'TodoyuProjectroleManager::saveProjectrole',
	'delete'=> 'TodoyuProjectroleManager::deleteProjectrole',
	'object'=> 'TodoyuProjectrole',
	'table'	=> 'ext_project_role'
));

TodoyuExtManager::addRecordConfig('project', 'worktype', array(
	'label'	=> 'LLL:project.records.worktype',
	'form'	=> 'ext/project/config/form/admin/worktype.xml',
	'list'	=> 'TodoyuWorktypeManager::getRecords',
	'save'	=> 'TodoyuWorktypeManager::saveWorktype',
	'delete'=> 'TodoyuWorktypeManager::deleteWorktype',
	'object'=> 'TodoyuWorktype',
	'table'	=> 'ext_project_worktype'
));

?>