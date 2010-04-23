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
 * Extension: Project
 * Functions to handle projects
 */
Todoyu.Ext.project.Project = {

	/**
	 * Ext shortcut
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Get DOM element of header of project with given ID
	 *
	 * @param	{Integer}		idProject
	 * @return	DomElement
	 */
	getHeader: function(idProject) {
		return $('project-' + idProject + '-header');
	},



	/**
	 * Edit given project
	 *
	 * @param {Integer} idProject
	 */
	edit: function(idProject){
		if(Todoyu.getArea() != 'project') {
			Todoyu.goTo('project', 'ext', {
				'action': 'edit',
				'project': idProject
			});
		} 

		this.hideDetails(idProject);
		this.ext.TaskTree.hide(idProject);

		this.Edit.createFormWrapDivs(idProject);
		this.Edit.loadForm(idProject);
	},



	/**
	 * Delete given project
	 *
	 * @param	{Integer}	idProject
	 */
	remove: function(idProject) {
		if( confirm('[LLL:project.js.removeProject]') ) {
			var url		= Todoyu.getUrl('project', 'project');
			var options	= {
				'parameters': {
					'action':	'remove',
					'project':	idProject
				},
				'onComplete': this.onRemoved.bind(this, idProject)
			};

			Todoyu.send(url, options);
		}
	},



	/**
	 * Handle completion event after project having been deleted. Remove project from project task tree and remove project tab.
	 *
	 * @param	{Integer}		idProject
	 */
	onRemoved: function(idProject) {
		if(Todoyu.getArea() == 'project')	{
			this.ext.ProjectTaskTree.removeProject(idProject);
			this.ext.ProjectTaskTree.openFirstTab();
			this.removeProjectSubnaviItem(idProject);
		} else {
			$('project-'+idProject).fade();
		}
		
		Todoyu.Hook.exec('onProjectDeleted', idProject);
	},



	/**
	 * Remove a project from the subnavi of the projec tab
	 *
	 * @param	{Integer}		idProject
	 */
	removeProjectSubnaviItem: function(idProject) {
		var subnavi	= $('navi-main-list').down('li.itemProject').down('ul');

		if( ! Object.isUndefined(subnavi) ) {
			var item	= subnavi.down('li.itemProject' + idProject);

			if( ! Object.isUndefined(item) ) {
				item.remove();
			}
		}
	},



	/**
	 * Toggle display of project details
	 *
	 * @param	{Integer}		idProject
	 */
	toggleDetails: function(idProject) {
		var detailDiv	= $('project-' + idProject + '-details');

		if( ! detailDiv.visible() ) {
			if( detailDiv.empty() ) {
				var url		= Todoyu.getUrl('project', 'project');
				var options	= {
					'parameters': {
						'action':	'details',
						'project':	idProject
					},
					'onComplete': this.onDetailsToggled.bind(this, idProject)
				};
				Todoyu.Ui.update(detailDiv, url, options);
			}
			detailDiv.show();
		} else {
			detailDiv.hide();
		}

		this.saveDetailsExpanded(idProject, detailDiv.visible());
	},



	/**
	 * @todo	check: used? remove? comment
	 */
	onDetailsToggled: function(idProject, response) {
//		Todoyu.log('OnComplete erreicht');
	},



	/**
	 * Save state of project details being expanded
	 *
	 * @param	{Integer}		idProject
	 * @param	{Boolean}		expanded
	 */
	saveDetailsExpanded: function(idProject, expanded) {
		Todoyu.Pref.save('project', 'detailsexpanded', expanded ? 1 : 0, idProject, 0);
	},



	/**
	 * Hide details of given project
	 *
	 * @param	{Integer}		idProject
	 */
	hideDetails: function(idProject) {
		Todoyu.Ui.hide('project-' + idProject + '-details');
	},



	/**
	 * Show project details
	 *
	 * @param	{Integer}		idProject
	 */
	showDetails: function(idProject) {
		Todoyu.Ui.show('project-' + idProject + '-details');
	},



	/**
	 * Add task to given project
	 *
	 * @param	{Integer}		idProject
	 */
	addTask: function(idProject) {
		if(Todoyu.getArea() != 'project') {
			Todoyu.goTo('project', 'ext', {
				'action': 'addtask',
				'project': idProject
			});
		}

		this.ext.Task.addTaskToProject(idProject);
	},



	/**
	 * Add new container to given project
	 *
	 * @param	{Integer}		idProject
	 */
	addContainer: function(idProject) {
		if( Todoyu.getArea() === 'project' ) {
			this.ext.Task.addContainerToProject(idProject);
		} else {
			Todoyu.goTo('project', 'ext', {
				'action': 'addcontainer',
				'project': idProject
			});
		}
	},



	/**
	 * Refresh given project display
	 *
	 * @param	{Integer}		idProject
	 */
	refresh: function(idProject) {
		var url		= Todoyu.getUrl('project', 'project');
		var options	= {
			'parameters': {
				'action':	'details',
				'project':	idProject
			}
		};
		var target	= 'project-' + idProject + '-details';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Change status of given project to given status
	 *
	 * @param	{Integer}		idProject
	 * @param	{Integer}		status
	 */
	updateStatus: function(idProject, status) {
		var url	= Todoyu.getUrl('project', 'project');
		var options	= {
			'parameters': {
				'action':	'setstatus',
				'project':	idProject,
				'status':	status
			},
			'onComplete':	this.onStatusUpdated.bind(this, idProject, status)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler being called after project status update being done: refresh project display and set status
	 *
	 * @param	{Integer}				idProject
	 * @param	{Integer}				status
	 * @param	{Ajax.Response}		response
	 */
	onStatusUpdated: function(idProject, status, response) {
		this.refresh(idProject);
		this.setStatus(idProject, status);
	},



	/**
	 * Get current status of given project
	 *
	 * @param	{Integer}		idProject
	 * @param	{Integer}		status index
	 */
	getStatus: function(idProject) {
		var classNames 	= $('project-' + idProject).down('div.projectstatus').classNames();
		var statusClass	= classNames.grep(/bcStatus(\d)/).first();

		return statusClass.split('Status').last();
	},



	/**
	 * Set project status
	 *
	 * @param	{Integer}		idProject
	 * @param	{Integer}		status
	 */
	setStatus: function(idProject, status) {
		var statusBar	= $('project-' + idProject).down('div.projectstatus');
		var oldStatus	= this.getStatus(idProject);

		statusBar.replaceClassName('bcStatus' + oldStatus);
		statusBar.addClassName('bcStatus' + status);
	}

};
