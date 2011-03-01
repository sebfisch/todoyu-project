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
 * Activity manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectActivityManager {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_project_activity';



	/**
	 * Gets a activity object
	 *
	 * @param	Integer		$idActivity
	 * @return	TodoyuProjectActivity
	 */
	public static function getActivity($idActivity) {
		$idActivity	= intval($idActivity);

		return TodoyuRecordManager::getRecord('TodoyuProjectActivity', $idActivity);
	}



	/**
	 * Get all work types
	 *
	 * @return	Array
	 */
	public static function getAllActivities() {
		return TodoyuRecordManager::getAllRecords(self::TABLE);
	}



	/**
	 * Save activity record to database
	 *
	 * @param	Array		$formData
	 * @param	String		$xmlPath
	 * @return	Integer
	 */
	public static function saveActivity(array $data) {
		$idActivity	= intval($data['id']);
		$xmlPath	= 'ext/project/config/form/admin/activity.xml';

		if( $idActivity === 0 ) {
			$idActivity = self::addActivity();
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idActivity);

		self::updateActivity($idActivity, $data);

		return $idActivity;
	}



	/**
	 * Add activity record
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addActivity(array $data = array()) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update activity record
	 *
	 * @param	Integer		$idActivity
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateActivity($idActivity, array $data) {
		return TodoyuRecordManager::updateRecord(self::TABLE, $idActivity, $data);
	}



	/**
	 * Gets activity records for list
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecords() {
		$activities	= self::getAllActivities();
		$reform		= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($activities, $reform);
	}



	/**
	 * Sets deleted flag for activity
	 *
	 * @param	Integer		$idActivity
	 */
	public static function deleteActivity($idActivity) {
		$idActivity	= intval($idActivity);

		return Todoyu::db()->deleteRecord(self::TABLE, $idActivity);
	}
}

?>