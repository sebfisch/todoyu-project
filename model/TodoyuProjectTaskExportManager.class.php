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
	 * @static
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
	 * @static
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
	 * @static
	 * @param	TodoyuProjectTask	$task
	 * @return	Array
	 */
	protected static function parseDataForExport(TodoyuProjectTask $task) {
		$exportData = array(
			TodoyuLanguage::getLabel('LLL:project.task.attr.id')					=> $task->id,
			TodoyuLanguage::getLabel('LLL:project.task.attr.date_create')			=> TodoyuTime::format($task->date_create, 'date'),
			TodoyuLanguage::getLabel('LLL:core.global.date_update')					=> TodoyuTime::format($task->date_update, 'date'),
			TodoyuLanguage::getLabel('LLL:core.global.id_person_create')			=> TodoyuContactPersonManager::getPerson($task->id_person_create)->getFullName(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.type')					=> $task->isContainer() ? Label('project.task.container') : Label('project.task.ext.task'),
			TodoyuLanguage::getLabel('LLL:project.ext.project')						=> TodoyuProjectProjectManager::getProject($task->id_project)->getFullTitle(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.id_parenttask')			=> TodoyuProjectTaskManager::getTask($task->id_parenttask)->getFullTitle(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.title')					=> $task->getFullTitle(),
			TodoyuLanguage::getLabel('LLL:project.task.description')				=> TodoyuString::strictHtml2text($task->description),
			TodoyuLanguage::getLabel('LLL:project.task.attr.person_assigned')		=> TodoyuContactPersonManager::getPerson($task->id_person_assigned)->getFullName(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.person_owner')			=> TodoyuContactPersonManager::getPerson($task->id_person_owner)->getFullName(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.date_deadline')			=> TodoyuTime::format($task->date_deadline),
			TodoyuLanguage::getLabel('LLL:project.task.attr.date_start')			=> TodoyuTime::format($task->date_start),
			TodoyuLanguage::getLabel('LLL:project.task.attr.date_end')				=> TodoyuTime::format($task->date_end),
			TodoyuLanguage::getLabel('LLL:project.task.taskno')						=> $task->tasknumber,
			TodoyuLanguage::getLabel('LLL:project.task.attr.status')				=> $task->getStatusLabel(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.activity')				=> $task->getActivityLabel(),
			TodoyuLanguage::getLabel('LLL:project.task.attr.estimated_workload')	=> TodoyuTime::formatTime($task->getEstimatedWorkload()),
			TodoyuLanguage::getLabel('LLL:project.task.attr.is_acknowledged')		=> $task->isAcknowledged() ? '' : Label('LLL:project.task.attr.notAcknowledged')
		);

		$publicKey	= $task->isPublic() ? 'public' : 'private';
		$publicTypeKey	= $task->isContainer ? '.container' : '';
		$exportData[Label('LLL:project.task.attr.is_public')]			= Label('LLL:project.task.attr.is_public.' . $publicKey . $publicTypeKey);

		$exportData	= TodoyuHookManager::callHookDataModifier('project', 'taskCSVExportParseData', $exportData, array('task'	=> $task));

		return $exportData;
	}
}

?>