<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Action controller for projecttasktree
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectProjecttasktreeActionController extends TodoyuActionController {

	public function init(array $params) {
		restrict('project', 'general:area');
	}



	/**
	 * Add a project to the tasktree view
	 * (Doesn't create a 'new' project, just adds an existing one to the displayed tree)
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function addprojectAction(array $params) {
		$idProject	= intval($params['project']);
		$idTask		= intval($params['task']);

		TodoyuProjectRights::restrictSee($idProject);

			// Save currently active project
		TodoyuProjectPreferences::addOpenProject($idProject);

			// Send some information headers
		if( $idTask > 0 ) {
			TodoyuHeader::sendHashHeader('task-' . $idTask);
		}

		$project = TodoyuProjectManager::getProject($idProject);
		$tabLabel= TodoyuString::crop($project->getCompany()->getShortLabel() . ': ' . $project->getTitle(), 23, '..', false);

			// Send project id and tab label as header
		TodoyuHeader::sendTodoyuHeader('project', $idProject);
			// Send tab label JSON encoded to make sure, all characters are encoded properly
		TodoyuHeader::sendTodoyuHeader('tablabel', $tabLabel);

			// Render project details and tabtree in tab
		return TodoyuProjectRenderer::renderTabbedProject($idProject, $idTask);
	}



	/**
	 * Save currently open projects in tasktree
	 *
	 * @param	Array		$params
	 */
	public function openprojectsAction(array $params) {
		$openProjectIDs	= TodoyuArray::intExplode(',', $params['projects'], true, true);

		TodoyuProjectPreferences::saveOpenProjectTabs($openProjectIDs);
	}

}

?>