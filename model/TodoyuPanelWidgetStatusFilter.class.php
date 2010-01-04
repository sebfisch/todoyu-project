<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Panel widget: status filter
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuPanelWidgetStatusFilter extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 * Preference name
	 *
	 */
	const PREF = 'panelwidget-statusfilter';



	/**
	 * Initialize panel widget status filter
	 *
	 * @param	Array		$config
	 * @param	Array		$params
	 * @param	Integer		$idArea
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0) {

			// construct PanelWidget (init basic configuration)
		parent::__construct(
			'project',								// ext key
			'statusfilter',							// panel widget ID
			'LLL:panelwidget-statusfilter.title',	// widget title text
			$config,								// widget config array
			$params,								// widget params
			$idArea									// area ID
		);
	}



	/**
	 * Get selected status IDs from preferences
	 *
	 * @return	Array
	 */
	public function getSelectedStatusIDs() {
		$statusIDs	= TodoyuProjectPreferences::getPref(self::PREF, 0, AREA);

		if( $statusIDs === false ) {
			$statusIDs = array();
		} else {
			$statusIDs = explode(',', $statusIDs);
		}

		return $statusIDs;
	}



	/**
	 * Render content of the panel widget (status list)
	 *
	 * @return	String
	 */
	public function renderContent() {
		$statusesInfos	= $this->getStatusesInfos();
		$selected		= $this->getSelectedStatusIDs();

		$tmpl 	= 'ext/project/view/panelwidgets/panelwidget-statusfilter.tmpl';
		$data	= array(
			'id'		=> $this->getID(),
			'statuses'	=> $statusesInfos,
			'selected'	=> $selected
		);

		$content	= render($tmpl, $data);

		$this->setContent($content);

		return $content;
	}



	/**
	 *	Get panelwidget statuses infos
	 *
	 *	@return	Array
	 */
	private function getStatusesInfos() {
		return TodoyuProjectStatusManager::getTaskStatusInfos();
	}



	/**
	 * Render full panel widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->renderContent();

			// Add public and widget assets
		TodoyuPage::addExtAssets('project');
		TodoyuPage::addExtAssets('project', 'panelwidget-statusfilter');

		TodoyuPage::addJsOnloadedFunction('Todoyu.Ext.project.PanelWidget.StatusFilter.init.bind(Todoyu.Ext.project.PanelWidget.StatusFilter)');

		return parent::render();
	}



	/**
	 *	Store prefs of the status filter panel widget
	 *
	 *	@param	Integer	$idArea
	 *	@param	Array	$statusIDs
	 */
	public static function saveSelectedStatuses($idArea = 0, array $statusIDs) {
		$idArea		= intval($idArea);
		$statuses	= implode(',', $statusIDs);

		TodoyuProjectPreferences::savePref(self::PREF, $statuses, 0, true, $idArea);
	}



	/**
	 *	Check allowance
	 *
	 *	@return	Boolean
	 */
	public static function isAllowed() {
		return allowed('project', 'panelwidget:statusFilter:use');
	}

}

?>