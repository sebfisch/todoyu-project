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
 * Project search renderer
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectSearchRenderer {

	/**
	 * Render search result based on given filter conditions
	 * This function is registered as typerenderer for the search panel
	 *
	 * @param	Integer		$idFilterset
	 * @param	Bool		$useConditions
	 * @param	Array		$filterConditions
	 * @param	String		$conjunction
	 * @return	String
	 */
	public static function renderSearchResults($idFilterset = 0, array $conditions = array(), $conjunction = 'AND')	{
		$idFilterset= intval($idFilterset);
		$projectIDs	= TodoyuProjectManager::getProjectIDsByFilter($idFilterset, $conditions, $conjunction);
		$content	= '';
		$tmpl		= 'ext/project/view/project-search-list.tmpl';
		$data		= array(
			'projects'	=> array()
		);

		foreach($projectIDs as $idProject)	{
			$data['projects'][$idProject] = TodoyuProjectRenderer::renderProjectHeader($idProject);
		}

		return render($tmpl, $data);
	}

}

?>