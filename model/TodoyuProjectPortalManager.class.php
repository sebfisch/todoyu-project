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
 * Project manager for portal
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPortalManager {

	/**
	 * Get task IDs for todo tab
	 *
	 * @return	Array
	 */
	public static function getTodoTaskIDs() {
		$conditions	= Todoyu::$CONFIG['EXT']['project']['portalTodoTabFilters'];
		$taskFilter	= new TodoyuTaskFilter($conditions);
		$taskIDs	= $taskFilter->getTaskIDs();

		return $taskIDs;
	}


	/**
	 * Get number of tasks for todo tabs
	 *
	 * @param	Array		$filtersetIDs		No needed, but standard
	 * @return	Integer
	 */
	public static function getTodoCount(array $filtersetIDs = array()) {
		$taskIDs	= self::getTodoTaskIDs();

		return sizeof($taskIDs);
	}

}

?>