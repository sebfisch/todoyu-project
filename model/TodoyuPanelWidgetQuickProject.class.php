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
 * Panelwidget to add a project
 *
 * @package		Todoyu
 * @subpackage	Project
 */
class TodoyuPanelWidgetQuickProject extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

	/**
	 *	Constructor
	 *
	 *	@param	Array	$config
	 *	@param	Array	$params
	 *	@param	Integer	$idArea
	 */
	public function __construct(array $config, array $params = array(), $idArea = 0)	{
		parent::__construct(
			'project',								// ext key
			'quickproject',							// panel widget ID
			'LLL:panelwidget-quickproject.title',	// widget title text
			$config,								// widget config array
			$params,								// widget params
			$idArea									// area ID
		);


		$this->addHasIconClass();
	}



	/**
	 *	Render content
	 *
	 *	@return	String
	 */
	public function renderContent()	{
		$tmpl = 'ext/project/view/panelwidgets/panelwidget-quickproject.tmpl';

		$data = array();

		$content = render($tmpl, $data);

		$this->setContent($content);

		return $content;
	}



	/**
	 * Render full panel widget
	 *
	 * @return	String
	 */
	public function render() {
		$this->renderContent();

			// Add widget assets
		TodoyuPage::addExtAssets('project', 'panelwidget-quickproject');

		return parent::render();
	}



	/**
	 *	Check whether allowance given
	 *
	 *	@return	Boolean
	 */
	public static function isAllowed() {
		return allowed('project', 'panelwidgets:quickProject');
	}
}
?>