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
 * View helper for task
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskViewHelper {

	/**
	 * Get options of all users somehow involved in a task
	 *
	 * @param	TodoyuFormElement $field
	 * @return	Array
	 */
	public static function getTaskUsersOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idTask		= intval($formData['id_project']);

		$options	= array();

		$users	= TodoyuTaskManager::getTaskUsers($idTask);
		foreach($users as $user) {
			$options[] = array(
				'value'	=> $user['id'],
				'label'	=> TodoyuUserManager::getLabel($user['id'], false, true)
			);
		}

		return $options;
	}



	/**
	 * Get users to which a task can be assigned (this are only project users) as options array
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskAssignUserOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idProject	= intval($formData['id_project']);

		$users	= TodoyuUserManager::getInternalUsers();
		$options= array(
			array(
				'label'		=> 'LLL:form.select.pleaseSelect',
				'value'		=> 0,
				'disabled'	=> true
			)
		);

		foreach($users as $user) {
			$options[] = array(
				'label'		=> TodoyuUserManager::getLabel($user['id']),
				'value'		=> $user['id'],
				'disabled'	=> false
			);
		}

		return $options;
	}



	/**
	 * Get task owner user options (alias of getTaskAssignUserOptions)
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskOwnerUserOptions(TodoyuFormElement $field) {
		return self::getTaskAssignUserOptions($field);
	}



	/**
	 * Get stored worktypes as options array
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskWorktypeOptions(TodoyuFormElement $field) {
		$options= array(
			0	=> array(
				'disabled'	=> true,
				'label'		=> 'LLL:form.select.pleaseSelect',
				'value'		=> 0,
			)
		);

		$worktypes	= TodoyuWorktypeManager::getAllWorktypes();
		foreach($worktypes as $num => $type) {
			if ($type['deleted'] == 0) {
				$options[] = array(
					'label'	=> $type['title'],
					'value'	=> $type['id']
				);
			}
		}

		return $options;
	}



	/**
	 * Get option of task owner as comment email receiver
	 *
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getOwnerEmailOption(TodoyuFormElement $field) {
		$idTask		= intval($field->getForm()->getHiddenField('id_task'));
		$taskOwner	= TodoyuTaskManager::getTaskOwner($idTask);

		$option = array(
			0 => array(
				'value'		=> $taskOwner[0]['id'],
				'label'		=> TodoyuUserManager::getLabel($taskOwner[0]['id'], true, true)
			)
		);

		return $option;
	}


}

?>