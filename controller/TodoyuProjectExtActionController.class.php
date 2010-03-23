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

	public function init(array $params) {
		restrict('project', 'general:use');
	}



	/**
	 * Default action
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function defaultAction(array $params) {
		restrict('project', 'general:area');

			// Set project tab
		TodoyuFrontend::setActiveTab('project');

			// Get deeplink parameters
		$idProject	= intval($params['project']);
		$idTask		= intval($params['task']);
		$taskTab	= $params['tab'];

			// Find project if only the task is given as parameter
		if( $idTask !== 0 && $idProject === 0 ) {
			$idProject = TodoyuTaskManager::getProjectID($idTask);
		}

			// Get project if not set by parameter or save the given one in preferences
		if( $idProject === 0 ) {
			$idProject	= TodoyuProjectPreferences::getActiveProject();
		}

			// If no project found yet, try to find one the person can see
		if( $idProject === 0 ) {
			$idProject	= TodoyuProjectManager::getAvailableProjectForPerson();
		}

			// Check access rights (if project selected)
		if( $idProject !== 0 ) {
			TodoyuProjectRights::restrictSee($idProject);
		}


			// Init page
		TodoyuPage::init('ext/project/view/ext.tmpl');

			// If a project is displayed
		if( $idProject !== 0 ) {
				// Prepend current project to list
			TodoyuProjectPreferences::addOpenProject($idProject);

			$project= TodoyuProjectManager::getProject($idProject);
			$title	= TodoyuLanguage::getLabel('project.page.title') . ' - ' . $project->getFullTitle();
		} else {
			$title	= TodoyuLanguage::getLabel('project.page.title.noSelected');
		}

		TodoyuPage::setTitle($title);

			// Render panel widgets and content
		$panelWidgets		= TodoyuProjectRenderer::renderPanelWidgets($idProject, $idTask);
		$projectTabs		= TodoyuProjectRenderer::renderProjectsTabs();
		$projectTaskTree	= TodoyuProjectRenderer::renderProjectsContent($idProject, $idTask, $taskTab);

		TodoyuPage::set('panelWidgets', $panelWidgets);
		TodoyuPage::set('projectTabs', $projectTabs);
		TodoyuPage::set('taskTree', $projectTaskTree);

			// Add JS onload functions
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.init.bind(Todoyu.Ext.project)', 100);
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.ContextMenuTask.attach.bind(Todoyu.Ext.project.ContextMenuTask)', 100);
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.ContextMenuProject.attach.bind(Todoyu.Ext.project.ContextMenuProject)', 100);

		return TodoyuPage::render();
	}



	/**
	 * Controller to handle direct edit access. Calls the default action first to render the whole site.
	 * After loading the site the js-edit method is called.
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function editAction(array $params)	{
		restrict('project', 'project:modify');

		$idProject = intval($params['project']);
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.Project.edit('.$idProject.')', 101);

		return $this->defaultAction($params);
	}



	/**
	 * Controller to handle direct add task access. Calls the default action first to render the whole site.
	 * After loading the site the js-addTask method is called.
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function addtaskAction(array $params)	{
		//restrict('project', 'project:addtask');

		$idProject = intval($params['project']);
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.Project.addTask('.$idProject.')', 101);

		return $this->defaultAction($params);
	}



	/**
	 * Controller to handle direct add container access. Calls the default action first to render the whole site
	 * After loading the site the js-addContainer method is called.
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function addcontainerAction(array $params)	{
		//restrict('project', 'project:addcontainer');

		$idProject = intval($params['project']);
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.Project.addContainer('.$idProject.')', 101);

		return $this->defaultAction($params);
	}

}

?>