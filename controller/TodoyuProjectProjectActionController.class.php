<?php

class TodoyuProjectProjectActionController extends TodoyuActionController {

	public function addAction(array $params) {
		$idProject	= intval($params['project']);

			// Send info headers for tab head
		TodoyuHeader::sendTodoyuHeader('idProject', $idProject);
		TodoyuHeader::sendTodoyuHeader('projectLabel', Label('LLL:project.newProject.tabLabel'));

		return TodoyuProjectRenderer::renderNewProjectEdit();

			// Send project html
		return TodoyuProjectRenderer::renderTabbedProject($idProject, 0);
	}


	public function addfirstAction(array $params) {
		return TodoyuProjectRenderer::renderNewProjectEdit();
	}


	public function editAction(array $params) {
		$idProject	= intval($params['project']);

		TodoyuProjectPreferences::saveExpandedDetails($idProject, true);

		return TodoyuProjectRenderer::renderProjectEditForm($idProject);
	}


	/**
	 * Save project (new or edit)
	 *
	 * @param	Array		$params
	 * @return	String		Form content if form is invalid
	 */
	public function saveAction(array $params) {
		$project	= $params['project'];
		$idProject	= intval($project['id']);

			// Construct form object
		$xmlPath	= 'ext/project/config/form/project.xml';
		$form		= new TodoyuForm($xmlPath);
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, $idProject);

			// Set form data
		$form->setFormData($project);

		if( $form->isValid() ) {
			$storageData= $form->getStorageData();
				// Save project
			$idProjectNew	= TodoyuProjectManager::saveProject($storageData);

			TodoyuProjectPreferences::saveExpandedDetails($idProjectNew, true);

			TodoyuHeader::sendTodoyuHeader('idProject', $idProjectNew);
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
		} else {
			TodoyuHeader::sendTodoyuHeader('idProjectOld', $idProject);
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}


	public function detailsAction(array $params) {
		$idProject	= intval($params['project']);

		return TodoyuProjectRenderer::renderProjectDetails($idProject);
	}


	public function autocompleteCustomerAction(array $params) {
		$sword	= $params['sword'];
		$results = TodoyuUserFilterDataSource::autocompleteCustomers($sword);

		return TodoyuRenderer::renderAutocompleteList($results);
	}


	public function autocompletionAction(array $params) {
		$sword	= $params['sword'];
		$config	= array();

		$data	= TodoyuProjectFilterDataSource::autocompleteProjects($sword, $config);

		return TodoyuRenderer::renderAutocompleteList($data);
	}


	public function setstatusAction(array $params) {
		$idProject	= intval($params['project']);
		$status		= intval($params['status']);

		TodoyuProjectManager::updateProjectStatus($idProject, $status);
	}

}


?>