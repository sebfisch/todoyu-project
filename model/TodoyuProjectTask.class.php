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
 * Task object
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTask extends TodoyuBaseObject {

	/**
	 * Initialize task
	 *
	 * @param	Integer		$idTask
	 */
	public function __construct($idTask) {
		parent::__construct($idTask, 'ext_project_task');
	}



	/**
	 * Get task title
	 *
	 * @return	String
	 */
	public function getTitle() {
		return $this->data['title'];
	}



	/**
	 * Get full task title (company - project - task)
	 *
	 * @return	String
	 */
	public function getFullTitle() {
		return $this->getProject()->getFullTitle() . ' - ' . $this->getTitle();
	}



	/**
	 * Get task number of the task.
	 * The task number is a combination of the project ID and an incrementing task number per project
	 *
	 * @param	Boolean		$full		True: project ID + task number (concatinated with a dot), FALSE: Only task number
	 * @return	String
	 */
	public function getTaskNumber($full = true) {
		if( $full ) {
			return $this->data['id_project'] . '.' . $this->data['tasknumber'];
		} else {
			return $this->data['tasknumber'];
		}
	}



	/**
	 * Check whether task has status
	 *
	 * @param	Integer		$idStatus
	 * @return	Boolean
	 */
	public function hasStatus($idStatus) {
		return $this->getStatus() === intval($idStatus);
	}



	/**
	 * Get task status
	 *
	 * @return	Integer
	 */
	public function getStatus() {
		return intval($this->data['status']);
	}



	/**
	 * Get task status text (not label, just text value of the status)
	 *
	 * @return	String
	 */
	public function getStatusKey() {
		return TodoyuProjectTaskStatusManager::getStatusKey($this->getStatus());
	}



	/**
	 * Get label for status
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuProjectTaskStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Check whether task has a parent task (or is in project root)
	 *
	 * @return	Boolean
	 */
	public function hasParentTask() {
		return $this->id_parenttask > 0;
	}



	/**
	 * Check whether the task has sub tasks
	 *
	 * @return	Boolean
	 */
	public function hasSubtasks() {
		return TodoyuProjectTaskManager::hasSubTasks($this->getID());
	}



	/**
	 * Get parent task if available
	 *
	 * @return	TodoyuProjectTask
	 */
	public function getParentTask() {
		if( $this->hasParentTask() ) {
			return TodoyuProjectTaskManager::getTask($this->id_parenttask);
		} else {
			return false;
		}
	}



	/**
	 * Get parent task ID. May be 0 when task is in project root
	 *
	 * @return	Integer
	 */
	public function getParentTaskID() {
		return intval($this->id_parenttask);
	}



	/**
	 * Get project ID
	 *
	 * @return	Integer
	 */
	public function getProjectID() {
		return intval($this->id_project);
	}



	/**
	 * Get project array
	 *
	 * @return	Array
	 */
	public function getProjectArray() {
		return TodoyuProjectProjectManager::getProjectArray($this->getProjectID());
	}



	/**
	 * Get project object
	 *
	 * @return	TodoyuProjectProject
	 */
	public function getProject() {
		return TodoyuProjectProjectManager::getProject($this->getProjectID());
	}



	/**
	 * Get activity record
	 *
	 * @return	TodoyuProjectActivity
	 */
	public function getActivity() {
		return TodoyuProjectActivityManager::getActivity($this->getActivityID());
	}



	/**
	 * Get activity label
	 *
	 * @return	String
	 */
	public function getActivityLabel() {
		$activity	= $this->getActivity();

		return $activity['title'];
	}



	/**
	 * Get activity ID
	 *
	 * @return	Integer
	 */
	public function getActivityID() {
		return intval($this->data['id_activity']);
	}



	/**
	 * Get task type (TASK_TYPE_TASK = 1, TASK_TYPE_CONTAINER = 2)
	 *
	 * @return	Integer		Type constant
	 */
	public function getType() {
		return $this->data['type'];
	}



	/**
	 * Get start date
	 *
	 * @return	Integer
	 */
	public function getStartDate() {
		return $this->get('date_start');
	}



	/**
	 * Get end date
	 *
	 * @return	Integer
	 */
	public function getEndDate() {
		return $this->get('date_end');
	}



	/**
	 * Get deadline date
	 *
	 * @return	Integer
	 */
	public function getDeadlineDate() {
		return $this->get('date_deadline');
	}



	/**
	 * Check whether the deadline is exceeded
	 *
	 * @return	Boolean
	 */
	public function isDeadlineExceeded() {
		return $this->getDeadlineDate() < NOW;
	}



	/**
	 * Check whether the end date is exceeded
	 *
	 * @return	Boolean
	 */
	public function isEndDateExceeded() {
		return $this->getEndDate() < NOW;
	}



	/**
	 * Get estimated workload
	 *
	 * @return	Integer
	 */
	public function getEstimatedWorkload() {
		return intval($this->get('estimated_workload'));
	}



	/**
	 * Check whether the task has an estimated workload
	 *
	 * @return	Boolean
	 */
	public function hasEstimatedWorkload() {
		return $this->getEstimatedWorkload() > 0;
	}



	/**
	 * Check whether the task is a container
	 *
	 * @return	String
	 */
	public function isContainer() {
		return $this->getType() == TASK_TYPE_CONTAINER;
	}



	/**
	 * Check whether the task is a normal task (no container or something else)
	 *
	 * @return	Boolean
	 */
	public function isTask() {
		return $this->getType() == TASK_TYPE_TASK;
	}



	/**
	 * Check whether the task is marked as internal
	 *
	 * @return	Boolean
	 */
	public function isPublic() {
		return $this->get('is_public') == 1;
	}



	/**
	 * Check whether the task has already been acknowledged by the assigned person
	 *
	 * @return	Boolean
	 */
	public function isAcknowledged() {
		return $this->get('is_acknowledged') == 1;
	}



	/**
	 * Check if current person is assigned to this task
	 *
	 * @return	Boolean
	 */
	public function isCurrentPersonAssigned() {
		return TodoyuAuth::getPersonID() === $this->getAssignedPersonID();
	}



	/**
	 * Check whether task owner and creator is the same person
	 *
	 * @return	Boolean
	 */
	public function isOwnerAndCreatorSame() {
		return $this->getPersonID('owner') === $this->getPersonID('create');
	}



	/**
	 * Get assigned person
	 *
	 * @return	TodoyuContactPerson
	 */
	public function getAssignedPerson() {
		return TodoyuContactPersonManager::getPerson($this->getAssignedPersonID());
	}



	/**
	 * Get assigned person ID
	 *
	 * @return	Integer
	 */
	public function getAssignedPersonID() {
		return intval($this->get('id_person_assigned'));
	}



	/**
	 * Check whether the task/container can be edited
	 * Check if task/container is not locked any user has edit rights
	 *
	 * @return	Boolean
	 */
	public function isEditable() {
		$allowed	= TodoyuProjectTaskRights::isEditAllowed($this->getID());

		return $allowed && ( $this->isLocked() === false );
	}



	/**
	 * Check whether the task can be deleted
	 *
	 * @return	Boolean
	 */
	public function isDeletable() {
		$allowed	= TodoyuProjectTaskRights::isDeleteAllowed($this->getID());

		return $allowed && ( $this->isLocked() === false );
	}



	/**
	 * Check whether a task is locked
	 *
	 * @param	Boolean		$checkSubtasks		Check also whether a subtask is locked (one subtask locked = task locked too)
	 * @return	Boolean
	 */
	public function isLocked($checkSubtasks = false) {
		if( $checkSubtasks ) {
			return TodoyuProjectTaskManager::isLocked($this->getID()) || TodoyuProjectTaskManager::areSubtasksLocked($this->getID());
		} elseif( $this->isTask() ) {
			return TodoyuProjectTaskManager::isLocked($this->getID());
		} elseif( $this->isContainer()  ) {
			return TodoyuProjectTaskManager::areSubtasksLocked($this->getID());
		} else {
			return false;
		}
	}



	/**
	 * @todo	Check: implement?
	 */
	protected function loadForeignData() {

	}



	/**
	 * Get data for template rendering
	 *
	 * @todo	Use loadForeignData
	 * @param	Integer		$infoLevel		Level of information (the higher the number, the more information is collected)
	 * @return	Array
	 */
	public function getTemplateData($infoLevel = 0) {
		$infoLevel	= intval($infoLevel);
		$data		= parent::getTemplateData();

			// There are no BREAKs because everything after the level has to be loaded too
		switch( $infoLevel ) {
			case 5:
			case 4:
			case 3:
			case 2:
				$data['project']		= $this->getProject()->getTemplateData();
				$data['person_create']	= $this->getPersonData('create');
				$data['person_assigned']= $this->getPersonData('assigned');
				$data['person_owner']	= $this->getPersonData('owner');
				$data['activity']		= $this->getActivity()->getTemplateData();
				$data['fulltitle'] 		= $this->getFullTitle();
				$data['company'] 		= $this->getProject()->getCompany()->getTemplateData();

			case 1:
				$data['statuskey'] 		= $this->getStatusKey();
				$data['statuslabel']	= $this->getStatusLabel();

			case 0:
				$data['is_container']	= $this->isContainer();
				$data['is_locked']		= $this->isLocked();

		}

		return $data;
	}

}

?>