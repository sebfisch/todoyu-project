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
 * Project data source
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectDataSource {

	/**
	 * Get user project roles
	 *
	 * @return	Array
	 */
	public static function getProjectUserRoles() {
		$roles	= array();

		$fields	= '*';
		$table	= 'ext_project_userrole';
		$where	= 'deleted = 0';

		$result = Todoyu::db()->doSelect($fields, $table, $where);

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$row['title']	= TodoyuLocale::labelExists($row['title']) ? Label($row['title']) : $row['title'];
			$roles[] = $row;
		}

		return $roles;
	}



	/**
	 * Get user project roles formatted as option array for the form
	 *
	 * @param	TodoyuFormElement	$field		Reference to current field
	 * @return	Array
	 */
	public static function getProjectUserRoleOptions(TodoyuFormElement $field) {
		$roles	= self::getProjectUserRoles();
		$roles	= TodoyuArray::reform($roles, array('id'=>'value','title'=>'label'), true);

		return $roles;
	}

}

?>