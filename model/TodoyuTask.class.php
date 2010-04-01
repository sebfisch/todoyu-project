<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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

class TodoyuTask extends TodoyuBaseObject {

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
	 * Check if task has status
	 *
	 * @param	Integer		$idStatus
	 * @return	Bool
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
		return TodoyuTaskStatusManager::getStatusKey($this->getStatus());
	}



	/**
	 * Get label for status
	 *
	 * @return	String
	 */
	public function getStatusLabel() {
		return TodoyuTaskStatusManager::getStatusLabel($this->getStatus());
	}



	/**
	 * Check if tasks has a parent task (or is in project root)
	 *
	 * @return	Boolean
	 */
	public function hasParentTask() {
		return $this->id_parenttask > 0;
	}



	/**
	 * Check if the task has sub tasks
	 *
	 * @return	Bool
	 */
	public function hasSubtasks() {
		return TodoyuTaskManager::hasSubTasks($this->getID());
	}



	/**
	 * Get parent task if available
	 *
	 * @return	Task
	 */
	public function getParentTask() {
		if( $this->hasParentTask() ) {
			return TodoyuTaskManager::getTask($this->id_parenttask);
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
		return $this->id_project;
	}



	/**
	 * Get project array
	 *
	 * @return	Array
	 */
	public function getProjectArray() {
		$idProject	= $this->getProjectID();
		$table		= 'ext_project_project';

		return Todoyu::db()->getRecord($table, $idProject);
	}



	/**
	 * Get project object
	 *
	 * @return	Project
	 */
	public function getProject() {
		return TodoyuProjectManager::getProject($this->getProjectID());
	}



	/**
	 * Get worktype record
	 *
	 * @return	Array
	 */
	public function getWorktype() {
		return Todoyu::db()->getRecord('ext_project_worktype', $this->id_worktype);
	}



	/**
	 * Get task type
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
	 * Check if the task is a container
	 *
	 * @return	String
	 */
	public function isContainer() {
		return $this->getType() == TASK_TYPE_CONTAINER;
	}



	/**
	 * Check if the task is a normal task (no container or something else)
	 *
	 * @return	Boolean
	 */
	public function isTask() {
		return $this->getType() == TASK_TYPE_TASK;
	}



	/**
	 * Check if the task is marked as internal
	 *
	 * @return	Bool
	 */
	public function isPublic() {
		return $this->get('is_public') == 1;
	}



	/**
	 * Check if the task has already been acknowledged by the assigned person
	 *
	 * @return	Boolean
	 */
	public function isAcknowledged() {
		return $this->get('is_acknowledged') == 1;
	}



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
		switch($infoLevel) {
			case 5:
			case 4:
			case 3:
			case 2:
				$data['project']		= $this->getProjectArray();
				$data['person_create']	= $this->getPersonData('create');
				$data['person_assigned']= $this->getPersonData('assigned');
				$data['person_owner']	= $this->getPersonData('owner');
				$data['worktype']		= $this->getWorktype();
				$data['fulltitle'] 		= $this->getFullTitle();
				$data['company'] 		= $this->getProject()->getCompanyData();

			case 1:
				$data['statuskey'] 		= $this->getStatusKey();
				$data['statuslabel']	= $this->getStatusLabel();

			case 0:
				$data['is_container']	= $this->isContainer();

		}

		return $data;
	}

}

?>