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
	 * Get label for projectuser record in project
	 *
	 * @param	TodoyuFormElement		$formElement		The form element
	 * @param	Array			$data				Form data for the current record to be labeled
	 * @return	String			Record label
	 */
	public static function getProjectUserLabel(TodoyuFormElement $formElement, array $data) {
		$idUser		= intval($data['id_user']);
		$idUserrole	= intval($data['id_userrole']);
		$label		= '';

		if( $idUser	!== 0 ) {
			$user	= TodoyuUserManager::getUser($idUser);
			$role	= TodoyuUserroleManager::getUserrole($idUserrole);

			$label	= $user->getFullName() . ', ' . $role->getTitle();
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
	 * Get status options
	 * Only allowed "changeto" status and current are available
	 *
	 * @return	Array
	 */
	public static function getProjectStatusOptions(TodoyuFormElement $field) {
		$statuses	= TodoyuProjectStatusManager::getProjectStatuses();
		$values		= $field->getValue();
		$value		= intval($values[0]);
		$options	= array();

		foreach($statuses as $statusID => $statusKey)	{
			if( allowed('project', 'projectstatus:' . $statusKey . ':changeto') || $value == $statusID ) {
				$options[] = array(
					'value'		=> $statusID,
					'label'		=> TodoyuProjectStatusManager::getProjectStatusLabel($statusKey)
				);
			}
		}

		return $options;
	}


	public static function getTaskStatusOptions(TodoyuFormElement $field) {
		$statuses	= TodoyuProjectStatusManager::getTaskStatuses();
		$values		= $field->getValue();
		$value		= intval($values[0]);
		$options	= array();

		foreach($statuses as $statusID => $statusKey)	{
			if( allowed('project', 'taskstatus:' . $statusKey . ':changeto') || $value == $statusID ) {
				$options[] = array(
					'value'		=> $statusID,
					'label'		=> TodoyuProjectStatusManager::getTaskStatusLabel($statusKey)
				);
			}
		}

		return $options;
	}

}

?>