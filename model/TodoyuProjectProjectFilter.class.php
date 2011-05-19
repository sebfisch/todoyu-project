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
 * Project filter. Compile and execute active filters.
 * All project filters are defined in this class
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectProjectFilter extends TodoyuSearchFilterBase implements TodoyuFilterInterface {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_project_project';



	/**
	 * Initialize project filter with active filters
	 *
	 * @param	Array		$activeFilters
	 */
	public function __construct(array $activeFilters = array(), $conjunction = 'AND') {
		parent::__construct('PROJECT', 'ext_project_project', $activeFilters, $conjunction);

		$this->addRightsClauseFilter();
	}



	/**
	 * Add rights clause for projects
	 */
	private function addRightsClauseFilter() {
			// Add rights clause
		$this->addRightsFilter('rights', '');

			// Add status filter
		if( ! TodoyuAuth::isAdmin() ) {
			if( count(TodoyuProjectProjectStatusManager::getStatuses()) > 0 ) {
				$status	= array_keys(TodoyuProjectProjectStatusManager::getStatuses());
				$this->addRightsFilter('status', $status);
			} else {
				$this->addRightsFilter('Not', 0);
			}
		}
	}



	/**
	 * Get IDs of the project which match to all the filters
	 *
	 * @param	String		$orderBy
	 * @param	String		$limit
	 * @param	Boolean		$showDeleted
	 * @return	Array
	 */
	public function getProjectIDs($sorting = '', $limit = '') {
		return parent::getItemIDs($sorting, $limit);
	}



	/**
	 * General items function for anonymous access
	 *
	 * @param	String		$sorting
	 * @param	Integer		$limit
	 * @return	Array
	 */
	public function getItemIDs($sorting = '', $limit = '') {
		return $this->getProjectIDs($sorting, $limit);
	}



	/**
	 * Project rights clause. Limit output by person rights
	 * If person is not admin or can see all project, limit projects to assigned ones
	 *
	 * @param	String		$value			IGNORED
	 * @param	Boolean		$negate			IGNORED
	 */
	public function Filter_rights($value, $negate = false) {
		$queryParts	= false;

		if( ! TodoyuAuth::isAdmin() && ! Todoyu::allowed('project', 'project:seeAll') ) {
			$tables	= array(
				'ext_project_mm_project_person'
			);
			$where	= 'ext_project_mm_project_person.id_person	= ' . TodoyuAuth::getPersonID();
			$join	= array(
				'ext_project_project.id	= ext_project_mm_project_person.id_project'
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
	 * Filter search words full-text, optionally negated
	 *
	 * @param	String	$searchWords
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public function Filter_fulltext($searchWords, $negate = false) {
		$searchWords= trim($searchWords);
		$searchWords= TodoyuArray::trimExplode(' ', $searchWords);
		$queryParts	= false;

		if( sizeof($searchWords) > 0 ) {
			$searchInFields	= array(
				'ext_project_project.id',
				'ext_project_project.title',
				'ext_project_project.description',
				'ext_contact_company.title',
				'ext_contact_company.shortname'
			);

			$tables	= array(
				'ext_contact_company'
			);
			$where	= Todoyu::db()->buildLikeQuery($searchWords, $searchInFields);
			$join	= array(
				'ext_project_project.id_company	= ext_contact_company.id'
			);

			$queryParts = array(
				'tables'=> $tables,
				'where'	=> $where,
				'join'	=> $join
			);
		}

		return $queryParts;
	}



//	/**
//	 * Filter for locked projects, optionally negated
//	 *
//	 * @todo	implement negation
//	 *
//	 * @param	Boolean	$locked
//	 * @param	Boolean	$negate
//	 * @return	Array
//	 */
//	public function Filter_locked($locked, $negate = false) {
//		$tables	= array('system_lock');
//
//		if( $negate === false ) {
//			$where	= 'system_lock.table = \'ext_project_project\'';
//			$join	= array('ext_project_project.id	= system_lock.id_record');
//		} else {
//			$where	= ' ext_project_project.id NOT IN(
//							SELECT system_lock.id_record FROM system_lock WHERE system_lock.table = \'ext_project_project\'
//						)';
//			$join	= array();
//		}
//
//		$queryParts = array(
//			'tables'=> $tables,
//			'where'	=> $where,
//			'join'	=> $join
//		);
//
//		return $queryParts;
//	}



	/**
	 * Filter projects by status
	 *
	 * @param	Mixed	$status
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public function Filter_status($value, $negate = false) {
		$status		= is_array($value) ? TodoyuArray::intval($value, true, true) : TodoyuArray::intExplode(',', $value, true, true);
		$queryParts	= false;

		if( sizeof($status) > 0 ) {
			$compare	= $negate ? 'NOT IN' : 'IN' ;

			$queryParts	= array(
				'where'	=> 'ext_project_project.status ' . $compare . '(' . implode(',', $status) . ')'
			);
		}

		return $queryParts;
	}



	/**
	 * Filter projects by title
	 *
	 * @param	String	$title
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public function Filter_title($title, $negate = false) {
		$title		= trim($title);
		$queryParts	= false;

		if( $title !== '' ) {
			$titleParts	= explode(' ', $title);

			$where	= Todoyu::db()->buildLikeQuery($titleParts, array('ext_project_project.title'), $negate);

			$queryParts = array(
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter projects by (customer) company
	 *
	 * @param	Integer		$idCompany
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_company($idCompany, $negate = false) {
		$idCompany	= intval($idCompany);
		$queryParts	= false;

		if( $idCompany > 0 ) {
			$compare	= $negate ? '!=' : '=' ;

			$where	= 'ext_project_project.id_company ' . $compare . ' ' . $idCompany;

			$queryParts = array(
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter condition for projectrole
	 * The value is a combination between the projectroles and the selected person
	 *
	 * @param	String		$value		Format: PERSON:ROLE,ROLE,ROLE
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_projectrole($value, $negate = false) {
		$parts		= explode(':', $value);
		$idPerson	= intval($parts[0]);
		$roles		= TodoyuArray::intExplode(',', $parts[1]);

		$queryParts	= false;

		if( $idPerson !== 0 && sizeof($roles) > 0 ) {
				// This double subquery if here for performance reasons (don't optimize it!)
			$subQuery	= '	SELECT
								id_project
							FROM (
								SELECT
									id_project
								FROM
									ext_project_mm_project_person
								WHERE
										id_person	= ' . $idPerson .
								' AND	id_role IN (' . implode(',', $roles) . ')
								GROUP BY
									id_project
					  		) as x';
			$compare	= $negate ? 'NOT IN' : 'IN';
			$where		= ' ext_project_project.id ' . $compare . ' (' . $subQuery . ')';

			$queryParts	= array(
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Prepare projectrole filter widget: get available projectroles for selector
	 *
	 * @param	Array	$definitions
	 * @return	Array
	 */
	public function prepareDataForProjectroleWidget(array $definitions) {
		$projectroles	= TodoyuProjectProjectroleManager::getProjectroles(true);
		$reform			= array(
			'id'	=> 'value',
			'title'	=> 'label'
		);

		$definitions['options']	= TodoyuArray::reform($projectroles, $reform);

			// Prepare separate values
		$values	= explode(':', $definitions['value']);
		$definitions['valuePerson'] 		= intval($values[0]);
		$definitions['valuePersonLabel']	= TodoyuContactPersonManager::getLabel($values[0]);
		$definitions['valueProjectroles']	= TodoyuArray::intExplode(',', $values[1], true, true);

			// Add JS config
		$definitions['specialConfig'] = json_encode(array(
			'acOptions' => array(
				'afterUpdateElement' => 'Todoyu.Ext.project.Filter.onProjectrolePersonAcSelect'
			)
		));

		return $definitions;
	}



	/**
	 * Filter condition: date_start
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_startdate($date, $negate = false) {
		return $this->makeFilter_date('date_start', $date, $negate);
	}



	/**
	 * Filter condition: date_end
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_enddate($date, $negate = false) {
		return $this->makeFilter_date('date_end', $date, $negate);
	}



	/**
	 * Filter condition: deadline
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_deadline($date, $negate = false) {
		return $this->makeFilter_date('date_deadline', $date, $negate);
	}



	/**
	 * Filter condition: date_create
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_createdate($date, $negate = false) {
		return $this->makeFilter_date('date_create', $date, $negate);
	}



	/**
	 * get the dynamic create date
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_createdateDyn($value, $negate) {
		$timeStamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return $this->Filter_dateDyn($timeStamps, 'date_create', $negate);
	}



	/**
	 * Filter condition: date_update
	 *
	 * @param	String		$date
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_editdate($date, $negate = false) {
		return $this->makeFilter_date('date_update', $date, $negate);
	}



	/**
	 * get the dynamic edit date
	 *
	 * @param	String		$value
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public function Filter_editdateDyn($value, $negate) {
		$timeStamps = TodoyuProjectTaskFilterDataSource::getDynamicDateTimestamp($value, $negate);

		return $this->Filter_dateDyn($timeStamps, 'date_update', $negate);
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

		$where	= 'ext_project_project.' . $field . ' ' . $compare . ' ' . $date;

		return array(
			'where' 	=> $where
		);
	}



	/**
	 * Prepare date based filter widget for given field
	 *
	 * @param	String		$field
	 * @param	Integer		$date
	 * @param	Boolean		$negate
	 * @return	Boolean
	 */
	public function makeFilter_date($field, $date, $negate = false) {
		$tables	= array(self::TABLE);
		$field	= self::TABLE . '.' . $field;

		return TodoyuSearchFilterHelper::getDateFilterQueryparts($tables, $field, $date, $negate);
	}



	/**
	 * Set filtering WHERE clause falsified (e.g. if no project status is allowed) to avoid mySql errors
	 *
	 * @return	Array
	 */
	public function Filter_Not() {
		return array(
			'where' => '0'
		);
	}



	/**
	 * Filter by tasks from the project which match a Filter condition from task-filter
	 *
	 * @param	String		$value
	 * @return	Array
	 */
	public function Filter_taskFilter($value, $negate = false) {
		$taskFilter	= new TodoyuProjectTaskFilter();

		$filterSets	= TodoyuArray::intExplode(',', $value, true, true);

		foreach( $filterSets as $filterSet) {
			$filterObject	= $taskFilter->Filter_filterSet($filterSet);

			$compare	= $negate ? ' NOT IN ' : ' IN ';

			TodoyuDebug::printInFirebug($filterObject);

			$whereArray[]	= 'ext_project_project.id ' . $compare . '(
								SELECT id_project FROM ' . implode(',', $filterObject['tables']) .
							   ' WHERE ' . $filterObject['where'] . ')';
		}

		if( is_array($whereArray) ) {
			$where	= implode(' AND ', $whereArray);
		}

		return array(
			'where'	=> $where
		);
	}
}

?>