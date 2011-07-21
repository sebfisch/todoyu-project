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
 * @module	Project
 */

Todoyu.Ext.project.Task.Tab = {

	/**
	 * Reference to extension
	 *
	 * @property	ext
	 * @type		Object
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Handle onSelect event of tab: show affected tab which the event occured on
	 *
	 * @method	onSelect
	 * @param	{Event}		event
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	onSelect: function(event, tabKey) {
		var idParts	= event.findElement('li').id.split('-');

		this.show(idParts[1], idParts[3]);
	},



	/**
	 * Show given tab of given task
	 *
	 * @method	show
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	show: function(idTask, tabKey, onComplete) {
		var tabContainer = this.buildTabID(idTask, tabKey);

		if( ! Todoyu.exists(tabContainer) ) {
			this.createTabContainer(idTask, tabKey);
			this.load(idTask, tabKey, onComplete);
		} else {
			this.saveSelection(idTask, tabKey);
			Todoyu.callIfExists(onComplete, this, idTask, tabKey);
		}

		this.activate(idTask, tabKey);
	},



	/**
	 * Load given tab of given task
	 *
	 * @method	load
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	load: function(idTask, tabKey, onComplete) {
		var url 	= Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'tabload',
				task:	idTask,
				tab:	tabKey
			},
			onComplete:	this.onLoaded.bind(this, idTask, tabKey, onComplete)
		};

		var tabDiv	= this.buildTabID(idTask, tabKey);
		Todoyu.Ui.update(tabDiv, url, options);
	},



	/**
	 * Handler when tab is loaded
	 *
	 * @method	onLoaded
	 * @param	{Number}		idTask
	 * @param	{String}		tabKey
	 * @param	{Function}	onComplete callback
	 */
	onLoaded: function(idTask, tabKey, onComplete) {
		this.activate(idTask, tabKey);
		Todoyu.callIfExists(onComplete, this, idTask, tabKey);

		Todoyu.Hook.exec('project.taskTab.onLoaded', idTask);
	},



	/**
	 * Check if a tab of a task is already loaded
	 *
	 * @method	isLoaded
	 * @param	{Number}		idTask
	 * @param	{String}		tabKey
	 */
	isLoaded: function(idTask, tabKey) {
		return Todoyu.exists('task-' + idTask + '-tabcontent-' + tabKey);
	},



	/**
	 * Create tab container to given task.
	 *
	 * @method	creatTabContainer
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	createTabContainer: function(idTask, tabKey) {
		$('task-' + idTask + '-tabcontent').insert({
			'top': new Element(
				'div', {
					'id':		this.buildTabID(idTask, tabKey),
					'class':	'tab'
				}
			)
		});
	},



	/**
	 * Render element ID of given tab of given task
	 *
	 * @method	buildTabID
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 * @return	{String}
	 */
	buildTabID: function(idTask, tabKey) {
		return 'task-' + idTask + '-tabcontent-' + tabKey;
	},



	/**
	 * Activate given tab of given task: hide other tabs, activate tab head, set tab content visible
	 *
	 * @method	activate
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	activate: function(idTask, tabKey) {
		this.hideAll(idTask);
		this.setVisible(idTask, tabKey);
		Todoyu.Tabs.setActive('task-' + idTask, tabKey);
	},



	/**
	 * Save given task's selected (given) tab
	 *
	 * @method	saveSelection
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	saveSelection: function(idTask, tabKey) {
		var url = Todoyu.getUrl('project', 'task');
		var options	= {
			parameters: {
				action:	'tabselected',
				idTask:	idTask,
				tab:	tabKey
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Hide all tabs of given task
	 *
	 * @method	hideAll
	 * @param	{Number}	idTask
	 */
	hideAll: function(idTask) {
		this.getContainer(idTask).select('.tab').invoke('hide');
	},




	/**
	 * Set given tab of given task visible
	 *
	 * @method	setVisible
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	setVisible: function(idTask, tabKey) {
		$(this.buildTabID(idTask, tabKey)).show();
	},



	/**
	 * Get tabs container element of given task
	 *
	 * @method	getContainer
	 * @param	{Number}	idTask
	 * @return	{Element}
	 */
	getContainer: function(idTask) {
		return $('task-' + idTask + '-tabcontainer');
	},



	/**
	 * Get tab head ID of given tab of given task
	 *
	 * @method	getHeadID
	 * @param	{Number}	idTask
	 * @param	{String}	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 * @return	{String}
	 */
	getHeadID: function(idTask, tabKey) {
		return 'task-' + idTask + '-tabhead-' + tabKey;
	},



	/**
	 * Extract tabKey (e.g 'timetracking' / 'comment' / 'assets') out of item ID
	 *
	 * @param	{Number}	idItem
	 * @return	{String}
	 */
	getKeyFromID: function(idItem) {
		return idItem.split('-').last();
	}

};