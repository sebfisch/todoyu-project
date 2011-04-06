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
 * Task action controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskActionController extends TodoyuActionController {

	/**
	 * Initialize controller
	 * Check whether project extension allowed
	 *
	 * @param	Array		$params
	 */
	public function init(array $params) {
		restrict('project', 'general:use');
	}



	/**
	 * Add a new task directly to the project root
	 *
	 * @param	Array		$params
	 * @return	String		Empty task form
	 */
	public function addprojecttaskAction(array $params) {
		$idProject 	= intval($params['project']);

		TodoyuProjectTaskRights::restrictAddToProject($idProject);

			// Send task id for JS
		TodoyuHeader::sendTodoyuHeader('idTask', 0);

			// Send task with form in details part
		return TodoyuProjectProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_TASK);
	}



	/**
	 * Add a new container directly to the project
	 *
	 * @param	Array		$params
	 * @return	String		Container edit form
	 */
	public function addprojectcontainerAction(array $params) {
		$idProject 	= intval($params['project']);

		TodoyuProjectTaskRights::restrictAddToProject($idProject);

			// Send task id for JS
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_CONTAINER);
	}



	/**
	 * Add a sub task to a task
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function addsubtaskAction(array $params) {
			// Parent for the new sub task
		$idParentTask	= intval($params['task']);

		TodoyuProjectTaskRights::restrictAdd($idParentTask);

			// Send task id for JS
		TodoyuHeader::sendTodoyuHeader('idTask', 0);

			// Send task with form in details part
		return TodoyuProjectProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_TASK);
	}



	/**
	 * Add new sub container
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function addsubcontainerAction(array $params) {
			// Parent for the new sub task
		$idParentTask	= intval($params['task']);

		TodoyuProjectTaskRights::restrictAdd($idParentTask);

			// Send task id for JS
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_CONTAINER);
	}



	/**
	 * Open task/container for editing
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuProjectTaskRights::restrictEdit($idTask);

		return TodoyuProjectTaskRenderer::renderTaskEditForm($idTask);
	}



	/**
	 * Save task
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		$data			= $params['task'];

		$idTask			= intval($data['id']);
		$idParentTask	= intval($data['id_parenttask']);
		$idProject		= intval($data['id_project']);

			// Check rights
		if( $idTask === 0 ) {
			TodoyuProjectTaskRights::restrictAddToProject($idProject);
		} else {
			TodoyuProjectTaskRights::restrictEdit($idTask);
		}

			// Create temp record in cache to keep all data. Necessary to save contains valid
		$task = new TodoyuProjectTask(0);
		$task->injectData($data);
		$cacheKey	= TodoyuRecordManager::makeClassKey('TodoyuProjectTask', 0);
		TodoyuCache::set($cacheKey, $task);

			// Initialize form for validation
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idTask);

		$form->setFormData($data);

			// Check if form is valid
		if( $form->isValid() ) {
				// Set parent task open status
			if( $idParentTask !== 0 ) {
				TodoyuProjectPreferences::saveSubTasksVisibility($idParentTask, true);
			}

				// If form is valid, get form storage data and update task
			$storageData= $form->getStorageData();

				// Save task
			$idTaskNew	= TodoyuProjectTaskManager::saveTask($storageData);

				// Set parent task expanded (to make sure saved task is visible)
			TodoyuProjectPreferences::saveTaskExpandedStatus($idParentTask, true);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);
			TodoyuHeader::sendTodoyuHeader('idTaskOld', $idTask);

			if( AREA === EXTID_PROJECT ) {
				return TodoyuProjectProjectRenderer::renderTask($idTaskNew);
			} else {
				return TodoyuProjectTaskRenderer::renderListingTask($idTaskNew);
			}
		} else {
			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}



	/**
	 * Update task status
	 *
	 * @param	Array	$params
	 */
	public function setstatusAction(array $params) {
		$idTask		= intval($params['task']);
		$idStatus	= intval($params['status']);
		$status		= TodoyuProjectTaskStatusManager::getStatusKey($idStatus);

		TodoyuProjectTaskRights::restrictStatusChangeTo($status, $idTask);

		TodoyuProjectTaskManager::updateTaskStatus($idTask, $idStatus);

			// If new status is not visible for the user, send info header
		if( ! allowed('project', 'seetask:' . $status . ':see') ) {
			TodoyuHeader::sendTodoyuHeader('statusNotAllowed', 1);
		}
	}



	/**
	 * Get task content
	 * Render a full task for refresh
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function getAction(array $params) {
		$idTask	= intval($params['task']);

		TodoyuProjectTaskRights::restrictSee($idTask);

		if( TodoyuProjectTaskManager::isTaskVisible($idTask) ) {
			if( AREA === EXTID_PROJECT ) {
				return TodoyuProjectProjectRenderer::renderTask($idTask, 0, true);
			} else {
				return TodoyuProjectTaskRenderer::renderListingTask($idTask);
			}
		} else {
			Todoyu::log('Tried to get task data of a not visible task', TodoyuLogger::LEVEL_SECURITY, $idTask);
		}
	}



	/**
	 * Get task/container detail content
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function detailAction(array $params) {
		$idTask	= intval($params['task']);
		$tab	= trim($params['tab']);

		TodoyuProjectTaskRights::restrictSee($idTask);

			// Save task open
		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, true);

		if( $tab !== '' ) {
			TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tab);
		}

			// Set task acknowledged
		TodoyuProjectTaskManager::setTaskAcknowledged($idTask);

		return TodoyuProjectTaskRenderer::renderTaskDetail($idTask, $tab);
	}



	/**
	 * Copy a task (and sub tasks)
	 * Add to clipboard, the copy action happens when pasting
	 *
	 * @param	Array		$params
	 */
	public function copyAction(array $params) {
		$idTask			= intval($params['task']);
		$withSubtasks	= intval($params['subtasks']) === 1;

		TodoyuProjectTaskRights::restrictSee($idTask);

		TodoyuProjectTaskClipboard::addTaskForCopy($idTask, $withSubtasks);
	}



	/**
	 * Cut a task and sub tasks
	 * Add to clipboard, the copy action happens when pasting
	 *
	 * @param	Array		$params
	 */
	public function cutAction(array $params) {
		$idTask			= intval($params['task']);

		TodoyuProjectTaskRights::restrictEdit($idTask);

		TodoyuProjectTaskClipboard::addTaskForCut($idTask);
	}



	/**
	 * Paste a copied or cut task
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function pasteAction(array $params) {
		$idTask	= intval($params['task']);
		$mode	= $params['mode'];

		TodoyuProjectTaskRights::restrictAdd($idTask);

		$idTaskNew = TodoyuProjectTaskClipboard::pasteTask($idTask, $mode);

		TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);

		return TodoyuProjectProjectRenderer::renderTask($idTaskNew);
	}



	/**
	 * Clone a task and add it right behind the clone source
	 *
	 * @param	Array 		$params
	 * @return	String		Cloned task HTML
	 */
	public function cloneAction(array $params) {
		$idTask			= intval($params['task']);
		$withSubTasks	= intval($params['subtasks']) === 1;

		TodoyuProjectTaskRights::restrictSee($idTask);
		TodoyuProjectTaskRights::restrictAdd($idTask);

		$idTaskNew		= TodoyuProjectTaskManager::cloneTask($idTask, $withSubTasks);

		TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);

		return TodoyuProjectProjectRenderer::renderTask($idTaskNew, 0);
	}



	/**
	 * 'acknowledge' action method
	 *
	 * @param	Array	$params
	 */
	public function acknowledgeAction(array $params) {
		$idTask	= intval($params['task']);

		TodoyuProjectTaskRights::restrictSee($idTask);

		TodoyuProjectTaskManager::setTaskAcknowledged($idTask);
	}



	/**
	 * Delete a task
	 *
	 * @param	Array	$params
	 */
	public function deleteAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuProjectTaskRights::restrictDelete($idTask);

		TodoyuProjectTaskManager::deleteTask($idTask, true);
	}



	/**
	 * Load task tab
	 *
	 * @param	Array	$params
	 */
	public function tabloadAction(array $params) {
		$idTask		= intval($params['task']);
		$tab		= $params['tab'];

		TodoyuProjectTaskRights::restrictSee($idTask);
		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tab);

		return TodoyuProjectTaskRenderer::renderTabContent($idTask, $tab);
	}



	/**
	 * 'tabselected' action method
	 *
	 * @param	Array	$params
	 */
	public function tabselectedAction(array $params) {
		$idTask		= intval($params['idTask']);
		$tabKey		= $params['tab'];

		TodoyuProjectTaskRights::restrictSee($idTask);

		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tabKey);
	}



	/**
	 * Render sub tasks
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function subtasksAction(array $params) {
		$idTask		= intval($params['task']);
		$idTaskShow	= intval($params['show']);

		TodoyuProjectTaskRights::restrictSee($idTask);

			// Save open status
		TodoyuProjectPreferences::saveSubTasksVisibility($idTask, true, AREA);

		return TodoyuProjectProjectRenderer::renderSubTasks($idTask, $idTaskShow);
	}



	/**
	 * Get task ID from task number
	 *
	 * @param	Array		$params
	 * @return	Integer
	 */
	public function number2idAction(array $params) {
		$taskNumber	= trim($params['tasknumber']);

		$idTask		= TodoyuProjectTaskManager::getTaskIDByTaskNumber($taskNumber);
		TodoyuProjectTaskRights::restrictSee($idTask);

		return $idTask;
	}

}

?>