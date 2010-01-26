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
 * Project filter. Compile and execute active filters.
 * All project filters are defined in this class
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectFilter extends TodoyuFilterBase {

	/**
	 * Initialize project filter with active filters
	 *
	 * @param	Array		$activeFilters
	 */
	public function __construct(array $activeFilters = array(), $conjunction = 'AND') {
		parent::__construct('PROJECT', 'ext_project_project', $activeFilters, $conjunction);
	}



	/**
	 * Get IDs of the project which match to all the filters
	 *
	 * @param	String		$orderBy
	 * @param	String		$limit
	 * @param	Bool		$showDeleted
	 * @return	Array
	 */
	public function getProjectIDs($orderBy = '', $limit = '', $showDeleted = false) {
		return parent::getItemIDs($orderBy, $limit, $showDeleted);
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
		$searchInFields	= array('ext_project_project.title', 'ext_project_project.description', 'ext_user_company.title', 'ext_user_company.shortname');

		$tables	= array('ext_project_project', 'ext_user_company');
		$where	= 'ext_project_project.id_company	= ext_user_company.id';

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
	 * @param	Bool		$negate
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



//	/**
//	 * Filter projects by dynamic project role
//	 *
//	 * @param	Integer	$idProjectleader
//	 * @param	Boolean	$negate
//	 * @return	Array
//	 */
//	public static function Filter_projectrole($idUserrole, $negate = false) {
//		$idUser	= intval(idUser);
//		$idUserrole	= intval(idUserrole);
//
//		if( $idProjectleader === 0 ) {
//			return false;
//		}
//
////		$compare	= $negate ? 'NOT IN' : 'IN' ;
////
////		$tables	= array('ext_project_project');
////		$where	= '	ext_project_project.id ' . $compare . ' (
////						SELECT
////							ext_project_project.id
////						FROM
////							ext_project_project,
////							ext_project_mm_project_user,
////							ext_project_userrole
////						WHERE
////							ext_project_project.id 					= ext_project_mm_project_user.id_project AND
////							ext_project_mm_project_user.id_user		= ' . $idProjectleader . ' AND
////							ext_project_mm_project_user.id_userrole	= ext_project_userrole.id AND
////							ext_project_userrole.rolekey			= \'projectleader\'
////					)';
////
////		return array(
////			'tables'=> $tables,
////			'where'	=> $where
////		);
//	}



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
							ext_project_mm_project_user,
							ext_project_userrole
						WHERE
							ext_project_project.id 					= ext_project_mm_project_user.id_project AND
							ext_project_mm_project_user.id_user		= ' . $idProjectleader . ' AND
							ext_project_mm_project_user.id_userrole	= ext_project_userrole.id AND
							ext_project_userrole.rolekey			= \'projectleader\'
					)';

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}



	/**
	 * Filter projects by project supervisor
	 *
	 * @param	Integer	$idProjectsupervisor
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_projectsupervisor($idProjectsupervisor, $negate = false) {
		$idProjectsupervisor	= intval($idProjectsupervisor);

		if( $idProjectsupervisor === 0 ) {
			return false;
		}

		$compare	= $negate ? 'NOT IN' : 'IN' ;

		$tables	= array('ext_project_project');
		$where	= '	ext_project_project.id ' . $compare . '
					(SELECT ext_project_project.id
					 FROM ext_project_project,ext_project_mm_project_user,ext_project_userrole
					 WHERE	ext_project_project.id	= ext_project_mm_project_user.id_project
					 AND	ext_project_mm_project_user.id_user	= ' . $idProjectsupervisor . '
					 AND	ext_project_mm_project_user.id_userrole	= ext_project_userrole.id
					 AND	ext_project_userrole.rolekey = \'projectsupervisor\'
					)';

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}



	/**
	 * Filter projects by being fixed-projects
	 *
	 * @param	Boolean	$isFixed
	 * @param	Boolean	$negate
	 * @return	Array
	 */
	public static function Filter_isfixed($isFixed, $negate = false)	{
		$tables = array('ext_project_project');

		$value = $negate === true ? 0 : 1;

		$where = ' is_fixed = ' . intval($value);

		return array(
			'tables'	=> $tables,
			'where'		=> $where
		);
	}


}


?>