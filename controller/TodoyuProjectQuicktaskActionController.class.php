<?php

class TodoyuProjectQuicktaskActionController extends TodoyuActionController {

	/**
	 * @todo comment
	 *
	 *	@param	Array	$params
	 */
	public function popupAction(array $params) {
		return TodoyuQuickTaskManager::renderForm();
	}



	/**
	 * Save quick task
	 *
	 *	@param	Array	$params
	 *	@return	String
	 */
	public function saveAction(array $params) {
		$params['quicktask']['start_tracking'] = intval($params['quicktask']['start_tracking']);
		$formData	= $params['quicktask'];

			// Construct form object
		$xmlPath	= 'ext/project/config/form/quicktask.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Set form data
		$form->setFormData($formData);

			// Valdiate, save workload record / re-render form
		if( $form->isValid() )	{
			$storageData	= $form->getStorageData();

			$idTask		= TodoyuQuickTaskManager::save($storageData);
			$idProject	= intval($storageData['id_project']);
			$start		= intval($storageData['start_tracking']) === 1;

			TodoyuHeader::sendTodoyuHeader('idTask', $idTask);
			TodoyuHeader::sendTodoyuHeader('idProject', $idProject);
			TodoyuHeader::sendTodoyuHeader('start', $start);
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			return $form->render();
		}
	}

}

?>