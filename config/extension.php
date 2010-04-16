<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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

/**
 * General configuration for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

/**
 * Add autocompleters
 */
TodoyuAutocompleter::addAutocompleter('projectperson', 'TodoyuPersonFilterDataSource::autocompletePersons', array('project', 'general:use'));
TodoyuAutocompleter::addAutocompleter('projecttask', 'TodoyuTaskViewHelper::getProjecttaskAutocomplete', array('project', 'general:use'));
TodoyuAutocompleter::addAutocompleter('project', 'TodoyuProjectFilterDataSource::autocompleteProjects', array('project', 'general:use'));


TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskManager::getContextMenuItems', 10);

TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskClipboard::getTaskContextMenuItems', 100);
TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskManager::removeEmptyContextMenuParents', 100000);
TodoyuContextMenuManager::addFunction('Project', 'TodoyuProjectManager::getContextMenuItems', 10);



Todoyu::$CONFIG['EXT']['project']['STATUS']['PROJECT'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_PROGRESS		=> 'progress',
	STATUS_DONE			=> 'done',
	STATUS_CLEARED		=> 'cleared',
	STATUS_WARRANTY		=> 'warranty'
);

Todoyu::$CONFIG['EXT']['project']['STATUS']['TASK'] = array(
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


Todoyu::$CONFIG['EXT']['project']['Task']['defaultEstimatedWorkload'] = 0;

/**
 * Temporary tab force for all tasks
 * Don't set it here!
 */
Todoyu::$CONFIG['EXT']['project']['Task']['forceTab'] = false;

/**
 * Add filterwidget type "projectrole"
 */
Todoyu::$CONFIG['EXT']['search']['widgettypes']['projectrole'] =array(
	'tmpl'			=> 'ext/project/view/filterwidget-projectrole.tmpl',
	'configFunc'	=> 'TodoyuProjectFilter::prepareDataForProjectroleWidget'
);


	// Add portal tab: 'todos'
TodoyuPortalManager::addTab('todo', 'TodoyuProjectPortalRenderer::getTodoTabLabel', 'TodoyuProjectPortalRenderer::renderTodoTabContent', 20, array('project/public', 'project/portal'));



/**
 * Configuration for 'todo' tab
 *
 * @see	ext/project/config/filters.php	(all filter declarations)
 */
Todoyu::$CONFIG['EXT']['project']['portalTodoTabFilters'] = array(
	array(
		'filter'	=> 'currentPersonAssigned'
	),
	array(
		'filter'	=> 'status',
		'value'		=> STATUS_OPEN . ',' . STATUS_PROGRESS
	)
);

	// Max projects in project listig widget
Todoyu::$CONFIG['EXT']['project']['panelWidgetProjectListing']['maxProjects']	= 30;

/**
 * Default 'task defaults'. Will be overridden with extconf values of set
 */
Todoyu::$CONFIG['EXT']['project']['taskDefaults'] = array(
	'estimatedWorkload'		=> 0,
	'status'				=> STATUS_PLANNING,
	'statusQuickTask'		=> STATUS_OPEN
);

?>