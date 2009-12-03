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

	public static function addTask($idTask, $mode = 'copy', $withSubtasks = true) {
		$idTask	= intval($idTask);
		$data	= array(
			'mode'		=> $mode,
			'task'		=> $idTask,
			'subtasks'	=> $withSubtasks
		);

		TodoyuClipboard::set('task', $data);
	}


	public static function getData() {
		$data	= TodoyuClipboard::get('task');

		return TodoyuArray::assure($data);
	}



	public static function hasTask() {
		return TodoyuClipboard::has('task');
	}

	public static function clear() {
		TodoyuClipboard::remove('task');
	}


	public static function addTaskCopy($idTask, $widthSubtasks = true) {
		self::addTask($idTask, 'copy', $widthSubtasks);
	}

	public static function addTaskCut($idTask) {
		self::addTask($idTask, 'cut', true);
	}

	public static function pasteTask($idParent = 0, $mode = 'in') {
		$idParent		= intval($idParent);
		$dataClipboard	= self::getData();
		$taskParent		= TodoyuTaskManager::getTaskData($idParent);

		if( $dataClipboard['mode'] === 'copy' ) {
			$idNewTask	= TodoyuTaskManager::copyTask($dataClipboard['task'], $idParent, $dataClipboard['subtasks'], $taskParent['id_project']);
		} elseif( $dataClipboard['mode'] === 'cut' ) {
			TodoyuTaskManager::moveTask($dataClipboard['task'], $idParent, $dataClipboard['id_project']);
			$idNewTask = $dataClipboard['task'];
		}

		self::clear();

		TodoyuHeader::sendTodoyuHeader('clipboardAction', $dataClipboard['mode']);

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