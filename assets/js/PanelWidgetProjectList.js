Todoyu.Ext.project.PanelWidget.ProjectList = {
	
	ext: Todoyu.Ext.project,
	
	fulltextTimeout: null,
	
	filters: {},
	
	
	/**
	 * Initialize panelwidget
	 * 
	 * @param	Object		filters		Filter hash. Because of JSON, an (empty) array means no data
	 */
	init: function(filters) {
			// If filters are given as parameters, add them to internal storage
		if( typeof(filters) === 'object' && ! Object.isArray(filters) ) {
			$H(filters).each(function(pair){
				this.applyFilter(pair.key, pair.value, false);
			}, this);
		}	
		
		this.observeFulltext();
		this.observeProjects();
		this.observeStatusSelector();
		
			// Add a hook for project saving
		Todoyu.Hook.add('onProjectSaved', this.onProjectUpdated.bind(this));		
	},
	

	observeFulltext: function() {
			// Install fulltext observer
		$('panelwidget-projectlist-field-fulltext').observe('keyup', this.onFulltextKeyup.bindAsEventListener(this));
	},
	
	observeProjects: function() {
			// Install project observer
		$('panelwidget-projectlist-list').observe('click', this.onProjectClick.bindAsEventListener(this));
	},
	
	observeStatusSelector: function() {
		Todoyu.PanelWidget.observe('projectstatusfilter', this.onStatusFilterUpdate.bind(this));
	},
		
	onFulltextKeyup: function(event) {
		this.clearTimeout();		
		this.applyFilter('fulltext', this.getFulltext());		
		
		this.startTimeout();		
	},
	
	onProjectClick: function(event) {
		var idProject = event.findElement('li').id.split('-').last();	
		
		this.ext.ProjectTaskTree.openProject(idProject);
	},
	
	onStatusFilterUpdate: function(widgetkey, statuses) {
		this.applyFilter('status', statuses, true);
	},
	
	clearTimeout: function() {
		clearTimeout(this.fulltextTimeout);
	},
	
	startTimeout: function() {
		this.fulltextTimeout = this.update.bind(this).delay(0.3);
	},
	
	getFulltext: function() {
		return $F('panelwidget-projectlist-field-fulltext');
	},
	
	applyFilter: function(name, value, update) {
		this.filters[name] = value;		
		
		if( update === true ) {
			this.clearTimeout();
			this.update();
		}
	},
	
	update: function() {
		var url		= Todoyu.getUrl('project', 'panelwidgetprojectlist');
		var options	= {
			'parameters': {
				'action':	'list',
				'filters':	Object.toJSON(this.filters)
			},
			'onComplete':	this.onUpdated.bind(this)
		};
		var target	= 'panelwidget-projectlist-list';
		
		Todoyu.Ui.replace(target, url, options);
	},
	
	onUpdated: function(response) {
		this.observeProjects();
	},
	
	isProjectListed: function(idProject) {
		return Todoyu.exists('panelwidget-projectlist-project-' + idProject);
	},
	
	onProjectUpdated: function(idProject) {
		if( this.isProjectListed(idProject) ) {
			this.update();
		}
	}
	
};