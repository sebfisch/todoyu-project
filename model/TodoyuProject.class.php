<?php
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

		if( $idProject === 0 )	{
			$this->initialize();
		}
	}



	/**
	 * set initial data of a (empty) project
	 *
	 */
	protected function initialize()	{
		$this->offsetSet('title', Label('LLL:project.newProject.tabLabel'));
	}



	/**
	 * Get full project title with customer short name
	 *
	 * @return	String
	 */
	public function getFullTitle() {
		$customer	= $this->getCustomerArray();

		return $customer['shortname'] . ' - ' . $this->getTitle();
	}



	/**
	 * Get customer ID
	 *
	 * @return	Integer
	 */
	public function getCustomerID() {
		return intval($this->data['id_customer']);
	}



	/**
	 * Get customer object
	 *
	 * @return	Customer
	 */
	public function getCustomer() {
		return TodoyuCustomerManager::getCustomer($this->getCustomerID());
	}



	/**
	 * Get customer array
	 *
	 * @return	Array
	 */
	public function getCustomerArray() {
		if( $this->isInCache('customer') ) {
			$customer	= $this->getCacheItem('customer');
		} else {
			$customer 	= TodoyuCustomerManager::getCustomerArray($this->getCustomerID());
			$this->addToCache('customer', $customer);
		}

		return $customer;
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
		return TodoyuProjectStatusManager::getProjectStatusKey($this->getStatus());
	}



	/**
	 * Get status label of the project
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuProjectStatusManager::getProjectStatusLabel($this->getStatus());
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
	 * loads foreign data of a project
	 *
	 */
	public function loadForeignData()	{
		$this->data['projectusers'] = $this->loadProjectUserRecords();
	}



	/**
	 * loads data from ext_project_mm_project_user and injects them to the project data array
	 *
	 * @return	Array
	 */
	private function loadProjectUserRecords()	{
		$idProject = intval($this->get('id'));

		$table	= 'ext_project_mm_project_user';
		$fields = '*';
		$where	= 'id_project = '.$idProject;

		$result = Todoyu::db()->doSelect($fields, $table, $where);

		$projectUsers = array();

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$projectUsers[] = $row;
		}

		return $projectUsers;
	}



	/**
	 *	Get template data
	 *
	 *	@param	Boolean	$loadForeignData
	 *	@return	Array
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