/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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

Todoyu.Ext.project.Portal = {

	/**
	 * Extension backlink
	 *
	 * @var	{Object}	ext
	 */
	ext: Todoyu.Ext.project,

	init: function() {
		if( Todoyu.getArea() === 'portal' ) {
			Todoyu.Hook.add('project.project.created', this.onProjectCreate.bind(this));
			Todoyu.Hook.add('project.project.saved', this.onProjectSaved.bind(this));
		}
	},



	/**
	 * @todo	comment
	 * @param	{Number}	idProject
	 */
	onProjectCreate: function(idProject) {
		this.refreshProjectListing();
	},



	/**
	 * @todo	comment
	 * @param	{Number}	idProject
	 */
	onProjectSaved: function(idProject) {
		this.refreshProjectListing();
	},


	/**
	 * @todo	comment
	 */
	isProjectListingActive: function() {
		if( Todoyu.Ext.portal.Tab.getActiveTab() === 'selection' ) {
			return Todoyu.exists('projectlist');
		}

		return false;
	},



	/**
	 * @todo	comment
	 */
	refreshProjectListing: function() {
		if( this.isProjectListingActive() ) {
			Todoyu.Ext.portal.Tab.refresh();
		}
	}

};