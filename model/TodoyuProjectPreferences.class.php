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
	 * @param	Integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get a preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idPerson
	 * @return	String
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_PROJECT, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get  project preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Delete project preference
	 *
	 * @param	String		$preference
	 * @param	String		$value
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idPerson
	 */
	public static function deletePref($preference, $value = null, $idItem = 0, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, $preference, $value, $idItem, $idArea, $idPerson);
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
	 * @return	String
	 */
	public static function getActiveTaskTab($idTask) {
		$idTask	= intval($idTask);

			// Override selected tab
		$forceTab	= self::getForcedTaskTab();

		if( $forceTab !== false ) {
			$prefTab = $forceTab;
		} else {
			$prefTab= self::getPref('task-tab', $idTask);

			if( $prefTab === false || $prefTab === '' ) {
				$prefTab = TodoyuTaskManager::getDefaultTab($idTask);
			}
		}

		return $prefTab;
	}



	/**
	 * Save active tab in task
	 *
	 * @param	Integer		$idTask
	 * @param	String		$tab
	 */
	public static function saveActiveTaskTab($idTask, $tab) {
		$idTask	= intval($idTask);

		self::savePref('task-tab', $tab, $idTask, true);
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
				self::savePref('task-expanded', $idTask);
			} else {
				self::deletePref('task-expanded', $idTask);
			}
		}
	}



	/**
	 * Get IDs of the expanded tasks
	 *
	 * @return	Array
	 */
	public static function getExpandedTasks() {
		$taskIDs = self::getPrefs('task-expanded');

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
	public static function getOpenProjectIDs() {
		$list	= TodoyuPreferenceManager::getPreference(EXTID_PROJECT, 'projecttabs');

		if( $list === false || $list === '' ) {
			$tabs = array();
		} else {
			$tabs = TodoyuArray::intExplode(',', $list);
		}

		return $tabs;
	}



	/**
	 * Add a new project to the open tab list
	 *
	 * @param	Integer		$idProject
	 */
	public static function addOpenProject($idProject) {
		$idProject	= intval($idProject);

			// Get currently tabbed projects
		$projectIDs	= self::getOpenProjectIDs();

			// Remove project from list if already in
		$projectIDs	= TodoyuArray::removeByValue($projectIDs, array($idProject));

			// Prepend the current one
		array_unshift($projectIDs, $idProject);

		self::saveOpenProjectTabs($projectIDs);
	}


	public static function removeOpenProject($idProject) {
		$idProject	= intval($idProject);

			// Get currently tabbed projects
		$projectIDs	= self::getOpenProjectIDs();

			// Remove project from list
		$projectIDs	= TodoyuArray::removeByValue($projectIDs, array($idProject));

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



	/**
	 * Set forced tab for current rendering
	 *
	 * @param	String		$tab
	 */
	public static function setForcedTaskTab($tab) {
		$GLOBALS['CONFIG']['EXT']['project']['Task']['forceTab'] = $tab;
	}



	/**
	 * Get currently forced tab (or false)
	 *
	 * @return	String		Or FALSE
	 */
	public static function getForcedTaskTab() {
		return $GLOBALS['CONFIG']['EXT']['project']['Task']['forceTab'];
	}

}

?>