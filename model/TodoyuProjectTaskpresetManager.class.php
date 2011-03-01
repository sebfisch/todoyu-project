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
 * Taskpreset manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskpresetManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_project_taskpreset';



	/**
	 * Gets a taskpreset object
	 *
	 * @param	Integer				$idTaskpreset		Taskpreset ID
	 * @return	TodoyuProjectTaskpreset
	 */
	public static function getTaskpreset($idTaskpreset) {
		$idTaskpreset	= intval($idTaskpreset);

		return TodoyuRecordManager::getRecord('TodoyuProjectTaskpreset', $idTaskpreset);
	}



	/**
	 * Gets data of taskpreset
	 *
	 * @param	Integer				$idTaskpreset		Taskpreset ID
	 * @return	TodoyuProjectTaskpreset
	 */
	public static function getTaskpresetData($idTaskpreset) {
		$idTaskpreset	= intval($idTaskpreset);

		$preset	= TodoyuRecordManager::getRecord('TodoyuProjectTaskpreset', $idTaskpreset);

		return $preset->getData();
	}



	/**
	 * Get all task presets
	 *
	 * @return	Array
	 */
	public static function getAllTaskpresets() {
		return TodoyuRecordManager::getAllRecords(self::TABLE);
	}



	/**
	 * Save task preset record to database
	 *
	 * @param	Array	$formData
	 * @param	String	$xmlPath
	 * @return	Integer
	 */
	public static function saveTaskpreset(array $data) {
		$idTaskpreset	= intval($data['id']);
		$xmlPath		= 'ext/project/config/form/admin/taskpreset.xml';

		if( $idTaskpreset === 0 ) {
			$idTaskpreset = self::addTaskpreset();
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idTaskpreset);

		self::updateTaskpreset($idTaskpreset, $data);

		return $idTaskpreset;
	}



	/**
	 * Add task preset record
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addTaskpreset(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update task preset record
	 *
	 * @param	Integer		$idTaskpreset
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateTaskpreset($idTaskpreset, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idTaskpreset, $data);
	}



	/**
	 * Gets task preset records for list
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecords() {
		$taskPresets	= self::getAllTaskpresets();
		$reform		= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($taskPresets, $reform);
	}



	/**
	 * Sets deleted flag for given task preset record
	 *
	 * @param	Integer		$idTaskpreset
	 */
	public static function deleteTaskpreset($idTaskpreset) {
		$idTaskpreset	= intval($idTaskpreset);

		return Todoyu::db()->deleteRecord(self::TABLE, $idTaskpreset);
	}
}

?>