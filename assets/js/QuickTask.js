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
	 * Ext shortcut
	 */
	ext:			Todoyu.Ext.project,

	popupID:		'quicktask',


	/**
	 * Toggle quick task popup
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
	 * Close quicktask wizard popup
	 */	
	closePopup: function() {
		Todoyu.Popup.close(this.popupID);
	},	
	
	
	
	/**
	 * Handler when Popup is loaded
	 * Call hook to inform other extensions
	 * 
	 * @param	{Ajax.Response}		response
	 */
	onPopupLoaded: function(response) {
		Todoyu.Hook.exec('QuickTaskOpen', response);
	},



	/**
	 * Save (quick-) task
	 *
	 * @param	{String}	form
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
	 * @param	{String}	form
	 * @param	{Object}	response
	 */
	onSaved: function(form, response) {
		if( response.hasTodoyuError() ) {
			$(form).replace(response.responseText);
		} else {
			var idTask		= response.getTodoyuHeader('idTask');
			var idProject	= response.getTodoyuHeader('idProject');

			this.closePopup();
			Todoyu.notifySuccess('[LLL:project.js.quicktask.saved]');
			
				// Call hook with saved data
			Todoyu.Hook.exec('QuickTaskSaved', idTask, idProject, response);
		}
	}

};