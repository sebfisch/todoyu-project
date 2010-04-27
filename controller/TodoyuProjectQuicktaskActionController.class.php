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
 * Quicktask controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectQuicktaskActionController extends TodoyuActionController {


	public function init(array $params) {
		restrict('project', 'general:use');
	}



	/**
	 * Get quicktask form rendered
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		return TodoyuQuickTaskManager::renderForm();
	}



	/**
	 * Save quick task
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function saveAction(array $params) {
		$params['quicktask']['start_tracking'] = intval($params['quicktask']['start_tracking']);
		$formData	= $params['quicktask'];

			// Get form object
		$form		= TodoyuQuickTaskManager::getQuickTaskForm();

			// Set form data
		$form->setFormData($formData);

			// Validate, save workload record / re-render form
		if( $form->isValid() )	{
			$storageData	= $form->getStorageData();

			$idTask		= TodoyuQuickTaskManager::save($storageData);
			$idProject	= intval($storageData['id_project']);

			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);

				// Call hook when quicktask is saved
			TodoyuHookManager::callHook('project', 'QuickTaskSaved', array($idTask, $idProject, $storageData));
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>