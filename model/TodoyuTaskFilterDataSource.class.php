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
 * Handles the Datasource for filter-widgets which belong to a task
 *
 * @package		Todoyu
 * @subpackage	project
 */
class TodoyuTaskFilterDataSource {

	/**
	 * AutoCompleter function to search for tasks
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
	 * Get autoComplete suggestions list by task filters
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
				$tasksAc[$task['id']] = $task['id_project'] . '.' . $task['tasknumber'] . ' ' . $task['title'];
			}

		} else {
			$tasksAc	= array();
		}

		return $tasksAc;
	}



	/**
	 * Prepare options of task-status for rendering in widget.
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getStatusOptions(array $definitions)	{
		$options	= array();
		$statuses	= TodoyuTaskStatusManager::getStatusInfos();
//		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);
		// @todo	check - needed?

		foreach($statuses as $status) {
			$options[] = array(
				'label'		=> $status['label'],
				'value'		=> $status['index'],
//				'selected'	=> in_array($status['index'], $selected)
			);
		}

		$definitions['options'] = $options;

		return $definitions;
	}



	/**
	 * Get projectRole as options for widget
	 *
	 * @param	Array		$definitions
	 * @return	Array
	 */
	public static function getProjectroleOptionDefinitions(array $definitions) {
		$projectroles	= TodoyuRoleManager::getAllRoles();
//		$selected		= TodoyuArray::intExplode(',', $definitions['value'], true, true);
//		@todo	- check selection needed?
		$reform			= array(
			'title'	=> 'label',
			'id'	=> 'value'
		);

		$definitions['options'] = TodoyuArray::reform($projectroles, $reform);

		return $definitions;
	}



	/**
	 * Get options config array of workTypes
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getWorktypeOptions(array $definitions) {
		$options	= array();
		$workTypes	= TodoyuWorktypeManager::getAllWorktypes();

//		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);
//		@todo check - selection needed? it's currently unused 

		foreach($workTypes as $workType) {
			$options[] = array(
				'label'		=> $workType['title'],
				'value'		=> $workType['id']
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
	 * Dynamic dateInput options
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
	 * Calculates timestamps by dynamic type
	 *
	 * @param	String	$value
	 * @return	Array
	 */
	public static function getDynamicDateinputTimestamps($value)	{
		$currentDayOfWeek = date('w') == 0 ? 7 : date('w');

		$dayBegin	= 1 - $currentDayOfWeek;
		$dayEnd		= 7 - $currentDayOfWeek;

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
				$individualStartSummand = $dayBegin;
				$individualEndSummand	= $dayEnd;
				break;

			case 'nextweek':
				$individualStartSummand = $dayBegin;
				$individualEndSummand	= $dayEnd;

				$generalDaySummand = 7;
				break;

			case 'lastweek':
				$individualStartSummand = $dayBegin;
				$individualEndSummand	= $dayEnd;

				$generalDaySummand = -7;
				break;

			case 'todoay':
			default:
					// Do nothing
				break;
		}

		$start	= mktime(0, 0, 0, date('n'), (date('j') + $individualStartSummand) + $generalDaySummand, date('Y'));
		$end	= mktime(23, 59, 59, date('n'), (date('j') + $individualEndSummand) + $generalDaySummand, date('Y'));

 		return array(
			'start' => $start,
			'end' => $end
		);
	}

}

?>