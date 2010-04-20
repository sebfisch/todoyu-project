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
 * Project rights functions
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectRights {

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
	 * Check if person can see the project
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isSeeAllowed($idProject) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);
		$status		= $project->getStatus();

			// Check if project has allowed status
		if( ! self::isStatusAllowed($status) && ! TodoyuAuth::isAdmin() ) {
			return false;
		}

			// See all projects
		if( allowed('project', 'project:seeAll') ) {
			return true;
		}

			// See own projects and is project member
		if( allowed('project', 'project:seeOwn') ) {
			return TodoyuProjectManager::isPersonAssigned($idProject);
		}

		return false;
	}



	/**
	 * Check if person can edit the project
	 *
	 * @return	Boolean
	 */
	public static function isEditAllowed() {
		return allowed('project', 'project:editAndDelete');
	}



	/**
	 * Check if person can add new projects
	 *
	 * @return	Boolean
	 */
	public static function isAddAllowed() {
		return allowed('project', 'project:add');
	}



	/**
	 * Check if a project status is allowed
	 *
	 * @param	Integer		$status
	 * @return	Bool
	 */
	public static function isStatusAllowed($status) {
		$allowedStatuses	= array_keys(TodoyuProjectStatusManager::getStatuses());

			// Check if project has allowed status
		return in_array($status, $allowedStatuses);
	}



	/**
	 * Restrict access to persons who are allowed to see the project
	 *
	 * @param	Integer		$idProject
	 */
	public static function restrictSee($idProject) {
		if( ! self::isSeeAllowed($idProject) ) {
			self::deny('project:see');
		}
	}



	/**
	 * Restrict access to persons who are allowed to edit projects
	 */
	public static function restrictEdit() {
		if( ! self::isEditAllowed() ) {
			self::deny('project:edit');
		}
	}



	/**
	 * Restrict access to persons who are allowed to add projects
	 */
	public static function restrictAdd() {
		if( ! self::isAddAllowed() ) {
			self::deny('project:add');
		}
	}
}

?>