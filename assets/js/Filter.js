Todoyu.Ext.project.Filter = {
	
	ext: Todoyu.Ext.project,
	
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