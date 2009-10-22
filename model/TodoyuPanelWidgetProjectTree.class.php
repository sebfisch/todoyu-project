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
 * Panel widget for project tree
 *
 * @package		Todoyu
 * @subpackage	Project
 */

class TodoyuPanelWidgetProjectTree extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {


	/**
	 * Expanded projects in the tree
	 *
	 * @var	Array
	 */
	private $expandedProjects 	= array();



	/**
	 * Expanded tasks in the tree
	 *
	 * @var	Array
	 */
	private $expandedTasks 		= array();



	/**
	 * Active filters
	 *
	 * @var	Array
	 */
	private $activeFilters		= array();



	/**
	 * Initialize projectTree PanelWidget
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

			// construct PanelWidget (init basic configuration)
		parent::__construct(
			'project',								// ext key
			'projecttree',							// panel widget ID
			'LLL:panelwidget-projecttree.title',	// widget title text
			$config,								// widget config array
			$params,								// widget params
			$idArea									// area ID
		);

			// Load widget data
		$this->initData();
	}



	/**
	 * Load widget preference data
	 *
	 */
	private function initData() {
		$this->expandedProjects = TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', 0, $this->getArea());
		$this->expandedTasks 	= TodoyuPreferenceManager::getPreferences(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', 0, $this->getArea());
		$this->activeFilters	= self::getActiveFilters($this->getArea());
	}



	/**
	 * Get projects which match to the active filters
	 *
	 * @return	Array
	 */
	private function getProjectIDs() {
			// Create project filter
		$projectFilter	= new TodoyuProjectFilter($this->activeFilters);

			// Add extra table to search in and order by
		$projectFilter->addExtraTable('ext_user_customer');
		$projectFilter->addExtraWhere('ext_project_project.id_customer = ext_user_customer.id');

			// Define output config
		$order		= 'ext_user_customer.shortname, ext_project_project.title';
		$limit		= 20;

			// Get matching project IDs
		$projectIDs	= $projectFilter->getProjectIDs($order, $limit);

		return $projectIDs;
	}



	/**
	 * Check if a project should be expanded in the project
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	private function isProjectExpanded($idProject) {
		return in_array($idProject, $this->expandedProjects);
	}



	/**
	 * Check if a task is expanded
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	private function isTaskExpanded($idTask) {
		$idTask	= intval($idTask);

		return in_array($idTask, $this->expandedTasks);
	}



	/**
	 * Get data to render the project tree
	 *
	 * @return	Array
	 */
	private function getProjectTreeData() {
		$projectIDs	= $this->getProjectIDs();
		$data		= array();

		foreach($projectIDs as $idProject) {
			$project		= TodoyuProjectManager::getProject($idProject);
			$isExpanded		= $this->isProjectExpanded($idProject);

			$projectData = array(	'idProject'		=> $idProject,
									'htmlId'		=> 'panelwidget-projecttree-project-' . $idProject,
									'status'		=> 'project' . ucfirst($project->getStatusKey()),
									'clickExpand'	=> 'Todoyu.Ext.project.PanelWidget.ProjectTree.toggleProjectTasks(' . $idProject . ')',
									'clickDetail'	=> 'Todoyu.Ext.project.PanelWidget.ProjectTree.openProject(' . $idProject . ')',
									'title'			=> 'Projekt ID: ' . $idProject,
									'label'			=> $project->getFullTitle(),
									'class'			=> 'project expandable',
									'expandable'	=> true);

			if( $isExpanded ) {
				$projectData['subtasks'] = $this->renderTaskTree($idProject, 'project', true);
				$projectData['class'] .= ' expanded';
			}

			$data[] = $projectData;
		}

		return $data;
	}



	/**
	 * Get data to render the task tree
	 *
	 * @param	Integer		$idParent		ID of the parent element
	 * @param	String		$type			Type of the parent of the tasktree
	 * @return	Array
	 */
	public function getTaskTreeData($idParent, $type) {
		$idParent	= intval($idParent);

		switch($type) {
			case 'project':
				$taskIDs	= TodoyuProjectManager::getTaskIDs($idParent);
			break;

			case 'task':
				$taskIDs	= TodoyuTaskManager::getSubtaskIDs($idParent);
			break;

			default:
				$taskIDs	= array();
		}

		foreach($taskIDs as $idTask) {
			$task			= TodoyuTaskManager::getTask($idTask);

			$taskData = array(
				'idProject'		=> $task->getProjectID(),
				'idTask'		=> $idTask,
				'htmlId'		=> 'panelwidget-projecttree-task-' . $idTask,
				'status'		=> $task->getStatusKey(),
				'clickExpand'	=> 'Todoyu.Ext.project.PanelWidget.ProjectTree.toggleSubtasks(' . $idTask . ')',
				'clickDetail'	=> 'Todoyu.Ext.project.PanelWidget.ProjectTree.openProjectTask(' . $task->getProjectID() . ',' . $idTask . ')',
				'title'			=> 'Task ID: ' . $idTask,
				'label'			=> $task->getTitle(),
				'class'			=> '',
				'expandable'	=> false
			);


			if( $this->isTaskExpanded($idTask) ) {
				$taskData['subtasks'] = $this->renderTaskTree($idTask, 'task', true);
				$taskData['class'] = 'expanded';
			}

			if( TodoyuTaskManager::hasSubtasks($idTask) ) {
				$taskData['class'] .= ' expandable';
				$taskData['expandable'] = true;
			}

			$data[] = $taskData;
		}

		return $data;
	}



	/**
	 * Render task tree
	 *
	 * @param	Integer		$idParent
	 * @param	String		$type
	 * @param	Boolean		$display			Display task tree (not hidden)
	 * @return	String
	 */
	public function renderTaskTree($idParent, $type, $display = false) {
		$idParent	= intval($idParent);
		$data		= array(
			'id'	=> 'panelwidget-projecttree-subtasks-' . $type . '-' . $idParent,
			'class'	=> 'subtree',
			'tree'	=> $this->getTaskTreeData($idParent, $type),
			'style'	=> 'display:' . ($display ? 'block' : 'none')
		);
		$tmpl	= 'ext/project/view/panelwidget-projecttree-tree.tmpl';

		return render($tmpl, $data);
	}



	/**
	 * Render the filter form based on the selected filters
	 *
	 * @return	String
	 */
	private function renderFilterForm() {
			// Construct form object
		$xmlPath 	= 'ext/project/config/form/panelwidget-projecttree-filter.xml';
		$form 		= new TodoyuForm($xmlPath);
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, 0);

			// Load form data
		$formData		= array();
		$activeFilters	= $this->getActiveFilters(AREA);
			// Set a global variable for the form
		$form->setVars(	array( 'idArea' => $this->idArea) );
		$displayFieldset	= $form->getFieldset('display');

		foreach($activeFilters as $filter) {
			$field	= $form->getField( $filter['filter'] );
			$displayFieldset->addField( $filter['filter'], $field );
				// Add filters' values to formData
			$formData[ $filter['filter'] ] = $filter['value'];
		}

		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, 0);

			// Set form data
		$form->removeFieldset('filters');
		$form->setFormData($formData);
		$form->setRecordID(0);
		$form->setUseRecordID(false);

			// Render
		return $form->render();
	}



	/**
	 * Render project tree
	 *
	 * @return	String
	 */
	public function renderTree() {
		$tmpl	= 'ext/project/view/panelwidget-projecttree-tree.tmpl';
		$data	= array(
			'id'	=> 'panelwidget-projecttree-tree',
			'class'	=> 'tree',
			'tree'	=> $this->getProjectTreeData()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render filter form
	 *
	 * @return	String
	 */
	public function renderFilters() {
		$tmpl	= 'ext/project/view/panelwidget-projecttree-filter.tmpl';
		$data	= array(
			'id'	=> $this->getID(),
			'filter'=> $this->renderFilterForm()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render the panel widget content
	 *
	 * @return	String
	 */
	public function renderContent() {
		$content	= $this->renderFilters();
		$content	.=$this->renderTree();

		$this->setContent($content);

		return $content;
	}



	/**
	 * Render the whole panel widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->renderContent();

			// Add widget assets
		TodoyuPage::addExtAssets('project', 'panelwidget-projecttree');

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.PanelWidget.ProjectTree.init.bind(Todoyu.Ext.project.PanelWidget.ProjectTree)');

		return parent::render();
	}



	/**
	 * Get available filters which are not in use yet
	 *
	 * @param	TodoyuFormElement	$field
	 * @return	Array				Option array for select element
	 */
	public static function getAvailableFields(TodoyuFormElement $field) {
		$filters	= $field->getForm()->getFieldset('filters')->getFieldNames();
		$idArea		= intval($field->getForm()->getVar('idArea'));

		$activeFilterNames = self::getActiveFilterNames($idArea);

		$filters	= array_values(array_diff($filters, $activeFilterNames));

		$options	= array();
		$options[]	= array(
			'label' => 'LLL:panelwidget-projecttree.filter.addNewFilter',
			'value' => '0'
		);

		foreach($filters as $fieldName) {
			$field = $field->getForm()->getField($fieldName);

			$options[] = array(
				'label'	=> '- ' . TodoyuDiv::getLabel($field->getLabel()),
				'value'	=> $fieldName
			);
		}

		return $options;
	}



	/**
	 * Get status options for select element
	 *
	 * @return	Array
	 */
	public static function getStatusOptions() {
		$statusInfos= TodoyuProjectStatusManager::getProjectStatusInfos();
		$options	= array();

		foreach($statusInfos as $index => $statusInfo) {
			$options[] = array(
				'value'	=> $statusInfo['index'],
				'label'	=> $statusInfo['label']
			);
		}

		return $options;
	}



	/**
	 * Get active filters
	 *
	 * @param 	Integer	$idArea
	 * @return	Array
	 */
	public static function getActiveFilters($idArea = 0) {
		$idArea	= intval($idArea);

		$filters = TodoyuProjectPreferences::getPref('panelwidget-projecttree-filter', 0, $idArea);

		if( $filters === false || $filters === '' ) {
			return array();
		} else {
			return json_decode($filters, true);
		}
	}



	/**
	 * Save active filters in user preferences
	 *
	 * @param	Array		$activeFilters
	 * @param	Integer		$idArea
	 */
	public static function saveActiveFilters(array $activeFilters = array(), $idArea = 0) {
		$activeFilters	= json_encode($activeFilters);
		$idArea			= intval($idArea);

		TodoyuProjectPreferences::savePref('panelwidget-projecttree-filter', $activeFilters, 0, true, $idArea);
	}



	/**
	 * Get the names of the active filters
	 *
	 * @param	Integer	$idArea
	 * @return	Array
	 */
	public static function getActiveFilterNames($idArea = 0) {
		$idArea		= intval($idArea);

		$filters	= self::getActiveFilters($idArea);
		$names		= array();

		foreach($filters as $filter) {
			$names[] = $filter['filter'];
		}

		return $names;
	}



	/**
	 * Update the active filters. Option to add a new filter
	 *
	 * @param	Array		$activeFilters
	 * @param	Integer		$idArea
	 */
	public static function updateActiveFilters(array $currentFilters, $idArea = 0) {
		$idArea	= intval($idArea);

			// Load active filters, but without caching
		TodoyuCache::disable();
		$activeFilters	= self::getActiveFilters($idArea);
		TodoyuCache::enable();

			// Update active filters with new submitted values
		foreach($activeFilters as $index => $filterData) {
			foreach($currentFilters as $name => $value) {
				if( $filterData['filter'] == $name ) {
					$activeFilters[$index]['value'] = $value;
				}
			}
		}

		self::saveActiveFilters($activeFilters, $idArea);
	}



	/**
	 * Add a new filter to the filter list
	 *
	 * @param	String		$name
	 * @param	Mixed		$value
	 * @param	Integer		$idArea
	 */
	public static function addNewFilter($name, $value = '', $idArea = 0) {
		$idArea	= intval($idArea);

			// Load active filters, but without caching
		TodoyuCache::disable();
		$activeFilters	= self::getActiveFilters($idArea);
		TodoyuCache::enable();

		$activeFilters[] = array(
			'filter'=> $name,
			'value'	=> $value
		);

		self::saveActiveFilters($activeFilters, $idArea);
	}



	/**
	 * Remove a filter from the filter list
	 *
	 * @param	String		$name
	 * @param	Integer		$idArea
	 */
	public static function removeFilter($name, $idArea = 0) {
		$idArea	= intval($idArea);

			// Load active filters, but without caching
		TodoyuCache::disable();
		$activeFilters	= self::getActiveFilters($idArea);
		TodoyuCache::enable();

		foreach($activeFilters as $index => $activeFilter) {
			if( $activeFilter['filter'] === $name ) {
				unset($activeFilters[$index]);
			}
		}

		self::saveActiveFilters($activeFilters, $idArea);
	}



	/**
	 * Save an expanded project in the user preferences
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public static function saveProjectExpanded($idProject, $idArea, $expanded = true) {
		$idProject	= intval($idProject);
		$idArea		= intval($idArea);

		if( $expanded ) {
			TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', $idProject, 0, false, $idArea);
		} else {
			TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', $idProject, 0, $idArea);
		}
	}



	/**
	 * Save an expanded task in the user preferences
	 *
	 * @param	Integer		$idTask
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public static function saveTaskExpanded($idTask, $idArea, $expanded = true) {
		$idTask	= intval($idTask);
		$idArea		= intval($idArea);

		if( $expanded ) {
			TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', $idTask, 0, false, $idArea);
		} else {
			TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', $idTask, 0, $idArea);
		}
	}


	public static function isAllowed() {
		return allowed('project', 'panelwidget.projectTree.use');
	}

}


?>