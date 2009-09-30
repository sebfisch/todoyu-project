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
 * Handles the Datasource for filter-widgets which belong to a task
 *
 * @package todoyu
 * @subpackage project
 *
 */
class TodoyuTaskFilterDataSource {

	/**
	 * Autocompleter function to search for taskss
	 *
	 * @param	String	$search
	 * @param	Array	$config
	 */
	public static function getTaskAutocompleteListBySearchword($search, $config = array())	{
		$data = array();

		$result = TodoyuTaskSearch::searchTask($search);

		if(count($result) > 0)	{
			foreach($result as $task)	{

				$data[$task['id']] = $task['id_project'].'.'.$task['tasknumber'].' - '.$task['title'];
			}
		}

		return $data;
	}



	/**
	 * Get autocomplete list by task filters
	 *
	 * @param	Array		$filters
	 * @return	Array
	 */
	public static function getTaskAutocompleteListByFilter(array $filters = array()) {
		// Search tasks by filter
		$taskFilter	= new TodoyuTaskFilter($filters);
		$taskIDs	= $taskFilter->getTaskIDs();

		if( sizeof($taskIDs) > 0 ) {
			// Get task details
			$fields	= 'id, title, id_project, tasknumber';
			$table	= 'ext_project_task';
			$where	= 'id IN(' . implode(',', $taskIDs) . ')';
			$order	= 'title';

			$tasks		= Todoyu::db()->getArray($fields, $table, $where, '', $order);
			$tasksAc 	= array();

			foreach($tasks as $task) {
				$tasksAc[$task['id']] = '[' . $task['id_project'] . '.' . $task['tasknumber'] . '] ' . $task['title'];
			}

		} else {
			$tasksAc	= array();
		}

		return $tasksAc;
	}



	/**
	 * Prepares the options of task-status for rendering in the widget.
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getStatusOptions(array $definitions)	{
		$options	= array();
		$statuses	= TodoyuProjectStatusManager::getProjectStatusInfos();

		foreach($statuses as $index => $status) {
			$selected = TodoyuDiv::isInList($status['index'], $definitions['value']);
			$options[$index] = array(	'label'	=> $status['label'],
										'selected'	=> $selected
										);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	/**
	 * Dynamic dateinput options
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getDynamicDateinput($definitions)	{
		$optionsArray = array(
			''					=> array('label' => Label('LLL:projectFilter.filterlabel.pleasechoose')),
			'today'				=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.today')),
			'tomorrow'			=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.tomorrow')),
			'dayaftertomorrow'	=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.dayaftertomorrow')),
			'yesterday'			=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.yesterday')),
			'daybeforeyesterday'=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.daybeforeyesterday')),
			'currentweek'		=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.currentweek')),
			'nextweek'			=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.nextweek')),
			'lastweek'			=> array('label' => Label('LLL:projectFilter.filterlabel.task.dyndateinput.lastweek')),
		);

		$optionsArray[$definitions['value']]['selected'] = true;

		$definitions['options'] = $optionsArray;

		return $definitions;
	}



	/**
	 * calculates the timestamps by dynamic type
	 *
	 * @param	String	$value
	 * @return	Array
	 */
	public static function getDynamicDateinputTimestamps($value)	{
		$currentDayOfWeek = date('w', $date) == 0 ? 7:date('w');

		$dayBeginn = 1-$currentDayOfWeek;
		$dayEnd	   = 7-$currentDayOfWeek;

		$individualStartSummand = 0;
		$individualEndSummand = 0;
		$generalDaySummand = 0;

		switch($value)	{
			case 'tomorrow':
				$generalDaySummand = 1;
				break;
			case 'dayaftertomorrow':
				$generalDaySummand = 2;
				break;
			case 'yesterday':
				$generalDaySummand = -1;
				break;
			case 'daybeforeyesterday':
				$generalDaySummand = -2;
				break;
			case 'currentweek':
				$individualStartSummand = $dayBeginn;
				$individualEndSummand	= $dayEnd;
				break;
			case 'nextweek':
				$individualStartSummand = $dayBeginn;
				$individualEndSummand	= $dayEnd;

				$generalDaySummand = 7;
				break;
			case 'lastweek':
				$individualStartSummand = $dayBeginn;
				$individualEndSummand	= $dayEnd;

				$generalDaySummand = -7;
				break;
			case 'todoay':
			default:
				// do nothing
				break;
		}

		$start	= mktime(0, 0, 0, date('n'), (date('j')+$individualStartSummand)+$generalDaySummand, date('Y'));
		$end	= mktime(23, 59, 59, date('n'), (date('j')+$individualEndSummand)+$generalDaySummand, date('Y'));

		return array('start' => $start, 'end' => $end);
	}



	/**
	 * gets the task billing types
	 *
	 * @todo move to the projectbilling module
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getTaskBillingTypes($definitions)	{
		$optionsArray = array();
		$result = Todoyu::db()->doSelect('id,title', 'ext_projectbilling_type', 'deleted = 0');

		while($option = Todoyu::db()->fetchAssoc($result))	{
			$optionsArray[$option['id']] = array('label' => Label($option['label']),
												 'selected' => TodoyuDiv::isInList($option['id'], $definitions['value'])
												 );
		}

		$definitions['options'] = $optionsArray;

		return $definitions;
	}
}
?>