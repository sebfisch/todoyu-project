<?php

class TodoyuProjectProjectformActionController extends TodoyuActionController {

	public function addSubformAction(array $params) {
		$formname	= $params['formname'];
		$fieldName	= $params['field'];
		$index		= intval($params['indexOfForeignRecord']);
		
		switch( $fieldName ) {
			case 'projectusers':
					// Render project users form
				$xmlPath= 'ext/project/config/form/project.xml';
				$form	= new TodoyuForm( $xmlPath );
				$form	= TodoyuFormHook::callBuildForm($xmlPath, $form, $index);

				// Load (/preset) form data
				$formData	= array();
				$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idSubform);

					// Set form data
				$form->setFormData($formData);

				$field	= $form->getField($fieldName);
				$form['name'] = $formname;

				return $field->addNewRecord($index);
				break;
		}
	}
		
}

?>