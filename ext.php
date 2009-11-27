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
 * Extension main file for project extension
 *
 * @package		Todoyu
 * @subpackage	Project
 */

	// Declare ext ID, path
define('EXTID_PROJECT', 112);
define('PATH_EXT_PROJECT', PATH_EXT . '/project');

	// Register module locales
TodoyuLocale::register('project', PATH_EXT_PROJECT . '/locale/ext.xml');
TodoyuLocale::register('task', PATH_EXT_PROJECT . '/locale/task.xml');
TodoyuLocale::register('projectFilter', PATH_EXT_PROJECT . '/locale/filter.xml');
TodoyuLocale::register('panelwidget-projecttree', PATH_EXT_PROJECT . '/locale/panelwidget-projecttree.xml');
TodoyuLocale::register('panelwidget-statusfilter', PATH_EXT_PROJECT . '/locale/panelwidget-statusfilter.xml');
TodoyuLocale::register('panelwidget-quickproject', PATH_EXT_PROJECT . '/locale/panelwidget-quickproject.xml');

	// Request configurations
require_once( PATH_EXT_PROJECT . '/config/constants.php' );
require_once( PATH_EXT_PROJECT . '/config/extension.php' );
require_once( PATH_EXT_PROJECT . '/config/filters.php' );
require_once( PATH_EXT_PROJECT . '/config/search.php' );
require_once( PATH_EXT_PROJECT . '/config/panelwidgets.php' );
require_once( PATH_EXT_PROJECT . '/config/hooks.php' );
require_once( PATH_EXT_PROJECT . '/dwoo/plugins.php');

?>