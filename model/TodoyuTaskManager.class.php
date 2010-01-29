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
 * Task Manager
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuTaskManager {

	/**
	 * Tablename
	 */
	const TABLE = 'ext_project_task';

	/**
	 * Installed tabs for tasks
	 *
	 * @var	Array
	 */
	private static $tabs = null;



	/**
	 * Get object of a task.
	 *
	 * @param	Integer		$idTask		Task ID
	 * @return	TodoyuTask
	 */
	public static function getTask($idTask) {
		$idTask	= intval($idTask);

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
	 * @param	String		$fullTasknumber			Tasknumber divided by point (.)
	 * @return	Integer		0 if task not found
	 */
	public static function getTaskIDByTaskNumber($fullTasknumber) {
		$idTask	= 0;
		$parts	= TodoyuArray::intExplode('.', $fullTasknumber, true, true);

		if( sizeof($parts) === 2 ) {
			$field	= 'id';
			$table	= self::TABLE;
			$where	= '	id_project	= ' . $parts[0] . ' AND
						tasknumber	= ' . $parts[1];
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
	 * @return	Array
	 */
	public static function getTasks(array $taskIDs, $orderBy = 'id') {
		$taskIDs= TodoyuArray::intval($taskIDs, true, true);
		$tasks	= array();

		if( sizeof($taskIDs) > 0 ) {
			$where	= 'id IN(' . implode(',', $taskIDs) . ')';
			$tasks	= Todoyu::db()->getArray('*', self::TABLE, $where, '', $orderBy);
		}

		return $tasks;
	}



	/**
	 * Save a task. If a task number is given, the task will be updated, else
	 * a new task will be created
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function saveTask(array $data) {
		$xmlPath	= 'ext/project/config/form/task.xml';
		$idTask		= intval($data['id']);
		$idProject	= intval($data['id_project']);

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
		}

		if( $data['status'] == STATUS_DONE || $data['status'] == STATUS_ACCEPTED ) {
			$data['date_finish'] = NOW;
		}

			// Call save data handler
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
	 * @return	Bool
	 */
	public static function updateTask($idTask, array $data) {
		$idTask	= intval($idTask);

		unset($data['id']);
		$data['date_update']	= NOW;

		self::removeTaskFromCache($idTask);

		return Todoyu::db()->updateRecord(self::TABLE, $idTask, $data) === 1;
	}



	/**
	 * Add a new task
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addTask(array $data = array()) {
		unset($data['id']);
		$data['date_create']	= NOW;
		$data['date_update']	= NOW;
		$data['id_user_create']	= TodoyuAuth::getUserID();

			// Create task number
		$idProject	= intval($data['id_project']);
		$data['tasknumber'] = TodoyuProjectManager::getNextTaskNumber($idProject);

			// Create sorting flag
		$idParent	= intval($data['id_parenttask']);
		$data['sorting']	= self::getNextSortingPosition($idProject, $idParent);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Get next sorting position for a new task. For every subtask, sorting starts new
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idParentTask
	 * @return	Integer
	 */
	public static function getNextSortingPosition($idProject, $idParentTask) {
		$idProject		= intval($idProject);
		$idParentTask	= intval($idParentTask);

		$field		= 'MAX(sorting) as sorting';
		$table		= self::TABLE;
		$where		= '	id_project		= ' . $idProject . ' AND
						id_parenttask	= ' . $idParentTask;
		$group		= 'sorting';
		$order		= 'sorting DESC';
		$limit		= 1;

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
	 */
	public static function deleteTask($idTask, $deleteSubtasks = true) {
		$data	= array(
			'deleted'		=> 1,
			'date_update'	=> NOW
		);

		self::updateTask($idTask, $data);

			// Delete all subtasks
		if( $deleteSubtasks ) {
			$allSubtaskIDs	= self::getAllSubtaskIDs($idTask);

			if( sizeof($allSubtaskIDs) > 0 ) {
				$where	= 'id IN(' . implode(',', $allSubtaskIDs) . ')';
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

		$table	= self::TABLE;
		$where	= 'id_project = ' . $idProject;
		$data	= array(
			'deleted'		=> 1,
			'date_update'	=> NOW
		);

		Todoyu::db()->doUpdate($table, $where, $data);
	}



	/**
	 * Add a new container
	 *
	 * @param	Array		$data
	 * @return	Integer		New container ID
	 */
	public static function addContainer(array $data) {
		$data['type']				= TASK_TYPE_CONTAINER;
//		$data['id_user_assigned']	= userid();

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

			// Set finishing date if task is done
		if( $newStatus === STATUS_DONE || $newStatus === STATUS_ACCEPTED ) {
			$data['date_finish']	= NOW;
		}

		self::updateTask($idTask, $data);
	}



	/**
	 * Get the project ID of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getProjectID($idTask) {
		$field	= 'id_project';
		$table	= self::TABLE;
		$where	= 'id = ' . intval($idTask);

		return intval(Todoyu::db()->getFieldValue($field, $table, $where));
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
	 * @return	Array		Config array for context menu
	 */
	public static function getContextMenuItems($idTask, array $items) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);
		$allowed= array();
		$type	= '';

		if( $task->isTask() ) {
			$ownItems	=& $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Task'];
			$type		= 'task';
		} elseif( $task->isContainer() ) {
			$ownItems	=& $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Container'];
			$type		= 'container';
		}

		if( $task->isTask() || $task->isContainer() ) {
			$allowed['header']	= $ownItems['header'];

				// Edit
			if( allowed('project', $type . ':edit') ) {
				$allowed['edit'] = $ownItems['edit'];
			}

				// Actions (with submenu)
			$allowed['actions'] = $ownItems['actions'];
			unset($allowed['actions']['submenu']);

				// Add copy
			if( allowed('project',  $type . ':copy') ) {
				$allowed['actions']['submenu']['copy'] = $ownItems['actions']['submenu']['copy'];
			}

				// Add cut
			if( allowed('project',  $type . ':cut') ) {
				$allowed['actions']['submenu']['cut'] = $ownItems['actions']['submenu']['cut'];
			}

				// Add clone
			if( allowed('project',  $type . ':clone') ) {
				$allowed['actions']['submenu']['clone'] = $ownItems['actions']['submenu']['clone'];
			}

				// Add delete
			if( allowed('project',  $type . ':delete') ) {
				$allowed['actions']['submenu']['delete'] = $ownItems['actions']['submenu']['delete'];
			}



				// Add (with submenu)
			$allowed['add'] = $ownItems['add'];
			unset($allowed['add']['submenu']);

				// Add subtask
			if( allowed('project',  $type . ':addtask') ) {
				$allowed['add']['submenu']['task'] =$ownItems['add']['submenu']['task'];
			}

				// Add subcontainer
			if( allowed('project',  $type . ':addcontainer') ) {
				$allowed['add']['submenu']['container'] =$ownItems['add']['submenu']['container'];
			}


				// Status
			if( $task->isTask() && allowed('project', 'task:status') ) {
				$allowed['status'] = $ownItems['status'];

				$statuses = TodoyuProjectStatusManager::getTaskStatuses('changeto');

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
	 * Remove empty parent menues if they have no submenu entries
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
		if( ! is_array($items['add']['submenu']) ) {
			unset($items['add']);
		}

			// Remove status if empty
		if( ! is_array($items['status']['submenu']) ) {
			unset($items['status']);
		}

		return $items;
	}



	/**
	 * Get the IDs of all subtasks of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubtaskIDs($idTask) {
		$idTask	= intval($idTask);

		$field	= 'id';
		$table	= self::TABLE;
		$where	= '	id_parenttask 	= ' . $idTask . ' AND
					deleted 		= 0';

		return Todoyu::db()->getColumn($field, $table, $where);
	}



	/**
	 * Get ALL subtasks of a task (the whole tree, instead only the direct childs)
	 * Get also sub-sub-...-tasks
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAllSubtaskIDs($idTask) {
		$idTask		= intval($idTask);
		$subtasks	= array();

		$field	= 'id';
		$table	= self::TABLE;
		$whereF	= '	id_parenttask IN(%s) AND
					deleted	= 0';

		$where	= sprintf($whereF, $idTask);

		$newTasks	= Todoyu::db()->getColumn($field, $table, $where);

		while( sizeof($newTasks) > 0 ) {
			$subtasks = array_merge($subtasks, $newTasks);

			$where = sprintf($whereF, implode(',', $newTasks));

			$newTasks = Todoyu::db()->getColumn($field, $table, $where);
		}

		return $subtasks;
	}



	/**
	 * Get subtasks (as data array) of given task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubtasks($idTask) {
		$idTask	= intval($idTask);

		if( $idTask === 0 )	{
			return array();
		}

		$field	= '*';
		$table	= self::TABLE;
		$where	= '	id_parenttask	= ' . $idTask . ' AND
					deleted	= 0';
		$groupBy= '';
		$orderBy= 'date_create';
		$limit	= '';

		return Todoyu::db()->getArray($field, $table, $where, $groupBy, $orderBy, $limit);
	}



	/**
	 * Check if a task has subtasks
	 *
	 * @param	Integer		$idTask
	 * @return	Bool
	 */
	public static function hasSubtasks($idTask) {
		$idTask	= intval($idTask);

		$subtaskIDs	= self::getSubtaskIDs($idTask);

		return sizeof($subtaskIDs) > 0 ;
	}



	/**
	 * Check if a task is a subtask of a task.
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idParent
	 * @param	Bool		$checkDeep		TRUE: check all levels, FALSE: check only direct childs
	 * @return	Bool
	 */
	public static function isSubtaskOf($idTask, $idParent, $checkDeep = false) {
		$idTask		= intval($idTask);
		$idParent	= intval($idParent);

		if( $checkDeep ) {
			$subtasks	= self::getAllSubtaskIDs($idParent);
		} else {
			$subtasks	= self::getSubtaskIDs($idParent);
		}

		return in_array($idTask, $subtasks);
	}



	/**
	 * Check if task has a parent
	 *
	 * @param	Integer		$idTask
	 * @return 	Bool
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
	 * Get task tabs config array (labels parsed)
	 *
	 * @param	Integer		$idTask
	 * @param	Bool		$evalLabel		If true, all labels with a function reference will be parsed
	 * @return	Array
	 */
	public static function getTabs($idTask, $evalLabel = true) {
		if( is_null(self::$tabs) ) {
			$tabs = TodoyuArray::assure($GLOBALS['CONFIG']['EXT']['project']['task']['tabs']);
			self::$tabs = TodoyuArray::sortByLabel($tabs);
		}

		$tabs = self::$tabs;

		if( $evalLabel ) {
			foreach($tabs as $index => $tab) {
				$labelFunc	= $tab['label'];
				$tabs[$index]['label'] = TodoyuDiv::callUserFunction($labelFunc, $idTask);
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
		return $GLOBALS['CONFIG']['EXT']['project']['task']['tabs'][$tabKey];
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
	 * @param	String		$icon
	 * @param	Integer		$position
	 */
	public static function addTaskTab($idTab, $labelFunction, $contentFunction, $position = 100) {
		$GLOBALS['CONFIG']['EXT']['project']['task']['tabs'][$idTab] = array(
			'id'		=> $idTab,
			'position'	=> intval($position),
			'label'		=> $labelFunction,
			'content'	=> $contentFunction
		);
	}



	/**
	 * Get all users which are somehow connected with this task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskUsers($idTask) {
		$idTask	= intval($idTask);

		$fields	= ' u.*';
		$tables	= ' ext_user_user u,
					ext_project_task t';
		$where	= '	t.id				= ' . $idTask . ' AND
					(t.id_user_create	= u.id OR
					t.id_user_assigned	= u.id OR
					t.id_user_owner		= u.id)';
		$group	= 'u.id';
		$order	= 'u.lastname, u.firstname';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order);
	}


	/**
	 * Get task owner
	 *
	 * @param	Integer		$idTask
	 * @return	Integer
	 */
	public static function getTaskOwner($idTask) {
		$idTask	= intval($idTask);

		$fields	= ' u.*';
		$tables	= ' ext_project_task t,
					ext_user_user u';
		$where	= '	t.id		= ' . $idTask . ' AND
					u.id		= t.id_user_owner';
		$group	= '';
		$order	= '';

		return Todoyu::db()->getArray($fields, $tables, $where, $group, $order);
	}



	/**
	 * Get label for the contextmenu of a task
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function getContextMenuHeader($idTask) {

		$task	= self::getTaskData($idTask);

		return TodoyuDiv::cropText($task['title'], 24, '', false);
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
	 * @return unknown
	 */
	public static function getTaskInfoArray($idTask, $infoLevel = 0) {
		$idTask		= intval($idTask);
		$infoLevel	= intval($infoLevel);

		$task	= self::getTask($idTask);
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

				// Status
			$info['status']	= array(
				'label'		=> 'LLL:core.status',
				'value'		=> $taskData['statuslabel'],
				'position'	=> 10,
				'className'	=> ''
			);

				// Worktype
			$info['worktype'] = array(
				'label'		=> 'LLL:task.attr.worktype',
				'value'		=> $taskData['worktype']['title'],// 'Internes / Administration',
				'position'	=> 20,
				'className'	=> ''
			);

				// Public
			$info['is_public']	= array(
				'label'	=> 'LLL:task.attr.is_public',
				'value'	=> Label('LLL:task.attr.is_public.' . ($taskData['is_public'] ? 'public' : 'private')),
				'position'	=> 25,
				'className'	=> ''
			);

				// Estimated workload
			$info['esitmated_workload']	= array(
				'label'	=> 'LLL:task.attr.esitmated_workload',
				'value'	=> TodoyuTime::sec2hour($taskData['estimated_workload']),
				'position'	=> 30,
				'className'	=> 'sectionStart'
			);

				// Estimated workload
			$info['is_estimatedworkload_public']	= array(
				'label'		=> 'LLL:task.attr.is_estimatedworkload_public',
				'value'		=> Label('task.attr.is_estimatedworkload_public.' . $taskData['is_estimatedworkload_public']),
				'position'	=> 35,
				'className'	=> ''
			);



				// User assigned
			$info['user_assigned']	= array(
				'label'		=> 'LLL:task.attr.user_assigned',
				'value'		=> $taskData['user_assigned']['lastname'] . ', ' . $taskData['user_assigned']['firstname'],
				'position'	=> 80,
				'className'	=> 'sectionStart'
			);



				// Date start
			$info['date_start']	= array(
				'label'	=> 'LLL:task.attr.date_start',
				'value'	=> TodoyuTime::format( $taskData['date_start'], 'date'),
				'position'	=> 100,
				'className'	=> 'sectionStart'
			);

				// Date end (if set)
			if( $taskData['date_end'] > 0 ) {
				$formatEnd	= date('F', $data['date_end']) == 0 ? 'date' : 'datetime';
				$info['date_end']	= array(
					'label'	=> 'LLL:task.attr.date_end',
					'value'	=> TodoyuTime::format($taskData['date_end'], $formatEnd),
					'position'	=> 110,
					'className'	=> ''
				);
			}
				// Date deadline
			if( $taskData['date_deadline'] > 0 ) {
				$formatDeadline	= date('s', $taskData['date_deadline']) === '00' ? 'date' : 'datetime';
				$info['date_deadline']	= array(
					'label'	=> 'LLL:task.attr.date_deadline',
					'value'	=> TodoyuTime::format($taskData['date_deadline'], $formatDeadline),
					'position'	=> 120,
					'className'	=> ''
				);
			}

		}

			// Attributes of tasks and containers

			// User owner
		$info['user_owner']	= array(
			'label'	=> 'LLL:task.attr.user_owner',
			'value'	=> '<a href="javascript:void(0)" onclick="alert(\'Quick user detail anzeigen\')" class="quickInfoLink">' . $taskData['user_owner']['lastname'] . ', ' . $taskData['user_owner']['firstname'] . '</a>',
			'position'	=> 150,
			'className'	=> 'sectionStart'
		);

					// Date create
		$info['date_create']	= array(
			'label'	=> 'LLL:task.attr.date_create',
			'value'	=> TodoyuTime::format($taskData['date_create'], 'datetime'),
			'position'	=> 160,
			'className'	=> ''
		);

		$data	= array_merge($data, $info);

		return $data;
	}



	/**
	 *	Add container info to task data
	 *
	 *	@param	Array	$taskData
	 *	@param	Integer	$idTask
	 *	@param	Integer	$infoLevel
	 *	@return	Array
	 */
	public static function addContainerInfoToTaskData($taskData, $idTask, $infoLevel) {
		$idTask		= intval($idTask);
		$infoLevel	= intval($infoLevel);
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
	 * @param	Integer	$idTask
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
	 * Get project task info icons
	 *
	 * @param	Array		$icons
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskIcons(array $icons, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

			// Task-only infos (not relevant for containers)
		if ( $task->isTask() ) {
			if( $task->getStatus() != STATUS_CLEARED && ($task->getDeadlineDate() < NOW || $task->getEndDate() < NOW) ) {
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

			// Add is public icon
		if( $task->isPublic() ) {
			$icons['public'] = array(
				'id'		=> 'task-' . $idTask . '-public',
				'class'		=> 'isPublic',
				'label'		=> 'LLL:task.attr.is_public.public',
				'position'	=> 80
			);
		}

			// Is acknowledged?
		if( ! $task->isAcknowledged() ) {
			$icons['notacknowledged'] = array(
				'id'		=> 'task-' . $idTask . '-notacknowledged',
				'class'		=> 'notAcknowledged',
				'label'		=> 'LLL:task.attr.notAcknowledged',
				'onclick'	=> 'Todoyu.Ext.project.Task.setAcknowledged(' . $idTask . ')',
				'position'	=> 100
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

		TodoyuCache::removeRecord('TodoyuTask', $idTask);
		TodoyuCache::removeRecordQuery(self::TABLE, $idTask);
	}



	/**
	 * Set task acknowledged
	 *
	 * @param	Integer		$idTask
	 */
	public static function setTaskAcknowledged($idTask) {
		$idTask	= intval($idTask);

		$update	= array(
			'is_acknowledged' => 1
		);

		self::updateTask($idTask, $update);
	}



	/**
	 * Get task autocompletion label
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
	 * If userIDs given:	limit to tasks assigned to given users
	 * If statuses given:	limit to tasks with given statuses
	 *
	 * @param	Integer		$timesStart
	 * @param	Intger		$timeEnd
	 * @param	Array		$statusIDs
	 * @param	Array		$userIDs		(id_user_assigned)
	 * @param	String		$limit
	 * @param	Boolean		$getContainers
	 * @return	Array
	 */
	public static function getTasksInTimeSpan($timeStart = 0, $timeEnd = 0, array $statusIDs = array(), array $userIDs = array(), $limit = '', $getContainers = false) {
		$timeStart	= intval($timeStart);
		$timeEnd	= intval($timeEnd);
		$statusIDs	= TodoyuArray::intval($statusIDs, true, true);
		$userIDs	= TodoyuArray::intval($userIDs, true, true);

		$fields	= '*';
		$table	= self::TABLE;

		$where	=  ' deleted 	= 0 ' . ($getContainers ? '' : ' AND type = 1 ');
//		$where	.= ($timeStart	> 0	? (' AND date_start	>= ' . $timeStart .	' AND date_end >= ' . $timeStart) : '');
		$where	.= ($timeEnd	> 0	? (' AND date_end 	<= ' . $timeEnd .	' AND date_start <= ' . $timeEnd) : '');

			// Add status IDs
		if( count($statusIDs) > 0 ) {
			$where .= ' AND status IN(' . implode(',', $statusIDs) . ')';
		}

			// Add user IDs
		if( sizeof($userIDs) ) {
			$where .= ' AND id_user_assigned IN(' . implode(',', $userIDs) . ')';
		}

		$order	= 'date_start';
		$index	= 'id';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit, $index);
	}



	/**
	 * Use task filter to find the matching tasks by filter conditions or filterset
	 *
	 * @param	Integer		$idFilterset
	 * @param	Bool		$useConditions
	 * @param	Array		$filterConditions
	 * @param	String		$conjunction
	 * @return	Array
	 */
	public static function getTaskIDsByFilter($idFilterset = 0, array $conditions = array(), $conjunction = 'AND')	{
		$idFilterset	= intval($idFilterset);

		if( $idFilterset !== 0 ) {
			$conditions = TodoyuFilterConditionManager::getFilterSetConditions($idFilterset);
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
	public static function getDefaultTaskData($idParentTask, $idProject = 0, $type = TASK_TYPE_TASK) {
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$type			= intval($type);

			// Find project if not available as parameter
		if( $idProject === 0 && $idParentTask !== 0 ) {
			$idProject	= self::getProjectID($idParentTask);
		}

			// Get data
		$idUser		= TodoyuAuth::getUserID();
		$taskNumber	= TodoyuProjectManager::getNextTaskNumber($idProject);

			// Calculate dates based on project and container parents
		$range		= self::getParentDateRanges($idParentTask, $idProject, true);
		$dateStart	= $range['start'] < NOW ? NOW : $range['start'];
		$dateEnd	= $range['end'] < NOW ? NOW : $range['end'];

			// Set default data
		$defaultData	= array(
			'id'				=> 0,
			'title'				=> '',
			'tasknumber'		=> $taskNumber,
			'status'			=> STATUS_OPEN,
			'estimated_workload'=> intval($GLOBALS['CONFIG']['EXT']['project']['Task']['defaultEstimatedWorkload']),
			'id_project'		=> $idProject,
			'id_parenttask'		=> $idParentTask,
			'id_user_owner'		=> $idUser,
			'type'				=> $type,
			'class'				=> ''
		);

			// Set type specific information
		switch($type) {
			case TASK_TYPE_TASK:
				//$defaultData['title']	= Label('task.new.Task.defaultTitle');
			break;

			case TASK_TYPE_CONTAINER:
				//$defaultData['title'] = Label('task.new.Container.defaultTitle');
				$defaultData['class'] .= ' container';
			break;
		}

			// Call hook to modify default task data
		$defaultData	= TodoyuHookManager::callHookDataModifier('project', 'taskDefaultData', $defaultData, array($idParentTask, $idProject));

		return $defaultData;
	}



	/**
	 * Get parent element date ranges. Parent means in this case container or project (not parent task)
	 *
	 * @param	Integer		$idTask			Task ID to check upwards from
	 * @param	Integer		$idProject		Used for project range check, if task ID is 0
	 * @param	Bool		$checkSelf		Check element itself for container
	 * @return	Array		[start,end]
	 */
	public static function getParentDateRanges($idTask, $idProject = 0, $checkSelf = false) {
		$idTask		= intval($idTask);
		$idProject	= intval($idProject);
		$range		= false;

		if( $idTask > 0 ) {
			$rootlineTasks	= self::getRootlineTasks($idTask);


			if( $checkSelf !== true ) {
					// Remove element itself
				array_shift($rootlineTasks);
			}

				// Check all parent elements if there is a container and use its dates for the range
			foreach($rootlineTasks as $task) {
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
	 * Get the rootline of a task (all parent task IDs)
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskRootline($idTask) {
		$idTask		= intval($idTask);

			// Check if already cached
		$idCache	= 'rootline:' . $idTask;

		if( TodoyuCache::isIn($idCache) ) {
			$rootline	= TodoyuCache::get($idCache);
		} else {
			$idParent	= self::getParentTaskID($idTask);

			$rootline	= array($idTask);

			while( $idParent !== 0 ) {
				$rootline[] = $idParent;
				$idParent = self::getParentTaskID($idParent);
			}

			TodoyuCache::set($idCache, $rootline);
		}

		return $rootline;
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

		$rootline	= self::getTaskRootline($idTask);
		$list		= implode(',', $rootline);

		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'id IN(' . $list . ')';
		$order	= 'FIND_IN_SET(id, \'' . $list . '\')';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
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
		$defaultData= self::getDefaultTaskData($idParentTask, $idProject, $type);

			// Store task with default data in cache
		$idCache	= TodoyuCache::makeClassKey('TodoyuTask', 0);
		$task		= new TodoyuTask(0);
		$task->injectData($defaultData);
		TodoyuCache::set($idCache, $task);
	}



	/**
	 * Check if a task exists
	 *
	 * @param	Integer		$idTask
	 * @return	Bool
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
	 * Check if a tasknumber is valid
	 * $mustExist is not set (default), only the format is checked.
	 * If $mustExist is set, also a database request will check if this task exists
	 *
	 * @param	Integer		$fullTasknumber			Identifier with project id and tasknumber
	 * @param	Bool		$mustExist				TRUE = Has to be in database
	 * @return
	 */
	public static function isTasknumber($fullTasknumber, $mustExist = false) {
		$valid	= false;

			// Check for point (.)
		if( strpos($fullTasknumber, '.') !== false ) {
				// Split into project/tasknumber
			$parts	= TodoyuArray::intExplode('.', $fullTasknumber, true, true);

				// If 2 valid integers found
			if( sizeof($parts) === 2 ) {
					// Database check required?
				if( $mustExist ) {
						// Get task ID for validation
					$idTask	= self::getTaskIDByTaskNumber($fullTasknumber);
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
	 * Check if a task is visible (available for rendering)
	 *
	 * @param	Integer		$idTask
	 * @return	Bool
	 */
	public static function isTaskVisible($idTask) {
		$idTask	= intval($idTask);

		if( self::isTask($idTask) ) {
			$task	= TodoyuTaskManager::getTask($idTask);

			if( $task['deleted'] == 0 ) {
				return true;
			}
		}

		return false;
	}



	/**
	 * Modify form for task edit
	 *
	 * @param	TodoyuForm		$form
	 * @param	Integer			$idTask
	 * @return	TodoyuForm
	 */
	public static function modifyFormfieldsForTask(TodoyuForm $form, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

		if( $task->isTask() ) {
				// New task have no parent
			if( $idTask === 0 ) {
//				$form->getField('id_parenttask')->remove();
				$form->addHiddenField('id_parenttask', 0);
			}
		}

		return $form;
	}



	/**
	 * Modify task form object for container editing
	 *
	 * @param	TodoyuForm	$form			Task edit form object
	 * @param	Integer		$idTask			Task ID
	 * @return	TodoyuForm	Moddified form object
	 */
	public static function modifyFormfieldsForContainer(TodoyuForm $form, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

			// Remove field which are not needed in the container
		if( $task->isContainer() ) {
			$form->getField('id_worktype')->remove();
			$form->getField('estimated_workload')->remove();
			$form->getField('is_estimatedworkload_public')->remove();
			$form->getField('date_start')->remove();
			$form->getField('date_end')->remove();
			$form->getField('date_deadline')->remove();
			$form->getField('id_user_assigned')->remove();
//			$form->getField('id_user_owner')->remove();
			$form->getField('status')->remove();

				// Remove
			if( $idTask === 0 ) {
				$form->getField('id_parenttask')->remove();
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
	 * @param	Bool		$withSubtasks
	 * @param	Integer		$idProject
	 * @return	Integer
	 */
	public static function copyTask($idTask, $idParent, $withSubtasks = true, $idProject = 0) {
		$idTask		= intval($idTask);
		$idParent	= intval($idParent);
		$idProject	= intval($idProject);

			// Get orginal task data
		$data		= self::getTaskData($idTask);

			// Set new project id if given
		if( $idProject !== 0 ) {
			$data['id_project'] = $idProject;
		}
			// Set new parent (needed for sorting)
		$data['id_parenttask']	= $idParent;

			// Add new task (with old data)
		$idTaskNew	= self::addTask($data);

//		TodoyuDebug::printLastQueryInFirebug('copy task');

			// Call data modifier hook, so other extensions can change data if they want
		$hookData	= array(
			'idTask'	=> $idTask,
			'idTaskNew'	=> $idTaskNew,
			'idParent'	=> $idParent,
			'idProject'	=> $idProject
		);
		$data		= TodoyuHookManager::callHookDataModifier('project', 'taskcopy', $data, $hookData);

			// Set status
		$data['status']		= STATUS_OPEN;
			// Remove old tasknumber
		unset($data['tasknumber']);

			// Update task data
		self::updateTask($idTaskNew, $data);

			// Copy subtasks if enabled
		if( $withSubtasks && $idTask !== $idParent ) {
			$subtaskIDs = self::getSubtaskIDs($idTask);

			foreach($subtaskIDs as $idSubtask) {
				$idSubtaskNew = self::copyTask($idSubtask, $idTaskNew, true, $idProject);
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
	 */
	public static function moveTask($idTask, $idParentTask, $idProject = 0) {
		$idTask			= intval($idTask);
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$taskData		= self::getTaskData($idTask);
		$parentData		= self::getTaskData($idParentTask);

			// Basic update
		$update		= array(
			'id_parenttask'	=> $idParentTask
		);

		if( $idParentTask !== 0 ) {
			$update['id_project'] = $parentData['id_project'];
		} else {
			$update['id_project'] = $idProject;
		}

			// If project changed, generate a new tasknumber
		if( $taskData['id_project'] != $parentData['id_project'] ) {
			$update['tasknumber']	= TodoyuProjectManager::getNextTaskNumber($parentData['id_project']);
		}

			// Update the moved task
		self::updateTask($idTask, $update);

			// If project changed, update also all subtasks with new project ID and generate new tasknumber
		if( $taskData['id_project'] != $parentData['id_project'] ) {
			$allSubtaskIDs	= self::getAllSubtaskIDs($idTask);

			foreach($allSubtaskIDs as $idSubtask) {
				$subUpdate	= array(
					'id_project'	=> $parentData['id_project'],
					'tasknumber'	=> TodoyuProjectManager::getNextTaskNumber($parentData['id_project'])
				);

				Todoyu::db()->updateRecord(self::TABLE, $idSubtask, $subUpdate);
			}
		}

		return $idTask;
	}



	/**
	 * Clone a task
	 *
	 * @param 	Integer		$idTask
	 * @return	Integer
	 */
	public static function cloneTask($idTask, $withSubtasks = true) {
		$idTask		= intval($idTask);
		$taskData	= TodoyuTaskManager::getTaskData($idTask);

		$idNewTask	= self::copyTask($idTask, $taskData['id_parenttask'], $withSubtasks, $taskData['id_project']);

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
		$where	= '	id_project		= ' . $taskMove['id_project'] . ' AND
					id_parenttask	= ' . $taskRef['id_parenttask'];
		$noQuote= array('sorting');

			// Move other task which are between the move and the ref task
			// Adjust the reference sorting position
		$refSort= $after ? $taskRef['sorting'] + 1 : $taskRef['sorting'] - 1;

			// If task get a higher postion
		if( $taskMove['sorting'] < $taskRef['sorting'] ) {
			$min	= $taskMove['sorting'];
			$max	= $refSort;
			$update['sorting']	= 'sorting-1';
		} else {
			$min	= $refSort;
			$max	= $taskMove['sorting'];
			$update['sorting']	= 'sorting+1';
		}

			// Limits for updateing other tasks
		$where .= ' AND sorting > ' . $min . ' AND
						sorting < ' . $max;

		Todoyu::db()->doUpdate($table, $where, $update, $noQuote);

			// Update moved task
		$update['sorting'] = $after ? $taskRef['sorting'] + 1 : $taskRef['sorting'];

		Todoyu::db()->updateRecord(self::TABLE, $idTaskMove, $update, $noQuote);
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

}

?>