/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

Todoyu.Ext.project.Container = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Evoke editing of given container (handled via task editing)
	 * 
	 *	@param	Integer	idContainer
	 */
	edit: function(idContainer) {
		this.ext.Task.edit(idContainer);
	},



	/**
	 *	Clone container
	 *
	 *	@param	Integer	idContainer
	 */
	clone: function(idContainer) {
		this.ext.Task.clone(idContainer);
	},

	
	
	/**
	 *	Copy container
	 *
	 *	@param	Integer	idContainer
	 */
	cut: function(idContainer) {
		this.ext.Task.cut(idContainer);
	},
	
	
	
	/**
	 *	Copy container
	 *
	 *	@param	Integer	idContainer
	 */
	copy: function(idContainer) {
		this.ext.Task.copy(idContainer);
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