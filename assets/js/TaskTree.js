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

Todoyu.Ext.project.TaskTree = {

	ext: Todoyu.Ext.project,



	/**
	 * Enter description here...
	 *
	 */
	init: function() {
		this.installObservers();
	},



	/**
	 * Enter description here...
	 *
	 */
	installObservers: function() {
		Todoyu.PanelWidget.observe('statusfilter', this.onStatusFilterUpdate.bind(this));
	},


	/**
	 * Enter description here...
	 *
	 * @param unknown_type idProject
	 */
	tree: function(idProject) {
		return $('project-' + idProject + '-tasks');
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idProject
	 */
	toggle: function(idProject) {
		this.tree(idProject).toggle();
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idProject
	 */
	hide: function(idProject) {
		var taskTree = this.tree(idProject);

		if (taskTree) {
			taskTree.hide();
		}
	},


	/**
	 * Update task tree with a new filter configuration
	 * @param	Integer		idProject
	 * @param	String		filterName
	 * @param	String		filterValue
	 */
	update: function(idProject, filterName, filterValue) {
		var url		= Todoyu.getUrl('project', 'tasktree');
		var options	= {
			'parameters': {
				'cmd': 'update',
				'project': idProject
			},
			'onComplete': this.onUpdated.bind(this)
		};
		var target	= 'project-' + idProject + '-tasks';

		if( typeof(filterName) !== 'undefined' ) {
			options.parameters["filter[name]"] = filterName;
			options.parameters["filter[value]"] = filterValue;
		}

		Todoyu.Ui.update(target, url, options);
	},


	/**
	 * Enter description here...
	 *
	 * @param unknown_type response
	 */
	onUpdated: function(response) {
		this.addContextMenu();
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type widgetName
	 * @param unknown_type params
	 */
	onStatusFilterUpdate: function(widgetName, params) {
		var idProject 	= this.getProjectID();
		var filterValue	= params.join(',');

		this.update(idProject, 'status', filterValue);
	},



	/**
	 * Enter description here...
	 *
	 */
	getProjectID: function() {
		return this.ext.ProjectTaskTree.getActiveProjectID();
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 */
	toggleSubtasks: function(idTask) {
		var newLoaded = false;

			// Toggle expanding icon
		this.toggleSubtaskTriggerIcon(idTask);

			// Load subtasks if they are not already loaded
		if( ! this.areSubtasksLoaded(idTask) ) {
			this.loadSubtasks(idTask);
			newLoaded = true;
		}

		var subtasks = $('task-' + idTask + '-subtasks');

			// Toggle subtasks
		$('task-' + idTask + '-subtasks').toggle();
		$('task-' + idTask + '-subtasks').toggleClassName('expanded');
		//subtasks.visible() ? this.closeSubtasks(idTask) : this.openSubtasks(idTask) ;

			// Save status
		if( ! newLoaded ) {
			this.saveSubtaskOpenStatus(idTask, subtasks.visible());
		}
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 */
	toggleSubtaskTriggerIcon: function(idTask) {
		$('task-' + idTask + '-subtasks-trigger').toggleClassName('expanded');
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 */
	areSubtasksLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks');
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 */
	areSubtasksVisible: function(idTask) {
		return $('task-' + idTask + '-subtasks').visible();
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 */
	loadSubtasks: function(idTask) {
		var url		= Todoyu.getUrl('project', 'subtasks');
		var options	= {
			'parameters': {
				'cmd': 'load',
				'task': idTask,
				'show': 0
			},
			'insertion': 'after',
			'asynchronous': false
		};
		var target	= 'task-' + idTask;

		Todoyu.Ui.update(target, url, options);

		Todoyu.Ext.project.ContextMenuTask.reattach();

		$('task-' + idTask + '-subtasks').hide();
	},



	/**
	 * Enter description here...
	 *
	 * @param unknown_type idTask
	 * @param unknown_type isOpen
	 */
	saveSubtaskOpenStatus: function(idTask, isOpen) {
		Todoyu.Pref.save('project', 'subtasks', isOpen?1:0, idTask);
	},



	/**
	 * Enter description here...
	 *
	 */
	addContextMenu: function() {
		Todoyu.Ext.project.ContextMenuTask.reattach();
	}
};