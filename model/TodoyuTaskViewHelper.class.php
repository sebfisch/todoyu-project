<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * View helper for task
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskViewHelper {

	/**
	 * Get config array for one status option
	 *
	 * @param	Integer		$index
	 * @param	String		$statusKey
	 * @param	String		$label
	 * @return	Array
	 */
	public static function getStatusOption($index, $statusKey, $label) {
		return TodoyuProjectViewHelper::getStatusOption($index, $statusKey, $label);
	}



	/**
	 * Get task status options
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array
	 */
	public static function getTaskStatusOptions(TodoyuFormElement $field) {
		$values			= $field->getValue();
		$currentStatus	= intval($values[0]);
		$statuses		= TodoyuTaskStatusManager::getStatuses('changeto', $currentStatus);
		$options		= array();

		foreach($statuses as $statusID => $statusKey) {
			$options[] = array(
				'value'		=> $statusID,
				'label'		=> TodoyuTaskStatusManager::getStatusLabel($statusKey)
			);
		}

		return $options;
	}



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
		$worktypes	= TodoyuWorktypeManager::getAllWorktypes();
		$reform		= array(
			'title'	=> 'label',
			'id'	=> 'value'
		);

		return TodoyuArray::reform($worktypes, $reform);
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
			array(
				'value'		=> $taskOwner[0]['id'],
				'label'		=> TodoyuPersonManager::getLabel($taskOwner[0]['id'], true, true)
			)
		);

		return $option;
	}



	public static function getProjecttaskAutocomplete($input, array $formData, $name = '') {
		$idProject	= intval($formData['id_project']);
		$idTask		= intval($formData['id']);

		$filters	= array(
			array(
				'filter'=> 'tasknumberortitle',
				'value'	=> $input
			),
			array(
				'filter'=> 'nottask',
				'value'	=> $idTask
			),
			array(
				'filter'=> 'project',
				'value'	=> $idProject
			),
			array(
				'filter'=> 'subtask',
				'value'	=> $idTask,
				'negate'=> true
			)
		);

		return TodoyuTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);
	}

}

?>