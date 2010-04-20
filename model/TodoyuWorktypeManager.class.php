<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Worktype manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuWorktypeManager {

	/**
	 * Default table for database requests
	 */
	const TABLE = 'ext_project_worktype';



	/**
	 * Gets a Worktype object
	 *
	 * @param	Integer		$idWorktype		Worktype ID
	 * @return	TodoyuWorktype
	 */
	public static function getWorktype($idWorktype) {
		$idWorktype	= intval($idWorktype);

		return TodoyuRecordManager::getRecord('TodoyuWorktype', $idWorktype);
	}



	/**
	 * Get all worktypes
	 *
	 * @return	Array
	 */
	public static function getAllWorktypes() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'title';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Save worktype record to database
	 *
	 * @param	Array	$formData
	 * @param	String	$xmlPath
	 * @return	Integer
	 */
	public static function saveWorktype(array $data) {
		$idWorktype	= intval($data['id']);
		$xmlPath	= 'ext/project/config/form/admin/worktype.xml';

		if( $idWorktype === 0 ) {
			$idWorktype = self::addWorktype();
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idWorktype);

		self::updateWorktype($idWorktype, $data);

		return $idWorktype;
	}



	/**
	 * Add worktype record
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addWorktype(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update worktype record
	 *
	 * @param	Integer		$idWorktype
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateWorktype($idWorktype, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idWorktype, $data);
	}



	/**
	 * Gets worktype records for list
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecords() {
		$worktypes	= self::getAllWorktypes();
		$reform		= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($worktypes, $reform);
	}



	/**
	 * Sets deleted flag for current worktype
	 *
	 * @param	Integer		$idWorktype
	 */
	public static function deleteWorktype($idWorktype)	{
		$idWorktype	= intval($idWorktype);

		return Todoyu::db()->deleteRecord(self::TABLE, $idWorktype);
	}
}

?>