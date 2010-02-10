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

Todoyu.Ext.project.Task.Tab = {

	/**
 	 *	Ext shortcut
 	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Show given tab of given task
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	show: function(idTask, tabKey, onComplete) {
		var tabID = this.buildTabID(idTask, tabKey);

		if( ! Todoyu.exists(tabID) ) {
			this.createTabContainer(idTask, tabKey);
			this.load(idTask, tabKey, onComplete);
		} else {
			this.saveSelection(idTask, tabKey);
			Todoyu.callIfExists(onComplete, idTask, tabKey);
		}

		this.activate(idTask, tabKey);
	},



	/**
	 * Load given tab of given task
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	load: function(idTask, tabKey, onComplete) {
		var url 	= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'tabload',
				'task':		idTask,
				'tab':		tabKey
			},
			'onComplete':	this.onLoaded.bind(this, idTask, tabKey, onComplete)
		};

		var tabDiv	= this.buildTabID(idTask, tabKey);
		Todoyu.Ui.update(tabDiv, url, options);
	},


	/**
	 * Handler when tab is loaded
	 * 
	 * @param	Integer		idTask
	 * @param	String		tabKey
	 * @param	Function	onComplete callback
	 */
	onLoaded: function(idTask, tabKey, onComplete) {
		this.activate(idTask, tabKey);
		Todoyu.callIfExists(onComplete, idTask, tabKey);
	},
	
	
	
	/**
	 * Check if a tab of a task is already loaded
	 * 
	 * @param	Integer		idTask
	 * @param	String		tabKey
	 */
	isLoaded: function(idTask, tabKey) {
		return Todoyu.exists('task-' + idTask + '-tabcontent-' + tabKey);
	},



	/**
	 * Create tab container to given task.
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	createTabContainer: function(idTask, tabKey) {
			// Create elements
		var loader	= new Element('img', {'src':'assets/img/ajax-loader.png'});
		var spacer	= new Element('p', {'style':'padding:50px;text-align:center'}).update(loader);

		var tabDiv	= new Element(
			'div', {
				'id':		this.buildTabID(idTask, tabKey),
				'class':	'tab'
			}
		).update(spacer);

		var tabContainer = 'task-' + idTask + '-tabcontent';
		$(tabContainer).insert({'top': tabDiv});
	},



	/**
	 * Render element ID of given tab of given task
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 * @return	String
	 */
	buildTabID: function(idTask, tabKey) {
		return 'task-' + idTask + '-tabcontent-' + tabKey;
	},



	/**
	 * Activate given tab of given task: hide other tabs, activate tab head, set tab content visible
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	activate: function(idTask, tabKey) {
		this.hideAll(idTask);
		this.setActiveHead(idTask, tabKey);
		this.setVisible(idTask, tabKey);
	},



	/**
	 * Save given task's selected (given) tab
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	saveSelection: function(idTask, tabKey) {
		var url = Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':	'tabselected',
				'idTask':	idTask,
				'tab':		tabKey
			}
		};

		Todoyu.send(url, options);
	},



	/**
	 * Hide all tabs of given task
	 *
	 * @param	Integer	idTask
	 */
	hideAll: function(idTask) {
		var tabDiv	= this.getContainer(idTask);
		var tabs	= tabDiv.select('.tab');

		tabs.invoke('hide');
	},



	/**
	 * Set style of given tab of given task to active, deactivate the other tabs of that task
	 *
	 * @param	Integer	idTask
	 * @param	String	activeTab	(tabKey, e.g 'timetracking' / 'comment' / 'assets')
	 */
	setActiveHead: function(idTask, activeTab) {
		var tabHeadList	= $('task-' + idTask + '-tabheads');
		var tabHeads	= tabHeadList.select('li');

		tabHeads.each(function(tabHead) {
			tabHead.removeClassName('active');
		});

		$(this.getHeadID(idTask, activeTab)).addClassName('active');
	},



	/**
	 * Set given tab of given task visible
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	setVisible: function(idTask, tabKey) {
		$(this.buildTabID(idTask, tabKey)).show();
	},



	/**
	 * Get tabs container element of given task
	 *
	 * @param	Integer	idTask
	 * 	@return	Element
	 */
	getContainer: function(idTask) {
		return $('task-' + idTask + '-tabs');
	},



	/**
	 * Get tab head ID of given tab of given task
	 *
	 * @param	Integer	idTask
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 * 	@return	String
	 */
	getHeadID: function(idTask, tabKey) {
		return 'task-' + idTask + '-tabhead-' + tabKey;
	},



	/**
	 * Extract tabKey (e.g 'timetracking' / 'comment' / 'assets') out of item ID
	 *
	 * @param	Integer	idItem
	 * 	@return	String
	 */
	getKeyFromID: function(idItem) {
		return idItem.split('-').last();
	},



	/**
	 * Handle onSelect event of tab: show affected tab which the event occured on
	 *
	 * @param	Object	event
	 * @param	String	tabKey	(e.g 'timetracking' / 'comment' / 'assets')
	 */
	onSelect: function(event, tabKey) {
		var info = tabKey.split('-');

		this.show(info[1], info[0]);
	}	

};
