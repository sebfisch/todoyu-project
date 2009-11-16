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
Todoyu.Ext.project.PanelWidget.StatusFilter = {

	/**
	 * Reference to extension js
	 */
	ext: Todoyu.Ext.project,

	/**
	 * PanelWidget ID
	 */
	key: 'statusfilter',

	/**
	 * Initialization of the PanelWidget
	 */
	init: function() {
		this.installObserver();
	},

	/**
	 * Install event observer for the PanelWidget
	 */
	installObserver: function() {
		this.getForm().observe('click', this.onClick.bindAsEventListener(this));
	},



	/**
	 * Get the PanelWidget form
	 */
	getForm: function() {
		return $('panelwidget-' + this.key + '-form');
	},



	/**
	 * If user clicked on an li instead on the checkbox or the label
	 *
	 * @param	Event		event
	 */
	onClick: function(event) {
			// Ignore label click events, because this there will also be a click event for the input
		if( event.element().tagName === 'LABEL' ) {
			return false;
		}

		var outOfLabel = event.findElement('label') === undefined;
		var notCheckbox= event.findElement('input') === undefined;

		if( outOfLabel && notCheckbox ) {
			var input		= event.findElement('li').select('input')[0];
			input.checked	= !input.checked;
		}

		this.onUpdate();
	},



	/**
	 * Handler when PanelWidget is updated
	 */
	onUpdate: function() {
		Todoyu.PanelWidget.inform(this.key, this.getValue());
		this.savePreference();
	},



	/**
	 * Get form value of the PanelWidget (selected statuses)
	 *
	 * @return	Array
	 */
	getValue: function() {
		var statuses = this.getForm().serialize(true)['status[]'];

		if( typeof(statuses) === 'string' ) {
			statuses = [statuses];
		}

		if( typeof(statuses) === 'undefined' ) {
			statuses = [];
		}

		return statuses;
	},



	/**
	 * Get selected statuses
	 *
	 * @return Array
	 */
	getSelectedStatuses: function() {
		return this.getValue();
	},



	/**
	 * Get the number of selected statuses
	 */
	getNumSelected: function() {
		return this.getValue().length;
	},



	/**
	 *	Check if any status' checkbox is checked
	 *
	 *	@return	Boolean
	 */
	isAnyStatusSelected: function() {
		return this.getSelectedStatuses().length > 0;
	},



	/**
	 * Save the current selected statuses as preference
	 */
	savePreference: function() {
		var pref	= this.getSelectedStatuses().join(',');
		var action		= 'panelwidget' + this.key;

		Todoyu.Pref.save('project', action, pref);
	}
};