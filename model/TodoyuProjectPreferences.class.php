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
 * Project preference manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectPreferences {

	/**
	 * Save a preference for project
	 *
	 * @param	String		$preference
	 * @param	String		$value
	 * @param	Integer		$idItem
	 * @param	Bool		$unique
	 * @param	Integer		$idUser
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idUser = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, $preference, $value, $idItem, $unique, $idArea, $idUser);
	}



	/**
	 * Get a preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idUser
	 * @return	String
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idUser = 0) {
		$idItem	= intval($idItem);
		$idUser	= intval($idUser);

		return TodoyuPreferenceManager::getPreference(EXTID_PROJECT, $preference, $idItem, $idArea, $unserialize, $idUser);
	}



	/**
	 * Get  project preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idUser
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idUser = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, $preference, $idItem, $idArea, $idUser);
	}



	/**
	 * Delete project preference
	 *
	 * @param	String		$preference
	 * @param	String		$value
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idUser
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idUser = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, $preference, $value, $idItem, $idArea, $idUser);
	}



	/**
	 * Get active project ID
	 *
	 * @return	Integer
	 */
	public static function getActiveProject() {
		return intval(self::getPref('project'));
	}



	/**
	 * Save visibility of the subtasks of a task
	 *
	 * @param	Integer		$idTask
	 * @param	Bool		$isVisible
	 * @param	Integer		$idArea
	 */
	public static function saveSubtasksVisibility($idTask, $isVisible = true, $idArea = 0) {
		$idTask	= intval($idTask);
		$idArea	= intval($idArea);

		if( $isVisible ) {
			self::savePref('tasktree-subtasks', $idTask, 0, false, $idArea);
		} else {
			self::deletePref('tasktree-subtasks', $idTask, 0, $idArea);
		}
	}



	/**
	 * Get visible sub tasks
	 *
	 * @param	Integer $idArea
	 * @return	Array
	 */
	public static function getVisibleSubtasks($idArea = 0) {
		$idArea	= intval($idArea);

		return self::getPrefs('tasktree-subtasks', 0, $idArea);

	}



	/**
	 * Get the key of the currently active tab (default if none is selected)
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idArea
	 * @return	String
	 */
	public static function getActiveTaskTab($idTask, $idArea = null) {
		$idTask	= intval($idTask);
		$idArea	= is_null($idArea) ? AREA : intval($idArea);

		$prefTab= self::getPref('task-tab', $idTask, $idArea);

		if( $prefTab === false ) {
			$prefTab = TodoyuTaskManager::getDefaultTab($idTask);
		}

		return $prefTab;
	}



	/**
	 * Save active tab in task
	 *
	 * @param	Integer	$idTask
	 * @param	String	$tabKey
	 * @param	Integer	$idArea
	 */
	public static function saveActiveTaskTab($idTask, $tabKey, $idArea = null) {
		$idTask	= intval($idTask);
		$idArea	= is_null($idArea) ? AREA : intval($idArea);

		self::savePref('task-tab', $tabKey, $idTask, true, $idArea);
	}



	/**
	 * Save currently active project
	 *
	 * @param	Integer		$idProject
	 */
	public static function saveCurrentProject($idProject) {
		$idProject	= intval($idProject);

		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'project', $idProject, 0, true);
	}



	/**
	 * Save expanded task status
	 *
	 * @param	Integer		$idTask			Task ID
	 * @param	Bool		$expanded		Is task now expanded?
	 */
	public static function saveTaskExpandedStatus($idTask, $expanded = true) {
		$idTask	= intval($idTask);

		if( $idTask !== 0 ) {
			if( $expanded ) {
				self::savePref('tasktree-task-exp', $idTask);
			} else {
				self::deletePref('tasktree-task-exp', $idTask);
			}
		}
	}



	/**
	 * Get IDs of the expanded tasks
	 *
	 * @return	Array
	 */
	public static function getExpandedTasks() {
		$taskIDs = self::getPrefs('tasktree-task-exp');

		if( $taskIDs === false ) {
			$taskIDs = array();
		}

		return $taskIDs;
	}



	/**
	 * Save open project tabs (ID of the open projects)
	 *
	 * @param	Array		$projectIDs
	 */
	public static function saveOpenProjectTabs(array $projectIDs = array()) {
		$projectIDs	= TodoyuArray::intval($projectIDs, true, true);
		$list		= implode(',', $projectIDs);

		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'projecttabs', $list, 0, true);
	}



	/**
	 * Save pref: details being expanded?
	 *
	 * @param	Integer	idProject
	 * @param	Boolean	$expanded
	 */
	public static function saveExpandedDetails($idProject, $expanded = true) {
		$idProject	= intval($idProject);

		if( $expanded ) {
			self::savePref('detailsexpanded', 1, $idProject, true);
		} else {
			self::deletePref('detailsexpanded', null, $idProject);
		}
	}



	/**
	 * Check whether given project details are expanded
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isProjectDetailsExpanded($idProject) {
		$idProject	= intval($idProject);

		return self::getPref('detailsexpanded', $idProject) == 1;
	}



	/**
	 * Get open project tabs
	 *
	 * @return	Array		IDs of the projects which are displayed as tabs
	 */
	public static function getOpenProjectTabs() {
		$list	= TodoyuPreferenceManager::getPreference(EXTID_PROJECT, 'projecttabs');

		if( $list === false || $list === '' ) {
			$tabs = array();
		} else {
			$tabs = TodoyuDiv::intExplode(',', $list);
		}

		return $tabs;
	}



	/**
	 * Add a new project to the open tab list
	 *
	 * @param	Integer		$idProject
	 */
	public static function addNewOpenProjectTab($idProject) {
		$idProject	= intval($idProject);

			// Get currently tabbed projects
		$projectIDs	= self::getOpenProjectTabs();

			// Remove project from list if already in
		$projectIDs	= TodoyuArray::removeByValue($projectIDs, array($idProject));

			// Prepend the current one
		array_unshift($projectIDs, $idProject);

		self::saveOpenProjectTabs($projectIDs);
	}



	/**
	 * Get visibility status
	 *
	 * @param	Integer	$idProject
	 */
	public static function getVisibleStatuses($idProject) {
		$idProject	= intval($idProject);

	}

}

?>