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
 * Task filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskFilter extends TodoyuSearchFilterBase implements TodoyuFilterInterface {

	/**
	 * @var	String		Default table for database requests
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
	 */
	private function addRightsClauseFilter() {
			// Limit to current person
		if( ! allowed('project', 'task:seeAll') ) {
			$this->addRightsFilter('assignedPerson', personid());
		}

			// Limit to selected status
		if( ! TodoyuAuth::isAdmin() ) {
			$statuses	= implode(',', array_keys(TodoyuProjectTaskStatusManager::getStatuses('see')));
			$this->addRightsFilter('status', $statuses);

				// Limit to tasks which are in available projects
			if( ! allowed('project', 'project:seeAll') ) {
				$this->addRightsFilter('availableprojects', 0);
			}

				// Add public filter for all externals (not internal)
			if( ! Todoyu::person()->isInternal() ) {
				$this->addRightsFilter('isPublic', 1);
			}
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

		return parent::getItemIDs($sorting, $limit, false);
	}



	/**
	 * General access to the result items
	 *
	 * @param	String		$sorting
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public function getItemIDs($sorting = 'sorting', $limit = 200) {
		return $this->getTaskIDs($sorting, $limit);
	}



	/**
	 * Filter condition: tasks of given project
	 *
	 * @param	Integer	$idProject
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_project($idProject, $negate = false) {
		$idProject	= intval($idProject);
		$queryParts	= false;

		if( $idProject > 0 ) {
				// Set up query parts array
			$tables	= array(self::TABLE);
			$compare= $negate ? '!= ' : '= ';
			$where	= 'ext_project_task.id_project ' . $compare . $idProject;

			$queryParts	= array(
				'tables'	=> $tables,
				'where'		=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Get query parts for available projects filter
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_availableprojects($value, $negate = false) {
		$availableProjects	= TodoyuProjectProjectManager::getAvailableProjectsForPerson();

		if( sizeof($availableProjects) > 0 ) {
			$queryParts	= array(
				'tables'	=> array(
					'ext_project_project'
				),
				'where'	=> 'ext_project_task.id_project IN(' . implode(',', $availableProjects) . ')'
			);

		} else {
				// Add negative where. Will definitely cause an empty result
			$queryParts	= array(
				'where'	=> '0'
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks of projects of given customer
	 *
	 * @param	Integer		$idProject
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_company($idCompany, $negate = false) {
		$idCompany	= intval($idCompany);
		$queryParts	= false;

		if( $idCompany > 0 ) {
			$compare	= $negate ? '!=' : '=' ;

			$tables	= array(
				'ext_project_project'
			);
			$where	= 'ext_project_project.id_company ' . $compare . ' ' . $idCompany;
			$join	= array(
				'ext_project_task.id_project = ext_project_project.id'
			);

			return array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
			);
		}

		return $queryParts;
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
		$queryArray	= false;

		if( $idOwner !== 0 ) {
				// Set up query parts array
			$tables	= array(self::TABLE);
			$compare= $negate === true ? '!= ' : '= ';
			$where	= 'ext_project_task.id_person_owner ' . $compare . $idOwner;

			$queryArray	= array(
				'tables'	=> $tables,
				'where'		=> $where
			);
		}

		return $queryArray;
	}



	/**
	 * Filter condition: tasks where current person is owner
	 *
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_currentPersonOwner($negate = false) {
		$idPerson	= personid();

		$queryParts	= self::Filter_ownerPerson($idPerson, $negate);

		return $queryParts;
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
				self::TABLE,
				'ext_contact_mm_person_role'
			);
			$where	= 'ext_contact_mm_person_role.id_role IN(' . implode(',', $roleIDs) . ')';
			$join	= array(
				'ext_project_task.id_person_owner = ext_contact_mm_person_role.id_person'
			);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
			);
		}

		return $queryParts;
	}



	/**
	 * Filter for task number
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_tasknumber($value, $negate = false) {
		$taskNumber	= intval($value);
		$queryParts	= false;

		if( $taskNumber > 0 ) {
			$tables	= array(self::TABLE);
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
			$tables	= array(self::TABLE);
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
	 * Task full-text filter. Searches in task number, title, description
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_fulltext($value, $negate = false) {
		$value		= trim($value);
		$queryParts	= false;

		if( $value !== '' ) {
			$tables	= array(self::TABLE);
			$keyword= Todoyu::db()->escape($value);
			$where	= '(
						( 		ext_project_task.description 	LIKE \'%' . $keyword . '%\'
							OR	ext_project_task.title 			LIKE \'%' . $keyword . '%\'
						)';

			if( strpos($value, '.') !== false ) {
				list($project, $task) = TodoyuArray::intExplode('.', $value);
				$where	.= ' OR (
									  ext_project_task.id_project = ' . $project .
							 	' AND ext_project_task.tasknumber = ' . $task .
							')';
			}

			$where	 .= ')';

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Search tasks which match the value in the title or the task number
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_tasknumberortitle($value, $negate = false) {
		$taskNumber = trim($value);
		$title		= trim($value);
		$queryParts	= false;

		$whereParts	= array();

			// If task number was numeric and bigger than zero, check the task number
		if( strpos($taskNumber, '.') === false && intval($taskNumber) > 0 ) {
			$taskNumber	= intval($value);
			$whereParts[] = 'ext_project_task.tasknumber = ' . $taskNumber;
		} else if( strpos($taskNumber, '.') !== false ) {
			list($project, $task) = explode('.', $taskNumber);
			$whereParts[] = '(ext_project_task.id_project = ' . intval($project) . ' AND ext_project_task.tasknumber = ' . intval($task) . ')';
		}

			// If value was not empty, check matches in the title
		if( $title !== '' ) {
			$whereParts[] = 'ext_project_task.title LIKE \'%' . Todoyu::db()->escape($title) . '%\'';
		}

		if( sizeof($whereParts) > 0 ) {
			$where	= '(' . implode(' OR ', $whereParts) . ')';

			$queryParts	= array(
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

			$tables	= array(self::TABLE);
			$where	= 'ext_project_task.id_person_create ' . $logic . ' ' . $idPerson;

			$queryParts = array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Task created by a person which is member of one of the selected roles
	 *
	 * @param	Array		$roleIDs
	 * @param	Boolean		$negate
	 * @return	Array		Or FALSE
	 */
	public static function Filter_creatorRoles($roleIDs, $negate = false) {
		$roleIDs	= TodoyuArray::intExplode(',', $roleIDs, true, true);
		$queryParts	= false;

		if( sizeof($roleIDs) > 0 ) {
			$tables	= array(
				self::TABLE,
				'ext_contact_mm_person_role'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= 'ext_contact_mm_person_role.id_role ' . $compare . '(' . implode(',', $roleIDs) . ')';
			$join	= array(
				'ext_project_task.id_person_create = ext_contact_mm_person_role.id_person'
			);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
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
				// Set up query parts array
			$compare= $negate ? '!=' : '=';
			$where	= 'ext_project_task.id_person_assigned ' . $compare . ' ' . intval($idPerson);

			$queryParts	= array(
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
				'ext_contact_mm_person_role'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= 'ext_contact_mm_person_role.id_role ' . $compare . '(' . implode(',', $roleIDs) . ')';
			$join	= array(
				'ext_project_task.id_person_assigned = ext_contact_mm_person_role.id_person'
			);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
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
	public static function Filter_projectDescription($value = '', $negate = false) {
		$queryParts	= false;

		$string	= trim($value);

		if( strlen($string) ) {
			$string	= Todoyu::db()->escape($string);

			$tables	= array(
				'ext_project_project'
			);
			$where	=  'ext_project_project.description LIKE \'%' . $string . '%\'';
			$join	= array(
				'ext_project_task.id_project = ext_project_project.id'
			);

			$queryParts	= array(
				'where'	=> $where,
				'tables'=> $tables,
				'join'	=> $join
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: tasks of projects with given status
	 *
	 * @param	String		$statusList
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_projectstatus($statusList, $negate = false) {
		$statusList	= TodoyuArray::intExplode(',', $statusList, true, true);
		$queryParts	= false;

		if( sizeof($statusList) > 0 ) {
			$tables	= array('ext_project_project');

			$compare= $negate ? 'NOT IN' : 'IN';
			$where	=  'ext_project_project.status ' . $compare . '(' . implode(',', $statusList) . ')';
			$join	= array('ext_project_task.id_project = ext_project_project.id');

			$queryParts	= array(
				'where'	=> $where,
				'tables'=> $tables,
				'join'	=> $join
			);

		}

		return $queryParts;
	}



	/**
	 * Filters for tasks being publicly visible
	 *
	 * @todo	implement negation
	 *
	 * @param	Integer		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_isPublic($value, $negate = false) {
		$tables 	= array(self::TABLE);
		$isPublic	= $negate ? 0 : 1;
		$where		= 'ext_project_task.is_public = ' . $isPublic;

		$queryParts	= array(
			'tables'	=> $tables,
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Filter condition: Task status in given status list?
	 *
	 * @param	String		$statusList
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_status($statusList, $negate = false) {
		$queryParts	= false;
		$statusList	= TodoyuArray::intExplode(',', $statusList, true, true);

		if( sizeof($statusList) > 0 ) {
			$tables	= array(self::TABLE);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '(	ext_project_task.status ' . $compare . '(' . implode(',', $statusList) . ')
						OR	ext_project_task.type = ' . TASK_TYPE_CONTAINER . ')';

			$queryParts	= array(
				'tables'	=> $tables,
				'where'		=> $where
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
			$check	= $negate ? 0 : 1;
			$where	= '		ext_project_task.id_person_assigned	= ' . $idPerson .
					  ' AND	ext_project_task.is_acknowledged	= ' . $check;

			$queryParts	= array(
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
	 * Get only tasks whose parent matches to the value
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_parentTask($idTask, $negate = false) {
		$where	= 'ext_project_task.id_parenttask ' . ( $negate ? '!=' : '=' ) . ' ' . intval($idTask);

		$queryParts	= array(
			'where'		=> $where
		);

		return $queryParts;
	}



	/**
	 * Filter condition: date_deadline
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_deadlinedate($date, $negate = false) {
		return self::makeFilter_date('date_deadline', $date, $negate);
	}



	/**
	 * Get the dynamic deadline
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_deadlinedateDyn($value, $negate) {
		$timeStamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return self::Filter_dateDyn($timeStamps, 'date_deadline', $negate);
	}



	/**
	 * Filter condition: date_start
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_startdate($date, $negate = false) {
		return self::makeFilter_date('date_start', $date, $negate);
	}



	/**
	 * Get the dynamic startdate
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_startdateDyn($value, $negate) {
		$timeStamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return self::Filter_dateDyn($timeStamps, 'date_start', $negate);
	}



	/**
	 * Filter condition: date_end
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_enddate($date, $negate = false) {
		return self::makeFilter_date('date_end', $date, $negate);
	}



	/**
	 * get the dynamic enddate
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_enddateDyn($value, $negate = false) {
		$date = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return self::Filter_dateDyn($date, 'date_end', $negate);
	}



	/**
	 * Filter condition: date_update
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_editdate($date, $negate = false) {
		return self::makeFilter_date('date_update', $date, $negate);
	}



	/**
	 * get the dynamic edit date
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_editdateDyn($value, $negate) {
		$rangeTimestamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return self::Filter_dateDyn($rangeTimestamps, 'date_update', $negate);
	}



	/**
	 * Filter condition: date_create
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_createdate($date, $negate = false) {
		return self::makeFilter_date('date_create', $date, $negate);
	}



	/**
	 * Get the dynamic creation date (date_create)
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_createdateDyn($value, $negate) {
		$timeStamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return self::Filter_dateDyn($timeStamps, 'date_create', $negate);
	}



	/**
	 * Get the dynamic date
	 *
	 * @param	Array		$dateRange
	 * @param	String		$field
	 * @return	Array
	 */
	protected static function Filter_dateDyn($date, $field, $negation = false) {
		$date	= intval($date);
		$compare= $negation ? '>=' : '<=';
		$where 	= 'ext_project_task.' . $field . ' ' . $compare . ' ' . $date;

		return array(
			'where' 	=> $where
		);
	}



	/**
	 * Filter task by not being given ID (get all but given)
	 *
	 * @param	String		$value
	 * @param	Boolean 	$negate
	 * @return	Array
	 */
	public static function Filter_nottask($value, $negate = false) {
		$idTask	= intval($value);

		$where = 'ext_project_task.id != ' . $idTask;

		return array(
			'where' 	=> $where
		);
	}



	/**
	 * Filter by type (task / container)
	 *
	 * @param	Integer		$value		both: 0, TASK_TYPE_TASK: 1 / TASK_TYPE_CONTAINER: 2
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_type($value, $negate = false) {
		$type		= intval($value);
		$queryParts	= false;

		if( $type > 0 ) {
			$compare	= $negate ? '!=' : '=';
			$queryParts = array(
				'where'		=> 'ext_project_task.type ' . $compare . $type
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition: Task has activity
	 *
	 * @param	Array		$activityIDs
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_activity($activityIDs, $negate = false) {
		$queryParts	= false;
		$activityIDs= TodoyuArray::intExplode(',', $activityIDs);

		if( sizeof($activityIDs) !== 0 ) {
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= 'ext_project_task.id_activity ' . $compare . '(' . implode(',', $activityIDs) . ')';

			$queryParts	= array(
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition for project role
	 * The value is a combination between the project role and the person
	 *
	 * @param	String		$value		Format: PERSON:ROLE,ROLE,ROLE
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_projectrole($value, $negate = false) {
		$parts		= explode(':', $value);
		$idPerson	= intval($parts[0]);
		$roles		= TodoyuArray::intExplode(',', $parts[1]);

		$queryParts	= false;

		if( $idPerson !== 0 && sizeof($roles) > 0 ) {
			$tables	= array(
				'ext_project_mm_project_person'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '		ext_project_mm_project_person.id_person	= ' . $idPerson .
					  ' AND ext_project_mm_project_person.id_role ' . $compare . '(' . implode(',', $roles) . ')';
			$join	= array(
				'ext_project_task.id_project = ext_project_mm_project_person.id_project'
			);

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition for all subTasks (recursive)
	 *
	 * @param	Integer		$value			idTask
	 * @param	Boolean		$negate
	 * @return	Array|Boolean
	 */
	public static function Filter_subtask($value, $negate = false) {
		$idTask		= intval($value);
		$queryParts	= false;

		if( $idTask !== 0 ) {
			$subTasks	= TodoyuProjectTaskManager::getAllSubTaskIDs($idTask);

			if( sizeof($subTasks) > 0 ) {
				$compare= $negate ? 'NOT IN' : 'IN';
				$where	= 'ext_project_task.id ' . $compare . '(' . implode(',', $subTasks) . ')';

				$queryParts	= array(
					'where'		=> $where
				);
			}
		}

		return $queryParts;
	}



	/**
	 * Setup query parts for task date_... fields (create, update, start, end, deadline) filter
	 *
	 * @param	String		$field
	 * @param	Integer		$date
	 * @param	Boolean		$negate
	 * @return	Boolean
	 */
	public static function makeFilter_date($field, $date, $negate = false) {
		$tables	= array(self::TABLE);
		$field	= 'ext_project_task.' . $field;

		return TodoyuSearchFilterHelper::getDateFilterQueryparts($tables, $field, $date, $negate);
	}



	/**
	 * Alias Method for TodoyuSearchFiltersetManager::Filter_filterObject for TaskFilter
	 *
	 * @param	Array		$value
	 * @param	Boolean		$negate
	 * @return	Boolean
	 */
	public static function Filter_filterObject(array $value, $negate = false) {
		return TodoyuSearchFiltersetManager::Filter_filterObject($value, $negate);
	}

}

?>