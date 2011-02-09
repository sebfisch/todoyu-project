<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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

		if( count($result) > 0 ) {
			foreach($result as $task) {
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
		$statuses	= TodoyuTaskStatusManager::getStatusInfos('see');

		foreach($statuses as $status) {
			$options[] = array(
				'label'		=> $status['label'],
				'value'		=> $status['index']
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
	 * Dynamic date options
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getDynamicDateOptions($definitions)	{
		$definitions['options'] = array(
			array(
				'label' => Label('projectFilter.task.dyndate.today'),
				'value'	=> 'today'
			),
			array(
				'label' => Label('projectFilter.task.dyndate.tomorrow'),
				'value'	=> 'tomorrow'
			),
			array(
				'label' => Label('projectFilter.task.dyndate.dayaftertomorrow'),
				'value'	=> 'dayaftertomorrow'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndate.yesterday'),
				'value'	=> 'yesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndate.daybeforeyesterday'),
				'value'	=> 'daybeforeyesterday'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndate.currentweek'),
				'value'	=> 'currentweek'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndate.nextweek'),
				'value'	=> 'nextweek'
			),
			array(
				'label' => Label('LLL:projectFilter.task.dyndate.lastweek'),
				'value'	=> 'lastweek'
			)
		);

		return $definitions;
	}



	/**
	 * Calculates timestamps by dynamic type
	 *
	 * @param	String		$dateRangeKey
	 * @return	Integer
	 */
	public static function getDynamicDateTimestamp($dateRangeKey, $negate = false)	{
		$todayStart	= TodoyuTime::getStartOfDay();
		$todayEnd	= TodoyuTime::getEndOfDay();
		$date		= $negate ? $todayEnd : $todayStart;

		switch( $dateRangeKey ) {
			case 'tomorrow':
				$date += TodoyuTime::SECONDS_DAY;
				break;

			case 'dayaftertomorrow':
				$date += TodoyuTime::SECONDS_DAY * 2;

				break;

			case 'yesterday':
				$date -= TodoyuTime::SECONDS_DAY;
				break;

			case 'daybeforeyesterday':
				$date -= TodoyuTime::SECONDS_DAY * 2;
				break;

			case 'currentweek':
				$weekRange	= TodoyuTime::getWeekRange(NOW);
				$date		= $negate ? $weekRange['end'] : $weekRange['start'];
				break;

			case 'nextweek':
				$weekRange	= TodoyuTime::getWeekRange(NOW + TodoyuTime::SECONDS_WEEK);
				$date		= $negate ? $weekRange['end'] : $weekRange['start'];
				break;

			case 'lastweek':
				$weekRange	= TodoyuTime::getWeekRange(NOW - TodoyuTime::SECONDS_WEEK);
				$date		= $negate ? $weekRange['end'] : $weekRange['start'];
				break;

			case 'todoay':
			default:
				break;
		}

		return $date;
	}

}

?>