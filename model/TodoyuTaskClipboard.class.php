<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
class TodoyuTaskClipboard {

	/**
	 * Add a task to clipboard
	 *
	 * @param	Integer		$idTask				Task to hold on clipboard
	 * @param 	String		$mode				Clipboard mode
	 * @param	Bool		$withSubtasks		Copy subtasks
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
	 * Get clipboard data (task, mode, subtasks)
	 *
	 * @return	Array
	 */
	public static function getData() {
		$data	= TodoyuClipboard::get('task');

		return TodoyuArray::assure($data);
	}



	/**
	 * Check if a task is in clipboard
	 *
	 * @return	Bool
	 */
	public static function hasTask() {
		return TodoyuClipboard::has('task');
	}



	/**
	 * Get current clipboard mode
	 *
	 * @return	String
	 */
	public static function getCurrentMode() {
		$data	= self::getData();

		return $data['mode'];
	}


	/**
	 * Get current task ID in clipboard
	 *
	 * @return	Integer
	 */
	public static function getCurrentTask() {
		$data	= self::getData();

		return intval($data['task']);
	}



	/**
	 * Check if clipboard is in copy mode
	 *
	 * @return	Bool
	 */
	public static function isInCopyMode() {
		return self::getCurrentMode() === 'copy';
	}



	/**
	 * Check if clipboard is in cut mode
	 *
	 * @return	Bool
	 */
	public static function isInCutMode() {
		return self::getCurrentMode() === 'cut';
	}



	/**
	 * Clear clipboard (remove current task)
	 *
	 */
	public static function clear() {
		TodoyuClipboard::remove('task');
	}



	/**
	 * Add task for copy mode
	 *
	 * @param	Integer		$idTask
	 * @param	Bool		$widthSubtasks
	 */
	public static function addTaskCopy($idTask, $widthSubtasks = true) {
		self::addTask($idTask, 'copy', $widthSubtasks);
	}



	/**
	 * Add task for cut mode
	 *
	 * @param	Integer		$idTask
	 */
	public static function addTaskCut($idTask) {
		self::addTask($idTask, 'cut', true);
	}



	/**
	 * Paste current task in clipboard
	 *
	 * @param	Integer		$idCurrentTask		New parent task
	 * @param	String		$insertMode		Insert mode (before,in,after)
	 * @return	Integer		New task ID (or old if only moved)
	 */
	public static function pasteTask($idCurrentTask = 0, $insertMode = 'in') {
		$idCurrentTask	= intval($idCurrentTask);
		$currentTask	= TodoyuTaskManager::getTaskData($idCurrentTask);
		$dataClipboard	= self::getData();


			// In: Current is parent, After/Before: Currents parent is the parent
		if( $insertMode === 'in' ) {
			$idParentTask = $idCurrentTask;
		} else {
			$idParentTask = $currentTask['id_parenttask'];
		}

		$taskParent		= TodoyuTaskManager::getTaskData($idParentTask);

		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask = TodoyuTaskManager::copyTask($dataClipboard['task'], $idParentTask, $dataClipboard['subtasks'], $currentTask['id_project']);
		} elseif( $dataClipboard['mode'] === 'cut' ) {
			$idNewTask = TodoyuTaskManager::moveTask($dataClipboard['task'], $idParentTask, $currentTask['id_project']);
		}

			// Clear clipboard
		self::clear();

			// Fix order
		if( $insertMode === 'after' || $insertMode === 'before' ) {
			TodoyuTaskManager::changeTaskOrder($idNewTask, $idCurrentTask, $insertMode);
		}

			// Send current mo
		TodoyuHeader::sendTodoyuHeader('clipboardMode', $dataClipboard['mode']);

		return $idNewTask;
	}



	/**
	 * Add contextmenu to paste tasks
	 *
	 * @param	Integer		$idTask
	 * @param	Array		$items
	 * @return	Array
	 */
	public static function getTaskContextMenuItems($idTask, array $items) {
		$idTask	= intval($idTask);

			// Only show context menu in project area and if something is on the clipboard
		if( self::hasTask() ) {
			$ownItems	= Todoyu::$CONFIG['EXT']['project']['ContextMenu']['TaskClipboard'];

				// Paste is only available in project view
			if( AREA === EXTID_PROJECT && TodoyuTaskRights::isAddAllowed($idTask) ) {
				$mergeItems	= $ownItems;
				$data		= self::getData();
				$isSubtask	= TodoyuTaskManager::isSubtaskOf($idTask, $data['task'], true);

					// Don't allow paste on itself or subtaks when: cut-mode or with-subtasks
				if( $idTask == $data['task'] || $isSubtask ) {
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

}

?>