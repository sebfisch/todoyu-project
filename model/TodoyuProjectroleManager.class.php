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
	 * Default table for database requests
	 */
	const TABLE = 'ext_project_role';



	/**
	 * Get projectrole
	 *
	 * @param	Integer		$idProjectrole
	 * @return	TodoyuProjectrole
	 */
	public static function getProjectrole($idProjectrole) {
		return TodoyuRecordManager::getRecord('TodoyuProjectrole', $idProjectrole);
	}



	/**
	 * Save projectrole
	 *
	 * @param	Array		$data
	 * @return	Integer		$idProjectrole
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
	 * Add new projectrole
	 *
	 * @param	Array		$data
	 * @return	Integer		New projectrole ID
	 */
	public static function addProjectrole(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
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
	 * @param	Integer		$idProjectrole
	 */
	public static function deleteProjectrole($idProjectrole) {
		return TodoyuRecordManager::deleteRecord(self::TABLE, $idProjectrole);
	}


	/**
	 * Get label of projectrole
	 *
	 * @param	Integer		$idProjectrole
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
	 * Get all active projectroles
	 *
	 * @return	Array
	 */
	public static function getProjectroles($parse = true) {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'id';

		$projectroles	= Todoyu::db()->getArray($fields, $table, $where, '', $order);

		if( $parse ) {
			foreach($projectroles as $index => $projectrole) {
				$projectroles[$index]['title'] = TodoyuString::getLabel($projectrole['title']);
			}
		}

		return $projectroles;
	}



	/**
	 * Get list of existing projectrole records
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