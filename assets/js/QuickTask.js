Todoyu.Ext.project.QuickTask = {
	
	ext: Todoyu.Ext.project,
	
	fieldStart: 'quicktask-0-field-start-tracking',

	fieldDone: 'quicktask-0-field-task-done',
	
	popupID: 'quicktask',
	
	savedCallback: null,
	
	
	/**
	 *	Toggle quick task popup
	 */
	openPopup: function(callback) {
		var url		= Todoyu.getUrl('project', 'quicktask');
		var options	= {
			'parameters': {
				'action': 'popup'
			}
		};
		this.savedCallback = callback;

		Todoyu.Popup.openWindow(this.popupID, 'Quicktask wizard', 420, 310, url, options);
	},
	
	closePopup: function() {
		Todoyu.Popup.close(this.popupID);
	},	



	/**
	 *	Save (quick-) task
	 *
	 *	@param	String	form
	 */
	save: function(form)	{
		tinyMCE.triggerSave();

		$(form).request({
			'parameters': {
				'action': 'save'
			},
			'onComplete': this.onSaved.bind(this, form)
		});

		return false;
	},

	onSaved: function(form, response) {
		if( response.hasTodoyuError() ) {
			$(form).replace(response.responseText);
		} else {
			var idTask		= response.getTodoyuHeader('idTask');
			var idProject	= response.getTodoyuHeader('idProject');
			var start		= response.getTodoyuHeader('start') == 1;
			
			this.closePopup();
			Todoyu.notifySuccess('[LLL:project.quicktask.saved]');

			if( start ) {
				var idTask= response.getTodoyuHeader('idTask');
				Todoyu.Ext.timetracking.Task.start(idTask);
			}
			
			if( this.savedCallback !== null ) {
				this.savedCallback(idTask, idProject, start);
				this.savedCallback = null;
			}
		}
	},


	
	preventStartDone: function(key, field) {
		if( key === 'start' ) {
			if( $(this.fieldDone).checked ) {
				$(this.fieldDone).checked = false;
			}
		}
		if( key === 'done' ) {
			if( $(this.fieldStart).checked ) {
				$(this.fieldStart).checked = false;
			}
		}
	},



	/**
	 *	Disable checkbox 'task done'
	 *
	 *	@param	Element	input
	 */
	disableCheckboxTaskDone: function(input)	{
		if(input.getValue() == 1)	{
			$(this.fieldDone).disabled = true;
		} else {
			$(this.fieldDone).disabled = false;
		}
	},



	/**
	 *	Disable checkbox 'start workload'
	 *
	 *	@param	Element	input
	 */
	disableCheckboxStartWorkload: function(input)	{
		if(input.getValue() == 1)	{
			$(this.fieldStart).disabled = true;
		} else {
			$(this.fieldStart).disabled = false;
		}
	}
	
};