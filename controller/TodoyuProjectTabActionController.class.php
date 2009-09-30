<?php

class TodoyuProjectTabActionController extends TodoyuActionController {
	
	public function loadAction(array $params) {
		$idTask	= intval($params['task']);
		$tabKey	= $params['tab'];
		
		TodoyuProjectPreferences::saveSelectedTab($idTask, $tabKey, AREA);
		
		$tabConf	= TodoyuTaskManager::getTabConfig($tabKey);
		$funcRef	= $tabConf['content'];

		return TodoyuDiv::callUserFunction($funcRef, $idTask);
	}
	
	public function selectedAction(array $params) {
		$idTask	= intval($params['task']);
		$tabKey	= $params['tab'];
		
		TodoyuProjectPreferences::saveSelectedTab($idTask, $tabKey, AREA);
	}
	
}

?>