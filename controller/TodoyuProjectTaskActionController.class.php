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
 * Task action controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskActionController extends TodoyuActionController {

	/**
	 * Add a new task directly to the project root
	 *
	 * @param	Array		$params
	 * @return	String		Empty task form
	 */
	public function addprojecttaskAction(array $params) {
		$idProject 	= intval($params['project']);

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

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_CONTAINER);
	}



	/**
	 * Add a subtask to a task
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function addsubtaskAction(array $params) {
			// Parent for the new subtask
		$idParentTask	= intval($params['task']);

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
			// Parent for the new subtask
		$idParentTask	= intval($params['task']);

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

//		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, true);

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

			// Create a cache record for the buildform hooks
		$task = new TodoyuTask(0);
		$task->injectData($data);
		TodoyuCache::addRecord($task);

			// Initialize form for validation
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idTask);

		$form->setFormData($data);

			// Check if form is valid
		if( $form->isValid() ) {
				// Set parenttask open status
			if( $idParentTask !== 0 ) {
				TodoyuProjectPreferences::saveSubtasksVisibility($idParentTask, true);
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
			return TodoyuProjectRenderer::renderTask($idTaskNew, 0);
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
		$status		= TodoyuProjectStatusManager::getTaskStatusKey($idStatus);

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

		if( TodoyuTaskManager::isTaskVisible($idTask) ) {
			return TodoyuProjectRenderer::renderTask($idTask, 0, true);
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
	 * Copy a task (and subtasks)
	 * Add to clipboard, the copy action happens when pasting
	 *
	 * @param	Array		$params
	 */
	public function copyAction(array $params) {
		$idTask			= intval($params['task']);
		$withSubtasks	= intval($params['subtasks']) === 1;

		TodoyuTaskClipboard::addTaskCopy($idTask, $withSubtasks);
	}



	/**
	 * Cut a task and subtasks
	 * Add to clipboard, the copy action happens when pasting
	 *
	 * @param	Array		$params
	 */
	public function cutAction(array $params) {
		$idTask			= intval($params['task']);

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

		TodoyuTaskManager::deleteTask($idTask, true);
	}



	/**
	 * 'autocompleteprojecttask' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function autocompleteprojecttaskAction(array $params) {
		$formName	= $params['formName'];
		$sword		= trim($params['sword']);
		$formData	= $params[$formName];
		$idProject	= intval($formData['id_project']);
		$idTask		= intval($formData['id']);

		$filters	= array(
			array(
				'filter'=> 'tasknumberortitle',
				'value'	=> $sword
			),
			array(
				'filter'=> 'nottask',
				'value'	=> $idTask
			),
			array(
				'filter'=> 'project',
				'value'	=> $idProject
			)
		);

		$tasks	= TodoyuTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);

		return TodoyuRenderer::renderAutocompleteList($tasks);
	}



	/**
	 * 'tabload' action method
	 *
	 * @param	Array	$params
	 */
	public function tabloadAction(array $params) {
		$idTask		= intval($params['task']);
		$tabKey		= $params['tab'];

		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tabKey, AREA);

		$tabConf	= TodoyuTaskManager::getTabConfig($tabKey);

		return TodoyuDiv::callUserFunction($tabConf['content'], $idTask);
	}



	/**
	 * 'tabselected' action method
	 *
	 * @param	Array	$params
	 */
	public function tabselectedAction(array $params) {
		$idTask		= intval($params['task']);
		$tabKey		= $params['tab'];

		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tabKey, AREA);
	}

}

?>