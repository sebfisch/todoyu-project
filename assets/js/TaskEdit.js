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

Todoyu.Ext.project.Task.Edit = {

	/**
 	 *	Ext shortcut
	 *
	 * @var	{Object}	ext
 	 */
	ext:	Todoyu.Ext.project,

	/**
	 * Create DIVs (details, data) wrapping form (inside header) of given task and have task details be displayed. Div positions: Data before, details after header of given task.
	 *
	 * @method	createFormWrapDivs
	 * @param	{Number}	idTask
	 */
	createFormWrapDivs: function(idTask) {
		var idHeader	= 'task-' + idTask + '-header';
		var idDetails	= 'task-' + idTask + '-details';
		var idData		= 'task-' + idTask + '-data';

		if( ! Todoyu.exists(idDetails) ) {
				// Create details div
			var details = new Element('div', {
				'id':		idDetails,
				'class':	'details edit'
			});

				// Create data div
			var data = new Element('div', {
				'id':		idData,
				'class':	'data'
			});

			details.insert({
				'top':	data
			});

			$(idHeader).insert({
				'after':	details
			});
		}

		$(idDetails).show();
	},



	/**
	 * Load task editing form.
	 *
	 * @method	loadForm
	 * @param	{Number}	idTask
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
	 * Scrolls to given task, calls onTaskEdit hook.
	 * Evoked after task editing form having been loaded.
	 *
	 * @method	onFormLoaded
	 * @param	{Number}			idTask
	 * @param	{Ajax.Response}		response
	 */
	onFormLoaded: function(idTask, response) {
		this.ext.Task.scrollTo(idTask);
		this.ext.Task.setExpandedStyle(idTask, true);

		Todoyu.Hook.exec('project.task.formLoaded', idTask);
	},



	/**
	 * Save edited task
	 *
	 * @method	save
	 * @param	{Element}	form
	 */
	save: function(form) {
		Todoyu.Ui.saveRTE();

		$(form).request({
			'parameters': {
				'action':	'save',
				'area':		Todoyu.getArea()
			},
			onComplete: this.onSaved.bind(this, form)
		});
	},



	/**
	 * Evoked after edited task having been saved. Handles display of success / failure message and refresh of saved task / failed form.
	 *
	 * @method	onSaved
	 * @param	{Ajax.Response}		response
	 */
	onSaved: function(form, response) {
		var idTask		= response.getTodoyuHeader('idTask');
		var idTaskOld	= response.getTodoyuHeader('idTaskOld');

		Todoyu.Ui.closeRTE(form);

			// Save resulted in error?
		if( response.hasTodoyuError() ) {
				// Update task edit form with form remarks, display failure notification
			this.updateFormDiv(idTask, response.responseText);
			Todoyu.notifyError('[LLL:project.task.save.error]');

				// Saving went ok?
		} else {
				// Update displayed task data, re-add context menu, notify of saving success
			this.ext.Task.update(idTaskOld, response.responseText);
			this.ext.Task.addContextMenu(idTask);

			Todoyu.Hook.exec('project.task.saved', idTask);
			Todoyu.notifySuccess('[LLL:project.task.save.success]');

				// Scroll to task and highlight it
			this.ext.Task.scrollTo(idTask);
			this.ext.Task.highlight.bind(this.ext.Task).delay(0.5, idTask);
		}
	},



	/**
	 * Update editing form of given task with given HTML
	 *
	 * @method	updateFormDiv
	 * @param	{Number}	idTask
	 * @param	{String}	formHTML
	 */
	updateFormDiv: function(idTask, formHTML) {
		$('task-' + idTask + '-formdiv').update(formHTML);
	},



	/**
	 * Cancel editing of given task. Refresh task's parent sub tasks expand trigger, refresh the task.
	 *
	 * @method	cancel
	 * @param	{Number}	idTask
	 */
	cancel: function(idTask) {
		if( this.ext.Task.hasParentTask(idTask) && idTask == 0) {
			this.ext.Task.checkAndRemoveTriggerFromParent(idTask);
		}

		Todoyu.Ui.closeRTE('task-' + idTask + '-form');

		if( idTask == 0 ) {
			$('task-' + idTask).remove();
		} else {
			this.ext.Task.refresh(idTask);
		}
	},



	/**
	 * Handler when parenttask field is autocompleted
	 *
	 * @method	onParenttaskAutocomplete
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onParenttaskAutocomplete: function(response, autocompleter) {
		if( response.isEmptyAcResult() ) {
			Todoyu.notifyInfo('[LLL:project.task.ac.parenttask.notFoundInfo]');
			return false;
		}
	},



	/**
	 * Project suggestion selected: update form (fields presets)
	 *
	 * @method	onPersonAcSelected
	 * @param		{Element}				inputField
	 * @param		{Element}				idField
	 * @param		{String}				idProject
	 * @param		{String}				selectedText
	 * @param		{Todoyu.Autocompleter}	autocompleter
	 */
	onProjectAcSelected: function(inputField, idField, idProject, selectedText, autocompleter) {
		var isQuicktask	= inputField.up('div').id.indexOf('quicktask') != -1;

			// Update quicktask / regular quickcreate task popup form
		var area	= (isQuicktask ? 'quicktask' : 'task') + '-0-form';
		Todoyu.Ui.closeRTE(area);

		this.updateCreateTaskForm.bind(this).defer(isQuicktask, idProject);
	},



	/**
	 * Event handler when project has been selected in select (dropdown) of quicktask / task creation popup form
	 *
	 * @method	onProjectSelectSelected
	 * @param inputField
	 */
	onProjectSelectSelected: function(inputField) {
		this.onProjectAcSelected(inputField, inputField.id, $F(inputField), '', '');
	},



	/**
	 * Update quicktask / quickcreate task form
	 *
	 * @method	updateCreateTaskForm
	 * @param	{Boolean}	isQuicktask
	 * @param	{Number}	idProject
	 */
	updateCreateTaskForm: function(isQuicktask, idProject) {
		if( isQuicktask ) {
			Todoyu.Ext.project.QuickTask.updateForm(idProject);
		} else {
			Todoyu.Ext.project.QuickCreateTask.updateForm(idProject);
		}
	}

};
