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
 * Task Manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuTaskManager {

	/**
	 * @var	Array		Expanded tasks in project view
	 */
	private static $expandedTaskIDs = null;

	/**
	 * @var	String		Default ext table for database requests
	 */
	const TABLE = 'ext_project_task';

	/**
	 * @var	Array		Installed tabs for tasks
	 */
	private static $tabs = null;



	/**
	 * Get task quick create form object
	 *
	 * @return	TodoyuForm				form object
	 */
	public static function getQuickCreateForm() {
			// Create empty record of type task cache first. so hooks know what kind of task it is
		self::createNewTaskWithDefaultsInCache(0, 0, TASK_TYPE_TASK);

			// Construct form object
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Adjust for quick create
		$form->removeField('id_parenttask', true);
		$form->removeHiddenField('id_project');

			// Load form with extra field data
		$xmlPathInsert	= 'ext/project/config/form/field-id_project.xml';
		$insertForm		= TodoyuFormManager::getForm($xmlPathInsert);

			// If person can add tasks in all project, show auto-completion field, else only a select element
		if( allowed('project', 'task:addInAllProjects') ) {
			$field	= $insertForm->getField('id_project_ac');
		} else {
			$field	= $insertForm->getField('id_project_select');
		}

			// Add field to form
		$form->getFieldset('left')->addField('id_project', $field, 'after:title');

			// Change form action and button functions
		$form->setAttribute('action', '?ext=project&amp;controller=quickcreatetask');
		$form->getField('save')->setAttribute('onclick', 'Todoyu.Ext.project.QuickCreateTask.save(this.form)');
		$form->getField('cancel')->setAttribute('onclick', 'Todoyu.Popup.close(\'quickcreate\')');

			// Load task default data
		$formData	= self::getTaskDefaultData();

		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, 0, array('form'=>$form));

			// Ensure the preset project allows for adding tasks
		if( ! TodoyuTaskRights::isAddInProjectAllowed($formData['id_project']) ) {
			$formData['id_project']	= 0;
		}

		$form->setFormData($formData);

		return $form;
	}



	/**
	 * Get object of a task.
	 *
	 * @param	Integer		$idTask		Task ID
	 * @return	TodoyuTask
	 */
	public static function getTask($idTask) {
		return TodoyuRecordManager::getRecord('TodoyuTask', $idTask);
	}



	/**
	 * Get task record data
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskData($idTask) {
		return TodoyuRecordManager::getRecordData(self::TABLE, $idTask);
	}



	/**
	 * Get task ID by full task number
	 *
	 * @param	String		$fullTaskNumber			Task number separated by point (.)
	 * @return	Integer		0 if task not found
	 */
	public static function getTaskIDByTaskNumber($fullTaskNumber) {
		$idTask	= 0;
		$parts	= TodoyuArray::intExplode('.', $fullTaskNumber, true, true);

		if( sizeof($parts) === 2 ) {
			$field	= 'id';
			$table	= self::TABLE;
			$where	= '		id_project	= ' . $parts[0] .
					  ' AND	tasknumber	= ' . $parts[1];
			$limit	= 1;

			$foundID= Todoyu::db()->getFieldValue($field, $table, $where, '', '', $limit);

			if( $foundID !== false ) {
				$idTask = intval($foundID);
			}
		}

		return $idTask;
	}



	/**
	 * Get a number of tasks as array
	 *
	 * @param	Array	$taskIDs
	 * @param	String	$order
	 * @return	Array
	 */
	public static function getTasks(array $taskIDs, $order = 'id') {
		$taskIDs= TodoyuArray::intval($taskIDs, true, true);
		$tasks	= array();

		if( sizeof($taskIDs) > 0 ) {
			$where	= 'id IN(' . implode(',', $taskIDs) . ')';
			$tasks	= TodoyuRecordManager::getAllRecords(self::TABLE, $where, $order);
		}

		return $tasks;
	}



	/**
	 * Save a task. If a task number is given, the task will be updated, else
	 * a new task will be created
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function saveTask(array $data) {
		$xmlPath	= 'ext/project/config/form/task.xml';
		$idTask		= intval($data['id']);

		if( $idTask === 0 ) {
				// Create new task with necessary data
			$firstData	= array(
				'id_project'	=> intval($data['id_project']),
				'id_parenttask'	=> intval($data['id_parenttask'])
			);

			$idTask = self::addTask($firstData);
		}

			// Check for type
		if( empty($data['type']) ) {
			$data['type'] = TASK_TYPE_TASK;
		} elseif( $data['type'] == TASK_TYPE_CONTAINER ) {
				// Init container status (none) 
			$data['status'] = 0;
		}

			// Call hooked save data functions
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idTask);

		self::updateTask($idTask, $data);
		self::removeTaskFromCache($idTask);

		return $idTask;
	}



	/**
	 * Update task
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$data
	 * @return	Boolean
	 */
	public static function updateTask($idTask, array $data) {
		$idTask	= intval($idTask);

		self::removeTaskFromCache($idTask);

		return TodoyuRecordManager::updateRecord(self::TABLE, $idTask, $data);
	}



	/**
	 * Add a new task
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addTask(array $data = array()) {
			// Create task number
		$idProject	= intval($data['id_project']);
		$data['tasknumber'] = TodoyuProjectManager::getNextTaskNumber($idProject);

			// Create sorting flag
		$idParent	= intval($data['id_parenttask']);
		$data['sorting']	= self::getNextSortingPosition($idProject, $idParent);

		$data	= self::setDefaultValuesForNotAllowedFields($data);

		return TodoyuRecordManager::addRecord(self::TABLE, $data);
	}



	/**
	 * Get next sorting position for a new task. For every sub task, sorting starts new
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idParentTask
	 * @return	Integer
	 */
	public static function getNextSortingPosition($idProject, $idParentTask) {
		$idProject		= intval($idProject);
		$idParentTask	= intval($idParentTask);

		$field	= 'MAX(sorting) as sorting';
		$table	= self::TABLE;
		$where	= '		id_project		= ' . $idProject
				. ' AND	id_parenttask	= ' . $idParentTask;
		$group	= 'sorting';
		$order	= 'sorting DESC';
		$limit	= 1;

		$maxSorting	= Todoyu::db()->getFieldValue($field, $table, $where, $group, $order, $limit, 'sorting'); // getRecordByQuery($fields, $table, $where, $group);

		if( $maxSorting === false ) {
			return 0;
		} else {
			return intval($maxSorting) + 1;
		}
	}



	/**
	 * Delete a task
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$deleteSubTasks
	 */
	public static function deleteTask($idTask, $deleteSubTasks = true) {
		$data	= array(
			'deleted'		=> 1,
			'date_update'	=> NOW
		);

		self::updateTask($idTask, $data);

			// Delete all sub tasks
		if( $deleteSubTasks ) {
			$allSubTaskIDs	= self::getAllSubTaskIDs($idTask);

			if( sizeof($allSubTaskIDs) > 0 ) {
				$where	= 'id IN(' . implode(',', $allSubTaskIDs) . ')';
				$update	= array(
					'deleted'		=> 1,
					'date_update'	=> NOW
				);

				Todoyu::db()->doUpdate(self::TABLE, $where, $update);
			}
		}
	}



	/**
	 * Delete all tasks of given project
	 *
	 * @param	Integer		$idProject
	 */
	public static function deleteProjectTasks($idProject) {
		$idProject	= intval($idProject);

		$where	= 'id_project = ' . $idProject;
		$data	= array(
			'deleted'	=> 1
		);

		TodoyuRecordManager::updateRecords(self::TABLE, $where, $data);
	}



	/**
	 * Add a new container
	 *
	 * @param	Array		$data
	 * @return	Integer		New container ID
	 */
	public static function addContainer(array $data) {
		$data['type']				= TASK_TYPE_CONTAINER;
//		$data['id_person_assigned']	= personid();

		return self::addTask($data);
	}



	/**
	 * Update task status only (shortcut for updateTask)
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$newStatus
	 */
	public static function updateTaskStatus($idTask, $newStatus) {
		$data = array(
			'status' => intval($newStatus)
		);

		$data	= TodoyuHookManager::callHookDataModifier('project', 'onTaskStatusChanged', $data, array('idTask' => $idTask));

		self::updateTask($idTask, $data);
	}


	/**
	 * Update status of multiple tasks
	 *
	 * @param	Array		$taskIDs
	 * @param	Integer		$status
	 */
	public static function updateTaskStatuses(array $taskIDs, $status) {
		$update	= array(
			'status'	=> intval($status)
		);

		self::updateTasks($taskIDs, $update);
	}



	/**
	 * Update multiple tasks
	 *
	 * @param	Array		$taskIDs
	 * @param	Array		$fieldValues
	 */
	public static function updateTasks(array $taskIDs, array $fieldValues) {
		$taskIDs= TodoyuArray::intval($taskIDs);

		if( sizeof($taskIDs) > 0 ) {
			$where	= 'id IN(' . implode(',', $taskIDs) . ')';

			Todoyu::db()->doUpdate(self::TABLE, $where, $fieldValues);
		}
	}



	/**
	 * Get the project ID of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getProjectID($idTask) {
		$idTask	= intval($idTask);

		return self::getTask($idTask)->getProjectID();
	}



	/**
	 * Get the project object of a task
	 *
	 * @param	Integer		$idTask
	 * @return	TodoyuProject
	 */
	public static function getProject($idTask) {
		$idProject	= self::getProjectID($idTask);

		return TodoyuProjectManager::getProject($idProject);
	}



	/**
	 * Get the context menu items for a task
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$items
	 * @return	Array		Config array for context menu
	 */
	public static function getContextMenuItems($idTask, array $items) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);
		$project= $task->getProject();
		$allowed= array();

		if( $task->isTask() ) {
			$ownItems	=& Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Task'];
		} elseif( $task->isContainer() ) {
			$ownItems	=& Todoyu::$CONFIG['EXT']['project']['ContextMenu']['Container'];
		}

		if( $task->isTask() || $task->isContainer() ) {
				// Add project back-link if not in project area
			if( AREA !== EXTID_PROJECT ) {
				if( TodoyuProjectRights::isSeeAllowed($task->getProjectID()) ) {
					$allowed['showinproject'] = $ownItems['showinproject'];
				}
			}

				// Edit
			if( $task->isEditable() ) {
				$allowed['edit'] = $ownItems['edit'];
			}

				// Actions (with sub menu)
			$allowed['actions'] = $ownItems['actions'];
			unset($allowed['actions']['submenu']);

				// Copy
			if( allowed('project', 'task:addInOwnProjects') ) {
				$allowed['actions']['submenu']['copy']	= $ownItems['actions']['submenu']['copy'];
			}

				// Cut
			if( $task->isEditable() ) {
				$allowed['actions']['submenu']['cut']	= $ownItems['actions']['submenu']['cut'];
			}

				// Clone
			if( ! $task->isLocked() && TodoyuTaskRights::isAddAllowed($idTask) ) {
				$allowed['actions']['submenu']['clone']	= $ownItems['actions']['submenu']['clone'];
			}

				// Delete
			if( $task->isEditable() ) {
				$allowed['actions']['submenu']['delete'] = $ownItems['actions']['submenu']['delete'];
			}

				// Add (with sub menu)
			$allowed['add'] = $ownItems['add'];
			unset($allowed['add']['submenu']);

			if( !$project->isLocked() && TodoyuTaskRights::isAddAllowed($idTask) ) {
					// Add sub task
				$allowed['add']['submenu']['task'] = $ownItems['add']['submenu']['task'];
					// Add sub container
				$allowed['add']['submenu']['container'] = $ownItems['add']['submenu']['container'];
			}

				// Status
			if( $task->isTask() && ! $task->isLocked() && allowed('project', 'task:editStatus') && TodoyuTaskRights::isStatusChangeAllowed($idTask) ) {
				$allowed['status'] = $ownItems['status'];

				$statuses = TodoyuTaskStatusManager::getStatuses('changeto');

				foreach($allowed['status']['submenu'] as $key => $status) {
					if( ! in_array($key, $statuses) ) {
						unset($allowed['status']['submenu'][$key]);
					}
				}
			}
		}

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Remove empty parent menus if they have no sub entries
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function removeEmptyContextMenuParents($idTask, array $items) {
			// Remove actions if empty
		if( ! is_array($items['actions']['submenu']) ) {
			unset($items['actions']);
		}

			// Remove add if empty
		if( ! is_array($items['add']['submenu']) || sizeof($items['add']['submenu']) === 0 ) {
			unset($items['add']);
		}

			// Remove status if empty
		if( ! is_array($items['status']['submenu']) ) {
			unset($items['status']);
		}

		return $items;
	}



	/**
	 * Get the IDs of all sub tasks of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubTaskIDs($idTask) {
		$idTask	= intval($idTask);

		$filters	= TodoyuProjectManager::getTaskTreeFilterStruct();

		$filters[]	= array(
			'filter'=> 'parentTask',
			'value'	=> $idTask
		);

		$taskFilter	= new TodoyuTaskFilter($filters);
		$taskIDs	= $taskFilter->getTaskIDs();

		return $taskIDs;
	}



	/**
	 * Get ALL sub tasks of a task (the whole tree, instead only the direct children)
	 * Get also sub-sub-...-tasks
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAllSubTaskIDs($idTask) {
		$idTask		= intval($idTask);
		$subTasks	= array();

		$field	= 'id';
		$table	= self::TABLE;
		$whereF	= '		id_parenttask IN(%s)
					AND	deleted	= 0';

		$where	= sprintf($whereF, $idTask);
		$newTasks	= Todoyu::db()->getColumn($field, $table, $where);

		while( sizeof($newTasks) > 0 ) {
			$subTasks	= array_merge($subTasks, $newTasks);
			$where		= sprintf($whereF, implode(',', $newTasks));
			$newTasks	= Todoyu::db()->getColumn($field, $table, $where);
		}

		return $subTasks;
	}



	/**
	 * Get estimated workload of task
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getEstimatedWorkload($idTask) {
		$idTask	= intval($idTask);

		return self::getTask($idTask)->get('estimated_workload');
	}



	/**
	 * Get direct sub tasks (as data array) of given task (1 level)
	 *
	 * @param	Integer		$idTask
	 * @param	String		$order
	 * @return	Array
	 */
	public static function getSubTasks($idTask, $order = 'date_create') {
		$idTask	= intval($idTask);

		if( $idTask === 0 )	{
			return array();
		}

		$where	=	'		id_parenttask	= ' . $idTask
				  . ' AND	deleted			= 0';

		return TodoyuRecordManager::getAllRecords(self::TABLE, $where, $order);
	}



	/**
	 * Get IDs of direct (1 level) sub tasks of given task
	 *
	 * @param	Integer		$idTask
	 * @param	String		$order
	 * @return	Array
	 */
	public static function getSubTasksIDs($idTask, $order = 'date_create') {
		$idTask	= intval($idTask);

		if( $idTask === 0 )	{
			return array();
		}

		$field	= 'id';
		$where	=	'		id_parenttask	= ' . $idTask
				  . ' AND	deleted			= 0';

		return Todoyu::db()->getColumn($field, self::TABLE, $where, '', $order);
	}



	/**
	 * Check whether a task has sub tasks
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function hasSubTasks($idTask) {
		$idTask	= intval($idTask);

		$subTaskIDs	= self::getSubTaskIDs($idTask);

		return sizeof($subTaskIDs) > 0 ;
	}



	/**
	 * Check whether a task is a sub task of a task.
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idParent
	 * @param	Boolean		$checkDeep		TRUE: check all levels, FALSE: check only direct childs
	 * @return	Boolean
	 */
	public static function isSubTaskOf($idTask, $idParent, $checkDeep = false) {
		$idTask		= intval($idTask);
		$idParent	= intval($idParent);

		if( $checkDeep ) {
			$subTasks	= self::getAllSubTaskIDs($idParent);
		} else {
			$subTasks	= self::getSubTaskIDs($idParent);
		}

		return in_array($idTask, $subTasks);
	}



	/**
	 * Check whether task has a parent
	 *
	 * @param	Integer		$idTask
	 * @return 	Boolean
	 */
	public static function hasParentTask($idTask) {
		$idTask	= intval($idTask);

		$field	= 'id_parenttask';
		$table	= self::TABLE;
		$where	= 'id = ' . $idTask;

		$task	= Todoyu::db()->getRecordByQuery($field, $table, $where);

		return intval($task['id_parenttask']) > 0;
	}



	/**
	 * Check whether ending date of given task is in the past
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isEnddateExceeded($idTask) {
		$idTask	= intval($idTask);

		$endDate= self::getTask($idTask)->getEndDate();

		return $endDate < NOW;
	}



	/**
	 * Check whether deadline of given task is in the past
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isDeadlineExceeded($idTask) {
		$idTask	= intval($idTask);

		$deadline= self::getTask($idTask)->getDeadlineDate();

		return $deadline < NOW;
	}



	/**
	 * Get task tabs config array (labels parsed)
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$evalLabel		If true, all labels with a function reference will be parsed
	 * @return	Array
	 */
	public static function getTabs($idTask, $evalLabel = true) {
		if( is_null(self::$tabs) ) {
			$tabs = TodoyuArray::assure(Todoyu::$CONFIG['EXT']['project']['task']['tabs']);
			self::$tabs = TodoyuArray::sortByLabel($tabs);
		}

		$tabs = self::$tabs;

		if( $evalLabel ) {
			foreach($tabs as $index => $tab) {
				$labelFunc	= $tab['label'];
				$tabs[$index]['label'] = TodoyuFunction::callUserFunction($labelFunc, $idTask);
			}
		}

		return $tabs;
	}



	/**
	 * Get a tab configuration
	 *
	 * @param	String		$tabKey
	 * @return	Array
	 */
	public static function getTabConfig($tabKey) {
		return Todoyu::$CONFIG['EXT']['project']['task']['tabs'][$tabKey];
	}



	/**
	 * Get the tab which is active by default (if no preference is stored)
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function getDefaultTab($idTask) {
		$tabs	= self::getTabs($idTask, false);
		$first	= array_shift($tabs);

		return $first['id'];
	}



	/**
	 * Register a task tab
	 *
	 * @param	String		$idTab					Tab identifier
	 * @param	String		$labelFunction			Function which renders the label or just a label string
	 * @param	String		$contentFunction		Function which renders the content
	 * @param	Integer		$position
	 */
	public static function addTaskTab($idTab, $labelFunction, $contentFunction, $position = 100) {
		Todoyu::$CONFIG['EXT']['project']['task']['tabs'][$idTab] = array(
			'id'		=> $idTab,
			'label'		=> $labelFunction,
			'position'	=> intval($position),
			'content'	=> $contentFunction
		);
	}



	/**
	 * Get all persons which are somehow connected with this task
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$withAccount
	 * @return	Array
	 */
	public static function getTaskPersons($idTask, $withAccount = false) {
		$idTask	= intval($idTask);

		$fields	= ' p.*';
		$tables	= ' ext_contact_person p,
					ext_project_task t';
		$where	= '	t.id				= ' . $idTask . '
					AND	(
							t.id_person_create	= p.id
						OR	t.id_person_assigned= p.id
						OR	t.id_person_owner	= p.id
					)';
		$group	= 'p.id';
		$order	= 'p.lastname, p.firstname';

			// Add check for active account
		if( $withAccount ) {
			$where .= ' AND p.active = 1';
		}

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order);
	}



	/**
	 * Get task owner
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskOwner($idTask) {
		$idTask	= intval($idTask);

		$fields	= ' u.*';
		$tables	= ' ext_project_task t,
					ext_contact_person u';
		$where	= '		t.id	= ' . $idTask .
				  ' AND	u.id	= t.id_person_owner';

		return Todoyu::db()->getArray($fields, $tables, $where);
	}



	/**
	 * Get all task data informations.
	 * Information from all extensions are merged, labels are parsed and the list is sorted
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskInfos($idTask) {
		$idTask	= intval($idTask);
		$data	= array();

		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskdata', $data, array($idTask));

		$data = TodoyuArray::sortByLabel($data, 'position');

		return $data;
	}



	/**
	 * Get info array for a task. This array contains the data from getTemplateData()
	 * of the task and the data provided by all registered hooks
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$infoLevel
	 * @return	Array
	 */
	public static function getTaskInfoArray($idTask, $infoLevel = 0) {
		$idTask		= intval($idTask);
		$infoLevel	= intval($infoLevel);
		$task		= self::getTask($idTask);

		$data	= $task->getTemplateData($infoLevel);

			// Call hooks to add extra data (filled in in the data array)
		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskinfo', $data, array($idTask, $infoLevel));

		return $data;
	}



	/**
	 * Attributes for task data list
	 *
	 * @param	Array		$data
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskDataAttributes(array $data, $idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuTaskManager::getTask($idTask);
		$taskData	= $task->getTemplateData(2);

		$info	= array();

			// Attributes which are only for tasks (not relevant for containers)
		if( $task->isTask() ) {
				// Date start
			if( $taskData['date_start'] > 0 && (Todoyu::person()->isInternal() || TodoyuAuth::isAdmin()) ) {
				$info['date_start']	= array(
					'label'		=> 'LLL:task.attr.date_start',
					'value'		=> TodoyuTime::format( $taskData['date_start'], 'date'),
					'position'	=> 10,
					'className'	=> 'sectionStart'
				);
			}

				// Date end (if set) (internal deadline)
			if( $taskData['date_end'] > 0 && (Todoyu::person()->isInternal() || TodoyuAuth::isAdmin()) ) {
				$formatEnd	= date('F', $data['date_end']) == 0 ? 'date' : 'datetime';
				$info['date_end']	= array(
					'label'	=> 'LLL:task.attr.date_end',
					'value'	=> TodoyuTime::format($taskData['date_end'], $formatEnd),
					'position'	=> 20,
					'className'	=> TodoyuTaskManager::isEnddateExceeded($idTask) ? 'red' : ''
				);
			}
				// Date deadline
			if( $taskData['date_deadline'] > 0 ) {
				$formatDeadline	= date('s', $taskData['date_deadline']) === '00' ? 'date' : 'datetime';
				$info['date_deadline']	= array(
					'label'	=> 'LLL:task.attr.date_deadline',
					'value'	=> TodoyuTime::format($taskData['date_deadline'], $formatDeadline),
					'position'	=> 30,
					'className'	=> TodoyuTaskManager::isDeadlineExceeded($idTask) ? 'red' : ''
				);
			}

				// Status
			$info['status']	= array(
				'label'		=> 'LLL:core.status',
				'value'		=> $taskData['statuslabel'],
				'position'	=> 50,
				'className'	=> ''
			);

				// Person assigned
			if( intval($taskData['person_assigned']['id']) !== 0 && (Todoyu::person()->isInternal() || TodoyuAuth::isAdmin()) ) {
				$info['person_assigned']	= array(
					'label'		=> 'LLL:task.attr.person_assigned',
					'value'		=> TodoyuPersonManager::getLabel($taskData['person_assigned']['id']),
					'position'	=> 60,
					'className'	=> 'sectionStart ' . ( $taskData['is_acknowledged'] === '1' ? 'acknowledged' : 'unread')
				);
			}

				// Work type
			if( ! empty($taskData['worktype']) ) {
				$info['worktype'] = array(
					'label'		=> 'LLL:task.attr.worktype',
					'value'		=> $taskData['worktype']['title'],// 'Internes / Administration',
					'position'	=> 90,
					'className'	=> ''
				);
			}

				// Estimated workload
			if( $task->hasEstimatedWorkload() ) {
				$info['estimated_workload']	= array(
					'label'	=> 'LLL:task.attr.estimated_workload',
					'value'	=> TodoyuTime::sec2hour($task->getEstimatedWorkload()),
					'position'	=> 100,
					'className'	=> 'sectionStart'
				);
			}
		}

			// Attributes of tasks and containers

			// Person owner
		$idPersonOwner	= intval($taskData['person_owner']['id']);
		$idPersonCreator= intval($taskData['id_person_create']);

		if( $idPersonOwner !== 0 ) {
			$info['person_owner'] = array(
				'label'		=> $task->isContainer() ? 'LLL:task.container.attr.person_owner' : 'LLL:task.attr.person_owner',
				'value'		=> TodoyuPersonManager::getLabel($idPersonOwner),
				'position'	=> 70,
				'className'	=> 'sectionStart'
			);
		}

			// Task creator: Different person owns / created task? have both displayed
		if( $idPersonCreator !== 0 && $idPersonOwner !== $idPersonCreator ) {
			$info['person_create'] = array(
				'label'		=> 'LLL:task.attr.person_create',
				'value'		=> TodoyuPersonManager::getLabel($idPersonCreator),
				'position'	=> 65,
				'className'	=> ''
			);
		}

			// Public
		$info['is_public']	= array(
			'label'	=> $task->isContainer() ? 'LLL:task.container.attr.is_public' : 'LLL:task.attr.is_public',
			'value'	=> Label('LLL:task.attr.is_public.' . ($taskData['is_public'] ? 'public' : 'private') . ($task->isContainer() ? '.container' : '')) ,
			'position'	=> 110,
			'className'	=> ''
		);

			// Date create
		$info['date_create']	= array(
			'label'	=> 'LLL:task.attr.date_create',
			'value'	=> TodoyuTime::format($taskData['date_create'], 'datetime'),
			'position'	=> 190,
			'className'	=> ''
		);

		$data	= array_merge($data, $info);

		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskdataattributes', $data, array($idTask));

		return $data;
	}



	/**
	 * Add container info to task data
	 *
	 * @param	Array		$taskData
	 * @param	Integer		$idTask
	 * @param	Integer		$infoLevel
	 * @return	Array
	 */
	public static function addContainerInfoToTaskData($taskData, $idTask, $infoLevel) {
		$idTask		= intval($idTask);
		$task		= self::getTask($idTask);

			// Add special CSS class for containers
		if( $task->isContainer() ) {
			$taskData['class'] .= ' container';
		}

		return $taskData;
	}



	/**
	 * Get all info icons
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAllTaskIcons($idTask) {
		$idTask	= intval($idTask);
		$icons	= array();

		$icons	= TodoyuHookManager::callHookDataModifier('project', 'taskIcons', $icons, array($idTask));

		$icons	= TodoyuArray::sortByLabel($icons, 'position');

		return $icons;
	}



	/**
	 * Get all task header extras
	 * This extras will be displayed between the task label and the task number
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAllTaskHeaderExtras($idTask) {
		$idTask	= intval($idTask);
		$extras	= array();

		$extras	= TodoyuHookManager::callHookDataModifier('project', 'taskHeaderExtras', $extras, array($idTask));

		$extras	= TodoyuArray::sortByLabel($extras, 'position');

		return $extras;
	}



	/**
	 * Get project task info icons
	 *
	 * @param	Array		$icons
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskIcons(array $icons, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

			// Task-only information (not relevant for containers)
		if( $task->isTask() ) {
				// 'dateover': end date or deadline passed
			if( $task->getStatus() != STATUS_CLEARED && (($task->getEndDate() > 0 && $task->getEndDate() < NOW) || $task->getDeadlineDate() < NOW) ) {
				$icons['dateover']= array(
					'id'		=> 'task-' . $idTask . '-dateover',
					'class'		=> 'dateover',
					'label'		=> 'LLL:task.attr.dateover',
					'position'	=> 10
				);
			}
		}

			// Add container icon
		if( $task->isContainer() ) {
			$icons['container'] = array(
				'id'		=> 'task-' . $idTask . '-container',
				'class'		=> 'taskcontainer',
				'label'		=> 'LLL:task.type.container',
				'position'	=> 10
			);
		}

			// Add public icon for internals
		if( $task->isPublic() && (Todoyu::person()->isInternal() || TodoyuAuth::isAdmin()) ) {
			$icons['public'] = array(
				'id'		=> 'task-' . $idTask . '-public',
				'class'		=> 'isPublic',
				'label'		=> 'LLL:task.attr.is_public.public' . ($task->isContainer() ? '.container' : ''),
				'position'	=> 80
			);
		}

			// Is acknowledged?
		if( $task->isTask() && ! $task->isAcknowledged() && $task->isCurrentPersonAssigned() ) {
			$icons['notacknowledged'] = array(
				'id'		=> 'task-' . $idTask . '-notacknowledged',
				'class'		=> 'notAcknowledged',
				'label'		=> 'LLL:task.attr.notAcknowledged',
				'onclick'	=> 'Todoyu.Ext.project.Task.setAcknowledged(event, ' . $idTask . ')',
				'position'	=> 100
			);
		}

			// Locked (not editable)
		if( $task->isLocked() ) {
			$icons['locked'] = array(
				'id'		=> 'task-' . $idTask . '-locked',
				'class'		=> 'locked',
				'label'		=> 'LLL:task.attr.locked',
				'position'	=> 150
			);
		}

		return $icons;
	}



	/**
	 * Remove a task from cache (only necessary if the task has been loaded from database
	 * and updated after in the same request and needs to be loaded again
	 *
	 * @param	Integer		$idTask
	 */
	public static function removeTaskFromCache($idTask) {
		$idTask	= intval($idTask);

		TodoyuRecordManager::removeRecordCache('TodoyuTask', $idTask);
		TodoyuRecordManager::removeRecordQueryCache(self::TABLE, $idTask);
	}



	/**
	 * Set task acknowledged
	 *
	 * @param	Integer		$idTask
	 */
	public static function setTaskAcknowledged($idTask) {
		$idTask	= intval($idTask);

		if( self::getTask($idTask)->isCurrentPersonAssigned() )	{
			$update	= array(
				'is_acknowledged' => 1
			);

			self::updateTask($idTask, $update);
		}
	}



	/**
	 * Get task auto-completion label
	 *
	 * @param	Integer	$idTask
	 * @return	String
	 */
	public static function getAutocompleteLabel($idTask) {
		$idTask	= intval($idTask);
		$label	= '';

		if( $idTask > 0 ) {
			$task	= self::getTask($idTask);
			$label	= '[' . $task->getTaskNumber(true) . '] ' . $task->getTitle();
		}

		return $label;
	}



	/**
	 * Get tasks in given timespan
	 * If timestamp of start/end == 0: don't use it (there by this method can be used as well to query for tasks before / after a given timestamp)
	 * If personIDs given:	limit to tasks assigned to given persons
	 * If statuses given:	limit to tasks with given statuses
	 *
	 * @param	Integer		$start
	 * @param	Integer		$end
	 * @param	Array		$statusIDs
	 * @param	Array		$personIDs		(id_person_assigned)
	 * @param	String		$limit
	 * @param	Boolean		$getContainers
	 * @return	Array
	 */
	public static function getTasksInTimeSpan($start = 0, $end = 0, array $statusIDs = array(), array $personIDs = array(), $limit = '', $getContainers = false) {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= self::getTasksInTimeSpanWhereClause($start, $end, $statusIDs, $personIDs, $getContainers);
		$order	= 'date_start';
		$index	= 'id';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit, $index);
	}



	/**
	 * Get earliest starting task of given person, optionally filtered by status
	 *
	 * @param	Integer		$idPerson
	 * @param	Array		$statusIDs
	 * @return	Array
	 */
	public static function getEarliestStartingTaskOfPerson($idPerson, array $statusIDs = array()) {
		$idPerson	= intval($idPerson);
		$statusIDs	= TodoyuArray::intval($statusIDs);

		$field	= '*';
		$table	= self::TABLE;

		$where	= '		deleted = 0 '
				. ' AND	date_start > 0 '
				. ' AND	id_person_assigned = ' . $idPerson;
		$where .= ( count($statusIDs) > 0 ) ? ' AND status IN (' . implode(',', $statusIDs) . ') ' : '';

		$order	= 'date_start ASC';
		$limit	= '1';

		$rows	= Todoyu::db()->getArray($field, $table, $where, '', $order, $limit);
		return $rows[0];
	}



	/**
	 * Get latest ending task of given person, optionally filtered by status
	 *
	 * @param	Integer		$idPerson
	 * @param	Array		$statusIDs
	 * @return	Array
	 */
	public static function getLatestEndingTaskOfPerson($idPerson, array $statusIDs = array()) {
		$idPerson	= intval($idPerson);
		$statusIDs	= TodoyuArray::intval($statusIDs);

		$field	= '*';
		$table	= self::TABLE;

		$where	= '		deleted = 0'
				. ' AND	id_person_assigned = ' . $idPerson;
		$where .= ( count($statusIDs) > 0 ) ? ' AND status IN (' . implode(',', $statusIDs) . ') ' : '';

		$order	= 'date_end DESC';
		$limit	= '1';

		$rows	= Todoyu::db()->getArray($field, $table, $where, '', $order, $limit);
		return $rows[0];
	}



	/**
	 * Get earliest starting one of the tasks intersecting the given timespan
	 *
	 * @param	Integer		$start
	 * @param	Integer		$end
	 * @param	Array		$statusIDs
	 * @param	Array		$personIDs		(id_person_assigned)
	 * @return	Array
	 */
	public static function getEarliestTaskInTimespan($start = 0, $end = 0, array $statusIDs = array(), array $personIDs = array()) {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= self::getTasksInTimeSpanWhereClause($start, $end, $statusIDs, $personIDs);
		$order	= 'date_start ASC';

		return Todoyu::db()->getRecordByQuery($fields, $table, $where, '', $order);
	}



	/**
	 * Get latest ending one of the tasks intersecting the given timespan
	 *
	 * @param	Integer		$start
	 * @param	Integer		$end
	 * @param	Array		$statusIDs
	 * @param	Array		$personIDs		(id_person_assigned)
	 * @return	Array
	 */
	public static function getLatestEndingTaskInTimespan($start = 0, $end = 0, array $statusIDs = array(), array $personIDs = array()) {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= self::getTasksInTimeSpanWhereClause($start, $end, $statusIDs, $personIDs);
		$order	= 'date_end DESC';

		return Todoyu::db()->getRecordByQuery($fields, $table, $where, '', $order);
	}



	/**
	 * Get IDs of tasks in given timespan
	 * If timestamp of start/end == 0: don't use it (there by this method can be used as well to query for tasks before / after a given timestamp)
	 * If personIDs given:	limit to tasks assigned to given persons
	 * If statuses given:	limit to tasks with given statuses
	 *
	 * @param	Integer		$start
	 * @param	Integer		$end
	 * @param	Array		$projectIDs
	 * @param	Array		$statusIDs
	 * @param	Array		$personIDs		(id_person_assigned)
	 * @param	String		$limit
	 * @param	Boolean		$getContainers
	 * @return	Array
	 */
	public static function getTaskIDsInTimeSpan($start = 0, $end = 0, array $projectIDs = array(), array $statusIDs = array(), array $personIDs = array(), $limit = '', $getContainers = false) {
		$fields	= 'id';
		$table	= self::TABLE;

		$where	= self::getTasksInTimeSpanWhereClause($start, $end, $statusIDs, $personIDs, $getContainers);
		$where .= count($projectIDs) > 0 ? ' AND id_project IN (' . implode(',', $projectIDs) . ') ' : '';

		$order	= 'date_start';
		$index	= 'id';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit, $index);
	}



	/**
	 * Get WHERE clause for tasks in timespan query
	 *
	 * @param	Integer		$start
	 * @param	Integer		$end
	 * @param	Array		$statusIDs
	 * @param	Array		$personIDs
	 * @param	Boolean		$getContainers
	 * @return	String
	 */
	public static function getTasksInTimeSpanWhereClause($start = 0, $end = 0, array $statusIDs = array(), array $personIDs = array(), $getContainers = false) {
		$start		= intval($start);
		$end		= intval($end);
		$statusIDs	= TodoyuArray::intval($statusIDs, true, true);
		$personIDs	= TodoyuArray::intval($personIDs, true, true);

		$where	=  ' deleted = 0 ' . ( $getContainers !== true ? ' AND type = 1 ' : '' );

			// Start and end given: task must intersect with span defined by them
		if( $start > 0 && $end > 0 ) {
			$where	.= ' AND ( date_start <= ' . $end . ' AND date_end >= ' . $start . ' )';
		} else {
				// Only start or end given. Start and end of task must be (at or) after given starting time
			if( $start > 0 ) {
				$where	.=	' AND date_start  >= ' . $start
						.	' AND date_end    >= ' . $start;
			}
				// Start and end of task must be (at or) before given ending time
			if( $end > 0 ) {
				$where	.=	' AND date_end    <= ' . $end
						.	' AND date_start  <= ' . $end;
			}
		}

			// Filter by status IDs
		if( count($statusIDs) > 0 ) {
			$where .= ' AND status IN(' . implode(',', $statusIDs) . ')';
		}
			// Filter by assigned person IDs
		if( sizeof($personIDs) ) {
			$where .= ' AND id_person_assigned IN(' . implode(',', $personIDs) . ')';
		}
		
		return $where;
	}



	/**
	 * Use task filter to find the matching tasks by filter conditions or filterset
	 *
	 * @param	Integer		$idFilterSet
	 * @param	Array		$conditions
	 * @param	String		$conjunction
	 * @return	Array
	 */
	public static function getTaskIDsByFilter($idFilterSet = 0, array $conditions = array(), $conjunction = 'AND') {
		$idFilterSet	= intval($idFilterSet);

		if( $idFilterSet !== 0 ) {
			$conditions = TodoyuFilterConditionManager::getFilterSetConditions($idFilterSet);
		} else {
			$conditions = TodoyuFilterConditionManager::buildFilterConditionArray($conditions);
		}

		$taskFilter = new TodoyuTaskFilter($conditions, $conjunction);

		return $taskFilter->getTaskIDs();
	}



	/**
	 * Get default task data values for a new task/container
	 *
	 * @param	Integer		$idParentTask
	 * @param	Integer		$idProject
	 * @param	Integer		$type
	 * @return	Array
	 */
	public static function getTaskDefaultData($idParentTask = 0, $idProject = 0, $type = TASK_TYPE_TASK) {
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$type			= intval($type);

			// Find project if not available as parameter
		if( $idProject === 0 && $idParentTask !== 0 ) {
			$idProject	= self::getProjectID($idParentTask);
		}

			// Get extension config
		$extConf	= TodoyuExtConfManager::getExtConf('project');

			// Set default data
		$data	= array(
			'id'				=> 0,
			'tasknumber'		=> 0,
			'title'				=> trim($extConf['title']),
			'id_project'		=> $idProject,
			'description'		=> trim($extConf['description']),
			'date_start'		=> self::getDateFromExtConfDefault($extConf['date_start']),
			'date_end'			=> self::getDateFromExtConfDefault($extConf['date_end']),
			'date_deadline'		=> self::getDateFromExtConfDefault($extConf['date_deadline']),
			'status'			=> intval($extConf['status']),
			'id_person_assigned'=> 0,
			'id_person_owner'	=> personid(),
			'estimated_workload'=> intval($extConf['estimated_workload']),
			'is_public'			=> intval($extConf['is_public']),
			'id_parenttask'		=> $idParentTask,
			'type'				=> $type,
			'id_worktype'		=> intval($extConf['id_worktype'])
		);

			// Call hook to modify default task data
		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskDefaultData', $data);

		return $data;
	}



	/**
	 * Create a new task with default values and ID 0
	 * After we have done this, we can access this template task by ID 0 over normal mechanism
	 *
	 * @param	Integer			$idParentTask		ID of the parent task (if it has one)
	 * @param	Integer			$idProject			ID of the project. If task is in the root, there will be no parent task, so you have to give the project ID
	 * @param	Integer			$type				Type of the new task
	 */
	public static function createNewTaskWithDefaultsInCache($idParentTask, $idProject = 0, $type = TASK_TYPE_TASK) {
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$type			= intval($type);

			// Default task data
		$defaultData= self::getTaskDefaultData($idParentTask, $idProject, $type);

			// Store task with default data in cache
		$key	= TodoyuRecordManager::makeClassKey('TodoyuTask', 0);
		$task	= new TodoyuTask(0);
		$task->injectData($defaultData);
		TodoyuCache::set($key, $task);
	}



	/**
	 * Set default task values if missing
	 * Person may not be allowed to enter the values, so we use the defaults from extConf
	 *
	 * @param	Array		$data
	 * @return	Array
	 */
	private static function setDefaultValuesForNotAllowedFields(array $data) {
		$extConf	= TodoyuExtConfManager::getExtConf('project');
		$original	= $data;

			// Set dates to 0
		if( ! isset($data['date_start']) ) {
			$data['date_start'] = self::getDateFromExtConfDefault($extConf['date_start']);
		}
		if( ! isset($data['date_end']) ) {
			$data['date_end'] = self::getDateFromExtConfDefault($extConf['date_end']);;
		}
		if( ! isset($data['date_deadline']) ) {
			$data['date_deadline'] = self::getDateFromExtConfDefault($extConf['date_deadline']);;
		}

			// Set status
		if( ! isset($data['status']) ) {
			$extConfStatus	= intval($extConf['status']);
			$defaultStatus	= intval(Todoyu::$CONFIG['EXT']['project']['taskDefaults']['status']);
			$data['status']	= $extConfStatus === 0 ? $defaultStatus : $extConfStatus;
		}

			// Set is_public flag
		if( ! isset($data['is_public']) ) {
			$extConfPublic	= intval($extConf['is_public']);

			if( $extConfPublic === 1 || Todoyu::person()->isExternal() ) {
				$data['is_public']	= 1;
			}
		}


			// Get assigned person from default
		if( ! isset($data['id_person_assigned']) ) {
			$idRole		= intval($extConf['person_assigned_role']);
			$idProject	= intval($data['id_project']);

			if( $idRole !== 0 && $idProject !== 0 ) {
				$personIDs	= TodoyuProjectManager::getRolePersonIDs($idProject, $idRole);
				$idPerson	= intval($personIDs[0]);

				if( $idPerson !== 0 ) {
					$data['id_person_assigned'] = $idPerson;
				}
			}
		}

			// Get owner person from default
		if( ! isset($data['id_person_owner']) ) {
			$idRole		= intval($extConf['person_owner_role']);
			$idProject	= intval($data['id_project']);

			if( $idRole !== 0 && $idProject !== 0 ) {
				$personIDs	= TodoyuProjectManager::getRolePersonIDs($idProject, $idRole);
				$idPerson	= intval($personIDs[0]);

				if( $idPerson !== 0 ) {
					$data['id_person_owner'] = $idPerson;
				}
			}
		}

			// Get work type from default
		if( ! isset($data['id_worktype']) ) {
			$data['id_worktype'] = intval($extConf['id_worktype']);
		}

			// Get workload from default
		if( ! isset($data['estimated_workload']) ) {
			$data['estimated_workload'] = intval($extConf['estimated_workload']);
		}

			// Call hook to allow other extensions to set default values
		$data	= TodoyuHookManager::callHookDataModifier('project', 'defaultsForNotAllowedTaskFields', $data, array('savedData'=>$original));

		return $data;
	}



	/**
	 * Get a date based on the extconf value set for this type
	 *
	 * @param	Integer		$type		Number of days of the date in the future from now
	 * @return	Integer		timestamp
	 */
	private static function getDateFromExtConfDefault($type) {
		$type	= intval($type);

		if( $type === 0 ) {
			$date	= 0;
		} elseif( $type === -1 ) {
			$date	= TodoyuTime::getStartOfDay();
		} else {
			$time	= NOW + TodoyuTime::SECONDS_DAY * $type;
			$date	= TodoyuTime::getStartOfDay($time);
		}

		return $date;
	}



	/**
	 * Get parent element date ranges. Parent means in this case container or project (not parent task)
	 *
	 * @param	Integer		$idTask			Task ID to check upwards from
	 * @param	Integer		$idProject		Used for project range check, if task ID is 0
	 * @param	Boolean		$checkSelf		Check element itself for container
	 * @return	Array		[start,end]
	 */
	public static function getParentDateRanges($idTask, $idProject = 0, $checkSelf = false) {
		$idTask		= intval($idTask);
		$idProject	= intval($idProject);
		$range		= false;

		if( $idTask > 0 ) {
			$rootLineTasks	= self::getRootlineTasks($idTask);


			if( $checkSelf !== true ) {
					// Remove element itself
				array_shift($rootLineTasks);
			}

				// Check all parent elements if there is a container and use its dates for the range
			foreach($rootLineTasks as $task) {
				if( $task['type'] == TASK_TYPE_CONTAINER ) {
					$range	= array(
						'start'	=> $task['date_start'],
						'end'	=> $task['date_end']
					);
					break;
				}
			}
		}

			// If no container found, use project
		if( $range === false ) {
			if( $idProject !== 0 ) {
				$project	= TodoyuProjectManager::getProject($idProject);
			} elseif( $idTask !== 0 ) {
				$project	= TodoyuTaskManager::getProject($idTask);
			}

			if( isset($project) ) {
				$range	= array(
					'start'	=> $project->getStartDate(),
					'end'	=> $project->getEndDate()
				);
			}
		}

		return $range;
	}



	/**
	 * Get parent task ID
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getParentTaskID($idTask) {
		$idTask	= intval($idTask);

		$field	= 'id_parenttask';
		$table	= self::TABLE;
		$where	= 'id = ' . $idTask;

		$idParent	= Todoyu::db()->getFieldValue($field, $table, $where);

		return intval($idParent);
	}



	/**
	 * Get the root line of a task (all parent task IDs)
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskRootline($idTask) {
		$idTask		= intval($idTask);

			// Check whether already cached
		$idCache	= 'rootline:' . $idTask;

		if( TodoyuCache::isIn($idCache) ) {
			$rootLine	= TodoyuCache::get($idCache);
		} else {
			$idParent	= self::getParentTaskID($idTask);

			$rootLine	= array($idTask);

			while( $idParent !== 0 ) {
				$rootLine[] = $idParent;
				$idParent = self::getParentTaskID($idParent);
			}

			TodoyuCache::set($idCache, $rootLine);
		}

		return $rootLine;
	}



	/**
	 * Get array which contains all tasks in the rootline of a task
	 * The task itself is the first element
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getRootlineTasks($idTask) {
		$idTask	= intval($idTask);

		$rootLine	= self::getTaskRootline($idTask);
		$list		= implode(',', $rootLine);

		$where	= 'id IN(' . $list . ')';
		$order	= 'FIND_IN_SET(id, \'' . $list . '\')';

		return TodoyuRecordManager::getAllRecords(self::TABLE, $where, $order);
	}



	/**
	 * Get parent task of a task
	 * If there is no parent task (task is in project root), return false
	 *
	 * @param	Integer		$idTask
	 * @return	TodoyuTask	Or FALSE if there is no parent task
	 */
	public static function getParentTask($idTask) {
		$idTask	= intval($idTask);

		$task		= self::getTask($idTask);
		$idParent	= $task->getParentTaskID();

		if( $idParent != 0 ) {
			return self::getTask($idParent);
		} else {
			return false;
		}
	}



	/**
	 * Check whether a task exists
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isTask($idTask) {
		$idTask	= intval($idTask);

		if( $idTask !== 0 ) {
			$task = self::getTaskData($idTask);

			if( is_array($task) ) {
				return true;
			}
		}

		return false;
	}



	/**
	 * Check whether a task number is valid
	 * $mustExist is not set (default), only the format is checked.
	 * If $mustExist is set, also a database request will check if this task exists
	 *
	 * @param	Integer		$fullTaskNumber			Identifier with project id and task number
	 * @param	Boolean		$mustExist				TRUE = Has to be in database
	 * @return
	 */
	public static function isTasknumber($fullTaskNumber, $mustExist = false) {
		$valid	= false;

			// Check for point (.)
		if( strpos($fullTaskNumber, '.') !== false ) {
				// Split into project / task number
			$parts	= TodoyuArray::intExplode('.', $fullTaskNumber, true, true);

				// If 2 valid integers found
			if( sizeof($parts) === 2 ) {
					// Database check required?
				if( $mustExist ) {
						// Get task ID for validation
					$idTask	= self::getTaskIDByTaskNumber($fullTaskNumber);
					if( $idTask !== 0 ) {
						$valid = true;
					}
				} else {
						// If no db check required, set valid
					$valid = true;
				}
			}
		}

		return $valid;
	}



	/**
	 * Check whether a task is visible (available for rendering)
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isTaskVisible($idTask) {
		$idTask	= intval($idTask);

		if( self::isTask($idTask) ) {
			$task	= TodoyuTaskManager::getTask($idTask);

			if( ! $task->isDeleted() ) {
				return true;
			}
		}

		return false;
	}



	/**
	 * Check whether a task is expanded
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isTaskExpanded($idTask) {
		$idTask	= intval($idTask);

		if( is_null(self::$expandedTaskIDs) ) {
			self::$expandedTaskIDs = TodoyuProjectPreferences::getExpandedTasks();
		}

		return in_array($idTask, self::$expandedTaskIDs);
	}



	/**
	 * Modify form for task edit
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idTask
	 * @return	TodoyuForm
	 */
	public static function hookModifyFormfieldsForTask(TodoyuForm $form, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

		if( $task->isTask() ) {
				// New task has no parent?
			if( $idTask === 0 ) {
				$form->getField('id_parenttask')->remove();
				$form->addHiddenField('id_parenttask', 0);

				if( $form->hasField('status') ) {
						// Remove empty status field
					$statuses	= TodoyuTaskStatusManager::getStatuses('create');

					if( sizeof($statuses) === 0 ) {
						$form->getField('status')->remove();
					}
				}
			}
		}

		return $form;
	}



	/**
	 * Modify task form object for container editing
	 *
	 * @param	TodoyuForm	$form			Task edit form object
	 * @param	Integer		$idTask			Task ID
	 * @return	TodoyuForm	Modified form object
	 */
	public static function hookModifyFormfieldsForContainer(TodoyuForm $form, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

			// Remove fields which are not needed in containers
		if( $task->isContainer() ) {
			$formFields			= $form->getFieldnames();
				// Ensure the fields to be removed do still exist
			$fieldsToBeRemoved	= array_intersect($formFields, array(
					'id_worktype',
					'estimated_workload',
					'date_start',
					'date_end',
					'date_deadline',
					'id_person_assigned',
//					'id_person_owner',
					'status'
				)
			);

			foreach( $fieldsToBeRemoved as $fieldName ) {
				$form->getField($fieldName)->remove();
			}

				// Remove
			if( $idTask === 0 ) {
				if( in_array('id_parenttask', $formFields) ) {
					$form->getField('id_parenttask')->remove();
				}
				$form->addHiddenField('id_parenttask', 0);
			}
		}

			// Call hooks to modify $form
		$form	= TodoyuHookManager::callHookDataModifier('project', 'modifyFormfieldsForContainer', $form, array('idTask' => $idTask));

		return $form;
	}



	/**
	 * Copy a task (set also a new parent)
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idParent
	 * @param	Boolean		$withSubTasks
	 * @param	Integer		$idProject
	 * @return	Integer
	 */
	public static function copyTask($idTask, $idParent, $withSubTasks = true, $idProject = 0) {
		$idTask		= intval($idTask);
		$idParent	= intval($idParent);
		$idProject	= intval($idProject);

			// Get original task data
		$data		= self::getTaskData($idTask);

			// Set new project id if given
		if( $idProject !== 0 ) {
			$data['id_project'] = $idProject;
		}
			// Set new parent (needed for sorting)
		$data['id_parenttask']	= $idParent;

			// Call data modifier hook for task data
		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskcopydata', $data, array($idTask, $idParent, $withSubTasks, $idProject));

			// Add new task (with old data)
		$idTaskNew	= self::addTask($data);

			// Call data modifier hook, so other extensions can modify data if needed
		$hookData	= array(
			'idTask'	=> $idTask,
			'idTaskNew'	=> $idTaskNew,
			'idParent'	=> $idParent,
			'idProject'	=> $idProject
		);
		$data		= TodoyuHookManager::callHookDataModifier('project', 'taskcopy', $data, $hookData);

			// Set status. Check for editing right and use default status as fallback
		$extConf		= TodoyuExtConfManager::getExtConf('project');
		$defaultStatus	= intval($extConf['status']);
		if(  allowed('project', 'task:editStatus') ) {
			$data['status']		= STATUS_OPEN;
		} elseif( $defaultStatus !== 0 ) {
			$data['status']		= $defaultStatus;
		} else{
			$data['status']		= STATUS_PLANNING;
		}

			// Remove old task number
		unset($data['tasknumber']);

			// Update task data
		self::updateTask($idTaskNew, $data);

			// Copy sub tasks if enabled
		if( $withSubTasks && $idTask !== $idParent ) {
			$subTaskIDs = self::getSubTaskIDs($idTask);

			foreach($subTaskIDs as $idSubTask) {
				self::copyTask($idSubTask, $idTaskNew, true, $idProject);
			}
		}

		return $idTaskNew;
	}



	/**
	 * Move a task. Change its parent
	 * Move to another project is also supported
	 *
	 * @param	Integer		$idTask				Task to move
	 * @param	Integer		$idParentTask		New parent task
	 * @param	Integer		$idProject
	 * @return	Integer
	 */
	public static function moveTask($idTask, $idParentTask, $idProject = 0) {
		$idTask			= intval($idTask);
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$taskData		= self::getTaskData($idTask);
		$parentData		= $idParentTask === 0 ? false : self::getTaskData($idParentTask);
		$idNewProject	= $idParentTask === 0 ? $idProject : intval($parentData['id_project']);

			// Basic update
		$update		= array(
			'id_parenttask'	=> $idParentTask,
			'id_project'	=> $idNewProject
		);

			// If project changed, generate a new task number
		if( $taskData['id_project'] != $idNewProject ) {
			$update['tasknumber']	= TodoyuProjectManager::getNextTaskNumber($idNewProject);
		}

			// Update the moved task
		self::updateTask($idTask, $update);

			// If project changed, update also all sub tasks with new project ID and generate new task number
		if( $taskData['id_project'] != $idNewProject ) {
			$allSubTaskIDs	= self::getAllSubTaskIDs($idTask);

			foreach($allSubTaskIDs as $idSubTask) {
				$subUpdate	= array(
					'id_project'	=> $idNewProject,
					'tasknumber'	=> TodoyuProjectManager::getNextTaskNumber($idNewProject)
				);

				Todoyu::db()->updateRecord(self::TABLE, $idSubTask, $subUpdate);
			}
		}

		return $idTask;
	}



	/**
	 * Clone given task
	 *
	 * @param 	Integer		$idTask
	 * @param 	Boolean		$withSubTasks
	 * @return	Integer
	 */
	public static function cloneTask($idTask, $withSubTasks = true) {
		$idTask		= intval($idTask);
		$taskData	= TodoyuTaskManager::getTaskData($idTask);

		$idNewTask	= self::copyTask($idTask, $taskData['id_parenttask'], $withSubTasks, $taskData['id_project']);

		TodoyuTaskManager::changeTaskOrder($idNewTask, $idTask, 'after');

		return $idNewTask;
	}



	/**
	 * Change to sorting order of the tasks
	 *
	 * @param	Integer		$idTaskMove			Task which was moved
	 * @param	Integer		$idTaskRef			Task which is the reference for after/before
	 * @param	String		$moveMode			Mode: after or before
	 */
	public static function changeTaskOrder($idTaskMove, $idTaskRef, $moveMode) {
		$idTaskMove	= intval($idTaskMove);
		$idTaskRef	= intval($idTaskRef);
		$taskMove	= TodoyuTaskManager::getTaskData($idTaskMove);
		$taskRef	= TodoyuTaskManager::getTaskData($idTaskRef);
		$after		= strtolower(trim($moveMode)) === 'after';

			// Update parameters
		$update	= array();
		$table	= self::TABLE;
		$where	= '		id_project		= ' . $taskMove['id_project'] .
				  ' AND	id_parenttask	= ' . $taskRef['id_parenttask'];
		$noQuote= array('sorting');

			// Move other task which are between the move and the ref task
			// Adjust the reference sorting position
		$refSort= $after ? $taskRef['sorting'] + 1 : $taskRef['sorting'] - 1;

			// If task get a higher position
		if( $taskMove['sorting'] < $taskRef['sorting'] ) {
			$min	= $taskMove['sorting'];
			$max	= $refSort;
			$update['sorting']	= 'sorting-1';
		} else {
			$min	= $refSort;
			$max	= $taskMove['sorting'];
			$update['sorting']	= 'sorting+1';
		}

			// Limits for updating other tasks
		$where .= ' AND sorting > ' . $min . ' AND
						sorting < ' . $max;

		Todoyu::db()->doUpdate($table, $where, $update, $noQuote);

			// Update moved task
		$update['sorting'] = $after ? $taskRef['sorting'] + 1 : $taskRef['sorting'];

		TodoyuRecordManager::updateRecord(self::TABLE, $idTaskMove, $update, $noQuote);
	}



	/**
	 * Sort task IDs by a field in the database
	 * This is useful if you have task IDs from severals sources (filters) and
	 * they should all be sorted by one field
	 *
	 * @param	Array		$taskIDs		Task IDs to sort
	 * @param	String		$order			Order statement
	 * @return	Array
	 */
	public static function sortTaskIDs(array $taskIDs, $order) {
		$taskIDs	= TodoyuArray::intval($taskIDs, true, true);

		if( sizeof($taskIDs) === 0 ) {
			return array();
		}

		$field	= 'id';
		$table	= self::TABLE;
		$where	= 'id IN(' . implode(',', $taskIDs) . ')';

		return Todoyu::db()->getColumn($field, $table, $where, '', $order);
	}



	/**
	 * Check whether a person is assigned to a task as owner or assigned person
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idPerson
	 * @param	Boolean		$checkCreator		Creator is an assigned person too
	 * @return	Boolean
	 */
	public static function isPersonAssigned($idTask, $idPerson = 0, $checkCreator = false) {
		$idTask		= intval($idTask);
		$idPerson	= personid($idPerson);

		$fields	= 'id';
		$table	= self::TABLE;
		$where	= '	id					= ' . $idTask .
				  ' AND (
							 id_person_assigned	= ' . $idPerson .
						' OR id_person_owner	= ' . $idPerson;

			// Add creator field check
		if( $checkCreator ) {
			$where .= ' OR id_person_create = ' . $idPerson;
		}

		$where .= ')';

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Check whether a person is assigned to the task's project
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isPersonAssignedToProject($idTask, $idPerson = 0) {
		$idTask		= intval($idTask);
		$idPerson	= personid($idPerson);

		$fields	= '	t.id';
		$table	= 	self::TABLE . ' t,
					ext_project_mm_project_person mm';
		$where	= '		t.id			= ' . $idTask .
				  ' AND	t.id_project	= mm.id_project
				  	AND	mm.id_person	= ' . $idPerson;

		return Todoyu::db()->hasResult($fields, $table, $where);
	}



	/**
	 * Check whether a person is assigned to the task or the project
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isPersonAssignedToTaskOrProject($idTask, $idPerson = 0) {
		return self::isPersonAssigned($idTask, $idPerson) || self::isPersonAssignedToProject($idTask, $idPerson);
	}



	/**
	 * Load task data for quicktask
	 *
	 * @param	Array		$data
	 * @param	Integer		$idRecord
	 * @param	Array		$params
	 * @return	Array
	 */
	public static function hookLoadTaskFormData(array $data, $idRecord, array $params = array()) {
		if( TodoyuRequest::getArea() === 'project' ) {
				// Set project ID
			if( intval($data['id_project']) === 0 ) {
				$data['id_project']	= TodoyuProjectPreferences::getActiveProject();
			}
		}

			// Set owner for quickCreate tasks
		if( strtolower(CONTROLLER) === 'quickcreatetask' ) {
			$data['id_person_owner'] = personid();
		}

			// Status
		$extConf	= TodoyuExtConfManager::getExtConf('project');
		if( ! isset($data['status']) ) {
			$data['status'] = intval($extConf['status']);
		}

		return $data;
	}


	/**
	 * Freeze a task
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function freeze($idTask) {
		return TodoyuFreezeManager::freezeObject('TodoyuTask', $idTask);
	}



	/**
	 * Unfreeze a task
	 *
	 * @param	Integer					$idTask
	 * @return	Boolean|TodoyuTask
	 */
	public static function unfreeze($idTask) {
		return TodoyuFreezeManager::unfreezeElement('TodoyuTask', $idTask);
	}



	/**
	 * Lock a task
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$ext		ext ID
	 */
	public static function lockTask($idTask, $ext = EXTID_PROJECT) {
		TodoyuLockManager::lock($ext, 'ext_project_task', $idTask);
	}



	/**
	 * Unlock a task
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$ext		ext ID
	 */
	public static function unlockTask($idTask, $ext = EXTID_PROJECT) {
		TodoyuLockManager::unlock($ext, 'ext_project_task', $idTask);
	}



	/**
	 * Lock multiple tasks
	 *
	 * @param	Array		$taskIDs
	 * @param	Integer		$ext		ext ID
	 */
	public static function lockTasks(array $taskIDs, $ext = EXTID_PROJECT) {
		foreach($taskIDs as $idTask) {
			self::lockTask($idTask, $ext);
		}
	}



	/**
	 * Unlock multiple tasks
	 *
	 * @param	Array		$taskIDs
	 * @param	Integer		$ext		ext ID
	 */
	public static function unlockTasks(array $taskIDs, $ext = EXTID_PROJECT) {
		foreach($taskIDs as $idTask) {
			self::unlockTask($idTask, $ext);
		}
	}



	/**
	 * Check whether task is locked
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function isLocked($idTask) {
		return TodoyuLockManager::isLocked('ext_project_task', $idTask);
	}



	/**
	 * Check if a container is locked
	 * A container is not locked directly, but if a subtask is locked, the container is locked too
	 *
	 * @param	Integer		$idContainer
	 * @return	Boolean
	 */
	public static function isContainerLocked($idContainer) {
		$allSubtaskIDs	= self::getAllSubTaskIDs($idContainer);

		if( sizeof($allSubtaskIDs) === 0 ) {
			return false;
		} else {
			return TodoyuLockManager::areLocked('ext_project_task', $allSubtaskIDs);
		}
	}



	/**
	 * Link task IDs in given text
	 *
	 * @param	String		$text
	 * @return	String
	 */
	public static function linkTaskIDsInText($text) {
		if( allowed('project', 'general:area') ) {
			$pattern	= '/(<p>|<span>|\s|^)(\d+\.\d+)(<\/p>|<\/span>|\s|$)/';
			$replace	= '$1<a href="javascript:void(0)" onclick="Todoyu.Ext.project.goToTaskInProjectByTasknumber(\'$2\')">$2</a>$3';

			$text	= preg_replace($pattern, $replace, $text);
		}

		return $text;
	}

}

?>