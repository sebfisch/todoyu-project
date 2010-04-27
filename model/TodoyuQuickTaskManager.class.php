<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/


/**
 * Manage quicktasks
 *
 * @package		Todoyu
 * @subpackage	QuickTask
 */
class TodoyuQuickTaskManager {

	/**
	 * Render quicktask form
	 *
	 * @return	String
	 */
	public static function renderForm()	{
		$form	= self::getQuickTaskForm();

		return $form->render();
	}



	/**
	 * Get quicktask form which is customized for current user
	 *
	 * @return	TodoyuForm
	 */
	public static function getQuickTaskForm() {
			// Construct form object
		$xmlPath	= 'ext/project/config/form/quicktask.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Load form with extra field data
		$xmlPathInsert	= 'ext/project/config/form/field-id_project.xml';
		$insertForm		= TodoyuFormManager::getForm($xmlPathInsert);


			// If person can add tasks in all project, show autocomplete field, else only a select element
		if( allowed('project', 'task:addInAllProjects') ) {
			$field	= $insertForm->getField('id_project_ac');
		} else {
			$field	= $insertForm->getField('id_project_select');
		}

			// Remove normal project field
		$form->removeField('id_project', true);

			// Add custom project field
		$form->getFieldset('main')->addField('id_project', $field, 'after:title');

			// Load form data by hooks (default is empty)
		$formData	= TodoyuFormHook::callLoadData($xmlPath, array());
		
		$form->setFormData($formData);

		return $form;
	}



	/**
	 * Adds mandatory task data to that received from quicktask form, saves new task to DB
	 *
	 * @param	Array	$formData
	 * @return	Integer
	 */
	public static function save(array $data) {
			// Add empty task to have a task ID to work with
		$firstData	= array(
			'id_project'	=> intval($data['id_project']),
			'id_parenttask'	=> 0
		);
		$idTask		= TodoyuTaskManager::addTask($firstData);

			// Prepare data to save task
		$data['id']					= $idTask;
		$data['status']				= intval(Todoyu::$CONFIG['EXT']['project']['taskDefaults']['statusQuickTask']);
		$data['id_person_assigned']	= TodoyuAuth::getPersonID();
		$data['id_person_owner']		= TodoyuAuth::getPersonID();
		$data['date_start']			= NOW;
		$data['date_end']			= NOW + TodoyuTime::SECONDS_WEEK;
		$data['date_deadline']		= NOW + TodoyuTime::SECONDS_WEEK;
		$data['type']				= TASK_TYPE_TASK;
		$data['estimated_workload']	= TodoyuTime::SECONDS_HOUR;

			// If task already done: set also date_end
		if( intval($data['task_done']) === 1 ) {
			$data['status'] 	= STATUS_DONE;
			$data['date_end']	= NOW;
		}
		unset($data['task_done']);

			// Call form hooks to save external data
		$xmlPath	= 'ext/project/config/form/quicktask.xml';
		$data		= TodoyuFormHook::callSaveData($xmlPath, $data, $idTask);

			// Save task to DB
		$idTask = TodoyuTaskManager::saveTask($data);

		return $idTask;
	}


}

?>