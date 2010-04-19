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

Todoyu.Ext.project.Headlet.QuickTask = {

	/**
	 * Ext shortcut
	 */
	ext:	Todoyu.Ext.project,
	
	
	init: function() {
		Todoyu.Hook.add('QuickTaskSaved', this.onQuickTaskSaved.bind(this));
	},
	
	
	onButtonClick: function(event) {
		this.add();
	},



	/**
	 * Add quicktask
	 */
	add: function() {
		this.ext.QuickTask.openPopup();
	},


	
	/**
	 * Handler when quicktask has been saved
	 * Update the tasklist, if task has been added to the active project
	 * 
	 * @param	{Integer}		idTask
	 * @param	{Integer}		idProject
	 * @param	{Boolean}		started
	 */
	onQuickTaskSaved: function(idTask, idProject, response) {
		if ( Todoyu.getArea() == 'project' ) {
			if( idProject == this.ext.ProjectTaskTree.getActiveProjectID() ) {
				this.ext.TaskTree.update();
			}
		}
	}
	
};