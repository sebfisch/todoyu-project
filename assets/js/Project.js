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
 * Extension: Project
 * Functions to handle projects
 */
Todoyu.Ext.project.Project = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.project,



	/**
	 *
	 *	@param {Object} idProject
	 */
	getHeader: function(idProject) {
		return $('project-' + idProject + '-header');
	},



	/**
	 *	Edit given project
	 *
	 *	@param Integer	idProject
	 */
	edit: function(idProject){
		this.hideDetails(idProject);
		this.ext.TaskTree.hide(idProject);

		this.Edit.createFormWrapDivs(idProject);
		this.Edit.loadForm(idProject);
	},



	/**
	 *	Delete given project
	 *
	 *	@param	Integer	idProject
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
	 *	@param	Integer	idProjectRemoved
	 */
	onRemoved: function(idProject) {
		this.ext.ProjectTaskTree.removeProject(idProject);
		this.ext.ProjectTaskTree.openFirstTab();
		this.removeProjectSubnaviItem(idProject);
	},
	
	
	
	/**
	 * Remove a project from the subnavi of the projec tab
	 * 
	 * @param	Integer		idProject
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
	 *	Toggle display of project details
	 *
	 *	@param	Integer	idProject
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


	onDetailsToggled: function(idProject, response) {
//		Todoyu.log('OnComplete erreicht');
	},



	/**
	 *	Save state of project details being expanded
	 *
	 *	@param	Integer	idProject
	 *	@param	Boolean	expanded
	 */
	saveDetailsExpanded: function(idProject, expanded) {
		Todoyu.Pref.save('project', 'detailsexpanded', expanded ? 1 : 0, idProject, 0);
	},



	/**
	 *	Hide project details
	 *
	 *	@param	Integer	idProject
	 */
	hideDetails: function(idProject) {
		Todoyu.Ui.hide('project-' + idProject + '-details');
	},



	/**
	 *	Show project details
	 *
	 *	@param	Integer	idProject
	 */
	showDetails: function(idProject) {
		Todoyu.Ui.show('project-' + idProject + '-details');
	},



	/**
	 *	Add project
	 */
	add: function() {
		var url		= Todoyu.getUrl('project', 'project');
		var options	= {
			'parameters': {
				'action':	'add'
			},
			'onComplete': this.onAdded.bind(this)
		};
		var target	= 'projects';

			// Remove project form or dummy start screen
		this.ext.ProjectTaskTree.removeProject(0);
		this.ext.ProjectTaskTree.removeProject('noselection');

		Todoyu.Ui.insert(target, url, options);
	},



	/**
	 *	Custom event handler, being evoked after adding a new project
	 *
	 *	@param	unknown	response
	 */
	onAdded: function(response) {
		var idProject	= response.getHeader('Todoyu-idProject');
		var projectLabel= response.getHeader('Todoyu-projectLabel');

		this.ext.ProjectTaskTree.addNewTabhead(idProject, projectLabel);
		this.ext.ProjectTaskTree.displayActiveProject(idProject);
	},



	/**
	 *	Add task to given project
	 *
	 *	@param	Integer	idProject
	 */
	addTask: function(idProject) {
		this.ext.Task.addTaskToProject(idProject);
	},



	/**
	 *	Add container to given project
	 *
	 *	@param	Integer	idProject
	 */
	addContainer: function(idProject) {
		this.ext.Task.addContainerToProject(idProject);
	},



	/**
	 *	Refresh given project
	 *
	 *	@param	Integer	idProject
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
	 *	Change status of given project to given status
	 *
	 *	@param	Integer	idProject
	 *	@param	Integer	status
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

	onStatusUpdated: function(idProject, status, response) {
		this.refresh(idProject);
		this.setStatus(idProject, status);
	},


	getStatus: function(idProject) {
		var classNames 	= $('project-' + idProject).down('div.projectstatus').classNames();
		var statusClass	= classNames.grep(/bcStatus(\d)/).first();
		var statusIndex	= statusClass.split('Status').last();

		return statusIndex;
	},

	setStatus: function(idProject, status) {
		var statusBar	= $('project-' + idProject).down('div.projectstatus');
		var oldStatus	= this.getStatus(idProject);

		statusBar.replaceClassName('bcStatus' + oldStatus);
		statusBar.addClassName('bcStatus' + status);
	},



/* -----------------------------------------------
	Todoyu.Ext.project.Project.Edit
-------------------------------------------------- */

	/**
	 *	Edit project (class) methods
	 */
	Edit: {
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
		 *	onSaved project custom event handler
		 *
		 *	@param	unknown	response
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
		 *	@param	Integer	idProject
		 *	@param	String	formHTML
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
		}
	}
};
