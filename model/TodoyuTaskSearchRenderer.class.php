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

class TodoyuTaskSearchRenderer {

	/**
	 * Render task search results
	 *
	 * @param	Integer	$idFilterset
	 * @param	Array	$conditions
	 * @param	String	$conjunction
	 * @return	String
	 */
	public static function renderResults($idFilterset = 0, array $conditions = array(), $conjunction = 'AND') {
		$idFilterset	= intval($idFilterset);

		$taskIDs = TodoyuTaskManager::getTaskIDsByFilter($idFilterset, $conditions, $conjunction);
		
		return self::renderTaskList($taskIDs);
	}



	/**
	 * Render task list
	 *
	 * @param	Array	$taskIDs
	 * @return	String
	 */
	public static function renderTaskList(array $taskIDs) {
		$content= '';

		foreach($taskIDs as $idTask) {
			$content .= TodoyuPortalRenderer::renderTask($idTask);
		}
		
		$tmpl	= 'ext/portal/view/tasklist.tmpl';
		$data	= array(
			'tasks'	=> $content,
		);
		
		return render($tmpl, $data);
	}

}

?>