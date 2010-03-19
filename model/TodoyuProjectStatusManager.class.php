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

	/**
	 * Get project status key by index
	 *
	 * @param	Integer		$idStatus
	 * @return	Array
	 */
	public static function getStatusKey($idStatus) {
		$idStatus	= intval($idStatus);

		return $GLOBALS['CONFIG']['EXT']['project']['STATUS']['PROJECT'][$idStatus];
	}



	/**
	 * Get project status label by index or key
	 *
	 * @param	Mixed		$status			Status index or key
	 * @return	String
	 */
	public static function getStatusLabel($status) {
		if( is_numeric($status) ) {
			$idStatus	= intval($status);
			$statusKey	= self::getStatusKey($idStatus);
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
	public static function getStatuses($forceStatus = 0) {
		$forceStatus= intval($forceStatus);

		$statuses	= TodoyuArray::assure($GLOBALS['CONFIG']['EXT']['project']['STATUS']['PROJECT']);

		foreach($statuses as $index => $statusKey) {
				// Only get allowed status which the person can see
			if( ! allowed('project', 'projectstatus:' . $statusKey . ':see') && $index !== $forceStatus) {
				unset($statuses[$index]);
			}
		}

		return sizeof($statuses) > 0 ? $statuses : array(999 => 0);
	}



	/**
	 * Get project status label arrays. The keys are the status indexes
	 *
	 * @return	Array
	 */
	public static function getStatusLabels() {
		$keys	= self::getStatuses();
		$labels	= array();

		foreach( $keys as $index => $statusKey ) {
			$labels[$index] = self::getStatusLabel($statusKey);
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
	public static function getStatusInfos() {
		$statuses	= self::getStatuses();
		$infos		= array();

		foreach($statuses as $index => $statusKey) {
			$label	= self::getStatusLabel($statusKey);
			$infos[$index] = TodoyuProjectViewHelper::getStatusOption($index, $statusKey, $label);
		}

		return $infos;
	}

}

?>