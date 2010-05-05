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

Todoyu.Ext.project.Project.Edit = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext: Todoyu.Ext.project,



	/**
	 * Create form wrap DIVs
	 *
	 * @param	{Number}	idProject
	 */
	createFormWrapDivs: function(idProject) {
		var idDetails	= 'project-' + idProject + '-details';
		var idData		= 'project-' + idProject + '-data';

			// Create data DIV above project details DIV element
		if( ! Todoyu.exists(idData) ) {
			var data = new Element('div', {
				'id':		idData,
				'class':	'data edit'
			});

			$(idDetails).insert({
				'top': data
			});
		}

			// Set data DIV visually to 'editing'-style, display it
		$(idData).addClassName('edit');
		$(idDetails).show();
	},



	/**
	 * Load project form
	 *
	 * @param	{Number}	idProject
	 */
	loadForm: function(idProject) {
		var url 	= Todoyu.getUrl('project', 'project');
		var options = {
			'parameters': {
				'action':	'edit',
				'project':	idProject
			},
			onComplete: this.onFormLoaded.bind(this, idProject)
		};
		var target	= 'project-' + idProject + '-data';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Form has been loaded
	 *
	 * @param	{Number}	idProject
	 */
	onFormLoaded: function(idProject) {
		Todoyu.Hook.exec('onProjectFormLoaded', idProject);
	},



	/**
	 * Save project
	 *
	 * @param	{Element}	form
	 */
	save: function(form){
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action':	'save'
			},
			onComplete: this.onSaved.bind(this)
		});

		return false;
	},



	/**
	 * On created handler
	 *
	 * @param	{Number}	idProject
	 */
	onProjectCreated: function(idProject) {
		if( Todoyu.getArea() === 'project' ) {
			this.ext.ProjectTaskTree.openProject(idProject);
			
			Todoyu.Ui.scrollToTop();
		}		
	},



	/**
	 * onSaved project custom event handler
	 *
	 * @param	{Ajax.Response}		response
	 */
	onSaved: function(response){
		var idProject	= response.getTodoyuHeader('idProject');
		var idProjectOld= response.getTodoyuHeader('idProjectOld');

		if( response.hasTodoyuError() ) {
			this.updateFormDiv(idProjectOld, response.responseText);
			Todoyu.notifyError('[LLL:project.save.error]');
		} else {
			this.ext.ProjectTaskTree.removeProject(idProjectOld);
			this.ext.ProjectTaskTree.openProject(idProject);
			
			Todoyu.Ui.scrollToTop();

			Todoyu.Hook.exec('onProjectSaved', idProject);
			Todoyu.notifySuccess('[LLL:project.save.success]');
		}
	},



	/**
	 * Update form DIV
	 *
	 * @param	{Number}		idProject
	 * @param	{String}		formHTML
	 */
	updateFormDiv: function(idProject, formHTML) {
		$('project-' + idProject + '-data').update(formHTML);
	},



	/**
	 * Cancel project editing / creation
	 *
	 * @param	{Number}	idProject
	 */
	cancel: function(idProject) {
		if( idProject === 0 ) {
				// If the form of a new project is canceled
			this.ext.ProjectTaskTree.removeProject(idProject);
			idProject = this.ext.ProjectTaskTree.getActiveProjectID();

				// If there is a project
			if( idProject !== false )	{
				this.ext.ProjectTaskTree.openProject(idProject);
				this.ext.ProjectTaskTree.moveTabToFront(idProject);
			} else {
					// No project-tab found? reload to show startup-wizard
				Todoyu.goTo('project');
			}
		} else {
				// If the for of an existing project is canceled
			this.ext.Project.showDetails(idProject);
			this.ext.TaskTree.toggle(idProject);

			this.ext.Project.refresh(idProject);
			Todoyu.Ui.scrollToTop();
		}

	},



	/**
	 * Handler when customer/company field is autocompleted
	 *
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onCompanyAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') === '0' ) {
			Todoyu.notifyInfo('[LLL:project.ac.company.notFoundInfo]');
		}
	},



	/**
	 * Handler when person field is autocompleted
	 *
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onPersonAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') === '0' ) {
			Todoyu.notifyInfo('[LLL:project.ac.person.notFoundInfo]');
		}
	},



	/**
	 * Handler when projectleader (person) field is autocompleted
	 *
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onProjectleaderAutocomplete: function(response, autocompleter) {
		this.onPersonAutocomplete(response, autocompleter);
	}

};