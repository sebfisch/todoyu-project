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
 * Project View Helper
 * Helper functions for project rendering
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectViewHelper {

	/**
	 * Get options of project users
	 *
	 * @param	TodoyuFormElement	$formElement
	 * @return	Array
	 */
	public static function getProjectUsersOptions(TodoyuFormElement $formElement) {
		$users	= TodoyuProjectManager::getProjectUsers($idProject);
		$options= array();

		foreach($users as $user) {
			$options[] = array(
				'value'	=> $user['id'],
				'label'	=> TodoyuUserManager::getLabel($user['id'])
			);
		}

		return $options;
	}



	/**
	 * Get label for projectuser record in project
	 *
	 * @param	TodoyuFormElement		$formElement		The form element
	 * @param	Array			$data				Form data for the current record to be labeled
	 * @return	String			Record label
	 */
	public static function getProjectUserLabel(TodoyuFormElement $formElement, array $data) {
		$idUser		= intval($data['id_user']);
		$idProject	= intval($data['id_project']);
		$idUserrole	= intval($data['id_userrole']);

		$label		= '';

		if( $idUser	!== 0 ) {
			$label	= TodoyuUserRoleManager::getUserLabel($idUser, $idProject, $idUserrole);
		}

		return $label;
	}



	/**
	 * Callback function to concat name
	 *
	 * @param	TodoyuFormElement	$field
	 * @param	Array				$data
	 * @return	String
	 */
	public static function concatName(TodoyuFormElement $field, $data) {
		return $data['lastname'] . ' ' . $data['firstname'];
	}



	/**
	 * Get project status options. Only allowed "changeto" statuses and currently set one are available
	 *
	 * @param TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getProjectStatusOptions(TodoyuFormElement $field) {
		$values		= $field->getValue();
		$idStatus	= intval($values[0]);
		$options	= array();
		$statuses	= TodoyuProjectStatusManager::getProjectStatuses('changeto', $idStatus);

		foreach($statuses as $statusID => $statusKey) {
			$options[] = array(
				'value'		=> $statusID,
				'label'		=> TodoyuProjectStatusManager::getProjectStatusLabel($statusKey)
			);
		}

		return $options;
	}



	/**
	 * Get task status options
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskStatusOptions(TodoyuFormElement $field) {
		$statuses	= TodoyuProjectStatusManager::getTaskStatuses('changeto');
		$values		= $field->getValue();
		$value		= intval($values[0]);
		$options	= array();

		foreach($statuses as $statusID => $statusKey) {
			$options[] = array(
				'value'		=> $statusID,
				'label'		=> TodoyuProjectStatusManager::getTaskStatusLabel($statusKey)
			);
		}

		return $options;
	}

}

?>