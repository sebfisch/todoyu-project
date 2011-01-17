/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
Todoyu.Ext.project.PanelWidget.ProjectStatusFilter = Class.create(Todoyu.PanelWidgetStatusSelector, {

	/**
	 * Reference to extension namespace
	 *
	 * @var	{Object}	ext
	 */
	ext:	Todoyu.Ext.project,


	/**
	 * PanelWidget ID
	 */
	key:	'projectstatusfilter',



	/**
	 * Initialize the panelWidget: setup properties, install element observers
	 *
	 * @param	{Function}	super				parent constructor
	 * @param	{Array}		selectedStatusIDs	selected status IDs
	 */
	initialize: function($super, selectedStatusIDs) {
		$super('panelwidget-projectstatusfilter-list');

			// Inject the current filter status into the project list widget
		this.ext.PanelWidget.ProjectList.applyFilter('status', selectedStatusIDs.join(','), false);
	},


	/**
	 * Handler when selection is changed
	 *
	 * @param	{Event}		event
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