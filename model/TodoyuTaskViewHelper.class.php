<?php


class TodoyuTaskViewHelper {

	/**
	 * Get users to which a task can be assigned (this are only project users) as options array
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskAssignUserOptions(TodoyuFormElement $field) {
		$formData	= $field->getForm()->getFormData();
		$idProject	= intval($formData['id_project']);

		$users	= TodoyuProjectManager::getProjectUsers($idProject);
		$options= array(
			0	=> array(
				'disabled'	=> true,
				'label'		=> 'LLL:form.select.pleaseSelect',
				'value'		=> 0,
			)
		);

		foreach($users as $user) {
			$options[] = array(
				'label'	=> $user['lastname'] . ' ' . $user['firstname'] . ' [' . TodoyuLocale::getLabel($user['rolelabel']) . ']',
				'value'	=> $user['id']
			);
		}

		return $options;
	}



	/**
	 * Get stored worktypes as options array
	 *
	 * @param	TodoyuFormElement		$field
	 * @return	Array
	 */
	public static function getTaskWorktypeOptions(TodoyuFormElement $field) {
		$options= array(
			0	=> array(
				'disabled'	=> true,
				'label'		=> 'LLL:form.select.pleaseSelect',
				'value'		=> 0,
			)
		);

		$worktypes	= TodoyuWorktypeManager::getAllWorktypes();
		foreach($worktypes as $num => $type) {
			if ($type['deleted'] == 0) {
				$options[] = array(
					'label'	=> $type['title'],
					'value'	=> $type['id']
				);
			}
		}

		return $options;
	}
}

?>