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
 * Panel widget: status filter base
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuPanelWidgetProjectStatusFilter extends TodoyuPanelWidgetStatusFilter implements TodoyuPanelWidgetIf {

	/**
	 * Initialize panel widget status filter
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

			// construct PanelWidget (init basic configuration)
		parent::__construct(
			'project',								// ext key
			'projectstatusfilter',					// panel widget ID
			'LLL:panelwidget-statusfilter.title.project',	// widget title text
			$config,								// widget config array
			$params,								// widget params
			$idArea									// area ID
		);

			// Define preference
		$this->pref	= 'panelwidget-projectstatusfilter';

			// Add assets
		TodoyuPage::addExtAssets('project', 'panelwidget-taskstatusfilter');

			// Get selected status IDs
		$filterJSON			= json_encode($this->getSelectedStatusIDs());

			// Initialize javascript
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.PanelWidget.ProjectStatusFilter.init.bind(Todoyu.Ext.project.PanelWidget.ProjectStatusFilter, ' . $filterJSON . ')');
	}



	/**
	 * Get panelwidget statuses infos
	 *
	 * @return	Array
	 */
	protected function getStatusesInfos() {
		return TodoyuProjectStatusManager::getProjectStatusInfos('see');
	}



	/**
	 * Check allowance
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return allowed('project', 'panelwidgets:projectStatusFilter');
	}

}

?>