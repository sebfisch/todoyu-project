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
	 */
	init: function() {
		this.statusFilter = new Todoyu.Ext.project.PanelWidget.StatusFilter('panelwidget-projectstatusfilter-list', this.onSelectionChange.bind(this));
	},


	/**
	 * If user clicked on an li instead on the checkbox or the label
	 *
	 *	@param	Event		event
	 */
	onSelectionChange: function(event) {		
		this.onUpdate();
	},



	/**
	 * Handler when PanelWidget is updated
	 */
	onUpdate: function() {
		Todoyu.PanelWidget.inform(this.key, this.statusFilter.getValue());
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