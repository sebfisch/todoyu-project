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
 * Manager for projects
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectManager {

	/**
	 * @var	String		Default table for database requests
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

		return TodoyuRecordManager::getRecord('TodoyuProject', $idProject);
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
	 * Get project label
	 * With short company name prefixed
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function getLabel($idProject) {
		return self::getProject($idProject)->getFullTitle(true);
	}



	/**
	 * Add a project to the database
	 *
	 * @param	Array		$data		Data to fill all database fields
	 * @return	Integer		New project id
	 */
	public static function addProject(array $data) {
		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Update a project
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateProject($idProject, array $data) {
		$idProject	=	intval($idProject);
		
		TodoyuRecordManager::removeRecordCache('TodoyuProject', $idProject);

		return TodoyuRecordManager::updateRecord(self::TABLE, $idProject, $data);
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

			// Call project status change hooks
		TodoyuHookManager::callHook('project', 'changeprojectstatus', array($idProject,$status));

		self::updateProject($idProject, $data);
	}



	/**
	 * Save a project (add or update)
	 *
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

		$persons	= TodoyuArray::assure($data['persons']);

			// Save project persons
		self::saveProjectPersons($idProject, $persons);
		unset($data['persons']);

			// Call save hooks
		$data = TodoyuFormHook::callSaveData($xmlPath, $data, $idProject);

		self::updateProject($idProject, $data);

		return $idProject;
	}



	/**
	 * Delete a project (set deleted flag)
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
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
		$where		= 'id_project = ' . $idProject;

		return TodoyuRecordManager::getAllRecords(TodoyuTaskManager::TABLE, $where, '');
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
	 * Get a project which is available for the person
	 *
	 * @return	Integer
	 */
	public static function getAvailableProjectForPerson() {
		$filter		= new TodoyuProjectFilter();
		$projectIDs	= $filter->getProjectIDs('date_create DESC', 1);

		return intval($projectIDs[0]);
	}



	/**
	 * Check whether a project is visible (available and not deleted)
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	public static function isProjectVisible($idProject) {
		$idProject	= intval($idProject);
		$project	= self::getProjectArray($idProject);

		return $project !== false && intval($project['deleted']) !== 1;
	}



	/**
	 * Check whether a person is assigned to a project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isPersonAssigned($idProject, $idPerson = 0) {
		$idProject	= intval($idProject);
		$idPerson	= personid($idPerson);

		$fields	= 'id';
		$table	= 'ext_project_mm_project_person';
		$where	= '		id_project	= ' . $idProject .
				  ' AND	id_person	= ' . $idPerson;

		return Todoyu::db()->hasResult($fields, $table, $where);
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
	 * Get the sub tasks of a task which match to all the filters
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubTaskIDs($idTask) {
		$idTask		= intval($idTask);

			// Get task filters
		$filters	= self::getTaskTreeFilterStruct();

			// Add parent task filter
		$filters[]	= array(
			'filter'	=> 'parentTask',
			'value'		=> $idTask
		);

		$taskFilter	= new TodoyuTaskFilter($filters);

		return $taskFilter->getTaskIDs();
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
	 * @param	Array	$items
	 * @return	Array
	 */
	public static function getContextMenuItems($idProject, array $items) {
		$idProject	= intval($idProject);
		$project	= self::getProject($idProject);
		$isExpanded	= TodoyuProjectPreferences::isProjectDetailsExpanded($idProject);

		$ownItems	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Project']);
		$allowed	= array();

			// Show in project area
		if( AREA !== EXTID_PROJECT ) {
			if( TodoyuProjectRights::isSeeAllowed($idProject) ) {
				$allowed['showinproject'] = $ownItems['showinproject'];
			}
		}

			// Show details
		if( TodoyuProjectRights::isSeeAllowed($idProject) ) {
			if( $isExpanded ) {
				$allowed['hidedetails'] = $ownItems['hidedetails'];
			} else {
				$allowed['showdetails'] = $ownItems['showdetails'];
			}
		}

			// Modify project
		if( $project->isEditable() ) {
				// Edit
			$allowed['edit'] = $ownItems['edit'];

				// Status
			$allowed['status'] = $ownItems['status'];
			$statuses = TodoyuProjectStatusManager::getStatuses();

			foreach($allowed['status']['submenu'] as $key => $status) {
				if( ! in_array($key, $statuses) ) {
					unset($allowed['status']['submenu'][$key]);
				}
			}

			if( ! $project->hasLockedTasks() ) {
					// Delete
				$allowed['delete'] = $ownItems['delete'];
			}
		}

			// Only add elements to project, if it is not locked
		if( ! $project->isLocked() ) {
			if( allowed('project', 'task:addInAllProjects') || (allowed('project', 'task:addInOwnProjects') && TodoyuProjectManager::isPersonAssigned($idProject)) ) {
					// Add task
				$allowed['addtask'] = $ownItems['addtask'];
					// Add container
				$allowed['addcontainer'] = $ownItems['addcontainer'];
			}
		}		

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Get next available task number
	 *
	 * @param	Integer		$idProject
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
		$projectIDs	= TodoyuProjectPreferences::getOpenProjectIDs();
		$projectList= implode(',', $projectIDs);

			// Get tab data
		if( sizeof($projectIDs) > 0 ) {
			$fields	= '	p.id,
						p.title,
						c.shortname as companyShort,
						c.title as companyFull';
			$table	= '	ext_project_project p,
						ext_contact_company c';
			$where	= '		p.id IN(' . $projectList . ')
						AND	(p.id_company = 0 OR p.id_company = c.id)
						AND	p.deleted = 0';
			$order	= 'FIELD(p.id, ' . $projectList . ')';
			$limit	= 3;

			$projects	= Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit);
		} else {
			$projects	= array();
		}

			// Build tab config
		$tabs	= array();

		foreach($projects as $project) {
			if ( TodoyuProjectRights::isSeeAllowed($project['id']) ) {
				$companyLabel	= trim($project['companyShort']) === '' ? TodoyuString::crop($project['companyFull'], 8, '..', false) : $project['companyShort'];
				$tabs[] = array(
					'id'		=> $project['id'],
					'label'		=> TodoyuString::crop($companyLabel. ': ' . $project['title'], 23, '..', false)
				);
			}
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
			'label'		=> 'LLL:project.attr.company',
			'value'		=> $project->getCompany()->getTitle(),
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

		if( $project->getDeadlineDate() > 0 && Todoyu::person()->isInternal() ) {
			$info[]	= array(
				'label'		=> 'LLL:project.attr.date_deadline',
				'value'		=> TodoyuTime::format($project->getDeadlineDate(), 'D2MlongY4'),
				'position'	=> 34
			);
		}

//		print_r($info);
//		exit();
		
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
	 * parent task doesn't match to the filter and is not displayed with all its sub tasks
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
		$where		= 'id_project = ' . $idProject; // . ' AND id IN(' . implode(',', $matchingNotDisplayedTaskIDs) . ')';
		$index		= 'id';
		$parentMap	= Todoyu::db()->getColumn($field, $table, $where, '', '', '', '', $index);

		$lostTasks	= array();

		foreach( $matchingNotDisplayedTaskIDs as $matchingNotDisplayedTaskID ) {
				// Start with the parent of the not displayed task
			$idParent	= $parentMap[$matchingNotDisplayedTaskID];

				// Memorize already checked parent. If there is in any case a recursion (should not happen),
				// this check will prevent a deadlock
			$checkedParents	= array();

				// Check all parents, if one of them does not match this current filter (and ist
				// not displayed with all its sub tasks, add the not display task to the lost list
			while( $idParent != 0 && ! in_array($idParent, $checkedParents) ) {
				$checkedParents[] = $idParent;
				
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
	 * Get persons which are connected with the project
	 *
	 * @param	Integer		$idProject
	 * @param	Boolean		$personUnique
	 * @param	Boolean		$withAccount
	 * @return	Array
	 */
	public static function getProjectPersons($idProject, $personUnique = false, $withAccount = false) {
		$idProject	= intval($idProject);

			// Get project persons
		$fields	= '	mmpp.*,
					pe.*,
					pe.id as id_person,
					pr.rolekey,
					pr.title as rolelabel';
		$table	= '	ext_contact_person pe,
					ext_project_role pr,
					ext_project_mm_project_person mmpp';
		$where	= '		mmpp.id_person	= pe.id
					AND mmpp.id_project	= ' . $idProject .
				  ' AND	mmpp.id_role	= pr.id
				    AND	pe.deleted		= 0';
		$group	= '	mmpp.id';
		$order	= '	pe.lastname,
					pe.firstname';

			// Add public check for external person
		if( ! Todoyu::person()->isInternal() ) {
			$where .= ' AND (
							mmpp.is_public 	= 1 OR
							mmpp.id_person	= ' . personid() . '
						)';
		}

			// If persons should be unique, group by id (we don't care about the project roles)
		if( $personUnique === true ) {
			$group	= 'pe.id';
		}

			// Limit to persons with active todoyu account
		if( $withAccount === true ) {
			$where .= ' AND pe.active = 1';
		}


		$persons= Todoyu::db()->getArray($fields, $table, $where, $group, $order);

			// Get company information
		foreach($persons as $index => $person) {
			$persons[$index]['company'] = TodoyuPersonManager::getPersonCompanyRecords($person['id']);
		}

		return $persons;
	}



	/**
	 * Get project person label (name + project role)
	 *
	 * @param	Integer		$idPerson
	 * @param	Integer		$idProject
	 * @param	Integer		$idProjectRole
	 * @return	String
	 */
	public static function getProjectPersonLabel($idPerson, $idProject, $idProjectRole = 0) {
		$idPerson		= intval($idPerson);
		$idProject		= intval($idProject);
		$idProjectRole	= intval($idProjectRole);

		$label	= TodoyuPersonManager::getLabel($idPerson);

		if ( $idProjectRole === 0 ) {
			$label	.= ' - ' . self::getProjectroleLabel($idPerson, $idProject);
		} else {
			$label	.= ' - ' . TodoyuProjectroleManager::getLabel($idProjectRole);
		}

		return $label;
	}



	/**
	 * Get role of person in project
	 *
	 * @param	Integer		$idPerson
	 * @param	Integer		$idProject
	 * @return	Integer
	 */
	public static function getProjectroleLabel($idPerson, $idProject) {
		return self::getProjectrole($idProject, $idPerson)->getTitle();
	}



	/**
	 * Get project role of a person in a project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idPerson
	 * @return	TodoyuProjectrole
	 */
	public static function getProjectrole($idProject, $idPerson) {
		$idPerson	= intval($idPerson);
		$idProject	= intval($idProject);

		$field		= '	pr.id';
		$tables		= '	ext_project_role pr,
						ext_project_mm_project_person mmpp';
		$where		= '		mmpp.id_project	= ' . $idProject .
					  ' AND	mmpp.id_person	= ' . $idPerson .
					  ' AND	mmpp.id_role	= pr.id';

		$idProjectRole	= Todoyu::db()->getFieldValue($field, $tables, $where);

		return TodoyuProjectroleManager::getProjectrole($idProjectRole);
	}



	/**
	 * Get all roles which are used in a project
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */
	public static function getProjectRoles($idProject) {
		$fields	= '	DISTINCT pr.*';
		$table	= '	ext_project_mm_project_person mm,
					ext_project_role pr';
		$where	= '		mm.id_project	= ' . $idProject .
				  ' AND	mm.id_role		= pr.id
				  	AND	pr.deleted		= 0';

		return Todoyu::db()->getArray($fields, $table, $where);
	}



	/**
	 * Get first person with a specific role in project
	 * If no person has this role, FALSE is returned
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idRole
	 * @return	TodoyuPerson				Or FALSE if not found
	 */
	public static function getRolePerson($idProject, $idRole) {
		$personIDs	= self::getRolePersonIDs($idProject, $idRole);
		$idPerson	= intval($personIDs[0]);

		if( $idPerson !== 0 ) {
			return TodoyuPersonManager::getPerson($idPerson);
		} else {
			return false;
		}
	}



	/**
	 * Get IDs of all persons with the given roles in the given project
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$roleIDs
	 * @return	Array
	 */
	public static function getRolesPersonIDs($idProject, array $roleIDs = array()) {
		$idProject	= intval($idProject);

		if ( sizeof($roleIDs) > 0 ) {
			$field	= 'id_role,id_person';
			$table	= '	ext_project_mm_project_person';
			$where	= '	id_project	= ' . $idProject .
				  	  ' AND id_role IN (' . TodoyuArray::intImplode($roleIDs) . ')';

			$rolesPersonsIDs	= Todoyu::db()->getArray($field, $table, $where);
		} else {
				// No roles given? there can be no persons assigned to
			$rolesPersonsIDs	= array();
		}

		return $rolesPersonsIDs;
	}



	/**
	 * Get IDs of all persons with the given role in the given project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idRole
	 * @param	Boolean		$onlyFirstPerson
	 * @return	Array
	 */
	public static function getRolePersonIDs($idProject, $idRole) {
		$idProject	= intval($idProject);
		$idRole		= intval($idRole);

		$field	= 'id_person';
		$table	= '	ext_project_mm_project_person';
		$where	= '		id_project	= ' . $idProject .
				  ' AND	id_role		= ' . $idRole;
		$order	= 'id';

		$personIDs	= TodoyuArray::flatten(Todoyu::db()->getArray($field, $table, $where, '', $order));

		return $personIDs;
	}



	/**
	 * Remove all persons from a project
	 *
	 * @param	Integer		$idProject
	 * @return	Integer		Number of removed persons
	 */
	public static function removeAllProjectPersons($idProject) {
		$idProject	= intval($idProject);

		$table	= 'ext_project_mm_project_person';
		$where	= '	id_project	= ' . $idProject;

		return Todoyu::db()->doDelete($table, $where);
	}



	/**
	 * Remove a person from a project (as project member)
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idPerson
	 * @return	Bool		Success
	 */
	public static function removeProjectPerson($idProject, $idPerson) {
		$idProject	= intval($idProject);
		$idPerson	= intval($idPerson);

		$table	= 'ext_project_mm_project_person';
		$where	= '	id_project	= ' . $idProject . ' AND
					id_person	= ' . $idPerson;

		return Todoyu::db()->doDelete($table, $where) !== 0;
	}



	/**
	 * Add a person to project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idPerson
	 * @param	Integer		$idProjectRole
	 * @param	Array		$extraData
	 * @return	Integer		Link ID
	 */
	public static function addPerson($idProject, $idPerson, $idProjectRole, array $extraData = array()) {
		$idProject		= intval($idProject);
		$idPerson		= intval($idPerson);
		$idProjectRole	= intval($idProjectRole);

		unset($extraData['id']);
		unset($extraData['id_project']);
		unset($extraData['id_role']);
		unset($extraData['id_person']);

		$table	= 'ext_project_mm_project_person';
		$fields	= array(
			'id_project'	=> $idProject,
			'id_person'		=> $idPerson,
			'id_role'		=> $idProjectRole
		);
		$fields	= array_merge($extraData, $fields);

		return Todoyu::db()->addRecord($table, $fields);
	}



	/**
	 * Save project person data and link the persons with the project
	 *
	 * @param	Integer		$idProject
	 * @param	Array		$persons
	 */
	public static function saveProjectPersons($idProject, array $persons) {
		$idProject	= intval($idProject);

		self::removeAllProjectPersons($idProject);

		foreach($persons as $person) {
			self::addPerson($idProject, $person['id'], $person['id_role'], $person);
		}
	}



	/**
	 * Get project ID array by filter
	 *
	 * @param	Integer	$filterSetID
	 * @param	Array	$filterConditions
	 * @param	String	$conjunction
	 * @return	Array
	 */
	public static function getProjectIDsByFilter($idFilterSet = 0, array $filterConditions = array(), $conjunction = 'AND')	{
		$idFilterSet = intval($idFilterSet);

		if( $idFilterSet !== 0 ) {
			$conditions = TodoyuFilterConditionManager::getFilterSetConditions($idFilterSet, false);
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
			'id'				=> 0,
			'date_create'		=> NOW,
			'date_update'		=> NOW,
			'id_person_create'	=> TodoyuAuth::getPersonID(),
			'deleted'			=> 0,
			'title'				=> TodoyuLanguage::getLabel('project.newproject.title'),
			'description'		=> '',
			'status'			=> STATUS_PLANNING,
			'id_company'		=> 0,
			'date_start'		=> NOW,
			'date_end'			=> NOW + 3600 * 24 * 30,
			'date_deadline'		=> NOW + 3600 * 24 * 30
		);

			// Call hook to modify default task data
		$defaultData	= TodoyuHookManager::callHookDataModifier('project', 'projectDefaultData', $defaultData);

		return $defaultData;
	}



	/**
	 * Get data for sub menu entries of currently open projects
	 *
	 * @return	Array
	 */
	public static function getOpenProjectLabels() {
		$entries		= array();

		$openProjectIDs	= TodoyuProjectPreferences::getOpenProjectIDs();
		foreach($openProjectIDs as $idProject) {
			if ( TodoyuProjectRights::isSeeAllowed($idProject) ) {
				$project	= TodoyuProjectManager::getProject($idProject);
				$entries[$idProject]	= $project->getCompany()->getShortLabel() . ' - ' . $project->getTitle();
			}
		}

		return $entries;
	}



	/**
	 * Get project IDs where user can add tasks
	 *
	 * @return	Array
	 */
	public static function getProjectIDsForTaskAdd() {
			// If person can't event add tasks in own projects, there is no need to get the visible projects
		if( ! allowed('project', 'task:addInOwnProjects') ) {
			return array();
		}

			// Get visible projects
		$filter		= new TodoyuProjectFilter();
		$projectIDs	= $filter->getProjectIDs();

		foreach($projectIDs as $index => $idProject) {
			$project	= TodoyuProjectManager::getProject($idProject);

			if( ! allowed('project', 'task:addInAllProjects') ) {
				if( ! $project->isCurrentPersonAssigned() ) {
					unset($projectIDs[$index]);
				}
			}
		}

		return $projectIDs;
	}



	/**
	 * Get quick create project form object
	 *
	 * @param	Integer		$idProject
	 * @return	TodoyuForm
	 */
	public static function getQuickCreateForm($idProject = 0) {
		$idProject	= intval($idProject);

			// Construct form object
		$xmlPath	= 'ext/project/config/form/project.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idProject);

			// Adjust form to needs of quick creation wizard
		$form->setAttribute('action', '?ext=project&amp;controller=quickcreateproject');
		$form->setAttribute('onsubmit', 'return false');
		$form->getFieldset('buttons')->getField('save')->setAttribute('onclick', 'Todoyu.Ext.project.QuickCreateProject.save(this.form)');
		$form->getFieldset('buttons')->getField('cancel')->setAttribute('onclick', 'Todoyu.Popup.close(\'quickcreate\')');

		return $form;
	}



	/**
	 * Lock project
	 *
	 * @param	Integer		$idProject
	 */
	public static function lockProject($idProject, $ext = EXTID_PROJECT) {
		TodoyuLockManager::lock($ext, 'ext_project_project', $idProject);
	}



	/**
	 * Unlock a project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$ext
	 */
	public static function unlockProject($idProject, $ext = EXTID_PROJECT) {
		TodoyuLockManager::unlock($ext, 'ext_project_project', $idProject);
	}

	

	/**
	 * Lock all tasks of a project
	 *
	 * @param	Integer		$idProject
	 */
	public static function lockAllTasks($idProject, $ext = EXTID_PROJECT) {
		$idProject	= intval($idProject);

		$taskIDs	= self::getTaskIDs($idProject);

		foreach($taskIDs as $idTask) {
			TodoyuTaskManager::lockTask($idTask, $ext);
		}
	}

}

?>