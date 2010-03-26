<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Task filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskFilter extends TodoyuFilterBase implements TodoyuFilterInterface {

	/**
	 * Default table for database requests
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
	 * Add rights clause to limit view to the persons rights
	 *
	 */
	private function addRightsClauseFilter() {
			// Limit to current person
		if( ! allowed('project', 'task:seeAll') ) {
			$this->addExtraFilter('assignedPerson', personid());
		}

			// Limit to selected status
		if( ! TodoyuAuth::isAdmin() ) {
			$statuses	= implode(',', array_keys(TodoyuTaskStatusManager::getStatuses('see')));
			$this->addExtraFilter('status', $statuses);
		}

			// Add public filter for all externals (not internal)
		if( ! Todoyu::person()->isInternal() ) {
			$this->addExtraFilter('isPublic', 1);
		}
	}



	/**
	 * Get task IDs which match to all filters
	 *
	 * @param	String		$sorting		Force sorting column
	 * @param	Integer		$limit			Limit result items
	 * @return	Array
	 */
	public function getTaskIDs($sorting = 'sorting', $limit = '') {
		$this->addRightsClauseFilter();

		return parent::getItemIDs($sorting, $limit);
	}



	/**
	 * General access to the result items
	 *
	 * @param	String		$sorting
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public function getItemIDs($sorting = 'sorting', $limit = 300) {
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
	 * Filter condition: tasks where person is owner
	 *
	 * @param	Integer		$idOwner
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_ownerPerson($idOwner, $negate = false) {
		$idOwner	= intval($idOwner);

		if( $idOwner === 0 ) {
				// no owner given?
			$queryArray	= false;
		} else {
				// set up query parts array
			$logic = ($negate === true) ? '!=':'=';
			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.id_person_owner ' . $logic . ' ' . $idOwner;

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
	 * @param	Array		$roleIDs		Selected roles
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_ownerRoles($roleIDs, $negate = false) {
		$roleIDs	= TodoyuArray::intExplode(',', $roleIDs, true, true);
		$queryParts	= false;

		if( sizeof($roleIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$where	= ' ext_project_task.id_person_owner = ext_contact_mm_person_role.id_person AND
						ext_contact_mm_person_role.id_role IN(' . implode(',', $roleIDs) . ')';

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
			$tables	= array(
				'ext_project_task'
			);
			$keyword= Todoyu::db()->escape($value);
			$where	= '	ext_project_task.description 	LIKE \'%' . $keyword . '%\' OR
						ext_project_task.title 			LIKE \'%' . $keyword . '%\'';

			if( strpos($value, '.') !== false ) {
				list($project, $task) = TodoyuArray::intExplode('.', $value);
				$where	.= ' OR (
								ext_project_task.id_project = ' . $project . ' AND
								ext_project_task.tasknumber = ' . $task .
							')';
			}

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
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
	 * Filter condition: tasks created by person
	 *
	 * @param	Integer		$idPerson
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_creatorPerson($idPerson, $negate = false) {
		$idPerson	= intval($idPerson);
		$queryParts	= false;

		if( $idPerson !== 0 ) {
			$logic = ($negate === true) ? '!=':'=';

			$tables	= array('ext_project_task');
			$where	= 'ext_project_task.id_person_create ' . $logic . ' ' . $idPerson;

			$queryParts = array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}


	/**
	 * Filter condition: Task created by an person which is member of one of the selected roles
	 *
	 * @param	Array		$roleIDs
	 * @param	Bool		$negate
	 * @return	Array		Or FALSE
	 */
	public static function Filter_creatorRoles($roleIDs, $negate = false) {
		$roleIDs	= TodoyuArray::intExplode(',', $roleIDs, true, true);
		$queryParts	= false;

		if( sizeof($roleIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= ' ext_project_task.id_person_create = ext_contact_mm_person_role.id_person AND
						ext_contact_mm_person_role.id_role ' . $compare . '(' . implode(',', $roleIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks assigned to person
	 *
	 * @param	Integer		$idPerson
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_assignedPerson($idPerson, $negate = false) {
		$queryParts	= false;
		$idPerson	= intval($idPerson);

		if( $idPerson !== 0 ) {
				// set up query parts array
			$tables	= array('ext_project_task');
			$compare= $negate ? '!=' : '=';
			$where	= 'ext_project_task.id_person_assigned ' . $compare . ' ' . intval($idPerson);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: task assigned to person of a role?
	 *
	 * @param	Array		$roleIDs
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_assignedRoles($roleIDs, $negate = false) {
		$roleIDs	= TodoyuArray::intExplode(',', $roleIDs, true, true);
		$queryParts	= false;

		if( sizeof($roleIDs) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_contact_mm_person_role'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= ' ext_project_task.id_person_assigned = ext_contact_mm_person_role.id_person AND
						ext_contact_mm_person_role.id_role ' . $compare . '(' . implode(',', $roleIDs) . ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks assigned to current person
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_currentPersonAssigned($value = '', $negate = false) {
		$idPerson	= personid();

		$queryParts	= self::Filter_assignedPerson($idPerson, $negate);

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
		$isPublic	= $negate ? 0 : 1;
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
	 * Filter condition: Task acknowledged by person?
	 *
	 * @param	Integer		$idPerson
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_acknowledged($idPerson, $negate = false) {
		$queryParts	= false;
		$idPerson	= intval($idPerson);

		if( $idPerson !== 0 ) {
			$tables	= array(
				'ext_project_task'
			);
			$check	= $negate ? 0 : 1;
			$where	= '	ext_project_task.id_person_assigned	= ' . $idPerson . ' AND
						ext_project_task.is_acknowledged	= ' . $check;

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: task acknowledged by person
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_currentPersonHasAcknowledged($value, $negate) {
		$idPerson	= personid();
		$queryParts	= self::Filter_acknowledged($idPerson, $negate);

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
	 * @param	Integer		$value			both: 0, TASK_TYPE_TASK: 1 / TASK_TYPE_CONTAINER: 2
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_type($value, $negate = false) {
		$type		= intval($value);
		$queryParts	= false;

		if( $type > 0 ) {
			$queryParts['tables'] 	= array('ext_project_task');
			$queryParts['where']	= 'ext_project_task.type ' . ( $negate ? '!=' : '=' ) . $type;
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
	 * The value is a combination between the projectrole and the person
	 *
	 * @param	String		$value		Format: PERSON:ROLE,ROLE,ROLE
	 * @param	Bool		$negate
	 * @return	Array
	 */
	public static function Filter_projectrole($value, $negate = false) {
		$parts		= explode(':', $value);
		$idPerson	= intval($parts[0]);
		$roles		= TodoyuArray::intExplode(',', $parts[1]);

		$queryParts	= false;

		if( $idPerson !== 0 && sizeof($roles) > 0 ) {
			$tables	= array(
				'ext_project_task',
				'ext_project_mm_project_person'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '	ext_project_task.id_project			= ext_project_mm_project_person.id_project AND
						ext_project_mm_project_person.id_person	= ' . $idPerson . ' AND
						ext_project_mm_project_person.id_role ' . $compare . '(' . implode(',', $roles) . ')';

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

		if( $timestamp !== 0 ) {
			$info	= self::getTimeAndLogicForDate($timestamp, $negate);

			$queryParts = array(
				'tables'=> array(
					'ext_project_task'
				),
				'where'	=> 'ext_project_task.' . $field . ' ' . $info['logic'] . ' ' . $info['timestamp']
			);
		}

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