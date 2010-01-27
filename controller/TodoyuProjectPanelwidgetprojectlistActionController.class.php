<?php

class TodoyuProjectPanelwidgetProjectlistActionController extends TodoyuActionController {


	public function listAction(array $params) {
		$filters	= json_decode($params['filters'], true);

		$widget	= TodoyuPanelWidgetManager::getPanelWidget('ProjectList');

		$widget->saveFilters($filters);

		return $widget->renderList();
	}



	public function updatetreeAction(array $params) {
		$currentFilters	= $params['filter'];
		$currentFilters	= is_array($currentFilters) ? $currentFilters : array();

		TodoyuPanelWidgetProjectTree::updateActiveFilters($currentFilters, AREA);

		$params			= array();
		$projectTree 	= TodoyuPanelWidgetManager::getPanelWidget('ProjectTree', AREA, $params);

		return $projectTree->renderTree();
	}



	/**
	 * Update filterlist for project tree (add or remove a filter)
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public function updatefilterAction(array $params) {
		$command= $params['command'];
		$field	= $params['field'];

		if( $command === 'add' ) {
			TodoyuPanelWidgetProjectTree::addNewFilter($field, '', AREA);
		} elseif( $command === 'remove' ) {
			TodoyuPanelWidgetProjectTree::removeFilter($field, AREA);
		}

		$params			= array();
		$projectTree 	= TodoyuPanelWidgetManager::getPanelWidget('ProjectTree', $idArea, $params);

		return $projectTree->renderFilters();
	}

}

?>