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
 * Quickcreate project controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectQuickCreateProjectActionController extends TodoyuActionController {

	public function init(array $params) {
		TodoyuProjectRights::restrictAdd();
	}



	/**
	 * Render project form
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		return TodoyuProjectRenderer::renderQuickCreateForm($params);
	}



	/**
	 * Save project (new or edit)
	 *
	 * @param	Array		$params
	 * @return	String		Form content if form is invalid
	 */
	public function saveAction(array $params) {
		$data		= $params['project'];
		$idProject	= intval($data['id']);

			// Get form object, call save hooks, set form data
		$form	= TodoyuProjectManager::getQuickCreateForm();
		$data	= TodoyuFormHook::callSaveData('ext/project/config/form/project.xml', $data, 0);
		$form->setFormData($data);

		if( $form->isValid() ) {
			$storageData= $form->getStorageData();

				// Save project
			$idProjectNew	= TodoyuProjectManager::saveProject($storageData);

			TodoyuHeader::sendTodoyuHeader('idProject', $idProjectNew);
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
		} else {
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>