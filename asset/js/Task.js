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
 *	@module		Project
 */

/**
 * Task
 *
 * @class		Task
 * @namespace	Todoyu.Ext.project
 */
Todoyu.Ext.project.Task = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.project,

	/**
	 * @property	type
	 * @type		Object
	 */
	type: {
		task:		1,
		container:	2
	},



	/**
	 * Load and display editing form of given task
	 *
	 * @method	edit
	 * @param	{Number}		idTask
	 */
	edit: function(idTask) {
		this.Edit.createFormWrapDivs(idTask);
		this.Edit.loadForm(idTask);
	},




	/**
	 * Copy a task (and sub tasks) to clipboard
	 *
	 * @method	copy
	 * @param	{Number}		idTask
	 */
	copy: function(idTask) {
		var withSubtasks = false;

			// Ask to copy sub tasks
		if( this.hasSubtasks(idTask) ) {
			withSubtasks = confirm('[LLL:project.task.copySubtasks]') ? 1 : 0;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'copy',
				'task':		idTask,
				'subtasks':	withSubtasks
			},
			onComplete: this.onCopied.bind(this, idTask)
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
	 * @method	onCopied
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onCopied: function(idTask, response) {

	},



	/**
	 * Cut task (to clipboard)
	 *
	 * @method	cut
	 * @param	{Number}			idTask
	 */
	cut: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'cut',
				'task':		idTask
			},
			onComplete: this.onCut.bind(this, idTask)
		};

		Todoyu.send(url, options);

		this.highlight(idTask);
		this.highlightSubtasks(idTask);
	},



	/**
	 * Handler when task is cut
	 *
	 * @method	onCut
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onCut: function(idTask, response) {

	},



	/**
	 * Paste task
	 *
	 * @method	paste
	 * @param	{Number}	idTask		Task where to insert
	 * @param	{String}	mode		Insert mode (in,after,before)
	 */
	paste: function(idTask, mode) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'paste',
				'task':		idTask,
				'mode':		mode
			},
			onComplete: this.onPasted.bind(this, idTask, mode)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when task is pasted
	 *
	 * @method	onPasted
	 * @param	{Number}			idTask
	 * @param	{String}			insertMode
	 * @param	{Ajax.Response}		response
	 */
	onPasted: function(idTask, insertMode, response) {
		var idTaskNew		= response.getTodoyuHeader('idTask');
		var clipboardMode	= response.getTodoyuHeader('clipboardMode');

			// If task was cut, remove old element
		if( clipboardMode === 'cut' ) {
			this.removeTaskElement(idTaskNew);
		}

			// Insert as sub task of the current task
		if( insertMode === 'in' ) {
				// If sub task container already exists, add it
			if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
				$('task-' + idTask + '-subtasks').insert({
					'bottom': response.responseText
				});
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
	 *
	 * @method	pasteNotAllowed
	 */
	pasteNotAllowed: function() {
		alert("[LLL:project.task.pasteNotAllowed]");
	},



	/**
	 * Clone given task (open new task creation form with attributes of task filled-in, title renamed to 'copy of..')
	 *
	 * @method	clone
	 * @param	{Number}		idTask
	 */
	clone: function(idTask) {
			// Has sub tasks? ask whether to include them in copy
		var copySubTasks	= ( this.hasSubtasks(idTask) ) ? (confirm('[LLL:project.task.cloneSubtasks.confirm]') ? 1 : 0) : false;

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'clone',
				'task':		idTask,
				'subtasks':	copySubTasks
			},
			onComplete: this.onCloned.bind(this, idTask)
		};
		var target	= 'task-' + idTask;

			// If task has already subtasks, add the form after the subtasks
		if( this.hasSubTasksAndContainer(idTask) ) {
			target += '-subtasks';
		}

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handler when task was cloned
	 *
	 * @method	onCloned
	 * @param	{Number}			idSourceTask
	 * @param	{Ajax.Response}		response
	 */
	onCloned: function(idSourceTask, response) {
			// Get task id from header
		var idTask = response.getTodoyuHeader('idTask');
			// Attach context menu
		this.addContextMenu(idTask);
			// Highlight cloned element
		this.highlight(idTask);
		this.highlightSubtasks(idTask);

		Todoyu.Hook.exec('project.task.cloned', idSourceTask, idTask);
	},



	/**
	 * Delete a task
	 *
	 * @method	remove
	 * @param	{Number}		idTask
	 */
	remove: function(idTask, container) {
		var confirmLabel	= container === true ? '[LLL:project.task.js.removecontainer.question]' : '[LLL:project.task.js.removetask.question]';
		if( ! confirm(confirmLabel) ) {
			return;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'delete',
				'task':		idTask
			},
			onComplete: this.onRemoved.bind(this, idTask)
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
	 * @method	onRemove
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(idTask, response) {
		Todoyu.Hook.exec('project.task.removed', idTask);
	},



	/**
	 * Remove given task element and related sub tasks
	 *
	 * @method	removeTaskElement
	 * @param	{Number}	idTask
	 */
	removeTaskElement: function(idTask) {
		if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
			$('task-' + idTask + '-subtasks').remove();
		}
		if( Todoyu.exists('task-' + idTask) ) {
			if( this.isSubtask(idTask) ) {
				var idParent	= this.getParentTaskID(idTask);

				$('task-' + idTask).remove();

				this.checkAndRemoveTriggerFromTask(idParent);
			} else {
				$('task-' + idTask).remove();
			}
		}
	},



	/**
	 * Scroll to given task
	 *
	 * @method	scrollTo
	 * @param	{Number}		idTask
	 */
	scrollTo: function(idTask) {
		if( Todoyu.exists('task-' + idTask) ) {
			$('task-' + idTask).scrollToElement();
		}
	},



	/**
	 * Highlight a task
	 *
	 * @method	highlight
	 * @param	{Number}		idTask
	 */
	highlight: function(idTask) {
		if( Todoyu.exists('task-' + idTask) ) {
			new Effect.Highlight('task-' + idTask);
		}
	},



	/**
	 * Highlight sub task container of a task
	 *
	 * @method	highlightSubtasks
	 * @param	{Number}		idTask
	 */
	highlightSubtasks: function(idTask) {
		if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
			new Effect.Highlight('task-' + idTask + '-subtasks');
		}
	},



	/**
	 * Check if a task has sub tasks
	 *
	 * @method	hasSubtasks
	 * @param	{Number}		idTask
	 * @return	{Boolean}
	 */
	hasSubtasks: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks') || ($('task-' + idTask + '-subtasks-trigger') && $('task-' + idTask + '-subtasks-trigger').hasClassName('expandable'));
	},



	/**
	 * Check if sub task container is in DOM
	 *
	 * @method	hasSubtaskContainer
	 * @param	{Number}		idTask
	 */
	hasSubtaskContainer: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-subtasks');
	},



	/**
	 * Check whether given task has sub tasks and a sub task container
	 *
	 * @method	hasSubTasksAndContainer
	 * @param	{Number}	idTask
	 */
	hasSubTasksAndContainer: function(idTask) {
		return this.hasSubtasks(idTask) && this.hasSubtaskContainer(idTask);
	},



	/**
	 * check if task has a parent task
	 *
	 * @method	hasParentTask
	 * @param	{Number}		idTask
	 */
	hasParentTask: function(idTask){
		return $('task-' + idTask).up().hasClassName('subtasks');
	},



	/**
	 * Get ID of parent task of given task
	 *
	 * @method	getParentTaskID
	 * @return	{Number}
	 */
	getParentTaskID: function(idTask) {
		var idParent	= false;

		if( Todoyu.exists('task-' + idTask) ) {
			var subTaskContainer	= $('task-' + idTask).up('.subtasks');

			if( subTaskContainer !== undefined ) {
				idParent	= subTaskContainer.id.split('-')[1];
			}
		}

		return idParent;
	},



	/**
	 * Check whether given task is a sub task
	 *
	 * @method	isSubtask
	 * @return	{Boolean}
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
	 * @method	checkAndRemoveTriggerFromParent
	 * @param	{Number}		idTask
	 */
	checkAndRemoveTriggerFromParent: function(idTask) {
		var idArray = $('task-' + idTask).up().id.split('-');
		var idParentTask = idArray[1];

		this.checkAndRemoveTriggerFromTask(idParentTask);
	},



	/**
	 * Is this the only sub task? remove expandability
	 *
	 * @method	checkAndRemoveTriggerFromTask
	 * @param	{Number}
	 */
	checkAndRemoveTriggerFromTask: function(idTask) {
		if( $('task-' + idTask + '-subtasks').select('div.task').size() < 1 ) {
			$('task-' + idTask + '-subtasks-trigger').removeClassName('expandable');
		}
	},



	/**
	 * Change status of given task to given status, in DB and visual
	 *
	 * @method	updateStatus
	 * @param	{Number}		idTask
	 * @param	{Number}	 	status
	 */
	updateStatus: function(idTask, status) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				task:	idTask,
				action:	'setstatus',
				status:	status
			},
			onComplete: this.onStatusUpdated.bind(this, idTask, status)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Handler when status has been updated
	 *
	 * @method	onStatusUpdated
	 * @param	{Number}				idTask
	 * @param	{Number}				status
	 * @param	{Ajax.Response}			response
	 */
	onStatusUpdated: function(idTask, status, response) {
		Todoyu.Hook.exec('project.task.statusUpdated', idTask, status);

		var statusNotAllowed	= response.getTodoyuHeader('statusNotAllowed') == 1;

		if( statusNotAllowed ) {
			Todoyu.notifyInfo('[LLL:project.task.statusNotVisible]');
			Effect.BlindUp('task-' + idTask, {
				'duration': 0.7
			});
		} else {
			if( this.isDetailsLoaded(idTask) ) {
				this.refresh(idTask);
			} else {
				if( this.isLoaded(idTask) ) {
					this.setStatus(idTask, status);
				}
			}
		}
	},



	/**
	 * Get status key of the task
	 *
	 * @method	getStatus
	 * @param	{Number}		idTask
	 */
	getStatus: function(idTask) {
		if( this.isLoaded(idTask) ) {
			var element		= $('task-' + idTask + '-header').down('.headLabel');

			return this.ext.getStatusOfElement(element);
		} else {
			return 0;
		}
	},



	/**
	 * Set status of given task
	 *
	 * @method	setStatus
	 * @param	{Number}		idTask
	 * @param	{String}		newStatus
	 */
	setStatus: function(idTask, newStatus) {
		if( this.isLoaded(idTask) ) {
			var head		= $('task-' + idTask + '-header');
			var headLabel	= head.down('.headLabel');

			this.ext.setStatusOfElement(head, newStatus);
			this.ext.setStatusOfElement(headLabel, newStatus);
		}
	},




	/**
	 * Refresh a task if loaded
	 *
	 * @method	refresh
	 * @param	{Number}			idTask
	 */
	refresh: function(idTask) {
		var target	= 'task-' + idTask;
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'get',
				'task':		idTask
			},
			onComplete: this.onRefreshed.bind(this, idTask)
		};

		if( Todoyu.exists(target) ) {
				// Update task
			Todoyu.Ui.replace(target, url, options);
		}
	},



	/**
	 * Handler when task has been refreshed
	 *
	 * @method	onRefreshed
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onRefreshed: function(idTask, response) {
		this.addContextMenu(idTask);
	},



	/**
	 * Update given task with given content
	 *
	 * @method	update
	 * @param	{Number} 	idTask
	 * @param	{String}	taskHtml
	 */
	update: function(idTask, taskHtml) {
		if( Todoyu.exists('task-' + idTask + '-subtasks') ) {
			$('task-' + idTask + '-subtasks').remove();
		}

		$('task-' + idTask).replace(taskHtml);

		this.addContextMenu(idTask);
	},



	/**
	 * Remove new task container element from DOM
	 *
	 * @method	removeNewTaskContainer
	 */
	removeNewTaskContainer: function() {
		if( Todoyu.exists('task-0') ) {
			$('task-0').remove();
		}
	},



	/**
	 * Evoke adding of a task to given project.
	 *
	 * @method	addTaskToProject
	 * @param	{Number} 	idProject
	 */
	addTaskToProject: function(idProject) {
		this.removeNewTaskContainer();

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'addprojecttask',
				'project':	idProject
			},
			onComplete: this.onProjectTaskAdded.bind(this)
		};

			// Lost tasks are displayed? => add before lost tasks / else: add at bottom of list
		var target;
		if( Todoyu.exists('project-' + idProject + '-losttasks') ) {
			target	= 'project-' + idProject + '-losttasks';
			options.insertion = 'before';
		} else {
			target	= 'project-' + idProject + '-tasks';
			options.insertion = 'bottom';
		}

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 * Adds context menu to new task and scrolls the task into view.
	 * Called after completion of (request of) task being added to project.
	 *
	 * @method	onProjectTaskAdded
	 * @param	{Object} 		response
	 */
	onProjectTaskAdded: function(response) {
			// Get task ID from header
		var idTask = response.getTodoyuHeader('idTask');
			// Add context menu to new task
		this.addContextMenu(idTask);
			// Scroll to task
		this.scrollTo(idTask);

		Todoyu.Hook.exec('project.task.formLoaded', idTask);
	},



	/**
	 * Focus title field in task edit form
	 *
	 * @hooked	onTaskEdit
	 * @method	focusTitleField
	 * @param	{Number}		idTask
	 */
	focusTitleField: function(idTask) {
		$('task-' + idTask + '-field-title').focus();
	},



	/**
	 * Evoke adding of new container to given project
	 *
	 * @method	addContainerToProject
	 * @param	{Number} idProject
	 */
	addContainerToProject: function(idProject) {
		this.removeNewTaskContainer();

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'addprojectcontainer',
				'project':	idProject
			},
			onComplete: this.onProjectContainerAdded.bind(this)
		};
		var target	= 'project-' + idProject + '-tasks';

		Todoyu.Ui.insert(target, url, options);
	},



	/**
	 * Evoked after container having been added to project. Add context menu and scroll to the new container.
	 *
	 * @method	onProjectContainerAdded
	 * @param	{Ajax.Response}		response
	 */
	onProjectContainerAdded: function(response) {
			// Get task id from header
		var idContainer = response.getTodoyuHeader('idContainer');

		this.addContextMenu(idContainer);
		this.scrollTo(idContainer);

		Todoyu.Hook.exec('project.task.containerAdded', idContainer);
	},



	/**
	 * Evoke adding of sub task to given task. Ensures existence of sub tasks display DOM-element in parent task, so the sub tasks can be shown.
	 *
	 * @method	addSubTask
	 * @param	{Number}		idTask
	 */
	addSubTask: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'addsubtask',
				'task':		idTask
			},
			onComplete: this.onSubTaskAdded.bind(this, idTask)
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
	 * @method	onSubTaskAdded
	 * @param	{Number}			idParentTask
	 * @param	{Ajax.Response}		response
	 */
	onSubTaskAdded: function(idParentTask, response) {
		var idTask = response.getHeader('Todoyu-idTask');

		this.showSubtasks(idParentTask);

		this.addContextMenu(idTask);
		this.scrollTo(idTask);

		Todoyu.Hook.exec('project.task.subtaskAdded', idTask);
	},



	/**
	 * Adds DOM-element to show sub tasks of (and in) given task in.
	 *
	 * @method	createSubTaskContainer
	 * @param	{Number}		idTask
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
	 * @method	addSubContainer
	 * @param	{Number}		idTask
	 */
	addSubContainer: function(idTask) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'addsubcontainer',
				'task':		idTask
			},
			onComplete: this.onSubContainerAdded.bind(this, idTask)
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
	 * @method	onSubContainerAdded
	 * @param	{Number}			idParentTask
	 * @param	{Ajax.Response}		response
	 */
	onSubContainerAdded: function(idParentTask, response) {
		var idContainer = response.getHeader('Todoyu-idContainer');

		this.addContextMenu(idContainer);
		this.showSubtasks(idParentTask);
		this.scrollTo(idContainer);

		Todoyu.Hook.exec('project.task.subContainerAdded', idContainer);
	},



	/**
	 * Attach context menu to given task
	 *
	 * @method	addContextMenu
	 * @param	{Number}	idTask
	 */
	addContextMenu: function(idTask) {
		this.ext.ContextMenuTask.attach();
	},



	/**
	 * Set given task being acknowledged
	 *
	 * @method	setAcknowledged
	 * @param	{Event}		event
	 * @param	{Number}		idTask
	 */
	setAcknowledged: function(event, idTask) {
		Todoyu.Ui.stopEventBubbling(event);

		this.fadeAcknowledgeIcon(idTask);

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'acknowledge',
				'task':		idTask
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Fade out 'acknowledged' icon of given task
	 *
	 * @method	fadeAcknowledgeIcon
	 * @param	{Number}		idTask
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
	 * @method	toggleDetails
	 * @param	{Number}		idTask
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
	 * @method	onDetailsToggled
	 * @param	{Number}			idTask
	 * @param	{String}			tab
	 * @param	{Ajax.Response}		response
	 */
	onDetailsToggled: function(idTask, tab, response) {
		this.refreshExpandedStyle(idTask);

		Todoyu.Hook.exec('project.task.detailsToggled', idTask);
	},



	/**
	 * Refresh the task style for class expanded
	 *
	 * @method	refreshExpandedStyle
	 * @param	{Number}		idTask
	 */
	refreshExpandedStyle: function(idTask) {
		if( this.isDetailsVisible(idTask) ) {
			this.setExpandedStyle(idTask, true);
		} else {
			this.setExpandedStyle(idTask, false);
		}
	},



	/**
	 * Set task style expanded/ collapsed
	 *
	 * @method	setExpandedStyle
	 * @param	{Number}	idTask
	 * @param	{Boolean}	isExpanded
	 */
	setExpandedStyle: function(idTask, isExpanded) {
		var task = $('task-' + idTask);

		if( isExpanded ) {
			task.addClassName('expanded');
		} else {
			task.removeClassName('expanded');
		}
	},



	/**
	 * Check whether details of given task are loaded
	 *
	 * @method	isDetailsLoaded
	 * @param	{Number}		idTask
	 */
	isDetailsLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask + '-details');
	},



	/**
	 * Check if details of a task are visible (loaded and displayed)
	 *
	 * @method	isDetailsVisible
	 * @param	{Number}		idTask
	 * @return	{Boolean}
	 */
	isDetailsVisible: function(idTask) {
		var details = 'task-' + idTask + '-details';

		return Todoyu.exists(details) && $(details).visible();
	},



	/**
	 * Show details of given task, if not loaded yet: load them
	 *
	 * @method	showDetails
	 * @param	{Number}		idTask
	 * @param	{String}		tab
	 * @param	{Function}		onComplete
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
	 * @method	onDetailsShowed
	 * @param	{Number}			idTask
	 * @param	{String}			tab
	 * @param	{Ajax.Response}		response
	 */
	onDetailsShowed: function(idTask, tab, response) {
		this.refreshExpandedStyle(idTask);
		this.Tab.show(idTask, tab);
	},



	/**
	 * Show sub tasks of given task.
	 *
	 * @method	showSubtasks
	 * @param	{Number}		idTask
	 */
	showSubtasks: function(idTask) {
		var idDiv		= 'task-' + idTask + '-subtasks';
		var idTrigger	= 'task-' + idTask + '-subtasks-trigger';

		if( $(idDiv) ) {
			$(idDiv).show();
		}

		if( $(idTrigger) ){
			if( ! $(idTrigger).hasClassName('expandable') ) {
				$(idTrigger).addClassName('expandable');
			}

			if( ! $(idTrigger).hasClassName('expanded') ) {
				$(idTrigger).addClassName('expanded');
			}
		}
	},



	/**
	 * Load details of given task and append them to (have them shown inside) header of given task
	 *
	 * @method	loadDetails
	 * @param	{Number}		idTask
	 * @param	{String}		tab
	 */
	loadDetails: function(idTask, tab, onComplete) {
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'detail',
				'task':		idTask,
				'tab':		tab
			},
			onComplete: this.onDetailsLoaded.bind(this, idTask, tab, onComplete)
		};
		var target	= 'task-' + idTask + '-header';

			// Fade out the "not acknowledged" icon if its there
		this.fadeAcknowledgeIcon.delay(1, idTask);

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 * Handler when details are loaded
	 *
	 * @method	onDetailsLoaded
	 * @param	{Number}			idTask
	 * @param	{String}			tab
	 * @param	{Function}			onComplete
	 * @param	{Ajax.Response}		response
	 */
	onDetailsLoaded: function(idTask, tab, onComplete, response) {
		Todoyu.callIfExists(onComplete, this, idTask, tab, response);
	},



	/**
	 * Save given task being expanded status
	 *
	 * @method	saveTaskOpen
	 * @param	{Number}		idTask
	 * @param	{Boolean}		open
	 */
	saveTaskOpen: function(idTask, open) {
		var value = open ? 1 : 0;
		this.ext.savePref('taskopen', value, idTask);
	},



	/**
	 * Check whether given task is loaded and exists in DOM
	 *
	 * @method	isLoaded
	 * @return	{Boolean}
	 */
	isLoaded: function(idTask) {
		return Todoyu.exists('task-' + idTask);
	}

};