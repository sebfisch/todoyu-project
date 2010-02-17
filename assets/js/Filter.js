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

Todoyu.Ext.project.Filter = {
	
	ext: Todoyu.Ext.project,


	
	onProjectSearchResultsUpdated: function() {
		this.ext.ContextMenuProject.attach();
	},



	onTaskSearchResultsUpdated: function() {
		this.ext.ContextMenuTask.attach();
	},



	onProjectroleUserAcSelect: function(name, textInput, listElement) {
		$('widget-autocompleter-' + name + '-hidden').value = listElement.id;
		
		Todoyu.Ext.project.Filter.updateProjectRoleConditionValue(name);
	},



	onProjectroleRoleChange: function(name) {
		Todoyu.Ext.project.Filter.updateProjectRoleConditionValue(name);
	},



	getProjectroleUser: function(name) {
		return $F('widget-autocompleter-' + name + '-hidden');
	},



	getProjectroleRoles: function(name) {
		return $F('filterwidget-select-' + name);
	},



	updateProjectRoleConditionValue: function(name) {
		var idUser 		= this.getProjectroleUser(name);
		var projectRoles= this.getProjectroleRoles(name);
		var value		= idUser + ':' + projectRoles.join(',');

		Todoyu.Ext.search.Filter.updateConditionValue(name, value);
	}
	
};