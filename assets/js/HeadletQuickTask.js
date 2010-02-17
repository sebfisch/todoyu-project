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

Todoyu.Headlet.QuickTask = {

	/**
	 *	Ext shortcut
	 */
	ext:	Todoyu.Ext.project,



	/**
	 * Add quicktask
	 */
	add: function() {
		Todoyu.Ext.project.QuickTask.openPopup(this.onTaskAdded.bind(this));
	},


	
	/**
	 * Handler when quicktask has been saved
	 * Update the tasklist, if task has been added to the active project
	 * 
	 * @param	Integer		idTask
	 * @param	Integer		idProject
	 * @param	Bool		started
	 */
	onTaskAdded: function(idTask, idProject, started) {
		if( idProject == this.ext.ProjectTaskTree.getActiveProjectID() ) {
			this.ext.TaskTree.update();
		}
	}
	
};