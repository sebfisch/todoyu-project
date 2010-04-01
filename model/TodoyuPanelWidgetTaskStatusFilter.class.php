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
 * Panel widget: task status filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuPanelWidgetTaskStatusFilter extends TodoyuPanelWidgetStatusFilter implements TodoyuPanelWidgetIf {

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
			'project',									// ext key
			'taskstatusfilter',							// panel widget ID
			'LLL:panelwidget-statusfilter.title.task',	// widget title text
			$config,									// widget config array
			$params,									// widget params
			$idArea										// area ID
		);

			// Define preference
		$this->pref	= 'panelwidget-taskstatusfilter';

			// Initialize javaScript
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.PanelWidget.TaskStatusFilter.init.bind(Todoyu.Ext.project.PanelWidget.TaskStatusFilter)', 100);
	}



	/**
	 * Get panelwidget statuses infos
	 *
	 * @return	Array
	 */
	protected function getStatusesInfos() {
		return TodoyuTaskStatusManager::getStatusInfos('see');
	}



	/**
	 * Check allowance
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		$statuses	= TodoyuTaskStatusManager::getStatuses();

		return allowed('project', 'general:use') && sizeof($statuses) > 1;
	}

}

?>