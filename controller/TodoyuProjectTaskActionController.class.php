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

	public function addprojectcontainerAction(array $params) {
		$idProject 	= intval($params['project']);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit(0, $idProject, TASK_TYPE_CONTAINER);
	}


	public function addsubtaskAction(array $params) {
			// Parent for the new subtask
		$idParentTask	= intval($params['task']);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idTask', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_TASK);
	}


	public function addsubcontainerAction(array $params) {
			// Parent for the new subtask
		$idParentTask	= intval($params['task']);

			// Send task id for js
		TodoyuHeader::sendTodoyuHeader('idContainer', 0);

			// Send task with form in details part
		return TodoyuProjectRenderer::renderNewTaskEdit($idParentTask, 0, TASK_TYPE_CONTAINER);
	}


	public function editAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, true);

		return TodoyuTaskRenderer::renderTaskEditForm($idTask);
	}



	/**
	 * Save task
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function saveAction(array $params) {
			// Get form data from request
		$data			= $params['task'];
		$idTask			= intval($data['id']);
		$idParentTask	= intval($data['id_parenttask']);

			// Create task object and override data with current form data
//		$task = TodoyuTaskManager::getTask($idTask);
//		$task->injectData($taskData);

			// Initialize form for validation
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= new TodoyuForm($xmlPath);

			// Call form hooks
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, $idTask);

		$form->setFormData($data);

			// Set parenttask open status
		if( $idParentTask !== 0 ) {
			TodoyuProjectPreferences::saveSubtasksVisibility($idParentTask, true);
		}

			// Check if form is valid
		if( $form->isValid() ) {
				// If form is valid, get form storage data and update task
			$storageData= $form->getStorageData();

			$idTaskReal	= TodoyuTaskManager::saveTask($storageData);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTaskReal);
			TodoyuHeader::sendTodoyuHeader('idTaskOld', $idTask);

			return TodoyuProjectRenderer::renderTask($idTaskReal, $idTaskReal);
		} else {
			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

	public function setstatusAction(array $params) {
		$idTask		= intval($params['task']);
		$idStatus	= intval($params['status']);

		TodoyuTaskManager::updateTaskStatus($idTask, $idStatus);
	}

	public function getAction(array $params) {
		$idTask	= intval($params['task']);

		if( TodoyuTaskManager::isTaskVisible($idTask) ) {
			return TodoyuProjectRenderer::renderTask($idTask, 0, true);
		}
	}

	public function detailAction(array $params) {
		$idTask		= intval($params['task']);

			// Save task open
		TodoyuPortalPreferences::saveTaskExpandedStatus($idTask, true);
		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, true);

			// Set task acknowledged
		TodoyuTaskManager::setTaskAcknowledged($idTask);

		return TodoyuTaskRenderer::renderTaskDetail($idTask);
	}

	public function cloneAction(array $params) {
		$idTask		= intval($params['task']);
		$idTaskNew	= TodoyuTaskManager::cloneTask($idTask);

		TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);

		return TodoyuProjectRenderer::renderTask($idTaskNew, 0, true);
	}

	public function cloneContainerAction(array $params) {
		$idContainer		= intval($params['container']);
		$cloneSubElements	= intval($params['cloneSubElements']) === 1;

		$idContainerNew		= TodoyuTaskManager::cloneContainer($idContainer, array(), $cloneSubElements);

		TodoyuHeader::sendTodoyuHeader('idContainer', $idContainerNew);

		return TodoyuProjectRenderer::renderTask($idContainerNew, 0, false);
	}

	public function acknowledgeAction(array $params) {
		$idTask	= intval($params['task']);

		TodoyuTaskManager::setTaskAcknowledged($idTask);
	}

	public function deleteAction(array $params) {
		$idTask		= intval($params['task']);

		TodoyuTaskManager::deleteTask($idTask, true);
	}

	public function autocompleteprojecttaskAction(array $params) {
		$formName	= $params['formName'];
		$sword		= $params['sword'];
		$formData	= $params[$formName];
		$idProject	= intval($formData['id_project']);
		$idTask		= intval($formData['id']);

		$filters	= array(
			array(
				'filter'=> 'tasknumberortitle',
				'value'	=> $sword
			),
			array(
				'filter'=> 'project',
				'value'	=> $idProject
			),
			array(
				'filter'=> 'nottask',
				'value'	=> $idTask
			)
		);

		$tasks	= TodoyuTaskFilterDataSource::getTaskAutocompleteListByFilter($filters);

		return TodoyuRenderer::renderAutocompleteList($tasks);
	}

	public function tabloadAction(array $params) {
		$idTask		= intval($params['task']);
		$tabKey		= $params['tab'];

		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tabKey, AREA);

		$tabConf	= TodoyuTaskManager::getTabConfig($tabKey);

		return TodoyuDiv::callUserFunction($tabConf['content'], $idTask);
	}

	public function tabselectedAction(array $params) {
		$idTask		= intval($params['task']);
		$tabKey		= $params['tab'];

		TodoyuProjectPreferences::saveActiveTaskTab($idTask, $tabKey, AREA);
	}

}

?>