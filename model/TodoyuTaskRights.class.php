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
* it under the terms of the BSC License.
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
	 * Check if person can edit a task
	 * Check if person has edit rights and if person can edit a status
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isEditAllowed($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$statusIDs	= array_keys(TodoyuTaskStatusManager::getStatuses('edit'));

		$editAllowed	= self::isEditInProjectAllowed($idProject);
		$statusAllowed	= in_array($task->getStatus(), $statusIDs);

		return $editAllowed && $statusAllowed;
	}



	/**
	 * Check if person can edit tasks in this project
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isEditInProjectAllowed($idProject) {
		$idProject	= intval($idProject);

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', 'task:editAndDeleteInOwnProjects');
		} else {
			return allowed('project', 'task:editAndDeleteInAllProjects');
		}
	}



	/**
	 * Check if person can add a new task under the parent task
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
	 * Check if a person can add a new task in this project
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isAddInProjectAllowed($idProject) {
		$idProject	= intval($idProject);

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', 'task:addInOwnProjects');
		} else {
			return allowed('project', 'task:addInAllProjects');
		}
	}



	/**
	 * Check if a person can see the task
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isSeeAllowed($idTask) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);
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