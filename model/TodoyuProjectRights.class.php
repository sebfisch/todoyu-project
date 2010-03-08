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
 * Project rights functions
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectRights {

	private static function deny($right) {
		TodoyuRightsManager::deny('project', $right);
	}

	public static function canTaskEdit($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);
		$idProject	= TodoyuTaskManager::getProjectID($idTask);
		$statusIDs	= array_keys(TodoyuProjectStatusManager::getTaskStatuses('edit'));

		$canEditTask	= self::canTaskEditInProject($idProject);
		$canEditStatus	= in_array($task->getStatus(), $statusIDs);

		return $canEditTask && $canEditStatus;
	}


	public static function canTaskEditInProject($idProject) {
		$idProject	= intval($idProject);

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', 'task:editAndDeleteInOwnProjects');
		} else {
			return allowed('project', 'task:editAndDeleteInAllProjects');
		}
	}


	public static function canTaskAdd($idParentTask) {
		$idParentTask	= intval($idParentTask);
		$idProject		= TodoyuTaskManager::getProjectID($idParentTask);

		return self::canTaskAddInProject($idProject);
	}

	public static function canTaskAddInProject($idProject) {
		$idProject	= intval($idProject);

		if( TodoyuProjectManager::isPersonAssigned($idProject) ) {
			return allowed('project', 'task:addInOwnProjects');
		} else {
			return allowed('project', 'task:addInAllProjects');
		}
	}


	public static function canTaskSee($idTask) {
		$idTask	= intval($idTask);

		if( ! TodoyuTaskManager::isPersonAssigned($idTask) ) {
			return allowed('project', 'task:seeAll');
		}

		return true;
	}


	public static function canProjectSee($idProject) {
		$idProject	= intval($idProject);

		if( allowed('project', 'project:seeAll') ) {
			return true;
		}

		if( allowed('project', 'project:seeOwn') ) {
			return TodoyuProjectManager::isPersonAssigned($idProject);
		}

		return false;
	}


	public static function canProjectEdit() {
		return allowed('project', 'project:editAndDelete');
	}



	/**
	 * Check if a person can edit a task
	 *
	 * @param	Integer		$idTask
	 */
	public static function checkTaskEdit($idTask) {
		if( ! self::canTaskEdit($idTask) ) {
			self::deny('task:edit');
		}
	}


	public static function checkTaskEditInProject($idProject) {
		if( ! self::canTaskEditInProject($idProject) ) {
			self::deny('task:edit');
		}
	}



	/**
	 * Check if a person can add a subtask
	 *
	 * @param	Integer		$idParentTask
	 */
	public static function checkTaskAdd($idParentTask) {
		if( ! self::canTaskAdd($idParentTask) ) {
			self::deny('task:add');
		}
	}



	/**
	 * Check if a person can add a task to a project
	 *
	 * @param	Integer		$idProject
	 */
	public static function checkTaskAddToProject($idProject) {
		if( ! self::canTaskAddInProject($idProject) ) {
			self::deny('task:add');
		}
	}


	public static function checkTaskSee($idTask) {
		if( ! self::canTaskSee($idTask) ) {
			self::deny('task:see');
		}
	}


	public static function checkProjectSee($idProject) {
		if( ! self::canProjectSee($idProject) ) {
			self::deny('project:see');
		}
	}


	public static function checkProjectEdit() {
		if( ! self::canProjectEdit() ) {
			self::deny('project:edit');
		}
	}
}

?>