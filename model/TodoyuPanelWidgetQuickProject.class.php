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

class TodoyuPanelWidgetQuickProject extends TodoyuPanelWidget implements TodoyuPanelWidgetIf {

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

	public function renderContent()	{
		$tmpl = 'ext/project/view/panelwidget-quickproject.tmpl';

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

	public static function isAllowed() {
		return allowed('project', 'panelwidget.quickProject.use');
	}
}
?>