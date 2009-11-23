<?php

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
		$formData	= array();
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, 0);

		return $form->render();
	}

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

			// If task already done
		if( intval($formData['task_done']) === 1 ) {
			$data['status'] 	= STATUS_DONE;
			$data['date_end']	= NOW;
			$data['date_finish']= NOW;
		}

			// Start tracking
		if( intval($formData['start_tracking']) === 1 ) {
			$data['status'] = STATUS_PROGRESS;
		}

			// Save task
		$idTask = TodoyuTaskManager::saveTask($data);

			// If already tracked workload set
		if( intval($formData['workload_tracked']) > 0 ) {
			self::addTrackedWorkload($idTask, $formData['workload_tracked']);
		}

		return $idTask;
	}



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