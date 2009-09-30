<?php

class TodoyuProjectAutocompleteActionController extends TodoyuActionController {

	public function userAction(array $params) {
		$sword		= trim($params['sword']);
		$config		= array();
		$results	= TodoyuUserFilterDataSource::autocompleteUsers($sword, $config);
		
			// Render & display output
		return TodoyuRenderer::renderAutocompleteList($results);
	}
		
}

?>