/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

Todoyu.Headlet.QuickCreate.Project = {

	/**
	 * Evoked upon opening of event quick create wizard popup
	 */
	onPopupOpened: function() {

	},



	/**
	 * Save method
	 *
	 * @param	Element		form		Form element
	 */
	save: function(form) {
		$(form).request({
			'parameters': {
				'action':	'save'
			},
			'onComplete': this.onSaved.bind(this)
		});
	},



	/**
	 * If saved, close the creation wizard popup
	 *
	 * @param	Object	response	Response, containing startdate of the event
	 */
	onSaved: function(response) {
		var isError = response.getTodoyuHeader('error') == 1;

		if( response.hasTodoyuError() ) {
			Todoyu.Popup.setContent('quickcreate', response.responseText);
			$('quickcreateproject-form').innerHTML.evalScripts();
		} else {
			Todoyu.Headlet.QuickCreate.closePopup();
		}
	}

};