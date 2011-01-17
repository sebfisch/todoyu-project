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

Todoyu.Ext.project.QuickTask = {

	/**
	 * Ext shortcut
	 *
	 * @var	{Object}	ext
	 */
	ext:			Todoyu.Ext.project,

	popupID:		'quicktask',



	/**
	 * Toggle quick task popUp
	 *
	 * @todo	check - used? to be removed?!
	 */
	openPopup: function() {
		var url		= Todoyu.getUrl('project', 'quicktask');
		var options	= {
			'parameters': {
				'action':	'popup'
			},
			'onComplete':	this.onPopupLoaded.bind(this)
		};

		Todoyu.Popup.openWindow(this.popupID, '[LLL:headlet-quicktask.title]', 520, url, options);
	},



	/**
	 * Close quicktask wizard popUp
	 */
	closePopup: function() {
		Todoyu.Popup.close(this.popupID);
	},



	/**
	 * Handler when PopUp is loaded
	 * Call hook to inform other extensions
	 * 
	 * @param	{Ajax.Response}		response
	 */
	onPopupLoaded: function(response) {
		Todoyu.Hook.exec('project.quickTask.formLoaded', response);
	},



	/**
	 * Save (quick-) task
	 *
	 * @param	{String}	form
	 */
	save: function(form) {
		Todoyu.Ui.closeRTE(form);

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
	 * @param	{String}			form
	 * @param	{Ajax.Response}		response
	 */
	onSaved: function(form, response) {
		if( response.hasTodoyuError() ) {
			$(form).replace(response.responseText);
			Todoyu.Hook.exec('project.quickTask.formLoaded', response);
		} else {
			var idTask		= response.getTodoyuHeader('idTask');
			var idProject	= response.getTodoyuHeader('idProject');

			this.closePopup();
			Todoyu.notifySuccess('[LLL:project.js.quicktask.saved]');

				// Call hook with saved data
			Todoyu.Hook.exec('project.quickTask.saved', idTask, idProject, response);
		}
	}

};