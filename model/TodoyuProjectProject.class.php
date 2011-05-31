<?php
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
 * Project object
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectProject extends TodoyuBaseObject {

	/**
	 * Initialize project
	 *
	 * @param	Integer		$idProject
	 */
	public function __construct($idProject) {
		parent::__construct($idProject, 'ext_project_project');
	}



	/**
	 * Get full project title with company short name
	 *
	 * @param	Boolean	$companyShort
	 * @return	String
	 */
	public function getFullTitle($companyShort = false) {
		$company	= $companyShort ? $this->getCompany()->getShortLabel() : $this->getCompany()->getTitle();

		return $company . ' - ' . $this->getTitle();
	}



	/**
	 * Get company ID
	 *
	 * @return	Integer
	 */
	public function getCompanyID() {
		return intval($this->data['id_company']);
	}



	/**
	 * Get company object
	 *
	 * @return	 TodoyuContactCompany
	 */
	public function getCompany() {
		return TodoyuContactCompanyManager::getCompany($this->getCompanyID());
	}



	/**
	 * Get project status ID
	 *
	 * @return	Integer
	 */
	public function getStatus() {
		return intval($this->data['status']);
	}



	/**
	 * Get status key of the project
	 *
	 * @return	String
	 */
	public function getStatusKey() {
		return TodoyuProjectProjectStatusManager::getStatusKey($this->getStatus());
	}



	/**
	 * Get status label of the project
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuProjectProjectStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Get project start date
	 *
	 * @return	Integer
	 */
	public function getStartDate() {
		return intval($this->get('date_start'));
	}



	/**
	 * Get project end date
	 *
	 * @return	Integer
	 */
	public function getEndDate() {
		return intval($this->get('date_end'));
	}



	/**
	 * Get project deadline date
	 *
	 * @return	Integer
	 */
	public function getDeadlineDate() {
		return intval($this->get('date_deadline'));
	}



	/**
	 * Get project range
	 *
	 * @return	Array
	 */
	public function getRange() {
		return array(
			'start'	=> $this->getStartDate() === 0 ? $this->get('date_create') : $this->getStartDate(),
			'end'	=> $this->getDeadlineDate() === 0 ? $this->getEndDate() === 0 ? NOW : $this->getEndDate() : $this->getDeadlineDate()
		);
	}



	/**
	 * Check whether current person is assigned to this project
	 *
	 * @return	Boolean
	 */
	public function isCurrentPersonAssigned() {
		return TodoyuProjectProjectManager::isPersonAssigned($this->getID());
	}



	/**
	 * Load foreign data of a project
	 */
	public function loadForeignData() {
		$this->data['persons'] = $this->getPersons();
		$this->data['company'] = $this->getCompany()->getTemplateData(false);
	}



	/**
	 * Get project persons
	 *
	 * @return	Array
	 */
	public function getPersons() {
		return TodoyuProjectProjectManager::getProjectPersons($this->getID());
	}



	/**
	 * Get IDs of project persons
	 *
	 * @return	Array
	 */
	public function getPersonsIDs() {
		$persons		= $this->getPersons();
		$reformConfig	= array(
			'id'	=> 'id_person',
		);

		return TodoyuArray::flatten(TodoyuArray::reform($persons, $reformConfig));
	}



	/**
	 * Get all IDs of persons with the ID of their todoyu role
	 *
	 * @param	Boolean		$indexWithPersonIDs
	 * @return	Array
	 */
	public function getPersonsRolesIDs($indexWithPersonIDs = false) {
		$persons		= $this->getPersons();

		$reformConfig	= array(
			'id_person'	=> 'id_person',
			'id_role'	=> 'id_role'
		);

		if( $indexWithPersonIDs ) {
			$personRoles = TodoyuArray::reformWithFieldAsIndex($persons, $reformConfig, false, 'id_person');
		} else {
			$personRoles = TodoyuArray::reform($persons, $reformConfig);	
		}

		return $personRoles;
	}



	/**
	 * Get all IDs of persons with the label of their assigned projectrole
	 *
	 * @param	Boolean		$indexWithPersonIDs
	 * @return	Array
	 */
	public function getPersonsProjectrolesLabels($indexWithPersonIDs = false) {
		$persons		= $this->getPersons();
		$reformConfig	= array(
			'id_person'	=> 'id_person',
			'rolelabel'	=> 'rolelabel'
		);

		if( $indexWithPersonIDs ) {
			$personRoles = TodoyuArray::reformWithFieldAsIndex($persons, $reformConfig, false, 'id_person');
		} else {
			$personRoles = TodoyuArray::reform($persons, $reformConfig);
		}

		return $personRoles;
	}



	/**
	 * Get ID of role of given (or currently logged-in) person in project
	 *
	 * @param	Integer				$idPerson
	 * @return	TodoyuProjectProjectrole				0 if no role defined for person
	 */
	public function getPersonRoleID($idPerson = 0) {
		$idPerson	= ( $idPerson === 0 ) ? Todoyu::personid() : intval($idPerson);
		$idRole		= 0;

		$persons	= $this->getPersons();
		foreach($persons as $person) {
			if( $person['id'] == $idPerson ) {
				$idRole	= $person['id_role'];
				break;
			}
		}

		return $idRole;
	}



	/**
	 * Get role of given (or currently logged-in) person in project
	 *
	 * @param	Integer				$idPerson
	 * @return	TodoyuProjectProjectrole
	 */
	public function getPersonRole($idPerson = 0) {
		$idRole		= $this->getPersonRoleID($idPerson);

		return TodoyuProjectProjectroleManager::getProjectrole($idRole);
	}



	/**
	 * Get ID of assigned taskpreset of project
	 *
	 * @return	Integer
	 */
	public function getTaskpresetID() {
		return intval($this->data['id_taskpreset']);
	}



	/**
	 * Check whether project is locked
	 *
	 * @return	Boolean
	 */
	public function isLocked() {
		return TodoyuLockManager::isLocked('ext_project_project', $this->getID());
	}



	/**
	 * Check whether a project has locked tasks
	 *
	 * @return	Boolean
	 */
	public function hasLockedTasks() {
		$field	= '	t.id';
		$tables	= '	system_lock sl,
					ext_project_task t';
		$where	= '		t.id_project= ' . $this->getID()
				. ' AND	t.id		= sl.id_record'
				. ' AND sl.table	= \'ext_project_task\'';

		return Todoyu::db()->hasResult($field, $tables, $where);
	}



	/**
	 * Check whether this project is editable
	 *
	 * @return	Boolean
	 */
	public function isEditable() {
		return TodoyuProjectProjectRights::isEditAllowed() && $this->isLocked() === false;
	}



	/**
	 * Get template data
	 *
	 * @param	Boolean		$loadForeignData
	 * @return	Array
	 */
	public function getTemplateData($loadForeignData = false) {
		if( $loadForeignData ) {
			$this->loadForeignData();
		}

		$this->data['fulltitle'] = $this->getFullTitle();
		$this->data['statusKey'] = $this->getStatusKey();

		return parent::getTemplateData();
	}

}

?>