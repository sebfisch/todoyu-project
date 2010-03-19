Todoyu.Ext.project.Portal = {

	ext: Todoyu.Ext.project,

	init: function() {
		if( Todoyu.getArea() === 'portal' ) {
			Todoyu.Hook.add('onProjectCreated', this.onProjectCreate.bind(this));
			Todoyu.Hook.add('onProjectSaved', this.onProjectSaved.bind(this));
		}
	},

	onProjectCreate: function(idProject) {
		this.refreshProjectListing();
	},

	onProjectSaved: function(idProject) {
		this.refreshProjectListing();
	},

	isProjectListingActive: function() {
		if( Todoyu.Ext.portal.Tab.getActiveTab() === 'selection' ) {
			return Todoyu.exists('projectlist');
		}

		return false;
	},

	refreshProjectListing: function() {
		if( this.isProjectListingActive() ) {
			Todoyu.Ext.portal.Tab.refresh();
		}
	}

};