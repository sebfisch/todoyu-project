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
class TodoyuTaskFilter extends TodoyuFilterBase implements TodoyuFilterInterface {

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


	public function getItemIDs($sorting = 'sorting', $limit = 100) {
		return $this->getTaskIDs($sorting, $limit);
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
	public static function Filter_ownerUser($idOwner, $negate = false) {
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

		return self::Filter_ownerUser($idUser, $negate);
	}



	/**
	 * Filter condition: tasks of given owner
	 *
	 * @param	Array		$groupIDs		Selected groups
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_ownerGroups($groupIDs, $negate = false) {
		$groupIDs	= TodoyuArray::intExplode(',', $groupIDs, true, true);
		$queryParts	= false;

		if( sizeof($groupIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$where	= ' ext_project_task.id_user_owner = ext_contact_mm_person_role.id_user AND
						ext_contact_mm_person_role.id_group IN(' . implode(',', $groupIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

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
			$tables	= array(
				'ext_project_task'
			);
			$compare= $negate ? 'NOT LIKE' : 'LIKE';
			$where	= 'ext_project_task.title ' . $compare . ' \'%' . Todoyu::db()->escape($title) . '%\'';

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
	public static function Filter_creatorUser($idUser, $negate = false) {
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
	 * Filter condition: Task created by an user which is member of one of the selected groups
	 *
	 * @param	Array		$groupIDs
	 * @param	Bool		$negate
	 * @return	Array		Or FALSE
	 */
	public static function Filter_creatorGroups($groupIDs, $negate = false) {
		$groupIDs	= TodoyuArray::intExplode(',', $groupIDs, true, true);
		$queryParts	= false;

		if( sizeof($groupIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= ' ext_project_task.id_user_create = ext_contact_mm_person_role.id_user AND
						ext_contact_mm_person_role.id_group ' . $compare . '(' . implode(',', $groupIDs) . ')';

			$queryParts	= array(
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

		return self::Filter_creatorUser($idUser, $negate);
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
			$tables	= array('ext_project_task', 'ext_contact_mm_person_role');
			$where	 = ' ext_project_task.id_user_create = ext_contact_mm_person_role.id_user';
			$where	.= ' AND ext_contact_mm_person_role.id_group = ' . $idGroup;

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
	public static function Filter_assignedUser($idUser, $negate) {
		$idUser	= intval($idUser);

		if( $idUser === 0 ) {
				// no user given?
			$queryParts	= false;
		} else {
				// set up query parts array
			$tables	= array('ext_project_task');

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
	 * Filter condition: task assigned to user of given group?
	 *
	 * @param	Integer	$idGroup
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_assignedGroups($groupIDs, $negate = false) {
		$groupIDs	= TodoyuArray::intExplode(',', $groupIDs, true, true);
		$queryParts	= false;

		if( sizeof($groupIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$where	= ' ext_project_task.id_user_assigned = ext_contact_mm_person_role.id_user AND
						ext_contact_mm_person_role.id_group IN(' . implode(',', $groupIDs) . ')';

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
	public static function Filter_currentUserIsAssigned($value = '', $negate = false) {
		$idUser		= userid();

		$queryParts	= self::Filter_assignedUser($idUser, $negate);

		return $queryParts;
	}



	/**
	 * Filter condition: Project description like given filter value?
	 *
	 * @param	Array	$filter
	 * @return	Array
	 * @todo	Recheck this function
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
		$queryParts	= false;
		$statuses	= TodoyuArray::intExplode(',', $statuses, true, true);

		if( sizeof($statuses) > 0 ) {
			$tables	= array(
				'ext_project_task'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '(ext_project_task.status ' . $compare . '(' . implode(',', $statuses) . ') OR
						ext_project_task.type = ' . TASK_TYPE_CONTAINER . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Task acknowledged by given user?
	 *
	 * @param	Integer	$idUser
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_acknowledged($idUser, $negate = false) {
		$queryParts	= false;
		$idUser		= intval($idUser);

		if( $idUser !== 0 ) {
			$tables	= array(
				'ext_project_task'
			);
			$check	= $negate ? 0 : 1;
			$where	= '	ext_project_task.id_user_assigned	= ' . $idUser . ' AND
						ext_project_task.is_acknowledged	= ' . $check;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

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
	 * Filter condition: date_deadline
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_deadlinedate($date, $negate = false)	{
		return self::makeFilter_date('date_deadline', $date, $negate);
	}



	/**
	 * Get the dynamic deadline
	 *
	 * @param	String	$value
	 * @param	boolean	$negate
	 * @return	Array
	 */
	public static function Filter_deadlinedateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_deadline');
	}



	/**
	 * Filter condition: date_start
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_startdate($date, $negate = false)	{
		return self::makeFilter_date('date_start', $date, $negate);
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
	 * Filter condition: date_end
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_enddate($date, $negate = false)	{
		return self::makeFilter_date('date_end', $date, $negate);
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
	 * Filter condition: date_finish
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_finishdate($date, $negate)	{
		return self::makeFilter_date('date_finish', $date, $negate);
	}



	/**
	 * get the dynamic finish date
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_finishdateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_finish');
	}



	/**
	 * Filter condition: date_update
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_editdate($date, $negate = false)	{
		return self::makeFilter_date('date_update', $date, $negate);
	}



	/**
	 * get the dynamic edit date
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_editdateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_update');
	}


	/**
	 * Filter condition: date_create
	 *
	 * @param	String		$date
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_createdate($date, $negate = false)	{
		return self::makeFilter_date('date_create', $date, $negate);
	}



	/**
	 * get the dynamic edit date
	 *
	 * @param	String	$value
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_createdateDyn($value, $negate)	{
		$timeStamps = TodoyuTaskFilterDataSource::getDynamicDateinputTimestamps($value);

		return self::Filter_dateDyn($timeStamps, 'date_update');
	}





	/**
	 * Get the dynamic date
	 *
	 * @param	Array	$timeStamps
	 * @param	String	$field
	 * @return	Array
	 */
	protected static function Filter_dateDyn(array $timeStamps, $field)	{
		$timeStamps	= TodoyuArray::intval($timeStamps);
		$tables 	= array(
			'ext_project_task'
		);
		$where 		= 'ext_project_task.' . $field . ' BETWEEN ' . $timeStamps['start'] . ' AND ' . $timeStamps['end'];

		return array(
			'tables'=> $tables,
			'where' => $where
		);
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

		return array(
			'tables'	=> $tables,
			'where' 	=> $where
		);
	}



	/**
	 * Filter by type (task/container)
	 *
	 * @param	Integer		$value
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_type($value, $negate = false) {
		$type		= intval($value);
		$queryParts	= false;

		if( $type > 0 ) {
			$queryParts['tables'] 	= array('ext_project_task');
			$queryParts['where']	= 'ext_project_task.type ' . ($negate?'!=':'=') . $type;
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Task has worktype
	 *
	 * @param	Array		$worktypeIDs
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_worktype($worktypeIDs, $negate = false) {
		$queryParts	= false;
		$worktypeIDs= TodoyuArray::intExplode(',', $worktypeIDs);

		if( sizeof($worktypeIDs) !== 0 ) {
			$tables	= array(
				'ext_project_task'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= 'ext_project_task.id_worktype ' . $compare . '(' . implode(',', $worktypeIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition for projectrole
	 * The value is a combination between the userroles and the selected user
	 *
	 * @param	String		$value		Format: USER:ROLE,ROLE,ROLE
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_projectrole($value, $negate = false) {
		$parts	= explode(':', $value);
		$idUser	= intval($parts[0]);
		$roles	= TodoyuArray::intExplode(',', $parts[1]);

		$queryParts	= false;

		if( $idUser !== 0 && sizeof($roles) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_project_mm_project_user'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '	ext_project_task.id_project			= ext_project_mm_project_user.id_project AND
						ext_project_mm_project_user.id_user	= ' . $idUser . ' AND
						ext_project_mm_project_user.id_userrole ' . $compare . '(' . implode(',', $roles) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	public static function makeFilter_date($field, $date, $negate = false) {
		$queryParts	= false;
		$timestamp	= TodoyuTime::parseDate($date);

		TodoyuDebug::printInFirebug($date, 'date');

		if( $timestamp !== 0 ) {
			$info	= self::getTimeAndLogicForDate($timestamp, $negate);

			$queryParts = array(
				'tables'=> array(
					'ext_project_task'
				),
				'where'	=> 'ext_project_task.' . $field . ' ' . $info['logic'] . ' ' . $info['timestamp']
			);
		}

		TodoyuDebug::printInFirebug($queryParts);

		return $queryParts;
	}



	/**
	 * Returns timestamp and logic for dateinput querys
	 *
	 * @param	Integer		$timestamp
	 * @param	Boolean		$negate
	 * @return	Array		[timestamp,logic]
	 */
	public static function getTimeAndLogicForDate($timestamp, $negate = false)	{
		$timestamp	= intval($timestamp);

		if( $negate ) {
			$info	= array(
				'timestamp'	=> TodoyuTime::getStartOfDay($timestamp),
				'logic'		=> '>='
			);
		} else {
			$info	= array(
				'timestamp'	=> TodoyuTime::getEndOfDay($timestamp),
				'logic'		=> '<='
			);
		}

		return $info;
	}

}

?>