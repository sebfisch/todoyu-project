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


/* ---------------------------------------------
	Add autocompleters for project data types
   --------------------------------------------- */
	// Person
TodoyuAutocompleter::addAutocompleter('projectperson', 'TodoyuPersonFilterDataSource::autocompletePersons', array('project', 'general:use'));
	// Task
TodoyuAutocompleter::addAutocompleter('projecttask', 'TodoyuTaskViewHelper::getProjecttaskAutocomplete', array('project', 'general:use'));
	// Project
TodoyuAutocompleter::addAutocompleter('project', 'TodoyuProjectFilterDataSource::autocompleteProjects', array('project', 'general:use'));
	// Project that tasks can be added to
TodoyuAutocompleter::addAutocompleter('taskaddableproject', 'TodoyuProjectFilterDataSource::autocompleteTaskAddableProjects', array('project', 'general:use'));



/* ----------------------------
	Context Menu Callbacks
   ---------------------------- */
TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskManager::getContextMenuItems', 10);
TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskClipboard::getTaskContextMenuItems', 100);
TodoyuContextMenuManager::addFunction('Task', 'TodoyuTaskManager::removeEmptyContextMenuParents', 100000);
TodoyuContextMenuManager::addFunction('Project', 'TodoyuProjectManager::getContextMenuItems', 10);
TodoyuContextMenuManager::addFunction('Project', 'TodoyuTaskClipboard::getProjectContextMenuItems', 100);



/* --------------------------------------
	Declare project sub type statuses
   -------------------------------------- */
	// Project status
Todoyu::$CONFIG['EXT']['project']['STATUS']['PROJECT'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_PROGRESS		=> 'progress',
	STATUS_DONE			=> 'done',
	STATUS_WARRANTY		=> 'warranty',
	STATUS_CLEARED		=> 'cleared',
);
	// Task status
Todoyu::$CONFIG['EXT']['project']['STATUS']['TASK'] = array(
	STATUS_PLANNING		=> 'planning',
	STATUS_OPEN			=> 'open',
	STATUS_PROGRESS		=> 'progress',
	STATUS_CONFIRM		=> 'confirm',
	STATUS_DONE			=> 'done',
	STATUS_ACCEPTED		=> 'accepted',
	STATUS_REJECTED		=> 'rejected',
	STATUS_CLEARED		=> 'cleared'
);
	// Non-editable project status (tasks/containers in project cannot be modified)
Todoyu::$CONFIG['EXT']['project']['projectStatusDisallowChildrenEditing'] = array(
	STATUS_DONE,
	STATUS_CLEARED,
);



/* --------------------------------------
	Temporary tab force for all tasks
 	Don't set it here!
   -------------------------------------- */
/**
 * @todo	implement sustainable solution
 */
Todoyu::$CONFIG['EXT']['project']['Task']['forceTab'] = false;



/* ----------------------------
	Add search filter widgets
   ---------------------------- */
	// Projectrole
Todoyu::$CONFIG['EXT']['search']['widgettypes']['projectrole'] =array(
	'tmpl'			=> 'ext/project/view/filterwidget-projectrole.tmpl',
	'configFunc'	=> 'TodoyuProjectFilter::prepareDataForProjectroleWidget'
);


/* ------------------------------
	Filters used in "todo" tab
   ------------------------------ */
	// Assigned tasks to be worked on
Todoyu::$CONFIG['EXT']['project']['portalTodoTabFilters']['assigned'] = array(
	array(
		'filter'	=> 'type',
		'value'		=> TASK_TYPE_TASK
	),
	array(
		'filter'	=> 'currentPersonAssigned'
	),
	array(
		'filter'	=> 'status',
		'value'		=> STATUS_OPEN . ',' . STATUS_PROGRESS
	)
);
	// Tasks the current user has to review and confirm
Todoyu::$CONFIG['EXT']['project']['portalTodoTabFilters']['owner'] = array(
	array(
		'filter'	=> 'type',
		'value'		=> TASK_TYPE_TASK
	),
	array(
		'filter'	=> 'currentPersonOwner'
	),
	array(
		'filter'	=> 'status',
		'value'		=> STATUS_CONFIRM
	)
);



/* ---------------------------------------------------------------
	Task default values. Overridden with extConf values if set
   --------------------------------------------------------------- */
Todoyu::$CONFIG['EXT']['project']['taskDefaults'] = array(
	'status'			=> STATUS_PLANNING,
	'statusQuickTask'	=> STATUS_OPEN
);
	// Duration (timespan from date_start to date_end/deadline) of quicktasks
Todoyu::$CONFIG['EXT']['project']['quicktask']['durationDays']  = 3;



/* ----------------------------
	Configure panel widgets
   ---------------------------- */
	// Maximum projects in project listing widget
Todoyu::$CONFIG['EXT']['project']['panelWidgetProjectList']['maxProjects']	= 30;



/* -----------------------
	Add filter exports
   ----------------------- */
TodoyuSearchActionPanelManager::addExport('task', 'csvexport', 'TodoyuTaskExportManager::exportCSV', 'LLL:task.export.csv', 'taskExportCsv', 'project:export.taskcsv');
TodoyuSearchActionPanelManager::addExport('project', 'csvexport', 'TodoyuProjectExportManager::exportCSV', 'LLL:project.export.csv', 'projectExportCsv', 'project:export.projectcsv');

?>
