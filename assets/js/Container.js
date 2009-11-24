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

Todoyu.Ext.project.Container = {

	ext: Todoyu.Ext.project,

	edit: function(idContainer) {
		this.ext.Task.edit(idContainer);
	},



	/**
	 *	Clone container
	 *
	 *	@param	Integer	idContainer
	 */
	clone: function(idContainer) {
		var cloneSubElements = confirm('[LLL:task.cloneSubtasks.confirm]');

		var url		= Todoyu.getUrl('project', 'task');
		var options	= {
			'parameters': {
				'action':			'cloneContainer',
				'container':		idContainer,
				'cloneSubElements':	(cloneSubElements ? 1 : 0)
			},
			'onComplete': this.onCloned.bind(this)
		};
		var target	= 'task-' + idContainer;

		Todoyu.Ui.append(target, url, options);
	},



	/**
	 *	Event handler 'onCloned'
	 *
	 *	@param	unknown	response
	 */
	onCloned: function(response) {
			// Get task id from header
		var idContainer = response.getHeader('Todoyu-idContainer');
			// Attach context menu
		this.ext.Task.addContextMenu(idContainer);
	},



	/**
	 *	Remove given container
	 *
	 *	@param	Integer	idContainer
	 */
	remove: function(idContainer) {
		this.ext.Task.remove(idContainer);
	},



	/**
	 *	Add sub task to container
	 *
	 *	@param	Integer	idContainer
	 */
	addSubTask: function(idContainer) {
		this.ext.Task.addSubTask(idContainer);
	},



	/**
	 *	Add sub container to given container
	 *
	 *	@param	Integer	idContainer
	 */
	addSubContainer: function(idContainer) {
		this.ext.Task.addSubContainer(idContainer);
	},



	/**
	 *	Update status of given container
	 *
	 *	@param	Integer	idContainer
	 *	@param	String	idStatus
	 */
	updateStatus: function(idContainer, idStatus) {
		this.ext.Task.updateStatus(idContainer, idStatus);
	}


};