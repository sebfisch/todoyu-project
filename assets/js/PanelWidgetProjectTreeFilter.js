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

Todoyu.Ext.project.PanelWidget.ProjectTree.Filter = {

	ext:	Todoyu.Ext.project,

	key:	'projecttree',

	fieldObservers: {},
	addObserver: null,
	form: 'panelwidget-projecttree-filter-form',
	checkInterval: 1.5,

	ext: Todoyu.Ext.project,

	init: function() {
		 this.installObserver();
		 this.addRemoveIcons();
	},



	/**
	 * Install project tree filter panel widget observer
	 */
	installObserver: function() {
		var elements = $(this.form).getElements();

			// Observe all inputs except the add field
		elements.each(function(element){
			if( element.id.endsWith('-add')) {
				this.addObserver = this.onAddChange.bindAsEventListener(this);
				element.observe('change', this.addObserver);
			} else {
				this.fieldObservers[element.id] = new Form.Element.Observer(element, this.checkInterval, this.onFieldChange.bind(this));
			}
		}.bind(this));
	},



	/**
	 * Add filter condition removal icons to project tree filter panel widget
	 */
	addRemoveIcons: function() {
			// Get form field divs in the display area
		var formFields = $('panelwidget-projecttree-filter-fieldset-display').select('.fField');

			// Add a remove button the each element
		formFields.each(function(formField) {
				// Get input element
			var input	= formField.select('input', 'select', 'textarea').first();
				// Get input id (the 5 first parts)
			var inputID	= input.id.split('-').slice(0,5).join('-')

			formField.insert(new Element('a', {
				'id': 	inputID + '-remove',
				'class':'remove',
				'href': 'javascript:void(0)',
				'onclick': 'Todoyu.Ext.project.PanelWidget.ProjectTree.Filter.remove(\'' + inputID + '\')',
				'title': '[LLL:panelwidget-projecttree.filter.remove]'
			}));
		}.bind(this));
	},



	/**
	 * Uninstall project tree filter panel widget observer
	 */
	uninstallObservers: function() {
		var elements = $(this.form).getElements();

		elements.each(function(element){
			if( element.id.endsWith('-add') ) {
				element.stopObserving('change', this.addObserver);
			} else {
				this.fieldObservers[element.id].stop();
			}
		}.bind(this));
	},



	/**
	 *	Event handler: 'onAddChange'
	 *
	 *	@param	Object event
	 */
	onAddChange: function(event) {
		var selection = $F('panelwidget-projecttree-filter-field-add');

		if( selection !== '0' ) {
			this.uninstallObservers();
			this.update({'add': selection});
		}
	},



	/**
	 * Event handler: 'onFieldChange'
	 *
	 *	@param	String	field
	 *	@param	String	value
	 */
	onFieldChange: function(field, value) {
			// Only update tree if a field with a name (which will be submitted) has changed
		if( field.name ) {
			this.updateTree();
		}
	},



	/**
	 * Update project tree
	 */
	updateTree: function() {
		var values	= this.getFilterValues();

		this.ext.PanelWidget.ProjectTree.update(values);
	},



	/**
	 *	Update project tree filter panel widget
	 *
	 *	@param	Object	option
	 */
	update: function(option) {
		var	url		= Todoyu.getUrl('project', 'panelwidgetprojecttree');
		var options	= {
			'parameters': {
				'cmd': 'updatefilter'
			},
			'onComplete': this.onUpdated.bind(this)
		};

		if( option.add ) {
			options.parameters.action	='add';
			options.parameters.field	= option.add;
		}
		if( option.remove ) {
			options.parameters.action	='remove';
			options.parameters.field	= option.remove;
		}

		var target	= 'panelwidget-projecttree-filter';

		Todoyu.Ui.update(target, url, options);
	},



	/**
	 *	Get filter values
	 *
	 *	@return array
	 */
	getFilterValues: function() {
		var values	= {};
		var elements= this.getForm().getElements();

		elements.each(function(element){
			var name 	= element.id.split('-').last();
			values[name]= element.getValue();
		});

		delete values.add;

		return values;
	},



	/**
	 *	Event handler: 'onUpdated' (evoked prior to update having finished)
	 *
	 *	@param	unknown	response
	 */
	onUpdated: function(response) {
		this.installObserver();
		this.addRemoveIcons();
		this.updateTree();
	},



	/**
	 *	Get project tree filter panel widget form
	 *
	 *	@return	Element
	 */
	getForm: function() {
		return $(this.form);
	},



	/**
	 *	Remove field of given input from project tree filter panel widget
	 *
	 *	@param	String	idInput
	 */
	remove: function(idInput) {
		var formElement = $(idInput).up('.fElement').remove();
		var fieldName	= idInput.split('-').last();

		this.update({'remove': fieldName});
	}

 };