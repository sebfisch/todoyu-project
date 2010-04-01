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

Todoyu.Ext.project.QuickTask = {

	/**
	 *	Ext shortcut
	 */
	ext:			Todoyu.Ext.project,

	fieldStart:		'quicktask-0-field-start-tracking',

	fieldDone:		'quicktask-0-field-task-done',

	popupID:		'quicktask',

	savedCallback:	null,



	/**
	 * Toggle quick task popup
	 * 
	 *	@param	Callback	callback
	 */
	openPopup: function(callback) {
		var url		= Todoyu.getUrl('project', 'quicktask');
		var options	= {
			'parameters': {
				'action':	'popup'
			}
		};
		this.savedCallback = callback;

		Todoyu.Popup.openWindow(this.popupID, '[LLL:headlet-quicktask.title]', 520, url, options);
	},



	/**
	 * Close quicktask wizard popup
	 */	
	closePopup: function() {
		Todoyu.Popup.close(this.popupID);
	},	



	/**
	 * Save (quick-) task
	 *
	 *	@param	String	form
	 */
	save: function(form)	{
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onSaved.bind(this, form)
		});

		return false;
	},



	/**
	 * Evoked upon completion of saving a quicktask
	 * 
	 * 	@param	String	form
	 * 	@param	Object	response
	 */
	onSaved: function(form, response) {
		if( response.hasTodoyuError() ) {
			$(form).replace(response.responseText);
		} else {
			var idTask		= response.getTodoyuHeader('idTask');
			var idProject	= response.getTodoyuHeader('idProject');
			var start		= response.getTodoyuHeader('start') == 1;

			this.closePopup();
			Todoyu.notifySuccess('[LLL:project.js.quicktask.saved]');

			if( start ) {
				idTask= response.getTodoyuHeader('idTask');
				Todoyu.Ext.timetracking.Task.start(idTask);
			}

			if( this.savedCallback !== null ) {
				this.savedCallback(idTask, idProject, start);
				this.savedCallback = null;
			}
		}
	},


	
	/**
	 * Prevent field 'done' being checked and 'start timetracking' being selected together at time of creation of new quicktask
	 * 
	 * 	@param	String	key
	 * 	@param	String	field
	 */
	preventStartDone: function(key, field) {
		if( key === 'start' ) {
			if( $(this.fieldDone).checked ) {
				$(this.fieldDone).checked = false;
			}
		}
		if( key === 'done' ) {
			if( $(this.fieldStart).checked ) {
				$(this.fieldStart).checked = false;
			}
		}
	},



	/**
	 *	Disable checkbox 'task done'
	 *
	 *	@param	Element	input
	 */
	disableCheckboxTaskDone: function(input)	{
		$(this.fieldDone).disabled = input.getValue() == 1;
	},



	/**
	 *	Disable checkbox 'start workload'
	 *
	 *	@param	Element	input
	 */
	disableCheckboxStartWorkload: function(input)	{
		$(this.fieldStart).disabled = input.getValue() == 1;
	}

};