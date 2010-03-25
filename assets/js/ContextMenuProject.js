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

/**
 * Context menu for tasks
 *
*/

Todoyu.Ext.project.ContextMenuProject = {


	/**
	 *	Attach project context menu
	 */
	attach: function() {
		Todoyu.ContextMenu.attachMenuToClass('contextmenuproject', this.load.bind(this));
	},



	/**
	 *	Detach project context menu
	 */
	detach: function() {
		Todoyu.ContextMenu.detachAllMenus('contextmenuproject');
	},



	/**
	 *	Reattach (refresh by removing and reattaching) project context menu
	 */
	reattach: function() {
		this.detach();
		this.attach();
	},



	/**
	 *	Load project context menu
	 *
	 *	@param	Object	event
	 */
	load: function(event) {
		var idProject	= event.findElement('.contextmenuproject').id.split('-')[1];

			// Prepare request parameters
		var url		= Todoyu.getUrl('project', 'contextmenu');
		var options	= {
			'parameters': {
				'action':	'project',
				'project':	idProject
			}
		};

		Todoyu.ContextMenu.showMenu(url, options, event);

		return false;
	},



	/**
	 *	Attach project context menu to given element
	 *
	 *	@param	String	element
	 */
	attachToElement: function(element) {
		Todoyu.ContextMenu.attachMenuToElement($(element), this.load.bind(this));
	}
};