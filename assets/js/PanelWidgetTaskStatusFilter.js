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

/**
 *	Panel widget: StatusFilter JS
 */
Todoyu.Ext.project.PanelWidget.TaskStatusFilter = Class.create(Todoyu.PanelWidgetStatusSelector, {

	/**
	 * Reference to extension JS
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.project,


	/**
	 * PanelWidget ID
	 */
	key:	'taskstatusfilter',


	/**
	 * Initialize object
	 *
	 * @param	Function		$super		Original constructor function
	 */
	initialize: function($super) {
		$super('panelwidget-taskstatusfilter-list');
	},



	/**
	 * Handler when selection is changed
	 */
	onChange: function(event) {
		this.fireUpdate(this.key);
		this.savePreference();

		return true;
	},



	/**
	 * Save the current selected statuses as preference
	 */
	savePreference: function() {
		var pref	= this.getSelectedStatuses().join(',');
		var action	= 'panelwidget' + this.key;

		Todoyu.Pref.save('project', action, pref);
	}

});