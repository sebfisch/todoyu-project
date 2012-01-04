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
		var withSubTasks = false;

			// Ask to copy sub tasks
		if( this.hasSubTasks(idTask) ) {
			withSubTasks = confirm('[LLL:project.task.copySubtasks]') ? 1 : 0;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:		'copy',
				task:		idTask,
				subtasks:	withSubTasks
			},
			onComplete: this.onCopied.bind(this, idTask)
		};

		Todoyu.send(url, options);

			// Highlight copied task
		this.highlight(idTask);

			// Highlight sub tasks if selected to copy
		if( withSubTasks ) {
			this.highlightSubTasks(idTask);
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
				task:	idTask
			},
			onComplete: this.onCut.bind(this, idTask)
		};

		Todoyu.send(url, options);

		this.highlight(idTask);
		this.highlightSubTasks(idTask);
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
				task:	idTask,
				mode:	mode
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

			// If task was cut, remove old element
		if( response.getTodoyuHeader('clipboardMode') === 'cut' ) {
			this.removeTaskElement(idTaskNew);
		}

		switch( insertMode ) {
				// Insert as sub task of the current task
			case 'in':
					// If sub task container already exists, add it
				if( this.hasSubTasksContainer(idTask) ) {
					this.getSubTasksContainer(idTask).insert({bottom: response.responseText});
					this.ext.TaskTree.expandSubTasks(idTask);
					this.updateSubTasksExpandTrigger(idTask);
				} else {
						// If no sub task container available, refresh task and load its sub task
					this.refresh(idTask);
						// Append sub tasks
					this.ext.TaskTree.loadSubTasks(idTask, this.ext.TaskTree.toggleSubTasksTriggerIcon.bind(this, idTask));
				}
				break;

				// Insert task before current
			case 'before':
				$('task-' + idTask).insert({before: response.responseText});
				break;

				// Insert task after current
			case 'after':
				var target = this.hasSubTasksContainer(idTask) ? this.getSubTasksContainerID(idTask) : 'task-' + idTask;
				$(target).insert({after: response.responseText});
				break;
		}

			// Attach context menu to all tasks (so the pasted ones get one too), re-init drag&drop
		this.ext.ContextMenuTask.attach();

		if( Todoyu.getArea() === 'project' ) {
			this.ext.TaskTree.reloadSortable();
		}

			// Highlight the new pasted task
		this.highlight(idTaskNew);
		this.highlightSubTasks(idTaskNew);

		this.ext.TaskTree.removeEmptySubTaskContainers();
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
		var copySubTasks = 0;
		if ( this.hasSubTasks(idTask) ) {
				// Has sub tasks? ask whether to include them in copy
			copySubTasks	= confirm('[LLL:project.task.cloneSubtasks.confirm]') ? 1 : 0;
		}

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:		'clone',
				task:		idTask,
				subtasks:	copySubTasks
			},
			onComplete: this.onCloned.bind(this, idTask)
		};
		var target	= 'task-' + idTask;

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
			// Get task ID from header
		var idTask = response.getTodoyuHeader('idTask');
			// Attach context menu, re-init drag&drop
		this.addContextMenu(idTask);

		if( Todoyu.getArea() === 'project' ) {
			this.ext.TaskTree.reloadSortable();
		}

			// Highlight cloned element
		this.highlight(idTask);
		this.highlightSubTasks(idTask);

		Todoyu.Hook.exec('project.task.cloned', idSourceTask, idTask);
	},



	/**
	 * Mark a task
	 *
	 * @param	{Number}	idTask
	 * @param	{Boolean}	mark
	 * @param	{Boolean}	withSubTasks
	 */
	markTask: function(idTask, mark, withSubTasks) {
		var method	= mark !== false ? 'addClassName' : 'removeClassName';

		$('task-' + idTask)[method]('marked');

		if( withSubTasks !== false ) {
			this.getSubTasks(idTask).each(function(subTask) {
				var idSubTask	= subTask.id.split('-').last();
				this.markTask(idSubTask, mark, true);
			}, this);
		}
	},



	/**
	 * Confirm and remove task + sub tasks
	 *
	 * @method	remove
	 * @param	{Number}		idTask
	 * @param	{Boolean}		isContainer
	 */
	remove: function(idTask, isContainer) {
		Todoyu.ContextMenu.hide();
		this.markTask(idTask);

		var confirmLabel	= isContainer === true ? '[LLL:project.task.js.removecontainer.question]' : '[LLL:project.task.js.removetask.question]';
		if( ! confirm(confirmLabel) ) {
			this.markTask(idTask, false);
			return;
		}
			// Removal has been confirmed
		var idParent= this.getParentTaskID(idTask);

			// Animate element deletion (task itself and empty container of sub elements if present)
		this.animateRemove(idTask);

			// Start deletion request
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'delete',
				task:	idTask
			},
			onComplete: this.onRemoved.bind(this, idTask, idParent)
		};

		Todoyu.send(url, options);
	},



	/**
	 * Animate removal of a task
	 *
	 * @param	{Number}	idTask
	 */
	animateRemove: function(idTask) {
		var idParent= this.getParentTaskID(idTask);
		
			// Animate element deletion (task itself and empty container of sub elements if present)
		if( this.hasSubTasksContainer(idTask) ) {
			Effect.BlindUp(this.getSubTasksContainer(idTask), {
				duration: 0.5
			});
		}
		Effect.BlindUp('task-' + idTask, {
			duration: 	0.7,
			afterFinish: function(event) {
					// Update sub elements expand/collapse trigger of parent task after finishing the animation
				this.updateSubTasksExpandTrigger(idParent);
				$('task-' + idTask).remove();
			}.bind(this)
		});
	},



	/**
	 * Handler when task removed
	 *
	 * @method	onRemove
	 * @param	{Number}			idTask
	 * @param	{Number}			idParent
	 * @param	{Ajax.Response}		response
	 */
	onRemoved: function(idTask, idParent, response) {
		Todoyu.Hook.exec('project.task.removed', idTask);
	},



	/**
	 * Remove given task element and related sub tasks
	 *
	 * @method	removeTaskElement
	 * @param	{Number}	idTask
	 */
	removeTaskElement: function(idTask) {
		if( Todoyu.exists('task-' + idTask) ) {
			var idParentTask = this.getParentTaskID(idTask);

			$('task-' + idTask).remove();

			if( idParentTask ) {
				this.updateSubTasksExpandTrigger(idParentTask);
				this.removeEmptySubTaskContainer(idParentTask);
			}
		}
	},

	

	/**
	 * Remove empty subtask container
	 *
	 * @param	{Number}	idTask
	 */
	removeEmptySubTaskContainer: function(idTask) {
		if( this.hasSubTasksContainer(idTask) && !this.hasSubTasks(idTask) ) {
			this.getSubTasksContainer(idTask).remove();
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
	 * Highlight (container of) sub tasks  of a task
	 *
	 * @method	highlightSubTasks
	 * @param	{Number}		idTask
	 */
	highlightSubTasks: function(idTask) {
		if( this.hasSubTasksContainer(idTask) ) {
			var idSubTasksContainer	= this.getSubTasksContainerID(idTask);

			new Effect.Highlight(idSubTasksContainer);
		}
	},



	/**
	 * Check whether the task with the given ID has sub tasks
	 *
	 * @method	hasSubTasks
	 * @param	{Number}		idTask
	 * @return	{Boolean}
	 */
	hasSubTasks: function(idTask) {
		var subTasksTrigger				= this.getSubTasksExpandTrigger(idTask);
		var isSubTasksTriggerExpandable	= subTasksTrigger ? subTasksTrigger.hasClassName('expandable') : false;

		return this.hasSubTasksContainer() || isSubTasksTriggerExpandable;
	},



	/**
	 * Check if sub tasks container is in DOM
	 *
	 * @method	hasSubTasksContainer
	 * @param	{Number}				idTask
	 */
	hasSubTasksContainer: function(idTask) {
		return Todoyu.exists(this.getSubTasksContainerID(idTask));
	},



	/**
	 * Get ID of sub tasks container element of task with given ID
	 *
	 * @method	getSubTasksContainerID
	 * @param	{Number}				idTask
	 * @return	{String}
	 */
	getSubTasksContainerID: function(idTask) {
		return 'task-' + idTask + '-subtasks';
	},



	/**
	 * Get sub tasks container element to given task
	 *
	 * @method	hasSubTasksContainer
	 * @param	{Number}		idTask
	 */
	getSubTasksContainer: function(idTask) {
		return $(this.getSubTasksContainerID(idTask));
	},



	/**
	 * Check whether task (exists and) has a parent task
	 *
	 * @method	hasParentTask
	 * @param	{Number}		idTask
	 */
	hasParentTask: function(idTask){
		if( ! Todoyu.exists('task-' + idTask) ) {
			return false;
		}

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
				// Traverse up to sub tasks container of parent
			var subTasksContainer	= $('task-' + idTask).up('.subtasks');
			if( subTasksContainer !== undefined ) {
					// Extract parent task ID
				idParent	= subTasksContainer.id.split('-')[1];
			}
		}

		return idParent;
	},



	/**
	 * Get parent task element
	 *
	 * @param	{Number}	idTask
	 * @return	{Element}
	 */
	getParentTask: function(idTask) {
		var parent 	= false;
		var idParent= this.getParentTaskID();

		if( idParent  ) {
			parent = $('task-' + idTask);
		}

		return parent;
	},



	/**
	 * Get (optionally only visible) child tasks of given task
	 *
	 * @method	getSubTasks
	 * @param	{Number}	idTask
	 * @param	{Boolean}	visibleOnly			Default: false
	 * @return	{Array}
	 */
	getSubTasks: function(idTask, visibleOnly) {
		visibleOnly	= ( typeof visibleOnly == 'undefined' ) ? false : visibleOnly;
		var subTasks= [];

		if( this.hasSubTasksContainer(idTask) ) {
			if( ! visibleOnly ) {
					// Get all sub tasks
				subTasks	= this.getSubTasksContainer(idTask).select('div.task');
			} else {
					// Get only visible sub tasks
				this.getSubTasks(idTask, false).each(
					function(subTask) {
						if( subTask.visible() ) {
							subTasks.push(subTask);
						}
					}
				);
			}
		}

		return subTasks;
	},



	/**
	 * Check whether given task is a sub task
	 *
	 * @method	isSubTask
	 * @return	{Boolean}
	 */
	isSubTask: function(idTask) {
		return Todoyu.exists('task-' + idTask) && $('task-' + idTask).up('.subtasks') !== undefined;
	},



	/**
	 * Get sub tasks expand trigger element of given task
	 *
	 * @method	getSubTasksExpandTrigger
	 * @param	{Number}	idTask
	 */
	getSubTasksExpandTrigger: function(idTask) {
		return $(this.getSubTasksContainerID(idTask) + '-trigger');
	},



	/**
	 * Check whether given task has a sub tasks expand trigger
	 *
	 * @method	hasSubTasksExpandTrigger
	 * @param	{Number}	idTask
	 * @return	{Boolean}
	 */
	hasSubTasksExpandTrigger: function(idTask) {
		return Todoyu.exists(this.getSubTasksExpandTrigger(idTask));
	},



	/**
	 * Add or remove the subtask trigger. Depends on existing (visible) sub tasks
	 *
	 * @method	updateSubTasksExpandTrigger
	 * @param	{Number}
	 */
	updateSubTasksExpandTrigger: function(idTask) {
		var show	= this.getSubTasks(idTask, true).length > 0;

		if( show ) {
			this.addSubTasksExpandTrigger(idTask);
		} else {
			this.removeSubTasksExpandTrigger(idTask);
		}
	},



	/**
	 * Remove expand trigger from given task header
	 *
	 * @method	addSubTasksExpandTrigger
 	 * @param	{Number}	idTask
	 */
	addSubTasksExpandTrigger: function(idTask) {
		var trigger	= this.getSubTasksExpandTrigger(idTask);
		if( trigger ) {
			trigger.addClassName('expandable');
		}
	},



	/**
	 * Remove expand trigger from given task header
	 *
	 * @method	removeSubTasksExpandTrigger
 	 * @param	{Number}	idTask
	 */
	removeSubTasksExpandTrigger: function(idTask) {
		var trigger	= this.getSubTasksExpandTrigger(idTask);
		if( trigger ) {
			trigger.removeClassName('expandable');
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
			var element		= $('task-' + idTask).down('.statusColor');

			return this.ext.getStatusOfElement(element);
		} else {
			return 0;
		}
	},



	/**
	 * Set given task status styles
	 *
	 * @method	setStatus
	 * @param	{Number}		idTask
	 * @param	{String}		newStatus
	 */
	setStatus: function(idTask, newStatus) {
		if( this.isLoaded(idTask) ) {
			var task		= $('task-' + idTask);
			
			this.ext.setStatusOfElement(task.down('h3'), newStatus);
			this.ext.setStatusOfElement(task.down('.statusColor'), newStatus);
			this.ext.setStatusOfElement(task.down('.details'), newStatus);
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
				task:	idTask
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
	 * Refresh task header
	 *
	 * @method	refresh
	 * @param	{Number}			idTask
	 */
	refreshHeader: function(idTask) {
		var target	= 'task-' + idTask + '-header';
		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'header',
				task:	idTask
			},
			onComplete: this.onHeaderRefreshed.bind(this, idTask)
		};

		if( Todoyu.exists(target) ) {
			Todoyu.Ui.replace(target, url, options);
		}
	},



	/**
	 * Handler when task header has been refreshed
	 *
	 * @method	onRefreshed
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onHeaderRefreshed: function(idTask, response) {
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
		$('task-' + idTask).replace(taskHtml);

		this.addContextMenu(idTask);

		if( Todoyu.getArea() === 'project' ) {
			this.ext.TaskTree.reloadSortable();
		}
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
				action:		'addprojecttask',
				project:	idProject
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

		this.ext.TaskTree.reloadSortable();
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
				action:		'addprojectcontainer',
				project:	idProject
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
			// Get task ID from header
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
				task:	idTask
			},
			onComplete: this.onSubTaskAdded.bind(this, idTask)
		};

		if( ! this.hasSubTasksContainer(idTask) ) {
			this.createSubTaskContainer(idTask);
		}

		var idSubTasksContainer	= this.getSubTasksContainerID(idTask);

		Todoyu.Ui.insert(idSubTasksContainer, url, options);
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

		this.showSubTasks(idParentTask);

		this.addContextMenu(idTask);
		this.scrollTo(idTask);

		Todoyu.Hook.exec('project.task.subtaskAdded', idTask);
	},



	/**
	 * Adds DOM-element to show sub tasks of (and in) given task in.
	 *
	 * @method	createSubTaskContainer
	 * @param	{Number}		idTask
	 * @return	{Element}
	 */
	createSubTaskContainer: function(idTask) {
		var idSubTasksContainer	= this.getSubTasksContainerID(idTask);

		if( ! Todoyu.exists(idSubTasksContainer) ) {
			$('task-' + idTask).insert(new Element('div', {
								id:			idSubTasksContainer,
								'class':	'subtasks'
							}));
			$('task-' + idTask + '-subtasks-trigger').addClassName('expandable').addClassName('expanded');
		}

		return $(idSubTasksContainer);
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
				task:	idTask
			},
			onComplete: this.onSubContainerAdded.bind(this, idTask)
		};

		if( ! this.hasSubTasksContainer(idTask) ) {
			this.createSubTaskContainer(idTask);
		}

		var idSubTasksContainer	= this.getSubTasksContainerID(idTask);

		Todoyu.Ui.insert(idSubTasksContainer, url, options);
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
		this.showSubTasks(idParentTask);
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
	 * @param	{Number}		idTask
	 */
	setAcknowledged: function(idTask) {
		this.fadeAcknowledgeIcon(idTask);

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'acknowledge',
				task:		idTask
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
	 * @method	showSubTasks
	 * @param	{Number}		idTask
	 */
	showSubTasks: function(idTask) {
			// If sub tasks container present: set visible
		if( this.hasSubTasksContainer(idTask) ) {
			this.getSubTasksContainer(idTask).show();
		}
			// If sub tasks expand trigger present: set expandable, expanded
		if( this.hasSubTasksExpandTrigger(idTask) ){
			var subTasksExpandTrigger	= this.getSubTasksExpandTrigger(idTask);

			if( ! subTasksExpandTrigger.hasClassName('expandable') ) {
				subTasksExpandTrigger.addClassName('expandable');
			}

			if( ! subTasksExpandTrigger.hasClassName('expanded') ) {
				subTasksExpandTrigger.addClassName('expanded');
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
				task:	idTask,
				tab:	tab
			},
			onComplete: this.onDetailsLoaded.bind(this, idTask, tab, onComplete)
		};

			// Fade out the "not acknowledged" icon if its there
		this.fadeAcknowledgeIcon.delay(1, idTask);

		Todoyu.send(url,  options);
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
		var target	= 'task-' + idTask + '-header';

		if(!this.isDetailsLoaded(idTask)) {
			$(target).insert({after: response.responseText});
		}

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