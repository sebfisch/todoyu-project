/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * @module	Project
 */

/**
 * Quicktask
 *
 * @class		Quicktask
 * @namespace	Todoyu.Ext.project
 */
Todoyu.Ext.project.QuickTask = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:			Todoyu.Ext.project,

	/**
	 * @property	popupID
	 * @type		String
	 */
	popupID:		'quicktask',



	/**
	 * Toggle quick task popUp
	 *
	 * @method	openPopup
	 * @todo	check - used? to be removed?!
	 */
	openPopup: function() {
		var url		= Todoyu.getUrl('project', 'quicktask');
		var options	= {
			parameters: {
				action:	'popup'
			},
			onComplete:	this.onPopupLoaded.bind(this)
		};

		Todoyu.Popups.open(this.popupID, '[LLL:project.headlet-quicktask.title]', 520, url, options);
	},



	/**
	 * Close quicktask wizard popUp
	 *
	 * @method	closePopup
	 */
	closePopup: function() {
		Todoyu.Hook.exec('project.quickTask.closePopup');
		Todoyu.Popups.close(this.popupID);
	},



	/**
	 * Handler when PopUp is loaded
	 * Call hook to inform other extensions
	 *
	 * @method	onPopupLoaded
	 * @param	{Ajax.Response}		response
	 */
	onPopupLoaded: function(response) {
		Todoyu.Hook.exec('project.quickTask.formLoaded', response);
	},



	/**
	 * Update quicktask form with given project preselected
	 *
	 * @method	updateForm
	 * @param	{Number}	idProject
	 */
	updateForm: function(idProject) {
		var url		= Todoyu.getUrl('project', 'quicktask');
		var options	= {
			parameters: {
				action:		'popup',
				project:	idProject
			},
			onComplete:	this.onPopupLoaded.bind(this)
		};

		Todoyu.Ui.update('quicktask_content', url, options);
	},



	/**
	 * Save (quick-) task
	 *
	 * @method	save
	 * @param	{String}	form
	 */
	save: function(form) {
		Todoyu.Ui.closeRTE(form);

		$(form).request({
			parameters: {
				action:	'save',
				area:	Todoyu.getArea()
			},
			onComplete: this.onSaved.bind(this, form)
		});

		return false;
	},



	/**
	 * Evoked upon completion of saving a quicktask
	 *
	 * @method	onSaved
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
			Todoyu.notifySuccess('[LLL:project.ext.js.quicktask.saved]');

				// Call hook with saved data
			Todoyu.Hook.exec('project.quickTask.saved', idTask, idProject, response);
		}
	}

};