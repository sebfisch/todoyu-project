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
 * Panel widget for project tree
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuPanelWidgetProjectList extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Initialize projectTree PanelWidget
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 * @param	Boolean		$expanded
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

			// Construct PanelWidget (init basic configuration)
		parent::__construct(
			'project',								// ext key
			'projectlist',							// panel widget ID
			'LLL:panelwidget-projecttree.title',	// widget title text
			$config,								// widget config array
			$params,								// widget parameters
			$idArea									// area ID
		);

		$filterJSON	= json_encode(self::getFilters());

			// Init widget JS (observers)
		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.PanelWidget.ProjectList.init.bind(Todoyu.Ext.project.PanelWidget.ProjectList, ' . $filterJSON . ')', 100);
	}



	/**
	 * Get project IDs which match to current filters
	 *
	 * @return	Array
	 */
	private function getProjectIDs() {
		$filters	= self::getFilters();
		$filter		= new TodoyuProjectFilter($filters);
		$limit		= intval(Todoyu::$CONFIG['EXT']['project']['panelWidgetProjectList']['maxProjects']);

			// Get matching project IDs
		$projectIDs	= $filter->getProjectIDs('', $limit);

		return $projectIDs;
	}



	/**
	 * Get projects which match the filters
	 *
	 * @return	Array
	 */
	private function getListedProjects() {
		$projectIDs	= $this->getProjectIDs();

		if( sizeof($projectIDs) > 0 ) {
			$fields	= '	ext_project_project.id,
						ext_project_project.title,
						ext_project_project.status,
						ext_contact_company.shortname as companyShort,
						ext_contact_company.title as company';
			$tables	= '	ext_project_project,
						ext_contact_company';
			$where	= '	ext_project_project.id_company	= ext_contact_company.id  AND
						ext_project_project.id IN(' . implode(',', $projectIDs) . ')';
			$order	= ' ext_contact_company.shortname,
						ext_project_project.title';

			$projects	= Todoyu::db()->getArray($fields, $tables, $where, '', $order);
		} else {
			$projects	= array();
		}

		return $projects;
	}



	/**
	 * Get value of the full-text filter
	 *
	 * @return	String
	 */
	public static function getSearchText() {
		$filters	= self::getFilters();
		$fulltext	= '';

		foreach($filters as $filter) {
			if( $filter['filter'] === 'fulltext' ) {
				$fulltext = $filter['value'];
			}
		}

		return $fulltext;
	}



	/**
	 * Render filter form
	 *
	 * @return	String
	 */
	public static function renderFilter() {
		$xmlPath= 'ext/project/config/form/panelwidget-projectlist.xml';
		$form	= TodoyuFormManager::getForm($xmlPath);
		$data	= array(
			'fulltext'	=> self::getSearchText()
		);

		$form->setFormData($data);
		$form->setUseRecordID(false);

		return $form->render();
	}



	/**
	 * Render project list
	 *
	 * @return	String
	 */
	public function renderList() {
		$tmpl	= 'ext/project/view/panelwidgets/panelwidget-projectlist-list.tmpl';
		$data	= array(
			'id'		=> $this->getID(),
			'projects'	=> $this->getListedProjects()
		);

		return render($tmpl, $data);
	}



	/**
	 * Render the panel widget content
	 *
	 * @return	String
	 */
	public function renderContent() {
		$filter	= self::renderFilter();
		$list	= $this->renderList();

		$tmpl	= 'ext/project/view/panelwidgets/panelwidget-projectlist.tmpl';
		$data	= array(
			'id'		=> $this->getID(),
			'filter'	=> $filter,
			'list'		=> $list
		);

		$content = render($tmpl, $data);

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

		return parent::render();
	}



	/**
	 * Get active filters
	 *
	 * @param 	Integer	$idArea
	 * @return	Array
	 */
	public static function getFilters() {
		$filters = TodoyuProjectPreferences::getPref('panelwidget-projectlist-filter', 0, AREA);

		if( $filters === false || $filters === '' ) {
			return array();
		} else {
			return json_decode($filters, true);
		}
	}



	/**

	 *
	 * @param	Array		$activeFilters
	 * @param	Integer		$idArea
	 */
	public function saveFilters(array $filters) {
		$filterConfig = array();

		foreach($filters as $name => $value) {
			$filterConfig[] = array(
				'filter'=> $name,
				'value'	=> $value
			);
		}

		$filterPref	= json_encode($filterConfig);

		TodoyuProjectPreferences::savePref('panelwidget-projectlist-filter', $filterPref, 0, true, AREA);
	}



	/**
	 * Check panelWidget access permission
	 *
	 * @return	Boolean
	 */
	public static function isAllowed() {
		return allowed('project', 'general:use');
	}

}

?>