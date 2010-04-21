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

		TodoyuTaskRights::restrictAddToProject($idProject);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idTask', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_TASK);
	}



	/**
	 * Add a new container directly to the project
	 *
	 * @param	Array		$params
	 * @return	String		Container edit form
	 */
	public function addprojectcontainerAction(array $params) {
		$idProject 	= intval($params['project']);

		TodoyuTaskRights::restrictAddToProject($idProject);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_CONTAINER);
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

		TodoyuTaskRights::restrictAdd($idParentTask);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idTask', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_TASK);
	}



	/**
	 * 'addsubcontainer' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function addsubcontainerAction(array $params) {
			// Parent for the new sub task
		$idParentTask	= intval($params['task']);

		TodoyuTaskRights::restrictAdd($idParentTask);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_CONTAINER);
	}



	/**
	 * 'edit' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuTaskRights::restrictEdit($idTask);

		return TodoyuTaskRenderer::renderTaskEditForm($idTask);
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
			TodoyuTaskRights::restrictAddToProject($idProject);
		} else {
			TodoyuTaskRights::restrictEditInProject($idProject);
		}

			// Create a cache record for the buildform hooks
		$task = new TodoyuTask(0);
		$task->injectData($data);
		$cacheKey	= TodoyuRecordManager::makeClassKey('TodoyuTask', 0);
		TodoyuCache::set($cacheKey, $task);

			// Initialize form for validation
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idTask);

		$form->setFormData($data);

			// Check if form is valid
		if( $form->isValid() ) {
				// Set parenttask open status
			if( $idParentTask !== 0 ) {
				TodoyuProjectPreferences::saveSubTasksVisibility($idParentTask, true);
			}

				// If form is valid, get form storage data and update task
			$storageData= $form->getStorageData();

				// Save task
			$idTaskNew	= TodoyuTaskManager::saveTask($storageData);

				// Save task as open (and its parent)
//			TodoyuProjectPreferences::saveTaskExpandedStatus($idTaskNew, true);
			TodoyuProjectPreferences::saveTaskExpandedStatus($idParentTask, true);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);
			TodoyuHeader::sendTodoyuHeader('idTaskOld', $idTask);

//			return TodoyuProjectRenderer::renderTask($idTaskNew, $idTaskReal);
		
			return $params['area'] == 'portal' ? TodoyuTaskRenderer::renderListingTask($idTaskNew) : TodoyuProjectRenderer::renderTask($idTaskNew, 0);
		} else {
			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}



	/**
	 * 'setstatus' action method
	 *
	 * @param	Array	$params
	 */
	public function setstatusAction(array $params) {
		$idTask		= intval($params['task']);
		$idStatus	= intval($params['status']);
		$status		= TodoyuTaskStatusManager::getStatusKey($idStatus);

		restrict('project', 'taskstatus:' . $status . ':changeto');

		TodoyuTaskManager::updateTaskStatus($idTask, $idStatus);
	}



	/**
	 * Get task content
	 * Render a full task to for refresh
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function getAction(array $params) {
		$idTask	= intval($params['task']);

		TodoyuTaskRights::restrictSee($idTask);

		if( TodoyuTaskManager::isTaskVisible($idTask) ) {
			if( AREA === EXTID_PROJECT ) {
				return TodoyuProjectRenderer::renderTask($idTask, 0, true);
			} else {
				return TodoyuTaskRenderer::renderListingTask($idTask);
			}
		} else {
			Todoyu::log('Tried to get task data of a not visible task', LOG_LEVEL_SECURITY, $idTask);
		}
	}



	/**
	 * Get task detail content
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function detailAction(array $params) {
		$idTask	= intval($params['task']);
		$tab	= trim($params['tab']);

		TodoyuTaskRights::restrictSee($idTask);


			// Save task open
		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, true);

		if( $tab !== '' ) {
			TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tab);
		}

			// Set task acknowledged
		TodoyuTaskManager::setTaskAcknowledged($idTask);

		return TodoyuTaskRenderer::renderTaskDetail($idTask, $tab);
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

		TodoyuTaskRights::restrictSee($idTask);

		TodoyuTaskClipboard::addTaskCopy($idTask, $withSubtasks);
	}



	/**
	 * Cut a task and sub tasks
	 * Add to clipboard, the copy action happens when pasting
	 *
	 * @param	Array		$params
	 */
	public function cutAction(array $params) {
		$idTask			= intval($params['task']);

		TodoyuTaskRights::restrictEdit($idTask);

		TodoyuTaskClipboard::addTaskCut($idTask);
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

		TodoyuTaskRights::restrictAdd($idTask);

		$idTaskNew = TodoyuTaskClipboard::pasteTask($idTask, $mode);

		TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);

		return TodoyuProjectRenderer::renderTask($idTaskNew);
	}



	/**
	 * Clone a task and add it right behind the clone source
	 *
	 * @param	Array 		$params
	 * @return	String		Cloned task html
	 */
	public function cloneAction(array $params) {
		$idTask			= intval($params['task']);
		$withSubtasks	= intval($params['subtasks']) === 1;

		TodoyuTaskRights::restrictSee($idTask);
		TodoyuTaskRights::restrictAdd($idTask);

		$idTaskNew		= TodoyuTaskManager::cloneTask($idTask, $withSubtasks);

		TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);

		return TodoyuProjectRenderer::renderTask($idTaskNew, 0);
	}



	/**
	 * 'acknowledge' action method
	 *
	 * @param	Array	$params
	 */
	public function acknowledgeAction(array $params) {
		$idTask	= intval($params['task']);

		TodoyuTaskManager::setTaskAcknowledged($idTask);
	}



	/**
	 * 'delete' action method
	 *
	 * @param	Array	$params
	 */
	public function deleteAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuTaskRights::restrictEdit($idTask);

		TodoyuTaskManager::deleteTask($idTask, true);
	}

	

	/**
	 * Load task tab
	 *
	 * @param	Array	$params
	 */
	public function tabloadAction(array $params) {
		$idTask		= intval($params['task']);
		$tab		= $params['tab'];

		TodoyuTaskRights::restrictSee($idTask);
		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tab);

		return TodoyuTaskRenderer::renderTabContent($idTask, $tab);
	}



	/**
	 * 'tabselected' action method
	 *
	 * @param	Array	$params
	 */
	public function tabselectedAction(array $params) {
		$idTask		= intval($params['task']);
		$tabKey		= $params['tab'];

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

			// Save open status
		TodoyuProjectPreferences::saveSubTasksVisibility($idTask, true, AREA);

		return TodoyuProjectRenderer::renderSubTasks($idTask, $idTaskShow);
	}

}

?>