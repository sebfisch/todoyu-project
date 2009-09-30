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

/**
 * General configuration for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */


if( ! defined('TODOYU') ) die('NO ACCESS');


TodoyuRenderer::addAreaRenderer('project', 'content', 'TodoyuProjectRenderer::renderProjectView');

//$CONFIG['EXT']['project']['renderer']['content'][] 	= 'TodoyuProjectRenderer::renderProjectView';
$CONFIG['EXT']['project']['renderer']['panel'][]	= 'TodoyuProjectRenderer::renderPanelWidgets';


TodoyuFormHook::registerBuildForm('ext/project/config/form/task.xml', 'TodoyuTaskManager::modifyFormfieldsForContainer');


TodoyuRenderer::addAreaRenderer('project', 'projectinfo', 'TodoyuProjectRenderer::renderProjectAssignedUsers');

TodoyuHookManager::registerHook('project', 'taskinfo', 'TodoyuTaskManager::addContainerInfoToTaskData');

TodoyuContextMenuManager::registerFunction('Task', 'TodoyuTaskManager::getContextMenuItems', 10);
TodoyuContextMenuManager::registerFunction('Project', 'TodoyuProjectManager::getContextMenuItems', 10);




	// @see	constants are defined in  constants.php
$CONFIG['EXT']['project']['STATUS']['PROJECT'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_OPEN			=> 'open',
	STATUS_PROGRESS		=> 'progress',
	STATUS_CONFIRM		=> 'confirm',
	STATUS_DONE			=> 'done',
	STATUS_ACCEPTED		=> 'accepted',
	STATUS_REJECTED		=> 'rejected',
	STATUS_CLEARED		=> 'cleared',
	STATUS_WARRANTY		=> 'warranty'
);

$CONFIG['EXT']['project']['STATUS']['TASK'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_OPEN			=> 'open',
	STATUS_PROGRESS		=> 'progress',
	STATUS_CONFIRM		=> 'confirm',
	STATUS_DONE			=> 'done',
	STATUS_ACCEPTED		=> 'accepted',
	STATUS_REJECTED		=> 'rejected',
	STATUS_CLEARED		=> 'cleared',
	STATUS_WARRANTY		=> 'warranty',
	STATUS_CUSTOMER		=> 'customer'
);


$CONFIG['FORM']['TYPES']['projectusers'] = array(
	'class'		=> 'TodoyuFormElement_ProjectUsers',
	'template'	=> 'ext/project/view/formelement-projectusers.tmpl'
);


TodoyuExtManager::addRecordConfig('project', 'userrole', array(
	'label'	=> 'LLL:project.records.userrole',
	'form'	=> 'ext/project/config/form/admin/userrole.xml',
	'list'	=> 'TodoyuUserroleManager::getRecordList',
	'save'	=> 'TodoyuUserroleManager::save',
	'delete'=> 'TodoyuUserroleManager::delete',
	'object'=> 'TodoyuUserrole'
));

TodoyuExtManager::addRecordConfig('project', 'worktype', array(
	'label'	=> 'LLL:project.records.worktype',
	'form'	=> 'ext/project/config/form/admin/worktype.xml',
	'list'	=> 'TodoyuWorktypeManager::getRecordList',
	'save'	=> 'TodoyuWorktypeManager::save',
	'delete'=> 'TodoyuWorktypeManager::delete',
	'object'=> 'TodoyuWorktype'
));




$CONFIG['EXT']['project']['Task']['defaultEstimatedWorkload'] = 3600;

?>