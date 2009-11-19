<?php

class TodoyuProjectPreferenceActionController extends TodoyuActionController {

	public function detailsexpandedAction(array $params) {
		$idProject	= intval($params['item']);
		$expanded	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveExpandedDetails($idProject, $expanded);
	}

	public function taskopenAction(array $params) {
		$idTask		= intval($params['item']);
		$expanded	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveTaskExpandedStatus($idTask, $expanded);
	}

	public function panelwidgetprojecttreeexpandAction(array $params) {
		$idItem	= intval($params['item']);
		$info	= explode(':', $params['value']);
		$type	= $info[0];
		$expand	= intval($info[1]) === 1;

		switch($type) {
			case 'project':
				PanelWidget_ProjectTree::saveProjectExpanded($idItem, AREA, $expand);
				break;
			case 'task':
				PanelWidget_ProjectTree::saveTaskExpanded($idItem, AREA, $expand);
				break;
		}
	}


	public function subtasksAction(array $params) {
		$idTask	= intval($params['item']);
		$isOpen	= intval($params['value']) === 1;

		TodoyuProjectPreferences::saveSubtasksVisibility($idTask, $isOpen, AREA);
	}


	public function panelwidgetstatusfilterAction(array $params) {
		$selectedStatuses	= TodoyuDiv::intExplode(',', $params['value'], true, true);

		TodoyuPanelWidgetStatusFilter::saveSelectedStatuses(AREA, $selectedStatuses);
	}



	/**
	 *	General panelWidget action, saves collapse status
	 *
	 *	@param	Array	$params
	 */
	public function pwidgetAction(array $params) {
		$idWidget	= $params['item'];
		$value		= $params['value'];

		TodoyuPanelWidgetManager::saveCollapsedStatus(EXTID_PROJECT, $idWidget, $value);
	}
}

?>