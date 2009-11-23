Todoyu.Ext.project.PanelWidget.QuickProject = {
	
	ext: Todoyu.Ext.project,
	
	addProject: function() {
		this.ext.Project.add();
	},
	
	addTask: function() {
		Todoyu.Ext.project.QuickTask.openPopup(this.onTaskAdded.bind(this));
	},
	
	onTaskAdded: function(idTask, idProject, started) {
		
	}
	
};