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
 * Handle project task tree in project module
 * Manage tabs
 */
Todoyu.Ext.project.ProjectTaskTree = {

	/**
	 * Backlink to project extension
	 */
	ext:			Todoyu.Ext.project,

	/**
	 * Maximum allowed amount of simultaneous open tabs
	 */
	maxOpenTabs:	3,



	/**
	 *	OnTabSelect custom event handler
	 *
	 *	@param	Event		event		Click event
	 *	@param	String		tabKey		Key of clicked tab
	 */
	onTabSelect: function(event, tabKey) {
		this.openProject(tabKey, 0);
		
		this.moveTabToFront(tabKey);
	},



	/**
	 * Open a project
	 *  - Load project tree if not loaded
	 *  - Add or activate tab
	 *  - Hide other project
	 *
	 *	@param	Integer		idProject
	 *	@param	Integer		idTask
	 */
	openProject: function(idProject, idTask) {
		idTask = Todoyu.Helper.intval(idTask);
		
		if( this.isProjectLoaded(idProject) && (idTask === 0 || (idTask !== 0 && this.isTaskLoaded(idTask) ) ) ) {
			this.displayActiveProject(idProject);
			if( idTask !== 0 ) {
				this.ext.Task.scrollTo(idTask);
			}
		} else {
			this.addNewProject(idProject, idTask);
		}
	},



	/**
	 * Remove a project (tab and tree)
	 *
	 *	@param	Integer		idProject
	 */
	removeProject: function(idProject) {
		if ( $('project-' + idProject) ) {
			$('project-' + idProject).remove();
			if ( this.hasProjectTab(idProject) ) {
				$('projecttab-' + idProject).remove();	
			}
		}
	},



	/**
	 *
	 *	@param	Integer		idProject
	 *	@param	Integer		idTask
	 */
	addNewProject: function(idProject, idTask) {
		var url		= Todoyu.getUrl('project', 'projecttasktree');
		var options = {
			'parameters': {
				'action': 	'project',
				'project': 	idProject,
				'task': 	idTask
			},
			'onComplete': 	this.onProjectLoaded.bind(this)
		};

		Todoyu.send(url, options);
	},



	/**
	 *	OnProjectLoaded custom event handler
	 *
	 *	@param	unknown	response
	 */
	onProjectLoaded: function(response) {
		var idProject 	= response.getHeader('Todoyu-project');
		var label		= response.getHeader('Todoyu-tablabel');

		this.insertTabContent(idProject, response.responseText);
		
		if( ! this.hasProjectTab(idProject) ) {
			this.addNewTabhead(idProject, label);
		}

		this.removeSurplusProject();
		this.displayActiveProject(idProject);

		this.ext.ContextMenuTask.reattach();
		this.ext.ContextMenuProject.reattach();
	},



	/**
	 * Insert project tab content (task of project)
	 *
	 *	@param	Integer	idProject
	 *	@param 	String	tabContent
	 */
	insertTabContent: function(idProject, tabContent) {
		$('projects').insert(tabContent);
		$('project-' + idProject).hide();
	},



	/**
	 * Add new tab head with given label to project tabs
	 *
	 *	@param	Integer	idProject
	 *	@param	String	label
	 */
	addNewTabhead: function(idProject, label) {
		if(typeof($('projecttab-'+idProject)) !== null)	{
			var idTab	= 'projecttab-' + idProject;
			var tabClass= 'tabkey-' + idProject + ' project' + idProject + ' projecttab item bcg05';

			var tab		= Todoyu.Tabs.build(idTab, tabClass, label, true);

			$('project-tabs').insert({'top':tab});
		}
	},



	/**
	 * Move tab to front (place if leftmost)
	 *
	 *	@param	Integer	idTab
	 */
	moveTabToFront: function(idTab) {
		var tab = $('projecttab-' + idTab);

		tab.remove();

		$('project-tabs').insert({
			'top':	tab
		});
		
		this.highlightTab(idTab);		
	},



	/**
	 * Open (activate) first project tab
	 */
	openFirstTab: function() {
		var allTabs = 	$$('li.projecttab');
		if ( allTabs.length > 0 ) {
			var idFirstTab = this.getFirstTab();
			this.moveTabToFront(idFirstTab);
			
			var activeProjectID	= this.getActiveProjectID();
			this.openProject(activeProjectID);
		}
	},



	/**
	 * Highlight the front tab
	 * 
	 *	@param	String		idTab
	 */
	highlightTab: function(idTab) {
		/*
		var label = $('projecttab-' + idTab).select('.labeltext').first();
		
		Effect.Shake(label, {
			'distance': 5,
			'duration': 0.3
		});
		*/
	},



	/**
	 * Remove surplus (spare) project
	 */
	removeSurplusProject: function() {
		var surplusTab = $('project-tabs').down('li').next('li', this.maxOpenTabs-1);

		if( surplusTab !== undefined ) {
				// Get project ID from tab ID
			var idProject = (surplusTab.readAttribute('id').split('-'))[1];

				// Remove tab
			surplusTab.remove();

				// Remove project tree if loaded
			if( Todoyu.exists('project-' + idProject) ) {
				$('project-' + idProject).remove();
			}
		}
	},



	/**
	 * Display (given) active project
	 *
	 *	@param	Integer		idProject
	 */
	displayActiveProject: function(idProject) {
		$('projects').childElements().invoke('hide');
		$('project-' + idProject).show();

		$('project-tabs').childElements().invoke('removeClassName', 'active');
		$('projecttab-' + idProject).addClassName('active');

		this.moveTabToFront(idProject);

		this.saveOpenProjects();
	},



	/**
	 * Check whether given project is loaded (is displayed as content in one of the projects tabs)
	 *
	 *	@param	Integer	idProject
	 *	@return	Boolean
	 */
	isProjectLoaded: function(idProject) {
		return Todoyu.exists('project-' + idProject);
	},



	/**
	 * Check whether given task is loaded
	 * 
	 *	@param	Integer	idTask
	 * 	@return	Boolean
	 */
	isTaskLoaded: function(idTask) {
		return this.ext.Task.isLoaded(idTask);
	},



	/**
	 * Check whether given project is currently active (tab is activated)
	 * 
	 *	@param	Integer	idProject
	 *	@return	Boolean
	 */
	isProjectActive: function(idProject) {
		return $('project-tabs').select('li.active').first().readAttribute('id').split('-')[1] == idProject;
	},



	/**
	 * Check whether given project is loaded (the resp. project tab exists)
	 *
	 *	@param	Integer	idProject
	 *	@return	Boolean
	 */
	hasProjectTab: function(idProject) {
		return Todoyu.exists('projecttab-' + idProject);
	},



	/**
	 * Save currently open projects in user prefs
	 */
	saveOpenProjects: function() {
		this.openProjects = [];

		$('project-tabs').childElements().each(function(tab) {
			this.openProjects.push(tab.id.split('-')[1])
		}.bind(this));

		var url		= Todoyu.getUrl('project', 'projecttasktree');
		var options	= {
			'parameters': {
				'action':	'openprojects',
				'projects':	this.openProjects.join(',')
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Get ID of active project (project shown in currently activated project tab)
	 *
	 *	@return	String
	 */
	getActiveProjectID: function() {
		return $('project-tabs').select('li.active')[0].id.substr(11);
	},



	/**
	 * Get first (leftmost) of the project tabs
	 *
	 *	@return	Mixed
	 */
	getFirstTab: function()	{
		if ( $('project-tabs') && $('project-tabs').select('li').length > 0 ) {
			return $('project-tabs').select('li')[0].id.substr(11);
		} else {
			return false;
		}

	}

};