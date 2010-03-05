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
 * Quickcreate task controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectQuickCreateTaskActionController extends TodoyuActionController {

	/**
	 * Render form
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		return TodoyuTaskRenderer::renderQuickCreateForm();
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
		$idProject		= intval($data['id_project']);

			// Create a cache record for the buildform hooks
		$task = new TodoyuTask(0);
		$task->injectData($data);
		TodoyuCache::addRecord($task);

			// Get form object, call save hooks, set form data
		$form	= TodoyuTaskManager::getQuickCreateForm();
		$data	= TodoyuFormHook::callSaveData('ext/project/config/form/task.xml', $data, 0);
		$form->setFormData($data);

			// Check if form is valid
		if( $form->isValid() ) {
				// If form is valid, get form storage data and update task
			$storageData= $form->getStorageData();

				// Save task
			$idTaskNew	= TodoyuTaskManager::saveTask($storageData);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTaskNew);
			TodoyuHeader::sendTodoyuHeader('idTaskOld', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);

		} else {
				// Form data not valid
			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>