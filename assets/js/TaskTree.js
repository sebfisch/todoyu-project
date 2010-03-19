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

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Init project task tree
	 */
	init: function() {
		this.installObservers();
	},



	/**
	 * Install observers
	 */
	installObservers: function() {
		Todoyu.PanelWidget.observe('taskstatusfilter', this.onStatusFilterUpdate.bind(this));
	},



	/**
	 * Get tree's DOM element ID
	 *
	 * @param	Integer	idProject
	 */
	tree: function(idProject) {
		return $('project-' + idProject + '-tasks');
	},



	/**
	 * Toggle display of task tree of given project
	 *
	 * @param	unknown_type idProject
	 */
	toggle: function(idProject) {
		if ( this.tree(idProject) ) {
			this.tree(idProject).toggle();
		}
	},



	/**
	 * Hide task tree of given project
	 *
	 * @param	Integer	idProject
	 */
	hide: function(idProject) {
		var taskTree = this.tree(idProject);

		if (taskTree) {
			taskTree.hide();
		}
	},



	/**
	 * Update task tree with a new filter configuration
	 *
	 * @param	Integer		idProject
	 * @param	String		filterName
	 * 	param	String		filterValue
	 */
	update: function(idProject, filterName, filterValue) {
		if( Object.isUndefined(idProject) ) {
			idProject = this.ext.ProjectTaskTree.getActiveProjectID();
		}

		var url		= Todoyu.getUrl('project', 'tasktree');
		var options	= {
			'parameters': {
				'action':	'update',
				'project':	idProject
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
	 * Evoked after task tree updating has been completed. Adds the context menu.
	 *
	 * @param	Object	response
	 */
	onUpdated: function(response) {
		this.addContextMenu();
	},



	/**
	 * Evoked upon update of status filter: evokes update of given project's tree
	 *
	 * @param	String	widgetName
	 * @param	Array	params
	 */
	onStatusFilterUpdate: function(widgetName, params) {
		var idProject 	= this.getProjectID();
		var filterValue	= params.join(',');

		this.update(idProject, 'status', filterValue);
	},



	/**
	 * Get ID of currently active task tree project
	 *
	 * @return	Integer
	 */
	getProjectID: function() {
		return this.ext.ProjectTaskTree.getActiveProjectID();
	},
	
	

	/**
	 * Toggle display of sub tasks and save resulting display state of given given task inside the task tree(, load sub tasks if toggled to be shown and not loaded yet)
	 *
	 * @param Integer	idTask
	 */
	toggleSubtasks: function(idTask) {
			// Load subtasks if they are not already loaded
		if( ! this.areSubtasksLoaded(idTask) ) {
			this.loadSubtasks(idTask, this.onSubtasksToggled.bind(this));
		} else {
			$('task-' + idTask + '-subtasks').toggle();
			this.saveSubtaskOpenStatus(idTask, $('task-' + idTask + '-subtasks').visible());
			this.onSubtasksToggled(idTask);
		}

			// Toggle expanding icon
		this.toggleSubtaskTriggerIcon(idTask);
	},



	/**
	 * Handler when subtasks are toggled
	 * @param {Object} idTask
	 * @param {Object} response
	 */
	onSubtasksToggled: function(idTask) {

	},



	/**
	 * Expand subtasks of given task in task tree
	 *
	 * @param	Integer	idTask
	 */
	expandSubtasks: function(idTask) {
		if( ! this.areSubtasksVisible(idTask) ) {
			this.toggleSubtasks(idTask);
		}
	},



	/**
	 * Toggle expand-option icon of given task
	 *
	 * @param	Integer idTask
	 */
	toggleSubtaskTriggerIcon: function(idTask) {
		$('task-' + idTask + '-subtasks-trigger').toggleClassName('expanded');
	},



	/**
	 * Check whether sub tasks of given task are loaded (DOM elements exist)
	 *
	 * @param	Integer	idTask
	 * @return	Boolean
	 */
	areSubtasksLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks');
	},



	/**
	 * Check whether sub tasks of given task are set visible currently
	 *
	 * @param	Integer idTask
	 * @return	Boolean
	 */
	areSubtasksVisible: function(idTask) {
		return $('task-' + idTask + '-subtasks').visible();
	},



	/**
	 * Load subtasks
	 *
	 * @param	Integer		idTask
	 * @param	Function	callback
	 */
	loadSubtasks: function(idTask, callback) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'subtasks',
				'task':		idTask,
				'show':		0
			},
			'onComplete': this.onSubtasksLoaded.bind(this, idTask, callback)
		};
		var target	= 'task-' + idTask;

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handler when subtasks are loaded
	 *
	 * @param	Integer			idTask
	 * @param	Function		callback
	 * @param	Ajax.Response	response
	 */
	onSubtasksLoaded: function(idTask, callback, response) {
		Todoyu.Ext.project.ContextMenuTask.reattach();

		if( typeof callback === 'function' ) {
			callback(idTask, response);
		}
	},



	/**
	 * Save task tree sub tasks being opened status pref
	 *
	 * @param	Integer	idTask
	 * @param	Boolean	isOpen
	 */
	saveSubtaskOpenStatus: function(idTask, isOpen) {
		Todoyu.Pref.save('project', 'subtasks', isOpen?1:0, idTask);
	},



	/**
	 * Evoke (Re-)Adding of task tree (tasks') context menu
	 *
	 */
	addContextMenu: function() {
		Todoyu.Ext.project.ContextMenuTask.reattach();
	}

};