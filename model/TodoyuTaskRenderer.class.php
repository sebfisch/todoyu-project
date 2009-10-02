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
 * Render class Todoyufor task elements
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuTaskRenderer {

	/**
	 * Render a task detail
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTaskDetail($idTask, $activeTab = null) {
		$idTask	= intval($idTask);
		$task	= TodoyuTaskManager::getTask($idTask);

		$tmpl	= 'ext/project/view/task-details.tmpl';
		$data	= array(
			'task'	=> $task,
			'idTask'	=> $idTask,
			'taskdata'	=> self::renderTaskData($idTask)
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
		$task	= TodoyuTaskManager::getTask($idTask);

			// Get task template data
		$data	= $task->getTemplateData(0);
		$data['data']	= TodoyuTaskManager::getTaskDataArray($idTask);

		return render('ext/project/view/task-data.tmpl', $data);
	}



	/**
	 * Render the task edit form
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTaskEditForm($idTask) {
		$idTask			= intval($idTask);
		$task			= TodoyuTaskManager::getTask($idTask);

			// Construct form object
		$xmlPath	= 'ext/project/config/form/task.xml';
		$form		= new TodoyuForm($xmlPath);
		TodoyuFormHook::callBuildForm($xmlPath, $form, $idTask);

			// Load form data
		$formData	= $task->getTemplateData(0);
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idTask);

			// Set form data
		$form->setFormData($formData);
		$form->setRecordID($idTask);

			// Render
		$data	= array(
			'idTask'	=> $idTask,
			'formhtml'	=> $form->render()
		);

		return render('ext/project/view/task-edit.tmpl', $data);
	}



	/**
	 * Render edit form to edit a new task. This form is wraped by
	 * the "detail" and "data" div as used in detail view
	 *
	 * @return	String
	 */
	public static function renderNewTaskEditForm() {
		$idTask			= 0;
		$idProject		= intval($idProject);
		$idParentTask	= intval($idParentTask);

			// Render form for new empty task
		$form	= self::renderTaskEditForm($idTask);

			// Render form into detail wrapper
		$data	= array(
			'idTask'	=> $idTask,
			'task'	=> array(
				'status'	=> STATUS_OPEN
			),
			'taskdata'	=> $form
		);
		$tmpl	= 'ext/project/view/task-detail-data-wrap.tmpl';

		return render($tmpl, $data);
	}



	/**
	 * Render all task tabs (only detail of the active tab)
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTabs($idTask, $activeTab = null) {
		$idTask		= intval($idTask);
		$activeTab	= is_null($activeTab) ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab ;

		$tabHeads		= self::renderTabHeads($idTask, $activeTab);
		$tabContents	= self::renderTabContent($idTask, $activeTab);

		return $tabHeads . $tabContents;
	}



	/**
	 * Render the heads of all tabs
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTabHeads($idTask, $activeTab = null) {
		$idTask		= intval($idTask);

		$listID		= 'task-' . $idTask . '-tabheads';
		$class		= 'tabs taskTabheads';
		$jsHandler	= 'Todoyu.Ext.project.Task.Tab.onSelect.bind(Todoyu.Ext.project.Task.Tab)';
		$tabs		= TodoyuTaskManager::getTabs($idTask);
		$idArea		= Todoyu::getArea();
		$activeTab	= is_null($activeTab) ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab;

			// Add special fields for task tabs
		foreach($tabs as $index => $tab) {
			$tabs[$index]['htmlId'] 	= 'task-' . $idTask . '-tabhead-' . $tab['id'];
			$tabs[$index]['classKey'] 	= $tab['id'] . '-' . $idTask;
		}

		return TodoyuTabheadRenderer::renderTabs($listID, $class, $jsHandler, $tabs, $activeTab);
	}



	/**
	 * Render the active tab content of a task
	 *
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderTabContent($idTask, $activeTab = null) {
		$idTask		= intval($idTask);

		$tabsConfig	= TodoyuTaskManager::getTabs($idTask);
		$tabContent	= '';
		$activeTab	= is_null($activeTab) ? TodoyuProjectPreferences::getActiveTaskTab($idTask) : $activeTab;

			// Only render active tab
		foreach($tabsConfig as $tabConfig) {
			if( $tabConfig['id'] == $activeTab ) {
				$tabContent	= TodoyuDiv::callUserFunction($tabConfig['content'], $idTask);
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