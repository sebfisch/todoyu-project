<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
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
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderForm($idProject = 0) {
		$idProject	= intval($idProject);

		$form		= self::getQuickTaskForm($idProject);

		return $form->render();
	}



	/**
	 * Get quicktask form which is customized for current user
	 *
	 * @param	Integer		$idProject
	 * @return	TodoyuForm
	 */
	public static function getQuickTaskForm($idProject = 0) {
		$idProject	= intval($idProject);

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
		$form->getFieldset('main')->addField('id_project', $field, 'before:title');

		$formData	= TodoyuTaskManager::getTaskDefaultData(0, $idProject);
			// Set project ID, if given and allowed to user
		if( $idProject > 0 && TodoyuTaskRights::isAddInProjectAllowed($idProject, false) ) {
			$formData['id_project']	= $idProject;
		}

			// Load form data by hooks (default is empty)
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData);

			// Ensure the preset project allows for adding tasks
		if( ! TodoyuTaskRights::isAddInProjectAllowed($formData['id_project']) ) {
			$formData['id_project']	= 0;
		}

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
		$idProject	= intval($data['id_project']);
		$firstData	= array(
			'id_project'	=> $idProject,
			'id_parenttask'	=> 0
		);
		$idTask		= TodoyuTaskManager::addTask($firstData);

			// Get presets from taskpreset set (if assigned) or extension config
		$idTaskpreset	= TodoyuProjectManager::getProject($idProject)->getTaskpresetID();
		if( intval($idTaskpreset) > 0 ) {
			$presets	= TodoyuTaskpresetManager::getTaskpresetData($idTaskpreset);
			$presets['title']	= $presets['tasktitle'];
		} else {
			$presets	= TodoyuExtConfManager::getExtConf('project');
		}

			// Prepare data to save task
		$data['id']					= $idTask;
		$data['status']				= $presets['status'] > 0 ? $presets['status'] : intval(Todoyu::$CONFIG['EXT']['project']['taskDefaults']['statusQuickTask']);

		if( ! allowed('project', 'task:editPersonAssigned')) {
			$data['id_person_assigned']	= ( $presets['id_person_assigned'] > 0 ) ? intval($presets['id_person_assigned']) : TodoyuProjectManager::getRolePerson($idProject, $presets['person_assigned_role']);
		} else {
			$data['id_person_assigned']	= TodoyuAuth::getPersonID();
		}

		if( ! allowed('project', 'task:editPersonOwner')) {
			$data['id_person_owner']= ( $presets['id_person_owner'] > 0 ) ? intval($presets['id_person_owner']) : TodoyuProjectManager::getRolePerson($idProject, $presets['person_owner_role']);
		} else {
			$data['id_person_owner']= TodoyuAuth::getPersonID();
		}

		$data['date_start']			= NOW;

		$durationInDays			= $presets['quicktask_duration_days'] > 0 ? intval($presets['quicktask_duration_days']) : intval(Todoyu::$CONFIG['EXT']['project']['quicktask']['durationDays']);
		$timestampDateEnd		= NOW + ($durationInDays * TodoyuTime::SECONDS_DAY);
		$data['date_end']		= $timestampDateEnd;
		$data['date_deadline']	= $timestampDateEnd;

		$data['type']				= TASK_TYPE_TASK;
		$data['estimated_workload']	= $presets['estimated_workload'] > 0 ? $presets['estimated_workload'] : TodoyuTime::SECONDS_HOUR;

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