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

Todoyu.Ext.project.PanelWidget.ProjectTree = {

	ext: Todoyu.Ext.project,

	key:	'projecttree',

	url: Todoyu.getUrl('project', 'panelwidgetprojecttree'),

	init: function() {
		this.Filter.init();
	},



	/**
	 *	Update project tree panel widget
	 *
	 *	@param	Array	filterValues
	 */
	update: function(filterValues) {
		var filterValues = $H(filterValues);
		var options	= {
			'parameters': {
				'action': 'updatetree'
			}
		};

		filterValues.each(function(pair){
			postfix = Object.isArray(pair.value) ? '[]' : '';
			options.parameters['filter[' + pair.key + ']' + postfix] = pair.value;
		});
		var target	= 'panelwidget-projecttree-tree';

		Todoyu.Ui.replace(target, this.url, options);
	},



	/**
	 *	OnUpdate event handler
	 */
	onUpdate: function() {
		Todoyu.PanelWidget.inform(this.id, null);
	},



	/**
	 *	Toggle project (Alias of: Todoyu.Ext.project.PanelWidget.ProjectTree.toggle(...))
	 *
	 *	@param	Integer	idProject
	 */
	toggleProjectTasks: function(idProject) {
		this.toggle(idProject, 'project');
	},



	/**
	 *	Toggle sub tasks (Alias of: Todoyu.Ext.project.PanelWidget.ProjectTree.toggle(...))
	 *
	 *	@param	Integer	idTask
	 */
	toggleSubtasks: function(idTask) {
		this.toggle(idTask, 'task');
	},



	/**
	 *	Toggle element(s)
	 *
	 *	@param	String	idItem
	 *	@param	String	type
	 */
	toggle: function(idItem, type) {
		var idElement	= 'panelwidget-projecttree-' + type + '-' + idItem;
		var idSubtasks	= 'panelwidget-projecttree-subtasks-' + type + '-' + idItem;

		$(idElement).toggleClassName('expanded');

		if( Todoyu.exists(idSubtasks) ) {
			$(idSubtasks).toggle();
		} else {
			var options	= {
				parameters: {
					'action': 	'subtasks',
					'parent':	idItem,
					'type':		type,
					'area':		Todoyu.getArea()
				},
				'insertion': 'bottom',
				'onComplete': function(response) {
					$(idSubtasks).show();
				}
			};

			Todoyu.Ui.update(idElement, this.url, options);
		}

		this.saveExpanded(type, idItem, $(idElement).hasClassName('expanded'));





//	subtasks = $('pwdgt-projecttree-subtasks-' + idElement);
/*
		if( subtasks.visible() ) {
			subtasks.hide();
			element.removeClassName('expanded');
			this.sendClose(type, idElement);
		} else {
			subtasks.show();
			element.addClassName('expanded');
			this.sendOpen(type, idElement);
		}
		*/
	},



	/**
	 *	Save pref: which project(s) are expanded
	 *
	 *	@param	String	type
	 *	@param	Integer	idElement
	 *	@param	Boolean	expanded
	 */
	saveExpanded: function(type, idElement, expanded) {
		var value = type + ':' + (expanded ? 1 : 0) ;
		this.savePref('pwidget-projecttree-expand', value, idElement);
	},



	/**
	 *	Save preference
	 *
	 *	@param	String	pref
	 *	@param	String	value
	 *	@param	Integer	idItem
	 */
	savePref: function(pref, value, idItem) {
		Todoyu.Pref.save('project', pref, value, idItem);
	},



	/**
	 *	Send 'close' (collapse) request
	 *
	 *	@param	String	type
	 *	@param	Integer	idElement
	 */
	sendClose: function(type, idElement) {
		var options = {
			'parameters': {
				'action':	'collapse',
				'type':		type,
				'element':	idElement
			}
		};

		Todoyu.send(this.url, options);
	},



	/**
	 *	Send 'open' (expand) request
	 *
	 *	@param	String	type
	 *	@param	Integer	idElement
	 */
	sendOpen: function(type, idElement) {
		var options = {
			'parameters': {
				'action':	'expand',
				'type':		type,
				'element':	idElement
			}
		};

		Todoyu.send(this.url, options);
	},



	/**
	 *	Open (expand) project
	 *
	 *	@param	Integer	idProject
	 */
	openProject: function(idProject) {
		this.ext.ProjectTaskTree.openProject(idProject);
	},
	
	
	
	/**
	 * Open a specific task in a project
	 * @param {Object} idProject
	 * @param {Object} idTask
	 */
	openProjectTask: function(idProject, idTask) {
		this.ext.ProjectTaskTree.openProject(idProject, idTask);
	}
};