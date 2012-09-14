<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2012, snowflake productions GmbH, Switzerland
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
 * Project selector panelwidget action controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPanelwidgetprojectselectorActionController extends TodoyuActionController {

	/**
	 * @param	Array	$params
	 */
	public function init(array $params) {
		Todoyu::restrict('project', 'panelwidgets:projectSelector');
	}



	/**
	 * Get list of matching projects and groups
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function listAction(array $params) {
		$search			= trim($params['search']);
		$selectorWidget	= TodoyuProjectManager::getPanelWidgetProjectSelector(AREAEXT);
		/**
		 * @var	TodoyuProjectPanelWidgetProjectSelector	$selectorWidget
		 */
		$selectorWidget->saveSearchText($search);

		return $selectorWidget->renderList();
	}



	/**
	 * Save selected projects preference
	 *
	 * @param	Array	$params
	 */
	public function saveAction(array $params) {
		$items	= TodoyuString::trimExplode(',', $params['selection'], true);

		$selectorWidget	= TodoyuProjectManager::getPanelWidgetProjectSelector(AREAEXT);
		/**
		 * @var	TodoyuProjectPanelWidgetProjectSelector	$selectorWidget
		 */
		$selectorWidget->saveSelection($items);

			// Send back current selection
		TodoyuHeader::sendTypeJSON();

		echo json_encode(array(
			'items'		=> $items,
			'projects'	=> $selectorWidget->getProjectIDsOfSelection()
		));
	}



	/**
	 * Save selected projects + groups as new "virtual" group (preference)
	 *
	 * @param	Array	$params
	 */
	public function saveGroupAction(array $params) {
			// Validate title to be unique
		$title	= TodoyuContactPanelWidgetStaffSelector::validateGroupTitle(trim($params['title']));

			// Store new "virtual" group preference
			// Group items: projects and groups, as type-prefixed IDs e.g. g1 g2 g3 p1 p2 p3...
		$groupItems	= $params['items'];
		/**	@var TodoyuProjectPanelWidgetProjectSelector $selectorWidget */
		$selectorWidget	= TodoyuProjectManager::getPanelWidgetProjectSelector(AREAEXT);
		$idPref	= $selectorWidget->saveVirtualGroup($title, $groupItems);

			// Add new item into selection and store selection
		$selection	= $selectorWidget->getSelection();
		$selection[]= 'v'.$idPref;
		$selectorWidget->saveSelection($selection);

		TodoyuHeader::sendTodoyuHeader('idPreference', $idPref);
	}



	/**
	 * Delete given "virtual" group (pref)
	 *
	 * @param	Array	$params
	 */
	public function deleteGroupAction(array $params) {
		$idPref = (int) $params['group'];

			// Only admin and pref-owner can delete a virtual group
		if( ! TodoyuAuth::isAdmin() ) {
			$prefRecord		= Todoyu::db()->getRecord(TodoyuPreferenceManager::TABLE, $idPref);
			$idPrefPerson	= (int) $prefRecord['id_person'];

			if( Todoyu::personid() !== $idPrefPerson ) {
					// Deletion failed because pref belongs to another person
				TodoyuHeader::sendTodoyuErrorHeader();
				return ;
			}
		}

		Todoyu::db()->deleteRecord(TodoyuPreferenceManager::TABLE, $idPref);
	}

}

?>