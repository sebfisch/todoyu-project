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

Todoyu.Ext.project.Filter = {

	ext: Todoyu.Ext.project,



	onProjectSearchResultsUpdated: function() {
		this.ext.ContextMenuProject.attach();
	},



	onTaskSearchResultsUpdated: function() {
		this.ext.ContextMenuTask.attach();
	},



	onProjectrolePersonAcSelect: function(name, textInput, listElement) {
		$('widget-autocompleter-' + name + '-hidden').value = listElement.id;

		Todoyu.Ext.project.Filter.updateProjectRoleConditionValue(name);
	},



	onProjectroleRoleChange: function(name) {
		Todoyu.Ext.project.Filter.updateProjectRoleConditionValue(name);
	},



	getProjectrolePerson: function(name) {
		return $F('widget-autocompleter-' + name + '-hidden');
	},



	getProjectroleRoles: function(name) {
		return $F('filterwidget-select-' + name);
	},



	updateProjectRoleConditionValue: function(name) {
		var idPerson	= this.getProjectrolePerson(name);
		var projectRoles= this.getProjectroleRoles(name);
		var value		= idPerson + ':' + projectRoles.join(',');

		Todoyu.Ext.search.Filter.updateConditionValue(name, value);
	}

};