<?php

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


	public static function getTaskContextMenuItems($idTask, array $items) {
		$idTask	= intval($idTask);

		if( self::hasTask() ) {
			$ownItems	= $GLOBALS['CONFIG']['EXT']['project']['ContextMenu']['TaskClipboard'];

			$items	= array_merge_recursive($items, $ownItems);
		}

		return $items;
	}


}

?>