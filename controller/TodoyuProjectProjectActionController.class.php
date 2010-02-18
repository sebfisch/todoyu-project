<?php

class TodoyuProjectProjectActionController extends TodoyuActionController {

	/**
	 * 'add' action method
	 *
	 * @param	Array $params
	 * @return	String
	 */
	public function addAction(array $params) {
		$idProject	= intval($params['project']);

			// Send info headers for tab head
		TodoyuHeader::sendTodoyuHeader('idProject', $idProject);
		TodoyuHeader::sendTodoyuHeader('projectLabel', Label('LLL:project.newProject.tabLabel'));

		return TodoyuProjectRenderer::renderNewProjectEdit();

			// Send project html
		return TodoyuProjectRenderer::renderTabbedProject($idProject, 0);
	}



	/**
	 * 'addfirst' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function addfirstAction(array $params) {
		return TodoyuProjectRenderer::renderNewProjectEdit();
	}



	/**
	 * 'edit' action method
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function editAction(array $params) {
		$idProject	= intval($params['project']);

		return TodoyuProjectRenderer::renderProjectEditForm($idProject);
	}



	/**
	 * Save project (new or edit)
	 *
	 * @param	Array		$params
	 * @return	String		Form content if form is invalid
	 */
	public function saveAction(array $params) {
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
		restrict('project', 'project:details');

		$idProject	= intval($params['project']);

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
		restrict('project', 'project:edit');

		$xmlPath	= 'ext/project/config/form/project.xml';

		$formName	= $params['form'];
		$fieldName	= $params['field'];

		$index		= intval($params['index']);
		$idRecord	= intval($params['record']);

		return TodoyuFormManager::renderSubformRecord($xmlPath, $fieldName, $formName, $index, $idRecord);
	}

}

?>