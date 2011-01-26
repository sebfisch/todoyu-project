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
 * Handle project task tree in project module
 * Manage tabs
 */
Todoyu.Ext.project.ProjectTaskTree = {

	/**
	 * Backlink to project extension
	 *
	 * @var	{Object}	ext
	 */
	ext:			Todoyu.Ext.project,

	/**
	 * Maximum allowed amount of simultaneous open tabs (projects)
	 */
	maxOpenTabs:	3,



	/**
	 * OnTabSelect custom event handler
	 *
	 * @method	onTabSelect
	 * @param	{Event}		event			click event
	 * @param	{Number}	idProject		key of clicked tab
	 */
	onTabSelect: function(event, idProject) {
		this.openProject(idProject, 0);
		this.moveTabToFront(idProject);
	},



	/**
	 * Hooked into projectlist panelwidget: handle project click event
	 *
	 * @method	onPanelwidgetProjectlistProjectClick
	 * @param	{Event}		event
	 */
	onPanelwidgetProjectlistProjectClick: function(event) {
		var listElement = event.findElement('li');

		if( Object.isElement(listElement) ) {
			var idProject = listElement.id.split('-').last();
			this.openProject(idProject);
		}
	},



	/**
	 * Open a project
	 *  - Load project tree if not loaded
	 *  - Add or activate tab
	 *  - Hide other project
	 *
	 * @method	openProject
	 * @param	{Number}		idProject
	 * @param	{Number}		idTask
	 */
	openProject: function(idProject, idTask) {
		if( this.isProjectLoaded(idProject) && (idTask === 0 || idTask === undefined || (idTask !== 0 && this.isTaskLoaded(idTask) ) ) ) {
			this.displayActiveProject(idProject);
			if( idTask !== 0 ) {
				this.ext.Task.scrollTo(idTask);
			}
		} else {
			this.addNewProject(idProject, idTask);
		}

		Todoyu.Hook.exec('project.tasktree.openproject', idProject);
	},



	/**
	 * Remove a project (tab and tree)
	 *
	 * @method	removeProject
	 * @param	{Number}		idProject
	 */
	removeProject: function(idProject) {
		var id	= 'project-' + idProject;

		if( Todoyu.exists(id) ) {
			$(id).remove();
			this.removeProjectTab(idProject);
		}
	},



	/**
	 * Remove sub tab of given project
	 *
	 * @method	removeProjectTab
	 * @param	{Number}		idProject
	 */
	removeProjectTab: function(idProject) {
		var id	= 'project-tab-' + idProject;

		if( Todoyu.exists(id) ) {
			$(id).remove();
		}
	},



	/**
	 * Remove dummy tab which appears when no project is selected
	 *
	 * @method	removeNoSelectionTab
	 */
	removeNoSelectionTab: function() {
		if( Todoyu.exists('project-tab-noselection') ) {
			$('project-tab-noselection').remove();
		}
	},



	/**
	 * Show a project as list tree
	 *
	 * @method	addNewProject
	 * @param	{Number}		idProject
	 * @param	{Number}		idTask
	 */
	addNewProject: function(idProject, idTask) {
		var url		= Todoyu.getUrl('project', 'projecttasktree');
		var options = {
			'parameters': {
				'action': 	'addproject',
				'project': 	idProject,
				'task': 	idTask
			},
			'onComplete': 	this.onProjectLoaded.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 * On project loaded event: insert tab content in project area
	 *
	 * @method	onProjectLoaded
	 * @param	{Ajax.Response}		response
	 */
	onProjectLoaded: function(response) {
		var idProject 	= response.getTodoyuHeader('project');
			// Label is JSON encoded to use its character encoding
		var label		= response.getTodoyuHeader('tablabel');

		this.insertTabContent(idProject, response.responseText);

		if( ! Todoyu.Tabs.hasTab('project', idProject) ) {
			this.addNewTabhead(idProject, label);
		}

		this.removeSurplusProject();
		this.displayActiveProject(idProject);

		this.ext.ContextMenuTask.attach();
		this.ext.ContextMenuProject.attach();

		Todoyu.Hook.exec('project.project.loaded', idProject);
	},



	/**
	 * Insert project tab content (task of project)
	 *
	 * @method	insertTabContent
	 * @param	{Number}	idProject
	 * @param 	{String}	tabContent
	 */
	insertTabContent: function(idProject, tabContent) {
		$('projects').insert(tabContent);
		$('project-' + idProject).hide();
	},



	/**
	 * Add new tab head with given label to project tabs
	 *
	 * @method	addNewTabhead
	 * @param	{Number}	idProject
	 * @param	{String}	label
	 */
	addNewTabhead: function(idProject, label) {
		var tabClass= 'project' + idProject + ' projecttab';

		Todoyu.Tabs.addTab('project', idProject, tabClass, label, true, true);
	},



	/**
	 * Move tab to front (place if leftmost)
	 *
	 * @method	moveTabToFront
	 * @param	{Number}	idTab
	 */
	moveTabToFront: function(idTab) {
			// Remove no selection tab first
		this.removeNoSelectionTab();

		Todoyu.Tabs.moveAsFirst('project', idTab);
	},



	/**
	 * Open (activate) first project tab
	 *
	 * @method	openFirstTab
	 */
	openFirstTab: function() {
		if( this.getNumTabs() > 0 ) {
			var idProject = this.getActiveProjectID();
			this.moveTabToFront(idProject);
			this.openProject(idProject);
		} else {
			this.loadNoProjectSelectedView();
		}
	},



	/**
	 * Get amount of sub tabs shown
	 *
	 * @method	getNumTabs
	 * @return	{Number}
	 */
	getNumTabs: function() {
		return Todoyu.Tabs.getNumTabs('project');
	},



	/**
	 * Load initial view (first load, so there's no project selected yet)
	 *
	 * @method	loadNoProjectSelectedView
	 */
	loadNoProjectSelectedView: function() {
		var url		= Todoyu.getUrl('project', 'project');
		var options	= {
			'parameters': {
				'action':	'noProjectView'
			},
			'onComplete': this.onNoProjectSelectedViewLoaded.bind(this)
		};

		Todoyu.Ui.updateContent(url, options);
	},



	/**
	 * Handler after "no project selected" view has been loaded
	 *
	 * @method	onNoProjectSelectedViewLoaded
	 * @param	{Ajax.Response}		response
	 */
	onNoProjectSelectedViewLoaded: function(response) {

	},



	/**
	 * Remove surplus (spare) project
	 *
	 * @method	removeSurplusProject
	 */
	removeSurplusProject: function() {
		var removedProjects = Todoyu.Tabs.removeSurplus('project', this.maxOpenTabs);

		removedProjects.each(function(idProject){
			if( Todoyu.exists('project-' + idProject) ) {
				$('project-' + idProject).remove();
			}
		});
	},



	/**
	 * Display (given) active project
	 *
	 * @method	displayActiveProject
	 * @param	{Number}		idProject
	 */
	displayActiveProject: function(idProject) {
		$('projects').childElements().invoke('hide');
		$('project-' + idProject).show();

		Todoyu.Tabs.setActive('project', idProject);

		var title	= this.getActiveProjectTitle().replace(':', ' - ');
		Todoyu.Ui.setTitle('[LLL:project.page.title] - ' + title );

		this.moveTabToFront(idProject);

		this.saveOpenProjects();

		Todoyu.Hook.exec('project.taskTree.activeProjectDisplayed', idProject);
	},



	/**
	 * Check whether given project is loaded (is displayed as content in one of the projects tabs)
	 *
	 * @method	isProjectLoaded
	 * @param	{Number}		idProject
	 * @return	{Boolean}
	 */
	isProjectLoaded: function(idProject) {
		return Todoyu.exists('project-' + idProject);
	},



	/**
	 * Check whether given task is loaded
	 *
	 * @method	isTaskLoaded
	 * @param	{Number}		idTask
	 * @return	{Boolean}
	 */
	isTaskLoaded: function(idTask) {
		return this.ext.Task.isLoaded(idTask);
	},



	/**
	 * Check whether given project is currently active (tab is activated)
	 *
	 * @method	isProjectActive
	 * @param	{Number}		idProject
	 * @return	{Boolean}
	 */
	isProjectActive: function(idProject) {
		return Todoyu.Tabs.getActiveKey('project') == idProject;
	},



	/**
	 * Save currently open projects in prefs
	 *
	 * @method	saveOpenProjects
	 */
	saveOpenProjects: function() {
		var openProjects	= Todoyu.Tabs.getTabNames('project');

		var url		= Todoyu.getUrl('project', 'projecttasktree');
		var options	= {
			'parameters': {
				action:	'openprojects',
				'projects':	openProjects.join(',')
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Get ID of active project (project shown in currently activated project tab)
	 *
	 * @method	getActiveProjectID
	 * @return	{String}
	 */
	getActiveProjectID: function() {
		return Todoyu.Tabs.getActiveKey('project');
//		var activeTabs	= $('project-tabs').select('li.active');
//
//		if( activeTabs.size() === 1 ) {
//			return activeTabs.first().id.split('-').last();
//		} else {
//			if( this.getNumTabs() > 0 ) {
//				return this.getTabs().first().id.split('-').last();
//			}
//		}
//
//		return false;
	},



	/**
	 * Get title of project shown in currently activated project tab
	 *
	 * @method	getActiveProjectTitle
	 * @return  {String}
	 */
	getActiveProjectTitle: function() {
		return Todoyu.Tabs.getActive('project').down('.labeltext').innerHTML;

//		var activeTab	= $('project-tabs').select('li.active span.labeltext');
//		var title		= '';
//
//		if( activeTab.size() === 1 ) {
//			title = activeTab.first().innerHTML;
//		} else {
//			if( this.getNumTabs() > 0 ) {
//				title = this.getTabs().first().innerHTML;
//			}
//		}
//
//		return title;
	},



	/**
	 * Get current tab items (DOM elements)
	 *
	 * @method	getTabs
	 * @return	{Array}
	 */
	getTabs: function() {
		return Todoyu.Tabs.getAllTabs('project');
	},



	/**
	 * Get first (leftmost) of the project tabs
	 *
	 * @method	getFirstTab
	 * @return	{Mixed}
	 */
	getFirstTab: function() {
		return Todoyu.Tabs.getFirstTab('project');

//		if( $('project-tabs') && $('project-tabs').select('li').length > 0 ) {
//			return $('project-tabs').select('li')[0].id.substr(11);
//		} else {
//			return false;
//		}
	}

};