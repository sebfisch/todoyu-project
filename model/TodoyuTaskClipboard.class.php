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
		if( AREA === EXTID_PROJECT && self::hasTask() ) {
			$ownItems	= $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['TaskClipboard'];






			$items	= array_merge_recursive($items, $ownItems);
		}



		return $items;
	}

}

?>