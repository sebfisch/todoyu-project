<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Assets (JS, CSS) requirements for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

Todoyu::$CONFIG['EXT']['project']['assets'] = array(
	'js' => array(
		array(
			'file'		=> 'ext/project/assets/js/Ext.js',
			'position'	=> 80
		),
		array(
			'file'		=> 'ext/project/assets/js/Project.js',
			'position'	=> 81
		),
		array(
			'file'		=> 'ext/project/assets/js/QuickTask.js',
			'position'	=> 109
		),
		array(
			'file'		=> 'ext/project/assets/js/HeadletQuickTask.js',
			'position'	=> 90
		),
			// Add creation engines to quick create headlet
		array(
			'file'		=> 'ext/project/assets/js/QuickCreateProject.js',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/project/assets/js/QuickCreateTask.js',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/project/assets/js/ProjectEdit.js',
			'position'	=> 102
		),
		array(
			'file'		=> 'ext/project/assets/js/Task.js',
			'position'	=> 102
		),
		array(
			'file'		=> 'ext/project/assets/js/TaskEdit.js',
			'position'	=> 102
		),
		array(
			'file'		=> 'ext/project/assets/js/TaskTab.js',
			'position'	=> 102
		),
		array(
			'file'		=> 'ext/project/assets/js/Container.js',
			'position'	=> 103
		),
		array(
			'file'		=> 'ext/project/assets/js/TaskTree.js',
			'position'	=> 104
		),
		array(
			'file'		=> 'ext/project/assets/js/ContextMenuTask.js',
			'position'	=> 105
		),
		array(
			'file'		=> 'ext/project/assets/js/ContextMenuProject.js',
			'position'	=> 106
		),
		array(
			'file'		=> 'ext/project/assets/js/ProjectTaskTree.js',
			'position'	=> 107
		),
		array(
			'file'		=> 'ext/project/assets/js/TaskParentAc.js',
			'position'	=> 108
		),
		array(
			'file'		=> 'ext/project/assets/js/hooks.js',
			'position'	=> 1000
		),
		array(
			'file'		=> 'ext/project/assets/js/Filter.js',
			'position'	=> 200
		),
		array(
			'file'		=> 'ext/project/assets/js/PanelWidgetProjectList.js',
			'position'	=> 110
		),
		array(
			'file' => 'ext/project/assets/js/PanelWidgetTaskStatusFilter.js',
			'position' => 120,
		),
		array(
			'file' => 'ext/project/assets/js/PanelWidgetProjectStatusFilter.js',
			'position' => 120,
		),
		array(
			'file' => 'ext/project/assets/js/Portal.js',
			'position' => 120,
		)
	),
	'css' => array(
		array(
			'file'		=> 'ext/project/assets/css/headlet-quicktask.css',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/project/assets/css/ext.css',
			'media'		=> 'all',
			'position'	=> 100
		),
		array(
			'file'		=> 'ext/project/assets/css/task.css',
			'position'	=> 101
		),
		array(
			'file'		=> 'ext/project/assets/css/project.css',
			'position'	=> 102
		),
		array(
			'file'		=> 'ext/project/assets/css/contextmenu.css',
			'position'	=> 103
		),
		array(
			'file'		=> 'ext/project/assets/css/taskparent-ac.css',
			'position'	=> 104
		),
		array(
			'file'		=> 'ext/project/assets/css/panelwidget-projectlist.css',
			'media'		=> 'all',
			'position'	=> 110
		),
		array(
			'file' => 'ext/project/assets/css/panelwidget-statusfilter.css',
			'position' => 120,
		),
		array(
			'file' => 'ext/project/assets/css/panelwidget-taskstatusfilter.css',
			'position' => 120,
		),
		array(
			'file' => 'ext/project/assets/css/panelwidget-projectstatusfilter.css',
			'position' => 120,
		)
	)
);

?>