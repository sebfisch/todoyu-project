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
	 * @return	Bool
	 */
	public static function isSeeAllowed($idProject) {
		$idProject	= intval($idProject);

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
	 * @return	Bool
	 */
	public static function isEditAllowed() {
		return allowed('project', 'project:editAndDelete');
	}



	/**
	 * Restrict access to person which are allowed to see the project
	 *
	 * @param	Integer		$idProject
	 */
	public static function restrictSee($idProject) {
		if( ! self::isSeeAllowed($idProject) ) {
			self::deny('project:see');
		}
	}



	/**
	 * Restrict access to person which are allowed to edit projects
	 *
	 */
	public static function restrictEdit() {
		if( ! self::isEditAllowed() ) {
			self::deny('project:edit');
		}
	}
}

?>