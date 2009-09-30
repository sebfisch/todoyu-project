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
 * Worktype manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuWorktypeManager {

	/**
	 * Managed table
	 *
	 */
	const TABLE = 'ext_project_worktype';



	/**
	 * Gets a Worktype object
	 *
	 * @param	Integer		$idWorktype		Worktype ID
	 * @return	TodoyuWorktype
	 */
	public static function getWorktype($idWorktype) {
		$idWorktype	= intval($idWorktype);

		return TodoyuCache::getRecord('TodoyuWorktype', $idWorktype);
	}



	/**
	 * Save worktype record to database
	 *
	 * @param	Array	$formData
	 * @param	String	$xmlPath
	 * @return	Integer
	 */
	public static function save(array $formData, $xmlPath) {
		$idWorktype	= intval($formData['id']);
		unset($formData['id']);

		if($idWorktype === 0)	{
			$idWorktype = self::createNewRecord();
		}

		$formData	= TodoyuFormHook::callSaveData($xmlPath, $formData, $idWorktype);

		$formData['date_update']	= NOW;

		Todoyu::db()->doUpdate(self::TABLE , 'id = '.$idWorktype, $formData);

		return $idWorktype;
	}



	/**
	 * Gets worktype records for list
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecordList(array $params = array()) {
		$worktypes = array();

		$result	= Todoyu::db()->doSelect('id, title', self::TABLE , 'deleted = 0');

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$worktypes[] = array(
				'id' 	=> $row['id'],
				'label'	=> $row['title']
			);
		}

		return $worktypes;
	}



	/**
	 * Sets deleted flag for current worktype
	 *
	 * @param	Integer	$idWorktype
	 */
	public static function delete($idWorktype)	{
		$idWorktype	= intval($idWorktype);

		$update = array(
				'deleted'	=> 1
		);

		Todoyu::db()->doUpdate(self::TABLE , 'id = '.$idWorktype, $update);
	}



	/**
	 * Creates an empty worktype record
	 *
	 * @return	Integer
	 */
	protected static function createNewRecord()	{
		$insertArray = array(
			'id_user_create'	=> userid(),
			'date_create'		=> NOW,
			'deleted'			=> 0
		);

		return Todoyu::db()->doInsert(self::TABLE , $insertArray);
	}


}

?>