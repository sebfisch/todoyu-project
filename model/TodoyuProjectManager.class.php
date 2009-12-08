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
 * Manager for projects
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectManager {

	/**
	 * Default table for database requests
	 *
	 */
	const TABLE = 'ext_project_project';



	/**
	 * Get project
	 *
	 * @param	Integer		$idProject
	 * @return	TodoyuProject
	 */
	public static function getProject($idProject) {
		$idProject	= intval($idProject);

		return TodoyuCache::getRecord('TodoyuProject', $idProject);
	}



	/**
	 * Get project record
	 *
	 * @param	Integer	$idProject
	 * @return	Array
	 */
	public static function getProjectArray($idProject) {
		$idProject	= intval($idProject);

		return Todoyu::db()->getRecord(self::TABLE, $idProject);
	}



	/**
	 * Add a project to the database
	 *
	 * @param	Array		$data		Data to fill all database fields
	 * @return	Integer		New project id
	 */
	public static function addProject(array $data) {
		$data['id_user_create']	= userid();
		$data['date_create']	= NOW;
		$data['date_update']	= NOW;

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Update a project
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateProject($idProject, array $data) {
		$idProject	= intval($idProject);
		unset($data['id']);

		$data['date_update']	= NOW;

		return Todoyu::db()->updateRecord(self::TABLE, $idProject, $data) === 1;
	}



	/**
	 * Update status of project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$status
	 */
	public static function updateProjectStatus($idProject, $status) {
		$idProject	= intval($idProject);
		$status		= intval($status);

		$data = array(
			'status'	=> $status
		);

		self::updateProject($idProject, $data);
	}



	/**
	 * Save a project (add or update)
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$data
	 * @return	Integer		Project ID
	 */
	public static function saveProject(array $data) {
		$xmlPath	= 'ext/project/config/form/project.xml';
		$idProject	= intval($data['id']);
		unset($data['id']);

			// Add new project if it not already exists
		if( $idProject === 0 ) {
			$idProject = self::addProject(array());
		}

		TodoyuDebug::printInFirebug($data['title'], 'title');

		$projectUsers	= TodoyuArray::assure($data['projectusers']);

			// Save project users
		self::saveProjectUser($idProject, $projectUsers);
		unset($data['projectusers']);

			// Call save hooks
		$data = TodoyuFormHook::callSaveData($xmlPath, $data, $idProject);

//		$data = self::saveProjectUsersFormData($data, $idProject);

		self::updateProject($idProject, $data);

		TodoyuDebug::printInFirebug(TodoyuDiv::isUTF8($data['title']), 'isUTF8');

		TodoyuDebug::printLastQueryInFirebug();

		return $idProject;
	}



	/**
	 * Delete a project (set deleted flag)
	 *
	 * @param	Integer		$idProject
	 * @return	Bool
	 */
	public static function deleteProject($idProject) {
		$idProject	= intval($idProject);

			// Delete project
		$data	= array(
			'deleted'		=> 1,
			'date_update'	=> NOW
		);

		self::updateProject($idProject, $data);

			// Delete all tasks of project
		TodoyuTaskManager::deleteProjectTasks($idProject);
	}



	/**
	 * Get all tasks of a project
	 *
	 * @param	Integer		$idProject
	 * @param	String		$orderBy
	 * @return	Array
	 */
	public static function getTasks($idProject, $orderBy = 'date_create') {
		$idProject	= intval($idProject);

		$fields	= '*';
		$table	= TodoyuTaskManager::TABLE;
		$where	= 'id_project = ' . $idProject;

		return Todoyu::db()->getArray($fields, $table, $where, '', $orderBy);
	}



	/**
	 * Get all task IDs of a project
	 *
	 * @param	Integer		$idProject
	 * @param	String		$orderBy
	 * @return	Array
	 */
	public static function getTaskIDs($idProject, $orderBy = 'date_create') {
		$idProject	= intval($idProject);

		$field	= 'id';
		$table	= TodoyuTaskManager::TABLE;
		$where	= 'id_project = ' . $idProject;

		return Todoyu::db()->getColumn($field, $table, $where, '', $orderBy);
	}



	/**
	 * Get the ID of the currently selected project
	 *
	 * @return	Integer
	 */
	public static function getActiveProjectID() {
		$idProject	= TodoyuProjectPreferences::getActiveProject();

		if( $idProject !== 0 && ! self::isProjectVisible($idProject) ) {
			$open		= TodoyuProjectPreferences::getOpenProjectTabs();
			$open		= array_diff($open, array($idProject));
			$idProject	= intval(array_shift($open));
		}

		return $idProject;
	}



	/**
	 * Check if a project is visible (available and not deleted)
	 *
	 * @param	Integer		$idProject
	 * @return	Bool
	 */
	public static function isProjectVisible($idProject) {
		$idProject	= intval($idProject);
		$project	= self::getProjectArray($idProject);

		return $project !== false && intval($project['deleted']) !== 1;
	}



	/**
	 * Get root task IDs
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */
	public static function getRootTaskIDs($idProject) {
		$idProject	= intval($idProject);

			// Get general filters
		$filters	= self::getTaskTreeFilterStruct();

			// Add filter for current project
		$filters[]	= array(
			'filter'=> 'project',
			'value'	=> $idProject
		);
			// Add filter for root tasks
		$filters[]	= array(
			'filter'=> 'parentTask',
			'value'	=> 0
		);

		$taskFilter	= new TodoyuTaskFilter($filters);

		$taskIDs	= $taskFilter->getTaskIDs();

		return $taskIDs;
	}



	/**
	 * Check whether given task is expanded
	 *
	 * @param	Integer	$idTask
	 * @return	Boolean
	 */
	public static function isTaskExpanded($idTask) {
		$idTask	= intval($idTask);

		return TodoyuPreferenceManager::isPreferenceSet(EXTID_PROJECT, 'expandedtask', $idTask);
	}



	/**
	 * Set given task expanded
	 *
	 * @param	Integer	$idTask
	 */
	public static function setTaskExpanded($idTask) {
		$idTask	= intval($idTask);

		TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'expandedtask', $idTask);
	}



	/**
	 * Get IDs of expanded tasks
	 *
	 * @return	Array
	 */
	public static function getExpandedTaskIDs() {
		return TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, 'expandedtask');
	}



	/**
	 * Get context menu items
	 *
	 * @param	Integer	$idProject
	 * @return	Array
	 */
	public static function getContextMenuItems($idProject, array $items) {
		$idProject	= intval($idProject);
		$isExpanded	= TodoyuProjectPreferences::isProjectDetailsExpanded($idProject);

		$ownItems	= $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Project'];
		$allowed	= array();

		$allowed[] = $ownItems['header'];

			// Show details
		if( allowed('project', 'project:details') ) {
			if( $isExpanded ) {
				$allowed['hidedetails'] = $ownItems['hidedetails'];
			} else {
				$allowed['showdetails'] = $ownItems['showdetails'];
			}
		}

			// Edit
		if( allowed('project', 'project:edit') ) {
			$allowed['edit'] = $ownItems['edit'];
		}

			// Delete
		if( allowed('project', 'project:delete') ) {
			$allowed['delete'] = $ownItems['delete'];
		}

			// Status
		if( allowed('project', 'project:status') ) {
			$allowed['status'] = $ownItems['status'];

			$statuses = array_flip(TodoyuProjectStatusManager::getProjectStatuses());

			foreach($allowed['status']['submenu'] as $key => $status) {
				if( ! allowed('project', 'status:' . $statuses[$key] . ':changeto') ) {
					unset($allowed['status']['submenu'][$key]);
				}
			}
		}

			// Add task
		if( allowed('project', 'project:addtask') ) {
			$allowed['addtask'] = $ownItems['addtask'];
		}

			// Add container
		if( allowed('project', 'project:addcontainer') ) {
			$allowed['addcontainer'] = $ownItems['addcontainer'];
		}

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Get context menu header
	 *
	 * @param	Integer	$idProject
	 * @return	String
	 */
	public static function getContextMenuHeader($idProject) {
		$idProject	= intval($idProject);
		$project	= self::getProjectArray($idProject);

		$header		= TodoyuDiv::cropText($project['title'], 27, '...', false);

		return $header;
	}



	/**
	 * Get next available task nunmber
	 *
	 * @param	Integer	$idProject
	 * @return	Integer
	 */
	public static function getNextTaskNumber($idProject) {
		$idProject	= intval($idProject);

		$field	= 'MAX(tasknumber) as tasknr';
		$table	= 'ext_project_task';
		$where	= 'id_project	= ' . $idProject;

		$highestNumber	= Todoyu::db()->getFieldValue($field, $table, $where, '', '', '', 'tasknr');

		$nextNumber		= intval($highestNumber) + 1;

		return $nextNumber;
	}



	/**
	 * Get open project tabs
	 *
	 * @return	Array
	 */
	public static function getOpenProjectTabs() {
		$projectIDs	= TodoyuProjectPreferences::getOpenProjectTabs();
		$projectList= implode(',', $projectIDs);

			// Get tab data
		if( sizeof($projectIDs) > 0 ) {
			$fields	= '	p.id,
						p.title,
						c.shortname as customer';
			$table	= '	ext_project_project p,
						ext_user_customer c';
			$where	= '	p.id IN(' . $projectList . ') AND
						(p.id_customer = 0 OR p.id_customer = c.id) AND
						p.deleted = 0';
			$order	= 'FIELD(p.id, ' . $projectList . ')';
			$limit	= 3;

			$projects	= Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit);
		} else {
			$projects	= array();
		}

			// Build tab config
		$tabs	= array();

		foreach($projects as $project) {
			$tabs[] = array(
				'id'		=> $project['id'],
				'htmlId'	=> 'projecttab-' . $project['id'],
				'class'		=> 'projecttab',
				'classKey'	=> $project['id'],
				'label'		=> TodoyuDiv::cropText($project['customer'] . ': ' . $project['title'], 23, '..', false)
			);
		}

		return $tabs;
	}



	/**
	 * Get all data attributes for the project (merged from all extensions)
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */

	public static function getProjectDataArray($idProject) {
		$idProject	= intval($idProject);
		$data		= array();

		$tempData	= TodoyuHookManager::callHook('project', 'projectdata', array($idProject));

		foreach($tempData as $hookInfo) {
			$data	= array_merge($data, $hookInfo);
		}

		$data = TodoyuArray::sortByLabel($data);

		return $data;
	}



	/**
	 * Get attributes array for a project data list
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */
	public static function getProjectDataAttributes($idProject) {
		$idProject	= intval($idProject);
		$info		= array();
		$project	= TodoyuProjectManager::getProject($idProject);

		$info[]	= array(
			'label'		=> 'LLL:core.status',
			'value'		=> $project->getStatusLabel(),
			'position'	=> 10
		);

		$info[]	= array(
			'label'		=> 'LLL:project.attr.customer',
			'value'		=> $project->getCustomer()->getTitle(),
			'position'	=> 20
		);

		$info[]	= array(
			'label'		=> 'LLL:project.attr.date_start',
			'value'		=> TodoyuTime::format($project->getStartDate(), 'D2MlongY4'),
			'position'	=> 30
		);

		$info[]	= array(
			'label'		=> 'LLL:project.attr.date_end',
			'value'		=> TodoyuTime::format($project->getEndDate(), 'D2MlongY4'),
			'position'	=> 32
		);

		$info[]	= array(
			'label'		=> 'LLL:project.attr.date_deadline',
			'value'		=> TodoyuTime::format($project->getDeadlineDate(), 'D2MlongY4'),
			'position'	=> 34
		);

		return $info;
	}



	/**
	 * Get task tree filters
	 *
	 * @return	Array
	 */
	public static function getTaskTreeFilters() {
		$filterConfig	= TodoyuProjectPreferences::getPref('tasktree-filters', 0, 0, true);

		if( $filterConfig === false ) {
			$filterConfig = array();
		}

		return $filterConfig;
	}



	/**
	 * Get task tree filters in default filter format
	 *
	 * @return	Array
	 */
	public static function getTaskTreeFilterStruct() {
		$struct	= array();
		$filters= self::getTaskTreeFilters();



		foreach($filters as $filter => $value) {
			$struct[] = array(
				'filter'=> $filter,
				'value'	=> $value
			);
		}

		return $struct;
	}



	/**
	 * Update task tree filters (add a new filter)
	 *
	 * @param	String	$filterName
	 * @param	Mixed	$filterValue
	 */
	public static function updateProjectTreeFilters($filterName, $filterValue) {
			// Get current filters
		TodoyuCache::disable();
		$filters	= self::getTaskTreeFilters();
		TodoyuCache::enable();

			// Add new filter
		$filters[$filterName] = $filterValue;

			// Serialize for database
		$filters = serialize($filters);

		TodoyuProjectPreferences::savePref('tasktree-filters', $filters, 0, true);
	}



	/**
	 * Get the tasks which should be displayed the current filter settings, but aren't because
	 * parent task doesn't match to the filter and is not displayed with all its subtasks
	 *
	 * @param	Integer		$idProject			Project ID
	 * @param	Array		$displayedTasks		Tasks which have been rendered
	 * @return	Array		List of "lost" tasks. They should be displayed, but aren't
	 */
	public static function getLostTaskInTaskTree($idProject, array $displayedTasks) {
		$idProject		= intval($idProject);
		$displayedTasks	= TodoyuArray::intval($displayedTasks, true, true);

		$activeFilters	= self::getTaskTreeFilterStruct();

			// Set filter to selected project
		$activeFilters[] = array(
			'filter'=> 'project',
			'value'	=> $idProject
		);

			// Get all tasks which should be displayed in the tree
		$taskFilter		= new TodoyuTaskFilter($activeFilters);
		$matchingTaskIDs= $taskFilter->getTaskIDs();

			// Get all tasks which should be displayed, but were not (they are lost)
		$matchingNotDisplayedTaskIDs = array_diff($matchingTaskIDs, $displayedTasks);


			// Get an array for mapping between tasks and their parents
		$field		= 'id_parenttask';
		$table		= 'ext_project_task';
		$where		= 'id_project = ' . $idProject; // IN(' . implode(',', $matchingNotDisplayedTaskIDs) . ')';
		$index		= 'id';
		$parentMap	= Todoyu::db()->getColumn($field, $table, $where, '', '', '', '', $index);

		$lostTasks	= array();

		foreach( $matchingNotDisplayedTaskIDs as $matchingNotDisplayedTaskID ) {
				// Start with the parent of the not displayed task
			$idParent	= $parentMap[$matchingNotDisplayedTaskID];

				// Check all parents, if one of them does not match this current filter (and ist
				// not displayed with all its subtasks, add the not display task to the lost list
			while($idParent != 0) {
					// If parent doesn't match to the filter
				if( ! in_array($idParent, $matchingTaskIDs) ) {
						// Add task to lost list and stop checking its
					$lostTasks[] = $matchingNotDisplayedTaskID;
					break;
				}
				$idParent = $parentMap[$idParent];
			}
		}

		return $lostTasks;
	}



	/**
	 * Get users which are connected with the project
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */
	public static function getProjectUsers($idProject) {
		$idProject	= intval($idProject);

			// Get project users
		$fields	= '	u.*,
					ur.id as id_userrole,
					ur.rolekey as rolekey,
					ur.title as rolelabel,
					mmpu.comment';
		$table	= '	ext_user_user u,
					ext_project_userrole ur,
					ext_project_mm_project_user mmpu';
		$where	= '	mmpu.id_user	= u.id AND
					mmpu.id_project	= ' . $idProject . ' AND
					mmpu.id_userrole= ur.id';
		$group	= '	mmpu.id';
		$order	= '	u.lastname,
					u.firstname';

		$users	= Todoyu::db()->getArray($fields, $table, $where, $group, $order);

			// Get company information
		foreach($users as $index => $user) {
			$users[$index]['company'] = TodoyuUserManager::getUserCompanyRecords($user['id']);
		}


		return $users;
	}



	/**
	 * Remove all users from a project
	 *
	 * @param	Integer		$idProject
	 * @return	Integer		Number of removed users
	 */
	public static function removeAllProjectUsers($idProject) {
		$idProject	= intval($idProject);

		$table	= 'ext_project_mm_project_user';
		$where	= '	id_project	= ' . $idProject;

		return Todoyu::db()->doDelete($table, $where);
	}



	/**
	 * Remove a user from a project (as project member)
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idUser
	 * @return	Bool		Success
	 */
	public static function removeProjectUser($idProject, $idUser) {
		$idProject	= intval($idProject);
		$idUser		= intval($idUser);

		$table	= 'ext_project_mm_project_user';
		$where	= '	id_project	= ' . $idProject . ' AND
					id_user		= ' . $idUser;

		return Todoyu::db()->doDelete($table, $where) !== 0;
	}



	/**
	 * Add project user
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idUser
	 * @param	Integer		$idUserrole
	 * @param	String		$comment
	 * @return	Integer		ID of new user record
	 */
	public static function addProjectUser($idProject, $idUser, $idUserrole, $comment = '') {
		$idProject	= intval($idProject);
		$idUser		= intval($idUser);
		$idUserrole	= intval($idUserrole);

		$table	= 'ext_project_mm_project_user';
		$fields	= array(
			'id_project'	=> $idProject,
			'id_user'		=> $idUser,
			'id_userrole'	=> $idUserrole,
			'comment'		=> $comment
		);

		return Todoyu::db()->addRecord($table, $fields);
	}



	/**
	 * Add project user to project
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$projectUserData
	 */
	public static function saveProjectUser($idProject, array $projectUserData) {
		$idProject	= intval($idProject);

		self::removeAllProjectUsers($idProject);

		foreach($projectUserData as $projectUser) {
			self::addProjectUser($idProject, $projectUser['id_user'], $projectUser['id_userrole'], $projectUser['comment']);
		}
	}



	/**
	 * Save project users form data
	 *
	 * @param	Array	$formData
	 * @param	Integer	$idProject
	 * @return	Array
	 */
	public static function saveProjectUsersFormData(array $formData, $idProject) {
		$idProject		= intval($idProject);
		$projectUsers	= $formData['projectusers'];
		$relationIDs	= array();

			// Remove all project users from database
		self::removeAllProjectUsers($idProject);

		if( is_array($projectUsers) ) {
			foreach($projectUsers as $projectUser) {
				$relationIDs[] = self::addProjectUser($idProject, $projectUser['id_user'], $projectUser['id_userrole'], $projectUser['comment']);
			}
		}

			// Remove project user data from data array
		unset($formData['projectusers']);

		return $formData;
	}



	/**
	 * Get project ID array by filter
	 *
	 * @param	Integer	$filterSetID
	 * @return	Array
	 */
	public static function getProjectIDsByFilter($idFilterset = 0, array $filterConditions = array(), $conjunction = 'AND')	{
		$idFilterset = intval($idFilterset);

		if( $idFilterset !== 0 ) {
			$conditions = TodoyuFilterConditionManager::getFilterSetConditions($idFilterset, false);
		} else {
			$conditions = TodoyuFilterConditionManager::buildFilterConditionArray($filterConditions);
		}

		$projectFilter = new TodoyuProjectFilter($conditions, $conjunction);

		return $projectFilter->getProjectIDs('ext_project_project.title');
	}



	/**
	 * Get project default data for new projects
	 *
	 * @return	Array
	 */
	public static function getDefaultProjectData() {
		$defaultData	= array(
			'id'			=> 0,
			'date_create'	=> NOW,
			'date_update'	=> NOW,
			'id_user_create'=> TodoyuAuth::getUserID(),
			'deleted'		=> 0,
			'title'			=> TodoyuLocale::getLabel('project.newproject.title'),
			'description'	=> '',
			'status'		=> STATUS_PLANNING,
			'id_customer'	=> 0,
			'date_start'	=> NOW,
			'date_end'		=> NOW + 3600*24*30,
			'date_deadline'	=> NOW + 3600*24*30
		);

			// Call hook to modify default task data
		$defaultData	= TodoyuHookManager::callHookDataModifier('project', 'projectDefaultData', $defaultData);

		return $defaultData;
	}



	/**
	 *	Get data for submenu entries of currently open projects
	 *
	 *	@return	Array
	 */
	public function getOpenProjectSubmenuEntryTitles() {
		$entries		= array();

		$projectsData	= self::getOpenProjectsSubmenuEntriesData();
		foreach($projectsData as $idProject => $project) {
			$entries[ $idProject ]	= $project['customer']['shortname'] . ': ' . $project['title'];
		}

		return $entries;
	}



	/**
	 *	Get data (array) of the currently open projects
	 *
	 *	@return	Array
	 */
	public function getOpenProjectsSubmenuEntriesData() {
		$openProjectIDs	= TodoyuProjectPreferences::getOpenProjectTabs();

		$projectsData	= array();
		foreach($openProjectIDs as $idProject) {
			$project	= TodoyuProjectManager::getProject($idProject);

			$data				= $project->getTemplateData();
			$data['customer']	= $project->getCustomer();

			$projectsData[$idProject]	= $data;
		}

		return $projectsData;
	}

}

?>