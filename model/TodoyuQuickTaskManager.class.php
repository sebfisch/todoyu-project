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
	public function renderForm()	{
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
	public static function save(array $formData) {
		$data	= array(
			'title'				=> $formData['title'],
			'description'		=> $formData['description'],
			'id_project'		=> $formData['id_project'],
			'id_worktype'		=> $formData['id_worktype'],
			'status'			=> STATUS_OPEN,
			'id_user_assigned'	=> TodoyuAuth::getUserID(),
			'id_user_owner'		=> TodoyuAuth::getUserID(),
			'date_start'		=> NOW,
			'date_end'			=> NOW + TodoyuTime::SECONDS_WEEK,
			'date_deadline'		=> NOW + TodoyuTime::SECONDS_WEEK,
			'type'				=> TASK_TYPE_TASK,
			'estimated_workload'=> TodoyuTime::SECONDS_HOUR
		);

			// If task already done: set also date_end and date_finish
		if( intval($formData['task_done']) === 1 ) {
			$data['status'] 	= STATUS_DONE;
			$data['date_end']	= NOW;
			$data['date_finish']= NOW;
		}

			// 'Start tracking' checked? set status accordingly
		if( intval($formData['start_tracking']) === 1 ) {
			$data['status'] = STATUS_PROGRESS;
		}

			// Save task to DB
		$idTask = TodoyuTaskManager::saveTask($data);

			// If already tracked workload: have it added to workload record of task in DB
		if( intval($formData['workload_tracked']) > 0 ) {
			self::addTrackedWorkload($idTask, $formData['workload_tracked']);
		}

		return $idTask;
	}



	/**
	 * Add already tracked (seconds of) workload to workload record of given task.
	 *
	 *	@param	Integer	$idTask
	 *	@param	Integer	$workload
	 */
	protected static function addTrackedWorkload($idTask, $workload) {
		$idTask		= intval($idTask);
		$workload	= intval($workload);

		$data	= array(
			'id_user'			=> TodoyuAuth::getUserID(),
			'id_task'			=> $idTask,
			'date_create'		=> NOW,
			'workload_tracked'	=> $workload
		);

		TodoyuTimetrackingManager::saveWorkloadRecord($data);
	}

}

?>