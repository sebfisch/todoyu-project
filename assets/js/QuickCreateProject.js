/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

Todoyu.Ext.project.QuickCreateProject = {

	/**
	 * Evoked upon opening of event quick create wizard popup
	 */
	onPopupOpened: function() {

	},



	/**
	 *	Save project
	 *
	 *	@param	unknown	form
	 */
	save: function(form){
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action':	'save'
			},
			onComplete: this.onSaved.bind(this)
		});

		return false;
	},



	/**
	 *	onSaved project custom event handler
	 *
	 *	@param	Ajax.Response		response
	 */
	onSaved: function(response){
		var error		= response.hasTodoyuError();

		if( error ) {
			Todoyu.Headlet.QuickCreate.updatePopupContent(response.responseText);
			Todoyu.notifyError('[LLL:project.save.error]');
		} else {
			var idProject	= response.getTodoyuHeader('idProject');
			Todoyu.Hook.exec('onProjectCreated', idProject);
			Todoyu.Hook.exec('onProjectSaved', idProject);

			Todoyu.Headlet.QuickCreate.closePopup();
			Todoyu.notifySuccess('[LLL:project.save.success]');
		}
	}

};