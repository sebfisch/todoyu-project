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
 * Userrole manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuUserroleManager {

	/**
	 * Managed table
	 *
	 */
	const TABLE = 'ext_project_userrole';



	/**
	 * Get user role
	 *
	 * @param	Integer		$idUserrole		Userrole ID
	 * @return	TodoyuUserrole
	 */
	public static function getUserrole($idUserrole) {
		$idUserrole	= intval($idUserrole);

		return TodoyuCache::getRecord('TodoyuUserrole', $idUserrole);
	}



	/**
	 * Saves userrole record
	 *
	 */
	public static function save(array $formData, $xmlPath) {
		$idUserrole	= intval($formData['id']);
		unset($formData['id']);

		if($idUserrole === 0)	{
			$idUserrole = self::createNewRecord();
		}

		$formData	= TodoyuFormHook::callSaveData($xmlPath, $formData, $idUserrole);

		Todoyu::db()->doUpdate(self::TABLE , 'id = '.$idUserrole, $formData);

		return $idUserrole;
	}



	/**
	 * Sets deleted flag for current worktype
	 *
	 * @param	Integer	$idWorktype
	 */
	public static function delete($idUserrole)	{
		$idUserrole	= intval($idUserrole);

		$update = array(
				'deleted'	=> 1
		);

		Todoyu::db()->doUpdate(self::TABLE , 'id = '.$idUserrole, $update);
	}



	/**
	 * Get list of existing userrole records
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecordList(array $params = array()) {
		$userroles = array();

		$result	= Todoyu::db()->doSelect('id, title, rolekey', self::TABLE , 'deleted = 0');

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$userroles[] = array(
				'id' 	=> $row['id'],
				'label'	=> $row['title']
			);
		}

		return $userroles;
	}



	/**
	 * Creates an empty worktype record
	 *
	 * @return	Integer
	 */
	protected static function createNewRecord()	{
		$insertArray = array(
			'deleted'			=> 0
		);

		return Todoyu::db()->doInsert(self::TABLE , $insertArray);
	}
}
?>