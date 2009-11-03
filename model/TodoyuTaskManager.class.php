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

		return TodoyuCache::getRecord('TodoyuTask', $idTask);
	}



	/**
	 * Get task record
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskArray($idTask) {
		return Todoyu::db()->getRecord(self::TABLE, $idTask);
	}



	/**
	 * Get task ID by full task number
	 *
	 * @param	String		$fullTasknumber			Tasknumber divided by point (.)
	 * @return	Integer		0 if task not found
	 */
	public static function getTaskIDByTaskNumber($fullTasknumber) {
		$idTask	= 0;
		$parts	= TodoyuDiv::intExplode('.', $fullTasknumber, true, true);

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
			$idTask = self::addTask();

				// Set tasknumber
			$data['tasknumber']	= TodoyuProjectManager::getNextTaskNumber($idProject);
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

		if( empty($data['tasknumber']) ) {
			$idProject	= intval($data['id_project']);
			$data['tasknumber'] = TodoyuProjectManager::getNextTaskNumber($idProject);
		}

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Delete a task
	 *
	 * @param	Integer		$idTask
	 */
	public static function deleteTask($idTask, $deleteSubtasks = true) {
		$data	= array('deleted'		=> 1,
		'date_update'	=> NOW);

		self::updateTask($idTask, $data);

		if( $deleteSubtasks ) {
			self::deleteSubtasks($idTask, true);
		}
	}



	/**
	 * Delete all subtasks
	 *
	 * @param	Integer		$idTask				Task ID whichs subtaks shall be deleted
	 * @param	Bool		$recursive			Delete recursive => delete subtasks of subtasks of subtasks ...
	 */
	public static function deleteSubtasks($idTask, $recursive = false) {
		$idTask	= intval($idTask);

		// Delete all subtasks of the subtasks
		if( $recursive ) {
			$subtaskIDs	= self::getSubtaskIDs($idTask);

			foreach($subtaskIDs as $idSubtask) {
				self::deleteSubtasks($idSubtask, true);
			}
		}

		// Delete all subtasks
		$table	= self::TABLE;
		$where	= 'id_parenttask = ' . $idTask;
		$data	= array('deleted'		=> 1,
		'date_update'	=> NOW);

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
		$data['id_user_assigned']	= userid();

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

			// Set finish date if task is done
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

		if( $task->isTask() ) {
			$ownItems	=& $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Task'];
		} elseif( $task->isContainer() ) {
			$ownItems	=& $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['Container'];
		}


		if( $task->isTask() || $task->isContainer() ) {
			$allowed['header']	= $ownItems['header'];

				// Edit
			if( allowed('project', 'task:edit') ) {
				$allowed['edit'] = $ownItems['edit'];
			}

				// Clone
			if( allowed('project', 'task:clone') ) {
				$allowed['clone'] = $ownItems['clone'];
			}

				// Add
			if( ! allowed('project', 'task:addtask') ) {
				unset($ownItems['add']['submenu']['task']);
			}
			if( ! allowed('project', 'task:addcontainer') ) {
				unset($ownItems['add']['submenu']['container']);
			}
			$allowed['add'] = $ownItems['add'];

				// Status
			if( allowed('project', 'task:status') ) {
				$allowed['status'] = $ownItems['status'];
			}

				// Delete
			if( allowed('project', 'task:delete') ) {
				$allowed['delete'] = $ownItems['delete'];
			}
		}

		return array_merge_recursive($items, $allowed);
	}



	/**
	 * Get the IDs of all subtasks of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubtaskIDs($idTask) {
		$idTask	= intval($idTask);

		$subtasks	= self::getSubtasks($idTask);

		return TodoyuArray::getColumn($subtasks, 'id');
	}



	/**
	 * Get subtasks (as data array) of a task
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getSubtasks($idTask) {
		$idTask	= intval($idTask);

		if($idTask === 0)	{
			return array();
		}

		$field	= '*';
		$table	= self::TABLE;
		$where	= '	id_parenttask	= ' . $idTask . ' AND deleted	= 0';
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
			self::$tabs = TodoyuArray::sortByLabel($GLOBALS['CONFIG']['EXT']['project']['task']['tabs']);
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
	public static function registerTaskTab($idTab, $labelFunction, $contentFunction, $position = 100) {
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
	 * Get label for the contextmenu of a task
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function getContextMenuHeader($idTask) {
		$task	= self::getTaskArray($idTask);

		return TodoyuDiv::cropText($task['title'], 24, '', false);
	}



	/**
	 * Get all task data informations.
	 * Information from all extensions are merged, labels are parsed and the list is sorted
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskDataArray($idTask) {
		$idTask		= intval($idTask);
		$taskData	= array();

		$hookData	= TodoyuHookManager::callHook('project', 'taskdata', array($idTask));

		foreach($hookData as $hookTaskData) {
			$taskData	= array_merge($taskData, $hookTaskData);
		}

		$taskData = TodoyuArray::sortByLabel($taskData);

		return $taskData;
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
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getTaskDataAttributes($idTask) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);
		$data	= $task->getTemplateData(2);

		$info	= array();

			// Status
		$info[]	= array(
			'label'		=> Label('task.attr.status'),
			'value'		=> $data['statuslabel'],
			'position'	=> 20
		);

			// Date create
		$info[]	= array(
			'label'	=> Label('task.attr.date_create'),
			'value'	=> TodoyuTime::format($data['date_create'], 'datetime'),
			'position'	=> 50
		);

			// Date start
		$info[]	= array(
			'label'	=> Label('task.attr.date_start'),
			'value'	=> TodoyuTime::format( $data['date_start'], 'date'),
			'position'	=> 60
		);

			// Date deadline
		if( $data['date_deadline'] > 0 ) {
			$formatDeadline	= date('s', $data['date_deadline']) === '00' ? 'date' : 'datetime';
			$info[]	= array(
				'label'	=> Label('task.attr.date_deadline'),
				'value'	=> TodoyuTime::format($data['date_deadline'], $formatDeadline),
				'position'	=> 80
			);
		}

			// Attributes which are only for tasks
		if( $task->isTask() ) {
				// Worktype
			$info[] = array(
				'label'		=> Label('task.attr.worktype'),
				'value'		=> $data['worktype']['title'],// 'Internes / Administration',
				'position'	=> 10
			);

				// Estimated workload
			$info[]	= array(
				'label'	=> Label('task.attr.esitmated_workload'),
				'value'	=> TodoyuTime::sec2hour($data['estimated_workload']),
				'position'	=> 30
			);

				// Date end (if set)
			$formatEnd	= date('m', $data['date_end']) === '00' ? 'date' : 'datetime';
			$info[]	= array(
				'label'	=> Label('task.attr.date_end'),
				'value'	=> TodoyuTime::format($data['date_end'], $formatEnd),
				'position'	=> 70
			);

				// User owner
			$info[]	= array(
				'label'	=> Label('task.attr.user_owner'),
				'value'	=> '<a href="javascript:void(0)" onclick="alert(\'Quick user detail anzeigen\')" class="quickInfoLink">' . $data['user_owner']['lastname'] . ', ' . $data['user_owner']['firstname'] . '</a>',
				'position'	=> 90
			);

				// User assigned
			$info[]	= array(
				'label'	=> Label('task.attr.user_assigned'),
				'value'	=> '<a href="javascript:void(0)" onclick="alert(\'Quick user detail anzeigen\')" class="quickInfoLink">' . $data['user_assigned']['lastname'] . ', ' . $data['user_assigned']['firstname'] . '</a>',
				'position'	=> 100
			);
		}

		return $info;
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

			// Add special class Todoyufor containers
		if( $task->isContainer() ) {
			$taskData['class'] .= ' container';
		}

		return $taskData;
	}



	/**
	 * Get all icons for a task (provided by all installed extensions)
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAdditionalTaskIcons($idTask) {
		$idTask	= intval($idTask);
		$icons	= array();

		$temp	= TodoyuHookManager::callHook('project', 'additionalIcons', array($idTask));

		foreach($temp as $hookIcons) {
			$icons	= array_merge($icons, $hookIcons);
		}

		return $icons;
	}



	/**
	 * Get additional task icons provided by the project extension
	 * This functions is called by getAdditionalTaskIcons()
	 *
	 * @param	Integer		$idTask
	 * @return	Array
	 */
	public static function getAdditionalProjectTaskIcons($idTask) {
		$icons	= array();

		$task	= self::getTask($idTask);

			// Add container icon
		if( $task->isContainer() ) {
			$icons[] = array(
				'class'	=> 'taskcontainer',
				'label'	=> Label('LLL:task.type.container')
			);
		}

			// Add is public icon
		if( $task->isPublic() ) {
			$icons[] = array(
				'class'	=> 'isPublic',
				'label'	=> Label('LLL:task.attr.is_public')
			);
		}

		return $icons;
	}



	/**
	 * Get all info icons
	 *
	 * @param	Integer	$idTask
	 * @return	Array
	 */
	public static function getAllTaskInfoIcons($idTask) {
		$idTask	= intval($idTask);
		$icons	= array();

		$iconSets = TodoyuHookManager::callHook('project', 'infoIcons', array($idTask));

		foreach($iconSets as $hookIcons) {
			$icons	= array_merge($icons, $hookIcons);
		}

		return $icons;
	}



	/**
	 * Get project task info icons
	 *
	 * @param	Integer	$idTask
	 * @return	Array
	 */
	public static function getProjectTaskInfoIcons($idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

		$icons	= array();

		if( ! $task->isAcknowledged() ) {
			$icons[]= array(
				'id'		=> 'task-' . $idTask . '-notacknowledged',
				'class'		=> 'notAcknowledged',
				'label'		=> Label('task.attr.notAcknowledged'),
				'onclick'	=> 'Todoyu.Ext.project.Task.setAcknowledged(' . $idTask . ')'
			);
		}

		if( $task->getDeadlineDate() > NOW || $task->getEndDate() > NOW ) {
			$icons[]= array(
				'id'		=> 'task-' . $idTask . '-timeover',
				'class'		=> 'timeover',
				'label'		=> Label('task.attr.timeover')
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
	 * Clone a task
	 *
	 * @param 	Integer		$idTask
	 * @return	Integer
	 */
	public static function cloneTask($idTask, array $options = array()) {
		$idTask	= intval($idTask);
		$task	= self::getTaskArray($idTask);

			// Remove id to get a new one on insert
		unset($task['id']);

			// Set new fields for the cloned version
		$task['tasknumber']		= TodoyuProjectManager::getNextTaskNumber($task['id_project']);
		$task['is_acknowledged']= 0;
		$task['title']			= 'Kopie von: ' . $task['title'];
		$task['id_user_owner']	= userid();

		$task = array_merge($task, $options);

		return self::addTask($task);
	}



	/**
	 * Clone given container
	 *
	 * @param	Integer	$idContainer
	 * @param	Array	$options
	 * @param	Boolean	$cloneSubElements
	 * @return	Integer	ID of new container
	 */
	public static function cloneContainer($idContainer, array $options = array(), $cloneSubElements = false) {
		$idContainer	= intval($idContainer);
		$idNewContainer	= self::cloneTask($idContainer, $options);

		if( $cloneSubElements ) {
			self::cloneSubElements($idContainer, $idNewContainer);
		}

		return $idNewContainer;
	}



	/**
	 * Clone sub elements of given parent element
	 *
	 * @param	Integer	$idSourceElement
	 * @param	Integer	$idTargetElement
	 */
	public static function cloneSubElements($idSourceElement, $idTargetElement) {
		$idSourceElement	= intval($idSourceElement);
		$idTargetElement	= intval($idTargetElement);

		$subElements = self::getSubtasks($idSourceElement);

		// Force a new parent task for the cloned object
		$options = array('id_parenttask' => $idTargetElement);

		foreach($subElements as $subElement) {

			switch( $subElement['type'] ) {
				case TASK_TYPE_TASK:
					self::cloneTask($subElement['id'], $options);
					break;

				case TASK_TYPE_CONTAINER:
					self::cloneContainer($subElement['id'], $options, true);
					break;
			}

		}
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
	 */
	public static function getTasksInTimeSpan($timeStart = 0, $timeEnd = 0, array $statusIDs = array(), array $userIDs = array(), $limit = '') {
		$timeStart	= intval($timeStart);
		$timeEnd	= intval($timeEnd);
		$statusIDs	= TodoyuArray::intval($statusIDs, true, true);
		$userIDs	= TodoyuArray::intval($userIDs, true, true);

		$fields	= '*';
		$table	= self::TABLE;

		$where	=  ' deleted 	= 0 ';
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
		$project		= TodoyuProjectManager::getProject($idProject);
		$parentTask		= TodoyuTaskManager::getTask($idParentTask);


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
			'date_start'		=> $dateStart,
			'date_deadline'		=> $dateEnd,
			'date_end'			=> $dateEnd,
			'id_user_owner'		=> $idUser,
			'id_user_assigned'	=> $idUser,
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
		$order	= 'FIND_IN_SET(id, ' . $list . ')';

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
	 * Add a new temporary task. The new task has the deleted flag (which will
	 * be removed when task is saved) and is filled in with default values
	 *
	 * @param	Integer		$idParentTask
	 * @param	Integer		$idProject
	 * @return	Integer		ID of the temporary task
	 */
	public static function addTemporaryTask($idParentTask, $idProject = 0, $type = TASK_TYPE_TASK) {
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);

		if( $idParentTask !== 0 ) {
			$idProject	= self::getProjectID($idParentTask);
		}

			// Add temporary task
		$idTempTask	= self::addTask($idProject);
		$idUser		= TodoyuAuth::getUserID();

			// Update temporary task, set deleted
		$update	= array(
			'deleted'			=> 1,
			'title'				=> Label('task.newTask.empty'),
			'status'			=> STATUS_OPEN,
			'estimated_workload'=> intval($GLOBALS['CONFIG']['EXT']['project']['Task']['defaultEstimatedWorkload']),
			'id_project'		=> $idProject,
			'id_parenttask'		=> $idParentTask,
			'date_start'		=> NOW,
			'date_deadline'		=> NOW,
			'date_end'			=> NOW,
			'id_user_owner'		=> $idUser,
			'id_user_assigned'	=> $idUser,
			'type'				=> intval($type)
			);

		$update	= TodoyuHookManager::callHookDataModifier('project', 'addTemporaryTask', $update, array($idProject, $idParentTask));

		self::updateTask($idTempTask, $update);

		return $idTempTask;
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
			$task = self::getTaskArray($idTask);

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
			$parts	= TodoyuDiv::intExplode('.', $fullTasknumber, true, true);

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

			// If new, no need for a parent to set
		if( $idTask === 0 ) {
			$form->getField('id_parenttask')->remove();
		}

		return $form;
	}



	/**
	 * Modify task form object for container editing
	 *
	 * @param	TodoyuForm		$form			Task edit form object
	 * @param	Integer		$idTask			Task ID
	 * @return	TodoyuForm		Moddified form object
	 */
	public static function modifyFormfieldsForContainer(TodoyuForm $form, $idTask) {
		$idTask	= intval($idTask);
		$task	= self::getTask($idTask);

			// Remove field which are not needed in the container
		if( $task->isContainer() ) {
			$form->getField('id_worktype')->remove();
			$form->getField('estimated_workload')->remove();
			$form->getField('is_estimatedworkload_public')->remove();
			$form->getField('date_end')->remove();
			$form->getField('id_user_assigned')->remove();
			$form->getField('id_user_owner')->remove();
		}

		return $form;
	}
}


?>