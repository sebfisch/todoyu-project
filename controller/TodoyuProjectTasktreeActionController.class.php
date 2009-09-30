<?php

class TodoyuProjectTasktreeActionController extends TodoyuActionController {
	
	public function updateAction(array $params) {
		$idProject	= intval($params['project']);
		$filter		= $params['filter'];

			// If a filter is submitted
		if( ! is_null($filter) ) {
			TodoyuProjectManager::updateProjectTreeFilters($filter['name'], $filter['value']);
		}

		return TodoyuProjectRenderer::renderProjectTaskTree($idProject);
	}
	
}

?>