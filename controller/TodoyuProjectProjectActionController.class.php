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
class TodoyuProjectProjectActionController extends TodoyuActionController {


	public function init(array $params) {
		restrict('project', 'general:use');
	}


	/**
	 * Edit project
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idProject	= intval($params['project']);

		TodoyuProjectRights::checkProjectEdit($idProject);

		return TodoyuProjectRenderer::renderProjectEditForm($idProject);
	}



	/**
	 * Save project (new or edit)
	 *
	 * @param	Array		$params
	 * @return	String		Form content if form is invalid
	 */
	public function saveAction(array $params) {
		TodoyuProjectRights::checkProjectEdit();

		$data		= $params['project'];
		$idProject	= intval($data['id']);

			// Construct form object
		$xmlPath	= 'ext/project/config/form/project.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idProject);

			// Set form data
		$form->setFormData($data);

		if( $form->isValid() ) {
			$storageData= $form->getStorageData();

				// Save project
			$idProjectNew	= TodoyuProjectManager::saveProject($storageData);

//			TodoyuProjectPreferences::saveExpandedDetails($idProjectNew, true);

			TodoyuHeader::sendTodoyuHeader('idProject', $idProjectNew);
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
		} else {
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}



	/**
	 * 'details' action method
	 *
	 * @param	Array	$params
	 * @return String
	 */
	public function detailsAction(array $params) {
		$idProject	= intval($params['project']);

		TodoyuProjectRights::checkProjectSee($idProject);

		return TodoyuProjectRenderer::renderProjectDetails($idProject);
	}



	/**
	 * Get company autocomplete
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function autocompleteCompanyAction(array $params) {
		$sword	= $params['sword'];
		$results = TodoyuPersonFilterDataSource::autocompleteCompanies($sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}



	/**
	 * 'autocmpletion' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function autocompletionAction(array $params) {
		$sword	= $params['sword'];
		$config	= array();

		$data	= TodoyuProjectFilterDataSource::autocompleteProjects($sword, $config);

		return TodoyuRenderer::renderAutocompleteList($data);
	}



	/**
	 * 'setstatus' action method
	 *
	 * @param	Array	$params
	 */
	public function setstatusAction(array $params) {
		TodoyuProjectRights::checkProjectEdit();

		$idProject	= intval($params['project']);
		$status		= intval($params['status']);

		TodoyuProjectManager::updateProjectStatus($idProject, $status);
	}



	/**
	 * 'remove' action method
	 *
	 * @param	Array	$params
	 */
	public function removeAction(array $params) {
		TodoyuProjectRights::checkProjectEdit();

		$idProject	= intval($params['project']);

		TodoyuProjectManager::deleteProject($idProject);
		TodoyuProjectPreferences::removeOpenProject($idProject);
	}



	/**
	 * @todo	comment
	 * @param	Array		$params
	 */
	public function noProjectViewAction(array $params) {
		return TodoyuProjectRenderer::renderNoProjectSelectedView();
	}



	/**
	 * Add a subform to the project form
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function addSubformAction(array $params) {
		TodoyuProjectRights::checkProjectEdit();

		$xmlPath	= 'ext/project/config/form/project.xml';

		$formName	= $params['form'];
		$fieldName	= $params['field'];

		$index		= intval($params['index']);
		$idRecord	= intval($params['record']);

		return TodoyuFormManager::renderSubformRecord($xmlPath, $fieldName, $formName, $index, $idRecord);
	}

}

?>