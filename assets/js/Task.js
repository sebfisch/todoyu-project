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

Todoyu.Ext.project.Task = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Load and display editing form of given task
	 *
	 * @param	Integer		idTask
	 */
	edit: function(idTask) {
		this.Edit.createFormWrapDivs(idTask);
		this.Edit.loadForm(idTask);
	},




	/**
	 * Copy a task (and sub tasks) to clipboard
	 *
	 * @param	Integer		idTask
	 */
	copy: function(idTask) {
		var withSubtasks = false;

			// Ask to copy sub tasks
		if ( this.hasSubtasks(idTask) ) {
			withSubtasks = confirm('[LLL:task.copySubtasks]') ? 1 : 0;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'copy',
				'task':		idTask,
				'subtasks':	withSubtasks
			},
			'onComplete': this.onCopied.bind(this, idTask)
		};

		Todoyu.send(url, options);

			// Highlight copied task
		this.highlight(idTask);

			// Highlight sub tasks if selected to copy
		if( withSubtasks ) {
			this.highlightSubtasks(idTask);
		}
	},



	/**
	 * Handler when copied to clipboard
	 *
	 * @param	Integer				idTask
	 * @param	Ajax.Response		response
	 */
	onCopied: function(idTask, response) {

	},



	/**
	 * Cut task (to clipboard)
	 *
	 * @param	Integer			idTask
	 */
	cut: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'cut',
				'task':		idTask
			},
			'onComplete': this.onCut.bind(this, idTask)
		};

		Todoyu.send(url, options);

		this.highlight(idTask);
		this.highlightSubtasks(idTask);
	},



	/**
	 * Handler when task is cut
	 *
	 * @param	Integer				idTask
	 * @param	Ajax.Response		response
	 */
	onCut: function(idTask, response) {

	},



	/**
	 * Paste task
	 *
	 * @param	Integer		idTask		Task where to paste
	 * @param	String		mode		Insert mode (in,after,before)
	 */
	paste: function(idTask, mode) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'paste',
				'task':		idTask,
				'mode':		mode
			},
			'onComplete': this.onPasted.bind(this, idTask, mode)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when task is pasted
	 *
	 * @param	Integer			idTask
	 * @param	String			insertMode
	 * @param	Ajax.Response	response
	 */
	onPasted: function(idTask, insertMode, response) {
		var idTaskNew		= response.getTodoyuHeader('idTask');
		var clipboardMode	= response.getTodoyuHeader('clipboardMode');

			// If task was cut, remove old element
		if( clipboardMode === 'cut' ) {
			if( Todoyu.exists('task-' + idTaskNew + '-subtasks') ) {
				$('task-' + idTaskNew + '-subtasks').remove();
			}
			if( Todoyu.exists('task-' + idTaskNew) ) {
				if( this.isSubtask(idTaskNew) ) {
					var idParent	= this.getParentTaskID(idTaskNew);

					$('task-' + idTaskNew).remove();

					this.checkAndRemoveTriggerFromTask(idParent);
				} else {
					$('task-' + idTaskNew).remove();
				}
			}
		}

			// Insert as sub task of the current task
		if( insertMode === 'in' ) {
				// If sub task container already exists, add it
			if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
				$('task-' + idTask + '-subtasks').insert({
					'bottom': response.responseText
				});
				console.log("in if");
				this.ext.TaskTree.expandSubtasks(idTask);
			} else {
					// If no sub task container available, refresh task and load its sub task
				this.refresh(idTask);
					// Append sub tasks
				this.ext.TaskTree.loadSubtasks(idTask, this.ext.TaskTree.toggleSubtaskTriggerIcon.bind(this, idTask));
				
			}
		} else if( insertMode === 'before' ) {
				// Insert task before current
			$('task-' + idTask).insert({
				'before': response.responseText
			});
		} else if( insertMode === 'after' ) {
				// Insert task after current
			var target = Todoyu.exists('task-' + idTask + '-subtasks') ? 'task-' + idTask + '-subtasks' : 'task-' + idTask;
			$(target).insert({
				'after': response.responseText
			});
		}

			// Attach context menu to all tasks (so the pasted ones get one too)
		this.ext.ContextMenuTask.attach();
			// Highlight the new pasted task
		this.highlight(idTaskNew);
		this.highlightSubtasks(idTaskNew);
	},



	/**
	 * Handler if person tries to paste on a position which is not allowed
	 */
	pasteNotAllowed: function() {
		alert("[LLL:task.pasteNotAllowed]");
	},



	/**
	 * Clone given task (open new task creation form with attributes of task filled-in, title renamed to 'copy of..')
	 *
	 * @param	Integer		idTask
	 */
	clone: function(idTask) {
		var withSubtasks = false;

			// Ask to copy sub tasks
		if ( this.hasSubtasks(idTask) ) {
			withSubtasks = confirm('Auch Unteraufgaben klonen?') ? 1 : 0;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'clone',
				'task':		idTask,
				'subtasks': withSubtasks
			},
			'onComplete': this.onCloned.bind(this, idTask)
		};

		if( this.hasSubtasks(idTask) && this.hasSubtaskContainer(idTask) ) {
			var target	= 'task-' + idTask + '-subtasks';
		} else {
			var target	= 'task-' + idTask;
		}

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handler when task was cloned
	 *
	 * @param	Integer				idSourceTask
	 * @param	Ajax.Response		response
	 */
	onCloned: function(idSourceTask, response) {
			// Get task id from header
		var idTask = response.getTodoyuHeader('idTask');
			// Attach context menu
		this.addContextMenu(idTask);
			// Highlight cloned element
		this.highlight(idTask);
		this.highlightSubtasks(idTask);

		Todoyu.Hook.exec('taskcloned', idSourceTask, idTask);
	},



	/**
	 * Delete a task
	 *
	 * @param	Integer		idTask
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
			},
			'onComplete': this.onRemoved.bind(this, idTask)
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
	 * Handler when task removed
	 *
	 * @param	Integer			idTask
	 * @param	Ajax.Response	response
	 */
	onRemoved: function(idTask, response) {
		Todoyu.Hook.exec('taskremoved', idTask);
	},



	/**
	 * Scoll to given task
	 *
	 * @param	Integer		idTask
	 */
	scrollTo: function(idTask) {
		$('task-' + idTask).scrollToElement();
	},



	/**
	 * Highlight a task
	 *
	 * @param	Integer		idTask
	 */
	highlight: function(idTask) {
		if( Todoyu.exists('task-' + idTask) ) {
			new Effect.Highlight('task-' + idTask);
		}
	},



	/**
	 * Highlight sub task container of a task
	 *
	 * @param	Integer		idTask
	 */
	highlightSubtasks: function(idTask) {
		if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
			new Effect.Highlight('task-' + idTask + '-subtasks');
		}
	},



	/**
	 * Check if a task has sub tasks
	 * @param	Integer		idTask
	 * @return	Boolean
	 */
	hasSubtasks: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks') || ($('task-' + idTask + '-subtasks-trigger') && $('task-' + idTask + '-subtasks-trigger').hasClassName('expandable'));
	},



	/**
	 * Check if sub task container is in DOM
	 *
	 * @param	Integer		idTask
	 */
	hasSubtaskContainer: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks');
	},



	/**
	 * check if task has a parent task
	 *
	 * @param	Integer		idTask
	 */
	hasParentTask: function(idTask){
		return $('task-'+idTask).up().hasClassName('subtasks');
	},



	/**
	 * Get ID of parent task of given task
	 *
	 * @return	Integer
	 */
	getParentTaskID: function(idTask) {
		var idParent	= false;

		if( Todoyu.exists('task-' + idTask) ) {
			var subTaskContainer	= $('task-'+idTask).up('.subtasks');

			if( subTaskContainer !== undefined ) {
				idParent	= subTaskContainer.id.split('-')[1];
			}
		}

		return idParent;
	},



	/**
	 * Check whether given task is a sub task
	 *
	 * @return	Boolean
	 */
	isSubtask: function(idTask) {
		if( Todoyu.exists('task-' + idTask) ) {
			return $('task-' + idTask).up('.subtasks') !== undefined;
		}

		return false;
	},


	/**
	 * Remove expand-trigger from parent of give task if its the only sub task
	 *
	 * @param	Integer		idTask
	 */
	checkAndRemoveTriggerFromParent: function(idTask)	{
		var idArray = $('task-'+idTask).up().id.split('-');
		var idParentTask = idArray[1];

		this.checkAndRemoveTriggerFromTask(idParentTask);
	},



	/**
	 * Is this the only sub task? remove expandability
	 *
	 * @param	Integer
	 */
	checkAndRemoveTriggerFromTask: function(idTask) {
		if( $('task-' + idTask + '-subtasks').select('div.task').size() < 1 ) {
			$('task-' + idTask + '-subtasks-trigger').removeClassName('expandable');
		}
	},



	/**
	 * Change status of given task to given status, in DB and visual
	 *
	 * @param	Integer		idTask
	 * @param	Integer	 	status
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
	 * @param	Integer				idTask
	 * @param	Integer				status
	 * @param	Ajax.response		response
	 */
	onStatusUpdated: function(idTask, status, response) {
		Todoyu.Hook.exec('taskStatusUpdated', idTask, status);

		if( this.isDetailsLoaded(idTask) ) {
			this.refresh(idTask);
		} else {
			this.setStatus(idTask, status);
		}
	},



	/**
	 * Get status key of the task
	 *
	 * @param	Integer		idTask
	 */
	getStatus: function(idTask) {
		var htmlID		= 'task-' + idTask + '-header';
		var statusIndex	= 0;

		if (Todoyu.exists(htmlID)) {
			var classNames 	= $(htmlID).down('.headLabel').classNames();
			var statusClass	= classNames.grep(/.*Status(\d)/).first();
			statusIndex		= statusClass.split('Status').last();
		}

		return statusIndex;
	},


	/**
	 * Set status of given task
	 *
	 * 	@param	Integer		idTask
	 * 	@param	status
	 */
	setStatus: function(idTask, status) {
		var htmlID		= 'task-' + idTask + '-header';

		if( Todoyu.exists(htmlID) ) {
			var headLabel	= $(htmlID).down('.headLabel');
			var oldStatus	= this.getStatus(idTask);

			headLabel.replaceClassName('bcStatus' + oldStatus);
			headLabel.addClassName('bcStatus' + status);
		}
	},



	/**
	 * Refresh a task if loaded
	 *
	 * @param	Integer			idTask
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
				// Update task
			Todoyu.Ui.replace(target, url, options);
		}
	},



	/**
	 * Handler when task has been refreshed
	 *
	 * @param	Integer				idTask
	 * @param	Ajax.Response		response
	 */
	onRefreshed: function(idTask, response) {
		this.addContextMenu(idTask);
	},



	/**
	 * Update (reload) given task
	 *
	 * @param	Integer 	idTask
	 * @param	String		taskHtml
	 */
	update: function(idTask, taskHtml) {
		$('task-' + idTask).replace(taskHtml);

		this.addContextMenu(idTask);
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
	 * Evoke adding of a task to given project.
	 *
	 * @param	Integer 	idProject
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
	 * Adds context menu to new task and scrolls the task into view.
	 * Called after completion of (request of) task being added to project.
	 *
	 * @param	Object 		response
	 */
	onProjectTaskAdded: function(response) {
			// Get task ID from header
		var idTask = response.getTodoyuHeader('idTask');
			// Add context menu to new task
		this.addContextMenu(idTask);
			// Scroll to task
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
	 * Evoke adding of new container to given project
	 *
	 * @param	Integer idProject
	 */
	addContainerToProject: function(idProject) {
		this.removeNewTaskContainer();

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'addprojectcontainer',
				'project':	idProject
			},
			'onComplete': this.onProjectContainerAdded.bind(this)
		};
		var target	= 'project-' + idProject + '-tasks';

		Todoyu.Ui.insert(target, url, options);
	},


	/**
	 * Evoked after container having been added to project. Add context menu and scroll to the new container.
	 *
	 * @param	Object		response
	 */
	onProjectContainerAdded: function(response) {
			// Get task id from header
		var idContainer = response.getHeader('Todoyu-idContainer');

		this.addContextMenu(idContainer);
		this.scrollTo(idContainer);
	},



	/**
	 * Evoke adding of sub task to given task. Ensures existence of sub tasks display DOM-element in parent task, so the sub tasks can be shown.
	 *
	 * @param	Integer		idTask
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
	 * Evoked upon completion of request having added a sub task to given parent task. Adds context menu to new task, ensures sub tasks (including the new one) of given task are shown, scrolls new task into view.
	 *
	 * @param	Integer		idParentTask
	 * @param	Object		response
	 */
	onSubTaskAdded: function(idParentTask, response) {
		var idTask = response.getHeader('Todoyu-idTask');

		this.showSubtasks(idParentTask);

		this.addContextMenu(idTask);
		this.scrollTo(idTask);
	},



	/**
	 * Adds DOM-element to show sub tasks of (and in) given task in.
	 *
	 * @param	Integer		idTask
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
	 * Evokes adding of container under given task, ensures given task has a sub tasks display element
	 *
	 * @param	Integer		idTask
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
	 * Evoked after completion of request adding sub container to given task. Attaches context menu to container, activates display of sub tasks of task (including the new container), scrolls container into focus.
	 *
	 * @param	Integer		idParentTask
	 * @param	Object		response
	 */
	onSubContainerAdded: function(idParentTask, response) {
		var idContainer = response.getHeader('Todoyu-idContainer');

		this.addContextMenu(idContainer);
		this.showSubtasks(idParentTask);
		this.scrollTo(idContainer);
	},



	/**
	 * Attach context menu to given task
	 *
	 * @param	Integer	idTask
	 */
	addContextMenu: function(idTask) {
		this.ext.ContextMenuTask.attach();
	},

	

	/**
	 * Set given task being acknowledged
	 *
	 * @param	Event		event
	 * @param	Integer		idTask
	 */
	setAcknowledged: function(event, idTask) {
		Todoyu.Ui.stopEventBubbling(event);

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
	 * Fade out 'acknowledged' icon of given task
	 *
	 * @param	Integer		idTask
	 */
	fadeAcknowledgeIcon: function(idTask) {
		var idElement = 'task-' + idTask + '-notacknowledged';
		if( Todoyu.Ui.isVisible(idElement) ) {
			$(idElement).fade();
		}
	},



	/**
	 * Toggle visibility of details of given task, if not shown currently and not loaded yet: load details and show them.
	 *
	 * @param	Integer		idTask
	 */
	toggleDetails: function(idTask) {
			// If detail is loaded yet, send only a preference request
		if( this.isDetailsLoaded(idTask) ) {
			var details = $('task-' + idTask + '-details');
				// Toggle the details
			details.toggle();
				// Save expanded status
			this.saveTaskOpen(idTask, details.visible());
				// Call handler
			this.onDetailsToggled(idTask, '');
		} else {
				// Load details
			this.loadDetails(idTask, '', this.onDetailsToggled.bind(this));
		}
	},



	/**
	 * Handler when task details have been toggled
	 *
	 * @param	Integer				idTask
	 * @param	String				tab
	 * @param	Ajax.Response		response
	 */
	onDetailsToggled: function(idTask, tab, response) {
		this.refreshExpandedStyle(idTask);
	},



	/**
	 * Refresh the task style for class expanded
	 *
	 * @param	Integer		idTask
	 */
	refreshExpandedStyle: function(idTask) {
		var task = $('task-' + idTask);

		if( this.isDetailsVisible(idTask) ) {
			task.addClassName('expanded');
		} else {
			task.removeClassName('expanded');
		}
	},



	/**
	 *
	 * @param	Integer		idTask
	 */
	isDetailsLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-details');
	},



	/**
	 * Check if details of a task are visible (loaded and displayed)
	 *
	 * @param	Integer		idTask
	 * @return	Boolean
	 *
	 */
	isDetailsVisible: function(idTask) {
		var details = 'task-' + idTask + '-details';

		return Todoyu.exists(details) && $(details).visible();
	},



	/**
	 * Show details of given task, if not loaded yet: load them
	 *
	 * @param	Integer		idTask
	 * @param	String		tab
	 */
	showDetails: function(idTask, tab, onComplete) {
		if( this.isDetailsLoaded(idTask) ) {
			this.Tab.show(idTask, tab, onComplete);
		} else {
			var func = this.onDetailsShowed.bind(this);

			if( Object.isFunction(onComplete) ) {
				func = func.wrap(
					function(onComplete, callOriginal, idTask, tab, response) {
						onComplete(idTask, tab, response);
						callOriginal(idTask, tab, response);
					}.bind(this, onComplete)
				);
			}

			this.loadDetails(idTask, tab, func);
		}
	},



	/**
	 * Handler when task details are showed
	 *
	 * @param	Integer				idTask
	 * @param	String				tab
	 * @param	Ajax.Response		response
	 */
	onDetailsShowed: function(idTask, tab, response) {
		this.refreshExpandedStyle(idTask);
		this.Tab.show(idTask, tab);
	},



	/**
	 * Show sub tasks of given task.
	 *
	 * @param	Integer		idTask
	 */
	showSubtasks: function(idTask) {
		var idDiv		= 'task-' + idTask + '-subtasks';
		var idTrigger	= 'task-' + idTask + '-subtasks-trigger';

		if( $(idDiv) ) {
			$(idDiv).show();
		}

		if( $(idTrigger) ){
			if( ! $(idTrigger).hasClassName('expandable') )	{
				$(idTrigger).addClassName('expandable');
			}

			if( ! $(idTrigger).hasClassName('expanded') )	{
				$(idTrigger).addClassName('expanded');
			}
		}
	},



	/**
	 * Load details of given task and append them to (have them shown inside) header of given task
	 *
	 * @param	Integer		idTask
	 * @param	String		tab
	 */
	loadDetails: function(idTask, tab, onComplete) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'detail',
				'task':		idTask,
				'tab':		tab
			},
			'onComplete': this.onDetailsLoaded.bind(this, idTask, tab, onComplete)
		};
		var target	= 'task-' + idTask + '-header';

			// Fade out the "not acknowledged" icon if its there
		this.fadeAcknowledgeIcon.delay(1, idTask);

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handler when details are loaded
	 *
	 * @param	Integer				idTask
	 * @param	String				tab
	 * @param	Function			onComplete
	 * @param	Ajax.Response		response
	 */
	onDetailsLoaded: function(idTask, tab, onComplete, response) {
		Todoyu.callIfExists(onComplete, this, idTask, tab, response);
	},



	/**
	 * Save given task being expanded status
	 *
	 * @param	Integer		idTask
	 * @param	Boolean		open
	 */
	saveTaskOpen: function(idTask, open) {
		var value = open ? 1 : 0;
		this.ext.savePref('taskopen', value, idTask);
	},



	/**
	 * Check whether given task is loaded and exists in DOM
	 *
	 * @return	Boolean
	 */
	isLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask);
	}

};