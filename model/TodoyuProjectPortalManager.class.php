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
* it under the terms of the BSC License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

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