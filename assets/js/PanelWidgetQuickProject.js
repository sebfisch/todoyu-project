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

Todoyu.Ext.project.PanelWidget.QuickProject = {

	ext: Todoyu.Ext.project,



	/**
	 * Add new quick project
	 */
	addProject: function() {
		this.ext.Project.add();
	},



	/**
	 * Add quicktask
	 */
	addTask: function() {
		Todoyu.Ext.project.QuickTask.openPopup(this.onTaskAdded.bind(this));
	},



	/**
	 * Handle task being added
	 * 
	 *	@param	Integer	idTask
	 * 	@param	Integer	idProject
	 * 	@param	Boolean	started
	 */	
	onTaskAdded: function(idTask, idProject, started) {
		
	}
	
};