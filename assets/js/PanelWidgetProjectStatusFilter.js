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
 *	Panel widget: StatusFilter JS
 *
 */
Todoyu.Ext.project.PanelWidget.ProjectStatusFilter = {

	/**
	 * Reference to extension js
	 */
	ext:	Todoyu.Ext.project,


	/**
	 * PanelWidget ID
	 */
	key:	'projectstatusfilter',



	/**
	 * Initialize the panelWidget: setup properties, install element observers
	 *
	 * @param	Array		Selected Status IDs
	 */
	init: function(selectedStatusIDs) {
		this.statusFilter = new Todoyu.Ext.project.PanelWidget.StatusFilter('panelwidget-projectstatusfilter-list', this.onSelectionChange.bind(this));

			// Inject the current filter status into the project list widget
		this.ext.PanelWidget.ProjectList.applyFilter('status', selectedStatusIDs.join(','), false);
	},


	/**
	 * If clicked on an li instead on the checkbox or the label
	 *
	 * @param	Event		event
	 */
	onSelectionChange: function(event) {
		this.onUpdate();
	},



	/**
	 * Handler when PanelWidget is updated
	 */
	onUpdate: function() {
		Todoyu.PanelWidget.fire(this.key, this.statusFilter.getValue());
		this.savePreference();
	},



	/**
	 * Save the current selected statuses as preference
	 */
	savePreference: function() {
		var pref	= this.statusFilter.getSelectedStatuses().join(',');
		var action	= 'panelwidget' + this.key;

		Todoyu.Pref.save('project', action, pref);
	}

};