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
	 * Get user label containing also the user's role label
	 *
	 * @param	Integer	$userID
	 * @param	Integer $idProject
	 * @param	Integer	$idUserrole
	 * @return	String
	 */
	public static function getUserLabel($idUser, $idProject, $idUserrole = 0) {
		$idUser		= intval($idUser);
		$idProject	= intval($idProject);
		$idUserrole	= intval($idUserrole);

		$label	= TodoyuUserManager::getLabel($idUser);

		if ( $idUserrole == 0 ) {
			$label	.= ' - ' . TodoyuProjectManager::getUserRoleLabel($idUser, $idProject);
		} else {
			$label	.= ' - ' . self::getUserroleLabel($idUserrole);
		}

		return  $label;
	}



	/**
	 * Get label of given userrole
	 *
	 * @param	Integer	$idUserrole
	 * @return	String
	 */
	public static function getUserroleLabel($idUserrole) {
		$idUserrole	= intval($idUserrole);
		$label		= '';

		if ( $idUserrole !== 0 ) {
			$userrole	= self::getUserrole($idUserrole);
			$label		= $userrole->getTitle();
		}

		return $label;
	}



	/**
	 * Get user role
	 *
	 * @param	Integer			$idUserrole		Userrole ID
	 * @return	TodoyuUserrole
	 */
	public static function getUserrole($idUserrole) {
		$idUserrole	= intval($idUserrole);

		return TodoyuCache::getRecord('TodoyuUserrole', $idUserrole);
	}



	/**
	 * Get all visible userroles
	 *
	 * @return	Array
	 */
	public static function getAllUserroles() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'id';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Saves userrole record
	 *
	 * @param	Array	$data
	 * @return	Integer	$idUserrole
	 */
	public static function saveUserrole(array $data) {
		$idUserrole	= intval($data['id']);
		$xmlPath	= 'ext/project/config/form/admin/userrole.xml';

		if( $idUserrole === 0 ) {
			$idUserrole = self::addUserrole();
		}

		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idUserrole);

		self::updateUserrole($idUserrole, $data);

		return $idUserrole;
	}


	/**
	 * @todo	comment
	 *
	 * @param	Array		$data
	 * @return	Integer		New record ID
	 */
	public static function addUserrole(array $data = array()) {
		unset($data['id']);

		$data['date_create']	= NOW;
		$data['id_user_create']	= userid();

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * @todo	comment
	 *
	 * @param	Integer		$idUserrole
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateUserrole($idUserrole, array $data) {
		$idUserrole	= intval($idUserrole);

		$data['date_update']	= NOW;

		return Todoyu::db()->updateRecord(self::TABLE, $idUserrole, $data);
	}



	/**
	 * Sets deleted flag for current worktype
	 *
	 * @param	Integer	$idUserrole
	 */
	public static function deleteUserrole($idUserRole)	{
		$idUserRole	= intval($idUserRole);

		return Todoyu::db()->deleteRecord(self::TABLE, $idUserRole);
	}



	/**
	 * Get list of existing userrole records
	 *
	 * @param	Array	$params
	 * @return	Array
	 */
	public static function getRecords() {
		$userroles	= self::getAllUserroles();
		$reform		= array(
			'id'	=> 'id',
			'title'	=> 'label'
		);

		return TodoyuArray::reform($userroles, $reform);
	}

}
?>