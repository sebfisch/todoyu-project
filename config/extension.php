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

	// Register quicktask headlet
if ( allowed('project', 'general:quicktaskheadlet') ) {
	TodoyuHeadletManager::registerRight('TodoyuHeadletQuickTask', 10);
}



/**
 * @todo	Implement this with a hook
 */
TodoyuRenderer::addAreaRenderer('project', 'projectinfo', 'TodoyuProjectRenderer::renderProjectPersons');

TodoyuContextMenuManager::registerFunction('Task', 'TodoyuTaskManager::getContextMenuItems', 10);

TodoyuContextMenuManager::registerFunction('Task', 'TodoyuTaskClipboard::getTaskContextMenuItems', 100);
TodoyuContextMenuManager::registerFunction('Task', 'TodoyuTaskManager::removeEmptyContextMenuParents', 100000);
TodoyuContextMenuManager::registerFunction('Project', 'TodoyuProjectManager::getContextMenuItems', 10);



$CONFIG['EXT']['project']['STATUS']['PROJECT'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_PROGRESS		=> 'progress',
	STATUS_DONE			=> 'done',
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
//	STATUS_CUSTOMER		=> 'customer'
);


$CONFIG['EXT']['project']['Task']['defaultEstimatedWorkload'] = 0;

/**
 * Temporary tab force for all tasks
 * Don't set it here!
 */
$CONFIG['EXT']['project']['Task']['forceTab'] = false;

/**
 * Add filterwidget type "projectrole"
 */
$CONFIG['EXT']['search']['widgettypes']['projectrole'] =array(
	'tmpl'			=> 'ext/project/view/filterwidget-projectrole.tmpl',
	'configFunc'	=> 'TodoyuProjectFilter::prepareDataForProjectroleWidget'
);

?>