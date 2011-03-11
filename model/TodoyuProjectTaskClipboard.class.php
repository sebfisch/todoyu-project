<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
 * Project task clipboard
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskClipboard {

	/**
	 * Add a task to clipboard
	 *
	 * @param	Integer		$idTask				Task to hold on clipboard
	 * @param	String		$mode				Clipboard mode
	 * @param	Boolean		$withSubtasks		Copy sub tasks
	 */
	public static function addTask($idTask, $mode = 'copy', $withSubtasks = true) {
		$idTask	= intval($idTask);
		$data	= array(
			'mode'		=> $mode,
			'task'		=> $idTask,
			'subtasks'	=> $withSubtasks
		);

		TodoyuClipboard::set('task', $data);
	}



	/**
	 * Get clipboard data (task, mode, sub tasks)
	 *
	 * @return	Array
	 */
	public static function getData() {
		$data	= TodoyuClipboard::get('task');

		return TodoyuArray::assure($data);
	}



	/**
	 * Check whether a task is in clipboard
	 *
	 * @return	Boolean
	 */
	public static function hasTask() {
		return TodoyuClipboard::has('task');
	}



	/**
	 * Get current clipboard mode
	 *
	 * @return	String
	 */
	public static function getMode() {
		$data	= self::getData();

		return $data['mode'];
	}



	/**
	 * Get current task ID in clipboard
	 *
	 * @return	Integer
	 */
	public static function getClipboardTaskID() {
		$data	= self::getData();

		return intval($data['task']);
	}



	/**
	 * Get current task
	 *
	 * @return		TodoyuProjectTask
	 */
	public static function getClipboardTask() {
		return TodoyuProjectTaskManager::getTask(self::getClipboardTaskID());
	}



	/**
	 * Check whether clipboard is in copy mode
	 *
	 * @return	Boolean
	 */
	public static function isInCopyMode() {
		return self::getMode() === 'copy';
	}



	/**
	 * Check whether clipboard is in cut mode
	 *
	 * @return	Boolean
	 */
	public static function isInCutMode() {
		return self::getMode() === 'cut';
	}



	/**
	 * Clear clipboard (remove current task)
	 */
	public static function clear() {
		TodoyuClipboard::remove('task');
	}



	/**
	 * Add task for copy mode
	 *
	 * @param	Integer		$idTask
	 * @param	Boolean		$widthSubTasks
	 */
	public static function addTaskForCopy($idTask, $widthSubTasks = true) {
		self::addTask($idTask, 'copy', $widthSubTasks);
	}



	/**
	 * Add task for cut mode
	 *
	 * @param	Integer		$idTask
	 */
	public static function addTaskForCut($idTask) {
		self::addTask($idTask, 'cut', true);
	}



	/**
	 * Paste task from clipboard into given project
	 *
	 * @param	Integer		$idWorkingTask		New parent task
	 * @param	String		$insertMode			Insert mode (before,in,after)
	 * @return	Integer							New task ID (or old if only moved)
	 */
	public static function pasteTask($idWorkingTask = 0, $insertMode = 'in') {
		$idWorkingTask	= intval($idWorkingTask);
		$workingTask	= TodoyuProjectTaskManager::getTaskData($idWorkingTask);
		$dataClipboard	= self::getData();

			// In: Working task is parent, After/Before: Working tasks parent is the parent
		if( $insertMode === 'in' ) {
			$idParentTask = $idWorkingTask;
		} else {
			$idParentTask = $workingTask['id_parenttask'];
		}

			// Copy or move the task
		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask = TodoyuProjectTaskManager::copyTask($dataClipboard['task'], $idParentTask, $dataClipboard['subtasks'], $workingTask['id_project']);
		} elseif( $dataClipboard['mode'] === 'cut' ) {
			$idNewTask = TodoyuProjectTaskManager::moveTask($dataClipboard['task'], $idParentTask, $workingTask['id_project']);
		}

			// Clear clipboard
		self::clear();

			// Reorder tasks
		if( $insertMode === 'after' || $insertMode === 'before' ) {
			TodoyuProjectTaskManager::changeTaskOrder($idNewTask, $idWorkingTask, $insertMode);
		}

			// Send active clipboard mode for cleanup in javascript
		TodoyuHeader::sendTodoyuHeader('clipboardMode', $dataClipboard['mode']);

		return $idNewTask;
	}



	/**
	 * Paste cut/copied task from clipboard into given project
	 *
	 * @param	Integer		$idProject
	 * @return	Integer
	 */
	public static function pasteTaskInProject($idProject) {
		$idProject		= intval($idProject);
		$dataClipboard	= self::getData();

			// Copy or move the task
		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask = TodoyuProjectTaskManager::copyTask($dataClipboard['task'], 0, $dataClipboard['subtasks'], $idProject);
		} elseif( $dataClipboard['mode'] === 'cut' ) {
			$idNewTask = TodoyuProjectTaskManager::moveTask($dataClipboard['task'], 0, $idProject);
		}

			// Clear clipboard
		self::clear();

			// Send active clipboard mode for cleanup in javascript
		TodoyuHeader::sendTodoyuHeader('clipboardMode', $dataClipboard['mode']);

		return $idNewTask;
	}



	/**
	 * Add context menu to paste tasks
	 *
	 * @param	Integer		$idTaskContextmenu
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getTaskContextMenuItems($idTaskContextmenu, array $items) {
			// Only show context menu in project area and if something is on the clipboard
		if( self::hasTask() ) {
			$data			= self::getData();
			$ownItems		= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['project']['ContextMenu']['TaskClipboard']);
			$clipboardTask	= self::getClipboardTask();
			$contextTask	= TodoyuProjectTaskManager::getTask($idTaskContextmenu);
			$isLocked		= $clipboardTask->isLocked(true);
			$isSameProject	= $clipboardTask->getProjectID() === $contextTask->getProjectID();
			$isAddAllowed	= TodoyuProjectTaskRights::isAddAllowed($idTaskContextmenu);
			$isProjectArea	= AREA === EXTID_PROJECT;
			$mergeItems		= array();

			if( self::isInCutMode() ) {
				$ownItems['paste']['label'] .= '.cut';
			} else {
				$ownItems['paste']['label'] .= '.copy';
			}

				// Change labels for containers
			if( $clipboardTask->isContainer() ) {
				$ownItems['paste']['label'] .= '.container';
			}

				// Paste is only available in project view
			if( $isProjectArea && $isAddAllowed && (!$isLocked || $isSameProject) ) {
				$mergeItems	= $ownItems;

				$isSubTask	= TodoyuProjectTaskManager::isSubTaskOf($idTaskContextmenu, $data['task'], true);

					// Don't allow paste on itself or sub tasks when: cut mode or with sub tasks
				if( $idTaskContextmenu == $data['task'] || $isSubTask ) {
					if( $data['mode'] === 'cut' || $data['subtasks'] ) {
						$mergeItems = array();
					}
				}
			}

			if( sizeof($mergeItems) === 0 ) {
				$mergeItems = $ownItems;
				unset($mergeItems['paste']['submenu']);
				$mergeItems['paste']['class'] .= ' disabled';
				$mergeItems['paste']['jsAction'] = 'Todoyu.Ext.project.Task.pasteNotAllowed()';
			}

			$items	= array_merge_recursive($items, $mergeItems);
		}

		return $items;
	}



	/**
	 * Get task clipboard option items for context menu
	 *
	 * @param	Integer		$idProjectContextmenu
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getProjectContextMenuItems($idProjectContextmenu, array $items) {
		$idProjectContextmenu	= intval($idProjectContextmenu);

			// Only show context menu in project area and if something is on the clipboard
		if( self::hasTask() ) {
//			$data			= self::getData();
			$ownItems		= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['project']['ContextMenu']['TaskClipboardProject']);
			$clipboardTask	= self::getClipboardTask();

				// Change labels for containers
			if( $clipboardTask->isContainer() ) {
				$ownItems['paste']['label'] .= '.container';
			}

				// Paste is only available in project view
			if( AREA === EXTID_PROJECT && TodoyuProjectTaskRights::isAddInProjectAllowed($idProjectContextmenu) ) {
				$items	= array_merge_recursive($items, $ownItems);
			}
		}

		return $items;
	}

}

?>