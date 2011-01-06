<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 */
class TodoyuTaskExportManager {



	/**
	 * @static
	 * @param	Array	$taskIDs
	 */
	public static function exportCSV(array $taskIDs) {
		$taskIDs	= TodoyuArray::intval($taskIDs);
		
		$tasksToExport	= self::prepareDataForExport($taskIDs);

		$export		= new TodoyuExportCSV($tasksToExport);

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
			$task	= TodoyuTaskManager::getTask($idTask);
			
			$exportData[]	= self::parseDataForExport($task);
		}

		return $exportData;
	}



	/**
	 * @static
	 * @param	TodoyuTask	$task
	 * @return	Array
	 */
	protected static function parseDataForExport(TodoyuTask $task) {
		$exportData = array();

		$exportData['id[Label]']													= $task->id;
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.date_create')]			= TodoyuTime::format($task->date_create, 'date');
		$exportData['date_update[Label]']											= TodoyuTime::format($task->date_update, 'date');
		$exportData['id_person_create[Label]']										= TodoyuPersonManager::getPerson($task->id_person_create)->getFullName();
		$exportData['type[Label]']													= $task->isContainer() ? TodoyuLanguage::getLabel('LLL:task.container') : TodoyuLanguage::getLabel('LLL:task.task');
		$exportData[TodoyuLanguage::getLabel('LLL:project.project')]				= TodoyuProjectManager::getProject($task->id_project)->getFullTitle();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.id_parenttask')]		= TodoyuTaskManager::getTask($task->id_parenttask)->getFullTitle();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.title')]				= $task->getFullTitle();
		$exportData[TodoyuLanguage::getLabel('LLL:task.description')]				= TodoyuString::strictHtml2text($task->description);
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.person_assigned')]		= TodoyuPersonManager::getPerson($task->id_person_assigned)->getFullName();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.person_owner')]			= TodoyuPersonManager::getPerson($task->id_person_owner)->getFullName();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.date_deadline')]		= TodoyuTime::format($task->date_deadline);
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.date_start')]			= TodoyuTime::format($task->date_start);
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.date_end')]				= TodoyuTime::format($task->date_end);
		$exportData[TodoyuLanguage::getLabel('LLL:task.taskno')]					= $task->tasknumber;
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.status')]				= $task->getStatusLabel();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.worktype')]				= $task->getWorktypeLabel();
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.estimated_workload')]	= TodoyuTime::formatTime($task->getEstimatedWorkload());
		$exportData['is_acknowledged[Label]']										= $task->isAcknowledged() ? '' : TodoyuLanguage::getLabel('LLL:task.attr.notAcknowledged');

		$publicKey	= $task->isPublic() ? 'public' : 'private';
		$publicTypeKey	= $task->isContainer ? '.container' : '';
		$exportData[TodoyuLanguage::getLabel('LLL:task.attr.is_public')]			= TodoyuLanguage::getLabel('LLL:task.attr.is_public.' . $publicKey . $publicTypeKey);

		$exportData	= TodoyuHookManager::callHookDataModifier('project', 'onTaskCSVExportParseData', $exportData, array('task'	=> $task));
		
		return $exportData;
	}
}

?>