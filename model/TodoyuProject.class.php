<?php
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

/**
 * Project object
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProject extends TodoyuBaseObject {

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
	 * @return	TodoyuCompany
	 */
	public function getCompany() {
		return TodoyuCompanyManager::getCompany($this->getCompanyID());
	}



	/**
	 * Get company array
	 *
	 * @return	Array
	 */
	public function getCompanyData() {
		if( $this->isInCache('company') ) {
			$company	= $this->getCacheItem('company');
		} else {
			$company 	= TodoyuCompanyManager::getCompanyData($this->getCompanyID());
			$this->addToCache('company', $company);
		}

		return $company;
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
		return TodoyuProjectStatusManager::getStatusKey($this->getStatus());
	}



	/**
	 * Get status label of the project
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuProjectStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Get project start date
	 *
	 * @return	Integer
	 */
	public function getStartDate() {
		return $this->get('date_start');
	}



	/**
	 * Get project end date
	 *
	 * @return	Integer
	 */
	public function getEndDate() {
		return $this->get('date_end');
	}



	/**
	 * Get project deadline date
	 *
	 * @return	Integer
	 */
	public function getDeadlineDate() {
		return $this->get('date_deadline');
	}
	
	
	
	/**
	 * checks if the Project is deleted
	 * 
	 * @return	Boolean
	 */
	public function isDeleted()	{
		return $this->get('deleted') == 1;
	}



	/**
	 * Check whether current person is assigned to this project
	 *
	 * @return	Boolean
	 */
	public function isCurrentPersonAssigned() {
		return TodoyuProjectManager::isPersonAssigned($this->id);
	}



	/**
	 * Load foreign data of a project
	 */
	public function loadForeignData()	{
		$this->data['persons'] = TodoyuProjectManager::getProjectPersons($this->id);
		$this->data['company'] = $this->getCompanyData();
	}



	/**
	 * Get project persons
	 *
	 * @return	Array
	 */
	public function getPersons() {
		if( ! array_key_exists('persons', $this->data) ) {
			$this->loadForeignData();
		}

		return $this->data['persons'];
	}



	/**
	 * Get ID of role of given (or currently logged-in) person in project
	 *
	 * @param	Integer				$idPerson
	 * @return	TodoyuProjectRole				0 if no role defined for person
	 */
	public function getPersonRoleID($idPerson = 0) {
		$idPerson	= ( $idPerson === 0 ) ? personid() : intval($idPerson);
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
	 * @return	TodoyuProjectRole
	 */
	public function getPersonRole($idPerson = 0) {
		$idRole		= $this->getPersonRoleID($idPerson);

		return TodoyuProjectroleManager::getProjectrole($idRole);
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
		return TodoyuProjectRights::isEditAllowed() && $this->isLocked() === false;
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