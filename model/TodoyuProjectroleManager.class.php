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
 * Projectrole manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectroleManager {

	/**
	 * Managed table
	 *
	 */
	const TABLE = 'ext_project_role';



	/**
	 * Get user role
	 *
	 * @param	Integer			$idUserrole		Userrole ID
	 * @return	TodoyuProjectrole
	 */
	public static function getProjectrole($idProjectrole) {
		return TodoyuCache::getRecord('TodoyuProjectrole', $idProjectrole);
	}



	/**
	 * Save userrole record
	 *
	 * @param	Array	$data
	 * @return	Integer	$idUserrole
	 */
	public static function saveProjectrole(array $data) {
		$idProjectrole	= intval($data['id']);
		$xmlPath		= 'ext/project/config/form/admin/projectrole.xml';

		if( $idProjectrole === 0 ) {
			$idProjectrole = self::addProjectrole();
		}

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idProjectrole);

		self::updateProjectrole($idProjectrole, $data);

		return $idProjectrole;
	}



	/**
	 * Store new userrole with given data in DB
	 *
	 * @param	Array		$data
	 * @return	Integer		New record ID
	 */
	public static function addProjectrole(array $data = array()) {
		unset($data['id']);

		$data['date_create']	= NOW;
		$data['id_person_create']	= personid();

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Update projectrole record with given data
	 *
	 * @param	Integer		$idProjectrole
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateProjectrole($idProjectrole, array $data) {
		$idProjectrole	= intval($idProjectrole);

		return TodoyuRecordManager::updateRecord(self::TABLE, $idProjectrole, $data);
	}



	/**
	 * Sets deleted flag for current worktype
	 *
	 * @param	Integer	$idUserrole
	 */
	public static function deleteProjectrole($idProjectrole) {
		return TodoyuRecordManager::deleteRecord(self::TABLE, $idProjectrole);
	}


	/**
	 * Get label of given userrole
	 *
	 * @param	Integer	$idUserrole
	 * @return	String
	 */
	public static function getLabel($idProjectrole) {
		$idProjectrole	= intval($idProjectrole);
		$label			= '';

		if ( $idProjectrole !== 0 ) {
			$projectrole= self::getProjectrole($idProjectrole);
			$label		= $projectrole->getTitle();
		}

		return $label;
	}








	/**
	 * Get all visible userroles
	 *
	 * @return	Array
	 */
	public static function getProjectroles() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'id';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Get list of existing userrole records
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecords() {
		$projectroles	= self::getProjectroles();

		$reform		= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($projectroles, $reform);
	}

}
?>