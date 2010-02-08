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
 * @package Todoyu
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
				$data[$task['id']] = $task['id_project'] . '.' . $task['tasknumber'] . ' - ' . $task['title'];
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
			$format		= '%s.%s %s';

			foreach($tasks as $task) {
				$tasksAc[$task['id']] = sprintf($format, $task['id_project'], $task['tasknumber'], $task['title']);
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
		$statuses	= TodoyuProjectStatusManager::getTaskStatusInfos();
		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);

		foreach($statuses as $status) {
			$options[] = array(
				'label'		=> $status['label'],
				'value'		=> $status['index'],
				//'selected'	=> in_array($status['index'], $selected)
			);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	/**
	 * Get usergroups as options for widget
	 *
	 * @param	Array		$definitions
	 * @return	Array
	 */
	public static function getUsergroupOptions(array $definitions) {
		$options	= array();
		$groups		= TodoyuUsergroupManager::getAllUsergroups();
		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);

		foreach($groups as $group) {
			$options[] = array(
				'label'		=> $group['title'],
				'value'		=> $group['id']
			);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	public static function getWorktypeOptions(array $definitions) {
		$options	= array();
		$worktypes	= TodoyuWorktypeManager::getAllWorktypes();
		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);

		foreach($worktypes as $worktype) {
			$options[] = array(
				'label'		=> $worktype['title'],
				'value'		=> $worktype['id']
			);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	/**
	 * Get task type options for filter widget
	 *
	 * @param	Array		$definitions
	 * @return	Array
	 */
	public static function getTypeOptions(array $definitions) {
		$definitions['options'] = array(
			array(
				'label'	=> Label('task.type.task'),
				'value'	=> TASK_TYPE_TASK
			),
			array(
				'label'	=> Label('task.type.container'),
				'value'	=> TASK_TYPE_CONTAINER
			)
		);

		return $definitions;
	}



	/**
	 * Dynamic dateinput options
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getDynamicDateinput($definitions)	{
		$definitions['options'] = array(
			array(
				'label' => Label('projectFilter.task.dyndateinput.today'),
				'value'	=> 'today'
			),
			array(
				'label' => Label('projectFilter.task.dyndateinput.tomorrow'),
				'value'	=> 'tomorrow'
			),
			array(
				'label' => Label('projectFilter.task.dyndateinput.dayaftertomorrow'),
				'value'	=> 'dayaftertomorrow'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndateinput.yesterday'),
				'value'	=> 'yesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndateinput.daybeforeyesterday'),
				'value'	=> 'daybeforeyesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndateinput.currentweek'),
				'value'	=> 'currentweek'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndateinput.nextweek'),
				'value'	=> 'nextweek'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndateinput.lastweek'),
				'value'	=> 'lastweek'
			)
		);

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