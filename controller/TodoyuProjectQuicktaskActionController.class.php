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
 * Quicktask controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectQuicktaskActionController extends TodoyuActionController {

	/**
	 * Initialize controller: restrict access (project extension must be allowed)
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		Todoyu::restrict('project', 'general:use');
	}



	/**
	 * Get quicktask form rendered
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		$idProject	= intval($params['project']);

		TodoyuProjectTaskRights::restrictAddToProject($idProject);

		return TodoyuProjectQuickTaskManager::renderForm($idProject);
	}



	/**
	 * Save quick task
	 *
	 * @param	Array		$params
	 * @return	Void|String				Failure returns re-rendered form with error messages
	 */
	public function saveAction(array $params) {
		$params['quicktask']['start_tracking']	= intval($params['quicktask']['start_tracking']);
		$formData	= $params['quicktask'];
		$idProject	= intval($formData['id_project']);

		TodoyuProjectTaskRights::restrictAddToProject($idProject);

			// Get form object
		$form	= TodoyuProjectQuickTaskManager::getQuickTaskForm();

			// Set form data
		$form->setFormData($formData);

			// Validate, save workload record / re-render form
		if( $form->isValid() ) {
			$storageData	= $form->getStorageData();
			$idTask			= TodoyuProjectQuickTaskManager::save($storageData);

			$idProject	= intval($storageData['id_project']);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);

				// Call hook when quicktask is saved
			TodoyuHookManager::callHook('project', 'quicktask.saved', array($idTask, $idProject, $storageData));
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>