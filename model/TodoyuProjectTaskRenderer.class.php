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
 * Render class for task elements
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectTaskRenderer {

	/**
	 * Render task for listing
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderListingTask($idTask) {
		$idTask		= intval($idTask);

				// Get some task information
		$isExpanded	= TodoyuProjectTaskManager::isTaskExpanded($idTask);
		$taskData	= TodoyuProjectTaskManager::getTaskInfoArray($idTask, 3);

			// Prepare data array for template
		$tmpl	= 'ext/project/view/task-listing-item.tmpl';
		$data 	= array(
			'task'				=> $taskData,
			'isExpanded'		=> $isExpanded,
			'subtasks'			=> '',
			'taskIcons'			=> TodoyuProjectTaskManager::getAllTaskIcons($idTask),
		);

			// Render details if task is expanded
		if( $isExpanded ) {
			$activeTab		= TodoyuProjectPreferences::getActiveTaskTab($idTask);
			$data['details']= TodoyuProjectTaskRenderer::renderTaskDetail($idTask, $activeTab);
			$data['task']['class'] .= ' expanded';
		}

		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskDataBeforeRendering', $data, array($idTask));

		return render($tmpl, $data);
	}



	/**
	 * Render task list
	 *
	 * @param	Array	$taskIDs
	 * @return	String
	 */
	public static function renderTaskListing(array $taskIDs) {
		$taskIDs	= TodoyuArray::intval($taskIDs, true, true);
		$tasksHTML	= array();

		foreach($taskIDs as $idTask) {
			if( TodoyuProjectTaskRights::isSeeAllowed($idTask) ) {
				$tasksHTML[] = self::renderListingTask($idTask);
			}
		}

		$tmpl	= 'ext/project/view/task-listing.tmpl';
		$data	= array(
			'tasks'		=> $tasksHTML
		);

			// Add context menu init scripts
		$data['javascript'] = 'Todoyu.Ext.project.ContextMenuTask.attach();';

		return render($tmpl, $data);
	}



	/**
	 * Render details of given task
	 *
	 * @param	Integer		$idTask
	 * @param	String		$activeTab
	 * @return	String
	 */
	public static function renderTaskDetail($idTask, $activeTab = '') {
		$idTask	= intval($idTask);
		$task	= TodoyuProjectTaskManager::getTask($idTask);

		$tmpl	= 'ext/project/view/task-details.tmpl';
		$data	= array(
			'task'		=> $task,
			'idTask'	=> $idTask,
			'taskData'	=> self::renderTaskData($idTask)
		);

			// Only add tabs if its a normal task (not container)
		if( $task->isTask() ) {
			$data['tabs'] = self::renderTabs($idTask, $activeTab);
		}

		return render($tmpl, $data);
	}



	/**
	 * Render the task data
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTaskData($idTask) {
		$idTask	= intval($idTask);

			// Get task object
		$task	= TodoyuProjectTaskManager::getTask($idTask);

		$tmpl	= 'ext/project/view/task-data.tmpl';

			// Get task template data
		$data			= $task->getTemplateData(0);
		$data['data']	= TodoyuProjectTaskManager::getTaskInfos($idTask);

		$fieldKeys		= TodoyuArray::getColumn($data['data'], '__key');
		$fieldIndexes	= array_flip($fieldKeys);

			// Person can only see public tasks? remove visibility info
		if( ! Todoyu::person()->isInternal() || ! allowed('project', 'task:seeAll') ) {
			unset($data['data'][$fieldIndexes['is_public']]);
		}

			// Remove info about task owner/creator if not visible to current user
		if( ! allowed('contact', 'person:seeAllPersons') ) {
			$allowedPersonIDs	= TodoyuContactPersonRights::getPersonIDsAllowedToBeSeen();

			if( ! in_array($task->getPersonID('owner'), $allowedPersonIDs) ) {
				unset($data['data'][$fieldIndexes['person_owner']]);
			}

			if( ! in_array($task->getPersonID('create'), $allowedPersonIDs) ) {
				unset($data['data'][$fieldIndexes['person_create']]);
			}
		}

		return render($tmpl, $data);
	}



	/**
	 * Render quick creation form
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderQuickCreateForm($idProject = 0) {
		$idProject	= intval($idProject);

		$form	= TodoyuProjectTaskManager::getQuickCreateForm($idProject);

		return $form->render();
	}



	/**
	 * Render the task edit form
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTaskEditForm($idTask) {
		$idTask		= intval($idTask);
		$task		= TodoyuProjectTaskManager::getTask($idTask);
		$xmlPath	= 'ext/project/config/form/task.xml';

			// Construct form object
		$form		= TodoyuFormManager::getForm($xmlPath, $idTask);

			// Adapt task form labels for container
		if( $task->isContainer() ) {
			$fieldNames	= $form->getFieldnames();
			$rightFieldSet	= $form->getFieldset('right');
			if( in_array('id_person_owner', $fieldNames) ) {
				$rightFieldSet->getField('id_person_owner')->setAttribute('label', 'project.task.container.attr.person_owner');
			}
			if( in_array('is_public', $fieldNames) ) {
				$rightFieldSet->getField('is_public')->setAttribute('label', 'project.task.container.attr.is_public');
			}
		}

			// Load form data
		$data	= $task->getTemplateData(0);
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idTask);

			// Set form data
		$form->setFormData($data);

			// Render
		$tmpl	= 'ext/project/view/task-edit.tmpl';
		$data	= array(
			'idTask'	=> $idTask,
			'formhtml'	=> $form->render()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render edit form to edit a new task or container. This form is wrapped by
	 * the "detail" and "data" div as used in detail view
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idParentTask
	 * @param	Integer		$type
	 * @return	String
	 */
	public static function renderNewTaskEditForm($idProject, $idParentTask = 0, $type = TASK_TYPE_TASK) {
		$idTask			= 0;

			// Render form for new empty task
		$form	= self::renderTaskEditForm($idTask);

			// Render form into detail wrapper
		$tmpl	= 'ext/project/view/task-detail-data-wrap.tmpl';
		$data	= array(
			'idTask'	=> $idTask,
			'task'	=> array(
				'status'	=> ( $type === TASK_TYPE_TASK ) ? STATUS_OPEN : 0
			),
			'taskdata'	=> $form
		);

		return render($tmpl, $data);
	}



	/**
	 * Render all task tabs (only detail of the active tab)
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTabs($idTask, $activeTab = '') {
		$idTask		= intval($idTask);
		$activeTab	= trim($activeTab) === '' ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab ;

		$tabHeads		= self::renderTabHeads($idTask, $activeTab);
		$tabContents	= self::renderTabsContent($idTask, $activeTab);

		return $tabHeads . $tabContents;
	}



	/**
	 * Render content of a task tab
	 *
	 * @param	Integer		$idTask
	 * @param	String		$tab
	 * @return	String
	 */
	public static function renderTabContent($idTask, $tab) {
		$idTask		= intval($idTask);
		$tabConfig	= TodoyuProjectTaskManager::getTabConfig($tab);

		return TodoyuFunction::callUserFunction($tabConfig['content'], $idTask);
	}



	/**
	 * Render the heads of all tabs
	 *
	 * @param	Integer		$idTask
	 * @param	String		$activeTab
	 * @return	String
	 */
	public static function renderTabHeads($idTask, $activeTab = '') {
		$idTask		= intval($idTask);
		$name		= 'task-' . $idTask;
		$jsHandler	= 'Todoyu.Ext.project.Task.Tab.onSelect.bind(Todoyu.Ext.project.Task.Tab)';
		$tabs		= TodoyuProjectTaskManager::getTabs($idTask);
		$activeTab	= $activeTab === '' ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab;

			// Add special fields for task tabs
		foreach($tabs as $index => $tab) {
			$tabs[$index]['htmlId'] 	= 'task-' . $idTask . '-tab-' . $tab['id'];
			$tabs[$index]['classKey'] 	= $tab['id'] . '-' . $idTask;
		}

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $activeTab);
	}



	/**
	 * Render tab container with the content tab of the active task
	 *
	 * @param	Integer		$idTask
	 * @param	String		$activeTab
	 * @return	String
	 */
	public static function renderTabsContent($idTask, $activeTab = '') {
		$idTask		= intval($idTask);

		$tabsConfig	= TodoyuProjectTaskManager::getTabs($idTask);
		$tabContent	= '';
		$activeTab	= $activeTab === '' ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab;

			// Only render active tab
		foreach($tabsConfig as $tabConfig) {
			if( $tabConfig['id'] == $activeTab ) {
				$tabContent	= TodoyuFunction::callUserFunction($tabConfig['content'], $idTask);
				break;
			}
		}

		$data	= array(
			'tabHtml'	=> $tabContent,
			'tabKey'	=> $activeTab,
			'idTask'	=> $idTask
		);
		$tmpl	= 'ext/project/view/task-tabs.tmpl';

		return render($tmpl, $data);
	}

}

?>