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
			// Construct form object
		$xmlPath	= 'ext/project/config/form/quicktask.xml';
		$form		= TodoyuFormManager::getForm($xmlPath);

			// Preset (empty) form data
		$formData	= $form->getFormData();
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, 0);

		return $form->render();
	}



	/**
	 * Adds mandatory task data to that received from quicktask form, saves new task to DB
	 *
	 *	@param	Array	$formData
	 *	@return	Integer
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
		$data['status']				= STATUS_OPEN;
		$data['id_user_assigned']	= TodoyuAuth::getUserID();
		$data['id_user_owner']		= TodoyuAuth::getUserID();
		$data['date_start']			= NOW;
		$data['date_end']			= NOW + TodoyuTime::SECONDS_WEEK;
		$data['date_deadline']		= NOW + TodoyuTime::SECONDS_WEEK;
		$data['type']				= TASK_TYPE_TASK;
		$data['estimated_workload']	= TodoyuTime::SECONDS_HOUR;

			// If task already done: set also date_end and date_finish
		if( intval($data['task_done']) === 1 ) {
			$data['status'] 	= STATUS_DONE;
			$data['date_end']	= NOW;
			$data['date_finish']= NOW;
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