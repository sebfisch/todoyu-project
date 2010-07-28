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

Todoyu.Ext.project.PanelWidget.ProjectList = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext: Todoyu.Ext.project,

	fulltextTimeout: null,

	filters: {},



	/**
	 * Initialize panelWidget
	 *
	 * @param	{Object}		filters		Filter hash. Because of JSON, an (empty) array means no data
	 */
	init: function(filters) {
			// If filters are given as parameters, add them to internal storage
		if( typeof(filters) === 'object' && ! Object.isArray(filters) ) {
			$H(filters).each(function(pair){
				this.applyFilter(pair.key, pair.value, false);
			}, this);
		}

		this.observeFulltext();
		this.observeProjects();
		this.observeStatusSelector();

		this.addHooks();
	},



	/**
	 * Add various JS hooks
	 */
	addHooks: function() {
			// Project save
		Todoyu.Hook.add('onProjectSaved', this.onProjectSaved.bind(this));
			// Project create
		Todoyu.Hook.add('onProjectCreated', this.onProjectCreated.bind(this));
			// Add delete
		Todoyu.Hook.add('onProjectDeleted', this.onProjectDeleted.bind(this));
	},



	/**
	 * Install keyup event observer on full-text search input field
	 */
	observeFulltext: function() {
		$('panelwidget-projectlist-field-fulltext').observe('keyup', this.onFulltextKeyup.bindAsEventListener(this));
	},



	/**
	 * Install click event observer on items of projects list 
	 */
	observeProjects: function() {
		$('panelwidget-projectlist-list').observe('click', this.onProjectClick.bindAsEventListener(this));
	},



	/**
	 * Install status selection observer
	 */
	observeStatusSelector: function() {
		Todoyu.PanelWidget.observe('projectstatusfilter', this.onStatusFilterUpdate.bind(this));
	},



	/**
	 * Handler for keyup events of full-text search input field
	 *
	 * @param	{Object}		event
	 */
	onFulltextKeyup: function(event) {
		this.clearTimeout();
		this.applyFilter('fulltext', this.getFulltext());

		this.startTimeout();
	},



	/**
	 * Click event handler for project
	 *
	 * @param	{Object}		event
	 */
	onProjectClick: function(event) {
		var listElement = event.findElement('li');

		if( Object.isElement(listElement) ) {
			var idProject = listElement.id.split('-').last();
			this.ext.ProjectTaskTree.openProject(idProject);
		}
	},



	/**
	 * Update handler for status filter
	 *
	 * @param	{String}	widgetKey
	 * @param	{Array}	statuses
	 */
	onStatusFilterUpdate: function(widgetKey, statuses) {
		this.applyFilter('status', statuses, true);
	},



	/**
	 * Clear (full-text) timeout
	 */
	clearTimeout: function() {
		clearTimeout(this.fulltextTimeout);
	},



	/**
	 * Install full-text timeout
	 */
	startTimeout: function() {
		this.fulltextTimeout = this.update.bind(this).delay(0.3);
	},



	/**
	 * Get full-text input field value
	 */
	getFulltext: function() {
		return $F('panelwidget-projectlist-field-fulltext');
	},



	/**
	 * Apply filter to project list panelwidget
	 *
	 * @param	{String}		name
	 * @param	{String}		value
	 * @param	{Boolean}		update
	 */
	applyFilter: function(name, value, update) {
		this.filters[name] = value;

		if( update === true ) {
			this.clearTimeout();
			this.update();
		}
	},



	/**
	 * Refresh project list panelWidget
	 */
	update: function() {
		var url		= Todoyu.getUrl('project', 'panelwidgetprojectlist');
		var options	= {
			'parameters': {
				'action':	'list',
				'filters':	Object.toJSON(this.filters)
			},
			'onComplete':	this.onUpdated.bind(this)
		};
		var target	= 'panelwidget-projectlist-list';

		Todoyu.Ui.replace(target, url, options);
	},



	/**
	 * Handler to be evoked after refresh of project list panelWidget
	 *
	 * @param	{Object}  response
	 */
	onUpdated: function(response) {
		this.observeProjects();
	},



	/**
	 * Check whether given project is listed in panelWidget's project list
	 *
	 * @param	{Number}		idProject
	 * @return  {Boolean}
	 */
	isProjectListed: function(idProject) {
		return Todoyu.exists('panelwidget-projectlist-project-' + idProject);
	},



	/**
	 * Handler being evoked after saving of projects: updates the project list panel widget
	 *
	 * @param	{Number}		idProject
	 */
	onProjectSaved: function(idProject) {
		this.update();
	},



	/**
	 * Handler being evoked after creation of new projects: updates the project list panel widget
	 * 
	 * @param	{Number}		idProject
	 */
	onProjectCreated: function(idProject) {
		this.update();
	},



	/**
	 * Handler being evoked after deletion projects: updates the project list panel widget
	 *
	 * @param	{Number}		idProject
	 */
	onProjectDeleted: function(idProject) {
		this.update();
	}

};