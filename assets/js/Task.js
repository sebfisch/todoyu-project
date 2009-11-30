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

Todoyu.Ext.project.Task = {

	ext: Todoyu.Ext.project,



	/**
	 * Load and display editing form of given task
	 *
	 *	@param	Integer	idTask
	 */
	edit: function(idTask) {
		this.Edit.createFormWrapDivs(idTask);
		this.Edit.loadForm(idTask);
	},



	/**
	 * Scoll to given task
	 *
	 *	@param	Integer	idTask
	 */
	scrollTo: function(idTask) {
		$('task-' + idTask).scrollToElement();
	},



	/**
	 * Change status of given task to given status, in DB and visual 
	 *
	 *	@param	Integer	idTask
	 *	@param	unknown_type status
	 */
	updateStatus: function(idTask, status) {
		var url	= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'task':		idTask,
				'action':	'setstatus',
				'status':	status
			},
			'onComplete': this.onStatusUpdated.bind(this, idTask, status)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when status has been updated
	 * 
	 *	@param	Integer			idTask
	 *	@param	Integer			status
	 *	@param	Ajax.response	response
	 */
	onStatusUpdated: function(idTask, status, response) {
		Todoyu.Hook.exec('taskStatusUpdated', idTask, status);
		this.refresh(idTask);
	},



	/**
	 * Get status key of the task
	 * 
	 * @param	Integer		idTask
	 */
	getStatus: function(idTask) {
		var classNames 	= $('task-' + idTask + '-header').down('span.headLabel').classNames();
		var statusClass	= classNames.grep(/.*Status(\d)/).first();
		var statusKey	= statusClass.split('Status').last();

		return statusKey;
	},

	setStatus: function(idTask, status) {
		console.log('not implemented yet');
	},



	/**
	 * Refresh a task if loaded
	 *
	 *	@param	Integer		idTask
	 */
	refresh: function(idTask) {
		var target	= 'task-' + idTask;
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'get',
				'task':		idTask
			},
			'onComplete': this.onRefreshed.bind(this, idTask)
		};

		if( Todoyu.exists(target) ) {
				// Detach menu
			this.removeContextMenu(idTask);
				// Update task
			Todoyu.Ui.replace(target, url, options);
		}
	},



	/**
	 * Handler when task has been refreshed
	 * 
	 *	@param	Integer			idTask
	 *	@param	Ajax.Response	response
	 */
	onRefreshed: function(idTask, response) {
		this.addContextMenu(idTask);
	},



	/**
	 * Confirm whether user is sure, and evoke deletion of given task if
	 *
	 *	@param	Integer idTask
	 */
	remove: function(idTask) {
		if( ! confirm('[LLL:task.js.removetask.question]') ) {
			return;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'delete',
				'task':		idTask
			}
		};

		Todoyu.send(url, options);

		Effect.BlindUp('task-' + idTask, {
			'duration': 0.7
		});

		if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
			Effect.BlindUp('task-' + idTask + '-subtasks', {
				'duration': 0.3
			});
		}
	},



	/**
	 * Update (reload) given task
	 *
	 *	@param	Integer idTask
	 *	@param	String	taskHtml
	 */
	update: function(idTask, taskHtml) {
		this.removeContextMenu(idTask);

		$('task-' + idTask).replace(taskHtml);

		this.addContextMenu(idTask);
	},



	/**
	 * Clone given task (open new task creation form with attributes of task filled-in, title renamed to 'copy of..')
	 *
	 * @param	Integer	idTask
	 */
	clone: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'clone',
				'task':		idTask
			},
			'onComplete': this.onCloned.bind(this)
		};
		var target	= 'task-' + idTask;

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handle completion of task being cloned
	 *
	 * @param	Object	response
	 */
	onCloned: function(response) {
			// Get task id from header
		var idTask = response.getHeader('Todoyu-idTask');
			// Attach context menu
		this.addContextMenu(idTask);
			// Open edit mode
		this.edit(idTask);
	},



	/**
	 * Remove new task container element from DOM
	 */
	removeNewTaskContainer: function() {
		if( Todoyu.exists('task-0') ) {
			$('task-0').remove();
		}
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idProject
	 */
	addTaskToProject: function(idProject) {
		this.removeNewTaskContainer();

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'addprojecttask',
				'project':	idProject
			},
			'onComplete': this.onProjectTaskAdded.bind(this)
		};

		var target	= 'project-' + idProject + '-tasks';

		Todoyu.Ui.insert(target, url, options);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	unknown_type response
	 */
	onProjectTaskAdded: function(response) {
			// Get task id from header
		var idTask = response.getTodoyuHeader('idTask');
			// Add context menu to new task
		this.addContextMenu(idTask);
			// Scroll to new task
		this.scrollTo(idTask);

		Todoyu.Hook.exec('onTaskEdit', idTask);
	},



	/**
	 * Focus title field in task edit form
	 *
	 * @hooked	onTaskEdit
	 * @param	Integer		idTask
	 */
	focusTitleField: function(idTask) {
		$('task-' + idTask + '-field-title').focus();
	},



	/**
	 * Enter description here...
	 *
	 * @param	Integer idProject
	 */
	addContainerToProject: function(idProject) {
		this.removeNewTaskContainer();

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':		'addprojectcontainer',
				'project':		idProject
			},
			'onComplete': this.onProjectContainerAdded.bind(this)
		};
		var target	= 'project-' + idProject + '-tasks';

		Todoyu.Ui.insert(target, url, options);
	},


	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	unknown_type response
	 */
	onProjectContainerAdded: function(response) {
			// Get task id from header
		var idContainer = response.getHeader('Todoyu-idContainer');
			// Add context menu to new task
		this.addContextMenu(idContainer);
			// Scroll to new task
		this.scrollTo(idContainer);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	addSubTask: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'addsubtask',
				'task':		idTask
			},
			'onComplete': this.onSubTaskAdded.bind(this, idTask)
		};
		var target	= 'task-' + idTask + '-subtasks';

		if( ! Todoyu.exists(target) ) {
			this.createSubTaskContainer(idTask);
		}

		Todoyu.Ui.insert(target, url, options);
	},



	/**
	 * Enter description here...
	 *
	 * @param	Integer	idParentTask
	 * @param	Object	response
	 */
	onSubTaskAdded: function(idParentTask, response) {
		var idTask = response.getHeader('Todoyu-idTask');

		this.addContextMenu(idTask);
		this.showSubtasks(idParentTask);
		this.scrollTo(idTask);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	createSubTaskContainer: function(idTask) {
		var idSubtaskContainer	= 'task-' + idTask + '-subtasks';
		var idTaskContainer		= 'task-' + idTask;

		if( ! Todoyu.exists(idSubtaskContainer) ) {
			$(idTaskContainer).insert({
				'after': new Element('div', {
					'id':		idSubtaskContainer,
					'class':	'subtasks'
				})
			});
		}
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	addSubContainer: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'addsubcontainer',
				'task':		idTask
			},
			'onComplete': this.onSubContainerAdded.bind(this, idTask)
		};
		var target	= 'task-' + idTask + '-subtasks';

		if( ! Todoyu.exists(target) ) {
			this.createSubTaskContainer(idTask);
		}

		Todoyu.Ui.insert(target, url, options);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idParentTask
	 * @param	unknown_type response
	 */
	onSubContainerAdded: function(idParentTask, response) {
		var idContainer = response.getHeader('Todoyu-idContainer');

		this.addContextMenu(idContainer);
		this.showSubtasks(idParentTask);
		this.scrollTo(idContainer);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	addContextMenu: function(idTask) {
		this.ext.ContextMenuTask.attachToElement('task-' + idTask + '-header');
	},



	/**
	 * Enter description here...
	 * @todo	comment
	 * @param	Integer idTask
	 */
	removeContextMenu: function(idTask) {
		Todoyu.ContextMenu.detachMenuFromElement('task-' + idTask + '-header');
	},



	/**
	 * Enter description here...
	 *
	 * @param	Integer idTask
	 */
	setAcknowledged: function(idTask) {
		this.fadeAcknowledgeIcon(idTask);

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'acknowledge',
				'task':		idTask
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	fadeAcknowledgeIcon: function(idTask) {
		var idElement = 'task-' + idTask + '-notacknowledged';
		if( Todoyu.Ui.isVisible(idElement) ) {
			$(idElement).fade();
		}
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	toggleDetails: function(idTask) {
		var details;

			// If detail is loaded yet, send only a preference request
		if( this.isDetailsLoaded(idTask) ) {
			details = $('task-' + idTask + '-details');
				// Save preference
			this.saveTaskOpen(idTask, !details.visible());
		} else {
			this.loadDetails(idTask);
			details	= $('task-' + idTask + '-details');
			details.hide();
		}

			// Toggle visibility
		details.toggle();
		$('task-' + idTask).toggleClassName('expanded');
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	isDetailsLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-details');
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 * @param	unknown_type tab
	 */
	showDetails: function(idTask, tab) {
		if( this.isDetailsLoaded(idTask) ) {
			$('task-' + idTask + '-details').show();
		} else {
			this.loadDetails(idTask, tab);
		}

		this.Tab.show(idTask, tab);
	},


	
	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 */
	showSubtasks: function(idTask) {
		var idDiv	= 'task-' + idTask + '-subtasks';

		if( $(idDiv) ) {
			$(idDiv).show();
		}
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 * @param	unknown_type tab
	 */
	loadDetails: function(idTask, tab) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'detail',
				'task':		idTask
			},
			'asynchronous': false
		};
		var target	= 'task-' + idTask + '-header';

			// Fade out the "not acknowledged" icon if its there
		this.fadeAcknowledgeIcon.delay(1, idTask);

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 * @param	Integer idTask
	 * @param	unknown_type open
	 */
	saveTaskOpen: function(idTask, open) {
		var value = open ? 1 : 0;
		this.ext.savePref('taskopen', value, idTask);
	},



	isLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask);
	},



	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 */
	Edit: {
		ext: Todoyu.Ext.project,

		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 */
		createFormWrapDivs: function(idTask) {
			var idHeader	= 'task-' + idTask + '-header';
			var idDetails	= 'task-' + idTask + '-details';
			var idData		= 'task-' + idTask + '-data';

			if( ! Todoyu.exists(idDetails) ) {
					// Create details div
				var details = new Element('div', {
					'id': idDetails,
					'class': 'details edit'
				});

					// Create data div
				var data = new Element('div', {
					'id': idData,
					'class': 'data'
				});

				details.insert({
					'top': data
				});

				$(idHeader).insert({
					'after': details
				});
			}

			$(idDetails).show();
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 */
		loadForm: function(idTask) {
			var url 	= Todoyu.getUrl('project', 'task');
			var options	= {
				parameters: {
					'action': 'edit',
					'task':	idTask
				},
				'onComplete': this.onFormLoaded.bind(this, idTask)
			};
			var target	= 'task-' + idTask + '-data';

			Todoyu.Ui.update(target, url, options);
		},


		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type response
		 */
		onFormLoaded: function(idTask, response) {
			this.ext.Task.scrollTo(idTask);

			Todoyu.Hook.exec('onTaskEdit', idTask);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	unknown_type form
		 */
		save: function(form) {
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
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	unknown_type response
		 */
		onSaved: function(response) {
			var idTask		= response.getTodoyuHeader('idTask');
			var idTaskOld	= response.getTodoyuHeader('idTaskOld');

			if( response.hasTodoyuError() ) {
				this.updateFormDiv(idTask, response.responseText);
				Todoyu.notifyError('[LLL:task.save.error]');
			} else {
				this.ext.Task.update(idTaskOld, response.responseText);
				this.ext.Task.addContextMenu(idTask);

				Todoyu.Hook.exec('onTaskSaved', idTask);
				Todoyu.notifySuccess('[LLL:task.save.success]');
			}
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type formHTML
		 */
		updateFormDiv: function(idTask, formHTML) {
			$('task-' + idTask + '-formdiv').update(formHTML);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 */
		cancel: function(idTask) {
			this.ext.Task.refresh(idTask);
		}
	},


	/**
	 * Enter description here...
	 *
	 * @todo	comment
	 */
	Tab: {

		/**
		 * Enter description here...
		 *
		 * @param Integer idTask
		 * @param unknown_type tabKey
		 */
		show: function(idTask, tabKey) {
			var tabID = this.buildTabID(idTask, tabKey);

			if( ! Todoyu.exists(tabID) ) {
				this.createTabContainer(idTask, tabKey);
				this.load(idTask, tabKey);
			} else {
				this.saveSelection(idTask, tabKey);
			}

			this.activate(idTask, tabKey);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		load: function(idTask, tabKey) {
			var url 	= Todoyu.getUrl('project', 'task');
			var options	= {
				'parameters': {
					'action':	'tabload',
					'task':		idTask,
					'tab':		tabKey
				}
			};
			var tabDiv	= this.buildTabID(idTask, tabKey);

			Todoyu.Ui.update(tabDiv, url, options);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		createTabContainer: function(idTask, tabKey) {
			var tabContainer = 'task-' + idTask + '-tabcontent';
				// Create elements
			var loader	= new Element('img', {'src':'assets/img/ajax-loader.gif'});
			var spacer	= new Element('p', {'style':'padding:50px;text-align:center'}).update(loader);
			var tabDiv	= new Element('div', {
					'id':		this.buildTabID(idTask, tabKey),
					'class':	'tab'
				}).update(spacer);
			$(tabContainer).insert({'top': tabDiv});
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		buildTabID: function(idTask, tabKey) {
			return 'task-' + idTask + '-tabcontent-' + tabKey;
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		activate: function(idTask, tabKey) {
			this.hideAll(idTask);
			this.setActiveHead(idTask, tabKey);
			this.setVisible(idTask, tabKey);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		saveSelection: function(idTask, tabKey) {
			var url = Todoyu.getUrl('project', 'task');
			var options	= {
				'parameters': {
					'action':	'tabselected',
					'idTask':	idTask,
					'tab':		tabKey
				}
			};

			Todoyu.send(url, options);
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 */
		hideAll: function(idTask) {
			var tabDiv	= this.getContainer(idTask);
			var tabs	= tabDiv.select('.tab');

			tabs.invoke('hide');
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type activeTab
		 */
		setActiveHead: function(idTask, activeTab) {
			var tabHeadList	= $('task-' + idTask + '-tabheads');
			var tabHeads	= tabHeadList.select('li');

			tabHeads.each(function(tabHead) {
				tabHead.removeClassName('active');
			});

			$(this.getHeadID(idTask, activeTab)).addClassName('active');
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		setVisible: function(idTask, tabKey) {
			$(this.buildTabID(idTask, tabKey)).show();
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 */
		getContainer: function(idTask) {
			return $('task-' + idTask + '-tabs');
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer idTask
		 * @param	unknown_type tabKey
		 */
		getHeadID: function(idTask, tabKey) {
			return 'task-' + idTask + '-tabhead-' + tabKey;
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	Integer	idItem
		 */
		getKeyFromID: function(idItem) {
			return idItem.split('-').last();
		},



		/**
		 * Enter description here...
		 *
		 * @todo	comment
		 * @param	unknown_type event
		 * @param	unknown_type tabKey
		 */
		onSelect: function(event, tabKey) {
			var info = tabKey.split('-');

			this.show(info[1], info[0]);
		}
	}

};