<?php

class TodoyuProjectSubtasksActionController extends TodoyuActionController {
	
	public function loadAction(array $params) {
		$idTask		= intval($params['task']);
		$idTaskShow	= intval($params['show']);

			// Save open status
		TodoyuProjectPreferences::saveSubtasksVisibility($idTask, true, AREA);

		return TodoyuProjectRenderer::renderSubtasks($idTask, $idTaskShow);
	}
	
	
	
}

?>