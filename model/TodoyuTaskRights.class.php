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

		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);

		if( $task->isTask() ) {
			if( ! self::isStatusChangeAllowed($idTask) ) {
				return false;
			}

				// Check if person can edit his own tasks
			if( $task->isCurrentPersonCreator() ) {
				if( ! allowed('project', 'task:editOwnTasks') ) {
					return false;
				}
			}

				// Check whether edit for status is allowed
			if( ! self::isStatusEditAllowed($idTask) ) {
				return false;
			}
		}


		if( $task->isContainer() ) {
				// Check if person can edit his own containers
			if( $task->isCurrentPersonCreator() && ! allowed('project', 'container:editOwnContainers') ) {
				return false;
			}
		}

		return self::isEditInProjectAllowed($idTask);
	}



	/**
	 * Check whether a task can get deleted
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isDeleteAllowed($idTask) {
		if( TodoyuAuth::isAdmin() ) {
			return true;
		}

		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);

		if( $task->isTask() ) {
			if( $task->isCurrentPersonCreator() ) {
				if( allowed('project', 'task:deleteOwnTasks') ) {
					return true;
				}
			}
		} elseif( $task->isContainer() ) {
				// Check if person can delete his own containers
			if( $task->isCurrentPersonCreator() ) {
				if( allowed('project', 'container:deleteOwnContainers') ) {
					return true;
				}
			}
		}

		return self::isDeleteInProjectAllowed($idTask);
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

			// Explicit status edit right
		if( allowed('project', 'task:editStatus') ) {
			return true;
		}

			// Task edit right in project
		if( ! self::isEditInProjectAllowed($idTask) ) {
			return false;
		}

		$statusIDs	= array_keys(TodoyuTaskStatusManager::getStatuses('changefrom'));

		return in_array($task->getStatus(), $statusIDs);
	}



	/**
	 * Check whether task edit for status is allowed
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isStatusEditAllowed($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);

		$statusIDs	= array_keys(TodoyuTaskStatusManager::getStatuses('edit'));

		return in_array($task->getStatus(), $statusIDs);
	}



	/**
	 * Check whether person can edit tasks/containers in this project
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isEditInProjectAllowed($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);
		$project	= $task->getProject();
		$status		= $project->getStatus();

		if( in_array($status, Todoyu::$CONFIG['EXT']['project']['projectStatusDisallowChildrenEditing']) || $project->isLocked() ) {
			return false;
		}

		$typeName	= $task->isTask() ? 'task' : 'container';
		$rightName	= $project->isCurrentPersonAssigned() ? 'editInOwnProjects' : 'editInAllProjects';

		return allowed('project', $typeName . ':' . $rightName);
	}



	/**
	 * Check whether a task can get deleted by project rights
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isDeleteInProjectAllowed($idTask) {
		if( TodoyuAuth::isAdmin() ) {
			return true;
		}

		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);
		$project	= $task->getProject();
		$status		= $project->getStatus();

		if( in_array($status, Todoyu::$CONFIG['EXT']['project']['projectStatusDisallowChildrenEditing']) || $project->isLocked() ) {
			return false;
		}

			// Build rights dynamically with type and right
		$typeName	= $task->isTask() ? 'task' : 'container';
		$rightName	= $project->isCurrentPersonAssigned() ? 'deleteInOwnProjects' : 'deleteInAllProjects';

		return allowed('project', $typeName . ':' . $rightName);
	}



	/**
	 * Check whether person can add a new task under the parent task
	 *
	 * @param	Integer		$idParentTask
	 * @param	Boolean		$isContainer		Element to be added is container?
	 * @return	Boolean
	 */
	public static function isAddAllowed($idParentTask, $isContainer = false) {
		$idParentTask	= intval($idParentTask);
		$idProject		= TodoyuTaskManager::getProjectID($idParentTask);

		return self::isAddInProjectAllowed($idProject, $isContainer);
	}



	/**
	 * Check whether person can add a new container under the parent task
	 *
	 * @param	Integer		$idParentTask
	 * @return	Boolean
	 */
	public static function isAddContainerAllowed($idParentTask) {
		return self::isAddAllowed($idParentTask, true);
	}



	/**
	 * Check whether a person can add a new task in this project
	 *
	 * @param	Integer		$idProject
	 * @param	Boolean		$isContainer	added element is a container?
	 * @return	Boolean
	 */
	public static function isAddInProjectAllowed($idProject, $isContainer = false) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);

		if( in_array($project->getStatus(), Todoyu::$CONFIG['EXT']['project']['projectStatusDisallowChildrenEditing']) || $project->isLocked() ) {
			return false;
		}

		$elementType	= $isContainer ? 'container' : 'task';

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', $elementType . ':addInOwnProjects');
		} else {
			return allowed('project', $elementType . ':addInAllProjects');
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
	 * Restrict access if person cannot delete the task
	 *
	 * @param	Integer		$idTask
	 */
	public static function restrictDelete($idTask) {
		if( ! self::isDeleteAllowed($idTask) ) {
			self::deny('task:delete');
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