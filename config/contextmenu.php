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
 * Context menu configuration for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

	/**
	 * Context menu configuration for task
	 */

Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Task'] = array(
	'showinproject'	=> array(
		'key'		=> 'showinproject',
		'label'		=> 'task.contextmenu.showinproject',
		'jsAction'	=> 'Todoyu.Ext.project.goToTaskInProject(#ID#)',
		'class'		=> 'task-ctxmenu task-showinproject',
		'position'	=> 10
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'task.contextmenu.edit',
		'jsAction'	=> 'Todoyu.Ext.project.Task.edit(#ID#)',
		'class'		=> 'taskContextMenu taskEdit',
		'position'	=> 20
	),
	'actions' => array(
		'key'		=> 'actions',
		'label'		=> 'task.contextmenu.actions',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu taskActions',
		'position'	=> 30,
		'submenu'	=> array(
			'copy'	=> array(
				'key'		=> 'copy',
				'label'		=> 'task.contextmenu.copy',
				'jsAction'	=> 'Todoyu.Ext.project.Task.copy(#ID#)',
				'class'		=> 'taskContextMenu taskCopy',
				'position'	=> 10
			),
			'cut'	=> array(
				'key'		=> 'cut',
				'label'		=> 'task.contextmenu.cut',
				'jsAction'	=> 'Todoyu.Ext.project.Task.cut(#ID#)',
				'class'		=> 'taskContextMenu taskCut',
				'position'	=> 20
			),
			'clone'	=> array(
				'key'		=> 'clone',
				'label'		=> 'task.contextmenu.clone',
				'jsAction'	=> 'Todoyu.Ext.project.Task.clone(#ID#)',
				'class'		=> 'taskContextMenu taskClone',
				'position'	=> 30
			),
			'delete'	=> array(
				'key'		=> 'delete',
				'label'		=> 'task.contextmenu.delete',
				'jsAction'	=> 'Todoyu.Ext.project.Task.remove(#ID#)',
				'class'		=> 'taskContextMenu taskDelete',
				'position'	=> 40
			)
		)
	),
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'task.contextmenu.add',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu taskAdd',
		'position'	=> 40,
		'submenu'	=> array(
			'task'	=> array(
				'key'		=> 'add-task',
				'label'		=> 'task.contextmenu.add.task',
				'jsAction'	=> 'Todoyu.Ext.project.Task.addSubTask(#ID#)',
				'class'		=> 'taskContextMenu taskAddTask'
			),
			'container'	=> array(
				'key'		=> 'add-container',
				'label'		=> 'task.contextmenu.add.container',
				'jsAction'	=> 'Todoyu.Ext.project.Task.addSubContainer(#ID#)',
				'class'		=> 'taskContextMenu taskAddContainer'
			)
		)
	),
	'status' => array(
		'key'		=> 'status',
		'label'		=> 'task.contextmenu.status.change',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu taskStatus',
		'position'	=> 50,
		'submenu'	=> array(
			'planning'	=> array(
				'key'		=> 'status-planning',
				'label'		=> 'task.status.planning',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_PLANNING . ')',
				'class'		=> 'taskContextMenu taskStatusPlanning'
			),
			'open'	=> array(
				'key'		=> 'status-open',
				'label'		=> 'task.status.open',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_OPEN . ')',
				'class'		=> 'taskContextMenu taskStatusOpen'
			),
			'progress'	=> array(
				'key'		=> 'status-progress',
				'label'		=> 'task.status.progress',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_PROGRESS . ')',
				'class'		=> 'taskContextMenu taskStatusProgress'
			),
			'confirm'	=> array(
				'key'		=> 'status-confirm',
				'label'		=> 'task.status.confirm',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_CONFIRM . ')',
				'class'		=> 'taskContextMenu taskStatusConfirm'
			),
			'done'	=> array(
				'key'		=> 'status-done',
				'label'		=> 'task.status.done',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_DONE . ')',
				'class'		=> 'taskContextMenu taskStatusDone'
			),
			'accepted'	=> array(
				'key'		=> 'status-accepted',
				'label'		=> 'task.status.accepted',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_ACCEPTED . ')',
				'class'		=> 'taskContextMenu taskStatusAccepted'
			),
			'rejected'	=> array(
				'key'		=> 'status-rejected',
				'label'		=> 'task.status.rejected',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_REJECTED . ')',
				'class'		=> 'taskContextMenu taskStatusRejected'
			),
			'cleared'	=> array(
				'key'		=> 'status-cleared',
				'label'		=> 'task.status.cleared',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_CLEARED . ')',
				'class'		=> 'taskContextMenu taskStatusCleared'
			),
			'warranty'	=> array(
				'key'		=> 'status-warranty',
				'label'		=> 'task.status.warranty',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_WARRANTY . ')',
				'class'		=> 'taskContextMenu taskStatusWarranty'
			),
			'customer'	=> array(
				'key'		=> 'status-customer',
				'label'		=> 'task.status.customer',
				'jsAction'	=> 'Todoyu.Ext.project.Task.updateStatus(#ID#, ' . STATUS_CUSTOMER . ')',
				'class'		=> 'taskContextMenu taskStatusCustomer'
			)
		)
	)
);





	/**
	 * Context menu configuration for container
	 */

Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Container'] = array(
	'showinproject'	=> array(
		'key'		=> 'showinproject',
		'label'		=> 'task.contextmenu.showinproject',
		'jsAction'	=> 'Todoyu.Ext.project.goToTaskInProject(#ID#)',
		'class'		=> 'task-ctxmenu task-showinproject',
		'position'	=> 10
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'task.contextmenu.container.edit',
		'jsAction'	=> 'Todoyu.Ext.project.Container.edit(#ID#)',
		'class'		=> 'taskContextMenu containerEdit',
		'position'	=> 20
	),
	'actions' => array(
		'key'		=> 'actions',
		'label'		=> 'task.contextmenu.container.actions',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu containerActions',
		'position'	=> 30,
		'submenu'	=> array(
			'copy'	=> array(
				'key'		=> 'copy',
				'label'		=> 'task.contextmenu.container.copy',
				'jsAction'	=> 'Todoyu.Ext.project.Container.copy(#ID#)',
				'class'		=> 'taskContextMenu containerCopy',
				'position'	=> 10
			),
			'cut'	=> array(
				'key'		=> 'cut',
				'label'		=> 'task.contextmenu.container.cut',
				'jsAction'	=> 'Todoyu.Ext.project.Container.cut(#ID#)',
				'class'		=> 'taskContextMenu containerCut',
				'position'	=> 20
			),
			'clone'	=> array(
				'key'		=> 'clone',
				'label'		=> 'task.contextmenu.container.clone',
				'jsAction'	=> 'Todoyu.Ext.project.Container.clone(#ID#)',
				'class'		=> 'taskContextMenu containerClone',
				'position'	=> 30
			),
			'delete'	=> array(
				'key'		=> 'delete',
				'label'		=> 'task.contextmenu.container.delete',
				'jsAction'	=> 'Todoyu.Ext.project.Container.remove(#ID#)',
				'class'		=> 'taskContextMenu containerDelete',
				'position'	=> 40
			)
		)
	),
	'add'	=> array(
		'key'		=> 'add',
		'label'		=> 'task.contextmenu.add',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu containerAdd',
		'position'	=> 40,
		'submenu'	=> array(
			'task'	=> array(
				'key'		=> 'add-task',
				'label'		=> 'task.contextmenu.add.task',
				'jsAction'	=> 'Todoyu.Ext.project.Container.addSubTask(#ID#)',
				'class'		=> 'taskContextMenu containerAddTask'
			),
			'container'	=> array(
				'key'		=> 'add-container',
				'label'		=> 'task.contextmenu.add.container',
				'jsAction'	=> 'Todoyu.Ext.project.Container.addSubContainer(#ID#)',
				'class'		=> 'taskContextMenu containerAddContainer'
			)
		)
	),
	'status' => array(
		'key'		=> 'status',
		'label'		=> 'task.contextmenu.status.change',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu taskStatus',
		'position'	=> 50,
		'submenu'	=> array(
			'progress'	=> array(
				'key'		=> 'status-progress',
				'label'		=> 'task.status.progress',
				'jsAction'	=> 'Todoyu.Ext.project.Container.updateStatus(#ID#, ' . STATUS_PROGRESS . ')',
				'class'		=> 'taskContextMenu projectStatusProgress'
			),
			'done'	=> array(
				'key'		=> 'status-done',
				'label'		=> 'task.status.done',
				'jsAction'	=> 'Todoyu.Ext.project.Container.updateStatus(#ID#, ' . STATUS_DONE . ')',
				'class'		=> 'taskContextMenu projectStatusDone'
			),
			'cleared'	=> array(
				'key'		=> 'status-cleared',
				'label'		=> 'task.status.cleared',
				'jsAction'	=> 'Todoyu.Ext.project.Container.updateStatus(#ID#, ' . STATUS_CLEARED . ')',
				'class'		=> 'taskContextMenu projectStatusCleared'
			)
		)
	)
);






	/**
	 * Context menu configuration for project
	 */

Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Project'] = array(
	'showinproject'	=> array(
		'key'		=> 'showinproject',
		'label'		=> 'project.contextmenu.showinproject',
		'jsAction'	=> 'Todoyu.Ext.project.goToTaskInProject(#ID#)',
		'class'		=> 'projectContextMenu showInProject',
		'position'	=> 10
	),
	'showdetails'	=> array(
		'key'		=> 'showdetails',
		'label'		=> 'project.contextmenu.showdetails',
		'jsAction'	=> 'Todoyu.Ext.project.Project.toggleDetails(#ID#)',
		'class'		=> 'projectContextMenu projectDetails',
		'position'	=> 10
	),
	'hidedetails'	=> array(
		'key'		=> 'hidedetails',
		'label'		=> 'project.contextmenu.hidedetails',
		'jsAction'	=> 'Todoyu.Ext.project.Project.toggleDetails(#ID#)',
		'class'		=> 'projectContextMenu projectDetails',
		'position'	=> 11
	),
	'edit'	=> array(
		'key'		=> 'edit',
		'label'		=> 'project.contextmenu.edit',
		'jsAction'	=> 'Todoyu.Ext.project.Project.edit(#ID#)',
		'class'		=> 'projectContextMenu projectEdit',
		'position'	=> 20
	),
	'delete' => array(
		'key'		=> 'delete',
		'label'		=> 'project.contextmenu.delete',
		'jsAction'	=> 'Todoyu.Ext.project.Project.remove(#ID#)',
		'class'		=> 'projectContextMenu projectDelete',
		'position'	=> 25
	),
	'status' => array(
		'key'		=> 'status',
		'label'		=> 'task.contextmenu.status.change',
		'jsAction'	=> 'void(0)',
		'class'		=> 'projectContextMenu projectStatus',
		'position'	=> 30,
		'submenu'	=> array(
			'planning'	=> array(
				'key'		=> 'status-planning',
				'label'		=> 'task.status.planning',
				'jsAction'	=> 'Todoyu.Ext.project.Project.updateStatus(#ID#, ' . STATUS_PLANNING . ')',
				'class'		=> 'projectContextMenu projectStatusPlanning'
			),
			'progress'	=> array(
				'key'		=> 'status-progress',
				'label'		=> 'task.status.progress',
				'jsAction'	=> 'Todoyu.Ext.project.Project.updateStatus(#ID#, ' . STATUS_PROGRESS . ')',
				'class'		=> 'projectContextMenu projectStatusProgress'
			),
			'done'	=> array(
				'key'		=> 'status-done',
				'label'		=> 'task.status.done',
				'jsAction'	=> 'Todoyu.Ext.project.Project.updateStatus(#ID#, ' . STATUS_DONE . ')',
				'class'		=> 'projectContextMenu projectStatusDone'
			),
			'cleared'	=> array(
				'key'		=> 'status-cleared',
				'label'		=> 'task.status.cleared',
				'jsAction'	=> 'Todoyu.Ext.project.Project.updateStatus(#ID#, ' . STATUS_CLEARED . ')',
				'class'		=> 'projectContextMenu projectStatusCleared'
			),
			'warranty'	=> array(
				'key'		=> 'status-warranty',
				'label'		=> 'task.status.warranty',
				'jsAction'	=> 'Todoyu.Ext.project.Project.updateStatus(#ID#, ' . STATUS_WARRANTY . ')',
				'class'		=> 'projectContextMenu projectStatusWarranty'
			)
		)
	),
	'addtask'	=> array(
		'key'		=> 'addtask',
		'label'		=> 'project.contextmenu.add.task',
		'jsAction'	=> 'Todoyu.Ext.project.Project.addTask(#ID#)',
		'class'		=> 'projectContextMenu projectAddTask',
		'position'	=> 40
	),
	'addcontainer'	=> array(
		'key'		=> 'addcontainer',
		'label'		=> 'project.contextmenu.add.container',
		'jsAction'	=> 'Todoyu.Ext.project.Project.addContainer(#ID#)',
		'class'		=> 'projectContextMenu projectAddContainer',
		'position'	=> 50
	)
);



	/**
	 * Context menu configuration task clipboard functions
	 */

Todoyu::$CONFIG['EXT']['project']['ContextMenu']['TaskClipboard'] = array(
	'paste'	=> array(
		'key'		=> 'paste',
		'label'		=> 'task.contextmenu.paste',
		'jsAction'	=> 'void(0)',
		'class'		=> 'taskContextMenu taskPaste',
		'position'	=> 35,
		'submenu'	=> array(
			'in'	=> array(
				'key'		=> 'paste-in',
				'label'		=> 'task.contextmenu.paste.in',
				'jsAction'	=> 'Todoyu.Ext.project.Task.paste(#ID#, \'in\')',
				'class'		=> 'taskContextMenu taskPasteIn'
			),
			'before'	=> array(
				'key'		=> 'paste-before',
				'label'		=> 'task.contextmenu.paste.before',
				'jsAction'	=> 'Todoyu.Ext.project.Task.paste(#ID#, \'before\')',
				'class'		=> 'taskContextMenu taskPasteBefore'
			),
			'after'	=> array(
				'key'		=> 'paste-after',
				'label'		=> 'task.contextmenu.paste.after',
				'jsAction'	=> 'Todoyu.Ext.project.Task.paste(#ID#, \'after\')',
				'class'		=> 'taskContextMenu taskPasteAfter'
			)
		)
	)
);

?>