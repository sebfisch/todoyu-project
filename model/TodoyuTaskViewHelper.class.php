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
	 * Get options of all persons somehow involved in a task
	 *
	 * @param	TodoyuFormElement $field
	 * @return	Array
	 */
	public static function getTaskPersonOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idTask		= intval($formData['id']);

		$options	= array();
		$persons	= TodoyuTaskManager::getTaskPersons($idTask);

		foreach($persons as $person) {
			$options[] = array(
				'value'	=> $person['id'],
				'label'	=> TodoyuPersonManager::getLabel($person['id'], false, true)
			);
		}

		return $options;
	}



	/**
	 * Get options array for task owner person selector
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskOwnerOptions(TodoyuFormElement $field) {
		return self::getPersonAssignedGroupedOptions($field);
	}



	/**
	 * Get options array for assigned person selector, options are grouped into: task members, project persons, all staff persons
	 *
	 * @param	Array		$formData
	 * @return	Array
	 */
	public static function getPersonAssignedGroupedOptions(TodoyuFormElement $field) {
		$options	= array();

			// TaskMember persons
		$groupLabel	= Label('comment.group.taskmembers');
		$options[$groupLabel]	= self::getTaskPersonOptions($field);

			// Get project persons
		$groupLabel	= Label('comment.group.projectmembers');
		$options[$groupLabel]	= TodoyuProjectViewHelper::getProjectPersonOptions($field);

			// Get staff persons (employees of internal company)
		$groupLabel	= Label('comment.group.employees');
		$options[$groupLabel]	= TodoyuContactViewHelper::getInternalPersonOptions($field);

		return $options;
	}



	/**
	 * Get stored worktypes as options array
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskWorktypeOptions(TodoyuFormElement $field) {
		$options= array();

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
				'label'		=> TodoyuPersonManager::getLabel($taskOwner[0]['id'], true, true)
			)
		);

		return $option;
	}

}

?>