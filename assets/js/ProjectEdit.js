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

Todoyu.Ext.project.Project.Edit = {
	
	ext: Todoyu.Ext.project,



	/**
	 *	Create form wrap DIVs
	 *
	 *	@param	Integer	idProject
	 */
	createFormWrapDivs: function(idProject) {
		var idDetails	= 'project-' + idProject + '-details';
		var idData		= 'project-' + idProject + '-data';

		if( ! Todoyu.exists(idData) ) {
				// Create data div
			var data = new Element('div', {
				'id': idData,
				'class': 'data edit'
			});

			$(idDetails).insert({
				'top': data
			});
		}

		$(idData).addClassName('edit');

		$(idDetails).show();
	},



	/**
	 *	Load project form
	 *
	 *	@param	Integer	idProject
	 */
	loadForm: function(idProject) {
		var url 	= Todoyu.getUrl('project', 'project');
		var options = {
			'parameters': {
				'action':	'edit',
				'project':	idProject
			}
		};
		var target	= 'project-' + idProject + '-data';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 *	Save project
	 *
	 *	@param	unknown	form
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
	 */
	onCreated: function(idProject) {
		idProject	= Todoyu.Helper.intval(idProject);

		Todoyu.Ext.project.ProjectTaskTree.openProject(idProject);
		window.scrollTo(0,0);
	},



	/**
	 *	onSaved project custom event handler
	 *
	 *	@param	Ajax.Response		response
	 */
	onSaved: function(response){
		var idProject	= response.getTodoyuHeader('idProject');
		var idProjectOld= response.getTodoyuHeader('idProjectOld');
		var error		= response.hasTodoyuError();

		if( error ) {
			this.updateFormDiv(idProjectOld, response.responseText);
			Todoyu.notifyError('[LLL:project.save.error]');
		} else {
			this.ext.ProjectTaskTree.removeProject(idProjectOld);
			this.ext.ProjectTaskTree.openProject(idProject);

			window.scrollTo(0,0);

			Todoyu.Hook.exec('onProjectSaved', idProject);
			Todoyu.notifySuccess('[LLL:project.save.success]');
		}
	},



	/**
	 *	Update form DIV
	 *
	 *	@param	Integer		idProject
	 *	@param	String		formHTML
	 */
	updateFormDiv: function(idProject, formHTML) {
		$('project-' + idProject + '-data').update(formHTML);
	},



	/**
	 * Cancel project editing / creation
	 *
	 *	@param	Integer	idProject
	 */
	cancel: function(idProject) {
		if( idProject == 0 ) {
				// If the form of a new project is canceled
			this.ext.ProjectTaskTree.removeProject(idProject);
			var idProject = this.ext.ProjectTaskTree.getActiveProjectID();

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
		}

	},



	/**
	 * Handler when customer/company field is autocompleted
	 *
	 * @param	Ajax.Response			response
	 * @param	Todoyu.Autocompleter	autocompleter
	 */
	onCompanyAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') == 0 ) {
			Todoyu.notifyInfo('[LLL:project.ac.company.notFoundInfo]');
		}
	},



	/**
	 * Handler when user field is autocompleted
	 *
	 * @param	Ajax.Response			response
	 * @param	Todoyu.Autocompleter	autocompleter
	 */
	onPersonAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') == 0 ) {
			Todoyu.notifyInfo('[LLL:project.ac.person.notFoundInfo]');
		}
	},



	/**
	 * Handler when projectleader (user) field is autocompleted
	 *
	 * @param	Ajax.Response			response
	 * @param	Todoyu.Autocompleter	autocompleter
	 */
	onProjectleaderAutocomplete: function(response, autocompleter) {
		this.onUserAutocomplete(response, autocompleter);
	},



	/**
	 * Handler when customer manager (user) field is autocompleted
	 *
	 * @param	Ajax.Response			response
	 * @param	Todoyu.Autocompleter	autocompleter
	 */
	onCustomerManagerAutocomplete: function(response, autocompleter) {
		this.onUserAutocomplete(response, autocompleter);
	}
		
};