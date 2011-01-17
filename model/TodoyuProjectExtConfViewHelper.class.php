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
 * View helper for project extconf
 * 
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectExtConfViewHelper {

	public static function getDefaultValueDateOptions(TodoyuFormElement $field) {

		return array(
			array(
				'label'	=> 'LLL:task.default.date.date_0',
				'value'	=> '-1'
			),
			array(
				'label'	=> 'LLL:task.default.date.date_1',
				'value'	=> '1'
			),
			array(
				'label'	=> 'LLL:task.default.date.date_2',
				'value'	=> '2'
			),
			array(
				'label'	=> 'LLL:task.default.date.date_3',
				'value'	=> '3'
			),
			array(
				'label'	=> 'LLL:task.default.date.date_7',
				'value'	=> '7'
			),
			array(
				'label'	=> 'LLL:task.default.date.date_14',
				'value'	=> '14'
			)
		);
	}



	/**
	 * Get status infos of default task 
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getDefaultValueStatusOptions(TodoyuFormElement $field) {
		return TodoyuTaskStatusManager::getStatusInfos('see');
	}

}

?>