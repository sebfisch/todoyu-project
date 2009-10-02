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
 * Ext action controller
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuProjectExtActionController extends TodoyuActionController {

	/**
	 * Default action
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function defaultAction(array $params) {
		TodoyuFrontend::setActiveTab('project');

			// Get deeplink parameters
		$idProject	= intval($params['project']);
		$idTask		= intval($params['task']);

			// Find project if only the task is given as parameter
		if( $idTask !== 0 && $idProject === 0 ) {
			$idProject = TodoyuTaskManager::getProjectID($idTask);
		}

			// Get project if not set by parameter or save the given one in preferences
		if( $idProject === 0 ) {
			$idProject = TodoyuProjectManager::getCurrentProjectID();
		} else {
			TodoyuProjectPreferences::saveCurrentProject($idProject);
		}

			// Prepend current project to list
		TodoyuProjectPreferences::addNewOpenProjectTab($idProject);

		$project	= TodoyuProjectManager::getProject($idProject);

			// Init page
		TodoyuPage::init('ext/project/view/ext.tmpl');

		$title	= TodoyuLocale::getLabel('project.page.title') . ' - ' . $project->getTitle();
		TodoyuPage::setTitle($title);


		// Render panel widgets and content
		$panelWidgets		= TodoyuProjectRenderer::renderPanelWidgets($idProject, $idTask);
		$projectTaskTree	= TodoyuProjectRenderer::renderProjectView($idProject, $idTask);

		TodoyuPage::set('panelWidgets', $panelWidgets);
		TodoyuPage::set('taskTree', $projectTaskTree);

		TodoyuPage::addExtAssets('project', 'public');
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.ContextMenuTask.attach.bind(Todoyu.Ext.project.ContextMenuTask)');
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.ContextMenuProject.attach.bind(Todoyu.Ext.project.ContextMenuProject)');

		return TodoyuPage::render();
	}

}

?>