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

Todoyu.Ext.project.QuickCreateTask = {

	/**
	 * Evoked upon opening of event quick create wizard popup
	 */
	onPopupOpened: function() {
		
	},



	/**
	 * Save task
	 *
	 * @param	String		form
	 */
	save: function(form) {
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action':	'save'
			},
			onComplete: this.onSaved.bind(this)
		});
	},



	/**
	 * Evoked after edited task having been saved. Handles display of success / failure message and refresh of saved task / failed form.
	 *
	 * @param	Object	response
	 */
	onSaved: function(response) {
		var idTask		= response.getTodoyuHeader('idTask');
		var idTaskOld	= response.getTodoyuHeader('idTaskOld');

			// Save resulted in error?
		if( response.hasTodoyuError() ) {
				// Update task edit form with form remarks, display failure notification
			Todoyu.Headlet.QuickCreate.updatePopupContent(response.responseText);
			Todoyu.notifyError('[LLL:task.save.error]');
		} else {
			// Saving went ok
			Todoyu.Hook.exec('onTaskSaved', idTask);

			Todoyu.Popup.close('quickcreate');
			Todoyu.notifySuccess('[LLL:task.save.success]');
		}
	}

};