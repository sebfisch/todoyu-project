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
	 * @param	{Integer}	idTask
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
	 * @param	{Integer}	idTask
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
	 * @param	{Integer}	idTask
	 * @param	{Object}	response
	 */
	onFormLoaded: function(idTask, response) {
		this.ext.Task.scrollTo(idTask);

		Todoyu.Hook.exec('onTaskEdit', idTask);
	},



	/**
	 * Save edited task
	 *
	 * @param	unknown_type	form
	 */
	save: function(form) {
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action':	'save',
				'area':		Todoyu.getArea()
			},
			onComplete: this.onSaved.bind(this)
		});
	},



	/**
	 * Evoked after edited task having been saved. Handles display of success / failure message and refresh of saved task / failed form.
	 *
	 * @param	{Object}	response
	 */
	onSaved: function(response) {
		var idTask		= response.getTodoyuHeader('idTask');
		var idTaskOld	= response.getTodoyuHeader('idTaskOld');

			// Save resulted in error?
		if( response.hasTodoyuError() ) {
				// Update task edit form with form remarks, display failure notification
			this.updateFormDiv(idTask, response.responseText);
			Todoyu.notifyError('[LLL:task.save.error]');

				// Saving went ok?
		} else {
				// Update displayed task data, re-add context menu, notify of saving success
			this.ext.Task.update(idTaskOld, response.responseText);
			this.ext.Task.addContextMenu(idTask);

			Todoyu.Hook.exec('onTaskSaved', idTask);
			Todoyu.notifySuccess('[LLL:task.save.success]');

				// Scroll to task and highlight it
			this.ext.Task.scrollTo(idTask);
			this.ext.Task.highlight.bind(this.ext.Task).delay(0.5, idTask);
		}
	},



	/**
	 * Update editing form of given task with given HTML
	 *
	 * @param	{Integer}	idTask
	 * @param	{String}	formHTML
	 */
	updateFormDiv: function(idTask, formHTML) {
		$('task-' + idTask + '-formdiv').update(formHTML);
	},



	/**
	 * Cancel editing of given task. Refresh task's parent sub tasks expand trigger, refresh the task.
	 *
	 * @param	{Integer}	idTask
	 */
	cancel: function(idTask) {
		if( this.ext.Task.hasParentTask(idTask) && idTask == 0) {
			this.ext.Task.checkAndRemoveTriggerFromParent(idTask);
		}
		
		if( idTask == 0 ) {
			$('task-' + idTask).remove();
		} else {
			this.ext.Task.refresh(idTask);
		}
	},



	/**
	 * Handler when parenttask field is autocompleted
	 *
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onParenttaskAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') == 0 ) {
			Todoyu.notifyInfo('[LLL:task.ac.parenttask.notFoundInfo]');
		}
	},



	/**
	 * Handler when project field is autocompleted
	 *
	 * @param	{Ajax.Response}			response
	 * @param	{Todoyu.Autocompleter}	autocompleter
	 */
	onProjectAutocomplete: function(response, autocompleter) {
		if( response.getTodoyuHeader('acElements') == 0 ) {
			Todoyu.notifyInfo('[LLL:task.ac.project.notFoundInfo]');
		}
	}

};
