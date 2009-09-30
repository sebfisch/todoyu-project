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
 * Task search
 * Delivers search results for task to the search engine
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuTaskSearch implements TodoyuSearchEngineIf {

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

			// If keyword is a tasknumber, directly get the task
		if( sizeof($find) === 1 ) {
			if( TodoyuTaskManager::isTasknumber($find[0], true) ) {
				$idTask	= TodoyuTaskManager::getTaskIDByTaskNumber($find[0]);

				return array($idTask);
			}
		}

//
//			// Handle task number
//		foreach($find as $index => $keyword) {
//				// if there is a point in the keyword, it could be a tasknumber
//			if( strpos($keyword, '.') !== false ) {
//					// Remove the concatinated value
//				unset($find[$index]);
//					// Split the parts and add them to the find array
//				$parts = explode('.', $keyword);
//				foreach($parts as $part) {
//					$find[] = $part;
//				}
//			}
//		}

		$table	= 'ext_project_task';
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
						c.shortname as customer';
			$table	= '	ext_project_task t,
						ext_project_project p,
						ext_user_customer c';
			$where	= '	t.id_project = p.id AND
						p.id_customer= c.id AND
						t.id IN(' . implode(',', $taskIDs) . ')';
			$order	= '	t.date_create DESC';

			$tasks	= Todoyu::db()->getArray($fields, $table, $where, '', $order);

			foreach($tasks as $task) {
				$suggestions[] = array(
					'labelTitle'=> $task['id_project'] . '.' . $task['tasknumber'] . ': ' . $task['title'],
					'labelInfo'	=> $task['project'] . ', ' . $task['customer'],
					'title'		=> $task['id_project'] . '.' . $task['tasknumber'] . ': ' . $task['title'],
					'onclick'	=> 'location.href=\'?ext=project&amp;project=' . $task['id_project'] . '&amp;task=' . $task['id'] . '#task-' . $task['id'] . '\'');
			}
		}

		return $suggestions;
	}



	/**
	 * Get task search results
	 *
	 * @param	Array	$find
	 * @param	Array	$ignore
	 * @param	String	$limit
	 */
	public static function getResults(array $find, array $ignore = array(), $limit = 20) {

	}



	/**
	 * Search task by title, description, project and tasknumber
	 *
	 * if there is a . in the sword explode it and search by id_project (1st param of explode) and tasknumber (2nd param of explode)
	 *
	 * else create a normal like search
	 *
	 * @param	String	$sword
	 * @return	Array
	 */
	public static function searchTask($sword)	{
		$fields = array('id', 'title', 'description', 'id_project', 'tasknumber');
		$table = 'ext_project_task';

		if(strstr($sword, '.'))	{
			list($project, $taskNumber) = explode('.', $sword);
			$where = 'id_project = '.intval($project).' AND tasknumber = '.intval($taskNumber);
		} else {
			$searchWords = TodoyuDiv::trimExplode(' ', $sword, true);

			$where = Todoyu::db()->buildLikeQuery($searchWords, $fields);
		}

		if($where)	{
			$where.= ' AND deleted = 0';
		}

		$tasks = Todoyu::db()->getArray(implode(',', $fields), $table, $where);

		return $tasks;
	}
}

?>