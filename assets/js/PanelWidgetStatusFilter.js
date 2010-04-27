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
 */
Todoyu.Ext.project.PanelWidget.StatusFilter = Class.create({

	/**
	 * Reference to extension JS
	 *
	 * @var	{Object}	ext
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
	 * @param	{Event}		event
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
	 * @return	{Number}
	 */
	getNumSelected: function() {
		return this.getValue().length;
	},



	/**
	 * Check if any status' checkbox is checked
	 *
	 * @return	{Boolean}
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