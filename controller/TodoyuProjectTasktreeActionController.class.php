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
 * Tasktree action controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTasktreeActionController extends TodoyuActionController {

	public function init(array $params) {
		restrict('project', 'general:area');
	}

	/**
	 * Update tasktree in project view
	 *
	 * @param 	Array		$params
	 * @return	String
	 */
	public function updateAction(array $params) {
		$idProject	= intval($params['project']);
		$filter		= $params['filter'];

			// If a filter is submitted
		if( ! is_null($filter) ) {
			TodoyuProjectManager::updateProjectTreeFilters($filter['name'], $filter['value']);
		}

		return TodoyuProjectRenderer::renderProjectTaskTree($idProject);
	}

}

?>