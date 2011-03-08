/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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

/**
 *	Ext: project
 */

Todoyu.Ext.project.Sysmanager = {

	/**
	 * Initialize taskpreset record form of sysadmin
	 *
	 * @method	initTaskpresetForm
	 * @param	{Number}	idRecord
	 */
	initTaskpresetForm: function(idRecord) {
			// Init "person_assigned"
		var personAssignedField	= $('record-' + idRecord + '-field-id-person-assigned');
		if( personAssignedField ) {
			this.onChangeTaskpresetPersonAssigned(personAssignedField);
		}
			// Init "person_owner"
		var personOwnerField	= $('record-' + idRecord + '-field-id-person-owner');
		if( personOwnerField ) {
			this.onChangeTaskpresetPersonOwner(personOwnerField);
		}
	},



	/**
	 * Initialize sysmanager project extension config
	 *
	 * @method	initExtConfig
	 */
	initExtConfig: function() {
			// Init "person_assigned"
		var personAssignedField	= $('config-field-id-person-assigned');
		if( personAssignedField ) {
			this.onChangeTaskpresetPersonAssigned(personAssignedField);
		}
			// Init "person_owner"
		var personOwnerField	= $('config-field-id-person-owner');
		if( personOwnerField ) {
			this.onChangeTaskpresetPersonOwner(personOwnerField);
		}
	},



	/**
	 * Onchange handler for assigned person select field in taskpreset (sysadmin record form)
	 *
	 * @method	onChangeTaskpresetPersonAssigned
	 * @param	{Element}	field
	 */
	onChangeTaskpresetPersonAssigned: function(field) {
		var idAssignedRoleField	= field.id.replace('id-person-assigned', 'person-assigned-role');
		var idSelectedPerson	= $F(field.id);

		this.onChangePersonVersusRoleOption(idAssignedRoleField, idSelectedPerson);
	},



	/**
	 * Onchange handler for assigned person select field in taskpreset (sysadmin record form)
	 *
	 * @method	onChangeTaskpresetPersonOwner
	 * @param	{Element}	field
	 */
	onChangeTaskpresetPersonOwner: function(field) {
		var idAssignedRoleField	= field.id.replace('id-person-owner', 'person-owner-role');
		var idSelectedPerson	= $F(field.id);

		this.onChangePersonVersusRoleOption(idAssignedRoleField, idSelectedPerson);
	},



	/**
	 * Onchange handling for person option for fields which there is a corresponding role-based selector for also
	 * If any person selected, disable role-based option and deselect role over there
	 *
	 * @method	onChangePersonVersusRoleOption
	 * @param	{String}	idRoleField
	 * @param	{Number}	idPerson
	 */
	onChangePersonVersusRoleOption: function(idRoleField, idPerson) {
		if( idPerson != 0 ) {
			$(idRoleField).options[0].selected	= true;
			$(idRoleField).disabled	= true;
		} else {
			$(idRoleField).disabled	= false;
		}
	}

};