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
 * Task search
 * Delivers search results for task to the search engine
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskSearch implements TodoyuSearchEngineIf {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_project_task';



	/**
	 * Search task which match the keywords
	 *
	 * @param	Array		$find		Keywords which must be in the task
	 * @param	Array		$ignore		Keywords which must not be in the task
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public static function searchTasks(array $find, array $ignore = array(), $limit = 200) {
		$limit	= intval($limit);

			// If keyword is a task number, directly get the task
		if( sizeof($find) === 1 ) {
			if( TodoyuTaskManager::isTasknumber($find[0], true) ) {
				$idTask	= TodoyuTaskManager::getTaskIDByTaskNumber($find[0]);

				return array($idTask);
			}
		}

			// Task full-text search
		$table	= self::TABLE;
		$fields	= array('id_project', 'tasknumber', 'description', 'title');

		return TodoyuSearch::searchTable($table, $fields, $find, $ignore, $limit);
	}



	/**
	 * Get suggestions
	 *
	 * @param	Array	$find		Array of words to search for
	 * @param	Array	$ignore		Array of words to be ignored
	 * @param	Integer	$limit
	 * @return	Array
	 */
	public static function getSuggestions(array $find, array $ignore = array(), $limit = 5) {
		$limit		= intval($limit);
		$suggestions= array();

			// Search matching projects
		$taskIDs	= self::searchTasks($find, $ignore, $limit);

		if( sizeof($taskIDs) > 0 ) {
			$fields	= '	t.id,
						t.id_project,
						t.tasknumber,
						t.title,
						p.title as project,
						c.shortname as company';
			$table	= self::TABLE . ' t,
						ext_project_project p,
						ext_contact_company c';
			$where	= '		t.id_project = p.id
						AND	p.id_company= c.id
						AND	t.id IN(' . implode(',', $taskIDs) . ')';
			$order	= '	t.date_create DESC';

			$tasks	= Todoyu::db()->getArray($fields, $table, $where, '', $order);




				// Assemble found task suggestions
			foreach($tasks as $task) {
				if( TodoyuTaskRights::isSeeAllowed($task['id']) ) {
					$suggestions[] = array(
						'labelTitle'=> $task['id_project'] . '.' . $task['tasknumber'] . ': ' . htmlentities($task['title'], ENT_QUOTES, 'UTF-8'),
						'labelInfo'	=> $task['project'] . ', ' . $task['company'],
						'title'		=> $task['id_project'] . '.' . $task['tasknumber'] . ': ' . $task['title'],
//						'onclick'	=> 'location.href=\'?ext=project&amp;project=' . $task['id_project'] . '&amp;task=' . $task['id'] . '#task-' . $task['id'] . '\''
						'onclick'	=> 'Todoyu.goToHashURL(\'?ext=project&amp;project=' . $task['id_project'] . '&amp;task=' . $task['id'] . '\', \'task-' . $task['id'] . '\')'
					);
				}
			}
		}

		return $suggestions;
	}



	/**
	 * Search task by title, description, project and task number
	 *
	 * if there is a . in the sword explode it and search by id_project (1st parameter of explode) and task number (2nd parameter of explode)
	 *
	 * else create a normal like search
	 *
	 * @param	String	$sword
	 * @return	Array
	 */
	public static function searchTask($sword)	{
		$fields	= array('id', 'title', 'description', 'id_project', 'tasknumber');
		$table	= self::TABLE;

		if( strstr($sword, '.') )	{
			list($project, $taskNumber) = explode('.', $sword);
			$where = 'id_project = '.intval($project).' AND tasknumber = '.intval($taskNumber);
		} else {
			$searchWords = TodoyuArray::trimExplode(' ', $sword, true);

			$where = Todoyu::db()->buildLikeQuery($searchWords, $fields);
		}

		if( $where )	{
			$where.= ' AND deleted = 0';
		}

		$tasks = Todoyu::db()->getArray(implode(',', $fields), $table, $where);

		return $tasks;
	}
}

?>