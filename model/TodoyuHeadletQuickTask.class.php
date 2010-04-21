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
 * Quicktask headlet
 * Shows AJAX loading icon if a request is active
 *
 * @package		Todoyu
 * @subpackage	Core
 */
class TodoyuHeadletQuickTask extends TodoyuHeadletTypeButton {

	/**
	 * Initialize headlets
	 */
	protected function init() {
			// Set javaScript object which handles events
		$this->setJsHeadlet('Todoyu.Ext.project.Headlet.QuickTask');
	}



	/**
	 * Get headlet label
	 *
	 * @return	String
	 */
	public function getLabel() {
		return Label('headlet-quicktask.label');
	}



	/**
	 * Check whether there are project where the user can add the task to
	 * If no projects available, hide widget
	 *
	 * @return Boolean
	 */
	public function isEmpty() {
		$projectIDs	= TodoyuProjectManager::getProjectIDsForTaskAdd();
		
		return sizeof($projectIDs) === 0;
	}

}

?>