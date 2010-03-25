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
 * Controller for project contextmenu
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectContextmenuActionController extends TodoyuActionController {

	/**
	 * Initialize project context menu controller: restrict rights
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		restrict('project', 'general:use');
	}



	/**
	 * Get task (and container) related contextmenu as JSON
	 *
	 * @param	Array		$params
	 */
	public function taskAction(array $params) {
		$idTask		= intval($params['task']);
		$contextMenu= new TodoyuContextMenu('Task', $idTask);

		$contextMenu->printJSON();
	}



	/**
	 * Get project related contextmenu as JSON
	 *
	 * @param	Array		$params
	 */
	public function projectAction(array $params) {
		$idProject	= intval($params['project']);
		$contextMenu= new TodoyuContextMenu('Project', $idProject);

		$contextMenu->printJSON();
	}

}

?>