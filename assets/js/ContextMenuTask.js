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

/**
 * Context menu for tasks
 */

Todoyu.Ext.project.ContextMenuTask = {



	/**
	 * Attach task context menu
	 */
	attach: function() {
		Todoyu.ContextMenu.attachMenuToClass('contextmenutask', this.load.bind(this));
	},



	/**
	 * Detach task context menu
	 */
	detach: function() {
		Todoyu.ContextMenu.detachAllMenus('contextmenutask');
	},



	/**
	 * Reattach task context menu
	 */
	reattach: function() {
		this.detach();
		this.attach();
	},



	/**
	 * Load task context menu
	 *
	 * @param	Object	event
	 */
	load: function(event) {
		var h3		= Event.findElement(event, 'h3');
		var idParts	= h3.id.split('-');
		var idTask	= Todoyu.Helper.intval(idParts[1]);

		var url		= Todoyu.getUrl('project', 'contextmenu');
		var options	= {
			'parameters': {
				'action':	'task',
				'task':		idTask
			}
		};

		Todoyu.ContextMenu.showMenu(url, options, event);

		return false;
	},



	/**
	 * Attach task context menu to given element
	 *
	 * @param	String	element
	 */
	attachToElement: function(element) {
		Todoyu.ContextMenu.attachMenuToElement(element, this.load.bind(this));
	}

};