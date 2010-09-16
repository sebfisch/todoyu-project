<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * handles the datasource for the filter widgets
 *
 * @package		Todoyu
 * @subpackage	project
 */
class TodoyuProjectFilterDataSource {

	/**
	 * Search for projects by given search string from the auto-completion
	 *
	 * @param	String	$search
	 * @param	Array	$conf
	 * @return	Array				array (id => label)
	 */
	public static function autocompleteProjects($input, array $formData = array(), $name = '')	{
		$data = array();

		$keywords		= TodoyuArray::trimExplode(' ', $input, true);
		$projectIDs		= TodoyuProjectSearch::searchProjects($keywords, array(), 30);
		
		if( sizeof($projectIDs) > 0 ) {
			$fields		= '	p.id,
							p.title,
							c.shortname as company';
			$tables		= ' ext_project_project p,
							ext_contact_company c';
			$where		= ' p.id_company = c.id AND
							p.id IN(' . implode(',', $projectIDs) . ') ';

			$projects	= Todoyu::db()->getArray($fields, $tables, $where, '', '', 30);

			foreach($projects as $project) {
				if( TodoyuProjectRights::isSeeAllowed($project['id']) ) {
					$data[$project['id']] = $project['company'] .' - ' . $project['title'];
				}
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



	/**
	 * Prepares the options of project-status for rendering in the widget.
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getStatusOptions(array $definitions)	{
		$options	= array();
		$statuses	= TodoyuProjectStatusManager::getStatusInfos();
		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);

		foreach($statuses as $status) {
			$options[] = array(
				'label'		=> $status['label'],
				'value'		=> $status['index'],
			);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	/**
	 * Dynamic date options
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getDynamicDateOptions($definitions)	{
		$definitions['options'] = array(
			array(
				'label' => Label('projectFilter.project.dyndate.today'),
				'value'	=> 'today'
			),
			array(
				'label' => Label('projectFilter.project.dyndate.tomorrow'),
				'value'	=> 'tomorrow'
			),
			array(
				'label' => Label('projectFilter.project.dyndate.dayaftertomorrow'),
				'value'	=> 'dayaftertomorrow'
			),
			array(
				'label' => Label('LLL:projectFilter.project.dyndate.yesterday'),
				'value'	=> 'yesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.project.dyndate.daybeforeyesterday'),
				'value'	=> 'daybeforeyesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.project.dyndate.currentweek'),
				'value'	=> 'currentweek'
			),
			array(
				'label' => Label('LLL:projectFilter.project.dyndate.nextweek'),
				'value'	=> 'nextweek'
			),
			array(
				'label' => Label('LLL:projectFilter.project.dyndate.lastweek'),
				'value'	=> 'lastweek'
			)
		);

		return $definitions;
	}

}

?>