<?php

class TodoyuProjectContextmenuActionController extends TodoyuActionController {

	public function taskAction(array $params) {
		$idTask		= intval($params['task']);
		$contextMenu= new TodoyuContextMenu('Task', $idTask);

		TodoyuHeader::sendHeaderJSON();

		return $contextMenu->getJSON();
	}
	
	
	public function projectAction(array $params) {
		$idProject	= intval($params['project']);
		$contextMenu= new TodoyuContextMenu('Project', $idProject);

		TodoyuHeader::sendHeaderJSON();

		return $contextMenu->getJSON();
	}
	
}

?>