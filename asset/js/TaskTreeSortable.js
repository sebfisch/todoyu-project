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
 * Sortable task tree with drag and drop
 * This script was inspired by the sortable tree script from svenfuchs
 *
 * @see	https://github.com/svenfuchs/scriptaculous-sortabletree
 *
 * @module	Project
 * @class	Sortable
 * @namespace	Todoyu.Ext.project.TaskTree
 */
Todoyu.Ext.project.TaskTree.Sortable = Class.create({
	/**
	 * Tree container element
	 */
	element: null,
	/**
	 * Root node
	 */
	root: null,
	/**
	 * Tree options
	 */
	options: {},
	/**
	 * Ext back ref
	 */
	ext: Todoyu.Ext.project,

	/**
	 * Enable debugging
	 */
	debug: false,

	/**
	 * Marker arrow element
	 */
	marker: null,

	/**
	 * Top offset of task tree container
	 */
	topOffset: 0,
	


	/**
	 * Initialize sortable tree
	 *
	 * @param	{String|Element}	container
	 * @param	{Object}			options
	 */
	initialize: function(container, options) {
		this.options = options || {};
		this.options.droppable = this.options.droppable || {};
		this.options.draggable = this.options.draggable || {};

		this.element= $(container);
		this.root	= new this.ext.TaskTree.SortableNode(this, null, this.element, this.options);

		if( this.options.auto !== false ) {
			this.makeSortable();
		}

			// Set top offset of container
		this.topOffset = this.element.cumulativeOffset().top;

		this.createMarker();
	},

	

	/**
	 * Create marker element and it to the DOM
	 */
	createMarker: function() {
		this.marker = new Element('div',{
			id:	'dragDropMarker'
		}).setStyle({
			display: 'none'
		}).insert(
			new Element('div')
		);
		$(document.body).insert(this.marker);
	},



	/**
	 * Get marker element
	 *
	 * @return	{Element}
	 */
	getMarker: function() {
		return this.marker;
	},



	/**
	 * Show marker
	 */
	showMarker: function() {
		this.getMarker().show();
	},



	/**
	 * Hide marker
	 *
	 * Also remove inside class which may be added
	 * and move the marker back to the body element to prevent any removed by task refresh action
	 */
	hideMarker: function() {
		this.getMarker().hide();
		this.removeMarkerClasses();
		$(document.body).insert(this.getMarker());
	},

	removeMarkerClasses: function() {
		this.getMarker().removeClassName('in').removeClassName('before').removeClassName('after').removeClassName('outside');
	},

	

	/**
	 * Reload tree
	 * Detects new added elements and adds drop and drop behaviour
	 */
	reload: function() {
		this.root.destroy();
		this.root = new this.ext.TaskTree.SortableNode(this, null, this.element, this.options);

		this.makeSortable();
	},



	/**
	 * Make tree sortable
	 */
	makeSortable: function() {
		this.root.makeSortable();
	},



	/**
	 * Disable tree sorting
	 */
	disableSortable: function() {
		this.root.disableSortable();
	},



	/**
	 * Find a node by DOM element
	 *
	 * @param	{Element}	element
	 */
	findNode: function(element) {
		return this.root.findNode(element);
	},



	/**
	 * Call change handler
	 *
	 * @param	{Number}	idTaskDrag
	 * @param	{Number}	idTaskDrop
	 * @param	{String}	position
	 */
	onChange: function(idTaskDrag, idTaskDrop, position) {
		this.log('Save: Dragged ' + idTaskDrag + ' ' + position + ' ' + idTaskDrop);
		if( this.options.onChange ) {
			this.options.onChange(idTaskDrag, idTaskDrop, position);
		}
	},



	/**
	 * Log message
	 *
	 * @param	{String}	message
	 * @param	{Object}	item
	 */
	log: function(message, item) {
		if( this.debug ) {
			console.info(message);

			if(item) {
				console.log(item);
			}
		}
	},



	/**
	 * Get the debug tree object
	 * This is a simplified tree with useful debug info
	 */
	getDebugTree: function() {
		return this.root.getDebugTree();
	}
});