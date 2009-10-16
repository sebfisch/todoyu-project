<?php

class TodoyuProjectPanelwidgetProjecttreeActionController extends TodoyuActionController {

	public function subtasksAction(array $params) {
		$idParent	= intval($params['parent']);
		$type		= $params['type'];

		switch($type) {
			case 'project':
				TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', $idParent);
				break;
			case 'task':
				TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', $idParent);
				break;
		}

		$params			= array();
		$projectTree	= TodoyuPanelWidgetManager::getPanelWidget('ProjectTree', $idArea, $params);

		return $projectTree->renderTaskTree($idParent, $type, false);
	}

	public function collapseAction(array $params) {
		$idElement	= intval($params['element']);
		$type		= $params['type'];

		switch($type) {
			case 'project':
				TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', $idElement);
			break;
			case 'task':
				TodoyuPreferenceManager::deletePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', $idElement);
			break;
		}
	}

	public function expandAction(array $params) {
		$idElement	= intval($params['element']);
		$type		= $params['type'];

		switch($type) {
			case 'project':
				TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-project', $idElement);
			break;
			case 'task':
				TodoyuPreferenceManager::savePreference(EXTID_PROJECT, 'panelwidget-projecttree-exp-task', $idElement);
			break;
		}
	}

	public function updatetreeAction(array $params) {
		$currentFilters	= $params['filter'];
		$currentFilters	= is_array($currentFilters) ? $currentFilters : array();

		TodoyuPanelWidgetProjectTree::updateActiveFilters($currentFilters, AREA);

		$params			= array();
		$projectTree 	= TodoyuPanelWidgetManager::getPanelWidget('ProjectTree', AREA, $params);

		return $projectTree->renderTree();
	}

	public function updatefilterAction(array $params) {
		$action	= $params['action'];
		$field	= $params['field'];

		if( $action === 'add' ) {
			TodoyuPanelWidgetProjectTree::addNewFilter($field, '', AREA);
		}
		if( $action === 'remove' ) {
			TodoyuPanelWidgetProjectTree::removeFilter($field, AREA);
		}

		$params			= array();
		$projectTree 	= TodoyuPanelWidgetManager::getPanelWidget('ProjectTree', $idArea, $params);

		return $projectTree->renderFilters();
	}

}

?>