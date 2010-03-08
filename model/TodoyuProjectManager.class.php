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
	 * Get quick create project form object
	 *
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
		$form->getFieldset('buttons')->getField('save')->setAttribute('onclick', 'Todoyu.Headlet.QuickCreate.Project.save(this.form)');
		$form->getFieldset('buttons')->getField('cancel')->setAttribute('onclick', 'Todoyu.Popup.close(\'quickcreate\')');

		return $form;
	}



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
		$data['id_person_create']	= personid();
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
			$open		= TodoyuProjectPreferences::getOpenProjectIDs();
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
	 * Check if a person is assigned to a project
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idPerson
	 * @return	Bool
	 */
	public static function isPersonAssigned($idProject, $idPerson = 0) {
		$idProject	= intval($idProject);
		$idPerson	= personid($idPerson);

		$fields	= 'id';
		$table	= 'ext_project_mm_project_person';
		$where	= ' id_project	= ' . $idProject . ' AND
					id_person	= ' . $idPerson;

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

		$ownItems	= TodoyuArray::assure($GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Project']);
		$allowed	= array();

			// Show details
		if( TodoyuProjectRights::canProjectSee($idProject) ) {
			if( $isExpanded ) {
				$allowed['hidedetails'] = $ownItems['hidedetails'];
			} else {
				$allowed['showdetails'] = $ownItems['showdetails'];
			}
		}


			// Modify project
		if( TodoyuProjectRights::canProjectEdit($idProject) ) {
				// Edit
			$allowed['edit'] = $ownItems['edit'];

				// Status
			$allowed['status'] = $ownItems['status'];
			$statuses = TodoyuProjectStatusManager::getProjectStatuses();

			foreach($allowed['status']['submenu'] as $key => $status) {
				if( ! in_array($key, $statuses) ) {
					unset($allowed['status']['submenu'][$key]);
				}
			}

				// Delete
			$allowed['delete'] = $ownItems['delete'];
		}


		if( allowed('project', 'task:addInAllProjects') || (allowed('project', 'task:addInOwnProjects') && TodoyuProjectManager::isPersonAssigned($idProject)) ) {
				// Add task
			$allowed['addtask'] = $ownItems['addtask'];
				// Add container
			$allowed['addcontainer'] = $ownItems['addcontainer'];
		}

		return array_merge_recursive($items, $allowed);
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
			$where	= '	p.id IN(' . $projectList . ') AND
						(p.id_company = 0 OR p.id_company = c.id) AND
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
			$companyLabel	= trim($project['companyShort']) === '' ? TodoyuDiv::cropText($project['companyFull'], 10, '..', false) : $project['companyShort'];
			$tabs[] = array(
				'id'		=> $project['id'],
				'label'		=> TodoyuDiv::cropText($companyLabel. ': ' . $project['title'], 23, '..', false)
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
	 * Get persons which are connected with the project
	 *
	 * @param	Integer		$idProject
	 * @return	Array
	 */
	public static function getProjectPersons($idProject) {
		$idProject	= intval($idProject);

			// Get project persons
		$fields	= '	pe.*,
					pe.id as id_person,
					pr.id as id_role,
					pr.rolekey,
					pr.title as rolelabel,
					mmpp.id_project,
					mmpp.comment';
		$table	= '	ext_contact_person pe,
					ext_project_role pr,
					ext_project_mm_project_person mmpp';
		$where	= '	mmpp.id_person	= pe.id AND
					mmpp.id_project	= ' . $idProject . ' AND
					mmpp.id_role	= pr.id AND
					pe.deleted		= 0';
		$group	= '	mmpp.id';
		$order	= '	pe.lastname,
					pe.firstname';

		$persons= Todoyu::db()->getArray($fields, $table, $where, $group, $order);

			// Get company information
		foreach($persons as $index => $person) {
			$persons[$index]['company'] = TodoyuPersonManager::getPersonCompanyRecords($person['id']);
		}

		return $persons;
	}


	/**
	 * Get project person label (name + projectrole)
	 *
	 * @param	Integer		$idPerson
	 * @param	Integer		$idProject
	 * @param	Integer		$idProjectrole$idProjectrole
	 * @return	String
	 */
	public static function getProjectPersonLabel($idPerson, $idProject, $idProjectrole = 0) {
		$idPerson		= intval($idPerson);
		$idProject		= intval($idProject);
		$idProjectrole	= intval($idProjectrole);

		$label	= TodoyuPersonManager::getLabel($idPerson);

		if ( $idProjectrole === 0 ) {
			$label	.= ' - ' . self::getProjectroleLabel($idPerson, $idProject);
		} else {
			$label	.= ' - ' . TodoyuProjectroleManager::getLabel($idProjectrole);
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
	 * Get projectrole of a person in a project
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
		$where		= '	mmpp.id_project	= ' . $idProject . ' AND
						mmpp.id_person	= ' . $idPerson . ' AND
						mmpp.id_role	= pr.id';

		$idProjectrole	= Todoyu::db()->getFieldValue($field, $tables, $where);

		return TodoyuProjectroleManager::getProjectrole($idProjectrole);
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
	 * @param	Integer		$idProjectrole
	 * @return	Integer		Link ID
	 */
	public static function addPerson($idProject, $idPerson, $idProjectrole, array $extraData = array()) {
		$idProject		= intval($idProject);
		$idPerson		= intval($idPerson);
		$idProjectrole	= intval($idProjectrole);

		unset($extraData['id']);

		$table	= 'ext_project_mm_project_person';
		$fields	= array(
			'id_project'	=> $idProject,
			'id_person'		=> $idPerson,
			'id_role'		=> $idProjectrole
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
			self::addPerson($idProject, $person['id_person'], $person['id_role'], $person);
		}
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
			'id_person_create'=> TodoyuAuth::getPersonID(),
			'deleted'		=> 0,
			'title'			=> TodoyuLanguage::getLabel('project.newproject.title'),
			'description'	=> '',
			'status'		=> STATUS_PLANNING,
			'id_company'	=> 0,
			'date_start'	=> NOW,
			'date_end'		=> NOW + 3600*24*30,
			'date_deadline'	=> NOW + 3600*24*30
		);

			// Call hook to modify default task data
		$defaultData	= TodoyuHookManager::callHookDataModifier('project', 'projectDefaultData', $defaultData);

		return $defaultData;
	}



	/**
	 * Get data for submenu entries of currently open projects
	 *
	 * @return	Array
	 */
	public static function getOpenProjectLabels() {
		$entries		= array();

		$openProjectIDs	= TodoyuProjectPreferences::getOpenProjectIDs();

		foreach($openProjectIDs as $idProject) {
			$project	= TodoyuProjectManager::getProject($idProject);

			$entries[$idProject]	= $project->getCompany()->getShortLabel() . ' - ' . $project->getTitle();
		}

		return $entries;
	}
}

?>