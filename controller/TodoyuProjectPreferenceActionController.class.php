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
 * ActionController for project prefernces
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPreferenceActionController extends TodoyuActionController {

	public function detailsexpandedAction(array $params) {
		$idProject	= intval($params['item']);
		$expanded	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveExpandedDetails($idProject, $expanded);
	}



	/**
	 * Save task open/closed status
	 *
	 * @param	Array		$params
	 */
	public function taskopenAction(array $params) {
		$idTask		= intval($params['item']);
		$expanded	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, $expanded);
	}

	public function panelwidgetprojecttreeexpandAction(array $params) {
		$idItem	= intval($params['item']);
		$info	= explode(':', $params['value']);
		$type	= $info[0];
		$expand	= intval($info[1]) === 1;

		switch($type) {
			case 'project':
				PanelWidget_ProjectTree::saveProjectExpanded($idItem, AREA, $expand);
				break;
			case 'task':
				PanelWidget_ProjectTree::saveTaskExpanded($idItem, AREA, $expand);
				break;
		}
	}


	public function subtasksAction(array $params) {
		$idTask	= intval($params['item']);
		$isOpen	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveSubtasksVisibility($idTask, $isOpen, AREA);
	}


	public function panelwidgettaskstatusfilterAction(array $params) {
		$selectedStatuses	= TodoyuArray::intExplode(',', $params['value'], true, true);

		$widget	= TodoyuPanelWidgetManager::getPanelWidget('TaskStatusFilter');

		$widget->saveSelectedStatuses($selectedStatuses);
	}

	public function panelwidgetprojectstatusfilterAction(array $params) {
		$selectedStatuses	= TodoyuArray::intExplode(',', $params['value'], true, true);

		$widget	= TodoyuPanelWidgetManager::getPanelWidget('ProjectStatusFilter');

		$widget->saveSelectedStatuses($selectedStatuses);
	}



	/**
	 *	General panelWidget action, saves collapse status
	 *
	 *	@param	Array	$params
	 */
	public function pwidgetAction(array $params) {
		$idWidget	= $params['item'];
		$value		= $params['value'];

		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_PROJECT, $idWidget, $value);
	}
}

?>