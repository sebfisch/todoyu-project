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
	 * @var	String	Paste failure identifier - wrong area
	 */
	const TASK_CONTEXTMENU_PASTE_ERROR_PROJECTAREA	= 'projectarea';
	/**
	 * @var	String	Paste failure identifier - task is locked
	 */
	const TASK_CONTEXTMENU_PASTE_ERROR_LOCKED		= 'locked';
	/**
	 * @var	String	Paste failure identifier - paste not allowed
	 */
	const TASK_CONTEXTMENU_PASTE_ERROR_NOTALLOWED	= 'notallowed';
	/**
	 * @var	String	Paste failure identifier - task cannot be pasted into itself
	 */
	const TASK_CONTEXTMENU_PASTE_ERROR_SELF			= 'self';



	/**
	 * Add a task to clipboard
	 *
	 * @param	Integer		$idTask				Task to hold on clipboard
	 * @param	String		$mode				Clipboard mode
	 * @param	Boolean		$withSubTasks		Copy sub tasks
	 */
	public static function addTask($idTask, $mode = 'copy', $withSubTasks = true) {
		$idTask	= intval($idTask);
		$data	= array(
			'mode'		=> $mode,
			'task'		=> $idTask,
			'subtasks'	=> $withSubTasks
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
		$idParentTask = $insertMode === 'in' ? $idWorkingTask : $workingTask['id_parenttask'];

			// Copy or move the task
		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask = TodoyuProjectTaskManager::copyTask($dataClipboard['task'], $idParentTask, $dataClipboard['subtasks'], $workingTask['id_project']);
		} else {
			$idNewTask = TodoyuProjectTaskManager::changeTaskParent($dataClipboard['task'], $idParentTask, $workingTask['id_project']);
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
		$idNewTask	= null;
		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask = TodoyuProjectTaskManager::copyTask($dataClipboard['task'], 0, $dataClipboard['subtasks'], $idProject);
		} elseif( $dataClipboard['mode'] === 'cut' ) {
			$idNewTask = TodoyuProjectTaskManager::changeTaskParent($dataClipboard['task'], 0, $idProject);
		}

			// Clear clipboard
		self::clear();

			// Send active clipboard mode for cleanup in javascript
		TodoyuHeader::sendTodoyuHeader('clipboardMode', $dataClipboard['mode']);

		return $idNewTask;
	}



	/**
	 * Get "own" (implemented by project extension) task / project contextmenu items
	 *
	 * @param	String	$contextMenu	Identifier
	 * @return	Array
	 */
	public static function getContextMenuOwnItems($contextMenu = 'TaskClipboard') {
		$ownItems	= TodoyuArray::assure(Todoyu::$CONFIG['EXT']['project']['ContextMenu'][$contextMenu]);

			// Change labels for paste-mode and item type and
		$ownItems['paste']['label'] .= self::isInCutMode() ? '.cut' : '.copy';

		if( self::getClipboardTask()->isContainer() ) {
			$ownItems['paste']['label'] .= '.container';
		}
		TodoyuDebug::printInFirebug($ownItems, 'oi');
		return $ownItems;
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

			$clipboardTask	= self::getClipboardTask();
			$isLocked		= $clipboardTask->isLocked(true);
			$contextTask	= TodoyuProjectTaskManager::getTask($idTaskContextmenu);
			$isSameProject	= $clipboardTask->getProjectID() === $contextTask->getProjectID();
			$isAddAllowed	= TodoyuProjectTaskRights::isAddAllowed($idTaskContextmenu);
			$isProjectArea	= AREA === EXTID_PROJECT;
			$isCopyMode		= self::isInCopyMode();
			$mergeItems		= array();

			$ownItems	= self::getContextMenuOwnItems('TaskClipboard');

				// Paste is only available in project view
			if( $isProjectArea && $isAddAllowed && ($isCopyMode || ! $isLocked || $isSameProject) ) {
				$mergeItems	= $ownItems;
				$isSubTask	= TodoyuProjectTaskManager::isSubTaskOf($idTaskContextmenu, $data['task'], true);

					// Don't allow paste on itself or sub tasks when: cut mode or with sub tasks
				if( $idTaskContextmenu == $data['task'] || $isSubTask ) {
					if( $data['mode'] === 'cut' || $data['subtasks'] ) {
						$mergeItems = array();
						$errorCode = self::TASK_CONTEXTMENU_PASTE_ERROR_SELF;
					}
				}
			} else {
					// Paste not available
				$errorCode = self::getPasteErrorCode($isProjectArea, $isLocked);
			}

			if( sizeof($mergeItems) === 0 ) {
				$mergeItems = self::mergeItemsPasteNotAllowed($ownItems, $errorCode);
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
			$clipboardTask	= self::getClipboardTask();
			$isLocked		= $clipboardTask->isLocked(true);
			$isSameProject	= $clipboardTask->getProjectID() === $idProjectContextmenu;
			$isAddAllowed	= TodoyuProjectTaskRights::isAddInProjectAllowed($idProjectContextmenu, $clipboardTask->isContainer());
			$isProjectArea	= AREA === EXTID_PROJECT;
			$isCopyMode		= self::isInCopyMode();

			$ownItems	= self::getContextMenuOwnItems('TaskClipboardProject');

				// Paste only when allowed & in project view & when tasks are not looked & when cut-pasting in same project
			if( $isProjectArea && $isAddAllowed && ($isCopyMode || ! $isLocked || $isSameProject) ) {
				$mergeItems	= $ownItems;
			} else {
				$errorCode = self::getPasteErrorCode($isProjectArea, $isAddAllowed, $isLocked);
				$mergeItems = self::mergeItemsPasteNotAllowed($ownItems, $errorCode);
			}

			$items	= array_merge_recursive($items, $mergeItems);
		}

		return $items;
	}



	/**
	 * @static
	 * @param	Boolean		$isProjectArea
	 * @param	Boolean		$isLocked
	 * @return	String
	 */
	protected static function getPasteErrorCode($isProjectArea, $isLocked) {
		if ( ! $isProjectArea ) {
			return self::TASK_CONTEXTMENU_PASTE_ERROR_PROJECTAREA;
		} else if ( $isLocked ) {
			return self::TASK_CONTEXTMENU_PASTE_ERROR_LOCKED;
		}

		return self::TASK_CONTEXTMENU_PASTE_ERROR_NOTALLOWED;
	}



	/**
	 * @static
	 * @param	Array		$ownItems
	 * @return	Array
	 */
	protected static function mergeItemsPasteNotAllowed($ownItems, $errorCode) {
		$mergeItems = $ownItems;
		unset($mergeItems['paste']['submenu']);

		$mergeItems['paste']['submenu']['message'] = array(
			'key'		=> 'pastewarning',
			'class'		=> 'taskContextMenu pasteWarning',
			'label'		=> 'project.task.contextmenu.paste.error.' . $errorCode,
			'jsAction'	=> 'void(0)',
		);

		$mergeItems['paste']['class'] .= ' disabled';
		$mergeItems['paste']['jsAction'] = 'Todoyu.Ext.project.Task.pasteNotAllowed()';

		return $mergeItems;
	}

}

?>