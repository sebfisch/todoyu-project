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
 *	Ext: project
 */

Todoyu.Ext.project = {

	PanelWidget: {},

	Headlet: {},


	/**
	 * Initialization
	 */
	init: function() {
			// Add project creation hooks
		Todoyu.Hook.add('onProjectCreated', this.Project.Edit.onCreated.bind(this.ProjectEdit));
	},



	/**
	 *	To be called from other areas (e.g portal) to jump to a specific task within its project,
	 *	to be shown inside the project area
	 *
	 *	@param	Integer	idTask
	 *	@param	Integer	idProject
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
	 *	Toggle task tree of given project
	 *
	 *	@param	Integer	idProject
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
	 *	Show project tree of given project / task
	 *
	 *	@param	Integer	idProject
	 *	@param	Integer	idTask
	 */
	showProjectTree: function(idProject, idTask) {
		var url		= Todoyu.getUrl('project', 'projecttasktree');
		var options = {
			'parameters': {
				'project':	idProject,
				'task':		Todoyu.Helper.intval(idTask)
			},
			'onComplete': 	this.onTreeUpdate.bind(this)
		};

		this.ContextMenuTask.detach();
		this.ContextMenuProject.detach();

		Todoyu.Ui.updateContent(url, options);
	},



	/**
	 *	Event handler: 'onTreeUpdate'
	 *
	 *	@param	unknown	response
	 */
	onTreeUpdate: function(response) {
		this.ContextMenuTask.attach.bindAsEventListener(this.ContextMenuTask)();
		this.ContextMenuProject.attach.bindAsEventListener(this.ContextMenuProject)();

		if( response.getHeader('Todoyu-hash') ) {
			window.location.hash = response.getHeader('Todoyu-hash');
		}
	},



	/**
	 *	Attach project context menu
	 */
	attachContextMenu: function() {
		this.ContextMenuProject.attach();
		this.ContextMenuTask.attach();
	},	
	


	/**
	 *	Add the very first project
	 */
	addFirstProject: function() {
		var url		= Todoyu.getUrl('project', 'project');
		var options	= {
			'parameters': {
				'action':	'addfirst'
			},
			'onComplete': this.onFirstProjectAdded.bind(this)
		};
		var target	= 'project-0';

			// Set tab label
		$('projecttab-noselection').writeAttribute('id', 'projecttab-0');
		$('projecttab-noselection-label').writeAttribute('id', 'projecttab-0-label');
		$('projecttab-0-label').down('.labeltext', 0).update('[LLL:project.newproject.tab]');

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 *	Custom event handler, being evoked after creation of the very first project
	 *
	 *	@param	unknown	response
	 */
	onFirstProjectAdded: function(response) {
		
	},



	/**
	 * Highlight the project tree
	 */
	highlightProjecttree: function() {		
		Effect.Shake('panelwidget-projecttree');
		new Effect.Highlight('panelwidget-projecttree');
	},



	/**
	 *	Save project pref
	 *
	 *	@param	String	preference
	 *	@param	String	value
	 *	@param	Integer	idItem
	 *	@param	String	onComplete
	 */
	savePref: function(preference, value, idItem, onComplete) {
		Todoyu.Pref.save('project', preference, value, idItem, onComplete);
	}

};