<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013, snowflake productions GmbH, Switzerland
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
 * Project renderer for portal
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPortalRenderer {

	/**
	 * Get label of todo tab in portal
	 *
	 * @param	Boolean		$count
	 * @return	String
	 */
	public static function getTodoTabLabel($count = true) {
		$label		= Todoyu::Label('project.ext.portal.tab.todos');

		if( $count ) {
			$numTasks	= TodoyuProjectPortalManager::getTodoCount();
			$label		=  $label . ' (' . $numTasks . ')';
		}

		return $label;
	}



	/**
	 * Get content of todo tab in portal
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public static function renderTodoTabContent(array $params = array()) {
		$taskIDs= TodoyuProjectPortalManager::getTodoTaskIDs();

		TodoyuHeader::sendTodoyuHeader('items', sizeof($taskIDs));

		return TodoyuProjectTaskRenderer::renderTaskListing($taskIDs);
	}

}

?>