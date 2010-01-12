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
 * Task filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuTaskFilter extends TodoyuFilterBase {

	/**
	 * Default table
	 *
	 */
	const TABLE = 'ext_project_task';


	/**
	 * Init filter object
	 *
	 * @param	Array	$activeFilters		Active filters for request
	 */
	public function __construct(array $activeFilters = array(), $conjunction = 'AND') {
		parent::__construct('TASK', self::TABLE, $activeFilters, $conjunction);
	}



	/**
	 * Get task IDs which match to all filters
	 *
	 * @return	Array
	 */
	public function getTaskIDs($sorting = 'sorting', $limit = 100) {
		$limit	= intval($limit);
		$taskIDs= parent::getItemIDs($sorting, $limit);

		return $taskIDs;
	}



	/**
	 * Filter condition: tasks of given project
	 *
	 * @param	Integer	$idProject
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_project($idProject, $negate) {
		$idProject	= intval($idProject);

		if( $idProject === 0 ) {
				// no project given?
			$queryArray	= false;
		} else {
				// setup query parts array
			$tables	= array('ext_project_task');

			if( $negate )	{
				$where	= 'ext_project_task.id_project != ' . $idProject;
			} else {
				$where	= 'ext_project_task.id_project = ' . $idProject;
			}

			$queryArray	= array(
				'tables'	=> $tables,
				'where'		=> $where
			);
		}

		return $queryArray;
	}



	/**
	 * Filter condition: tasks of given owner
	 *
	 * @param	Integer	$idOwner
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_owner($idOwner, $negate) {
		$idOwner	= intval($idOwner);

		if( $idOwner === 0 ) {
				// no owner given?
			$queryArray	= false;
		} else {
				// set up query parts array
			$logic = ($negate === true) ? '!=':'=';
			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.id_user_owner ' . $logic . ' ' . $idOwner;

			$queryArray	= array(
				'tables'	=> $tables,
				'where'		=> $where
			);
		}

		return $queryArray;
	}



	/**
	 * Filter to check if current user is to owner
	 *
	 * @param	String		$value
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_currentUserIsOwner($value, $negate = false) {
		$idUser		= userid();

		$queryParts	= self::filter_owner($idUser, $negate);

		return $queryParts;
	}



	/**
	 * Filter for tasknumber
	 *
	 * @param	String		$value
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_tasknumber($value, $negate = false) {
		$taskNumber	= intval($value);
		$queryParts	= false;

		if( $taskNumber > 0 ) {
			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.tasknumber = ' . $taskNumber;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: task title like given string?
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_title($value, $negate = false) {
		$title		= trim($value);
		$queryParts	= false;

		if( $title !== '' ) {
			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.title LIKE \'%' . Todoyu::db()->escape($title) . '%\'';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Task fulltext filter. Searches in tasknumber, title, description
	 *
	 * @param	String		$value
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_fulltext($value, $negate = false) {
		$value		= trim($value);
		$queryParts	= false;

		if( $value !== '' ) {
			$tables	= array('ext_project_task');
			$wheres	= array();

			$wheres[] = 'ext_project_task.description 	LIKE \'%' . Todoyu::db()->escape($value) . '%\'';
			$wheres[] = 'ext_project_task.title 		LIKE \'%' . Todoyu::db()->escape($value) . '%\'';

			$taskNumber	= intval($value);
			if( $taskNumber !== 0 ) {
				$wheres[] = 'ext_project_task.tasknumber = ' . $taskNumber;
			}

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> implode(' AND ', $wheres)
			);
		}

		return $queryParts;
	}



	/**
	 * Search tasks which match the value in the title or the tasknumber
	 *
	 * @param	String		$value
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_tasknumberortitle($value, $negate = false) {
		$taskNumber = trim($value);
		$title		= trim($value);
		$queryParts	= false;

		$whereParts	= array();

			// If tasknumber was numeric and bigger than zero, check the tasknumber
		if( strpos($taskNumber, '.') === false && intval($taskNumber) > 0 ) {
			$taskNumber	= intval($value);
			$whereParts[] = 'ext_project_task.tasknumber = ' . $taskNumber;
		} else if( strpos($taskNumber, '.') !== false )	{
			list($project, $task) = explode('.', $taskNumber);
			$whereParts[] = '(ext_project_task.id_project = '.intval($project).' AND ext_project_task.tasknumber = '.intval($task).')';
		}

			// If value was not empty, check matches in the title
		if( $title !== '' ) {
			$whereParts[] = 'ext_project_task.title LIKE \'%' . Todoyu::db()->escape($title) . '%\'';
		}

		if( sizeof($whereParts) > 0 ) {
			$tables	= array('ext_project_task');
			$where	= '(' . implode(' OR ', $whereParts) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks created by given user
	 *
	 * @param	Integer	$idUser
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_userCreate($idUser, $negate = false) {
		$idUser	= intval($idUser);

		if( $idUser === 0 ) {
				// no user given?
			$queryParts	= false;
		} else {
				// set up query parts array
			$logic = ($negate === true) ? '!=':'=';

			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.id_user_create ' . $logic . ' ' . $idUser;

			$queryParts = array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks created by current user
	 *
	 *
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_currentUserIsUserCreate($value, $negate = false) {
		$idUser	= userid();

		return self::Filter_userCreate($idUser, $negate);
	}



	/**
	 * Filter condition: task creator belongs to given group?
	 *
	 * @param	Integer	$idGroup
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_userCreateGroup($idGroup, $negate) {
		$idGroup	= intval($idGroup);

		if( $idGroup === 0 ) {
				// no group given?
			$queryParts	= false;
		} else {
				// set up query parts
			$tables	= array('ext_project_task', 'ext_user_mm_user_group');
			$where	 = ' ext_project_task.id_user_create = ext_user_mm_user_group.id_user';
			$where	.= ' AND ext_user_mm_user_group.id_group = ' . $idGroup;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: task assigned to user of given group?
	 *
	 * @param	Integer	$idGroup
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_userAssignedGroup($idGroup, $negate) {
		$idGroup	= intval($idGroup);

		if( $idGroup === 0 ) {
				// no group given?
			$queryParts	= false;
		} else {
				// set up query parts array
			$tables	= array('ext_project_task', 'ext_user_mm_user_group');
			$where	 = ' ext_project_task.id_user_assigned = ext_user_mm_user_group.id_user';
			$where	.= ' AND ext_user_mm_user_group.id_group = ' . $idGroup;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks assigned to User
	 *
	 * @param	Integer	$idUser
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_userAssigned($idUser, $negate) {
		$idUser	= intval($idUser);

		if( $idUser === 0 ) {
				// no user given?
			$queryParts	= false;
		} else {
				// set up query parts array
			$tables	= ('ext_project_task');

			if( $negate )	{
				$where	= 'ext_project_task.id_user_assigned != ' . intval($idUser);
			} else {
				$where	= 'ext_project_task.id_user_assigned = ' . intval($idUser);
			}


			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks assigned to current user
	 *
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_currentUserIsAssigned($value = '', $negate) {
		$idUser		= userid();

		$queryParts	= self::Filter_userAssigned($idUser, $negate);

		return $queryParts;
	}



	/**
	 * Filter condition: Project description like given filter value?
	 *
	 * @param	Array	$filter
	 * @return	Array
	 */
	public static function Filter_projectDescription(array $filter) {
		$string	= Todoyu::db()->escape($filter['value']);

		$where	 = ' ext_project_project.description LIKE \'%' . $string . '%\'';
		$where	.= ' AND ext_project_task.id_project = ext_project_project.id';

		$queryParts	= array(
			'where'		=> $where,
			'tables'	=> array('ext_project_project')
		);

		return $queryParts;
	}



	/**
	 * Public filter
	 *
	 * @param	Integer	$value
	 * @param	boolean	$negate
	 * @return	Array
	 */
	public static function Filter_isPublic($value, $negate = false)	{
		$tables 	= array('ext_project_task');
		$isPublic	= $negate ? 1 : 0;
		$where		= 'ext_project_task.is_public = ' . $isPublic;

		$queryParts	= array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Filter condition: Task status in given stati list?
	 *
	 * @param	Array		$status
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_status($statuses, $negate = false) {
		$statuses	= TodoyuDiv::intExplode(',', $statuses, true, true);

			// If no status selected, get all allowed
		if( sizeof($statuses) === 0 ) {
			$statuses = array_keys(TodoyuProjectStatusManager::getTaskStatuses('see'));
		}

		$tables	= array('ext_project_task');

		if( $negate )	{
			$where	= 'ext_project_task.status NOT IN(' . implode(',', $statuses) . ')';
		} else {
			$where	= 'ext_project_task.status IN(' . implode(',', $statuses) . ')';
		}

		return array(
			'tables'	=> $tables,
			'where'		=> $where
		);
	}



	/**
	 * Filter condition: Task acknowledged by given user?
	 *
	 * @param	Integer	$idUser
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_acknowledged($idUser, $negate) {
		$tables	= array('ext_project_task');

		$value = ($negate === true) ? 0 : 1;

		$where	 = ' ext_project_task.id_user_assigned	= ' . intval($idUser);
		$where	.= ' AND ext_project_task.is_acknowledged = ' . $value;

		$queryParts	= array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Filter condition: task acknowledged by current user
	 *
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_currentUserHasAcknowledged($value, $negate) {
		$idUser		= TodoyuAuth::getUserID();
		$queryParts	= self::filter_acknowledged($idUser, $negate);

		return $queryParts;
	}



	/**
	 * Get only tasks whichs parent matches to the value
	 *
	 * @param	Integer		$idTask
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_parentTask($idTask, $negate = false) {
		$compare= $negate ? '!=' : '=';
		$tables	= array('ext_project_task');
		$where	= 'ext_project_task.id_parenttask ' . $compare . ' ' . intval($idTask);

		$queryParts	= array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}

	/**
	 * Gets Task by requested deadline date
	 *
	 * negate true: from
	 * negate false: to
	 *
	 * from is start-of-day and to is end-of-day
	 *
	 * @param	String	$value
	 * @param	boolean	$negate
	 * @return	Array
	 */
	public static function Filter_deadline($value, $negate = false)	{
		$queryParts	= false;
		$value		= trim($value);

		if( $value !== '' ) {
			list($logic, $timestampToCheck) = self::getTimestampAndLogicForSimpleDateInputs($value, $negate);

			$queryParts = array(
				'tables'	=> array('ext_project_task'),
				'where'		=> 'ext_project_task.date_deadline ' . $logic . ' ' . $timestampToCheck
			);
		}

		return $queryParts;
	}



	/**
	 * Get the dynamic deadline
	 *
	 * @param	String	$value
	 * @param	boolean	$negate
	 * @return	Array
	 */
	public static function Filter_deadlineDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_deadline');
	}



	/**
	 * Gets Task by requested start date
	 *
	 * negate true: from
	 * negate false: to
	 *
	 * from is start-of-day and to is end-of-day
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_startdate($value, $negate)	{
		$tables = array('ext_project_task');

		list($logic, $timestampToCheck) = self::getTimestampAndLogicForSimpleDateInputs($value, $negate);

		$tables = array('ext_project_task');
		$where = 'ext_project_task.date_start ' . $logic . ' ' . $timestampToCheck;

		$queryParts = array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Get the dynamic startdate
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_startdateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_start');
	}



	/**
	 * Gets Task by requested end date
	 *
	 * negate true: from
	 * negate false: to
	 *
	 * from is start-of-day and to is end-of-day
	 *
	 * @param	String	$value
	 * @param	Boolean $negate
	 * @return	Array
	 */
	public static function Filter_enddate($value, $negate = false)	{
		$tables = array('ext_project_task');

		list($logic, $timestampToCheck) = self::getTimestampAndLogicForSimpleDateInputs($value, $negate);

		$tables = array('ext_project_task');
		$where = 'ext_project_task.date_end ' . $logic . ' ' . $timestampToCheck;

		$queryParts = array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * get the dynamic enddate
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_enddateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_end');
	}



	/**
	 * get finishdate
	 *
	 * @param	Integer		$value
	 * @param	Bool		$negate
	 */
	public static function Filter_finishdate($value, $negate)	{
		$tables = array('ext_project_task');

		list($logic, $timestampToCheck) = self::getTimestampAndLogicForSimpleDateInputs($value, $negate);

		$tables = array('ext_project_task');
		$where = 'ext_project_task.date_finish ' . $logic . ' ' . $timestampToCheck;

		$queryParts = array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Get the dynamic date
	 *
	 * @param	Array	$timeStamps
	 * @param	String	$field
	 * @return	Array
	 */
	protected static function Filter_dateDyn(array $timeStamps, $field)	{
		$tables = array('ext_project_task');
		$where = '(		ext_project_task.' . $field . ' > ' . intval($timeStamps['start']) . '
					AND	ext_project_task.' . $field . ' < ' . intval($timeStamps['end']) . ' )';

		return array('tables' => $tables, 'where' => $where);
	}



	/**
	 * Filter task by not being given ID (get all but given)
	 *
	 * @param	String	$value
	 * @param	Boolean $negate
	 * @return	Array
	 */
	public static function Filter_nottask($value, $negate = false) {
		$idTask	= intval($value);

		$tables = array('ext_project_task');
		$where = 'ext_project_task.id != ' . $idTask;

		return array('tables' => $tables, 'where' => $where);
	}



	/**
	 * Filter task by field ext_projectbilling_type
	 *
	 * @todo: move to the projectbilling module
	 *
	 * @param	String	$value	commaseparated
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_billingType($value, $negate = false)	{
		if( $value )	{
			$value = explode(',', $value);

			$value = TodoyuArray::intval($value, true, true);
			$value = implode(',', $value);

			$tables = array('ext_project_task');

			if( $negate )	{
				$where = 'ext_project_task.ext_projectbilling_type NOT IN (' . $value . ')';
			} else {
				$where = 'ext_project_task.ext_projectbilling_type IN (' . $value . ')';
			}


			return array('tables' => $tables, 'where' => $where);
		}
	}



	/**
	 * Returns timestamp and logic for dateinput querys
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function getTimestampAndLogicForSimpleDateInputs($value, $negate = false)	{
		$value = !is_numeric($value) ? TodoyuTime::parseDate($value) : $value;

		if( $negate )	{
			$timestampToCheck = mktime(0, 0, 0, date('n', $value), date('d', $value), date('Y', $value));
			$logic = '>=';
		} else {
			$timestampToCheck = mktime(23, 59, 59, date('n', $value), date('d', $value), date('Y', $value));
			$logic = '<=';
		}

		return array($logic, $timestampToCheck);
	}

}

?>