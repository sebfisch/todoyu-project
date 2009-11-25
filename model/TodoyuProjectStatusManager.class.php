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
 * Project status manager
 * Status access functions for task and project statuses
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectStatusManager {


	### TASK ###


	/**
	 * Get task status key by status index
	 *
	 * @param	Integer		$idStatus
	 * @return	String
	 */
	public static function getTaskStatusKey($idStatus) {
		$idStatus	= intval($idStatus);

		return $GLOBALS['CONFIG']['EXT']['project']['STATUS']['TASK'][$idStatus];
	}



	/**
	 * Get task status label by index or key
	 *
	 * @param	Mixed		$status			Status index or key
	 * @return	String
	 */
	public static function getTaskStatusLabel($status) {
		if( is_numeric($status) ) {
			$idStatus	= intval($status);
			$statusKey	= self::getTaskStatusKey($idStatus);
		} else {
			$statusKey	= $status;
		}

		return Label('task.status.' . $statusKey);
	}



	/**
	 * Get all task statuses
	 *
	 * @return	Array
	 */
	public static function getTaskStatuses() {
		return $GLOBALS['CONFIG']['EXT']['project']['STATUS']['TASK'];
	}



	/**
	 * Get task status label arrays. The keys are the status indexes
	 *
	 * @return	Array
	 */
	public static function getTaskStatusLabels() {
		$keys	= self::getTaskStatuses();
		$labels	= array();

		foreach( $keys as $index => $statusKey ) {
			$labels[$index] = self::getTaskStatusLabel($statusKey);
		}

		return $labels;
	}

	/**
	 * Get task status infos.
	 * The array index is the status index.
	 * The keys are: index, key, label
	 *
	 * @return	Array
	 */
	public static function getTaskStatusInfos() {
		$statuses	= self::getTaskStatuses();
		$infos		= array();

		foreach($statuses as $index => $statusKey) {
			$infos[$index] = array(
				'index'	=> $index,
				'key'	=> $statusKey,
				'label'	=> self::getTaskStatusLabel($statusKey)
			);
		}

		return $infos;
	}







	### PROJECT ###



	/**
	 * Get project status key by index
	 *
	 * @param	Integer		$idStatus
	 * @return	Array
	 */
	public static function getProjectStatusKey($idStatus) {
		$idStatus	= intval($idStatus);

		return $GLOBALS['CONFIG']['EXT']['project']['STATUS']['PROJECT'][$idStatus];
	}



	/**
	 * Get project status label by index or key
	 *
	 * @param	Mixed		$status			Status index or key
	 * @return	String
	 */
	public static function getProjectStatusLabel($status) {
		if( is_numeric($status) ) {
			$idStatus	= intval($status);
			$statusKey	= self::getProjectStatusKey($idStatus);
		} elseif ($status != '') {
			$statusKey	= $status;
		} else {
			$statusKey	= 'undefined';
		}

		return Label('project.status.' . $statusKey);
	}



	/**
	 * Get all project statuses
	 *
	 * @return	Array
	 */
	public static function getProjectStatuses() {
		return $GLOBALS['CONFIG']['EXT']['project']['STATUS']['PROJECT'];
	}



	/**
	 * Get project status label arrays. The keys are the status indexes
	 *
	 * @return	Array
	 */
	public static function getProjectStatusLabels() {
		$keys	= self::getProjectStatuses();
		$labels	= array();

		foreach( $keys as $index => $statusKey ) {
			$labels[$index] = self::getProjectStatusLabel($statusKey);
		}

		return $labels;
	}



	/**
	 * Get project status infos.
	 * The array index is the status index.
	 * The keys are: index, key, label
	 *
	 * @return	Array
	 */
	public static function getProjectStatusInfos() {
		$statuses	= self::getProjectStatuses();
		$infos		= array();

		foreach($statuses as $index => $statusKey) {
			$infos[$index] = array(
				'index'	=> $index,
				'key'	=> $statusKey,
				'label'	=> self::getProjectStatusLabel($statusKey)
			);
		}

		return $infos;
	}

}

?>