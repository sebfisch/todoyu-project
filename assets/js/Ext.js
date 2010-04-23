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
 *	Ext: project
 */

Todoyu.Ext.project = {

	PanelWidget: {},

	Headlet: {},



	/**
	 * Initialization
	 */
	init: function() {
		this.registerHooks();

		this.Portal.init();
	},


	
	/**
	 * Register callbacks to JS hooks
	 */
	registerHooks: function() {
			// Add project creation hooks
		Todoyu.Hook.add('onProjectCreated', this.Project.Edit.onProjectCreated.bind(this.Project.Edit));
		
//		Todoyu.Hook.add('onTaskSaved', this.Task.onProjectTaskAdded(response);
	},

	
	
	/**
	 * To be called from other areas (e.g portal) to jump to a specific task within its project,
	 *	to be shown inside the project area
	 *
	 * @param	{Integer}	idTask
	 * @param	{Integer}	idProject
	 */
	goToTaskInProject: function(idTask, idProject) {
		var params = {
			'task': idTask
		};
		if( ! Object.isUndefined(idProject) ) {
			params.project = idProject;
		}

		Todoyu.goTo('project', 'ext', params, 'task-' + idTask);
	},



	/**
	 * Toggle task tree of given project
	 *
	 * @param	{Integer}	idProject
	 */
	toggleTaskTree: function(idProject) {
		this.TaskTree.toggle();

		Todoyu.Helper.toggleImage(
			'project-' + idProject + '-tasktreetoggle-image',
			'assets/img/toggle_plus.png',
			'assets/img/toggle_minus.png'
		);
	},



	/**
	 * Event handler: 'onTreeUpdate'
	 *
	 * @param	unknown	response
	 */
	onTreeUpdate: function(response) {
		this.ContextMenuTask.attach.bindAsEventListener(this.ContextMenuTask)();
		this.ContextMenuProject.attach.bindAsEventListener(this.ContextMenuProject)();

		if( response.getHeader('Todoyu-hash') ) {
			window.location.hash = response.getHeader('Todoyu-hash');
		}
	},



	/**
	 * Attach project context menu
	 */
	attachContextMenu: function() {
		this.ContextMenuProject.attach();
		this.ContextMenuTask.attach();
	},



	/**
	 * Highlight the project tree
	 */
	highlightProjecttree: function() {
		Effect.Shake('panelwidget-projecttree');
		new Effect.Highlight('panelwidget-projecttree');
	},



	/**
	 * Save project pref
	 *
	 * @param	{String}	preference
	 * @param	{String}	value
	 * @param	{Integer}	idItem
	 * @param	{String}	onComplete
	 */
	savePref: function(preference, value, idItem, onComplete) {
		Todoyu.Pref.save('project', preference, value, idItem, onComplete);
	}

};