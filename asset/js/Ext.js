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

/**
 * @module	Project
 */

/**
 * Main project object
 *
 * @class		project
 * @namespace	Todoyu.Ext
 */
Todoyu.Ext.project = {

	/**
	 * @property	PanelWidget
	 * @type		Object
	 */
	PanelWidget: {},

	/**
	 * @property	Headlet
	 * @type		Object
	 */
	Headlet: {},



	/**
	 * Initialization
	 *
	 * @method	init
	 */
	init: function() {
		this.registerHooks();

		if( Todoyu.getArea() === 'portal' ) {
			this.Portal.init();
		}
	},



	/**
	 * Register callbacks to JS hooks
	 *
	 * @method	registerHooks
	 */
	registerHooks: function() {
		Todoyu.Hook.add('project.project.created', this.Project.Edit.onProjectCreated.bind(this.Project.Edit));
//		Todoyu.Hook.add('project.task.saved', this.Task.onProjectTaskAdded(response);

			// Register area specific callbacks
		if( Todoyu.getArea() === 'project' ) {
			Todoyu.Hook.add('panelwidget.projectlist.onProjectClick', this.ProjectTaskTree.onPanelwidgetProjectlistProjectClick.bind(this.ProjectTaskTree));
		}
	},



	/**
	 * To be called from other areas (e.g portal) to jump to a specific task within its project,
	 *	to be shown inside the project area
	 *
	 * @method	goToTaskInProject
	 * @param	{Number}	idTask
	 * @param	{Number}	idProject
	 * @param	{Boolean}	newWindow
	 * @param	{String}	windowName
	 */
	goToTaskInProject: function(idTask, idProject, newWindow, windowName) {
		newWindow	= newWindow ? newWindow : false;
		windowName	= windowName ? windowName : '';

		var params = {
			'task': idTask
		};
		if( ! Object.isUndefined(idProject) ) {
			params.project = idProject;
		}

		Todoyu.goTo('project', 'ext', params, 'task-' + idTask, newWindow, windowName);
	},



	/**
	 * Go to a task in project view, if you have only the full tasknumber (no task ID)
	 * Gets the task ID by AJAX and redirects the browser
	 *
	 * @method	goToTaskInProjectByTasknumber
	 * @param	{String}	taskNumber
	 */
	goToTaskInProjectByTasknumber: function(taskNumber) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:		'number2id',
				tasknumber: taskNumber
			},
			onComplete: this.onGoToTaskInProjectByTasknumber.bind(this, taskNumber)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler for task IDs request
	 * responseText is the task ID
	 *
	 * @method	onGoToTaskInProjectByTasknumber
	 * @param	{String}		taskNumber
	 * @param	{Ajax.Response}	response
	 */
	onGoToTaskInProjectByTasknumber: function(taskNumber, response) {
		var idTask	= parseInt(response.responseText, 10);

		this.goToTaskInProject(idTask);
	},



	/**
	 * Toggle task tree of given project
	 *
	 * @method	toggleTaskTree
	 * @param	{Number}	idProject
	 */
	toggleTaskTree: function(idProject) {
		this.TaskTree.toggle();

		Todoyu.Helper.toggleImage(
			'project-' + idProject + '-tasktreetoggle-image',
			'asset/img/toggle_plus.png',
			'asset/img/toggle_minus.png'
		);
	},



	/**
	 * Event handler: 'onTreeUpdate'
	 *
	 * @method	onTreeUpdate
	 * @param	{Ajax.Response}		response
	 */
	onTreeUpdate: function(response) {
		this.ContextMenuTask.attach.bindAsEventListener(this.ContextMenuTask)();
		this.ContextMenuProject.attach.bindAsEventListener(this.ContextMenuProject)();

		if( response.getHeader('Todoyu-hash') ) {
			window.location.hash = response.getHeader('Todoyu-hash');
		}
	},



	/**
	 * Attach project and task context menus
	 *
	 * @method	attachContextMenu
	 */
	attachContextMenu: function() {
		this.ContextMenuProject.attach();
		this.ContextMenuTask.attach();
	},



	/**
	 * Save project pref
	 *
	 * @method	savePref
	 * @param	{String}	preference
	 * @param	{String}	value
	 * @param	{Number}	idItem
	 * @param	{String}	onComplete
	 */
	savePref: function(preference, value, idItem, onComplete) {
		Todoyu.Pref.save('project', preference, value, idItem, onComplete);
	},



	/**
	 * Open popup with quick create for project
	 *
	 * @method	openProjectPopup
	 */
	openProjectPopup: function() {
		Todoyu.Headlets.getHeadlet('todoyuheadletquickcreate').openTypePopup('project','project')
	}

};