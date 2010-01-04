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
 * handles the datasource for the filter widgets
 *
 * @package Todoyu
 * @subpackage project
 */
class TodoyuProjectFilterDataSource {

	/**
	 * search for projects by given search string from the autocompletion
	 *
	 *
	 * 	returns data as array (id => label)
	 *
	 * @param	String	$search
	 * @param	Array	$conf
	 * @return	Array
	 */
	public static function autocompleteProjects($searchString, array $conf = array())	{
		$data = array();

		$keywords	= TodoyuDiv::trimExplode(' ', $searchString, true);
		$projectIDs	= TodoyuProjectSearch::searchProjects($keywords, array(), 30);

		if( sizeof($projectIDs) > 0 ) {
			$fields		= '	p.id,
							p.title,
							c.shortname as company';
			$tables		= ' ext_project_project p,
							ext_user_company c';
			$where		= ' p.id_company = c.id AND
							p.id IN(' . implode(',', $projectIDs) . ')';

			$projects	= Todoyu::db()->getArray($fields, $tables, $where);

			foreach($projects as $project) {
				$data[$project['id']] = $project['company'] .' - ' . $project['title'];
			}
		}

		return $data;
	}



	/**
	 * Gets the label for the current Autocompletion value.
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public function getLabel($definitions)	{
		$project = new TodoyuProject($definitions['value']);

		$definitions['value_label'] = $project->getFullTitle();

		return $definitions;
	}
}

?>