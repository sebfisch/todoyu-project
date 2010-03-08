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

			// Construct form object
		$xmlPath	= 'ext/project/config/form/quicktask.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Set form data
		$form->setFormData($formData);

			// Valdiate, save workload record / re-render form
		if( $form->isValid() )	{
			$storageData	= $form->getStorageData();

			$idTask		= TodoyuQuickTaskManager::save($storageData);
			$idProject	= intval($storageData['id_project']);
			$start		= intval($storageData['start_tracking']) === 1;

			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);
			TodoyuHeader::sendTodoyuHeader('start', $start);
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>