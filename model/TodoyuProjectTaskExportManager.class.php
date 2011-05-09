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
 *
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskExportManager {

	/**
	 * @param	Array	$taskIDs
	 */
	public static function exportCSV(array $taskIDs) {
		$taskIDs	= TodoyuArray::intval($taskIDs);

		$tasksToExport	= self::prepareDataForExport($taskIDs);

		$export		= new TodoyuExportCSV($tasksToExport);

		$export->setFilename('todoyu_task_export_' . date('YmdHis') . '.csv');

		$export->download();
	}



	/**
	 * @param	Array	$taskIDs
	 * @return	Array
	 */
	public static function prepareDataForExport(array $taskIDs) {
		$taskIDs	= TodoyuArray::intval($taskIDs);

		$exportData	= array();

		foreach($taskIDs as $idTask)	 {
			$task	= TodoyuProjectTaskManager::getTask($idTask);

			$exportData[]	= self::parseDataForExport($task);
		}

		return $exportData;
	}



	/**
	 * @param	TodoyuProjectTask	$task
	 * @return	Array
	 */
	protected static function parseDataForExport(TodoyuProjectTask $task) {
		$exportData = array(
			Todoyu::Label('project.task.attr.id')					=> $task->getID(),
			Todoyu::Label('project.task.attr.date_create')			=> TodoyuTime::format($task->date_create, 'date'),
			Todoyu::Label('core.global.date_update')				=> TodoyuTime::format($task->date_update, 'date'),
			Todoyu::Label('core.global.id_person_create')			=> $task->getCreatePerson()->getFullName(),
			Todoyu::Label('project.task.attr.type')					=> $task->isContainer() ? Todoyu::Label('project.task.container') : Todoyu::Label('project.task.task'),
			Todoyu::Label('project.ext.project')					=> $task->getProject()->getFullTitle(),
			Todoyu::Label('project.task.attr.id_parenttask')		=> $task->hasParentTask() ? $task->getParentTask()->getFullTitle() : '',
			Todoyu::Label('project.task.attr.title')				=> $task->getFullTitle(),
			Todoyu::Label('project.task.description')				=> TodoyuString::strictHtml2text($task->getDescription()),
			Todoyu::Label('project.task.attr.person_assigned')		=> $task->getPerson('assigned')->getFullName(),
			Todoyu::Label('project.task.attr.person_owner')			=> $task->getPerson('owner')->getFullName(),
			Todoyu::Label('project.task.attr.date_deadline')		=> TodoyuTime::format($task->getDeadlineDate()),
			Todoyu::Label('project.task.attr.date_start')			=> TodoyuTime::format($task->getStartDate()),
			Todoyu::Label('project.task.attr.date_end')				=> TodoyuTime::format($task->getEndDate()),
			Todoyu::Label('project.task.taskno')					=> $task->getTaskNumber(true),
			Todoyu::Label('project.task.attr.status')				=> $task->getStatusLabel(),
			Todoyu::Label('project.task.attr.activity')				=> $task->getActivityLabel(),
			Todoyu::Label('project.task.attr.estimated_workload')	=> TodoyuTime::formatTime($task->getEstimatedWorkload()),
			Todoyu::Label('project.task.attr.is_acknowledged')		=> $task->isAcknowledged() ? '' : Todoyu::Label('LLL:project.task.attr.notAcknowledged')
		);

		$publicKey		= $task->isPublic() ? 'public' : 'private';
		$publicTypeKey	= $task->isContainer() ? '.container' : '';
		$exportData[Todoyu::Label('project.task.attr.is_public')]			= Todoyu::Label('project.task.attr.is_public.' . $publicKey . $publicTypeKey);

		$exportData	= TodoyuHookManager::callHookDataModifier('project', 'taskCSVExportParseData', $exportData, array('task'	=> $task));

		return $exportData;
	}
}

?>