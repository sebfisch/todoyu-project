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
Todoyu.Ext.project.PanelWidget.StatusFilter = Class.create({

	/**
	 * Reference to extension JS
	 */
	ext:	Todoyu.Ext.project,

	list:	null,

	handler: null,



	/**
	 * Initialize the panelWidget: setup properties, install element observers
	 */
	initialize: function(list, handlerFunction) {
		this.list	= $(list);
		this.handler= handlerFunction;
		
		this.installObservers();
	},



	/**
	 * Install (selection change) event observer for the PanelWidget
	 */
	installObservers: function(handlerFunction) {
		this.list.observe('change', this.onSelectionChange.bind(this));
	},



	/**
	 * If clicked on an li instead on the checkbox or the label
	 *
	 * @param	Event		event
	 */
	onSelectionChange: function(event) {
			// If no status is selected, select all, because it will be handled this way anyway
		if( ! this.isAnyStatusSelected() ) {
			this.selectAll();
		}

		this.handler(event);
	},


	/**
	 * Get form value of the PanelWidget (selected statuses)
	 *
	 * @return	Array
	 */
	getValue: function() {
		return this.getSelectedStatuses();
	},



	/**
	 * Get selected statuses
	 *
	 * @return	Array
	 */
	getSelectedStatuses: function() {
		return $F(this.list);
	},



	/**
	 * Get the number of selected statuses
	 *
	 * @return	Integer
	 */
	getNumSelected: function() {
		return this.getValue().length;
	},



	/**
	 * Check if any status' checkbox is checked
	 *
	 * @return	Boolean
	 */
	isAnyStatusSelected: function() {
		return this.getNumSelected() > 0;
	},



	/**
	 * Select all statuses
	 */
	selectAll: function() {
		$(this.list).childElements().each(function(option){
			option.selected = true;
		});
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