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
 * Task rights functions
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskRights {

	/**
	 * Deny access
	 * Shortcut for project
	 *
	 * @param	String		$right		Denied right
	 */
	private static function deny($right) {
		TodoyuRightsManager::deny('project', $right);
	}



	/**
	 * Check whether person can edit a task
	 * Check whether person has edit rights and if person can edit a status
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isEditAllowed($idTask) {
		if( TodoyuAuth::isAdmin() ) {
			return true;
		}

		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);

		if( $task->isTask() ) {
			if( ! self::isStatusChangeAllowed($idTask) ) {
				return false;
			}

				// Check if person can edit his own tasks
			if( $task->isCurrentPersonCreator() ) {
				if( ! allowed('project', 'task:editAndDeleteOwnTasks') ) {
					return false;
				}
			}
		}

		if( $task->isContainer() ) {
				// Check if person can edit his own containers
			if( $task->isCurrentPersonCreator() ) {
				if( ! allowed('project', 'container:editAndDeleteOwnContainers') ) {
					return false;
				}
			}

			
		}

		$idProject	= TodoyuTaskManager::getProjectID($idTask);

		return self::isEditInProjectAllowed($idProject, $task->isContainer());
	}



	/**
	 * Check whether a status change of a task is allowed
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isStatusChangeAllowed($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);

		if( $task->isLocked() ) {
			return false;
		}

		$statusIDs	= array_keys(TodoyuTaskStatusManager::getStatuses('changefrom'));

		return in_array($task->getStatus(), $statusIDs);
	}



	/**
	 * Check whether person can edit tasks/containers in this project
	 *
	 * @param	Integer		$idProject
	 * @param	Boolean		$isContainer
	 * @return	Boolean
	 */
	public static function isEditInProjectAllowed($idProject, $isContainer = false) {
		$idProject	= intval($idProject);

		if( $isContainer == false ) {
				// Task
			if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
				return allowed('project', 'task:editAndDeleteInOwnProjects');
			} else {
				return allowed('project', 'task:editAndDeleteInAllProjects');
			}
		} else {
				// Container
			if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
				return allowed('project', 'container:editAndDeleteInOwnProjects');
			} else {
				return allowed('project', 'container:editAndDeleteInAllProjects');
			}
		}
	}



	/**
	 * Check whether person can add a new task under the parent task
	 *
	 * @param	Integer		$idParentTask
	 * @return	Boolean
	 */
	public static function isAddAllowed($idParentTask) {
		$idParentTask	= intval($idParentTask);
		$idProject		= TodoyuTaskManager::getProjectID($idParentTask);

		return self::isAddInProjectAllowed($idProject);
	}



	/**
	 * Check whether a person can add a new task in this project
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isAddInProjectAllowed($idProject) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);

		if( in_array($project->getStatus(), array(STATUS_DONE, STATUS_CLEARED)) || $project->isLocked() ) {
			return false;
		}

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', 'task:addInOwnProjects');
		} else {
			return allowed('project', 'task:addInAllProjects');
		}
	}



	/**
	 * Check whether quick-add of tasks is allowed
	 * Needs at least one project where he can add tasks
	 *
	 * @return	Boolean
	 */
	public static function isQuickAddAllowed() {
		$projectIDs	= TodoyuProjectManager::getProjectIDsForTaskAdd();

		return sizeof($projectIDs) > 0;
	}



	/**
	 * Check whether a person can see the task
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isSeeAllowed($idTask) {
		if( TodoyuAuth::isAdmin() ) {
			return true;
		}

		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);

			// If container, check if person can see the project
		$seeProject	= TodoyuProjectRights::isSeeAllowed($task->getProjectID());

		if( $task->isContainer() || $seeProject === false ) {
			return $seeProject;
		}

		$status	= $task->getStatusKey();

			// Check status
		if( ! self::hasStatusRight($status, 'see') ) {
			return false;
		}

			// Check view rights with assignment
		if( ! TodoyuTaskManager::isPersonAssigned($idTask, 0, true) ) {
			return allowed('project', 'task:seeAll');
		}

		return true;
	}



	/**
	 * Check whether person can see a taskstatus
	 *
	 * @param	String		$status
	 * @param	String		$type
	 * @return	Boolean
	 */
	public static function hasStatusRight($status, $type = 'see') {
		return allowed('project', 'taskstatus:' . $status . ':' . $type);
	}



	/**
	 * Restrict access to persons who are allowed to add tasks in the project if this task
	 *
	 * @param	Integer		$idTask
	 */
	public static function restrictEdit($idTask) {
		if( ! self::isEditAllowed($idTask) ) {
			self::deny('task:edit');
		}
	}



	/**
	 * Restrict access to persons who are allowed to edit tasks in the project
	 *
	 * @param	Integer		$idProject
	 */
	public static function restrictEditInProject($idProject) {
		if( ! self::isEditInProjectAllowed($idProject) ) {
			self::deny('task:edit');
		}
	}



	/**
	 * Restrict access to persons who are allowed to add tasks in the project of this task
	 *
	 * @param	Integer		$idParentTask
	 */
	public static function restrictAdd($idParentTask) {
		if( ! self::isAddAllowed($idParentTask) ) {
			self::deny('task:add');
		}
	}



	/**
	 * Restrict access to persons who are allowed to add tasks in the project
	 *
	 * @param	Integer		$idProject
	 */
	public static function restrictAddToProject($idProject) {
		if( ! self::isAddInProjectAllowed($idProject) ) {
			self::deny('task:add');
		}
	}



	/**
	 * Restrict access to persons who are allowed to see the task
	 *
	 * @param	Integer		$idTask
	 */
	public static function restrictSee($idTask) {
		if( ! self::isSeeAllowed($idTask) ) {
			self::deny('task:see');
		}
	}

}
?>