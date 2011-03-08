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
 * Panel widget: task status filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectPanelWidgetStatusFilterTask extends TodoyuProjectPanelWidgetStatusFilter {

	/**
	 * Initialize panel widget status filter
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {
			// Construct panelWidget (init basic configuration)
		parent::__construct(
			'project',									// ext key
			'taskstatusfilter',							// panel widget ID
			'LLL:project.panelwidget-statusfilter.title.task',	// widget title text
			$config,									// widget config array
			$params,									// widget parameters
			$idArea										// area ID
		);

			// Define preference
		$this->pref	= 'panelwidget-taskstatusfilter';

			// Initialize JavaScript
		TodoyuPage::addJsOnloadedFunction('function(){Todoyu.Ext.project.PanelWidget.TaskStatusFilterInstance = new Todoyu.Ext.project.PanelWidget.TaskStatusFilter()}', 100);
	}



	/**
	 * Get array of statuses (which the current person has the right to see) in listing of panel widget
	 *
	 * @return	Array
	 */
	protected function getStatusesInfos() {
		return TodoyuProjectTaskStatusManager::getStatusInfos('see');
	}



	/**
	 * Get currently selected statuses
	 *
	 * @return Array|String
	 */
	public function getSelectedStatuses() {
		$statusIDs	= TodoyuProjectPreferences::getPref($this->pref, 0, AREA);

		if( $statusIDs === false ) {
			$statusIDs = TodoyuProjectTaskStatusManager::getStatusIDs();
		} else {
			$statusIDs = TodoyuArray::intExplode(',', $statusIDs);
		}

		return $statusIDs;
	}



	/**
	 * Check general allowance of widget: access to project extension and seeing at least one task status allowed
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		$statuses	= TodoyuProjectTaskStatusManager::getStatuses();

		return allowed('project', 'general:use') && sizeof($statuses) > 1;
	}

}

?>