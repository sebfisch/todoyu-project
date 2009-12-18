<?php

class TodoyuProjectProjectformActionController extends TodoyuActionController {

	public function addSubformAction(array $params) {
		$formName	= $params['form'];
		$fieldName	= $params['field'];
		$index		= intval($params['index']);
		$idRecord	= intval($params['record']);

		switch($fieldName) {

			case 'projectusers':
					// Render project users form
				$xmlPath= 'ext/project/config/form/project.xml';
				$form	= TodoyuFormManager::getForm($xmlPath, $index);

				// Load (/preset) form data
				$formData	= $form->getFormData();
				$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $index);

					// Set form data
				$form->setFormData($formData);
				$form->setRecordID($idRecord);
				$form->setName($formName);

				return $form->getField($fieldName)->renderNewRecord($index);
				break;
		}
	}

}

?>