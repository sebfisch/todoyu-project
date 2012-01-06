<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Task preset manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskPresetManager {

	/**
	 * Default table for database requests
	 *
	 * @var	String
	 */
	const TABLE = 'ext_project_taskpreset';



	/**
	 * Gets a taskpreset object
	 *
	 * @param	Integer		$idTaskPreset
	 * @return	TodoyuProjectTaskPreset
	 */
	public static function getTaskPreset($idTaskPreset) {
		$idTaskPreset	= intval($idTaskPreset);

		return TodoyuRecordManager::getRecord('TodoyuProjectTaskPreset', $idTaskPreset);
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
	public static function getAllTaskPresets() {
		return TodoyuRecordManager::getAllRecords(self::TABLE);
	}



	/**
	 * Save task preset record to database
	 *
	 * @param	Array	$data
	 * @return	Integer
	 */
	public static function saveTaskPreset(array $data) {
		$idTaskPreset	= intval($data['id']);
		$xmlPath		= 'ext/project/config/form/admin/taskpreset.xml';

		if( $idTaskPreset === 0 ) {
			$idTaskPreset = self::addTaskpreset();
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idTaskPreset);

		self::updateTaskPreset($idTaskPreset, $data);

		return $idTaskPreset;
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
	 * @param	Integer		$idTaskPreset
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateTaskPreset($idTaskPreset, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idTaskPreset, $data);
	}



	/**
	 * Gets task preset records for list
	 *
	 * @return	Array
	 */
	public static function getRecords() {
		$taskPresets	= self::getAllTaskPresets();
		$reformConfig	= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($taskPresets, $reformConfig);
	}



	/**
	 * Sets deleted flag for given task preset record
	 *
	 * @param	Integer		$idTaskPreset
	 * @return	Boolean
	 */
	public static function deleteTaskPreset($idTaskPreset) {
		$idTaskPreset	= intval($idTaskPreset);

		return TodoyuRecordManager::deleteRecord(self::TABLE, $idTaskPreset);
	}

}

?>