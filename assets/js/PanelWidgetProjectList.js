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

Todoyu.Ext.project.PanelWidget.ProjectList = {

	ext: Todoyu.Ext.project,

	fulltextTimeout: null,

	filters: {},



	/**
	 * Initialize panelwidget
	 *
	 * @param	Object		filters		Filter hash. Because of JSON, an (empty) array means no data
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
	 * Install fulltext observer
	 */
	observeFulltext: function() {
		$('panelwidget-projectlist-field-fulltext').observe('keyup', this.onFulltextKeyup.bindAsEventListener(this));
	},



	/**
	 * Install project observer
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
	 * Keyup event handler for fulltext
	 *
	 * @param	Object		event
	 */
	onFulltextKeyup: function(event) {
		this.clearTimeout();
		this.applyFilter('fulltext', this.getFulltext());

		this.startTimeout();
	},



	/**
	 * Click event handler for project
	 *
	 * @param	Object		event
	 */
	onProjectClick: function(event) {
		var idProject = event.findElement('li').id.split('-').last();

		this.ext.ProjectTaskTree.openProject(idProject);
	},



	/**
	 * Update handler for status filter
	 *
	 * @param	String	widgetkey
	 * @param	Array	statuses
	 */
	onStatusFilterUpdate: function(widgetkey, statuses) {
		this.applyFilter('status', statuses, true);
	},



	/**
	 * Clear (fulltext) timeout
	 */
	clearTimeout: function() {
		clearTimeout(this.fulltextTimeout);
	},



	/**
	 * Install fulltext timeout
	 */
	startTimeout: function() {
		this.fulltextTimeout = this.update.bind(this).delay(0.3);
	},



	/**
	 * Get fulltext input field value
	 */
	getFulltext: function() {
		return $F('panelwidget-projectlist-field-fulltext');
	},



	applyFilter: function(name, value, update) {
		this.filters[name] = value;

		if( update === true ) {
			this.clearTimeout();
			this.update();
		}
	},



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



	onUpdated: function(response) {
		this.observeProjects();
	},



	isProjectListed: function(idProject) {
		return Todoyu.exists('panelwidget-projectlist-project-' + idProject);
	},



	onProjectSaved: function(idProject) {
		this.update();
	},

	onProjectCreated: function(idProject) {
		this.update();
	},

	onProjectDeleted: function(idProject) {
		this.update();
	}

};