<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Project filters for companies.
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectCompanyFilter extends TodoyuSearchFilterBase implements TodoyuFilterInterface {

	/**
	 * Default table for database requests
	 *
	 * @var	String
	 */
	const TABLE = 'ext_contact_company';

	/**
	 * Init filter object
	 *
	 * @param	Array		$activeFilters
	 * @param	String		$conjunction
	 * @param	Array		$sorting
	 */
	public function __construct(array $activeFilters = array(), $conjunction = 'AND', array $sorting = array()) {
		parent::__construct('COMPANY', self::TABLE, $activeFilters, $conjunction, $sorting);
	}



	/**
	 * Filter condition: companies with projects with given status
	 *
	 * @param	String			$value			Comma-separated statuses
	 * @param	Boolean			$negate
	 * @return	Array|Boolean					Query parts / false if no statuses given
	 */
	public function Filter_projectstatus($value, $negate = false) {
		$statuses	= TodoyuArray::intExplode(',', $value, true, true);

		if( sizeof($statuses) === 0 ) {
			return false;
		}

		$tables	= array(
			self::TABLE,
			'ext_project_project'
		);

		$where	= TodoyuSql::buildInListQueryPart($statuses, 'ext_project_project.status', true, $negate)
				. ' AND ext_project_project.id_company = ' . self::TABLE . '.id ';

		$join	= array(self::TABLE . '.id = ext_project_project.id_company');

		return array(
			'where'	=> $where,
			'tables'=> $tables,
			'join'	=> $join
		);
	}



	/**
	 * Filter for companies with task created before/after date
	 *
	 * @param	String		$date		DD/MM/YYYY
	 * @param	Boolean		$negate
	 * @return	Array|Boolean
	 */
	public function Filter_dateCreateTask($date, $negate = false) {
		$queryParts	= false;

		if( !empty($date) ) {
			$timestamp	= TodoyuTime::parseDate($date);
			$queryParts	= self::getTaskDateCreateQueryParts($timestamp, $negate);
		}

		return $queryParts;
	}



	/**
	 * @param	String	$dateRangeKey	"today", "tomorrow", ...
	 * @param	Boolean	$negate
	 * @return	Array|Boolean
	 */
	public function Filter_dateCreateTaskDynamic($dateRangeKey, $negate = false) {
		$queryParts	= false;

		if( !empty($dateRangeKey) ) {
			$timestamp	= TodoyuSearchFilterHelper::getDynamicDateTimestamp($dateRangeKey, $negate);
			$queryParts	= self::getTaskDateCreateQueryParts($timestamp, $negate);
		}

		return $queryParts;
	}



	/**
	 * @param	Integer		$timestamp
	 * @param	Boolean		$negate
	 */
	private static function getTaskDateCreateQueryParts($timestamp, $negate = false) {
		$tables	= array(
			self::TABLE,
			'ext_project_project',
			'ext_project_task',
		);

		$info	= TodoyuSearchFilterHelper::getTimeAndLogicForDate($timestamp, $negate);

		$where	= ' 	ext_project_task.deleted 		= 0 '
				. ' AND ext_project_task.date_create ' . $info['logic'] . ' ' . $info['timestamp']
				. ' AND ext_project_project.id			= ext_project_task.id_project '
				. ' AND ext_project_project.deleted		= 0'
				. ' AND ext_project_project.id_company	= ' . self::TABLE . '.id';

		return array(
			'tables'=> $tables,
			'where'	=> $where
		);
	}

}

?>