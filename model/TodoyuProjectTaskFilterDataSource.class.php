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
 * Handles the data source for filter-widgets which belong to a task
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskFilterDataSource {

	/**
	 * Get autocomplete values for task
	 *
	 * @param	String		$input
	 * @param	Array		$formData
	 * @param	String		$name
	 * @return	Array
	 */
	public static function autocompleteTasks($input, array $formData = array(), $name = '') {
		$filters	= array(
			array(
				'filter'=> 'tasknumberortitle',
				'value'	=> $input
			)
		);

		return TodoyuProjectTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);
	}



	/**
	 * Gets the label for the current autocompletion value.
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public function getLabel(array $definitions) {
		$idTask	= intval($definitions['value']);


		if( $idTask !== 0 ) {
			$task	= TodoyuProjectTaskManager::getTask($idTask);

			$definitions['value_label'] = $task->getTitleWithTaskNumber();
		} else {
			$definitions['value_label'] = '';
		}

		return $definitions;
	}



	/**
	 * AutoCompleter function to search for tasks
	 *
	 * @param	String	$search
	 * @param	Array	$config
	 * @return	Array
	 */
	public static function getTaskAutocompleteListBySearchword($search, $config = array()) {
		$data = array();

		$result = TodoyuProjectTaskSearch::searchTask($search);

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
		$taskFilter	= new TodoyuProjectTaskFilter($filters);
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
	public static function getStatusOptions(array $definitions) {
		$options	= array();
		$statuses	= TodoyuProjectTaskStatusManager::getStatusInfos('see');

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
		$reformConfig			= array(
			'title'	=> 'label',
			'id'	=> 'value'
		);
		$definitions['options'] = TodoyuArray::reform($projectroles, $reformConfig);

		return $definitions;
	}



	/**
	 * Get options config array of activity
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public static function getActivityOptions(array $definitions) {
		$options	= array();
		$activities	= TodoyuProjectActivityManager::getAllActivities();

//		$selected	= TodoyuArray::intExplode(',', $definitions['value'], true, true);
//		@todo check - selection needed? it's currently unused

		foreach($activities as $activity) {
			$options[] = array(
				'label'	=> $activity['title'],
				'value'	=> $activity['id']
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
				'label'	=> Todoyu::Label('project.task.type.task'),
				'value'	=> TASK_TYPE_TASK
			),
			array(
				'label'	=> Todoyu::Label('project.task.type.container'),
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
	public static function getDynamicDateOptions($definitions) {
		$definitions['options'] = array(
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.today'),
				'value'	=> 'today'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.tomorrow'),
				'value'	=> 'tomorrow'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.dayaftertomorrow'),
				'value'	=> 'dayaftertomorrow'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.yesterday'),
				'value'	=> 'yesterday'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.daybeforeyesterday'),
				'value'	=> 'daybeforeyesterday'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.currentweek'),
				'value'	=> 'currentweek'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.nextweek'),
				'value'	=> 'nextweek'
			),
			array(
				'label' => Todoyu::Label('project.filter.task.dyndate.lastweek'),
				'value'	=> 'lastweek'
			)
		);

		return $definitions;
	}



	/**
	 * Calculates timestamps by dynamic type
	 *
	 * @param	String		$dateRangeKey
	 * @param	Boolean		$negate
	 * @return	Integer
	 */
	public static function getDynamicDateTimestamp($dateRangeKey, $negate = false) {
		$todayStart	= TodoyuTime::getStartOfDay();
		$todayEnd	= TodoyuTime::getEndOfDay();
		$date		= $negate ? $todayStart : $todayEnd;

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
				$date		= $negate ? $weekRange['start'] : $weekRange['end'] ;
				break;

			case 'nextweek':
				$weekRange	= TodoyuTime::getWeekRange(NOW + TodoyuTime::SECONDS_WEEK);
				$date		= $negate ? $weekRange['start'] : $weekRange['end'] ;
				break;

			case 'lastweek':
				$weekRange	= TodoyuTime::getWeekRange(NOW - TodoyuTime::SECONDS_WEEK);
				$date		= $negate ? $weekRange['start'] : $weekRange['end'] ;
				break;

			case 'todoay':
			default:
				break;
		}

		return $date;
	}

}

?>