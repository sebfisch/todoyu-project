<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Project Renderer
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuProjectRenderer {

	/**
	 * @var	String		Extension key
	 */
	const EXTKEY = 'project';

	/**
	 * @var	Array		Visible sub tasks in project view
	 */
	private static $visibleSubTaskIDs = null;

	/**
	 * @var	Array		Rootline of a task which is forced to be open
	 */
	private static $openRootline	= array();

	/**
	 * @var	Array		List of rendered task IDs
	 */
	public static $renderedTasks	= array();



	/**
	 * Render sub tabs of (recently viewed) projects in projects area
	 *
	 * @return	String
	 */
	public static function renderProjectsTabs() {
		$openProjectIDs	= TodoyuProjectPreferences::getOpenProjectIDs();

		if( sizeof($openProjectIDs) === 0 ) {
			return self::renderNoProjectSelectedTab();
		} else {
			return self::renderProjectTabs();
		}
	}



	/**
	 * Render task tree view for project tab
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idTask			Make sure this task is visible (tree open)
	 * @return	String
	 */
	public static function renderProjectsContent($idProject, $idTask, $tab = null) {
		$idProject	= intval($idProject);
		$idTask		= intval($idTask);
		$content	= '';

		if( $idProject === 0 ) {
			$content= self::renderNoProjectSelectContent();
		} else {
			$content= self::renderSelectedProjectContent($idProject, $idTask, $tab);
		}

		return $content;
	}



	/**
	 * Render project view where no project is selected yet.
	 * Instead of the tree, there will be an infobox with options
	 *
	 * @return	String
	 */
	protected static function renderNoProjectSelectContent() {
		$tmpl	= 'ext/project/view/project-noselected.tmpl';
		$data	= array();

		return render($tmpl, $data);
	}



	/**
	 * Render project view with a currently selected project (and task)
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idTask
	 * @return	String
	 */
	protected static function renderSelectedProjectContent($idProject, $idTask = 0, $tab = null) {
		$idProject	= intval($idProject);
		$idTask		= intval($idTask);

		$tmpl	= 'ext/project/view/projecttasktrees.tmpl';
		$data	= array(
			'idProject'	=> $idProject,
			'project'	=> self::renderTabbedProject($idProject, $idTask, $tab),
		);

		return render($tmpl, $data);
	}



	/**
	 * Render tabbed project
	 *
	 * @param	Integer	$idProject
	 * @param	Integer	$idTask
	 * @return 	String
	 */
	public static function renderTabbedProject($idProject, $idTask, $tab = null) {
		$idProject	= intval($idProject);
		$idTask		= intval($idTask);

		$project	= TodoyuProjectManager::getProject($idProject);

		$tmpl	= 'ext/project/view/projecttasktree.tmpl';
		$data	= array(
			'idProject'	=> $idProject,
			'statusKey'	=> $project->getStatusKey(),
			'header'	=> self::renderProjectHeader($idProject),
			'tasktree'	=> self::renderProjectTaskTree($idProject, $idTask, $tab)
		);

		return render($tmpl, $data);
	}



	/**
	 *
	 * @return	String
	 */
	public static function renderNoProjectSelectedView() {
		$tabs	= self::renderNoProjectSelectedTab();
		$content= self::renderNoProjectSelectContent();

		return TodoyuRenderer::renderContent($content, $tabs);
	}



	/**
	 * Render project panel widgets
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idTask
	 * @return	String
	 */
	public static function renderPanelWidgets($idProject, $idTask) {
		$idProject	= intval($idProject);
		$idTask		= intval($idTask);

		$params	= array(
			'project'	=> $idProject,
			'task'		=> $idTask
		);

		return TodoyuPanelWidgetRenderer::renderPanelWidgets('project', $params);
	}



	/**
	 * Render project header
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderProjectHeader($idProject, $withDetails = null) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);
		$tmpl		= 'ext/project/view/project-header.tmpl';

		$data	= $project->getTemplateData();
		$data	= TodoyuHookManager::callHookDataModifier('project', 'renderProjectHeader', $data);

			// If not forced, check preference
		if( is_null($withDetails) ) {
			$withDetails = TodoyuProjectPreferences::isProjectDetailsExpanded($idProject);
		}

		if( $withDetails === true ) {
			$data['details'] = self::renderProjectDetails($idProject);
		}

		return render($tmpl, $data);
	}



	/**
	 * Render project details in project view
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderProjectDetails($idProject) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);

		$tmpl		= 'ext/project/view/project-details.tmpl';

		$data		= $project->getTemplateData();

		$data['assignedPersons']	= TodoyuProjectRenderer::renderProjectPersons($idProject);

			// Get project data for info listing
		$data['properties']		= TodoyuProjectManager::getProjectDataArray($idProject);

			// Call hook to modify the collected project data
		$data	= TodoyuHookManager::callHookDataModifier('project', 'projectDataBeforeRendering', $data, array($idProject));

			// Get dynamicly added details elements
		$data['dynamicElements']	= TodoyuHookManager::callHook('project', 'projectDetailsDynamicElements', array($idProject));

		return render($tmpl, $data);
	}



	/**
	 * Render project persons for project info
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderProjectPersons($idProject) {
		$idProject	= intval($idProject);
		$tmpl		= 'ext/project/view/project-persons.tmpl';
		$data		= array(
			'persons'	=> TodoyuProjectManager::getProjectPersons($idProject)
		);

		return render($tmpl, $data);
	}



	/**
	 * Render project quick creation form
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public static function renderQuickCreateForm(array $params) {
		$form	= TodoyuProjectManager::getQuickCreateForm();

			// Preset (empty) form data
		$formData	= $form->getFormData();
		$formData	= TodoyuFormHook::callLoadData('ext/project/config/form/project.xml', $formData, 0);
		$form->setFormData($formData);

		return $form->render();
	}



	/**
	 * Render project edit form
	 *
	 * @param	Integer		$idProject
	 * @return	String
	 */
	public static function renderProjectEditForm($idProject) {
		$idProject	= intval($idProject);
		$project	= TodoyuProjectManager::getProject($idProject);

			// Build form object
		$xmlPath	= 'ext/project/config/form/project.xml';
		$form		= TodoyuFormManager::getForm($xmlPath, $idProject);

			// Load form data
		$data	= $project->getTemplateData(true);
		$data	= TodoyuFormHook::callLoadData($xmlPath, $data, $idProject);

		$form->setFormData($data);
		$form->setRecordID($idProject);

		$tmpl	= 'ext/project/view/project-edit.tmpl';
		$data	= array(
			'formhtml'	=> $form->render()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render project task tree
	 * The tree includes all task which match the current filter, and the lost tasks section
	 *
	 * @param	Integer		$idProject			Render the task tree of this project
	 * @param	Integer		$idTask				Make sure this task is visible
	 * @return	String
	 */
	public static function renderProjectTaskTree($idProject, $idTaskShow = 0, $tab = null) {
		$idProject	= intval($idProject);
		$idTaskShow	= intval($idTaskShow);

			// Initialize tree in javaScript if not a AJAX refresh
		if( ! TodoyuRequest::isAjaxRequest() ) {
			TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.TaskTree.init.bind(Todoyu.Ext.project.TaskTree)', 100);
		}

			// Get root tasks in project
		$rootTaskIDs	= TodoyuProjectManager::getRootTaskIDs($idProject);

			// Set rootline of the task, if a task is forced to be shown
		self::$openRootline = $idTaskShow === 0 ? array() : TodoyuTaskManager::getTaskRootline($idTaskShow);

			// Tree HTML buffer
		$treeHtml	= '';

			// Render tasks (with their sub tasks)
		foreach($rootTaskIDs as $idTask) {
			$treeHtml .= self::renderTask($idTask, $idTaskShow, false, $tab);
		}

			// Add a list of lost task (this task should be display, but their parent doesn't match the current filter)
		$treeHtml .= self::renderLostTasks($idProject, $idTaskShow);

		return $treeHtml;
	}



	/**
	 * Render sub tasks
	 *
	 * @param	Integer		$idTask			Parent task ID
	 * @param	Integer		$idTaskShow		Task to show (all parent subtrees will be rendered to show this task)
	 * @return	String
	 */
	public static function renderSubTasks($idTask, $idTaskShow = 0) {
		$idTask		= intval($idTask);
		$idTaskShow	= intval($idTaskShow);

		$tmpl	= 'ext/project/view/subtasks.tmpl';

			// Load open rootline if necessary
		if( $idTaskShow > 0 && sizeof(self::$openRootline) === 0 ) {
			self::$openRootline	= TodoyuTaskManager::getTaskRootline($idTaskShow);
		}

		$subTaskIDs	= TodoyuProjectManager::getSubTaskIDs($idTask);
		$data		= array(
			'idTask' 		=> $idTask,
			'subtaskHtml'	=> ''
		);

			// Render all sub tasks
		foreach($subTaskIDs as $idSubTask) {
			if ( TodoyuTaskRights::isSeeAllowed($idSubTask) ) {
				$data['subtaskHtml'] .= TodoyuProjectRenderer::renderTask($idSubTask, $idTaskShow);
			}
		}

		return render($tmpl, $data);
	}



	/**
	 * Render (list of) lost tasks
	 *
	 * @param	Integer		$idProject
	 * @param	Integer		$idTaskShow
	 * @return	String		HTML
	 */
	public static function renderLostTasks($idProject, $idTaskShow) {
		$idProject	= intval($idProject);
		$idTaskShow	= intval($idTaskShow);

		$tmpl	= 'ext/project/view/losttasks.tmpl';

			// Get lost task IDs
		$lostTaskIDs	= TodoyuProjectManager::getLostTaskInTaskTree($idProject, self::$renderedTasks);

			// If forced task is set, but not rendered, add to lost task if allowed
		if( $idTaskShow !== 0 ) {
			if( ! in_array($idTaskShow, self::$renderedTasks) ) {
				if( TodoyuTaskRights::isSeeAllowed($idTaskShow) ) {
					$lostTaskIDs[] = $idTaskShow;
				}
			}
		}

		$lostTaskHtml	= '';

		foreach($lostTaskIDs as $idLostTask) {
			if ( TodoyuTaskRights::isSeeAllowed($idLostTask) ) {
				$lostTaskHtml .= self::renderTask($idLostTask, 0, true);
			}
		}

		$data	= array(
			'losttasks' => $lostTaskHtml,
			'numTasks'	=> sizeof($lostTaskIDs),
			'idProject'	=> $idProject
		);

		return render($tmpl, $data);
	}



	/**
	 * Check whether task is expanded
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	private static function isTaskExpanded($idTask) {
		$idTask	= intval($idTask);

		if( is_null(self::$expandedTaskIDs) ) {
			self::$expandedTaskIDs = TodoyuProjectPreferences::getExpandedTasks();
		}

		return in_array($idTask, self::$expandedTaskIDs);
	}



	/**
	 * Check whether project is expanded
	 *
	 * @param	Integer		$idProject
	 * @return	Boolean
	 */
	private static function isProjectExpanded($idProject) {
		$idProject	= intval($idProject);

		return TodoyuPreferenceManager::getPreference(EXTID_PROJECT, 'expandedproject') == $idProject;
	}



	/**
	 * Check whether sub tasks are visible
	 *
	 * @param	Integer		$idTask
	 * @return	Boolean
	 */
	public static function areSubTasksVisible($idTask) {
		$idTask	= intval($idTask);

		if( in_array($idTask, self::$openRootline) ) {
			return true;
		}

		if( is_null(self::$visibleSubTaskIDs) ) {
			self::$visibleSubTaskIDs = TodoyuProjectPreferences::getVisibleSubTasks(EXTID_PROJECT);
		}

		return in_array($idTask, self::$visibleSubTaskIDs);
	}



	/**
	 * Render task for project task tree view
	 *
	 * @param	Integer		$idTask				ID of the task to render
	 * @param	Integer		$idTaskShow			ID of the task which is forced to be shown (if its a sub task of the rendered task)
	 * @param	Boolean		$withoutSubTasks	Don't render sub tasks
	 * @return	String		Rendered task HTML for project task tree view
	 */
	public static function renderTask($idTask, $idTaskShow = 0, $withoutSubTasks = false, $tab = null) {
		$idTask		= intval($idTask);
		$idTaskShow = intval($idTaskShow);

			// Register which tasks have been rendered
		self::$renderedTasks[] = $idTask;

			// Get some task information
		$isExpanded	= $idTask > 0 ? ( $idTask === $idTaskShow ? true : TodoyuTaskManager::isTaskExpanded($idTask)) : false ;
		$infoLevel	= $isExpanded ? 3 : 1;
		$taskData	= TodoyuTaskManager::getTaskInfoArray($idTask, $infoLevel);

			// Prepare data array for template
		$data = array(
			'task'				=> $taskData,
			'taskIcons'			=> TodoyuTaskManager::getAllTaskIcons($idTask),
			'taskHeaderExtras'	=> TodoyuTaskManager::getAllTaskHeaderExtras($idTask),
			'hasSubtasks'		=> TodoyuTaskManager::hasSubTasks($idTask),
			'areSubtasksVisible'=> self::areSubTasksVisible($idTask),
			'isExpanded'		=> $isExpanded
		);

			// Render details if task is expanded
		if( $isExpanded ) {
			if( ! is_null($tab) && $idTask === $idTaskShow ) {
				$activeTab	= trim(strtolower($tab));
			} else {
				$activeTab	= TodoyuProjectPreferences::getActiveTaskTab($idTask);
			}

			$data['details']= TodoyuTaskRenderer::renderTaskDetail($idTask, $activeTab);
		}

			// Render sub tasks
		if( $withoutSubTasks === false && $data['hasSubtasks'] && $data['areSubtasksVisible'] ) {
			$data['subtasks'] = self::renderSubTasks($idTask, $idTaskShow);
		}

		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskDataBeforeRendering', $data, array($idTask));
		$tmpl	= 'ext/project/view/task-header.tmpl';

		return render($tmpl, $data);
	}



	/**
	 * Render a new task in edit mode
	 *
	 *
	 * @param	Integer	$idParentTask
	 * @param	String	$type
	 * @return	String
	 */
	public static function renderNewTaskEdit($idParentTask = 0, $idProject = 0, $type = TASK_TYPE_TASK) {
		$idParentTask	= intval($idParentTask);
		$idProject		= intval($idProject);
		$type			= intval($type);
		$idTask			= 0;

			// Find project id if not given as parameter
		if( $idProject === 0 && $idParentTask > 0 ) {
			$idProject	= TodoyuTaskManager::getProjectID($idParentTask);
		}

			// Create task with defaults in cache with ID: 0
		TodoyuTaskManager::createNewTaskWithDefaultsInCache($idParentTask, $idProject, $type);

			// Get default task
		$task		= TodoyuTaskManager::getTask($idTask);

			// Get task data for rendering
		$taskData	= $task->getTemplateData(2);

			// Render edit form wraped by details and data div like in the view template
		$wrapedForm	= TodoyuTaskRenderer::renderNewTaskEditForm($idProject, $idParentTask);

			// Prepare data array for template
		$tmpl	= 'ext/project/view/task-header.tmpl';
		$data = array(
			'task'		=> $taskData,
			'details'	=> $wrapedForm
		);

			// Call last hook before rendering
		$data	= TodoyuHookManager::callHookDataModifier('project', 'taskDataBeforeRendering', $data, array($idTask));

		return render($tmpl, $data);
	}



	/**
	 * Render editing view for a new project (form)
	 *
	 * @return	String
	 */
	public static function renderNewProjectEdit() {
			// Default project data
		$defaultData= TodoyuProjectManager::getDefaultProjectData();

			// Store task with default data in cache
		$idCache	= TodoyuRecordManager::makeClassKey('TodoyuProject', 0);
		$project	= new TodoyuProject(0);
		$project->injectData($defaultData);
		TodoyuCache::set($idCache, $project);

		$wrapedForm	= self::renderNewProjectEditForm();

		$tmpl	= 'ext/project/view/project-header.tmpl';
		$data	= $project->getTemplateData();
		$data['details'] = $wrapedForm;

		$newProjectEditContent = render($tmpl, $data);

			// Wrap project
		$tmpl	= 'ext/project/view/project-wrap.tmpl';
		$data	= array(
			'content'	=> $newProjectEditContent,
			'idProject'	=> 0
		);

		return render($tmpl, $data);
	}



	/**
	 * Render form for new project (wrapped by data DIV)
	 *
	 * @return	String
	 */
	public static function renderNewProjectEditForm() {
		$idProject	= 0;

			// Render form for new empty project
		$form	= self::renderProjectEditForm($idProject);

			// Render form into detail wrapper
		$data	= array(
			'idProject'	=> $idProject,
			'data'		=> $form
		);
		$tmpl	= 'ext/project/view/project-data-wrap.tmpl';

		return render($tmpl, $data);
	}



	/**
	 * Render tabs over the project taskTree. TabHeads for the 4 latest used projects are rendered.
	 *
	 * @return	String
	 */
	public static function renderProjectTabs() {
		$name		= 'project';
		$jsHandler	= 'Todoyu.Ext.project.ProjectTaskTree.onTabSelect.bind(Todoyu.Ext.project.ProjectTaskTree)';
		$tabs		= TodoyuProjectManager::getOpenProjectTabs();
		$active		= TodoyuProjectPreferences::getActiveProject();

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $active);
	}



	/**
	 * Render dummy tab (if no project is selected)
	 *
	 * @return	String
	 */
	public static function renderNoProjectSelectedTab() {
		$name		= 'project';
		$jsHandler	= 'Prototype.emptyFunction';
		$active		= 0;
		$tabs		= array(
			array(
				'id'		=> 'noselection',
				'label'		=> 'LLL:project.noproject.tab'
			)
		);

		return TodoyuTabheadRenderer::renderTabs($name, $tabs, $jsHandler, $active);
	}



	/**
	 * Render
	 *
	 * @param	Array	$projectIDs
	 * @return	String
	 */
	public static function renderProjectListing(array $projectIDs) {
		$projectIDs		= TodoyuArray::intval($projectIDs, true, true);
		$projectsHTML	= array();

		foreach($projectIDs as $idProject) {
			$projectsHTML[] = self::renderListingProject($idProject);
		}

		$tmpl	= 'ext/project/view/project-listing.tmpl';
		$data	= array(
			'projects'	=> $projectsHTML,
			'javascript'=> 'Todoyu.Ext.project.ContextMenuProject.attach();'
		);

		if( ! TodoyuRequest::isAjaxRequest() ) {
			TodoyuHookManager::callHook('project', 'renderProjects');
		}

		return render($tmpl, $data);
	}


	public static function renderListingProject($idProject) {
		$idProject	= intval($idProject);

				// Get project information
		$project	= TodoyuProjectManager::getProject($idProject);
		$isExpanded	= TodoyuProjectPreferences::isProjectDetailsExpanded($idProject);


			// Prepare data array for template
		$tmpl	= 'ext/project/view/project-listing-item.tmpl';
		$data 	= array(
			'project'			=> $project->getTemplateData(false),
			'isExpanded'		=> $isExpanded
		);

			// Render details if task is expanded
		if( $isExpanded ) {
			$data['details']= self::renderProjectDetails($idProject);
		}

		return render($tmpl, $data);
	}

}

?>