<?php


class TodoyuTaskViewHelper {

	/**
	 * Get users to which a task can be assigned (this are only project users)
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskAssignUserOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idProject	= intval($formData['id_project']);

		$users	= TodoyuProjectManager::getProjectUsers($idProject);
		$options= array();

		foreach($users as $user) {
			$options[] = array(
				'label'	=> $user['lastname'] . ' ' . $user['firstname'] . ' [' . $user['rolelabel'] . ']',
				'value'	=> $user['id']
			);
		}

		return $options;
	}

}

?>