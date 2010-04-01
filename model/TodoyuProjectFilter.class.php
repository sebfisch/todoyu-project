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
 * Project filter. Compile and execute active filters.
 * All project filters are defined in this class
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectFilter extends TodoyuFilterBase implements TodoyuFilterInterface {

	/**
	 * Initialize project filter with active filters
	 *
	 * @param	Array		$activeFilters
	 */
	public function __construct(array $activeFilters = array(), $conjunction = 'AND') {
		parent::__construct('PROJECT', 'ext_project_project', $activeFilters, $conjunction);
	}



	/**
	 * Add rights clause for projects
	 *
	 */
	private function addRightsClauseFilter() {
			// Add rights clause
		$this->activeFilters[] = array(
			'filter'	=> 'rights',
			'value'		=> ''
		);

			// Add status filter
		if( ! TodoyuAuth::isAdmin() ) {
			$statuses	= implode(',', array_keys(TodoyuProjectStatusManager::getStatuses()));
			$this->addExtraFilter('status', $statuses);
		}
	}



	/**
	 * Get IDs of the project which match to all the filters
	 *
	 * @param	String		$orderBy
	 * @param	String		$limit
	 * @param	Bool		$showDeleted
	 * @return	Array
	 */
	public function getProjectIDs($sorting = '', $limit = '') {
		$this->addRightsClauseFilter();

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
	 * @param	Bool		$negate			IGNORED
	 */
	public static function Filter_rights($value, $negate = false) {
		$queryParts	= false;

		if( ! TodoyuAuth::isAdmin() && ! allowed('project', 'project:seeAll') ) {
			$tables	= array(
				'ext_project_project',
				'ext_project_mm_project_person'
			);
			$where	= '	ext_project_project.id					= ext_project_mm_project_person.id_project AND
						ext_project_mm_project_person.id_person	= ' . TodoyuAuth::getPersonID();

			$queryParts	= array(
				'tables'=> $tables,
				'where'	=> $where
			);
		}

		return $queryParts;
	}



	/**
	 * Filter search words fulltext, optionally negated
	 *
	 * @param	String	$searchWords
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_fulltext($searchWords, $negate = false) {
		$searchWords	= TodoyuArray::trimExplode(' ', $searchWords);
		$searchInFields	= array('ext_project_project.title', 'ext_project_project.description', 'ext_contact_company.title', 'ext_contact_company.shortname');

		$tables	= array('ext_project_project', 'ext_contact_company');
		$where	= 'ext_project_project.id_company	= ext_contact_company.id';

		$where .= ' AND ' . Todoyu::db()->buildLikeQuery($searchWords, $searchInFields);

		return array(
			'tables'	=> $tables,
			'where'		=> $where
		);
	}



	/**
	 * Filter projects by status
	 *
	 * @param	Mixed	$status
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_status($value, $negate = false) {
		$status		= is_array($value) ? TodoyuArray::intval($value, true, true) : TodoyuArray::intExplode(',', $value, true, true);
		$queryParts	= false;

		if( sizeof($status) > 0 ) {
			$compare	= $negate ? 'NOT IN' : 'IN' ;

			$queryParts	= array(
				'tables'=> array('ext_project_project'),
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
	public static function Filter_title($title, $negate = false) {
		$title		= trim($title);

		if( $title === '' ) {
			return false;
		}

		$titleParts	= explode(' ', $title);

		$tables	= array('ext_project_project');
		$where	= Todoyu::db()->buildLikeQuery($titleParts, array('ext_project_project.title'), $negate);

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}



	/**
	 * Filter projects by (customer) company
	 *
	 * @param	Integer		$idCompany
	 * @param	Boolean		$negate
	 * @return	Array
	 */
	public static function Filter_company($idCompany, $negate = false) {
		$idCompany	= intval($idCompany);

		if( $idCompany === 0 ) {
			return false;
		}

		$compare	= $negate ? '!=' : '=' ;

		$tables	= array('ext_project_project');
		$where	= 'ext_project_project.id_company ' . $compare . ' ' . $idCompany;

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}



	/**
	 * Filter projects by project leader
	 *
	 * @param	Integer	$idProjectleader
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_projectleader($idProjectleader, $negate = false) {
		$idProjectleader	= intval($idProjectleader);

		if( $idProjectleader === 0 ) {
			return false;
		}

		$compare	= $negate ? 'NOT IN' : 'IN' ;

		$tables	= array('ext_project_project');
		$where	= '	ext_project_project.id ' . $compare . ' (
						SELECT
							ext_project_project.id
						FROM
							ext_project_project,
							ext_project_mm_project_person,
							ext_project_role
						WHERE
							ext_project_project.id 					= ext_project_mm_project_person.id_project AND
							ext_project_mm_project_person.id_person	= ' . $idProjectleader . ' AND
							ext_project_mm_project_person.id_role	= ext_project_role.id AND
							ext_project_role.rolekey				= \'projectleader\'
					)';

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}



	/**
	 * Filter condition for projectrole
	 * The value is a combination between the projectroles and the selected person
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
				'ext_project_project',
				'ext_project_mm_project_person'
			);
			$compare= $negate ? 'NOT IN' : 'IN';
			$where	= '	ext_project_project.id				= ext_project_mm_project_person.id_project AND
						ext_project_mm_project_person.id_person	= ' . $idPerson . ' AND
						ext_project_mm_project_person.id_role ' . $compare . '(' . implode(',', $roles) . ')';

			$queryParts	= array(
				'tables'=> $tables,
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
	public static function prepareDataForProjectroleWidget(array $definitions) {
		$projectroles	= TodoyuProjectroleManager::getProjectroles(true);
		$reform			= array(
			'id'	=> 'value',
			'title'	=> 'label'
		);

		$definitions['options']	= TodoyuArray::reform($projectroles, $reform);

			// Prepare seperate values
		$values	= explode(':', $definitions['value']);
		$definitions['valuePerson'] 		= intval($values[0]);
		$definitions['valuePersonLabel']	= TodoyuPersonManager::getLabel($values[0]);
		$definitions['valueProjectroles']	= TodoyuArray::intExplode(',', $values[1], true, true);

			// Add JS config
		$definitions['specialConfig'] = json_encode(array(
			'acOptions' => array(
				'afterUpdateElement' => 'Todoyu.Ext.project.Filter.onProjectrolePersonAcSelect'
			)
		));

		return $definitions;
	}

}


?>